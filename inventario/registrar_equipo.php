<?php
require_once __DIR__ . '/componentes/boot.php';

$tituloPagina   = 'Registrar equipo';
$colegioUsuario = $inventario->obtenerColegioDelUsuario($idUsuarioSession);
$idColegioForm  = (int)($colegioUsuario['id_colegio'] ?? 0);
$usuarios       = $inventario->obtenerUsuarios();
$estados        = $inventario->obtenerEstados();
$tiposPc        = $inventario->obtenerTiposPc();
$ubicaciones    = $inventario->obtenerUbicacionesPorColegio($idColegioForm);
$modo = 'crear';

require __DIR__ . '/componentes/layout_top.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Registrar equipo</h1>
        <p class="text-muted mb-0">Carga estructurada de ficha tecnica, compra, componentes y evidencias fotograficas.</p>
    </div>
</div>

<!-- ===== PANEL INVENTARIO AUTOMATICO ===== -->
<div class="card border-0 shadow-sm mb-4 inv-agent-panel">
    <div class="card-body p-4">

        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="inv-agent-icon"><i class="bi bi-cpu"></i></span>
            <div>
                <h6 class="mb-0 fw-semibold">Inventario Automatico mediante Agente</h6>
                <p class="text-muted small mb-0">Ejecuta el agente en el equipo y sube el JSON para autocompletar los datos tecnicos.</p>
            </div>
        </div>

        <div class="row g-4 align-items-start">

            <!-- Pasos -->
            <div class="col-lg-5">
                <ol class="inv-agent-steps mb-0">
                    <li>Descarga el agente y cópialo al equipo a inventariar.</li>
                    <li>Ejecútalo como Administrador (doble clic en el <code>.exe</code>).</li>
                    <li>Se generará un archivo <code>inventario_NOMBREEQUIPO.json</code>.</li>
                    <li>Selecciona ese archivo con el botón de abajo.</li>
                    <li>Los datos técnicos se completarán automáticamente en el formulario.</li>
                </ol>
            </div>

            <!-- Acciones -->
            <div class="col-lg-7">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="ajax/descargar_agente.php" class="btn btn-outline-primary">
                        <i class="bi bi-download me-1"></i>Descargar Agente
                    </a>
                    <label class="btn btn-primary mb-0" for="importarJsonAgente">
                        <i class="bi bi-file-earmark-arrow-up me-1"></i>Importar Inventario Automático
                    </label>
                    <input type="file" id="importarJsonAgente" accept=".json" class="d-none">
                </div>

                <!-- Resultado de importacion -->
                <div id="agentImportResult" class="d-none"></div>
            </div>

        </div>
    </div>
</div>

<!-- ===== FORMULARIO MANUAL ===== -->
<div class="card shadow-sm border-0 inv-panel">
    <div class="card-body">
        <?php require __DIR__ . '/componentes/formulario_equipo.php'; ?>
    </div>
</div>

