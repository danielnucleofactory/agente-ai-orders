<div>
    @if (session()->has('message'))
        <div class="p-4 mb-4 text-green-700 bg-green-100 rounded border border-green-400">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 mb-4 text-red-700 bg-red-100 rounded border border-red-400">
            {{ session('error') }}
        </div>
    @endif

    @can('filter')
    <div class="flex justify-between mb-4">
        <div class="flex items-center">
            <div class="relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar"
                    class="px-4 py-2 w-64 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <div class="flex absolute inset-y-0 right-0 items-center pr-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <select
                wire:model.live="activeFilter"
                class="px-4 py-2 ml-4 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Todos los estados</option>
                <option value="true">Activo</option>
                <option value="false">Inactivo</option>
            </select>
        </div>
        <div>
            <select
                wire:model="perPage"
                class="px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="10">10 por página</option>
                <option value="20">20 por página</option>
                <option value="50">50 por página</option>
                <option value="100">100 por página</option>
            </select>
        </div>
    </div>
    @endcan

    <div class="overflow-x-auto rounded-t-xl">
        <table class="min-w-full divide-y divide-[#D4F5ED]">
            <thead class="bg-[#D4F5ED]">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-lg font-bold text-[#121619] cursor-pointer" wire:click="sortBy('name')">
                        Nombre
                        @if ($sortField === 'name')
                            <svg class="inline-block ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                @if ($sortDirection === 'asc')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                @endif
                            </svg>
                        @endif
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-lg font-bold text-[#121619]">OLO ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-lg font-bold text-[#121619]">Compañía</th>
                    <th scope="col" class="px-6 py-3 text-left text-lg font-bold text-[#121619] cursor-pointer" wire:click="sortBy('active')">
                        Estado
                        @if ($sortField === 'active')
                            <svg class="inline-block ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                @if ($sortDirection === 'asc')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                @endif
                            </svg>
                        @endif
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#D4F5ED] bg-white">
                @forelse ($shippingLines as $item)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="text-sm text-[#2E2E2E] font-dm-sans">{{ $item['name'] ?? '--' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-[#2E2E2E] font-dm-sans">{{ $item['olo_id'] ?? '--' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-[#2E2E2E] font-dm-sans">{{ $item['company'] ?? '--' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ ($item['active'] ?? false) ? 'bg-green-100 text-white' : 'bg-red-100 text-white' }}">
                                {{ ($item['active'] ?? false) ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            No se encontraron líneas de envío.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between mt-4">
        <div class="text-sm text-gray-700">
            Mostrando {{ $shippingLines->firstItem() ?? 0 }} a {{ $shippingLines->lastItem() ?? 0 }} de {{ $shippingLines->total() }} resultados
        </div>
        <div class="flex items-center space-x-1">
            @if ($shippingLines->onFirstPage())
                <span class="px-3 py-1 text-gray-500 bg-gray-200 rounded-md cursor-not-allowed">
                    <span class="sr-only">Anterior</span>
                    &larr;
                </span>
            @else
                <button wire:click="previousPage" class="px-3 py-1 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <span class="sr-only">Anterior</span>
                    &larr;
                </button>
            @endif

            @foreach ($shippingLines->getUrlRange(max(1, $shippingLines->currentPage() - 3), min($shippingLines->lastPage(), $shippingLines->currentPage() + 3)) as $page => $url)
                @if ($page == $shippingLines->currentPage())
                    <span class="px-3 py-1 text-white bg-[#1AAD8A] rounded-md">{{ $page }}</span>
                @else
                    <button wire:click="gotoPage({{ $page }})" class="px-3 py-1 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">{{ $page }}</button>
                @endif
            @endforeach

            @if ($shippingLines->hasMorePages())
                <button wire:click="nextPage" class="px-3 py-1 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <span class="sr-only">Siguiente</span>
                    &rarr;
                </button>
            @else
                <span class="px-3 py-1 text-gray-500 bg-gray-200 rounded-md cursor-not-allowed">
                    <span class="sr-only">Siguiente</span>
                    &rarr;
                </span>
            @endif
        </div>
    </div>
</div>
