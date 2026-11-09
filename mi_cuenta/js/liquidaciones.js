/* Mi cuenta · Liquidaciones: indicadores, gráfico, tabla y detalle. */
(function () {
    'use strict';
    const D = window.MC_LIQ;
    if (!D || !D.lista || !D.lista.length) { return; }
    const { esc, pesos, Periodo, periodo, get, post, toast, privado } = window.MC;
    const $ = s => document.querySelector(s);
    const lista = D.lista.slice().sort((a, b) => b.periodo.localeCompare(a.periodo));
    const anios = [...new Set(lista.map(l => l.periodo.slice(0, 4)))];
    let anio = 'ultimos';
    let grafico = null;

    // ---------- filtro de año
    $('#filtroAnio').innerHTML = '<option value="ultimos">Últimos 12 meses</option>' + anios.map(a => `<option value="${a}">Año ${a}</option>`).join('');
    $('#filtroAnio').addEventListener('change', e => { anio = e.target.value; pintar(); });

    const visibles = () => anio === 'ultimos' ? lista.slice(0, 12) : lista.filter(l => l.periodo.startsWith(anio));
    const suma = (arr, k) => arr.reduce((s, l) => s + (Number(l[k]) || 0), 0);
    const monto = n => `<span class="mc-monto">${esc(pesos(n))}</span>`;

    function pintarKpis(v) {
        const ultima = lista[0], previa = lista[1];
        let delta = '';
        if (previa && previa.liquido) {
            const dif = ultima.liquido - previa.liquido;
            const pct = Math.round(dif / previa.liquido * 1000) / 10;
            delta = `<span class="mc-kpi__delta ${dif >= 0 ? 'is-sube' : 'is-baja'}"><i class="bi bi-arrow-${dif >= 0 ? 'up' : 'down'}-right"></i>${Math.abs(pct).toLocaleString('es-CL')} % vs ${esc(periodo(previa.periodo).slice(0, 3) + ' ' + previa.periodo.slice(0, 4))}</span>`;
        }
        const etiqueta = anio === 'ultimos' ? 'últimos ' + v.length + ' meses' : 'año ' + anio;
        $('#mcKpis').innerHTML = `
            <article class="mc-kpi mc-kpi--principal">
                <span class="mc-kpi__ico"><i class="bi bi-wallet2"></i></span>
                <div><small>Último líquido · ${esc(periodo(ultima.periodo))}</small><strong>${monto(ultima.liquido)}</strong>${delta}</div>
            </article>
            <article class="mc-kpi">
                <span class="mc-kpi__ico mc-kpi__ico--verde"><i class="bi bi-graph-up"></i></span>
                <div><small>Líquido promedio · ${esc(etiqueta)}</small><strong>${monto(v.length ? suma(v, 'liquido') / v.length : 0)}</strong></div>
            </article>
            <article class="mc-kpi">
                <span class="mc-kpi__ico mc-kpi__ico--amarillo"><i class="bi bi-cash-coin"></i></span>
                <div><small>Total bruto · ${esc(etiqueta)}</small><strong>${monto(suma(v, 'bruto'))}</strong></div>
            </article>
            <article class="mc-kpi">
                <span class="mc-kpi__ico mc-kpi__ico--rojo"><i class="bi bi-receipt"></i></span>
                <div><small>Total descuentos · ${esc(etiqueta)}</small><strong>${monto(suma(v, 'descuentos'))}</strong></div>
            </article>`;
    }

    function pintarTabla(v) {
        $('#tablaSub').textContent = v.length + (v.length === 1 ? ' liquidación' : ' liquidaciones');
        $('#tablaLiq').innerHTML = v.map(l => `
            <tr>
                <td><button type="button" class="mc-link" data-ver="${esc(l.id)}">${esc(Periodo(l.periodo))}</button></td>
                <td class="num">${monto(l.bruto)}</td>
                <td class="num mc-rojo">${monto(-l.descuentos).replace('$ -', '− $ ')}</td>
                <td class="num"><strong>${monto(l.liquido)}</strong></td>
                <td class="num mc-ocultar-movil">${monto(l.aporte_patronal)}</td>
                <td class="num mc-ocultar-movil">${esc(String(l.dias_trabajados).replace('.', ','))}</td>
                <td class="acc">
                    <button type="button" class="btn-icon" data-ver="${esc(l.id)}" title="Ver detalle" aria-label="Ver detalle de ${esc(periodo(l.periodo))}"><i class="bi bi-list-columns-reverse"></i></button>
                    ${l.tiene_pdf ? `<a class="btn-icon" href="descargar.php?tipo=liquidacion&id=${encodeURIComponent(l.id)}&ver=1" target="_blank" rel="noopener" title="Ver PDF" aria-label="Ver PDF de ${esc(periodo(l.periodo))}"><i class="bi bi-file-earmark-pdf"></i></a>
                    <a class="btn-icon" href="descargar.php?tipo=liquidacion&id=${encodeURIComponent(l.id)}" title="Descargar" aria-label="Descargar ${esc(periodo(l.periodo))}"><i class="bi bi-download"></i></a>` : ''}
                </td>
            </tr>`).join('') || '<tr><td colspan="7" class="mc-vacio">Sin liquidaciones en este período.</td></tr>';
    }

    function pintarGrafico(v) {
        if (typeof Chart === 'undefined') { $('#graficoLiq').closest('.mc-grafico').innerHTML = '<p class="mc-vacio">No se pudo cargar el gráfico.</p>'; return; }
        const datos = v.slice().reverse();
        const css = getComputedStyle(document.documentElement);
        const azul = (css.getPropertyValue('--blue') || '#005B96').trim();
        const amarillo = (css.getPropertyValue('--yellow') || '#F4B000').trim();
        const oculto = document.documentElement.classList.contains('mc-privado');
        const cfg = {
            data: {
                labels: datos.map(l => { const t = periodo(l.periodo); return t.slice(0, 3) + ' ' + l.periodo.slice(2, 4); }),
                datasets: [
                    { type: 'bar', label: 'Líquido', data: datos.map(l => l.liquido), backgroundColor: azul, borderRadius: 6, maxBarThickness: 34, order: 2 },
                    { type: 'line', label: 'Bruto', data: datos.map(l => l.bruto), borderColor: amarillo, backgroundColor: amarillo, tension: .35, pointRadius: 3, order: 1 },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } },
                    tooltip: { callbacks: { label: c => c.dataset.label + ': ' + (document.documentElement.classList.contains('mc-privado') ? '$ •••••' : pesos(c.parsed.y)) } },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: n => oculto ? '•••' : '$ ' + Math.round(n / 1000).toLocaleString('es-CL') + ' mil' }, grid: { color: 'rgba(0,0,0,.05)' } },
                    x: { grid: { display: false } },
                },
            },
        };
        if (grafico) { grafico.destroy(); }
        grafico = new Chart($('#graficoLiq'), cfg);
    }

    function pintar() {
        const v = visibles();
        pintarKpis(v);
        pintarTabla(v);
        pintarGrafico(v);
    }

    // ---------- detalle
    async function verDetalle(id) {
        const base = lista.find(l => l.id === id);
        if (!base) { return; }
        const dlg = $('#dlgLiq');
        $('#dlgLiqTitulo').textContent = 'Liquidación de ' + periodo(base.periodo);
        $('#dlgLiqSub').textContent = 'Días trabajados: ' + String(base.dias_trabajados).replace('.', ',') + (base.horas_extra ? ' · Horas extra: ' + base.horas_extra : '');
        $('#dlgLiqCuerpo').innerHTML = '<p class="mc-vacio">Cargando…</p>';
        $('#dlgLiqPie').innerHTML = base.tiene_pdf
            ? `<a class="btn btn-outline" href="descargar.php?tipo=liquidacion&id=${encodeURIComponent(id)}&ver=1" target="_blank" rel="noopener"><i class="bi bi-eye"></i> Ver PDF</a>
               <a class="btn btn-primary" href="descargar.php?tipo=liquidacion&id=${encodeURIComponent(id)}"><i class="bi bi-download"></i> Descargar PDF</a>`
            : '';
        if (!dlg.open) { dlg.showModal(); }
        try {
            const l = (await get({ accion: 'liquidacion', id })).liquidacion;
            const filas = (arr, signo) => arr.map(([c, m, imp]) => `<li><span>${esc(c)}${imp === false ? ' <small title="No imponible">NI</small>' : ''}</span>${signo ? `<span class="mc-monto">− ${esc(pesos(m))}</span>` : monto(m)}</li>`).join('');
            const pctDesc = l.bruto ? Math.round(l.descuentos / l.bruto * 100) : 0;
            $('#dlgLiqCuerpo').innerHTML = `
                <div class="mc-liq">
                    <section><h3><i class="bi bi-plus-circle"></i> Haberes</h3><ul>${filas(l.haberes || [])}</ul><p class="mc-liq__total"><span>Total haberes</span>${monto(l.bruto)}</p></section>
                    <section class="mc-liq--desc"><h3><i class="bi bi-dash-circle"></i> Descuentos</h3><ul>${filas(l.descuentos_detalle || [], true)}</ul><p class="mc-liq__total"><span>Total descuentos</span><span class="mc-monto">− ${esc(pesos(l.descuentos))}</span></p></section>
                </div>
                <div class="mc-liq__barra" title="Proporción del sueldo bruto"><span style="width:${100 - pctDesc}%"></span></div>
                <p class="mc-liq__leyenda"><span><i class="mc-punto mc-punto--azul"></i>Recibes el ${100 - pctDesc} %</span><span><i class="mc-punto mc-punto--rojo"></i>Descuentos ${pctDesc} %</span></p>
                <div class="mc-liq__liquido"><span>Líquido a pagar</span>${monto(l.liquido)}</div>
                ${l.aporte_patronal ? `<p class="mc-nota">Además, el empleador aporta ${monto(l.aporte_patronal)} (seguro de invalidez, cesantía y mutual); no se descuenta de tu sueldo.</p>` : ''}`;
        } catch (err) {
            $('#dlgLiqCuerpo').innerHTML = '<p class="mc-vacio">' + esc(err.message) + '</p>';
        }
    }
    document.addEventListener('click', e => {
        const b = e.target.closest('[data-ver]');
        if (b) { verDetalle(b.dataset.ver); }
    });

    // ---------- modo privado y actualizar
    privado(privado());
    $('#btnPrivado').addEventListener('click', () => { privado(!privado()); pintarGrafico(visibles()); });
    const btnAct = $('#btnActualizar');
    if (btnAct) {
        btnAct.addEventListener('click', async () => {
            btnAct.disabled = true;
            try { await post({ accion: 'actualizar' }); location.reload(); } catch (err) { toast('error', 'Error', err.message); btnAct.disabled = false; }
        });
    }

    pintar();
})();
