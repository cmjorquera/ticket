<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/../class/funciones.php';

function eventos_h($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function eventos_url($ruta = '')
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $base = '/eventos';

    $pos = strpos($scriptName, '/eventos');
    if ($pos !== false) {
        $resto = substr($scriptName, $pos);
        $partes = explode('/', trim($resto, '/'));
        if (!empty($partes[0])) {
            $base = '/' . $partes[0];
        }
    }

    return $ruta === '' ? $base : $base . '/' . ltrim($ruta, '/');
}

function eventos_sistema_url($ruta = '')
{
    $ruta = ltrim((string) $ruta, '/');
    return '../' . $ruta;
}

function eventos_asset_version($rutaRelativa)
{
    $rutaFisica = __DIR__ . '/' . ltrim((string) $rutaRelativa, '/');
    if (is_file($rutaFisica)) {
        return (string) filemtime($rutaFisica);
    }
    return (string) time();
}

function eventos_db()
{
    static $db = null;
    if ($db === null) {
        $db = new MySQL('', '', '');
    }
    return $db;
}

function eventos_usuario_actual()
{
    return [
        'id' => isset($_SESSION['id']) ? (int) $_SESSION['id'] : 0,
        'nombre' => trim(
            (string) ($_SESSION['nombre'] ?? '') . ' ' .
            (string) ($_SESSION['apellido_paterno'] ?? '') . ' ' .
            (string) ($_SESSION['apellido_materno'] ?? '')
        ),
        'area' => isset($_SESSION['id_area_trabajo']) ? (int) $_SESSION['id_area_trabajo'] : 0,
    ];
}

function eventos_requiere_login($ajax = false)
{
    $usuario = eventos_usuario_actual();
    if ($usuario['id'] > 0) {
        return $usuario;
    }

    if ($ajax) {
        eventos_responder_json(['ok' => false, 'message' => 'Sesión expirada'], 401);
    }

    header('Location: ../index.php');
    exit;
}

