<?php
declare(strict_types=1);

require_once __DIR__ . '/_inicio.php';
require_once __DIR__ . '/../clases/Usuario.php';
require_once __DIR__ . '/../clases/PDF/fpdf.php';

/** Convierte UTF-8 al juego de caracteres de las fuentes base de FPDF. */
function pdf_texto(mixed $valor): string
{
    $texto = (string) $valor;
    $convertido = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $texto);
    return $convertido !== false ? $convertido : $texto;
}

final class ReporteUsuariosPdf extends FPDF
{
    /** @var array<int, array{titulo:string, ancho:float}> */
    private array $columnas;
    private string $detalleFiltros;
    /** @var array<string, int> */
    private array $resumen;

    /**
     * @param array<int, array{titulo:string, ancho:float}> $columnas
     * @param array<string, int> $resumen
     */
    public function __construct(array $columnas, string $detalleFiltros, array $resumen)
    {
        parent::__construct('L', 'mm', 'A4');
        $this->columnas = $columnas;
        $this->detalleFiltros = $detalleFiltros;
        $this->resumen = $resumen;
        $this->SetMargins(8, 8, 8);
        $this->SetAutoPageBreak(true, 13);
    }

    public function Header(): void
    {
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(24, 55, 88);
        $this->Cell(0, 7, pdf_texto('Reporte de usuarios'), 0, 1, 'L');

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(91, 105, 120);
        $this->Cell(0, 4.5, pdf_texto('Generado: ' . date('d-m-Y H:i') . ' | ' . $this->detalleFiltros), 0, 1, 'L');

        $partes = [];
        foreach ($this->resumen as $estado => $cantidad) {
            $partes[] = $estado . ': ' . $cantidad;
        }
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(40, 55, 70);
        $this->Cell(0, 5, pdf_texto(implode('   |   ', $partes)), 0, 1, 'L');
        $this->Ln(1);
        $this->dibujarEncabezadoTabla();
    }

    public function Footer(): void
    {
        $this->SetY(-9);
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(110, 120, 130);
        $this->Cell(0, 4, pdf_texto('Página ' . $this->PageNo()), 0, 0, 'R');
    }

    private function dibujarEncabezadoTabla(): void
    {
        $this->SetFillColor(16, 105, 164);
        $this->SetDrawColor(190, 199, 209);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 7);
        foreach ($this->columnas as $columna) {
            $this->Cell($columna['ancho'], 7, pdf_texto($columna['titulo']), 1, 0, 'C', true);
        }
        $this->Ln();
    }

    /** @param array<int, string> $celdas */
    public function fila(array $celdas, bool $alternada): void
    {
        $lineas = 1;
        foreach ($celdas as $indice => $texto) {
            $lineas = max($lineas, $this->cantidadLineas($this->columnas[$indice]['ancho'], pdf_texto($texto)));
        }
        $alto = max(6.0, $lineas * 3.7);

        if ($this->GetY() + $alto > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }

        $xInicial = $this->GetX();
        $yInicial = $this->GetY();
        $this->SetFillColor($alternada ? 245 : 255, $alternada ? 248 : 255, $alternada ? 251 : 255);
        $this->SetDrawColor(215, 222, 229);
        $this->SetTextColor(35, 45, 55);
        $this->SetFont('Arial', '', 6.7);

        foreach ($celdas as $indice => $texto) {
            $ancho = $this->columnas[$indice]['ancho'];
            $x = $this->GetX();
            $this->Rect($x, $yInicial, $ancho, $alto, $alternada ? 'DF' : 'D');
            $this->SetXY($x + 1, $yInicial + 1);
            $this->MultiCell($ancho - 2, 3.7, pdf_texto($texto), 0, $indice === 0 ? 'C' : 'L');
            $this->SetXY($x + $ancho, $yInicial);
        }
        $this->SetXY($xInicial, $yInicial + $alto);
    }

    private function cantidadLineas(float $ancho, string $texto): int
    {
        $cw = $this->CurrentFont['cw'];
        if ($ancho === 0.0) {
            $ancho = $this->w - $this->rMargin - $this->x;
        }
        $anchoMaximo = ($ancho - 2) * 1000 / $this->FontSize;
        $texto = str_replace("\r", '', $texto);
        $longitud = strlen($texto);
        if ($longitud > 0 && $texto[$longitud - 1] === "\n") {
            $longitud--;
        }
        $separador = -1;
        $inicio = 0;
        $anchoLinea = 0;
        $lineas = 1;
        for ($i = 0; $i < $longitud; $i++) {
            $caracter = $texto[$i];
            if ($caracter === "\n") {
                $separador = -1;
                $inicio = $i + 1;
                $anchoLinea = 0;
                $lineas++;
                continue;
            }
            if ($caracter === ' ') {
                $separador = $i;
            }
            $anchoLinea += $cw[$caracter] ?? 0;
            if ($anchoLinea > $anchoMaximo) {
                if ($separador === -1) {
                    if ($i === $inicio) {
                        $i++;
                    }
                } else {
                    $i = $separador;
                }
                $separador = -1;
                $inicio = $i;
                $anchoLinea = 0;
                $lineas++;
            }
        }
        return $lineas;
    }
}

