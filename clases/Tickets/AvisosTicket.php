<?php
declare(strict_types=1);

require_once __DIR__ . '/../Conexion.php';
require_once __DIR__ . '/../Notificaciones.php';
require_once __DIR__ . '/../../helpers/tickets.php';

/**
 * Qué avisos de la campanita genera cada evento de un ticket y a quién.
 * Lógica: ticket/logica/LOGICA_NOTIFICACIONES.txt
 *
 *   evento                    avisa a
 *   creado                    técnico asignado; solicitante si lo creó otra persona
 *   asignado (admin)          técnico nuevo y solicitante
 *   comenzado (técnico)       solicitante: "en proceso · listo el dd/mm"
 *   cambio de estado          solicitante y técnico (al que no lo hizo)
 *   mensaje del chat          la otra parte (agrupado: "3 mensajes nuevos")
 *   nota de administración    técnico (agrupado)
 * Nunca se avisa a quien hizo la acción, salvo en el chat cuando la misma
 * persona es solicitante y técnico y escribe desde la otra bandeja.
 */
final class AvisosTicket
{
    private Conexion $db;
    private Notificaciones $n;

    public function __construct(Conexion $db)
    {
        $this->db = $db;
        $this->n = new Notificaciones($db);
    }

    private function ticket(int $id): ?array
    {
        $t = $this->db->fetchOne(
            "SELECT t.id_ticket, t.id_usuario, t.id_tecnico, t.asunto,
                    TRIM(CONCAT_WS(' ', s.nombre, s.apellido_paterno)) AS solicitante,
                    TRIM(CONCAT_WS(' ', tc.nombre, tc.apellido_paterno)) AS tecnico
               FROM tickets t
          LEFT JOIN usuarios s ON s.id = t.id_usuario
          LEFT JOIN usuarios tc ON tc.id = t.id_tecnico
              WHERE t.id_ticket = ?",
            [$id]
        );
        return $t ?: null;
    }

    private function nombre(int $id): string
    {
        $u = $this->db->fetchOne("SELECT TRIM(CONCAT_WS(' ', nombre, apellido_paterno)) AS n FROM usuarios WHERE id = ?", [$id]);
        return (string) ($u['n'] ?? 'Alguien');
    }

    private static function url(int $id, string $vista, string $ancla = ''): string
    {
        return 'ticket/ticket_detalle.php?id=' . $id . '&vista=' . $vista . ($ancla !== '' ? '#' . $ancla : '');
    }

    /** Ejecuta sin interrumpir nunca la acción principal. */
    private function seguro(callable $f): void
    {
        try {
            if ($this->n->instalada()) {
                $f();
            }
        } catch (Throwable $e) {
            error_log('[avisos_ticket] ' . $e->getMessage());
        }
    }

    public function creado(int $idTicket, int $actor): void
    {
        $this->seguro(function () use ($idTicket, $actor) {
            $t = $this->ticket($idTicket);
            if (!$t) {
                return;
            }
            $tec = (int) $t['id_tecnico'];
            if ($tec && $tec !== $actor) {
                $this->n->crear($tec, 'ticket_creado', "Nuevo ticket asignado #{$idTicket}",
                    $t['asunto'] . ' · de ' . $t['solicitante'], self::url($idTicket, 'tecnico'), $idTicket, $actor);
            }
            if ((int) $t['id_usuario'] !== $actor) {
                $this->n->crear((int) $t['id_usuario'], 'ticket_creado', "Se creó el ticket #{$idTicket} a tu nombre",
                    $t['asunto'] . ' · por ' . $this->nombre($actor), self::url($idTicket, 'usuario'), $idTicket, $actor);
            }
        });
    }

    public function asignado(int $idTicket, int $actor): void
    {
        $this->seguro(function () use ($idTicket, $actor) {
            $t = $this->ticket($idTicket);
            if (!$t || !(int) $t['id_tecnico']) {
                return;
            }
            $tec = (int) $t['id_tecnico'];
            if ($tec !== $actor) {
                $this->n->crear($tec, 'ticket_asignado', "Te asignaron el ticket #{$idTicket}",
                    $t['asunto'] . ' · de ' . $t['solicitante'], self::url($idTicket, 'tecnico'), $idTicket, $actor);
            }
            if ((int) $t['id_usuario'] !== $actor) {
                $this->n->crear((int) $t['id_usuario'], 'ticket_asignado', "Tu ticket #{$idTicket} fue asignado",
                    'Técnico: ' . $t['tecnico'], self::url($idTicket, 'usuario'), $idTicket, $actor);
            }
        });
    }

