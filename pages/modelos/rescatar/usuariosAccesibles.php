<?php
header('Content-Type: application/json');
require_once '../../class/conexion.php';

$idUsuario = $_POST['id'] ?? null;

if (!$idUsuario) {
    echo json_encode(['success' => false, 'message' => 'ID no recibido']);
    exit;
}

$db = new MySQL("", "", "");

// Verificar si tiene perfil 3
$consulta = "SELECT id_perfil FROM usuario_perfil WHERE id_usuario = '$idUsuario'";
$res = $db->consulta($consulta);

$esAdmin = false;
while ($row = $db->fetch_array($res)) {
    if ((int)$row['id_perfil'] === 3) {
        $esAdmin = true;
        break;
    }
}

if ($esAdmin) {
    $queryUsuarios = "SELECT id, nombre, apellido_paterno FROM usuarios ORDER BY nombre";
} else {
    $queryUsuarios = "SELECT id, nombre, apellido_paterno FROM usuarios WHERE id = '$idUsuario'";
}

$resUsuarios = $db->consulta($queryUsuarios);
$usuarios = [];

while ($row = $db->fetch_array($resUsuarios)) {
    $usuarios[] = $row;
}

echo json_encode([
    'success' => true,
    'usuarios' => $usuarios,
    'esAdmin' => $esAdmin
]);
exit;
