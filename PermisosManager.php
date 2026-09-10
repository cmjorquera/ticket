<?php
declare(strict_types=1);

require_once __DIR__ . '/clases/Conexion.php';

/**
 * PermisosManager - Gestor centralizado de permisos del Sistema SEDUC
 * 
 * ✅ Conecta a BD PRINCIPAL usando Conexion::getInstance()
 * ✅ Conecta a BD PERMISOS con credenciales PROPIAS (diferentes)
 * ✅ SOLO este archivo accede a BD permisos
 * 
 * @author Sistema SEDUC Panel Central
 * @version 2.0.1 (CORREGIDA)
 * @since 2026-09-10
 */
final class PermisosManager
{
    // ============================================================
    // CONSTANTES DE ROLES
    // ============================================================
    
    public const ROL_SUPERADMIN = 'SUPERADMIN';
    public const ROL_JEFE_DEPARTAMENTO = 'JEFE_DEPARTAMENTO';
    public const ROL_ADMIN_COLEGIO = 'ADMIN_COLEGIO';
    public const ROL_TECNICO = 'TECNICO';
    public const ROL_USUARIO = 'USUARIO';

    // ============================================================
    // PROPIEDADES PRIVADAS
    // ============================================================
    
    private int $idUsuario = 0;
    private ?Conexion $dbPrincipal = null;        // BD Principal: crisf668_sistema_panel_central
    private ?\PDO $dbPermisos = null;             // BD Permisos: crisf668_logica_permisos (CREDENCIALES PROPIAS)
    private ?array $usuario = null;
    private array $perfiles = [];
    private string $rolPrincipal = self::ROL_USUARIO;
    private ?array $jefatura = null;
    private bool $usuarioValido = false;
    private array $cache = [];

    // ============================================================
    // CREDENCIALES PARA BD PERMISOS (SOLO ESTE ARCHIVO)
    // CAMBIAR AQUÍ SI LAS CREDENCIALES SON DIFERENTES
    // ============================================================
    
    private const DB_PERMISOS_HOST = 'localhost';
    private const DB_PERMISOS_USER = 'crisf668_jorquera';
    private const DB_PERMISOS_PASS = 'Ingeniero86#';
    private const DB_PERMISOS_NAME = 'crisf668_logica_permisos';

    // ============================================================
    // CONSTRUCTOR
    // ============================================================
    
    /**
     * Constructor: Inicializa PermisosManager para un usuario específico
     * 
     * @param int $idUsuario ID del usuario desde BD principal
     * @param array $credencialesPermisos [opcional] Credenciales para BD permisos
     *                                    ['user' => '...', 'password' => '...']
     */
    public function __construct($idUsuario, array $credencialesPermisos = [])
    {
        // Validar ID del usuario
        $this->idUsuario = filter_var($idUsuario, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]) ?: 0;
        
        if ($this->idUsuario === 0) {
            $this->registrarError('ID de usuario inválido: ' . var_export($idUsuario, true));
            return;
        }

