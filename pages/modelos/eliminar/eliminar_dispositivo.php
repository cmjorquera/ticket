<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Leer el ID desde la URL
    $idDispositivo = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // Depurar el valor recibido
    error_log("ID recibido para eliminar: " . $idDispositivo);

    // Verificar que el ID sea válido
    if ($idDispositivo > 0) {
        $db = new MySQL("", "", "");

        // Escapar valores para evitar problemas
        $idDispositivo = $db->escape_string($idDispositivo);

        // Construir y ejecutar la consulta
        $query = "DELETE FROM otros_dispositivos WHERE id_dispositivo = $idDispositivo";
        error_log("Consulta ejecutada: " . $query);

        // Ejecutar la consulta
        $resultado = $db->consulta($query);

        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Dispositivo eliminado correctamente.']);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Error al ejecutar la consulta: ' . $db->getLastError()
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'ID de dispositivo inválido o no proporcionado.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
}
?>
