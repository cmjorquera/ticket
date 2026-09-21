<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// Recolectar fecha y hora actuales
$fecha = date("Y-m-d");
$hora  = date("H:i:s");

// Conexión a la base de datos
$db = new MySQL("", "", "");

// Acción enviada
$accion = isset($_POST["accion"]) ? trim($_POST["accion"]) : null;

// Validación de la acción
if (!$accion) {
    echo json_encode(["status" => "error", "message" => "Acción no especificada."]);
    exit;
}

switch ($accion) {

    // -----------------------------------
    // Caso 1: Enviar mensaje a un colaborador
    // -----------------------------------
    case 'crear_mensaje_colaboradores':
        $idDestino = isset($_POST["idUsuario"]) ? trim($_POST["idUsuario"]) : null;
        $idOrigen  = isset($_POST["idUsuarioSession"]) ? trim($_POST["idUsuarioSession"]) : null;
        $mensaje   = isset($_POST["mensaje"]) ? trim($_POST["mensaje"]) : null;
        $urgente   = isset($_POST["urgente"]) && $_POST["urgente"] === 'true' ? 1 : 0;
        $fechaHora = date("Y-m-d H:i:s");
    
        if (!$idOrigen || !$idDestino || !$mensaje) {
            echo json_encode([
                "status" => "error",
                "message" => "Faltan datos para crear el mensaje."
            ]);
            exit;
        }
    
        $db = new MySQL("", "", "");
        $mensajeSafe = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
    
        // 1. Insertar mensaje sin id_conversacion (será NULL por defecto)
        $sqlInsert = "
            INSERT INTO mensajes_chat (
                id_conversacion, mensaje, de, para, fecha_hora, leido, urgente, eliminado, prioridad
            ) VALUES (
                NULL, '$mensajeSafe', '$idOrigen', '$idDestino', '$fechaHora', 0, '$urgente', 0, 'NORMAL'
            )
        ";
    
        $resultado = $db->guardar($sqlInsert);
    
        if ($resultado === 0) {
            $idGenerado = $db->ultimo_id(); // Este será también el id_conversacion
    
            // 2. Actualizar ese mensaje con su propio id como id_conversacion
            $sqlUpdate = "UPDATE mensajes_chat SET id_conversacion = '$idGenerado' WHERE id = '$idGenerado'";
            $db->consulta($sqlUpdate);
    
            echo json_encode([
                "status" => "success",
                "message" => "Mensaje enviado correctamente.",
                "id_conversacion" => $idGenerado
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al enviar el mensaje."
            ]);
        }
        break;
    
    
    
    // -----------------------------------
    // Caso 2: Enviar mensaje a un destinatario específico
    // -----------------------------------
    case 'crear_mensaje':
        $idOrigen   = isset($_POST["idUsuario"])        ? trim($_POST["idUsuario"]) : null; // ID del remitente
        $idDestino  = isset($_POST["destinatarioId"])    ? trim($_POST["destinatarioId"]) : null; // ID del destinatario
        $mensaje    = isset($_POST["mensaje"])          ? trim($_POST["mensaje"]) : null;
        $urgente    = isset($_POST["urgente"])  && $_POST["urgente"] === 'true' ? "SI" : "NO";

        if (!$idOrigen || !$idDestino || !$mensaje) {
            echo json_encode(["status" => "error", "message" => "Faltan datos para crear el mensaje."]);
            exit;
        }

        $mensajeSafe = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
        $sql = "INSERT INTO `mensajes` (`mensaje`, `para`, `de`, `leido`, `urgente`, `fecha`, `hora`, `eliminado`)
                VALUES ('$mensajeSafe', '$idDestino', '$idOrigen', 'NO', '$urgente', '$fecha', '$hora', 'NO')";
        $resultado = $db->guardar($sql);

        echo $resultado === 0
            ? json_encode(["status" => "success", "message" => "Mensaje creado exitosamente."])
            : json_encode(["status" => "error", "message" => "Error al crear el mensaje."]);
        break;

    // -----------------------------------
    // Caso 3: Enviar mensaje a múltiples destinatarios
    // -----------------------------------
    case 'crear_mensaje_multiple':
        $idsUsuarios = isset($_POST['ids']) ? $_POST['ids'] : []; // Array de IDs destinatarios
        $idOrigen    = isset($_POST["idUsuarioSession"]) ? trim($_POST["idUsuarioSession"]) : null; // Remitente
        $mensaje     = isset($_POST["mensaje"]) ? trim($_POST["mensaje"]) : null;
        $urgente     = isset($_POST["urgente"]) && $_POST["urgente"] === 'true' ? "SI" : "NO";

        if (empty($idsUsuarios) || !$idOrigen || !$mensaje) {
            echo json_encode(["status" => "error", "message" => "Faltan datos para crear mensajes múltiples."]);
            exit;
        }

        $errores = [];
        $mensajeSafe = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');

        foreach ($idsUsuarios as $idDestino) {
            $idDestinoSafe = $db->escape_string($idDestino);
            $sql = "INSERT INTO `mensajes` (`mensaje`, `para`, `de`, `leido`, `urgente`, `fecha`, `hora`, `eliminado`)
                    VALUES ('$mensajeSafe', '$idDestinoSafe', '$idOrigen', 'NO', '$urgente', '$fecha', '$hora', 'NO')";
            $resultado = $db->guardar($sql);

            if ($resultado !== 0) {
                $errores[] = "Error al insertar mensaje para ID $idDestinoSafe.";
            }
        }

        echo empty($errores)
            ? json_encode(["status" => "success", "message" => "Mensajes enviados correctamente a todos los destinatarios."])
            : json_encode(["status" => "error", "message" => "Errores: " . implode(', ', $errores)]);
        break;

    // -----------------------------------
    // Default: Acción no reconocida
    // -----------------------------------
    default:
        echo json_encode(["status" => "error", "message" => "Acción no reconocida."]);
        break;
}
