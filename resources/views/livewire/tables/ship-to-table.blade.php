<div>
    <div class="mb-4 flex justify-between">
        <div class="flex items-center">
            <div class="relative">
                <input
                    type="text"
                    wire:model.debounce.300ms="search"
                    placeholder="Buscar direcciones..."
                    class="w-64 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <select
                wire:model="statusFilter"
                class="ml-4 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Todos los estados</option>
                <option value="active">Activo</option>
                <option value="inactive">Inactivo</option>
            </select>
        </div>
        <div>
            <select
                wire:model="perPage"
                class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="10">10 por página</option>
                <option value="25">25 por página</option>
                <option value="50">50 por página</option>
                <option value="100">100 por página</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
        <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
            <thead>
                <tr class="text-left">
                    <th class="py-2 px-3 sticky top-0 border-b border-gray-200 bg-gray-100">
                        <div class="flex items-center cursor-pointer" wire:click="sortBy('name')">
                            <span>Nombre</span>
                            @if ($sortField === 'name')
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    @if ($sortDirection === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    @endif
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th class="py-2 px-3 sticky top-0 border-b border-gray-200 bg-gray-100">
                        <div class="flex items-center cursor-pointer" wire:click="sortBy('email')">
                            <span>Email</span>
                            @if ($sortField === 'email')
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    @if ($sortDirection === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    @endif
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th class="py-2 px-3 sticky top-0 border-b border-gray-200 bg-gray-100">Contacto</th>
                    <th class="py-2 px-3 sticky top-0 border-b border-gray-200 bg-gray-100">Dirección</th>
                    <th class="py-2 px-3 sticky top-0 border-b border-gray-200 bg-gray-100">Teléfono</th>
                    <th class="py-2 px-3 sticky top-0 border-b border-gray-200 bg-gray-100">
                        <div class="flex items-center cursor-pointer" wire:click="sortBy('status')">
                            <span>Estado</span>
                            @if ($sortField === 'status')
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    @if ($sortDirection === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    @endif
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th class="py-2 px-3 sticky top-0 border-b border-gray-200 bg-gray-100">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shipTos as $shipTo)
                    <tr>
                        <td class="border-dashed border-t border-gray-200 px-3 py-2">{{ $shipTo->name }}</td>
                        <td class="border-dashed border-t border-gray-200 px-3 py-2">{{ $shipTo->email ?? '--' }}</td>
                        <td class="border-dashed border-t border-gray-200 px-3 py-2">{{ $shipTo->contact_person ?? '--' }}</td>
                        <td class="border-dashed border-t border-gray-200 px-3 py-2">{{ $shipTo->ship_to_direccion ?? '--' }}</td>
                        <td class="border-dashed border-t border-gray-200 px-3 py-2">{{ $shipTo->ship_to_telefono ?? '--' }}</td>
                        <td class="border-dashed border-t border-gray-200 px-3 py-2">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $shipTo->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $shipTo->status === 'active' ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="border-dashed border-t border-gray-200 px-3 py-2">
                            <div class="flex space-x-2">
                                <a href="{{ route('ship-to.edit', $shipTo->id) }}" class="text-blue-600 hover:text-blue-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <button wire:click="$emit('confirmDelete', {{ $shipTo->id }})" class="text-red-600 hover:text-red-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="border-dashed border-t border-gray-200 px-3 py-6 text-center text-gray-500">
                            No se encontraron direcciones de envío.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $shipTos->links() }}
    </div>

    <!-- Modal de confirmación para eliminar -->
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('confirmDelete', function (shipToId) {
                if (confirm('¿Está seguro de que desea eliminar esta dirección de envío?')) {
                    window.livewire.find('@this.id').deleteShipTo(shipToId);
                }
            });
        });
    </script>
</div>
