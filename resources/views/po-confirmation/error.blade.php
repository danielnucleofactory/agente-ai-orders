<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Error' }} - Raga Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-red-100">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    {{ $title ?? 'Error' }}
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Raga Orders
                </p>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                {{ $title ?? 'Error' }}
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>{{ $message ?? 'Ha ocurrido un error inesperado.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                ¿Qué hacer ahora?
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Si cree que esto es un error, contacte a nuestro equipo de soporte.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Si necesita ayuda, contacte a nuestro equipo de soporte.
                    </p>
                    <p class="text-xs text-gray-500 mt-2">
                        Puede cerrar esta ventana de forma segura.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
