(function () {
    const configuracion = window.ADMIN_CATEGORIAS_CONFIG || {};
    const ROOT = configuracion.root || '../';
    const TECNICOS_CATEGORIA = Array.isArray(configuracion.tecnicos) ? configuracion.tecnicos : [];

    function escaparHtmlCategoria(valor) {
        return String(valor ?? '').replace(/[&<>"']/g, caracter => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[caracter]));
    }

    function alternarChip(chip, activar) {
        chip.classList.toggle('cat-chip--on', activar);
        chip.classList.toggle('cat-chip--off', !activar);
        const iconoEstado = chip.querySelector('i:first-child');
        if (iconoEstado) {
            iconoEstado.className = 'bi ' + (activar ? 'bi-check-circle-fill' : 'bi-circle');
        }
    }

    function actualizarContador(card) {
        const contador = card.querySelector('.cat-count');
        if (contador) {
            contador.textContent = card.querySelectorAll('.cat-chip--on').length;
        }
    }

    function toggleChip(chip) {
        const esOn = chip.classList.contains('cat-chip--on');
        const idCat = chip.dataset.idCategoria;
        const card = chip.closest('.ac-card');
        if (!card) return;

        if (!esOn) {
            const yaAsignado = document.querySelector(
                `.ac-card:not([data-tecnico-id="${card.dataset.tecnicoId}"]) .cat-chip--on[data-id-categoria="${idCat}"]`
            );

            if (yaAsignado) {
                const otroCard = yaAsignado.closest('.ac-card');
                const otroNombre = otroCard?.querySelector('.ac-card__name')?.textContent.trim() || 'otro tecnico';
                Swal.fire({
                    icon: 'warning',
                    title: 'Categoria ya asignada',
                    html: `<b>${chip.textContent.trim()}</b> ya esta asignada a <b>${otroNombre}</b>.<br>Quieres reasignarla a este tecnico?`,
                    showCancelButton: true,
                    confirmButtonText: 'Si, reasignar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#4e73df',
                    customClass: { popup: 'config-swal-popup' }
                }).then(result => {
                    if (!result.isConfirmed) return;
                    alternarChip(yaAsignado, false);
                    actualizarContador(otroCard);
                    alternarChip(chip, true);
                    actualizarContador(card);
                });
                return;
            }
        }

        alternarChip(chip, !esOn);
        actualizarContador(card);
    }

    function guardarCambiosCards() {
        Swal.fire({
            title: 'Guardar cambios',
            text: 'Confirmas los cambios de asignacion de categorias?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4e73df',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Si, guardar',
            cancelButtonText: 'Cancelar',
            customClass: { popup: 'config-swal-popup' }
        }).then(result => {
            if (!result.isConfirmed) return;

            const peticiones = [];

            document.querySelectorAll('.ac-card').forEach(card => {
                const idTecnico = parseInt(card.dataset.tecnicoId, 10);
                const idsCategorias = [];

                card.querySelectorAll('.cat-chip--on').forEach(chip => {
                    const idCat = parseInt(chip.dataset.idCategoria, 10);
                    if (!Number.isNaN(idCat)) idsCategorias.push(idCat);
                });

                peticiones.push(
                    fetch(ROOT + 'modelos/guardar/guardar_permisos_categoria.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_usuario: idTecnico, ids_categorias: idsCategorias })
                    }).then(r => r.json())
                );
            });

            Promise.all(peticiones)
                .then(respuestas => {
                    const conError = respuestas.some(r => !r || r.status !== 'ok');
                    if (conError) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrio un error al guardar una o mas asignaciones.',
                            customClass: { popup: 'config-swal-popup' }
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Guardado',
                            text: 'Las asignaciones se guardaron correctamente.',
                            timer: 2000,
                            showConfirmButton: false,
                            customClass: { popup: 'config-swal-popup' }
                        });
                    }
                })
                .catch(() => Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo conectar al servidor.',
                    customClass: { popup: 'config-swal-popup' }
                }));
        });
    }

    function abrirModalAdministrarCategorias() {
        const modalEl = document.getElementById('modalAdministrarCategorias');
        if (!modalEl) return;
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        $('#contenedorAdministrarCategorias').html(`
            <div class="d-flex justify-content-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        `);

        $.ajax({
            url: ROOT + 'modelos/rescatar/administrar_categorias_.php',
            type: 'GET',
            success: function (html) {
                $('#contenedorAdministrarCategorias').html(html);
            },
            error: function () {
                $('#contenedorAdministrarCategorias').html(
                    '<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-circle me-2"></i>No se pudo cargar el listado de categorias.</div>'
                );
            }
        });
    }

    function opcionesTecnicos(idTecnicoActual) {
        return [
            '<option value="">Seleccione tecnico</option>',
            ...TECNICOS_CATEGORIA.map(tec => {
                const idTecnico = parseInt(tec.id, 10);
                const selected = idTecnico === idTecnicoActual ? ' selected' : '';
                return `<option value="${idTecnico}"${selected}>${escaparHtmlCategoria(tec.nombre)}</option>`;
            })
        ].join('');
    }

    function agregarCategoria() {
        const opciones = TECNICOS_CATEGORIA.length
            ? TECNICOS_CATEGORIA.map(tec => `<option value="${parseInt(tec.id, 10)}">${escaparHtmlCategoria(tec.nombre)}</option>`).join('')
            : '<option value="">No hay tecnicos disponibles</option>';

        Swal.fire({
            title: '',
            html: `
                <div class="swal-categoria">
                    <h2 class="swal-categoria__title">Nueva categoria</h2>
                    <div class="swal-categoria__grid">
                        <div class="swal-categoria__field swal-categoria__field--full">
                            <label class="swal-categoria__label" for="nc_tecnico">Tecnico asignado</label>
                            <select id="nc_tecnico" class="swal-categoria__select">
                                <option value="">Seleccione tecnico</option>
                                ${opciones}
                            </select>
                        </div>
                        <div class="swal-categoria__field">
                            <label class="swal-categoria__label" for="nc_nombre">Nombre</label>
                            <input id="nc_nombre" class="swal-categoria__input" placeholder="Nombre de la categoria">
                        </div>
                        <div class="swal-categoria__field">
                            <label class="swal-categoria__label" for="nc_abrev">Abreviacion</label>
                            <input id="nc_abrev" class="swal-categoria__input" placeholder="Abreviacion corta">
                        </div>
                        <div class="swal-categoria__field swal-categoria__field--full">
                            <label class="swal-categoria__label" for="nc_icono">Icono Bootstrap</label>
                            <div class="swal-categoria__icon-row">
                                <span class="swal-categoria__icon-preview" id="nc_icono_preview"><i class="bi bi-tag"></i></span>
                                <input id="nc_icono" class="swal-categoria__input" placeholder="bi-tag" value="bi-tag">
                            </div>
                            <div class="swal-categoria__hint">
                                Copia el nombre del icono desde
                                <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener noreferrer">Bootstrap Icons</a>.
                            </div>
                        </div>
                    </div>
                </div>`,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0f78b5',
            cancelButtonColor: '#eef4ff',
            customClass: {
                popup: 'swal-categoria-popup',
                confirmButton: 'px-4',
                cancelButton: 'px-4 text-dark'
            },
            focusConfirm: false,
            didOpen: () => {
                const iconoInput = document.getElementById('nc_icono');
                const preview = document.getElementById('nc_icono_preview');
                iconoInput?.addEventListener('input', function () {
                    const icono = this.value.trim() || 'bi-tag';
                    preview.innerHTML = `<i class="bi ${icono}"></i>`;
                });
            },
            preConfirm: () => {
                const nombre = document.getElementById('nc_nombre').value.trim();
                const idTecnico = parseInt(document.getElementById('nc_tecnico').value, 10);
                if (!idTecnico) { Swal.showValidationMessage('Debes seleccionar un tecnico'); return false; }
                if (!nombre) { Swal.showValidationMessage('El nombre es obligatorio'); return false; }
                return {
                    id_tecnico: idTecnico,
                    nombre_categoria: nombre,
                    abreviacion: document.getElementById('nc_abrev').value.trim(),
                    icono: document.getElementById('nc_icono').value.trim() || 'bi-tag',
                };
            }
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: ROOT + 'modelos/guardar/crear_categorias.php',
                type: 'POST',
                dataType: 'json',
                data: result.value,
                success: function (resp) {
                    if (resp && resp.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Categoria creada',
                            timer: 1500,
                            showConfirmButton: false,
                            customClass: { popup: 'config-swal-popup' }
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: (resp && resp.message) ? resp.message : 'No se pudo crear la categoria.',
                            customClass: { popup: 'config-swal-popup' }
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo conectar con el servidor.',
                        customClass: { popup: 'config-swal-popup' }
                    });
                }
            });
        });
    }

    $(document).on('click', '.js-editar-categoria', function () {
        const idTecnicoActual = parseInt($(this).data('id_tecnico'), 10) || '';

        $('#editar_id_categoria').val($(this).data('id_categoria'));
        $('#editar_nombre_categoria').val($(this).data('nombre_categoria') || '');
        $('#editar_abreviacion').val($(this).data('abreviacion') || '');
        const icono = $(this).data('icono') || '';
        $('#editar_id_tecnico').html(opcionesTecnicos(idTecnicoActual));
        $('#editar_icono').val(icono);
        $('#iconoPreview').html('<i class="bi ' + (icono || 'bi-tag') + '"></i>');

        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAdministrarCategorias')).hide();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarCategoria')).show();
    });

    $('#editar_icono').on('input', function () {
        const icono = this.value.trim() || 'bi-tag';
        $('#iconoPreview').html('<i class="bi ' + icono + '"></i>');
    });

    $('#formEditarCategoria').on('submit', function (e) {
        e.preventDefault();
        if (!$('#editar_id_tecnico').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Atencion',
                text: 'Debes seleccionar un tecnico.',
                customClass: { popup: 'config-swal-popup' }
            });
            return;
        }
        const data = $(this).serialize();
        $.post(ROOT + 'modelos/editar/editar_categoria_ticket_.php', data, function (resp) {
            let ok = false;
            try { ok = (typeof resp === 'object' ? resp : JSON.parse(resp)).success; } catch (ex) {}
            if (ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Guardado',
                    timer: 1500,
                    showConfirmButton: false,
                    customClass: { popup: 'config-swal-popup' }
                }).then(() => abrirModalAdministrarCategorias());
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarCategoria')).hide();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo guardar la categoria.',
                    customClass: { popup: 'config-swal-popup' }
                });
            }
        });
    });

    $(document).on('click', '.js-toggle-estado-categoria', function () {
        const idCat = $(this).data('id_categoria');
        const estado = parseInt($(this).data('estado'), 10);
        const accion = estado === 1 ? 'desactivar' : 'activar';

        Swal.fire({
            title: accion.charAt(0).toUpperCase() + accion.slice(1) + ' categoria',
            text: 'Confirmas esta accion?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: estado === 1 ? '#e74a3b' : '#1cc88a',
            customClass: { popup: 'config-swal-popup' }
        }).then(result => {
            if (!result.isConfirmed) return;
            $.post(ROOT + 'modelos/editar/cambiar_estado_categoria_ticket.php',
                { id_categoria: idCat, estado: estado === 1 ? 0 : 1 },
                function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Listo',
                        timer: 1200,
                        showConfirmButton: false,
                        customClass: { popup: 'config-swal-popup' }
                    }).then(() => {
                        $.ajax({
                            url: ROOT + 'modelos/rescatar/administrar_categorias_.php',
                            success: html => $('#contenedorAdministrarCategorias').html(html)
                        });
                    });
                }
            );
        });
    });

    window.toggleChip = toggleChip;
    window.guardarCambiosCards = guardarCambiosCards;
    window.abrirModalAdministrarCategorias = abrirModalAdministrarCategorias;
    window.agregarCategoria = agregarCategoria;
})();
