<?php
declare(strict_types=1);

require_once __DIR__ . '/../Conexion.php';
require_once __DIR__ . '/../../helpers/tickets.php';

/**
 * Mensajería de un ticket (chat entre quien lo pidió y el técnico).
 * Lógica completa: ticket/logica/LOGICA_MENSAJERIA.txt
 *
 * Tabla: ticket_conversaciones (la misma del sistema antiguo)
 *   id, id_ticket, emisor, receptor, mensaje, adjunto, fecha, hora, leido, eliminado, creado_en
 *   + lado ENUM('usuario','tecnico','admin')  (ticket/sql/01_mensajeria.sql)
 *
 * "lado" = desde qué bandeja se escribió el mensaje:
 *   usuario  → Ticket Nuevo (quien pidió el ticket)
 *   tecnico  → Tickets Asignados (el técnico asignado)
 *   admin    → Ticket Administrador (alguien que administra, sin ser parte)
 * Así funciona aunque la misma persona pida y atienda el ticket (ej.: Cristian
 * crea un ticket que se le asigna a él mismo).
 *
 * leido = 1 cuando la otra parte ya lo vio. Un mensaje está "no leído" para
 * la bandeja V si fue escrito desde otro lado (lado <> V) y leido = 0.
 * Abrir el chat como administrador no marca nada como leído.
 */
final class ConversacionTicket
{
    public const VISTAS = ['usuario', 'tecnico', 'admin'];
    public const LARGO_MAXIMO = 5000;

    private static ?bool $conLado = null;
    private Conexion $db;

    public function __construct(Conexion $db)
    {
        $this->db = $db;
    }

    /** ¿Ya se ejecutó ticket/sql/01_mensajeria.sql? */
    public function conLado(): bool
    {
        if (self::$conLado === null) {
            try {
                self::$conLado = (bool) $this->db->fetchOne(
                    "SELECT 1 FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_conversaciones' AND COLUMN_NAME = 'lado'"
                );
            } catch (Throwable $e) {
                self::$conLado = false;
            }
        }
        return self::$conLado;
    }

    /**
     * Expresión SQL con el lado de un mensaje. Sin la columna (o en mensajes
     * antiguos) se deduce: si lo escribió quien pidió el ticket, es "usuario".
     */
    public function sqlLado(string $m = 'm', string $t = 't'): string
    {
        $deducido = "CASE WHEN {$m}.emisor = {$t}.id_usuario THEN 'usuario' ELSE 'tecnico' END";
        return $this->conLado() ? "COALESCE({$m}.lado, {$deducido})" : $deducido;
    }

