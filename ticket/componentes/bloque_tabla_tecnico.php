<?php
declare(strict_types=1);

/** Tabla de tickets asignados. Requiere: $tickets y $errorCarga. */
$tickets = isset($tickets) && is_array($tickets) ? $tickets : [];
$errorCarga = $errorCarga ?? null;
?>
<section class="card" id="ticket-tabla-tecnico">
  <div class="card-header ticket-list-header">
    <div><h2 class="card-title">Bandeja técnica</h2><p class="card-desc">Abre un caso para revisar su descripción y cambiar el estado.</p></div>
    <span class="ticket-list-count"><?= count($tickets) ?> ticket<?= count($tickets) === 1 ? '' : 's' ?></span>
  </div>
  <div class="ticket-toolbar">
    <div class="form-group ticket-search">
      <label class="form-label" for="ticket-buscar">Buscar</label>
      <input class="form-input" id="ticket-buscar" type="search" placeholder="Folio, asunto, solicitante o colegio">
    </div>
    <div class="form-group">
      <label class="form-label" for="ticket-estado">Estado</label>
      <select class="form-input" id="ticket-estado">
        <option value="">Todos</option><option value="nuevo">Nuevo</option><option value="en_proceso">En proceso</option><option value="atrasado">Atrasado</option><option value="resuelto">Resuelto</option><option value="cerrado">Cerrado</option>
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
        <thead><tr><th>Folio / fecha</th><th>Caso</th><th>Solicitante</th><th>Estado</th><th>Prioridad</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($tickets as $ticket):
          $estado = strtolower((string) $ticket['estado']);
          $prioridad = str_replace(['í', 'Í'], 'i', strtolower((string) $ticket['prioridad']));
          $datosTicket = json_encode($ticket, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        ?>
          <tr data-search="<?= e(strtolower(implode(' ', [(string) $ticket['id_ticket'], $ticket['asunto'], $ticket['usuario_nombre'], $ticket['colegio_nombre'], $ticket['categoria_nombre']]))) ?>" data-estado="<?= e($estado) ?>">
            <td><span class="ticket-id">#<?= (int) $ticket['id_ticket'] ?></span><div class="text-xs text-muted"><?= e(date('d/m/Y H:i', strtotime((string) $ticket['fecha_creacion']))) ?></div></td>
            <td class="ticket-subject"><strong><?= e($ticket['asunto']) ?></strong><small><?= e($ticket['categoria_nombre']) ?> · <?= e($ticket['colegio_nombre']) ?></small></td>
            <td class="ticket-person"><strong><?= e($ticket['usuario_nombre']) ?></strong></td>
            <td><span class="ticket-badge ticket-badge--<?= e($estado) ?>"><?= e(str_replace('_', ' ', $estado)) ?></span></td>
            <td><span class="ticket-priority ticket-priority--<?= e($prioridad) ?>"><?= e($ticket['prioridad']) ?></span></td>
            <td><div class="ticket-actions"><a class="btn btn-outline btn-sm" href="ticket_detalle.php?id=<?= (int) $ticket['id_ticket'] ?>"><i class="bi bi-eye"></i> Abrir</a><button class="btn btn-outline btn-sm" type="button" data-ticket="<?= e($datosTicket) ?>" onclick="abrirTicket(this)"><i class="bi bi-pencil"></i> Estado</button></div></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="ticket-filter-empty" id="tickets-tecnico-sin-resultados" hidden><i class="bi bi-search"></i>No hay tickets que coincidan con la búsqueda.</div>
  <?php endif; ?>
</section>
