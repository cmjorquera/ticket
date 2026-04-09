function escapeHtmlEliminar(valor) {
    return String(valor ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function obtenerRutaEliminarTicket() {
    return '../modelos/guardar/mantenimiento_ticket_eliminar.php';
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
        return '<div class="alert alert-warning mb-0">No se encontraron relaciones para este ticket.</div>';
    }

    return `
        <div class="list-group">
            ${items.map((item) => `
                <div class="list-group-item d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <strong>${escapeHtmlEliminar(item.tabla)}</strong>
                        <div>${escapeHtmlEliminar(item.detalle || '')}</div>
                    </div>
                    <span class="badge bg-secondary">
                        ${item.existe ? escapeHtmlEliminar(item.total ?? 0) : 'No existe'}
                    </span>
                </div>
            `).join('')}
        </div>
    `;
}

document.addEventListener('DOMContentLoaded', function () {
    const tabla = document.getElementById('tablaMantenimientoEliminar');
    const btnRevisar = document.getElementById('btnRevisarEliminarTicket');
    const btnBorrador = document.getElementById('btnGuardarBorradorEliminar');
    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminarTicket');
    const preview = document.getElementById('ticketDeletePreview');
    const ticketInput = document.getElementById('ticketDeleteId');
    const modalElement = document.getElementById('modalEliminarTicketMantenimiento');

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
                if (btnConfirmarEliminar) {
                    btnConfirmarEliminar.disabled = true;
                }
                preview.innerHTML = '<div class="alert alert-warning mb-0">Debes indicar el ticket que deseas revisar.</div>';
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
                        if (btnConfirmarEliminar) {
                            btnConfirmarEliminar.disabled = true;
                        }
                        preview.innerHTML = `<div class="alert alert-warning mb-0">${escapeHtmlEliminar(response?.message || 'No se pudo revisar el ticket.')}</div>`;
                        return;
                    }

                    const ticket = response?.tickets?.objetivo || {};
                    const alertas = Array.isArray(response?.alertas) ? response.alertas.filter(Boolean) : [];

                    preview.innerHTML = `
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card shadow h-100">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Ticket seleccionado #${escapeHtmlEliminar(ticket.id_ticket || '')}</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="mb-0">
                                    <li><strong>Asunto:</strong> ${escapeHtmlEliminar(ticket.asunto || 'Sin asunto')}</li>
                                    <li><strong>Usuario:</strong> ${escapeHtmlEliminar(ticket.usuario || 'Sin usuario')}</li>
                                    <li><strong>Estado:</strong> ${escapeHtmlEliminar(ticket.estado || 'Sin estado')}</li>
                                    <li><strong>Categoria:</strong> ${escapeHtmlEliminar(ticket.categoria || 'Sin categoria')}</li>
                                    <li><strong>Fecha:</strong> ${escapeHtmlEliminar(ticket.fecha || 'Sin fecha')}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card shadow h-100">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Impacto relacionado</h6>
                                    </div>
                                    <div class="card-body">
                                        ${renderizarImpactoEliminar(response?.impacto?.objetivo)}
                                    </div>
                                </div>
                            </div>
                        </div>
                        ${alertas.length ? `<div class="alert alert-warning">${alertas.map((item) => `<div>${escapeHtmlEliminar(item)}</div>`).join('')}</div>` : ''}
                    `;

                    if (btnConfirmarEliminar) {
                        btnConfirmarEliminar.disabled = false;
                    }
                },
                error: function () {
                    if (btnConfirmarEliminar) {
                        btnConfirmarEliminar.disabled = true;
                    }
                    preview.innerHTML = '<div class="alert alert-warning mb-0">Ocurrio un error al revisar este ticket.</div>';
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

    if (ticketInput) {
        ticketInput.addEventListener('input', function () {
            if (btnConfirmarEliminar) {
                btnConfirmarEliminar.disabled = true;
            }
        });
    }

    if (modalElement) {
        modalElement.addEventListener('hidden.bs.modal', function () {
            if (btnConfirmarEliminar) {
                btnConfirmarEliminar.disabled = true;
            }
        });
    }

    if (btnConfirmarEliminar) {
        btnConfirmarEliminar.addEventListener('click', function () {
            const ticketId = (ticketInput?.value || '').replace(/[^\d]/g, '');
            const tipo = document.getElementById('ticketDeleteTipo')?.value || 'completa';
            const motivo = document.getElementById('ticketDeleteMotivo')?.value || '';
            if (!ticketId) {
                return;
            }

            btnConfirmarEliminar.disabled = true;

            $.ajax({
                url: obtenerRutaEliminarTicket(),
                type: 'POST',
                dataType: 'json',
                data: {
                    accion: 'solicitar_codigo',
                    ticket_id: ticketId,
                    tipo_eliminacion: tipo,
                    motivo: motivo
                },
                success: function (response) {
                    if (!response || !response.success) {
                        btnConfirmarEliminar.disabled = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo enviar el codigo',
                            text: response?.message || 'No fue posible enviar el codigo de verificacion.',
                            customClass: { popup: 'cuerpo_modal_guardar' }
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Confirmar eliminacion',
                        text: 'Se envio un codigo al correo del administrador. Ingresalo para continuar.',
                        input: 'text',
                        inputLabel: `Codigo para ticket #${ticketId}`,
                        inputPlaceholder: 'Ingresa el codigo recibido',
                        confirmButtonText: 'Validar codigo',
                        showCancelButton: true,
                        cancelButtonText: 'Cancelar',
                        customClass: { popup: 'cuerpo_modal_guardar' },
                        preConfirm: (codigo) => {
                            const codigoLimpio = String(codigo || '').trim();
                            if (!codigoLimpio) {
                                Swal.showValidationMessage('Debes ingresar el codigo.');
                                return false;
                            }

                            return $.ajax({
                                url: obtenerRutaEliminarTicket(),
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    accion: 'confirmar_eliminacion',
                                    ticket_id: ticketId,
                                    codigo: codigoLimpio
                                }
                            }).then((confirmacion) => {
                                if (!confirmacion || !confirmacion.success) {
                                    throw new Error(confirmacion?.message || 'No se pudo confirmar la eliminacion.');
                                }
                                return confirmacion;
                            }).catch((error) => {
                                Swal.showValidationMessage(error?.message || 'No se pudo validar el codigo.');
                            });
                        }
                    }).then((result) => {
                        btnConfirmarEliminar.disabled = false;

                        if (!result.isConfirmed || !result.value?.success) {
                            return;
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Ticket actualizado',
                            text: `El ticket #${ticketId} cambio su estado correctamente.`,
                            customClass: { popup: 'cuerpo_modal_guardar' }
                        }).then(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEliminarTicketMantenimiento'));
                            modal?.hide();
                            window.location.reload();
                        });
                    });
                },
                error: function () {
                    btnConfirmarEliminar.disabled = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No fue posible iniciar la validacion por correo.',
                        customClass: { popup: 'cuerpo_modal_guardar' }
                    });
                }
            });
        });
    }
});
