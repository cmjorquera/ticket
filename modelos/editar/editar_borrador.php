<?php
header('Content-Type: application/json'); // Indicar explícitamente que la respuesta será JSON
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_ticket                  = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $asunto_ticket              = isset($_POST['asunto']) ? $_POST['asunto'] : '';
    $descripcion_ticket         = isset($_POST['descripcion_ticket']) ? $_POST['descripcion_ticket'] : '';
    $categoria_ticket           = isset($_POST['id_categoria_ticket']) ? intval($_POST['id_categoria_ticket']) : 0;

    if ($id_ticket === 0 || empty($asunto_ticket) || empty($descripcion_ticket) || $categoria_ticket === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos para modificar el borrador']);
        exit;
    }

    $db = new MySQL("", "", "");

    // Verificar si la nueva categoría tiene un técnico asociado
    $sql_tecnico = "SELECT id_tecnico FROM categoria_tecnico WHERE id_categoria = '$categoria_ticket'";
    $result_tecnico = $db->consulta($sql_tecnico);
    $tecnico_asignado = null;

    if ($row_tecnico = $db->fetch_array($result_tecnico)) {
        $tecnico_asignado = $row_tecnico['id_tecnico'];
    }

    // Actualizar el borrador en la base de datos
    $sql = "UPDATE `tickets` 
            SET `asunto`                    = '$asunto_ticket', 
                `descripcion_ticket`        = '$descripcion_ticket', 
                `id_categoria_ticket`       = '$categoria_ticket',
                `id_tecnico`                = " . ($tecnico_asignado !== null ? "'$tecnico_asignado'" : "NULL") . "
            WHERE `id_ticket` = '$id_ticket' AND `id_estado` = 4"; // Asegurar que solo se modifiquen borradores

    if ($db->guardar($sql)) {
        // Actualizar proceso_tickets con valores actuales del sistema
        $fecha_actual = date('Y-m-d');
        $hora_actual = date('H:i:s');

        $sql_proceso = ($tecnico_asignado !== null) ?
            "UPDATE `proceso_tickets` 
             SET `fecha_asignacion_tecnico`  = '$fecha_actual', 
                 `hora_asignacion_tecnico`   = '$hora_actual',
                 `fecha_creacion_inicio`     = '$fecha_actual', 
                 `hora_creacion_inicio`      = '$hora_actual'
             WHERE `id_ticket` = '$id_ticket'" :
            "UPDATE `proceso_tickets` 
             SET `fecha_asignacion_tecnico` = NULL, 
                 `hora_asignacion_tecnico` = NULL,
                 `fecha_creacion_inicio`   = NULL, 
                 `hora_creacion_inicio`    = NULL
             WHERE `id_ticket` = '$id_ticket'";

        if ($db->guardar($sql_proceso)) {
            echo json_encode(['status' => 'success', 'message' => 'Borrador modificado correctamente']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al modificar el proceso del ticket']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al modificar el borrador']);
    }

    $db->CerrarConexion();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método de solicitud no válido']);
}
?>


