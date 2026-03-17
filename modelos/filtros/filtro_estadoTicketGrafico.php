<?php
require_once '../../class/conexion.php'; 
require_once '../../class/funciones.php'; 

$funciones = new Funciones();

try {
    // Capturar los parámetros
    $tipoUsuario = $_GET['tipoUsuario'] ?? 'todos'; // Por defecto, muestra todos los tickets
    $idUsuario = isset($_GET['idUsuario']) ? intval($_GET['idUsuario']) : null;

    // Llamar la función con el filtro adecuado según el tipo de usuario
    if ($tipoUsuario === 'tecnico' && $idUsuario) {
        $estados = $funciones->cargarEstadosTicket($idUsuario);
    } elseif ($tipoUsuario === 'usuario' && $idUsuario) {
        $estados = $funciones->cargarEstadosTicketUsuario($idUsuario);
    } else {
        $estados = $funciones->cargarEstadosTicketTodos();
    }

    header('Content-Type: application/json');
    echo json_encode($estados, JSON_PRETTY_PRINT);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
