<?php
require_once dirname(__DIR__) . '/componentes/boot.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json; charset=utf-8');

function inventario_normalizar_texto_masivo($valor)
{
    $valor = trim((string)$valor);
    if ($valor === '') {
        return '';
    }
    if (function_exists('iconv')) {
        $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor);
        if ($convertido !== false) {
            $valor = $convertido;
        }
    }
    $valor = strtolower($valor);
    $valor = preg_replace('/[^a-z0-9]+/', '_', $valor);
    return trim((string)$valor, '_');
}

function inventario_alias_columna_masiva($columna)
{
    static $aliases = [
        'colegio' => 'colegio',
        'id_colegio' => 'colegio',
        'nombre_herramienta' => 'nombre_herramienta',
        'herramienta' => 'nombre_herramienta',
        'nombre' => 'nombre_herramienta',
        'numero_serie' => 'numero_serie',
        'n_serie' => 'numero_serie',
        'serie' => 'numero_serie',
        'serial' => 'numero_serie',
        'categoria' => 'categoria',
        'marca' => 'marca',
        'modelo' => 'modelo',
        'cantidad' => 'cantidad',
        'stock_minimo' => 'stock_minimo',
        'stock_min' => 'stock_minimo',
        'ubicacion' => 'ubicacion',
        'ubicacion_fisica' => 'ubicacion',
        'estado' => 'estado',
        'id_estado' => 'estado',
        'usuario_asignado' => 'usuario_asignado',
        'responsable' => 'usuario_asignado',
        'responsable_actual' => 'usuario_asignado',
        'asignado' => 'usuario_asignado',
        'qr' => 'qr_code',
        'qr_code' => 'qr_code',
        'codigo_qr' => 'qr_code',
        'codigo' => 'qr_code',
        'observaciones' => 'observaciones',
        'observacion' => 'observaciones',
        'tipo_energia' => 'tipo_energia',
        'medida' => 'medida',
        'capacidad' => 'capacidad',
        'requiere_mantencion' => 'requiere_mantencion',
        'frecuencia_mantencion_dias' => 'frecuencia_mantencion_dias',
        'frecuencia_mantencion' => 'frecuencia_mantencion_dias',
        'fecha_ultima_mantencion' => 'fecha_ultima_mantencion',
        'fecha_proxima_mantencion' => 'fecha_proxima_mantencion',
        'garantia_hasta' => 'garantia_hasta',
        'valor' => 'valor_herramienta',
        'valor_herramienta' => 'valor_herramienta',
        'proveedor' => 'proveedor',
        'numero_factura' => 'numero_factura',
        'factura' => 'numero_factura',
        'fecha_compra' => 'fecha_compra',
        'observacion_compra' => 'observacion_compra',
        'detalle_compra' => 'observacion_compra',
    ];
    return $aliases[$columna] ?? $columna;
}

function inventario_resolver_id_masivo($valor, $mapaPorId, $mapaPorNombre)
{
    $valor = trim((string)$valor);
    if ($valor === '') {
        return 0;
    }
    if (ctype_digit($valor) && isset($mapaPorId[(int)$valor])) {
        return (int)$valor;
    }
    $normalizado = inventario_normalizar_texto_masivo($valor);
    return isset($mapaPorNombre[$normalizado]) ? (int)$mapaPorNombre[$normalizado] : 0;
}

function inventario_normalizar_booleano_masivo($valor)
{
    $valor = inventario_normalizar_texto_masivo($valor);
    return in_array($valor, ['1', 'si', 'sí', 'true', 'x', 'yes'], true) ? 1 : 0;
}

function inventario_normalizar_numero_masivo($valor, $default = 0)
{
    $valor = trim((string)$valor);
    if ($valor === '') {
        return $default;
    }
    $valor = str_replace(['$', ' '], '', $valor);
    if (strpos($valor, ',') !== false && strpos($valor, '.') !== false) {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    } else {
        $valor = str_replace(',', '.', $valor);
    }
    return is_numeric($valor) ? (float)$valor : $default;
}

function inventario_normalizar_fecha_masiva($valor)
{
    $valor = trim((string)$valor);
    if ($valor === '') {
        return '';
    }
    $valor = str_replace('.', '/', $valor);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
        return $valor;
    }
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $valor, $m)) {
        return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
    }
    $timestamp = strtotime($valor);
    return $timestamp ? date('Y-m-d', $timestamp) : '';
}

