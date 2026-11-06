<?php
session_start();

if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED);

require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/../PDF/PDF/fpdf.php';

function usuarios_pdf_texto($texto)
{
    $texto = (string)$texto;
    if (function_exists('iconv')) {
        $convertido = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
        if ($convertido !== false) {
            return $convertido;
        }
    }
    return function_exists('utf8_decode') ? utf8_decode($texto) : $texto;
}

class UsuariosSistemaPdf extends FPDF
{
    public $resumen = '';
    public $generadoPor = '';

    private $anchos = [8, 37, 42, 20, 34, 29, 20, 54, 33];
    private $titulos = ['#', 'Nombre completo', 'Correo', 'Telefono', 'Cargo', 'Perfiles', 'Estado', 'Colegio(s)', 'Departamento / area'];
    private $alineaciones = ['C', 'L', 'L', 'L', 'L', 'L', 'C', 'L', 'L'];

    public function Header()
    {
        $this->SetFillColor(0, 91, 150);
        $this->Rect(0, 0, 297, 20, 'F');
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 14);
        $this->SetXY(10, 4.5);
        $this->Cell(0, 6, usuarios_pdf_texto('Registro general de usuarios'), 0, 1, 'L');
        $this->SetFont('Arial', '', 7.5);
        $this->SetX(10);
        $this->Cell(0, 4, usuarios_pdf_texto('SISTEMA TICKET  |  Generado el ' . date('d-m-Y H:i') . ' por ' . $this->generadoPor), 0, 1, 'L');

