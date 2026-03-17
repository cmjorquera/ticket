
<?php
require_once 'class/conexion.php';
require_once 'class/funciones.php';

$bd = new MySQL('', '', '');
$fun = new Funciones();
session_start();

$_SESSION['id'] = 1;
$filtro = isset($_GET['buscar']) ? $_GET['buscar'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SeduCentro</title>
        <link rel="icon" type="image/x-icon" href="imagenes/logo_seduc.png" />
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    * { box-sizing: border-box; }

    body {
      margin: 0;
        padding: 60px 30px; /* espacio superior e inferior, y a los lados */
      font-family: Arial, sans-serif;
      background-color: #F5F7FA;
      color: #fff;
    }

    .contenedor { padding: 120px; }

    .buscador {
      width: 100%;
      max-width: 600px;
      margin: 0 auto;
      
    }

    .buscador input {
      width: 100%;
      padding: 12px 20px;
      border-radius: 8px;
      border: color:black;
      font-size: 16px;
    }

    .grid-accesos {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
      gap: 15px;
      padding: 20px 10px;
    }

.acceso {
  background-color: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  text-align: center;
  color: #2c3e50;
  transition: transform 0.2s, box-shadow 0.2s;
}

.acceso:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}

 

    .barra-derecha {
      position: fixed;
      top: 50%;
      right: 10px;
      transform: translateY(-50%);
      display: flex;
      flex-direction: column;
      gap: 15px;
      z-index: 1000;
    }

    .boton {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      border: none;
      color: white;
      font-size: 18px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.3);
      transition: transform 0.2s;
    }

    .boton:hover { transform: scale(1.1); }

    #pantallaBloqueo {
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background-color: rgba(0,0,0,0.95);
      color: white;
      z-index: 9999;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .bloqueo-modal {
      text-align: center;
      background-color: #333;
      padding: 30px;
      border-radius: 10px;
    }

    .bloqueo-modal input {
      padding: 10px;
      font-size: 18px;
      margin-top: 10px;
    }

    .red     { background-color: #cc2e2e; }
    .red2    { background-color: #e74c3c; }
    .dark    { background-color: #111; }
    .white   { background-color: #fff; color: #333; }
    .blue    { background-color: #00bfff; }
    .blue2   { background-color: #007bff; }
    .yellow  { background-color: #f1c40f; color: #000; }
    .green   { background-color: #2ecc71; }

    @media (max-width: 600px) {
      .grid-accesos {
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
      }
    }
  </style>
</head>
<body>

  <!-- Botón flotante para mostrar/ocultar buscador -->
  <!--<div class="boton white" style="position: fixed; top: 20px; left: 20px; z-index: 1001;" onclick="toggleBuscador()" title="Buscar">-->
  <!--  <i class="fas fa-search"></i>-->
  <!--</div>-->

  <div class="contenedor">
    <!-- Buscador oculto -->
    <div id="contenedorBuscador" class="buscador" >
      <input type="text" id="buscador" placeholder="Buscar accesos..." onkeyup="filtrarAccesos()">
    </div>

    <!-- Accesos dinámicos -->
    <div class="grid-accesos" id="contenedores">
      <?php $fun->contenedoresUsuario($_SESSION['id'], $filtro); ?>
    </div>
  </div>

  <!-- Modal: Agregar Contenedor -->
  <div class="modal fade" id="modalAgregarContenedor" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-dark text-white">
        <div class="modal-header border-0">
          <h5 class="modal-title" id="modalAgregarLabel"><i class="fas fa-plus-circle me-2"></i>Agregar Contenedor</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formAgregarContenedor">
            <div class="mb-3">
              <label for="tituloContenedor" class="form-label">Título</label>
              <input type="text" class="form-control" id="tituloContenedor" required>
            </div>
            <div class="mb-3">
              <label for="iconoContenedor" class="form-label">Ícono (ej: <code>fab fa-google</code>)</label>
              <input type="text" class="form-control" id="iconoContenedor" value="fas fa-box fa-2x">
            </div>
          </form>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" onclick="guardarContenedor()">Guardar</button>
        </div>
      </div>
    </div>
  </div>






















  <!-- Barra lateral derecha -->
  <div class="barra-derecha">
    <button class="boton yellow" title="Agregar Contenedor" data-bs-toggle="modal" data-bs-target="#modalAgregarContenedor">
      <i class="fas fa-plus"></i>
    </button>
    <button class="boton red2" title="Ticket"><i class="fas fa-ticket-alt"></i></button>
    <button class="boton blue" title="Chat"><i class="fas fa-comments"></i></button>
    <button class="boton green" title="Calendario"><i class="fas fa-calendar-alt"></i></button>
<button class="boton white" title="Bloquear" onclick="window.location.href='https://acceso.seduc.cl/seduc.php'">
  <i class="fas fa-lock"></i>
</button>
  </div>

  <!-- Pantalla de bloqueo -->
  <div id="pantallaBloqueo" style="display: none;">
    <div class="bloqueo-modal">
      <h2>🔒 Pantalla bloqueada</h2>
      <input type="password" placeholder="Ingresa tu clave" id="claveDesbloqueo">
      <button onclick="desbloquear()">Desbloquear</button>
    </div>
  </div>

<script>
  function desbloquear() {
    const clave = document.getElementById('claveDesbloqueo').value;
    if (clave === "1234") {
      document.getElementById('pantallaBloqueo').style.display = 'none';
    } else {
      alert("Clave incorrecta");
    }
  }

  function filtrarAccesos() {
    const filtro = document.getElementById('buscador').value.toLowerCase();
    document.querySelectorAll('.acceso').forEach(function(acceso) {
      const titulo = acceso.getAttribute('data-titulo').toLowerCase();
      acceso.style.display = titulo.includes(filtro) ? '' : 'none';
    });
  }

//   function toggleBuscador() {
//     const buscador = document.getElementById('contenedorBuscador');
//     buscador.style.display = (buscador.style.display === "none" || buscador.style.display === "") ? "block" : "none";
//   }

  function guardarContenedor() {
    // Aquí se enviaría el formulario a un PHP por AJAX
    alert("🚧 Aquí se implementará guardarContenedor()");
  }

  document.querySelector('.fa-lock').addEventListener('click', function() {
    document.getElementById('pantallaBloqueo').style.display = 'flex';
  });
</script>

</body>
</html>
