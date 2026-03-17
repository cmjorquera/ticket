<?php
$id_ticket = $_POST['id_ticket'] ?? "7";

if (!$id_ticket) {
    die("Error: No se recibió el ID del ticket.");
}

echo "ID del Ticket recibido: " . htmlspecialchars($id_ticket);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación del Ticket</title>
    <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
            }

            .email-container {
                max-width: 600px;
                margin: 20px auto;
                background-color: #ffffff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }

            .header {
                text-align: center;
                padding: 10px 0;
            }

            .header img {
                width: 100px;
            }

            .content {
                text-align: center;
                padding: 20px 0;
            }

            .content h1 {
                color: #333333;
            }

            .content p {
                color: #555555;
                line-height: 1.6;
            }

            .footer {
                text-align: center;
                padding: 10px 0;
                color: #aaaaaa;
                font-size: 12px;
            }

            .button {
                display: inline-block;
                padding: 10px 20px;
                margin: 20px auto;
                background-color: #0d6efd;
                color: #ffffff;
                text-decoration: none;
                border-radius: 5px;
                text-align: center;
            }

            .button-container {
                text-align: center;
            }

            .form-group {
                margin: 20px 0;
                text-align: left;
            }

            .form-group label {
                display: flex;
                align-items: center;
                margin-bottom: 10px;
            }

            .form-group label input {
                margin-right: 10px;
            }

            .form-group textarea {
                width: 100%;
                padding: 10px;
                border-radius: 5px;
                border: 1px solid #cccccc;
                resize: vertical;
                display: none;
            }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <img alt="Seduc" src="https://www.siae.cl/email/archivos/1FiVR9m09zWTnZ4IdW1g/header_siae_seduc.png"
                style="width:100%; max-width:600px; border-radius: 10px;" />
        </div>
        <div style="width: 50%; background-color:#3A913F; height: 4px; float: left"></div>
        <div style="width: 25%; background-color:#EAAA00; height: 4px; float: left"></div>
        <div style="width: 25%; background-color:#005587; height: 4px; float: left"></div>
        <div style="clear:both;"></div>
        <div class="content">
            <h1>Validación del Ticket</h1>
            <p>Por favor, valide la resolución del ticket proporcionado por el técnico.</p>
            <form id="validacionForm" action="../../modelos/guardar/procesar_calificacion.php" method="POST"
                onsubmit="return validarFormulario()">
                <input type="hidden" name="id_ticket" value="<?php echo $id_ticket; ?>">
                <div class="form-group">
                    <label><input type="radio" name="calificacion" value="El ticket está bien resuelto"> El ticket está
                        bien resuelto</label>
                    <label><input type="radio" name="calificacion" value="El ticket no está bien resuelto"> El ticket no
                        está bien resuelto</label>
                    <label><input type="radio" name="calificacion" value="El ticket necesita más trabajo"> El ticket
                        necesita más trabajo</label>
                    <label><input type="radio" name="calificacion" value="Otra" onclick="mostrarTextarea()">
                        Otra</label>
                    <textarea name="comentario" id="comentario"
                        placeholder="Por favor, escriba sus comentarios aquí..."></textarea>
                </div>
                <div id="mensajeError" style="color: red; display: none;">Por favor, seleccione una opción o deje un
                    comentario antes de enviar.</div>
                <div class="button-container">
                    <button type="submit" class="button">Enviar Validación</button>
                </div>
            </form>
            <div style="text-align: center;">
            </div>
        </div>


        <div>
            <hr />
            <img alt="pie" src="https://www.siae.cl/email/archivos/IuNYrJlxxZN9U0v254lx/Footer_colegios.png"
                style="max-width:100%; width:600px" />
        </div>
    </div>
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