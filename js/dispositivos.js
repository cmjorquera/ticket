//****************************************************************** */
//*************************GUARDAR DISPOSITIVO********************************** */
//****************************************************************** */

//funcion apra guardar un nuevo dispositivo  (1)
function crearDispositivo() {
    // Mostrar alerta inicial
    // alert("Iniciando proceso de guardado...");

    // Obtener los formularios y sus campos
    const formCaracteristicas = document.getElementById('formAgregarDispositivo'); // Ajustar IDs
    const formObservaciones = document.getElementById('formObservacionesDispositivo');

    // Obtener los inputs, selects y textareas
    const tipoDispositivo = document.getElementById('tipo');
    const inputs = [
        ...formCaracteristicas.querySelectorAll('input, select, textarea'),
        ...formObservaciones.querySelectorAll('input, select, textarea')
    ];

    const equipoData = {}; // Objeto para almacenar los datos del equipo
    const camposVacios = []; // Lista de campos vacíos

    // Validar y recolectar datos
    inputs.forEach(input => {
        equipoData[input.id] = input.value.trim(); // Almacenar valor del campo
        if (!input.value.trim()) {
            camposVacios.push(input); // Añadir a la lista de campos vacíos
            input.classList.add('is-invalid'); // Marcar como inválido
            // Remover la clase inválida al escribir
            input.addEventListener('input', () => input.classList.remove('is-invalid'));
        } else {
            input.classList.remove('is-invalid'); // Remover clase inválida si está lleno
        }
    });

    // Validar el campo "Tipo de Dispositivo"
    if (!tipoDispositivo.value.trim()) {
        Swal.fire({
            title: '<div class="alert alert-dark" role="alert">ERROR</div>',
            text: 'El campo "Tipo de Dispositivo" es obligatorio y no puede estar vacío.',
            confirmButtonText: 'OK',
            customClass: {
                popup: 'cuerpo_modal_eliminar',
                confirmButton: 'bt_eliminar'
            }
        });
        return; // Salir si está vacío
    }

    // Mostrar advertencia si hay campos vacíos
    if (camposVacios.length > 0) {
        Swal.fire({
            title: '¿Desea guardar la información de este equipo?',
            text: 'Algunos campos están vacíos, ¿desea continuar?',
            showCancelButton: true,
            confirmButtonText: 'Guardar de todas formas',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'cuerpo_modal_eliminar',
                confirmButton: 'bt_crear',
                cancelButton: 'bt_cerrar',
            }

        }).then((result) => {
            if (result.isConfirmed) {
                enviarDatosDispositivo(equipoData); // Enviar datos si el usuario confirma
            }
        });
        return;
    }

    // Si no hay campos vacíos, enviar los datos directamente
    enviarDatosDispositivo(equipoData);
}




