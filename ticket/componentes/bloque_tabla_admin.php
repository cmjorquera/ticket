<?php
declare(strict_types=1);

/**
 * Control general de tickets.
 * Requiere: $tickets, $colegios, $tecnicos, $estadoFiltro, $colegioFiltro,
 * $errorCarga y $esGlobal.
 */
$tickets = isset($tickets) && is_array($tickets) ? $tickets : [];
$colegios = isset($colegios) && is_array($colegios) ? $colegios : [];
$tecnicos = isset($tecnicos) && is_array($tecnicos) ? $tecnicos : [];
$estadoFiltro = isset($estadoFiltro) ? (int) $estadoFiltro : 0;
$colegioFiltro = isset($colegioFiltro) ? (int) $colegioFiltro : 0;
$tecnicoFiltro = isset($tecnicoFiltro) ? (int) $tecnicoFiltro : 0;
$errorCarga = $errorCarga ?? null;
$esGlobal = !empty($esGlobal);
?>
<div id="ticket-tabla-admin">
  <section class="card">
    <div class="card-header"><div><h2 class="card-title">Control de casos</h2><p class="card-desc"><?= $esGlobal ? 'Vista general de todos los colegios.' : 'Vista limitada a tus colegios administrados.' ?></p></div></div>
    <form class="ticket-toolbar" method="get">
      <div class="form-group ticket-search"><label class="form-label" for="admin-buscar">Buscar en resultados</label><input class="form-input" id="admin-buscar" type="search" placeholder="Folio, asunto, solicitante o técnico"></div>
      <div class="form-group"><label class="form-label" for="admin-estado">Estado</label><select class="form-input" id="admin-estado" name="estado"><option value="0">Todos</option><?php foreach (ticket_estados_legacy() as $valor=>$info): ?><option value="<?= $valor ?>" <?= $estadoFiltro === $valor ? 'selected' : '' ?>><?= e($info['nombre']) ?></option><?php endforeach; ?></select></div>
      <div class="form-group"><label class="form-label" for="admin-colegio">Colegio</label><select class="form-input" id="admin-colegio" name="colegio"><option value="0">Todos</option><?php foreach ($colegios as $colegio): ?><option value="<?= (int) $colegio['id_colegio'] ?>" <?= $colegioFiltro === (int) $colegio['id_colegio'] ? 'selected' : '' ?>><?= e($colegio['nom_colegio']) ?></option><?php endforeach; ?></select></div>
      <div class="form-group"><label class="form-label" for="admin-tecnico">Técnico</label><select class="form-input" id="admin-tecnico" name="tecnico"><option value="0">Todos</option><?php foreach ($tecnicos as $tecnico): ?><option value="<?= (int) $tecnico['id'] ?>" <?= $tecnicoFiltro === (int) $tecnico['id'] ? 'selected' : '' ?>><?= e($tecnico['nombre']) ?></option><?php endforeach; ?></select></div>
      <button class="btn btn-outline" type="submit"><i class="bi bi-funnel"></i> Filtrar</button>
    </form>
    <?php if ($errorCarga): ?>
      <div class="ticket-empty"><i class="bi bi-exclamation-circle"></i><?= e($errorCarga) ?></div>
    <?php elseif (!$tickets): ?>
      <div class="ticket-empty"><i class="bi bi-inbox"></i>No hay tickets que coincidan con los filtros.</div>
    <?php else: ?>
      <div class="table-wrap"><table id="admin-tabla"><thead><tr><th>Folio / fecha</th><th>Caso</th><th>Solicitante</th><th>Técnico</th><th>Estado</th><th>Prioridad</th><th></th></tr></thead><tbody>
      <?php foreach ($tickets as $ticket):
        $estado = strtolower((string) $ticket['estado']);
        $prioridad = str_replace('í', 'i', strtolower((string) $ticket['prioridad']));
        $json = json_encode($ticket, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        $fechaTicket = strtotime((string) $ticket['fecha_creacion']);
      ?>
        <tr data-search="<?= e(strtolower(implode(' ', [(string)$ticket['id_ticket'],$ticket['asunto'],$ticket['usuario_nombre'],$ticket['tecnico_nombre'],$ticket['colegio_nombre']]))) ?>">
          <td><span class="ticket-id">#<?= (int) $ticket['id_ticket'] ?></span><div class="text-xs text-muted"><?= $fechaTicket ? e(date('d/m/Y H:i', $fechaTicket)) : 'Sin fecha' ?></div></td>
          <td class="ticket-subject"><strong><?= e($ticket['asunto']) ?></strong><small><?= e($ticket['categoria_nombre']) ?> · <?= e($ticket['colegio_nombre']) ?></small></td>
          <td class="ticket-person"><strong><?= e($ticket['usuario_nombre']) ?></strong></td>
          <td class="ticket-person"><strong><?= e($ticket['tecnico_nombre'] ?: 'Sin asignar') ?></strong></td>
          <td><span class="ticket-badge ticket-badge--<?= e($estado) ?>"><?= e($ticket['estado_nombre'] ?? str_replace('_',' ',$estado)) ?></span></td>
          <td><span class="ticket-priority ticket-priority--<?= e($prioridad) ?>"><?= e($ticket['prioridad']) ?></span></td>
          <td><div class="ticket-actions"><a class="btn btn-outline btn-sm" href="ticket_detalle.php?id=<?= (int) $ticket['id_ticket'] ?>"><i class="bi bi-eye"></i> Abrir</a><button class="btn btn-outline btn-sm" type="button" data-ticket="<?= e($json) ?>" onclick="administrarTicket(this)"><i class="bi bi-sliders"></i> Gestionar</button></div></td>
        </tr>
      <?php endforeach; ?></tbody></table></div>
    <?php endif; ?>
  </section>
</div>
