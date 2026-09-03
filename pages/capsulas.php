<?php

require_once __DIR__ . '/../validar_sesion.php';

$pagina_titulo = 'Cápsulas';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">
    <h2><i class="fa-solid fa-capsules"></i> Cápsulas</h2>
    <button class="btn btn-primary" onclick="openOC('Nueva cápsula', formCapsula())">
        <i class="fa-solid fa-plus"></i> Nueva
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div id="tabla-capsulas"><p>Cargando...</p></div>
    </div>
</div>

<script>
async function cargarCapsulas() {
    const res  = await fetch('../api/capsulas.php?accion=listar');
    const data = await res.json();

    if (!data.success) {
        document.getElementById('tabla-capsulas').innerHTML = '<p class="text-danger">Error al cargar cápsulas.</p>';
        return;
    }

    if (!data.data.length) {
        document.getElementById('tabla-capsulas').innerHTML = '<p>No hay cápsulas registradas.</p>';
        return;
    }

    let html = `<table class="tabla"><thead><tr>
        <th>#</th><th>Título</th><th>Categoría</th><th>Activo</th><th>Acciones</th>
    </tr></thead><tbody>`;

    data.data.forEach(c => {
        html += `<tr>
            <td>${c.id}</td>
            <td>${escHtml(c.titulo)}</td>
            <td>${escHtml(c.categoria ?? '')}</td>
            <td>${c.activo == 1 ? '<span class="badge badge-green">Sí</span>' : '<span class="badge badge-red">No</span>'}</td>
            <td>
                <button class="btn btn-sm btn-outline" onclick="editarCapsula(${c.id})"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-danger"  onclick="eliminarCapsula(${c.id})"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>`;
    });

    html += '</tbody></table>';
    document.getElementById('tabla-capsulas').innerHTML = html;
}

function formCapsula(c = {}) {
    return `<form onsubmit="guardarCapsula(event, ${c.id || 0})">
        <input type="hidden" name="id" value="${c.id || ''}">
        <div class="field"><label>Título</label><input type="text" name="titulo" value="${escHtml(c.titulo || '')}" required></div>
        <div class="field"><label>Categoría</label><input type="text" name="categoria" value="${escHtml(c.categoria || '')}"></div>
        <div class="field"><label>Descripción</label><textarea name="descripcion" rows="4">${escHtml(c.descripcion || '')}</textarea></div>
        <div class="field"><label>URL de video</label><input type="text" name="url_video" value="${escHtml(c.url_video || '')}"></div>
        <button type="submit" class="btn btn-primary btn-block">Guardar</button>
    </form>`;
}

async function guardarCapsula(e, id) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('accion', id ? 'actualizar' : 'crear');
    const res  = await fetch('../api/capsulas.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Guardado' : 'Error', data.mensaje);
    if (data.success) { closeOC(); cargarCapsulas(); }
}

async function editarCapsula(id) {
    const res  = await fetch(`../api/capsulas.php?accion=obtener&id=${id}`);
    const data = await res.json();
    if (data.success) openOC('Editar cápsula', formCapsula(data.data));
}

async function eliminarCapsula(id) {
    if (!confirm('¿Eliminar esta cápsula?')) return;
    const fd = new FormData();
    fd.append('accion', 'eliminar');
    fd.append('id', id);
    const res  = await fetch('../api/capsulas.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Eliminado' : 'Error', data.mensaje);
    if (data.success) cargarCapsulas();
}

cargarCapsulas();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
