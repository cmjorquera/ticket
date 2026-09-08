<?php
declare(strict_types=1);

/**
 * Tabla de solicitudes del usuario actual.
 * Requiere: $misTickets, $errorListado y $etiquetasEstado.
 */
$misTickets = isset($misTickets) && is_array($misTickets) ? $misTickets : [];
$errorListado = $errorListado ?? null;
$etiquetasEstado = $etiquetasEstado ?? [
    'nuevo' => 'Nuevo',
    'en_proceso' => 'En proceso',
    'atrasado' => 'Atrasado',
    'resuelto' => 'Resuelto',
    'cerrado' => 'Cerrado',
];
?>
<section class="card ticket-request-list" id="ticket-tabla-usuario" aria-labelledby="mis-solicitudes-titulo">
  <div class="card-header ticket-list-header">
    <div>
      <h2 class="card-title" id="mis-solicitudes-titulo">Mis solicitudes</h2>
      <p class="card-desc">Consulta el estado y la asignación de los tickets que has creado.</p>
    </div>
    <span class="ticket-list-count"><?= count($misTickets) ?> ticket<?= count($misTickets) === 1 ? '' : 's' ?></span>
  </div>

  <div class="ticket-toolbar">
    <div class="form-group ticket-search">
      <label class="form-label" for="mis-tickets-buscar">Buscar</label>
      <input class="form-input" id="mis-tickets-buscar" type="search" placeholder="Folio, asunto, categoría o colegio">
    </div>
    <div class="form-group">
      <label class="form-label" for="mis-tickets-estado">Estado</label>
      <select class="form-input" id="mis-tickets-estado">
        <option value="">Todos</option>
        <?php foreach ($etiquetasEstado as $valor => $texto): ?>
          <option value="<?= e($valor) ?>"><?= e($texto) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label" for="mis-tickets-fecha">Fecha de creación</label>
      <input class="form-input" id="mis-tickets-fecha" type="date">
    </div>
    <button class="btn btn-primary" type="button" onclick="abrirModalTicket()"><i class="bi bi-plus-circle"></i> Agregar ticket</button>
  </div>

  <?php if ($errorListado): ?>
    <div class="ticket-empty"><i class="bi bi-exclamation-circle"></i><?= e($errorListado) ?></div>
  <?php elseif (!$misTickets): ?>
    <div class="ticket-empty ticket-empty--action">
      <i class="bi bi-inbox"></i>
      <strong>Aún no tienes solicitudes</strong>
      <span>Cuando crees un ticket, podrás seguir su avance desde este listado.</span>
      <button class="btn btn-primary" type="button" onclick="abrirModalTicket()"><i class="bi bi-plus-circle"></i> Crear primer ticket</button>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table id="mis-tickets-tabla">
        <thead><tr><th>Folio / fecha</th><th>Caso</th><th>Colegio</th><th>Técnico</th><th>Estado</th><th>Prioridad</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($misTickets as $ticket):
          $estado = strtolower((string) $ticket['estado']);
          $prioridad = str_replace(['í', 'Í'], 'i', strtolower((string) $ticket['prioridad']));
          $textoEstado = $etiquetasEstado[$estado] ?? ucfirst(str_replace('_', ' ', $estado));
        ?>
          <tr data-search="<?= e(strtolower(implode(' ', [(string) $ticket['id_ticket'], $ticket['asunto'], $ticket['categoria_nombre'], $ticket['colegio_nombre'], $ticket['tecnico_nombre']]))) ?>" data-estado="<?= e($estado) ?>" data-fecha="<?= e(date('Y-m-d', strtotime((string) $ticket['fecha_creacion']))) ?>">
            <td><span class="ticket-id">#<?= (int) $ticket['id_ticket'] ?></span><div class="text-xs text-muted"><?= e(date('d/m/Y H:i', strtotime((string) $ticket['fecha_creacion']))) ?></div></td>
            <td class="ticket-subject"><strong><?= e($ticket['asunto']) ?></strong><small><?= e($ticket['categoria_nombre']) ?></small></td>
            <td class="ticket-person"><strong><?= e($ticket['colegio_nombre']) ?></strong></td>
            <td class="ticket-person"><strong><?= e(trim((string) $ticket['tecnico_nombre']) ?: 'Sin asignar') ?></strong></td>
            <td><span class="ticket-badge ticket-badge--<?= e($estado) ?>"><?= e($textoEstado) ?></span></td>
            <td><span class="ticket-priority ticket-priority--<?= e($prioridad) ?>"><?= e($ticket['prioridad']) ?></span></td>
            <td><div class="ticket-actions"><a class="btn btn-outline btn-sm" href="ticket_detalle.php?id=<?= (int) $ticket['id_ticket'] ?>"><i class="bi bi-eye"></i> Ver</a></div></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="ticket-filter-empty" id="mis-tickets-sin-resultados" hidden><i class="bi bi-search"></i>No hay tickets que coincidan con la búsqueda.</div>
  <?php endif; ?>
</section>
