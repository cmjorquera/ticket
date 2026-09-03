<?php

require_once __DIR__ . '/../validar_sesion.php';

$pagina_titulo = 'Módulos';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">
    <h2><i class="fa-solid fa-cubes"></i> Módulos</h2>
    <button class="btn btn-primary" onclick="openOC('Nuevo módulo', formNuevoModulo())">
        <i class="fa-solid fa-plus"></i> Nuevo
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div id="tabla-modulos">Cargando...</div>
    </div>
</div>

<script>
async function cargarModulos() {
    showSkeleton('tabla-modulos', 5, 6);
    const data = await apiCall('../api/modulos.php?accion=listar', 'GET');

    if (!data.success) {
        document.getElementById('tabla-modulos').innerHTML = '<p class="text-danger">Error al cargar módulos.</p>';
        return;
    }

    crearTabla('tabla-modulos', [
        { key: 'id', label: '#' },
        { key: 'nombre', label: 'Nombre' },
        { key: 'ruta', label: 'Ruta' },
        { key: 'orden', label: 'Orden' },
        { key: 'activo', label: 'Activo', render: m => m.activo == 1 ? '<span class="badge badge-green">Sí</span>' : '<span class="badge badge-red">No</span>' },
        { key: 'id', label: 'Acciones', render: m => `<button class="btn btn-sm btn-outline" onclick="editarModulo(${m.id})"><i class="fa-solid fa-pen"></i></button> <button class="btn btn-sm btn-danger" onclick="eliminarModulo(${m.id})"><i class="fa-solid fa-trash"></i></button>` },
    ], data.data, { titulo: 'Módulos', csv: 'modulos', emptyText: 'No hay módulos registrados.' });
}

function formNuevoModulo(m = {}) {
    return `<form id="form-modulo" onsubmit="guardarModulo(event, ${m.id || 0})">
        <input type="hidden" name="id" value="${m.id || ''}">
        <div class="field"><label>Nombre</label><input type="text" name="nombre" value="${escHtml(m.nombre || '')}" required></div>
        <div class="field"><label>Descripción</label><input type="text" name="descripcion" value="${escHtml(m.descripcion || '')}"></div>
        <div class="field"><label>Ruta</label><input type="text" name="ruta" value="${escHtml(m.ruta || '')}"></div>
        <div class="field"><label>Ícono (Font Awesome)</label><input type="text" name="icono" value="${escHtml(m.icono || '')}"></div>
        <div class="field"><label>Orden</label><input type="number" name="orden" value="${m.orden || 0}"></div>
        <button type="submit" class="btn btn-primary btn-block">Guardar</button>
    </form>`;
}

async function guardarModulo(e, id) {
    e.preventDefault();
    const form   = e.target;
    const data   = new FormData(form);
    const accion = id ? 'actualizar' : 'crear';
    data.append('accion', accion);

    const res  = await fetch('../api/modulos.php', { method: 'POST', body: data });
    const resp = await res.json();

    showToast(resp.success ? 'success' : 'error', resp.success ? 'Éxito' : 'Error', resp.mensaje);
    if (resp.success) { closeOC(); cargarModulos(); }
}

async function editarModulo(id) {
    const res  = await fetch(`../api/modulos.php?accion=obtener&id=${id}`);
    const data = await res.json();
    if (data.success) openOC('Editar módulo', formNuevoModulo(data.data));
}

async function eliminarModulo(id) {
    if (!confirm('¿Eliminar este módulo?')) return;
    const fd = new FormData();
    fd.append('accion', 'eliminar');
    fd.append('id', id);
    const res  = await fetch('../api/modulos.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Eliminado' : 'Error', data.mensaje);
    if (data.success) cargarModulos();
}

document.addEventListener('DOMContentLoaded', cargarModulos);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
