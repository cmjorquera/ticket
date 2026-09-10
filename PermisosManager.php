<?php
declare(strict_types=1);

require_once __DIR__ . '/clases/Conexion.php';

/**
 * Centraliza la autorización de tickets por rol, departamento y colegio.
 *
 * Todas las comprobaciones fallan de forma segura: ante un dato inválido o un
 * error de base de datos se retorna false, [] o null, según corresponda.
 */
final class PermisosManager
{
    private const ROL_SUPERADMIN = 'superadmin';
    private const ROL_JEFE = 'jefe_departamento';
    private const ROL_TRABAJADOR = 'trabajador';

    private int $idUsuario;
    private ?Conexion $dbPrincipal = null;
    private ?Conexion $dbPermisos = null;
    private ?array $usuario = null;
    private string $rol = self::ROL_TRABAJADOR;
    private ?array $jefatura = null;
    private bool $usuarioValido = false;

    /** @var array<string, mixed> */
    private array $cache = [];

    public function __construct($idUsuario)
    {
        $this->idUsuario = filter_var($idUsuario, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]) ?: 0;

        if ($this->idUsuario === 0) {
            return;
        }

        try {
            $this->conectar();
            $this->cargarDatos();
            $this->determinarRol();
        } catch (Throwable $ex) {
            $this->registrarError('inicializar el gestor', $ex);
            $this->usuario = null;
            $this->jefatura = null;
            $this->usuarioValido = false;
            $this->rol = self::ROL_TRABAJADOR;
        }
    }

    /** Abre y conserva una conexión independiente para cada base de datos. */
    private function conectar(): void
    {
        // Conexion agrega el prefijo DB_PREFIX del proyecto (actualmente crist668_).
        $this->dbPrincipal = Conexion::getInstance('sistema_panel_central');
        $this->dbPermisos = Conexion::getInstance('logica_permisos');
    }

    /** Carga una sola vez el usuario y su eventual jefatura activa. */
    private function cargarDatos(): void
    {
        if ($this->dbPrincipal === null || $this->dbPermisos === null) {
            return;
        }

        $usuario = $this->dbPrincipal->fetchOne(
            'SELECT id, nombre, es_admin_global, id_area_trabajo, estado
               FROM usuarios
              WHERE id = ?
              LIMIT 1',
            [$this->idUsuario]
        );

        if (!$usuario || !$this->estadoActivo($usuario['estado'] ?? null)) {
            return;
        }

        $this->usuario = $usuario;
        $this->usuarioValido = true;

        $jefatura = $this->dbPermisos->fetchOne(
            'SELECT id, id_usuario, id_departamento, id_colegio, estado
               FROM jefatura_departamento
              WHERE id_usuario = ? AND estado = 1
           ORDER BY id ASC
              LIMIT 1',
            [$this->idUsuario]
        );

        $this->jefatura = $jefatura ?: null;
    }

    private function determinarRol(): void
    {
        if (!$this->usuarioValido) {
            return;
        }

        if ((int) ($this->usuario['es_admin_global'] ?? 0) === 1) {
            $this->rol = self::ROL_SUPERADMIN;
        } elseif ($this->jefatura !== null) {
            $this->rol = self::ROL_JEFE;
        }
    }

    public function getRol(): string
    {
        return $this->rol;
    }

    public function esSuperAdmin(): bool
    {
        return $this->usuarioValido && $this->rol === self::ROL_SUPERADMIN;
    }

    /** El departamento se obtiene con getDepartamento(). */
    public function esJefeDepartamento(): bool
    {
        return $this->usuarioValido && $this->rol === self::ROL_JEFE;
    }

    /**
     * Retorna los usuarios activos del departamento y colegio a cargo.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTrabajadores(): array
    {
        if (!$this->esJefeDepartamento() || $this->dbPrincipal === null) {
            return [];
        }

        if (isset($this->cache['trabajadores'])) {
            return $this->cache['trabajadores'];
        }

        $idDepartamento = (int) ($this->jefatura['id_departamento'] ?? 0);
        $idColegio = (int) ($this->jefatura['id_colegio'] ?? 0);
        if ($idDepartamento === 0 || $idColegio === 0) {
            return $this->cache['trabajadores'] = [];
        }

        try {
            return $this->cache['trabajadores'] = $this->dbPrincipal->fetchAll(
                "SELECT DISTINCT u.id, u.nombre, u.id_area_trabajo, u.estado,
                        uc.id_colegio
                   FROM usuarios u
                   JOIN usuario_colegio uc
                     ON uc.id_usuario = u.id AND uc.estado = 1
                  WHERE u.id_area_trabajo = ?
                    AND uc.id_colegio = ?
                    AND u.id <> ?
                    AND LOWER(CAST(u.estado AS CHAR)) IN ('1', 'activo')
               ORDER BY u.nombre ASC",
                [$idDepartamento, $idColegio, $this->idUsuario]
            );
        } catch (Throwable $ex) {
            $this->registrarError('consultar trabajadores', $ex);
            return $this->cache['trabajadores'] = [];
        }
    }

    /**
     * Retorna la jefatura y confirma que el departamento está habilitado para
     * el colegio. No presupone una tabla catálogo de departamentos.
     */
    public function getDepartamento(): ?array
    {
        if (!$this->esJefeDepartamento() || $this->dbPermisos === null) {
            return null;
        }

        if (array_key_exists('departamento', $this->cache)) {
            return $this->cache['departamento'];
        }

        try {
            $departamento = $this->dbPermisos->fetchOne(
                'SELECT jd.id AS id_jefatura, jd.id_usuario,
                        jd.id_departamento, jd.id_colegio,
                        cd.id AS id_colegio_departamento
                   FROM jefatura_departamento jd
                   JOIN colegio_departamento cd
                     ON cd.id_colegio = jd.id_colegio
                    AND cd.id_departamento = jd.id_departamento
                    AND cd.estado = 1
                  WHERE jd.id = ? AND jd.estado = 1
                  LIMIT 1',
                [(int) $this->jefatura['id']]
            );

            return $this->cache['departamento'] = ($departamento ?: null);
        } catch (Throwable $ex) {
            $this->registrarError('consultar departamento', $ex);
            return $this->cache['departamento'] = null;
        }
    }

    public function getColegio(): ?int
    {
        if (!$this->usuarioValido || $this->dbPrincipal === null) {
            return null;
        }

        if (array_key_exists('id_colegio', $this->cache)) {
            return $this->cache['id_colegio'];
        }

        if ($this->jefatura !== null && (int) ($this->jefatura['id_colegio'] ?? 0) > 0) {
            return $this->cache['id_colegio'] = (int) $this->jefatura['id_colegio'];
        }

        try {
            $colegio = $this->dbPrincipal->fetchOne(
                'SELECT id_colegio
                   FROM usuario_colegio
                  WHERE id_usuario = ? AND estado = 1
               ORDER BY id ASC
                  LIMIT 1',
                [$this->idUsuario]
            );
            $idColegio = (int) ($colegio['id_colegio'] ?? 0);
            return $this->cache['id_colegio'] = ($idColegio > 0 ? $idColegio : null);
        } catch (Throwable $ex) {
            $this->registrarError('consultar colegio', $ex);
            return $this->cache['id_colegio'] = null;
        }
    }

    /** Consulta la matriz general; las restricciones por recurso se validan aparte. */
    public function tienePermiso($accion): bool
    {
        if (!$this->usuarioValido || !is_string($accion)) {
            return false;
        }

        $accion = strtolower(trim($accion));
        $matriz = [
            self::ROL_SUPERADMIN => ['crear_ticket', 'ver_ticket', 'asignar_tecnico', 'cambiar_estado', 'ver_usuarios', 'calificar'],
            self::ROL_JEFE => ['crear_ticket', 'ver_ticket', 'asignar_tecnico', 'cambiar_estado', 'ver_usuarios', 'calificar'],
            self::ROL_TRABAJADOR => ['crear_ticket', 'ver_ticket', 'calificar'],
        ];

        return in_array($accion, $matriz[$this->rol] ?? [], true);
    }

    public function puedeVer($idTicket): bool
    {
        $idTicket = filter_var($idTicket, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
        if ($idTicket === 0 || !$this->tienePermiso('ver_ticket') || $this->dbPrincipal === null) {
            return false;
        }

        $cacheKey = 'ver_ticket_' . $idTicket;
        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        try {
            if ($this->esSuperAdmin()) {
                $ticket = $this->dbPrincipal->fetchOne(
                    'SELECT id_ticket FROM tickets WHERE id_ticket = ? AND estado = 1 LIMIT 1',
                    [$idTicket]
                );
                return $this->cache[$cacheKey] = (bool) $ticket;
            }

            if ($this->esJefeDepartamento()) {
                $departamento = $this->getDepartamento();
                if ($departamento === null) {
                    return $this->cache[$cacheKey] = false;
                }

                $ticket = $this->dbPrincipal->fetchOne(
                    'SELECT t.id_ticket
                       FROM tickets t
                       JOIN usuarios solicitante ON solicitante.id = t.id_usuario
                       JOIN usuario_colegio uc
                         ON uc.id_usuario = solicitante.id AND uc.estado = 1
                      WHERE t.id_ticket = ? AND t.estado = 1
                        AND solicitante.id_area_trabajo = ?
                        AND uc.id_colegio = ?
                      LIMIT 1',
                    [$idTicket, (int) $departamento['id_departamento'], (int) $departamento['id_colegio']]
                );
                return $this->cache[$cacheKey] = (bool) $ticket;
            }

            $ticket = $this->dbPrincipal->fetchOne(
                'SELECT id_ticket
                   FROM tickets
                  WHERE id_ticket = ? AND id_usuario = ? AND estado = 1
                  LIMIT 1',
                [$idTicket, $this->idUsuario]
            );
            return $this->cache[$cacheKey] = (bool) $ticket;
        } catch (Throwable $ex) {
            $this->registrarError('validar acceso al ticket', $ex);
            return $this->cache[$cacheKey] = false;
        }
    }

    public function puedeAsignar($idTecnico): bool
    {
        $idTecnico = filter_var($idTecnico, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
        if ($idTecnico === 0 || !$this->tienePermiso('asignar_tecnico') || $this->dbPrincipal === null) {
            return false;
        }

        $cacheKey = 'asignar_' . $idTecnico;
        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        try {
            if ($this->esSuperAdmin()) {
                $tecnico = $this->dbPrincipal->fetchOne(
                    "SELECT id FROM usuarios
                      WHERE id = ? AND LOWER(CAST(estado AS CHAR)) IN ('1', 'activo')
                      LIMIT 1",
                    [$idTecnico]
                );
                return $this->cache[$cacheKey] = (bool) $tecnico;
            }

            $departamento = $this->getDepartamento();
            if ($departamento === null) {
                return $this->cache[$cacheKey] = false;
            }

            $tecnico = $this->dbPrincipal->fetchOne(
                "SELECT u.id
                   FROM usuarios u
                   JOIN usuario_colegio uc
                     ON uc.id_usuario = u.id AND uc.estado = 1
                  WHERE u.id = ?
                    AND u.id_area_trabajo = ?
                    AND uc.id_colegio = ?
                    AND LOWER(CAST(u.estado AS CHAR)) IN ('1', 'activo')
                  LIMIT 1",
                [$idTecnico, (int) $departamento['id_departamento'], (int) $departamento['id_colegio']]
            );
            return $this->cache[$cacheKey] = (bool) $tecnico;
        } catch (Throwable $ex) {
            $this->registrarError('validar técnico', $ex);
            return $this->cache[$cacheKey] = false;
        }
    }

    private function estadoActivo(mixed $estado): bool
    {
        return in_array(strtolower(trim((string) $estado)), ['1', 'activo'], true);
    }

    private function registrarError(string $operacion, Throwable $ex): void
    {
        error_log(sprintf(
            'PermisosManager: no fue posible %s para usuario %d: %s',
            $operacion,
            $this->idUsuario,
            $ex->getMessage()
        ));
    }
}
