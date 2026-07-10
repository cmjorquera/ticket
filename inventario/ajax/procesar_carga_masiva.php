<?php
require_once dirname(__DIR__) . '/componentes/boot.php';

// PHPExcel genera avisos de deprecacion en PHP 8.x; suprimirlos para no corromper la respuesta JSON.
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

header('Content-Type: application/json; charset=utf-8');

function cm_responder($payload, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function cm_serie_limpia(string $numeroSerie): string
{
    return preg_replace('/[^A-Z0-9\-_.\/]/', '', strtoupper(preg_replace('/\s+/', ' ', trim($numeroSerie))));
}

function cm_payload_desde_datos(array $datos, int $idColegio, int $idUsuarioSession, int $idEstado, int $idUbicacion, int $idUsuarioAsignado): array
{
    $serieLimpia  = cm_serie_limpia($datos['numero_serie']);
    $nombreEquipo = 'PC-' . ($serieLimpia !== '' ? $serieLimpia : uniqid());

    $payload = [
        'equipo' => [
            'id_usuario'           => $idUsuarioSession,
            'id_colegio'           => $idColegio,
            'id_usuario_registra'  => $idUsuarioSession,
            'id_usuario_asignado'  => $idUsuarioAsignado,
            'id_ubicacion'         => $idUbicacion,
            'nombre_personalizado' => mb_substr($datos['nombre_personalizado'], 0, 150),
            'nombre_equipo'        => $nombreEquipo,
            'fabricante'           => $datos['fabricante'],
            'producto'             => $datos['producto'],
            'numero_serie'         => $datos['numero_serie'],
            'tipo_pc'              => $datos['tipo_pc'],
            'qr_code'              => $nombreEquipo,
            'id_estado'            => $idEstado,
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

    return $payload;
}

function cm_leer_filas_excel(string $tmpFile): array
{
    require_once dirname(__DIR__, 2) . '/excel/Classes/PHPExcel.php';

    $inputFileType = PHPExcel_IOFactory::identify($tmpFile);
    $reader        = PHPExcel_IOFactory::createReader($inputFileType);
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($tmpFile);
    $sheet       = $spreadsheet->getActiveSheet();
    $todasFilas  = $sheet->toArray(null, true, true, false);

    $columnaMap = [
        0  => 'id_ubicacion',
        1  => 'id_usuario_asignado',
        2  => 'nombre_personalizado',
        3  => 'numero_serie',
        4  => 'tipo_pc',
        5  => 'id_estado',
        6  => 'fabricante',
        7  => 'producto',
        8  => 'valor_equipo',
        9  => 'proveedor',
        10 => 'numero_factura',
        11 => 'fecha_compra',
        12 => 'observacion_compra',
        13 => 'almacenamiento_modelo',
        14 => 'almacenamiento_capacidad',
        15 => 'almacenamiento_tamano',
        16 => 'procesador_fabricante',
        17 => 'procesador_modelo',
        18 => 'procesador_velocidad',
        19 => 'windows',
        20 => 'office',
        21 => 'antivirus',
        22 => 'memoria_designacion',
        23 => 'memoria_formato',
        24 => 'memoria_tipo',
        25 => 'memoria_tamano',
        26 => 'memoria_frecuencia',
        27 => 'memoria_marca',
        28 => 'monitor_modelo',
        29 => 'monitor_codigo',
        30 => 'monitor_serie',
        31 => 'monitor_tamano',
        32 => 'monitor_resolucion',
    ];

    $filas = [];
    foreach (array_slice($todasFilas, 6) as $i => $fila) {
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

        $filas[] = [
            'fila' => $i + 7,
            'datos' => $datos,
        ];
    }

    return $filas;
}

function cm_validar_fila(Inventario $inventario, array $datos, int $idColegio): array
{
    $errores = [];
    $resueltos = [
        'id_estado' => 0,
        'id_ubicacion' => 0,
        'id_usuario_asignado' => 0,
    ];

    if ($datos['numero_serie'] === '') {
        $errores[] = 'Numero de serie es obligatorio.';
    }

    if ($datos['tipo_pc'] === '') {
        $errores[] = 'Tipo de PC es obligatorio.';
    } elseif (!$inventario->tipoPcActivo($datos['tipo_pc'])) {
        $errores[] = 'Tipo de PC no existe o no esta activo.';
    }

    $resueltos['id_estado'] = $inventario->resolverEstadoActivoDesdeExcel($datos['id_estado']);
    if ($resueltos['id_estado'] <= 0) {
        $errores[] = 'Estado es obligatorio y debe existir activo en estado_equipo.';
    }

    try {
        $resueltos['id_ubicacion'] = $inventario->resolverUbicacionCargaMasiva($idColegio, $datos['id_ubicacion'], false);
    } catch (Throwable $e) {
        $errores[] = $e->getMessage();
    }

    $resueltos['id_usuario_asignado'] = $inventario->resolverUsuarioAsignadoCargaMasiva($idColegio, $datos['id_usuario_asignado']);
    if ($resueltos['id_usuario_asignado'] < 0) {
        $errores[] = 'Usuario asignado no existe, no esta activo o no pertenece al colegio.';
    }

    return [
        'ok' => empty($errores),
        'errores' => $errores,
        'resueltos' => $resueltos,
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cm_responder(['ok' => false, 'mensaje' => 'Metodo no permitido.'], 405);
}

if ($idUsuarioSession <= 0) {
    cm_responder(['ok' => false, 'mensaje' => 'Sesion no activa.'], 401);
}

try {
    $accion = strtolower(trim((string)($_POST['accion'] ?? 'preview')));
    if (!in_array($accion, ['preview', 'insert'], true)) {
        throw new RuntimeException('Accion no valida.');
    }

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

    $filas = cm_leer_filas_excel($_FILES['archivo']['tmp_name']);

    if ($accion === 'preview') {
        $detalle = [];
        $validas = 0;
        $conError = 0;
        foreach ($filas as $fila) {
            $datos = $fila['datos'];
            $validacion = cm_validar_fila($inventario, $datos, $idColegio);
            $validacion['ok'] ? $validas++ : $conError++;
            $detalle[] = [
                'fila' => $fila['fila'],
                'ok' => $validacion['ok'],
                'seleccionable' => $validacion['ok'],
                'datos' => [
                    'nombre_personalizado' => $datos['nombre_personalizado'],
                    'numero_serie' => $datos['numero_serie'],
                    'tipo_pc' => $datos['tipo_pc'],
                    'estado' => $datos['id_estado'],
                    'ubicacion' => $datos['id_ubicacion'],
                    'usuario_asignado' => $datos['id_usuario_asignado'],
                    'fabricante' => $datos['fabricante'],
                    'producto' => $datos['producto'],
                    'valor_equipo' => $datos['valor_equipo'],
                ],
                'errores' => $validacion['errores'],
            ];
        }

        cm_responder([
            'ok' => true,
            'accion' => 'preview',
            'total' => count($detalle),
            'valid_count' => $validas,
            'error_count' => $conError,
            'detalle' => $detalle,
        ]);
    }

    $seleccionadasRaw = json_decode((string)($_POST['filas'] ?? '[]'), true);
    if (!is_array($seleccionadasRaw)) {
        $seleccionadasRaw = [];
    }
    $seleccionadas = array_values(array_unique(array_filter(array_map('intval', $seleccionadasRaw))));
    $seleccionadasSet = array_fill_keys($seleccionadas, true);

    $resultados = [];
    $insertados = 0;
    $rechazados = 0;
    $omitidos = 0;
    $duplicados = 0;
    $errUsuario = 0;
    $errUbicacion = 0;
    $errEstadoTipo = 0;

    foreach ($filas as $fila) {
        $numFilaExcel = (int)$fila['fila'];
        $datos = $fila['datos'];

        if (!isset($seleccionadasSet[$numFilaExcel])) {
            $omitidos++;
            $resultados[] = [
                'fila' => $numFilaExcel,
                'ok' => false,
                'omitida' => true,
                'mensaje' => 'Fila omitida por no estar seleccionada.',
            ];
            continue;
        }

        $validacion = cm_validar_fila($inventario, $datos, $idColegio);
        if (!$validacion['ok']) {
            $rechazados++;
            $mensaje = implode(' ', $validacion['errores']);
            if (stripos($mensaje, 'usuario') !== false) { $errUsuario++; }
            if (stripos($mensaje, 'ubicacion') !== false) { $errUbicacion++; }
            if (stripos($mensaje, 'estado') !== false || stripos($mensaje, 'tipo') !== false) { $errEstadoTipo++; }
            $resultados[] = [
                'fila' => $numFilaExcel,
                'ok' => false,
                'mensaje' => $mensaje,
            ];
            continue;
        }

        try {
            $idUbicacion = $inventario->resolverUbicacionCargaMasiva($idColegio, $datos['id_ubicacion'], true);
            $payload = cm_payload_desde_datos(
                $datos,
                $idColegio,
                $idUsuarioSession,
                (int)$validacion['resueltos']['id_estado'],
                $idUbicacion,
                (int)$validacion['resueltos']['id_usuario_asignado']
            );
            $idEquipo = $inventario->guardarEquipoDesdeArray($payload, $idUsuarioSession);
            $insertados++;
            $resultados[] = [
                'fila' => $numFilaExcel,
                'ok' => true,
                'mensaje' => 'Equipo registrado correctamente (ID: ' . $idEquipo . ').',
            ];
        } catch (Throwable $e) {
            $rechazados++;
            $mensaje = $e->getMessage();
            if (stripos($mensaje, 'serie ya existe') !== false) { $duplicados++; }
            if (stripos($mensaje, 'usuario') !== false) { $errUsuario++; }
            if (stripos($mensaje, 'ubicacion') !== false) { $errUbicacion++; }
            if (stripos($mensaje, 'estado') !== false || stripos($mensaje, 'tipo') !== false) { $errEstadoTipo++; }
            $resultados[] = [
                'fila' => $numFilaExcel,
                'ok' => false,
                'mensaje' => $mensaje,
            ];
        }
    }

    cm_responder([
        'ok' => true,
        'accion' => 'insert',
        'total' => count($resultados),
        'insertados' => $insertados,
        'omitidos' => $omitidos,
        'rechazados' => $rechazados,
        'duplicados' => $duplicados,
        'errores_usuario' => $errUsuario,
        'errores_ubicacion' => $errUbicacion,
        'errores_estado_tipo' => $errEstadoTipo,
        'detalle' => $resultados,
    ]);
} catch (Throwable $e) {
    cm_responder([
        'ok' => false,
        'mensaje' => $e->getMessage(),
    ], 500);
}
