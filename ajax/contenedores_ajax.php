<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responder_json(['ok' => false, 'error' => 'Método no permitido.'], 405);
}

$usuarioId = (int) ($_SESSION['id'] ?? 0);
$accion = strtolower(trim((string) ($_POST['accion'] ?? '')));
$csrf = (string) ($_POST['csrf'] ?? '');
$csrfSesion = (string) ($_SESSION['csrf_contenedores'] ?? '');

if ($csrfSesion === '' || !hash_equals($csrfSesion, $csrf)) {
    responder_json(['ok' => false, 'error' => 'La sesión de edición expiró. Recarga la página.'], 419);
}

function validar_datos_contenedor(array $datos): array
{
    $nombre = trim((string) ($datos['nombre'] ?? ''));
    $url = trim((string) ($datos['url'] ?? ''));
    if ($nombre === '' || $url === '') {
        responder_json(['ok' => false, 'error' => 'Nombre y URL son obligatorios.'], 422);
    }
    if (strlen($nombre) > 100 || strlen($url) > 2048) {
        responder_json(['ok' => false, 'error' => 'El nombre o la URL superan el largo permitido.'], 422);
    }
    if (!preg_match('~^https?://~i', $url)) {
        $url = 'https://' . ltrim($url, '/');
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        responder_json(['ok' => false, 'error' => 'Ingresa una URL HTTP o HTTPS válida.'], 422);
    }
    return [$nombre, $url];
}

function directorio_imagenes_contenedor(int $usuarioId): array
{
    $raiz = dirname(__DIR__) . '/imagenes';
    $usaNivelInterno = is_dir($raiz . '/imagenes');
    $base = $usaNivelInterno ? $raiz . '/imagenes' : $raiz;
    $prefijo = $usaNivelInterno ? 'imagenes/' : '';
    $relativa = 'usuarios_contenedores/usuario_' . $usuarioId;
    return [$base . '/' . $relativa, $prefijo . $relativa];
}

function guardar_imagen_contenedor(array $archivo, int $usuarioId): ?string
{
    $error = (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('La imagen no pudo cargarse correctamente.');
    }
    if ((int) ($archivo['size'] ?? 0) <= 0 || (int) $archivo['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('La imagen debe pesar menos de 2 MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file((string) $archivo['tmp_name']);
    $extensiones = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    if (!isset($extensiones[$mime])) {
        throw new RuntimeException('El archivo debe ser una imagen JPG, PNG, GIF o WEBP.');
    }

    [$directorio, $rutaBd] = directorio_imagenes_contenedor($usuarioId);
    if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
        throw new RuntimeException('No fue posible preparar la carpeta de imágenes.');
    }
    $nombre = 'contenedor_' . bin2hex(random_bytes(12)) . '.' . $extensiones[$mime];
    if (!move_uploaded_file((string) $archivo['tmp_name'], $directorio . '/' . $nombre)) {
        throw new RuntimeException('No fue posible guardar la imagen.');
    }
    return $rutaBd . '/' . $nombre;
}

function eliminar_imagen_contenedor(string $imagen, int $usuarioId): void
{
    $imagen = str_replace('\\', '/', trim($imagen));
    if ($imagen === '' || str_contains($imagen, '..')) {
        return;
    }
    [$directorio] = directorio_imagenes_contenedor($usuarioId);
    $nombre = basename($imagen);
    $ruta = $directorio . '/' . $nombre;
    if (is_file($ruta) && !@unlink($ruta)) {
        error_log('No fue posible eliminar la imagen del contenedor: ' . $ruta);
    }
}

try {
    $db = Conexion::getInstance('sistema_panel_central');

    if ($accion === 'guardar') {
        [$nombre, $url] = validar_datos_contenedor($_POST);
        $imagen = guardar_imagen_contenedor($_FILES['imagen_file'] ?? [], $usuarioId);
        try {
            $db->execute(
                'INSERT INTO contenedor (id_usuario, nombre, url_, imagen) VALUES (?, ?, ?, ?)',
                [$usuarioId, $nombre, $url, $imagen]
            );
        } catch (Throwable $ex) {
            if ($imagen !== null) {
                eliminar_imagen_contenedor($imagen, $usuarioId);
            }
            throw $ex;
        }
        responder_json(['ok' => true, 'mensaje' => 'Contenedor creado.']);
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        responder_json(['ok' => false, 'error' => 'El contenedor indicado no es válido.'], 422);
    }
    $actual = $db->fetchOne('SELECT id, imagen FROM contenedor WHERE id = ? AND id_usuario = ? LIMIT 1', [$id, $usuarioId]);
    if (!$actual) {
        responder_json(['ok' => false, 'error' => 'Contenedor no encontrado.'], 404);
    }

    if ($accion === 'actualizar') {
        [$nombre, $url] = validar_datos_contenedor($_POST);
        $imagenNueva = guardar_imagen_contenedor($_FILES['imagen_file'] ?? [], $usuarioId);
        try {
            if ($imagenNueva !== null) {
                $db->execute('UPDATE contenedor SET nombre = ?, url_ = ?, imagen = ? WHERE id = ? AND id_usuario = ?', [$nombre, $url, $imagenNueva, $id, $usuarioId]);
                eliminar_imagen_contenedor((string) ($actual['imagen'] ?? ''), $usuarioId);
            } else {
                $db->execute('UPDATE contenedor SET nombre = ?, url_ = ? WHERE id = ? AND id_usuario = ?', [$nombre, $url, $id, $usuarioId]);
            }
        } catch (Throwable $ex) {
            if ($imagenNueva !== null) {
                eliminar_imagen_contenedor($imagenNueva, $usuarioId);
            }
            throw $ex;
        }
        responder_json(['ok' => true, 'mensaje' => 'Contenedor actualizado.']);
    }

    if ($accion === 'eliminar') {
        $db->execute('DELETE FROM contenedor WHERE id = ? AND id_usuario = ?', [$id, $usuarioId]);
        eliminar_imagen_contenedor((string) ($actual['imagen'] ?? ''), $usuarioId);
        responder_json(['ok' => true, 'mensaje' => 'Contenedor eliminado.']);
    }

    responder_json(['ok' => false, 'error' => 'Acción no válida.'], 400);
} catch (RuntimeException $ex) {
    responder_json(['ok' => false, 'error' => $ex->getMessage()], 422);
} catch (Throwable $ex) {
    error_log('Error en contenedores_ajax.php: ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible completar la operación.'], 500);
}
