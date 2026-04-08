function configuracionUrl(ruta) {
    const raiz = typeof window.CONFIG_RELATIVE_ROOT === 'string' ? window.CONFIG_RELATIVE_ROOT : '../';
    return raiz + ruta.replace(/^\/+/, '');
}

function agregarUsuario() {
    $.ajax({
        url: configuracionUrl('modelos/rescatar/area_trabajo.php'),
        type: 'GET',
        dataType: 'json',
        success: function (areas) {
            $.ajax({
                url: configuracionUrl('modelos/rescatar/colegio.php'),
                type: 'GET',
                dataType: 'json',
                success: function (colegios) {
                    $.ajax({
                        url: configuracionUrl('modelos/rescatar/menu_1.php'),
                        type: 'GET',
                        dataType: 'json',
                        success: function (menus) {
                            const menusObligatorios = [8, 3];
                            let optionsHtml = '<option value="">Seleccione Área de Trabajo</option>';
                            let colegiosHtml = '<option value="">Seleccione Colegio</option>';
                            areas.forEach(area => {
                                optionsHtml += `<option value="${area.id}">${area.nombre_area}</option>`;
                            });
                            colegios.forEach(colegio => {
                                colegiosHtml += `<option value="${colegio.id}">${colegio.nombre}</option>`;
                            });

                            let menusHtml = '';
                            menus.forEach(menu => {
                                const idMenu = parseInt(menu.id_menu, 10);
                                const esObligatorio = menusObligatorios.includes(idMenu);
                                const checked = esObligatorio ? 'checked' : '';
                                const disabled = esObligatorio ? 'disabled' : '';
                                const fixed = esObligatorio ? '<span class="menu-badge-obligatorio">Obligatorio</span>' : '';

                                menusHtml += `
                                    <div class="col-md-6">
                                        <div class="form-check border rounded bg-white px-3 py-2 h-100 menu-item-disponible ${esObligatorio ? 'menu-item-obligatorio' : ''}">
                                            <input class="form-check-input menu-checkbox" type="checkbox" value="${menu.id_menu}" id="menu_${menu.id_menu}" ${checked} ${disabled}>
                                            <label class="form-check-label ms-2" for="menu_${menu.id_menu}">
                                                ${menu.nombre} ${fixed}
                                            </label>
                                        </div>
                                    </div>
                                `;
                            });

                    Swal.fire({
                        title: '',
                        html: `
                                <div class="swal-usuario">
                                    <style>
                                        .swal-usuario { text-align: left; color: #1f2a44; }
                                        .swal-usuario__header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1.25rem; margin-bottom: 1.35rem; }
                                        .swal-usuario__title-wrap { flex: 1 1 auto; padding-top: 0.35rem; }
                                        .swal-usuario__title { margin: 0; font-size: 2rem; font-weight: 800; color: #1c2740; text-align: center; }
                                        .swal-usuario__college-card { min-width: 240px; max-width: 280px; display: flex; align-items: center; gap: 0.8rem; padding: 0.85rem 1rem; border: 1px solid #d9e4f2; border-radius: 20px; background: linear-gradient(180deg, #ffffff 0%, #f5f9ff 100%); box-shadow: 0 10px 24px rgba(31, 69, 123, 0.08); }
                                        .swal-usuario__college-logo, .swal-usuario__college-fallback { width: 54px; height: 54px; border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #d7e1ee; background: #fff; flex-shrink: 0; }
                                        .swal-usuario__college-logo { object-fit: contain; padding: 5px; }
                                        .swal-usuario__college-fallback { font-weight: 800; color: #31527d; background: #edf4fb; }
                                        .swal-usuario__college-name { font-size: 1.02rem; font-weight: 800; color: #1e2d47; line-height: 1.2; }
                                        .swal-usuario__college-meta { color: #6c7b91; font-size: 0.9rem; margin-top: 0.15rem; }
                                        .swal-usuario__grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.7rem 0.9rem; }
                                        .swal-usuario__field { display: grid; gap: 0.28rem; }
                                        .swal-usuario__field--full { grid-column: 1 / -1; }
                                        .swal-usuario__label { font-size: 0.96rem; font-weight: 800; color: #1f2a44; margin-bottom: 0; }
                                        .swal-usuario__input, .swal-usuario__select { width: 100%; min-height: 48px; border-radius: 14px; border: 1px solid #c7d4e5; background: #eef4ff; color: #1f2a44; padding: 0.72rem 0.9rem; font-size: 0.96rem; outline: none; transition: border-color .2s ease, box-shadow .2s ease, background .2s ease; }
                                        .swal-usuario__input:focus, .swal-usuario__select:focus { border-color: #4f83ff; box-shadow: 0 0 0 0.18rem rgba(79, 131, 255, 0.16); background: #f7faff; }
                                        .swal-usuario__input--email { background: #ffffff; }
                                        .swal-usuario__help { min-height: 16px; font-size: 0.86rem; font-weight: 600; }
                                        .swal-usuario__help--ok { color: #13824c; }
                                        .swal-usuario__help--error { color: #c23a3a; }
                                        .swal-usuario__menu-box { margin-top: 0.55rem; border: 1px solid #d9e4f2; border-radius: 20px; background: #f8fbff; padding: 0.9rem; }
                                        .swal-usuario__menu-title { font-weight: 800; color: #1f2a44; margin-bottom: 0.65rem; }
                                        .menu-item-disponible { display: flex; align-items: center; gap: 10px; min-height: 52px; }
                                        .menu-item-disponible .form-check-input { margin-top: 0; flex-shrink: 0; }
                                        .menu-item-disponible .form-check-label { width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 0; font-size: 15px; }
                                        .menu-item-obligatorio { border-color: #8bb3ff !important; background: linear-gradient(180deg, #f7faff 0%, #eef4ff 100%) !important; }
                                        .menu-badge-obligatorio { display: inline-flex; align-items: center; justify-content: center; min-width: 96px; padding: 5px 12px; border-radius: 999px; background: #5f8ee6; color: #fff; font-size: 12px; font-weight: 700; letter-spacing: .02em; box-shadow: 0 4px 12px rgba(95, 142, 230, .22); flex-shrink: 0; }
                                        .swal-usuario__error { font-size: 0.85rem; color: #c23a3a; min-height: 16px; }
                                        @media (max-width: 900px) { .swal-usuario__header { flex-direction: column; } .swal-usuario__college-card { max-width: 100%; width: 100%; } .swal-usuario__grid { grid-template-columns: 1fr; } }
                                    </style>
                                    <div class="swal-usuario__header">
                                        <div class="swal-usuario__title-wrap">
                                            <h2 class="swal-usuario__title">Agregar usuario</h2>
                                        </div>
                                        <div class="swal-usuario__college-card">
                                            <img src="${configuracionUrl('img/colegios/colegio_0.png')}" alt="" class="swal-usuario__college-logo" id="colegioResumenLogo"
                                                onerror="this.style.display='none'; document.getElementById('colegioResumenFallback').style.display='inline-flex';">
                                            <span class="swal-usuario__college-fallback" id="colegioResumenFallback" style="display:none;">--</span>
                                            <div>
                                                <div class="swal-usuario__college-name" id="colegioResumenNombre">Selecciona un colegio</div>
                                                <div class="swal-usuario__college-meta" id="colegioResumenMeta">ID colegio: --</div>
                                            </div>
                                        </div>
                                    </div>
                                    <form class="swal-usuario__grid" id="usuarioForm">
                                        <div class="swal-usuario__field">
                                            <label class="swal-usuario__label" for="email">Email</label>
                                            <input type="email" class="swal-usuario__input swal-usuario__input--email" id="email" placeholder="usuario@correo.cl">
                                            <div id="emailEstado" class="swal-usuario__help"></div>
                                            <div id="email-error" class="swal-usuario__error"></div>
                                        </div>
                                        <div class="swal-usuario__field">
                                            <label class="swal-usuario__label" for="area_trabajo">Departamento</label>
                                            <select class="swal-usuario__select" id="area_trabajo">${optionsHtml}</select>
                                            <div id="area_trabajo-error" class="swal-usuario__error"></div>
                                        </div>
                                        <div class="swal-usuario__field">
                                            <label class="swal-usuario__label" for="nombre">Nombre</label>
                                            <input type="text" class="swal-usuario__input" id="nombre" placeholder="Nombre">
                                            <div id="nombre-error" class="swal-usuario__error"></div>
                                        </div>
                                        <div class="swal-usuario__field">
                                            <label class="swal-usuario__label" for="apellidoPaterno">Apellido paterno</label>
                                            <input type="text" class="swal-usuario__input" id="apellidoPaterno" placeholder="Apellido paterno">
                                            <div id="apellidoPaterno-error" class="swal-usuario__error"></div>
                                        </div>
                                        <div class="swal-usuario__field">
                                            <label class="swal-usuario__label" for="apellidoMaterno">Apellido materno</label>
                                            <input type="text" class="swal-usuario__input" id="apellidoMaterno" placeholder="Apellido materno">
                                            <div id="apellidoMaterno-error" class="swal-usuario__error"></div>
                                        </div>
                                        <div class="swal-usuario__field">
                                            <label class="swal-usuario__label" for="id_colegio">Colegio</label>
                                            <select class="swal-usuario__select" id="id_colegio">${colegiosHtml}</select>
                                            <div id="id_colegio-error" class="swal-usuario__error"></div>
                                        </div>
                                        <div class="swal-usuario__field">
                                            <label class="swal-usuario__label" for="sexo">Sexo</label>
                                            <select class="swal-usuario__select" id="sexo">
                                                <option value="">Seleccione sexo</option>
                                                <option value="1">Masculino</option>
                                                <option value="2">Femenino</option>
                                            </select>
                                            <div id="sexo-error" class="swal-usuario__error"></div>
                                        </div>
                                        <div class="swal-usuario__field swal-usuario__field--full">
                                            <div class="swal-usuario__menu-box">
                                                <div class="swal-usuario__menu-title">Menus disponibles</div>
                                                <div class="row g-2">${menusHtml}</div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            `,
                        showCancelButton: true,
                        width: "980px",
                        padding: "24px",
                        confirmButtonColor: '#0b6aa2',
                        cancelButtonColor: '#e9edf3',
                        confirmButtonText: 'Crear usuario',
                        cancelButtonText: 'Cancelar',
                        customClass: {
                            popup: 'cuerpo_modal_guardar'
                        },
                        preConfirm: () => {
                            const nombre = document.getElementById('nombre');
                            const apellidoPaterno = document.getElementById('apellidoPaterno');
                            const apellidoMaterno = document.getElementById('apellidoMaterno');
                            const email = document.getElementById('email');
                            const areaTrabajo = document.getElementById('area_trabajo');
                            const sexo = document.getElementById('sexo');
                            const idColegio = document.getElementById('id_colegio');
                            const menuIds = Array.from(document.querySelectorAll('.menu-checkbox:checked')).map(item => item.value);

                            if (!menuIds.includes('8')) {
                                menuIds.push('8');
                            }
                            if (!menuIds.includes('3')) {
                                menuIds.push('3');
                            }

                            if (!validarCamposVacios(nombre, apellidoPaterno, email, areaTrabajo, sexo, idColegio)) {
                                Swal.showValidationMessage('Por favor, complete los campos obligatorios en rojo.');
                                return false;
                            }

                            return new Promise((resolve) => {
                                $.ajax({
                                    url: configuracionUrl('modelos/guardar/guardar_usuario.php'),
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        email: email.value
                                    },
                                    success: function (response) {
                                        if (response.email_exists) {
                                            Swal.showValidationMessage(
                                                `Usuario existe y se llama ${response.email_owner}`
                                            );
                                            resolve(false);
                                        } else {
                                            resolve({
                                                action: 'agregarUsuario',
                                                nombre: nombre.value,
                                                apellidoPaterno: apellidoPaterno.value,
                                                apellidoMaterno: apellidoMaterno.value,
                                                email: email.value,
                                                sexo: sexo.value,
                                                areaTrabajo: areaTrabajo.value,
                                                idColegio: idColegio.value,
                                                menuIds: menuIds
                                            });
                                        }
                                    },
                                    error: function () {
                                        Swal.showValidationMessage(
                                            'Error al validar los datos. Por favor, intente nuevamente.'
                                        );
                                        resolve(false);
                                    }
                                });
                            });
                        },
                        didOpen: () => {
                            const popup = Swal.getPopup();
                            const emailInput = popup.querySelector('#email');
                            const emailEstado = popup.querySelector('#emailEstado');
                            const colegioSelect = popup.querySelector('#id_colegio');
                            const colegioNombre = popup.querySelector('#colegioResumenNombre');
                            const colegioMeta = popup.querySelector('#colegioResumenMeta');
                            const colegioLogo = popup.querySelector('#colegioResumenLogo');
                            const colegioFallback = popup.querySelector('#colegioResumenFallback');
                            const confirmButton = popup.querySelector('.swal2-confirm');
                            const cancelButton = popup.querySelector('.swal2-cancel');

                            popup.style.borderRadius = '28px';
                            popup.style.boxShadow = '0 28px 60px rgba(20, 42, 82, 0.20)';

                            if (confirmButton) {
                                confirmButton.style.borderRadius = '16px';
                                confirmButton.style.padding = '0.9rem 1.3rem';
                                confirmButton.style.fontWeight = '800';
                            }

                            if (cancelButton) {
                                cancelButton.style.borderRadius = '16px';
                                cancelButton.style.padding = '0.9rem 1.3rem';
                                cancelButton.style.fontWeight = '700';
                                cancelButton.style.color = '#1f2a44';
                            }

                            function actualizarResumenColegio() {
                                const opcion = colegioSelect.options[colegioSelect.selectedIndex];
                                const idColegio = opcion ? opcion.value : '';
                                const nombre = opcion ? opcion.text : 'Selecciona un colegio';

                                colegioNombre.textContent = idColegio ? nombre : 'Selecciona un colegio';
                                colegioMeta.textContent = idColegio ? `ID colegio: ${idColegio}` : 'ID colegio: --';
                                colegioFallback.textContent = idColegio || '--';

                                if (idColegio) {
                                    colegioLogo.style.display = 'inline-flex';
                                    colegioLogo.src = configuracionUrl(`img/colegios/colegio_${idColegio}.png`);
                                    colegioFallback.style.display = 'none';
                                } else {
                                    colegioLogo.style.display = 'none';
                                    colegioFallback.style.display = 'inline-flex';
                                }
                            }

                            let emailValidationRequest = null;
                            function validarEmailDisponibilidad(email) {
                                if (!email) {
                                    emailEstado.textContent = '';
                                    emailEstado.className = 'swal-usuario__help';
                                    return;
                                }

                                if (emailValidationRequest && typeof emailValidationRequest.abort === 'function') {
                                    emailValidationRequest.abort();
                                }

                                emailEstado.textContent = 'Validando correo...';
                                emailEstado.className = 'swal-usuario__help';

                                emailValidationRequest = $.ajax({
                                    url: configuracionUrl('modelos/rescatar/creando_usuario.php'),
                                    type: 'GET',
                                    dataType: 'json',
                                    data: { email },
                                    success: function (response) {
                                        if (response.email_exists) {
                                            emailEstado.textContent = `Usuario existe y se llama ${response.email_owner}`;
                                            emailEstado.className = 'swal-usuario__help swal-usuario__help--error';
                                            Swal.showValidationMessage(`Usuario existe y se llama ${response.email_owner}`);
                                        } else {
                                            emailEstado.textContent = 'Correo disponible.';
                                            emailEstado.className = 'swal-usuario__help swal-usuario__help--ok';
                                            Swal.resetValidationMessage();
                                        }
                                    },
                                    error: function () {
                                        emailEstado.textContent = 'No se pudo validar el correo.';
                                        emailEstado.className = 'swal-usuario__help swal-usuario__help--error';
                                    }
                                });
                            }

                            actualizarResumenColegio();
                            $(colegioSelect).on('change', actualizarResumenColegio);

                            $(emailInput).on('blur', function () {
                                validarEmailDisponibilidad($(this).val().trim());
                            }).on('input', function () {
                                emailEstado.textContent = '';
                                emailEstado.className = 'swal-usuario__help';
                                Swal.resetValidationMessage();
                            });
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const data = result.value;
                            if (data) {
                                $.ajax({
                                    url: configuracionUrl('modelos/guardar/guardar_usuario.php'),
                                    type: 'POST',
                                    data: data,
                                    success: function (response) {
                                        let mensaje = 'Usuario agregado correctamente.';

                                        try {
                                            const respuesta = typeof response === 'string' ? JSON.parse(response) : response;
                                            if (respuesta.message) {
                                                mensaje = respuesta.message;
                                            }
                                        } catch (e) {}

                                        Swal.fire('Guardado', mensaje, 'success');
                                    },
                                    error: function (xhr, status, error) {
                                        console.error('Error al guardar el usuario:', error);
                                        Swal.fire('Error',
                                            'No se pudo agregar el usuario. Por favor, intente nuevamente.',
                                            'error');
                                    }
                                });
                            }
                        }
                    });
                },
                error: function () {
                    Swal.fire('Error',
                        'No se pudieron cargar los menus disponibles. Por favor, intente nuevamente.',
                        'error');
                }
            });
                },
                error: function () {
                    Swal.fire('Error',
                        'No se pudieron cargar los colegios disponibles. Por favor, intente nuevamente.',
                        'error');
                }
            });
        },
        error: function (xhr, status, error) {
            console.error('Error al obtener las áreas de trabajo:', error);
            Swal.fire('Error',
                'No se pudieron cargar las áreas de trabajo. Por favor, intente nuevamente.',
                'error');
        }
    });
}



