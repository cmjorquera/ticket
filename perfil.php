<?php
    session_start();
    require_once 'class/conexion.php';
    require_once 'class/funciones.php';
    require_once 'class/personas.php';

    
    $funciones          = new Funciones();
    $personas           = new Persona();

    $idUsuarioSession   = htmlspecialchars($_SESSION['id']);

    $idPagActual        = "9";  // Página de bitácora
    
    // *************** INSTANCIA DE LAS FUNCIONES
    $usuario                       = $personas->datosUsuario($idUsuarioSession );
    // $usuarios                   = $funciones->listarUsuarios();
    // $tiposDispositivos          = $funciones->listarTiposDispositivos();
    // $listarOtrosDispositivos    = $funciones->listarOtrosDispositivos();
    // $listarTodosDispositivosqr  = $funciones->listarTodosDispositivosqr();


?>

<!DOCTYPE html>
<html lang="es">
<meta charset="UTF-8">

<head>
    <?php $funciones->header(); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="css/bitacora.css"> <!-- PROPIO DE ESTA PAGINA -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="js/comunes.js"></script> <!-- FUNCIONES COMUNES -->
    <script src="js/equipos.js"></script> <!-- FUNCIONES DE COMPUTADORES -->
    <script src="js/dispositivos.js"></script> <!-- FUNCIONES DE DISPOSITIVOS -->

</head>

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
                <div class="container-fluid">
                    <section class="section profile">
                        <div class="row">
                            <div class="col-xl-4">
                                <div class="card">
                                    <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                                        <?php
                                          switch ($idUsuarioSession) {
                                                        case 28:
                                                            $imagenPerfil = 'img/logo_tabancura.jpeg';
                                                            break;
                                                        case 29:
                                                            $imagenPerfil = 'img/logo_los_andes.png';
                                                            break;
                                                        case 30:
                                                            $imagenPerfil = 'img/logo_huinganal.png';
                                                            break;
                                                        case 31:
                                                            $imagenPerfil = 'img/logo_cordillera.jpeg';
                                                            break;
                                                        case 32:
                                                            $imagenPerfil = 'img/logo_huelen.jpeg';
                                                            break;
                                                        case 33:
                                                            $imagenPerfil = 'img/logo_huinganal.png';
                                                            break;
                                                        default:
                                                            // Si no cumple ninguna condición, usar lógica del sexo
                                                            $imagenPerfil = ($row['sexo'] == 2) ? 'img/undraw_profile_1.svg' : 'img/undraw_profile.svg';
                                                            break;
                                                    }
                                        ?>
                                        <img src="<?php echo $imagenPerfil; ?>" alt="Profile" class=""><br><br>
                                        <h2><?php echo $personas->nombre . " " .  htmlentities($personas->apellido_paterno, ENT_HTML5, "ISO-8859-1"); ?></h2><br>
                                        <h3><?php echo htmlentities($personas->cargo, ENT_HTML5, "ISO-8859-1"); ?></h3>
                                        <!--<h3><?php echo htmlentities($personas->cargo, ENT_HTML5, "ISO-8859-1"); ?></h3>-->

                                        <!--<div class="social-links mt-2">-->
                                        <!--    <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>-->
                                        <!--    <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>-->
                                        <!--    <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>-->
                                        <!--    <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>-->
                                        <!--</div>-->
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-8">
                                <div class="card">
                                    <div class="card-body pt-3">
                                        <!-- Bordered Tabs -->
                                        <ul class="nav nav-tabs nav-tabs-bordered" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" data-bs-toggle="tab"
                                                    data-bs-target="#profile-overview" aria-selected="true"
                                                    role="tab">Datos Personales</button>
                                            </li>
                                            <!--<li class="nav-item" role="presentation">-->
                                            <!--    <button class="nav-link" data-bs-toggle="tab"-->
                                            <!--        data-bs-target="#profile-edit" aria-selected="false" tabindex="-1"-->
                                            <!--        role="tab">Familiares</button>-->
                                            <!--</li>-->
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" data-bs-toggle="tab"
                                                    data-bs-target="#configuracion" aria-selected="false"
                                                    tabindex="-1" role="tab">Configuración</button>
                                            </li>
                                        </ul>
                                        <div class="tab-content pt-2">
                                            <div class="tab-pane fade show active profile-overview"
                                                id="profile-overview" role="tabpanel">
                                                <h5 class="card-title">Datos</h5>
                                                <p class="small fst-italic"><?php echo htmlentities($personas->cargo, ENT_HTML5, "ISO-8859-1"); ?></p>
                                                <h5 class="card-title">Detalle Profesional</h5>
                                                <div class="row">
                                                    <div class="col-lg-3 col-md-4 label ">Nombre Completo</div>
                                                    <div class="col-lg-9 col-md-8">
                                                        <?php
                                                        echo htmlentities($personas->nombre, ENT_HTML5, "ISO-8859-1") . " " .
                                                            htmlentities($personas->apellido_paterno, ENT_HTML5, "ISO-8859-1");
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-3 col-md-4 label">Compañía</div>
                                                    <div class="col-lg-9 col-md-8">Seduc</div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-3 col-md-4 label">Trabajo</div>
                                                    <div class="col-lg-9 col-md-8"><?php echo htmlentities($personas->cargo, ENT_HTML5, "ISO-8859-1"); ?></div>
                                                </div>
                                                    <div class="row">
                                                    <div class="col-lg-3 col-md-4 label">Colegio</div>
                                                    <div class="col-lg-9 col-md-8"><?php echo  htmlentities($personas->apellido_materno, ENT_HTML5, "ISO-8859-1");?></div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-3 col-md-4 label">Teléfono</div>
                                                    <div class="col-lg-9 col-md-8"><?php echo $personas->telefono; ?></div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-3 col-md-4 label">Email</div>
                                                    <div class="col-lg-9 col-md-8"><?php echo $personas->email; ?></div>
                                                </div>
                                            </div>

                                            <!-- *************************** Pestaña Configuración  **************************************************-->
                                            <div class="tab-pane fade pt-3" id="configuracion" role="tabpanel">
                                                <p class="small fst-italic">
                                                    Adjunta un archivo TXT con información del CPU.
                                             
                                                </p>
                                                <!-- Formulario para adjuntar archivo TXT -->
                                                <form id="formCPU" action="class/procesar_txt_cpu.php" method="post" enctype="multipart/form-data">
                                                    <div class="mb-3">
                                                        <!-- <label for="formFile" class="form-label">Adjuntar archivo TXT</label> -->
                                                        <input class="form-control" type="file" id="formFile" name="archivo_txt" accept=".txt" required>
                                                    </div>
                                                    <!-- Enviar el ID del usuario -->
                                                    <input type="hidden" name="id_usuario" value="<?php echo $idUsuarioSession; ?>">
                                                    <button type="button" class="btn btn-primary" onclick="procesarArchivo()">Procesar TXT</button>
                                                    <?php if ($fechaEquipo): ?>
                                                        Última actualización: <?php echo date("d-m-Y", strtotime($fechaEquipo)); ?>.
                                                    <?php endif; ?>
                                                </form>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <a class="scroll-to-top rounded" href="#page-top">
                    <i class="fas fa-angle-up"></i>
                </a>
            </div>
            <?php $funciones->footer(); ?>
        </div>
    </div>
