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

if (isset($_POST['accion']) && $_POST['accion'] === 'ingresar_ticket') {
    $id_usuario_session   = isset($_POST['usuarioId']) ? intval($_POST['usuarioId']) : 0;
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

    $identificador = $funciones->generarIdentificadorUnico('tickets', $db, 16);
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');

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

            // Archivos
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
                                if ($db->consulta($sql3)) {
                                    echo "Archivo adjunto insertado correctamente<br>";
                                } else {
                                    echo "Error al insertar archivo adjunto: " . $db->error . "<br>";
                                }
                    }
                }
                
                
            }

            // 🔹 Responder
            ignore_user_abort(true);
            header("Connection: close");
            ob_start();
            echo json_encode(['status' => 'success', 'message' => 'Ticket creado correctamente']);
            $size = ob_get_length();
            header("Content-Length: $size");
            ob_end_flush();
            flush();

            // 🔽 Enviar correos (asíncrono en segundo plano)
       if ($estado != 4) {
                    // Correo al usuario
                    $sqlUsuario = "SELECT `email`, `nombre`, `apellido_paterno` FROM `usuarios` WHERE `id` = '$id_usuario_session'";
                    $resUsuario = $db->consulta($sqlUsuario);
                    if ($rowUsuario = $db->fetch_array($resUsuario)) {
                        $emailUsuario = $rowUsuario['email'];
                        $nombreUsarioCompleto = $rowUsuario['nombre'] . ' ' . $rowUsuario['apellido_paterno'];

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

                            $body = file_get_contents('../../class/correos/ingreso_ticket.php');
                            $body = str_replace('{identificador}', htmlspecialchars($identificador), $body);
                            $body = str_replace('{codigo}', htmlspecialchars($id_ticket), $body);
                            $body = str_replace('{fecha}', htmlspecialchars($fecha), $body);
                            $body = str_replace('{hora}', htmlspecialchars($hora), $body);
                            $body = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $body);
                            $body = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $body);
                            $body = str_replace('{descripcion}', htmlspecialchars($descripcion_ticket), $body);
                            $mail->Body = $body;
                            $mail->send();

                            // Si hay técnico asignado
                            if ($id_tecnico !== 'NULL') {
                                $sqlTec = "SELECT `email`, `nombre`, `apellido_paterno` FROM `usuarios` WHERE `id` = $id_tecnico";
                                $resTec = $db->consulta($sqlTec);
                                if ($rowTec = $db->fetch_array($resTec)) {
                                    $emailTecnico = $rowTec['email'];
                                    $nombreTecnicoCompleto = $rowTec['nombre'] . ' ' . $rowTec['apellido_paterno'];

                                    $mail->clearAddresses();
                                    $mail->addAddress($emailTecnico);
                                    $mail->Subject = 'Nuevo Ticket Asignado';

                                    $bodyTec = file_get_contents('../../class/correos/ticket_tecnico.php');
                                    $bodyTec = str_replace('{identificador}', htmlspecialchars($identificador), $bodyTec);
                                    $bodyTec = str_replace('{codigo}', htmlspecialchars($id_ticket), $bodyTec);
                                    $bodyTec = str_replace('{fecha}', htmlspecialchars($fecha), $bodyTec);
                                    $bodyTec = str_replace('{hora}', htmlspecialchars($hora), $bodyTec);
                                    $bodyTec = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $bodyTec);
                                    $bodyTec = str_replace('{nombreTecnicoCompleto}', htmlspecialchars($nombreTecnicoCompleto), $bodyTec);
                                    $bodyTec = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $bodyTec);
                                    $bodyTec = str_replace('{descripcion}', htmlspecialchars($descripcion_ticket), $bodyTec);
                                    $mail->Body = $bodyTec;
                                    $mail->send();
                                }
                            }

                        } catch (Exception $e) {
                            error_log("Error al enviar correo: " . $mail->ErrorInfo);
                        }
                    }
        }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo recuperar el ID del ticket']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al insertar ticket']);
    }
}