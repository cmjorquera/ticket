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
                        title: '<div class="alert alert-dark" role="alert">AGREGAR USUARIO</div>',
                        html: `
                                <div class="alert alert-secondary" role="alert">
                                    <style>
                                        .menu-item-disponible {
                                            display: flex;
                                            align-items: center;
                                            gap: 10px;
                                            min-height: 50px;
                                        }
                                        .menu-item-disponible .form-check-input {
                                            margin-top: 0;
                                            flex-shrink: 0;
                                        }
                                        .menu-item-disponible .form-check-label {
                                            width: 100%;
                                            display: flex;
                                            align-items: center;
                                            justify-content: space-between;
                                            gap: 12px;
                                            margin-bottom: 0;
                                            font-size: 16px;
                                        }
                                        .menu-item-obligatorio {
                                            border-color: #8bb3ff !important;
                                            background: linear-gradient(180deg, #f7faff 0%, #eef4ff 100%) !important;
                                        }
                                        .menu-badge-obligatorio {
                                            display: inline-flex;
                                            align-items: center;
                                            justify-content: center;
                                            min-width: 96px;
                                            padding: 5px 12px;
                                            border-radius: 999px;
                                            background: #5f8ee6;
                                            color: #fff;
                                            font-size: 12px;
                                            font-weight: 700;
                                            letter-spacing: .02em;
                                            box-shadow: 0 4px 12px rgba(95, 142, 230, .22);
                                            flex-shrink: 0;
                                        }
                                    </style>
                                    <form class="row g-3" id="usuarioForm">              
                                        <div class="col-md-4">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="nombre" placeholder="Nombre">
                                                <label for="nombre">Nombre</label>
                                                <div id="nombre-error" class="text-danger"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="apellidoPaterno" placeholder="Apellido Paterno">
                                                <label for="apellidoPaterno">Apellido Paterno</label>
                                                <div id="apellidoPaterno-error" class="text-danger"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="apellidoMaterno" placeholder="Apellido Materno">
                                                <label for="apellidoMaterno">Apellido Materno</label>
                                                <div id="apellidoMaterno-error" class="text-danger"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-floating mb-3">
                                                <input type="email" class="form-control" id="email" placeholder="Email">
                                                <label for="email">Email</label>
                                                <div id="email-error" class="text-danger"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-floating mb-3">
                                                <select class="form-control" id="area_trabajo">
                                                    ${optionsHtml}
                                                </select>
                                                <label for="area_trabajo">Área de Trabajo</label>
                                                <div id="area_trabajo-error" class="text-danger"></div>
                                            </div>
                                        </div>
                             
                                        <div class="col-md-4">
                                            <div class="form-floating mb-3">
                                                <select class="form-control" id="sexo">
                                                    <option value="">Seleccione Sexo</option>
                                                    <option value="1">Masculino</option>
                                                    <option value="2">Femenino</option>
                                                </select>
                                                <label for="sexo">Sexo</label>
                                                <div id="sexo-error" class="text-danger"></div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <select class="form-control" id="id_colegio">
                                                    ${colegiosHtml}
                                                </select>
                                                <label for="id_colegio">Colegio</label>
                                                <div id="id_colegio-error" class="text-danger"></div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="border rounded bg-light p-3 text-start">
                                                <div class="fw-bold mb-2">Menus disponibles</div>
                                                <div class="row g-2">
                                                    ${menusHtml}
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            `,
                        showCancelButton: true,
                        width: "980px",
                        padding: "20px",
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
                            $('#email').blur(function () {
                                const email = $(this).val();
                                if (email) {
                                    $.ajax({
                                        url: configuracionUrl('modelos/rescatar/creando_usuario.php'),
                                        type: 'GET',
                                        dataType: 'json',
                                        data: {
                                            email
                                        },
                                        success: function (response) {
                                            if (response.email_exists) {
                                                Swal.showValidationMessage(
                                                    `Usuario existe y se llama ${response.email_owner}`
                                                );
                                            }
                                        }
                                    });
                                }
                            }).on('input', function () {
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
