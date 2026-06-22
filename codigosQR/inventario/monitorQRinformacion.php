<?php
require_once __DIR__ . '/../../class/conexion.php';
header('Content-Type: text/html; charset=UTF-8');

$idMonitor = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_GET['id_monitor'] ?? 0);

function qr_mon_h($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function qr_mon_table_exists(MySQL $db, $table)
{
    $table = $db->escape_string($table);
    $rs = $db->consulta("SELECT COUNT(*) total FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table'");
    $row = $db->fetch_assoc($rs);
    return (int)($row['total'] ?? 0) > 0;
}

function qr_mon_column_exists(MySQL $db, $table, $column)
{
    $table = $db->escape_string($table);
    $column = $db->escape_string($column);
    $rs = $db->consulta("SELECT COUNT(*) total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND COLUMN_NAME = '$column'");
    $row = $db->fetch_assoc($rs);
    return (int)($row['total'] ?? 0) > 0;
}

function qr_mon_estado_nombre($idEstado)
{
    switch ((int)$idEstado) {
        case 1: return 'Activo';
        case 2: return 'Bodega';
        case 3: return 'Reparacion';
        case 4: return 'Baja';
        case 5: return 'Prestado';
        default: return 'Sin estado';
    }
}

function qr_mon_render_page($titulo, $subtitulo, array $secciones, $estado = '')
{
    ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= qr_mon_h($titulo) ?></title>
    <style>
        :root{--azul:#0f4f8a;--texto:#102a43;--muted:#65758b;--borde:#dbe6f1;--fondo:#eef5fb}
        *{box-sizing:border-box}
        body{margin:0;font-family:Arial,Helvetica,sans-serif;background:var(--fondo);color:var(--texto)}
        .page{max-width:920px;margin:0 auto;padding:20px 14px 32px}
        .head{background:linear-gradient(135deg,#0f4f8a,#1b78b9);color:#fff;border-radius:16px;padding:20px;box-shadow:0 14px 30px rgba(15,79,138,.22)}
        .head h1{margin:0 0 8px;font-size:28px;line-height:1.15}
        .head p{margin:0;opacity:.9;font-size:15px}
        .badge{display:inline-block;margin-top:14px;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.35);font-weight:700}
        .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:16px}
        .card{background:#fff;border:1px solid var(--borde);border-radius:14px;padding:16px;box-shadow:0 8px 22px rgba(16,42,67,.08)}
        .card h2{font-size:18px;margin:0 0 12px}
        .item{display:flex;justify-content:space-between;gap:16px;border-top:1px solid #edf2f7;padding:10px 0}
        .item:first-of-type{border-top:0;padding-top:0}
        .label{color:var(--muted);font-size:13px}
        .value{font-weight:700;text-align:right;word-break:break-word}
        .actions{display:flex;gap:10px;justify-content:flex-end;margin-top:16px}
        .btn{border:1px solid var(--borde);background:#fff;color:var(--azul);font-weight:700;border-radius:10px;padding:10px 14px;text-decoration:none;cursor:pointer}
        @media(max-width:720px){.grid{grid-template-columns:1fr}.head h1{font-size:24px}.item{display:block}.value{text-align:left;margin-top:3px}}
        @media print{body{background:#fff}.page{padding:0}.actions{display:none}.head,.card{box-shadow:none}}
    </style>
</head>
<body>
    <main class="page">
        <section class="head">
            <h1><?= qr_mon_h($titulo) ?></h1>
            <p><?= qr_mon_h($subtitulo) ?></p>
            <?php if ($estado !== ''): ?><span class="badge"><?= qr_mon_h($estado) ?></span><?php endif; ?>
        </section>
        <div class="actions">
            <button class="btn" type="button" onclick="window.print()">Imprimir</button>
        </div>
        <section class="grid">
            <?php foreach ($secciones as $seccion): ?>
                <article class="card">
                    <h2><?= qr_mon_h($seccion['titulo']) ?></h2>
                    <?php foreach ($seccion['items'] as $item): ?>
                        <div class="item">
                            <div class="label"><?= qr_mon_h($item[0]) ?></div>
                            <div class="value"><?= qr_mon_h($item[1] !== '' ? $item[1] : '-') ?></div>
                        </div>
                    <?php endforeach; ?>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
</body>
</html>
    <?php
    exit;
}

if ($idMonitor <= 0) {
    http_response_code(400);
    qr_mon_render_page('Monitor no valido', 'El codigo QR no contiene un identificador valido.', [], '');
}

$db = new MySQL('', '', '');
$joinEstado = qr_mon_table_exists($db, 'estado_equipo') ? 'LEFT JOIN estado_equipo ee ON ee.id_estado = m.id_estado' : '';
$selectEstado = qr_mon_table_exists($db, 'estado_equipo')
    ? "COALESCE(ee.nombre_estado, '') AS nombre_estado"
    : "'' AS nombre_estado";
$conUbicacion = qr_mon_table_exists($db, 'equipo_ubicacion') && qr_mon_column_exists($db, 'monitores', 'id_ubicacion');
$joinUbic = $conUbicacion ? 'LEFT JOIN equipo_ubicacion eu ON eu.id_ubicacion = m.id_ubicacion' : '';
$selectUbic = $conUbicacion
    ? "COALESCE(eu.nombre_ubicacion, '') AS nombre_ubicacion, COALESCE(eu.tipo_ubicacion, '') AS tipo_ubicacion"
    : "'' AS nombre_ubicacion, '' AS tipo_ubicacion";

$sql = "
    SELECT m.*,
           COALESCE(c.nom_colegio, '') AS nom_colegio,
           CONCAT(COALESCE(ua.nombre, ''), ' ', COALESCE(ua.apellido_paterno, ''), ' ', COALESCE(ua.apellido_materno, '')) AS usuario_asignado,
           $selectEstado,
           $selectUbic
    FROM monitores m
    LEFT JOIN colegio c ON c.id_colegio = m.id_colegio
    LEFT JOIN usuarios ua ON ua.id = m.id_usuario_asignado
    $joinEstado
    $joinUbic
    WHERE m.id_monitor = $idMonitor
    LIMIT 1
";
$rs = $db->consulta($sql);
$monitor = $rs ? $db->fetch_assoc($rs) : null;

if (!$monitor) {
    http_response_code(404);
    qr_mon_render_page('Monitor no encontrado', 'No existe un monitor asociado a este codigo QR.', [], '');
}

$compra = [];
if (qr_mon_table_exists($db, 'monitor_compra')) {
    $rsCompra = $db->consulta("SELECT proveedor, numero_factura, fecha_compra, valor_monitor FROM monitor_compra WHERE id_monitor = $idMonitor LIMIT 1");
    $compra = $rsCompra ? ($db->fetch_assoc($rsCompra) ?: []) : [];
}

$estado = trim((string)($monitor['nombre_estado'] ?? ''));
if ($estado === '') {
    $estado = qr_mon_estado_nombre($monitor['id_estado'] ?? 0);
}

$ubicacion = trim((string)($monitor['nombre_ubicacion'] ?? ''));
if ($ubicacion !== '' && trim((string)($monitor['tipo_ubicacion'] ?? '')) !== '') {
    $ubicacion = trim((string)$monitor['tipo_ubicacion']) . ' - ' . $ubicacion;
}

$secciones = [
    [
        'titulo' => 'Datos basicos',
        'items' => [
            ['Colegio', $monitor['nom_colegio'] ?? ''],
            ['Serie', $monitor['numero_serie'] ?? ''],
            ['Codigo interno', $monitor['codigo_interno'] ?? ''],
            ['Fecha registro', $monitor['fecha_registro'] ?? ''],
        ],
    ],
    [
        'titulo' => 'Asignacion',
        'items' => [
            ['Estado', $estado],
            ['Ubicacion', $ubicacion !== '' ? $ubicacion : 'Sin ubicacion'],
            ['Usuario asignado', trim((string)($monitor['usuario_asignado'] ?? '')) ?: 'Sin asignar'],
        ],
    ],
    [
        'titulo' => 'Monitor',
        'items' => [
            ['Marca', $monitor['marca'] ?? ''],
            ['Modelo', $monitor['modelo'] ?? ''],
            ['Tamano', $monitor['tamano_monitor'] ?? ''],
            ['Resolucion', $monitor['resolucion_monitor'] ?? ''],
            ['Panel', $monitor['tipo_panel'] ?? ''],
            ['Conexion', $monitor['tipo_conexion'] ?? ''],
        ],
    ],
    [
        'titulo' => 'Compra',
        'items' => [
            ['Proveedor', $compra['proveedor'] ?? ''],
            ['Factura', $compra['numero_factura'] ?? ''],
            ['Fecha compra', $compra['fecha_compra'] ?? ''],
            ['Valor', isset($compra['valor_monitor']) ? '$' . number_format((float)$compra['valor_monitor'], 0, ',', '.') : ''],
        ],
    ],
];

$marcaModelo = trim((string)($monitor['marca'] ?? '') . ' ' . (string)($monitor['modelo'] ?? ''));
$subtitulo = trim(($monitor['nom_colegio'] ?? '') . ($marcaModelo !== '' ? ' | ' . $marcaModelo : '') . ' | Serie ' . ($monitor['numero_serie'] ?? ''));
qr_mon_render_page($monitor['nombre_monitor'] ?: 'Monitor inventario', $subtitulo, $secciones, $estado);
