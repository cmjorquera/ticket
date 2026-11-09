/* Mi cuenta · Documentos: carpetas, búsqueda y descarga. */
(function () {
    'use strict';
    const D = window.MC_DOCS;
    if (!D || !D.lista || !D.lista.length) { return; }
    const { esc, fecha, tamano } = window.MC;
    const $ = s => document.querySelector(s);
    const CARPETAS = {
        todos:        { nombre: 'Todos', icono: 'bi-grid' },
        contratos:    { nombre: 'Contratos', icono: 'bi-file-earmark-text' },
        anexos:       { nombre: 'Anexos', icono: 'bi-files' },
        certificados: { nombre: 'Certificados', icono: 'bi-patch-check' },
        personales:   { nombre: 'Personales', icono: 'bi-person-badge' },
        otros:        { nombre: 'Otros', icono: 'bi-folder' },
    };
    const ICONOS = { pdf: 'bi-file-earmark-pdf', doc: 'bi-file-earmark-word', docx: 'bi-file-earmark-word', xls: 'bi-file-earmark-excel', xlsx: 'bi-file-earmark-excel', jpg: 'bi-file-earmark-image', jpeg: 'bi-file-earmark-image', png: 'bi-file-earmark-image' };
    let carpeta = 'todos';
    let texto = '';

    const norm = t => String(t).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    // Nombre legible: sin prefijo APELLIDO_RUT_, guiones bajos → espacios
    const legible = n => n.replace(/\.[a-z0-9]+$/i, '').replace(/^[A-ZÑ]+_(\d{7,8}-[\dkK]_\d+_)?/, '').replace(/_/g, ' ').trim() || n;

    function pintarCarpetas() {
        const cuenta = { todos: D.lista.length };
        D.lista.forEach(d => { cuenta[d.categoria] = (cuenta[d.categoria] || 0) + 1; });
        $('#mcCarpetas').innerHTML = Object.entries(CARPETAS).filter(([k]) => cuenta[k]).map(([k, c]) => `
            <button type="button" role="tab" class="mc-carpeta${k === carpeta ? ' is-activa' : ''}" data-carpeta="${k}" aria-selected="${k === carpeta}">
                <span class="mc-carpeta__ico mc-carpeta__ico--${k}"><i class="bi ${c.icono}"></i></span>
                <span class="mc-carpeta__txt"><strong>${esc(c.nombre)}</strong><small>${cuenta[k]} ${cuenta[k] === 1 ? 'archivo' : 'archivos'}</small></span>
            </button>`).join('');
    }

    function pintarLista() {
        const q = norm(texto);
        const v = D.lista.filter(d => (carpeta === 'todos' || d.categoria === carpeta) && (!q || norm(d.nombre).includes(q)));
        $('#docTitulo').innerHTML = `<i class="bi ${CARPETAS[carpeta].icono}"></i> ${carpeta === 'todos' ? 'Todos los documentos' : esc(CARPETAS[carpeta].nombre)}`;
        $('#docSub').textContent = v.length + (v.length === 1 ? ' documento' : ' documentos') + (q ? ' para “' + texto + '”' : '');
        $('#listaDocs').innerHTML = v.map(d => {
            const url = 'descargar.php?tipo=documento&id=' + encodeURIComponent(d.id);
            const ext = (d.extension || '').toLowerCase();
            return `<li class="mc-doc">
                <span class="mc-doc__ico mc-doc__ico--${esc(ext)}"><i class="bi ${ICONOS[ext] || 'bi-file-earmark'}"></i></span>
                <div class="mc-doc__txt">
                    <a href="${url}&ver=1" target="_blank" rel="noopener" title="${esc(d.nombre)}">${esc(legible(d.nombre))}</a>
                    <small><span class="mc-tag mc-tag--${esc(d.categoria)}">${esc((CARPETAS[d.categoria] || CARPETAS.otros).nombre)}</span> ${esc(ext.toUpperCase())} · ${esc(tamano(d.tamano))} · ${esc(fecha(d.fecha))}</small>
                </div>
                <div class="mc-doc__acc">
                    ${ext === 'pdf' ? `<a class="btn-icon" href="${url}&ver=1" target="_blank" rel="noopener" title="Ver" aria-label="Ver ${esc(d.nombre)}"><i class="bi bi-eye"></i></a>` : ''}
                    <a class="btn-icon" href="${url}" title="Descargar" aria-label="Descargar ${esc(d.nombre)}"><i class="bi bi-download"></i></a>
                </div>
            </li>`;
        }).join('') || '<li class="mc-vacio">No hay documentos que coincidan.</li>';
    }

    $('#mcCarpetas').addEventListener('click', e => {
        const b = e.target.closest('[data-carpeta]');
        if (!b) { return; }
        carpeta = b.dataset.carpeta;
        pintarCarpetas();
        pintarLista();
    });
    $('#buscarDoc').addEventListener('input', e => { texto = e.target.value.trim(); pintarLista(); });

    pintarCarpetas();
    pintarLista();
})();
