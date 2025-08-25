<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Orden de Compra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8fafc;
            padding: 30px;
            border: 1px solid #e2e8f0;
        }
        .po-details {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #e2e8f0;
        }
        .btn {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 20px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            font-size: 14px;
            color: #64748b;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Confirmación de Orden de Compra</h1>
        <p>Raga Orders</p>
    </div>

    <div class="content">
        <p>Estimado proveedor,</p>

        <p>Hemos recibido su orden de compra y necesitamos su confirmación para proceder con el procesamiento.</p>

        <div class="po-details">
            <h3>Detalles de la Orden de Compra</h3>
            <p><strong>Número de Orden:</strong> {{ $po->order_number }}</p>
            <p><strong>Fecha de Orden:</strong> {{ $po->order_date ? $po->order_date->format('d/m/Y') : 'No especificada' }}</p>
            <p><strong>Total:</strong> ${{ number_format($po->total, 2) }}</p>
            <p><strong>Moneda:</strong> {{ $po->currency ?? 'USD' }}</p>
            <p><strong>Fecha de Entrega Requerida:</strong> {{ $po->date_required_in_destination ? $po->date_required_in_destination->format('d/m/Y') : 'No especificada' }}</p>
        </div>

        <div class="warning">
            <p><strong>⚠️ Importante:</strong> Este enlace expirará en {{ $expiryHours }} horas por razones de seguridad.</p>
        </div>

        <div style="text-align: center;">
            <a href="{{ $confirmationUrl }}" class="btn">Confirmar Orden de Compra</a>
        </div>

        <p>Si tiene alguna pregunta o necesita modificar la fecha de entrega, puede hacerlo a través del enlace de confirmación.</p>

        <p>Gracias por su atención.</p>

        <p>Saludos cordiales,<br>
        <strong>Equipo de Raga Orders</strong></p>
    </div>

    <div class="footer">
        <p>Este es un email automático. Por favor no responda a este mensaje.</p>
        <p>Si tiene problemas con el enlace, contacte a nuestro equipo de soporte.</p>
    </div>
</body>
</html>
