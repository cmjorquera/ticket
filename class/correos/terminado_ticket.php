<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Ticket resuelto</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    @media (max-width:620px){
      .container{width:100%!important}
      .stack{display:block!important;width:100%!important}
      .btn{display:block!important;width:100%!important;margin-bottom:8px!important}
      .p-24{padding:16px!important}
    }
  </style>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f4f6f8;">
    <tr>
      <td align="center" style="padding:24px;">
        <!-- CONTENEDOR -->
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" class="container" style="width:600px;max-width:600px;background:#ffffff;border-radius:10px;overflow:hidden;">
          
          <!-- LOGO -->
          <tr>
            <td align="center" style="padding:10px 24px 0;">
              <!-- Usa {logo_src} para URL absoluta o cid: -->
            <img alt="Seduc" src="https://www.siae.cl/email/archivos/1FiVR9m09zWTnZ4IdW1g/header_siae_seduc.png" style="width:600px" /></div>
            </td>
          </tr>

          <!-- TITULAR -->
          <tr>
            <td align="center" style="padding:12px 24px 0;">
              <div style="height:2px;width:80px;background:#0ea5e9;margin:0 auto 14px;"></div>
              <h1 style="margin:0;font-size:22px;color:#111827;font-weight:700;">¡Todo listo!</h1>
              <p style="margin:8px 0 0;font-size:15px;color:#374151;">Tu ticket fue resuelto con éxito.</p>
            </td>
          </tr>

          <!-- IDENTIFICADOR -->
          <tr>
            <td align="center" style="padding:12px 24px 8px;">
              <span style="display:inline-block;background:#0f172a;color:#ffffff;font-weight:700;font-size:14px;padding:10px 16px;border-radius:24px;">
                N° DE TICKET: {codigo}
              </span>
            </td>
          </tr>

          <!-- RESUMEN (2 columnas) -->
<tr>
  <td class="p-24" style="padding:24px;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #e5e7eb;border-radius:8px;">
      <tr>
        <!-- IZQUIERDA -->
        <td class="stack" valign="top" style="width:50%;padding:16px;">
          <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
            <!-- Usuario -->
            <tr>
              <td valign="middle" style="width:26px;padding-right:8px;">
                <img alt="Usuario" width="22" src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/user.png" style="display:block;border:0;">
              </td>
              <td style="font-size:14px;color:#111827;">{nombreUsarioCompleto}</td>
            </tr>
            <tr><td colspan="2" style="height:12px;line-height:12px;">&nbsp;</td></tr>

            <!-- Asunto -->
            <tr>
              <td valign="middle" style="width:26px;padding-right:8px;">
                <img alt="Asunto" width="22" src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/briefcase.png" style="display:block;border:0;">
              </td>
              <td style="font-size:14px;color:#111827;">{asunto}</td>
            </tr>

            <!-- Si quieres mostrar la descripción, descomenta este bloque
            <tr><td colspan="2" style="height:12px;line-height:12px;">&nbsp;</td></tr>
            <tr>
              <td valign="top" style="width:26px;padding-right:8px;">
                <img alt="Descripción" width="22" src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/file-text.png" style="display:block;border:0;">
              </td>
              <td style="font-size:14px;color:#111827;line-height:1.45;">{descripcion}</td>
            </tr>
            -->
          </table>
        </td>

        <!-- DERECHA -->
        <td class="stack" valign="top" style="width:50%;padding:16px;border-left:1px solid #e5e7eb;">
          <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
            <!-- Fecha -->
            <tr>
              <td valign="middle" style="width:26px;padding-right:8px;">
                <img alt="Fecha de finalización" width="22" src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/calendar.png" style="display:block;border:0;">
              </td>
              <td style="font-size:14px;color:#111827;">{fecha}</td>
            </tr>
            <tr><td colspan="2" style="height:12px;line-height:12px;">&nbsp;</td></tr>

            <!-- Hora -->
            <tr>
              <td valign="middle" style="width:26px;padding-right:8px;">
                <img alt="Hora de finalización" width="22" src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/clock.png" style="display:block;border:0;">
              </td>
              <td style="font-size:14px;color:#111827;">{hora}</td>
            </tr>
            <tr><td colspan="2" style="height:12px;line-height:12px;">&nbsp;</td></tr>

            <!-- Técnico asignado -->
            <tr>
              <td valign="middle" style="width:26px;padding-right:8px;">
                <img alt="Técnico asignado" width="22" src="https://qa.seduc.cl/sistema/sistema_ticket/imagenes/user.png" style="display:block;border:0;">
              </td>
              <td style="font-size:14px;color:#111827;">{nombre_tecnico}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>
          
          
          <tr>
            <td style="padding:0 24px 8px;">
              <h3 style="margin:0 0 8px;font-size:16px;color:#111827;">Descripcion </h3>
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;">
                <tr>
                  <td style="padding:16px;font-size:14px;color:#374151;line-height:1.5;">
                    {descripcion}
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <!-- COMENTARIO DEL TÉCNICO -->
          <tr>
            <td style="padding:0 24px 8px;">
              <h3 style="margin:0 0 8px;font-size:16px;color:#111827;">Comentario del técnico</h3>
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;">
                <tr>
                  <td style="padding:16px;font-size:14px;color:#374151;line-height:1.5;">
                    {comentarioTecnicoFinal}
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          
 

          <!-- CTAs -->
          <tr>
            <td align="center" style="padding:16px 24px 20px;">
              <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td class="btn" align="center" style="border-radius:8px;background:#2563eb;">
                    <a href="https://acceso.seduc.cl/ticket.php" style="display:inline-block;padding:10px 16px;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;border-radius:8px;">Ir al Sistema</a>
                  </td>
                  <td style="width:12px;"></td>
             
                </tr>
              </table>
            </td>
          </tr>

          <!-- FOOTER -->
          <tr>
            <td align="center" style="padding:0 24px 24px;color:#9ca3af;font-size:12px;">
                      <img alt="pie" src="https://www.siae.cl/email/archivos/IuNYrJlxxZN9U0v254lx/Footer_colegios.png"
                style="max-width:100%; width:600px" />
            </td>
          </tr>
        </table>
        <!-- /CONTENEDOR -->
      </td>
    </tr>
  </table>
</body>
</html>
