// filtro_tickets.php
include 'config/database.php';  // Asegúrate de incluir tu archivo de configuración de base de datos

if(isset($_GET['estado'])) {
    $estado = $_GET['estado'];
    $query = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $estado);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    // Aquí podrías calcular el porcentaje o cualquier otra métrica necesaria
    $response = [
        'total' => $result['total'],
        // 'porcentaje' => calcula_tu_porcentaje_aquí
    ];

    echo json_encode($response);
} else {
    echo json_encode(['error' => 'Estado no especificado']);
}
