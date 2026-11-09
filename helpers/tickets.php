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
        "SELECT DISTINCT p.nombre_perfil
           FROM perfiles p
           JOIN usuario_perfil up ON up.id_perfil = p.id_perfil
          WHERE up.id_usuario = ?
            AND p.estado = 1",
        [$usuarioId]
    );

    foreach ($filas as $fila) {
        if (ticket_normalizar_perfil((string) ($fila['nombre_perfil'] ?? '')) === $buscado) {
            return true;
        }
    }
    return false;
}

function es_administrador_global(int $usuarioId, Conexion $db): bool
{
    $usuario = $db->fetchOne('SELECT es_admin_global FROM usuarios WHERE id = ? LIMIT 1', [$usuarioId]);
    if ((int) ($usuario['es_admin_global'] ?? 0) === 1) {
        return true;
    }

    foreach (['super_admin', 'administrador', 'admin'] as $perfil) {
        if (tiene_perfil($usuarioId, $perfil, $db)) {
            return true;
        }
    }
    return false;
}

function es_admin_colegio(int $usuarioId, ?int $colegioId, Conexion $db): bool
{
    $sql = "SELECT 1
              FROM jefatura_departamento j
              JOIN usuario_perfil up ON up.id_usuario = j.id_usuario
              JOIN perfiles p ON p.id_perfil = up.id_perfil AND p.estado = 1
             WHERE j.id_usuario = ?
               AND j.estado = 1
               AND LOWER(REPLACE(p.nombre_perfil, ' ', '_')) IN ('admin_colegio', 'admin_area', 'administrador_colegio')";
    $params = [$usuarioId];
    if ($colegioId !== null && $colegioId > 0) {
        $sql .= ' AND j.id_colegio = ?';
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
        1 => ['codigo' => 'nuevo', 'nombre' => 'Recibido'],
        2 => ['codigo' => 'asignado', 'nombre' => 'Asignado'],
        3 => ['codigo' => 'en_proceso', 'nombre' => 'En proceso'],
        4 => ['codigo' => 'borrador', 'nombre' => 'Borrador'],
        5 => ['codigo' => 'resuelto', 'nombre' => 'Terminado'],
        6 => ['codigo' => 'cerrado', 'nombre' => 'Cerrado'],
        7 => ['codigo' => 'atrasado', 'nombre' => 'Demorado'],
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
        'nuevo' => 1, 'recibido' => 1, 'asignado' => 2, 'en_proceso' => 3, 'borrador' => 4,
        'terminado' => 5, 'resuelto' => 5, 'cerrado' => 6,
        'demorado' => 7, 'atrasado' => 7,
    ];
    return $alias[$codigo] ?? 0;
}

function ticket_estado_sin_escritura(int $estadoId): bool
{
    return in_array($estadoId, [5, 6], true);
}

/** Retorna el grupo usado por los contenedores de resumen. */
function ticket_estado_grupo(int $estadoId): string
{
    return match ($estadoId) {
        1, 2 => 'nuevo',
        3 => 'en_proceso',
        5, 6 => 'resuelto',
        7 => 'atrasado',
        default => 'borrador',
    };
}

/** Valida un filtro proveniente de los contenedores de resumen. */
function ticket_estado_grupo_filtro(mixed $estado): string
{
    $estado = ticket_normalizar_perfil((string) $estado);
    return in_array($estado, ['nuevo', 'en_proceso', 'resuelto', 'atrasado'], true) ? $estado : '';
}

/** Retorna la etiqueta visible de un grupo de estados. */
function ticket_estado_grupo_nombre(string $estado): string
{
    return [
        'nuevo' => 'Nuevos',
        'en_proceso' => 'En proceso',
        'resuelto' => 'Resueltos',
        'atrasado' => 'Atrasados',
    ][$estado] ?? '';
}

/**
 * Prioridades = tabla `prioridad` (1 Alta, 2 Media, 3 Baja). 0 / NULL = sin definir:
 * el ticket nace sin prioridad y la define el técnico al comenzarlo
 * (ticket/logica/LOGICA_COMENZAR_TICKET.txt).
 */
