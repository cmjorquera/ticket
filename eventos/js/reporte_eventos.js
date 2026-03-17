(function () {
    function createChart(id, config) {
        const ctx = document.getElementById(id);
        if (!ctx) {
            return null;
        }
        return new Chart(ctx, config);
    }

    async function fetchData() {
        const response = await fetch(EventosConfig.urls.reporte);
        return response.json();
    }

    function colors() {
        return ['#0f4c81', '#0ea5e9', '#16a34a', '#f97316', '#7c3aed', '#ef4444', '#14b8a6'];
    }

    window.EventosReportes = {
        async init() {
            const data = await fetchData();
            if (!data.ok) {
                return;
            }

            createChart('chart-eventos-mes', {
                type: 'bar',
                data: {
                    labels: data.por_mes.labels,
                    datasets: [{ label: `Eventos ${data.year}`, data: data.por_mes.values, backgroundColor: '#0f4c81', borderRadius: 8 }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            createChart('chart-eventos-tipo', {
                type: 'doughnut',
                data: { labels: data.por_tipo.labels, datasets: [{ data: data.por_tipo.values, backgroundColor: colors() }] },
                options: { responsive: true, maintainAspectRatio: false }
            });

            createChart('chart-eventos-estado', {
                type: 'bar',
                data: { labels: data.por_estado.labels, datasets: [{ label: 'Estados', data: data.por_estado.values, backgroundColor: ['#0ea5e9', '#ef4444', '#16a34a'] }] },
                options: { responsive: true, maintainAspectRatio: false }
            });

            createChart('chart-top-responsables', {
                type: 'bar',
                data: { labels: data.top_responsables.labels, datasets: [{ label: 'Eventos asignados', data: data.top_responsables.values, backgroundColor: '#14b8a6', borderRadius: 8 }] },
                options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false }
            });

            createChart('chart-requerimientos', {
                type: 'doughnut',
                data: { labels: data.requerimientos.labels, datasets: [{ data: data.requerimientos.values, backgroundColor: ['#0f4c81', '#f97316', '#7c3aed'] }] },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    };
})();
