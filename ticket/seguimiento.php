<?php
/**
 * SEGUIMIENTO DEL TICKET (página pública, sin sesión) — la abre el código QR.
 * Reemplaza a codigosQR/ticket/ticketQRinformacion.php?id=… del sistema antiguo.
 *
 * Se entra con el código aleatorio tickets.identificador (?c=…), no con el número
 * del ticket, para que nadie pueda ver otros tickets cambiando el número.
 * Muestra solo lo necesario para seguir el caso: estado, ciclo, técnico y los
 * movimientos del sistema. NO muestra la descripción, el chat ni la bitácora
 * interna (para eso hay que ingresar al sistema).
 * Lógica: ticket/logica/LOGICA_SEGUIMIENTO_QR.txt
 */
declare(strict_types=1);

require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../helpers/tickets.php';
require_once __DIR__ . '/../clases/Tickets/BitacoraTicket.php';

header('Content-Type: text/html; charset=UTF-8');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

$codigo = (string) ($_GET['c'] ?? '');
$t = false;
$movimientos = [];
try {
    if (preg_match('/^[A-Za-z0-9]{12,32}$/', $codigo)) {
        $db = Conexion::getInstance('sistema_panel_central');
        $t = $db->fetchOne(
            "SELECT t.id_ticket, t.asunto, t.id_estado, e.nombre AS estado, e.color,
                    c.nombre_categoria AS categoria, c.icono,
                    TRIM(CONCAT_WS(' ', s.nombre, s.apellido_paterno)) AS solicitante,
                    TRIM(CONCAT_WS(' ', tc.nombre, tc.apellido_paterno)) AS tecnico,
                    CONCAT(pt.fecha_creacion_inicio, ' ', COALESCE(pt.hora_creacion_inicio, '00:00:00')) AS creado,
                    CONCAT(pt.fecha_asignacion_tecnico, ' ', COALESCE(pt.hora_asignacion_tecnico, '00:00:00')) AS asignado,
                    CONCAT(pt.fecha_comienzo_ticket, ' ', COALESCE(pt.hora_comienzo_ticket, '00:00:00')) AS comenzado,
                    CONCAT(pt.fecha_termino_ticket, ' ', COALESCE(pt.hora_termino_ticket, '00:00:00')) AS terminado,
                    pt.fecha_estimada_admin AS estimada
               FROM tickets t
          LEFT JOIN estados_ticket e ON e.id = t.id_estado
          LEFT JOIN categoria_de_ticket c ON c.id_categoria = t.id_categoria_ticket
          LEFT JOIN usuarios s ON s.id = t.id_usuario
          LEFT JOIN usuarios tc ON tc.id = t.id_tecnico
          LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
              WHERE BINARY t.identificador = ? AND t.estado = 1 AND t.id_estado <> 4
              LIMIT 1",
            [$codigo]
        );
        if ($t) {
            // Solo los registros automáticos (creación, asignación, estados): la bitácora es interna
            foreach ((new BitacoraTicket($db))->listar((int) $t['id_ticket'], 0) as $m) {
                if ($m['lado'] === 'sistema') {
                    $movimientos[] = $m;
                }
            }
        }
    }
} catch (Throwable $e) {
    error_log('[seguimiento] ' . $e->getMessage());
    $t = false;
}
if (!$t) {
    http_response_code(404);
}