        $this->SetY(22);
        $this->SetTextColor(63, 66, 74);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 5, usuarios_pdf_texto($this->resumen), 0, 1, 'L');
        $this->Ln(1);
        $this->dibujarCabeceraTabla();
    }

    public function Footer()
    {
        $this->SetY(-11);
        $this->SetDrawColor(229, 231, 235);
        $this->Line(10, $this->GetY(), 287, $this->GetY());
        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(190, 7, usuarios_pdf_texto('Documento de control interno - Estado registrado al momento de la descarga'), 0, 0, 'L');
        $this->Cell(87, 7, usuarios_pdf_texto('Pagina ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }

    private function dibujarCabeceraTabla()
    {
        $basicos = array_sum(array_slice($this->anchos, 0, 5));
        $acceso = $this->anchos[5] + $this->anchos[6];
        $organizacion = array_sum(array_slice($this->anchos, 7, 2));

        $this->SetFont('Arial', 'B', 7.5);
        $this->SetTextColor(255, 255, 255);
        $this->SetFillColor(63, 66, 74);
        $this->Cell($basicos, 6, usuarios_pdf_texto('DATOS BASICOS'), 1, 0, 'C', true);
        $this->Cell($acceso, 6, usuarios_pdf_texto('ACCESO'), 1, 0, 'C', true);
        $this->Cell($organizacion, 6, usuarios_pdf_texto('ORGANIZACION'), 1, 1, 'C', true);

        $this->SetFillColor(231, 241, 248);
        $this->SetTextColor(0, 76, 129);
        $this->SetFont('Arial', 'B', 6.8);
        foreach ($this->anchos as $i => $ancho) {
            $this->Cell($ancho, 7, usuarios_pdf_texto($this->titulos[$i]), 1, 0, $this->alineaciones[$i], true);
        }
        $this->Ln();
    }

    private function numeroLineas($ancho, $texto)
    {
        $cw = $this->CurrentFont['cw'];
        $anchoMaximo = ($ancho - 2) * 1000 / $this->FontSize;
        $texto = str_replace("\r", '', (string)$texto);
        $largo = strlen($texto);
        if ($largo > 0 && $texto[$largo - 1] === "\n") {
            $largo--;
        }

        $separador = -1;
        $i = 0;
        $j = 0;
        $suma = 0;
        $lineas = 1;
        while ($i < $largo) {
            $caracter = $texto[$i];
            if ($caracter === "\n") {
                $i++;
                $separador = -1;
                $j = $i;
                $suma = 0;
                $lineas++;
                continue;
            }
            if ($caracter === ' ') {
                $separador = $i;
            }
            $suma += $cw[$caracter] ?? 0;
            if ($suma > $anchoMaximo) {
                if ($separador === -1) {
                    if ($i === $j) {
                        $i++;
                    }
                } else {
                    $i = $separador + 1;
                }
                $separador = -1;
                $j = $i;
                $suma = 0;
                $lineas++;
            } else {
                $i++;
            }
        }
        return $lineas;
    }

    public function filaUsuario(array $datos, $alternada = false)
    {
        $this->SetFont('Arial', '', 6.7);
        $convertidos = [];
        $maxLineas = 1;
        foreach ($this->anchos as $i => $ancho) {
            $convertidos[$i] = usuarios_pdf_texto($datos[$i] ?? '');
            $maxLineas = max($maxLineas, $this->numeroLineas($ancho, $convertidos[$i]));
        }
        $altoLinea = 3.7;
        $altoFila = max(7.4, $maxLineas * $altoLinea + 1.2);

        if ($this->GetY() + $altoFila > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }

        $x = $this->GetX();
        $y = $this->GetY();
        $estado = strtolower(trim((string)($datos[6] ?? '')));

        foreach ($this->anchos as $i => $ancho) {
            $esEstado = ($i === 6);
            if ($esEstado && $estado === 'activo') {
                $this->SetFillColor(230, 248, 240);
                $this->SetTextColor(15, 122, 82);
            } elseif ($esEstado && $estado === 'pendiente') {
                $this->SetFillColor(254, 246, 224);
                $this->SetTextColor(154, 107, 0);
            } elseif ($esEstado && in_array($estado, ['bloqueado', 'inactivo'], true)) {
                $this->SetFillColor(253, 236, 235);
                $this->SetTextColor(165, 45, 34);
            } else {
                $tono = $alternada ? 248 : 255;
                $this->SetFillColor($tono, $tono === 248 ? 250 : 255, $tono === 248 ? 252 : 255);
                $this->SetTextColor(63, 66, 74);
            }

            $this->Rect($x, $y, $ancho, $altoFila, 'DF');
            $this->SetXY($x + 1, $y + .7);
            $this->MultiCell($ancho - 2, $altoLinea, $convertidos[$i], 0, $this->alineaciones[$i]);
            $x += $ancho;
        }
        $this->SetXY(10, $y + $altoFila);
    }
}

try {
    $idUsuarioSesion = (int)($_SESSION['id'] ?? 0);
    if ($idUsuarioSesion <= 0) {
        http_response_code(401);
        exit('No autorizado');
    }

    $db = new MySQL('', '', '');
    $resultadoPermiso = $db->consulta("
        SELECT 1
        FROM permisos_menu_1
        WHERE id_usuario = " . $idUsuarioSesion . "
          AND id_menu1 = 7
          AND id_tipo_permiso IN (1, 2)
        LIMIT 1
    ");
    if (!$db->fetch_assoc($resultadoPermiso)) {
        http_response_code(403);
        exit('No tiene permiso para descargar el registro de usuarios.');
    }

    $resultado = $db->consulta("
        SELECT
            u.id,
            TRIM(CONCAT_WS(' ', u.nombre, u.apellido_paterno, u.apellido_materno)) AS nombre_completo,
            COALESCE(NULLIF(TRIM(u.email), ''), 'No informado') AS email,
            COALESCE(NULLIF(TRIM(u.telefono), ''), 'No informado') AS telefono,
            COALESCE(NULLIF(TRIM(u.cargo), ''), 'No informado') AS cargo,
            COALESCE(NULLIF(TRIM(u.estado), ''), 'Sin estado') AS estado,
            COALESCE(NULLIF(TRIM(at.nombre_area), ''), 'Sin departamento') AS departamento,
            COALESCE(pu.perfiles, 'Sin perfil') AS perfiles,
            COALESCE(cu.colegios, 'Sin colegio') AS colegios
        FROM usuarios u
        LEFT JOIN area_trabajo at ON at.id_area = u.id_area_trabajo
        LEFT JOIN (
            SELECT up.id_usuario,
                   GROUP_CONCAT(DISTINCT CONCAT(UCASE(LEFT(p.nombre, 1)), SUBSTRING(p.nombre, 2))
                                ORDER BY p.id_perfil SEPARATOR ', ') AS perfiles
            FROM usuario_perfil up
            INNER JOIN perfiles p ON p.id_perfil = up.id_perfil
            GROUP BY up.id_usuario
        ) pu ON pu.id_usuario = u.id
        LEFT JOIN (
            SELECT uc.id_usuario,
                   GROUP_CONCAT(DISTINCT c.nom_colegio ORDER BY c.nom_colegio SEPARATOR ', ') AS colegios
            FROM usuario_colegio uc
            INNER JOIN colegio c ON c.id_colegio = uc.id_colegio
            GROUP BY uc.id_usuario
        ) cu ON cu.id_usuario = u.id
        ORDER BY
            CASE LOWER(TRIM(COALESCE(u.estado, '')))
                WHEN 'bloqueado' THEN 1
                WHEN 'inactivo' THEN 2
                WHEN 'pendiente' THEN 3
                WHEN 'activo' THEN 4
                ELSE 5
            END,
            u.nombre, u.apellido_paterno, u.apellido_materno
    ");

    $usuarios = [];
    $conteoEstados = [];
    while ($fila = $db->fetch_assoc($resultado)) {
        $usuarios[] = $fila;
        $estado = ucfirst(strtolower(trim((string)$fila['estado'])));
        $conteoEstados[$estado] = ($conteoEstados[$estado] ?? 0) + 1;
    }

    $resGenerador = $db->consulta("SELECT TRIM(CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno)) AS nombre FROM usuarios WHERE id = " . $idUsuarioSesion . " LIMIT 1");
    $filaGenerador = $db->fetch_assoc($resGenerador);
    $generadoPor = trim((string)($filaGenerador['nombre'] ?? ''));
    if ($generadoPor === '') {
        $generadoPor = 'Usuario #' . $idUsuarioSesion;
    }

    $partesResumen = ['Total: ' . count($usuarios)];
    $estadosMostrados = 0;
    foreach (['Activo', 'Pendiente', 'Bloqueado', 'Inactivo', 'Sin estado'] as $nombreEstado) {
        if (!empty($conteoEstados[$nombreEstado])) {
            $partesResumen[] = $nombreEstado . ': ' . $conteoEstados[$nombreEstado];
            $estadosMostrados += $conteoEstados[$nombreEstado];
        }
    }
    if ($estadosMostrados < count($usuarios)) {
        $partesResumen[] = 'Otros estados: ' . (count($usuarios) - $estadosMostrados);
    }

    $pdf = new UsuariosSistemaPdf('L', 'mm', 'A4');
    $pdf->AliasNbPages();
    $pdf->SetTitle(usuarios_pdf_texto('Registro general de usuarios'));
    $pdf->SetAuthor(usuarios_pdf_texto('SISTEMA TICKET'));
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 13);
    $pdf->generadoPor = $generadoPor;
    $pdf->resumen = implode('  |  ', $partesResumen);
    $pdf->AddPage();

    if (empty($usuarios)) {
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(107, 114, 128);
        $pdf->Cell(277, 10, usuarios_pdf_texto('No hay usuarios registrados en el sistema.'), 1, 1, 'C');
    } else {
        foreach ($usuarios as $indice => $usuario) {
            $pdf->filaUsuario([
                (string)($indice + 1),
                $usuario['nombre_completo'] ?: 'Sin nombre',
                $usuario['email'],
                $usuario['telefono'],
                $usuario['cargo'],
                $usuario['perfiles'],
                $usuario['estado'],
                $usuario['colegios'],
                $usuario['departamento'],
            ], ($indice % 2) === 1);
        }
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    $pdf->Output('D', 'registro_usuarios_' . date('Ymd_His') . '.pdf');
} catch (Throwable $e) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    error_log('Usuarios PDF error: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No fue posible generar el PDF de usuarios. Revise el registro de errores del servidor.';
}
exit;
