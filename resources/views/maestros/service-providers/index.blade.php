<x-app-layout>
    <div class="flex items-center justify-between">
        <x-view-title>
            <x-slot:title>
                Proveedores de Servicio
            </x-slot:title>

            <x-slot:content>
                Gestiona todos los proveedores de servicio
            </x-slot:content>
        </x-view-title>
    </div>

    <div class="mt-4">
        <livewire:maestros.service-providers-table />
    </div>
</x-app-layout>

