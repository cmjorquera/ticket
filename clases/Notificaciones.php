<?php
declare(strict_types=1);

require_once __DIR__ . '/Conexion.php';

/**
 * Notificaciones de la campanita (barra superior).
 * Tabla: notificaciones (ticket/sql/03_notificaciones.sql). Si la tabla aún no
 * existe, todo funciona igual y simplemente no se guardan avisos.
 * Lógica: ticket/logica/LOGICA_NOTIFICACIONES.txt
 */
final class Notificaciones
{
    public const LIMITE_LISTA = 20;
    private static ?bool $instalada = null;
    private Conexion $db;

    public function __construct(Conexion $db)
    {
        $this->db = $db;
    }

    public function instalada(): bool
    {
        if (self::$instalada === null) {
            try {
                self::$instalada = (bool) $this->db->fetchOne(
                    "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notificaciones'"
                );
            } catch (Throwable $e) {
                self::$instalada = false;
            }
        }
        return self::$instalada;
    }

    /**
     * Crea un aviso. Nunca interrumpe la acción principal (errores al log).
     * Con $agrupar = true y un aviso igual SIN LEER para ese ticket, en vez de
     * crear otro suma 1 a "cantidad" (ej.: "3 mensajes nuevos").
     */
    public function crear(int $destinatario, string $tipo, string $titulo, string $texto = '', string $url = '',
                          ?int $idTicket = null, ?int $idActor = null, bool $agrupar = false): void
    {
        if ($destinatario <= 0 || !$this->instalada()) {
            return;
        }
        try {
            if ($agrupar && $idTicket) {
                $previa = $this->db->fetchOne(
                    'SELECT id, cantidad FROM notificaciones
                      WHERE id_usuario = ? AND id_ticket = ? AND tipo = ? AND leida = 0 ORDER BY id DESC LIMIT 1',
                    [$destinatario, $idTicket, $tipo]
                );
                if ($previa) {
                    $this->db->execute(
                        'UPDATE notificaciones SET cantidad = cantidad + 1, titulo = ?, texto = ?, id_actor = ?, actualizada = NOW() WHERE id = ?',
                        [mb_substr($titulo, 0, 150), mb_substr($texto, 0, 255), $idActor, (int) $previa['id']]
                    );
                    return;
                }
            }
            $this->db->execute(
                'INSERT INTO notificaciones (id_usuario, tipo, titulo, texto, url, id_ticket, id_actor, cantidad, leida, creada, actualizada)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, NOW(), NOW())',
                [$destinatario, $tipo, mb_substr($titulo, 0, 150), mb_substr($texto, 0, 255), mb_substr($url, 0, 255), $idTicket, $idActor]
            );
        } catch (Throwable $e) {
            error_log('[notificaciones] ' . $e->getMessage());
        }
    }

    public function contar(int $usuarioId): int
    {
        if (!$this->instalada()) {
            return 0;
        }
        return (int) ($this->db->fetchOne('SELECT COUNT(*) AS n FROM notificaciones WHERE id_usuario = ? AND leida = 0', [$usuarioId])['n'] ?? 0);
    }

    public function listar(int $usuarioId, int $limite = self::LIMITE_LISTA): array
    {
        if (!$this->instalada()) {
            return [];
        }
        $limite = max(1, min(50, $limite));
        return array_map(static fn (array $f): array => [
            'id' => (int) $f['id'],
            'tipo' => (string) $f['tipo'],
            'titulo' => (string) $f['titulo'],
            'texto' => (string) $f['texto'],
            'url' => (string) $f['url'],
            'cantidad' => (int) $f['cantidad'],
            'leida' => (int) $f['leida'] === 1,
            'fecha' => (string) $f['actualizada'],
        ], $this->db->fetchAll(
            "SELECT id, tipo, titulo, texto, url, cantidad, leida, actualizada
               FROM notificaciones WHERE id_usuario = ?
           ORDER BY leida ASC, actualizada DESC, id DESC LIMIT {$limite}",
            [$usuarioId]
        ));
    }

