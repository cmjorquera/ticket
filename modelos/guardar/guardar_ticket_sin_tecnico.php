<?php
/**
 * modelos/guardar/guardar_ticket_sin_tecnico.php
 * Crea ticket categoría=10 (sin técnico) + envía correos a usuario y administradores.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../class/PHPMailer/src/Exception.php';
require '../../class/PHPMailer/src/PHPMailer.php';
require '../../class/PHPMailer/src/SMTP.php';

include("../../class/conexion.php");
include("../../class/funciones.php");

// === DEBUG (opcional; quitar en producción) ===
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Base absoluta para rutas de plantillas (sube desde /modelos/guardar/ dos niveles)
define('APP_BASE', dirname(__DIR__, 2)); // .../sistema_ticket (ajusta si tu raíz difiere)

date_default_timezone_set('America/Santiago');

$db        = new MySQL("", "", "");
$funciones = new Funciones();

if (isset($_POST['accion']) && $_POST['accion'] === 'ingresar_ticket') {

    $id_usuario_session = isset($_POST['usuarioId']) ? intval($_POST['usuarioId']) : 0;
    $asunto_ticket      = $_POST['asunto'] ?? '';
    $descripcion_ticket = $_POST['descripcion_ticket'] ?? '';
    $categoriaSelect    = $_POST['categoria'] ?? '';
    $estado             = $_POST['estado'] ?? '';
    $prioridad          = (isset($_POST['prioridad']) && $_POST['prioridad'] !== '') ? intval($_POST['prioridad']) : null;
    $fecha_resolucion   = $_POST['fecha_resolucion'] ?? '';
    $dias_resolucion    = isset($_POST['dias_resolucion']) ? intval($_POST['dias_resolucion']) : 0;

    // Validaciones mínimas
    if ($id_usuario_session === 0 || $asunto_ticket === '' || $descripcion_ticket === '' || $categoriaSelect === '' || $estado === '') {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos para registrar el ticket (sin técnico)']);
        exit;
    }

    // Este endpoint es sólo para categoría = 10
    if ((string)$categoriaSelect !== '10') {
        echo json_encode(['status' => 'error', 'message' => 'Este endpoint es sólo para categoría = 10']);
        exit;
    }

    $identificador = $funciones->generarIdentificadorUnico('tickets', $db, 16);
    $fecha = date('Y-m-d');
    $hora  = date('H:i:s');

    // SIN técnico
    $id_tecnico = 'NULL';

    // Respetamos el estado enviado por POST
    $id_estado = $estado;

    // Insert ticket
    $sql1 = "INSERT INTO `tickets`
            (`id_usuario`, `asunto`, `descripcion_ticket`, `id_categoria_ticket`, `id_estado`, `identificador`, `id_tecnico`, `id_prioridad`)
            VALUES
            ('$id_usuario_session', '$asunto_ticket', '$descripcion_ticket', '$categoriaSelect', '$id_estado', '$identificador', $id_tecnico, " . ($prioridad !== null ? $prioridad : "NULL") . ")";

    if ($db->consulta($sql1)) {
        // Recuperar id_ticket
        $sql2   = "SELECT `id_ticket` FROM `tickets` WHERE `identificador` = '$identificador'";
        $result = $db->consulta($sql2);

        if ($row = $db->fetch_array($result)) {
            $id_ticket = $row['id_ticket'];

            // Insert proceso SIN técnico
            $sqlProceso = "INSERT INTO `proceso_tickets`
                           (`id_ticket`, `fecha_creacion_inicio`, `hora_creacion_inicio`, `fecha_estimada_admin`, `dias_estimada_admin`)
                           VALUES
                           ('$id_ticket', '$fecha', '$hora', '$fecha_resolucion', '$dias_resolucion')";
            $db->consulta($sqlProceso);

            // === Adjuntos (igual a tu flujo actual) ===
            if (isset($_FILES['archivo']) && is_array($_FILES['archivo']['name'])) {
                $adjuntos  = $_FILES['archivo'];
                $num_files = count($adjuntos['name']);

                for ($i = 0; $i < $num_files; $i++) {
                    $nombreArchivo = $db->escape_string($adjuntos['name'][$i]);
                    $rutaTemporal  = $adjuntos['tmp_name'][$i];
                    $rutaDestino   = '../../archivos/' . uniqid() . '-' . $nombreArchivo;

                    if (move_uploaded_file($rutaTemporal, $rutaDestino)) {
                        $rutaRelativa = 'archivos/' . basename($rutaDestino);

                        $sql3 = "INSERT INTO `archivos_adjuntos_ticket` (`id_ticket`, `adjunto`) VALUES ('$id_ticket', '$rutaRelativa')";
                        if (!$db->consulta($sql3)) {
                            error_log("Error al insertar archivo adjunto: " . $db->error);
                        }
                    }
                }
            }

            // === Responder al frontend rápido (como haces hoy) ===
            ignore_user_abort(true);
            header("Connection: close");
            ob_start();
            echo json_encode(['status' => 'success', 'message' => 'Ticket (cat=10) creado correctamente, sin técnico asignado']);
            $size = ob_get_length();
            header("Content-Length: $size");
            ob_end_flush();
            flush();

            // ===== Envío de correos (en “segundo plano” post-respuesta) =====

            // 1) Correo al USUARIO (misma lógica que en tu guardar_ticket1.php) 
            $sqlUsuario = "SELECT `email`, `nombre`, `apellido_paterno` FROM `usuarios` WHERE `id` = '$id_usuario_session'";
            $resUsuario = $db->consulta($sqlUsuario);
            if ($rowUsuario = $db->fetch_array($resUsuario)) {

                $emailUsuario         = $rowUsuario['email'];
                $nombreUsarioCompleto = trim($rowUsuario['nombre'] . ' ' . $rowUsuario['apellido_paterno']);

                $mail = new PHPMailer(true);
                try {
                    // === SMTP, igual que en tu archivo actual === 
                    $mail->IsSMTP();
                    $mail->SMTPDebug  = SMTP::DEBUG_OFF;
                    $mail->Host       = 'smtp.itdchile.cl';
                    $mail->Port       = 46500;
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'm.gutierrez';
                    $mail->Password   = 'Seduc2024.,';
                    $mail->SMTPSecure = 'tcp';
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom('seduc.informa@seduc.cl', 'SeducSPA');
                    $mail->addAddress($emailUsuario, $nombreUsarioCompleto);
                    $mail->isHTML(true);
                    $mail->Subject = 'Nuevo Ticket Creado';

                    // Plantilla del usuario (igual a tu flujo actual)
                    $tplUsuario = APP_BASE . '/class/correos/ingreso_ticket.php';
                    if (!file_exists($tplUsuario)) {
                        error_log("TPL usuario no encontrado: " . $tplUsuario);
                    }
                    $body = file_get_contents($tplUsuario);
                    $body = str_replace('{identificador}', htmlspecialchars($identificador), $body);
                    $body = str_replace('{codigo}', htmlspecialchars($id_ticket), $body);
                    $body = str_replace('{fecha}', htmlspecialchars($fecha), $body);
                    $body = str_replace('{hora}', htmlspecialchars($hora), $body);
                    $body = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $body);
                    $body = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $body);
                    $body = str_replace('{descripcion}', htmlspecialchars($descripcion_ticket), $body);

                    $mail->Body = $body;

                    if (!$mail->send()) {
                        error_log('PHPMailer usuario (cat=10) falló: ' . $mail->ErrorInfo);
                    }

                    // 2) Correo a ADMINISTRADORES (IDs 7,8,42) usando ././class/correos/sin_tecnico.php
                    $sqlAdmins = "
                        SELECT id,
                               CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno) AS nombre_completo,
                               email
                        FROM usuarios
                        WHERE id IN (7,8,42)
                          AND email IS NOT NULL
                          AND email <> ''
                    ";
                    $resAdmins = $db->consulta($sqlAdmins);
                    $admins = [];
                    while ($rowA = $db->fetch_array($resAdmins)) {
                        if (filter_var($rowA['email'], FILTER_VALIDATE_EMAIL)) {
                            $admins[] = $rowA;
                        }
                    }

                    if (!empty($admins)) {
                        // Reusar la misma instancia
                        if (method_exists($mail, 'clearAllRecipients')) {
                            $mail->clearAllRecipients();
                        } else {
                            $mail->ClearAddresses();
                            $mail->ClearCCs();
                            $mail->ClearBCCs();
                            $mail->ClearReplyTos();
                        }

                        // Si prefieres ocultar correos entre admins, usa addBCC en vez de addAddress
                        foreach ($admins as $adm) {
                            $mail->addAddress($adm['email'], $adm['nombre_completo']);
                        }

                        $mail->Subject = 'Ticket sin técnico asignado';

                        // Plantilla específica para administradores
                        $tplAdmin = APP_BASE . '/class/correos/sin_tecnico.php';
                        if (!file_exists($tplAdmin)) {
                            error_log("TPL admin no encontrado: " . $tplAdmin);
                        }
                        $bodyAdmins = @file_get_contents($tplAdmin);
                        if ($bodyAdmins === false) { $bodyAdmins = "<p>El usuario {nombreUsarioCompleto} reportó un problema (Ticket A-0{codigo}). Se requiere asignar técnico.</p>"; }

                        // Placeholders disponibles (coinciden con los que ya usas)
                        $bodyAdmins = str_replace('{identificador}', htmlspecialchars($identificador), $bodyAdmins);
                        $bodyAdmins = str_replace('{codigo}', htmlspecialchars($id_ticket), $bodyAdmins);
                        $bodyAdmins = str_replace('{fecha}', htmlspecialchars($fecha), $bodyAdmins);
                        $bodyAdmins = str_replace('{hora}', htmlspecialchars($hora), $bodyAdmins);
                        $bodyAdmins = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $bodyAdmins);
                        // Para admins no hay técnico asignado:
                        $bodyAdmins = str_replace('{nombreTecnicoCompleto}', '-', $bodyAdmins);
                        $bodyAdmins = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $bodyAdmins);
                        $bodyAdmins = str_replace('{descripcion}', htmlspecialchars($descripcion_ticket), $bodyAdmins);

                        $mail->Body = $bodyAdmins;

                        if (!$mail->send()) {
                            error_log('PHPMailer admins (cat=10) falló: ' . $mail->ErrorInfo);
                        }
                    }

                } catch (Exception $e) {
                    error_log('Excepción PHPMailer cat=10: ' . $e->getMessage());
                }
            }

        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo recuperar el ID del ticket']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al insertar ticket']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
}
