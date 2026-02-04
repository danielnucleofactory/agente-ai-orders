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

                                        // Abrir el modal inmediatamente con estado de carga
                                        $dispatch('open-modal', 'modal-po-stage-change');
                                        
                                        // Cargar los datos de la tarea en segundo plano
                                        $wire.setCurrentTask(taskId, newColumn).then(function() {
                                            // Esperar un momento para que Livewire actualice el DOM y el modal esté completamente renderizado
                                            // Aumentar el timeout para dar tiempo a que el listener de open-modal termine de limpiar
                                            setTimeout(function() {
                                                // Poblar los inputs de fecha con los valores cargados de Livewire
                                                populateDateFieldsFromLivewire($wire);
                                            }, 150);
                                        }).catch(function(error) {
                                            console.error('Error al cargar datos de la tarea:', error);
                                        });
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

        <x-modal name="modal-po-stage-change" maxWidth="lg">
            <div class="flex flex-col max-h-[75vh] relative">
                {{-- Overlay de carga usando wire:loading --}}
                <div wire:loading wire:target="setCurrentTask" 
                     class="absolute inset-0 z-50 flex items-center justify-center bg-white bg-opacity-90 rounded-lg">
                    <div class="flex flex-col items-center">
                        <svg class="w-8 h-8 text-[#1AAD8A] animate-spin mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-sm font-medium text-gray-700">Cargando datos del formulario...</p>
                    </div>
                </div>

                {{-- Header fijo --}}
                <div class="flex-shrink-0 mb-3">
                    <h3 class="mb-2 text-lg font-bold text-center text-light-blue">¿Cambiar la Orden de compra de etapa?</h3>

                    @if ($currentTask)
                        <div class="mb-4 text-center">
                            <p class="text-[#171717] underline underline-offset-4">PO: {{ $currentTask['po'] }}</p>
                        </div>
                    @endif

                    <div class="mb-3">
                        <x-form-select label="" name="etapa"
                                       :options="collect($columns)->pluck('name','id')->toArray()"
                                       optionPlaceholder="Seleccionar etapa"
                                       :value="$newColumnId" wire:model.live="newColumnId"
                                       x-on:change="moveTaskToColumn($event.target.value)" />
                    </div>
                </div>

                {{-- Contenido scrolleable --}}
                <div class="flex-1 overflow-y-auto pr-2 -mr-2 mb-3 pt-2">
                    <div class="mb-4">
                {{-- Etapa 1: Nuevo (ID 1) --}}
                <div class="{{ $newColumnId == 1 ? '' : 'hidden' }}">
                    <div class="mb-8">
                        <x-form-textarea label="" name="comment_stage_01" wireModel="comment" placeholder="Comentarios" />
                    </div>
                </div>

                {{-- Etapa 2: Producción (ID 2) --}}
                <div class="{{ $newColumnId == 2 ? '' : 'hidden' }}">
                    <div class="mb-8 pt-2" wire:ignore>
                        <x-form-input>
                            <x-slot:label>Carga Lista Variable <span class="text-red-500">*</span></x-slot:label>
                            <x-slot:input type="date" name="date_variable_date" wire:model="date_variable_date" class="pr-10 {{ $errors->has('date_variable_date') ? 'border-red-500'  : '' }}">
                            </x-slot:input>
                            {{-- Error oculto --}}
                        </x-form-input>
                    </div>
                    <div class="mb-8" wire:ignore>
                        <x-form-input>
                            <x-slot:label>Carga Lista Teórica</x-slot:label>
                            <x-slot:input type="date" name="date_theorical_load" wire:model="date_theorical_load" readonly class="pr-10 bg-gray-100 cursor-not-allowed"></x-slot:input>
                        </x-form-input>
                    </div>
                    <div class="mb-8">
                        <x-form-select
                            label="Proveedor de Servicio"
                            name="service_provider"
                            wire:model.live="service_provider"
                            :options="$serviceProviderArray"
                            :error="false"
                        />
                        {{-- Error oculto --}}
                    </div>
                    <div class="mb-8 hidden">
                        <x-form-input>
                            <x-slot:label>Agente de Carga <span class="text-red-500">*</span></x-slot:label>
                            <x-slot:input type="text" name="forwarder_name" placeholder="Ingrese agente de carga" wire:model="forwarder_name" class="pr-10 {{ $errors->has('forwarder_name') ? 'border-red-500'  : '' }}">
                            </x-slot:input>
                            {{-- Error oculto --}}
                        </x-form-input>
                    </div>
                    <div class="mb-8">
                        <x-form-textarea label="" name="comment_stage_02" wireModel="comment" placeholder="Comentarios" />
                    </div>
                </div>

                {{-- Etapa 3: Booking (ID 3) --}}
                {{-- Mismos campos que Producción + campos de tracking --}}
                <div class="{{ $newColumnId == 3 ? '' : 'hidden' }}">
                    <div class="mb-8 pt-2" wire:ignore>
                        <x-form-input>
                            <x-slot:label>Carga Lista Variable <span class="text-red-500">*</span></x-slot:label>
                            <x-slot:input type="date" name="date_variable_date" wire:model="date_variable_date" class="pr-10 {{ $errors->has('date_variable_date') ? 'border-red-500'  : '' }}">
                            </x-slot:input>
                        </x-form-input>
                    </div>
                    <div class="mb-8" wire:ignore>
                        <x-form-input>
                            <x-slot:label>Carga Lista Teórica</x-slot:label>
                            <x-slot:input type="date" name="date_theorical_load" wire:model="date_theorical_load" readonly class="pr-10 bg-gray-100 cursor-not-allowed"></x-slot:input>
                        </x-form-input>
                    </div>
                    <div class="mb-8">
                        <x-form-select
                            label="Proveedor de Servicio"
                            name="service_provider"
                            wire:model.live="service_provider"
                            :options="$serviceProviderArray"
                            :error="false"
                        />
                    </div>

                    {{-- Campos de tracking para habilitar seguimiento --}}
                    <div class="mb-4">
                        <div class="mb-3 p-3 bg-blue-50 border-l-4 border-blue-400 text-blue-700 text-sm">
                            <p><strong>Nota:</strong> Debe proporcionar al menos uno de los siguientes: Número de Booking, MBL o Número de Contenedor.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-4">
                            <div>
                                <x-form-input>
                                    <x-slot:label>Número de Contenedor</x-slot:label>
                                    <x-slot:input name="container_number" wire:model="container_number" placeholder="Ingrese número de contenedor" autocomplete="off"
                                                  class="pr-10 {{ $errors->has('container_number') ? 'border-red-500' : '' }}"></x-slot:input>
                                </x-form-input>
                            </div>

                            <div>
                                <x-form-input>
                                    <x-slot:label>MBL</x-slot:label>
                                    <x-slot:input name="mbl_number" wire:model="mbl_number" placeholder="Ingrese MBL"
                                                  class="pr-10 {{ $errors->has('mbl_number') ? 'border-red-500' : '' }}"></x-slot:input>
                                </x-form-input>
                            </div>

                            <div class="md:col-span-2">
                                <x-form-input>
                                    <x-slot:label>Número de Booking</x-slot:label>
                                    <x-slot:input name="tracking_id" wire:model="tracking_id" placeholder="Ingrese número de booking"
                                                  class="pr-10 {{ $errors->has('tracking_id') ? 'border-red-500' : '' }}"></x-slot:input>
                                </x-form-input>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <x-form-textarea label="" name="comment_stage_03" wireModel="comment" placeholder="Comentarios" />
                    </div>
                </div>

                {{-- Etapa 4: Consolidador (ID 4) --}}
                <div class="{{ $newColumnId == 4 ? '' : 'hidden' }}">
                    <div class="mb-8">
                        <x-form-textarea label="" name="comment_stage_04" wireModel="comment" placeholder="Comentarios" />
                    </div>
                </div>

                {{-- Etapa 5: En Tránsito (ID 5) --}}
                <div class="{{ $newColumnId == 5 ? '' : 'hidden' }}">
                    <div class="mb-8 pt-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-5 gap-y-6">
                            <div wire:ignore>
                                <x-form-input>
                                    <x-slot:label>ETD Real (ATD) <span class="text-red-500">*</span></x-slot:label>
                                    <x-slot:input type="date" name="date_atd" wire:model="date_atd"
                                                  class="pr-10 {{ $errors->has('date_atd') ? 'border-red-500' : '' }}"></x-slot:input>
                                    {{-- Error oculto --}}
                                </x-form-input>
                            </div>

                            <div wire:ignore>
                                <x-form-input>
                                    <x-slot:label>ETA Inicial <span class="text-red-500">*</span></x-slot:label>
                                    <x-slot:input type="date" name="date_eta_initial" wire:model="date_eta_initial"
                                                  class="pr-10 {{ $errors->has('date_eta_initial') ? 'border-red-500' : '' }}"></x-slot:input>
                                    {{-- Error oculto --}}
                                </x-form-input>
                            </div>

                            <div wire:ignore>
                                <x-form-input>
                                    <x-slot:label>ETA Variable <span class="text-red-500">*</span></x-slot:label>
                                    <x-slot:input type="date" name="date_eta" wire:model="date_eta"
                                                  class="pr-10 {{ $errors->has('date_eta') ? 'border-red-500' : '' }}"></x-slot:input>
                                    {{-- Error oculto --}}
                                </x-form-input>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-6">
                            {{-- Campos de tracking ocultos - ya se capturaron en el paso a Booking --}}
                            <div class="hidden">
                                <x-form-input>
                                    <x-slot:label>Número de Contenedor</x-slot:label>
                                    <x-slot:input name="container_number" wire:model="container_number" placeholder="Ingrese número de contenedor" autocomplete="off"
                                                  class="pr-10 {{ $errors->has('container_number') ? 'border-red-500' : '' }}"></x-slot:input>
                                </x-form-input>
                            </div>

                            <div>
                                <x-form-select
                                    label="Tipo de Contenedor"
                                    name="container_type"
                                    wire:model.live="container_type"
                                    :options="$containerTypeArray"
                                    :error="$errors->has('container_type')" />
                                {{-- Error oculto --}}
                            </div>

                            <div class="hidden">
                                <x-form-input>
                                    <x-slot:label>MBL</x-slot:label>
                                    <x-slot:input name="mbl_number" wire:model="mbl_number" placeholder="Ingrese MBL"
                                                  class="pr-10 {{ $errors->has('mbl_number') ? 'border-red-500' : '' }}"></x-slot:input>
                                </x-form-input>
                            </div>

                            <div>
                                <x-form-input>
                                    <x-slot:label>Monto</x-slot:label>
                                    <x-slot:input type="number" step="0.01" inputmode="decimal" name="freight_amount" placeholder="0" wire:model="freight_amount" autocomplete="off"
                                                  class="pr-10 {{ $errors->has('freight_amount') ? 'border-red-500' : '' }}"></x-slot:input>
                                    {{-- Error oculto --}}
                                </x-form-input>
                            </div>

                            <div>
                                <x-form-select
                                    label="Línea Naviera <span class='text-red-500'>*</span>"
                                    name="shipping_line"
                                    wire:model.live="shipping_line"
                                    :options="$shippingLineArray"
                                    :error="$errors->has('shipping_line')"
                                    :showError="false" />
                            </div>

                            <div>
                                <x-form-input>
                                    <x-slot:label>Estado de Llegada</x-slot:label>
                                    <x-slot:input name="arrival_status" wire:model="arrival_status" readonly disabled
                                                  class="pr-10 bg-gray-100 cursor-not-allowed"></x-slot:input>
                                    {{-- Campo de solo lectura --}}
                                </x-form-input>
                            </div>

                            <div>
                                <x-form-input>
                                    <x-slot:label>Factura de Mercancía</x-slot:label>
                                    <x-slot:input name="factura_merca" wire:model="factura_merca" placeholder="Ingrese factura de mercancía"
                                                  class="pr-10 {{ $errors->has('factura_merca') ? 'border-red-500' : '' }}"></x-slot:input>
                                    {{-- Error oculto --}}
                                </x-form-input>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-6">
                            {{-- Número de Booking oculto - ya se capturó en el paso a Booking --}}
                            <div class="md:col-span-2 hidden">
                                <x-form-input>
                                    <x-slot:label>Número de Booking</x-slot:label>
                                    <x-slot:input name="tracking_id" wire:model="tracking_id" placeholder="Ingrese número de booking"
                                                  class="pr-10 {{ $errors->has('tracking_id') ? 'border-red-500' : '' }}"></x-slot:input>
                                </x-form-input>
                            </div>

                            <div>
                                <x-form-select
                                    label="Puerto de Embarque <span class='text-red-500'>*</span>"
                                    name="departure_port"
                                    wire:model.live="departure_port"
                                    :options="$departurePortArray"
                                    :error="$errors->has('departure_port')"
                                    :showError="false" />
                            </div>

                            <div>
                                <x-form-select
                                    label="Puerto de Arribo <span class='text-red-500'>*</span>"
                                    name="arrival_port"
                                    wire:model.live="arrival_port"
                                    :options="$arrivalPortArray"
                                    :error="$errors->has('arrival_port')"
                                    :showError="false" />
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <x-form-textarea label="" name="comment_stage_05" wireModel="comment" placeholder="Comentarios" />
                    </div>
                </div>

                {{-- Etapa 6: Puerto (ID 6) --}}
                <div class="{{ $newColumnId == 6 ? '' : 'hidden' }}">
                    <div class="mb-8 pt-2" wire:ignore>
                        <x-form-input>
                            <x-slot:label>ETA Real (ATA) <span class="text-red-500">*</span></x-slot:label>
                            <x-slot:input type="date" name="date_ata_stage6" wire:model="date_ata"
                                          class="pr-10 {{ $errors->has('date_ata') ? 'border-red-500' : '' }}"></x-slot:input>
                            {{-- Error oculto --}}
                        </x-form-input>
                    </div>
                    <div class="mb-8">
                        <x-form-textarea label="" name="comment_stage_06" wireModel="comment" placeholder="Comentarios" />
                    </div>
                </div>

                {{-- Etapa 7: Almacén Fiscal (ID 7) --}}
                <div class="{{ $newColumnId == 7 ? '' : 'hidden' }}">
                    <div class="mb-8 pt-2" wire:ignore>
                        <x-form-input>
                            <x-slot:label>Ingreso Almacén Fiscal <span class="text-red-500">*</span></x-slot:label>
                            <x-slot:input type="date" name="bonded_warehouse_enter" wire:model="bonded_warehouse_enter"
                                          class="pr-10 {{ $errors->has('bonded_warehouse_enter') ? 'border-red-500' : '' }}"></x-slot:input>
                            {{-- Error oculto --}}
                        </x-form-input>
                    </div>
                    <div class="mb-8" wire:ignore>
                        <x-form-input>
                            <x-slot:label>Salida Almacén Fiscal <span class="text-red-500">*</span></x-slot:label>
                            <x-slot:input type="date" name="bonded_warehouse_exit" wire:model="bonded_warehouse_exit"
                                          class="pr-10 {{ $errors->has('bonded_warehouse_exit') ? 'border-red-500' : '' }}"></x-slot:input>
                            {{-- Error oculto --}}
                        </x-form-input>
                    </div>
                    <div class="mb-8" wire:ignore>
                        <x-form-input>
                            <x-slot:label>ETA Real (ATA) <span class="text-red-500">*</span></x-slot:label>
                            <x-slot:input type="date" name="date_ata_stage7" wire:model="date_ata"
                                          class="pr-10 {{ $errors->has('date_ata') ? 'border-red-500' : '' }}"></x-slot:input>
                            {{-- Error oculto --}}
                        </x-form-input>
                    </div>
                    <div class="mb-8">
                        <x-form-textarea label="" name="comment_stage_07" wireModel="comment" placeholder="Comentarios" />
                    </div>
                </div>

                {{-- Etapa 8: En otra ZF (ID 8) --}}
                <div class="{{ $newColumnId == 8 ? '' : 'hidden' }}">
                    <div class="mb-8">
                        <x-form-textarea label="" name="comment_stage_08" wireModel="comment" placeholder="Comentarios" />
                    </div>
                </div>

                {{-- Etapa 9: Recibiendo CDI (ID 9) --}}
                <div class="{{ $newColumnId == 9 ? '' : 'hidden' }}">
                    <div class="mb-8" wire:ignore>
                        <x-form-input>
                            <x-slot:label>Fecha Disp. Bodega Estimada <span class="text-red-500">*</span></x-slot:label>
                            <x-slot:input type="date" name="estimated_dc_availability_date" wire:model="estimated_dc_availability_date"
                                          class="pr-10 {{ $errors->has('estimated_dc_availability_date') ? 'border-red-500' : '' }}"></x-slot:input>
                            {{-- Error oculto --}}
                        </x-form-input>
                    </div>
                    <div class="mb-8">
                        <x-form-textarea label="" name="comment_stage_09" wireModel="comment" placeholder="Comentarios" />
                    </div>
                </div>

                {{-- Etapa 10: Ingresada (ID 10) --}}
                <div class="{{ $newColumnId == 10 ? '' : 'hidden' }}">
                    <div class="mb-8">
                        <x-form-input>
                            <x-slot:label>Nota de Recibo</x-slot:label>
                            <x-slot:input type="text" placeholder="Ingrese nota de recibo" wire:model.live="receipt_note"></x-slot:input>
                        </x-form-input>
                    </div>
                    <div class="mb-8">
                        <x-form-textarea label="" name="comment_stage_10" wireModel="comment" placeholder="Comentarios" />
                    </div>
                </div>

                {{-- Etapa 11: Anulada (ID 11) --}}
                <div class="{{ $newColumnId == 11 ? '' : 'hidden' }}">
                    <div class="mb-8">
                        <x-form-textarea label="" name="comment_stage_11" wireModel="comment" placeholder="Comentarios" />
                    </div>
                </div>
                    </div>
                </div>

                {{-- Footer fijo --}}
                <div class="flex-shrink-0 space-y-3 pt-2 border-t border-gray-200">
                    <div class="space-y-2">
                        <input type="file" wire:model="attachment" class="hidden" x-ref="fileInput" id="file-upload-po-stage-change">
                        <x-secondary-button onclick="document.getElementById('file-upload-po-stage-change').click()" class="group flex w-full items-center justify-center gap-[0.625rem]">
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

                    {{-- Mostrar errores de validación --}}
                    @if ($errors->any())
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-red-600 font-medium text-sm mb-1">Por favor corrija los siguientes errores:</p>
                            <ul class="list-disc list-inside text-red-500 text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="flex gap-[1.875rem]">
                        <x-secondary-button wire:click="cancelModal" class="w-full">Cancelar</x-secondary-button>
                        <x-primary-button 
                            x-on:click="syncKanbanDateFieldsAndSave($wire, $event.currentTarget)"
                            class="w-full"
                            id="btn-continuar-stage">Continuar</x-primary-button>
                    </div>
                </div>
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

    <script>
        // Asegurar que el scroll comience desde arriba cuando se abre el modal
        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('open-modal', function(event) {
                if (event.detail === 'modal-po-stage-change') {
                    setTimeout(function() {
                        const scrollableContent = document.querySelector('[name="modal-po-stage-change"]')?.closest('div[x-data]')?.querySelector('.overflow-y-auto');
                        if (scrollableContent) {
                            scrollableContent.scrollTop = 0;
                        }
                    }, 100);
                }
            });

            // Limpiar campos del modal cuando se cierra
            window.addEventListener('close-modal', function(event) {
                if (event.detail === 'modal-po-stage-change') {
                    // Limpiar todos los inputs del modal, incluyendo los que están en wire:ignore
                    const modal = document.querySelector('[name="modal-po-stage-change"]');
                    if (modal) {
                        const inputs = modal.querySelectorAll('input, select, textarea');
                        inputs.forEach(function(input) {
                            // Limpiar campos de fecha dentro de wire:ignore
                            if (input.closest('[wire\\:ignore]')) {
                                // Si tiene Flatpickr, limpiarlo
                                if (input._flatpickr) {
                                    try {
                                        input._flatpickr.clear();
                                    } catch (e) {
                                        console.warn('Error clearing Flatpickr:', e);
                                    }
                                }
                                // Limpiar el valor del input
                                input.value = '';
                                input.removeAttribute('data-date-value');
                            } else {
                                // Limpiar otros campos normalmente
                                if (input.type === 'checkbox' || input.type === 'radio') {
                                    input.checked = false;
                                } else {
                                    input.value = '';
                                }
                            }
                        });
                    }
                }
            });

            // Limpiar campos ANTES de abrir el modal (para asegurar que estén limpios)
            // IMPORTANTE: Limpiar inmediatamente, no con setTimeout, para evitar condición de carrera
            window.addEventListener('open-modal', function(event) {
                if (event.detail === 'modal-po-stage-change') {
                    // Limpiar inmediatamente cuando se abre el modal, antes de que setCurrentTask se ejecute
                    // Esto evita que populateDateFieldsFromLivewire intente poblar campos que luego se limpian
                    const modal = document.querySelector('[name="modal-po-stage-change"]');
                    if (modal) {
                        // Limpiar todos los campos de fecha dentro de wire:ignore
                        const dateInputs = modal.querySelectorAll('[wire\\:ignore] input[type="date"], [wire\\:ignore] input.flatpickr-initialized');
                        dateInputs.forEach(function(input) {
                            if (input._flatpickr) {
                                try {
                                    input._flatpickr.clear();
                                } catch (e) {
                                    console.warn('Error clearing Flatpickr on open:', e);
                                }
                            }
                            input.value = '';
                            input.removeAttribute('data-date-value');
                        });
                    } else {
                        // Si el modal no está en el DOM aún, esperar un momento muy corto
                        setTimeout(function() {
                            const modal = document.querySelector('[name="modal-po-stage-change"]');
                            if (modal) {
                                const dateInputs = modal.querySelectorAll('[wire\\:ignore] input[type="date"], [wire\\:ignore] input.flatpickr-initialized');
                                dateInputs.forEach(function(input) {
                                    if (input._flatpickr) {
                                        try {
                                            input._flatpickr.clear();
                                        } catch (e) {
                                            console.warn('Error clearing Flatpickr on open:', e);
                                        }
                                    }
                                    input.value = '';
                                    input.removeAttribute('data-date-value');
                                });
                            }
                        }, 10);
                    }
                }
            });
        });

        /**
         * Pobla los inputs de fecha (que están en wire:ignore) con los valores
         * cargados desde Livewire después de setCurrentTask.
         * 
         * Esto es necesario porque wire:ignore impide que Livewire actualice
         * el DOM de estos inputs, así que debemos hacerlo manualmente.
         * 
         * @param {Object} $wire - El objeto $wire de Livewire pasado desde Alpine.js
         * @param {Number} attempts - Número de intentos realizados (para limitar reintentos)
         */
        function populateDateFieldsFromLivewire($wire, attempts) {
            attempts = attempts || 0;
            const MAX_ATTEMPTS = 15; // máximo ~3 segundos (15 * 200ms)
            
            console.log('Poblando campos de fecha desde Livewire...');
            
            // Buscar el modal primero para asegurar que estamos buscando dentro del contexto correcto
            const modal = document.querySelector('[name="modal-po-stage-change"]');
            
            if (!modal) {
                if (attempts >= MAX_ATTEMPTS) {
                    console.error('Modal no encontrado después de ' + MAX_ATTEMPTS + ' intentos. Abortando.');
                    return;
                }
                console.warn('Modal no encontrado, reintentando en 200ms... (intento ' + (attempts + 1) + '/' + MAX_ATTEMPTS + ')');
                setTimeout(function() {
                    populateDateFieldsFromLivewire($wire, attempts + 1);
                }, 200);
                return;
            }
            
            // Lista de todos los campos de fecha que pueden estar en el modal
            const dateFields = [
                'date_variable_date',
                'date_theorical_load',
                'date_booking_request',
                'date_booking_authorized',
                'date_etd_initial',
                'date_etd',
                'date_atd',
                'date_eta',
                'date_eta_initial',
                'date_ata',
                'bonded_warehouse_enter',
                'bonded_warehouse_exit',
                'estimated_dc_availability_date'
            ];
            
            let populatedCount = 0;
            
            dateFields.forEach(function(fieldName) {
                // Buscar el input dentro del modal por wire:model o por name
                const input = modal.querySelector(`input[wire\\:model="${fieldName}"]`) ||
                             modal.querySelector(`input[name="${fieldName}"]`);
                
                if (input) {
                    // NO limpiar el campo aquí - ya fue limpiado por el listener de open-modal
                    // Solo obtener el valor desde Livewire y poblar si existe
                    const value = $wire.get(fieldName);
                    
                    if (value) {
                        input.value = value;
                        input.setAttribute('data-date-value', value);
                        
                        // Si tiene Flatpickr, establecer la fecha
                        if (input._flatpickr && typeof input._flatpickr.setDate === 'function') {
                            try {
                                input._flatpickr.setDate(value, false);
                            } catch (e) {
                                console.warn('Error setting Flatpickr date:', e);
                            }
                        } else if (fieldName === 'date_theorical_load') {
                            // Si no tiene Flatpickr, esperar un poco y reintentar
                            setTimeout(function() {
                                if (input._flatpickr && typeof input._flatpickr.setDate === 'function') {
                                    input._flatpickr.setDate(value, false);
                                } else {
                                    // Si aún no tiene Flatpickr, establecer el valor directamente
                                    input.value = value;
                                    input.setAttribute('data-date-value', value);
                                }
                            }, 150);
                        }
                        populatedCount++;
                        console.log('✓ Poblado', fieldName, '=', value);
                    } else {
                        console.log('○ Campo vacío', fieldName);
                    }
                }
            });
            
            console.log(`Total de campos de fecha poblados: ${populatedCount}`);
        }

        /**
         * Sincroniza los campos de fecha dentro de wire:ignore con Livewire
         * antes de ejecutar saveAndMove()
         * 
         * @param {Object} $wire - El objeto $wire de Livewire pasado desde Alpine.js
         * @param {HTMLElement} button - El botón que disparó la acción (opcional)
         */
        async function syncKanbanDateFieldsAndSave($wire, button) {
            console.log('Sincronizando campos de fecha del modal Kanban...');

            if (!$wire) {
                console.error('No se recibió el componente $wire');
                return;
            }

            // Mostrar estado de carga en el botón - mantener colores primarios
            if (button) {
                button.disabled = true;
                button.dataset.originalText = button.innerHTML;
                // Mantener el fondo verde (primario) y usar spinner blanco
                button.style.backgroundColor = '#1AAD8A';
                button.style.opacity = '1';
                button.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span class="text-white font-medium">Procesando...</span>';
            }

            try {
                // Buscar el modal del cambio de etapa
                const modal = document.querySelector('[name="modal-po-stage-change"]');
                
                // Buscar TODOS los inputs de fecha con wire:ignore que estén VISIBLES
                // (es decir, que no estén dentro de un div con clase 'hidden')
                let wireIgnoreInputs = [];
                
                // Buscar todos los divs con wire:ignore que contengan inputs de fecha
                // Incluir inputs que puedan haber sido convertidos a "text" por Flatpickr
                const allWireIgnoreDivs = document.querySelectorAll('[wire\\:ignore]');
                allWireIgnoreDivs.forEach(function(div) {
                    // Verificar que el div NO esté dentro de un contenedor oculto
                    const parentWithHidden = div.closest('.hidden');
                    if (!parentWithHidden) {
                        // Buscar inputs de fecha dentro de este div
                        // Incluir inputs type="date" y también inputs con clase flatpickr-initialized o que tengan wire:model con "date"
                        const dateInputs = div.querySelectorAll('input[type="date"], input.flatpickr-initialized, input[name*="date"], input[name*="Date"]');
                        dateInputs.forEach(function(input) {
                            // Verificar que tenga wire:model relacionado con fechas
                            const wireModel = input.getAttribute('wire:model') ||
                                             input.getAttribute('wire:model.live') ||
                                             input.getAttribute('wire:model.defer') ||
                                             input.getAttribute('wire:model.lazy');
                            if (wireModel && wireModel.toLowerCase().includes('date')) {
                                wireIgnoreInputs.push(input);
                            }
                        });
                    }
                });
                
                console.log('Inputs de fecha visibles con wire:ignore:', wireIgnoreInputs.length);

                // Sincronizar valores de campos con wire:ignore usando $wire.set()
                let syncCount = 0;
                let syncPromises = [];
                
                wireIgnoreInputs.forEach(function(input) {
                    const wireModel = input.getAttribute('wire:model') ||
                                     input.getAttribute('wire:model.live') ||
                                     input.getAttribute('wire:model.defer') ||
                                     input.getAttribute('wire:model.lazy');

                    if (wireModel) {
                        // Obtener el valor del input (puede ser del input original o de Flatpickr)
                        let value = null;

                        if (input._flatpickr && input._flatpickr.selectedDates.length > 0) {
                            // Si tiene Flatpickr, usar el valor de Flatpickr
                            value = input._flatpickr.formatDate(input._flatpickr.selectedDates[0], 'Y-m-d');
                        } else {
                            // Si no tiene Flatpickr, usar el valor del input directamente
                            value = input.value || input.getAttribute('data-date-value') || null;
                        }

                        // Usar $wire.set() para establecer el valor - devuelve una promesa en Livewire 3
                        try {
                            const setPromise = $wire.set(wireModel, value, false); // false = no commit inmediato
                            if (setPromise && typeof setPromise.then === 'function') {
                                syncPromises.push(setPromise);
                            }
                            syncCount++;
                            console.log('✓ Sincronizado', wireModel, '=', value !== null ? value : '(vacío/null)');
                        } catch (e) {
                            console.warn('⚠ Error al sincronizar', wireModel, ':', e);
                        }
                    } else {
                        console.warn('⚠ Input sin wire:model:', input.name || input.id || 'sin nombre');
                    }
                });
                
                console.log(`Total de campos sincronizados: ${syncCount} de ${wireIgnoreInputs.length}`);

                // Esperar a que todas las sincronizaciones se completen
                if (syncPromises.length > 0) {
                    await Promise.all(syncPromises);
                }

                // Forzar commit de todos los cambios pendientes antes de saveAndMove
                if ($wire.$commit && typeof $wire.$commit === 'function') {
                    console.log('Ejecutando $commit para sincronizar cambios...');
                    await $wire.$commit();
                }

                // Pequeña espera adicional para asegurar que el DOM esté sincronizado
                await new Promise(resolve => setTimeout(resolve, 100));

                console.log('Ejecutando saveAndMove...');
                
                // Llamar a saveAndMove y esperar el resultado
                const result = await $wire.saveAndMove();
                console.log('saveAndMove completado:', result);
                
                // Manejar el resultado
                if (result && result.success) {
                    console.log('✓ PO movida correctamente');
                    // Cerrar el modal manualmente por si el dispatch no llegó
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'modal-po-stage-change' }));
                    // Refrescar el kanban
                    $wire.$refresh();
                } else if (result && !result.success) {
                    console.warn('✗ Error al mover PO:', result.message);
                    // El mensaje ya se muestra en el modal vía session flash
                }
                
            } catch (e) {
                console.error('Error en syncKanbanDateFieldsAndSave:', e);
                
                // Si es un error de validación de Livewire, no mostrar alerta (los errores se muestran en el modal)
                if (e.name !== 'ValidationException' && !e.message?.includes('validation')) {
                    // Solo mostrar alerta para errores inesperados
                    console.error('Error inesperado:', e.message || e);
                }
            } finally {
                // Restaurar el botón
                if (button) {
                    button.disabled = false;
                    button.style.backgroundColor = '';
                    button.style.opacity = '';
                    if (button.dataset.originalText) {
                        button.innerHTML = button.dataset.originalText;
                    }
                }
            }
        }
    </script>
</div>
