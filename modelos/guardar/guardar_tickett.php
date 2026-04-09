<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../class/PHPMailer/src/Exception.php';
require '../../class/PHPMailer/src/PHPMailer.php';
require '../../class/PHPMailer/src/SMTP.php';

include("../../class/conexion.php");
include("../../class/funciones.php");

date_default_timezone_set('America/Santiago');

$db = new MySQL("", "", "");
$funciones = new Funciones();

if (isset($_POST['accion'])) {
    switch ($_POST['accion']) {
        case 'ingresar_ticket':
            $id_usuario_session   = isset($_POST['usuarioId'])            ? intval($_POST['usuarioId']) : 0;
            $asunto_ticket        = $_POST['asunto'] ?? '';
            $descripcion_ticket   = $_POST['descripcion_ticket'] ?? '';
            $categoriaSelect      = $_POST['categoria'] ?? '';
            $estado               = $_POST['estado'] ?? '';
            $prioridad            = isset($_POST['prioridad']) && $_POST['prioridad'] !== '' ? intval($_POST['prioridad']) : null;
            $fecha_resolucion     = $_POST['fecha_resolucion'] ?? '';
            $dias_resolucion      = isset($_POST['dias_resolucion']) ? intval($_POST['dias_resolucion']) : 0;

            if ($id_usuario_session === 0 || empty($asunto_ticket) || empty($descripcion_ticket) || empty($categoriaSelect) || empty($estado)) {
                echo json_encode(['status' => 'error', 'message' => 'Faltan datos para registrar el ticket']);
                exit;
            }

            // ✅ Usando función desde la clase Funciones
            $identificador = $funciones->generarIdentificadorUnico('tickets', $db, 16);
            $fecha         = date('Y-m-d');
            $hora          = date('H:i:s');

            $sql = "SELECT id_tecnico FROM categoria_tecnico WHERE id_categoria = '$categoriaSelect'";
            $result = $db->consulta($sql);

            $tecnicos = [];
            while ($row = $db->fetch_array($result)) {
                $tecnicos[] = $row['id_tecnico'];
            }

            $id_tecnico = !empty($tecnicos) ? $tecnicos[0] : 'NULL';
            $id_estado  = (!empty($tecnicos) && $estado != 4) ? 2 : $estado;

            $sql1 = "INSERT INTO `tickets` 
                     (`id_usuario`, `asunto`, `descripcion_ticket`, `id_categoria_ticket`, `id_estado`, `identificador`, `id_tecnico`, `id_prioridad`) 
                     VALUES 
                     ('$id_usuario_session', '$asunto_ticket', '$descripcion_ticket', '$categoriaSelect', '$id_estado', '$identificador', $id_tecnico, " . ($prioridad !== null ? $prioridad : "NULL") . ")";

            if ($db->consulta($sql1)) {
                $sql2 = "SELECT `id_ticket` FROM `tickets` WHERE `identificador` = '$identificador'";
                $result = $db->consulta($sql2);
                if ($row = $db->fetch_array($result)) {
                    $id_ticket = $row['id_ticket'];

                    $sqlProceso = !empty($tecnicos) ?
                        "INSERT INTO `proceso_tickets` 
                        (`id_ticket`, `fecha_creacion_inicio`, `hora_creacion_inicio`, `fecha_asignacion_tecnico`, `hora_asignacion_tecnico`, `fecha_estimada_admin`, `dias_estimada_admin`) 
                        VALUES 
                        ('$id_ticket', '$fecha', '$hora', '$fecha', '$hora', '$fecha_resolucion', '$dias_resolucion')" :
                        "INSERT INTO `proceso_tickets` 
                        (`id_ticket`, `fecha_creacion_inicio`, `hora_creacion_inicio`, `fecha_estimada_admin`, `dias_estimada_admin`) 
                        VALUES 
                        ('$id_ticket', '$fecha', '$hora', '$fecha_resolucion', '$dias_resolucion')";
                    $db->consulta($sqlProceso);

                    // Adjuntos
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
                                $db->consulta($sql3);
                            }
                        }
                    }

                    // 🔹 Enviar correos si estado != 4
                    if ($estado != 4) {
                        $sql4 = "SELECT `email`, `nombre`, `apellido_paterno`, `telefono` FROM `usuarios` WHERE `id` = '$id_usuario_session'";
                        $result = $db->consulta($sql4);
                        if ($row = $db->fetch_array($result)) {
                            $emailUsuario         = $row['email'];
                            $nombreUsarioCompleto = $row['nombre'] . ' ' . $row['apellido_paterno'];
                            $telefonoUsuario      = $row['telefono'];

                            $mail = new PHPMailer(true);
                            try {
                                $mail->IsSMTP();
                                $mail->SMTPDebug = SMTP::DEBUG_OFF;
                                $mail->Host = 'smtp.itdchile.cl';
                                $mail->Port = 46500;
                                $mail->SMTPAuth = true;
                                $mail->Username = 'm.gutierrez';
                                $mail->Password = 'Seduc2024.,';
                                $mail->SMTPSecure = 'tcp';
                                $mail->CharSet = 'UTF-8';

                                $mail->setFrom('seduc.informa@seduc.cl', 'SeducSPA');
                                $mail->addAddress($emailUsuario);
                                $mail->isHTML(true);
                                $mail->Subject = 'Nuevo Ticket Creado';

                                $mailBody = file_get_contents('../../class/correos/ingreso_ticket.php');
                                $mailBody = str_replace('{identificador}', htmlspecialchars($identificador), $mailBody);
                                $mailBody = str_replace('{codigo}', htmlspecialchars($id_ticket), $mailBody);
                                $mailBody = str_replace('{fecha}', htmlspecialchars($fecha), $mailBody);
                                $mailBody = str_replace('{hora}', htmlspecialchars($hora), $mailBody);
                                $mailBody = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $mailBody);
                                $mailBody = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $mailBody);
                                $mailBody = str_replace('{descripcion}', nl2br(htmlspecialchars(trim(html_entity_decode(strip_tags((string) $descripcion_ticket), ENT_QUOTES | ENT_HTML5, 'UTF-8')), ENT_QUOTES, 'UTF-8')), $mailBody);
                                $mail->Body = $mailBody;
                                $mail->send();

                                // Correo al técnico
                                if ($id_tecnico !== 'NULL') {
                                    $sql5 = "SELECT `email`, `nombre`, `apellido_paterno`, `telefono` FROM `usuarios` WHERE `id` = $id_tecnico";
                                    $result = $db->consulta($sql5);
                                    if ($row = $db->fetch_array($result)) {
                                        $emailTecnico          = $row['email'];
                                        $nombreTecnicoCompleto = $row['nombre'] . ' ' . $row['apellido_paterno'];

                                        $mail->clearAddresses();
                                        $mail->addAddress($emailTecnico);
                                        $mail->Subject = 'Nuevo Ticket Asignado';

                                        $mailBodyTecnico = file_get_contents('../../class/correos/ticket_tecnico.php');
                                        $mailBodyTecnico = str_replace('{identificador}', htmlspecialchars($identificador), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{codigo}', htmlspecialchars($id_ticket), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{fecha}', htmlspecialchars($fecha), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{hora}', htmlspecialchars($hora), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{nombreTecnicoCompleto}', htmlspecialchars($nombreTecnicoCompleto), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{descripcion}', nl2br(htmlspecialchars(trim(html_entity_decode(strip_tags((string) $descripcion_ticket), ENT_QUOTES | ENT_HTML5, 'UTF-8')), ENT_QUOTES, 'UTF-8')), $mailBodyTecnico);
                                        $mail->Body = $mailBodyTecnico;
                                        $mail->send();
                                    }
                                }
                            } catch (Exception $e) {
                                error_log("Error al enviar correo: " . $mail->ErrorInfo);
                            }
                        }
                    }

                    echo json_encode(['status' => 'success', 'message' => 'Ticket creado correctamente']);
                    exit;
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'No se pudo recuperar el ID del ticket']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al insertar ticket']);
            }
            break;
    }
}
?>
