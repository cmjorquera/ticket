<?php
declare(strict_types=1);

/**
 * Fusión de tickets: operaciones transaccionales.
 * Requiere ticket/sql/04_fusion_tickets.sql ejecutado manualmente.
 */
final class FusionTicket
{
    public static function instalada(Conexion $db): bool
    {
        return (bool) $db->fetchOne(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_fusiones' LIMIT 1"
        );
    }

    public static function esSecundario(Conexion $db, int $id): bool
    {
        return (bool) $db->fetchOne('SELECT id_fusion FROM ticket_fusiones WHERE id_ticket_secundario = ? LIMIT 1', [$id]);
    }

    public static function tieneSecundarios(Conexion $db, int $id): bool
    {
        return (bool) $db->fetchOne('SELECT id_fusion FROM ticket_fusiones WHERE id_ticket_principal = ? LIMIT 1', [$id]);
    }

    /**
     * Debe invocarse DENTRO de la transacción que termina el ticket principal.
     * Bloquea relaciones y tickets; evita cierres parciales y reintentos duplicados.
     * Devuelve los ID cerrados para notificar tras el commit.
     */
    public static function terminarSecundarios(Conexion $db, int $principal, int $actor): array
    {
        if (!self::instalada($db)) {
            return [];
        }
        $relaciones = $db->fetchAll(
            'SELECT id_fusion, id_ticket_secundario FROM ticket_fusiones WHERE id_ticket_principal = ? ORDER BY id_ticket_secundario FOR UPDATE',
            [$principal]
        );
        $cerrados = [];
        foreach ($relaciones as $relacion) {
            $id = (int) $relacion['id_ticket_secundario'];
            $ticket = $db->fetchOne('SELECT id_estado, estado FROM tickets WHERE id_ticket = ? FOR UPDATE', [$id]);
            if (!$ticket || (int) $ticket['estado'] !== 1 || (int) $ticket['id_estado'] === 5) {
                continue;
            }
            $db->execute('UPDATE tickets SET id_estado = 5 WHERE id_ticket = ? AND estado = 1', [$id]);
            $db->execute(
                'UPDATE proceso_tickets SET fecha_termino_ticket = COALESCE(fecha_termino_ticket, CURDATE()), hora_termino_ticket = COALESCE(hora_termino_ticket, CURTIME()) WHERE id_ticket = ?',
                [$id]
            );
            $db->execute('UPDATE ticket_fusiones SET fecha_cierre_automatico = NOW() WHERE id_fusion = ?', [(int) $relacion['id_fusion']]);
            $db->execute(
                'INSERT INTO ticket_fusion_eventos (id_fusion, id_ticket, id_usuario_actor, tipo_evento, detalle) VALUES (?, ?, ?, ?, ?)',
                [(int) $relacion['id_fusion'], $id, $actor, 'cierre_automatico', 'Cierre por término del ticket principal #' . $principal]
            );
            $cerrados[] = $id;
        }
        return $cerrados;
    }

    public static function fusionar(Conexion $db, int $principal, int $secundario, int $actor, string $motivo): int
    {
        if (!self::instalada($db)) {
            throw new RuntimeException('Debes ejecutar ticket/sql/04_fusion_tickets.sql en QA antes de fusionar.');
        }
        if ($principal <= 0 || $secundario <= 0 || $principal === $secundario) {
            throw new DomainException('Selecciona dos tickets distintos.');
        }
        if (mb_strlen($motivo) < 5 || mb_strlen($motivo) > 500) {
            throw new DomainException('Indica un motivo de entre 5 y 500 caracteres.');
        }
        $pdo = $db->getPDO();
        $pdo->beginTransaction();
        try {
            // Orden consistente de bloqueo para reducir deadlocks.
            $ids = [$principal, $secundario];
            sort($ids);
            $rows = $db->fetchAll(
                'SELECT id_ticket, id_estado, estado, id_usuario FROM tickets WHERE id_ticket IN (?, ?) ORDER BY id_ticket FOR UPDATE',
                $ids
            );
            if (count($rows) !== 2) {
                throw new DomainException('Uno de los tickets no existe.');
            }
            foreach ($rows as $row) {
                if ((int) $row['estado'] !== 1 || in_array((int) $row['id_estado'], [5, 6], true)) {
                    throw new DomainException('Solo se pueden fusionar tickets activos y no terminados.');
                }
            }
            if (self::esSecundario($db, $principal) || self::esSecundario($db, $secundario) || self::tieneSecundarios($db, $secundario)) {
                throw new DomainException('No se permite fusionar tickets que ya son secundarios o principales de otra fusión.');
            }
            $db->execute(
                'INSERT INTO ticket_fusiones (id_ticket_principal, id_ticket_secundario, id_usuario_fusion, motivo) VALUES (?, ?, ?, ?)',
                [$principal, $secundario, $actor, $motivo]
            );
            $fusionId = (int) $db->lastInsertId();
            $db->execute(
                'INSERT INTO ticket_fusion_eventos (id_fusion, id_ticket, id_usuario_actor, tipo_evento, detalle) VALUES (?, ?, ?, ?, ?)',
                [$fusionId, $secundario, $actor, 'fusion', $motivo]
            );
            $pdo->commit();
            return $fusionId;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
}