<script>
(function () {
    'use strict';

    var input   = document.getElementById('importarJsonAgente');
    var result  = document.getElementById('agentImportResult');

    if (!input) return;

    input.addEventListener('change', function () {
        var file = this.files && this.files[0];
        if (!file) return;

        var reader = new FileReader();
        reader.onload = function (e) {
            try {
                var data = JSON.parse(e.target.result);
                aplicarDatos(data);
            } catch (err) {
                mostrarEstado('error', 'El archivo no es un JSON válido.');
            }
        };
        reader.readAsText(file, 'utf-8');
        // Reset input para permitir volver a importar el mismo archivo
        input.value = '';
    });

    function aplicarDatos(d) {
        var campos_ok  = 0;
        var campos_err = [];

        // ── Datos generales ─────────────────────────────────────────────
        set('nombre_equipo', d.nombre_equipo,   campos_err) && campos_ok++;
        set('fabricante',    d.fabricante,       campos_err) && campos_ok++;
        set('producto',      d.modelo,           campos_err) && campos_ok++;
        set('numero_serie',  d.serial,           campos_err) && campos_ok++;

        // ── Procesador ──────────────────────────────────────────────────
        set('procesador_fabricante', d.procesador_fab, campos_err) && campos_ok++;
        set('procesador_modelo',     d.procesador,     campos_err) && campos_ok++;
        set('procesador_velocidad',  d.procesador_vel, campos_err) && campos_ok++;

        // ── Almacenamiento (primer disco) ────────────────────────────────
        var disco = Array.isArray(d.almacenamiento) && d.almacenamiento.length
            ? d.almacenamiento[0] : null;
        if (disco) {
            set('equipo_modelo',    disco.modelo,    campos_err) && campos_ok++;
            set('equipo_capacidad', disco.capacidad, campos_err) && campos_ok++;
            set('equipo_tamano',    disco.tipo,      campos_err) && campos_ok++;
        }

        // ── Software — Windows ───────────────────────────────────────────
        set('windows', d.windows, campos_err) && campos_ok++;

        // ── RAM — primer modulo de memoria ───────────────────────────────
        if (d.ram_gb && d.ram_gb > 0) {
            var ramGb = d.ram_gb + ' GB';
            set('memoria[0][tamano_memoria]', ramGb, campos_err) && campos_ok++;
        }

        // ── Monitores asociados al PC ────────────────────────────────────
        if (Array.isArray(d.monitores) && d.monitores.length) {
            llenarMonitores(d.monitores);
            campos_ok++;
        }

        // ── Abrir acordeones relevantes ──────────────────────────────────
        abrirAcordeon('datosGenerales');
        abrirAcordeon('datosHardware');
        if (d.ram_gb > 0) abrirAcordeon('datosRam');
        if (d.monitores && d.monitores.length) abrirAcordeon('datosMonitores');

        // ── Resumen visual ───────────────────────────────────────────────
        var equipo  = d.nombre_equipo || '(sin nombre)';
        var fab     = [d.fabricante, d.modelo].filter(Boolean).join(' ');
        var discos  = Array.isArray(d.almacenamiento)
            ? d.almacenamiento.map(function (dk) { return dk.tipo + ' ' + dk.capacidad; }).join(', ')
            : '—';
        var mons    = Array.isArray(d.monitores) ? d.monitores.length : 0;

        mostrarEstado('ok',
            '<strong>Datos importados correctamente.</strong><br>' +
            '<span class="text-muted small">' +
                equipo + (fab ? ' · ' + fab : '') +
                (d.serial ? ' · S/N: ' + d.serial : '') + '<br>' +
                (d.procesador ? 'CPU: ' + d.procesador + ' · ' : '') +
                (d.ram_gb ? 'RAM: ' + d.ram_gb + ' GB · ' : '') +
                'Disco: ' + discos + ' · ' +
                mons + ' monitor(s)' +
            '</span>'
        );
    }

    function set(name, value, errList) {
        if (value === null || value === undefined || String(value).trim() === '') {
            return false;
        }
        var el = document.querySelector('[name="' + name + '"]');
        if (!el) { errList.push(name); return false; }
        el.value = String(value).trim();
        return true;
    }

    function llenarMonitores(monitores) {
        // Limpiar filas existentes vacías y rellenar desde cero
        var contenedor = document.getElementById('contenedorMonitores');
        if (!contenedor) return;

        // Mantener la primera fila (PHP la renderiza), eliminar las extras
        var items = contenedor.querySelectorAll('.monitor-item');
        for (var i = 1; i < items.length; i++) {
            items[i].remove();
        }

        monitores.forEach(function (mon, idx) {
            if (idx === 0) {
                // Llenar la primera fila ya existente
                setByName('monitor[0][modelo_monitor]',     mon.modelo || '');
                setByName('monitor[0][resolucion_monitor]', mon.resolucion || '');
            } else {
                // Agregar filas adicionales via el mismo template del boton
                var item = crearFilaMonitor(idx, mon);
                contenedor.appendChild(item);
            }
        });
    }

    function crearFilaMonitor(idx, mon) {
        var div = document.createElement('div');
        div.className = 'inv-repeat-card monitor-item';
        div.innerHTML =
            '<div class="row g-3">' +
                '<div class="col-md-3"><label class="form-label">Modelo</label>' +
                    '<input type="text" name="monitor[' + idx + '][modelo_monitor]" class="form-control" value="' + esc(mon.modelo || '') + '"></div>' +
                '<div class="col-md-2"><label class="form-label">Codigo</label>' +
                    '<input type="text" name="monitor[' + idx + '][codigo_monitor]" class="form-control"></div>' +
                '<div class="col-md-2"><label class="form-label">Serie</label>' +
                    '<input type="text" name="monitor[' + idx + '][serie_monitor]" class="form-control"></div>' +
                '<div class="col-md-2"><label class="form-label">Tamano</label>' +
                    '<input type="text" name="monitor[' + idx + '][tamano_monitor]" class="form-control"></div>' +
                '<div class="col-md-2"><label class="form-label">Resolucion</label>' +
                    '<input type="text" name="monitor[' + idx + '][resolucion_monitor]" class="form-control" value="' + esc(mon.resolucion || '') + '"></div>' +
                '<div class="col-md-1"><label class="form-label">Orden</label>' +
                    '<input type="number" name="monitor[' + idx + '][orden_monitor]" class="form-control" value="' + (idx + 1) + '"></div>' +
            '</div>' +
            '<button type="button" class="btn btn-link text-danger px-0 mt-2 btnEliminarFila">Quitar fila</button>';
        return div;
    }

    function setByName(name, value) {
        var el = document.querySelector('[name="' + name + '"]');
        if (el) el.value = value;
    }

    function abrirAcordeon(id) {
        var el = document.getElementById(id);
        if (!el) return;
        // Bootstrap 5: añadir clases show y quitar collapsed del boton
        if (!el.classList.contains('show')) {
            el.classList.add('show');
            var btn = document.querySelector('[data-bs-target="#' + id + '"]');
            if (btn) btn.classList.remove('collapsed');
        }
    }

    function mostrarEstado(tipo, html) {
        result.className = tipo === 'ok'
            ? 'alert alert-success py-2 px-3 small mb-0'
            : 'alert alert-danger py-2 px-3 small mb-0';
        result.innerHTML = html;
        result.classList.remove('d-none');
    }

    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
}());
</script>

<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
