<div>
    <div class="flex max-w-[1254px] items-center justify-between mb-10">
        <x-view-title>
            <x-slot:title>
                {{ $title }}
            </x-slot:title>

            <x-slot:content>
                {{ $subtitle }}
            </x-slot:content>
        </x-view-title>
    </div>

    <form wire:submit.prevent="saveShipTo">
        <div class="w-full max-w-[1254px] space-y-6 bg-white rounded-2xl p-8">
            <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">
                <!-- Información básica -->
                <x-form-input>
                    <x-slot:label>
                        Nombre *
                    </x-slot:label>
                    <x-slot:input name="name" placeholder="Ingrese nombre" wire:model="name" required></x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('name') }}
                    </x-slot:error>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Email
                    </x-slot:label>
                    <x-slot:input type="email" name="email" placeholder="Ingrese email" wire:model="email"></x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('email') }}
                    </x-slot:error>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Persona de contacto
                    </x-slot:label>
                    <x-slot:input name="contact_person" placeholder="Ingrese persona de contacto" wire:model="contact_person"></x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('contact_person') }}
                    </x-slot:error>
                </x-form-input>

                <!-- Dirección y contacto -->
                <x-form-input>
                    <x-slot:label>
                        Dirección
                    </x-slot:label>
                    <x-slot:input name="address" placeholder="Ingrese dirección" wire:model="address"></x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('address') }}
                    </x-slot:error>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Código Postal
                    </x-slot:label>
                    <x-slot:input name="postal_code" placeholder="Ingrese código postal" wire:model="postal_code"></x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('postal_code') }}
                    </x-slot:error>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        País
                    </x-slot:label>
                    <x-slot:input name="country" placeholder="Ingrese país" wire:model="country"></x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('country') }}
                    </x-slot:error>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Estado/Provincia
                    </x-slot:label>
                    <x-slot:input name="state" placeholder="Ingrese estado o provincia" wire:model="state"></x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('state') }}
                    </x-slot:error>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Teléfono
                    </x-slot:label>
                    <x-slot:input name="phone" placeholder="Ingrese teléfono" wire:model="phone"></x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('phone') }}
                    </x-slot:error>
                </x-form-input>

                <x-form-select
                    label="Estado"
                    name="status"
                    wireModel="status"
                    :options="['active' => 'Activo', 'inactive' => 'Inactivo']"
                />
            </div>

            <div class="mt-6">
                <x-form-textarea
                    label="Notas"
                    name="notes"
                    wireModel="notes"
                    placeholder="Ingrese notas adicionales"
                    rows="4"
                />
            </div>

            <div class="flex justify-end mt-6 space-x-4">
                <a href="{{ route('ship-to.index') }}" class="px-4 py-2 text-gray-700 bg-gray-300 rounded-md hover:bg-gray-400">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">
                    {{ $isEdit ? 'Actualizar' : 'Crear' }} Dirección de Envío
                </button>
            </div>
        </div>
    </form>

    <x-modal-success name="modal-ship-to-created">
        <x-slot:title>
            Dirección de envío creada correctamente
        </x-slot:title>

        <x-slot:description>
            La dirección de envío ha sido creada correctamente con el nombre: {{ $name }}
        </x-slot:description>

        <x-primary-button wire:click="closeModal" class="w-full">
            Cerrar
        </x-primary-button>
    </x-modal-success>

    <x-modal-success name="modal-ship-to-updated">
        <x-slot:title>
            Dirección de envío actualizada correctamente
        </x-slot:title>

        <x-slot:description>
            La dirección de envío ha sido actualizada correctamente con el nombre: {{ $name }}
        </x-slot:description>

        <x-primary-button wire:click="closeModal" class="w-full">
            Cerrar
        </x-primary-button>
    </x-modal-success>
</div>
