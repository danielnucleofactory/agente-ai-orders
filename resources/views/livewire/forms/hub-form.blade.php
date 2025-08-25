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

    <form wire:submit.prevent="saveHub">
        <div class="w-full max-w-[1254px] space-y-6 bg-white rounded-2xl p-8">
            <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">
                <!-- Información básica -->
                <x-form-input>
                    <x-slot:label>
                        Nombre *
                    </x-slot:label>
                    <x-slot:input name="name" placeholder="Ingrese nombre del hub" wire:model="name" required></x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('name') }}
                    </x-slot:error>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Código de Hub *
                    </x-slot:label>
                    <x-slot:input name="code" placeholder="Ingrese código del hub" wire:model="code" required></x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('code') }}
                    </x-slot:error>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        País *
                    </x-slot:label>
                    <x-slot:input name="country" placeholder="Ingrese país del hub" wire:model="country" required></x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('country') }}
                    </x-slot:error>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Corte documental *
                    </x-slot:label>
                    <x-slot:input name="documentary_cut" placeholder="Ingrese corte documental" wire:model="documentary_cut" required></x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('documentary_cut') }}
                    </x-slot:error>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Zarpe *
                    </x-slot:label>
                    <x-slot:input name="zarpe" placeholder="Ingrese zarpe" wire:model="zarpe" required></x-slot:input>
                    <x-slot:error>
                        {{ $errors->first('zarpe') }}
                    </x-slot:error>
                </x-form-input>
            </div>

            <div class="flex justify-end mt-6 space-x-4">
                <x-secondary-button type="button" wire:click="backToList">
                    Cancelar
                </x-secondary-button>
                <x-primary-button type="submit">
                    {{ $isEdit ? 'Actualizar' : 'Crear' }} Hub
                </x-primary-button>
            </div>
        </div>
    </form>

    <x-modal-success name="modal-hub-created">
        <x-slot:title>
            Hub creado correctamente
        </x-slot:title>

        <x-slot:description>
            El hub ha sido creado correctamente con el nombre: {{ $name }}
        </x-slot:description>

        <x-primary-button wire:click="closeModal" class="w-full">
            Cerrar
        </x-primary-button>
    </x-modal-success>

    <x-modal-success name="modal-hub-updated">
        <x-slot:title>
            Hub actualizado correctamente
        </x-slot:title>

        <x-slot:description>
            El hub ha sido actualizado correctamente con el nombre: {{ $name }}
        </x-slot:description>

        <x-primary-button wire:click="closeModal" class="w-full">
            Cerrar
        </x-primary-button>
    </x-modal-success>
</div>
