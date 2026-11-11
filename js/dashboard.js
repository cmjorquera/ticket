(() => {
  'use strict';

  const config = window.SEDUC_DASHBOARD || {};
  const app = document.getElementById('dashboard-app');
  if (!app) return;

  const state = {
    perfil: config.perfil || 'usuario',
    data: null,
    estado: '',
    busqueda: '',
    fechaCreacion: '',
    fechaRespuesta: '',
    chartEstados: null,
    chartCategorias: null,
    controller: null,
  };

  const $ = id => document.getElementById(id);
  const escape = value => typeof window.dtEsc === 'function'
    ? window.dtEsc(value)
    : String(value ?? '').replace(/[&<>'"]/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[char]));
  const safeColor = (value, fallback = '#005B96') => {
    const color = String(value || '').trim();
    if (/^#[0-9a-f]{6}$/i.test(color)) return color;
    if (/^#[0-9a-f]{3}$/i.test(color)) return `#${color.slice(1).split('').map(char => char + char).join('')}`;
    return fallback;
  };
  const profileName = profile => ({ usuario: 'Usuario', tecnico: 'Técnico', administrador: 'Administrador' }[profile] || 'Usuario');
  const dateOnly = value => String(value || '').slice(0, 10);
  const formatDate = value => {
    if (!value) return 'Sin fecha';
    const normalized = String(value).replace(' ', 'T');
    const date = new Date(normalized);
    return Number.isNaN(date.getTime())
      ? escape(value)
      : new Intl.DateTimeFormat('es-CL', { dateStyle: 'short', timeStyle: 'short' }).format(date);
  };

  // Skeletons con la misma geometría de las tarjetas, gráficos y tabla.
  function showSkeletons() {
    const kpis = $('dashboard-kpis');
    if (kpis) {
      kpis.innerHTML = Array.from({ length: 4 }, () => `
        <div class="dashboard-kpi-cell">
          <div class="dashboard-skeleton-card" aria-hidden="true">
            <span class="dashboard-skeleton sk-line sk-kpi-label"></span>
            <span class="dashboard-skeleton sk-line sk-kpi-number"></span>
            <span class="dashboard-skeleton sk-line sk-kpi-sub"></span>
            <span class="dashboard-skeleton sk-kpi-icon"></span>
          </div>
        </div>`).join('');
    }
    const table = $('dashboard-ticket-table');
    if (table) {
      table.innerHTML = `<div class="dashboard-skeleton-table" aria-hidden="true">
        <div class="dashboard-skeleton-table-head">
          ${Array.from({length:6},(_,i)=>`<span class="dashboard-skeleton sk-line" style="width:${i===1?75:55}%"></span>`).join('')}
        </div>
        ${Array.from({length:5},(_,row)=>`<div class="dashboard-skeleton-table-row">
          ${Array.from({length:6},(_,col)=>`<span class="dashboard-skeleton sk-line" style="width:${col===1?85:row%2?60:72}%"></span>`).join('')}
        </div>`).join('')}
      </div>`;
    }
    const count = $('dashboard-list-count');
    if (count) count.textContent = '—';
  }

  function setBusy(busy) {
    app.setAttribute('aria-busy', busy ? 'true' : 'false');
    document.querySelectorAll('[data-dashboard-profile]').forEach(button => { button.disabled = busy; });
    document.querySelectorAll('.dashboard-chart-body').forEach(body => {
      body.classList.toggle('dashboard-chart-is-loading', busy);
    });
    ['loadingGraficoTicket', 'loadingGraficoCategorias'].forEach(id => {
      const loader = $(id);
      if (loader) loader.hidden = !busy;
    });
    if (busy) showSkeletons();
  }

  function showError(message) {
    const alert = $('dashboard-alert');
    if (!alert) return;
    alert.querySelector('span').textContent = message;
    alert.hidden = false;
  }

  function clearError() {
    const alert = $('dashboard-alert');
    if (alert) alert.hidden = true;
  }

  async function requestJson(url, options = {}) {
    const response = await fetch(url, {
      credentials: 'same-origin',
      ...options,
      headers: { Accept: 'application/json', ...(options.headers || {}) },
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok || !payload.ok) {
      throw new Error(payload.mensaje || 'La respuesta del servidor no es válida.');
    }
    return payload;
  }

  const MIN_SKELETON_MS = 2000;

  async function loadDashboard(profile = state.perfil) {
    if (state.controller) state.controller.abort();
    const controller = new AbortController();
    const skeletonStartedAt = performance.now();
    state.controller = controller;
    setBusy(true);
    clearError();

    try {
      const endpoint = `${config.endpoints.datos}?perfil=${encodeURIComponent(profile)}`;
      const payload = await requestJson(endpoint, { signal: controller.signal });
      const remaining = MIN_SKELETON_MS - (performance.now() - skeletonStartedAt);
      if (remaining > 0) await new Promise(resolve => setTimeout(resolve, remaining));
      if (controller.signal.aborted) return;

      state.data = payload.data;
      state.perfil = payload.data.perfil;
      state.estado = '';
      state.busqueda = '';
      state.fechaCreacion = '';
      state.fechaRespuesta = '';
      syncControls();
      renderAll();
    } catch (error) {
      if (error.name === 'AbortError') return;
      showError(error.message || 'No fue posible cargar el dashboard.');
      renderLoadFailure();
    } finally {
      if (state.controller === controller) setBusy(false);
    }
  }

  function syncControls() {
    document.querySelectorAll('[data-dashboard-profile]').forEach(button => {
      const active = button.dataset.dashboardProfile === state.perfil;
      button.classList.toggle('active', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
    document.querySelectorAll('[data-profile-label]').forEach(label => { label.textContent = profileName(state.perfil); });
    document.querySelectorAll('[data-chart-profile]').forEach(select => { select.value = state.perfil; });
    const stateSelect = $('dashboard-filter-state');
    if (stateSelect && state.data) {
      stateSelect.innerHTML = '<option value="">Todos los estados</option>' + state.data.grafico_estados
        .map(item => `<option value="${Number(item.id_estado)}">${escape(item.estado)}</option>`).join('');
      stateSelect.value = state.estado;
    }
    if ($('dashboard-filter-created')) $('dashboard-filter-created').value = state.fechaCreacion;
    if ($('dashboard-filter-response')) $('dashboard-filter-response').value = state.fechaRespuesta;
    if ($('dashboard-filter-search')) $('dashboard-filter-search').value = state.busqueda;
  }

  function renderAll() {
    renderKpis();
    renderStateChart();
    renderCategoryChart();
    renderTickets();
    renderInsights();
    updateFilterStatus();
  }

  function renderKpis() {
    const container = $('dashboard-kpis');
    if (!container || !state.data) return;
    const fallback = { 2: '#0066CC', 3: '#FF9800', 5: '#28A745', 7: '#DC3545' };
    container.innerHTML = state.data.kpis.map(kpi => {
      const id = Number(kpi.id_estado);
      const color = safeColor(kpi.color, fallback[id]);
      const classByState = { 2: 'nuevos', 3: 'en-proceso', 5: 'resueltos', 7: 'atrasados' };
      const active = String(id) === String(state.estado);
      const description = kpi.descripcion || `${kpi.estado}: ${kpi.cantidad} tickets.`;
      return `<div class="dashboard-kpi-cell">
        <button class="contenedor-ticket dashboard-kpi ${classByState[id] || ''}${active ? ' is-active' : ''}" type="button" data-kpi-state="${id}" style="--kpi-color:${color}" title="${escape(description)}" aria-pressed="${active ? 'true' : 'false'}">
          <span class="contenedor-ticket-body">
            <span class="contenedor-ticket-titulo">${escape(kpi.estado)}</span>
            <strong class="contenedor-ticket-numero">${Number(kpi.cantidad)}</strong>
            <small class="contenedor-ticket-porcentaje">${Number(kpi.porcentaje)}% de la bandeja</small>
          </span>
          <span class="contenedor-ticket-icono"><i class="bi ${escape(kpi.icono)}" aria-hidden="true"></i></span>
        </button>
      </div>`;
    }).join('');

    container.querySelectorAll('[data-kpi-state]').forEach(button => {
      button.addEventListener('click', () => {
        const selected = button.dataset.kpiState || '';
        state.estado = state.estado === selected ? '' : selected;
        if ($('dashboard-filter-state')) $('dashboard-filter-state').value = state.estado;
        renderKpis();
        renderTickets();
        updateFilterStatus();
      });
    });
  }


  // Indicadores calculados exclusivamente sobre los tickets devueltos para el perfil autorizado.
  // El endpoint limita la bandeja a 100 filas: no presentar estos conteos como totales históricos.
  function renderInsights() {
    if (!state.data) return;
    const tickets = state.data.tickets || [];
    const profile = state.perfil;
    const titles = {
      usuario: ['Estado de mis solicitudes', 'Mis solicitudes pendientes', 'Distribución de los tickets visibles.', 'Tickets abiertos que puedo consultar.'],
      tecnico: ['Prioridades de mis tickets', 'Mis tickets por atender', 'Distribución por prioridad de mi bandeja.', 'Casos activos ordenados por antigüedad.'],
      administrador: ['Prioridades de la bandeja', 'Casos que requieren supervisión', 'Distribución por prioridad de los tickets visibles.', 'Casos demorados o pendientes de gestión.']
    };
    const copy = titles[profile] || titles.usuario;
    $('dashboard-insight-chart-title').textContent = copy[0];
    $('dashboard-insight-list-title').textContent = copy[1];
    $('dashboard-insight-chart-description').textContent = copy[2];
    $('dashboard-insight-list-description').textContent = copy[3];
    const colors = { alta: '#C83B3B', media: '#D18A19', baja: '#2E9460' };
    const isOpen = ticket => ![5, 6].includes(Number(ticket.id_estado));
    const priorities = { alta: 0, media: 0, baja: 0 };
    tickets.forEach(ticket => {
      if (!isOpen(ticket)) return;
      const priority = String(ticket.prioridad || '').toLowerCase().trim();
      if (Object.prototype.hasOwnProperty.call(priorities, priority)) priorities[priority]++;
    });
    const countByStatus = new Map();
    tickets.forEach(ticket => {
      const id = Number(ticket.id_estado);
      countByStatus.set(id, (countByStatus.get(id) || 0) + 1);
    });
    const chart = $('dashboard-insight-chart');
    if (profile === 'usuario') {
      const statuses = state.data.grafico_estados.filter(item => Number(item.cantidad) > 0);
      const total = statuses.reduce((sum, item) => sum + Number(item.cantidad), 0);
      chart.innerHTML = total ? statuses.map(item => {
        const quantity = Number(item.cantidad);
        const percent = Math.round(quantity * 100 / total);
        const color = safeColor(item.color);
        return `<div class="dashboard-insight-bar"><div class="dashboard-insight-bar-label"><strong>${escape(item.estado)}</strong><span>${quantity} · ${percent}%</span></div><div class="dashboard-insight-track"><span style="width:${percent}%;background:${color}"></span></div></div>`;
      }).join('') : '<p class="dashboard-insight-empty">Todavía no hay solicitudes para mostrar.</p>';
    } else {
      const total = Object.values(priorities).reduce((sum, n) => sum + n, 0);
      chart.innerHTML = total ? Object.entries(priorities).map(([name, quantity]) => {
        const percent = Math.round(quantity * 100 / total);
        return `<div class="dashboard-insight-bar"><div class="dashboard-insight-bar-label"><strong>${escape(name[0].toUpperCase() + name.slice(1))}</strong><span>${quantity} · ${percent}%</span></div><div class="dashboard-insight-track"><span style="width:${percent}%;background:${colors[name]}"></span></div></div>`;
      }).join('') : '<p class="dashboard-insight-empty">No hay tickets activos con prioridad definida en esta bandeja.</p>';
    }
    const selected = tickets.filter(ticket => {
      if (!isOpen(ticket)) return false;
      return profile !== 'administrador' || Number(ticket.id_estado) === 7 || Number(ticket.id_estado) === 1 || Number(ticket.id_estado) === 2;
    }).sort((a, b) => {
      const aLate = Number(a.id_estado) === 7 ? 0 : 1;
      const bLate = Number(b.id_estado) === 7 ? 0 : 1;
      return aLate - bLate || String(a.fecha_creacion).localeCompare(String(b.fecha_creacion));
    }).slice(0, 4);
    const list = $('dashboard-insight-list');
    list.innerHTML = selected.length ? selected.map(ticket => {
      const id = Number(ticket.id_ticket);
      const target = profile === 'administrador' ? 'ticket/ticket_admin_v2.php' : profile === 'tecnico' ? 'ticket/ticket_asignados.php' : 'ticket/ticket.php';
      return `<a class="dashboard-insight-item" href="${target}" aria-label="Abrir bandeja para consultar ticket ${id}">
        <span class="dashboard-insight-id">#${id}</span>
        <span class="dashboard-insight-item-body"><strong>${escape(ticket.asunto)}</strong><small>${escape(ticket.categoria)} · ${escape(ticket.colegio)}</small></span>
        <span class="dashboard-ticket-state" style="--state-color:${safeColor(ticket.estado_color)}">${escape(ticket.estado)}</span>
        <i class="bi bi-chevron-right" aria-hidden="true"></i>
      </a>`;
    }).join('') : '<p class="dashboard-insight-empty">No hay tickets pendientes en esta vista.</p>';
  }

  function renderStateChart() {
    if (!state.data || typeof window.Chart !== 'function') return;
    const canvas = $('graficoTicket');
    if (!canvas) return;
    state.chartEstados?.destroy();
    const rows = state.data.grafico_estados;
    state.chartEstados = new window.Chart(canvas, {
      type: 'bar',
      data: {
        labels: rows.map(row => row.estado),
        datasets: [{
          label: 'Tickets',
          data: rows.map(row => Number(row.cantidad)),
          backgroundColor: rows.map(row => `${safeColor(row.color)}CC`),
          borderColor: rows.map(row => safeColor(row.color)),
          borderWidth: 1,
          borderRadius: 5,
          borderSkipped: false,
          barThickness: 18,
        }],
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 500 },
        plugins: {
          legend: { display: true, position: 'bottom', labels: { usePointStyle: true, boxWidth: 7, color: '#6B7280', font: { size: 10 } } },
          tooltip: { displayColors: true, padding: 10, cornerRadius: 6 },
        },
        scales: {
          x: { beginAtZero: true, ticks: { precision: 0, color: '#6B7280' }, grid: { color: '#EEF0F3' }, border: { display: false } },
          y: { ticks: { color: '#3F424A', font: { size: 11, weight: 600 } }, grid: { display: false }, border: { display: false } },
        },
      },
    });
  }

  function renderCategoryChart() {
    if (!state.data || typeof window.ApexCharts !== 'function') return;
    const container = $('graficoCategorias');
    if (!container) return;
    state.chartCategorias?.destroy();
    container.innerHTML = '';
    const graph = state.data.grafico_categorias;
    if (!graph.categorias.length) {
      container.innerHTML = '<div class="empty-state"><h3>Sin categorías</h3><p>No existen categorías activas para graficar.</p></div>';
      return;
    }
    state.chartCategorias = new window.ApexCharts(container, {
      chart: { type: 'area', height: 345, toolbar: { show: false }, animations: { enabled: !window.matchMedia('(prefers-reduced-motion: reduce)').matches } },
      series: graph.series.map(series => ({ name: series.name, data: series.data.map(Number) })),
      colors: graph.series.map(series => safeColor(series.color)),
      stroke: { width: 2, curve: 'smooth' },
      fill: { type: 'solid', opacity: 0.08 },
      markers: { size: 3, strokeWidth: 0, hover: { size: 5 } },
      dataLabels: { enabled: false },
      xaxis: { categories: graph.categorias, labels: { rotate: -35, trim: true, style: { colors: '#6B7280', fontSize: '10px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
      yaxis: { min: 0, forceNiceScale: true, decimalsInFloat: 0, labels: { style: { colors: '#6B7280', fontSize: '10px' } } },
      grid: { borderColor: '#EEF0F3', strokeDashArray: 3, padding: { left: 4, right: 8 } },
      legend: { position: 'bottom', fontSize: '10px', labels: { colors: '#6B7280' }, markers: { width: 7, height: 7, radius: 7 } },
      tooltip: { theme: 'light', shared: true, intersect: false },
      noData: { text: 'Sin datos para mostrar' },
    });
    state.chartCategorias.render();
  }

  function filteredTickets() {
    if (!state.data) return [];
    return state.data.tickets.filter(ticket => {
      const searchable = [ticket.id_ticket, ticket.asunto, ticket.usuario, ticket.tecnico, ticket.categoria, ticket.colegio, ticket.estado]
        .join(' ').toLocaleLowerCase('es');
      return (!state.busqueda || searchable.includes(state.busqueda.toLocaleLowerCase('es')))
        && (!state.estado || String(ticket.id_estado) === String(state.estado))
        && (!state.fechaCreacion || dateOnly(ticket.fecha_creacion) === state.fechaCreacion)
        && (!state.fechaRespuesta || dateOnly(ticket.fecha_respuesta) === state.fechaRespuesta);
    });
  }

  function renderTickets() {
    if (!state.data || typeof window.crearTabla !== 'function') return;
    const tickets = filteredTickets().map((ticket, index) => ({ ...ticket, _numeroFila: index + 1 }));
    const columns = [
      { key: 'id_ticket', label: 'N° / fecha', render: ticket => `<span class="dashboard-ticket-folio"><strong class="dashboard-ticket-row-number">${ticket._numeroFila}</strong><a class="ticket-id" href="ticket/ticket_detalle.php?id=${Number(ticket.id_ticket)}&vista=${({ usuario: 'usuario', tecnico: 'tecnico', administrador: 'admin' }[state.perfil] || 'usuario')}">Ticket #${Number(ticket.id_ticket)}</a><small>${formatDate(ticket.fecha_creacion)}</small></span>` },
      { key: 'asunto', label: 'Caso', render: ticket => `<span class="dashboard-ticket-main"><strong>${escape(ticket.asunto)}</strong><small>${escape(ticket.colegio)}</small></span>` },
    ];
    if (state.perfil !== 'usuario') columns.push({ key: 'usuario', label: 'Usuario' });
    columns.push({ key: 'tecnico', label: 'Técnico' });
    columns.push(
      { key: 'estado', label: 'Estado', render: ticket => `<span class="dashboard-ticket-state" style="--state-color:${safeColor(ticket.estado_color)}">${escape(ticket.estado)}</span>` },
      { key: 'fecha_respuesta', label: 'Fecha de respuesta', render: ticket => `<span class="text-nowrap">${formatDate(ticket.fecha_respuesta)}</span>` },
      { key: 'categoria', label: 'Categoría', render: ticket => `<span class="badge badge-realizado">${escape(ticket.categoria)}</span>` },
      { key: 'acciones', label: 'Acciones', render: renderActions },
    );
    window.crearTabla('dashboard-ticket-table', columns, tickets, {
      titulo: 'Tickets',
      perPage: 10,
      csv: `tickets-${state.perfil}`,
      showSearch: false,
      prevLabel: 'Anterior',
      nextLabel: 'Siguiente',
      emptyTitle: 'Sin tickets',
      emptyText: 'No hay casos que coincidan con los filtros seleccionados.',
    });
    const count = $('dashboard-list-count');
    if (count) count.textContent = `${tickets.length} ticket${tickets.length === 1 ? '' : 's'}`;
  }

  function renderActions(ticket) {
    const id = Number(ticket.id_ticket);
    // el detalle muestra lo que corresponde a la bandeja (ticket/logica/ESTUDIO_MODULO_TICKET.txt)
    const v = `&vista=${({ usuario: 'usuario', tecnico: 'tecnico', administrador: 'admin' }[state.perfil] || 'usuario')}`;
    const links = [
      `<a href="ticket/ticket_detalle.php?id=${id}${v}" title="Ver ticket" aria-label="Ver ticket ${id}"><i class="bi bi-eye" aria-hidden="true"></i><span>Ver</span></a>`,
      `<a href="ticket/ticket_detalle.php?id=${id}${v}#conversacion" title="Abrir conversación" aria-label="Abrir conversación del ticket ${id}"><i class="bi bi-chat-dots" aria-hidden="true"></i><span>Chat</span></a>`,
    ];
    if (ticket.puede_calificar) {
      links.push(`<a class="is-rate" href="ticket/ticket_detalle.php?id=${id}&vista=usuario#calificacion" title="Calificar atención" aria-label="Calificar ticket ${id}"><i class="bi bi-star-fill" aria-hidden="true"></i><span>Calificar</span></a>`);
    }
    if (state.perfil === 'tecnico') {
      links.push(`<a href="ticket/ticket_asignados.php" title="Gestionar estado" aria-label="Gestionar ticket ${id}"><i class="bi bi-pencil" aria-hidden="true"></i><span>Estado</span></a>`);
    } else if (state.perfil === 'administrador') {
      links.push(`<a href="ticket/ticket_admin_v2.php" title="Administrar ticket" aria-label="Administrar ticket ${id}"><i class="bi bi-sliders" aria-hidden="true"></i><span>Gestionar</span></a>`);
    }
    return `<span class="dashboard-ticket-actions">${links.join('')}</span>`;
  }

  function updateFilterStatus() {
    const element = $('dashboard-filter-status');
    if (!element || !state.data) return;
    const activeState = state.data.grafico_estados.find(item => String(item.id_estado) === String(state.estado));
    element.hidden = !activeState;
    element.textContent = activeState ? `Filtrando por: ${activeState.estado}` : '';
  }

  function renderLoadFailure() {
    const table = $('dashboard-ticket-table');
    if (table) table.innerHTML = '<div class="empty-state"><h3>Datos no disponibles</h3><p>Reintenta para consultar los tickets.</p></div>';
    ['dashboard-kpis'].forEach(id => {
      const element = $(id);
      if (element) element.innerHTML = '';
    });
  }

  async function changeProfile(profile) {
    if (profile === state.perfil) return;
    setBusy(true);
    try {
      const body = new URLSearchParams({ perfil: profile, csrf: config.csrf || '' });
      await requestJson(config.endpoints.cambiarPerfil, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
        body,
      });
      history.replaceState(null, '', `${location.pathname}?vista_perfil=${encodeURIComponent(profile)}`);
      await loadDashboard(profile);
    } catch (error) {
      showError(error.message || 'No fue posible cambiar el perfil.');
      syncControls();
      setBusy(false);
    }
  }

  document.querySelectorAll('[data-dashboard-profile]').forEach(button => {
    button.addEventListener('click', () => changeProfile(button.dataset.dashboardProfile));
  });
  document.querySelectorAll('[data-chart-profile]').forEach(select => {
    select.addEventListener('change', () => changeProfile(select.value));
  });
  $('dashboard-filter-search')?.addEventListener('input', event => { state.busqueda = event.target.value.trim(); renderTickets(); });
  $('dashboard-filter-state')?.addEventListener('change', event => {
    state.estado = event.target.value;
    renderKpis(); renderTickets(); updateFilterStatus();
  });
  $('dashboard-filter-created')?.addEventListener('change', event => { state.fechaCreacion = event.target.value; renderTickets(); });
  $('dashboard-filter-response')?.addEventListener('change', event => { state.fechaRespuesta = event.target.value; renderTickets(); });
  $('dashboard-clear-filters')?.addEventListener('click', () => {
    state.estado = ''; state.busqueda = ''; state.fechaCreacion = ''; state.fechaRespuesta = '';
    syncControls(); renderKpis(); renderTickets(); updateFilterStatus();
  });
  $('dashboard-retry')?.addEventListener('click', () => loadDashboard());

  loadDashboard();
})();