    /** Subconsulta: mensajes no leídos del ticket {$t} para la bandeja indicada. */
    public function sqlNoLeidos(string $vista, string $t = 't'): string
    {
        if (!in_array($vista, ['usuario', 'tecnico'], true)) {
            return '0';
        }
        return "(SELECT COUNT(*) FROM ticket_conversaciones m
                  WHERE m.id_ticket = {$t}.id_ticket AND m.eliminado = 0 AND COALESCE(m.leido, 0) = 0
                    AND " . $this->sqlLado('m', $t) . " <> '{$vista}')";
    }

    /** ¿La persona puede usar el chat desde esa bandeja? */
    public function vistaPermitida(int $usuarioId, array $ticket, string $vista): bool
    {
        return match ($vista) {
            'usuario' => (int) ($ticket['id_usuario'] ?? 0) === $usuarioId,
            'tecnico' => (int) ($ticket['id_tecnico_asignado'] ?? 0) === $usuarioId,
            'admin' => puede_administrar_ticket($usuarioId, (int) ($ticket['id_colegio'] ?? 0), $this->db),
            default => false,
        };
    }

    /** Bandeja pedida si corresponde; si no, la primera que le corresponda (usuario > técnico > admin). */
    public function resolverVista(int $usuarioId, array $ticket, string $pedida = ''): ?string
    {
        if ($pedida !== '' && $this->vistaPermitida($usuarioId, $ticket, $pedida)) {
            return $pedida;
        }
        foreach (self::VISTAS as $v) {
            if ($this->vistaPermitida($usuarioId, $ticket, $v)) {
                return $v;
            }
        }
        return null;
    }

    /** Mensajes del ticket, en orden, vistos desde la bandeja indicada. */
    public function mensajes(int $ticketId, string $vista): array
    {
        $lado = $this->sqlLado('m', 't');
        $filas = $this->db->fetchAll(
            "SELECT m.id, m.emisor, m.mensaje, m.adjunto, COALESCE(m.leido, 0) AS leido,
                    CONCAT(m.fecha, ' ', m.hora) AS fecha, {$lado} AS lado,
                    TRIM(CONCAT_WS(' ', u.nombre, u.apellido_paterno)) AS autor
               FROM ticket_conversaciones m
               JOIN tickets t ON t.id_ticket = m.id_ticket
          LEFT JOIN usuarios u ON u.id = m.emisor
              WHERE m.id_ticket = ? AND m.eliminado = 0
           ORDER BY m.id ASC",
            [$ticketId]
        );
        return array_map(static function (array $f) use ($vista): array {
            $adjunto = trim((string) ($f['adjunto'] ?? ''));
            $texto = (string) $f['mensaje'];
            return [
                'id' => (int) $f['id'],
                'autor' => (string) ($f['autor'] ?: 'Usuario'),
                'lado' => (string) $f['lado'],
                'propio' => $f['lado'] === $vista,
                'visto' => (int) $f['leido'] === 1,
                'fecha' => (string) $f['fecha'],
                'texto' => $adjunto !== '' ? '' : $texto,
                'adjunto' => $adjunto === '' ? null : [
                    'nombre' => $texto !== '' ? $texto : basename($adjunto),
                    'extension' => strtolower(pathinfo($adjunto, PATHINFO_EXTENSION)),
                ],
            ];
        }, $filas);
    }

    /** Guarda un mensaje (o un adjunto) y devuelve su id. */
    public function enviar(array $ticket, int $emisor, string $vista, string $texto, ?string $adjunto = null): int
    {
        if (!in_array($vista, self::VISTAS, true)) {
            throw new InvalidArgumentException('Bandeja no válida.');
        }
        // Destinatario: la otra parte del ticket (0 si todavía no hay técnico)
        $receptor = $vista === 'usuario'
            ? (int) ($ticket['id_tecnico_asignado'] ?? 0)
            : (int) ($ticket['id_usuario'] ?? 0);
        $campos = 'id_ticket, emisor, receptor, mensaje, adjunto, fecha, hora, leido, eliminado, creado_en';
        $valores = '?, ?, ?, ?, ?, CURDATE(), CURTIME(), 0, 0, NOW()';
        $params = [(int) $ticket['id_ticket'], $emisor, $receptor, $texto, $adjunto];
        if ($this->conLado()) {
            $campos .= ', lado';
            $valores .= ', ?';
            $params[] = $vista;
        }
        $this->db->execute("INSERT INTO ticket_conversaciones ({$campos}) VALUES ({$valores})", $params);
        return (int) $this->db->lastInsertId();
    }

    /** Marca como leído lo que la otra parte escribió. Devuelve cuántos cambió. */
    public function marcarLeidos(int $ticketId, string $vista): int
    {
        if (!in_array($vista, ['usuario', 'tecnico'], true)) {
            return 0;   // el administrador solo mira
        }
        $lado = $this->sqlLado('m', 't');
        return $this->db->execute(
            "UPDATE ticket_conversaciones m JOIN tickets t ON t.id_ticket = m.id_ticket
                SET m.leido = 1
              WHERE m.id_ticket = ? AND COALESCE(m.leido, 0) = 0 AND m.eliminado = 0 AND {$lado} <> ?",
            [$ticketId, $vista]
        );
    }

    /**
     * No leídos por ticket para la bandeja (solo tickets donde la persona es
     * esa parte). @param int[] $ids  @return array<int,int>  id_ticket => cantidad
     */
    public function noLeidos(int $usuarioId, string $vista, array $ids): array
    {
        $ids = array_slice(array_values(array_unique(array_filter(array_map('intval', $ids), static fn ($v) => $v > 0))), 0, 200);
        if (!$ids || !in_array($vista, ['usuario', 'tecnico'], true)) {
            return [];
        }
        $campo = $vista === 'usuario' ? 't.id_usuario' : 't.id_tecnico';
        $marcas = implode(',', array_fill(0, count($ids), '?'));
        $filas = $this->db->fetchAll(
            "SELECT t.id_ticket, " . $this->sqlNoLeidos($vista, 't') . " AS n
               FROM tickets t
              WHERE t.id_ticket IN ({$marcas}) AND {$campo} = ? AND t.estado = 1",
            array_merge($ids, [$usuarioId])
        );
        $res = [];
        foreach ($filas as $f) {
            $res[(int) $f['id_ticket']] = (int) $f['n'];
        }
        return $res;
    }

    /** Ruta guardada de un adjunto del chat (para descargarlo). */
    public function rutaAdjunto(int $idMensaje): ?array
    {
        $f = $this->db->fetchOne(
            'SELECT id_ticket, adjunto, mensaje FROM ticket_conversaciones WHERE id = ? AND eliminado = 0 AND adjunto IS NOT NULL AND adjunto <> ""',
            [$idMensaje]
        );
        return $f ? ['id_ticket' => (int) $f['id_ticket'], 'ruta' => (string) $f['adjunto'], 'nombre' => (string) $f['mensaje']] : null;
    }
}
