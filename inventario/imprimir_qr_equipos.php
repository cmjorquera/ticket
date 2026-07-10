<?php
require_once __DIR__ . '/componentes/boot.php';

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($autoloadPath)) {
    require_once $autoloadPath;
}

if (!class_exists('FPDF')) {
    $fpdfPaths = [
        dirname(__DIR__) . '/vendor/setasign/fpdf/fpdf.php',
        dirname(__DIR__) . '/PDF/PDF/fpdf.php',
        dirname(__DIR__) . '/PDF/fpdf.php',
    ];
    foreach ($fpdfPaths as $fpdfPath) {
        if (is_file($fpdfPath)) {
            require_once $fpdfPath;
            break;
        }
    }
}

if (!class_exists('FPDF')) {
    inventario_responder_error('No se encontro la libreria FPDF disponible para generar el PDF de codigos QR.', 500);
}

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

if (!class_exists(QRCode::class) || !class_exists(QROptions::class)) {
    inventario_responder_error('No se encontro la libreria local de codigos QR en vendor/chillerlan.', 500);
}

ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED);

function inv_qr_pdf_text($texto)
{
    $texto = (string)$texto;
    $convertido = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
    return $convertido !== false ? $convertido : $texto;
}

function inv_qr_slug($texto)
{
    $texto = strtoupper(trim((string)$texto));
    $texto = preg_replace('/[^A-Z0-9\-_.]+/', '-', $texto);
    $texto = trim($texto, '-');
    return $texto !== '' ? $texto : 'EQUIPO';
}

function inv_qr_dibujar_en_pdf(FPDF $pdf, $url, $x, $y, $size)
{
    $options = new QROptions([
        'eccLevel' => QRCode::ECC_M,
        'quietzoneSize' => 2,
    ]);

    $qr = (new QRCode($options))->addByteSegment((string)$url);
    $matrix = $qr->getQRMatrix();
    $data = $matrix->getMatrix();
    $count = count($data);
    if ($count <= 0) {
        return;
    }

    $module = $size / $count;
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect($x, $y, $size, $size, 'F');
    $pdf->SetFillColor(0, 0, 0);

    foreach ($data as $rowIndex => $row) {
        foreach ($row as $colIndex => $moduleType) {
            if ($matrix->isDark((int)$moduleType)) {
                $pdf->Rect($x + ($colIndex * $module), $y + ($rowIndex * $module), $module, $module, 'F');
            }
        }
    }
}

function inv_qr_generar_png_si_disponible($url, $idEquipo, $nombreEquipo)
{
    if (!extension_loaded('gd')) {
        return null;
    }

    $dir = dirname(__DIR__) . '/codigosQR/equipos';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $archivo = $dir . '/' . (int)$idEquipo . '_' . inv_qr_slug($nombreEquipo) . '.png';
    if (is_file($archivo) && filesize($archivo) > 0) {
        return $archivo;
    }

    $options = new QROptions([
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel' => QRCode::ECC_M,
        'scale' => 6,
        'quietzoneSize' => 2,
        'imageBase64' => false,
    ]);

    (new QRCode($options))->render((string)$url, $archivo);
    return is_file($archivo) ? $archivo : null;
}

try {
    $alcanceInventario = $inventario->obtenerAlcanceInventario($idUsuarioSession);
    $filtros = [
        'id_colegio' => (int)($_GET['id_colegio'] ?? 0),
        'id_estado' => (int)($_GET['id_estado'] ?? 0),
        'tipo_pc' => '',
        'id_ubicacion' => (int)($_GET['id_ubicacion'] ?? 0),
        'id_usuario_asignado' => (int)($_GET['id_usuario_asignado'] ?? 0),
        'busqueda' => trim((string)($_GET['busqueda'] ?? '')),
    ];
    $filtros = $inventario->normalizarFiltrosPorAlcance($filtros, $alcanceInventario);
    $equipos = $inventario->listarEquipos($filtros);
} catch (Throwable $e) {
    inventario_responder_error('No fue posible preparar los codigos QR: ' . $e->getMessage(), 422);
}

$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->SetTitle(inv_qr_pdf_text('Codigos QR Inventario PC'));
$pdf->SetAuthor(inv_qr_pdf_text('SISTEMA TICKET'));
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, inv_qr_pdf_text('Codigos QR - Inventario PC'), 0, 1, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(90, 105, 125);
$pdf->Cell(0, 6, inv_qr_pdf_text('Equipos visibles segun filtros y permisos actuales'), 0, 1, 'L');
$pdf->Ln(2);
$pdf->SetTextColor(0, 0, 0);

