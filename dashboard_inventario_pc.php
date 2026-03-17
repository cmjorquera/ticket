<?php
// dashboard_inventario_pc.php
// INCLUDE desde permisos.php
// Requiere Bootstrap 5 + Bootstrap Icons + Chart.js cargados en la página principal.

$idColegioSel = isset($idColegioSel) ? (int)$idColegioSel : (int)($_GET['colegio'] ?? 0);
$colegios     = isset($colegios) && is_array($colegios) ? $colegios : [];

// Endpoints (ajusta si tu ruta real difiere)
$BASE_INV = 'js/inventario';
$EP_KPIS  = $BASE_INV . '/kpis.php';
$EP_CHART = $BASE_INV . '/chart_colegio.php';
$EP_LIST  = $BASE_INV . '/listado.php';
?>

<style>
  .inv-card{
    border-radius: 18px;
    background: #f2f2f2;
    border: 0;
    box-shadow: 0 6px 18px rgba(0,0,0,.06);
    padding: 18px 18px;
    height: 120px;
  }
  .inv-card .inv-title{ font-weight: 700; margin-bottom: 6px; }
  .inv-card .inv-value{ font-size: 20px; font-weight: 800; line-height: 1.1; }
  .inv-card .inv-sub{ font-size: 13px; color: #666; margin-top: 4px; }
  .inv-bar{ height: 6px; border-radius: 99px; background: rgba(0,0,0,.10); overflow:hidden; margin-top: 12px; }
  .inv-bar > span{ display:block; height:100%; width:0%; }
  .inv-icon{
    width: 38px; height: 38px; border-radius: 999px;
    display:flex; align-items:center; justify-content:center;
    background:#fff; box-shadow: 0 6px 14px rgba(0,0,0,.08);
  }
  .inv-panel{
    border-radius: 18px;
    border: 1px solid rgba(0,0,0,.08);
    box-shadow: 0 6px 18px rgba(0,0,0,.05);
  }
  .inv-acc .accordion-item{ border: 1px solid rgba(0,0,0,.08); border-radius: 14px; overflow: hidden; margin-bottom: 10px; }
  .inv-acc .accordion-button{ background:#fff; }
  .inv-acc .accordion-button:focus{ box-shadow:none; }
  .inv-badge-right{
    min-width: 98px;
    text-align:center;
  }
  .badgeInventario{
    position: absolute;
    top: -10px;
    right: -60px;
    background-color: red;
    color: white;
    border-radius: 50%;
    padding: 34px 88px;

    
      
  }
  /* ===== Badge derecho SOLO Inventario (evita conflicto con .badge global) ===== */
#accEquipos .inv-badge-right{
  position: static !important;      /* mata el absolute del otro css */
  right: auto !important;
  top: auto !important;

  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;

  min-width: 140px;                 /* similar a tu imagen */
  height: 44px;
  padding: 0 18px !important;

  border-radius: 999px !important;  /* pill */
  font-weight: 700;
  font-size: 13px;
  line-height: 1;

  box-shadow: 0 10px 18px rgba(0,0,0,.12);
  white-space: nowrap;
}

/* Asegura que el botón del accordion no lo “encime” */
#accEquipos .accordion-button .w-100{
  gap: 12px;
}

/* En pantallas chicas, que no se desborde */
@media (max-width: 576px){
  #accEquipos .inv-badge-right{
    min-width: 120px;
    height: 40px;
    padding: 0 14px !important;
    font-size: 12px;
  }
}

  
</style>

