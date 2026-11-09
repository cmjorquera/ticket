<?php
declare(strict_types=1);

require_once __DIR__ . '/Eventos.php';
require_once __DIR__ . '/../../clases/Correo.php';
require_once __DIR__ . '/../../clases/Notificaciones.php';

/**
 * Correos del módulo Eventos (como Google Calendar):
 *   creado      → invitación con .ics (METHOD:REQUEST): Gmail/Outlook muestran
 *                 "Agregar al calendario" y Sí / No / Quizás. También lleva
 *                 botones propios que responden en el sistema (eventos/responder.php).
 *   actualizado → misma invitación con SEQUENCE mayor: el calendario del invitado
 *                 se actualiza solo; el correo dice qué cambió.
 *   cancelado   → .ics METHOD:CANCEL: el evento se borra de su calendario.
 * Además avisa en la campanita a los invitados que tienen cuenta.
 * Lógica: eventos/logica/LOGICA.txt
 */
final class CorreoEvento
{
    private Conexion $db;
    private Eventos $ev;

    public function __construct(Conexion $db)
    {
        $this->db = $db;
        $this->ev = new Eventos($db);
    }

    public static function baseUrl(): string
    {
        if (!defined('BASE_URL') && is_file(dirname(__DIR__, 2) . '/config.php')) {
            require_once dirname(__DIR__, 2) . '/config.php';
        }
        if (defined('BASE_URL')) {
            return rtrim((string) BASE_URL, '/') . '/';
        }
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        return ($https ? 'https' : 'http') . '://' . preg_replace('/[^\w.\-:]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost')) . '/';
    }

    private function evento(int $id): ?array
    {
        $e = $this->db->fetchOne(
            "SELECT e.*, c.nom_colegio, TRIM(CONCAT_WS(' ', r.nombre, r.apellido_paterno)) AS responsable, r.email AS responsable_email
               FROM eventos e LEFT JOIN colegio c ON c.id_colegio = e.id_colegio LEFT JOIN usuarios r ON r.id = e.responsable_id
              WHERE e.id = ?",
            [$id]
        );
        return $e ?: null;
    }

    /**
     * Envía los correos y avisos de un evento.
     * @param string $tipo creado | actualizado | cancelado
     * @param array|null $soloA lista de destinatarios (para invitados recién agregados o quitados)
     * @return array{enviados:int, total:int, motivo:string}
     */
    public function notificar(string $tipo, int $id, int $actor, array $cambios = [], ?array $soloA = null): array
    {
        $e = $this->evento($id);
        if (!$e) {
            return ['enviados' => 0, 'total' => 0, 'motivo' => ''];
        }
        $destinos = array_values(array_filter($soloA ?? $this->ev->destinatarios($id), static fn ($d) => (int) ($d['id_usuario'] ?? 0) !== $actor));
        $this->campanita($tipo, $e, $destinos, $actor);
        $enviados = 0;
        $motivo = '';
        foreach ($destinos as $d) {
            $r = Correo::enviar(
                [[$d['email'], (string) ($d['nombre'] ?? '')]],
                $this->asunto($tipo, $e),
                $this->html($tipo, $e, $d, $cambios),
                '',
                [],
                $this->ics($e, $tipo === 'cancelado' ? 'CANCEL' : 'REQUEST', $d)
            );
            if ($r['enviado']) {
                $enviados++;
            } else {
                $motivo = $r['motivo'];
                if (str_contains($motivo, 'no está configurado')) {
                    break;   // sin SMTP no tiene sentido seguir intentando
                }
            }
        }
        if ($enviados > 0) {
            $this->ev->marcarCorreo($id);
        }
        return ['enviados' => $enviados, 'total' => count($destinos), 'motivo' => $motivo];
    }

    private function campanita(string $tipo, array $e, array $destinos, int $actor): void
    {
        $n = new Notificaciones($this->db);
        $cuando = self::cuando($e);
        foreach ($destinos as $d) {
            if (empty($d['id_usuario'])) {
                continue;
            }
            [$tipoN, $titulo] = match ($tipo) {
                'creado' => ['evento_invitacion', 'Te invitaron: ' . $e['titulo']],
                'cancelado' => ['evento_cancelado', 'Evento cancelado: ' . $e['titulo']],
                default => ['evento_actualizado', 'Evento modificado: ' . $e['titulo']],
            };
            $n->crear((int) $d['id_usuario'], $tipoN, $titulo, $cuando . ($e['ubicacion'] ? ' · ' . $e['ubicacion'] : ''),
                'eventos/index.php?evento=' . (int) $e['id'], null, $actor);
        }
    }

    private function asunto(string $tipo, array $e): string
    {
        return match ($tipo) {
            'creado' => 'Invitación: ' . $e['titulo'] . ' · ' . self::cuando($e),
            'cancelado' => 'Cancelado: ' . $e['titulo'] . ' · ' . self::cuando($e),
            default => 'Actualizado: ' . $e['titulo'] . ' · ' . self::cuando($e),
        };
    }

    /** "mar 10 nov 2026 · 15:00 – 16:30" */
    public static function cuando(array $e): string
    {
        $dias = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];
        $meses = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sept', 'oct', 'nov', 'dic'];
        $f = static function (string $d) use ($dias, $meses): string {
            $t = strtotime($d);
            return $dias[(int) date('w', $t)] . ' ' . date('j', $t) . ' ' . $meses[(int) date('n', $t)] . ' ' . date('Y', $t);
        };
        $ini = $f((string) $e['fecha_inicio']);
        $fin = (string) ($e['fecha_fin'] ?: $e['fecha_inicio']);
        if ((int) ($e['todo_el_dia'] ?? 0) === 1 || !$e['hora_inicio']) {
            return $ini . ($fin !== $e['fecha_inicio'] ? ' – ' . $f($fin) : '') . ' · todo el día';
        }
        $hi = substr((string) $e['hora_inicio'], 0, 5);
        $hf = substr((string) $e['hora_fin'], 0, 5);
        return $fin !== $e['fecha_inicio'] ? "{$ini} {$hi} – " . $f($fin) . " {$hf}" : "{$ini} · {$hi} – {$hf}";
    }

    /** Invitación iCalendar (RFC 5545). Horas en UTC a partir de America/Santiago. */
    public function ics(array $e, string $metodo, array $para): string
    {
        $zona = new DateTimeZone('America/Santiago');
        $utc = new DateTimeZone('UTC');
        $esc = static fn (string $t): string => str_replace(["\\", ';', ',', "\r\n", "\n"], ["\\\\", '\\;', '\\,', '\\n', '\\n'], $t);
        $todo = (int) ($e['todo_el_dia'] ?? 0) === 1 || !$e['hora_inicio'];
        $fin = (string) ($e['fecha_fin'] ?: $e['fecha_inicio']);
        $host = parse_url(self::baseUrl(), PHP_URL_HOST) ?: 'seduc.cl';
        $remitente = (string) ((ConfigPrivada::opcional('smtp') ?? [])['remitente'] ?? 'seduc.informa@seduc.cl');
        $l = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//SEDUC//Panel Central Eventos//ES', 'CALSCALE:GREGORIAN', 'METHOD:' . $metodo,
            'BEGIN:VEVENT',
            'UID:evento-' . (int) $e['id'] . '@' . $host,
            'SEQUENCE:' . (int) ($e['secuencia'] ?? 0),
            'DTSTAMP:' . gmdate('Ymd\THis\Z')];
        if ($todo) {
            $l[] = 'DTSTART;VALUE=DATE:' . date('Ymd', strtotime((string) $e['fecha_inicio']));
            $l[] = 'DTEND;VALUE=DATE:' . date('Ymd', strtotime($fin . ' +1 day'));
        } else {
            $l[] = 'DTSTART:' . (new DateTimeImmutable($e['fecha_inicio'] . ' ' . $e['hora_inicio'], $zona))->setTimezone($utc)->format('Ymd\THis\Z');
            $l[] = 'DTEND:' . (new DateTimeImmutable($fin . ' ' . ($e['hora_fin'] ?: $e['hora_inicio']), $zona))->setTimezone($utc)->format('Ymd\THis\Z');
        }
        $l[] = 'SUMMARY:' . $esc((string) $e['titulo']);
        if (trim((string) $e['descripcion']) !== '') {
            $l[] = 'DESCRIPTION:' . $esc((string) $e['descripcion']);
        }
        if (trim((string) $e['ubicacion']) !== '') {
            $l[] = 'LOCATION:' . $esc((string) $e['ubicacion']);
        }
        $l[] = 'URL:' . self::baseUrl() . 'eventos/index.php?evento=' . (int) $e['id'];
        $l[] = 'ORGANIZER;CN=' . $esc((string) ($e['responsable'] ?: 'SEDUC')) . ':mailto:' . $remitente;
        $l[] = 'ATTENDEE;CN=' . $esc((string) ($para['nombre'] ?: $para['email'])) . ';ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=TRUE:mailto:' . $para['email'];
        $l[] = 'STATUS:' . ($metodo === 'CANCEL' ? 'CANCELLED' : 'CONFIRMED');
        if ($metodo !== 'CANCEL') {
            array_push($l, 'BEGIN:VALARM', 'TRIGGER:-PT30M', 'ACTION:DISPLAY', 'DESCRIPTION:' . $esc((string) $e['titulo']), 'END:VALARM');
        }
        array_push($l, 'END:VEVENT', 'END:VCALENDAR');
        // líneas de máximo 75 octetos (plegado RFC 5545)
        $out = [];
        foreach ($l as $linea) {
            while (strlen($linea) > 75) {
                $corte = 75;
                while ($corte > 0 && (ord($linea[$corte]) & 0xC0) === 0x80) {
                    $corte--;   // no cortar un carácter UTF-8 por la mitad
                }
                $out[] = substr($linea, 0, $corte);
                $linea = ' ' . substr($linea, $corte);
            }
            $out[] = $linea;
        }
        return implode("\r\n", $out) . "\r\n";
    }

    private function html(string $tipo, array $e, array $para, array $cambios): string
    {
        $h = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $base = self::baseUrl();
        [$etiqueta, $color, $intro] = match ($tipo) {
            'creado' => ['Invitación', '#16365c', 'Te invitaron a este evento.'],
            'cancelado' => ['Evento cancelado', '#b42318', 'Este evento fue cancelado. Ya no es necesario asistir.'],
            default => ['Evento actualizado', '#8a6200', $cambios ? 'Se modificó: <strong>' . $h(implode(', ', $cambios)) . '</strong>.' : 'El evento fue actualizado.'],
        };
        if (!empty($para['responsable']) && $tipo === 'creado') {
            $intro = 'Quedaste como <strong>responsable</strong> de este evento.';
        }
        $fila = static fn (string $l, string $v): string => $v === '' ? '' : '<tr><td style="padding:5px 0;color:#6b7280;font-size:13px;width:120px;vertical-align:top">'
            . $l . '</td><td style="padding:5px 0;font-size:14px;color:#111827">' . $v . '</td></tr>';
        $logistica = array_filter([
            (int) $e['con_audio'] ? 'Audio' : '', (int) $e['solo_presentacion'] ? 'Presentación (proyector)' : '',
            (int) $e['musica_ambiental'] ? 'Música ambiental' : '', (int) $e['cantidad_personas'] ? (int) $e['cantidad_personas'] . ' personas' : '',
        ]);
        $tachado = $tipo === 'cancelado' ? 'text-decoration:line-through;' : '';
        $botones = '';
        if ($tipo !== 'cancelado' && !empty($para['token'])) {
            $b = static fn (string $r, string $txt, string $bg): string => '<a href="' . $h($base . 'eventos/responder.php?t=' . $para['token'] . '&r=' . $r)
                . '" style="display:inline-block;margin:0 6px 6px 0;padding:10px 16px;border-radius:8px;background:' . $bg . ';color:#fff;text-decoration:none;font-weight:700;font-size:14px">' . $txt . '</a>';
            $botones = '<p style="margin:18px 0 4px;font-size:13px;color:#374151">¿Asistirás?</p>'
                . $b('si', 'Sí', '#2f7d32') . $b('quizas', 'Quizás', '#8a6200') . $b('no', 'No', '#6b7280');
        }
        return '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden">'
            . '<div style="background:' . $color . ';color:#fff;padding:16px 22px;border-bottom:4px solid #e8a33d">'
            . '<div style="font-size:12px;opacity:.85">SEDUC · ' . $etiqueta . '</div>'
            . '<div style="font-size:20px;font-weight:700;' . $tachado . '">' . $h($e['titulo']) . '</div></div>'
            . '<div style="padding:18px 22px"><p style="margin:0 0 12px;font-size:14px;color:#374151">' . $intro . '</p>'
            . '<table style="width:100%;border-collapse:collapse">'
            . $fila('Cuándo', '<strong>' . $h(self::cuando($e)) . '</strong>')
            . $fila('Dónde', $h($e['ubicacion']))
            . $fila('Colegio', $h($e['nom_colegio'] ?? ''))
            . $fila('Responsable', $h($e['responsable']))
            . $fila('Tipo', $h(Eventos::TIPOS[$e['tipo_evento']][0] ?? $e['tipo_evento']))
            . $fila('Logística', $h(implode(' · ', $logistica)))
            . '</table>'
            . (trim((string) $e['descripcion']) !== '' ? '<p style="margin:14px 0 0;font-size:14px;color:#111827;white-space:pre-wrap">' . $h($e['descripcion']) . '</p>' : '')
            . $botones
            . '<p style="margin:18px 0 0"><a href="' . $h($base . 'eventos/index.php?evento=' . (int) $e['id']) . '" style="color:#16365c;font-weight:700;font-size:13px">Ver en el calendario de SEDUC →</a></p>'
            . '<p style="margin:14px 0 0;font-size:11.5px;color:#9ca3af">Este correo trae una invitación de calendario: tu Gmail u Outlook te ofrecerá agregarla a tu agenda.</p>'
            . '</div></div>';
    }
}
