<?php

class Inventario
{
    private $db;
    private $cn;
    private $cacheTablas = [];
    private $categoriasFallback = ['Desinfectantes', 'Limpieza general', 'Pisos', 'Banos', 'Vidrios', 'Lavanderia', 'Consumibles', 'Otro'];
    private $unidadesFallback = ['Litro', 'Botella', 'Bidon', 'Unidad', 'Caja', 'Bolsa', 'Paquete', 'Galon'];

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
        $tablas = ['aseo_productos', 'aseo_movimientos', 'aseo_historial'];
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
        $sql = "SELECT id, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) AS nombre_completo
                FROM usuarios
                ORDER BY nombre ASC, apellido_paterno ASC";
        $rs = $this->db->consulta($sql);
        while ($fila = $this->db->fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function obtenerCategorias()
    {
        if ($this->tablaExiste('aseo_categoria_catalogo')) {
            $datos = [];
            $rs = $this->db->consulta("SELECT nombre_categoria FROM aseo_categoria_catalogo WHERE activo = 1 ORDER BY nombre_categoria ASC");
            while ($fila = $this->db->fetch_assoc($rs)) {
                $datos[] = $fila['nombre_categoria'];
            }
            if (!empty($datos)) {
                return $datos;
            }
        }
        return $this->categoriasFallback;
    }

    public function obtenerUnidades()
    {
        if ($this->tablaExiste('aseo_unidad_catalogo')) {
            $datos = [];
            $rs = $this->db->consulta("SELECT nombre_unidad FROM aseo_unidad_catalogo WHERE activo = 1 ORDER BY nombre_unidad ASC");
            while ($fila = $this->db->fetch_assoc($rs)) {
                $datos[] = $fila['nombre_unidad'];
            }
            if (!empty($datos)) {
                return $datos;
            }
        }
        return $this->unidadesFallback;
    }

    public function obtenerResumen($filtros = [])
    {
        $vacio = [
            'total_productos' => 0,
            'stock_total' => 0,
            'productos_bajo_minimo' => 0,
            'productos_agotados' => 0,
            'sin_movimientos' => 0,
            'ingresos_mes' => 0,
            'salidas_mes' => 0,
            'por_colegio' => [],
        ];

        if (!$this->tablaExiste('aseo_productos')) {
            return $vacio;
        }

        $where = $this->construirWhere($filtros);
        $stockSql = $this->subconsultaStock();
        $sql = "SELECT
                    COUNT(*) AS total_productos,
                    COALESCE(SUM(COALESCE(s.stock_actual, 0)), 0) AS stock_total,
                    SUM(CASE WHEN COALESCE(s.stock_actual, 0) <= p.stock_minimo THEN 1 ELSE 0 END) AS productos_bajo_minimo,
                    SUM(CASE WHEN COALESCE(s.stock_actual, 0) <= 0 THEN 1 ELSE 0 END) AS productos_agotados,
                    SUM(CASE WHEN s.ultimo_movimiento IS NULL THEN 1 ELSE 0 END) AS sin_movimientos,
                    COALESCE(SUM(COALESCE(s.ingresos_mes, 0)), 0) AS ingresos_mes,
                    COALESCE(SUM(COALESCE(s.salidas_mes, 0)), 0) AS salidas_mes
                FROM aseo_productos p
                LEFT JOIN ({$stockSql}) s ON s.id_producto = p.id_producto
                {$where['sql']}";
        $stmt = mysqli_prepare($this->cn, $sql);
        $this->bindParams($stmt, $where['types'], $where['params']);
        mysqli_stmt_execute($stmt);
        $resumen = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: $vacio;
        mysqli_stmt_close($stmt);

        $resumen['por_colegio'] = [];
        $sqlColegios = "SELECT c.nom_colegio, COUNT(*) AS productos, COALESCE(SUM(COALESCE(s.stock_actual, 0)), 0) AS stock
                        FROM aseo_productos p
                        INNER JOIN colegio c ON c.id_colegio = p.id_colegio
                        LEFT JOIN ({$stockSql}) s ON s.id_producto = p.id_producto
                        {$where['sql']}
                        GROUP BY p.id_colegio, c.nom_colegio
                        ORDER BY stock DESC, c.nom_colegio ASC";
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

    public function listarProductos($filtros = [])
    {
        if (!$this->tablaExiste('aseo_productos')) {
            return [];
        }

        $where = $this->construirWhere($filtros);
        $stockSql = $this->subconsultaStock();
        $sql = "SELECT
                    p.id_producto,
                    p.nombre_producto,
                    p.categoria,
                    p.unidad_medida,
                    p.stock_minimo,
                    p.descripcion,
                    p.activo,
                    c.nom_colegio,
                    COALESCE(s.stock_actual, 0) AS stock_actual,
                    s.ultimo_movimiento
                FROM aseo_productos p
                INNER JOIN colegio c ON c.id_colegio = p.id_colegio
                LEFT JOIN ({$stockSql}) s ON s.id_producto = p.id_producto
                {$where['sql']}
                ORDER BY p.id_producto DESC";
        $stmt = mysqli_prepare($this->cn, $sql);
        $this->bindParams($stmt, $where['types'], $where['params']);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);

        $datos = [];
        while ($fila = mysqli_fetch_assoc($rs)) {
            $fila['badge_stock'] = $this->renderBadgeStock((float) $fila['stock_actual'], (float) $fila['stock_minimo']);
            $datos[] = $fila;
        }
        mysqli_stmt_close($stmt);
        return $datos;
    }

    public function obtenerProductoCompleto($idProducto)
    {
        $idProducto = (int) $idProducto;
        if ($idProducto <= 0 || !$this->tablaExiste('aseo_productos')) {
            return null;
        }

        $stockSql = $this->subconsultaStock();
        $sql = "SELECT
                    p.*,
                    c.nom_colegio,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS usuario_registra,
                    COALESCE(s.stock_actual, 0) AS stock_actual,
                    s.ultimo_movimiento,
                    COALESCE(s.ingresos_mes, 0) AS ingresos_mes,
                    COALESCE(s.salidas_mes, 0) AS salidas_mes
                FROM aseo_productos p
                INNER JOIN colegio c ON c.id_colegio = p.id_colegio
                LEFT JOIN usuarios u ON u.id = p.id_usuario
                LEFT JOIN ({$stockSql}) s ON s.id_producto = p.id_producto
                WHERE p.id_producto = ?";
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idProducto);
        mysqli_stmt_execute($stmt);
        $producto = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$producto) {
            return null;
        }

