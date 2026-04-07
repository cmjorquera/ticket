



// let modalTimer; // Variable para el temporizador del modal

function cerrar_session() {
    Swal.fire({
        title: `<div class="alert alert-dark" role="alert">¿CERRAR SESIÓN?</div>`,
        text: '¿Seguro que quieres cerrar tu sesión?',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: 'CERRAR',
        cancelButtonText: 'CANCELAR',
        confirmButtonColor: '#5B8E4A',
        // cancelButtonColor: '#d33',
        allowOutsideClick: false,
        // allowEscapeKey: false,
        // allowEnterKey: false,
        width: "570px",
        padding: "40px",
        showCloseButton: true,
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_activar_alumno',
            cancelButton: 'bt_activar_alumno'
        },
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirigir a cerrar_sesion.php para destruir la sesión y redirigir a index.php
            window.location.href = 'cerrar_session.php';
        } else if (result.isDismissed) {
            // Lógica para cuando se cancela el cierre de sesión
            console.log('Cierre de sesión cancelado');
        }
    });
}



// function showModal() {
//     clearTimeout(modalTimer); // Limpia el temporizador del modal anterior si existe
//     Swal.fire({
//         title: '<div class="alert alert-dark" role="alert">¿CERRAR SESIÓN?</div>',
//         text: '¿Seguro que quieres cerrar tu sesión?',
//         showCancelButton: true,
//         confirmButtonColor: '#5B8E4A',
//         cancelButtonColor: '#d33',
//         confirmButtonText: 'CONTINUAR',
//         cancelButtonText: 'CERRAR SESSION',
//         customClass: {
//             popup: 'cuerpo_modal_guardar',
//             confirmButton: 'bt_activar_alumno',
//             cancelButton: 'bt_activar_alumno'
//         }
//     }).then((result) => {
//         clearTimeout(modalTimer); // Asegúrate de limpiar el temporizador cuando el modal se cierre
//         if (result.isDismissed || result.dismiss === Swal.DismissReason.cancel) {
//             window.location.href = 'index.php'; // Redirecciona al index.php si el usuario elige cerrar sesión
//         } else {
//             resetTimer(); // Reinicia el temporizador si el usuario elige continuar
//         }
//     });

//     modalTimer = setTimeout(() => {
//         window.location.href = 'index.php'; // Redirecciona si no hay respuesta (no apreto no activar ni cerrar) en 20 segundos
//     }, 20 * 1000);
// }

// function modalCerrarSession() {
//     let timer; // Variable para el temporizador de inactividad

//     clearTimeout(timer); // Limpiar el temporizador de inactividad
//     timer = setTimeout(showModal, 300 *
//         1000); // Establecer el temporizador para mostrar el modal después de 300  segundos de inactividad (5 minutos)
// }

// Reiniciar el temporizador cuando haya actividad del usuario
// function resetTimer() {
//     clearTimeout(timer);
//     modalCerrarSession();
// }



function soloLetras(evento) {
    // Obtener el valor del input
    const valor = evento.target.value;

    // Expresión regular para validar solo letras (incluyendo espacios)
    const soloLetrasRegex = /^[a-zA-Z\s]+$/;

    // Verificar si el valor cumple con la expresión regular
    const esValido = soloLetrasRegex.test(valor);

    // Si no es válido (contiene números o caracteres especiales)
    if (!esValido) {
        // Obtener el valor sin los caracteres no permitidos
        const valorSinNumeros = valor.replace(/[^a-zA-Z\s]/g, '');

        // Actualizar el valor del input con el valor sin los caracteres no permitidos
        evento.target.value = valorSinNumeros;
    }
}



// Agregar eventos de escucha para el movimiento del mouse y la interacción del usuario
// document.addEventListener('mousemove', resetTimer);
// document.addEventListener('keypress', resetTimer);
// document.addEventListener('DOMContentLoaded', modalCerrarSession);
// <!-- ***************************************************************************** -->
// <!-- **************CERRAR SESSION************************************************** -->
function actualizar_pag() {
    window.location.reload(true); // El 'true' fuerza la recarga sin caché desde el servidor
}
function mostrarModalConfirmacion() {
    return Swal.fire({
        title: '<div class="alert alert-dark" role="alert">TICKET CREADOoooooooooooo</div>',
        width: "5700px",
        height: "800px",
        padding: "40px",
        showCancelButton: false,
        showConfirmButton: false,
        timer: 2000,
        customClass: {
            popup: 'cuerpo_modal_guardar',
        }
    });
}
function confirmarEliminacion() {
    return Swal.fire({
        title: '<div class="alert alert-dark" role="alert">¿ESTÁS SEGURO?</div>',
        text: "No podrás revertir esto!",
        width: '570px',
        padding: '40px',
        showCancelButton: true,
        confirmButtonColor: '#5B8E4A',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ELIMINAR',
        cancelButtonText: 'CANCELAR',
        customClass: {
            popup: 'cuerpo_modal_eliminar',        
                confirmButton: 'btn-modal-eliminar',
            cancelButton: 'bt_activar_alumno'
        }
    });
}

function mostrarModalEliminacionConfirmacion() {
    return Swal.fire({
        title: '<div class="alert alert-dark" role="alert">TICKET ELIMINADO</div>',
        width: "570px",
        height: "800px",
        padding: "40px",
        showCancelButton: false,
        showConfirmButton: false,
        timer: 2000,
        customClass: {
            popup: 'cuerpo_modal_guardar',
        }
    });
}

function validaTexto(id, error) {
    var er = "";
    var campo = document.getElementById(id);
    campo.classList.remove("campo-invalido"); // Elimina la clase de campo inválido antes de realizar la validación
    campo.style.background = "#FFFFFF"; // Restablece el color de fondo a blanco

    if (campo.value.trim() === "") {
        campo.classList.add("campo-invalido"); // Agrega la clase de campo inválido para resaltar el borde en rojo
        er = error; // Establece el mensaje de error
    }

    // Agrega un controlador de eventos para revertir el color del borde cuando el usuario comienza a escribir
    campo.addEventListener('input', function () {
        this.classList.remove("campo-invalido"); // Elimina la clase de campo inválido
    });

    return er; // Retorna el mensaje de error (vacío si no hay error)
}
function validarCamposVacios(...elementos) {
    let camposValidos = true;
    elementos.forEach(elemento => {
        if (!elemento.value.trim()) {
            elemento.style.borderColor = 'red';
            camposValidos = false;
        } else {
            elemento.style.borderColor = ''; // Restablecer el borde si no está vacío
        }
    });
    return camposValidos;
}
//******************permisos.php*********************** */ 
//*************AGREGAR USUARIO******************************* */

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
                url: 'modelos/guardar/guardar_permisos.php',
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
                url: 'modelos/rescatar/menu_1.php',
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
function toggleSubmenuColor(element) {
    if (element.classList.contains('green')) {
        element.classList.remove('green');
        element.classList.add('red');
    } else if (element.classList.contains('red')) {
        element.classList.remove('red');
        element.classList.add('green');
    } else {
        element.classList.add('green');
    }
}
function toggleLock(menuId) {
    var lockIcon = document.getElementById('lockIcon' + menuId);
    if (lockIcon.classList.contains('bi-unlock')) {
        lockIcon.classList.remove('bi-unlock');
        lockIcon.classList.add('bi-lock');
        lockIcon.classList.remove('blue');
        lockIcon.classList.add('red');
    } else {
        lockIcon.classList.remove('bi-lock');
        lockIcon.classList.add('bi-unlock');
        lockIcon.classList.remove('red');
        lockIcon.classList.add('blue');
    }
}
function estadoUsuario(userId, currentState) {
    if (currentState === 'Bloqueado') {
        openActivateModal(userId);
    } else {
        openDeactivateModal(userId);
    }
}
function openActivateModal(userId) {
    Swal.fire({
        title: '<div class="alert alert-dark" role="alert">CONFIRMACION</div>',
        text: "¿Realmente desea activar al usuario?",
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
        if (result.isConfirmed) {
            $.ajax({
                url: 'modelos/guardar/guardar_usuario.php',
                type: 'POST',
                data: {
                    action: 'activarUsuario',
                    userId: userId
                },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: '<div class="alert alert-dark" role="alert">Usuario Activado</div>',
                        showConfirmButton: false,
                        showCancelButton: false,
                        timer: 2000, // 2000 milisegundos = 2 segundos
                        timerProgressBar: true, // Muestra una barra de progreso que indica el tiempo restante
                        customClass: {
                            popup: 'cuerpo_modal_guardar'
                        }
                    }).then(() => {
                        location
                            .reload(); // Recargar la página después de cerrar el mensaje
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Error al activar el usuario:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo activar el usuario. Por favor, intente nuevamente.',
                        icon: 'error'
                    });
                }
            });
        }
    });
}

function openDeactivateModal(userId) {
    $.ajax({
        url: 'modelos/rescatar/razones_bloqueo.php',
        type: 'GET',
        dataType: 'json',
        success: function (razones) {
            let optionsHtml = '';
            razones.forEach(razon => {
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
                        Swal.showValidationMessage(
                            'Es necesario escribir un comentario para esta opción.');
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
                        if (userSelect.value === 'otra') {
                            userTextarea.style.display = 'block';
                        } else {
                            userTextarea.style.display = 'none';
                        }
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'modelos/guardar/guardar_usuario.php',
                        type: 'POST',
                        data: {
                            action: 'bloquearUsuario',
                            userId: userId,
                            selectedOption: result.value.selectedOption,
                            textareaValue: result.value.textareaValue
                        },
                        success: function (response) {
                            Swal.fire({
                                icon: 'success',
                                title: '<div class="alert alert-dark" role="alert">USUARIO BLOQUEADO</div>',
                                showConfirmButton: false,
                                showCancelButton: false,
                                timer: 2000, // 2000 milisegundos = 2 segundos
                                timerProgressBar: true, // Muestra una barra de progreso que indica el tiempo restante
                                customClass: {
                                    popup: 'cuerpo_modal_guardar'
                                }
                            }).then(() => {
                                location
                                    .reload(); // Recargar la página después de cerrar el mensaje
                            });
                        },
                        error: function (xhr, status, error) {
                            console.error('Error al guardar los datos:', error);
                            Swal.fire('Error',
                                'No se pudieron guardar los datos. Por favor, intente nuevamente.',
                                'error');
                        }
                    });
                }
            });
        },
        error: function (xhr, status, error) {
            console.error("Error al obtener las razones de bloqueo:", error);
            Swal.fire('Error',
                'No se pudieron cargar las razones de bloqueo. Por favor, intente nuevamente.',
                'error');
        }
    });
}

function modificarUsuario(userId) {
    $.ajax({
        url: 'modelos/rescatar/usuariModificacion.php',
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
                                    <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono">
                                    <label for="telefono">Teléfono</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-control" id="area_trabajo" name="areaTrabajo">
                                        <option value="">Seleccione Área de Trabajo</option>
                                        <!-- Las opciones se rellenarán dinámicamente -->
                                    </select>
                                    <label for="area_trabajo">Área de Trabajo</label>
                                </div>
                            </div>
                            <!-- Campo Clave 
                            <div class="col-md-4 position-relative">
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="clave" name="clave" placeholder="Clave">
                                    <label for="clave">Clave</label>
                                    <button type="button" class="btn btn-outline-secondary" style="position: absolute; right: 10px; top: 10px;" onclick="mostrarocultarPass('clave','toggleIconClave')">
                                        <i class="bi bi-eye-fill" id="toggleIconClave"></i>
                                    </button>
                                </div>
                            </div>-->
                            <!-- Campo Confirmar Clave
                            <div class="col-md-4 position-relative">
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="confirmarClave" name="confirmarClave" placeholder="Confirmar Clave">
                                    <label for="confirmarClave">Confirmar Clave</label>
                                    <button type="button" class="btn btn-outline-secondary" style="position: absolute; right: 10px; top: 10px;" onclick="mostrarocultarPass('confirmarClave','toggleIconConfirmarClave')">
                                        <i class="bi bi-eye-fill" id="toggleIconConfirmarClave"></i>
                                    </button>
                                </div>
                            </div> -->
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
                width: "870px",
                padding: "20px",
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
                    // Rellenar los campos con los datos del usuario
                    $('#nombre').val(data.nombre);
                    $('#apellidoPaterno').val(data.apellido_paterno);
                    $('#apellidoMaterno').val(data.apellido_materno);
                    $('#email').val(data.email);
                    $('#telefono').val(data.telefono);
                    $('#anexo').val(data.anexo);
                    $('#sexo').val(data.sexo);
                    // $('#clave').val(data.clave);
                    // $('#confirmarClave').val(data.clave);
                    
                    // Llenar el select de áreas de trabajo y marcar el seleccionado
                    let optionsHtml = '';
                    data.areas.forEach(area => {
                        const selected = area.id === data.id_area_trabajo ? ' selected' : '';
                        optionsHtml += `<option value="${area.id}"${selected}>${area.nombre_area}</option>`;
                    });
                    $('#area_trabajo').html(optionsHtml);
                },
                preConfirm: () => {
                    const nombre = document.getElementById('nombre');
                    const apellidoPaterno = document.getElementById('apellidoPaterno');
                    const email = document.getElementById('email');
                    const telefono = document.getElementById('telefono');
                    const clave = document.getElementById('clave');
                    const confirmarClave = document.getElementById('confirmarClave');

                    if (!validarCamposVacios(nombre, apellidoPaterno, email, clave, confirmarClave)) {
                        Swal.showValidationMessage('Por favor, complete los campos obligatorios en rojo.');
                        return false;
                    }

                    if (clave.value !== confirmarClave.value) {
                        confirmarClave.style.borderColor = 'red';
                        Swal.showValidationMessage('Las claves no coinciden');
                        return false;
                    } else {
                        confirmarClave.style.borderColor = '';
                    }

                    return {
                        action: 'modificarUsuario',
                        userId: userId,
                        nombre: nombre.value,
                        apellidoPaterno: apellidoPaterno.value,
                        apellidoMaterno: document.getElementById('apellidoMaterno').value,
                        email: email.value,
                        telefono: telefono.value,
                        clave: clave.value,
                        anexo: document.getElementById('anexo').value,
                        areaTrabajo: document.getElementById('area_trabajo').value,
                        sexo: document.getElementById('sexo').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'modelos/guardar/guardar_usuario.php',
                        type: 'POST',
                        data: result.value,
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    title: '<div class="alert alert-dark" role="alert">USUARIO ACTUALIZADO</div>',
                                    width: "570px",
                                    padding: "40px",
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    customClass: {
                                        popup: 'cuerpo_modal_guardar'
                                    },
                                    willClose: () => {
                                        location.reload();
                                    }
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Error al guardar los cambios del usuario:', error);
                            Swal.fire('Error', 'No se pudo guardar los cambios del usuario. Por favor, intente nuevamente.', 'error');
                        }
                    });
                }
            });
        },
        error: function (xhr, status, error) {
            console.error('Error al obtener los datos del usuario:', error);
            Swal.fire('Error', 'No se pudo cargar la información del usuario. Por favor, intente nuevamente.', 'error');
        }
    });
}


//******************contenedor.php*********************** */ 
//*************CONTENEDOR******************************* */




function agregar_contenedor(idUsuario) {
    const content = document.createElement('div');
    content.className = 'modal-content'; // Se agrega esta clase para aplicar los estilos CSS
    content.innerHTML = `
                                                <div class="alert alert-secondary " role="alert">
                                                    <div class="row">
                                                        <!-- Columna izquierda para cargar y previsualizar imagen -->
                                                        <div class="col-md-6 col-sm-12">
                                                            <div class="input-column">
                                                                <label for="imageInput" class="btn btn-primary">
                                                                    Subir Imagen
                                                                    <input type="file" id="imageInput" accept="image/*" onchange="previewImage(event)">
                                                                </label>
        
                                                                <img id="imagePreview" src="imagenes/logo_contenedor.png" alt="Vista previa de la imagen" style="width: 100%; height: auto; max-height: 200px; margin-top: 10px;">
                                                            </div>
                                                        </div>
        
                                                        <!-- Columna derecha para los inputs de texto -->
                                                        <div class="col-md-6 col-sm-12">
                                                            <div class="input-column">
                                                                <label for="textInput1" class="form-label">Nombre Contenedor:</label>
                                                                <input type="text" id="textInput1" class="form-control mb-3" placeholder="Nombre del contenedor">
                                                                
                                                                <label for="textInput2" class="form-label">URL:</label>
                                                                <i class="fas fa-info-circle" data-bs-toggle="modal" data-bs-target="#videoModal" style="color:red;"></i>
                                                                <input type="text" id="textInput2" class="form-control mb-1" placeholder="www.seduc.cl">
                                                                <!-- Icono que activa un modal -->
                                                            </div>
                                                        </div>
        
                                                        <!-- Modal -->
                                                        <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <video controls width="100%">
                                                                        <source src="imagenes/video_explicativo_2.mp4" type="video/mp4">
                                                                            Video explicativo
                                                                        </video>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> `;
    Swal.fire({
        title: '<div class="alert alert-dark" role="alert">AGREGAR COsssNTENEDOR</div>',
        html: content,
        width: '870px',
        padding: '40px',
        showCancelButton: true,
        confirmButtonColor: '#5B8E4A',
        cancelButtonColor: '#d33',
        confirmButtonText: 'CREAR',
        cancelButtonText: 'CANCELAR',
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_activar_alumno',
            cancelButton: 'bt_activar_alumno'
        },
        preConfirm: () => {
            return new Promise((resolve) => {
                const imageInput = document.getElementById('imageInput').files[0];
                const nombreContenedor = document.getElementById('textInput1').value;
                const url = document.getElementById('textInput2').value;
                if (!nombreContenedor) {
                    Swal.showValidationMessage('Ingrese una nombre a su contenedor.');
                    resolve();
                    return;
                }
                // if (!imageInput) {
                //     Swal.showValidationMessage('Ingrese una imagen para su contenedor.');
                //     resolve();
                //     return;
                // }
                if (!url) {
                    Swal.showValidationMessage('Ingrese una URL a su contenedor.');
                    resolve();
                    return;
                }
                let formData = new FormData();
                formData.append('image', imageInput);
                formData.append('nombreContenedor', nombreContenedor);
                formData.append('url', url);
                formData.append('idUsuario', idUsuario); // Añadir ID de usuario al formData
                $.ajax({
                    url: 'modelos/guardar/guardar_contenedor.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        Swal.fire({
                            title: '<div class="alert alert-dark" role="alert">GUARDADO</div>',
                            icon: 'success',
                            showConfirmButton: false, // Oculta el botón de confirmación
                            timer: 2000, // Establece un temporizador de 5 segundos
                            customClass: {
                                popup: 'cuerpo_modal_guardar',
                            },
                            willClose: () => {
                                // Recargar la página automáticamente cuando el modal se cierre
                                window.location.reload();
                            }
                        });
                    },

                    error: function () {
                        Swal.fire('Error', 'Hubo un error al guardar el contenedor',
                            'error');
                        resolve();
                    }
                });
            });
        }
    });
}

