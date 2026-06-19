<?php

if (!function_exists('di_h')) {
    function di_h($valor): string
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('di_money')) {
    function di_money($valor): string
    {
        return '$' . number_format((float)$valor, 0, ',', '.');
    }
}

if (!function_exists('di_sql_value')) {
    function di_sql_value(MySQL $db, $value, string $type): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if ($type === 'i') {
            return (string)(int)$value;
        }
        if ($type === 'd') {
            return (string)(float)$value;
        }

        return "'" . $db->escape_string((string)$value) . "'";
    }
}

if (!function_exists('di_sql_with_params')) {
    function di_sql_with_params(MySQL $db, string $sql, string $types = '', array $params = []): string
    {
        if ($types === '' || empty($params)) {
            return $sql;
        }

        $parts = explode('?', $sql);
        $finalSql = array_shift($parts);
        foreach ($params as $index => $param) {
            $type = $types[$index] ?? 's';
            $finalSql .= di_sql_value($db, $param, $type) . (array_shift($parts) ?? '');
        }

        return $finalSql . implode('?', $parts);
    }
}

if (!function_exists('di_scalar')) {
    function di_scalar(MySQL $db, string $sql, string $types = '', array $params = [])
    {
        $rows = di_rows($db, $sql, $types, $params);
        return $rows[0]['valor'] ?? null;
    }
}

