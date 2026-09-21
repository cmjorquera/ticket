<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

$idUsuarioSession = isset($_SESSION['id']) ? (int) $_SESSION['id'] : 0;
$accion = isset($_POST['accion']) ? trim((string) $_POST['accion']) : '';
$ticketPrincipal = isset($_POST['ticket_principal']) ? (int) $_POST['ticket_principal'] : 0;
$ticketSecundario = isset($_POST['ticket_secundario']) ? (int) $_POST['ticket_secundario'] : 0;
$ticketEliminar = isset($_POST['ticket_eliminar']) ? (int) $_POST['ticket_eliminar'] : 0;
$tipoEliminacion = isset($_POST['tipo_eliminacion']) ? trim((string) $_POST['tipo_eliminacion']) : 'completa';
$motivo = isset($_POST['motivo']) ? trim((string) $_POST['motivo']) : '';

if ($idUsuarioSession <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Sesion no valida.'
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

$db = new MySQL("", "", "");
$db->set_charset('utf8mb4');

function table_exists($db, $tableName)
{
    $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
    $query = $db->consulta("SHOW TABLES LIKE '{$safeTable}'");
    return $db->num_rows($query) > 0;
}

function contar_relacionados($db, $tableName, $ticketId)
{
    if (!table_exists($db, $tableName)) {
        return [
            'tabla' => $tableName,
            'existe' => false,
            'total' => null,
            'detalle' => 'Tabla no detectada en la base de datos.'
        ];
    }

    $query = $db->consulta("SELECT COUNT(*) AS total FROM {$tableName} WHERE id_ticket = {$ticketId}");
    $row = $db->fetch_assoc($query);

    return [
        'tabla' => $tableName,
        'existe' => true,
        'total' => (int) ($row['total'] ?? 0),
        'detalle' => 'Registros vinculados por id_ticket.'
    ];
}

function obtener_ticket_resumen($db, $ticketId)
{
    $ticketId = (int) $ticketId;
    if ($ticketId <= 0) {
        return null;
    }

    $consulta = $db->consulta("
        SELECT
            t.id_ticket,
            t.asunto,
            t.descripcion_ticket,
            t.comentario_administrador,
            t.comentario_final,
            t.id_estado,
            u.nombre AS nombre_usuario,
            u.apellido_paterno AS apellido_usuario,
            tec.nombre AS nombre_tecnico,
            tec.apellido_paterno AS apellido_tecnico,
            et.nombre AS estado_nombre,
            p.nombre AS prioridad_nombre,
            c.nombre_categoria,
            pt.fecha_creacion_inicio,
            pt.hora_creacion_inicio
        FROM tickets t
        LEFT JOIN usuarios u ON u.id = t.id_usuario
        LEFT JOIN usuarios tec ON tec.id = t.id_tecnico
        LEFT JOIN estados_ticket et ON et.id = t.id_estado
        LEFT JOIN prioridad p ON p.id = t.id_prioridad
        LEFT JOIN categoria_de_ticket c ON c.id_categoria = t.id_categoria_ticket
        LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
        WHERE t.id_ticket = {$ticketId}
        LIMIT 1
    ");

    if ($db->num_rows($consulta) === 0) {
        return null;
    }

    $row = $db->fetch_assoc($consulta);

    $usuarioNombre = trim(($row['nombre_usuario'] ?? '') . ' ' . ($row['apellido_usuario'] ?? ''));
    $tecnicoNombre = trim(($row['nombre_tecnico'] ?? '') . ' ' . ($row['apellido_tecnico'] ?? ''));

    return [
        'id_ticket' => (int) $row['id_ticket'],
        'asunto' => $row['asunto'] ?? '',
        'descripcion' => $row['descripcion_ticket'] ?? '',
        'comentario_administrador' => $row['comentario_administrador'] ?? '',
        'comentario_final' => $row['comentario_final'] ?? '',
        'estado' => $row['estado_nombre'] ?? 'Sin estado',
        'prioridad' => $row['prioridad_nombre'] ?? 'Sin prioridad',
        'categoria' => $row['nombre_categoria'] ?? 'Sin categoria',
        'usuario' => $usuarioNombre !== '' ? $usuarioNombre : 'Sin usuario',
        'tecnico' => $tecnicoNombre !== '' ? $tecnicoNombre : 'Sin tecnico',
        'fecha' => trim(($row['fecha_creacion_inicio'] ?? '') . ' ' . ($row['hora_creacion_inicio'] ?? ''))
    ];
}

function construir_impacto_ticket($db, $ticketId)
{
    $tablas = [
        'tickets',
        'proceso_tickets',
        'archivos_adjuntos_ticket',
        'ticket_conversaciones',
        'avance_tecnicos',
        'calificacion_tickett',
        'comentario_ticket'
    ];

    $impacto = [];
    foreach ($tablas as $tabla) {
        if ($tabla === 'tickets') {
            $impacto[] = [
                'tabla' => $tabla,
                'existe' => table_exists($db, $tabla),
                'total' => table_exists($db, $tabla) ? 1 : null,
                'detalle' => 'Registro maestro del ticket.'
            ];
            continue;
        }

        $impacto[] = contar_relacionados($db, $tabla, $ticketId);
    }

    return $impacto;
}

$response = [
    'success' => false,
    'accion' => $accion,
    'message' => 'Solicitud no valida.'
];

if ($accion === 'fusionar') {
    if ($ticketPrincipal <= 0 || $ticketSecundario <= 0) {
        $response['message'] = 'Debes indicar ticket principal y ticket secundario.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    if ($ticketPrincipal === $ticketSecundario) {
        $response['message'] = 'Los tickets no pueden ser el mismo.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    $principal = obtener_ticket_resumen($db, $ticketPrincipal);
    $secundario = obtener_ticket_resumen($db, $ticketSecundario);

    if ($principal === null || $secundario === null) {
        $response['message'] = 'Uno de los tickets no existe o no pudo ser cargado.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    $response = [
        'success' => true,
        'accion' => $accion,
        'message' => 'Previsualizacion lista para fusion.',
        'motivo' => $motivo,
        'tickets' => [
            'principal' => $principal,
            'secundario' => $secundario
        ],
        'impacto' => [
            'principal' => construir_impacto_ticket($db, $ticketPrincipal),
            'secundario' => construir_impacto_ticket($db, $ticketSecundario)
        ],
        'alertas' => [
            ($principal['usuario'] !== $secundario['usuario']) ? 'Los tickets pertenecen a usuarios distintos.' : null,
            ($principal['categoria'] !== $secundario['categoria']) ? 'Los tickets tienen categorias distintas.' : null,
            ($principal['estado'] !== $secundario['estado']) ? 'Los tickets hoy se encuentran en estados distintos.' : null
        ]
    ];
}

if ($accion === 'eliminar') {
    if ($ticketEliminar <= 0) {
        $response['message'] = 'Debes indicar el ticket a revisar.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    $ticket = obtener_ticket_resumen($db, $ticketEliminar);
    if ($ticket === null) {
        $response['message'] = 'El ticket indicado no existe o no pudo ser cargado.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    $response = [
        'success' => true,
        'accion' => $accion,
        'message' => 'Previsualizacion lista para eliminacion.',
        'motivo' => $motivo,
        'tipo_eliminacion' => $tipoEliminacion,
        'tickets' => [
            'objetivo' => $ticket
        ],
        'impacto' => [
            'objetivo' => construir_impacto_ticket($db, $ticketEliminar)
        ],
        'alertas' => [
            $tipoEliminacion === 'ocultar' ? 'El sistema hoy no tiene implementada la ocultacion; solo existe el flujo de eliminacion completa.' : null,
            'Antes de eliminar, conviene registrar motivo y usuario administrador en una bitacora.'
        ]
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

