<?php
/**
 * AGENDA · página de inicio para secretaría / coordinación.
 * Muestra los próximos eventos (con su logística) y los almuerzos del día:
 * menú, cuántos pidió cada opción, quién pidió qué y descarga del PDF.
 *
 * Se asigna por usuario con usuarios.pagina_inicio = 'agenda/index.php'
 * (clases/PaginaInicio.php · agenda/sql/01_pagina_inicio.sql).
 * Cualquiera con sesión puede abrirla; el detalle de pedidos solo lo ve quien
 * tiene permiso en Almuerzos (encargado o almuerzo_configuracion.ids_ver_pedidos).
 */
declare(strict_types=1);

require_once __DIR__ . '/../validar_sesion.php';
require_once __DIR__ . '/../eventos/class/Eventos.php';
require_once __DIR__ . '/../almuerzos/class/Almuerzos.php';

$idUsuario = (int) ($_SESSION['id'] ?? 0);
$db = Conexion::getInstance('sistema_panel_central');
$zona = new DateTimeZone('America/Santiago');
$hoy = new DateTimeImmutable('today', $zona);
$h = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$diasSemana = ['', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaLarga = static fn (DateTimeInterface $d): string =>
    ucfirst($diasSemana[(int) $d->format('N')]) . ' ' . (int) $d->format('j') . ' de ' . $meses[(int) $d->format('n')];

// ------------------------------------------------------------------ EVENTOS
$diasEventos = 14;
$eventosPorDia = [];
$errorEventos = '';
try {
    $ev = new Eventos($db);
    $desde = $hoy->format('Y-m-d');
    $hasta = $hoy->modify('+' . $diasEventos . ' days')->format('Y-m-d');
    foreach ($ev->listar($idUsuario, $desde, $hasta) as $resumen) {
        $e = $ev->obtener((int) $resumen['id'], $idUsuario);
        if (!$e) {
            continue;
        }
        $dia = max((string) $e['fecha_inicio'], $desde);   // los que empezaron antes aparecen hoy
        $si = $no = $pend = 0;
        foreach ($e['invitados'] as $i) {
            $r = (string) $i['respuesta'];
            $r === 'si' ? $si++ : ($r === 'no' ? $no++ : $pend++);
        }
        $e['_si'] = $si;
        $e['_no'] = $no;
        $e['_pend'] = $pend;
        $e['_color'] = $resumen['color'] ?? '#0f766e';
        $eventosPorDia[$dia][] = $e;
    }
    ksort($eventosPorDia);
} catch (Throwable $ex) {
    error_log('[agenda] eventos: ' . $ex->getMessage());
    $errorEventos = 'No fue posible cargar los eventos.';
}

// ---------------------------------------------------------------- ALMUERZOS
$alm = new Almuerzos($db);
$almInstalado = false;
$verPedidos = false;
$fechaTxt = (string) ($_GET['fecha'] ?? '');
$fechaAlm = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaTxt) ? DateTimeImmutable::createFromFormat('!Y-m-d', $fechaTxt, $zona) : false;
if (!$fechaAlm) {
    $fechaAlm = $hoy;
    while (!Almuerzos::esDiaHabil($fechaAlm)) {       // sábado/domingo → próximo lunes
        $fechaAlm = $fechaAlm->modify('+1 day');
    }
}
$diaAlm = $fechaAlm->format('Y-m-d');
$menu = null;
$pedidos = [];
$conteo = ['menu' => 0, 'hipo' => 0, 'picnic' => 0, 'no' => 0, 'sin' => 0];
$semana = [];
try {
    $almInstalado = $alm->instalado();
    if ($almInstalado) {
        $verPedidos = $alm->puedeVerPedidos($idUsuario);
        $menu = $alm->menuDia($diaAlm);
        if ($verPedidos) {
            $pedidos = $menu ? $alm->pedidosDelDia($diaAlm) : [];
            $conteo = Almuerzos::conteo($pedidos);
            $lunes = $fechaAlm->modify('monday this week');
            for ($i = 0; $i < 5; $i++) {
                $d = $lunes->modify("+{$i} days");
                $k = $d->format('Y-m-d');
                $tieneMenu = (bool) $alm->menuDia($k);
                $semana[$k] = ['fecha' => $d, 'menu' => $tieneMenu, 'conteo' => $tieneMenu ? Almuerzos::conteo($alm->pedidosDelDia($k)) : null];
            }
        }
    }
} catch (Throwable $ex) {
    error_log('[agenda] almuerzos: ' . $ex->getMessage());
}
$etiquetas = ['menu' => 'Menú del día', 'hipo' => 'Hipocalórico', 'picnic' => 'Picnic', 'no' => 'No almuerza', 'sin' => 'Sin responder'];
$totalPersonas = array_sum($conteo);
$totalAlmuerzos = $conteo['menu'] + $conteo['hipo'] + $conteo['picnic'];
$diaAnterior = $fechaAlm->modify('-1 day');
while (!Almuerzos::esDiaHabil($diaAnterior)) {
    $diaAnterior = $diaAnterior->modify('-1 day');
}
$diaSiguiente = $fechaAlm->modify('+1 day');
while (!Almuerzos::esDiaHabil($diaSiguiente)) {
    $diaSiguiente = $diaSiguiente->modify('+1 day');
}

