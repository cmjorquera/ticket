<?php
/**
 * PDF de pedidos de almuerzo de un día: cada usuario y lo que pidió.
 * Solo el encargado (AlmuerzosDemo::ENCARGADO_ID). Parámetro opcional ?fecha=AAAA-MM-DD.
 */
declare(strict_types=1);

ob_start();
require_once __DIR__ . '/../validar_sesion.php';
require_once __DIR__ . '/../clases/PDF/fpdf.php';
require_once __DIR__ . '/class/AlmuerzosDemo.php';

$idUsuario = (int) ($_SESSION['id'] ?? 0);
if (!AlmuerzosDemo::esEncargado($idUsuario)) {
    ob_end_clean();
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('No tienes permiso para descargar este reporte.');
}

$zona = new DateTimeZone('America/Santiago');
$fechaTexto = (string) ($_GET['fecha'] ?? '');
$fecha = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaTexto) ? DateTimeImmutable::createFromFormat('!Y-m-d', $fechaTexto, $zona) : false;
if (!$fecha) {
    $fecha = new DateTimeImmutable('today', $zona);
}

final class PdfAlmuerzos extends FPDF
{
    public string $tituloFecha = '';

    public static function t(string $texto): string
    {
        $c = @iconv('UTF-8', 'windows-1252//TRANSLIT', $texto);
        return $c !== false ? $c : $texto;
    }

    public function Header(): void
    {
        $this->SetFillColor(0, 91, 150);
        $this->Rect(0, 0, 210, 20, 'F');
        $this->SetFillColor(244, 176, 0);
        $this->Rect(0, 20, 210, 1.2, 'F');
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 13);
        $this->SetXY(12, 6);
        $this->Cell(120, 8, self::t('Pedidos de almuerzo'), 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        $this->Cell(74, 8, self::t($this->tituloFecha), 0, 0, 'R');
        $this->SetTextColor(63, 66, 74);
        $this->SetY(27);
    }

    public function Footer(): void
    {
        $this->SetY(-12);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(95, 6, self::t('SEDUC · Generado el ' . (new DateTimeImmutable('now', new DateTimeZone('America/Santiago')))->format('d-m-Y H:i')), 0, 0, 'L');
        $this->Cell(95, 6, self::t('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $habil = AlmuerzosDemo::esDiaHabil($fecha);
    $pedidos = $habil ? AlmuerzosDemo::pedidosDelDia($db, $fecha) : [];
    $conteo = AlmuerzosDemo::conteo($pedidos);

    $dias = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $fechaLarga = $dias[(int) $fecha->format('N')] . ' ' . (int) $fecha->format('j') . ' de ' . $meses[(int) $fecha->format('n')] . ' de ' . $fecha->format('Y');

    $pdf = new PdfAlmuerzos('P', 'mm', 'A4');
    $pdf->tituloFecha = $fechaLarga;
    $pdf->AliasNbPages();
    $pdf->SetMargins(12, 27, 12);
    $pdf->SetAutoPageBreak(true, 16);
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(184, 134, 11);
    $pdf->Cell(0, 5, PdfAlmuerzos::t('Datos de ejemplo: los pedidos son inventados hasta conectar el módulo a la base de datos.'), 0, 1);
    $pdf->Ln(2);

    if (!$habil) {
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetTextColor(63, 66, 74);
        $pdf->Cell(0, 10, PdfAlmuerzos::t('No hay servicio de almuerzo este día (fin de semana).'), 0, 1);
    } else {
        // Resumen para cocina
        $colores = ['menu' => [0, 91, 150], 'hipo' => [60, 141, 47], 'picnic' => [201, 138, 0], 'no' => [107, 114, 128], '' => [192, 57, 43]];
        $ancho = 186 / 5;
        $y = $pdf->GetY();
        foreach (AlmuerzosDemo::OPCIONES as $clave => $nombre) {
            [$r, $g, $b] = $colores[$clave];
            $x = $pdf->GetX();
            $pdf->SetDrawColor(229, 231, 235);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Rect($x, $y, $ancho - 2, 18, 'D');
            $pdf->SetFillColor($r, $g, $b);
            $pdf->Rect($x, $y, 1.4, 18, 'F');
            $pdf->SetXY($x + 4, $y + 2);
            $pdf->SetFont('Arial', '', 7.5);
            $pdf->SetTextColor(107, 114, 128);
            $pdf->Cell($ancho - 6, 4, PdfAlmuerzos::t(mb_strtoupper($nombre)), 0, 2);
            $pdf->SetFont('Arial', 'B', 15);
            $pdf->SetTextColor($r, $g, $b);
            $pdf->Cell($ancho - 6, 9, (string) $conteo[$clave], 0, 0);
            $pdf->SetXY($x + $ancho, $y);
        }
        $pdf->SetXY(12, $y + 21);
        $totalAlmuerzos = $conteo['menu'] + $conteo['hipo'] + $conteo['picnic'];
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(63, 66, 74);
        $pdf->Cell(0, 6, PdfAlmuerzos::t('Total de almuerzos a preparar: ' . $totalAlmuerzos . '   ·   Personas en la lista: ' . count($pedidos)), 0, 1);
        $pdf->Ln(2);

        // Tabla
        $w = [10, 74, 62, 40];
        $encabezado = static function () use ($pdf, $w): void {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(244, 245, 247);
            $pdf->SetTextColor(107, 114, 128);
            $pdf->SetDrawColor(229, 231, 235);
            foreach (['N°', 'NOMBRE', 'COLEGIO', 'PEDIDO'] as $i => $t) {
                $pdf->Cell($w[$i], 7, PdfAlmuerzos::t($t), 'B', 0, $i === 0 ? 'C' : 'L', true);
            }
            $pdf->Ln();
        };
        $encabezado();
        $n = 0;
        foreach ($pedidos as $p) {
            if ($pdf->GetY() > 275) {
                $pdf->AddPage();
                $encabezado();
            }
            $n++;
            $par = $n % 2 === 0;
            $pdf->SetFillColor($par ? 250 : 255, $par ? 250 : 255, $par ? 251 : 255);
            $pdf->SetFont('Arial', '', 8.5);
            $pdf->SetTextColor(107, 114, 128);
            $pdf->Cell($w[0], 6.5, (string) $n, 'B', 0, 'C', true);
            $pdf->SetTextColor(63, 66, 74);
            $pdf->Cell($w[1], 6.5, PdfAlmuerzos::t(mb_strimwidth($p['nombre'], 0, 44, '…')), 'B', 0, 'L', true);
            $pdf->SetTextColor(107, 114, 128);
            $pdf->Cell($w[2], 6.5, PdfAlmuerzos::t(mb_strimwidth($p['colegio'] ?: '—', 0, 38, '…')), 'B', 0, 'L', true);
            [$r, $g, $b] = $colores[$p['pedido']];
            $pdf->SetTextColor($r, $g, $b);
            $pdf->SetFont('Arial', 'B', 8.5);
            $pdf->Cell($w[3], 6.5, PdfAlmuerzos::t(AlmuerzosDemo::OPCIONES[$p['pedido']]), 'B', 1, 'L', true);
        }
    }

    ob_end_clean();
    $pdf->Output('I', 'almuerzos_' . $fecha->format('Y-m-d') . '.pdf');
} catch (Throwable $e) {
    ob_end_clean();
    error_log('[almuerzos] pdf: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No fue posible generar el PDF. Intenta nuevamente.';
}
