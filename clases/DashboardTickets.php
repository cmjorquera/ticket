<?php
declare(strict_types=1);

require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/Tickets/TicketUsuario.php';
require_once __DIR__ . '/Tickets/TicketTecnico.php';
require_once __DIR__ . '/Tickets/TicketAdministrador.php';
require_once __DIR__ . '/../helpers/tickets.php';

/** Datos y reglas de alcance del dashboard operativo de tickets. */
final class DashboardTickets
{
    private const ESTADOS = [1, 2, 3, 5, 4, 7, 6];
    private const KPI_ESTADOS = [2, 3, 5, 7];

    private Conexion $db;
    private int $usuarioId;
    private ?array $perfilesCache = null;
    private ?bool $administradorGlobal = null;
    private ?array $colegiosAdministrados = null;
    private bool $colegiosConsultados = false;

    public function __construct(Conexion $db, int $usuarioId)
    {
        if ($usuarioId <= 0) {
            throw new InvalidArgumentException('El usuario del dashboard no es válido.');
        }
        $this->db = $db;
        $this->usuarioId = $usuarioId;
    }

    /** @return array<int,array{clave:string,nombre:string}> */
    public function perfilesDisponibles(): array
    {
        if ($this->perfilesCache !== null) {
            return $this->perfilesCache;
        }
        $filas = $this->db->fetchAll(
            "SELECT DISTINCT p.id_perfil, p.nombre
               FROM perfiles p
               JOIN usuario_perfil up ON up.id_perfil = p.id_perfil
              WHERE up.id_usuario = ?
              UNION
             SELECT DISTINCT p.id_perfil, p.nombre
               FROM perfiles p
               JOIN usuario_colegio uc ON uc.id_perfil = p.id_perfil AND uc.estado = 1
              WHERE uc.id_usuario = ?
           ORDER BY id_perfil ASC",
            [$this->usuarioId, $this->usuarioId]
        );

        $perfiles = [];
        foreach ($filas as $fila) {
            $clave = self::normalizarPerfil((string) ($fila['nombre'] ?? ''));
            $perfiles[$clave] = [
                'clave' => $clave,
                'nombre' => self::nombrePerfil($clave),
            ];
        }

        if ($perfiles === []) {
            $perfiles['usuario'] = ['clave' => 'usuario', 'nombre' => 'Usuario'];
        }

        $prioridad = ['administrador' => 1, 'tecnico' => 2, 'usuario' => 3];
        uasort($perfiles, static fn (array $a, array $b): int => ($prioridad[$a['clave']] ?? 9) <=> ($prioridad[$b['clave']] ?? 9));
        return $this->perfilesCache = array_values($perfiles);
    }

    public function resolverPerfil(?string $solicitado = null): string
    {
        $disponibles = array_column($this->perfilesDisponibles(), 'clave');
        if (trim((string) $solicitado) !== '') {
            $perfil = self::normalizarPerfil((string) $solicitado);
            if (in_array($perfil, $disponibles, true)) {
                return $perfil;
            }
        }
        foreach (['administrador', 'tecnico', 'usuario'] as $preferido) {
            if (in_array($preferido, $disponibles, true)) {
                return $preferido;
            }
        }
        return 'usuario';
    }

    public function actualizarDemorados(): int
    {
        return $this->db->execute(
            "UPDATE tickets t
               JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
                SET t.id_estado = 7
              WHERE t.estado = 1
                AND t.id_estado IN (2, 3)
                AND pt.fecha_estimada_admin IS NOT NULL
                AND pt.fecha_estimada_admin <> '0000-00-00'
                AND pt.fecha_estimada_admin < CURDATE()"
        );
    }

    /** @return array<string,mixed> */
    public function obtenerDatos(string $perfil): array
    {
        $perfil = $this->resolverPerfil($perfil);
        $estados = $this->estadosConCantidad($perfil);

        return [
            'perfil' => $perfil,
            'perfiles' => $this->perfilesDisponibles(),
            'kpis' => $this->kpis($estados),
            'grafico_estados' => array_values($estados),
            'grafico_categorias' => $this->estadosPorCategoria($perfil, $estados),
            'tickets' => $this->tickets($perfil),
        ];
    }

    /** @return array<int,array<string,mixed>> */
    public function obtenerGraficoEstados(string $perfil): array
    {
        return array_values($this->estadosConCantidad($this->resolverPerfil($perfil)));
    }

    /** @return array{categorias:array<int,string>,series:array<int,array<string,mixed>>} */
    public function obtenerGraficoCategorias(string $perfil): array
    {
        $perfil = $this->resolverPerfil($perfil);
        $estados = $this->estadosConCantidad($perfil);
        return $this->estadosPorCategoria($perfil, $estados);
    }