    public function leer(int $usuarioId, int $id): void
    {
        if ($this->instalada()) {
            $this->db->execute('UPDATE notificaciones SET leida = 1 WHERE id = ? AND id_usuario = ?', [$id, $usuarioId]);
        }
    }

    public function leerTodas(int $usuarioId): void
    {
        if ($this->instalada()) {
            $this->db->execute('UPDATE notificaciones SET leida = 1 WHERE id_usuario = ? AND leida = 0', [$usuarioId]);
        }
    }

    /**
     * Números para el menú lateral y el sobre de la barra superior:
     *   ticket_usuario  mensajes del chat sin leer en mis tickets (Ticket Nuevo)
     *   ticket_tecnico  mensajes del chat + notas de administración sin leer en los
     *                   tickets que atiendo (Ticket Asignados)
     *   mensajes        mensajes internos sin leer (módulo Mensajes)
     * Cada cálculo es independiente: si una tabla no existe, ese número queda en 0.
     */
    public function contadores(int $usuarioId): array
    {
        $out = ['ticket_usuario' => 0, 'ticket_tecnico' => 0, 'mensajes' => 0];
        $uno = function (string $sql, array $p): int {
            try {
                return (int) ($this->db->fetchOne($sql, $p)['n'] ?? 0);
            } catch (Throwable $e) {
                return 0;
            }
        };
        if (is_file(__DIR__ . '/Tickets/ConversacionTicket.php')) {
            require_once __DIR__ . '/Tickets/ConversacionTicket.php';
            require_once __DIR__ . '/Tickets/BitacoraTicket.php';
            $lado = (new ConversacionTicket($this->db))->sqlLado('m', 't');
            $base = "SELECT COUNT(*) AS n FROM ticket_conversaciones m JOIN tickets t ON t.id_ticket = m.id_ticket
                      WHERE t.estado = 1 AND m.eliminado = 0 AND COALESCE(m.leido, 0) = 0";
            $out['ticket_usuario'] = $uno("{$base} AND t.id_usuario = ? AND {$lado} <> 'usuario'", [$usuarioId]);
            $out['ticket_tecnico'] = $uno("{$base} AND t.id_tecnico = ? AND {$lado} <> 'tecnico'", [$usuarioId]);
            $bit = new BitacoraTicket($this->db);
            if ($bit->disponible()) {
                $out['ticket_tecnico'] += $uno(
                    "SELECT COUNT(*) AS n FROM avance_tecnicos b JOIN tickets t ON t.id_ticket = b.id_ticket
                      WHERE t.estado = 1 AND t.id_tecnico = ? AND b.eliminado = 0 AND b.leido = 0 AND b.lado = 'admin' AND COALESCE(b.id_usuario, 0) <> ?",
                    [$usuarioId, $usuarioId]
                );
            }
        }
        $out['mensajes'] = $uno(
            "SELECT COUNT(*) AS n FROM mensajes_chat m
          LEFT JOIN mensajes_chat_carpeta c ON c.id_conversacion = m.id_conversacion AND c.id_usuario = ?
              WHERE m.para = ? AND m.leido = 0 AND m.eliminado = 0 AND (c.carpeta IS NULL OR c.carpeta <> 'eliminado')",
            [$usuarioId, $usuarioId]
        );
        return $out;
    }

    /** Marca como leídos los avisos de un ticket (al abrir su detalle o su chat). */
    public function leerPorTicket(int $usuarioId, int $idTicket, array $tipos = []): void
    {
        if (!$this->instalada() || $idTicket <= 0) {
            return;
        }
        try {
            $sql = 'UPDATE notificaciones SET leida = 1 WHERE id_usuario = ? AND id_ticket = ? AND leida = 0';
            $p = [$usuarioId, $idTicket];
            if ($tipos) {
                $sql .= ' AND tipo IN (' . implode(',', array_fill(0, count($tipos), '?')) . ')';
                $p = array_merge($p, array_values($tipos));
            }
            $this->db->execute($sql, $p);
        } catch (Throwable $e) {
            error_log('[notificaciones] ' . $e->getMessage());
        }
    }
}
