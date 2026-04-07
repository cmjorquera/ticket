<?php

class InventarioSoftware
{
    private $db;
    private $cn;
    private $cacheTablas = [];
    private $tiposLicenciamiento = ['Suscripcion', 'Licencia perpetua', 'Gratuita'];
    private $pagadoPor = ['Colegio', 'Seduc', 'Persona'];
    private $monedas = ['CLP', 'USD'];
    private $tiposSitio = ['Web', 'App', 'Cliente'];
    private $estadosSitio = ['Activo', 'Mantenimiento', 'En desarrollo', 'Inactivo'];

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
        $tablas = ['software_catalogo', 'software_datos_almacenamiento', 'software_historial', 'sitios_web_catalogo', 'inventario_software_datos_sensibles', 'software_datos_sensibles_rel', 'inventario_software_tipo_usuario', 'software_tipo_usuario_rel'];
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

    public function obtenerTiposLicenciamiento()
    {
        return $this->tiposLicenciamiento;
    }

    public function obtenerPagadores()
    {
        return $this->pagadoPor;
    }

    public function obtenerMonedas()
    {
        return $this->monedas;
    }

    public function obtenerTiposSitio()
    {
        return $this->tiposSitio;
    }

    public function obtenerEstadosSitio()
    {
        return $this->estadosSitio;
    }

    public function obtenerResumen()
    {
        $resumen = [
            'total_softwares' => 0,
            'total_licencias' => 0,
            'total_suscripciones' => 0,
            'total_perpetuas' => 0,
            'total_gratuitas' => 0,
            'total_sitios' => 0,
            'total_webs' => 0,
            'total_apps' => 0,
            'total_clientes' => 0,
            'por_colegio' => [],
        ];

        if ($this->tablaExiste('software_catalogo')) {
            $sql = "SELECT COUNT(*) AS total_softwares,
                           COALESCE(SUM(cantidad_licencias),0) AS total_licencias,
                           SUM(CASE WHEN tipo_licenciamiento = 'Suscripcion' THEN 1 ELSE 0 END) AS total_suscripciones,
                           SUM(CASE WHEN tipo_licenciamiento = 'Licencia perpetua' THEN 1 ELSE 0 END) AS total_perpetuas,
                           SUM(CASE WHEN tipo_licenciamiento = 'Gratuita' THEN 1 ELSE 0 END) AS total_gratuitas
                    FROM software_catalogo
                    WHERE activo = 1";
            $fila = $this->db->fetch_assoc($this->db->consulta($sql));
            if ($fila) {
                $resumen = array_merge($resumen, $fila);
            }

            $rs = $this->db->consulta("SELECT COALESCE(NULLIF(c.nom_colegio, ''), CONCAT('Colegio ID ', s.id_colegio)) AS nom_colegio, COUNT(*) AS total FROM software_catalogo s LEFT JOIN colegio c ON c.id_colegio = s.id_colegio WHERE s.activo = 1 GROUP BY s.id_colegio, c.nom_colegio ORDER BY total DESC, nom_colegio ASC");
            while ($fila = $this->db->fetch_assoc($rs)) {
                $resumen['por_colegio'][] = $fila;
            }
        }

        if ($this->tablaExiste('sitios_web_catalogo')) {
            $sql = "SELECT COUNT(*) AS total_sitios,
                           SUM(CASE WHEN tipo_sitio = 'Web' THEN 1 ELSE 0 END) AS total_webs,
                           SUM(CASE WHEN tipo_sitio = 'App' THEN 1 ELSE 0 END) AS total_apps,
                           SUM(CASE WHEN tipo_sitio = 'Cliente' THEN 1 ELSE 0 END) AS total_clientes
                    FROM sitios_web_catalogo
                    WHERE activo = 1";
            $fila = $this->db->fetch_assoc($this->db->consulta($sql));
            if ($fila) {
                $resumen = array_merge($resumen, $fila);
            }
        }

        return $resumen;
    }

