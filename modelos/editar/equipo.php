<?php
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

$bdato = new MySQL('', '', ''); 

//********************************************************************** */
// Decodificar los datos JSON enviados
$data = json_decode(file_get_contents('php://input'), true);
//**********************************************************************

//******************************************************************************
//***********************EQUIPOS************************************************
//******************************************************************************
// Obtener los datos de la solicitud y asegurarse de que los campos se conviertan a NULL si están vacíos
$id_equipo              = isset($data['id_equipo'])                          ? intval($data['id_equipo']) : 0;
$nombreEquipo           = isset($data['nombre_equipo'])                      ? "'" . $bdato->escape_string($data['nombre_equipo']) . "'"    : 'NULL';
$fabricante             = isset($data['fabricante'])                         ? "'" . $bdato->escape_string($data['fabricante']) . "'"       : 'NULL';
$producto               = isset($data['producto'])                           ? "'" . $bdato->escape_string($data['producto']) . "'"         : 'NULL';
$numeroSerie            = isset($data['numero_serie'])                       ? "'" . $bdato->escape_string($data['numero_serie']) . "'"     : 'NULL';
$tipoPc                 = isset($data['tipo_pc'])                            ? "'" . $bdato->escape_string($data['tipo_pc']) . "'"          : 'NULL';
$idUsuario              = isset($data['usuarios_select'])                    ? "'" . $bdato->escape_string($data['usuarios_select']) . "'"  : 'NULL';

// Actualizar la tabla equipos
$sqlUpdateEquipos = "
    UPDATE equipos 
    SET 
        id_usuario      = $idUsuario, 
        nombre_equipo   = $nombreEquipo, 
        fabricante      = $fabricante, 
        producto        = $producto, 
        numero_serie    = $numeroSerie, 
        tipo_pc         = $tipoPc
    WHERE id_equipo = $id_equipo
";

//******************************************************************************
//***********************COMPRA*************************************************
//******************************************************************************
$resultado = $bdato->consulta($sqlUpdateEquipos);

