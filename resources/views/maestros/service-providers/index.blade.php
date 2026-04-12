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
        @php
            $headers = [
                'name' => 'Nombre',
                'olo_id' => 'OLO ID',
                'company' => 'Compañía',
                'active' => 'Estado',
            ];
            $sortable = [];
            $searchable = ['name', 'olo_id', 'company', 'active'];
            $filterable = ['active'];
            $filterOptions = [
                'active' => [
                    'true' => 'Activo',
                    'false' => 'Inactivo',
                ],
            ];
        @endphp

        <livewire:components.reusable-table
            maestro-catalog-key="service-providers"
            :headers="$headers"
            :sortable="$sortable"
            :searchable="$searchable"
            :filterable="$filterable"
            :filterOptions="$filterOptions"
        />
    </div>
</x-app-layout>

