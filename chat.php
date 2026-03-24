<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';

$funciones          = new Funciones();
$idUsuarioSession   = htmlspecialchars($_SESSION['id']);
$idPagActual        = "9";  // Página de bitácora

$para               = $_GET['para'] ?? '';
$mensajes           = $funciones->listar_mensajes($idUsuarioSession);
$usuarios           = $funciones->listarUsuarios();
$hayMensajes        = false;

// Iniciar la salida de mensajes
$output = '';

// Filtrar y mostrar solo los mensajes relevantes
foreach ($mensajes as $mensaje) {
    if ($mensaje['de'] == $idUsuarioSession && $mensaje['para'] == $para) {
        // Mensaje enviado por el usuario (a la derecha)
        $output .= '<div class="message receiver d-flex mb-3 justify-content-end">
                        <div class="message-content bg-primary text-white rounded p-2">
                            <p>' . htmlspecialchars($mensaje['mensaje']) . '</p>
                        </div>
                        <img src="img/undraw_profile.svg" class="img-profile rounded-circle" alt="User">
                    </div>';
        $hayMensajes = true;
    } elseif ($mensaje['para'] == $idUsuarioSession && $mensaje['de'] == $para) {
        // Mensaje recibido por el usuario (a la izquierda)
        $output .= '<div class="message sender d-flex mb-3">
                        <img src="img/undraw_profile_3.svg" class="img-profile rounded-circle" alt="User">
                        <div class="message-content bg-light rounded p-2">
                            <p>' . htmlspecialchars($mensaje['mensaje']) . '</p>
                        </div>
                    </div>';
        $hayMensajes = true;
    }
}

// Si no hay mensajes, mostrar "Sin mensajes"
if (!$hayMensajes) {
    $output = '<p class="text-center text-muted">Sin mensajes</p>';
}

// Imprimir el resultado final
echo $output;
?>


<!DOCTYPE html>
<html lang="en">

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
    <link href="css/principal.css" rel="stylesheet">
    <script type="text/javascript" src="js/funciones.js"></script>
</head>

<body id="page-top">
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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Colaboradores</h1>
                    </div>

                    <div id="collaboratorContainer" class="row chat-module">
                        <!-- Lista de Usuarios -->
                        <div class="col-md-3 chat-users bg-light p-3">
                            <h4 class="mb-3">Usuarios</h4>
                            <input type="text" class="form-control mb-3" placeholder="Buscar usuarios...">
                            <ul class="list-group user-list" style="max-height: 600px; overflow-y: auto; overflow-x: hidden;">
                                <?php
                                if (!empty($usuarios)) {
                                    foreach ($usuarios as $usuario) {
                                        $estado = "bg-success";
                                        $icono = "Activo";
                                ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center user-item"
                                            data-id="<?php echo htmlspecialchars($usuario['id']); ?>">
                                            <div class="user-info">
                                                <img src="img/undraw_profile.svg" class="img-profile rounded-circle" alt="User">
                                                <?php echo htmlspecialchars($usuario['nombre'] . " " . $usuario['apellido_paterno']); ?>
                                            </div>
                                            <span class="badge <?php echo $estado; ?> rounded-pill" style="top: 10px;right: 10px;">
                                                <?php echo $icono; ?>
                                            </span>
                                        </li>
                                <?php
                                    }
                                } else {
                                    echo "<p>No hay usuarios disponibles.</p>";
                                }
                                ?>
                            </ul>
                        </div>

                        <!-- Input oculto para almacenar el ID del usuario seleccionado -->
                        <input type="hidden" id="selectedUserId" value="">

                        <!-- Mensajes del Chat -->
                        <div class="col-md-9 chat-messages bg-white p-4">
                            <div class="chat-header d-flex justify-content-between align-items-center mb-3">
                                <h4 class="mb-0">Departamento</h4>
                                <span class="text-muted">Última vez visto: Hace 2 horas</span>
                            </div>

                            <div class="chat-body" style="background-color: #bcccdd;"><br>
                                <?php


                                // Mostrar los mensajes
                                foreach ($mensajes as $mensaje) {
                                    if ($mensaje['de'] == $idUsuarioSession) {
                                        // Mensaje enviado por el usuario (a la derecha)
                                ?>
                                        <div class="message receiver d-flex mb-3 justify-content-end">
                                            <div class="message-content bg-primary text-white rounded p-2">
                                                <p><?php echo htmlspecialchars($mensaje['mensaje']); ?></p>
                                            </div>
                                            <img src="img/undraw_profile.svg" class="img-profile rounded-circle" alt="User">
                                        </div><br>
                                    <?php
                                    } else if ($mensaje['para'] == $idUsuarioSession) {
                                        // Mensaje recibido por el usuario (a la izquierda)
                                    ?>
                                        <div class="message sender d-flex mb-3"><br>
                                            <img src="img/undraw_profile_3.svg" class="img-profile rounded-circle" alt="User">
                                            <div class="message-content bg-light rounded p-2">
                                                <p><?php echo htmlspecialchars($mensaje['mensaje']); ?></p>
                                            </div>
                                        </div>
                                <?php
                                    }
                                }
                                ?>
                            </div>

                            <div class="chat-footer mt-3 d-flex align-items-center">
                                <input type="text" class="form-control me-2" placeholder="Escribe un mensaje...">
                                <button class="btn btn-light me-2" onclick="adjuntarDocumento()">
                                    <i class="fa fa-paperclip"></i>
                                </button>
                                <button class="btn btn-light me-2" onclick="iniciarGrabacion()">
                                    <i class="fa fa-microphone"></i>
                                </button>
                                <button class="btn btn-primary" onclick="enviarMensaje()">
                                    <i class="fa fa-paper-plane"></i>
                                </button>
                                <input type="file" id="documentInput" style="display: none;" />
                            </div>
                        </div>
                    </div>
                </div>

                <a class="scroll-to-top rounded" href="#page-top">
                    <i class="fas fa-angle-up"></i>
                </a>
            </div>
            <?php $funciones->footer(); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"
        integrity="sha384-oBqDVmMz4fnFO9gybByC1LzYr6MkiJG6sjid20+VRmYhJs9axSBLlXcrp1KccfQ3" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-p5JpGJnOt6tW2d2ecXBvm8r3rEN5hAcnM4ygjQ2XJg6roMX9zthHl5OqVM+FQcTf" crossorigin="anonymous">
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <?php $funciones->script(); ?>
    <script>
        // Capturar el evento de clic en un usuario
        document.querySelectorAll('.user-item').forEach(item => {
            item.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                document.getElementById('selectedUserId').value = userId; // Almacenar el ID en el input oculto

                // Llamar a una función para actualizar los mensajes
                actualizarMensajes(userId);
            });
        });

        // Función para actualizar los mensajes
        function actualizarMensajes(userId) {
    fetch(`chat.php?para=${userId}`)
        .then(response => response.text())
        .then(data => {
            document.querySelector('.chat-body').innerHTML = data;
        })
        .catch(error => console.error('Error al obtener los mensajes:', error));
}

        // Función para adjuntar un documento
        function adjuntarDocumento() {
            document.getElementById('documentInput').click();
        }

        // Función para iniciar la grabación
        function iniciarGrabacion() {
            alert('Grabación iniciada');
        }

        // Función para enviar el mensaje
        function enviarMensaje() {
            alert('Mensaje enviado!');
        }
    </script>
</body>

</html>
