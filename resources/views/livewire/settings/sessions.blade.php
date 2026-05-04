<div class="space-y-6 rounded-2xl bg-white p-8">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-[#1AAD8A]">Lista de sesiones</h2>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-100 p-4 text-green-700">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-100 p-4 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="relative w-fit max-w-full">
            <input
                wire:model.live.debounce.300ms="search"
                class="rounded-xl border-2 border-[#A5A3A3] py-[0.625rem] pl-11 pr-[1.125rem] placeholder:text-[#28C7A1]"
                placeholder="Buscar usuario, dispositivo o IP"
            />

            <div class="pointer-events-none absolute left-[1.125rem] top-1/2 flex -translate-y-1/2 items-center">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        @if ($search)
            <button type="button" wire:click="$set('search', '')" class="text-sm text-[#1AAD8A] hover:text-[#0F614D]">
                Limpiar búsqueda
            </button>
        @endif
    </div>

    @include('livewire.components.reusable-table')

    <x-modal-success name="modal-session-closed">
        <x-slot:title>
            Sesión cerrada correctamente
        </x-slot:title>

        <x-slot:description>
            La sesión ha sido cerrada correctamente
        </x-slot:description>

        <x-primary-button wire:click="closeModal" class="w-full">
            Cerrar
        </x-primary-button>
    </x-modal-success>
</div>
