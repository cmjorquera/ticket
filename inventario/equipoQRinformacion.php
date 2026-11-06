<?php
require_once __DIR__ . '/componentes/boot.php';

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

$idEquipo = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_GET['id_equipo'] ?? 0);

function inv_qr_info_estado($idEstado)
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

function inv_qr_info_valor($valor, $fallback = '-')
{
    $valor = trim((string)$valor);
    return $valor !== '' ? $valor : $fallback;
}

try {
    if ($idEquipo <= 0) {
        throw new RuntimeException('Equipo no encontrado o codigo no valido.');
    }

    $equipo = $inventario->obtenerEquipoCompleto($idEquipo);
    if (!$equipo) {
        throw new RuntimeException('Equipo no encontrado o codigo no valido.');
    }
} catch (Throwable $e) {
    http_response_code($idEquipo > 0 ? 404 : 400);
    $equipo = null;
    $mensajeError = $e->getMessage();
}

$estado = '';
$ubicacion = '';
$responsable = '';
$cpu = '';
$ram = '';
$disco = '';

if ($equipo) {
    $estado = inv_qr_info_valor($equipo['nombre_estado'] ?? '', inv_qr_info_estado($equipo['id_estado'] ?? 0));
    $nomUbic = trim((string)($equipo['nombre_ubicacion'] ?? ''));
    $tipUbic = trim((string)($equipo['tipo_ubicacion'] ?? ''));
    $ubicacion = $nomUbic !== '' ? ($tipUbic !== '' ? $tipUbic . ' - ' . $nomUbic : $nomUbic) : 'Sin ubicacion';
    $responsable = inv_qr_info_valor($equipo['usuario_asignado'] ?? '', 'Sin asignar');
    $cpu = trim((string)($equipo['procesador']['equipo_fabricante'] ?? '') . ' ' . (string)($equipo['procesador']['equipo_modelo'] ?? ''));
    $disco = trim((string)($equipo['almacenamiento']['equipo_modelo'] ?? '') . ' ' . (string)($equipo['almacenamiento']['equipo_capacidad'] ?? ''));
    if (!empty($equipo['memorias'])) {
        $ram = trim((string)($equipo['memorias'][0]['tamano_memoria'] ?? ''));
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $equipo ? inventario_h($equipo['nombre_equipo']) : 'Equipo no encontrado' ?></title>
    <style>
        :root{--blue:#0f4f8a;--ink:#14213d;--muted:#64748b;--line:#dbe6f1;--soft:#eef6ff;--ok:#16803a}
        *{box-sizing:border-box}
        body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#eef5fb;color:var(--ink)}
        .page{max-width:840px;margin:0 auto;padding:18px 12px 30px}
        .hero{background:linear-gradient(135deg,#0f4f8a,#2a9d8f);color:#fff;border-radius:16px;padding:20px;box-shadow:0 14px 30px rgba(15,79,138,.2)}
        h1{margin:0 0 6px;font-size:26px;line-height:1.15}
        .hero p{margin:0;font-size:14px;opacity:.92}
        .badge{display:inline-flex;align-items:center;gap:7px;margin-top:12px;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.35);font-weight:700}
        .badge:before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor}
        .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:14px}
        .card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:15px;box-shadow:0 8px 20px rgba(16,42,67,.07)}
        .card h2{font-size:16px;margin:0 0 10px}
        .item{display:flex;justify-content:space-between;gap:14px;border-top:1px solid #edf2f7;padding:9px 0}
        .item:first-of-type{border-top:0;padding-top:0}
        .label{color:var(--muted);font-size:13px}
        .value{font-weight:700;text-align:right;word-break:break-word}
        .error{background:#fff;border:1px solid var(--line);border-radius:16px;padding:22px;margin-top:18px;box-shadow:0 8px 20px rgba(16,42,67,.07)}
        .actions{display:flex;justify-content:flex-end;margin-top:14px}
        .btn{border:1px solid var(--line);background:#fff;color:var(--blue);font-weight:700;border-radius:10px;padding:10px 14px;text-decoration:none}
        @media(max-width:700px){.grid{grid-template-columns:1fr}h1{font-size:23px}.item{display:block}.value{text-align:left;margin-top:3px}}
        @media print{body{background:#fff}.page{padding:0}.hero,.card,.error{box-shadow:none}.actions{display:none}}
    </style>
</head>
<body>
<main class="page">
    <?php if (!$equipo): ?>
        <section class="hero">
            <h1>Equipo no encontrado</h1>
            <p>El codigo escaneado no corresponde a un equipo vigente del inventario.</p>
        </section>
        <div class="error"><?= inventario_h($mensajeError ?? 'Equipo no encontrado o codigo no valido.') ?></div>
    <?php else: ?>
        <section class="hero">
            <h1><?= inventario_h($equipo['nombre_equipo'] ?: 'Equipo inventario') ?></h1>
            <p><?= inventario_h(trim(($equipo['nom_colegio'] ?? '') . ' | ' . ($equipo['tipo_pc'] ?? '') . ' | Serie ' . ($equipo['numero_serie'] ?? ''))) ?></p>
            <span class="badge"><?= inventario_h($estado) ?></span>
        </section>
        <div class="actions"><button class="btn" type="button" onclick="window.print()">Imprimir</button></div>
        <section class="grid">
            <article class="card">
                <h2>Datos basicos</h2>
                <div class="item"><div class="label">Colegio</div><div class="value"><?= inventario_h($equipo['nom_colegio'] ?? '') ?></div></div>
                <div class="item"><div class="label">Tipo</div><div class="value"><?= inventario_h($equipo['tipo_pc'] ?? '') ?></div></div>
                <div class="item"><div class="label">Serie</div><div class="value"><?= inventario_h($equipo['numero_serie'] ?? '') ?></div></div>
                <div class="item"><div class="label">Codigo QR</div><div class="value"><?= inventario_h($equipo['qr_code'] ?? '') ?></div></div>
            </article>
            <article class="card">
                <h2>Asignacion</h2>
                <div class="item"><div class="label">Estado</div><div class="value"><?= inventario_h($estado) ?></div></div>
                <div class="item"><div class="label">Ubicacion</div><div class="value"><?= inventario_h($ubicacion) ?></div></div>
                <div class="item"><div class="label">Responsable</div><div class="value"><?= inventario_h($responsable) ?></div></div>
            </article>
            <article class="card">
                <h2>Equipo</h2>
                <div class="item"><div class="label">Fabricante</div><div class="value"><?= inventario_h($equipo['fabricante'] ?? '') ?></div></div>
                <div class="item"><div class="label">Producto/modelo</div><div class="value"><?= inventario_h($equipo['producto'] ?? '') ?></div></div>
                <div class="item"><div class="label">CPU</div><div class="value"><?= inventario_h(inv_qr_info_valor($cpu)) ?></div></div>
                <div class="item"><div class="label">RAM principal</div><div class="value"><?= inventario_h(inv_qr_info_valor($ram)) ?></div></div>
                <div class="item"><div class="label">Almacenamiento</div><div class="value"><?= inventario_h(inv_qr_info_valor($disco)) ?></div></div>
            </article>
            <article class="card">
                <h2>Registro</h2>
                <div class="item"><div class="label">Registrado por</div><div class="value"><?= inventario_h($equipo['nombre_usuario_registra'] ?: ($equipo['usuario_registra'] ?? '-')) ?></div></div>
                <div class="item"><div class="label">Fecha registro</div><div class="value"><?= inventario_h($equipo['fecha_registro'] ?? '-') ?></div></div>
            </article>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
