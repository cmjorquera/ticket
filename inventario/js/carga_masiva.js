(function () {
    'use strict';

    var dropZone      = document.getElementById('cmDropZone');
    var fileInput     = document.getElementById('cmFileInput');
    var fileNameLabel = document.getElementById('cmFileName');
    var btnCargar     = document.getElementById('btnCargarMasiva');
    var btnInsertar   = document.getElementById('btnInsertarPc');
    var btnConfirmar  = document.getElementById('btnConfirmarInsertarPc');
    var btnCancelar   = document.getElementById('btnCancelarInsertarPc');
    var btnCerrarModal = document.getElementById('btnCerrarModalInsertarPc');
    var seleccionarTodos = document.getElementById('cmSeleccionarTodos');
    var resultSection = document.getElementById('cmResultados');
    var resultTable   = document.getElementById('cmTablaResultados');
    var resultSummary = document.getElementById('cmResumenResultados');
    var panelRevision = document.getElementById('cmPanelRevision');
    var resultTitle   = document.getElementById('cmTituloResultados');
    var confirmarCantidad = document.getElementById('cmConfirmarCantidad');
    var confirmarValidas = document.getElementById('cmConfirmarValidas');
    var confirmarUbicacionesNuevas = document.getElementById('cmConfirmarUbicacionesNuevas');
    var confirmarErrores = document.getElementById('cmConfirmarErrores');
    var confirmarAdvertencias = document.getElementById('cmConfirmarAdvertencias');
    var confirmarSinUbicacion = document.getElementById('cmConfirmarSinUbicacion');
    var confirmarSinResponsable = document.getElementById('cmConfirmarSinResponsable');
    var spinnerCargar = document.getElementById('cmSpinner');
    var spinnerInsertar = document.getElementById('cmSpinnerInsertar');
    var dropIcon      = dropZone.querySelector('.cm-dropzone-icon');
    var dropText      = dropZone.querySelector('.cm-dropzone-text');
    var modalEl       = document.getElementById('modalConfirmarInsertarPc');
    var overlayCarga  = document.getElementById('cmOverlayCarga');
    var overlayMensaje = document.getElementById('cmOverlayMensaje');

    var archivoSeleccionado = null;
    var procesando          = false;
    var filasPreview        = [];
    var modalConfirmacion   = null;

    if (window.bootstrap && modalEl) {
        modalConfirmacion = bootstrap.Modal.getOrCreateInstance(modalEl);
    }

    function activarPaso(num) {
        var pasos  = document.querySelectorAll('.cm-stepper-step');
        var lineas = document.querySelectorAll('.cm-stepper-line');

        pasos.forEach(function (paso) {
            var n = parseInt(paso.getAttribute('data-step'), 10);
            paso.classList.remove('active', 'completed');
            if (n < num) { paso.classList.add('completed'); }
            if (n === num) { paso.classList.add('active'); }
        });

        lineas.forEach(function (linea) {
            var n = parseInt(linea.getAttribute('data-line'), 10);
            linea.classList.remove('active', 'completed');
            if (n < num) { linea.classList.add('completed'); }
            if (n === num) { linea.classList.add('active'); }
        });
    }

    activarPaso(1);

    function setDropzoneProcesando(activo, texto) {
        if (activo) {
            dropZone.classList.add('cm-drop-processing');
            dropIcon.className = 'bi bi-hourglass-split cm-dropzone-icon';
            dropText.textContent = texto || 'Procesando archivo...';
        } else {
            dropZone.classList.remove('cm-drop-processing');
            dropIcon.className = 'bi bi-cloud-upload cm-dropzone-icon';
            dropText.textContent = 'Arrastra el archivo aqui o haz clic para seleccionarlo';
        }
    }

    function setArchivo(file) {
        archivoSeleccionado = file;
        filasPreview = [];
        limpiarTabla();
        if (file) {
            fileNameLabel.textContent = file.name;
            fileNameLabel.classList.remove('text-muted');
            fileNameLabel.classList.add('text-dark', 'fw-semibold');
            if (btnCargar) { btnCargar.disabled = false; }
            activarPaso(3);
            enviarArchivo('preview', []);
        } else {
            fileNameLabel.textContent = 'Ningun archivo seleccionado';
            fileNameLabel.classList.remove('text-dark', 'fw-semibold');
            fileNameLabel.classList.add('text-muted');
            if (btnCargar) { btnCargar.disabled = true; }
            activarPaso(1);
        }
    }

    function limpiarTabla() {
        resultSection.classList.add('d-none');
        resultTable.querySelector('tbody').innerHTML = '';
        resultSummary.innerHTML = '';
        if (panelRevision) {
            panelRevision.innerHTML = '<div class="cm-review-empty">Adjunta un Excel para ver el resumen de revisión.</div>';
        }
        btnInsertar.classList.add('d-none');
        btnInsertar.disabled = true;
        seleccionarTodos.checked = false;
        seleccionarTodos.indeterminate = false;
    }

    function enviarArchivo(accion, filasSeleccionadas) {
        if (!archivoSeleccionado || procesando) return;

        procesando = true;
        if (btnCargar) { btnCargar.disabled = true; }
        btnInsertar.disabled = true;
        if (btnConfirmar) { btnConfirmar.disabled = accion === 'insert'; }

        if (accion === 'preview') {
            if (spinnerCargar) { spinnerCargar.classList.remove('d-none'); }
            setDropzoneProcesando(true, 'Procesando archivo...');
        } else {
            if (modalConfirmacion) { modalConfirmacion.hide(); }
            mostrarOverlayCarga('Insertando equipos, por favor espere...');
            spinnerInsertar.classList.remove('d-none');
            setDropzoneProcesando(true, 'Insertando PC...');
        }

        var formData = new FormData();
        formData.append('archivo', archivoSeleccionado);
        formData.append('accion', accion);
        if (accion === 'insert') {
            formData.append('filas', JSON.stringify(filasSeleccionadas || []));
        }

        fetch('ajax/procesar_carga_masiva.php', {
            method: 'POST',
            body: formData,
        })
            .then(function (res) {
                if (!res.ok) {
                    return res.json().then(function (d) {
                        throw new Error(d.mensaje || 'Error HTTP ' + res.status);
                    });
                }
                return res.json();
            })
            .then(function (data) {
                procesando = false;
                if (spinnerCargar) { spinnerCargar.classList.add('d-none'); }
                spinnerInsertar.classList.add('d-none');
                setDropzoneProcesando(false);
                if (btnCargar) { btnCargar.disabled = false; }

                if (!data.ok) {
                    if (accion === 'insert') {
                        ocultarOverlayCarga();
                        if (btnConfirmar) { btnConfirmar.disabled = false; }
                    }
                    mostrarAlerta(data.mensaje || 'Error desconocido.');
                    actualizarBotonInsertar();
                    return;
                }

                if (accion === 'preview') {
                    filasPreview = data.detalle || [];
                    mostrarPreview(data);
                    activarPaso(4);
                } else {
                    mostrarResultadoInsercion(data);
                    activarPaso(4);
                    mostrarOverlayCarga('Equipos insertados correctamente. Redirigiendo...');
                    setTimeout(function () {
                        window.location.href = 'index.php';
                    }, 5000);
                }
            })
            .catch(function (err) {
                procesando = false;
                if (spinnerCargar) { spinnerCargar.classList.add('d-none'); }
                spinnerInsertar.classList.add('d-none');
                setDropzoneProcesando(false);
                if (btnCargar) { btnCargar.disabled = !archivoSeleccionado; }
                if (accion === 'insert') {
                    ocultarOverlayCarga();
                    if (btnConfirmar) { btnConfirmar.disabled = false; }
                }
                actualizarBotonInsertar();
                mostrarAlerta('Error al procesar: ' + err.message);
            });
    }

    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (!procesando) { dropZone.classList.add('cm-drop-active'); }
    });

    dropZone.addEventListener('dragleave', function (e) {
        e.stopPropagation();
        dropZone.classList.remove('cm-drop-active');
    });

    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.remove('cm-drop-active');
        if (procesando) return;
        var files = e.dataTransfer.files;
        if (files.length > 0) {
            var file = files[0];
            var ext  = file.name.split('.').pop().toLowerCase();
            if (ext !== 'xlsx' && ext !== 'xls') {
                mostrarAlerta('Solo se aceptan archivos .xlsx o .xls.');
                return;
            }
            setArchivo(file);
        }
    });

    dropZone.addEventListener('click', function () {
        if (!procesando) { fileInput.click(); }
    });

    dropZone.addEventListener('keydown', function (e) {
        if ((e.key === 'Enter' || e.key === ' ') && !procesando) {
            e.preventDefault();
            fileInput.click();
        }
    });

    fileInput.addEventListener('change', function () {
        var file = fileInput.files[0] || null;
        if (file) {
            var ext = file.name.split('.').pop().toLowerCase();
            if (ext !== 'xlsx' && ext !== 'xls') {
                fileInput.value = '';
                setArchivo(null);
                mostrarAlerta('El archivo seleccionado no es valido. Debe ser .xlsx o .xls.');
                return;
            }
        }
        setArchivo(file);
    });

    if (btnCargar) {
        btnCargar.addEventListener('click', function () {
            enviarArchivo('preview', []);
        });
    }

    seleccionarTodos.addEventListener('change', function () {
        resultTable.querySelectorAll('.cm-row-check:not(:disabled)').forEach(function (chk) {
            chk.checked = seleccionarTodos.checked;
        });
        actualizarBotonInsertar();
    });

    resultTable.addEventListener('change', function (e) {
        if (e.target.classList.contains('cm-row-check')) {
            actualizarBotonInsertar();
        }
    });

    btnInsertar.addEventListener('click', function () {
        var seleccionadas = obtenerFilasSeleccionadas();
        if (seleccionadas.length === 0) {
            mostrarAlerta('Selecciona al menos una fila valida para insertar.');
            return;
        }
        var stats = obtenerEstadisticasSeleccion();
        confirmarCantidad.textContent = stats.seleccionadas;
        if (confirmarValidas) { confirmarValidas.textContent = stats.validasSeleccionadas; }
        if (confirmarUbicacionesNuevas) { confirmarUbicacionesNuevas.textContent = stats.ubicacionesNuevasSeleccionadas; }
        if (confirmarErrores) { confirmarErrores.textContent = stats.erroresOmitidos; }
        if (confirmarAdvertencias) { confirmarAdvertencias.textContent = stats.advertenciasSeleccionadas; }
        if (confirmarSinUbicacion) { confirmarSinUbicacion.textContent = stats.sinUbicacionSeleccionadas; }
        if (confirmarSinResponsable) { confirmarSinResponsable.textContent = stats.sinResponsableSeleccionadas; }
        if (modalConfirmacion) {
            modalConfirmacion.show();
        } else if (confirm('Se insertaran ' + seleccionadas.length + ' equipos seleccionados. ¿Continuar?')) {
            enviarArchivo('insert', seleccionadas);
        }
    });

    btnConfirmar.addEventListener('click', function () {
        if (procesando) {
            return;
        }
        btnConfirmar.disabled = true;
        enviarArchivo('insert', obtenerFilasSeleccionadas());
    });

    if (btnCancelar) {
        btnCancelar.addEventListener('click', function () {
            cerrarModalConfirmacion();
            if (!overlayCarga || overlayCarga.classList.contains('d-none')) {
                procesando = false;
                if (btnConfirmar) { btnConfirmar.disabled = false; }
                setDropzoneProcesando(false);
                actualizarBotonInsertar();
            }
        });
    }

    if (btnCerrarModal) {
        btnCerrarModal.addEventListener('click', function () {
            cerrarModalConfirmacion();
        });
    }

    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
            if (!overlayCarga || overlayCarga.classList.contains('d-none')) {
                if (btnConfirmar) { btnConfirmar.disabled = false; }
                actualizarBotonInsertar();
            }
        });
    }

    function mostrarPreview(data) {
        resultTitle.textContent = 'Previsualizacion de equipos';

        var tbody = resultTable.querySelector('tbody');
        tbody.innerHTML = '';

        if (!filasPreview.length) {
            tbody.innerHTML = '<tr><td colspan="12" class="text-center text-muted py-3">No se encontraron filas de datos.</td></tr>';
        } else {
            filasPreview.forEach(function (row, index) {
                var d = row.datos || {};
                var tr = document.createElement('tr');
                tr.setAttribute('data-fila', row.fila);
                if (!row.ok) { tr.classList.add('cm-row-error'); }
                else if (row.ubicacion_nueva || (row.advertencias || []).length) { tr.classList.add('cm-row-warning'); }
                else { tr.classList.add('cm-row-valid'); }
                var mensajes = crearMensajesFila(row);
                tr.innerHTML =
                    '<td class="cm-check-col text-center"><input type="checkbox" class="form-check-input cm-row-check" data-fila="' + row.fila + '"' + (row.ok ? ' checked' : ' disabled') + '></td>' +
                    '<td class="cm-row-col text-center fw-semibold">' + (index + 1) + '</td>' +
                    '<td>' + escapeHtml(d.nombre_personalizado || '') + '</td>' +
                    '<td>' + escapeHtml(d.numero_serie || '') + '</td>' +
                    '<td>' + escapeHtml(d.tipo_pc || '') + '</td>' +
                    '<td>' + escapeHtml(d.estado || '') + '</td>' +
                    '<td>' + escapeHtml(d.ubicacion || '') + '</td>' +
                    '<td>' + escapeHtml(d.usuario_asignado || '') + '</td>' +
                    '<td>' + escapeHtml(d.fabricante || '') + '</td>' +
                    '<td>' + escapeHtml(d.producto || '') + '</td>' +
                    '<td>' + escapeHtml(formatearMonedaCLP(d.valor_equipo)) + '</td>' +
                    '<td class="cm-col-advertencias">' + mensajes + '</td>';
                tbody.appendChild(tr);
            });
        }

        btnInsertar.classList.remove('d-none');
        resultSection.classList.remove('d-none');
        resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        actualizarBotonInsertar();
    }

    function mostrarResultadoInsercion(data) {
        resultTitle.textContent = 'Resultado de insercion';
        var resumenInsercion = {
            total: data.total || 0,
            validas: data.insertados || 0,
            errores: data.rechazados || 0,
            advertencias: data.ubicaciones_nuevas || 0,
            ubicaciones_nuevas: data.ubicaciones_nuevas || 0,
            usuarios_no_validos: data.errores_usuario || 0,
            sin_responsable: 0,
            sin_ubicacion: data.errores_ubicacion || 0,
            no_seleccionadas: data.omitidos || 0
        };
        renderResumenSuperior(resumenInsercion);
        renderPanelRevision(resumenInsercion, true);

        var tbody = resultTable.querySelector('tbody');
        tbody.innerHTML = '';
        (data.detalle || []).forEach(function (row, index) {
            var tr = document.createElement('tr');
            if (row.ok) {
                tr.classList.add('table-success');
            } else if (row.omitida) {
                tr.classList.add('table-secondary');
            } else {
                tr.classList.add('table-danger');
            }
            tr.innerHTML =
                '<td class="cm-check-col text-center">-</td>' +
                '<td class="cm-row-col text-center fw-semibold">' + (index + 1) + '</td>' +
                '<td colspan="9">' + escapeHtml(row.mensaje || '') + '</td>' +
                '<td class="cm-col-advertencias">' + (row.ok ? '<span class="badge text-bg-success">Insertado</span>' : (row.omitida ? '<span class="badge text-bg-secondary">Omitido</span>' : '<span class="badge text-bg-danger">Error</span>')) + '</td>';
            tbody.appendChild(tr);
        });

        seleccionarTodos.checked = false;
        seleccionarTodos.indeterminate = false;
        seleccionarTodos.disabled = true;
        btnInsertar.classList.add('d-none');
        btnInsertar.disabled = true;
    }

    function obtenerFilasSeleccionadas() {
        return Array.prototype.slice.call(resultTable.querySelectorAll('.cm-row-check:checked'))
            .map(function (chk) { return parseInt(chk.getAttribute('data-fila'), 10); })
            .filter(function (n) { return n > 0; });
    }

    function obtenerEstadisticasSeleccion() {
        var seleccionadas = obtenerFilasSeleccionadas();
        var seleccionadasSet = {};
        seleccionadas.forEach(function (fila) { seleccionadasSet[fila] = true; });

        var validasSeleccionadas = 0;
        var ubicacionesNuevasSeleccionadas = 0;
        var advertenciasSeleccionadas = 0;
        var erroresOmitidos = 0;
        var sinUbicacionSeleccionadas = 0;
        var sinResponsableSeleccionadas = 0;

        filasPreview.forEach(function (row) {
            if (!row.ok) {
                erroresOmitidos++;
                return;
            }
            if (seleccionadasSet[row.fila]) {
                validasSeleccionadas++;
                if (row.ubicacion_nueva || (row.advertencias || []).length) {
                    advertenciasSeleccionadas++;
                }
                if (row.ubicacion_nueva) {
                    ubicacionesNuevasSeleccionadas++;
                }
                if (tieneAdvertencia(row, 'Sin ubicación')) {
                    sinUbicacionSeleccionadas++;
                }
                if (tieneAdvertencia(row, 'Sin responsable')) {
                    sinResponsableSeleccionadas++;
                }
            }
        });

        return {
            seleccionadas: seleccionadas.length,
            validasSeleccionadas: validasSeleccionadas,
            ubicacionesNuevasSeleccionadas: ubicacionesNuevasSeleccionadas,
            advertenciasSeleccionadas: advertenciasSeleccionadas,
            erroresOmitidos: erroresOmitidos,
            sinUbicacionSeleccionadas: sinUbicacionSeleccionadas,
            sinResponsableSeleccionadas: sinResponsableSeleccionadas
        };
    }

    function actualizarBotonInsertar() {
        var checks = Array.prototype.slice.call(resultTable.querySelectorAll('.cm-row-check:not(:disabled)'));
        var checked = checks.filter(function (chk) { return chk.checked; });
        btnInsertar.disabled = checked.length === 0 || procesando;
        seleccionarTodos.disabled = checks.length === 0 || procesando;
        seleccionarTodos.checked = checks.length > 0 && checked.length === checks.length;
        seleccionarTodos.indeterminate = checked.length > 0 && checked.length < checks.length;
        resultTable.querySelectorAll('tbody tr[data-fila]').forEach(function (tr) {
            var chk = tr.querySelector('.cm-row-check');
            tr.classList.toggle('cm-row-unselected', !!chk && !chk.checked && !chk.disabled);
        });
        var resumen = crearResumenPreview();
        renderResumenSuperior(resumen);
        renderPanelRevision(resumen, false);
    }

    function crearResumenPreview() {
        var checksSeleccionados = {};
        resultTable.querySelectorAll('.cm-row-check:checked').forEach(function (chk) {
            checksSeleccionados[parseInt(chk.getAttribute('data-fila'), 10)] = true;
        });

        var resumen = {
            total: filasPreview.length,
            validas: 0,
            errores: 0,
            advertencias: 0,
            duplicados_serie: 0,
            ubicaciones_nuevas: 0,
            usuarios_no_validos: 0,
            sin_responsable: 0,
            sin_ubicacion: 0,
            no_seleccionadas: 0
        };

        filasPreview.forEach(function (row) {
            var filaSinUbicacion = false;
            var filaConAdvertencia = row.ubicacion_nueva || (row.advertencias || []).length > 0;
            if (row.ok) {
                if (filaConAdvertencia) {
                    resumen.advertencias++;
                } else {
                    resumen.validas++;
                }
                if (!checksSeleccionados[row.fila]) {
                    resumen.no_seleccionadas++;
                }
                if (row.ubicacion_nueva) {
                    resumen.ubicaciones_nuevas++;
                }
                if (tieneAdvertencia(row, 'Sin responsable')) {
                    resumen.sin_responsable++;
                }
                if (tieneAdvertencia(row, 'Sin ubicación')) {
                    filaSinUbicacion = true;
                }
            } else {
                resumen.errores++;
                if (tieneError(row, 'serie ya existe')) {
                    resumen.duplicados_serie++;
                }
                if (tieneError(row, 'usuario asignado')) {
                    resumen.usuarios_no_validos++;
                }
                if (tieneError(row, 'ubicacion') || tieneError(row, 'ubicación')) {
                    filaSinUbicacion = true;
                }
            }
            if (filaSinUbicacion) {
                resumen.sin_ubicacion++;
            }
        });

        return resumen;
    }

    function renderResumenSuperior(resumen) {
        resultSummary.innerHTML =
            summaryCard('Filas leídas', resumen.total, 'neutral') +
            summaryCard('Válidas', resumen.validas, 'success') +
            summaryCard('Advertencias', resumen.advertencias, 'warning') +
            summaryCard('Errores', resumen.errores, 'danger') +
            summaryCard('Ubicaciones nuevas', resumen.ubicaciones_nuevas, 'warning') +
            summaryCard('Sin ubicación', resumen.sin_ubicacion, 'warning') +
            summaryCard('Sin responsable', resumen.sin_responsable, 'warning');
    }

    function renderPanelRevision(resumen, esInsercion) {
        if (!panelRevision) { return; }
        var items = [
            reviewItem('error', 'Revisa filas rojas', 'Las filas con errores reales no se insertarán hasta corregir el Excel.'),
            reviewItem('warning', 'Advertencias permitidas', 'Las filas amarillas se pueden seleccionar e insertar con sus advertencias.'),
            reviewItem('info', 'Ubicaciones nuevas', 'Las ubicaciones nuevas se crearán automáticamente para este colegio al insertar.')
        ];

        panelRevision.innerHTML = items.join('');
        if (esInsercion) {
            panelRevision.insertAdjacentHTML('afterbegin', '<div class="cm-review-result-note">Resultado final de inserción</div>');
        }
    }

    function reviewItem(tipo, titulo, texto) {
        var iconos = {
            ok: 'bi-check-circle',
            error: 'bi-exclamation-octagon',
            warning: 'bi-exclamation-triangle',
            info: 'bi-info-circle'
        };
        return '<article class="cm-review-card cm-review-card--' + tipo + '">' +
            '<i class="bi ' + iconos[tipo] + '"></i>' +
            '<div><strong>' + escapeHtml(titulo) + '</strong><p>' + escapeHtml(texto) + '</p></div>' +
            '</article>';
    }

    function crearMensajesFila(row) {
        var mensajes = [];
        if (!row.ok) {
            (row.errores || []).forEach(function (msg) {
                mensajes.push(rowMessage('danger', 'Error', msg));
            });
            if (!mensajes.length) {
                mensajes.push(rowMessage('danger', 'Error', 'La fila tiene errores reales.'));
            }
            return '<div class="cm-row-messages">' + mensajes.join('') + '</div>';
        }

        if (row.ubicacion_nueva) {
            mensajes.push(rowMessage('warning', 'Ubicación nueva', 'Se creará al insertar.'));
        }

        (row.advertencias || []).forEach(function (msg) {
            if (row.ubicacion_nueva && msg.toLowerCase().indexOf('ubicación nueva') !== -1) {
                return;
            }
            if (msg.toLowerCase().indexOf('sin ubicación') !== -1) {
                mensajes.push(rowMessage('warning', 'Sin ubicación', 'Se insertará como pendiente.'));
                return;
            }
            if (msg.toLowerCase().indexOf('sin responsable') !== -1) {
                mensajes.push(rowMessage('info', 'Sin responsable', 'Se insertará como Sin asignar.'));
                return;
            }
            mensajes.push(rowMessage('warning', 'Advertencia', msg));
        });

        if (!mensajes.length) {
            mensajes.push(rowMessage('success', 'Correcto', 'Fila lista para insertar.'));
        }

        return '<div class="cm-row-messages">' + mensajes.join('') + '</div>';
    }

    function rowMessage(tipo, titulo, texto) {
        var iconos = {
            warning: 'bi-exclamation-triangle',
            info: 'bi-info-circle',
            danger: 'bi-exclamation-circle',
            success: 'bi-check-circle'
        };
        return '<div class="cm-row-message cm-row-message--' + tipo + '">' +
            '<i class="bi ' + iconos[tipo] + '"></i>' +
            '<div><strong>' + escapeHtml(titulo) + '</strong><span>' + escapeHtml(texto) + '</span></div>' +
            '</div>';
    }

    function formatearMonedaCLP(valor) {
        var texto = String(valor || '').trim();
        var normalizado = texto.replace(/\$/g, '').replace(/\s+/g, '').replace(/\./g, '').replace(',', '.');
        var numero = Number(normalizado) || 0;
        return '$' + new Intl.NumberFormat('es-CL', {
            maximumFractionDigits: 0
        }).format(numero);
    }

    function summaryCard(label, value, type) {
        return '<span class="cm-summary-card cm-summary-card--' + type + '">' +
            '<small>' + escapeHtml(label) + '</small>' +
            '<strong>' + value + '</strong>' +
            '</span>';
    }

    function badge(label, value, type) {
        return '<span class="badge text-bg-' + type + ' px-3 py-2">' + label + ': ' + value + '</span>';
    }

    function tieneError(row, texto) {
        return (row.errores || []).join(' ').toLowerCase().indexOf(texto.toLowerCase()) !== -1;
    }

    function tieneAdvertencia(row, texto) {
        return (row.advertencias || []).join(' ').toLowerCase().indexOf(texto.toLowerCase()) !== -1;
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function mostrarAlerta(msg) {
        if (window.Swal) {
            Swal.fire({ icon: 'warning', title: 'Atencion', text: msg, confirmButtonColor: '#0f4c81' });
        } else {
            alert(msg);
        }
    }

    function mostrarOverlayCarga(mensaje) {
        if (!overlayCarga) { return; }
        if (overlayMensaje) {
            overlayMensaje.textContent = mensaje || 'Insertando equipos, por favor espere...';
        }
        overlayCarga.classList.remove('d-none');
    }

    function ocultarOverlayCarga() {
        if (!overlayCarga) { return; }
        overlayCarga.classList.add('d-none');
    }

    function cerrarModalConfirmacion() {
        if (modalConfirmacion) {
            modalConfirmacion.hide();
            return;
        }
        if (!modalEl) { return; }
        modalEl.classList.remove('show');
        modalEl.setAttribute('aria-hidden', 'true');
        modalEl.style.display = 'none';
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
            backdrop.parentNode.removeChild(backdrop);
        });
    }
})();
