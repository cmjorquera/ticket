<?php
    session_start();
    require_once 'class/conexion.php';
    require_once 'class/funciones.php';
    $funciones = new Funciones();

    $idUsuarioSession = htmlspecialchars($_SESSION['id']);
    $nombresession    = htmlspecialchars($_SESSION['nombre']) . '--' . htmlspecialchars($_SESSION['apellido_paterno']);
    $idPagActual      = 12;  // PAGINA ACOSDENUNCIA

    // Al inicio, obtenemos el flag "anonima"
    $anonima = isset($_POST['anonima']) ? 1 : 0;

    // Si es denuncia anónima, forzamos los datos del denunciado a ser nulos o vacíos
    if ($anonima == 1) {
        $id_usuario = null;
        $nombre = '';
        $cargo = '';
    } else {
        // Si no es anónima se asignan valores de sesión o recibidos por POST
        $id_usuario = $_SESSION['id'] ?? null;
        $nombre = $_POST['nombre'] ?? '';
        $cargo = $_POST['cargo'] ?? '';
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $funciones->header(); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/acosoLaboral.css.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <script type="text/javascript" src="js/funciones.js"></script>
    <script type="text/javascript" src="js/mensajes.js"></script>
    <script type="text/javascript" src="js/ticket.js"></script>
    <script type="text/javascript" src="js/buscadores.js"></script>
</head>

<body id="page-top">

    <!-- Offcanvas con contenido cargado -->
    <?php include("guiaParaColaboradores.php"); ?>

    <!-- Cargar Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <div id="wrapper">
        <?php $funciones->menuLateral($idUsuarioSession, $idPagActual); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>
                <div class="container-fluid">
                    <div class="row contenedor-tickets" id="idContenedoresEstadosTicket"></div>
                    <div class="card shadow-lg">
                        <div
                            class="card-header bg-danger text-white position-relative d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <h5 class="mb-0">Formulario de Denuncia por Acoso Laboral</h5>
                            </div>
                            <button class="btn btn-primary btn-sm position-absolute" style="top: 10px; right: 10px;"
                                type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDerecho"
                                aria-controls="offcanvasDerecho">
                                <i class="bi bi-info-circle"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <form id="formDenuncia" enctype="multipart/form-data">
                                <!-- Checkbox para denuncia anónima -->
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="anonima" name="anonima"
                                        onchange="toggleCamposUsuario(this.checked)">
                                    <label class="form-check-label" for="anonima">Deseo que mi denuncia sea
                                        anónima</label>
                                </div>
                                <!-- Datos del denunciante (se ocultan si es anónima) -->
                                <div class="row datos-usuario">
                                    <div class="col-md-6 mb-3">
                                        <label for="nombre" class="form-label">Nombre completo</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre"
                                            value="<?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido_paterno']); ?>"
                                            readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cargo" class="form-label">Cargo</label>
                                        <input type="text" class="form-control" id="cargo" name="cargo"
                                            value="<?php echo htmlspecialchars($_SESSION['cargo'] ?? ''); ?>" readonly>
                                    </div>
                                </div>
                                <!-- Sección de dos columnas -->
                                <div class="row">
                                    <!-- Columna Izquierda -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="fecha_incidente" class="form-label">Fecha del incidente</label>
                                            <input type="date" class="form-control" id="fecha_incidente"
                                                name="fecha_incidente" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="involucrado" class="form-label">Persona o área involucrada
                                                (opcional)</label>
                                            <input type="text" class="form-control" id="involucrado" name="involucrado"
                                                placeholder="Ej: Juan Pérez o Área de Logística">
                                        </div>
                                    </div>
                                    <!-- Columna Derecha -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="descripcion" class="form-label">Descripción detallada del
                                                hecho</label>
                                            <textarea class="form-control" id="descripcion" name="descripcion" rows="5"
                                                required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="archivo" class="form-label">Adjuntar evidencia
                                                (opcional)</label>
                                            <input type="file" class="form-control" id="archivo" name="archivo">
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="contactar"
                                                name="contactar">
                                            <label class="form-check-label" for="contactar">Autorizo ser contactado para
                                                más información</label>
                                        </div>
                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" id="confidencial"
                                                name="confidencial" checked>
                                            <label class="form-check-label" for="confidencial">Deseo un seguimiento
                                                confidencial</label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Botón de envío -->
                                <div class="d-grid">
                                    <button type="button" onclick="enviarDenuncia()" class="btn btn-danger">Enviar
                                        denuncia</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php $funciones->footer(); ?>
        </div>
    </div>

    <div id="offcanvasContainerTicket"></div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <?php $funciones->script(); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-1CmrxMRARb6aLqgBO7+2k0ftKGIJyZr+UAm1RlT0dOeXdf33zNBiSXQYHlgN+Pfh" crossorigin="anonymous">
    </script>
    <script type="text/javascript" src="js/funciones.js"></script>
    <script>
    // Función para ocultar los campos de usuario si la denuncia es anónima
    function toggleCamposUsuario(esAnonima) {
        document.querySelector('.datos-usuario').style.display = esAnonima ? 'none' : 'flex';
    }

    function enviarDenuncia() {
        // Primero, eliminamos validaciones previas
        $(".is-invalid").removeClass("is-invalid");
        $("#alertValidation").remove();

        let hasError = false;

        // Definimos los campos obligatorios (puedes incluir más si es necesario)
        const requiredFields = ["#fecha_incidente", "#descripcion", "#involucrado"];

        requiredFields.forEach(function(selector) {
            let input = $(selector);
            if (!input.val().trim()) {
                input.addClass("is-invalid");
                hasError = true;
            }
        });

        // Si existe error, mostramos alert de Bootstrap y detenemos el envío
        if (hasError) {
            var alertHtml = '<div id="alertValidation" class="alert alert-warning" role="alert">' +
                'Por favor, completa los campos obligatorios.' +
                '</div>';
            $("#formDenuncia").prepend(alertHtml);
            return; // Evita enviar el formulario vía AJAX
        }

        // Si no hay error, se procede con el envío por AJAX
        var formData = new FormData(document.getElementById("formDenuncia"));
        $.ajax({
            url: 'modelos/guardar/denuncia_acoso.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                try {
                    var res = JSON.parse(response);
                    if (res.status === "ok") {
                        Swal.fire({
                            title: "Denuncia recibida",
                            text: "Gracias por confiar en nosotros. Tu denuncia ha sido enviada con éxito y será tratada con total confidencialidad y seriedad. No estás sola/o.",
                            icon: "success",
                            confirmButtonText: "Aceptar",
                            showCloseButton: true,
                            customClass: {
                                popup: 'cuerpo_modal_guardar',
                                confirmButton: 'bt_crear'
                            }
                        }).then(function() {
                            $("#formDenuncia")[0].reset();
                        });
                    } else {
                        Swal.fire({
                            title: "Algo salió mal",
                            text: "No se pudo enviar tu denuncia. Por favor, intenta nuevamente o comunícate con el área correspondiente.",
                            icon: "error",
                            confirmButtonText: "Aceptar"
                        });
                    }
                } catch (e) {
                    Swal.fire({
                        title: "Error inesperado",
                        text: "Hubo un problema al procesar la respuesta del servidor. Por favor, intenta más tarde.",
                        icon: "error",
                        confirmButtonText: "Aceptar"
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Swal.fire({
                    title: "Error de conexión",
                    text: "No pudimos enviar tu denuncia debido a un error de red. Intenta nuevamente o revisa tu conexión.",
                    icon: "error",
                    confirmButtonText: "Aceptar"
                });
            }
        });
    }

    // Para cada campo requerido, al escribir se elimina la clase "is-invalid" y la alerta, si existe.
    const requiredFields = ["#fecha_incidente", "#descripcion", "#involucrado"];
    requiredFields.forEach(function(selector) {
        $(selector).on('input', function() {
            if ($(this).val().trim() !== "") {
                $(this).removeClass("is-invalid");
                $("#alertValidation").remove();
            }
        });
    });
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            imageUrl: 'img/ley_karin.png',
            imageAlt: 'Ley Karin',
            imageWidth: 600,
            imageHeight: 'auto',
            title: '',
            showConfirmButton: false,
            backdrop: true,
            allowOutsideClick: true,
            allowEscapeKey: true,
            showCloseButton: true,
            customClass: {
                popup: 'cuerpo_modal_guardar',
            },
            html: `
                <a href="PDF/GUIA-LEY-KARIN-06.08.24-POR-PAGINA-1.pdf" download="Guia_Ley_Karin.pdf" class="btn btn-primary mt-3">
                    <i class="bi bi-download"></i> Descargar Guía en Practica PDF
                </a>
            `
        });
    });
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</body>

</html>