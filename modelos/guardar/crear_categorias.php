<?php


header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("../../class/conexion.php");

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!empty($input['nombre_categoria'])) {
            $nombre = trim($input['nombre_categoria']);

            $bdato = new MySQL("", "", "");

            $sql = "INSERT INTO categoria_de_ticket (nombre_categoria) 
                    VALUES ('$nombre')";

            if ($bdato->consulta($sql)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo insertar']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Campo vacío']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Método inválido']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Excepción: ' . $e->getMessage()]);
}
