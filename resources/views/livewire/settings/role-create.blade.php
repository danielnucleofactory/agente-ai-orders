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

        <!-- Acciones globales -->
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-[#F7F7F7] p-4">
            <div class="text-sm text-[#231F20]">
                <span class="font-semibold">Permisos</span>
                <span class="text-gray-500">({{ count(array_keys(array_filter($selectedPermissions ?? []))) }} seleccionados)</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="selectAllPermissions"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-[#231F20] hover:bg-gray-50">
                    Seleccionar todos
                </button>
                <button type="button" wire:click="clearAllPermissions"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-[#231F20] hover:bg-gray-50">
                    Limpiar
                </button>
                <button type="button" wire:click="expandAllPermissionGroups"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-[#231F20] hover:bg-gray-50">
                    Expandir grupos
                </button>
                <button type="button" wire:click="collapseAllPermissionGroups"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-[#231F20] hover:bg-gray-50">
                    Colapsar grupos
                </button>
            </div>
        </div>

        <!-- Permisos organizados por grupos (colapsables) -->
        <div class="space-y-4">
            @foreach($permissionGroups as $groupName => $permissions)
                <details
                    data-perm-panel
                    wire:key="perm-grp-{{ md5($groupName) }}"
                    @if($expandedGroups[$groupName] ?? false) open @endif
                    class="group overflow-hidden rounded-2xl border border-gray-200 bg-white">
                    <summary wire:click.prevent="toggleGroupExpanded(@js($groupName))" class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-[#F7F7F7] px-6 py-4 [&::-webkit-details-marker]:hidden">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center text-[#127A62] transition-transform group-open:rotate-90" aria-hidden="true">▸</span>
                            <h2 class="text-base font-bold text-[#231F20]">{{ $groupName }}</h2>
                            <span class="rounded-full bg-[#D4F5ED] px-2.5 py-0.5 text-xs font-semibold text-[#0F614D]">
                                {{ $this->selectedCountForGroup($groupName) }}/{{ $this->totalCountForGroup($groupName) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2" onclick="event.preventDefault()">
                            <button type="button" wire:click.stop='selectPermissionsByGroup({{ json_encode($groupName) }})'
                                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-[#231F20] hover:bg-gray-50">
                                Seleccionar módulo
                            </button>
                            <button type="button" wire:click.stop='clearPermissionsByGroup({{ json_encode($groupName) }})'
                                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-[#231F20] hover:bg-gray-50">
                                Limpiar módulo
                            </button>
                        </div>
                    </summary>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[#D4F5ED]">
                            <thead class="bg-[#D4F5ED]">
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-[#121619]">Permiso</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-[#121619]">Clave</th>
                                    <th class="px-6 py-3 text-right text-sm font-bold text-[#121619]">Activo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#D4F5ED] bg-white">
                                @foreach($permissions as $permissionKey => $permissionLabel)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium text-[#231F20]">
                                            {{ $permissionLabel }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <code class="rounded bg-gray-100 px-2 py-1">{{ $permissionKey }}</code>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button type="button" wire:click="togglePermission('{{ $permissionKey }}')" class="toggle-button">
                                                <div class="w-12 h-6 rounded-full transition-all {{ ($selectedPermissions[$permissionKey] ?? false) ? 'bg-[#1AAD8A]' : 'bg-gray-300' }} relative">
                                                    <div class="w-4 h-4 bg-white rounded-full absolute top-1 transition-all {{ ($selectedPermissions[$permissionKey] ?? false) ? 'right-1' : 'left-1' }}"></div>
                                                </div>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endforeach
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
