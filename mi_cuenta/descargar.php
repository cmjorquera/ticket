<?php
/**
 * Descarga (o abre con ver=1) una liquidación o un documento de la persona
 * que inició sesión. El id solo sirve si aparece en SU propia lista.
 *   descargar.php?tipo=liquidacion&id=…[&ver=1]
 *   descargar.php?tipo=documento&id=…[&ver=1]
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/validar_sesion.php';
require_once __DIR__ . '/class/MiCuenta.php';
require_once __DIR__ . '/class/Rrhh.php';

$tipo = (string) ($_GET['tipo'] ?? '');
$idArchivo = (string) ($_GET['id'] ?? '');
if (!in_array($tipo, ['liquidacion', 'documento'], true) || !preg_match('/^[\w\-.]{1,80}$/', $idArchivo)) {
    http_response_code(400);
    exit('Solicitud no válida.');
}
try {
    $persona = (new MiCuenta(Conexion::getInstance('sistema_panel_central')))->persona((int) $_SESSION['id']);
    $fuente = Rrhh::fuente();
    $archivo = $tipo === 'liquidacion' ? $fuente->liquidacionPdf($persona, $idArchivo) : $fuente->documentoArchivo($persona, $idArchivo);
} catch (Throwable $e) {
    error_log('[mi_cuenta] descarga: ' . $e->getMessage());
    http_response_code(502);
    exit($e instanceof RuntimeException ? $e->getMessage() : 'No fue posible obtener el archivo.');
}
if (!$archivo) {
    http_response_code(404);
    exit('Archivo no encontrado.');
}
$nombre = preg_replace('/[^\w.\-]+/', '_', (string) $archivo['nombre']);
$ver = !empty($_GET['ver']) && $archivo['mime'] === 'application/pdf';
header('Content-Type: ' . $archivo['mime']);
header('Content-Disposition: ' . ($ver ? 'inline' : 'attachment') . '; filename="' . $nombre . '"');
header('Content-Length: ' . strlen($archivo['contenido']));
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
echo $archivo['contenido'];
