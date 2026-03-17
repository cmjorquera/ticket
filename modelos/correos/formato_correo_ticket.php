<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen Ticket</title>
</head>

<body>
    <div style="width: 600px; margin: 0 auto;">
        <div style="width: 600px;"><img alt="Seduc"
                src="https://www.siae.cl/email/archivos/1FiVR9m09zWTnZ4IdW1g/header_siae_seduc.png"
                style="width:600px" /></div>

        <div style="width: 50%;background-color:#3A913F;height: 4px; float: left">&nbsp;</div>
        <div style="width: 25%;background-color:#EAAA00;height: 4px; float: left">&nbsp;</div>
        <div style="width: 25%;background-color:#005587;height: 4px; float: left">&nbsp;</div>

        <div style="padding: 5px; margin-top: 5px; font-family:arial,helvetica,sans-serif; color:#474d58; font-size:14px">
            <table style="width:100%;border-spacing:10px 20px;border-radius:20px;background-color:#ffffff">
                <thead>
                    <tr>
                        <th style="text-align:center;padding-bottom:25px" colspan="4">
                            <p>Hola <strong>{nombreUsuario}</strong>,</p>
                            <hr style="width:60px;border:2px solid #ccff5f">
                            <p>Te enviamos una copia de tu ticket registrado:</p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align:center;width:15%"><img alt="subject" width="30px" style="margin-bottom:10px" src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/briefcase.png"></td>
                        <td style="font-size:14px;text-align:left;">
                            <p style="margin:0;text-transform:uppercase;font-weight:bold">Asunto</p>
                            <p style="margin-top:0">{asunto}</p>
                        </td>
                        <td style="text-align:center;width:15%"><img alt="description" width="30px" style="margin-bottom:10px" src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/job-description.png"></td>
                        <td style="font-size:14px;text-align:left;">
                            <p style="margin:0;text-transform:uppercase;font-weight:bold">Descripción</p>
                            <p style="margin-top:0">{descripcion}</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p style="text-align: center; margin-top: 30px;">Adjunto encontrarás el PDF con los detalles.</p>
            <p style="text-align: center; font-size: 12px; color: #777;">Sistema Ticket</p>

            <!--<div style="text-align: center;">-->
            <!--    <a href="https://www.acceso.seduc.cl/" style="display: inline-block; padding: 10px 10px; margin: 10px auto; background-color: #0d6efd; color: #ffffff; text-decoration: none; border-radius: 5px; text-align: center; color:black;">Ir al Sistema</a>-->
            <!--</div>-->
        </div>

        <div>
            <hr />
            <img alt="pie" src="https://www.siae.cl/email/archivos/IuNYrJlxxZN9U0v254lx/Footer_colegios.png" style="max-width:100%; width:600px" />
        </div>
    </div>
</body>

</html>