$nombre = trim((string) ($_SESSION['nombre'] ?? ''));
$pagina_titulo = 'Agenda';
$pagina_estilos = ['agenda/css/agenda.css?v=' . (string) @filemtime(__DIR__ . '/css/agenda.css')];
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-header ag-header">
  <div>
    <h1><i class="bi bi-calendar2-week" aria-hidden="true"></i> Hola<?= $nombre !== '' ? ', ' . $h($nombre) : '' ?></h1>
    <p><?= $h($fechaLarga($hoy)) ?> · próximos eventos y almuerzos</p>
  </div>
  <div class="page-header-actions">
    <a class="btn btn-outline" href="../eventos/index.php"><i class="bi bi-calendar-event"></i> Calendario de eventos</a>
    <a class="btn btn-outline" href="../almuerzos/index.php"><i class="bi bi-cup-hot"></i> Almuerzos</a>
  </div>
</div>

<div class="ag-grid">

  <!-- ============================== ALMUERZOS ============================== -->
  <section class="ag-card ag-almuerzos" aria-labelledby="ag-alm-tit">
    <header class="ag-card__head">
      <div>
        <h2 id="ag-alm-tit"><i class="bi bi-cup-hot"></i> Almuerzos</h2>
        <span><?= $h($fechaLarga($fechaAlm)) ?><?= $diaAlm === $hoy->format('Y-m-d') ? ' · hoy' : '' ?></span>
      </div>
      <div class="ag-dia-nav">
        <a class="btn btn-outline btn-sm" href="?fecha=<?= $diaAnterior->format('Y-m-d') ?>" aria-label="Día anterior"><i class="bi bi-chevron-left"></i></a>
        <form method="get"><input class="form-input ag-fecha" type="date" name="fecha" value="<?= $h($diaAlm) ?>" onchange="this.form.submit()" aria-label="Elegir día"></form>
        <a class="btn btn-outline btn-sm" href="?fecha=<?= $diaSiguiente->format('Y-m-d') ?>" aria-label="Día siguiente"><i class="bi bi-chevron-right"></i></a>
      </div>
    </header>

    <?php if (!$almInstalado): ?>
      <p class="ag-vacio">El módulo de almuerzos aún no está instalado.</p>
    <?php elseif (!$menu): ?>
      <p class="ag-vacio">No hay almuerzo publicado para este día.</p>
    <?php else: ?>
      <div class="ag-menu">
        <?php foreach (['menu' => 'Menú del día', 'hipo' => 'Hipocalórico', 'picnic' => 'Picnic'] as $k => $etq): ?>
          <div class="ag-menu__item is-<?= $k ?>"><span><?= $etq ?></span><b><?= $h($menu[$k] ?? '—') ?></b></div>
        <?php endforeach; ?>
        <?php if (!empty($menu['obs'])): ?><p class="ag-menu__obs"><i class="bi bi-info-circle"></i> <?= $h($menu['obs']) ?></p><?php endif; ?>
      </div>

      <?php if ($verPedidos): ?>
        <div class="ag-conteo">
          <?php foreach ($etiquetas as $k => $etq): ?>
            <div class="ag-conteo__item is-<?= $k ?>"><b><?= (int) $conteo[$k] ?></b><span><?= $etq ?></span></div>
          <?php endforeach; ?>
        </div>
        <p class="ag-total"><b><?= $totalAlmuerzos ?></b> almuerzos para cocina · <?= $totalPersonas ?> personas en la lista</p>

        <div class="ag-acciones">
          <a class="btn btn-primary" href="../almuerzos/pdf_pedidos.php?fecha=<?= $h($diaAlm) ?>" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf"></i> Descargar pedidos del día</a>
          <a class="btn btn-outline" href="../almuerzos/pdf_menu_mes.php?mes=<?= $fechaAlm->format('Y-m') ?>" target="_blank" rel="noopener"><i class="bi bi-calendar3"></i> Menú del mes (PDF)</a>
        </div>

        <details class="ag-detalle" open>
          <summary>Quién pidió qué <small>(<?= $totalPersonas ?>)</small></summary>
          <div class="ag-filtro" role="group" aria-label="Filtrar pedidos">
            <button type="button" class="pill active" data-filtro="">Todos</button>
            <?php foreach ($etiquetas as $k => $etq): if (!$conteo[$k]) continue; ?>
              <button type="button" class="pill" data-filtro="<?= $k ?>"><?= $etq ?> (<?= (int) $conteo[$k] ?>)</button>
            <?php endforeach; ?>
          </div>
          <div class="table-wrap">
            <table class="ag-tabla">
              <thead><tr><th>Persona</th><th>Pedido</th><th>Hora</th></tr></thead>
              <tbody>
              <?php foreach ($pedidos as $p): $k = $p['pedido'] === '' ? 'sin' : $p['pedido']; ?>
                <tr data-pedido="<?= $h($k) ?>"><td><?= $h($p['nombre']) ?></td><td><span class="ag-tag is-<?= $h($k) ?>"><?= $h($etiquetas[$k] ?? $k) ?></span></td><td><?= $h($p['hora'] ?: '—') ?></td></tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </details>
      <?php else: ?>
        <div class="ag-acciones">
          <a class="btn btn-outline" href="../almuerzos/pdf_menu_mes.php?mes=<?= $fechaAlm->format('Y-m') ?>" target="_blank" rel="noopener"><i class="bi bi-calendar3"></i> Menú del mes (PDF)</a>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if ($verPedidos && $semana): ?>
      <div class="ag-semana">
        <h3>Esta semana</h3>
        <table class="ag-tabla is-compacta">
          <thead><tr><th>Día</th><th>Menú</th><th>Hipo</th><th>Picnic</th><th>No</th><th>Sin resp.</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($semana as $k => $d): $c = $d['conteo']; ?>
            <tr class="<?= $k === $diaAlm ? 'is-actual' : '' ?>">
              <td><?= $h(ucfirst($diasSemana[(int) $d['fecha']->format('N')]) . ' ' . $d['fecha']->format('j')) ?></td>
              <?php if ($c): ?>
                <td><?= (int) $c['menu'] ?></td><td><?= (int) $c['hipo'] ?></td><td><?= (int) $c['picnic'] ?></td><td><?= (int) $c['no'] ?></td><td><?= (int) $c['sin'] ?></td>
                <td><a href="?fecha=<?= $h($k) ?>" class="ag-link">Ver</a> · <a href="../almuerzos/pdf_pedidos.php?fecha=<?= $h($k) ?>" target="_blank" rel="noopener" class="ag-link">PDF</a></td>
              <?php else: ?>
                <td colspan="6" class="text-muted">Sin servicio</td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>

  <!-- =============================== EVENTOS =============================== -->
  <section class="ag-card ag-eventos" aria-labelledby="ag-ev-tit">
    <header class="ag-card__head">
      <div>
        <h2 id="ag-ev-tit"><i class="bi bi-calendar-event"></i> Próximos eventos</h2>
        <span>Hoy y los próximos <?= $diasEventos ?> días</span>
      </div>
      <a class="btn btn-outline btn-sm" href="../eventos/index.php">Ver calendario</a>
    </header>

    <?php if ($errorEventos): ?>
      <p class="ag-vacio"><?= $h($errorEventos) ?></p>
    <?php elseif (!$eventosPorDia): ?>
      <p class="ag-vacio">No hay eventos en los próximos <?= $diasEventos ?> días.</p>
    <?php endif; ?>

    <?php foreach ($eventosPorDia as $dia => $lista):
      $d = DateTimeImmutable::createFromFormat('!Y-m-d', $dia, $zona);
      $esHoy = $dia === $hoy->format('Y-m-d');
    ?>
      <h3 class="ag-dia<?= $esHoy ? ' is-hoy' : '' ?>"><?= $esHoy ? 'Hoy · ' : '' ?><?= $h($fechaLarga($d)) ?></h3>
      <?php foreach ($lista as $e):
        $todo = (int) ($e['todo_el_dia'] ?? 0) === 1 || !$e['hora_inicio'];
        $hora = $todo ? 'Todo el día' : substr((string) $e['hora_inicio'], 0, 5) . ($e['hora_fin'] ? '–' . substr((string) $e['hora_fin'], 0, 5) : '');
        $logistica = array_filter([
            !empty($e['con_audio']) ? '<i class="bi bi-volume-up"></i> Audio' : '',
            !empty($e['solo_presentacion']) ? '<i class="bi bi-easel"></i> Presentación' : '',
            !empty($e['musica_ambiental']) ? '<i class="bi bi-music-note-beamed"></i> Música ambiental' : '',
            (int) ($e['cantidad_personas'] ?? 0) > 0 ? '<i class="bi bi-people"></i> ' . (int) $e['cantidad_personas'] . ' personas' : '',
        ]);
        $tipo = Eventos::TIPOS[$e['tipo_evento']][0] ?? ucfirst((string) $e['tipo_evento']);
      ?>
        <article class="ag-evento" style="--ev: <?= $h($e['_color']) ?>">
          <div class="ag-evento__hora"><?= $h($hora) ?></div>
          <div class="ag-evento__cuerpo">
            <h4><a href="../eventos/index.php?evento=<?= (int) $e['id'] ?>"><?= $h($e['titulo']) ?></a></h4>
            <p class="ag-evento__meta">
              <span class="ag-tag is-tipo"><?= $h($tipo) ?></span>
              <?php if (trim((string) $e['ubicacion']) !== ''): ?><span><i class="bi bi-geo-alt"></i> <?= $h($e['ubicacion']) ?></span><?php endif; ?>
              <?php if (!empty($e['nom_colegio'])): ?><span><i class="bi bi-building"></i> <?= $h($e['nom_colegio']) ?></span><?php endif; ?>
              <?php if (!empty($e['responsable'])): ?><span><i class="bi bi-person"></i> <?= $h($e['responsable']) ?></span><?php endif; ?>
            </p>
            <?php if ($logistica): ?><p class="ag-evento__log"><?= implode('<span class="sep">·</span>', $logistica) ?></p><?php endif; ?>
            <?php if (trim((string) ($e['observaciones_logisticas'] ?? '')) !== ''): ?><p class="ag-evento__obs"><?= nl2br($h($e['observaciones_logisticas'])) ?></p><?php endif; ?>
            <?php if ($e['invitados']): ?>
              <p class="ag-evento__inv"><i class="bi bi-envelope-check"></i> <?= count($e['invitados']) ?> invitados: <b class="is-si"><?= $e['_si'] ?> sí</b> · <b class="is-no"><?= $e['_no'] ?> no</b> · <?= $e['_pend'] ?> sin responder</p>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </section>
</div>

<script>
document.querySelectorAll('.ag-filtro .pill').forEach(function (b) {
  b.addEventListener('click', function () {
    document.querySelectorAll('.ag-filtro .pill').forEach(function (x) { x.classList.toggle('active', x === b); });
    var f = b.dataset.filtro;
    document.querySelectorAll('.ag-tabla tbody tr[data-pedido]').forEach(function (tr) {
      tr.hidden = f !== '' && tr.dataset.pedido !== f;
    });
  });
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