if (empty($equipos)) {
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 10, inv_qr_pdf_text('No hay equipos disponibles para imprimir con los filtros actuales.'), 0, 1, 'L');
    $pdf->Output('I', 'codigos_qr_inventario_pc.pdf');
    exit;
}

$cols = 3;
$gap = 5;
$pageW = 216;
$margin = 10;
$cardW = ($pageW - ($margin * 2) - ($gap * ($cols - 1))) / $cols;
$cardH = 58;
$qrSize = 29;
$x0 = $margin;
$y0 = 28;

foreach ($equipos as $idx => $equipo) {
    $col = $idx % $cols;
    $row = intdiv($idx % 12, $cols);
    if ($idx > 0 && $idx % 12 === 0) {
        $pdf->AddPage();
        $y0 = 12;
    }

    $x = $x0 + ($col * ($cardW + $gap));
    $y = $y0 + ($row * ($cardH + $gap));

    $idEquipo = (int)($equipo['id_equipo'] ?? 0);
    $identificadorTecnico = (string)($equipo['nombre_equipo'] ?? ('Equipo ' . $idEquipo));
    $nombrePersonalizadoPdf = trim((string)($equipo['nombre_personalizado'] ?? ''));
    $nombre = $nombrePersonalizadoPdf !== '' ? $nombrePersonalizadoPdf : $identificadorTecnico;
    $serie = (string)($equipo['numero_serie'] ?? '');
    $url = inventario_url_absoluta('equipoQRinformacion.php?id=' . $idEquipo);
    $nombreArchivoQr = (int)$idEquipo . '_' . inv_qr_slug($identificadorTecnico) . '.png';
    $qrPath = inv_qr_generar_png_si_disponible($url, $idEquipo, $identificadorTecnico);

    $pdf->SetDrawColor(216, 226, 239);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect($x, $y, $cardW, $cardH, 'D');
    if ($qrPath) {
        $pdf->Image($qrPath, $x + 4, $y + 6, $qrSize, $qrSize);
    } else {
        inv_qr_dibujar_en_pdf($pdf, $url, $x + 4, $y + 6, $qrSize);
    }

    $tx = $x + 36;
    $pdf->SetXY($tx, $y + 6);
    $pdf->SetFont('Arial', 'B', 8.6);
    $pdf->MultiCell($cardW - 39, 4, inv_qr_pdf_text($nombre), 0, 'L');
    $pdf->SetX($tx);
    $pdf->SetFont('Arial', '', 7.4);
    $pdf->SetTextColor(78, 92, 110);
    if ($nombrePersonalizadoPdf !== '') {
        $pdf->MultiCell($cardW - 39, 3.7, inv_qr_pdf_text('(' . $identificadorTecnico . ')'), 0, 'L');
        $pdf->SetX($tx);
    }
    $pdf->MultiCell($cardW - 39, 3.7, inv_qr_pdf_text('Serie: ' . ($serie ?: 'Sin serie')), 0, 'L');
    $pdf->SetX($tx);
    $pdf->MultiCell($cardW - 39, 3.7, inv_qr_pdf_text('Colegio: ' . (($equipo['nom_colegio'] ?? '') ?: 'Sin colegio')), 0, 'L');
    $pdf->SetX($tx);
    $pdf->MultiCell($cardW - 39, 3.7, inv_qr_pdf_text('Ubicacion: ' . (($equipo['nombre_ubicacion'] ?? '') ?: 'Pendiente')), 0, 'L');
    $pdf->SetX($tx);
    $pdf->MultiCell($cardW - 39, 3.7, inv_qr_pdf_text('Resp.: ' . (trim((string)($equipo['usuario_asignado'] ?? '')) ?: 'Sin asignar')), 0, 'L');
    $pdf->SetXY($x + 4, $y + 39);
    $pdf->SetFont('Arial', '', 6.4);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->MultiCell($cardW - 8, 3.2, inv_qr_pdf_text($url . ' | ' . $nombreArchivoQr), 0, 'L');
    $pdf->SetTextColor(0, 0, 0);
}

$pdf->Output('I', 'codigos_qr_inventario_pc.pdf');
