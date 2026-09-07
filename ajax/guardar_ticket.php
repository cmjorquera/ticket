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

        $db->execute(
            "INSERT INTO tickets
                (id_usuario, id_categoria, id_colegio, asunto, descripcion, prioridad, estado, fecha_creacion, id_tecnico_asignado)
             VALUES (?, ?, ?, ?, ?, ?, 'nuevo', NOW(), ?)",
            [$usuarioId, $categoriaId, $colegioId, $asunto, $descripcion, $prioridad, $tecnicoId]
        );
        responder_json([
            'ok' => true,
            'ticket_id' => (int) $db->lastInsertId(),
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

