<?php
$textoCarga = isset($textoCarga) && trim((string)$textoCarga) !== ''
    ? trim((string)$textoCarga)
    : 'Cargando datos...';
$loaderVisibleInicialmente = isset($loaderVisibleInicialmente) ? (bool)$loaderVisibleInicialmente : true;
$loaderFallbackMs = isset($loaderFallbackMs) ? max(0, (int)$loaderFallbackMs) : 8000;

// Una misma respuesta puede pasar por helpers compatibles. El guard evita
// duplicar el único ID público del loader dentro del documento actual.
if (!empty($GLOBALS['pantalla_cargando_renderizada'])) {
    return;
}
$GLOBALS['pantalla_cargando_renderizada'] = true;
?>
<style>
    .pantalla-cargando {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: flex;
        width: 100vw;
        height: 100vh;
        height: 100dvh;
        margin: 0;
        padding: clamp(16px, 4vw, 32px);
        align-items: center;
        justify-content: center;
        overflow: hidden;
        box-sizing: border-box;
        background: rgba(255, 255, 255, .96);
    }
    .pantalla-cargando[hidden] { display: none !important; }
    .pantalla-cargando__contenido {
        display: flex;
        width: min(360px, 100%);
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 14px;
        text-align: center;
    }
    .pantalla-cargando__texto {
        color: #004e8c;
        font: 700 .98rem/1.45 system-ui, -apple-system, "Segoe UI", sans-serif;
        letter-spacing: .01em;
    }
    body.pantalla-cargando-activa { overflow: hidden !important; }
    @media (prefers-reduced-motion: reduce) {
        .pantalla-cargando, .pantalla-cargando * {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
        }
    }
