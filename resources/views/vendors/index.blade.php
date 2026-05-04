@php
    $etapaArray = ["e1" => "Etapa 1", "e2" => "Etapa 2"];
@endphp

<x-app-layout>
    <div class="flex items-center justify-between">
        <x-view-title>
            <x-slot:title>
                Gestión de Proveedores
            </x-slot:title>

            <x-slot:content>
                Gestiona todos los proveedores
            </x-slot:content>
        </x-view-title>

        @can('has_create_vendors')
            <a href="{{ route('vendors.create') }}">
                <x-primary-button>
                    Nuevo Proveedor
                </x-primary-button>
            </a>
        @endcan
    </div>

    <!-- Tabs for switching between views -->
    <div x-data="{ activeTab: 'kanban' }" class="mb-6">
        <!-- Kanban View -->
        <div x-show="activeTab === 'kanban'" class="mt-4">
            @php
                $headers = [
                    'name' => 'Nombre',
                    'vendo_code' => 'Código',
                    'email' => 'Email',
                    'contact_person' => 'Contacto',
                    'address' => 'Dirección',
                    'phone' => 'Teléfono',
                    'status' => 'Estado',
                    'actions' => 'Acciones',
                ];

                $sortable = [];
                $searchable = ['name', 'email', 'phone', 'status', 'vendo_code', 'contact_person', 'address'];
                $filterable = [];
                $filterOptions = [];
            @endphp


            <livewire:components.reusable-table
                :headers="$headers"
                :sortable="$sortable"
                :searchable="$searchable"
                :filterable="$filterable"
                :filterOptions="$filterOptions"
                :actions="true"
                :actionsView="false"
                :actionsEdit="true"
                :actionsDelete="true"
                :editPermission="'has_edit_vendors'"
                :deletePermission="'has_delete_vendors'"
                :model="\App\Models\Vendor::class"
            />
        </div>
    </div>

</x-app-layout>