function ticket_prioridades(): array
{
    return [1 => 'Alta', 2 => 'Media', 3 => 'Baja'];
}

function ticket_prioridad_nombre(int $id): string
{
    return ticket_prioridades()[$id] ?? 'Sin definir';
}

/** Clase CSS de la prioridad: alta | media | baja | sin-definir */
function ticket_prioridad_clase(string $nombre): string
{
    return trim((string) preg_replace('/[^a-z]+/', '-', strtolower(strtr($nombre, ['í' => 'i', 'Í' => 'i']))), '-');
}

/** Convierte una fila de la BD histórica al contrato usado por las vistas actuales. */
function ticket_normalizar_fila(array $fila): array
{
    $idEstado = (int) ($fila['id_estado'] ?? 0);
    $estado = ticket_estados_legacy()[$idEstado] ?? ['codigo' => 'borrador', 'nombre' => 'Sin estado'];
    $fila['estado'] = $estado['codigo'];
    $fila['estado_nombre'] = trim((string) ($fila['estado_nombre'] ?? '')) ?: $estado['nombre'];
    $fila['estado_color'] = (string) ($fila['estado_color'] ?? $fila['colorEstado'] ?? '');
    $fila['estado_degradado'] = (string) ($fila['estado_degradado'] ?? $fila['colorDegradado'] ?? '');
    $fila['cantidad_archivos'] = max(0, (int) ($fila['cantidad_archivos'] ?? $fila['cantidadArchivos'] ?? 0));
    $fila['prioridad'] = ticket_prioridad_nombre((int) ($fila['id_prioridad'] ?? 0));
    $fila['descripcion'] = (string) ($fila['descripcion'] ?? $fila['descripcion_ticket'] ?? '');
    $fila['descripcion_texto'] = ticket_descripcion_texto($fila['descripcion']);
    $fila['id_tecnico_asignado'] = $fila['id_tecnico'] ?? null;
    $fila['fecha_creacion'] = (string) ($fila['fecha_creacion'] ?? '');
    $fila['fecha_respuesta'] = (string) ($fila['fecha_respuesta'] ?? '');
    return $fila;
}

/** Convierte un hexadecimal de la BD a una terna RGB segura para una variable CSS. */
function ticket_color_estado_rgb(string $color): string
{
    $color = ltrim(trim($color), '#');
    if (preg_match('/^[0-9a-f]{3}$/i', $color)) {
        $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
    }
    if (!preg_match('/^[0-9a-f]{6}$/i', $color)) {
        return '';
    }
    return hexdec(substr($color, 0, 2)) . ','
        . hexdec(substr($color, 2, 2)) . ','
        . hexdec(substr($color, 4, 2));
}

/** Genera el badge con el nombre y color configurados en estados_ticket. */
function ticket_badge_estado(array $ticket): string
{
    $idEstado = (int) ($ticket['id_estado'] ?? 0);
    $estadoBase = ticket_estados_legacy()[$idEstado] ?? ['codigo' => 'borrador', 'nombre' => 'Sin estado'];
    $nombre = trim((string) ($ticket['estado_nombre'] ?? '')) ?: $estadoBase['nombre'];
    $rgb = ticket_color_estado_rgb((string) ($ticket['estado_color'] ?? ''));
    $iconos = [
        1 => 'bi-inbox',
        2 => 'bi-person-check',
        3 => 'bi-hourglass-split',
        4 => 'bi-file-earmark',
        5 => 'bi-check-circle',
        6 => 'bi-lock',
        7 => 'bi-exclamation-triangle',
    ];
    $estilo = $rgb !== '' ? ' style="--ticket-state-rgb:' . $rgb . '"' : '';

    return '<span class="ticket-badge ticket-badge--database"' . $estilo . '>'
        . '<i class="bi ' . ($iconos[$idEstado] ?? 'bi-circle') . '" aria-hidden="true"></i>'
        . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</span>';
}

