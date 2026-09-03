<?php

require_once __DIR__ . '/../validar_sesion.php';

$pagina_titulo = 'Configuración';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">
    <h2><i class="fa-solid fa-gear"></i> Configuración</h2>
</div>

<div class="tabs" id="tabs-config">
    <div class="tabs-header">
        <button class="tab-btn active" onclick="initTabs(this, 'tab-general')">General</button>
        <button class="tab-btn" onclick="initTabs(this, 'tab-sistema')">Sistema</button>
        <button class="tab-btn" onclick="initTabs(this, 'tab-seguridad')">Seguridad</button>
    </div>

    <div id="tab-general" class="tab-content active">
        <div class="card mt-3">
            <div class="card-header"><h3>Configuración general</h3></div>
            <div class="card-body">
                <form id="form-general" onsubmit="guardarConfiguracion(event, 'general')">
                    <div class="field">
                        <label>Nombre del sistema</label>
                        <input type="text" name="nombre_sistema" value="Sistema Panel Central">
                    </div>
                    <div class="field">
                        <label>Correo de contacto</label>
                        <input type="email" name="correo_contacto" placeholder="contacto@sistema.cl">
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>

    <div id="tab-sistema" class="tab-content">
        <div class="card mt-3">
            <div class="card-header"><h3>Información del sistema</h3></div>
            <div class="card-body">
                <table class="tabla">
                    <tbody>
                        <tr><td>PHP</td><td><?= PHP_VERSION ?></td></tr>
                        <tr><td>Servidor</td><td><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') ?></td></tr>
                        <tr><td>Usuario sesión</td><td><?= htmlspecialchars(Session::get('usuario_correo', '—')) ?></td></tr>
                        <tr><td>Rol</td><td><?= htmlspecialchars(Session::get('usuario_rol', '—')) ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="tab-seguridad" class="tab-content">
        <div class="card mt-3">
            <div class="card-header"><h3>Cambiar contraseña</h3></div>
            <div class="card-body">
                <form id="form-password" onsubmit="cambiarPassword(event)" style="max-width:400px">
                    <div class="field">
                        <label>Contraseña actual</label>
                        <input type="password" name="password_actual" required autocomplete="current-password">
                    </div>
                    <div class="field">
                        <label>Nueva contraseña</label>
                        <input type="password" name="password_nueva" required autocomplete="new-password">
                    </div>
                    <div class="field">
                        <label>Confirmar nueva contraseña</label>
                        <input type="password" name="password_confirmar" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
async function guardarConfiguracion(e, seccion) {
    e.preventDefault();
    showToast('success', 'Guardado', 'Configuración actualizada');
}

async function cambiarPassword(e) {
    e.preventDefault();
    const form   = e.target;
    const nueva  = form.password_nueva.value;
    const conf   = form.password_confirmar.value;

    if (nueva !== conf) {
        showToast('error', 'Error', 'Las contraseñas no coinciden');
        return;
    }

    if (nueva.length < 6) {
        showToast('error', 'Error', 'La contraseña debe tener al menos 6 caracteres');
        return;
    }

    const fd = new FormData();
    fd.append('accion', 'actualizar');
    fd.append('id', <?= (int) Session::get('usuario_id') ?>);
    fd.append('password', nueva);

    const res  = await fetch('../api/usuarios.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Listo' : 'Error', data.mensaje);
    if (data.success) form.reset();
}

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        const target = this.getAttribute('onclick').match(/'([^']+)'\)/)[1];
        document.getElementById(target).classList.add('active');
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
