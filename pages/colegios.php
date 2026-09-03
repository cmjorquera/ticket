<?php

require_once __DIR__ . '/../validar_sesion.php';

$pagina_titulo = 'Colegios';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">
    <h2><i class="fa-solid fa-school"></i> Colegios</h2>
    <button class="btn btn-primary" onclick="openOC('Nuevo colegio', formColegio())">
        <i class="fa-solid fa-plus"></i> Nuevo
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div id="tabla-colegios"><p>Cargando...</p></div>
    </div>
</div>

<script>
async function cargarColegios() {
    showSkeleton('tabla-colegios', 5, 5);
    const data = await apiCall('../api/colegios.php?accion=listar', 'GET');

    if (!data.success) {
        document.getElementById('tabla-colegios').innerHTML = '<p class="text-danger">Error al cargar colegios.</p>';
        return;
    }

    crearTabla('tabla-colegios', [
        { key: 'id', label: '#' },
        { key: 'nombre', label: 'Nombre' },
        { key: 'direccion', label: 'Dirección', render: c => escHtml(c.direccion ?? '') },
        { key: 'telefono', label: 'Teléfono', render: c => escHtml(c.telefono ?? '') },
        { key: 'activo', label: 'Activo', render: c => c.activo == 1 ? '<span class="badge badge-green">Sí</span>' : '<span class="badge badge-red">No</span>' },
        { key: 'id', label: 'Acciones', render: c => `<button class="btn btn-sm btn-outline" onclick="editarColegio(${c.id})"><i class="fa-solid fa-pen"></i></button> <button class="btn btn-sm btn-danger" onclick="eliminarColegio(${c.id})"><i class="fa-solid fa-trash"></i></button>` },
    ], data.data, { titulo: 'Colegios', csv: 'colegios', emptyText: 'No hay colegios registrados.' });
}

function formColegio(c = {}) {
    return `<form onsubmit="guardarColegio(event, ${c.id || 0})">
        <input type="hidden" name="id" value="${c.id || ''}">
        <div class="field"><label>Nombre</label><input type="text" name="nombre" value="${escHtml(c.nombre || '')}" required></div>
        <div class="field"><label>Dirección</label><input type="text" name="direccion" value="${escHtml(c.direccion || '')}"></div>
        <div class="field"><label>Teléfono</label><input type="text" name="telefono" value="${escHtml(c.telefono || '')}"></div>
        <button type="submit" class="btn btn-primary btn-block">Guardar</button>
    </form>`;
}

async function guardarColegio(e, id) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('accion', id ? 'actualizar' : 'crear');
    const res  = await fetch('../api/colegios.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Guardado' : 'Error', data.mensaje);
    if (data.success) { closeOC(); cargarColegios(); }
}

async function editarColegio(id) {
    const res  = await fetch(`../api/colegios.php?accion=obtener&id=${id}`);
    const data = await res.json();
    if (data.success) openOC('Editar colegio', formColegio(data.data));
}

async function eliminarColegio(id) {
    if (!confirm('¿Eliminar este colegio?')) return;
    const fd = new FormData();
    fd.append('accion', 'eliminar');
    fd.append('id', id);
    const res  = await fetch('../api/colegios.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Eliminado' : 'Error', data.mensaje);
    if (data.success) cargarColegios();
}

document.addEventListener('DOMContentLoaded', cargarColegios);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
