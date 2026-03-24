<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';

$funciones = new Funciones();
$idUsuarioSession   = htmlspecialchars($_SESSION['id']);
$nombresession      = htmlspecialchars($_SESSION['nombre']) . '-' . htmlspecialchars($_SESSION['apellido_paterno']);
$nombre             = htmlspecialchars($_SESSION['nombre']) . ' ' . htmlspecialchars($_SESSION['apellido_paterno']) . ' ' . htmlspecialchars($_SESSION['apellido_materno']);
$idPagActual        = 0;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php $funciones->header(); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <style>
        
        .carousel-caption {
            position: absolute;
            top: 5%;
            /* Ajusta este valor para subir o bajar la posición del contenido */
            left: 50%;
            transform: translate(-50%, -30%);
            text-align: center;
            z-index: 10;
            color: #fff;
            width: 80%;
            /* Ajusta según sea necesario */
        }

        .carousel-caption .btn,
        .carousel-caption h5,
        .carousel-caption p {
            margin-bottom: 10px;
        }

        .carousel-item {
            transition: transform 0.6s ease-in-out;
            backface-visibility: hidden;
            perspective: 1000px;
        }

        .carousel-inner {
            position: relative;
            width: 100%;
            overflow: hidden;
            transform-style: preserve-3d;
        }

        .carousel-item-next,
        .carousel-item-prev,
        .carousel-item.active {
            display: block;
        }

        .carousel-caption-right {
            position: absolute;
            right: 0;
            top: 0;
            padding: 10px;
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            font-size: 1.5em;
            z-index: 20;
        }

        .carousel-inner {
            max-height: 400px;
        }

        .carousel-inner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .container {
            width: 100%;
            margin: auto;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .col {
            flex: 0 0 25%;
            max-width: 25%;
            padding: 10px;
            box-sizing: border-box;
        }

        .col-xl-2.col-md-6.mb-3 {
            padding: 10px;
        }

        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            transition: transform 0.2s;
            height: 300px;
            position: relative;
        }

        .cardcarrusel {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            transition: transform 0.2s;
            height: 350px;
            position: relative;
        }

        .card:hover {
            transform: translateY(-10px);
        }

        .card-body {
            padding: 20px;
        }

        .contenedor-imagen img {
            width: 100%;
            height: auto;
        }

        .text-xs.font-weight-bold.text-primary.text-uppercase.mb-1 {
            position: absolute;
            bottom: 10px;
            left: 0;
            right: 0;
            color: black;
            text-align: center;
            padding: 20px;
        }

        .shadow-box {
            background-size: cover;
            background-position: center;
            transition: transform 0.2s;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            height: 100px;
            position: relative;
            padding: 20px;
        }

        .shadow-box:hover {
            transform: translateY(-10px);
        }

        .shadow-box .card-title {
            position: absolute;
            bottom: 10px;
            left: 0;
            right: 0;
            color: black;
            text-align: center;
            padding: 20px;
        }

        .icono-grande-negro {
            display: block;
            margin: auto;
            font-size: 150rem;
            color: black;
            padding-left: 55px;
        }

        .tabla {
            width: 100%;
        }

        .tabla td {
            padding: 30px;
            vertical-align: top;
        }

        .shadow-box h4 {
            color: white;
            font-weight: bold;
        }

        @media (max-width: 992px) {
            .tabla {
                display: flex;
                flex-direction: column;
            }

            .tabla tr {
                display: flex;
                flex-wrap: wrap;
                width: 100%;
            }

            .tabla td {
                flex: 1 1 100%;
                max-width: 100%;
                box-sizing: border-box;
            }
        }

        .fixed-buttons {
            position: fixed;
            top: 130px;
            right: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 1000;
            /* Asegura que los botones se mantengan sobre otros elementos */
        }
    </style>

</head>

<script>
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

