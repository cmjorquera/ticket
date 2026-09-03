<?php

require_once __DIR__ . '/../validar_sesion.php';

$pagina_titulo = 'Permisos';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">
    <h2><i class="fa-solid fa-shield-halved"></i> Permisos</h2>
    <button class="btn btn-primary" onclick="openOC('Nuevo permiso', formPermiso())">
        <i class="fa-solid fa-plus"></i> Nuevo
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div id="tabla-permisos"><p>Cargando...</p></div>
    </div>
</div>

<script>
async function cargarPermisos() {
    const res  = await fetch('../api/permisos.php?accion=listar');
    const data = await res.json();

    if (!data.success) {
        document.getElementById('tabla-permisos').innerHTML = '<p class="text-danger">Error al cargar permisos.</p>';
        return;
    }

    if (!data.data.length) {
        document.getElementById('tabla-permisos').innerHTML = '<p>No hay permisos registrados.</p>';
        return;
    }

    let html = `<table class="tabla"><thead><tr>
        <th>#</th><th>Nombre</th><th>Módulo</th><th>Rol</th><th>Acciones</th>
    </tr></thead><tbody>`;

    data.data.forEach(p => {
        html += `<tr>
            <td>${p.id}</td>
            <td>${escHtml(p.nombre)}</td>
            <td>${escHtml(p.modulo ?? '')}</td>
            <td><span class="badge badge-blue">${escHtml(p.rol ?? '')}</span></td>
            <td>
                <button class="btn btn-sm btn-outline" onclick="editarPermiso(${p.id})"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-danger"  onclick="eliminarPermiso(${p.id})"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>`;
    });

    html += '</tbody></table>';
    document.getElementById('tabla-permisos').innerHTML = html;
}

function formPermiso(p = {}) {
    return `<form onsubmit="guardarPermiso(event, ${p.id || 0})">
        <input type="hidden" name="id" value="${p.id || ''}">
        <div class="field"><label>Nombre</label><input type="text" name="nombre" value="${escHtml(p.nombre || '')}" required></div>
        <div class="field"><label>Módulo</label><input type="text" name="modulo" value="${escHtml(p.modulo || '')}"></div>
        <div class="field"><label>Rol</label>
            <select name="rol">
                <option value="usuario" ${p.rol === 'usuario' ? 'selected' : ''}>Usuario</option>
                <option value="editor"  ${p.rol === 'editor'  ? 'selected' : ''}>Editor</option>
                <option value="admin"   ${p.rol === 'admin'   ? 'selected' : ''}>Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Guardar</button>
    </form>`;
}

async function guardarPermiso(e, id) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('accion', id ? 'actualizar' : 'crear');
    const res  = await fetch('../api/permisos.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Guardado' : 'Error', data.mensaje);
    if (data.success) { closeOC(); cargarPermisos(); }
}

async function editarPermiso(id) {
    const res  = await fetch(`../api/permisos.php?accion=obtener&id=${id}`);
    const data = await res.json();
    if (data.success) openOC('Editar permiso', formPermiso(data.data));
}

async function eliminarPermiso(id) {
    if (!confirm('¿Eliminar este permiso?')) return;
    const fd = new FormData();
    fd.append('accion', 'eliminar');
    fd.append('id', id);
    const res  = await fetch('../api/permisos.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Eliminado' : 'Error', data.mensaje);
    if (data.success) cargarPermisos();
}

cargarPermisos();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
