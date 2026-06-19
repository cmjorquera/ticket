<?php
require_once dirname(__DIR__) . '/componentes/boot.php';

// PHPExcel genera avisos de deprecacion en PHP 8.x — suprimirlos para no corromper la respuesta JSON
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
    // ── Obtener id_colegio desde la sesión (via usuario_colegio) ─────────────
    // $_SESSION no almacena id_colegio directamente; se consulta usuario_colegio.
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
    //   Filas 0-4 (Excel 1-5): encabezado institucional (logo, colegio, título, fecha, separador)
    //   Fila 5  (Excel 6)    : encabezados de columna legibles
    //   Fila 6+ (Excel 7+)   : datos
    $filasDatos = array_slice($todasFilas, 6);

    // Mapeo columna 0-indexed => campo interno (sin id_colegio/nombre/QR — vienen del sistema)
    $columnaMap = [
        0  => 'id_usuario_asignado',
        1  => 'fabricante',
        2  => 'producto',
        3  => 'numero_serie',
        4  => 'tipo_pc',
        5  => 'id_estado',
        6  => 'valor_equipo',
        7  => 'proveedor',
        8  => 'numero_factura',
        9  => 'fecha_compra',
        10 => 'observacion_compra',
        11 => 'almacenamiento_modelo',
        12 => 'almacenamiento_capacidad',
        13 => 'almacenamiento_tamano',
        14 => 'procesador_fabricante',
        15 => 'procesador_modelo',
        16 => 'procesador_velocidad',
        17 => 'windows',
        18 => 'office',
        19 => 'antivirus',
        20 => 'memoria_designacion',
        21 => 'memoria_formato',
        22 => 'memoria_tipo',
        23 => 'memoria_tamano',
        24 => 'memoria_frecuencia',
        25 => 'memoria_marca',
        26 => 'monitor_modelo',
        27 => 'monitor_codigo',
        28 => 'monitor_serie',
        29 => 'monitor_tamano',
        30 => 'monitor_resolucion',
    ];

    $resultados = [];
    $totalOk    = 0;
    $totalError = 0;

    foreach ($filasDatos as $i => $fila) {
        // Número de fila real en el Excel (datos empiezan en fila 7)
        $numFilaExcel = $i + 7;

        // Mapear columnas a campos nombrados
        $datos = [];
        foreach ($columnaMap as $idx => $campo) {
            $valor = $fila[$idx] ?? null;

            // Detectar y convertir fecha serial de Excel en campo fecha_compra
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
            if ($v !== '') {
                $todasVacias = false;
                break;
            }
        }
        if ($todasVacias) {
            continue;
        }

        // ── Validaciones ─────────────────────────────────────────────────────
        $numeroSerie  = $datos['numero_serie'];

        if ($numeroSerie === '') {
            $resultados[] = ['fila' => $numFilaExcel, 'ok' => false, 'mensaje' => 'Numero de serie es obligatorio.'];
            $totalError++;
            continue;
        }

        if ($datos['tipo_pc'] === '') {
            $resultados[] = ['fila' => $numFilaExcel, 'ok' => false, 'mensaje' => 'Tipo de PC es obligatorio.'];
            $totalError++;
            continue;
        }

        // ── Construir payload ─────────────────────────────────────────────────
        $idEstado = (int)$datos['id_estado'];
        if ($idEstado <= 0) {
            $idEstado = 1;
        }

        $serieLimpia = preg_replace('/[^A-Za-z0-9\-]/', '', strtoupper($numeroSerie));
        $nombreEquipo = 'PC-' . ($serieLimpia !== '' ? $serieLimpia : uniqid());
        $qrCode = 'PC-' . ($serieLimpia !== '' ? $serieLimpia : uniqid());

        $payload = [
            'equipo' => [
                'id_usuario'          => $idUsuarioSession,
                'id_colegio'          => $idColegio,
                'id_usuario_registra' => $idUsuarioSession,
                'id_usuario_asignado' => (int)$datos['id_usuario_asignado'],
                'nombre_equipo'       => $nombreEquipo,
                'fabricante'          => $datos['fabricante'],
                'producto'            => $datos['producto'],
                'numero_serie'        => $numeroSerie,
                'tipo_pc'             => $datos['tipo_pc'],
                'qr_code'             => $qrCode,
                'id_estado'           => $idEstado,
            ],
            'compra' => [
                'valor_equipo'   => (float)str_replace(',', '.', $datos['valor_equipo']),
                'proveedor'      => $datos['proveedor'],
                'numero_factura' => $datos['numero_factura'],
                'fecha_compra'   => $datos['fecha_compra'],
                'observacion'    => $datos['observacion_compra'],
            ],
            'almacenamiento' => [
                'equipo_modelo'    => $datos['almacenamiento_modelo'],
                'equipo_capacidad' => $datos['almacenamiento_capacidad'],
                'equipo_tamano'    => $datos['almacenamiento_tamano'],
            ],
            'procesador' => [
                'equipo_fabricante' => $datos['procesador_fabricante'],
                'equipo_modelo'     => $datos['procesador_modelo'],
                'equipo_velocidad'  => $datos['procesador_velocidad'],
            ],
            'software' => [
                'windows'   => $datos['windows'],
                'office'    => $datos['office'],
                'antivirus' => $datos['antivirus'],
            ],
            'memorias'  => [],
            'monitores' => [],
        ];

        // Memoria RAM (opcional — se agrega solo si tiene al menos un campo con valor)
        $camposMemoria = [
            'designacion_memoria' => $datos['memoria_designacion'],
            'formato_memoria'     => $datos['memoria_formato'],
            'tipo_memoria'        => $datos['memoria_tipo'],
            'tamano_memoria'      => $datos['memoria_tamano'],
            'frecuencia_memoria'  => $datos['memoria_frecuencia'],
            'marca_memoria'       => $datos['memoria_marca'],
            'orden_memoria'       => 1,
        ];
        foreach ($camposMemoria as $k => $v) {
            if ($k !== 'orden_memoria' && $v !== '') {
                $payload['memorias'][] = $camposMemoria;
                break;
            }
        }

        // Monitor (opcional)
        $camposMonitor = [
            'modelo_monitor'     => $datos['monitor_modelo'],
            'codigo_monitor'     => $datos['monitor_codigo'],
            'serie_monitor'      => $datos['monitor_serie'],
            'tamano_monitor'     => $datos['monitor_tamano'],
            'resolucion_monitor' => $datos['monitor_resolucion'],
            'orden_monitor'      => 1,
        ];
        foreach ($camposMonitor as $k => $v) {
            if ($k !== 'orden_monitor' && $v !== '') {
                $payload['monitores'][] = $camposMonitor;
                break;
            }
        }

        // ── Guardar (cada fila en su propia transacción) ─────────────────────
        try {
            $idEquipo     = $inventario->guardarEquipoDesdeArray($payload, $idUsuarioSession);
            $resultados[] = [
                'fila'    => $numFilaExcel,
                'ok'      => true,
                'mensaje' => 'Equipo registrado correctamente (ID: ' . $idEquipo . ').',
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
