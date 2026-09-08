<?php
require_once '../class/conexion.php';
require_once '../class/Funciones.php';
session_start();

$bdato = new MySQL("", "", "");
$funciones = new Funciones();

$idUsuario = $_SESSION['id'];
$tipoUsuario = $_GET['tipoUsuario'] ?? 'auto';

if ($tipoUsuario === 'auto') {
    // Detectar perfil por defecto del usuario
    $sqlPerfil = "SELECT p.nombre FROM usuario_perfil up 
                  JOIN perfiles p ON up.id_perfil = p.id_perfil 
                  WHERE up.id_usuario = $idUsuario LIMIT 1";
    $res = $bdato->consulta($sqlPerfil);
    $row = $bdato->fetch_array($res);
    $tipoUsuario = strtolower($row['nombre'] ?? 'usuario');
}

$sql = "";
switch ($tipoUsuario) {
    case 'usuario':
        $sql = "SELECT e.nombre AS estado, COUNT(t.id_ticket) AS cantidad_tickets, e.color 
                FROM tickets t 
                INNER JOIN estado e ON t.id_estado = e.id 
                WHERE t.id_usuario = $idUsuario AND t.eliminado = 0
                GROUP BY e.id";
        break;

    case 'tecnico':
        $sql = "SELECT e.nombre AS estado, COUNT(t.id_ticket) AS cantidad_tickets, e.color 
                FROM tickets t 
                INNER JOIN estado e ON t.id_estado = e.id 
                WHERE t.id_tecnico = $idUsuario AND t.eliminado = 0
                GROUP BY e.id";
        break;

    case 'admin':
        $sql = "SELECT e.nombre AS estado, COUNT(t.id_ticket) AS cantidad_tickets, e.color 
                FROM tickets t 
                INNER JOIN estado e ON t.id_estado = e.id 
                WHERE t.eliminado = 0
                GROUP BY e.id";
        break;
}

$res = $bdato->consulta($sql);
$data = [];

while ($row = $bdato->fetch_array($res)) {
    $data[] = [
        'estado' => $row['estado'],
        'cantidad_tickets' => (int)$row['cantidad_tickets'],
        'color_estado' => $row['color']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
