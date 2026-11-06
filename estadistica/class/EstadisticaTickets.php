<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/clases/Conexion.php';
require_once dirname(__DIR__, 2) . '/clases/DashboardTickets.php';

/**
 * Datos del módulo Estadística (sistema de tickets).
 *
 * Todas las consultas respetan el mismo alcance que el Dashboard:
 *  - usuario       → solo sus tickets
 *  - técnico       → solo los tickets asignados a él
 *  - administrador → todos (admin global) o los de sus colegios (jefaturas)
 * Se trabaja con tickets activos (tickets.estado = 1).
 */
final class EstadisticaTickets
{
    /** Estados que se consideran resueltos. */
    private const RESUELTOS = [5, 6];
    /** Borrador no cuenta en las estadísticas. */
    private const BORRADOR = 4;

    private const ETIQUETAS = [
        'Recibido'   => 'Sin técnico',
        'Asignado'   => 'Con técnico',
        'En proceso' => 'Técnico trabajando',
        'Terminado'  => 'Terminado',
        'Cerrado'    => 'Cerrado',
        'Demorado'   => 'Demorado',
    ];
    private const ICONOS = [
        'Recibido'   => 'bi-inbox',
        'Asignado'   => 'bi-person-check',
        'En proceso' => 'bi-tools',
        'Terminado'  => 'bi-check2',
        'Cerrado'    => 'bi-check2-circle',
        'Demorado'   => 'bi-exclamation-triangle',
    ];

    private Conexion $db;
    private DashboardTickets $dashboard;
    private string $perfil;
    private string $alcanceSql;
    private array $alcanceParams;

    public function __construct(Conexion $db, int $usuarioId, ?string $perfilSolicitado = null)
    {
        $this->db = $db;
        $this->dashboard = new DashboardTickets($db, $usuarioId);
        $this->perfil = $this->dashboard->resolverPerfil($perfilSolicitado);
        [$this->alcanceSql, $this->alcanceParams] = $this->dashboard->alcanceTickets($this->perfil);
    }

    public function perfil(): string
    {
        return $this->perfil;
    }

    /** @return array<int,array{clave:string,nombre:string}> */
    public function perfiles(): array
    {
        return $this->dashboard->perfilesDisponibles();
    }

