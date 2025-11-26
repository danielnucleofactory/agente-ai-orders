<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Soporte Recibida</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #F7F7F7;">

<table style="width: 100%;">
    <tr>
        <td style="text-align: center; padding: 20px 0;">
            <img src="{{ asset('img/logo-raga-email.png') }}" alt="Raga Logo" style="max-width: 234px; height: auto;">
        </td>
    </tr>
</table>

<table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; padding: 20px; background: #fff; border-radius: 30px;">
    <tr>
        <td style="text-align: center; padding: 30px 0;">
            <!-- Support Icon -->
            <div style="background-color: #E6F9F4; width: 130px; height: 130px; border-radius: 50%; margin: 0 auto; text-align: center; line-height: 130px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="77" height="77" viewBox="0 0 24 24" fill="none" stroke="#1AAD8A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
            </div>
        </td>
    </tr>
    <tr>
        <td style="text-align: center; padding: 20px 0;">
            <h1 style="color: #1AAD8A; font-size: 24px; margin: 0; padding: 0;">Solicitud de Soporte Recibida</h1>
        </td>
    </tr>
    <tr>
        <td style="padding: 20px 40px;">
            <p style="color: #333333; font-size: 16px; line-height: 1.5; margin: 0 0 20px 0;">
                Hola <strong>{{ $user->name }}</strong>,
            </p>
            <p style="color: #333333; font-size: 16px; line-height: 1.5; margin: 0 0 20px 0;">
                Hemos recibido tu solicitud de soporte y la estamos revisando. Te contactaremos pronto.
            </p>
        </td>
    </tr>
    <tr>
        <td style="padding: 0 40px;">
            <div style="background-color: #F7F7F7; border-radius: 10px; padding: 20px; margin: 20px 0;">
                <h3 style="color: #333333; font-size: 18px; margin: 0 0 15px 0; font-weight: bold;">Detalles de tu solicitud:</h3>
                <p style="color: #666666; font-size: 14px; margin: 5px 0;">
                    <strong style="color: #333333;">Asunto:</strong> {{ $supportRequest->subject }}
                </p>
                <p style="color: #666666; font-size: 14px; margin: 5px 0;">
                    <strong style="color: #333333;">Mensaje:</strong>
                </p>
                <p style="color: #666666; font-size: 14px; margin: 10px 0; padding: 10px; background-color: #ffffff; border-radius: 5px; white-space: pre-wrap;">
                    {{ $supportRequest->message }}
                </p>
                <p style="color: #666666; font-size: 12px; margin: 15px 0 5px 0;">
                    <strong style="color: #333333;">Número de solicitud:</strong> #{{ $supportRequest->id }}
                </p>
                <p style="color: #666666; font-size: 12px; margin: 5px 0;">
                    <strong style="color: #333333;">Fecha:</strong> {{ $supportRequest->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
        </td>
    </tr>
    <tr>
        <td style="padding: 20px 40px;">
            <p style="color: #333333; font-size: 16px; line-height: 1.5; margin: 0 0 20px 0;">
                Nuestro equipo de soporte revisará tu solicitud y te responderá a la brevedad posible.
            </p>
            <p style="color: #666666; font-size: 14px; line-height: 1.5; margin: 0;">
                Si tienes alguna pregunta urgente, no dudes en contactarnos directamente.
            </p>
        </td>
    </tr>
    <tr>
        <td style="text-align: center; padding: 30px 0;">
            <a href="{{ route('support.index') }}" style="
                background-color: #1AAD8A;
                color: #ffffff;
                padding: 12px 30px;
                text-decoration: none;
                border-radius: 5px;
                font-size: 16px;
                font-weight: bold;
                display: inline-block;
            ">
                Ver Solicitud
            </a>
        </td>
    </tr>
    <tr>
        <td style="text-align: center; padding: 20px 40px; border-top: 1px solid #E5E5E5; margin-top: 20px;">
            <p style="color: #999999; font-size: 12px; margin: 0;">
                Este es un email automático, por favor no respondas a este mensaje.
            </p>
        </td>
    </tr>
</table>
</body>
</html>

