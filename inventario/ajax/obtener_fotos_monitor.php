<?php
require_once dirname(__DIR__) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $idMonitor = (int)($_GET['id_monitor'] ?? 0);
    if ($idMonitor <= 0) {
        throw new RuntimeException('Monitor no válido.');
    }
    $monitor = $inventario->obtenerMonitorPorId($idMonitor);
    if (!$monitor) {
        throw new RuntimeException('Monitor no encontrado.');
    }
    $alcanceInventario = $inventario->obtenerAlcanceInventario($idUsuarioSession);
    if (!$inventario->colegioPermitidoPorAlcance((int)($monitor['id_colegio'] ?? 0), $alcanceInventario)) {
        throw new RuntimeException('No tienes permiso para ver este monitor.');
    }

    $fotos = $monitor['fotos'] ?? [];
    ob_start();
    if (empty($fotos)): ?>
        <div class="alert alert-light border mb-0">Sin imágenes registradas.</div>
    <?php else: ?>
        <div class="row g-3">
        <?php foreach ($fotos as $foto):
            $ruta = htmlspecialchars($foto['ruta_foto'], ENT_QUOTES, 'UTF-8');
            $esPrincipal = (int)$foto['principal'] === 1;
        ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="position-relative">
                    <img src="/<?= $ruta ?>" alt="Foto monitor" class="img-fluid rounded shadow-sm">
                    <?php if ($esPrincipal): ?>
                        <span class="inv-badge-principal-overlay">⭐ Principal</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif;
    $html = ob_get_clean();

    echo json_encode([
        'ok'         => true,
        'monitor'    => ['nombre_monitor' => $monitor['nombre_monitor'] ?? ''],
        'galeria_html' => $html,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
