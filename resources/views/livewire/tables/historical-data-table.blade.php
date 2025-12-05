<div>
    @push('styles')
        <style>
            /* Estilos para filtros de historical-data - igualar al dashboard */
            .historical-filters-section {
                display: flex;
                align-items: flex-end;
                gap: 16px;
                flex-wrap: wrap;
                font-family: 'Lato', sans-serif;
                margin-bottom: 24px;
            }
            
            .historical-filters-section .filter-group {
                width: 150px !important;
                max-width: 150px !important;
            }
            
            .historical-filters-section .filter-label {
                color: #1AAD8A;
                font-size: 14px;
                display: block;
                margin-bottom: 4px;
            }
            
            /* Buscador 500x40 - Máxima especificidad */
            .historical-filters-section .search-input-wrapper {
                width: 500px !important;
                min-width: 500px !important;
                max-width: 500px !important;
            }
            
            .historical-filters-section .search-input-wrapper > div,
            .historical-filters-section .search-input-wrapper .relative {
                width: 500px !important;
                min-width: 500px !important;
                max-width: 500px !important;
                position: relative !important;
            }
            
            .historical-filters-section .search-input-wrapper input,
            .historical-filters-section .search-input-wrapper input[type="text"] {
                width: 500px !important;
                min-width: 500px !important;
                max-width: 500px !important;
                height: 40px !important;
                min-height: 40px !important;
                max-height: 40px !important;
                box-sizing: border-box !important;
                padding: 8px 14px 8px 44px !important;
                position: relative !important;
                z-index: 10 !important;
                pointer-events: auto !important;
                -webkit-appearance: none !important;
                -moz-appearance: none !important;
                appearance: none !important;
            }
            
            /* Inputs de fecha - altura exacta de 40px (incluyendo border) */
            .historical-filters-section .filter-group input[type="date"],
            .historical-filters-section .filter-group input.flatpickr-alt-input,
            .historical-filters-section input[type="date"],
            .historical-filters-section input.flatpickr-alt-input {
                width: 150px !important;
                max-width: 150px !important;
                min-width: 150px !important;
                height: 40px !important;
                min-height: 40px !important;
                max-height: 40px !important;
                padding: 0 14px !important;
                border: 2px solid #28C7A1 !important;
                border-radius: 10px !important;
                font-size: 16px !important;
                line-height: 36px !important;
                color: #222 !important;
                font-family: 'Lato', sans-serif !important;
                box-sizing: border-box !important;
                background: #fff !important;
                margin: 0 !important;
            }
            
            /* Selects - color #222 y altura 40px (incluyendo border) */
            .historical-filters-section .filter-group select,
            .historical-filters-section select {
                width: 150px !important;
                max-width: 150px !important;
                min-width: 150px !important;
                height: 40px !important;
                min-height: 40px !important;
                max-height: 40px !important;
                padding: 0 14px !important;
                border: 2px solid #28C7A1 !important;
                border-radius: 10px !important;
                font-size: 16px !important;
                line-height: 36px !important;
                color: #222 !important;
                font-family: 'Lato', sans-serif !important;
                box-sizing: border-box !important;
                background: #fff !important;
                -webkit-appearance: none !important;
                -moz-appearance: none !important;
                appearance: none !important;
                margin: 0 !important;
            }
            
            /* Asegurar que el select mantenga su estructura durante el morphing */
            .historical-filters-section select option {
                padding: 8px !important;
                color: #222 !important;
                background: #fff !important;
            }
            
            .historical-filters-section select:focus,
            .historical-filters-section input[type="date"]:focus,
            .historical-filters-section input.flatpickr-alt-input:focus {
                outline: none;
                border-color: #1AAD8A !important;
                box-shadow: 0 0 0 3px rgba(26, 173, 138, 0.1);
            }
            
            .historical-filters-section select option {
                padding: 8px;
                color: #222 !important;
            }
        </style>
    @endpush

    <div class="space-y-4">
        <!-- Filtros y búsqueda en una sola fila -->
        <div class="historical-filters-section">
            <!-- Buscador -->
            <div class="search-input-wrapper">
                <label for="historical-search-input" class="sr-only">Buscar</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="historical-search-input"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por orden, proveedor, contenedor..."
                        class="rounded-xl border-2 border-[#A5A3A3] pl-11 pr-[1.125rem] py-[0.625rem] placeholder:text-[#28C7A1] block w-full"
                        autocomplete="off"
                    />
                </div>
            </div>

            <!-- Filtros -->
            <div class="filter-group">
                <label class="filter-label">Fecha desde</label>
                <input 
                    type="date" 
                    wire:model.live="filters.date_from" 
                    placeholder="Fecha desde"
                />
            </div>

            <div class="filter-group">
                <label class="filter-label">Fecha hasta</label>
                <input 
                    type="date" 
                    wire:model.live="filters.date_to" 
                    placeholder="Fecha hasta"
                />
            </div>

            <div class="filter-group">
                <label class="filter-label">Proveedor</label>
                <select wire:model.live="filters.vendor">
                    <option value="">Todos los proveedores</option>
                    @foreach($vendors as $vendorId => $vendorName)
                        <option value="{{ $vendorId }}">{{ $vendorName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Empresa</label>
                <select wire:model.live="filters.trading_company">
                    <option value="">Todas las empresas</option>
                    @foreach($tradingCompanies as $company)
                        <option value="{{ $company }}">{{ $company }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Controles superiores: Por página -->
        <div class="flex justify-end mb-4">
            <div>
                <label for="perPage" class="sr-only">Por página</label>
                <select wire:model.live="perPage" id="perPage" class="block w-full border-gray-300 rounded-md focus:border-[#1AAD8A] focus:ring-[#1AAD8A] sm:text-sm">
                    <option value="10">10 por página</option>
                    <option value="25">25 por página</option>
                    <option value="50">50 por página</option>
                    <option value="100">100 por página</option>
                </select>
            </div>
        </div>

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

        <!-- Paginación - Estilo igual a purchase-orders -->
        <div class="flex items-center justify-between mt-6">
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

                        <!-- Números de página (mostrar solo si hay pocas páginas para evitar sobrecarga) -->
                        @if($historicalData->lastPage() <= 10)
                            @for ($i = 1; $i <= $historicalData->lastPage(); $i++)
                                <button 
                                    wire:click="gotoPage({{ $i }})" 
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium {{ $historicalData->currentPage() === $i ? 'z-10 bg-[#D4F5ED] border-[#1AAD8A] text-[#1AAD8A]' : 'text-gray-700 hover:bg-gray-50' }}">
                                    {{ $i }}
                                </button>
                            @endfor
                        @else
                            <!-- Paginación inteligente para muchas páginas -->
                            @php
                                $currentPage = $historicalData->currentPage();
                                $lastPage = $historicalData->lastPage();
                                $start = max(1, $currentPage - 2);
                                $end = min($lastPage, $currentPage + 2);
                            @endphp

                            @if($start > 1)
                                <button wire:click="gotoPage(1)" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                                    1
                                </button>
                                @if($start > 2)
                                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300">
                                        ...
                                    </span>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                <button wire:click="gotoPage({{ $i }})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium {{ $currentPage === $i ? 'z-10 bg-[#D4F5ED] border-[#1AAD8A] text-[#1AAD8A]' : 'text-gray-700 hover:bg-gray-50' }}">
                                    {{ $i }}
                                </button>
                            @endfor

                            @if($end < $lastPage)
                                @if($end < $lastPage - 1)
                                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300">
                                        ...
                                    </span>
                                @endif
                                <button wire:click="gotoPage({{ $lastPage }})" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                                    {{ $lastPage }}
                                </button>
                            @endif
                        @endif

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

