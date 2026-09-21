<?php
header('Content-Type: application/json; charset=utf-8');
include("../../../class/conexion.php");
date_default_timezone_set('America/Santiago');
require '../../../vendor/autoload.php'; // Librería QR instalada con Composer

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$db = new MySQL("seduc", "", "");
$response = ["success" => false, "message" => ""];

function generarQRCursos($idCurso) {
    $nombreArchivo = "Curso_{$idCurso}.png";
    $rutaArchivo = "../../../codigosQR/asistenciaCursos/{$nombreArchivo}";

    // URL del curso donde se escaneará el QR
    $url = "https://www.acceso.seduc.cl/leyendoCursoQR.php?id={$idCurso}";

    $options = new QROptions([
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel'   => QRCode::ECC_L,
        'scale'      => 5,
    ]);

    $qrCode = (new QRCode($options))->render($url);

    // Guardar el código QR en la carpeta
    if (file_put_contents($rutaArchivo, base64_decode(explode(',', $qrCode)[1])) === false) {  
        throw new Exception("Error al guardar el código QR.");
    }

    return $rutaArchivo;
}




if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case "crear_curso":
            $nombreTaller   = $db->escape_string(trim($_POST["nombreTaller"]));
            $fechaTaller    = $db->escape_string(trim($_POST["fechaTaller"]));
            $urlFormulario  = $db->escape_string(trim($_POST["urlFormulario"]));
            $idUsuario      = intval($_POST["idUsuario"]); // Asegurar que sea un número

            try {
                // Insertar curso y obtener ID
                $sqlInsert = "INSERT INTO curso_codigos_qr (nombre_taller, fecha_taller, url_formulario, generado_por, fecha_generacion, eliminado) 
                              VALUES ('$nombreTaller', '$fechaTaller', '$urlFormulario', '$idUsuario', NOW(),'no')";
                
                $resultado = $db->consulta($sqlInsert);

                if ($resultado) {
                    $idQR = $db->insert_id(); // Obtener el ID generado

                    // Generar QR y actualizar la tabla
                    $rutaQR = generarQRCursos($idQR);
                    $sqlUpdateQR = "UPDATE curso_codigos_qr SET qr_path = '$rutaQR' WHERE id_qr = $idQR";
                    $db->consulta($sqlUpdateQR);

                    $response["success"] = true;
                    $response["qr_path"] = $rutaQR;
                } else {
                    $response["message"] = "Error al guardar el curso.";
                }
            } catch (Exception $e) {
                $response["message"] = $e->getMessage();
            }
            break;

        case "actualizar_curso":
            $idQR           = intval($_POST["idQR"]);
            $nombreTaller   = $db->escape_string(trim($_POST["nombreTaller"]));
            $fechaTaller    = $db->escape_string(trim($_POST["fechaTaller"]));
            $urlFormulario  = $db->escape_string(trim($_POST["urlFormulario"]));

            try {
                // Generar nuevamente el QR
                $rutaQR = generarQRCursos($idQR);

                $sqlUpdate = "UPDATE curso_codigos_qr 
                              SET nombre_taller = '$nombreTaller', fecha_taller = '$fechaTaller', url_formulario = '$urlFormulario', qr_path = '$rutaQR'
                              WHERE id_qr = $idQR";

                $resultado = $db->consulta($sqlUpdate);

                if ($resultado) {
                    $response["success"] = true;
                } else {
                    $response["message"] = "Error al actualizar en la base de datos.";
                }
            } catch (Exception $e) {
                $response["message"] = $e->getMessage();
            }
            break;

        default:
            $response["message"] = "Acción no válida.";
            break;
    }
}

echo json_encode($response);