/**
 * Botón "Chat" de las bandejas con el número de mensajes no leídos.
 * ticket/js/chat_ticket_panel.js actualiza ese número cada pocos segundos.
 */
function ticket_boton_chat(int $ticketId, int $noLeidos = 0): string
{
    $n = max(0, $noLeidos);
    $titulo = $n === 0 ? 'Abrir chat' : ($n === 1 ? '1 mensaje sin leer' : $n . ' mensajes sin leer');
    return '<button class="btn btn-outline btn-sm ticket-chat-button js-ticket-chat' . ($n ? ' has-unread' : '') . '" type="button"'
        . ' data-ticket-id="' . $ticketId . '" title="' . $titulo . '" aria-label="Chat: ' . $titulo . '">'
        . '<i class="bi bi-chat-dots" aria-hidden="true"></i> Chat'
        . '<span class="ticket-chat-unread"' . ($n ? '' : ' hidden') . '>' . ($n > 99 ? '99+' : $n) . '</span></button>';
}

/**
 * Botón "Abrir" (detalle) con el número de entradas nuevas en la bitácora
 * interna (técnico ↔ administración). ticket/js/chat_ticket_panel.js lo actualiza.
 */
function ticket_boton_abrir(int $ticketId, int $bitacoraNoLeidos = 0, string $vista = 'tecnico'): string
{
    $n = max(0, $bitacoraNoLeidos);
    $titulo = $n === 0 ? 'Abrir el detalle' : ($n === 1 ? '1 avance nuevo en la bitácora' : $n . ' avances nuevos en la bitácora');
    return '<a class="btn btn-outline btn-sm ticket-open-button js-ticket-open js-ticket-ver' . ($n ? ' has-unread' : '') . '" href="ticket_detalle.php?id=' . $ticketId . '&amp;vista=' . rawurlencode($vista) . ($n ? '#bitacora' : '') . '"'
        . ' data-ticket-id="' . $ticketId . '" title="' . $titulo . '"><i class="bi bi-eye" aria-hidden="true"></i> Abrir'
        . '<span class="ticket-bitacora-unread"' . ($n ? '' : ' hidden') . '><i class="bi bi-journal-text" aria-hidden="true"></i>' . ($n > 99 ? '99+' : $n) . '</span></a>';
}

