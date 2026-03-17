

function validacionEliminarCurso() {
    return Swal.fire({
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
            }
            return true;
        }
    }).then(result => result.isConfirmed);
}





function cambioClave(userId) {
    $.ajax({
        url: 'modelos/rescatar/usuariModificacion.php',
        type: 'POST',
        dataType: 'json',
        data: { id: userId },
        success: function(usuario) {
            const opcionesAreas = usuario.areas.map(area => {
                const selected = area.id == usuario.id_area_trabajo ? 'selected' : '';
                return `<option value="${area.id}" ${selected}>${area.nombre_area}</option>`;
            }).join('');

            Swal.fire({
                title: '<div class="alert alert-dark" role="alert">Cambio de Clave</div>',
                html: `
                    <div class="alert alert-secondary" role="alert">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="nombre" value="${usuario.nombre}" readonly>
                                    <label for="nombre">Nombre</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="apellidoPaterno" value="${usuario.apellido_paterno}" readonly>
                                    <label for="apellidoPaterno">Apellido Paterno</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="apellidoMaterno" value="${usuario.apellido_materno}" readonly>
                                    <label for="apellidoMaterno">Apellido Materno</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="email" value="${usuario.email}" readonly>
                                    <label for="email">Email</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="areaTrabajo" disabled>
                                        ${opcionesAreas}
                                    </select>
                                    <label for="areaTrabajo">Área de Trabajo</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="nuevaClave" placeholder="Ingrese nueva clave">
                                    <label for="nuevaClave">Nueva Clave</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="confirmarClave" placeholder="Confirme nueva clave">
                                    <label for="confirmarClave">Confirmar Clave</label>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                width: "900px",
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
                preConfirm: () => {
                    const nuevaClave = document.getElementById('nuevaClave').value;
                    const confirmarClave = document.getElementById('confirmarClave').value;
                    if (!nuevaClave || !confirmarClave) {
                        Swal.showValidationMessage('Complete todos los campos');
                        return false;
                    }
                    if (nuevaClave !== confirmarClave) {
                        Swal.showValidationMessage('Las claves no coinciden');
                        return false;
                    }
                    return nuevaClave;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'modelos/guardar/guardar_usuario.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'cambioClave',
                            userId: userId,
                            nuevaClave: result.value
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: '<div class="alert alert-dark" role="alert">Clave Actualizada</div>',
                                    text: 'La clave ha sido actualizada correctamente.',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false,
                                    customClass: { popup: 'cuerpo_modal_guardar' }
                                });
                            } else {
                                Swal.fire('Error', response.message || 'No se pudo actualizar la clave', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Error al actualizar la clave.', 'error');
                        }
                    });
                }
            });
        },
        error: function() {
            Swal.fire('Error', 'No se pudieron obtener los datos del usuario.', 'error');
        }
    });
}
