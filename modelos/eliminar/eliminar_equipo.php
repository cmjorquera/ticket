<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");

$bdato = new MySQL('', '', ''); // Configura la conexión

header('Content-Type: application/json');
parse_str(file_get_contents("php://input"), $_DELETE);


// Verificar si el ID del equipo fue proporcionado
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idEquipo = intval($_GET['id']);
    $success = true;

    // Eliminar de la tabla equipos
    $sqlEquipos                 = "DELETE FROM equipos WHERE id_equipo = $idEquipo";
    $resultEquipos              = $bdato->consulta($sqlEquipos);

    // Eliminar de la tabla equipo_software
    $sqlSoftware                = "DELETE FROM equipo_software WHERE id_equipo = $idEquipo";
    $resultSoftware             = $bdato->consulta($sqlSoftware);

    // Eliminar de la tabla equipos_compra
    $sqlCompra                  = "DELETE FROM equipos_compra WHERE id_equipo = $idEquipo";
    $resultCompra               = $bdato->consulta($sqlCompra);

    // Eliminar de la tabla equipo_memoria
    $sqlMemoria                 = "DELETE FROM equipo_memoria WHERE id_equipo = $idEquipo";
    $resultMemoria              = $bdato->consulta($sqlMemoria);

    // Eliminar de la tabla equipo_procesador
    $sqlProcesador              = "DELETE FROM equipo_procesador WHERE id_equipo = $idEquipo";
    $resultProcesador           = $bdato->consulta($sqlProcesador);

    // Eliminar de la tabla equipo_almacenamiento
    $sqlAlmacenamiento          = "DELETE FROM equipo_almacenamiento WHERE id_equipo = $idEquipo";
    $resultAlmacenamiento       = $bdato->consulta($sqlAlmacenamiento);

    // Comprobar si todas las eliminaciones fueron exitosas
    if ($resultEquipos && $resultSoftware && $resultCompra && $resultMemoria && $resultProcesador && $resultAlmacenamiento) {
        echo json_encode(['success' => true, 'message' => 'Equipo eliminado correctamente en todas las tablas']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar en una o más tablas']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID de equipo no proporcionado o inválido']);
}
