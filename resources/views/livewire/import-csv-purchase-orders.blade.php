<div>
    {{-- Botón Importar CSV --}}
    <button
        wire:click="openModal"
        class="inline-flex items-center px-4 py-2.5 bg-white border-2 border-[#1AAD8A] text-[#1AAD8A] font-medium rounded-lg hover:bg-[#D4F5ED] transition-colors duration-200 w-[209px] justify-center">
        <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        Importar CSV
    </button>

    {{-- Modal --}}
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
        wire:key="import-csv-modal">

        {{-- Overlay --}}
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        {{-- Modal Container --}}
        <div class="flex min-h-full items-center justify-center p-4">
            <div
                class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
                x-on:click.stop>

                {{-- Header --}}
                <div class="bg-[#D4F5ED] px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">
                            Importar Ordenes de Compra desde CSV
                        </h3>
                        <button
                            wire:click="closeModal"
                            @if($importing) disabled @endif
                            class="text-gray-400 hover:text-gray-500 disabled:opacity-50">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4">
                    {{-- Instrucciones (solo si no hay resultados) --}}
                    @if(!$importResult)
                        <div class="mb-4 text-sm text-gray-500">
                            <p class="font-medium mb-1">Formato esperado del CSV:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Separador: punto y coma (<code class="bg-gray-100 px-1 rounded">;</code>)</li>
                                <li>Primera fila: headers (ORDER_NUMBER, TRADING_COMPANY, etc.)</li>
                                <li>Fechas en formato DD-MM-YY o DD/MM/YYYY</li>
                                <li>Booleanos: Y / N</li>
                                <li>Decimales en formato europeo (ej: 22.127,28)</li>
                            </ul>
                        </div>
                    @endif

                    {{-- File input --}}
                    @if(!$importing && !$importResult)
                        <div class="mb-4">
                            <label for="importCsvFile" class="block text-sm font-medium text-gray-700 mb-2">
                                Seleccionar archivo CSV
                            </label>
                            <input
                                type="file"
                                id="importCsvFile"
                                wire:model="csvFile"
                                accept=".csv,.txt"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#1AAD8A] file:text-white hover:file:bg-[#0F614D] file:cursor-pointer border border-gray-300 rounded-lg">

                            @error('csvFile')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            @if($csvFile)
                                <p class="mt-2 text-sm text-green-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ $csvFile->getClientOriginalName() }}
                                </p>
                            @endif
                        </div>
                    @endif

                    {{-- Estado de importación --}}
                    @if($importing)
                        <div class="mb-4">
                            <div class="flex items-center justify-center py-8">
                                <div class="text-center">
                                    <svg class="animate-spin h-10 w-10 text-[#1AAD8A] mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <p class="text-sm font-medium text-gray-700">Enviando {{ $totalRows }} POs al endpoint bulk...</p>
                                    <p class="text-xs text-gray-500 mt-1">Esto puede tomar un momento</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Resultados finales --}}
                    @if($importResult && !$importing)
                        <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">Resultado de la importación</h4>

                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div class="text-center p-3 bg-white rounded-lg border">
                                    <div class="text-2xl font-bold text-gray-800">{{ $importResult['total'] }}</div>
                                    <div class="text-xs text-gray-500 mt-1">Total</div>
                                </div>
                                <div class="text-center p-3 bg-white rounded-lg border border-green-200">
                                    <div class="text-2xl font-bold text-green-600">{{ $importResult['success'] }}</div>
                                    <div class="text-xs text-gray-500 mt-1">Exitosas</div>
                                </div>
                                <div class="text-center p-3 bg-white rounded-lg border {{ $importResult['failed'] > 0 ? 'border-red-200' : '' }}">
                                    <div class="text-2xl font-bold {{ $importResult['failed'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $importResult['failed'] }}</div>
                                    <div class="text-xs text-gray-500 mt-1">Fallidas</div>
                                </div>
                            </div>

                            {{-- Log detallado --}}
                            @if(count($importLog) > 0)
                                <div class="max-h-60 overflow-y-auto border rounded-lg">
                                    <table class="w-full text-xs">
                                        <thead class="bg-gray-100 sticky top-0">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-medium text-gray-600">PO</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-600">Estado</th>
                                                <th class="px-3 py-2 text-left font-medium text-gray-600">Detalle</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($importLog as $log)
                                                <tr class="{{ $log['status'] === 'ok' ? 'bg-green-50' : 'bg-red-50' }} border-t">
                                                    <td class="px-3 py-1.5 font-mono font-medium">{{ $log['order'] }}</td>
                                                    <td class="px-3 py-1.5">
                                                        @if($log['status'] === 'ok')
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">OK</span>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Error</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-1.5 text-gray-700">{{ $log['message'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            {{-- Errores detallados --}}
                            @if(!empty($importResult['errors']))
                                <div class="mt-3">
                                    <p class="text-sm font-medium text-red-600 mb-1">Mensajes de error:</p>
                                    <ul class="text-xs text-red-600 list-disc list-inside max-h-32 overflow-y-auto bg-red-50 p-3 rounded">
                                        @foreach($importResult['errors'] as $error)
                                            <li class="mb-1">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-3">
                    @if($importResult && !$importing)
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-white bg-[#1AAD8A] rounded-lg hover:bg-[#0F614D]">
                            Cerrar
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="closeModal"
                            @if($importing) disabled @endif
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50">
                            Cancelar
                        </button>
                        <button
                            type="button"
                            wire:click="importCsv"
                            wire:loading.attr="disabled"
                            wire:target="importCsv,csvFile"
                            @if(!$csvFile || $importing) disabled @endif
                            class="px-5 py-2 text-sm font-medium text-white bg-[#1AAD8A] rounded-lg hover:bg-[#0F614D] disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="importCsv" class="flex items-center">
                                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Importar
                            </span>
                            <span wire:loading wire:target="importCsv" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Procesando...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
