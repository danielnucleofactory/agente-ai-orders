<div>
<div class="space-y-8">
    <form id="roleForm" wire:submit.prevent="createRole" class="p-8 space-y-10 bg-white rounded-2xl">
        @if (session()->has('message'))
            <div class="p-4 text-green-700 bg-green-100 rounded-lg">
                {{ session('message') }}
            </div>
        @endif

        <x-form-input class="w-1/4">
            <x-slot:label>
                Nombre de rol
            </x-slot:label>
            <x-slot:input name="name" placeholder="Nombre de rol" wire:model="name">
            </x-slot:input>
            @error('name')
                <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
            @enderror
        </x-form-input>

        @error('selectedPermissions')
            <div class="p-4 text-red-700 bg-red-100 rounded-lg">
                {{ $message }}
            </div>
        @enderror

        <!-- Permisos organizados por grupos -->
        <div class="space-y-8">
            @foreach($permissionGroups as $groupName => $permissions)
                <div class="space-y-4 text-lg text-[#231F20]">
                    <h2 class="pb-2 text-xl font-semibold border-b border-gray-200">{{ $groupName }}</h2>
                    <ul class="space-y-4 text-sm text-[#2B3674]">
                        @foreach($permissions as $permissionKey => $permissionLabel)
                            <li class="flex items-start gap-4">
                                <button type="button" wire:click="togglePermission('{{ $permissionKey }}')" class="toggle-button">
                                    <div class="w-12 h-6 rounded-full transition-all {{ $selectedPermissions[$permissionKey] ?? false ? 'bg-[#7288FF]' : 'bg-gray-300' }} relative">
                                        <div class="w-4 h-4 bg-white rounded-full absolute top-1 transition-all {{ $selectedPermissions[$permissionKey] ?? false ? 'right-1' : 'left-1' }}"></div>
                                    </div>
                                </button>
                                <label class="ml-2 cursor-pointer" wire:click="togglePermission('{{ $permissionKey }}')">{{ $permissionLabel }}</label>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
            <x-secondary-button type="button" wire:click="backToList">
                Cancelar
            </x-secondary-button>
            <x-primary-button type="submit">
                Crear Rol
            </x-primary-button>
        </div>
    </form>
</div>

<x-modal-success name="modal-role-created">
    <x-slot:title>
        Rol creado correctamente
    </x-slot:title>

    <x-slot:description>
        El rol ha sido creado correctamente con el nombre: {{ $name }}
    </x-slot:description>

    <x-primary-button wire:click="closeModal" class="w-full">
        Cerrar
    </x-primary-button>
</x-modal-success>

<style>
.toggle-button {
    display: inline-block;
    height: 24px;
    outline: none;
    border: none;
    background: transparent;
    cursor: pointer;
    padding: 0;
}
</style>
</div>
