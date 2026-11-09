<?php
/**
 * Bitácora interna del ticket (técnico ↔ administración). El solicitante no tiene acceso.
 * POST: csrf, ticket_id, vista (tecnico|admin), accion = listar | guardar (texto) | no_leidos (rol, ids[])
 * Lógica: ticket/logica/LOGICA_BITACORA.txt
 */
declare(strict_types=1);

require_once __DIR__ . '/_ticket_chat.php';
require_once __DIR__ . '/../clases/Tickets/BitacoraTicket.php';

$datos = entrada_ajax();
try {
    [$db, , $usuarioId] = chat_inicio($datos);
    $bitacora = new BitacoraTicket($db);
    $accion = (string) ($datos['accion'] ?? 'listar');

    if ($accion === 'no_leidos') {
        $conteo = $bitacora->noLeidos($usuarioId, (string) ($datos['rol'] ?? ''), (array) ($datos['ids'] ?? []));
        responder_json(['ok' => true, 'no_leidos' => (object) $conteo]);
    }

    $ticket = ticket_buscar_para_acceso((int) ($datos['ticket_id'] ?? 0), $db);
    if (!$ticket) {
        responder_json(['ok' => false, 'error' => 'Ticket no encontrado.'], 404);
    }
    $rol = $bitacora->rolPara($usuarioId, $ticket, (string) ($datos['vista'] ?? ''));
    if ($rol === null) {
        responder_json(['ok' => false, 'error' => 'La bitácora es solo para el técnico y administración.'], 403);
    }
    if (!$bitacora->disponible()) {
        responder_json(['ok' => false, 'error' => 'La bitácora todavía no está instalada en la base de datos (ticket/sql/02_bitacora.sql).'], 503);
    }
    $ticketId = (int) $ticket['id_ticket'];
    $cerrado = ticket_estado_sin_escritura((int) ($ticket['id_estado'] ?? 0));

    if ($accion === 'guardar') {
        $texto = trim(str_replace("\r\n", "\n", (string) ($datos['texto'] ?? '')));
        $largo = mb_strlen($texto, 'UTF-8');
        if ($largo < 1 || $largo > BitacoraTicket::LARGO_MAXIMO) {
            responder_json(['ok' => false, 'error' => 'Escribe el avance (máximo ' . BitacoraTicket::LARGO_MAXIMO . ' caracteres).'], 422);
        }
        if ($cerrado) {
            responder_json(['ok' => false, 'error' => 'El ticket está terminado: la bitácora queda como registro.'], 409);
        }
        $id = $bitacora->registrar($ticketId, $usuarioId, $rol, $texto);
        responder_json(['ok' => true, 'id' => $id, 'mensaje' => 'Avance registrado.'], 201);
    }

    if ((string) ($datos['marcar'] ?? '1') === '1') {
        $bitacora->marcarLeidos($ticketId, $rol, $usuarioId);
    }
    responder_json([
        'ok' => true,
        'rol' => $rol,
        'puede_escribir' => !$cerrado,
        'entradas' => $bitacora->listar($ticketId, $usuarioId),
    ]);
} catch (Throwable $ex) {
    error_log('[ticket_bitacora] ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible procesar la bitácora.'], 500);
}
