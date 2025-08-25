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

        <a href="{{ route('vendors.create') }}">
            <x-primary-button>
                Nuevo Proveedor
            </x-primary-button>
        </a>
    </div>

    <!-- Tabs for switching between views -->
    <div x-data="{ activeTab: 'kanban' }" class="mb-6">
        <!-- Kanban View -->
        <div x-show="activeTab === 'kanban'" class="mt-4">
            @php
                $headers = [
                    'name' => 'Nombre',
                    'email' => 'Email',
                    'contact_person' => 'Contacto',
                    'address' => 'Dirección',
                    'phone' => 'Teléfono',
                    'status' => 'Estado',
                    'actions' => 'Acciones',
                    'actions_html' => '',
                ];

                $sortable = ['name', 'email', 'phone', 'status'];
                $searchable = ['name', 'email', 'phone', 'status'];
                $filterable = ['name', 'email', 'phone', 'status'];
                $filterOptions = ['name', 'email', 'phone', 'status'];
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
                :model="\App\Models\Vendor::class"
            />
        </div>
    </div>

</x-app-layout>