        $producto['movimientos'] = $this->tablaExiste('aseo_movimientos')
            ? $this->obtenerVariasFilas(
                "SELECT m.*, CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS usuario_nombre
                 FROM aseo_movimientos m
                 LEFT JOIN usuarios u ON u.id = m.id_usuario
                 WHERE m.id_producto = ?
                 ORDER BY m.fecha_movimiento DESC, m.id_movimiento DESC",
                $idProducto
            )
            : [];

        return $producto;
    }

    public function obtenerProductosBajoMinimo($limit = 20)
    {
        if (!$this->tablaExiste('aseo_productos')) {
            return [];
        }

        $limit = max(1, (int) $limit);
        $stockSql = $this->subconsultaStock();
        $sql = "SELECT p.nombre_producto, p.categoria, p.unidad_medida, p.stock_minimo, c.nom_colegio, COALESCE(s.stock_actual, 0) AS stock_actual
                FROM aseo_productos p
                INNER JOIN colegio c ON c.id_colegio = p.id_colegio
                LEFT JOIN ({$stockSql}) s ON s.id_producto = p.id_producto
                WHERE p.activo = 1 AND COALESCE(s.stock_actual, 0) <= p.stock_minimo
                ORDER BY stock_actual ASC, p.nombre_producto ASC
                LIMIT {$limit}";
        $rs = $this->db->consulta($sql);
        $datos = [];
        while ($fila = $this->db->fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function obtenerMovimientosRecientes($limit = 30)
    {
        if (!$this->tablaExiste('aseo_movimientos')) {
            return [];
        }

        $limit = max(1, (int) $limit);
        $sql = "SELECT m.tipo_movimiento, m.cantidad, m.fecha_movimiento, m.responsable, m.observacion,
                       p.nombre_producto, p.unidad_medida, c.nom_colegio
                FROM aseo_movimientos m
                INNER JOIN aseo_productos p ON p.id_producto = m.id_producto
                INNER JOIN colegio c ON c.id_colegio = p.id_colegio
                ORDER BY m.fecha_movimiento DESC, m.id_movimiento DESC
                LIMIT {$limit}";
        $rs = $this->db->consulta($sql);
        $datos = [];
        while ($fila = $this->db->fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function guardarProducto($post, $idUsuario)
    {
        $payload = $this->normalizarPayload($post, 0, $idUsuario);

        mysqli_begin_transaction($this->cn);
        try {
            $stmt = mysqli_prepare(
                $this->cn,
                "INSERT INTO aseo_productos
                (id_usuario, id_colegio, nombre_producto, categoria, unidad_medida, descripcion, stock_minimo, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param(
                $stmt,
                'iissssii',
                $payload['producto']['id_usuario'],
                $payload['producto']['id_colegio'],
                $payload['producto']['nombre_producto'],
                $payload['producto']['categoria'],
                $payload['producto']['unidad_medida'],
                $payload['producto']['descripcion'],
                $payload['producto']['stock_minimo'],
                $payload['producto']['activo']
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $idProducto = mysqli_insert_id($this->cn);
            $this->guardarMovimientos($idProducto, $payload['movimientos'], $idUsuario);
            $this->registrarHistorial($idProducto, 'creacion', 'Producto de aseo registrado.', $idUsuario);
            mysqli_commit($this->cn);
            return $idProducto;
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    public function actualizarProducto($idProducto, $post, $idUsuario)
    {
        $idProducto = (int) $idProducto;
        $payload = $this->normalizarPayload($post, $idProducto, $idUsuario);

        mysqli_begin_transaction($this->cn);
        try {
            $stmt = mysqli_prepare(
                $this->cn,
                "UPDATE aseo_productos SET
                    id_colegio = ?,
                    nombre_producto = ?,
                    categoria = ?,
                    unidad_medida = ?,
                    descripcion = ?,
                    stock_minimo = ?,
                    activo = ?
                 WHERE id_producto = ?"
            );
            mysqli_stmt_bind_param(
                $stmt,
                'issssiii',
                $payload['producto']['id_colegio'],
                $payload['producto']['nombre_producto'],
                $payload['producto']['categoria'],
                $payload['producto']['unidad_medida'],
                $payload['producto']['descripcion'],
                $payload['producto']['stock_minimo'],
                $payload['producto']['activo'],
                $idProducto
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $this->guardarMovimientos($idProducto, $payload['movimientos'], $idUsuario);
            $this->registrarHistorial($idProducto, 'actualizacion', 'Producto de aseo actualizado.', $idUsuario);
            mysqli_commit($this->cn);
            return true;
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    public function cambiarEstadoLogico($idProducto, $activo, $idUsuario)
    {
        $stmt = mysqli_prepare($this->cn, "UPDATE aseo_productos SET activo = ? WHERE id_producto = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $activo, $idProducto);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $descripcion = $activo ? 'Producto reactivado.' : 'Producto desactivado logicamente.';
        $this->registrarHistorial((int) $idProducto, 'estado', $descripcion, (int) $idUsuario);
        return true;
    }

    public function renderBadgeStock($stockActual, $stockMinimo)
    {
        $stockActual = (float) $stockActual;
        $stockMinimo = (float) $stockMinimo;

        if ($stockActual <= 0) {
            return '<span class="badge text-bg-danger">Agotado</span>';
        }
        if ($stockActual <= $stockMinimo) {
            return '<span class="badge text-bg-warning">Bajo minimo</span>';
        }
        return '<span class="badge text-bg-success">Disponible</span>';
    }

    public function obtenerSqlSugerido()
    {
        return <<<'SQL'
CREATE TABLE IF NOT EXISTS aseo_categoria_catalogo (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1
);

INSERT INTO aseo_categoria_catalogo (nombre_categoria, activo) VALUES
('Desinfectantes', 1),
('Limpieza general', 1),
('Pisos', 1),
('Banos', 1),
('Vidrios', 1),
('Lavanderia', 1),
('Consumibles', 1),
('Otro', 1)
ON DUPLICATE KEY UPDATE activo = VALUES(activo);

CREATE TABLE IF NOT EXISTS aseo_unidad_catalogo (
    id_unidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre_unidad VARCHAR(80) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1
);

INSERT INTO aseo_unidad_catalogo (nombre_unidad, activo) VALUES
('Litro', 1),
('Botella', 1),
('Bidon', 1),
('Unidad', 1),
('Caja', 1),
('Bolsa', 1),
('Paquete', 1),
('Galon', 1)
ON DUPLICATE KEY UPDATE activo = VALUES(activo);

CREATE TABLE IF NOT EXISTS aseo_productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_colegio INT NOT NULL,
    nombre_producto VARCHAR(150) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    unidad_medida VARCHAR(80) NOT NULL,
    descripcion TEXT NULL,
    stock_minimo DECIMAL(12,2) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_aseo_producto_colegio FOREIGN KEY (id_colegio) REFERENCES colegio(id_colegio),
    CONSTRAINT fk_aseo_producto_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS aseo_movimientos (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    tipo_movimiento ENUM('ingreso', 'salida') NOT NULL,
    cantidad DECIMAL(12,2) NOT NULL DEFAULT 0,
    fecha_movimiento DATETIME NOT NULL,
    responsable VARCHAR(150) DEFAULT '',
    observacion TEXT NULL,
    id_usuario INT NOT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_aseo_movimiento_producto FOREIGN KEY (id_producto) REFERENCES aseo_productos(id_producto) ON DELETE CASCADE,
    CONSTRAINT fk_aseo_movimiento_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS aseo_historial (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    accion VARCHAR(60) NOT NULL,
    descripcion TEXT NOT NULL,
    id_usuario INT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_aseo_historial_producto FOREIGN KEY (id_producto) REFERENCES aseo_productos(id_producto) ON DELETE CASCADE,
    CONSTRAINT fk_aseo_historial_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);
SQL;
    }

    private function subconsultaStock()
    {
        return "SELECT
                    m.id_producto,
                    SUM(CASE WHEN m.tipo_movimiento = 'ingreso' THEN m.cantidad ELSE -m.cantidad END) AS stock_actual,
                    MAX(m.fecha_movimiento) AS ultimo_movimiento,
                    SUM(CASE WHEN m.tipo_movimiento = 'ingreso' AND DATE_FORMAT(m.fecha_movimiento, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN m.cantidad ELSE 0 END) AS ingresos_mes,
                    SUM(CASE WHEN m.tipo_movimiento = 'salida' AND DATE_FORMAT(m.fecha_movimiento, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN m.cantidad ELSE 0 END) AS salidas_mes
                FROM aseo_movimientos m
                GROUP BY m.id_producto";
    }

    private function construirWhere($filtros)
    {
        $condiciones = [];
        $types = '';
        $params = [];

        if (!empty($filtros['id_colegio'])) {
            $condiciones[] = 'p.id_colegio = ?';
            $types .= 'i';
            $params[] = (int) $filtros['id_colegio'];
        }
        if (!empty($filtros['categoria'])) {
            $condiciones[] = 'p.categoria = ?';
            $types .= 's';
            $params[] = trim((string) $filtros['categoria']);
        }
        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $condiciones[] = 'p.activo = ?';
            $types .= 'i';
            $params[] = (int) $filtros['activo'];
        }
        if (!empty($filtros['busqueda'])) {
            $busqueda = '%' . trim((string) $filtros['busqueda']) . '%';
            $condiciones[] = '(p.nombre_producto LIKE ? OR p.descripcion LIKE ? OR p.unidad_medida LIKE ?)';
            $types .= 'sss';
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

    private function obtenerVariasFilas($sql, $idProducto)
    {
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idProducto);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $filas = [];
        while ($fila = mysqli_fetch_assoc($rs)) {
            $filas[] = $fila;
        }
        mysqli_stmt_close($stmt);
        return $filas;
    }

    private function normalizarPayload($post, $idProducto, $idUsuario)
    {
        $producto = [
            'id_usuario' => (int) $idUsuario,
            'id_colegio' => (int) ($post['id_colegio'] ?? 0),
            'nombre_producto' => trim((string) ($post['nombre_producto'] ?? '')),
            'categoria' => trim((string) ($post['categoria'] ?? '')),
            'unidad_medida' => trim((string) ($post['unidad_medida'] ?? '')),
            'descripcion' => trim((string) ($post['descripcion'] ?? '')),
            'stock_minimo' => (float) str_replace(',', '.', (string) ($post['stock_minimo'] ?? 0)),
            'activo' => isset($post['activo']) ? (int) $post['activo'] : 1,
        ];

        if ($producto['id_colegio'] <= 0 || $producto['nombre_producto'] === '' || $producto['categoria'] === '' || $producto['unidad_medida'] === '') {
            throw new RuntimeException('Debes completar colegio, nombre del producto, categoria y unidad de medida.');
        }

        return [
            'producto' => $producto,
            'movimientos' => $this->normalizarMovimientos($post['movimiento'] ?? []),
        ];
    }

    private function normalizarMovimientos($filas)
    {
        $resultado = [];
        foreach ($filas as $fila) {
            $tipo = trim((string) ($fila['tipo_movimiento'] ?? ''));
            $cantidad = (float) str_replace(',', '.', (string) ($fila['cantidad'] ?? 0));
            $fecha = str_replace('T', ' ', trim((string) ($fila['fecha_movimiento'] ?? '')));
            $responsable = trim((string) ($fila['responsable'] ?? ''));
            $observacion = trim((string) ($fila['observacion'] ?? ''));

            if ($tipo === '' && $cantidad <= 0 && $fecha === '' && $responsable === '' && $observacion === '') {
                continue;
            }

            if (!in_array($tipo, ['ingreso', 'salida'], true)) {
                throw new RuntimeException('Existe un movimiento con tipo invalido.');
            }
            if ($cantidad <= 0) {
                throw new RuntimeException('La cantidad de un movimiento debe ser mayor a cero.');
            }
            if ($fecha === '') {
                throw new RuntimeException('Cada movimiento debe tener fecha.');
            }

            $resultado[] = [
                'tipo_movimiento' => $tipo,
                'cantidad' => $cantidad,
                'fecha_movimiento' => $fecha,
                'responsable' => $responsable,
                'observacion' => $observacion,
            ];
        }
        return $resultado;
    }

    private function guardarMovimientos($idProducto, $movimientos, $idUsuario)
    {
        if (!$this->tablaExiste('aseo_movimientos') || empty($movimientos)) {
            return;
        }

        $stmt = mysqli_prepare(
            $this->cn,
            "INSERT INTO aseo_movimientos
            (id_producto, tipo_movimiento, cantidad, fecha_movimiento, responsable, observacion, id_usuario)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        foreach ($movimientos as $movimiento) {
            mysqli_stmt_bind_param(
                $stmt,
                'isdsssi',
                $idProducto,
                $movimiento['tipo_movimiento'],
                $movimiento['cantidad'],
                $movimiento['fecha_movimiento'],
                $movimiento['responsable'],
                $movimiento['observacion'],
                $idUsuario
            );
            mysqli_stmt_execute($stmt);
        }

        mysqli_stmt_close($stmt);
    }

    private function registrarHistorial($idProducto, $accion, $descripcion, $idUsuario)
    {
        if (!$this->tablaExiste('aseo_historial')) {
            return;
        }

        $stmt = mysqli_prepare($this->cn, "INSERT INTO aseo_historial (id_producto, accion, descripcion, id_usuario) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'issi', $idProducto, $accion, $descripcion, $idUsuario);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
