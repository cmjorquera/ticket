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
