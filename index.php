<?php
/**
 * INDEX · Login · SEDUC Chile - Panel Central
 * Sin validacion CSRF (sesiones inestables en este hosting).
 * Proteccion: bcrypt + bloqueo por intentos fallidos.
 */

// Fix sesiones
$_sp = '/var/cpanel/php/sessions/ea-php83';
if (!is_dir($_sp)) { session_save_path(sys_get_temp_dir()); }
session_start();

require_once __DIR__ . '/clases/Conexion.php';

const MAX_INTENTOS = 3;
$error = '';

if (!empty($_SESSION['id'])) {
    header('Location: contenedores.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim((string)($_POST['email'] ?? ''));
    $clave = (string)($_POST['password'] ?? '');

    if ($email === '' || $clave === '') {
        $error = 'Por favor completa todos los campos.';
    } else {
        try {
            $db = Conexion::getInstance('sistema_panel_central');

            $usuario = $db->fetchOne(
                "SELECT id, nombre, email, clave, estado, intentos_fallidos, token_reinicio
                   FROM usuarios WHERE email = ? LIMIT 1",
                [$email]
            );

            if ($usuario === false) {
                $error = 'Usuario no encontrado.';
            } elseif ($usuario['estado'] === 'bloqueado') {
                $error = 'Tu cuenta está bloqueada. Contacta al administrador.';
            } elseif ($usuario['estado'] === 'inactivo') {
                $error = 'Tu cuenta está inactiva. Contacta al administrador.';
            } elseif (strcasecmp((string) $usuario['estado'], 'Pendiente') === 0 && (string) $usuario['clave'] === '') {
                // Cuenta nueva sin activar (clases/ActivacionCuenta.php): no cuenta como intento fallido
                $error = 'Tu cuenta aún no está activada. Usa el enlace que te llegó por correo para elegir tu contraseña, o pide que te lo reenvíen.';
            } elseif (!password_verify($clave, (string)$usuario['clave'])) {
                $intentos = (int)$usuario['intentos_fallidos'] + 1;
                if ($intentos >= MAX_INTENTOS) {
                    $db->execute(
                        "UPDATE usuarios SET estado = 'bloqueado', intentos_fallidos = ? WHERE id = ?",
                        [$intentos, (int)$usuario['id']]
                    );
                    $error = 'Cuenta bloqueada por exceso de intentos. Contacta al administrador.';
                } else {
                    $db->execute(
                        "UPDATE usuarios SET intentos_fallidos = ? WHERE id = ?",
                        [$intentos, (int)$usuario['id']]
                    );
                    $restantes = MAX_INTENTOS - $intentos;
                    $error = "Contraseña incorrecta. Te queda" . ($restantes === 1 ? '' : 'n')
                           . " {$restantes} intento" . ($restantes === 1 ? '' : 's') . '.';
                }
            } else {
                $db->execute(
                    "UPDATE usuarios SET intentos_fallidos = 0 WHERE id = ?",
                    [(int)$usuario['id']]
                );
                $perfiles = $db->fetchAll(
                    "SELECT pf.id_perfil, pf.nombre_perfil
                       FROM usuario_perfil up
       INNER JOIN perfiles pf ON pf.id_perfil = up.id_perfil
                      WHERE up.id_usuario = ?
                      ORDER BY pf.id_perfil ASC",
                    [(int)$usuario['id']]
                );

                

                session_regenerate_id(true);
                $_SESSION['id']       = (int)$usuario['id'];
                $_SESSION['nombre']   = $usuario['nombre'];
                $_SESSION['email']    = $usuario['email'];
                $_SESSION['perfil']   = $perfiles[0]['nombre_perfil'] ?? 'Sin perfil';
                $_SESSION['perfiles'] = array_column($perfiles, 'nombre_perfil');

                // Cuenta nueva con clave temporal (clases/AltaUsuario.php): debe cambiarla antes de seguir
                if (($usuario['token_reinicio'] ?? '') === 'CAMBIAR_CLAVE') {
                    $_SESSION['debe_cambiar_clave'] = true;
                    header('Location: mi_cuenta/perfil.php?cambiar_clave=1');
                    exit;
                }
                header('Location: contenedores.php');
                exit;
            }
        } catch (\Throwable $e) {
            error_log('[login] ' . $e->getMessage());
            $error = 'No fue posible iniciar sesión en este momento. Intenta nuevamente o contacta al administrador.';
        }
    }
}

$email_valor = htmlspecialchars((string)($_POST['email'] ?? ''), ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · SEDUC Panel Central</title>
    <link rel="icon" type="image/png" href="imagenes/logo_seduc.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --navy-900: #0f2440;
            --navy-800: #16365c;
            --navy-700: #1d4675;
            --ambar: #e8a33d;
            --gris-fondo: #f3f4f6;
            --texto: #111827;
            --texto-suave: #6b7280;
            --borde: #e5e7eb;
            --error-bg: #fef2f2; --error-tx: #991b1b; --error-bd: #fecaca;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--texto);
            background: var(--gris-fondo);
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        /* ---------- Panel izquierdo ---------- */
        .panel-info {
            position: relative;
            color: #e5e7eb;
            background: linear-gradient(180deg, #15335a 0%, var(--navy-900) 100%);
            padding: 48px 56px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .panel-info::after {
            content: ""; position: absolute; pointer-events: none;
            width: 560px; height: 536px; right: -200px; bottom: -190px;
            background: url('imagenes/logo_seduc_blanco.png') no-repeat center / contain;
            opacity: .06;
        }
        .panel-info > * { position: relative; z-index: 1; }
        .marca { display: flex; align-items: center; gap: 14px; }
        .marca-icono {
            width: 48px; height: 48px; border-radius: 50%;
            background: #fff;
            display: grid; place-items: center;
        }
        .marca-icono img { width: 34px; height: 34px; object-fit: contain; }
        .marca strong { display: block; color: #fff; font-size: 16px; font-weight: 700; }
        .marca span { font-size: 13px; color: #cbd5e1; }

        .contenido { margin: auto 0; max-width: 560px; padding: 40px 0; }
        .acento { width: 40px; height: 3px; background: var(--ambar); border-radius: 2px; margin-bottom: 28px; }
        .contenido h1 {
            color: #fff; font-size: 34px; line-height: 1.18; font-weight: 800;
            letter-spacing: -.02em; margin-bottom: 18px;
        }
        .contenido p.desc { font-size: 15px; line-height: 1.65; color: #cbd5e1; margin-bottom: 28px; }

        .modulos { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 28px; margin-bottom: 34px; padding-top: 22px; border-top: 1px solid rgba(255,255,255,.1); }
        .modulo { display: flex; align-items: flex-start; gap: 10px; font-size: 13.5px; color: #e2e8f0; line-height: 1.35; }
        .modulo i {
            flex: 0 0 28px; height: 28px; border-radius: 8px;
            background: rgba(232,163,61,.14); color: var(--ambar);
            display: grid; place-items: center; font-size: 14px;
        }
        .modulo small { display: block; color: #94a3b8; font-size: 12px; margin-top: 1px; }

        .lema { color: #cbd5e1; font-style: italic; font-size: 14px; }
        .lema b { color: var(--ambar); font-weight: 600; }
        .copy { font-size: 12px; color: #64748b; }

        /* ---------- Panel derecho ---------- */
        .panel-acceso {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 40px 24px; gap: 28px;
        }
        .indicadores {
            width: 100%; max-width: 480px;
            border-radius: 16px; color: #fff;
            background: var(--navy-800);
            border-top: 3px solid var(--ambar);
            box-shadow: 0 10px 24px rgba(15,36,64,.14);
            padding: 20px 8px 14px;
        }
        .indicadores-grid { display: grid; grid-template-columns: repeat(4, 1fr); }
        .indicador { text-align: center; padding: 4px 6px; }
        .indicador + .indicador { border-left: 1px solid rgba(255,255,255,.15); }
        .indicador i { font-size: 18px; color: #9fb7d6; display: block; margin-bottom: 6px; }
        .indicador .nombre { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #dbeafe; }
        .indicador .valor { font-size: 15px; font-weight: 700; margin-top: 4px; min-height: 20px; font-variant-numeric: tabular-nums; }
        .indicadores-pie { text-align: center; font-size: 11px; color: rgba(255,255,255,.65); margin-top: 10px; }

        .tarjeta-login {
            width: 100%; max-width: 480px;
            background: #fff; border: 1px solid var(--borde); border-radius: 18px;
            box-shadow: 0 10px 30px rgba(17,24,39,.06);
            padding: 40px;
        }
        .tarjeta-login h2 { font-size: 26px; font-weight: 800; letter-spacing: -.01em; margin-bottom: 6px; }
        .tarjeta-login .sub { color: var(--texto-suave); font-size: 14px; margin-bottom: 26px; }
        .campo { margin-bottom: 18px; }
        .campo-cab { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px; }
        label { font-size: 13px; font-weight: 600; color: #1f2937; }
        .campo-cab a { font-size: 12.5px; color: var(--navy-700); text-decoration: none; }
        .campo-cab a:hover { text-decoration: underline; }
        .input-wrap { position: relative; }
        input[type=email], input[type=password], input[type=text] {
            width: 100%; padding: 13px 14px; font-size: 14.5px; font-family: inherit;
            border: 1px solid var(--borde); border-radius: 10px; background: #f9fafb;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .input-wrap input { padding-right: 46px; }
        input:focus { outline: none; border-color: var(--navy-700); background: #fff; box-shadow: 0 0 0 3px rgba(29,70,117,.12); }
        .ver-clave {
            position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
            border: 0; background: transparent; color: var(--texto-suave);
            width: 36px; height: 36px; border-radius: 8px; cursor: pointer; font-size: 17px;
        }
        .ver-clave:hover { background: #eef2f7; color: var(--texto); }
        .ver-clave:focus-visible { outline: 2px solid var(--navy-700); }

        .alerta {
            display: flex; gap: 10px; align-items: flex-start;
            background: var(--error-bg); color: var(--error-tx); border: 1px solid var(--error-bd);
            border-radius: 10px; padding: 12px 14px; font-size: 13.5px; margin-bottom: 18px;
        }
        .btn-ingresar {
            width: 100%; margin-top: 6px; padding: 14px;
            background: var(--navy-800); color: #fff; border: 0; border-radius: 10px;
            font-size: 15px; font-weight: 700; font-family: inherit; cursor: pointer;
            box-shadow: 0 8px 18px rgba(22,54,92,.25);
            transition: background .15s, transform .1s;
        }
        .btn-ingresar:hover { background: var(--navy-900); }
        .btn-ingresar:active { transform: translateY(1px); }
        .btn-ingresar[disabled] { opacity: .75; cursor: wait; }
        .legal { text-align: center; font-size: 12px; color: #9ca3af; margin-top: 22px; line-height: 1.5; }
        .ayuda { display: none; margin-top: 14px; font-size: 13px; color: #374151; background: #f1f5f9; border-radius: 10px; padding: 12px 14px; }
        .ayuda.visible { display: block; }

        .red-colegios { width: 100%; max-width: 480px; text-align: center; }
        .red-colegios h3 {
            font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
            color: #9ca3af; margin-bottom: 14px;
        }
        .escudos { list-style: none; display: grid; grid-template-columns: repeat(8, 1fr); gap: 6px; }
        .escudos li { display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .escudos img {
            height: 38px; width: auto; max-width: 46px; object-fit: contain;
            filter: grayscale(35%); opacity: .9; transition: filter .2s, opacity .2s, transform .2s;
        }
        .escudos li:hover img { filter: none; opacity: 1; transform: translateY(-2px); }
        .escudos span { font-size: 9.5px; line-height: 1.2; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .02em; }

        /* ---------- Responsive ---------- */
        @media (max-width: 960px) {
            body { grid-template-columns: 1fr; }
            .panel-info { padding: 28px 24px; }
            .contenido { padding: 24px 0 8px; }
            .contenido h1 { font-size: 26px; }
            .modulos, .lema, .copy { display: none; }
            .panel-acceso { padding: 24px 16px 40px; }
        }
        @media (max-width: 480px) {
            .escudos { grid-template-columns: repeat(4, 1fr); row-gap: 14px; }
            .tarjeta-login { padding: 28px 22px; }
            .indicador .valor { font-size: 13px; }
            .indicador i { font-size: 17px; }
        }
    </style>
</head>
<body>

    <!-- ===== Panel izquierdo: qué es el sistema ===== -->
    <aside class="panel-info">
        <div class="marca">
            <div class="marca-icono"><img src="imagenes/logo_seduc.png" alt=""></div>
            <div>
                <strong>SEDUC Chile</strong>
                <span>Panel Central</span>
            </div>
        </div>

        <div class="contenido">
            <div class="acento"></div>
            <h1>Todo el soporte de SEDUC, en un solo lugar.</h1>
            <p class="desc">
                Plataforma institucional que centraliza las solicitudes de todos los
                colegios de la red: crea, asigna y sigue cada ticket hasta su cierre,
                con comunicación directa entre usuarios y técnicos, y accesos según
                el perfil de cada persona.
            </p>

            <div class="modulos">
                <div class="modulo"><i class="bi bi-ticket-perforated"></i><div>Gestión de tickets<small>Creación, asignación, adjuntos y seguimiento</small></div></div>
                <div class="modulo"><i class="bi bi-chat-dots"></i><div>Mensajería<small>Conversación con técnicos y colaboradores</small></div></div>
                <div class="modulo"><i class="bi bi-buildings"></i><div>Colegios y departamentos<small>Toda la red en una misma plataforma</small></div></div>
                <div class="modulo"><i class="bi bi-shield-lock"></i><div>Perfiles y permisos<small>Cada usuario ve solo lo que le corresponde</small></div></div>
                <div class="modulo"><i class="bi bi-bar-chart-line"></i><div>Estadísticas<small>Estado de las solicitudes y carga de atención</small></div></div>
                <div class="modulo"><i class="bi bi-grid-1x2"></i><div>Contenedores<small>Accesos directos a tus sistemas</small></div></div>
            </div>

            <p class="lema">SEDUC SpA · <b>"Educar para servir."</b></p>
        </div>

        <p class="copy">&copy; <?= date('Y') ?> SEDUC SpA · Panel Central</p>
    </aside>

    <!-- ===== Panel derecho: indicadores + acceso ===== -->
    <main class="panel-acceso">

        <section class="indicadores" aria-label="Indicadores económicos">
            <div class="indicadores-grid">
                <div class="indicador"><i class="bi bi-cash-coin"></i><div class="nombre">UF</div><div class="valor" id="ind-uf">—</div></div>
                <div class="indicador"><i class="bi bi-currency-dollar"></i><div class="nombre">Dólar</div><div class="valor" id="ind-dolar">—</div></div>
                <div class="indicador"><i class="bi bi-currency-euro"></i><div class="nombre">Euro</div><div class="valor" id="ind-euro">—</div></div>
                <div class="indicador"><i class="bi bi-graph-up-arrow"></i><div class="nombre">IPC</div><div class="valor" id="ind-ipc">—</div></div>
            </div>
            <div class="indicadores-pie" id="ind-pie">Cargando indicadores…</div>
        </section>

        <section class="tarjeta-login">
            <h2>Iniciar sesión</h2>
            <p class="sub">Acceso restringido a personal autorizado.</p>

            <?php if ($error !== ''): ?>
                <div class="alerta" role="alert"><i class="bi bi-exclamation-circle"></i><span><?= htmlspecialchars($error) ?></span></div>
            <?php endif; ?>

            <form method="POST" action="" id="form-login" novalidate>
                <div class="campo">
                    <div class="campo-cab"><label for="email">Correo institucional</label></div>
                    <input type="email" id="email" name="email" placeholder="usuario@seduc.cl"
                           autocomplete="username" required autofocus value="<?= $email_valor ?>">
                </div>
                <div class="campo">
                    <div class="campo-cab">
                        <label for="password">Contraseña</label>
                        <a href="#" id="link-olvido">¿Olvidaste tu clave?</a>
                    </div>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password" placeholder="••••••••"
                               autocomplete="current-password" required>
                        <button type="button" class="ver-clave" id="ver-clave" aria-label="Mostrar contraseña">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn-ingresar" id="btn-ingresar">Ingresar al panel</button>
            </form>

            <div class="ayuda" id="ayuda-olvido">
                <i class="bi bi-info-circle"></i>
                Para restablecer tu contraseña, contacta al administrador del sistema
                o a la mesa de ayuda TI de SEDUC.
            </div>

            <p class="legal">Al continuar aceptas los términos de uso y la política de privacidad institucional.</p>
        </section>
        <section class="red-colegios" aria-label="Colegios de la red SEDUC">
            <h3>Colegios de la red SEDUC</h3>
            <ul class="escudos">
                <li><img src="assets/images/escudo/9.png" alt="Escudo Los Andes" loading="lazy"><span>Los Andes</span></li>
                <li><img src="assets/images/escudo/8.png" alt="Escudo Tabancura" loading="lazy"><span>Tabancura</span></li>
                <li><img src="assets/images/escudo/11.png" alt="Escudo Huelén" loading="lazy"><span>Huelén</span></li>
                <li><img src="assets/images/escudo/1.png" alt="Escudo Cordillera" loading="lazy"><span>Cordillera</span></li>
                <li><img src="assets/images/escudo/10.png" alt="Escudo Los Alerces" loading="lazy"><span>Los Alerces</span></li>
                <li><img src="assets/images/escudo/13.png" alt="Escudo Huinganal" loading="lazy"><span>Huinganal</span></li>
                <li><img src="assets/images/escudo/12.png" alt="Escudo Cantagallo" loading="lazy"><span>Cantagallo</span></li>
                <li><img src="assets/images/escudo/17.jpg" alt="Escudo Valle Alegre" loading="lazy"><span>Valle Alegre</span></li>
            </ul>
        </section>
    </main>

    <script>
    (function () {
        // Mostrar / ocultar contraseña
        var btn = document.getElementById('ver-clave');
        var pass = document.getElementById('password');
        btn.addEventListener('click', function () {
            var oculto = pass.type === 'password';
            pass.type = oculto ? 'text' : 'password';
            btn.innerHTML = oculto ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
            btn.setAttribute('aria-label', oculto ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });

        // ¿Olvidaste tu clave?
        document.getElementById('link-olvido').addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('ayuda-olvido').classList.toggle('visible');
        });

        // Evitar doble envío
        document.getElementById('form-login').addEventListener('submit', function () {
            var b = document.getElementById('btn-ingresar');
            b.disabled = true; b.textContent = 'Ingresando…';
        });

        // Indicadores económicos
        var clp = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', minimumFractionDigits: 2, maximumFractionDigits: 2 });
        function poner(id, v, fmt) { if (v !== null && v !== undefined) document.getElementById(id).textContent = fmt(v); }
        fetch('ajax/indicadores.php', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) {
                var pie = document.getElementById('ind-pie');
                if (!d || d.uf === null) { pie.textContent = 'Indicadores no disponibles por ahora'; return; }
                poner('ind-uf', d.uf, clp.format);
                poner('ind-dolar', d.dolar, clp.format);
                poner('ind-euro', d.euro, clp.format);
                poner('ind-ipc', d.ipc, function (v) { return v.toLocaleString('es-CL') + ' %'; });
                var f = d.fecha ? d.fecha.split('-').reverse().join('-') : '';
                pie.textContent = 'Valores al ' + f + ' · Fuente: Banco Central (mindicador.cl)';
            })
            .catch(function () { document.getElementById('ind-pie').textContent = 'Indicadores no disponibles por ahora'; });
    })();
    </script>
</body>
</html>
