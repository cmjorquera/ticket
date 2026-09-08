<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../helpers/tickets.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responder_json(['ok' => false, 'error' => 'Método no permitido.'], 405);
}

$usuarioId = (int) ($_SESSION['id'] ?? 0);
$ticketId = (int) ($_POST['ticket_id'] ?? 0);
$csrf = (string) ($_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

if (!ticket_csrf_valido($csrf)) {
    responder_json(['ok' => false, 'error' => 'La sesión venció. Recarga la página.'], 419);
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $ticket = ticket_buscar_para_acceso($ticketId, $db);
    if (!$ticket) {
        responder_json(['ok' => false, 'error' => 'Ticket no encontrado.'], 404);
    }
    if (!puede_ver_ticket($usuarioId, $ticket, $db)) {
        responder_json(['ok' => false, 'error' => 'No tienes permiso para adjuntar archivos.'], 403);
    }
    if ((int) ($ticket['id_estado'] ?? 0) === 5) {
        responder_json(['ok' => false, 'error' => 'El ticket está cerrado.'], 409);
    }

    $columnas = ticket_columnas_tabla('archivos_adjuntos_ticket', $db);
    foreach (['id_ticket', 'nombre_archivo', 'ruta_archivo', 'tipo_archivo', 'tamaño_archivo', 'id_usuario'] as $requerida) {
        if (!isset($columnas[$requerida])) {
            responder_json(['ok' => false, 'error' => 'Los adjuntos todavía no están habilitados en la base de datos.'], 503);
        }
    }

    $nombres = $_FILES['archivos']['name'] ?? [];
    if (!is_array($nombres)) {
        responder_json(['ok' => false, 'error' => 'No se recibieron archivos.'], 422);
    }
    $cantidadActual = (int) (($db->fetchOne(
        'SELECT COUNT(*) AS total FROM archivos_adjuntos_ticket WHERE id_ticket = ?',
        [$ticketId]
    )['total'] ?? 0));
    $cantidadNueva = count(array_filter($nombres, static fn ($nombre): bool => trim((string) $nombre) !== ''));
    if ($cantidadNueva < 1 || $cantidadActual + $cantidadNueva > 5) {
        responder_json(['ok' => false, 'error' => 'Cada ticket admite un máximo total de 5 archivos.'], 422);
    }

    $permitidos = [
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp',
        'application/pdf' => 'pdf', 'text/plain' => 'txt',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/zip' => 'zip', 'application/x-zip-compressed' => 'zip',
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $archivos = [];
    foreach ($nombres as $indice => $nombreOriginal) {
        $error = (int) ($_FILES['archivos']['error'][$indice] ?? UPLOAD_ERR_NO_FILE);
        $temporal = (string) ($_FILES['archivos']['tmp_name'][$indice] ?? '');
        $tamano = (int) ($_FILES['archivos']['size'][$indice] ?? 0);
        if ($error !== UPLOAD_ERR_OK || $temporal === '' || !is_uploaded_file($temporal)) {
            responder_json(['ok' => false, 'error' => 'Uno de los archivos no pudo subirse.'], 422);
        }
        $mime = (string) $finfo->file($temporal);
        if ($tamano <= 0 || $tamano > 5 * 1024 * 1024 || !isset($permitidos[$mime])) {
            responder_json(['ok' => false, 'error' => 'Revisa el tipo y tamaño de los archivos (máximo 5 MB cada uno).'], 422);
        }
        $archivos[] = [
            'original' => basename((string) $nombreOriginal),
            'temporal' => $temporal,
            'tamano' => $tamano,
            'mime' => $mime,
            'extension' => $permitidos[$mime],
        ];
    }

    $directorio = dirname(__DIR__) . '/uploads/tickets/' . $ticketId;
    if (!is_dir($directorio) && !mkdir($directorio, 0750, true) && !is_dir($directorio)) {
        throw new RuntimeException('No fue posible preparar la carpeta de adjuntos.');
    }

    $pdo = $db->getPDO();
    $guardados = [];
    $pdo->beginTransaction();
    try {
        foreach ($archivos as $archivo) {
            $nombreSeguro = bin2hex(random_bytes(16)) . '.' . $archivo['extension'];
            $destino = $directorio . '/' . $nombreSeguro;
            if (!move_uploaded_file($archivo['temporal'], $destino)) {
                throw new RuntimeException('No fue posible guardar un archivo adjunto.');
            }
            $guardados[] = $destino;
            $campos = ['id_ticket', 'nombre_archivo', 'ruta_archivo', 'tipo_archivo', '`tamaño_archivo`', 'id_usuario'];
            $valores = ['?', '?', '?', '?', '?', '?'];
            $params = [
                $ticketId,
                $archivo['original'],
                'uploads/tickets/' . $ticketId . '/' . $nombreSeguro,
                $archivo['mime'],
                $archivo['tamano'],
                $usuarioId,
            ];
            if (isset($columnas['fecha_subida'])) {
                $campos[] = 'fecha_subida';
                $valores[] = 'NOW()';
            }
            $db->execute(
                'INSERT INTO archivos_adjuntos_ticket (' . implode(', ', $campos) . ') VALUES (' . implode(', ', $valores) . ')',
                $params
            );
        }
        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        foreach ($guardados as $archivoGuardado) {
            @unlink($archivoGuardado);
        }
        throw $ex;
    }

    ticket_registrar_cambio($db, $ticketId, $usuarioId, 'adjuntar');
    responder_json(['ok' => true, 'mensaje' => 'Archivos adjuntados correctamente.'], 201);
} catch (Throwable $ex) {
    error_log('Error al guardar adjunto del ticket: ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible guardar los archivos.'], 500);
}
