<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/PDF/fpdf.php';
require_once dirname(__DIR__) . '/Conexion.php';
require_once dirname(__DIR__, 2) . '/helpers/tickets.php';
require_once __DIR__ . '/ConversacionTicket.php';
require_once __DIR__ . '/BitacoraTicket.php';

/**
 * PDF del ticket (como modelos/descarga/ticket_pdf.php del sistema antiguo).
 * Datos, ciclo con fechas, descripción, conversación y — solo para el técnico
 * o administración — la bitácora interna. Lo usan ajax/ticket_pdf.php (descargar)
 * y ajax/ticket_correo.php (adjunto). Lógica: ticket/logica/LOGICA_PDF_CORREO.txt
 */
final class PdfTicket extends FPDF
{
    private array $d = [];

    public static function t(?string $texto): string
    {
        $c = @iconv('UTF-8', 'windows-1252//TRANSLIT', (string) $texto);
        return $c !== false ? $c : (string) $texto;
    }

    /** Reúne todo lo que va en el PDF. $conBitacora solo para técnico / administración. */
    public static function datos(Conexion $db, int $ticketId, bool $conBitacora): ?array
    {
        $t = $db->fetchOne(
            "SELECT t.id_ticket, t.asunto, t.descripcion_ticket, t.id_estado, t.id_prioridad, t.identificador,
                    e.nombre AS estado, c.nombre_categoria AS categoria,
                    TRIM(CONCAT_WS(' ', s.nombre, s.apellido_paterno)) AS solicitante, s.email AS email_solicitante,
                    TRIM(CONCAT_WS(' ', tc.nombre, tc.apellido_paterno)) AS tecnico,
                    col.nom_colegio AS colegio,
                    CONCAT(pt.fecha_creacion_inicio, ' ', COALESCE(pt.hora_creacion_inicio, '00:00:00')) AS creado,
                    CONCAT(pt.fecha_asignacion_tecnico, ' ', COALESCE(pt.hora_asignacion_tecnico, '00:00:00')) AS asignado,
                    CONCAT(pt.fecha_comienzo_ticket, ' ', COALESCE(pt.hora_comienzo_ticket, '00:00:00')) AS comenzado,
                    CONCAT(pt.fecha_termino_ticket, ' ', COALESCE(pt.hora_termino_ticket, '00:00:00')) AS terminado,
                    pt.fecha_estimada_admin AS estimada
               FROM tickets t
          LEFT JOIN estados_ticket e ON e.id = t.id_estado
          LEFT JOIN categoria_de_ticket c ON c.id_categoria = t.id_categoria_ticket
          LEFT JOIN usuarios s ON s.id = t.id_usuario
          LEFT JOIN usuarios tc ON tc.id = t.id_tecnico
          LEFT JOIN (SELECT id_usuario, MIN(id_colegio) AS id_colegio FROM usuario_colegio WHERE estado = 1 GROUP BY id_usuario) uc
                 ON uc.id_usuario = t.id_usuario
          LEFT JOIN colegio col ON col.id_colegio = uc.id_colegio
          LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
              WHERE t.id_ticket = ? AND t.estado = 1
              LIMIT 1",
            [$ticketId]
        );
        if (!$t) {
            return null;
        }
        $t['mensajes'] = (new ConversacionTicket($db))->mensajes($ticketId, 'admin');
        $t['bitacora'] = $conBitacora ? (new BitacoraTicket($db))->listar($ticketId, 0) : null;
        $t['enlace'] = ticket_enlace_seguimiento(ticket_codigo_seguimiento($db, $ticketId));
        return $t;
    }

    public static function generar(array $datos): string
    {
        $pdf = new self('P', 'mm', 'Letter');
        $pdf->d = $datos;
        $pdf->SetTitle(self::t('Ticket #' . $datos['id_ticket']));
        $pdf->SetAuthor('SEDUC Panel Central');
        $pdf->AliasNbPages();
        $pdf->SetMargins(15, 34, 15);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();
        $pdf->cuerpo();
        return $pdf->Output('S');
    }

    private static function fecha(?string $f, bool $hora = true): string
    {
        $f = trim((string) $f);
        if ($f === '' || str_starts_with($f, '0000') || strtotime($f) <= 0) {
            return '—';
        }
        return date($hora && strlen($f) > 10 ? 'd/m/Y H:i' : 'd/m/Y', strtotime($f));
    }