if ($resultado) {
    // Datos para la tabla equipos_compra
    $valorEquipo                        = isset($data['valor_equipo'])              ? "'" . $bdato->escape_string($data['valor_equipo']) . "'"      : 'NULL';
    $proveedor                          = isset($data['proveedor'])                 ? "'" . $bdato->escape_string($data['proveedor']) . "'"         : 'NULL';
    $numeroFactura                      = isset($data['numero_factura'])            ? "'" . $bdato->escape_string($data['numero_factura']) . "'"    : 'NULL';
    $observacion                        = isset($data['observacion'])               ? "'" . $bdato->escape_string($data['observacion']) . "'"       : 'NULL';
    $fechaCompra                        = isset($data['fecha_compra'])              ? "'" . $bdato->escape_string($data['fecha_compra']) . "'"      : '0000-00-00';


    $sqlUpdateEquiposCompra = "
        UPDATE equipos_compra 
        SET 
            valor_equipo        = $valorEquipo, 
            proveedor           = $proveedor, 
            numero_factura      = $numeroFactura, 
            fecha_compra        = $fechaCompra,
            observacion         = $observacion
        WHERE id_equipo = $id_equipo
    ";
    $resultadoEquiposCompra = $bdato->consulta($sqlUpdateEquiposCompra);

    // Datos para la tabla equipo_almacenamiento
    $equipoModeloAlmacenamiento         = isset($data['nombre_almacenamiento'])     ? "'" . $bdato->escape_string($data['nombre_almacenamiento']) . "'"         : 'NULL';
    $capacidadAlmacenamiento            = isset($data['capacidad_almacenamiento'])  ? "'" . $bdato->escape_string($data['capacidad_almacenamiento']) . "'"      : 'NULL';
    $tipoAlmacenamiento                 = isset($data['tipo_almacenamiento'])       ? "'" . $bdato->escape_string($data['tipo_almacenamiento']) . "'"           : 'NULL';

    $sqlUpdateAlmacenamiento = "
        UPDATE equipo_almacenamiento 
        SET 
            equipo_modelo = $equipoModeloAlmacenamiento, 
            equipo_capacidad = $capacidadAlmacenamiento, 
            equipo_tamano = $tipoAlmacenamiento
        WHERE id_equipo = $id_equipo
    ";
    $resultadoAlmacenamiento = $bdato->consulta($sqlUpdateAlmacenamiento);

//******************************************************************************
//***********************MEMORIA************************************************
// *****************************************************************************
if (isset($data['designacion_memoria1'])) {
    $memorias = [];
    $cantidadMemorias = 0;

    // Determinar la cantidad de memorias basándonos en las propiedades
    while (isset($data["designacion_memoria" . ($cantidadMemorias + 1)])) {
        $cantidadMemorias++;
    }

    // Construir el array de memorias
    for ($i = 1; $i <= $cantidadMemorias; $i++) {
        $memorias[] = [
            'designacion_memoria' => isset($data["designacion_memoria$i"]) ? $data["designacion_memoria$i"] : null,
            'formato_memoria' => isset($data["formato_memoria$i"]) ? $data["formato_memoria$i"] : null,
            'tipo_memoria' => isset($data["tipo_memoria$i"]) ? $data["tipo_memoria$i"] : null,
            'tamano_memoria' => isset($data["tamano_memoria$i"]) ? $data["tamano_memoria$i"] : null,
            'frecuencia_memoria' => isset($data["frecuencia_memoria$i"]) ? $data["frecuencia_memoria$i"] : null,
            'marca_memoria' => isset($data["marca_memoria$i"]) ? $data["marca_memoria$i"] : null,
        ];
    }

    // Ahora procesar las memorias como un array de objetos
    foreach ($memorias as $index => $memoria) {
        $designacionMemoria = isset($memoria['designacion_memoria']) ? "'" . $bdato->escape_string($memoria['designacion_memoria']) . "'" : 'NULL';
        $formatoMemoria = isset($memoria['formato_memoria']) ? "'" . $bdato->escape_string($memoria['formato_memoria']) . "'" : 'NULL';
        $tipoMemoria = isset($memoria['tipo_memoria']) ? "'" . $bdato->escape_string($memoria['tipo_memoria']) . "'" : 'NULL';
        $tamanoMemoria = isset($memoria['tamano_memoria']) ? "'" . $bdato->escape_string($memoria['tamano_memoria']) . "'" : 'NULL';
        $frecuenciaMemoria = isset($memoria['frecuencia_memoria']) ? "'" . $bdato->escape_string($memoria['frecuencia_memoria']) . "'" : 'NULL';
        $marcaMemoria = isset($memoria['marca_memoria']) ? "'" . $bdato->escape_string($memoria['marca_memoria']) . "'" : 'NULL';

        $sqlUpdateMemoria = "
            UPDATE equipo_memoria 
            SET 
                designacion_memoria = $designacionMemoria,
                formato_memoria = $formatoMemoria,
                tipo_memoria = $tipoMemoria,
                tamano_memoria = $tamanoMemoria,
                frecuencia_memoria = $frecuenciaMemoria,
                marca_memoria = $marcaMemoria
            WHERE id_equipo = $id_equipo AND orden_memoria = " . ($index + 1) . "
        ";

        // echo "<pre>Consulta SQL Generada:\n$sqlUpdateMemoria</pre>"; // Debug: Ver la consulta generada
        $resultadoMemoria = $bdato->consulta($sqlUpdateMemoria);
    }
}


//******************************************************************************
//***********************PROCESADOR*********************************************
//******************************************************************************

    // Datos para la tabla equipo_procesador
    $fabricanteProcesador               = isset($data['fabricante_procesador'])     ? "'" . $bdato->escape_string($data['fabricante_procesador']) . "'"         : 'NULL';
    $modeloProcesador                   = isset($data['modelo_procesador'])         ? "'" . $bdato->escape_string($data['modelo_procesador']) . "'"             : 'NULL';
    $frecuenciaProcesador               = isset($data['frecuencia_procesador'])     ? "'" . $bdato->escape_string($data['frecuencia_procesador']) . "'"         : 'NULL';

    $sqlUpdateProcesador = "
        UPDATE equipo_procesador 
        SET 
            equipo_fabricante = $fabricanteProcesador, 
            equipo_modelo = $modeloProcesador, 
            equipo_velocidad = $frecuenciaProcesador
        WHERE id_equipo = $id_equipo
    ";
    $resultadoProcesador = $bdato->consulta($sqlUpdateProcesador);




//******************************************************************************
//***********************SOFTWARE***********************************************
//******************************************************************************
    // Datos para la tabla equipo_software
    $windows = isset($data['windows'])          ? "'" . $bdato->escape_string($data['windows']) . "'"       : 'NULL';
    $office = isset($data['office'])            ? "'" . $bdato->escape_string($data['office']) . "'"        : 'NULL';
    $antiVirus = isset($data['antiVirus'])      ? "'" . $bdato->escape_string($data['antiVirus']) . "'"     : 'NULL';

    $sqlUpdateSoftware = "
        UPDATE equipo_software 
        SET 
            windows = $windows, 
            office = $office, 
            antivirus = $antiVirus
        WHERE id_equipo = $id_equipo
    ";
    $resultadoSoftware = $bdato->consulta($sqlUpdateSoftware);

    // Verificar si todas las consultas se ejecutaron correctamente
    if ($resultadoEquiposCompra && $resultadoAlmacenamiento && $resultadoProcesador && $resultadoSoftware && $resultadoMemoria) {
        echo json_encode(["success" => true, "message" => "Datos actualizados correctamente."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al actualizar los datos en una o más tablas."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Error al actualizar los datos en la tabla equipos."]);
}
?>
