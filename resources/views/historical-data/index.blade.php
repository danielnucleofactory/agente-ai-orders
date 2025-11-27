<x-app-layout>
    <div class="w-full">
        <div class="flex justify-between items-center mb-6">
            <x-view-title>
                <x-slot:title>
                    Histórico de Datos
                </x-slot:title>

                <x-slot:content>
                    Visualiza y gestiona los datos históricos de órdenes de compra
                </x-slot:content>
            </x-view-title>

            <div class="flex gap-2 items-center">
                @can('has_import_historical_data')
                    @livewire('historical-data.upload-csv-form')
                @endcan
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm w-full">
            <div class="p-6">
                @livewire('tables.historical-data-table')
            </div>
        </div>
    </div>
</x-app-layout>

