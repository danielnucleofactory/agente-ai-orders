<x-app-layout>
    <div class="flex items-center justify-between">
        <x-view-title>
            <x-slot:title>
                Tipos de Tarifa
            </x-slot:title>

            <x-slot:content>
                Gestiona todos los tipos de tarifa
            </x-slot:content>
        </x-view-title>
    </div>

    <div class="mt-4">
        <livewire:maestros.rate-types-table />
    </div>
</x-app-layout>

