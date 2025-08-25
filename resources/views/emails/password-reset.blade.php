<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #F7F7F7;">

<table style="width: 100%;">
    <tr>
        <td style="text-align: center; padding: 20px 0;">
            <img src="{{ asset('img/logo-raga-email.png') }}" alt="Raga Logo" style="max-width: 234px; height: auto;">
        </td>
    </tr>
</table>


<table role="presentation" style="width: 100%; max-width: 400px; margin: 0 auto; padding: 20px;background: #fff;border-radius: 30px;">
    <tr>
        <td style="text-align: center; padding: 30px 0;">
            <!-- Lock Icon -->
            <div style="background-color: #f0f4ff; width: 130px; height: 130px; border-radius: 50%; margin: 0 auto; text-align: center; line-height: 130px;">
                <img src="{{ asset('img/icono-emai-password.png') }}" alt="Password Reset" style="max-width: 77px; height: auto; vertical-align: middle;">
            </div>
        </td>
    </tr>
    <tr>
        <td style="text-align: center; padding: 20px 0;">
            <h1 style="color: #0000FF; font-size: 24px; margin: 0; padding: 0;">Restablecer Contraseña</h1>
        </td>
    </tr>
    <tr>
        <td style="text-align: center; padding: 20px 40px;">
            <p style="color: #333333; font-size: 16px; line-height: 1.5; margin: 0;">
                Haz clic en el siguiente enlace para restablecer tu contraseña dentro de las próximas 24 horas. Después de ese tiempo, deberás solicitar un nuevo enlace.
            </p>
        </td>
    </tr>
    <tr>
        <td style="text-align: center; padding: 30px 0;">
            <a href="{{ $resetUrl }}" style="
                    background-color: #0000FF;
                    color: #ffffff;
                    padding: 12px 30px;
                    text-decoration: none;
                    border-radius: 5px;
                    font-size: 16px;
                    font-weight: bold;
                    display: inline-block;
                ">
                Restablecer contraseña
            </a>
        </td>
    </tr>
</table>
</body>
</html>
