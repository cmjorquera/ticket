<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/PermisosManager.php';

$idUsuario = (int) ($_SESSION['id'] ?? 0);
$permisos = new PermisosManager($idUsuario);

// Dashboard: el rol principal decide la vista y los perfiles las capacidades.
$dashboard = $permisos->getRolPrincipal();
$perfilesDisponibles = $permisos->getPerfiles();

// Listado: SQL parametrizado según el alcance principal.
$sql = 'SELECT t.* FROM tickets t WHERE t.id_usuario = ?';
$parametros = [$idUsuario];

if ($permisos->esSuperAdmin()) {
    $sql = 'SELECT t.* FROM tickets t';
    $parametros = [];
} elseif ($permisos->esJefeDepartamento()) {
    $departamento = $permisos->getDepartamento();
    if ($departamento !== null) {
        $sql = 'SELECT DISTINCT t.*
                  FROM tickets t
                  JOIN usuarios u ON u.id = t.id_usuario
                  JOIN usuario_colegio uc ON uc.id_usuario = u.id AND uc.estado = 1
                 WHERE u.id_area_trabajo = ? AND uc.id_colegio = ?';
        $parametros = [(int) $departamento['id_departamento'], (int) $departamento['id_colegio']];
    }
} elseif ($permisos->esAdminColegio()) {
    $colegio = $permisos->getColegio();
    if ($colegio !== null) {
        $sql = 'SELECT DISTINCT t.* FROM tickets t
                  JOIN usuario_colegio uc ON uc.id_usuario = t.id_usuario AND uc.estado = 1
                 WHERE uc.id_colegio = ?';
        $parametros = [(int) $colegio['id_colegio']];
    }
} elseif ($permisos->esTecnico()) {
    $sql = 'SELECT t.* FROM tickets t WHERE t.id_tecnico = ?';
    $parametros = [$idUsuario];
}

// $db = Conexion::getInstance('sistema_panel_central');
// $tickets = $db->fetchAll($sql, $parametros);

// Permisos generales para controles de interfaz.
$mostrarCrear = $permisos->tienePermiso('crear_ticket');
$mostrarAsignar = $permisos->tienePermiso('asignar_tecnico');
$mostrarCambioEstado = $permisos->tienePermiso('cambiar_estado');

// Los endpoints deben validar nuevamente el recurso concreto.
$idTicket = (int) ($_GET['id_ticket'] ?? 0);
if ($idTicket > 0 && !$permisos->puedeVer($idTicket)) {
    http_response_code(403);
    exit('No tienes permiso para ver este ticket.');
}

$idTecnico = (int) ($_POST['id_tecnico'] ?? 0);
if ($idTecnico > 0 && !$permisos->puedeAsignar($idTecnico)) {
    http_response_code(403);
    exit('No tienes permiso para asignar este técnico.');
}

// Para credenciales diferentes, el segundo argumento acepta dos DSN:
/*
$permisos = new PermisosManager($idUsuario, [
    'principal' => [
        'dsn' => 'mysql:host=localhost;dbname=crist668_sistema_panel_central;charset=utf8mb4',
        'usuario' => 'usuario_principal',
        'password' => 'secreto_principal',
    ],
    'permisos' => [
        'dsn' => 'mysql:host=localhost;dbname=crist668_logica_permisos;charset=utf8mb4',
        'usuario' => 'usuario_permisos',
        'password' => 'secreto_permisos',
    ],
]);
*/

