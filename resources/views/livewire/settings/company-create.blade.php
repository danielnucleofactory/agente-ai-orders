<div>
    <div class="p-8 space-y-10 bg-white rounded-2xl">
        <div class="flex gap-4">
            <div class="space-y-6 w-full">
                <h3 class="text-lg font-bold text-neutral-blue">{{ $title }}</h3>
                <p class="text-sm text-gray-600">{{ $subtitle }}</p>

                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-2 gap-4">
                        <x-form-input>
                            <x-slot:label>
                                Nombre *
                            </x-slot:label>
                            <x-slot:input
                                name="name"
                                placeholder="Ingrese nombre de la empresa"
                                wire:model="name"
                                class="pr-10 {{ $errors->has('name') ? 'border-red-500' : '' }}">
                            </x-slot:input>
                            <x-slot:error>
                                {{ $errors->first('name') }}
                            </x-slot:error>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>
                                Dirección
                            </x-slot:label>
                            <x-slot:input
                                name="address"
                                placeholder="Ingrese dirección de la empresa"
                                wire:model="address"
                                class="pr-10 {{ $errors->has('address') ? 'border-red-500' : '' }}">
                            </x-slot:input>
                            <x-slot:error>
                                {{ $errors->first('address') }}
                            </x-slot:error>
                        </x-form-input>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <x-form-input>
                            <x-slot:label>
                                País
                            </x-slot:label>
                            <x-slot:input
                                name="country"
                                placeholder="Ingrese país"
                                wire:model="country"
                                class="pr-10 {{ $errors->has('country') ? 'border-red-500' : '' }}">
                            </x-slot:input>
                            <x-slot:error>
                                {{ $errors->first('country') }}
                            </x-slot:error>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>
                                Ciudad
                            </x-slot:label>
                            <x-slot:input
                                name="city"
                                placeholder="Ingrese ciudad"
                                wire:model="city"
                                class="pr-10 {{ $errors->has('city') ? 'border-red-500' : '' }}">
                            </x-slot:input>
                            <x-slot:error>
                                {{ $errors->first('city') }}
                            </x-slot:error>
                        </x-form-input>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <x-form-input>
                            <x-slot:label>
                                Código Postal
                            </x-slot:label>
                            <x-slot:input
                                name="zip"
                                placeholder="Ingrese código postal"
                                wire:model="zip"
                                class="pr-10 {{ $errors->has('zip') ? 'border-red-500' : '' }}">
                            </x-slot:input>
                            <x-slot:error>
                                {{ $errors->first('zip') }}
                            </x-slot:error>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>
                                Teléfono
                            </x-slot:label>
                            <x-slot:input
                                name="phone"
                                placeholder="Ingrese teléfono"
                                wire:model="phone"
                                class="pr-10 {{ $errors->has('phone') ? 'border-red-500' : '' }}">
                            </x-slot:input>
                            <x-slot:error>
                                {{ $errors->first('phone') }}
                            </x-slot:error>
                        </x-form-input>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <x-form-input>
                            <x-slot:label>
                                Sitio Web
                            </x-slot:label>
                            <x-slot:input
                                type="url"
                                name="website"
                                placeholder="https://ejemplo.com"
                                wire:model="website"
                                class="pr-10 {{ $errors->has('website') ? 'border-red-500' : '' }}">
                            </x-slot:input>
                            <x-slot:error>
                                {{ $errors->first('website') }}
                            </x-slot:error>
                        </x-form-input>
                    </div>

                    <div class="mt-4">
                        <x-form-input>
                            <x-slot:label>
                                Descripción
                            </x-slot:label>
                            <x-slot:input
                                name="description"
                                placeholder="Ingrese descripción de la empresa"
                                wire:model="description"
                                class="pr-10 {{ $errors->has('description') ? 'border-red-500' : '' }}">
                            </x-slot:input>
                            <x-slot:error>
                                {{ $errors->first('description') }}
                            </x-slot:error>
                        </x-form-input>
                    </div>

                    <div class="flex gap-4 justify-end mt-6">
                        <x-secondary-button type="button" wire:click="backToList">
                            Cancelar
                        </x-secondary-button>

                        <x-primary-button type="submit">
                            {{ $id ? 'Actualizar Empresa' : 'Crear Empresa' }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-modal-success name="modal-company-created">
        <x-slot:title>
            {{ $id ? 'Empresa actualizada correctamente' : 'Empresa creada correctamente' }}
        </x-slot:title>

        <x-slot:description>
            {{ $id ? 'La empresa ha sido actualizada correctamente' : 'La empresa ha sido creada correctamente' }}
        </x-slot:description>

        <x-primary-button wire:click="closeModal" class="w-full">
            Cerrar
        </x-primary-button>
    </x-modal-success>
</div>
