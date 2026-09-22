(() => {
  'use strict';

  const iconEndpoint = '../pages/modelos/rescatar/obtener_icono_colegio.php';
  const departmentsEndpoint = '../pages/modelos/rescatar/departamentos_por_colegio.php';
  const saveEndpoint = 'ajax/guardar_departamento.php';

  async function getJson(url, options = {}) {
    const response = await fetch(url, { credentials: 'same-origin', ...options });
    const data = await response.json().catch(() => null);
    if (!response.ok || data === null) {
      throw new Error(data?.message || data?.error || 'El servidor entregó una respuesta inválida.');
    }
    return data;
  }

  function createEmptyDepartment(department) {
    const section = document.createElement('section');
    section.className = 'org-department-group';
    section.dataset.departmentId = String(department.id);
    section.innerHTML = `
      <button type="button" class="org-department-group__header" data-department-toggle aria-expanded="true">
        <i class="bi bi-diagram-3" aria-hidden="true"></i>
        <h3></h3>
        <span class="org-department-count">0</span>
        <i class="bi bi-chevron-down org-department-chevron" aria-hidden="true"></i>
      </button>
      <div class="org-department-panel">
        <div class="org-users"><p class="text-muted">No hay usuarios asignados.</p></div>
      </div>`;
    section.querySelector('h3').textContent = department.nombre + (department.sigla ? ` (${department.sigla})` : '');
    return section;
  }

  async function loadSchoolIcon(holder) {
    if (holder.dataset.iconLoaded === '1') return;
    holder.dataset.iconLoaded = '1';
    try {
      const data = await getJson(`${iconEndpoint}?id_colegio=${encodeURIComponent(holder.dataset.schoolLogo)}`);
      holder.replaceChildren();
      if (data.existe && data.icono) {
        const image = document.createElement('img');
        image.src = data.icono;
        image.alt = '';
        image.width = 48;
        image.height = 48;
        holder.appendChild(image);
      } else {
        holder.textContent = data.sigla || data.iniciales || holder.dataset.fallback || 'C';
      }
    } catch (_) {
      holder.textContent = holder.dataset.fallback || 'C';
    }
  }

  async function reloadSchoolDepartments(schoolId) {
    const school = document.querySelector(`.org-school[data-school-id="${Number(schoolId)}"]`);
    const container = school?.querySelector('[data-school-departments]');
    if (!container) return [];

    const departments = await getJson(`${departmentsEndpoint}?id_colegio=${encodeURIComponent(schoolId)}`);
    if (!Array.isArray(departments)) throw new Error('No fue posible interpretar los departamentos.');

    departments.forEach(department => {
      let group = container.querySelector(`[data-department-id="${Number(department.id)}"]`);
      if (!group) {
        group = createEmptyDepartment(department);
      }
      const count = group.querySelector('.org-department-count');
      if (count) count.textContent = String(department.usuarios_count || 0);
      container.appendChild(group);
    });

    container.dataset.departmentsLoaded = '1';
    initializeDepartmentButtons(container);
    return departments;
  }

  function toggleDepartment(button) {
    const group = button.closest('.org-department-group');
    if (!group) return;
    const collapsed = group.classList.toggle('is-collapsed');
    button.setAttribute('aria-expanded', String(!collapsed));
  }

  function initializeDepartmentButtons(root = document) {
    root.querySelectorAll('[data-department-toggle]').forEach(button => {
      if (button.dataset.accordionReady === '1') return;
      button.dataset.accordionReady = '1';
      button.addEventListener('click', async () => {
        const school = button.closest('.org-school');
        const container = school?.querySelector('[data-school-departments]');
        if (container?.dataset.departmentsLoaded !== '1' && school?.dataset.schoolId) {
          try {
            await reloadSchoolDepartments(school.dataset.schoolId);
          } catch (error) {
            console.error('No fue posible cargar los departamentos.', error);
          }
        }
        toggleDepartment(button);
      });
    });
  }

  function initializeAddButtons(root = document) {
    root.querySelectorAll('[data-add-department]').forEach(button => {
      if (button.dataset.departmentReady === '1') return;
      button.dataset.departmentReady = '1';
      button.addEventListener('click', () => {
        abrirModalAgregarDepto(Number(button.dataset.schoolId), button.dataset.schoolName || 'Colegio');
      });
    });
  }

  function initializeToggleAllButtons(root = document) {
    root.querySelectorAll('[data-toggle-all-departments]').forEach(button => {
      if (button.dataset.toggleAllReady === '1') return;
      button.dataset.toggleAllReady = '1';
      button.addEventListener('click', async () => {
        const school = button.closest('.org-school');
        const container = school?.querySelector('[data-school-departments]');
        if (!container) return;
        if (container.dataset.departmentsLoaded !== '1' && school.dataset.schoolId) {
          try {
            await reloadSchoolDepartments(school.dataset.schoolId);
          } catch (error) {
            console.error('No fue posible cargar los departamentos.', error);
          }
        }
        const groups = [...container.querySelectorAll('.org-department-group')];
        const expand = groups.some(group => group.classList.contains('is-collapsed'));
        groups.forEach(group => {
          group.classList.toggle('is-collapsed', !expand);
          group.querySelector('[data-department-toggle]')?.setAttribute('aria-expanded', String(expand));
        });
      });
    });
  }

  function inicializarAcordeon(root = document) {
    root.querySelectorAll('[data-school-logo]').forEach(loadSchoolIcon);
    initializeDepartmentButtons(root);
    initializeAddButtons(root);
    initializeToggleAllButtons(root);
  }

  function abrirModalAgregarDepto(idColegio, nombreColegio = 'Colegio') {
    document.getElementById('departamento-id-colegio').value = String(idColegio);
    document.getElementById('modal-departamento-colegio').textContent = nombreColegio;
    document.getElementById('departamento-nombre').value = '';
    document.getElementById('departamento-sigla').value = '';
    document.getElementById('modal-departamento-error').hidden = true;
    openModal('Modal_AgregarDepartamento');
    document.getElementById('departamento-nombre').focus();
  }

  async function saveDepartment() {
    const button = document.getElementById('departamento-guardar');
    const error = document.getElementById('modal-departamento-error');
    const idColegio = Number(document.getElementById('departamento-id-colegio').value || 0);
    const nombre = document.getElementById('departamento-nombre').value.trim();
    const sigla = document.getElementById('departamento-sigla').value.trim().toUpperCase();
    error.hidden = true;

    if (!idColegio || !nombre || !/^[A-ZÁÉÍÓÚÑ0-9]{2,3}$/u.test(sigla)) {
      error.textContent = 'Completa el nombre y una sigla de 2 a 3 caracteres.';
      error.hidden = false;
      return;
    }

    button.disabled = true;
    try {
      const view = document.getElementById('vista-organigrama');
      const result = await getJson(saveEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({
          csrf: view.dataset.departmentCsrf || '',
          id_colegio: idColegio,
          nombre_departamento: nombre,
          sigla,
        }),
      });
      if (!result.success) throw new Error(result.message || 'No fue posible crear el departamento.');
      closeModal('Modal_AgregarDepartamento');
      let recargar = true;
      if (window.Swal?.fire) {
        const confirmacion = await window.Swal.fire({
          icon: 'success',
          title: 'Departamento creado',
          text: '¿Actualizar los departamentos de este colegio?',
          showCancelButton: true,
          confirmButtonText: 'Sí, actualizar',
          cancelButtonText: 'Ahora no',
          confirmButtonColor: '#0069a6',
        });
        recargar = confirmacion.isConfirmed;
      } else {
        recargar = window.confirm('Departamento creado. ¿Actualizar los departamentos de este colegio?');
      }
      if (recargar) {
        await reloadSchoolDepartments(idColegio);
      }
    } catch (requestError) {
      error.textContent = requestError.message;
      error.hidden = false;
    } finally {
      button.disabled = false;
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('departamento-guardar')?.addEventListener('click', saveDepartment);
    inicializarAcordeon(document);
  });

  window.inicializarAcordeon = inicializarAcordeon;
  window.abrirModalAgregarDepto = abrirModalAgregarDepto;
  window.recargarDepartamentosColegio = reloadSchoolDepartments;
})();
