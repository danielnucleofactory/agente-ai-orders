<div>
    <div class="w-full px-0 mx-0">
        @if($hasActiveFilters)
        <div class="flex items-center justify-between p-3 mb-4 rounded-md bg-blue-50">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
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

        <div class="flex w-full gap-4 pb-4 overflow-x-auto kanban-container" wire:poll.10s>
            @if(!$board)
                <div class="p-6 bg-white rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-700">No hay tableros Kanban disponibles</h3>
                    <p class="mt-2 text-gray-600">No se encontró ningún tablero Kanban para documentación de embarque. Contacta al administrador para crear uno.</p>
                </div>
            @else
                @foreach($columns as $column)
                    <div class="flex-shrink-0 p-3 mx-2 rounded-lg kanban-column w-80 {{ $loop->first ? 'first-column' : '' }}"">
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
                <!-- Campo para la columna 1 -->
                <div class="{{ $newColumnId == $columns[0]['id'] ? '' : 'hidden' }}">
                    <x-form-input class="mb-4">
                        <x-slot:label>
                            Ingrese fecha de release
                        </x-slot:label>

                        <x-slot:input name="release_date" type="date" placeholder="Ingrese fecha de release" wire:model="release_date" class="pr-10"></x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('release_date') }}
                        </x-slot:error>
                    </x-form-input>
                </div>

                <!-- Campos para la columna 2 -->
                <div class="{{ $newColumnId == $columns[1]['id'] ? '' : 'hidden' }}"
                     x-data="{ validating: false }"
                     x-init="
                        $watch('validating', value => {
                            console.log('Estado local de validación cambiado:', value);
                            $wire.setIsValidating(value);
                        });
                        // Sincronizar con el estado de Livewire inicialmente
                        validating = {{ $isValidating ? 'true' : 'false' }};

                        // Escuchar cambios en el estado de Livewire
                        window.addEventListener('validating-state-changed', (event) => {
                            validating = event.detail.isValidating;
                            console.log('Estado de Livewire cambió a:', validating);
                        });
                     ">
                    <!-- Alerta informativa para validación de códigos -->
                    <div class="p-3 mb-4 border border-blue-200 rounded-md bg-blue-50" x-show="!validating">
                        <p class="text-sm text-blue-700">
                            <i class="mr-1 fa fa-info-circle"></i>
                            Debe proporcionar al menos un código de seguimiento (ID de tracking o Master BL o Container).
                            Ambos códigos serán validados antes de mover el documento.
                        </p>
                    </div>

                    <!-- Indicador de validación en curso -->
                    <div class="p-3 mb-4 border border-yellow-200 rounded-md bg-yellow-50" x-show="validating">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-yellow-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm text-yellow-700">Validando código(s) de seguimiento...</p>
                        </div>
                    </div>

                    <x-form-input class="mb-4">
                        <x-slot:label>
                            Ingrese ID tracking
                        </x-slot:label>

                        <x-slot:input name="tracking_id" placeholder="Ingrese ID tracking" wire:model="tracking_id" class="pr-10"></x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('tracking_id') }}
                        </x-slot:error>
                    </x-form-input>

                    <x-form-input class="mb-4">
                        <x-slot:label>
                            Ingrese código booking
                        </x-slot:label>

                        <x-slot:input name="booking_code" placeholder="Ingrese código booking" wire:model="booking_code" class="pr-10"></x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('booking_code') }}
                        </x-slot:error>
                    </x-form-input>

                    <x-form-input class="mb-4">
                        <x-slot:label>
                            Ingrese Contenedor
                        </x-slot:label>

                        <x-slot:input name="container_number" placeholder="Ingrese Contenedor" wire:model="container_number" class="pr-10"></x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('container_number') }}
                        </x-slot:error>
                    </x-form-input>


                    <x-form-input>
                        <x-slot:label>
                            Ingrese MBL
                        </x-slot:label>

                        <x-slot:input name="mbl_number" placeholder="Ingrese MBL" wire:model="mbl_number" class="pr-10"></x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('mbl_number') }}
                        </x-slot:error>
                    </x-form-input>
                </div>

                <!-- Campo para la columna "Digitaciones" (ID 14) -->
                <div class="{{ $newColumnId == 14 ? '' : 'hidden' }}">
                    <x-form-input class="mb-4">
                        <x-slot:label>
                            Ingrese fecha de instrucción
                        </x-slot:label>

                        <x-slot:input name="instruction_date" type="date" placeholder="Ingrese fecha de instrucción" wire:model="instruction_date" class="pr-10"></x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('instruction_date') }}
                        </x-slot:error>
                    </x-form-input>
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
