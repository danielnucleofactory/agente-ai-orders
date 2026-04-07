<div>
    <div class="flex items-center justify-between">
        <x-view-title>
            <x-slot:title>
                Gestión de Empresas
            </x-slot:title>

            <x-slot:content>
                Administra las empresas del sistema
            </x-slot:content>
        </x-view-title>

        @can('has_create_companies')
            <a href="{{ route('settings.companies.create') }}">
                <x-primary-button>
                    Nueva Empresa
                </x-primary-button>
            </a>
        @endcan
    </div>

    @if (session()->has('message'))
        <div class="p-4 mb-4 text-green-700 bg-green-100 rounded-md">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 mb-4 text-red-700 bg-red-100 rounded-md">
            {{ session('error') }}
        </div>
    @endif

    <div class="mt-4">
        @php
            $headers = [
                'name' => 'Nombre',
                'country' => 'País',
                'city' => 'Ciudad',
                'phone' => 'Teléfono',
                'website' => 'Sitio Web',
                'users_count' => 'Usuarios',
                'actions' => 'Acciones',
                'actions_html' => '',
            ];

            $sortable = ['name', 'country', 'city'];
            $searchable = ['name', 'country', 'city', 'website'];
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
            :editPermission="'has_edit_companies'"
            :deletePermission="'has_delete_companies'"
            :baseRoute="'settings.companies'"
            :model="\App\Models\Company::class"
            :showSearch="auth()->user()?->can('filter') ?? false"
            :showPerPage="auth()->user()?->can('filter') ?? false"
        />
    </div>
</div>