function enviarDatosDispositivo(data) {
    fetch('modelos/guardar/dispositivos.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data) // Convertir el objeto a JSON
    })
        .then(response => response.json())
        .then(result => {
            // Manejar la respuesta del servidor
            if (result.success) {
                Swal.fire({
                    title: 'Éxito',
                    text: 'El dispositivo se guardó correctamente.',
                    icon: 'success',
                    timer: 2000, // Tiempo en milisegundos
                    timerProgressBar: true,
                    showConfirmButton: false, // No mostrar el botón de confirmación
                    customClass: {
                        popup: 'cuerpo_modal_guardar',
                    },
                    didOpen: () => {
                        Swal.showLoading(); // Mostrar el icono de carga mientras dura el tiempo
                    }
                }).then(() => {
                    // Redirigir a bitacora.php con el parámetro para activar la pestaña señalada
                    window.location.href = 'bitacora.php?tab=pestaña2';
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: result.message || 'Ocurrió un error al guardar el dispositivo.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            // Manejar errores de la solicitud
            console.error('Error al enviar datos:', error);
            Swal.fire({
                title: 'Error',
                text: 'No se pudo conectar con el servidor.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
}



function verQrDispositivo(idDispositivos) {
    if (!idDispositivos) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'ID de equipo no válido.',
        });
        return;
    }

    fetch(`modelos/rescatar/obtener_dispositivo.php?id=${idDispositivos}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
        .then((response) => response.json())
        .then((result) => {
            if (result.success) {
                const equipo = result.equipo;
                let qrCodePath = equipo.qr_code;

                // Eliminar "../../" de la ruta si existe
                if (qrCodePath.startsWith('../../')) {
                    qrCodePath = qrCodePath.replace('../../', '');
                }

                if (!qrCodePath) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sin código QR',
                        text: 'Este equipo no tiene un código QR asignado.',
                    });
                    return;
                }

                const idCompleto = idDispositivos.toString().padStart(5, '0');

                Swal.fire({
                    title: `<div class="alert alert-dark" role="alert">Dispositico_${idCompleto}</div>`,
                    html: `
                        <div>
                            <img src="${qrCodePath}" alt="Código QR" style="max-width: 100%; height: auto; margin-bottom: 20px;">
                        </div>
                    `,
                     confirmButtonText: 'Imprimir',
                    cancelButtonText: 'Cerrar',
                    customClass: {
                        popup: 'cuerpo_modal_guardar',
                        confirmButton: 'bt_crear',
                        cancelButton: 'bt_cerrar',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Acción del botón "Imprimir"
                        window.open(
                            `modelos/imprimir/dispositivo.php?id_dispositivo=${idDispositivos}`,
                            '_blank'
                        );
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.error || 'No se encontró el equipo.',
                });
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al procesar la solicitud.',
            });
        });
}












function imprimirTodosQR() {
    // Mostrar un modal de confirmación con SweetAlert
    Swal.fire({
        title: '¿Desea imprimir todos los códigos QR?',
        text: "Esta acción imprimirá todos los códigos QR disponibles.",
        // icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, imprimir',
        cancelButtonText: 'No, cancelar',
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_activar_alumno'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Si el usuario confirma, redirigir a la página de impresión
            window.open('modelos/imprimir/imprimir_codigos_qr.php', '_blank');
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Si el usuario cancela, mostrar un mensaje de cancelación
            Swal.fire('Cancelado', 'La impresión de códigos QR ha sido cancelada.', 'error');
        }
    });
}









function limpiarFormularioDispositivo() {
    // IDs de los formularios a limpiar
    const formularios = ['formObservacionesDispositivo', 'formAgregarDispositivo'];

    formularios.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            // Selecciona todos los inputs dentro del formulario y los limpia
            const inputs = form.querySelectorAll('input');
            inputs.forEach(input => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false; // Deselecciona checkboxes y radios
                } else {
                    input.value = ''; // Limpia el valor de otros tipos de input
                }
            });

            // Limpia los select
            const selects = form.querySelectorAll('select');
            selects.forEach(select => {
                select.selectedIndex = 0; // Restaura el select al valor predeterminado
            });

            // Limpia los textareas
            const textareas = form.querySelectorAll('textarea');
            textareas.forEach(textarea => {
                textarea.value = ''; // Limpia el contenido del textarea
            });
        }
    });

    console.log("Formularios limpiados");
}



function agregarEquipo() {
    // Crear el HTML para el select y los inputs en el modal
    let modalContent = `
            <select id="tipoDispositivoSelect" class="swal2-input">
                <option value="">Seleccione un tipo de dispositivo</option>
                <?php foreach ($tiposDispositivos as $dispositivo): ?>
                    <option value="<?php echo $dispositivo['id_tipo_dispositivo']; ?>">
                        <?php echo htmlspecialchars($dispositivo['nombre_dispositivo']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="text" id="caracteristica1" class="swal2-input" placeholder="Característica 1">
            <input type="text" id="caracteristica2" class="swal2-input" placeholder="Característica 2">
        `;

    // Mostrar el modal de SweetAlert
    Swal.fire({
        title: 'Agregar Nuevo Equipo',
        html: modalContent,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const tipoDispositivo = document.getElementById('tipoDispositivoSelect').value;
            const caracteristica1 = document.getElementById('caracteristica1').value.trim();
            const caracteristica2 = document.getElementById('caracteristica2').value.trim();

            // Validar que los campos no estén vacíos
            if (!tipoDispositivo) {
                Swal.showValidationMessage('Debe seleccionar un tipo de dispositivo');
            }
            if (!caracteristica1) {
                Swal.showValidationMessage('La Característica 1 es obligatoria');
            }
            if (!caracteristica2) {
                Swal.showValidationMessage('La Característica 2 es obligatoria');
            }

            return {
                tipoDispositivo,
                caracteristica1,
                caracteristica2
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Obtener los valores ingresados
            const tipoDispositivo = result.value.tipoDispositivo;
            const caracteristica1 = result.value.caracteristica1;
            const caracteristica2 = result.value.caracteristica2;

            // Enviar los datos por AJAX a nuevo_equipo.php
            $.ajax({
                url: 'modelos/guardar/nuevo_equipo.php',
                method: 'POST',
                data: {
                    tipoDispositivo: tipoDispositivo,
                    caracteristica1: caracteristica1,
                    caracteristica2: caracteristica2
                },
                success: function (response) {
                    Swal.fire({
                        title: 'Equipo Agregado',
                        text: 'El equipo ha sido agregado exitosamente.',
                        icon: 'success'
                    });
                },
                error: function () {
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un problema al agregar el equipo.',
                        icon: 'error'
                    });
                }
            });
        }
    });
}


//****************************************************************** */
//*************************EDITAR DISPOSITIVO*********************************** */
//****************************************************************** */

function editarDispositivo(idDispositivo) {
    
    const acordeonRow = document.getElementById(`acordeonRow_${idDispositivo}`);
    const filaDispositivo = document.getElementById(`dispositivo_${idDispositivo}`);

    // Verificar si el acordeón ya está visible
    if (acordeonRow.style.display === 'table-row') {
        acordeonRow.style.display = 'none';
        filaDispositivo.classList.remove('efecto-3d');
        return;
    }

    // Ocultar otros acordeones abiertos
    document.querySelectorAll("tr[id^='acordeonRow_']").forEach(row => row.style.display = 'none');
    document.querySelectorAll("tr[id^='dispositivo_']").forEach(row => row.classList.remove('efecto-3d'));

    // Mostrar el acordeón actual
    acordeonRow.style.display = 'table-row';
    filaDispositivo.classList.add('efecto-3d');

    const acordeonContainer = document.getElementById(`acordeonEdicionDispositivo_${idDispositivo}`);
    acordeonContainer.innerHTML = '<p>Cargando...</p>';

    // Solicitud AJAX para obtener los datos del dispositivo
    fetch('modelos/rescatar/otro_dispositivo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_dispositivo: idDispositivo })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const dispositivo = data.data;

                // Generar el contenido del acordeón con los datos del dispositivo
                acordeonContainer.innerHTML = `
                    <ul class="nav nav-tabs" id="editTab_${idDispositivo}" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" href="#caracteristicas_${idDispositivo}" role="tab" aria-selected="true">Características del Equipo</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#observaciones_${idDispositivo}" role="tab" aria-selected="false">Observaciones</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-3">
                        <div class="tab-pane fade show active" id="caracteristicas_${idDispositivo}" role="tabpanel">
                    <form id="formEditarDispositivo_${idDispositivo}">
                        <div class="container">
                            <div class="row">
                                <!-- Primera fila con 3 inputs -->
                                <div class="col-md-4 mb-3">
                                    <label for="tipo_${idDispositivo}" class="form-label">Tipo de Dispositivo</label>
                                    <select class="form-control" id="tipo_${idDispositivo}" name="tipo">
                                        <option value="">Cargando tipos de dispositivo...</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="marca_${idDispositivo}" class="form-label">Marca</label>
                                    <input type="text" class="form-control" id="marca_${idDispositivo}" name="marca" value="${dispositivo.marca || ''}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="modelo_${idDispositivo}" class="form-label">Modelo</label>
                                    <input type="text" class="form-control" id="modelo_${idDispositivo}" name="modelo" value="${dispositivo.modelo || ''}">
                                </div>
                            </div>
                            <div class="row">
                                <!-- Segunda fila con 2 inputs y un espacio vacío -->
                                <div class="col-md-4 mb-3">
                                    <label for="n_serie_${idDispositivo}" class="form-label">Número de Serie</label>
                                    <input type="text" class="form-control" id="n_serie_${idDispositivo}" name="n_serie" value="${dispositivo.n_serie || ''}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="asignado_${idDispositivo}" class="form-label">Asignado a</label>
                                    <select class="form-control" id="asignado_${idDispositivo}" name="asignado" data-id="${dispositivo.asignado}">
                                        <option value="">Cargando usuarios...</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <!-- Espacio vacío para mantener el diseño consistente -->
                                </div>
                            </div>
                        </div>
                    </form>

                        </div>
                        <div class="tab-pane fade" id="observaciones_${idDispositivo}" role="tabpanel">
                          <form id="formObservaciones_${idDispositivo}">
                            <div class="row align-items-center">
                                <div class="col-md-3 mb-3">
                                    <label for="proveedor_${idDispositivo}" class="form-label">Proveedor</label>
                                    <input type="text" class="form-control" id="proveedor_${idDispositivo}" name="proveedor" value="${dispositivo.proveedor || ''}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="fecha_compra_${idDispositivo}" class="form-label">Fecha de Compra</label>
                                    <input type="date" class="form-control" id="fecha_compra_${idDispositivo}" name="fecha_compra" value="${dispositivo.fecha_compra || ''}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="numero_factura_${idDispositivo}" class="form-label">Número Factura</label>
                                    <input type="text" class="form-control" id="numero_factura_${idDispositivo}" name="numero_factura" value="${dispositivo.n_factura || ''}" placeholder="Número de Factura" oninput="permitirSoloNumeros(event)">
                                </div>
                         <div class="col-md-3 mb-3">
                                    <label for="precio_${idDispositivo}" class="form-label">Precio</label>
                                    <input type="text" class="form-control" id="precio_${idDispositivo}" name="precio" 
                                        value="${dispositivo.precio ? dispositivo.precio.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') : ''}" 
                                        placeholder="Precio en $" oninput="formatearPesos(event)">
                                </div>
                                                        
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="observaciones_${idDispositivo}" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observaciones_${idDispositivo}" name="observaciones" rows="3">${dispositivo.observaciones || ''}</textarea>
                                </div>
                            </div>
                        </form>


                        </div>
                    </div>
                    <div id="botonesAccion">
                        <button id="guardarOtroEquipo_${idDispositivo}" class="btn btn-primary mt-3" onclick="activarEdicionEquiposnn(${idDispositivo})">
                            <i class="fas fa-edit"></i> Guardar Dispositivo
                        </button>
                    </div>
                `;

                // Cargar usuarios y tipos de dispositivos
                cargarUsuarios(idDispositivo);
                cargarTiposDispositivos(idDispositivo, dispositivo.tipo);
            } else {
                acordeonContainer.innerHTML = '<p>Error al cargar los datos del dispositivo.</p>';
            }
        })
        .catch(error => {
            console.error('Error al cargar los datos del dispositivo:', error);
            acordeonContainer.innerHTML = '<p>Error al cargar los datos del dispositivo.</p>';
        });
}




// Función para cargar los usuarios
function cargarUsuarios(idDispositivo) {
    return fetch('modelos/rescatar/usuarios.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    })
        .then(res => res.json())
        .then(usuariosData => {
            const selectAsignado = document.getElementById(`asignado_${idDispositivo}`);
            const dataId = parseInt(selectAsignado.dataset.id, 10);

            if (usuariosData.success) {
                selectAsignado.innerHTML = '<option value="">Seleccionar usuario</option>';
                usuariosData.usuarios.forEach(usuario => {
                    const selected = parseInt(usuario.id, 10) === dataId ? 'selected' : '';
                    selectAsignado.innerHTML += `<option value="${usuario.id}" ${selected}>${usuario.nombre} ${usuario.apellido_paterno}</option>`;
                });
            } else {
                console.error('Error al cargar usuarios:', usuariosData.error);
                selectAsignado.innerHTML = '<option value="">No hay usuarios disponibles</option>';
            }
        })
        .catch(error => {
            console.error('Error al cargar usuarios:', error);
        });
}

// Función para cargar los tipos de dispositivos
function cargarTiposDispositivos(idDispositivo, dispositivoTipo) {
    return fetch('modelos/rescatar/obtener_tipo_equipo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_dispositivo: idDispositivo })
    })
        .then(res => {
            if (!res.ok) {
                throw new Error(`Error HTTP: ${res.status}`);
            }
            return res.json();
        })
        .then(tiposData => {
            const selectTipo = document.getElementById(`tipo_${idDispositivo}`);
            if (tiposData.success) {
                selectTipo.innerHTML = '<option value="">Seleccionar tipo de dispositivo</option>';
                tiposData.tipos.forEach(tipo => {
                    const selected = tipo.id_tipo_dispositivo == dispositivoTipo ? 'selected' : '';
                    selectTipo.innerHTML += `<option value="${tipo.id_tipo_dispositivo}" ${selected}>${tipo.nombre_dispositivo}</option>`;
                });
            } else {
                console.error('Error al cargar tipos de dispositivos:', tiposData.error);
                selectTipo.innerHTML = '<option value="">No se encontraron tipos de dispositivos</option>';
            }
        })
        .catch(error => {
            console.error('Error al cargar tipos de dispositivos:', error);
        });
}
























// esta es la funcion para editar el dispositivo, el button esta  en el js (2)
function activarEdicionEquiposnn(idDispositivo) {
    // alert(idDispositivo);
    // Formularios de características y observaciones
    const formCaracteristicas = document.getElementById(`formEditarDispositivo_${idDispositivo}`);
    const formObservaciones = document.getElementById(`formObservaciones_${idDispositivo}`);

    // Obtener todos los inputs
    const tipoDispositivo = document.getElementById(`tipo_${idDispositivo}`);
    const inputs = [
        ...formCaracteristicas.querySelectorAll('input, select, textarea'),
        ...formObservaciones.querySelectorAll('input, select, textarea')
    ];

    const equipoData = { id_dispositivo: idDispositivo };
    const camposVacios = [];

    // Validar y recolectar datos
    inputs.forEach(input => {
        equipoData[input.id] = input.value.trim();
        if (!input.value.trim()) {
            camposVacios.push(input);
            input.classList.add('is-invalid');
            input.addEventListener('input', () => input.classList.remove('is-invalid'));
        } else {
            input.classList.remove('is-invalid');
        }
    });

    // Verificar si "Tipo de Dispositivo" está vacío
    if (!tipoDispositivo.value.trim()) {
        Swal.fire({
            title: '<div class="alert alert-dark" role="alert">ERROR</div>',
            text: 'El campo "Tipo de Dispositivo" es obligatorio y no puede estar vacío.',
            confirmButtonText: 'OK',
            customClass: {
                popup: 'cuerpo_modal_eliminar',
                confirmButton: 'bt_eliminar'
            }
        });
        return; // Salir de la función si "Tipo de Dispositivo" está vacío
    }

    // Si hay otros campos vacíos, mostrar el modal con opciones de "Guardar" y "Cancelar"
    if (camposVacios.length > 0) {
        Swal.fire({
            title: '¿Desea Guardar la información de este equipo?',
            text: 'Algunos campos están vacíos, ¿desea continuar?',
            showCancelButton: true,
            confirmButtonText: 'Guardar de todas formas',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'cuerpo_modal_guardar',
                confirmButton: 'bt_crear',
                cancelButton: 'bt_cerrar',
            },
        }).then((result) => {
            if (result.isConfirmed) {
                enviarDatosEquiponn(equipoData); // Llamar a la función para enviar los datos
            }
        });
        return;
    }

    // Si no hay campos vacíos, enviar los datos directamente
    enviarDatosEquiponn(equipoData);
}


// esta es la funcion envia la informacion a dispositico.php apra actualizar
// Esta función envía los datos del dispositivo al servidor
    function enviarDatosEquiponn(equipoData) {
        // alert("lupe"); // Mensaje para confirmar que se ejecuta la función
    
        // Realiza una solicitud fetch para enviar los datos al servidor
        fetch('modelos/editar/dispositivo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(equipoData) // Convierte el objeto equipoData a JSON
        })
            .then(response => {
                // Verifica si la respuesta del servidor es exitosa
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json(); // Convierte la respuesta a JSON
            })
            .then(data => {
                if (data.success) {
                    // Muestra un mensaje de éxito con SweetAlert2
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'El equipo se ha actualizado correctamente.',
                        timer: 2000,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'cuerpo_modal_guardar',
                            confirmButton: 'bt_activar_alumno'
                        }
                    }).then(() => {
                        // Redirige a la página 'bitacora.php' después de mostrar el mensaje de éxito
                    window.location.href = 'bitacora.php?tab=pestaña2';
                    });
                } else {
                    // Maneja los errores específicos enviados por el servidor
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Hubo un error al actualizar los datos del equipo.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                // Maneja los errores relacionados con la conexión o el fetch
                console.error('Error al actualizar los datos del equipo:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un error al conectar con el servidor para actualizar.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
    }
    

// Función para cancelar la edición de un equipo
function cancelarEdicionEquiponn(idEquipo) {
    // Alert para verificar que la función es llamada
    // alert("funcion cancelarEdicionEquipo " + idEquipo);

    // Obtener el acordeón específico del equipo y eliminarlo del DOM
    const acordeon = document.getElementById(`acordeonEquipo_${idEquipo}`);
    if (acordeon) acordeon.remove();
}

//*********************************************************** */
//*******************ELIMINAR DISPOSITIVO*********************************
//*********************************************************** */

function eliminarDispositivo(idDispositivo) {
    
    // alert("asdas");
    if (!idDispositivo || isNaN(idDispositivo)) {
        console.error('ID de dispositivo inválido:', idDispositivo);
        return;
    }

    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer',
        // icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'cuerpo_modal_eliminar',
            confirmButton: 'bt_crear',
            cancelButton: 'bt_cerrar',
        }

    }).then((result) => {
        if (result.isConfirmed) {
            // Enviar solicitud AJAX para eliminar el dispositivo
            fetch(`modelos/eliminar/eliminar_dispositivo.php?id=${idDispositivo}`, {
                method: 'DELETE'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Dispositivo eliminado',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false,
                            customClass: {
                                popup: 'cuerpo_modal_guardar',
                                confirmButton: 'bt_activar_alumno'
                            }

                        }).then(() => location.reload());

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.error || 'No se pudo eliminar el dispositivo.'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error al eliminar el dispositivo:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al procesar la solicitud.'
                    });
                });
        }
    });
}

//*********************************************************** */
//**************ACTIVAR BUTTON DESCARGAR ******************** */
//*********************************************************** */



// Evento onclick para descargar dispositivos
function descargaDispositivos() {
    const dispositivosSeleccionados = [];

    // Recopilar los ID de los dispositivos seleccionados
    $('.seleccionDispositivo:checked').each(function () {
        dispositivosSeleccionados.push($(this).val());
    });

    if (dispositivosSeleccionados.length === 0) {
        Swal.fire('Advertencia', 'No hay dispositivos seleccionados para descargar.', 'warning');
        return;
    }

    // Realizar la solicitud AJAX para la descarga
    $.ajax({
        url: 'modelos/descarga/descarga_otro_dispositivo.php',
        type: 'POST',
        data: { dispositivos: dispositivosSeleccionados },
        success: function (response) {
            console.log(response); // Verifica qué devuelve el servidor
            window.location.href = response;
        },

        error: function () {
            Swal.fire('Error', 'Hubo un problema al intentar descargar los dispositivos.', 'error');
        }
    });
}


function filtrarDispositivos() {
    const input = document.getElementById('buscadorDispositivos');
    const filtro = input.value.toLowerCase();
    const filas = document.querySelectorAll('#otrosDispositivosTableBody tr');

    // Cerrar todos los acordeones relacionados con los dispositivos
    document.querySelectorAll("tr[id^='acordeonRow_']").forEach(row => {
        row.style.display = 'none'; // Ocultar los acordeones
    });

    // Quitar cualquier efecto visual de las filas principales
    document.querySelectorAll("tr[id^='dispositivo_']").forEach(row => {
        row.classList.remove('efecto-3d');
    });

    // Filtrar las filas según el texto ingresado
    filas.forEach(fila => {
        const textoFila = fila.innerText.toLowerCase();
        if (textoFila.includes(filtro)) {
            fila.style.display = ''; // Mostrar la fila si coincide con el filtro
        } else {
            fila.style.display = 'none'; // Ocultar la fila si no coincide
        }
    });

    // Asegurarse de que todos los acordeones permanezcan cerrados
    document.querySelectorAll("tr[id^='acordeonRow_']").forEach(acordeon => {
        acordeon.style.display = 'none';
    });
}


//************************************************