    /** Colegio "principal" del solicitante (mismo criterio del sistema antiguo). */
    private function joinColegio(): string
    {
        return "LEFT JOIN (
                    SELECT uc.id_usuario, MIN(c.id_colegio) AS id_colegio, MIN(c.nom_colegio) AS nom_colegio
                      FROM usuario_colegio uc
                      JOIN colegio c ON c.id_colegio = uc.id_colegio
                     WHERE uc.estado = 1 AND c.estado = 1
                  GROUP BY uc.id_usuario
                ) col ON col.id_usuario = t.id_usuario";
    }

    /** WHERE base: activos, sin borradores y dentro del alcance. */
    private function where(string $extra = ''): array
    {
        $sql = 't.estado = 1 AND t.id_estado <> ' . self::BORRADOR . ' AND (' . $this->alcanceSql . ')';
        return [$extra !== '' ? $sql . ' AND ' . $extra : $sql, $this->alcanceParams];
    }

    // ------------------------------------------------------------------
    // Vida del ticket
    // ------------------------------------------------------------------

    /** @return array<int,array<string,mixed>> */
    public function listadoTickets(): array
    {
        [$where, $params] = $this->where();
        return $this->db->fetchAll(
            "SELECT t.id_ticket, t.id_estado, t.asunto,
                    et.nombre AS estado_nombre, et.color AS estado_color,
                    pr.nombre AS prioridad_nombre,
                    ct.nombre_categoria,
                    TRIM(CONCAT(COALESCE(u.nombre,''), ' ', COALESCE(u.apellido_paterno,''))) AS usuario_nombre,
                    TRIM(CONCAT(COALESCE(tec.nombre,''), ' ', COALESCE(tec.apellido_paterno,''))) AS tecnico_nombre,
                    COALESCE(col.nom_colegio, 'Sin colegio') AS colegio_nombre,
                    pt.fecha_creacion_inicio, pt.hora_creacion_inicio
               FROM tickets t
          LEFT JOIN estados_ticket et ON et.id = t.id_estado
          LEFT JOIN prioridad pr ON pr.id = t.id_prioridad
          LEFT JOIN categoria_de_ticket ct ON ct.id_categoria = t.id_categoria_ticket
          LEFT JOIN usuarios u ON u.id = t.id_usuario
          LEFT JOIN usuarios tec ON tec.id = t.id_tecnico
          {$this->joinColegio()}
          LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
              WHERE {$where}
           ORDER BY pt.fecha_creacion_inicio DESC, pt.hora_creacion_inicio DESC, t.id_ticket DESC",
            $params
        );
    }

    /**
     * Ticket con sus fechas de proceso y su actividad, solo si está dentro
     * del alcance del usuario. Devuelve null si no existe o no lo puede ver.
     */
    public function detalleTicket(int $idTicket): ?array
    {
        if ($idTicket <= 0) {
            return null;
        }
        [$where, $params] = $this->where('t.id_ticket = ?');
        $params[] = $idTicket;
        $ticket = $this->db->fetchOne(
            "SELECT t.id_ticket, t.asunto, t.descripcion_ticket, t.comentario_final,
                    et.nombre AS estado_nombre, et.color AS estado_color,
                    pr.nombre AS prioridad_nombre, ct.nombre_categoria,
                    COALESCE(col.nom_colegio, 'Sin colegio') AS colegio_nombre,
                    TRIM(CONCAT(COALESCE(u.nombre,''), ' ', COALESCE(u.apellido_paterno,''), ' ', COALESCE(u.apellido_materno,''))) AS usuario_nombre,
                    TRIM(CONCAT(COALESCE(tec.nombre,''), ' ', COALESCE(tec.apellido_paterno,''), ' ', COALESCE(tec.apellido_materno,''))) AS tecnico_nombre,
                    pt.fecha_creacion_inicio, pt.hora_creacion_inicio,
                    pt.fecha_estimada_admin,
                    pt.fecha_asignacion_tecnico, pt.hora_asignacion_tecnico,
                    pt.fecha_comienzo_ticket, pt.hora_comienzo_ticket,
                    pt.fecha_termino_ticket, pt.hora_termino_ticket,
                    pt.fecha_cierre_ticket, pt.hora_cierre_ticket
               FROM tickets t
          LEFT JOIN estados_ticket et ON et.id = t.id_estado
          LEFT JOIN prioridad pr ON pr.id = t.id_prioridad
          LEFT JOIN categoria_de_ticket ct ON ct.id_categoria = t.id_categoria_ticket
          LEFT JOIN usuarios u ON u.id = t.id_usuario
          LEFT JOIN usuarios tec ON tec.id = t.id_tecnico
          {$this->joinColegio()}
          LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
              WHERE {$where}
              LIMIT 1",
            $params
        );
        if (!$ticket) {
            return null;
        }

        $ticket['actividad'] = $this->actividadTicket($idTicket);
        return $ticket;
    }

    /**
     * Actividad registrada: avances técnicos (si existe la tabla del sistema
     * antiguo) y mensajes de la conversación del ticket. Más reciente primero.
     */
    private function actividadTicket(int $idTicket): array
    {
        $actividad = [];
        if ($this->tablaExiste('avance_tecnicos')) {
            foreach ($this->db->fetchAll(
                'SELECT accion, fecha_avance, hora_avance FROM avance_tecnicos WHERE id_ticket = ?',
                [$idTicket]
            ) as $fila) {
                $actividad[] = [
                    'tipo' => 'avance',
                    'autor' => 'Técnico',
                    'texto' => (string) $fila['accion'],
                    'fecha' => trim($fila['fecha_avance'] . ' ' . $fila['hora_avance']),
                ];
            }
        }
        if ($this->tablaExiste('ticket_conversaciones')) {
            foreach ($this->db->fetchAll(
                "SELECT c.mensaje, c.fecha, c.hora, c.adjunto,
                        TRIM(CONCAT(COALESCE(u.nombre,''), ' ', COALESCE(u.apellido_paterno,''))) AS autor
                   FROM ticket_conversaciones c
              LEFT JOIN usuarios u ON u.id = c.emisor
                  WHERE c.id_ticket = ? AND c.eliminado = 0",
                [$idTicket]
            ) as $fila) {
                $actividad[] = [
                    'tipo' => 'mensaje',
                    'autor' => $fila['autor'] !== '' ? $fila['autor'] : 'Usuario',
                    'texto' => (string) $fila['mensaje'] !== '' ? (string) $fila['mensaje'] : 'Adjuntó un archivo',
                    'fecha' => trim($fila['fecha'] . ' ' . $fila['hora']),
                ];
            }
        }
        usort($actividad, static fn (array $a, array $b): int => strcmp($b['fecha'], $a['fecha']));
        return $actividad;
    }

    // ------------------------------------------------------------------
    // Estadística general
    // ------------------------------------------------------------------

    /** Conteo por estado (todos los estados aunque tengan 0). */
    public function estados(): array
    {
        [$where, $params] = $this->where();
        $conteos = [];
        foreach ($this->db->fetchAll("SELECT t.id_estado, COUNT(*) AS total FROM tickets t WHERE {$where} GROUP BY t.id_estado", $params) as $fila) {
            $conteos[(int) $fila['id_estado']] = (int) $fila['total'];
        }
        $total = array_sum($conteos);
        $estados = [];
        foreach ($this->db->fetchAll('SELECT id, nombre, color FROM estados_ticket WHERE id <> ? ORDER BY orden ASC', [self::BORRADOR]) as $fila) {
            $nombre = (string) $fila['nombre'];
            $cantidad = $conteos[(int) $fila['id']] ?? 0;
            $estados[] = [
                'id' => (int) $fila['id'],
                'nombre' => $nombre,
                'etiqueta' => self::ETIQUETAS[$nombre] ?? $nombre,
                'icono' => self::ICONOS[$nombre] ?? 'bi-circle',
                'color' => (string) ($fila['color'] ?: '#94a3b8'),
                'total' => $cantidad,
                'pct' => $total > 0 ? round($cantidad / $total * 100, 1) : 0.0,
            ];
        }
        return $estados;
    }

    /** KPIs de la cabecera. */
    public function kpis(): array
    {
        [$where, $params] = $this->where();
        $fila = $this->db->fetchOne(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN t.id_estado IN (5,6) THEN 1 ELSE 0 END) AS resueltos,
                    SUM(CASE WHEN t.id_estado NOT IN (5,6) THEN 1 ELSE 0 END) AS abiertos,
                    SUM(CASE WHEN t.id_estado = 7 THEN 1 ELSE 0 END) AS demorados,
                    SUM(CASE WHEN t.id_tecnico IS NULL OR t.id_tecnico = 0 THEN 1 ELSE 0 END) AS sin_tecnico
               FROM tickets t
              WHERE {$where}",
            $params
        ) ?: [];
        [$whereRes, $paramsRes] = $this->where($this->condicionResolucion());
        $prom = $this->db->fetchOne(
            "SELECT ROUND(AVG(DATEDIFF(pt.fecha_termino_ticket, pt.fecha_creacion_inicio)), 1) AS dias
               FROM tickets t JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
              WHERE {$whereRes}",
            $paramsRes
        ) ?: [];
        return [
            'total' => (int) ($fila['total'] ?? 0),
            'resueltos' => (int) ($fila['resueltos'] ?? 0),
            'abiertos' => (int) ($fila['abiertos'] ?? 0),
            'demorados' => (int) ($fila['demorados'] ?? 0),
            'sin_tecnico' => (int) ($fila['sin_tecnico'] ?? 0),
            'dias_promedio' => isset($prom['dias']) ? (float) $prom['dias'] : null,
        ];
    }

    private function condicionResolucion(): string
    {
        return "pt.fecha_termino_ticket IS NOT NULL AND pt.fecha_termino_ticket <> '0000-00-00'
                AND pt.fecha_creacion_inicio IS NOT NULL AND pt.fecha_creacion_inicio <> '0000-00-00'
                AND DATEDIFF(pt.fecha_termino_ticket, pt.fecha_creacion_inicio) BETWEEN 0 AND 180";
    }

    /** Porcentaje de cada estado dentro de cada categoría. */
    public function estadosPorCategoria(): array
    {
        [$where, $params] = $this->where();
        $filas = $this->db->fetchAll(
            "SELECT COALESCE(ct.nombre_categoria, 'Sin categoría') AS categoria, t.id_estado, COUNT(*) AS total
               FROM tickets t
          LEFT JOIN categoria_de_ticket ct ON ct.id_categoria = t.id_categoria_ticket
              WHERE {$where}
           GROUP BY categoria, t.id_estado",
            $params
        );
        $datos = [];
        foreach ($filas as $fila) {
            $cat = (string) $fila['categoria'];
            $datos[$cat]['total'] = ($datos[$cat]['total'] ?? 0) + (int) $fila['total'];
            $datos[$cat]['estados'][(int) $fila['id_estado']] = (int) $fila['total'];
        }
        uasort($datos, static fn (array $a, array $b): int => $b['total'] <=> $a['total']);
        return $datos;
    }

    /** Tickets por colegio del solicitante, abiertos y resueltos. */
    public function porColegio(): array
    {
        [$where, $params] = $this->where();
        return $this->db->fetchAll(
            "SELECT COALESCE(col.nom_colegio, 'Sin colegio') AS colegio,
                    COUNT(*) AS total,
                    SUM(CASE WHEN t.id_estado IN (5,6) THEN 1 ELSE 0 END) AS resueltos,
                    SUM(CASE WHEN t.id_estado NOT IN (5,6) THEN 1 ELSE 0 END) AS abiertos,
                    SUM(CASE WHEN t.id_estado = 7 THEN 1 ELSE 0 END) AS demorados
               FROM tickets t
          {$this->joinColegio()}
              WHERE {$where}
           GROUP BY colegio
           ORDER BY total DESC, colegio ASC",
            $params
        );
    }

    /** Horas promedio entre cada etapa (solo tickets con todas las fechas de esa etapa). */
    public function tiemposPorEtapa(): array
    {
        $etapas = [
            'Recibido → Asignado' => ['fecha_creacion_inicio', 'hora_creacion_inicio', 'fecha_asignacion_tecnico', 'hora_asignacion_tecnico'],
            'Asignado → En proceso' => ['fecha_asignacion_tecnico', 'hora_asignacion_tecnico', 'fecha_comienzo_ticket', 'hora_comienzo_ticket'],
            'En proceso → Terminado' => ['fecha_comienzo_ticket', 'hora_comienzo_ticket', 'fecha_termino_ticket', 'hora_termino_ticket'],
            'Terminado → Cerrado' => ['fecha_termino_ticket', 'hora_termino_ticket', 'fecha_cierre_ticket', 'hora_cierre_ticket'],
        ];
        $resultado = [];
        foreach ($etapas as $nombre => [$f1, $h1, $f2, $h2]) {
            [$where, $params] = $this->where(
                "pt.{$f1} IS NOT NULL AND pt.{$f1} <> '0000-00-00' AND pt.{$f2} IS NOT NULL AND pt.{$f2} <> '0000-00-00'"
            );
            $fila = $this->db->fetchOne(
                "SELECT COUNT(*) AS casos,
                        ROUND(AVG(GREATEST(0, TIMESTAMPDIFF(MINUTE,
                              TIMESTAMP(pt.{$f1}, COALESCE(pt.{$h1}, '00:00:00')),
                              TIMESTAMP(pt.{$f2}, COALESCE(pt.{$h2}, '00:00:00'))))) / 60, 1) AS horas
                   FROM tickets t JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
                  WHERE {$where}",
                $params
            ) ?: [];
            $resultado[] = [
                'etapa' => $nombre,
                'casos' => (int) ($fila['casos'] ?? 0),
                'horas' => isset($fila['horas']) ? (float) $fila['horas'] : null,
            ];
        }
        return $resultado;
    }

    /** Resumen por técnico: asignados, abiertos, resueltos, demorados y días promedio. */
    public function porTecnico(): array
    {
        [$where, $params] = $this->where('t.id_tecnico > 0');
        return $this->db->fetchAll(
            "SELECT t.id_tecnico,
                    TRIM(CONCAT(COALESCE(u.nombre,''), ' ', COALESCE(u.apellido_paterno,''))) AS tecnico,
                    COUNT(*) AS total,
                    SUM(CASE WHEN t.id_estado IN (5,6) THEN 1 ELSE 0 END) AS resueltos,
                    SUM(CASE WHEN t.id_estado NOT IN (5,6) THEN 1 ELSE 0 END) AS abiertos,
                    SUM(CASE WHEN t.id_estado = 7 THEN 1 ELSE 0 END) AS demorados,
                    ROUND(AVG(CASE WHEN {$this->condicionResolucion()}
                                   THEN DATEDIFF(pt.fecha_termino_ticket, pt.fecha_creacion_inicio) END), 1) AS dias
               FROM tickets t
          LEFT JOIN usuarios u ON u.id = t.id_tecnico
          LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
              WHERE {$where}
           GROUP BY t.id_tecnico, tecnico
           ORDER BY total DESC",
            $params
        );
    }

    // ------------------------------------------------------------------
    // Estadística v2 (gráficos)
    // ------------------------------------------------------------------

    /** Tickets creados, resueltos y abiertos por mes (últimos 24 meses). */
    public function mensual(): array
    {
        [$where, $params] = $this->where(
            "pt.fecha_creacion_inicio IS NOT NULL AND pt.fecha_creacion_inicio <> '0000-00-00'
             AND pt.fecha_creacion_inicio >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 23 MONTH)"
        );
        return array_map(static fn (array $f): array => [
            'mes' => (string) $f['mes'],
            'total' => (int) $f['total'],
            'resueltos' => (int) $f['resueltos'],
            'abiertos' => (int) $f['abiertos'],
        ], $this->db->fetchAll(
            "SELECT DATE_FORMAT(pt.fecha_creacion_inicio, '%Y-%m') AS mes,
                    COUNT(*) AS total,
                    SUM(CASE WHEN t.id_estado IN (5,6) THEN 1 ELSE 0 END) AS resueltos,
                    SUM(CASE WHEN t.id_estado NOT IN (5,6) THEN 1 ELSE 0 END) AS abiertos
               FROM tickets t JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
              WHERE {$where}
           GROUP BY mes
           ORDER BY mes ASC",
            $params
        ));
    }

    /** Estados por técnico, para el gráfico de dona al elegir un técnico. */
    public function estadosPorTecnico(): array
    {
        [$where, $params] = $this->where('t.id_tecnico > 0');
        $datos = [];
        foreach ($this->db->fetchAll(
            "SELECT t.id_tecnico, et.nombre, et.color, COUNT(*) AS total
               FROM tickets t LEFT JOIN estados_ticket et ON et.id = t.id_estado
              WHERE {$where}
           GROUP BY t.id_tecnico, t.id_estado, et.nombre, et.color
           ORDER BY et.orden",
            $params
        ) as $fila) {
            $id = (string) $fila['id_tecnico'];
            $nombre = (string) ($fila['nombre'] ?? 'Sin estado');
            $datos[$id]['labels'][] = self::ETIQUETAS[$nombre] ?? $nombre;
            $datos[$id]['values'][] = (int) $fila['total'];
            $datos[$id]['colors'][] = (string) ($fila['color'] ?: '#94a3b8');
        }
        return $datos;
    }

    /** Categorías por volumen, total y por estado (para el filtro del gráfico). */
    public function categoriasPorEstado(): array
    {
        $resultado = ['todos' => []];
        foreach ($this->estadosPorCategoria() as $categoria => $info) {
            $resultado['todos'][$categoria] = $info['total'];
            foreach ($info['estados'] as $idEstado => $cantidad) {
                $resultado[(string) $idEstado][$categoria] = $cantidad;
            }
        }
        foreach ($resultado as &$serie) {
            arsort($serie);
        }
        return $resultado;
    }

    private function tablaExiste(string $tabla): bool
    {
        static $cache = [];
        if (!array_key_exists($tabla, $cache)) {
            $cache[$tabla] = (bool) $this->db->fetchOne(
                'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1',
                [$tabla]
            );
        }
        return $cache[$tabla];
    }
}
