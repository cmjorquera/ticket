<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../ajax/_bootstrap.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responder_json(['ok' => false, 'error' => 'Método no permitido.'], 405);
}

function es_super_admin_edicion(Conexion $db, int $idUsuario): bool
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

function ids_perfiles_edicion(mixed $valor): array
{
    if (is_string($valor)) {
        $decodificado = json_decode($valor, true);
        $valor = is_array($decodificado) ? $decodificado : explode(',', $valor);
    }
    if (!is_array($valor)) {
        return [];
    }
    return array_values(array_unique(array_filter(
        array_map('intval', $valor),
        static fn (int $id): bool => $id > 0
    )));
}

$datos = entrada_ajax();
$csrf = (string) ($datos['csrf'] ?? '');
$csrfSesion = (string) ($_SESSION['csrf_editar_usuario_avanzado'] ?? '');
if ($csrfSesion === '' || !hash_equals($csrfSesion, $csrf)) {
    responder_json(['ok' => false, 'error' => 'La sesión de edición expiró. Recarga la página.'], 419);
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $pdo = $db->getPDO();
    $usuarioActual = (int) Sesion::get('id', 0);
    $idUsuario = max(0, (int) ($datos['id_usuario'] ?? $datos['id'] ?? 0));
    $idColegio = max(0, (int) ($datos['id_colegio'] ?? 0));
    $idDepartamento = max(0, (int) ($datos['id_departamento_colegio'] ?? 0));
    $perfiles = ids_perfiles_edicion($datos['perfiles'] ?? []);

    if (!es_super_admin_edicion($db, $usuarioActual)) {
        responder_json(['ok' => false, 'error' => 'Solo un super admin puede cambiar departamentos y perfiles.'], 403);
    }
    if ($idUsuario <= 0 || !$db->fetchOne('SELECT id FROM usuarios WHERE id = ? LIMIT 1', [$idUsuario])) {
        responder_json(['ok' => false, 'error' => 'El usuario indicado no existe.'], 404);
    }
    if ($idColegio <= 0 || !$db->fetchOne('SELECT id_colegio FROM colegio WHERE id_colegio = ? AND estado = 1 LIMIT 1', [$idColegio])) {
        responder_json(['ok' => false, 'error' => 'Selecciona un colegio válido.'], 422);
    }
    if ($idDepartamento <= 0 || !$db->fetchOne(
        'SELECT id FROM departamentos_colegio WHERE id = ? AND id_colegio = ? AND estado = 1 LIMIT 1',
        [$idDepartamento, $idColegio]
    )) {
        responder_json(['ok' => false, 'error' => 'Selecciona un departamento válido para el colegio.'], 422);
    }
    if ($perfiles === []) {
        responder_json(['ok' => false, 'error' => 'Selecciona al menos un perfil.'], 422);
    }

    $marcas = implode(',', array_fill(0, count($perfiles), '?'));
    $perfilesValidos = $db->fetchAll(
        "SELECT id_perfil FROM perfiles WHERE estado = 1 AND id_perfil IN ($marcas)",
        $perfiles
    );
    if (count($perfilesValidos) !== count($perfiles)) {
        responder_json(['ok' => false, 'error' => 'Uno de los perfiles seleccionados no existe o está inactivo.'], 422);
    }

    $nombre = trim((string) ($datos['nombre'] ?? ''));
    $apellidoPaterno = trim((string) ($datos['apellido_paterno'] ?? ''));
    $apellidoMaterno = trim((string) ($datos['apellido_materno'] ?? ''));
    $email = strtolower(trim((string) ($datos['email'] ?? '')));
    $telefono = trim((string) ($datos['telefono'] ?? ''));
    $sexo = trim((string) ($datos['sexo'] ?? ''));
    $idArea = max(0, (int) ($datos['id_area_trabajo'] ?? 0));

    if ($nombre === '' || $apellidoPaterno === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $idArea <= 0 || $sexo === '') {
        responder_json(['ok' => false, 'error' => 'Completa correctamente los datos obligatorios del usuario.'], 422);
    }
    if ($db->fetchOne('SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1', [$email, $idUsuario])) {
        responder_json(['ok' => false, 'error' => 'El email ya está registrado por otro usuario.'], 409);
    }
    if (!$db->fetchOne('SELECT id_area FROM area_trabajo WHERE id_area = ? LIMIT 1', [$idArea])) {
        responder_json(['ok' => false, 'error' => 'El área seleccionada no existe.'], 422);
    }

    $pdo->beginTransaction();
    try {
        $db->execute(
            'UPDATE usuarios
                SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?,
                    id_area_trabajo = ?, telefono = ?, sexo = ?
              WHERE id = ?',
            [$nombre, $apellidoPaterno, $apellidoMaterno, $email, $idArea, $telefono, $sexo, $idUsuario]
        );

        $db->execute('DELETE FROM jefatura_departamento WHERE id_usuario = ?', [$idUsuario]);
        $db->execute(
            "INSERT INTO jefatura_departamento
                (id_usuario, id_colegio, id_departamento_colegio, tipo_jefatura, estado)
             VALUES (?, ?, ?, 'Admin_Departamento', 1)",
            [$idUsuario, $idColegio, $idDepartamento]
        );

        $db->execute('DELETE FROM usuario_perfil WHERE id_usuario = ?', [$idUsuario]);
        foreach ($perfiles as $idPerfil) {
            $db->execute(
                'INSERT INTO usuario_perfil (id_usuario, id_perfil, estado) VALUES (?, ?, 1)',
                [$idUsuario, $idPerfil]
            );
        }

        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $ex;
    }

    error_log(sprintf(
        'Usuario actualizado por super admin: administrador=%d usuario=%d colegio=%d departamento=%d perfiles=%s',
        $usuarioActual,
        $idUsuario,
        $idColegio,
        $idDepartamento,
        implode(',', $perfiles)
    ));

    responder_json(['ok' => true, 'mensaje' => 'Usuario actualizado']);
} catch (Throwable $ex) {
    error_log('Error al editar usuario avanzado: ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible actualizar el usuario.'], 500);
}
