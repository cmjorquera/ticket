<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// Crear conexión a la base de datos
$db = new MySQL("", "", "");
$db->consulta("SET NAMES 'utf8'");

// Verificar si se recibe un ID válido
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(["success" => false, "error" => "ID no proporcionado."]);
    exit;
}

$id_qr = intval($_POST['id']); // Sanitizar el ID

// Consulta SQL corregida con `prepare()`
$stmt = $db->prepare("SELECT id_qr, nombre_taller, fecha_taller, url_formulario, qr_path FROM curso_codigos_qr WHERE id_qr = ?");
if ($stmt) {
    $stmt->bind_param("i", $id_qr);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            "success" => true,
            "id_qr" => $row['id_qr'],
            "nombre_taller" => $row['nombre_taller'],
            "fecha_taller" => $row['fecha_taller'],
            "url_formulario" => $row['url_formulario'],
            "qr_path" => $row['qr_path']
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "QR no encontrado."]);
    }
    
    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "Error en la preparación de la consulta."]);
}

$db->CerrarConexion();
?>
