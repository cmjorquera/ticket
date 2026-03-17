(function () {
    const modulo = {
        calendar: null,
        modalEvento: null,
        modalDetalle: null,
        form: null,

        initDashboard() {
            this.cache();
            this.bindBaseActions();
            this.initCalendar();
        },

        initCalendarPage() {
            this.cache();
            this.bindBaseActions();
            this.initCalendar({ initialView: 'timeGridWeek' });
        },

        cache() {
            this.form = document.getElementById('form-evento');
            this.modalEvento = document.getElementById('modalEvento') ? bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEvento')) : null;
            this.modalDetalle = document.getElementById('modalDetalleEvento') ? bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleEvento')) : null;
            this.estadoWrap = document.getElementById('eventos-estado-wrap');
        },

        bindBaseActions() {
            document.querySelectorAll('[data-eventos-action="nuevo"]').forEach((button) => {
                button.addEventListener('click', () => this.openCreateModal());
            });

            if (this.form) {
                this.form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    this.saveEvento();
                });
            }

            const btnEliminar = document.getElementById('btn-eliminar-evento');
            if (btnEliminar) {
                btnEliminar.addEventListener('click', () => this.confirmDelete());
            }
        },

        initCalendar(options = {}) {
            const calendarEl = document.getElementById('eventos-calendar');
            if (!calendarEl || typeof FullCalendar === 'undefined') {
                return;
            }

            this.calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                firstDay: 1,
                height: 'auto',
                initialView: options.initialView || 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Día',
                    list: 'Lista'
                },
                selectable: true,
                navLinks: true,
                events: EventosConfig.urls.eventos,
                dateClick: (info) => this.openCreateModal(info.dateStr),
                eventClick: (info) => this.loadEvento(info.event.id),
                eventDidMount: (info) => this.decorateEvent(info),
            });

            this.calendar.render();
        },

        decorateEvent(info) {
            const props = info.event.extendedProps || {};
            const items = [
                props.ubicacion || 'Sin ubicación',
                props.responsable || 'Sin responsable',
                props.cantidad_personas ? `${props.cantidad_personas} personas` : null,
                props.con_audio ? 'Audio' : null,
                props.musica_ambiental ? 'Música' : null,
                props.solo_presentacion ? 'Proyector' : null
            ].filter(Boolean);
            info.el.setAttribute('title', items.join(' · '));
        },

        openCreateModal(dateStr) {
            if (!this.form || !this.modalEvento) {
                return;
            }

            this.form.reset();
            this.form.querySelector('[name="id"]').value = '';
            this.form.querySelector('[name="estado"]').value = 'programado';
            document.getElementById('modalEventoLabel').textContent = 'Nuevo evento';
            document.getElementById('btn-guardar-evento').textContent = 'Guardar evento';
            document.getElementById('btn-eliminar-evento').classList.add('d-none');
            this.toggleEstadoField(false);

            if (dateStr) {
                this.form.querySelector('[name="fecha_inicio"]').value = dateStr;
                this.form.querySelector('[name="fecha_fin"]').value = dateStr;
            }

            this.updateColorByType();
            this.modalEvento.show();
        },

        async loadEvento(id) {
            const response = await fetch(`${EventosConfig.urls.editar}?id=${encodeURIComponent(id)}`);
            const data = await response.json();

            if (!data.ok) {
                this.toast('error', data.message || 'No se pudo cargar el evento.');
                return;
            }

            this.fillForm(data.evento);
            this.renderDetail(data.evento);
            if (this.modalEvento) {
                this.modalEvento.show();
            }
        },

        fillForm(evento) {
            document.getElementById('modalEventoLabel').textContent = 'Editar evento';
            document.getElementById('btn-guardar-evento').textContent = 'Guardar cambios';
            document.getElementById('btn-eliminar-evento').classList.remove('d-none');
            this.toggleEstadoField(true);

            const fields = ['id', 'titulo', 'descripcion', 'fecha_inicio', 'fecha_fin', 'hora_inicio', 'hora_fin', 'cantidad_personas', 'responsable_id', 'ubicacion', 'tipo_evento', 'estado', 'observaciones_logisticas', 'color_evento'];
            fields.forEach((field) => {
                const input = this.form.querySelector(`[name="${field}"]`);
                if (input) {
                    input.value = evento[field] || '';
                }
            });

            this.form.querySelector('[name="con_audio"]').checked = Number(evento.con_audio) === 1;
            this.form.querySelector('[name="musica_ambiental"]').checked = Number(evento.musica_ambiental) === 1;
            this.form.querySelector('[name="solo_presentacion"]').checked = Number(evento.solo_presentacion) === 1;
        },

        renderDetail(evento) {
            const body = document.getElementById('detalle-evento-body');
            if (!body) {
                return;
            }

            const requerimientos = [];
            if (Number(evento.con_audio) === 1) requerimientos.push('Audio');
            if (Number(evento.musica_ambiental) === 1) requerimientos.push('Música ambiental');
            if (Number(evento.solo_presentacion) === 1) requerimientos.push('Solo presentación');

            body.innerHTML = `
                <div class="eventos-detail-grid">
                    <div><span>Título</span><strong>${evento.titulo || ''}</strong></div>
                    <div><span>Responsable</span><strong>${evento.responsable_nombre || ''}</strong></div>
                    <div><span>Inicio</span><strong>${evento.fecha_inicio || ''} ${String(evento.hora_inicio || '').slice(0, 5)}</strong></div>
                    <div><span>Término</span><strong>${evento.fecha_fin || ''} ${String(evento.hora_fin || '').slice(0, 5)}</strong></div>
                    <div><span>Ubicación</span><strong>${evento.ubicacion || 'Sin ubicación'}</strong></div>
                    <div><span>Estado</span><strong>${evento.estado || ''}</strong></div>
                    <div><span>Tipo</span><strong>${evento.tipo_evento || ''}</strong></div>
                    <div><span>Participantes</span><strong>${evento.cantidad_personas || 0}</strong></div>
                </div>
                <div class="eventos-detail-block">
                    <span>Descripción</span>
                    <p>${evento.descripcion || 'Sin descripción'}</p>
                </div>
                <div class="eventos-detail-block">
                    <span>Observaciones logísticas</span>
                    <p>${evento.observaciones_logisticas || 'Sin observaciones'}</p>
                </div>
                <div class="eventos-detail-block">
                    <span>Requerimientos técnicos</span>
                    <p>${requerimientos.length ? requerimientos.join(', ') : 'Sin requerimientos especiales'}</p>
                </div>
            `;
        },

        validateForm() {
            const inicio = this.form.querySelector('[name="fecha_inicio"]').value;
            const fin = this.form.querySelector('[name="fecha_fin"]').value;
            const horaInicio = this.form.querySelector('[name="hora_inicio"]').value;
            const horaFin = this.form.querySelector('[name="hora_fin"]').value;

            if (fin < inicio) {
                this.toast('warning', 'La fecha de término no puede ser menor a la de inicio.');
                return false;
            }

            if (inicio === fin && horaFin < horaInicio) {
                this.toast('warning', 'La hora de término no puede ser menor a la de inicio en el mismo día.');
                return false;
            }

            return true;
        },

        async saveEvento() {
            if (!this.validateForm()) {
                return;
            }

            const formData = new FormData(this.form);
            const endpoint = formData.get('id') ? EventosConfig.urls.editar : EventosConfig.urls.guardar;
            const response = await fetch(endpoint, { method: 'POST', body: formData });
            const raw = await response.text();
            let data = null;

            try {
                data = raw ? JSON.parse(raw) : null;
            } catch (error) {
                this.toast('error', 'El servidor devolvió una respuesta no válida al guardar el evento.');
                return;
            }

            if (!response.ok && !data) {
                this.toast('error', `Error ${response.status} al guardar el evento.`);
                return;
            }

            if (!data.ok) {
                this.toast('error', data.message || 'No fue posible guardar el evento.');
                return;
            }

            this.modalEvento.hide();
            this.toast('success', data.message || 'Evento guardado correctamente.');
            this.refreshCalendar();
        },

        async confirmDelete() {
            const id = this.form.querySelector('[name="id"]').value;
            if (!id) {
                return;
            }

            const result = await Swal.fire({
                icon: 'warning',
                title: 'Eliminar evento',
                text: 'Se realizará una eliminación lógica del evento.',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (!result.isConfirmed) {
                return;
            }

            const formData = new FormData();
            formData.append('id', id);

            const response = await fetch(EventosConfig.urls.eliminar, { method: 'POST', body: formData });
            const raw = await response.text();
            let data = null;

            try {
                data = raw ? JSON.parse(raw) : null;
            } catch (error) {
                this.toast('error', 'El servidor devolvió una respuesta no válida al eliminar el evento.');
                return;
            }

            if (!data.ok) {
                this.toast('error', data.message || 'No fue posible eliminar el evento.');
                return;
            }

            this.modalEvento.hide();
            this.toast('success', data.message || 'Evento eliminado correctamente.');
            this.refreshCalendar();
        },

        refreshCalendar() {
            if (this.calendar) {
                this.calendar.refetchEvents();
            }
            window.setTimeout(() => window.location.reload(), 500);
        },

        updateColorByType() {
            const tipoInput = this.form.querySelector('[name="tipo_evento"]');
            const colorInput = this.form.querySelector('[name="color_evento"]');
            if (!tipoInput || !colorInput) {
                return;
            }

            const color = (window.eventosTipoColores || {})[tipoInput.value];
            if (color) {
                colorInput.value = color;
            }
        },

        toggleEstadoField(show) {
            if (!this.estadoWrap) {
                return;
            }

            this.estadoWrap.classList.toggle('d-none', !show);
        },

        toast(icon, text) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon,
                title: text,
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        }
    };

    document.addEventListener('change', function (event) {
        if (event.target && event.target.name === 'tipo_evento' && window.EventosModulo) {
            window.EventosModulo.updateColorByType();
        }
    });

    window.EventosModulo = modulo;
})();
