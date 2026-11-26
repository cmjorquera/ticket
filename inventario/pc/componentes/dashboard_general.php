<?php
/**
 * Dashboard de inventario · VISTA GENERAL ("Todos los colegios").
 * Pensada para gerencia / super_admin: cuántos equipos hay y dónde, cuántos
 * funcionan y qué tan completo está el inventario de cada colegio.
 * Se incluye desde dashboard.php cuando no hay un colegio filtrado y la
 * persona ve más de un colegio. Con un colegio elegido se usa la vista detallada.
 *
 * Usa (de dashboard.php): $resumenSistema (una fila por colegio, incluye con_valor),
 * $kpis, $dashboard['estados'], $dashboard['tipos'], $alertas, $tipoSeleccionado,
 * $tipoEquipoFiltro, $inventario. Colores: color_principal de la tabla colegio.
 */

$plural = (string)$tipoSeleccionado['plural'];
// "PC" queda en mayúsculas dentro de una frase; "Monitores" → "monitores"
$pluralMin = $plural === mb_strtoupper($plural) ? $plural : mb_strtolower($plural);
$pct = static fn(int $parte, int $total): int => $total > 0 ? (int)round($parte * 100 / $total) : 0;

// --- Colegios: con y sin inventario -------------------------------------
$colegiosConEquipos = [];
$colegiosSinEquipos = [];
foreach ($resumenSistema as $fila) {
    $id = (int)($fila['id_colegio'] ?? 0);
    $colores = inv_dash_colores_colegio($inventario, $id, '#5b6b7f', '#9aa5b1');
    $nombre = (string)($fila['nom_colegio'] ?? '');
    $corto = trim(preg_replace('/^colegio\s+/iu', '', $nombre));
    $palabras = preg_split('/\s+/u', preg_replace('/\b(de|del|la|las|los|el|y)\b/iu', '', $corto), -1, PREG_SPLIT_NO_EMPTY) ?: [$corto];
    $iniciales = mb_strtoupper(mb_substr($palabras[0], 0, 1) . (isset($palabras[1]) ? mb_substr($palabras[1], 0, 1) : mb_substr($palabras[0], 1, 1)));
    $total = (int)($fila['total_equipos'] ?? 0);
    $dato = [
        'id' => $id,
        'nombre' => $nombre,
        'corto' => $corto,
        'iniciales' => $iniciales,
        'color' => $colores['principal'],
        'total' => $total,
        'activos' => (int)($fila['activos'] ?? 0),
        'fuera' => (int)($fila['en_reparacion'] ?? 0) + (int)($fila['dados_baja'] ?? 0),
        'sin_ubicacion' => (int)($fila['sin_ubicacion'] ?? 0),
        'sin_responsable' => (int)($fila['sin_responsable'] ?? 0),
        'con_valor' => (int)($fila['con_valor'] ?? 0),
    ];
    if ($total > 0) {
        $colegiosConEquipos[] = $dato;
    } else {
        $colegiosSinEquipos[] = $dato;
    }
}
usort($colegiosConEquipos, static fn($a, $b) => $b['total'] <=> $a['total']);

// --- Totales --------------------------------------------------------------
$tTotal = (int)($kpis['total_pcs'] ?? 0);
$tActivos = (int)($kpis['activos'] ?? 0);
$tReparacion = (int)($kpis['en_reparacion'] ?? 0);
$tBaja = (int)($kpis['dados_baja'] ?? 0);
$tFuera = $tReparacion + $tBaja;
$tSinResp = (int)($kpis['sin_responsable'] ?? 0);
$tSinUbic = (int)($kpis['sin_ubicacion'] ?? 0);
$tConValor = array_sum(array_column($colegiosConEquipos, 'con_valor'));
$maxColegio = max(1, ...array_map(static fn($c) => $c['total'], $colegiosConEquipos ?: [['total' => 1]]));
$nColegios = count($colegiosConEquipos) + count($colegiosSinEquipos);