<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <h5 class="fw-bold mb-1">Dashboard Inventario (PC)</h5>
    <div class="text-muted small">Resumen general por colegio</div>
  </div>

  <div class="d-flex align-items-center gap-2" style="min-width:320px;">
    <label class="text-muted small mb-0">Colegio</label>
    <select id="invSelectColegio" class="form-select form-select-sm">
      <option value="0" <?= $idColegioSel === 0 ? 'selected' : '' ?>>Todos los colegios</option>
      <?php foreach ($colegios as $c): ?>
        <option value="<?= (int)$c['id_colegio'] ?>" <?= $idColegioSel === (int)$c['id_colegio'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($c['nom_colegio'], ENT_QUOTES, 'UTF-8') ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<!-- KPI cards -->
<div class="row g-3 mb-3">
  <div class="col-12 col-md-3">
    <div class="inv-card d-flex justify-content-between">
      <div>
        <div class="inv-title">Total PCs</div>
        <div class="inv-value" id="kpiTotal">0</div>
        <div class="inv-bar"><span id="barTotal" style="background:#0d6efd; width:100%"></span></div>
      </div>
      <div class="inv-icon"><i class="bi bi-pc-display text-primary"></i></div>
    </div>
  </div>

  <div class="col-12 col-md-3">
    <div class="inv-card d-flex justify-content-between">
      <div>
        <div class="inv-title">PCs Operativos</div>
        <div class="inv-value"><span id="kpiOk">0</span> <span class="small text-muted">(<span id="kpiOkPct">0</span>%)</span></div>
        <div class="inv-bar"><span id="barOk" style="background:#198754; width:0%"></span></div>
      </div>
      <div class="inv-icon"><i class="bi bi-check-circle-fill text-success"></i></div>
    </div>
  </div>

  <div class="col-12 col-md-3">
    <div class="inv-card d-flex justify-content-between">
      <div>
        <div class="inv-title">En Reparación</div>
        <div class="inv-value"><span id="kpiRep">0</span> <span class="small text-muted">(<span id="kpiRepPct">0</span>%)</span></div>
        <div class="inv-bar"><span id="barRep" style="background:#ffc107; width:0%"></span></div>
      </div>
      <div class="inv-icon"><i class="bi bi-tools text-warning"></i></div>
    </div>
  </div>

  <div class="col-12 col-md-3">
    <div class="inv-card d-flex justify-content-between">
      <div>
        <div class="inv-title">Dados de Baja</div>
        <div class="inv-value"><span id="kpiBaja">0</span> <span class="small text-muted">(<span id="kpiBajaPct">0</span>%)</span></div>
        <div class="inv-bar"><span id="barBaja" style="background:#dc3545; width:0%"></span></div>
      </div>
      <div class="inv-icon"><i class="bi bi-x-circle-fill text-danger"></i></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-7">
    <div class="p-3 inv-panel">
      <div class="fw-bold mb-1">PC por colegio</div>
      <div class="text-muted small mb-2">Totales y desglose de estado</div>
      <div style="height: 320px;">
        <canvas id="chartColegio"></canvas>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-5">
    <div class="p-3 inv-panel">
      <div class="fw-bold mb-1">Estado (selección)</div>
      <div class="text-muted small mb-2">Operativo vs Reparación vs Baja</div>
      <div style="height: 320px;">
        <canvas id="chartEstado"></canvas>
      </div>
    </div>
  </div>
















  <div class="col-12">
    <div class="card shadow-sm rounded-4 p-3 mt-2 inv-panel">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h6 class="fw-bold mb-0">Listado de Equipos</h6>
          <small class="text-muted">Resumen + detalle al desplegar</small>
        </div>
        <!--<span class="badge bg-secondary" id="eqCountBadge">0 equipos</span>-->
      </div>

      <div class="accordion inv-acc" id="accEquipos">
        <!-- se llena por JS -->
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const EP_KPIS  = <?= json_encode($EP_KPIS) ?>;
  const EP_CHART = <?= json_encode($EP_CHART) ?>;
  const EP_LIST  = <?= json_encode($EP_LIST) ?>;

  const sel = document.getElementById('invSelectColegio');

  // Helpers
  const pct = (part, total) => total > 0 ? Math.round((part * 100) / total) : 0;

  function setText(id, val){ const el = document.getElementById(id); if(el) el.textContent = val; }
  function setBar(id, p){ const el = document.getElementById(id); if(el) el.style.width = (p||0) + '%'; }

  // Charts
  let chartColegio = null;
  let chartEstado  = null;

  function buildOrUpdateChartColegio(payload){
    // payload esperado (ejemplo):
    // { labels:[], ok:[], rep:[], baja:[] }
    const ctx = document.getElementById('chartColegio');
    if(!ctx) return;

    const data = {
      labels: payload.labels || [],
      datasets: [
        { label:'Operativos', data: payload.ok   || [] },
        { label:'En reparación', data: payload.rep  || [] },
        { label:'Baja', data: payload.baja || [] }
      ]
    };

    if(chartColegio){
      chartColegio.data = data;
      chartColegio.update();
      return;
    }

    chartColegio = new Chart(ctx, {
      type: 'bar',
      data,
      options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{ legend:{ position:'top' } },
        scales:{
          x:{ stacked:true },
          y:{ stacked:true, beginAtZero:true }
        }
      }
    });
  }

  function buildOrUpdateChartEstado(payload){
    // payload esperado:
    // { ok: n, rep: n, baja: n }
    const ctx = document.getElementById('chartEstado');
    if(!ctx) return;

    const data = {
      labels:['Operativos','En reparación','Baja'],
      datasets:[{
        data:[payload.ok||0, payload.rep||0, payload.baja||0],
      }]
    };

    if(chartEstado){
      chartEstado.data = data;
      chartEstado.update();
      return;
    }

    chartEstado = new Chart(ctx, {
      type:'doughnut',
      data,
      options:{
        responsive:true,
        maintainAspectRatio:false,
        cutout:'55%',
        plugins:{ legend:{ position:'bottom' } }
      }
    });
  }

  function renderListado(equipos){
    // equipos: array de objetos ya normalizados
    const acc = document.getElementById('accEquipos');
    if(!acc) return;

    acc.innerHTML = '';
    setText('eqCountBadge', (equipos.length || 0) + ' equipos');

    if(!equipos.length){
      acc.innerHTML = `<div class="text-muted small">No hay equipos para el filtro seleccionado.</div>`;
      return;
    }

    const esc = (s) => {
      if(s === null || s === undefined) return '—';
      return String(s)
        .replaceAll('&','&amp;')
        .replaceAll('<','&lt;')
        .replaceAll('>','&gt;')
        .replaceAll('"','&quot;')
        .replaceAll("'","&#039;");
    };

    equipos.forEach((pc) => {
      const idPc   = parseInt(pc.id_pc || pc.id_equipo || 0, 10);
      const headId = 'eqHead' + idPc;
      const colId  = 'eqCol' + idPc;

      const nombre = esc(pc.nombre_equipo || '—');
      const coleg  = esc(pc.nom_colegio || '—');
      const asig   = esc(pc.asignado_nombre || 'Sin asignar');

      const ram    = esc(pc.ram_gb ?? '—');
      const disco  = esc(pc.disco_gb ?? '—');

      const marca  = esc(pc.marca || pc.fabricante || '—');
      const modelo = esc(pc.modelo || pc.producto || '—');
      const tipo   = esc(pc.tipo_pc || '—');
      const serie  = esc(pc.numero_serie || '—');

      const cpuFab = esc(pc.cpu_fabricante || '—');
      const cpuMod = esc(pc.cpu_modelo || '—');
      const cpuHz  = esc(pc.cpu_freq || '—');

      const almNom = esc(pc.alm_nombre || '—');
      const almTipo= esc(pc.alm_tipo || '—');

      const win    = esc(pc.windows || '—');
      const office = esc(pc.office || '—');
      const av     = esc(pc.antivirus || '—');
      const obs    = esc(pc.observaciones || '');

      // Estado: soporta id_estado o estado texto (OK/REP/BAJA)
      let st = String(pc.estado || '').toUpperCase().trim();
      if(!st && pc.id_estado){
        // Ajusta si tus estados son otros: 1=OK,2=REP,3=BAJA (ejemplo)
        const m = { '1':'OK','2':'REP','3':'BAJA' };
        st = m[String(pc.id_estado)] || 'OK';
      }
      let badgeClass = 'bg-success', badgeText='Operativo';
      if(st === 'REP'){ badgeClass='bg-warning text-dark'; badgeText='En reparación'; }
      if(st === 'BAJA'){ badgeClass='bg-danger'; badgeText='Baja'; }

      acc.insertAdjacentHTML('beforeend', `
        <div class="accordion-item">
          <h2 class="accordion-header" id="${headId}">
            <button class="accordion-button collapsed" type="button"
              data-bs-toggle="collapse" data-bs-target="#${colId}"
              aria-expanded="false" aria-controls="${colId}">
              <div class="w-100 d-flex justify-content-between align-items-center">
                <div class="d-flex flex-column">
                  <div class="fw-bold">
                    <i class="bi bi-pc-display me-1 text-primary"></i>${nombre}
                  </div>
                  <div class="small text-muted">
                    <span class="me-2"><strong>Colegio:</strong> ${coleg}</span>
                    <span class="me-2"><strong>Asignado:</strong> ${asig}</span>
                    <span class="me-2"><strong>RAM:</strong> ${ram} GB</span>
                    <span class="me-2"><strong>Disco:</strong> ${disco} GB</span>
                  </div>
                </div>
                <span class="badge ${badgeClass} ms-3 inv-badge-right">${badgeText}</span>
              </div>
            </button>
          </h2>

          <div id="${colId}" class="accordion-collapse collapse"
            aria-labelledby="${headId}" data-bs-parent="#accEquipos">
            <div class="accordion-body">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Nombre del Equipo</label>
                  <input class="form-control" value="${nombre}" disabled>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Marca</label>
                  <input class="form-control" value="${marca}" disabled>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Modelo</label>
                  <input class="form-control" value="${modelo}" disabled>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Número de Serie</label>
                  <input class="form-control" value="${serie}" disabled>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Tipo de PC</label>
                  <input class="form-control" value="${tipo}" disabled>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Asignado a</label>
                  <input class="form-control" value="${asig}" disabled>
                </div>

                <div class="col-12 mt-2"><hr><h6 class="fw-bold mb-2">Procesador</h6></div>
                <div class="col-md-4">
                  <label class="form-label">Fabricante</label>
                  <input class="form-control" value="${cpuFab}" disabled>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Modelo</label>
                  <input class="form-control" value="${cpuMod}" disabled>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Frecuencia</label>
                  <input class="form-control" value="${cpuHz}" disabled>
                </div>

                <div class="col-12 mt-2"><hr><h6 class="fw-bold mb-2">Almacenamiento</h6></div>
                <div class="col-md-4">
                  <label class="form-label">Nombre</label>
                  <input class="form-control" value="${almNom}" disabled>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Capacidad (GB)</label>
                  <input class="form-control" value="${disco}" disabled>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Tipo</label>
                  <input class="form-control" value="${almTipo}" disabled>
                </div>

                <div class="col-12 mt-2"><hr><h6 class="fw-bold mb-2">Programas</h6></div>
                <div class="col-md-4">
                  <label class="form-label">Windows</label>
                  <input class="form-control" value="${win}" disabled>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Office</label>
                  <input class="form-control" value="${office}" disabled>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Anti-Virus</label>
                  <input class="form-control" value="${av}" disabled>
                </div>

                <div class="col-12 mt-2"><hr><h6 class="fw-bold mb-2">Observaciones</h6></div>
                <div class="col-12">
                  <textarea class="form-control" rows="3" disabled>${obs}</textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
      `);
    });
  }

  async function fetchJson(url){
    const r = await fetch(url, { credentials:'same-origin' });
    const txt = await r.text();

    // 1) intenta JSON directo
    try { return JSON.parse(txt); } catch(e){}

    // 2) si el endpoint ya hace header json pero algo se rompió, entrega error legible
    return { ok:false, error:'Respuesta no-JSON', raw: txt };
  }

  function applyKpis(k){
    const total = parseInt(k.total||0,10);
    const ok    = parseInt(k.ok||0,10);
    const rep   = parseInt(k.rep||0,10);
    const baja  = parseInt(k.baja||0,10);

    setText('kpiTotal', total);
    setText('kpiOk', ok);
    setText('kpiRep', rep);
    setText('kpiBaja', baja);

    const pOk   = pct(ok,total);
    const pRep  = pct(rep,total);
    const pBaja = pct(baja,total);

    setText('kpiOkPct', pOk);
    setText('kpiRepPct', pRep);
    setText('kpiBajaPct', pBaja);

    setBar('barOk', pOk);
    setBar('barRep', pRep);
    setBar('barBaja', pBaja);
  }

  async function refreshAll(){
    const id = parseInt(sel.value || '0', 10);

    // KPI
    const k = await fetchJson(`${EP_KPIS}?colegio=${id}`);
    if(k && (k.ok === true || k.total !== undefined)) applyKpis(k);

    // Chart por colegio
    const ch = await fetchJson(`${EP_CHART}?colegio=${id}`);
    if(ch && ch.labels) buildOrUpdateChartColegio(ch);

    // Donut estado usa KPI (más simple) o payload propio
    buildOrUpdateChartEstado({
      ok:  parseInt((k && k.ok)  ? k.ok  : 0, 10),
      rep: parseInt((k && k.rep) ? k.rep : 0, 10),
      baja:parseInt((k && k.baja)? k.baja:0, 10),
    });

    // Listado
    const lst = await fetchJson(`${EP_LIST}?colegio=${id}`);

    // lst puede venir como {equipos:[...]} o directamente [...]
    let equipos = [];
    if(Array.isArray(lst)) equipos = lst;
    else if(lst && Array.isArray(lst.equipos)) equipos = lst.equipos;

    // Si por alguna razón viene como string JSON dentro:
    if(typeof equipos === 'string'){
      try { equipos = JSON.parse(equipos); } catch(e){ equipos = []; }
    }

    renderListado(equipos);
  }

  // Cambio select -> refresca todo
  sel.addEventListener('change', refreshAll);

  // Carga inicial
  document.addEventListener('DOMContentLoaded', refreshAll);
})();
</script>
