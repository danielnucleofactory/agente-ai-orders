<div>
    <div class="space-y-4">
        <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between">
            <x-search-input 
                class="w-full md:w-64" 
                wire:model.debounce.300ms="search" 
                placeholder="Buscar por orden, proveedor, contenedor..." 
            />

            <div class="flex flex-wrap gap-4">
                <select wire:model.live="filters.vendor" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1AAD8A]">
                    <option value="">Todos los proveedores</option>
                    @foreach($vendors as $vendorId => $vendorName)
                        <option value="{{ $vendorId }}">{{ $vendorName }}</option>
                    @endforeach
                </select>

                <input 
                    type="date" 
                    wire:model.live="filters.date_from" 
                    class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1AAD8A]"
                    placeholder="Fecha desde"
                />

                <input 
                    type="date" 
                    wire:model.live="filters.date_to" 
                    class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1AAD8A]"
                    placeholder="Fecha hasta"
                />

                <select wire:model.live="filters.trading_company" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1AAD8A]">
                    <option value="">Todas las empresas</option>
                    @foreach($tradingCompanies as $company)
                        <option value="{{ $company }}">{{ $company }}</option>
                    @endforeach
                </select>

                <select wire:model.live="perPage" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1AAD8A]">
                    <option value="10">10 por página</option>
                    <option value="25">25 por página</option>
                    <option value="50">50 por página</option>
                    <option value="100">100 por página</option>
                </select>
            </div>
        </div>

        <!-- Debug info (temporal) -->
        @if(config('app.debug'))
            <div class="mb-4 p-2 bg-yellow-100 text-xs">
                Total: {{ $historicalData->total() }}, 
                En página: {{ $historicalData->count() }}, 
                Página: {{ $historicalData->currentPage() }}
            </div>
        @endif

        <!-- Tabla -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-[#D4F5ED]">
                    <tr>
                        <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-black uppercase">Orden</th>
                        <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-black uppercase">Proveedor</th>
                        <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-black uppercase">Fecha Emisión</th>
                        <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-black uppercase">Total Neto</th>
                        <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-black uppercase">Contenedor</th>
                        <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-black uppercase">ETD</th>
                        <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-black uppercase">ETA</th>
                        <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-black uppercase">Empresa</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($historicalData as $record)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                {{ $record->order_number }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div class="max-w-xs truncate" title="{{ $record->vendor_name }}">
                                    {{ $record->vendor_name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $record->emision_date_po ? formatDate($record->emision_date_po) : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                @if($record->net_total)
                                    {{ number_format($record->net_total, 2) }} {{ $record->currency ?? '' }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $record->container_number ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $record->date_etd ? formatDate($record->date_etd) : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $record->date_eta ? formatDate($record->date_eta) : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $record->trading_company ?? 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                No se encontraron registros históricos
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="flex items-center justify-between mt-4">
            <div class="flex justify-between flex-1 sm:hidden">
                <button 
                    wire:click="previousPage" 
                    @if($historicalData->onFirstPage()) disabled @endif 
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 {{ $historicalData->onFirstPage() ? 'opacity-50 cursor-not-allowed' : '' }}">
                    Anterior
                </button>
                <button 
                    wire:click="nextPage" 
                    @if(!$historicalData->hasMorePages()) disabled @endif 
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 {{ !$historicalData->hasMorePages() ? 'opacity-50 cursor-not-allowed' : '' }}">
                    Siguiente
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando
                        <span class="font-medium">{{ $historicalData->firstItem() ?? 0 }}</span>
                        a
                        <span class="font-medium">{{ $historicalData->lastItem() ?? 0 }}</span>
                        de
                        <span class="font-medium">{{ $historicalData->total() }}</span>
                        resultados
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <!-- Botón Anterior -->
                        <button 
                            wire:click="previousPage" 
                            @if($historicalData->onFirstPage()) disabled @endif 
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 {{ $historicalData->onFirstPage() ? 'opacity-50 cursor-not-allowed' : '' }}">
                            <span class="sr-only">Anterior</span>
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Números de página -->
                        @for ($i = 1; $i <= $historicalData->lastPage(); $i++)
                            <button 
                                wire:click="gotoPage({{ $i }})" 
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium {{ $historicalData->currentPage() === $i ? 'z-10 bg-[#D4F5ED] border-[#1AAD8A] text-[#1AAD8A]' : 'text-gray-700 hover:bg-gray-50' }}">
                                {{ $i }}
                            </button>
                        @endfor

                        <!-- Botón Siguiente -->
                        <button 
                            wire:click="nextPage" 
                            @if(!$historicalData->hasMorePages()) disabled @endif 
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 {{ !$historicalData->hasMorePages() ? 'opacity-50 cursor-not-allowed' : '' }}">
                            <span class="sr-only">Siguiente</span>
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

