<?php
header('Content-Type: text/html; charset=utf-8');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

$id_ticket = isset($_POST['id_ticket']) ? (int)$_POST['id_ticket'] : 0;

if ($id_ticket <= 0) {
    echo '<div class="text-danger">ID de ticket inválido.</div>';
    exit;
}

function h($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function renderDescripcionTicketHtml($html)
{
    $html = (string)($html ?? '');
    $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $permitidas = '<p><br><ol><ul><li><strong><b><em><i><u><s><blockquote><code><pre><h1><h2><h3><a>';
    $html = strip_tags($html, $permitidas);
    $html = preg_replace('/\s+on\w+\s*=\s*("|\').*?\1/iu', '', $html);
    $html = preg_replace('/\sstyle\s*=\s*("|\').*?\1/iu', '', $html);
    $html = preg_replace('/\s(href)\s*=\s*("|\')\s*javascript:.*?\2/iu', '', $html);
    $html = trim((string)$html);
    return $html !== '' ? $html : 'Sin descripción';
}

$db = new MySQL("", "", "");
$db->set_charset('utf8mb4');

$sql = "
    SELECT
        t.id_ticket,
        t.asunto,
        t.descripcion_ticket,
        t.identificador,
        t.id_estado,
        u.nombre AS nombre_usuario,
        u.apellido_paterno AS apellido_usuario,
        et.nombre AS nombre_estado,
        c.nombre_categoria,
        pt.fecha_creacion_inicio,
        pt.hora_creacion_inicio
    FROM tickets t
    LEFT JOIN usuarios u ON u.id = t.id_usuario
    LEFT JOIN estados_ticket et ON et.id = t.id_estado
    LEFT JOIN categoria_de_ticket c ON c.id_categoria = t.id_categoria_ticket
    LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
    WHERE t.id_ticket = {$id_ticket}
    LIMIT 1
";

$res = $db->consulta($sql);
if (!$res || $db->num_rows($res) === 0) {
    echo '<div class="text-danger">No se encontró información del ticket.</div>';
    exit;
}

$row = $db->fetch_array($res);
$descripcionHtml = renderDescripcionTicketHtml($row['descripcion_ticket'] ?? '');
$usuario = trim(($row['nombre_usuario'] ?? '') . ' ' . ($row['apellido_usuario'] ?? ''));
$usuario = $usuario !== '' ? $usuario : 'Sin usuario';
$asunto = trim((string)($row['asunto'] ?? ''));
$asunto = $asunto !== '' ? $asunto : 'Sin asunto';
$categoria = trim((string)($row['nombre_categoria'] ?? ''));
$categoria = $categoria !== '' ? $categoria : 'Sin categoría';
$estado = trim((string)($row['nombre_estado'] ?? ''));
$estado = $estado !== '' ? $estado : 'Sin estado';
$fecha = trim((string)($row['fecha_creacion_inicio'] ?? ''));
$hora = trim((string)($row['hora_creacion_inicio'] ?? ''));

echo '
<div class="d-flex flex-column gap-3">
  <div class="border rounded-3 bg-white p-3">
    <div class="small text-muted mb-1">Asunto</div>
    <div class="fw-semibold">' . h($asunto) . '</div>
  </div>

  // <div class="row g-2">
  //   <div class="col-6">
  //     <div class="border rounded-3 bg-white p-3 h-100">
  //       <div class="small text-muted mb-1">Usuario</div>
  //       <div>' . h($usuario) . '</div>
  //     </div>
  //   </div>
  //   <div class="col-6">
  //     <div class="border rounded-3 bg-white p-3 h-100">
  //       <div class="small text-muted mb-1">Estado</div>
  //       <div>' . h($estado) . '</div>
  //     </div>
  //   </div>
  //   <div class="col-6">
  //     <div class="border rounded-3 bg-white p-3 h-100">
  //       <div class="small text-muted mb-1">Categoría</div>
  //       <div>' . h($categoria) . '</div>
  //     </div>
  //   </div>
  //   <div class="col-6">
  //     <div class="border rounded-3 bg-white p-3 h-100">
  //       <div class="small text-muted mb-1">Creado</div>
  //       <div>' . h(trim($fecha . ' ' . $hora)) . '</div>
  //     </div>
  //   </div>
  // </div>

  <div class="border rounded-3 bg-white p-3">
    <div class="small text-muted mb-2">Descripción</div>
    <div class="ticket-offcanvas-descripcion" style="line-height:1.45;">' . $descripcionHtml . '</div>
  </div>
</div>';

