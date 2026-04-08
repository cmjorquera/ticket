<?php
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');
require_once("../../class/PHPMailer/src/PHPMailer.php");
require_once("../../class/PHPMailer/src/Exception.php");
require_once("../../class/PHPMailer/src/SMTP.php");


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Funciones para generar y buscar identificador
function generarIdentificadorUnico($tabla, $bd, $num_caracteres) {
    $columna = 'identificador';
    $bl = 0;
    while ($bl == 0) {
        $identificador = generarIdentificador($num_caracteres);
        if (buscarIdentificador($bd, $tabla, $columna, $identificador) == "") {
            $bl = 1;
        }
    }
    return $identificador;
}

function generarIdentificador($num_caracteres) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $identificador = '';
    for ($i = 0; $i < $num_caracteres; $i++) {
        $identificador .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $identificador;
}

function buscarIdentificador($bd, $tabla, $columna, $identificador) {
    $query = "SELECT $columna FROM $tabla WHERE $columna = '$identificador'";
    $resultado = $bd->consulta($query);
    if ($bd->num_rows($resultado) > 0) {
        $fila = $bd->fetch_array($resultado);
        return $fila[$columna];
    } else {
        return "";
    }
}

// Conectar a la base de datos
$db = new MySQL("", "", "");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

    switch ($accion) {
        case 'ingresar_ticket':
            $id_usuario_session         = isset($_POST['usuarioId'])            ? intval($_POST['usuarioId']) : 0;
            $asunto_ticket              = isset($_POST['asunto'])               ? $_POST['asunto'] : '';
            $descripcion_ticket         = isset($_POST['descripcion_ticket'])   ? $_POST['descripcion_ticket'] : '';
            $categoriaSelect            = isset($_POST['categoria'])            ? $_POST['categoria'] : '';
            $estado                     = isset($_POST['estado'])               ? $_POST['estado'] : '';
            $prioridad                  = isset($_POST['prioridad']) && $_POST['prioridad'] !== '' ? intval($_POST['prioridad']) : null;
            $fecha_resolucion           = isset($_POST['fecha_resolucion'])     ? $_POST['fecha_resolucion'] : '';
            $dias_resolucion            = isset($_POST['dias_resolucion'])      ? intval($_POST['dias_resolucion']) : 0;
        
            echo "ID: $id_usuario_session, Asunto: $asunto_ticket, Descripción: $descripcion_ticket, Categoría: $categoriaSelect, Estado: $estado, Prioridad: $prioridad, Fecha Resolución: $fecha_resolucion, Días Resolución: $dias_resolucion<br>";
            if ($id_usuario_session === 0 || empty($asunto_ticket) || empty($descripcion_ticket) || empty($categoriaSelect) || empty($estado)) {
                echo json_encode(['status' => 'error', 'message' => 'Faltan datos para registrar el ticket']);
                exit;
            }
        
            $identificador = generarIdentificadorUnico('tickets', $db, 16);
            $fecha = date('Y-m-d');
            $hora = date('H:i:s');
        
            // Consultar los técnicos asociados a la categoría seleccionada
            $sql = "SELECT id_tecnico FROM categoria_tecnico WHERE id_categoria = '$categoriaSelect'";
            $result = $db->consulta($sql);
        
            $tecnicos = [];
            while ($row = $db->fetch_array($result)) {
                $tecnicos[] = $row['id_tecnico'];   // aca rescato el id_del usuario que tiene asocciado al categoria que se seleciono en el combo box de seleccion de categoria en ticket 
            }
        
            // Determinar el técnico y el estado
            if (!empty($tecnicos)) {
                $id_tecnico = $tecnicos[0];
                $id_estado = ($estado == 4) ? 4 : 2;
            } else {
                $id_tecnico = 'NULL';
                $id_estado = $estado;
            }
        
            // Construir la consulta SQL para insertar en la tabla `tickets`
            $sql1 = "INSERT INTO `tickets` 
            (`id_usuario`, `asunto`, `descripcion_ticket`, `id_categoria_ticket`, `id_estado`, `identificador`, `id_tecnico`, `id_prioridad`) 
            VALUES 
            ('$id_usuario_session', '$asunto_ticket', '$descripcion_ticket', '$categoriaSelect', '$id_estado', '$identificador', $id_tecnico, " . ($prioridad !== null ? $prioridad : "NULL") . ")";
            if ($db->consulta($sql1)) {
                // Obtener el id_ticket recién creado usando el identificador
                $sql2 = "SELECT `id_ticket` FROM `tickets` WHERE `identificador` = '$identificador'";
                $result = $db->consulta($sql2);
                if ($row = $db->fetch_array($result)) {
                    $id_ticket = $row['id_ticket'];
        
                    // Insertar en la tabla `proceso_tickets`
                    if (!empty($tecnicos)) {
                        $sqlProceso = "INSERT INTO `proceso_tickets` 
                                    (`id_ticket`, `fecha_creacion_inicio`, `hora_creacion_inicio`, `fecha_asignacion_tecnico`, `hora_asignacion_tecnico`, `fecha_estimada_admin`, `dias_estimada_admin`) 
                                    VALUES 
                                    ('$id_ticket', '$fecha', '$hora', '$fecha', '$hora', '$fecha_resolucion', '$dias_resolucion')";
                    } else {
                        $sqlProceso = "INSERT INTO `proceso_tickets` 
                                    (`id_ticket`, `fecha_creacion_inicio`, `hora_creacion_inicio`, `fecha_estimada_admin`, `dias_estimada_admin`) 
                                    VALUES 
                                    ('$id_ticket', '$fecha', '$hora', '$fecha_resolucion', '$dias_resolucion')";
                    }
                    $db->consulta($sqlProceso);
        
                    // Procesar los archivos adjuntos
                    if (isset($_FILES['archivo']) && is_array($_FILES['archivo']['name'])) {
                        $adjuntos        = $_FILES['archivo'];
                        $num_files       = count($adjuntos['name']);
        
                        for ($i = 0; $i < $num_files; $i++) {
                            $nombreArchivo = $db->escape_string($adjuntos['name'][$i]);
                            $rutaTemporal = $adjuntos['tmp_name'][$i];
        
                            // Generar una ruta única para el archivo
                            $rutaDestino = '../../archivos/' . uniqid() . '-' . $nombreArchivo;
        
                            // Mover el archivo a la ruta destino
                            if (move_uploaded_file($rutaTemporal, $rutaDestino)) {
                                $rutaRelativa = 'archivos/' . basename($rutaDestino);
        
                                // Insertar el archivo adjunto en la base de datos
                                $sql3 = "INSERT INTO `archivos_adjuntos_ticket` (`id_ticket`, `adjunto`) VALUES ('$id_ticket', '$rutaRelativa')";
                                if ($db->consulta($sql3)) {
                                    echo "Archivo adjunto insertado correctamente<br>";
                                } else {
                                    echo "Error al insertar archivo adjunto: " . $db->error . "<br>";
                                }
                            }
                        }
                    }
        
                    // Enviar correos solo si el estado no es 4 (borrador)
                    if ($estado != 4) {
                        // Obtener el correo electrónico del usuario
                        $sql4 = "SELECT `email`, `nombre`, `apellido_paterno`, `telefono` FROM `usuarios` WHERE `id` = '$id_usuario_session'";
                        $result = $db->consulta($sql4);
                        if ($row = $db->fetch_array($result)) {
                            $emailUsuario           = $row['email'];
                            $nombreUsarioCompleto   = $row['nombre'] . ' ' . $row['apellido_paterno'];
                            $telefonoUsuario        = $row['telefono'];
        
                            // Enviar correo electrónico al usuario usando PHPMailer
                            $mail = new PHPMailer(true);
                            try {
                                $mail->IsSMTP();
                                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
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
        
                                // Cargar plantilla de correo y reemplazar los detalles del ticket
                                $mailBody = file_get_contents('../../class/correos/ingreso_ticket.php');
                                $mailBody = str_replace('{identificador}', htmlspecialchars($identificador), $mailBody);
                                $mailBody = str_replace('{codigo}', htmlspecialchars($id_ticket), $mailBody);
                                $mailBody = str_replace('{fecha}', htmlspecialchars($fecha), $mailBody);
                                $mailBody = str_replace('{hora}', htmlspecialchars($hora), $mailBody);
                                $mailBody = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $mailBody);
                                $mailBody = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $mailBody);
                                $mailBody = str_replace('{descripcion}', htmlspecialchars($descripcion_ticket), $mailBody);
                                $mail->Body = $mailBody;
        
                                $mail->send();
                                echo 'Correo enviado correctamente al usuario<br>';
                            } catch (Exception $e) {
                                error_log('Mailer Error: ' . $mail->ErrorInfo);
                                echo "El correo no se pudo enviar al usuario. Mailer Error: {$mail->ErrorInfo}<br>";
                            }
        
                            // Obtener el correo electrónico y nombre del técnico
                            $sql5 = "SELECT `email`, `nombre`, `apellido_paterno`, `telefono` FROM `usuarios` WHERE `id` = $id_tecnico";
                            $result = $db->consulta($sql5);
                            if ($row = $db->fetch_array($result)) {
                                $emailTecnico = $row['email'];
                                $nombreTecnicoCompleto = $row['nombre'] . ' ' . $row['apellido_paterno'];
                                $telefonoTecnico = $row['telefono'];
        
                                // Enviar correo electrónico al técnico usando PHPMailer
                                try {
                                    $mail->clearAddresses(); // Limpiar las direcciones anteriores
                                    $mail->addAddress($emailTecnico);
                                    $mail->Subject = 'Nuevo Ticket Asignado';
        
                                    // Cargar plantilla de correo y reemplazar los detalles del ticket
                                    $mailBodyTecnico = file_get_contents('../../class/correos/ticket_tecnico.php');
                                    $mailBodyTecnico = str_replace('{identificador}', htmlspecialchars($identificador), $mailBodyTecnico);
                                    $mailBodyTecnico = str_replace('{codigo}', htmlspecialchars($id_ticket), $mailBodyTecnico);
                                    $mailBodyTecnico = str_replace('{fecha}', htmlspecialchars($fecha), $mailBodyTecnico);
                                    $mailBodyTecnico = str_replace('{hora}', htmlspecialchars($hora), $mailBodyTecnico);
                                    $mailBodyTecnico = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $mailBodyTecnico);
                                    $mailBodyTecnico = str_replace('{nombreTecnicoCompleto}', htmlspecialchars($nombreTecnicoCompleto), $mailBodyTecnico);
                                    $mailBodyTecnico = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $mailBodyTecnico);
                                    $mailBodyTecnico = str_replace('{descripcion}', htmlspecialchars($descripcion_ticket), $mailBodyTecnico);
                                    $mail->Body = $mailBodyTecnico;
        
                                    $mail->send();
                                    echo 'Correo enviado correctamente al técnico';
                                } catch (Exception $e) {
                                    error_log('Mailer Error: ' . $mail->ErrorInfo);
                                    echo "El correo no se pudo enviar al técnico. Mailer Error: {$mail->ErrorInfo}";
                                }
                            } else {
                                echo json_encode(['status' => 'error', 'message' => 'Error al obtener el correo del técnico']);
                            }
                        } else {
                            echo json_encode(['status' => 'error', 'message' => 'Error al obtener el correo del usuario']);
                        }
                    } else {
                        echo json_encode(['status' => 'success', 'message' => 'Borrador guardado correctamente']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al obtener el ID del ticket']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al insertar en la tabla tickets: ' . $db->error]);
            }
            break;
        
        

        case 'comenzar_proceso':
                if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
                    echo "ID de ticket no válido.";
                    break;
                }
            
                $id_ticket = $_POST['id'];
                $fecha      = date("Y-m-d");
                $hora       = date("H:i:s");
            
                // Capturar los datos adicionales
                $prioridad              = $_POST['prioridad'];
                $diasResolucion         = $_POST['dias_estimados'] ?? null;
                $fecha_resolucion       = $_POST['fecha_estimada'] ?? null;


            
                // Actualización en la tabla `proceso_tickets`
                $sql = "UPDATE `proceso_tickets` SET                     
                    `fecha_estimada_admin`     = '$fecha_resolucion', 
                    `dias_estimada_admin`      = '$diasResolucion',  
                    `fecha_comienzo_ticket`    = '$fecha',      
                    `hora_comienzo_ticket`     = '$hora'
                WHERE `id_ticket`   = '$id_ticket'";

                $result = $db->guardar($sql);
            
                // Actualización de la tabla `tickets`
                $sql1 = "UPDATE `tickets` SET 
                    `id_estado`         = 3,           
                    `id_prioridad`      = '$prioridad'
                    WHERE `id_ticket`   = '$id_ticket'";   
                    $result1 = $db->guardar($sql1);
                // echo "*****".$sql1."********";

                // Inserción en la tabla `avance_tecnicos`
                // $sql2 = "INSERT INTO `avance_tecnicos`(`id_ticket`, `accion`, `fecha_avance`, `hora_avance`)
                //          VALUES ('$id_ticket', 'Inicio Ticket', '$fecha', '$hora')";
                // $result2 = $db->guardar($sql2);

                
            
                // Enviar correo electrónico al usuario
                $sql3 = "SELECT u.email, u.nombre, u.apellido_paterno, t.asunto, t.descripcion_ticket, u2.nombre AS nombre_tecnico, u2.apellido_paterno AS apePaternoTecnico
                         FROM tickets t
                         JOIN usuarios u ON t.id_usuario = u.id
                         JOIN usuarios u2 ON t.id_tecnico = u2.id
                         WHERE t.id_ticket = '$id_ticket'";
                $result3 = $db->consulta($sql3);
            
                if ($row = $db->fetch_array($result3)) {
                    $emailUsuario           = $row['email'];
                    $nombreUsuarioCompleto  = $row['nombre'] . ' ' . $row['apellido_paterno'];
                    $asuntoTicket           = $row['asunto'];
                    $descripcionTicket      = $row['descripcion_ticket'];
                    $nombreTecnico          = $row['nombre_tecnico'] . ' ' . $row['apePaternoTecnico'];
            
                    $mail = new PHPMailer(true);
                    try {
                        $mail->IsSMTP();
                        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
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
                        $mail->Subject = 'Ticket en Proceso';
            
                        // Cargar plantilla de correo y reemplazar los detalles del ticket
                        $mailBody = file_get_contents('../../class/correos/en_proceso_ticket.php');
                        $mailBody = str_replace('{codigo}', htmlspecialchars($id_ticket), $mailBody);
                        $mailBody = str_replace('{hora}', htmlspecialchars($hora), $mailBody);
                        $mailBody = str_replace('{nombre_tecnico}', htmlspecialchars($nombreTecnico), $mailBody);
                        $mailBody = str_replace('{asunto}', htmlspecialchars($asuntoTicket), $mailBody);
                        $mailBody = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsuarioCompleto), $mailBody);
                        $mailBody = str_replace('{fecha_resolucion}', htmlspecialchars($fecha_resolucion), $mailBody);
                        $mailBody = str_replace('{dias_administrador_estima}', htmlspecialchars($dias_estimada_admin), $mailBody);
                        $mail->Body = $mailBody;
            
                        $mail->send();
                        echo 'Correo enviado correctamente al usuario<br>';
                    } catch (Exception $e) {
                        error_log('Mailer Error: ' . $mail->ErrorInfo);
                        echo "El correo no se pudo enviar al usuario. Mailer Error: {$mail->ErrorInfo}<br>";
                    }
            
                    // Enviar correo electrónico al técnico
                    try {
                        $mail->clearAddresses(); // Limpiar las direcciones anteriores
                        $mail->addAddress($emailTecnico);
                        $mail->Subject = 'Nuevo Ticket Asignado';
            
                        // Cargar plantilla de correo y reemplazar los detalles del ticket
                        $mailBodyTecnico = file_get_contents('../../class/correos/ticket_tecnico.php');
                        $mailBodyTecnico = str_replace('{identificador}', htmlspecialchars($identificador), $mailBodyTecnico);
                        $mailBodyTecnico = str_replace('{codigo}', htmlspecialchars($id_ticket), $mailBodyTecnico);
                        $mailBodyTecnico = str_replace('{fecha}', htmlspecialchars($fecha), $mailBodyTecnico);
                        $mailBodyTecnico = str_replace('{hora}', htmlspecialchars($hora), $mailBodyTecnico);
                        $mailBodyTecnico = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsuarioCompleto), $mailBodyTecnico);
                        $mailBodyTecnico = str_replace('{nombreTecnicoCompleto}', htmlspecialchars($nombreTecnicoCompleto), $mailBodyTecnico);
                        $mailBodyTecnico = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $mailBodyTecnico);
                        $mailBodyTecnico = str_replace('{descripcion}', htmlspecialchars($descripcion_ticket), $mailBodyTecnico);
                        $mail->Body = $mailBodyTecnico;
            
                        $mail->send();
                        echo 'Correo enviado correctamente al técnico';
                    } catch (Exception $e) {
                        error_log('Mailer Error: ' . $mail->ErrorInfo);
                        echo "El correo no se pudo enviar al técnico. Mailer Error: {$mail->ErrorInfo}";
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al obtener el correo del usuario']);
                }
            
                // Verificación de resultados
                if ($result && $result1 && $result2) {
                    echo "El proceso para el ticket $id_ticket fue guardado correctamente.";
                } else {
                    echo "Error al guardar el proceso del ticket.";
                }
                break;
            
        case 'modificar_borrador':
                    $id_ticket          = isset($_POST['id']) ? intval($_POST['id']) : 0;
                    $asunto_ticket      = isset($_POST['asunto']) ? $_POST['asunto'] : '';
                    $descripcion_ticket = isset($_POST['descripcion_ticket']) ? $_POST['descripcion_ticket'] : '';
                    $categoriaSelect    = isset($_POST['id_categoria_ticket']) ? $_POST['id_categoria_ticket'] : '';
                
                    if ($id_ticket === 0 || empty($asunto_ticket) || empty($descripcion_ticket) || empty($categoriaSelect)) {
                        echo json_encode(['status' => 'error', 'message' => 'Faltan datos para modificar el borrador']);
                        exit;
                    }
                
                    // Consultar los técnicos asociados a la categoría seleccionada
                    $sql = "SELECT id_tecnico FROM categoria_tecnico WHERE id_categoria = '$categoriaSelect'";
                    $result = $db->consulta($sql);
                
                    $tecnicos = [];
                    while ($row = $db->fetch_array($result)) {
                        $tecnicos[] = $row['id_tecnico'];
                    }
                
                    // Determinar el técnico y el estado
                    if (!empty($tecnicos)) {
                        $id_tecnico = $tecnicos[0];
                        $id_estado = 2; // Asignado
                    } else {
                        $id_tecnico = 'NULL';
                        $id_estado = 1; // Recibido
                    }
                
                    // Actualizar el ticket
                    $sql1 = "UPDATE `tickets` 
                            SET `asunto` = '$asunto_ticket', 
                                `descripcion_ticket` = '$descripcion_ticket', 
                                `id_categoria_ticket` = '$categoriaSelect', 
                                `id_estado` = '$id_estado',
                                `id_tecnico` = $id_tecnico
                            WHERE `id_ticket` = '$id_ticket'";
                
                    if ($db->consulta($sql1)) {
                        // Enviar correos si el estado ha cambiado a 2 (Asignado)
                        if ($id_estado == 2) {
                            // Obtener el correo electrónico del usuario
                            $sql4 = "SELECT `email`, `nombre`, `apellido_paterno`, `telefono` FROM `usuarios` WHERE `id` = (SELECT `id_usuario` FROM `tickets` WHERE `id_ticket` = '$id_ticket')";
                            $result = $db->consulta($sql4);
                            if ($row = $db->fetch_array($result)) {
                                $emailUsuario = $row['email'];
                                $nombreUsarioCompleto = $row['nombre'] . ' ' . $row['apellido_paterno'];
                                $telefonoUsuario = $row['telefono'];
                
                                // Enviar correo electrónico al usuario usando PHPMailer
                                $mail = new PHPMailer(true);
                                try {
                                    $mail->IsSMTP();
                                    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
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
                
                                    // Cargar plantilla de correo y reemplazar los detalles del ticket
                                    $mailBody = file_get_contents('../../class/correos/ingreso_ticket.php');
                                    $mailBody = str_replace('{identificador}', htmlspecialchars($id_ticket), $mailBody);
                                    $mailBody = str_replace('{codigo}', htmlspecialchars($id_ticket), $mailBody);
                                    $mailBody = str_replace('{fecha}', htmlspecialchars(date('Y-m-d')), $mailBody);
                                    $mailBody = str_replace('{hora}', htmlspecialchars(date('H:i:s')), $mailBody);
                                    $mailBody = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $mailBody);
                                    $mailBody = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $mailBody);
                                    $mailBody = str_replace('{descripcion}', htmlspecialchars($descripcion_ticket), $mailBody);
                                    $mail->Body = $mailBody;
                
                                    $mail->send();
                                    echo 'Correo enviado correctamente al usuario<br>';
                                } catch (Exception $e) {
                                    error_log('Mailer Error: ' . $mail->ErrorInfo);
                                    echo "El correo no se pudo enviar al usuario. Mailer Error: {$mail->ErrorInfo}<br>";
                                }
                
                                // Obtener el correo electrónico y nombre del técnico
                                $sql5 = "SELECT `email`, `nombre`, `apellido_paterno`, `telefono` FROM `usuarios` WHERE `id` = $id_tecnico";
                                $result = $db->consulta($sql5);
                                if ($row = $db->fetch_array($result)) {
                                    $emailTecnico = $row['email'];
                                    $nombreTecnicoCompleto = $row['nombre'] . ' ' . $row['apellido_paterno'];
                                    $telefonoTecnico = $row['telefono'];
                
                                    // Enviar correo electrónico al técnico usando PHPMailer
                                    try {
                                        $mail->clearAddresses(); // Limpiar las direcciones anteriores
                                        $mail->addAddress($emailTecnico);
                                        $mail->Subject = 'Nuevo Ticket Asignado';
                
                                        // Cargar plantilla de correo y reemplazar los detalles del ticket
                                        $mailBodyTecnico = file_get_contents('../../class/correos/ticket_tecnico.php');
                                        $mailBodyTecnico = str_replace('{identificador}', htmlspecialchars($id_ticket), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{codigo}', htmlspecialchars($id_ticket), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{fecha}', htmlspecialchars(date('Y-m-d')), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{hora}', htmlspecialchars(date('H:i:s')), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{nombreTecnicoCompleto}', htmlspecialchars($nombreTecnicoCompleto), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{descripcion}', htmlspecialchars($descripcion_ticket), $mailBodyTecnico);
                                        $mail->Body = $mailBodyTecnico;
                
                                        $mail->send();
                                        echo 'Correo enviado correctamente al técnico';
                
                                        // Enviar mensaje SMS al técnico
                                        $basic = new Basic("TU_API_KEY", "TU_API_SECRET");
                                        $client = new Client($basic);
                                        $message = $client->message()->send([
                                            'to' => $telefonoTecnico,
                                            'from' => 'VonageAPI',
                                            'text' => "Nuevo ticket asignado: $asunto_ticket. Descripción: $descripcion_ticket"
                                        ]);
                                        echo 'Mensaje SMS enviado correctamente al técnico';
                                    } catch (Exception $e) {
                                        error_log('Mailer Error: ' . $mail->ErrorInfo);
                                        echo "El correo no se pudo enviar al técnico. Mailer Error: {$mail->ErrorInfo}";
                                    }
                                } else {
                                    echo json_encode(['status' => 'error', 'message' => 'Error al obtener el correo del técnico']);
                                }
                            } else {
                                echo json_encode(['status' => 'error', 'message' => 'Error al obtener el correo del usuario']);
                            }
                        } else {
                            echo json_encode(['status' => 'success', 'message' => 'Borrador modificado correctamente']);
                        }
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el ticket: ' . $db->error]);
                    }
                    break;
                
        

        
        case 'terminar_proceso':
                $id_ticket           = $_POST['id'] ?? '';
                $comentario_final    = $_POST['mensaje_usuario'] ?? '';

            
                if (empty($id_ticket)) {
                    echo "ID de ticket no válido.";
                    break;
                }
            
                $fecha = date("Y-m-d");
                $hora = date("H:i:s");
            
                // Actualización en la tabla `proceso_tickets`
                $sql1 = "UPDATE `proceso_tickets` SET 
                         `fecha_termino_ticket` = '$fecha', 
                         `hora_termino_ticket` = '$hora'
                         WHERE `id_ticket` = '$id_ticket'";
                $result1 = $db->guardar($sql1);
            
                  // Actualización de la tabla `tickets` poniendo el tikcket en estado en validacion 
                    $sql2 = "UPDATE `tickets` SET 
                    `id_estado` = '5',
                    `comentario_final` = '$comentario_final'
                    WHERE `id_ticket` = '$id_ticket'";
                $result2 = $db->guardar($sql2);
            
                // Inserción en la tabla `avance_tecnicos`
                // $sql3 = "INSERT INTO `avance_tecnicos`(`id_ticket`, `accion`, `fecha_avance`, `hora_avance`)
                //          VALUES ('$id_ticket', 'Ticket Finalizado', '$fecha', '$hora')";
                // $result3 = $db->guardar($sql3);
            
                // Obtener el correo electrónico del usuario y los detalles del ticket
                $sql4 = "SELECT t.*, u.email, u.nombre, u.apellido_paterno, uss.nombre AS nombre_tecnico, uss.apellido_paterno AS apePaternoTecnico
                         FROM tickets t
                         JOIN usuarios u ON t.id_usuario = u.id
                         JOIN usuarios uss ON t.id_tecnico = uss.id
                         WHERE t.id_ticket = '$id_ticket'";
                $result4 = $db->consulta($sql4);
            
                if ($row = $db->fetch_array($result4)) {
                    $emailUsuario               = $row['email'];
                    $nombreUsarioCompleto       = $row['nombre'] . ' ' . $row['apellido_paterno'];
                    $asunto_ticket              = $row['asunto'];
                    $descripcion_ticket         = $row['descripcion_ticket'];
                    $identificador              = $row['identificador'];
                    $nombreTecnicoCompleto      = $row['nombre_tecnico'] . ' ' . $row['apePaternoTecnico'];
                    $comentarioTecnicoFinal     = $row['comentario_final'];

            
                    // Enviar correo electrónico al usuario usando PHPMailer
                    $mail = new PHPMailer(true);
                    try {
                            $mail->IsSMTP();
                            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
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
                        $mail->Subject = 'Ticket Terminado';
            
                        // Cargar plantilla de correo y reemplazar los detalles del ticket
                        $mailBody = file_get_contents('../../class/correos/terminado_ticket.php');
                        $mailBody = str_replace('{identificador}', htmlspecialchars($identificador), $mailBody);
                        $mailBody = str_replace('{codigo}', htmlspecialchars($id_ticket), $mailBody);
                        $mailBody = str_replace('{fecha}', htmlspecialchars($fecha), $mailBody);
                        $mailBody = str_replace('{hora}', htmlspecialchars($hora), $mailBody);
                        $mailBody = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $mailBody);
                        $mailBody = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $mailBody);
                        $mailBody = str_replace('{descripcion}', htmlspecialchars($descripcion_ticket), $mailBody);
                        $mailBody = str_replace('{nombre_tecnico}', htmlspecialchars($nombreTecnicoCompleto), $mailBody);
                        $mailBody = str_replace('{comentarioTecnicoFinal}', htmlspecialchars($comentarioTecnicoFinal), $mailBody);

                        $mail->Body = $mailBody;
            
                        $mail->send();
                        echo json_encode(['status' => 'success', 'message' => 'Ticket finalizado y correo enviado']);
                    } catch (Exception $e) {
                        error_log('Mailer Error: ' . $mail->ErrorInfo);
                        echo json_encode(['status' => 'error', 'message' => "El correo no se pudo enviar al usuario. Mailer Error: {$mail->ErrorInfo}"]);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al obtener los detalles del ticket']);
                }
            
                // Verificación de resultados
                if ($result1 && $result2 && $result3) {
                    echo "Ticket $id_ticket terminado correctamente.";
                } else {
                    echo "Error al terminar el ticket.";
                }
                break;

        case 'asignar_tecnico':
                // Recuperar y validar los datos recibidos
                $id_ticket = isset($_POST['id_ticket']) ? intval($_POST['id_ticket']) : 0;
                $tecnico   = isset($_POST['tecnico'])   ? intval($_POST['tecnico'])   : 0;
                $prioridad = (isset($_POST['prioridad']) && $_POST['prioridad'] !== '') ? intval($_POST['prioridad']) : null;
                $comentario = isset($_POST['comentario']) ? $_POST['comentario'] : ''; // comentario opcional
            
                if ($id_ticket <= 0 || $tecnico <= 0 || $prioridad === null) {
                    echo json_encode(['status' => 'error', 'message' => 'Faltan datos para asignar técnico']);
                    exit;
                }
            
                // Actualizar el ticket asignando técnico, prioridad y colocando el estado en "Asignado" (estado 2)
                $sql = "UPDATE tickets 
                        SET id_tecnico           = '$tecnico', 
                            id_prioridad         = '$prioridad', 
                            id_estado            = 3";
                // Si se incluye comentario, se guarda en la columna comentario_final
                if (!empty($comentario)) {
                    $sql .= ", comentario_administrador = '" . $db->escape_string($comentario) . "'";
                }
                $sql .= " WHERE id_ticket = '$id_ticket'";
            
                if ($db->consulta($sql)) {
                    // Actualizar la fecha y hora de asignación en proceso_tickets
                    $fecha = date('Y-m-d');
                    $hora  = date('H:i:s');
                    $sqlProceso = "UPDATE proceso_tickets 
                                   SET fecha_asignacion_tecnico = '$fecha', 
                                       hora_asignacion_tecnico = '$hora'
                                   WHERE id_ticket = '$id_ticket'";
                    $db->consulta($sqlProceso);
            
                    echo json_encode(['status' => 'success', 'message' => 'Técnico asignado correctamente.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al asignar técnico: ' . $db->error]);
                }
                break;



        case 'pasarDeBorradorATicket':
                    $id_ticket              = isset($_POST['id']) ? intval($_POST['id']) : 0;
                    $asunto_ticket          = isset($_POST['asunto']) ? $_POST['asunto'] : '';
                    $descripcion_ticket     = isset($_POST['descripcion_ticket']) ? $_POST['descripcion_ticket'] : '';
                    $categoriaSelect        = isset($_POST['id_categoria_ticket']) ? $_POST['id_categoria_ticket'] : '';
                    
                    if ($id_ticket === 0 || empty($asunto_ticket) || empty($descripcion_ticket) || empty($categoriaSelect)) {
                        echo json_encode(['status' => 'error', 'message' => 'Faltan datos para modificar el borrador']);
                        exit;
                    }
                    
                    $db = new MySQL("", "", "");
        
                    // Obtener la fecha y hora actuales
                    $fecha_actual = date('Y-m-d');
                    $hora_actual = date('H:i:s');
                    
                    // Consultar los técnicos asociados a la categoría seleccionada
                    $sql = "SELECT id_tecnico FROM categoria_tecnico WHERE id_categoria = '$categoriaSelect'";
                    $result = $db->consulta($sql);
                    
                    $tecnicos = [];
                    while ($row = $db->fetch_array($result)) {
                        $tecnicos[] = $row['id_tecnico'];
                    }
                    
                    // Determinar el técnico y el estado
                    if (!empty($tecnicos)) {
                        $id_tecnico = $tecnicos[0];
                        $id_estado = 2; // Asignado
                    } else {
                        $id_tecnico = 'NULL';
                        $id_estado = 1; // Recibido
                    }
                    
                    // Actualizar el ticket
                    $sql1 = "UPDATE `tickets` 
                            SET `asunto`                = '$asunto_ticket', 
                                `descripcion_ticket`    = '$descripcion_ticket', 
                                `id_categoria_ticket`   = '$categoriaSelect', 
                                `id_estado`             = '$id_estado',
                                `id_tecnico`            = $id_tecnico
                            WHERE `id_ticket` = '$id_ticket' AND `id_estado` = 4"; // Asegurar que solo se modifiquen borradores
                    
                    $sql2 = "UPDATE `proceso_tickets`
                             SET `fecha_creacion_inicio`    = '$fecha_actual',
                                 `hora_creacion_inicio`     = '$hora_actual',
                                `fecha_asignacion_tecnico`  = '$fecha_actual',
                                `hora_asignacion_tecnico`  = '$hora_actual'


                             WHERE `id_ticket` = '$id_ticket'";
                    
                    if ($db->consulta($sql1) && $db->consulta($sql2)) {
                        // Enviar correos si el estado ha cambiado a 2 (Asignado)
                        if ($id_estado == 2) {
                            // Obtener el correo electrónico del usuario
                            $sql4 = "SELECT `email`, `nombre`, `apellido_paterno`, `telefono` FROM `usuarios` WHERE `id` = (SELECT `id_usuario` FROM `tickets` WHERE `id_ticket` = '$id_ticket')";
                            $result = $db->consulta($sql4);
                            if ($row = $db->fetch_array($result)) {
                                $emailUsuario           = $row['email'];
                                $nombreUsarioCompleto   = $row['nombre'] . ' ' . $row['apellido_paterno'];
                                $telefonoUsuario        = $row['telefono'];
                    
                                // Enviar correo electrónico al usuario usando PHPMailer
                                $mail = new PHPMailer(true);
                                try {
                                    $mail->IsSMTP();
                                    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
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
                    
                                    // Cargar plantilla de correo y reemplazar los detalles del ticket
                                    $mailBody = file_get_contents('../../class/correos/ingreso_ticket.php');
                                    $mailBody = str_replace('{identificador}', htmlspecialchars($id_ticket), $mailBody);
                                    $mailBody = str_replace('{codigo}', htmlspecialchars($id_ticket), $mailBody);
                                    $mailBody = str_replace('{fecha}', htmlspecialchars($fecha_actual), $mailBody);
                                    $mailBody = str_replace('{hora}', htmlspecialchars($hora_actual), $mailBody);
                                    $mailBody = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $mailBody);
                                    $mailBody = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $mailBody);
                                    $mailBody = str_replace('{descripcion}', htmlspecialchars($descripcion_ticket), $mailBody);
                                    $mail->Body = $mailBody;
                    
                                    $mail->send();
                                    echo 'Correo enviado correctamente al usuario<br>';
                                } catch (Exception $e) {
                                    error_log('Mailer Error: ' . $mail->ErrorInfo);
                                    echo "El correo no se pudo enviar al usuario. Mailer Error: {$mail->ErrorInfo}<br>";
                                }
                    
                                // Obtener el correo electrónico y nombre del técnico
                                $sql5 = "SELECT `email`, `nombre`, `apellido_paterno`, `telefono` FROM `usuarios` WHERE `id` = $id_tecnico";
                                $result = $db->consulta($sql5);
                                if ($row = $db->fetch_array($result)) {
                                    $emailTecnico           = $row['email'];
                                    $nombreTecnicoCompleto  = $row['nombre'] . ' ' . $row['apellido_paterno'];
                                    $telefonoTecnico        = $row['telefono'];
                    
                                    // Enviar correo electrónico al técnico usando PHPMailer
                                    try {
                                        $mail->IsSMTP();
                                        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
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
                                        $mail->clearAddresses(); // Limpiar las direcciones anteriores
                                        $mail->addAddress($emailTecnico);
                                        $mail->Subject = 'Nuevo Ticket Asignado';
                    
                                        // Cargar plantilla de correo y reemplazar los detalles del ticket
                                        $mailBodyTecnico = file_get_contents('../../class/correos/ticket_tecnico.php');
                                        $mailBodyTecnico = str_replace('{identificador}', htmlspecialchars($id_ticket), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{codigo}', htmlspecialchars($id_ticket), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{fecha}', htmlspecialchars($fecha_actual), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{hora}', htmlspecialchars($hora_actual), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{nombreTecnicoCompleto}', htmlspecialchars($nombreTecnicoCompleto), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $mailBodyTecnico);
                                        $mailBodyTecnico = str_replace('{descripcion}', htmlspecialchars($descripcion_ticket), $mailBodyTecnico);
                                        $mail->Body = $mailBodyTecnico;
                    
                                        $mail->send();
                                        echo 'Correo enviado correctamente al técnico';
                    
                                        // Enviar mensaje SMS al técnico
                                        $basic = new Basic("TU_API_KEY", "TU_API_SECRET");
                                        $client = new Client($basic);
                                        $message = $client->message()->send([
                                            'to' => $telefonoTecnico,
                                            'from' => 'VonageAPI',
                                            'text' => "Nuevo ticket asignado: $asunto_ticket. Descripción: $descripcion_ticket"
                                        ]);
                                        echo 'Mensaje SMS enviado correctamente al técnico';
                                    } catch (Exception $e) {
                                        error_log('Mailer Error: ' . $mail->ErrorInfo);
                                        echo "El correo no se pudo enviar al técnico. Mailer Error: {$mail->ErrorInfo}";
                                    }
                                } else {
                                    echo json_encode(['status' => 'error', 'message' => 'Error al obtener el correo del técnico']);
                                }
                            } else {
                                echo json_encode(['status' => 'error', 'message' => 'Error al obtener el correo del usuario']);
                            }
                        } else {
                            echo json_encode(['status' => 'success', 'message' => 'Borrador modificado correctamente']);
                        }
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el ticket: ' . $db->error]);
                    }
                    break;



        case 'validacionticket':
            $id_ticket              = $_POST['id_ticket'] ?? '';
            $comentario             = $_POST['comentario'] ?? '';
            $validacionSeleccionada = $_POST['validacion'] ?? '';
            $idUsuarioSession       = $_POST['usuario'] ?? ''; // Asegúrate de que este campo se envíe correctamente desde el frontend
                    
                        // Verificar que el ID del ticket sea válido
                        if (empty($id_ticket)) {
                            echo "ID de ticket no válido.";
                            break;
                        }
                    
                        $fecha = date("Y-m-d");
                        $hora = date("H:i:s");
                    
                        // Si la validación seleccionada es "El ticket está bien resuelto", se considera que el ticket se cierra.
                        if ($validacionSeleccionada === 'El ticket está bien resuelto') {
                            // Actualización en la tabla `proceso_tickets`
                            $sql1 = "UPDATE `proceso_tickets` SET 
                                     `fecha_cerrado_ticket` = '$fecha', 
                                     `hora_cerrado_ticket` = '$hora'
                                     WHERE `id_ticket` = '$id_ticket'";
                            $result1 = $db->guardar($sql1);
                            if ($result1) {
                                echo "La tabla proceso_tickets se ha actualizado correctamente.";
                            } else {
                                echo "Error al actualizar la tabla proceso_tickets.";
                                error_log($db->error()); // Registrar el error de la base de datos en los logs
                            }
                    
                            // Actualización de la tabla `tickets` poniendo el ticket en estado "cerrado"
                            $sql2 = "UPDATE `tickets` SET 
                                     `id_estado` = '6'
                                     WHERE `id_ticket` = '$id_ticket'";
                            $result2 = $db->guardar($sql2);
                    
                            // Inserción en la tabla `avance_tecnicos`
                            // $sql3 = "INSERT INTO `avance_tecnicos`(`id_ticket`, `accion`, `fecha_avance`, `hora_avance`)
                            //          VALUES ('$id_ticket', 'Ticket Cerrado', '$fecha', '$hora')";
                            // $result3 = $db->guardar($sql3);
                        } else {
                            // Inserción en la tabla `reactivacion_ticket` si el ticket se está reactivando
                            $sql1 = "INSERT INTO `reactivacion_ticket`(`id_ticket`, `comentario`, `fecha_reactivacion`, `hora_reactivacion`, `usuario_reactivacion`)
                                     VALUES ('$id_ticket', '$comentario', '$fecha', '$hora', '$idUsuarioSession')";
                            $result1 = $db->guardar($sql1);
                    
                            // Actualización de la tabla `tickets` poniendo el ticket en estado "en validación"
                            $sql2 = "UPDATE `tickets` SET 
                                     `id_estado` = '7', 
                                     `comentario_final` = '$comentario'
                                     WHERE `id_ticket` = '$id_ticket'";
                            $result2 = $db->guardar($sql2);
                    
                            // Inserción en la tabla `avance_tecnicos`
                            $sql3 = "INSERT INTO `avance_tecnicos`(`id_ticket`, `accion`, `fecha_avance`, `hora_avance`)
                                     VALUES ('$id_ticket', 'Ticket en validacion', '$fecha', '$hora')";
                            $result3 = $db->guardar($sql3);
                        }
                    
                        // Obtener el correo electrónico del usuario y los detalles del ticket
                        $sql4 = "SELECT t.*, u.email, u.nombre, u.apellido_paterno, uss.nombre AS nombre_tecnico, uss.apellido_paterno AS apePaternoTecnico
                                 FROM tickets t
                                 JOIN usuarios u ON t.id_usuario = u.id
                                 JOIN usuarios uss ON t.id_tecnico = uss.id
                                 WHERE t.id_ticket = '$id_ticket'";
                        $result4 = $db->consulta($sql4);
                    
                        if ($row = $db->fetch_array($result4)) {
                            $emailUsuario = $row['email'];
                            $nombreUsarioCompleto = $row['nombre'] . ' ' . $row['apellido_paterno'];
                            $asunto_ticket = $row['asunto'];
                            $descripcion_ticket = $row['descripcion_ticket'];
                            $identificador = $row['identificador'];
                            $nombreTecnicoCompleto = $row['nombre_tecnico'] . ' ' . $row['apePaternoTecnico'];
                    
                            // Enviar correo electrónico al usuario usando PHPMailer
                            $mail = new PHPMailer(true);
                            try {
                                $mail->IsSMTP();
                                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
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
                                $mail->Subject = 'Ticket Terminado';
                    
                                // Cargar plantilla de correo y reemplazar los detalles del ticket
                                $mailBody = file_get_contents('../../class/correos/cerrar_ticket.php');
                                $mailBody = str_replace('{identificador}', htmlspecialchars($identificador), $mailBody);
                                $mailBody = str_replace('{codigo}', htmlspecialchars($id_ticket), $mailBody);
                                $mailBody = str_replace('{fecha}', htmlspecialchars($fecha), $mailBody);
                                $mailBody = str_replace('{hora}', htmlspecialchars($hora), $mailBody);
                                $mailBody = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $mailBody);
                                $mailBody = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $mailBody);
                                $mailBody = str_replace('{descripcion}', htmlspecialchars($descripcion_ticket), $mailBody);
                                $mailBody = str_replace('{nombre_tecnico}', htmlspecialchars($nombreTecnicoCompleto), $mailBody);
                                $mail->Body = $mailBody;
                    
                                $mail->send();
                                echo json_encode(['status' => 'success', 'message' => 'Ticket finalizado y correo enviado']);
                            } catch (Exception $e) {
                                error_log('Mailer Error: ' . $mail->ErrorInfo);
                                echo json_encode(['status' => 'error', 'message' => "El correo no se pudo enviar al usuario. Mailer Error: {$mail->ErrorInfo}"]);
                            }
                        } else {
                            echo json_encode(['status' => 'error', 'message' => 'Error al obtener los detalles del ticket']);
                        }
                    
                        // Verificación de resultados
                        if ($result1 && $result2 && $result3) {
                            echo "Ticket $id_ticket procesado correctamente.";
                        } else {
                            echo "Error al procesar el ticket.";
                        }
                        break;
        case "calificando_ticket":
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["accion"]) && $_POST["accion"] == "calificando_ticket") {

            // Validar y limpiar los datos
            $id_ticket    = isset($_POST["id_ticket"]) ? intval($_POST["id_ticket"]) : 0;
            $calificacion = isset($_POST["calificacion"]) ? intval($_POST["calificacion"]) : 0; // Recibe ID de la calificación
            $comentario   = isset($_POST["comentario"]) ? trim($_POST["comentario"]) : ""; // Comentario opcional
            $idUsuario    = isset($_POST["usuario"]) ? intval($_POST["usuario"]) : 0; // ID del usuario que valida
            $fechaCierre  = date("Y-m-d");
            $horaCierre   = date("H:i:s");
            
            // Validaciones básicas
            if ($id_ticket <= 0 || $calificacion <= 0) {
                echo json_encode(["success" => false, "message" => " Error: Datos inválidos. Debes seleccionar una calificación."]);
                exit;
            }
            
            // Conectar con la base de datos
            $db = new MySQL("", "", ""); 
            
            // **Determinar el nuevo estado según la calificación**
            if ($calificacion == 1) { 
                // "El ticket está bien resuelto" → Estado 6 (Cerrado)
                $nuevoEstado = 6;
            } else { 
                // "El ticket no está bien resuelto" o "necesita más trabajo" → Estado 3 (Reabierto/En proceso)
                $nuevoEstado = 3;
            }
            
            // **1️ ACTUALIZAR EL ESTADO DEL TICKET**
            $updateTicket = "UPDATE tickets SET id_estado = $nuevoEstado WHERE id_ticket = $id_ticket";
            $result1 = $db->guardar($updateTicket);
            
            // **2️ ACTUALIZAR PROCESO_TICKETS** (Solo si el ticket se cierra)
            if ($nuevoEstado == 6) {
                $updateProceso = "UPDATE proceso_tickets 
                          SET fecha_cierre_ticket = '$fechaCierre', 
                          hora_cierre_ticket  = '$horaCierre' 
                          WHERE id_ticket = $id_ticket";
                $result2 = $db->guardar($updateProceso);
            } else {
                $result2 = true; // No afecta si el ticket se reabre
            }
            
            // **3️ REGISTRAR AVANCE EN AVANCE_TECNICOS (SOLO si el ticket está bien resuelto)**
            if ($nuevoEstado == 6) { 
                $insertAvance = "INSERT INTO avance_tecnicos (id_ticket, accion, fecha_avance, hora_avance) 
                         VALUES ($id_ticket, 'Ticket Cerrado', '$fechaCierre', '$horaCierre')";
                $result3 = $db->guardar($insertAvance);
            } else {
                $result3 = true; // No se registra avance si el ticket se reabre
            }
            
            // **4️ INSERTAR EN REACTIVACION_TICKET (Solo si el ticket se reabre)**
            if ($nuevoEstado == 3) {
                $insertReactivacion = "INSERT INTO reactivacion_ticket (id_ticket, comentario, fecha_reactivacion, hora_reactivacion, usuario_reactivacion) 
                           VALUES ($id_ticket, '$comentario', '$fechaCierre', '$horaCierre', $idUsuario)";
                $result4 = $db->guardar($insertReactivacion);
            } else {
                $result4 = true; // No se inserta si el ticket se cierra
            }

            // Enviar correo si el ticket se cierra
            if ($nuevoEstado == 6) {
                // Obtener el correo electrónico del usuario y los detalles del ticket
                $sql4 = "SELECT 
                t.*, 
                u.email, 
                u.nombre, 
                u.apellido_paterno, 
                uss.nombre AS nombre_tecnico, 
                uss.apellido_paterno AS apePaternoTecnico, 
                pt.*
             FROM tickets t
             JOIN usuarios u ON t.id_usuario = u.id
             JOIN usuarios uss ON t.id_tecnico = uss.id
             JOIN proceso_tickets pt ON t.id_ticket = pt.id_ticket
             WHERE t.id_ticket = '$id_ticket'";
    
                $result4 = $db->consulta($sql4);
            
                if ($row = $db->fetch_array($result4)) {
                $emailUsuario           = $row['email'];
                $nombreUsarioCompleto   = $row['nombre'] . ' ' . $row['apellido_paterno'];
                $asunto_ticket          = $row['asunto'];
                $descripcion_ticket     = $row['descripcion_ticket'];
                $identificador          = $row['identificador'];
                $nombreTecnicoCompleto  = $row['nombre_tecnico'] . ' ' . $row['apePaternoTecnico'];
                //tabla processo_tickets
                $fecha_creacion_inicio      = $row['fecha_creacion_inicio'];
                $hora_creacion_inicio       = $row['hora_creacion_inicio'];
                $fecha_asignacion_tecnico   = $row['fecha_asignacion_tecnico'];
                $hora_asignacion_tecnico    = $row['hora_asignacion_tecnico'];
                $fecha_comienzo_ticket      = $row['fecha_comienzo_ticket'];
                $hora_comienzo_ticket       = $row['hora_comienzo_ticket'];
                $fecha_cierre_ticket        = $row['fecha_cierre_ticket'];
                $hora_cierre_ticket         = $row['hora_cierre_ticket'];
                $fecha_termino_ticket       = $row['fecha_termino_ticket'];
                $hora_termino_ticket        = $row['hora_termino_ticket'];
              
                // Enviar correo electrónico al usuario usando PHPMailer
                $mail = new PHPMailer(true);
                try {
                    $mail->IsSMTP();
                    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
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
                    $mail->Subject = 'Ticket Cerrado';
            
                    // Cargar plantilla de correo y reemplazar los detalles del ticket
                    $mailBody = file_get_contents('../../class/correos/gracias.php');
                    $mailBody = str_replace('{identificador}', htmlspecialchars($identificador), $mailBody);
                    $mailBody = str_replace('{codigo}', htmlspecialchars($id_ticket), $mailBody);
                    $mailBody = str_replace('{fecha}', htmlspecialchars($fechaCierre), $mailBody);
                    $mailBody = str_replace('{hora}', htmlspecialchars($horaCierre), $mailBody);
                    $mailBody = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $mailBody);
                    $mailBody = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $mailBody);
                    $mailBody = str_replace('{descripcion}', htmlspecialchars($descripcion_ticket), $mailBody);
                    $mailBody = str_replace('{nombre_tecnico}', htmlspecialchars($nombreTecnicoCompleto), $mailBody);
                    $mailBody = str_replace('{fecha_creacion_inicio}', htmlspecialchars($fecha_creacion_inicio), $mailBody);
                    $mailBody = str_replace('{hora_creacion_inicio}', htmlspecialchars($hora_creacion_inicio), $mailBody);
                    $mailBody = str_replace('{fecha_asignacion_tecnico}', htmlspecialchars($fecha_asignacion_tecnico), $mailBody);
                    $mailBody = str_replace('{hora_asignacion_tecnico}', htmlspecialchars($hora_asignacion_tecnico), $mailBody);
                    $mailBody = str_replace('{fecha_comienzo_ticket}', htmlspecialchars($fecha_comienzo_ticket), $mailBody);
                    $mailBody = str_replace('{hora_comienzo_ticket}', htmlspecialchars($hora_comienzo_ticket), $mailBody);
                    $mailBody = str_replace('{fecha_cierre_ticket}', htmlspecialchars($fecha_cierre_ticket), $mailBody);
                    $mailBody = str_replace('{hora_cierre_ticket}', htmlspecialchars($hora_cierre_ticket), $mailBody);
                    $mailBody = str_replace('{fecha_termino_ticket}', htmlspecialchars($fecha_termino_ticket), $mailBody);
                    $mailBody = str_replace('{hora_termino_ticket}', htmlspecialchars($hora_termino_ticket), $mailBody);
                    $mail->Body = $mailBody;
            
                    $mail->send();
                    echo json_encode(['status' => 'success', 'message' => 'Ticket finalizado y correo enviado']);
                } catch (Exception $e) {
                    // error_log('Mailer Error: ' . $mail->ErrorInfo);
                    echo json_encode(['status' => 'success', 'message' => "El correo no se pudo enviar al usuario. Mailer Error: {$mail->ErrorInfo}"]);
                }
                } else {
                    echo json_encode(['status' => 'success', 'message' => 'Ticket finalizado y correo enviado']);
                }
            }
            
            // **VERIFICACIÓN FINAL**
            if ($result1 && $result2 && $result3 && $result4) {
                echo json_encode(["success" => true, "message" => " Ticket actualizado correctamente."]);
            } else {
                echo json_encode(["success" => true, "message" => " Ticket actualizado correctamente."]);
            }
            }
        break;
                        
                        
                        
                        
                        
                            
                        
    }
}

$db->CerrarConexion();
?>
