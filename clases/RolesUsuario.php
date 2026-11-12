<?php
declare(strict_types=1);

require_once __DIR__ . '/Conexion.php';

/**
 * Reglas del modelo de roles (configuracion/logica/LOGICA_ROLES.txt).
 *
 *   usuario_perfil  → QUÉ PUEDE HACER (global): 1 usuario, 2 técnico, 3 super_admin
 *   usuario_colegio → DÓNDE Y CON QUÉ CARGO: rol admin_colegio | jefe_departamento | trabajador
 *                     en un colegio y (salvo admin_colegio) un departamento.
 *
 * Quién crea qué:
 *   super_admin        → todo (es el único que crea administradores de colegio)
 *   admin_colegio      → jefes y trabajadores de SU colegio (y sus departamentos)
 *   jefe_departamento  → trabajadores de SU departamento
 *   trabajador         → nada
 *   Seduc (colegio 15) no tiene administrador de colegio.
 *
 * Si todavía no se ejecutó configuracion/sql/01_roles_estructura.sql
 * (no existe usuario_colegio.rol), modeloActivo() devuelve false y el resto
 * del sistema sigue con la lógica anterior (jefatura_departamento).
 */
final class RolesUsuario
{
    public const ID_SEDUC = 15;
    /** Departamento de Seduc que atiende los tickets (Configuración → Categorías). */
    public const DEPTO_TECNICOS = 'INFORMATICA';
    public const PERFIL_USUARIO = 1;
    public const PERFIL_TECNICO = 2;
    public const PERFIL_SUPER = 3;
    public const ROLES = [
        'admin_colegio' => 'Administrador del colegio',
        'jefe_departamento' => 'Jefe de departamento',
        'trabajador' => 'Trabajador',
    ];

    private static ?bool $activo = null;
    private Conexion $db;
    /** @var array<int,array> */
    private array $cacheAlcance = [];

    public function __construct(Conexion $db)
    {
        $this->db = $db;
    }

    public function modeloActivo(): bool
    {
        if (self::$activo === null) {
            try {
                self::$activo = (bool) $this->db->fetchOne(
                    "SELECT 1 FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuario_colegio' AND COLUMN_NAME = 'rol'"
                );
            } catch (Throwable $e) {
                self::$activo = false;
            }
        }
        return self::$activo;
    }

    public function esSuperAdmin(int $idUsuario): bool
    {
        return $this->alcance($idUsuario)['super'];
    }

    /**
     * Lo que la persona administra:
     *   super            bool
     *   colegios_admin   int[]  colegios donde es admin_colegio
     *   deptos_jefe      list<array{0:int,1:int}>  [id_colegio, id_departamento] donde es jefe
     *   asignaciones     filas de usuario_colegio activas
     */
    public function alcance(int $idUsuario): array
    {
        if (isset($this->cacheAlcance[$idUsuario])) {
            return $this->cacheAlcance[$idUsuario];
        }
        $super = (bool) $this->db->fetchOne(
            'SELECT 1 FROM usuario_perfil WHERE id_usuario = ? AND id_perfil = ? LIMIT 1',
            [$idUsuario, self::PERFIL_SUPER]
        );
        $asig = $this->modeloActivo() ? $this->asignaciones($idUsuario) : [];
        $admin = [];
        $jefe = [];
        foreach ($asig as $a) {
            if ($a['rol'] === 'admin_colegio') {
                $admin[] = (int) $a['id_colegio'];
            } elseif ($a['rol'] === 'jefe_departamento' && $a['id_departamento_colegio']) {
                $jefe[] = [(int) $a['id_colegio'], (int) $a['id_departamento_colegio']];
            }
        }
        return $this->cacheAlcance[$idUsuario] = [
            'super' => $super,
            'colegios_admin' => array_values(array_unique($admin)),
            'deptos_jefe' => $jefe,
            'asignaciones' => $asig,
        ];
    }

