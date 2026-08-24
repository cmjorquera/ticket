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
        $whereActivo = '';
        if ($this->columnaExiste('colegio', 'activo')) {
            $whereActivo = 'WHERE activo = 1';
        } elseif ($this->columnaExiste('colegio', 'estado')) {
            $whereActivo = 'WHERE estado = 1';
        }
        $rs = $this->db->consulta("SELECT id_colegio, nom_colegio FROM colegio {$whereActivo} ORDER BY nom_colegio ASC");
        while ($fila = $this->db->fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function obtenerAlcanceInventario(int $idUsuario): array
    {
        if ($idUsuario <= 0 || !$this->tablaExiste('usuario_colegio')) {
            return [
                'id_perfil' => 1,
                'colegios' => [],
                'ids_colegio' => [],
                'id_colegio_predeterminado' => 0,
                'mostrar_filtro_colegio' => false,
                'mostrar_columna_colegio' => false,
            ];
        }

        $condicionColegioActivo = '';
        if ($this->columnaExiste('colegio', 'activo')) {
            $condicionColegioActivo = 'AND c.activo = 1';
        } elseif ($this->columnaExiste('colegio', 'estado')) {
            $condicionColegioActivo = 'AND c.estado = 1';
        }

        $stmt = mysqli_prepare($this->cn, "
            SELECT uc.id_colegio, uc.id_perfil, c.nom_colegio
            FROM usuario_colegio uc
            INNER JOIN colegio c ON c.id_colegio = uc.id_colegio
            WHERE uc.id_usuario = ?
              AND uc.estado = 1
              {$condicionColegioActivo}
            ORDER BY uc.id_perfil DESC, c.nom_colegio ASC
        ");
        mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);

        $filas = [];
        $perfil = 1;
        while ($fila = mysqli_fetch_assoc($rs)) {
            $filas[] = $fila;
            $perfil = max($perfil, (int)($fila['id_perfil'] ?? 1));
        }
        mysqli_stmt_close($stmt);

        if ($perfil >= 3) {
            $colegios = $this->obtenerColegios();
            return [
                'id_perfil' => 3,
                'colegios' => $colegios,
                'ids_colegio' => array_map(static fn($c) => (int)$c['id_colegio'], $colegios),
                'id_colegio_predeterminado' => 0,
                'mostrar_filtro_colegio' => true,
                'mostrar_columna_colegio' => true,
            ];
        }

        $colegios = [];
        $vistos = [];
        foreach ($filas as $fila) {
            $idColegio = (int)($fila['id_colegio'] ?? 0);
            if ($idColegio <= 0 || isset($vistos[$idColegio])) {
                continue;
            }
            $vistos[$idColegio] = true;
            $colegios[] = [
                'id_colegio' => $idColegio,
                'nom_colegio' => $fila['nom_colegio'] ?? '',
            ];
        }

        if ($perfil <= 1 && count($colegios) > 1) {
            // Perfil 1 no usa selector de colegio; si tiene mas de una relacion activa,
            // Inventario toma la primera fila ordenada por nombre para mantener un colegio unico.
            $colegios = [$colegios[0]];
        }

        return [
            'id_perfil' => $perfil >= 2 ? 2 : 1,
            'colegios' => $colegios,
            'ids_colegio' => array_map(static fn($c) => (int)$c['id_colegio'], $colegios),
            'id_colegio_predeterminado' => (int)($colegios[0]['id_colegio'] ?? 0),
            'mostrar_filtro_colegio' => $perfil >= 2,
            'mostrar_columna_colegio' => $perfil >= 2,
        ];
    }

    public function normalizarFiltrosPorAlcance(array $filtros, array $alcance): array
    {
        $perfil = (int)($alcance['id_perfil'] ?? 1);
        $idsPermitidos = array_values(array_map('intval', $alcance['ids_colegio'] ?? []));
        $idSolicitado = (int)($filtros['id_colegio'] ?? 0);

        unset($filtros['ids_colegio']);

        if ($perfil >= 3) {
            if ($idSolicitado > 0 && !in_array($idSolicitado, $idsPermitidos, true)) {
                throw new RuntimeException('El colegio solicitado no esta disponible para inventario.');
            }
            $filtros['id_colegio'] = $idSolicitado;
            $this->validarResponsableFiltroPorColegios($filtros, $idSolicitado > 0 ? [$idSolicitado] : $idsPermitidos);
            return $filtros;
        }

        if (empty($idsPermitidos)) {
            $filtros['id_colegio'] = -1;
            return $filtros;
        }

        if ($perfil === 2) {
            if ($idSolicitado > 0) {
                if (!in_array($idSolicitado, $idsPermitidos, true)) {
                    throw new RuntimeException('No tienes permiso para consultar ese colegio.');
                }
                $filtros['id_colegio'] = $idSolicitado;
            } else {
                $filtros['id_colegio'] = 0;
                $filtros['ids_colegio'] = $idsPermitidos;
            }
            $this->validarResponsableFiltroPorColegios($filtros, !empty($filtros['id_colegio']) ? [(int)$filtros['id_colegio']] : $idsPermitidos);
            return $filtros;
        }

        $idUnico = (int)($alcance['id_colegio_predeterminado'] ?? 0);
        if ($idSolicitado > 0 && $idSolicitado !== $idUnico) {
            throw new RuntimeException('No tienes permiso para consultar ese colegio.');
        }
        $filtros['id_colegio'] = $idUnico;
        $this->validarResponsableFiltroPorColegios($filtros, [$idUnico]);
        return $filtros;
    }

    public function normalizarFiltrosDashboard(array $filtros, array $alcance): array
    {
        $tipoEquipo = strtolower(trim((string)($filtros['tipo_equipo'] ?? 'pc')));
        if (!in_array($tipoEquipo, ['pc', 'tablet', 'monitor', 'impresora'], true)) {
            $tipoEquipo = 'pc';
        }

        $normalizados = [
            'id_colegio' => (int)($filtros['id_colegio'] ?? 0),
            'tipo_equipo' => $tipoEquipo,
        ];

        return $this->normalizarFiltrosPorAlcance($normalizados, $alcance);
    }

    public function colegioPermitidoPorAlcance(int $idColegio, array $alcance): bool
    {
        if ($idColegio <= 0) {
            return false;
        }
        $ids = array_values(array_map('intval', $alcance['ids_colegio'] ?? []));
        return in_array($idColegio, $ids, true);
    }

    private function estadoEquipoActivoExiste(int $idEstado): bool
    {
        if ($idEstado <= 0 || !$this->tablaExiste('estado_equipo')) {
            return false;
        }

        $whereActivo = $this->columnaExiste('estado_equipo', 'estado') ? 'AND estado = 1' : '';
        $stmt = mysqli_prepare($this->cn, "SELECT 1 FROM estado_equipo WHERE id_estado = ? {$whereActivo} LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $idEstado);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $existe = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        return $existe;
    }

    private function validarResponsableFiltroPorColegios(array $filtros, array $idsColegio): void
    {
        $idUsuario = (int)($filtros['id_usuario_asignado'] ?? 0);
        if ($idUsuario <= 0) {
            return;
        }

        $idsColegio = array_values(array_unique(array_filter(array_map('intval', $idsColegio))));
        if (empty($idsColegio)) {
            throw new RuntimeException('El responsable solicitado no esta disponible para inventario.');
        }

        $estadoUsuario = $this->columnaExiste('usuarios', 'estado')
            ? "AND LOWER(COALESCE(u.estado, '')) = 'activo'"
            : '';
        $sql = "SELECT 1
                FROM usuario_colegio uc
                INNER JOIN usuarios u ON u.id = uc.id_usuario
                WHERE uc.id_usuario = ?
                  AND uc.estado = 1
                  {$estadoUsuario}
                  AND uc.id_colegio IN (" . implode(',', array_fill(0, count($idsColegio), '?')) . ")
                LIMIT 1";
        $stmt = mysqli_prepare($this->cn, $sql);
        $types = 'i' . str_repeat('i', count($idsColegio));
        $params = array_merge([$idUsuario], $idsColegio);
        $this->bindParams($stmt, $types, $params);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $valido = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if (!$valido) {
            throw new RuntimeException('El responsable solicitado no pertenece a los colegios permitidos.');
        }
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

    // Igual que obtenerColoresColegio(), pero por id_colegio en vez de id_usuario.
    // obtenerColoresColegio() da el branding DEL COLEGIO DEL USUARIO EN SESION
    // (fijo, no cambia con los filtros de pantalla). Este metodo se usa donde
    // se necesita el branding del colegio que el usuario tiene SELECCIONADO en
    // un filtro (p.ej. dashboard.php), que puede ser distinto al suyo propio.
    public function obtenerColoresPorColegio(int $idColegio): array
    {
        if ($idColegio <= 0) {
            return [];
        }
        try {
            $stmt = mysqli_prepare(
                $this->cn,
                "SELECT color_principal, color_secundario, color_terciario, color_cuaternario
                 FROM colegio
                 WHERE id_colegio = ?
                 LIMIT 1"
            );
            mysqli_stmt_bind_param($stmt, 'i', $idColegio);
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

    public function obtenerUsuariosAsignablesPorColegio(int $idColegio): array
    {
        return $this->obtenerUsuariosAsignablesPorColegios($idColegio > 0 ? [$idColegio] : []);
    }

    public function obtenerUsuariosAsignablesPorColegios(array $idsColegio = []): array
    {
        if (!$this->tablaExiste('usuario_colegio') || !$this->tablaExiste('usuarios')) {
            return [];
        }

        $estadoUsuario = $this->columnaExiste('usuarios', 'estado')
            ? "AND LOWER(COALESCE(u.estado, '')) = 'activo'"
            : '';
        $idsColegio = array_values(array_unique(array_filter(array_map('intval', $idsColegio))));
        $filtroColegios = '';
        $types = '';
        $params = [];
        if (!empty($idsColegio)) {
            $filtroColegios = 'AND uc.id_colegio IN (' . implode(',', array_fill(0, count($idsColegio), '?')) . ')';
            $types = str_repeat('i', count($idsColegio));
            $params = $idsColegio;
        }

        $stmt = mysqli_prepare(
            $this->cn,
            "SELECT DISTINCT
                    u.id,
                    TRIM(CONCAT_WS(' ', u.nombre, u.apellido_paterno, u.apellido_materno)) AS nombre_completo
             FROM usuario_colegio uc
             INNER JOIN usuarios u ON u.id = uc.id_usuario
             WHERE 1 = 1
               {$filtroColegios}
               AND uc.estado = 1
               {$estadoUsuario}
             ORDER BY u.nombre ASC, u.apellido_paterno ASC, u.apellido_materno ASC"
        );
        $this->bindParams($stmt, $types, $params);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);

        $datos = [];
        while ($fila = mysqli_fetch_assoc($rs)) {
            $datos[] = $fila;
        }
        mysqli_stmt_close($stmt);
        return $datos;
    }

    public function obtenerResponsablesCorreoDashboard(int $idColegio, array $idsUsuarios = []): array
    {
        if ($idColegio <= 0 || !$this->tablaExiste('usuario_colegio') || !$this->tablaExiste('usuarios')) {
            return [];
        }

        $estadoUsuario = $this->columnaExiste('usuarios', 'estado')
            ? "AND (u.estado = 1 OR LOWER(COALESCE(u.estado, '')) = 'activo')"
            : '';

        $idsUsuarios = array_values(array_unique(array_filter(array_map('intval', $idsUsuarios))));
        $types = 'i';
        $params = [$idColegio];
        $filtroUsuarios = '';

        if (!empty($idsUsuarios)) {
            $filtroUsuarios = 'AND u.id IN (' . implode(',', array_fill(0, count($idsUsuarios), '?')) . ')';
            $types .= str_repeat('i', count($idsUsuarios));
            array_push($params, ...$idsUsuarios);
        }

        $stmt = mysqli_prepare(
            $this->cn,
            "SELECT DISTINCT
                    u.id AS id_usuario,
                    TRIM(CONCAT_WS(' ', u.nombre, u.apellido_paterno, u.apellido_materno)) AS nombre,
                    TRIM(COALESCE(u.email, '')) AS email
             FROM usuario_colegio uc
             INNER JOIN usuarios u ON u.id = uc.id_usuario
             WHERE uc.id_colegio = ?
               AND uc.estado = 1
               {$estadoUsuario}
               {$filtroUsuarios}
             ORDER BY u.nombre ASC, u.apellido_paterno ASC, u.apellido_materno ASC"
        );
        $this->bindParams($stmt, $types, $params);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);

        $datos = [];
        while ($fila = mysqli_fetch_assoc($rs)) {
            $email = trim((string)($fila['email'] ?? ''));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $datos[] = [
                'id_usuario' => (int)($fila['id_usuario'] ?? 0),
                'nombre' => trim((string)($fila['nombre'] ?? '')) ?: 'Usuario sin nombre',
                'email' => $email,
            ];
        }
        mysqli_stmt_close($stmt);

        return $datos;
    }

    public function resolverUsuarioAsignadoCargaMasiva(int $idColegio, string $valorExcel): int
    {
        $valorExcel = trim($valorExcel);
        if ($valorExcel === '') {
            return 0;
        }

        $idUsuario = $this->extraerIdDesdeSeleccionExcel($valorExcel);
        if ($idUsuario <= 0) {
            return stripos($valorExcel, 'sin asignar') !== false ? 0 : -1;
        }

        return $this->usuarioAsignableEnColegio($idUsuario, $idColegio) ? $idUsuario : -1;
    }

    public function resolverEstadoActivoDesdeExcel(string $valorExcel): int
    {
        if (!$this->tablaExiste('estado_equipo')) {
            return 0;
        }

        $valorExcel = trim($valorExcel);
        if ($valorExcel === '') {
            return 0;
        }

        $idEstado = $this->extraerIdDesdeSeleccionExcel($valorExcel);
        if ($idEstado > 0) {
            $stmt = mysqli_prepare($this->cn, "SELECT id_estado FROM estado_equipo WHERE id_estado = ? AND estado = 1 LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'i', $idEstado);
        } else {
            $stmt = mysqli_prepare($this->cn, "SELECT id_estado FROM estado_equipo WHERE LOWER(TRIM(nombre_estado)) = LOWER(TRIM(?)) AND estado = 1 LIMIT 1");
            mysqli_stmt_bind_param($stmt, 's', $valorExcel);
        }

        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
        mysqli_stmt_close($stmt);

        return (int)($fila['id_estado'] ?? 0);
    }

    public function tipoPcActivo(string $tipoPc): bool
    {
        if (!$this->tablaExiste('tipo_pc_catalogo')) {
            return false;
        }

        $tipoPc = trim($tipoPc);
        if ($tipoPc === '') {
            return false;
        }

        $stmt = mysqli_prepare($this->cn, "SELECT 1 FROM tipo_pc_catalogo WHERE LOWER(TRIM(nombre_tipo)) = LOWER(TRIM(?)) AND activo = 1 LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $tipoPc);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $existe = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        return $existe;
    }

    public function resolverUbicacionCargaMasiva(int $idColegio, string $valorExcel, bool $crearSiNoExiste = true): int
    {
        $analisis = $this->analizarUbicacionCargaMasiva($idColegio, $valorExcel);
        if (!$analisis['nueva']) {
            return (int)$analisis['id_ubicacion'];
        }

        if (!$crearSiNoExiste) {
            return 0;
        }

        return $this->obtenerOCrearUbicacionEquipo($idColegio, (string)$analisis['nombre_ubicacion']);
    }

    public function analizarUbicacionCargaMasiva(int $idColegio, string $valorExcel): array
    {
        if (!$this->tablaExiste('equipo_ubicacion')) {
            return [
                'id_ubicacion' => 0,
                'nueva' => false,
                'nombre_ubicacion' => '',
                'mensaje' => '',
            ];
        }

        $valorExcel = $this->normalizarNombreUbicacion($valorExcel);
        if ($valorExcel === '') {
            throw new RuntimeException('Debe indicar una ubicación o escribir una nueva.');
        }

        $tieneFormatoId = preg_match('/^(\d+)\s*-\s*(.*)$/u', $valorExcel, $m);
        if ($tieneFormatoId || ctype_digit($valorExcel)) {
            $idUbicacion = $tieneFormatoId ? (int)$m[1] : (int)$valorExcel;
            $nombreDespuesId = $tieneFormatoId ? trim((string)$m[2]) : '';
            if ($tieneFormatoId && $nombreDespuesId === '') {
                throw new RuntimeException('La ubicación indicada está incompleta. Seleccione una ubicación válida o escriba una nueva sin número adelante.');
            }

            $ubicacion = $this->obtenerDatosUbicacionPorId($idUbicacion);
            if (empty($ubicacion)) {
                throw new RuntimeException('La ubicación indicada no existe. Seleccione una ubicación válida o escriba una nueva sin número adelante.');
            }
            if ((int)($ubicacion['id_colegio'] ?? 0) !== $idColegio) {
                throw new RuntimeException('La ubicación indicada pertenece a otro colegio. Seleccione una ubicación válida o escriba una nueva sin número adelante.');
            }
            if ((int)($ubicacion['estado'] ?? 0) !== 1) {
                throw new RuntimeException('La ubicación indicada está inactiva. Seleccione una ubicación activa o escriba una nueva sin número adelante.');
            }
            return [
                'id_ubicacion' => $idUbicacion,
                'nueva' => false,
                'nombre_ubicacion' => '',
                'mensaje' => '',
            ];
        }

        if (preg_match('/^\d/u', $valorExcel)) {
            throw new RuntimeException('Formato de ubicación inválido. Seleccione una ubicación válida o escriba una nueva sin número adelante.');
        }

        $idExistente = $this->buscarUbicacionPorNombreNormalizado($idColegio, $valorExcel);
        if ($idExistente > 0) {
            return [
                'id_ubicacion' => $idExistente,
                'nueva' => false,
                'nombre_ubicacion' => $valorExcel,
                'mensaje' => '',
            ];
        }

        return [
            'id_ubicacion' => 0,
            'nueva' => true,
            'nombre_ubicacion' => $valorExcel,
            'mensaje' => 'Ubicación nueva detectada. Se creará para este colegio al insertar.',
        ];
    }

    public function existeNumeroSerieEquipo(string $numeroSerie): bool
    {
        return $this->validarSerieDuplicada($this->normalizarNumeroSerieEquipo($numeroSerie));
    }

    private function extraerIdDesdeSeleccionExcel(string $valorExcel): int
    {
        $valorExcel = trim($valorExcel);
        if ($valorExcel === '') {
            return 0;
        }

        if (preg_match('/^(\d+)\s*-/', $valorExcel, $m)) {
            return (int)$m[1];
        }

        return ctype_digit($valorExcel) ? (int)$valorExcel : 0;
    }

    private function obtenerDatosUbicacionPorId(int $idUbicacion): array
    {
        if ($idUbicacion <= 0 || !$this->tablaExiste('equipo_ubicacion')) {
            return [];
        }

        $stmt = mysqli_prepare(
            $this->cn,
            "SELECT id_ubicacion, id_colegio, estado, nombre_ubicacion
             FROM equipo_ubicacion
             WHERE id_ubicacion = ?
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, 'i', $idUbicacion);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
        mysqli_stmt_close($stmt);

        return $fila;
    }

    public function obtenerEstados()
    {
        if (!$this->tablaExiste('estado_equipo')) {
            return [];
        }

        $datos = [];
        $whereEstado = $this->columnaExiste('estado_equipo', 'estado') ? 'WHERE estado = 1' : '';
        $rs = $this->db->consulta("
            SELECT id_estado, nombre_estado, color_badge
            FROM estado_equipo
            {$whereEstado}
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

    public function obtenerDashboardInventario(array $filtros = []): array
    {
        return [
            'kpis' => $this->obtenerKpisDashboard($filtros),
            'valor' => $this->obtenerValorDashboard($filtros),
            'valor_por_colegio' => $this->obtenerValorPorColegioDashboard($filtros),
            'resumen_por_colegio' => $this->obtenerResumenPorColegioDashboard($filtros),
            'estados' => $this->obtenerDistribucionEstadosDashboard($filtros),
            'tipos' => $this->obtenerDistribucionTiposDashboard($filtros),
            'alertas' => $this->obtenerAlertasDashboard($filtros),
            'ultimos' => $this->obtenerUltimosEquiposDashboard($filtros),
            'hardware' => $this->obtenerHardwareDashboard($filtros),
            'detalle_valor' => $this->obtenerDetalleValorDashboard($filtros),
        ];
    }

    private function obtenerFuenteDashboard(array $filtros): array
    {
        $tipo = strtolower(trim((string)($filtros['tipo_equipo'] ?? 'pc')));
        if (!in_array($tipo, ['pc', 'tablet', 'monitor', 'impresora'], true)) {
            $tipo = 'pc';
        }

        if ($tipo === 'monitor') {
            if (!$this->tablaExiste('monitores')) {
                return ['tipo' => $tipo, 'disponible' => false];
            }
            $compraJoin = $this->tablaExiste('monitor_compra')
                ? "LEFT JOIN (SELECT id_monitor, MAX(COALESCE(valor_monitor, 0)) AS valor_equipo FROM monitor_compra GROUP BY id_monitor) compra ON compra.id_monitor = m.id_monitor"
                : '';
            return [
                'tipo' => $tipo,
                'disponible' => true,
                'tabla' => 'monitores',
                'alias' => 'm',
                'id' => 'm.id_monitor',
                'colegio' => 'm.id_colegio',
                'estado' => 'm.id_estado',
                'ubicacion' => $this->columnaExiste('monitores', 'id_ubicacion') ? 'm.id_ubicacion' : 'NULL',
                'usuario' => $this->columnaExiste('monitores', 'id_usuario_asignado') ? 'm.id_usuario_asignado' : 'NULL',
                'serie' => $this->columnaExiste('monitores', 'numero_serie') ? 'm.numero_serie' : "''",
                'nombre' => $this->columnaExiste('monitores', 'nombre_monitor') ? 'm.nombre_monitor' : "CONCAT('Monitor ', m.id_monitor)",
                'tipo_label' => "'Monitor'",
                'fecha' => $this->columnaExiste('monitores', 'fecha_registro') ? 'm.fecha_registro' : 'NULL',
                'eliminado' => $this->columnaExiste('monitores', 'eliminado') ? 'm.eliminado' : null,
                'compra_join' => $compraJoin,
                'valor' => $this->tablaExiste('monitor_compra') ? 'COALESCE(compra.valor_equipo, 0)' : '0',
                'extra_sql' => '',
                'extra_types' => '',
                'extra_params' => [],
            ];
        }

        if ($tipo === 'impresora') {
            return ['tipo' => $tipo, 'disponible' => false];
        }

        if (!$this->tablaExiste('equipos')) {
            return ['tipo' => $tipo, 'disponible' => false];
        }

        $extraSql = $tipo === 'tablet'
            ? "LOWER(COALESCE(e.tipo_pc, '')) LIKE ?"
            : "LOWER(COALESCE(e.tipo_pc, '')) NOT LIKE ?";
        $extraParam = $tipo === 'tablet' ? '%tablet%' : '%tablet%';
        $compraSql = $this->subconsultaValorCompra();

        return [
            'tipo' => $tipo,
            'disponible' => true,
            'tabla' => 'equipos',
            'alias' => 'e',
            'id' => 'e.id_equipo',
            'colegio' => 'e.id_colegio',
            'estado' => 'e.id_estado',
            'ubicacion' => $this->columnaExiste('equipos', 'id_ubicacion') ? 'e.id_ubicacion' : 'NULL',
            'usuario' => $this->columnaExiste('equipos', 'id_usuario_asignado') ? 'e.id_usuario_asignado' : 'NULL',
            'serie' => 'e.numero_serie',
            'nombre' => $this->columnaExiste('equipos', 'nombre_personalizado')
                ? "COALESCE(NULLIF(e.nombre_personalizado, ''), e.nombre_equipo)"
                : 'e.nombre_equipo',
            'tipo_label' => 'e.tipo_pc',
            'fecha' => $this->columnaExiste('equipos', 'fecha_registro') ? 'e.fecha_registro' : 'NULL',
            'eliminado' => $this->columnaExiste('equipos', 'eliminado') ? 'e.eliminado' : null,
            'compra_join' => "LEFT JOIN {$compraSql} compra ON compra.id_equipo = e.id_equipo",
            'valor' => 'COALESCE(compra.valor_equipo, 0)',
            'extra_sql' => $extraSql,
            'extra_types' => 's',
            'extra_params' => [$extraParam],
        ];
    }

    private function condicionColegioActivoDashboard(string $alias = 'c'): string
    {
        if ($this->columnaExiste('colegio', 'activo')) {
            return "{$alias}.activo = 1";
        }
        if ($this->columnaExiste('colegio', 'estado')) {
            return "{$alias}.estado = 1";
        }
        return '1=1';
    }

    private function construirWhereDashboardFuente(array $filtros, array $fuente): array
    {
        if (empty($fuente['disponible'])) {
            return ['sql' => 'WHERE 1=0', 'types' => '', 'params' => []];
        }

        $alias = $fuente['alias'];
        $condiciones = [$this->condicionColegioActivoDashboard('c')];
        $types = '';
        $params = [];

        if (!empty($fuente['eliminado'])) {
            $condiciones[] = "COALESCE({$fuente['eliminado']}, 0) = 0";
        }
        if (!empty($fuente['extra_sql'])) {
            $condiciones[] = $fuente['extra_sql'];
            $types .= $fuente['extra_types'];
            $params = array_merge($params, $fuente['extra_params']);
        }
        if (!empty($filtros['id_colegio'])) {
            $condiciones[] = "{$fuente['colegio']} = ?";
            $types .= 'i';
            $params[] = (int)$filtros['id_colegio'];
        } elseif (!empty($filtros['ids_colegio']) && is_array($filtros['ids_colegio'])) {
            $idsColegio = array_values(array_unique(array_filter(array_map('intval', $filtros['ids_colegio']))));
            if (!empty($idsColegio)) {
                $condiciones[] = "{$fuente['colegio']} IN (" . implode(',', array_fill(0, count($idsColegio), '?')) . ")";
                $types .= str_repeat('i', count($idsColegio));
                $params = array_merge($params, $idsColegio);
            }
        }

        return [
            'sql' => 'WHERE ' . implode(' AND ', $condiciones),
            'types' => $types,
            'params' => $params,
        ];
    }

    private function construirWhereColegioDashboardFuente(array $filtros, array $fuente): array
    {
        $condicionesColegio = [$this->condicionColegioActivoDashboard('c')];
        $condicionesEquipo = [];
        $typesColegio = '';
        $paramsColegio = [];
        $typesEquipo = '';
        $paramsEquipo = [];

        if (!empty($filtros['id_colegio'])) {
            $condicionesColegio[] = 'c.id_colegio = ?';
            $typesColegio .= 'i';
            $paramsColegio[] = (int)$filtros['id_colegio'];
        } elseif (!empty($filtros['ids_colegio']) && is_array($filtros['ids_colegio'])) {
            $idsColegio = array_values(array_unique(array_filter(array_map('intval', $filtros['ids_colegio']))));
            if (!empty($idsColegio)) {
                $condicionesColegio[] = 'c.id_colegio IN (' . implode(',', array_fill(0, count($idsColegio), '?')) . ')';
                $typesColegio .= str_repeat('i', count($idsColegio));
                $paramsColegio = array_merge($paramsColegio, $idsColegio);
            }
        }

        if (!empty($fuente['disponible'])) {
            if (!empty($fuente['eliminado'])) {
                $condicionesEquipo[] = "COALESCE({$fuente['eliminado']}, 0) = 0";
            }
            if (!empty($fuente['extra_sql'])) {
                $condicionesEquipo[] = $fuente['extra_sql'];
                $typesEquipo .= $fuente['extra_types'];
                $paramsEquipo = array_merge($paramsEquipo, $fuente['extra_params']);
            }
        } else {
            $condicionesEquipo[] = '1=0';
        }

        return [
            'sql' => 'WHERE ' . implode(' AND ', $condicionesColegio),
            'join_equipos' => count($condicionesEquipo) ? ' AND ' . implode(' AND ', $condicionesEquipo) : '',
            'types' => $typesEquipo . $typesColegio,
            'params' => array_merge($paramsEquipo, $paramsColegio),
        ];
    }

    public function obtenerKpisDashboard(array $filtros = []): array
    {
        $fuente = $this->obtenerFuenteDashboard($filtros);
        if (empty($fuente['disponible'])) {
            return ['total_pcs' => 0, 'activos' => 0, 'sin_ubicacion' => 0, 'sin_responsable' => 0, 'en_reparacion' => 0, 'dados_baja' => 0];
        }
        $where = $this->construirWhereDashboardFuente($filtros, $fuente);

        $sql = "SELECT
                    COUNT(DISTINCT {$fuente['id']}) AS total_pcs,
                    COUNT(DISTINCT CASE WHEN LOWER(COALESCE(ee.nombre_estado, '')) LIKE '%activo%' THEN {$fuente['id']} END) AS activos,
                    COUNT(DISTINCT CASE WHEN {$fuente['ubicacion']} IS NULL OR {$fuente['ubicacion']} = 0 THEN {$fuente['id']} END) AS sin_ubicacion,
                    COUNT(DISTINCT CASE WHEN {$fuente['usuario']} IS NULL OR {$fuente['usuario']} = 0 THEN {$fuente['id']} END) AS sin_responsable,
                    COUNT(DISTINCT CASE WHEN LOWER(COALESCE(ee.nombre_estado, '')) LIKE '%repar%' THEN {$fuente['id']} END) AS en_reparacion,
                    COUNT(DISTINCT CASE WHEN LOWER(COALESCE(ee.nombre_estado, '')) LIKE '%baja%' THEN {$fuente['id']} END) AS dados_baja
                FROM {$fuente['tabla']} {$fuente['alias']}
                INNER JOIN colegio c ON c.id_colegio = {$fuente['colegio']}
                LEFT JOIN estado_equipo ee ON ee.id_estado = {$fuente['estado']}
                {$where['sql']}";
        return $this->obtenerFilaPreparada($sql, $where['types'], $where['params']);
    }

    public function obtenerValorDashboard(array $filtros = []): array
    {
        $fuente = $this->obtenerFuenteDashboard($filtros);
        if (empty($fuente['disponible'])) {
            return ['total_equipos' => 0, 'valor_total' => 0, 'sin_valor' => 0, 'valor_promedio' => 0, 'colegio_mayor_valor' => null];
        }
        $where = $this->construirWhereDashboardFuente($filtros, $fuente);
        $sql = "SELECT
                    COUNT(DISTINCT {$fuente['id']}) AS total_equipos,
                    COALESCE(SUM(CASE WHEN {$fuente['valor']} > 0 THEN {$fuente['valor']} ELSE 0 END), 0) AS valor_total,
                    COUNT(DISTINCT CASE WHEN {$fuente['valor']} IS NULL OR {$fuente['valor']} <= 0 THEN {$fuente['id']} END) AS sin_valor
                FROM {$fuente['tabla']} {$fuente['alias']}
                INNER JOIN colegio c ON c.id_colegio = {$fuente['colegio']}
                {$fuente['compra_join']}
                {$where['sql']}";
        $fila = $this->obtenerFilaPreparada($sql, $where['types'], $where['params']);
        $totalEquipos = max(0, (int)($fila['total_equipos'] ?? 0));
        $valorTotal = max(0, (int)($fila['valor_total'] ?? 0));
        $fila['valor_promedio'] = $totalEquipos > 0 ? (int)round($valorTotal / $totalEquipos) : 0;

        $porColegio = $this->obtenerValorPorColegioDashboard($filtros);
        $fila['colegio_mayor_valor'] = $porColegio[0] ?? null;
        return $fila;
    }

    public function obtenerValorPorColegioDashboard(array $filtros = []): array
    {
        $fuente = $this->obtenerFuenteDashboard($filtros);
        $where = $this->construirWhereColegioDashboardFuente($filtros, $fuente);
        if (empty($fuente['disponible'])) {
            $sql = "SELECT c.id_colegio, c.nom_colegio, 0 AS total_equipos, 0 AS valor_total
                    FROM colegio c
                    {$where['sql']}
                    ORDER BY c.nom_colegio ASC";
            return $this->obtenerFilasPreparadas($sql, $where['types'], $where['params']);
        }
        $sql = "SELECT
                    c.id_colegio,
                    c.nom_colegio,
                    COUNT(DISTINCT {$fuente['id']}) AS total_equipos,
                    COALESCE(SUM(CASE WHEN {$fuente['valor']} > 0 THEN {$fuente['valor']} ELSE 0 END), 0) AS valor_total
                FROM colegio c
                LEFT JOIN {$fuente['tabla']} {$fuente['alias']} ON {$fuente['colegio']} = c.id_colegio{$where['join_equipos']}
                {$fuente['compra_join']}
                {$where['sql']}
                GROUP BY c.id_colegio, c.nom_colegio
                ORDER BY valor_total DESC, total_equipos DESC, c.nom_colegio ASC";
        return $this->obtenerFilasPreparadas($sql, $where['types'], $where['params']);
    }

    public function obtenerResumenPorColegioDashboard(array $filtros = []): array
    {
        $fuente = $this->obtenerFuenteDashboard($filtros);
        $where = $this->construirWhereColegioDashboardFuente($filtros, $fuente);
        if (empty($fuente['disponible'])) {
            $sql = "SELECT c.id_colegio, c.nom_colegio, 0 AS total_equipos, 0 AS activos, 0 AS sin_ubicacion,
                           0 AS sin_responsable, 0 AS en_reparacion, 0 AS dados_baja, 0 AS valor_total
                    FROM colegio c
                    {$where['sql']}
                    ORDER BY c.nom_colegio ASC";
            return $this->obtenerFilasPreparadas($sql, $where['types'], $where['params']);
        }
        $sql = "SELECT
                    c.id_colegio,
                    c.nom_colegio,
                    COUNT(DISTINCT {$fuente['id']}) AS total_equipos,
                    COUNT(DISTINCT CASE WHEN LOWER(COALESCE(ee.nombre_estado, '')) LIKE '%activo%' THEN {$fuente['id']} END) AS activos,
                    COUNT(DISTINCT CASE WHEN {$fuente['ubicacion']} IS NULL OR {$fuente['ubicacion']} = 0 THEN {$fuente['id']} END) AS sin_ubicacion,
                    COUNT(DISTINCT CASE WHEN {$fuente['usuario']} IS NULL OR {$fuente['usuario']} = 0 THEN {$fuente['id']} END) AS sin_responsable,
                    COUNT(DISTINCT CASE WHEN LOWER(COALESCE(ee.nombre_estado, '')) LIKE '%repar%' THEN {$fuente['id']} END) AS en_reparacion,
                    COUNT(DISTINCT CASE WHEN LOWER(COALESCE(ee.nombre_estado, '')) LIKE '%baja%' THEN {$fuente['id']} END) AS dados_baja,
                    COALESCE(SUM(CASE WHEN {$fuente['valor']} > 0 THEN {$fuente['valor']} ELSE 0 END), 0) AS valor_total
                FROM colegio c
                LEFT JOIN {$fuente['tabla']} {$fuente['alias']} ON {$fuente['colegio']} = c.id_colegio{$where['join_equipos']}
                LEFT JOIN estado_equipo ee ON ee.id_estado = {$fuente['estado']}
                {$fuente['compra_join']}
                {$where['sql']}
                GROUP BY c.id_colegio, c.nom_colegio
                ORDER BY c.nom_colegio ASC";
        return $this->obtenerFilasPreparadas($sql, $where['types'], $where['params']);
    }

    public function obtenerDistribucionEstadosDashboard(array $filtros = []): array
    {
        $fuente = $this->obtenerFuenteDashboard($filtros);
        if (empty($fuente['disponible'])) {
            return [];
        }
        $where = $this->construirWhereDashboardFuente($filtros, $fuente);
        $sql = "SELECT
                    COALESCE(ee.nombre_estado, 'Sin estado') AS etiqueta,
                    COALESCE(ee.color_badge, 'secondary') AS color_badge,
                    COUNT(DISTINCT {$fuente['id']}) AS total
                FROM {$fuente['tabla']} {$fuente['alias']}
                INNER JOIN colegio c ON c.id_colegio = {$fuente['colegio']}
                LEFT JOIN estado_equipo ee ON ee.id_estado = {$fuente['estado']}
                {$where['sql']}
                GROUP BY etiqueta, color_badge
                ORDER BY total DESC, etiqueta ASC";
        return $this->obtenerFilasPreparadas($sql, $where['types'], $where['params']);
    }

    public function obtenerDistribucionTiposDashboard(array $filtros = []): array
    {
        $fuente = $this->obtenerFuenteDashboard($filtros);
        if (empty($fuente['disponible'])) {
            return [];
        }
        $where = $this->construirWhereDashboardFuente($filtros, $fuente);
        $sql = "SELECT COALESCE(NULLIF(TRIM({$fuente['tipo_label']}), ''), 'Sin tipo') AS etiqueta, COUNT(DISTINCT {$fuente['id']}) AS total
                FROM {$fuente['tabla']} {$fuente['alias']}
                INNER JOIN colegio c ON c.id_colegio = {$fuente['colegio']}
                {$where['sql']}
                GROUP BY etiqueta
                ORDER BY total DESC, etiqueta ASC";
        return $this->obtenerFilasPreparadas($sql, $where['types'], $where['params']);
    }

    public function obtenerAlertasDashboard(array $filtros = []): array
    {
        $fuente = $this->obtenerFuenteDashboard($filtros);
        if (empty($fuente['disponible'])) {
            return ['sin_ubicacion' => 0, 'sin_responsable' => 0, 'sin_qr' => 0, 'sin_fotografia' => 0, 'sin_valor' => 0, 'sin_serie' => 0];
        }
        $where = $this->construirWhereDashboardFuente($filtros, $fuente);
        $sinQr = $fuente['tipo'] === 'monitor' ? '0' : "COUNT(DISTINCT CASE WHEN e.qr_code IS NULL OR TRIM(e.qr_code) = '' THEN {$fuente['id']} END)";
        $sinFoto = $fuente['tipo'] === 'monitor'
            ? ($this->tablaExiste('monitor_fotos') ? "COUNT(DISTINCT CASE WHEN NOT EXISTS (SELECT 1 FROM monitor_fotos mf WHERE mf.id_monitor = {$fuente['id']}) THEN {$fuente['id']} END)" : '0')
            : ($this->tablaExiste('equipo_fotos') ? "COUNT(DISTINCT CASE WHEN NOT EXISTS (SELECT 1 FROM equipo_fotos ef WHERE ef.id_equipo = {$fuente['id']}) THEN {$fuente['id']} END)" : '0');
        $sql = "SELECT
                    COUNT(DISTINCT CASE WHEN {$fuente['ubicacion']} IS NULL OR {$fuente['ubicacion']} = 0 THEN {$fuente['id']} END) AS sin_ubicacion,
                    COUNT(DISTINCT CASE WHEN {$fuente['usuario']} IS NULL OR {$fuente['usuario']} = 0 THEN {$fuente['id']} END) AS sin_responsable,
                    {$sinQr} AS sin_qr,
                    {$sinFoto} AS sin_fotografia,
                    COUNT(DISTINCT CASE WHEN {$fuente['valor']} IS NULL OR {$fuente['valor']} <= 0 THEN {$fuente['id']} END) AS sin_valor,
                    COUNT(DISTINCT CASE WHEN {$fuente['serie']} IS NULL OR TRIM({$fuente['serie']}) = '' THEN {$fuente['id']} END) AS sin_serie
                FROM {$fuente['tabla']} {$fuente['alias']}
                INNER JOIN colegio c ON c.id_colegio = {$fuente['colegio']}
                {$fuente['compra_join']}
                {$where['sql']}";
        return $this->obtenerFilaPreparada($sql, $where['types'], $where['params']);
    }

    public function obtenerUltimosEquiposDashboard(array $filtros = [], int $limite = 8): array
    {
        $fuente = $this->obtenerFuenteDashboard($filtros);
        if (empty($fuente['disponible'])) {
            return [];
        }
        $where = $this->construirWhereDashboardFuente($filtros, $fuente);
        $order = $fuente['fecha'] !== 'NULL'
            ? "{$fuente['fecha']} DESC, {$fuente['id']} DESC"
            : "{$fuente['id']} DESC";
        $limite = max(1, min(20, $limite));

        $sql = "SELECT
                    {$fuente['id']} AS id_equipo,
                    {$fuente['nombre']} AS nombre_personalizado,
                    {$fuente['nombre']} AS nombre_equipo,
                    {$fuente['serie']} AS numero_serie,
                    {$fuente['tipo_label']} AS tipo_pc,
                    c.nom_colegio,
                    {$fuente['fecha']} AS fecha_registro
                FROM {$fuente['tabla']} {$fuente['alias']}
                INNER JOIN colegio c ON c.id_colegio = {$fuente['colegio']}
                {$where['sql']}
                ORDER BY {$order}
                LIMIT {$limite}";
        return $this->obtenerFilasPreparadas($sql, $where['types'], $where['params']);
    }

    public function obtenerDetalleValorDashboard(array $filtros = [], int $limite = 25): array
    {
        $fuente = $this->obtenerFuenteDashboard($filtros);
        if (empty($fuente['disponible'])) {
            return [];
        }
        $where = $this->construirWhereDashboardFuente($filtros, $fuente);
        $limite = max(1, min(50, $limite));

        $sql = "SELECT
                    {$fuente['id']} AS id_equipo,
                    {$fuente['nombre']} AS nombre_personalizado,
                    {$fuente['nombre']} AS nombre_equipo,
                    {$fuente['serie']} AS numero_serie,
                    {$fuente['tipo_label']} AS tipo_pc,
                    c.nom_colegio,
                    COALESCE(eu.nombre_ubicacion, '') AS nombre_ubicacion,
                    CONCAT(ua.nombre, ' ', ua.apellido_paterno, ' ', ua.apellido_materno) AS usuario_asignado,
                    COALESCE(ee.nombre_estado, 'Sin estado') AS nombre_estado,
                    {$fuente['valor']} AS valor_equipo
                FROM {$fuente['tabla']} {$fuente['alias']}
                INNER JOIN colegio c ON c.id_colegio = {$fuente['colegio']}
                LEFT JOIN equipo_ubicacion eu ON eu.id_ubicacion = {$fuente['ubicacion']}
                LEFT JOIN usuarios ua ON ua.id = {$fuente['usuario']}
                LEFT JOIN estado_equipo ee ON ee.id_estado = {$fuente['estado']}
                {$fuente['compra_join']}
                {$where['sql']}
                ORDER BY valor_equipo DESC, {$fuente['id']} DESC
                LIMIT {$limite}";
        return $this->obtenerFilasPreparadas($sql, $where['types'], $where['params']);
    }

    public function obtenerHardwareDashboard(array $filtros = []): array
    {
        return [
            'ram' => $this->obtenerDistribucionRamDashboard($filtros),
            'ram_baja' => $this->obtenerEquiposRamBajaDashboard($filtros),
            'sistemas' => $this->obtenerSistemasOperativosDashboard($filtros),
        ];
    }

    private function obtenerDistribucionRamDashboard(array $filtros): array
    {
        $fuente = $this->obtenerFuenteDashboard($filtros);
        if (($fuente['tabla'] ?? '') !== 'equipos') {
            return [];
        }
        $where = $this->construirWhereDashboardFuente($filtros, $fuente);
        $sql = "SELECT e.id_equipo, COALESCE(SUM(NULLIF(em.tamano_memoria, 0)), 0) AS ram_total
                FROM equipos e
                INNER JOIN colegio c ON c.id_colegio = e.id_colegio
                LEFT JOIN equipo_memoria em ON em.id_equipo = e.id_equipo
                {$where['sql']}
                GROUP BY e.id_equipo";
        $filas = $this->obtenerFilasPreparadas($sql, $where['types'], $where['params']);
        $rangos = ['4 GB' => 0, '8 GB' => 0, '16 GB' => 0, '32 GB' => 0, 'Otros' => 0, 'Sin dato' => 0];
        foreach ($filas as $fila) {
            $ram = (int)($fila['ram_total'] ?? 0);
            if ($ram <= 0) {
                $rangos['Sin dato']++;
            } elseif ($ram <= 4) {
                $rangos['4 GB']++;
            } elseif ($ram <= 8) {
                $rangos['8 GB']++;
            } elseif ($ram <= 16) {
                $rangos['16 GB']++;
            } elseif ($ram <= 32) {
                $rangos['32 GB']++;
            } else {
                $rangos['Otros']++;
            }
        }
        $salida = [];
        foreach ($rangos as $etiqueta => $total) {
            if ($total > 0) {
                $salida[] = ['etiqueta' => $etiqueta, 'total' => $total];
            }
        }
        return $salida;
    }

    private function obtenerEquiposRamBajaDashboard(array $filtros): int
    {
        $fuente = $this->obtenerFuenteDashboard($filtros);
        if (($fuente['tabla'] ?? '') !== 'equipos') {
            return 0;
        }
        $where = $this->construirWhereDashboardFuente($filtros, $fuente);
        $sql = "SELECT COUNT(*) AS total
                FROM (
                    SELECT e.id_equipo, COALESCE(SUM(NULLIF(em.tamano_memoria, 0)), 0) AS ram_total
                    FROM equipos e
                    INNER JOIN colegio c ON c.id_colegio = e.id_colegio
                    LEFT JOIN equipo_memoria em ON em.id_equipo = e.id_equipo
                    {$where['sql']}
                    GROUP BY e.id_equipo
                    HAVING ram_total > 0 AND ram_total < 8
                ) ram";
        $fila = $this->obtenerFilaPreparada($sql, $where['types'], $where['params']);
        return (int)($fila['total'] ?? 0);
    }

    private function obtenerSistemasOperativosDashboard(array $filtros): array
    {
        $fuente = $this->obtenerFuenteDashboard($filtros);
        if (($fuente['tabla'] ?? '') !== 'equipos') {
            return [];
        }
        $where = $this->construirWhereDashboardFuente($filtros, $fuente);
        $sql = "SELECT
                    CASE
                        WHEN es.windows IS NULL OR TRIM(es.windows) = '' THEN 'Sin dato'
                        WHEN LOWER(es.windows) LIKE '%windows 11%' THEN 'Windows 11'
                        WHEN LOWER(es.windows) LIKE '%windows 10%' THEN 'Windows 10'
                        ELSE LEFT(TRIM(es.windows), 40)
                    END AS etiqueta,
                    COUNT(DISTINCT e.id_equipo) AS total
                FROM equipos e
                INNER JOIN colegio c ON c.id_colegio = e.id_colegio
                LEFT JOIN equipo_software es ON es.id_equipo = e.id_equipo
                {$where['sql']}
                GROUP BY etiqueta
                ORDER BY total DESC, etiqueta ASC";
        return $this->obtenerFilasPreparadas($sql, $where['types'], $where['params']);
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

        $selectNombrePersonalizado = $this->columnaExiste('equipos', 'nombre_personalizado')
            ? "e.nombre_personalizado,"
            : "NULL AS nombre_personalizado,";

        $sql = "SELECT
                    e.id_equipo,
                    e.id_usuario_asignado,
                    {$selectNombrePersonalizado}
                    e.nombre_equipo,
                    e.fabricante,
                    e.producto,
                    e.numero_serie,
                    e.tipo_pc,
                    e.qr_code,
                    e.id_estado,
                    c.id_colegio,
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

    public function usuarioPuedeGestionarEquipoPorAlcance(int $idEquipo, array $alcance): bool
    {
        $stmt = mysqli_prepare($this->cn, "SELECT id_colegio FROM equipos WHERE id_equipo = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $idEquipo);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
        mysqli_stmt_close($stmt);

        return $this->colegioPermitidoPorAlcance((int)($fila['id_colegio'] ?? 0), $alcance);
    }

    public function usuarioPuedeGestionarMonitorPorAlcance(int $idMonitor, array $alcance): bool
    {
        if (!$this->tablaExiste('monitores')) {
            return false;
        }

        $stmt = mysqli_prepare($this->cn, "SELECT id_colegio FROM monitores WHERE id_monitor = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $idMonitor);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
        mysqli_stmt_close($stmt);

        return $this->colegioPermitidoPorAlcance((int)($fila['id_colegio'] ?? 0), $alcance);
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
        } elseif (!empty($filtros['ids_colegio']) && is_array($filtros['ids_colegio'])) {
            $idsColegio = array_values(array_unique(array_filter(array_map('intval', $filtros['ids_colegio']))));
            if (!empty($idsColegio)) {
                $condiciones[] = 'e.id_colegio IN (' . implode(',', array_fill(0, count($idsColegio), '?')) . ')';
                $types .= str_repeat('i', count($idsColegio));
                foreach ($idsColegio as $idColegio) {
                    $params[] = $idColegio;
                }
            }
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
            $condNombrePersonalizado = $this->columnaExiste('equipos', 'nombre_personalizado')
                ? ' OR e.nombre_personalizado LIKE ?'
                : '';
            $condiciones[] = "(e.nombre_equipo LIKE ? OR e.numero_serie LIKE ? OR e.producto LIKE ? OR e.qr_code LIKE ?{$condNombrePersonalizado})";
            $types .= 'ssss';
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
            if ($condNombrePersonalizado !== '') {
                $types .= 's';
                $params[] = $busqueda;
            }
        }

        return [
            'sql' => count($condiciones) ? 'WHERE ' . implode(' AND ', $condiciones) : '',
            'types' => $types,
            'params' => $params,
        ];
    }

    private function construirWhereDashboard(array $filtros): array
    {
        return $this->construirWhere([
            'id_colegio' => (int)($filtros['id_colegio'] ?? 0),
            'ids_colegio' => $filtros['ids_colegio'] ?? [],
            'id_estado' => (int)($filtros['id_estado'] ?? 0),
        ]);
    }

    private function construirWhereColegioDashboard(array $filtros): array
    {
        $condicionesColegio = [];
        $condicionesEquipo = [];
        $typesColegio = '';
        $paramsColegio = [];
        $typesEquipo = '';
        $paramsEquipo = [];

        if ($this->columnaExiste('colegio', 'activo')) {
            $condicionesColegio[] = 'c.activo = 1';
        } elseif ($this->columnaExiste('colegio', 'estado')) {
            $condicionesColegio[] = 'c.estado = 1';
        }

        if (!empty($filtros['id_colegio'])) {
            $condicionesColegio[] = 'c.id_colegio = ?';
            $typesColegio .= 'i';
            $paramsColegio[] = (int)$filtros['id_colegio'];
        } elseif (!empty($filtros['ids_colegio']) && is_array($filtros['ids_colegio'])) {
            $idsColegio = array_values(array_unique(array_filter(array_map('intval', $filtros['ids_colegio']))));
            if (!empty($idsColegio)) {
                $condicionesColegio[] = 'c.id_colegio IN (' . implode(',', array_fill(0, count($idsColegio), '?')) . ')';
                $typesColegio .= str_repeat('i', count($idsColegio));
                foreach ($idsColegio as $idColegio) {
                    $paramsColegio[] = $idColegio;
                }
            }
        }

        if ($this->columnaExiste('equipos', 'eliminado')) {
            $condicionesEquipo[] = 'e.eliminado = 0';
        }

        if (!empty($filtros['id_estado'])) {
            $condicionesEquipo[] = 'e.id_estado = ?';
            $typesEquipo .= 'i';
            $paramsEquipo[] = (int)$filtros['id_estado'];
        }

        return [
            'sql' => count($condicionesColegio) ? 'WHERE ' . implode(' AND ', $condicionesColegio) : '',
            'join_equipos' => count($condicionesEquipo) ? ' AND ' . implode(' AND ', $condicionesEquipo) : '',
            'types' => $typesEquipo . $typesColegio,
            'params' => array_merge($paramsEquipo, $paramsColegio),
        ];
    }

    private function subconsultaValorCompra(): string
    {
        return "(SELECT id_equipo, MAX(COALESCE(valor_equipo, 0)) AS valor_equipo
                 FROM equipos_compra
                 GROUP BY id_equipo)";
    }

    private function obtenerFilaPreparada(string $sql, string $types = '', array $params = []): array
    {
        $stmt = mysqli_prepare($this->cn, $sql);
        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar la consulta de inventario.');
        }
        $this->bindParams($stmt, $types, $params);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
        mysqli_stmt_close($stmt);
        return $fila;
    }

    private function obtenerFilasPreparadas(string $sql, string $types = '', array $params = []): array
    {
        $stmt = mysqli_prepare($this->cn, $sql);
        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar la consulta de inventario.');
        }
        $this->bindParams($stmt, $types, $params);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $filas = [];
        while ($fila = mysqli_fetch_assoc($rs)) {
            $filas[] = $fila;
        }
        mysqli_stmt_close($stmt);
        return $filas;
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
        mysqli_begin_transaction($this->cn);

        try {
            $payload = $this->normalizarPayload($post, 0, $idUsuario, (int)$idColegio);

            if ($this->validarSerieDuplicada($payload['equipo']['numero_serie'])) {
                throw new RuntimeException('El numero de serie ya existe en otro equipo.');
            }

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

            $idUsuarioAsignado = (int)($payload['equipo']['id_usuario_asignado'] ?? 0);
            if ($idUsuarioAsignado > 0) {
                $this->registrarAsignacionEquipo(
                    $idEquipo,
                    null,
                    $idUsuarioAsignado,
                    (int)$idUsuario,
                    'Asignacion inicial de equipo',
                    'Equipo registrado con usuario asignado.'
                );
            }

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

            $idUbicacion = (int)($payload['equipo']['id_ubicacion'] ?? 0);
            if ($idUbicacion > 0) {
                $this->registrarMovimiento(
                    $idEquipo,
                    0,
                    $idUbicacion,
                    $idUsuario,
                    'Alta inicial de inventario',
                    'Equipo registrado via carga masiva.'
                );
            }

            $idUsuarioAsignado = (int)($payload['equipo']['id_usuario_asignado'] ?? 0);
            if ($idUsuarioAsignado > 0) {
                $this->registrarAsignacionEquipo(
                    $idEquipo,
                    null,
                    $idUsuarioAsignado,
                    $idUsuario,
                    'Asignacion inicial de equipo',
                    'Equipo registrado via carga masiva con usuario asignado.'
                );
            }

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

        if (isset($p['nombre_personalizado']) && $this->columnaExiste('equipos', 'nombre_personalizado')) {
            $cols[]   = 'nombre_personalizado';
            $vals[]   = 'NULLIF(?, \'\')';
            $types   .= 's';
            $params[] = (string)$p['nombre_personalizado'];
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
        $idUsuarioAsignadoAnterior = !empty($actual['id_usuario_asignado']) ? (int)$actual['id_usuario_asignado'] : null;

        mysqli_begin_transaction($this->cn);

        try {
            $payload = $this->normalizarPayload($post, $idEquipo, $idUsuario, $idColegio);

            if ($this->validarSerieDuplicada($payload['equipo']['numero_serie'], $idEquipo)) {
                throw new RuntimeException('El numero de serie ya existe en otro equipo.');
            }

            $idUbicacionNueva       = (int)($payload['equipo']['id_ubicacion'] ?? 0);
            $idUsuarioAsignadoNuevo = (int)($payload['equipo']['id_usuario_asignado'] ?? 0);
            $idUsuarioAsignadoNuevo = $idUsuarioAsignadoNuevo > 0 ? $idUsuarioAsignadoNuevo : null;
            $observacionMovimiento  = trim((string)($post['observacion_movimiento'] ?? ''));

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

            if ($this->columnaExiste('equipos', 'nombre_personalizado')) {
                $cols[]   = 'nombre_personalizado=NULLIF(?, \'\')';
                $types   .= 's';
                $params[] = (string)($payload['equipo']['nombre_personalizado'] ?? '');
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

            if ($idUsuarioAsignadoAnterior !== $idUsuarioAsignadoNuevo) {
                $this->registrarAsignacionEquipo(
                    $idEquipo,
                    $idUsuarioAsignadoAnterior,
                    $idUsuarioAsignadoNuevo,
                    (int)$idUsuario,
                    $idUsuarioAsignadoNuevo === null ? 'Liberacion de equipo' : 'Cambio de usuario asignado',
                    'Cambio registrado desde edicion de equipo.'
                );
            }

            $this->limpiarRelacionados($idEquipo);
            $this->guardarTablasRelacionadas($idEquipo, $payload);
            $this->procesarEliminacionFotos($idEquipo, $post);
            $this->procesarFotoPrincipal($idEquipo, $post);
            $this->guardarFotos($idEquipo, $files);

            mysqli_commit($this->cn);
            return true;
        } catch (Throwable $e) {
            mysqli_rollback($this->cn);
            throw $e;
        }
    }

    private function usuarioAsignableEnColegio(int $idUsuario, int $idColegio): bool
    {
        if ($idUsuario <= 0) {
            return true;
        }

        if ($idColegio <= 0 || !$this->tablaExiste('usuario_colegio') || !$this->tablaExiste('usuarios')) {
            return false;
        }

        $estadoUsuario = $this->columnaExiste('usuarios', 'estado')
            ? "AND LOWER(COALESCE(u.estado, '')) = 'activo'"
            : '';

        $stmt = mysqli_prepare(
            $this->cn,
            "SELECT 1
             FROM usuario_colegio uc
             INNER JOIN usuarios u ON u.id = uc.id_usuario
             WHERE uc.id_usuario = ?
               AND uc.id_colegio = ?
               AND uc.estado = 1
               {$estadoUsuario}
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, 'ii', $idUsuario, $idColegio);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $valido = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        return $valido;
    }

    private function resolverUbicacionEquipo($post, int $idColegio): int
    {
        if (!$this->tablaExiste('equipo_ubicacion')) {
            return 0;
        }

        $idUbicacionRaw = trim((string)($post['id_ubicacion'] ?? ''));
        $nombreNuevo = preg_replace('/\s+/', ' ', trim((string)($post['nombre_ubicacion_nueva'] ?? '')));

        if ($idUbicacionRaw === '__nueva__' || ($idUbicacionRaw === '' && $nombreNuevo !== '')) {
            if ($nombreNuevo === '') {
                throw new RuntimeException('Debes escribir el nombre de la nueva ubicacion.');
            }

            return $this->obtenerOCrearUbicacionEquipo($idColegio, $nombreNuevo);
        }

        $idUbicacion = (int)$idUbicacionRaw;
        if ($idUbicacion <= 0) {
            throw new RuntimeException('Debes seleccionar una ubicacion para el equipo.');
        }

        $stmt = mysqli_prepare(
            $this->cn,
            "SELECT 1
             FROM equipo_ubicacion
             WHERE id_ubicacion = ?
               AND id_colegio = ?
               AND estado = 1
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, 'ii', $idUbicacion, $idColegio);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $valid = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if (!$valid) {
            throw new RuntimeException('La ubicacion seleccionada no pertenece al colegio del equipo.');
        }

        return $idUbicacion;
    }

    private function normalizarNombreUbicacion(string $nombreUbicacion): string
    {
        return preg_replace('/\s+/', ' ', trim($nombreUbicacion));
    }

    private function claveNombreUbicacion(string $nombreUbicacion): string
    {
        $nombreUbicacion = mb_strtolower($this->normalizarNombreUbicacion($nombreUbicacion), 'UTF-8');
        return strtr($nombreUbicacion, [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n',
        ]);
    }

    private function buscarUbicacionPorNombreNormalizado(int $idColegio, string $nombreUbicacion): int
    {
        $claveBuscada = $this->claveNombreUbicacion($nombreUbicacion);
        if ($idColegio <= 0 || $claveBuscada === '') {
            return 0;
        }

        $stmt = mysqli_prepare(
            $this->cn,
            "SELECT id_ubicacion, nombre_ubicacion, estado
             FROM equipo_ubicacion
             WHERE id_colegio = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $idColegio);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $idEncontrado = 0;
        $estadoEncontrado = 1;
        while ($fila = mysqli_fetch_assoc($rs)) {
            if ($this->claveNombreUbicacion((string)($fila['nombre_ubicacion'] ?? '')) === $claveBuscada) {
                $idEncontrado = (int)($fila['id_ubicacion'] ?? 0);
                $estadoEncontrado = (int)($fila['estado'] ?? 1);
                break;
            }
        }
        mysqli_stmt_close($stmt);

        if ($idEncontrado > 0 && $estadoEncontrado !== 1) {
            $stmtActivo = mysqli_prepare($this->cn, "UPDATE equipo_ubicacion SET estado = 1 WHERE id_ubicacion = ? LIMIT 1");
            mysqli_stmt_bind_param($stmtActivo, 'i', $idEncontrado);
            mysqli_stmt_execute($stmtActivo);
            mysqli_stmt_close($stmtActivo);
        }

        return $idEncontrado;
    }

    private function obtenerOCrearUbicacionEquipo(int $idColegio, string $nombreUbicacion): int
    {
        $nombreUbicacion = $this->normalizarNombreUbicacion($nombreUbicacion);
        if ($nombreUbicacion === '') {
            throw new RuntimeException('Debe indicar una ubicación o escribir una nueva.');
        }

        $idExistente = $this->buscarUbicacionPorNombreNormalizado($idColegio, $nombreUbicacion);
        if ($idExistente > 0) {
            return $idExistente;
        }

        $tipoUbicacion = 'Otra';
        $descripcion = 'Creada desde carga masiva de inventario';
        if ($this->columnaExiste('equipo_ubicacion', 'descripcion')) {
            $stmt = mysqli_prepare(
                $this->cn,
                "INSERT INTO equipo_ubicacion
                    (id_colegio, nombre_ubicacion, tipo_ubicacion, descripcion, estado)
                 VALUES (?, ?, ?, ?, 1)"
            );
            mysqli_stmt_bind_param($stmt, 'isss', $idColegio, $nombreUbicacion, $tipoUbicacion, $descripcion);
        } else {
            $stmt = mysqli_prepare(
                $this->cn,
                "INSERT INTO equipo_ubicacion
                    (id_colegio, nombre_ubicacion, tipo_ubicacion, estado)
                 VALUES (?, ?, ?, 1)"
            );
            mysqli_stmt_bind_param($stmt, 'iss', $idColegio, $nombreUbicacion, $tipoUbicacion);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return (int)mysqli_insert_id($this->cn);
    }

    private function normalizarNumeroSerieEquipo(string $numeroSerie): string
    {
        $numeroSerie = preg_replace('/\s+/', ' ', trim($numeroSerie));
        $numeroSerie = strtoupper($numeroSerie);
        return preg_replace('/[^A-Z0-9\-_.\/]/', '', $numeroSerie);
    }

    private function generarNombreEquipoDesdeSerie(string $numeroSerie): string
    {
        return 'PC-' . $this->normalizarNumeroSerieEquipo($numeroSerie);
    }

    private function normalizarPayload($post, $idEquipo, $idUsuario, $idColegio = 0)
    {
        $idColegio = (int)$idColegio;
        $numeroSerie = $this->normalizarNumeroSerieEquipo((string)($post['numero_serie'] ?? ''));

        $equipo = [
            'id_usuario'          => (int)$idUsuario,
            'id_colegio'          => $idColegio,
            'id_usuario_registra' => (int)$idUsuario,
            'id_usuario_asignado' => (int)($post['id_usuario_asignado'] ?? 0),
            'nombre_personalizado' => mb_substr(trim((string)($post['nombre_personalizado'] ?? '')), 0, 150),
            'nombre_equipo'       => '',
            'fabricante'          => trim((string)($post['fabricante'] ?? '')),
            'producto'            => trim((string)($post['producto'] ?? '')),
            'numero_serie'        => $numeroSerie,
            'tipo_pc'             => trim((string)($post['tipo_pc'] ?? '')),
            'qr_code'             => trim((string)($post['qr_code'] ?? '')),
            'id_estado'           => (int)($post['id_estado'] ?? 1),
            'id_ubicacion'        => 0,
        ];

        if ($idColegio <= 0) {
            throw new RuntimeException('No se pudo determinar el colegio del usuario. Contacta al administrador.');
        }

        if ($equipo['id_usuario_asignado'] > 0 && !$this->usuarioAsignableEnColegio($equipo['id_usuario_asignado'], $idColegio)) {
            throw new RuntimeException('El usuario responsable seleccionado no pertenece al colegio del equipo o no esta activo.');
        }

        if ($equipo['numero_serie'] === '') {
            throw new RuntimeException('El numero de serie es obligatorio.');
        }

        $equipo['nombre_equipo'] = $this->generarNombreEquipoDesdeSerie($equipo['numero_serie']);

        if ($equipo['tipo_pc'] === '') {
            throw new RuntimeException('Debes seleccionar el tipo de PC.');
        }

        $equipo['id_ubicacion'] = $this->resolverUbicacionEquipo($post, $idColegio);

        if ($equipo['qr_code'] === '') {
            $equipo['qr_code'] = $equipo['nombre_equipo'];
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

    private function registrarAsignacionEquipo(int $idEquipo, ?int $idUsuarioAnterior, ?int $idUsuarioNuevo, int $idUsuarioAccion, string $motivo, string $observacion = ''): void
    {
        if (!$this->tablaExiste('equipo_asignacion_historial')) {
            return;
        }

        $stmt = mysqli_prepare($this->cn, "
            INSERT INTO equipo_asignacion_historial
                (id_equipo, id_usuario_anterior, id_usuario_nuevo, id_usuario_accion, motivo, observacion)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, 'iiiiss', $idEquipo, $idUsuarioAnterior, $idUsuarioNuevo, $idUsuarioAccion, $motivo, $observacion);
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
        $stmt = mysqli_prepare($this->cn, "SELECT id_colegio, id_usuario_asignado{$selectExtra} FROM equipos WHERE id_equipo = ? LIMIT 1");
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
        } elseif (!empty($filtros['ids_colegio']) && is_array($filtros['ids_colegio'])) {
            $idsColegio = array_values(array_unique(array_filter(array_map('intval', $filtros['ids_colegio']))));
            if (!empty($idsColegio)) {
                $condiciones[] = 'm.id_colegio IN (' . implode(',', array_fill(0, count($idsColegio), '?')) . ')';
                $types .= str_repeat('i', count($idsColegio));
                foreach ($idsColegio as $idColegio) {
                    $params[] = $idColegio;
                }
            }
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
                    c.id_colegio,
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
        } elseif (!empty($filtros['ids_colegio']) && is_array($filtros['ids_colegio'])) {
            $idsColegio = array_values(array_unique(array_filter(array_map('intval', $filtros['ids_colegio']))));
            if (!empty($idsColegio)) {
                $condiciones[] = 'm.id_colegio IN (' . implode(',', array_fill(0, count($idsColegio), '?')) . ')';
                $types .= str_repeat('i', count($idsColegio));
                foreach ($idsColegio as $idColegio) {
                    $params[] = $idColegio;
                }
            }
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
