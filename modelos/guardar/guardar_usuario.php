<?php
header('Content-Type: application/json; charset=utf-8');

require_once("../../class/conexion.php");
require_once("../../class/PHPMailer/src/PHPMailer.php");
require_once("../../class/PHPMailer/src/Exception.php");
require_once("../../class/PHPMailer/src/SMTP.php");

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

date_default_timezone_set('America/Santiago');

function enviarCorreoActivacion($email, $nombreCompleto, $areaTrabajo, $token)
{
    $mail = new PHPMailer(true);
    $enlaceActivacion = 'http://acceso.seduc.cl/reiniciar_clave.php?token=' . urlencode($token);
    $plantilla = file_get_contents(__DIR__ . '/../../class/correos/activar_cuenta.php');

    if ($plantilla === false) {
        throw new Exception('No se pudo cargar la plantilla de activacion.');
    }

    $mailBody = str_replace('{nombreCompleto}', htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8'), $plantilla);
    $mailBody = str_replace('{emailUsuario}', htmlspecialchars($email, ENT_QUOTES, 'UTF-8'), $mailBody);
    $mailBody = str_replace('{areaTrabajo}', htmlspecialchars($areaTrabajo, ENT_QUOTES, 'UTF-8'), $mailBody);
    $mailBody = str_replace('{enlaceActivacion}', htmlspecialchars($enlaceActivacion, ENT_QUOTES, 'UTF-8'), $mailBody);

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
    $mail->addAddress($email, $nombreCompleto);
    $mail->isHTML(true);
    $mail->Subject = 'Activa tu cuenta';
    $mail->Body = $mailBody;

    $mail->send();
}

function guardarPermisosIniciales($db, $idUsuario, $menuIds)
{
    $menuIdsSeleccionados = array_map('intval', (array)$menuIds);
    $menuIdsSeleccionados[] = 8;
    $menuIdsSeleccionados[] = 3;
    $menuIdsSeleccionados = array_values(array_unique(array_filter($menuIdsSeleccionados)));

    $consultaMenus = $db->consulta("SELECT id_menu FROM menu_1");
    while ($filaMenu = $db->fetch_assoc($consultaMenus)) {
        $idMenu = (int)$filaMenu['id_menu'];
        $idTipoPermiso = in_array($idMenu, $menuIdsSeleccionados, true) ? 1 : 3;
        $sqlPermiso = "INSERT INTO permisos_menu_1 (id_menu1, id_usuario, id_tipo_permiso)
                       VALUES ('$idMenu', '$idUsuario', '$idTipoPermiso')";
        $db->consulta($sqlPermiso);
    }
}

function guardarPerfilInicial($db, $idUsuario)
{
    $sqlPerfil = "INSERT INTO usuario_perfil (id_usuario, id_perfil) VALUES ('$idUsuario', '1')";
    $db->consulta($sqlPerfil);
}

function guardarColegioInicial($db, $idUsuario, $idColegio)
{
    if ((int)$idColegio <= 0) {
        return;
    }

    $fechaAsignacion = date('Y-m-d H:i:s');
    $sqlColegio = "INSERT INTO usuario_colegio (id_usuario, id_colegio, id_perfil, estado, fecha_asignacion)
                   VALUES ('$idUsuario', '$idColegio', '1', '1', '$fechaAsignacion')";
    $db->consulta($sqlColegio);
}

$action = isset($_POST["action"]) ? $_POST["action"] : "";
$db = new MySQL("", "", "");

if ($action === "" && (isset($_POST['email']) || isset($_POST['anexo']))) {
    $response = [
        'email_exists' => false,
        'anexo_exists' => false,
        'email_owner' => '',
        'anexo_owner' => ''
    ];

    if (isset($_POST['email']) && trim($_POST['email']) !== '') {
        $emailValidar = $db->escape_string(trim($_POST['email']));
        $consultaEmail = $db->consulta("SELECT nombre, apellido_paterno FROM usuarios WHERE email = '$emailValidar' LIMIT 1");
        if ($filaEmail = $db->fetch_assoc($consultaEmail)) {
            $response['email_exists'] = true;
            $response['email_owner'] = trim($filaEmail['nombre'] . ' ' . $filaEmail['apellido_paterno']);
        }
    }

    if (isset($_POST['anexo']) && trim($_POST['anexo']) !== '') {
        $anexoValidar = $db->escape_string(trim($_POST['anexo']));
        $consultaAnexo = $db->consulta("SELECT nombre, apellido_paterno FROM usuarios WHERE anexo = '$anexoValidar' LIMIT 1");
        if ($filaAnexo = $db->fetch_assoc($consultaAnexo)) {
            $response['anexo_exists'] = true;
            $response['anexo_owner'] = trim($filaAnexo['nombre'] . ' ' . $filaAnexo['apellido_paterno']);
        }
    }

    echo json_encode($response);
    exit;
}

switch ($action) {
    case 'agregarUsuario':
        $nombre = isset($_POST["nombre"]) ? trim($_POST["nombre"]) : "";
        $apellidoPaterno = isset($_POST["apellidoPaterno"]) ? trim($_POST["apellidoPaterno"]) : "";
        $apellidoMaterno = isset($_POST["apellidoMaterno"]) ? trim($_POST["apellidoMaterno"]) : "";
        $email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
        $telefono = isset($_POST["telefono"]) ? trim($_POST["telefono"]) : "";
        $anexo = isset($_POST["anexo"]) ? trim($_POST["anexo"]) : "";
        $sexo = isset($_POST["sexo"]) ? trim($_POST["sexo"]) : "";
        $fechaNacimiento = isset($_POST["fechaNacimiento"]) ? trim($_POST["fechaNacimiento"]) : "0000-00-00";
        $areaTrabajo = isset($_POST["areaTrabajo"]) ? trim($_POST["areaTrabajo"]) : "";
        $idColegio = isset($_POST["idColegio"]) ? (int)$_POST["idColegio"] : 0;
        $menuIds = isset($_POST["menuIds"]) ? $_POST["menuIds"] : [];

        if (!is_array($menuIds)) {
            $menuIds = [$menuIds];
        }

        if ($nombre === "" || $apellidoPaterno === "" || $email === "" || $areaTrabajo === "" || $sexo === "" || $idColegio <= 0) {
            echo json_encode(["success" => false, "message" => "Faltan datos obligatorios para crear el usuario."]);
            break;
        }

        $nombre = $db->escape_string($nombre);
        $apellidoPaterno = $db->escape_string($apellidoPaterno);
        $apellidoMaterno = $db->escape_string($apellidoMaterno);
        $email = $db->escape_string($email);
        $telefono = $db->escape_string($telefono);
        $anexo = $db->escape_string($anexo);
        $sexo = $db->escape_string($sexo);
        $fechaNacimiento = $db->escape_string($fechaNacimiento);
        $areaTrabajo = $db->escape_string($areaTrabajo);

        $tokenActivacion = bin2hex(random_bytes(16));

        $sql = "INSERT INTO usuarios (
                    nombre,
                    apellido_paterno,
                    apellido_materno,
                    email,
                    telefono,
                    clave,
                    anexo,
                    sexo,
                    fecha_nacimiento,
                    id_area_trabajo,
                    estado,
                    token_reinicio
                ) VALUES (
                    '$nombre',
                    '$apellidoPaterno',
                    '$apellidoMaterno',
                    '$email',
                    '$telefono',
                    NULL,
                    '$anexo',
                    '$sexo',
                    '$fechaNacimiento',
                    '$areaTrabajo',
                    'Pendiente',
                    '$tokenActivacion'
                )";

        $resultado = $db->consulta($sql);

        if ($resultado) {
            $idUsuarioNuevo = $db->insert_id();
            guardarPermisosIniciales($db, $idUsuarioNuevo, $menuIds);
            guardarPerfilInicial($db, $idUsuarioNuevo);
            guardarColegioInicial($db, $idUsuarioNuevo, $idColegio);

            $nombreCompleto = trim($nombre . ' ' . $apellidoPaterno . ' ' . $apellidoMaterno);
            $sqlArea = "SELECT nombre_area FROM area_trabajo WHERE id_area = '$areaTrabajo' LIMIT 1";
            $resArea = $db->consulta($sqlArea);
            $rowArea = $db->fetch_assoc($resArea);
            $nombreArea = $rowArea ? $rowArea['nombre_area'] : 'Sin area asignada';

            try {
                enviarCorreoActivacion($email, $nombreCompleto, $nombreArea, $tokenActivacion);
                echo json_encode(["success" => true, "message" => "Usuario creado en estado Pendiente y correo de activacion enviado."]);
            } catch (Exception $e) {
                error_log('Error correo activacion usuario: ' . $e->getMessage());
                echo json_encode([
                    "success" => true,
                    "message" => "Usuario creado en estado Pendiente, pero el correo de activacion no se pudo enviar."
                ]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Error al agregar el usuario."]);
        }
        break;

    case 'modificarUsuario':
        $userId = isset($_POST["userId"]) ? $_POST["userId"] : "";
        $nombre = isset($_POST["nombre"]) ? $_POST["nombre"] : "";
        $apellidoPaterno = isset($_POST["apellidoPaterno"]) ? $_POST["apellidoPaterno"] : "";
        $apellidoMaterno = isset($_POST["apellidoMaterno"]) ? $_POST["apellidoMaterno"] : "";
        $email = isset($_POST["email"]) ? $_POST["email"] : "";
        $telefono = isset($_POST["telefono"]) ? $_POST["telefono"] : "";
        $clave = isset($_POST["clave"]) ? $_POST["clave"] : "";
        $anexo = isset($_POST["anexo"]) ? $_POST["anexo"] : "";
        $areaTrabajo = isset($_POST["areaTrabajo"]) ? $_POST["areaTrabajo"] : "";
        $sexo = isset($_POST["sexo"]) ? $_POST["sexo"] : "";

        $sql = "UPDATE usuarios SET 
                nombre              ='$nombre',
                apellido_paterno    ='$apellidoPaterno',
                apellido_materno    ='$apellidoMaterno',
                email               ='$email', 
                telefono            ='$telefono', 
                clave               ='$clave', 
                anexo               ='$anexo', 
                sexo                ='$sexo',                          
                id_area_trabajo     ='$areaTrabajo'
        WHERE id='$userId'";

        $resultado = $db->consulta($sql);
        if ($resultado) {
            echo json_encode(["success" => true, "message" => "Usuario actualizado correctamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar el usuario."]);
        }
        break;

    case 'bloquearUsuario':
        $idUsuario = isset($_POST["userId"]) ? $_POST["userId"] : "";

        $sql = "UPDATE usuarios SET estado = 'Bloqueado' WHERE id = '$idUsuario'";
        $resultado = $db->consulta($sql);

        if ($resultado) {
            echo json_encode(["success" => true, "message" => "Usuario bloqueado correctamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al bloquear el usuario."]);
        }
        break;

    case 'activarUsuario':
        $idUsuario = isset($_POST["userId"]) ? $_POST["userId"] : "";

        $sql = "UPDATE usuarios SET estado = 'Activo' WHERE id = '$idUsuario'";
        $resultado = $db->consulta($sql);

        if ($resultado) {
            echo json_encode(["success" => true, "message" => "Usuario activado correctamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al activar el usuario."]);
        }
        break;

    case 'reenviarActivacion':
        $idUsuario = isset($_POST["userId"]) ? (int)$_POST["userId"] : 0;

        if ($idUsuario <= 0) {
            echo json_encode(["success" => false, "message" => "Usuario invalido."]);
            break;
        }

        $sqlUsuario = "SELECT id, nombre, apellido_paterno, apellido_materno, email, clave, estado, id_area_trabajo, token_reinicio
                       FROM usuarios
                       WHERE id = '$idUsuario'
                       LIMIT 1";
        $resUsuario = $db->consulta($sqlUsuario);
        $usuario = $db->fetch_assoc($resUsuario);

        if (!$usuario) {
            echo json_encode(["success" => false, "message" => "Usuario no encontrado."]);
            break;
        }

        if (!empty($usuario['clave'])) {
            echo json_encode(["success" => false, "message" => "Este usuario ya activo su cuenta y no necesita reenviar activacion."]);
            break;
        }

        $tokenActivacion = trim((string)($usuario['token_reinicio'] ?? ''));
        if ($tokenActivacion === '') {
            $tokenActivacion = bin2hex(random_bytes(16));
            $tokenSeguro = $db->escape_string($tokenActivacion);
            $db->consulta("UPDATE usuarios SET token_reinicio = '$tokenSeguro' WHERE id = '$idUsuario'");
        }

        $idAreaTrabajo = $db->escape_string((string)($usuario['id_area_trabajo'] ?? ''));
        $sqlArea = "SELECT nombre_area FROM area_trabajo WHERE id_area = '$idAreaTrabajo' LIMIT 1";
        $resArea = $db->consulta($sqlArea);
        $rowArea = $db->fetch_assoc($resArea);
        $nombreArea = $rowArea ? $rowArea['nombre_area'] : 'Sin area asignada';

        $nombreCompleto = trim(
            (string)$usuario['nombre'] . ' ' .
            (string)$usuario['apellido_paterno'] . ' ' .
            (string)$usuario['apellido_materno']
        );

        try {
            enviarCorreoActivacion($usuario['email'], $nombreCompleto, $nombreArea, $tokenActivacion);
            echo json_encode(["success" => true, "message" => "Correo de activacion reenviado correctamente."]);
        } catch (Exception $e) {
            error_log('Error reenvio activacion usuario: ' . $e->getMessage());
            echo json_encode(["success" => false, "message" => "No se pudo reenviar el correo de activacion."]);
        }
        break;

    default:
        echo json_encode(["success" => false, "message" => "Acci¨®n no v¨¢lida."]);
        break;
}
?>
