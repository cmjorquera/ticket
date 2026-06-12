<?php

class Inventario
{
    private $db;
    private $cn;
    private $cacheTablas = [];
    private $estadosFallback = [
        1 => ['nombre_estado' => 'Activo', 'color_badge' => 'success'],
        2 => ['nombre_estado' => 'Bodega', 'color_badge' => 'secondary'],
        3 => ['nombre_estado' => 'Reparacion', 'color_badge' => 'warning'],
        4 => ['nombre_estado' => 'Baja', 'color_badge' => 'danger'],
        5 => ['nombre_estado' => 'Prestado', 'color_badge' => 'info'],
    ];

    public function __construct()
    {
        $this->db = new MySQL('', '', '');
        $this->cn = $this->obtenerConexionSistema($this->db);
    }

    private function obtenerConexionSistema($db)
    {
        if (!class_exists('MySQL')) {
            throw new RuntimeException('No se encontro la clase MySQL del sistema en class/conexion.php.');
        }

        if (method_exists($db, 'getConexion')) {
            $conexion = $db->getConexion();
            if ($conexion instanceof mysqli) {
                return $conexion;
            }
        }

        if (property_exists($db, 'conexion')) {
            $ref = new ReflectionObject($db);
            if ($ref->hasProperty('conexion')) {
                $prop = $ref->getProperty('conexion');
                $prop->setAccessible(true);
                $conexion = $prop->getValue($db);
                if ($conexion instanceof mysqli) {
                    return $conexion;
                }
            }
        }

        throw new RuntimeException('La clase MySQL no expone la conexion activa.');
    }

    public function tablaExiste($tabla)
    {
        if (isset($this->cacheTablas[$tabla])) {
            return $this->cacheTablas[$tabla];
        }

        $tablaSegura = $this->db->escape_string($tabla);
        $sql = "SELECT 1
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
                  AND table_name = '{$tablaSegura}'
                LIMIT 1";
        $rs = $this->db->consulta($sql);
        $existe = $this->db->num_rows($rs) > 0;

        $this->cacheTablas[$tabla] = $existe;
        return $existe;
    }

