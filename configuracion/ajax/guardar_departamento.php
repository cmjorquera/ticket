<?php
declare(strict_types=1);

require_once __DIR__ . '/../../ajax/_bootstrap.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responder_json(['success' => false, 'message' => 'Método no permitido.'], 405);
}

function puede_crear_departamento(Conexion $db, int $idUsuario): bool
{
    $perfil = strtolower(trim((string) Sesion::get('perfil', '')));
    $perfil = preg_replace('/[\s_-]+/', ' ', $perfil) ?: '';
    $esSuperAdmin = in_array($perfil, ['super admin', 'superadmin'], true)
        || (bool) $db->fetchOne(
            'SELECT 1 FROM usuario_perfil WHERE id_usuario = ? AND id_perfil = 3 LIMIT 1',
            [$idUsuario]
        );
    return $esSuperAdmin;
}

$datos = entrada_ajax();
$csrf = (string) ($datos['csrf'] ?? '');
$csrfSesion = (string) ($_SESSION['csrf_departamentos'] ?? '');
if ($csrfSesion === '' || !hash_equals($csrfSesion, $csrf)) {
    responder_json(['success' => false, 'message' => 'La sesión de edición expiró. Recarga la página.'], 419);
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $pdo = $db->getPDO();
    $usuarioActual = (int) Sesion::get('id', 0);
    $idColegio = max(0, (int) ($datos['id_colegio'] ?? 0));
    $nombre = trim((string) ($datos['nombre_departamento'] ?? ''));
    $sigla = strtoupper(trim((string) ($datos['sigla'] ?? '')));

    if ($idColegio <= 0 || !$db->fetchOne('SELECT id_colegio FROM colegio WHERE id_colegio = ? AND estado = 1 LIMIT 1', [$idColegio])) {
        responder_json(['success' => false, 'message' => 'El colegio indicado no existe.'], 404);
    }
    if (!puede_crear_departamento($db, $usuarioActual)) {
        responder_json(['success' => false, 'message' => 'Solo un super admin puede agregar departamentos.'], 403);
    }
    if ($nombre === '') {
        responder_json(['success' => false, 'message' => 'El nombre del departamento es obligatorio.'], 422);
    }
    if (!preg_match('/^[A-ZÁÉÍÓÚÑ0-9]{2,3}$/u', $sigla)) {
        responder_json(['success' => false, 'message' => 'La sigla debe contener entre 2 y 3 caracteres.'], 422);
    }
    if ($db->fetchOne(
        'SELECT id FROM departamentos_colegio WHERE id_colegio = ? AND LOWER(sigla) = LOWER(?) LIMIT 1',
        [$idColegio, $sigla]
    )) {
        responder_json(['success' => false, 'message' => 'La sigla ya está registrada en este colegio.'], 409);
    }
    if ($db->fetchOne(
        'SELECT id FROM departamentos_colegio WHERE id_colegio = ? AND LOWER(nombre_departamento) = LOWER(?) LIMIT 1',
        [$idColegio, $nombre]
    )) {
        responder_json(['success' => false, 'message' => 'Ya existe un departamento con ese nombre en el colegio.'], 409);
    }

    $pdo->beginTransaction();
    try {
        $db->execute(
            'INSERT INTO departamentos_colegio
                (id_colegio, nombre_departamento, sigla, estado)
             VALUES (?, ?, ?, 1)',
            [$idColegio, $nombre, $sigla]
        );
        $idDepartamento = (int) $db->lastInsertId();
        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $ex;
    }

    error_log(sprintf(
        'Departamento creado: usuario=%d colegio=%d departamento=%d sigla=%s',
        $usuarioActual,
        $idColegio,
        $idDepartamento,
        $sigla
    ));
    responder_json(['success' => true, 'message' => 'Departamento creado', 'id' => $idDepartamento]);
} catch (Throwable $ex) {
    error_log('Error al guardar departamento: ' . $ex->getMessage());
    responder_json(['success' => false, 'message' => 'No fue posible crear el departamento.'], 500);
}
