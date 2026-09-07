class Paginacion {
  constructor({ contenedor, totalItems, porPagina = 10, onCambio }) {
    this.contenedor = document.getElementById(contenedor);
    this.totalItems = Math.max(0, Number(totalItems) || 0);
    this.porPagina = Math.max(1, Number(porPagina) || 10);
    this.paginaActual = 1;
    this.onCambio = typeof onCambio === 'function' ? onCambio : () => {};
    this.totalPaginas = Math.ceil(this.totalItems / this.porPagina);
  }

  actualizar(totalItems) {
    this.totalItems = Math.max(0, Number(totalItems) || 0);
    this.totalPaginas = Math.ceil(this.totalItems / this.porPagina);
    this.paginaActual = 1;
    this.render();
  }

  ir(pagina) {
    if (pagina < 1 || pagina > this.totalPaginas) return;
    this.paginaActual = pagina;
    this.render();
    this.onCambio(pagina, this.porPagina);
  }

  render() {
    if (!this.contenedor) return;

    const p = this.paginaActual;
    const t = this.totalPaginas;
    const inicio = this.totalItems ? ((p - 1) * this.porPagina) + 1 : 0;
    const fin = Math.min(p * this.porPagina, this.totalItems);
    let html = `<div class="pag-info">Mostrando ${inicio}–${fin} de ${this.totalItems}</div>`;
    html += '<div class="pag-botones">';
    html += `<button type="button" class="pag-btn" aria-label="Página anterior" ${p === 1 || t === 0 ? 'disabled' : ''} onclick="this.closest('.paginacion')._pag.ir(${p - 1})">‹</button>`;

    for (let i = 1; i <= t; i += 1) {
      if (i === 1 || i === t || (i >= p - 1 && i <= p + 1)) {
        html += `<button type="button" class="pag-btn ${i === p ? 'active' : ''}" ${i === p ? 'aria-current="page"' : ''} onclick="this.closest('.paginacion')._pag.ir(${i})">${i}</button>`;
      } else if (i === p - 2 || i === p + 2) {
        html += '<span class="pag-dots">…</span>';
      }
    }

    html += `<button type="button" class="pag-btn" aria-label="Página siguiente" ${p === t || t === 0 ? 'disabled' : ''} onclick="this.closest('.paginacion')._pag.ir(${p + 1})">›</button>`;
    html += '</div>';
    this.contenedor.innerHTML = html;
    this.contenedor._pag = this;
  }
}

window.Paginacion = Paginacion;