        try {
            // Conectar a ambas BDs
            $this->conectarBDs($credencialesPermisos);
            
            // Cargar datos del usuario
            $this->cargarDatos();
            
            // Determinar rol principal
            $this->determinarRolPrincipal();
            
        } catch (Throwable $ex) {
            $this->registrarError('Inicializar PermisosManager', $ex);
            $this->revocarDatos();
        }
    }

    // ============================================================
    // MÉTODOS PRIVADOS - CONEXIÓN A BDs
    // ============================================================
    
    /**
     * Conecta a ambas bases de datos
     * 
     * @param array $credencialesPermisos Credenciales opcionales para BD permisos
     * @throws Exception Si falla la conexión
     */
    private function conectarBDs(array $credencialesPermisos = []): void
    {
        // Conexión BD Principal usando Conexion.php existente
        // Mantiene las credenciales actuales: crist668_jorquera / Ingeniero86#
        $this->dbPrincipal = Conexion::getInstance('sistema_panel_central');
        
        if (!$this->dbPrincipal) {
            throw new RuntimeException('No se pudo conectar a BD principal');
        }

        // Conexión BD Permisos con credenciales PROPIAS
        $this->conectarBDPermisos($credencialesPermisos);
    }

    /**
     * Conecta a la base de datos de permisos con credenciales específicas
     * SOLO ESTE ARCHIVO conecta a BD permisos
     * 
     * @param array $credenciales ['user' => '...', 'password' => '...'] (opcional)
     * @throws PDOException Si falla la conexión
     */
    private function conectarBDPermisos(array $credenciales = []): void
    {
        // Usar credenciales proporcionadas O las del constantes
        $user = $credenciales['user'] ?? self::DB_PERMISOS_USER;
        $pass = $credenciales['password'] ?? self::DB_PERMISOS_PASS;
        
        $dsn = sprintf(
            "mysql:host=%s;dbname=%s;charset=utf8mb4",
            self::DB_PERMISOS_HOST,
            self::DB_PERMISOS_NAME
        );

        try {
            $this->dbPermisos = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (\PDOException $e) {
            throw new \PDOException(
                "Error conectando a BD Permisos (" . self::DB_PERMISOS_NAME . "): " . $e->getMessage()
            );
        }
    }

    // ============================================================
    // MÉTODOS PRIVADOS - CARGA DE DATOS
    // ============================================================
    
    /**
     * Carga todos los datos relevantes del usuario
     */
    private function cargarDatos(): void
    {
        if (!$this->dbPrincipal || !$this->dbPermisos) {
            throw new RuntimeException('Conexiones no inicializadas');
        }

        // Cargar usuario de BD principal
        $this->cargarUsuario();
        
        if (!$this->usuario || !$this->usuarioValido) {
            return;
        }
        
        // Cargar perfiles de BD permisos
        $this->cargarPerfiles();
        
        // Cargar jefatura si existe
        $this->cargarJefatura();
    }

    /**
     * Carga datos del usuario desde BD principal
     */
    private function cargarUsuario(): void
    {
        $sql = "
            SELECT 
                id, nombre, apellido_paterno, apellido_materno,
                email, telefono, id_area_trabajo, id_colegio,
                es_admin_global, estado, cargo
            FROM usuarios
            WHERE id = :id
            LIMIT 1
        ";
        
        try {
            $stmt = $this->dbPrincipal->getPDO()->prepare($sql);
            $stmt->execute([':id' => $this->idUsuario]);
            $this->usuario = $stmt->fetch() ?: null;
            
            // Validar que el usuario existe y está activo
            if ($this->usuario && in_array($this->usuario['estado'] ?? '', [1, '1', 'activo', 'Activo'])) {
                $this->usuarioValido = true;
            }
        } catch (Throwable $ex) {
            $this->registrarError('Cargar usuario', $ex);
        }
    }

    /**
     * Carga perfiles del usuario desde BD permisos
     */
    private function cargarPerfiles(): void
    {
        $sql = "
            SELECT 
                p.id_perfil,
                p.nombre,
                p.descripcion
            FROM usuario_perfil up
            JOIN perfiles p ON up.id_perfil = p.id_perfil
            WHERE up.id_usuario = :id
            AND up.estado = 1
            AND p.estado = 1
            ORDER BY p.id_perfil ASC
        ";
        
        try {
            $stmt = $this->dbPermisos->prepare($sql);
            $stmt->execute([':id' => $this->idUsuario]);
            
            $resultados = $stmt->fetchAll();
            $this->perfiles = [];
            
            foreach ($resultados as $row) {
                $this->perfiles[] = $row['nombre'];
            }
        } catch (Throwable $ex) {
            $this->registrarError('Cargar perfiles', $ex);
            // Si falla carga de perfiles, al menos tienen rol USUARIO
            $this->perfiles = [];
        }
    }

    /**
     * Carga información de jefatura si existe
     */
    private function cargarJefatura(): void
    {
        $sql = "
            SELECT 
                j.id,
                j.id_usuario,
                j.id_departamento,
                j.id_colegio,
                d.nombre_departamento,
                j.fecha_asignacion,
                j.estado
            FROM jefatura_departamento j
            LEFT JOIN departamentos d ON j.id_departamento = d.id_departamento
            WHERE j.id_usuario = :id
            AND j.estado = 1
            LIMIT 1
        ";
        
        try {
            $stmt = $this->dbPermisos->prepare($sql);
            $stmt->execute([':id' => $this->idUsuario]);
            $this->jefatura = $stmt->fetch() ?: null;
        } catch (Throwable $ex) {
            $this->registrarError('Cargar jefatura', $ex);
            $this->jefatura = null;
        }
    }

    /**
     * Determina el rol principal basado en perfiles
     * 
     * Orden de prioridad:
     * 1. SUPERADMIN (es_admin_global = 1 O perfil superadmin)
     * 2. JEFE_DEPARTAMENTO (admin_area + en jefatura)
     * 3. ADMIN_COLEGIO (admin_colegio)
     * 4. TECNICO (tecnico)
     * 5. USUARIO (default)
     */
    private function determinarRolPrincipal(): void
    {
        if (!$this->usuarioValido) {
            $this->rolPrincipal = self::ROL_USUARIO;
            return;
        }

        // Verificar SUPERADMIN
        if ($this->usuario['es_admin_global'] == 1 || in_array('superadmin', $this->perfiles)) {
            $this->rolPrincipal = self::ROL_SUPERADMIN;
            return;
        }
        
        // Verificar JEFE_DEPARTAMENTO
        if (in_array('admin_area', $this->perfiles) && $this->jefatura) {
            $this->rolPrincipal = self::ROL_JEFE_DEPARTAMENTO;
            return;
        }
        
        // Verificar ADMIN_COLEGIO
        if (in_array('admin_colegio', $this->perfiles)) {
            $this->rolPrincipal = self::ROL_ADMIN_COLEGIO;
            return;
        }
        
        // Verificar TECNICO
        if (in_array('tecnico', $this->perfiles)) {
            $this->rolPrincipal = self::ROL_TECNICO;
            return;
        }
        
        // Default: USUARIO
        $this->rolPrincipal = self::ROL_USUARIO;
    }

    // ============================================================
    // MÉTODOS PÚBLICOS - INFORMACIÓN DEL USUARIO
    // ============================================================
    
    /**
     * ¿Es un usuario válido?
     * 
     * @return bool true si el usuario fue cargado correctamente
     */
    public function esValido(): bool
    {
        return $this->usuarioValido;
    }

    /**
     * Obtiene todos los datos del usuario
     * 
     * @return array Datos del usuario o array vacío
     */
    public function getUsuario(): array
    {
        return $this->usuario ?? [];
    }

    /**
     * Obtiene todos los perfiles del usuario
     * 
     * @return array Array de nombres de perfiles
     */
    public function getPerfiles(): array
    {
        return $this->perfiles;
    }

    /**
     * Obtiene el rol principal del usuario
     * 
     * @return string 'SUPERADMIN', 'JEFE_DEPARTAMENTO', 'ADMIN_COLEGIO', 'TECNICO', 'USUARIO'
     */
    public function getRolPrincipal(): string
    {
        return $this->rolPrincipal;
    }

    /**
     * Obtiene datos de jefatura si existe
     * 
     * @return array|null Array con datos de jefatura o null
     */
    public function getJefatura(): ?array
    {
        return $this->jefatura;
    }

    /**
     * Obtiene el departamento que administra
     * 
     * @return array|null Array con id_departamento, nombre_departamento, etc.
     */
    public function getDepartamento(): ?array
    {
        if (!$this->jefatura) {
            return null;
        }
        
        return [
            'id_departamento' => $this->jefatura['id_departamento'],
            'nombre_departamento' => $this->jefatura['nombre_departamento'] ?? '',
            'id_colegio' => $this->jefatura['id_colegio'],
        ];
    }

    // ============================================================
    // MÉTODOS PÚBLICOS - VALIDACIÓN DE ROLES
    // ============================================================
    
    /**
     * ¿Es superadmin?
     * 
     * @return bool true si tiene acceso total
     */
    public function esSuperAdmin(): bool
    {
        return $this->rolPrincipal === self::ROL_SUPERADMIN;
    }

    /**
     * ¿Es jefe de departamento?
     * 
     * @return bool true si es jefe
     */
    public function esJefeDepartamento(): bool
    {
        return $this->rolPrincipal === self::ROL_JEFE_DEPARTAMENTO;
    }

    /**
     * ¿Es admin de colegio?
     * 
     * @return bool true si es admin colegio
     */
    public function esAdminColegio(): bool
    {
        return $this->rolPrincipal === self::ROL_ADMIN_COLEGIO;
    }

    /**
     * ¿Es técnico?
     * 
     * @return bool true si es técnico
     */
    public function esTecnico(): bool
    {
        return $this->rolPrincipal === self::ROL_TECNICO;
    }

    /**
     * ¿Es usuario normal?
     * 
     * @return bool true si solo tiene perfil usuario
     */
    public function esUsuarioNormal(): bool
    {
        return $this->rolPrincipal === self::ROL_USUARIO;
    }

    // ============================================================
    // MÉTODOS PÚBLICOS - VALIDACIÓN DE PERMISOS
    // ============================================================
    
    /**
     * Valida si tiene permiso para una acción
     * 
     * @param string $accion Acción a validar
     * @return bool true si tiene permiso
     */
    public function tienePermiso(string $accion): bool
    {
        if (!$this->usuarioValido) {
            return false;
        }

        // Superadmin puede TODO
        if ($this->esSuperAdmin()) {
            return true;
        }
        
        // Matriz de permisos por rol
        $permisos = [
            self::ROL_USUARIO => [
                'crear_ticket',
                'responder_ticket_propio',
                'calificar_ticket',
            ],
            self::ROL_TECNICO => [
                'ver_tickets_asignados',
                'responder_ticket',
                'cambiar_estado_asignado',
                'crear_ticket',
                'calificar_ticket',
            ],
            self::ROL_JEFE_DEPARTAMENTO => [
                'ver_tickets_depto',
                'asignar_tecnico',
                'cambiar_estado',
                'crear_ticket',
                'ver_usuarios_depto',
                'responder_ticket',
                'calificar_ticket',
            ],
            self::ROL_ADMIN_COLEGIO => [
                'ver_tickets_colegio',
                'asignar_tecnico_colegio',
                'cambiar_estado',
                'crear_ticket',
                'ver_usuarios_colegio',
                'crear_usuarios',
                'responder_ticket',
                'calificar_ticket',
            ]
        ];
        
        return in_array($accion, $permisos[$this->rolPrincipal] ?? []);
    }

    /**
     * ¿Puede ver un ticket específico?
     * 
     * @param int $idTicket ID del ticket
     * @return bool true si puede verlo
     */
    public function puedeVer(int $idTicket): bool
    {
        if (!$this->usuarioValido || !$this->dbPrincipal) {
            return false;
        }

        // Superadmin ve TODO
        if ($this->esSuperAdmin()) {
            return true;
        }
        
        // Obtener datos del ticket
        $sql = "SELECT id_usuario, id_tecnico, id_departamento FROM tickets WHERE id_ticket = ? LIMIT 1";
        
        try {
            $stmt = $this->dbPrincipal->getPDO()->prepare($sql);
            $stmt->execute([$idTicket]);
            $ticket = $stmt->fetch();
            
            if (!$ticket) {
                return false;
            }
            
            // Es su propio ticket
            if ($ticket['id_usuario'] == $this->idUsuario) {
                return true;
            }
            
            // Es técnico asignado
            if ($ticket['id_tecnico'] == $this->idUsuario) {
                return true;
            }
            
            // Es jefe del depto
            if ($this->esJefeDepartamento() && $ticket['id_departamento'] == $this->jefatura['id_departamento']) {
                return true;
            }
            
            return false;
        } catch (Throwable $ex) {
            $this->registrarError('Validar si puede ver ticket', $ex);
            return false;
        }
    }

    /**
     * ¿Puede asignar un técnico?
     * 
     * @param int $idTecnico ID del técnico
     * @return bool true si puede asignarlo
     */
    public function puedeAsignar(int $idTecnico): bool
    {
        if (!$this->usuarioValido || !$this->dbPrincipal) {
            return false;
        }

        // Superadmin puede asignar a cualquiera
        if ($this->esSuperAdmin()) {
            return true;
        }
        
        if (!$this->esJefeDepartamento() && !$this->esAdminColegio()) {
            return false;
        }
        
        // Obtener datos del técnico
        $sql = "SELECT id_area_trabajo, id_colegio FROM usuarios WHERE id = ? LIMIT 1";
        
        try {
            $stmt = $this->dbPrincipal->getPDO()->prepare($sql);
            $stmt->execute([$idTecnico]);
            $tecnico = $stmt->fetch();
            
            if (!$tecnico) {
                return false;
            }
            
            // Jefe puede asignar técnicos de su depto
            if ($this->esJefeDepartamento()) {
                return $tecnico['id_area_trabajo'] == $this->jefatura['id_departamento'];
            }
            
            // Admin colegio puede asignar técnicos de su colegio
            if ($this->esAdminColegio()) {
                return $tecnico['id_colegio'] == $this->usuario['id_colegio'];
            }
            
            return false;
        } catch (Throwable $ex) {
            $this->registrarError('Validar si puede asignar técnico', $ex);
            return false;
        }
    }

    /**
     * Obtiene trabajadores que dependen del usuario
     * 
     * @return array Array de usuarios
     */
    public function getTrabajadores(): array
    {
        if (!$this->usuarioValido || !$this->dbPrincipal) {
            return [];
        }

        try {
            if ($this->esJefeDepartamento()) {
                $sql = "
                    SELECT id, nombre, apellido_paterno, email, id_area_trabajo, cargo
                    FROM usuarios
                    WHERE id_area_trabajo = :area
                    AND id != :id
                    AND estado IN (1, 'Activo', 'activo')
                    ORDER BY nombre
                ";
                
                $stmt = $this->dbPrincipal->getPDO()->prepare($sql);
                $stmt->execute([
                    ':area' => $this->jefatura['id_departamento'] ?? 0,
                    ':id' => $this->idUsuario
                ]);
                
                return $stmt->fetchAll() ?: [];
            }
            
            if ($this->esAdminColegio()) {
                $sql = "
                    SELECT id, nombre, apellido_paterno, email, id_colegio, cargo
                    FROM usuarios
                    WHERE id_colegio = :colegio
                    AND id != :id
                    AND estado IN (1, 'Activo', 'activo')
                    ORDER BY nombre
                ";
                
                $stmt = $this->dbPrincipal->getPDO()->prepare($sql);
                $stmt->execute([
                    ':colegio' => $this->usuario['id_colegio'] ?? 0,
                    ':id' => $this->idUsuario
                ]);
                
                return $stmt->fetchAll() ?: [];
            }
            
            return [];
        } catch (Throwable $ex) {
            $this->registrarError('Obtener trabajadores', $ex);
            return [];
        }
    }

    /**
     * Resumen legible de permisos
     * 
     * @return string Resumen formateado
     */
    public function resumen(): string
    {
        if (!$this->usuarioValido) {
            return "Usuario inválido o no encontrado.\n";
        }

        $resumen = "=== RESUMEN DE PERMISOS ===\n";
        $resumen .= "Usuario: " . ($this->usuario['nombre'] ?? 'N/A') . "\n";
        $resumen .= "Rol Principal: {$this->rolPrincipal}\n";
        $resumen .= "Perfiles: " . (empty($this->perfiles) ? 'Ninguno' : implode(', ', $this->perfiles)) . "\n";
        
        if ($this->jefatura) {
            $resumen .= "Jefe de Depto: " . ($this->jefatura['nombre_departamento'] ?? 'N/A') . "\n";
        }
        
        return $resumen;
    }

    // ============================================================
    // MÉTODOS PRIVADOS - UTILIDAD
    // ============================================================
    
    /**
     * Revoca todos los datos (cierre de seguridad)
     */
    private function revocarDatos(): void
    {
        $this->usuario = null;
        $this->perfiles = [];
        $this->rolPrincipal = self::ROL_USUARIO;
        $this->jefatura = null;
        $this->usuarioValido = false;
        $this->cache = [];
    }

    /**
     * Registra errores de forma segura
     * 
     * @param string $contexto Contexto del error
     * @param Throwable|string $ex Excepción o mensaje
     */
    private function registrarError(string $contexto, $ex = ''): void
    {
        $mensaje = "PermisosManager - {$contexto}";
        
        if ($ex instanceof Throwable) {
            $mensaje .= ": " . $ex->getMessage();
        } else {
            $mensaje .= ": {$ex}";
        }
        
        error_log($mensaje);
    }
}
?>
