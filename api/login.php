<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../clases/Session.php';
require_once __DIR__ . '/../clases/Usuario.php';

Session::iniciar();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

$correo   = trim($_POST['correo']   ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($correo) || empty($password)) {
    echo json_encode(['success' => false, 'mensaje' => 'Correo y contraseña son obligatorios']);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'mensaje' => 'Correo no válido']);
    exit;
}

try {
    $usuarioModel = new Usuario('sistema_panel_central');
    $usuario      = $usuarioModel->login($correo, $password);

    if (!$usuario) {
        echo json_encode(['success' => false, 'mensaje' => 'Credenciales incorrectas']);
        exit;
    }

    Session::set('usuario_id',       $usuario['id']);
    Session::set('usuario_nombre',   $usuario['nombre']);
    Session::set('usuario_apellido', $usuario['apellido']);
    Session::set('usuario_correo',   $usuario['correo']);
    Session::set('usuario_rol',      $usuario['rol']);

    echo json_encode([
        'success' => true,
        'mensaje' => 'Acceso correcto',
        'usuario' => [
            'id'       => $usuario['id'],
            'nombre'   => $usuario['nombre'],
            'apellido' => $usuario['apellido'],
            'correo'   => $usuario['correo'],
            'rol'      => $usuario['rol'],
        ],
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'Error interno del servidor']);
}
