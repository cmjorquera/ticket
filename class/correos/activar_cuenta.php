<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activar Cuenta</title>
    <link rel="stylesheet" href="https://acceso.seduc.cl/css/correos/estilo_correos.css">
</head>
<body>
    <div class="mail-shell">
        <div class="mail-frame">
            <img alt="Seduc" class="mail-header-image" src="https://www.siae.cl/email/archivos/1FiVR9m09zWTnZ4IdW1g/header_siae_seduc.png">
            <div class="mail-stripes"></div>

            <div class="mail-card">
                <div style="text-align:center;">
                    <span class="mail-badge">Sistema de Tickets</span>
                    <h1 class="mail-title">Bienvenido al sistema</h1>
                    <div class="mail-divider"></div>
                    <div class="mail-intro">
                        Activa tu cuenta para comenzar a gestionar solicitudes, hacer seguimiento y consultar el estado de tus tickets de forma clara y ordenada.
                    </div>
                </div>

                <div class="mail-section">
                    <p class="mail-text">Hola <strong>{nombreCompleto}</strong>,</p>
                    <p class="mail-text">Tu cuenta fue creada correctamente. Desde el Sistema de Tickets podras registrar solicitudes, revisar avances en tiempo real y mantener visibilidad completa del ciclo de atencion.</p>
                    <p class="mail-text">Tambien podras consultar informacion resumida sobre tus requerimientos para trabajar con mayor trazabilidad y autonomia.</p>
                </div>

                <table class="mail-grid" role="presentation">
                    <tr>
                        <td width="50%">
                            <div class="mail-panel mail-panel-accent-green">
                                <p class="mail-panel-title green">Seguimiento</p>
                                <p class="mail-panel-text">Consulta el estado de cada ticket y revisa su avance desde el ingreso hasta el cierre.</p>
                            </div>
                        </td>
                        <td width="50%">
                            <div class="mail-panel mail-panel-accent-blue">
                                <p class="mail-panel-title blue">Gestion</p>
                                <p class="mail-panel-text">Accede a un espacio organizado para registrar solicitudes y mantener historial de tus atenciones.</p>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="mail-cta-wrap">
                    <p class="mail-text">Para comenzar, haz clic en <strong>Activar Cuenta</strong>. Desde ahi podras crear tu contrasena e ingresar al sistema.</p>
                    <a class="mail-button" href="{enlaceActivacion}">Activar Cuenta</a>
                </div>

                <div class="mail-section">
                    <div class="mail-panel">
                        <p class="mail-panel-title blue">Datos de acceso</p>
                        <table class="mail-data" role="presentation">
                            <tr>
                                <td class="mail-data-label">Usuario</td>
                                <td class="mail-data-value">{emailUsuario}</td>
                            </tr>
                            <tr>
                                <td class="mail-data-label">Area de trabajo</td>
                                <td class="mail-data-value">{areaTrabajo}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <p class="mail-note">Si el boton no funciona, copia y pega este enlace en tu navegador:</p>
                <p class="mail-note"><a class="mail-link" href="{enlaceActivacion}">{enlaceActivacion}</a></p>
            </div>

            <img alt="Colegios SEDUC" class="mail-footer-image" src="https://www.siae.cl/email/archivos/IuNYrJlxxZN9U0v254lx/Footer_colegios.png">
        </div>
    </div>
</body>
</html>
