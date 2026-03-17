

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keyword" content="" />
    <meta name="author" content="maryinparis" />
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>SeduCentro</title>
    <!--! END:  Apps Title-->
    <!--! BEGIN: Favicon-->
              <link rel="icon" type="image/x-icon" href="imagenes/logo_seduc.png" />
    <!--! END: Favicon-->
    <!--! BEGIN: Bootstrap CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css" />
    <!--! END: Bootstrap CSS-->
    <!--! BEGIN: Vendors CSS-->
    <link rel="stylesheet" type="text/css" href="assets/vendors/css/vendors.min.css" />
    <link rel="stylesheet" type="text/css" href="assets/vendors/css/daterangepicker.min.css" />
	
    <link rel="stylesheet" type="text/css" href="assets/vendors/css/jquery-jvectormap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/vendors/css/select2.min.css">
    <link rel="stylesheet" type="text/css" href="assets/vendors/css/select2-theme.min.css">
    <link rel="stylesheet" type="text/css" href="assets/vendors/css/jquery.time-to.min.css">	
	
    <link rel="stylesheet" type="text/css" href="assets/vendors/css/tagify.min.css">
    <link rel="stylesheet" type="text/css" href="assets/vendors/css/tagify-data.min.css">
    <link rel="stylesheet" type="text/css" href="assets/vendors/css/quill.min.css">

    <link type="text/css" rel="stylesheet" href="assets/vendors/css/tui-calendar.min.css">
    <link type="text/css" rel="stylesheet" href="assets/vendors/css/tui-theme.min.css">
    <link type="text/css" rel="stylesheet" href="assets/vendors/css/tui-time-picker.min.css">
    <link type="text/css" rel="stylesheet" href="assets/vendors/css/tui-date-picker.min.css">

	<link type="text/css" rel="stylesheet" href="assets/vendors/css/emojionearea.min.css">	

	<link rel="stylesheet" type="text/css" href="assets/vendors/css/jquery.time-to.min.css">
	
	<link rel="stylesheet" type="text/css" href="assets/vendors/css/dataTables.bs5.min.css">	
    <!--! END: Vendors CSS-->
    <!--! BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/theme.min.css" />
    <!--! END: Custom CSS-->
    <!--! HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries !-->
    <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
    <!--[if lt IE 9]>
			<script src="https:oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https:oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
    <?php echo (isset($css) ? $css   : '')?>
</head>





<!DOCTYPE html>
<html lang="zxx">
<!--<?php include './partials/head.php'?>-->
    <link rel="stylesheet" type="text/css" href="assets/vendors/css/select2-theme.min.css">

<body>
    <!--! ================================================================ !-->
    <!--! [Inicio] Contenido Principal !-->
    <!--! ================================================================ !-->
    <main class="auth-cover-wrapper">
        <div class="auth-cover-content-inner">
            <div class="auth-cover-content-wrapper">
                <div class="auth-img" style="text-align:left;width: 1142px;">
    <img src="assets/images/logo_seduc_laugth.png" alt="" class="img-fluid" style="">
</div>

            </div>
        </div>
        <div class="auth-cover-sidebar-inner">
            <div class="auth-cover-card-wrapper">
                <div class="auth-cover-card p-sm-5">
                    <div class="wd-50 mb-5">
                        <img src="imagenes/logo_seduc.png" alt="" class="img-fluid">
                    </div>
                    <h2 class="fs-20 fw-bolder mb-4">Iniciar Sesión</h2>
                    <h4 class="fs-13 fw-bold mb-2">Ingresa a tu cuenta</h4>
                        <p class="fs-12 fw-medium text-muted">
                          Bienvenido a <strong>SeduCentro</strong>, el punto de acceso unificado a tus principales herramientas institucionales. Gestiona, consulta y accede rápidamente a todo lo que necesitas en un solo lugar.
                        </p>
                    <form action="layout.php" class="w-100 mt-4 pt-2">
                        <div class="mb-4">
                            <input type="email" class="form-control" placeholder="Correo o Nombre de usuario" value="wrapcode.info@gmail.com" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control" placeholder="Contraseña" value="123456" required>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="rememberMe">
                                    <label class="custom-control-label c-pointer" for="rememberMe">Recordarme</label>
                                </div>
                            </div>
                            <div>
                                <a href="auth-reset-cover.php" class="fs-11 text-primary">¿Olvidaste tu contraseña?</a>
                            </div>
                        </div>
                        <div class="mt-5">
                            <button type="submit" class="btn btn-lg btn-primary w-100">Iniciar Sesión</button>
                        </div>
                    </form>
                    <div class="w-100 mt-5 text-center mx-auto">
                        <div class="mb-4 border-bottom position-relative">
                            <span class="small py-1 px-3 text-uppercase text-muted bg-white position-absolute translate-middle">o</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center gap-2">
                         <a href="https://www.seduc.cl" target="_blank" class="btn btn-light-brand flex-fill" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Ir al sitio web de SEDUC">
  <i class="fas fa-globe"></i>
</a>

                         <a href="https://www.instagram.com/seduc_chile/?hl=es" target="_blank" class="btn btn-light-brand flex-fill" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Visítanos en Instagram">
                                  <i class="fab fa-instagram"></i>
                                </a>

                        <a href="mailto:cjorqueraseduc.cl" class="btn btn-light-brand flex-fill" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Enviar correo a cjorqueraseduc.cl">
  <i class="fas fa-envelope"></i>
</a>

                        </div>
                    </div>
                    <div class="mt-5 text-muted">
                        <span>¿No tienes una cuenta?</span>
                        <a href="auth-register-cover.php" class="fw-bold">Crear una cuenta</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!--! ================================================================ !-->
    <!--! [Fin] Contenido Principal !-->
    <!--! ================================================================ !-->
	<!--<< Inicio de Sección Footer >>-->
	<?php include './partials/theme-customizer.php'?>
	<!--<< Todos los Plugins JS >>-->
	<?php include './partials/script.php'?>		
</body>

</html>