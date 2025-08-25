<x-app-layout>
    <div class="flex items-center justify-between">
        <x-view-title>
            <x-slot:title>
                Gestión de Forecast
            </x-slot:title>

            <x-slot:content>
                Revisa y actualiza el forecast de productos
            </x-slot:content>
        </x-view-title>
    </div>

    <div class="flex hidden gap-4">
        <x-search-input />

        <x-primary-button class="group flex items-center gap-[0.625rem]">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="20" viewBox="0 0 22 20" fill="none">
                <path
                    d="M1 2.6C1 2.03995 1 1.75992 1.10899 1.54601C1.20487 1.35785 1.35785 1.20487 1.54601 1.10899C1.75992 1 2.03995 1 2.6 1H19.4C19.9601 1 20.2401 1 20.454 1.10899C20.6422 1.20487 20.7951 1.35785 20.891 1.54601C21 1.75992 21 2.03995 21 2.6V3.26939C21 3.53819 21 3.67259 20.9672 3.79756C20.938 3.90831 20.8901 4.01323 20.8255 4.10776C20.7526 4.21443 20.651 4.30245 20.4479 4.4785L14.0521 10.0215C13.849 10.1975 13.7474 10.2856 13.6745 10.3922C13.6099 10.4868 13.562 10.5917 13.5328 10.7024C13.5 10.8274 13.5 10.9618 13.5 11.2306V16.4584C13.5 16.6539 13.5 16.7517 13.4685 16.8363C13.4406 16.911 13.3953 16.9779 13.3363 17.0315C13.2695 17.0922 13.1787 17.1285 12.9971 17.2012L9.59711 18.5612C9.22957 18.7082 9.0458 18.7817 8.89827 18.751C8.76927 18.7242 8.65605 18.6476 8.58325 18.5377C8.5 18.4122 8.5 18.2142 8.5 17.8184V11.2306C8.5 10.9618 8.5 10.8274 8.46715 10.7024C8.43805 10.5917 8.39014 10.4868 8.32551 10.3922C8.25258 10.2856 8.15102 10.1975 7.94789 10.0215L1.55211 4.4785C1.34898 4.30245 1.24742 4.21443 1.17449 4.10776C1.10986 4.01323 1.06195 3.90831 1.03285 3.79756C1 3.67259 1 3.53819 1 3.26939V2.6Z"
                    stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="transition-colors duration-500 group-disabled:stroke-[#C2C2C2]" />
            </svg>
            <span>Filtros</span>
        </x-primary-button>
    </div>

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
                    'material' => 'Material',
                    'short_text' => 'Descripción',
                    'supplying_plant' => 'Planta de Suministro',
                    'uom_real' => 'UOM Real',
                    'quantity_requested' => 'Cantidad Solicitada',
                    'delivery_date' => 'Fecha de Entrega',
                    'unit_of_measure' => 'Unidad de Medida',
                    'plant' => 'Planta',
                    'vendor_name' => 'Proveedor',
                    'vendor_code' => 'Código Proveedor',
                    'actions' => 'Acciones',
                    'actions_html' => '',
                ];

                $sortable = ['release_date', 'material', 'short_text', 'purchase_requisition', 'supplying_plant', 'uom_real', 'quantity_requested', 'delivery_date', 'unit_of_measure', 'plant', 'planned_delivery_time', 'mrp_controller', 'vendor_name', 'vendor_code'];
                $searchable = ['release_date', 'material', 'short_text', 'purchase_requisition', 'supplying_plant', 'uom_real', 'quantity_requested', 'delivery_date', 'unit_of_measure', 'plant', 'planned_delivery_time', 'mrp_controller', 'vendor_name', 'vendor_code'];
                $filterable = ['release_date', 'material', 'short_text', 'purchase_requisition', 'supplying_plant', 'uom_real', 'quantity_requested', 'delivery_date', 'unit_of_measure', 'plant', 'planned_delivery_time', 'mrp_controller', 'vendor_name', 'vendor_code'];
                $filterOptions = ['release_date', 'material', 'short_text', 'purchase_requisition', 'supplying_plant', 'uom_real', 'quantity_requested', 'delivery_date', 'unit_of_measure', 'plant', 'planned_delivery_time', 'mrp_controller', 'vendor_name', 'vendor_code'];
            @endphp


            <livewire:components.reusable-table :headers="$headers" :sortable="$sortable" :searchable="$searchable" :filterable="$filterable"
                :filterOptions="$filterOptions" :actions="true" :actionsView="false" :actionsEdit="true" :actionsDelete="true"
                :baseRoute="'products.forecast'" :model="\App\Models\Forecast::class" />
        </div>
    </div>
</x-app-layout>
