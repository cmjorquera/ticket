<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificación del Ticket</title>
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
            background-color: #f4f4f4;
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
            display: none; /* Ocultar inicialmente */
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/ticket.jpeg" alt="Logo de la Empresa">
        </div>
        <div class="content">
            <h1>Califique la Calidad del Técnico</h1>
            <p>Por favor, califique la calidad del servicio proporcionado por el técnico.</p>
            <form id="calificacionForm" action="modelos/guardar/procesar_calificacion.php" method="POST" onsubmit="return validarFormulario()">
                <div class="form-group">
                    <input type="hidden" name="id_ticket" value="{identificador}">
                    <label>
                        <input type="radio" name="calificacion" value="El ticket cumplió mis expectativas"> El ticket cumplió mis expectativas
                    </label>
                    <label>
                        <input type="radio" name="calificacion" value="El ticket no cumplió mis expectativas"> El ticket no cumplió mis expectativas
                    </label>
                    <label>
                        <input type="radio" name="calificacion" value="El ticket superó mis expectativas"> El ticket superó mis expectativas
                    </label>
                    <label>
                        <input type="radio" name="calificacion" value="Otra" onclick="mostrarTextarea()"> Otra
                    </label>
                    <textarea name="comentario" id="comentario" placeholder="Por favor, escriba sus comentarios aquí..."></textarea>
                </div>
                <div id="mensajeError" style="color: red; display: none;">Si ya estás aquí, ayúdanos a mejorar la calidad del servicio.</div>
                <div class="button-container">
                    <button type="submit" class="button">Enviar Calificación</button>
                </div>
            </form>
        </div>
        <div class="footer">
            <p>Este es un correo automático, por favor no responda a este mensaje.</p>
            <p>&copy; 2024 SeducSPA. Todos los derechos reservados.</p>
        </div>
    </div>
    <script>
        function mostrarTextarea() {
            document.getElementById('comentario').style.display = 'block';
        }

        function validarFormulario() {
            const form = document.getElementById('calificacionForm');
            const radios = form.elements['calificacion'];
            const comentario = document.getElementById('comentario');
            let opcionSeleccionada = false;

            for (let i = 0; i < radios.length; i++) {
                if (radios[i].checked) {
                    opcionSeleccionada = true;
                    break;
                }
            }

            if (opcionSeleccionada || comentario.value.trim() !== "") {
                return true; // Permite enviar el formulario
            } else {
                document.getElementById('mensajeError').style.display = 'block';
                return false; // Impide enviar el formulario
            }
        }
    </script>
</body>
</html>
