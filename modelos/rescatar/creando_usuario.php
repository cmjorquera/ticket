<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

$db = new MySQL("", "", "");

$response = [
    'email_exists' => false,
    'anexo_exists' => false,
    'email_owner' => '',
    'anexo_owner' => ''
];

if (isset($_GET['email'])) {
    $email = $_GET['email'];
    $query = "SELECT * FROM usuarios WHERE email = '$email'";
    $consulta = $db->consulta($query);

    if ($row = $db->fetch_array($consulta)) {
        $response['email_exists'] = true;
        $response['email_owner'] = $row['nombre'] . " " . $row['apellido_paterno'];
    }
}

if (isset($_GET['anexo'])) {
    $anexo = $_GET['anexo'];
    $query = "SELECT * FROM usuarios WHERE anexo = '$anexo'";
    $consulta = $db->consulta($query);

    if ($row = $db->fetch_array($consulta)) {
        $response['anexo_exists'] = true;
        $response['anexo_owner'] = $row['nombre'] . " " . $row['apellido_paterno'];
    }
}

echo json_encode($response);
exit;
?>
