<?php
/**
 * Opciones del formulario "Nuevo usuario" para quien está conectado:
 * colegios que puede usar, los departamentos de CADA colegio y los cargos
 * (roles) que puede asignar en él. Reglas: clases/RolesUsuario.php.
 */
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../clases/RolesUsuario.php';
Sesion::requerir();

try {
    $actor = (int) ($_SESSION['id'] ?? 0);
    $roles = new RolesUsuario(Conexion::getInstance('sistema_panel_central'));
    if (!$roles->modeloActivo()) {
        responder_json(['ok' => false, 'mensaje' => 'Falta ejecutar configuracion/sql/01_roles_estructura.sql y 02_roles_asignaciones.sql.'], 503);
    }
    responder_json(['ok' => true, 'data' => [
        'super_admin' => $roles->esSuperAdmin($actor),
        'colegios' => $roles->opcionesAlta($actor),
        'roles' => RolesUsuario::ROLES,
        'id_seduc' => RolesUsuario::ID_SEDUC,
    ]]);
} catch (Throwable $e) {
    error_log('[usuarios_alta_opciones] ' . $e->getMessage());
    responder_json(['ok' => false, 'mensaje' => 'No fue posible cargar las opciones.'], 500);
}