function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        // Validación del tipo de archivo
        if (!file.type.match('image/(jpeg|jpg|gif|png|svg\+xml)')) {
            Swal.showValidationMessage(
                'Solo se permiten imágenes (formatos permitidos: .jpg, .jpeg, .png, .gif, .svg).');
            return; // Salir de la función si el archivo no es una imagen válida
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const imgElement = document.getElementById('imagePreview');
            imgElement.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

 function eliminarContenedor(id) {
    Swal.fire({
        title: '<div class="alert alert-dark" role="alert">¿ESTÁS SEGURO?</div>',
        text: "No podrás revertir esto!",
        width: '570px',
        padding: '40px',
        showCancelButton: true,
        confirmButtonColor: '#5B8E4A',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ELIMINAR',
        cancelButtonText: 'CANCELAR',
        customClass: {
            popup: 'cuerpo_modal_eliminar',        
                confirmButton: 'btn-modal-eliminar',
            cancelButton: 'bt_activar_alumno'
        },
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'modelos/eliminar/eliminar_contenedor.php', // Asegúrate de que esta URL es correcta
                type: 'POST',
                data: {
                    id: id
                }, // Envía el ID del contenedor a eliminar
                success: function (response) {
                    // Procesa la respuesta del servidor
                    if (response.success) {
                        Swal.fire({
                            title: '<div class="alert alert-dark" role="alert">ELIMINADO!</div>',
                            // text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK',
                            timer: 2000, // Tiempo antes de cerrar el modal automáticamente, en milisegundos
                            showConfirmButton: false, // Oculta el botón de confirmación
                            customClass: {
                                popup: 'cuerpo_modal_guardar',

                            },

                            willClose: () => {
                                // Acciones a realizar justo antes de que el modal se cierre, por ejemplo, recargar la página
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Error', 'No se pudo eliminar el contenedor: ' + response.error,
                            'error');
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire('Error', 'Ha ocurrido un error al intentar eliminar el contenedor: ' +
                        error, 'error');
                }
            });
        }
    });
}

function modificarContenedor(id) {
    // Solicitud AJAX para obtener los datos del contenedor
    $.ajax({
        url: 'modelos/rescatar/con_acceso_directo.php',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function (data) {
            if (data.success) {
                const contenedor = data.contenedor;

                // Mostrar el modal con los datos actuales
                Swal.fire({
                    title: '<div class="alert alert-dark" role="alert">MODIFICAR CONTENEDOR</div>',
                    html: `
                        <div class="alert alert-secondary" role="alert">
                            <form id="formModificarContenedor" class="row g-3">
                                <!-- Subir Imagen -->
                                <div class="col-md-6 col-sm-12">
                                    <div class="input-column">
                                        <label for="imageInput" class="btn btn-primary">
                                            Subir Imagen
                                            <input type="file" id="imageInput" accept="image/*" onchange="previewImage(event)" style="display: none;">
                                        </label>
                                        <img id="imagePreview" src="imagenes/${contenedor.imagen}" alt="Vista previa de la imagen" style="width: 100%; height: auto; max-height: 200px; margin-top: 10px;">
                                    </div>
                                </div>
                                <!-- Nombre y URL -->
                                <div class="col-md-6 col-sm-12">
                                    <label for="id_nombre" class="form-label">Nombre Contenedor:</label>
                                    <input type="text" class="form-control mb-3" id="id_nombre" value="${contenedor.nombre}" required>
                                    <label for="id_url" class="form-label">URL:</label>
                                    <i class="fas fa-info-circle" data-bs-toggle="modal" data-bs-target="#videoModal" style="color:red;"></i>
                                    <input type="text" class="form-control mb-1" id="id_url" value="${contenedor.url_}" required>
                                </div>
                            </form>
                        </div>
                    `,
                    showCancelButton: true,
                     width: '870px',
                        padding: '40px',
                    confirmButtonColor: '#5B8E4A',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'MODIFICAR',
                    cancelButtonText: 'CANCELAR',
                    customClass: {
                            popup: 'cuerpo_modal_guardar',
                            confirmButton: 'bt_activar_alumno',
                            cancelButton: 'bt_activar_alumno'
                        },
                    preConfirm: () => {
                        // Obtener datos del formulario
                        const imageInput     = document.getElementById('imageInput').files[0];
                        const nombre        = document.getElementById('id_nombre').value.trim();
                        const url           = document.getElementById('id_url').value.trim();

                        if (!nombre || !url) {
                            Swal.showValidationMessage('Por favor, complete todos los campos obligatorios.');
                            return false;
                        }

                        return { imageInput, nombre, url };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const { imageInput, nombre, url } = result.value;

                        // Crear FormData para enviar los datos al servidor
                        const formData = new FormData();
                        formData.append('id', id);
                        formData.append('nombre', nombre);
                        formData.append('url', url);
                        if (imageInput) {
                            formData.append('image', imageInput); // Solo enviar si se selecciona una imagen nueva
                        }

                        // Enviar solicitud AJAX para actualizar el contenedor
                       $.ajax({
                            url: 'modelos/editar/contenedor_accesos.php',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Modificado!',
                                        position: "top-end",
                                        text: 'El contenedor ha sido modificado correctamente.',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        // Recargar la página después de mostrar el mensaje
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', response.message || 'No se pudo modificar el contenedor.', 'error');
                                }
                            },
                            error: function () {
                                Swal.fire('Error', 'Hubo un problema al modificar el contenedor.', 'error');
                            }
                        });

                    }
                });
            } else {
                Swal.fire('Error', 'No se pudieron obtener los datos del contenedor.', 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Hubo un problema al obtener los datos del contenedor.', 'error');
        }
    });
}
//******************mensajes.php*********************** */ 
//*************MENSAJES******************************* */




// function enviar_mensaje_mensaje(idUsuario) {
//     $.ajax({
//         type: "POST",
//         url: "modelos/rescatar/musuarios.php",
//         dataType: "json",
//         success: function (data) {
//             if (data.length > 0) {
//                 var opcionesUsuarios = data.map(usuario => `<option value="${usuario.id}">${usuario.nombre} ${usuario.apellido_paterno}</option>`).join('');

//                 Swal.fire({
//                     title: '<div class="alert alert-dark" role="alert">ENVIAR MENSAJE</div>',
//                     html: `
//                         <div class="alert alert-primary" role="alert">
//                             <div class="form-group">
//                                 <div class="form-check">
//                                     <input type="checkbox" class="form-check-input" id="swal-input-urgent">
//                                     <label class="form-check-label" for="swal-input-urgent">Urgente</label>
//                                 </div>
//                             </div>
//                         </div>
//                         <div class="alert alert-secondary" role="alert">
//                             <form class="row g-3" id="ticketForm">
//                                 <div class="col-md-6">
//                                     <div class="form-floating">
//                                         <input type="text" class="form-control" id="swal-input-rem" placeholder="De"
//                                             value="<?= htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido_paterno'] . ' ' . $_SESSION['apellido_materno']); ?>" readonly>
//                                         <label for="swal-input-rem">DE</label>
//                                     </div>
//                                 </div>
//                                 <div class="col-md-6">
//                                     <div class="form-floating">
//                                         <select class="form-control" id="swal-input-dest">
//                                             ${opcionesUsuarios}
//                                         </select>
//                                         <label for="swal-input-dest">Para</label>
//                                     </div>
//                                 </div>
//                                 <div class="col-12">
//                                     <div class="form-floating">
//                                         <textarea class="form-control" id="swal-input-msg" placeholder="Escribe el mensaje aquí..." style="height: 200px;"></textarea>
//                                         <label for="swal-input-msg">Mensaje</label>
//                                     </div>
//                                 </div>
//                             </form>
//                         </div>`,
//                     showCancelButton: true,
//                     confirmButtonText: 'ENVIAR',
//                     cancelButtonText: 'CANCELAR',
//                     preConfirm: () => {
//                         const mensaje = document.getElementById('swal-input-msg').value;
//                         const destinatarioId = document.getElementById('swal-input-dest').value;
//                         const urgente = document.getElementById('swal-input-urgent').checked;

//                         if (!mensaje || !destinatarioId) {
//                             Swal.showValidationMessage("Por favor, completa todos los campos necesarios.");
//                             return false;
//                         }

//                         // Enviar los datos mediante AJAX
//                         return $.ajax({
//                             type: "POST",
//                             url: "modelos/guardar/guardar_mensaje.php",
//                             data: {
//                                 idUsuario: idUsuario, // Pasar el ID del usuario correctamente
//                                 destinatarioId: destinatarioId,
//                                 mensaje: mensaje,
//                                 urgente: urgente,
//                                 accion: 'crear_mensaje_2'
//                             },
//                             dataType: "json"
//                         }).then(response => {
//                             if (response.status !== 'success') {
//                                 return Swal.showValidationMessage(response.message || 'Error al enviar el mensaje.');
//                             }
//                             return response;
//                         });
//                     }
//                 }).then(result => {
//                     if (result.value) {
//                         Swal.fire({
//                             icon: 'success',
//                             title: 'Mensaje Enviado',
//                             timer: 2000,
//                             showConfirmButton: false
//                         }).then(() => location.reload());
//                     }
//                 });
//             }
//         },
//         error: function () {
//             Swal.fire('Error', 'No se pudo cargar la lista de usuarios.', 'error');
//         }
//     });
// }













function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const imgElement = document.getElementById('imagePreview');
            imgElement.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}
function eliminar_mensaje(idMensaje) {
    Swal.fire({
        title: '<div class="alert alert-dark" role="alert">¿ESTÁS SEGURO?</div>',
        text: "No podrás revertir esto!",
        width: '570px',
        padding: '40px',
        showCancelButton: true,
        confirmButtonColor: '#5B8E4A',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ELIMINAR',
        cancelButtonText: 'CANCELAR',
        customClass: {
    popup: 'cuerpo_modal_eliminar',        
                confirmButton: 'btn-modal-eliminar',
            cancelButton: 'bt_activar_alumno'
        },
    }).then((result) => {
        if (result.isConfirmed) {
            // Código para eliminar el mensaje
            // Asumimos que tienes una función AJAX o similar para manejar la eliminación
            $.ajax({
                url: 'modelos/eliminar/eliminar_mensaje.php', // Asegúrate de que esta URL es correcta
                type: 'POST',
                data: {
                    id: idMensaje
                },
                success: function (response) {
                    // Procesa la respuesta del servidor
                    if (response.success) {
                        Swal.fire({
                            title: '<div class="alert alert-dark" role="alert">ELIMINADO!</div>',
                            // text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK',
                            timer: 2000, // Tiempo antes de cerrar el modal automáticamente, en milisegundos
                            showConfirmButton: false, // Oculta el botón de confirmación
                            customClass: {
                                popup: 'cuerpo_modal_guardar',

                            },

                            willClose: () => {
                                // Acciones a realizar justo antes de que el modal se cierre, por ejemplo, recargar la página
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Error', 'No se pudo eliminar el contenedor: ' + response.error,
                            'error');
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire('Error', 'Ha ocurrido un error al intentar eliminar el contenedor: ' +
                        error, 'error');
                }
            });
        }
    });
}
function openChatModal(para, de) {
    // alert("para: " + para + ", de: " + de);
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
    });

    swalWithBootstrapButtons.fire({
        title: '<div class="alert alert-dark" role="alert">CONVERSACIÓN</div>',
        html: `
                                                <div class="chat-container">
                                                    <div class="chat-message received">
                                                        <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                                                        <span>Mensaje del usuario con problemas</span>
                                                    </div>
                                                    <div class="chat-message sent">
                                                        <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                                                        <span>Mensaje del técnico asignado</span>
                                                    </div>
                                                </div>
                                                <form id="chatForm" action="modelos/guardar/guardar_mensajes.php" method="POST" style="padding: 10px;">
                                                    <input type="hidden" name="idTicket" value="{id del ticket}">  <!-- Asegúrate de tener el valor correcto aquí -->
                                                    <input type="hidden" name="usuario_problem" value="{id del usuario}">  <!-- Asegúrate de tener el valor correcto aquí -->
                                                    <input type="hidden" name="para" value="${para}">
                                                    <input type="hidden" name="de" value="${de}">
                                                    <input type="text" name="mensaje" placeholder="Escribe un mensaje..." style="width: 75%; padding: 8px; border: 1px solid #ccc; border-radius: 20px; margin-right: 10px;">
                                                    <button type="submit" class="btn btn-primary">Enviar</button>
                                                </form>
                                            `,
        showCancelButton: true, // Solo mostrar el botón de cancelar que actuará como botón de cerrar
        cancelButtonText: 'CANCELAR',
        width: 870,
        cancelButtonColor: '#d33',
        padding: '40px',
        customClass: {
            popup: 'cuerpo_modal_guardar', // Clase personalizada para el popup
            cancelButton: 'bt_activar_alumno' // Clase personalizada para el botón
        },
        onOpen: () => {
            cargarMensajes(); // Cargar los mensajes existentes al abrir el modal
            const form = document.getElementById('chatForm');
            form.onsubmit = () => {
                const mensaje = form.querySelector('input[name="mensaje"]').value;
                $.ajax({
                    url: form.action,
                    type: 'POST',
                    data: $(form).serialize(),
                    success: function (response) {
                        $('#chatContainer').append(`
                                                                <div class="chat-message sent">
                                                                    <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                                                                    <span>${mensaje}</span>
                                                                </div>
                                                            `);
                        form.reset(); // Resetear el formulario después de enviar
                    }
                });
                return false; // Prevenir el envío normal del formulario
            };
        },
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise(function (resolve) {
                setTimeout(function () {
                    resolve();
                }, 2000);
            });
        }
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.cancel) {
            swalWithBootstrapButtons.close(); // Solo cierra el modal si se hace clic en Cancelar
        }
    });
}
function cargarMensajes() {
    // Implementa la lógica para cargar mensajes del servidor
    $('#chatContainer').html('Cargando mensajes...');
    // Supongamos que 'api/cargar_mensajes.php' devuelve los mensajes en HTML
    $.get('api/cargar_mensajes.php', function (data) {
        $('#chatContainer').html(data);
    });
}
function verMensajeResibido(id) {
    // alert(id);
    // alert("----verMensajeResibido-----");
    $.ajax({
        url: "modelos/rescatar/rescatar_mensajes.php",
        type: "POST",
        data: { id: id },
        dataType: "json",
        success: function (data) {
            if (data) {
                var mensaje = data.mensaje || "No disponible";
                var fecha = data.fecha || "No disponible";
                var hora = data.hora || "No disponible";
                var nombre_apellido = (data.nombreRemitente || "No disponible") + " " + (data.apellidoRemitente || "");

                Swal.fire({
                    title: '<div class="alert alert-dark" role="alert">MENSAJE</div>',
                    html: `
                                        <div class="alert alert-secondary" role="alert">
                                            <form class="row g-3">                                                                     
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="floatingFecha" value="${fecha}" disabled>
                                                        <label for="floatingFecha">Fecha</label>
                                                    </div>
                                                </div>       
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="floatingHora" value="${hora}" disabled>
                                                        <label for="floatingHora">Hora</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="floatingDe" value="${nombre_apellido}" disabled>
                                                        <label for="floatingDe">De</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <textarea class="form-control" placeholder="Address" id="swal-input-msg" style="height: 100px;" disabled>${mensaje}</textarea>
                                                        <label for="floatingTextarea">Mensaje</label>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>`,
                    width: "870px",
                    padding: "40px",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showCloseButton: true,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'CERRAR',
                    showDenyButton: true,
                    denyButtonText: 'ARCHIVAR',
                    customClass: {
                        popup: 'cuerpo_modal_guardar',
                        confirmButton: 'bt_cerrar',
                        denyButton: 'bt_borrador',
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Lógica para manejar la acción al presionar "Contestar"
                        console.log('Contestar clicked');
                        // Aquí puedes redirigir a la página de respuesta o abrir otro modal
                    } else if (result.isDenied) {
                        // Mostrar otro modal para confirmar el archivado
                        Swal.fire({
                            title: '<div class="alert alert-dark" role="alert">¿YA LEÍSTE ESTE MENSAJE?</div>',
                            html: '<div class="alert alert-warning" role="alert">Este mensaje será movido a la carpeta de archivados,<strong>no estará visible</strong> en la bandeja de enviados, <strong>el sistema lo marcará como leído.</strong></div>',
                            icon: 'warning',
                            showCancelButton: false,
                            width: "570px",
                            padding: "40px",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            showCloseButton: true,
                            showCancelButton: false,
                            showConfirmButton: true,
                            showDenyButton: true,
                            confirmButtonText: 'SI, ARCHIVAR',
                            cancelButtonText: 'CANCELAR',
                            customClass: {
                                popup: 'cuerpo_modal_alertas',
                                confirmButton: 'bt_crear',
                                denyButton: 'bt_eliminar',
                            }
                        }).then((archiveResult) => {
                            if (archiveResult.isConfirmed) {
                                // Lógica para archivar el mensaje
                                $.ajax({
                                    url: 'modelos/guardar/archivar_mensaje.php',
                                    type: 'POST',
                                    data: {
                                        id: id,
                                        accion: 'crear_borrador'
                                    },
                                    success: function (response) {
                                        Swal.fire({
                                            title: '<div class="alert alert-dark" role="alert">MENSAJE ARCHIVADO</div>',
                                            html: '<div class="alert alert-warning" role="alert">' +
                                                'Revida su mensajes Archivados' +
                                                ' </div>',
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 2000, // Tiempo en milise|gundos antes de que el modal se cierre automáticamente
                                            timerProgressBar: true, // Mostrar la barra de progreso
                                            allowOutsideClick: false, // No permitir cerrar al hacer clic fuera del modal
                                            customClass: {
                                                popup: 'cuerpo_modal_guardar',
                                            }
                                        }).then(() => {
                                            location.reload();
                                        });
                                    },
                                    error: function (xhr, status, error) {
                                        console.error('Error al archivar el mensaje:', error);
                                        Swal.fire('Error', 'Hubo un problema al archivar el mensaje.', 'error');
                                    }
                                });
                            }
                        });
                    }
                });
            }
        },
    });
}
function pasar_borrador_creado(id) {
    $.ajax({
        url: "modelos/rescatar/rescatar_mensajes.php",
        type: "POST",
        data: { id: id },
        dataType: "json",
        success: function (data) {
            if (data) {
                var mensaje = data.mensaje || "No disponible";
                var fecha = data.fecha || "No disponible";
                var hora = data.hora || "No disponible";
                var nombre_apellido = (data.nombreRemitente || "No disponible") + " " + (data.apellidoRemitente || "");

                Swal.fire({
                    title: '<div class="alert alert-dark" role="alert">MENSAJE</div>',
                    html: `
                                        <div class="alert alert-secondary" role="alert">
                                            <form class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="floatingFecha" value="${fecha}" disabled>
                                                        <label for="floatingFecha">Fecha</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="floatingHora" value="${hora}" disabled>
                                                        <label for="floatingHora">Hora</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="floatingDe" value="${nombre_apellido}" disabled>
                                                        <label for="floatingDe">De</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <textarea class="form-control" placeholder="Address" id="swal-input-msg" style="height: 100px;">${mensaje}</textarea>
                                                        <label for="floatingTextarea">Mensaje</label>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>`,
                    width: "870px",
                    padding: "40px",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showCloseButton: true,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'MODIFICAR',
                    showDenyButton: true,
                    denyButtonText: 'VOLVER A RECIBIDOS',
                    customClass: {
                        popup: 'cuerpo_modal_guardar',
                        confirmButton: 'bt_eliminar',
                        denyButton: 'bt_cerrar',
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        var nuevoMensaje = document.getElementById('swal-input-msg').value;
                        if (!validarCamposVacios(document.getElementById('swal-input-msg'))) {
                            alert('El mensaje no puede estar vacío.');
                        } else {
                            $.ajax({
                                url: 'modelos/editar/mensaje_archivado.php',
                                type: 'POST',
                                data: {
                                    id: id,
                                    mensaje: nuevoMensaje
                                },
                                success: function (response) {
                                    Swal.fire({
                                        title: '<div class="alert alert-dark" role="alert">MENSAJE MODIFICADO</div>',
                                        html: '<div class="alert alert-warning" role="alert">El mensaje ha sido modificado exitosamente.</div>',
                                        icon: 'success',
                                        showConfirmButton: false,
                                        timer: 3000,
                                        timerProgressBar: true,
                                        allowOutsideClick: false,
                                        customClass: {
                                            popup: 'cuerpo_modal_guardar',
                                        }
                                    }).then(() => {
                                        location.reload();
                                    });
                                },
                                error: function (xhr, status, error) {
                                    console.error('Error al modificar el mensaje:', error);
                                    Swal.fire('Error', 'Hubo un problema al modificar el mensaje.', 'error');
                                }
                            });
                        }
                    } else if (result.isDenied) {
                        Swal.fire({
                            title: '<div class="alert alert-dark" role="alert">¿ESTÁS SEGURO?</div>',
                            html: '<div class="alert alert-warning" role="alert">Este mensaje <strong>ARCHIVADO</strong> se verá en su lista de <strong>mensajes recibidos</strong>.</div>',
                            icon: 'warning',
                            showCancelButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            showCloseButton: true,
                            width: "570px",
                            padding: "40px",
                            confirmButtonText: 'SI, CREAR',
                            cancelButtonText: 'CANCELAR',
                            customClass: {
                                popup: 'cuerpo_modal_alertas',
                                confirmButton: 'bt_crear',
                                denyButton: 'bt_eliminar',
                            }
                        }).then((createResult) => {
                            if (createResult.isConfirmed) {
                                $.ajax({
                                    url: 'modelos/guardar/archivar_mensaje.php',
                                    type: 'POST',
                                    data: {
                                        id: id,
                                        accion: 'borrador_mensaje'
                                    },
                                    success: function (response) {
                                        Swal.fire({
                                            title: '<div class="alert alert-dark" role="alert">MENSAJE CREADO</div>',
                                            html: '<div class="alert alert-warning" role="alert">Revisa tus mensajes recibidos.</div>',
                                            icon: 'success',
                                            showConfirmButton: false,
                                            timer: 3000,
                                            timerProgressBar: true,
                                            allowOutsideClick: false,
                                            customClass: {
                                                popup: 'cuerpo_modal_guardar',
                                            }
                                        }).then(() => {
                                            location.reload();
                                        });
                                    },
                                    error: function (xhr, status, error) {
                                        console.error('Error al crear el mensaje:', error);
                                        Swal.fire('Error', 'Hubo un problema al crear el mensaje.', 'error');
                                    }
                                });
                            }
                        });
                    }
                });
            }
        },
    });
}


