


<?php
$token = $_POST['token'] ?? "";

if (!$token) {
    die("Error: No se recibió el ID del ticket.");
}

echo "ID del Ticket recibido: " . htmlspecialchars($token);
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
            <table style="width:100%;border-spacing:10px 20px;border-radius:20px;background-color:#ffffff">
                <thead>
                    <tr>
                        <th style="text-align:center;padding-bottom:25px" colspan="4">
                            <p style="font-size:22px;margin-bottom:0;margin-top:15px;font-weight:bold">Reinicio de Contraseña</p>
                            <hr style="width:60px;border:2px solid #ccff5f">
                            <p>Hola {nombreUsuario}.{apellidoUsuario}.</p>
                            <p>Has solicitado reiniciar la contraseña de tu cuenta. Si no realizaste esta solicitud, por favor ignora este correo.</p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align:center;width:15%"><img alt="user" width="30px" style="margin-bottom:10px" src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/user.png"></td>
                        <td style="font-size:14px;text-align:left;">
                            <p style="margin:0;text-transform:uppercase;font-weight:bold">Usuario</p>
                            <p style="margin-top:0">{emailUsuario}</p>
                        </td>
                        <td style="text-align:center;width:15%"><img alt="user" width="30px" style="margin-bottom:10px" src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/briefcase.png"></td>
                        <td style="font-size:14px;text-align:left;">
                            <p style="margin:0;text-transform:uppercase;font-weight:bold">Area De Trabajo</p>
                            <p style="margin-top:0">{areaTrabajo}</p>
                            <!-- <p style="margin-top:0">{token}</p> -->

                        </td>
                    </tr>
           
                </tbody>
            </table>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="http://acceso.seduc.cl/reiniciar_clave.php?token={token}" style="display: inline-block; padding: 10px 20px; background-color: #0d6efd; color: #ffffff; text-decoration: none; border-radius: 5px;">Reiniciar Contraseña</a>
                <!-- <a href="http://tickets.local/reiniciar_clave.php?token={token}" style="display: inline-block; padding: 10px 20px; background-color: #0d6efd; color: #ffffff; text-decoration: none; border-radius: 5px;">Reiniciar Contraseña</a> -->
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





















