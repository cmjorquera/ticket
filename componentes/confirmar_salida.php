<?php
/**
 * Confirmación antes de cerrar sesión. Intercepta cualquier enlace a
 * cerrar_sesion.php (menú del usuario y "Cerrar sesión" del menú lateral)
 * y muestra un modal. Se incluye desde includes/footer.php y componentes/scripts.php.
 */
if (empty($_SESSION['id'])) {
    return;
}
$salidaNombre = trim((string) ($_SESSION['nombre'] ?? ''));
?>
<dialog class="salida" id="dlgSalida" aria-labelledby="salidaTitulo" aria-describedby="salidaTexto">
    <div class="salida__box">
        <div class="salida__ico" aria-hidden="true"><i class="bi bi-box-arrow-right"></i></div>
        <h2 id="salidaTitulo">¿Cerrar sesión<?= $salidaNombre !== '' ? ', ' . htmlspecialchars($salidaNombre, ENT_QUOTES, 'UTF-8') : '' ?>?</h2>
        <p id="salidaTexto">Para volver a entrar tendrás que ingresar tu correo y contraseña.</p>
        <div class="salida__acciones">
            <button type="button" class="salida__btn salida__btn--sec" data-salida-cancelar autofocus>Cancelar</button>
            <a class="salida__btn salida__btn--peligro" id="salidaConfirmar" href="#"><i class="bi bi-box-arrow-right"></i> <span>Sí, cerrar sesión</span></a>
        </div>
    </div>
</dialog>
<style>
.salida{margin:auto;padding:0;border:0;border-radius:18px;width:min(380px,calc(100vw - 32px));box-shadow:0 30px 80px rgba(0,0,0,.28);background:var(--card,#fff);color:var(--fg,#1f2937);overflow:hidden}
.salida::backdrop{background:rgba(15,23,42,.5);backdrop-filter:blur(3px)}
.salida[open]{animation:salida-entra .18s ease-out}
.salida[open]::backdrop{animation:salida-fondo .18s ease-out}
@keyframes salida-entra{from{opacity:0;transform:translateY(8px) scale(.97)}to{opacity:1;transform:none}}
@keyframes salida-fondo{from{opacity:0}to{opacity:1}}
.salida__box{padding:26px 24px 20px;text-align:center}
.salida__ico{width:58px;height:58px;margin:0 auto 14px;border-radius:50%;display:grid;place-items:center;font-size:24px;color:var(--danger,#c0392b);background:var(--danger-light,#fdecea);box-shadow:0 0 0 8px rgba(192,57,43,.06)}
.salida h2{margin:0 0 6px;font-size:18px;font-weight:700}
.salida p{margin:0;font-size:13.5px;color:var(--muted,#6b7280);line-height:1.45}
.salida__acciones{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:20px}
.salida__btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:10px 14px;border-radius:10px;font:inherit;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;border:1px solid transparent;transition:background .15s,border-color .15s}
.salida__btn--sec{background:var(--card,#fff);border-color:var(--border,#e5e7eb);color:var(--fg,#1f2937)}
.salida__btn--sec:hover{background:var(--bg,#f4f6f9)}
.salida__btn--peligro{background:var(--danger,#c0392b);color:#fff}
.salida__btn--peligro:hover{background:#a93226;color:#fff}
.salida__btn.is-saliendo{pointer-events:none;opacity:.8}
</style>
<script>
(function () {
    'use strict';
    const dlg = document.getElementById('dlgSalida');
    if (!dlg || typeof dlg.showModal !== 'function') { return; }   // navegador antiguo: el enlace funciona igual
    const confirmar = document.getElementById('salidaConfirmar');
    document.addEventListener('click', function (e) {
        const a = e.target.closest('a[href*="cerrar_sesion.php"]');
        if (!a || a.id === 'salidaConfirmar' || e.ctrlKey || e.metaKey || e.shiftKey) { return; }
        e.preventDefault();
        confirmar.href = a.href;
        document.querySelectorAll('.dropdown-menu.open').forEach(function (m) { m.classList.remove('open'); });
        dlg.showModal();
    });
    dlg.addEventListener('click', function (e) {
        if (e.target === dlg || e.target.closest('[data-salida-cancelar]')) { dlg.close(); }
    });
    confirmar.addEventListener('click', function () {
        confirmar.classList.add('is-saliendo');
        confirmar.querySelector('span').textContent = 'Cerrando sesión…';
    });
})();
</script>
