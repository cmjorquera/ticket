<?php
/**
 * Dashboard de inventario · VISTA DE UN COLEGIO.
 * Responde "¿cómo está este colegio y qué hay que hacer ahí?": cabecera con
 * los colores del colegio (tabla colegio), indicadores comparados con la red,
 * dónde están los equipos, estado y tipo, colegio vs. red, últimos
 * movimientos y pendientes (con "Avisar" = alertas por correo existentes).
 *
 * Usa (de dashboard.php): $idColegioFiltro, $colegioSeleccionado, $resumenSistema,
 * $kpis, $alertas, $alertasSistema, $dashboard, $ubicacionesColegio,
 * $movimientosColegio, $coloresActivos, $tipoSeleccionado, $alertaMeta, $inventario.
 */

$plural = (string)$tipoSeleccionado['plural'];
$pluralMin = $plural === mb_strtoupper($plural) ? $plural : mb_strtolower($plural);
$pct = static fn(int $parte, int $total): int => $total > 0 ? (int)round($parte * 100 / $total) : 0;
$hex = static fn($v, string $f): string => preg_match('/^#[0-9a-fA-F]{3,8}$/', (string)$v) ? (string)$v : $f;

// --- Colores y escudo del colegio --------------------------------------
$cPrin = $hex($coloresActivos['color_principal'] ?? null, '#16365c');
$cSec = $hex($coloresActivos['color_secundario'] ?? null, $cPrin);
$escudo = inv_dash_logo_colegio($idColegioFiltro);
$corto = trim(preg_replace('/^colegio\s+/iu', '', (string)$colegioSeleccionado));
$pal = preg_split('/\s+/u', preg_replace('/\b(de|del|la|las|los|el|y)\b/iu', '', $corto), -1, PREG_SPLIT_NO_EMPTY) ?: [$corto];
$iniciales = mb_strtoupper(mb_substr($pal[0], 0, 1) . (isset($pal[1]) ? mb_substr($pal[1], 0, 1) : mb_substr($pal[0], 1, 1)));

// --- Este colegio -----------------------------------------------------------
$total = (int)($kpis['total_pcs'] ?? 0);
$activos = (int)($kpis['activos'] ?? 0);
$reparacion = (int)($kpis['en_reparacion'] ?? 0);
$baja = (int)($kpis['dados_baja'] ?? 0);
$fuera = $reparacion + $baja;
$sinResp = (int)($kpis['sin_responsable'] ?? 0);
$sinUbic = (int)($kpis['sin_ubicacion'] ?? 0);
$sinValor = (int)($alertas['sin_valor'] ?? 0);
$sinFoto = (int)($alertas['sin_fotografia'] ?? 0);

// --- La red (todos los colegios visibles con equipos) -----------------------
$red = ['total' => 0, 'activos' => 0, 'sin_ubicacion' => 0, 'sin_responsable' => 0, 'con_valor' => 0, 'colegios' => 0];
$ranking = [];
foreach ($resumenSistema as $f) {
    $t = (int)($f['total_equipos'] ?? 0);
    if ($t <= 0) {
        continue;
    }
    $red['colegios']++;
    $red['total'] += $t;
    $red['activos'] += (int)($f['activos'] ?? 0);
    $red['sin_ubicacion'] += (int)($f['sin_ubicacion'] ?? 0);
    $red['sin_responsable'] += (int)($f['sin_responsable'] ?? 0);
    $red['con_valor'] += (int)($f['con_valor'] ?? 0);
    $ranking[(int)$f['id_colegio']] = $t;
}
arsort($ranking);
$posicion = array_search($idColegioFiltro, array_keys($ranking), true);
$redSinFoto = (int)($alertasSistema['sin_fotografia'] ?? 0);

