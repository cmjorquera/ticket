<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Validar método de la petición
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método HTTP no permitido']);
    exit;
}

// Verificar que se haya enviado el ID
if (!isset($_POST['id'])) {
    echo json_encode(['error' => 'ID no proporcionado']);
    exit;
}

$eventoId = intval($_POST['id']);

require_once '../../class/conexion.php';
$bdato = new MySQL('', '', ''); // Conexión a la base de datos

$query = "SELECT 
            e.con_audio, 
            e.solo_presentacion, 
            e.musica_ambiental, 
            e.titulo, 
            e.descripcion, 
            e.fecha_inicio, 
            e.hora_evento, 
            e.creado_en, 
            e.eliminado,
            u.nombre,
            u.apellido_paterno,
            u.apellido_materno,
            u.email,
            u.foto,
            u.cargo
          FROM eventos e
          LEFT JOIN usuarios u ON e.responsable_id = u.id
          WHERE e.id = $eventoId";

$resultado = $bdato->consulta($query);

if ($resultado->num_rows > 0) {
    $evento = $resultado->fetch_assoc();

    // Se construye la respuesta usando los campos consultados
    $response = [
        'con_audio'         => (int)$evento['con_audio'],
        'solo_presentacion' => (int)$evento['solo_presentacion'],
        'musica_ambiental'  => (int)$evento['musica_ambiental'],
        'titulo'            => $evento['titulo'],
        'descripcion'       => $evento['descripcion'],
        'fecha_inicio'      => $evento['fecha_inicio'],
        'hora_evento'       => $evento['hora_evento'],
        'creado_en'         => $evento['creado_en'],
        'eliminado'         => $evento['eliminado'],
        'nombre'         => $evento['nombre'],
        'apellido_paterno'         => $evento['apellido_paterno'],


    ];
} else {
    $response = ['error' => 'Evento no encontrado'];
}

echo json_encode($response);
?>