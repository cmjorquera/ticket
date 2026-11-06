/* Dashboard Inventario PC: gráficos con Chart.js a partir de window.DI_DATOS. */
(function () {
    'use strict';
    const D = window.DI_DATOS;
    if (!D || typeof Chart === 'undefined') { return; }
    const AZUL = '#1e4e8c', CELESTE = '#0ea5e9', VERDE = '#16a34a', AMBAR = '#f59e0b', ROJO = '#dc2626', GRIS = '#94a3b8';
    Chart.defaults.plugins.legend.labels.boxWidth = 12;
    const peso = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 });
    const ejeEntero = { beginAtZero: true, ticks: { precision: 0 } };
    const base = { responsive: true, maintainAspectRatio: false };
    const pluginVacio = { id: 'vacio', afterDraw(chart) {
        if (chart.data.datasets.some(d => d.data.some(v => +v > 0))) { return; }
        const c = chart.ctx; c.save(); c.fillStyle = '#64748b'; c.textAlign = 'center'; c.font = '13px sans-serif';
        c.fillText('Sin datos para mostrar', chart.width / 2, chart.height / 2); c.restore();
    } };
    const crear = (id, cfg) => { const el = document.getElementById(id); return el ? new Chart(el, { ...cfg, plugins: [pluginVacio] }) : null; };

    crear('gColegios', { type: 'bar', data: { labels: D.colegios, datasets: [
        { label: 'PC', data: D.pc, backgroundColor: AZUL, borderRadius: 4 },
        { label: 'Monitores', data: D.monitores, backgroundColor: CELESTE, borderRadius: 4 }
    ] }, options: { ...base, scales: { y: ejeEntero } } });

    // Color fijo por estado (sin tildes ni mayúsculas para comparar)
    const norm = t => String(t).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    const colorEstado = t => { const n = norm(t);
        if (n.startsWith('activ')) { return VERDE; } if (n.startsWith('repar')) { return AMBAR; }
        if (n.startsWith('baja')) { return ROJO; } if (n.startsWith('prest')) { return CELESTE; }
        if (n.startsWith('bodeg')) { return GRIS; } return '#7c3aed'; };
    crear('gEstados', { type: 'doughnut', data: { labels: D.estados, datasets: [{ data: D.estadosValores, backgroundColor: D.estados.map(colorEstado), borderColor: '#fff', borderWidth: 2 }] },
        options: { ...base, cutout: '60%', plugins: { legend: { position: 'right' } } } });

    crear('gAntiguedad', { type: 'bar', data: { labels: D.antiguedad, datasets: [{ label: 'Activos', data: D.antiguedadValores, backgroundColor: [VERDE, AMBAR, ROJO, GRIS], borderRadius: 4 }] },
        options: { ...base, plugins: { legend: { display: false } }, scales: { y: ejeEntero } } });

    crear('gValor', { type: 'bar', data: { labels: D.colegios, datasets: [{ label: 'Valor', data: D.valores, backgroundColor: AZUL, borderRadius: 4 }] },
        options: { ...base, indexAxis: 'y', plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ' ' + peso.format(c.parsed.x) } } },
            scales: { x: { beginAtZero: true, ticks: { callback: v => peso.format(v) } } } } });

    crear('gUbicaciones', { type: 'bar', data: { labels: D.ubicaciones, datasets: [{ label: 'Activos', data: D.ubicacionesValores, backgroundColor: CELESTE, borderRadius: 4 }] },
        options: { ...base, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: ejeEntero } } });
}());
