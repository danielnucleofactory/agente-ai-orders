<div>
    <div class="px-0 mx-0 w-full">
        @if($hasActiveFilters)
        <div class="flex justify-between items-center p-3 mb-4 bg-blue-50 rounded-md">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 w-5 h-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium text-blue-700">Mostrando documentos filtrados. Los resultados que estás viendo están limitados por los filtros activos.</span>
            </div>
            <button
                wire:click="$dispatch('clearShippingDocumentationFilters')"
                class="px-3 py-1 ml-3 text-xs font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200"
            >
                Limpiar filtros
            </button>
        </div>
        @endif

        <div class="flex overflow-x-auto gap-4 pb-4 w-full kanban-container" wire:poll.10s>
            @if(!$board)
                <div class="p-6 bg-white rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-700">No hay tableros Kanban disponibles</h3>
                    <p class="mt-2 text-gray-600">No se encontró ningún tablero Kanban para documentación de embarque. Contacta al administrador para crear uno.</p>
                </div>
            @else
                @foreach($columns as $column)
                    <div class="flex-shrink-0 p-3 mx-2 rounded-lg kanban-column w-80 {{ $loop->first ? 'first-column' : '' }}">
                        <h3 class="mb-4 border-b-2 border-[#2E2E2E] px-2 text-lg font-bold text-[#2E2E2E]">
                            {{ $column['name'] }}
                            <span class="ml-2 text-sm font-normal text-gray-600">
                                ({{ count($documentsByColumn[$column['id']]) }})
                            </span>
                        </h3>

                        <div
                            id="column-{{ $column['id'] }}"
                            data-column-id="{{ $column['id'] }}"
                            class="space-y-3 min-h-40"
                            x-data="{
                                isModalOpen: false,
                                originalColumnId: null
                            }"
                            x-init="
                                new Sortable($el, {
                                    group: 'documents',
                                    animation: 150,
                                    ghostClass: 'bg-gray-100',
                                    chosenClass: 'bg-gray-200',
                                    dragClass: 'cursor-grabbing',
                                    forceFallback: true,
                                    fallbackClass: 'sortable-fallback',
                                    fallbackOnBody: true,
                                    onStart: function(evt) {
                                        originalColumnId = evt.from.getAttribute('data-column-id');
                                        console.log('Drag started from column:', originalColumnId);
                                    },
                                    onEnd: function(evt) {
                                        const documentId = evt.item.getAttribute('data-document-id');
                                        const newColumn = evt.to.getAttribute('data-column-id');

                                        console.log('Drag ended:', {
                                            documentId: documentId,
                                            newColumn: newColumn,
                                            originalColumn: originalColumnId
                                        });

                                        if (originalColumnId !== newColumn) {
                                            $wire.setCurrentDocument(documentId, newColumn)
                                                .then(() => {
                                                    $dispatch('open-modal', 'modal-document-move');
                                                });
                                        }
                                    }
                                });

                                // Event listeners
                                window.addEventListener('document-moved-successfully', () => {
                                    console.log('Document moved successfully');
                                    $wire.loadData();
                                });

                                window.addEventListener('error', (e) => {
                                    console.error('Error moving document:', e.detail);
                                    // Revertir el movimiento
                                    const cards = document.querySelectorAll('.document-card');
                                    cards.forEach(card => {
                                        if (card.getAttribute('data-document-id') === documentId) {
                                            const originalColumn = document.querySelector(`#column-${originalColumnId}`);
                                            if (originalColumn) {
                                                originalColumn.appendChild(card);
                                            }
                                        }
                                    });
                                });
                            "
                        >
                            @foreach($documentsByColumn[$column['id']] as $document)
                                <div
                                    class="cursor-move document-card"
                                    data-document-id="{{ $document['id'] }}">
                                    <x-shipping-documentation-card
                                        :documentId="$document['document_number']"
                                        :trackingId="$document['document_id']"
                                        :hubLocation="$document['hub_location']"
                                        :creationDate="$document['creation_date']"
                                        :estimatedDepartureDate="$document['estimated_departure_date']"
                                        :estimatedArrivalDate="$document['estimated_arrival_date']"
                                        :totalWeight="number_format($document['weight_kg'], 0) . ' kg'"
                                        :poCount="$document['po_count']"
                                    />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <x-modal name="modal-document-move" maxWidth="lg">
        <h3 class="mb-2 text-lg font-bold text-center text-light-blue">
            ¿Cambiar el documento de etapa?
        </h3>

        @if ($currentDocument)
            <div class="mb-5 text-center">
                <p class="text-[#171717] underline underline-offset-4">Documento: {{ $currentDocument['document_number'] }}</p>
            </div>
        @endif

        <div class="mb-4">
            <x-form-select
                label=""
                name="etapa_documento"
                :options="collect($columns)->pluck('name', 'id')->toArray()"
                optionPlaceholder="Seleccionar etapa"
                :value="$newColumnId"
                wire:model.live="newColumnId" />

            <div class="mt-4">

                <!-- Nueva -->
                <div class="{{ (isset($columns[0]) && $newColumnId == $columns[0]['id']) ? '' : 'hidden' }}">
                    <x-form-input class="mb-4">
                        <x-slot:label>
                            Ingrese fecha de release
                        </x-slot:label>

                        <x-slot:input name="release_date" type="date" placeholder="Ingrese fecha de release" wire:model="release_date" class="pr-10 {{ $errors->has('release_date') ? 'border-red-500' : '' }}"></x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('release_date') }}
                        </x-slot:error>
                    </x-form-input>
                </div>

                {{-- Consolidador --}}
                <div class="{{ (isset($columns[1]) && $newColumnId == $columns[1]['id']) ? '' : 'hidden' }}">
                    {{-- sin campos definidos en hoja Etapas --}}
                </div>

                {{-- Producción (ID: 2) --}}
                <div class="{{ (isset($columns[2]) && $newColumnId == $columns[2]['id']) ? '' : 'hidden' }}">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        {{-- 1) Carga Lista Variable --}}
                        <x-form-input class="mb-4">
                            <x-slot:label>Carga Lista Variable</x-slot:label>
                            <x-slot:input type="date" wire:model="date_variable_date" class="pr-10 {{ $errors->has('date_variable_date') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('date_variable_date') }}</x-slot:error>
                        </x-form-input>

                        {{-- 2) Carga Lista Teórica --}}
                        <x-form-input class="mb-4">
                            <x-slot:label>Carga Lista Teórica</x-slot:label>
                            <x-slot:input type="date" wire:model="date_theorical_load" class="pr-10 {{ $errors->has('date_theorical_load') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('date_theorical_load') }}</x-slot:error>
                        </x-form-input>

                        {{-- 3) Proveedor de Servicio --}}
                        <x-form-input class="mb-4">
                            <x-slot:label>Proveedor de Servicio</x-slot:label>
                            <x-slot:input type="text" wire:model="service_provider" placeholder="Ingrese proveedor de servicio" class="pr-10 {{ $errors->has('service_provider') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('service_provider') }}</x-slot:error>
                        </x-form-input>

                        {{-- 4) Agente de Carga --}}
                        <x-form-input class="mb-4">
                            <x-slot:label>Agente de Carga</x-slot:label>
                            <x-slot:input type="text" wire:model="forwarder_name" placeholder="Ingrese agente de carga" class="pr-10 {{ $errors->has('forwarder_name') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('forwarder_name') }}</x-slot:error>
                        </x-form-input>
                    </div>
                </div>

                {{-- Booking (ID: 3) --}}
                <div class="{{ (isset($columns[3]) && $newColumnId == $columns[3]['id']) ? '' : 'hidden' }}">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-form-input class="mb-4">
                            <x-slot:label>Solicitud de Booking</x-slot:label>
                            <x-slot:input type="date" wire:model="date_booking_request" class="pr-10 {{ $errors->has('date_booking_request') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('date_booking_request') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>Autorización de Booking</x-slot:label>
                            <x-slot:input type="date" wire:model="date_booking_authorized" class="pr-10 {{ $errors->has('date_booking_authorized') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('date_booking_authorized') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>ETD Inicial</x-slot:label>
                            {{-- reutiliza el existente si lo usas en tu flujo --}}
                            <x-slot:input type="date" wire:model="estimated_departure_date" class="pr-10 {{ $errors->has('estimated_departure_date') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('estimated_departure_date') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>ETD Variable</x-slot:label>
                            <x-slot:input type="date" wire:model="date_etd_updated" class="pr-10 {{ $errors->has('date_etd_updated') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('date_etd_updated') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>Tipo de Contenedor</x-slot:label>
                            <x-slot:input type="text" wire:model="container_type" placeholder="Ingrese tipo de contenedor" class="pr-10 {{ $errors->has('container_type') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('container_type') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>Modo de transporte</x-slot:label>
                            <x-slot:input type="text" wire:model="mode" placeholder="Ingrese modo de transporte" class="pr-10 {{ $errors->has('mode') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('mode') }}</x-slot:error>
                        </x-form-input>
                    </div>
                </div>

                {{-- Tránsito (ID: 4) --}}
                <div class="{{ (isset($columns[4]) && $newColumnId == $columns[4]['id']) ? '' : 'hidden' }}">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        {{-- Fechas --}}
                        <x-form-input class="mb-4">
                            <x-slot:label>ETD Real (ATD)</x-slot:label>
                            <x-slot:input type="date" wire:model="actual_departure_date" class="pr-10 {{ $errors->has('actual_departure_date') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('actual_departure_date') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>ETA Inicial</x-slot:label>
                            <x-slot:input type="date" wire:model="estimated_arrival_date" class="pr-10 {{ $errors->has('estimated_arrival_date') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('estimated_arrival_date') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>ETA Variable</x-slot:label>
                            <x-slot:input type="date" wire:model="date_eta_updated" class="pr-10 {{ $errors->has('date_eta_updated') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('date_eta_updated') }}</x-slot:error>
                        </x-form-input>

                        {{-- Equipo / BL / Naviera + montos --}}
                        <x-form-input class="mb-4">
                            <x-slot:label>Número de Contenedor</x-slot:label>
                            <x-slot:input type="text" wire:model="container_number" placeholder="Ingrese número de contenedor" class="pr-10 {{ $errors->has('container_number') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('container_number') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>MBL</x-slot:label>
                            <x-slot:input type="text" wire:model="bill_of_lading" placeholder="Ingrese MBL" class="pr-10 {{ $errors->has('bill_of_lading') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('bill_of_lading') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>Monto</x-slot:label>
                            <x-slot:input type="number" step="0.01" inputmode="decimal" placeholder="Ingrese monto" wire:model="Invoice_amount" class="pr-10 {{ $errors->has('Invoice_amount') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('Invoice_amount') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>Línea Naviera</x-slot:label>
                            <x-slot:input type="text" wire:model="shipping_line" placeholder="Ingrese línea naviera" class="pr-10 {{ $errors->has('shipping_line') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('shipping_line') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>Estado</x-slot:label>
                            <x-slot:input type="text" wire:model="arrival_status" placeholder="Ingrese estado" class="pr-10 {{ $errors->has('arrival_status') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('arrival_status') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>Factura de Mercancía</x-slot:label>
                            <x-slot:input type="text" wire:model="factura_merca" placeholder="Ingrese factura de mercancía" class="pr-10 {{ $errors->has('factura_merca') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('factura_merca') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>Número de Booking</x-slot:label>
                            <x-slot:input type="text" wire:model="tracking_id" placeholder="Ingrese número de booking" class="pr-10 {{ $errors->has('tracking_id') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('tracking_id') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>Puerto de Embarque</x-slot:label>
                            <x-slot:input type="text" wire:model="departure_port" placeholder="Ingrese puerto de embarque" class="pr-10 {{ $errors->has('departure_port') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('departure_port') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>Puerto de Arribo</x-slot:label>
                            <x-slot:input type="text" wire:model="arrival_port" placeholder="Ingrese puerto de arribo" class="pr-10 {{ $errors->has('arrival_port') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('arrival_port') }}</x-slot:error>
                        </x-form-input>
                    </div>
                </div>

                {{-- Puerto (ID: 5) --}}
                <div class="{{ (isset($columns[5]) && $newColumnId == $columns[5]['id']) ? '' : 'hidden' }}">
                    <x-form-input class="mb-4">
                        <x-slot:label>ETA Real (ATA)</x-slot:label>
                        <x-slot:input type="date" wire:model="actual_arrival_date" class="pr-10 {{ $errors->has('actual_arrival_date') ? 'border-red-500'  : '' }}"></x-slot:input>
                        <x-slot:error>{{ $errors->first('actual_arrival_date') }}</x-slot:error>
                    </x-form-input>
                </div>

                {{-- Almacén Fiscal (ID: 6) --}}
                <div class="{{ (isset($columns[6]) && $newColumnId == $columns[6]['id']) ? '' : 'hidden' }}">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-form-input class="mb-4">
                            <x-slot:label>Ingreso Almacén Fiscal</x-slot:label>
                            <x-slot:input type="date" wire:model="bonded_warehouse_enter" class="pr-10 {{ $errors->has('bonded_warehouse_enter') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('bonded_warehouse_enter') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input class="mb-4">
                            <x-slot:label>Salida Almacén Fiscal</x-slot:label>
                            <x-slot:input type="date" wire:model="bonded_warehouse_exit" class="pr-10 {{ $errors->has('bonded_warehouse_exit') ? 'border-red-500'  : '' }}"></x-slot:input>
                            <x-slot:error>{{ $errors->first('bonded_warehouse_exit') }}</x-slot:error>
                        </x-form-input>
                    </div>
                </div>

                {{-- Ingresada (ID: 7) --}}
                <div class="{{ (isset($columns[7]) && $newColumnId == $columns[7]['id']) ? '' : 'hidden' }}">
                    <x-form-input class="mb-4">
                        <x-slot:label>Nota de Recibo</x-slot:label>
                        <x-slot:input type="text" wire:model="receipt_note" placeholder="Ingrese nota de recibo" class="pr-10 {{ $errors->has('receipt_note') ? 'border-red-500'  : '' }}"></x-slot:input>
                        <x-slot:error>{{ $errors->first('receipt_note') }}</x-slot:error>
                    </x-form-input>
                </div>

                {{-- En otra ZF (ID: 8) --}}
                <div class="{{ (isset($columns[8]) && $newColumnId == $columns[8]['id']) ? '' : 'hidden' }}">
                    {{-- sin campos definidos en hoja Etapas --}}
                </div>

                {{-- Recibiendo CDI (ID: 9) --}}
                <div class="{{ (isset($columns[9]) && $newColumnId == $columns[9]['id']) ? '' : 'hidden' }}">
                    {{-- sin campos definidos en hoja Etapas --}}
                </div>

                {{-- Anulada (ID: 10) --}}
                <div class="{{ (isset($columns[10]) && $newColumnId == $columns[10]['id']) ? '' : 'hidden' }}">
                    {{-- sin campos definidos en hoja Etapas --}}
                </div>


            </div>
        </div>

        <div class="mb-4">
            <x-form-textarea label="" name="comentario_documento" wireModel="comment" placeholder="Comentarios" />
        </div>

        <div class="mb-12 space-y-2">
            <input
                type="file"
                wire:model.live="attachment"
                class="hidden"
                x-ref="fileInput"
                id="file-upload-po"
                x-bind:disabled="!$wire.comment || $wire.comment.trim() === ''"
            >
            <x-secondary-button
                onclick="document.getElementById('file-upload-po').click()"
                class="group flex w-full items-center justify-center gap-[0.625rem]"
                x-bind:disabled="!$wire.comment || $wire.comment.trim() === ''"
            >
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
                    Archivo seleccionado: {{ is_object($attachment) ? $attachment->getClientOriginalName() : $attachment['name'] ?? 'Archivo' }}
                </div>
            @endif

            <div class="flex flex-col text-sm text-[#A5A3A3]">
                <span>Tipo de formato .xls .xlsx .pdf</span>
                <span>Tamaño máximo 5MB</span>
            </div>

            @error('attachment')
                <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex gap-[1.875rem]">
            <x-secondary-button
                x-on:click="$dispatch('close-modal', 'modal-document-move')"
                class="w-full">
                Cancelar
            </x-secondary-button>

            <x-primary-button
                wire:click="saveAndMoveDocument"
                wire:loading.attr="disabled"
                class="w-full"
                x-on:click="validating = true"
                wire:target="saveAndMoveDocument">
                <span wire:loading.remove wire:target="saveAndMoveDocument">Continuar</span>
                <span wire:loading wire:target="saveAndMoveDocument">Procesando...</span>
            </x-primary-button>
        </div>
    </x-modal>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Función auxiliar para resetear el estado de validación en todos los componentes Alpine
            function resetValidatingState() {
                document.querySelectorAll('[x-data]').forEach(function(el) {
                    if (typeof el.__x !== 'undefined' &&
                        el.__x.$data &&
                        'validating' in el.__x.$data) {
                        el.__x.$data.validating = false;
                    }
                });
            }

            // Función para actualizar de forma segura la propiedad Livewire
            function safeUpdateLivewireProperty(property, value) {
                try {
                    const wireElement = document.querySelector('[wire\\:id]');
                    if (wireElement) {
                        const componentId = wireElement.getAttribute('wire:id');
                        const component = Livewire.find(componentId);
                        if (component) {
                            // Verificar que el componente tenga la propiedad antes de intentar actualizarla
                            // Esto evita el error cuando se intenta actualizar propiedades en componentes que no las tienen
                            if (property in component.serverMemo.data) {
                                component.set(property, value);
                            } else {
                                console.warn(`El componente ${component.fingerprint.name} no tiene la propiedad ${property}`);
                            }
                        }
                    }
                } catch (e) {
                    console.error('Error al actualizar propiedad Livewire:', e);
                }
            }

            // Escuchar el evento validating-state-changed que viene del backend
            window.addEventListener('validating-state-changed', function(event) {
                console.log('Evento recibido validating-state-changed:', event.detail);
                resetValidatingState();
            });

            Livewire.on('notify', function(data) {
                // Si el mensaje es de error, restablecer el estado de validación
                if (data.type === 'error') {
                    resetValidatingState();
                    // También actualizamos la propiedad Livewire
                    safeUpdateLivewireProperty('isValidating', false);
                }

                // Mostrar la notificación (asumiendo que tienes alguna biblioteca de notificaciones)
                if (typeof Toast !== 'undefined') {
                    Toast.fire({
                        icon: data.type,
                        title: data.message
                    });
                } else {
                    // Fallback si no está disponible Toast
                    console.log(data.type + ': ' + data.message);
                    console.log(data.message);
                }
            });

            // Cuando el documento se mueve exitosamente, resetear el estado de validación
            Livewire.on('document-moved-successfully', function() {
                resetValidatingState();
                // También actualizamos la propiedad Livewire
                safeUpdateLivewireProperty('isValidating', false);
            });

            // Si ocurre un error, resetear el estado de validación
            Livewire.on('error', function(data) {
                resetValidatingState();
                // También actualizamos la propiedad Livewire
                safeUpdateLivewireProperty('isValidating', false);

                // Mostrar el mensaje de error
                if (typeof Toast !== 'undefined') {
                    Toast.fire({
                        icon: 'error',
                        title: data.message
                    });
                } else {
                    console.error(data.message);
                    console.log(data.message);
                }
            });
        });
    </script>

    <style>
        .kanban-container {
            display: flex;
            overflow-x: auto;
            padding-bottom: 1rem;
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
            scrollbar-width: thin;
        }

        .sortable-fallback {
            opacity: 0.8;
            transform: rotate(2deg);
            min-height: 180px !important;
            width: 320px !important;
        }

        .first-column {
            padding-left: 0;
        }

        .document-card {
            transition: transform 0.2s ease;
            width: 100%;
        }

        .document-card:hover {
            transform: translateY(-2px);
        }
    </style>
</div>
