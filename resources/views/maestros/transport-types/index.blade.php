<x-app-layout>
    <div class="flex items-center justify-between">
        <x-view-title>
            <x-slot:title>
                Tipos de Transporte
            </x-slot:title>

            <x-slot:content>
                Gestiona todos los tipos de transporte
            </x-slot:content>
        </x-view-title>
    </div>

    <div class="mt-4">
        <livewire:maestros.transport-types-table />
    </div>
</x-app-layout>