</style>
<div id="pantalla-cargando"
     class="pantalla-cargando"
     role="status"
     aria-live="polite"
     aria-busy="<?= $loaderVisibleInicialmente ? 'true' : 'false' ?>"
     data-fallback-ms="<?= (int)$loaderFallbackMs ?>"
     <?= $loaderVisibleInicialmente ? '' : 'hidden' ?>>
    <div class="pantalla-cargando__contenido">
        <?php include __DIR__ . '/logo_3d_seduc.php'; ?>
        <div class="pantalla-cargando__texto"><?= htmlspecialchars($textoCarga, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
</div>
<script>
(function (global) {
    'use strict';
    var fallbackTimer = null;
    var capasObservadas = [];
    var ajustePendiente = false;

    /**
     * Calcula la porcion de ESTA pagina que realmente es visible en la
     * ventana superior. Es necesario cuando el modulo vive dentro de uno o
     * mas iframes autoajustables: 100vh corresponde al iframe completo, no a
     * la franja que el usuario esta viendo despues de hacer scroll.
     */
    function medirViewportVisible() {
        var ventanas = [global];
        var marcos = [];
        var actual = global;
        try {
            while (actual.parent && actual.parent !== actual && actual.frameElement) {
                marcos.push(actual.frameElement.getBoundingClientRect());
                actual = actual.parent;
                ventanas.push(actual);
            }
        } catch (e) {
            return { embebido: false, left: 0, top: 0, width: global.innerWidth, height: global.innerHeight };
        }

        if (ventanas.length === 1) {
            return { embebido: false, left: 0, top: 0, width: global.innerWidth, height: global.innerHeight };
        }

        var origenes = new Array(ventanas.length);
        origenes[ventanas.length - 1] = { left: 0, top: 0 };
        for (var i = ventanas.length - 2; i >= 0; i--) {
            origenes[i] = {
                left: origenes[i + 1].left + marcos[i].left,
                top: origenes[i + 1].top + marcos[i].top
            };
        }

        var izquierda = origenes[0].left;
        var arriba = origenes[0].top;
        var derecha = izquierda + ventanas[0].innerWidth;
        var abajo = arriba + ventanas[0].innerHeight;
        for (var j = 1; j < ventanas.length; j++) {
            izquierda = Math.max(izquierda, origenes[j].left);
            arriba = Math.max(arriba, origenes[j].top);
            derecha = Math.min(derecha, origenes[j].left + ventanas[j].innerWidth);
            abajo = Math.min(abajo, origenes[j].top + ventanas[j].innerHeight);
        }

        return {
            embebido: true,
            left: Math.max(0, izquierda - origenes[0].left),
            top: Math.max(0, arriba - origenes[0].top),
            width: Math.max(1, derecha - izquierda),
            height: Math.max(1, abajo - arriba)
        };
    }

    global.sincronizarCapaViewportVisible = function (elemento) {
        if (!elemento) return false;
        var area = medirViewportVisible();
        if (!area.embebido) {
            elemento.style.position = 'fixed';
            elemento.style.inset = '0';
            elemento.style.width = '100vw';
            elemento.style.height = '100vh';
            if (global.CSS && CSS.supports && CSS.supports('height', '100dvh')) {
                elemento.style.height = '100dvh';
            }
            return true;
        }
        elemento.style.position = 'absolute';
        elemento.style.inset = 'auto';
        elemento.style.left = (global.scrollX + area.left) + 'px';
        elemento.style.top = (global.scrollY + area.top) + 'px';
        elemento.style.width = area.width + 'px';
        elemento.style.height = area.height + 'px';
        return true;
    };

    function reajustarCapas() {
        ajustePendiente = false;
        capasObservadas.forEach(function (elemento) {
            if (elemento && elemento.isConnected) global.sincronizarCapaViewportVisible(elemento);
        });
    }

    function solicitarReajuste() {
        if (ajustePendiente) return;
        ajustePendiente = true;
        global.requestAnimationFrame(reajustarCapas);
    }

    global.observarCapaViewportVisible = function (elemento) {
        if (!elemento) return false;
        if (capasObservadas.indexOf(elemento) === -1) capasObservadas.push(elemento);
        if (!elemento.__viewportVisibleObservado) {
            elemento.__viewportVisibleObservado = true;
            var ventana = global;
            try {
                while (ventana) {
                    ventana.addEventListener('scroll', solicitarReajuste, { passive: true });
                    ventana.addEventListener('resize', solicitarReajuste, { passive: true });
                    if (!ventana.parent || ventana.parent === ventana) break;
                    ventana = ventana.parent;
                }
            } catch (e) {}
        }
        global.sincronizarCapaViewportVisible(elemento);
        return true;
    };

    function obtenerLoader() {
        return document.getElementById('pantalla-cargando');
    }

    function limpiarFallback() {
        if (fallbackTimer !== null) {
            global.clearTimeout(fallbackTimer);
            fallbackTimer = null;
        }
    }

    function programarFallback(loader, fallbackMs) {
        limpiarFallback();
        var espera = Number(fallbackMs);
        if (!Number.isFinite(espera)) espera = Number(loader.dataset.fallbackMs || 0);
        if (espera <= 0) return;
        fallbackTimer = global.setTimeout(function () {
            global.ocultarPantallaCarga();
        }, espera);
    }

    global.mostrarPantallaCarga = function (mensaje, fallbackMs) {
        var loader = obtenerLoader();
        if (!loader) return false;
        var texto = loader.querySelector('.pantalla-cargando__texto');
        if (texto && typeof mensaje === 'string' && mensaje.trim() !== '') {
            texto.textContent = mensaje.trim();
        }
        loader.hidden = false;
        loader.style.display = 'flex';
        loader.setAttribute('aria-busy', 'true');
        if (document.body) document.body.classList.add('pantalla-cargando-activa');
        global.observarCapaViewportVisible(loader);
        programarFallback(loader, fallbackMs);
        return true;
    };

    global.ocultarPantallaCarga = function () {
        limpiarFallback();
        var loader = obtenerLoader();
        if (loader) {
            loader.hidden = true;
            loader.style.display = 'none';
            loader.setAttribute('aria-busy', 'false');
        }
        if (document.body) document.body.classList.remove('pantalla-cargando-activa');
        return !!loader;
    };

    // Alias temporal para módulos antiguos. Todos terminan usando la API raíz.
    global.__ocultarCargando = global.ocultarPantallaCarga;

    function iniciar() {
        var loader = obtenerLoader();
        if (!loader || loader.hidden) return;
        if (document.body) document.body.classList.add('pantalla-cargando-activa');
        global.observarCapaViewportVisible(loader);
        programarFallback(loader);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar, { once: true });
    } else {
        iniciar();
    }
})(window);
</script>
