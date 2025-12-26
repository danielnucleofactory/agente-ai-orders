<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Soporte Recibida</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f7f7f7; padding: 30px; border-radius: 10px;">
        <h1 style="color: #565AFF; margin-bottom: 20px;">Solicitud de Soporte Recibida</h1>
        
        <p>Hola {{ $user->name }},</p>
        
        <p>Hemos recibido tu solicitud de soporte correctamente. Nuestro equipo la revisará y te contactará pronto.</p>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #565AFF;">
            <h2 style="color: #565AFF; margin-top: 0;">Detalles de tu solicitud:</h2>
            <p><strong>Nombre:</strong> {{ $user->name }}</p>
            <p><strong>Correo:</strong> {{ $user->email }}</p>
            <p><strong>Asunto:</strong> {{ $supportRequest->subject }}</p>
            <p><strong>Descripción:</strong></p>
            <p style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 10px; white-space: pre-wrap;">{{ $supportRequest->message }}</p>
            <p style="color: #666; font-size: 14px; margin-top: 15px;"><strong>Fecha:</strong> {{ formatDateTime($supportRequest->created_at) }}</p>
        </div>
        
        <p>Gracias por contactarnos. Te responderemos a la brevedad posible.</p>
        
        <p style="margin-top: 30px; color: #666; font-size: 14px;">
            Saludos,<br>
            Equipo de Soporte - Next
        </p>
    </div>
</body>
</html>
