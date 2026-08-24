<?php
require_once __DIR__ . '/../componentes/boot.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../../class/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../class/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../class/PHPMailer/src/SMTP.php';

header('Content-Type: application/json; charset=utf-8');

function inv_dash_alerta_responder(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function inv_dash_alertas_meta(): array
{
    return [
        'sin_ubicacion' => [
            'asunto' => 'Alerta inventario - Equipos sin ubicación',
            'mensaje' => 'El colegio seleccionado tiene {total} PC sin ubicación asignada.',
        ],
        'sin_responsable' => [
            'asunto' => 'Alerta inventario - Equipos sin responsable',
            'mensaje' => 'El colegio seleccionado tiene {total} PC sin responsable asignado.',
        ],
        'en_reparacion' => [
            'asunto' => 'Alerta inventario - Equipos en reparación',
            'mensaje' => 'El colegio seleccionado tiene {total} PC en reparación.',
        ],
        'dados_baja' => [
            'asunto' => 'Alerta inventario - Equipos dados de baja',
            'mensaje' => 'El colegio seleccionado tiene {total} PC dados de baja.',
        ],
        'sin_valor' => [
            'asunto' => 'Alerta inventario - Equipos sin valor registrado',
            'mensaje' => 'El colegio seleccionado tiene {total} PC sin valor de compra registrado.',
        ],
    ];
}

function inv_dash_configurar_mail(PHPMailer $mail): void
{
    $mail->isSMTP();
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
    $mail->Host = 'smtp.itdchile.cl';
    $mail->Port = 46500;
    $mail->SMTPAuth = true;
    $mail->Username = 'm.gutierrez';
    $mail->Password = 'Seduc2024.,';
    $mail->SMTPSecure = 'tcp';
    $mail->CharSet = 'UTF-8';
    $mail->setFrom('seduc.informa@seduc.cl', 'SeducSPA');
}

function inv_dash_enviar_correo_alerta(array $destinatarios, string $asunto, string $mensaje): void
{
    $mail = new PHPMailer(true);
    inv_dash_configurar_mail($mail);

    foreach ($destinatarios as $destinatario) {
        $mail->addAddress((string)$destinatario['email'], (string)$destinatario['nombre']);
    }

    $mensajeSeguro = nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'));
    $mail->isHTML(true);
    $mail->Subject = $asunto;
    $mail->Body = "
        <div style='font-family:Arial,sans-serif;font-size:14px;color:#1f2937;line-height:1.55'>
            <p>Estimado/a,</p>
            <p>{$mensajeSeguro}</p>
            <p>Este aviso fue generado desde el dashboard de inventario.</p>
        </div>
    ";
    $mail->AltBody = $mensaje;
    $mail->send();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        inv_dash_alerta_responder(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
    }

    if ($idUsuarioSession <= 0) {
        inv_dash_alerta_responder(['ok' => false, 'mensaje' => 'Sesión expirada.'], 401);
    }

    $alcanceInventario = $inventario->obtenerAlcanceInventario($idUsuarioSession);
    $perfilInventario = (int)($alcanceInventario['id_perfil'] ?? 1);

    if ($perfilInventario < 2) {
        inv_dash_alerta_responder(['ok' => false, 'mensaje' => 'No tienes permiso para usar el dashboard de inventario.'], 403);
    }

    $accion = trim((string)($_POST['accion'] ?? 'enviar'));
    $idColegio = (int)($_POST['id_colegio'] ?? 0);
    $tipoAlerta = trim((string)($_POST['tipo_alerta'] ?? ''));
    $totalAlerta = max(0, (int)($_POST['total_alerta'] ?? 0));
    $alertasMeta = inv_dash_alertas_meta();

    if ($idColegio <= 0) {
        inv_dash_alerta_responder(['ok' => false, 'mensaje' => 'Seleccione un colegio específico para enviar correos a sus responsables.'], 422);
    }

    if (!$inventario->colegioPermitidoPorAlcance($idColegio, $alcanceInventario)) {
        inv_dash_alerta_responder(['ok' => false, 'mensaje' => 'No tienes permiso para enviar correos a ese colegio.'], 403);
    }

    if (!isset($alertasMeta[$tipoAlerta])) {
        inv_dash_alerta_responder(['ok' => false, 'mensaje' => 'Tipo de alerta no válido.'], 422);
    }

    if ($accion === 'responsables') {
        inv_dash_alerta_responder([
            'ok' => true,
            'responsables' => $inventario->obtenerResponsablesCorreoDashboard($idColegio),
        ]);
    }

    if ($accion !== 'enviar') {
        inv_dash_alerta_responder(['ok' => false, 'mensaje' => 'Acción no válida.'], 422);
    }

    if ($totalAlerta <= 0) {
        inv_dash_alerta_responder(['ok' => false, 'mensaje' => 'La alerta no tiene equipos pendientes.'], 422);
    }

    $destinatariosIds = $_POST['destinatarios'] ?? [];
    if (!is_array($destinatariosIds)) {
        $destinatariosIds = [$destinatariosIds];
    }
    $destinatariosIds = array_values(array_unique(array_filter(array_map('intval', $destinatariosIds))));

    if (empty($destinatariosIds)) {
        inv_dash_alerta_responder(['ok' => false, 'mensaje' => 'Seleccione al menos un responsable.'], 422);
    }

    $destinatarios = $inventario->obtenerResponsablesCorreoDashboard($idColegio, $destinatariosIds);
    if (empty($destinatarios)) {
        inv_dash_alerta_responder(['ok' => false, 'mensaje' => 'No hay destinatarios válidos para este colegio.'], 422);
    }

    $asunto = trim((string)($_POST['asunto'] ?? ''));
    $mensaje = trim((string)($_POST['mensaje'] ?? ''));
    $meta = $alertasMeta[$tipoAlerta];

    if ($asunto === '') {
        $asunto = $meta['asunto'];
    }
    if ($mensaje === '') {
        $mensaje = str_replace('{total}', (string)$totalAlerta, $meta['mensaje']);
    }

    inv_dash_enviar_correo_alerta($destinatarios, $asunto, $mensaje);

    inv_dash_alerta_responder([
        'ok' => true,
        'mensaje' => 'Correo enviado a ' . count($destinatarios) . ' responsable(s).',
    ]);
} catch (Exception $e) {
    error_log('Dashboard inventario alerta correo: ' . $e->getMessage());
    inv_dash_alerta_responder(['ok' => false, 'mensaje' => 'No fue posible enviar el correo.'], 500);
} catch (Throwable $e) {
    error_log('Dashboard inventario alerta: ' . $e->getMessage());
    inv_dash_alerta_responder(['ok' => false, 'mensaje' => 'No fue posible procesar la alerta.'], 500);
}
