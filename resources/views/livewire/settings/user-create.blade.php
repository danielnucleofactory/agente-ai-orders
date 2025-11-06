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

                    <!-- Sección de Empresas -->
                    <div class="grid grid-cols-1 gap-4 mt-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#565AFF] ml-[1.125rem]">
                                Empresas *
                            </label>
                            <div class="space-y-2 max-h-40 overflow-y-auto border-2 border-[#9AABFF] rounded-md p-3 bg-white {{ $errors->has('company_ids') ? 'border-red-500' : '' }}">
                                @foreach($companies as $company)
                                    <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                                        <input
                                            type="checkbox"
                                            wire:model="company_ids"
                                            value="{{ $company->id }}"
                                            class="rounded border-[#9AABFF] text-[#565AFF] focus:ring-[#9AABFF]"
                                        >
                                        <span class="text-sm text-[#2E2E2E]">{{ $company->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @if($errors->has('company_ids') || $errors->has('company_ids.*'))
                                <span class="text-red-500 text-xs mt-1">
                                    {{ $errors->first('company_ids') ?: $errors->first('company_ids.*') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 mt-6">
                        <a href="{{ route('settings.users') }}">
                            <x-secondary-button type="button">
                                Cancelar
                            </x-secondary-button>
                        </a>

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