$pc = [
    'ubicacion' => $pct($total - $sinUbic, $total),
    'responsable' => $pct($total - $sinResp, $total),
    'valor' => $pct($total - $sinValor, $total),
    'foto' => $pct($total - $sinFoto, $total),
    'operativos' => $pct($activos, $total),
];
$pr = [
    'ubicacion' => $pct($red['total'] - $red['sin_ubicacion'], $red['total']),
    'responsable' => $pct($red['total'] - $red['sin_responsable'], $red['total']),
    'valor' => $pct($red['con_valor'], $red['total']),
    'foto' => $pct($red['total'] - $redSinFoto, $red['total']),
    'operativos' => $pct($red['activos'], $red['total']),
];
// Calidad 0–100: promedio de qué tan completos están los 4 datos clave
$calidad = (int)round(($pc['ubicacion'] + $pc['responsable'] + $pc['valor'] + $pc['foto']) / 4);

/** Diferencia en puntos vs. la red. $masEsMejor: true si un % más alto es bueno. */
$delta = static function (int $propio, int $redPct, bool $masEsMejor): string {
    $d = $propio - $redPct;
    if ($d === 0) {
        return '<span class="is-eq">= igual que la red (' . $redPct . '%)</span>';
    }
    $mejor = $masEsMejor ? $d > 0 : $d < 0;
    return '<span class="' . ($mejor ? 'is-good' : 'is-bad') . '">' . ($d > 0 ? '▲' : '▼') . ' ' . abs($d) . ' pts</span> '
        . ($mejor ? 'mejor' : 'peor') . ' que la red (' . $redPct . '%)';
};

// --- Ubicaciones --------------------------------------------------------------
$ubicaciones = [];
$sinUbicFila = 0;
foreach ($ubicacionesColegio as $u) {
    if ($u['nombre'] === null) {
        $sinUbicFila += $u['total'];
    } else {
        $ubicaciones[] = $u;
    }
}
$maxUbic = max(1, $sinUbicFila, ...array_map(static fn($u) => $u['total'], $ubicaciones ?: [['total' => 1]]));
// Hasta 7 filas; si hay más, las menores se agrupan en "Otras"
$otras = count($ubicaciones) > 7 ? array_slice($ubicaciones, 6) : [];
$ubicaciones = $otras ? array_slice($ubicaciones, 0, 6) : $ubicaciones;
if ($otras) {
    $ubicaciones[] = ['nombre' => 'Otras (' . count($otras) . ' ubicaciones)', 'total' => array_sum(array_column($otras, 'total'))];
}

// --- Estados y tipos ------------------------------------------------------------
$colorBadge = ['success' => '#1f8a4c', 'danger' => '#c0392b', 'warning' => '#e67e22', 'info' => '#2b6cb0', 'secondary' => '#d9a12a', 'primary' => '#16365c', 'dark' => '#374151'];
$estados = [];
$gradiente = [];
$sumaE = max(1, array_sum(array_map(static fn($e) => (int)($e['total'] ?? 0), $dashboard['estados'] ?? [])));
$acum = 0;
foreach (($dashboard['estados'] ?? []) as $e) {
    $n = (int)($e['total'] ?? 0);
    if ($n <= 0) {
        continue;
    }
    $color = $colorBadge[(string)($e['color_badge'] ?? '')] ?? '#9aa5b1';
    $gradiente[] = sprintf('%s %.2f%% %.2f%%', $color, $acum * 100 / $sumaE, ($acum + $n) * 100 / $sumaE);
    $acum += $n;
    $estados[] = ['etiqueta' => (string)($e['etiqueta'] ?? 'Sin estado'), 'total' => $n, 'color' => $color];
}
$tipos = array_values(array_filter($dashboard['tipos'] ?? [], static fn($t) => (int)($t['total'] ?? 0) > 0));
$sumaT = max(1, array_sum(array_map(static fn($t) => (int)$t['total'], $tipos)));
$coloresTipo = [$cPrin, $cSec, '#9aa5b1', '#d9a12a', '#0f766e'];

