function limpiarIdTicket(valor) {
    return String(valor || '').replace(/[^\d]/g, '');
}

function escapeHtmlPermisos(valor) {
    return String(valor ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function construirResumenTicketMantenimiento(ticket, etiqueta) {
    if (!ticket) {
        return '';
    }

    return `
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">${escapeHtmlPermisos(etiqueta)} #${escapeHtmlPermisos(ticket.id_ticket)}</h6>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <div><strong>Asunto:</strong> ${escapeHtmlPermisos(ticket.asunto || 'Sin asunto')}</div>
                <div><strong>Usuario:</strong> ${escapeHtmlPermisos(ticket.usuario || 'Sin usuario')}</div>
                <div><strong>Tecnico:</strong> ${escapeHtmlPermisos(ticket.tecnico || 'Sin tecnico')}</div>
                <div><strong>Estado:</strong> ${escapeHtmlPermisos(ticket.estado || 'Sin estado')}</div>
                <div><strong>Categoria:</strong> ${escapeHtmlPermisos(ticket.categoria || 'Sin categoria')}</div>
                <div><strong>Prioridad:</strong> ${escapeHtmlPermisos(ticket.prioridad || 'Sin prioridad')}</div>
                <div><strong>Fecha:</strong> ${escapeHtmlPermisos(ticket.fecha || 'Sin fecha')}</div>
            </div>
        </div>
    `;
}

function construirImpactoTicketMantenimiento(items) {
    if (!Array.isArray(items) || items.length === 0) {
        return '<div class="alert alert-warning mb-0">No se encontraron relaciones para este ticket.</div>';
    }

    return `
        <div class="list-group">
            ${items.map((item) => `
                <div class="list-group-item d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <strong>${escapeHtmlPermisos(item.tabla)}</strong>
                        <div>${escapeHtmlPermisos(item.detalle || '')}</div>
                    </div>
                    <span class="badge ${item.existe ? 'bg-secondary' : 'bg-warning text-dark'}">
                        ${item.existe ? escapeHtmlPermisos(item.total ?? 0) : 'No existe'}
                    </span>
                </div>
            `).join('')}
        </div>
    `;
}

function construirAlertasMantenimiento(alertas) {
    const alertasValidas = Array.isArray(alertas) ? alertas.filter(Boolean) : [];
    if (alertasValidas.length === 0) {
        return '<div class="alert alert-info">No se detectaron alertas adicionales para esta previsualizacion.</div>';
    }

    return `
        <div class="alert alert-warning">
            ${alertasValidas.map((alerta) => `<div>${escapeHtmlPermisos(alerta)}</div>`).join('')}
        </div>
    `;
}

function renderPreviewFusion(data) {
    const contenedor = document.getElementById('ticketMergePreview');
    if (!contenedor) {
        return;
    }

    contenedor.innerHTML = `
        <h6 class="font-weight-bold text-primary mb-3">Previsualizacion de fusion</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                ${construirResumenTicketMantenimiento(data?.tickets?.principal, 'Ticket principal')}
            </div>
            <div class="col-md-6 mb-3">
                ${construirResumenTicketMantenimiento(data?.tickets?.secundario, 'Ticket secundario')}
            </div>
        </div>
        <div class="row mt-1">
            <div class="col-md-6 mb-3">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Impacto ticket principal</h6>
                    </div>
                    <div class="card-body">
                        ${construirImpactoTicketMantenimiento(data?.impacto?.principal)}
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Impacto ticket secundario</h6>
                    </div>
                    <div class="card-body">
                        ${construirImpactoTicketMantenimiento(data?.impacto?.secundario)}
                    </div>
                </div>
            </div>
        </div>
        ${construirAlertasMantenimiento(data?.alertas)}
    `;
}

function renderPreviewEliminar(data) {
    const contenedor = document.getElementById('ticketDeletePreview');
    if (!contenedor) {
        return;
    }

    contenedor.innerHTML = `
        <h6 class="font-weight-bold text-primary mb-3">Previsualizacion de eliminacion</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                ${construirResumenTicketMantenimiento(data?.tickets?.objetivo, 'Ticket objetivo')}
            </div>
            <div class="col-md-6 mb-3">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Impacto del ticket</h6>
                    </div>
                    <div class="card-body">
                        ${construirImpactoTicketMantenimiento(data?.impacto?.objetivo)}
                    </div>
                </div>
            </div>
        </div>
        ${construirAlertasMantenimiento(data?.alertas)}
    `;
}

function mostrarErrorPreviewMantenimiento(contenedorId, mensaje) {
    const contenedor = document.getElementById(contenedorId);
    if (!contenedor) {
        return;
    }

    contenedor.innerHTML = `<div class="alert alert-warning mb-0">${escapeHtmlPermisos(mensaje)}</div>`;
}

function solicitarPreviewMantenimiento(payload, onSuccess, contenedorErrorId) {
    $.ajax({
        url: 'modelos/rescatar/ticket_mantenimiento_preview.php',
        type: 'POST',
        dataType: 'json',
        data: payload,
        success: function(response) {
            if (!response || !response.success) {
                mostrarErrorPreviewMantenimiento(contenedorErrorId, response?.message || 'No se pudo generar la previsualizacion.');
                return;
            }

            onSuccess(response);
        },
        error: function() {
            mostrarErrorPreviewMantenimiento(contenedorErrorId, 'Ocurrio un error al consultar el impacto del ticket.');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const btnPreviewFusion = document.getElementById('btnPreviewFusion');
    const btnImpactoFusion = document.getElementById('btnImpactoFusion');
    const btnPreviewEliminar = document.getElementById('btnPreviewEliminar');
    const btnGuardarBorradorMantenimiento = document.getElementById('btnGuardarBorradorMantenimiento');

    function ejecutarPreviewFusion() {
        const ticketPrincipal = limpiarIdTicket(document.getElementById('ticketMergePrincipal')?.value);
        const ticketSecundario = limpiarIdTicket(document.getElementById('ticketMergeSecundario')?.value);
        const motivo = document.getElementById('ticketMergeMotivo')?.value || '';

        if (!ticketPrincipal || !ticketSecundario) {
            mostrarErrorPreviewMantenimiento('ticketMergePreview', 'Debes indicar ticket principal y ticket secundario.');
            return;
        }

        solicitarPreviewMantenimiento({
            accion: 'fusionar',
            ticket_principal: ticketPrincipal,
            ticket_secundario: ticketSecundario,
            motivo: motivo
        }, renderPreviewFusion, 'ticketMergePreview');
    }

    function ejecutarPreviewEliminar() {
        const ticketEliminar = limpiarIdTicket(document.getElementById('ticketDeleteId')?.value);
        const tipoEliminacion = document.getElementById('ticketDeleteTipo')?.value || 'completa';
        const motivo = document.getElementById('ticketDeleteMotivo')?.value || '';

        if (!ticketEliminar) {
            mostrarErrorPreviewMantenimiento('ticketDeletePreview', 'Debes indicar el ticket que deseas revisar.');
            return;
        }

        solicitarPreviewMantenimiento({
            accion: 'eliminar',
            ticket_eliminar: ticketEliminar,
            tipo_eliminacion: tipoEliminacion,
            motivo: motivo
        }, renderPreviewEliminar, 'ticketDeletePreview');
    }

    if (btnPreviewFusion) {
        btnPreviewFusion.addEventListener('click', ejecutarPreviewFusion);
    }

    if (btnImpactoFusion) {
        btnImpactoFusion.addEventListener('click', ejecutarPreviewFusion);
    }

    if (btnPreviewEliminar) {
        btnPreviewEliminar.addEventListener('click', ejecutarPreviewEliminar);
    }

    if (btnGuardarBorradorMantenimiento) {
        btnGuardarBorradorMantenimiento.addEventListener('click', function() {
            Swal.fire({
                icon: 'info',
                title: 'Borrador visual',
                text: 'Todavia no se guarda en base de datos. Primero estamos preparando la previsualizacion segura.',
                customClass: {
                    popup: 'cuerpo_modal_guardar'
                }
            });
        });
    }

});
