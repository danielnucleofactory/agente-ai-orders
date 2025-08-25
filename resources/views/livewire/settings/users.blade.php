<div class="p-8 space-y-6 bg-white rounded-2xl">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-[#7288FF]">Lista de usuarios</h2>

        <div class="flex space-x-4">
            <a href="{{ route('settings.users.create') }}">
                <x-primary-button class="flex items-center gap-[0.625rem]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M8 1V15M1 8H15" stroke="#F7F7F7" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    <span>Crear usuario</span>
                </x-primary-button>
            </a>
        </div>
    </div>

    <div>
        <div class="flex items-center">
            <div class="relative w-fit">
                <input
                    wire:model.live="search"
                    class="rounded-xl border-2 border-[#A5A3A3] pl-11 pr-[1.125rem] py-[0.625rem] placeholder:text-[#9AABFF]"
                    placeholder="Buscar"
                />

                <div class="pointer-events-none absolute top-1/2 -translate-y-1/2 left-[1.125rem] flex items-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-[#E0E5FF]">
            <tr>
                @foreach($headers as $key => $label)
                    <th class="px-6 py-6 text-xs tracking-wider text-left text-black uppercase font-bolf">
                        {{ $label }}
                    </th>
                @endforeach
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
            @if($users->count())
                @foreach($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500">Operator</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                            {{ $user->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-sm text-white bg-green-500 rounded-[5px]">
                                Activa
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                                {{ $user->roles->first()?->name ?? 'Sin rol' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex space-x-2">
                                <a href="{{ route('settings.users.edit', $user) }}" class="text-gray-600 hover:text-gray-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </a>

                                <button class="text-gray-600 hover:text-gray-900" wire:click="openModal({{ $user->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="{{ count($headers) }}" class="px-6 py-4 text-center text-gray-500">
                        No se encontraron usuarios que coincidan con la búsqueda.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <x-modal-warning name="modal-delete-user">
        <x-slot:title>
            Estás seguro de querer eliminar el usuario {{ $user->name }}?
        </x-slot:title>

        <div class="flex space-x-2">
            <x-primary-button wire:click="closeModal" class="w-full">
                Cancelar
            </x-primary-button>

            <x-primary-button wire:click="deleteUser({{ $id }})" class="w-full bg-red-600 hover:bg-red-700">
                Eliminar
            </x-primary-button>
        </div>
    </x-modal-warning>
</div>
