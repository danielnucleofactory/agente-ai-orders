<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden Confirmada - {{ $po->order_number ?? 'Raga Orders' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-green-100">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    ¡Orden Confirmada!
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Raga Orders
                </p>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">
                                Confirmación Exitosa
                            </h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p>Su orden de compra ha sido confirmada exitosamente.</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if(isset($po))
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Detalles de la Orden</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Número de Orden:</span>
                            <span class="font-medium">{{ $po->order_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Fecha de Orden:</span>
                            <span class="font-medium">{{ $po->order_date ? $po->order_date->format('d/m/Y') : 'No especificada' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total:</span>
                            <span class="font-medium">${{ number_format($po->total, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Moneda:</span>
                            <span class="font-medium">{{ $po->currency ?? 'USD' }}</span>
                        </div>
                        @if($po->update_date_po)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nueva Fecha de Entrega:</span>
                            <span class="font-medium text-blue-600">{{ $po->update_date_po->format('d/m/Y') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-600">Fecha de Confirmación:</span>
                            <span class="font-medium">{{ now()->format('d/m/Y H:i:s') }}</span>
                        </div>
                    </div>
                </div>
                @endif

                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                Próximos Pasos
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Nuestro equipo procesará su orden y se pondrá en contacto con usted si es necesario.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Gracias por confirmar su orden de compra.
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
