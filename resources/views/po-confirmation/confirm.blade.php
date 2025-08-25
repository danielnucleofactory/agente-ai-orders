<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Orden de Compra - {{ $po->order_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-blue-100">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Confirmar Orden de Compra
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Raga Orders
                </p>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
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
                        <div class="flex justify-between">
                            <span class="text-gray-600">Fecha de Entrega Original:</span>
                            <span class="font-medium">{{ $po->date_required_in_destination ? $po->date_required_in_destination->format('d/m/Y') : 'No especificada' }}</span>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('po.confirm.process', $hash) }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="new_delivery_date" class="block text-sm font-medium text-gray-700">
                            Nueva Fecha de Entrega (Opcional)
                        </label>
                        <input type="date"
                               name="new_delivery_date"
                               id="new_delivery_date"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <p class="mt-1 text-xs text-gray-500">
                            Si necesita cambiar la fecha de entrega, seleccione una nueva fecha.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">
                                        Se encontraron errores:
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    Importante
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Al confirmar esta orden, usted acepta los términos y condiciones establecidos.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        <button type="submit"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                            Confirmar Orden
                        </button>
                    </div>
                </form>
            </div>

            <div class="text-center">
                <p class="text-xs text-gray-500">
                    Este enlace expirará en {{ config('po-confirmation.hash_expiry_hours', 72) }} horas por razones de seguridad.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