function lupe() {
    Swal.fire({
        title: `<div class="alert alert-dark" role="alert">Agregar Contenedor</div>`,
        html: `
            <div class="alert alert-secondary" role="alert">
                <form class="row g-3" id="ticketForm" enctype="multipart/form-data">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="nombreContenedor" placeholder="Nombre del Contenedor">
                            <label for="nombreContenedor">Nombre del Contenedor</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="file" class="form-control" id="urlImagen" accept="image/*" placeholder="URL de la Imagen">
                            <label for="urlImagen">URL de la Imagen</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="urlBeneficio" placeholder="URL del Beneficio">
                            <label for="urlBeneficio">URL del Beneficio</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <img id="previewImage" src="" alt="Previsualización de la imagen" style="display: none; width: 100%; max-height: 300px; object-fit: cover;">
                    </div>
                </form>
            </div>`,
        focusConfirm: false,
        width: "870px",
        padding: "40px",
        showCancelButton: false,
        confirmButtonText: 'CREAR CONTENEDOR',
        confirmButtonColor: '#5B8E4A',
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_activar_alumno'
        },
        preConfirm: () => {
            const nombreContenedor = document.getElementById('nombreContenedor');
            const urlImagen = document.getElementById('urlImagen');
            const urlBeneficio = document.getElementById('urlBeneficio');

            if (!validarCamposVacios(nombreContenedor, urlImagen, urlBeneficio)) {
                Swal.showValidationMessage("Por favor, completa todos los campos.");
                return false;
            }

            const formData = new FormData();
            formData.append('nombreContenedor', nombreContenedor.value);
            formData.append('urlImagen', urlImagen.files[0]);
            formData.append('urlBeneficio', urlBeneficio.value);

            return formData;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            $.ajax({
                url: 'modelos/guardar/guardar_contenedor_beneficios.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.fire({
                        title: '<div class="alert alert-dark" role="alert">CONTENEDOR GUARDADO</div>',
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
                        window.location.reload();
                    });
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un problema al registrar el contenedor. Inténtalo de nuevo.',
                        icon: 'error',
                        confirmButtonText: 'Cerrar',
                        customClass: {
                            popup: 'cuerpo_modal_guardar',
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            });
        }
    });

    document.getElementById('urlImagen').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImage = document.getElementById('previewImage');
                previewImage.src = e.target.result;
                previewImage.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
}


function carrusel() {
    Swal.fire({
        title: `<div class="alert alert-dark" role="alert">Agregar destacados en carrusel</div>`,
        html: `
            <div class="alert alert-secondary" role="alert">
                <form class="row g-3" id="ticketForm" enctype="multipart/form-data">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="titulo" placeholder="Nombre del Contenedor">
                            <label for="titulo">Titulo</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="file" class="form-control" id="urlImagen" accept="image/*" placeholder="URL de la Imagen">
                            <label for="urlImagen">Imagen</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="urlBeneficio" placeholder="URL del Beneficio">
                            <label for="urlBeneficio">URL del Beneficio</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="idDescripcion" placeholder="Descripcion">
                            <label for="idDescripcion">Descripcion</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <img id="previewImage" src="" alt="Previsualización de la imagen" style="display: none; width: 100%; max-height: 300px; object-fit: cover;">
                    </div>
                </form>
            </div>`,
        focusConfirm: false,
        width: "870px",
        padding: "40px",
        showCancelButton: false,
        confirmButtonText: 'CREAR CONTENEDOR',
        confirmButtonColor: '#5B8E4A',
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_activar_alumno'
        },
        preConfirm: () => {
            const titulo = document.getElementById('titulo');
            const urlImagen = document.getElementById('urlImagen');
            const urlBeneficio = document.getElementById('urlBeneficio');
            const descripcion = document.getElementById('idDescripcion');

            if (!validarCamposVacios(titulo, urlImagen, urlBeneficio, descripcion)) {
                Swal.showValidationMessage("Por favor, completa todos los campos.");
                return false;
            }

            const formData = new FormData();
            formData.append('titulo', titulo.value);
            formData.append('urlImagen', urlImagen.files[0]);
            formData.append('urlBeneficio', urlBeneficio.value);
            formData.append('descripcion', descripcion.value);

            return formData;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = result.value;
            $.ajax({
                url: 'modelos/guardar/guardar_contenedor_carrusel.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.fire({
                        title: '<div class="alert alert-dark" role="alert">CONTENEDOR GUARDADO</div>',
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
                        window.location.reload();
                    });
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un problema al registrar el contenedor. Inténtalo de nuevo.',
                        icon: 'error',
                        confirmButtonText: 'Cerrar',
                        customClass: {
                            popup: 'cuerpo_modal_guardar',
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            });
        }
    });

    document.getElementById('urlImagen').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImage = document.getElementById('previewImage');
                previewImage.src = e.target.result;
                previewImage.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
}

function eliminarBeneficioPrincipal(id) {
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
            confirmButton: 'bt_activar_alumno',
            cancelButton: 'bt_activar_alumno'
        },
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'modelos/eliminar/eliminar_contenedor_beneficio.php', // Asegúrate de que esta URL es correcta
                type: 'POST',
                data: {
                    id: id
                }, // Envía el ID del contenedor a eliminar
                success: function(response) {
                    // Procesa la respuesta del servidor
                    if (response.success) {
                        Swal.fire({
                            title: '<div class="alert alert-dark" role="alert">ELIMINADO!</div>',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            timer: 3000, // Tiempo antes de cerrar el modal automáticamente, en milisegundos
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
                        Swal.fire('Error', 'No se pudo eliminar el contenedor: ' + response.error, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error', 'Ha ocurrido un error al intentar eliminar el contenedor: ' + error, 'error');
                }
            });
        }
    });
}


