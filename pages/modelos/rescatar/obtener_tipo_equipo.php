<?php
ob_start(); // Inicia el buffer de salida

header('Content-Type: application/json');

// Habilitar reporte de errores para depuraci贸n
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../class/conexion.php';
require_once '../../class/funciones.php';


function convertirArrayUtf8($array) {
    if (!is_array($array)) {
        return $array;
    }

    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = convertirArrayUtf8($value);
        } else {
            $array[$key] = mb_convert_encoding($value, 'UTF-8', 'auto');
        }
    }

    return $array;
}



try {
    $funciones = new Funciones();
    $tiposDispositivos = $funciones->listarTiposDispositivos();

    // Convertir el array a UTF-8
    $tiposDispositivosUTF8 = convertirArrayUtf8($tiposDispositivos);
    

    if (!empty($tiposDispositivosUTF8)) {
        echo json_encode(['success' => true, 'tipos' => $tiposDispositivosUTF8]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se encontraron tipos de dispositivos']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

exit;