function mensajeArchivado(id) {
    Swal.fire({
        title: '<div class="alert alert-dark" role="alert">¿YA LEÍSTE ESTE MENSAJE?</div>',
        html: '<div class="alert alert-warning" role="alert">Este mensaje será movido a la carpeta de archivados, <strong>no estará visible</strong> en la bandeja de enviados, <strong>el sistema lo marcará como leído.</strong></div>',
        icon: 'warning',
        showCancelButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showCloseButton: true,
        confirmButtonColor: '#5B8E4A',
        cancelButtonColor: '#d33',
        confirmButtonText: 'SI, ARCHIVAR',
        cancelButtonText: 'NO',
        width: "570px",
        padding: "40px",
        customClass: {
            popup: 'cuerpo_modal_alertas',
            confirmButton: 'bt_activar_alumno',
            cancelButton: 'bt_activar_alumno'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'modelos/guardar/archivar_mensaje.php',
                type: 'POST',
                data: {
                    id: id,  // Asegúrate de que esto coincida con lo que tu backend espera
                    accion: 'crear_borrador' // Verifica que esta acción sea manejada correctamente en el backend
                },
                success: function (response) {
                    console.log('Respuesta del servidor:', response);
                    Swal.fire({
                        icon: 'success',
                        title: '<div class="alert alert-dark" role="alert">MENSAJE ARCHIVADO</div>',
                        text: 'El mensaje ha sido archivado correctamente.',
                        width: "570px",
                        height: "800px",
                        padding: "40px",
                        showCancelButton: false,
                        showConfirmButton: false,
                        timer: 2000,
                        customClass: {
                            popup: 'cuerpo_modal_guardar',
                        }


                    }).then(() => {
                        location.reload(); // Recarga la página después de cerrar el modal de confirmación
                    });
                },
                error: function (error) {
                    console.error('Error en la solicitud AJAX:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo archivar el mensaje. Intente nuevamente.'
                    });
                }
            });
            console.log('Mensaje marcado como leído'); // Simulación de acción
        } else {
            console.log('El usuario indica que no ha leído el mensaje');
        }
    });
}



