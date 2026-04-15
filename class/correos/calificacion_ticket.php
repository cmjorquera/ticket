<?php
$id_ticket = $_POST['id_ticket'] ?? $_GET['id_ticket'] ?? "7";

if (!$id_ticket) {
    die("Error: No se recibio el ID del ticket.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validacion del Ticket</title>
    <link rel="stylesheet" href="https://acceso.seduc.cl/css/correos/estilo_correos.css">
</head>
<body>
    <div class="mail-shell">
        <div class="mail-frame">
            <img alt="Seduc" class="mail-header-image" src="https://www.siae.cl/email/archivos/1FiVR9m09zWTnZ4IdW1g/header_siae_seduc.png">
            <div class="mail-stripes"></div>

            <div class="mail-card">
                <div style="text-align:center;">
                    <span class="mail-badge">Validacion de Ticket</span>
                    <h1 class="mail-title">Tu opinion es importante</h1>
                    <div class="mail-divider"></div>
                    <div class="mail-intro">
                        Revisa la resolucion entregada por el tecnico y confirma si la atencion fue correcta. Tu respuesta nos ayuda a cerrar el proceso con claridad.
                    </div>
                </div>

                <div class="mail-alert info">
                    Ticket en revision: <strong>A-0<?php echo htmlspecialchars($id_ticket, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>

                <form id="validacionForm" class="mail-form" action="../../modelos/guardar/procesar_calificacion.php" method="POST" onsubmit="return validarFormulario()">
                    <input type="hidden" name="id_ticket" value="<?php echo htmlspecialchars($id_ticket, ENT_QUOTES, 'UTF-8'); ?>">

                    <label class="mail-option"><input type="radio" name="calificacion" value="El ticket está bien resuelto">El ticket esta bien resuelto</label>
                    <label class="mail-option"><input type="radio" name="calificacion" value="El ticket no está bien resuelto">El ticket no esta bien resuelto</label>
                    <label class="mail-option"><input type="radio" name="calificacion" value="El ticket necesita más trabajo">El ticket necesita mas trabajo</label>
                    <label class="mail-option"><input type="radio" name="calificacion" value="Otra" onclick="mostrarTextarea()">Otra observacion</label>

                    <textarea class="mail-textarea" name="comentario" id="comentario" placeholder="Escribe aqui tus comentarios si deseas entregar mas contexto..." style="display:none;"></textarea>

                    <div id="mensajeError" class="mail-error">Por favor, selecciona una opcion o escribe un comentario antes de enviar la validacion.</div>

                    <div class="mail-cta-wrap">
                        <button type="submit" class="mail-button" style="border:0;cursor:pointer;">Enviar Validacion</button>
                    </div>
                </form>
            </div>

            <img alt="Colegios SEDUC" class="mail-footer-image" src="https://www.siae.cl/email/archivos/IuNYrJlxxZN9U0v254lx/Footer_colegios.png">
        </div>
    </div>
    <script>
    function mostrarTextarea() {
        document.getElementById('comentario').style.display = 'block';
    }

    function validarFormulario() {
        const radios = document.querySelectorAll('input[name="calificacion"]');
        const comentario = document.getElementById('comentario');
        let opcionSeleccionada = false;
        for (let radio of radios) {
            if (radio.checked) {
                opcionSeleccionada = true;
                break;
            }
        }
        if (opcionSeleccionada || comentario.value.trim() !== "") {
            return true;
        } else {
            document.getElementById('mensajeError').style.display = 'block';
            return false;
        }
    }
    </script>
</body>
</html>
