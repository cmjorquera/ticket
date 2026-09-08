<?php
declare(strict_types=1);

/** Utilidades de autorización y seguridad del módulo de tickets. */

function ticket_normalizar_perfil(string $perfil): string
{
    $perfil = function_exists('mb_strtolower') ? mb_strtolower(trim($perfil), 'UTF-8') : strtolower(trim($perfil));
    $ascii = function_exists('iconv') ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $perfil) : false;
    return trim((string) preg_replace('/[^a-z0-9]+/', '_', $ascii !== false ? $ascii : $perfil), '_');
}

function tiene_perfil(int $usuarioId, string $perfil, Conexion $db): bool
{
    $buscado = ticket_normalizar_perfil($perfil);
    $filas = $db->fetchAll(
        "SELECT DISTINCT p.nombre
           FROM perfiles p
           JOIN usuario_perfil up ON up.id_perfil = p.id_perfil
          WHERE up.id_usuario = ?
          UNION
         SELECT DISTINCT p.nombre
           FROM perfiles p
           JOIN usuario_colegio uc ON uc.id_perfil = p.id_perfil AND uc.estado = 1
          WHERE uc.id_usuario = ?",
        [$usuarioId, $usuarioId]
    );

    foreach ($filas as $fila) {
        if (ticket_normalizar_perfil((string) ($fila['nombre'] ?? '')) === $buscado) {
            return true;
        }
    }
    return false;
}

function es_administrador_global(int $usuarioId, Conexion $db): bool
{
    foreach (['administrador', 'admin'] as $perfil) {
        if (tiene_perfil($usuarioId, $perfil, $db)) {
            return true;
        }
    }
    return false;
}

function es_admin_colegio(int $usuarioId, ?int $colegioId, Conexion $db): bool
{
    $sql = "SELECT 1
              FROM usuario_colegio uc
         LEFT JOIN perfiles p ON p.id_perfil = uc.id_perfil
             WHERE uc.id_usuario = ?
               AND uc.estado = 1
               AND (uc.es_admin_colegio = 1 OR LOWER(p.nombre) IN ('admin colegio', 'admin_colegio', 'administrador colegio'))";
    $params = [$usuarioId];
    if ($colegioId !== null && $colegioId > 0) {
        $sql .= ' AND uc.id_colegio = ?';
        $params[] = $colegioId;
    }
    return (bool) $db->fetchOne($sql . ' LIMIT 1', $params);
}

function puede_administrar_ticket(int $usuarioId, int $colegioId, Conexion $db): bool
{
    return es_administrador_global($usuarioId, $db) || es_admin_colegio($usuarioId, $colegioId, $db);
}

/** @return array<int,array{codigo:string,nombre:string}> */
function ticket_estados_legacy(): array
{
    return [
        1 => ['codigo' => 'nuevo', 'nombre' => 'Nuevo'],
        2 => ['codigo' => 'asignado', 'nombre' => 'Asignado'],
        3 => ['codigo' => 'en_proceso', 'nombre' => 'En proceso'],
        4 => ['codigo' => 'borrador', 'nombre' => 'Borrador'],
        5 => ['codigo' => 'resuelto', 'nombre' => 'Terminado'],
        6 => ['codigo' => 'atrasado', 'nombre' => 'Demorado'],
    ];
}

function ticket_estado_id(mixed $estado): int
{
    if (is_numeric($estado)) {
        $id = (int) $estado;
        return isset(ticket_estados_legacy()[$id]) ? $id : 0;
    }
    $codigo = ticket_normalizar_perfil((string) $estado);
    $alias = [
        'nuevo' => 1, 'asignado' => 2, 'en_proceso' => 3, 'borrador' => 4,
        'terminado' => 5, 'resuelto' => 5, 'cerrado' => 5,
        'demorado' => 6, 'atrasado' => 6,
    ];
    return $alias[$codigo] ?? 0;
}

function ticket_prioridad_nombre(int $id): string
{
    return [1 => 'Baja', 2 => 'Media', 3 => 'Alta', 4 => 'Crítica'][$id] ?? 'Media';
}

/** Convierte una fila de la BD histórica al contrato usado por las vistas actuales. */
function ticket_normalizar_fila(array $fila): array
{
    $idEstado = (int) ($fila['id_estado'] ?? 0);
    $estado = ticket_estados_legacy()[$idEstado] ?? ['codigo' => 'borrador', 'nombre' => 'Sin estado'];
    $fila['estado'] = $estado['codigo'];
    $fila['estado_nombre'] = trim((string) ($fila['estado_nombre'] ?? '')) ?: $estado['nombre'];
    $fila['prioridad'] = ticket_prioridad_nombre((int) ($fila['id_prioridad'] ?? 2));
    $fila['descripcion'] = (string) ($fila['descripcion'] ?? $fila['descripcion_ticket'] ?? '');
    $fila['id_tecnico_asignado'] = $fila['id_tecnico'] ?? null;
    $fila['fecha_creacion'] = (string) ($fila['fecha_creacion'] ?? '');
    $fila['fecha_respuesta'] = (string) ($fila['fecha_respuesta'] ?? '');
    return $fila;
}