</body>
<?php $funciones->script(); ?>
<?php include("modal_salir.php"); ?>

<script>
    function procesarArchivo() {
        var form = document.getElementById('formCPU');
        var fileInput = document.getElementById('formFile');
        var file = fileInput.files[0];

        // Validar si se ha seleccionado un archivo
        if (!file) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor selecciona un archivo.',
                confirmButtonText: 'OK',
                customClass: {
                            popup: 'cuerpo_modal_eliminar',
                            confirmButton: 'bt_activar_alumno',
                        }
            });
            return;
        }

        // Validar que el archivo sea un .txt
        if (file.type !== 'text/plain') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El archivo debe ser un archivo .txt.',
                confirmButtonText: 'OK',
                customClass: {
                            popup: 'cuerpo_modal_eliminar',
                            confirmButton: 'bt_activar_alumno',
                        }
                
            });
            return;
        }

        // Usamos fetch para enviar el formulario de manera asíncrona
        var formData = new FormData(form);

        fetch('class/procesar_txt_cpu.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar SweetAlert de éxito
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'El archivo ha sido procesado correctamente.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    // Mostrar SweetAlert de error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al procesar el archivo.',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'cuerpo_modal_eliminar'
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Mostrar SweetAlert de error
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Hubo un problema con la solicitud.',
                    confirmButtonText: 'OK'
                });
            });
    }
</script>

</html>
