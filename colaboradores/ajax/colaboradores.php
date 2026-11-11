<?php
declare(strict_types=1);

/**
 * Colaboradores
 *   GET                         → lista de colaboradores activos (directorio)
 *   POST accion=enviar_mensaje  → envía un mensaje a uno o varios colaboradores
 *
 * Los mensajes se guardan en mensajes_chat con el mismo formato que usaba
 * el sistema antiguo (un registro por destinatario, cada uno con su propia
 * conversación: se inserta con id_conversacion = 0 y luego se iguala a su id,
 * porque la columna es NOT NULL). Tabla: sql/mensajes_chat.sql. El remitente siempre es el usuario de la sesión.
 */

require_once __DIR__ . '/../../ajax/_bootstrap.php';
Sesion::requerir();

// true  = cada usuario ve a TODOS los colaboradores activos (directorio).
// false = se aplican las reglas de GestorAccesoModular (área / colegio / depto).
const COLABORADORES_VER_TODOS = true;
const COLABORADORES_MAX_DESTINATARIOS = 200;
const COLABORADORES_MAX_MENSAJE = 1000;

$usuarioId = (int) ($_SESSION['id'] ?? 0);
$db = Conexion::getInstance('sistema_panel_central');

function columna_existe(Conexion $db, string $tabla, string $columna): bool
{
    return (bool) $db->fetchOne(
        'SELECT 1 FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1',
        [$tabla, $columna]
    );
}

function tabla_existe(Conexion $db, string $tabla): bool
{
    return (bool) $db->fetchOne(
        'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1',
        [$tabla]
    );
}

/** IDs que el usuario actual puede ver, o null si puede ver a todos. */
function ids_visibles(Conexion $db, int $usuarioId): ?array
{
    if (COLABORADORES_VER_TODOS) {
        return null;
    }
    require_once __DIR__ . '/../../clases/GestorAccesoModular.php';
    $gestor = new GestorAccesoModular($db);
    return array_map(static fn (array $u): int => (int) $u['id'], $gestor->obtenerUsuariosAsociados($usuarioId));
}

