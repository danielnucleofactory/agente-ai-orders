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
            wire:click="$dispatch('clearKanbanFilters')"
            class="px-3 py-1 ml-3 text-xs font-medium text-[#127A62] bg-[#D4F5ED] rounded-md hover:bg-[#C0F0E5]"
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

                                        // Abrimos el modal según la etapa (id en BD)
                                        if (newColumn == 1) {
                                            $dispatch('open-modal', 'modal-nuevo');
                                        } else if (newColumn == 2) {
                                            $dispatch('open-modal', 'modal-produccion');
                                        } else if (newColumn == 3) {
                                            $dispatch('open-modal', 'modal-booking');
                                        } else if (newColumn == 4) {
                                            $dispatch('open-modal','modal-consolidador');
                                        } else if (newColumn == 5) {
                                            $dispatch('open-modal', 'modal-en-transito');
                                        } else if (newColumn == 6) {
                                            $dispatch('open-modal', 'modal-puerto');
                                        } else if (newColumn == 7) {
                                            $dispatch('open-modal', 'modal-alm-fiscal');
                                        } else if (newColumn == 8) {
                                            $dispatch('open-modal', 'modal-en-otra-zf');
                                        } else if (newColumn == 9) {
                                            $dispatch('open-modal', 'modal-recibiendo-cdi');
                                        } else if (newColumn == 10) {
                                            $dispatch('open-modal', 'modal-ingresada');
                                        } else if (newColumn == 11) {
                                            $dispatch('open-modal', 'modal-anulada');
                                        }

                                        $wire.setCurrentTask(taskId, newColumn);
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

    <x-modal-success name="success-modal" show="true">
        <div>
            @if ($currentTask)
                <p>PO: {{ $currentTask['po'] }}</p>
            @endif
        </div>
    </x-modal-success>

        <x-modal name="modal-nuevo" maxWidth="lg">
            <h3 class="mb-2 text-lg font-bold text-center text-light-blue">¿Cambiar la Orden de compra de etapa?</h3>

            @if ($currentTask)
                <div class="mb-5 text-center">
                    <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
                </div>
            @endif

            <div class="mb-8">
                <x-form-select label="" name="etapa"
                               :options="collect($columns)->pluck('name','id')->toArray()"
                               optionPlaceholder="Seleccionar etapa"
                               :value="$newColumnId" wire:model.live="newColumnId"
                               x-on:change="moveTaskToColumn($event.target.value)" />
            </div>

            <div class="mb-8">
                <x-form-textarea label="" name="comment_stage_01" wireModel="comment" placeholder="Comentarios" />
            </div>

            <div class="mb-12 space-y-2">
                <input type="file" wire:model="attachment" class="hidden" x-ref="fileInput" id="file-upload-nuevo">
                <x-secondary-button onclick="document.getElementById('file-upload-nuevo').click()" class="group flex w-full items-center justify-center gap-[0.625rem]">
                    @svg('heroicon-o-paper-clip', 'w-5 h-5')
                    <span>Adjuntar documentación...</span>
                </x-secondary-button>
                @if($attachment)
                    <div class="text-sm text-gray-600">Archivo seleccionado: {{ $attachment->getClientOriginalName() }}</div>
                @endif
                <div class="flex flex-col text-sm text-[#A5A3A3]">
                    <span>Tipo de formato .xls .xlsx .pdf</span><span>Tamaño máximo 5MB</span>
                </div>
            </div>

            <div class="flex gap-[1.875rem]">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-nuevo')" class="w-full">Cancelar</x-secondary-button>
                <x-primary-button wire:click="saveAndMove" class="w-full">Continuar</x-primary-button>
            </div>
        </x-modal>

        <x-modal name="modal-consolidador" maxWidth="lg">
            <h3 class="mb-2 text-lg font-bold text-center text-light-blue">¿Cambiar la Orden de compra de etapa?</h3>

            @if ($currentTask)
                <div class="mb-5 text-center">
                    <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
                </div>
            @endif

            <div class="mb-8">
                <x-form-select label="" name="etapa"
                               :options="collect($columns)->pluck('name','id')->toArray()"
                               optionPlaceholder="Seleccionar etapa"
                               :value="$newColumnId" wire:model.live="newColumnId"
                               x-on:change="moveTaskToColumn($event.target.value)" />
            </div>

            <div class="mb-8">
                <x-form-textarea label="" name="comment_stage_01" wireModel="comment" placeholder="Comentarios" />
            </div>

            <div class="mb-12 space-y-2">
                <input type="file" wire:model="attachment" class="hidden" x-ref="fileInput" id="file-upload-nuevo">
                <x-secondary-button onclick="document.getElementById('file-upload-nuevo').click()" class="group flex w-full items-center justify-center gap-[0.625rem]">
                    @svg('heroicon-o-paper-clip', 'w-5 h-5')
                    <span>Adjuntar documentación...</span>
                </x-secondary-button>
                @if($attachment)
                    <div class="text-sm text-gray-600">Archivo seleccionado: {{ $attachment->getClientOriginalName() }}</div>
                @endif
                <div class="flex flex-col text-sm text-[#A5A3A3]">
                    <span>Tipo de formato .xls .xlsx .pdf</span><span>Tamaño máximo 5MB</span>
                </div>
            </div>

            <div class="flex gap-[1.875rem]">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-nuevo')" class="w-full">Cancelar</x-secondary-button>
                <x-primary-button wire:click="saveAndMove" class="w-full">Continuar</x-primary-button>
            </div>
        </x-modal>

        <x-modal name="modal-produccion" maxWidth="lg">
            <h3 class="mb-2 text-lg font-bold text-center text-light-blue">¿Cambiar la Orden de compra de etapa?</h3>
            @if ($currentTask)
                <div class="mb-5 text-center">
                    <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
                </div>
            @endif

            <div class="mb-8">
                <x-form-select label="" name="etapa"
                               :options="collect($columns)->pluck('name','id')->toArray()"
                               optionPlaceholder="Seleccionar etapa"
                               :value="$newColumnId" wire:model.live="newColumnId"
                               x-on:change="moveTaskToColumn($event.target.value)" />
            </div>

            <div class="mb-8">
                <x-form-input>
                    <x-slot:label>Carga Lista Variable <span class="text-red-500">*</span></x-slot:label>
                    <x-slot:input type="date" name="date_variable_date" wire:model="date_variable_date" class="pr-10 {{ $errors->has('date_variable_date') ? 'border-red-500'  : '' }}">
                    </x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('date_variable_date') }}
                    </x-slot:error></x-form-input>
            </div>
            <div class="mb-8">
                <x-form-input>
                    <x-slot:label>Carga Lista Teórica</x-slot:label>
                    <x-slot:input type="date" name="date_theorical_load" wire:model="date_theorical_load"></x-slot:input>
                </x-form-input>
            </div>
            <div class="mb-8">
                <x-form-input>
                    <x-slot:label>Proveedor de Servicio <span class="text-red-500">*</span></x-slot:label>
                    <x-slot:input type="text" wire:model.live="service_provider" placeholder="Ingrese proveedor de servicio" class="pr-10 {{ $errors->has('service_provider') ? 'border-red-500'  : '' }}">
                    </x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('service_provider') }}
                    </x-slot:error>
                </x-form-input>
            </div>
            <div class="mb-8">
                <x-form-input>
                    <x-slot:label>Agente de Carga <span class="text-red-500">*</span></x-slot:label>
                    <x-slot:input type="text" name="forwarder_name" placeholder="Ingrese agente de carga" wire:model="forwarder_name" class="pr-10 {{ $errors->has('forwarder_name') ? 'border-red-500'  : '' }}">
                    </x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('forwarder_name') }}
                    </x-slot:error>
                </x-form-input>
            </div>

            <div class="mb-8">
                <x-form-textarea label="" name="comment_stage_02" wireModel="comment" placeholder="Comentarios" />
            </div>

            <div class="mb-12 space-y-2">
                <input type="file" wire:model="attachment" class="hidden" id="file-upload-produccion">
                <x-secondary-button onclick="document.getElementById('file-upload-produccion').click()" class="group flex w-full items-center justify-center gap-[0.625rem]">
                    @svg('heroicon-o-paper-clip', 'w-5 h-5') <span>Adjuntar documentación...</span>
                </x-secondary-button>
                @if($attachment)
                    <div class="text-sm text-gray-600">Archivo seleccionado: {{ $attachment->getClientOriginalName() }}</div>
                @endif
                <div class="flex flex-col text-sm text-[#A5A3A3]">
                    <span>Tipo de formato .xls .xlsx .pdf</span><span>Tamaño máximo 5MB</span>
                </div>
            </div>

            <div class="flex gap-[1.875rem]">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-produccion')" class="w-full">Cancelar</x-secondary-button>
                <x-primary-button wire:click="saveAndMove" class="w-full">Continuar</x-primary-button>
            </div>
        </x-modal>

        <x-modal name="modal-booking" maxWidth="lg">
            <div class="space-y-4 sm:space-y-6">
                <h3 class="text-center text-lg font-bold text-light-blue">¿Cambiar la Orden de compra de etapa?</h3>

                @if ($currentTask)
                    <div class="text-center">
                        <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
                    </div>
                @endif

                <!-- Contenedor scrollable suave dentro del modal -->
                <div class="max-h-[70vh] overflow-y-auto px-1 sm:px-0">
                    <!-- Etapa -->
                    <div class="mb-6">
                        <x-form-select
                            label=""
                            name="etapa"
                            :options="collect($columns)->pluck('name','id')->toArray()"
                            optionPlaceholder="Seleccionar etapa"
                            :value="$newColumnId"
                            wire:model.live="newColumnId"
                            x-on:change="moveTaskToColumn($event.target.value)" />
                    </div>

                    <!-- Grid principal -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <!-- Solicitud de Booking -->
                        <div>
                            <x-form-input>
                                <x-slot:label>Solicitud de Booking <span class="text-red-500">*</span></x-slot:label>
                                <x-slot:input
                                    type="date"
                                    name="date_booking_request"
                                    wire:model="date_booking_request"
                                    class="w-full pr-10 {{ $errors->has('date_booking_request') ? 'border-red-500'  : '' }}">
                                </x-slot:input>
                                <x-slot:error>
                                    {{ $errors->first('date_booking_request') }}
                                </x-slot:error>
                            </x-form-input>
                        </div>

                        <!-- Autorizacion de Booking -->
                        <div>
                            <x-form-input>
                                <x-slot:label>Autorización de Booking <span class="text-red-500">*</span></x-slot:label>
                                <x-slot:input
                                    type="date"
                                    name="date_booking_authorized"
                                    wire:model="date_booking_authorized"
                                    class="w-full pr-10 {{ $errors->has('date_booking_authorized') ? 'border-red-500'  : '' }}">
                                </x-slot:input>
                                <x-slot:error>
                                    {{ $errors->first('date_booking_authorized') }}
                                </x-slot:error>
                            </x-form-input>
                        </div>

                        <!-- ETD Inicial -->
                        <div>
                            <x-form-input>
                                <x-slot:label>ETD Inicial <span class="text-red-500">*</span></x-slot:label>
                                <x-slot:input
                                    type="date"
                                    wire:model.live="date_etd_initial"
                                    class="w-full pr-10 {{ $errors->has('date_etd_initial') ? 'border-red-500'  : '' }}">
                                </x-slot:input>
                                <x-slot:error>
                                    {{ $errors->first('date_etd_initial') }}
                                </x-slot:error>
                            </x-form-input>
                        </div>

                        <!-- ETD Variable -->
                        <div>
                            <x-form-input>
                                <x-slot:label>ETD Variable <span class="text-red-500">*</span></x-slot:label>
                                <x-slot:input
                                    type="date"
                                    name="date_etd_updated"
                                    wire:model="date_etd_updated"
                                    class="w-full pr-10 {{ $errors->has('date_etd_updated') ? 'border-red-500'  : '' }}">
                                </x-slot:input>
                                <x-slot:error>
                                    {{ $errors->first('date_etd_updated') }}
                                </x-slot:error>
                            </x-form-input>
                        </div>

                        <!-- Modo de transporte -->
                        <div>
                            <x-form-select
                                label="Modo de transporte <span class='text-red-500'>*</span>"
                                name="mode"
                                wire:model.live="mode"
                                :options="['maritimo' => 'Marítimo', 'aereo' => 'Aéreo','terrestre' => 'Terrestre']"
                                :error="$errors->has('mode')" />
                        </div>

                        <!-- Comentarios (a lo ancho) -->
                        <div class="sm:col-span-2">
                            <x-form-textarea
                                label=""
                                name="comment_stage_03"
                                wireModel="comment"
                                class="w-full"
                                placeholder="Comentarios" />
                        </div>

                        <!-- Adjuntos (a lo ancho) -->
                        <div class="sm:col-span-2">
                            <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                <input type="file" wire:model="attachment" class="hidden" id="file-upload-booking">
                                <x-secondary-button
                                    onclick="document.getElementById('file-upload-booking').click()"
                                    class="group flex w-full items-center justify-center gap-2">
                                    @svg('heroicon-o-paper-clip', 'w-5 h-5')
                                    <span>Adjuntar documentación...</span>
                                </x-secondary-button>

                                @if($attachment)
                                    <div class="text-sm text-gray-700 dark:text-gray-300">
                                        Archivo seleccionado: {{ $attachment->getClientOriginalName() }}
                                    </div>
                                @endif

                                <div class="text-xs sm:text-sm text-[#A5A3A3]">
                                    <span>Formatos aceptados: .xls .xlsx .pdf</span>
                                    <span class="mx-1">•</span>
                                    <span>Tamaño máximo 5MB</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="mt-6 flex flex-col-reverse sm:flex-row gap-3 sm:gap-4">
                        <x-secondary-button
                            x-on:click="$dispatch('close-modal', 'modal-booking')"
                            class="w-full sm:w-auto sm:flex-1">
                            Cancelar
                        </x-secondary-button>

                        <x-primary-button
                            wire:click="saveAndMove"
                            class="w-full sm:w-auto sm:flex-1">
                            Continuar
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </x-modal>

        <x-modal name="modal-en-transito" maxWidth="lg">
            <h3 class="mb-2 text-lg font-bold text-center text-light-blue">¿Cambiar la Orden de compra de etapa?</h3>
            @if ($currentTask)
                <div class="mb-5 text-center">
                    <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
                </div>
            @endif

            <div class="mb-8">
                <x-form-select label="" name="etapa"
                               :options="collect($columns)->pluck('name','id')->toArray()"
                               optionPlaceholder="Seleccionar etapa"
                               :value="$newColumnId" wire:model.live="newColumnId"
                               x-on:change="moveTaskToColumn($event.target.value)" />
            </div>

            {{-- Fechas tránsito --}}
            <div class="mb-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-5 gap-y-6">
                    <div>
                        <x-form-input>
                            <x-slot:label>ETD Real (ATD) <span class="text-red-500">*</span></x-slot:label>
                            <x-slot:input type="date" name="date_atd" wire:model="date_atd"
                                          class="pr-10 {{ $errors->has('date_atd') ? 'border-red-500' : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('date_atd') }}</x-slot:error>
                        </x-form-input>
                    </div>

                    <div>
                        <x-form-input>
                            <x-slot:label>ETA Inicial <span class="text-red-500">*</span></x-slot:label>
                            <x-slot:input type="date" name="date_eta" wire:model="date_eta"
                                          class="pr-10 {{ $errors->has('date_eta') ? 'border-red-500' : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('date_eta') }}</x-slot:error>
                        </x-form-input>
                    </div>

                    <div>
                        <x-form-input>
                            <x-slot:label>ETA Variable <span class="text-red-500">*</span></x-slot:label>
                            <x-slot:input type="date" name="date_eta_updated" wire:model="date_eta_updated"
                                          class="pr-10 {{ $errors->has('date_eta_updated') ? 'border-red-500' : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('date_eta_updated') }}</x-slot:error>
                        </x-form-input>
                    </div>
                </div>
            </div>

            {{-- Equipo / BL / Naviera + nuevos campos --}}
            <div class="mb-8">
                <div class="mb-3 p-3 bg-blue-50 border-l-4 border-blue-400 text-blue-700 text-sm">
                    <p><strong>Nota:</strong> Debe proporcionar al menos uno de los siguientes: Número de Booking, MBL o Número de Contenedor.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-6">
                    {{-- Número de Contenedor --}}
                    <div>
                        <x-form-input>
                            <x-slot:label>Número de Contenedor</x-slot:label>
                            <x-slot:input name="container_number" wire:model="container_number" placeholder="Ingrese número de contenedor"
                                          class="pr-10 {{ $errors->has('container_number') ? 'border-red-500' : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('container_number') }}</x-slot:error>
                        </x-form-input>
                    </div>

                    <!-- Tipo de Contenedor -->
                    <div>
                        <x-form-input>
                            <x-slot:label>Tipo de Contenedor</x-slot:label>
                            <x-slot:input
                                name="container_type"
                                wire:model="container_type"
                                class="w-full"
                                placeholder="Tipo de contenedor">
                            </x-slot:input>
                        </x-form-input>
                    </div>

                    {{-- MBL --}}
                    <div>
                        <x-form-input>
                            <x-slot:label>MBL</x-slot:label>
                            <x-slot:input name="bill_of_lading" wire:model="bill_of_lading" placeholder="Ingrese MBL"
                                          class="pr-10 {{ $errors->has('bill_of_lading') ? 'border-red-500' : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('bill_of_lading') }}</x-slot:error>
                        </x-form-input>
                    </div>

                    {{-- Monto - requerido --}}
                    <div>
                        <x-form-input>
                            <x-slot:label>Monto</x-slot:label>
                            <x-slot:input type="number" step="0.01" inputmode="decimal" name="shipment_amount" placeholder="0" wire:model="shipment_amount"
                                          class="pr-10 {{ $errors->has('shipment_amount') ? 'border-red-500' : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('shipment_amount') }}</x-slot:error>
                        </x-form-input>
                    </div>

                    {{-- Naviera - requerido --}}
                    <div>
                        <x-form-input>
                            <x-slot:label>Línea Naviera <span class="text-red-500">*</span></x-slot:label>
                            <x-slot:input name="shipping_line" wire:model="shipping_line" placeholder="Ingrese línea naviera"
                                          class="pr-10 {{ $errors->has('shipping_line') ? 'border-red-500' : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('shipping_line') }}</x-slot:error>
                        </x-form-input>
                    </div>

                    {{-- Estado - requerido --}}
                    <div>
                        <x-form-input>
                            <x-slot:label>Estado</x-slot:label>
                            <x-slot:input name="shipment_status" wire:model="shipment_status" placeholder="Ingrese estado"
                                          class="pr-10 {{ $errors->has('shipment_status') ? 'border-red-500' : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('shipment_status') }}</x-slot:error>
                        </x-form-input>
                    </div>

                    {{-- Factura de mercancía (N°) - requerido --}}
                    <div>
                        <x-form-input>
                            <x-slot:label>Factura de Mercancía</x-slot:label>
                            <x-slot:input name="merchandise_invoice" wire:model="merchandise_invoice" placeholder="Ingrese factura de mercancia"
                                          class="pr-10 {{ $errors->has('merchandise_invoice') ? 'border-red-500' : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('merchandise_invoice') }}</x-slot:error>
                        </x-form-input>
                    </div>
                </div>
            </div>

            {{-- Tracking y Puertos --}}
            <div class="mb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-6">
                    <div class="md:col-span-2">
                        <x-form-input>
                            <x-slot:label>Número de Booking</x-slot:label>
                            <x-slot:input name="tracking_id" wire:model="tracking_id" placeholder="Ingrese número de booking"
                                          class="pr-10 {{ $errors->has('tracking_id') ? 'border-red-500' : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('tracking_id') }}</x-slot:error>
                        </x-form-input>
                    </div>

                    <div>
                        <x-form-input>
                            <x-slot:label>Puerto de Embarque <span class="text-red-500">*</span></x-slot:label>
                            <x-slot:input name="departure_port" wire:model="departure_port" placeholder="Ingrese puerto de embarque"
                                          class="pr-10 {{ $errors->has('departure_port') ? 'border-red-500' : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('departure_port') }}</x-slot:error>
                        </x-form-input>
                    </div>

                    <div>
                        <x-form-input>
                            <x-slot:label>Puerto de Arribo <span class="text-red-500">*</span></x-slot:label>
                            <x-slot:input name="arrival_port" wire:model="arrival_port" placeholder="Ingrese puerto de arribo"
                                          class="pr-10 {{ $errors->has('arrival_port') ? 'border-red-500' : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('arrival_port') }}</x-slot:error>
                        </x-form-input>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <x-form-textarea label="" name="comment_stage_04" wireModel="comment" placeholder="Comentarios" />
            </div>

            <div class="mb-12 space-y-2">
                <input type="file" wire:model="attachment" class="hidden" id="file-upload-en-transito">
                <x-secondary-button onclick="document.getElementById('file-upload-en-transito').click()" class="group flex w-full items-center justify-center gap-[0.625rem]">
                    @svg('heroicon-o-paper-clip', 'w-5 h-5') <span>Adjuntar documentación...</span>
                </x-secondary-button>
                @if($attachment)
                    <div class="text-sm text-gray-600">Archivo seleccionado: {{ $attachment->getClientOriginalName() }}</div>
                @endif
                <div class="flex flex-col text-sm text-[#A5A3A3]">
                    <span>Tipo de formato .xls .xlsx .pdf</span><span>Tamaño máximo 5MB</span>
                </div>
            </div>

            <div class="flex gap-[1.875rem]">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-en-transito')" class="w-full">Cancelar</x-secondary-button>
                <x-primary-button wire:click="saveAndMove" class="w-full">Continuar</x-primary-button>
            </div>
        </x-modal>

        <x-modal name="modal-puerto" maxWidth="lg">
            <h3 class="mb-2 text-lg font-bold text-center text-light-blue">¿Cambiar la Orden de compra de etapa?</h3>
            @if ($currentTask)
                <div class="mb-5 text-center">
                    <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
                </div>
            @endif

            <div class="mb-8">
                <x-form-select label="" name="etapa"
                               :options="collect($columns)->pluck('name','id')->toArray()"
                               optionPlaceholder="Seleccionar etapa"
                               :value="$newColumnId" wire:model.live="newColumnId"
                               x-on:change="moveTaskToColumn($event.target.value)" />
            </div>

            <div class="mb-8">
                <x-form-input>
                    <x-slot:label>ETA Real (ATA) <span class="text-red-500">*</span></x-slot:label>
                    <x-slot:input type="date" name="date_ata" wire:model="date_ata"
                                  class="pr-10 {{ $errors->has('date_ata') ? 'border-red-500' : '' }}"></x-slot:input>
                    <x-slot:error>{{ $errors->first('date_ata') }}</x-slot:error>
                </x-form-input>
            </div>

            <div class="mb-8">
                <x-form-textarea label="" name="comment_stage_05" wireModel="comment" placeholder="Comentarios" />
            </div>

            <div class="mb-12 space-y-2">
                <input type="file" wire:model="attachment" class="hidden" id="file-upload-puerto">
                <x-secondary-button onclick="document.getElementById('file-upload-puerto').click()" class="group flex w-full items-center justify-center gap-[0.625rem]">
                    @svg('heroicon-o-paper-clip', 'w-5 h-5') <span>Adjuntar documentación...</span>
                </x-secondary-button>
                @if($attachment)
                    <div class="text-sm text-gray-600">Archivo seleccionado: {{ $attachment->getClientOriginalName() }}</div>
                @endif
                <div class="flex flex-col text-sm text-[#A5A3A3]">
                    <span>Tipo de formato .xls .xlsx .pdf</span><span>Tamaño máximo 5MB</span>
                </div>
            </div>

            <div class="flex gap-[1.875rem]">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-puerto')" class="w-full">Cancelar</x-secondary-button>
                <x-primary-button wire:click="saveAndMove" class="w-full">Continuar</x-primary-button>
            </div>
        </x-modal>

        <x-modal name="modal-alm-fiscal" maxWidth="lg">
            <h3 class="mb-2 text-lg font-bold text-center text-light-blue">¿Cambiar la Orden de compra de etapa?</h3>
            @if ($currentTask)
                <div class="mb-5 text-center">
                    <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
                </div>
            @endif

            <div class="mb-8">
                <x-form-select label="" name="etapa"
                               :options="collect($columns)->pluck('name','id')->toArray()"
                               optionPlaceholder="Seleccionar etapa"
                               :value="$newColumnId" wire:model.live="newColumnId"
                               x-on:change="moveTaskToColumn($event.target.value)" />
            </div>

            <div class="mb-8">
                <x-form-input>
                    <x-slot:label>Ingreso Almacén Fiscal <span class="text-red-500">*</span></x-slot:label>
                    <x-slot:input type="date" name="bonded_warehouse_enter" wire:model.live="bonded_warehouse_enter"
                                  class="pr-10 {{ $errors->has('bonded_warehouse_enter') ? 'border-red-500' : '' }}"></x-slot:input>
                    <x-slot:error>{{ $errors->first('bonded_warehouse_enter') }}</x-slot:error>
                </x-form-input>
            </div>

            <div class="mb-8">
                <x-form-input>
                    <x-slot:label>Salida Almacén Fiscal <span class="text-red-500">*</span></x-slot:label>
                    <x-slot:input type="date" name="bonded_warehouse_exit" wire:model.live="bonded_warehouse_exit"
                                  class="pr-10 {{ $errors->has('bonded_warehouse_exit') ? 'border-red-500' : '' }}"></x-slot:input>
                    <x-slot:error>{{ $errors->first('bonded_warehouse_exit') }}</x-slot:error>
                </x-form-input>
            </div>

            <div class="mb-8">
                <x-form-input>
                    <x-slot:label>ETA Real (ATA) <span class="text-red-500">*</span></x-slot:label>
                    <x-slot:input type="date" name="date_ata" wire:model="date_ata"
                                  class="pr-10 {{ $errors->has('date_ata') ? 'border-red-500' : '' }}"></x-slot:input>
                    <x-slot:error>{{ $errors->first('date_ata') }}</x-slot:error>
                </x-form-input>
            </div>

            <div class="mb-8">
                <x-form-textarea label="" name="comment_stage_06" wireModel="comment" placeholder="Comentarios" />
            </div>

            <div class="mb-12 space-y-2">
                <input type="file" wire:model="attachment" class="hidden" id="file-upload-alm-fiscal">
                <x-secondary-button onclick="document.getElementById('file-upload-alm-fiscal').click()" class="group flex w-full items-center justify-center gap-[0.625rem]">
                    @svg('heroicon-o-paper-clip', 'w-5 h-5') <span>Adjuntar documentación...</span>
                </x-secondary-button>
                @if($attachment)
                    <div class="text-sm text-gray-600">Archivo seleccionado: {{ $attachment->getClientOriginalName() }}</div>
                @endif
                <div class="flex flex-col text-sm text-[#A5A3A3]">
                    <span>Tipo de formato .xls .xlsx .pdf</span><span>Tamaño máximo 5MB</span>
                </div>
            </div>

            <div class="flex gap-[1.875rem]">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-alm-fiscal')" class="w-full">Cancelar</x-secondary-button>
                <x-primary-button wire:click="saveAndMove" class="w-full">Continuar</x-primary-button>
            </div>
        </x-modal>

        <x-modal name="modal-en-otra-zf" maxWidth="lg">
            <h3 class="mb-2 text-lg font-bold text-center text-light-blue">¿Cambiar la Orden de compra de etapa?</h3>
            @if ($currentTask)
                <div class="mb-5 text-center">
                    <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
                </div>
            @endif

            <div class="mb-8">
                <x-form-select label="" name="etapa"
                               :options="collect($columns)->pluck('name','id')->toArray()"
                               optionPlaceholder="Seleccionar etapa"
                               :value="$newColumnId" wire:model.live="newColumnId"
                               x-on:change="moveTaskToColumn($event.target.value)" />
            </div>

            <div class="mb-8">
                <x-form-textarea label="" name="comment_stage_07" wireModel="comment" placeholder="Comentarios" />
            </div>

            <div class="mb-12 space-y-2">
                <input type="file" wire:model="attachment" class="hidden" id="file-upload-en-otra-zf">
                <x-secondary-button onclick="document.getElementById('file-upload-en-otra-zf').click()" class="group flex w-full items-center justify-center gap-[0.625rem]">
                    @svg('heroicon-o-paper-clip', 'w-5 h-5') <span>Adjuntar documentación...</span>
                </x-secondary-button>
                @if($attachment)
                    <div class="text-sm text-gray-600">Archivo seleccionado: {{ $attachment->getClientOriginalName() }}</div>
                @endif
                <div class="flex flex-col text-sm text-[#A5A3A3]">
                    <span>Tipo de formato .xls .xlsx .pdf</span><span>Tamaño máximo 5MB</span>
                </div>
            </div>

            <div class="flex gap-[1.875rem]">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-en-otra-zf')" class="w-full">Cancelar</x-secondary-button>
                <x-primary-button wire:click="saveAndMove" class="w-full">Continuar</x-primary-button>
            </div>
        </x-modal>

        <x-modal name="modal-recibiendo-cdi" maxWidth="lg">
            <h3 class="mb-2 text-lg font-bold text-center text-light-blue">¿Cambiar la Orden de compra de etapa?</h3>
            @if ($currentTask)
                <div class="mb-5 text-center">
                    <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
                </div>
            @endif

            <div class="mb-8">
                <x-form-select label="" name="etapa"
                               :options="collect($columns)->pluck('name','id')->toArray()"
                               optionPlaceholder="Seleccionar etapa"
                               :value="$newColumnId" wire:model.live="newColumnId"
                               x-on:change="moveTaskToColumn($event.target.value)" />
            </div>

            <div class="mb-8">
                <x-form-textarea label="" name="comment_stage_08" wireModel="comment" placeholder="Comentarios" />
            </div>

            <div class="mb-12 space-y-2">
                <input type="file" wire:model="attachment" class="hidden" id="file-upload-recibiendo-cdi">
                <x-secondary-button onclick="document.getElementById('file-upload-recibiendo-cdi').click()" class="group flex w-full items-center justify-center gap-[0.625rem]">
                    @svg('heroicon-o-paper-clip', 'w-5 h-5') <span>Adjuntar documentación...</span>
                </x-secondary-button>
                @if($attachment)
                    <div class="text-sm text-gray-600">Archivo seleccionado: {{ $attachment->getClientOriginalName() }}</div>
                @endif
                <div class="flex flex-col text-sm text-[#A5A3A3]">
                    <span>Tipo de formato .xls .xlsx .pdf</span><span>Tamaño máximo 5MB</span>
                </div>
            </div>

            <div class="flex gap-[1.875rem]">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-recibiendo-cdi')" class="w-full">Cancelar</x-secondary-button>
                <x-primary-button wire:click="saveAndMove" class="w-full">Continuar</x-primary-button>
            </div>
        </x-modal>

        <x-modal name="modal-ingresada" maxWidth="lg">
            <h3 class="mb-2 text-lg font-bold text-center text-light-blue">¿Cambiar la Orden de compra de etapa?</h3>
            @if ($currentTask)
                <div class="mb-5 text-center">
                    <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
                </div>
            @endif

            <div class="mb-8">
                <x-form-select label="" name="etapa"
                               :options="collect($columns)->pluck('name','id')->toArray()"
                               optionPlaceholder="Seleccionar etapa"
                               :value="$newColumnId" wire:model.live="newColumnId"
                               x-on:change="moveTaskToColumn($event.target.value)" />
            </div>

            <div class="mb-8">
                <x-form-input>
                    <x-slot:label>Nota de Recibo</x-slot:label>
                    <x-slot:input type="text" placeholder="Ingrese nota de recibo" wire:model.live="receipt_note"></x-slot:input>
                </x-form-input>
            </div>

            <div class="mb-8">
                <x-form-textarea label="" name="comment_stage_09" wireModel="comment" placeholder="Comentarios" />
            </div>

            <div class="mb-12 space-y-2">
                <input type="file" wire:model="attachment" class="hidden" id="file-upload-ingresada">
                <x-secondary-button onclick="document.getElementById('file-upload-ingresada').click()" class="group flex w-full items-center justify-center gap-[0.625rem]">
                    @svg('heroicon-o-paper-clip', 'w-5 h-5') <span>Adjuntar documentación...</span>
                </x-secondary-button>
                @if($attachment)
                    <div class="text-sm text-gray-600">Archivo seleccionado: {{ $attachment->getClientOriginalName() }}</div>
                @endif
                <div class="flex flex-col text-sm text-[#A5A3A3]">
                    <span>Tipo de formato .xls .xlsx .pdf</span><span>Tamaño máximo 5MB</span>
                </div>
            </div>

            <div class="flex gap-[1.875rem]">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-ingresada')" class="w-full">Cancelar</x-secondary-button>
                <x-primary-button wire:click="saveAndMove" class="w-full">Continuar</x-primary-button>
            </div>
        </x-modal>

        <x-modal name="modal-anulada" maxWidth="lg">
            <h3 class="mb-2 text-lg font-bold text-center text-light-blue">¿Cambiar la Orden de compra de etapa?</h3>
            @if ($currentTask)
                <div class="mb-5 text-center">
                    <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
                </div>
            @endif

            <div class="mb-8">
                <x-form-select label="" name="etapa"
                               :options="collect($columns)->pluck('name','id')->toArray()"
                               optionPlaceholder="Seleccionar etapa"
                               :value="$newColumnId" wire:model.live="newColumnId"
                               x-on:change="moveTaskToColumn($event.target.value)" />
            </div>

            <div class="mb-8">
                <x-form-textarea label="" name="comment_stage_10" wireModel="comment" placeholder="Comentarios" />
            </div>

            <div class="mb-12 space-y-2">
                <input type="file" wire:model="attachment" class="hidden" id="file-upload-anulada">
                <x-secondary-button onclick="document.getElementById('file-upload-anulada').click()" class="group flex w-full items-center justify-center gap-[0.625rem]">
                    @svg('heroicon-o-paper-clip', 'w-5 h-5') <span>Adjuntar documentación...</span>
                </x-secondary-button>
                @if($attachment)
                    <div class="text-sm text-gray-600">Archivo seleccionado: {{ $attachment->getClientOriginalName() }}</div>
                @endif
                <div class="flex flex-col text-sm text-[#A5A3A3]">
                    <span>Tipo de formato .xls .xlsx .pdf</span><span>Tamaño máximo 5MB</span>
                </div>
            </div>

            <div class="flex gap-[1.875rem]">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'modal-anulada')" class="w-full">Cancelar</x-secondary-button>
                <x-primary-button wire:click="saveAndMove" class="w-full">Continuar</x-primary-button>
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
