<?php

require_once __DIR__ . '/../validar_sesion.php';

$pagina_titulo = 'Contenedores';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">
    <h2><i class="fa-solid fa-box"></i> Contenedores</h2>
    <button class="btn btn-primary" onclick="openOC('Nuevo contenedor', formContenedor())">
        <i class="fa-solid fa-plus"></i> Nuevo
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div id="tabla-contenedores"><p>Cargando...</p></div>
    </div>
</div>

<script>
async function cargarContenedores() {
    const res  = await fetch('../api/contenedores.php?accion=listar');
    const data = await res.json();

    if (!data.success) {
        document.getElementById('tabla-contenedores').innerHTML = '<p class="text-danger">Error al cargar contenedores.</p>';
        return;
    }

    if (!data.data.length) {
        document.getElementById('tabla-contenedores').innerHTML = '<p>No hay contenedores registrados.</p>';
        return;
    }

    let html = `<table class="tabla"><thead><tr>
        <th>#</th><th>Nombre</th><th>Tipo</th><th>Activo</th><th>Acciones</th>
    </tr></thead><tbody>`;

    data.data.forEach(c => {
        html += `<tr>
            <td>${c.id}</td>
            <td>${escHtml(c.nombre)}</td>
            <td>${escHtml(c.tipo ?? '')}</td>
            <td>${c.activo == 1 ? '<span class="badge badge-green">Sí</span>' : '<span class="badge badge-red">No</span>'}</td>
            <td>
                <button class="btn btn-sm btn-outline" onclick="editarContenedor(${c.id})"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-danger"  onclick="eliminarContenedor(${c.id})"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>`;
    });

    html += '</tbody></table>';
    document.getElementById('tabla-contenedores').innerHTML = html;
}

function formContenedor(c = {}) {
    return `<form onsubmit="guardarContenedor(event, ${c.id || 0})">
        <input type="hidden" name="id" value="${c.id || ''}">
        <div class="field"><label>Nombre</label><input type="text" name="nombre" value="${escHtml(c.nombre || '')}" required></div>
        <div class="field"><label>Tipo</label><input type="text" name="tipo" value="${escHtml(c.tipo || '')}"></div>
        <div class="field"><label>Descripción</label><textarea name="descripcion">${escHtml(c.descripcion || '')}</textarea></div>
        <button type="submit" class="btn btn-primary btn-block">Guardar</button>
    </form>`;
}

async function guardarContenedor(e, id) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('accion', id ? 'actualizar' : 'crear');
    const res  = await fetch('../api/contenedores.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Guardado' : 'Error', data.mensaje);
    if (data.success) { closeOC(); cargarContenedores(); }
}

async function editarContenedor(id) {
    const res  = await fetch(`../api/contenedores.php?accion=obtener&id=${id}`);
    const data = await res.json();
    if (data.success) openOC('Editar contenedor', formContenedor(data.data));
}

async function eliminarContenedor(id) {
    if (!confirm('¿Eliminar este contenedor?')) return;
    const fd = new FormData();
    fd.append('accion', 'eliminar');
    fd.append('id', id);
    const res  = await fetch('../api/contenedores.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Eliminado' : 'Error', data.mensaje);
    if (data.success) cargarContenedores();
}

cargarContenedores();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
