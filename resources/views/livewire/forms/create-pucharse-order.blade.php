<div>
    <!-- Notification area for errors and success messages -->
    <div x-data="{ showNotification: false, notificationMessage: '', notificationType: 'error' }"
         @show-error.window="showNotification = true; notificationMessage = $event.detail; notificationType = 'error'; setTimeout(() => showNotification = false, 5000)"
         @show-success.window="showNotification = true; notificationMessage = $event.detail; notificationType = 'success'; setTimeout(() => showNotification = false, 5000)">

        <!-- Error/Success Notification -->
        <div x-show="showNotification"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2"
             class="fixed top-4 right-4 z-50 p-4 max-w-sm rounded-lg shadow-lg"
             :class="notificationType === 'error' ? 'bg-red-100 border border-red-400 text-red-700' : 'bg-green-100 border border-green-400 text-green-700'">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg x-show="notificationType === 'error'" class="w-5 h-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <svg x-show="notificationType === 'success'" class="w-5 h-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium" x-text="notificationMessage"></p>
                </div>
                <div class="pl-3 ml-auto">
                    <div class="-mx-1.5 -my-1.5">
                        <button @click="showNotification = false" type="button" class="inline-flex p-1.5 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2" :class="notificationType === 'error' ? 'text-red-500 hover:bg-red-200 focus:ring-red-600' : 'text-green-500 hover:bg-green-200 focus:ring-green-600'">
                            <span class="sr-only">Dismiss</span>
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-center">
        <x-view-title>
            <x-slot:title>
                {{ isset($id) ? 'Editar Orden de compra: ' . $order_number : 'Generar nueva orden de compra' }}
            </x-slot:title>

            <x-slot:content>
                {{ isset($id) ? 'Modifique los datos de la Orden de compra' : 'Ingrese los datos para cargar su Orden de compra' }}
            </x-slot:content>
        </x-view-title>

        <div class="space-x-4">
            @if($id)
                <a href="{{ route('purchase-orders.detail', $id) }}">
                    <x-secondary-button class="w-[209px]">
                        Cancelar
                    </x-secondary-button>
                </a>
            @endif

            @if($id)
                <x-primary-button wire:click="updatePurchaseOrder({{ $id }})" class="w-[209px]">
                    Actualizar Orden
                </x-primary-button>
            @else
                <x-primary-button wire:click="createPurchaseOrder" class="w-[209px]">
                    Crear nueva Orden
                </x-primary-button>
            @endif
        </div>
    </div>

    <x-form>
        <div class="p-8 space-y-10 bg-white rounded-2xl">
            <div class="flex gap-4">
                <div class="space-y-6 w-full">
                    <h3 class="text-lg font-bold text-[#7288FF]">Datos generales</h3>
                    <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">
                        <div class="relative">
                            <x-form-input>
                                <x-slot:label>
                                    Número PO
                                </x-slot:label>

                                <x-slot:input name="order_number" placeholder="Ingrese número PO" wire:model="order_number" class="pr-10 {{ $errors->has('order_number') ? 'border-red-500 bg-red-50' : '' }}"></x-slot:input>

                                <x-slot:error>
                                    <div class="flex items-center text-red-600">
                                        {{ $errors->first('order_number') }}
                                    </div>
                                </x-slot:error>
                            </x-form-input>
                        </div>
                        <x-form-input>
                            <x-slot:label>
                                Fecha de creación
                            </x-slot:label>
                            <x-slot:input name="order_date" type="date" placeholder="Ingrese número PO" wire:model="order_date" class="pr-10 {{ $errors->has('order_date') ? 'border-red-500' : '' }}">
                            </x-slot:input>
                            <x-slot:error>
                                {{ $errors->first('order_date') }}
                            </x-slot:error>
                        </x-form-input>
                        <x-form-select label="Moneda" name="currency" wireModel="currency" :options="$currencyArray" :error="$errors->has('currency') ? true : false" />
                        <x-form-select label="Incoterms" name="incoterms" wireModel="incoterms" :options="$tiposIncotermArray" :error="$errors->has('incoterms') ? true : false" />
                        <x-form-select label="HUB Planificado" name="planned_hub_id" wireModel="planned_hub_id" :options="$hubsArray" :error="$errors->has('planned_hub_id') ? true : false" />
                        <x-form-select label="HUB Real" name="actual_hub_id" wireModel="actual_hub_id" :options="$hubsArray" :error="$errors->has('actual_hub_id') ? true : false" />
                    </div>
                </div>

                <x-form-input-file class="hidden space-y-6">
                    <x-slot:label class="!text-lg !font-bold">
                        Adjunte la orden para autocompletar
                    </x-slot:label>
                    <x-slot:input name="autocomplete_product">
                    </x-slot:input>
                </x-form-input-file>
            </div>

            <div class="space-y-6 w-full">
                <h3 class="text-lg font-bold text-[#7288FF]">Datos vendor</h3>

                <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">
                    <x-form-select label="Seleccionar Vendor" name="vendor_id" wireModel="vendor_id"
                        :options="$vendorArray" :error="$errors->has('vendor_id') ? true : false" />
                </div>
            </div>

            <div class="space-y-6 w-full">
                <h3 class="text-lg font-bold text-[#7288FF]">Datos Ship to</h3>

                <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">
                    <x-form-select label="Seleccionar Ship to" name="ship_to_id" wireModel="ship_to_id"
                        :options="$shipToArray" :error="$errors->has('ship_to_id') ? true : false" />
                </div>
            </div>

            <div class="space-y-6 w-full">
                <h3 class="text-lg font-bold text-[#7288FF]">Datos de facturación</h3>
                <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">
                    <x-form-select label="Seleccionar Bill to" name="bill_to_id" wireModel="bill_to_id"
                        :options="$billToArray" :error="$errors->has('bill_to_id') ? true : false" />
                </div>
            </div>

            <div class="space-y-6 w-full">
                <h3 class="text-lg font-bold text-[#7288FF]">Dimensiones en centímetros</h3>
                <div class="grid grid-cols-[1fr,1fr,1fr,1fr] gap-x-5 gap-y-6">
                    <x-form-input>
                        <x-slot:label>
                            Largo (in)
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" wire:model="largo" name="largo" placeholder="0.00" class="pr-10 {{ $errors->has('largo') ? 'border-red-500' : '' }}">
                        </x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('largo') }}
                        </x-slot:error>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Ancho (in)
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" wire:model="ancho" name="ancho" placeholder="0.00" class="pr-10 {{ $errors->has('ancho') ? 'border-red-500' : '' }}">
                        </x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('ancho') }}
                        </x-slot:error>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Alto (in)
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" wire:model="alto" name="alto" placeholder="0.00" class="pr-10 {{ $errors->has('alto') ? 'border-red-500' : '' }}">
                        </x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('alto') }}
                        </x-slot:error>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>
                            Volumen (ft³)
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="volumen" placeholder="0.00"
                            wire:model="volumen" disabled class="pr-10 {{ $errors->has('volumen') ? 'border-red-500' : '' }}">
                        </x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('volumen') }}
                        </x-slot:error>
                    </x-form-input>
                </div>
            </div>

            <div class="space-y-6 w-full">
                <h3 class="text-lg font-bold text-[#7288FF]">Dimensiones</h3>
                <div class="grid grid-cols-[1fr,1fr,1fr,1fr] gap-x-5 gap-y-6">
                    <x-form-input>
                        <x-slot:label>
                            Largo (cm)
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="largo" placeholder="0.00"
                            wire:model="length_cm" disabled class="pr-10 {{ $errors->has('length_cm') ? 'border-red-500' : '' }}">
                        </x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('length_cm') }}
                        </x-slot:error>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Ancho (cm)
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="ancho" placeholder="0.00"
                            wire:model="width_cm" disabled class="pr-10 {{ $errors->has('width_cm') ? 'border-red-500' : '' }}">
                        </x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('width_cm') }}
                        </x-slot:error>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Alto (cm)
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="alto" placeholder="0.00"
                            wire:model="height_cm" disabled class="pr-10 {{ $errors->has('height_cm') ? 'border-red-500' : '' }}">
                        </x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('height_cm') }}
                        </x-slot:error>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Peso (kg)
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" min="0" name="peso_kg"
                            placeholder="0.00" wire:model="peso_kg" class="pr-10 {{ $errors->has('peso_kg') ? 'border-red-500' : '' }}">
                        </x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('peso_kg') }}
                        </x-slot:error>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Peso (lb)
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" min="0" name="peso_lb"
                            placeholder="0.00" wire:model="peso_lb">
                        </x-slot:input>
                    </x-form-input>
                </div>
            </div>

            <div class="space-y-6 w-full">
                <h3 class="text-lg font-bold text-[#7288FF]">Fechas</h3>

                <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">
                    <x-form-input>
                        <x-slot:label>
                            Fecha requerida en destino
                        </x-slot:label>

                        <x-slot:input type="date" name="date_required_in_destination"
                            wire:model="date_required_in_destination" class="pr-10 {{ $errors->has('date_required_in_destination') ? 'border-red-500' : '' }}">
                        </x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('date_required_in_destination') }}
                        </x-slot:error>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Fecha pickup planificada
                        </x-slot:label>

                        <x-slot:input type="date" name="date_planned_pickup"
                            wire:model="date_planned_pickup">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Fecha pickup real
                        </x-slot:label>

                        <x-slot:input type="date" name="date_actual_pickup"
                            wire:model="date_actual_pickup">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Fecha estimada de llegada al hub
                        </x-slot:label>

                        <x-slot:input type="date" name="date_estimated_hub_arrival"
                            wire:model="date_estimated_hub_arrival">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Fecha de llegada real al hub
                        </x-slot:label>

                        <x-slot:input type="date" name="date_actual_hub_arrival"
                            wire:model="date_actual_hub_arrival">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Fecha ETD (Fecha estimada de salida)
                        </x-slot:label>

                        <x-slot:input type="date" name="date_etd" wire:model="date_etd">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Fecha ATD (Fecha real de salida)
                        </x-slot:label>

                        <x-slot:input type="date" name="date_atd" wire:model="date_atd">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Fecha ETA (Fecha estimada de llegada)
                        </x-slot:label>

                        <x-slot:input type="date" name="date_eta" wire:model="date_eta">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Fecha ATA (Fecha real de llegada)
                        </x-slot:label>

                        <x-slot:input type="date" name="date_ata" wire:model="date_ata">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Fecha de consolidado
                        </x-slot:label>

                        <x-slot:input type="date" name="date_consolidation"
                            wire:model="date_consolidation">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Fecha de release
                        </x-slot:label>

                        <x-slot:input type="date" name="release_date" wire:model="release_date">
                        </x-slot:input>
                    </x-form-input>
                </div>
            </div>

            <div class="space-y-6 w-full">
                <h3 class="text-lg font-bold text-[#7288FF]">Información Adicional</h3>
                <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">

                    <div>
                        <label class="text-sm font-medium text-[#565AFF]">
                            Tipo de Material
                        </label>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            @foreach($materialTypeOptions as $value => $label)
                                <div class="flex items-center">
                                    <input
                                        id="material_type_{{ $value }}"
                                        type="checkbox"
                                        value="{{ $value }}"
                                        wire:model="material_type"
                                        class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
                                    >
                                    <label for="material_type_{{ $value }}" class="block ml-2 text-sm text-gray-700">
                                        {{ $label }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('material_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-form-select label="Seguro" name="ensurence_type" wire:model="ensurence_type" :options="['pending' => 'Pendiente', 'applied' => 'Aplicado']" />

                    <x-form-select label="Modo de transporte" name="mode" wire:model.live="mode" :options="['maritimo' => 'Marítimo', 'aereo' => 'Aéreo']" :error="$errors->has('mode') ? true : false" />
                    <x-form-input>
                        <x-slot:label>
                            Tracking ID
                        </x-slot:label>
                        <x-slot:input name="tracking_id" placeholder="Ingrese Tracking ID" wire:model="tracking_id">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Cantidad estimada de pallets
                        </x-slot:label>
                        <x-slot:input type="number" name="pallet_quantity" placeholder="0" wire:model.live="pallet_quantity">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Cantidad Real de Pallets
                        </x-slot:label>
                        <x-slot:input type="number" name="pallet_quantity_real" placeholder="0" wire:model.live="pallet_quantity_real">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Conocimiento de Embarque
                        </x-slot:label>
                        <x-slot:input type="number" name="bill_of_lading" placeholder="0" wire:model="bill_of_lading">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Costo Transporte terrestre EWR
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="ground_transport_cost_1" placeholder="0.00" wire:model.live="ground_transport_cost_1">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Costo Transporte terrestre MIA
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="ground_transport_cost_2" placeholder="0.00" wire:model.live="ground_transport_cost_2">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Costo de Nacionalización
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="cost_nationalization" placeholder="0.00" wire:model="cost_nationalization">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Costo OFR Estimado
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="cost_ofr_estimated" placeholder="0.00" wire:model.live="cost_ofr_estimated" disabled>
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Costo OFR Real
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="cost_ofr_real" placeholder="0.00" wire:model.live="cost_ofr_real">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Costo Estimado de Pallets
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="estimated_pallet_cost" placeholder="0.00" wire:model.live="estimated_pallet_cost">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Costo Real Estimado PO
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="real_cost_estimated_po" placeholder="0.00" wire:model="real_cost_estimated_po">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Costo Real PO
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="real_cost_real_po" placeholder="0.00" wire:model="real_cost_real_po">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Costo de Seguro
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="insurance_cost" placeholder="0.00" wire:model.live="insurance_cost">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Otros Gastos
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="other_expenses" placeholder="0.00" wire:model.live="other_expenses">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Peso Variable Calculado
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="variable_calculare_weight" placeholder="0.00" wire:model="variable_calculare_weight">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Ahorros OFR FCL
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="savings_ofr_fcl" placeholder="0.00" wire:model.live="savings_ofr_fcl" disabled>
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Ahorro en pickup
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="saving_pickup" placeholder="0.00" wire:model="saving_pickup" disabled>
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Ahorro Ejecutado
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="saving_executed" placeholder="0.00" wire:model="saving_executed">
                        </x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>
                            Ahorro No Ejecutado
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" name="saving_not_executed" placeholder="0.00" wire:model="saving_not_executed">
                        </x-slot:input>
                    </x-form-input>
                </div>
            </div>
        </div>

        <div class="p-8 space-y-6 w-full bg-white rounded-2xl">
            <h3 class="w-fit border-b-2 border-[#190FDB] pb-2 text-lg font-bold text-[#190FDB]">Carga / Contenido</h3>

            <div class="flex flex-col space-y-4">
                <!-- Buscador de productos -->
                <div class="flex gap-4">
                    <div class="w-full max-w-md">
                        <div class="relative">
                            <x-form-input class="w-full">
                                <x-slot:label>
                                    Buscar producto
                                </x-slot:label>
                                <x-slot:input name="searchTerm" wire:model.live="searchTerm"
                                    wire:keyup="searchProducts" placeholder="Buscar por ID o descripción">
                                </x-slot:input>
                            </x-form-input>
                            @if (count($searchResults) > 0)
                                <div class="absolute z-10 mt-1 w-full bg-white rounded-md shadow-lg">
                                    <ul class="overflow-auto py-1 max-h-60 text-base rounded-md sm:text-sm">
                                        @foreach ($searchResults as $product)
                                            <li class="relative py-2 pr-9 pl-3 cursor-pointer select-none hover:bg-gray-100"
                                                wire:click="selectProduct({{ $product->id }})">
                                                <div class="flex flex-col items-start">
                                                    <span class="font-medium">{{ $product->material_id }}</span>
                                                    <span class="text-sm text-gray-500">{{ $product->short_text ?? 'Sin descripción' }}</span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>

                    <x-form-input class="w-24">
                        <x-slot:label>
                            Cantidad
                        </x-slot:label>
                        <x-slot:input type="number" min="1" name="quantity" wire:model="quantity">
                        </x-slot:input>
                    </x-form-input>

                    <div class="self-end h-fit" x-data="{ selectedProduct: @entangle('selectedProduct') }">
                        <x-primary-button class="border-[3px] border-[#565AFF] disabled:border-[#EDEDED]"
                             wire:click="addProduct">
                            Agregar
                        </x-primary-button>
                    </div>
                </div>

                <!-- Producto seleccionado -->
                @if ($selectedProduct)
                    <div class="p-3 mt-2 bg-gray-50 rounded-md">
                        <div class="flex justify-between">
                            <div>
                                <p class="font-medium">{{ $selectedProduct->material_id }}</p>
                                <p class="text-sm text-gray-500">{{ $selectedProduct->short_text }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium">Precio: {{ number_format($selectedProduct->price_per_unit, 2) }}
                                </p>
                                <p class="text-sm text-gray-500">Subtotal:
                                    {{ number_format($selectedProduct->price_per_unit * $quantity, 2) }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($errors->has('products'))
                    <div class="mt-4 text-red-500">{{ $errors->first('products') }}</div>
                @endif
                <!-- Tabla de productos agregados -->
                <div class="mt-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th scope="col"
                                    class="bg-[#E0E5FF] px-6 py-3 text-left text-lg font-bold text-[#171717]">
                                    ID</th>
                                <th scope="col"
                                    class="bg-[#E0E5FF] px-6 py-3 text-left text-lg font-bold text-[#171717]">
                                    Descripción</th>
                                <th scope="col"
                                    class="bg-[#E0E5FF] px-6 py-3 text-left text-lg font-bold text-[#171717]">
                                    Precio unitario</th>
                                <th scope="col"
                                    class="bg-[#E0E5FF] px-6 py-3 text-left text-lg font-bold text-[#171717]">
                                    Carga (kg)</th>
                                <th scope="col"
                                    class="bg-[#E0E5FF] px-6 py-3 text-left text-lg font-bold text-[#171717]">
                                    Subtotal</th>
                                <th scope="col"
                                    class="bg-[#E0E5FF] px-6 py-3 text-left text-lg font-bold text-[#171717]">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($orderProducts as $index => $product)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                        {{ $product['material_id'] }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $product['short_text'] ?? 'Sin descripción' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        $ {{ number_format($product['price_per_unit'], 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        <input type="number"
                                            wire:model.live="orderProducts.{{ $index }}.quantity"
                                            wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                            min="1"
                                            class="block w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        $ {{ number_format($product['subtotal'], 2) }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-left whitespace-nowrap">
                                        <button type="button" wire:click="removeProduct({{ $index }})"
                                            class="text-red-600 hover:text-red-900">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                                                    <path d="M5 1H9M1 3H13M11.6667 3L11.1991 10.0129C11.129 11.065 11.0939 11.5911 10.8667 11.99C10.6666 12.3412 10.3648 12.6235 10.0011 12.7998C9.58798 13 9.06073 13 8.00623 13H5.99377C4.93927 13 4.41202 13 3.99889 12.7998C3.63517 12.6235 3.33339 12.3412 3.13332 11.99C2.90607 11.5911 2.871 11.065 2.80086 10.0129L2.33333 3" stroke="#666666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:stroke-red-900"></path>
                                                </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-sm text-center text-gray-500">No hay
                                        productos agregados</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-sm font-medium text-right text-gray-900">
                                    Total Neto:</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                    $ {{ number_format($net_total, 2) }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-sm font-medium text-right text-gray-900">
                                    Costo Adicional:</td>
                                <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                    <input type="number" wire:model.live="additional_cost" step="0.01"
                                        class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-sm font-medium text-right text-gray-900">
                                    Costo de Seguro:</td>
                                <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                    <input type="number" wire:model.live="insurance_cost" step="0.01"
                                        class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" disabled>
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-sm font-bold text-right text-gray-900">TOTAL:
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900 whitespace-nowrap">
                                    $ {{ number_format($total, 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </x-form>

    <x-modal-success name="modal-purchase-order-created">
        <x-slot:title>
            @if ($id)
                Orden de compra fue editada correctamente
            @else
                Orden de compra creada correctamente
            @endif
        </x-slot:title>

        <x-slot:description>
            @if ($id)
                La orden de compra ha sido editada correctamente con el número de orden: {{ $order_number }}
            @else
                La orden de compra ha sido creada correctamente con el número de orden: {{ $order_number }}
            @endif
        </x-slot:description>

        <x-primary-button wire:click="closeModal" class="w-full">
            Cerrar
        </x-primary-button>
    </x-modal-success>
</div>

<script>
    // Ejecutar cuando el DOM esté completamente cargado
    document.addEventListener('DOMContentLoaded', function() {
        // Función para encontrar un campo por su wire:model
        function findField(wireModel) {
            return document.querySelector(`[wire\\:model="${wireModel}"], [wire\\:model\\.live="${wireModel}"]`);
        }

        // Obtener referencias a los campos
        const lengthCmField = findField('length_cm');
        const widthCmField = findField('width_cm');
        const heightCmField = findField('height_cm');
        const lengthInField = findField('largo');
        const widthInField = findField('ancho');
        const heightInField = findField('alto');
        const volumeField = findField('volumen');
        const weightKgField = findField('peso_kg');
        const weightLbField = findField('peso_lb');

        console.log('Campos encontrados:', {
            lengthCm: lengthCmField,
            widthCm: widthCmField,
            heightCm: heightCmField,
            lengthIn: lengthInField,
            widthIn: widthInField,
            heightIn: heightInField,
            volume: volumeField,
            weightKg: weightKgField,
            weightLb: weightLbField
        });

        // Constante de conversión: 1 pulgada = 2.54 cm
        const INCH_TO_CM = 2.54;

        // Función para convertir pulgadas a cm
        function convertInchToCm(value) {
            return value * INCH_TO_CM;
        }

        // Función para convertir pulgadas a cm y actualizar campos
        function updateDimensions() {
            console.log('Actualizando dimensiones');

            const lengthIn = parseFloat(lengthInField?.value) || 0;
            const widthIn = parseFloat(widthInField?.value) || 0;
            const heightIn = parseFloat(heightInField?.value) || 0;

            // Convertir a centímetros
            const lengthCm = convertInchToCm(lengthIn);
            const widthCm = convertInchToCm(widthIn);
            const heightCm = convertInchToCm(heightIn);

            console.log('Valores en pulgadas:', { lengthIn, widthIn, heightIn });
            console.log('Valores convertidos a cm:', { lengthCm, widthCm, heightCm });

            // Actualizar campos de centímetros
            if (lengthCmField) {
                lengthCmField.value = lengthCm.toFixed(2);
                lengthCmField.dispatchEvent(new Event('input', { bubbles: true }));
            }

            if (widthCmField) {
                widthCmField.value = widthCm.toFixed(2);
                widthCmField.dispatchEvent(new Event('input', { bubbles: true }));
            }

            if (heightCmField) {
                heightCmField.value = heightCm.toFixed(2);
                heightCmField.dispatchEvent(new Event('input', { bubbles: true }));
            }

            console.log('Dimensiones convertidas a centímetros:', {
                lengthCm: lengthCm.toFixed(2),
                widthCm: widthCm.toFixed(2),
                heightCm: heightCm.toFixed(2)
            });

            // Calcular volumen usando las dimensiones en pulgadas
            calculateVolume(lengthIn, widthIn, heightIn);
        }

        // Función para calcular el volumen en pies cúbicos usando pulgadas
        function calculateVolume(lengthIn, widthIn, heightIn) {
            console.log('Calculando volumen con dimensiones en pulgadas');

            if (lengthIn && widthIn && heightIn) {
                // Fórmula para convertir pulgadas cúbicas a pies cúbicos: (L × W × H) ÷ 1,728
                const volume = (lengthIn * widthIn * heightIn) / 1728;
                if (volumeField) {
                    volumeField.value = volume.toFixed(3);
                    // Disparar evento de cambio para que Livewire detecte el cambio
                    volumeField.dispatchEvent(new Event('input', { bubbles: true }));
                }
                console.log('Volumen calculado en pies cúbicos:', volume.toFixed(3));
            }
        }

        // Variables para evitar bucles infinitos en conversiones de peso
        let isConverting = false;

        // Función para convertir kg a lb
        function convertKgToLb() {
            if (isConverting) return; // Evitar bucle infinito

            console.log('Convirtiendo kg a lb');
            const kg = parseFloat(weightKgField?.value) || 0;

            if (kg) {
                isConverting = true;
                const lb = kg * 2.20462;
                if (weightLbField) {
                    weightLbField.value = lb.toFixed(2);
                    // Disparar evento de cambio para que Livewire detecte el cambio
                    weightLbField.dispatchEvent(new Event('input', { bubbles: true }));
                }
                console.log('Peso convertido a lb:', lb.toFixed(2));
                isConverting = false;
            }
        }

        // Función para convertir lb a kg
        function convertLbToKg() {
            if (isConverting) return; // Evitar bucle infinito

            console.log('Convirtiendo lb a kg');
            const lb = parseFloat(weightLbField?.value) || 0;

            if (lb) {
                isConverting = true;
                const kg = lb * 0.453592;
                if (weightKgField) {
                    weightKgField.value = kg.toFixed(2);
                    // Disparar evento de cambio para que Livewire detecte el cambio
                    weightKgField.dispatchEvent(new Event('input', { bubbles: true }));
                }
                console.log('Peso convertido a kg:', kg.toFixed(2));
                isConverting = false;
            }
        }

        // Agregar event listeners para los campos en pulgadas
        if (lengthInField) {
            lengthInField.addEventListener('input', updateDimensions);
            console.log('Event listener agregado a lengthIn');
        }

        if (widthInField) {
            widthInField.addEventListener('input', updateDimensions);
            console.log('Event listener agregado a widthIn');
        }

        if (heightInField) {
            heightInField.addEventListener('input', updateDimensions);
            console.log('Event listener agregado a heightIn');
        }

        // Mantener los event listeners para peso
        if (weightKgField) {
            weightKgField.addEventListener('input', convertKgToLb);
            console.log('Event listener agregado a weightKg');
        }

        if (weightLbField) {
            weightLbField.addEventListener('input', convertLbToKg);
            console.log('Event listener agregado a weightLb');
        }

        // Calcular valores iniciales si ya hay datos
        updateDimensions();
        convertKgToLb();

        console.log('Script de cálculo inicializado');
    });
</script>
