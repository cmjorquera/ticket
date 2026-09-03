// Escapa HTML para renderizar valores seguros en celdas.
function dtEsc(valor) {
  return String(valor ?? '').replace(/[&<>'"]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[m]));
}

// Genera una tabla inteligente reutilizable con búsqueda, orden, paginación y CSV.
function crearTabla(containerId, columnas, datos, opciones = {}) {
  const el = document.getElementById(containerId);
  if (!el) return;

  const estado = { busqueda: '', pagina: 1, porPagina: opciones.perPage || 5, orden: null, dir: 1 };
  const titulo = opciones.titulo || 'Registros';
  const valor = (fila, col) => fila[col.key] ?? '';
  const celda = (fila, col) => typeof col.render === 'function' ? col.render(fila) : dtEsc(valor(fila, col));

  // Descarga los registros filtrados como archivo CSV.
  function exportarCSV(filas) {
    const cabecera = columnas.map(col => `"${col.label}"`).join(',');
    const cuerpo = filas.map(fila => columnas.map(col => `"${String(valor(fila, col)).replaceAll('"', '""')}"`).join(',')).join('\n');
    const link = document.createElement('a');
    link.href = URL.createObjectURL(new Blob([cabecera + '\n' + cuerpo], { type: 'text/csv;charset=utf-8;' }));
    link.download = (opciones.csv || titulo).toLowerCase().replaceAll(' ', '-') + '.csv';
    link.click();
    URL.revokeObjectURL(link.href);
  }

  // Redibuja la tabla según el estado actual.
  function render() {
    let filas = datos.filter(fila => columnas.some(col => String(valor(fila, col)).toLowerCase().includes(estado.busqueda.toLowerCase())));
    if (estado.orden) {
      const col = columnas.find(c => c.key === estado.orden);
      filas.sort((a, b) => String(valor(a, col)).localeCompare(String(valor(b, col)), 'es', { numeric: true }) * estado.dir);
    }

    const total = filas.length;
    const paginas = Math.max(1, Math.ceil(total / estado.porPagina));
    estado.pagina = Math.min(estado.pagina, paginas);
    const visibles = filas.slice((estado.pagina - 1) * estado.porPagina, estado.pagina * estado.porPagina);

    el.innerHTML = `
        <div class="dt-toolbar">
          <div class="dt-search"><input data-dt="q" value="${dtEsc(estado.busqueda)}" placeholder="Buscar en ${dtEsc(titulo.toLowerCase())}..."></div>
          <div class="dt-actions">
            <select class="fc" data-dt="per" style="width:auto"><option ${estado.porPagina === 5 ? 'selected' : ''}>5</option><option ${estado.porPagina === 10 ? 'selected' : ''}>10</option><option ${estado.porPagina === 25 ? 'selected' : ''}>25</option></select>
            <button class="btn btn-outline btn-sm" data-dt="csv">Exportar CSV</button>
          </div>
        </div>
        <div class="table-responsive"><table class="tabla"><thead><tr>${columnas.map(col => `<th class="dt-sort" data-sort="${col.key}">${col.label}<span class="si">${estado.orden === col.key ? (estado.dir === 1 ? '▲' : '▼') : '↕'}</span></th>`).join('')}</tr></thead><tbody>${visibles.map(fila => `<tr>${columnas.map(col => `<td>${celda(fila, col)}</td>`).join('')}</tr>`).join('')}</tbody></table>${!total ? '<div id="' + containerId + '-empty"></div>' : ''}</div>
        <div class="dt-foot"><span class="tsm tm">Mostrando ${visibles.length} de ${total} registros</span><div class="pag"><button class="pg-b" data-p="prev" ${estado.pagina === 1 ? 'disabled' : ''}>‹</button>${Array.from({ length: paginas }).map((_, i) => `<button class="pg-b ${estado.pagina === i + 1 ? 'active' : ''}" data-p="${i + 1}">${i + 1}</button>`).join('')}<button class="pg-b" data-p="next" ${estado.pagina === paginas ? 'disabled' : ''}>›</button></div></div>
      `;

    if (!total && typeof showEmptyState === 'function') {
      showEmptyState(containerId + '-empty', opciones.emptyTitle || 'Sin resultados', opciones.emptyText || 'Prueba con otra búsqueda.');
    }

    el.querySelector('[data-dt="q"]').addEventListener('input', e => { estado.busqueda = e.target.value; estado.pagina = 1; render(); });
    el.querySelector('[data-dt="per"]').addEventListener('change', e => { estado.porPagina = Number(e.target.value); estado.pagina = 1; render(); });
    el.querySelector('[data-dt="csv"]').addEventListener('click', () => exportarCSV(filas));
    el.querySelectorAll('[data-sort]').forEach(th => th.addEventListener('click', () => { estado.dir = estado.orden === th.dataset.sort ? -estado.dir : 1; estado.orden = th.dataset.sort; render(); }));
    el.querySelectorAll('[data-p]').forEach(btn => btn.addEventListener('click', () => { estado.pagina = btn.dataset.p === 'prev' ? estado.pagina - 1 : btn.dataset.p === 'next' ? estado.pagina + 1 : Number(btn.dataset.p); render(); }));
  }

  render();
}
