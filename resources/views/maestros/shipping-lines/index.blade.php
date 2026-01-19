<x-app-layout>
    <div class="flex items-center justify-between">
        <x-view-title>
            <x-slot:title>
                Líneas de Envío
            </x-slot:title>

            <x-slot:content>
                Gestiona todas las líneas de envío
            </x-slot:content>
        </x-view-title>
    </div>

    <div class="mt-4">
        <livewire:maestros.shipping-lines-table />
    </div>
</x-app-layout>

