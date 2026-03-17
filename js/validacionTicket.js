   
   
    // funcion para dar por termiando el ticket o reactivacion del ticket
    function validacionTicketPorUsuario(idTicket,id_usuario,id_tecnico) {
        $.ajax({
            url: 'modelos/rescatar/calificacion_ticket.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                data.sort((a, b) => a.orden - b.orden);
    
                let estrellasHTML = '';
                let calificacionesPorOrden = {};
                let idsPorOrden = {};
    
                // Generar HTML de estrellas y mapear calificaciones e IDs
                data.forEach((item) => {
                    estrellasHTML += `<i class="fa fa-star estrella" 
                        data-id="${item.id}" 
                        data-orden="${item.orden}" 
                        title="${item.calificacion}"></i>`;
                    
                    calificacionesPorOrden[parseInt(item.orden)] = item.calificacion;
                    idsPorOrden[parseInt(item.orden)] = item.id;
                });
    
                // Mostrar SweetAlert con estrellas y textarea
                Swal.fire({
            title: "<div class='alert alert-dark' role='alert'>Calificacion del Ticket</div>",
                  html: `
                  
                    <style>
                        .estrella {
                            font-size: 50px;
                            color: #ccc;
                            cursor: pointer;
                            transition: color 0.3s;
                            margin: 0 3px;
                        }
                        .estrella.seleccionada {
                            color: gold;
                        }
                        .contenedor-estrellas-error {
                            border: 2px solid red;
                            border-radius: 6px;
                            padding: 8px;
                        }
                    </style>
                    <div class="text-center">
                        <h4>Califica la resolución del ticket</h4>
                        
                        <input type="hidden" id="id_ticket" value="${idTicket}">
                        <input type="hidden" id="id_usuario" value="${id_usuario}">
                        <input type="hidden" id="id_tecnico" value="${id_tecnico}">

                        

                        <div id="nombreCalificacion" class="mb-2 fw-bold text-primary"></div>
                        <div id="estrellasWrapper">
                            <div id="estrellas" class="mb-3">${estrellasHTML}</div>
                        </div>
                        <textarea id="comentario" class="form-control mt-2" style="height: 200px;" placeholder="Deja un comentario (opcional)..."></textarea>
                        <div id="mensajeError" class="text-danger mt-2" style="display: none;">Por favor, selecciona una calificación.</div>
                    </div>
                `,

                     confirmButtonText: "Enviar Validación",
                    allowOutsideClick: false,   // Opcional: evita que se cierre al hacer clic fuera del modal
                    allowEscapeKey: false,      // Opcional: evita que se cierre con la tecla Escape
                    showCloseButton: true,

                          width: '800px',
                    // padding: "40px",
                    customClass: {
                        popup: "cuerpo_modal_guardar",
                        confirmButton: "bt_crear"
                    },
                    
                didOpen: () => {
                    const estrellas = Swal.getPopup().querySelectorAll('.estrella');
                    const nombreCalificacion = Swal.getPopup().querySelector('#nombreCalificacion');
                    const estrellasWrapper = Swal.getPopup().querySelector('#estrellasWrapper');
                    const textarea = Swal.getPopup().querySelector('#comentario');
                
                    estrellas.forEach((estrella, index) => {
                        estrella.addEventListener('click', () => {
                            const orden = parseInt(estrella.dataset.orden);
                
                            // Pintar estrellas hasta la seleccionada
                            estrellas.forEach((e, i) => {
                                e.classList.toggle('seleccionada', i <= index);
                            });
                
                            // Mostrar texto correspondiente
                            nombreCalificacion.textContent = calificacionesPorOrden[orden] || "Sin descripción";
                
                            // Guardar datos en dataset
                            Swal.getPopup().dataset.calificacionId = idsPorOrden[orden];
                            Swal.getPopup().dataset.ordenSeleccionado = orden;
                
                            // Quitar borde rojo si existía
                            estrellasWrapper.classList.remove('contenedor-estrellas-error');
                        });
                    });
                
                    // Eliminar borde rojo del textarea al enfocar
                    textarea.addEventListener('focus', () => {
                        textarea.classList.remove('is-invalid');
                    });
                },

 preConfirm: () => {
    return new Promise((resolve) => {
        const id_ticket = document.getElementById("id_ticket").value;
        const comentario = document.getElementById("comentario").value.trim();
        const calificacionId = Swal.getPopup().dataset.calificacionId;
        const mensajeError = document.getElementById("mensajeError");
        const estrellasWrapper = document.getElementById("estrellasWrapper");
        const textarea = document.getElementById("comentario");

        let valido = true;

        // Validar estrella
        if (!calificacionId) {
            mensajeError.style.display = "block";
            estrellasWrapper.classList.add('contenedor-estrellas-error');
            valido = false;
        } else {
            mensajeError.style.display = "none";
            estrellasWrapper.classList.remove('contenedor-estrellas-error');
        }

        // Validar textarea (si es obligatorio)
        // if (comentario === "") {
        //     textarea.classList.add('is-invalid');
        //     valido = false;
        // }

        // Si hay errores, cancelar
        if (!valido) {
            resolve(false); // ⚠️ IMPORTANTE para que Swal no bloquee el botón
            return;
        }

        // Enviar datos si está todo correcto
        $.ajax({
            type: "POST",
            url: "modelos/guardar/calificaion_ticket.php",
            data: {
                accion: "calificando_ticket",
                id_ticket: id_ticket,
                id_usuario: id_usuario,
                id_tecnico: id_tecnico,

                calificacion: calificacionId,
                comentario: comentario
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    mostrarModalNegativo(); // Puedes usar mostrarModalPositivo si prefieres
                } else {
                    Swal.fire("❌ Error", response.message, "error");
                }
            },
            error: function() {
                mostrarModalPositivo(); // fallback
            }
        });

        resolve(); // ✅ Se cierra correctamente si todo va bien
    });
}

                    
                    
                });
            },
            error: function() {
                Swal.fire("❌ Error", "No se pudieron cargar las calificaciones.", "error");
            }
        });
    }
    
    
    // Función para mostrar el textarea si se selecciona una opción que lo requiere
    function mostrarTexarea(mostrar) {
        let textarea = document.getElementById("comentario");
        textarea.style.display = mostrar ? "block" : "none";
    }

    // Modal positivo para "El ticket está bien resuelto"
    function mostrarModalPositivo() {
            Swal.fire({
                title: "<div class='alert alert-dark' role='alert'>¡Nos alegra ayudarte! 😊</div>",
                text: "Gracias por confiar en nuestro soporte. Si necesitas más ayuda en el futuro, aquí estaremos.",
                icon: "success",
                showConfirmButton: false,       // ❌ No muestra el botón
                timer: 5000,                    // ⏱️ Se cierra en 5 segundos
                allowOutsideClick: false,       // 🔒 No se cierra haciendo clic fuera
                allowEscapeKey: false,          // 🔒 No se cierra con ESC
                customClass: {
                    popup: "cuerpo_modal_guardar"
                }
            }).then(() => {
                    location.reload(); // ✅ Recarga la página al cerrar
                });
            }


    // Modal negativo para otras opciones
    function mostrarModalNegativo() {
        Swal.fire({
            title: "<div class='alert alert-dark' role='alert'>Lo sentimos 😢</div>",
            text: "Lamentamos que el problema no se haya resuelto completamente. Nuestro equipo revisará tu comentario y trabajará en una solución.",
            icon: "warning",
            timer: 4000, // Se cerrará automáticamente en 4 segundos
            showConfirmButton: false, // Oculta el botón de confirmación
            customClass: {
                popup: "cuerpo_modal_guardar"
            }
        }).then(() => {
            location.reload(); // Recargar la página tras éxito
        });
    }

