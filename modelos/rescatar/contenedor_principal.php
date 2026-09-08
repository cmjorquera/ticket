<?php
header('Content-Type: application/json');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// Recibir el ID con validación básica
$id = $_GET['id'] ?? null;

if ($id === null) {
    echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
    exit;
}

try {
    // Crear la conexión utilizando la clase MySQL
    $db = new MySQL("", "", "");  // Asegúrate de que los parámetros están correctamente proporcionados

    // Construir la consulta
    $sql = "SELECT * FROM beneficios_principales WHERE id = '$id'";
    echo $sql."********";
    $result = $db->consulta($sql);

    if ($db->num_rows($result) > 0) {
        $beneficio = $db->fetch_array($result);
        echo json_encode(['success' => true, 'data' => $beneficio]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Beneficio no encontrado']);
    }

    $db->CerrarConexion();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Excepción capturada: ' . $e->getMessage()]);
}
?>
