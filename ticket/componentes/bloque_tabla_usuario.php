<?php
declare(strict_types=1);

/**
 * Tabla de solicitudes del usuario actual.
 * Requiere: $misTickets, $ticketsPagina, datos de paginación, $errorListado y $etiquetasEstado.
 */
$misTickets = isset($misTickets) && is_array($misTickets) ? $misTickets : [];
$ticketsPagina = isset($ticketsPagina) && is_array($ticketsPagina) ? $ticketsPagina : $misTickets;
$totalTickets = isset($totalTickets) ? max(0, (int) $totalTickets) : count($misTickets);
$totalPaginas = isset($totalPaginas) ? max(0, (int) $totalPaginas) : 0;
$paginaActual = isset($paginaActual) ? max(1, (int) $paginaActual) : 1;
$offsetTickets = isset($offsetTickets) ? max(0, (int) $offsetTickets) : 0;
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
    <span class="ticket-list-count" aria-live="polite"><?= $totalTickets ?> ticket<?= $totalTickets === 1 ? '' : 's' ?></span>
  </div>

  <div class="ticket-toolbar">
    <div class="form-group ticket-search">
      <label class="form-label" for="mis-tickets-buscar">Buscar</label>
      <input class="form-input" id="mis-tickets-buscar" type="search" placeholder="N°, asunto, categoría o colegio">
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
    <div class="form-group">
      <label class="form-label" for="mis-tickets-respuesta">Fecha estimada</label>
      <input class="form-input" id="mis-tickets-respuesta" type="date">
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
        <thead><tr><?= ticket_th('N° / fecha', 'fecha') ?><?= ticket_th('Caso', 'caso') ?><?= ticket_th('Técnico', 'tecnico') ?><?= ticket_th('Estado', 'estado') ?><?= ticket_th('Plazo', 'plazo') ?><?= ticket_th('Categoría', 'categoria') ?><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($ticketsPagina as $indice => $ticket):
          $estado = strtolower((string) $ticket['estado']);
          $fechaTicket = strtotime((string) $ticket['fecha_creacion']);
          // el filtro "Fecha estimada" usa la fecha que dio el técnico al comenzar
          $fechaRespuesta = !empty($ticket['fecha_estimada_admin']) ? strtotime((string) $ticket['fecha_estimada_admin']) : false;
          $estadoRgb = ticket_color_estado_rgb((string) ($ticket['estado_color'] ?? ''));
          $cantidadArchivos = max(0, (int) ($ticket['cantidad_archivos'] ?? 0));
          $estadoGrupo = ticket_estado_grupo((int) $ticket['id_estado']);
        ?>
          <tr class="<?= $estadoRgb !== '' ? 'ticket-row--state' : '' ?>"<?= $estadoRgb !== '' ? ' style="--ticket-state-rgb:' . e($estadoRgb) . '"' : '' ?> data-search="<?= e(strtolower(implode(' ', [(string) $ticket['id_ticket'], $ticket['asunto'], $ticket['categoria_nombre'], $ticket['usuario_nombre'], $ticket['colegio_nombre'], $ticket['tecnico_nombre']]))) ?>" data-estado="<?= e($estado) ?>" data-estado-grupo="<?= e($estadoGrupo) ?>" data-fecha="<?= $fechaTicket ? e(date('Y-m-d', $fechaTicket)) : '' ?>" data-fecha-respuesta="<?= $fechaRespuesta ? e(date('Y-m-d', $fechaRespuesta)) : '' ?>">
            <td><div class="tk-num"><strong class="ticket-id" title="Ticket #<?= (int) $ticket['id_ticket'] ?>"><?= $offsetTickets + $indice + 1 ?></strong><?= ticket_sobre((int) $ticket['id_ticket'], (int) ($ticket['mensajes_no_leidos'] ?? 0)) ?></div><div class="tk-num__ticket">Ticket #<?= (int) $ticket['id_ticket'] ?></div><div class="text-xs text-muted"><?= $fechaTicket ? e(date('d/m/Y H:i', $fechaTicket)) : 'Sin fecha' ?></div></td>
            <td class="ticket-subject"><strong><?= e($ticket['asunto']) ?></strong></td>
            <td class="ticket-person"><strong><?= e(trim((string) $ticket['tecnico_nombre']) ?: 'Sin asignar') ?></strong></td>
            <td><?= ticket_badge_estado($ticket) ?></td>
            <td><?= ticket_plazo($ticket) ?></td>
            <td><span class="badge badge-realizado"><?= e(trim((string) $ticket['categoria_nombre']) ?: 'Sin categoría') ?></span></td>
            <td><div class="ticket-actions"><?= ticket_boton_chat((int) $ticket['id_ticket'], (int) ($ticket['mensajes_no_leidos'] ?? 0)) ?><?php if ((int) ($ticket['cantidad_imagenes'] ?? 0) > 0): ?><button type="button" class="btn btn-outline btn-sm js-ticket-gallery" data-ticket-id="<?= (int) $ticket['id_ticket'] ?>" title="Ver imágenes y archivos adjuntos" aria-label="Ver adjuntos del ticket #<?= (int) $ticket['id_ticket'] ?>"><i class="bi bi-images"></i> Adjuntos</button><?php endif; ?><a class="btn btn-outline btn-sm js-ticket-ver" data-ticket-id="<?= (int) $ticket['id_ticket'] ?>" href="ticket_detalle.php?id=<?= (int) $ticket['id_ticket'] ?>&amp;vista=usuario"><i class="bi bi-eye"></i> Ver</a></div></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if ($totalPaginas > 1): ?>
      <nav class="ticket-pagination" aria-label="Paginación de solicitudes">
        <ul class="pagination">
          <li class="page-item<?= $paginaActual <= 1 ? ' disabled' : '' ?>">
            <?php if ($paginaActual <= 1): ?><span class="page-link" aria-disabled="true">Anterior</span><?php else: ?><a class="page-link" href="?<?= e(http_build_query(array_merge($_GET, ['pagina' => $paginaActual - 1]))) ?>">Anterior</a><?php endif; ?>
          </li>
          <?php for ($pagina = 1; $pagina <= $totalPaginas; $pagina++): ?>
            <li class="page-item<?= $pagina === $paginaActual ? ' active' : '' ?>"><a class="page-link" href="?<?= e(http_build_query(array_merge($_GET, ['pagina' => $pagina]))) ?>"<?= $pagina === $paginaActual ? ' aria-current="page"' : '' ?>><?= $pagina ?></a></li>
          <?php endfor; ?>
          <li class="page-item<?= $paginaActual >= $totalPaginas ? ' disabled' : '' ?>">
            <?php if ($paginaActual >= $totalPaginas): ?><span class="page-link" aria-disabled="true">Siguiente</span><?php else: ?><a class="page-link" href="?<?= e(http_build_query(array_merge($_GET, ['pagina' => $paginaActual + 1]))) ?>">Siguiente</a><?php endif; ?>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
    <div class="ticket-filter-empty" id="mis-tickets-sin-resultados" hidden><i class="bi bi-search"></i>No hay tickets que coincidan con la búsqueda.</div>
  <?php endif; ?>
</section>
