<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// Recolectar la variable que viene del JS
$idUsuario = $_POST["id"];

$db = new MySQL("","","");

// Consulta para obtener los datos del usuario y su área de trabajo
$st = "SELECT u.id, u.nombre, u.apellido_paterno, u.apellido_materno, u.anexo, u.email, u.id_area_trabajo, u.clave, u.telefono,u.sexo,
       a.nombre_area, a.encargado_area, a.sigla_area, a.correo_encargado 
       FROM usuarios u
       LEFT JOIN area_trabajo a ON u.id_area_trabajo = a.id_area
       WHERE u.id = '$idUsuario'";

$consulta = $db->consulta($st);

$listaUsuario = []; // Inicializado como un arreglo vacío

if ($db->num_rows($consulta) > 0) {
    $row = $db->fetch_array($consulta);
    $listaUsuario['id'] = $row['id'];
    $listaUsuario['nombre'] = $row['nombre'];
    $listaUsuario['apellido_paterno'] = $row['apellido_paterno'];
    $listaUsuario['apellido_materno'] = $row['apellido_materno'];
    $listaUsuario['anexo'] = $row['anexo'];
    $listaUsuario['clave'] = $row['clave'];
    $listaUsuario['sexo'] = $row['sexo'];
    $listaUsuario['email'] = $row['email'];
    $listaUsuario['telefono'] = $row['telefono'];
    $listaUsuario['id_area_trabajo'] = $row['id_area_trabajo'];
    $listaUsuario['nombre_area'] = $row['nombre_area'];
    $listaUsuario['encargado_area'] = $row['encargado_area'];
    $listaUsuario['sigla_area'] = $row['sigla_area'];
    $listaUsuario['correo_encargado'] = $row['correo_encargado'];
}

$st_colegio_actual = "SELECT id_colegio
                      FROM usuario_colegio
                      WHERE id_usuario = '$idUsuario' AND estado = 1
                      ORDER BY fecha_asignacion DESC, id DESC";
$consulta_colegio_actual = $db->consulta($st_colegio_actual);
if ($db->num_rows($consulta_colegio_actual) > 0) {
    $row_colegio_actual = $db->fetch_array($consulta_colegio_actual);
    $listaUsuario['id_colegio'] = $row_colegio_actual['id_colegio'];
} else {
    $listaUsuario['id_colegio'] = '';
}

// Consulta para obtener todas las áreas de trabajo
$st_areas = "SELECT id_area, nombre_area FROM area_trabajo";
$consulta_areas = $db->consulta($st_areas);

$areas = []; // Inicializar el arreglo para las áreas

if ($db->num_rows($consulta_areas) > 0) {
    while ($row_area = $db->fetch_array($consulta_areas)) {
        $area = [
            'id' => $row_area['id_area'],
            'nombre_area' => $row_area['nombre_area']
        ];
        $areas[] = $area; // Agregar cada área al arreglo
    }
}

// Consulta para obtener todos los colegios activos
$st_colegios = "SELECT id_colegio, nom_colegio FROM colegio WHERE estado = 1 ORDER BY nom_colegio ASC";
$consulta_colegios = $db->consulta($st_colegios);

$colegios = [];

if ($db->num_rows($consulta_colegios) > 0) {
    while ($row_colegio = $db->fetch_array($consulta_colegios)) {
        $colegios[] = [
            'id' => $row_colegio['id_colegio'],
            'nombre' => $row_colegio['nom_colegio']
        ];
    }
}

// Agregar las áreas de trabajo a los datos del usuario
$listaUsuario['areas'] = $areas;
$listaUsuario['colegios'] = $colegios;

echo json_encode($listaUsuario); // Devuelve el arreglo con los datos del usuario y su área de trabajo
?>
