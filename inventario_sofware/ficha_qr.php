<?php
require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/class/InventarioSoftware.php';

function ficha_qr_h($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$inventario = new InventarioSoftware();
$tipo = ($_GET['tipo'] ?? '') === 'sitio' ? 'sitio' : 'software';
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>QR no valido</title></head><body><p>Registro no valido.</p></body></html>';
    exit;
}

if ($tipo === 'sitio') {
    $registro = $inventario->obtenerSitioWeb($id);
} else {
    $registro = $inventario->obtenerSoftwareCompleto($id);
}

if (!$registro) {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>No encontrado</title></head><body><p>No se encontro el registro solicitado.</p></body></html>';
    exit;
}

$fechaInicioLicencia = !empty($registro['fecha_inicio_licencia']) && $registro['fecha_inicio_licencia'] !== '0000-00-00'
    ? date('d-m-Y', strtotime($registro['fecha_inicio_licencia']))
    : '-';
$fechaFinLicencia = !empty($registro['fecha_fin_licencia']) && $registro['fecha_fin_licencia'] !== '0000-00-00'
    ? date('d-m-Y', strtotime($registro['fecha_fin_licencia']))
    : '-';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tipo === 'sitio' ? 'Ficha QR sitio' : 'Ficha QR software' ?></title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: linear-gradient(180deg, #eef4fb 0%, #f8fbff 100%); color: #17324d; }
        .wrap { max-width: 860px; margin: 0 auto; padding: 28px 18px 40px; }
        .card { background: rgba(255,255,255,.96); border: 1px solid #d8e4ef; border-radius: 22px; box-shadow: 0 18px 45px rgba(15,76,129,.08); overflow: hidden; }
        .hero { padding: 24px; background: radial-gradient(circle at top right, rgba(20,184,166,.18), transparent 32%), linear-gradient(135deg, #ffffff, #eff6ff 48%, #f8fafc); border-bottom: 1px solid #d8e4ef; }
        .kicker { display: inline-block; padding: 6px 12px; border-radius: 999px; background: rgba(15,76,129,.08); color: #0f4c81; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        h1 { margin: 12px 0 8px; font-size: 34px; line-height: 1.1; }
        .sub { color: #5f7388; font-size: 18px; }
        .content { padding: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; }
        .item { border: 1px solid rgba(15,76,129,.10); border-radius: 16px; background: #fbfdff; padding: 16px; }
        .item span { display: block; font-size: 13px; color: #5f7388; margin-bottom: 8px; }
        .item strong, .item p { margin: 0; font-size: 24px; line-height: 1.25; }
        .item p { font-size: 18px; font-weight: 700; }
        .footer { padding: 0 24px 24px; color: #5f7388; font-size: 14px; }
        a { color: #0d6efd; word-break: break-word; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="hero">
                <span class="kicker"><?= $tipo === 'sitio' ? 'Sitio web / app / cliente' : 'Software y licencias' ?></span>
                <h1><?= ficha_qr_h($tipo === 'sitio' ? $registro['nombre_sitio'] : $registro['nombre_software']) ?></h1>
                <div class="sub"><?= ficha_qr_h($registro['nom_colegio'] ?? '-') ?></div>
            </div>

            <?php if ($tipo === 'sitio'): ?>
                <div class="content">
                    <div class="item"><span>Tipo</span><p><?= ficha_qr_h($registro['tipo_sitio']) ?></p></div>
                    <div class="item"><span>Estado</span><p><?= ficha_qr_h($registro['estado_sitio']) ?></p></div>
                    <div class="item"><span>Responsable</span><p><?= ficha_qr_h($registro['responsable'] ?: 'Sin asignar') ?></p></div>
                    <div class="item"><span>Proveedor</span><p><?= ficha_qr_h($registro['proveedor_hosting'] ?: '-') ?></p></div>
                    <div class="item" style="grid-column: 1 / -1;"><span>URL</span><p><?= $registro['url_sitio'] ? '<a href="' . ficha_qr_h($registro['url_sitio']) . '" target="_blank" rel="noopener noreferrer">' . ficha_qr_h($registro['url_sitio']) . '</a>' : '-' ?></p></div>
                    <div class="item" style="grid-column: 1 / -1;"><span>Observaciones</span><p><?= nl2br(ficha_qr_h($registro['observaciones'] ?: 'Sin observaciones.')) ?></p></div>
                </div>
            <?php else: ?>
                <div class="content">
                    <div class="item"><span>Version</span><p><?= ficha_qr_h($registro['version_software'] ?: '-') ?></p></div>
                    <div class="item"><span>Licencias</span><p><?= (int)$registro['cantidad_licencias'] ?></p></div>
                    <div class="item"><span>Licenciamiento</span><p><?= ficha_qr_h($registro['tipo_licenciamiento']) ?></p></div>
                    <div class="item"><span>Inicio licencia</span><p><?= ficha_qr_h($fechaInicioLicencia) ?></p></div>
                    <div class="item"><span>Fin licencia</span><p><?= ficha_qr_h($fechaFinLicencia) ?></p></div>
                    <div class="item"><span>Pagado por</span><p><?= ficha_qr_h($registro['pagado_por']) ?></p></div>
                    <div class="item"><span>Costo</span><p><?= ficha_qr_h($registro['moneda']) ?> <?= number_format((float)$registro['costo'], 2, ',', '.') ?></p></div>
                    <div class="item"><span>Responsable</span><p><?= ficha_qr_h($registro['responsable'] ?: 'Sin asignar') ?></p></div>
                    <div class="item"><span>Proveedor</span><p><?= ficha_qr_h($registro['proveedor'] ?: '-') ?></p></div>
                    <div class="item" style="grid-column: 1 / -1;"><span>URL o referencia</span><p><?= $registro['url_referencia'] ? '<a href="' . ficha_qr_h($registro['url_referencia']) . '" target="_blank" rel="noopener noreferrer">' . ficha_qr_h($registro['url_referencia']) . '</a>' : '-' ?></p></div>
                    <div class="item" style="grid-column: 1 / -1;"><span>Tipos de usuario</span><p><?= !empty($registro['tipos_usuario']) ? ficha_qr_h(implode(', ', array_map(static function ($fila) { return $fila['nombre']; }, $registro['tipos_usuario']))) : 'Sin tipos de usuario asociados.' ?></p></div>
                    <div class="item" style="grid-column: 1 / -1;"><span>Datos sensibles</span><p><?= !empty($registro['datos_sensibles']) ? ficha_qr_h(implode(', ', array_map(static function ($fila) { return $fila['nombre']; }, $registro['datos_sensibles']))) : 'Sin datos sensibles asociados.' ?></p></div>
                    <div class="item" style="grid-column: 1 / -1;"><span>Observaciones</span><p><?= nl2br(ficha_qr_h($registro['observaciones'] ?: 'Sin observaciones.')) ?></p></div>
                </div>
            <?php endif; ?>

            <div class="footer">
                Registro consultado desde inventario de software.
            </div>
        </div>
    </div>
</body>
</html>
