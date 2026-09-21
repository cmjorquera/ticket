<?php
require_once '../../class/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id_conversacion']) || !is_numeric($_POST['id_conversacion'])) {
        echo json_encode(["status" => "error", "message" => "ID de conversación inválido."]);
        exit;
    }

    $idConversacion = intval($_POST['id_conversacion']);
    $bdato = new MySQL("", "", "");
    $sql = "
    SELECT 
        mc.id_conversacion AS id_mensaje,
        mc.mensaje,
        mc.para,
        mc.de,
        mc.fecha_hora,
        ue.id AS id_usuario,
        ue.nombre AS nombre_emisor,
        ue.apellido_paterno AS apellido_emisor,
        ur.nombre AS nombre_receptor,
        ur.apellido_paterno AS apellido_receptor
    FROM mensajes_chat mc
    LEFT JOIN usuarios ue ON ue.id = mc.de
    LEFT JOIN usuarios ur ON ur.id = mc.para
    WHERE mc.id_conversacion = $idConversacion
    ORDER BY mc.fecha_hora desc
";

    $result = $bdato->consulta($sql);
    $mensajes = [];

    while ($row = $bdato->fetch_assoc($result)) {
        $mensajes[] = [
            "id_mensaje"        => $row["id_mensaje"],
            "mensaje"           => $row["mensaje"],
            "para"              => $row["para"],
            "de"                => $row["de"],
            "id_usuario"        => $row["id_usuario"],
            "nombre_emisor"     => $row["nombre_emisor"],
            "apellido_emisor"   => $row["apellido_emisor"],
            "nombre_receptor"   => $row["nombre_receptor"],
            "apellido_receptor" => $row["apellido_receptor"],
            "fecha"             => $row["fecha_hora"],
            "hora"              => date("H:i", strtotime($row["fecha_hora"])) // hora separada opcional
        ];
    }

    $primerMensaje = count($mensajes) > 0 ? $mensajes[0]["mensaje"] : "Conversación $idConversacion";

    echo json_encode([
        "status"                => "success",
        "idConversacionActual"  => $idConversacion,
        "idUsuarioDeActual"     => $mensajes[0]["de"] ?? null,
        "idUsuarioParaActual"   => $mensajes[0]["para"] ?? null,
        "primerMensaje"         => $primerMensaje,
        "mensajes"              => $mensajes
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}
