<?php
require_once '../../class/conexion.php'; // Incluye la clase MySQL para la conexión a la base de datos

// Recibir el JSON enviado por el fetch
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['apellido_paterno'])) {
    $bdato = new MySQL('', '', ''); // Conexión a la base de datos
    $apellidoPaterno = $bdato->escape_string($data['apellido_paterno']);

    $apellidoPaterno = ucfirst(strtolower($apellidoPaterno)); // Esto transformará "JORQUERA" a "Jorquera"

    // Ahora puedes usar el apellido normalizado en la consulta
    $sqlCheck = "SELECT id, nombre, apellido_paterno FROM usuarios WHERE apellido_paterno = '$apellidoPaterno'";
    $resultado = $bdato->consulta($sqlCheck);

    

    if ($bdato->num_rows($resultado) > 0) {
        $usuarios = [];
        while ($row = $bdato->fetch_assoc($resultado)) {
            $usuarios[] = [
                'id'                => $row['id'],
                'nombre'            => $row['nombre'],
                'apellido_paterno'  => $row['apellido_paterno']
            ];
        }
        echo json_encode(['success' => true, 'usuarios' => $usuarios]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    }
}
?>