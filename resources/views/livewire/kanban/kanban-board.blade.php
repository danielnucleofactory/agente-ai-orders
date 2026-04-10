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

    <div class="flex overflow-x-auto gap-4 pb-4 w-full kanban-container" wire:poll.30000ms>
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
                                        $wire.setCurrentTask(taskId, newColumn).catch(function(error) {
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

    <x-modal-success name="success-modal">
        <x-slot:title>
            Datos guardados exitosamente
        </x-slot:title>

        <x-slot:description>
            @if ($currentTask)
                La orden de compra {{ $currentTask['po'] }} ha sido movida correctamente a la nueva etapa.
            @else
                La orden de compra ha sido movida correctamente a la nueva etapa.
            @endif
        </x-slot:description>

        <x-primary-button wire:click="$dispatch('close-modal', 'success-modal')" class="w-full">
            Cerrar
        </x-primary-button>
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
                    <div class="mb-8 pt-2">
                        <x-date-picker wire:model="date_variable_date" label="Carga Lista Variable <span class='text-red-500'>*</span>" :error="$errors->first('date_variable_date')" />
                    </div>
                    <div class="mb-8">
                        <x-date-picker wire:model="date_theorical_load" label="Carga Lista Teórica" readonly />
                    </div>
                    <div class="mb-8">
                        <x-form-select
                            label="Proveedor de Servicio"
                            name="service_provider"
                            wire:model.live="service_provider"
                            :options="$serviceProviderArray"
                            :value="$service_provider"
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
                    <div class="mb-8 pt-2">
                        <x-date-picker wire:model="date_variable_date" label="Carga Lista Variable <span class='text-red-500'>*</span>" :error="$errors->first('date_variable_date')" />
                    </div>
                    <div class="mb-8">
                        <x-date-picker wire:model="date_theorical_load" label="Carga Lista Teórica" readonly />
                    </div>
                    <div class="mb-8">
                        <x-form-select
                            label="Proveedor de Servicio"
                            name="service_provider"
                            wire:model.live="service_provider"
                            :options="$serviceProviderArray"
                            :value="$service_provider"
                            :error="false"
                        />
                    </div>

                    <div class="mb-8">
                        <x-form-select
                            label="Naviera"
                            name="shipping_line"
                            wire:model.live="shipping_line"
                            :options="$shippingLineArray"
                            :value="$shipping_line"
                            :error="false"
                        />
                    </div>

                    {{-- Campos de tracking para habilitar seguimiento --}}
                    <div class="mb-4">
                        <div class="mb-3 p-3 bg-blue-50 border-l-4 border-blue-400 text-blue-700 text-sm">
                            <p><strong>Nota:</strong> Debe proporcionar el número de contenedor para avanzar.</p>
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
                                    <x-slot:label>Documento de tránsito</x-slot:label>
                                    <x-slot:input name="mbl_number" wire:model="mbl_number" placeholder="Ingrese Documento de tránsito"
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
                            <div>
                                <x-date-picker wire:model="date_atd" label="ETD Real (ATD) <span class='text-red-500'>*</span>" :error="$errors->first('date_atd')" />
                            </div>

                            <div>
                                <x-date-picker wire:model="date_eta_initial" label="ETA Inicial <span class='text-red-500'>*</span>" :error="$errors->first('date_eta_initial')" />
                            </div>

                            <div>
                                <x-date-picker wire:model="date_eta" label="ETA Variable <span class='text-red-500'>*</span>" :error="$errors->first('date_eta')" />
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
                                    :value="$container_type"
                                    :error="$errors->has('container_type')" />
                                {{-- Error oculto --}}
                            </div>

                            <div class="hidden">
                                <x-form-input>
                                    <x-slot:label>Documento de tránsito</x-slot:label>
                                    <x-slot:input name="mbl_number" wire:model="mbl_number" placeholder="Ingrese Documento de tránsito"
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
                                    :value="$shipping_line"
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
                                    :value="$departure_port"
                                    :error="$errors->has('departure_port')"
                                    :showError="false" />
                            </div>

                            <div>
                                <x-form-select
                                    label="Puerto de Arribo <span class='text-red-500'>*</span>"
                                    name="arrival_port"
                                    wire:model.live="arrival_port"
                                    :options="$arrivalPortArray"
                                    :value="$arrival_port"
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
                    <div class="mb-8 pt-2">
                        <x-date-picker wire:model="date_ata" label="ETA Real (ATA) <span class='text-red-500'>*</span>" :error="$errors->first('date_ata')" />
                    </div>
                    <div class="mb-8">
                        <x-form-textarea label="" name="comment_stage_06" wireModel="comment" placeholder="Comentarios" />
                    </div>
                </div>

                {{-- Etapa 7: Almacén Fiscal (ID 7) --}}
                <div class="{{ $newColumnId == 7 ? '' : 'hidden' }}">
                    <div class="mb-8 pt-2">
                        <x-date-picker wire:model="bonded_warehouse_enter" label="Ingreso Almacén Fiscal <span class='text-red-500'>*</span>" :error="$errors->first('bonded_warehouse_enter')" />
                    </div>
                    <div class="mb-8">
                        <x-date-picker wire:model="bonded_warehouse_exit" label="Salida Almacén Fiscal" :error="$errors->first('bonded_warehouse_exit')" />
                    </div>
                    <div class="mb-8">
                        <x-date-picker wire:model="date_ata" label="ETA Real (ATA) <span class='text-red-500'>*</span>" :error="$errors->first('date_ata')" />
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
                    <div class="mb-8">
                        <x-date-picker wire:model="estimated_dc_availability_date" label="Fecha Disp. Bodega Estimada <span class='text-red-500'>*</span>" :error="$errors->first('estimated_dc_availability_date')" />
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
                            wire:click="saveAndMove"
                            wire:loading.attr="disabled"
                            wire:target="saveAndMove"
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

            // Limpiar campos no-date del modal cuando se cierra
            window.addEventListener('close-modal', function(event) {
                if (event.detail === 'modal-po-stage-change') {
                    const modal = document.querySelector('[name="modal-po-stage-change"]');
                    if (modal) {
                        const inputs = modal.querySelectorAll('input, select, textarea');
                        inputs.forEach(function(input) {
                            if (input.type === 'checkbox' || input.type === 'radio') {
                                input.checked = false;
                            } else if (input.tagName === 'TEXTAREA') {
                                input.value = '';
                            }
                        });
                    }
                }
            });
        });
    </script>
</div>