$h = static fn (?string $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$fecha = static fn (?string $v): string => ($v && strtotime($v) && !str_starts_with($v, ' ')) ? date('d/m/Y H:i', strtotime($v)) : '';
$estadoId = (int) ($t['id_estado'] ?? 0);
$pasos = $t ? [
    ['Recibido', 'bi-inbox', $fecha($t['creado']), true],
    ['Asignado', 'bi-person-check', $fecha($t['asignado']), !in_array($estadoId, [1, 4], true)],
    ['En proceso', 'bi-gear', $fecha($t['comenzado']), in_array($estadoId, [3, 5, 6, 7], true)],
    ['Terminado', 'bi-flag', $fecha($t['terminado']), in_array($estadoId, [5, 6], true)],
] : [];
$hechos = count(array_filter($pasos, static fn ($p) => $p[3]));
$icono = preg_match('/^bi-[a-z0-9-]+$/', (string) ($t['icono'] ?? '')) ? $t['icono'] : 'bi-tag';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#16365c">
    <title><?= $t ? 'Ticket #' . (int) $t['id_ticket'] . ' · ' : '' ?>Seguimiento · SEDUC</title>
    <link rel="icon" type="image/png" href="../imagenes/logo_seduc.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root{--navy:#16365c;--navy-2:#0f2440;--ambar:#e8a33d;--fg:#111827;--muted:#6b7280;--border:#e5e7eb;--ok:#2f7d32;--ok-l:#e8f5e9;--estado:<?= $h(preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($t['color'] ?? '')) ? $t['color'] : '#E6E6FA') ?>}
        *{margin:0;padding:0;box-sizing:border-box}
        body{min-height:100vh;font-family:Inter,system-ui,sans-serif;color:var(--fg);background:#f3f5f8}
        .cab{background:linear-gradient(180deg,#1d4675,var(--navy-2));color:#fff;padding:22px 18px 70px;border-bottom:4px solid var(--ambar)}
        .marca{display:flex;align-items:center;gap:10px;max-width:520px;margin:0 auto}
        .marca img{width:36px;height:36px;border-radius:9px;background:#fff;padding:3px}
        .marca strong{display:block;font-size:15px}.marca span{font-size:11.5px;opacity:.8}
        main{max-width:520px;margin:-52px auto 0;padding:0 14px 28px;display:grid;gap:12px}
        .card{background:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(15,36,64,.08);padding:18px}
        .folio{font:500 12px 'DM Mono',monospace;color:#1d4675;letter-spacing:.06em}
        h1{font-size:20px;line-height:1.3;margin:4px 0 12px;overflow-wrap:anywhere}
        .estado{display:inline-flex;align-items:center;gap:7px;padding:6px 12px;border-radius:99px;background:var(--estado);font-weight:600;font-size:13px;color:#1f2937}
        .barra{height:8px;border-radius:99px;background:#eef1f4;margin:16px 0 6px;overflow:hidden}
        .barra span{display:block;height:100%;border-radius:99px;background:linear-gradient(90deg,var(--ambar),var(--ok))}
        .avance{font-size:12px;color:var(--muted)}
        h2{font-size:14px;margin-bottom:12px}
        ol{list-style:none}
        .ciclo li{display:grid;grid-template-columns:30px 1fr;gap:12px;position:relative;padding-bottom:16px}
        .ciclo li::before{content:"";position:absolute;left:14px;top:30px;bottom:0;width:2px;background:var(--border)}
        .ciclo li:last-child{padding-bottom:0}.ciclo li:last-child::before{display:none}
        .ciclo li>span{display:grid;place-items:center;width:30px;height:30px;border-radius:50%;background:#eef1f4;color:var(--muted);z-index:1}
        .ciclo li.ok>span{background:var(--ok);color:#fff}.ciclo li.ok::before{background:var(--ok)}
        .ciclo strong{display:block;font-size:14px}.ciclo small{font:500 11.5px 'DM Mono',monospace;color:var(--muted)}
        dl{display:grid;gap:10px}
        dl div{display:flex;justify-content:space-between;gap:12px;padding-bottom:10px;border-bottom:1px solid var(--border)}
        dl div:last-child{border:0;padding:0}
        dt{font-size:12px;color:var(--muted)} dd{font-size:13.5px;font-weight:600;text-align:right}
        .mov li{display:flex;gap:10px;padding:9px 0;border-bottom:1px solid var(--border);font-size:13px}
        .mov li:last-child{border:0}.mov i{color:#1d4675;margin-top:2px}
        .mov time{display:block;font:500 11px 'DM Mono',monospace;color:var(--muted);margin-top:2px}
        .acciones{display:grid;gap:8px}
        .btn{display:flex;justify-content:center;align-items:center;gap:8px;padding:13px;border-radius:12px;font:600 14px Inter,sans-serif;text-decoration:none;border:0;cursor:pointer}
        .btn--p{background:var(--navy);color:#fff}.btn--s{background:#fff;color:var(--navy);border:1px solid var(--border)}
        .nota{font-size:11.5px;color:var(--muted);text-align:center;line-height:1.5}
        .vacio{text-align:center;padding:30px 18px}.vacio i{font-size:40px;color:#991b1b}
        .vacio h1{margin-top:10px}.vacio p{color:var(--muted);font-size:14px;line-height:1.5}
        @media print{.acciones,.nota{display:none}.cab{padding-bottom:20px}main{margin-top:12px}}
    </style>
</head>
<body>
<header class="cab"><div class="marca"><img src="../imagenes/logo_seduc.png" alt=""><div><strong>SEDUC Chile</strong><span>Seguimiento de ticket</span></div></div></header>
<main>
<?php if (!$t): ?>
    <section class="card vacio">
        <i class="bi bi-qr-code-scan"></i>
        <h1>Ticket no encontrado</h1>
        <p>El código no es válido o el ticket ya no está disponible. Revisa el QR o ingresa al sistema para ver tus tickets.</p>
        <div class="acciones" style="margin-top:16px"><a class="btn btn--p" href="../index.php"><i class="bi bi-box-arrow-in-right"></i> Ingresar al sistema</a></div>
    </section>
<?php else: ?>
    <section class="card">
        <span class="folio">TICKET #<?= (int) $t['id_ticket'] ?></span>
        <h1><?= $h($t['asunto']) ?></h1>
        <span class="estado"><i class="bi bi-circle-fill" style="font-size:8px;opacity:.6"></i> <?= $h($t['estado'] ?: 'Sin estado') ?></span>
        <div class="barra" role="progressbar" aria-valuemin="0" aria-valuemax="4" aria-valuenow="<?= $hechos ?>"><span style="width:<?= $hechos * 25 ?>%"></span></div>
        <span class="avance"><?= $hechos ?> de 4 etapas completadas</span>
    </section>

    <section class="card ciclo">
        <h2>Ciclo del ticket</h2>
        <ol>
        <?php foreach ($pasos as [$nombre, $ic, $f, $ok]): ?>
            <li class="<?= $ok ? 'ok' : '' ?>"><span><i class="bi <?= $ok ? 'bi-check-lg' : $ic ?>"></i></span><div><strong><?= $h($nombre) ?></strong><small><?= $h($f ?: ($ok ? '' : 'Pendiente')) ?></small></div></li>
        <?php endforeach; ?>
        </ol>
    </section>

    <section class="card">
        <h2>Datos</h2>
        <dl>
            <div><dt>Solicitante</dt><dd><?= $h($t['solicitante']) ?></dd></div>
            <div><dt>Técnico</dt><dd><?= $h($t['tecnico'] ?: 'Por asignar') ?></dd></div>
            <div><dt>Categoría</dt><dd><i class="bi <?= $h($icono) ?>"></i> <?= $h($t['categoria'] ?: '—') ?></dd></div>
            <div><dt>Fecha estimada</dt><dd><?= $t['estimada'] && strtotime((string) $t['estimada']) ? $h(date('d/m/Y', strtotime((string) $t['estimada']))) : 'Sin estimación' ?></dd></div>
        </dl>
    </section>

    <?php if ($movimientos): ?>
    <section class="card mov">
        <h2>Movimientos</h2>
        <ol>
        <?php foreach (array_reverse($movimientos) as $m): ?>
            <li><i class="bi bi-arrow-repeat"></i><div><?= $h($m['texto']) ?><time><?= $h($fecha($m['fecha'])) ?></time></div></li>
        <?php endforeach; ?>
        </ol>
    </section>
    <?php endif; ?>

    <div class="acciones">
        <a class="btn btn--p" href="ticket_detalle.php?id=<?= (int) $t['id_ticket'] ?>&amp;vista=usuario"><i class="bi bi-chat-dots"></i> Ingresar para conversar con el técnico</a>
        <a class="btn btn--s" href="?c=<?= $h(rawurlencode($codigo)) ?>"><i class="bi bi-arrow-clockwise"></i> Actualizar</a>
    </div>
    <p class="nota">Esta página muestra solo el avance del ticket. La descripción y la conversación se ven al ingresar al sistema.</p>
<?php endif; ?>
</main>
</body>
</html>
