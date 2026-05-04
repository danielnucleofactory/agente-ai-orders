<div>
    @include('livewire.components.reusable-table')

    @if ($viewingStages)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeViewingStages"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div class="inline-block w-full max-w-5xl transform overflow-hidden rounded-lg bg-white py-6 pb-4 text-left align-bottom shadow-xl transition-all sm:my-8 sm:max-w-7xl sm:align-middle">
                    <div class="absolute top-0 right-0 hidden pt-4 pr-4 sm:block">
                        <button type="button" wire:click="closeViewingStages" class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#1AAD8A] focus:ring-offset-2">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
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
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Posición</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nombre</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @forelse ($selectedBoard->statuses as $stage)
                                            <tr>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $stage->position }}</td>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $stage->name }}</td>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                                    <button type="button" wire:click="startEditStage({{ $stage->id }})" class="text-[#1AAD8A] hover:text-[#0F614D]">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-500">No hay etapas configuradas para este tablero</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="py-4 text-center text-gray-500">Selecciona un tablero para ver sus etapas</div>
                        @endif
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" wire:click="closeViewingStages" class="inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1AAD8A] focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($editingStageModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeEditingStage"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div class="inline-block w-full max-w-lg transform overflow-hidden rounded-lg bg-white py-6 pb-4 text-left align-bottom shadow-xl transition-all sm:my-8 sm:align-middle">
                    <div class="absolute top-0 right-0 hidden pt-4 pr-4 sm:block">
                        <button type="button" wire:click="closeEditingStage" class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#1AAD8A] focus:ring-offset-2">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="mb-4 text-xl font-semibold text-gray-900">Editar Nombre de Etapa</h3>

                        <div class="mb-4">
                            <label for="stageName" class="block text-sm font-medium text-gray-700">Nombre de la etapa</label>
                            <input type="text" id="stageName" wire:model="stageName" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-[#1AAD8A] focus:outline-none focus:ring-[#1AAD8A] sm:text-sm">
                            @error('stageName')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" wire:click="updateStageName" class="inline-flex w-full justify-center rounded-md border border-transparent bg-[#1AAD8A] px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-[#127A62] focus:outline-none focus:ring-2 focus:ring-[#1AAD8A] focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar
                        </button>
                        <button type="button" wire:click="closeEditingStage" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1AAD8A] focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