function eliminarMensaje(idMensaje) {
    Swal.fire({
        title: '<div class="alert alert-dark" role="alert">¿YA LEÍSTE ESTE MENSAJE?</div>',
        text: "Este mensaje será eliminado permanentemente.",
        width: '670px',
        padding: '40px',
        showCancelButton: true,
        confirmButtonColor: '#5B8E4A',
        cancelButtonColor: '#d33',
        confirmButtonText: 'SI',
        cancelButtonText: 'NO',
        customClass: {
            popup: 'cuerpo_modal_eliminar',        
            confirmButton: 'btn-modal-eliminar',
            cancelButton: 'bt_activar_alumno'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'modelos/eliminar/eliminar_mensaje.php',
                type: 'POST',
                data: { idMensaje: idMensaje },
                dataType: 'json',  // Espera una respuesta en formato JSON
                success: function (response) {
                    console.log('Respuesta del servidor:', response);
                    Swal.fire({
                        title: '<div class="alert alert-dark" role="alert">MENSAJE ELIMINADO</div>',
                        // text: 'El mensaje ha sido eliminado permanentemente.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2000, // El modal se cierra automáticamente después de 5 segundos+
                        customClass: {
                            popup: 'cuerpo_modal_eliminar',        
                        

                        }
                    }).then(() => {
                        location.reload(); // Recarga la página después de cerrar el modal de confirmación
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo eliminar el mensaje. Intente nuevamente.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        } else {
            console.log('El usuario indica que no ha leído el mensaje');
        }
    });
}


function eliminarMensajeEnviado(idMensaje) {
    Swal.fire({
        title: '<div class="alert alert-dark" role="alert">¿SEGURO?</div>',
        text: "Este mensaje será eliminado permanentemente.",
        width: '670px',
        padding: '40px',
        showCancelButton: true,
        confirmButtonColor: '#5B8E4A',
        cancelButtonColor: '#d33',
        confirmButtonText: 'SI',
        cancelButtonText: 'NO',
        customClass: {
            popup: 'cuerpo_modal_eliminar',        
            confirmButton: 'btn-modal-eliminar',
            cancelButton: 'bt_activar_alumno'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'modelos/eliminar/eliminar_mensaje.php', // Asegúrate de que esta URL es correcta
                type: 'POST',
                data: {
                    idMensaje: idMensaje
                },
                success: function (response) {
                    console.log('Respuesta del servidor:', response);
                    // Muestra un modal de confirmación cuando el mensaje se ha eliminado
                       Swal.fire({
                        title: '<div class="alert alert-dark" role="alert">MENSAJE ELIMINADO</div>',
                        // text: 'El mensaje ha sido eliminado permanentemente.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2000, // El modal se cierra automáticamente después de 5 segundos+
                        customClass: {
                            popup: 'cuerpo_modal_guardar',

                        }
                        // confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload(); // Recarga la página después de cerrar el modal de confirmación
                    });
                },
                error: function (error) {
                    console.error('Error en la solicitud AJAX:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo eliminar el mensaje. Intente nuevamente.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        } else {
            console.log('El usuario indica que no ha leído el mensaje');
        }
    });

}


function onclikEnviar_MensajeEnviados(id) {
    $.ajax({
        url: "modelos/rescatar/rescatar_mensajes.php",
        type: "POST",
        data: { id: id },
        dataType: "json",
        success: function (data) {
            if (data) {
                var mensaje = data.mensaje || "No disponible";
                var fecha = data.fecha || "No disponible";
                var hora = data.hora || "No disponible";
                var nombre_apellido = (data.nombreDestinatario || "No disponible") + " " + (data.apellidoDestinatario || "");

                Swal.fire({
                    title: '<div class="alert alert-dark" role="alert">MENSAJE</div>',
                    html: `
                                        <div class="alert alert-secondary" role="alert">
                                            <form class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="floatingFecha" value="${fecha}" disabled>
                                                        <label for="floatingFecha">Fecha</label>
                                                    </div>
                                                </div>       
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="floatingHora" value="${hora}" disabled>
                                                        <label for="floatingHora">Hora</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="floatingDe" value="${nombre_apellido}" disabled>
                                                        <label for="floatingDe">para</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <textarea class="form-control" placeholder="Address" id="swal-input-msg" style="height: 100px;" disabled>${mensaje}</textarea>
                                                        <label for="floatingTextarea">Mensaje</label>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>`,
                    width: "870px",
                    padding: "40px",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCloseButton: true,
                    allowEnterKey: false,
                    showCancelButton: true,
                    cancelButtonText: 'CERRAR', // Sólo mantener el botón de cerrar
                    showConfirmButton: false, // Quitar botón de confirmar
                    customClass: {
                        popup: 'cuerpo_modal_guardar',
                        cancelButton: 'bt_cerrar'
                    }
                });
            }
        },
    });
}

//******************Ticket.php*********************** */ 
//*************TICKET******************************* */       

function ingresarTicket(idUsuarioSession) {
    alert(idUsuarioSession);
    let url = (idUsuarioSession === '7' || idUsuarioSession === '6' || idUsuarioSession === '8') ? "modelos/rescatar/usuarios.php" : "modelos/rescatar/usuario.php"; // para que vean todos los usuarios
    $.ajax({
        url: url,
        type: 'POST',
        dataType: 'json',
        data: { id: idUsuarioSession },
        success: function (response) {
            if ($.isEmptyObject(response)) {
                alert("No se encontraron usuarios");
                return

            }
            let inputField = '<select class="form-control" id="idUsuarioSession">';
            if (Array.isArray(response)) {
                response.forEach(user => {
                    let nombreCompleto = `${user.nombre} ${user.apellido_paterno || ''} ${user.apellido_materno || ''}`.trim();
                    let selected = user.id === idUsuarioSession ? 'selected' : '';
                    inputField += `<option value="${user.id}" ${selected}>${nombreCompleto}</option>`;
                });
            } else {
                let nombreCompleto = `${response.nombre} ${response.apellido_paterno || ''} ${response.apellido_materno || ''}`.trim();
                let selected = response.id === idUsuarioSession ? 'selected' : '';
                inputField += `<option value="${response.id}" ${selected}>${nombreCompleto}</option>`;
            }
            inputField += '</select>';

            cargarCategoriasYMostrarModal(inputField, idUsuarioSession);
        },
        error: function () {
            alert("Error en la conexión con el servidor");
        }
    });
}



function enviarDatosTicket(data, estado, mensaje) {
    // Verificar si los datos necesarios están presentes
    if (!data || !data.usuarioId || !data.asunto || !data.ticketTexarea || !document.getElementById('categoriaSelect')) {
        Swal.fire('Error', 'Faltan datos para enviar el ticket.', 'error');
        return;
    }

    // Crear FormData y agregar los datos
    let formData = new FormData();
    formData.append('usuarioId', data.usuarioId);
    formData.append('asunto', data.asunto);
    formData.append('descripcion_ticket', data.ticketTexarea);
    formData.append('categoria', document.getElementById('categoriaSelect').value);
    formData.append('estado', estado);
    formData.append('accion', 'ingresar_ticket'); // Agregar el parámetro acción

    // Adjuntar archivos
    if (archivosSeleccionados && archivosSeleccionados.length > 0) {
        archivosSeleccionados.forEach((file, index) => {
            formData.append('archivo[]', file);
        });

        // Mostrar los archivos en la consola para depuración
        let archivosList = archivosSeleccionados.map(file => file.name);
        console.log("Archivos que se van a enviar:", archivosList);
    }

    // Mostrar los datos en la consola para depuración
    for (var pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    // Enviar datos a través de AJAX
    $.ajax({
        url: "modelos/guardar/guardar_ticket.php",
        type: 'POST',
        processData: false,
        contentType: false,
        data: formData,
        success: function (response) {
            console.log(response);
            Swal.fire({
                title: `<div class="alert alert-dark" role="alert">${mensaje}</div>`,
                icon: 'success',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                allowOutsideClick: false,
                customClass: {
                    popup: 'cuerpo_modal_guardar',
                    confirmButton: 'btn btn-primary'
                }
            }).then(() => {
                location.reload();
            });
        },
        error: function (xhr, status, error) {
            console.log(error);
            Swal.fire('Error', 'Hubo un problema al registrar el ticket.', 'error');
        }
    });
}

function validarCamposVacios(asunto, ticketTexarea) {
    let camposValidos = true;

    if (asunto.value.trim() === '') {
        asunto.style.borderColor = "red";
        camposValidos = false;
    } else {
        asunto.style.borderColor = "";
    }

    if (ticketTexarea.value.trim() === '') {
        ticketTexarea.style.borderColor = "red";
        camposValidos = false;
    } else {
        ticketTexarea.style.borderColor = "";
    }

    return camposValidos;
}


function mostrarArchivo(urlArchivo, fileType) {
    let contentHtml = '';

    switch (fileType) {
        case 'image/jpeg':
        case 'image/png':
        case 'image/gif':
            contentHtml = '<img src="' + urlArchivo + '" class="img-fluid" alt="Imagen Adjunta">';
            break;
        case 'application/pdf':
            contentHtml = '<embed src="' + urlArchivo + '" type="application/pdf" width="100%" height="500px"/>';
            break;
        case 'video/mp4':
            contentHtml = '<video controls width="100%"><source src="' + urlArchivo + '" type="video/mp4">Your browser does not support the video tag.</video>';
            break;
        case 'text/csv':
            contentHtml = '<div>CSV file detected. <a href="' + urlArchivo +
                '" target="_blank">Download CSV</a></div>';
            break;
        default:
            contentHtml = '<p>Archivo no soportado para visualización.</p>';
            break;
    }

    // Close the main modal before opening the file view modal
    Swal.close();

    Swal.fire({
        title: '<div class="alert alert-dark" role="alert">Visualizacion de Archivo</div>',
        html: contentHtml,
        width: "870px",
        padding: "40px",
        showCancelButton: false,
        confirmButtonText: 'CERRAR',
        confirmButtonColor: '#5B8E4A',
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_activar_alumno'
        }
    }).then(() => {
        openMainModal();  // Reopen the main modal after closing the file view modal
    });
}



















//*********************************************************** */ 
//**************TICKET.PHP*********************************** */      


// nuestra el modal para ver las caracteristicas del ticket
function verTicket(id) {
    $.ajax({
        url: "modelos/rescatar/ticket_administracion.php",
        type: "POST",
        data: {
            id: id
        },
        dataType: "json",
        success: function (data) {
            if (data) {
                var fecha_creacion_inicio = data.fecha_creacion_inicio || '';
                var hora_creacion_inicio = data.hora_creacion_inicio || '';
                var fecha_estimada_admin = data.fecha_estimada_admin || '';
                var dias_estimada_admin = data.dias_estimada_admin || 'esperando Confirmacion';

                // Verificar si la fecha es válida
                var fecha_resolucion_html = '';
                if (fecha_estimada_admin) {
                    var date = new Date(fecha_estimada_admin);
                    if (isNaN(date.getTime())) {
                        // No es una fecha válida
                        fecha_resolucion_html = '<input type="text" class="form-control" id="fecha_termino_1" value="esperando Confirmacion" disabled>';
                    } else {
                        // Es una fecha válida
                        fecha_resolucion_html = `<input type="date" class="form-control" id="fecha_termino_1" value="${fecha_estimada_admin}" disabled>`;
                    }
                } else {
                    // No hay fecha asignada
                    fecha_resolucion_html = '<input type="text" class="form-control" id="fecha_termino_1" value="esperando Confirmacion" disabled>';
                }

                // Verificar si los días estimados son válidos
                var dias_resolucion_html = '';
                if (dias_estimada_admin === null || dias_estimada_admin === '0') {
                    dias_resolucion_html = 'esperando Confirmacion';
                } else {
                    dias_resolucion_html = `${dias_estimada_admin} Días`;
                }


                var fecha_asignacion_tecnico = '';
                if (data.fecha_asignacion_tecnico) {
                    var fecha = data.fecha_asignacion_tecnico.split('-'); // Suponiendo que la fecha está en formato 'YYYY-MM-DD'
                    var anio = fecha[0];
                    var mes = fecha[1];
                    var dia = fecha[2];
                    fecha_asignacion_tecnico = `${dia}-${mes}-${anio}`;
                }
                var hora_asignacion_tecnico = data.hora_asignacion_tecnico || '';
                var fecha_comienzo_ticket = data.fecha_comienzo_ticket || '';
                var hora_comienzo_ticket = data.hora_comienzo_ticket || '';
                var fecha_termino_ticket = data.fecha_termino_ticket || '';
                var hora_termino_ticket = data.hora_termino_ticket || '';

                var asunto = data.asunto || '';
                var descripcion_ticket = data.descripcion_ticket || '';

                var nombrePrioridad = data.nombrePrioridad || 'esperando Confirmacion';
                var id_estado = data.id_estado || '';
                var nombreEstado = data.nombreEstado || '';
                var nombreUsuario = data.nombreUsuario || '';
                var apePaternoUsuario = data.apePaternoUsuario || '';
                var nombreTecnico = data.nombreTecnico || '';
                var apePaternoTecnico = data.apePaternoTecnico || '';
                var apeMaternoTecnico = data.apeMaternoTecnico || '';
                var id_categoria_ticket = data.id_categoria_ticket || '';
                var nombre_categoria = data.nombre_categoria || '';

                var iniciotecnico = new Date(`${fecha_comienzo_ticket}T${hora_comienzo_ticket}`);
                var terminoTecnico = new Date(`${fecha_termino_ticket}T${hora_termino_ticket}`);
                var diffTime = Math.abs(terminoTecnico - iniciotecnico);
                var diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                var diffHours = Math.floor((diffTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var diffMinutes = Math.floor((diffTime % (1000 * 60)) / (1000 * 60));
                var diffString = `Diferencia: ${diffDays} días, ${diffHours} horas, ${diffMinutes} minutos`;

                var inputNombreTecnico = nombreTecnico === ""
                    ? '<input type="text" class="form-control"  id="nombreTecnico" placeholder="De" value="SIN TECNICO" disabled>'
                    : `<input type="text" class="form-control" id="nombreTecnico" placeholder="De" value="${nombreTecnico + " " + apePaternoTecnico + " " + apeMaternoTecnico}" disabled>`;

                var inputAlertaEstadoHTML = id_estado == 1 ? `<div class="alert alert-danger" role="alert">
                                                                        Aún no se ha asignado un técnico
                                                                    </div>` : '';
                var estadoAsignadoHTML = id_estado == 2 || id_estado == 5 || id_estado == 3 ? `
                        <div class="accordion" id="uniqueAccordionExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="uniqueHeadingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#uniqueAcordeonEstados_1_2_3" aria-expanded="true" aria-controls="uniqueAcordeonEstados_1_2_3">
                                        Detalle Técnico
                                    </button>
                                </h2>
                                <div id="uniqueAcordeonEstados_1_2_3" class="accordion-collapse collapse show" aria-labelledby="uniqueHeadingOne" data-bs-parent="#uniqueAccordionExample">
                                    <div class="accordion-body">
                                        <div class="alert alert-info" role="alert">
                                            <form class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="fecha_asignacion_tecnico" placeholder="Fecha" value="${fecha_asignacion_tecnico}" disabled>
                                                        <label for="fecha_asignacion_tecnico">Fecha Asignación Técnieeco</label>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="hora_click2" placeholder="Hora" value="${hora_asignacion_tecnico}" disabled>
                                                        <label for="hora_click2">Hora Asignación Técnico</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="nombre_tecnico" placeholder="Técnico" value="${nombreTecnico + " " + apePaternoTecnico + " " + apeMaternoTecnico}" disabled>
                                                        <label for="nombre_tecnico">Técnico</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="prioridad" placeholder="Prioridad" value="${nombrePrioridad}" readonly>
                                                        <label for="prioridad">Prioridad</label>
                                                    </div>
                                                </div>
                                              <div class="col-md-6">
                                                <div class="form-floating">
                                                    ${fecha_resolucion_html}
                                                    <label for="fecha_termino">Fecha Resolución</label>
                                                </div>
                                                </div>
                                                <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="dias_resolucion" placeholder="Días Resolución" value="${dias_resolucion_html}" disabled>
                                                    <label for="dias_resolucion">Días resolución</label>
                                                </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><br>` : '';

                var estadoTerminadoHTML = id_estado == 5 ? `
                        <div class="accordion" id="acordeonTerminado">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTerminado">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTerminado" aria-expanded="false" aria-controls="collapseTerminado">
                                        Detalles del Término del Ticket
                                    </button>
                                </h2>
                                <div id="collapseTerminado" class="accordion-collapse collapse" aria-labelledby="headingTerminado" data-bs-parent="#acordeonTerminado">
                                    <div class="accordion-body">
                                        <div class="alert alert-warning" role="alert">
                                            <form class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="date" class="form-control" id="fecha" placeholder="Fecha" value="${fecha_termino_ticket}" disabled>
                                                        <label for="fecha">Fecha Término Ticket</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="hora" placeholder="Hora" value="${hora_termino_ticket}" disabled>
                                                        <label for="hora">Hora Término Ticket</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="de1" placeholder="De" value="" disabled>
                                                        <label for="de1">Técnico demora</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="de2" placeholder="De" value="Dentro de la fecha estipulada" disabled>
                                                        <label for="de2">En tomar el ticket</label>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>` : '';

                var asuntoHTML = id_estado == 4 ?
                    `<input type="text" class="form-control" id="asunto" placeholder="Asunto" value="${asunto}">` :
                    `<input type="text" class="form-control" id="asunto" placeholder="Asunto" value="${asunto}" disabled>`;

                var problemaHTML = id_estado == 4 ?
                    `<textarea class="form-control" placeholder="Describe el problema aquí" id="problema" style="height: 100px;">${descripcion_ticket}</textarea>` :
                    `<textarea class="form-control" placeholder="Describe el problema aquí" id="problema" style="height: 100px;" disabled>${descripcion_ticket}</textarea>`;

                var categoriaHTML = id_estado == 4 ?
                    `<select class="form-control" id="categoria"></select>` :
                    `<input type="text" class="form-control" id="floatingName" placeholder="Asunto" value="${nombre_categoria}" disabled>`;

                Swal.fire({
                    title: `<div class="alert alert-dark" role="alert">TICKET T-0${id}</div>`,
                    html: `
                        <div class="alert alert-secondary" role="alert">
                            <div class="container-fluid">${inputAlertaEstadoHTML}</div>
                            <form class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="date" class="form-control" id="floatingEmail" placeholder="Fecha" value="${fecha_creacion_inicio}" disabled>
                                        <label for="floatingEmail">Fecha Ticket</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="floatingPassword" placeholder="hora" value="${hora_creacion_inicio}" disabled>
                                        <label for="floatingPassword">Hora</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="floatingPassword" placeholder="De" value="${nombreUsuario + " " + apePaternoUsuario}" disabled>
                                        <label for="floatingPassword">De</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="estadoTicket" placeholder="" value="${nombreEstado}" disabled>
                                        <label for="estadoTicket">Estado</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        ${categoriaHTML}
                                        <label for="floatingName">Categoria</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        ${inputNombreTecnico}
                                        <label for="floatingPassword">Técnico</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating">
                                        ${asuntoHTML}
                                        <label for="floatingName">Asunto</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        ${problemaHTML}
                                        <label for="floatingTextarea">Problema</label>
                                    </div>
                                </form>
                            </div>
                            ${estadoAsignadoHTML}
                            ${estadoTerminadoHTML}
                        `,
                    showCancelButton: false,
                    showConfirmButton: id_estado == 4,
                    confirmButtonText: 'CREAR TICKET',
                    showDenyButton: id_estado == 4,
                    denyButtonText: 'ELIMINAR BORRADOR',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    width: "870px",
                    padding: "0px",
                    showCloseButton: true,
                    customClass: {
                        popup: 'cuerpo_modal_guardar',
                        confirmButton: 'bt_crear',
                        denyButton: 'bt_eliminar'
                    },
                    didRender: () => {
                        if (id_estado == 4) {
                            const buttonContainer = document.querySelector('.swal2-actions');
                            const modifyDraftButton = document.createElement('button');
                            modifyDraftButton.id = 'modifyDraftButton';
                            modifyDraftButton.className = 'swal2-confirm swal2-styled bt_borrador';
                            modifyDraftButton.innerText = 'MODIFICAR BORRADOR';
                            buttonContainer.appendChild(modifyDraftButton);

                            // Cargar categorías en el select
                            $.ajax({
                                url: "modelos/rescatar/categoria_de_ticket.php",
                                type: "GET",
                                dataType: "json",
                                success: function (categorias) {
                                    var select = document.getElementById('categoria');
                                    categorias.forEach(function (categoria) {
                                        var option = document.createElement('option');
                                        option.value = categoria.id;
                                        option.text = categoria.nombre_categoria;
                                        if (categoria.id == id_categoria_ticket) {
                                            option.selected = true;
                                        }
                                        select.appendChild(option);
                                    });
                                },
                                error: function (xhr, status, error) {
                                    console.error('Error al cargar las categorías:', error);
                                }
                            });

                            document.getElementById('modifyDraftButton').addEventListener('click', function () {
                                var asunto = document.getElementById('asunto');
                                var descripcion = document.getElementById('problema');
                                var categoria = document.getElementById('categoria').value;

                                if (validarCamposVacios(asunto, descripcion)) {
                                    modificarBorrador(id, categoria, asunto.value, descripcion.value);
                                }
                            });
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        var asunto = document.getElementById('asunto').value;
                        var descripcion = document.getElementById('problema').value;
                        var categoria = document.getElementById('categoria').value;
                        pasarDeBorradorATicket(id, categoria, asunto, descripcion);
                    } else if (result.isDenied) {
                        eliminarBorrador(id);
                    }
                });

                var inputDe1 = document.getElementById("de1");
                if (inputDe1) {
                    inputDe1.value = diffString;
                } else {
                    console.error('El elemento con id="de1" no se encontró en el DOM.');
                }
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar los detalles del ticket:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Error al cargar los datos del ticket. Por favor, intente de nuevo.' + error,
                confirmButtonText: 'Cerrar'
            });
        }
    });
}


// Nueva función para pasar de borrador a ticket
function pasarDeBorradorATicket(id, categoria, asunto, descripcion) {
    $.ajax({
        url: 'modelos/guardar/guardar_ticket.php',
        type: 'POST',
        data: {
            id: id,
            accion: 'pasarDeBorradorATicket',
            id_categoria_ticket: categoria,
            asunto: asunto,
            descripcion_ticket: descripcion
        },
        success: function (response) {
            Swal.fire({
                title: '<div class="alert alert-dark" role="alert">TICKET qweqweCREADO</div>',
                icon: 'success',
                width: "570px",
                height: "800px",
                padding: "40px",
                showCancelButton: false,
                showConfirmButton: false,
                timer: 2000,
                customClass: {
                    popup: 'cuerpo_modal_guardar',
                }
            }).then(() => {
                window.location.reload();
            });
        },
        error: function (xhr, status, error) {
            console.error('Error al crear el ticket:', error);
            Swal.fire('Error', 'Hubo un problema al crear el ticket.', 'error');
        }
    });
}

// Función para eliminar borrador
function eliminarBorrador(id) {
    $.ajax({
        url: 'modelos/eliminar/eliminar_ticket.php',
        type: 'POST',
        data: {
            id: id
        },
        success: function (response) {
            Swal.fire({
                title: '<div class="alert alert-dark" role="alert">BORRADOR ELIMINADO</div>',
                icon: 'success',
                width: "570px",
                height: "800px",
                padding: "40px",
                showCancelButton: false,
                showConfirmButton: false,
                timer: 2000,
                customClass: {
                    popup: 'cuerpo_modal_guardar',
                }
            }).then(() => {
                window.location.reload();
            });
        },
        error: function (xhr, status, error) {
            console.error('Error al eliminar el borrador:', error);
            Swal.fire('Error', 'Hubo un problema al eliminar el borrador.', 'error');
        }
    });
}


// Función para modificar borrador
function modificarBorrador(id, categoria, asunto, descripcion) {
    $.ajax({
        url: 'modelos/editar/editar_borrador.php',
        type: 'POST',
        data: {
            id: id,
            id_categoria_ticket: categoria,
            asunto: asunto,
            descripcion_ticket: descripcion
        },
        success: function (response) {
            Swal.fire({
                title: '<div class="alert alert-dark" role="alert">BORRADOR MODIFICADO</div>',
                icon: 'success',
                width: "570px",
                height: "800px",
                padding: "40px",
                showCancelButton: false,
                showConfirmButton: false,
                timer: 2000,
                customClass: {
                    popup: 'cuerpo_modal_guardar',
                }
            }).then(() => {
                window.location.reload();
            });
        },
        error: function (xhr, status, error) {
            console.error('Error al modificar el borrador:', error);
            Swal.fire('Error', 'Hubo un problema al modificar el borrador.', 'error');
        }
    });
}




// esta funcion la ocupo lata ticket_asignados.php
function archivosAdjuntos(id) {
    // Hacer una llamada AJAX al servidor para obtener los archivos adjuntos
    $.ajax({
        url: 'modelos/rescatar/archivos_adjuntos.php',
        type: 'POST',
        data: { id_ticket: id },
        success: function (response) {
            // Si la respuesta ya es un objeto JSON, no necesitas parsearla
            const archivos = response;
            let tabs = '';
            let tabContents = '';

            archivos.forEach((archivo, index) => {
                const activeClass = index === 0 ? 'active' : '';
                const tabTitle = `Adjunto ${index + 1}`;
                tabs += `<li class="nav-item">
                                <a class="nav-link ${activeClass}" id="tab-${index}" data-bs-toggle="tab" href="#content-${index}" role="tab" aria-controls="content-${index}" aria-selected="true">${tabTitle}</a>
                             </li>`;

                const fileType = archivo.urlArchivo.split('.').pop().toLowerCase();
                let contentHtml = '';

                switch (fileType) {
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                        contentHtml = '<img src="archivos/ticket/' + archivo.urlArchivo + '" class="img-fluid" alt="Imagen Adjunta">';
                        break;
                    case 'pdf':
                        contentHtml = '<embed src="archivos/ticket/' + archivo.urlArchivo + '" type="application/pdf" width="100%" height="500px"/>';
                        break;
                    case 'mp4':
                        contentHtml = '<video controls width="100%"><source src="' + archivo.urlArchivo +
                            '" type="video/mp4">Your browser does not support the video tag.</video>';
                        break;
                    case 'csv':
                    case 'xlsx':
                    case 'xls':
                        contentHtml = '<div>Archivo Excel/CSV detectado. <a href="' + archivo.urlArchivo +
                            '" target="_blank">Descargar Archivo</a></div>';
                        break;
                    case 'doc':
                    case 'docx':
                        contentHtml = '<div>Documento de Word detectado. <a href="' + archivo.urlArchivo +
                            '" target="_blank">Descargar Documento</a></div>';
                        break;
                    default:
                        contentHtml = '<p>Archivo no soportado para visualización.</p>';
                        break;
                }

                tabContents += `<div class="tab-pane fade show ${activeClass}" id="content-${index}" role="tabpanel" aria-labelledby="tab-${index}">${contentHtml}</div>`;
            });

            const htmlContent = `<ul class="nav nav-tabs" id="myTab" role="tablist">
                                        ${tabs}
                                     </ul>
                                     <div class="tab-content" id="myTabContent">
                                        ${tabContents}
                                     </div>
                                     <a id="downloadLink" href="${archivos[0].urlArchivo}" download="${archivos[0].nombreArchivo}" style="display: none;"></a>`;

            // Mostrar el modal con SweetAlert
            Swal.fire({
                title: '<div class="alert alert-dark" role="alert">VISUALIZACION DE ADJUNTOS</div>',
                html: htmlContent,
                width: "870px",
                padding: "40px",
                showCancelButton: true,
                cancelButtonText: 'CERRAR',
                confirmButtonText: 'DESCARGAR',
                confirmButtonColor: '#5B8E4A',
                cancelButtonColor: '#d33',
                showCloseButton: false, // Oculta el botón de cierre
                allowOutsideClick: false, // Evita el cierre al hacer clic fuera del modal
                customClass: {
                    popup: 'cuerpo_modal_guardar',
                    confirmButton: 'bt_activar_alumno',
                    cancelButton: 'bt_eliminar'
                },
                preConfirm: () => {
                    const downloadLink = document.getElementById('downloadLink');
                    downloadLink.click();
                    return false; // Evita que el modal se cierre
                }
            });

            // Agregar funcionalidad de descarga a cada pestaña
            archivos.forEach((archivo, index) => {
                document.querySelector(`#tab-${index}`).addEventListener('click', () => {
                    const downloadLink = document.getElementById('downloadLink');
                    downloadLink.href = archivo.urlArchivo;
                    downloadLink.download = archivo.nombreArchivo;
                });
            });
        },
        error: function (error) {
            console.error('Error al obtener los archivos adjuntos:', error);
        }
    });
}

//******************Ticketadministrador.php*********************** */ 
//*************TICKET* ADMINISTRADOR****************************** */ 


function asignacio3nTecnico(id_tecnico, id_ticket) {
    // Hacer una llamada AJAX al servidor para obtener los datos del ticket
    $.ajax({
        url: "modelos/rescatar/ticket.php",
        type: "POST",
        data: { id: id_ticket },
        dataType: "json",
        success: function (data) {
            if (data) {
                var fecha_creacion_inicio = data.fecha_creacion_inicio || '';
                var hora_creacion_inicio = data.hora_creacion_inicio || '';
                var nombreUsuario = data.nombre_usuario || 'No disponible';
                var apePaternoUsuario = data.apellido_paterno_usuario || '';
                var nombreEstado = data.nombreEstado || 'No disponible';
                var idAsunto = data.asunto || 'No disponible';
                var descripcion_ticket = data.descripcion_ticket || 'No hay detalles disponibles';
                var id_prioridad = data.id_prioridad || '';
                // Segunda llamada AJAX para obtener los datos del técnico
                $.ajax({
                    url: "modelos/rescatar/usuario.php",
                    type: "POST",
                    data: { id: id_tecnico },
                    dataType: "json",
                    success: function (tecnicoData) {
                        var nombreTecnico = tecnicoData.nombre || '';
                        var apePaternoTecnico = tecnicoData.apellido_paterno || '';
                        var apeMaternoTecnico = tecnicoData.apellido_materno || '';

                        // Crear el contenido HTML del modal
                        let contentHtml = `
                                    <form class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingName" placeholder="" value="${fecha_creacion_inicio}" disabled>
                                                <label for="floatingName">Fecha ticket</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingEmail" placeholder="" value="${hora_creacion_inicio}" disabled>
                                                <label for="floatingEmail">Hora Ticket</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="nombreUsuarioProblema" placeholder="" value="${nombreUsuario} ${apePaternoUsuario}" disabled>
                                                <label for="nombreUsuarioProblema">De</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control estado-warning" id="estadoTicket" placeholder="" value="${nombreEstado}" disabled>
                                                <label for="estadoTicket">Estado</label>                                              
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingName" placeholder="Asunto" value="${idAsunto}" disabled>
                                                <label for="floatingName">Asunto</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <textarea class="form-control" placeholder="Address" id="floatingTextarea" disabled style="height: 100px;">${descripcion_ticket}</textarea>
                                                <label for="floatingTextarea">Problema</label>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="alert alert-secondary" role="alert">
                                    <form class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <select class="form-select" id="prioridadTicket" aria-label="prioridadTicket">
                                                    <option value="" ${id_prioridad == null || id_prioridad === '' ? 'selected' : ''}>Sin Prioridad</option>
                                                    <option value="1" ${id_prioridad === '1' ? 'selected' : ''}>Alta</option>
                                                    <option value="2" ${id_prioridad === '2' ? 'selected' : ''}>Media</option>
                                                    <option value="3" ${id_prioridad === '3' ? 'selected' : ''}>Baja</option>
                                                </select>
                                                <label for="prioridadTicket">Prioridad</label>
                                                <div class="invalid-feedback">La prioridad es importante</div>
                                            </div>
                                        </div>    
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="nombreTecnico" placeholder="" value="${nombreTecnico} ${apePaternoTecnico} ${apeMaternoTecnico}" disabled>
                                                <label for="nombreTecnico">Responsable</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-floating">
                                                <input type="date" class="form-control" id="fecha_termino" placeholder="">
                                                <label for="fecha_termino">Fecha Término</label>
                                                <div class="invalid-feedback">La fecha de término es obligatoria.</div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <textarea class="form-control" placeholder="Address" id="comentarioTicket" style="height: 100px;"></textarea>
                                                <label for="comentarioTicket">Asignar Comentario</label>
                                            </div>
                                        </div>
                                        <div class="alert alert-danger" role="alert" id="daysSinceTicket" style="display: none;"></div>
                                    </form>              
                                </div>`;

                        // Mostrar el modal con SweetAlert
                        Swal.fire({
                            title: '<div class="alert alert-dark" role="alert">ASIGNANDO TICKET</div>',
                            html: contentHtml,
                            width: "870px",
                            padding: "40px",
                            showCancelButton: true,
                            showConfirmButton: true,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            showCloseButton: true,
                            confirmButtonColor: '#5B8E4A',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'ENVIAR',
                            cancelButtonText: 'CERRAR',
                            customClass: {
                                popup: 'cuerpo_modal_guardar',
                                confirmButton: 'bt_activar_alumno',
                                cancelButton: 'bt_activar_alumno'
                            },
                            didOpen: function () {
                                const alertDaysSinceTicket = document.getElementById('daysSinceTicket');
                                const inputFechaTermino = document.getElementById('fecha_termino');
                                const prioridadTicket = document.getElementById('prioridadTicket');

                                const fechaHoy = new Date();
                                const fechaMinima = fechaHoy.toISOString().split('T')[0]; // Formatea la fecha actual a YYYY-MM-DD
                                inputFechaTermino.setAttribute('min', fechaMinima);

                                inputFechaTermino.addEventListener('change', function () {
                                    const fechaElegida = this.value;
                                    const fechaHoy = new Date();
                                    fechaHoy.setHours(0, 0, 0, 0);

                                    if (fechaElegida) {
                                        const dias = calculateDaysDifference(fechaHoy, fechaElegida);
                                        alertDaysSinceTicket.textContent = `Días hasta la fecha de término: ${dias}`;
                                        alertDaysSinceTicket.style.display = 'block';
                                        inputFechaTermino.classList.remove('is-invalid');
                                    } else {
                                        alertDaysSinceTicket.style.display = 'none';
                                        inputFechaTermino.classList.add('is-invalid');
                                    }
                                });
                            },
                            preConfirm: () => {
                                const selectedDate = document.getElementById('fecha_termino').value;
                                const comentario = document.getElementById('comentarioTicket').value;
                                const prioridad = document.getElementById('prioridadTicket').value;
                                const diffDays = document.getElementById('daysSinceTicket').innerText.replace(/[^0-9]/g, '');

                                const inputFechaTermino = document.getElementById('fecha_termino');
                                const inputPrioridadTicket = document.getElementById('prioridadTicket');

                                let valid = true;

                                // Validar que la fecha no esté vacía
                                if (!selectedDate) {
                                    inputFechaTermino.classList.add('is-invalid');
                                    valid = false;
                                } else {
                                    inputFechaTermino.classList.remove('is-invalid');
                                }

                                // Validar que la prioridad no esté vacía
                                if (!prioridad) {
                                    inputPrioridadTicket.classList.add('is-invalid');
                                    valid = false;
                                } else {
                                    inputPrioridadTicket.classList.remove('is-invalid');
                                }

                                // Si alguna validación falla, retornar false para evitar que se cierre el modal
                                if (!valid) {
                                    return false;
                                }

                                return {
                                    selectedDate: selectedDate,
                                    comentario: comentario,
                                    prioridad: prioridad,
                                    diffDays: diffDays
                                };
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                enviarDatos(
                                    result.value.selectedDate, // Fecha seleccionada para la terminación
                                    result.value.comentario,   // Comentario asignado
                                    result.value.diffDays,     // Días hasta la fecha de término calculados
                                    result.value.prioridad,    // Prioridad seleccionada
                                    id_tecnico,                // ID del técnico
                                    id_ticket                  // ID del ticket
                                );
                            }
                        });
                    },
                    error: function (error) {
                        console.error('Error al obtener los datos del técnico:', error);
                    }
                });
            }
        },
        error: function (error) {
            console.error('Error al obtener los datos del ticket:', error);
        }
    });
}


function calculateDaysDifference(fechaInicio, fechaFin) {
    const diffTime = Math.abs(new Date(fechaFin) - fechaInicio);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
}

function enviarDatos(selectedDate, comentario, diffDays, prioridad, id_tecnico, id_ticket) {
    console.log(selectedDate, comentario, diffDays, prioridad, id_tecnico, id_ticket);
    $.ajax({
        url: "modelos/guardar/guardar_responsable_ticket.php",
        type: 'POST',
        data: {
            fechaSeleccionada: selectedDate,
            comentario: comentario,
            dias_demora: diffDays,
            prioridad: prioridad,
            id_responsable: id_tecnico,
            idTicket: id_ticket
        },
        success: function (response) {
            Swal.fire({
                icon: 'success',
                title: '<div class="alert alert-dark" role="alert">TECNICO ASIGNADO</div>',
                showConfirmButton: false,
                showCancelButton: false,
                timer: 2000, // 2000 milisegundos = 2 segundos
                timerProgressBar: true, // Muestra una barra de progreso que indica el tiempo restante
                customClass: {
                    popup: 'cuerpo_modal_guardar'
                },
                willClose: () => {
                    // Código que se ejecuta cuando el modal se cierra
                    console.log('Modal cerrado');
                    location.reload(); // Recarga la página tras cerrarse el modal
                }
            }).then((result) => {
                /* Si se usa timer, result.dismiss puede ser Swal.DismissReason.timer */
                if (result.dismiss === Swal.DismissReason.timer) {
                    console.log('Cerrado por el timer');
                    // Recargar aquí si no se desea esperar a que `willClose` se ejecute por alguna razón
                }
            });
        }
    });
}






function actualizarTimeline(idTicket) {
    $.ajax({
        url: "modelos/rescatar/avances_tecnicos.php",
        type: "POST",
        data: {
            id: idTicket
        },
        dataType: "json",
        success: function (data) {
            console.log("Datos recibidos para actualizar el timeline:", data);
            var accionesHTML = construirAccionesHTML(data.acciones);
            $('#collapseOne .accordion-body .sbp-preview-content .timeline').html(accionesHTML);
        },
        error: function (xhr, status, error) {
            console.error('Error al obtener la línea de tiempo:', error);
        }
    });
}

function construirAccionesHTML(acciones) {
    var accionesHTML = '';
    if (acciones && acciones.length > 0) {
        acciones.forEach(function (accion) {
            // Check if the action includes specific keywords
            var styleClass = '';
            if (accion.accion.includes("Ticket Finalizado") || accion.accion.includes("Inicio Ticket")) {
                styleClass = 'style="color: red;"'; // Apply red color style if conditions are met
            }

            accionesHTML += `
                        <div class="timeline-item">
                            <div class="timeline-item-marker">
                                <div class="timeline-item-marker-text">${accion.fecha_avance}(${accion.hora_avance})</div>
                                <div class="timeline-item-marker-indicator bg-primary"></div>
                            </div>
                            <div class="timeline-item-content" ${styleClass}>${accion.accion}</div>
                        </div>`;
        });
    } else {
        accionesHTML = '<div class="timeline-item"><div class="timeline-item-content">No hay avances registrados.</div></div>';
    }
    return accionesHTML;
}

function construirAccionesAcordeonHTML(acciones, idTicket, id_estado, fecha_inicio, hora_inicio) {
    var accionesHTML = construirAccionesHTML(acciones); // Usar la función original para construir el HTML de las acciones

    if (id_estado == 3 || id_estado == 5) { // El acordeón se muestra solo si el estado es EN PROCESO O TERMINADO
        return `
                    <div class="accordion" id="accordionExample">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Detalle del Avance del Técnico 
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    <div class="alert alert-secondary" role="alert">
                                        <div class="sbp-preview-content">
                                            <div class="timeline timeline-xs">${accionesHTML}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div></br>`;
    } else {
        // Retorna un string vacío si el estado no es 3 o 5
        return '';
    }
}







// function verTicketAdministrativo(id) {
//     // alert("adasda");

//     $.ajax({
//         url: "modelos/rescatar/ticket_administracion.php",
//         type: "POST",
//         data: {
//             id: id
//         },
//         dataType: "json",
//         success: function (data) {
//             if (data) {
//                 // Recopilar los datos del ticket
//                 var id_ticket = data.id_ticket || '';
//                 var fecha_creacion_inicio = data.fecha_creacion_inicio || '';
//                 var hora_creacion_inicio = data.hora_creacion_inicio || '';
//                 var id_usuario = data.id_usuario || '';
//                 var asunto = data.asunto || '';
//                 var descripcion_ticket = data.descripcion_ticket || '';
//                 var prioridad = data.prioridad || '';
//                 var comentario_administrador = data.comentario_administrador || '';
//                 var fecha_estimada_admin = data.fecha_estimada_admin || '';
//                 var dias_estimada_admin = data.dias_estimada_admin || '';
//                 var fecha_asignacion_tecnico = data.fecha_asignacion_tecnico || '';
//                 var hora_asignacion_tecnico = data.hora_asignacion_tecnico || '';
//                 var fecha_comienzo_ticket = data.fecha_comienzo_ticket || '';
//                 var hora_comienzo_ticket = data.hora_comienzo_ticket || '';
//                 var fecha_termino_ticket = data.fecha_termino_ticket || '';
//                 var hora_termino_ticket = data.hora_termino_ticket || '';
//                 var identificador = data.identificador || '';
//                 var id_prioridad = data.id_prioridad || '';
//                 var nombrePrioridad = data.nombrePrioridad || '';
//                 var id_estado = data.id_estado || '';
//                 var nombreEstado = data.nombreEstado || '';
//                 var nombreUsuario = data.nombreUsuario || '';
//                 var apePaternoUsuario = data.apePaternoUsuario || '';
//                 var apeMaternoUsuario = data.apeMaternoUsuario || '';
//                 var nombreTecnico = data.nombreTecnico || '';
//                 var apePaternoTecnico = data.apePaternoTecnico || '';
//                 var apeMaternoTecnico = data.apeMaternoTecnico || '';
//                 var id_categoria_ticket = data.id_categoria_ticket || '';
//                 var nombre_categoria = data.nombre_categoria || '';


//                 // var validarTicket = '';
//                 // if (id_estado == 6) { // Cambiar '=' por '=='
//                 //     validarTicket = `
//                 //         <button type="button" class="btn btn-primary" 
//                 //             onclick="window.open('class/correos/calificacion_ticket.php?id_ticket=${id_ticket}', '_blank')">
//                 //            CERRAR
//                 //         </button>
//                 //     `;
//                 // } else {
//                 //     validarTicket = '';
//                 // }
                

//                 var validacionCorreoTicket = '';    
//                 if (id_estado == 6) { // Cambiar '=' por '=='
//                     validacionCorreoTicket = `
//                        <div class="alert alert-info" role="alert">
//                         VALIDA TU TICKET EN TU CORREO 
//                         </div>
                      
//                     `;
//                 } else {
//                     validacionCorreoTicket = '';
//                 }

//                 // alert(id_estado);
//                 // Crear el input para el nombre del técnico o el select si no hay técnico asignado
//                 var inputNombreTecnico = '';
//                 if (nombreTecnico === "") {
//                     inputNombreTecnico = `
//                                 <select class="form-select" id="nombreTecnicoSelect">
//                                     <option value="">Seleccione un técnico</option>
//                                     <option value="Ramon">Ramon</option>
//                                     <option value="Manuel">Manuel</option>
//                                     <option value="Cristian">Cristian</option>
//                                 </select>`;
//                 } else {
//                     inputNombreTecnico = `<input type="text" class="form-control" id="nombreTecnico" placeholder="De" value="${nombreTecnico + " " + apePaternoTecnico + " " + apeMaternoTecnico}" disabled>`;
//                 }

//                 // Formatear fecha y hora correctamente
//                 if (fecha_asignacion_tecnico) {
//                     var date = new Date(fecha_asignacion_tecnico);
//                     fecha_asignacion_tecnico = date.toLocaleDateString('es-ES', {
//                         day: '2-digit',
//                         month: '2-digit',
//                         year: 'numeric'
//                     }).replace(/\//g, '-'); // Reemplazar "/" por "-"
//                 } else {
//                     fecha_asignacion_tecnico = '';
//                 }

//                 // Generar HTML dinámicamente para los distintos elementos
//                 var accionesAcordeonHTML = construirAccionesAcordeonHTML(data.acciones, data.id_ticket, id_estado, fecha_comienzo_ticket, hora_comienzo_ticket);

//                 // Comentarios, asignaciones y otros detalles que permanecen igual
//                 var estadoAsignadoHTML = ''; // Aquí generas HTML similar al que ya tenías para los diferentes estados.

//                 Swal.fire({
//                     title: `<div class="alert alert-dark" role="alert">TICKET A-00${id}</div>`,
//                     html: `
//                     ${validacionCorreoTicket}
                 
//                             <div class="alert alert-secondary" role="alert">
//                                 <div class="container-fluid"></div>
//                                 <form class="row g-3">
//                                     <div class="col-md-6">
//                                         <div class="form-floating">
//                                             <input type="date" class="form-control" id="floatingEmail" placeholder="Fecha" value="${fecha_creacion_inicio}" disabled>
//                                             <label for="floatingEmail">Fecha Ticket</label>
//                                         </div>
//                                     </div>
//                                     <div class="col-md-6">
//                                         <div class="form-floating">
//                                             <input type="text" class="form-control" id="floatingPassword" placeholder="hora" value="${hora_creacion_inicio}" disabled>
//                                             <label for="floatingPassword">Hora</label>
//                                         </div>
//                                     </div>
//                                     <div class="col-md-6">
//                                         <div class="form-floating">
//                                             <input type="text" class="form-control" id="floatingPassword" placeholder="De" value="${nombreUsuario + " " + apePaternoUsuario}" disabled>
//                                             <label for="floatingPassword">De</label>
//                                         </div>
//                                     </div>
//                                     <div class="col-md-6">
//                                         <div class="form-floating">
//                                             <input type="text" class="form-control" id="estadoTicket" placeholder="" value="${nombreEstado}" disabled>
//                                             <label for="estadoTicket">Estado</label>
//                                         </div>
//                                     </div>
//                                     <div class="col-md-6">
//                                         <div class="form-floating">
//                                             <input type="text" class="form-control" id="floatingName" placeholder="Asunto" value="${nombre_categoria}" disabled>
//                                             <label for="floatingName">Categoria</label>
//                                         </div>
//                                     </div>
//                                     <div class="col-md-6">
//                                         <div class="form-floating">
//                                             ${inputNombreTecnico}
//                                             <label for="floatingPassword">Técnico</label>
//                                         </div>
//                                     </div>
//                                     <div class="col-md-12">
//                                         <div class="form-floating">
//                                             <input type="text" class="form-control" id="floatingName" placeholder="Asunto" value="${asunto}" disabled>
//                                             <label for="floatingName">Asunto</label>
//                                         </div>
//                                     </div>
//                                     <div class="col-12">
//                                         <div class="form-floating">
//                                             <textarea class="form-control" placeholder="Address" id="floatingTextarea" disabled style="height: 100px;">${descripcion_ticket}</textarea>
//                                             <label for="floatingTextarea">Problema</label>
//                                         </div>
//                                     </div>
//                                 </form>
//                             </div>
                           
//                             ${estadoAsignadoHTML}
//                             ${accionesAcordeonHTML}
//                             `,
//                     showCancelButton: false,
//                     showConfirmButton: false,
//                     showDenyButton: false,
//                     allowOutsideClick: false,
//                     allowEscapeKey: false,
//                     allowEnterKey: false,
//                     width: "870px",
//                     padding: "40px",
//                     showCloseButton: true,
//                     customClass: {
//                         popup: 'cuerpo_modal_guardar',
//                     },
//                 });

//                 // Asignar la cadena de diferencia de tiempo al campo de entrada
//                 var inputDe1 = document.getElementById("de1");
//                 if (inputDe1) {
//                     inputDe1.value = diffString;
//                 } else {
//                     console.error('El elemento con id="de1" no se encontró en el DOM.');
//                 }
//             }
//         },
//         error: function (xhr, status, error) {
//             console.error('Error al cargar los detalles del ticket:', error);
//             Swal.fire({
//                 icon: 'error',
//                 title: 'Error!',
//                 text: 'Error al cargar los datos del ticket. Por favor, intente de nuevo.',
//                 confirmButtonText: 'Cerrar'
//             });
//         }
//     });
// }





function verTicketAdministrador(id) {
    $.ajax({
        url: "modelos/rescatar/ticket.php",
        type: "POST",
        data: {
            id: id
        },
        dataType: "json",
        success: function (data) {
            if (data) {
                var id_ticket = data.id_ticket || '';
                var fecha_creacion = data.fecha_creacion || '';
                var hora_creacion = data.hora_creacion || 'No hay detalles disponibles';
                var idAsunto = data.asunto || 'No disponible';
                var descripcion_ticket = data.descripcion_ticket || 'No hay detalles disponibles';
                var comentario_administrador = data.comentario_administrador || 'No hay detalles disponibles';

                var nombreUsuario = data.nombre_usuario || 'No disponible';
                var apePaternoUsuario = data.apellido_paterno_usuario || 'No disponible';
                var estado = data.estado || 'No disponible';
                var nombreEstado = data.nombreEstado || 'No disponible';
                var nombreTecnico = data.nombre_tecnico || 'No disponible';
                var apePaternoTecnico = data.apellido_paterno_tecnico || 'No disponible';
                var fechaTerminoAdmin = data.fecha_asignacion || '';
                var adjunto = data.adjunto || '';



                var accionesAcordeonHTML = construirAccionesAcordeonHTML(data.acciones, data.id, id_estado, fecha_proceso, hora_proceso);





                var title;
                if (id_estado == 1) {
                    title = '<div class="alert alert-warning" role="alert">Ticket Sin asignacion detecnico</div>';
                } else if (estado == 2) {
                    title = '<div class="alert alert-warning" role="alert">Su técnico es Aun no comienza el ticket</div>';
                } else {
                    title = '';
                }

                var inputFechaTerminoAdmin;
                if (fechaTerminoAdmin.trim() === '') {
                    inputFechaTerminoAdmin = ``;
                } else {
                    inputFechaTerminoAdmin = `<label for="fecha_termino" class="form-label">Fecha Resolución:</label>
                                <input type="date" class="form-control mb-3" value="${fechaTerminoAdmin.split('T')[0]}" disabled>`;
                }


                Swal.fire({
                    title: `<div class="alert alert-dark" role="alert">TICKET T-00${id_ticket}</div>`,
                    html: `<div class="alert alert-secondary" role="alert">
                                    <div class="container-fluid">${title}${inputFechaTerminoAdmin}
                                        <form class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="date" class="form-control" id="floatingName" placeholder="" value="${fecha_creacion}" disabled>
                                                    <label for="floatingName">Fecha</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="horaCreacionticket" placeholder="" value="${hora_creacion}" disabled>
                                                    <label for="horaCreacionticket">Hora</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="nombreUsuario" placeholder="" value="${nombreUsuario} ${apePaternoUsuario}" disabled>
                                                    <label for="nombreUsuario">Usuario</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="estadoTicket" placeholder="" value="${nombreEstado}" disabled>
                                                    <label for="estadoTicket">Estado</label>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="asuntoTicket" placeholder="" value="${idAsunto}" disabled>
                                                    <label for="asuntoTicket">Asunto</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating">
                                                    <textarea class="form-control" placeholder="Address" id="descripcionTicket" style="height: 100px;" disabled>${descripcion_ticket}</textarea>
                                                    <label for="descripcionTicket">Problema</label>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>`,
                    width: "870px",
                    padding: "40px",
                    showCancelButton: false,
                    confirmButtonText: 'CERRAR',
                    confirmButtonColor: '#5B8E4A',
                    customClass: {
                        popup: 'cuerpo_modal_guardar',
                        confirmButton: 'bt_activar_alumno'
                    }
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar los detalles del ticket:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Error al cargar los datos del ticket. Por favor, intente de nuevo.',
                confirmButtonText: 'Cerrar'
            });
        }
    });
}

function mostrarArchivo(urlArchivo) {

    alert(urlArchivo);
    // Determinar el tipo de archivo basado en la extensión
    const fileType = urlArchivo.split('.').pop().toLowerCase();
    let contentHtml = '';  // Contenido HTML basado en el tipo de archivo

    switch (fileType) {
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            contentHtml = '<img src="' + urlArchivo + '" class="img-fluid" alt="Imagen Adjunta">';
            break;
        case 'pdf':
            contentHtml = '<embed src="' + urlArchivo + '" type="application/pdf" width="100%" height="500px"/>';
            break;
        case 'mp4':
            contentHtml = '<video controls width="100%"><source src="' + urlArchivo + '" type="video/mp4">Your browser does not support the video tag.</video>';
            break;
        case 'csv':
            contentHtml = '<div>CSV file detected. <a href="' + urlArchivo + '" target="_blank">Download CSV</a></div>';
            break;
        default: let timer; // Variable para el temporizador de inactividad
            let modalTimer; // Variable para el temporizador del modal

    }
}


//******************TICKET_ASIGNADOS.php*********************** */ 
//*************TICKET_ASIGNADOSS****************************** */ 





function finalizarTicket(id) {
    Swal.fire({
        title: '<div class="alert alert-dark" role="alert">¿SEGURO DE FINALIZAR TICKET?</div>',
        html: `
                        <div class="form-floating">
                            <textarea id="comentario_finalizacion" class="form-control" placeholder="Escribe un comentario..."></textarea>
                            <label for="comentario_finalizacion">Comentario</label>
                        </div>
                    `,
        showCancelButton: true,
        confirmButtonColor: '#5B8E4A',
        cancelButtonColor: '#d33',
        confirmButtonText: 'FINALIZAR',
        cancelButtonText: 'CANCELAR',
        width: "870px",
        padding: "40px",
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_activar_alumno',
            cancelButton: 'bt_activar_alumno'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            var comentarioFinalizacion = document.getElementById('comentario_finalizacion').value;

            Swal.showLoading(); // Mostrar indicador de carga

            $.ajax({
                url: 'modelos/guardar/guardar_ticket.php',
                type: 'POST',
                data: {
                    id: id,
                    accion: 'terminar_proceso',
                    comentario_finalizacion: comentarioFinalizacion // Enviar el comentario con los datos
                },
                success: function (response) {
                    Swal.fire({
                        title: '<div class="alert alert-dark" role="alert">TICKET FINALIZADO</div>',
                        showConfirmButton: false, // No mostrar botón de confirmación
                        timer: 2000, // Cerrar automáticamente después de 2 segundos
                        padding: "40px",
                        allowOutsideClick: false, // No permitir cerrar al hacer clic fuera del modal
                        timerProgressBar: true, // Activar la barra de progreso
                        customClass: {
                            popup: 'cuerpo_modal_guardar',
                            confirmButton: 'bt_activar_alumno'
                        }
                    }).then(() => {
                        location.reload(); // Recargar la página después de mostrar el mensaje de éxito
                    });
                },
                error: function (xhr, status, error) {
                    console.error("Error al finalizar el ticket:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al finalizar el ticket: ' + error,
                        confirmButtonColor: '#5B8E4A',
                        confirmButtonText: 'CERRAR',
                        padding: "40px",
                        customClass: {
                            popup: 'cuerpo_modal_guardar',
                            confirmButton: 'bt_activar_alumno'
                        }
                    });
                }
            }).always(function () {
                Swal.hideLoading(); // Ocultar indicador de carga
            });
        }
    });
}


function calcularDiasResolucion() {
    var fechaEstimada = new Date(document.getElementById('fecha_termino').value);
    var fechaActual = new Date();

    // Ajustar la fecha actual para ignorar la hora y comparar solo la fecha
    fechaActual.setHours(0, 0, 0, 0);

    var diferenciaTiempo = fechaEstimada - fechaActual;
    var diferenciaDias = Math.ceil(diferenciaTiempo / (1000 * 3600 * 24)); // Convertir de milisegundos a días

    // Asegurarse de que el contador de días comience desde 1
    if (diferenciaDias < 1) {
        diferenciaDias = 1;
    }

    var diasResolucionInput = document.getElementById('dias_resolucion');
    diasResolucionInput.value = diferenciaDias + " Días";
}

function agregarComentarioTecnico(idTicket) {
    var comentario = $('#comentario_tecnico').val();
    if (comentario.trim() === '') {
        alert('Por favor, escribe un comentario anteseee de enviar.');
        return;
    }
    $.ajax({
        url: "modelos/guardar/guardar_avance_tecnicos.php",
        type: 'POST',
        data: {
            id: idTicket,
            comentario: comentario
        },
        success: function (response) {
            // alert('Comentario agregado correctamente');
            $('#comentario_tecnico').val(''); // Limpiar el campo de texto
            actualizarTimeline(idTicket); // Llamar a la función para actualizar la línea de tiempo
        },
        error: function () {
            alert('Error al agregar el comentario');
        }
    });
}

function agregarCategoria() {
    Swal.fire({
        title: 'Agregar Nueva Categoría',
        html:
            '<input id="nombreCategoria" class="swal2-input" placeholder="Nombre de la categoría">',
        confirmButtonText: 'Guardar',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        focusConfirm: false,
        preConfirm: async () => {
            const nombre = document.getElementById('nombreCategoria').value.trim();

            if (!nombre) {
                Swal.showValidationMessage('Debes ingresar un nombre');
                return false;
            }

            try {
                const res = await fetch('modelos/rescatar/categorias_ticket.php');
                const data = await res.json();
                const existe = data.some(cat => cat.nombre_categoria.toLowerCase() === nombre.toLowerCase());

                if (existe) {
                    Swal.showValidationMessage('Ya existe una categoría con ese nombre');
                    return false;
                }

                return nombre;
            } catch (error) {
                console.error(error);
                Swal.showValidationMessage('Error al validar categorías existentes');
                return false;
            }
        },
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_crear',
            cancelButton: 'bt_cancelar'
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const data = { nombre_categoria: result.value };

            fetch('modelos/guardar/crear_categorias.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Categoría creada',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'No se pudo crear la categoría', 'error');
                }
            })
            .catch(error => {
                console.error(error);
                Swal.fire('Error', 'Error de conexión o servidor', 'error');
            });
        }
    });
}

function obtenerModalBootstrap(idModal) {
    const elemento = document.getElementById(idModal);
    return elemento ? bootstrap.Modal.getOrCreateInstance(elemento) : null;
}

function abrirModalAdministrarCategorias() {
    const modal = obtenerModalBootstrap('modalAdministrarCategorias');
    if (!modal) {
        Swal.fire('Error', 'No se encontro el modal de categorias.', 'error');
        return;
    }

    modal.show();
    cargarTablaAdministrarCategorias();
}

function cargarTablaAdministrarCategorias() {
    const contenedor = $('#contenedorAdministrarCategorias');
    contenedor.html(`
        <div class="d-flex justify-content-center align-items-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
    `);

    $.ajax({
        url: 'modelos/rescatar/administrar_categorias.php',
        type: 'GET',
        success: function (html) {
            contenedor.html(html);
        },
        error: function () {
            contenedor.html('<div class="alert alert-danger mb-0">No se pudo cargar el listado de categorias.</div>');
        }
    });
}

function abrirModalEditarCategoria(categoria) {
    $('#editar_id_categoria').val(categoria.id);
    $('#editar_nombre_categoria').val(categoria.nombre);
    $('#editar_abreviacion').val(categoria.abreviacion);
    $('#editar_icono').val(categoria.icono);
    $('#editar_orden').val(categoria.orden);

    const modal = obtenerModalBootstrap('modalEditarCategoria');
    if (modal) {
        modal.show();
    }
}

$(document).on('click', '.js-editar-categoria', function () {
    abrirModalEditarCategoria({
        id: $(this).data('id_categoria'),
        nombre: $(this).data('nombre_categoria') || '',
        abreviacion: $(this).data('abreviacion') || '',
        icono: $(this).data('icono') || '',
        orden: $(this).data('orden') || 0
    });
});

$(document).on('click', '.js-toggle-estado-categoria', function () {
    const idCategoria = $(this).data('id_categoria');
    const estadoActual = parseInt($(this).data('estado'), 10) || 0;
    const accion = estadoActual === 1 ? 'desactivar' : 'activar';

    Swal.fire({
        title: `<div class="alert alert-dark" role="alert">${accion.toUpperCase()} CATEGORIA</div>`,
        text: `Se va a ${accion} esta categoria.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
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
            url: 'modelos/editar/cambiar_estado_categoria_ticket.php',
            type: 'POST',
            dataType: 'json',
            data: { id_categoria: idCategoria },
            success: function (response) {
                if (!response || !response.success) {
                    Swal.fire('Error', response.message || 'No se pudo cambiar el estado.', 'error');
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: response.message,
                    timer: 1400,
                    showConfirmButton: false
                });
                cargarTablaAdministrarCategorias();
            },
            error: function () {
                Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
            }
        });
    });
});

$(document).on('submit', '#formEditarCategoria', function (event) {
    event.preventDefault();

    const nombre = $('#editar_nombre_categoria').val().trim();
    if (!nombre) {
        Swal.fire('Error', 'El nombre de la categoria es obligatorio.', 'error');
        return;
    }

    $.ajax({
        url: 'modelos/editar/editar_categoria_ticket.php',
        type: 'POST',
        dataType: 'json',
        data: $(this).serialize(),
        success: function (response) {
            if (!response || !response.success) {
                Swal.fire('Error', response.message || 'No se pudo guardar la categoria.', 'error');
                return;
            }

            const modal = obtenerModalBootstrap('modalEditarCategoria');
            if (modal) {
                modal.hide();
            }

            Swal.fire({
                icon: 'success',
                title: response.message,
                timer: 1400,
                showConfirmButton: false
            });
            cargarTablaAdministrarCategorias();
        },
        error: function () {
            Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
        }
    });
});


function guardarCambios() {
    Swal.fire({
        title: '<div class="alert alert-dark" role="alert">GUARDAR CAMBIOS</div>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_activar_alumno',
            cancelButton: 'bt_activar_alumno'
        }
    }).then((result) => {
        if (!result.isConfirmed) return;

        // Recolectar todos los técnicos y sus categorías activas
        const cambiosPorTecnico = [];

        document.querySelectorAll('.submenu-container').forEach(container => {
            const row = container.closest('tr');
            if (!row) {
                return;
            }

            const id_usuario = parseInt(row.getAttribute('data-id_usuario'), 10);
            if (!id_usuario) {
                return;
            }

            const categoriasSeleccionadas = [];
            container.querySelectorAll('.submenu-item.green').forEach(item => {
                const id_categoria = parseInt(item.getAttribute('data-id_categoria'), 10);
                if (!Number.isNaN(id_categoria)) {
                    categoriasSeleccionadas.push(id_categoria);
                }
            });

            cambiosPorTecnico.push({
                id_usuario,
                ids_categorias: categoriasSeleccionadas
            });
        });

        if (cambiosPorTecnico.length === 0) {
            Swal.fire('Error', 'No se encontraron técnicos para guardar.', 'error');
            return;
        }

        // Enviar cada conjunto de permisos individualmente
        const peticiones = cambiosPorTecnico.map(cambio =>
            fetch('modelos/guardar/guardar_permisos_categoria.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(cambio)
            }).then(response => response.json())
        );

        // Esperar que todas las respuestas terminen
        Promise.all(peticiones)
            .then(respuestas => {
                const algunError = respuestas.some(r => !r || r.status !== 'ok');
                if (algunError) {
                    Swal.fire('Error', 'Ocurrió un error al guardar una o más asignaciones.', 'error');
                } else {
                    Swal.fire({
                        title: '<div class="alert alert-dark" role="alert">PERMISOS GUARDADOS</div>',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'cuerpo_modal_guardar'
                        }
                    }).then(() => location.reload());
                }
            })
            .catch(error => {
                console.error('Error de red o servidor:', error);
                Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
            });
    });
}


//******************ticket.php********************************************/ 
//*************CONVERSACION***********************************************

function verConversacion(idTicket, idUsuarioSession, idTecnico) {
    $.ajax({
        url: 'modelos/rescatar/ticket_administracion.php',
        type: 'POST',
        data: { id: idTicket },
        success: function (ticketResponse) {
            console.log(ticketResponse); // Depuración: ver la respuesta en la consola

            if (!ticketResponse || !ticketResponse.asunto) {
                Swal.fire('Error', 'No se pudo obtener los datos del ticket.', 'error');
                return;
            }

            // Obtener la conversación del ticket
            $.ajax({
                url: 'modelos/rescatar/conversacion.php',
                type: 'GET',
                data: { id_ticket: idTicket },
                success: function (response) {
                    console.log(response); // Depuración: ver la respuesta en la consola
                    let mensajes = response.mensajes || [];

                    // Crear el HTML para la conversación con barra desplazadora
                    let conversacionHTML = '<div class="chat-container" style="max-height: 400px; overflow-y: auto;">';

                    if (mensajes.length === 0) {
                        conversacionHTML += '<p style="text-align: center; color: red;">No existen mensajes asociados a este ticket</p>';
                    } else {
                        mensajes.forEach(function (mensaje) {
                            let tipoClase = mensaje.id_usuario === idUsuarioSession ? 'right user-message' : 'left tech-message';
                            conversacionHTML += `
                                    <div class="chat-message ${tipoClase}">
                                        <div class="chat-message-content">
                                            <p>${mensaje.contenido}</p>`;

                            if (mensaje.adjunto && mensaje.adjunto.length > 0) {
                                mensaje.adjunto.forEach(function (adjunto) {
                                    if (adjunto.match(/\.(jpeg|jpg|gif|png)$/)) {
                                        conversacionHTML += `<img src="archivos/ticket/${adjunto}" class="adjunto-img adjunto-img-mediano">`;
                                    } else {
                                        conversacionHTML += `<a href="archivos/ticket/${adjunto}" download><i class="bi bi-paperclip"></i> Descargar Adjunto</a>`;
                                    }
                                });
                            }

                            conversacionHTML += `<span class="chat-timestamp">${mensaje.fecha} ${mensaje.hora}</span>
                                        </div>
                                    </div>`;
                        });
                    }

                    conversacionHTML += '</div>';

                    // Agregar textarea, botón para enviar mensajes, botón para adjuntar archivos y área de previsualización
                    conversacionHTML += `
                            <div class="chat-input-container">
                                <div class="chat-input">
                                    <textarea id="nuevoMensaje" rows="1" placeholder="Escribe un mensaje..."></textarea>
                                    <input type="file" id="adjuntarArchivo" multiple style="display: none;" onchange="adjuntarArchivo(event)">
                                    <button class="attach-button" onclick="document.getElementById('adjuntarArchivo').click();">
                                        <i class="bi bi-paperclip"></i>
                                        <span id="adjuntoCounter" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"></span>
                                    </button>
                                    <button onclick="enviarMensaje('${idTicket}', '${idUsuarioSession}', '${idTecnico}')">
                                        <i class="bi bi-send"></i>
                                    </button>
                                </div>
                                <div id="previewContainer" class="preview-container"></div>
                            </div>`;

                    // Crear el acordeón para mostrar los detalles del ticket
                    let acordeonHTML = `
                            <div id="customAccordion">
                                <div class="accordion-header" onclick="activarAcordeon(this)">
                                    Detalles del Ticket
                                </div>
                                <div class="accordion-body">
                                    <p><strong>ID Ticket:</strong> ${ticketResponse.id_ticket}</p>
                                    <p><strong>Prioridad:</strong> ${ticketResponse.prioridad}</p>
                                    <p><strong>Estado:</strong> ${ticketResponse.nombreEstado}</p>
                                </div>
                            </div>`;

                    // Mostrar la conversación en un modal con el asunto como título
                    Swal.fire({
                        title: `<div class="alert alert-dark" role="alert">${ticketResponse.asunto}</div>`,
                        html: acordeonHTML + conversacionHTML,
                        width: '870px',
                        showCloseButton: true,
                        showConfirmButton: false,
                        padding: "40px",
                        customClass: {
                            popup: 'cuerpo_modal_guardar'
                        }
                    });
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Error al cargar la conversación:', textStatus, errorThrown); // Depuración
                    Swal.fire('Error', 'No se pudo cargar la conversación.', 'error');
                }
            });
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error al cargar el ticket:', textStatus, errorThrown); // Depuración
            Swal.fire('Error', 'No se pudo cargar el ticket.', 'error');
        }
    });
}











let adjuntos = []; // Array para almacenar los archivos adjuntos
function adjuntarArchivo(event) {
    const fileInput = event.target;
    const files = fileInput.files;
    const previewContainer = document.getElementById('previewContainer');
    const adjuntoCounter = document.getElementById('adjuntoCounter');

    // Reemplaza el array con los nuevos archivos
    adjuntos = Array.from(files);

    // Limpiar previsualizaciones anteriores
    previewContainer.innerHTML = '';

    adjuntos.forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('preview-image');
                previewContainer.appendChild(img);
            }
            reader.readAsDataURL(file);
        } else {
            const filePreview = document.createElement('div');
            filePreview.classList.add('file-preview');
            filePreview.innerHTML = `<i class="bi bi-paperclip"></i> Adjunto ${index + 1}`;
            previewContainer.appendChild(filePreview);
        }
    });

    if (adjuntos.length > 0) {
        adjuntoCounter.textContent = adjuntos.length;
    } else {
        adjuntoCounter.textContent = '';
    }

    // Limpiar el input de archivo para permitir agregar más archivos con el mismo nombre
    fileInput.value = '';
}

function enviarMensaje(idTicket, idUsuario, idTecnico) {
    let nuevoMensaje = document.getElementById('nuevoMensaje');
    let formData = new FormData();

    // Verificar si el mensaje está vacío y no hay adjuntos
    if (!validarCamposVacios(nuevoMensaje) && adjuntos.length === 0) {
        nuevoMensaje.style.borderColor = 'red';
        return;
    }

    formData.append('id_ticket', idTicket);
    formData.append('contenido', nuevoMensaje.value);
    formData.append('tipo', 'user'); // O 'tech' según corresponda
    formData.append('id_usuario', idUsuario);
    formData.append('id_tecnico', idTecnico);
    formData.append('accion', 'enviar');

    // Agregar adjuntos solo si existen
    if (adjuntos.length > 0) {
        adjuntos.forEach(file => {
            formData.append('adjunto[]', file);
        });
    }

    $.ajax({
        url: 'modelos/guardar/conversacion.php', // Ruta correcta para el AJAX
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            if (response.error) {
                Swal.fire('Error', response.error, 'error');
                return;
            }
            // Limpiar el textarea y el input de archivo después de enviar el mensaje
            nuevoMensaje.value = '';
            document.getElementById('adjuntarArchivo').value = '';
            document.getElementById('previewContainer').innerHTML = '';
            document.getElementById('adjuntoCounter').textContent = '';
            nuevoMensaje.style.borderColor = '';
            adjuntos = []; // Reiniciar el array de archivos adjuntos después de enviar

            // Actualizar la conversación sin cerrar el modal
            actualizarConversacion(idTicket, idUsuario, idTecnico);
        },
        error: function (error) {
            console.log(error);
            Swal.fire('Error', 'Error al enviar el mensaje', 'error');
        }
    });
}



function actualizarConversacion(idTicket, idUsuarioSession, idTecnico) {
    $.ajax({
        url: 'modelos/rescatar/conversacion.php', // Ruta correcta para el AJAX
        type: 'GET',
        data: { id_ticket: idTicket },
        success: function (response) {
            console.log(response); // Depuración: ver la respuesta en la consola
            let mensajes = response.mensajes || [];
            let adjuntos = response.adjuntos || [];
            let conversacionHTML = '';

            if (mensajes.length === 0 && adjuntos.length === 0) {
                conversacionHTML += '<p style="text-align: center; color: red;">No existen mensajes asociados a este ticket</p>';
            } else {
                // Primero agregamos los mensajes en orden correcto (de más antiguo a más reciente)
                mensajes.reverse().forEach(function (mensaje) {
                    let tipoClase = mensaje.id_usuario === idUsuarioSession ? 'right user-message' : 'left tech-message';
                    conversacionHTML += `
                            <div class="chat-message ${tipoClase}">
                                <div class="chat-message-content">
                                    <p>${mensaje.contenido}</p>
                                    <span class="chat-timestamp">${mensaje.fecha} ${mensaje.hora}</span>
                                </div>
                            </div>`;
                });

                // Luego agregamos los adjuntos como mensajes separados en el mismo orden
                adjuntos.reverse().forEach(function (adjunto) {
                    let tipoClase = 'left tech-message'; // Asumiendo que los adjuntos son siempre de la misma clase
                    if (adjunto.match(/\.(jpeg|jpg|gif|png)$/)) {
                        conversacionHTML += `
                                <div class="chat-message ${tipoClase}">
                                    <div class="chat-message-content">
                                        <img src="archivos/ticket/${adjunto}" class="adjunto-img adjunto-img-mediano">
                                    </div>
                                </div>`;
                    } else {
                        conversacionHTML += `
                                <div class="chat-message ${tipoClase}">
                                    <div class="chat-message-content">
                                        <a href="archivos/ticket/${adjunto}" download><i class="bi bi-paperclip"></i> Descargar Adjunto</a>
                                    </div>
                                </div>`;
                    }
                });
            }

            $('.chat-container').html(conversacionHTML);
            $('.chat-container').scrollTop($('.chat-container')[0].scrollHeight); // Desplazar al final de la conversación
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error al actualizar la conversación:', textStatus, errorThrown); // Depuración
            Swal.fire('Error', 'Error al actualizar la conversación', 'error');
        }
    });
}




function validarCamposVacios(...campos) {
    for (let campo of campos) {
        if (campo.value.trim() === '') {
            return false;
        }
    }
    return true;
}




//**************************************************************************
//**************************************************************************




// function verConversacion(idTicket, idUsuarioSession, idTecnico) {
//     let row = $(`a[onclick="verConversacion('${idTicket}', '${idUsuarioSession}', '${idTecnico}')"]`).closest('tr');

//     // Verificar si ya existe una fila de conversación
//     if (row.next().hasClass('conversation-row')) {
//         row.next().remove(); // Eliminar la conversación si ya está desplegada
//         return; // Salir de la función
//     }

//     $.ajax({
//         url: 'modelos/rescatar/conversacion.php', // Ruta correcta para el AJAX
//         type: 'GET',
//         data: { id_ticket: idTicket },
//         success: function(response) {
//             console.log(response); // Depuración: ver la respuesta en la consola
//             let mensajes = response.mensajes || [];

//             mostrarEstructuraConversacion(row, idTicket, idUsuarioSession, idTecnico, mensajes);
//         },
//         error: function(jqXHR, textStatus, errorThrown) {
//             console.error('Error al cargar la conversación:', textStatus, errorThrown); // Depuración
//             alert('Error al cargar la conversación');
//         }
//     });
// }

// function mostrarEstructuraConversacion(row, idTicket, idUsuarioSession, idTecnico, mensajes) {
//     // Crear el HTML para la conversación con barra desplazadora
//     let conversacionHTML = '<div class="chat-container" style="max-height: 200px; overflow-y: auto;">';

//     if (mensajes.length === 0) {
//         conversacionHTML += '<p style="text-align: center; color: red;">No existen mensajes asociados a este ticket</p>';
//     } else {
//         // Ordenar los mensajes por fecha y hora ascendente
//         mensajes.sort((a, b) => {
//             let dateA = new Date(`${a.fecha} ${a.hora}`);
//             let dateB = new Date(`${b.fecha} ${b.hora}`);
//             return dateA - dateB;
//         });

//         mensajes.forEach(function(mensaje) {
//             let tipoClase = mensaje.id_usuario === idUsuarioSession ? 'right user-message' : 'left tech-message';
//             conversacionHTML += `
//                 <div class="chat-message ${tipoClase}">
//                     <div class="chat-message-content">
//                         <p>${mensaje.contenido}</p>
//                         <span class="chat-timestamp">${mensaje.fecha} ${mensaje.hora}</span>
//                     </div>
//                 </div>`;
//         });
//     }

//     conversacionHTML += '</div>';

//     // Agregar textarea y botón para enviar mensajes
//     conversacionHTML += `
//         <div class="chat-input">
//             <textarea id="nuevoMensaje" class="form-control" rows="3" placeholder="Escribe un mensaje..."></textarea>
//             <button class="btn btn-primary mt-2" onclick="enviarMensaje('${idTicket}', '${idUsuarioSession}', '${idTecnico}')">
//                 Enviar <i class="bi bi-mouse"></i>
//             </button>
//         </div>`;

//     let newRow = `<tr class="conversation-row"><td colspan="7">${conversacionHTML}</td></tr>`;
//     row.after(newRow); // Agregar la conversación debajo de la fila actual
//     $('.chat-container').scrollTop($('.chat-container')[0].scrollHeight); // Desplazar al final de la conversación
// }

// function enviarMensaje(idTicket, idUsuario, idTecnico) {
//     let nuevoMensaje = document.getElementById('nuevoMensaje');

//     if (!validarCamposVacios(nuevoMensaje)) { // Usar la función validarCamposVacios para validar el textarea
//         nuevoMensaje.style.borderColor = 'red';
//         return;
//     }

//     $.ajax({
//         url: 'modelos/guardar/conversacion.php', // Ruta correcta para el AJAX
//         type: 'POST',
//         data: { 
//             id_ticket: idTicket,
//             contenido: nuevoMensaje.value,
//             tipo: 'user', // O 'tech' según corresponda
//             id_usuario: idUsuario, // Pasamos el ID del usuario actual
//             id_tecnico: idTecnico  // Pasamos el ID del técnico actual
//         },
//         success: function(response) {
//             if (response.error) {
//                 alert(response.error);
//                 return;
//             }
//             // Actualizar la conversación sin cerrar el acordeón
//             actualizarConversacion(idTicket, idUsuario, idTecnico);
//         },
//         error: function(error) {
//             console.log(error);
//             alert('Error al enviar el mensaje');
//         }
//     });

//     // Limpiar el textarea después de enviar el mensaje
//     nuevoMensaje.value = '';
//     nuevoMensaje.style.borderColor = ''; // Restablecer el borde
// }

// function actualizarConversacion(idTicket, idUsuarioSession, idTecnico) {
//     $.ajax({
//         url: 'modelos/rescatar/conversacion.php', // Ruta correcta para el AJAX
//         type: 'GET',
//         data: { id_ticket: idTicket },
//         success: function(response) {
//             console.log(response); // Depuración: ver la respuesta en la consola
//             let mensajes = response.mensajes || [];
//             let conversacionHTML = '';

//             if (mensajes.length === 0) {
//                 conversacionHTML += '<p style="text-align: center; color: red;">No existen mensajes asociados a este ticket</p>';
//             } else {
//                 // Ordenar los mensajes por fecha y hora ascendente
//                 mensajes.sort((a, b) => {
//                     let dateA = new Date(`${a.fecha} ${a.hora}`);
//                     let dateB = new Date(`${b.fecha} ${b.hora}`);
//                     return dateA - dateB;
//                 });

//                 mensajes.forEach(function(mensaje) {
//                     let tipoClase = mensaje.id_usuario === idUsuarioSession ? 'right user-message' : 'left tech-message';
//                     conversacionHTML += `
//                         <div class="chat-message ${tipoClase}">
//                             <div class="chat-message-content">
//                                 <p>${mensaje.contenido}</p>
//                                 <span class="chat-timestamp">${mensaje.fecha} ${mensaje.hora}</span>
//                             </div>
//                         </div>`;
//                 });
//             }

//             $('.chat-container').html(conversacionHTML);
//             $('.chat-container').scrollTop($('.chat-container')[0].scrollHeight); // Desplazar al final de la conversación
//         },
//         error: function(jqXHR, textStatus, errorThrown) {
//             console.error('Error al actualizar la conversación:', textStatus, errorThrown); // Depuración
//             alert('Error al actualizar la conversación');
//         }
//     });
// }





//******************principal.php*********************** */ 
//*************CONTEDOR****************************** */ 
function agregar_contenedorr(idUsuario) {
    const content = document.createElement('div');
    content.className = 'modal-content';
    content.innerHTML = `
          <div class="alert alert-secondary" role="alert">
            <form class="row g-3" id="ticketForm">
                <div class="col-md-6 col-sm-12">
                    <div class="input-column">
                        <label for="imageInput" class="btn btn-primary">
                            Subir Imagen
                            <input type="file" id="imageInput" accept="image/*" onchange="previewImage(event)" style="display: none;">
                        </label>
                        <img id="imagePreview" src="imagenes/logo_contenedor.png" alt="Vista previa de la imagen" style="width: 100%; height: auto; max-height: 200px; margin-top: 10px;">
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="input-column">
                        <label for="textInput1" class="form-label">Nombre Contenedor:</label>
                        <input type="text" id="textInput1" name="nombreContenedor" class="form-control mb-3" placeholder="Nombre del contenedor">
                        <label for="textInput2" class="form-label">URL:</label>
                        <i class="fas fa-info-circle" data-bs-toggle="modal" data-bs-target="#videoModal" style="color:red;"></i>
                        <input type="text" id="textInput2" name="url" class="form-control mb-1" placeholder="www.seduc.cl">
                        <!-- Icono que activa un modal -->
                    </div>
                </div>
            </form>
            <!-- Modal -->
            <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <video controls width="100%">
                                <source src="imagenes/video_explicativo_2.mp4" type="video/mp4">
                                Video explicativo
                            </video>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        `;

    Swal.fire({
        title: '<div class="alert alert-dark" role="alert">AGREGAR CONTENEDOR</div>',
        html: content,
        width: '870px',
        padding: '40px',
        showCloseButton: true,
        backdrop: true, // Evitar cerrar al hacer clic fuera
        allowOutsideClick: false, // Evitar cerrar al hacer clic fuera
        showCancelButton: true,
        confirmButtonColor: '#5B8E4A',
        cancelButtonColor: '#d33',
        confirmButtonText: 'CREAR',
        cancelButtonText: 'CANCELAR',
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_activar_alumno',
            cancelButton: 'bt_activar_alumno'
        },
        preConfirm: () => {
            return new Promise((resolve) => {
                const imageInput = document.getElementById('imageInput').files[0];
                const nombreContenedor = document.getElementById('textInput1').value;
                const url = document.getElementById('textInput2').value;
                if (!nombreContenedor) {
                    Swal.showValidationMessage('Ingrese un nombre a su contenedor.');
                    resolve();
                    return;
                }
                if (!url) {
                    Swal.showValidationMessage('Ingrese una URL a su contenedor.');
                    resolve();
                    return;
                }
                let formData = new FormData();
                formData.append('image', imageInput);
                formData.append('nombreContenedor', nombreContenedor);
                formData.append('url', url);
                formData.append('idUsuario', idUsuario); // Añadir ID de usuario al formData
                $.ajax({
                    url: 'modelos/guardar/guardar_contenedor.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        Swal.fire({
                            title: '<div class="alert alert-dark" role="alert">GUARDADO</div>',
                            icon: 'success',
                            showConfirmButton: false, // Oculta el botón de confirmación
                            timer: 2000, // Establece un temporizador de 5 segundos
                            customClass: {
                                popup: 'cuerpo_modal_guardar',
                            },
                            willClose: () => {
                                // Recargar la página automáticamente cuando el modal se cierre
                                window.location.reload();
                            }
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error("Error en la solicitud AJAX:", error);
                        Swal.fire('Error', 'Hubo un error al guardar el contenedor: ' + xhr.responseText, 'error');
                        resolve();
                    }
                });
            });
        }
    });
}


//******************alertas*********************** */
//******************Todas las paginas *********************** */
function mostrarAlertas(idUsuarioSession) {
    const offcanvasHtml = `
<div class="offcanvas offcanvas-end shadow-sm" tabindex="-1" id="offcanvasDerecho" aria-labelledby="offcanvasDerechoLabel" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-semibold d-flex align-items-center gap-2" id="offcanvasDerechoLabel">
            <i class="bi bi-journal-text text-primary fs-5"></i> Mis Recordatorios
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column justify-content-between px-3 py-2">
        <div id="contenedorPrincipal">
            <div class="mb-3 text-end">
                <button id="botonAgregarTarea" class="btn btn-light border rounded-pill shadow-sm d-flex align-items-center gap-2 px-3 py-1">
                    <i class="bi bi-plus-circle text-success"></i>
                    <span class="fw-semibold">Añadir tarea</span>
                </button>
            </div>

            <form id="formularioTarea" class="mb-3" style="display: none;">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="tituloTarea" placeholder="Título de la tarea">
                    <label for="tituloTarea">Título</label>
                </div>
                <div class="form-floating mb-3">
                    <textarea class="form-control" id="detallesTarea" placeholder="Detalles" style="height: 100px;"></textarea>
                    <label for="detallesTarea">Detalle</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="date" class="form-control" id="fechaTarea" placeholder="Fecha">
                    <label for="fechaTarea">Fecha</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 rounded-pill">Guardar Tarea</button>
            </form>

            <hr class="my-3">
            <div id="mensajeSinTareas" class="text-center text-muted" style="display: none;">
                <i class="bi bi-journal-check" style="font-size: 3rem;"></i>
                <h6 class="mt-2">Aún no hay tareas</h6>
                <p>Agrega tareas para hacer seguimiento de tus pendientes.</p>
            </div>

            <ul id="listaTareas" class="list-group list-group-flush">
                <!-- Tareas activas -->
            </ul>
        </div>

        <div>
            <hr>
            <h6 id="completadasHeader" class="text-muted fw-semibold mb-2" style="cursor: pointer;">
                <i class="bi bi-check-circle-fill me-2 text-success"></i>Completadas (<span id="completadasCount">0</span>)
            </h6>
            <ul id="listaTareasCompletadas" class="list-group list-group-flush" style="display: none;">
                <!-- Tareas completadas -->
            </ul>
        </div>
    </div>
</div>

        `;

    document.body.insertAdjacentHTML('beforeend', offcanvasHtml);

    const offcanvasElement = document.getElementById('offcanvasDerecho');
    const offcanvas = new bootstrap.Offcanvas(offcanvasElement);
    offcanvas.show();

    offcanvasElement.addEventListener('hidden.bs.offcanvas', () => {
        offcanvasElement.remove();
    });

    const botonAgregarTarea = document.getElementById('botonAgregarTarea');
    const formularioTarea = document.getElementById('formularioTarea');

    botonAgregarTarea.addEventListener('click', function () {
        formularioTarea.style.display = 'block';
        this.style.display = 'none';
        document.getElementById('mensajeSinTareas').style.display = 'none'; // Ocultar mensaje de "Aún no hay tareas"
    });

    document.addEventListener('click', function (event) {
        if (!formularioTarea.contains(event.target) && !botonAgregarTarea.contains(event.target)) {
            formularioTarea.style.display = 'none';
            botonAgregarTarea.style.display = 'block';
            const listaTareas = document.getElementById('listaTareas');
            if (listaTareas.children.length === 0) {
                document.getElementById('mensajeSinTareas').style.display = 'block'; // Mostrar mensaje de "Aún no hay tareas" si no hay tareas
            }
        }
    });

    document.getElementById('formularioTarea').addEventListener('submit', function (event) {
        event.preventDefault();
        agregarTarea();
    });

    document.getElementById('completadasHeader').addEventListener('click', function () {
        const listaTareasCompletadas = document.getElementById('listaTareasCompletadas');
        if (listaTareasCompletadas.style.display === 'none') {
            listaTareasCompletadas.style.display = 'block';
        } else {
            listaTareasCompletadas.style.display = 'none';
        }
    });

    function agregarTarea() {
        const titulo = document.getElementById('tituloTarea').value;
        const detalles = document.getElementById('detallesTarea').value;
        const fecha = document.getElementById('fechaTarea').value;

        if (!validarCamposVacios(document.getElementById('tituloTarea'), document.getElementById('detallesTarea'), document.getElementById('fechaTarea'))) {
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'modelos/guardar/recordatorio.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    añadirTareaALista(titulo, detalles, fecha, response.id, 'NO', false);
                    document.getElementById('formularioTarea').reset();
                    formularioTarea.style.display = 'none';
                    botonAgregarTarea.style.display = 'block';
                    document.getElementById('mensajeSinTareas').style.display = 'none';
                } else {
                    alert('Error al guardar la tarea.');
                }
            }
        };
        xhr.send(`accion=crear_recordatorio&titulo=${encodeURIComponent(titulo)}&detalle=${encodeURIComponent(detalles)}&fecha=${encodeURIComponent(fecha)}&id_usuario=${encodeURIComponent(idUsuarioSession)}`);
    }

    function añadirTareaALista(titulo, detalles, fecha, id, recordar, completada) {
        let iconsHtml = `
                <i class="bi bi-pencil" id="editar-${id}" onclick="editarRecordatorio(${id})" title="Editar nota" data-bs-toggle="tooltip" data-bs-placement="top" style="cursor: pointer;"></i>
                <i class="fas fa-star ${recordar === 'SI' ? 'text-warning' : ''}" id="estrella-${id}" onclick="alternarEstrella(${id})" title="Ver en favoritos" data-bs-toggle="tooltip" data-bs-placement="top"></i>
                <i class="fas fa-trash text-danger" id="eliminar-${id}" onclick="eliminarTarea(${id})" title="Eliminar nota" data-bs-toggle="tooltip" data-bs-placement="top"></i>
            `;

        if (completada) {
            iconsHtml = `
                    <i class="fas fa-trash text-danger" id="eliminar-${id}" onclick="eliminarTarea(${id})" title="Eliminar nota" data-bs-toggle="tooltip" data-bs-placement="top"></i>
                `;
        }

        const taskHtml = `
                <li class="list-group-item" id="tarea-${id}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="checkbox-${id}" onclick="completarTarea(${id})" ${completada ? 'checked' : ''} title="Completada">
                            <label class="form-check-label ${completada ? 'text-decoration-line-through' : ''}" for="checkbox-${id}" style="cursor: pointer;">
                                ${titulo}
                            </label>
                        </div>
                        <div>
                            ${iconsHtml}
                        </div>
                    </div>
                    <div id="collapseDetalles-${id}" class="collapse ${completada ? 'show' : ''}">
                        <small class="text-muted detalle-texto">${detalles}</small><br>
                        <small class="text-muted fecha-texto">${fecha}</small>
                    </div>
                </li>
            `;

        if (completada) {
            const listaTareasCompletadas = document.getElementById('listaTareasCompletadas');
            listaTareasCompletadas.insertAdjacentHTML('beforeend', taskHtml);
        } else {
            const listaTareas = document.getElementById('listaTareas');
            listaTareas.insertAdjacentHTML('beforeend', taskHtml);
        }

        // Añadir evento para desplegar/colapsar detalles al hacer clic en el título
        document.querySelector(`#tarea-${id} .form-check-label`).addEventListener('click', function (event) {
            event.preventDefault(); // Prevenir el comportamiento predeterminado
            toggleDetalles(id);
        });

        // Activar los tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    function toggleDetalles(id) {
        const detallesElemento = document.getElementById(`collapseDetalles-${id}`);
        detallesElemento.classList.toggle('show');
    }

    function cargarTareas() {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'modelos/rescatar/recordatorios.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success && response.data.length > 0) {
                    document.getElementById('mensajeSinTareas').style.display = 'none';
                    let completadasCount = 0;
                    response.data.forEach(task => {
                        if (task.completada === 'SI') {
                            completadasCount++;
                        }
                        añadirTareaALista(task.titulo, task.detalle, task.fecha, task.id, task.recordar, task.completada === 'SI');
                    });
                    document.getElementById('completadasCount').textContent = completadasCount;
                } else {
                    document.getElementById('mensajeSinTareas').style.display = 'block';
                }
            }
        };
        xhr.send(`id_usuario=${encodeURIComponent(idUsuarioSession)}`);
    }

    cargarTareas();


}

