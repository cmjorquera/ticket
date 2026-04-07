<?php
header('Content-Type: application/json; charset=utf-8');

/* Logica simulada para una demo visual de IA.
 * No usa base de datos ni modelos reales.
 */
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'ok' => false,
            'mensaje' => 'Metodo no permitido.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $asunto = trim((string)($_POST['asunto'] ?? ''));
    $descripcion = trim((string)($_POST['descripcion'] ?? ''));
    $categoriaManual = trim((string)($_POST['categoria_manual'] ?? ''));
    $colegioArea = trim((string)($_POST['colegio_area'] ?? ''));
    $prioridadManual = trim((string)($_POST['prioridad_manual'] ?? ''));

    if ($asunto === '' || $descripcion === '') {
        http_response_code(422);
        echo json_encode([
            'ok' => false,
            'mensaje' => 'Debes enviar asunto y descripcion.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $texto = mb_strtolower($asunto . ' ' . $descripcion, 'UTF-8');

    $categorias = [
        'Redes' => ['internet', 'wifi', 'wi-fi', 'red', 'conexion', 'conexión', 'router', 'senal', 'señal'],
        'Impresoras' => ['impresora', 'tinta', 'imprimir', 'escaner', 'escaner', 'papel', 'toner', 'tóner'],
        'Correo' => ['correo', 'mail', 'outlook', 'bandeja', 'smtp', 'adjunto'],
        'Hardware / Soporte' => ['computador', 'pc', 'lento', 'pantalla', 'teclado', 'mouse', 'disco', 'equipo'],
        'Software' => ['sistema', 'plataforma', 'error', 'acceso', 'login', 'aplicacion', 'aplicación', 'software']
    ];

    $tecnicos = [
        'Redes' => ['nombre' => 'Juan Perez', 'especialidad' => 'Infraestructura y conectividad', 'tiempo' => '2 horas', 'carga' => '2 tickets activos', 'estado' => 'Disponible'],
        'Impresoras' => ['nombre' => 'Maria Soto', 'especialidad' => 'Perifericos e impresion', 'tiempo' => '4 horas', 'carga' => '4 tickets activos', 'estado' => 'Carga media'],
        'Correo' => ['nombre' => 'Carlos Munoz', 'especialidad' => 'Mensajeria y cuentas institucionales', 'tiempo' => '1 hora', 'carga' => '1 ticket activo', 'estado' => 'Disponible'],
        'Hardware / Soporte' => ['nombre' => 'Ana Torres', 'especialidad' => 'Soporte tecnico y estaciones de trabajo', 'tiempo' => '3 horas', 'carga' => '3 tickets activos', 'estado' => 'Carga media'],
        'Software' => ['nombre' => 'Pedro Rojas', 'especialidad' => 'Aplicaciones, accesos y plataformas', 'tiempo' => '5 horas', 'carga' => '5 tickets activos', 'estado' => 'Alta demanda']
    ];

    $similares = [
        'Redes' => [
            ['id_ticket' => 'TK-1042', 'asunto' => 'Wifi intermitente en biblioteca', 'categoria' => 'Redes', 'solucion' => 'Reconfiguracion de AP y ajuste de canal', 'tiempo_resolucion' => '1.5 horas'],
            ['id_ticket' => 'TK-0977', 'asunto' => 'Caida de internet en laboratorio', 'categoria' => 'Redes', 'solucion' => 'Reinicio de switch y validacion de uplink', 'tiempo_resolucion' => '2 horas'],
            ['id_ticket' => 'TK-0891', 'asunto' => 'Sin conexion en segundo piso', 'categoria' => 'Redes', 'solucion' => 'Cambio de patch cord y prueba de puerto', 'tiempo_resolucion' => '2.5 horas']
        ],
        'Impresoras' => [
            ['id_ticket' => 'TK-1108', 'asunto' => 'Impresora no toma papel', 'categoria' => 'Impresoras', 'solucion' => 'Limpieza de bandeja y calibracion', 'tiempo_resolucion' => '3 horas'],
            ['id_ticket' => 'TK-1015', 'asunto' => 'Error de toner en equipo directivo', 'categoria' => 'Impresoras', 'solucion' => 'Cambio de toner y reinicio del spooler', 'tiempo_resolucion' => '4 horas'],
            ['id_ticket' => 'TK-0960', 'asunto' => 'No imprime desde secretaria', 'categoria' => 'Impresoras', 'solucion' => 'Reinstalacion del driver de impresion', 'tiempo_resolucion' => '2 horas']
        ],
        'Correo' => [
            ['id_ticket' => 'TK-1120', 'asunto' => 'Outlook no sincroniza bandeja', 'categoria' => 'Correo', 'solucion' => 'Recreacion de perfil local', 'tiempo_resolucion' => '45 minutos'],
            ['id_ticket' => 'TK-1066', 'asunto' => 'No llegan correos con adjuntos', 'categoria' => 'Correo', 'solucion' => 'Ajuste de cuota y limpieza de OST', 'tiempo_resolucion' => '1 hora'],
            ['id_ticket' => 'TK-0948', 'asunto' => 'Cuenta bloqueada en mail institucional', 'categoria' => 'Correo', 'solucion' => 'Desbloqueo y cambio de credenciales', 'tiempo_resolucion' => '35 minutos']
        ],
        'Hardware / Soporte' => [
            ['id_ticket' => 'TK-1111', 'asunto' => 'PC demasiado lento en recepcion', 'categoria' => 'Hardware / Soporte', 'solucion' => 'Limpieza de inicio y optimizacion de disco', 'tiempo_resolucion' => '2.5 horas'],
            ['id_ticket' => 'TK-1001', 'asunto' => 'Pantalla sin imagen en oficina', 'categoria' => 'Hardware / Soporte', 'solucion' => 'Cambio de cable HDMI y ajuste de energia', 'tiempo_resolucion' => '1 hora'],
            ['id_ticket' => 'TK-0888', 'asunto' => 'Equipo no enciende', 'categoria' => 'Hardware / Soporte', 'solucion' => 'Reemplazo de fuente y test de hardware', 'tiempo_resolucion' => '4 horas']
        ],
        'Software' => [
            ['id_ticket' => 'TK-1090', 'asunto' => 'Error al ingresar a plataforma academica', 'categoria' => 'Software', 'solucion' => 'Correccion de permisos y limpieza de cache', 'tiempo_resolucion' => '3 horas'],
            ['id_ticket' => 'TK-1032', 'asunto' => 'Sistema no permite acceso a usuarios', 'categoria' => 'Software', 'solucion' => 'Ajuste de roles y validacion de autenticacion', 'tiempo_resolucion' => '4.5 horas'],
            ['id_ticket' => 'TK-0915', 'asunto' => 'Aplicacion entrega mensaje de error', 'categoria' => 'Software', 'solucion' => 'Revision de logs y reinicio de servicio', 'tiempo_resolucion' => '5 horas']
        ]
    ];

    $categoriaDetectada = 'Software';
    $coincidencias = [];

    foreach ($categorias as $categoria => $palabras) {
        $coincidencias[$categoria] = 0;
        foreach ($palabras as $palabra) {
            if (mb_strpos($texto, $palabra) !== false) {
                $coincidencias[$categoria]++;
            }
        }
    }

    arsort($coincidencias);
    $mejorCategoria = (string)array_key_first($coincidencias);
    if (($coincidencias[$mejorCategoria] ?? 0) > 0) {
        $categoriaDetectada = $mejorCategoria;
    } elseif ($categoriaManual !== '') {
        $categoriaDetectada = $categoriaManual;
    }

    $palabrasUrgentes = ['urgente', 'caido', 'caído', 'no funciona', 'critico', 'crítico', 'inmediato', 'bloqueado', 'sin servicio'];
    $esUrgente = false;
    foreach ($palabrasUrgentes as $palabraUrgente) {
        if (mb_strpos($texto, $palabraUrgente) !== false) {
            $esUrgente = true;
            break;
        }
    }

    $baseConfianza = 68;
    $baseConfianza += min(18, ($coincidencias[$categoriaDetectada] ?? 0) * 7);
    if ($categoriaManual !== '' && $categoriaManual === $categoriaDetectada) {
        $baseConfianza += 6;
    }
    if ($esUrgente) {
        $baseConfianza += 8;
    }
    if ($prioridadManual === 'Critica' || $prioridadManual === 'Alta') {
        $baseConfianza += 4;
    }
    $confianza = max(55, min(98, $baseConfianza));

    $tecnico = $tecnicos[$categoriaDetectada];
    $motivoSugerencia = 'Se sugiere a ' . $tecnico['nombre'] . ' por su experiencia en ' . mb_strtolower($tecnico['especialidad'], 'UTF-8') . '.';
    if ($colegioArea !== '') {
        $motivoSugerencia .= ' Se priorizo compatibilidad operativa con el contexto de ' . $colegioArea . '.';
    }
    if ($esUrgente) {
        $motivoSugerencia .= ' El ticket contiene senales de urgencia, por lo que se prioriza respuesta rapida.';
    }

    $riesgo = 'Bajo';
    $urgencia = 'Normal';
    if ($esUrgente || $prioridadManual === 'Critica') {
        $riesgo = 'Alto';
        $urgencia = 'Alta';
    } elseif ($prioridadManual === 'Alta' || $prioridadManual === 'Media') {
        $riesgo = 'Medio';
        $urgencia = 'Media';
    }

    $detalleClasificacion = 'La categoria se estimo cruzando palabras clave del asunto y la descripcion con un set de reglas internas.';
    if ($categoriaManual !== '' && $categoriaManual !== $categoriaDetectada) {
        $detalleClasificacion .= ' La sugerencia automatica difiere de la categoria manual, lo que puede servir para detectar desalineaciones.';
    }

    $detallePrediccion = 'El tiempo proyectado considera categoria detectada, severidad linguistica y prioridad manual ingresada en la demo.';
    if ($esUrgente) {
        $detallePrediccion .= ' Se detectaron terminos criticos que elevaron el riesgo y la urgencia.';
    }

    echo json_encode([
        'ok' => true,
        'data' => [
            'categoria_detectada' => $categoriaDetectada,
            'badge_categoria' => $categoriaDetectada,
            'confianza' => $confianza,
            'detalle_clasificacion' => $detalleClasificacion,
            'tecnico_sugerido' => $tecnico['nombre'],
            'especialidad' => $tecnico['especialidad'],
            'motivo_sugerencia' => $motivoSugerencia,
            'carga_actual' => $tecnico['carga'],
            'estado_carga' => $tecnico['estado'],
            'tiempo_estimado' => $tecnico['tiempo'],
            'riesgo' => $riesgo,
            'urgencia' => $urgencia,
            'detalle_prediccion' => $detallePrediccion,
            'similares' => $similares[$categoriaDetectada] ?? []
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error en la demo IA: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
