<?php

require_once __DIR__ . '/../validar_sesion.php';

$pagina_titulo = 'Mi Perfil';

require_once __DIR__ . '/../clases/Usuario.php';

$usuarioModel = new Usuario('sistema_panel_central');
$usuario      = $usuarioModel->getById((int) Session::get('usuario_id'));

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">
    <h2><i class="fa-solid fa-user-circle"></i> Mi Perfil</h2>
</div>

<div class="card" style="max-width:600px">
    <div class="card-header"><h3>Datos personales</h3></div>
    <div class="card-body">
        <form id="form-perfil">
            <div class="field">
                <label>Nombre</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label>Apellido</label>
                <input type="text" name="apellido" value="<?= htmlspecialchars($usuario['apellido'] ?? '') ?>">
            </div>
            <div class="field">
                <label>Correo electrónico</label>
                <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>" required>
            </div>
            <hr>
            <div class="field">
                <label>Nueva contraseña <small>(dejar vacío para no cambiar)</small></label>
                <input type="password" name="password" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </form>
    </div>
</div>

<script>
document.getElementById('form-perfil').addEventListener('submit', async function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('accion', 'actualizar');
    fd.append('id', <?= (int) Session::get('usuario_id') ?>);

    const res  = await fetch('../api/usuarios.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Guardado' : 'Error', data.mensaje);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
