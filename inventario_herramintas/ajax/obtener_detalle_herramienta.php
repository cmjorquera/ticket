<?php
require_once dirname(__DIR__) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

$idHerramienta = (int)($_GET['id_herramienta'] ?? 0);
$equipo = $inventario->obtenerHerramientaCompleta($idHerramienta);

if (!$equipo) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'mensaje' => 'Herramienta no encontrada.']);
    exit;
}

$galeriaHtml = '';
if (!empty($equipo['fotos'])) {
    $carouselId = 'carouselHerramienta' . (int)$idHerramienta;
    $galeriaHtml .= '<div class="inv-gallery-modal-wrap"><div id="' . inventario_h($carouselId) . '" class="carousel slide inv-gallery-carousel" data-bs-ride="false"><div class="carousel-inner">';

    foreach ($equipo['fotos'] as $index => $foto) {
        $urlFoto = inventario_h(inventario_url(ltrim($foto['ruta_foto'], '/')));
        $activeClass = $index === 0 ? ' active' : '';
        $galeriaHtml .= '<div class="carousel-item' . $activeClass . '"><a href="' . $urlFoto . '" target="_blank" class="inv-gallery-main-image"><img src="' . $urlFoto . '" class="d-block w-100" alt="Foto herramienta"></a></div>';
    }

    $galeriaHtml .= '</div><button class="carousel-control-prev" type="button" data-bs-target="#' . inventario_h($carouselId) . '" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Anterior</span></button><button class="carousel-control-next" type="button" data-bs-target="#' . inventario_h($carouselId) . '" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Siguiente</span></button></div><div class="inv-gallery-thumbs mt-3">';

    foreach ($equipo['fotos'] as $index => $foto) {
        $urlFoto = inventario_h(inventario_url(ltrim($foto['ruta_foto'], '/')));
        $thumbActiveClass = $index === 0 ? ' active' : '';
        $galeriaHtml .= '<button type="button" class="inv-gallery-thumb' . $thumbActiveClass . '" data-bs-target="#' . inventario_h($carouselId) . '" data-bs-slide-to="' . (int)$index . '" aria-label="Ir a imagen ' . ((int)$index + 1) . '"><img src="' . $urlFoto . '" alt="Miniatura herramienta"></button>';
    }

    $galeriaHtml .= '</div></div>';
} else {
    $galeriaHtml = '<div class="alert alert-light border mb-0">Esta herramienta no tiene imagenes registradas.</div>';
}

echo json_encode([
    'ok' => true,
    'equipo' => $equipo,
    'galeria_html' => $galeriaHtml,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
