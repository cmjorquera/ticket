<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/clases/PDF/fpdf.php';

/** Base de los PDF de almuerzos (FPDF de clases/PDF): encabezado, pie y filas de alto variable. */
class AlmuerzosPdf extends FPDF
{
    public string $titulo = '';
    public string $subtitulo = '';

    public const DIAS = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    public const MESES = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    public const COLORES = ['menu' => [0, 91, 150], 'hipo' => [60, 141, 47], 'picnic' => [201, 138, 0], 'no' => [107, 114, 128], 'sin' => [192, 57, 43]];

    public static function t(string $texto): string
    {
        $c = @iconv('UTF-8', 'windows-1252//TRANSLIT', $texto);
        return $c !== false ? $c : $texto;
    }

    public function Header(): void
    {
        $ancho = $this->GetPageWidth();
        $this->SetFillColor(0, 91, 150);
        $this->Rect(0, 0, $ancho, 20, 'F');
        $this->SetFillColor(244, 176, 0);
        $this->Rect(0, 20, $ancho, 1.2, 'F');
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 13);
        $this->SetXY(12, 6);
        $this->Cell(($ancho - 24) / 2, 8, self::t($this->titulo), 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        $this->Cell(($ancho - 24) / 2, 8, self::t($this->subtitulo), 0, 0, 'R');
        $this->SetTextColor(63, 66, 74);
        $this->SetY(27);
    }

    public function Footer(): void
    {
        $this->SetY(-12);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(107, 114, 128);
        $mitad = ($this->GetPageWidth() - 24) / 2;
        $this->Cell($mitad, 6, self::t('SEDUC · Generado el ' . (new DateTimeImmutable('now', new DateTimeZone('America/Santiago')))->format('d-m-Y H:i')), 0, 0, 'L');
        $this->Cell($mitad, 6, self::t('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }

    public static function fechaLarga(DateTimeInterface $d): string
    {
        return self::DIAS[(int) $d->format('N')] . ' ' . (int) $d->format('j') . ' de ' . self::MESES[(int) $d->format('n')] . ' de ' . $d->format('Y');
    }

    /** Líneas que ocupa un texto en una celda de ancho $w (adaptado del ejemplo oficial de FPDF). */
    public function lineas(float $w, string $txt): int
    {
        $cw = $this->CurrentFont['cw'];
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c === "\n") { $i++; $sep = -1; $j = $i; $l = 0; $nl++; continue; }
            if ($c === ' ') { $sep = $i; }
            $l += $cw[$c] ?? 500;
            if ($l > $wmax) {
                if ($sep === -1) { if ($i === $j) { $i++; } } else { $i = $sep + 1; }
                $sep = -1; $j = $i; $l = 0; $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }
}
