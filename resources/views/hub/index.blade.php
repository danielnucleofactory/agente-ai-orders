<x-app-layout>
    @php
        $etapaArray = ["e1" => "Etapa 1", "e2" => "Etapa 2"];
    @endphp

    <div class="flex items-center justify-between">
        <x-view-title>
            <x-slot:title>
                Gestión de Hubs
            </x-slot:title>

            <x-slot:content>
                Gestiona todos los hubs
            </x-slot:content>
        </x-view-title>

        <a href="{{ route('hub.create') }}">
            <x-primary-button>
                Nuevo Hub
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
                    'code' => 'Código',
                    'country' => 'País',
                    'documentary_cut' => 'Corte documental',
                    'zarpe' => 'Zarpe',
                    'actions' => 'Acciones',
                    'actions_html' => '',
                ];

                $sortable = ['name', 'code', 'country', 'documentary_cut', 'zarpe'];
                $searchable = ['name', 'code', 'country', 'documentary_cut', 'zarpe'];
                $filterable = ['name', 'code', 'country', 'documentary_cut', 'zarpe'];
                $filterOptions = ['name', 'code', 'country', 'documentary_cut', 'zarpe'];
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
                :baseRoute="'hub'"
                :model="\App\Models\Hub::class"
            />
        </div>
    </div>
</x-app-layout>
