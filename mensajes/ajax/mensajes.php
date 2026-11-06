<?php
/**
 * Acciones de mensajería (JSON).
 *   GET  accion=conversacion&id=…     → mensajes de una conversación
 *   GET  accion=destinatarios         → colaboradores activos
 *   GET  accion=resumen               → contadores
 *   POST accion=responder|nuevo|archivar|desarchivar|eliminar (+ csrf)
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function msg_json(array $r, int $codigo = 200): never
{
    http_response_code($codigo);
    echo json_encode($r, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!$mensajeriaInstalada || !$mensajeria) {
    msg_json(['ok' => false, 'mensaje' => 'La mensajería no está instalada. Ejecuta sql/mensajes_instalacion.sql.'], 503);
}

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$accion = (string) ($metodo === 'POST' ? ($_POST['accion'] ?? '') : ($_GET['accion'] ?? ''));

try {
    if ($metodo === 'GET') {
        switch ($accion) {
            case 'conversacion':
                $c = $mensajeria->conversacion((int) ($_GET['id'] ?? 0));
                if (!$c) {
                    msg_json(['ok' => false, 'mensaje' => 'No se encontró la conversación.'], 404);
                }
                msg_json(['ok' => true, 'data' => $c, 'resumen' => $mensajeria->resumen()]);
            case 'destinatarios':
                msg_json(['ok' => true, 'data' => $mensajeria->destinatarios()]);
            case 'resumen':
                msg_json(['ok' => true, 'data' => $mensajeria->resumen()]);
        }
        msg_json(['ok' => false, 'mensaje' => 'Acción no válida.'], 400);
    }

    if ($metodo !== 'POST') {
        msg_json(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
    }
    if (!hash_equals((string) ($_SESSION['csrf_mensajes'] ?? ''), (string) ($_POST['csrf'] ?? ''))) {
        msg_json(['ok' => false, 'mensaje' => 'La sesión expiró. Recarga la página.'], 419);
    }

    switch ($accion) {
        case 'responder':
            $mensajeria->responder((int) ($_POST['id'] ?? 0), (string) ($_POST['mensaje'] ?? ''));
            msg_json(['ok' => true, 'mensaje' => 'Respuesta enviada.']);
        case 'nuevo':
            $n = $mensajeria->nuevo((array) ($_POST['destinatarios'] ?? []), (string) ($_POST['mensaje'] ?? ''), ($_POST['urgente'] ?? '') === '1');
            msg_json(['ok' => true, 'mensaje' => $n === 1 ? 'Mensaje enviado.' : "Mensaje enviado a {$n} colaboradores."]);
        case 'archivar':
        case 'desarchivar':
        case 'eliminar':
            $mensajeria->moverACarpeta((int) ($_POST['id'] ?? 0), $accion);
            $textos = ['archivar' => 'Conversación archivada.', 'desarchivar' => 'Conversación devuelta a la bandeja.', 'eliminar' => 'Conversación eliminada.'];
            msg_json(['ok' => true, 'mensaje' => $textos[$accion]]);
    }
    msg_json(['ok' => false, 'mensaje' => 'Acción no válida.'], 400);
} catch (PDOException $e) {
    error_log('[mensajes] BD: ' . $e->getMessage());
    msg_json(['ok' => false, 'mensaje' => 'No fue posible completar la acción. Intenta nuevamente.'], 500);
} catch (RuntimeException $e) {
    msg_json(['ok' => false, 'mensaje' => $e->getMessage()], 422);
} catch (Throwable $e) {
    error_log('[mensajes] ' . $e->getMessage());
    msg_json(['ok' => false, 'mensaje' => 'No fue posible completar la acción. Intenta nuevamente.'], 500);
}
