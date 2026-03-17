<?php
require_once '../../class/conexion.php';
require_once '../../class/funciones.php';

$funciones = new Funciones();

if (isset($_GET['id_dispositivo'])) {
    // Caso: Imprimir un solo código QR para otro dispositivo
    $id_dispositivo = intval($_GET['id_dispositivo']); // Validar y convertir a entero
    $dispositivo = $funciones->obtenerDispositivoPorId($id_dispositivo); // Obtener el dispositivo por ID

    if ($dispositivo) {
        $qrCodePath = $dispositivo['qr_code'];
        $idCompleto = str_pad($dispositivo['id_dispositivo'], 5, '0', STR_PAD_LEFT);
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Imprimir Código QR</title>
            <link rel="stylesheet" href="../../css/codigos_qr.css">
        </head>
        <body onload="window.print()">
            <div class="qr-container">
                <div class="qr-item">
                    <img src="../../<?php echo htmlspecialchars($qrCodePath); ?>" alt="Código QR">
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
    // Caso: Imprimir todos los códigos QR de otros dispositivos
    $dispositivos = $funciones->listarOtrosDispositivos(); // Listar todos los dispositivos

    if (!empty($dispositivos)) {
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
            <h1 style="text-align: center;">Códigos QR de Otros Dispositivos</h1>
            <div class="qr-container">
                <?php foreach ($dispositivos as $dispositivo): ?>
                    <?php
                    $id_dispositivo = $dispositivo['id_dispositivo'];
                    $idCompleto = str_pad($id_dispositivo, 5, '0', STR_PAD_LEFT);
                    ?>
                    <div class="qr-item">
                        <img src="../../<?php echo htmlspecialchars($dispositivo['qr_code']); ?>" alt="Código QR">
                        <p><?php echo "CODIGO_" . $idCompleto; ?></p>
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