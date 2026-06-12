(function () {
    'use strict';

    var dropZone      = document.getElementById('cmDropZone');
    var fileInput     = document.getElementById('cmFileInput');
    var fileNameLabel = document.getElementById('cmFileName');
    var btnCargar     = document.getElementById('btnCargarMasiva');
    var resultSection = document.getElementById('cmResultados');
    var resultTable   = document.getElementById('cmTablaResultados');
    var resultSummary = document.getElementById('cmResumenResultados');
    var spinnerCargar = document.getElementById('cmSpinner');
    var dropIcon      = dropZone.querySelector('.cm-dropzone-icon');
    var dropText      = dropZone.querySelector('.cm-dropzone-text');

    var archivoSeleccionado = null;
    var procesando          = false;

    // ── Stepper ──────────────────────────────────────────────────────────────

    function activarPaso(num) {
        var pasos  = document.querySelectorAll('.cm-stepper-step');
        var lineas = document.querySelectorAll('.cm-stepper-line');

        pasos.forEach(function (paso) {
            var n = parseInt(paso.getAttribute('data-step'), 10);
            paso.classList.remove('active', 'completed');
            if (n < num)  { paso.classList.add('completed'); }
            if (n === num) { paso.classList.add('active');    }
        });

        lineas.forEach(function (linea) {
            var n = parseInt(linea.getAttribute('data-line'), 10);
            linea.classList.remove('active', 'completed');
            if (n < num)  { linea.classList.add('completed'); }
            if (n === num) { linea.classList.add('active');   }
        });
    }

    activarPaso(1);

    // ── Helpers ──────────────────────────────────────────────────────────────

    function setDropzoneProcesando(activo) {
        if (activo) {
            dropZone.classList.add('cm-drop-processing');
            dropIcon.className = 'bi bi-hourglass-split cm-dropzone-icon';
            dropText.textContent = 'Procesando archivo…';
        } else {
            dropZone.classList.remove('cm-drop-processing');
            dropIcon.className = 'bi bi-cloud-upload cm-dropzone-icon';
            dropText.textContent = 'Arrastra el archivo aquí o haz clic para seleccionarlo';
        }
    }

    function setArchivo(file) {
        archivoSeleccionado = file;
        if (file) {
            fileNameLabel.textContent = file.name;
            fileNameLabel.classList.remove('text-muted');
            fileNameLabel.classList.add('text-dark', 'fw-semibold');
            activarPaso(3);
            procesarCargaMasiva(file);
        } else {
            fileNameLabel.textContent = 'Ningún archivo seleccionado';
            fileNameLabel.classList.remove('text-dark', 'fw-semibold');
            fileNameLabel.classList.add('text-muted');
            activarPaso(1);
        }
        btnCargar.disabled = !file;
    }

    // ── Procesamiento ─────────────────────────────────────────────────────────

    function procesarCargaMasiva(archivo) {
        if (!archivo || procesando) return;

        procesando = true;
        btnCargar.disabled = true;
        spinnerCargar.classList.remove('d-none');
        resultSection.classList.add('d-none');
        setDropzoneProcesando(true);

        var formData = new FormData();
        formData.append('archivo', archivo);

        fetch('ajax/procesar_carga_masiva.php', {
            method: 'POST',
            body:   formData,
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
                btnCargar.disabled = false;
                setDropzoneProcesando(false);

                if (!data.ok && !data.detalle) {
                    mostrarAlerta('Error: ' + (data.mensaje || 'Error desconocido.'));
                    return;
                }

                mostrarResultados(data);
                activarPaso(4);
            })
            .catch(function (err) {
                procesando = false;
                spinnerCargar.classList.add('d-none');
                btnCargar.disabled = false;
                setDropzoneProcesando(false);
                mostrarAlerta('Error al procesar: ' + err.message);
            });
    }

    // ── Drag & drop ──────────────────────────────────────────────────────────

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

    // Botón como reintento manual
    btnCargar.addEventListener('click', function () {
        procesarCargaMasiva(archivoSeleccionado);
    });

    // ── Resultados ───────────────────────────────────────────────────────────

    function mostrarResultados(data) {
        var total   = data.total       || 0;
        var ok      = data.ok_count    || 0;
        var errores = data.error_count || 0;

        resultSummary.innerHTML =
            '<span class="badge text-bg-primary me-1 px-3 py-2">Total: ' + total + '</span>' +
            '<span class="badge text-bg-success me-1 px-3 py-2">Correctos: ' + ok + '</span>' +
            (errores > 0
                ? '<span class="badge text-bg-danger px-3 py-2">Con error: ' + errores + '</span>'
                : '<span class="badge text-bg-success px-3 py-2"><i class="bi bi-check-circle me-1"></i>Sin errores</span>');

        var tbody = resultTable.querySelector('tbody');
        tbody.innerHTML = '';

        var detalle = data.detalle || [];
        if (detalle.length === 0) {
            var trVacio = document.createElement('tr');
            trVacio.innerHTML = '<td colspan="3" class="text-center text-muted py-3">No se procesaron filas de datos.</td>';
            tbody.appendChild(trVacio);
        } else {
            detalle.forEach(function (row) {
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td class="text-center fw-semibold">' + row.fila + '</td>' +
                    '<td class="text-center">' +
                        (row.ok
                            ? '<span class="badge text-bg-success">OK</span>'
                            : '<span class="badge text-bg-danger">ERROR</span>') +
                    '</td>' +
                    '<td>' + escapeHtml(row.mensaje || '') + '</td>';
                tbody.appendChild(tr);
            });
        }

        resultSection.classList.remove('d-none');
        resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
            Swal.fire({ icon: 'warning', title: 'Atención', text: msg, confirmButtonColor: '#0f4c81' });
        } else {
            alert(msg);
        }
    }
})();
