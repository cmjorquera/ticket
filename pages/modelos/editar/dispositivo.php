<?php
header('Content-Type: application/json');
require_once '../../class/conexion.php';
require_once '../../class/funciones.php';

// Decodificar los datos JSON recibidos
$data = json_decode(file_get_contents('php://input'), true);

// Validar que el ID del dispositivo está presente
$id_dispositivo = $data['id_dispositivo'] ?? 0;
if ($id_dispositivo == 0) {
    echo json_encode(['success' => false, 'error' => 'ID del dispositivo no proporcionado']);
    exit;
}

// Remover el sufijo de cada campo
$tipo           = $data["tipo_$id_dispositivo"]                 ?? '';
$marca          = $data["marca_$id_dispositivo"]                ?? '';
$modelo         = $data["modelo_$id_dispositivo"]               ?? '';
$n_serie        = $data["n_serie_$id_dispositivo"]              ?? '';
$n_factura      = $data["numero_factura_$id_dispositivo"]       ?? '';
$asignado       = $data["asignado_$id_dispositivo"]             ?? '';
// Obtener el valor del precio desde el array
$precio = !empty($data["precio_$id_dispositivo"]) ? $data["precio_$id_dispositivo"] : null;

// Validar y limpiar el valor si no es null
if (!is_null($precio)) {
    // Eliminar el punto como separador de miles
    $precio = str_replace('.', '', $precio);

    // Convertir el valor a entero
    $precio = (int)$precio;
}

// Ahora $precio es un número entero listo para guardar en la base de datos
$proveedor      = $data["proveedor_$id_dispositivo"] ?? '';
$fecha_compra   = !empty($data["fecha_compra_$id_dispositivo"]) ? $data["fecha_compra_$id_dispositivo"] : '0000-00-00';
$observaciones  = $data["observaciones_$id_dispositivo"] ?? '';

// Crear una instancia de la conexión
$bdato = new MySQL('', '', ''); // Configura tus parámetros de conexión

// Preparar la consulta de actualización
$consulta = "
    UPDATE otros_dispositivos SET 
        tipo            = '$tipo', 
        marca           = '$marca', 
        modelo          = '$modelo', 
        n_serie         = '$n_serie', 
        n_factura       = '$n_factura', 
        asignado        = '$asignado', 
        precio          = '$precio', 
        proveedor       = '$proveedor', 
        fecha_compra    = '$fecha_compra', 
        observaciones   = '$observaciones'
    WHERE id_dispositivo = $id_dispositivo
";

// Ejecutar la consulta
$result = $bdato->consulta($consulta);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al actualizar el dispositivo']);
}
