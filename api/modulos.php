<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../clases/Session.php';
require_once __DIR__ . '/../clases/Conexion.php';

Session::iniciar();

if (!Session::verificar()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'mensaje' => 'No autorizado']);
    exit;
}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
$db     = new Conexion('sistema_panel_central');

try {
    switch ($accion) {

        case 'listar':
            $modulos = $db->fetchAll("SELECT * FROM modulos ORDER BY orden ASC, nombre ASC");
            echo json_encode(['success' => true, 'data' => $modulos]);
            break;

        case 'obtener':
            $id     = (int) ($_GET['id'] ?? 0);
            $modulo = $db->fetchOne("SELECT * FROM modulos WHERE id = ? LIMIT 1", [$id]);
            if (!$modulo) {
                echo json_encode(['success' => false, 'mensaje' => 'Módulo no encontrado']);
                break;
            }
            echo json_encode(['success' => true, 'data' => $modulo]);
            break;

        case 'crear':
            $nombre      = trim($_POST['nombre']      ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $ruta        = trim($_POST['ruta']        ?? '');
            $icono       = trim($_POST['icono']       ?? '');
            $orden       = (int) ($_POST['orden']     ?? 0);

            if (empty($nombre)) {
                echo json_encode(['success' => false, 'mensaje' => 'El nombre es obligatorio']);
                break;
            }

            $db->execute(
                "INSERT INTO modulos (nombre, descripcion, ruta, icono, orden, activo, created_at)
                 VALUES (?, ?, ?, ?, ?, 1, NOW())",
                [$nombre, $descripcion, $ruta, $icono, $orden]
            );
            echo json_encode(['success' => true, 'mensaje' => 'Módulo creado', 'id' => $db->lastInsertId()]);
            break;

        case 'actualizar':
            $id = (int) ($_POST['id'] ?? 0);
            $campos  = [];
            $valores = [];

            foreach (['nombre', 'descripcion', 'ruta', 'icono', 'orden', 'activo'] as $campo) {
                if (isset($_POST[$campo])) {
                    $campos[]  = "$campo = ?";
                    $valores[] = $_POST[$campo];
                }
            }

            if (empty($campos)) {
                echo json_encode(['success' => false, 'mensaje' => 'No hay campos para actualizar']);
                break;
            }

            $valores[] = $id;
            $filas = $db->execute(
                "UPDATE modulos SET " . implode(', ', $campos) . " WHERE id = ?",
                $valores
            );
            echo json_encode(['success' => $filas > 0, 'mensaje' => $filas > 0 ? 'Actualizado' : 'Sin cambios']);
            break;

        case 'eliminar':
            $id    = (int) ($_POST['id'] ?? 0);
            $filas = $db->execute("UPDATE modulos SET activo = 0 WHERE id = ?", [$id]);
            echo json_encode(['success' => $filas > 0, 'mensaje' => $filas > 0 ? 'Eliminado' : 'No encontrado']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'mensaje' => 'Acción no reconocida']);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'Error interno del servidor']);
}
