<div
    x-data="{ open: false }"
    @click.away="open = false"
    @keydown.escape.window="open = false"
    class="relative"
>
    <button
        @click="open = !open"
        type="button"
        class="group relative flex items-center gap-[0.625rem] rounded-md bg-indigo-600 px-4 py-2 text-white transition-colors duration-300 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="20" viewBox="0 0 22 20" fill="none">
            <path
                d="M1 2.6C1 2.03995 1 1.75992 1.10899 1.54601C1.20487 1.35785 1.35785 1.20487 1.54601 1.10899C1.75992 1 2.03995 1 2.6 1H19.4C19.9601 1 20.2401 1 20.454 1.10899C20.6422 1.20487 20.7951 1.35785 20.891 1.54601C21 1.75992 21 2.03995 21 2.6V3.26939C21 3.53819 21 3.67259 20.9672 3.79756C20.938 3.90831 20.8901 4.01323 20.8255 4.10776C20.7526 4.21443 20.651 4.30245 20.4479 4.4785L14.0521 10.0215C13.849 10.1975 13.7474 10.2856 13.6745 10.3922C13.6099 10.4868 13.562 10.5917 13.5328 10.7024C13.5 10.8274 13.5 10.9618 13.5 11.2306V16.4584C13.5 16.6539 13.5 16.7517 13.4685 16.8363C13.4406 16.911 13.3953 16.9779 13.3363 17.0315C13.2695 17.0922 13.1787 17.1285 12.9971 17.2012L9.59711 18.5612C9.22957 18.7082 9.0458 18.7817 8.89827 18.751C8.76927 18.7242 8.65605 18.6476 8.58325 18.5377C8.5 18.4122 8.5 18.2142 8.5 17.8184V11.2306C8.5 10.9618 8.5 10.8274 8.46715 10.7024C8.43805 10.5917 8.39014 10.4868 8.32551 10.3922C8.25258 10.2856 8.15102 10.1975 7.94789 10.0215L1.55211 4.4785C1.34898 4.30245 1.24742 4.21443 1.17449 4.10776C1.10986 4.01323 1.06195 3.90831 1.03285 3.79756C1 3.67259 1 3.53819 1 3.26939V2.6Z"
                stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="transition-colors duration-500 group-disabled:stroke-[#C2C2C2]" />
        </svg>

        <span>Filtros</span>

        @if($filterCount > 0)
            <span class="absolute flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-indigo-500 rounded-full -right-2 -top-2">
                {{ $filterCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown de filtros -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute right-0 z-50 p-4 mt-2 origin-top-right bg-white rounded-md shadow-lg w-72 ring-1 ring-black ring-opacity-5 focus:outline-none"
        style="display: none;"
    >
        <div class="pb-4 mb-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Filtrar Documentos</h3>
            <p class="mt-1 text-sm text-gray-500">Selecciona los filtros que deseas aplicar</p>
        </div>

        <div class="space-y-4">
            <!-- Filtro de Moneda -->
            @if(count($currencies) > 0)
                <div>
                    <label for="currency-filter" class="block text-sm font-medium text-gray-700">Moneda</label>
                    <select id="currency-filter" wire:model.live="selectedCurrency" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todas las monedas</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency }}">{{ $currency }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Filtro de Incoterms -->
            @if(count($incoterms) > 0)
                <div>
                    <label for="incoterm-filter" class="block text-sm font-medium text-gray-700">Incoterms</label>
                    <select id="incoterm-filter" wire:model.live="selectedIncoterm" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todos los incoterms</option>
                        @foreach($incoterms as $incoterm)
                            <option value="{{ $incoterm }}">{{ $incoterm }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Filtro de Hub Planificado -->
            @if(count($plannedHubs) > 0)
                <div>
                    <label for="planned-hub-filter" class="block text-sm font-medium text-gray-700">Hub Planificado</label>
                    <select id="planned-hub-filter" wire:model.live="selectedPlannedHub" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todos los hubs planificados</option>
                        @foreach($plannedHubs as $hubId => $hubName)
                            <option value="{{ $hubId }}">{{ $hubName }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Filtro de Hub Real -->
            @if(count($actualHubs) > 0)
                <div>
                    <label for="actual-hub-filter" class="block text-sm font-medium text-gray-700">Hub Real</label>
                    <select id="actual-hub-filter" wire:model.live="selectedActualHub" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todos los hubs reales</option>
                        @foreach($actualHubs as $hubId => $hubName)
                            <option value="{{ $hubId }}">{{ $hubName }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Filtro de Tipo de Material -->
            @if(count($materialTypes) > 0)
                <div>
                    <label for="material-type-filter" class="block text-sm font-medium text-gray-700">Tipo de Material</label>
                    <select id="material-type-filter" wire:model.live="selectedMaterialType" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todos los tipos de material</option>
                        @foreach($materialTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="flex justify-between pt-3">
                <button
                    wire:click="applyFilters"
                    type="button"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Aplicar filtros
                </button>

                <button
                    wire:click="resetFilters"
                    type="button"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Limpiar filtros
                </button>
            </div>
        </div>
    </div>
</div>
