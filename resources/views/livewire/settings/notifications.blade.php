<div>
<div class="p-6 bg-white rounded-2xl">
    <h2 class="mb-6 text-2xl font-bold">Notificaciones</h2>

    <form wire:submit.prevent="save">
        @if (session()->has('message'))
            <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Notificaciones activas -->
        <div class="mb-8">
            <h4 class="text-lg font-bold text-[#7288FF] mb-4">Notificaciones activas</h4>

            <div class="space-y-6">
                @foreach($activeNotifications as $key => $notification)
                    @if(isset($preferences[$key]))
                        <div class="flex items-start gap-4">
                            <button type="button" wire:click="togglePreference('{{ $key }}')" class="toggle-button">
                                <div class="w-12 h-6 rounded-full transition-all {{ $preferences[$key]['enabled'] ?? false ? 'bg-[#7288FF]' : 'bg-gray-300' }} relative">
                                    <div class="w-4 h-4 bg-white rounded-full absolute top-1 transition-all {{ $preferences[$key]['enabled'] ?? false ? 'right-1' : 'left-1' }}"></div>
                                </div>
                            </button>
                            <div>
                                <p class="text-gray-800">{{ $notification['name'] }}: {{ $notification['description'] }}</p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="flex justify-end">
            <button
                type="submit"
                class="w-full primary-btn h-[46px] bg-[#565AFF] rounded-[6px] text-white hover:bg-[#565AFF]/80 transition-colors duration-300"
            >
                Guardar preferencias
            </button>
        </div>
    </form>
</div>

<x-modal-success name="modal-notifications-saved">
    <x-slot:title class="text-center">
        Preferencias de notificaciones guardadas correctamente
    </x-slot:title>

    <x-primary-button wire:click="$dispatch('close-modal', 'modal-notifications-saved')" class="w-full">
        Cerrar
    </x-primary-button>
</x-modal-success>

<style>
/* Estilos para los botones de alternancia */
.toggle-button {
    display: inline-block;
    height: 24px;
    outline: none;
    border: none;
    background: transparent;
    cursor: pointer;
    padding: 0;
}
</style>
</div>
