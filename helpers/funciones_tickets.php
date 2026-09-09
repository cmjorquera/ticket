<?php
declare(strict_types=1);

/** Genera los indicadores de tickets según la vista y su alcance autorizado. */
final class FuncionesTicket
{
    public const ROL_USUARIO = 3;
    public const ROL_TECNICO = 4;
    public const ROL_ADMIN = 5;

    private Conexion $db;
    private array $cache = [];

    public function __construct(Conexion $db)
    {
        $this->db = $db;
    }

    /**
     * @param int[]|null $colegioIds null permite alcance global; [] no permite colegios.
     * @return array{nuevo:int,en_proceso:int,resuelto:int,atrasado:int}
     */
    private function totales(int $usuarioId, int $idPagActual, ?array $colegioIds = null): array
    {
        if (!in_array($idPagActual, [self::ROL_USUARIO, self::ROL_TECNICO, self::ROL_ADMIN], true)) {
            throw new InvalidArgumentException('Rol de bandeja de tickets no válido.');
        }

        $colegioIds = $colegioIds === null
            ? null
            : array_values(array_unique(array_filter(array_map('intval', $colegioIds), static fn (int $id): bool => $id > 0)));
        $cacheKey = $idPagActual . ':' . $usuarioId . ':' . ($colegioIds === null ? 'global' : implode(',', $colegioIds));
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $totales = ['nuevo' => 0, 'en_proceso' => 0, 'resuelto' => 0, 'atrasado' => 0];
        $sql = "SELECT CASE
                    WHEN t.id_estado IN (1, 2) THEN 'nuevo'
                    WHEN t.id_estado = 3 THEN 'en_proceso'
                    WHEN t.id_estado IN (5, 6) THEN 'resuelto'
                    WHEN t.id_estado = 7 THEN 'atrasado'
                    ELSE 'borrador'
                END AS estado_resumen,
                COUNT(*) AS total
                  FROM tickets t
             LEFT JOIN (
                           SELECT id_usuario, MIN(id_colegio) AS id_colegio
                             FROM usuario_colegio
                            WHERE estado = 1
                         GROUP BY id_usuario
                       ) uc_ticket ON uc_ticket.id_usuario = t.id_usuario
                 WHERE t.estado = 1";
        $params = [];

        if ($idPagActual === self::ROL_USUARIO) {
            $sql .= ' AND t.id_usuario = ?';
            $params[] = $usuarioId;
        } elseif ($idPagActual === self::ROL_TECNICO) {
            $sql .= ' AND t.id_tecnico = ?';
            $params[] = $usuarioId;
        } elseif ($colegioIds !== null) {
            if ($colegioIds === []) {
                $sql .= ' AND 1 = 0';
            } else {
                $sql .= ' AND uc_ticket.id_colegio IN (' . implode(',', array_fill(0, count($colegioIds), '?')) . ')';
                array_push($params, ...$colegioIds);
            }
        }

        try {
            $filas = $this->db->fetchAll($sql . ' GROUP BY estado_resumen', $params);
            foreach ($filas as $fila) {
                $estado = strtolower((string) ($fila['estado_resumen'] ?? ''));
                if ($idPagActual === self::ROL_TECNICO && $estado === 'atrasado') {
                    continue;
                }
                if (array_key_exists($estado, $totales)) {
                    $totales[$estado] = (int) ($fila['total'] ?? 0);
                }
            }
        } catch (Throwable $ex) {
            error_log('Error al calcular contenedores de tickets: ' . $ex->getMessage());
            if (function_exists('ticket_debug_sql')) {
                ticket_debug_sql('CONTENEDORES', $sql . ' GROUP BY estado_resumen', $params, $ex);
            }
        }

        return $this->cache[$cacheKey] = $totales;
    }