function mostrarPermisos(userId) {
    Swal.fire({
        title: '<div class="alert alert-dark" role="alert">PERMISOS PARA EL USUARIO</div>',
        html: '<div id="permisosContent"></div>',
        width: '800px',
        showCancelButton: true,
        confirmButtonColor: '#5B8E4A',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Guardar cambios',
        cancelButtonText: 'Cerrar',
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_activar_alumno',
            cancelButton: 'bt_activar_alumno'
        },
        preConfirm: () => {
            const permisos = [];
            $('.lock-icon').each(function () {
                const id_menu1 = $(this).attr('id').replace('lockIcon', '');
                const id_tipo_permiso = $(this).hasClass('blue') ? 1 : 3;
                permisos.push({
                    id_menu1,
                    id_tipo_permiso
                });
            });

            return $.ajax({
                url: configuracionUrl('modelos/guardar/guardar_permisos.php'),
                type: 'POST',
                data: {
                    user_id: userId,
                    permisos: JSON.stringify(permisos)
                },
                success: function (response) {
                    console.log("Permisos guardad   os: ", response);
                    Swal.fire({
                        title: '<div class="alert alert-dark" role="alert">PERMISOS GUARDADOS</div>',
                        showConfirmButton: false,
                        showCancelButton: false,
                        timer: 2000, // 2000 milisegundos = 2 segundos
                        timerProgressBar: true, // Muestra una barra de progreso que indica el tiempo restante
                        customClass: {
                            popup: 'cuerpo_modal_guardar'
                        },
                    });
                },
                error: function (xhr, status, error) {
                    console.error("Error al guardar los permisos: ", status, error);
                }
            });
        },
        didOpen: () => {
            $.ajax({
                url: configuracionUrl('modelos/rescatar/menu_1.php'),
                type: 'GET',
                data: {
                    user_id: userId
                },
                success: function (response) {
                    console.log("Respuesta AJAX recibida: ", response);
                    try {
                        var data = JSON.parse(response);
                        var html = `
                                <style>
                                    .submenu-container {
                                        display: flex;
                                        flex-wrap: wrap;
                                    }
                                    .submenu-item {
                                        margin: 10px;
                                        padding: 10px;
                                        border: 1px solid #ccc;
                                        border-radius: 5px;
                                        cursor: pointer;
                                        text-align: center;
                                        transition: transform 0.2s, box-shadow 0.2s;
                                        background-color: white;
                                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                                    }
                                    .submenu-item.green {
                                        background-color: #90ee90; /* Verde */
                                        color: white;
                                        transform: translateY(-5px);
                                        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
                                    }
                                    .submenu-item.red {
                                        background-color: #F9856C; /* Rojo */
                                        color: white;
                                    }
                                    .lock-icon.red {
                                        color: red; /* Rojo */
                                        font-size: 1.5em; /* Ajusta el tamaño del icono */
                                    }
                                    .lock-icon.blue {
                                        color: #90ee90; /* Azul */
                                        font-size: 1.5em; /* Ajusta el tamaño del icono */
                                    }
                                </style>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Menú</th>
                                                <th>Submenús</th>
                                                <th>Permiso</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;

                        data.forEach(function (menu) {
                            var submenusHtml = '';
                            if (menu.submenus.length > 0) {
                                submenusHtml += '<div class="submenu-container">';
                                menu.submenus.forEach(function (submenu) {
                                    submenusHtml += `
                                            <div class="submenu-item" onclick="toggleSubmenuColor(this)">
                                                ${submenu.nombre}
                                            </div>`;
                                });
                                submenusHtml += '</div>';
                            }

                            var lockIcon = '';
                            if (menu.id_tipo_permiso == 1) {
                                lockIcon = `<i class="bi bi-unlock lock-icon blue" id="lockIcon${menu.id_menu}" onclick="toggleLock(${menu.id_menu})"></i>`;
                            } else if (menu.id_tipo_permiso == 2 || menu.id_tipo_permiso == 3) {
                                lockIcon = `<i class="bi bi-lock lock-icon red" id="lockIcon${menu.id_menu}" onclick="toggleLock(${menu.id_menu})"></i>`;
                            }

                            html += `
                                    <tr>
                                        <td>${menu.nombre}</td>
                                        <td>${submenusHtml}</td>
                                        <td>${lockIcon}</td>
                                    </tr>`;
                        });

                        html += '</tbody></table></div>';
                        $('#permisosContent').html(html);
                    } catch (e) {
                        console.error("Error al parsear la respuesta: ", e);
                        $('#permisosContent').html('Error al cargar los permisos. Por favor, intente nuevamente.');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error AJAX: ", status, error);
                    $('#permisosContent').html('Error al cargar los permisos. Por favor, intente nuevamente.');
                }
            });
        }
    });
}

function estadoUsuario(userId, currentState) {
    if (currentState === 'Activo') {
        openDeactivateModal(userId);
        return;
    }

    openActivateModal(userId);
}

function openActivateModal(userId) {
    Swal.fire({
        title: '<div class="alert alert-dark" role="alert">CONFIRMACION</div>',
        text: '¿Realmente desea activar al usuario?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#5B8E4A',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ACTIVAR',
        cancelButtonText: 'CANCELAR',
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_activar_alumno',
            cancelButton: 'bt_activar_alumno'
        }
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            url: configuracionUrl('modelos/guardar/guardar_usuario.php'),
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'activarUsuario',
                userId: userId
            },
            success: function (response) {
                if (!response.success) {
                    Swal.fire('Error', response.message || 'No se pudo activar el usuario.', 'error');
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: '<div class="alert alert-dark" role="alert">USUARIO ACTIVADO</div>',
                    showConfirmButton: false,
                    timer: 1800,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'cuerpo_modal_guardar'
                    }
                }).then(() => {
                    location.reload();
                });
            },
            error: function (xhr, status, error) {
                console.error('Error al activar el usuario:', error);
                Swal.fire('Error', 'No se pudo activar el usuario. Por favor, intente nuevamente.', 'error');
            }
        });
    });
}

function openDeactivateModal(userId) {
    $.ajax({
        url: configuracionUrl('modelos/rescatar/razones_bloqueo.php'),
        type: 'GET',
        dataType: 'json',
        success: function (razones) {
            let optionsHtml = '';
            razones.forEach((razon) => {
                optionsHtml += `<option value="${razon.id}">${razon.razon}</option>`;
            });

            Swal.fire({
                title: 'Seleccionar una opción',
                html: `
                    <select id="userSelect" class="swal2-input">
                        ${optionsHtml}
                    </select>
                    <textarea id="userTextarea" class="swal2-textarea" style="display: none;" placeholder="Escribe tu comentario"></textarea>
                `,
                showCancelButton: true,
                confirmButtonColor: '#5B8E4A',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'cuerpo_modal_guardar',
                    confirmButton: 'bt_activar_alumno',
                    cancelButton: 'bt_activar_alumno'
                },
                preConfirm: () => {
                    const selectedOption = document.getElementById('userSelect').value;
                    const textareaValue = document.getElementById('userTextarea').value;

                    if (selectedOption === 'otra' && !textareaValue) {
                        Swal.showValidationMessage('Es necesario escribir un comentario para esta opción.');
                        return false;
                    }

                    return {
                        selectedOption,
                        textareaValue
                    };
                },
                didOpen: () => {
                    const userSelect = document.getElementById('userSelect');
                    const userTextarea = document.getElementById('userTextarea');
                    userSelect.addEventListener('change', () => {
                        userTextarea.style.display = userSelect.value === 'otra' ? 'block' : 'none';
                    });
                }
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: configuracionUrl('modelos/guardar/guardar_usuario.php'),
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'bloquearUsuario',
                        userId: userId,
                        selectedOption: result.value.selectedOption,
                        textareaValue: result.value.textareaValue
                    },
                    success: function (response) {
                        if (!response.success) {
                            Swal.fire('Error', response.message || 'No se pudo bloquear el usuario.', 'error');
                            return;
                        }

                        Swal.fire({
                            icon: 'success',
                            title: '<div class="alert alert-dark" role="alert">USUARIO BLOQUEADO</div>',
                            showConfirmButton: false,
                            timer: 1800,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'cuerpo_modal_guardar'
                            }
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error('Error al bloquear el usuario:', error);
                        Swal.fire('Error', 'No se pudieron guardar los datos. Por favor, intente nuevamente.', 'error');
                    }
                });
            });
        },
        error: function (xhr, status, error) {
            console.error('Error al obtener las razones de bloqueo:', error);
            Swal.fire('Error', 'No se pudieron cargar las razones de bloqueo. Por favor, intente nuevamente.', 'error');
        }
    });
}

function modificarUsuario(userId) {
    $.ajax({
        url: configuracionUrl('modelos/rescatar/usuariModificacion.php'),
        type: 'POST',
        dataType: 'json',
        data: { id: userId },
        success: function (data) {
            Swal.fire({
                title: '<div class="alert alert-dark" role="alert">MODIFICAR USUARIO</div>',
                html: `
                    <div class="alert alert-secondary" role="alert">
                        <form class="row g-3" id="usuarioForm">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre">
                                    <label for="nombre">Nombre</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="apellidoPaterno" name="apellidoPaterno" placeholder="Apellido Paterno">
                                    <label for="apellidoPaterno">Apellido Paterno</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="apellidoMaterno" name="apellidoMaterno" placeholder="Apellido Materno">
                                    <label for="apellidoMaterno">Apellido Materno</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email">
                                    <label for="email">Email</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Telefono">
                                    <label for="telefono">Telefono</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-control" id="area_trabajo" name="areaTrabajo">
                                        <option value="">Seleccione Area de Trabajo</option>
                                    </select>
                                    <label for="area_trabajo">Area de Trabajo</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="anexo" name="anexo" placeholder="Anexo">
                                    <label for="anexo">Anexo</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-control" id="sexo" name="sexo">
                                        <option value="">Seleccione Sexo</option>
                                        <option value="1">Masculino</option>
                                        <option value="2">Femenino</option>
                                    </select>
                                    <label for="sexo">Sexo</label>
                                </div>
                            </div>
                        </form>
                    </div>
                `,
                showCancelButton: true,
                width: '870px',
                padding: '20px',
                confirmButtonColor: '#5B8E4A',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Guardar cambios',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'cuerpo_modal_guardar',
                    confirmButton: 'bt_activar_alumno',
                    cancelButton: 'bt_activar_alumno'
                },
                didOpen: () => {
                    $('#nombre').val(data.nombre || '');
                    $('#apellidoPaterno').val(data.apellido_paterno || '');
                    $('#apellidoMaterno').val(data.apellido_materno || '');
                    $('#email').val(data.email || '');
                    $('#telefono').val(data.telefono || '');
                    $('#anexo').val(data.anexo || '');
                    $('#sexo').val(data.sexo || '');

                    let optionsHtml = '<option value="">Seleccione Area de Trabajo</option>';
                    (data.areas || []).forEach((area) => {
                        const selected = String(area.id) === String(data.id_area_trabajo) ? ' selected' : '';
                        optionsHtml += `<option value="${area.id}"${selected}>${area.nombre_area}</option>`;
                    });
                    $('#area_trabajo').html(optionsHtml);
                },
                preConfirm: () => {
                    const nombre = document.getElementById('nombre');
                    const apellidoPaterno = document.getElementById('apellidoPaterno');
                    const email = document.getElementById('email');

                    if (!validarCamposVacios(nombre, apellidoPaterno, email)) {
                        Swal.showValidationMessage('Por favor, complete los campos obligatorios en rojo.');
                        return false;
                    }

                    return {
                        action: 'modificarUsuario',
                        userId: userId,
                        nombre: nombre.value,
                        apellidoPaterno: apellidoPaterno.value,
                        apellidoMaterno: document.getElementById('apellidoMaterno').value,
                        email: email.value,
                        telefono: document.getElementById('telefono').value,
                        clave: '',
                        anexo: document.getElementById('anexo').value,
                        areaTrabajo: document.getElementById('area_trabajo').value,
                        sexo: document.getElementById('sexo').value
                    };
                }
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: configuracionUrl('modelos/guardar/guardar_usuario.php'),
                    type: 'POST',
                    data: result.value,
                    dataType: 'json',
                    success: function (response) {
                        if (!response.success) {
                            Swal.fire('Error', response.message || 'No se pudo guardar los cambios del usuario.', 'error');
                            return;
                        }

                        Swal.fire({
                            title: '<div class="alert alert-dark" role="alert">USUARIO ACTUALIZADO</div>',
                            width: '570px',
                            padding: '40px',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1800,
                            customClass: {
                                popup: 'cuerpo_modal_guardar'
                            },
                            willClose: () => {
                                location.reload();
                            }
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error('Error al guardar los cambios del usuario:', error);
                        Swal.fire('Error', 'No se pudo guardar los cambios del usuario. Por favor, intente nuevamente.', 'error');
                    }
                });
            });
        },
        error: function (xhr, status, error) {
            console.error('Error al obtener los datos del usuario:', error);
            Swal.fire('Error', 'No se pudo cargar la informacion del usuario. Por favor, intente nuevamente.', 'error');
        }
    });
}

function confirmarReenvioActivacion(userId, email) {
    Swal.fire({
        title: 'Reenviar activacion',
        text: `¿Desea reenviar el correo de activacion de cuenta a ${email}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Si, reenviar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'cuerpo_modal_guardar'
        }
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            url: configuracionUrl('modelos/guardar/guardar_usuario.php'),
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'reenviarActivacion',
                userId: userId
            },
            success: function (response) {
                if (!response.success) {
                    Swal.fire('Error', response.message || 'No se pudo reenviar el correo de activacion.', 'error');
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Correo reenviado',
                    text: response.message || 'Correo de activacion reenviado correctamente.',
                    confirmButtonText: 'Entendido',
                    customClass: {
                        popup: 'cuerpo_modal_guardar'
                    }
                });
            },
            error: function (xhr, status, error) {
                console.error('Error al reenviar el correo de activacion:', error);
                Swal.fire('Error', 'No se pudo reenviar el correo de activacion. Por favor, intente nuevamente.', 'error');
            }
        });
    });
}
