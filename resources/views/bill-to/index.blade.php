<x-app-layout>
    @php
        $etapaArray = ["e1" => "Etapa 1", "e2" => "Etapa 2"];
    @endphp

    <div class="flex items-center justify-between">
        <x-view-title>
            <x-slot:title>
                Gestión de Direcciones de facturación
            </x-slot:title>

            <x-slot:content>
                Gestiona todas las direcciones de facturación
            </x-slot:content>
        </x-view-title>

        <a href="{{ route('bill-to.create') }}">
            <x-primary-button>
                Nueva Dirección de facturación
            </x-primary-button>
        </a>
    </div>

    <nav class="px-6 py-4 text-lg bg-white rounded-2xl">
        <ul class="flex items-center justify-between max-w-screen-md mx-auto">
            <a href="{{ route('ship-to.index') }}" class="border-b-2 border-transparent">
                Direcciones de entrega
            </a>
            <a href="{{ route('bill-to.index') }}" class="border-b-2 border-[#190FDB] text-[#190FDB]">
                Direcciones de facturación
            </a>
        </ul>
    </nav>

    <!-- Tabs for switching between views -->
    <div x-data="{ activeTab: 'kanban' }" class="mb-6">
        <!-- Kanban View -->
        <div x-show="activeTab === 'kanban'" class="mt-4">
            @php
                $headers = [
                    'name' => 'Nombre',
                    'email' => 'Email',
                    'contact_person' => 'Persona de contacto',
                    'address' => 'Dirección',
                    'country' => 'País',
                    'phone' => 'Teléfono',
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
                :baseRoute="'bill-to'"
                :model="\App\Models\BillTo::class"
            />
        </div>
    </div>
</x-app-layout>