/** Genera el botón de adjuntos solamente cuando el ticket tiene archivos. */
function ticket_boton_archivos(int $ticketId, int $cantidad): string
{
    if ($ticketId <= 0 || $cantidad <= 0) {
        return '';
    }

    $etiqueta = $cantidad === 1 ? 'Ver 1 archivo adjunto' : 'Ver ' . $cantidad . ' archivos adjuntos';
    return '<button class="btn btn-outline btn-sm ticket-files-button js-ticket-files" type="button"'
        . ' data-ticket-id="' . $ticketId . '" title="' . htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') . '"'
        . ' aria-label="' . htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') . '">'
        . '<i class="bi bi-paperclip" aria-hidden="true"></i>'
        . '<span class="ticket-files-count">' . $cantidad . '</span></button>';
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

/** Comprueba si el actor puede crear un ticket a nombre del solicitante indicado. */
function puede_crear_ticket_para(int $actorId, int $solicitanteId, Conexion $db): bool
{
    if ($actorId <= 0 || $solicitanteId <= 0) {
        return false;
    }
    if ($actorId === $solicitanteId || es_administrador_global($actorId, $db)) {
        return true;
    }

    return (bool) $db->fetchOne(
        "SELECT 1
           FROM jefatura_departamento administrador
           JOIN jefatura_departamento solicitante
             ON solicitante.id_colegio = administrador.id_colegio
            AND solicitante.id_usuario = ?
            AND solicitante.estado = 1
           JOIN usuario_perfil up ON up.id_usuario = administrador.id_usuario
           JOIN perfiles p ON p.id_perfil = up.id_perfil AND p.estado = 1
          WHERE administrador.id_usuario = ?
            AND administrador.estado = 1
            AND LOWER(REPLACE(p.nombre_perfil, ' ', '_')) IN ('admin_colegio','admin_area','administrador_colegio')
          LIMIT 1",
        [$solicitanteId, $actorId]
    );
}

/** Devuelve texto legible desde una descripción antigua o con formato controlado. */
function ticket_descripcion_texto(string $descripcion): string
{
    $texto = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $descripcion);
    $texto = preg_replace('/<\s*\/\s*(p|li)\s*>/i', "\n", (string) $texto);
    return trim(html_entity_decode(strip_tags((string) $texto), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

/** Conserva solo el formato permitido por el editor del módulo. */
function ticket_sanitizar_descripcion(string $descripcion): string
{
    $descripcion = trim($descripcion);
    if ($descripcion === '') {
        return '';
    }
    if (!class_exists('DOMDocument')) {
        return ticket_descripcion_texto($descripcion);
    }

    $permitidas = ['p', 'h3', 'br', 'strong', 'b', 'em', 'i', 'u', 'ol', 'ul', 'li', 'a'];
    $dom = new DOMDocument('1.0', 'UTF-8');
    $estadoErrores = libxml_use_internal_errors(true);
    $dom->loadHTML(
        '<?xml encoding="utf-8" ?><div id="ticket-description-root">' . $descripcion . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($estadoErrores);
    $root = $dom->getElementById('ticket-description-root');
    if (!$root) {
        return ticket_descripcion_texto($descripcion);
    }

    $limpiar = static function (DOMNode $nodo) use (&$limpiar, $permitidas): void {
        foreach (iterator_to_array($nodo->childNodes) as $hijo) {
            if ($hijo instanceof DOMElement) {
                $etiqueta = strtolower($hijo->tagName);
                if (!in_array($etiqueta, $permitidas, true)) {
                    while ($hijo->firstChild) {
                        $nodo->insertBefore($hijo->firstChild, $hijo);
                    }
                    $nodo->removeChild($hijo);
                    // Reiniciar este nivel para sanear también los nodos promovidos.
                    $limpiar($nodo);
                    return;
                }
                foreach (iterator_to_array($hijo->attributes) as $atributo) {
                    $nombre = strtolower($atributo->name);
                    if ($etiqueta !== 'a' || !in_array($nombre, ['href', 'title'], true)) {
                        $hijo->removeAttribute($atributo->name);
                    }
                }
                if ($etiqueta === 'a') {
                    $href = trim($hijo->getAttribute('href'));
                    if (!preg_match('#^(https?://|mailto:)#i', $href)) {
                        $hijo->removeAttribute('href');
                    } else {
                        $hijo->setAttribute('target', '_blank');
                        $hijo->setAttribute('rel', 'noopener noreferrer');
                    }
                }
            }
            $limpiar($hijo);
        }
    };
    $limpiar($root);

    $salida = '';
    foreach ($root->childNodes as $hijo) {
        $salida .= $dom->saveHTML($hijo);
    }
    return trim($salida);
}

/** Renderiza texto histórico y contenido enriquecido ya saneado. */
function ticket_renderizar_descripcion(string $descripcion): string
{
    if (preg_match('/<\/?(?:p|h3|br|strong|b|em|i|u|ol|ul|li|a)\b/i', $descripcion)) {
        return ticket_sanitizar_descripcion($descripcion);
    }
    return nl2br(htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8'));
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
                      FROM jefatura_departamento
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

/**
 * Registra un archivo en archivos_adjuntos_ticket con las columnas que existan:
 * la tabla original es (id, id_ticket, adjunto); ticket/sql/01_mensajeria.sql
 * agrega nombre_archivo, tamano, id_usuario y fecha_subida.
 */
function ticket_registrar_adjunto(Conexion $db, int $ticketId, string $ruta, string $nombre, int $tamano, int $usuarioId): void
{
    $columnas = ticket_columnas_tabla('archivos_adjuntos_ticket', $db);
    $datos = ['id_ticket' => $ticketId];
    if (isset($columnas['adjunto'])) {
        $datos['adjunto'] = $ruta;
    }
    if (isset($columnas['ruta_archivo'])) {
        $datos['ruta_archivo'] = $ruta;
    }
    foreach (['nombre_archivo' => $nombre, 'tamano' => $tamano, 'tamaño_archivo' => $tamano, 'id_usuario' => $usuarioId] as $c => $v) {
        if (isset($columnas[$c])) {
            $datos[$c] = $v;
        }
    }
    $campos = array_map(static fn ($c) => '`' . $c . '`', array_keys($datos));
    $valores = array_fill(0, count($datos), '?');
    if (isset($columnas['fecha_subida'])) {
        $campos[] = 'fecha_subida';
        $valores[] = 'NOW()';
    }
    $db->execute(
        'INSERT INTO archivos_adjuntos_ticket (' . implode(', ', $campos) . ') VALUES (' . implode(', ', $valores) . ')',
        array_values($datos)
    );
}

/** Adjuntos de un ticket: [{id, nombre, tamano}] sin importar la versión de la tabla. */
function ticket_listar_adjuntos(Conexion $db, int $ticketId): array
{
    $c = ticket_columnas_tabla('archivos_adjuntos_ticket', $db);
    if (!isset($c['id_ticket'])) {
        return [];
    }
    $id = isset($c['id']) ? 'id' : (isset($c['id_archivo']) ? 'id_archivo' : null);
    $ruta = isset($c['adjunto']) ? 'adjunto' : (isset($c['ruta_archivo']) ? 'ruta_archivo' : null);
    if ($id === null || $ruta === null) {
        return [];
    }
    $nombre = isset($c['nombre_archivo']) ? "NULLIF(nombre_archivo, '')" : 'NULL';
    $tamano = isset($c['tamano']) ? 'tamano' : (isset($c['tamaño_archivo']) ? '`tamaño_archivo`' : '0');
    $filas = $db->fetchAll(
        "SELECT {$id} AS id, {$ruta} AS ruta, {$nombre} AS nombre, {$tamano} AS tamano
           FROM archivos_adjuntos_ticket WHERE id_ticket = ? ORDER BY {$id} ASC",
        [$ticketId]
    );
    return array_map(static fn (array $f): array => [
        'id' => (int) $f['id'],
        'nombre' => (string) ($f['nombre'] ?? '') !== '' ? (string) $f['nombre'] : basename(str_replace('\\', '/', (string) $f['ruta'])),
        'extension' => strtolower(pathinfo((string) $f['ruta'], PATHINFO_EXTENSION)),
        'tamano' => (int) $f['tamano'],
    ], $filas);
}

/**
 * Ruta física de un adjunto guardado en la base, solo si está dentro de las
 * carpetas de adjuntos del sistema (nuevo y antiguo). null si no existe.
 */
function ticket_ruta_adjunto_segura(string $ruta): ?string
{
    $raiz = realpath(dirname(__DIR__));
    $ruta = ltrim(str_replace('\\', '/', $ruta), '/');
    if ($raiz === false || $ruta === '' || str_contains($ruta, '..')) {
        return null;
    }
    $permitidas = ['uploads/ticket_conversacion/', 'uploads/tickets/', 'archivos/'];
    $ok = false;
    foreach ($permitidas as $p) {
        $ok = $ok || str_starts_with($ruta, $p);
    }
    $real = $ok ? realpath($raiz . '/' . $ruta) : false;
    if ($real === false || !is_file($real)) {
        return null;
    }
    foreach ($permitidas as $p) {
        $base = realpath($raiz . '/' . rtrim($p, '/'));
        if ($base !== false && str_starts_with($real, $base . DIRECTORY_SEPARATOR)) {
            return $real;
        }
    }
    return null;
}

/** Carpeta (relativa a la raíz) donde se guardan los adjuntos nuevos de tickets. */
const TICKET_DIR_ADJUNTOS = 'uploads/ticket_conversacion';
const TICKET_MAX_ADJUNTOS = 5;
const TICKET_MAX_BYTES = 5 * 1024 * 1024;

/**
 * Valida $_FILES['archivos'] (múltiple). Devuelve [original, tmp, tamano, extension]
 * o lanza RuntimeException con un mensaje para mostrar.
 */
function ticket_validar_archivos(array $files): array
{
    $permitidos = [
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif',
        'application/pdf' => 'pdf', 'text/plain' => 'txt',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/zip' => 'zip', 'application/x-zip-compressed' => 'zip',
    ];
    $nombres = $files['name'] ?? [];
    if (!is_array($nombres)) {
        $nombres = [$nombres];
        foreach (['tmp_name', 'size', 'error'] as $k) {
            $files[$k] = [$files[$k] ?? null];
        }
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $archivos = [];
    foreach ($nombres as $i => $original) {
        $error = (int) ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $tmp = (string) ($files['tmp_name'][$i] ?? '');
        $tamano = (int) ($files['size'][$i] ?? 0);
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE || $tamano > TICKET_MAX_BYTES) {
            throw new RuntimeException('Cada archivo puede pesar como máximo 5 MB.');
        }
        if ($error !== UPLOAD_ERR_OK || $tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Uno de los archivos no pudo subirse.');
        }
        $mime = (string) $finfo->file($tmp);
        if ($tamano <= 0 || !isset($permitidos[$mime])) {
            throw new RuntimeException('Tipo de archivo no permitido: ' . basename((string) $original) . '. Usa imágenes, PDF, Word, Excel, TXT o ZIP.');
        }
        $limpio = trim((string) preg_replace('/[\x00-\x1F\x7F\/\\\\]+/u', '', basename((string) $original)));
        $archivos[] = [
            'original' => mb_substr($limpio !== '' ? $limpio : 'archivo.' . $permitidos[$mime], 0, 200),
            'tmp' => $tmp,
            'tamano' => $tamano,
            'extension' => $permitidos[$mime],
        ];
    }
    return $archivos;
}

/** Mueve un archivo validado a uploads/ticket_conversacion/{id}/ y devuelve la ruta relativa. */
function ticket_mover_archivo(int $ticketId, array $archivo): string
{
    $relDir = TICKET_DIR_ADJUNTOS . '/' . $ticketId;
    $dir = dirname(__DIR__) . '/' . $relDir;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('No fue posible preparar la carpeta de adjuntos.');
    }
    $nombre = bin2hex(random_bytes(16)) . '.' . $archivo['extension'];
    if (!move_uploaded_file($archivo['tmp'], $dir . '/' . $nombre)) {
        throw new RuntimeException('No fue posible guardar un archivo adjunto.');
    }
    return $relDir . '/' . $nombre;
}

/**
 * Código de seguimiento público del ticket (tickets.identificador, 16 caracteres
 * aleatorios). Si el ticket no tiene, se genera. Lo usa el QR del detalle.
 * Lógica: ticket/logica/LOGICA_SEGUIMIENTO_QR.txt
 */
function ticket_codigo_seguimiento(Conexion $db, int $ticketId): string
{
    $fila = $db->fetchOne('SELECT identificador FROM tickets WHERE id_ticket = ?', [$ticketId]);
    $codigo = (string) ($fila['identificador'] ?? '');
    if (preg_match('/^[A-Za-z0-9]{12,32}$/', $codigo)) {
        return $codigo;
    }
    $abc = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    do {
        $codigo = '';
        for ($i = 0; $i < 16; $i++) {
            $codigo .= $abc[random_int(0, strlen($abc) - 1)];
        }
    } while ($db->fetchOne('SELECT 1 FROM tickets WHERE identificador = ?', [$codigo]));
    $db->execute('UPDATE tickets SET identificador = ? WHERE id_ticket = ?', [$codigo, $ticketId]);
    return $codigo;
}

/** Enlace público de seguimiento (lo que abre el QR en el celular). */
function ticket_enlace_seguimiento(string $codigo): string
{
    if (!defined('BASE_URL') && is_file(dirname(__DIR__) . '/config.php')) {
        require_once dirname(__DIR__) . '/config.php';
    }
    if (defined('BASE_URL')) {
        $base = rtrim((string) BASE_URL, '/') . '/';
    } else {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $host = preg_replace('/[^\w.\-:]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $base = ($https ? 'https' : 'http') . '://' . $host . '/';
    }
    return $base . 'ticket/seguimiento.php?c=' . rawurlencode($codigo);
}

/**
 * Avance visual del ticket (como la barra "2 de 4 completados" del sistema antiguo):
 * 4 pasos Recibido → Asignado → En proceso → Terminado + qué falta o para cuándo está.
 * Usa id_estado, fecha_estimada_admin y fecha_termino_ticket.
 * Lógica: ticket/logica/LOGICA_COMENZAR_TICKET.txt
 */
function ticket_avance(array $t): string
{
    $estado = (int) ($t['id_estado'] ?? 0);
    $pasos = match ($estado) { 1 => 1, 2 => 2, 3, 7 => 3, 5, 6 => 4, default => 0 };
    $fechaOk = static fn ($f): ?DateTimeImmutable => ($f && strtotime((string) $f) > 0 && !str_starts_with((string) $f, '0000'))
        ? new DateTimeImmutable(substr((string) $f, 0, 10)) : null;
    $estimada = $fechaOk($t['fecha_estimada_admin'] ?? null);
    $hoy = new DateTimeImmutable('today');
    $tono = 'normal';
    $icono = 'bi-hourglass-split';
    switch ($estado) {
        case 1:
            [$texto, $tono, $icono] = ['Sin técnico asignado', 'aviso', 'bi-person-dash'];
            break;
        case 2:
            [$texto, $tono, $icono] = ['Falta comenzar', 'aviso', 'bi-play-circle'];
            break;
        case 3:
        case 7:
            if (!$estimada) {
                [$texto, $icono] = ['En proceso · sin fecha estimada', 'bi-gear'];
                break;
            }
            $dias = (int) $hoy->diff($estimada)->format('%r%a');
            $fecha = $estimada->format('d/m');
            if ($dias < 0) {
                $texto = 'Atrasado ' . abs($dias) . ' día' . (abs($dias) === 1 ? '' : 's') . ' · era el ' . $fecha;
                [$tono, $icono] = ['atraso', 'bi-exclamation-triangle-fill'];
            } elseif ($dias === 0) {
                [$texto, $tono, $icono] = ['Listo para hoy (' . $fecha . ')', 'hoy', 'bi-calendar-check'];
            } else {
                $texto = 'Listo el ' . $fecha . ' · en ' . $dias . ' día' . ($dias === 1 ? '' : 's');
                $icono = 'bi-calendar-event';
            }
            break;
        case 5:
        case 6:
            $termino = $fechaOk($t['fecha_termino_ticket'] ?? null);
            [$texto, $tono, $icono] = ['Terminado' . ($termino ? ' el ' . $termino->format('d/m') : ''), 'ok', 'bi-check-circle-fill'];
            break;
        default:
            [$texto, $icono] = ['Borrador', 'bi-pencil'];
    }
    $html = '<div class="tk-av tk-av--' . $tono . '" title="' . $pasos . ' de 4 etapas">'
        . '<div class="tk-av__pasos" aria-hidden="true">';
    for ($i = 1; $i <= 4; $i++) {
        $html .= '<span' . ($i <= $pasos ? ' class="is-on"' : '') . '></span>';
    }
    return $html . '</div><small><strong>' . $pasos . '/4</strong> <i class="bi ' . $icono . '" aria-hidden="true"></i> '
        . htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') . '</small></div>';
}
