<?php
/** Menú del mes en PDF (cualquier usuario con sesión). ?mes=AAAA-MM */
declare(strict_types=1);

ob_start();
require_once __DIR__ . '/../validar_sesion.php';
require_once __DIR__ . '/class/Almuerzos.php';
require_once __DIR__ . '/class/AlmuerzosPdf.php';

$zona = new DateTimeZone('America/Santiago');
$mesTexto = (string) ($_GET['mes'] ?? '');
$inicio = preg_match('/^\d{4}-\d{2}$/', $mesTexto) ? DateTimeImmutable::createFromFormat('!Y-m-d', $mesTexto . '-01', $zona) : false;
if (!$inicio) {
    $inicio = (new DateTimeImmutable('first day of this month', $zona))->setTime(0, 0);
}

try {
    $alm = new Almuerzos(Conexion::getInstance('sistema_panel_central'));
    $menu = $alm->instalado() ? $alm->menuMes((int) $inicio->format('Y'), (int) $inicio->format('n')) : ['dias' => [], 'publicado' => false];
    $pedidos = $alm->instalado() ? $alm->pedidosUsuarioMes((int) $_SESSION['id'], (int) $inicio->format('Y'), (int) $inicio->format('n')) : [];

    $pdf = new AlmuerzosPdf('L', 'mm', 'A4');
    $pdf->titulo = 'Menú del mes';
    $pdf->subtitulo = ucfirst(AlmuerzosPdf::MESES[(int) $inicio->format('n')]) . ' ' . $inicio->format('Y');
    $pdf->AliasNbPages();
    $pdf->SetMargins(12, 27, 12);
    $pdf->SetAutoPageBreak(false);
    $pdf->AddPage();

    if (!$menu['dias']) {
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 10, AlmuerzosPdf::t('Todavía no se publica el menú de este mes.'), 0, 1);
    } else {
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->SetTextColor(107, 114, 128);
        $pdf->Cell(0, 5, AlmuerzosPdf::t('Pedidos de lunes a viernes entre ' . Almuerzos::HORA_INICIO . ' y ' . Almuerzos::HORA_FIN . '. El punto • y el fondo de color indican tu pedido.'), 0, 1);
        $pdf->Ln(1);

        $w = [34, 81, 81, 77];
        $cols = ['menu' => 'MENÚ DEL DÍA', 'hipo' => 'HIPOCALÓRICO', 'picnic' => 'PICNIC'];
        $encabezado = static function () use ($pdf, $w, $cols): void {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetDrawColor(229, 231, 235);
            $pdf->SetFillColor(244, 245, 247);
            $pdf->SetTextColor(107, 114, 128);
            $pdf->Cell($w[0], 7, AlmuerzosPdf::t('DÍA'), 'B', 0, 'L', true);
            $i = 1;
            foreach ($cols as $clave => $titulo) {
                [$r, $g, $b] = AlmuerzosPdf::COLORES[$clave];
                $pdf->SetTextColor($r, $g, $b);
                $pdf->Cell($w[$i++], 7, AlmuerzosPdf::t($titulo), 'B', 0, 'L', true);
            }
            $pdf->Ln();
        };
        $encabezado();

        $semanaAnterior = null;
        foreach ($menu['dias'] as $fecha => $m) {
            $d = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha, $zona);
            $pdf->SetFont('Arial', '', 8.5);
            $textos = [];
            $alto = 1;
            foreach (['menu', 'hipo', 'picnic'] as $i => $op) {
                $txt = str_replace(' + ', "\n", $m[$op]);
                $textos[$op] = $txt;
                $alto = max($alto, $pdf->lineas($w[$i + 1], AlmuerzosPdf::t($txt)));
            }
            $h = $alto * 4.4 + 3;
            if ($pdf->GetY() + $h > $pdf->GetPageHeight() - 16) {
                $pdf->AddPage();
                $encabezado();
                $semanaAnterior = null;
            }
            // Línea más marcada al cambiar de semana
            $semana = $d->format('W');
            if ($semanaAnterior !== null && $semana !== $semanaAnterior) {
                $pdf->SetDrawColor(180, 186, 196);
                $pdf->Line(12, $pdf->GetY(), $pdf->GetPageWidth() - 12, $pdf->GetY());
            }
            $semanaAnterior = $semana;

            $x = 12;
            $y = $pdf->GetY();
            $pdf->SetDrawColor(229, 231, 235);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor(63, 66, 74);
            $pdf->SetXY($x, $y + 1.5);
            $pdf->MultiCell($w[0], 4.4, AlmuerzosPdf::t(AlmuerzosPdf::DIAS[(int) $d->format('N')] . ' ' . (int) $d->format('j')), 0, 'L');
            $x += $w[0];
            foreach (['menu', 'hipo', 'picnic'] as $i => $op) {
                $mio = ($pedidos[$fecha] ?? '') === $op;
                [$r, $g, $b] = AlmuerzosPdf::COLORES[$op];
                if ($mio) {
                    $pdf->SetFillColor((int) (255 - (255 - $r) * .12), (int) (255 - (255 - $g) * .12), (int) (255 - (255 - $b) * .12));
                    $pdf->Rect($x, $y, $w[$i + 1], $h, 'F');
                }
                $pdf->SetXY($x, $y + 1.5);
                $pdf->SetFont('Arial', $mio ? 'B' : '', 8.5);
                $pdf->SetTextColor(63, 66, 74);
                $pdf->MultiCell($w[$i + 1], 4.4, AlmuerzosPdf::t(($mio ? '• ' : '') . $textos[$op]), 0, 'L');
                $x += $w[$i + 1];
            }
            $pdf->Line(12, $y + $h, $pdf->GetPageWidth() - 12, $y + $h);
            $pdf->SetXY(12, $y + $h);
        }
    }

    ob_end_clean();
    $pdf->Output('I', 'menu_almuerzos_' . $inicio->format('Y-m') . '.pdf');
} catch (Throwable $e) {
    ob_end_clean();
    error_log('[almuerzos] pdf menú: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No fue posible generar el PDF. Intenta nuevamente.';
}
