<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta Porth - Puerto sin match</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f7f7f7; padding: 30px; border-radius: 10px;">
        <h1 style="color: #b91c1c; margin-bottom: 20px;">Puerto sin match en Porth</h1>

        <div style="background-color: white; padding: 20px; border-radius: 8px; border-left: 4px solid #b91c1c;">
            <p><strong>PO:</strong> {{ $context['order_number'] ?? 'N/A' }}</p>
            <p><strong>Container number:</strong> {{ $context['container_number'] ?? 'N/A' }}</p>
            <p><strong>Puerto:</strong> {{ $context['port_role_label'] ?? 'N/A' }}</p>
            <p><strong>Nombre recibido:</strong> {{ $context['port_name'] ?? 'N/A' }}</p>
        </div>
    </div>
</body>
</html>
