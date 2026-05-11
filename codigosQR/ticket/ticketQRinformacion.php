<?php
/******************************************************
 * public_html/codigosQR/ticket/ticketQRinformacion.php
 * Info de ticket (desde QR) + gráfico de duraciones.
 * Usa tu clase MySQL (consulta / fetch_array).
 ******************************************************/

require_once __DIR__ . '/../../class/conexion.php';
header('Content-Type: text/html; charset=UTF-8');

// ---------- Parámetro ----------
$id_ticket = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_ticket <= 0) {
  http_response_code(400);
  echo "<!doctype html><html lang='es'><meta charset='utf-8'><title>Error</title><body><h3>Solicitud inválida</h3><p>Parámetro <code>id</code> faltante o inválido.</p></body></html>";
  exit;
}

// ---------- Conexión ----------
$db = new MySQL("", "", "");

// ---------- Utils ----------
function esc($s = '') { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function fmtDT($f = '', $h = '') {
  $f = trim((string)$f); $h = trim((string)$h);
  if ($f === '' && $h === '') return '—';
  if ($f === '') return esc($h);
  if ($h === '') return esc($f);
  return esc("$f $h");
}
function mkDT($f = '', $h = '') {
  $txt = trim(trim((string)$f) . ' ' . trim((string)$h));
  if ($txt === '') return null;
  try { return new DateTime($txt); } catch(Throwable $e) { return null; }
}
function diffHours(?DateTime $a, ?DateTime $b): ?float {
  if (!$a || !$b) return null;
  $sec = $b->getTimestamp() - $a->getTimestamp();
  if ($sec < 0) return null;
  return round($sec / 3600, 1); // 1 decimal
}

// =======================
// 1) TICKET (con categoría)
// =======================
$sqlTicket = "
  SELECT
    t.id_ticket, t.id_usuario, t.asunto, t.descripcion_ticket, t.id_categoria_ticket,
    t.id_estado, t.id_prioridad, t.id_tecnico, t.comentario_administrador,
    t.comentario_final, t.comentario_reactivacion, t.identificador,
    c.id_categoria   AS cat_id,
    c.nombre_categoria AS cat_nombre,
    c.abreviacion    AS cat_abrev,
    c.icono          AS cat_icono,
    c.orden          AS cat_orden
  FROM tickets t
  LEFT JOIN categoria_de_ticket c
         ON c.id_categoria = t.id_categoria_ticket
  WHERE t.id_ticket = $id_ticket
  LIMIT 1
";
$rsTicket = $db->consulta($sqlTicket);
$ticket   = $rsTicket ? $db->fetch_array($rsTicket) : null;

if (!$ticket) {
  http_response_code(404);
  echo "<!doctype html><html lang='es'><meta charset='utf-8'><title>No encontrado</title><body><h3>Ticket no encontrado</h3><p>El ticket con id ".esc($id_ticket)." no existe.</p></body></html>";
  exit;
}

// =======================
// 2) PROCESO (último por ticket)
// =======================
$sqlProc = "
  SELECT id_proceso, id_ticket,
         fecha_creacion_inicio,  hora_creacion_inicio,
         fecha_estimada_admin,   dias_estimada_admin,
         fecha_asignacion_tecnico, hora_asignacion_tecnico,
         fecha_comienzo_ticket,  hora_comienzo_ticket,
         fecha_termino_ticket,   hora_termino_ticket,
         fecha_cierre_ticket,    hora_cierre_ticket
  FROM proceso_tickets
  WHERE id_ticket = $id_ticket
  ORDER BY id_proceso DESC
  LIMIT 1
";
$rsProc  = $db->consulta($sqlProc);
$proceso = $rsProc ? $db->fetch_array($rsProc) : [];

// =======================
// 3) ESTADO (para nombre y colores)
// =======================
$id_estado = isset($ticket['id_estado']) ? intval($ticket['id_estado']) : 0;
$sqlEst = "
  SELECT id, nombre, orden, color, color_degradado, descripcion_estado
  FROM estados_ticket
  WHERE id = $id_estado
  LIMIT 1
";
$rsEst  = $db->consulta($sqlEst);
$estado = $rsEst ? ($db->fetch_array($rsEst) ?: []) : [];

$estadoNom       = (string)($estado['nombre'] ?? 'Sin estado');
$estadoColor     = trim($estado['color'] ?? '#0d6efd');      // sólido
$estadoGradiente = trim($estado['color_degradado'] ?? '');    // puede ser 'linear-gradient(...)'
$bgHeader        = $estadoGradiente !== '' ? $estadoGradiente : $estadoColor;

// =======================
// 4) USUARIO + TÉCNICO (tabla usuarios)
// =======================
$id_usuario = (int)($ticket['id_usuario'] ?? 0);
$id_tecnico = (int)($ticket['id_tecnico'] ?? 0);

$usuario_nombre = '—';
$tecnico_nombre = '—';

$idsBuscar = [];
if ($id_usuario > 0) $idsBuscar[] = $id_usuario;
if ($id_tecnico > 0) $idsBuscar[] = $id_tecnico;

if ($idsBuscar) {
  $in = implode(',', array_map('intval', $idsBuscar));
  $sqlUsers = "SELECT id, nombre, apellido_paterno, apellido_materno, email, telefono
               FROM usuarios
               WHERE id IN ($in)";
  $rsUsers = $db->consulta($sqlUsers);
  $map = [];
  while ($rsUsers && ($u = $db->fetch_array($rsUsers))) {
    $full = trim(($u['nombre'] ?? '') . ' ' . ($u['apellido_paterno'] ?? '') . ' ' . ($u['apellido_materno'] ?? ''));
    $map[(int)$u['id']] = $full !== '' ? $full : ($u['email'] ?? '—');
  }
  if (isset($map[$id_usuario])) $usuario_nombre = $map[$id_usuario];
  if (isset($map[$id_tecnico])) $tecnico_nombre = $map[$id_tecnico];
}

// =======================
// 5) Tiempos para timeline (human-readable)
// =======================
$titulo      = 'Detalles del Ticket';

$creado_dt   = mkDT($proceso['fecha_creacion_inicio'] ?? '',   $proceso['hora_creacion_inicio'] ?? '');
$asignado_dt = mkDT($proceso['fecha_asignacion_tecnico'] ?? '', $proceso['hora_asignacion_tecnico'] ?? '');
$inicio_dt   = mkDT($proceso['fecha_comienzo_ticket'] ?? '',   $proceso['hora_comienzo_ticket'] ?? '');
$termino_dt  = mkDT($proceso['fecha_termino_ticket'] ?? '',    $proceso['hora_termino_ticket'] ?? '');
$cierre_dt   = mkDT($proceso['fecha_cierre_ticket'] ?? '',     $proceso['hora_cierre_ticket'] ?? '');

$timeline = [];
if ($asignado_dt) $timeline[] = ['dt' => fmtDT($proceso['fecha_asignacion_tecnico'] ?? '', $proceso['hora_asignacion_tecnico'] ?? ''), 'txt' => 'Se asignó técnico al ticket.'];
if ($inicio_dt)   $timeline[] = ['dt' => fmtDT($proceso['fecha_comienzo_ticket'] ?? '',     $proceso['hora_comienzo_ticket'] ?? ''),    'txt' => 'Inicio del trabajo / En proceso.'];
if ($termino_dt)  $timeline[] = ['dt' => fmtDT($proceso['fecha_termino_ticket'] ?? '',      $proceso['hora_termino_ticket'] ?? ''),     'txt' => 'Término del trabajo.'];
if ($cierre_dt)   $timeline[] = ['dt' => fmtDT($proceso['fecha_cierre_ticket'] ?? '',       $proceso['hora_cierre_ticket'] ?? ''),      'txt' => 'Ticket cerrado.'];

// =======================
// 6) Datos para el GRÁFICO (dinámico días/horas) + colores por estado
// =======================

// helpers para días/horas
function mkDT2($f='', $h=''){ $t=trim("$f $h"); if($t==='') return null; try{ return new DateTime($t); }catch(Throwable $e){ return null; } }
function diffDays(?DateTime $a, ?DateTime $b){
  if(!$a || !$b) return null;
  $sec = $b->getTimestamp() - $a->getTimestamp();
  if ($sec < 0) return null;
  return round($sec / 86400, 2); // 2 decimales
}
function diffHours2(?DateTime $a, ?DateTime $b){
  if(!$a || !$b) return null;
  $sec = $b->getTimestamp() - $a->getTimestamp();
  if ($sec < 0) return null;
  return round($sec / 3600, 2); // 2 decimales
}

// Hitos crudos para el gráfico
$dt_recibido  = mkDT2($proceso['fecha_creacion_inicio'] ?? '',   $proceso['hora_creacion_inicio'] ?? '');
$dt_asignado  = mkDT2($proceso['fecha_asignacion_tecnico'] ?? '', $proceso['hora_asignacion_tecnico'] ?? '');
$dt_inicio    = mkDT2($proceso['fecha_comienzo_ticket'] ?? '',    $proceso['hora_comienzo_ticket'] ?? '');
$dt_terminado = mkDT2($proceso['fecha_termino_ticket'] ?? '',     $proceso['hora_termino_ticket'] ?? '');
$dt_cerrado   = mkDT2($proceso['fecha_cierre_ticket'] ?? '',      $proceso['hora_cierre_ticket'] ?? '');

// Labels y valores en DÍAS
$chartLabels = ['Recibido→Asignado','Asignado→En proceso','En proceso→Terminado','Terminado→Cerrado'];
$chartValues = [
  diffDays($dt_recibido,  $dt_asignado),
  diffDays($dt_asignado,  $dt_inicio),
  diffDays($dt_inicio,    $dt_terminado),
  diffDays($dt_terminado, $dt_cerrado),
];


// ---- SLA
$estimadasHoras = (int)($proceso['dias_estimada_admin'] ?? 0) * 24;
$finParaSLA     = $dt_terminado ?: new DateTime();  // si no está terminado, hasta ahora
$horasTrans     = diffHours2($dt_recibido, $finParaSLA) ?? 0;

$porcSLA  = ($estimadasHoras > 0) ? round(($horasTrans / $estimadasHoras) * 100) : null;
if ($estimadasHoras <= 0) {
  $slaTexto = 'Sin estimación';
  $slaClase = 'sla-unk';
} elseif ($horasTrans <= $estimadasHoras) {
  $slaTexto = 'Dentro de plazo';
  $slaClase = 'sla-ok';
} else {
  $slaTexto = 'Fuera de plazo';
  $slaClase = 'sla-bad';
}







// Colores del estado de llegada de cada tramo
$colMap = [];
$rsCol = $db->consulta("SELECT id, nombre, color FROM estados_ticket WHERE id IN (2,3,5,6)");
while ($rsCol && ($row = $db->fetch_array($rsCol))) { $colMap[(int)$row['id']] = trim($row['color'] ?: '#999'); }
$chartColors = [
  $colMap[2] ?? '#FFF4C1', // Asignado
  $colMap[3] ?? '#FFE0E0', // En proceso
  $colMap[5] ?? '#C8C7C5', // Terminado
  $colMap[6] ?? '#E0E0E0', // Cerrado
];

// Escala dinámica: si los días son muy chicos, cambiamos a HORAS
$valsDays = array_values(array_filter($chartValues, fn($v)=> $v !== null));
$maxDays  = $valsDays ? max($valsDays) : 0;

if ($maxDays < 0.5) { // menos de ~12h
  $chartUnit   = 'Horas';
  $chartSeries = [
    diffHours2($dt_recibido,  $dt_asignado),
    diffHours2($dt_asignado,  $dt_inicio),
    diffHours2($dt_inicio,    $dt_terminado),
    diffHours2($dt_terminado, $dt_cerrado),
  ];
} else {
  $chartUnit   = 'Días';
  $chartSeries = $chartValues;
}

// =======================
// 7) Canonical
// =======================
$canonical = (function () {
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $uri    = $_SERVER['REQUEST_URI'] ?? '/';
  return $scheme . '://' . $host . $uri;
})();





// orden: 1 Recibido, 2 Asignado, 3 En proceso, 5 Terminado, 6 Cerrado
$pasos = [1,2,3,5,6];
$idxActual = array_search((int)$id_estado, $pasos, true);
if ($idxActual === false) $idxActual = 0;

$coloresEstados = [];
$rsCols = $db->consulta("SELECT id, color FROM estados_ticket WHERE id IN (1,2,3,5,6)");
while($rsCols && ($r=$db->fetch_array($rsCols))){
  $coloresEstados[(int)$r['id']] = $r['color'];
}




?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= esc($titulo) ?></title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
 <style>
    :root{ --blue:#0d6efd; --border:#e5e7eb; --ink:#101828; --muted:#667085; }
    *{ box-sizing:border-box; }
    body{ margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial,'Noto Sans',sans-serif; background:#f6f8fb; color:var(--ink); }
    .ticket-page{ max-width:1000px; margin:24px auto; padding:0 12px; }
    /* Header con color del estado */
    .ticket-head{  display:flex;  align-items:center;  justify-content:space-between;  padding:16px 20px;  border-radius:12px;}
    /* aspecto “barra azul” */
    .ticket-head--solid{  background: linear-gradient(180deg,#0d6efd 0%, #0b5ed7 100%);  color:#fff;  box-shadow:    0 10px 26px rgba(13,110,253,.35),  /* glow exterior */    inset 0 -2px 0 rgba(255,255,255,.18); /* leve brillo inferior */}
    .ticket-head--solid h1{  margin:0;  font-weight:800;  color:#fff;  text-shadow: 0 1px 0 rgba(0,0,0,.08);}
    .ticket-id-badge{  font-weight:800;  color:#fff;  background: rgba(255,255,255,.15);  border:1px solid rgba(255,255,255,.35);  padding:6px 10px;  border-radius:10px;}
    .ticket-head h1{ margin:0; font-weight:800; font-size:28px; }
    .ticket-id-badge{font-weight:800; color:#fff; background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.35); padding:6px 10px; border-radius:10px;}
    .ticket-grid{ display:grid; gap:14px; margin-top:16px; grid-template-columns:repeat(3,1fr); }
    @media (max-width:992px){ .ticket-grid{ grid-template-columns:repeat(2,1fr); } }
    @media (max-width:576px){ .ticket-grid{ grid-template-columns:1fr; } }
    .info-card{ background:#fff; border:1px solid var(--border); border-radius:10px; padding:14px 16px; box-shadow:0 1px 0 rgba(16,24,40,.04); }
    .info-card .label{ color:#0d6efd; font-weight:800; margin-bottom:6px; font-size:.95rem; display:flex; gap:.4rem; align-items:center; }
    .info-card .value{ color:var(--ink); }
    .info-card--wide{ grid-column:span 2; }
    @media (max-width:576px){ .info-card--wide{ grid-column:1; } }

    .section-title{ margin:24px 0 10px; font-weight:800; color:var(--ink); text-align:center; }
    .section-title::after{ content:""; display:block; height:3px; width:70%; margin:8px auto 0; background:#ef4444; border-radius:4px; }
    .timeline{ background:#fff; border:1px solid var(--border); border-radius:10px; padding:8px 16px; }
    .timeline .item{ padding:10px 0; border-bottom:1px solid #f1f5f9; }
    .timeline .item:last-child{ border-bottom:0; }
    .timeline .when{ font-weight:700; color:#111827; font-size:.95rem; }
    .timeline .text{ color:#374151; }
    .chart-card{ background:#fff; border:1px solid var(--border); border-radius:10px; padding:16px; margin-top:16px; }
    .footer{ color:#98A2B3; font-size:.85rem; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-top:12px; }
    .estado-chip{ display:inline-flex; align-items:center; gap:.5rem; padding:.35rem .7rem; border-radius:999px; background:#fff; color:#111; font-weight:600; border:1px solid rgba(0,0,0,.08); }
    /* Botón de ayuda (Descripción) */
    .help-dot{width:22px; height:22px; border-radius:50%;border:1px solid rgba(0,0,0,.15);background:#fff; color:#111; font-weight:700; line-height:20px;display:inline-flex; align-items:center; justify-content:center;cursor:pointer;}
    .help-pop{position:absolute; z-index:9999; max-width:280px;background:#fff; color:#111; border:1px solid #e5e7eb; border-radius:8px;box-shadow:0 8px 24px rgba(16,24,40,.12);padding:10px 12px; font-size:.92rem;}
    .header-actions{ display:flex; gap:8px; align-items:center; }
    .sla-chip{font-weight:700; padding:6px 10px; border-radius:999px; border:1px solid transparent;background:#f3f4f6; color:#111;}
    .sla-ok{  background:#e8f7ef; color:#0f5132; border-color:#a3e4c4; }
    .sla-bad{ background:#fdecec; color:#842029; border-color:#f5b5b5; }
    .sla-unk{ background:#eef2ff; color:#1e3a8a; border-color:#c7d2fe; }
    .btn-ghost{  background:rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.35);  padding:6px 10px; border-radius:10px; cursor:pointer; font-weight:700;}
    .btn-ghost:hover{ filter:brightness(1.05); }
    @media print{.btn-ghost, .help-dot { display:none !important; }
      .chart-card { page-break-inside: avoid; }
      body { background:#fff; }
    }
    .progress-estado{ display:grid; grid-template-columns:repeat(5,1fr); gap:6px; margin:10px 0 0; }
    .progress-estado .seg{ height:6px; border-radius:999px; }
    .timeline .item{ position:relative; padding-left:26px; }
    .timeline .item::before{content:""; position:absolute; left:6px; top:14px; width:8px; height:8px; border-radius:50%;background:#0d6efd; box-shadow:0 0 0 3px rgba(13,110,253,.15);}
    .timeline-summary{margin-top:8px;padding:10px 12px;display:flex;gap:8px;justify-content:flex-end;align-items:center;border-top:1px dashed #e5e7eb;}


  </style>
</head>
<body>
  <div class="ticket-page">
    <!-- Header -->
    <div class="ticket-head ticket-head--solid">
      <h1><?= esc($titulo) ?></h1>
      <div class="head-right">
        <span class="ticket-id-badge">#00-<?= esc($ticket['id_ticket']) ?></span>
    
        <button class="btn-ghost" id="btnPrint" title="Imprimir">🖨️</button>
      </div>
    </div>

    <!-- Cards (ordenadas por filas) -->
    <div class="ticket-grid">
      <!-- Fila 1: Usuario | Técnico | Categoría -->
      <div class="info-card">
        <div class="label">Usuario</div>
        <div class="value"><?= esc($usuario_nombre) ?></div>
      </div>

      <div class="info-card">
        <div class="label">Técnico</div>
        <div class="value"><?= esc($tecnico_nombre) ?></div>
      </div>

      <div class="info-card">
        <div class="label">Categoría</div>
        <div class="value"><?= esc($ticket['cat_nombre'] ?? '—') ?></div>
      </div>

      <!-- Fila 2: Asunto | Descripción (dos columnas) -->
      <div class="info-card">
        <div class="label">Asunto</div>
        <div class="value"><?= esc($ticket['asunto'] ?? '') ?: '—' ?></div>
      </div>

      <div class="info-card info-card--wide">
        <div class="label">
          Descripción
          <button type="button" class="help-dot" data-help="Detalle textual del requerimiento reportado por el usuario.">?</button>
        </div>
        <div class="value" style="white-space:pre-wrap;"><?= esc($ticket['descripcion_ticket'] ?? '') ?: '—' ?></div>
      </div>

      <!-- Fila 3: Fecha de Creación | Comentario Final (dos columnas) -->
      <div class="info-card">
        <div class="label">Fecha de Creación</div>
        <div class="value"><?= fmtDT($proceso['fecha_creacion_inicio'] ?? '', $proceso['hora_creacion_inicio'] ?? '') ?></div>
      </div>

      <div class="info-card info-card--wide">
        <div class="label">Comentario Final del Tecnico</div>
        <div class="value" style="white-space:pre-wrap;"><?= esc(trim($ticket['comentario_final'] ?? '')) ?: '—' ?></div>
      </div>
    </div>

    <!-- Avances -->
    <h3 class="section-title">Avances del Técnico</h3>
<div class="timeline">
  <?php if (!$timeline): ?>
    <div class="item">
      <div class="text">Sin avances registrados en el flujo del proceso.</div>
    </div>
  <?php else: ?>
    <?php foreach ($timeline as $a): ?>
      <div class="item">
        <div class="when"><?= esc($a['dt']) ?></div>
        <div class="text"><?= esc($a['txt']) ?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- resumen a la derecha -->
  <div class="timeline-summary">
    <span class="estado-chip"><strong><?= esc($estadoNom) ?></strong></span>
    <span class="sla-chip <?= esc($slaClase) ?>">
      <?= esc($slaTexto) ?><?= $porcSLA !== null ? ' · '.esc($porcSLA).'%' : '' ?>
    </span>
  </div>
</div>

    <!-- Gráfico -->
    <div class="chart-card" style="height:260px;">
      <canvas id="chartDuraciones"></canvas>
    </div>

    <!--<div class="footer">-->
    <!--  <span class="estado-chip" style="background:#fff;">-->
    <!--    Estado: <strong><?= esc($estadoNom) ?></strong>-->
    <!--  </span>-->
    <!--</div>-->
  </div>

  <!-- Chart.js + datalabels -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
  <script>
    const labelsSingle = <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>;
    const rawVals      = <?= json_encode($chartSeries) ?>;
    const barColors    = <?= json_encode($chartColors) ?>;
    const unitLabel    = <?= json_encode($chartUnit) ?>;

    const labelsY  = labelsSingle.map(s => s.includes('→') ? s.split('→') : [s]);
    const plotVals = rawVals.map(v => (v === null ? 0 : v));
    const hayDatos = plotVals.some(v => v > 0);

    Chart.register(ChartDataLabels);
    const ctx = document.getElementById('chartDuraciones').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labelsY,
        datasets: [{
          data: plotVals,
          backgroundColor: barColors,
          borderColor: barColors,
          borderWidth: 1,
          borderRadius: 8,
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        layout: { padding: { right: 28 } },
        scales: {
          x: { beginAtZero: true, title: { display: true, text: unitLabel }, grid: { color: 'rgba(0,0,0,.06)' }, ticks: { precision: 0 } },
          y: { grid: { display: false }, ticks: { font: { weight: '700' } } }
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => {
                const v = rawVals[ctx.dataIndex];
                return (v === null) ? 'Sin datos' : `${v} ${unitLabel.toLowerCase()}`;
              }
            }
          },
          datalabels: {
            display: hayDatos,
            anchor: 'end',
            align: 'right',
            offset: 6,
            color: '#111',
            font: { weight: 'bold' },
            formatter: (v, ctx) => {
              const rv = rawVals[ctx.dataIndex];
              return (rv === null || rv === 0) ? '' : `${rv} ${unitLabel[0].toLowerCase()}`;
            }
          }
        }
      }
    });
  </script>

  <script>
    document.getElementById('btnPrint')?.addEventListener('click', ()=> window.print());
  </script>
</body>
</html>
