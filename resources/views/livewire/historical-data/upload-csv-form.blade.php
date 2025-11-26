<div>
    <!-- Botón para abrir modal -->
    <button 
        wire:click="openModal"
        class="inline-flex items-center px-4 py-2 bg-[#1AAD8A] hover:bg-[#0F614D] text-white font-medium rounded-lg transition-colors duration-200">
        <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        Cargar CSV Histórico
    </button>

    <!-- Modal -->
    <div 
        x-data="{ show: @entangle('showModal') }"
        x-show="show"
        x-cloak
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
        wire:key="upload-modal">
            
            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <!-- Modal Container -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div 
                    class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                    x-on:click.stop>
                    
                    <!-- Header -->
                    <div class="bg-[#D4F5ED] px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900">
                                Cargar Datos Históricos desde CSV
                            </h3>
                            <button 
                                wire:click="closeModal"
                                class="text-gray-400 hover:text-gray-500">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-4">
                        @if (session()->has('message'))
                            <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg border border-green-300">
                                {{ session('message') }}
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg border border-red-300">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if (session()->has('warning'))
                            <div class="mb-4 p-4 text-sm text-yellow-700 bg-yellow-100 rounded-lg border border-yellow-300">
                                {{ session('warning') }}
                            </div>
                        @endif

                        <form wire:submit.prevent="uploadCsv" class="space-y-4">
                            <div>
                                <label for="csvFile" class="block text-sm font-medium text-gray-700 mb-2">
                                    Seleccionar archivo CSV
                                </label>
                                <input 
                                    type="file" 
                                    id="csvFile"
                                    wire:model="csvFile"
                                    accept=".csv,.txt"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#1AAD8A] file:text-white hover:file:bg-[#0F614D] file:cursor-pointer border border-gray-300 rounded-lg">
                                
                                @error('csvFile')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                @if($csvFile)
                                    <p class="mt-2 text-sm text-gray-600">
                                        Archivo seleccionado: {{ $csvFile->getClientOriginalName() }}
                                    </p>
                                @endif
                            </div>

                            <div class="text-sm text-gray-500">
                                <p class="font-medium mb-1">Requisitos del archivo:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Formato CSV con separador punto y coma (;)</li>
                                    <li>Primera fila debe contener los headers</li>
                                    <li>Tamaño máximo: 50MB</li>
                                    <li>Los registros duplicados (mismo order_number + trading_company) serán saltados</li>
                                </ul>
                            </div>

                            @if($importResult)
                                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                    <h4 class="font-medium text-gray-900 mb-2">Resultados de la importación:</h4>
                                    <ul class="text-sm text-gray-700 space-y-1">
                                        <li>Total procesados: <span class="font-medium">{{ $importResult['total'] }}</span></li>
                                        <li>Importados: <span class="font-medium text-green-600">{{ $importResult['imported'] }}</span></li>
                                        <li>Duplicados saltados: <span class="font-medium text-yellow-600">{{ $importResult['skipped'] }}</span></li>
                                        @if($importResult['errors'] > 0)
                                            <li>Errores: <span class="font-medium text-red-600">{{ $importResult['errors'] }}</span></li>
                                        @endif
                                    </ul>

                                    @if(!empty($importResult['error_messages']))
                                        <div class="mt-3">
                                            <p class="text-sm font-medium text-red-600 mb-1">Mensajes de error:</p>
                                            <ul class="text-xs text-red-600 list-disc list-inside max-h-32 overflow-y-auto">
                                                @foreach(array_slice($importResult['error_messages'], 0, 10) as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                                @if(count($importResult['error_messages']) > 10)
                                                    <li>... y {{ count($importResult['error_messages']) - 10 }} errores más</li>
                                                @endif
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="flex justify-end gap-3 pt-4">
                                <button 
                                    type="button"
                                    wire:click="closeModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Cancelar
                                </button>
                                <button 
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="uploadCsv"
                                    class="px-4 py-2 text-sm font-medium text-white bg-[#1AAD8A] rounded-lg hover:bg-[#0F614D] disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="uploadCsv">Cargar</span>
                                    <span wire:loading wire:target="uploadCsv">
                                        <span class="inline-flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Procesando...
                                        </span>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

