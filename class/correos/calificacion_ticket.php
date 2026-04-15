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
    <style>
body {
    margin: 0;
    padding: 0;
    background-color: #dfe5ef;
    font-family: Arial, Helvetica, sans-serif;
    color: #334155;
}

a {
    color: #0f4c81;
}

.mail-shell {
    width: 100%;
    background-color: #dfe5ef;
    padding: 18px 0;
}

.mail-frame {
    width: 760px;
    max-width: 760px;
    margin: 0 auto;
}

.mail-header-image,
.mail-footer-image {
    width: 100%;
    display: block;
    border: 0;
}

.mail-header-image {
    border-radius: 22px 22px 0 0;
}

.mail-footer-image {
    border-radius: 0 0 22px 22px;
}

.mail-stripes {
    width: 100%;
    height: 5px;
    background: linear-gradient(90deg, #3a913f 0 50%, #eaaa00 50% 75%, #005587 75% 100%);
}

.mail-card {
    background: #f7f9fc;
    border-radius: 0 0 22px 22px;
    padding: 18px 22px 22px;
    box-sizing: border-box;
}

.mail-badge {
    display: inline-block;
    margin-bottom: 14px;
    padding: 7px 18px;
    border-radius: 999px;
    border: 1px solid #c9d8ee;
    background: #ffffff;
    color: #0f4c81;
    font-size: 11px;
    font-weight: bold;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.mail-title {
    margin: 0;
    color: #1f3f73;
    font-size: 31px;
    line-height: 1.18;
    font-weight: bold;
}

.mail-divider {
    width: 74px;
    height: 4px;
    margin: 14px auto 18px;
    border-radius: 999px;
    background: #eaaa00;
}

.mail-intro {
    margin: 0 auto 18px;
    padding: 18px 20px;
    border: 1px solid #d9e2ef;
    border-radius: 18px;
    background: linear-gradient(135deg, #edf6ee 0%, #eef4ff 60%, #fff8eb 100%);
    color: #334155;
    font-size: 15px;
    line-height: 1.8;
}

.mail-section {
    margin-top: 18px;
}

.mail-subtitle {
    margin: 0 0 10px;
    color: #1e3a5f;
    font-size: 18px;
    line-height: 1.4;
    font-weight: bold;
}

.mail-text {
    margin: 0 0 12px;
    color: #475569;
    font-size: 15px;
    line-height: 1.8;
}

.mail-grid {
    width: 100%;
    border-collapse: separate;
    border-spacing: 12px;
}

.mail-grid td {
    vertical-align: top;
}

.mail-panel {
    background: #ffffff;
    border: 1px solid #d8e2ef;
    border-radius: 16px;
    padding: 16px 18px;
    box-sizing: border-box;
}

.mail-panel-accent-green {
    border-top: 4px solid #3a913f;
}

.mail-panel-accent-gold {
    border-top: 4px solid #eaaa00;
}

.mail-panel-accent-blue {
    border-top: 4px solid #005587;
}

.mail-panel-title {
    margin: 0 0 8px;
    color: #334155;
    font-size: 12px;
    font-weight: bold;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.mail-panel-title.green {
    color: #2f7a34;
}

.mail-panel-title.gold {
    color: #a97800;
}

.mail-panel-title.blue {
    color: #005587;
}

.mail-panel-text {
    margin: 0;
    color: #475569;
    font-size: 14px;
    line-height: 1.75;
}

.mail-highlight {
    background: #ffffff;
    border: 1px solid #d8e2ef;
    border-left: 5px solid #005587;
    border-radius: 16px;
    padding: 16px 18px;
}

.mail-highlight.gold {
    border-left-color: #eaaa00;
}

.mail-highlight.green {
    border-left-color: #3a913f;
}

.mail-chip {
    display: inline-block;
    padding: 10px 22px;
    border-radius: 999px;
    background: #1f2c4b;
    color: #ffffff;
    font-size: 14px;
    font-weight: normal;
}

.mail-chip strong {
    color: #d7f57d;
}

.mail-data {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
}

.mail-data-label {
    width: 180px;
    padding-right: 12px;
    color: #64748b;
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    vertical-align: top;
}

.mail-data-value {
    color: #334155;
    font-size: 14px;
    line-height: 1.7;
}

.mail-cta-wrap {
    margin-top: 18px;
    padding: 18px 20px;
    border: 1px solid #d9e2ef;
    border-radius: 18px;
    background: linear-gradient(135deg, #edf6ee 0%, #eef4ff 60%, #fff8eb 100%);
    text-align: center;
}

.mail-button {
    display: inline-block;
    padding: 12px 28px;
    border-radius: 10px;
    background: #1f2c4b;
    color: #ffffff;
    text-decoration: none;
    font-size: 15px;
    font-weight: bold;
}

.mail-link {
    color: #2c63b8;
    text-decoration: none;
    word-break: break-word;
}

.mail-note {
    margin-top: 14px;
    color: #64748b;
    font-size: 13px;
    line-height: 1.7;
}

.mail-alert {
    padding: 16px 18px;
    border-radius: 16px;
    border: 1px solid #d8e2ef;
    background: #fffaf0;
    color: #5b4a1f;
    font-size: 14px;
    line-height: 1.75;
}

.mail-alert.info {
    background: #f0f7ff;
    color: #1f4b74;
}

.mail-alert.success {
    background: #f1f8f1;
    color: #2f6c35;
}

.mail-status {
    display: inline-block;
    padding: 9px 18px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.mail-status.success {
    background: #edf8ee;
    color: #2f7a34;
}

.mail-status.warning {
    background: #fff6df;
    color: #9a6a00;
}

.mail-status.info {
    background: #edf4ff;
    color: #0f4c81;
}

.mail-status.dark {
    background: #1f2c4b;
    color: #ffffff;
}

.mail-timeline {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 12px;
}

.mail-timeline-item td {
    padding: 14px 16px;
    border: 1px solid #d9e2ef;
    border-radius: 16px;
    background: #ffffff;
}

.mail-timeline-step {
    width: 150px;
    color: #1f3f73;
    font-size: 14px;
    font-weight: bold;
}

.mail-timeline-date {
    color: #475569;
    font-size: 14px;
    line-height: 1.6;
}

.mail-form {
    margin-top: 18px;
    text-align: left;
}

.mail-option {
    display: block;
    margin-bottom: 10px;
    padding: 12px 14px;
    border: 1px solid #d7e0ed;
    border-radius: 12px;
    background: #ffffff;
    color: #334155;
    font-size: 14px;
}

.mail-option input {
    margin-right: 10px;
}

.mail-textarea {
    width: 100%;
    min-height: 120px;
    margin-top: 10px;
    padding: 12px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    box-sizing: border-box;
    resize: vertical;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;
    color: #334155;
}

.mail-error {
    display: none;
    margin-top: 10px;
    color: #b42318;
    font-size: 13px;
    line-height: 1.6;
}

@media only screen and (max-width: 780px) {
    .mail-frame {
        width: 100% !important;
        max-width: 100% !important;
    }

    .mail-card {
        padding: 16px !important;
    }

    .mail-grid,
    .mail-grid tbody,
    .mail-grid tr,
    .mail-grid td,
    .mail-data,
    .mail-data tbody,
    .mail-data tr,
    .mail-data td,
    .mail-timeline,
    .mail-timeline tbody,
    .mail-timeline tr,
    .mail-timeline td {
        display: block !important;
        width: 100% !important;
        box-sizing: border-box;
    }

    .mail-grid {
        border-spacing: 0 !important;
    }

    .mail-grid td {
        padding-bottom: 12px !important;
    }

    .mail-data-label {
        padding-bottom: 6px !important;
    }

    .mail-title {
        font-size: 26px !important;
    }
}

</style>
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
