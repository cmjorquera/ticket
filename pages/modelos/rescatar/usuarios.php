<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../class/conexion.php';

try {
    $bd = new MySQL("", "", "");

    $consultaUsuarios = "
        SELECT 
            u.id,
            u.nombre,
            u.apellido_paterno,
            u.apellido_materno,
            CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo_usuario,
            u.email,
            u.telefono
        FROM usuarios u
        ORDER BY u.nombre ASC
    ";

    $resultadoUsuarios = $bd->consulta($consultaUsuarios);

    $usuarios = [];

    while ($usuario = $bd->fetch_assoc($resultadoUsuarios)) {
        $idUsuario = $usuario['id'];

        $consultaPerfiles = "SELECT id_perfil FROM usuario_perfil WHERE id_usuario = '$idUsuario'";
        $resultadoPerfiles = $bd->consulta($consultaPerfiles);

        $perfiles = [];
        while ($perfil = $bd->fetch_assoc($resultadoPerfiles)) {
            $perfiles[] = (string)$perfil['id_perfil']; // 🔥 cast a string
        }

        $usuario['perfiles'] = $perfiles;
        $usuarios[] = $usuario;
    }

    echo json_encode([
        'success' => true,
        'usuarios' => $usuarios
    ]);

} catch (Exception $e) {
    error_log('Error al recuperar usuarios: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al recuperar usuarios',
        'error' => $e->getMessage()
    ]);
}
exit;
