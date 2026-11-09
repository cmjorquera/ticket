<?php
/**
 * ACTIVAR CUENTA (página pública, sin sesión).
 * La persona llega desde el enlace del correo (clases/ActivacionCuenta.php)
 * y elige su contraseña. Reemplaza a reiniciar_clave.php del sistema antiguo.
 */
declare(strict_types=1);

require_once __DIR__ . '/clases/Conexion.php';
require_once __DIR__ . '/clases/ActivacionCuenta.php';

header('Content-Type: text/html; charset=UTF-8');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');   // el token no se filtra a otros sitios

$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$estado = 'formulario';   // formulario | listo | invalido | vencido
$error = '';
$nombre = '';

try {
    $act = new ActivacionCuenta(Conexion::getInstance('sistema_panel_central'));
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        try {
            $u = $act->activar($token, (string) ($_POST['clave'] ?? ''), (string) ($_POST['repetir'] ?? ''));
            $nombre = (string) $u['nombre'];
            $estado = 'listo';
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }
    }
    if ($estado !== 'listo') {
        $r = $act->buscar($token);
        if ($r['motivo'] !== 'ok') {
            $estado = $r['motivo'];
        } else {
            $nombre = (string) $r['usuario']['nombre'];
            $email = (string) $r['usuario']['email'];
        }
    }
} catch (Throwable $e) {
    error_log('[activar_cuenta] ' . $e->getMessage());
    $estado = 'invalido';
}
$h = static fn (?string $t): string => htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Activar cuenta · SEDUC Panel Central</title>
    <link rel="icon" type="image/png" href="imagenes/logo_seduc.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --navy-900:#0f2440; --navy-800:#16365c; --navy-700:#1d4675; --ambar:#e8a33d; --texto:#111827; --suave:#6b7280; --borde:#e5e7eb; --ok:#2f7d32; --error:#991b1b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { min-height:100vh; display:grid; place-items:center; padding:24px 16px; font-family:Inter,system-ui,sans-serif; color:var(--texto);
               background:radial-gradient(circle at 85% 10%, rgba(232,163,61,.18), transparent 35%), linear-gradient(180deg,#15335a 0%,var(--navy-900) 100%); }
        .tarjeta { width:min(440px,100%); background:#fff; border-radius:18px; box-shadow:0 30px 80px rgba(0,0,0,.35); overflow:hidden; }
        .cab { padding:22px 26px; background:var(--navy-800); border-bottom:4px solid var(--ambar); display:flex; align-items:center; gap:12px; color:#fff; }
        .cab img { width:40px; height:40px; border-radius:10px; background:#fff; padding:4px; }
        .cab strong { display:block; font-size:16px; } .cab span { font-size:12px; opacity:.8; }
        .cuerpo { padding:26px; }
        h1 { font-size:21px; margin-bottom:6px; } p { color:var(--suave); font-size:14px; line-height:1.5; }
        .campo { margin-top:16px; } label { display:block; font-size:13px; font-weight:600; margin-bottom:6px; }
        .clave { position:relative; }
        input[type=password], input[type=text] { width:100%; padding:12px 44px 12px 14px; border:1px solid var(--borde); border-radius:10px; font:inherit; font-size:15px; background:#f9fafb; }
        input:focus { outline:none; border-color:var(--navy-700); background:#fff; box-shadow:0 0 0 3px rgba(29,70,117,.12); }
        .ojo { position:absolute; right:6px; top:50%; transform:translateY(-50%); border:0; background:none; color:var(--suave); padding:8px; cursor:pointer; border-radius:8px; }
        .reglas { list-style:none; display:flex; flex-wrap:wrap; gap:6px 14px; margin-top:12px; font-size:12.5px; color:var(--suave); }
        .reglas li.ok { color:var(--ok); }
        .boton { display:flex; justify-content:center; align-items:center; gap:8px; width:100%; margin-top:20px; padding:13px; border:0; border-radius:10px; background:var(--navy-800); color:#fff; font:inherit; font-weight:600; font-size:15px; cursor:pointer; text-decoration:none; }
        .boton:disabled { opacity:.55; cursor:not-allowed; }
        .error { margin-top:14px; padding:10px 12px; border-radius:10px; background:#fef2f2; color:var(--error); font-size:13.5px; border:1px solid #fecaca; }
        .icono { width:64px; height:64px; margin:0 auto 14px; border-radius:50%; display:grid; place-items:center; font-size:30px; }
        .icono.ok { background:#e8f5e9; color:var(--ok); } .icono.mal { background:#fef2f2; color:var(--error); }
        .centro { text-align:center; }
    </style>
</head>
<body>
<main class="tarjeta">
    <div class="cab">
        <img src="imagenes/logo_seduc.png" alt="">
        <div><strong>SEDUC Chile</strong><span>Panel Central</span></div>
    </div>
    <div class="cuerpo">
    <?php if ($estado === 'listo'): ?>
        <div class="centro">
            <div class="icono ok"><i class="bi bi-check-lg"></i></div>
            <h1>¡Listo, <?= $h($nombre) ?>!</h1>
            <p>Tu cuenta quedó activa. Ya puedes ingresar con tu correo y la contraseña que elegiste.</p>
            <a class="boton" href="index.php"><i class="bi bi-box-arrow-in-right"></i> Iniciar sesión</a>
        </div>
    <?php elseif ($estado === 'formulario'): ?>
        <h1>Hola, <?= $h($nombre) ?></h1>
        <p>Elige la contraseña para tu cuenta <strong><?= $h($email ?? '') ?></strong>.</p>
        <?php if ($error): ?><div class="error" role="alert"><?= $h($error) ?></div><?php endif; ?>
        <form method="post" action="activar_cuenta.php" id="formActivar" autocomplete="off">
            <input type="hidden" name="token" value="<?= $h($token) ?>">
            <input type="text" name="usuario" value="<?= $h($email ?? '') ?>" autocomplete="username" hidden>
            <div class="campo">
                <label for="clave">Nueva contraseña</label>
                <div class="clave"><input type="password" id="clave" name="clave" required maxlength="72" autocomplete="new-password" autofocus><button type="button" class="ojo" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button></div>
            </div>
            <div class="campo">
                <label for="repetir">Repítela</label>
                <div class="clave"><input type="password" id="repetir" name="repetir" required maxlength="72" autocomplete="new-password"><button type="button" class="ojo" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button></div>
            </div>
            <ul class="reglas">
                <li data-r="largo"><i class="bi bi-circle"></i> 8 caracteres o más</li>
                <li data-r="letra"><i class="bi bi-circle"></i> Una letra</li>
                <li data-r="numero"><i class="bi bi-circle"></i> Un número</li>
                <li data-r="iguales"><i class="bi bi-circle"></i> Coinciden</li>
            </ul>
            <button type="submit" class="boton" id="btnActivar" disabled><i class="bi bi-shield-check"></i> Activar mi cuenta</button>
        </form>
    <?php else: ?>
        <div class="centro">
            <div class="icono mal"><i class="bi bi-link-45deg"></i></div>
            <h1><?= $estado === 'vencido' ? 'El enlace venció' : 'Enlace no válido' ?></h1>
            <p><?= $estado === 'vencido'
                ? 'Por seguridad el enlace dura ' . ActivacionCuenta::DIAS_VIGENCIA . ' días. Pide a quien creó tu cuenta que te reenvíe la activación.'
                : 'Este enlace no existe o ya fue usado. Si ya activaste tu cuenta, inicia sesión normalmente.' ?></p>
            <a class="boton" href="index.php"><i class="bi bi-box-arrow-in-right"></i> Ir a iniciar sesión</a>
        </div>
    <?php endif; ?>
    </div>
</main>
<script>
(function () {
    var f = document.getElementById('formActivar');
    if (!f) { return; }
    var c = document.getElementById('clave'), r = document.getElementById('repetir'), b = document.getElementById('btnActivar');
    function revisar() {
        var v = c.value, ok = {
            largo: v.length >= 8, letra: /[A-Za-zÁÉÍÓÚÑáéíóúñ]/.test(v), numero: /\d/.test(v), iguales: v !== '' && v === r.value
        }, todo = true;
        Object.keys(ok).forEach(function (k) {
            var li = f.querySelector('[data-r="' + k + '"]');
            li.classList.toggle('ok', ok[k]);
            li.querySelector('i').className = 'bi ' + (ok[k] ? 'bi-check-circle-fill' : 'bi-circle');
            todo = todo && ok[k];
        });
        b.disabled = !todo;
    }
    c.addEventListener('input', revisar); r.addEventListener('input', revisar);
    f.addEventListener('click', function (e) {
        var o = e.target.closest('.ojo'); if (!o) { return; }
        var i = o.previousElementSibling, ver = i.type === 'password';
        i.type = ver ? 'text' : 'password';
        o.querySelector('i').className = 'bi ' + (ver ? 'bi-eye-slash' : 'bi-eye');
    });
    f.addEventListener('submit', function () { b.disabled = true; b.innerHTML = 'Activando…'; });
})();
</script>
</body>
</html>
