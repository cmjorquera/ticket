<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/CursosCodigosQR.php';


// Validar que el ID esté presente en la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID del curso no especificado.');
}

// Obtener y sanitizar el ID del curso
$idCurso = intval($_GET['id']);
$CodigosQR           = new CodigosQR();

// Obtener información del curso
$curso = $CodigosQR->listarCursoQR($idCurso); // 🔹 Se corrigió el nombre de la función

// Validar si el curso fue encontrado
if (!$curso) {
    die('No se encontró un curso con el ID proporcionado.');
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Curso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/leyendo_qr.css">

    <style>
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
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
    </style>
</head>

<body>
    <div class="container">
        <header class="header">
            <h1>Detalles del Curso</h1>
        </header>

        <section class="curso-info">
            <div class="info-grid">
                <!-- Ícono Representativo -->
                <div class="info-item" style="grid-column: span 2; text-align: center;">
                    <i class="bi-journal-bookmark fa-10x"></i>
                    <p style="margin-top: 10px;">Curso QR</p>
                </div>

                <!-- Detalles del Curso -->
                <div class="info-item">
                    <h3>Nombre del Curso</h3>
                    <p><?php echo htmlspecialchars($curso['nombre_taller'] ?? 'N/A'); ?></p>
                </div>

                <div class="info-item">
                    <h3>Fecha del Curso</h3>
                    <p><?php echo htmlspecialchars($curso['fecha_taller'] ?? 'N/A'); ?></p>
                </div>

                <div class="info-item">
                    <h3>Formulario</h3>
                    <p>
                        <a href="<?php echo htmlspecialchars($curso['url_formulario'] ?? '#'); ?>" target="_blank"
                            title="Ir al formulario">
                            <i class="bi bi-box-arrow-up-right" style="font-size: 1.5rem; color: #007bff;"></i>
                        </a>
                    </p>
                </div>


                <div class="info-item">
                    <h3>Generado por</h3>
                    <p><?php echo htmlspecialchars($curso['nombre_usuario'] . ' ' . $curso['apellido_paterno_usuario'] ?? 'Desconocido'); ?>
                    </p>
                </div>

                <div class="info-item">
                    <h3>Fecha de Generación</h3>
                    <p><?php echo htmlspecialchars($curso['fecha_generacion'] ?? 'N/A'); ?></p>
                </div>

                <div class="info-item">
                    <h3>Cantidad de Alumnos</h3>
                    <p><?php echo htmlspecialchars($curso['generado_por'] ?? 'N/A'); ?></p>
                </div>
            </div>
        </section>

        <footer class="footer">
            <button class="btn btn-primary" onclick="volverInicio()">Volver</button>
        </footer>
    </div>

    <script>
    function volverInicio() {
        window.location.href = 'index.php'; // Cambia a tu archivo de inicio
    }
    </script>
</body>

</html>