// --- Pendientes ------------------------------------------------------------------
$pendientes = [];
$addPend = static function (int $n, string $texto, string $alerta = '') use (&$pendientes, $total) {
    if ($n > 0) {
        $pendientes[] = ['n' => $n, 'texto' => $texto, 'alerta' => $alerta, 'grave' => $total > 0 && $n * 2 >= $total];
    }
};
$addPend($sinValor, $plural . ' sin valor de compra', 'sin_valor');
$addPend($sinResp, $plural . ' sin responsable', 'sin_responsable');
$addPend($sinFoto, $plural . ' sin fotografía');
$addPend($sinUbic, $plural . ' sin ubicación', 'sin_ubicacion');
$addPend($reparacion, $plural . ' en reparación', 'en_reparacion');
$addPend((int)($alertas['sin_qr'] ?? 0), $plural . ' sin código QR');
$addPend((int)($alertas['sin_serie'] ?? 0), $plural . ' sin número de serie');
usort($pendientes, static fn($a, $b) => $b['n'] <=> $a['n']);
$pendientes = array_slice($pendientes, 0, 5);

$tiposMov = ['ubicacion' => ['Ubicación', 'is-blue'], 'estado' => ['Estado', 'is-red'], 'responsable' => ['Responsable', 'is-green']];
$fmtFecha = static function ($f): string {
    $t = strtotime((string)$f);
    return $t ? date('d-m H:i', $t) : '—';
};
?>
<div class="inv-gen inv-col" style="--col-1: <?= inventario_h($cPrin) ?>; --col-2: <?= inventario_h($cSec) ?>">

    <div class="inv-col-hero">
        <div class="inv-col-hero__logo">
            <?php if ($escudo): ?><img src="<?= inventario_h($escudo) ?>" alt=""><?php else: ?><?= inventario_h($iniciales) ?><?php endif; ?>
        </div>
        <div class="inv-col-hero__txt">
            <h2><?= inventario_h($colegioSeleccionado) ?></h2>
            <p><?= inv_dash_int($total) ?> <?= inventario_h($pluralMin) ?><?php if ($red['total'] > 0 && $red['colegios'] > 1): ?> · <?= $pct($total, $red['total']) ?>% de los equipos de la red<?php endif; ?> · actualizado <?= date('d-m-Y H:i') ?></p>
        </div>
        <div class="inv-col-hero__pills">
            <?php if ($posicion !== false && $red['colegios'] > 1): ?>
            <div><b><?= $posicion + 1 ?>° de <?= $red['colegios'] ?></b><span>en cantidad de <?= inventario_h($pluralMin) ?></span></div>
            <?php endif; ?>
            <div title="Promedio de % con ubicación, responsable, valor de compra y fotografía"><b><?= $calidad ?> / 100</b><span>calidad del inventario</span></div>
        </div>
    </div>

    <div class="inv-gen-kpis">
        <div class="inv-gen-kpi inv-col-kpi">
            <span class="inv-gen-kpi__l">Total de <?= inventario_h($plural) ?></span>
            <strong><?= inv_dash_int($total) ?></strong>
            <small><?php if ($red['colegios'] > 1): ?>Promedio por colegio: <?= inv_dash_int((int)round($red['total'] / $red['colegios'])) ?><?php else: ?>&nbsp;<?php endif; ?></small>
        </div>
        <div class="inv-gen-kpi is-ok">
            <span class="inv-gen-kpi__l">Operativos</span>
            <strong><?= inv_dash_int($activos) ?><em><?= $pc['operativos'] ?>%</em></strong>
            <small><?= $red['colegios'] > 1 ? $delta($pc['operativos'], $pr['operativos'], true) : 'Estado Activo' ?></small>
        </div>
        <div class="inv-gen-kpi is-bad">
            <span class="inv-gen-kpi__l">Fuera de servicio</span>
            <strong><?= inv_dash_int($fuera) ?><em><?= $pct($fuera, $total) ?>%</em></strong>
            <small><?= inv_dash_int($reparacion) ?> en reparación · <?= inv_dash_int($baja) ?> de baja</small>
        </div>
        <div class="inv-gen-kpi is-mid">
            <span class="inv-gen-kpi__l">Sin responsable</span>
            <strong><?= inv_dash_int($sinResp) ?><em><?= $pct($sinResp, $total) ?>%</em></strong>
            <small><?= $red['colegios'] > 1 ? $delta($pct($sinResp, $total), 100 - $pr['responsable'], false) : 'Nadie asignado' ?></small>
        </div>
        <div class="inv-gen-kpi is-mid">
            <span class="inv-gen-kpi__l">Sin ubicación</span>
            <strong><?= inv_dash_int($sinUbic) ?><em><?= $pct($sinUbic, $total) ?>%</em></strong>
            <small><?= $red['colegios'] > 1 ? $delta($pct($sinUbic, $total), 100 - $pr['ubicacion'], false) : 'No se sabe en qué sala están' ?></small>
        </div>
    </div>

    <div class="inv-gen-row inv-col-row3">
        <section class="inv-gen-card">
            <header>
                <h2>¿Dónde están?</h2>
                <span><?= inventario_h($plural) ?> por ubicación del colegio (salas, departamentos)</span>
            </header>
            <div class="inv-gen-body">
                <?php if (!$ubicaciones && !$sinUbicFila): ?>
                    <div class="inv-empty-state">Sin <?= inventario_h($pluralMin) ?> registrados en este colegio.</div>
                <?php endif; ?>
                <?php foreach ($ubicaciones as $u): ?>
                    <div class="inv-col-ub"><span><?= inventario_h($u['nombre']) ?></span><span class="inv-col-ub__t"><i style="width: <?= round($u['total'] * 100 / $maxUbic, 2) ?>%"></i></span><b><?= inv_dash_int($u['total']) ?></b></div>
                <?php endforeach; ?>
                <?php if ($sinUbicFila > 0): ?>
                    <div class="inv-col-ub is-pend"><span>Sin ubicación</span><span class="inv-col-ub__t"><i style="width: <?= round($sinUbicFila * 100 / $maxUbic, 2) ?>%"></i></span><b><?= inv_dash_int($sinUbicFila) ?></b></div>
                <?php endif; ?>
            </div>
        </section>

        <section class="inv-gen-card">
            <header>
                <h2>Estado y tipo</h2>
                <span>Situación actual de los equipos</span>
            </header>
            <div class="inv-gen-body">
                <div class="inv-col-estado">
                    <div class="inv-gen-donut is-sm" style="background: conic-gradient(<?= $gradiente ? implode(', ', $gradiente) : '#e5e7eb 0 100%' ?>)">
                        <b><?= $pc['operativos'] ?>%<small>operativos</small></b>
                    </div>
                    <div class="inv-col-lista">
                        <?php foreach ($estados as $e): ?>
                            <div><span><i style="background: <?= $e['color'] ?>"></i><?= inventario_h($e['etiqueta']) ?></span><b><?= inv_dash_int($e['total']) ?></b></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if ($tipos): ?>
                <div class="inv-gen-stack inv-col-stack">
                    <?php foreach ($tipos as $i => $t): ?>
                        <span style="width: <?= round((int)$t['total'] * 100 / $sumaT, 2) ?>%; background: <?= inventario_h($coloresTipo[$i % count($coloresTipo)]) ?>" title="<?= inventario_h($t['etiqueta']) ?>: <?= (int)$t['total'] ?>"></span>
                    <?php endforeach; ?>
                </div>
                <div class="inv-gen-legend">
                    <?php foreach ($tipos as $i => $t): ?>
                        <span><i style="background: <?= inventario_h($coloresTipo[$i % count($coloresTipo)]) ?>"></i><?= inventario_h($t['etiqueta']) ?> <?= inv_dash_int($t['total']) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="inv-gen-card">
            <header>
                <h2><?= inventario_h($corto) ?><?= $red['colegios'] > 1 ? ' vs. la red' : ': datos completos' ?></h2>
                <span>% de <?= inventario_h($pluralMin) ?> con cada dato<?= $red['colegios'] > 1 ? ' · la marca negra es el promedio de la red' : '' ?></span>
            </header>
            <div class="inv-gen-body">
                <?php foreach (['ubicacion' => 'Con ubicación', 'responsable' => 'Con responsable', 'valor' => 'Con valor de compra', 'foto' => 'Con fotografía', 'operativos' => 'Operativos'] as $clave => $etq):
                    $v = $pc[$clave];
                    $clase = $v >= 80 ? 'is-ok' : ($v >= 40 ? 'is-mid' : 'is-bad');
                ?>
                    <div class="inv-col-cmp">
                        <span><?= $etq ?></span>
                        <span class="inv-col-cmp__t <?= $clase ?>"><i style="width: <?= $v ?>%"></i><?php if ($red['colegios'] > 1): ?><u style="left: <?= $pr[$clave] ?>%" title="Red: <?= $pr[$clave] ?>%"></u><?php endif; ?></span>
                        <b><?= $v ?>%</b>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <div class="inv-gen-row inv-gen-row--32">
        <section class="inv-gen-card">
            <header>
                <h2>Últimos movimientos</h2>
                <span>Cambios de ubicación, de estado y de responsable en este colegio</span>
            </header>
            <?php if (!$movimientosColegio): ?>
                <div class="inv-gen-body"><div class="inv-empty-state">Aún no hay movimientos registrados en este colegio.</div></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table inv-gen-table align-middle">
                    <thead><tr><th>Fecha</th><th>Equipo</th><th>Qué pasó</th><th>Quién</th></tr></thead>
                    <tbody>
                    <?php foreach ($movimientosColegio as $m):
                        [$etq, $cls] = $tiposMov[$m['tipo']] ?? ['Cambio', 'is-blue'];
                        $vacio = ['responsable' => 'Sin asignar', 'ubicacion' => 'Sin ubicación', 'estado' => 'Sin estado'][$m['tipo']] ?? 'Sin dato';
                        $desde = trim((string)($m['desde'] ?? '')) ?: $vacio;
                        $hacia = trim((string)($m['hacia'] ?? '')) ?: $vacio;
                    ?>
                        <tr>
                            <td class="text-nowrap"><?= inventario_h($fmtFecha($m['fecha'])) ?></td>
                            <td><?= inventario_h($m['equipo'] ?? '') ?></td>
                            <td><span class="inv-col-tag <?= $cls ?>"><?= $etq ?></span> <?= inventario_h($desde) ?> → <?= inventario_h($hacia) ?></td>
                            <td><?= inventario_h(trim((string)($m['quien'] ?? '')) ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>

        <section class="inv-gen-card">
            <header>
                <h2>Pendientes de <?= inventario_h($corto) ?></h2>
                <span>Qué falta completar y a quién avisar</span>
            </header>
            <div class="inv-gen-body">
                <?php if (!$pendientes): ?>
                    <div class="inv-empty-state">Sin pendientes. El inventario de este colegio está completo.</div>
                <?php endif; ?>
                <?php foreach ($pendientes as $p): ?>
                    <div class="inv-gen-alert<?= $p['grave'] ? ' is-bad' : '' ?>">
                        <b><?= inv_dash_int($p['n']) ?></b>
                        <div><?= inventario_h($p['texto']) ?></div>
                        <?php if ($p['alerta'] !== '' && isset($alertaMeta[$p['alerta']])): ?>
                            <button type="button" class="inv-col-avisar inv-dashboard-alert-action"
                                    data-alert-type="<?= inventario_h($p['alerta']) ?>" data-alert-total="<?= (int)$p['n'] ?>"
                                    title="Enviar un correo a los responsables">Avisar <i class="bi bi-envelope"></i></button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if ($pendientes): ?><small class="inv-col-nota">"Avisar" envía un correo a los responsables del colegio.</small><?php endif; ?>
            </div>
        </section>
    </div>
</div>
