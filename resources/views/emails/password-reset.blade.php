<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablece tu contraseña de SGCI</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f8f7; color: #334155; }
        .button:hover { background-color: #154734 !important; }
    </style>
</head>
<body style="background-color: #f4f8f7; margin: 0; padding: 0;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding: 40px 15px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; box-shadow: 0 8px 24px rgba(15,45,42,0.12); overflow: hidden;">
                    <tr>
                        <td align="center" style="background-color: #071D49; padding: 32px 20px;">
                            <img src="https://katoki.coop/wp-content/uploads/2024/08/02-Logo-blanco.png" alt="Logotipo KATO-KI" width="160" style="display: block; margin-bottom: 14px; max-width: 100%; height: auto;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; letter-spacing: 1px;">SGCI</h1>
                            <p style="color: #dcefd1; margin: 8px 0 0 0; font-size: 14px;">Sistema de Gestión de Capacitación Interna</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px 35px;">
                            <p style="margin: 0 0 18px 0; font-size: 16px; line-height: 1.6; color: #334155;">Hola, <strong style="color: #071D49;">{{ $user->name }}</strong>.</p>
                            <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.6; color: #475569;">Recibimos una solicitud para cambiar la contraseña de tu cuenta SGCI. Usa el siguiente botón para elegir una nueva contraseña.</p>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f8f7; border: 1px solid #dce8df; border-radius: 12px; margin-bottom: 28px;">
                                <tr>
                                    <td style="padding: 18px 20px;">
                                        <p style="margin: 0; font-size: 14px; line-height: 1.55; color: #315744;"><strong>Enlace protegido:</strong> por seguridad, solo puede utilizarse una vez y vence en {{ $expiresInMinutes }} minutos.</p>
                                    </td>
                                </tr>
                            </table>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $resetUrl }}" class="button" style="display: inline-block; background-color: #4C8C2B; color: #ffffff; font-size: 16px; font-weight: 700; text-decoration: none; padding: 14px 30px; border-radius: 10px;">Cambiar mi contraseña</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 32px 0 0 0; font-size: 14px; line-height: 1.5; color: #64748b;">Si no solicitaste este cambio, puedes ignorar este correo. Tu contraseña actual seguirá siendo válida.</p>
                            <p style="margin: 24px 0 0 0; font-size: 13px; line-height: 1.5; color: #64748b;">Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #2563eb; word-break: break-all;"><a href="{{ $resetUrl }}" style="color: #2563eb; text-decoration: underline;">{{ $resetUrl }}</a></p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="background-color: #f8fafc; padding: 24px; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; font-size: 13px; color: #94a3b8;">© {{ date('Y') }} SGCI KATO-KI. Todos los derechos reservados.</p>
                            <p style="margin: 8px 0 0 0; font-size: 12px; color: #94a3b8;">Este es un mensaje generado automáticamente, por favor no respondas a esta dirección.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
