<?php
declare(strict_types=1);

/**
 * Control general de tickets.
 * Requiere: $tickets, $colegios, $tecnicos, $estadoFiltro, $colegioFiltro,
 * $errorCarga y $esGlobal.
 */
$tickets = isset($tickets) && is_array($tickets) ? $tickets : [];
$ticketsPagina = isset($ticketsPagina) && is_array($ticketsPagina) ? $ticketsPagina : $tickets;
$totalPaginas = isset($totalPaginas) ? max(0, (int) $totalPaginas) : 0;
$paginaActual = isset($paginaActual) ? max(1, (int) $paginaActual) : 1;
$offsetTickets = isset($offsetTickets) ? max(0, (int) $offsetTickets) : 0;
$resumenFiltro = isset($resumenFiltro) ? ticket_estado_grupo_filtro($resumenFiltro) : '';
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
      <?php if ($resumenFiltro !== ''): ?><input type="hidden" name="resumen" value="<?= e($resumenFiltro) ?>"><?php endif; ?>
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
      <div class="table-wrap"><table id="admin-tabla"><thead><tr><th>Folio / fecha</th><th>Caso</th><th>Técnico</th><th>Usuario</th><th>Estado</th><th>Prioridad</th><th></th></tr></thead><tbody>
      <?php foreach ($ticketsPagina as $indice => $ticket):
        $estado = strtolower((string) $ticket['estado']);
        $prioridad = str_replace('í', 'i', strtolower((string) $ticket['prioridad']));
        $json = json_encode($ticket, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        $fechaTicket = strtotime((string) $ticket['fecha_creacion']);
        $estadoRgb = ticket_color_estado_rgb((string) ($ticket['estado_color'] ?? ''));
      ?>
        <tr class="<?= $estadoRgb !== '' ? 'ticket-row--state' : '' ?>"<?= $estadoRgb !== '' ? ' style="--ticket-state-rgb:' . e($estadoRgb) . '"' : '' ?> data-search="<?= e(strtolower(implode(' ', [(string)$ticket['id_ticket'],$ticket['asunto'],$ticket['usuario_nombre'],$ticket['tecnico_nombre'],$ticket['colegio_nombre']]))) ?>">
          <td><strong class="ticket-id">#<?= e($offsetTickets + $indice + 1) ?></strong><div class="text-xs text-muted"><?= $fechaTicket ? e(date('d/m/Y H:i', $fechaTicket)) : 'Sin fecha' ?></div></td>
          <td class="ticket-subject"><strong><?= e($ticket['asunto']) ?></strong><small><?= e($ticket['categoria_nombre']) ?> · <?= e($ticket['colegio_nombre']) ?></small></td>
          <td class="ticket-person"><strong><?= e($ticket['tecnico_nombre'] ?: 'Sin asignar') ?></strong></td>
          <td class="ticket-person"><strong><?= e($ticket['usuario_nombre']) ?></strong></td>
          <td><?= ticket_badge_estado($ticket) ?></td>
          <td><span class="ticket-priority ticket-priority--<?= e($prioridad) ?>"><?= e($ticket['prioridad']) ?></span></td>
          <td><div class="ticket-actions"><button class="btn btn-outline btn-sm js-ticket-chat" type="button" data-ticket-id="<?= (int) $ticket['id_ticket'] ?>"><i class="bi bi-chat-dots"></i> Chat</button><a class="btn btn-outline btn-sm" href="ticket_detalle.php?id=<?= (int) $ticket['id_ticket'] ?>"><i class="bi bi-eye"></i> Abrir</a><button class="btn btn-outline btn-sm" type="button" data-ticket="<?= e($json) ?>" onclick="administrarTicket(this)"><i class="bi bi-sliders"></i> Gestionar</button></div></td>
        </tr>
      <?php endforeach; ?></tbody></table></div>
      <?php if ($totalPaginas > 1): ?>
        <nav class="ticket-pagination" aria-label="Paginación de tickets administrados"><ul class="pagination">
          <li class="page-item<?= $paginaActual <= 1 ? ' disabled' : '' ?>"><?php if ($paginaActual <= 1): ?><span class="page-link" aria-disabled="true">Anterior</span><?php else: ?><a class="page-link" href="?<?= e(http_build_query(array_merge($_GET, ['pagina' => $paginaActual - 1]))) ?>">Anterior</a><?php endif; ?></li>
          <?php for ($pagina = 1; $pagina <= $totalPaginas; $pagina++): ?><li class="page-item<?= $pagina === $paginaActual ? ' active' : '' ?>"><a class="page-link" href="?<?= e(http_build_query(array_merge($_GET, ['pagina' => $pagina]))) ?>"<?= $pagina === $paginaActual ? ' aria-current="page"' : '' ?>><?= $pagina ?></a></li><?php endfor; ?>
          <li class="page-item<?= $paginaActual >= $totalPaginas ? ' disabled' : '' ?>"><?php if ($paginaActual >= $totalPaginas): ?><span class="page-link" aria-disabled="true">Siguiente</span><?php else: ?><a class="page-link" href="?<?= e(http_build_query(array_merge($_GET, ['pagina' => $paginaActual + 1]))) ?>">Siguiente</a><?php endif; ?></li>
        </ul></nav>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</div>
