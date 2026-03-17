<?php
// Iniciar sesión y requerir la conexión a la base de datos
session_start();
require_once '../../class/conexion.php';

// Configurar cabeceras para devolver JSON
header('Content-Type: application/json');

try {
    // Validar que se envíe un ID y que sea numérico
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id_dispositivo = intval($_GET['id']); // Sanitizar el ID

        // Crear instancia de conexión
        $bdato = new MySQL('', '', ''); // Ajusta con tus credenciales de conexión

        // Consulta SQL para obtener datos del dispositivo con el ID proporcionado
            $consulta = "SELECT 
                            id_dispositivo, 
                            tipo, 
                            marca, 
                            modelo, 
                            n_serie, 
                            asignado, 
                            proveedor, 
                            precio, 
                            fecha_compra, 
                            observaciones, 
                            qr_code 
                        FROM otros_dispositivos
                        WHERE id_dispositivo = $id_dispositivo";

        // Ejecutar consulta
        $resultado = $bdato->consulta($consulta);

        // Verificar si se encontraron resultados
        if ($bdato->num_rows($resultado) > 0) {
            $dispositivo = $bdato->fetch_assoc($resultado);

            // Devolver los datos en formato JSON
            echo json_encode([
                'success' => true,
                'equipo' => $dispositivo // Aquí se devuelve un solo dispositivo
            ]);
        } else {
            // Si no se encontró el dispositivo
            echo json_encode([
                'success' => false,
                'error' => 'Dispositivo no encontrado.'
            ]);
        }
    } else {
        // Si no se proporciona un ID válido
        echo json_encode([
            'success' => false,
            'error' => 'ID de dispositivo no proporcionado o inválido.'
        ]);
    }
} catch (Exception $e) {
    // Manejo de errores
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
