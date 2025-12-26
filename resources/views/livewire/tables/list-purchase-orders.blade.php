<div class="p-[32px] bg-white rounded-lg mt-8">
    <div class="space-y-4">
        <!-- Filtros -->
        <div class="flex items-center justify-between">
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
                    <select wire:model.live="statusFilter" id="statusFilter"
                            class="block w-full border-gray-300 rounded-md focus:border-[#1AAD8A] focus:ring-[#1AAD8A] sm:text-sm">
                        <option value="">Todas las etapas</option>
                        <option value="__trashed">Anuladas</option>
                        <option value="__no_kanban">Sin etapa</option>
                        @foreach($kanbanStatuses as $kanbanStatus)
                            <option value="kanban_{{ $kanbanStatus->id }}">{{ $kanbanStatus->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Columnas visibles -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" type="button" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1AAD8A] focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 -ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                        Columnas visibles
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 z-10 mt-2 origin-top-right bg-white rounded-md shadow-lg w-60">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.order_number" class="w-4 h-4 text-[#1AAD8A] border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Número de Orden</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.vendor" class="w-4 h-4 text-[#1AAD8A] border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Vendor</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.status" class="w-4 h-4 text-[#1AAD8A] border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Estado</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.order_date" class="w-4 h-4 text-[#1AAD8A] border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Fecha de Orden</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.total" class="w-4 h-4 text-[#1AAD8A] border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Total</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.updated_at" class="w-4 h-4 text-[#1AAD8A] border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Ultima edición</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.route_label" class="w-4 h-4 text-[#1AAD8A] border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Ruta logística</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.mbl_number" class="w-4 h-4 text-[#1AAD8A] border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Master BL</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.container_number" class="w-4 h-4 text-[#1AAD8A] border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Número de contenedor</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.customer" class="w-4 h-4 text-[#1AAD8A] border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Cliente</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.actions" class="w-4 h-4 text-[#1AAD8A] border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Acciones</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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

        <!-- Tabla -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-[#D4F5ED]">
                    <tr>
                        @if($visibleColumns['order_number'])
                        <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
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
                        @endif

                        @if($visibleColumns['vendor'])
                        <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('vendor_id')">
                                <span>Vendor</span>
                                @if ($sortField === 'vendor_id')
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
                        @endif

                        @if($visibleColumns['status'])
                        <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('status')">
                                <span>Estado</span>
                                @if ($sortField === 'status')
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
                        @endif

                        @if($visibleColumns['order_date'])
                        <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('order_date')">
                                <span>Fecha de Orden</span>
                                @if ($sortField === 'order_date')
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
                        @endif

                        @if($visibleColumns['total'])
                        <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('total')">
                                <span>Total</span>
                                @if ($sortField === 'total')
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
                        @endif

                        @if($visibleColumns['updated_at'])
                        <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('updated_at')">
                                <span>Ultima edición</span>
                                @if ($sortField === 'updated_at')
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
                        @endif

                        @if($visibleColumns['route_label'])
                        <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('route_label')">
                                <span>Ruta logística</span>
                                @if ($sortField === 'route_label')
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
                        @endif

                        @if($visibleColumns['mbl_number'])
                        <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('mbl_number')">
                                <span>Master BL</span>
                                @if ($sortField === 'mbl_number')
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
                        @endif

                        @if($visibleColumns['container_number'])
                        <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('container_number')">
                                <span>Número de contenedor</span>
                                @if ($sortField === 'container_number')
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
                        @endif

                        @if($visibleColumns['customer'])
                        <th scope="col" class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('bill_to_id')">
                                <span>Cliente</span>
                                @if ($sortField === 'bill_to_id')
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
                        @endif

                        @if($visibleColumns['actions'])
                                <th scope="col" class="px-6 py-3 text-xs font-bold text-black uppercase tracking-wider text-center">
                                    ACCIONES
                                </th>
                            @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($purchaseOrders as $order)
                        <tr>
                            @if($visibleColumns['order_number'])
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                {{ $order->order_number }}
                            </td>
                            @endif

                            @if($visibleColumns['vendor'])
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $order->vendor_id ?? 'N/A' }}
                            </td>
                            @endif

                            @if($visibleColumns['status'])
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                    @if(!empty($order->deleted_at))  {{-- PO soft-deleted --}}
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-red-100 text-red-800">
                                            Anulado
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5
                                            {{ $order->kanbanStatus ? 'bg-[#D4F5ED] text-[#0F614D]' : 'bg-gray-100 text-gray-800' }}
                                        ">
                                            {{ $order->kanbanStatus ? $order->kanbanStatus->name : 'Sin etapa' }}
                                        </span>
                                    @endif
                                </td>
                            @endif

                            @if($visibleColumns['order_date'])
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ formatDate($order->order_date) }}
                            </td>
                            @endif

                            @if($visibleColumns['total'])
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $order->total ? number_format($order->total, 2) : 'N/A' }}
                            </td>
                            @endif

                            @if($visibleColumns['updated_at'])
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $order->updated_at ? formatDateTime($order->updated_at) : 'N/A' }}
                            </td>
                            @endif

                            @if($visibleColumns['route_label'])
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $order->route_label ?? 'N/A' }}
                            </td>
                            @endif

                            @if($visibleColumns['mbl_number'])
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $order->mbl_number ?? 'N/A' }}
                            </td>
                            @endif

                            @if($visibleColumns['container_number'])
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $order->container_number ?? 'N/A' }}
                            </td>
                            @endif

                            @if($visibleColumns['customer'])
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $order->billTo->name ?? 'N/A' }}
                            </td>
                            @endif

                                @if($visibleColumns['actions'])
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-center">
                                        <div class="inline-flex items-center justify-center gap-4">
                                            @if(!empty($order->deleted_at))
                                                {{-- PO anulada: no mostrar botón restaurar --}}
                                            @else
                                                <a href="{{ route('purchase-orders.detail', $order->id) }}"
                                                   class="text-[#1AAD8A] hover:text-[#0F614D]">Ver</a>

                                                <a href="{{ route('purchase-orders.edit', $order->id) }}"
                                                   class="text-[#1AAD8A] hover:text-[#0F614D]">Editar</a>

                                                @can('has_delete_orders')
                                                    <button type="button"
                                                            wire:click="confirmDelete({{ $order->id }})"
                                                            class="text-red-600 hover:text-red-800">Eliminar</button>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count(array_filter($visibleColumns)) }}" class="px-6 py-4 text-sm text-center text-gray-500">
                                No se encontraron órdenes de compra
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="flex items-center justify-between mt-6">
            <div class="flex justify-between flex-1 sm:hidden">
                <button wire:click="previousPage" @if($purchaseOrders->onFirstPage()) disabled @endif class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 {{ $purchaseOrders->onFirstPage() ? 'opacity-50 cursor-not-allowed' : '' }}">
                    Anterior
                </button>
                <button wire:click="nextPage" @if(!$purchaseOrders->hasMorePages()) disabled @endif class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 {{ !$purchaseOrders->hasMorePages() ? 'opacity-50 cursor-not-allowed' : '' }}">
                    Siguiente
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando
                        <span class="font-medium">{{ $purchaseOrders->firstItem() ?? 0 }}</span>
                        a
                        <span class="font-medium">{{ $purchaseOrders->lastItem() ?? 0 }}</span>
                        de
                        <span class="font-medium">{{ $purchaseOrders->total() }}</span>
                        resultados
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <!-- Botón Anterior -->
                        <button wire:click="previousPage" @if($purchaseOrders->onFirstPage()) disabled @endif class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 {{ $purchaseOrders->onFirstPage() ? 'opacity-50 cursor-not-allowed' : '' }}">
                            <span class="sr-only">Anterior</span>
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Números de página (mostrar solo si hay pocas páginas para evitar sobrecarga) -->
                        @if($purchaseOrders->lastPage() <= 10)
                            @for ($i = 1; $i <= $purchaseOrders->lastPage(); $i++)
                                <button wire:click="gotoPage({{ $i }})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium {{ $purchaseOrders->currentPage() === $i ? 'z-10 bg-[#D4F5ED] border-[#1AAD8A] text-[#1AAD8A]' : 'text-gray-700 hover:bg-gray-50' }}">
                                    {{ $i }}
                                </button>
                            @endfor
                        @else
                            <!-- Paginación inteligente para muchas páginas -->
                            @php
                                $currentPage = $purchaseOrders->currentPage();
                                $lastPage = $purchaseOrders->lastPage();
                                $start = max(1, $currentPage - 2);
                                $end = min($lastPage, $currentPage + 2);
                            @endphp

                            @if($start > 1)
                                <button wire:click="gotoPage(1)" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                                    1
                                </button>
                                @if($start > 2)
                                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300">
                                        ...
                                    </span>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                <button wire:click="gotoPage({{ $i }})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium {{ $currentPage === $i ? 'z-10 bg-[#D4F5ED] border-[#1AAD8A] text-[#1AAD8A]' : 'text-gray-700 hover:bg-gray-50' }}">
                                    {{ $i }}
                                </button>
                            @endfor

                            @if($end < $lastPage)
                                @if($end < $lastPage - 1)
                                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300">
                                        ...
                                    </span>
                                @endif
                                <button wire:click="gotoPage({{ $lastPage }})" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                                    {{ $lastPage }}
                                </button>
                            @endif
                        @endif

                        <!-- Botón Siguiente -->
                        <button wire:click="nextPage" @if(!$purchaseOrders->hasMorePages()) disabled @endif class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 {{ !$purchaseOrders->hasMorePages() ? 'opacity-50 cursor-not-allowed' : '' }}">
                            <span class="sr-only">Siguiente</span>
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    {{-- Flash message simple (opcional) --}}
    @if (session('message'))
        <div class="mt-4 rounded-md bg-green-50 p-4 text-green-800">
            {{ session('message') }}
        </div>
    @endif

    {{-- Modal de confirmación --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/30"></div>

            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                <div class="px-6 pt-6">
                    <h3 class="text-lg font-semibold">
                        {{ $confirmMode === 'restore' ? 'Restaurar Orden de compra' : 'Eliminar Orden de compra' }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        @if($confirmMode === 'restore')
                            ¿Seguro que deseas restaurar la Orden de Compra N° #{{ $confirmOrderNumber }}?
                        @else
                            ¿Seguro que deseas eliminar la Orden de Compra N° #{{ $confirmOrderNumber }}?
                        @endif
                    </p>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4">
                    <button type="button"
                            wire:click="cancelConfirm"
                            class="px-4 py-2 text-sm font-medium bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                        Cancelar
                    </button>

                    @if($confirmMode === 'restore')
                        <button type="button"
                                wire:click="restoreConfirmed"
                                class="px-4 py-2 text-sm font-semibold text-white rounded-md bg-green-600 hover:bg-green-700">
                            Restaurar
                        </button>
                    @else
                        <button type="button"
                                wire:click="deleteConfirmed"
                                class="px-4 py-2 text-sm font-semibold text-white rounded-md bg-red-600 hover:bg-red-700">
                            Eliminar
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
