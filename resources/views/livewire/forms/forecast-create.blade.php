<div>
    <div class="flex items-center justify-between" wire:ignore>
        <x-view-title>
            <x-slot:title>
                {{ $title }}
            </x-slot:title>

            <x-slot:content>
                {{ $subtitle }}
            </x-slot:content>
        </x-view-title>

        <div class="flex gap-4">
            <x-primary-button wire:click="{{ $forecast ? 'updateForecast' : 'createForecast' }}" class="w-[209px]">
                {{ $forecast ? 'Actualizar forecast' : 'Crear forecast' }}
            </x-primary-button>
        </div>
    </div>

    <x-form wire:submit.prevent="{{ $forecast ? 'updateForecast' : 'createForecast' }}">
        <div class="p-8 space-y-10 bg-white rounded-2xl">
            <div class="flex gap-4">
                <div class="w-full space-y-6">
                    <h3 class="text-lg font-bold text-neutral-blue">Información Básica</h3>

                    <div class="flex gap-4">
                        <div class="grow">
                            <x-form-input>
                                <x-slot:label>
                                    Material
                                </x-slot:label>
                                <x-slot:input name="material" placeholder="Ingrese material"
                                    wire:model="material">
                                </x-slot:input>
                            </x-form-input>
                        </div>
                        <x-form-input class="grow">
                            <x-slot:label>
                                Short Text
                            </x-slot:label>
                            <x-slot:input name="short_text" placeholder="Ingrese short text"
                                wire:model="short_text">
                            </x-slot:input>
                        </x-form-input>
                    </div>

                    <div class="flex gap-4">
                        <div class="grow">
                            <x-form-input>
                                <x-slot:label>
                                    Purchase Requisition
                                </x-slot:label>
                                <x-slot:input name="purchase_requisition" placeholder="Ingrese requisición"
                                    wire:model="purchase_requisition">
                                </x-slot:input>
                            </x-form-input>
                        </div>
                        <x-form-input class="grow">
                            <x-slot:label>
                                Fecha de Liberación
                            </x-slot:label>
                            <x-slot:input name="release_date" type="date" wire:model="release_date">
                            </x-slot:input>
                        </x-form-input>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">
                <x-form-input>
                    <x-slot:label>
                        Supplying Plant
                    </x-slot:label>
                    <x-slot:input name="supplying_plant" placeholder="Ingrese supplying plant" wire:model="supplying_plant">
                    </x-slot:input>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Unit of Measure
                    </x-slot:label>
                    <x-slot:input name="unit_of_measure" placeholder="Ingrese unidad de medida" wire:model="unit_of_measure">
                    </x-slot:input>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        UOM Real
                    </x-slot:label>
                    <x-slot:input name="uom_real" placeholder="Ingrese UOM real" wire:model="uom_real">
                    </x-slot:input>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Plant
                    </x-slot:label>
                    <x-slot:input name="plant" placeholder="Ingrese planta" wire:model="plant">
                    </x-slot:input>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Quantity Requested
                    </x-slot:label>
                    <x-slot:input name="quantity_requested" type="number" step="0.001" placeholder="Ingrese cantidad solicitada" wire:model="quantity_requested">
                    </x-slot:input>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Qty Real
                    </x-slot:label>
                    <x-slot:input name="qty_real" type="number" step="0.001" placeholder="Ingrese cantidad real" wire:model="qty_real">
                    </x-slot:input>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Delivery Date
                    </x-slot:label>
                    <x-slot:input name="delivery_date" type="date" wire:model="delivery_date">
                    </x-slot:input>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Planned Delivery Time (days)
                    </x-slot:label>
                    <x-slot:input name="planned_delivery_time" type="number" placeholder="Tiempo de entrega planificado" wire:model="planned_delivery_time">
                    </x-slot:input>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        MRP Controller
                    </x-slot:label>
                    <x-slot:input name="mrp_controller" placeholder="Ingrese MRP controller" wire:model="mrp_controller">
                    </x-slot:input>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Vendor Name
                    </x-slot:label>
                    <x-slot:input name="vendor_name" placeholder="Ingrese nombre del proveedor" wire:model="vendor_name">
                    </x-slot:input>
                </x-form-input>

                <x-form-input>
                    <x-slot:label>
                        Vendor Code
                    </x-slot:label>
                    <x-slot:input name="vendor_code" placeholder="Ingrese código del proveedor" wire:model="vendor_code">
                    </x-slot:input>
                </x-form-input>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="p-4 mt-4 text-green-700 bg-green-100 rounded-md">
                {{ session('message') }}
            </div>
        @endif
    </x-form>

    <x-modal-success name="modal-forecast-created">
        <x-slot:title>
            {{ $forecast ? 'Forecast actualizado correctamente' : 'Forecast creado correctamente' }}
        </x-slot:title>

        <x-slot:description>
            {{ $forecast ? 'El forecast ha sido actualizado correctamente' : 'El forecast ha sido creado correctamente' }}
        </x-slot:description>

        <x-primary-button wire:click="$dispatch('close-modal', 'modal-forecast-created')" class="w-full">
            Cerrar
        </x-primary-button>
    </x-modal-success>
</div>
