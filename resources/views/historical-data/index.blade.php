<x-app-layout>
    <div class="w-full">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0 flex-1">
                <x-view-title>
                    <x-slot:title>
                        Histórico de Datos
                    </x-slot:title>

                    <x-slot:content>
                        Visualiza y gestiona los datos históricos de órdenes de compra
                    </x-slot:content>
                </x-view-title>
            </div>

            <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                @can('has_import_historical_data')
                    @livewire('historical-data.upload-csv-form', [], key('historical-upload-csv'))
                @endcan
                <button type="button" onclick="exportHistoricalData()"
                    class="inline-flex h-10 shrink-0 items-center whitespace-nowrap rounded-lg bg-[#1AAD8A] px-4 text-sm font-medium text-white transition-colors duration-200 hover:bg-[#159a7a]">
                    <svg class="mr-2 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Descargar Excel
                </button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm w-full">
            <div class="p-6">
                @livewire('tables.historical-data-table')
            </div>
        </div>
    </div>
</x-app-layout>

