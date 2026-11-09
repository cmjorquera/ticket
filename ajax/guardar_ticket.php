<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../helpers/tickets.php';
require_once __DIR__ . '/../clases/Tickets/BitacoraTicket.php';
require_once __DIR__ . '/../clases/Tickets/AvisosTicket.php';

/** Nombre corto de un usuario para los registros automáticos de la bitácora. */
function bitacora_nombre(Conexion $db, ?int $id): string
{
    if (!$id) {
        return 'nadie';
    }
    $u = $db->fetchOne("SELECT TRIM(CONCAT_WS(' ', nombre, apellido_paterno)) AS n FROM usuarios WHERE id = ?", [$id]);
    return (string) ($u['n'] ?? ('usuario ' . $id));
}

function bitacora_estado(int $id): string
{
    return ticket_estados_legacy()[$id]['nombre'] ?? ('estado ' . $id);
}
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
        $solicitanteId = (int) ($datos['solicitante_id'] ?? $usuarioId);
        $asunto = trim((string) ($datos['asunto'] ?? ''));
        $descripcion = ticket_sanitizar_descripcion((string) ($datos['descripcion'] ?? ''));
        $descripcionTexto = ticket_descripcion_texto($descripcion);
        $esBorrador = (string) ($datos['modo'] ?? '') === 'borrador'
            || (int) ($datos['es_borrador'] ?? 0) === 1;

        if ($categoriaId <= 0 || $solicitanteId <= 0 || $asunto === '' || $descripcionTexto === '') {
            responder_json(['ok' => false, 'error' => 'Completa todos los campos obligatorios.'], 422);
        }
        if (strlen($asunto) < 5) {
            responder_json(['ok' => false, 'error' => 'El asunto debe tener al menos 5 caracteres.'], 422);
        }
        if (strlen($descripcionTexto) < 10) {
            responder_json(['ok' => false, 'error' => 'La descripción debe tener al menos 10 caracteres.'], 422);
        }
        if (strlen($asunto) > 180 || strlen($descripcionTexto) > 10000) {
            responder_json(['ok' => false, 'error' => 'El asunto o la descripción exceden el largo permitido.'], 422);
        }
        if (!$db->fetchOne('SELECT id_categoria FROM categoria_de_ticket WHERE id_categoria = ? AND estado = 1 LIMIT 1', [$categoriaId])) {
            responder_json(['ok' => false, 'error' => 'La categoría seleccionada no está disponible.'], 422);
        }
        if (!$db->fetchOne("SELECT id FROM usuarios WHERE id = ? AND LOWER(estado) = 'activo' LIMIT 1", [$solicitanteId])) {
            responder_json(['ok' => false, 'error' => 'El solicitante seleccionado no está disponible.'], 422);
        }
        if (!puede_crear_ticket_para($usuarioId, $solicitanteId, $db)) {
            responder_json(['ok' => false, 'error' => 'No puedes crear tickets para ese usuario.'], 403);
        }
        $colegio = $db->fetchOne(
            "SELECT uc.id_colegio, c.nom_colegio
               FROM usuario_colegio uc
               JOIN colegio c ON c.id_colegio = uc.id_colegio AND c.estado = 1
              WHERE uc.id_usuario = ? AND uc.estado = 1
           ORDER BY uc.id_colegio ASC
              LIMIT 1",
            [$solicitanteId]
        );
        if (!$colegio) {
            responder_json(['ok' => false, 'error' => 'El solicitante no tiene un colegio activo asociado.'], 422);
        }
        if ($solicitanteId === $usuarioId) {
            $_SESSION['ticket_colegio_id'] = (int) $colegio['id_colegio'];
        }

        // Técnico: el de la categoría con menos tickets abiertos. Con el modelo de
        // roles solo cuentan los funcionarios de Informática de Seduc
        // (configuracion/logica/LOGICA_CATEGORIAS.txt).
        require_once __DIR__ . '/../clases/RolesUsuario.php';
        [$condTecnico, $paramTecnico] = (new RolesUsuario($db))->modeloActivo()
            ? RolesUsuario::condicionDepartamento('u')
            : ['1 = 1', []];
        $tecnico = !$esBorrador ? $db->fetchOne(
            "SELECT ct.id_tecnico
               FROM categoria_tecnico ct
               JOIN usuarios u ON u.id = ct.id_tecnico AND LOWER(u.estado) = 'activo'
          LEFT JOIN tickets abiertos
                 ON abiertos.id_tecnico = ct.id_tecnico
                AND abiertos.id_estado IN (1, 2, 3)
                AND abiertos.estado = 1
              WHERE ct.id_categoria = ? AND {$condTecnico}
           GROUP BY ct.id_tecnico
           ORDER BY COUNT(abiertos.id_ticket) ASC, ct.id_tecnico ASC
              LIMIT 1",
            array_merge([$categoriaId], $paramTecnico)
        ) : false;
        $tecnicoId = $tecnico ? (int) $tecnico['id_tecnico'] : null;

        $archivos = [];
        if (isset($_FILES['archivos'])) {
            try {
                $archivos = ticket_validar_archivos($_FILES['archivos']);
            } catch (RuntimeException $e) {
                responder_json(['ok' => false, 'error' => $e->getMessage()], 422);
            }
            if (count($archivos) > TICKET_MAX_ADJUNTOS) {
                responder_json(['ok' => false, 'error' => 'Puedes adjuntar un máximo de ' . TICKET_MAX_ADJUNTOS . ' archivos.'], 422);
            }
        }

        $pdo = $db->getPDO();
        $guardados = [];
        $prioridadId = null;   // la define el técnico al comenzar (LOGICA_COMENZAR_TICKET.txt)
        $estadoNuevo = $db->fetchOne("SELECT id FROM estados_ticket WHERE LOWER(nombre) IN ('nuevo','recibido') ORDER BY id LIMIT 1");
        $estadoInicial = $esBorrador ? 4 : ($tecnicoId ? 2 : (int) ($estadoNuevo['id'] ?? 1));
        $identificador = bin2hex(random_bytes(8));
        $pdo->beginTransaction();
        try {
            $db->execute(
                "INSERT INTO tickets
                    (id_usuario, id_categoria_ticket, asunto, descripcion_ticket,
                     id_prioridad, id_estado, identificador, id_tecnico, estado)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)",
                [$solicitanteId, $categoriaId, $asunto, $descripcion, $prioridadId, $estadoInicial, $identificador, $tecnicoId]
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
            foreach ($archivos as $archivo) {   // misma carpeta y tabla que el chat (helpers/tickets.php)
                $ruta = ticket_mover_archivo($ticketId, $archivo);
                $guardados[] = dirname(__DIR__) . '/' . $ruta;
                ticket_registrar_adjunto($db, $ticketId, $ruta, $archivo['original'], $archivo['tamano'], $usuarioId);
            }
            $pdo->commit();
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            foreach ($guardados as $archivoGuardado) @unlink($archivoGuardado);
            throw $ex;
        }
        ticket_registrar_cambio($db, $ticketId, $usuarioId, 'crear');
        if (!$esBorrador) {
            (new AvisosTicket($db))->creado($ticketId, $usuarioId);   // campanita
        }
        (new BitacoraTicket($db))->registrarSistema($ticketId, $usuarioId, $esBorrador
            ? 'Ticket guardado como borrador.'
            : ($tecnicoId ? 'Ticket creado y asignado automáticamente a ' . bitacora_nombre($db, $tecnicoId) . '.' : 'Ticket creado; queda pendiente de asignación.'));
        responder_json([
            'ok' => true,
            'ticket_id' => $ticketId,
            'borrador' => $esBorrador,
            'tecnico' => $tecnicoId ? bitacora_nombre($db, $tecnicoId) : null,
            'mensaje' => $esBorrador
                ? 'Borrador guardado correctamente.'
                : ($tecnicoId ? 'Ticket creado y asignado automáticamente.' : 'Ticket creado; queda pendiente de asignación.'),
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

    /*
     * COMENZAR: solo el técnico asignado, con el ticket Recibido o Asignado.
     * Define la prioridad y la fecha estimada de término (como el modal antiguo).
     * Lógica: ticket/logica/LOGICA_COMENZAR_TICKET.txt
     */
    if ($accion === 'comenzar') {
        if ((int) ($ticket['id_tecnico'] ?? 0) !== $usuarioId) {
            responder_json(['ok' => false, 'error' => 'Solo el técnico asignado puede comenzar el ticket.'], 403);
        }
        if (!in_array((int) $ticket['id_estado'], [1, 2], true)) {
            responder_json(['ok' => false, 'error' => 'El ticket ya fue comenzado.'], 409);
        }
        $prioridad = (int) ($datos['prioridad'] ?? 0);
        if (!isset(ticket_prioridades()[$prioridad])) {
            responder_json(['ok' => false, 'error' => 'Elige la prioridad del ticket.'], 422);
        }
        $fechaTxt = (string) ($datos['fecha_estimada'] ?? '');
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $fechaTxt);
        $hoy = new DateTimeImmutable('today');
        if (!$fecha || $fecha->format('Y-m-d') !== $fechaTxt) {
            responder_json(['ok' => false, 'error' => 'Indica la fecha estimada de término.'], 422);
        }
        if ($fecha < $hoy || $fecha > $hoy->modify('+1 year')) {
            responder_json(['ok' => false, 'error' => 'La fecha estimada debe ser desde hoy y dentro de un año.'], 422);
        }
        $dias = (int) $hoy->diff($fecha)->days;
        $pdo = $db->getPDO();
        $pdo->beginTransaction();
        try {
            $db->execute('UPDATE tickets SET id_estado = 3, id_prioridad = ? WHERE id_ticket = ?', [$prioridad, $ticketId]);
            if (!$db->fetchOne('SELECT 1 FROM proceso_tickets WHERE id_ticket = ?', [$ticketId])) {
                $db->execute('INSERT INTO proceso_tickets (id_ticket) VALUES (?)', [$ticketId]);
            }
            $db->execute(
                'UPDATE proceso_tickets
                    SET fecha_estimada_admin = ?, dias_estimada_admin = ?,
                        fecha_comienzo_ticket = CURDATE(), hora_comienzo_ticket = CURTIME(),
                        fecha_asignacion_tecnico = COALESCE(fecha_asignacion_tecnico, CURDATE()),
                        hora_asignacion_tecnico = COALESCE(hora_asignacion_tecnico, CURTIME())
                  WHERE id_ticket = ?',
                [$fecha->format('Y-m-d'), $dias, $ticketId]
            );
            $pdo->commit();
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }
        ticket_registrar_cambio($db, $ticketId, $usuarioId, 'comenzar', 'id_estado', (string) $ticket['id_estado'], '3');
        $plazo = $dias === 0 ? 'hoy' : ($dias === 1 ? '1 día' : $dias . ' días');
        (new BitacoraTicket($db))->registrarSistema($ticketId, $usuarioId, bitacora_nombre($db, $usuarioId)
            . ' comenzó el ticket · prioridad ' . ticket_prioridad_nombre($prioridad)
            . ' · fecha estimada ' . $fecha->format('d/m/Y') . ' (' . $plazo . ').');
        (new AvisosTicket($db))->comenzado($ticketId, $usuarioId, ticket_prioridad_nombre($prioridad), $fecha->format('d/m/Y'));
        responder_json(['ok' => true, 'mensaje' => 'Ticket comenzado.']);
    }

    if ($accion === 'actualizar_estado') {
        $estadoId = ticket_estado_id($datos['estado'] ?? '');
        if ($estadoId <= 0) {
            responder_json(['ok' => false, 'error' => 'Estado no válido.'], 422);
        }
        // Pasar a "En proceso" desde Recibido/Asignado es COMENZAR: pide prioridad y fecha estimada
        if ($estadoId === 3 && in_array((int) $ticket['id_estado'], [1, 2], true)) {
            responder_json(['ok' => false, 'comenzar' => true, 'error' => 'Para pasarlo a "En proceso" usa «Comenzar ticket» e indica la prioridad y la fecha estimada.'], 422);
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
        (new AvisosTicket($db))->estado($ticketId, $usuarioId, (int) $ticket['id_estado'], $estadoId);
        if ($estadoId !== (int) $ticket['id_estado']) {
            (new BitacoraTicket($db))->registrarSistema($ticketId, $usuarioId, bitacora_nombre($db, $usuarioId) . ' cambió el estado: '
                . bitacora_estado((int) $ticket['id_estado']) . ' → ' . bitacora_estado($estadoId) . '.');
        }
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
        if ($tecnicoId > 0 && $tecnicoId !== (int) ($ticket['id_tecnico'] ?? 0)) {
            (new AvisosTicket($db))->asignado($ticketId, $usuarioId);
        }
        ticket_registrar_cambio($db, $ticketId, $usuarioId, 'asignar', 'id_tecnico', (string) ($ticket['id_tecnico'] ?? ''), $tecnicoId > 0 ? (string) $tecnicoId : null);
        if ($tecnicoId !== (int) ($ticket['id_tecnico'] ?? 0)) {
            (new BitacoraTicket($db))->registrarSistema($ticketId, $usuarioId, $tecnicoId
                ? bitacora_nombre($db, $usuarioId) . ' asignó el ticket a ' . bitacora_nombre($db, $tecnicoId) . '.'
                : bitacora_nombre($db, $usuarioId) . ' quitó la asignación del técnico.');
        }
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
        $avisos = new AvisosTicket($db);
        if ($tecnicoId > 0 && $tecnicoId !== (int) ($ticket['id_tecnico'] ?? 0)) {
            $avisos->asignado($ticketId, $usuarioId);
        }
        $avisos->estado($ticketId, $usuarioId, (int) $ticket['id_estado'], $estadoId);
        $cambios = [];
        if ($tecnicoId !== (int) ($ticket['id_tecnico'] ?? 0)) {
            $cambios[] = $tecnicoId ? 'asignó el ticket a ' . bitacora_nombre($db, $tecnicoId) : 'quitó la asignación del técnico';
        }
        if ($estadoId !== (int) $ticket['id_estado']) {
            $cambios[] = 'cambió el estado: ' . bitacora_estado((int) $ticket['id_estado']) . ' → ' . bitacora_estado($estadoId);
        }
        if ($cambios) {
            (new BitacoraTicket($db))->registrarSistema($ticketId, $usuarioId, bitacora_nombre($db, $usuarioId) . ' ' . implode(' y ', $cambios) . '.');
        }
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
