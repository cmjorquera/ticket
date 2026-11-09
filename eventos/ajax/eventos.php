<?php
/**
 * API del calendario de eventos (eventos/js/eventos.js).
 *   GET  accion=listar&inicio=Y-m-d&fin=Y-m-d[&colegio&tipo&mios=1&cancelados=1]  → eventos (FullCalendar)
 *   GET  accion=detalle&id=N                                                      → evento con invitados e historial
 *   GET  accion=usuarios&q=texto                                                  → buscador de personas
 *   POST (JSON, con csrf) accion = guardar | mover | cancelar | eliminar | responder
 * Lógica: eventos/logica/LOGICA.txt
 */
declare(strict_types=1);

require_once __DIR__ . '/../../ajax/_bootstrap.php';
require_once __DIR__ . '/../class/Eventos.php';
require_once __DIR__ . '/../class/CorreoEvento.php';
Sesion::requerir();

$actor = (int) ($_SESSION['id'] ?? 0);
$db = Conexion::getInstance('sistema_panel_central');
$ev = new Eventos($db);

/** Texto para el usuario según cómo salieron los correos. */
function ev_resumen_correo(array $r): string
{
    if ($r['total'] === 0) {
        return '';
    }
    if ($r['enviados'] === $r['total']) {
        return ' Se envió la invitación por correo a ' . $r['total'] . ($r['total'] === 1 ? ' persona.' : ' personas.');
    }
    return ' Correos: ' . $r['enviados'] . ' de ' . $r['total'] . ' enviados' . ($r['motivo'] !== '' ? ' (' . $r['motivo'] . ')' : '') . '.';
}

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
        $accion = (string) ($_GET['accion'] ?? 'listar');
        if ($accion === 'listar') {
            $ini = substr((string) ($_GET['inicio'] ?? ''), 0, 10);
            $fin = substr((string) ($_GET['fin'] ?? ''), 0, 10);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ini) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fin)) {
                responder_json(['ok' => false, 'error' => 'Rango de fechas no válido.'], 422);
            }
            responder_json(['ok' => true, 'eventos' => $ev->listar($actor, $ini, $fin, [
                'colegio' => (int) ($_GET['colegio'] ?? 0), 'tipo' => (string) ($_GET['tipo'] ?? ''),
                'mios' => ($_GET['mios'] ?? '') === '1', 'cancelados' => ($_GET['cancelados'] ?? '') === '1',
            ])]);
        }
        if ($accion === 'detalle') {
            $e = $ev->obtener((int) ($_GET['id'] ?? 0), $actor);
            if (!$e) {
                responder_json(['ok' => false, 'error' => 'El evento no existe o no tienes acceso.'], 404);
            }
            unset($e['responsable_email']);
            responder_json(['ok' => true, 'evento' => $e, 'cuando' => CorreoEvento::cuando($e)]);
        }
        if ($accion === 'usuarios') {
            responder_json(['ok' => true, 'usuarios' => $ev->buscarUsuarios((string) ($_GET['q'] ?? ''))]);
        }
        responder_json(['ok' => false, 'error' => 'Acción no válida.'], 400);
    }

    $d = entrada_ajax();
    $token = (string) ($_SESSION['csrf_eventos'] ?? '');
    if ($token === '' || !hash_equals($token, (string) ($d['csrf'] ?? ''))) {
        responder_json(['ok' => false, 'error' => 'La sesión expiró. Recarga la página.'], 419);
    }
    $accion = (string) ($d['accion'] ?? '');
    $id = (int) ($d['id'] ?? 0);
    $correo = new CorreoEvento($db);

    if ($accion === 'guardar' || $accion === 'mover') {
        if ($accion === 'guardar') {
            try {
                $f = $ev->normalizar($d, $actor);
            } catch (InvalidArgumentException $e) {
                responder_json(['ok' => false, 'error' => $e->getMessage()], 422);
            }
            $r = $ev->guardar($f, $actor, $id ?: null);
        } else {
            $r = $ev->mover($id, $actor, (string) ($d['inicio'] ?? ''), (string) ($d['fin'] ?? ''), !empty($d['todo_el_dia']));
        }
        // Correos: invitación a todos si es nuevo; si se editó, "actualizado" a los que ya estaban,
        // invitación a los recién agregados y cancelación a los que se quitaron.
        if ($r['nuevo']) {
            $res = $correo->notificar('creado', $r['id'], $actor);
        } else {
            $nuevos = array_column($r['invitados_nuevos'], 'email');
            $existentes = array_values(array_filter($ev->destinatarios($r['id']), static fn ($x) => !in_array($x['email'], $nuevos, true)));
            $res = ['enviados' => 0, 'total' => 0, 'motivo' => ''];
            foreach ([
                $r['cambios'] ? $correo->notificar('actualizado', $r['id'], $actor, $r['cambios'], $existentes) : null,
                $r['invitados_nuevos'] ? $correo->notificar('creado', $r['id'], $actor, [], $r['invitados_nuevos']) : null,
                $r['invitados_quitados'] ? $correo->notificar('cancelado', $r['id'], $actor, [], array_map(static fn ($q) => ['id_usuario' => $q['id_usuario'], 'email' => $q['email'], 'nombre' => $q['nombre'], 'token' => ''], $r['invitados_quitados'])) : null,
            ] as $x) {
                if ($x) {
                    $res['enviados'] += $x['enviados'];
                    $res['total'] += $x['total'];
                    $res['motivo'] = $res['motivo'] ?: $x['motivo'];
                }
            }
        }
        responder_json(['ok' => true, 'id' => $r['id'], 'mensaje' => ($r['nuevo'] ? 'Evento creado.' : 'Evento actualizado.') . ev_resumen_correo($res)]);
    }

    if ($accion === 'cancelar') {
        $ev->cancelar($id, $actor, (string) ($d['motivo'] ?? ''));
        $res = $correo->notificar('cancelado', $id, $actor);
        responder_json(['ok' => true, 'mensaje' => 'Evento cancelado.' . ev_resumen_correo($res)]);
    }

    if ($accion === 'eliminar') {
        $ev->eliminar($id, $actor);
        responder_json(['ok' => true, 'mensaje' => 'Evento eliminado.']);
    }

    if ($accion === 'responder') {
        if (!$ev->responder($id, $actor, (string) ($d['respuesta'] ?? ''))) {
            responder_json(['ok' => false, 'error' => 'No estás invitado a este evento.'], 404);
        }
        responder_json(['ok' => true, 'mensaje' => 'Respuesta guardada.']);
    }

    responder_json(['ok' => false, 'error' => 'Acción no válida.'], 400);
} catch (InvalidArgumentException $e) {
    responder_json(['ok' => false, 'error' => $e->getMessage()], 422);
} catch (RuntimeException $e) {
    responder_json(['ok' => false, 'error' => $e->getMessage()], 403);
} catch (Throwable $e) {
    error_log('[eventos] ' . $e->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible completar la acción.'], 500);
}