    public static function normalizarPerfil(string $perfil): string
    {
        $perfil = ticket_normalizar_perfil($perfil);
        $perfilCompacto = str_replace('_', '', $perfil);
        if (str_contains($perfilCompacto, 'admin')) {
            return 'administrador';
        }
        if (str_contains($perfilCompacto, 'tecn')) {
            return 'tecnico';
        }
        return 'usuario';
    }

    private static function nombrePerfil(string $perfil): string
    {
        return match ($perfil) {
            'administrador' => 'Administrador',
            'tecnico' => 'Técnico',
            default => 'Usuario',
        };
    }

    /** @return array{0:string,1:array<int,int>} */
    private function alcance(string $perfil): array
    {
        if ($perfil === 'usuario') {
            return ['t.id_usuario = ?', [$this->usuarioId]];
        }
        if ($perfil === 'tecnico') {
            return ['t.id_tecnico = ?', [$this->usuarioId]];
        }
        if ($this->esAdministradorGlobal()) {
            return ['1 = 1', []];
        }

        $colegios = $this->obtenerColegiosAdministrados();
        if ($colegios === []) {
            return ['1 = 0', []];
        }
        $marcadores = implode(',', array_fill(0, count($colegios), '?'));
        return [
            "EXISTS (SELECT 1 FROM usuario_colegio uc_dashboard
                      WHERE uc_dashboard.id_usuario = t.id_usuario
                        AND uc_dashboard.estado = 1
                        AND uc_dashboard.id_colegio IN ({$marcadores}))",
            $colegios,
        ];
    }

    /** @return int[]|null */
    private function obtenerColegiosAdministrados(): ?array
    {
        if ($this->colegiosConsultados) {
            return $this->colegiosAdministrados;
        }
        $this->colegiosConsultados = true;
        if ($this->esAdministradorGlobal()) {
            return $this->colegiosAdministrados = null;
        }

        $filas = $this->db->fetchAll(
            "SELECT DISTINCT uc.id_colegio
               FROM usuario_colegio uc
          LEFT JOIN perfiles p ON p.id_perfil = uc.id_perfil
              WHERE uc.id_usuario = ?
                AND uc.estado = 1
                AND (uc.es_admin_colegio = 1
                     OR LOWER(p.nombre) IN ('admin colegio', 'admin_colegio', 'administrador colegio'))",
            [$this->usuarioId]
        );
        return $this->colegiosAdministrados = array_values(array_filter(array_map(
            static fn (array $fila): int => (int) ($fila['id_colegio'] ?? 0),
            $filas
        )));
    }

    private function esAdministradorGlobal(): bool
    {
        return $this->administradorGlobal ??= es_administrador_global($this->usuarioId, $this->db);
    }

    /** @return array<int,array<string,mixed>> */
    private function estadosConCantidad(string $perfil): array
    {
        [$alcance, $parametros] = $this->alcance($perfil);
        $conteos = $this->db->fetchAll(
            "SELECT t.id_estado, COUNT(*) AS cantidad
               FROM tickets t
              WHERE t.estado = 1 AND {$alcance}
           GROUP BY t.id_estado",
            $parametros
        );
        $porEstado = [];
        foreach ($conteos as $fila) {
            $porEstado[(int) ($fila['id_estado'] ?? 0)] = (int) ($fila['cantidad'] ?? 0);
        }

        $metadatos = $this->db->fetchAll(
            'SELECT id, nombre, color, color_degradado, descripcion_estado FROM estados_ticket WHERE id IN (1,2,3,4,5,6,7)'
        );
        $porId = [];
        foreach ($metadatos as $fila) {
            $porId[(int) ($fila['id'] ?? 0)] = $fila;
        }

        $fallback = [
            1 => ['nombre' => 'Recibido', 'color' => '#005B96'],
            2 => ['nombre' => 'Asignado', 'color' => '#0066CC'],
            3 => ['nombre' => 'En proceso', 'color' => '#FF9800'],
            4 => ['nombre' => 'Borrador', 'color' => '#6B7280'],
            5 => ['nombre' => 'Terminado', 'color' => '#28A745'],
            6 => ['nombre' => 'Cerrado', 'color' => '#3F424A'],
            7 => ['nombre' => 'Demorado', 'color' => '#DC3545'],
        ];
        $resultado = [];
        foreach (self::ESTADOS as $id) {
            $meta = $porId[$id] ?? [];
            $resultado[$id] = [
                'id_estado' => $id,
                'estado' => trim((string) ($meta['nombre'] ?? '')) ?: $fallback[$id]['nombre'],
                'color' => trim((string) ($meta['color'] ?? '')) ?: $fallback[$id]['color'],
                'color_degradado' => trim((string) ($meta['color_degradado'] ?? '')),
                'descripcion' => trim((string) ($meta['descripcion_estado'] ?? '')),
                'cantidad' => $porEstado[$id] ?? 0,
            ];
        }
        return $resultado;
    }

    /** @param array<int,array<string,mixed>> $estados */
    private function kpis(array $estados): array
    {
        $total = array_sum(array_column($estados, 'cantidad'));
        $iconos = [2 => 'bi-person-check', 3 => 'bi-hourglass-split', 5 => 'bi-check-circle', 7 => 'bi-exclamation-triangle'];
        $resultado = [];
        foreach (self::KPI_ESTADOS as $id) {
            $estado = $estados[$id];
            $cantidad = (int) $estado['cantidad'];
            $resultado[] = $estado + [
                'total' => $total,
                'porcentaje' => $total > 0 ? (int) round(($cantidad / $total) * 100) : 0,
                'icono' => $iconos[$id],
            ];
        }
        return $resultado;
    }

    /** @param array<int,array<string,mixed>> $estados */
    private function estadosPorCategoria(string $perfil, array $estados): array
    {
        $categorias = $this->db->fetchAll(
            'SELECT id_categoria, nombre_categoria FROM categoria_de_ticket WHERE estado = 1 ORDER BY orden ASC, nombre_categoria ASC'
        );
        [$alcance, $parametros] = $this->alcance($perfil);
        $filas = $this->db->fetchAll(
            "SELECT t.id_categoria_ticket, t.id_estado, COUNT(*) AS cantidad
               FROM tickets t
              WHERE t.estado = 1 AND {$alcance}
           GROUP BY t.id_categoria_ticket, t.id_estado",
            $parametros
        );
        $conteos = [];
        foreach ($filas as $fila) {
            $conteos[(int) ($fila['id_estado'] ?? 0)][(int) ($fila['id_categoria_ticket'] ?? 0)] = (int) ($fila['cantidad'] ?? 0);
        }

        $idsCategorias = array_map(static fn (array $fila): int => (int) $fila['id_categoria'], $categorias);
        $series = [];
        foreach (self::ESTADOS as $idEstado) {
            $series[] = [
                'name' => $estados[$idEstado]['estado'],
                'color' => $estados[$idEstado]['color'],
                'data' => array_map(static fn (int $idCategoria): int => $conteos[$idEstado][$idCategoria] ?? 0, $idsCategorias),
            ];
        }
        return [
            'categorias' => array_map(static fn (array $fila): string => (string) $fila['nombre_categoria'], $categorias),
            'series' => $series,
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function tickets(string $perfil): array
    {
        if ($perfil === 'tecnico') {
            $filas = (new TicketTecnico($this->db, $this->usuarioId))->traer();
        } elseif ($perfil === 'administrador') {
            $filas = (new TicketAdministrador($this->db, $this->usuarioId))->traer(
                null,
                null,
                $this->obtenerColegiosAdministrados()
            );
        } else {
            $filas = (new TicketUsuario($this->db, $this->usuarioId))->traer();
        }

        $resultado = [];
        foreach ($filas as $fila) {
            $ticket = ticket_normalizar_fila($fila);
            $resultado[] = [
                'id_ticket' => (int) ($ticket['id_ticket'] ?? 0),
                'id_estado' => (int) ($ticket['id_estado'] ?? 0),
                'estado' => (string) ($ticket['estado_nombre'] ?? 'Sin estado'),
                'estado_codigo' => (string) ($ticket['estado'] ?? ''),
                'estado_color' => (string) ($ticket['estado_color'] ?? ''),
                'asunto' => (string) ($ticket['asunto'] ?? ''),
                'categoria' => trim((string) ($ticket['categoria_nombre'] ?? '')) ?: 'Sin categoría',
                'usuario' => trim((string) ($ticket['usuario_nombre'] ?? '')) ?: 'Sin usuario',
                'tecnico' => trim((string) ($ticket['tecnico_nombre'] ?? '')) ?: 'Sin asignar',
                'colegio' => trim((string) ($ticket['colegio_nombre'] ?? '')) ?: 'Sin colegio',
                'prioridad' => (string) ($ticket['prioridad'] ?? 'Media'),
                'fecha_creacion' => (string) ($ticket['fecha_creacion'] ?? ''),
                'fecha_respuesta' => (string) ($ticket['fecha_respuesta'] ?? ''),
                'puede_calificar' => $perfil === 'usuario'
                    && (int) ($ticket['id_estado'] ?? 0) === 5
                    && empty($ticket['tiene_calificacion']),
            ];
        }

        usort($resultado, static function (array $a, array $b): int {
            $fechaA = strtotime((string) ($a['fecha_creacion'] ?? '')) ?: 0;
            $fechaB = strtotime((string) ($b['fecha_creacion'] ?? '')) ?: 0;
            return $fechaB <=> $fechaA;
        });

        return array_slice($resultado, 0, 100);
    }
}
