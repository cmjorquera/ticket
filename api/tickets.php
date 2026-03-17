<?php
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'funciones_api.php';


$idUsuario = $_SESSION['id_usuario'] ?? 27;

$tickets = obtenerTicketsUsuario($idUsuario);


// echo "<pre>";
// print_r($tickets);
// echo "</pre>";


echo json_encode($tickets);
