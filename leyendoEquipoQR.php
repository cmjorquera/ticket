<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';

// Validar que el ID esté presente en la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID de dispositivo no especificado.');
}

// Obtener y sanitizar el ID del dispositivo
$id_dispositivo = intval($_GET['id']); // Convertir el ID a entero para mayor seguridad
$funciones = new Funciones();

// Llamar a la función obtenerEquipoPorId
$equipo = $funciones->obtenerEquipoPorId($id_dispositivo);

// Validar si el equipo fue encontrado
if (!$equipo) {
    die('No se encontró un dispositivo con el ID proporcionado.');
}

// Determinar el icono según el tipo de PC
$tipo_pc = $equipo['tipo_pc'] ?? '';
$icono = ($tipo_pc === 'Notebook') ? 'bi-laptop' : 'bi-pc'; // Ícono para Notebook o PC normal
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Dispositivo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="css/leyendo_qr.css">

    <style>
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* 2 columnas */
            gap: 20px;
        }

        .info-item {
            text-align: left;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
        }

        .icon-container {
            text-align: center;
            margin: 20px 0;
        }

        .icon-container i {
            font-size: 64px;
            color: #007bff;
        }
        /* Estilo para el número como notificación circular */
.info-item {
    position: relative;
    display: inline-block;
    text-align: center;
}

.badge-notification {
    position: absolute;
    top: 10%; /* Ajusta para moverlo verticalmente */
    transform: translateY(-50%); /* Centrarlo verticalmente */
    left: 150px; /* Ajusta para posicionarlo horizontalmente */
    background-color: red;
    color: white;
    font-size: 12px;
    font-weight: bold;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
}



    </style>
</head>

<body>
    <div class="container">
        <header class="header">
            <h1>Detalles del Computador</h1>
        </header>
        <section class="dispositivo-info">
            <div class="info-grid">
                <!-- Ícono Representativo -->
        <div class="info-item" style="grid-column: span 2; text-align: center;">
    <i class="<?php echo htmlspecialchars($icono); ?> fa-10x"></i>
    <p style="margin-top: 10px;"><?php echo htmlspecialchars($tipo_pc); ?></p>
</div>


                <!-- Detalles del dispositivo -->
                     <div class="info-item">
                    <h3>Tipo  Equipo</h3>
                    <p id="nombre_equipo"><?php echo htmlspecialchars($equipo['tipo_pc'] ?? 'N/A'); ?></p>
                </div>
                      <div class="info-item">
                    <h3>Marca</h3>
                    <p id="nombre_equipo"><?php echo htmlspecialchars($equipo['fabricante'] ?? 'N/A'); ?></p>
                </div>
                
                        <div class="info-item">
                    <h3>Modelo</h3>
                    <p id="nombre_equipo"><?php echo htmlspecialchars($equipo['producto'] ?? 'N/A'); ?></p>
                </div>
                           <div class="info-item">
                    <h3>Procesador</h3>
                    <p id="nombre_equipo"><?php echo htmlspecialchars($equipo['modelo_procesador'] ?? 'N/A'); ?></p>
                </div>
                
                
                <div class="info-item">
                <h3>
        RAM 
        <span class="badge-notification">4</span>
    </h3>
                        <p id="nombre_equipo">
                            <?php 
                                echo isset($equipo['total_tamano_memoria']) 
                                    ? htmlspecialchars($equipo['total_tamano_memoria']) . ' GB' 
                                    : 'N/A'; 
                            ?>
                        </p>
                </div>
                          <div class="info-item">
                    <h3>Disco</h3>
                <p id="nombre_equipo">
                    <?php 
                        echo isset($equipo['capacidad_almacenamiento']) 
                            ? htmlspecialchars($equipo['capacidad_almacenamiento']) . ' GB' 
                            : 'N/A'; 
                    ?>
                </p>
                </div>
                
                               <div class="info-item">
                    <h3>Windows</h3>
                    <p id="nombre_equipo"><?php echo htmlspecialchars($equipo['windows'] ?? 'N/A'); ?></p>
                </div>
                
           
        
         
                <div class="info-item">
                    <h3>Usuario Asignado</h3>
                    <p id="asignado"><?php echo htmlspecialchars($equipo['nombre_completo_usuario'] ?? 'No asignado'); ?></p>
                </div>
           
            </div>
        </section>
        <footer class="footer">
            <button class="btn btn-primary" onclick="iniciarSesion()">Iniciar Sesión</button>
        </footer>
    </div>

    <script>
        function iniciarSesion() {
            window.location.href = 'index.php'; // Cambia a tu archivo de inicio de sesión
        }
    </script>
</body>

</html>
