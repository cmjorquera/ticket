<?php
/**
 * PDF de pedidos de almuerzo de un día: cada usuario y lo que pidió.
 * Solo el encargado (almuerzo_configuracion.id_usuario_encargado). Lista solo el colegio id_colegio_pdf. Parámetro opcional ?fecha=AAAA-MM-DD.
 */
declare(strict_types=1);

ob_start();
require_once __DIR__ . '/../validar_sesion.php';
require_once __DIR__ . '/class/Almuerzos.php';
require_once __DIR__ . '/class/AlmuerzosPdf.php';

if (!(new Almuerzos(Conexion::getInstance('sistema_panel_central')))->esEncargado((int) ($_SESSION['id'] ?? 0))) {
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

try {
    $alm = new Almuerzos(Conexion::getInstance('sistema_panel_central'));
    $dia = $fecha->format('Y-m-d');
    $menu = $alm->instalado() ? $alm->menuDia($dia) : null;
    $pedidos = $menu ? $alm->pedidosDelDia($dia) : [];
    $conteo = Almuerzos::conteo($pedidos);

    $pdf = new AlmuerzosPdf('P', 'mm', 'A4');
    $pdf->titulo = 'Pedidos de almuerzo';
    $pdf->subtitulo = AlmuerzosPdf::fechaLarga($fecha);
    $pdf->AliasNbPages();
    $pdf->SetMargins(12, 27, 12);
    $pdf->SetAutoPageBreak(true, 16);
    $pdf->AddPage();

    if (!$menu) {
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 10, AlmuerzosPdf::t(AlmuerzosPdf::fechaLarga($fecha) . ': no hay menú publicado, no hay servicio de almuerzo.'), 0, 1);
    } else {
        // Menú del día
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->SetTextColor(107, 114, 128);
        foreach (['menu' => 'Menú del día', 'hipo' => 'Hipocalórico', 'picnic' => 'Picnic'] as $op => $nombre) {
            [$r, $g, $b] = AlmuerzosPdf::COLORES[$op];
            $pdf->SetTextColor($r, $g, $b);
            $pdf->SetFont('Arial', 'B', 8.5);
            $pdf->Cell(26, 5, AlmuerzosPdf::t($nombre . ':'), 0, 0);
            $pdf->SetTextColor(63, 66, 74);
            $pdf->SetFont('Arial', '', 8.5);
            $pdf->Cell(0, 5, AlmuerzosPdf::t($menu[$op]), 0, 1);
        }
        $pdf->Ln(3);

        // Resumen para cocina
        $cajas = ['menu' => 'Menú del día', 'hipo' => 'Hipocalórico', 'picnic' => 'Picnic', 'no' => 'No almuerza', 'sin' => 'Sin respuesta'];
        $ancho = 186 / 5;
        $y = $pdf->GetY();
        $x = 12;
        foreach ($cajas as $clave => $nombre) {
            [$r, $g, $b] = AlmuerzosPdf::COLORES[$clave];
            $pdf->SetDrawColor(229, 231, 235);
            $pdf->Rect($x, $y, $ancho - 2, 18, 'D');
            $pdf->SetFillColor($r, $g, $b);
            $pdf->Rect($x, $y, 1.4, 18, 'F');
            $pdf->SetXY($x + 4, $y + 2);
            $pdf->SetFont('Arial', '', 7.5);
            $pdf->SetTextColor(107, 114, 128);
            $pdf->Cell($ancho - 6, 4, AlmuerzosPdf::t(mb_strtoupper($nombre)), 0, 2);
            $pdf->SetFont('Arial', 'B', 15);
            $pdf->SetTextColor($r, $g, $b);
            $pdf->Cell($ancho - 6, 9, (string) $conteo[$clave], 0, 0);
            $x += $ancho;
        }
        $pdf->SetXY(12, $y + 21);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(63, 66, 74);
        $pdf->Cell(0, 6, AlmuerzosPdf::t('Total de almuerzos a preparar: ' . ($conteo['menu'] + $conteo['hipo'] + $conteo['picnic']) . '   ·   Personas en la lista: ' . count($pedidos)), 0, 1);
        $pdf->Ln(2);

        // Lista por persona
        $w = [10, 70, 58, 34, 14];
        $encabezado = static function () use ($pdf, $w): void {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(244, 245, 247);
            $pdf->SetTextColor(107, 114, 128);
            $pdf->SetDrawColor(229, 231, 235);
            foreach (['N°', 'NOMBRE', 'COLEGIO', 'PEDIDO', 'HORA'] as $i => $t) {
                $pdf->Cell($w[$i], 7, AlmuerzosPdf::t($t), 'B', 0, $i === 0 || $i === 4 ? 'C' : 'L', true);
            }
            $pdf->Ln();
        };
        $encabezado();
        $nombres = Almuerzos::OPCIONES + ['' => 'Sin respuesta'];
        foreach ($pedidos as $n => $p) {
            if ($pdf->GetY() > 275) {
                $pdf->AddPage();
                $encabezado();
            }
            $par = $n % 2 === 1;
            $pdf->SetFillColor($par ? 250 : 255, $par ? 250 : 255, $par ? 251 : 255);
            $pdf->SetFont('Arial', '', 8.5);
            $pdf->SetTextColor(107, 114, 128);
            $pdf->Cell($w[0], 6.5, (string) ($n + 1), 'B', 0, 'C', true);
            $pdf->SetTextColor(63, 66, 74);
            $pdf->Cell($w[1], 6.5, AlmuerzosPdf::t(mb_strimwidth($p['nombre'], 0, 42, '…')), 'B', 0, 'L', true);
            $pdf->SetTextColor(107, 114, 128);
            $pdf->Cell($w[2], 6.5, AlmuerzosPdf::t(mb_strimwidth($p['colegio'] ?: '—', 0, 34, '…')), 'B', 0, 'L', true);
            [$r, $g, $b] = AlmuerzosPdf::COLORES[$p['pedido'] === '' ? 'sin' : $p['pedido']];
            $pdf->SetTextColor($r, $g, $b);
            $pdf->SetFont('Arial', 'B', 8.5);
            $pdf->Cell($w[3], 6.5, AlmuerzosPdf::t($nombres[$p['pedido']]), 'B', 0, 'L', true);
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(107, 114, 128);
            $pdf->Cell($w[4], 6.5, AlmuerzosPdf::t($p['hora'] ?: '—'), 'B', 1, 'C', true);
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
