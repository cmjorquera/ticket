<?php
/**
 * Descarga / vista de un adjunto de ticket, solo para quien puede ver el ticket.
 * GET a={id de archivos_adjuntos_ticket}  o  m={id del mensaje en ticket_conversaciones}
 *     &descargar=1 fuerza la descarga (si no, imágenes y PDF se abren en el navegador).
 * Lógica: ticket/logica/LOGICA_MENSAJERIA.txt
 */
declare(strict_types=1);

require_once __DIR__ . '/../clases/Session.php';
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../helpers/tickets.php';
require_once __DIR__ . '/../clases/Tickets/ConversacionTicket.php';

function archivo_error(int $codigo, string $texto): never
{
    http_response_code($codigo);
    header('Content-Type: text/plain; charset=utf-8');
    echo $texto;
    exit;
}

Session::iniciar();
$usuarioId = (int) ($_SESSION['id'] ?? 0);
if ($usuarioId <= 0) {
    archivo_error(401, 'Tu sesión expiró. Vuelve a ingresar.');
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $conv = new ConversacionTicket($db);
    $info = null;
    if (($m = (int) ($_GET['m'] ?? 0)) > 0) {
        $info = $conv->rutaAdjunto($m);
    } elseif (($a = (int) ($_GET['a'] ?? 0)) > 0) {
        $c = ticket_columnas_tabla('archivos_adjuntos_ticket', $db);
        $campoRuta = isset($c['adjunto']) ? 'adjunto' : 'ruta_archivo';
        $campoNombre = isset($c['nombre_archivo']) ? 'nombre_archivo' : "''";
        $f = $db->fetchOne("SELECT id_ticket, {$campoRuta} AS ruta, {$campoNombre} AS nombre FROM archivos_adjuntos_ticket WHERE id = ?", [$a]);
        $info = $f ? ['id_ticket' => (int) $f['id_ticket'], 'ruta' => (string) $f['ruta'], 'nombre' => (string) $f['nombre']] : null;
    }
    if (!$info) {
        archivo_error(404, 'El archivo no existe.');
    }
    $ticket = ticket_buscar_para_acceso($info['id_ticket'], $db);
    if (!$ticket || !puede_ver_ticket($usuarioId, $ticket, $db)) {
        archivo_error(403, 'No tienes permiso para ver este archivo.');
    }
    $fisica = ticket_ruta_adjunto_segura($info['ruta']);
    if ($fisica === null) {
        archivo_error(404, 'El archivo ya no está en el servidor.');
    }
} catch (Throwable $ex) {
    error_log('[ticket_archivo] ' . $ex->getMessage());
    archivo_error(500, 'No fue posible abrir el archivo.');
}

$nombre = $info['nombre'] !== '' ? $info['nombre'] : basename($fisica);
$mime = (string) (new finfo(FILEINFO_MIME_TYPE))->file($fisica);
$enLinea = !isset($_GET['descargar']) && in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'], true);
$ascii = preg_replace('/[^A-Za-z0-9._ -]/', '_', $nombre);

header('Content-Type: ' . ($enLinea ? $mime : 'application/octet-stream'));
header('Content-Length: ' . filesize($fisica));
header('Content-Disposition: ' . ($enLinea ? 'inline' : 'attachment') . '; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode($nombre));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=300');
if ($mime !== 'application/pdf') {   // el visor de PDF del navegador no funciona con sandbox
    header("Content-Security-Policy: default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox");
}
readfile($fisica);
