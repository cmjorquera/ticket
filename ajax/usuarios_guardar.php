<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responder_json(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
}

$datos = entrada_ajax();
$action = strtolower(trim((string) ($datos['action'] ?? '')));

function ids_recibidos(mixed $valor): array
{
    if (is_string($valor)) {
        $decodificado = json_decode($valor, true);
        $valor = is_array($decodificado) ? $decodificado : explode(',', $valor);
    }
    if (!is_array($valor)) {
        return [];
    }
    $ids = array_map('intval', $valor);
    return array_values(array_unique(array_filter($ids, static fn (int $id): bool => $id > 0)));
}

function menus_validos(Conexion $db, array $ids): array
{
    if (!$ids) {
        return [];
    }
    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $filas = $db->fetchAll("SELECT id_menu FROM menu_1 WHERE id_menu IN ($marcas)", $ids);
    return array_map('intval', array_column($filas, 'id_menu'));
}

function guardar_permisos_usuario(Conexion $db, int $idUsuario, array $menuIds): void
{
    $db->execute('DELETE FROM permisos_menu_1 WHERE id_usuario = ?', [$idUsuario]);
    $columna = $db->fetchOne(
        "SELECT COUNT(*) AS total FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'permisos_menu_1'
            AND COLUMN_NAME = 'id_submenu'"
    );
    $guardaSubmenus = (int) ($columna['total'] ?? 0) > 0;

    foreach (menus_validos($db, $menuIds) as $idMenu) {
        if ($guardaSubmenus) {
            $db->execute(
                'INSERT INTO permisos_menu_1 (id_usuario, id_menu1, id_submenu, id_tipo_permiso) VALUES (?, ?, NULL, 1)',
                [$idUsuario, $idMenu]
            );
            foreach ($db->fetchAll('SELECT id_submenu FROM menu_1_sub WHERE id_menu = ?', [$idMenu]) as $submenu) {
                $db->execute(
                    'INSERT INTO permisos_menu_1 (id_usuario, id_menu1, id_submenu, id_tipo_permiso) VALUES (?, ?, ?, 1)',
                    [$idUsuario, $idMenu, (int) $submenu['id_submenu']]
                );
            }
        } else {
            $db->execute(
                'INSERT INTO permisos_menu_1 (id_usuario, id_menu1, id_tipo_permiso) VALUES (?, ?, 1)',
                [$idUsuario, $idMenu]
            );
        }
    }
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $pdo = $db->getPDO();

    if ($action === 'crear') {
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        $apellidoPaterno = trim((string) ($datos['apellido_paterno'] ?? ''));
        $apellidoMaterno = trim((string) ($datos['apellido_materno'] ?? ''));
        $email = strtolower(trim((string) ($datos['email'] ?? '')));
        $telefono = trim((string) ($datos['telefono'] ?? ''));
        $sexo = trim((string) ($datos['sexo'] ?? ''));
        $idArea = (int) ($datos['id_area_trabajo'] ?? 0);
        $idColegio = (int) ($datos['id_colegio'] ?? 0);
        $menuIds = ids_recibidos($datos['menus'] ?? []);

        if ($nombre === '' || $apellidoPaterno === '' || $email === '' || $idArea <= 0 || $sexo === '') {
            responder_json(['ok' => false, 'mensaje' => 'Nombre, apellido paterno, email, área y sexo son obligatorios.'], 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            responder_json(['ok' => false, 'mensaje' => 'El email no es válido.'], 422);
        }
        if ($db->fetchOne('SELECT id FROM usuarios WHERE email = ? LIMIT 1', [$email])) {
            responder_json(['ok' => false, 'mensaje' => 'El email ya está registrado.'], 409);
        }
        if (!$db->fetchOne('SELECT id_area FROM area_trabajo WHERE id_area = ? LIMIT 1', [$idArea])) {
            responder_json(['ok' => false, 'mensaje' => 'El área seleccionada no existe.'], 422);
        }
        if ($idColegio > 0 && !$db->fetchOne('SELECT id_colegio FROM colegio WHERE id_colegio = ? AND estado = 1 LIMIT 1', [$idColegio])) {
            responder_json(['ok' => false, 'mensaje' => 'El colegio seleccionado no está disponible.'], 422);
        }

        $pdo->beginTransaction();
        try {
            $token = bin2hex(random_bytes(32));
            $db->execute(
                "INSERT INTO usuarios
                    (nombre, apellido_paterno, apellido_materno, email, clave, estado,
                     intentos_fallidos, id_area_trabajo, telefono, sexo, token_reinicio)
                 VALUES (?, ?, ?, ?, NULL, 'Pendiente', 0, ?, ?, ?, ?)",
                [$nombre, $apellidoPaterno, $apellidoMaterno, $email, $idArea, $telefono, $sexo, $token]
            );
            $idUsuario = (int) $db->lastInsertId();

            guardar_permisos_usuario($db, $idUsuario, $menuIds);
            $db->execute('INSERT INTO usuario_perfil (id_usuario, id_perfil) VALUES (?, 1)', [$idUsuario]);

            if ($idColegio > 0) {
                $db->execute(
                    'INSERT INTO usuario_colegio
                        (id_usuario, id_colegio, id_perfil, estado, es_admin_colegio, fecha_asignacion)
                     VALUES (?, ?, 1, 1, 0, NOW())',
                    [$idUsuario, $idColegio]
                );
            }
            $pdo->commit();
            responder_json(['ok' => true, 'mensaje' => 'Usuario creado en estado pendiente.', 'id' => $idUsuario]);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    $idUsuario = (int) ($datos['id'] ?? $datos['id_usuario'] ?? 0);
    if ($idUsuario <= 0 || !$db->fetchOne('SELECT id FROM usuarios WHERE id = ? LIMIT 1', [$idUsuario])) {
        responder_json(['ok' => false, 'mensaje' => 'Usuario no encontrado.'], 404);
    }

    if ($action === 'editar') {
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        $apellidoPaterno = trim((string) ($datos['apellido_paterno'] ?? ''));
        $apellidoMaterno = trim((string) ($datos['apellido_materno'] ?? ''));
        $email = strtolower(trim((string) ($datos['email'] ?? '')));
        $telefono = trim((string) ($datos['telefono'] ?? ''));
        $sexo = trim((string) ($datos['sexo'] ?? ''));
        $idArea = (int) ($datos['id_area_trabajo'] ?? 0);

        if ($nombre === '' || $apellidoPaterno === '' || $email === '' || $idArea <= 0 || $sexo === '') {
            responder_json(['ok' => false, 'mensaje' => 'Nombre, apellido paterno, email, área y sexo son obligatorios.'], 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            responder_json(['ok' => false, 'mensaje' => 'El email no es válido.'], 422);
        }
        if ($db->fetchOne('SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1', [$email, $idUsuario])) {
            responder_json(['ok' => false, 'mensaje' => 'El email ya está registrado por otro usuario.'], 409);
        }
        if (!$db->fetchOne('SELECT id_area FROM area_trabajo WHERE id_area = ? LIMIT 1', [$idArea])) {
            responder_json(['ok' => false, 'mensaje' => 'El área seleccionada no existe.'], 422);
        }

        $db->execute(
            'UPDATE usuarios
                SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?,
                    id_area_trabajo = ?, telefono = ?, sexo = ?
              WHERE id = ?',
            [$nombre, $apellidoPaterno, $apellidoMaterno, $email, $idArea, $telefono, $sexo, $idUsuario]
        );
        responder_json(['ok' => true, 'mensaje' => 'Usuario actualizado.']);
    }

    if ($action === 'bloquear') {
        $db->execute("UPDATE usuarios SET estado = 'bloqueado' WHERE id = ?", [$idUsuario]);
        responder_json(['ok' => true, 'mensaje' => 'Usuario bloqueado.']);
    }

    if ($action === 'activar') {
        $db->execute("UPDATE usuarios SET estado = 'activo', intentos_fallidos = 0 WHERE id = ?", [$idUsuario]);
        responder_json(['ok' => true, 'mensaje' => 'Usuario activado.']);
    }

    if ($action === 'permisos') {
        $menuIds = ids_recibidos($datos['menus'] ?? []);
        $pdo->beginTransaction();
        try {
            guardar_permisos_usuario($db, $idUsuario, $menuIds);
            $pdo->commit();
            responder_json(['ok' => true, 'mensaje' => 'Permisos actualizados.']);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    responder_json(['ok' => false, 'mensaje' => 'Acción no reconocida.'], 400);
} catch (Throwable $e) {
    responder_json(['ok' => false, 'mensaje' => 'No fue posible guardar los cambios.'], 500);
}