    /** @return array{nuevo:string,en_proceso:string,resuelto:string,atrasado:string} */
    private function descripciones(): array
    {
        $cacheKey = 'descripciones-estados';
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $descripciones = [
            'nuevo' => 'El ticket fue recibido y está pendiente de asignación.',
            'en_proceso' => 'El técnico está trabajando activamente en este ticket.',
            'resuelto' => 'El ticket fue terminado y su solución está disponible.',
            'atrasado' => 'El ticket superó la fecha estimada de resolución.',
        ];
        $grupos = [1 => 'nuevo', 3 => 'en_proceso', 5 => 'resuelto', 7 => 'atrasado'];

        try {
            foreach ($this->db->fetchAll('SELECT id, descripcion_estado FROM estados_ticket WHERE id IN (1, 3, 5, 7)') as $fila) {
                $grupo = $grupos[(int) ($fila['id'] ?? 0)] ?? '';
                $descripcion = trim((string) ($fila['descripcion_estado'] ?? ''));
                if ($grupo !== '' && $descripcion !== '') {
                    $descripciones[$grupo] = $descripcion;
                }
            }
        } catch (Throwable $ex) {
            error_log('Error al cargar descripciones de estados de tickets: ' . $ex->getMessage());
        }

        return $this->cache[$cacheKey] = $descripciones;
    }

    /** @param int[]|null $colegioIds */
    private function renderizar(int $usuarioId, int $idPagActual, string $estado, string $titulo, string $icono, string $clase, ?array $colegioIds = null): void
    {
        $totales = $this->totales($usuarioId, $idPagActual, $colegioIds);
        $cantidad = (int) ($totales[$estado] ?? 0);
        $total = array_sum($totales);
        $porcentaje = $total > 0 ? (int) round(($cantidad / $total) * 100) : 0;
        $descripcion = $this->descripciones()[$estado];
        $tooltipId = 'tooltip-ticket-' . str_replace('_', '-', $estado);

        echo '<article class="contenedor-ticket ' . $clase . '" data-estado="' . htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') . '" aria-describedby="' . $tooltipId . '">';
        echo '<div class="contenedor-ticket-body"><h2 class="contenedor-ticket-titulo">' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</h2>';
        echo '<div class="contenedor-ticket-numero">' . $cantidad . '</div><small class="contenedor-ticket-porcentaje">' . $porcentaje . '% de la bandeja</small></div>';
        echo '<div class="contenedor-ticket-icono"><i class="bi ' . htmlspecialchars($icono, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true"></i>';
        echo '<span class="contenedor-ticket-tooltip" id="' . $tooltipId . '" role="tooltip">' . htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') . '</span></div>';
        echo '</article>';
    }

    /** @param int[]|null $colegioIds */
    public function contenedorTicketNuevos(int $usuarioId, int $idPagActual = self::ROL_TECNICO, ?array $colegioIds = null): void
    {
        $this->renderizar($usuarioId, $idPagActual, 'nuevo', 'Nuevos', 'bi-inbox', 'nuevos', $colegioIds);
    }

    /** @param int[]|null $colegioIds */
    public function contenedorTicketEnProceso(int $usuarioId, int $idPagActual = self::ROL_TECNICO, ?array $colegioIds = null): void
    {
        $this->renderizar($usuarioId, $idPagActual, 'en_proceso', 'En proceso', 'bi-hourglass-split', 'en-proceso', $colegioIds);
    }

    /** @param int[]|null $colegioIds */
    public function contenedorTicketResueltos(int $usuarioId, int $idPagActual = self::ROL_TECNICO, ?array $colegioIds = null): void
    {
        $this->renderizar($usuarioId, $idPagActual, 'resuelto', 'Resueltos', 'bi-check-circle', 'resueltos', $colegioIds);
    }

    /** @param int[]|null $colegioIds */
    public function contenedorTicketAtrasados(int $usuarioId, int $idPagActual = self::ROL_TECNICO, ?array $colegioIds = null): void
    {
        if ($idPagActual === self::ROL_TECNICO) {
            return;
        }
        $this->renderizar($usuarioId, $idPagActual, 'atrasado', 'Atrasados', 'bi-exclamation-triangle', 'atrasados', $colegioIds);
    }

    /** @param int[]|null $colegioIds */
    public function renderizarContenedores(int $usuarioId, int $idPagActual, ?array $colegioIds = null): void
    {
        echo '<div class="contenedor-tickets">';
        $this->contenedorTicketNuevos($usuarioId, $idPagActual, $colegioIds);
        $this->contenedorTicketEnProceso($usuarioId, $idPagActual, $colegioIds);
        $this->contenedorTicketResueltos($usuarioId, $idPagActual, $colegioIds);
        $this->contenedorTicketAtrasados($usuarioId, $idPagActual, $colegioIds);
        echo '</div>';
    }
}
