(function () {
    'use strict';

    var dropZone      = document.getElementById('cmDropZone');
    var fileInput     = document.getElementById('cmFileInput');
    var fileNameLabel = document.getElementById('cmFileName');
    var btnCargar     = document.getElementById('btnCargarMasiva');
    var btnInsertar   = document.getElementById('btnInsertarPc');
    var btnConfirmar  = document.getElementById('btnConfirmarInsertarPc');
    var seleccionarTodos = document.getElementById('cmSeleccionarTodos');
    var resultSection = document.getElementById('cmResultados');
    var resultTable   = document.getElementById('cmTablaResultados');
    var resultSummary = document.getElementById('cmResumenResultados');
    var resultTitle   = document.getElementById('cmTituloResultados');
    var confirmarCantidad = document.getElementById('cmConfirmarCantidad');
    var spinnerCargar = document.getElementById('cmSpinner');
    var spinnerInsertar = document.getElementById('cmSpinnerInsertar');
    var dropIcon      = dropZone.querySelector('.cm-dropzone-icon');
    var dropText      = dropZone.querySelector('.cm-dropzone-text');
    var modalEl       = document.getElementById('modalConfirmarInsertarPc');

    var archivoSeleccionado = null;
    var procesando          = false;
    var filasPreview        = [];
    var modalConfirmacion   = null;

    if (window.bootstrap && modalEl) {
        modalConfirmacion = new bootstrap.Modal(modalEl);
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
            btnCargar.disabled = false;
            activarPaso(3);
        } else {
            fileNameLabel.textContent = 'Ningun archivo seleccionado';
            fileNameLabel.classList.remove('text-dark', 'fw-semibold');
            fileNameLabel.classList.add('text-muted');
            btnCargar.disabled = true;
            activarPaso(1);
        }
    }

    function limpiarTabla() {
        resultSection.classList.add('d-none');
        resultTable.querySelector('tbody').innerHTML = '';
        resultSummary.innerHTML = '';
        btnInsertar.classList.add('d-none');
        btnInsertar.disabled = true;
        seleccionarTodos.checked = false;
        seleccionarTodos.indeterminate = false;
    }

    function enviarArchivo(accion, filasSeleccionadas) {
        if (!archivoSeleccionado || procesando) return;

        procesando = true;
        btnCargar.disabled = true;
        btnInsertar.disabled = true;

        if (accion === 'preview') {
            spinnerCargar.classList.remove('d-none');
            setDropzoneProcesando(true, 'Leyendo archivo...');
        } else {
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
                spinnerCargar.classList.add('d-none');
                spinnerInsertar.classList.add('d-none');
                setDropzoneProcesando(false);
                btnCargar.disabled = false;

                if (!data.ok) {
                    mostrarAlerta(data.mensaje || 'Error desconocido.');
                    actualizarBotonInsertar();
                    return;
                }

                if (accion === 'preview') {
                    filasPreview = data.detalle || [];
                    mostrarPreview(data);
                    activarPaso(4);
                } else {
                    if (modalConfirmacion) { modalConfirmacion.hide(); }
                    mostrarResultadoInsercion(data);
                    activarPaso(4);
                }
            })
            .catch(function (err) {
                procesando = false;
                spinnerCargar.classList.add('d-none');
                spinnerInsertar.classList.add('d-none');
                setDropzoneProcesando(false);
                btnCargar.disabled = !archivoSeleccionado;
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
        setArchivo(fileInput.files[0] || null);
    });

    btnCargar.addEventListener('click', function () {
        enviarArchivo('preview', []);
    });

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
        confirmarCantidad.textContent = seleccionadas.length;
        if (modalConfirmacion) {
            modalConfirmacion.show();
        } else if (confirm('Se insertaran ' + seleccionadas.length + ' equipos seleccionados. ¿Continuar?')) {
            enviarArchivo('insert', seleccionadas);
        }
    });

    btnConfirmar.addEventListener('click', function () {
        enviarArchivo('insert', obtenerFilasSeleccionadas());
    });

    function mostrarPreview(data) {
        resultTitle.textContent = 'Previsualizacion de equipos';
        resultSummary.innerHTML =
            '<span class="badge text-bg-primary me-1 px-3 py-2">Total: ' + (data.total || 0) + '</span>' +
            '<span class="badge text-bg-success me-1 px-3 py-2">Validos: ' + (data.valid_count || 0) + '</span>' +
            '<span class="badge text-bg-danger me-1 px-3 py-2">Con error: ' + (data.error_count || 0) + '</span>';

        var tbody = resultTable.querySelector('tbody');
        tbody.innerHTML = '';

        if (!filasPreview.length) {
            tbody.innerHTML = '<tr><td colspan="12" class="text-center text-muted py-3">No se encontraron filas de datos.</td></tr>';
        } else {
            filasPreview.forEach(function (row) {
                var d = row.datos || {};
                var tr = document.createElement('tr');
                if (!row.ok) { tr.classList.add('table-danger'); }
                tr.innerHTML =
                    '<td class="text-center"><input type="checkbox" class="form-check-input cm-row-check" data-fila="' + row.fila + '"' + (row.ok ? ' checked' : ' disabled') + '></td>' +
                    '<td class="text-center fw-semibold">' + row.fila + '</td>' +
                    '<td>' + escapeHtml(d.nombre_personalizado || '') + '</td>' +
                    '<td>' + escapeHtml(d.numero_serie || '') + '</td>' +
                    '<td>' + escapeHtml(d.tipo_pc || '') + '</td>' +
                    '<td>' + escapeHtml(d.estado || '') + '</td>' +
                    '<td>' + escapeHtml(d.ubicacion || '') + '</td>' +
                    '<td>' + escapeHtml(d.usuario_asignado || '') + '</td>' +
                    '<td>' + escapeHtml(d.fabricante || '') + '</td>' +
                    '<td>' + escapeHtml(d.producto || '') + '</td>' +
                    '<td>' + escapeHtml(d.valor_equipo || '') + '</td>' +
                    '<td>' + (row.ok ? '<span class="badge text-bg-success">Valida</span>' : escapeHtml((row.errores || []).join(' '))) + '</td>';
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
        resultSummary.innerHTML =
            '<span class="badge text-bg-success me-1 px-3 py-2">Insertados: ' + (data.insertados || 0) + '</span>' +
            '<span class="badge text-bg-secondary me-1 px-3 py-2">Omitidos: ' + (data.omitidos || 0) + '</span>' +
            '<span class="badge text-bg-danger me-1 px-3 py-2">Rechazados: ' + (data.rechazados || 0) + '</span>' +
            '<span class="badge text-bg-warning me-1 px-3 py-2">Duplicados: ' + (data.duplicados || 0) + '</span>' +
            '<span class="badge text-bg-info me-1 px-3 py-2">Usuario: ' + (data.errores_usuario || 0) + '</span>' +
            '<span class="badge text-bg-info me-1 px-3 py-2">Ubicacion: ' + (data.errores_ubicacion || 0) + '</span>' +
            '<span class="badge text-bg-info px-3 py-2">Estado/Tipo: ' + (data.errores_estado_tipo || 0) + '</span>';

        var tbody = resultTable.querySelector('tbody');
        tbody.innerHTML = '';
        (data.detalle || []).forEach(function (row) {
            var tr = document.createElement('tr');
            if (row.ok) {
                tr.classList.add('table-success');
            } else if (row.omitida) {
                tr.classList.add('table-secondary');
            } else {
                tr.classList.add('table-danger');
            }
            tr.innerHTML =
                '<td class="text-center">-</td>' +
                '<td class="text-center fw-semibold">' + row.fila + '</td>' +
                '<td colspan="9">' + escapeHtml(row.mensaje || '') + '</td>' +
                '<td>' + (row.ok ? '<span class="badge text-bg-success">Insertado</span>' : (row.omitida ? '<span class="badge text-bg-secondary">Omitido</span>' : '<span class="badge text-bg-danger">Error</span>')) + '</td>';
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

    function actualizarBotonInsertar() {
        var checks = Array.prototype.slice.call(resultTable.querySelectorAll('.cm-row-check:not(:disabled)'));
        var checked = checks.filter(function (chk) { return chk.checked; });
        btnInsertar.disabled = checked.length === 0 || procesando;
        seleccionarTodos.disabled = checks.length === 0 || procesando;
        seleccionarTodos.checked = checks.length > 0 && checked.length === checks.length;
        seleccionarTodos.indeterminate = checked.length > 0 && checked.length < checks.length;
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
})();