try {
    if (!isset($_FILES['archivo_excel']) || !is_uploaded_file($_FILES['archivo_excel']['tmp_name'])) {
        throw new RuntimeException('Debes seleccionar un archivo Excel valido.');
    }

    $spreadsheet = IOFactory::load($_FILES['archivo_excel']['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray('', true, true, false);
    if (count($rows) < 2) {
        throw new RuntimeException('El archivo no tiene filas de datos para importar.');
    }

    $headers = array_shift($rows);
    $headerMap = [];
    foreach ($headers as $index => $header) {
        $normalizado = inventario_alias_columna_masiva(inventario_normalizar_texto_masivo($header));
        if ($normalizado !== '') {
            $headerMap[$index] = $normalizado;
        }
    }

    if (empty($headerMap)) {
        throw new RuntimeException('No fue posible reconocer las columnas del archivo.');
    }

    $colegios = $inventario->obtenerColegios();
    $usuarios = $inventario->obtenerUsuarios();
    $estados = $inventario->obtenerEstados();

    $mapaColegiosId = [];
    $mapaColegiosNombre = [];
    foreach ($colegios as $colegio) {
        $mapaColegiosId[(int)$colegio['id_colegio']] = $colegio['nom_colegio'];
        $mapaColegiosNombre[inventario_normalizar_texto_masivo($colegio['nom_colegio'])] = (int)$colegio['id_colegio'];
    }

    $mapaUsuariosId = [0 => 'Sin asignar'];
    $mapaUsuariosNombre = [];
    foreach ($usuarios as $usuario) {
        $id = (int)$usuario['id'];
        $nombre = trim((string)$usuario['nombre_completo']);
        $mapaUsuariosId[$id] = $nombre;
        $mapaUsuariosNombre[inventario_normalizar_texto_masivo($nombre)] = $id;
    }

    $mapaEstadosId = [];
    $mapaEstadosNombre = [];
    foreach ($estados as $estado) {
        $id = (int)$estado['id_estado'];
        $nombre = trim((string)$estado['nombre_estado']);
        $mapaEstadosId[$id] = $nombre;
        $mapaEstadosNombre[inventario_normalizar_texto_masivo($nombre)] = $id;
    }

    $filasPreview = [];
    $filasValidas = [];
    $seriesArchivo = [];

    foreach ($rows as $rowIndex => $row) {
        $fila = [];
        foreach ($headerMap as $index => $nombreColumna) {
            $fila[$nombreColumna] = isset($row[$index]) ? trim((string)$row[$index]) : '';
        }

        $tieneContenido = false;
        foreach ($fila as $valor) {
            if (trim((string)$valor) !== '') {
                $tieneContenido = true;
                break;
            }
        }
        if (!$tieneContenido) {
            continue;
        }

        $errores = [];
        $idColegio = inventario_resolver_id_masivo($fila['colegio'] ?? '', $mapaColegiosId, $mapaColegiosNombre);
        if ($idColegio <= 0) {
            $errores[] = 'Colegio no reconocido.';
        }

        $nombreHerramienta = trim((string)($fila['nombre_herramienta'] ?? ''));
        if ($nombreHerramienta === '') {
            $errores[] = 'Falta nombre_herramienta.';
        }

        $numeroSerie = trim((string)($fila['numero_serie'] ?? ''));
        if ($numeroSerie === '') {
            $errores[] = 'Falta numero_serie.';
        } else {
            $serieNormalizada = inventario_normalizar_texto_masivo($numeroSerie);
            if (isset($seriesArchivo[$serieNormalizada])) {
                $errores[] = 'Numero de serie duplicado dentro del archivo.';
            }
            if ($inventario->validarSerieDuplicada($numeroSerie)) {
                $errores[] = 'Numero de serie ya existe en la base de datos.';
            }
        }

        $categoria = trim((string)($fila['categoria'] ?? ''));
        if ($categoria === '') {
            $errores[] = 'Falta categoria.';
        }

        $idEstado = 1;
        if (trim((string)($fila['estado'] ?? '')) !== '') {
            $idEstado = inventario_resolver_id_masivo($fila['estado'], $mapaEstadosId, $mapaEstadosNombre);
            if ($idEstado <= 0) {
                $errores[] = 'Estado no reconocido.';
            }
        }

        $idUsuarioAsignado = 0;
        if (trim((string)($fila['usuario_asignado'] ?? '')) !== '') {
            $idUsuarioAsignado = inventario_resolver_id_masivo($fila['usuario_asignado'], $mapaUsuariosId, $mapaUsuariosNombre);
            if ($idUsuarioAsignado <= 0) {
                $errores[] = 'Usuario asignado no reconocido.';
            }
        }

        $cantidad = max(1, (int)inventario_normalizar_numero_masivo($fila['cantidad'] ?? '', 1));
        $stockMinimo = max(0, (int)inventario_normalizar_numero_masivo($fila['stock_minimo'] ?? '', 0));

        $payload = [
            'id_colegio' => $idColegio,
            'id_usuario_asignado' => $idUsuarioAsignado,
            'nombre_herramienta' => $nombreHerramienta,
            'marca' => trim((string)($fila['marca'] ?? '')),
            'modelo' => trim((string)($fila['modelo'] ?? '')),
            'numero_serie' => $numeroSerie,
            'categoria' => $categoria,
            'qr_code' => trim((string)($fila['qr_code'] ?? '')),
            'id_estado' => $idEstado > 0 ? $idEstado : 1,
            'cantidad' => $cantidad,
            'stock_minimo' => $stockMinimo,
            'ubicacion' => trim((string)($fila['ubicacion'] ?? '')),
            'observaciones' => trim((string)($fila['observaciones'] ?? '')),
            'tipo_energia' => trim((string)($fila['tipo_energia'] ?? '')),
            'medida' => trim((string)($fila['medida'] ?? '')),
            'capacidad' => trim((string)($fila['capacidad'] ?? '')),
            'requiere_mantencion' => inventario_normalizar_booleano_masivo($fila['requiere_mantencion'] ?? ''),
            'frecuencia_mantencion_dias' => max(0, (int)inventario_normalizar_numero_masivo($fila['frecuencia_mantencion_dias'] ?? '', 0)),
            'fecha_ultima_mantencion' => inventario_normalizar_fecha_masiva($fila['fecha_ultima_mantencion'] ?? ''),
            'fecha_proxima_mantencion' => inventario_normalizar_fecha_masiva($fila['fecha_proxima_mantencion'] ?? ''),
            'garantia_hasta' => inventario_normalizar_fecha_masiva($fila['garantia_hasta'] ?? ''),
            'valor_herramienta' => inventario_normalizar_numero_masivo($fila['valor_herramienta'] ?? '', 0),
            'proveedor' => trim((string)($fila['proveedor'] ?? '')),
            'numero_factura' => trim((string)($fila['numero_factura'] ?? '')),
            'fecha_compra' => inventario_normalizar_fecha_masiva($fila['fecha_compra'] ?? ''),
            'observacion_compra' => trim((string)($fila['observacion_compra'] ?? '')),
        ];

        $filaPreview = [
            'linea' => $rowIndex + 2,
            'colegio' => $idColegio > 0 ? ($mapaColegiosId[$idColegio] ?? ($fila['colegio'] ?? '')) : ($fila['colegio'] ?? ''),
            'nombre_herramienta' => $nombreHerramienta,
            'numero_serie' => $numeroSerie,
            'categoria' => $categoria,
            'cantidad' => $cantidad,
            'estado' => $payload['id_estado'] > 0 ? ($mapaEstadosId[$payload['id_estado']] ?? '') : '',
            'errores' => $errores,
            'ok' => empty($errores),
        ];
        $filasPreview[] = $filaPreview;

        if (empty($errores)) {
            $seriesArchivo[inventario_normalizar_texto_masivo($numeroSerie)] = true;
            $filasValidas[] = $payload;
        }
    }

    echo json_encode([
        'ok' => true,
        'resumen' => [
            'total' => count($filasPreview),
            'validas' => count($filasValidas),
            'invalidas' => count($filasPreview) - count($filasValidas),
        ],
        'filas_preview' => $filasPreview,
        'filas_validas' => $filasValidas,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
