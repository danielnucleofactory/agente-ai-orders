<div>
    @if (session()->has('error'))
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500">
            <li class="mr-2">
                <a href="#" wire:click.prevent="setTab('route_label')"
                   class="inline-flex p-4 border-b-2 rounded-t-lg group {{ $activeTab === 'route_label' ? 'text-[#1AAD8A] border-[#1AAD8A]' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                    Por Ruta Logística
                </a>
            </li>
            <li class="mr-2">
                <a href="#" wire:click.prevent="setTab('container_number')"
                   class="inline-flex p-4 border-b-2 rounded-t-lg group {{ $activeTab === 'container_number' ? 'text-[#1AAD8A] border-[#1AAD8A]' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                    Por Contenedor
                </a>
            </li>
            <li class="mr-2">
                <a href="#" wire:click.prevent="setTab('actual')"
                   class="inline-flex p-4 border-b-2 rounded-t-lg group {{ $activeTab === 'actual' ? 'text-[#1AAD8A] border-[#1AAD8A]' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                    General
                </a>
            </li>
        </ul>
    </div>

    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-4">
            <div>
                <label for="search" class="sr-only">Buscar</label>
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" id="search" class="block w-full pl-10 border-gray-300 rounded-md focus:border-[#1AAD8A] focus:ring-[#1AAD8A] sm:text-sm" placeholder="Buscar órdenes...">
                </div>
            </div>

            <div>
                <label for="statusFilter" class="sr-only">Filtrar por estado</label>
                <select wire:model.live="statusFilter" id="statusFilter" class="block w-full border-gray-300 rounded-md focus:border-[#1AAD8A] focus:ring-[#1AAD8A] sm:text-sm">
                    <option value="">Todos los estados</option>
                    @foreach($this->getAvailableStatuses() as $status => $label)
                        <option value="{{ $status }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="flex items-center space-x-4">
            @if(count($selected) > 0)
                <span class="text-sm font-medium text-gray-700">
                    {{ count($selected) }} {{ count($selected) === 1 ? 'orden seleccionada' : 'órdenes seleccionadas' }}
                </span>
            @endif

            <button
                wire:click="openReleaseModal"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#1AAD8A] border border-transparent rounded-md shadow-sm hover:bg-[#127A62] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1AAD8A] @if(count($selected) === 0) opacity-50 cursor-not-allowed @endif"
                @if(count($selected) === 0) disabled @endif
            >
                Crear Documento de Embarque
            </button>

            <div>
                <label for="perPage" class="sr-only">Por página</label>
                <select wire:model.live="perPage" id="perPage" class="block w-full border-gray-300 rounded-md focus:border-[#1AAD8A] focus:ring-[#1AAD8A] sm:text-sm">
                    <option value="10">10 por página</option>
                    <option value="25">25 por página</option>
                    <option value="50">50 por página</option>
                    <option value="100">100 por página</option>
                </select>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[#D4F5ED]">
                <tr>
                    <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase">
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                wire:model.live="selectAll"
                                class="w-4 h-4 text-[#1AAD8A] border-gray-300 rounded focus:ring-[#1AAD8A]"
                            >
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase">
                        <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('order_number')">
                            <span>Número de Orden</span>
                            @if ($sortField === 'order_number')
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    @if ($sortDirection === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    @endif
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase">
                        <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('vendor_id')">
                            <span>Vendor</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase">
                        <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('kanban_status_id')">
                            <span>Estado</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase">
                        <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('order_date')">
                            <span>Fecha de Orden</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase">
                        <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('{{ $activeTab === 'route_label' ? 'route_label' : 'container_number' }}')">
                            <span>{{ $activeTab === 'route_label' ? 'Ruta Logística' : 'Número de Contenedor' }}</span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($purchaseOrders as $order)
                    <tr style="{{ isset($order->color) ? 'background-color:' . $order->color : '' }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                            <input
                                type="checkbox"
                                wire:model.live="selected"
                                value="{{ $order->id }}"
                                class="w-4 h-4 text-[#1AAD8A] border-gray-300 rounded focus:ring-[#1AAD8A]"
                            >
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                            {{ $order->order_number }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                            {{ $order->vendor->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5
                                {{ $order->kanbanStatus ? 'bg-[#D4F5ED] text-[#0F614D]' : 'bg-gray-100 text-gray-800' }}
                            ">
                                {{ $order->kanbanStatus ? $order->kanbanStatus->name : 'Sin etapa' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                            {{ $order->order_date ? formatDate($order->order_date) : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                            @if($activeTab === 'route_label')
                                {{ $order->route_label ?: 'Sin ruta' }}
                            @elseif($activeTab === 'container_number')
                                {{ $order->container_number ?: 'Sin contenedor' }}
                            @else
                                {{ $order->route_label ?: 'Sin ruta' }}
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                            <a href="/purchase-orders/{{ $order->id }}/detail" class="text-[#1AAD8A] hover:text-[#0F614D]">Ver</a>
                            <a href="/purchase-orders/{{ $order->id }}/edit" class="ml-4 text-[#1AAD8A] hover:text-[#0F614D]">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500">
                            No se encontraron órdenes de compra
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between mt-4">
        {{ $purchaseOrders->links() }}
    </div>

    <x-modal name="modal-hub-teorico" maxWidth="lg">
        <h3 class="mb-2 text-lg font-bold text-center text-light-blue">
            Agrega fecha de release
        </h3>

        <div class="mb-4">
            <x-date-picker wire:model="release_date" label="Fecha de release" :error="$errors->first('release_date')" />
        </div>

        <div class="mb-4">
            <x-form-textarea label="" name="comment_release" wireModel="comment_release" placeholder="Comentarios" />
        </div>

        <div class="mb-12 space-y-2">
            <label class="block mb-2 text-sm font-medium text-gray-700">
                Adjuntar documentación (opcional)
            </label>

            <div class="flex items-center justify-center w-full">
                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="mb-1 text-sm text-gray-500">
                            <span class="font-semibold">Haz clic para subir</span> o arrastra y suelta
                        </p>
                        <p class="text-xs text-gray-500">XLS, XLSX, PDF (máx. 5MB)</p>
                    </div>
                    <input id="dropzone-file" wire:model="file" type="file" class="hidden" />
                </label>
            </div>

            @error('file')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @if($file)
                <div class="flex items-center justify-between p-2 mt-2 bg-gray-100 rounded-md">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-sm text-gray-700 truncate">{{ $file->getClientOriginalName() }}</span>
                    </div>
                    <button type="button" wire:click="$set('file', null)" class="text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endif

            <div class="flex flex-col text-sm text-[#A5A3A3]">
                <span>Tipo de formato .xls .xlsx .pdf</span>
                <span>Tamaño máximo 5MB</span>
            </div>
        </div>

        <div class="flex gap-[1.875rem]">
            <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-hub-teorico')" class="w-full">
                Cancelar
            </x-secondary-button>

            <x-primary-button
                class="w-full"
                wire:click="addReleaseDate" >
                Continuar
            </x-primary-button>
        </div>
    </x-modal>

    <x-modal-success name="modal-consolidate-order">
        <x-slot:title>
            Orden consolidada correctamente
        </x-slot:title>

        <x-slot:description>
            La orden ha sido consolidada correctamente
        </x-slot:description>

        <x-primary-button wire:click="$dispatch('close-modal', 'modal-consolidate-order')" class="w-full">
            Cerrar
        </x-primary-button>
    </x-modal-success>
</div>