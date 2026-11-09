<?php
/**
 * Campanita de la barra superior (js/notificaciones.js).
 *   GET  ?accion=contar            → {ok, sin_leer}
 *   GET  ?accion=listar            → {ok, sin_leer, instalada, avisos: [...]}
 *   POST accion=leer, id, csrf     → marca uno como leído
 *   POST accion=leer_todas, csrf   → marca todos
 * Lógica: ticket/logica/LOGICA_NOTIFICACIONES.txt
 */
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../clases/Notificaciones.php';
Sesion::requerir();

$usuarioId = (int) ($_SESSION['id'] ?? 0);
try {
    $n = new Notificaciones(Conexion::getInstance('sistema_panel_central'));
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $d = entrada_ajax();
        $token = (string) ($_SESSION['csrf_notificaciones'] ?? '');
        if ($token === '' || !hash_equals($token, (string) ($d['csrf'] ?? ''))) {
            responder_json(['ok' => false, 'error' => 'La sesión expiró. Recarga la página.'], 419);
        }
        $accion = (string) ($d['accion'] ?? '');
        if ($accion === 'leer') {
            $n->leer($usuarioId, (int) ($d['id'] ?? 0));
        } elseif ($accion === 'leer_todas') {
            $n->leerTodas($usuarioId);
        } else {
            responder_json(['ok' => false, 'error' => 'Acción no válida.'], 400);
        }
        responder_json(['ok' => true, 'sin_leer' => $n->contar($usuarioId)]);
    }
    if (($_GET['accion'] ?? 'contar') === 'listar') {
        responder_json(['ok' => true, 'instalada' => $n->instalada(), 'sin_leer' => $n->contar($usuarioId), 'avisos' => $n->listar($usuarioId)]);
    }
    responder_json(['ok' => true, 'sin_leer' => $n->contar($usuarioId)]);
} catch (Throwable $e) {
    error_log('[notificaciones] ' . $e->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible consultar las notificaciones.'], 500);
}
