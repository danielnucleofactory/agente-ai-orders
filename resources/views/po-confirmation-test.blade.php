<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test PO Confirmation Module</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8 px-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Test del Módulo PO Confirmation</h1>

        @livewire('po-confirmation-manager')
    </div>

    @livewireScripts
</body>
</html>
