<?php
header('Content-Type: application/json');
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
    $rutaArchivo = "../../codigosQR/computadores/{$nombreArchivo}";

    // $url = "https://127.0.0.1/ticket/bitacora.php?id={$idDispositivo}";
    // $url    = "http://20.1.1.170/ticket/leyendoEquipoQR.php?id={$idDispositivo}";
    $url    = "https://www.acceso.seduc.cl/leyendoEquipoQR.php?id={$idDispositivo}";


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




$bdato = new MySQL('', '', ''); 
//********************************************************************** 
// Decodificar los datos JSON enviados
$data = json_decode(file_get_contents('php://input'), true);
//**********************************************************************

// Variables para controlar el estado de las inserciones
$resultadoQR = $resultadoEquiposCompra = $resultadoAlmacenamiento = $resultadoMemoria1 = $resultadoMemoria2 = $resultadoProcesador = $resultadoSoftware = $resultadoMonitor1 = $resultadoMonitor2 = false;

try {
    // Primera inserción: Tabla equipos
    $nombreEquipo       = isset($data['nombre_equipo'])           ? "'" . $bdato->escape_string($data['nombre_equipo']) . "'"   : 'NULL';
    $fabricante         = isset($data['fabricante'])              ? "'" . $bdato->escape_string($data['fabricante']) . "'"      : 'NULL';
    $producto           = isset($data['producto'])                ? "'" . $bdato->escape_string($data['producto']) . "'"        : 'NULL';
    $numeroSerie        = isset($data['numero_serie'])            ? "'" . $bdato->escape_string($data['numero_serie']) . "'"    : 'NULL';
    $tipoPc             = isset($data['tipo_pc'])                 ? "'" . $bdato->escape_string($data['tipo_pc']) . "'"         : 'NULL';
    $idUsuario          = isset($data['usuarios_select'])         ? "'" . $bdato->escape_string($data['usuarios_select']) . "'" : 'NULL';


    $sqlInsertEquipos = "
        INSERT INTO equipos (id_usuario, nombre_equipo, fabricante, producto, numero_serie, tipo_pc)
        VALUES ($idUsuario, $nombreEquipo, $fabricante, $producto, $numeroSerie, $tipoPc)
    ";
    $resultado = $bdato->consulta($sqlInsertEquipos);

    if ($resultado) {
        // Rescatar el ID del equipo
        $idEquipo = $bdato->insert_id();

        // Generar QR y actualizar la tabla equipos
        $rutaQR = generarQR($idEquipo, $data['usuarios_select'] ?? '_Sin_asignar');
        $sqlUpdateQR = "
            UPDATE equipos
            SET qr_code = '" . $bdato->escape_string($rutaQR) . "'
            WHERE id_equipo = $idEquipo
        ";
        $resultadoQR = $bdato->consulta($sqlUpdateQR);
    }
//*************************************************************************************************************************************************
//*************************************************EQUIPOS-COMPRA************************************************************
//*************************************************************************************************************************************************
$valorEquipo        = isset($data['valor_equipo']) ? (int) str_replace('.', '', $data['valor_equipo']) : null;
$proveedor          = isset($data['proveedor'])                    ? "'" . $bdato->escape_string($data['proveedor']) . "'"         : 'NULL';
$numeroFactura      = isset($data['numero_factura'])               ? "'" . $bdato->escape_string($data['numero_factura']) . "'"    : 'NULL';
$observacion        = isset($data['observacion'])                  ? "'" . $bdato->escape_string($data['observacion']) . "'"       : 'NULL';
$fechaCompra        = isset($data['fecha_compra_equipo'])          ? "'" . $bdato->escape_string($data['fecha_compra_equipo']) . "'"      : '0000-00-00';
// $valorEquipo  ="20000";

$sqlInsertEquiposCompra = "
    INSERT INTO equipos_compra (id_equipo, valor_equipo, proveedor, numero_factura, fecha_compra, observacion)
    VALUES ($idEquipo, $valorEquipo, $proveedor, $numeroFactura,  $fechaCompra,  $observacion)
";
$resultadoEquiposCompra = $bdato->consulta($sqlInsertEquiposCompra);


//*************************************************************************************************************************************************
//*************************************************************EQUIPO-PRCESADOR************************************************************************************
//************************************************************************************************************************************************

// Sexta inserción: Tabla equipo_procesador
$fabricanteProcesador   = isset($data['fabricante_procesador'])     ? "'" . $bdato->escape_string($data['fabricante_procesador']) . "'"         : 'NULL';
$modeloProcesador       = isset($data['modelo_procesador'])         ? "'" . $bdato->escape_string($data['modelo_procesador']) . "'"             : 'NULL';
$frecuenciaProcesador   = isset($data['frecuencia_procesador'])     ? "'" . $bdato->escape_string($data['frecuencia_procesador']) . "'"         : 'NULL';

$sqlInsertProcesador = "
    INSERT INTO equipo_procesador (id_equipo, equipo_fabricante, equipo_modelo, equipo_velocidad)
    VALUES ($idEquipo, $fabricanteProcesador, $modeloProcesador, $frecuenciaProcesador)
";
$resultadoProcesador = $bdato->consulta($sqlInsertProcesador);



//*************************************************************************************************************************************************
//******************************************************EQUIPO_SOFWARE**************************************************************************
//************************************************************************************************************************************************

$windows        = isset($data['windows'])       ? "'" . $bdato->escape_string($data['windows']) . "'"       : 'NULL';
$office         = isset($data['office'])        ? "'" . $bdato->escape_string($data['office']) . "'"        : 'NULL';
$antiVirus      = isset($data['antiVirus'])     ? "'" . $bdato->escape_string($data['antiVirus']) . "'"     : 'NULL';

$sqlInsertSoftware = "
    INSERT INTO equipo_software (id_equipo, windows, office, antivirus)
    VALUES ($idEquipo, $windows, $office, $antiVirus)
";
$resultadoSoftware = $bdato->consulta($sqlInsertSoftware);


//*************************************************************************************************************************************************
//******************************************************EQUIPO_ALMACENAMINETO**************************************************************************
//************************************************************************************************************************************************

// Obtener los datos enviados desde el formulario
$nombreAlmacenamiento       = isset($data['nombre_almacenamiento'])       ? "'" . $bdato->escape_string($data['nombre_almacenamiento']) . "'"      : 'NULL';
$capacidadAlmacenamiento    = isset($data['capacidad_almacenamiento'])    ? "'" . $bdato->escape_string($data['capacidad_almacenamiento']) . "'"   : 'NULL';
$tipoAlmacenamiento         = isset($data['tipo_almacenamiento'])         ? "'" . $bdato->escape_string($data['tipo_almacenamiento']) . "'"        : 'NULL';

// Construir la consulta dinámica


$sqlInsertAlmacenamiento = "
    INSERT INTO equipo_almacenamiento (id_equipo, equipo_modelo, equipo_capacidad, equipo_tamano)
    VALUES ($idEquipo, $nombreAlmacenamiento, $capacidadAlmacenamiento, $tipoAlmacenamiento)
";
$resultadoAlmacenamiento = $bdato->consulta($sqlInsertAlmacenamiento);



//*************************************************************************************************************************************************
//****************************************************EQUIPOS-MONITOR********************************************************************
//*************************************************************************************************************************************************



// Inserción para el primer monitor, si los datos están presentes

    $codigoMonitor1       = isset($data['monitor_1_codigo'])         ? "'" . $bdato->escape_string($data['monitor_1_codigo']) . "'"          : 'NULL';
    $modeloMonitor1      = isset($data['monitor_1_modelo'])         ? "'" . $bdato->escape_string($data['monitor_1_modelo']) . "'"          : 'NULL';
    $serieMonitor1       = isset($data['monitor_1_numero_serie'])   ? "'" . $bdato->escape_string($data['monitor_1_numero_serie']) . "'"    : 'NULL';
    $tamanoMonitor1      = isset($data['monitor_1_tamano'])         ? "'" . $bdato->escape_string($data['monitor_1_tamano']) . "'"          : 'NULL';
    $resolucionMonitor1  = isset($data['monitor_1_resolucion'])     ? "'" . $bdato->escape_string($data['monitor_1_resolucion']) . "'"      : 'NULL';
    $ordenMonitor1       = 1;

    // SQL para insertar el primer monitor
    $sqlInsertMonitor1 = "
        INSERT INTO equipo_monitor (id_equipo, modelo_monitor, codigo_monitor, serie_monitor, tamano_monitor, resolucion_monitor, orden_monitor)
        VALUES ($idEquipo, $modeloMonitor1, $codigoMonitor1, $serieMonitor1, $tamanoMonitor1, $resolucionMonitor1, $ordenMonitor1)
    ";
    $resultadoMonitor1 = $bdato->consulta($sqlInsertMonitor1);


// Inserción para el segundo monitor, si los datos están presentes
  

    $codigoMonitor2      = isset($data['monitor_2_codigo'])        ? "'" . $bdato->escape_string($data['monitor_2_codigo']) . "'"        : 'NULL';
    $modeloMonitor2      = isset($data['monitor_2_modelo'])        ? "'" . $bdato->escape_string($data['monitor_2_modelo']) . "'"        : 'NULL';
    $serieMonitor2       = isset($data['monitor_2_numero_serie'])  ? "'" . $bdato->escape_string($data['monitor_2_numero_serie']) . "'"  : 'NULL';
    $tamanoMonitor2      = isset($data['monitor_2_tamano'])        ? "'" . $bdato->escape_string($data['monitor_2_tamano']) . "'"        : 'NULL';
    $resolucionMonitor2  = isset($data['monitor_2_resolucion'])    ? "'" . $bdato->escape_string($data['monitor_2_resolucion']) . "'"    : 'NULL';
    $ordenMonitor2       = 2;

    // SQL para insertar el segundo monitor
    $sqlInsertMonitor2 = "
        INSERT INTO equipo_monitor (id_equipo, modelo_monitor, codigo_monitor, serie_monitor, tamano_monitor, resolucion_monitor, orden_monitor)
        VALUES ($idEquipo, $modeloMonitor2, $codigoMonitor2, $serieMonitor2, $tamanoMonitor2, $resolucionMonitor2, $ordenMonitor2)
    ";
    $resultadoMonitor2 = $bdato->consulta($sqlInsertMonitor2);



//*************************************************************************************************************************************************
//****************************************************EQUIPOS-MEMORIA********************************************************************
//*************************************************************************************************************************************************

$cantidadMemorias = count(array_filter($data, function ($key) {
    return preg_match('/^designacion_memoria\d+$/', $key);
}, ARRAY_FILTER_USE_KEY));

$erroresMemorias = [];

// Iterar a través de todas las pestañas de memoria detectadas
for ($i = 1; $i <= $cantidadMemorias; $i++) {
    // Construir los nombres de las variables dinámicamente
    $designacionMemoria = isset($data["designacion_memoria$i"]) ? "'" . $bdato->escape_string($data["designacion_memoria$i"]) . "'" : 'NULL';
    $formatoMemoria     = isset($data["formato_memoria$i"])     ? "'" . $bdato->escape_string($data["formato_memoria$i"]) . "'"     : 'NULL';
    $tipoMemoria        = isset($data["tipo_memoria$i"])        ? "'" . $bdato->escape_string($data["tipo_memoria$i"]) . "'"        : 'NULL';
    $tamanoMemoria      = isset($data["tamano_memoria$i"])      ? "'" . $bdato->escape_string($data["tamano_memoria$i"]) . "'"      : 'NULL';
    $frecuenciaMemoria  = isset($data["frecuencia_memoria$i"])  ? "'" . $bdato->escape_string($data["frecuencia_memoria$i"]) . "'"  : 'NULL';
    $marcaMemoria       = isset($data["marca_memoria$i"])       ? "'" . $bdato->escape_string($data["marca_memoria$i"]) . "'"       : 'NULL';
    $ordenMemoria       = $i;

    // Generar la consulta SQL para insertar cada módulo de memoria
    $sqlInsertMemoria = "
        INSERT INTO equipo_memoria (id_equipo, designacion_memoria, formato_memoria, tipo_memoria, tamano_memoria, frecuencia_memoria, marca_memoria, orden_memoria)
        VALUES ($idEquipo, $designacionMemoria, $formatoMemoria, $tipoMemoria, $tamanoMemoria, $frecuenciaMemoria, $marcaMemoria, $ordenMemoria)
    ";

    // Ejecutar la consulta
    $resultadoMemoria = $bdato->consulta($sqlInsertMemoria);

    // Validar si la inserción fue exitosa
    if (!$resultadoMemoria) {
        $erroresMemorias[] = "Error en Memoria $i.";
    }
}

// Comprobar si hubo errores en alguna memoria
if (!empty($erroresMemorias)) {
    throw new Exception("Errores detectados en las memorias: " . implode(', ', $erroresMemorias));
}

//*************************************************************************************************************************************************
//****************************************** VALIDACIÓN FINAL DE TODOS LOS PASOS ***************************************************************
//*************************************************************************************************************************************************

if (
    $resultadoQR &&
    $resultadoEquiposCompra &&
    $resultadoAlmacenamiento &&
    $resultadoProcesador &&
    $resultadoSoftware
) {
    // Todas las inserciones fueron exitosas
    echo json_encode([
        'success' => true,
        'message' => 'Equipo creado con QR y datos adicionales insertados correctamente.',
        'id_equipo' => $idEquipo
    ]);
} else {
    // Identificar en qué paso falló
    $errores = [];
    if (!$resultadoQR) $errores[] = "Error en QR.";
    if (!$resultadoEquiposCompra) $errores[] = "Error en Equipos Compra.";
    if (!$resultadoAlmacenamiento) $errores[] = "Error en Almacenamiento.";
    if (!$resultadoProcesador) $errores[] = "Error en Procesador.";
    if (!$resultadoSoftware) $errores[] = "Error en Software.";

    // Validar si hubo errores en las memorias dinámicamente
    if (!empty($erroresMemorias)) {
        $errores = array_merge($errores, $erroresMemorias);
    }

    throw new Exception("Errores detectados: " . implode(', ', $errores));
}
} catch (Exception $e) {
    // Registrar el error sin eliminar el equipo
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'id_equipo' => $idEquipo
    ]);
}
