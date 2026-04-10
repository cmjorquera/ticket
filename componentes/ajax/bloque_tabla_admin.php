<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("../../class/conexion.php");
require_once("../../class/funciones.php");
$funciones = new Funciones();

// Recibir por POST
$idUsuarioSession = isset($_POST['idUsuarioSession']) ? intval($_POST['idUsuarioSession']) : null;
$estado = isset($_POST['estado']) ? intval($_POST['estado']) : null;

if (!$idUsuarioSession) {
    echo "ID de usuario no recibido.";
    exit;
}

// Pasar el estado como variable global (para el include)
$GLOBALS['estadoTicketFiltro'] = $estado;
$GLOBALS['ticketAdminColorColumn'] = isset($_POST['vista']) && $_POST['vista'] === 'ticket_admin';
// Lógica principal: cargar la tabla con filtro si aplica
include("../../componentes/bloque_tabla_adminn.php");





