<?php
include("../../class/conexion.php");
$db = new MySQL("", "", "");


// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Migrar datos de la tabla `tickett` a `tickets`
$sqlTickett = "SELECT id, fecha, hora, id_usuario, area_trabajo, asunto, ticket_texarea, archivo_adjunto, estado, prioridad, dias_asignados, id_asignacion, fecha_asignacion, fecha_asignacion_click, hora_asignacion_click, comentario_asignacion, dias_demora_manuel, dias_proceso, fecha_proceso, hora_proceso, fecha_termino, hora_termino, dias_termino, comentario_final FROM tickett";
$resultTickett = $conn->query($sqlTickett);

if ($resultTickett->num_rows > 0) {
    while ($row = $resultTickett->fetch_assoc()) {
        $id_ticket = $row["id"];
        $id_usuario = $row["id_usuario"];
        $asunto = $row["asunto"];
        $descripcion_ticket = $row["ticket_texarea"];
        $id_categoria_ticket = $row["area_trabajo"]; // Esto puede necesitar un mapeo específico
        $id_estado = $row["estado"];
        $id_prioridad = $row["prioridad"];
        $id_tecnico = $row["id_asignacion"];
        $comentario_administrador = $row["comentario_asignacion"];
        $identificador = null; // Asignar un valor apropiado o dejarlo como NULL
        
        $sqlInsertTicket = "INSERT INTO tickets (id_ticket, id_usuario, asunto, descripcion_ticket, id_categoria_ticket, id_estado, id_prioridad, id_tecnico, comentario_administrador, identificador) VALUES ('$id_ticket', '$id_usuario', '$asunto', '$descripcion_ticket', '$id_categoria_ticket', '$id_estado', '$id_prioridad', '$id_tecnico', '$comentario_administrador', '$identificador')";
        
        if (!$conn->query($sqlInsertTicket)) {
            echo "Error al insertar en tickets: " . $conn->error . "<br>";
        }
    }
}

// Migrar datos de la tabla `proceso_tickets` antigua a `proceso_tickets` nueva
$sqlProcesoTickets = "SELECT id_proceso, id_ticket, fecha_creacion_ticket, hora_creacion_ticket, fecha_administador_estima, dis_administrador_estima, fecha_asignacion_tecnico, hora_asignacion_tecnico, fecha_inicio, hora_inicio, fecha_termino, hora_termino FROM proceso_tickets";
$resultProcesoTickets = $conn->query($sqlProcesoTickets);

if ($resultProcesoTickets->num_rows > 0) {
    while ($row = $resultProcesoTickets->fetch_assoc()) {
        $id_proceso = $row["id_proceso"];
        $id_ticket = $row["id_ticket"];
        $fecha_creacion_inicio = $row["fecha_creacion_ticket"];
        $hora_creacion_inicio = $row["hora_creacion_ticket"];
        $fecha_estimada_admin = $row["fecha_administador_estima"];
        $dias_estimada_admin = $row["dis_administrador_estima"];
        $fecha_asignacion_tecnico = $row["fecha_asignacion_tecnico"];
        $hora_asignacion_tecnico = $row["hora_asignacion_tecnico"];
        $fecha_comienzo_ticket = $row["fecha_inicio"];
        $hora_comienzo_ticket = $row["hora_inicio"];
        $fecha_termino_ticket = $row["fecha_termino"];
        $hora_termino_ticket = $row["hora_termino"];
        
        $sqlInsertProcesoTickets = "INSERT INTO proceso_tickets (id_proceso, id_ticket, fecha_creacion_inicio, hora_creacion_inicio, fecha_estimada_admin, dias_estimada_admin, fecha_asignacion_tecnico, hora_asignacion_tecnico, fecha_comienzo_ticket, hora_comienzo_ticket, fecha_termino_ticket, hora_termino_ticket) VALUES ('$id_proceso', '$id_ticket', '$fecha_creacion_inicio', '$hora_creacion_inicio', '$fecha_estimada_admin', '$dias_estimada_admin', '$fecha_asignacion_tecnico', '$hora_asignacion_tecnico', '$fecha_comienzo_ticket', '$hora_comienzo_ticket', '$fecha_termino_ticket', '$hora_termino_ticket')";
        
        if (!$conn->query($sqlInsertProcesoTickets)) {
            echo "Error al insertar en proceso_tickets: " . $conn->error . "<br>";
        }
    }
}

// Cerrar conexión
$conn->close();

echo "Migración completada.";
?>
