<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud de Soporte</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f7f7f7; padding: 30px; border-radius: 10px;">
        <h1 style="color: #1AAD8A; margin-bottom: 20px;">Nueva Solicitud de Soporte</h1>
        
        <p>Se ha recibido una nueva solicitud de soporte que requiere atención.</p>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #1AAD8A;">
            <h2 style="color: #1AAD8A; margin-top: 0;">Detalles de la solicitud:</h2>
            <p><strong>Usuario:</strong> {{ $userName }}</p>
            <p><strong>Correo del Usuario:</strong> {{ $userEmail }}</p>
            <p><strong>Asunto:</strong> {{ $subject }}</p>
            <p><strong>Descripción:</strong></p>
            <p style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 10px; white-space: pre-wrap;">{{ $description }}</p>
            <p style="color: #666; font-size: 14px; margin-top: 15px;"><strong>Fecha:</strong> {{ $createdAt }}</p>
        </div>
        
        <p style="margin-top: 20px;">Por favor, revisa esta solicitud y contacta al usuario a la brevedad posible.</p>
        
        <p style="margin-top: 30px; color: #666; font-size: 14px;">
            Saludos,<br>
            Sistema de Notificaciones - Next Pricing
        </p>
    </div>
</body>
</html>
