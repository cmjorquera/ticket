function escapeHtmlEliminar(valor) {
    return String(valor ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function abrirEliminarTicket(idTicket) {
    const modalElement = document.getElementById('modalEliminarTicketMantenimiento');
    const ticketInput = document.getElementById('ticketDeleteId');
    const preview = document.getElementById('ticketDeletePreview');

    if (!modalElement || !ticketInput || !preview) {
        return;
    }

    ticketInput.value = `#${idTicket}`;
    preview.classList.remove('is-visible');
    preview.innerHTML = '';

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
}

function renderizarImpactoEliminar(items) {
    if (!Array.isArray(items) || items.length === 0) {
        return '<div class="ticket-maint-alert">No se encontraron relaciones para este ticket.</div>';
    }

    return `
        <div class="ticket-maint-impact-list">
            ${items.map((item) => `
                <div class="ticket-maint-impact-item">
                    <div>
                        <strong>${escapeHtmlEliminar(item.tabla)}</strong>
                        <span>${escapeHtmlEliminar(item.detalle || '')}</span>
                    </div>
                    <div class="ticket-maint-impact-badge">
                        ${item.existe ? escapeHtmlEliminar(item.total ?? 0) : 'No existe'}
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

document.addEventListener('DOMContentLoaded', function () {
    const tabla = document.getElementById('tablaMantenimientoEliminar');
    const btnRevisar = document.getElementById('btnRevisarEliminarTicket');
    const btnBorrador = document.getElementById('btnGuardarBorradorEliminar');
    const preview = document.getElementById('ticketDeletePreview');

    if (tabla && window.jQuery && $.fn.DataTable) {
        $('#tablaMantenimientoEliminar').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 10,
            order: [[0, 'desc']]
        });
    }

    if (btnRevisar) {
        btnRevisar.addEventListener('click', function () {
            const ticketId = (document.getElementById('ticketDeleteId')?.value || '').replace(/[^\d]/g, '');
            const tipo = document.getElementById('ticketDeleteTipo')?.value || 'completa';
            const motivo = document.getElementById('ticketDeleteMotivo')?.value || '';

            if (!ticketId) {
                preview.classList.add('is-visible');
                preview.innerHTML = '<div class="ticket-maint-alert">Debes indicar el ticket que deseas revisar.</div>';
                return;
            }

            $.ajax({
                url: '../modelos/rescatar/ticket_mantenimiento_preview.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    accion: 'eliminar',
                    ticket_eliminar: ticketId,
                    tipo_eliminacion: tipo,
                    motivo: motivo
                },
                success: function (response) {
                    if (!response || !response.success) {
                        preview.classList.add('is-visible');
                        preview.innerHTML = `<div class="ticket-maint-alert">${escapeHtmlEliminar(response?.message || 'No se pudo revisar el ticket.')}</div>`;
                        return;
                    }

                    const ticket = response?.tickets?.objetivo || {};
                    const alertas = Array.isArray(response?.alertas) ? response.alertas.filter(Boolean) : [];

                    preview.classList.add('is-visible');
                    preview.innerHTML = `
                        <div class="ticket-maint-preview-grid">
                            <div class="ticket-maint-summary-card">
                                <h6>Ticket seleccionado #${escapeHtmlEliminar(ticket.id_ticket || '')}</h6>
                                <ul>
                                    <li><strong>Asunto:</strong> ${escapeHtmlEliminar(ticket.asunto || 'Sin asunto')}</li>
                                    <li><strong>Usuario:</strong> ${escapeHtmlEliminar(ticket.usuario || 'Sin usuario')}</li>
                                    <li><strong>Estado:</strong> ${escapeHtmlEliminar(ticket.estado || 'Sin estado')}</li>
                                    <li><strong>Categoria:</strong> ${escapeHtmlEliminar(ticket.categoria || 'Sin categoria')}</li>
                                    <li><strong>Fecha:</strong> ${escapeHtmlEliminar(ticket.fecha || 'Sin fecha')}</li>
                                </ul>
                            </div>
                            <div class="ticket-maint-summary-card">
                                <h6>Impacto relacionado</h6>
                                ${renderizarImpactoEliminar(response?.impacto?.objetivo)}
                            </div>
                        </div>
                        ${alertas.length ? `<div class="ticket-maint-alert">${alertas.map((item) => `<div>${escapeHtmlEliminar(item)}</div>`).join('')}</div>` : ''}
                    `;
                },
                error: function () {
                    preview.classList.add('is-visible');
                    preview.innerHTML = '<div class="ticket-maint-alert">Ocurrio un error al revisar este ticket.</div>';
                }
            });
        });
    }

    if (btnBorrador) {
        btnBorrador.addEventListener('click', function () {
            Swal.fire({
                icon: 'info',
                title: 'Borrador visual',
                text: 'Todavia no se ejecuta la eliminacion real. Primero estamos dejando listo el flujo de revision.',
                customClass: { popup: 'cuerpo_modal_guardar' }
            });
        });
    }
});
