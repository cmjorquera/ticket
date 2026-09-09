<?php
declare(strict_types=1);

/** Tabla de tickets asignados. Requiere: $tickets y $errorCarga. */
$tickets = isset($tickets) && is_array($tickets) ? $tickets : [];
$ticketsPagina = isset($ticketsPagina) && is_array($ticketsPagina) ? $ticketsPagina : $tickets;
$totalTickets = isset($totalTickets) ? max(0, (int) $totalTickets) : count($tickets);
$totalPaginas = isset($totalPaginas) ? max(0, (int) $totalPaginas) : 0;
$paginaActual = isset($paginaActual) ? max(1, (int) $paginaActual) : 1;
$offsetTickets = isset($offsetTickets) ? max(0, (int) $offsetTickets) : 0;
$errorCarga = $errorCarga ?? null;
?>
<section class="card" id="ticket-tabla-tecnico">
  <div class="card-header ticket-list-header">
    <div><h2 class="card-title">Bandeja técnica</h2><p class="card-desc">Abre un caso para revisar su descripción y cambiar el estado.</p></div>
    <span class="ticket-list-count"><?= $totalTickets ?> ticket<?= $totalTickets === 1 ? '' : 's' ?></span>
  </div>
  <div class="ticket-toolbar">
    <div class="form-group ticket-search">
      <label class="form-label" for="ticket-buscar">Buscar</label>
      <input class="form-input" id="ticket-buscar" type="search" placeholder="Folio, asunto, solicitante o colegio">
    </div>
    <div class="form-group">
      <label class="form-label" for="ticket-estado">Estado</label>
      <select class="form-input" id="ticket-estado">
        <option value="">Todos</option><?php foreach (ticket_estados_legacy() as $infoEstado): ?><option value="<?= e($infoEstado['codigo']) ?>"><?= e($infoEstado['nombre']) ?></option><?php endforeach; ?>
      </select>
    </div>
  </div>
  <?php if ($errorCarga): ?>
    <div class="ticket-empty"><i class="bi bi-exclamation-circle"></i><?= e($errorCarga) ?></div>
  <?php elseif (!$tickets): ?>
    <div class="ticket-empty"><i class="bi bi-inbox"></i>No tienes tickets asignados en este momento.</div>
  <?php else: ?>
    <div class="table-wrap">
      <table id="tabla-tickets">
        <thead><tr><th>Folio / fecha</th><th>Caso</th><th>Técnico</th><th>Usuario</th><th>Estado</th><th>Prioridad</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($ticketsPagina as $indice => $ticket):
          $estado = strtolower((string) $ticket['estado']);
          $prioridad = str_replace(['í', 'Í'], 'i', strtolower((string) $ticket['prioridad']));
          $datosTicket = json_encode($ticket, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
          $fechaTicket = strtotime((string) $ticket['fecha_creacion']);
          $estadoRgb = ticket_color_estado_rgb((string) ($ticket['estado_color'] ?? ''));
        ?>
          <tr class="<?= $estadoRgb !== '' ? 'ticket-row--state' : '' ?>"<?= $estadoRgb !== '' ? ' style="--ticket-state-rgb:' . e($estadoRgb) . '"' : '' ?> data-search="<?= e(strtolower(implode(' ', [(string) $ticket['id_ticket'], $ticket['asunto'], $ticket['usuario_nombre'], $ticket['tecnico_nombre'], $ticket['colegio_nombre'], $ticket['categoria_nombre']]))) ?>" data-estado="<?= e($estado) ?>">
            <td><strong class="ticket-id">#<?= e($offsetTickets + $indice + 1) ?></strong><div class="text-xs text-muted"><?= $fechaTicket ? e(date('d/m/Y H:i', $fechaTicket)) : 'Sin fecha' ?></div></td>
            <td class="ticket-subject"><strong><?= e($ticket['asunto']) ?></strong><small><?= e($ticket['categoria_nombre']) ?> · <?= e($ticket['colegio_nombre']) ?></small></td>
            <td class="ticket-person"><strong><?= e(trim((string) $ticket['tecnico_nombre']) ?: 'Sin asignar') ?></strong></td>
            <td class="ticket-person"><strong><?= e($ticket['usuario_nombre']) ?></strong></td>
            <td><?= ticket_badge_estado($ticket) ?></td>
            <td><span class="ticket-priority ticket-priority--<?= e($prioridad) ?>"><?= e($ticket['prioridad']) ?></span></td>
            <td><div class="ticket-actions"><button class="btn btn-outline btn-sm js-ticket-chat" type="button" data-ticket-id="<?= (int) $ticket['id_ticket'] ?>"><i class="bi bi-chat-dots"></i> Chat</button><a class="btn btn-outline btn-sm" href="ticket_detalle.php?id=<?= (int) $ticket['id_ticket'] ?>"><i class="bi bi-eye"></i> Abrir</a><button class="btn btn-outline btn-sm" type="button" data-ticket="<?= e($datosTicket) ?>" onclick="abrirTicket(this)"><i class="bi bi-pencil"></i> Estado</button></div></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if ($totalPaginas > 1): ?>
      <nav class="ticket-pagination" aria-label="Paginación de tickets asignados"><ul class="pagination">
        <li class="page-item<?= $paginaActual <= 1 ? ' disabled' : '' ?>"><?php if ($paginaActual <= 1): ?><span class="page-link" aria-disabled="true">Anterior</span><?php else: ?><a class="page-link" href="?<?= e(http_build_query(array_merge($_GET, ['pagina' => $paginaActual - 1]))) ?>">Anterior</a><?php endif; ?></li>
        <?php for ($pagina = 1; $pagina <= $totalPaginas; $pagina++): ?><li class="page-item<?= $pagina === $paginaActual ? ' active' : '' ?>"><a class="page-link" href="?<?= e(http_build_query(array_merge($_GET, ['pagina' => $pagina]))) ?>"<?= $pagina === $paginaActual ? ' aria-current="page"' : '' ?>><?= $pagina ?></a></li><?php endfor; ?>
        <li class="page-item<?= $paginaActual >= $totalPaginas ? ' disabled' : '' ?>"><?php if ($paginaActual >= $totalPaginas): ?><span class="page-link" aria-disabled="true">Siguiente</span><?php else: ?><a class="page-link" href="?<?= e(http_build_query(array_merge($_GET, ['pagina' => $paginaActual + 1]))) ?>">Siguiente</a><?php endif; ?></li>
      </ul></nav>
    <?php endif; ?>
    <div class="ticket-filter-empty" id="tickets-tecnico-sin-resultados" hidden><i class="bi bi-search"></i>No hay tickets que coincidan con la búsqueda.</div>
  <?php endif; ?>
</section>
