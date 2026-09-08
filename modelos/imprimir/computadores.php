<?php
require_once '../../class/conexion.php';
require_once '../../class/funciones.php';

$funciones = new Funciones();



if (isset($_GET['id_dispositivo'])) {
    // Caso: Imprimir un solo código QR
    $id_dispositivo = intval($_GET['id_dispositivo']); // Cambié a id_dispositivo
    $computador = $funciones->obtenerEquipoPorId($id_dispositivo); // Obtener un solo equipo

    if ($computador) {
        $qrCodePath = $computador['qr_code'];
        $idCompleto = str_pad($computador['id_equipo'], 5, '0', STR_PAD_LEFT); // Cambié a id_equipo
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <h1 style="text-align: center;">Códigos QR Computador</h1>
            <link rel="stylesheet" href="../../css/codigos_qr.css">
        </head>
        <body onload="window.print()">
            <div class="qr-container">
                <div class="qr-item">
                    <img src="<?php echo htmlspecialchars($qrCodePath); ?>" alt="Código QR">
                    <p><?php echo "" . $idCompleto; ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "Dispositivo no encontrado.";
    }
} else {
    // Caso: Imprimir todos los códigos QR
    $computadores = $funciones->listarEquipos(); // Listar todos los equipos

    if (!empty($computadores)) {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Imprimir Códigos QR</title>
            <link rel="stylesheet" href="../../css/codigos_qr.css">
        </head>
        <body onload="window.print()">
            <h1 style="text-align: center;">Códigos QR Computador</h1>
                <div class="qr-container">
                    <?php foreach ($computadores as $computador): ?>
                        <?php
                        $id_equipo = $computador['id_equipo']; // Cambié a id_equipo
                        $idCompleto = str_pad($id_equipo, 5, '0', STR_PAD_LEFT);
                        ?>
                        <div class="qr-item-container">
                            <div class="qr-item">
                                <img src="<?php echo htmlspecialchars($computador['qr_code']); ?>" alt="Código QR">
                            </div>
                            <p class="qr-name"><?php echo $idCompleto; ?></p>
                            <p class="qr-name"><?php echo "*" . $computador['nombre'] . "-" . $computador['apellido_paterno'] . "*"; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
        </body>
        </html>
        <?php
    } else {
        echo "No hay dispositivos disponibles para imprimir.";
    }
}

?>
