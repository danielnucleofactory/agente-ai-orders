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
            maestro-catalog-key="transport-types"
            :headers="$headers"
            :sortable="$sortable"
            :searchable="$searchable"
            :filterable="$filterable"
            :filterOptions="$filterOptions"
        />
    </div>
</x-app-layout>

