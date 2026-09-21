<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// Obtener los datos del formulario
$titulo             = $_POST['titulo'];
$descripcion        = $_POST['descripcion'];
$fechaInicio        = $_POST['fechaInicio'];
$horaEvento         = $_POST['horaEvento'];
$conAudio           = isset($_POST['conAudio']) ? 1 : 0;
$soloPresentacion   = isset($_POST['soloPresentacion']) ? 1 : 0;
$musicaAmbiental    = isset($_POST['musicaAmbiental']) ? 1 : 0;
$responsable_id     = $_POST['personaResponsable']; // Nueva columna
$cantidadInvitados     = $_POST['cantidadInvitados']; // Nueva columna


// Insertar los datos en la base de datos
$bdato = new MySQL("", "", ""); // Ajusta los valores de conexión

$sql = "INSERT INTO eventos (titulo, descripcion, fecha_inicio, hora_evento, con_audio, solo_presentacion, musica_ambiental, cantidad_personas, responsable_id, eliminado)
        VALUES ('$titulo', '$descripcion', '$fechaInicio', '$horaEvento', '$conAudio', '$soloPresentacion', '$musicaAmbiental','$cantidadInvitados', '$responsable_id', 'no')";

$resultado = $bdato->guardar($sql);

if ($resultado) {
    echo "Nuevo evento creado con éxito";
} else {
    echo "Error: " . $sql . "<br>" . $bdato->error;
}
echo json_encode(["success" => true]);
?>