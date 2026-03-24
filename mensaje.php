<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';
$funciones = new Funciones();
$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$nombresession      = htmlspecialchars($_SESSION['nombre']) . '-' . htmlspecialchars($_SESSION['apellido_paterno']);
$idPagActual = 2;

?>
<script>
    var nombresession = "<?php echo $nombresession; ?>";
    var idUsuarioSession = "<?php echo $idUsuarioSession; ?>";
</script>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php $funciones->header(); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <script type="text/javascript" src="js/funciones.js"></script>
    <!--<script type="text/javascript" src="js/actualizaciones.js"></script>-->
    <script type="text/javascript" src="js/mensajes.js"></script>




</head>
<style>
    .badge-counter {
        position: absolute;
        top: 22px;
        /* Ajusta este valor según sea necesario */
        right: 0px;
        /* Ajusta este valor según sea necesario */
        transform: translate(50%, -50%);
        z-index: 10;
        font-size: 0.75rem;
        font-weight: 700;
        color: #fff;
        background-color: #e74a3b;
        border-radius: 10rem;
        padding: 0.25rem 0.5rem;
    }

    /* Contenedor del chat */
    .chat-container {
        background-color: #e5ddd5;
        /* Fondo tipo WhatsApp */
        border-radius: 10px;
        padding: 10px;
        overflow-y: auto;
        max-height: 300px;
    }

    /* Mensajes del chat */
    .chat-message {
        margin-bottom: 10px;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 14px;
        line-height: 1.4;
        max-width: 80%;
    }

    .chat-message.user {
        background-color: #dcf8c6;
        /* Verde claro */
        align-self: flex-end;
        text-align: right;
    }

    .chat-message.other {
        background-color: #fff;
        align-self: flex-start;
        border: 1px solid #ddd;
    }

    /* Input del chat */
    .chat-input-container {
        display: flex;
        align-items: center;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 30px;
        padding: 5px;
        overflow: hidden;
    }

    .chat-input {
        border: none;
        outline: none;
        box-shadow: none;
    }

    .icon-btn {
        background-color: #f1f1f1;
        border: none;
        color: #555;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        margin-right: 5px;
        transition: background-color 0.2s;
    }

    .icon-btn:hover {
        background-color: #ddd;
    }

    .send-btn {
        background-color: #0088ff;
        color: #fff;
        border: none;
        font-size: 1.2rem;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s;
    }

    .send-btn:hover {
        background-color: #0077e6;
    }
</style>