try {
    // ----------------------------------------------------------------- LISTA
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
        $conAnexo = columna_existe($db, 'usuarios', 'anexo');
        $conUsuarioColegio = tabla_existe($db, 'usuario_colegio');

        $campoAnexo = $conAnexo ? 'u.anexo' : "''";
        $joinUc = $conUsuarioColegio
            ? 'LEFT JOIN usuario_colegio uc ON uc.id_usuario = u.id AND uc.estado = 1
               LEFT JOIN colegio c2 ON c2.id_colegio = uc.id_colegio'
            : '';
        $colegioId = $conUsuarioColegio ? 'COALESCE(MAX(jd.id_colegio), MAX(uc.id_colegio))' : 'MAX(jd.id_colegio)';
        $colegioNom = $conUsuarioColegio ? 'COALESCE(MAX(c1.nom_colegio), MAX(c2.nom_colegio))' : 'MAX(c1.nom_colegio)';

        $filas = $db->fetchAll(
            "SELECT u.id, u.nombre, u.apellido_paterno, u.apellido_materno, u.email,
                    u.cargo, u.telefono, {$campoAnexo} AS anexo,
                    u.id_area_trabajo, at.nombre_area,
                    {$colegioId} AS id_colegio, {$colegioNom} AS nom_colegio
               FROM usuarios u
          LEFT JOIN area_trabajo at ON at.id_area = u.id_area_trabajo
          LEFT JOIN jefatura_departamento jd ON jd.id_usuario = u.id AND jd.estado = 1
          LEFT JOIN colegio c1 ON c1.id_colegio = jd.id_colegio
               {$joinUc}
              WHERE u.id <> ? AND LOWER(u.estado) = 'activo'
           GROUP BY u.id, u.nombre, u.apellido_paterno, u.apellido_materno, u.email,
                    u.cargo, u.telefono, anexo, u.id_area_trabajo, at.nombre_area
           ORDER BY u.nombre ASC, u.apellido_paterno ASC",
            [$usuarioId]
        );

        $visibles = ids_visibles($db, $usuarioId);
        if ($visibles !== null) {
            $permitidos = array_flip($visibles);
            $filas = array_values(array_filter($filas, static fn (array $f): bool => isset($permitidos[(int) $f['id']])));
        }

        $datos = array_map(static fn (array $f): array => [
            'id'        => (int) $f['id'],
            'nombre'    => trim((string) $f['nombre'] . ' ' . (string) $f['apellido_paterno'] . ' ' . (string) $f['apellido_materno']),
            'cargo'     => trim((string) ($f['cargo'] ?? '')),
            'email'     => trim((string) ($f['email'] ?? '')),
            'anexo'     => trim((string) ($f['anexo'] ?? '')),
            'telefono'  => trim((string) ($f['telefono'] ?? '')),
            'id_area'   => (int) ($f['id_area_trabajo'] ?? 0),
            'area'      => trim((string) ($f['nombre_area'] ?? '')),
            'id_colegio'=> (int) ($f['id_colegio'] ?? 0),
            'colegio'   => trim((string) ($f['nom_colegio'] ?? '')),
        ], $filas);

        responder_json(['ok' => true, 'data' => $datos]);
    }

    // ----------------------------------------------------------- ENVIAR MENSAJE
    $csrf = (string) ($_POST['csrf'] ?? '');
    $csrfSesion = (string) ($_SESSION['csrf_colaboradores'] ?? '');
    if ($csrfSesion === '' || !hash_equals($csrfSesion, $csrf)) {
        responder_json(['ok' => false, 'mensaje' => 'La sesión expiró. Recarga la página.'], 419);
    }
    if (($_POST['accion'] ?? '') !== 'enviar_mensaje') {
        responder_json(['ok' => false, 'mensaje' => 'Acción no válida.'], 400);
    }

    $mensaje = trim((string) ($_POST['mensaje'] ?? ''));
    $urgente = ($_POST['urgente'] ?? '') === '1' ? 1 : 0;
    $destinos = array_values(array_unique(array_filter(
        array_map('intval', (array) ($_POST['destinatarios'] ?? [])),
        static fn (int $id): bool => $id > 0 && $id !== $usuarioId
    )));

    if ($mensaje === '') {
        responder_json(['ok' => false, 'mensaje' => 'Escribe un mensaje.'], 422);
    }
    if (mb_strlen($mensaje) > COLABORADORES_MAX_MENSAJE) {
        responder_json(['ok' => false, 'mensaje' => 'El mensaje supera los ' . COLABORADORES_MAX_MENSAJE . ' caracteres.'], 422);
    }
    if (!$destinos) {
        responder_json(['ok' => false, 'mensaje' => 'Selecciona al menos un destinatario.'], 422);
    }
    if (count($destinos) > COLABORADORES_MAX_DESTINATARIOS) {
        responder_json(['ok' => false, 'mensaje' => 'Máximo ' . COLABORADORES_MAX_DESTINATARIOS . ' destinatarios por envío.'], 422);
    }

    // Solo destinatarios activos y visibles para quien envía
    $marcas = implode(',', array_fill(0, count($destinos), '?'));
    $validos = array_map('intval', array_column($db->fetchAll(
        "SELECT id FROM usuarios WHERE id IN ($marcas) AND LOWER(estado) = 'activo'",
        $destinos
    ), 'id'));
    $visibles = ids_visibles($db, $usuarioId);
    if ($visibles !== null) {
        $validos = array_values(array_intersect($validos, $visibles));
    }
    if (count($validos) !== count($destinos)) {
        responder_json(['ok' => false, 'mensaje' => 'Uno o más destinatarios no están disponibles.'], 422);
    }

    if (!tabla_existe($db, 'mensajes_chat')) {
        responder_json(['ok' => false, 'mensaje' => 'La mensajería aún no está instalada en este servidor (falta la tabla mensajes_chat).'], 503);
    }

    $pdo = $db->getPDO();
    $pdo->beginTransaction();
    try {
        $fecha = (new DateTimeImmutable('now', new DateTimeZone('America/Santiago')))->format('Y-m-d H:i:s');
        foreach ($validos as $idDestino) {
            $db->execute(
                "INSERT INTO mensajes_chat
                    (id_conversacion, mensaje, de, para, fecha_hora, leido, urgente, eliminado, prioridad)
                 VALUES (0, ?, ?, ?, ?, 0, ?, 0, ?)",
                [$mensaje, $usuarioId, $idDestino, $fecha, $urgente, 'NORMAL']
            );
            $idMensaje = (int) $db->lastInsertId();
            $db->execute('UPDATE mensajes_chat SET id_conversacion = ? WHERE id = ?', [$idMensaje, $idMensaje]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    $n = count($validos);
    responder_json(['ok' => true, 'mensaje' => $n === 1 ? 'Mensaje enviado.' : "Mensaje enviado a {$n} colaboradores."]);

} catch (Throwable $e) {
    error_log('[colaboradores] ' . $e->getMessage());
    responder_json(['ok' => false, 'mensaje' => 'No fue posible completar la acción. Intenta nuevamente.'], 500);
}