function eventos_responder_json(array $data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function eventos_trim($value)
{
    return trim((string) $value);
}

function eventos_where_activos()
{
    return "(e.eliminado = 0 OR e.eliminado = '0' OR e.eliminado = 'no' OR e.eliminado IS NULL)";
}

function eventos_tipo_colores()
{
    return [
        'reunion' => '#2563eb',
        'capacitacion' => '#059669',
        'celebracion' => '#d97706',
        'actividad escolar' => '#7c3aed',
    ];
}

function eventos_color_por_tipo($tipo)
{
    $mapa = eventos_tipo_colores();
    return $mapa[$tipo] ?? '#0f766e';
}

function eventos_bool_post($key)
{
    return isset($_POST[$key]) && in_array((string) $_POST[$key], ['1', 'true', 'on', 'si'], true) ? 1 : 0;
}

function eventos_recoger_payload()
{
    $tipo = eventos_trim($_POST['tipo_evento'] ?? 'reunion');
    $color = eventos_trim($_POST['color_evento'] ?? '');

    return [
        'titulo' => eventos_trim($_POST['titulo'] ?? ''),
        'descripcion' => eventos_trim($_POST['descripcion'] ?? ''),
        'fecha_inicio' => eventos_trim($_POST['fecha_inicio'] ?? ''),
        'hora_inicio' => eventos_trim($_POST['hora_inicio'] ?? ''),
        'fecha_fin' => eventos_trim($_POST['fecha_fin'] ?? ''),
        'hora_fin' => eventos_trim($_POST['hora_fin'] ?? ''),
        'cantidad_personas' => max(0, (int) ($_POST['cantidad_personas'] ?? 0)),
        'responsable_id' => (int) ($_POST['responsable_id'] ?? 0),
        'ubicacion' => eventos_trim($_POST['ubicacion'] ?? ''),
        'tipo_evento' => $tipo,
        'estado' => eventos_trim($_POST['estado'] ?? 'programado'),
        'observaciones_logisticas' => eventos_trim($_POST['observaciones_logisticas'] ?? ''),
        'con_audio' => eventos_bool_post('con_audio'),
        'musica_ambiental' => eventos_bool_post('musica_ambiental'),
        'solo_presentacion' => eventos_bool_post('solo_presentacion'),
        'color_evento' => $color !== '' ? $color : eventos_color_por_tipo($tipo),
    ];
}

function eventos_validar_datos(array $data)
{
    $errores = [];

    if ($data['titulo'] === '') {
        $errores[] = 'El título es obligatorio.';
    }
    if ($data['fecha_inicio'] === '' || $data['fecha_fin'] === '') {
        $errores[] = 'Debe indicar fecha de inicio y término.';
    }
    if ($data['hora_inicio'] === '' || $data['hora_fin'] === '') {
        $errores[] = 'Debe indicar hora de inicio y término.';
    }
    if ($data['responsable_id'] <= 0) {
        $errores[] = 'Debe seleccionar un responsable.';
    }
    if (!in_array($data['tipo_evento'], ['reunion', 'capacitacion', 'celebracion', 'actividad escolar'], true)) {
        $errores[] = 'El tipo de evento no es válido.';
    }
    if (!in_array($data['estado'], ['programado', 'cancelado', 'finalizado'], true)) {
        $errores[] = 'El estado del evento no es válido.';
    }

    $inicio = strtotime($data['fecha_inicio'] . ' ' . $data['hora_inicio']);
    $fin = strtotime($data['fecha_fin'] . ' ' . $data['hora_fin']);
    if ($inicio === false || $fin === false) {
        $errores[] = 'Las fechas u horas no tienen un formato válido.';
    } elseif ($fin < $inicio) {
        $errores[] = 'La fecha/hora de término no puede ser menor a la de inicio.';
    }

    return $errores;
}

function eventos_stmt_result_all($stmt)
{
    $result = $stmt->get_result();
    if (!$result) {
        return [];
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function eventos_stmt_result_one($stmt)
{
    $rows = eventos_stmt_result_all($stmt);
    return $rows ? $rows[0] : null;
}

function eventos_obtener_responsables()
{
    $db = eventos_db();
    $resultado = $db->consulta("SELECT id, nombre, apellido_paterno, apellido_materno, email FROM usuarios ORDER BY nombre ASC, apellido_paterno ASC");
    $items = [];

    while ($row = $db->fetch_assoc($resultado)) {
        $row['nombre_completo'] = trim($row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']);
        $items[] = $row;
    }

    return $items;
}

function eventos_obtener_responsable($responsableId)
{
    $db = eventos_db();
    $stmt = $db->prepare("SELECT id, nombre, apellido_paterno, apellido_materno, email FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $responsableId);
    $stmt->execute();
    $row = eventos_stmt_result_one($stmt);
    $stmt->close();

    if ($row) {
        $row['nombre_completo'] = trim($row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']);
    }

    return $row;
}

function eventos_obtener_eventos($fechaInicio = null, $fechaFin = null)
{
    $db = eventos_db();
    $where = [eventos_where_activos()];
    $types = '';
    $params = [];

    if ($fechaInicio !== null) {
        $where[] = 'e.fecha_inicio >= ?';
        $types .= 's';
        $params[] = $fechaInicio;
    }
    if ($fechaFin !== null) {
        $where[] = 'e.fecha_inicio <= ?';
        $types .= 's';
        $params[] = $fechaFin;
    }

    $sql = "SELECT
                e.id, e.titulo, e.descripcion, e.fecha_inicio, e.fecha_fin, e.hora_evento, e.hora_inicio, e.hora_fin,
                e.con_audio, e.solo_presentacion, e.musica_ambiental, e.cantidad_personas, e.creado_en, e.actualizado_en,
                e.responsable_id, e.eliminado, e.ubicacion, e.tipo_evento, e.estado, e.color_evento, e.observaciones_logisticas,
                e.correo_enviado, u.nombre, u.apellido_paterno, u.apellido_materno, u.email
            FROM eventos e
            LEFT JOIN usuarios u ON u.id = e.responsable_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY e.fecha_inicio ASC, COALESCE(e.hora_inicio, e.hora_evento) ASC";

    $stmt = $db->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $rows = eventos_stmt_result_all($stmt);
    $stmt->close();

    foreach ($rows as &$row) {
        $row['responsable_nombre'] = trim(($row['nombre'] ?? '') . ' ' . ($row['apellido_paterno'] ?? '') . ' ' . ($row['apellido_materno'] ?? ''));
        $row['hora_inicio'] = $row['hora_inicio'] ?: $row['hora_evento'];
        $row['hora_fin'] = $row['hora_fin'] ?: $row['hora_evento'];
        $row['color_evento'] = $row['color_evento'] ?: eventos_color_por_tipo($row['tipo_evento']);
    }

    return $rows;
}

function eventos_obtener_evento($id)
{
    $db = eventos_db();
    $stmt = $db->prepare("SELECT e.*, u.nombre, u.apellido_paterno, u.apellido_materno, u.email
                          FROM eventos e
                          LEFT JOIN usuarios u ON u.id = e.responsable_id
                          WHERE e.id = ?
                          LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = eventos_stmt_result_one($stmt);
    $stmt->close();

    if (!$row) {
        return null;
    }

    $row['responsable_nombre'] = trim(($row['nombre'] ?? '') . ' ' . ($row['apellido_paterno'] ?? '') . ' ' . ($row['apellido_materno'] ?? ''));
    $row['hora_inicio'] = $row['hora_inicio'] ?: $row['hora_evento'];
    $row['hora_fin'] = $row['hora_fin'] ?: $row['hora_evento'];
    $row['color_evento'] = $row['color_evento'] ?: eventos_color_por_tipo($row['tipo_evento']);

    return $row;
}

function eventos_resumen_tecnico(array $evento)
{
    $items = [];
    if ((int) $evento['con_audio'] === 1) {
        $items[] = 'Audio';
    }
    if ((int) $evento['musica_ambiental'] === 1) {
        $items[] = 'Música ambiental';
    }
    if ((int) $evento['solo_presentacion'] === 1) {
        $items[] = 'Solo presentación';
    }
    return $items ?: ['Sin requerimientos especiales'];
}

function eventos_formatear_calendario(array $evento)
{
    $horaInicio = $evento['hora_inicio'] ?: '00:00:00';
    $horaFin = $evento['hora_fin'] ?: $horaInicio;

    return [
        'id' => (int) $evento['id'],
        'title' => $evento['titulo'],
        'start' => $evento['fecha_inicio'] . 'T' . substr($horaInicio, 0, 8),
        'end' => $evento['fecha_fin'] . 'T' . substr($horaFin, 0, 8),
        'backgroundColor' => $evento['color_evento'],
        'borderColor' => $evento['color_evento'],
        'textColor' => '#ffffff',
        'extendedProps' => [
            'descripcion' => $evento['descripcion'],
            'ubicacion' => $evento['ubicacion'],
            'responsable' => $evento['responsable_nombre'],
            'responsable_email' => $evento['email'] ?? '',
            'cantidad_personas' => (int) $evento['cantidad_personas'],
            'con_audio' => (int) $evento['con_audio'],
            'musica_ambiental' => (int) $evento['musica_ambiental'],
            'solo_presentacion' => (int) $evento['solo_presentacion'],
            'tipo_evento' => $evento['tipo_evento'],
            'estado' => $evento['estado'],
            'observaciones_logisticas' => $evento['observaciones_logisticas'],
            'hora_inicio' => substr($horaInicio, 0, 5),
            'hora_fin' => substr($horaFin, 0, 5),
            'requerimientos' => eventos_resumen_tecnico($evento),
        ],
    ];
}

function eventos_contexto_dashboard()
{
    $eventos = eventos_obtener_eventos();
    $hoy = date('Y-m-d');
    $inicioSemana = date('Y-m-d', strtotime('monday this week'));
    $finSemana = date('Y-m-d', strtotime('sunday this week'));
    $inicioMes = date('Y-m-01');
    $finMes = date('Y-m-t');

    $eventosHoy = [];
    $proximos = [];
    $resumen = ['hoy' => 0, 'semana' => 0, 'mes' => 0, 'cancelados' => 0];
    $requerimientos = ['audio' => 0, 'musica_ambiental' => 0, 'solo_presentacion' => 0];

    foreach ($eventos as $evento) {
        if ($evento['fecha_inicio'] === $hoy) {
            $resumen['hoy']++;
            $eventosHoy[] = $evento;
        }
        if ($evento['fecha_inicio'] >= $inicioSemana && $evento['fecha_inicio'] <= $finSemana) {
            $resumen['semana']++;
        }
        if ($evento['fecha_inicio'] >= $inicioMes && $evento['fecha_inicio'] <= $finMes) {
            $resumen['mes']++;
        }
        if ($evento['estado'] === 'cancelado') {
            $resumen['cancelados']++;
        }
        if ($evento['fecha_inicio'] >= $hoy && count($proximos) < 6) {
            $proximos[] = $evento;
        }
        $requerimientos['audio'] += (int) $evento['con_audio'];
        $requerimientos['musica_ambiental'] += (int) $evento['musica_ambiental'];
        $requerimientos['solo_presentacion'] += (int) $evento['solo_presentacion'];
    }

    return [
        'resumen' => $resumen,
        'eventos_hoy' => $eventosHoy,
        'proximos' => $proximos,
        'requerimientos' => $requerimientos,
        'responsables' => eventos_obtener_responsables(),
        'tipo_colores' => eventos_tipo_colores(),
    ];
}

function eventos_historial_columnas()
{
    static $columnas = null;
    if ($columnas !== null) {
        return $columnas;
    }

    $db = eventos_db();
    $resultado = @$db->consulta("SHOW COLUMNS FROM eventos_historial");
    $columnas = [];
    if (!$resultado) {
        return $columnas;
    }
    while ($row = $db->fetch_assoc($resultado)) {
        $columnas[] = $row['Field'];
    }
    return $columnas;
}

function eventos_registrar_historial($eventoId, $accion, $descripcion, $usuarioId)
{
    $db = eventos_db();
    $columnas = eventos_historial_columnas();
    if (!$columnas) {
        return false;
    }

    $mapa = [
        'evento_id' => $eventoId,
        'id_evento' => $eventoId,
        'accion' => $accion,
        'tipo_accion' => $accion,
        'descripcion' => $descripcion,
        'detalle' => $descripcion,
        'observacion' => $descripcion,
        'usuario_id' => $usuarioId,
        'id_usuario' => $usuarioId,
        'creado_en' => date('Y-m-d H:i:s'),
        'fecha_registro' => date('Y-m-d H:i:s'),
        'fecha_creacion' => date('Y-m-d H:i:s'),
    ];

    $campos = [];
    $placeholders = [];
    $types = '';
    $values = [];

    foreach ($mapa as $columna => $valor) {
        if (in_array($columna, $columnas, true)) {
            $campos[] = $columna;
            $placeholders[] = '?';
            $types .= is_int($valor) ? 'i' : 's';
            $values[] = $valor;
        }
    }

    if (!$campos) {
        return false;
    }

    $stmt = $db->prepare("INSERT INTO eventos_historial (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")");
    $stmt->bind_param($types, ...$values);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function eventos_enviar_correo(array $evento, array $responsable)
{
    return eventos_enviar_correo_evento('creado', $evento, $responsable);
}

function eventos_correo_h($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function eventos_correo_formatear_valor($campo, $valor)
{
    if ($valor === null || $valor === '') {
        return 'Sin informacion';
    }

    if (in_array($campo, ['con_audio', 'musica_ambiental', 'solo_presentacion'], true)) {
        return (int) $valor === 1 ? 'Si' : 'No';
    }

    if (in_array($campo, ['hora_inicio', 'hora_fin'], true)) {
        return substr((string) $valor, 0, 5);
    }

    if ($campo === 'responsable_id') {
        $responsable = eventos_obtener_responsable((int) $valor);
        return $responsable['nombre_completo'] ?? (string) $valor;
    }

    if ($campo === 'cantidad_personas') {
        return (string) ((int) $valor);
    }

    return (string) $valor;
}

function eventos_correo_detalle_html(array $evento)
{
    $items = [
        'Titulo' => $evento['titulo'] ?? '',
        'Descripcion' => $evento['descripcion'] ?? '',
        'Fecha inicio' => $evento['fecha_inicio'] ?? '',
        'Hora inicio' => eventos_correo_formatear_valor('hora_inicio', $evento['hora_inicio'] ?? ($evento['hora_evento'] ?? '')),
        'Fecha termino' => $evento['fecha_fin'] ?? '',
        'Hora termino' => eventos_correo_formatear_valor('hora_fin', $evento['hora_fin'] ?? ($evento['hora_evento'] ?? '')),
        'Cantidad personas' => eventos_correo_formatear_valor('cantidad_personas', $evento['cantidad_personas'] ?? 0),
        'Responsable' => $evento['responsable_nombre'] ?? '',
        'Ubicacion' => $evento['ubicacion'] ?? '',
        'Tipo de evento' => $evento['tipo_evento'] ?? '',
        'Estado' => $evento['estado'] ?? '',
        'Color' => $evento['color_evento'] ?? '',
        'Observaciones logisticas' => $evento['observaciones_logisticas'] ?? '',
        'Con audio' => eventos_correo_formatear_valor('con_audio', $evento['con_audio'] ?? 0),
        'Musica ambiental' => eventos_correo_formatear_valor('musica_ambiental', $evento['musica_ambiental'] ?? 0),
        'Solo presentacion' => eventos_correo_formatear_valor('solo_presentacion', $evento['solo_presentacion'] ?? 0),
    ];

    $html = '';
    foreach ($items as $label => $valor) {
        $html .= '<tr>';
        $html .= '<td style="padding:10px 12px;border-bottom:1px solid #e6edf5;color:#66788d;font-size:13px;width:34%;"><strong>' . eventos_correo_h($label) . '</strong></td>';
        $html .= '<td style="padding:10px 12px;border-bottom:1px solid #e6edf5;color:#32465d;font-size:14px;">' . nl2br(eventos_correo_h($valor)) . '</td>';
        $html .= '</tr>';
    }

    return $html;
}

function eventos_correo_comparacion_html(array $anterior, array $nuevo)
{
    $campos = [
        'titulo' => 'Titulo',
        'descripcion' => 'Descripcion',
        'fecha_inicio' => 'Fecha inicio',
        'hora_inicio' => 'Hora inicio',
        'fecha_fin' => 'Fecha termino',
        'hora_fin' => 'Hora termino',
        'cantidad_personas' => 'Cantidad personas',
        'responsable_id' => 'Responsable',
        'ubicacion' => 'Ubicacion',
        'tipo_evento' => 'Tipo de evento',
        'estado' => 'Estado',
        'color_evento' => 'Color',
        'observaciones_logisticas' => 'Observaciones logisticas',
        'con_audio' => 'Con audio',
        'musica_ambiental' => 'Musica ambiental',
        'solo_presentacion' => 'Solo presentacion',
    ];

    $html = '';
    foreach ($campos as $campo => $label) {
        $valorAnterior = eventos_correo_formatear_valor($campo, $anterior[$campo] ?? '');
        $valorNuevo = eventos_correo_formatear_valor($campo, $nuevo[$campo] ?? '');
        $cambio = $valorAnterior !== $valorNuevo;

        $html .= '<tr>';
        $html .= '<td style="padding:10px 12px;border-bottom:1px solid #e6edf5;color:#66788d;font-size:13px;width:24%;"><strong>' . eventos_correo_h($label) . '</strong></td>';
        $html .= '<td style="padding:10px 12px;border-bottom:1px solid #e6edf5;color:#32465d;font-size:14px;width:33%;background:' . ($cambio ? '#fff7e8' : '#ffffff') . ';">' . nl2br(eventos_correo_h($valorAnterior)) . '</td>';
        $html .= '<td style="padding:10px 12px;border-bottom:1px solid #e6edf5;color:#32465d;font-size:14px;width:33%;background:' . ($cambio ? '#edf7ef' : '#ffffff') . ';">' . nl2br(eventos_correo_h($valorNuevo)) . '</td>';
        $html .= '</tr>';
    }

    return $html;
}

function eventos_cargar_plantilla_correo($archivo, array $reemplazos)
{
    $ruta = __DIR__ . '/correos/' . ltrim($archivo, '/');
    if (!is_file($ruta)) {
        return '';
    }

    $html = (string) file_get_contents($ruta);
    foreach ($reemplazos as $clave => $valor) {
        $html = str_replace('{' . $clave . '}', (string) $valor, $html);
    }
    return $html;
}

function eventos_enviar_correo_evento($tipo, array $evento, array $responsable, ?array $eventoAnterior = null)
{
    if (empty($responsable['email'])) {
        return false;
    }

    require_once __DIR__ . '/../class/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../class/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../class/PHPMailer/src/SMTP.php';

    $mapa = [
        'creado' => [
            'template' => 'evento_creado.php',
            'subject' => 'Nuevo evento registrado: ' . ($evento['titulo'] ?? ''),
            'badge' => 'Evento creado',
            'title' => 'Nuevo evento registrado',
            'summary' => 'Se registro un nuevo evento en el sistema y aqui puedes revisar sus caracteristicas principales.',
            'comparison' => '',
            'detail_title' => 'Detalle del evento creado',
        ],
        'actualizado' => [
            'template' => 'evento_actualizado.php',
            'subject' => 'Evento actualizado: ' . ($evento['titulo'] ?? ''),
            'badge' => 'Evento actualizado',
            'title' => 'Se realizaron cambios en un evento',
            'summary' => 'El evento asignado a tu responsabilidad fue modificado. A continuacion puedes revisar el antes y el despues.',
            'comparison' => eventos_correo_comparacion_html($eventoAnterior ?? [], $evento),
            'detail_title' => 'Datos actuales del evento',
        ],
        'cancelado' => [
            'template' => 'evento_cancelado.php',
            'subject' => 'Evento cancelado: ' . ($evento['titulo'] ?? ''),
            'badge' => 'Evento cancelado',
            'title' => 'El evento fue cancelado',
            'summary' => 'El evento asociado a tu responsabilidad fue cancelado. A continuacion puedes revisar los cambios aplicados.',
            'comparison' => eventos_correo_comparacion_html($eventoAnterior ?? [], $evento),
            'detail_title' => 'Datos finales del evento',
        ],
    ];

    if (!isset($mapa[$tipo])) {
        return false;
    }

    $config = $mapa[$tipo];
    $html = eventos_cargar_plantilla_correo($config['template'], [
        'badge' => $config['badge'],
        'title' => $config['title'],
        'summary' => $config['summary'],
        'nombreResponsable' => eventos_correo_h($responsable['nombre_completo'] ?? ''),
        'detalleTitulo' => $config['detail_title'],
        'detalleHtml' => eventos_correo_detalle_html($evento),
        'comparacionHtml' => $config['comparison'],
        'anio' => date('Y'),
    ]);

    if ($html === '') {
        return false;
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $destinatarioEmail = 'cm.jorquerag@gmail.com';
        $destinatarioNombre = $responsable['nombre_completo'] ?: 'Responsable del evento';

        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = 'smtp.itdchile.cl';
        $mail->Port = 46500;
        $mail->SMTPAuth = true;
        $mail->Username = 'm.gutierrez';
        $mail->Password = 'Seduc2024.,';
        $mail->SMTPSecure = 'tcp';
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('seduc.informa@seduc.cl', 'SeducSPA');
        $mail->addAddress($destinatarioEmail, $destinatarioNombre);
        $mail->isHTML(true);
        $mail->Subject = $config['subject'];
        $mail->Body = $html;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Error correo evento: ' . $mail->ErrorInfo);
        return false;
    }
}
