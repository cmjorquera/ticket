<?php
require_once dirname(__DIR__) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $filas = json_decode((string)($_POST['filas'] ?? '[]'), true);
    if (!is_array($filas) || empty($filas)) {
        throw new RuntimeException('No hay filas validas para insertar.');
    }

    $insertadas = 0;
    $errores = [];

    foreach ($filas as $indice => $fila) {
        if (!is_array($fila)) {
            $errores[] = 'La fila ' . ($indice + 1) . ' no tiene un formato valido.';
            continue;
        }
        try {
            $inventario->guardarHerramienta($fila, [], $idUsuarioSession);
            $insertadas++;
        } catch (Throwable $e) {
            $errores[] = 'Fila ' . ($indice + 1) . ': ' . $e->getMessage();
        }
    }

    if ($insertadas === 0) {
        throw new RuntimeException('No se insertaron herramientas. ' . implode(' | ', $errores));
    }

    $mensaje = 'Se insertaron ' . $insertadas . ' herramienta(s) correctamente.';
    if (!empty($errores)) {
        $mensaje .= ' Algunas filas no se cargaron: ' . implode(' | ', $errores);
    }

    echo json_encode([
        'ok' => true,
        'mensaje' => $mensaje,
        'insertadas' => $insertadas,
        'errores' => $errores,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
