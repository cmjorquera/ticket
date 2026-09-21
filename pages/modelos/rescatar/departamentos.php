<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../ajax/_bootstrap.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    responder_json(['ok' => false, 'error' => 'Método no permitido.'], 405);
}

function es_super_admin_departamentos(Conexion $db, int $idUsuario): bool
{
    $perfil = strtolower(trim((string) Sesion::get('perfil', '')));
    $perfil = preg_replace('/[\s_-]+/', ' ', $perfil) ?: '';
    if (in_array($perfil, ['super admin', 'superadmin'], true)) {
        return true;
    }
    return (bool) $db->fetchOne(
        'SELECT 1 FROM usuario_perfil WHERE id_usuario = ? AND id_perfil = 3 LIMIT 1',
        [$idUsuario]
    );
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $usuarioActual = (int) Sesion::get('id', 0);
    $idColegio = max(0, (int) ($_GET['id_colegio'] ?? 0));

    if (!es_super_admin_departamentos($db, $usuarioActual)) {
        responder_json(['ok' => false, 'error' => 'Solo un super admin puede consultar departamentos.'], 403);
    }
    if ($idColegio <= 0 || !$db->fetchOne('SELECT id_colegio FROM colegio WHERE id_colegio = ? LIMIT 1', [$idColegio])) {
        responder_json(['ok' => false, 'error' => 'El colegio indicado no existe.'], 404);
    }

    $departamentos = array_map(
        static fn (array $fila): array => [
            'id' => (int) $fila['id'],
            'nombre' => (string) $fila['nombre_departamento'],
        ],
        $db->fetchAll(
            'SELECT id, nombre_departamento
               FROM departamentos_colegio
              WHERE id_colegio = ? AND estado = 1
              ORDER BY nombre_departamento ASC',
            [$idColegio]
        )
    );

    responder_json($departamentos);
} catch (Throwable $ex) {
    error_log('Error al rescatar departamentos: ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible cargar los departamentos.'], 500);
}