    public function comenzado(int $idTicket, int $actor, string $prioridad, string $fecha): void
    {
        $this->seguro(function () use ($idTicket, $actor, $prioridad, $fecha) {
            $t = $this->ticket($idTicket);
            if ($t && (int) $t['id_usuario'] !== $actor) {
                $this->n->crear((int) $t['id_usuario'], 'ticket_comenzado', "Tu ticket #{$idTicket} está en proceso",
                    $t['tecnico'] . ' lo comenzó · listo el ' . $fecha . ' (prioridad ' . mb_strtolower($prioridad) . ')',
                    self::url($idTicket, 'usuario'), $idTicket, $actor);
            }
        });
    }

    public function estado(int $idTicket, int $actor, int $anterior, int $nuevo): void
    {
        if ($anterior === $nuevo) {
            return;
        }
        $this->seguro(function () use ($idTicket, $actor, $nuevo) {
            $t = $this->ticket($idTicket);
            if (!$t) {
                return;
            }
            $nombreEstado = ticket_estados_legacy()[$nuevo]['nombre'] ?? 'otro estado';
            if ((int) $t['id_usuario'] !== $actor) {
                $this->n->crear((int) $t['id_usuario'], 'ticket_estado', "Tu ticket #{$idTicket} cambió a {$nombreEstado}",
                    $nuevo === 5 ? $t['asunto'] . ' · ¡califica la atención!' : $t['asunto'],
                    self::url($idTicket, 'usuario', $nuevo === 5 ? 'calificacion' : ''), $idTicket, $actor);
            }
            $tec = (int) $t['id_tecnico'];
            if ($tec && $tec !== $actor) {
                $this->n->crear($tec, 'ticket_estado', "El ticket #{$idTicket} cambió a {$nombreEstado}",
                    $t['asunto'] . ' · por ' . $this->nombre($actor), self::url($idTicket, 'tecnico'), $idTicket, $actor);
            }
        });
    }

    /** Mensaje del chat ($vista = bandeja desde la que se escribió). Agrupado por ticket. */
    public function mensaje(int $idTicket, int $actor, string $vista, string $texto): void
    {
        $this->seguro(function () use ($idTicket, $actor, $vista, $texto) {
            $t = $this->ticket($idTicket);
            if (!$t) {
                return;
            }
            $destinos = match ($vista) {
                'usuario' => [[(int) $t['id_tecnico'], 'tecnico']],
                'tecnico' => [[(int) $t['id_usuario'], 'usuario']],
                default => [[(int) $t['id_usuario'], 'usuario'], [(int) $t['id_tecnico'], 'tecnico']],
            };
            $extracto = $this->nombre($actor) . ': ' . (mb_strlen($texto) > 90 ? mb_substr($texto, 0, 90) . '…' : $texto);
            foreach ($destinos as [$dest, $vistaDest]) {
                if ($dest > 0) {   // el chat avisa aunque sea la misma persona en la otra bandeja
                    $this->n->crear($dest, 'ticket_mensaje', "Nuevo mensaje en el ticket #{$idTicket}", $extracto,
                        self::url($idTicket, $vistaDest, 'conversacion'), $idTicket, $actor, true);
                }
            }
        });
    }

    /** Nota de administración en la bitácora → técnico. Agrupado por ticket. */
    public function notaAdministracion(int $idTicket, int $actor, string $texto): void
    {
        $this->seguro(function () use ($idTicket, $actor, $texto) {
            $t = $this->ticket($idTicket);
            $tec = (int) ($t['id_tecnico'] ?? 0);
            if ($t && $tec && $tec !== $actor) {
                $this->n->crear($tec, 'ticket_bitacora', "Nota de administración en el ticket #{$idTicket}",
                    $this->nombre($actor) . ': ' . (mb_strlen($texto) > 90 ? mb_substr($texto, 0, 90) . '…' : $texto),
                    self::url($idTicket, 'tecnico', 'bitacora'), $idTicket, $actor, true);
            }
        });
    }
}
