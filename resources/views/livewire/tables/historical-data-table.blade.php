<div id="lw-historical-data-table">
    @push('styles')
        <style>
            /* Estilos para filtros de historical-data - igualar al dashboard */
            .historical-filters-section {
                display: flex;
                align-items: flex-end;
                gap: 12px;
                flex-wrap: nowrap;
                font-family: 'Lato', sans-serif;
                margin-bottom: 24px;
                width: 100%;
                min-width: 0;
                overflow-x: auto;
                overflow-y: visible;
                padding-bottom: 4px;
                -webkit-overflow-scrolling: touch;
            }
            
            .historical-filters-section .filter-group {
                flex: 0 1 9rem;
                min-width: 0;
                width: auto !important;
                max-width: none !important;
            }
            
            .historical-filters-section .filter-label {
                color: #1AAD8A;
                font-size: 14px;
                display: block;
                margin-bottom: 4px;
            }
            
            .historical-filters-section .search-input-wrapper {
                flex: 1 1 8rem;
                min-width: 0;
                width: auto !important;
                max-width: none !important;
            }
            
            .historical-filters-section .search-input-wrapper > div,
            .historical-filters-section .search-input-wrapper .relative {
                width: 100% !important;
                min-width: 0 !important;
                max-width: none !important;
                position: relative !important;
            }
            
            .historical-filters-section .search-input-wrapper input,
            .historical-filters-section .search-input-wrapper input[type="text"] {
                width: 100% !important;
                min-width: 0 !important;
                max-width: none !important;
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
            .historical-filters-section .filter-group input.flatpickr-alt-input,
            .historical-filters-section input.flatpickr-alt-input {
                width: 100% !important;
                max-width: none !important;
                min-width: 0 !important;
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
                width: 100% !important;
                max-width: none !important;
                min-width: 0 !important;
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
                <x-date-picker wire:model.live="filters.date_from" label="Fecha desde" />
            </div>

            <div class="filter-group">
                <x-date-picker wire:model.live="filters.date_to" label="Fecha hasta" />
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

        @include('livewire.components.reusable-table')
    </div>

    @push('scripts')
    <script>
        function exportHistoricalData() {
            const root = document.getElementById('lw-historical-data-table');
            if (!root) {
                return;
            }
            const wireId = root.getAttribute('wire:id');
            if (!wireId || typeof Livewire === 'undefined') {
                return;
            }
            const component = Livewire.find(wireId);
            if (!component) {
                return;
            }
            const search = component.get('search') ?? '';
            const filters = component.get('filters') ?? {};
            
            // Construir la URL con los parámetros
            const params = new URLSearchParams();
            
            if (search) {
                params.append('search', search);
            }
            
            if (filters.vendor) {
                params.append('vendor', filters.vendor);
            }
            
            if (filters.date_from) {
                params.append('date_from', filters.date_from);
            }
            
            if (filters.date_to) {
                params.append('date_to', filters.date_to);
            }
            
            if (filters.trading_company) {
                params.append('trading_company', filters.trading_company);
            }
            
            // Construir la URL completa
            const url = '{{ route("historical-data.export") }}' + (params.toString() ? '?' + params.toString() : '');
            
            // Descargar el archivo
            window.location.href = url;
        }
    </script>
    @endpush
</div>