/** Diagnóstico temporal del módulo. Quitar cuando termine la revisión en producción. */
function ticket_debug_sql(string $contexto, string $sql, array $params = [], ?Throwable $error = null): void
{
    error_log('========== DEBUG SQL ' . $contexto . ' ==========');
    error_log($sql);
    error_log('PARAMS: ' . json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($error !== null) {
        error_log('ERROR: ' . $error->getMessage());
    }
    error_log('===============================================');
}

/** Comprueba si el usuario puede consultar o participar en un ticket. */
function puede_ver_ticket(int $usuarioId, array $ticket, Conexion $db): bool
{
    return (int) ($ticket['id_usuario'] ?? 0) === $usuarioId
        || (int) ($ticket['id_tecnico_asignado'] ?? 0) === $usuarioId
        || puede_administrar_ticket($usuarioId, (int) ($ticket['id_colegio'] ?? 0), $db);
}

/** Obtiene los datos mínimos y la asociación de colegio necesarios para autorizar un ticket. */
function ticket_buscar_para_acceso(int $ticketId, Conexion $db): array|false
{
    if ($ticketId <= 0) {
        return false;
    }

    return $db->fetchOne(
        "SELECT t.id_ticket, t.id_usuario, t.id_tecnico AS id_tecnico_asignado,
                t.id_estado, uc_ticket.id_colegio, t.asunto
           FROM tickets t
      LEFT JOIN (
                    SELECT id_usuario, MIN(id_colegio) AS id_colegio
                      FROM usuario_colegio
                     WHERE estado = 1
                  GROUP BY id_usuario
                ) uc_ticket ON uc_ticket.id_usuario = t.id_usuario
          WHERE t.id_ticket = ? AND t.estado = 1
          LIMIT 1",
        [$ticketId]
    );
}

/**
 * Devuelve metadatos de columnas para integrar tablas opcionales sin romper el módulo.
 *
 * @return array<string,array{key:string,extra:string}>
 */
function ticket_columnas_tabla(string $tabla, Conexion $db): array
{
    $permitidas = ['tickets', 'proceso_tickets', 'comentarios_ticket', 'calificacion_ticket', 'calificacion_tickett', 'log_cambios_ticket', 'ticket_adjuntos', 'archivos_adjuntos_ticket'];
    if (!in_array($tabla, $permitidas, true)) {
        return [];
    }

    try {
        $filas = $db->fetchAll(
            'SELECT COLUMN_NAME, COLUMN_KEY, EXTRA
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            [$tabla]
        );
    } catch (Throwable $ex) {
        error_log('No fue posible inspeccionar la tabla opcional ' . $tabla . ': ' . $ex->getMessage());
        return [];
    }

    $columnas = [];
    foreach ($filas as $fila) {
        $nombre = (string) ($fila['COLUMN_NAME'] ?? '');
        if ($nombre !== '') {
            $columnas[$nombre] = [
                'key' => (string) ($fila['COLUMN_KEY'] ?? ''),
                'extra' => strtolower((string) ($fila['EXTRA'] ?? '')),
            ];
        }
    }
    return $columnas;
}

/** Registra un evento si el historial está instalado; nunca interrumpe la acción principal. */
function ticket_registrar_cambio(
    Conexion $db,
    int $ticketId,
    int $usuarioId,
    string $accion,
    ?string $campo = null,
    ?string $anterior = null,
    ?string $nuevo = null
): void {
    try {
        $columnas = ticket_columnas_tabla('log_cambios_ticket', $db);
        if (!isset($columnas['id_ticket'], $columnas['id_usuario'], $columnas['accion'])) {
            return;
        }
        $campos = ['id_ticket', 'id_usuario', 'accion'];
        $valores = ['?', '?', '?'];
        $params = [$ticketId, $usuarioId, $accion];
        foreach (['campo_modificado' => $campo, 'valor_anterior' => $anterior, 'valor_nuevo' => $nuevo] as $nombre => $valor) {
            if (isset($columnas[$nombre])) {
                $campos[] = $nombre;
                $valores[] = '?';
                $params[] = $valor;
            }
        }
        if (isset($columnas['fecha_cambio'])) {
            $campos[] = 'fecha_cambio';
            $valores[] = 'NOW()';
        }
        $db->execute(
            'INSERT INTO log_cambios_ticket (' . implode(', ', $campos) . ') VALUES (' . implode(', ', $valores) . ')',
            $params
        );
    } catch (Throwable $ex) {
        error_log('No fue posible registrar historial de ticket: ' . $ex->getMessage());
    }
}

function ticket_csrf_token(): string
{
    Session::iniciar();
    if (empty($_SESSION['ticket_csrf'])) {
        $_SESSION['ticket_csrf'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['ticket_csrf'];
}

function ticket_csrf_valido(string $token): bool
{
    Session::iniciar();
    return $token !== '' && isset($_SESSION['ticket_csrf'])
        && hash_equals((string) $_SESSION['ticket_csrf'], $token);
}
