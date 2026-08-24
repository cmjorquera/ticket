(function () {
    'use strict';

    var data = window.inventarioDashboardCharts || {};
    var config = window.inventarioDashboardConfig || {};
    var modalInstance = null;
    var alertaActual = null;
    var chartInstances = {};

    function chartColors(count) {
        var palette = ['#0d6efd', '#3a9131', '#f2b705', '#dc3545', '#20c997', '#6f42c1', '#0dcaf0', '#6c757d'];
        var colors = [];
        for (var i = 0; i < count; i += 1) {
            colors.push(palette[i % palette.length]);
        }
        return colors;
    }

    function createChart(id, type, source) {
        if (typeof Chart === 'undefined' || !source || !source.labels || !source.labels.length) {
            return;
        }
        var el = document.getElementById(id);
        if (!el) {
            return;
        }

        var isBar = type === 'bar';
        var colors = isBar ? source.labels.map(function () { return '#3a9131'; }) : chartColors(source.labels.length);
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

    document.addEventListener('DOMContentLoaded', function () {
        bindDashboardFilters();
        bindAlertModal();
        createChart('chartEstados', 'doughnut', data.estados);
        createChart('chartTipos', 'bar', data.tipos);
        createChart('chartRam', 'doughnut', data.ram);
        createChart('chartSistemas', 'bar', data.sistemas);
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