    public function obtenerDashboardColegios()
    {
        $usaSoftware = $this->tablaExiste('software_catalogo');
        $usaSitios = $this->tablaExiste('sitios_web_catalogo');

        $subSoftware = $usaSoftware
            ? "SELECT id_colegio,
                      COUNT(*) AS total_softwares,
                      COALESCE(SUM(cantidad_licencias), 0) AS total_licencias,
                      COALESCE(SUM(costo), 0) AS costo_total
               FROM software_catalogo
               WHERE activo = 1
               GROUP BY id_colegio"
            : "SELECT 0 AS id_colegio, 0 AS total_softwares, 0 AS total_licencias, 0 AS costo_total";

        $subSitios = $usaSitios
            ? "SELECT id_colegio,
                      COUNT(*) AS total_sitios,
                      SUM(CASE WHEN tipo_sitio = 'Web' THEN 1 ELSE 0 END) AS total_webs,
                      SUM(CASE WHEN tipo_sitio = 'App' THEN 1 ELSE 0 END) AS total_apps,
                      SUM(CASE WHEN tipo_sitio = 'Cliente' THEN 1 ELSE 0 END) AS total_clientes
               FROM sitios_web_catalogo
               WHERE activo = 1
               GROUP BY id_colegio"
            : "SELECT 0 AS id_colegio, 0 AS total_sitios, 0 AS total_webs, 0 AS total_apps, 0 AS total_clientes";

        $sql = "SELECT c.id_colegio,
                       c.nom_colegio,
                       c.rbd_colegio,
                       COALESCE(s.total_softwares, 0) AS total_softwares,
                       COALESCE(s.total_licencias, 0) AS total_licencias,
                       COALESCE(s.costo_total, 0) AS costo_total,
                       COALESCE(sw.total_sitios, 0) AS total_sitios,
                       COALESCE(sw.total_webs, 0) AS total_webs,
                       COALESCE(sw.total_apps, 0) AS total_apps,
                       COALESCE(sw.total_clientes, 0) AS total_clientes
                FROM colegio c
                LEFT JOIN ({$subSoftware}) s ON s.id_colegio = c.id_colegio
                LEFT JOIN ({$subSitios}) sw ON sw.id_colegio = c.id_colegio
                WHERE c.estado = 1
                ORDER BY c.nom_colegio ASC";

        $rs = $this->db->consulta($sql);
        $datos = [];
        while ($fila = $this->db->fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function obtenerOpcionesSoftwareConsulta()
    {
        if (!$this->tablaExiste('software_catalogo')) {
            return [];
        }

        $rs = $this->db->consulta("SELECT DISTINCT nombre_software FROM software_catalogo WHERE activo = 1 AND nombre_software <> '' ORDER BY nombre_software ASC");
        $datos = [];
        while ($fila = $this->db->fetch_assoc($rs)) {
            $datos[] = $fila['nombre_software'];
        }
        return $datos;
    }

    public function obtenerDetalleColegio($idColegio)
    {
        $idColegio = (int)$idColegio;
        if ($idColegio <= 0) {
            return null;
        }

        $stmt = mysqli_prepare($this->cn, "SELECT id_colegio, nom_colegio, rza_colegio, dir_colegio, rbd_colegio, tel_colegio, web_colegio, rut_colegio, email_comunicaciones, url_pagina FROM colegio WHERE id_colegio = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $idColegio);
        mysqli_stmt_execute($stmt);
        $colegio = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
        mysqli_stmt_close($stmt);
        return $colegio;
    }

    public function obtenerConsultaPorSoftware($nombreSoftware)
    {
        $nombreSoftware = trim((string)$nombreSoftware);
        $vacio = [
            'software' => $nombreSoftware,
            'resumen' => [
                'total_licencias' => 0,
                'total_registros' => 0,
                'total_colegios' => 0,
                'costo_total' => 0,
            ],
            'colegios' => [],
        ];

        if ($nombreSoftware === '' || !$this->tablaExiste('software_catalogo')) {
            return $vacio;
        }

        $sql = "SELECT COALESCE(SUM(cantidad_licencias), 0) AS total_licencias,
                       COUNT(*) AS total_registros,
                       COUNT(DISTINCT id_colegio) AS total_colegios,
                       COALESCE(SUM(costo), 0) AS costo_total
                FROM software_catalogo
                WHERE activo = 1 AND nombre_software = ?";
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $nombreSoftware);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        if ($fila) {
            $vacio['resumen'] = $fila;
        }

        $usaDatosSensibles = $this->tablaExiste('software_datos_sensibles_rel') && $this->tablaExiste('inventario_software_datos_sensibles');
        $selectDatosSensibles = $usaDatosSensibles
            ? ", GROUP_CONCAT(DISTINCT d.nombre ORDER BY d.nombre SEPARATOR ', ') AS datos_sensibles"
            : ", '' AS datos_sensibles";
        $joinDatosSensibles = $usaDatosSensibles
            ? " LEFT JOIN software_datos_sensibles_rel r ON r.id_software = s.id_software
                LEFT JOIN inventario_software_datos_sensibles d ON d.id_dato_sensible = r.id_dato_sensible AND d.activo = 1"
            : '';

        $sql = "SELECT c.id_colegio,
                       COALESCE(NULLIF(c.nom_colegio, ''), CONCAT('Colegio ID ', s.id_colegio)) AS nom_colegio,
                       COALESCE(SUM(s.cantidad_licencias), 0) AS licencias,
                       COUNT(*) AS registros,
                       GROUP_CONCAT(DISTINCT s.tipo_licenciamiento ORDER BY s.tipo_licenciamiento SEPARATOR ', ') AS licenciamiento,
                       GROUP_CONCAT(DISTINCT s.pagado_por ORDER BY s.pagado_por SEPARATOR ', ') AS pagado_por,
                       " . ltrim($selectDatosSensibles, ', ') . ",
                       COALESCE(SUM(s.costo), 0) AS costo_total
                FROM software_catalogo s
                LEFT JOIN colegio c ON c.id_colegio = s.id_colegio
                {$joinDatosSensibles}
                WHERE s.activo = 1 AND s.nombre_software = ?
                GROUP BY s.id_colegio, c.nom_colegio
                ORDER BY licencias DESC, nom_colegio ASC";
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $nombreSoftware);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($fila = mysqli_fetch_assoc($rs)) {
            $vacio['colegios'][] = $fila;
        }
        mysqli_stmt_close($stmt);

        return $vacio;
    }

    public function obtenerConsultaPorColegio($idColegio)
    {
        $idColegio = (int)$idColegio;
        $vacio = [
            'colegio' => null,
            'resumen' => [
                'total_softwares' => 0,
                'total_licencias' => 0,
                'costo_total' => 0,
                'total_webs' => 0,
                'total_apps' => 0,
                'total_clientes' => 0,
                'total_sitios' => 0,
            ],
            'softwares' => [],
            'sitios' => [],
        ];

        if ($idColegio <= 0) {
            return $vacio;
        }

        $vacio['colegio'] = $this->obtenerDetalleColegio($idColegio);

        if (!$vacio['colegio']) {
            return $vacio;
        }

        if ($this->tablaExiste('software_catalogo')) {
            $stmt = mysqli_prepare($this->cn, "SELECT COUNT(*) AS total_softwares, COALESCE(SUM(cantidad_licencias), 0) AS total_licencias, COALESCE(SUM(costo), 0) AS costo_total FROM software_catalogo WHERE activo = 1 AND id_colegio = ?");
            mysqli_stmt_bind_param($stmt, 'i', $idColegio);
            mysqli_stmt_execute($stmt);
            $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);
            if ($fila) {
                $vacio['resumen'] = array_merge($vacio['resumen'], $fila);
            }

            $stmt = mysqli_prepare($this->cn, "SELECT id_software, nombre_software, version_software, cantidad_licencias, tipo_licenciamiento, pagado_por, costo, proveedor FROM software_catalogo WHERE activo = 1 AND id_colegio = ? ORDER BY cantidad_licencias DESC, nombre_software ASC");
            mysqli_stmt_bind_param($stmt, 'i', $idColegio);
            mysqli_stmt_execute($stmt);
            $rs = mysqli_stmt_get_result($stmt);
            while ($fila = mysqli_fetch_assoc($rs)) {
                $vacio['softwares'][] = $fila;
            }
            mysqli_stmt_close($stmt);

            $mapaDatosSensibles = $this->obtenerMapaDatosSensiblesPorSoftware(array_column($vacio['softwares'], 'id_software'));
            foreach ($vacio['softwares'] as &$fila) {
                $idSoftware = (int)($fila['id_software'] ?? 0);
                $fila['datos_sensibles'] = $mapaDatosSensibles[$idSoftware] ?? '';
            }
            unset($fila);
        }

        if ($this->tablaExiste('sitios_web_catalogo')) {
            $stmt = mysqli_prepare($this->cn, "SELECT COUNT(*) AS total_sitios, SUM(CASE WHEN tipo_sitio = 'Web' THEN 1 ELSE 0 END) AS total_webs, SUM(CASE WHEN tipo_sitio = 'App' THEN 1 ELSE 0 END) AS total_apps, SUM(CASE WHEN tipo_sitio = 'Cliente' THEN 1 ELSE 0 END) AS total_clientes FROM sitios_web_catalogo WHERE activo = 1 AND id_colegio = ?");
            mysqli_stmt_bind_param($stmt, 'i', $idColegio);
            mysqli_stmt_execute($stmt);
            $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);
            if ($fila) {
                $vacio['resumen'] = array_merge($vacio['resumen'], $fila);
            }

            $stmt = mysqli_prepare($this->cn, "SELECT nombre_sitio, tipo_sitio, url_sitio, estado_sitio, proveedor_hosting FROM sitios_web_catalogo WHERE activo = 1 AND id_colegio = ? ORDER BY tipo_sitio ASC, nombre_sitio ASC");
            mysqli_stmt_bind_param($stmt, 'i', $idColegio);
            mysqli_stmt_execute($stmt);
            $rs = mysqli_stmt_get_result($stmt);
            while ($fila = mysqli_fetch_assoc($rs)) {
                $vacio['sitios'][] = $fila;
            }
            mysqli_stmt_close($stmt);
        }

