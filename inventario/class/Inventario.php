<?php

class Inventario
{
    private $db;
    private $cn;
    private $cacheTablas  = [];
    private $cacheColumnas = [];

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

    public function columnaExiste($tabla, $columna)
    {
        $key = $tabla . '.' . $columna;
        if (isset($this->cacheColumnas[$key])) {
            return $this->cacheColumnas[$key];
        }

        $tablaS   = $this->db->escape_string($tabla);
        $columnaS = $this->db->escape_string($columna);
        $sql = "SELECT 1
                FROM information_schema.columns
                WHERE table_schema = DATABASE()
                  AND table_name   = '{$tablaS}'
                  AND column_name  = '{$columnaS}'
                LIMIT 1";
        $rs = $this->db->consulta($sql);
        $existe = $this->db->num_rows($rs) > 0;

        $this->cacheColumnas[$key] = $existe;
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
        if (!$this->tablaExiste('estado_equipo')) {
            return [];
        }

        $datos = [];
        $rs = $this->db->consulta("
            SELECT id_estado, nombre_estado, color_badge
            FROM estado_equipo
            WHERE estado = 1
            ORDER BY id_estado ASC
        ");
        while ($fila = $this->db->fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function obtenerTiposPc()
    {
        if (!$this->tablaExiste('tipo_pc_catalogo')) {
            return [];
        }

        $datos = [];
        $rs = $this->db->consulta("
            SELECT nombre_tipo
            FROM tipo_pc_catalogo
            WHERE activo = 1
            ORDER BY nombre_tipo ASC
        ");
        while ($fila = $this->db->fetch_assoc($rs)) {
            $datos[] = $fila['nombre_tipo'];
        }
        return $datos;
    }

    public function obtenerUbicacionesPorColegio($idColegio)
    {
        if (!$this->tablaExiste('equipo_ubicacion')) {
            return [];
        }

        $idColegio = (int)$idColegio;
        $datos = [];

        $rs = $this->db->consulta("
            SELECT id_ubicacion, nombre_ubicacion, tipo_ubicacion
            FROM equipo_ubicacion
            WHERE id_colegio = {$idColegio}
            AND estado = 1
            ORDER BY tipo_ubicacion ASC, nombre_ubicacion ASC
        ");

        while ($fila = $this->db->fetch_assoc($rs)) {
            $datos[] = $fila;
        }

        return $datos;
    }

    public function obtenerTodasUbicaciones()
    {
        if (!$this->tablaExiste('equipo_ubicacion')) {
            return [];
        }

        $datos = [];
        $rs = $this->db->consulta("
            SELECT id_ubicacion, id_colegio, nombre_ubicacion, tipo_ubicacion
            FROM equipo_ubicacion
            WHERE estado = 1
            ORDER BY id_colegio ASC, tipo_ubicacion ASC, nombre_ubicacion ASC
        ");

        while ($fila = $this->db->fetch_assoc($rs)) {
            $idC = (int)$fila['id_colegio'];
            $datos[$idC][] = [
                'id_ubicacion'     => (int)$fila['id_ubicacion'],
                'nombre_ubicacion' => $fila['nombre_ubicacion'],
                'tipo_ubicacion'   => $fila['tipo_ubicacion'],
            ];
        }

        return $datos;
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

        $conUbicCol      = $this->columnaExiste('equipos', 'id_ubicacion') && $this->tablaExiste('equipo_ubicacion');
        $joinUbicacion   = $conUbicCol ? "LEFT JOIN equipo_ubicacion eu ON eu.id_ubicacion = e.id_ubicacion" : '';
        $selectUbicacion = $conUbicCol ? ", eu.nombre_ubicacion" : ", NULL AS nombre_ubicacion";

        $sql = "SELECT
                    e.id_equipo,
                    e.id_usuario_asignado,
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
                    {$selectUbicacion}
                FROM equipos e
                INNER JOIN colegio c ON c.id_colegio = e.id_colegio
                LEFT JOIN usuarios u ON u.id = e.id_usuario
                LEFT JOIN usuarios ua ON ua.id = e.id_usuario_asignado
                {$joinEstado}
                {$joinUbicacion}
                {$where['sql']}
                ORDER BY e.id_equipo DESC";
        $stmt = mysqli_prepare($this->cn, $sql);
        $this->bindParams($stmt, $where['types'], $where['params']);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);

        $datos = [];
        while ($fila = mysqli_fetch_assoc($rs)) {
            $idEstado = (int)($fila['id_estado'] ?? 0);
            $idEquipo = (int)($fila['id_equipo'] ?? 0);
            $fila['badge_estado'] = '<button type="button" class="badge border-0 text-bg-' . htmlspecialchars($fila['color_badge'], ENT_QUOTES, 'UTF-8') .
                ' btnCambiarEstadoEquipo" data-id="' . $idEquipo . '" data-estado="' . $idEstado .
                '" title="Cambiar estado">' . htmlspecialchars($fila['nombre_estado'], ENT_QUOTES, 'UTF-8') . '</button>';
            $datos[] = $fila;
        }
        mysqli_stmt_close($stmt);
        return $datos;
    }

    public function obtenerEquipoCompleto($idEquipo)
    {
        $idEquipo = (int)$idEquipo;

        $joinEstado = $this->tablaExiste('estado_equipo')
            ? "LEFT JOIN estado_equipo ee ON ee.id_estado = e.id_estado"
            : '';
        $selectEstado = $this->tablaExiste('estado_equipo')
            ? "COALESCE(ee.nombre_estado, 'Sin estado') AS nombre_estado, COALESCE(ee.color_badge, 'dark') AS color_badge"
            : "CASE e.id_estado WHEN 1 THEN 'Activo' WHEN 2 THEN 'Bodega' WHEN 3 THEN 'Reparacion' WHEN 4 THEN 'Baja' WHEN 5 THEN 'Prestado' ELSE 'Sin estado' END AS nombre_estado,
               CASE e.id_estado WHEN 1 THEN 'success' WHEN 2 THEN 'secondary' WHEN 3 THEN 'warning' WHEN 4 THEN 'danger' WHEN 5 THEN 'info' ELSE 'dark' END AS color_badge";

        $conUbicCol   = $this->columnaExiste('equipos', 'id_ubicacion') && $this->tablaExiste('equipo_ubicacion');
        $joinUbicacion   = $conUbicCol ? "LEFT JOIN equipo_ubicacion eu ON eu.id_ubicacion = e.id_ubicacion" : '';
        $selectUbicacion = $conUbicCol
            ? ", eu.nombre_ubicacion, eu.tipo_ubicacion"
            : ", NULL AS nombre_ubicacion, NULL AS tipo_ubicacion";

        $conRegCol   = $this->columnaExiste('equipos', 'id_usuario_registra');
        $joinRegistra   = $conRegCol ? "LEFT JOIN usuarios ureg ON ureg.id = e.id_usuario_registra" : '';
        $selectRegistra = $conRegCol
            ? ", CONCAT(ureg.nombre, ' ', ureg.apellido_paterno) AS nombre_usuario_registra"
            : ", NULL AS nombre_usuario_registra";

        $sql = "SELECT
                    e.*,
                    c.nom_colegio,
                    CONCAT(ur.nombre, ' ', ur.apellido_paterno, ' ', ur.apellido_materno) AS usuario_registra,
                    CONCAT(ua.nombre, ' ', ua.apellido_paterno, ' ', ua.apellido_materno) AS usuario_asignado,
                    {$selectEstado}
                    {$selectUbicacion}
                    {$selectRegistra}
                FROM equipos e
                INNER JOIN colegio c ON c.id_colegio = e.id_colegio
                LEFT JOIN usuarios ur ON ur.id = e.id_usuario
                LEFT JOIN usuarios ua ON ua.id = e.id_usuario_asignado
                {$joinEstado}
                {$joinUbicacion}
                {$joinRegistra}
                WHERE e.id_equipo = ?";
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
        mysqli_stmt_execute($stmt);
        $equipo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$equipo) {
            return null;
        }

        $equipo['compra']         = $this->obtenerFilaSimple('SELECT * FROM equipos_compra WHERE id_equipo = ?', $idEquipo);
        $equipo['almacenamiento'] = $this->obtenerFilaSimple('SELECT * FROM equipo_almacenamiento WHERE id_equipo = ?', $idEquipo);
        $equipo['procesador']     = $this->obtenerFilaSimple('SELECT * FROM equipo_procesador WHERE id_equipo = ?', $idEquipo);
        $equipo['software']       = $this->obtenerFilaSimple('SELECT * FROM equipo_software WHERE id_equipo = ?', $idEquipo);
        $equipo['memorias']       = $this->obtenerVariasFilas('SELECT * FROM equipo_memoria WHERE id_equipo = ? ORDER BY orden_memoria ASC, id_memoria ASC', $idEquipo);
        $equipo['monitores']      = $this->obtenerVariasFilas('SELECT * FROM equipo_monitor WHERE id_equipo = ? ORDER BY orden_monitor ASC, id_monitor ASC', $idEquipo);
        $equipo['fotos']          = $this->tablaExiste('equipo_fotos')
            ? $this->obtenerVariasFilas('SELECT * FROM equipo_fotos WHERE id_equipo = ? ORDER BY principal DESC, orden_foto ASC, id_foto ASC', $idEquipo)
            : [];
        $equipo['movimientos']    = $this->obtenerMovimientosEquipo($idEquipo);
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
        return $this->cambiarEstadoEquipo($idEquipo, $nuevoEstado, $idUsuario);
    }

    public function cambiarEstadoEquipo($idEquipo, $idEstado, $idUsuario)
    {
        $idEquipo = (int)$idEquipo;
        $idEstado = (int)$idEstado;
        $idUsuario = (int)$idUsuario;

        if ($idEquipo <= 0) {
            throw new RuntimeException('Equipo no valido.');
        }
        if ($idEstado <= 0) {
            throw new RuntimeException('Estado no valido.');
        }
        if ($idUsuario <= 0) {
            throw new RuntimeException('Usuario no valido.');
        }

        if (!$this->tablaExiste('estado_equipo')) {
            throw new RuntimeException('No existe la tabla de estados de equipo.');
        }

        $stmt = mysqli_prepare($this->cn, "SELECT 1 FROM estado_equipo WHERE id_estado = ? AND estado = 1 LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $idEstado);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $estadoExiste = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if (!$estadoExiste) {
            throw new RuntimeException('El estado seleccionado no existe o esta inactivo.');
        }

        $condEliminado = $this->columnaExiste('equipos', 'eliminado') ? ' AND e.eliminado = 0' : '';
        $stmt = mysqli_prepare($this->cn, "SELECT e.id_equipo, e.id_estado FROM equipos e WHERE e.id_equipo = ?{$condEliminado} LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
        mysqli_stmt_execute($stmt);
        $actual = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
        mysqli_stmt_close($stmt);

        if (!$actual) {
            throw new RuntimeException('El equipo no existe o fue eliminado.');
        }

        $idEstadoAnterior = isset($actual['id_estado']) ? (int)$actual['id_estado'] : null;

        mysqli_begin_transaction($this->cn);
        try {
            $stmt = mysqli_prepare($this->cn, "UPDATE equipos SET id_estado = ? WHERE id_equipo = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $idEstado, $idEquipo);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($this->tablaExiste('equipo_estado_historial')) {
                $observacion = 'Cambio de estado desde listado.';
                $stmt = mysqli_prepare($this->cn, "
                    INSERT INTO equipo_estado_historial
                        (id_equipo, id_estado_anterior, id_estado_nuevo, id_usuario_accion, observacion)
                    VALUES (?, ?, ?, ?, ?)
                ");
                mysqli_stmt_bind_param($stmt, 'iiiis', $idEquipo, $idEstadoAnterior, $idEstado, $idUsuario, $observacion);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            $this->registrarHistorial($idEquipo, 'estado', 'Cambio de estado desde listado.', $idUsuario);
            mysqli_commit($this->cn);
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }

        return true;
    }

    public function eliminarEquipo(int $idEquipo, int $idUsuario): bool
    {
        if ($idEquipo <= 0) {
            throw new RuntimeException('Equipo no valido.');
        }
        if ($idUsuario <= 0) {
            throw new RuntimeException('Usuario no valido.');
        }
        if (!$this->columnaExiste('equipos', 'eliminado')) {
            throw new RuntimeException('Falta ejecutar la migracion de eliminacion logica de equipos.');
        }

        $stmt = mysqli_prepare($this->cn, "
            SELECT id_equipo, eliminado
            FROM equipos
            WHERE id_equipo = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
        mysqli_stmt_execute($stmt);
        $actual = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
        mysqli_stmt_close($stmt);

        if (!$actual) {
            throw new RuntimeException('El equipo indicado no existe.');
        }
        if ((int)($actual['eliminado'] ?? 0) === 1) {
            throw new RuntimeException('El equipo ya fue eliminado.');
        }

        $setFecha = $this->columnaExiste('equipos', 'fecha_eliminado') ? ', fecha_eliminado = NOW()' : '';
        $setUsuario = $this->columnaExiste('equipos', 'id_usuario_elimina') ? ', id_usuario_elimina = ?' : '';
        $sql = "UPDATE equipos SET eliminado = 1{$setFecha}{$setUsuario} WHERE id_equipo = ?";
        $stmt = mysqli_prepare($this->cn, $sql);
        if ($setUsuario !== '') {
            mysqli_stmt_bind_param($stmt, 'ii', $idUsuario, $idEquipo);
        } else {
            mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $this->registrarHistorial($idEquipo, 'eliminacion_logica', 'Equipo eliminado logicamente desde listado.', $idUsuario);
        return true;
    }

    public function usuarioPuedeGestionarEquipo(int $idEquipo, int $idColegioUsuario): bool
    {
        if ($idColegioUsuario <= 0) {
            return true;
        }

        $stmt = mysqli_prepare($this->cn, "SELECT id_colegio FROM equipos WHERE id_equipo = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
        mysqli_stmt_close($stmt);

        return (int)($fila['id_colegio'] ?? 0) === $idColegioUsuario;
    }

    public function renderBadgeEstado($idEstado, $nombreEstado = '', $colorBadge = '')
    {
        if ($nombreEstado === '' || $colorBadge === '') {
            $meta = ['nombre_estado' => 'Sin estado', 'color_badge' => 'dark'];
            if ($this->tablaExiste('estado_equipo')) {
                $idInt = (int)$idEstado;
                $stmt = mysqli_prepare($this->cn, "SELECT nombre_estado, color_badge FROM estado_equipo WHERE id_estado = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt, 'i', $idInt);
                mysqli_stmt_execute($stmt);
                $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                mysqli_stmt_close($stmt);
                if ($fila) {
                    $meta = $fila;
                }
            }
            $nombreEstado = $nombreEstado !== '' ? $nombreEstado : $meta['nombre_estado'];
            $colorBadge   = $colorBadge  !== '' ? $colorBadge  : $meta['color_badge'];
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

        if ($this->columnaExiste('equipos', 'eliminado')) {
            $condiciones[] = 'e.eliminado = 0';
        }

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
        if (!empty($filtros['id_ubicacion']) && $this->columnaExiste('equipos', 'id_ubicacion')) {
            $condiciones[] = 'e.id_ubicacion = ?';
            $types .= 'i';
            $params[] = (int)$filtros['id_ubicacion'];
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

    public function guardarEquipo($post, $files, $idUsuario, $idColegio = 0)
    {
        $payload = $this->normalizarPayload($post, 0, $idUsuario, (int)$idColegio);

        if ($this->validarSerieDuplicada($payload['equipo']['numero_serie'])) {
            throw new RuntimeException('El numero de serie ya existe en otro equipo.');
        }

        mysqli_begin_transaction($this->cn);

        try {
            $idEquipo = $this->insertarRegistroEquipo($payload);
            $this->guardarTablasRelacionadas($idEquipo, $payload);
            $this->guardarFotos($idEquipo, $files);

            $idUbicacion = (int)($payload['equipo']['id_ubicacion'] ?? 0);
            if ($idUbicacion > 0) {
                $this->registrarMovimiento(
                    $idEquipo, 0, $idUbicacion, (int)$idUsuario,
                    'Alta inicial de inventario',
                    'Equipo registrado inicialmente en esta ubicacion.'
                );
            }

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
        $p      = $payload['equipo'];
        $cols   = ['id_usuario', 'id_colegio', 'id_usuario_asignado', 'nombre_equipo',
                   'fabricante', 'producto', 'numero_serie', 'tipo_pc', 'qr_code', 'id_estado'];
        $vals   = ['?', '?', '?', '?', '?', '?', '?', '?', '?', '?'];
        $types  = 'iiissssssi';
        $params = [
            $p['id_usuario'], $p['id_colegio'], $p['id_usuario_asignado'],
            $p['nombre_equipo'], $p['fabricante'], $p['producto'],
            $p['numero_serie'], $p['tipo_pc'], $p['qr_code'], $p['id_estado'],
        ];

        if (isset($p['id_ubicacion']) && $this->columnaExiste('equipos', 'id_ubicacion')) {
            $cols[]   = 'id_ubicacion';
            $vals[]   = 'NULLIF(?, 0)';
            $types   .= 'i';
            $params[] = (int)$p['id_ubicacion'];
        }

        if (isset($p['id_usuario_registra']) && $this->columnaExiste('equipos', 'id_usuario_registra')) {
            $cols[]   = 'id_usuario_registra';
            $vals[]   = '?';
            $types   .= 'i';
            $params[] = (int)$p['id_usuario_registra'];
        }

        $sql  = 'INSERT INTO equipos (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $vals) . ')';
        $stmt = mysqli_prepare($this->cn, $sql);
        $this->bindParams($stmt, $types, $params);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return (int)mysqli_insert_id($this->cn);
    }

    public function actualizarEquipo($idEquipo, $post, $files, $idUsuario)
    {
        $idEquipo = (int)$idEquipo;

        // Fetch current equipo state before any changes (colegio is immutable; detect ubicacion change)
        $actual = $this->obtenerCamposBasicos($idEquipo);
        if (!$actual) {
            throw new RuntimeException('El equipo indicado no existe.');
        }
        $idColegio           = (int)$actual['id_colegio'];
        $idUbicacionAnterior = (int)($actual['id_ubicacion'] ?? 0);

        $payload = $this->normalizarPayload($post, $idEquipo, $idUsuario, $idColegio);

        if ($this->validarSerieDuplicada($payload['equipo']['numero_serie'], $idEquipo)) {
            throw new RuntimeException('El numero de serie ya existe en otro equipo.');
        }

        $idUbicacionNueva       = (int)($payload['equipo']['id_ubicacion'] ?? 0);
        $observacionMovimiento  = trim((string)($post['observacion_movimiento'] ?? ''));

        mysqli_begin_transaction($this->cn);

        try {
            $cols   = ['id_usuario_asignado=?', 'nombre_equipo=?', 'fabricante=?', 'producto=?',
                       'numero_serie=?', 'tipo_pc=?', 'qr_code=?', 'id_estado=?'];
            $types  = 'issssssi';
            $params = [
                $payload['equipo']['id_usuario_asignado'],
                $payload['equipo']['nombre_equipo'],
                $payload['equipo']['fabricante'],
                $payload['equipo']['producto'],
                $payload['equipo']['numero_serie'],
                $payload['equipo']['tipo_pc'],
                $payload['equipo']['qr_code'],
                $payload['equipo']['id_estado'],
            ];

            if ($this->columnaExiste('equipos', 'id_ubicacion')) {
                $cols[]   = 'id_ubicacion=NULLIF(?, 0)';
                $types   .= 'i';
                $params[] = $idUbicacionNueva;
            }

            $types   .= 'i';
            $params[] = $idEquipo;

            $sql  = 'UPDATE equipos SET ' . implode(', ', $cols) . ' WHERE id_equipo = ?';
            $stmt = mysqli_prepare($this->cn, $sql);
            $this->bindParams($stmt, $types, $params);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Register location movement when ubicacion changes
            if ($idUbicacionNueva > 0 && $idUbicacionNueva !== $idUbicacionAnterior) {
                $this->registrarMovimiento(
                    $idEquipo,
                    $idUbicacionAnterior,
                    $idUbicacionNueva,
                    (int)$idUsuario,
                    'Cambio de ubicacion',
                    $observacionMovimiento
                );
            }

            $this->limpiarRelacionados($idEquipo);
            $this->guardarTablasRelacionadas($idEquipo, $payload);
            $this->procesarEliminacionFotos($idEquipo, $post);
            $this->procesarFotoPrincipal($idEquipo, $post);
            $this->guardarFotos($idEquipo, $files);
            $this->registrarHistorial($idEquipo, 'actualizacion', 'Equipo actualizado desde el modulo de inventario.', $idUsuario);

            mysqli_commit($this->cn);
            return true;
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    private function normalizarPayload($post, $idEquipo, $idUsuario, $idColegio = 0)
    {
        $idColegio = (int)$idColegio;

        $equipo = [
            'id_usuario'          => (int)$idUsuario,
            'id_colegio'          => $idColegio,
            'id_usuario_registra' => (int)$idUsuario,
            'id_usuario_asignado' => (int)($post['id_usuario_asignado'] ?? 0),
            'nombre_equipo'       => trim((string)($post['nombre_equipo'] ?? '')),
            'fabricante'          => trim((string)($post['fabricante'] ?? '')),
            'producto'            => trim((string)($post['producto'] ?? '')),
            'numero_serie'        => trim((string)($post['numero_serie'] ?? '')),
            'tipo_pc'             => trim((string)($post['tipo_pc'] ?? '')),
            'qr_code'             => trim((string)($post['qr_code'] ?? '')),
            'id_estado'           => (int)($post['id_estado'] ?? 1),
            'id_ubicacion'        => !empty($post['id_ubicacion']) ? (int)$post['id_ubicacion'] : 0,
        ];

        if ($idColegio <= 0) {
            throw new RuntimeException('No se pudo determinar el colegio del usuario. Contacta al administrador.');
        }

        if ($equipo['nombre_equipo'] === '') {
            throw new RuntimeException('El nombre del equipo es obligatorio.');
        }

        if ($equipo['numero_serie'] === '') {
            throw new RuntimeException('El numero de serie es obligatorio.');
        }

        if ($equipo['tipo_pc'] === '') {
            throw new RuntimeException('Debes seleccionar el tipo de PC.');
        }

        if ($this->tablaExiste('equipo_ubicacion') && $equipo['id_ubicacion'] <= 0) {
            throw new RuntimeException('Debes seleccionar una ubicacion para el equipo.');
        }

        if ($equipo['id_ubicacion'] > 0 && $this->tablaExiste('equipo_ubicacion')) {
            $stmt = mysqli_prepare($this->cn,
                "SELECT 1 FROM equipo_ubicacion WHERE id_ubicacion = ? AND id_colegio = ? AND estado = 1 LIMIT 1"
            );
            mysqli_stmt_bind_param($stmt, 'ii', $equipo['id_ubicacion'], $idColegio);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $valid = mysqli_stmt_num_rows($stmt) > 0;
            mysqli_stmt_close($stmt);
            if (!$valid) {
                throw new RuntimeException('La ubicacion seleccionada no pertenece al colegio del usuario.');
            }
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
            $stmt = mysqli_prepare($this->cn, "SELECT 1 FROM equipo_fotos WHERE id_equipo = ? AND principal = 1 LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $yaExistePrincipal = mysqli_stmt_num_rows($stmt) > 0;
            mysqli_stmt_close($stmt);

            $this->guardarMultiplesArchivos($idEquipo, $files['fotos_equipo'], $baseDir, 'normal', $yaExistePrincipal);
        }
    }

    private function guardarMultiplesArchivos($idEquipo, $fileBag, $baseDir, $tipo, $yaExistePrincipal = false)
    {
        if (!isset($fileBag['name']) || !is_array($fileBag['name'])) {
            return;
        }

        $stmtOrden = mysqli_prepare($this->cn, "SELECT COALESCE(MAX(orden_foto), 0) FROM equipo_fotos WHERE id_equipo = ?");
        mysqli_stmt_bind_param($stmtOrden, 'i', $idEquipo);
        mysqli_stmt_execute($stmtOrden);
        $rsOrden = mysqli_stmt_get_result($stmtOrden);
        $ordenInicio = (int)(mysqli_fetch_row($rsOrden)[0] ?? 0) + 1;
        mysqli_stmt_close($stmtOrden);

        $stmt = mysqli_prepare($this->cn, "INSERT INTO equipo_fotos (id_equipo, ruta_foto, tipo_foto, principal, orden_foto) VALUES (?, ?, ?, ?, ?)");
        $tienePrincipal = $yaExistePrincipal;
        $orden = $ordenInicio;

        foreach ($fileBag['name'] as $i => $nombre) {
            if (($fileBag['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }

            $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                continue;
            }
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

    private function registrarMovimiento(int $idEquipo, int $idUbicOrigen, int $idUbicDestino, int $idUsuario, string $motivo, string $observacion = ''): void
    {
        if (!$this->tablaExiste('equipo_movimiento')) {
            return;
        }

        $stmt = mysqli_prepare($this->cn, "
            INSERT INTO equipo_movimiento
                (id_equipo, id_ubicacion_origen, id_ubicacion_destino, id_usuario_movimiento, motivo, observacion)
            VALUES (?, NULLIF(?, 0), ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, 'iiiiss', $idEquipo, $idUbicOrigen, $idUbicDestino, $idUsuario, $motivo, $observacion);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function obtenerMovimientosEquipo(int $idEquipo): array
    {
        if (!$this->tablaExiste('equipo_movimiento')) {
            return [];
        }

        $conUbicacion = $this->tablaExiste('equipo_ubicacion');
        $joinUbic     = $conUbicacion
            ? "LEFT JOIN equipo_ubicacion uo ON uo.id_ubicacion = m.id_ubicacion_origen
               LEFT JOIN equipo_ubicacion ud ON ud.id_ubicacion = m.id_ubicacion_destino"
            : '';
        $selectUbic   = $conUbicacion
            ? "uo.nombre_ubicacion AS ubicacion_origen, ud.nombre_ubicacion AS ubicacion_destino,"
            : "NULL AS ubicacion_origen, NULL AS ubicacion_destino,";

        $stmt = mysqli_prepare($this->cn, "
            SELECT
                m.id_movimiento,
                m.fecha_movimiento,
                m.motivo,
                m.observacion,
                {$selectUbic}
                CONCAT(u.nombre, ' ', u.apellido_paterno) AS usuario_movimiento
            FROM equipo_movimiento m
            LEFT JOIN usuarios u ON u.id = m.id_usuario_movimiento
            {$joinUbic}
            WHERE m.id_equipo = ?
            ORDER BY m.fecha_movimiento DESC, m.id_movimiento DESC
        ");
        mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
        mysqli_stmt_execute($stmt);
        $rs    = mysqli_stmt_get_result($stmt);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        mysqli_stmt_close($stmt);
        return $datos;
    }

    private function obtenerCamposBasicos(int $idEquipo): array
    {
        $selectExtra = $this->columnaExiste('equipos', 'id_ubicacion') ? ', id_ubicacion' : '';
        $stmt = mysqli_prepare($this->cn, "SELECT id_colegio{$selectExtra} FROM equipos WHERE id_equipo = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
        mysqli_stmt_close($stmt);
        return $fila;
    }

    private function procesarFotoPrincipal(int $idEquipo, array $post): void
    {
        if (!$this->tablaExiste('equipo_fotos')) {
            return;
        }

        $idFoto = (int)($post['foto_principal'] ?? 0);
        if ($idFoto <= 0) {
            return;
        }

        // Verify the photo belongs to this equipo
        $stmt = mysqli_prepare($this->cn, "SELECT 1 FROM equipo_fotos WHERE id_foto = ? AND id_equipo = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'ii', $idFoto, $idEquipo);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $existe = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if (!$existe) {
            return;
        }

        $stmt = mysqli_prepare($this->cn, "UPDATE equipo_fotos SET principal = 0 WHERE id_equipo = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->cn, "UPDATE equipo_fotos SET principal = 1 WHERE id_foto = ? AND id_equipo = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $idFoto, $idEquipo);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // =========================================================================
    // MONITORES
    // =========================================================================

    private function normalizarPayloadMonitor(array $post, int $idUsuario, int $idColegio): array
    {
        if ($idColegio <= 0) {
            throw new RuntimeException('No se pudo determinar el colegio del usuario.');
        }

        $nombre      = trim((string)($post['nombre_monitor'] ?? ''));
        $serie       = trim((string)($post['numero_serie'] ?? ''));
        $idEstado    = (int)($post['id_estado'] ?? 1);
        $idUbicacion = !empty($post['id_ubicacion']) ? (int)$post['id_ubicacion'] : 0;

        if ($nombre === '') {
            throw new RuntimeException('El nombre del monitor es obligatorio.');
        }
        if ($serie === '') {
            throw new RuntimeException('El número de serie es obligatorio.');
        }
        if ($idEstado <= 0) {
            throw new RuntimeException('Debes seleccionar un estado para el monitor.');
        }
        if ($this->tablaExiste('equipo_ubicacion') && $idUbicacion <= 0) {
            throw new RuntimeException('Debes seleccionar una ubicación para el monitor.');
        }
        if ($idUbicacion > 0 && $this->tablaExiste('equipo_ubicacion')) {
            $stmt = mysqli_prepare($this->cn, "SELECT 1 FROM equipo_ubicacion WHERE id_ubicacion = ? AND id_colegio = ? AND estado = 1 LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'ii', $idUbicacion, $idColegio);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $valida = mysqli_stmt_num_rows($stmt) > 0;
            mysqli_stmt_close($stmt);
            if (!$valida) {
                throw new RuntimeException('La ubicación seleccionada no pertenece al colegio del usuario.');
            }
        }

        return [
            'id_colegio'          => $idColegio,
            'id_ubicacion'        => $idUbicacion,
            'id_usuario_asignado' => (int)($post['id_usuario_asignado'] ?? 0),
            'id_usuario_registra' => $idUsuario,
            'id_estado'           => $idEstado,
            'nombre_monitor'      => $nombre,
            'marca'               => trim((string)($post['marca'] ?? '')),
            'modelo'              => trim((string)($post['modelo'] ?? '')),
            'numero_serie'        => $serie,
            'codigo_interno'      => trim((string)($post['codigo_interno'] ?? '')),
            'tamano_monitor'      => trim((string)($post['tamano_monitor'] ?? '')),
            'resolucion_monitor'  => trim((string)($post['resolucion_monitor'] ?? '')),
            'tipo_panel'          => trim((string)($post['tipo_panel'] ?? '')),
            'tipo_conexion'       => trim((string)($post['tipo_conexion'] ?? '')),
            'observacion'         => trim((string)($post['observacion'] ?? '')),
            'compra' => [
                'valor_monitor'  => (int)str_replace([',', '.'], '', (string)($post['valor_monitor'] ?? 0)),
                'proveedor'      => trim((string)($post['proveedor'] ?? '')),
                'numero_factura' => trim((string)($post['numero_factura'] ?? '')),
                'fecha_compra'   => trim((string)($post['fecha_compra'] ?? '')),
                'observacion'    => trim((string)($post['observacion_compra'] ?? '')),
            ],
        ];
    }

    private function insertarMonitor(array $p): int
    {
        $sql = "INSERT INTO monitores
                (id_colegio, id_ubicacion, id_usuario_asignado, id_usuario_registra, id_estado,
                 nombre_monitor, marca, modelo, numero_serie, codigo_interno,
                 tamano_monitor, resolucion_monitor, tipo_panel, tipo_conexion, observacion)
                VALUES (?, NULLIF(?, 0), NULLIF(?, 0), ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->cn, $sql);
        $this->bindParams($stmt, 'iiiiissssssssss', [
            $p['id_colegio'], $p['id_ubicacion'], $p['id_usuario_asignado'],
            $p['id_usuario_registra'], $p['id_estado'],
            $p['nombre_monitor'], $p['marca'], $p['modelo'], $p['numero_serie'], $p['codigo_interno'],
            $p['tamano_monitor'], $p['resolucion_monitor'], $p['tipo_panel'], $p['tipo_conexion'], $p['observacion'],
        ]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return (int)mysqli_insert_id($this->cn);
    }

    private function insertarCompraMonitor(int $idMonitor, array $compra): void
    {
        if (!$this->tablaExiste('monitor_compra')) {
            return;
        }
        $fechaCompra = $compra['fecha_compra'] !== '' ? $compra['fecha_compra'] : null;
        $stmt = mysqli_prepare($this->cn,
            "INSERT INTO monitor_compra (id_monitor, valor_monitor, proveedor, numero_factura, fecha_compra, observacion)
             VALUES (?, NULLIF(?, 0), NULLIF(?, ''), NULLIF(?, ''), ?, NULLIF(?, ''))
             ON DUPLICATE KEY UPDATE
                valor_monitor = VALUES(valor_monitor),
                proveedor = VALUES(proveedor),
                numero_factura = VALUES(numero_factura),
                fecha_compra = VALUES(fecha_compra),
                observacion = VALUES(observacion)");
        mysqli_stmt_bind_param($stmt, 'iissss',
            $idMonitor, $compra['valor_monitor'], $compra['proveedor'],
            $compra['numero_factura'], $fechaCompra, $compra['observacion']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    private function obtenerCompraMonitor(int $idMonitor): array
    {
        if (!$this->tablaExiste('monitor_compra')) {
            return [];
        }
        $stmt = mysqli_prepare($this->cn, "SELECT * FROM monitor_compra WHERE id_monitor = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $idMonitor);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
        mysqli_stmt_close($stmt);
        return $fila;
    }

    private function obtenerCamposBasicosMonitor(int $idMonitor): array
    {
        $stmt = mysqli_prepare($this->cn,
            "SELECT id_colegio, id_ubicacion FROM monitores WHERE id_monitor = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $idMonitor);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
        mysqli_stmt_close($stmt);
        return $fila;
    }

    private function eliminarFotosMonitor(int $idMonitor, array $ids): void
    {
        if (!$this->tablaExiste('monitor_fotos') || empty($ids)) {
            return;
        }
        foreach ($ids as $idFoto) {
            $idFoto = (int)$idFoto;
            if ($idFoto <= 0) {
                continue;
            }
            $stmt = mysqli_prepare($this->cn,
                "SELECT ruta_foto FROM monitor_fotos WHERE id_foto = ? AND id_monitor = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'ii', $idFoto, $idMonitor);
            mysqli_stmt_execute($stmt);
            $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);
            if ($fila && $fila['ruta_foto']) {
                $rutaFisica = dirname(__DIR__, 2) . '/' . ltrim($fila['ruta_foto'], '/');
                if (file_exists($rutaFisica)) {
                    @unlink($rutaFisica);
                }
            }
            $stmtDel = mysqli_prepare($this->cn,
                "DELETE FROM monitor_fotos WHERE id_foto = ? AND id_monitor = ?");
            mysqli_stmt_bind_param($stmtDel, 'ii', $idFoto, $idMonitor);
            mysqli_stmt_execute($stmtDel);
            mysqli_stmt_close($stmtDel);
        }
    }

    private function procesarFotoPrincipalMonitor(int $idMonitor, array $post): void
    {
        if (!$this->tablaExiste('monitor_fotos')) {
            return;
        }
        $idFoto = (int)($post['foto_principal'] ?? 0);
        if ($idFoto <= 0) {
            return;
        }
        $stmt = mysqli_prepare($this->cn,
            "SELECT 1 FROM monitor_fotos WHERE id_foto = ? AND id_monitor = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'ii', $idFoto, $idMonitor);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $existe = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        if (!$existe) {
            return;
        }
        $stmt = mysqli_prepare($this->cn,
            "UPDATE monitor_fotos SET principal = 0 WHERE id_monitor = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idMonitor);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->cn,
            "UPDATE monitor_fotos SET principal = 1 WHERE id_foto = ? AND id_monitor = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $idFoto, $idMonitor);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function guardarFotosMonitor(int $idMonitor, $files): void
    {
        if (!$this->tablaExiste('monitor_fotos')) {
            return;
        }
        if (!isset($files['fotos_monitor']) || !is_array($files['fotos_monitor']['name'] ?? null)) {
            return;
        }

        $baseDir = dirname(__DIR__) . '/uploads/monitores/' . $idMonitor;
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0775, true);
        }

        $stmt = mysqli_prepare($this->cn,
            "SELECT 1 FROM monitor_fotos WHERE id_monitor = ? AND principal = 1 LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $idMonitor);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $tienePrincipal = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        $stmtOrden = mysqli_prepare($this->cn,
            "SELECT COALESCE(MAX(orden_foto), 0) FROM monitor_fotos WHERE id_monitor = ?");
        mysqli_stmt_bind_param($stmtOrden, 'i', $idMonitor);
        mysqli_stmt_execute($stmtOrden);
        $rsOrden = mysqli_stmt_get_result($stmtOrden);
        $orden   = (int)(mysqli_fetch_row($rsOrden)[0] ?? 0) + 1;
        mysqli_stmt_close($stmtOrden);

        $stmtIns = mysqli_prepare($this->cn,
            "INSERT INTO monitor_fotos (id_monitor, ruta_foto, tipo_foto, principal, orden_foto)
             VALUES (?, ?, 'normal', ?, ?)");

        foreach ($files['fotos_monitor']['name'] as $i => $nombre) {
            if (($files['fotos_monitor']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }
            $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                continue;
            }
            $nombreSeguro = uniqid('mon_', true) . '.' . $ext;
            $destino      = $baseDir . '/' . $nombreSeguro;
            if (!move_uploaded_file($files['fotos_monitor']['tmp_name'][$i], $destino)) {
                throw new RuntimeException('No fue posible guardar una de las fotos del monitor.');
            }
            $rutaRelativa = 'inventario/uploads/monitores/' . $idMonitor . '/' . $nombreSeguro;
            $principal    = $tienePrincipal ? 0 : 1;
            mysqli_stmt_bind_param($stmtIns, 'isii', $idMonitor, $rutaRelativa, $principal, $orden);
            mysqli_stmt_execute($stmtIns);
            $tienePrincipal = true;
            $orden++;
        }
        mysqli_stmt_close($stmtIns);
    }

    public function obtenerFotosMonitor(int $idMonitor): array
    {
        if (!$this->tablaExiste('monitor_fotos')) {
            return [];
        }
        $stmt = mysqli_prepare($this->cn,
            "SELECT id_foto, ruta_foto, tipo_foto, principal, orden_foto
             FROM monitor_fotos WHERE id_monitor = ? ORDER BY principal DESC, orden_foto ASC");
        mysqli_stmt_bind_param($stmt, 'i', $idMonitor);
        mysqli_stmt_execute($stmt);
        $rs    = mysqli_stmt_get_result($stmt);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        mysqli_stmt_close($stmt);
        return $datos;
    }

    public function marcarFotoPrincipalMonitor(int $idMonitor, int $idFoto): void
    {
        if (!$this->tablaExiste('monitor_fotos')) {
            return;
        }
        $stmt = mysqli_prepare($this->cn,
            "SELECT 1 FROM monitor_fotos WHERE id_foto = ? AND id_monitor = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'ii', $idFoto, $idMonitor);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $existe = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        if (!$existe) {
            throw new RuntimeException('La foto no pertenece a este monitor.');
        }
        $stmt = mysqli_prepare($this->cn,
            "UPDATE monitor_fotos SET principal = 0 WHERE id_monitor = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idMonitor);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->cn,
            "UPDATE monitor_fotos SET principal = 1 WHERE id_foto = ? AND id_monitor = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $idFoto, $idMonitor);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function registrarMovimientoMonitor(int $idMonitor, int $idOrigen, int $idDestino, int $idUsuario, string $motivo, string $observacion = ''): void
    {
        if (!$this->tablaExiste('monitor_movimiento')) {
            return;
        }
        $stmt = mysqli_prepare($this->cn,
            "INSERT INTO monitor_movimiento
             (id_monitor, id_ubicacion_origen, id_ubicacion_destino, id_usuario_movimiento, motivo, observacion)
             VALUES (?, NULLIF(?, 0), NULLIF(?, 0), ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iiiiss',
            $idMonitor, $idOrigen, $idDestino, $idUsuario, $motivo, $observacion);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function obtenerMovimientosMonitor(int $idMonitor): array
    {
        if (!$this->tablaExiste('monitor_movimiento')) {
            return [];
        }
        $conUbic    = $this->tablaExiste('equipo_ubicacion');
        $joinUbic   = $conUbic
            ? "LEFT JOIN equipo_ubicacion uo ON uo.id_ubicacion = m.id_ubicacion_origen
               LEFT JOIN equipo_ubicacion ud ON ud.id_ubicacion = m.id_ubicacion_destino"
            : '';
        $selectUbic = $conUbic
            ? "uo.nombre_ubicacion AS ubicacion_origen, ud.nombre_ubicacion AS ubicacion_destino,"
            : "NULL AS ubicacion_origen, NULL AS ubicacion_destino,";

        $stmt = mysqli_prepare($this->cn, "
            SELECT
                m.fecha_movimiento,
                {$selectUbic}
                m.motivo,
                m.observacion,
                CONCAT(u.nombre, ' ', u.apellido_paterno) AS usuario_movimiento
            FROM monitor_movimiento m
            LEFT JOIN usuarios u ON u.id = m.id_usuario_movimiento
            {$joinUbic}
            WHERE m.id_monitor = ?
            ORDER BY m.fecha_movimiento DESC, m.id_movimiento DESC
        ");
        mysqli_stmt_bind_param($stmt, 'i', $idMonitor);
        mysqli_stmt_execute($stmt);
        $rs    = mysqli_stmt_get_result($stmt);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        mysqli_stmt_close($stmt);
        return $datos;
    }

    public function listarMonitores(array $filtros = []): array
    {
        if (!$this->tablaExiste('monitores')) {
            return [];
        }
        $conEliminado = $this->columnaExiste('monitores', 'eliminado');
        $conUbicacion = $this->tablaExiste('equipo_ubicacion');
        $conEstado    = $this->tablaExiste('estado_equipo');

        $joinEstado    = $conEstado ? "LEFT JOIN estado_equipo ee ON ee.id_estado = m.id_estado" : '';
        $selectEstado  = $conEstado
            ? "COALESCE(ee.nombre_estado, 'Sin estado') AS nombre_estado, COALESCE(ee.color_badge, 'dark') AS color_badge"
            : "'Sin estado' AS nombre_estado, 'dark' AS color_badge";
        $joinUbicacion   = $conUbicacion ? "LEFT JOIN equipo_ubicacion eu ON eu.id_ubicacion = m.id_ubicacion" : '';
        $selectUbicacion = $conUbicacion ? ", eu.nombre_ubicacion, eu.tipo_ubicacion" : ", NULL AS nombre_ubicacion, NULL AS tipo_ubicacion";

        $condiciones = [];
        $types       = '';
        $params      = [];

        if ($conEliminado) {
            $condiciones[] = 'm.eliminado = 0';
        }
        if (!empty($filtros['id_colegio'])) {
            $condiciones[] = 'm.id_colegio = ?';
            $types .= 'i';
            $params[] = (int)$filtros['id_colegio'];
        }
        if (!empty($filtros['id_estado'])) {
            $condiciones[] = 'm.id_estado = ?';
            $types .= 'i';
            $params[] = (int)$filtros['id_estado'];
        }
        if (!empty($filtros['id_usuario_asignado'])) {
            $condiciones[] = 'm.id_usuario_asignado = ?';
            $types .= 'i';
            $params[] = (int)$filtros['id_usuario_asignado'];
        }
        if (!empty($filtros['busqueda'])) {
            $busq          = '%' . $filtros['busqueda'] . '%';
            $condiciones[] = '(m.nombre_monitor LIKE ? OR m.numero_serie LIKE ? OR m.marca LIKE ? OR m.modelo LIKE ? OR m.codigo_interno LIKE ?)';
            $types .= 'sssss';
            for ($i = 0; $i < 5; $i++) {
                $params[] = $busq;
            }
        }

        $where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';

        $sql = "SELECT
                    m.id_monitor,
                    m.id_usuario_asignado,
                    m.nombre_monitor,
                    m.marca,
                    m.modelo,
                    m.numero_serie,
                    m.codigo_interno,
                    m.tamano_monitor,
                    m.id_estado,
                    c.nom_colegio,
                    CONCAT(ua.nombre, ' ', ua.apellido_paterno) AS usuario_asignado,
                    {$selectEstado}
                    {$selectUbicacion}
                FROM monitores m
                INNER JOIN colegio c ON c.id_colegio = m.id_colegio
                LEFT JOIN usuarios ua ON ua.id = m.id_usuario_asignado
                {$joinEstado}
                {$joinUbicacion}
                {$where}
                ORDER BY m.id_monitor DESC";

        $stmt = mysqli_prepare($this->cn, $sql);
        if ($types !== '') {
            $this->bindParams($stmt, $types, $params);
        }
        mysqli_stmt_execute($stmt);
        $rs    = mysqli_stmt_get_result($stmt);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($rs)) {
            $fila['badge_estado'] = '<span class="badge text-bg-' .
                htmlspecialchars($fila['color_badge'] ?? 'dark', ENT_QUOTES, 'UTF-8') . '">' .
                htmlspecialchars($fila['nombre_estado'] ?? '', ENT_QUOTES, 'UTF-8') . '</span>';
            $datos[] = $fila;
        }
        mysqli_stmt_close($stmt);
        return $datos;
    }

    public function obtenerMonitorPorId(int $idMonitor): ?array
    {
        if (!$this->tablaExiste('monitores')) {
            return null;
        }
        $conUbicacion  = $this->tablaExiste('equipo_ubicacion');
        $conEstado     = $this->tablaExiste('estado_equipo');
        $joinEstado    = $conEstado ? "LEFT JOIN estado_equipo ee ON ee.id_estado = m.id_estado" : '';
        $selectEstado  = $conEstado
            ? "COALESCE(ee.nombre_estado, 'Sin estado') AS nombre_estado, COALESCE(ee.color_badge, 'dark') AS color_badge"
            : "'Sin estado' AS nombre_estado, 'dark' AS color_badge";
        $joinUbicacion   = $conUbicacion ? "LEFT JOIN equipo_ubicacion eu ON eu.id_ubicacion = m.id_ubicacion" : '';
        $selectUbicacion = $conUbicacion ? "eu.nombre_ubicacion, eu.tipo_ubicacion" : "NULL AS nombre_ubicacion, NULL AS tipo_ubicacion";

        $stmt = mysqli_prepare($this->cn, "
            SELECT m.*,
                c.nom_colegio,
                CONCAT(ua.nombre, ' ', ua.apellido_paterno) AS usuario_asignado,
                CONCAT(ur.nombre, ' ', ur.apellido_paterno) AS nombre_usuario_registra,
                {$selectEstado},
                {$selectUbicacion}
            FROM monitores m
            INNER JOIN colegio c ON c.id_colegio = m.id_colegio
            LEFT JOIN usuarios ua ON ua.id = m.id_usuario_asignado
            LEFT JOIN usuarios ur ON ur.id = m.id_usuario_registra
            {$joinEstado}
            {$joinUbicacion}
            WHERE m.id_monitor = ?
        ");
        mysqli_stmt_bind_param($stmt, 'i', $idMonitor);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$fila) {
            return null;
        }

        $fila['compra']      = $this->obtenerCompraMonitor($idMonitor);
        $fila['fotos']       = $this->obtenerFotosMonitor($idMonitor);
        $fila['movimientos'] = $this->obtenerMovimientosMonitor($idMonitor);
        return $fila;
    }

    public function guardarMonitor(array $post, $files, int $idUsuario, int $idColegio): int
    {
        $payload = $this->normalizarPayloadMonitor($post, $idUsuario, $idColegio);

        mysqli_begin_transaction($this->cn);
        try {
            $idMonitor = $this->insertarMonitor($payload);
            $this->insertarCompraMonitor($idMonitor, $payload['compra']);
            $this->guardarFotosMonitor($idMonitor, $files);

            if ($payload['id_ubicacion'] > 0) {
                $this->registrarMovimientoMonitor(
                    $idMonitor, 0, $payload['id_ubicacion'], $idUsuario,
                    'Alta inicial de inventario',
                    'Monitor registrado inicialmente en esta ubicación.'
                );
            }
            mysqli_commit($this->cn);
            return $idMonitor;
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    public function actualizarMonitor(int $idMonitor, array $post, $files, int $idUsuario): bool
    {
        $actual = $this->obtenerCamposBasicosMonitor($idMonitor);
        if (!$actual) {
            throw new RuntimeException('El monitor indicado no existe.');
        }
        $idColegio           = (int)$actual['id_colegio'];
        $idUbicacionAnterior = (int)($actual['id_ubicacion'] ?? 0);
        $payload             = $this->normalizarPayloadMonitor($post, $idUsuario, $idColegio);
        $idUbicacionNueva    = (int)$payload['id_ubicacion'];
        $obsMovimiento       = trim((string)($post['observacion_movimiento'] ?? ''));

        mysqli_begin_transaction($this->cn);
        try {
            $sql = "UPDATE monitores SET
                        id_ubicacion        = NULLIF(?, 0),
                        id_usuario_asignado = NULLIF(?, 0),
                        id_estado           = ?,
                        nombre_monitor      = ?,
                        marca               = ?,
                        modelo              = ?,
                        numero_serie        = ?,
                        codigo_interno      = ?,
                        tamano_monitor      = ?,
                        resolucion_monitor  = ?,
                        tipo_panel          = ?,
                        tipo_conexion       = ?,
                        observacion         = ?
                    WHERE id_monitor = ?";
            $stmt = mysqli_prepare($this->cn, $sql);
            $this->bindParams($stmt, 'iiissssssssssi', [
                $idUbicacionNueva,
                $payload['id_usuario_asignado'],
                $payload['id_estado'],
                $payload['nombre_monitor'],
                $payload['marca'],
                $payload['modelo'],
                $payload['numero_serie'],
                $payload['codigo_interno'],
                $payload['tamano_monitor'],
                $payload['resolucion_monitor'],
                $payload['tipo_panel'],
                $payload['tipo_conexion'],
                $payload['observacion'],
                $idMonitor,
            ]);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($idUbicacionNueva > 0 && $idUbicacionNueva !== $idUbicacionAnterior) {
                $this->registrarMovimientoMonitor(
                    $idMonitor, $idUbicacionAnterior, $idUbicacionNueva, $idUsuario,
                    'Cambio de ubicación', $obsMovimiento
                );
            }

            $this->insertarCompraMonitor($idMonitor, $payload['compra']);
            $this->eliminarFotosMonitor($idMonitor, $post['fotos_eliminar'] ?? []);
            $this->procesarFotoPrincipalMonitor($idMonitor, $post);
            $this->guardarFotosMonitor($idMonitor, $files);

            mysqli_commit($this->cn);
            return true;
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    public function eliminarMonitor(int $idMonitor): bool
    {
        if (!$this->tablaExiste('monitores')) {
            throw new RuntimeException('Tabla monitores no existe.');
        }
        if ($this->columnaExiste('monitores', 'eliminado')) {
            $stmt = mysqli_prepare($this->cn,
                "UPDATE monitores SET eliminado = 1 WHERE id_monitor = ?");
        } else {
            $stmt = mysqli_prepare($this->cn,
                "UPDATE monitores SET id_estado = 4 WHERE id_monitor = ?");
        }
        mysqli_stmt_bind_param($stmt, 'i', $idMonitor);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function liberarEquipo(int $idEquipo, int $idUsuarioAccion): void
    {
        $stmt = mysqli_prepare($this->cn,
            "SELECT id_equipo, id_usuario_asignado FROM equipos WHERE id_equipo = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
        mysqli_stmt_execute($stmt);
        $actual = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
        mysqli_stmt_close($stmt);

        if (!$actual) {
            throw new RuntimeException('El equipo indicado no existe.');
        }
        $idUsuarioAnterior = $actual['id_usuario_asignado'] !== null ? (int)$actual['id_usuario_asignado'] : null;
        if ($idUsuarioAnterior === null) {
            throw new RuntimeException('El equipo ya está sin asignar.');
        }

        mysqli_begin_transaction($this->cn);
        try {
            if ($this->tablaExiste('equipo_asignacion_historial')) {
                $stmt = mysqli_prepare($this->cn,
                    "INSERT INTO equipo_asignacion_historial
                     (id_equipo, id_usuario_anterior, id_usuario_nuevo, id_usuario_accion, motivo, observacion)
                     VALUES (?, ?, NULL, ?, 'Liberación de equipo', 'Equipo desvinculado del usuario asignado y dejado disponible.')");
                mysqli_stmt_bind_param($stmt, 'iii', $idEquipo, $idUsuarioAnterior, $idUsuarioAccion);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            $stmt = mysqli_prepare($this->cn,
                "UPDATE equipos SET id_usuario_asignado = NULL WHERE id_equipo = ?");
            mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($this->cn);
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    public function liberarMonitor(int $idMonitor, int $idUsuarioAccion): void
    {
        $stmt = mysqli_prepare($this->cn,
            "SELECT id_monitor, id_usuario_asignado FROM monitores WHERE id_monitor = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $idMonitor);
        mysqli_stmt_execute($stmt);
        $actual = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
        mysqli_stmt_close($stmt);

        if (!$actual) {
            throw new RuntimeException('El monitor indicado no existe.');
        }
        $idUsuarioAnterior = $actual['id_usuario_asignado'] !== null ? (int)$actual['id_usuario_asignado'] : null;
        if ($idUsuarioAnterior === null) {
            throw new RuntimeException('El monitor ya está sin asignar.');
        }

        mysqli_begin_transaction($this->cn);
        try {
            if ($this->tablaExiste('monitor_asignacion_historial')) {
                $stmt = mysqli_prepare($this->cn,
                    "INSERT INTO monitor_asignacion_historial
                     (id_monitor, id_usuario_anterior, id_usuario_nuevo, id_usuario_accion, motivo, observacion)
                     VALUES (?, ?, NULL, ?, 'Liberación de monitor', 'Monitor desvinculado del usuario asignado y dejado disponible.')");
                mysqli_stmt_bind_param($stmt, 'iii', $idMonitor, $idUsuarioAnterior, $idUsuarioAccion);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            $stmt = mysqli_prepare($this->cn,
                "UPDATE monitores SET id_usuario_asignado = NULL WHERE id_monitor = ?");
            mysqli_stmt_bind_param($stmt, 'i', $idMonitor);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($this->cn);
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    public function guardarMonitorDesdeArray(array $payload, int $idUsuario): int
    {
        $idColegio   = (int)($payload['id_colegio']   ?? 0);
        $idUbicacion = (int)($payload['id_ubicacion'] ?? 0);
        $idEstado    = (int)($payload['id_estado']    ?? 0);
        $nombre      = trim((string)($payload['nombre_monitor'] ?? ''));
        $serie       = trim((string)($payload['numero_serie']   ?? ''));

        if ($idColegio <= 0) {
            throw new RuntimeException('No se pudo determinar el colegio del usuario.');
        }
        if ($nombre === '') {
            throw new RuntimeException('El nombre del monitor es obligatorio.');
        }
        if ($serie === '') {
            throw new RuntimeException('El número de serie es obligatorio.');
        }
        if ($idEstado <= 0) {
            throw new RuntimeException('El estado del monitor es obligatorio.');
        }
        if ($this->tablaExiste('equipo_ubicacion') && $idUbicacion <= 0) {
            throw new RuntimeException('La ubicación del monitor es obligatoria.');
        }

        if ($idUbicacion > 0 && $this->tablaExiste('equipo_ubicacion')) {
            $stmt = mysqli_prepare($this->cn,
                "SELECT 1 FROM equipo_ubicacion WHERE id_ubicacion = ? AND id_colegio = ? AND estado = 1 LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'ii', $idUbicacion, $idColegio);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $valida = mysqli_stmt_num_rows($stmt) > 0;
            mysqli_stmt_close($stmt);
            if (!$valida) {
                throw new RuntimeException('La ubicación indicada no pertenece al colegio del usuario.');
            }
        }

        // Verificar número de serie duplicado dentro del mismo colegio
        $conElim = $this->columnaExiste('monitores', 'eliminado');
        $condElim = $conElim ? ' AND (eliminado IS NULL OR eliminado = 0)' : '';
        $stmt = mysqli_prepare($this->cn,
            "SELECT 1 FROM monitores WHERE numero_serie = ? AND id_colegio = ?{$condElim} LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'si', $serie, $idColegio);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $duplicado = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        if ($duplicado) {
            throw new RuntimeException("Ya existe un monitor con número de serie «{$serie}» en este colegio.");
        }

        $compraRaw = $payload['compra'] ?? [];
        $p = [
            'id_colegio'          => $idColegio,
            'id_ubicacion'        => $idUbicacion,
            'id_usuario_asignado' => (int)($payload['id_usuario_asignado'] ?? 0),
            'id_usuario_registra' => $idUsuario,
            'id_estado'           => $idEstado,
            'nombre_monitor'      => $nombre,
            'marca'               => trim((string)($payload['marca']              ?? '')),
            'modelo'              => trim((string)($payload['modelo']             ?? '')),
            'numero_serie'        => $serie,
            'codigo_interno'      => trim((string)($payload['codigo_interno']     ?? '')),
            'tamano_monitor'      => trim((string)($payload['tamano_monitor']     ?? '')),
            'resolucion_monitor'  => trim((string)($payload['resolucion_monitor'] ?? '')),
            'tipo_panel'          => trim((string)($payload['tipo_panel']         ?? '')),
            'tipo_conexion'       => trim((string)($payload['tipo_conexion']      ?? '')),
            'observacion'         => trim((string)($payload['observacion']        ?? '')),
            'compra' => [
                'valor_monitor'  => (int)str_replace([',', '.'], '', (string)($compraRaw['valor_monitor'] ?? 0)),
                'proveedor'      => trim((string)($compraRaw['proveedor']      ?? '')),
                'numero_factura' => trim((string)($compraRaw['numero_factura'] ?? '')),
                'fecha_compra'   => trim((string)($compraRaw['fecha_compra']   ?? '')),
                'observacion'    => trim((string)($compraRaw['observacion']    ?? '')),
            ],
        ];

        mysqli_begin_transaction($this->cn);
        try {
            $idMonitor = $this->insertarMonitor($p);
            $this->insertarCompraMonitor($idMonitor, $p['compra']);
            if ($idUbicacion > 0) {
                $this->registrarMovimientoMonitor(
                    $idMonitor, 0, $idUbicacion, $idUsuario,
                    'Alta inicial de inventario',
                    'Monitor registrado mediante carga masiva.'
                );
            }
            mysqli_commit($this->cn);
            return $idMonitor;
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    public function obtenerResumenMonitores(array $filtros = []): array
    {
        if (!$this->tablaExiste('monitores')) {
            return [];
        }
        $conEliminado = $this->columnaExiste('monitores', 'eliminado');
        $condiciones  = $conEliminado ? ['m.eliminado = 0'] : [];
        $types        = '';
        $params       = [];

        if (!empty($filtros['id_colegio'])) {
            $condiciones[] = 'm.id_colegio = ?';
            $types .= 'i';
            $params[] = (int)$filtros['id_colegio'];
        }
        $where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';

        $sql = "SELECT
                    COUNT(*) AS total_monitores,
                    SUM(CASE WHEN m.id_usuario_asignado IS NULL OR m.id_usuario_asignado = 0 THEN 1 ELSE 0 END) AS total_sin_asignar,
                    SUM(CASE WHEN m.id_estado = 1 THEN 1 ELSE 0 END) AS total_activos,
                    SUM(CASE WHEN m.id_estado = 2 THEN 1 ELSE 0 END) AS total_bodega,
                    SUM(CASE WHEN m.id_estado = 3 THEN 1 ELSE 0 END) AS total_reparacion,
                    SUM(CASE WHEN m.id_estado = 4 THEN 1 ELSE 0 END) AS total_baja
                FROM monitores m {$where}";

        $stmt = mysqli_prepare($this->cn, $sql);
        if ($types !== '') {
            $this->bindParams($stmt, $types, $params);
        }
        mysqli_stmt_execute($stmt);
        $resumen = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
        mysqli_stmt_close($stmt);
        return $resumen;
    }
}
