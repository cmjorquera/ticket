<?php
require_once 'class/conexion.php';
require_once 'class/funciones.php';

$funciones = new Funciones();

$idUsuario = isset($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : null;

if ($idUsuario) {
    $datos = $funciones->obtenerDatosGraficoColegios2($idUsuario);
    echo $datos;
} else {
    echo json_encode([]);
}
?>