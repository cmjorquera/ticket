<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../clases/Session.php';
require_once __DIR__ . '/../clases/Usuario.php';

Session::iniciar();

if (!Session::verificar()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'mensaje' => 'No autorizado']);
    exit;
}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
$model  = new Usuario('sistema_panel_central');

try {
    switch ($accion) {

        case 'listar':
            $usuarios = $model->getAll();
            echo json_encode(['success' => true, 'data' => $usuarios]);
            break;

        case 'obtener':
            $id      = (int) ($_GET['id'] ?? 0);
            $usuario = $model->getById($id);
            if (!$usuario) {
                echo json_encode(['success' => false, 'mensaje' => 'Usuario no encontrado']);
                break;
            }
            echo json_encode(['success' => true, 'data' => $usuario]);
            break;

        case 'crear':
            $datos = [
                'nombre'   => trim($_POST['nombre']   ?? ''),
                'apellido' => trim($_POST['apellido'] ?? ''),
                'correo'   => trim($_POST['correo']   ?? ''),
                'password' => $_POST['password']      ?? '',
                'rol'      => trim($_POST['rol']      ?? 'usuario'),
            ];

            if (empty($datos['nombre']) || empty($datos['correo']) || empty($datos['password'])) {
                echo json_encode(['success' => false, 'mensaje' => 'Nombre, correo y contraseña son obligatorios']);
                break;
            }

            $id = $model->crear($datos);
            if (!$id) {
                echo json_encode(['success' => false, 'mensaje' => 'No se pudo crear el usuario (el correo puede estar en uso)']);
                break;
            }
            echo json_encode(['success' => true, 'mensaje' => 'Usuario creado', 'id' => $id]);
            break;

        case 'actualizar':
            $id    = (int) ($_POST['id'] ?? 0);
            $datos = [];
            foreach (['nombre', 'apellido', 'correo', 'rol', 'activo', 'password'] as $campo) {
                if (isset($_POST[$campo]) && $_POST[$campo] !== '') {
                    $datos[$campo] = $_POST[$campo];
                }
            }

            $ok = $model->actualizar($id, $datos);
            echo json_encode(['success' => $ok, 'mensaje' => $ok ? 'Actualizado' : 'No se pudo actualizar']);
            break;

        case 'eliminar':
            $id = (int) ($_POST['id'] ?? 0);
            $ok = $model->eliminar($id);
            echo json_encode(['success' => $ok, 'mensaje' => $ok ? 'Eliminado' : 'No se pudo eliminar']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'mensaje' => 'Acción no reconocida']);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'Error interno del servidor']);
}
