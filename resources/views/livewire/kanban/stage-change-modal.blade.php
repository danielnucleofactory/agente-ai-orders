<div
    x-data
    x-on:kanban-stage-modal:open-request.window="
        const detail = $event.detail || {};
        const taskId = parseInt(detail.taskId ?? 0, 10);
        const newColumn = parseInt(detail.newColumn ?? 0, 10);

        if (!taskId || !newColumn) {
            return;
        }

        window.poKanbanOverlayShow && window.poKanbanOverlayShow('Cargando datos del formulario…');

        $wire.openForTask(taskId, newColumn)
            .then(() => {
                window.poKanbanOverlayHide && window.poKanbanOverlayHide();
                $dispatch('open-modal', 'modal-po-stage-change');

                if ([2, 3, 5].includes(newColumn)) {
                    queueMicrotask(() => $wire.loadMaestrosAsync(taskId, newColumn));
                }
            })
            .catch((error) => {
                window.poKanbanOverlayHide && window.poKanbanOverlayHide();
                console.error('Error al cargar datos de la tarea:', error);
                window.dispatchEvent(new CustomEvent('refreshKanban'));
            });
    "
>
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
                                   :value="$newColumnId" wire:model.live="newColumnId" />
                </div>

                <div wire:loading.flex wire:target="loadMaestrosAsync"
                     class="items-center justify-end gap-2 mt-2 text-xs font-medium text-gray-500">
                    <svg class="w-4 h-4 text-[#1AAD8A] animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Cargando catálogos…</span>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto pr-2 -mr-2 mb-3 pt-2">
                <div class="mb-4">
                    <div class="{{ $newColumnId == 1 ? '' : 'hidden' }}">
                        <div class="mb-8">
                            <x-form-textarea label="" name="comment_stage_01" wireModel="comment" placeholder="Comentarios" />
                        </div>
                    </div>

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
                        </div>
                        <div class="mb-8 hidden">
                            <x-form-input>
                                <x-slot:label>Agente de Carga <span class="text-red-500">*</span></x-slot:label>
                                <x-slot:input type="text" name="forwarder_name" placeholder="Ingrese agente de carga" wire:model="forwarder_name" class="pr-10 {{ $errors->has('forwarder_name') ? 'border-red-500'  : '' }}">
                                </x-slot:input>
                            </x-form-input>
                        </div>
                        <div class="mb-8">
                            <x-form-textarea label="" name="comment_stage_02" wireModel="comment" placeholder="Comentarios" />
                        </div>
                    </div>

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

                    <div class="{{ $newColumnId == 4 ? '' : 'hidden' }}">
                        <div class="mb-8">
                            <x-form-textarea label="" name="comment_stage_04" wireModel="comment" placeholder="Comentarios" />
                        </div>
                    </div>

                    <div class="{{ $newColumnId == 5 ? '' : 'hidden' }}">
                        <div class="mb-8 pt-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-5 gap-y-6">
                                <div>
                                    <x-date-picker wire:model="date_atd" label="ETD Real (ATD) <span class='text-red-500'>*</span>" :readonly="$trackingDatesLocked" :error="$errors->first('date_atd')" />
                                </div>
                                <div>
                                    <x-date-picker wire:model="date_eta_initial" label="ETA Inicial <span class='text-red-500'>*</span>" :readonly="$trackingDatesLocked" :error="$errors->first('date_eta_initial')" />
                                </div>
                                <div>
                                    <x-date-picker wire:model="date_eta" label="ETA Variable <span class='text-red-500'>*</span>" :readonly="$trackingDatesLocked" :error="$errors->first('date_eta')" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-6">
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
                                    </x-form-input>
                                </div>

                                <div>
                                    <x-form-input>
                                        <x-slot:label>Factura de Mercancía</x-slot:label>
                                        <x-slot:input name="factura_merca" wire:model="factura_merca" placeholder="Ingrese factura de mercancía"
                                                      class="pr-10 {{ $errors->has('factura_merca') ? 'border-red-500' : '' }}"></x-slot:input>
                                    </x-form-input>
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-6">
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

                    <div class="{{ $newColumnId == 6 ? '' : 'hidden' }}">
                        <div class="mb-8 pt-2">
                            <x-date-picker wire:model="date_ata" label="ETA Real (ATA) <span class='text-red-500'>*</span>" :readonly="$trackingDatesLocked" :error="$errors->first('date_ata')" />
                        </div>
                        <div class="mb-8">
                            <x-form-textarea label="" name="comment_stage_06" wireModel="comment" placeholder="Comentarios" />
                        </div>
                    </div>

                    <div class="{{ $newColumnId == 7 ? '' : 'hidden' }}">
                        <div class="mb-8 pt-2">
                            <x-date-picker wire:model="bonded_warehouse_enter" label="Ingreso Almacén Fiscal <span class='text-red-500'>*</span>" :error="$errors->first('bonded_warehouse_enter')" />
                        </div>
                        <div class="mb-8">
                            <x-date-picker wire:model="bonded_warehouse_exit" label="Salida Almacén Fiscal" :error="$errors->first('bonded_warehouse_exit')" />
                        </div>
                        <div class="mb-8">
                            <x-date-picker wire:model="date_ata" label="ETA Real (ATA) <span class='text-red-500'>*</span>" :readonly="$trackingDatesLocked" :error="$errors->first('date_ata')" />
                        </div>
                        <div class="mb-8">
                            <x-form-textarea label="" name="comment_stage_07" wireModel="comment" placeholder="Comentarios" />
                        </div>
                    </div>

                    <div class="{{ $newColumnId == 8 ? '' : 'hidden' }}">
                        <div class="mb-8">
                            <x-form-textarea label="" name="comment_stage_08" wireModel="comment" placeholder="Comentarios" />
                        </div>
                    </div>

                    <div class="{{ $newColumnId == 9 ? '' : 'hidden' }}">
                        <div class="mb-8">
                            <x-date-picker wire:model="estimated_dc_availability_date" label="Fecha Disp. Bodega Estimada <span class='text-red-500'>*</span>" :error="$errors->first('estimated_dc_availability_date')" />
                        </div>
                        <div class="mb-8">
                            <x-form-textarea label="" name="comment_stage_09" wireModel="comment" placeholder="Comentarios" />
                        </div>
                    </div>

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

                    <div class="{{ $newColumnId == 11 ? '' : 'hidden' }}">
                        <div class="mb-8">
                            <x-form-textarea label="" name="comment_stage_11" wireModel="comment" placeholder="Comentarios" />
                        </div>
                    </div>
                </div>
            </div>

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
                    <x-secondary-button
                        x-on:click="$dispatch('close-modal', 'modal-po-stage-change')"
                        wire:click="cancelModal"
                        class="w-full">Cancelar</x-secondary-button>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('open-modal', function(event) {
                if (event.detail === 'modal-po-stage-change') {
                    window.poKanbanOverlayHide && window.poKanbanOverlayHide();
                    setTimeout(function() {
                        const scrollableContent = document.querySelector('[name="modal-po-stage-change"]')?.closest('div[x-data]')?.querySelector('.overflow-y-auto');
                        if (scrollableContent) {
                            scrollableContent.scrollTop = 0;
                        }
                    }, 100);
                }
            });

            window.addEventListener('kanban:load-maestros', function(event) {
                const detail = event.detail || {};
                const taskId = parseInt(detail.taskId ?? detail.taskid ?? 0, 10);
                const stage = parseInt(detail.stage ?? 0, 10);

                if (!taskId || !stage) {
                    return;
                }

                queueMicrotask(function() {
                    $wire.loadMaestrosAsync(taskId, stage);
                });
            });

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
