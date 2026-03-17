<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <div style="width: 600px; margin: 0 auto;">
        <div style="width: 600px;"><img alt="Seduc"
                src="https://www.siae.cl/email/archivos/1FiVR9m09zWTnZ4IdW1g/header_siae_seduc.png"
                style="width:600px" /></div>

        <div style="width: 50%;background-color:#3A913F;height: 4px; float: left">&nbsp;</div>
        <div style="width: 25%;background-color:#EAAA00;height: 4px; float: left">&nbsp;</div>
        <div style="width: 25%;background-color:#005587;height: 4px; float: left">&nbsp;</div>


        <div
            style="padding: 5px; margin-top: 5px; font-family:arial,helvetica,sans-serif; color:#474d58; font-size:14px">
            <table style="width:100%;border-spacing:10px 20px;border-radius:20px;background-color:#ffffff">
                <thead>
                    <tr>
                        <th style="text-align:center;padding-bottom:25px" colspan="4">
                            <p style="font-size:22px;margin-bottom:0;margin-top:15px;font-weight:bold">¡Su Ticket está
                                en Proceso!</p>
                            <hr style="width:60px;border:2px solid #ccff5f">
                            <p>Nos complace informarle que su ticket ha sido asignado a un técnico y está en proceso de
                                Resolución.</p><br><br>
                            <span
                                style="background:#1f2c4b;padding:10px 30px;border-radius:20px;color:#ccff5f;font-size:15px;text-transform:uppercase;font-weight:normal">N°
                                de Ticket: <span style="font-weight:bold">A-0{codigo}</span></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align:center;width:15%"><img alt="date" width="30px" style="margin-bottom:10px"
                                src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/clock.png"></td>
                        <td style="font-size:14px;text-align:left;">
                            <p style="margin:0;text-transform:uppercase;font-weight:bold">Hora de Ingreso</p>
                            <p style="margin-top:0">{hora}</p>
                        </td>
                        <td style="text-align:center;width:15%"><img alt="technician" width="30px"
                                style="margin-bottom:10px"
                                src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/user.png"></td>
                        <td style="font-size:14px;text-align:left;">
                            <p style="margin:0;text-transform:uppercase;font-weight:bold">Técnico Asignado</p>
                            <p style="margin-top:0">{nombre_tecnico}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:center;width:15%"><img alt="subject" width="30px"
                                style="margin-bottom:10px"
                                src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/briefcase.png"></td>
                        <td style="font-size:14px;text-align:left;">
                            <p style="margin:0;text-transform:uppercase;font-weight:bold">Asunto</p>
                            <p style="margin-top:0">{asunto}</p>
                        </td>
                        <td style="text-align:center;width:15%"><img alt="description" width="30px"
                                style="margin-bottom:10px"
                                src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/job-description.png"></td>
                        <td style="font-size:14px;text-align:left;">
                            <p style="margin:0;text-transform:uppercase;font-weight:bold">Usuario</p>
                            <p style="margin-top:0">{nombreUsarioCompleto}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:center;width:15%"><img alt="subject" width="30px"
                                style="margin-bottom:10px"
                                src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/briefcase.png"></td>
                        <td style="font-size:14px;text-align:left;">
                            <p style="margin:0;text-transform:uppercase;font-weight:bold">Fecha Resolución</p>
                            <p style="margin-top:0">{fecha_resolucion}</p>
                        </td>
                        <td style="text-align:center;width:15%"><img alt="description" width="30px"
                                style="margin-bottom:10px"
                                src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/job-description.png"></td>
                        <td style="font-size:14px;text-align:left;">
                            <p style="margin:0;text-transform:uppercase;font-weight:bold">Días De Resolución</p>
                            <p style="margin-top:0;color:red;"><strong>{dias_administrador_estima}</strong></p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div style="text-align: center;">
                <a href="https://www.acceso.seduc.cl/"
                    style="display: inline-block; padding: 10px 10px; margin: 10px auto; background-color: #0d6efd; color: #ffffff; text-decoration: none; border-radius: 5px; text-align: center; color:black;">Ir
                    al Sistema</a>

            </div>


            <div style="text-align: center;">
            </div>
        </div>


        <div>
            <hr />
            <img alt="pie" src="https://www.siae.cl/email/archivos/IuNYrJlxxZN9U0v254lx/Footer_colegios.png"
                style="max-width:100%; width:600px" />
        </div>
    </div>
</body>

</html>