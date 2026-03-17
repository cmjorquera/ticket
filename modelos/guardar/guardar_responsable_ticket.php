<?php
/**
 * modelos/guardar/asignar_tecnico_ticket.php
 * Actualiza prioridad, técnico, comentario, estado y categoría de un ticket,
 * registra fechas en proceso_tickets y ENVÍA CORREO al TÉCNICO asignado.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../class/PHPMailer/src/Exception.php';
require '../../class/PHPMailer/src/PHPMailer.php';
require '../../class/PHPMailer/src/SMTP.php';

include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// (Opcional en desarrollo)
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// Base absoluta para rutas (sube dos niveles desde /modelos/guardar/)
define('APP_BASE', dirname(__DIR__, 2)); // .../sistema_ticket

$fechaHoraActual = date('Y-m-d H:i:s');
$fechaActual     = date('Y-m-d', strtotime($fechaHoraActual));
$horaActual      = date('H:i:s', strtotime($fechaHoraActual));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fechaSeleccionada  = $_POST['fechaSeleccionada'] ?? '';
    $dias_demora        = $_POST['dias_demora'] ?? '';
    $id_tecnico         = $_POST['id_responsable'] ?? '';
    $prioridad          = $_POST['prioridad'] ?? '';
    $comentario         = $_POST['comentario'] ?? ''; // puede venir vacío
    $idTicket           = $_POST['idTicket'] ?? '';
    $idCategoria        = isset($_POST['categoria']) ? $_POST['categoria'] : null;

    $db = new MySQL("", "", "");

    // ✅ Actualizar la tabla `tickets`, incluyendo la categoría
    $sql_tickets = "UPDATE `tickets` SET 
        `id_prioridad`              = '$prioridad',
        `id_tecnico`                = '$id_tecnico',
        `comentario_administrador`  = '$comentario',
        `id_estado`                 = '2',
        `id_categoria_ticket`       = '$idCategoria'
        WHERE id_ticket = '$idTicket'";

    // ✅ Actualizar la tabla `proceso_tickets`
    $sql_proceso_tickets = "UPDATE `proceso_tickets` SET 
        `fecha_estimada_admin`      = '$fechaSeleccionada',
        `dias_estimada_admin`       = '$dias_demora',
        `fecha_asignacion_tecnico`  = '$fechaActual',
        `hora_asignacion_tecnico`   = '$horaActual'
        WHERE id_ticket = '$idTicket'";

    // Ejecutar ambas consultas (tu método devuelve 0 en éxito)
    $result_tickets         = $db->guardar($sql_tickets);
    $result_proceso_tickets = $db->guardar($sql_proceso_tickets);

    // ======= ENVÍO DE CORREO AL TÉCNICO =======
    // Solo intentamos enviar si ambas operaciones fueron correctas
    if ($result_tickets === 0 && $result_proceso_tickets === 0) {

        // 1) Traer información del ticket + hora/fecha de ingreso
        $sqlInfo = "
            SELECT 
                t.id_ticket,
                t.identificador,
                t.asunto,
                t.descripcion_ticket,
                t.id_usuario,
                t.id_tecnico,
                pt.fecha_creacion_inicio AS fecha_ingreso,
                pt.hora_creacion_inicio  AS hora_ingreso
            FROM tickets t
            LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
            WHERE t.id_ticket = '$idTicket'
            LIMIT 1
        ";
        $rsInfo = $db->consulta($sqlInfo);
        if ($rowT = $db->fetch_array($rsInfo)) {

            // 2) Datos del usuario (quien creó el ticket)
            $idSolicitante = $rowT['id_usuario'];
            $sqlUser = "SELECT nombre, apellido_paterno, email FROM usuarios WHERE id = '$idSolicitante' LIMIT 1";
            $rsUser  = $db->consulta($sqlUser);
            $nombreUsarioCompleto = '';
            if ($rowU = $db->fetch_array($rsUser)) {
                $nombreUsarioCompleto = trim(($rowU['nombre'] ?? '') . ' ' . ($rowU['apellido_paterno'] ?? ''));
            }

            // 3) Datos del técnico
            $sqlTec = "SELECT nombre, apellido_paterno, email FROM usuarios WHERE id = '$id_tecnico' LIMIT 1";
            $rsTec  = $db->consulta($sqlTec);
            if ($rowTec = $db->fetch_array($rsTec)) {
                $emailTecnico            = $rowTec['email'] ?? '';
                $nombreTecnicoCompleto   = trim(($rowTec['nombre'] ?? '') . ' ' . ($rowTec['apellido_paterno'] ?? ''));

                // Validación mínima de correo
                if (filter_var($emailTecnico, FILTER_VALIDATE_EMAIL)) {
                    try {
                        $mail = new PHPMailer(true);

                        // === Config SMTP (misma que usas en el sistema) ===
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
                        $mail->addAddress($emailTecnico, $nombreTecnicoCompleto);
                        $mail->isHTML(true);
                        $mail->Subject = 'Nuevo Ticket Asignado';

                        // Cargar plantilla del técnico
                        $tplTec = APP_BASE . '/class/correos/ticket_tecnico.php';
                        if (!file_exists($tplTec)) {
                            // Fallback mínimo si no existe la plantilla
                            $bodyTec = "<p>Se te ha asignado el ticket A-0{codigo}.</p>
                                        <p>Usuario: {nombreUsarioCompleto}</p>
                                        <p>Asunto: {asunto}</p>
                                        <p>Descripción: {descripcion}</p>";
                        } else {
                            $bodyTec = file_get_contents($tplTec);
                        }

                        // Reemplazar placeholders
                        $bodyTec = str_replace('{identificador}', htmlspecialchars($rowT['identificador'] ?? ''), $bodyTec);
                        $bodyTec = str_replace('{codigo}', htmlspecialchars($rowT['id_ticket'] ?? ''), $bodyTec);
                        $bodyTec = str_replace('{fecha}', htmlspecialchars($rowT['fecha_ingreso'] ?? $fechaActual), $bodyTec);
                        $bodyTec = str_replace('{hora}', htmlspecialchars($rowT['hora_ingreso'] ?? $horaActual), $bodyTec);
                        $bodyTec = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto ?: 'Usuario'), $bodyTec);
                        $bodyTec = str_replace('{nombreTecnicoCompleto}', htmlspecialchars($nombreTecnicoCompleto ?: 'Técnico'), $bodyTec);
                        $bodyTec = str_replace('{asunto}', htmlspecialchars($rowT['asunto'] ?? ''), $bodyTec);
                        $bodyTec = str_replace('{descripcion}', htmlspecialchars($rowT['descripcion_ticket'] ?? ''), $bodyTec);

                        $mail->Body = $bodyTec;

                        // Enviar (si falla, solo loguea; no interrumpe la respuesta)
                        if (!$mail->send()) {
                            error_log('Correo a técnico falló: ' . $mail->ErrorInfo);
                        }
                    } catch (Exception $e) {
                        error_log('Excepción PHPMailer (técnico): ' . $e->getMessage());
                    }
                } else {
                    error_log('Correo técnico inválido para id=' . $id_tecnico . ' -> ' . $emailTecnico);
                }
            } else {
                error_log('No se encontró técnico id=' . $id_tecnico);
            }
        } else {
            error_log('No se encontró ticket para envío de correo. id_ticket=' . $idTicket);
        }

        // Respuesta final (éxito)
        echo "Datos actualizados correctamente.";
    } else {
        // Error en alguna de las dos actualizaciones
        echo "Error al actualizar los datos.";
    }
}
?>
