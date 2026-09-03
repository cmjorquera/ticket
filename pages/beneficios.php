<?php

require_once __DIR__ . '/../validar_sesion.php';

$pagina_titulo = 'Beneficios';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">
    <h2><i class="fa-solid fa-gift"></i> Beneficios</h2>
    <button class="btn btn-primary" onclick="openOC('Nuevo beneficio', formBeneficio())">
        <i class="fa-solid fa-plus"></i> Nuevo
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div id="tabla-beneficios"><p>Cargando...</p></div>
    </div>
</div>

<script>
async function cargarBeneficios() {
    const res  = await fetch('../api/beneficios.php?accion=listar');
    const data = await res.json();

    if (!data.success) {
        document.getElementById('tabla-beneficios').innerHTML = '<p class="text-danger">Error al cargar beneficios.</p>';
        return;
    }

    if (!data.data.length) {
        document.getElementById('tabla-beneficios').innerHTML = '<p>No hay beneficios registrados.</p>';
        return;
    }

    let html = `<table class="tabla"><thead><tr>
        <th>#</th><th>Nombre</th><th>Descripción</th><th>Activo</th><th>Acciones</th>
    </tr></thead><tbody>`;

    data.data.forEach(b => {
        html += `<tr>
            <td>${b.id}</td>
            <td>${escHtml(b.nombre)}</td>
            <td>${escHtml(b.descripcion ?? '')}</td>
            <td>${b.activo == 1 ? '<span class="badge badge-green">Sí</span>' : '<span class="badge badge-red">No</span>'}</td>
            <td>
                <button class="btn btn-sm btn-outline" onclick="editarBeneficio(${b.id})"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-danger"  onclick="eliminarBeneficio(${b.id})"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>`;
    });

    html += '</tbody></table>';
    document.getElementById('tabla-beneficios').innerHTML = html;
}

function formBeneficio(b = {}) {
    return `<form onsubmit="guardarBeneficio(event, ${b.id || 0})">
        <input type="hidden" name="id" value="${b.id || ''}">
        <div class="field"><label>Nombre</label><input type="text" name="nombre" value="${escHtml(b.nombre || '')}" required></div>
        <div class="field"><label>Descripción</label><textarea name="descripcion">${escHtml(b.descripcion || '')}</textarea></div>
        <div class="field"><label>URL/Enlace</label><input type="text" name="url" value="${escHtml(b.url || '')}"></div>
        <button type="submit" class="btn btn-primary btn-block">Guardar</button>
    </form>`;
}

async function guardarBeneficio(e, id) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('accion', id ? 'actualizar' : 'crear');
    const res  = await fetch('../api/beneficios.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Guardado' : 'Error', data.mensaje);
    if (data.success) { closeOC(); cargarBeneficios(); }
}

async function editarBeneficio(id) {
    const res  = await fetch(`../api/beneficios.php?accion=obtener&id=${id}`);
    const data = await res.json();
    if (data.success) openOC('Editar beneficio', formBeneficio(data.data));
}

async function eliminarBeneficio(id) {
    if (!confirm('¿Eliminar este beneficio?')) return;
    const fd = new FormData();
    fd.append('accion', 'eliminar');
    fd.append('id', id);
    const res  = await fetch('../api/beneficios.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.success ? 'success' : 'error', data.success ? 'Eliminado' : 'Error', data.mensaje);
    if (data.success) cargarBeneficios();
}

cargarBeneficios();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
