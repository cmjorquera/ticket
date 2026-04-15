<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ticket Resuelto</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://acceso.seduc.cl/css/correos/estilo_correos.css">
</head>
<body>
    <div class="mail-shell">
        <div class="mail-frame">
            <img alt="Seduc" class="mail-header-image" src="https://www.siae.cl/email/archivos/1FiVR9m09zWTnZ4IdW1g/header_siae_seduc.png">
            <div class="mail-stripes"></div>

            <div class="mail-card">
                <div style="text-align:center;">
                    <span class="mail-badge">Resolucion del Ticket</span>
                    <h1 class="mail-title">Todo listo</h1>
                    <div class="mail-divider"></div>
                    <div class="mail-intro">
                        Tu ticket fue resuelto exitosamente. A continuacion puedes revisar el resumen del cierre realizado por el tecnico.
                    </div>
                    <p><span class="mail-status success">Resuelto</span></p>
                    <span class="mail-chip">N° de Ticket: <strong>{codigo}</strong></span>
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
                                <p class="mail-panel-title gold">Fecha de finalizacion</p>
                                <p class="mail-panel-text">{fecha}</p>
                            </div>
                        </td>
                        <td width="50%">
                            <div class="mail-panel mail-panel-accent-blue">
                                <p class="mail-panel-title blue">Hora de finalizacion</p>
                                <p class="mail-panel-text">{hora}</p>
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

                <div class="mail-section">
                    <div class="mail-highlight green">
                        <p class="mail-panel-title green">Comentario del tecnico</p>
                        <p class="mail-panel-text">{comentarioTecnicoFinal}</p>
                    </div>
                </div>

                <div class="mail-cta-wrap">
                    <p class="mail-text">Puedes revisar el ticket en el sistema y validar la atencion si corresponde.</p>
                    <a class="mail-button" href="https://acceso.seduc.cl/ticket.php">Ir al Sistema</a>
                </div>
            </div>

            <img alt="Colegios SEDUC" class="mail-footer-image" src="https://www.siae.cl/email/archivos/IuNYrJlxxZN9U0v254lx/Footer_colegios.png">
        </div>
    </div>
</body>
</html>
