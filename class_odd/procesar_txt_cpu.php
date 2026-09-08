<?php
session_start();
require_once '../class/conexion.php'; // Incluye la clase MySQL para la conexión a la base de datos

// Verificar si se ha subido un archivo
if ($_FILES['archivo_txt']['error'] === UPLOAD_ERR_OK) {
    $nombreTmp = $_FILES['archivo_txt']['tmp_name']; // Ruta temporal del archivo
    $nombreArchivo = pathinfo($_FILES['archivo_txt']['name'], PATHINFO_FILENAME); // Nombre del archivo sin extensión
    $idUsuario = intval($_POST['id_usuario']); // Obtener el ID del usuario

    // Leer el contenido del archivo
    $contenido = file_get_contents($nombreTmp);

    // Función para extraer datos del archivo usando expresiones regulares
    function extraerDatosCpuZ($contenido) {
        preg_match('/Specification\s+(.+)/', $contenido, $procesador);
        preg_match('/Number of cores\s+(\d+)/', $contenido, $nucleos);
        preg_match('/Core Speed\s+([\d.]+) MHz/', $contenido, $velocidad);
        preg_match('/Voltage 0\s+([\d.]+) Volts/', $contenido, $voltaje);
        preg_match('/Type\s+(.+)/', $contenido, $tipo_memoria);
        preg_match('/Size\s+(.+)/', $contenido, $tamano_memoria);
        preg_match('/Command Rate\s+(.+)/', $contenido, $command_rate);

        // Devolver los datos encontrados
        return [
            'procesador' => $procesador[1] ?? 'Desconocido',
            'nucleos' => $nucleos[1] ?? 0,
            'velocidad' => $velocidad[1] ?? 0,
            'voltaje' => $voltaje[1] ?? 0,
            'tipo_memoria' => $tipo_memoria[1] ?? 'Desconocido',
            'tamano_memoria' => $tamano_memoria[1] ?? 'Desconocido',
            'command_rate' => $command_rate[1] ?? 'Desconocido',
        ];
    }

    // Extraer los datos del archivo TXT
    $datosCpuZ = extraerDatosCpuZ($contenido);

    // Conectar a la base de datos
    $bdato = new MySQL('', '', ''); // Conexión a la base de datos

    // Comprobar si ya existe un registro para el usuario en la tabla equipos
    $sqlCheck = "SELECT id_usuario FROM equipos WHERE id_usuario = $idUsuario";
    $resultado = $bdato->consulta($sqlCheck);

    if ($bdato->num_rows($resultado) > 0) {
        // Si ya existe un registro, realizar actualización
        $sqlUpdate = "UPDATE equipos SET 
            nombre_equipo = '" . $bdato->escape_string($nombreArchivo) . "', 
            procesador = '" . $bdato->escape_string($datosCpuZ['procesador']) . "', 
            cpu_nucleos = " . $datosCpuZ['nucleos'] . ", 
            voltaje_cpu = " . $datosCpuZ['voltaje'] . ", 
            velocidad_cpu = " . $datosCpuZ['velocidad'] . ", 
            tipo_memoria = '" . $bdato->escape_string($datosCpuZ['tipo_memoria']) . "', 
            tamano_memoria = '" . $bdato->escape_string($datosCpuZ['tamano_memoria']) . "', 
            command_rate = '" . $bdato->escape_string($datosCpuZ['command_rate']) . "',
            fecha_creacion = NOW() 
            WHERE id_usuario = $idUsuario";

        if ($bdato->consulta($sqlUpdate)) {
            echo json_encode(['success' => true, 'message' => 'Datos actualizados correctamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al actualizar los datos']);
        }
    } else {
        // Si no existe un registro, realizar la inserción
        $sqlInsert = "INSERT INTO equipos (id_usuario, nombre_equipo, procesador, cpu_nucleos, voltaje_cpu, velocidad_cpu, tipo_memoria, tamano_memoria, command_rate, fecha_creacion)
            VALUES ($idUsuario, '" . $bdato->escape_string($nombreArchivo) . "', '" . 
            $bdato->escape_string($datosCpuZ['procesador']) . "', " . 
            $datosCpuZ['nucleos'] . ", " . $datosCpuZ['voltaje'] . ", " . 
            $datosCpuZ['velocidad'] . ", '" . 
            $bdato->escape_string($datosCpuZ['tipo_memoria']) . "', '" . 
            $bdato->escape_string($datosCpuZ['tamano_memoria']) . "', '" . 
            $bdato->escape_string($datosCpuZ['command_rate']) . "', NOW())";

        if ($bdato->consulta($sqlInsert)) {
            echo json_encode(['success' => true, 'message' => 'Datos insertados correctamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al insertar los datos']);
        }
    }
} else {
    // Si hubo un error al subir el archivo
    echo json_encode(['success' => false, 'error' => 'Error al subir el archivo']);
}
