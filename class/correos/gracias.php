<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Ticket Validado</title>
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
        .timeline-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .timeline {
            position: relative;
            padding: 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 4px solid red;
        }
        .timeline-item {
            position: relative;
            width: 100px;
            text-align: center;
        }
        .timeline-circle {
            width: 20px;
            height: 20px;
            background-color: red;
            border-radius: 50%;
            display: inline-block;
            position: relative;
        }
        .timeline-content {
            margin-top: 10px;
            font-size: 14px;
        }
        .ticket-info {
            margin-top: 30px;
            font-size: 16px;
            color: #555;
        }
        .ticket-info strong {
            color: black;
        }
    </style>
</head>
<body>

    <div class="email-container">
        <div class="header">
            <img alt="Seduc" src="https://www.siae.cl/email/archivos/1FiVR9m09zWTnZ4IdW1g/header_siae_seduc.png" style="width:100%; max-width:600px; border-radius: 10px;" />
        </div>
        <div style="width: 50%; background-color:#3A913F; height: 4px; float: left"></div>
        <div style="width: 25%; background-color:#EAAA00; height: 4px; float: left"></div>
        <div style="width: 25%; background-color:#005587; height: 4px; float: left"></div>
        <div style="clear:both;"></div>

        <div class="content">
            <h1>¡Gracias por tu validación!</h1>
            <p>Estimado <strong>{nombreUsarioCompleto}</strong>,</p>
            <p>Agradecemos tu tiempo y colaboración en validar la resolución del ticket.</p>
            <p>Detalles del ticket validado:</p>
            
            <!-- <p><strong>Número de Ticket:</strong> A-0{codigo}</p> -->
            <p><strong>Asunto:</strong> {asunto}</p>
            <p><strong>Descripcion:</strong> {descripcion}</p>


          
        </div>

        <!-- 📌 Sección de Cronología del Ticket -->
        <div class="timeline-container">
            <h2>Cronología del Ticket</h2>

            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-circle"></div>
                    <div class="timeline-content">
                        <strong>Creado</strong><br>
                        {fecha_creacion_inicio}<br>
                        {hora_creacion_inicio}
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-circle"></div>
                    <div class="timeline-content">
                        <strong>Asignado</strong><br>
                        {fecha_asignacion_tecnico}<br>
                        {hora_asignacion_tecnico}

                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-circle"></div>
                    <div class="timeline-content">
                        <strong>En proceso</strong><br>
                        {fecha_comienzo_ticket}<br>
                        {hora_comienzo_ticket}

                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-circle"></div>
                    <div class="timeline-content">
                        <strong>Terminado</strong><br>
                        {fecha_cierre_ticket}<br>
                        {hora_cierre_ticket}

                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-circle"></div>
                    <div class="timeline-content">
                        <strong>Cerrado</strong><br>
                        
                        {fecha_termino_ticket}<br>
                        {hora_termino_ticket}

                    </div>
                </div>
            </div>

            <div class="ticket-info">
                <p><strong>Número de Ticket:</strong> A-0{codigo}</p>
                <!-- <p><strong>Comentario tecnico:</strong> {estado_validacion}</p> -->
                <p>Si tienes algún comentario adicional o necesitas más asistencia, no dudes en contactarnos.</p>
                <p>¡Gracias por tu confianza!</p>            </div>
        </div>

        <div>
            <hr />
            <img alt="pie" src="https://www.siae.cl/email/archivos/IuNYrJlxxZN9U0v254lx/Footer_colegios.png"
                style="max-width:100%; width:600px" />
        </div>
    </div>

</body>
</html>
