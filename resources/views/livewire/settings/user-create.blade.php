<div>
    <div class="p-8 space-y-10 bg-white rounded-2xl">
        <div class="flex gap-4">
            <div class="w-full space-y-6">
                <h3 class="text-lg font-bold text-neutral-blue">{{ $title }}</h3>
                <p class="text-sm text-gray-600">{{ $subtitle }}</p>

                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-2 gap-4">
                        <x-form-input>
                            <x-slot:label>
                                Nombre
                            </x-slot:label>
                            <x-slot:input
                                name="name"
                                placeholder="Ingrese nombre del usuario"
                                wire:model="name"
                                class="pr-10 {{ $errors->has('name') ? 'border-red-500' : '' }}">
                            </x-slot:input>
                            <x-slot:error>
                                {{ $errors->first('name') }}
                            </x-slot:error>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>
                                Correo Electrónico
                            </x-slot:label>
                            <x-slot:input
                                type="email"
                                name="email"
                                placeholder="Ingrese correo electrónico"
                                wire:model="email"
                                class="pr-10 {{ $errors->has('email') ? 'border-red-500' : '' }}">
                            </x-slot:input>
                            <x-slot:error>
                                {{ $errors->first('email') }}
                            </x-slot:error>
                        </x-form-input>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <x-form-input>
                            <x-slot:label>
                                Contraseña {{ $id ? '(dejar en blanco para mantener)' : '' }}
                            </x-slot:label>
                            <x-slot:input
                                type="password"
                                name="password"
                                placeholder="Ingrese contraseña"
                                wire:model="password"
                                class="pr-10 {{ $errors->has('password') ? 'border-red-500' : '' }}">
                            </x-slot:input>
                            <x-slot:error>
                                {{ $errors->first('password') }}
                            </x-slot:error>
                        </x-form-input>

                        <x-form-select
                            label="Rol"
                            name="role_id"
                            :options="$roles->pluck('name', 'id')"
                            wire:model="role_id"
                            :error="$errors->has('role_id') ? true : false"
                        />
                        <x-slot:error>
                            {{ $errors->first('role_id') }}
                        </x-slot:error>
                    </div>

                    <div class="flex justify-end gap-4 mt-6">
                        <x-secondary-button type="button" wire:click="$navigate('{{ route('settings.users') }}')">
                            Cancelar
                        </x-secondary-button>

                        <x-primary-button type="submit">
                            {{ $id ? 'Actualizar Usuario' : 'Crear Usuario' }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-modal-success name="modal-user-created">
        <x-slot:title>
            {{ $id ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente' }}
        </x-slot:title>

        <x-slot:description>
            {{ $id ? 'El usuario ha sido actualizado correctamente' : 'El usuario ha sido creado correctamente' }}
        </x-slot:description>

        <x-primary-button wire:click="closeModal" class="w-full">
            Cerrar
        </x-primary-button>
    </x-modal-success>
</div>
