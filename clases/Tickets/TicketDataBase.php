<?php
declare(strict_types=1);

require_once __DIR__ . '/../Conexion.php';
require_once __DIR__ . '/../../helpers/tickets.php';

/** Consulta base compartida por las bandejas de tickets. */
abstract class TicketDataBase
{
    protected Conexion $db;
    protected int $idUsuarioSession;

    /**
     * @param Conexion $db Conexión activa a la base de datos.
     * @param int $idUsuarioSession Identificador del usuario autenticado.
     */
    public function __construct(Conexion $db, int $idUsuarioSession)
    {
        if ($idUsuarioSession <= 0) {
            throw new InvalidArgumentException('El usuario de la sesión no es válido.');
        }

        $this->db = $db;
        $this->idUsuarioSession = $idUsuarioSession;
    }

    /**
     * Obtiene los tickets visibles para el tipo de bandeja.
     *
     * @param int|null $estado Estado opcional por el que se filtrará.
     * @return array<int,array<string,mixed>>
     */
    abstract public function traer(?int $estado = null): array;

    /**
     * Retorna el SELECT y los JOIN comunes a todas las bandejas.
     *
     * @return string
     */
    protected function queryBase(): string
    {
        $columnasAdjuntos = ticket_columnas_tabla('archivos_adjuntos_ticket', $this->db);
        $cantidadArchivos = isset($columnasAdjuntos['id_ticket'])
            ? '(SELECT COUNT(*) FROM archivos_adjuntos_ticket aa WHERE aa.id_ticket = t.id_ticket)'
            : '0';

        $tablaCalificacion = 'calificacion_ticket';
        $columnasCalificacion = ticket_columnas_tabla($tablaCalificacion, $this->db);
        if (!$columnasCalificacion) {
            $tablaCalificacion = 'calificacion_tickett';
            $columnasCalificacion = ticket_columnas_tabla($tablaCalificacion, $this->db);
        }

        $joinCalificacion = '';
        $idCalificacion = 'NULL';
        $tieneCalificacion = '0';
        if (isset($columnasCalificacion['id_ticket'])) {
            $joinCalificacion = "LEFT JOIN {$tablaCalificacion} ct ON ct.id_ticket = t.id_ticket";
            $idCalificacion = isset($columnasCalificacion['id_calificacion']) ? 'ct.id_calificacion' : 'ct.id_ticket';
            $tieneCalificacion = 'CASE WHEN ct.id_ticket IS NULL THEN 0 ELSE 1 END';
        }

        return "SELECT t.id_ticket, t.id_usuario, t.id_tecnico, t.id_estado,
                       t.id_categoria_ticket, t.id_categoria_ticket AS id_categoria,
                       t.id_prioridad, t.asunto,
                       t.descripcion_ticket AS descripcion,
                       et.nombre AS estado_nombre, et.nombre AS nombreEstado,
                       et.color AS estado_color, et.color AS colorEstado,
                       et.color_degradado AS estado_degradado,
                       CONCAT_WS(' ', us.nombre, us.apellido_paterno) AS usuario_nombre,
                       CONCAT_WS(' ', tc.nombre, tc.apellido_paterno) AS tecnico_nombre,
                       categoria.nombre_categoria AS categoria_nombre,
                       colegio.nom_colegio AS colegio_nombre,
                       uc_ticket.id_colegio,
                       COALESCE(CONCAT(pt.fecha_creacion_inicio, ' ', COALESCE(pt.hora_creacion_inicio, '00:00:00')), '') AS fecha_creacion,
                       COALESCE(CONCAT(pt.fecha_asignacion_tecnico, ' ', COALESCE(pt.hora_asignacion_tecnico, '00:00:00')), '') AS fecha_respuesta,
                       pt.fecha_creacion_inicio, pt.fecha_estimada_admin,
                       {$idCalificacion} AS id_calificacion,
                       {$tieneCalificacion} AS tiene_calificacion,
                       {$tieneCalificacion} AS tieneCalificacion,
                       CASE WHEN t.id_estado = 5 AND {$idCalificacion} IS NULL THEN 1 ELSE 0 END AS puede_calificar,
                       DATEDIFF(pt.fecha_estimada_admin, CURDATE()) AS dias_restantes,
                       (SELECT COUNT(*) FROM ticket_conversaciones mensajes
                         WHERE mensajes.id_ticket = t.id_ticket AND mensajes.leido = 0) AS mensajes_no_leidos,
                       {$cantidadArchivos} AS cantidad_archivos
                  FROM tickets t
                  JOIN estados_ticket et ON et.id = t.id_estado
                  JOIN usuarios us ON us.id = t.id_usuario
             LEFT JOIN categoria_de_ticket categoria ON categoria.id_categoria = t.id_categoria_ticket
             LEFT JOIN usuarios tc ON tc.id = t.id_tecnico
             LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
             LEFT JOIN (
                           SELECT id_usuario, MIN(id_colegio) AS id_colegio
                             FROM usuario_colegio
                            WHERE estado = 1
                         GROUP BY id_usuario
                       ) uc_ticket ON uc_ticket.id_usuario = t.id_usuario
             LEFT JOIN colegio ON colegio.id_colegio = uc_ticket.id_colegio
                  {$joinCalificacion}";
    }

    /**
     * Ejecuta la consulta común mediante parámetros preparados.
     *
     * @param string $condiciones Condiciones adicionales sin la palabra WHERE.
     * @param array<int,int|string> $parametros Valores de los marcadores de posición.
     * @return array<int,array<string,mixed>>
     */
    protected function ejecutar(string $condiciones = '', array $parametros = []): array
    {
        $sql = $this->queryBase() . ' WHERE t.estado = 1';
        if ($condiciones !== '') {
            $sql .= ' AND ' . $condiciones;
        }
        $sql .= ' GROUP BY t.id_ticket ORDER BY t.id_estado ASC, pt.fecha_creacion_inicio DESC, pt.hora_creacion_inicio DESC';

        return $this->db->fetchAll($sql, $parametros);
    }
}
