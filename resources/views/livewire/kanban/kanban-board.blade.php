@php
    $hubArray = App\Models\Hub::orderBy('name')->get();
    $hubArray = $hubArray->pluck('name', 'id')->toArray();
@endphp

<div class="mx-auto w-full"
     x-data="{
        currentTaskElement: null,

        moveTaskToColumn(newColumnId) {
            if (!window.kanbanCurrentTask || !newColumnId) return;

            const targetColumn = document.getElementById('column-' + newColumnId);
            if (targetColumn) {
                // Remover la tarjeta de su posición actual
                window.kanbanCurrentTask.remove();

                // Agregar la tarjeta a la nueva columna
                targetColumn.appendChild(window.kanbanCurrentTask);

                // Actualizar el valor del select wire model
                $wire.set('newColumnId', newColumnId);
            }
        }
     }"
     x-on:refreshKanban.window="$wire.$refresh()"
     x-on:purchaseOrderStatusUpdated.window="$wire.$refresh()">

    @if(isset($hasActiveFilters) && $hasActiveFilters)
    <div class="flex justify-between items-center p-3 mb-4 bg-[#E6F9F4] rounded-md">
        <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 w-5 h-5 text-[#1AAD8A]" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm font-medium text-[#127A62]">Mostrando órdenes filtradas. Los resultados que estás viendo están limitados por los filtros activos.</span>
        </div>
        <button
            type="button"
            wire:click="clearKanbanFiltersFromBanner"
            onclick="window.poKanbanOverlayShow && window.poKanbanOverlayShow('Limpiando filtros…')"
            class="px-3 py-1 ml-3 text-xs font-medium text-[#127A62] bg-[#D4F5ED] rounded-md hover:bg-[#C0F0E5]"
        >
            Limpiar filtros
        </button>
    </div>
    @endif

    <div class="flex overflow-x-auto gap-4 pb-4 w-full kanban-container" wire:poll.keep-alive.30000ms="keepSessionAlive">
        @if (!$board)
            <div class="p-6 bg-white rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-gray-700">No hay tableros Kanban disponibles</h3>
                <p class="mt-2 text-gray-600">No se encontró ningún tablero Kanban para tu compañía. Contacta al
                    administrador para crear uno.</p>
            </div>
        @else
            @foreach ($columns as $column)
                <div class="flex-shrink-0 p-3 mx-2 rounded-lg kanban-column w-80 {{ $loop->first ? 'first-column' : '' }}">
                    <h3 class="mb-4 border-b-2 border-[#2E2E2E] px-2 text-lg font-bold text-[#2E2E2E]">
                        {{ $column['name'] }}
                        <span class="ml-2 text-sm font-normal text-gray-600">
                            ({{ count($tasksByColumn[$column['id']]) }})
                        </span>
                    </h3>
                    <div class="max-h-[600px] overflow-y-scroll h-full scrollbar-thin scrollbar-thumb-gray-transparent scrollbar-track-gray-100">
                        @php
                            $isAnuladaCol = (int)($column['id'] ?? 0) === 10
                                || strtolower($column['name'] ?? '') === 'anulada'
                                || strtolower($column['slug'] ?? '') === 'anulada';
                        @endphp
                        <div id="column-{{ $column['id'] }}" data-column-id="{{ $column['id'] }}" class="space-y-3 min-h-40"
                            x-data x-init="new Sortable($el, {
                                group: 'tasks',
                                animation: 150,
                                ghostClass: 'bg-gray-100',
                                chosenClass: 'bg-gray-200',
                                dragClass: 'cursor-grabbing',
                                forceFallback: true,
                                fallbackClass: 'sortable-fallback',
                                fallbackOnBody: true,
                                draggable: '.task-card:not(.is-trashed)',
                                onEnd: function(evt) {
                                    const taskId = evt.item.getAttribute('data-task-id');
                                    const newColumn = evt.to.getAttribute('data-column-id');

                                    if (evt.from.getAttribute('data-column-id') !== newColumn) {
                                        // Guardamos la tarjeta actual para moverla si el usuario confirma
                                        window.kanbanCurrentTask = evt.item;

                                        window.dispatchEvent(new CustomEvent('kanban-stage-modal:open-request', {
                                            detail: {
                                                taskId: parseInt(taskId, 10),
                                                newColumn: parseInt(newColumn, 10),
                                            }
                                        }));
                                    }
                                }
                            })">
                            @foreach ($tasksByColumn[$column['id']] as $task)
                                <div
                                    class="task-card {{ $isAnuladaCol ? 'is-trashed opacity-60 cursor-not-allowed select-none' : 'cursor-move' }}"
                                    data-task-id="{{ $task['id'] }}"
                                    @if($isAnuladaCol) title="PO anulada: no se puede mover" @endif
                                >

                                <x-kanban-card :id="$task['id']" :purchaseOrder="$task" :po="$task['po']" :trackingId="$task['id']" :hubLocation="$task['company']" :leadTime="$task['order_date'] ?? 'N/A'"
                                        :recolectaTime="$task['requested_delivery_date'] ?? 'N/A'" :pickupTime="$task['requested_delivery_date'] ?? 'N/A'" :totalWeight="number_format($task['total'] ?? 0, 2)" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

        <style>
        .kanban-container {
            display: flex;
            overflow-x: auto;
            padding-bottom: 1rem;
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
            scrollbar-width: thin;
            /* max-height: 600px; */
        }

        .kanban-column {
            height: 100%;
            min-height: 600px;
        }

        .first-column {
            padding-left: 0;
        }

        .sortable-fallback {
            opacity: 0.8;
            transform: rotate(2deg);
            min-height: 180px !important;
            width: 320px !important;
        }

        .task-card {
            transition: transform 0.2s ease;
            width: 100%;
        }

        .task-card:hover {
            transform: translateY(-2px);
        }
    </style>

</div>
