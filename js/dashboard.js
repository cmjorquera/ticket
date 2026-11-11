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
    adminPeriod: '365',
    adminSchool: 0,
    adminCharts: [],
    selectedTicketId: null,
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
      const endpoint = `${config.endpoints.datos}?perfil=${encodeURIComponent(profile)}&periodo=${encodeURIComponent(state.adminPeriod)}&colegio=${encodeURIComponent(state.adminSchool)}`;
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
    renderTechAttention();
    renderAdminOverview();
    renderUserActiveTicket();
    renderStateChart();
    renderCategoryChart();
    renderTickets();
    renderInsights();
    updateFilterStatus();
  }

  // Alertas del técnico: sólo tickets autorizados de su bandeja, sin duplicar KPI.
  function renderTechAttention() {
    const panel = $('dashboard-tech-attention');
    if (!panel) return;
    const visible = state.perfil === 'tecnico' && !!state.data;
    panel.hidden = !visible;
    if (!visible) return;
    const tickets = (state.data.tickets || []).filter(t => [2, 3, 7].includes(Number(t.id_estado)));
    const scored = tickets.map(ticket => {
      const days = ticket.dias_restantes === null || ticket.dias_restantes === undefined || ticket.dias_restantes === '' ? null : Number(ticket.dias_restantes);
      const validDays = days !== null && Number.isFinite(days);
      const overdue = Number(ticket.id_estado) === 7 || (validDays && days < 0);
      const soon = !overdue && validDays && days <= 2;
      const inProgress = Number(ticket.id_estado) === 3;
      return { ticket, days, validDays, overdue, soon, inProgress, rank: overdue ? 0 : soon ? 1 : inProgress ? 2 : 3 };
    }).filter(item => item.rank < 3).sort((a,b) => a.rank - b.rank || (a.validDays ? a.days : 9999) - (b.validDays ? b.days : 9999));
    const count = scored.length;
    const title = panel.querySelector('[data-attention-count]');
    if (title) title.textContent = count + (count === 1 ? ' ticket' : ' tickets');
    const list = panel.querySelector('[data-attention-list]');
    if (!list) return;
    list.innerHTML = count ? scored.slice(0, 4).map(({ticket, days, validDays, overdue, soon}) => {
      const status = overdue ? (validDays && days < 0 ? `Vencido hace ${Math.abs(days)} d` : 'Demorado') : soon ? (days === 0 ? 'Vence hoy' : `Faltan ${days} d`) : 'En proceso';
      const tone = overdue ? 'late' : soon ? 'soon' : 'progress';
      const url = `ticket/ticket_detalle.php?id=${Number(ticket.id_ticket)}&vista=tecnico`;
      return `<a class="dashboard-attention-item" href="${url}" aria-label="Abrir ticket ${Number(ticket.id_ticket)}: ${escape(ticket.asunto)}">
        <span class="dashboard-attention-copy"><strong>#${Number(ticket.id_ticket)} · ${escape(ticket.asunto)}</strong><small>${escape(ticket.categoria || ticket.colegio || 'Ticket asignado')}</small></span>
        <span class="dashboard-attention-tag is-${tone}">${escape(status)}</span><i class="bi bi-chevron-right" aria-hidden="true"></i>
      </a>`;
    }).join('') + (count > 4 ? `<a class="dashboard-attention-more" href="ticket/ticket_asignados.php">Ver todos los tickets asignados (${count}) <i class="bi bi-arrow-right"></i></a>` : '') : '<p class="dashboard-attention-empty"><i class="bi bi-check-circle"></i> No hay tickets demorados, próximos a vencer o en proceso.</p>';
  }

  // Resumen del ticket activo: exclusivamente solicitudes autorizadas del usuario.
  function renderUserActiveTicket() {
    const panel = $('dashboard-user-active');
    if (!panel) return;
    const active = state.perfil === 'usuario' && !!state.data;
    panel.hidden = !active;
    if (!active) return;
    const tickets = (state.data.tickets || []).filter(t => ![5, 6].includes(Number(t.id_estado)));
    const ticket = tickets.sort((a,b) => {
      const priority = t => Number(t.id_estado) === 7 ? -10000 : t.dias_restantes === null || t.dias_restantes === undefined || t.dias_restantes === '' ? 10000 : Number(t.dias_restantes);
      return priority(a) - priority(b);
    })[0];
    if (!ticket) {
      panel.innerHTML = '<div class="dashboard-user-active-empty"><i class="bi bi-check-circle"></i><strong>No tienes solicitudes pendientes</strong><p>Cuando tengas un ticket activo podrás consultar aquí su técnico y fecha estimada.</p></div>';
      return;
    }
    const days = ticket.dias_restantes === null || ticket.dias_restantes === undefined || ticket.dias_restantes === '' ? null : Number(ticket.dias_restantes);
    const time = days === null || !Number.isFinite(days) ? 'Sin fecha estimada' : days < 0 ? 'Vencido hace ' + Math.abs(days) + ' día(s)' : days === 0 ? 'Vence hoy' : 'Faltan ' + days + ' día(s)';
    const late = Number(ticket.id_estado) === 7 || (days !== null && days < 0);
    panel.innerHTML = `<div class="dashboard-user-active-heading"><div><h2>Mi ticket que requiere atención</h2><p>Seguimiento de tu solicitud pendiente más urgente.</p></div><span class="dashboard-user-active-status">${escape(ticket.estado)}</span></div>
      <a class="dashboard-user-active-subject" href="ticket/ticket_detalle.php?id=${Number(ticket.id_ticket)}&vista=usuario">#${Number(ticket.id_ticket)} · ${escape(ticket.asunto)} <i class="bi bi-arrow-up-right"></i></a>
      <div class="dashboard-user-active-details"><div><small>Técnico asignado</small><strong>${escape(ticket.tecnico || 'Pendiente de asignación')}</strong></div><div><small>Fecha estimada</small><strong>${escape(ticket.fecha_estimada || 'Sin definir')}</strong></div></div>
      <div class="dashboard-user-active-deadline${late ? ' is-late' : ''}"><i class="bi bi-clock"></i>${escape(time)}</div>`;
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
      const help = state.perfil === 'tecnico' || state.perfil === 'usuario' ? `<details class="dashboard-admin-metric-help dashboard-tech-metric-help"><summary aria-label="Descripción del estado ${escape(kpi.estado)}" title="¿Qué significa este estado?"><i class="bi bi-question-circle" aria-hidden="true"></i></summary><div class="dashboard-admin-metric-help-text">${escape(description)}</div></details>` : '';
      return `<div class="dashboard-kpi-cell">
        <button class="contenedor-ticket dashboard-kpi ${classByState[id] || ''}${active ? ' is-active' : ''}${state.perfil === 'tecnico' && id === 7 && Number(kpi.cantidad) > 0 ? ' dashboard-kpi-alert' : ''}" type="button" data-kpi-state="${id}" style="--kpi-color:${color}" title="${escape(description)}" aria-pressed="${active ? 'true' : 'false'}">
          <span class="contenedor-ticket-body">
            <span class="contenedor-ticket-titulo">${escape(kpi.estado)}</span>
            <strong class="contenedor-ticket-numero">${Number(kpi.cantidad)}</strong>
            <small class="contenedor-ticket-porcentaje">${Number(kpi.porcentaje)}% de la bandeja</small>
          </span>
          <span class="contenedor-ticket-icono"><i class="bi ${escape(kpi.icono)}" aria-hidden="true"></i></span>
        </button>
        ${help}
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



  function renderAdminOverview() {
    const panel = $('dashboard-admin-overview');
    const charts = document.querySelector('.dashboard-chart-grid');
    const insights = $('dashboard-insights');
    const kpis = $('dashboard-kpis')?.closest('section');
    const admin = state.perfil === 'administrador' && !!state.data?.admin;
    const tecnico = state.perfil === 'tecnico';
    app.classList.toggle('dashboard--tecnico', tecnico);
    app.classList.toggle('dashboard--usuario', state.perfil === 'usuario');
    const title = $('dashboard-table-title');
    const desc = $('dashboard-table-description');
    if (title) title.textContent = tecnico ? 'Mis tickets asignados' : state.perfil === 'usuario' ? 'Mis solicitudes' : 'Últimos tickets';
    if (desc) desc.textContent = tecnico ? 'Listado de tickets a mi cargo. Selecciona el número para destacar una fila.' : state.perfil === 'usuario' ? 'Consulta quién atiende tus solicitudes, su estado y cuánto tiempo falta para la fecha estimada.' : 'Los casos más recientes disponibles para el perfil activo.';
    const headings = document.querySelectorAll('.dashboard-chart-grid .dashboard-panel-header > div:first-child');
    if (headings[0]) { headings[0].querySelector('h2').textContent = tecnico || state.perfil === 'usuario' ? 'Estados de mis tickets' : 'Gráfico de tickets'; headings[0].querySelector('p').textContent = tecnico || state.perfil === 'usuario' ? 'Distribución de mis solicitudes por estado.' : 'Distribución actual de la bandeja.'; }
    if (headings[1]) { headings[1].querySelector('h2').textContent = tecnico || state.perfil === 'usuario' ? 'Mis tickets por categoría' : 'Estados por categoría'; headings[1].querySelector('p').textContent = tecnico || state.perfil === 'usuario' ? 'Cantidad de solicitudes por tipo.' : 'Comparación de carga entre tipos de solicitud.'; }
    if (panel) panel.hidden = !admin;
    if (charts) charts.hidden = admin;
    if (insights) insights.hidden = admin || tecnico || state.perfil === 'usuario';
    if (kpis) kpis.hidden = admin;
    state.adminCharts.forEach(chart => chart.destroy());
    state.adminCharts = [];
    if (!panel || !admin) { if(panel) panel.innerHTML=''; return; }
    const data = state.data.admin;
    const summary = data.resumen || {};
    const total = Number(summary.total || 0);
    const resolved = Number(summary.resueltos || 0);
    const pending = Number(summary.pendientes || 0);
    const late = Number(summary.atrasados || 0);
    const percent = total ? Math.round(100*resolved/total) : 0;
    const schools = data.colegios_conteo || [];
    const techs = data.tecnicos || [];
    const cases = data.casos || [];
    const noTech = cases.filter(t => ![5,6].includes(Number(t.id_estado)) && Number(t.id_estado) !== 7);
    const delayed = cases.filter(t => Number(t.id_estado) === 7);
    const row = (t, showAge=false) => `<tr><td><a href="ticket/ticket_detalle.php?id=${Number(t.id_ticket)}&vista=admin">#${Number(t.id_ticket)}</a></td><td><strong>${escape(t.asunto)}</strong></td><td>${escape(showAge?'Demorado':t.colegio)}</td><td>${showAge ? escape(t.estimada || 'Sin fecha') : escape(t.fecha || 'Sin fecha')}</td><td><a class="dashboard-admin-view" href="ticket/ticket_detalle.php?id=${Number(t.id_ticket)}&vista=admin">Ver</a></td></tr>`;
    const table = (list,age=false) => list.length ? `<div class="dashboard-admin-table-wrap"><table class="dashboard-admin-table"><thead><tr><th>Ticket</th><th>Caso / Asunto</th><th>${age?'Estado':'Colegio'}</th><th>${age?'Fecha estimada':'Fecha'}</th><th></th></tr></thead><tbody>${list.slice(0,5).map(t=>row(t,age)).join('')}</tbody></table></div>` : '<p class="dashboard-admin-empty">No hay casos en este grupo.</p>';
    panel.innerHTML = `
      <div class="dashboard-admin-top"><div><h2>Dashboard – Administrador</h2><p>Visión global de la mesa de ayuda, gestión de tickets y carga del equipo de soporte.</p></div><div class="dashboard-admin-filters"><label>Período<select id="dashboard-admin-period"><option value="30">Últimos 30 días</option><option value="90">Últimos 90 días</option><option value="365">Últimos 12 meses</option><option value="all">Todo el historial</option></select></label><label>Colegio<select id="dashboard-admin-school"><option value="0">Todos los colegios</option>${data.colegios.map(s=>`<option value="${Number(s.id)}">${escape(s.nombre)}</option>`).join('')}</select></label><button id="dashboard-admin-refresh" type="button" aria-label="Actualizar indicadores"><i class="bi bi-arrow-clockwise"></i></button></div></div>
      <div class="dashboard-admin-metrics">
        <div class="dashboard-admin-metric"><span><i class="bi bi-layers"></i> Tickets totales</span><strong>${total}</strong><small>En el período seleccionado</small></div>
        ${[
          {id:1,nombre:'Recibido',icono:'bi-inbox',color:'#005B96'},
          {id:2,nombre:'Asignado',icono:'bi-person-check',color:'#FFBF4C'},
          {id:3,nombre:'En proceso',icono:'bi-hourglass-split',color:'#FFE0E0'},
          {id:5,nombre:'Terminado',icono:'bi-check-circle',color:'#C8F7C5'},
          {id:7,nombre:'Demorado',icono:'bi-exclamation-triangle',color:'#FFD6A5'}
        ].map(st=>{
          const count=Number((data.estados_conteo||{})[st.id]||0);
          const pct=total?Math.round(count*100/total):0;
          const description=String((data.estados_descripciones||{})[st.id]||st.nombre);
          return `<div class="dashboard-admin-metric" style="border-left-color:${st.color}"><div class="dashboard-admin-metric-heading"><span><i class="bi ${st.icono}"></i> ${st.nombre}</span><details class="dashboard-admin-metric-help"><summary aria-label="Descripción del estado ${escape(st.nombre)}" title="¿Qué significa este estado?"><i class="bi bi-question-circle"></i></summary><div class="dashboard-admin-metric-help-text">${escape(description)}</div></details></div><strong>${count}</strong><small>${pct}% del total de tickets</small></div>`;
        }).join('')}
      </div>
      <div class="dashboard-admin-grid">
        <article class="dashboard-admin-card"><h3>Tickets por colegio</h3><p>Distribución por estado de los tickets de cada colegio en el período seleccionado.</p><div class="dashboard-admin-graph" id="dashboard-admin-school-chart"></div></article>
        <article class="dashboard-admin-card"><h3>Evolución mensual de tickets</h3><p>Tickets creados y actualmente resueltos, agrupados por mes de creación.</p><div class="dashboard-admin-graph" id="dashboard-admin-month-chart"></div></article>
        <article class="dashboard-admin-card"><h3>Carga de trabajo por técnico</h3><p>Distribución de tickets en el período seleccionado.</p><div class="dashboard-admin-table-wrap"><table class="dashboard-admin-table"><thead><tr><th>Técnico</th><th>Activos</th><th>Atrasados</th><th>Resueltos</th><th>Total</th><th>Detalle</th></tr></thead><tbody>${techs.map((t,index)=>`<tr><td>${escape(t.nombre)}</td><td><span class="dashboard-admin-pill blue">${Number(t.activos)}</span></td><td><span class="dashboard-admin-pill red">${Number(t.atrasados)}</span></td><td><span class="dashboard-admin-pill green">${Number(t.resueltos)}</span></td><td><b>${Number(t.total)}</b></td><td><button type="button" class="dashboard-admin-detail-btn" data-tech-index="${index}" aria-label="Ver categorías de ${escape(t.nombre)}" title="Ver categorías"><i class="bi bi-pie-chart"></i> <span>Ver</span></button></td></tr>`).join('')}</tbody></table></div></article>
        <div class="dashboard-admin-stack">
          <article class="dashboard-admin-card"><h3>Tickets sin técnico asignado <span class="dashboard-admin-count">${noTech.length} casos</span></h3><p>Solicitudes que requieren asignación inmediata.</p>${table(noTech)}</article>
          <article class="dashboard-admin-card"><h3>Casos que requieren supervisión <span class="dashboard-admin-count">${delayed.length} casos</span></h3><p>Tickets demorados que requieren seguimiento.</p>${table(delayed,true)}</article>
        </div>
      </div>
      <div id="dashboard-tech-modal" class="dashboard-tech-modal" hidden role="dialog" aria-modal="true" aria-labelledby="dashboard-tech-modal-title">
        <div class="dashboard-tech-dialog">
          <header class="dashboard-tech-header"><div><h2 id="dashboard-tech-modal-title">Categorías del técnico</h2><p id="dashboard-tech-modal-subtitle"></p></div><button type="button" id="dashboard-tech-close" aria-label="Cerrar detalle"><i class="bi bi-x-lg"></i></button></header>
          <div class="dashboard-tech-content"><div class="dashboard-tech-chart-card"><h3>Distribución por categoría</h3><div id="dashboard-tech-pie"></div></div><div class="dashboard-tech-chart-card"><h3>Estados por categoría</h3><div id="dashboard-tech-bars"></div></div></div>
          <div class="dashboard-admin-table-wrap"><table class="dashboard-admin-table"><thead><tr><th>Categoría</th><th>Tickets</th><th>Porcentaje</th><th>Detalle por estado</th></tr></thead><tbody id="dashboard-tech-categories"></tbody></table></div>
        </div>
      </div>`;
    panel.querySelectorAll('[data-tech-index]').forEach(button => button.addEventListener('click', () => openTechCategories(techs[Number(button.dataset.techIndex)])));
    $('dashboard-tech-close').addEventListener('click', closeTechCategories);
    $('dashboard-tech-modal').addEventListener('click', event => { if (event.target === $('dashboard-tech-modal')) closeTechCategories(); });
    $('dashboard-admin-period').value = data.periodo;
    $('dashboard-admin-school').value = String(data.colegio);
    $('dashboard-admin-period').addEventListener('change', e => { state.adminPeriod=e.target.value; state.adminSchool=0; loadDashboard('administrador'); });
    $('dashboard-admin-school').addEventListener('change', e => { state.adminSchool=Number(e.target.value); loadDashboard('administrador'); });
    $('dashboard-admin-refresh').addEventListener('click', () => loadDashboard('administrador'));
    if (typeof window.ApexCharts !== 'function') return;
    const base = {chart:{toolbar:{show:false},fontFamily:'inherit',height:270},dataLabels:{enabled:false},grid:{borderColor:'#e6ebf2'},tooltip:{theme:'light'},noData:{text:'Sin datos'}};
    const schoolStates = [
      {id:1,name:'Recibido',color:'#5895d0'},
      {id:2,name:'Asignado',color:'#f0bd45'},
      {id:3,name:'En proceso',color:'#f38c80'},
      {id:4,name:'Borrador',color:'#9c8dbb'},
      {id:5,name:'Terminado',color:'#35b77a'},
      {id:6,name:'Cerrado',color:'#7b8797'},
      {id:7,name:'Demorado',color:'#e25b61'}
    ];
    const schoolChart = new window.ApexCharts($('dashboard-admin-school-chart'),{
      ...base,
      chart:{...base.chart,type:'bar',stacked:true,height:320},
      series:schoolStates.map(st=>({name:st.name,data:schools.map(s=>Number((s.estados||{})[st.id]||0))})),
      colors:schoolStates.map(st=>st.color),
      xaxis:{categories:schools.map(s=>s.nombre),labels:{rotate:-35,trim:false,style:{colors:'#213a59',fontSize:'11px'}}},
      yaxis:{min:0,forceNiceScale:true,labels:{formatter:v=>Math.round(v)}},
      plotOptions:{bar:{horizontal:false,columnWidth:'65%',borderRadius:2}},
      dataLabels:{enabled:false},
      legend:{show:true,position:'bottom',fontSize:'12px'},
      tooltip:{shared:true,intersect:false,y:{formatter:v=>Math.round(v)+' tickets'}},
      fill:{opacity:1},
      grid:{borderColor:'#e6ebf2'}
    });
    schoolChart.render();state.adminCharts.push(schoolChart);
    const months=data.meses || [];
    const monthChart = new window.ApexCharts($('dashboard-admin-month-chart'),{...base,chart:{...base.chart,type:'area'},series:[{name:'Creados',data:months.map(m=>Number(m.creados))},{name:'Resueltos actualmente',data:months.map(m=>Number(m.resueltos))}],colors:['#1684eb','#159654'],stroke:{width:3,curve:'smooth'},fill:{type:'solid',opacity:.12},markers:{size:3},xaxis:{categories:months.map(m=>m.mes),labels:{style:{colors:'#213a59'}}},yaxis:{min:0,forceNiceScale:true,labels:{formatter:v=>Math.round(v)}},legend:{position:'top',horizontalAlign:'right'}});
    monthChart.render();state.adminCharts.push(monthChart);
  }

  let techModalCharts = [];
  let techModalReturnFocus = null;
  function closeTechCategories() {
    const modal = $('dashboard-tech-modal');
    if (!modal || modal.hidden) return;
    modal.hidden = true;
    techModalCharts.forEach(chart => chart.destroy());
    techModalCharts = [];
    document.body.classList.remove('dashboard-tech-modal-open');
    techModalReturnFocus?.focus();
  }
  function openTechCategories(tech) {
    if (!tech) return;
    closeTechCategories();
    const modal = $('dashboard-tech-modal');
    if (!modal) return;
    techModalReturnFocus = document.activeElement;
    const categories = tech.categorias || [];
    const total = Number(tech.total || 0);
    $('dashboard-tech-modal-title').textContent = 'Categorías · ' + tech.nombre;
    $('dashboard-tech-modal-subtitle').textContent = total + ' tickets en el período y colegio seleccionados';
    const states = [{id:1,name:'Recibido',color:'#5895d0'},{id:2,name:'Asignado',color:'#f0bd45'},{id:3,name:'En proceso',color:'#f38c80'},{id:4,name:'Borrador',color:'#9c8dbb'},{id:5,name:'Terminado',color:'#35b77a'},{id:6,name:'Cerrado',color:'#7b8797'},{id:7,name:'Demorado',color:'#e25b61'}];
    $('dashboard-tech-categories').innerHTML = categories.length ? categories.map(cat => {
      const details = states.filter(s => Number(cat.estados?.[s.id] || 0) > 0).map(s => escape(s.name) + ': ' + Number(cat.estados[s.id])).join(' · ');
      return '<tr><td><strong>' + escape(cat.nombre) + '</strong></td><td>' + Number(cat.total) + '</td><td>' + (total ? Math.round(Number(cat.total) * 100 / total) : 0) + '%</td><td>' + details + '</td></tr>';
    }).join('') : '<tr><td colspan="4">Sin tickets para este técnico.</td></tr>';
    modal.hidden = false;
    document.body.classList.add('dashboard-tech-modal-open');
    $('dashboard-tech-close').focus();
    if (typeof window.ApexCharts !== 'function' || !categories.length) {
      $('dashboard-tech-pie').textContent = categories.length ? 'Gráfico no disponible.' : 'Sin datos';
      $('dashboard-tech-bars').textContent = categories.length ? 'Gráfico no disponible.' : 'Sin datos';
      return;
    }
    const pie = new window.ApexCharts($('dashboard-tech-pie'), {chart:{type:'donut',height:320,toolbar:{show:false}},series:categories.map(cat=>Number(cat.total)),labels:categories.map(cat=>cat.nombre),legend:{position:'bottom'},dataLabels:{enabled:true,formatter:val=>Math.round(val)+'%'},tooltip:{y:{formatter:val=>val+' tickets'}},noData:{text:'Sin datos'}});
    const bars = new window.ApexCharts($('dashboard-tech-bars'), {chart:{type:'bar',stacked:true,height:320,toolbar:{show:false}},series:states.map(s=>({name:s.name,data:categories.map(cat=>Number(cat.estados?.[s.id] || 0))})),colors:states.map(s=>s.color),xaxis:{categories:categories.map(cat=>cat.nombre),labels:{rotate:-35,trim:true}},yaxis:{min:0,labels:{formatter:val=>Math.round(val)}},plotOptions:{bar:{horizontal:false,columnWidth:'65%'}},legend:{position:'bottom'},dataLabels:{enabled:false},tooltip:{shared:true,intersect:false,y:{formatter:val=>val+' tickets'}}});
    techModalCharts = [pie,bars];
    techModalCharts.forEach(chart=>chart.render());
  }
  document.addEventListener('keydown', event => {
    const modal = $('dashboard-tech-modal');
    if (!modal || modal.hidden) return;
    if (event.key === 'Escape') { event.preventDefault(); closeTechCategories(); }
    if (event.key === 'Tab') {
      const focusable = [...modal.querySelectorAll('button:not([disabled]),a[href],input:not([disabled]),select:not([disabled]')];
      if (!focusable.length) return;
      const first = focusable[0], last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
      else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    }
  });

  function renderStateChart() {
    if (!state.data || typeof window.Chart !== 'function') return;
    const canvas = $('graficoTicket');
    if (!canvas) return;
    state.chartEstados?.destroy();
    const rows = state.perfil === 'tecnico' ? state.data.grafico_estados.filter(row => [2,3,5,7].includes(Number(row.id_estado))) : state.data.grafico_estados;
    const donut = state.perfil === 'tecnico' || state.perfil === 'usuario';
    const centerTotalPlugin = { id: 'dashboardTechCenterTotal', afterDraw(chart) { if (!donut) return; const arc = chart.getDatasetMeta(0).data[0]; if (!arc) return; const {ctx} = chart; const total = rows.reduce((sum, row) => sum + Number(row.cantidad || 0), 0); ctx.save(); ctx.textAlign = 'center'; ctx.textBaseline = 'middle'; ctx.fillStyle = '#132d50'; ctx.font = 'bold 30px sans-serif'; ctx.fillText(String(total), arc.x, arc.y - 10); ctx.font = '600 12px sans-serif'; ctx.fillText('Tickets', arc.x, arc.y + 18); ctx.restore(); } };
    state.chartEstados = new window.Chart(canvas, {
      type: donut ? 'doughnut' : 'bar',
      plugins: donut ? [centerTotalPlugin] : [],
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
          ...(donut ? { hoverOffset: 7, cutout: '68%' } : {}),
        }],
      },
      options: {
        indexAxis: donut ? 'x' : 'y',
        cutout: donut ? '68%' : undefined,
        responsive: true,
        maintainAspectRatio: false,
        animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 500 },
        plugins: {
          legend: { display: true, position: 'bottom', labels: { usePointStyle: true, boxWidth: 7, color: '#6B7280', font: { size: 11 } } },
          tooltip: { displayColors: true, padding: 10, cornerRadius: 6 },
        },
        scales: donut ? {} : {
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
    const tecnico = state.perfil === 'tecnico' || state.perfil === 'usuario';
    const categoryTotals = graph.categorias.map((_, index) => graph.series.reduce((sum, series) => sum + Number(series.data[index] || 0), 0));
    state.chartCategorias = new window.ApexCharts(container, {
      chart: { type: tecnico ? 'bar' : 'area', height: 345, toolbar: { show: false }, animations: { enabled: !window.matchMedia('(prefers-reduced-motion: reduce)').matches } },
      series: tecnico ? [{ name: 'Tickets', data: categoryTotals }] : graph.series.map(series => ({ name: series.name, data: series.data.map(Number) })),
      colors: tecnico ? ['#448CE0'] : graph.series.map(series => safeColor(series.color)),
      plotOptions: tecnico ? { bar: { borderRadius: 5, columnWidth: '48%', distributed: false } } : {},
      stroke: { width: 2, curve: 'smooth' },
      fill: { type: 'solid', opacity: tecnico ? 0.9 : 0.08 },
      markers: { size: 3, strokeWidth: 0, hover: { size: 5 } },
      dataLabels: { enabled: tecnico, style: { colors: ['#15345B'] }, offsetY: -18 },
      xaxis: { categories: graph.categorias, labels: { rotate: -35, trim: true, style: { colors: '#6B7280', fontSize: '10px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
      yaxis: { min: 0, forceNiceScale: true, decimalsInFloat: 0, labels: { style: { colors: '#6B7280', fontSize: '10px' } } },
      grid: { borderColor: '#EEF0F3', strokeDashArray: 3, padding: { left: 4, right: 8 } },
      legend: { show: !tecnico, position: 'bottom', fontSize: '10px', labels: { colors: '#6B7280' }, markers: { width: 7, height: 7, radius: 7 } },
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
    const remainingLabel = ticket => {
      if ([5, 6].includes(Number(ticket.id_estado))) return '<span class="dashboard-deadline is-done">Resuelto</span>';
      if (ticket.dias_restantes === null || ticket.dias_restantes === undefined || ticket.dias_restantes === '') return '<span class="dashboard-deadline is-undefined">Sin fecha definida</span>';
      const days = Number(ticket.dias_restantes);
      if (!Number.isFinite(days)) return '<span class="dashboard-deadline is-undefined">Sin fecha definida</span>';
      const label = days < 0 ? `Vencido hace ${Math.abs(days)} día${Math.abs(days) === 1 ? '' : 's'}` : days === 0 ? 'Vence hoy' : `Faltan ${days} día${days === 1 ? '' : 's'}`;
      return `<span class="dashboard-deadline ${days < 0 ? 'is-late' : days <= 2 ? 'is-soon' : 'is-ok'}">${label}</span>`;
    };
    const columns = [
      { key: 'id_ticket', label: 'N° / fecha', render: ticket => `<span class="dashboard-ticket-folio" data-estado="${escape(ticket.estado)}" data-ticket-id="${Number(ticket.id_ticket)}"><button type="button" class="dashboard-ticket-row-number" aria-pressed="false" title="Seleccionar fila ${ticket._numeroFila}">${ticket._numeroFila}</button><a class="ticket-id" href="ticket/ticket_detalle.php?id=${Number(ticket.id_ticket)}&vista=${({ usuario: 'usuario', tecnico: 'tecnico', administrador: 'admin' }[state.perfil] || 'usuario')}">Ticket #${Number(ticket.id_ticket)}</a><small>${formatDate(ticket.fecha_creacion)}</small></span>` },
      { key: 'asunto', label: 'Caso', render: ticket => `<span class="dashboard-ticket-main"><strong>${escape(ticket.asunto)}</strong><small>${escape(ticket.colegio)}</small></span>` },
    ];
    if (state.perfil !== 'usuario') columns.push({ key: 'usuario', label: 'Usuario' });
    if (state.perfil !== 'tecnico') columns.push({ key: 'tecnico', label: 'Técnico' });
    columns.push(
      { key: 'estado', label: 'Estado', render: ticket => `<span class="dashboard-ticket-state" style="--state-color:${safeColor(ticket.estado_color)}">${escape(ticket.estado)}</span>` },
      { key: 'fecha_respuesta', label: state.perfil === 'tecnico' || state.perfil === 'usuario' ? 'Fecha estimada' : 'Fecha de respuesta', render: ticket => `<span class="text-nowrap">${state.perfil === 'tecnico' || state.perfil === 'usuario' ? (ticket.fecha_estimada ? escape(ticket.fecha_estimada) : 'Sin fecha') : formatDate(ticket.fecha_respuesta)}</span>` },
      ...(['tecnico', 'usuario'].includes(state.perfil) ? [{ key: 'dias_restantes', label: 'Tiempo restante', render: remainingLabel }] : []),
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
    // Selección por folio, independiente de los enlaces de navegación.
    const table = $('dashboard-ticket-table');
    table?.querySelectorAll('.dashboard-ticket-folio[data-ticket-id]').forEach(folio => {
      const selected = Number(folio.dataset.ticketId) === state.selectedTicketId;
      folio.closest('tr')?.classList.toggle('dashboard-ticket-selected', selected);
      folio.querySelector('.dashboard-ticket-row-number')?.setAttribute('aria-pressed', String(selected));
    });
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
  $('dashboard-ticket-table')?.addEventListener('click', event => {
    const number = event.target.closest('.dashboard-ticket-row-number');
    if (!number) return;
    const folio = number.closest('.dashboard-ticket-folio[data-ticket-id]');
    if (!folio) return;
    const id = Number(folio.dataset.ticketId);
    state.selectedTicketId = state.selectedTicketId === id ? null : id;
    $('dashboard-ticket-table').querySelectorAll('.dashboard-ticket-folio[data-ticket-id]').forEach(item => {
      const selected = Number(item.dataset.ticketId) === state.selectedTicketId;
      item.closest('tr')?.classList.toggle('dashboard-ticket-selected', selected);
      item.querySelector('.dashboard-ticket-row-number')?.setAttribute('aria-pressed', String(selected));
    });
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