    public function Header(): void
    {
        $w = $this->GetPageWidth();
        $this->SetFillColor(22, 54, 92);
        $this->Rect(0, 0, $w, 24, 'F');
        $this->SetFillColor(232, 163, 61);
        $this->Rect(0, 24, $w, 1.2, 'F');
        $logo = dirname(__DIR__, 2) . '/imagenes/logo_seduc_blanco.png';
        if (is_file($logo)) {
            $this->Image($logo, 15, 5, 0, 14);
        }
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 15);
        $this->SetXY(60, 6);
        $this->Cell($w - 75, 7, self::t('TICKET #' . $this->d['id_ticket']), 0, 2, 'R');
        $this->SetFont('Arial', '', 9);
        $this->Cell($w - 75, 6, self::t('Estado: ' . ($this->d['estado'] ?: '—') . '  ·  ' . date('d/m/Y H:i')), 0, 0, 'R');
        $this->SetTextColor(40, 44, 52);
        $this->SetY(32);
    }

    public function Footer(): void
    {
        $this->SetY(-14);
        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(120, 125, 135);
        $this->Cell(0, 4, self::t('Seguimiento en línea: ' . $this->d['enlace']), 0, 1, 'L', false, $this->d['enlace']);
        $this->Cell(0, 4, self::t('SEDUC Panel Central · página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'L');
    }

    private function seccion(string $titulo): void
    {
        $this->Ln(3);
        if ($this->GetY() > $this->GetPageHeight() - 45) {
            $this->AddPage();
        }
        $this->SetFont('Arial', 'B', 10.5);
        $this->SetTextColor(22, 54, 92);
        $this->Cell(0, 7, self::t($titulo), 0, 1);
        $this->SetDrawColor(220, 224, 230);
        $this->Line(15, $this->GetY(), $this->GetPageWidth() - 15, $this->GetY());
        $this->Ln(2);
        $this->SetTextColor(40, 44, 52);
    }

    private function cuerpo(): void
    {
        $d = $this->d;
        $ancho = $this->GetPageWidth() - 30;

        $this->SetFont('Arial', 'B', 14);
        $this->MultiCell($ancho, 7, self::t((string) $d['asunto']));
        $this->Ln(1);

        $this->seccion('Datos del ticket');
        $prioridad = ticket_prioridad_nombre((int) ($d['id_prioridad'] ?? 0));
        $filas = [
            ['Solicitante', $d['solicitante'], 'Técnico', $d['tecnico'] ?: 'Por asignar'],
            ['Colegio', $d['colegio'] ?: '—', 'Categoría', $d['categoria'] ?: '—'],
            ['Estado', $d['estado'] ?: '—', 'Prioridad', $prioridad],
        ];
        $col = $ancho / 2;
        foreach ($filas as [$l1, $v1, $l2, $v2]) {
            foreach ([[$l1, $v1], [$l2, $v2]] as $i => [$l, $v]) {
                $x = 15 + $i * $col;
                $this->SetXY($x, $this->GetY());
                $this->SetFont('Arial', '', 7.5);
                $this->SetTextColor(120, 125, 135);
                $this->Cell($col - 4, 4, self::t(mb_strtoupper($l)), 0, 2);
                $this->SetFont('Arial', 'B', 10);
                $this->SetTextColor(40, 44, 52);
                $this->Cell($col - 4, 5.5, self::t((string) $v), 0, 0);
                if ($i === 0) {
                    $this->SetY($this->GetY() - 4);
                }
            }
            $this->Ln(8);
        }

        $this->seccion('Ciclo del ticket');
        $pasos = [
            ['Creado', self::fecha($d['creado'])],
            ['Asignado', self::fecha($d['asignado'])],
            ['En proceso', self::fecha($d['comenzado'])],
            ['Fecha estimada', self::fecha($d['estimada'], false)],
            ['Terminado', self::fecha($d['terminado'])],
        ];
        $w = $ancho / count($pasos);
        $y = $this->GetY();
        foreach ($pasos as $i => [$nombre, $f]) {
            $hecho = $f !== '—';
            $this->SetFillColor($hecho ? 47 : 225, $hecho ? 125 : 228, $hecho ? 50 : 233);
            $this->Rect(15 + $i * $w, $y, $w - 2, 1.6, 'F');
            $this->SetXY(15 + $i * $w, $y + 3);
            $this->SetFont('Arial', 'B', 8.5);
            $this->Cell($w - 2, 4.5, self::t($nombre), 0, 2);
            $this->SetFont('Arial', '', 8.5);
            $this->SetTextColor(100, 105, 115);
            $this->Cell($w - 2, 4.5, self::t($f), 0, 0);
            $this->SetTextColor(40, 44, 52);
        }
        $this->SetY($y + 14);

        $this->seccion('Descripción');
        $this->SetFont('Arial', '', 10);
        $this->MultiCell($ancho, 5.2, self::t(ticket_descripcion_texto((string) $d['descripcion_ticket']) ?: '—'));

        $this->seccion('Conversación (' . count($d['mensajes']) . ')');
        if (!$d['mensajes']) {
            $this->SetFont('Arial', 'I', 9.5);
            $this->Cell(0, 6, self::t('Sin mensajes.'), 0, 1);
        }
        $roles = ['usuario' => 'solicitante', 'tecnico' => 'técnico', 'admin' => 'administración'];
        foreach ($d['mensajes'] as $m) {
            $this->SetFont('Arial', 'B', 8.5);
            $this->SetTextColor(22, 54, 92);
            $this->Cell(0, 4.5, self::t($m['autor'] . ' (' . ($roles[$m['lado']] ?? $m['lado']) . ') · ' . self::fecha($m['fecha'])), 0, 1);
            $this->SetTextColor(40, 44, 52);
            $this->SetFont('Arial', '', 9.5);
            $this->MultiCell($ancho, 4.8, self::t($m['adjunto'] ? '[Archivo adjunto] ' . $m['adjunto']['nombre'] : $m['texto']));
            $this->Ln(1.5);
        }

        if (is_array($d['bitacora'])) {
            $this->seccion('Bitácora interna (técnico y administración)');
            if (!$d['bitacora']) {
                $this->SetFont('Arial', 'I', 9.5);
                $this->Cell(0, 6, self::t('Sin registros.'), 0, 1);
            }
            foreach ($d['bitacora'] as $b) {
                $this->SetFont('Arial', 'B', 8.5);
                $this->SetTextColor(138, 98, 0);
                $quien = $b['lado'] === 'sistema' ? 'Sistema' : $b['autor'] . ' (' . ($b['lado'] === 'admin' ? 'administración' : 'técnico') . ')';
                $this->Cell(0, 4.5, self::t($quien . ' · ' . self::fecha($b['fecha'])), 0, 1);
                $this->SetTextColor(40, 44, 52);
                $this->SetFont('Arial', '', 9.5);
                $this->MultiCell($ancho, 4.8, self::t($b['texto']));
                $this->Ln(1.5);
            }
        }
    }
}
