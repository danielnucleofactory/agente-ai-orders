<div>
    @if($show)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('show') }" x-show="show" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
                 @click="$wire.close()"
                 x-show="show" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
                 x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <!-- Header -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Detalles de la Actividad
                        </h3>
                        <button wire:click="close" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    @if($activity && is_array($activity))
                    <!-- Activity Info -->
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Tipo:</span>
                                <span class="ml-2">
                                    @php
                                        $actionType = $activity['action_type'] ?? 'comment';
                                        $actionTypeLabel = $activity['action_type_label'] ?? '';
                                    @endphp
                                    @if($actionType === 'comment' || $actionTypeLabel === 'Comentario')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Comentario
                                        </span>
                                    @elseif($actionType === 'field_change' || $actionTypeLabel === 'Cambio de Datos')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Cambio de Datos
                                        </span>
                                    @elseif($actionType === 'status_change' || $actionTypeLabel === 'Cambio de Estado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Cambio de Estado
                                        </span>
                                    @elseif($actionType === 'record_create' || $actionTypeLabel === 'Creación')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Creación
                                        </span>
                                    @elseif($actionType === 'porth_sync' || $actionTypeLabel === 'Actualización Porth')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-800">
                                            Actualización Porth
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $actionTypeLabel ?: 'Otro' }}
                                        </span>
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Usuario:</span>
                                <span class="ml-2">{{ $activity['user_name'] ?? 'Usuario desconocido' }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Fecha:</span>
                                <span class="ml-2">
                                    @if(!empty($activity['created_at']))
                                        @php
                                            try {
                                                // created_at ya viene como Carbon desde loadActivityFromDatabase
                                                $date = $activity['created_at'] instanceof \Carbon\Carbon 
                                                    ? $activity['created_at'] 
                                                    : \Carbon\Carbon::parse($activity['created_at']);
                                                echo formatDateTime($date);
                                            } catch (\Exception $e) {
                                                echo 'N/A';
                                            }
                                        @endphp
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Descripción:</span>
                                <span class="ml-2">{{ $activity['comment'] ?? 'Sin descripción' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Changes Comparison -->
                    @php
                        // Validar que oldValues y newValues sean arrays válidos
                        $hasOldValues = !empty($this->oldValues) && is_array($this->oldValues);
                        $hasNewValues = !empty($this->newValues) && is_array($this->newValues);
                        $hasChanges = $hasOldValues || $hasNewValues;
                    @endphp
                    @if($hasChanges)
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Comparación de Cambios</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campo</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Anterior</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Nuevo</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @php
                                        $oldKeys = $hasOldValues ? array_keys($this->oldValues) : [];
                                        $newKeys = $hasNewValues ? array_keys($this->newValues) : [];
                                        $allFields = array_unique(array_merge($oldKeys, $newKeys));
                                    @endphp
                                    @if(!empty($allFields))
                                        @foreach($allFields as $field)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $this->getFieldLabel($field) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {!! $this->formatValue($this->oldValues[$field] ?? null, $field) !!}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-medium">
                                                {!! $this->formatValue($this->newValues[$field] ?? null, $field) !!}
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">
                                                No hay cambios registrados
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @elseif(($activity['action_type'] ?? 'comment') !== 'comment')
                    <div class="border-t border-gray-200 pt-4">
                        <p class="text-sm text-gray-500 italic">No se encontraron detalles de cambios para esta actividad.</p>
                    </div>
                    @endif
                    @else
                    <!-- Error state -->
                    <div class="mb-4 p-4 bg-red-50 rounded-lg border border-red-200">
                        <p class="text-sm text-red-800">
                            <strong>Error:</strong> No se pudieron cargar los datos de la actividad.
                        </p>
                    </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="close" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#1AAD8A] text-base font-medium text-white hover:bg-[#0F614D] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1AAD8A] sm:ml-3 sm:w-auto sm:text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
