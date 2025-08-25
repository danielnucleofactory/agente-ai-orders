<div class="p-8 space-y-10 bg-white rounded-2xl">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-[#7288FF]">Lista de roles</h2>

        <div class="flex space-x-4">
            <a href="{{ route('settings.roles.create') }}">
                <x-primary-button class="flex items-center gap-[0.625rem]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M8 1V15M1 8H15" stroke="#F7F7F7" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    <span>Crear nuevo rol</span>
                </x-primary-button>
            </a>
        </div>
    </div>

    {{-- Lista --}}
    @if (session('message'))
        <div class="px-4 py-2 mb-4 text-green-700 bg-green-100 border border-green-400 rounded">
            {{ session('message') }}
        </div>
    @endif


    <!-- Tabs for switching between views -->
    <div x-data="{ activeTab: 'kanban' }" class="mb-6">
        <!-- Kanban View -->
        <div x-show="activeTab === 'kanban'" class="mt-4">
            @php
                $headers = [
                    'name' => 'Nombre del Rol',
                    'permissions_count' => 'Número de permisos',
                    'users_count' => 'Número de miembros',
                    'actions' => 'Acciones',
                ];

                $sortable = ['name', 'permissions_count', 'users_count'];
                $searchable = ['name'];
                $filterable = ['name'];
                $filterOptions = ['name'];
            @endphp

            <livewire:components.reusable-table :headers="$headers" :sortable="$sortable" :searchable="$searchable" :filterable="$filterable"
                :filterOptions="$filterOptions" :actions="true" :actionsView="false" :actionsEdit="true" :actionsDelete="true"
                :rows="$roles" :baseRoute="'settings.roles'"  :model="\Spatie\Permission\Models\Role::class" />
        </div>
    </div>
</div>