    public function asignaciones(int $idUsuario): array
    {
        return $this->db->fetchAll(
            "SELECT uc.id, uc.id_colegio, uc.id_departamento_colegio, uc.rol, c.nom_colegio, d.nombre_departamento
               FROM usuario_colegio uc
               JOIN colegio c ON c.id_colegio = uc.id_colegio
          LEFT JOIN departamentos_colegio d ON d.id = uc.id_departamento_colegio
              WHERE uc.id_usuario = ? AND uc.estado = 1
           ORDER BY FIELD(uc.rol, 'admin_colegio', 'jefe_departamento', 'trabajador'), c.nom_colegio",
            [$idUsuario]
        );
    }

    /**
     * Condición SQL sobre usuarios.id: personas activas en un departamento de un
     * colegio (por defecto, Informática de Seduc = quienes atienden tickets).
     * @return array{0:string,1:array} [condición, parámetros]
     */
    public static function condicionDepartamento(string $alias = 'u', int $idColegio = self::ID_SEDUC, string $departamento = self::DEPTO_TECNICOS): array
    {
        $alias = preg_replace('/\W/', '', $alias) ?: 'u';
        return ["{$alias}.id IN (SELECT uc.id_usuario FROM usuario_colegio uc
                                  JOIN departamentos_colegio d ON d.id = uc.id_departamento_colegio
                                 WHERE uc.estado = 1 AND uc.id_colegio = ? AND d.id_colegio = uc.id_colegio
                                   AND d.nombre_departamento = ?)", [$idColegio, $departamento]];
    }

    /** ¿Es funcionario de Informática de Seduc? */
    public function esDeInformaticaSeduc(int $idUsuario): bool
    {
        if (!$this->modeloActivo()) {
            return false;
        }
        [$cond, $p] = self::condicionDepartamento('u');
        return (bool) $this->db->fetchOne("SELECT 1 FROM usuarios u WHERE u.id = ? AND {$cond}", array_merge([$idUsuario], $p));
    }

    /** ¿Puede crear usuarios (o administrar a alguien)? */
    public function puedeAdministrar(int $idUsuario): bool
    {
        $a = $this->alcance($idUsuario);
        return $a['super'] || $a['colegios_admin'] || $a['deptos_jefe'];
    }

    /** Roles que $actor puede asignar en ese colegio / departamento. */
    public function rolesPermitidos(int $actor, int $idColegio, ?int $idDepartamento): array
    {
        $a = $this->alcance($actor);
        if ($a['super']) {
            return $idColegio === self::ID_SEDUC
                ? ['jefe_departamento', 'trabajador']
                : ['admin_colegio', 'jefe_departamento', 'trabajador'];
        }
        if (in_array($idColegio, $a['colegios_admin'], true)) {
            return ['jefe_departamento', 'trabajador'];
        }
        foreach ($a['deptos_jefe'] as [$c, $d]) {
            if ($c === $idColegio && ($idDepartamento === null || $d === $idDepartamento)) {
                return ['trabajador'];
            }
        }
        return [];
    }

    /**
     * Colegios y departamentos que $actor puede usar al crear un usuario,
     * con los roles permitidos en cada colegio.
     */
    public function opcionesAlta(int $actor): array
    {
        $a = $this->alcance($actor);
        $colegios = $this->db->fetchAll(
            'SELECT id_colegio, nom_colegio FROM colegio WHERE estado = 1 ORDER BY id_colegio = ? DESC, nom_colegio',
            [self::ID_SEDUC]
        );
        $deps = $this->db->fetchAll(
            'SELECT id, id_colegio, nombre_departamento FROM departamentos_colegio WHERE estado = 1 ORDER BY nombre_departamento'
        );
        $porColegio = [];
        foreach ($deps as $d) {
            $porColegio[(int) $d['id_colegio']][] = ['id' => (int) $d['id'], 'nombre' => (string) $d['nombre_departamento']];
        }
        $jefeEn = [];
        foreach ($a['deptos_jefe'] as [$c, $d]) {
            $jefeEn[$c][] = $d;
        }
        $res = [];
        foreach ($colegios as $c) {
            $id = (int) $c['id_colegio'];
            $deptos = $porColegio[$id] ?? [];
            if ($a['super'] || in_array($id, $a['colegios_admin'], true)) {
                // todos sus departamentos
            } elseif (isset($jefeEn[$id])) {
                $deptos = array_values(array_filter($deptos, static fn ($d) => in_array($d['id'], $jefeEn[$id], true)));
            } else {
                continue;
            }
            $res[] = [
                'id' => $id,
                'nombre' => (string) $c['nom_colegio'],
                'departamentos' => $deptos,
                'roles' => $this->rolesPermitidos($actor, $id, null),
            ];
        }
        return $res;
    }

