<?php
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// Recolectar los datos del formulario
$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : null;
$permisos = isset($_POST["permisos"]) ? json_decode($_POST["permisos"], true) : [];

if ($user_id === null || empty($permisos)) {
    echo "Datos insuficientes para realizar la operación.";
    exit;
}

$db = new MySQL("seduc", "", "");

foreach ($permisos as $permiso) {
    $id_menu1 = $permiso['id_menu1'];
    $id_tipo_permiso = $permiso['id_tipo_permiso'];

    // Verificar si ya existe un registro para el id_menu1 y user_id
    $check_sql = "SELECT COUNT(*) as count FROM permisos_menu_1 WHERE id_menu1 = '$id_menu1' AND id_usuario = '$user_id'";
    $result = $db->consulta($check_sql);
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // Si existe, actualiza el registro
        $sql = "UPDATE permisos_menu_1 SET id_tipo_permiso = '$id_tipo_permiso' WHERE id_menu1 = '$id_menu1' AND id_usuario = '$user_id'";
    } else {
        // Si no existe, inserta un nuevo registro
        $sql = "INSERT INTO permisos_menu_1 (id_menu1, id_usuario, id_tipo_permiso) VALUES ('$id_menu1', '$user_id', '$id_tipo_permiso')";
    }


    echo $sql;
    $bl = $db->guardar($sql);

    if ($bl !== 0) { // Asumiendo que el método guardar devuelve 0 en caso de éxito
        echo "Error al guardar el permiso para id_menu1 $id_menu1.";
        $db->CerrarConexion();
        exit;
    }
}

echo "Permisos guardados correctamente para el usuario $user_id.";
$db->CerrarConexion();
?>
