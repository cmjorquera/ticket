<?php
declare(strict_types=1);

require_once __DIR__ . '/../Conexion.php';
require_once __DIR__ . '/../../helpers/tickets.php';

/**
 * Bitácora del ticket: avances del técnico y conversación INTERNA con administración.
 * El solicitante no la ve. Lógica: ticket/logica/LOGICA_BITACORA.txt
 *
 * Tabla avance_tecnicos (la del sistema antiguo, ampliada por ticket/sql/02_bitacora.sql):
 *   id, id_ticket, accion (texto), fecha_avance, hora_avance,
 *   id_usuario, lado ('tecnico' | 'admin' | 'sistema'), leido, eliminado
 *
 *   tecnico  → lo escribió el técnico asignado
 *   admin    → lo escribió alguien de administración (super admin / admin del colegio)
 *   sistema  → registro automático: creación, asignación, cambios de estado
 *
 * No leído para el técnico = entradas de administración con leido = 0, y al revés.
 * Las propias nunca cuentan (sirve cuando la misma persona es técnico y admin).
 */
final class BitacoraTicket
{
    public const LARGO_MAXIMO = 3000;
    private static ?bool $disponible = null;
    private Conexion $db;

    public function __construct(Conexion $db)
    {
        $this->db = $db;
    }

    /** ¿Ya se ejecutó ticket/sql/02_bitacora.sql? */
    public function disponible(): bool
    {
        if (self::$disponible === null) {
            try {
                self::$disponible = (bool) $this->db->fetchOne(
                    "SELECT 1 FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'avance_tecnicos' AND COLUMN_NAME = 'lado'"
                );
            } catch (Throwable $e) {
                self::$disponible = false;
            }
        }
        return self::$disponible;
    }

    /** Papel de la persona en la bitácora: 'tecnico', 'admin' o null (no la ve). */
    public function rol(int $usuarioId, array $ticket): ?string
    {
        if ((int) ($ticket['id_tecnico_asignado'] ?? $ticket['id_tecnico'] ?? 0) === $usuarioId) {
            return 'tecnico';
        }
        // Quien pidió el ticket no ve la bitácora interna, aunque administre su colegio
        // (salvo el super admin, que lo ve todo).
        if ((int) ($ticket['id_usuario'] ?? 0) === $usuarioId && !es_administrador_global($usuarioId, $this->db)) {
            return null;
        }
        return puede_administrar_ticket($usuarioId, (int) ($ticket['id_colegio'] ?? 0), $this->db) ? 'admin' : null;
    }

    /**
     * Papel según la bandeja desde la que se abrió el detalle ('tecnico' | 'admin').
     * Si la persona no cumple ese papel, se usa el que le corresponda (rol()).
     */
    public function rolPara(int $usuarioId, array $ticket, string $pedido): ?string
    {
        if ($pedido === 'tecnico' && (int) ($ticket['id_tecnico_asignado'] ?? $ticket['id_tecnico'] ?? 0) === $usuarioId) {
            return 'tecnico';
        }
        if ($pedido === 'admin' && puede_administrar_ticket($usuarioId, (int) ($ticket['id_colegio'] ?? 0), $this->db)) {
            return 'admin';
        }
        return $pedido === 'usuario' ? null : $this->rol($usuarioId, $ticket);
    }

    private static function otroLado(string $rol): string
    {
        return $rol === 'tecnico' ? 'admin' : 'tecnico';
    }

    /** Subconsulta para las bandejas: entradas sin leer del otro lado (o '0'). */
    public function sqlNoLeidos(string $rol, int $usuarioId, string $t = 't'): string
    {
        if (!$this->disponible() || !in_array($rol, ['tecnico', 'admin'], true)) {
            return '0';
        }
        $otro = self::otroLado($rol);
        return "(SELECT COUNT(*) FROM avance_tecnicos b
                  WHERE b.id_ticket = {$t}.id_ticket AND b.eliminado = 0 AND b.leido = 0
                    AND b.lado = '{$otro}' AND COALESCE(b.id_usuario, 0) <> " . (int) $usuarioId . ')';
    }

