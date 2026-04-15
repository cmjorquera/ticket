<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reinicio de Contrasena</title>
    <link rel="stylesheet" href="https://acceso.seduc.cl/css/correos/estilo_correos.css">
</head>
<body>
    <div class="mail-shell">
        <div class="mail-frame">
            <img alt="Seduc" class="mail-header-image" src="https://www.siae.cl/email/archivos/1FiVR9m09zWTnZ4IdW1g/header_siae_seduc.png">
            <div class="mail-stripes"></div>

            <div class="mail-card">
                <div style="text-align:center;">
                    <span class="mail-badge">Seguridad de Cuenta</span>
                    <h1 class="mail-title">Reinicio de contrasena</h1>
                    <div class="mail-divider"></div>
                    <div class="mail-intro">
                        Hola <strong>{nombreUsuario} {apellidoUsuario}</strong>. Has solicitado reiniciar la contrasena de tu cuenta. Si no realizaste esta solicitud, puedes ignorar este mensaje.
                    </div>
                </div>

                <div class="mail-alert info">
                    Por seguridad, este enlace debe utilizarse solo para actualizar el acceso de tu cuenta institucional.
                </div>

                <div class="mail-section">
                    <div class="mail-panel">
                        <p class="mail-panel-title blue">Datos de la cuenta</p>
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

                <div class="mail-cta-wrap">
                    <p class="mail-text">Haz clic en el boton para continuar con el cambio de contrasena.</p>
                    <a class="mail-button" href="http://acceso.seduc.cl/reiniciar_clave.php?token={token}">Reiniciar Contrasena</a>
                </div>

                <p class="mail-note">Si el boton no funciona, utiliza este enlace: <a class="mail-link" href="http://acceso.seduc.cl/reiniciar_clave.php?token={token}">http://acceso.seduc.cl/reiniciar_clave.php?token={token}</a></p>
            </div>

            <img alt="Colegios SEDUC" class="mail-footer-image" src="https://www.siae.cl/email/archivos/IuNYrJlxxZN9U0v254lx/Footer_colegios.png">
        </div>
    </div>
</body>
</html>