function alternarEstrella(id) {
    const estrellaElemento = document.getElementById(`estrella-${id}`);
    const estaMarcada = estrellaElemento.classList.toggle('text-warning');  // Alternar la clase 'text-warning'

    // Mostrar la notificación de confirmación
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });
    Toast.fire({
        icon: "success",
        title: estaMarcada ? "Este recordatorio se verá en tu login de bienvenida" : "Este recordatorio no se verá en tu login de bienvenida"
    });

    // Enviar la actualización por AJAX a modelos/guardar/recordatorio.php
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'modelos/guardar/recordatorio.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status !== 200) {
            alert('Error al actualizar el recordatorio.');
            estrellaElemento.classList.toggle('text-warning');  // Revertir el toggle si hay un error
        }
    };
    xhr.send(`accion=modificar_recordatorio&id=${encodeURIComponent(id)}&recordar=${estaMarcada ? 'SI' : 'NO'}`);
}

function eliminarTarea(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta nota será eliminada",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminarla',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const tareaElemento = document.getElementById(`tarea-${id}`);

            // Enviar la solicitud de eliminación por AJAX a modelos/eliminar/recordatorio.php
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'modelos/eliminar/recordatorio.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        tareaElemento.remove();
                        verificarSinTareas();

                        // Mostrar la notificación de eliminación exitosa
                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.onmouseenter = Swal.stopTimer;
                                toast.onmouseleave = Swal.resumeTimer;
                            }
                        });
                        Toast.fire({
                            icon: "success",
                            title: "Nota eliminada correctamente"
                        });
                    } else {
                        alert('Error al eliminar la nota.');
                    }
                }
            };
            xhr.send(`id=${encodeURIComponent(id)}`);
        }
    });
}

