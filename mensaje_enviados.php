<?php
    session_start();
    require_once 'class/conexion.php';
    require_once 'class/funciones.php';
    $funciones          = new Funciones();
    $idUsuarioSession   = htmlspecialchars($_SESSION['id']);
    $nombresession      = htmlspecialchars($_SESSION['nombre']) . '-' . htmlspecialchars($_SESSION['apellido_paterno']);
    $idPagActual        = 2;
?>
<script>
    var nombresession       = "<?php echo $nombresession; ?>";
    var idUsuarioSession    = "<?php echo $idUsuarioSession; ?>";
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
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
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
    <input type="hidden" id="idUsuario" value="<?php echo $_SESSION['id']; ?>">

    <div id="wrapper">
        <?php $funciones->menuLateral3($idUsuarioSession, $idPagActual); ?>
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
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary" id="titulo"
                                    data-intro="Aquí puedes ver y gestionar tus mensajes.">MENSAJES ENVIADOS                              >
                                </h6>
                            </div>
                                <div id="botonesAccion">
                                    <button type="button" class="btn btn-success" onclick="actualizar_pag()">Actualizar </button>
                                    <button type="button" class="btn btn-primary" onclick="enviar_mensaje_mensaje(idUsuarioSession)">Enviar mensaje</button>
                                    <button type="button" class="btn btn-primary" onclick="enviar_mensaje_mensaje(idUsuarioSession)">Descargar</button>

                                </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th id="numero">N°</th>
                                            <th id="fecha">FECHA</th>
                                            <th id="hora">HORA</th>
                                            <th id="de">PARA</th>
                                            <th id="mensajes">MENSAJE</th>
                                            <th id="opciones">OPCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $mensajes = $funciones->mensajes_Enviados($idUsuarioSession);

                                        $contador = 1;
                                        foreach ($mensajes as $mensaje) {
                                            
                                            ?>
                                            <tr>
                                                <td><?php echo $contador++; ?></td>
                                                <td><?php echo $mensaje['fecha']; ?></td>
                                                <td><?php echo $mensaje['hora']; ?></td>
                                                <td><?php echo "{$mensaje['nombre']} - {$mensaje['apellido_paterno']}"; ?></td>
                                                <td><?php echo htmlentities($mensaje['mensaje'], ENT_HTML5, 'UTF-8'); ?></td>
                                                <td>
                                                    <!--<a href="#" class="btn btn-primary btn-icon-split position-relative" id="btnvermensaje"-->
                                                    <!--    onclick="onclikEnviar_MensajeEnviados(<?php echo $mensaje['id_mensaje']; ?>)">-->
                                                    <!--    <i class="bi bi-eye"></i>-->
                                                    <!--</a>-->
                                                    <?php if ($mensaje['urgente'] === 'SI') { ?>
                                                        <span class="position-absolute top-10 start-10 translate-middle badge rounded-pill bg-danger" id="spanUrgente">
                                                            Urgente
                                                            <span class="visually-hidden">unread messages</span>
                                                        </span>
                                                    <?php } ?>
                                                    <a href="#" class="btn btn-danger btn-icon-split" id="btneliminar" title="Eliminar mensaje"
                                                        onclick="eliminarMensajeEnviado(<?php echo $mensaje['id_mensaje']; ?>)">
                                                        <i class="bi bi-trash3"></i>
                                                    </a>
                                                    <a href="#"
                                                        class="btn btn-primary btn-icon-split" 
                                                        title="Ver Conversación"
                                                        onclick="cargarMensajesConversacionEnviados(<?php echo $mensaje['id_conversacion']; ?>, <?php echo $mensaje['de']; ?>, <?php echo $mensaje['para']; ?>);">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>               
                        <div id="offcanvasContainer"></div>

                    </div>
                </div>
                <a class="scroll-to-top rounded" href="#page-top">
                    <i class="fas fa-angle-up"></i>
                </a>
            </div>  
            <?php $funciones->footer(); ?>
        </div>
    </div>       
        <?php $funciones->script(); ?>

        <script>
                let idConversacionActual    = null;
                let idUsuarioDeActual       = null;
                let idUsuarioParaActual     = null;

            function cargarMensajesConversacionEnviados(idConversacion, idUsuarioDe, idUsuarioPara) {
                idConversacionActual = idConversacion;
                idUsuarioDeActual = idUsuarioDe;
                idUsuarioParaActual = idUsuarioPara;
            
                $.ajax({
                    url: 'modelos/rescatar/obtener_mensajes_conversacion.php',
                    method: 'POST',
                    data: { id_conversacion: idConversacion },
                    success: function (response) {
                        try {
                            const mensajes = JSON.parse(response);
            
                            // Genera el HTML de los mensajes
                            let htmlMensajes = mensajes.map(mensaje => `
                                <div class="chat-message ${mensaje.de == idUsuarioDeActual ? 'user' : 'other'}">
                                    <strong>${mensaje.nombre}:</strong> ${mensaje.mensaje || ''}
                                </div>`).join('');
            
                            // Crear el HTML del Offcanvas
                            const offcanvasHTML = `
                                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" 
                                     aria-labelledby="offcanvasRightLabel" data-bs-backdrop="true">
                                    <div class="offcanvas-header">
                                        <h5 class="offcanvas-title" id="offcanvasRightLabel">Conversación ${idConversacion}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                    </div>
                                    <div class="offcanvas-body d-flex flex-column">
                                        <div id="chatContainer" class="chat-container border rounded p-3 mb-3" 
                                             style="flex-grow: 1; overflow-y: auto; max-height: 700px;">
                                            ${htmlMensajes}
                                        </div>
                                        <div class="input-group mt-3">
                                            <input type="text" class="form-control" placeholder="Escribe un mensaje..." id="inputMensajeOffcanvas">
                                            <button class="btn btn-primary" onclick="enviarMensajeChatEnviados();">
                                                <i class="bi bi-send"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>`;
            
                            // Reemplazar el contenido existente del contenedor del Offcanvas
                            const container = document.getElementById('offcanvasContainer');
                            if (!container) {
                                console.error("El contenedor 'offcanvasContainer' no existe en el DOM.");
                                Swal.fire('Error', 'El contenedor del chat no existe. Verifica la estructura del HTML.', 'error');
                                return;
                            }
            
                            container.innerHTML = offcanvasHTML;
            
                            // Mostrar el Offcanvas
                            const offcanvas = new bootstrap.Offcanvas(document.getElementById("offcanvasRight"));
                            offcanvas.show();
                        } catch (error) {
                            console.error("Error al procesar JSON:", error);
                            Swal.fire('Error', 'Ocurrió un error al cargar los mensajes.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Error al comunicarse con el servidor.', 'error');
                    }
                });
            }

            function enviarMensajeChatEnviados() {
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
                        id_conversacion: idConversacionActual,
                        mensaje: mensaje,
                        id_usuario_de: idUsuarioDeActual,
                        id_usuario_para: idUsuarioParaActual
                    },
                    success: function (response) {
                        console.log("Respuesta del servidor al enviar mensaje:", response);
            
                        try {
                            const result = JSON.parse(response);
            
                            if (result.status === "success") {
                                const chatContainer = document.querySelector("#chatContainer");
            
                                if (chatContainer) {
                                    // Agregar el nuevo mensaje al contenedor
                                    const nuevoMensaje = document.createElement("div");
                                    nuevoMensaje.className = "chat-message user";
                                    nuevoMensaje.innerHTML = `<strong>${nombresession}:</strong> ${mensaje}`;
                                    chatContainer.appendChild(nuevoMensaje);
            
                                    // Hacer scroll al final
                                    chatContainer.scrollTop = chatContainer.scrollHeight;
            
                                    // Limpiar el input
                                    inputMensaje.value = "";
                                } else {
                                    console.error("El contenedor del chat no existe.");
                                    Swal.fire('Error', 'El contenedor del chat no está disponible.', 'error');
                                }
                            } else {
                                Swal.fire('Error', 'No se pudo enviar el mensaje: ' + result.message, 'error');
                            }
                        } catch (error) {
                            console.error("Error al procesar la respuesta del servidor:", error);
                            Swal.fire('Error', 'Ocurrió un error inesperado.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'No se pudo conectar al servidor.', 'error');
                    }
                });
            }
        </script>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>
        <!--<script type='text/javascript' src='template_01/js/funciones.js'></script>-->

</body>

</html>

