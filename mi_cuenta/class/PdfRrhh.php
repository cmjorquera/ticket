<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/clases/PDF/fpdf.php';

/**
 * PDF de ejemplo para "Mi cuenta" (solo FuenteDemo): liquidación y documentos.
 * Todos llevan la marca de agua "EJEMPLO – SIN VALIDEZ". Con Buk, los PDF
 * reales vienen de Buk y esta clase no se usa.
 */
final class PdfRrhh extends FPDF
{
    private const MESES = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    private string $titulo = '';
    private bool $marca = true;

    public static function t(string $texto): string
    {
        $c = @iconv('UTF-8', 'windows-1252//TRANSLIT', $texto);
        return $c !== false ? $c : $texto;
    }

    public static function pesos(int|float $n): string
    {
        return '$ ' . number_format((float) $n, 0, ',', '.');
    }

    public static function periodoLargo(string $periodo): string
    {
        [$a, $m] = array_map('intval', explode('-', $periodo));
        return self::MESES[$m] . ' ' . $a;
    }

    public function Header(): void
    {
        $w = $this->GetPageWidth();
        $this->SetFillColor(0, 91, 150);
        $this->Rect(0, 0, $w, 22, 'F');
        $this->SetFillColor(244, 176, 0);
        $this->Rect(0, 22, $w, 1.2, 'F');
        $logo = dirname(__DIR__, 2) . '/imagenes/logo_seduc_blanco.png';
        if (is_file($logo)) {
            $this->Image($logo, 12, 4, 0, 14);
        }
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 13);
        $this->SetXY(40, 7);
        $this->Cell($w - 52, 8, self::t($this->titulo), 0, 0, 'R');
        $this->SetTextColor(40, 44, 52);
        $this->SetY(32);
        if ($this->marca) {
            $this->marcaAgua();
        }
    }

    public function Footer(): void
    {
        $this->SetY(-14);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 60, 50);
        $this->Cell(0, 5, self::t('Documento de EJEMPLO generado por el sistema mientras se conecta Buk. No tiene validez.'), 0, 1, 'C');
    }

    private function marcaAgua(): void
    {
        $this->SetFont('Arial', 'B', 44);
        $this->SetTextColor(235, 225, 225);
        $this->rotar(35, 40, 210);
        $this->Text(40, 210, self::t('EJEMPLO – SIN VALIDEZ'));
        $this->rotar(0);
        $this->SetTextColor(40, 44, 52);
    }

    /** Rotación de texto (script clásico de FPDF). */
    private function rotar(float $angulo, float $x = -1, float $y = -1): void
    {
        if ($x === -1.0) { $x = $this->x; }
        if ($y === -1.0) { $y = $this->y; }
        if (!empty($this->rotando)) {
            $this->_out('Q');
        }
        $this->rotando = $angulo != 0;
        if ($this->rotando) {
            $a = deg2rad($angulo);
            $c = cos($a);
            $s = sin($a);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }
    private bool $rotando = false;

    public function _endpage(): void
    {
        if ($this->rotando) {
            $this->rotando = false;
            $this->_out('Q');
        }
        parent::_endpage();
    }

    // ------------------------------------------------------------ liquidación

    public static function liquidacion(array $persona, array $l, bool $ejemplo): string
    {
        $pdf = new self('P', 'mm', 'Letter');
        $pdf->titulo = 'Liquidación de sueldo · ' . self::periodoLargo($l['periodo']);
        $pdf->marca = $ejemplo;
        $pdf->SetMargins(14, 14, 14);
        $pdf->SetAutoPageBreak(true, 18);
        $pdf->AddPage();
        $w = $pdf->GetPageWidth() - 28;

        // Datos de la persona
        $pdf->SetFillColor(244, 246, 249);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($w, 8, self::t('  Trabajador(a)'), 0, 1, 'L', true);
        $pdf->SetFont('Arial', '', 10);
        $filas = [['Nombre', $persona['nombre']], ['Cargo', $persona['cargo'] ?: '—'], ['Área', $persona['area'] ?: '—'],
                  ['Período', self::periodoLargo($l['periodo'])], ['Días trabajados', (string) $l['dias_trabajados']]];
        foreach ($filas as [$k, $v]) {
            $pdf->Cell(40, 6.5, self::t('  ' . $k), 0, 0);
            $pdf->Cell($w - 40, 6.5, self::t((string) $v), 0, 1);
        }
        $pdf->Ln(4);

        // Haberes y descuentos en dos columnas
        $col = ($w - 6) / 2;
        $y = $pdf->GetY();
        $pdf->tablaConceptos(14, $y, $col, 'Haberes', array_map(static fn ($h) => [$h[0] . ($h[2] ? '' : ' *'), $h[1]], $l['haberes']), (int) $l['bruto'], [0, 91, 150]);
        $yH = $pdf->GetY();
        $pdf->tablaConceptos(14 + $col + 6, $y, $col, 'Descuentos', $l['descuentos_detalle'], (int) $l['descuentos'], [192, 57, 43]);
        $pdf->SetY(max($yH, $pdf->GetY()) + 6);

        // Líquido
        $pdf->SetFillColor(0, 91, 150);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell($w * 0.6, 12, self::t('  LÍQUIDO A PAGAR'), 0, 0, 'L', true);
        $pdf->Cell($w * 0.4, 12, self::t(self::pesos($l['liquido']) . '  '), 0, 1, 'R', true);
        $pdf->SetTextColor(107, 114, 128);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Ln(2);
        $pdf->MultiCell($w, 4.5, self::t('* Haber no imponible.   Aporte del empleador (no se descuenta): ' . self::pesos($l['aporte_patronal']) . '.'));
        return $pdf->Output('S');
    }

    private function tablaConceptos(float $x, float $y, float $w, string $titulo, array $filas, int $total, array $color): void
    {
        $this->SetXY($x, $y);
        $this->SetFillColor(...$color);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell($w, 8, self::t('  ' . $titulo), 0, 2, 'L', true);
        $this->SetTextColor(40, 44, 52);
        $this->SetFont('Arial', '', 9.5);
        foreach ($filas as $i => [$concepto, $monto]) {
            $this->SetX($x);
            $this->SetFillColor($i % 2 ? 255 : 248, $i % 2 ? 255 : 249, $i % 2 ? 255 : 251);
            $this->Cell($w * 0.64, 7, self::t('  ' . $concepto), 0, 0, 'L', true);
            $this->Cell($w * 0.36, 7, self::t(self::pesos($monto) . '  '), 0, 1, 'R', true);
        }
        $this->SetX($x);
        $this->SetFont('Arial', 'B', 10);
        $this->SetDrawColor(...$color);
        $this->Cell($w * 0.64, 8, self::t('  Total ' . mb_strtolower($titulo)), 'T', 0, 'L');
        $this->Cell($w * 0.36, 8, self::t(self::pesos($total) . '  '), 'T', 1, 'R');
    }

    // ------------------------------------------------------------ documento genérico

    public static function documentoEjemplo(array $persona, array $d): string
    {
        $pdf = new self('P', 'mm', 'Letter');
        $nombre = preg_replace('/\.pdf$/i', '', $d['nombre']);
        $nombre = trim(str_replace('_', ' ', (string) preg_replace('/^[A-Z]+_/', '', (string) $nombre)));
        $pdf->titulo = $nombre;
        $pdf->SetMargins(18, 14, 18);
        $pdf->AddPage();
        $w = $pdf->GetPageWidth() - 36;
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->MultiCell($w, 8, self::t($nombre), 0, 'L');
        $pdf->Ln(2);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(107, 114, 128);
        $pdf->Cell($w, 6, self::t($persona['nombre'] . ' · ' . ($persona['cargo'] ?: '') . ' · ' . date('d-m-Y', strtotime($d['fecha']))), 0, 1);
        $pdf->Ln(6);
        $pdf->SetTextColor(40, 44, 52);
        $pdf->SetFont('Arial', '', 11);
        $pdf->MultiCell($w, 6, self::t(
            "Este es un documento de ejemplo. Cuando el sistema esté conectado con Buk, aquí se abrirá el archivo "
            . "original que Recursos Humanos tiene cargado en tu ficha (contratos, anexos, certificados y documentos personales).\n\n"
            . "Categoría: " . ucfirst($d['categoria']) . "\nNombre del archivo: " . $d['nombre']
        ));
        return $pdf->Output('S');
    }
}
