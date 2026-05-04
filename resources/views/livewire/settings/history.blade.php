<div>
    <x-slot:header>
        <x-view-title>
            <x-slot:title>
                Histórico General
            </x-slot:title>

            <x-slot:content>
                Visualiza todos los comentarios y archivos adjuntos de todas las órdenes de compra
            </x-slot:content>
        </x-view-title>
    </x-slot:header>

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div class="flex min-w-0 flex-1 flex-wrap items-center gap-4">
            <div class="relative w-full max-w-xs sm:w-64">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por orden, usuario u operación..."
                    class="w-full rounded-xl border-2 border-[#A5A3A3] py-[0.625rem] pl-11 pr-10 placeholder:text-[#28C7A1] focus:border-[#1AAD8A] focus:outline-none"
                />
                <div class="pointer-events-none absolute left-[1.125rem] top-1/2 flex -translate-y-1/2 items-center">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                @if (!empty($search))
                    <button
                        type="button"
                        wire:click="$set('search', '')"
                        class="absolute right-2 top-1/2 flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                        title="Limpiar búsqueda"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        <div class="flex gap-4">
            <x-primary-button
                type="button"
                class="group flex items-center gap-2"
                x-on:click="window.location.reload()"
            >
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18.453 10.8927C18.1752 13.5026 16.6964 15.9483 14.2494 17.3611C10.1839 19.7083 4.98539 18.3153 2.63818 14.2499L2.38818 13.8168M1.54613 9.10664C1.82393 6.49674 3.30272 4.05102 5.74971 2.63825C9.8152 0.29104 15.0137 1.68398 17.3609 5.74947L17.6109 6.18248M1.49316 16.0657L2.22521 13.3336L4.95727 14.0657M15.0424 5.93364L17.7744 6.66569L18.5065 3.93364" stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Actualizar
            </x-primary-button>
        </div>
    </div>

    @include('livewire.components.reusable-table')

    @livewire('partials.activity-detail-modal')
</div>