function modificarBeneficioPrincipal(id) {
    // Primero, hacer una solicitud AJAX para obtener los datos actuales del beneficio
    $.ajax({
        url: 'modelos/rescatar/contenedor_principal.php', // Asegúrate de que esta URL es correcta
        type: 'GET',
        data: {
            id: id
        },
        success: function(response) {
            if (response.success) {
                const beneficio = response.data;

                // Mostrar el modal con los datos del beneficio cargados
                Swal.fire({
                    title: 'Editar Beneficio',
                    html: `
                        <div class="alert alert-secondary" role="alert">
                            <form class="row g-3" id="formEditarBeneficio" enctype="multipart/form-data">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="nombreContenedor" value="${beneficio.nombre}" placeholder="Nombre del Contenedor">
                                        <label for="nombreContenedor">Nombre del Contenedor</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="file" class="form-control" id="urlImagen" accept="image/*" placeholder="URL de la Imagen">
                                        <label for="urlImagen">URL de la Imagen</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="urlBeneficio" value="${beneficio.url}" placeholder="URL del Beneficio">
                                        <label for="urlBeneficio">URL del Beneficio</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <img id="previewImage" src="${beneficio.imagen}" alt="Previsualización de la imagen" style="display: block; width: 100%; max-height: 300px; object-fit: cover;">
                                </div>
                            </form>
                        </div>`,
                    focusConfirm: false,
                    width: "870px",
                    padding: "40px",
                    showCancelButton: true,
                    confirmButtonText: 'Guardar Cambios',
                    confirmButtonColor: '#5B8E4A',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        popup: 'cuerpo_modal_guardar',
                        confirmButton: 'bt_activar_alumno'
                    },
                    preConfirm: () => {
                        const nombreContenedor = document.getElementById('nombreContenedor').value;
                        const urlImagen = document.getElementById('urlImagen').files[0];
                        const urlBeneficio = document.getElementById('urlBeneficio').value;

                        if (!nombreContenedor || !urlBeneficio) {
                            Swal.showValidationMessage(`Por favor ingrese todos los campos`);
                            return false;
                        }

                        const formData = new FormData();
                        formData.append('id', id);
                        formData.append('nombre', nombreContenedor);
                        formData.append('url', urlBeneficio);
                        if (urlImagen) {
                            formData.append('imagen', urlImagen);
                        }

                        return formData;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = result.value;
                        $.ajax({
                            url: 'modelos/editar/editar_contenedor_beneficio.php',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Beneficio Editado',
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', 'No se pudo editar el beneficio: ' + response.error, 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire('Error', 'Ha ocurrido un error al intentar editar el beneficio: ' + error, 'error');
                            }
                        });
                    }
                });

                // Previsualización de la imagen seleccionada
                document.getElementById('urlImagen').addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewImage = document.getElementById('previewImage');
                            previewImage.src = e.target.result;
                            previewImage.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                Swal.fire('Error', 'No se pudieron obtener los datos del beneficio: ' + response.error, 'error');
            }
        },
        error: function(xhr, status, error) {
            Swal.fire('Error', 'Ha ocurrido un error al intentar obtener los datos del beneficio: ' + error, 'error');
        }
    });
}

</script>

<body id="page-top">
    <div id="wrapper">
        <?php $funciones->menuLateral2($idUsuarioSession, $idPagActual); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>
                <div class="fixed-buttons">
                    <button type="button" class="btn btn-warning" onclick="lupe()">cont</button>
                    <button type="button" class="btn btn-danger" onclick="carrusel()">Carrusel</button>
                </div>
                <div class="container-fluid" id="idContenedorPrincipal">
                    <div class="cardcarrusel">
                        <div class="card-body">
                            <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0"
                                        aria-label="Slide 1"></button>
                                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1"
                                        aria-label="Slide 2"></button>
                                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2"
                                        class="active" aria-current="true" aria-label="Slide 3"></button>
                                </div>
                                <div class="carousel-inner"
                                    style="border-style: solid; border-color: black; height: 300px">
                                    <div class="carousel-caption-right">
                                        BENEFICIOS DESTACADOS
                                    </div>
                                    <?php $funciones->beneficios_carrusel(); ?>
                                </div>
                                <button class="carousel-control-prev" type="button"
                                    data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden" style="color:red">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button"
                                    data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <?php $funciones->beneficios_principales($idUsuarioSession); ?>
                    </div>
                </div>

                <a class="scroll-to-top rounded" href="#page-top">
                    <i class="fas fa-angle-up"></i>
                </a>
            </div>
            <?php $funciones->footer(); ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9gybByC1LzYr6MkiJG6sjid20+VRmYhJs9axSBLlXcrp1KccfQ3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-p5JpGJnOt6tW2d2ecXBvm8r3rEN5hAcnM4ygjQ2XJg6roMX9zthHl5OqVM+FQcTf" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <?php $funciones->script(); ?>
    <?php include("modal_salir.php") ?>
</body>

</html>
