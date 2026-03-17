<?php

class Inventario
{
    private $db;
    private $cn;
    private $cacheTablas = [];
    private $estadosFallback = [
        1 => ['nombre_estado' => 'Disponible', 'color_badge' => 'success'],
        2 => ['nombre_estado' => 'En bodega', 'color_badge' => 'secondary'],
        3 => ['nombre_estado' => 'En reparacion', 'color_badge' => 'warning'],
        4 => ['nombre_estado' => 'Baja', 'color_badge' => 'danger'],
        5 => ['nombre_estado' => 'Prestada', 'color_badge' => 'info'],
        6 => ['nombre_estado' => 'Extraviada', 'color_badge' => 'dark'],
    ];
    private $categoriasFallback = ['Manual', 'Electrica', 'Medicion', 'Seguridad', 'Mantencion', 'Jardineria', 'Limpieza', 'Otro'];

    public function __construct()
    {
        $this->db = new MySQL('', '', '');
        $this->cn = $this->obtenerConexionSistema($this->db);
    }

    private function obtenerConexionSistema($db)
    {
        if (!class_exists('MySQL')) {
            throw new RuntimeException('No se encontro la clase MySQL del sistema.');
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
        $sql = "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '{$tablaSegura}' LIMIT 1";
        $rs = $this->db->consulta($sql);
        $this->cacheTablas[$tabla] = $this->db->num_rows($rs) > 0;
        return $this->cacheTablas[$tabla];
    }

    public function obtenerEstadoInstalacion()
    {
        $tablas = ['herramientas', 'herramienta_detalle', 'herramienta_compra', 'herramienta_prestamos', 'herramienta_mantenciones', 'herramienta_fotos', 'herramienta_historial'];
        $faltantes = [];
        foreach ($tablas as $tabla) {
            if (!$this->tablaExiste($tabla)) {
                $faltantes[] = $tabla;
            }
        }
        return ['instalado' => empty($faltantes), 'faltantes' => $faltantes];
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

    public function obtenerUsuarios()
    {
        $datos = [];
        $sql = "SELECT id, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) AS nombre_completo FROM usuarios ORDER BY nombre ASC, apellido_paterno ASC";
        $rs = $this->db->consulta($sql);
        while ($fila = $this->db->fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function obtenerEstados()
    {
        if ($this->tablaExiste('estado_herramienta')) {
            $datos = [];
            $rs = $this->db->consulta("SELECT id_estado, nombre_estado, color_badge FROM estado_herramienta ORDER BY id_estado ASC");
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

    public function obtenerCategorias()
    {
        if ($this->tablaExiste('categoria_herramienta_catalogo')) {
            $datos = [];
            $rs = $this->db->consulta("SELECT nombre_categoria FROM categoria_herramienta_catalogo WHERE activo = 1 ORDER BY nombre_categoria ASC");
            while ($fila = $this->db->fetch_assoc($rs)) {
                $datos[] = $fila['nombre_categoria'];
            }
            if (!empty($datos)) {
                return $datos;
            }
        }
        return $this->categoriasFallback;
    }

    public function obtenerResumen($filtros = [])
    {
        $vacio = ['total_herramientas' => 0, 'total_cantidad' => 0, 'total_disponibles' => 0, 'total_prestadas' => 0, 'total_reparacion' => 0, 'total_baja' => 0, 'total_bajo_stock' => 0, 'total_mantencion_vencida' => 0, 'por_colegio' => []];
        if (!$this->tablaExiste('herramientas')) {
            return $vacio;
        }

        $where = $this->construirWhere($filtros);
        $sql = "SELECT COUNT(*) AS total_herramientas, COALESCE(SUM(h.cantidad), 0) AS total_cantidad, SUM(CASE WHEN h.id_estado = 1 THEN 1 ELSE 0 END) AS total_disponibles, SUM(CASE WHEN h.id_estado = 5 THEN 1 ELSE 0 END) AS total_prestadas, SUM(CASE WHEN h.id_estado = 3 THEN 1 ELSE 0 END) AS total_reparacion, SUM(CASE WHEN h.id_estado = 4 THEN 1 ELSE 0 END) AS total_baja, SUM(CASE WHEN h.stock_minimo > 0 AND h.cantidad <= h.stock_minimo THEN 1 ELSE 0 END) AS total_bajo_stock, SUM(CASE WHEN hd.fecha_proxima_mantencion IS NOT NULL AND hd.fecha_proxima_mantencion <> '' AND hd.fecha_proxima_mantencion <= CURDATE() THEN 1 ELSE 0 END) AS total_mantencion_vencida FROM herramientas h LEFT JOIN herramienta_detalle hd ON hd.id_herramienta = h.id_herramienta {$where['sql']}";
        $stmt = mysqli_prepare($this->cn, $sql);
        $this->bindParams($stmt, $where['types'], $where['params']);
        mysqli_stmt_execute($stmt);
        $resumen = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: $vacio;
        mysqli_stmt_close($stmt);

        $resumen['por_colegio'] = [];
        $sqlColegios = "SELECT c.nom_colegio, COUNT(*) AS total, COALESCE(SUM(h.cantidad), 0) AS cantidad FROM herramientas h INNER JOIN colegio c ON c.id_colegio = h.id_colegio LEFT JOIN herramienta_detalle hd ON hd.id_herramienta = h.id_herramienta {$where['sql']} GROUP BY h.id_colegio, c.nom_colegio ORDER BY cantidad DESC, c.nom_colegio ASC";
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

    public function listarHerramientas($filtros = [])
    {
        if (!$this->tablaExiste('herramientas')) {
            return [];
        }

        $where = $this->construirWhere($filtros);
        $joinEstado = $this->tablaExiste('estado_herramienta') ? "LEFT JOIN estado_herramienta eh ON eh.id_estado = h.id_estado" : '';
        $selectEstado = $this->tablaExiste('estado_herramienta') ? "COALESCE(eh.nombre_estado, 'Sin estado') AS nombre_estado, COALESCE(eh.color_badge, 'dark') AS color_badge" : "CASE h.id_estado WHEN 1 THEN 'Disponible' WHEN 2 THEN 'En bodega' WHEN 3 THEN 'En reparacion' WHEN 4 THEN 'Baja' WHEN 5 THEN 'Prestada' WHEN 6 THEN 'Extraviada' ELSE 'Sin estado' END AS nombre_estado, CASE h.id_estado WHEN 1 THEN 'success' WHEN 2 THEN 'secondary' WHEN 3 THEN 'warning' WHEN 4 THEN 'danger' WHEN 5 THEN 'info' WHEN 6 THEN 'dark' ELSE 'dark' END AS color_badge";
        $sql = "SELECT h.id_herramienta, h.nombre_herramienta, h.marca, h.modelo, h.numero_serie, h.categoria, h.qr_code, h.cantidad, h.stock_minimo, h.ubicacion, h.id_estado, c.nom_colegio, CONCAT(ua.nombre, ' ', ua.apellido_paterno, ' ', ua.apellido_materno) AS usuario_asignado, {$selectEstado} FROM herramientas h INNER JOIN colegio c ON c.id_colegio = h.id_colegio LEFT JOIN usuarios ua ON ua.id = h.id_usuario_asignado {$joinEstado} {$where['sql']} ORDER BY h.id_herramienta DESC";
        $stmt = mysqli_prepare($this->cn, $sql);
        $this->bindParams($stmt, $where['types'], $where['params']);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($rs)) {
            $fila['badge_estado'] = '<span class="badge text-bg-' . htmlspecialchars($fila['color_badge'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($fila['nombre_estado'], ENT_QUOTES, 'UTF-8') . '</span>';
            $datos[] = $fila;
        }
        mysqli_stmt_close($stmt);
        return $datos;
    }
    public function obtenerHerramientaCompleta($idHerramienta)
    {
        $idHerramienta = (int)$idHerramienta;
        if ($idHerramienta <= 0 || !$this->tablaExiste('herramientas')) {
            return null;
        }

        $joinEstado = $this->tablaExiste('estado_herramienta') ? "LEFT JOIN estado_herramienta eh ON eh.id_estado = h.id_estado" : '';
        $selectEstado = $this->tablaExiste('estado_herramienta') ? "COALESCE(eh.nombre_estado, 'Sin estado') AS nombre_estado, COALESCE(eh.color_badge, 'dark') AS color_badge" : "CASE h.id_estado WHEN 1 THEN 'Disponible' WHEN 2 THEN 'En bodega' WHEN 3 THEN 'En reparacion' WHEN 4 THEN 'Baja' WHEN 5 THEN 'Prestada' WHEN 6 THEN 'Extraviada' ELSE 'Sin estado' END AS nombre_estado, CASE h.id_estado WHEN 1 THEN 'success' WHEN 2 THEN 'secondary' WHEN 3 THEN 'warning' WHEN 4 THEN 'danger' WHEN 5 THEN 'info' WHEN 6 THEN 'dark' ELSE 'dark' END AS color_badge";
        $sql = "SELECT h.*, c.nom_colegio, CONCAT(ur.nombre, ' ', ur.apellido_paterno, ' ', ur.apellido_materno) AS usuario_registra, CONCAT(ua.nombre, ' ', ua.apellido_paterno, ' ', ua.apellido_materno) AS usuario_asignado, {$selectEstado} FROM herramientas h INNER JOIN colegio c ON c.id_colegio = h.id_colegio LEFT JOIN usuarios ur ON ur.id = h.id_usuario LEFT JOIN usuarios ua ON ua.id = h.id_usuario_asignado {$joinEstado} WHERE h.id_herramienta = ?";
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idHerramienta);
        mysqli_stmt_execute($stmt);
        $herramienta = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        if (!$herramienta) {
            return null;
        }

        $herramienta['detalle'] = $this->tablaExiste('herramienta_detalle') ? $this->obtenerFilaSimple('SELECT * FROM herramienta_detalle WHERE id_herramienta = ?', $idHerramienta) : [];
        $herramienta['compra'] = $this->tablaExiste('herramienta_compra') ? $this->obtenerFilaSimple('SELECT * FROM herramienta_compra WHERE id_herramienta = ?', $idHerramienta) : [];
        $herramienta['prestamos'] = $this->tablaExiste('herramienta_prestamos') ? $this->obtenerVariasFilas('SELECT * FROM herramienta_prestamos WHERE id_herramienta = ? ORDER BY fecha_salida DESC, id_prestamo DESC', $idHerramienta) : [];
        $herramienta['mantenciones'] = $this->tablaExiste('herramienta_mantenciones') ? $this->obtenerVariasFilas('SELECT * FROM herramienta_mantenciones WHERE id_herramienta = ? ORDER BY fecha_mantencion DESC, id_mantencion DESC', $idHerramienta) : [];
        $herramienta['fotos'] = $this->tablaExiste('herramienta_fotos') ? $this->obtenerVariasFilas('SELECT * FROM herramienta_fotos WHERE id_herramienta = ? ORDER BY principal DESC, orden_foto ASC, id_foto ASC', $idHerramienta) : [];
        return $herramienta;
    }

    public function validarSerieDuplicada($numeroSerie, $idExcluir = 0)
    {
        if (!$this->tablaExiste('herramientas')) {
            return false;
        }
        $numeroSerie = trim((string)$numeroSerie);
        if ($numeroSerie === '') {
            return false;
        }

        $sql = 'SELECT id_herramienta FROM herramientas WHERE numero_serie = ?';
        if ($idExcluir > 0) {
            $sql .= ' AND id_herramienta <> ?';
        }
        $stmt = mysqli_prepare($this->cn, $sql);
        if ($idExcluir > 0) {
            mysqli_stmt_bind_param($stmt, 'si', $numeroSerie, $idExcluir);
        } else {
            mysqli_stmt_bind_param($stmt, 's', $numeroSerie);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $duplicado = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        return $duplicado;
    }

    public function guardarHerramienta($post, $files, $idUsuario)
    {
        $payload = $this->normalizarPayload($post, 0, $idUsuario);
        if ($this->validarSerieDuplicada($payload['herramienta']['numero_serie'])) {
            throw new RuntimeException('El numero de serie ya existe en otra herramienta.');
        }

        mysqli_begin_transaction($this->cn);
        try {
            $sql = "INSERT INTO herramientas (id_usuario, id_colegio, id_usuario_asignado, nombre_herramienta, marca, modelo, numero_serie, categoria, qr_code, id_estado, cantidad, stock_minimo, ubicacion, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($this->cn, $sql);
            mysqli_stmt_bind_param($stmt, 'iiissssssiiiss', $payload['herramienta']['id_usuario'], $payload['herramienta']['id_colegio'], $payload['herramienta']['id_usuario_asignado'], $payload['herramienta']['nombre_herramienta'], $payload['herramienta']['marca'], $payload['herramienta']['modelo'], $payload['herramienta']['numero_serie'], $payload['herramienta']['categoria'], $payload['herramienta']['qr_code'], $payload['herramienta']['id_estado'], $payload['herramienta']['cantidad'], $payload['herramienta']['stock_minimo'], $payload['herramienta']['ubicacion'], $payload['herramienta']['observaciones']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $idHerramienta = mysqli_insert_id($this->cn);
            $this->guardarRelacionados($idHerramienta, $payload);
            $this->guardarFotos($idHerramienta, $files);
            $this->registrarHistorial($idHerramienta, 'creacion', 'Herramienta registrada en inventario.', $idUsuario);
            mysqli_commit($this->cn);
            return $idHerramienta;
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    public function actualizarHerramienta($idHerramienta, $post, $files, $idUsuario)
    {
        $idHerramienta = (int)$idHerramienta;
        $payload = $this->normalizarPayload($post, $idHerramienta, $idUsuario);
        if ($this->validarSerieDuplicada($payload['herramienta']['numero_serie'], $idHerramienta)) {
            throw new RuntimeException('El numero de serie ya existe en otra herramienta.');
        }

        mysqli_begin_transaction($this->cn);
        try {
            $sql = "UPDATE herramientas SET id_colegio = ?, id_usuario_asignado = ?, nombre_herramienta = ?, marca = ?, modelo = ?, numero_serie = ?, categoria = ?, qr_code = ?, id_estado = ?, cantidad = ?, stock_minimo = ?, ubicacion = ?, observaciones = ? WHERE id_herramienta = ?";
            $stmt = mysqli_prepare($this->cn, $sql);
            mysqli_stmt_bind_param($stmt, 'iissssssiiissi', $payload['herramienta']['id_colegio'], $payload['herramienta']['id_usuario_asignado'], $payload['herramienta']['nombre_herramienta'], $payload['herramienta']['marca'], $payload['herramienta']['modelo'], $payload['herramienta']['numero_serie'], $payload['herramienta']['categoria'], $payload['herramienta']['qr_code'], $payload['herramienta']['id_estado'], $payload['herramienta']['cantidad'], $payload['herramienta']['stock_minimo'], $payload['herramienta']['ubicacion'], $payload['herramienta']['observaciones'], $idHerramienta);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $this->limpiarRelacionados($idHerramienta);
            $this->guardarRelacionados($idHerramienta, $payload);
            $this->procesarEliminacionFotos($idHerramienta, $post);
            $this->guardarFotos($idHerramienta, $files);
            $this->registrarHistorial($idHerramienta, 'actualizacion', 'Herramienta actualizada desde el modulo de inventario.', $idUsuario);
            mysqli_commit($this->cn);
            return true;
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    public function cambiarEstadoLogico($idHerramienta, $nuevoEstado, $idUsuario)
    {
        $stmt = mysqli_prepare($this->cn, 'UPDATE herramientas SET id_estado = ? WHERE id_herramienta = ?');
        mysqli_stmt_bind_param($stmt, 'ii', $nuevoEstado, $idHerramienta);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $this->registrarHistorial((int)$idHerramienta, 'estado', 'Cambio logico de estado de la herramienta.', (int)$idUsuario);
        return true;
    }

    public function renderBadgeEstado($idEstado, $nombreEstado = '', $colorBadge = '')
    {
        if ($nombreEstado === '' || $colorBadge === '') {
            $meta = $this->estadosFallback[(int)$idEstado] ?? ['nombre_estado' => 'Sin estado', 'color_badge' => 'dark'];
            $nombreEstado = $nombreEstado !== '' ? $nombreEstado : $meta['nombre_estado'];
            $colorBadge = $colorBadge !== '' ? $colorBadge : $meta['color_badge'];
        }
        return '<span class="badge text-bg-' . htmlspecialchars($colorBadge, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($nombreEstado, ENT_QUOTES, 'UTF-8') . '</span>';
    }

    public function obtenerSqlSugerido()
    {
        return <<<'SQL'
CREATE TABLE IF NOT EXISTS estado_herramienta (
    id_estado INT PRIMARY KEY,
    nombre_estado VARCHAR(60) NOT NULL,
    color_badge VARCHAR(20) NOT NULL DEFAULT 'secondary'
);

INSERT INTO estado_herramienta (id_estado, nombre_estado, color_badge) VALUES
(1, 'Disponible', 'success'),
(2, 'En bodega', 'secondary'),
(3, 'En reparacion', 'warning'),
(4, 'Baja', 'danger'),
(5, 'Prestada', 'info'),
(6, 'Extraviada', 'dark')
ON DUPLICATE KEY UPDATE nombre_estado = VALUES(nombre_estado), color_badge = VALUES(color_badge);

CREATE TABLE IF NOT EXISTS categoria_herramienta_catalogo (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(80) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1
);

INSERT INTO categoria_herramienta_catalogo (nombre_categoria, activo) VALUES
('Manual', 1),
('Electrica', 1),
('Medicion', 1),
('Seguridad', 1),
('Mantencion', 1),
('Jardineria', 1),
('Limpieza', 1),
('Otro', 1)
ON DUPLICATE KEY UPDATE
    activo = VALUES(activo);

CREATE TABLE IF NOT EXISTS herramientas (
    id_herramienta INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_colegio INT NOT NULL,
    id_usuario_asignado INT NULL,
    nombre_herramienta VARCHAR(150) NOT NULL,
    marca VARCHAR(120) DEFAULT '',
    modelo VARCHAR(120) DEFAULT '',
    numero_serie VARCHAR(120) NOT NULL,
    categoria VARCHAR(80) NOT NULL,
    qr_code VARCHAR(120) NOT NULL,
    id_estado INT NOT NULL DEFAULT 1,
    cantidad INT NOT NULL DEFAULT 1,
    stock_minimo INT NOT NULL DEFAULT 0,
    ubicacion VARCHAR(150) DEFAULT '',
    observaciones TEXT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_herramientas_numero_serie (numero_serie),
    UNIQUE KEY uq_herramientas_qr_code (qr_code),
    KEY idx_herramientas_colegio (id_colegio),
    KEY idx_herramientas_usuario (id_usuario),
    KEY idx_herramientas_usuario_asignado (id_usuario_asignado),
    KEY idx_herramientas_estado (id_estado),
    KEY idx_herramientas_categoria (categoria),
    CONSTRAINT fk_herramientas_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    CONSTRAINT fk_herramientas_colegio FOREIGN KEY (id_colegio) REFERENCES colegio(id_colegio),
    CONSTRAINT fk_herramientas_asignado FOREIGN KEY (id_usuario_asignado) REFERENCES usuarios(id),
    CONSTRAINT fk_herramientas_estado FOREIGN KEY (id_estado) REFERENCES estado_herramienta(id_estado)
);

CREATE TABLE IF NOT EXISTS herramienta_detalle (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_herramienta INT NOT NULL,
    tipo_energia VARCHAR(60) DEFAULT '',
    medida VARCHAR(80) DEFAULT '',
    capacidad VARCHAR(80) DEFAULT '',
    requiere_mantencion TINYINT(1) NOT NULL DEFAULT 0,
    frecuencia_mantencion_dias INT NOT NULL DEFAULT 0,
    fecha_ultima_mantencion DATE NULL,
    fecha_proxima_mantencion DATE NULL,
    garantia_hasta DATE NULL,
    UNIQUE KEY uq_herramienta_detalle_herramienta (id_herramienta),
    CONSTRAINT fk_herramienta_detalle FOREIGN KEY (id_herramienta) REFERENCES herramientas(id_herramienta) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS herramienta_compra (
    id_compra INT AUTO_INCREMENT PRIMARY KEY,
    id_herramienta INT NOT NULL,
    valor_herramienta DECIMAL(12,2) NOT NULL DEFAULT 0,
    proveedor VARCHAR(150) DEFAULT '',
    numero_factura VARCHAR(120) DEFAULT '',
    fecha_compra DATE NULL,
    observacion TEXT NULL,
    UNIQUE KEY uq_herramienta_compra_herramienta (id_herramienta),
    KEY idx_herramienta_compra_fecha (fecha_compra),
    CONSTRAINT fk_herramienta_compra FOREIGN KEY (id_herramienta) REFERENCES herramientas(id_herramienta) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS herramienta_prestamos (
    id_prestamo INT AUTO_INCREMENT PRIMARY KEY,
    id_herramienta INT NOT NULL,
    responsable_entrega VARCHAR(150) DEFAULT '',
    responsable_recibe VARCHAR(150) DEFAULT '',
    fecha_salida DATE NULL,
    fecha_devolucion_prevista DATE NULL,
    fecha_devolucion_real DATE NULL,
    estado_prestamo VARCHAR(60) DEFAULT '',
    observacion TEXT NULL,
    KEY idx_herramienta_prestamos_herramienta (id_herramienta),
    KEY idx_herramienta_prestamos_salida (fecha_salida),
    KEY idx_herramienta_prestamos_estado (estado_prestamo),
    CONSTRAINT fk_herramienta_prestamos FOREIGN KEY (id_herramienta) REFERENCES herramientas(id_herramienta) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS herramienta_mantenciones (
    id_mantencion INT AUTO_INCREMENT PRIMARY KEY,
    id_herramienta INT NOT NULL,
    fecha_mantencion DATE NULL,
    tipo_mantencion VARCHAR(120) DEFAULT '',
    proveedor VARCHAR(150) DEFAULT '',
    costo DECIMAL(12,2) NOT NULL DEFAULT 0,
    proxima_fecha DATE NULL,
    observacion TEXT NULL,
    KEY idx_herramienta_mantenciones_herramienta (id_herramienta),
    KEY idx_herramienta_mantenciones_fecha (fecha_mantencion),
    KEY idx_herramienta_mantenciones_proxima (proxima_fecha),
    CONSTRAINT fk_herramienta_mantenciones FOREIGN KEY (id_herramienta) REFERENCES herramientas(id_herramienta) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS herramienta_fotos (
    id_foto INT AUTO_INCREMENT PRIMARY KEY,
    id_herramienta INT NOT NULL,
    ruta_foto VARCHAR(255) NOT NULL,
    principal TINYINT(1) NOT NULL DEFAULT 0,
    orden_foto INT NOT NULL DEFAULT 1,
    fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_herramienta_fotos_herramienta (id_herramienta),
    KEY idx_herramienta_fotos_principal (id_herramienta, principal),
    CONSTRAINT fk_herramienta_fotos FOREIGN KEY (id_herramienta) REFERENCES herramientas(id_herramienta) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS herramienta_historial (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_herramienta INT NOT NULL,
    accion VARCHAR(60) NOT NULL,
    descripcion TEXT NOT NULL,
    id_usuario INT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_herramienta_historial_herramienta (id_herramienta),
    KEY idx_herramienta_historial_usuario (id_usuario),
    KEY idx_herramienta_historial_fecha (fecha),
    CONSTRAINT fk_herramienta_historial FOREIGN KEY (id_herramienta) REFERENCES herramientas(id_herramienta) ON DELETE CASCADE,
    CONSTRAINT fk_herramienta_historial_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);
SQL;
    }
    private function construirWhere($filtros)
    {
        $condiciones = [];
        $types = '';
        $params = [];
        if (!empty($filtros['id_colegio'])) { $condiciones[] = 'h.id_colegio = ?'; $types .= 'i'; $params[] = (int)$filtros['id_colegio']; }
        if (!empty($filtros['id_estado'])) { $condiciones[] = 'h.id_estado = ?'; $types .= 'i'; $params[] = (int)$filtros['id_estado']; }
        if (!empty($filtros['categoria'])) { $condiciones[] = 'h.categoria = ?'; $types .= 's'; $params[] = trim((string)$filtros['categoria']); }
        if (!empty($filtros['id_usuario_asignado'])) { $condiciones[] = 'h.id_usuario_asignado = ?'; $types .= 'i'; $params[] = (int)$filtros['id_usuario_asignado']; }
        if (!empty($filtros['busqueda'])) {
            $busqueda = '%' . trim((string)$filtros['busqueda']) . '%';
            $condiciones[] = '(h.nombre_herramienta LIKE ? OR h.numero_serie LIKE ? OR h.marca LIKE ? OR h.modelo LIKE ? OR h.qr_code LIKE ? OR h.ubicacion LIKE ?)';
            $types .= 'ssssss';
            $params[] = $busqueda; $params[] = $busqueda; $params[] = $busqueda; $params[] = $busqueda; $params[] = $busqueda; $params[] = $busqueda;
        }
        return ['sql' => count($condiciones) ? 'WHERE ' . implode(' AND ', $condiciones) : '', 'types' => $types, 'params' => $params];
    }

    private function bindParams($stmt, $types, $params)
    {
        if ($types === '' || empty($params)) { return; }
        $refs = [];
        foreach ($params as $k => $valor) { $refs[$k] = &$params[$k]; }
        array_unshift($refs, $types);
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }

    private function obtenerFilaSimple($sql, $idHerramienta)
    {
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idHerramienta);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
        mysqli_stmt_close($stmt);
        return $fila;
    }

    private function obtenerVariasFilas($sql, $idHerramienta)
    {
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idHerramienta);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $filas = [];
        while ($fila = mysqli_fetch_assoc($rs)) { $filas[] = $fila; }
        mysqli_stmt_close($stmt);
        return $filas;
    }

    private function normalizarPayload($post, $idHerramienta, $idUsuario)
    {
        $herramienta = [
            'id_usuario' => (int)$idUsuario,
            'id_colegio' => (int)($post['id_colegio'] ?? 0),
            'id_usuario_asignado' => (int)($post['id_usuario_asignado'] ?? 0),
            'nombre_herramienta' => trim((string)($post['nombre_herramienta'] ?? '')),
            'marca' => trim((string)($post['marca'] ?? '')),
            'modelo' => trim((string)($post['modelo'] ?? '')),
            'numero_serie' => trim((string)($post['numero_serie'] ?? '')),
            'categoria' => trim((string)($post['categoria'] ?? '')),
            'qr_code' => trim((string)($post['qr_code'] ?? '')),
            'id_estado' => (int)($post['id_estado'] ?? 1),
            'cantidad' => max(1, (int)($post['cantidad'] ?? 1)),
            'stock_minimo' => max(0, (int)($post['stock_minimo'] ?? 0)),
            'ubicacion' => trim((string)($post['ubicacion'] ?? '')),
            'observaciones' => trim((string)($post['observaciones'] ?? '')),
        ];
        if ($herramienta['id_colegio'] <= 0 || $herramienta['nombre_herramienta'] === '' || $herramienta['numero_serie'] === '' || $herramienta['categoria'] === '') {
            throw new RuntimeException('Debes completar colegio, nombre de herramienta, serie y categoria.');
        }
        if ($herramienta['qr_code'] === '') {
            $base = $herramienta['numero_serie'] !== '' ? $herramienta['numero_serie'] : ('HR-' . $idHerramienta);
            $herramienta['qr_code'] = 'HER-' . preg_replace('/[^A-Za-z0-9\-]/', '', strtoupper($base));
        }

        return [
            'herramienta' => $herramienta,
            'detalle' => ['tipo_energia' => trim((string)($post['tipo_energia'] ?? '')), 'medida' => trim((string)($post['medida'] ?? '')), 'capacidad' => trim((string)($post['capacidad'] ?? '')), 'requiere_mantencion' => !empty($post['requiere_mantencion']) ? 1 : 0, 'frecuencia_mantencion_dias' => max(0, (int)($post['frecuencia_mantencion_dias'] ?? 0)), 'fecha_ultima_mantencion' => trim((string)($post['fecha_ultima_mantencion'] ?? '')), 'fecha_proxima_mantencion' => trim((string)($post['fecha_proxima_mantencion'] ?? '')), 'garantia_hasta' => trim((string)($post['garantia_hasta'] ?? ''))],
            'compra' => ['valor_herramienta' => (float)str_replace(',', '.', (string)($post['valor_herramienta'] ?? 0)), 'proveedor' => trim((string)($post['proveedor'] ?? '')), 'numero_factura' => trim((string)($post['numero_factura'] ?? '')), 'fecha_compra' => trim((string)($post['fecha_compra'] ?? '')), 'observacion' => trim((string)($post['observacion_compra'] ?? ''))],
            'prestamos' => $this->normalizarFilas($post['prestamo'] ?? []),
            'mantenciones' => $this->normalizarFilas($post['mantencion'] ?? []),
        ];
    }

    private function normalizarFilas($filas)
    {
        $resultado = [];
        foreach ($filas as $fila) {
            $limpia = [];
            foreach ($fila as $clave => $valor) { $limpia[$clave] = trim((string)$valor); }
            $tieneContenido = false;
            foreach ($limpia as $valor) { if ($valor !== '') { $tieneContenido = true; break; } }
            if ($tieneContenido) { $resultado[] = $limpia; }
        }
        return $resultado;
    }

    private function guardarRelacionados($idHerramienta, $payload)
    {
        if ($this->tablaExiste('herramienta_detalle')) {
            $stmt = mysqli_prepare($this->cn, 'INSERT INTO herramienta_detalle (id_herramienta, tipo_energia, medida, capacidad, requiere_mantencion, frecuencia_mantencion_dias, fecha_ultima_mantencion, fecha_proxima_mantencion, garantia_hasta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'isssiisss', $idHerramienta, $payload['detalle']['tipo_energia'], $payload['detalle']['medida'], $payload['detalle']['capacidad'], $payload['detalle']['requiere_mantencion'], $payload['detalle']['frecuencia_mantencion_dias'], $payload['detalle']['fecha_ultima_mantencion'], $payload['detalle']['fecha_proxima_mantencion'], $payload['detalle']['garantia_hasta']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        if ($this->tablaExiste('herramienta_compra')) {
            $stmt = mysqli_prepare($this->cn, 'INSERT INTO herramienta_compra (id_herramienta, valor_herramienta, proveedor, numero_factura, fecha_compra, observacion) VALUES (?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'idssss', $idHerramienta, $payload['compra']['valor_herramienta'], $payload['compra']['proveedor'], $payload['compra']['numero_factura'], $payload['compra']['fecha_compra'], $payload['compra']['observacion']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        if ($this->tablaExiste('herramienta_prestamos') && !empty($payload['prestamos'])) {
            $stmt = mysqli_prepare($this->cn, 'INSERT INTO herramienta_prestamos (id_herramienta, responsable_entrega, responsable_recibe, fecha_salida, fecha_devolucion_prevista, fecha_devolucion_real, estado_prestamo, observacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            foreach ($payload['prestamos'] as $fila) {
                mysqli_stmt_bind_param($stmt, 'isssssss', $idHerramienta, $fila['responsable_entrega'], $fila['responsable_recibe'], $fila['fecha_salida'], $fila['fecha_devolucion_prevista'], $fila['fecha_devolucion_real'], $fila['estado_prestamo'], $fila['observacion']);
                mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);
        }
        if ($this->tablaExiste('herramienta_mantenciones') && !empty($payload['mantenciones'])) {
            $stmt = mysqli_prepare($this->cn, 'INSERT INTO herramienta_mantenciones (id_herramienta, fecha_mantencion, tipo_mantencion, proveedor, costo, proxima_fecha, observacion) VALUES (?, ?, ?, ?, ?, ?, ?)');
            foreach ($payload['mantenciones'] as $fila) {
                $costo = (float)str_replace(',', '.', (string)($fila['costo'] ?? 0));
                mysqli_stmt_bind_param($stmt, 'isssdss', $idHerramienta, $fila['fecha_mantencion'], $fila['tipo_mantencion'], $fila['proveedor'], $costo, $fila['proxima_fecha'], $fila['observacion']);
                mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);
        }
    }

    private function limpiarRelacionados($idHerramienta)
    {
        foreach (['herramienta_detalle', 'herramienta_compra', 'herramienta_prestamos', 'herramienta_mantenciones'] as $tabla) {
            if (!$this->tablaExiste($tabla)) { continue; }
            $stmt = mysqli_prepare($this->cn, "DELETE FROM {$tabla} WHERE id_herramienta = ?");
            mysqli_stmt_bind_param($stmt, 'i', $idHerramienta);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    private function procesarEliminacionFotos($idHerramienta, $post)
    {
        if (!$this->tablaExiste('herramienta_fotos')) { return; }
        $idsEliminar = $post['fotos_eliminar'] ?? [];
        if (!is_array($idsEliminar) || empty($idsEliminar)) { return; }

        $stmt = mysqli_prepare($this->cn, 'SELECT ruta_foto FROM herramienta_fotos WHERE id_foto = ? AND id_herramienta = ?');
        $stmtDelete = mysqli_prepare($this->cn, 'DELETE FROM herramienta_fotos WHERE id_foto = ? AND id_herramienta = ?');
        foreach ($idsEliminar as $idFoto) {
            $idFoto = (int)$idFoto;
            mysqli_stmt_bind_param($stmt, 'ii', $idFoto, $idHerramienta);
            mysqli_stmt_execute($stmt);
            $foto = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_bind_param($stmtDelete, 'ii', $idFoto, $idHerramienta);
            mysqli_stmt_execute($stmtDelete);
            if (!empty($foto['ruta_foto'])) {
                $rutaFisica = dirname(__DIR__) . $foto['ruta_foto'];
                if (is_file($rutaFisica)) { @unlink($rutaFisica); }
            }
        }
        mysqli_stmt_close($stmt);
        mysqli_stmt_close($stmtDelete);
    }

    private function guardarFotos($idHerramienta, $files)
    {
        if (!$this->tablaExiste('herramienta_fotos')) { return; }
        $baseDir = dirname(__DIR__) . '/uploads/herramientas/' . $idHerramienta;
        if (!is_dir($baseDir)) { mkdir($baseDir, 0775, true); }
        if (isset($files['fotos_herramienta'])) { $this->guardarMultiplesArchivos($idHerramienta, $files['fotos_herramienta'], $baseDir); }
    }

    private function guardarMultiplesArchivos($idHerramienta, $fileBag, $baseDir)
    {
        if (!isset($fileBag['name']) || !is_array($fileBag['name'])) { return; }
        $stmt = mysqli_prepare($this->cn, 'INSERT INTO herramienta_fotos (id_herramienta, ruta_foto, principal, orden_foto) VALUES (?, ?, ?, ?)');
        $tienePrincipal = false;
        $orden = 1;
        foreach ($fileBag['name'] as $i => $nombre) {
            if (($fileBag['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) { continue; }
            $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
            $nombreSeguro = uniqid('foto_', true) . '.' . $extension;
            $destino = $baseDir . '/' . $nombreSeguro;
            if (!move_uploaded_file($fileBag['tmp_name'][$i], $destino)) { throw new RuntimeException('No fue posible guardar una de las fotos de la herramienta.'); }
            $rutaRelativa = '/uploads/herramientas/' . $idHerramienta . '/' . $nombreSeguro;
            $principal = $tienePrincipal ? 0 : 1;
            mysqli_stmt_bind_param($stmt, 'isii', $idHerramienta, $rutaRelativa, $principal, $orden);
            mysqli_stmt_execute($stmt);
            $tienePrincipal = true;
            $orden++;
        }
        mysqli_stmt_close($stmt);
    }

    private function registrarHistorial($idHerramienta, $accion, $descripcion, $idUsuario)
    {
        if (!$this->tablaExiste('herramienta_historial')) { return; }
        $stmt = mysqli_prepare($this->cn, 'INSERT INTO herramienta_historial (id_herramienta, accion, descripcion, id_usuario) VALUES (?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'issi', $idHerramienta, $accion, $descripcion, $idUsuario);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
