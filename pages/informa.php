<?php

require_once __DIR__ . '/../validar_sesion.php';

$pagina_titulo = 'Informa';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">
    <h2><i class="fa-solid fa-newspaper"></i> Informa</h2>
    <button class="btn btn-primary" onclick="openOC('Nueva publicación', formInforma())">
        <i class="fa-solid fa-plus"></i> Nueva
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div id="tabla-informa"><p>Cargando...</p></div>
    </div>
</div>

<script>
async function cargarInforma() {
    const res  = await fetch('../api/informa.php?accion=listar');
    const data = await res.json();

    if (!data.success) {
        document.getElementById('tabla-informa').innerHTML = '<p class="text-danger">Error al cargar publicaciones.</p>';
        return;
    }

    if (!data.data.length) {
        document.getElementById('tabla-informa').innerHTML = '<p>No hay publicaciones registradas.</p>';
        return;
    }

    let html = `<table class="tabla"><thead><tr>
        <th>#</th><th>Título</th><th>Fecha</th><th>Activo</th><th>Acciones</th>
    </tr></thead><tbody>`;

    data.data.forEach(n => {
        html += `<tr>
            <td>${n.id}</td>
            <td>${escHtml(n.titulo)}</td>
            <td>${escHtml(n.fecha ?? '')}</td>
            <td>${n.activo == 1 ? '<span class="badge badge-green">Sí</span>' : '<span class="badge badge-red">No</span>'}</td>
            <td>
                <button class="btn btn-sm btn-outline" onclick="editarInforma(${n.id})"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-danger"  onclick="eliminarInforma(${n.id})"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>`;
    });

    html += '</tbody></table>';
    document.getElementById('tabla-informa').innerHTML = html;
}

function formInforma(n = {}) {
    return `<form onsubmit="guardarInforma(event, ${n.id || 0})">
        <input type="hidden" name="id" value="${n.id || ''}">
        <div class="field"><label>Título</label><input type="text" name="titulo" value="${escHtml(n.titulo || '')}" required></div>
        <div class="field"><label>Contenido</label><textarea name="contenido" rows="5">${escHtml(n.contenido || '')}</textarea></div>
        <div class="field"><label>Fecha publicación</label><input type="date" name="fecha" value="${n.fecha || ''}"></div>
        <button type="submit" class="btn btn-primary btn-block">Guardar</button>
    </form>`;
}

async function guardarInforma(e, id) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('accion', id ? 'actualizar' : 'crear');
    const res  = await fetch('../api/informa.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Guardado' : 'Error', data.mensaje);
    if (data.success) { closeOC(); cargarInforma(); }
}

async function editarInforma(id) {
    const res  = await fetch(`../api/informa.php?accion=obtener&id=${id}`);
    const data = await res.json();
    if (data.success) openOC('Editar publicación', formInforma(data.data));
}

async function eliminarInforma(id) {
    if (!confirm('¿Eliminar esta publicación?')) return;
    const fd = new FormData();
    fd.append('accion', 'eliminar');
    fd.append('id', id);
    const res  = await fetch('../api/informa.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Eliminado' : 'Error', data.mensaje);
    if (data.success) cargarInforma();
}

cargarInforma();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
