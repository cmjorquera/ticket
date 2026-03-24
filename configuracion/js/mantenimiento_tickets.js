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
        <div class="ticket-admin-summary-card">
            <h5>${escapeHtmlPermisos(etiqueta)} #${escapeHtmlPermisos(ticket.id_ticket)}</h5>
            <div class="ticket-admin-summary-meta">
                <span><strong>Asunto:</strong> ${escapeHtmlPermisos(ticket.asunto || 'Sin asunto')}</span>
                <span><strong>Usuario:</strong> ${escapeHtmlPermisos(ticket.usuario || 'Sin usuario')}</span>
                <span><strong>Tecnico:</strong> ${escapeHtmlPermisos(ticket.tecnico || 'Sin tecnico')}</span>
                <span><strong>Estado:</strong> ${escapeHtmlPermisos(ticket.estado || 'Sin estado')}</span>
                <span><strong>Categoria:</strong> ${escapeHtmlPermisos(ticket.categoria || 'Sin categoria')}</span>
                <span><strong>Prioridad:</strong> ${escapeHtmlPermisos(ticket.prioridad || 'Sin prioridad')}</span>
                <span><strong>Fecha:</strong> ${escapeHtmlPermisos(ticket.fecha || 'Sin fecha')}</span>
            </div>
        </div>
    `;
}

function construirImpactoTicketMantenimiento(items) {
    if (!Array.isArray(items) || items.length === 0) {
        return '<div class="ticket-admin-alert is-warning">No se encontraron relaciones para este ticket.</div>';
    }

    return `
        <div class="ticket-admin-impact-list">
            ${items.map((item) => `
                <div class="ticket-admin-impact-item">
                    <div>
                        <strong>${escapeHtmlPermisos(item.tabla)}</strong>
                        <span>${escapeHtmlPermisos(item.detalle || '')}</span>
                    </div>
                    <div class="ticket-admin-impact-badge ${item.existe ? '' : 'is-missing'}">
                        ${item.existe ? escapeHtmlPermisos(item.total ?? 0) : 'No existe'}
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function construirAlertasMantenimiento(alertas) {
    const alertasValidas = Array.isArray(alertas) ? alertas.filter(Boolean) : [];
    if (alertasValidas.length === 0) {
        return '<div class="ticket-admin-alert is-info">No se detectaron alertas adicionales para esta previsualizacion.</div>';
    }

    return `
        <div class="ticket-admin-alert is-warning">
            ${alertasValidas.map((alerta) => `<div>${escapeHtmlPermisos(alerta)}</div>`).join('')}
        </div>
    `;
}

function renderPreviewFusion(data) {
    const contenedor = document.getElementById('ticketMergePreview');
    if (!contenedor) {
        return;
    }

    contenedor.classList.remove('is-hidden');
    contenedor.innerHTML = `
        <div class="ticket-admin-preview-title">Previsualizacion de fusion</div>
        <div class="ticket-admin-summary-grid">
            ${construirResumenTicketMantenimiento(data?.tickets?.principal, 'Ticket principal')}
            ${construirResumenTicketMantenimiento(data?.tickets?.secundario, 'Ticket secundario')}
        </div>
        <div class="ticket-admin-summary-grid mt-3">
            <div class="ticket-admin-summary-card">
                <h5>Impacto ticket principal</h5>
                ${construirImpactoTicketMantenimiento(data?.impacto?.principal)}
            </div>
            <div class="ticket-admin-summary-card">
                <h5>Impacto ticket secundario</h5>
                ${construirImpactoTicketMantenimiento(data?.impacto?.secundario)}
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

    contenedor.classList.remove('is-hidden');
    contenedor.innerHTML = `
        <div class="ticket-admin-preview-title">Previsualizacion de eliminacion</div>
        <div class="ticket-admin-summary-grid">
            ${construirResumenTicketMantenimiento(data?.tickets?.objetivo, 'Ticket objetivo')}
            <div class="ticket-admin-summary-card">
                <h5>Impacto del ticket</h5>
                ${construirImpactoTicketMantenimiento(data?.impacto?.objetivo)}
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

    contenedor.classList.remove('is-hidden');
    contenedor.innerHTML = `<div class="ticket-admin-alert is-warning">${escapeHtmlPermisos(mensaje)}</div>`;
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
