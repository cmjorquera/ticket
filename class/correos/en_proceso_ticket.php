<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket en Proceso</title>
    <link rel="stylesheet" href="https://acceso.seduc.cl/css/correos/estilo_correos.css">
</head>
<body>
    <div class="mail-shell">
        <div class="mail-frame">
            <img alt="Seduc" class="mail-header-image" src="https://www.siae.cl/email/archivos/1FiVR9m09zWTnZ4IdW1g/header_siae_seduc.png">
            <div class="mail-stripes"></div>

            <div class="mail-card">
                <div style="text-align:center;">
                    <span class="mail-badge">Estado del Ticket</span>
                    <h1 class="mail-title">Tu ticket ya esta en proceso</h1>
                    <div class="mail-divider"></div>
                    <div class="mail-intro">
                        Nos complace informarte que tu solicitud ya fue asignada a un tecnico y se encuentra en etapa de resolucion.
                    </div>
                    <p><span class="mail-status warning">En Proceso</span></p>
                    <span class="mail-chip">N° de Ticket: <strong>A-0{codigo}</strong></span>
                </div>

                <table class="mail-grid" role="presentation">
                    <tr>
                        <td width="50%">
                            <div class="mail-panel mail-panel-accent-green">
                                <p class="mail-panel-title green">Usuario</p>
                                <p class="mail-panel-text">{nombreUsarioCompleto}</p>
                            </div>
                        </td>
                        <td width="50%">
                            <div class="mail-panel mail-panel-accent-blue">
                                <p class="mail-panel-title blue">Tecnico asignado</p>
                                <p class="mail-panel-text">{nombre_tecnico}</p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td width="50%">
                            <div class="mail-panel mail-panel-accent-gold">
                                <p class="mail-panel-title gold">Hora de ingreso</p>
                                <p class="mail-panel-text">{hora}</p>
                            </div>
                        </td>
                        <td width="50%">
                            <div class="mail-panel mail-panel-accent-blue">
                                <p class="mail-panel-title blue">Fecha estimada de resolucion</p>
                                <p class="mail-panel-text">{fecha_resolucion}</p>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="mail-highlight gold">
                    <p class="mail-panel-title gold">Asunto</p>
                    <p class="mail-panel-text">{asunto}</p>
                </div>

                <div class="mail-section">
                    <div class="mail-alert">
                        Tiempo estimado de atencion: <strong>{dias_administrador_estima}</strong>
                    </div>
                </div>

                <div class="mail-cta-wrap">
                    <p class="mail-text">Puedes ingresar al sistema para seguir el avance del requerimiento y revisar futuras actualizaciones.</p>
                    <a class="mail-button" href="https://www.acceso.seduc.cl/">Ir al Sistema</a>
                </div>
            </div>

            <img alt="Colegios SEDUC" class="mail-footer-image" src="https://www.siae.cl/email/archivos/IuNYrJlxxZN9U0v254lx/Footer_colegios.png">
        </div>
    </div>
</body>
</html>