function completarTarea(id) {
    const tareaElemento = document.getElementById(`tarea-${id}`);
    const tituloElemento = tareaElemento.querySelector('.form-check-label').textContent;
    const detallesElemento = tareaElemento.querySelector('.collapse').innerHTML;

    // Mostrar la notificación de confirmación
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });
    Toast.fire({
        icon: "success",
        title: "Tarea realizada"
    });

    // Mover la tarea a la lista de completadas
    const listaTareasCompletadas = document.getElementById('listaTareasCompletadas');
    const tareaCompletadaHtml = `
            <li class="list-group-item" id="tarea-completada-${id}">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="checkbox-${id}" checked title="Completada">
                        <label class="form-check-label text-decoration-line-through" for="checkbox-${id}" style="cursor: pointer;">
                            ${tituloElemento}
                        </label>
                    </div>
                    <div>
                        <i class="fas fa-trash text-danger" id="eliminar-${id}" onclick="eliminarTarea(${id})" title="Eliminar nota" data-bs-toggle="tooltip" data-bs-placement="top"></i>
                    </div>
                </div>
                <div id="collapseDetallesCompletada-${id}" class="collapse show">
                    ${detallesElemento}
                </div>
            </li>
        `;
    listaTareasCompletadas.insertAdjacentHTML('beforeend', tareaCompletadaHtml);

    // Actualizar el contador de tareas completadas
    const completadasCountElement = document.getElementById('completadasCount');
    let completadasCount = parseInt(completadasCountElement.textContent, 10);
    completadasCountElement.textContent = completadasCount + 1;

    // Eliminar la tarea original de la lista
    tareaElemento.remove();
    verificarSinTareas();

    // Enviar la actualización por AJAX a modelos/guardar/recordatorio.php
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'modelos/guardar/recordatorio.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status !== 200) {
            alert('Error al actualizar el recordatorio.');
        }
    };
    xhr.send(`accion=recordatorio_terminado&id=${encodeURIComponent(id)}`);

    // Verificar si todas las tareas están completadas
    verificarTodasTareasCompletadas();
}

