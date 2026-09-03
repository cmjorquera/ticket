<?php

require_once __DIR__ . '/../validar_sesion.php';

$pagina_titulo = 'Usuarios';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">
    <h2><i class="fa-solid fa-users"></i> Usuarios</h2>
    <button class="btn btn-primary" onclick="openOC('Nuevo usuario', formUsuario())">
        <i class="fa-solid fa-plus"></i> Nuevo
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div id="tabla-usuarios">Cargando...</div>
    </div>
</div>

<script>
async function cargarUsuarios() {
    showSkeleton('tabla-usuarios', 5, 6);
    const data = await apiCall('../api/usuarios.php?accion=listar', 'GET');

    if (!data.success) {
        document.getElementById('tabla-usuarios').innerHTML = '<p class="text-danger">Error al cargar usuarios.</p>';
        return;
    }

    crearTabla('tabla-usuarios', [
        { key: 'id', label: '#' },
        { key: 'nombre', label: 'Nombre', render: u => `${escHtml(u.nombre)} ${escHtml(u.apellido ?? '')}` },
        { key: 'correo', label: 'Correo' },
        { key: 'rol', label: 'Rol', render: u => `<span class="badge badge-blue">${escHtml(u.rol)}</span>` },
        { key: 'activo', label: 'Activo', render: u => u.activo == 1 ? '<span class="badge badge-green">Activo</span>' : '<span class="badge badge-red">Inactivo</span>' },
        { key: 'id', label: 'Acciones', render: u => `<button class="btn btn-sm btn-outline" onclick="editarUsuario(${u.id})"><i class="fa-solid fa-pen"></i></button> <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(${u.id})"><i class="fa-solid fa-trash"></i></button>` },
    ], data.data, { titulo: 'Usuarios', csv: 'usuarios', emptyText: 'No hay usuarios registrados.' });
}

function formUsuario(u = {}) {
    return `<form id="form-usuario" onsubmit="guardarUsuario(event, ${u.id || 0})">
        <input type="hidden" name="id" value="${u.id || ''}">
        <div class="field"><label>Nombre</label><input type="text" name="nombre" value="${escHtml(u.nombre || '')}" required></div>
        <div class="field"><label>Apellido</label><input type="text" name="apellido" value="${escHtml(u.apellido || '')}"></div>
        <div class="field"><label>Correo</label><input type="email" name="correo" value="${escHtml(u.correo || '')}" required></div>
        <div class="field"><label>Contraseña ${u.id ? '(dejar vacío para no cambiar)' : ''}</label>
            <input type="password" name="password" ${!u.id ? 'required' : ''} autocomplete="new-password"></div>
        <div class="field"><label>Rol</label>
            <select name="rol">
                <option value="usuario" ${u.rol === 'usuario' ? 'selected' : ''}>Usuario</option>
                <option value="admin"   ${u.rol === 'admin'   ? 'selected' : ''}>Admin</option>
                <option value="editor"  ${u.rol === 'editor'  ? 'selected' : ''}>Editor</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Guardar</button>
    </form>`;
}

async function guardarUsuario(e, id) {
    e.preventDefault();
    const form   = e.target;
    const data   = new FormData(form);
    const accion = id ? 'actualizar' : 'crear';
    data.append('accion', accion);

    const res  = await fetch('../api/usuarios.php', { method: 'POST', body: data });
    const resp = await res.json();

    showToast(resp.success ? 'success' : 'error', resp.success ? 'Éxito' : 'Error', resp.mensaje);
    if (resp.success) { closeOC(); cargarUsuarios(); }
}

async function editarUsuario(id) {
    const res  = await fetch(`../api/usuarios.php?accion=obtener&id=${id}`);
    const data = await res.json();
    if (data.success) openOC('Editar usuario', formUsuario(data.data));
}

async function eliminarUsuario(id) {
    if (!confirm('¿Desactivar este usuario?')) return;
    const fd = new FormData();
    fd.append('accion', 'eliminar');
    fd.append('id', id);
    const res  = await fetch('../api/usuarios.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Desactivado' : 'Error', data.mensaje);
    if (data.success) cargarUsuarios();
}

document.addEventListener('DOMContentLoaded', cargarUsuarios);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
