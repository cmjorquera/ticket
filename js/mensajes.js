    
    function enviar_mensaje_mensaje(idOrigen) {
        
        // alert(idOrigen);
        // Obtener los datos del usuario origen
        $.ajax({
            type: "POST",
            url: "modelos/rescatar/usuario.php",
            dataType: "json",
            data: { id: idOrigen },
            success: function (dataOrigen) {
                if (dataOrigen) {
                    var nombreOrigen = `${dataOrigen.nombre || "No disponible"} ${dataOrigen.apellido_paterno || ""}`;
    
                    // Obtener la lista de usuarios para el select
                    $.ajax({
                        type: "POST",
                        url: "modelos/rescatar/musuarios.php",
                        dataType: "json",
                        success: function (dataDestino) {
                            if (dataDestino.length > 0) {
                                // Generar opciones del select
                                var opcionesUsuarios = dataDestino.map(usuario => 
                                    `<option value="${usuario.id}">${usuario.nombre} ${usuario.apellido_paterno}</option>`
                                ).join('');
    
                                // Mostrar el modal con el mismo formato original
                                Swal.fire({
                                    title: '<div class="alert alert-dark" role="alert">ENVIAR MENSAJE</div>',
                                    html: `
                                        <div class="alert alert-dark d-flex justify-content-between align-items-center position-relative" role="alert">
                                            <span id="title-text" class="fw-bold">MENSAJE</span>
    
                                            <!-- Switch con ícono en la esquina superior derecha -->
                                            <div class="form-switch position-absolute" style="top: 5px; right: 5px;">
                                                <input class="form-check-input" type="checkbox" onclick="efecotUrgente()" id="swal-input-urgent" 
                                                       style="width: 1.2rem; height: 1rem;">
                                                <label for="swal-input-urgent" class="form-check-label" style="cursor: pointer;">
                                                    <i id="icon-urgent" class="far fa-bell text-secondary ms-1" style="font-size: 1rem;"></i>
                                                </label>
                                            </div>
                                        </div>
    
                                        <!-- Contenido del formulario -->
                                        <div class="alert alert-secondary mt-3" role="alert">
                                            <form class="row g-3" id="ticketForm">
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <!-- Usuario Origen -->
                                                        <input type="text" class="form-control" id="swal-input-rem" 
                                                               placeholder="De" value="${nombreOrigen}" readonly>
                                                        <label for="swal-input-rem">De</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <!-- Select para elegir el destinatario -->
                                                        <select class="form-control" id="swal-input-dest">
                                                            <option value="">Selecciona un usuario</option>
                                                            ${opcionesUsuarios}
                                                        </select>
                                                        <label for="swal-input-dest">Para</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <textarea class="form-control" id="swal-input-msg" 
                                                                  placeholder="Escribe el mensaje aquí..." style="height: 200px;"></textarea>
                                                        <label for="swal-input-msg">Mensaje</label>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    `,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    allowEnterKey: false,
                                    showCloseButton: true,
                                    width: "870px",
                                    padding: "40px",
                                    confirmButtonText: 'ENVIAR',
                                    customClass: {
                                        popup: 'cuerpo_modal_guardar',
                                        confirmButton: 'bt_crear',
                                    },
                                    preConfirm: () => {
                                        const mensaje = document.getElementById('swal-input-msg').value;
                                        const idDestino = document.getElementById('swal-input-dest').value;
                                        const urgente = document.getElementById('swal-input-urgent').checked;
    
                                        if (!idDestino || !mensaje.trim()) {
                                            Swal.showValidationMessage("Por favor, completa todos los campos necesarios.");
                                            return false;
                                        }
    
                                        // Enviar los datos al servidor
                                        return $.ajax({
                                            type: "POST",
                                            url: "modelos/guardar/guardar_mensaje.php",
                                            data: {
                                                idUsuario: idOrigen,
                                                destinatarioId: idDestino,
                                                mensaje: mensaje,
                                                urgente: urgente,
                                                accion: 'crear_mensaje'
                                            }
                                        }).then(response => {
                                            if (!response || response.status !== "success") {
                                                Swal.showValidationMessage('No se pudo enviar el mensaje.');
                                            }
                                            return response;
                                        });
                                    }
                                }).then(result => {
                                    if (result.value) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: '<div class="alert alert-dark" role="alert">MENSAJE ENVIADO</div>',
                                            showConfirmButton: false,
                                            timer: 2000
                                        }).then(() => {
                                    window.location.href = "mensaje_enviados.php";
                                        });
                                    }
                                });
                            } else {
                                Swal.fire('Error', 'No se encontraron usuarios disponibles.', 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'Hubo un problema al cargar los usuarios.', 'error');
                        }
                    });
                } else {
                    Swal.fire('Error', 'No se encontraron datos del usuario origen.', 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Hubo un problema al rescatar los datos del usuario origen.', 'error');
            }
        });
    }
    
    function efecotUrgente() {
        const checkBox = document.getElementById("swal-input-urgent");
        const titleText = document.getElementById("title-text");
        const bellIcon = document.getElementById("icon-urgent");
    
        if (checkBox.checked) {
            titleText.textContent = "URGENTE"; // Agrega URGENTE al título
            titleText.classList.add("text-danger"); // Cambia el color del texto a rojo
            bellIcon.classList.replace("text-secondary", "text-danger"); // Cambia el color del ícono
        } else {
            titleText.textContent = "MENSAJE"; // Restaura el título original
            titleText.classList.remove("text-danger"); // Elimina el color rojo
            bellIcon.classList.replace("text-danger", "text-secondary"); // Restaura el ícono a gris
        }
    }
    
    function crearMensajeColaboradores(idOrigen, idDestino) {
        // Realizar dos llamadas AJAX simultáneamente
        $.when(
            // Llamada para obtener el usuario origen
            $.ajax({
                type: "POST",
                url: "modelos/rescatar/usuario.php",
                dataType: "json",
                data: { id: idOrigen }
            }),
            // Llamada para obtener el usuario destino
            $.ajax({
                type: "POST",
                url: "modelos/rescatar/usuario.php",
                dataType: "json",
                data: { id: idDestino }
            })
        ).done(function (dataOrigen, dataDestino) {
            // Datos del usuario origen
            var nombreOrigen = `${dataOrigen[0].nombre || "No disponible"} ${dataOrigen[0].apellido_paterno || ""}`;
    
            // Datos del usuario destino
            var nombreDestino = `${dataDestino[0].nombre || "No disponible"} ${dataDestino[0].apellido_paterno || ""}`;
    
            // Mostrar el modal con el formato original
            Swal.fire({
                title: '<div class="alert alert-dark" role="alert">ENVIAR MENSAJE</div>',
                html: `
                    <div class="alert alert-dark d-flex justify-content-between align-items-center position-relative" role="alert">
                        <span id="title-text" class="fw-bold">MENSAJE</span>
    
                        <!-- Switch con ícono en la esquina superior derecha -->
                        <div class="form-switch position-absolute" style="top: 5px; right: 5px;">
                            <input class="form-check-input" type="checkbox"  onclick="efecotUrgente()"id="swal-input-urgent" 
                                   style="width: 1.2rem; height: 1rem;">
                            <label for="swal-input-urgent" class="form-check-label" style="cursor: pointer;">
                                <i id="icon-urgent" class="far fa-bell text-secondary ms-1" style="font-size: 1rem;"></i>
                            </label>
                        </div>
                    </div>
    
                    <!-- Contenido del formulario -->
                    <div class="alert alert-secondary mt-3" role="alert">
                        <form class="row g-3" id="ticketForm">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <!-- Usuario Origen -->
                                    <input type="text" class="form-control" id="swal-input-rem" placeholder="De" 
                                           value="${nombreOrigen}" readonly>
                                    <label for="swal-input-rem">De</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <!-- Usuario Destino -->
                                    <input type="text" class="form-control" id="swal-input-dest" placeholder="Para" 
                                           value="${nombreDestino}" readonly>
                                    <label for="swal-input-dest">Para</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" id="swal-input-msg" placeholder="Escribe el mensaje aquí..." 
                                              style="height: 200px;"></textarea>
                                    <label for="swal-input-msg">Mensaje</label>
                                </div>
                            </div>
                        </form>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showCloseButton: true,
                width: "870px",
                padding: "40px",
                confirmButtonText: 'ENVIAR',
                customClass: {
                    popup: 'cuerpo_modal_guardar',
                    confirmButton: 'bt_crear',
                },
                preConfirm: () => {
                    const mensaje = document.getElementById('swal-input-msg').value;
                    const urgente = document.getElementById('swal-input-urgent').checked;
    
                    if (!mensaje.trim()) {
                        Swal.showValidationMessage("Por favor, completa todos los campos necesarios.");
                        return false;
                    }
    
                    return $.ajax({
                        type: "POST",
                        url: "modelos/guardar/guardar_mensaje.php",
                        data: {
                            idUsuario: idDestino,
                            idUsuarioSession: idOrigen,
                            mensaje: mensaje,
                            urgente: urgente,
                            accion: 'crear_mensaje_colaboradores'
                        }
                    }).then(response => {
                        if (response.status !== "success") {
                            Swal.showValidationMessage(response.message || "Error al enviar el mensaje.");
                        }
                        return response;
                    });
                }
            }).then((result) => {
                if (result.value) {
                    Swal.fire({
                        icon: 'success',
                        title: '<div class="alert alert-dark" role="alert">MENSAJE ENVIADO</div>',
                        showConfirmButton: false,
                        width: "470px",
                        timer: 2000,
                        customClass: {
                            popup: 'cuerpo_modal_guardar'
                        }
                    }).then(() => {
                    window.location.href = "mensaje_enviados.php";
                    });
                }
            });
        }).fail(function () {
            Swal.fire('Error', 'Hubo un problema al rescatar los datos de los usuarios.', 'error');
        });
    }
    
    function mostrarModalConTextareaMultiple(ids) {
        $.ajax({
            type: "POST",
            url: "modelos/rescatar/colaboradoresMultiplesMensajes.php", // Nueva URL
            dataType: "json",
            data: { ids: ids }, // Enviar el array de IDs seleccionados
            success: function (response) {
                if (response.success && response.usuarios.length > 0) {
                    // Obtener el nombre del usuario origen (usuario logueado)
                    const nombreOrigen = nombresession.replace('--', ' ');
    
                    // Construir el HTML para cada destinatario
                    const destinatariosHtml = response.usuarios.map(user => `
                        <div class="form-floating mb-2">
                            <input type="text" class="form-control" placeholder="Para" 
                                   value="${user.nombre} ${user.apellido_paterno}" readonly>
                            <label>Para</label>
                        </div>
                    `).join('');
    
                    // Mostrar el modal
                    Swal.fire({
                        title: '<div class="alert alert-dark" role="alert">ENVIAR MENSAJE</div>',
                        html: `
                            <div class="alert alert-dark d-flex justify-content-between align-items-center position-relative" role="alert">
                                <span id="title-text" class="fw-bold">MENSAJE</span>
                                <!-- Switch con ícono en la esquina superior derecha -->
                                <div class="form-switch position-absolute" style="top: 5px; right: 5px;">
                                    <input class="form-check-input" type="checkbox" onclick="efecotUrgente()" id="swal-input-urgent" 
                                           style="width: 1.2rem; height: 1rem;">
                                    <label for="swal-input-urgent" class="form-check-label" style="cursor: pointer;">
                                        <i id="icon-urgent" class="far fa-bell text-secondary ms-1" style="font-size: 1rem;"></i>
                                    </label>
                                </div>
                            </div>
    
                            <!-- Contenido del formulario -->
                            <div class="alert alert-secondary mt-3" role="alert">
                                <form class="row g-3" id="ticketForm">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <!-- Usuario Origen -->
                                            <input type="text" class="form-control" id="swal-input-rem" placeholder="De" 
                                                   value="${nombreOrigen}" readonly>
                                            <label for="swal-input-rem">De</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <!-- Usuarios Destino -->
                                        ${destinatariosHtml}
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="swal-input-msg" placeholder="Escribe el mensaje aquí..." 
                                                      style="height: 200px;"></textarea>
                                            <label for="swal-input-msg">Mensaje</label>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        `,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showCloseButton: true,
                        width: "870px",
                        padding: "40px",
                        confirmButtonText: 'ENVIAR',
                        customClass: {
                            popup: 'cuerpo_modal_guardar',
                            confirmButton: 'bt_crear',
                        },
                        preConfirm: () => {
                            const mensaje = document.getElementById('swal-input-msg').value;
                            const urgente = document.getElementById('swal-input-urgent').checked;
    
                            if (!mensaje.trim()) {
                                Swal.showValidationMessage("Por favor, escribe un mensaje.");
                                return false;
                            }
    
                            // Enviar los datos seleccionados al servidor
                            return $.ajax({
                                type: "POST",
                                url: "modelos/guardar/guardar_mensaje.php",
                                data: {
                                    ids: ids,
                                    idUsuarioSession: idUsuarioSession,
                                    mensaje: mensaje,
                                    urgente: urgente,
                                    accion: 'crear_mensaje_multiple'
                                }
                            });
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                icon: 'success',
                                title: '<div class="alert alert-dark" role="alert">MENSAJE ENVIADO</div>',
                                showConfirmButton: false,
                                width: "470px",
                                timer: 2000,
                                customClass: {
                                    popup: 'cuerpo_modal_guardar',
                                }
                            }).then(() => {
                                window.location.href = "mensaje_enviados.php";
                            });
                        }
                    });
                } else {
                    Swal.fire('Error', 'No se encontraron usuarios seleccionados.', 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Hubo un problema al recuperar los usuarios.', 'error');
            }
        });
    }
    
    
    function ccc(idConversacion, idUsuarioDe, idUsuarioPara){
        alert("guadalupe jorquera");
    }
    //********************************************************************************//
    //********************MENSAJES CONVERSACION ENTRE USUARIOS  *****************//
    // este el modal quer se abra y muestra la conversacion que se ha teneido 
    function cargarConversacionMensajes(idConversacion, idUsuarioDe, idUsuarioPara) {
        // alert("*********");

        idConversacionActual = idConversacion;
        idUsuarioDeActual = idUsuarioDe;
        idUsuarioParaActual = idUsuarioPara;
        idUsuarioSession
        // alert(idUsuarioSession);

        $.ajax({
            url: 'modelos/rescatar/obtener_mensajes_conversacion.php',
            method: 'POST',
            data: {
                id_conversacion: idConversacion
            },
            success: function(response) {
                try {
                    const result = typeof response === "string" ? JSON.parse(response.trim()) : response;

                    if (result.status === "success") {
                        idConversacionActual = result.idConversacionActual;
                        idUsuarioDeActual = result.idUsuarioDeActual;
                        idUsuarioParaActual = result.idUsuarioParaActual;

                        const primerMensaje = result.primerMensaje;

                        const htmlMensajes = result.mensajes.map(mensaje => {
                            const clase = mensaje.para == idUsuarioSession ? 'user' : 'other';
                            const nombre = mensaje.de == idUsuarioSession ?
                                "Tú" :
                                `${mensaje.nombre_emisor} ${mensaje.apellido_emisor}`;


                            return `
                            <div class="chat-message ${clase}">
                                <strong>${nombre}:</strong> ${mensaje.mensaje}
                            </div>`;
                        }).join('');

                        const offcanvasHTML = `
                        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel" data-bs-backdrop="true">
                            <div class="offcanvas-header">
                            <h5 class="offcanvas-title" id="offcanvasRightLabel">
                                Conversación entre ${result.mensajes[0].para} y ${result.mensajes[0].de}
                            </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body d-flex flex-column">
                                <div id="chatContainer" class="chat-container border rounded p-3 mb-3" style="flex-grow: 1; overflow-y: auto; max-height: 700px;">
                                    ${htmlMensajes}
                                    
                                </div>
                                <div class="input-group mt-3">
                                    <input type="text" class="form-control" placeholder="Escribe un mensaje..." id="inputMensajeOffcanvas">
                                    <button class="btn btn-primary" onclick="enviarMensajeChatEnviados('${idConversacion}', '${idUsuarioDeActual}', '${idUsuarioParaActual}','${idUsuarioSession}')">
                                        <i class="bi bi-send"></i> Enviar
                                    </button>
                                </div>
                            </div>
                        </div>`;

                        const container = document.getElementById('offcanvasContainer');
                        if (!container) {
                            Swal.fire('Error',
                                'El contenedor del chat no existe. Verifica la estructura del HTML.',
                                'error');
                            return;
                        }

                        container.innerHTML = offcanvasHTML;
                        new bootstrap.Offcanvas(document.getElementById("offcanvasRight")).show();
                    } else {
                        Swal.fire('Error', 'No se pudieron cargar los mensajes.', 'error');
                    }
                } catch (error) {
                    console.error("Error al procesar la respuesta del servidor:", error);
                    Swal.fire('Error', 'La respuesta del servidor no es válida.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error al comunicarse con el servidor.', 'error');
            }
        });
    }
    // button del modal de conversaion que envia el mensaje de respuesta  ala tabla conversacion_chat


    function enviarMensajeChatEnviados(idConversacion, idUsuarioDe, idUsuarioPara, idUsuarioSession) {
        // alert("de--->" + idUsuarioDe + "  PARA --->" + idUsuarioPara + "usuario-Session---->" + idUsuarioSession);
        const inputMensaje = document.querySelector("#inputMensajeOffcanvas");
        const mensaje = inputMensaje.value.trim();

        if (mensaje === "") {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No puedes enviar un mensaje vacío.'
            });
            return;
        }

        $.ajax({
            url: "modelos/guardar/guardar_conversacion.php",
            method: "POST",
            data: {
                id_conversacion: idConversacion,
                mensaje: mensaje,
                id_usuario_de: idUsuarioDe,
                idUsuarioSession: idUsuarioSession,
                id_usuario_para: idUsuarioPara
            },
            success: function(response) {
                try {
                    const result = typeof response === "string" ? JSON.parse(response.trim()) : response;

                    if (result.status === "success") {
                        const chatContainer = document.querySelector("#chatContainer");

                        if (chatContainer) {
                            const nuevoMensaje = document.createElement("div");
                            nuevoMensaje.className = "chat-message user";
                            nuevoMensaje.innerHTML = `<strong>Tú:</strong> ${mensaje}`;
                            chatContainer.appendChild(nuevoMensaje);

                            chatContainer.scrollTop = chatContainer.scrollHeight;
                            inputMensaje.value = "";
                        } else {
                            Swal.fire('Error', 'El contenedor del chat no está disponible.', 'error');
                        }
                    } else {
                        Swal.fire('Error', 'No se pudo enviar el mensaje: ' + result.message, 'error');
                    }
                } catch (error) {
                    console.error("Error al procesar la respuesta del servidor:", error);
                    Swal.fire('Error', 'La respuesta del servidor no es válida.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo conectar al servidor.', 'error');
            }
        });
    }


