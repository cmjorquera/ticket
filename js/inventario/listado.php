<?php
session_start();
require_once '../../class/conexion.php';
require_once '../../class/funciones.php';

$funciones = new Funciones();

$idUsuario = (int)($_SESSION['id'] ?? 0);
$idColegio = (int)($_GET['colegio'] ?? 0);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($funciones->invPC_getListadoEquipos($idUsuario, $idColegio), JSON_UNESCAPED_UNICODE);