    /** Entradas de la bitácora en orden, vistas por $usuarioId. */
    public function listar(int $ticketId, int $usuarioId): array
    {
        if (!$this->disponible()) {
            return [];
        }
        $filas = $this->db->fetchAll(
            "SELECT b.id, b.accion, b.fecha_avance, b.hora_avance, b.id_usuario, b.lado, b.leido,
                    TRIM(CONCAT_WS(' ', u.nombre, u.apellido_paterno)) AS autor
               FROM avance_tecnicos b
          LEFT JOIN usuarios u ON u.id = b.id_usuario
              WHERE b.id_ticket = ? AND b.eliminado = 0
           ORDER BY b.fecha_avance ASC, b.hora_avance ASC, b.id ASC",
            [$ticketId]
        );
        return array_map(static function (array $f) use ($usuarioId): array {
            [$texto, $autorAntiguo] = self::textoAntiguo((string) $f['accion']);
            return [
                'id' => (int) $f['id'],
                'lado' => (string) ($f['lado'] ?: 'tecnico'),
                'autor' => (string) ($f['autor'] ?: ($autorAntiguo ?: ($f['lado'] === 'sistema' ? 'Sistema' : 'Técnico'))),
                'propio' => (int) $f['id_usuario'] === $usuarioId && $f['lado'] !== 'sistema',
                'visto' => (int) $f['leido'] === 1,
                'fecha' => trim($f['fecha_avance'] . ' ' . $f['hora_avance']),
                'texto' => $texto,
            ];
        }, $filas);
    }

    /**
     * El sistema antiguo guardaba '<div data-avance-autor="Nombre" ...>texto</div>'.
     * @return array{0:string,1:string} [texto limpio, autor]
     */
    private static function textoAntiguo(string $accion): array
    {
        $autor = preg_match('/data-avance-autor="([^"]*)"/', $accion, $m) ? html_entity_decode($m[1], ENT_QUOTES, 'UTF-8') : '';
        $texto = html_entity_decode(strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], "\n", $accion)), ENT_QUOTES, 'UTF-8');
        return [trim(stripslashes($texto)), trim($autor)];
    }

    public function registrar(int $ticketId, int $usuarioId, string $lado, string $texto): int
    {
        if (!$this->disponible()) {
            throw new RuntimeException('La bitácora todavía no está instalada (ticket/sql/02_bitacora.sql).');
        }
        $this->db->execute(
            'INSERT INTO avance_tecnicos (id_ticket, accion, fecha_avance, hora_avance, id_usuario, lado, leido, eliminado)
             VALUES (?, ?, CURDATE(), CURTIME(), ?, ?, ?, 0)',
            [$ticketId, $texto, $usuarioId ?: null, $lado, $lado === 'sistema' ? 1 : 0]
        );
        return (int) $this->db->lastInsertId();
    }

    /** Registro automático (no interrumpe la acción principal si falla). */
    public function registrarSistema(int $ticketId, int $usuarioId, string $texto): void
    {
        try {
            if ($this->disponible()) {
                $this->registrar($ticketId, $usuarioId, 'sistema', $texto);
            }
        } catch (Throwable $e) {
            error_log('[bitacora] ' . $e->getMessage());
        }
    }

    /** Marca como leídas las entradas del otro lado. */
    public function marcarLeidos(int $ticketId, string $rol, int $usuarioId): int
    {
        if (!$this->disponible() || !in_array($rol, ['tecnico', 'admin'], true)) {
            return 0;
        }
        return $this->db->execute(
            'UPDATE avance_tecnicos SET leido = 1
              WHERE id_ticket = ? AND leido = 0 AND eliminado = 0 AND lado = ? AND COALESCE(id_usuario, 0) <> ?',
            [$ticketId, self::otroLado($rol), $usuarioId]
        );
    }

    /** No leídos por ticket para la bandeja ($rol) — solo tickets donde la persona cumple ese papel. */
    public function noLeidos(int $usuarioId, string $rol, array $ids): array
    {
        $ids = array_slice(array_values(array_unique(array_filter(array_map('intval', $ids), static fn ($v) => $v > 0))), 0, 200);
        if (!$ids || !$this->disponible() || !in_array($rol, ['tecnico', 'admin'], true)) {
            return [];
        }
        $marcas = implode(',', array_fill(0, count($ids), '?'));
        $filas = $this->db->fetchAll(
            "SELECT t.id_ticket, t.id_tecnico, t.id_usuario, " . $this->sqlNoLeidos($rol, $usuarioId, 't') . " AS n
               FROM tickets t WHERE t.id_ticket IN ({$marcas}) AND t.estado = 1",
            $ids
        );
        $res = [];
        foreach ($filas as $f) {
            if ($rol === 'tecnico' && (int) $f['id_tecnico'] !== $usuarioId) {
                continue;
            }
            if ($rol === 'admin' && (int) $f['n'] > 0) {   // con 0 no hace falta revisar permisos
                $ticket = ticket_buscar_para_acceso((int) $f['id_ticket'], $this->db);
                if (!$ticket || !puede_administrar_ticket($usuarioId, (int) ($ticket['id_colegio'] ?? 0), $this->db)) {
                    continue;
                }
            }
            $res[(int) $f['id_ticket']] = (int) $f['n'];
        }
        return $res;
    }
}
