<x-app-layout>
    <div class="flex items-center justify-between">
        <x-view-title>
            <x-slot:title>
                Puertos
            </x-slot:title>

            <x-slot:content>
                Gestiona todos los puertos
            </x-slot:content>
        </x-view-title>
    </div>

    <div class="mt-4">
        <livewire:maestros.ports-table />
    </div>
</x-app-layout>