// --- Estados (color según color_badge de estado_equipo) -------------------
$colorBadge = ['success' => '#1f8a4c', 'danger' => '#c0392b', 'warning' => '#e67e22', 'info' => '#2b6cb0', 'secondary' => '#d9a12a', 'primary' => '#16365c', 'dark' => '#374151'];
$estados = [];
$acumulado = 0;
$sumaEstados = max(1, array_sum(array_map(static fn($e) => (int)($e['total'] ?? 0), $dashboard['estados'] ?? [])));
$gradiente = [];
foreach (($dashboard['estados'] ?? []) as $e) {
    $n = (int)($e['total'] ?? 0);
    if ($n <= 0) {
        continue;
    }
    $color = $colorBadge[(string)($e['color_badge'] ?? '')] ?? '#9aa5b1';
    $desde = $acumulado * 100 / $sumaEstados;
    $acumulado += $n;
    $hasta = $acumulado * 100 / $sumaEstados;
    $gradiente[] = sprintf('%s %.2f%% %.2f%%', $color, $desde, $hasta);
    $estados[] = ['etiqueta' => (string)($e['etiqueta'] ?? 'Sin estado'), 'total' => $n, 'color' => $color];
}

// --- Tipo de equipo ---------------------------------------------------------
$coloresTipo = ['#16365c', '#3f6ea6', '#9aa5b1', '#d9a12a', '#0f766e'];
$tipos = array_values(array_filter($dashboard['tipos'] ?? [], static fn($t) => (int)($t['total'] ?? 0) > 0));
$sumaTipos = max(1, array_sum(array_map(static fn($t) => (int)$t['total'], $tipos)));

// --- Qué corregir primero ---------------------------------------------------
$pendientes = [];
$agregar = static function (int $n, string $texto, string $detalle = '') use (&$pendientes, $tTotal) {
    if ($n > 0) {
        $pendientes[] = ['n' => $n, 'texto' => $texto, 'detalle' => $detalle, 'grave' => $tTotal > 0 && $n * 2 >= $tTotal];
    }
};
$agregar((int)($alertas['sin_valor'] ?? 0), $plural . ' sin valor de compra', 'Sin este dato no se puede calcular el valor del inventario');
$peorUbic = null;
$peorResp = null;
foreach ($colegiosConEquipos as $c) {
    if ($c['sin_ubicacion'] > 0 && ($peorUbic === null || $c['sin_ubicacion'] > $peorUbic['sin_ubicacion'])) {
        $peorUbic = $c;
    }
    if ($c['sin_responsable'] > 0 && ($peorResp === null || $c['sin_responsable'] > $peorResp['sin_responsable'])) {
        $peorResp = $c;
    }
}
if ($peorUbic) {
    $agregar($peorUbic['sin_ubicacion'], $plural . ' de ' . $peorUbic['corto'] . ' sin ubicación', 'El colegio con más equipos sin ubicación');
}
if ($peorResp) {
    $agregar($peorResp['sin_responsable'], $plural . ' de ' . $peorResp['corto'] . ' sin responsable', 'El colegio con más equipos sin responsable');
}
$agregar((int)($alertas['sin_fotografia'] ?? 0), $plural . ' sin fotografía');
$agregar((int)($alertas['sin_qr'] ?? 0), $plural . ' sin código QR');
$agregar((int)($alertas['sin_serie'] ?? 0), $plural . ' sin número de serie');
$agregar(count($colegiosSinEquipos), count($colegiosSinEquipos) === 1 ? 'Colegio sin inventario cargado' : 'Colegios sin inventario cargado');
usort($pendientes, static fn($a, $b) => $b['n'] <=> $a['n']);
$pendientes = array_slice($pendientes, 0, 5);

