<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket sin tecnico</title>
    <link rel="stylesheet" href="https://acceso.seduc.cl/css/correos/estilo_correos.css">
</head>
<body>
    <div class="mail-shell">
        <div class="mail-frame">
            <img alt="Seduc" class="mail-header-image" src="https://www.siae.cl/email/archivos/1FiVR9m09zWTnZ4IdW1g/header_siae_seduc.png">
            <div class="mail-stripes"></div>

            <div class="mail-card">
                <div style="text-align:center;">
                    <span class="mail-badge">Alerta de Asignacion</span>
                    <h1 class="mail-title">Ticket pendiente de tecnico</h1>
                    <div class="mail-divider"></div>
                    <div class="mail-intro">
                        El usuario <strong>{nombreUsarioCompleto}</strong> ingreso una nueva solicitud y aun no tiene tecnico asignado. Se requiere gestion administrativa.
                    </div>
                    <span class="mail-chip">N° de Ticket: <strong>A-0{codigo}</strong></span>
                </div>

                <div class="mail-alert">
                    Se recomienda asignar un tecnico a la brevedad para evitar retrasos en la atencion.
                </div>

                <table class="mail-grid" role="presentation">
                    <tr>
                        <td width="50%">
                            <div class="mail-panel mail-panel-accent-green">
                                <p class="mail-panel-title green">Hora de ingreso</p>
                                <p class="mail-panel-text">{hora}</p>
                            </div>
                        </td>
                        <td width="50%">
                            <div class="mail-panel mail-panel-accent-blue">
                                <p class="mail-panel-title blue">Fecha</p>
                                <p class="mail-panel-text">{fecha}</p>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="mail-highlight gold">
                    <p class="mail-panel-title gold">Asunto</p>
                    <p class="mail-panel-text">{asunto}</p>
                </div>

                <div class="mail-section">
                    <div class="mail-highlight">
                        <p class="mail-panel-title blue">Descripcion</p>
                        <p class="mail-panel-text">{descripcion}</p>
                    </div>
                </div>

                <div class="mail-cta-wrap">
                    <p class="mail-text">Ingresa al sistema para revisar el ticket y realizar la asignacion correspondiente.</p>
                    <a class="mail-button" href="https://www.acceso.seduc.cl/">Ir al Sistema</a>
                </div>
            </div>

            <img alt="Colegios SEDUC" class="mail-footer-image" src="https://www.siae.cl/email/archivos/IuNYrJlxxZN9U0v254lx/Footer_colegios.png">
        </div>
    </div>
</body>
</html>