    /** ¿$actor puede editar, bloquear o cambiar permisos de $objetivo? */
    public function puedeGestionar(int $actor, int $objetivo): bool
    {
        if ($actor <= 0 || $objetivo <= 0 || $actor === $objetivo) {
            return false;
        }
        $a = $this->alcance($actor);
        $o = $this->alcance($objetivo);
        if ($a['super']) {
            return true;
        }
        if ($o['super']) {
            return false;
        }
        foreach ($o['asignaciones'] as $asig) {
            $c = (int) $asig['id_colegio'];
            $d = (int) $asig['id_departamento_colegio'];
            if (in_array($c, $a['colegios_admin'], true) && $asig['rol'] !== 'admin_colegio') {
                return true;
            }
            foreach ($a['deptos_jefe'] as [$jc, $jd]) {
                if ($jc === $c && $jd === $d && $asig['rol'] === 'trabajador') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Condición SQL (sobre el alias de usuarios) con las personas que $actor puede VER
     * en el listado: super → todos; admin → su colegio; jefe → su departamento;
     * trabajador → su(s) departamento(s).
     */
    public function filtroVisibles(int $actor, string $alias = 'u'): array
    {
        $a = $this->alcance($actor);
        $alias = preg_replace('/\W/', '', $alias) ?: 'u';
        if ($a['super']) {
            return ['1 = 1', []];
        }
        $cond = [];
        $p = [];
        foreach ($a['colegios_admin'] as $c) {
            $cond[] = 'v.id_colegio = ?';
            $p[] = $c;
        }
        foreach ($a['asignaciones'] as $asig) {
            if ($asig['rol'] !== 'admin_colegio' && $asig['id_departamento_colegio']) {
                $cond[] = '(v.id_colegio = ? AND v.id_departamento_colegio = ?)';
                array_push($p, (int) $asig['id_colegio'], (int) $asig['id_departamento_colegio']);
            }
        }
        if (!$cond) {
            return ["{$alias}.id = ?", [$actor]];
        }
        return ["({$alias}.id = ? OR {$alias}.id IN (SELECT v.id_usuario FROM usuario_colegio v WHERE v.estado = 1 AND (" . implode(' OR ', $cond) . ')))',
                array_merge([$actor], $p)];
    }

    /**
     * Condición SQL (sobre el alias de usuarios) con las personas que $actor puede
     * ver como COLABORADORES (directorio, mensajes):
     *   super_admin → todas;
     *   el resto    → las personas de sus colegios (asignación activa en
     *                 usuario_colegio) + los super_admin, que están en todos.
     * @return array{0:string,1:array} [condición, parámetros]
     */
    public function condicionColaboradores(int $actor, string $alias = 'u'): array
    {
        $alias = preg_replace('/\W/', '', $alias) ?: 'u';
        if ($this->esSuperAdmin($actor)) {
            return ['1 = 1', []];
        }
        return ["({$alias}.id IN (SELECT cv.id_usuario FROM usuario_colegio cv
                                    WHERE cv.estado = 1
                                      AND cv.id_colegio IN (SELECT ca.id_colegio FROM usuario_colegio ca
                                                             WHERE ca.id_usuario = ? AND ca.estado = 1))
                 OR {$alias}.id IN (SELECT sa.id_usuario FROM usuario_perfil sa WHERE sa.id_perfil = " . self::PERFIL_SUPER . "))",
                [$actor]];
    }
}
