<?php
require_once dirname(__DIR__) . '/componentes/boot.php';

ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Metodo no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($idUsuarioSession <= 0) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'Sesion no activa.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $colegioSession = $inventario->obtenerColegioDelUsuario($idUsuarioSession);
    $idColegio      = (int)($colegioSession['id_colegio'] ?? 0);

    if ($idColegio <= 0) {
        throw new RuntimeException('No se encontro un colegio asignado para su usuario. Contacte al administrador.');
    }

    if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        $codigoError = $_FILES['archivo']['error'] ?? -1;
        throw new RuntimeException('No se recibio el archivo o hubo un error en la subida. Codigo: ' . $codigoError);
    }

    $extension = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['xlsx', 'xls'], true)) {
        throw new RuntimeException('El archivo debe ser .xlsx o .xls.');
    }

    $tmpFile = $_FILES['archivo']['tmp_name'];

    require_once dirname(__DIR__, 2) . '/excel/Classes/PHPExcel.php';

    $inputFileType = PHPExcel_IOFactory::identify($tmpFile);
    $reader        = PHPExcel_IOFactory::createReader($inputFileType);
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($tmpFile);
    $sheet       = $spreadsheet->getActiveSheet();

    // toArray: filas 0-indexed, columnas 0-indexed
    $todasFilas = $sheet->toArray(null, true, true, false);

    // Estructura del Excel:
    //   Filas 0-4 (Excel 1-5): encabezado institucional
    //   Fila 5  (Excel 6)    : encabezados de columna
    //   Fila 6+ (Excel 7+)   : datos
    $filasDatos = array_slice($todasFilas, 6);

    // Mapeo columna 0-indexed => campo interno (A=0 ... Q=16)
    // nombre_monitor no viene en Excel: se genera automaticamente desde el numero de serie.
    $columnaMap = [
        0  => 'id_usuario_asignado',
        1  => 'id_ubicacion',
        2  => 'id_estado',
        3  => 'marca',
        4  => 'modelo',
        5  => 'numero_serie',
        6  => 'codigo_interno',
        7  => 'tamano_monitor',
        8  => 'resolucion_monitor',
        9  => 'tipo_panel',
        10 => 'tipo_conexion',
        11 => 'observacion',
        12 => 'valor_monitor',
        13 => 'proveedor',
        14 => 'numero_factura',
        15 => 'fecha_compra',
        16 => 'observacion_compra',
    ];

    $resultados = [];
    $totalOk    = 0;
    $totalError = 0;

    foreach ($filasDatos as $i => $fila) {
        $numFilaExcel = $i + 7;

        $datos = [];
        foreach ($columnaMap as $idx => $campo) {
            $valor = $fila[$idx] ?? null;

            if ($campo === 'fecha_compra' && is_numeric($valor) && (float)$valor > 1000) {
                try {
                    $valor = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP((float)$valor));
                } catch (Exception $ex) {
                    $valor = '';
                }
            }

            $datos[$campo] = trim((string)($valor ?? ''));
        }

        // Saltar filas completamente vacías
        $todasVacias = true;
        foreach ($datos as $v) {
            if ($v !== '') { $todasVacias = false; break; }
        }
        if ($todasVacias) {
            continue;
        }

        // ── Validaciones de campo obligatorio ────────────────────────────────
        $numeroSerie   = $datos['numero_serie'];
        $idUbicacion   = (int)$datos['id_ubicacion'];
        $idEstado      = (int)$datos['id_estado'];

        if ($numeroSerie === '') {
            $resultados[] = ['fila' => $numFilaExcel, 'ok' => false, 'mensaje' => 'Número de serie es obligatorio.'];
            $totalError++;
            continue;
        }

        if ($idUbicacion <= 0) {
            $resultados[] = ['fila' => $numFilaExcel, 'ok' => false, 'mensaje' => 'Ubicacion es obligatoria. Seleccionela desde el desplegable.'];
            $totalError++;
            continue;
        }

        if ($idEstado <= 0) {
            $resultados[] = ['fila' => $numFilaExcel, 'ok' => false, 'mensaje' => 'Estado es obligatorio. Seleccionelo desde el desplegable.'];
            $totalError++;
            continue;
        }

        // ── Construir payload ─────────────────────────────────────────────────
        $serieLimpia = preg_replace('/[^A-Za-z0-9\-]/', '', strtoupper($numeroSerie));
        $nombreMonitor = 'MON-' . ($serieLimpia !== '' ? $serieLimpia : uniqid());

        $payload = [
            'id_colegio'          => $idColegio,
            'id_ubicacion'        => $idUbicacion,
            'id_usuario_asignado' => (int)$datos['id_usuario_asignado'],
            'id_estado'           => $idEstado,
            'nombre_monitor'      => $nombreMonitor,
            'marca'               => $datos['marca'],
            'modelo'              => $datos['modelo'],
            'numero_serie'        => $numeroSerie,
            'codigo_interno'      => $datos['codigo_interno'],
            'tamano_monitor'      => $datos['tamano_monitor'],
            'resolucion_monitor'  => $datos['resolucion_monitor'],
            'tipo_panel'          => $datos['tipo_panel'],
            'tipo_conexion'       => $datos['tipo_conexion'],
            'observacion'         => $datos['observacion'],
            'compra' => [
                'valor_monitor'  => (float)str_replace(',', '.', $datos['valor_monitor']),
                'proveedor'      => $datos['proveedor'],
                'numero_factura' => $datos['numero_factura'],
                'fecha_compra'   => $datos['fecha_compra'],
                'observacion'    => $datos['observacion_compra'],
            ],
        ];

        // ── Guardar (cada fila en su propia transacción) ─────────────────────
        try {
            $idMonitor    = $inventario->guardarMonitorDesdeArray($payload, $idUsuarioSession);
            $resultados[] = [
                'fila'    => $numFilaExcel,
                'ok'      => true,
                'mensaje' => 'Monitor registrado correctamente (ID: ' . $idMonitor . ').',
            ];
            $totalOk++;
        } catch (Throwable $e) {
            $resultados[] = [
                'fila'    => $numFilaExcel,
                'ok'      => false,
                'mensaje' => $e->getMessage(),
            ];
            $totalError++;
        }
    }

    echo json_encode([
        'ok'          => true,
        'total'       => $totalOk + $totalError,
        'ok_count'    => $totalOk,
        'error_count' => $totalError,
        'detalle'     => $resultados,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'      => false,
        'mensaje' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
