<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

if (!isset($_POST['id'], $_POST['titulo'], $_POST['descripcion'], $_POST['fecha'], $_POST['hora'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Faltan datos obligatorios']);
    exit;
}

require_once '../../class/conexion.php';
$bdato = new MySQL('', '', '');

// Sanitización
$id             = intval($_POST['id']);
$titulo         = $_POST['titulo'];
$descripcion    = $_POST['descripcion'];
$fecha          = $_POST['fecha'];
$hora           = $_POST['hora'];
$conAudio = isset($_POST['con_audio']) && $_POST['con_audio'] == 'true' ? 1 : 0;
$soloPresentacion = isset($_POST['solo_presentacion']) && $_POST['solo_presentacion'] == 'true' ? 1 : 0;
$musicaAmbiental = isset($_POST['musica_ambiental']) && $_POST['musica_ambiental'] == 'true' ? 1 : 0;

$sql = "UPDATE eventos SET 
            titulo = '$titulo',
            descripcion = '$descripcion',
            fecha_inicio = '$fecha',
            hora_evento = '$hora',
            con_audio = $conAudio,
            solo_presentacion = $soloPresentacion,
            musica_ambiental = $musicaAmbiental
        WHERE id = $id";

if ($bdato->consulta($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar el evento']);
}
?>
