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

    function toggleTicketRow(row) {
        if (!row) {
            return;
        }

        const ticketId = row.getAttribute('data-ticket-id');
        const detailRow = document.querySelector('.ticket-life-detail-row[data-detail-for="' + ticketId + '"]');
        if (!detailRow) {
            return;
        }

        const isOpen = !detailRow.classList.contains('d-none');
        document.querySelectorAll('.ticket-life-detail-row').forEach(function (otherDetail) {
            otherDetail.classList.add('d-none');
        });
        document.querySelectorAll('.ticket-life-row.is-open').forEach(function (otherRow) {
            otherRow.classList.remove('is-open');
        });

        if (isOpen) {
            return;
        }

        row.classList.add('is-open');
        detailRow.classList.remove('d-none');
        loadTicketLifeContent(detailRow.querySelector('.ticket-life-content'));
    }

    document.querySelectorAll('.ticket-life-row').forEach(function (row) {
        row.addEventListener('click', function (event) {
            if (event.target.closest('.ticket-life-row__toggle')) {
                return;
            }
            toggleTicketRow(row);
        });
    });

    document.querySelectorAll('.ticket-life-row__toggle').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            const row = document.querySelector('.ticket-life-row[data-ticket-id="' + button.getAttribute('data-ticket-id') + '"]');
            toggleTicketRow(row);
        });
    });

    const searchInput = document.getElementById('ticketLifeSearch');
    const colegioFilter = document.getElementById('ticketLifeFilterColegio');
    const categoriaFilter = document.getElementById('ticketLifeFilterCategoria');
    const estadoFilter = document.getElementById('ticketLifeFilterEstado');
    const noResults = document.getElementById('ticketLifeNoResults');

    function estadoMatches(filterValue, estadoValue) {
        if (!filterValue) {
            return true;
        }

        if (filterValue === 'activos') {
            return estadoValue.indexOf('cerr') === -1 && estadoValue.indexOf('termin') === -1;
        }

        return estadoValue === filterValue.toLowerCase();
    }

    function applyTicketFilters() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const colegio = colegioFilter ? colegioFilter.value.trim().toLowerCase() : '';
        const categoria = categoriaFilter ? categoriaFilter.value.trim().toLowerCase() : '';
        const estado = estadoFilter ? estadoFilter.value.trim().toLowerCase() : '';
        let visibleCount = 0;

        document.querySelectorAll('.ticket-life-row').forEach(function (row) {
            const haystack = row.getAttribute('data-ticket-search') || '';
            const rowColegio = row.getAttribute('data-colegio') || '';
            const rowCategoria = row.getAttribute('data-categoria') || '';
            const rowEstado = row.getAttribute('data-estado') || '';
            const match = haystack.indexOf(query) !== -1 &&
                (!colegio || rowColegio === colegio) &&
                (!categoria || rowCategoria === categoria) &&
                estadoMatches(estado, rowEstado);

            row.style.display = match ? '' : 'none';
            const detailRow = document.querySelector('.ticket-life-detail-row[data-detail-for="' + row.getAttribute('data-ticket-id') + '"]');
            if (detailRow) {
                detailRow.style.display = match ? '' : 'none';
                if (!match) {
                    detailRow.classList.add('d-none');
                    row.classList.remove('is-open');
                }
            }

            if (match) {
                visibleCount++;
            }
        });

        if (noResults) {
            noResults.classList.toggle('d-none', visibleCount > 0);
        }
    }

    [searchInput, colegioFilter, categoriaFilter, estadoFilter].forEach(function (input) {
        if (input) {
            input.addEventListener('input', applyTicketFilters);
            input.addEventListener('change', applyTicketFilters);
        }
    });

    applyTicketFilters();
})();
