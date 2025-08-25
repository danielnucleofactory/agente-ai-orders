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
                        <input wire:model.live.debounce.300ms="search" type="text" id="search" class="block w-full pl-10 border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Buscar órdenes...">
                    </div>
                </div>

                <div>
                    <label for="statusFilter" class="sr-only">Filtrar por estado</label>
                    <select wire:model.live="statusFilter" id="statusFilter" class="block w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todos los estados</option>
                        <option value="draft">Borrador</option>
                        <option value="pending">Pendiente</option>
                        <option value="approved">Aprobada</option>
                        <option value="shipped">Enviada</option>
                        <option value="delivered">Entregada</option>
                    </select>
                </div>

                <!-- Columnas visibles -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" type="button" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
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
                                    <input type="checkbox" wire:model.live="visibleColumns.order_number" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Número de Orden</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.vendor" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Vendor</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.status" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Estado</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.order_date" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Fecha de Orden</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.total" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Total</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.updated_at" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Ultima edición</span>
                                </label>
                            </div>
                            <div class="px-4 py-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="visibleColumns.actions" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Acciones</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label for="perPage" class="sr-only">Por página</label>
                <select wire:model.live="perPage" id="perPage" class="block w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
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
                <thead class="bg-[#E0E5FF]">
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

                        @if($visibleColumns['actions'])
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                            Acciones
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
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5
                                    {{ $order->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $order->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $order->status === 'shipped' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $order->status === 'delivered' ? 'bg-purple-100 text-purple-800' : '' }}
                                ">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            @endif

                            @if($visibleColumns['order_date'])
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $order->order_date ? $order->order_date->format('d/m/Y') : 'N/A' }}
                            </td>
                            @endif

                            @if($visibleColumns['total'])
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $order->total ? number_format($order->total, 2) : 'N/A' }}
                            </td>
                            @endif

                            @if($visibleColumns['updated_at'])
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $order->updated_at ? $order->updated_at->format('d/m/Y / H:i') : 'N/A' }}
                            </td>
                            @endif

                            @if($visibleColumns['actions'])
                            <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                <a href="{{ route('purchase-orders.detail', $order->id) }}" class="text-indigo-600 hover:text-indigo-900">Ver</a>
                                <a href="{{ route('purchase-orders.edit', $order->id) }}" class="ml-4 text-indigo-600 hover:text-indigo-900">Editar</a>
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
                                <button wire:click="gotoPage({{ $i }})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium {{ $purchaseOrders->currentPage() === $i ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'text-gray-700 hover:bg-gray-50' }}">
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
                                <button wire:click="gotoPage({{ $i }})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium {{ $currentPage === $i ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'text-gray-700 hover:bg-gray-50' }}">
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
</div>
