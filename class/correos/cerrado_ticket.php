<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Cerrado</title>
    <link rel="stylesheet" href="https://acceso.seduc.cl/css/correos/estilo_correos.css">
</head>
<body>
    <div class="mail-shell">
        <div class="mail-frame">
            <img alt="Seduc" class="mail-header-image" src="https://www.siae.cl/email/archivos/1FiVR9m09zWTnZ4IdW1g/header_siae_seduc.png">
            <div class="mail-stripes"></div>

            <div class="mail-card">
                <div style="text-align:center;">
                    <span class="mail-badge">Cierre de Ticket</span>
                    <h1 class="mail-title">Ticket cerrado correctamente</h1>
                    <div class="mail-divider"></div>
                    <div class="mail-intro">
                        Gracias por confiar en nuestro equipo. El ticket ya finalizo su ciclo y quedo registrado como cerrado.
                    </div>
                    <span class="mail-chip">N° de Ticket: <strong>A-0{codigo}</strong></span>
                </div>

                <div class="mail-section">
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
                                    <p class="mail-panel-title gold">Fecha de cierre</p>
                                    <p class="mail-panel-text">{fecha}</p>
                                </div>
                            </td>
                            <td width="50%">
                                <div class="mail-panel mail-panel-accent-blue">
                                    <p class="mail-panel-title blue">Hora de cierre</p>
                                    <p class="mail-panel-text">{hora}</p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="mail-highlight gold">
                    <p class="mail-panel-title gold">Asunto</p>
                    <p class="mail-panel-text">{asunto}</p>
                </div>

                <div class="mail-cta-wrap">
                    <p class="mail-text">Puedes ingresar al sistema para revisar el historial del ticket y consultar otros requerimientos cuando lo necesites.</p>
                    <a class="mail-button" href="https://www.acceso.seduc.cl/">Ir al Sistema</a>
                </div>
            </div>

            <img alt="Colegios SEDUC" class="mail-footer-image" src="https://www.siae.cl/email/archivos/IuNYrJlxxZN9U0v254lx/Footer_colegios.png">
        </div>
    </div>
</body>
</html>