$semaforo = static function (int $p): string {
    return $p >= 80 ? 'is-ok' : ($p >= 40 ? 'is-mid' : 'is-bad');
};
$urlColegio = static fn(int $id): string => 'dashboard.php?id_colegio=' . $id . '&tipo_equipo=' . rawurlencode((string)$tipoEquipoFiltro);
?>
<div class="inv-gen">

    <div class="inv-gen-kpis">
        <div class="inv-gen-kpi">
            <span class="inv-gen-kpi__l">Total de <?= inventario_h($plural) ?></span>
            <strong><?= inv_dash_int($tTotal) ?></strong>
            <small>En <?= count($colegiosConEquipos) ?> de <?= $nColegios ?> colegios</small>
        </div>
        <div class="inv-gen-kpi is-ok">
            <span class="inv-gen-kpi__l">Operativos</span>
            <strong><?= inv_dash_int($tActivos) ?><em><?= $pct($tActivos, $tTotal) ?>%</em></strong>
            <small>Estado Activo</small>
        </div>
        <div class="inv-gen-kpi is-bad">
            <span class="inv-gen-kpi__l">Fuera de servicio</span>
            <strong><?= inv_dash_int($tFuera) ?><em><?= $pct($tFuera, $tTotal) ?>%</em></strong>
            <small><?= inv_dash_int($tReparacion) ?> en reparación · <?= inv_dash_int($tBaja) ?> de baja</small>
        </div>
        <div class="inv-gen-kpi is-mid">
            <span class="inv-gen-kpi__l">Sin responsable</span>
            <strong><?= inv_dash_int($tSinResp) ?><em><?= $pct($tSinResp, $tTotal) ?>%</em></strong>
            <small>Nadie asignado</small>
        </div>
        <div class="inv-gen-kpi is-mid">
            <span class="inv-gen-kpi__l">Sin ubicación</span>
            <strong><?= inv_dash_int($tSinUbic) ?><em><?= $pct($tSinUbic, $tTotal) ?>%</em></strong>
            <small>No se sabe en qué sala están</small>
        </div>
    </div>

    <div class="inv-gen-row inv-gen-row--21">
        <section class="inv-gen-card">
            <header>
                <h2><?= inventario_h($plural) ?> por colegio</h2>
                <span>Cantidad de equipos y su estado · clic en un colegio para ver su detalle</span>
            </header>
            <div class="inv-gen-body">
                <?php if (!$colegiosConEquipos): ?>
                    <div class="inv-empty-state">Aún no hay <?= inventario_h($pluralMin) ?> registrados.</div>
                <?php endif; ?>
                <?php foreach ($colegiosConEquipos as $c):
                    $otros = max(0, $c['total'] - $c['activos'] - $c['fuera']);
                ?>
                <a class="inv-gen-bar" href="<?= inventario_h($urlColegio($c['id'])) ?>" title="Ver el detalle de <?= inventario_h($c['nombre']) ?>">
                    <span class="inv-gen-bar__name">
                        <span class="inv-gen-dot" style="background: <?= inventario_h($c['color']) ?>"><?= inventario_h($c['iniciales']) ?></span>
                        <?= inventario_h($c['nombre']) ?>
                    </span>
                    <span class="inv-gen-bar__track">
                        <span style="width: <?= round($c['activos'] * 100 / $maxColegio, 2) ?>%; background: <?= inventario_h($c['color']) ?>"></span>
                        <?php if ($otros > 0): ?><span style="width: <?= round($otros * 100 / $maxColegio, 2) ?>%; background: <?= inventario_h($c['color']) ?>; opacity: .4"></span><?php endif; ?>
                        <?php if ($c['fuera'] > 0): ?><span class="is-fuera" style="width: <?= max(0.8, round($c['fuera'] * 100 / $maxColegio, 2)) ?>%"></span><?php endif; ?>
                    </span>
                    <span class="inv-gen-bar__num">
                        <strong><?= inv_dash_int($c['total']) ?></strong>
                        <small><?= $pct($c['total'], $tTotal) ?>% · <?= inv_dash_int($c['fuera']) ?> fuera</small>
                    </span>
                </a>
                <?php endforeach; ?>
                <div class="inv-gen-legend">
                    <span><i class="is-col"></i>Operativos (color del colegio)</span>
                    <span><i class="is-col" style="opacity:.4"></i>Bodega / prestado</span>
                    <span><i class="is-fuera"></i>Fuera de servicio (reparación / baja)</span>
                </div>
                <?php if ($colegiosSinEquipos): ?>
                <div class="inv-gen-sin">
                    Colegios sin inventario cargado:
                    <?php foreach ($colegiosSinEquipos as $c): ?>
                        <span class="inv-gen-chip"><span class="inv-gen-dot is-sm" style="background: <?= inventario_h($c['color']) ?>"></span><?= inventario_h($c['corto']) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="inv-gen-card">
            <header>
                <h2>Estado general</h2>
                <span>Todos los <?= inventario_h($pluralMin) ?> visibles</span>
            </header>
            <div class="inv-gen-body">
                <div class="inv-gen-donut" style="background: conic-gradient(<?= $gradiente ? implode(', ', $gradiente) : '#e5e7eb 0 100%' ?>)">
                    <b><?= $pct($tActivos, $tTotal) ?>%<small>operativos</small></b>
                </div>
                <div class="inv-gen-estados">
                    <?php foreach ($estados as $e): ?>
                        <div><span><i style="background: <?= $e['color'] ?>"></i><?= inventario_h($e['etiqueta']) ?></span><b><?= inv_dash_int($e['total']) ?></b></div>
                    <?php endforeach; ?>
                </div>
                <?php if ($tipos): ?>
                <h3 class="inv-gen-sub">Tipo de equipo</h3>
                <div class="inv-gen-stack">
                    <?php foreach ($tipos as $i => $t): ?>
                        <span style="width: <?= round((int)$t['total'] * 100 / $sumaTipos, 2) ?>%; background: <?= $coloresTipo[$i % count($coloresTipo)] ?>" title="<?= inventario_h($t['etiqueta']) ?>: <?= (int)$t['total'] ?>"></span>
                    <?php endforeach; ?>
                </div>
                <div class="inv-gen-legend">
                    <?php foreach ($tipos as $i => $t): ?>
                        <span><i style="background: <?= $coloresTipo[$i % count($coloresTipo)] ?>"></i><?= inventario_h($t['etiqueta']) ?> <?= inv_dash_int($t['total']) ?> · <?= $pct((int)$t['total'], $sumaTipos) ?>%</span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="inv-gen-row inv-gen-row--32">
        <section class="inv-gen-card">
            <header>
                <h2>Calidad del inventario por colegio</h2>
                <span>% de equipos con cada dato completo · verde ≥ 80% · amarillo 40–79% · rojo &lt; 40%</span>
            </header>
            <div class="table-responsive">
                <table class="table inv-gen-table align-middle">
                    <thead><tr><th>Colegio</th><th><?= inventario_h($plural) ?></th><th>Con ubicación</th><th>Con responsable</th><th>Con valor de compra</th></tr></thead>
                    <tbody>
                    <?php foreach ($colegiosConEquipos as $c):
                        $pU = $pct($c['total'] - $c['sin_ubicacion'], $c['total']);
                        $pR = $pct($c['total'] - $c['sin_responsable'], $c['total']);
                        $pV = $pct($c['con_valor'], $c['total']);
                    ?>
                        <tr>
                            <td><a href="<?= inventario_h($urlColegio($c['id'])) ?>"><span class="inv-gen-dot is-sm" style="background: <?= inventario_h($c['color']) ?>"></span><?= inventario_h($c['corto']) ?></a></td>
                            <td><?= inv_dash_int($c['total']) ?></td>
                            <td><span class="inv-gen-pill <?= $semaforo($pU) ?>"><?= $pU ?>%</span></td>
                            <td><span class="inv-gen-pill <?= $semaforo($pR) ?>"><?= $pR ?>%</span></td>
                            <td><span class="inv-gen-pill <?= $semaforo($pV) ?>"><?= $pV ?>%</span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <?php if ($colegiosConEquipos):
                        $pU = $pct($tTotal - $tSinUbic, $tTotal);
                        $pR = $pct($tTotal - $tSinResp, $tTotal);
                        $pV = $pct($tConValor, $tTotal);
                    ?>
                    <tfoot><tr><td>Total</td><td><?= inv_dash_int($tTotal) ?></td>
                        <td><span class="inv-gen-pill <?= $semaforo($pU) ?>"><?= $pU ?>%</span></td>
                        <td><span class="inv-gen-pill <?= $semaforo($pR) ?>"><?= $pR ?>%</span></td>
                        <td><span class="inv-gen-pill <?= $semaforo($pV) ?>"><?= $pV ?>%</span></td></tr></tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </section>

        <section class="inv-gen-card">
            <header>
                <h2>Qué corregir primero</h2>
                <span>Pendientes con más equipos afectados</span>
            </header>
            <div class="inv-gen-body">
                <?php if (!$pendientes): ?>
                    <div class="inv-empty-state">Sin pendientes. El inventario está completo.</div>
                <?php endif; ?>
                <?php foreach ($pendientes as $p): ?>
                    <div class="inv-gen-alert<?= $p['grave'] ? ' is-bad' : '' ?>">
                        <b><?= inv_dash_int($p['n']) ?></b>
                        <div><?= inventario_h($p['texto']) ?><?php if ($p['detalle'] !== ''): ?><small><?= inventario_h($p['detalle']) ?></small><?php endif; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>
