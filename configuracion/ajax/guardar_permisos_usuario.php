<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    // ── Validar sesión ───────────────────────────────────────────────────────
    if ($idUsuarioSession <= 0) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Sesión no válida.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── Validar CSRF ─────────────────────────────────────────────────────────
    $csrfPost    = trim((string) ($_POST['csrf'] ?? ''));
    $csrfSession = (string) ($_SESSION['csrf_usuarios_permisos'] ?? '');
    if ($csrfPost === '' || !hash_equals($csrfSession, $csrfPost)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Token de seguridad inválido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── Parámetros ───────────────────────────────────────────────────────────
    $idUsuario = (int) ($_POST['usuario_id'] ?? 0);
    if ($idUsuario <= 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'ID de usuario no válido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // menus[]    → ids de menús principales marcados
    // submenus[] → ids de submenús marcados
    $menusChecked    = (array) ($_POST['menus']    ?? []);
    $submenusChecked = (array) ($_POST['submenus'] ?? []);

    // ── Guardar ──────────────────────────────────────────────────────────────
    require_once __DIR__ . '/../class/GestorPermisosUsuarios.php';
    $gestor    = new GestorPermisosUsuarios($bdato);
    $resultado = $gestor->guardarPermisosUsuario(
        $idUsuarioSession,
        $idUsuario,
        $menusChecked,
        $submenusChecked
    );

    echo json_encode([
        'ok'         => true,
        'mensaje'    => 'Permisos guardados correctamente.',
        'procesados' => $resultado['procesados'],
        'errores'    => $resultado['errores'],
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode([
        'ok'    => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}