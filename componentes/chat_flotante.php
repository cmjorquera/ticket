<?php
/**
 * Burbuja de chat flotante (abajo a la derecha) en todas las páginas con sesión.
 * Por ahora solo abre una conversación de bienvenida; más adelante se conectará
 * al chatbot (proyecto chatbot_jorquera). Se incluye desde includes/footer.php
 * y componentes/scripts.php.
 */
if (empty($_SESSION['id'])) {
    return;
}
$chatDepth = $depth ?? '';
$chatNombre = trim((string) ($_SESSION['nombre'] ?? ''));
$chatVersion = static function (string $ruta) {
    $f = dirname(__DIR__) . '/' . $ruta;
    return is_file($f) ? (string) filemtime($f) : '1';
};
?>
<link rel="stylesheet" href="<?= $chatDepth ?>css/chat_flotante.css?v=<?= $chatVersion('css/chat_flotante.css') ?>">
<div class="chatf" id="chatFlotante" data-usuario="<?= (int) $_SESSION['id'] ?>" data-nombre="<?= htmlspecialchars($chatNombre, ENT_QUOTES, 'UTF-8') ?>">
    <section class="chatf-panel" id="chatfPanel" role="dialog" aria-labelledby="chatfTitulo" aria-hidden="true">
        <header class="chatf-head">
            <div class="chatf-avatar"><i class="bi bi-robot" aria-hidden="true"></i></div>
            <div class="chatf-head__texto">
                <strong id="chatfTitulo">Asistente SEDUC</strong>
                <span><i class="chatf-dot"></i> En preparación</span>
            </div>
            <button type="button" class="chatf-icono" id="chatfReiniciar" title="Nueva conversación" aria-label="Nueva conversación"><i class="bi bi-arrow-counterclockwise"></i></button>
            <button type="button" class="chatf-icono" id="chatfCerrar" title="Cerrar" aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
        </header>
        <div class="chatf-mensajes" id="chatfMensajes" aria-live="polite"></div>
        <form class="chatf-form" id="chatfForm" autocomplete="off">
            <textarea id="chatfTexto" rows="1" maxlength="500" placeholder="Escribe tu pregunta…" aria-label="Mensaje"></textarea>
            <button type="submit" class="chatf-enviar" aria-label="Enviar"><i class="bi bi-send-fill"></i></button>
        </form>
    </section>
    <button type="button" class="chatf-burbuja" id="chatfBurbuja" aria-label="Abrir chat" aria-expanded="false" aria-controls="chatfPanel">
        <i class="bi bi-chat-dots-fill chatf-burbuja__abrir" aria-hidden="true"></i>
        <i class="bi bi-x-lg chatf-burbuja__cerrar" aria-hidden="true"></i>
    </button>
</div>
<script src="<?= $chatDepth ?>js/chat_flotante.js?v=<?= $chatVersion('js/chat_flotante.js') ?>" defer></script>