function verificarTodasTareasCompletadas() {
    const listaTareas = document.getElementById('listaTareas');
    const mensajeSinTareas = document.getElementById('mensajeSinTareas');

    if (listaTareas.children.length === 0) {
        mensajeSinTareas.style.display = 'block';
        mostrarImagenTareasCompletadas();
    } else {
        mensajeSinTareas.style.display = 'none';
    }
}

function mostrarImagenTareasCompletadas() {
    const listaTareasCompletadas = document.getElementById('listaTareasCompletadas');
    const tareasCompletadasHtml = `
            <div class="d-flex flex-column align-items-center">
                <i class="bi bi-check-circle-fill" style="font-size: 4rem; color: green;"></i>
                <h5 class="text-muted mt-2">¡Buen trabajo!</h5>
            </div>
        `;
    listaTareasCompletadas.insertAdjacentHTML('afterend', tareasCompletadasHtml);
}

function verificarSinTareas() {
    const listaTareas = document.getElementById('listaTareas');
    if (listaTareas.children.length === 0) {
        document.getElementById('mensajeSinTareas').style.display = 'block';
    } else {
        document.getElementById('mensajeSinTareas').style.display = 'none';
    }
}


function editarRecordatorio(id) {
    const tituloElemento = document.getElementById('tituloTarea');
    const detallesElemento = document.getElementById('detallesTarea');
    const fechaElemento = document.getElementById('fechaTarea');
    const botonAgregarTarea = document.getElementById('botonAgregarTarea');
    const formularioTarea = document.getElementById('formularioTarea');

    const tareaElemento = document.getElementById(`tarea-${id}`);
    const titulo = tareaElemento.querySelector('.form-check-label').textContent.trim();
    const detalles = tareaElemento.querySelector('.detalle-texto').textContent.trim();
    const fecha = tareaElemento.querySelector('.fecha-texto').textContent.trim();

    // Cargar los datos en el formulario
    tituloElemento.value = titulo;
    detallesElemento.value = detalles;
    fechaElemento.value = fecha;

    // Mostrar el formulario y ocultar el botón de agregar tarea
    formularioTarea.style.display = 'block';
    botonAgregarTarea.style.display = 'none';
    document.getElementById('mensajeSinTareas').style.display = 'none'; // Ocultar mensaje de "Aún no hay tareas"

    // Cambiar el texto del botón de añadir tarea para indicar que se está editando
    const botonSubmit = formularioTarea.querySelector('button[type="submit"]');
    botonSubmit.textContent = 'Guardar Cambios';

    // Quitar el evento previo y añadir un nuevo evento para guardar los cambios
    formularioTarea.removeEventListener('submit', agregarTarea);
    formularioTarea.addEventListener('submit', function actualizarTarea(event) {
        event.preventDefault();
        // Actualizar tarea en el servidor y en la interfaz
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'modelos/editar/recordatorio.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Actualizar la tarea en la lista
                    tareaElemento.querySelector('.form-check-label').textContent = tituloElemento.value;
                    tareaElemento.querySelector('.detalle-texto').textContent = detallesElemento.value;
                    tareaElemento.querySelector('.fecha-texto').textContent = fechaElemento.value;
                    // Resetear el formulario y volver al modo agregar tarea
                    formularioTarea.reset();
                    formularioTarea.style.display = 'none';
                    botonAgregarTarea.style.display = 'block';
                    botonSubmit.textContent = 'Añadir Tarea';
                    formularioTarea.removeEventListener('submit', actualizarTarea);
                    formularioTarea.addEventListener('submit', agregarTarea);
                } else {
                    alert('Error al actualizar la tarea.');
                }
            }
        };
        xhr.send(`accion=modificar_recordatorio&id=${encodeURIComponent(id)}&titulo=${encodeURIComponent(tituloElemento.value)}&detalle=${encodeURIComponent(detallesElemento.value)}&fecha=${encodeURIComponent(fechaElemento.value)}`);
    });
}





