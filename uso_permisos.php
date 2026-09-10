<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/PermisosManager.php';

$idUsuario = (int) ($_SESSION['id'] ?? 0);
$permisos = new PermisosManager($idUsuario);

// Ejemplo 1: adaptar una consulta de bandeja a cada rol.
$sql = 'SELECT * FROM tickets WHERE id_usuario = ?';
$parametros = [$idUsuario];

if ($permisos->esSuperAdmin()) {
    $sql = 'SELECT * FROM tickets';
    $parametros = [];
} elseif ($permisos->esJefeDepartamento()) {
    $departamento = $permisos->getDepartamento();
    if ($departamento !== null) {
        $sql = 'SELECT t.*
                  FROM tickets t
                  JOIN usuarios u ON u.id = t.id_usuario
                  JOIN usuario_colegio uc ON uc.id_usuario = u.id AND uc.estado = 1
                 WHERE u.id_area_trabajo = ? AND uc.id_colegio = ?';
        $parametros = [
            (int) $departamento['id_departamento'],
            (int) $departamento['id_colegio'],
        ];
    }
}

// $db = Conexion::getInstance('sistema_panel_central');
// $tickets = $db->fetchAll($sql, $parametros);

// Ejemplo 2: proteger acciones puntuales (IDs normalmente llegan por GET/POST).
$idTicket = (int) ($_GET['id_ticket'] ?? 0);
$idTecnico = (int) ($_POST['id_tecnico'] ?? 0);

if ($idTicket > 0 && !$permisos->puedeVer($idTicket)) {
    http_response_code(403);
    exit('No tienes permiso para ver este ticket.');
}

if ($idTecnico > 0 && !$permisos->puedeAsignar($idTecnico)) {
    http_response_code(403);
    exit('No tienes permiso para asignar este técnico.');
}

// Ejemplo 3: habilitar u ocultar controles de interfaz.
$puedeCrear = $permisos->tienePermiso('crear_ticket');
$puedeCambiarEstado = $permisos->tienePermiso('cambiar_estado');
$trabajadores = $permisos->getTrabajadores();

