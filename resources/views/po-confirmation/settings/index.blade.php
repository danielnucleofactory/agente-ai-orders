<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-medium text-gray-900">
                    Configuración de Confirmación de PO
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Configura los parámetros para el sistema de confirmación de órdenes de compra.
                </p>
            </div>

            <div class="bg-gray-50 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">
                <form wire:submit.prevent="saveSettings" class="space-y-6">

                    <!-- Mensajes de éxito/error -->
                    @if (session()->has('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if (session()->has('info'))
                        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('info') }}</span>
                        </div>
                    @endif

                    <!-- Configuración de Timing -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Configuración de Tiempo
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label for="days_before_confirmation" class="block text-sm font-medium text-gray-700">
                                    Días antes de la fecha de entrega
                                </label>
                                <input type="number"
                                       id="days_before_confirmation"
                                       wire:model="timingSettings.days_before_confirmation"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       min="1" max="30">
                                @error('timingSettings.days_before_confirmation')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">
                                    Número de días antes de la fecha de entrega para enviar el email de confirmación.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de Email -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Configuración de Email
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label for="email_subject" class="block text-sm font-medium text-gray-700">
                                    Asunto del Email
                                </label>
                                <input type="text"
                                       id="email_subject"
                                       wire:model="emailSettings.email_subject"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="Confirmación de Orden de Compra - {order_number}">
                                @error('emailSettings.email_subject')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">
                                    Use {order_number} para incluir el número de orden.
                                </p>
                            </div>

                            <div>
                                <label for="email_greeting" class="block text-sm font-medium text-gray-700">
                                    Saludo
                                </label>
                                <input type="text"
                                       id="email_greeting"
                                       wire:model="emailSettings.email_greeting"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="Estimado proveedor,">
                                @error('emailSettings.email_greeting')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email_body" class="block text-sm font-medium text-gray-700">
                                    Cuerpo del Email
                                </label>
                                <textarea id="email_body"
                                          wire:model="emailSettings.email_body"
                                          rows="4"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                          placeholder="Le informamos que tenemos una orden de compra pendiente de confirmación..."></textarea>
                                @error('emailSettings.email_body')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email_footer" class="block text-sm font-medium text-gray-700">
                                    Pie de Página
                                </label>
                                <textarea id="email_footer"
                                          wire:model="emailSettings.email_footer"
                                          rows="2"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                          placeholder="Si tiene alguna pregunta, no dude en contactarnos..."></textarea>
                                @error('emailSettings.email_footer')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Configuración General -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Mensajes del Sistema
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label for="success_message" class="block text-sm font-medium text-gray-700">
                                    Mensaje de Éxito
                                </label>
                                <input type="text"
                                       id="success_message"
                                       wire:model="generalSettings.success_message"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="¡Orden de compra confirmada exitosamente!">
                                @error('generalSettings.success_message')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="expired_message" class="block text-sm font-medium text-gray-700">
                                    Mensaje de Enlace Expirado
                                </label>
                                <input type="text"
                                       id="expired_message"
                                       wire:model="generalSettings.expired_message"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="El enlace de confirmación ha expirado...">
                                @error('generalSettings.expired_message')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="not_found_message" class="block text-sm font-medium text-gray-700">
                                    Mensaje de PO No Encontrada
                                </label>
                                <input type="text"
                                       id="not_found_message"
                                       wire:model="generalSettings.not_found_message"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="Orden de compra no encontrada.">
                                @error('generalSettings.not_found_message')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex justify-between items-center pt-6">
                        <button type="button"
                                wire:click="resetToDefaults"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Restaurar Valores por Defecto
                        </button>

                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Guardar Configuraciones
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Ocultar mensaje de éxito después de 3 segundos
    document.addEventListener('livewire:init', () => {
        Livewire.on('show-success-message', () => {
            setTimeout(() => {
                @this.set('showSuccessMessage', false);
            }, 3000);
        });
    });
</script>