        return $vacio;
    }

    public function listarSoftwares($filtros = [])
    {
        if (!$this->tablaExiste('software_catalogo')) {
            return [];
        }

        $where = ['s.activo = 1'];

        if (!empty($filtros['id_colegio'])) {
            $where[] = 's.id_colegio = ' . (int)$filtros['id_colegio'];
        }
        if (!empty($filtros['id_usuario_responsable'])) {
            $where[] = 's.id_usuario_responsable = ' . (int)$filtros['id_usuario_responsable'];
        }
        if (!empty($filtros['tipo_licenciamiento'])) {
            $tipoLicenciamiento = $this->db->escape_string(trim((string)$filtros['tipo_licenciamiento']));
            $where[] = "s.tipo_licenciamiento = '{$tipoLicenciamiento}'";
        }
        if (!empty($filtros['busqueda'])) {
            $like = $this->db->escape_string('%' . trim((string)$filtros['busqueda']) . '%');
            $where[] = "(s.nombre_software LIKE '{$like}' OR s.version_software LIKE '{$like}' OR s.proveedor LIKE '{$like}')";
        }

        $sql = "SELECT s.*,
                       COALESCE(NULLIF(c.nom_colegio, ''), CONCAT('Colegio ID ', s.id_colegio)) AS nom_colegio,
                       CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS responsable
                FROM software_catalogo s
                LEFT JOIN colegio c ON c.id_colegio = s.id_colegio
                LEFT JOIN usuarios u ON u.id = s.id_usuario_responsable
                WHERE " . implode(' AND ', $where) . "
                ORDER BY s.id_software DESC";
        $rs = $this->db->consulta($sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($rs)) {
            $datos[] = $fila;
        }

        $mapaDatosSensibles = $this->obtenerMapaDatosSensiblesPorSoftware(array_column($datos, 'id_software'));
        foreach ($datos as &$fila) {
            $idSoftware = (int)($fila['id_software'] ?? 0);
            $fila['datos_sensibles'] = $mapaDatosSensibles[$idSoftware] ?? '';
        }
        unset($fila);

        return $datos;
    }

    public function obtenerDatosSensiblesCatalogo()
    {
        if (!$this->tablaExiste('inventario_software_datos_sensibles')) {
            return [];
        }

        $datos = [];
        $rs = $this->db->consulta("SELECT id_dato_sensible, nombre, descripcion FROM inventario_software_datos_sensibles WHERE activo = 1 ORDER BY nombre ASC");
        while ($fila = $this->db->fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function obtenerTiposUsuarioCatalogo()
    {
        if (!$this->tablaExiste('inventario_software_tipo_usuario')) {
            return [];
        }

        $datos = [];
        $rs = $this->db->consulta("SELECT id_tipo_usuario, nombre, descripcion FROM inventario_software_tipo_usuario WHERE activo = 1 ORDER BY nombre ASC");
        while ($fila = $this->db->fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function registrarDatoSensible($nombre, $descripcion = '')
    {
        if (!$this->tablaExiste('inventario_software_datos_sensibles')) {
            throw new RuntimeException('Falta la tabla inventario_software_datos_sensibles.');
        }

        $nombre = trim((string)$nombre);
        $descripcion = trim((string)$descripcion);

        if ($nombre === '') {
            throw new RuntimeException('Debes indicar el nombre del dato sensible.');
        }

        $stmt = mysqli_prepare($this->cn, "SELECT id_dato_sensible, nombre, descripcion FROM inventario_software_datos_sensibles WHERE LOWER(nombre) = LOWER(?) LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $nombre);
        mysqli_stmt_execute($stmt);
        $existente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
        mysqli_stmt_close($stmt);

        if ($existente) {
            return [
                'creado' => false,
                'id_dato_sensible' => (int)$existente['id_dato_sensible'],
                'nombre' => (string)$existente['nombre'],
                'descripcion' => (string)($existente['descripcion'] ?? ''),
            ];
        }

        $stmt = mysqli_prepare($this->cn, "INSERT INTO inventario_software_datos_sensibles (nombre, descripcion, activo, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())");
        mysqli_stmt_bind_param($stmt, 'ss', $nombre, $descripcion);
        mysqli_stmt_execute($stmt);
        $idDatoSensible = (int)mysqli_insert_id($this->cn);
        mysqli_stmt_close($stmt);

        return [
            'creado' => true,
            'id_dato_sensible' => $idDatoSensible,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
        ];
    }

    public function obtenerConsultaPorDatoSensible($idDatoSensible)
    {
        $idDatoSensible = (int)$idDatoSensible;
        $vacio = [
            'dato' => null,
            'resumen' => [
                'total_softwares' => 0,
                'total_licencias' => 0,
                'total_colegios' => 0,
                'costo_total' => 0,
            ],
            'softwares' => [],
        ];

        if (
            $idDatoSensible <= 0 ||
            !$this->tablaExiste('inventario_software_datos_sensibles') ||
            !$this->tablaExiste('software_datos_sensibles_rel') ||
            !$this->tablaExiste('software_catalogo')
        ) {
            return $vacio;
        }

        $stmt = mysqli_prepare($this->cn, "SELECT id_dato_sensible, nombre, descripcion FROM inventario_software_datos_sensibles WHERE id_dato_sensible = ? AND activo = 1 LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $idDatoSensible);
        mysqli_stmt_execute($stmt);
        $vacio['dato'] = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
        mysqli_stmt_close($stmt);

        if (empty($vacio['dato'])) {
            return $vacio;
        }

        $sql = "SELECT COUNT(DISTINCT s.id_software) AS total_softwares,
                       COALESCE(SUM(s.cantidad_licencias), 0) AS total_licencias,
                       COUNT(DISTINCT s.id_colegio) AS total_colegios,
                       COALESCE(SUM(s.costo), 0) AS costo_total
                FROM software_datos_sensibles_rel r
                INNER JOIN software_catalogo s ON s.id_software = r.id_software
                WHERE r.id_dato_sensible = ? AND s.activo = 1";
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idDatoSensible);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        if ($fila) {
            $vacio['resumen'] = $fila;
        }

        $sql = "SELECT s.id_software,
                       s.nombre_software,
                       s.version_software,
                       s.cantidad_licencias,
                       s.tipo_licenciamiento,
                       s.pagado_por,
                       s.costo,
                       s.moneda,
                       COALESCE(NULLIF(c.nom_colegio, ''), CONCAT('Colegio ID ', s.id_colegio)) AS nom_colegio,
                       CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS responsable
                FROM software_datos_sensibles_rel r
                INNER JOIN software_catalogo s ON s.id_software = r.id_software
                LEFT JOIN colegio c ON c.id_colegio = s.id_colegio
                LEFT JOIN usuarios u ON u.id = s.id_usuario_responsable
                WHERE r.id_dato_sensible = ? AND s.activo = 1
                ORDER BY nom_colegio ASC, s.nombre_software ASC, s.id_software DESC";
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idDatoSensible);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($fila = mysqli_fetch_assoc($rs)) {
            $vacio['softwares'][] = $fila;
        }
        mysqli_stmt_close($stmt);

        return $vacio;
    }

    public function listarSitiosWeb($filtros = [])
    {
        if (!$this->tablaExiste('sitios_web_catalogo')) {
            return [];
        }

        $where = ['sw.activo = 1'];

        if (!empty($filtros['id_colegio'])) {
            $where[] = 'sw.id_colegio = ' . (int)$filtros['id_colegio'];
        }
        if (!empty($filtros['id_usuario_responsable'])) {
            $where[] = 'sw.id_usuario_responsable = ' . (int)$filtros['id_usuario_responsable'];
        }
        if (!empty($filtros['tipo_sitio'])) {
            $tipoSitio = $this->db->escape_string(trim((string)$filtros['tipo_sitio']));
            $where[] = "sw.tipo_sitio = '{$tipoSitio}'";
        }
        if (!empty($filtros['busqueda'])) {
            $like = $this->db->escape_string('%' . trim((string)$filtros['busqueda']) . '%');
            $where[] = "(sw.nombre_sitio LIKE '{$like}' OR sw.url_sitio LIKE '{$like}' OR sw.proveedor_hosting LIKE '{$like}')";
        }

        $sql = "SELECT sw.*,
                       COALESCE(NULLIF(c.nom_colegio, ''), CONCAT('Colegio ID ', sw.id_colegio)) AS nom_colegio,
                       CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS responsable
                FROM sitios_web_catalogo sw
                LEFT JOIN colegio c ON c.id_colegio = sw.id_colegio
                LEFT JOIN usuarios u ON u.id = sw.id_usuario_responsable
                WHERE " . implode(' AND ', $where) . "
                ORDER BY sw.id_sitio DESC";
        $rs = $this->db->consulta($sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function obtenerSoftwareCompleto($idSoftware)
    {
        $idSoftware = (int)$idSoftware;
        if ($idSoftware <= 0 || !$this->tablaExiste('software_catalogo')) {
            return null;
        }

        $sql = "SELECT s.*,
                       COALESCE(NULLIF(c.nom_colegio, ''), CONCAT('Colegio ID ', s.id_colegio)) AS nom_colegio,
                       CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS responsable
                FROM software_catalogo s
                LEFT JOIN colegio c ON c.id_colegio = s.id_colegio
                LEFT JOIN usuarios u ON u.id = s.id_usuario_responsable
                WHERE s.id_software = ? AND s.activo = 1";
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idSoftware);
        mysqli_stmt_execute($stmt);
        $software = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$software) {
            return null;
        }

        $software['almacenamiento'] = [];
        $software['datos_sensibles'] = [];
        $software['tipos_usuario'] = [];
        if ($this->tablaExiste('software_datos_almacenamiento')) {
            $stmt = mysqli_prepare($this->cn, "SELECT * FROM software_datos_almacenamiento WHERE id_software = ? ORDER BY orden_dato ASC, id_dato ASC");
            mysqli_stmt_bind_param($stmt, 'i', $idSoftware);
            mysqli_stmt_execute($stmt);
            $rs = mysqli_stmt_get_result($stmt);
            while ($fila = mysqli_fetch_assoc($rs)) {
                $software['almacenamiento'][] = $fila;
            }
            mysqli_stmt_close($stmt);
        }

        if ($this->tablaExiste('software_datos_sensibles_rel') && $this->tablaExiste('inventario_software_datos_sensibles')) {
            $stmt = mysqli_prepare($this->cn, "SELECT d.id_dato_sensible, d.nombre, d.descripcion
                                               FROM software_datos_sensibles_rel r
                                               INNER JOIN inventario_software_datos_sensibles d ON d.id_dato_sensible = r.id_dato_sensible
                                               WHERE r.id_software = ? AND d.activo = 1
                                               ORDER BY d.nombre ASC");
            mysqli_stmt_bind_param($stmt, 'i', $idSoftware);
            mysqli_stmt_execute($stmt);
            $rs = mysqli_stmt_get_result($stmt);
            while ($fila = mysqli_fetch_assoc($rs)) {
                $software['datos_sensibles'][] = $fila;
            }
            mysqli_stmt_close($stmt);
        }

        if ($this->tablaExiste('software_tipo_usuario_rel') && $this->tablaExiste('inventario_software_tipo_usuario')) {
            $stmt = mysqli_prepare($this->cn, "SELECT t.id_tipo_usuario, t.nombre, t.descripcion
                                               FROM software_tipo_usuario_rel r
                                               INNER JOIN inventario_software_tipo_usuario t ON t.id_tipo_usuario = r.id_tipo_usuario
                                               WHERE r.id_software = ? AND t.activo = 1
                                               ORDER BY t.nombre ASC");
            mysqli_stmt_bind_param($stmt, 'i', $idSoftware);
            mysqli_stmt_execute($stmt);
            $rs = mysqli_stmt_get_result($stmt);
            while ($fila = mysqli_fetch_assoc($rs)) {
                $software['tipos_usuario'][] = $fila;
            }
            mysqli_stmt_close($stmt);
        } elseif (!empty($software['id_tipo_usuario']) && $this->tablaExiste('inventario_software_tipo_usuario')) {
            $stmt = mysqli_prepare($this->cn, "SELECT id_tipo_usuario, nombre, descripcion FROM inventario_software_tipo_usuario WHERE id_tipo_usuario = ? AND activo = 1 LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'i', $software['id_tipo_usuario']);
            mysqli_stmt_execute($stmt);
            $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);
            if ($fila) {
                $software['tipos_usuario'][] = $fila;
            }
        }

        return $software;
    }

    public function obtenerSitioWeb($idSitio)
    {
        $idSitio = (int)$idSitio;
        if ($idSitio <= 0 || !$this->tablaExiste('sitios_web_catalogo')) {
            return null;
        }

        $sql = "SELECT sw.*,
                       COALESCE(NULLIF(c.nom_colegio, ''), CONCAT('Colegio ID ', sw.id_colegio)) AS nom_colegio,
                       CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS responsable
                FROM sitios_web_catalogo sw
                LEFT JOIN colegio c ON c.id_colegio = sw.id_colegio
                LEFT JOIN usuarios u ON u.id = sw.id_usuario_responsable
                WHERE sw.id_sitio = ? AND sw.activo = 1";
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idSitio);
        mysqli_stmt_execute($stmt);
        $sitio = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $sitio ?: null;
    }

    public function guardarSoftware($post, $idUsuario)
    {
        $this->validarTablasBaseSoftware();
        $payload = $this->normalizarPayloadSoftware($post);

        mysqli_begin_transaction($this->cn);
        try {
            $sql = "INSERT INTO software_catalogo (id_colegio, id_usuario_responsable, nombre_software, version_software, cantidad_licencias, tipo_licenciamiento, pagado_por, costo, moneda, proveedor, url_referencia, observaciones, id_usuario, activo, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())";
            $stmt = mysqli_prepare($this->cn, $sql);
            mysqli_stmt_bind_param($stmt, 'iississdssssi', $payload['id_colegio'], $payload['id_usuario_responsable'], $payload['nombre_software'], $payload['version_software'], $payload['cantidad_licencias'], $payload['tipo_licenciamiento'], $payload['pagado_por'], $payload['costo'], $payload['moneda'], $payload['proveedor'], $payload['url_referencia'], $payload['observaciones'], $idUsuario);
            mysqli_stmt_execute($stmt);
            $idSoftware = (int)mysqli_insert_id($this->cn);
            mysqli_stmt_close($stmt);

            $this->guardarDatosAlmacenamiento($idSoftware, $payload['almacenamiento']);
            $this->guardarDatosSensiblesSoftware($idSoftware, $payload['datos_sensibles']);
            $this->guardarTiposUsuarioSoftware($idSoftware, $payload['tipos_usuario']);
            $this->guardarHistorial($idSoftware, 'creacion', 'Software creado', $idUsuario);
            mysqli_commit($this->cn);
            return $idSoftware;
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    public function actualizarSoftware($idSoftware, $post, $idUsuario)
    {
        $this->validarTablasBaseSoftware();
        $idSoftware = (int)$idSoftware;
        if ($idSoftware <= 0) {
            throw new RuntimeException('ID de software invalido.');
        }

        $payload = $this->normalizarPayloadSoftware($post);
        mysqli_begin_transaction($this->cn);
        try {
            $sql = "UPDATE software_catalogo
                    SET id_colegio = ?, id_usuario_responsable = ?, nombre_software = ?, version_software = ?, cantidad_licencias = ?, tipo_licenciamiento = ?, pagado_por = ?, costo = ?, moneda = ?, proveedor = ?, url_referencia = ?, observaciones = ?, updated_at = NOW()
                    WHERE id_software = ?";
            $stmt = mysqli_prepare($this->cn, $sql);
            mysqli_stmt_bind_param($stmt, 'iississdssssi', $payload['id_colegio'], $payload['id_usuario_responsable'], $payload['nombre_software'], $payload['version_software'], $payload['cantidad_licencias'], $payload['tipo_licenciamiento'], $payload['pagado_por'], $payload['costo'], $payload['moneda'], $payload['proveedor'], $payload['url_referencia'], $payload['observaciones'], $idSoftware);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($this->cn, "DELETE FROM software_datos_almacenamiento WHERE id_software = ?");
            mysqli_stmt_bind_param($stmt, 'i', $idSoftware);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $this->guardarDatosAlmacenamiento($idSoftware, $payload['almacenamiento']);
            $this->guardarDatosSensiblesSoftware($idSoftware, $payload['datos_sensibles']);
            $this->guardarTiposUsuarioSoftware($idSoftware, $payload['tipos_usuario']);
            $this->guardarHistorial($idSoftware, 'actualizacion', 'Software actualizado', $idUsuario);
            mysqli_commit($this->cn);
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    public function eliminarLogicoSoftware($idSoftware, $idUsuario)
    {
        $stmt = mysqli_prepare($this->cn, "UPDATE software_catalogo SET activo = 0, updated_at = NOW() WHERE id_software = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idSoftware);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $this->guardarHistorial((int)$idSoftware, 'baja_logica', 'Software marcado como inactivo', (int)$idUsuario);
    }

    public function guardarSitioWeb($post, $idUsuario)
    {
        $this->validarTablaSitios();
        $payload = $this->normalizarPayloadSitio($post);
        $sql = "INSERT INTO sitios_web_catalogo (id_colegio, id_usuario_responsable, nombre_sitio, tipo_sitio, url_sitio, proveedor_hosting, estado_sitio, observaciones, id_usuario, activo, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())";
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 'iissssssi', $payload['id_colegio'], $payload['id_usuario_responsable'], $payload['nombre_sitio'], $payload['tipo_sitio'], $payload['url_sitio'], $payload['proveedor_hosting'], $payload['estado_sitio'], $payload['observaciones'], $idUsuario);
        mysqli_stmt_execute($stmt);
        $idSitio = (int)mysqli_insert_id($this->cn);
        mysqli_stmt_close($stmt);
        return $idSitio;
    }

    public function actualizarSitioWeb($idSitio, $post)
    {
        $this->validarTablaSitios();
        $payload = $this->normalizarPayloadSitio($post);
        $sql = "UPDATE sitios_web_catalogo
                SET id_colegio = ?, id_usuario_responsable = ?, nombre_sitio = ?, tipo_sitio = ?, url_sitio = ?, proveedor_hosting = ?, estado_sitio = ?, observaciones = ?, updated_at = NOW()
                WHERE id_sitio = ?";
        $stmt = mysqli_prepare($this->cn, $sql);
        mysqli_stmt_bind_param($stmt, 'iissssssi', $payload['id_colegio'], $payload['id_usuario_responsable'], $payload['nombre_sitio'], $payload['tipo_sitio'], $payload['url_sitio'], $payload['proveedor_hosting'], $payload['estado_sitio'], $payload['observaciones'], $idSitio);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function eliminarLogicoSitioWeb($idSitio)
    {
        $stmt = mysqli_prepare($this->cn, "UPDATE sitios_web_catalogo SET activo = 0, updated_at = NOW() WHERE id_sitio = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idSitio);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    private function normalizarPayloadSoftware($post)
    {
        $payload = [
            'id_colegio' => (int)($post['id_colegio'] ?? 0),
            'id_usuario_responsable' => (int)($post['id_usuario_responsable'] ?? 0),
            'nombre_software' => trim((string)($post['nombre_software'] ?? '')),
            'version_software' => trim((string)($post['version_software'] ?? '')),
            'cantidad_licencias' => max(1, (int)($post['cantidad_licencias'] ?? 1)),
            'tipo_licenciamiento' => trim((string)($post['tipo_licenciamiento'] ?? '')),
            'pagado_por' => trim((string)($post['pagado_por'] ?? '')),
            'costo' => (float)($post['costo'] ?? 0),
            'moneda' => trim((string)($post['moneda'] ?? 'USD')),
            'proveedor' => trim((string)($post['proveedor'] ?? '')),
            'url_referencia' => trim((string)($post['url_referencia'] ?? '')),
            'observaciones' => trim((string)($post['observaciones'] ?? '')),
            'almacenamiento' => [],
            'datos_sensibles' => [],
            'tipos_usuario' => [],
        ];

        if ($payload['id_colegio'] <= 0) {
            throw new RuntimeException('Debes seleccionar un colegio.');
        }
        if ($payload['nombre_software'] === '') {
            throw new RuntimeException('Debes indicar el nombre del software.');
        }
        if (!in_array($payload['tipo_licenciamiento'], $this->tiposLicenciamiento, true)) {
            throw new RuntimeException('Tipo de licenciamiento invalido.');
        }
        if (!in_array($payload['pagado_por'], $this->pagadoPor, true)) {
            throw new RuntimeException('Dato pagado por invalido.');
        }
        if (!in_array($payload['moneda'], $this->monedas, true)) {
            throw new RuntimeException('Moneda invalida.');
        }

        foreach (($post['almacenamiento'] ?? []) as $index => $fila) {
            $nombre = trim((string)($fila['nombre_contacto'] ?? ''));
            $rut = trim((string)($fila['rut_contacto'] ?? ''));
            $email = trim((string)($fila['email_contacto'] ?? ''));
            $otros = trim((string)($fila['otros_datos'] ?? ''));
            if ($nombre === '' && $rut === '' && $email === '' && $otros === '') {
                continue;
            }
            $payload['almacenamiento'][] = [
                'nombre_contacto' => $nombre,
                'rut_contacto' => $rut,
                'email_contacto' => $email,
                'otros_datos' => $otros,
                'orden_dato' => $index + 1,
            ];
        }

        $catalogo = [];
        foreach ($this->obtenerDatosSensiblesCatalogo() as $dato) {
            $catalogo[(int)$dato['id_dato_sensible']] = true;
        }

        foreach ((array)($post['datos_sensibles'] ?? []) as $idDato) {
            $idDato = (int)$idDato;
            if ($idDato <= 0) {
                continue;
            }
            if (!empty($catalogo) && !isset($catalogo[$idDato])) {
                continue;
            }
            $payload['datos_sensibles'][] = $idDato;
        }
        $payload['datos_sensibles'] = array_values(array_unique($payload['datos_sensibles']));

        $catalogoTiposUsuario = [];
        foreach ($this->obtenerTiposUsuarioCatalogo() as $tipoUsuario) {
            $catalogoTiposUsuario[(int)$tipoUsuario['id_tipo_usuario']] = true;
        }

        foreach ((array)($post['tipos_usuario'] ?? []) as $idTipoUsuario) {
            $idTipoUsuario = (int)$idTipoUsuario;
            if ($idTipoUsuario <= 0) {
                continue;
            }
            if (!empty($catalogoTiposUsuario) && !isset($catalogoTiposUsuario[$idTipoUsuario])) {
                continue;
            }
            $payload['tipos_usuario'][] = $idTipoUsuario;
        }
        $payload['tipos_usuario'] = array_values(array_unique($payload['tipos_usuario']));

        return $payload;
    }

    private function normalizarPayloadSitio($post)
    {
        $payload = [
            'id_colegio' => (int)($post['id_colegio'] ?? 0),
            'id_usuario_responsable' => (int)($post['id_usuario_responsable'] ?? 0),
            'nombre_sitio' => trim((string)($post['nombre_sitio'] ?? '')),
            'tipo_sitio' => trim((string)($post['tipo_sitio'] ?? '')),
            'url_sitio' => trim((string)($post['url_sitio'] ?? '')),
            'proveedor_hosting' => trim((string)($post['proveedor_hosting'] ?? '')),
            'estado_sitio' => trim((string)($post['estado_sitio'] ?? 'Activo')),
            'observaciones' => trim((string)($post['observaciones'] ?? '')),
        ];

        if ($payload['id_colegio'] <= 0) {
            throw new RuntimeException('Debes seleccionar un colegio.');
        }
        if ($payload['nombre_sitio'] === '') {
            throw new RuntimeException('Debes indicar el nombre del sitio.');
        }
        if (!in_array($payload['tipo_sitio'], $this->tiposSitio, true)) {
            throw new RuntimeException('Tipo de sitio invalido.');
        }
        if (!in_array($payload['estado_sitio'], $this->estadosSitio, true)) {
            throw new RuntimeException('Estado del sitio invalido.');
        }

        return $payload;
    }

    private function guardarDatosAlmacenamiento($idSoftware, $filas)
    {
        foreach ($filas as $fila) {
            $stmt = mysqli_prepare($this->cn, "INSERT INTO software_datos_almacenamiento (id_software, nombre_contacto, rut_contacto, email_contacto, otros_datos, orden_dato) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'issssi', $idSoftware, $fila['nombre_contacto'], $fila['rut_contacto'], $fila['email_contacto'], $fila['otros_datos'], $fila['orden_dato']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    private function guardarDatosSensiblesSoftware($idSoftware, $idsDatosSensibles)
    {
        if (!$this->tablaExiste('software_datos_sensibles_rel')) {
            return;
        }

        $stmt = mysqli_prepare($this->cn, "DELETE FROM software_datos_sensibles_rel WHERE id_software = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idSoftware);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$this->tablaExiste('inventario_software_datos_sensibles')) {
            return;
        }

        foreach ($idsDatosSensibles as $idDatoSensible) {
            $idDatoSensible = (int)$idDatoSensible;
            if ($idDatoSensible <= 0) {
                continue;
            }
            $stmt = mysqli_prepare($this->cn, "INSERT INTO software_datos_sensibles_rel (id_software, id_dato_sensible, created_at) VALUES (?, ?, NOW())");
            mysqli_stmt_bind_param($stmt, 'ii', $idSoftware, $idDatoSensible);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    private function guardarTiposUsuarioSoftware($idSoftware, $idsTiposUsuario)
    {
        if (!$this->tablaExiste('software_tipo_usuario_rel')) {
            return;
        }

        $stmt = mysqli_prepare($this->cn, "DELETE FROM software_tipo_usuario_rel WHERE id_software = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idSoftware);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$this->tablaExiste('inventario_software_tipo_usuario')) {
            return;
        }

        foreach ($idsTiposUsuario as $idTipoUsuario) {
            $idTipoUsuario = (int)$idTipoUsuario;
            if ($idTipoUsuario <= 0) {
                continue;
            }
            $stmt = mysqli_prepare($this->cn, "INSERT INTO software_tipo_usuario_rel (id_software, id_tipo_usuario, created_at) VALUES (?, ?, NOW())");
            mysqli_stmt_bind_param($stmt, 'ii', $idSoftware, $idTipoUsuario);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    private function obtenerMapaDatosSensiblesPorSoftware($idsSoftware)
    {
        $mapa = [];
        $idsSoftware = array_values(array_unique(array_filter(array_map('intval', (array)$idsSoftware))));
        if (
            empty($idsSoftware) ||
            !$this->tablaExiste('software_datos_sensibles_rel') ||
            !$this->tablaExiste('inventario_software_datos_sensibles')
        ) {
            return $mapa;
        }

        $idsSql = implode(',', $idsSoftware);
        $sql = "SELECT r.id_software,
                       GROUP_CONCAT(DISTINCT d.nombre ORDER BY d.nombre SEPARATOR ', ') AS datos_sensibles
                FROM software_datos_sensibles_rel r
                INNER JOIN inventario_software_datos_sensibles d ON d.id_dato_sensible = r.id_dato_sensible
                WHERE d.activo = 1 AND r.id_software IN ({$idsSql})
                GROUP BY r.id_software";
        $rs = $this->db->consulta($sql);
        while ($fila = $this->db->fetch_assoc($rs)) {
            $mapa[(int)$fila['id_software']] = (string)($fila['datos_sensibles'] ?? '');
        }

        return $mapa;
    }

    private function guardarHistorial($idSoftware, $accion, $detalle, $idUsuario)
    {
        if (!$this->tablaExiste('software_historial')) {
            return;
        }
        $stmt = mysqli_prepare($this->cn, "INSERT INTO software_historial (id_software, accion, detalle, id_usuario, fecha_registro) VALUES (?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, 'issi', $idSoftware, $accion, $detalle, $idUsuario);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    private function validarTablasBaseSoftware()
    {
        foreach (['software_catalogo', 'software_datos_almacenamiento'] as $tabla) {
            if (!$this->tablaExiste($tabla)) {
                throw new RuntimeException('Falta la tabla ' . $tabla . '. Ejecuta el SQL del modulo.');
            }
        }
    }

    private function validarTablaSitios()
    {
        if (!$this->tablaExiste('sitios_web_catalogo')) {
            throw new RuntimeException('Falta la tabla sitios_web_catalogo. Ejecuta el SQL del modulo.');
        }
    }

    private function bindParams($stmt, $types, $params)
    {
        if (!$stmt) {
            throw new RuntimeException('No fue posible preparar la consulta SQL.');
        }
        if ($types !== '' && !empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
    }
}
