<div>
    <x-slot:header>
        <x-view-title>
            <x-slot:title>
                Histórico General
            </x-slot:title>

            <x-slot:content>
                Visualiza todos los comentarios y archivos adjuntos de todas las órdenes de compra
            </x-slot:content>
        </x-view-title>
    </x-slot:header>

    {{-- Controles de búsqueda y filtros --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <div class="relative w-64">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por orden, usuario u operación..."
                    class="w-full rounded-xl border-2 border-[#A5A3A3] pl-11 pr-10 py-[0.625rem] placeholder:text-[#28C7A1] focus:border-[#1AAD8A] focus:outline-none"
                />
                <div class="pointer-events-none absolute top-1/2 -translate-y-1/2 left-[1.125rem] flex items-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                @if(!empty($search))
                    <button
                        type="button"
                        wire:click="$set('search', '')"
                        class="absolute flex items-center justify-center w-6 h-6 text-gray-400 -translate-y-1/2 rounded-full top-1/2 right-2 hover:text-gray-600 hover:bg-gray-100"
                        title="Limpiar búsqueda"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        <div class="flex gap-4">
            <x-primary-button
                type="button"
                class="flex items-center gap-2 group"
                x-on:click="window.location.reload()"
            >
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18.453 10.8927C18.1752 13.5026 16.6964 15.9483 14.2494 17.3611C10.1839 19.7083 4.98539 18.3153 2.63818 14.2499L2.38818 13.8168M1.54613 9.10664C1.82393 6.49674 3.30272 4.05102 5.74971 2.63825C9.8152 0.29104 15.0137 1.68398 17.3609 5.74947L17.6109 6.18248M1.49316 16.0657L2.22521 13.3336L4.95727 14.0657M15.0424 5.93364L17.7744 6.66569L18.5065 3.93364" stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Actualizar
            </x-primary-button>
        </div>
    </div>

    {{-- Tabla de historial --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[#D4F5ED]">
                <tr>
                    <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase">
                        <input type="checkbox" class="rounded text-primary-600">
                    </th>
                    <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase cursor-pointer"
                        wire:click="sortBy('created_at')">
                        Fecha y hora
                        @if ($sortField === 'created_at')
                            @if ($sortDirection === 'asc')
                                <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                </svg>
                            @else
                                <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        @endif
                    </th>
                    <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase cursor-pointer"
                        wire:click="sortBy('purchase_order_number')">
                        Orden de Compra
                        @if ($sortField === 'purchase_order_number')
                            @if ($sortDirection === 'asc')
                                <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                </svg>
                            @else
                                <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        @endif
                    </th>
                    <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase cursor-pointer"
                        wire:click="sortBy('user_name')">
                        Usuario
                        @if ($sortField === 'user_name')
                            @if ($sortDirection === 'asc')
                                <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                </svg>
                            @else
                                <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        @endif
                    </th>
                    <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase">
                        Rol
                    </th>
                    <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase cursor-pointer"
                        wire:click="sortBy('operacion')">
                        Operación
                        @if ($sortField === 'operacion')
                            @if ($sortDirection === 'asc')
                                <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                </svg>
                            @else
                                <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        @endif
                    </th>
                    <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase">
                        Tipo
                    </th>
                    <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase">
                        Archivos adjuntos
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($comments as $comment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <input type="checkbox" class="rounded text-primary-600">
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                            {{ formatDateTime($comment['created_at']) }}
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                            <a href="{{ route('purchase-orders.detail', $comment['purchase_order_id']) }}"
                               class="text-[#1AAD8A] hover:text-[#0F614D] hover:underline">
                                {{ $comment['purchase_order_number'] }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                            {{ $comment['user_name'] }}
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                            {{ $comment['user_role'] }}
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                            {{ $comment['operation'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(($comment['action_type'] ?? 'comment') === 'comment')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Comentario
                                </span>
                            @elseif($comment['action_type'] === 'field_change')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Cambio de Datos
                                </span>
                            @elseif($comment['action_type'] === 'status_change')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Cambio de Estado
                                </span>
                            @elseif($comment['action_type'] === 'record_create')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Creación
                                </span>
                            @elseif($comment['action_type'] === 'porth_sync')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-800">
                                    Actualización Porth
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Otro
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                            @if($comment['attachment'])
                                @if($comment['attachment']['is_pending'])
                                    <span class="flex items-center gap-1 text-orange-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $comment['attachment']['name'] }}
                                    </span>
                                @else
                                    <a href="{{ $comment['attachment']['url'] }}"
                                       class="flex items-center gap-1 text-[#1AAD8A] hover:text-[#0F614D]"
                                       target="_blank">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        {{ $comment['attachment']['name'] }}
                                    </a>
                                @endif
                            @else
                                <span class="text-gray-400">Sin archivos</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($comment['has_changes'] ?? false)
                                <button wire:click="$dispatchTo('partials.activity-detail-modal', 'openActivityDetail', @js($comment))" 
                                        class="text-[#1AAD8A] hover:text-[#0F614D] hover:underline">
                                    Ver cambios
                                </button>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-lg font-medium text-gray-900">No se encontraron comentarios</p>
                                <p class="text-sm text-gray-500">
                                    @if(!empty($search))
                                        No hay comentarios que coincidan con "{{ $search }}"
                                    @else
                                        Aún no se han agregado comentarios en ninguna orden de compra
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginador --}}
    @if($comments->hasPages() || $comments->total() > 0)
        <div class="flex items-center justify-between mt-4">
            <div class="flex items-center gap-3 text-sm text-gray-600">
                <span>Mostrando {{ $comments->firstItem() }}–{{ $comments->lastItem() }} de {{ $comments->total() }} registro(s)</span>
                <select wire:model.live="perPage" class="rounded-lg border border-gray-300 text-sm py-1 px-2 focus:border-[#1AAD8A] focus:ring-[#1AAD8A]">
                    <option value="25">25 por página</option>
                    <option value="50">50 por página</option>
                    <option value="100">100 por página</option>
                </select>
            </div>
            <div>
                {{ $comments->links() }}
            </div>
        </div>
    @endif

    @livewire('partials.activity-detail-modal')
</div>
