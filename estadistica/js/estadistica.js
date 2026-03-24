(function () {
    const dashboardData = window.estadisticaDashboardData || {};

    function createChart(canvasId, config) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || typeof Chart === 'undefined') {
            return null;
        }

        return new Chart(canvas, config);
    }

    const estadoLabels = (dashboardData.estados && dashboardData.estados.labels) || [];
    const estadoValues = (dashboardData.estados && dashboardData.estados.values) || [];
    const estadoColors = ['#1f5fae', '#7c90b0', '#d8a031', '#2c9c70', '#2b3853', '#77b6ea'];

    createChart('chartEstados', {
        type: 'doughnut',
        data: {
            labels: estadoLabels,
            datasets: [{
                data: estadoValues,
                backgroundColor: estadoColors,
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.label + ': ' + context.formattedValue;
                        }
                    }
                }
            }
        }
    });

    const categorias = Array.isArray(dashboardData.categorias) ? dashboardData.categorias : [];
    createChart('chartCategorias', {
        type: 'bar',
        data: {
            labels: categorias.map(function (item) { return item.label; }),
            datasets: [{
                label: 'Tickets',
                data: categorias.map(function (item) { return item.total; }),
                borderRadius: 10,
                backgroundColor: ['#1f5fae', '#2b73bf', '#4386c8', '#5d9dd0', '#77b6ea', '#94c8ef']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(31, 42, 68, 0.08)'
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    const meses = dashboardData.mensual || {};
    const labelsMensuales = Object.keys(meses);
    createChart('chartMensual', {
        type: 'line',
        data: {
            labels: labelsMensuales,
            datasets: [
                {
                    label: 'Recibidos',
                    data: labelsMensuales.map(function (mes) { return (meses[mes] && meses[mes].Recibido) || 0; }),
                    borderColor: '#1f5fae',
                    backgroundColor: 'rgba(31, 95, 174, 0.12)',
                    fill: true,
                    tension: 0.35
                },
                {
                    label: 'En proceso',
                    data: labelsMensuales.map(function (mes) { return (meses[mes] && meses[mes]['En Proceso']) || 0; }),
                    borderColor: '#d8a031',
                    backgroundColor: 'rgba(216, 160, 49, 0.10)',
                    fill: true,
                    tension: 0.35
                },
                {
                    label: 'Terminados',
                    data: labelsMensuales.map(function (mes) { return (meses[mes] && meses[mes].Terminado) || 0; }),
                    borderColor: '#2c9c70',
                    backgroundColor: 'rgba(44, 156, 112, 0.10)',
                    fill: true,
                    tension: 0.35
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(31, 42, 68, 0.08)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    const ciclo = Array.isArray(dashboardData.ciclo) ? dashboardData.ciclo : [];
    createChart('chartCiclo', {
        type: 'bar',
        data: {
            labels: ciclo.map(function (item) { return item.label; }),
            datasets: [{
                label: 'Horas promedio',
                data: ciclo.map(function (item) { return item.value; }),
                backgroundColor: ['#1f5fae', '#4d85c4', '#77b6ea', '#2c9c70'],
                borderRadius: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(31, 42, 68, 0.08)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    function loadTicketLifeContent(content) {
        if (!content || content.dataset.loaded === 'true') {
            return Promise.resolve();
        }

        const url = content.getAttribute('data-vida-ticket-url');
        if (!url) {
            return Promise.resolve();
        }

        content.innerHTML = '<div class="ticket-life-loading"><div class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div><span>Cargando vida del ticket...</span></div>';

        return fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('No se pudo cargar la vida del ticket.');
                }
                return response.text();
            })
            .then(function (html) {
                content.innerHTML = html;
                content.dataset.loaded = 'true';
            })
            .catch(function () {
                content.innerHTML = '<div class="ticket-life-error">No fue posible cargar la vida del ticket.</div>';
            });
    }

    const searchInput = document.getElementById('ticketLifeSearch');
    const colegioFilter = document.getElementById('ticketLifeFilterColegio');
    const categoriaFilter = document.getElementById('ticketLifeFilterCategoria');
    const estadoFilter = document.getElementById('ticketLifeFilterEstado');
    const noResults = document.getElementById('ticketLifeNoResults');
    const tableElement = document.getElementById('ticketLifeTable');
    let tablaVida = null;

    function estadoMatches(filterValue, estadoValue) {
        if (!filterValue) {
            return true;
        }

        if (filterValue === 'activos') {
            return estadoValue.indexOf('cerr') === -1 && estadoValue.indexOf('termin') === -1;
        }

        return estadoValue === filterValue.toLowerCase();
    }

    function toggleTicketRow(rowNode) {
        if (!tablaVida || !rowNode) {
            return;
        }

        const rowApi = tablaVida.row(rowNode);
        const ticketId = rowNode.getAttribute('data-ticket-id');
        if (!ticketId) {
            return;
        }

        const alreadyOpen = rowApi.child.isShown();

        tablaVida.rows().every(function () {
            if (this.child.isShown()) {
                this.child.hide();
                this.node().classList.remove('is-open');
            }
        });

        if (alreadyOpen) {
            return;
        }

        const content = document.createElement('div');
        content.className = 'ticket-life-content';
        content.dataset.ticketId = ticketId;
        content.setAttribute('data-vida-ticket-url', 'estadistica/vida_ticket.php?id_ticket=' + ticketId);
        rowApi.child(content).show();
        rowNode.classList.add('is-open');
        loadTicketLifeContent(content);
    }

    function updateNoResults() {
        if (!noResults || !tablaVida) {
            return;
        }

        noResults.classList.toggle('d-none', tablaVida.rows({ filter: 'applied' }).count() > 0);
    }

    if (window.jQuery && tableElement && window.jQuery.fn && window.jQuery.fn.dataTable) {
        if (!window.ticketLifeFiltersSearchRegistered) {
            window.jQuery.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (!settings.nTable || settings.nTable.id !== 'ticketLifeTable') {
                    return true;
                }

                const api = new window.jQuery.fn.dataTable.Api(settings);
                const rowNode = api.row(dataIndex).node();
                if (!rowNode) {
                    return true;
                }

                const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
                const colegio = colegioFilter ? colegioFilter.value.trim().toLowerCase() : '';
                const categoria = categoriaFilter ? categoriaFilter.value.trim().toLowerCase() : '';
                const estado = estadoFilter ? estadoFilter.value.trim().toLowerCase() : '';
                const haystack = rowNode.getAttribute('data-ticket-search') || '';
                const rowColegio = rowNode.getAttribute('data-colegio') || '';
                const rowCategoria = rowNode.getAttribute('data-categoria') || '';
                const rowEstado = rowNode.getAttribute('data-estado') || '';

                return haystack.indexOf(query) !== -1 &&
                    (!colegio || rowColegio === colegio) &&
                    (!categoria || rowCategoria === categoria) &&
                    estadoMatches(estado, rowEstado);
            });

            window.ticketLifeFiltersSearchRegistered = true;
        }

        tablaVida = window.jQuery('#ticketLifeTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            responsive: true,
            pageLength: 10,
            paging: true,
            pagingType: 'simple_numbers',
            info: true,
            searching: false,
            order: [[0, 'desc']]
        });

        window.jQuery('#ticketLifeTable tbody').on('click', 'tr.ticket-life-row', function (event) {
            if (event.target.closest('.ticket-life-row__toggle')) {
                return;
            }
            toggleTicketRow(this);
        });

        window.jQuery('#ticketLifeTable tbody').on('click', '.ticket-life-row__toggle', function (event) {
            event.preventDefault();
            event.stopPropagation();
            const rowNode = this.closest('tr.ticket-life-row');
            toggleTicketRow(rowNode);
        });

        [searchInput, colegioFilter, categoriaFilter, estadoFilter].forEach(function (input) {
            if (input) {
                input.addEventListener('input', function () {
                    tablaVida.draw();
                    updateNoResults();
                });
                input.addEventListener('change', function () {
                    tablaVida.draw();
                    updateNoResults();
                });
            }
        });

        tablaVida.on('draw', function () {
            updateNoResults();
        });

        tablaVida.draw();
        updateNoResults();
    }
})();
