<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracias por tu validacion</title>
    <link rel="stylesheet" href="https://acceso.seduc.cl/css/correos/estilo_correos.css">
</head>
<body>
    <div class="mail-shell">
        <div class="mail-frame">
            <img alt="Seduc" class="mail-header-image" src="https://www.siae.cl/email/archivos/1FiVR9m09zWTnZ4IdW1g/header_siae_seduc.png">
            <div class="mail-stripes"></div>

            <div class="mail-card">
                <div style="text-align:center;">
                    <span class="mail-badge">Validacion Recibida</span>
                    <h1 class="mail-title">Gracias por tu validacion</h1>
                    <div class="mail-divider"></div>
                    <div class="mail-intro">
                        Estimado <strong>{nombreUsarioCompleto}</strong>, hemos registrado tu respuesta sobre la atencion del ticket. Agradecemos tu tiempo y colaboracion.
                    </div>
                    <span class="mail-chip">N° de Ticket: <strong>A-0{codigo}</strong></span>
                </div>

                <div class="mail-section">
                    <div class="mail-highlight gold">
                        <p class="mail-panel-title gold">Asunto</p>
                        <p class="mail-panel-text">{asunto}</p>
                    </div>
                </div>

                <div class="mail-section">
                    <div class="mail-highlight">
                        <p class="mail-panel-title blue">Descripcion</p>
                        <p class="mail-panel-text">{descripcion}</p>
                    </div>
                </div>

                <div class="mail-section">
                    <h2 class="mail-subtitle">Cronologia del ticket</h2>
                    <table class="mail-timeline" role="presentation">
                        <tr class="mail-timeline-item">
                            <td class="mail-timeline-step">Creado</td>
                            <td class="mail-timeline-date">{fecha_creacion_inicio} - {hora_creacion_inicio}</td>
                        </tr>
                        <tr class="mail-timeline-item">
                            <td class="mail-timeline-step">Asignado</td>
                            <td class="mail-timeline-date">{fecha_asignacion_tecnico} - {hora_asignacion_tecnico}</td>
                        </tr>
                        <tr class="mail-timeline-item">
                            <td class="mail-timeline-step">En proceso</td>
                            <td class="mail-timeline-date">{fecha_comienzo_ticket} - {hora_comienzo_ticket}</td>
                        </tr>
                        <tr class="mail-timeline-item">
                            <td class="mail-timeline-step">Terminado</td>
                            <td class="mail-timeline-date">{fecha_cierre_ticket} - {hora_cierre_ticket}</td>
                        </tr>
                        <tr class="mail-timeline-item">
                            <td class="mail-timeline-step">Cerrado</td>
                            <td class="mail-timeline-date">{fecha_termino_ticket} - {hora_termino_ticket}</td>
                        </tr>
                    </table>
                </div>

                <div class="mail-cta-wrap">
                    <p class="mail-text">Si necesitas mas asistencia o deseas revisar otros tickets, puedes volver a ingresar al sistema cuando quieras.</p>
                    <a class="mail-button" href="https://www.acceso.seduc.cl/">Ir al Sistema</a>
                </div>
            </div>

            <img alt="Colegios SEDUC" class="mail-footer-image" src="https://www.siae.cl/email/archivos/IuNYrJlxxZN9U0v254lx/Footer_colegios.png">
        </div>
    </div>
</body>
</html>
