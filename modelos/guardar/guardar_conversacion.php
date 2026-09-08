<?php
session_start();
require_once '../../class/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje        = trim($_POST['mensaje']);
    $idUsuarioDe    = intval($_POST['id_usuario_de']);
    $idUsuarioPara  = intval($_POST['id_usuario_para']);

    if (empty($mensaje) || empty($idUsuarioDe) || empty($idUsuarioPara)) {
        echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
        exit;
    }

    $bdato = new MySQL("", "", "");

    // Escapar para seguridad
    $mensaje = $bdato->escape_string($mensaje);
    $idUsuarioDe = $bdato->escape_string($idUsuarioDe);
    $idUsuarioPara = $bdato->escape_string($idUsuarioPara);

    // ✅ 1. Verificar si ya existe la conversación
    $sqlBuscar = "
        SELECT id FROM conversaciones_chat 
        WHERE 
            (usuario_1 = '$idUsuarioDe' AND usuario_2 = '$idUsuarioPara') OR
            (usuario_1 = '$idUsuarioPara' AND usuario_2 = '$idUsuarioDe')
        LIMIT 1;
    ";
    $resultadoBuscar = $bdato->consulta($sqlBuscar);
    $idConversacion = null;

    if ($row = $bdato->fetch_assoc($resultadoBuscar)) {
        $idConversacion = $row['id']; // ya existe conversación
    } else {
        // ✅ 2. Si no existe, crear conversación
        $sqlCrear = "
            INSERT INTO conversaciones_chat (usuario_1, usuario_2, fecha_creacion)
            VALUES ('$idUsuarioDe', '$idUsuarioPara', NOW())
        ";
        $crear = $bdato->guardar($sqlCrear);

        if ($crear !== 0) {
            echo json_encode(["status" => "error", "message" => "No se pudo crear la conversación."]);
            exit;
        }

        // Recuperar el ID generado
        $idConversacion = $bdato->ultimo_id();
    }

    // ✅ 3. Insertar mensaje en mensajes_chat
    $sqlMensaje = "
        INSERT INTO mensajes_chat (
            id_conversacion,
            mensaje,
            de,
            para,
            fecha_hora,
            leido,
            urgente,
            eliminado,
            prioridad
        ) VALUES (
            '$idConversacion',
            '$mensaje',
            '$idUsuarioDe',
            '$idUsuarioPara',
            NOW(),
            0,
            0,
            0,
            'Media'
        );
    ";

    $resultadoMensaje = $bdato->guardar($sqlMensaje);

    if ($resultadoMensaje === 0) {
        echo json_encode([
            "status" => "success",
            "message" => "Mensaje enviado correctamente.",
            "id_conversacion" => $idConversacion
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Error al guardar el mensaje: " . $bdato->getLastError()
        ]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}
exit;
