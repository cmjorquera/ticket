// Paso 3: Crear la funci贸n JavaScript para cargar los mensajes de un ticket en tiempo real

let ejecutandoChat = false;





function cargarTicketConversacion(idTicket, idUsuarioSession, idUsuarioOtro,estado) {
// alert(idTicket + "\n" + idUsuarioSession + "\n" + idUsuarioOtro + "\n" + estado);
    const contenedor = document.getElementById('offcanvasContainerTicket');
    if (!contenedor) return;
    
    const mostrarInput = estado != 5;

const offcanvasHTML = `
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasTicket" aria-labelledby="offcanvasTicketLabel" data-bs-backdrop="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasTicketLabel">Ticket #${idTicket}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <div id="chatContainerTicket" class="chat-container border rounded p-3 mb-3" style="flex-grow: 1; overflow-y: auto; max-height: 700px;"></div>

        ${mostrarInput ? `
        <div class="input-group mt-3">
            <input type="text" class="form-control" placeholder="Escribe un mensaje..." id="inputMensajeTicket">
            <input type="file" id="inputArchivoTicket" accept="*/*" style="display: none;" 
                onchange="enviarAdjuntoTicket(${idTicket}, ${idUsuarioSession}, ${idUsuarioOtro})">
            <button class="btn btn-secondary" onclick="document.getElementById('inputArchivoTicket').click()" title="Adjuntar archivo">
                <i class="bi bi-paperclip"></i>
            </button>
            <button class="btn btn-primary" onclick="enviarMensajeTicket(${idTicket}, ${idUsuarioSession}, ${idUsuarioOtro})">
                <i class="bi bi-send"></i>
            </button>
        </div>
        ` : `
        <div class="text-center mt-3">
            <i class="bi bi-lock-fill text-secondary fs-2"></i>
            <p class="text-muted mb-0 mt-2">
                Este ticket esta cerrado.<br>No se pueden enviar nuevos mensajes.
            <!-- <button type="button" class="btn btn-primary btn-lg btn-block">Descargar Conversacion </button>-->
            </p>
        </div>
        `}
    </div>
</div>`;


    contenedor.innerHTML = offcanvasHTML;

    const offcanvas = new bootstrap.Offcanvas(document.getElementById("offcanvasTicket"));
    offcanvas.show();
    
        // ******************************************************************
    //  Aquí marcamos como leídos
//   fetch('modelos/actualizar/marcar_leido_ticket.php', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//         body: `id_ticket=${idTicket}&receptor=${idUsuarioSession}`
//     });
        
        $.ajax({
            url: 'modelos/actualizar/marcar_leido_ticket.php',
            method: 'POST',
            data: {
                id_ticket: idTicket,
                receptor: idUsuarioSession
            },
            success: function (res) {
                console.log("Mensajes marcados como leídos");
            },
            error: function () {
                console.error("Error al marcar como leídos");
            }
        });

    // ******************************************************************

    actualizarTicketChat(idTicket, idUsuarioSession, idUsuarioOtro);

    if (window.intervaloTicketChat) clearInterval(window.intervaloTicketChat);
    window.intervaloTicketChat = setInterval(() => {
        actualizarTicketChat(idTicket, idUsuarioSession, idUsuarioOtro);
    }, 3000);
}

// Paso 4: funci贸n que rescata los mensajes
function actualizarTicketChat(idTicket, idUsuarioSession, idUsuarioOtro) {
    if (ejecutandoChat) return;
    ejecutandoChat = true;

    fetch('modelos/rescatar/conversacionTecnicoUsuario.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_ticket=${idTicket}&usuario1=${idUsuarioSession}&usuario2=${idUsuarioOtro}`
    })
    .then(res => res.json())
    .then(async data => {
        if (!data.success || !Array.isArray(data.mensajes)) return;

        const container = document.getElementById('chatContainerTicket');
        let html = '';

        for (const msg of data.mensajes) {
            const esEmisor = msg.emisor == idUsuarioSession;
            const clase = esEmisor ? 'text-end bg-light' : 'text-start bg-success bg-opacity-25';

            let nombre = '';
            await $.ajax({
                url: 'modelos/rescatar/usuario.php',
                method: 'POST',
                data: { id: msg.emisor },
                dataType: 'json',
                async: false,
                success: function(data) {
                    nombre = `${data.nombre} ${data.apellido_paterno}`;
                }
            });

            // Si hay archivo adjunto
            if (msg.adjunto && msg.adjunto !== "") {
                const extension = msg.adjunto.split('.').pop().toLowerCase();
                let contenido = '';

                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
                    contenido = `<img src="${msg.adjunto}" alt="Imagen" style="max-width: 200px;">`;
                } else if (extension === 'pdf') {
                    contenido = `<iframe src="${msg.adjunto}" style="width:100%; height:400px;" frameborder="0"></iframe>`;
                } else if (['xls', 'xlsx'].includes(extension)) {
                    const urlPublica = `${window.location.origin}/${msg.adjunto}`;
                    contenido = `
                      <iframe src="https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(urlPublica)}"
                              style="width:100%; height:400px;" frameborder="0"></iframe>`;
                } else {
                    const icono = `<img src="iconos/archivo.png" alt="Archivo" style="width:40px;"><br>`;
                    contenido = `${icono}<a href="${msg.adjunto}" download="${msg.adjunto.split('/').pop()}">${msg.adjunto.split('/').pop()}</a>`;
                }

                html += `
                <div class="mb-2 ${clase} p-2 rounded">
                    <strong>${nombre}:</strong><br>
                    ${contenido}
                    <br><small class="text-muted">${msg.fecha} ${msg.hora}</small>
                </div>`;
            }

            // Si hay mensaje de texto
            if (msg.mensaje && msg.mensaje !== "") {
                html += `
                <div class="mb-2 ${clase} p-2 rounded">
                    <strong>${nombre}:</strong> ${msg.mensaje}<br>
                    <small class="text-muted">${msg.fecha} ${msg.hora}</small>
                </div>`;
            }
        }

        container.innerHTML = html;
        container.scrollTop = container.scrollHeight;
    })
    .finally(() => {
        ejecutandoChat = false;
    });
}