<body id="page-top">
    <div id="wrapper">
        <?php $funciones->menuLateral2($idUsuarioSession, $idPagActual);   ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>
                <div class="container-fluid">
                    <div class="row" id="idContenedoresEstadosTicket">
                        <?php $funciones->contendormensajesRecibidos($idUsuarioSession); ?>
                        <?php $funciones->contenedorMensajesArchivados($idUsuarioSession); ?>
                        <?php $funciones->contenedorMensajesEliminados($idUsuarioSession); ?>
                    </div>
                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary" id="titulo" data-intro="Aquí puedes ver y gestionar tus mensajes.">
                                    MENSAJES RECIBIDOS
                                </h6>
                                <div id="botonesAccion">
                                    <button type="button" class="btn btn-success" onclick="actualizar_pag()">Actualizar </button>
                                    <button type="button" class="btn btn-primary" onclick="enviar_mensaje_mensaje(idUsuarioSession)">Enviar mensaje</button>
                                    <button type="button" class="btn btn-primary" onclick="enviar_mensaje_mensaje(idUsuarioSession)">Descargar</button>

                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>N°</th>
                                            <th>FECHA</th>
                                            <th>HORA</th>
                                            <th>DE</th>
                                            <th>MENSAJE</th>
                                            <th>OPCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $mensajes = $funciones->obtenerMensajes($_SESSION['id']);
                                        $contador = 1;
                                        foreach ($mensajes as $row): ?>
                                            <tr>
                                                <td><?php echo $contador++; ?></td>
                                                <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                                                <td><?php echo htmlspecialchars($row['hora']); ?></td>
                                                <td><?php echo htmlspecialchars($row['nombre'] . ' - ' . $row['apellido_paterno']); ?></td>
                                                <td><?php echo htmlentities($row['mensaje'], ENT_HTML5, "ISO-8859-1"); ?></td>
                                                <td>
                                                    <!-- Ver mensaje -->
                                                    <!--<a href="#" class="btn btn-primary btn-icon-split" title="Ver Mensaje" onclick="verMensajeResibido(<?php echo $row['id_mensaje']; ?>)">-->
                                                    <!--    <i class="bi bi-eye"></i>-->
                                                    <!--</a>-->

                                                    <!-- Urgente Badge -->
                                                    <!--<?php if ($row['urgente'] == 'SI'): ?>-->
                                                    <!--    <span class="badge bg-danger text-light">Urgente</span>-->
                                                    <!--<?php endif; ?>-->

                                                    <!-- Archivar mensaje -->
                                                    <a href="#" class="btn btn-warning btn-icon-split" title="Archivar mensaje" onclick="mensajeArchivado(<?php echo $row['id_mensaje']; ?>)">
                                                        <i class="bi bi-archive"></i>
                                                    </a>

                                                    <!-- Eliminar mensaje -->
                                                    <a href="#" class="btn btn-danger btn-icon-split" title="Eliminar mensaje" onclick="eliminarMensaje(<?php echo $row['id_mensaje']; ?>)">
                                                        <i class="bi bi-trash3"></i>
                                                    </a>
                                                      <a href="#"
                                                           class="btn btn-primary btn-icon-split" 
                                                           title="Ver Conversación"
                                                           onclick="cargarMensajesConversacion(<?php echo $row['id_conversacion']; ?>, <?php echo $row['de']; ?>, <?php echo $row['para']; ?>);">
                                                            <i class="bi bi-eye"></i>
                                                        </a>



                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="offcanvasContainer"></div>

                    <a class="scroll-to-top rounded" href="#page-top">
                        <i class="fas fa-angle-up"></i>
                    </a>
                </div>
                <?php $funciones->footer(); ?>
            </div>
        </div>

        <?php $funciones->script(); ?>

        <script>
                let idConversacionActual = null;
                let idUsuarioDeActual = null;
                let idUsuarioParaActual = null;

            function cargarMensajesConversacion(idConversacion, idUsuarioDe, idUsuarioPara) {
                idConversacionActual = idConversacion;
                idUsuarioDeActual = idUsuarioDe;
                idUsuarioParaActual = idUsuarioPara;
            
                $.ajax({
                    url: 'modelos/rescatar/obtener_mensajes_conversacion.php',
                    method: 'POST',
                    data: { id_conversacion: idConversacion },
                    success: function (response) {
                        const mensajes = JSON.parse(response);
            
                        // Genera el HTML de los mensajes
                        let htmlMensajes = mensajes.map(mensaje => `
                            <div class="chat-message ${mensaje.de == idUsuarioDeActual ? 'user' : 'other'}">
                                <strong>${mensaje.nombre}:</strong> ${mensaje.mensaje || ''}
                                ${mensaje.archivo ? `
                                    <div class="archivo-adjunto">
                                        ${getIconoArchivo(mensaje.tipo_archivo)} 
                                        <a href="${mensaje.archivo}" target="_blank">${mensaje.nombre_archivo}</a>
                                    </div>` : ''}
                            </div>`).join('');
            
                        // Crea el HTML del Offcanvas
                        const offcanvasHTML = `
                            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" 
                                 aria-labelledby="offcanvasRightLabel" data-bs-backdrop="true">
                                <div class="offcanvas-header">
                                    <h5 class="offcanvas-title" id="offcanvasRightLabel">
                                        Conversación ${idConversacion}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body d-flex flex-column">
                                    <div id="chatContainer" class="chat-container border rounded p-3 mb-3" 
                                         style="flex-grow: 1; overflow-y: auto; max-height: 700px;">
                                        ${htmlMensajes}
                                    </div>
                                    <div class="input-group mt-3">
                                        <input type="text" class="form-control" placeholder="Escribe un mensaje..." id="inputMensajeOffcanvas">
                                        <button class="btn btn-secondary" onclick="document.getElementById('inputArchivo').click();" title="Adjuntar archivo">
                                            <i class="bi bi-paperclip"></i>
                                        </button>
                                        <input type="file" id="inputArchivo" class="d-none" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" onchange="prepararArchivo(this)">
                                        <button class="btn btn-primary" onclick="enviarMensajeChat();" title="Enviar mensaje">
                                            <i class="bi bi-send"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>`;
            
                        // Elimina Offcanvas existente y muestra el nuevo
                        const container = document.getElementById('offcanvasContainer');
                        container.innerHTML = offcanvasHTML;
            
                        const offcanvas = new bootstrap.Offcanvas(document.getElementById("offcanvasRight"));
                        offcanvas.show();
                    }
                });
            }

            
            function getIconoArchivo(tipoArchivo) {
                if (tipoArchivo.startsWith('image/')) {
                    return '<i class="bi bi-file-image text-primary" style="font-size: 2rem;"></i>';
                } else if (tipoArchivo === 'application/pdf') {
                    return '<i class="bi bi-file-pdf text-danger" style="font-size: 2rem;"></i>';
                } else if (tipoArchivo === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' || tipoArchivo === 'application/msword') {
                    return '<i class="bi bi-file-word text-primary" style="font-size: 2rem;"></i>';
                } else if (tipoArchivo === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || tipoArchivo === 'application/vnd.ms-excel') {
                    return '<i class="bi bi-file-excel text-success" style="font-size: 2rem;"></i>';
                } else {
                    return '<i class="bi bi-file-earmark text-secondary" style="font-size: 2rem;"></i>';
                }
            }

            function enviarMensajeChat() {
                const inputMensaje = document.querySelector("#inputMensajeOffcanvas");
                const mensaje = inputMensaje.value.trim();
            
                if (!mensaje && !archivoAdjunto) {
                    alert("Escribe un mensaje o adjunta un archivo.");
                    return;
                }
            
                const formData = new FormData();
                formData.append('id_conversacion', idConversacionActual);
                formData.append('mensaje', mensaje);
                formData.append('id_usuario_de', idUsuarioDeActual);
                formData.append('id_usuario_para', idUsuarioParaActual);
                if (archivoAdjunto) formData.append('archivo', archivoAdjunto);
            
                $.ajax({
                    url: "modelos/guardar/guardar_conversacion.php",
                    method: "POST",
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function(response) {
                        const result = JSON.parse(response);
            
                        if (result.status === "success") {
                            const chatContainer = document.querySelector("#chatContainer");
            
                            const nuevoMensaje = document.createElement("div");
                            nuevoMensaje.className = "chat-message other";
                            nuevoMensaje.innerHTML = `
                                <strong>${nombresession}:</strong> <span>${mensaje}</span>
                                ${archivoAdjunto ? `
                                    <div class="archivo-adjunto">
                                        <a href="${result.archivo_url}" target="_blank">Ver archivo adjunto</a>
                                    </div>` : ''}
                            `;
            
                            chatContainer.appendChild(nuevoMensaje);
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                            inputMensaje.value = "";
                            archivoAdjunto = null; // Limpiar archivo adjunto
                        } else {
                            alert("Error al enviar el mensaje: " + result.message);
                        }
                    },
                    error: function() {
                        alert("Error al enviar el mensaje.");
                    }
                });
            }

       
            let archivoAdjunto = null;
        
            function prepararArchivo(input) {
            archivoAdjunto = input.files[0];
            if (archivoAdjunto) {
                const archivoNombre = archivoAdjunto.name;
                const archivoTipo = archivoAdjunto.type;
        
                document.getElementById('archivoNombre').textContent = `Archivo seleccionado: ${archivoNombre}`;
                const previewContainer = document.getElementById('archivoPreview');
                previewContainer.innerHTML = '';
        
                if (archivoTipo.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(archivoAdjunto);
                    img.style.maxWidth = '100%';
                    img.style.height = 'auto';
                    previewContainer.appendChild(img);
                } else {
                    previewContainer.innerHTML = getIconoArchivo(archivoTipo);
                }
        
                const modal = new bootstrap.Modal(document.getElementById('modalPrevisualizarArchivo'));
                modal.show();
            }
        }


       
          document.getElementById('confirmarArchivo').addEventListener('click', function () {
            if (archivoAdjunto) {
                const archivoNombre = archivoAdjunto.name;
                const archivoTipo = archivoAdjunto.type;
        
                const iconHtml = getIconoArchivo(archivoTipo);
                const archivoHtml = `
                    <div class="chat-message user">
                        <strong>Tú:</strong>
                        <div class="archivo-adjunto">
                            ${iconHtml}
                            <span>${archivoNombre}</span>
                        </div>
                    </div>`;
        
                const chatContainer = document.getElementById('chatContainer');
                chatContainer.innerHTML += archivoHtml;
                chatContainer.scrollTop = chatContainer.scrollHeight;
        
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalPrevisualizarArchivo'));
                modal.hide();
            }
        });


        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>
        <!--<script type='text/javascript' src='template_01/js/funciones.js'></script>-->
</body>


</html>
