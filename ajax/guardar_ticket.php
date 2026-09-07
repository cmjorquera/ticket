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
                 ON abiertos.id_tecnico_asignado = ct.id_tecnico
                AND abiertos.estado IN ('nuevo', 'en_proceso')
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
        $pdo->beginTransaction();
        try {
            $db->execute(
                "INSERT INTO tickets
                    (id_usuario, id_categoria, id_colegio, asunto, descripcion, prioridad, estado, fecha_creacion, id_tecnico_asignado)
                 VALUES (?, ?, ?, ?, ?, ?, 'nuevo', NOW(), ?)",
                [$usuarioId, $categoriaId, $colegioId, $asunto, $descripcion, $prioridad, $tecnicoId]
            );
            $ticketId = (int) $db->lastInsertId();
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
                        'INSERT INTO ticket_adjuntos (id_ticket, nombre_original, nombre_archivo, tipo_mime, tamano, fecha_creacion) VALUES (?, ?, ?, ?, ?, NOW())',
                        [$ticketId, $archivo['original'], $nombreSeguro, $archivo['mime'], $archivo['tamano']]
                    );
                }
            }
            $pdo->commit();
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            foreach ($guardados as $archivoGuardado) @unlink($archivoGuardado);
            throw $ex;
        }
        responder_json([
            'ok' => true,
            'ticket_id' => $ticketId,
            'mensaje' => $tecnicoId ? 'Ticket creado y asignado automáticamente.' : 'Ticket creado; queda pendiente de asignación.',
        ], 201);
    }

    $ticketId = (int) ($datos['ticket_id'] ?? 0);
    $ticket = $ticketId > 0
        ? $db->fetchOne('SELECT id_ticket, id_colegio, id_tecnico_asignado, estado FROM tickets WHERE id_ticket = ? LIMIT 1', [$ticketId])
        : false;
    if (!$ticket) {
        responder_json(['ok' => false, 'error' => 'Ticket no encontrado.'], 404);
    }

    if ($accion === 'actualizar_estado') {
        $estado = strtolower(trim((string) ($datos['estado'] ?? '')));
        if (!in_array($estado, ['nuevo', 'en_proceso', 'resuelto', 'cerrado'], true)) {
            responder_json(['ok' => false, 'error' => 'Estado no válido.'], 422);
        }
        $esTecnicoAsignado = (int) ($ticket['id_tecnico_asignado'] ?? 0) === $usuarioId;
        if (!$esTecnicoAsignado && !puede_administrar_ticket($usuarioId, (int) $ticket['id_colegio'], $db)) {
            responder_json(['ok' => false, 'error' => 'No tienes permiso para actualizar este ticket.'], 403);
        }
        $fechaRespuesta = in_array($estado, ['resuelto', 'cerrado'], true) ? ', fecha_respuesta = COALESCE(fecha_respuesta, NOW())' : '';
        $db->execute("UPDATE tickets SET estado = ?{$fechaRespuesta} WHERE id_ticket = ?", [$estado, $ticketId]);
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
        $db->execute('UPDATE tickets SET id_tecnico_asignado = ? WHERE id_ticket = ?', [$tecnicoId ?: null, $ticketId]);
        responder_json(['ok' => true, 'mensaje' => $tecnicoId ? 'Ticket asignado.' : 'Asignación retirada.']);
    }

    if ($accion === 'actualizar') {
        if (!puede_administrar_ticket($usuarioId, (int) $ticket['id_colegio'], $db)) {
            responder_json(['ok' => false, 'error' => 'No tienes permiso para gestionar este ticket.'], 403);
        }
        $estado = strtolower(trim((string) ($datos['estado'] ?? '')));
        $tecnicoId = (int) ($datos['tecnico_id'] ?? 0);
        if (!in_array($estado, ['nuevo', 'en_proceso', 'resuelto', 'cerrado'], true)) {
            responder_json(['ok' => false, 'error' => 'Estado no válido.'], 422);
        }
        if ($tecnicoId > 0 && !$db->fetchOne("SELECT id FROM usuarios WHERE id = ? AND LOWER(estado) = 'activo' LIMIT 1", [$tecnicoId])) {
            responder_json(['ok' => false, 'error' => 'El técnico seleccionado no está disponible.'], 422);
        }
        $fechaRespuesta = in_array($estado, ['resuelto', 'cerrado'], true) ? ', fecha_respuesta = COALESCE(fecha_respuesta, NOW())' : '';
        $db->execute(
            "UPDATE tickets SET id_tecnico_asignado = ?, estado = ?{$fechaRespuesta} WHERE id_ticket = ?",
            [$tecnicoId ?: null, $estado, $ticketId]
        );
        responder_json(['ok' => true, 'mensaje' => 'Asignación y estado actualizados.']);
    }

    if ($accion === 'eliminar') {
        if (!es_administrador_global($usuarioId, $db)) {
            responder_json(['ok' => false, 'error' => 'Solo un administrador general puede eliminar tickets.'], 403);
        }
        $db->execute('DELETE FROM tickets WHERE id_ticket = ?', [$ticketId]);
        responder_json(['ok' => true, 'mensaje' => 'Ticket eliminado.']);
    }

    responder_json(['ok' => false, 'error' => 'Acción no reconocida.'], 400);
} catch (Throwable $ex) {
    error_log('Error en guardar_ticket.php: ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible procesar el ticket. Verifica que las tablas de tickets estén instaladas.'], 500);
}
