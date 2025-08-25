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
    <div class="flex justify-between items-center p-3 mb-4 bg-blue-50 rounded-md">
        <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 w-5 h-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm font-medium text-blue-700">Mostrando órdenes filtradas. Los resultados que estás viendo están limitados por los filtros activos.</span>
        </div>
        <button
            wire:click="$dispatch('clearKanbanFilters')"
            class="px-3 py-1 ml-3 text-xs font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200"
        >
            Limpiar filtros
        </button>
    </div>
    @endif

    <div class="flex overflow-x-auto gap-4 pb-4 w-full kanban-container" wire:poll.10s>
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
                                onEnd: function(evt) {
                                    const taskId = evt.item.getAttribute('data-task-id');
                                    const newColumn = evt.to.getAttribute('data-column-id');

                                    console.log(taskId, newColumn);
                                    if (evt.from.getAttribute('data-column-id') !== newColumn) {
                                        console.log(newColumn);

                                        // Guardar referencia de la tarjeta actual para poder moverla después
                                        window.kanbanCurrentTask = evt.item;

                                        if(newColumn == 1) {
                                            $dispatch('open-modal', 'modal-hub-teorico');
                                        } else if (newColumn == 2) {
                                            $dispatch('open-modal', 'modal-hub-teorico');
                                        } else if (newColumn == 3) {
                                            $dispatch('open-modal', 'modal-validacion-operativa');
                                        } else if (newColumn == 4) {
                                            $dispatch('open-modal', 'modal-pickup');
                                        } else if (newColumn == 5) {
                                            $dispatch('open-modal', 'modal-en-transito');
                                        } else if (newColumn == 6) {
                                            $dispatch('open-modal', 'modal-llegada-a-hub');
                                        } else if (newColumn == 7) {
                                            $dispatch('open-modal', 'modal-validacion-operativa-cliente');
                                        } else if (newColumn == 8) {
                                            $dispatch('open-modal', 'modal-consolidacion-hub-real');
                                        } else if (newColumn == 9) {
                                            $dispatch('open-modal', 'modal-gestion-documental');
                                        }

                                        $wire.setCurrentTask(taskId, newColumn);
                                    }
                                }
                            })">
                            @foreach ($tasksByColumn[$column['id']] as $task)
                                <div class="cursor-move task-card" data-task-id="{{ $task['id'] }}">
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

    <x-modal-success name="success-modal" show="true">
        <div>
            @if ($currentTask)
                <p>PO: {{ $currentTask['po'] }}</p>
            @endif
        </div>
    </x-modal-success>

    <x-modal name="modal-hub-teorico" maxWidth="lg">
        <h3 class="mb-2 text-lg font-bold text-center text-light-blue">
            ¿Cambiar la Orden de compra de etapa?
        </h3>

        @if ($currentTask)
            <div class="mb-5 text-center">
                <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
            </div>
        @endif

        <div class="mb-8">
            <x-form-select label="" name="etapa" :options="collect($columns)->pluck('name', 'id')->toArray()" optionPlaceholder="Seleccionar etapa"
                :value="$newColumnId" wire:model.live="newColumnId"
                x-on:change="moveTaskToColumn($event.target.value)" />
        </div>

        <div class="mb-8">
            <x-form-select label="HUB Planificado" name="actual_hub_id" wireModel="actual_hub_id" :options="$hubArray" />
        </div>

        <div class="mb-8">
            <x-form-textarea label="" name="comment_stage_01" wireModel="comment" placeholder="Comentarios" />
        </div>

        <div class="mb-12 space-y-2">
            <div class="space-y-4">
                <div class="flex flex-col gap-4 items-start">
                    <input
                        type="file"
                        wire:model="attachment"
                        class="hidden"
                        x-ref="fileInput"
                        id="file-upload-hub-teorico">
                    <x-secondary-button
                        onclick="document.getElementById('file-upload-hub-teorico').click()"
                        class="group flex w-full items-center justify-center gap-[0.625rem]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="21" height="22" viewBox="0 0 21 22"
                            fill="none">
                            <path
                                d="M19.1525 9.89897L10.1369 18.9146C8.08662 20.9648 4.7625 20.9648 2.71225 18.9146C0.661997 16.8643 0.661998 13.5402 2.71225 11.49L11.7279 2.47435C13.0947 1.10751 15.3108 1.10751 16.6776 2.47434C18.0444 3.84118 18.0444 6.05726 16.6776 7.42409L8.01555 16.0862C7.33213 16.7696 6.22409 16.7696 5.54068 16.0862C4.85726 15.4027 4.85726 14.2947 5.54068 13.6113L13.1421 6.00988"
                                stroke="#565AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="transition-colors duration-500 group-hover:stroke-dark-blue group-active:stroke-neutral-blue group-disabled:stroke-[#C2C2C2]" />
                        </svg>

                        <span>Adjuntar documentación...</span>
                    </x-secondary-button>

                    @if($attachment)
                        <div class="text-sm text-gray-600">
                            Archivo seleccionado: {{ $attachment->getClientOriginalName() }}
                        </div>
                    @endif

                    <div class="flex flex-col text-sm text-[#A5A3A3]">
                        <span>Tipo de formato .xls .xlsx .pdf</span>
                        <span>Tamaño máximo 5MB</span>
                    </div>
                </div>

                @error('attachment')
                    <span class="text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="flex gap-[1.875rem]">
            <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-hub-teorico')" class="w-full">
                Cancelar
            </x-secondary-button>

            <x-primary-button
                x-on:click="
                    $wire.setComments($wire.currentTaskId, document.querySelector('textarea[name=comment_stage_01]').value);
                    $wire.setActualHubId($wire.currentTaskId, document.querySelector('select[name=etapa]').value);
                    $wire.moveTask($wire.currentTaskId, document.querySelector('select[name=etapa]').value);
                    $dispatch('close-modal', 'modal-hub-teorico')"
                class="w-full">
                Continuar
            </x-primary-button>
        </div>
    </x-modal>

    <x-modal name="modal-validacion-operativa" maxWidth="lg">
        <h3 class="mb-2 text-lg font-bold text-center text-light-blue">
            ¿Cambiar la Orden de compra de etapa?
        </h3>

        @if ($currentTask)
            <div class="mb-5 text-center">
                <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
            </div>
        @endif

        <div class="mb-8">
            <x-form-select label="" name="etapa" :options="collect($columns)->pluck('name', 'id')->toArray()" optionPlaceholder="Seleccionar etapa"
                :value="$newColumnId" wire:model.live="newColumnId"
                x-on:change="moveTaskToColumn($event.target.value)" />
        </div>

        <div class="mb-8">
            <x-form-textarea label="" name="comment_stage_02" wireModel="comment" placeholder="Comentarios" />
        </div>

        <div class="mb-12 space-y-2">
            <input
                type="file"
                wire:model="attachment"
                class="hidden"
                x-ref="fileInput"
                id="file-upload-validacion-operativa">
            <x-secondary-button
                onclick="document.getElementById('file-upload-validacion-operativa').click()"
                class="group flex w-full items-center justify-center gap-[0.625rem]">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="22" viewBox="0 0 21 22"
                    fill="none">
                    <path
                        d="M19.1525 9.89897L10.1369 18.9146C8.08662 20.9648 4.7625 20.9648 2.71225 18.9146C0.661997 16.8643 0.661998 13.5402 2.71225 11.49L11.7279 2.47435C13.0947 1.10751 15.3108 1.10751 16.6776 2.47434C18.0444 3.84118 18.0444 6.05726 16.6776 7.42409L8.01555 16.0862C7.33213 16.7696 6.22409 16.7696 5.54068 16.0862C4.85726 15.4027 4.85726 14.2947 5.54068 13.6113L13.1421 6.00988"
                        stroke="#565AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="transition-colors duration-500 group-hover:stroke-dark-blue group-active:stroke-neutral-blue group-disabled:stroke-[#C2C2C2]" />
                </svg>

                <span>Adjuntar documentación...</span>
            </x-secondary-button>

            @if($attachment)
                <div class="text-sm text-gray-600">
                    Archivo seleccionado: {{ $attachment->getClientOriginalName() }}
                </div>
            @endif

            <div class="flex flex-col text-sm text-[#A5A3A3]">
                <span>Tipo de formato .xls .xlsx .pdf</span>
                <span>Tamaño máximo 5MB</span>
            </div>
        </div>

        <div class="flex gap-[1.875rem]">
            <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-validacion-operativa')" class="w-full">
                Cancelar
            </x-secondary-button>

            <x-primary-button
                x-on:click="
                    $wire.setComments($wire.currentTaskId, document.querySelector('textarea[name=comment_stage_02]').value);
                    $wire.moveTask($wire.currentTaskId, 3);
                    $dispatch('close-modal', 'modal-validacion-operativa')"
                class="w-full">
                Continuar
            </x-primary-button>
        </div>
    </x-modal>

    <x-modal name="modal-pickup" maxWidth="lg">
        <h3 class="mb-2 text-lg font-bold text-center text-light-blue">
            ¿Cambiar la Orden de compra de etapa?
        </h3>

        @if ($currentTask)
            <div class="mb-5 text-center">
                <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
            </div>
        @endif

        <div class="mb-8">
            <x-form-select label="" name="etapa" :options="collect($columns)->pluck('name', 'id')->toArray()" optionPlaceholder="Seleccionar etapa"
                :value="$newColumnId" wire:model.live="newColumnId"
                x-on:change="moveTaskToColumn($event.target.value)" />
        </div>

        <div class="mb-8">
            <x-form-input>
                <x-slot:label>
                    Ingrese fecha de pick up
                </x-slot:label>

                <x-slot:input name="pickup_date" type="date" placeholder="Ingrese fecha de pickup" wire:model="pickup_date" class="pr-10"></x-slot:input>
            </x-form-input>
        </div>

        <div class="mb-8">
            <x-form-textarea label="" name="comment_stage_03" wireModel="comment" placeholder="Comentarios" />
        </div>

        <div class="mb-12 space-y-2">
            <input
                type="file"
                wire:model="attachment"
                class="hidden"
                x-ref="fileInput"
                id="file-upload-pickup">
            <x-secondary-button
                onclick="document.getElementById('file-upload-pickup').click()"
                class="group flex w-full items-center justify-center gap-[0.625rem]">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="22" viewBox="0 0 21 22"
                    fill="none">
                    <path
                        d="M19.1525 9.89897L10.1369 18.9146C8.08662 20.9648 4.7625 20.9648 2.71225 18.9146C0.661997 16.8643 0.661998 13.5402 2.71225 11.49L11.7279 2.47435C13.0947 1.10751 15.3108 1.10751 16.6776 2.47434C18.0444 3.84118 18.0444 6.05726 16.6776 7.42409L8.01555 16.0862C7.33213 16.7696 6.22409 16.7696 5.54068 16.0862C4.85726 15.4027 4.85726 14.2947 5.54068 13.6113L13.1421 6.00988"
                        stroke="#565AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="transition-colors duration-500 group-hover:stroke-dark-blue group-active:stroke-neutral-blue group-disabled:stroke-[#C2C2C2]" />
                </svg>

                <span>Adjuntar documentación...</span>
            </x-secondary-button>

            @if($attachment)
                <div class="text-sm text-gray-600">
                    Archivo seleccionado: {{ $attachment->getClientOriginalName() }}
                </div>
            @endif

            <div class="flex flex-col text-sm text-[#A5A3A3]">
                <span>Tipo de formato .xls .xlsx .pdf</span>
                <span>Tamaño máximo 5MB</span>
            </div>
        </div>

        <div class="flex gap-[1.875rem]">
            <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-pickup')" class="w-full">
                Cancelar
            </x-secondary-button>

            <x-primary-button
                x-on:click="
                    $wire.setComments($wire.currentTaskId, document.querySelector('textarea[name=comment_stage_03]').value);
                    $wire.moveTask($wire.currentTaskId, 4);
                    $wire.setPickupDate($wire.currentTaskId, document.querySelector('input[name=pickup_date]').value);
                    $dispatch('close-modal', 'modal-pickup')"
                class="w-full">
                Continuar
            </x-primary-button>
        </div>
    </x-modal>

    <x-modal name="modal-en-transito" maxWidth="lg">
        <h3 class="mb-2 text-lg font-bold text-center text-light-blue">
            ¿Cambiar la Orden de compra de etapa?
        </h3>

        @if ($currentTask)
            <div class="mb-5 text-center">
                <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
            </div>
        @endif

        <div class="mb-8">
            <x-form-select label="" name="etapa" :options="collect($columns)->pluck('name', 'id')->toArray()" optionPlaceholder="Seleccionar etapa"
                :value="$newColumnId" wire:model.live="newColumnId"
                x-on:change="moveTaskToColumn($event.target.value)" />
        </div>

        <div class="mb-8">
            <x-form-input>
                <x-slot:label>
                    Ingrese ID Tracking
                </x-slot:label>

                <x-slot:input name="tracking_id" type="text" placeholder="Ingrese ID Tracking" wire:model="tracking_id" class="pr-10"></x-slot:input>
            </x-form-input>
        </div>

        <div class="mb-8">
            <x-form-textarea label="" name="comment_stage_04" wireModel="comment" placeholder="Comentarios" />
        </div>

        <div class="mb-12 space-y-2">
            <input
                type="file"
                wire:model="attachment"
                class="hidden"
                x-ref="fileInput"
                id="file-upload-en-transito">
            <x-secondary-button
                onclick="document.getElementById('file-upload-en-transito').click()"
                class="group flex w-full items-center justify-center gap-[0.625rem]">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="22" viewBox="0 0 21 22"
                    fill="none">
                    <path
                        d="M19.1525 9.89897L10.1369 18.9146C8.08662 20.9648 4.7625 20.9648 2.71225 18.9146C0.661997 16.8643 0.661998 13.5402 2.71225 11.49L11.7279 2.47435C13.0947 1.10751 15.3108 1.10751 16.6776 2.47434C18.0444 3.84118 18.0444 6.05726 16.6776 7.42409L8.01555 16.0862C7.33213 16.7696 6.22409 16.7696 5.54068 16.0862C4.85726 15.4027 4.85726 14.2947 5.54068 13.6113L13.1421 6.00988"
                        stroke="#565AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="transition-colors duration-500 group-hover:stroke-dark-blue group-active:stroke-neutral-blue group-disabled:stroke-[#C2C2C2]" />
                </svg>

                <span>Adjuntar documentación...</span>
            </x-secondary-button>

            @if($attachment)
                <div class="text-sm text-gray-600">
                    Archivo seleccionado: {{ $attachment->getClientOriginalName() }}
                </div>
            @endif

            <div class="flex flex-col text-sm text-[#A5A3A3]">
                <span>Tipo de formato .xls .xlsx .pdf</span>
                <span>Tamaño máximo 5MB</span>
            </div>
        </div>

        <div class="flex gap-[1.875rem]">
            <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-pickup')" class="w-full">
                Cancelar
            </x-secondary-button>

            <x-primary-button
                x-on:click="
                    $wire.setComments($wire.currentTaskId, document.querySelector('textarea[name=comment_stage_04]').value);
                    $wire.setTrackingId($wire.currentTaskId, document.querySelector('input[name=tracking_id]').value);
                    $wire.moveTask($wire.currentTaskId, 5);
                    $dispatch('close-modal', 'modal-en-transito')"
                class="w-full">
                Continuar
            </x-primary-button>
        </div>
    </x-modal>

    <x-modal name="modal-llegada-a-hub" maxWidth="lg">
        <h3 class="mb-2 text-lg font-bold text-center text-light-blue">
            ¿Cambiar la Orden de compra de etapa?
        </h3>

        @if ($currentTask)
            <div class="mb-5 text-center">
                <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
            </div>
        @endif

        <div class="mb-8">
            <x-form-select label="" name="etapa" :options="collect($columns)->pluck('name', 'id')->toArray()" optionPlaceholder="Seleccionar etapa"
                :value="$newColumnId" wire:model.live="newColumnId"
                x-on:change="moveTaskToColumn($event.target.value)" />
        </div>

        <div class="mb-8">
            <x-form-textarea label="" name="comment_stage_05" wireModel="comment" placeholder="Comentarios" />
        </div>

        <div class="mb-12 space-y-2">
            <input
                type="file"
                wire:model="attachment"
                class="hidden"
                x-ref="fileInput"
                id="file-upload-llegada-a-hub">
            <x-secondary-button
                onclick="document.getElementById('file-upload-llegada-a-hub').click()"
                class="group flex w-full items-center justify-center gap-[0.625rem]">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="22" viewBox="0 0 21 22"
                    fill="none">
                    <path
                        d="M19.1525 9.89897L10.1369 18.9146C8.08662 20.9648 4.7625 20.9648 2.71225 18.9146C0.661997 16.8643 0.661998 13.5402 2.71225 11.49L11.7279 2.47435C13.0947 1.10751 15.3108 1.10751 16.6776 2.47434C18.0444 3.84118 18.0444 6.05726 16.6776 7.42409L8.01555 16.0862C7.33213 16.7696 6.22409 16.7696 5.54068 16.0862C4.85726 15.4027 4.85726 14.2947 5.54068 13.6113L13.1421 6.00988"
                        stroke="#565AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="transition-colors duration-500 group-hover:stroke-dark-blue group-active:stroke-neutral-blue group-disabled:stroke-[#C2C2C2]" />
                </svg>

                <span>Adjuntar documentación...</span>
            </x-secondary-button>

            @if($attachment)
                <div class="text-sm text-gray-600">
                    Archivo seleccionado: {{ $attachment->getClientOriginalName() }}
                </div>
            @endif

            <div class="flex flex-col text-sm text-[#A5A3A3]">
                <span>Tipo de formato .xls .xlsx .pdf</span>
                <span>Tamaño máximo 5MB</span>
            </div>
        </div>

        <div class="flex gap-[1.875rem]">
            <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-llegada-a-hub')" class="w-full">
                Cancelar
            </x-secondary-button>

            <x-primary-button
                x-on:click="
                    $wire.setComments($wire.currentTaskId, document.querySelector('textarea[name=comment_stage_05]').value);
                    $wire.moveTask($wire.currentTaskId, 6);
                    $dispatch('close-modal', 'modal-llegada-a-hub')"
                class="w-full">
                Continuar
            </x-primary-button>
        </div>
    </x-modal>

    <x-modal name="modal-validacion-operativa-cliente" maxWidth="lg">
        <h3 class="mb-2 text-lg font-bold text-center text-light-blue">
            ¿Cambiar la Orden de compra de etapa?
        </h3>

        @if ($currentTask)
            <div class="mb-5 text-center">
                <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
            </div>
        @endif

        <div class="mb-8">
            <x-form-select label="" name="etapa" :options="collect($columns)->pluck('name', 'id')->toArray()" optionPlaceholder="Seleccionar etapa"
                :value="$newColumnId" wire:model.live="newColumnId"
                x-on:change="moveTaskToColumn($event.target.value)" />
        </div>

        <div class="mb-8">
            <x-form-textarea label="" name="comment_stage_06" wireModel="comment" placeholder="Comentarios" />
        </div>

        <div class="mb-12 space-y-2">
            <input
                type="file"
                wire:model="attachment"
                class="hidden"
                x-ref="fileInput"
                id="file-upload-validacion-operativa-cliente">
            <x-secondary-button
                onclick="document.getElementById('file-upload-validacion-operativa-cliente').click()"
                class="group flex w-full items-center justify-center gap-[0.625rem]">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="22" viewBox="0 0 21 22"
                    fill="none">
                    <path
                        d="M19.1525 9.89897L10.1369 18.9146C8.08662 20.9648 4.7625 20.9648 2.71225 18.9146C0.661997 16.8643 0.661998 13.5402 2.71225 11.49L11.7279 2.47435C13.0947 1.10751 15.3108 1.10751 16.6776 2.47434C18.0444 3.84118 18.0444 6.05726 16.6776 7.42409L8.01555 16.0862C7.33213 16.7696 6.22409 16.7696 5.54068 16.0862C4.85726 15.4027 4.85726 14.2947 5.54068 13.6113L13.1421 6.00988"
                        stroke="#565AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="transition-colors duration-500 group-hover:stroke-dark-blue group-active:stroke-neutral-blue group-disabled:stroke-[#C2C2C2]" />
                </svg>

                <span>Adjuntar documentación...</span>
            </x-secondary-button>

            @if($attachment)
                <div class="text-sm text-gray-600">
                    Archivo seleccionado: {{ $attachment->getClientOriginalName() }}
                </div>
            @endif

            <div class="flex flex-col text-sm text-[#A5A3A3]">
                <span>Tipo de formato .xls .xlsx .pdf</span>
                <span>Tamaño máximo 5MB</span>
            </div>
        </div>

        <div class="flex gap-[1.875rem]">
            <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-validacion-operativa-cliente')" class="w-full">
                Cancelar
            </x-secondary-button>

            <x-primary-button
                x-on:click="
                    $wire.setComments($wire.currentTaskId, document.querySelector('textarea[name=comment_stage_06]').value);
                    $wire.moveTask($wire.currentTaskId, 7);
                    $dispatch('close-modal', 'modal-validacion-operativa-cliente')"
                class="w-full">
                Continuar
            </x-primary-button>
        </div>
    </x-modal>

    <x-modal name="modal-consolidacion-hub-real" maxWidth="lg">
        <h3 class="mb-2 text-lg font-bold text-center text-light-blue">
            ¿Cambiar la Orden de compra de etapa?
        </h3>

        @if ($currentTask)
            <div class="mb-5 text-center">
                <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
            </div>
        @endif

        <div class="mb-8">
            <x-form-select label="" name="etapa" :options="collect($columns)->pluck('name', 'id')->toArray()" optionPlaceholder="Seleccionar etapa"
                :value="$newColumnId" wire:model.live="newColumnId"
                x-on:change="moveTaskToColumn($event.target.value)" />
        </div>

        <div class="mb-8">
            <x-form-textarea label="" name="comment_stage_07" wireModel="comment" placeholder="Comentarios" />
        </div>

        <div class="mb-12 space-y-2">
            <input
                type="file"
                wire:model="attachment"
                class="hidden"
                x-ref="fileInput"
                id="file-upload-consolidacion-hub-real">
            <x-secondary-button
                onclick="document.getElementById('file-upload-consolidacion-hub-real').click()"
                class="group flex w-full items-center justify-center gap-[0.625rem]">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="22" viewBox="0 0 21 22"
                    fill="none">
                    <path
                        d="M19.1525 9.89897L10.1369 18.9146C8.08662 20.9648 4.7625 20.9648 2.71225 18.9146C0.661997 16.8643 0.661998 13.5402 2.71225 11.49L11.7279 2.47435C13.0947 1.10751 15.3108 1.10751 16.6776 2.47434C18.0444 3.84118 18.0444 6.05726 16.6776 7.42409L8.01555 16.0862C7.33213 16.7696 6.22409 16.7696 5.54068 16.0862C4.85726 15.4027 4.85726 14.2947 5.54068 13.6113L13.1421 6.00988"
                        stroke="#565AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="transition-colors duration-500 group-hover:stroke-dark-blue group-active:stroke-neutral-blue group-disabled:stroke-[#C2C2C2]" />
                </svg>

                <span>Adjuntar documentación...</span>
            </x-secondary-button>

            @if($attachment)
                <div class="text-sm text-gray-600">
                    Archivo seleccionado: {{ $attachment->getClientOriginalName() }}
                </div>
            @endif

            <div class="flex flex-col text-sm text-[#A5A3A3]">
                <span>Tipo de formato .xls .xlsx .pdf</span>
                <span>Tamaño máximo 5MB</span>
            </div>
        </div>

        <div class="flex gap-[1.875rem]">
            <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-consolidacion-hub-real')" class="w-full">
                Cancelar
            </x-secondary-button>

            <x-primary-button
                x-on:click="
                    $wire.setComments($wire.currentTaskId, document.querySelector('textarea[name=comment_stage_07]').value);
                    $wire.moveTask($wire.currentTaskId, 8);
                    $dispatch('close-modal', 'modal-consolidacion-hub-real')"
                class="w-full">
                Continuar
            </x-primary-button>
        </div>
    </x-modal>

    <x-modal name="modal-gestion-documental" maxWidth="lg">
        <h3 class="mb-2 text-lg font-bold text-center text-light-blue">
            ¿Cambiar la Orden de compra de etapa?
        </h3>

        @if ($currentTask)
            <div class="mb-5 text-center">
                <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
            </div>
        @endif

        <div class="mb-8">
            <x-form-select label="" name="etapa" :options="collect($columns)->pluck('name', 'id')->toArray()" optionPlaceholder="Seleccionar etapa"
                :value="$newColumnId" wire:model.live="newColumnId"
                x-on:change="moveTaskToColumn($event.target.value)" />
        </div>

        <div class="mb-8">
            <x-form-textarea label="" name="comment_stage_08" wireModel="comment_stage_08" placeholder="Comentarios" />
        </div>

        <div class="mb-12 space-y-2">
            <x-secondary-button class="group flex w-full items-center justify-center gap-[0.625rem]">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="22" viewBox="0 0 21 22"
                    fill="none">
                    <path
                        d="M19.1525 9.89897L10.1369 18.9146C8.08662 20.9648 4.7625 20.9648 2.71225 18.9146C0.661997 16.8643 0.661998 13.5402 2.71225 11.49L11.7279 2.47435C13.0947 1.10751 15.3108 1.10751 16.6776 2.47434C18.0444 3.84118 18.0444 6.05726 16.6776 7.42409L8.01555 16.0862C7.33213 16.7696 6.22409 16.7696 5.54068 16.0862C4.85726 15.4027 4.85726 14.2947 5.54068 13.6113L13.1421 6.00988"
                        stroke="#565AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="transition-colors duration-500 group-hover:stroke-dark-blue group-active:stroke-neutral-blue group-disabled:stroke-[#C2C2C2]" />
                </svg>

                <span>Adjuntar documentación...</span>
            </x-secondary-button>
            <div class="flex flex-col text-sm text-[#A5A3A3]">
                <span>Tipo de formato .xls .xlsx .pdf</span>
                <span>Tamaño máximo 5MB</span>
            </div>
        </div>

        <div class="flex gap-[1.875rem]">
            <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-gestion-documental')" class="w-full">
                Cancelar
            </x-secondary-button>

            <x-primary-button
                x-on:click="
                    $wire.setComments($wire.currentTaskId, document.querySelector('textarea[name=comment_stage_08]').value);
                    $wire.moveTask($wire.currentTaskId, 9);
                    $dispatch('close-modal', 'modal-gestion-documental')"
                class="w-full">
                Continuar
            </x-primary-button>
        </div>
    </x-modal>

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