// Paso 5: funci贸n para enviar mensaje
function enviarMensajeTicket(idTicket, emisor, receptor) {
    const input = document.getElementById('inputMensajeTicket');
    const mensaje = input.value.trim();

    if (!mensaje) return;

    const formData = new FormData();
    formData.append('id_ticket', idTicket);
    formData.append('emisor', emisor);
    formData.append('receptor', receptor);
    formData.append('mensaje', mensaje);
    formData.append('tipo', 'texto');

    fetch('modelos/guardar/conversacionTecnicoUsuario.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            actualizarTicketChat(idTicket, emisor, receptor);
        } else {
            alert("Error al enviar mensaje");
        }
    });
}

// Nueva funci贸n para enviar adjunto por separado
function enviarAdjuntoTicket(idTicket, emisor, receptor, inputFileId = 'inputArchivoTicket') {
    const input = document.getElementById(inputFileId);
    const archivo = input.files[0];
    if (!archivo) return;

    const formData = new FormData();
    formData.append('id_ticket', idTicket);
    formData.append('emisor', emisor);
    formData.append('receptor', receptor);
    formData.append('archivo', archivo);

    fetch('modelos/guardar/conversacionAdjuntosTicket.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(async data => {
        if (data.success) {
            const chat = document.getElementById('chatContainerTicket');
            const nombre = await obtenerNombre(emisor);
            const extension = data.archivo.split('.').pop().toLowerCase();
            const nombreArchivo = data.archivo.split('/').pop();
            let contenido = '';

            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
                contenido = `<img src="${data.archivo}" alt="Imagen" style="max-width: 200px;">`;
            } else if (['pdf'].includes(extension)) {
                contenido = `<iframe src="${data.archivo}" style="width:100%; height:400px;" frameborder="0"></iframe>`;
            } else if (['xls', 'xlsx'].includes(extension)) {
                // Necesita que el archivo esté disponible públicamente para visualizar con Office
                const urlPublica = `${window.location.origin}/${data.archivo}`;
                contenido = `
                  <iframe src="https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(urlPublica)}"
                          style="width:100%; height:400px;" frameborder="0"></iframe>`;
            } else {
                const icono = `<img src="iconos/archivo.png" alt="Archivo" style="width:40px;"><br>`;
                contenido = `${icono}<a href="${data.archivo}" download="${nombreArchivo}">${nombreArchivo}</a>`;
            }

            const mensajeHTML = `
                <div class="mb-2 text-end bg-light p-2 rounded">
                    <strong style="font-size: 10px;">${nombre}:</strong><br>
                    ${contenido}
                    <br><small class="text-muted">${formatearFecha(data.fecha)} ${data.hora}</small>
                </div>
            `;

            chat.insertAdjacentHTML('beforeend', mensajeHTML);
            chat.scrollTop = chat.scrollHeight;
            input.value = '';
        } else {
            alert("Error al subir el archivo.");
        }
    })
    .catch(err => {
        console.error("Error al enviar archivo:", err);
        alert("Error inesperado.");
    });
}

function obtenerNombre(id) {
  return fetch('modelos/rescatar/usuario.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'id=' + encodeURIComponent(id)
  })
  .then(response => response.json())
  .then(data => `${data.nombre} ${data.apellido_paterno}`);
}

function formatearFecha(fecha) {
  const d = new Date(fecha);
  return d.toLocaleDateString('es-CL', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
}
