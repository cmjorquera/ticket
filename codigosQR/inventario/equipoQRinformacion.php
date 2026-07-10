<?php
require_once __DIR__ . '/../../class/conexion.php';
header('Content-Type: text/html; charset=UTF-8');

$idEquipo = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_GET['id_equipo'] ?? 0);

function qr_inv_h($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function qr_inv_db()
{
    return new MySQL('', '', '');
}

function qr_inv_conn(MySQL $db)
{
    if (method_exists($db, 'getConexion')) {
        $cn = $db->getConexion();
        if ($cn instanceof mysqli) {
            return $cn;
        }
    }

    if (property_exists($db, 'conexion')) {
        $ref = new ReflectionObject($db);
        if ($ref->hasProperty('conexion')) {
            $prop = $ref->getProperty('conexion');
            $prop->setAccessible(true);
            $cn = $prop->getValue($db);
            if ($cn instanceof mysqli) {
                return $cn;
            }
        }
    }

    throw new RuntimeException('No fue posible abrir la conexion del sistema.');
}

function qr_inv_fetch_one(mysqli $cn, $sql, $id)
{
    $stmt = mysqli_prepare($cn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
    mysqli_stmt_close($stmt);
    return $fila;
}

function qr_inv_table_exists(MySQL $db, $table)
{
    $table = $db->escape_string($table);
    $rs = $db->consulta("SELECT COUNT(*) total FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table'");
    $row = $db->fetch_assoc($rs);
    return (int)($row['total'] ?? 0) > 0;
}

function qr_inv_column_exists(MySQL $db, $table, $column)
{
    $table = $db->escape_string($table);
    $column = $db->escape_string($column);
    $rs = $db->consulta("SELECT COUNT(*) total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND COLUMN_NAME = '$column'");
    $row = $db->fetch_assoc($rs);
    return (int)($row['total'] ?? 0) > 0;
}

function qr_inv_estado_nombre($idEstado)
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

function qr_inv_render_page($titulo, $subtitulo, array $secciones, $estado = '')
{
    ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= qr_inv_h($titulo) ?></title>
    <style>
        :root{--azul:#0f4f8a;--texto:#102a43;--muted:#65758b;--borde:#dbe6f1;--fondo:#eef5fb;--ok:#12805c}
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
            <h1><?= qr_inv_h($titulo) ?></h1>
            <p><?= qr_inv_h($subtitulo) ?></p>
            <?php if ($estado !== ''): ?><span class="badge"><?= qr_inv_h($estado) ?></span><?php endif; ?>
        </section>
        <div class="actions">
            <button class="btn" type="button" onclick="window.print()">Imprimir</button>
        </div>
        <section class="grid">
            <?php foreach ($secciones as $seccion): ?>
                <article class="card">
                    <h2><?= qr_inv_h($seccion['titulo']) ?></h2>
                    <?php foreach ($seccion['items'] as $item): ?>
                        <div class="item">
                            <div class="label"><?= qr_inv_h($item[0]) ?></div>
                            <div class="value"><?= qr_inv_h($item[1] !== '' ? $item[1] : '-') ?></div>
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

if ($idEquipo <= 0) {
    http_response_code(400);
    qr_inv_render_page('Equipo no valido', 'El codigo QR no contiene un identificador valido.', [], '');
}

$db = qr_inv_db();
$cn = qr_inv_conn($db);
$joinEstado = qr_inv_table_exists($db, 'estado_equipo') ? 'LEFT JOIN estado_equipo ee ON ee.id_estado = e.id_estado' : '';
$selectEstado = qr_inv_table_exists($db, 'estado_equipo')
    ? "COALESCE(ee.nombre_estado, '') AS nombre_estado"
    : "'' AS nombre_estado";

$joinUbic = '';
$selectUbic = "'' AS nombre_ubicacion, '' AS tipo_ubicacion";
if (qr_inv_table_exists($db, 'equipo_ubicacion') && qr_inv_column_exists($db, 'equipos', 'id_ubicacion')) {
    $joinUbic = 'LEFT JOIN equipo_ubicacion eu ON eu.id_ubicacion = e.id_ubicacion';
    $selectUbic = "COALESCE(eu.nombre_ubicacion, '') AS nombre_ubicacion, COALESCE(eu.tipo_ubicacion, '') AS tipo_ubicacion";
}

$sql = "
    SELECT e.*,
           COALESCE(c.nom_colegio, '') AS nom_colegio,
           CONCAT(COALESCE(ua.nombre, ''), ' ', COALESCE(ua.apellido_paterno, ''), ' ', COALESCE(ua.apellido_materno, '')) AS usuario_asignado,
           $selectEstado,
           $selectUbic
    FROM equipos e
    LEFT JOIN colegio c ON c.id_colegio = e.id_colegio
    LEFT JOIN usuarios ua ON ua.id = e.id_usuario_asignado
    $joinEstado
    $joinUbic
    WHERE e.id_equipo = ?
    LIMIT 1
";
$equipo = qr_inv_fetch_one($cn, $sql, $idEquipo);

if (!$equipo) {
    http_response_code(404);
    qr_inv_render_page('Equipo no encontrado', 'No existe un equipo asociado a este codigo QR.', [], '');
}

$compra = [];
if (qr_inv_table_exists($db, 'equipos_compra')) {
    $compra = qr_inv_fetch_one($cn, "SELECT proveedor, numero_factura, fecha_compra, valor_equipo FROM equipos_compra WHERE id_equipo = ? LIMIT 1", $idEquipo) ?: [];
}

$procesador = [];
if (qr_inv_table_exists($db, 'equipo_procesador')) {
    $procesador = qr_inv_fetch_one($cn, "SELECT equipo_fabricante, equipo_modelo, equipo_velocidad FROM equipo_procesador WHERE id_equipo = ? LIMIT 1", $idEquipo) ?: [];
}

$almacenamiento = [];
if (qr_inv_table_exists($db, 'equipo_almacenamiento')) {
    $almacenamiento = qr_inv_fetch_one($cn, "SELECT equipo_modelo, equipo_capacidad, equipo_tamano FROM equipo_almacenamiento WHERE id_equipo = ? LIMIT 1", $idEquipo) ?: [];
}

$estado = trim((string)($equipo['nombre_estado'] ?? ''));
if ($estado === '') {
    $estado = qr_inv_estado_nombre($equipo['id_estado'] ?? 0);
}

$ubicacion = trim((string)($equipo['nombre_ubicacion'] ?? ''));
if ($ubicacion !== '' && trim((string)($equipo['tipo_ubicacion'] ?? '')) !== '') {
    $ubicacion = trim((string)$equipo['tipo_ubicacion']) . ' - ' . $ubicacion;
}

$nombrePersonalizado = trim((string)($equipo['nombre_personalizado'] ?? ''));

$secciones = [
    [
        'titulo' => 'Datos basicos',
        'items' => array_merge(
            $nombrePersonalizado !== '' ? [['Nombre', $nombrePersonalizado]] : [],
            [
                ['Identificador tecnico', $equipo['nombre_equipo'] ?? ''],
                ['Colegio', $equipo['nom_colegio'] ?? ''],
                ['Tipo', $equipo['tipo_pc'] ?? ''],
                ['Serie', $equipo['numero_serie'] ?? ''],
                ['Codigo QR', $equipo['qr_code'] ?? ''],
            ]
        ),
    ],
    [
        'titulo' => 'Asignacion',
        'items' => [
            ['Estado', $estado],
            ['Ubicacion', $ubicacion !== '' ? $ubicacion : 'Sin ubicacion'],
            ['Usuario asignado', trim((string)($equipo['usuario_asignado'] ?? '')) ?: 'Sin asignar'],
        ],
    ],
    [
        'titulo' => 'Equipo',
        'items' => [
            ['Fabricante', $equipo['fabricante'] ?? ''],
            ['Producto', $equipo['producto'] ?? ''],
            ['CPU', trim((string)($procesador['equipo_fabricante'] ?? '') . ' ' . (string)($procesador['equipo_modelo'] ?? ''))],
            ['Disco', trim((string)($almacenamiento['equipo_modelo'] ?? '') . ' ' . (string)($almacenamiento['equipo_capacidad'] ?? ''))],
        ],
    ],
    [
        'titulo' => 'Compra',
        'items' => [
            ['Proveedor', $compra['proveedor'] ?? ''],
            ['Factura', $compra['numero_factura'] ?? ''],
            ['Fecha compra', $compra['fecha_compra'] ?? ''],
            ['Valor', isset($compra['valor_equipo']) ? '$' . number_format((float)$compra['valor_equipo'], 0, ',', '.') : ''],
        ],
    ],
];

$subtitulo = trim(($equipo['nom_colegio'] ?? '') . ' | ' . ($equipo['tipo_pc'] ?? '') . ' | Serie ' . ($equipo['numero_serie'] ?? ''));
$tituloFicha = $nombrePersonalizado !== '' ? $nombrePersonalizado : ($equipo['nombre_equipo'] ?: 'Equipo inventario');
qr_inv_render_page($tituloFicha, $subtitulo, $secciones, $estado);
