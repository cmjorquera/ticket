(function () {
    'use strict';

    var data = window.inventarioDashboardCharts || {};
    var config = window.inventarioDashboardConfig || {};
    var modalInstance = null;
    var alertaActual = null;
    var chartInstances = {};

    // Cache de <img> del scatter de colegios (logo por id_colegio). Vive a
    // nivel de modulo para no recargar la imagen si el chart se redibuja
    // (p.ej. al hacer resize) dentro de la misma carga de pagina.
    var scatterImageCache = {};

    function chartColors(count) {
        var palette = ['#0d6efd', '#3a9131', '#f2b705', '#dc3545', '#20c997', '#6f42c1', '#0dcaf0', '#6c757d'];
        var colors = [];
        for (var i = 0; i < count; i += 1) {
            colors.push(palette[i % palette.length]);
        }
        return colors;
    }

    // Colores del colegio filtrado (principal/secundario/terciario/cuaternario),
    // con respaldo ya resuelto en PHP ($coloresChartColegio) -- siempre vienen
    // 4 hex validos, nunca vacio. Para mas de 4 series se completa con la
    // paleta generica chartColors().
    function colegioColorPalette(count) {
        var c = config.colores || {};
        var propios = [c.principal, c.secundario, c.terciario, c.cuaternario].filter(Boolean);
        if (!propios.length) { return chartColors(count); }
        var respaldo = chartColors(count);
        var colors = [];
        for (var i = 0; i < count; i += 1) {
            colors.push(i < propios.length ? propios[i] : respaldo[i]);
        }
        return colors;
    }

    function createChart(id, type, source, colorPalette) {
        if (typeof Chart === 'undefined' || !source || !source.labels || !source.labels.length) {
            return;
        }
        var el = document.getElementById(id);
        if (!el) {
            return;
        }

        var isBar = type === 'bar';
        var colors = isBar ? source.labels.map(function () { return '#3a9131'; }) : (colorPalette || chartColors(source.labels.length));
        if (chartInstances[id]) {
            chartInstances[id].destroy();
        }

        chartInstances[id] = new Chart(el, {
            type: type,
            data: {
                labels: source.labels,
                datasets: [{
                    data: source.values,
                    backgroundColor: colors,
                    borderColor: isBar ? '#3a9131' : colors,
                    borderWidth: isBar ? 0 : 1,
                    hoverBackgroundColor: isBar ? '#2f7d28' : colors,
                    barPercentage: 0.72,
                    categoryPercentage: 0.72
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 8,
                        right: 10,
                        bottom: 0,
                        left: 4
                    }
                },
                legend: {
                    display: type !== 'bar',
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        padding: 14,
                        fontColor: '#64748b',
                        fontSize: 12
                    }
                },
                scales: type === 'bar' ? {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0,
                            fontColor: '#94a3b8',
                            padding: 8
                        },
                        gridLines: {
                            color: 'rgba(15, 23, 42, 0.07)',
                            drawBorder: false,
                            zeroLineColor: 'rgba(15, 23, 42, 0.10)'
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            fontColor: '#94a3b8',
                            padding: 8,
                            autoSkip: true,
                            maxRotation: 0
                        },
                        gridLines: {
                            color: 'rgba(15, 23, 42, 0.05)',
                            drawBorder: false
                        }
                    }]
                } : {},
                cutoutPercentage: type === 'doughnut' ? 62 : undefined,
                tooltips: {
                    backgroundColor: 'rgba(15, 23, 42, 0.92)',
                    titleFontColor: '#fff',
                    bodyFontColor: '#fff',
                    cornerRadius: 8,
                    xPadding: 12,
                    yPadding: 10,
                    callbacks: {
                        label: function (tooltipItem, chartData) {
                            var label = chartData.labels[tooltipItem.index] || '';
                            var value = chartData.datasets[0].data[tooltipItem.index] || 0;
                            var total = chartData.datasets[0].data.reduce(function (sum, item) {
                                return sum + (parseInt(item, 10) || 0);
                            }, 0);
                            var porcentaje = total > 0 ? Math.round((value / total) * 100) : 0;
                            return type === 'doughnut'
                                ? label + ': ' + value + ' equipos (' + porcentaje + '%)'
                                : label + ': ' + value;
                        }
                    }
                }
            }
        });
    }

    // Dinero en formato CLP ($1.234.567), igual que inv_dash_money() en PHP.
    function formatMoney(value) {
        var n = Math.round(Number(value) || 0);
        return '$' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Version corta para ejes ($1,2M / $850K), donde el numero completo no cabe.
    function formatMoneyShort(value) {
        var n = Number(value) || 0;
        if (Math.abs(n) >= 1000000) { return '$' + (n / 1000000).toFixed(1).replace('.', ',') + 'M'; }
        if (Math.abs(n) >= 1000) { return '$' + Math.round(n / 1000) + 'K'; }
        return '$' + n;
    }

    // Scatter "equipos vs valor por colegio". Un solo punto por colegio;
    // si hay un colegio filtrado, se resalta en una serie aparte (no se
    // codifica identidad por color salvo para ese caso puntual).
    // Precarga los logos de colegio usados en el scatter y pide un redibujo
    // del chart cada vez que uno termina de cargar (si no, Chart.js ya
    // pinto el frame inicial sin la imagen -- <img>.onload es asincronico
    // y nada lo redibuja solo). Cachea por URL: si dos colegios repiten
    // imagen (no deberia pasar, pero por si acaso) no la pide dos veces.
    function precargarImagenesScatter(id, points) {
        points.forEach(function (p) {
            if (!p.imagen) { return; }
            if (scatterImageCache[p.imagen]) { return; }
            var img = new Image();
            scatterImageCache[p.imagen] = { img: img, cargada: false };
            img.onload = function () {
                scatterImageCache[p.imagen].cargada = true;
                if (chartInstances[id]) { chartInstances[id].update(); }
            };
            img.onerror = function () {
                // Imagen rota/404: se cae al circulo de color (ver plugin).
                delete scatterImageCache[p.imagen];
            };
            img.src = p.imagen;
        });
    }

    // Plugin Chart.js v2 (proyecto usa Chart.js 2.9.4 -- OJO si se
    // actualiza la libreria, la firma de plugins locales cambia en v3+).
    // Dibuja, sobre cada punto del scatter, un circulo con el logo del
    // colegio recortado (clip circular), fondo color_principal y borde
    // color_secundario. Sin imagen (o mientras carga) cae al circulo solo.
    var scatterLogoPlugin = {
        afterDatasetsDraw: function (chart) {
            var ctx = chart.ctx;
            var dataset = chart.data.datasets[0];
            var meta = chart.getDatasetMeta(0);

            meta.data.forEach(function (element, index) {
                var point = dataset.data[index];
                var pos = element.getCenterPoint ? element.getCenterPoint() : { x: element._model.x, y: element._model.y };
                var radio = (dataset.pointRadius && dataset.pointRadius[index]) || 14;
                var colorPrincipal = point.color_principal || '#2a78d6';
                var colorSecundario = point.color_secundario || '#008300';
                var cache = point.imagen ? scatterImageCache[point.imagen] : null;

                ctx.save();

                // Fondo (color_principal)
                ctx.fillStyle = colorPrincipal;
                ctx.beginPath();
                ctx.arc(pos.x, pos.y, radio, 0, 2 * Math.PI);
                ctx.fill();

                if (cache && cache.cargada) {
                    // Logo recortado en circulo
                    ctx.save();
                    ctx.beginPath();
                    ctx.arc(pos.x, pos.y, radio - 2, 0, 2 * Math.PI);
                    ctx.clip();
                    ctx.drawImage(cache.img, pos.x - (radio - 2), pos.y - (radio - 2), (radio - 2) * 2, (radio - 2) * 2);
                    ctx.restore();
                }

                // Borde (color_secundario). El colegio filtrado lleva un
                // segundo anillo exterior para distinguirse del resto.
                ctx.lineWidth = point.seleccionado ? 4 : 2.5;
                ctx.strokeStyle = colorSecundario;
                ctx.beginPath();
                ctx.arc(pos.x, pos.y, radio, 0, 2 * Math.PI);
                ctx.stroke();

                if (point.seleccionado) {
                    ctx.lineWidth = 2;
                    ctx.strokeStyle = colorPrincipal;
                    ctx.beginPath();
                    ctx.arc(pos.x, pos.y, radio + 4, 0, 2 * Math.PI);
                    ctx.stroke();
                }

                ctx.restore();
            });
        }
    };

    // Scatter "equipos vs valor por colegio": cada punto es el logo del
    // colegio (fondo color_principal, borde color_secundario -- ver
    // scatterLogoPlugin arriba), clickeable para filtrar el dashboard por
    // ese colegio. $chartScatterColegios en PHP ya trae id_colegio, x, y,
    // imagen e id_colegio validados (con respaldo si el colegio no tiene
    // colores/foto propios).
    function createScatterChart(id, points) {
        if (typeof Chart === 'undefined' || !points || !points.length) { return; }
        var el = document.getElementById(id);
        if (!el) { return; }
        if (chartInstances[id]) { chartInstances[id].destroy(); }

        precargarImagenesScatter(id, points);

        var radios = points.map(function (p) { return p.seleccionado ? 18 : 14; });
        var hoverRadios = points.map(function (p) { return p.seleccionado ? 22 : 18; });

        var chart = new Chart(el, {
            type: 'scatter',
            // Plugin LOCAL (solo este chart, no global): debe ir aca, en el
            // objeto de config que recibe "new Chart(...)". Registrarlo
            // despues de construido el chart (chart.config.plugins = ...)
            // NO funciona en Chart.js v2: la lista de plugins de cada chart
            // se cachea en la primera pasada y no vuelve a leerse.
            plugins: [scatterLogoPlugin],
            data: {
                datasets: [{
                    label: 'Colegios',
                    data: points, // cada item ya trae x, y, colegio, imagen, colores, seleccionado
                    backgroundColor: 'transparent', // el plugin pinta el fondo real por punto
                    borderColor: 'transparent',     // idem el borde -- ver scatterLogoPlugin
                    pointRadius: radios,
                    pointHoverRadius: hoverRadios
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false }, // leyenda con logos propia debajo del chart (dashboard.php)
                onHover: function (event, activeElements) {
                    // Se usa "el" (el <canvas>, cerrado por closure) en vez de
                    // event.target: en Chart.js v2 el evento de onHover no
                    // siempre expone .target de forma confiable entre navegadores.
                    el.style.cursor = activeElements.length ? 'pointer' : 'default';
                },
                // Click en un punto = filtrar el dashboard por ese colegio.
                // Se preserva el tab de tipo_equipo activo (config.tipoEquipo).
                onClick: function (event, activeElements) {
                    if (!activeElements || !activeElements.length) { return; }
                    var idx = activeElements[0]._index;
                    var punto = points[idx];
                    if (!punto || !punto.id_colegio) { return; }
                    window.location.href = 'dashboard.php?id_colegio=' + punto.id_colegio +
                        '&tipo_equipo=' + encodeURIComponent(config.tipoEquipo || 'pc');
                },
                scales: {
                    xAxes: [{
                        scaleLabel: { display: true, labelString: 'Cantidad de equipos', fontColor: '#94a3b8' },
                        ticks: { beginAtZero: true, precision: 0, fontColor: '#94a3b8' },
                        gridLines: { color: 'rgba(15, 23, 42, 0.05)', drawBorder: false }
                    }],
                    yAxes: [{
                        scaleLabel: { display: true, labelString: 'Valor del inventario', fontColor: '#94a3b8' },
                        ticks: { beginAtZero: true, fontColor: '#94a3b8', callback: formatMoneyShort },
                        gridLines: { color: 'rgba(15, 23, 42, 0.07)', drawBorder: false, zeroLineColor: 'rgba(15, 23, 42, 0.10)' }
                    }]
                },
                tooltips: {
                    backgroundColor: 'rgba(15, 23, 42, 0.92)',
                    titleFontColor: '#fff',
                    bodyFontColor: '#fff',
                    cornerRadius: 8,
                    xPadding: 12,
                    yPadding: 10,
                    callbacks: {
                        title: function (items, chartData) {
                            var item = items[0];
                            var point = chartData.datasets[item.datasetIndex].data[item.index];
                            return point.colegio + (point.seleccionado ? ' (filtro actual)' : '');
                        },
                        label: function (item, chartData) {
                            var point = chartData.datasets[item.datasetIndex].data[item.index];
                            return [point.x + ' equipos', formatMoney(point.y), 'Click para filtrar por este colegio'];
                        }
                    }
                }
            }
        });

        chartInstances[id] = chart;
    }

    // Bar agrupado "colegio vs promedio del sistema". Solo cantidades
    // (mismo eje/unidad); el valor monetario se muestra aparte en tarjetas
    // .inv-card-info para no mezclar dos escalas en un mismo eje Y.
    function createComparativaChart(id, comparativa) {
        if (typeof Chart === 'undefined' || !comparativa) { return; }
        var el = document.getElementById(id);
        if (!el) { return; }
        if (chartInstances[id]) { chartInstances[id].destroy(); }

        chartInstances[id] = new Chart(el, {
            type: 'bar',
            data: {
                labels: comparativa.categorias,
                datasets: [
                    {
                        label: comparativa.nombre,
                        data: comparativa.colegio,
                        backgroundColor: '#2a78d6',
                        barPercentage: 0.6,
                        categoryPercentage: 0.6
                    },
                    {
                        label: 'Promedio sistema',
                        data: comparativa.promedio,
                        backgroundColor: '#008300',
                        barPercentage: 0.6,
                        categoryPercentage: 0.6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: { boxWidth: 10, padding: 14, fontColor: '#64748b', fontSize: 12 }
                },
                scales: {
                    yAxes: [{
                        ticks: { beginAtZero: true, precision: 0, fontColor: '#94a3b8', padding: 8 },
                        gridLines: { color: 'rgba(15, 23, 42, 0.07)', drawBorder: false, zeroLineColor: 'rgba(15, 23, 42, 0.10)' }
                    }],
                    xAxes: [{
                        ticks: { fontColor: '#94a3b8', padding: 8, autoSkip: false, maxRotation: 0 },
                        gridLines: { display: false }
                    }]
                },
                tooltips: {
                    backgroundColor: 'rgba(15, 23, 42, 0.92)',
                    titleFontColor: '#fff',
                    bodyFontColor: '#fff',
                    cornerRadius: 8,
                    xPadding: 12,
                    yPadding: 10
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindDashboardFilters();
        bindAlertModal();
        createChart('chartEstados', 'doughnut', data.estados, colegioColorPalette((data.estados && data.estados.labels || []).length));
        createChart('chartTipos', 'bar', data.tipos);
        createChart('chartRam', 'doughnut', data.ram);
        createChart('chartSistemas', 'bar', data.sistemas);
        createScatterChart('chartScatterColegios', data.scatterColegios);
        createComparativaChart('chartComparativaColegio', data.comparativa);
    });

    function bindDashboardFilters() {
        document.querySelectorAll('[data-dashboard-filter]').forEach(function (select) {
            select.addEventListener('change', function () {
                redirectDashboard();
            });
        });
    }

    function redirectDashboard() {
        var colegioSelect = document.getElementById('id_colegio');
        var tipoSelect = document.getElementById('tipo_equipo');
        var idColegio = colegioSelect ? (colegioSelect.value || '0') : (config.idColegio || '0');
        var tipoEquipo = tipoSelect ? (tipoSelect.value || 'pc') : (config.tipoEquipo || 'pc');

        window.location.href = 'dashboard.php?id_colegio=' + encodeURIComponent(idColegio) +
            '&tipo_equipo=' + encodeURIComponent(tipoEquipo || 'pc');
    }

    function bindAlertModal() {
        var modalEl = document.getElementById('modalAlertaInventario');
        var sendBtn = document.getElementById('btnEnviarAlertaInventario');
        var selectAll = document.getElementById('invMailSelectAll');

        if (window.bootstrap && modalEl) {
            modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        }

        document.querySelectorAll('.inv-dashboard-alert-action').forEach(function (btn) {
            btn.addEventListener('click', function () {
                abrirModalAlerta(this.getAttribute('data-alert-type'), parseInt(this.getAttribute('data-alert-total'), 10) || 0);
            });
        });

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.inv-mail-recipient-check').forEach(function (chk) {
                    chk.checked = selectAll.checked;
                });
            });
        }

        if (sendBtn) {
            sendBtn.addEventListener('click', enviarAlerta);
        }
    }

    function abrirModalAlerta(tipo, total) {
        if (!tipo || !config.alertas || !config.alertas[tipo]) { return; }
        if (!config.idColegio || parseInt(config.idColegio, 10) <= 0) {
            mostrarMensaje('Seleccione un colegio específico para enviar correos a sus responsables.', 'warning');
            return;
        }

        var meta = config.alertas[tipo];
        alertaActual = { tipo: tipo, total: total };

        setText('modalAlertaInventarioLabel', meta.titulo || 'Enviar alerta de inventario');
        setValue('invMailSubject', meta.asunto || '');
        setValue('invMailMessage', (meta.mensaje || '').replace('{total}', total));
        setAlertBox('', '');
        setRecipientsLoading();

        if (modalInstance) {
            modalInstance.show();
        }

        fetchResponsables(tipo, total);
    }

    function fetchResponsables(tipo, total) {
        var formData = new FormData();
        formData.append('accion', 'responsables');
        formData.append('id_colegio', config.idColegio || 0);
        formData.append('tipo_alerta', tipo);
        formData.append('total_alerta', total);

        fetch(config.endpointAlertas || 'ajax/enviar_alerta_dashboard.php', {
            method: 'POST',
            body: formData
        })
            .then(parseJsonResponse)
            .then(function (data) {
                if (!data.ok) {
                    setAlertBox(data.mensaje || 'No fue posible cargar responsables.', 'warning');
                    renderResponsables([]);
                    return;
                }
                renderResponsables(data.responsables || []);
            })
            .catch(function (err) {
                setAlertBox('No fue posible cargar responsables: ' + err.message, 'danger');
                renderResponsables([]);
            });
    }

    function renderResponsables(items) {
        var list = document.getElementById('invMailRecipientsList');
        var selectAll = document.getElementById('invMailSelectAll');
        if (!list) { return; }
        if (selectAll) {
            selectAll.checked = false;
            selectAll.disabled = !items.length;
        }
        if (!items.length) {
            list.innerHTML = '<div class="text-muted small">No hay responsables con correo válido para este colegio.</div>';
            return;
        }
        list.innerHTML = items.map(function (item) {
            return '<label class="form-check inv-mail-recipient-item">' +
                '<input class="form-check-input inv-mail-recipient-check" type="checkbox" value="' + escapeHtml(item.id_usuario) + '">' +
                '<span class="form-check-label"><strong>' + escapeHtml(item.nombre) + '</strong><small>' + escapeHtml(item.email) + '</small></span>' +
                '</label>';
        }).join('');
    }

    function enviarAlerta() {
        if (!alertaActual) { return; }
        var destinatarios = Array.prototype.slice.call(document.querySelectorAll('.inv-mail-recipient-check:checked'))
            .map(function (chk) { return chk.value; });
        if (!destinatarios.length) {
            setAlertBox('Seleccione al menos un responsable.', 'warning');
            return;
        }

        var formData = new FormData();
        formData.append('accion', 'enviar');
        formData.append('id_colegio', config.idColegio || 0);
        formData.append('tipo_alerta', alertaActual.tipo);
        formData.append('total_alerta', alertaActual.total);
        formData.append('asunto', getValue('invMailSubject'));
        formData.append('mensaje', getValue('invMailMessage'));
        destinatarios.forEach(function (id) {
            formData.append('destinatarios[]', id);
        });

        setSending(true);
        fetch(config.endpointAlertas || 'ajax/enviar_alerta_dashboard.php', {
            method: 'POST',
            body: formData
        })
            .then(parseJsonResponse)
            .then(function (data) {
                setSending(false);
                if (!data.ok) {
                    setAlertBox(data.mensaje || 'No fue posible enviar el correo.', 'danger');
                    return;
                }
                setAlertBox(data.mensaje || 'Correo enviado correctamente.', 'success');
            })
            .catch(function (err) {
                setSending(false);
                setAlertBox('No fue posible enviar el correo: ' + err.message, 'danger');
            });
    }

    function parseJsonResponse(res) {
        return res.json().then(function (data) {
            if (!res.ok) {
                throw new Error(data.mensaje || 'Error HTTP ' + res.status);
            }
            return data;
        });
    }

    function setRecipientsLoading() {
        var list = document.getElementById('invMailRecipientsList');
        if (list) {
            list.innerHTML = '<div class="text-muted small">Cargando responsables...</div>';
        }
        var selectAll = document.getElementById('invMailSelectAll');
        if (selectAll) {
            selectAll.checked = false;
            selectAll.disabled = true;
        }
    }

    function setSending(isSending) {
        var btn = document.getElementById('btnEnviarAlertaInventario');
        var spinner = document.getElementById('invMailSpinner');
        if (btn) { btn.disabled = isSending; }
        if (spinner) { spinner.classList.toggle('d-none', !isSending); }
    }

    function setAlertBox(message, type) {
        var box = document.getElementById('invMailAlertBox');
        if (!box) { return; }
        box.className = 'alert d-none mb-3';
        box.textContent = '';
        if (!message) { return; }
        box.classList.remove('d-none');
        box.classList.add('alert-' + (type || 'info'));
        box.textContent = message;
    }

    function mostrarMensaje(message, icon) {
        if (window.Swal) {
            Swal.fire({ icon: icon || 'info', title: 'Inventario', text: message });
        } else {
            alert(message);
        }
    }

    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) { el.textContent = value || ''; }
    }

    function setValue(id, value) {
        var el = document.getElementById(id);
        if (el) { el.value = value || ''; }
    }

    function getValue(id) {
        var el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
}());
