<?php
session_start();
require_once '../../class/conexion.php';
require_once '../../class/config.php'; // Archivo con la clave de encriptación

$db = new MySQL("", "", "");

// Función para cifrar datos con AES-256-CBC
function encryptData($data) {
    if ($data === null) {
        return null; // o return '';
    }
    return openssl_encrypt($data, 'AES-256-CBC', ENCRYPTION_KEY, 0, ENCRYPTION_IV);
}
// Validar que se recibe una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anonima            = isset($_POST['anonima']) ? 1 : 0;
    $contactar          = isset($_POST['contactar']) ? 1 : 0;
    $confidencial       = isset($_POST['confidencial']) ? 1 : 0;
    $fecha_incidente    = $_POST['fecha_incidente'] ?? null;
    $involucrado        = $_POST['involucrado'] ?? null;
    $descripcion        = $_POST['descripcion'] ?? null;


    // echo $anonima."*********";
    // die();

    // Si es denuncia anónima, forzamos los datos del denunciado a ser nulos o vacíos
    if ($anonima == 1) {
        $id_usuario = null;
        $nombre = '';
        $cargo = '';
    } else {
        // Si no es anónima se asignan valores de sesión o recibidos por POST
        $id_usuario = $_SESSION['id'] ?? null;
        $nombre = $_POST['nombre'] ?? '';
        $cargo = $_POST['cargo'] ?? '';
    }

    // Validación básica de campos obligatorios
    if (!$fecha_incidente || !$descripcion) {
        echo json_encode(['status' => 'error', 'msg' => 'Faltan campos obligatorios']);
        exit;
    }

    // Cifrar los datos sensibles para garantizar la confidencialidad
    $nombre_encriptado = encryptData($nombre);
    $cargo_encriptado = encryptData($cargo);
    $involucrado_encriptado = encryptData($involucrado);
    $descripcion_encriptado = encryptData($descripcion);

    // Manejo del archivo (si se ha subido uno)
    $archivoNombreFinal = null;
    if (!empty($_FILES['archivo']['name'])) {
        $archivoTmp = $_FILES['archivo']['tmp_name'];
        $archivoNombre = basename($_FILES['archivo']['name']);
        $directorioDestino = '../../archivos_denuncias/';

        if (!file_exists($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }

        $archivoNombreFinal = uniqid() . "_" . preg_replace('/[^a-zA-Z0-9_.]/', '_', $archivoNombre);
        $rutaArchivo = $directorioDestino . $archivoNombreFinal;

        if (!move_uploaded_file($archivoTmp, $rutaArchivo)) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al subir el archivo']);
            exit;
        }
    }

    // Construir la consulta SQL usando escape_string de la clase MySQL
    $sql = "INSERT INTO denuncias_acoso 
        (id_usuario, nombre, cargo, fecha_incidente, involucrado, descripcion, archivo, anonima, contactar, confidencial) VALUES (";

    $sql .= ($id_usuario ? $id_usuario : "NULL") . ", ";
    $sql .= "'" . $db->escape_string($nombre_encriptado) . "', ";
    $sql .= "'" . $db->escape_string($cargo_encriptado) . "', ";
    $sql .= "'" . $db->escape_string($fecha_incidente) . "', ";
    $sql .= "'" . $db->escape_string($involucrado_encriptado) . "', ";
    $sql .= "'" . $db->escape_string($descripcion_encriptado) . "', ";
    $sql .= ($archivoNombreFinal ? "'" . $db->escape_string($archivoNombreFinal) . "'" : "NULL") . ", ";
    $sql .= $anonima . ", " . $contactar . ", " . $confidencial . ")";

    // Ejecutamos la consulta usando el método guardar de la clase MySQL
    $errorCode = $db->guardar($sql);

    if ($errorCode === 0) {
        echo json_encode(['status' => 'ok', 'msg' => 'Denuncia enviada correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Error al guardar en la base de datos']);
    }
    
    // No se utiliza $db->close() ya que en la clase se dispone de CerrarConexion()
    $db->CerrarConexion();
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Acceso no permitido']);
}
?>
