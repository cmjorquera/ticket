<?php

require_once __DIR__ . '/../validar_sesion.php';

$pagina_titulo = 'Eventos';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">
    <h2><i class="fa-solid fa-calendar"></i> Eventos</h2>
    <button class="btn btn-primary" onclick="openOC('Nuevo evento', formEvento())">
        <i class="fa-solid fa-plus"></i> Nuevo
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div id="tabla-eventos"><p>Cargando...</p></div>
    </div>
</div>

<script>
async function cargarEventos() {
    const res  = await fetch('../api/eventos.php?accion=listar');
    const data = await res.json();

    if (!data.success) {
        document.getElementById('tabla-eventos').innerHTML = '<p class="text-danger">Error al cargar eventos.</p>';
        return;
    }

    if (!data.data.length) {
        document.getElementById('tabla-eventos').innerHTML = '<p>No hay eventos registrados.</p>';
        return;
    }

    let html = `<table class="tabla"><thead><tr>
        <th>#</th><th>Título</th><th>Fecha</th><th>Activo</th><th>Acciones</th>
    </tr></thead><tbody>`;

    data.data.forEach(ev => {
        html += `<tr>
            <td>${ev.id}</td>
            <td>${escHtml(ev.titulo)}</td>
            <td>${escHtml(ev.fecha ?? '')}</td>
            <td>${ev.activo == 1 ? '<span class="badge badge-green">Sí</span>' : '<span class="badge badge-red">No</span>'}</td>
            <td>
                <button class="btn btn-sm btn-outline" onclick="editarEvento(${ev.id})"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-danger"  onclick="eliminarEvento(${ev.id})"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>`;
    });

    html += '</tbody></table>';
    document.getElementById('tabla-eventos').innerHTML = html;
}

function formEvento(ev = {}) {
    return `<form onsubmit="guardarEvento(event, ${ev.id || 0})">
        <input type="hidden" name="id" value="${ev.id || ''}">
        <div class="field"><label>Título</label><input type="text" name="titulo" value="${escHtml(ev.titulo || '')}" required></div>
        <div class="field"><label>Descripción</label><textarea name="descripcion">${escHtml(ev.descripcion || '')}</textarea></div>
        <div class="field"><label>Fecha</label><input type="date" name="fecha" value="${ev.fecha || ''}"></div>
        <button type="submit" class="btn btn-primary btn-block">Guardar</button>
    </form>`;
}

async function guardarEvento(e, id) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('accion', id ? 'actualizar' : 'crear');
    const res  = await fetch('../api/eventos.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Guardado' : 'Error', data.mensaje);
    if (data.success) { closeOC(); cargarEventos(); }
}

async function editarEvento(id) {
    const res  = await fetch(`../api/eventos.php?accion=obtener&id=${id}`);
    const data = await res.json();
    if (data.success) openOC('Editar evento', formEvento(data.data));
}

async function eliminarEvento(id) {
    if (!confirm('¿Eliminar este evento?')) return;
    const fd = new FormData();
    fd.append('accion', 'eliminar');
    fd.append('id', id);
    const res  = await fetch('../api/eventos.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Eliminado' : 'Error', data.mensaje);
    if (data.success) cargarEventos();
}

cargarEventos();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
