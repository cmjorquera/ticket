<?php
// Mostrar errores (solo en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Encabezado HTML para caracteres especiales (ñ, tildes, etc.)
header('Content-Type: text/html; charset=utf-8');

include("../../class/conexion.php");

// Conexión a base de datos
try {
    $bdato = new MySQL("", "", ""); // Ajusta si usas usuario, pass y DB
} catch (Exception $e) {
    echo '<div class="text-danger">Error de conexión con la base de datos.</div>';
    exit;
}

// Sanitizar ID del ticket
$id_ticket = isset($_POST['id_ticket']) ? $bdato->escape_string($_POST['id_ticket']) : '';

if (empty($id_ticket)) {
    echo '<div class="text-danger">ID de ticket no proporcionado.</div>';
    exit;
}

// Consulta
$sql = "SELECT descripcion_ticket FROM tickets WHERE id_ticket = '$id_ticket'";
$resultado = $bdato->consulta($sql);

// Mostrar contenido
if ($bdato->num_rows($resultado) > 0) {
    $row = $bdato->fetch_array($resultado);

echo '
<div class="p-3 rounded" style="background-color: #ffffff; color: #1f2937; font-size: 15px;">
    <label class="fw-semibold mb-2 d-flex align-items-center">
        <i class="bi bi-card-text me-2 text-primary"></i> Descripción:
    </label>
    <div class="border rounded shadow-sm p-3 bg-light text-dark" style="white-space: pre-wrap; word-break: break-word;">
        ' . nl2br(htmlspecialchars(mb_convert_encoding($row['descripcion_ticket'], 'UTF-8', 'UTF-8'), ENT_QUOTES, 'UTF-8')) . '
    </div>
</div>';

} else {
    echo '<div class="text-danger">No se encontró información del ticket.</div>';
}
?>
