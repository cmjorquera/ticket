/* Estadística v2: gráficos con Chart.js a partir de window.EST_DATOS. */
(function () {
    'use strict';
    const D = window.EST_DATOS;
    if (!D || typeof Chart === 'undefined') { return; }

    const css = getComputedStyle(document.documentElement);
    const color = (v, def) => (css.getPropertyValue(v) || '').trim() || def;
    const AZUL = '#1e4e8c', VERDE = '#16a34a', AMBAR = '#f59e0b', GRIS = color('--muted', '#64748b');
    Chart.defaults.font.family = color('--font-sans', 'system-ui, sans-serif');
    Chart.defaults.color = GRIS;
    Chart.defaults.plugins.legend.labels.boxWidth = 12;

    const MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    const etiquetaMes = ym => { const [y, m] = ym.split('-'); return MESES[+m - 1] + ' ' + y.slice(2); };
    const nf = new Intl.NumberFormat('es-CL', { maximumFractionDigits: 1 });
    const pluginVacio = { id: 'vacio', afterDraw(chart) {
        if (chart.data.datasets.some(d => d.data.some(v => +v > 0))) { return; }
        const c = chart.ctx; c.save(); c.fillStyle = GRIS; c.textAlign = 'center'; c.font = '13px sans-serif';
        c.fillText('Sin datos para mostrar', chart.width / 2, chart.height / 2); c.restore();
    } };
    const ejeEntero = { beginAtZero: true, ticks: { precision: 0 } };

    // Meses continuos (rellena los meses sin tickets con 0)
    function serieMensual(n) {
        const mapa = {};
        D.mensual.forEach(f => { mapa[f.mes] = f; });
        const hoy = new Date(); const filas = [];
        for (let i = n - 1; i >= 0; i--) {
            const d = new Date(hoy.getFullYear(), hoy.getMonth() - i, 1);
            const k = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
            filas.push(mapa[k] || { mes: k, total: 0, resueltos: 0, abiertos: 0 });
        }
        return filas;
    }

    // 1) Evolución mensual
    const gMensual = new Chart(document.getElementById('gMensual'), {
        type: 'line', plugins: [pluginVacio],
        data: { labels: [], datasets: [
            { label: 'Ingresados', data: [], borderColor: AZUL, backgroundColor: 'rgba(30,78,140,.12)', fill: true, tension: .3 },
            { label: 'Resueltos', data: [], borderColor: VERDE, backgroundColor: 'transparent', tension: .3 }
        ] },
        options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, scales: { y: ejeEntero } }
    });
    // 5) Abiertos vs resueltos
    const gAbiertos = new Chart(document.getElementById('gAbiertos'), {
        type: 'bar', plugins: [pluginVacio],
        data: { labels: [], datasets: [
            { label: 'Resueltos', data: [], backgroundColor: VERDE },
            { label: 'Abiertos', data: [], backgroundColor: AMBAR }
        ] },
        options: { responsive: true, maintainAspectRatio: false, scales: { x: { stacked: true }, y: { ...ejeEntero, stacked: true } } }
    });
    function pintarMeses() {
        const filas = serieMensual(+document.getElementById('filtroMeses').value);
        const etiquetas = filas.map(f => etiquetaMes(f.mes));
        gMensual.data.labels = etiquetas;
        gMensual.data.datasets[0].data = filas.map(f => f.total);
        gMensual.data.datasets[1].data = filas.map(f => f.resueltos);
        gMensual.update();
        gAbiertos.data.labels = etiquetas;
        gAbiertos.data.datasets[0].data = filas.map(f => f.resueltos);
        gAbiertos.data.datasets[1].data = filas.map(f => f.abiertos);
        gAbiertos.update();
    }
    document.getElementById('filtroMeses').addEventListener('change', pintarMeses);
    pintarMeses();

    // 2) Técnicos (barras) / un técnico (dona por estado)
    let gTecnicos = null;
    function pintarTecnicos() {
        const id = document.getElementById('filtroTecnico').value;
        if (gTecnicos) { gTecnicos.destroy(); }
        const canvas = document.getElementById('gTecnicos');
        if (!id) {
            gTecnicos = new Chart(canvas, {
                type: 'bar', plugins: [pluginVacio],
                data: { labels: D.tecnicos.map(t => t.nombre), datasets: [{ label: 'Tickets', data: D.tecnicos.map(t => t.total), backgroundColor: AZUL, borderRadius: 4 }] },
                options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: ejeEntero } }
            });
            return;
        }
        const d = D.estadosTecnico[id] || { labels: [], values: [], colors: [] };
        gTecnicos = new Chart(canvas, {
            type: 'doughnut', plugins: [pluginVacio],
            data: { labels: d.labels, datasets: [{ data: d.values, backgroundColor: d.colors, borderColor: '#fff', borderWidth: 2 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'right' },
                tooltip: { callbacks: { label: c => ` ${c.label}: ${c.parsed} tickets` } } } }
        });
    }
    document.getElementById('filtroTecnico').addEventListener('change', pintarTecnicos);
    pintarTecnicos();

    // 3) Categorías por estado
    const gCategorias = new Chart(document.getElementById('gCategorias'), {
        type: 'bar', plugins: [pluginVacio],
        data: { labels: [], datasets: [{ label: 'Tickets', data: [], backgroundColor: AZUL, borderRadius: 4 }] },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: ejeEntero } }
    });
    function pintarCategorias() {
        const clave = document.getElementById('filtroEstadoCategoria').value;
        const serie = D.categorias[clave] || {};
        const nombres = Object.keys(serie).slice(0, 10);
        const estado = D.estados.find(e => String(e.id) === clave);
        gCategorias.data.labels = nombres;
        gCategorias.data.datasets[0].data = nombres.map(n => serie[n]);
        gCategorias.data.datasets[0].backgroundColor = estado ? estado.color : AZUL;
        gCategorias.data.datasets[0].borderColor = estado ? 'rgba(0,0,0,.25)' : AZUL;
        gCategorias.data.datasets[0].borderWidth = estado ? 1 : 0;
        gCategorias.update();
    }
    document.getElementById('filtroEstadoCategoria').addEventListener('change', pintarCategorias);
    pintarCategorias();

    // 4) Días promedio de resolución por técnico
    const conDias = D.tecnicos.filter(t => t.dias !== null).sort((a, b) => a.dias - b.dias);
    new Chart(document.getElementById('gResolucion'), {
        type: 'bar', plugins: [pluginVacio],
        data: { labels: conDias.map(t => t.nombre), datasets: [{ label: 'Días promedio', data: conDias.map(t => t.dias), backgroundColor: '#0ea5e9', borderRadius: 4 }] },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false },
            tooltip: { callbacks: { label: c => ` ${nf.format(c.parsed.x)} días` } } }, scales: { x: { beginAtZero: true } } }
    });
}());
