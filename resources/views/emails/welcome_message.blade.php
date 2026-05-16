<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido al SGCI</title>
    <style>
        /* Estilos base para forzar a los clientes de correo a renderizar bien */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f5f7; color: #333333; }
        
        /* Efecto hover para el botón (soportado por algunos clientes) */
        .button:hover { background-color: #3b7320 !important; }
    </style>
</head>
<body style="background-color: #f4f5f7; margin: 0; padding: 0;">
    <!-- Contenedor principal para centrar el correo -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding: 40px 15px;">
                
                <!-- Tarjeta Blanca del Correo -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); overflow: hidden;">
                    
                    <!-- Cabecera (Header) -->
                    <tr>
                        <td align="center" style="background-color: #071D49; padding: 35px 20px;">
                            
                      
                            <img src="https://katoki.coop/wp-content/uploads/2024/08/02-Logo-blanco.png" alt="Logotipo KATO-KI" width="180" style="display: block; margin-bottom: 15px; max-width: 100%; height: auto;">
                            
                            <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; letter-spacing: 1px;">SGCI</h1>
                            <p style="color: #ffffff; margin: 8px 0 0 0; font-size: 15px;">Sistema de Gestión de Capacitaciones KATO-KI</p>
                        </td>
                    </tr>

                    <!-- Cuerpo del Correo (Body) -->
                    <tr>
                        <td style="padding: 40px 35px;">
                            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: #334155;">Hola, <strong style="color: #0f172a;">{{ $user->name }}</strong>.</p>
                            <p style="margin: 0 0 25px 0; font-size: 16px; line-height: 1.6; color: #475569;">Nos alegra informarte que tu cuenta ha sido creada exitosamente. A continuación, encontrarás tus credenciales de acceso temporal para ingresar al sistema:</p>
                            
                            <!-- Caja de Credenciales -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="margin: 0 0 12px 0; font-size: 15px; color: #334155;"><strong>👤 Usuario:</strong> {{ $user->email }}</p>
                                        <p style="margin: 0; font-size: 15px; color: #334155;"><strong>🔑 Contraseña:</strong> <span style="background-color: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-family: 'Courier New', Courier, monospace; font-weight: bold; letter-spacing: 1px;">{{ $temporaryPassword }}</span></p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 30px 0; font-size: 16px; line-height: 1.6; color: #475569;">Por protocolos de seguridad, te solicitamos que cambies esta contraseña inmediatamente haciendo clic en el siguiente botón:</p>
                            
                            <!-- Botón de Acción -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('password.reset', ['token' => 'init']) }}" class="button" style="display: inline-block; background-color: #4C8C2B; color: #ffffff; font-size: 16px; font-weight: 600; text-decoration: none; padding: 14px 30px; border-radius: 6px;">Cambiar mi contraseña</a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Enlace alternativo de respaldo -->
                            <p style="margin: 35px 0 0 0; font-size: 14px; line-height: 1.5; color: #64748b;">Si tienes problemas con el botón, copia y pega el siguiente enlace directamente en tu navegador web:</p>
                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #2563eb; word-break: break-all;">
                                <a href="{{ route('password.reset', ['token' => 'init']) }}" style="color: #2563eb; text-decoration: underline;">{{ route('password.reset', ['token' => 'init']) }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Pie de página (Footer) -->
                    <tr>
                        <td align="center" style="background-color: #f8fafc; padding: 25px; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; font-size: 13px; color: #94a3b8;">© {{ date('Y') }} SGCI KATO-KI. Todos los derechos reservados.</p>
                            <p style="margin: 8px 0 0 0; font-size: 12px; color: #94a3b8;">Este es un mensaje generado automáticamente, por favor no respondas a esta dirección de correo.</p>
                        </td>
                    </tr>

                </table>
                
            </td>
        </tr>
    </table>
</body>
</html>