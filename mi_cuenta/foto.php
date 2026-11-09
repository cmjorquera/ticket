<?php
/** Foto de la persona que inició sesión (usuarios.foto). 404 si no tiene. */
declare(strict_types=1);

require_once dirname(__DIR__) . '/clases/Session.php';
require_once __DIR__ . '/class/MiCuenta.php';
Session::iniciar();

$id = (int) ($_SESSION['id'] ?? 0);
$foto = $id > 0 ? (new MiCuenta(Conexion::getInstance('sistema_panel_central')))->foto($id) : null;
if ($foto === null) {
    http_response_code(404);
    exit;
}
$info = @getimagesizefromstring($foto);
header('Content-Type: ' . ($info['mime'] ?? 'image/jpeg'));
header('Cache-Control: private, max-age=86400');
header('X-Content-Type-Options: nosniff');
echo $foto;
