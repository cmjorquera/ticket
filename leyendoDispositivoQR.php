<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID de dispositivo no especificado.');
}

$id_dispositivo = intval($_GET['id']);
$funciones = new Funciones();

// Obtener datos del dispositivo
$dispositivo = $funciones->obtenerDispositivoPorId($id_dispositivo);

if (!$dispositivo) {
    die('No se encontr�� un dispositivo con el ID proporcionado.');
}

// Obtener el ��cono del dispositivo desde la base de datos
$icono = $dispositivo['icono'] ?? 'bi-box'; // Si no hay un icono, usa el gen��rico
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Dispositivo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/leyendo_qr.css">

</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Detalles del Dispositivo</h1>
        </header>
        <section class="dispositivo-info">
            <div class="info-grid">
                <!-- �0�1cono Representativo -->
                <div class="info-item" style="grid-column: span 2; text-align: center;">
                    <i class="<?php echo htmlspecialchars($icono); ?>"></i>
                    <p style="margin-top: 10px;">Dispositivo</p>
                </div>

                <!-- Detalles del dispositivo -->
                <div class="info-item">
                    <h3>Tipo</h3>
                    <p id="tipo"><?php echo htmlspecialchars($dispositivo['tipo'] ?? 'N/A'); ?></p>
                </div>
                <div class="info-item">
                    <h3>Marca</h3>
                    <p id="marca"><?php echo htmlspecialchars($dispositivo['marca'] ?? 'N/A'); ?></p>
                </div>
                <div class="info-item">
                    <h3>Modelo</h3>
                    <p id="modelo"><?php echo htmlspecialchars($dispositivo['modelo'] ?? 'N/A'); ?></p>
                </div>
                <div class="info-item">
                    <h3>Numero de Serie</h3>
                    <p id="n_serie"><?php echo htmlspecialchars($dispositivo['n_serie'] ?? 'N/A'); ?></p>
                </div>
                
            
                <div class="info-item">
                    <h3>Asignado a</h3>
                    <p id="asignado">
                        <?php
                        if (!empty($dispositivo['usuario_nombre']) && !empty($dispositivo['usuario_apellido'])) {
                            echo htmlspecialchars($dispositivo['usuario_nombre'] . ' ' . $dispositivo['usuario_apellido']);
                        } else {
                            echo 'No asignado';
                        }
                        ?>
                    </p>
                </div>
          
        
            </div>
        </section>
        <footer class="footer">
            <button class="btn btn-primary" onclick="iniciarSesion()">Iniciar Sesion</button>
        </footer>
    </div>

    <script>
        function iniciarSesion() {
            window.location.href = 'index.php'; // Cambia a tu archivo de inicio de sesi��n
        }
    </script>
</body>
</html>
