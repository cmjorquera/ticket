<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');
require '../../vendor/autoload.php'; // Librería QR instalada con Composer

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);




function generarQR($idDispositivo, $asignado) {
    $nombreAsignado = $asignado ? "_{$asignado}" : "Sin_asignar";
    $nombreArchivo = "{$idDispositivo}{$nombreAsignado}.png";
    $rutaArchivo = "../../codigosQR/equipos/{$nombreArchivo}";


    // $url = "https://127.0.0.1/ticket/bitacora.php?id={$idDispositivo}";
    $url    = "https://www.acceso.seduc.cl/leyendoDispositivoQR.php?id={$idDispositivo}";

    $options = new QROptions([
        'outputType' => 'png', // Correcto para la versión actual
        'eccLevel'   => 1,     // Nivel de corrección como entero
        'scale'      => 5,
    ]);

    $qrCode = (new QRCode($options))->render($url);

    // Decodificar el Base64 y guardar como archivo PNG
    if (file_put_contents($rutaArchivo, base64_decode(explode(',', $qrCode)[1])) === false) {  
        throw new Exception("Error al guardar el archivo QR.");
    }

    return $rutaArchivo;
}



try {
    // Crear conexión a la base de datos
    $db = new MySQL("", "", "");

    // Leer los datos enviados desde el frontend
    $input = json_decode(file_get_contents("php://input"), true);

    // Validar campos obligatorios
    if (empty($input['tipo']) ) {
        echo json_encode(['success' => false, 'error' => 'Campos obligatorios faltantes.']);
        exit;
    }

    // Escapar y procesar los datos
    $tipo           = !empty($input['tipo'])                ? $db->escape_string($input['tipo']) : null;
    $marca          = !empty($input['marca'])               ? $db->escape_string($input['marca']) : null;
    $modelo         = !empty($input['modelo'])              ? $db->escape_string($input['modelo']) : null;
    $asignado       = !empty($input['asignado'])            ? $db->escape_string($input['asignado']) : null;
    $n_serie        = !empty($input['n_serie'])             ? $db->escape_string($input['n_serie']) : null;
    $precio         = !empty($input['precio'])              ? (int) str_replace('.', '', $input['precio']) : null;
    $fecha_compra   = !empty($input['fecha_compra'])        ? $db->escape_string($input['fecha_compra']) : null;
    $observaciones  = !empty($input['observaciones'])       ? $db->escape_string($input['observaciones']) : null;
    $proveedor      = !empty($input['proveedor'])           ? $db->escape_string($input['proveedor']) : null;
    $n_factura      = !empty($input['numero_factura'])      ? $db->escape_string($input['numero_factura']) : null;


    // Insertar el dispositivo en la base de datos
    $query = "INSERT INTO otros_dispositivos (tipo, marca, modelo, n_serie,n_factura, asignado, proveedor, precio, fecha_compra, observaciones, qr_code) 
              VALUES ('$tipo', '$marca', '$modelo', '$n_serie','$n_factura', '$asignado', '$proveedor', '$precio', '$fecha_compra', '$observaciones', NULL)";
    if (!$db->consulta($query)) {
        echo json_encode(['success' => false, 'error' => 'Error al insertar el dispositivo en la base de datos.']);
        exit;
    }

    // Obtener el ID del nuevo dispositivo
    $id_dispositivo = $db->insert_id();

    // Generar y guardar el código QR
    $qr_path = generarQR($id_dispositivo, $asignado);

    // Actualizar la base de datos con la ruta del código QR
    $qr_code_db_path = $db->escape_string(str_replace("../../", "", $qr_path));
    $update_query = "UPDATE otros_dispositivos SET qr_code = '$qr_code_db_path' WHERE id_dispositivo = $id_dispositivo";
    if (!$db->consulta($update_query)) {
        echo json_encode(['success' => false, 'error' => 'Error al actualizar la ruta del QR en la base de datos.']);
        exit;
    }

    // Respuesta de éxito
    echo json_encode(['success' => true, 'message' => 'Dispositivo guardado correctamente.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
