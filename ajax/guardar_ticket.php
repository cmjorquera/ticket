<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../helpers/tickets.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responder_json(['ok' => false, 'error' => 'Método no permitido.'], 405);
}

$datos = entrada_ajax();
$accion = strtolower(trim((string) ($datos['accion'] ?? 'crear')));
$usuarioId = (int) ($_SESSION['id'] ?? 0);
$csrf = (string) ($datos['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

if (!ticket_csrf_valido($csrf)) {
    responder_json(['ok' => false, 'error' => 'La sesión del formulario venció. Recarga la página.'], 419);
}

try {
    $db = Conexion::getInstance('sistema_panel_central');

    if ($accion === 'crear') {
        $categoriaId = (int) ($datos['categoria_id'] ?? 0);
        $colegioId = (int) ($datos['colegio_id'] ?? 0);
        $asunto = trim((string) ($datos['asunto'] ?? ''));
        $descripcion = trim((string) ($datos['descripcion'] ?? ''));
        $prioridad = strtolower(trim((string) ($datos['prioridad'] ?? 'media')));
        $prioridad = $prioridad === 'critica' ? 'crítica' : $prioridad;

        if ($categoriaId <= 0 || $colegioId <= 0 || $asunto === '' || $descripcion === '') {
            responder_json(['ok' => false, 'error' => 'Completa todos los campos obligatorios.'], 422);
        }
        if (strlen($asunto) < 5) {
            responder_json(['ok' => false, 'error' => 'El asunto debe tener al menos 5 caracteres.'], 422);
        }
        if (strlen($descripcion) < 10) {
            responder_json(['ok' => false, 'error' => 'La descripción debe tener al menos 10 caracteres.'], 422);
        }
        if (strlen($asunto) > 180 || strlen($descripcion) > 10000) {
            responder_json(['ok' => false, 'error' => 'El asunto o la descripción exceden el largo permitido.'], 422);
        }
        if (!in_array($prioridad, ['baja', 'media', 'alta', 'crítica'], true)) {
            responder_json(['ok' => false, 'error' => 'La prioridad seleccionada no es válida.'], 422);
        }
        if (!$db->fetchOne('SELECT id_categoria FROM categoria_de_ticket WHERE id_categoria = ? AND estado = 1 LIMIT 1', [$categoriaId])) {
            responder_json(['ok' => false, 'error' => 'La categoría seleccionada no está disponible.'], 422);
        }
        if (!$db->fetchOne('SELECT id_colegio FROM colegio WHERE id_colegio = ? AND estado = 1 LIMIT 1', [$colegioId])) {
            responder_json(['ok' => false, 'error' => 'El colegio seleccionado no está disponible.'], 422);
        }

        $pertenece = $db->fetchOne(
            'SELECT 1 FROM usuario_colegio WHERE id_usuario = ? AND id_colegio = ? AND estado = 1 LIMIT 1',
            [$usuarioId, $colegioId]
        );
        if (!$pertenece && !es_administrador_global($usuarioId, $db)) {
            responder_json(['ok' => false, 'error' => 'No puedes crear tickets para ese colegio.'], 403);
        }

        $tecnico = $db->fetchOne(
            "SELECT ct.id_tecnico
               FROM categoria_tecnico ct
               JOIN usuarios u ON u.id = ct.id_tecnico AND LOWER(u.estado) = 'activo'
          LEFT JOIN tickets abiertos
                 ON abiertos.id_tecnico = ct.id_tecnico
                AND abiertos.id_estado IN (1, 2, 3)
                AND abiertos.estado = 1
              WHERE ct.id_categoria = ?
           GROUP BY ct.id_tecnico
           ORDER BY COUNT(abiertos.id_ticket) ASC, ct.id_tecnico ASC
              LIMIT 1",
            [$categoriaId]
        );
        $tecnicoId = $tecnico ? (int) $tecnico['id_tecnico'] : null;

        $archivos = [];
        if (isset($_FILES['archivos']['name']) && is_array($_FILES['archivos']['name'])) {
            $permitidos = [
                'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp',
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'application/vnd.ms-excel' => 'xls',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            ];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            foreach ($_FILES['archivos']['name'] as $i => $nombreOriginal) {
                $error = (int) ($_FILES['archivos']['error'][$i] ?? UPLOAD_ERR_NO_FILE);
                if ($error === UPLOAD_ERR_NO_FILE) continue;
                if ($error !== UPLOAD_ERR_OK) responder_json(['ok' => false, 'error' => 'Uno de los archivos no pudo subirse.'], 422);
                if (count($archivos) >= 5) responder_json(['ok' => false, 'error' => 'Puedes adjuntar un máximo de 5 archivos.'], 422);
                $temporal = (string) ($_FILES['archivos']['tmp_name'][$i] ?? '');
                $tamano = (int) ($_FILES['archivos']['size'][$i] ?? 0);
                $mime = $temporal !== '' ? (string) $finfo->file($temporal) : '';
                if ($tamano <= 0 || $tamano > 5 * 1024 * 1024 || !isset($permitidos[$mime])) {
                    responder_json(['ok' => false, 'error' => 'Revisa el tipo y tamaño de los archivos adjuntos.'], 422);
                }
                $archivos[] = ['original' => basename((string) $nombreOriginal), 'tmp' => $temporal, 'mime' => $mime, 'extension' => $permitidos[$mime], 'tamano' => $tamano];
            }
        }

        $pdo = $db->getPDO();
        $guardados = [];
        $prioridadId = ['baja' => 1, 'media' => 2, 'alta' => 3, 'crítica' => 4][$prioridad] ?? 2;
        $estadoNuevo = $db->fetchOne("SELECT id FROM estados_ticket WHERE LOWER(nombre) IN ('nuevo','recibido') ORDER BY id LIMIT 1");
        $estadoInicial = $tecnicoId ? 2 : (int) ($estadoNuevo['id'] ?? 1);
        $identificador = bin2hex(random_bytes(8));
        $pdo->beginTransaction();
        try {
            $db->execute(
                "INSERT INTO tickets
                    (id_usuario, id_categoria_ticket, asunto, descripcion_ticket,
                     id_prioridad, id_estado, identificador, id_tecnico, estado)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)",
                [$usuarioId, $categoriaId, $asunto, $descripcion, $prioridadId, $estadoInicial, $identificador, $tecnicoId]
            );
            $ticketId = (int) $db->lastInsertId();
            if ($tecnicoId) {
                $db->execute(
                    'INSERT INTO proceso_tickets (id_ticket, fecha_creacion_inicio, hora_creacion_inicio, fecha_asignacion_tecnico, hora_asignacion_tecnico) VALUES (?, CURDATE(), CURTIME(), CURDATE(), CURTIME())',
                    [$ticketId]
                );
            } else {
                $db->execute(
                    'INSERT INTO proceso_tickets (id_ticket, fecha_creacion_inicio, hora_creacion_inicio) VALUES (?, CURDATE(), CURTIME())',
                    [$ticketId]
                );
            }
            if ($archivos) {
                $directorio = dirname(__DIR__) . '/uploads/tickets/' . $ticketId;
                if (!is_dir($directorio) && !mkdir($directorio, 0750, true) && !is_dir($directorio)) {
                    throw new RuntimeException('No fue posible preparar la carpeta de adjuntos.');
                }
                foreach ($archivos as $archivo) {
                    $nombreSeguro = bin2hex(random_bytes(16)) . '.' . $archivo['extension'];
                    $destino = $directorio . '/' . $nombreSeguro;
                    if (!move_uploaded_file($archivo['tmp'], $destino)) throw new RuntimeException('No fue posible guardar un archivo adjunto.');
                    $guardados[] = $destino;
                    $db->execute(
                        'INSERT INTO archivos_adjuntos_ticket (id_ticket, nombre_archivo, ruta_archivo, tipo_archivo, tamaño_archivo, id_usuario, fecha_subida) VALUES (?, ?, ?, ?, ?, ?, NOW())',
                        [$ticketId, $archivo['original'], 'uploads/tickets/' . $ticketId . '/' . $nombreSeguro, $archivo['extension'], $archivo['tamano'], $usuarioId]
                    );
                }
            }
            $pdo->commit();
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            foreach ($guardados as $archivoGuardado) @unlink($archivoGuardado);
            throw $ex;
        }
        ticket_registrar_cambio($db, $ticketId, $usuarioId, 'crear');
        responder_json([
            'ok' => true,
            'ticket_id' => $ticketId,
            'mensaje' => $tecnicoId ? 'Ticket creado y asignado automáticamente.' : 'Ticket creado; queda pendiente de asignación.',
        ], 201);
    }

    $ticketId = (int) ($datos['ticket_id'] ?? 0);
    $ticket = $ticketId > 0
        ? $db->fetchOne(
            "SELECT t.id_ticket, uc_ticket.id_colegio, t.id_tecnico, t.id_estado, t.estado
               FROM tickets t
          LEFT JOIN (
                        SELECT id_usuario, MIN(id_colegio) AS id_colegio
                          FROM usuario_colegio
                         WHERE estado = 1
                      GROUP BY id_usuario
                    ) uc_ticket ON uc_ticket.id_usuario = t.id_usuario
              WHERE t.id_ticket = ? AND t.estado = 1
              LIMIT 1",
            [$ticketId]
        )
        : false;
    if (!$ticket) {
        responder_json(['ok' => false, 'error' => 'Ticket no encontrado.'], 404);
    }

    if ($accion === 'actualizar_estado') {
        $estadoId = ticket_estado_id($datos['estado'] ?? '');
        if ($estadoId <= 0) {
            responder_json(['ok' => false, 'error' => 'Estado no válido.'], 422);
        }
        $esTecnicoAsignado = (int) ($ticket['id_tecnico'] ?? 0) === $usuarioId;
        if (!$esTecnicoAsignado && !puede_administrar_ticket($usuarioId, (int) $ticket['id_colegio'], $db)) {
            responder_json(['ok' => false, 'error' => 'No tienes permiso para actualizar este ticket.'], 403);
        }
        $db->execute('UPDATE tickets SET id_estado = ? WHERE id_ticket = ?', [$estadoId, $ticketId]);
        if ($estadoId === 3) {
            $db->execute('UPDATE proceso_tickets SET fecha_comienzo_ticket = COALESCE(fecha_comienzo_ticket, CURDATE()), hora_comienzo_ticket = COALESCE(hora_comienzo_ticket, CURTIME()) WHERE id_ticket = ?', [$ticketId]);
        } elseif ($estadoId === 5) {
            $db->execute('UPDATE proceso_tickets SET fecha_termino_ticket = COALESCE(fecha_termino_ticket, CURDATE()), hora_termino_ticket = COALESCE(hora_termino_ticket, CURTIME()) WHERE id_ticket = ?', [$ticketId]);
        }
        ticket_registrar_cambio($db, $ticketId, $usuarioId, 'cambiar_estado', 'id_estado', (string) $ticket['id_estado'], (string) $estadoId);
        responder_json(['ok' => true, 'mensaje' => 'Estado actualizado.']);
    }

    if ($accion === 'asignar') {
        if (!puede_administrar_ticket($usuarioId, (int) $ticket['id_colegio'], $db)) {
            responder_json(['ok' => false, 'error' => 'No tienes permiso para asignar este ticket.'], 403);
        }
        $tecnicoId = (int) ($datos['tecnico_id'] ?? 0);
        if ($tecnicoId > 0 && !$db->fetchOne("SELECT id FROM usuarios WHERE id = ? AND LOWER(estado) = 'activo' LIMIT 1", [$tecnicoId])) {
            responder_json(['ok' => false, 'error' => 'El técnico seleccionado no está disponible.'], 422);
        }
        $estadoAsignacion = $tecnicoId > 0 && (int) $ticket['id_estado'] === 1 ? 2 : (int) $ticket['id_estado'];
        $db->execute('UPDATE tickets SET id_tecnico = ?, id_estado = ? WHERE id_ticket = ?', [$tecnicoId ?: null, $estadoAsignacion, $ticketId]);
        if ($tecnicoId > 0) {
            $db->execute('UPDATE proceso_tickets SET fecha_asignacion_tecnico = COALESCE(fecha_asignacion_tecnico, CURDATE()), hora_asignacion_tecnico = COALESCE(hora_asignacion_tecnico, CURTIME()) WHERE id_ticket = ?', [$ticketId]);
        }
        ticket_registrar_cambio($db, $ticketId, $usuarioId, 'asignar', 'id_tecnico', (string) ($ticket['id_tecnico'] ?? ''), $tecnicoId > 0 ? (string) $tecnicoId : null);
        responder_json(['ok' => true, 'mensaje' => $tecnicoId ? 'Ticket asignado.' : 'Asignación retirada.']);
    }

    if ($accion === 'actualizar') {
        if (!puede_administrar_ticket($usuarioId, (int) $ticket['id_colegio'], $db)) {
            responder_json(['ok' => false, 'error' => 'No tienes permiso para gestionar este ticket.'], 403);
        }
        $estadoId = ticket_estado_id($datos['estado'] ?? '');
        $tecnicoId = (int) ($datos['tecnico_id'] ?? 0);
        if ($estadoId <= 0) {
            responder_json(['ok' => false, 'error' => 'Estado no válido.'], 422);
        }
        if ($tecnicoId > 0 && !$db->fetchOne("SELECT id FROM usuarios WHERE id = ? AND LOWER(estado) = 'activo' LIMIT 1", [$tecnicoId])) {
            responder_json(['ok' => false, 'error' => 'El técnico seleccionado no está disponible.'], 422);
        }
        $db->execute(
            'UPDATE tickets SET id_tecnico = ?, id_estado = ? WHERE id_ticket = ?',
            [$tecnicoId ?: null, $estadoId, $ticketId]
        );
        if ($tecnicoId > 0) {
            $db->execute('UPDATE proceso_tickets SET fecha_asignacion_tecnico = COALESCE(fecha_asignacion_tecnico, CURDATE()), hora_asignacion_tecnico = COALESCE(hora_asignacion_tecnico, CURTIME()) WHERE id_ticket = ?', [$ticketId]);
        }
        if ($estadoId === 3) {
            $db->execute('UPDATE proceso_tickets SET fecha_comienzo_ticket = COALESCE(fecha_comienzo_ticket, CURDATE()), hora_comienzo_ticket = COALESCE(hora_comienzo_ticket, CURTIME()) WHERE id_ticket = ?', [$ticketId]);
        } elseif ($estadoId === 5) {
            $db->execute('UPDATE proceso_tickets SET fecha_termino_ticket = COALESCE(fecha_termino_ticket, CURDATE()), hora_termino_ticket = COALESCE(hora_termino_ticket, CURTIME()) WHERE id_ticket = ?', [$ticketId]);
        }
        ticket_registrar_cambio($db, $ticketId, $usuarioId, 'actualizar', 'id_estado', (string) $ticket['id_estado'], (string) $estadoId);
        responder_json(['ok' => true, 'mensaje' => 'Asignación y estado actualizados.']);
    }

    if ($accion === 'eliminar') {
        if (!es_administrador_global($usuarioId, $db)) {
            responder_json(['ok' => false, 'error' => 'Solo un administrador general puede eliminar tickets.'], 403);
        }
        $db->execute('UPDATE tickets SET estado = 2 WHERE id_ticket = ?', [$ticketId]);
        responder_json(['ok' => true, 'mensaje' => 'Ticket eliminado.']);
    }

    responder_json(['ok' => false, 'error' => 'Acción no reconocida.'], 400);
} catch (Throwable $ex) {
    error_log('Error en guardar_ticket.php: ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible procesar el ticket. Verifica que las tablas de tickets estén instaladas.'], 500);
}
