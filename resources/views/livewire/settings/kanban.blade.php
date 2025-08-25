<div>
    <!-- Tableros Kanban -->
    <div>
        <!-- Tabla de tableros kanban -->
        <div class="flex items-center justify-between mb-6">
            <x-search-input class="w-64" wire:model.debounce.300ms="search" placeholder="Buscar comentarios o archivos..." />
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-[#E0E5FF]">
                    <tr>
                        <th scope="col" class="px-6 py-6 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer" wire:click="sortBy('name')">
                            Nombre
                            @if($sortField === 'name')
                                @if($sortDirection === 'asc')
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @else
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                @endif
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-6 text-xs font-bold tracking-wider text-left text-black uppercase">
                            Descripción
                        </th>
                        <th scope="col" class="px-6 py-6 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer" wire:click="sortBy('type')">
                            Tipo
                            @if($sortField === 'type')
                                @if($sortDirection === 'asc')
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @else
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                @endif
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-6 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer" wire:click="sortBy('stages_count')">
                            Número de etapas
                            @if($sortField === 'stages_count')
                                @if($sortDirection === 'asc')
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @else
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                @endif
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-6 text-xs font-bold tracking-wider text-left text-black uppercase">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($boards as $board)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                {{ $board['name'] }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $board['description'] }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                {{ $board['type'] }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                {{ $board['stages_count'] }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                <button wire:click="viewStages({{ $board['id'] }})" class="text-blue-600 hover:text-blue-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <span class="sr-only">Ver etapas</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No se encontraron tableros Kanban
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para ver etapas -->
    @if($viewingStages)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" wire:click="closeViewingStages"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block w-full max-w-5xl py-6 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-7xl">
                <div class="absolute top-0 right-0 hidden pt-4 pr-4 sm:block">
                    <button type="button" wire:click="closeViewingStages" class="text-gray-400 bg-white rounded-md hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="sr-only">Close</span>
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="mb-4 text-xl font-semibold text-gray-900">
                        @if ($selectedBoard)
                            Etapas del tablero: {{ $selectedBoard->name }}
                        @endif
                    </h3>

                    @if ($selectedBoard)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Posición</th>
                                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nombre</th>
                                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($selectedBoard->statuses as $stage)
                                        <tr>
                                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $stage->position }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $stage->name }}</td>
                                            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                                <button wire:click="startEditStage({{ $stage->id }})" class="text-blue-600 hover:text-blue-900">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap">No hay etapas configuradas para este tablero</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-4 text-center text-gray-500">Selecciona un tablero para ver sus etapas</div>
                    @endif
                </div>

                <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="closeViewingStages" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal para editar nombre de etapa -->
    @if($editingStageModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" wire:click="closeEditingStage"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block w-full max-w-lg py-6 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle">
                <div class="absolute top-0 right-0 hidden pt-4 pr-4 sm:block">
                    <button type="button" wire:click="closeEditingStage" class="text-gray-400 bg-white rounded-md hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="sr-only">Close</span>
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="mb-4 text-xl font-semibold text-gray-900">Editar Nombre de Etapa</h3>

                    <div class="mb-4">
                        <label for="stageName" class="block text-sm font-medium text-gray-700">Nombre de la etapa</label>
                        <input type="text" id="stageName" wire:model="stageName" class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('stageName') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="updateStageName" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Guardar
                    </button>
                    <button type="button" wire:click="closeEditingStage" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
