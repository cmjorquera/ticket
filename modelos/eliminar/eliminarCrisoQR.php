<?php
header('Content-Type: application/json');  // Indica que la respuesta es JSON

include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

if (isset($_POST['idQR'])) {
    $idQR = intval($_POST['idQR']);  // Sanitizar el ID

    // Conexión a la base de datos
    $db = new MySQL("seduc", "", "");

    // Query para actualizar
    $query = "UPDATE curso_codigos_qr SET eliminado = 'si' WHERE id_qr = $idQR";

    // Ejecutar consulta
    $result = $db->guardar($query);

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Curso eliminado"]);
    } else {
        echo json_encode(["status" => "success", "message" => "Error al eliminar"]);
    }

 
} else {
    echo json_encode(["status" => "error", "message" => "ID no proporcionado"]);
}
?>
