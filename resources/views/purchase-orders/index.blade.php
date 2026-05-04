<x-app-layout>

    <div id="po-index-content-defer"
        class="space-y-5 opacity-0 pointer-events-none transition-opacity duration-300 ease-out">
    <div class="flex justify-between items-center">
        <x-view-title>
            <x-slot:title>
                Órdenes de Compra
            </x-slot:title>

            <x-slot:content>
                Visualiza y administra las órdenes de compra
            </x-slot:content>
        </x-view-title>


        <div class="flex gap-2 items-center">

            {{-- <a href="{{ route('shipping-documentation.create') }}" class="hidden">
                <x-secondary-button>
                    Crear nuevo embarque
                </x-secondary-button>
            </a> --}}

            <a href="{{ route('purchase-orders.create') }}">
                <x-primary-button>
                    Nueva orden de compra
                </x-primary-button>
            </a>
        </div>
    </div>

    <div class="flex hidden justify-between items-center mb-6">
        <x-search-input class="w-64" wire:model.debounce.300ms="search" placeholder="Buscar comentarios o archivos..." />
    </div>

    <div class="" x-data="{activeTab: 'tab1'}">
        <!-- Selector de pestañas -->
        <div class="flex gap-6 justify-start items-center mb-3 text-lg font-bold">
            <div class="flex gap-6 items-center">
                <button @click="activeTab = 'tab1'"
                    :class="activeTab === 'tab1' ? 'border-dark-blue text-dark-blue' : 'border-transparent'"
                    class="border-b-2 py-[0.625rem]">
                    Etapas
                </button>
                <button @click="activeTab = 'tab2'"
                    :class="activeTab === 'tab2' ? 'border-dark-blue text-dark-blue' : 'border-transparent'"
                    class="border-b-2 py-[0.625rem]">
                    Reporte
                </button>
            </div>

            <div class="flex gap-4">
                <livewire:kanban.kanban-filters />
            </div>
        </div>

        <!-- Contenido de las pestañas -->
        <div>
            <div x-show="activeTab === 'tab1'" x-transition class="">
                <div id="po-kanban-stack" class="relative min-h-[22rem]">
                    <div
                        class="po-kanban-loading-overlay po-kanban-loading-overlay-visible absolute inset-0 z-30 flex flex-col items-center justify-start rounded-xl bg-[#F7F7F7]/50 pt-8 backdrop-blur-[2px] transition-opacity duration-300 ease-out opacity-100"
                        aria-busy="true"
                        role="status"
                    >
                        <div
                            class="po-kanban-loading-card flex flex-col items-center gap-4 rounded-xl border border-gray-200 bg-white px-10 py-8"
                        >
                            <div
                                class="h-11 w-11 rounded-full border-[3px] border-emerald-100 border-t-[#1AAD8A] animate-spin motion-reduce:animate-none"
                                aria-hidden="true"
                            ></div>
                            <p class="po-kanban-loading-message m-0 text-center text-sm font-semibold text-gray-700">
                                Cargando tablero de órdenes…
                            </p>
                        </div>
                    </div>
                    <div id="kboard" data-po-kanban-board-host>
                        {{-- Mount y loadData del tablero se difieren al cliente (Livewire lazy on-load). --}}
                        <livewire:kanban.kanban-board embed-board-type="po_stages" lazy="on-load" />
                    </div>
                </div>
            </div>

            <div x-show="activeTab === 'tab2'" x-transition class="space-y-[1.875rem]">
                <livewire:tables.list-purchase-orders />
            </div>
        </div>
    </div>
    </div>
</x-app-layout>