try {
    $busqueda = trim((string) ($_GET['buscar'] ?? ''));
    $busqueda = function_exists('mb_substr')
        ? mb_substr($busqueda, 0, 100, 'UTF-8')
        : substr($busqueda, 0, 100);
    $filtros = [
        'estado'  => trim((string) ($_GET['estado'] ?? '')),
        'id_area' => max(0, (int) ($_GET['id_area'] ?? 0)),
        'buscar'  => $busqueda,
    ];
    $usuarios = Usuario::listar($db, $filtros);

    $resumenEstados = [];
    foreach ($usuarios as $usuario) {
        $estado = trim((string) ($usuario['estado'] ?? '')) ?: 'Sin estado';
        $resumenEstados[$estado] = ($resumenEstados[$estado] ?? 0) + 1;
    }
    ksort($resumenEstados, SORT_NATURAL | SORT_FLAG_CASE);
    $resumen = ['Total' => count($usuarios)] + $resumenEstados;

    $detalles = [];
    if ($filtros['buscar'] !== '') {
        $detalles[] = 'Búsqueda: ' . $filtros['buscar'];
    }
    if ($filtros['estado'] !== '') {
        $detalles[] = 'Estado: ' . $filtros['estado'];
    }
    if ($filtros['id_area'] > 0) {
        $area = $db->fetchOne('SELECT nombre_area FROM area_trabajo WHERE id_area = ? LIMIT 1', [$filtros['id_area']]);
        $detalles[] = 'Área: ' . ((string) ($area['nombre_area'] ?? $filtros['id_area']));
    }
    $detalleFiltros = $detalles !== [] ? implode(' | ', $detalles) : 'Todos los usuarios';

    $columnas = [
        ['titulo' => 'N°', 'ancho' => 8.0],
        ['titulo' => 'Usuario', 'ancho' => 40.0],
        ['titulo' => 'Email', 'ancho' => 43.0],
        ['titulo' => 'Teléfono', 'ancho' => 23.0],
        ['titulo' => 'Departamento', 'ancho' => 31.0],
        ['titulo' => 'Área', 'ancho' => 28.0],
        ['titulo' => 'Perfiles', 'ancho' => 35.0],
        ['titulo' => 'Colegio', 'ancho' => 38.0],
        ['titulo' => 'Estado', 'ancho' => 19.0],
    ];

    $pdf = new ReporteUsuariosPdf($columnas, $detalleFiltros, $resumen);
    $pdf->SetTitle(pdf_texto('Reporte de usuarios'));
    $pdf->SetAuthor(pdf_texto('Sistema SEDUC'));
    $pdf->AddPage();

    foreach ($usuarios as $indice => $usuario) {
        $nombreCompleto = trim(implode(' ', array_filter([
            $usuario['nombre'] ?? '',
            $usuario['apellido_paterno'] ?? '',
            $usuario['apellido_materno'] ?? '',
        ])));
        $perfiles = is_array($usuario['perfiles'] ?? null)
            ? implode(', ', $usuario['perfiles'])
            : (string) ($usuario['perfiles'] ?? '');
        $pdf->fila([
            (string) ($indice + 1),
            $nombreCompleto ?: 'Sin nombre',
            (string) ($usuario['email'] ?? '—'),
            trim((string) ($usuario['telefono'] ?? '')) ?: '—',
            (string) ($usuario['nombre_departamento'] ?? '—'),
            (string) ($usuario['nombre_area'] ?? 'Sin área'),
            $perfiles !== '' ? $perfiles : 'Sin perfil',
            (string) ($usuario['nom_colegio'] ?? 'Sin colegio'),
            (string) ($usuario['estado'] ?? 'Sin estado'),
        ], $indice % 2 === 1);
    }

    if ($usuarios === []) {
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(80, 90, 100);
        $pdf->Cell(0, 14, pdf_texto('No se encontraron usuarios para los filtros seleccionados.'), 1, 1, 'C');
    }

    $nombreArchivo = 'usuarios_' . date('Y-m-d_H-i') . '.pdf';
    $pdf->Output('D', $nombreArchivo);
    exit;
} catch (Throwable $e) {
    error_log('No fue posible generar el PDF de usuarios: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'No fue posible generar el reporte de usuarios.';
}