if (!function_exists('di_rows')) {
    function di_rows(MySQL $db, string $sql, string $types = '', array $params = []): array
    {
        $res = $db->consulta(di_sql_with_params($db, $sql, $types, $params));
        $rows = [];
        if ($res) {
            while ($row = $db->fetch_assoc($res)) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
}

if (!function_exists('di_table_exists')) {
    function di_table_exists(MySQL $db, string $table): bool
    {
        return (int)di_scalar($db, "SELECT COUNT(*) AS valor FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", 's', [$table]) > 0;
    }
}

if (!function_exists('di_column_exists')) {
    function di_column_exists(MySQL $db, string $table, string $column): bool
    {
        return (int)di_scalar($db, "SELECT COUNT(*) AS valor FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?", 'ss', [$table, $column]) > 0;
    }
}

if (!function_exists('di_parse_filters')) {
    function di_parse_filters(array $source): array
    {
        $today = new DateTime('today');
        $desde = trim((string)($source['desde'] ?? ''));
        $hasta = trim((string)($source['hasta'] ?? ''));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
            $desde = (clone $today)->modify('-12 months')->format('Y-m-d');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            $hasta = $today->format('Y-m-d');
        }
        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        return [
            'id_colegio' => (int)($source['id_colegio'] ?? 0),
            'desde' => $desde,
            'hasta' => $hasta,
        ];
    }
}

if (!function_exists('di_context')) {
    function di_context(MySQL $db, Funciones $funciones, int $idUsuario, array $filters): array
    {
        $colegios = $funciones->obtenerColegios($idUsuario);
        $idsPermitidos = array_map(static fn($c) => (int)$c['id_colegio'], $colegios);
        $usuariosGlobales = [6, 8, 9, 24, 42];
        $esAdmin = in_array($idUsuario, $usuariosGlobales, true);

        if (!$esAdmin && empty($idsPermitidos)) {
            return [
                'colegios' => [],
                'ids_permitidos' => [],
                'id_colegio' => 0,
                'where_colegio' => ' AND 1=0 ',
                'types_colegio' => '',
                'params_colegio' => [],
                'colegio_label' => 'Sin colegio asignado',
                'es_admin' => false,
            ];
        }

        $idSeleccionado = (int)$filters['id_colegio'];
        if ($idSeleccionado > 0 && !in_array($idSeleccionado, $idsPermitidos, true)) {
            $idSeleccionado = 0;
        }

        $whereColegio = '';
        $types = '';
        $params = [];
        if ($idSeleccionado > 0) {
            $whereColegio = ' AND base.id_colegio = ? ';
            $types = 'i';
            $params[] = $idSeleccionado;
        } elseif (!$esAdmin && !empty($idsPermitidos)) {
            $placeholders = implode(',', array_fill(0, count($idsPermitidos), '?'));
            $whereColegio = " AND base.id_colegio IN ($placeholders) ";
            $types = str_repeat('i', count($idsPermitidos));
            $params = $idsPermitidos;
        }

        $label = 'Todos los colegios';
        if ($idSeleccionado > 0) {
            foreach ($colegios as $colegio) {
                if ((int)$colegio['id_colegio'] === $idSeleccionado) {
                    $label = (string)$colegio['nom_colegio'];
                    break;
                }
            }
        }

        return [
            'colegios' => $colegios,
            'ids_permitidos' => $idsPermitidos,
            'id_colegio' => $idSeleccionado,
            'where_colegio' => $whereColegio,
            'types_colegio' => $types,
            'params_colegio' => $params,
            'colegio_label' => $label,
            'es_admin' => $esAdmin,
        ];
    }
}

if (!function_exists('di_where_asset')) {
    function di_where_asset(string $alias, array $ctx, string $fechaExpr = ''): array
    {
        $where = ' WHERE 1=1 ';
        $types = '';
        $params = [];

        if ($ctx['where_colegio'] !== '') {
            $where .= str_replace('base.', $alias . '.', $ctx['where_colegio']);
            $types .= $ctx['types_colegio'];
            $params = array_merge($params, $ctx['params_colegio']);
        }
        if ($fechaExpr !== '') {
            $where .= " AND ($fechaExpr IS NULL OR $fechaExpr = '0000-00-00' OR ($fechaExpr BETWEEN ? AND ?)) ";
            $types .= 'ss';
            $params[] = $ctx['filters']['desde'];
            $params[] = $ctx['filters']['hasta'];
        }

        return [$where, $types, $params];
    }
}

if (!function_exists('di_dashboard_data')) {
    function di_dashboard_data(MySQL $db, Funciones $funciones, int $idUsuario, array $filters): array
    {
        $ctx = di_context($db, $funciones, $idUsuario, $filters);
        $ctx['filters'] = $filters;

        $equiposTieneEliminado = di_column_exists($db, 'equipos', 'eliminado');
        $monitoresTieneEliminado = di_column_exists($db, 'monitores', 'eliminado');
        $equiposTieneUbicacion = di_column_exists($db, 'equipos', 'id_ubicacion');
        $monitoresTieneUbicacion = di_column_exists($db, 'monitores', 'id_ubicacion');

        $filtroPc = di_where_asset('e', $ctx, 'ec.fecha_compra');
        $filtroMon = di_where_asset('m', $ctx, 'mc.fecha_compra');
        if ($equiposTieneEliminado) {
            $filtroPc[0] .= ' AND COALESCE(e.eliminado,0) = 0 ';
        }
        if ($monitoresTieneEliminado) {
            $filtroMon[0] .= ' AND COALESCE(m.eliminado,0) = 0 ';
        }

        $pcRows = di_rows($db, "
            SELECT COUNT(*) total_pc,
                   SUM(CASE WHEN e.id_estado = 1 THEN 1 ELSE 0 END) activos,
                   SUM(CASE WHEN e.id_estado = 3 THEN 1 ELSE 0 END) reparacion,
                   SUM(CASE WHEN e.id_estado = 4 THEN 1 ELSE 0 END) baja,
                   COALESCE(SUM(ec.valor_equipo),0) valor
            FROM equipos e
            LEFT JOIN equipos_compra ec ON ec.id_equipo = e.id_equipo
            {$filtroPc[0]}
        ", $filtroPc[1], $filtroPc[2]);

        $monRows = di_rows($db, "
            SELECT COUNT(*) total_monitores,
                   SUM(CASE WHEN m.id_estado = 1 THEN 1 ELSE 0 END) activos,
                   SUM(CASE WHEN m.id_estado = 3 THEN 1 ELSE 0 END) reparacion,
                   SUM(CASE WHEN m.id_estado = 4 THEN 1 ELSE 0 END) baja,
                   COALESCE(SUM(mc.valor_monitor),0) valor
            FROM monitores m
            LEFT JOIN monitor_compra mc ON mc.id_monitor = m.id_monitor
            {$filtroMon[0]}
        ", $filtroMon[1], $filtroMon[2]);

        $pc = $pcRows[0] ?? [];
        $mon = $monRows[0] ?? [];
        $kpis = [
            'total_pc' => (int)($pc['total_pc'] ?? 0),
            'total_monitores' => (int)($mon['total_monitores'] ?? 0),
            'activos' => (int)($pc['activos'] ?? 0) + (int)($mon['activos'] ?? 0),
            'reparacion' => (int)($pc['reparacion'] ?? 0) + (int)($mon['reparacion'] ?? 0),
            'baja' => (int)($pc['baja'] ?? 0) + (int)($mon['baja'] ?? 0),
            'valor' => (float)($pc['valor'] ?? 0) + (float)($mon['valor'] ?? 0),
        ];

        $resumenColegio = di_rows($db, "
            SELECT c.id_colegio, c.nom_colegio,
                   SUM(base.pc) pc,
                   SUM(base.monitores) monitores,
                   SUM(base.activos) activos,
                   SUM(base.reparacion) reparacion,
                   SUM(base.baja) baja,
                   SUM(base.valor) valor
            FROM (
                SELECT e.id_colegio, 1 pc, 0 monitores,
                       CASE WHEN e.id_estado = 1 THEN 1 ELSE 0 END activos,
                       CASE WHEN e.id_estado = 3 THEN 1 ELSE 0 END reparacion,
                       CASE WHEN e.id_estado = 4 THEN 1 ELSE 0 END baja,
                       COALESCE(ec.valor_equipo,0) valor
                FROM equipos e
                LEFT JOIN equipos_compra ec ON ec.id_equipo = e.id_equipo
                WHERE 1=1 " . ($equiposTieneEliminado ? " AND COALESCE(e.eliminado,0)=0 " : "") . "
                  AND (ec.fecha_compra IS NULL OR ec.fecha_compra = '0000-00-00' OR (ec.fecha_compra BETWEEN ? AND ?))
                UNION ALL
                SELECT m.id_colegio, 0 pc, 1 monitores,
                       CASE WHEN m.id_estado = 1 THEN 1 ELSE 0 END activos,
                       CASE WHEN m.id_estado = 3 THEN 1 ELSE 0 END reparacion,
                       CASE WHEN m.id_estado = 4 THEN 1 ELSE 0 END baja,
                       COALESCE(mc.valor_monitor,0) valor
                FROM monitores m
                LEFT JOIN monitor_compra mc ON mc.id_monitor = m.id_monitor
                WHERE 1=1 " . ($monitoresTieneEliminado ? " AND COALESCE(m.eliminado,0)=0 " : "") . "
                  AND (mc.fecha_compra IS NULL OR mc.fecha_compra = '0000-00-00' OR (mc.fecha_compra BETWEEN ? AND ?))
            ) base
            INNER JOIN colegio c ON c.id_colegio = base.id_colegio
            WHERE 1=1 {$ctx['where_colegio']}
            GROUP BY c.id_colegio, c.nom_colegio
            ORDER BY c.nom_colegio ASC
        ", 'ssss' . $ctx['types_colegio'], array_merge([$filters['desde'], $filters['hasta'], $filters['desde'], $filters['hasta']], $ctx['params_colegio']));

        $estadoGeneral = di_rows($db, "
            SELECT estado, SUM(total) total
            FROM (
                SELECT COALESCE(ee.nombre_estado, CONCAT('Estado ', e.id_estado)) estado, COUNT(*) total
                FROM equipos e
                LEFT JOIN equipos_compra ec ON ec.id_equipo = e.id_equipo
                LEFT JOIN estado_equipo ee ON ee.id_estado = e.id_estado
                {$filtroPc[0]}
                GROUP BY estado
                UNION ALL
                SELECT COALESCE(ee.nombre_estado, CONCAT('Estado ', m.id_estado)) estado, COUNT(*) total
                FROM monitores m
                LEFT JOIN monitor_compra mc ON mc.id_monitor = m.id_monitor
                LEFT JOIN estado_equipo ee ON ee.id_estado = m.id_estado
                {$filtroMon[0]}
                GROUP BY estado
            ) x
            GROUP BY estado
            ORDER BY total DESC
        ", $filtroPc[1] . $filtroMon[1], array_merge($filtroPc[2], $filtroMon[2]));

        $topUbicacionesParts = [];
        $topTypes = '';
        $topParams = [];
        if ($equiposTieneUbicacion) {
            $topUbicacionesParts[] = "SELECT e.id_colegio, e.id_ubicacion, COUNT(*) total FROM equipos e WHERE e.id_estado = 1 " . ($equiposTieneEliminado ? " AND COALESCE(e.eliminado,0)=0 " : "") . " GROUP BY e.id_colegio, e.id_ubicacion";
        }
        if ($monitoresTieneUbicacion) {
            $topUbicacionesParts[] = "SELECT m.id_colegio, m.id_ubicacion, COUNT(*) total FROM monitores m WHERE m.id_estado = 1 " . ($monitoresTieneEliminado ? " AND COALESCE(m.eliminado,0)=0 " : "") . " GROUP BY m.id_colegio, m.id_ubicacion";
        }
        $topUbicaciones = [];
        if ($topUbicacionesParts) {
            $topUbicaciones = di_rows($db, "
                SELECT COALESCE(eu.nombre_ubicacion, 'Sin ubicacion') ubicacion, c.nom_colegio, SUM(base.total) total
                FROM (" . implode(' UNION ALL ', $topUbicacionesParts) . ") base
                LEFT JOIN equipo_ubicacion eu ON eu.id_ubicacion = base.id_ubicacion
                LEFT JOIN colegio c ON c.id_colegio = base.id_colegio
                WHERE 1=1 {$ctx['where_colegio']}
                GROUP BY ubicacion, c.nom_colegio
                ORDER BY total DESC
                LIMIT 10
            ", $ctx['types_colegio'], $ctx['params_colegio']);
        }

        $antiguedad = di_rows($db, "
            SELECT bucket, SUM(total) total FROM (
                SELECT CASE
                    WHEN ec.fecha_compra IS NULL OR ec.fecha_compra = '0000-00-00' THEN 'Sin fecha'
                    WHEN ec.fecha_compra >= DATE_SUB(CURDATE(), INTERVAL 3 YEAR) THEN 'Menos de 3 años'
                    WHEN ec.fecha_compra >= DATE_SUB(CURDATE(), INTERVAL 5 YEAR) THEN '3 a 5 años'
                    ELSE 'Más de 5 años'
                END bucket, COUNT(*) total
                FROM equipos e
                LEFT JOIN equipos_compra ec ON ec.id_equipo = e.id_equipo
                {$filtroPc[0]}
                GROUP BY bucket
                UNION ALL
                SELECT CASE
                    WHEN mc.fecha_compra IS NULL OR mc.fecha_compra = '0000-00-00' THEN 'Sin fecha'
                    WHEN mc.fecha_compra >= DATE_SUB(CURDATE(), INTERVAL 3 YEAR) THEN 'Menos de 3 años'
                    WHEN mc.fecha_compra >= DATE_SUB(CURDATE(), INTERVAL 5 YEAR) THEN '3 a 5 años'
                    ELSE 'Más de 5 años'
                END bucket, COUNT(*) total
                FROM monitores m
                LEFT JOIN monitor_compra mc ON mc.id_monitor = m.id_monitor
                {$filtroMon[0]}
                GROUP BY bucket
            ) x GROUP BY bucket
        ", $filtroPc[1] . $filtroMon[1], array_merge($filtroPc[2], $filtroMon[2]));

        $movimientos = di_rows($db, "
            SELECT * FROM (
                SELECT em.fecha_movimiento fecha,
                       CONVERT('PC' USING utf8mb4) COLLATE utf8mb4_general_ci tipo_activo,
                       CONVERT(COALESCE(e.nombre_equipo, '') USING utf8mb4) COLLATE utf8mb4_general_ci activo,
                       CONVERT(COALESCE(uo.nombre_ubicacion, 'Sin origen') USING utf8mb4) COLLATE utf8mb4_general_ci origen,
                       CONVERT(COALESCE(ud.nombre_ubicacion, 'Sin destino') USING utf8mb4) COLLATE utf8mb4_general_ci destino,
                       CONVERT(CONCAT(COALESCE(us.nombre,''), ' ', COALESCE(us.apellido_paterno,'')) USING utf8mb4) COLLATE utf8mb4_general_ci usuario,
                       CONVERT(COALESCE(em.motivo, '') USING utf8mb4) COLLATE utf8mb4_general_ci motivo,
                       e.id_colegio
                FROM equipo_movimiento em
                INNER JOIN equipos e ON e.id_equipo = em.id_equipo
                LEFT JOIN equipo_ubicacion uo ON uo.id_ubicacion = em.id_ubicacion_origen
                LEFT JOIN equipo_ubicacion ud ON ud.id_ubicacion = em.id_ubicacion_destino
                LEFT JOIN usuarios us ON us.id = em.id_usuario_movimiento
                UNION ALL
                SELECT mm.fecha_movimiento fecha,
                       CONVERT('Monitor' USING utf8mb4) COLLATE utf8mb4_general_ci tipo_activo,
                       CONVERT(COALESCE(m.nombre_monitor, '') USING utf8mb4) COLLATE utf8mb4_general_ci activo,
                       CONVERT(COALESCE(uo.nombre_ubicacion, 'Sin origen') USING utf8mb4) COLLATE utf8mb4_general_ci origen,
                       CONVERT(COALESCE(ud.nombre_ubicacion, 'Sin destino') USING utf8mb4) COLLATE utf8mb4_general_ci destino,
                       CONVERT(CONCAT(COALESCE(us.nombre,''), ' ', COALESCE(us.apellido_paterno,'')) USING utf8mb4) COLLATE utf8mb4_general_ci usuario,
                       CONVERT(COALESCE(mm.motivo, '') USING utf8mb4) COLLATE utf8mb4_general_ci motivo,
                       m.id_colegio
                FROM monitor_movimiento mm
                INNER JOIN monitores m ON m.id_monitor = mm.id_monitor
                LEFT JOIN equipo_ubicacion uo ON uo.id_ubicacion = mm.id_ubicacion_origen
                LEFT JOIN equipo_ubicacion ud ON ud.id_ubicacion = mm.id_ubicacion_destino
                LEFT JOIN usuarios us ON us.id = mm.id_usuario_movimiento
            ) base
            WHERE 1=1 {$ctx['where_colegio']}
            ORDER BY fecha DESC
            LIMIT 15
        ", $ctx['types_colegio'], $ctx['params_colegio']);

        $proximos5 = di_rows($db, "
            SELECT e.nombre_equipo equipo, e.tipo_pc tipo, c.nom_colegio colegio,
                   COALESCE(eu.nombre_ubicacion, 'Sin ubicacion') ubicacion,
                   ec.fecha_compra,
                   TIMESTAMPDIFF(YEAR, ec.fecha_compra, CURDATE()) antiguedad
            FROM equipos e
            INNER JOIN equipos_compra ec ON ec.id_equipo = e.id_equipo
            LEFT JOIN colegio c ON c.id_colegio = e.id_colegio
            LEFT JOIN equipo_ubicacion eu ON " . ($equiposTieneUbicacion ? "eu.id_ubicacion = e.id_ubicacion" : "1=0") . "
            WHERE ec.fecha_compra IS NOT NULL
              AND ec.fecha_compra <> '0000-00-00'
              AND ec.fecha_compra BETWEEN DATE_SUB(CURDATE(), INTERVAL 5 YEAR) AND DATE_SUB(CURDATE(), INTERVAL 4 YEAR)
              " . ($equiposTieneEliminado ? " AND COALESCE(e.eliminado,0)=0 " : "") . "
              " . str_replace('base.', 'e.', $ctx['where_colegio']) . "
            ORDER BY ec.fecha_compra ASC
            LIMIT 20
        ", $ctx['types_colegio'], $ctx['params_colegio']);

        return [
            'ctx' => $ctx,
            'kpis' => $kpis,
            'resumen_colegio' => $resumenColegio,
            'estado_general' => $estadoGeneral,
            'top_ubicaciones' => $topUbicaciones,
            'antiguedad' => $antiguedad,
            'movimientos' => $movimientos,
            'proximos5' => $proximos5,
        ];
    }
}
