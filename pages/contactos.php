<?php

require_once __DIR__ . '/../validar_sesion.php';

$pagina_titulo = 'Contactos';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">
    <h2><i class="fa-solid fa-address-book"></i> Contactos</h2>
    <button class="btn btn-primary" onclick="openOC('Nuevo contacto', formContacto())">
        <i class="fa-solid fa-plus"></i> Nuevo
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div id="tabla-contactos"><p>Cargando...</p></div>
    </div>
</div>

<script>
async function cargarContactos() {
    const res  = await fetch('../api/contactos.php?accion=listar');
    const data = await res.json();

    if (!data.success) {
        document.getElementById('tabla-contactos').innerHTML = '<p class="text-danger">Error al cargar contactos.</p>';
        return;
    }

    if (!data.data.length) {
        document.getElementById('tabla-contactos').innerHTML = '<p>No hay contactos registrados.</p>';
        return;
    }

    let html = `<table class="tabla"><thead><tr>
        <th>#</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Acciones</th>
    </tr></thead><tbody>`;

    data.data.forEach(c => {
        html += `<tr>
            <td>${c.id}</td>
            <td>${escHtml(c.nombre)}</td>
            <td>${escHtml(c.correo ?? '')}</td>
            <td>${escHtml(c.telefono ?? '')}</td>
            <td>
                <button class="btn btn-sm btn-outline" onclick="editarContacto(${c.id})"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-danger"  onclick="eliminarContacto(${c.id})"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>`;
    });

    html += '</tbody></table>';
    document.getElementById('tabla-contactos').innerHTML = html;
}

function formContacto(c = {}) {
    return `<form onsubmit="guardarContacto(event, ${c.id || 0})">
        <input type="hidden" name="id" value="${c.id || ''}">
        <div class="field"><label>Nombre</label><input type="text" name="nombre" value="${escHtml(c.nombre || '')}" required></div>
        <div class="field"><label>Correo</label><input type="email" name="correo" value="${escHtml(c.correo || '')}"></div>
        <div class="field"><label>Teléfono</label><input type="text" name="telefono" value="${escHtml(c.telefono || '')}"></div>
        <div class="field"><label>Cargo</label><input type="text" name="cargo" value="${escHtml(c.cargo || '')}"></div>
        <button type="submit" class="btn btn-primary btn-block">Guardar</button>
    </form>`;
}

async function guardarContacto(e, id) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('accion', id ? 'actualizar' : 'crear');
    const res  = await fetch('../api/contactos.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Guardado' : 'Error', data.mensaje);
    if (data.success) { closeOC(); cargarContactos(); }
}

async function editarContacto(id) {
    const res  = await fetch(`../api/contactos.php?accion=obtener&id=${id}`);
    const data = await res.json();
    if (data.success) openOC('Editar contacto', formContacto(data.data));
}

async function eliminarContacto(id) {
    if (!confirm('¿Eliminar este contacto?')) return;
    const fd = new FormData();
    fd.append('accion', 'eliminar');
    fd.append('id', id);
    const res  = await fetch('../api/contactos.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Eliminado' : 'Error', data.mensaje);
    if (data.success) cargarContactos();
}

cargarContactos();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