    public function obtenerColegios()
    {
        $datos = [];
        $rs = $this->db->consulta("SELECT id_colegio, nom_colegio FROM colegio ORDER BY nom_colegio ASC");
        while ($fila = $this->db->fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function obtenerColegioDelUsuario(int $idUsuario): array
    {
        $stmt = mysqli_prepare(
            $this->cn,
            "SELECT uc.id_colegio, c.nom_colegio
             FROM usuario_colegio uc
             INNER JOIN colegio c ON c.id_colegio = uc.id_colegio
             WHERE uc.id_usuario = ? AND uc.estado = 1
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
        mysqli_stmt_close($stmt);
        return $fila;
    }

    public function obtenerColoresColegio(int $idUsuario): array
    {
        try {
            $stmt = mysqli_prepare(
                $this->cn,
                "SELECT c.color_principal, c.color_secundario, c.color_terciario, c.color_cuaternario
                 FROM usuario_colegio uc
                 INNER JOIN colegio c ON c.id_colegio = uc.id_colegio
                 WHERE uc.id_usuario = ? AND uc.estado = 1
                 LIMIT 1"
            );
            mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
            mysqli_stmt_execute($stmt);
            $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
            mysqli_stmt_close($stmt);
            return $fila;
        } catch (Throwable $e) {
            return [];
        }
    }

    public function obtenerUsuarios()
    {
        $datos = [];
        $sql = "SELECT id, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) AS nombre_completo
                FROM usuarios
                ORDER BY nombre ASC, apellido_paterno ASC";
        $rs = $this->db->consulta($sql);
        while ($fila = $this->db->fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function obtenerEstados()
    {
        if ($this->tablaExiste('estado_equipo')) {
            $datos = [];
            $rs = $this->db->consulta("SELECT id_estado, nombre_estado, color_badge FROM estado_equipo ORDER BY id_estado ASC");
            while ($fila = $this->db->fetch_assoc($rs)) {
                $datos[] = $fila;
            }
            return $datos;
        }

        $datos = [];
        foreach ($this->estadosFallback as $id => $estado) {
            $datos[] = ['id_estado' => $id] + $estado;
        }
        return $datos;
    }

    public function obtenerTiposPc()
    {
        if ($this->tablaExiste('tipo_pc_catalogo')) {
            $datos = [];
            $rs = $this->db->consulta("SELECT nombre_tipo FROM tipo_pc_catalogo WHERE activo = 1 ORDER BY nombre_tipo ASC");
            while ($fila = $this->db->fetch_assoc($rs)) {
                $datos[] = $fila['nombre_tipo'];
            }
            return $datos;
        }

        return ['Desktop', 'Notebook', 'All In One', 'Mini PC', 'Servidor', 'Otro'];
    }

    public function obtenerResumen($filtros = [])
    {
        $where = $this->construirWhere($filtros);
        $sql = "SELECT
                    COUNT(*) AS total_equipos,
                    SUM(CASE WHEN LOWER(e.tipo_pc) LIKE '%notebook%' THEN 1 ELSE 0 END) AS total_notebooks,
                    SUM(CASE WHEN LOWER(e.tipo_pc) LIKE '%desktop%' THEN 1 ELSE 0 END) AS total_desktop,
                    SUM(CASE WHEN e.id_usuario_asignado IS NULL OR e.id_usuario_asignado = 0 THEN 1 ELSE 0 END) AS total_sin_asignar,
                    SUM(CASE WHEN e.id_estado = 1 THEN 1 ELSE 0 END) AS total_activos,
                    SUM(CASE WHEN e.id_estado = 3 THEN 1 ELSE 0 END) AS total_reparacion,
                    SUM(CASE WHEN e.id_estado = 4 THEN 1 ELSE 0 END) AS total_baja
                FROM equipos e
                {$where['sql']}";
        $stmt = mysqli_prepare($this->cn, $sql);
        $this->bindParams($stmt, $where['types'], $where['params']);
        mysqli_stmt_execute($stmt);
        $resumen = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
        mysqli_stmt_close($stmt);

        $resumen['por_colegio'] = [];
        $sqlColegios = "SELECT c.nom_colegio, COUNT(*) AS total
                        FROM equipos e
                        INNER JOIN colegio c ON c.id_colegio = e.id_colegio
                        {$where['sql']}
                        GROUP BY e.id_colegio, c.nom_colegio
                        ORDER BY total DESC, c.nom_colegio ASC";
        $stmt = mysqli_prepare($this->cn, $sqlColegios);
        $this->bindParams($stmt, $where['types'], $where['params']);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($fila = mysqli_fetch_assoc($rs)) {
            $resumen['por_colegio'][] = $fila;
        }
        mysqli_stmt_close($stmt);

        return $resumen;
    }

    public function listarEquipos($filtros = [])
    {
        $where = $this->construirWhere($filtros);
        $joinEstado = $this->tablaExiste('estado_equipo') ? "LEFT JOIN estado_equipo ee ON ee.id_estado = e.id_estado" : '';
        $selectEstado = $this->tablaExiste('estado_equipo')
            ? "COALESCE(ee.nombre_estado, 'Sin estado') AS nombre_estado, COALESCE(ee.color_badge, 'dark') AS color_badge"
            : "CASE e.id_estado WHEN 1 THEN 'Activo' WHEN 2 THEN 'Bodega' WHEN 3 THEN 'Reparacion' WHEN 4 THEN 'Baja' WHEN 5 THEN 'Prestado' ELSE 'Sin estado' END AS nombre_estado,
               CASE e.id_estado WHEN 1 THEN 'success' WHEN 2 THEN 'secondary' WHEN 3 THEN 'warning' WHEN 4 THEN 'danger' WHEN 5 THEN 'info' ELSE 'dark' END AS color_badge";

        $sql = "SELECT
                    e.id_equipo,
                    e.nombre_equipo,
                    e.fabricante,
                    e.producto,
                    e.numero_serie,
                    e.tipo_pc,
                    e.qr_code,
                    e.id_estado,
                    c.nom_colegio,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS usuario_registra,
                    CONCAT(ua.nombre, ' ', ua.apellido_paterno, ' ', ua.apellido_materno) AS usuario_asignado,
                    {$selectEstado}
                FROM equipos e
                INNER JOIN colegio c ON c.id_colegio = e.id_colegio
                LEFT JOIN usuarios u ON u.id = e.id_usuario
                LEFT JOIN usuarios ua ON ua.id = e.id_usuario_asignado
                {$joinEstado}
                {$where['sql']}
                ORDER BY e.id_equipo DESC";
        $stmt = mysqli_prepare($this->cn, $sql);
        $this->bindParams($stmt, $where['types'], $where['params']);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);

        $datos = [];
        while ($fila = mysqli_fetch_assoc($rs)) {
            $fila['badge_estado'] = '<span class="badge text-bg-' . htmlspecialchars($fila['color_badge'], ENT_QUOTES, 'UTF-8') . '">' .
                htmlspecialchars($fila['nombre_estado'], ENT_QUOTES, 'UTF-8') . '</span>';
            $datos[] = $fila;
        }
        mysqli_stmt_close($stmt);
        return $datos;
    }

    public function obtenerEquipoCompleto($idEquipo)
    {
        $idEquipo = (int)$idEquipo;
        $joinEstado = $this->tablaExiste('estado_equipo') ? "LEFT JOIN estado_equipo ee ON ee.id_estado = e.id_estado" : '';
        $selectEstado = $this->tablaExiste('estado_equipo')
            ? "COALESCE(ee.nombre_estado, 'Sin estado') AS nombre_estado, COALESCE(ee.color_badge, 'dark') AS color_badge"
            : "CASE e.id_estado WHEN 1 THEN 'Activo' WHEN 2 THEN 'Bodega' WHEN 3 THEN 'Reparacion' WHEN 4 THEN 'Baja' WHEN 5 THEN 'Prestado' ELSE 'Sin estado' END AS nombre_estado,
               CASE e.id_estado WHEN 1 THEN 'success' WHEN 2 THEN 'secondary' WHEN 3 THEN 'warning' WHEN 4 THEN 'danger' WHEN 5 THEN 'info' ELSE 'dark' END AS color_badge";

        $sql = "SELECT
                    e.*,
                    c.nom_colegio,
                    CONCAT(ur.nombre, ' ', ur.apellido_paterno, ' ', ur.apellido_materno) AS usuario_registra,
                    CONCAT(ua.nombre, ' ', ua.apellido_paterno, ' ', ua.apellido_materno) AS usuario_asignado,
                    {$selectEstado}
                FROM equipos e
                INNER JOIN colegio c ON c.id_colegio = e.id_colegio
                LEFT JOIN usuarios ur ON ur.id = e.id_usuario
                LEFT JOIN usuarios ua ON ua.id = e.id_usuario_asignado
                {$joinEstado}
                WHERE e.id_equipo = ?";
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
        mysqli_stmt_execute($stmt);
        $equipo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$equipo) {
            return null;
        }

        $equipo['compra'] = $this->obtenerFilaSimple('SELECT * FROM equipos_compra WHERE id_equipo = ?', $idEquipo);
        $equipo['almacenamiento'] = $this->obtenerFilaSimple('SELECT * FROM equipo_almacenamiento WHERE id_equipo = ?', $idEquipo);
        $equipo['procesador'] = $this->obtenerFilaSimple('SELECT * FROM equipo_procesador WHERE id_equipo = ?', $idEquipo);
        $equipo['software'] = $this->obtenerFilaSimple('SELECT * FROM equipo_software WHERE id_equipo = ?', $idEquipo);
        $equipo['memorias'] = $this->obtenerVariasFilas('SELECT * FROM equipo_memoria WHERE id_equipo = ? ORDER BY orden_memoria ASC, id_memoria ASC', $idEquipo);
        $equipo['monitores'] = $this->obtenerVariasFilas('SELECT * FROM equipo_monitor WHERE id_equipo = ? ORDER BY orden_monitor ASC, id_monitor ASC', $idEquipo);
        $equipo['fotos'] = $this->tablaExiste('equipo_fotos')
            ? $this->obtenerVariasFilas('SELECT * FROM equipo_fotos WHERE id_equipo = ? AND tipo_foto <> "panoramica" ORDER BY principal DESC, orden_foto ASC, id_foto ASC', $idEquipo)
            : [];
        return $equipo;
    }

    public function validarSerieDuplicada($numeroSerie, $idEquipoExcluir = 0)
    {
        $numeroSerie = trim((string)$numeroSerie);
        if ($numeroSerie === '') {
            return false;
        }

        $sql = "SELECT id_equipo FROM equipos WHERE numero_serie = ?";
        if ($idEquipoExcluir > 0) {
            $sql .= " AND id_equipo <> ?";
        }

        $stmt = mysqli_prepare($this->cn, $sql);
        if ($idEquipoExcluir > 0) {
            mysqli_stmt_bind_param($stmt, 'si', $numeroSerie, $idEquipoExcluir);
        } else {
            mysqli_stmt_bind_param($stmt, 's', $numeroSerie);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $duplicado = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        return $duplicado;
    }

    public function cambiarEstadoLogico($idEquipo, $nuevoEstado, $idUsuario)
    {
        $stmt = mysqli_prepare($this->cn, "UPDATE equipos SET id_estado = ? WHERE id_equipo = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $nuevoEstado, $idEquipo);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $this->registrarHistorial((int)$idEquipo, 'estado', 'Cambio logico de estado del equipo.', (int)$idUsuario);
        return true;
    }

    public function renderBadgeEstado($idEstado, $nombreEstado = '', $colorBadge = '')
    {
        if ($nombreEstado === '' || $colorBadge === '') {
            $meta = $this->estadosFallback[(int)$idEstado] ?? ['nombre_estado' => 'Sin estado', 'color_badge' => 'dark'];
            $nombreEstado = $nombreEstado !== '' ? $nombreEstado : $meta['nombre_estado'];
            $colorBadge = $colorBadge !== '' ? $colorBadge : $meta['color_badge'];
        }

        return '<span class="badge text-bg-' . htmlspecialchars($colorBadge, ENT_QUOTES, 'UTF-8') . '">' .
            htmlspecialchars($nombreEstado, ENT_QUOTES, 'UTF-8') . '</span>';
    }

    public function obtenerSqlSugerido()
    {
        return <<<SQL
CREATE TABLE IF NOT EXISTS estado_equipo (
    id_estado INT PRIMARY KEY,
    nombre_estado VARCHAR(60) NOT NULL,
    color_badge VARCHAR(20) NOT NULL DEFAULT 'secondary'
);

INSERT INTO estado_equipo (id_estado, nombre_estado, color_badge) VALUES
(1, 'Activo', 'success'),
(2, 'Bodega', 'secondary'),
(3, 'Reparacion', 'warning'),
(4, 'Baja', 'danger'),
(5, 'Prestado', 'info')
ON DUPLICATE KEY UPDATE nombre_estado = VALUES(nombre_estado), color_badge = VALUES(color_badge);

CREATE TABLE IF NOT EXISTS tipo_pc_catalogo (
    id_tipo_pc INT AUTO_INCREMENT PRIMARY KEY,
    nombre_tipo VARCHAR(80) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS equipo_fotos (
    id_foto INT AUTO_INCREMENT PRIMARY KEY,
    id_equipo INT NOT NULL,
    ruta_foto VARCHAR(255) NOT NULL,
    tipo_foto ENUM('normal', 'panoramica') NOT NULL DEFAULT 'normal',
    principal TINYINT(1) NOT NULL DEFAULT 0,
    orden_foto INT NOT NULL DEFAULT 1,
    fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_equipo_fotos_equipo FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS equipo_historial (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_equipo INT NOT NULL,
    accion VARCHAR(60) NOT NULL,
    descripcion TEXT NOT NULL,
    id_usuario INT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_equipo_historial_equipo FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo) ON DELETE CASCADE
);
SQL;
    }

    private function construirWhere($filtros)
    {
        $condiciones = [];
        $types = '';
        $params = [];

        if (!empty($filtros['id_colegio'])) {
            $condiciones[] = 'e.id_colegio = ?';
            $types .= 'i';
            $params[] = (int)$filtros['id_colegio'];
        }
        if (!empty($filtros['id_estado'])) {
            $condiciones[] = 'e.id_estado = ?';
            $types .= 'i';
            $params[] = (int)$filtros['id_estado'];
        }
        if (!empty($filtros['tipo_pc'])) {
            $condiciones[] = 'e.tipo_pc = ?';
            $types .= 's';
            $params[] = trim((string)$filtros['tipo_pc']);
        }
        if (!empty($filtros['id_usuario_asignado'])) {
            $condiciones[] = 'e.id_usuario_asignado = ?';
            $types .= 'i';
            $params[] = (int)$filtros['id_usuario_asignado'];
        }
        if (!empty($filtros['busqueda'])) {
            $busqueda = '%' . trim((string)$filtros['busqueda']) . '%';
            $condiciones[] = '(e.nombre_equipo LIKE ? OR e.numero_serie LIKE ? OR e.producto LIKE ? OR e.qr_code LIKE ?)';
            $types .= 'ssss';
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
        }

        return [
            'sql' => count($condiciones) ? 'WHERE ' . implode(' AND ', $condiciones) : '',
            'types' => $types,
            'params' => $params,
        ];
    }

    private function bindParams($stmt, $types, $params)
    {
        if ($types === '' || empty($params)) {
            return;
        }

        $refs = [];
        foreach ($params as $k => $valor) {
            $refs[$k] = &$params[$k];
        }
        array_unshift($refs, $types);
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }

    private function obtenerFilaSimple($sql, $idEquipo)
    {
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
        mysqli_stmt_close($stmt);
        return $fila;
    }

    private function obtenerVariasFilas($sql, $idEquipo)
    {
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $filas = [];
        while ($fila = mysqli_fetch_assoc($rs)) {
            $filas[] = $fila;
        }
        mysqli_stmt_close($stmt);
        return $filas;
    }

    public function guardarEquipo($post, $files, $idUsuario)
    {
        $payload = $this->normalizarPayload($post, 0, $idUsuario);

        if ($this->validarSerieDuplicada($payload['equipo']['numero_serie'])) {
            throw new RuntimeException('El numero de serie ya existe en otro equipo.');
        }

        // El alta del equipo se confirma solo si todos los componentes y archivos quedan persistidos.
        mysqli_begin_transaction($this->cn);

        try {
            $idEquipo = $this->insertarRegistroEquipo($payload);
            $this->guardarTablasRelacionadas($idEquipo, $payload);
            $this->guardarFotos($idEquipo, $files);
            $this->registrarHistorial($idEquipo, 'creacion', 'Equipo registrado en inventario.', (int)$idUsuario);

            mysqli_commit($this->cn);
            return $idEquipo;
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    public function guardarEquipoDesdeArray(array $payload, int $idUsuario): int
    {
        if ($this->validarSerieDuplicada($payload['equipo']['numero_serie'])) {
            throw new RuntimeException('El numero de serie ya existe en otro equipo.');
        }

        mysqli_begin_transaction($this->cn);

        try {
            $idEquipo = $this->insertarRegistroEquipo($payload);
            $this->guardarTablasRelacionadas($idEquipo, $payload);
            $this->registrarHistorial($idEquipo, 'creacion', 'Equipo registrado via carga masiva.', $idUsuario);

            mysqli_commit($this->cn);
            return $idEquipo;
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    private function insertarRegistroEquipo(array $payload): int
    {
        $sql = "INSERT INTO equipos
                (id_usuario, id_colegio, id_usuario_asignado, nombre_equipo, fabricante, producto, numero_serie, tipo_pc, qr_code, id_estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            'iiissssssi',
            $payload['equipo']['id_usuario'],
            $payload['equipo']['id_colegio'],
            $payload['equipo']['id_usuario_asignado'],
            $payload['equipo']['nombre_equipo'],
            $payload['equipo']['fabricante'],
            $payload['equipo']['producto'],
            $payload['equipo']['numero_serie'],
            $payload['equipo']['tipo_pc'],
            $payload['equipo']['qr_code'],
            $payload['equipo']['id_estado']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return (int)mysqli_insert_id($this->cn);
    }

    public function actualizarEquipo($idEquipo, $post, $files, $idUsuario)
    {
        $idEquipo = (int)$idEquipo;
        $payload = $this->normalizarPayload($post, $idEquipo, $idUsuario);

        if ($this->validarSerieDuplicada($payload['equipo']['numero_serie'], $idEquipo)) {
            throw new RuntimeException('El numero de serie ya existe en otro equipo.');
        }

        // La actualizacion reutiliza el mismo contrato de guardado para mantener consistencia entre tablas hijas.
        mysqli_begin_transaction($this->cn);

        try {
            $sql = "UPDATE equipos SET
                        id_colegio = ?,
                        id_usuario_asignado = ?,
                        nombre_equipo = ?,
                        fabricante = ?,
                        producto = ?,
                        numero_serie = ?,
                        tipo_pc = ?,
                        qr_code = ?,
                        id_estado = ?
                    WHERE id_equipo = ?";
            $stmt = mysqli_prepare($this->cn, $sql);
            mysqli_stmt_bind_param(
                $stmt,
                'iissssssii',
                $payload['equipo']['id_colegio'],
                $payload['equipo']['id_usuario_asignado'],
                $payload['equipo']['nombre_equipo'],
                $payload['equipo']['fabricante'],
                $payload['equipo']['producto'],
                $payload['equipo']['numero_serie'],
                $payload['equipo']['tipo_pc'],
                $payload['equipo']['qr_code'],
                $payload['equipo']['id_estado'],
                $idEquipo
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $this->limpiarRelacionados($idEquipo);
            $this->guardarTablasRelacionadas($idEquipo, $payload);
            $this->procesarEliminacionFotos($idEquipo, $post);
            $this->guardarFotos($idEquipo, $files);
            $this->registrarHistorial($idEquipo, 'actualizacion', 'Equipo actualizado desde el modulo de inventario.', $idUsuario);

            mysqli_commit($this->cn);
            return true;
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    private function normalizarPayload($post, $idEquipo, $idUsuario)
    {
        $equipo = [
            'id_usuario' => (int)$idUsuario,
            'id_colegio' => (int)($post['id_colegio'] ?? 0),
            'id_usuario_asignado' => (int)($post['id_usuario_asignado'] ?? 0),
            'nombre_equipo' => trim((string)($post['nombre_equipo'] ?? '')),
            'fabricante' => trim((string)($post['fabricante'] ?? '')),
            'producto' => trim((string)($post['producto'] ?? '')),
            'numero_serie' => trim((string)($post['numero_serie'] ?? '')),
            'tipo_pc' => trim((string)($post['tipo_pc'] ?? '')),
            'qr_code' => trim((string)($post['qr_code'] ?? '')),
            'id_estado' => (int)($post['id_estado'] ?? 1),
        ];

        if ($equipo['id_colegio'] <= 0 || $equipo['nombre_equipo'] === '' || $equipo['numero_serie'] === '') {
            throw new RuntimeException('Debes completar colegio, nombre del equipo y numero de serie.');
        }

        if ($equipo['qr_code'] === '') {
            $base = $equipo['numero_serie'] !== '' ? $equipo['numero_serie'] : ('EQ-' . $idEquipo);
            $equipo['qr_code'] = 'PC-' . preg_replace('/[^A-Za-z0-9\-]/', '', strtoupper($base));
        }

        return [
            'equipo' => $equipo,
            'compra' => [
                'valor_equipo' => (float)str_replace(',', '.', (string)($post['valor_equipo'] ?? 0)),
                'proveedor' => trim((string)($post['proveedor'] ?? '')),
                'numero_factura' => trim((string)($post['numero_factura'] ?? '')),
                'fecha_compra' => trim((string)($post['fecha_compra'] ?? '')),
                'observacion' => trim((string)($post['observacion_compra'] ?? '')),
            ],
            'almacenamiento' => [
                'equipo_modelo' => trim((string)($post['equipo_modelo'] ?? '')),
                'equipo_capacidad' => trim((string)($post['equipo_capacidad'] ?? '')),
                'equipo_tamano' => trim((string)($post['equipo_tamano'] ?? '')),
            ],
            'procesador' => [
                'equipo_fabricante' => trim((string)($post['procesador_fabricante'] ?? '')),
                'equipo_modelo' => trim((string)($post['procesador_modelo'] ?? '')),
                'equipo_velocidad' => trim((string)($post['procesador_velocidad'] ?? '')),
            ],
            'software' => [
                'windows' => trim((string)($post['windows'] ?? '')),
                'office' => trim((string)($post['office'] ?? '')),
                'antivirus' => trim((string)($post['antivirus'] ?? '')),
            ],
            'memorias' => $this->normalizarFilas($post['memoria'] ?? [], 'orden_memoria'),
            'monitores' => $this->normalizarFilas($post['monitor'] ?? [], 'orden_monitor'),
        ];
    }

    private function normalizarFilas($filas, $campoOrden)
    {
        $resultado = [];
        foreach ($filas as $fila) {
            $limpia = [];
            foreach ($fila as $clave => $valor) {
                $limpia[$clave] = trim((string)$valor);
            }

            $tieneContenido = false;
            foreach ($limpia as $valor) {
                if ($valor !== '') {
                    $tieneContenido = true;
                    break;
                }
            }

            if (!$tieneContenido) {
                continue;
            }

            $limpia[$campoOrden] = (int)($limpia[$campoOrden] ?? 1);
            $resultado[] = $limpia;
        }
        return $resultado;
    }

    private function guardarTablasRelacionadas($idEquipo, $payload)
    {
        $stmt = mysqli_prepare($this->cn, "INSERT INTO equipos_compra (id_equipo, valor_equipo, proveedor, numero_factura, fecha_compra, observacion) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'idssss', $idEquipo, $payload['compra']['valor_equipo'], $payload['compra']['proveedor'], $payload['compra']['numero_factura'], $payload['compra']['fecha_compra'], $payload['compra']['observacion']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->cn, "INSERT INTO equipo_almacenamiento (id_equipo, equipo_modelo, equipo_capacidad, equipo_tamano) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isss', $idEquipo, $payload['almacenamiento']['equipo_modelo'], $payload['almacenamiento']['equipo_capacidad'], $payload['almacenamiento']['equipo_tamano']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->cn, "INSERT INTO equipo_procesador (id_equipo, equipo_fabricante, equipo_modelo, equipo_velocidad) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isss', $idEquipo, $payload['procesador']['equipo_fabricante'], $payload['procesador']['equipo_modelo'], $payload['procesador']['equipo_velocidad']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->cn, "INSERT INTO equipo_software (id_equipo, windows, office, antivirus) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isss', $idEquipo, $payload['software']['windows'], $payload['software']['office'], $payload['software']['antivirus']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!empty($payload['memorias'])) {
            $stmt = mysqli_prepare($this->cn, "INSERT INTO equipo_memoria (id_equipo, designacion_memoria, formato_memoria, tipo_memoria, tamano_memoria, frecuencia_memoria, marca_memoria, orden_memoria) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($payload['memorias'] as $fila) {
                $orden = (int)($fila['orden_memoria'] ?? 1);
                mysqli_stmt_bind_param($stmt, 'issssssi', $idEquipo, $fila['designacion_memoria'], $fila['formato_memoria'], $fila['tipo_memoria'], $fila['tamano_memoria'], $fila['frecuencia_memoria'], $fila['marca_memoria'], $orden);
                mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);
        }

        if (!empty($payload['monitores'])) {
            $stmt = mysqli_prepare($this->cn, "INSERT INTO equipo_monitor (id_equipo, modelo_monitor, codigo_monitor, serie_monitor, tamano_monitor, resolucion_monitor, orden_monitor) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($payload['monitores'] as $fila) {
                $orden = (int)($fila['orden_monitor'] ?? 1);
                mysqli_stmt_bind_param($stmt, 'isssssi', $idEquipo, $fila['modelo_monitor'], $fila['codigo_monitor'], $fila['serie_monitor'], $fila['tamano_monitor'], $fila['resolucion_monitor'], $orden);
                mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);
        }
    }

    private function limpiarRelacionados($idEquipo)
    {
        foreach (['equipos_compra', 'equipo_almacenamiento', 'equipo_procesador', 'equipo_software', 'equipo_memoria', 'equipo_monitor'] as $tabla) {
            $stmt = mysqli_prepare($this->cn, "DELETE FROM {$tabla} WHERE id_equipo = ?");
            mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    private function procesarEliminacionFotos($idEquipo, $post)
    {
        if (!$this->tablaExiste('equipo_fotos')) {
            return;
        }

        $idsEliminar = $post['fotos_eliminar'] ?? [];
        if (!is_array($idsEliminar) || empty($idsEliminar)) {
            return;
        }

        $stmt = mysqli_prepare($this->cn, "SELECT ruta_foto FROM equipo_fotos WHERE id_foto = ? AND id_equipo = ?");
        $stmtDelete = mysqli_prepare($this->cn, "DELETE FROM equipo_fotos WHERE id_foto = ? AND id_equipo = ?");

        foreach ($idsEliminar as $idFoto) {
            $idFoto = (int)$idFoto;
            mysqli_stmt_bind_param($stmt, 'ii', $idFoto, $idEquipo);
            mysqli_stmt_execute($stmt);
            $foto = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            mysqli_stmt_bind_param($stmtDelete, 'ii', $idFoto, $idEquipo);
            mysqli_stmt_execute($stmtDelete);

            if (!empty($foto['ruta_foto'])) {
                $rutaFisica = dirname(__DIR__) . $foto['ruta_foto'];
                if (is_file($rutaFisica)) {
                    @unlink($rutaFisica);
                }
            }
        }

        mysqli_stmt_close($stmt);
        mysqli_stmt_close($stmtDelete);
    }

    private function guardarFotos($idEquipo, $files)
    {
        if (!$this->tablaExiste('equipo_fotos')) {
            return;
        }

        $baseDir = dirname(__DIR__) . '/uploads/equipos/' . $idEquipo;
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0775, true);
        }

        if (isset($files['fotos_equipo'])) {
            $this->guardarMultiplesArchivos($idEquipo, $files['fotos_equipo'], $baseDir, 'normal');
        }
    }

    private function guardarMultiplesArchivos($idEquipo, $fileBag, $baseDir, $tipo)
    {
        if (!isset($fileBag['name']) || !is_array($fileBag['name'])) {
            return;
        }

        $stmt = mysqli_prepare($this->cn, "INSERT INTO equipo_fotos (id_equipo, ruta_foto, tipo_foto, principal, orden_foto) VALUES (?, ?, ?, ?, ?)");
        $tienePrincipal = false;
        $orden = 1;

        foreach ($fileBag['name'] as $i => $nombre) {
            if (($fileBag['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }

            $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
            $nombreSeguro = uniqid('foto_', true) . '.' . $extension;
            $destino = $baseDir . '/' . $nombreSeguro;

            if (!move_uploaded_file($fileBag['tmp_name'][$i], $destino)) {
                throw new RuntimeException('No fue posible guardar una de las fotos del equipo.');
            }

            $rutaRelativa = '/uploads/equipos/' . $idEquipo . '/' . $nombreSeguro;
            $principal = $tienePrincipal ? 0 : 1;
            mysqli_stmt_bind_param($stmt, 'issii', $idEquipo, $rutaRelativa, $tipo, $principal, $orden);
            mysqli_stmt_execute($stmt);
            $tienePrincipal = true;
            $orden++;
        }

        mysqli_stmt_close($stmt);
    }

    private function registrarHistorial($idEquipo, $accion, $descripcion, $idUsuario)
    {
        if (!$this->tablaExiste('equipo_historial')) {
            return;
        }

        $stmt = mysqli_prepare($this->cn, "INSERT INTO equipo_historial (id_equipo, accion, descripcion, id_usuario) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'issi', $idEquipo, $accion, $descripcion, $idUsuario);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