// **********************************************************************
//COLABORADORES.PHP
//********************************************************************* */ 
function busca_colaborador(inputSelector, containerSelector, itemSelector) {
    $(inputSelector).on("input", function () {
        var value = $(this).val().toLowerCase();
        var allCollaborators = $(containerSelector).find(itemSelector);
        var visibleCount = 0; // Contador para colaboradores visibles

        if (value === '') {
            // Si el input está vacío, mostrar todos los colaboradores
            allCollaborators.show();
            // Eliminar cualquier mensaje de 'Sin colaboradores encontrados'
            $(containerSelector).find('.no-collaborators')
                .remove(); // Asegurarse de que se remueve el mensaje si existe
            return; // Salir del handler ya que no es necesario filtrar
        }

        allCollaborators.each(function () {
            var collaboratorText = $(this).text().toLowerCase();
            if (collaboratorText.includes(value)) {
                $(this).show();
                visibleCount++; // Incrementar el contador porque el colaborador es visible
            } else {
                $(this).hide();
            }
        });

        // Verificar si no hay colaboradores visibles después de aplicar el filtro
        if (visibleCount === 0) {
            if ($(containerSelector).find('.no-collaborators').length === 0) {
                $(containerSelector).append(
                    '<div class="col-12 no-collaborators"><p class="text-center">Sin colaboradores encontrados.</p></div>'
                );
            }
        } else {
            // Eliminar cualquier mensaje previo si hay colaboradores visibles
            $(containerSelector).find('.no-collaborators').remove();
        }
    });
}

// **********************************************************************
//ORDENANDO ASC O DESC LAS COLUMAS DE LAS TABLAS
//********************************************************************* */ 
function fil_Ticket_Usario(columnIndex) {
    var table = document.getElementById('dataTable');
    var rows = Array.prototype.slice.call(table.rows, 1);
    var sortedRows = rows.sort(function (a, b) {
        var aText = a.cells[columnIndex].innerText;
        var bText = b.cells[columnIndex].innerText;

        // Comparar fechas en formato dd-mm-yyyy
        if (columnIndex == 1) {
            var aDate = new Date(aText.split('-').reverse().join('-'));
            var bDate = new Date(bText.split('-').reverse().join('-'));
            return aDate - bDate;
        }

        // Comparar numéricos y otros valores
        return aText.localeCompare(bText, undefined, {
            numeric: true
        });
    });

    var isAscending = table.getAttribute('data-sort-order') === 'asc';
    if (isAscending) {
        sortedRows.reverse();
        table.setAttribute('data-sort-order', 'desc');
    } else {
        table.setAttribute('data-sort-order', 'asc');
    }

    // Actualizar los íconos de ordenamiento
    for (var i = 0; i <= 5; i++) {
        document.getElementById('icon-' + i).className = 'bi bi-arrow-down-short';
    }
    if (!isAscending) {
        document.getElementById('icon-' + columnIndex).className = 'bi bi-arrow-up-short';
    }

    var tbody = table.tBodies[0];
    tbody.innerHTML = '';
    sortedRows.forEach(function (row) {
        tbody.appendChild(row);
    });
}
function ordenarTablaAdministrador(columnIndex) {
    var table = document.getElementById('dataTable');
    var rows = Array.prototype.slice.call(table.rows, 1);
    var sortedRows = rows.sort(function (a, b) {
        var aText = a.cells[columnIndex].innerText;
        var bText = b.cells[columnIndex].innerText;

        // Comparar fechas en formato dd-mm-yyyy
        if (columnIndex == 1) {
            var aDate = new Date(aText.split('-').reverse().join('-'));
            var bDate = new Date(bText.split('-').reverse().join('-'));
            return aDate - bDate;
        }

        // Comparar numéricos y otros valores
        return aText.localeCompare(bText, undefined, {
            numeric: true
        });
    });

    var isAscending = table.getAttribute('data-sort-order') === 'asc';
    if (isAscending) {
        sortedRows.reverse();
        table.setAttribute('data-sort-order', 'desc');
    } else {
        table.setAttribute('data-sort-order', 'asc');
    }

    // Actualizar los íconos de ordenamiento
    for (var i = 0; i <= 8; i++) {
        document.getElementById('icon-' + i).className = 'bi bi-arrow-down-short';
    }
    if (!isAscending) {
        document.getElementById('icon-' + columnIndex).className = 'bi bi-arrow-up-short';
    }

    var tbody = table.tBodies[0];
    tbody.innerHTML = '';
    sortedRows.forEach(function (row) {
        tbody.appendChild(row);
    });
}




// ************************************************
// FUNCIONES PARA EL ARCHIVO TICKET.PHP
// ************************************************
// var estadoActual = null; // Guarda el estado seleccionado

// function filtrarTickets(estado, archivoPHP) {
//     // Si ya está seleccionado, mostrar todos
//     if (estadoActual === estado) {
//         verTodosLosTickets(archivoPHP); // Cargar todos
//         estadoActual = null;
//     } else {
//         var xhr = new XMLHttpRequest();
//         xhr.open("POST", archivoPHP, true);
//         xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
//         xhr.onreadystatechange = function () {
//             if (xhr.readyState === 4 && xhr.status === 200) {
//                 document.getElementById("ticketContainer").innerHTML = xhr.responseText;

//                 $('#dataTableAdministrador').DataTable({
//                     language: {
//                         url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
//                     },
//                     pageLength: 10,
//                     responsive: true,
//                     ordering: true
//                 });
//             }
//         };

//         xhr.send("estado=" + estado + "&idUsuarioSession=" + encodeURIComponent(idUsuarioSession));
//         estadoActual = estado;
//     }
// }


function mostrarTodosTickets(archivoPHP) {
    const xhr = new XMLHttpRequest();
    const contenedor = document.getElementById("contenedorTablaTickets");

    xhr.open("POST", archivoPHP, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            contenedor.innerHTML = xhr.responseText;

            $('#dataTableTecnicos').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                responsive: true,
                pageLength: 10,
                ordering: true
            });
        }
    };

    // Solo manda idUsuarioSession para cargar todo
    xhr.send("verTodos=1&idUsuarioSession=" + encodeURIComponent(idUsuarioSession));
}


//************************************************************************************************* */
// creador de codidos  QR para cursos
//************************************************************************************************* */
//************************************************************************************************* */







function verificarGoogleForm(url) {
    return new Promise((resolve) => {
        let win = window.open(url, "_blank", "width=600,height=400");
        setTimeout(() => {
            if (!win || win.closed || typeof win.closed === "undefined") {
                resolve(false); // El navegador bloqueó la ventana emergente
            } else {
                win.close();
                resolve(true); // La ventana se abrió correctamente, la URL es válida
            }
        }, 2000); // Esperamos 2 segundos antes de verificar
    });
}


//********************************************************************************************** */
//************************generador de cursos .php *************************************************
//**********************************************************************************************
function ButtongenerarQRCursos() {
    let idQR        = $('#idQR').val();
    let nombre      = $('#nombreTaller').val();
    let fecha       = $('#fechaTaller').val();
    let url         = $('#urlFormulario').val();
    let idUsuario   = $('#idUsuario').val();

    if (!nombre || !fecha || !url) {
        Swal.fire({
            title: 'Error',
            text: 'Todos los campos son obligatorios.',
            // icon: 'warning',
            customClass: {
                popup: 'cuerpo_modal_eliminar',        
                confirmButton: 'btn-modal-eliminar'
            }
        });
        return;
    }

    // Validar si es un Google Form
    if (!url.includes("docs.google.com/forms/d/e/")) {
        Swal.fire({
            title: 'Error',
            text: 'La URL ingresada no parece ser un formulario de Google Forms válido.',
            icon: 'error',
            customClass: {
                popup: 'cuerpo_modal_eliminar',        
                confirmButton: 'btn-modal-eliminar',
            }
        });
        return;
    }

    // Verificar si el Google Form realmente existe
    verificarGoogleForm(url).then(existe => {
        if (!existe) {
            Swal.fire({
                title: 'Error',
                text: 'El formulario no se pudo abrir. Verifique la URL.',
                icon: 'error',
                customClass: {
                    popup: 'cuerpo_modal_eliminar',        
                    confirmButton: 'btn-modal-eliminar'
                }
            });
            return;
        }

        // Si la URL es válida, continuar con la petición AJAX
        let action = idQR ? "actualizar_curso" : "crear_curso";

        $.ajax({
            url: '../modelos/guardar/cursosAsistenciaQR/cursosQR.php',
            type: 'POST',
            data: {
                action: action,
                idQR: idQR,
                nombreTaller: nombre,
                fechaTaller: fecha,
                urlFormulario: url,
                idUsuario: idUsuario
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Éxito',
                        text: `Curso ${idQR ? "actualizado" : "creado"} correctamente.`,
                        icon: 'success',
                        customClass: {
                            popup: 'cuerpo_modal_guardar',
                            confirmButton: 'bt_activar_alumno'
                        }
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message,
                        icon: 'error',
                        customClass: {
                            popup: 'cuerpo_modal_eliminar',        
                            confirmButton: 'btn-modal-eliminar'
                        }
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error',
                    text: 'Error en la petición AJAX.',
                    icon: 'error',
                    customClass: {
                        popup: 'cuerpo_modal_eliminar',        
                        confirmButton: 'btn-modal-eliminar'
                    }
                });
            }
        });
    });
}


function reimprimirQR(idQR) {
    if (!idQR) {
        Swal.fire('Error', 'No se pudo cargar el QR.', 'error');
        return;
    }

    $.ajax({
        url: 'modelos/rescatar/qrCursos.php',
        type: 'POST',
        data: { id: idQR },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: response.nombre_taller,
                    html: `
                        <p><strong>Fecha:</strong> ${response.fecha_taller}</p>
                        <p><strong>Formulario:</strong> <a href="${response.url_formulario}" target="_blank">Abrir Formulario</a></p>
                        <img src="${response.qr_path}" alt="Código QR" class="img-fluid" style="max-width: 300px;">
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Imprimir',
                    cancelButtonText: 'Cerrar',
                    customClass: {
                        popup: 'cuerpo_modal_guardar',
                        confirmButton: 'bt_activar_alumno',
                        cancelButton: 'bt_activar_alumno'
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        let ventana = window.open('', '_blank');
                        ventana.document.write(`
                            <html>
                            <head><title>Imprimir QR</title></head>
                            <body>
                                <h3>${response.nombre_taller}</h3>
                                <p>Fecha: ${response.fecha_taller}</p>
                                <img src="${response.qr_path}" style="width: 300px;">
                                <script>window.print();</script>
                            </body>
                            </html>
                        `);
                        ventana.document.close();
                    }
                });
            } else {
                Swal.fire('Error', 'No se encontró el QR.', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error en la petición AJAX.', 'error');
        }
    });
}

function modificarQR(idQR) {
    if (!idQR) {
        Swal.fire('Error', 'No se pudo cargar el QR.', 'error');
        return;
    }

    $.ajax({
        url: '../modelos/rescatar/qrCursos.php',
        type: 'POST',
        data: { id: idQR },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Llenamos los inputs del formulario de creación con los datos del QR
                $('#idQR').val(idQR);
                $('#nombreTaller').val(response.nombre_taller);
                $('#fechaTaller').val(response.fecha_taller);
                $('#urlFormulario').val(response.url_formulario);

                // Cambiamos el texto del botón para indicar que es una actualización
                $('#btnGenerarQR').text('Actualizar QR');
                $('#btnGenerarQR').attr('onclick', 'ButtongenerarQRCursos()');
                
                Swal.fire({
                    title: 'Edición activada',
                    text: 'Puedes modificar los datos del QR en el formulario.',
                    icon: 'info',
                    customClass: {
                        popup: 'cuerpo_modal_guardar',
                        confirmButton: 'bt_activar_alumno'
                    }
                });
            } else {
                Swal.fire('Error', 'No se encontró el QR.', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error en la petición AJAX.', 'error');
        }
    });

    

}


function EliminarCursoQR(idQR) {
    Swal.fire({
        title: 'Ingrese la clave para eliminar este curso',
        html: `
            <div style="position: relative;">
                <input type="password" class="form-control" id="claveInput" placeholder="Ingrese la clave" required>
                <i id="toggleClave" class="fas fa-eye" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
            </div>
        `,
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_activar_alumno'
        },
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        didOpen: () => {
            // Mostrar/ocultar clave
            const toggleClave = document.getElementById("toggleClave");
            const claveInput = document.getElementById("claveInput");
            toggleClave.addEventListener("click", () => {
                if (claveInput.type === "password") {
                    claveInput.type = "text";
                    toggleClave.classList.replace("fa-eye", "fa-eye-slash");
                } else {
                    claveInput.type = "password";
                    toggleClave.classList.replace("fa-eye-slash", "fa-eye");
                }
            });
        },
        preConfirm: () => {
            const clave = document.getElementById("claveInput").value;
            if (clave !== 'pulento') {  // Clave corregida
                Swal.fire({
                    icon: 'error',
                    title: 'Acceso denegado',
                    text: 'Usted no está autorizado para esto.',
                    customClass: { popup: 'cuerpo_modal_guardar' }
                });
                return false;
            } else {
                // Enviar por AJAX
                $.ajax({
                    url: 'modelos/eliminar/eliminarCrisoQR.php',
                    type: 'POST',
                    data: { idQR: idQR },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === "success") {
                            Swal.fire({
                                title: 'Curso eliminado',
                                text: 'El curso ha sido eliminado exitosamente.',
                                timer: 5000, // Tiempo en milisegundos (5 segundos)
                                timerProgressBar: true,
                                customClass: { popup: 'cuerpo_modal_guardar' }
                            }).then(() => {
                                location.reload();  // Recargar la página después de eliminar
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                customClass: { popup: 'cuerpo_modal_guardar' }
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo eliminar el curso. Intente de nuevo.',
                            customClass: { popup: 'cuerpo_modal_guardar' }
                        });
                    }
                });
            }
        }
    });
}

//**********************************************************************************************
//****************************************************************FIN  GENERADOR CURSOS QR *****
//**********************************************************************************************



//**********************************************************************************************
//***************************************************************PRINCIPAL*****
//**********************************************************************************************
// FUNCION PARA MOSTRAR LSO ACCESOS DIRECTOS 
function toggleMenu(id) {
    const menu = document.getElementById(`menu-acciones-${id}`);
    menu.classList.toggle('d-none');
}
function mostrarListado(tipo) {
    const contenedores = ['contenedorUsuario', 'contenedorTecnico', 'contenedorAdmin'];

    contenedores.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });

    const seleccionado = document.getElementById('contenedor' + tipo.charAt(0).toUpperCase() + tipo.slice(1));
    if (seleccionado) seleccionado.style.display = 'block';
}







  function cargarMensajesConversacion(idConversacion, idUsuarioDe, idUsuarioPara) {
      
      alert(idConversacion + idUsuarioDe + idUsuarioPara);
            idConversacionActual = idConversacion;
            idUsuarioDeActual = idUsuarioDe;
            idUsuarioParaActual = idUsuarioPara;

            $.ajax({
                url: 'modelos/resc33atar/obtener_mensajes_conversacion.php',
                method: 'POST',
                data: {
                    id_conversacion: idConversacion
                },
                success: function(response) {
                    try {
                        const result = typeof response === "string" ? JSON.parse(response.trim()) :
                            response;

                        if (result.status === "success") {
                            const mensajes = result.mensajes || [];
                            const htmlMensajes = mensajes.length > 0 ?
                                mensajes.map(m => {
                                    const clase = m.para == idUsuarioSession ? 'user' : 'other';
                                    const nombre = m.de == idUsuarioSession ? "Tú" :
                                        `${m.nombre_emisor} ${m.apellido_emisor}`;
                                    return `<div class="chat-message ${clase}"><strong>${nombre}:</strong> ${m.mensaje}</div>`;
                                }).join('') :
                                `<div class="text-muted">No hay mensajes en esta conversación aún.</div>`;

                            document.getElementById("chatContainer").innerHTML = htmlMensajes;
                        } else {
                            document.getElementById("chatContainer").innerHTML =
                                `<div class="text-danger">No se encontraron mensajes.</div>`;
                        }
                    } catch (e) {
                        console.error("Error al procesar mensajes:", e);
                        document.getElementById("chatContainer").innerHTML =
                            `<div class="text-danger">Error de formato en respuesta del servidor.</div>`;
                    }
                },
                error: function() {
                    document.getElementById("chatContainer").innerHTML =
                        `<div class="text-danger">Error al comunicarse con el servidor.</div>`;
                }
            });
        }



    function confirmarDescargaResumen(id_usuario) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "Se descargará un resumen de todos sus tickets.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, descargar',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: "cuerpo_modal_guardar",
                confirmButton: "bt_crear"
            },
        }).then((result) => {
            if (result.isConfirmed) {
                window.open('modelos/descarga/ticket_resumen.php?id_usuario=' + id_usuario, '_blank');
            }
        });
    }
    
    
    
    function mostrarOffcanvasAsunto(idTicket) {
      const myOffcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvasAsunto'));
      myOffcanvas.show();
    
      $.ajax({
        url: 'modelos/rescatar/asunto_del_ticket.php',
        type: 'POST',
        data: { id_ticket: idTicket },
        beforeSend: function () {
          $('#contenidoOffcanvasAsunto').html('<div class="text-center text-muted">Cargando...</div>');
        },
        success: function (data) {
          $('#contenidoOffcanvasAsunto').html(data);
        },
        error: function () {
          $('#contenidoOffcanvasAsunto').html('<div class="text-danger">Error al cargar la información</div>');
        }
      });
    }
    
    
    
    
    
    
    
    


        
