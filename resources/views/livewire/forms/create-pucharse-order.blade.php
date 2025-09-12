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
                    <div class="space-y-6">
                        <h2 class="text-lg font-bold text-blue-600">Datos generales</h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                            {{-- Identificación de la OC --}}
                            <div class="md:col-span-3">
                                <h4 class="text-sm font-semibold text-[#7288FF]">Identificación de la OC</h4>
                            </div>

                            {{-- Número de Orden (PO) --}}
                            <x-form-input>
                                <x-slot name="label">Número de Orden (PO)</x-slot>
                                <x-slot:input
                                    name="order_number"
                                    placeholder="Ingrese número de orden"
                                    wire:model="order_number"
                                    class="pr-10 {{ $errors->has('order_number') ? 'border-red-500'  : '' }}">
                                </x-slot:input>
                                <x-slot:error>
                                    {{ $errors->first('order_number') }}
                                </x-slot:error>
                            </x-form-input>

                            <x-form-input>
                                <x-slot name="label">Fecha emisión PO</x-slot>
                                <x-slot:input
                                    type="date"
                                    name="emision_date_po"
                                    wire:model="emision_date_po">
                                </x-slot:input>
                            </x-form-input>

                            {{-- Fecha de creación --}}
                            <x-form-input>
                                <x-slot name="label">Fecha de creación en RAGA</x-slot>
                                <x-slot:input
                                    type="date"
                                    name="order_date"
                                    wire:model="order_date"
                                    class="pr-10">
                                </x-slot:input>
                                <x-slot:error>
                                    {{ $errors->first('order-date') }}
                                </x-slot:error>
                            </x-form-input>

                            {{-- Condiciones comerciales --}}
                            <div class="md:col-span-3">
                                <h4 class="text-sm font-semibold text-[#7288FF]">Condiciones comerciales</h4>
                            </div>

                            {{-- Moneda --}}
                            <x-form-select
                                label="Moneda"
                                name="currency"
                                :options="$currencyArray"
                                wire:model="currency"
                                :error="$errors->has('currency')"
                            />

                            {{-- Incoterm Precios --}}
                            <x-form-select
                                label="Incoterm Precios"
                                name="price_incoterm"
                                :options="$tiposIncotermArray"
                                wire:model="price_incoterm"
                                :error="$errors->has('price_incoterm')"
                            />

                            {{-- Incoterms (Compra) --}}
                            <x-form-select
                                label="Incoterm de Compra"
                                name="incoterms"
                                :options="$tiposIncotermArray"
                                wire:model="incoterms"
                                :error="$errors->has('incoterms')"
                            />

                            {{-- Planificación logística --}}
                            <div class="md:col-span-3">
                                <h4 class="text-sm font-semibold text-[#7288FF]">Planificación logística</h4>
                            </div>

                            {{-- HUB planificado --}}
{{--                            <x-form-select--}}
{{--                                label="HUB Planificado"--}}
{{--                                name="planned_hub_id"--}}
{{--                                :options="$hubArray"--}}
{{--                                wire:model="planned_hub_id"--}}
{{--                                :error="$errors->has('planned_hub_id')"--}}
{{--                            />--}}

                            {{-- Incoterm logístico --}}
                            <x-form-select
                                label="Incoterm logístico"
                                name="logistics_incoterm"
                                :options="$tiposIncotermArray"
                                wire:model="logistics_incoterm"
                                :error="$errors->has('logistics_incoterm')"
                            />

                            {{-- HUB real --}}
{{--                            <x-form-select--}}
{{--                                label="HUB Real"--}}
{{--                                name="actual_hub_id"--}}
{{--                                :options="$hubArray"--}}
{{--                                wire:model="actual_hub_id"--}}
{{--                                :error="$errors->has('actual_hub_id')"--}}
{{--                            />--}}

                            {{-- Clasificación --}}
                            <div class="md:col-span-3">
                                <h4 class="text-sm font-semibold text-[#7288FF]">Clasificación</h4>
                            </div>

                            {{-- Categoría (nuevo) --}}
                            <x-form-input>
                                <x-slot:label>
                                    Categoría
                                </x-slot:label>
                                <x-slot:input
                                    name="category"
                                    placeholder="Categoría de la OC"
                                    wire:model="category"
                                    class="pr-10 {{ $errors->has('category') ? 'border-red-500' : '' }}">
                                </x-slot:input>
                                <x-slot:error>
                                    {{ $errors->first('category') }}
                                </x-slot:error>
                            </x-form-input>

                            {{-- Notas / Motivo --}}
                            <div class="md:col-span-3">
                                <h4 class="text-sm font-semibold text-[#7288FF]">Notas / Motivo</h4>
                            </div>

                            {{-- Motivo (nuevo) --}}
                            <div class="md:col-span-3">
                                <x-form-input>
                                    <x-slot:label>
                                        Motivo
                                    </x-slot:label>
                                    <x-slot:input
                                        name="reason"
                                        placeholder="Ingrese motivo de la PO"
                                        wire:model="reason"
                                        class="pr-10 {{ $errors->has('reason') ? 'border-red-500' : '' }}">
                                    </x-slot:input>
                                    <x-slot:error>
                                        {{ $errors->first('reason') }}
                                    </x-slot:error>
                                </x-form-input>
                            </div>

                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-blue-600">Identificadores y transporte</h3>
                    <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">

                        <!-- Itinerario -->
                        <div class="col-span-3">
                            <h4 class="text-sm font-semibold text-[#7288FF]">Itinerario</h4>
                        </div>

                        <x-form-input>
                            <x-slot:label>Puerto de Embarque</x-slot:label>
                            <x-slot:input name="departure_port" wire:model="departure_port" placeholder="Ingrese puerto de embarque"></x-slot:input>
                            <x-slot:error>{{ $errors->first('departure_port') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>Puerto de Arribo</x-slot:label>
                            <x-slot:input name="arrival_port" wire:model="arrival_port" placeholder="Ingrese puerto de arribo"></x-slot:input>
                            <x-slot:error>{{ $errors->first('arrival_port') }}</x-slot:error>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>Línea Naviera</x-slot:label>
                            <x-slot:input name="shipping_line" wire:model="shipping_line" placeholder="Ingrese naviera"></x-slot:input>
                        </x-form-input>

                        <!-- Naviera y equipo -->
                        <div class="col-span-3">
                            <h4 class="text-sm font-semibold text-[#7288FF]">Naviera y equipo</h4>
                        </div>

                        <x-form-input>
                            <x-slot:label>Tipo de Contenedor</x-slot:label>
                            <x-slot:input name="container_type" wire:model="container_type" placeholder="Tipo de contenedor"></x-slot:input>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>Número de Contenedor</x-slot:label>
                            <x-slot:input name="container_number" wire:model="container_number" placeholder="Ingrese número"></x-slot:input>
                        </x-form-input>

                        <!-- Identificadores de embarque -->
                        <div class="col-span-3">
                            <h4 class="text-sm font-semibold text-[#7288FF]">Identificadores de embarque</h4>
                        </div>

                        <x-form-input>
                            <x-slot:label>MBL Number</x-slot:label>
                            <x-slot:input name="mbl_number" wire:model="mbl_number" placeholder="Ingrese número"></x-slot:input>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>Proforma de Fábrica</x-slot:label>
                            <x-slot:input name="factory_proforma_number" wire:model="factory_proforma_number" placeholder="Ingrese número" class="pr-10 {{ $errors->has('factory_proforma_number') ? 'border-red-500' : '' }}">
                            </x-slot:input>
                            <x-slot:error>{{ $errors->first('factory_proforma_number') }}</x-slot:error>
                        </x-form-input>
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
                <h3 class="text-lg font-bold text-blue-600">Datos Proveedor</h3>

                <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">
                    <x-form-select label="Seleccionar Nombre del Proveedor" name="vendor_id" wireModel="vendor_id"
                        :options="$vendorArray" :error="$errors->has('vendor_id') ? true : false" />

                    <x-form-input>
                        <x-slot:label>Número de Proveedor</x-slot:label>
                        <x-slot:input
                            name="vendor_number"
                            wire:model="vendor_number"
                            placeholder="Ingrese número de proveedor">
                        </x-slot:input>
                    </x-form-input>
                </div>
            </div>

            <div class="space-y-6 w-full hidden">
                <h3 class="text-lg font-bold text-blue-600">Datos Ship to</h3>

                <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">
                    <x-form-select label="Seleccionar Ship to" name="ship_to_id" wireModel="ship_to_id"
                        :options="$shipToArray" :error="$errors->has('ship_to_id') ? true : false" />
                </div>
            </div>

            <div class="space-y-6 w-full hidden">
                <h3 class="text-lg font-bold text-blue-600">Datos de facturación</h3>
                <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">
                    <x-form-select label="Seleccionar Bill to" class="hidden" name="bill_to_id" wireModel="bill_to_id"
                        :options="$billToArray" :error="$errors->has('bill_to_id') ? true : false" />
                </div>
            </div>

            <div class="space-y-6 w-full hidden">
                <h3 class="text-lg font-bold text-blue-600">Dimensiones en centímetros</h3>
                <div class="grid grid-cols-[1fr,1fr,1fr,1fr] gap-x-5 gap-y-6">
                    <x-form-input class="hidden">
                        <x-slot:label>
                            Largo (in)
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" wire:model="largo" name="largo" placeholder="0.00" class="pr-10 {{ $errors->has('largo') ? 'border-red-500' : '' }}">
                        </x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('largo') }}
                        </x-slot:error>
                    </x-form-input>
                    <x-form-input class="hidden">
                        <x-slot:label>
                            Ancho (in)
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" wire:model="ancho" name="ancho" placeholder="0.00" class="pr-10 {{ $errors->has('ancho') ? 'border-red-500' : '' }}">
                        </x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('ancho') }}
                        </x-slot:error>
                    </x-form-input>
                    <x-form-input class="hidden">
                        <x-slot:label>
                            Alto (in)
                        </x-slot:label>
                        <x-slot:input type="number" step="0.01" wire:model="alto" name="alto" placeholder="0.00" class="pr-10 {{ $errors->has('alto') ? 'border-red-500' : '' }}">
                        </x-slot:input>

                        <x-slot:error>
                            {{ $errors->first('alto') }}
                        </x-slot:error>
                    </x-form-input>

                    <x-form-input class="hidden">
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
                <h3 class="text-lg font-bold text-blue-600">Dimensiones</h3>
                <div class="grid grid-cols-[1fr,1fr,1fr,1fr] gap-x-5 gap-y-6">
                    <x-form-input>
                        <x-slot:label>CBM (m³)</x-slot:label>
                        <x-slot:input
                            type="number"
                            step="0.01"
                            min="0"
                            name="cbm"
                            wire:model="cbm"
                            placeholder="0.00">
                        </x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
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
                    <x-form-input class="hidden">
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
                    <x-form-input class="hidden">
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
                <h3 class="text-lg font-bold text-blue-600">Fechas</h3>

                <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">

                    <!-- Booking y coordinación -->
                    <div class="col-span-3">
                        <h4 class="text-sm font-semibold text-[#7288FF]">Booking y coordinación</h4>
                    </div>

                    <x-form-input>
                        <x-slot:label>Solicitud de Booking</x-slot:label>
                        <x-slot:input type="date" name="date_booking_request" wire:model="date_booking_request"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Autorización Booking</x-slot:label>
                        <x-slot:input type="date" name="date_booking_authorized" wire:model="date_booking_authorized"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Fecha Agente de Carga</x-slot:label>
                        <x-slot:input
                            type="date"
                            name="forwader_date"
                            wire:model="forwader_date">
                        </x-slot:input>
                    </x-form-input>

                    <!-- Origen: preparación y carga -->
                    <div class="col-span-3">
                        <h4 class="text-sm font-semibold text-[#7288FF]">Origen: preparación y carga</h4>
                    </div>

                    <x-form-input>
                        <x-slot:label>Fecha Inspección</x-slot:label>
                        <x-slot:input type="date" wire:model.live="inspection_date"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Fecha Corte VGM</x-slot:label>
                        <x-slot:input type="date" wire:model.live="vgm_cut_date"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Fecha Carga Lista Teórica</x-slot:label>
                        <x-slot:input type="date" name="date_theorical_load" wire:model="date_theorical_load" class="pr-10 {{ $errors->has('date_theorical_load') ? 'border-red-500' : '' }}">
                        </x-slot:input>
                        <x-slot:error>{{ $errors->first('date_theorical_load') }}</x-slot:error>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Fecha Carga Lista Variable</x-slot:label>
                        <x-slot:input type="date" name="date_variable_date" wire:model="date_variable_date"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Fecha Carga Lista Real</x-slot:label>
                        <x-slot:input type="date" name="date_carga_po" wire:model="date_carga_po"></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Fecha pickup planificada</x-slot:label>
                        <x-slot:input type="date" name="date_planned_pickup" wire:model="date_planned_pickup"></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Fecha pickup real</x-slot:label>
                        <x-slot:input type="date" name="date_actual_pickup" wire:model="date_actual_pickup"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Fecha de consolidado</x-slot:label>
                        <x-slot:input type="date" name="date_consolidation" wire:model="date_consolidation"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Fecha de release</x-slot:label>
                        <x-slot:input type="date" name="release_date" wire:model="release_date"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Diferencia Fecha de Carga</x-slot:label>
                        <x-slot:input
                            type="date"
                            name="dif_load_date"
                            wire:model="dif_load_date">
                        </x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Nombre del Consolidador</x-slot:label>
                        <x-slot:input
                            name="consolidator_name"
                            wire:model="consolidator_name"
                            placeholder="Ingrese Nombre del consolidador">
                        </x-slot:input>
                    </x-form-input>

                    <!-- Salida (origen) -->
                    <div class="col-span-3">
                        <h4 class="text-sm font-semibold text-[#7288FF]">Salida (origen)</h4>
                    </div>

                    <x-form-input>
                        <x-slot:label>ETD Inicial</x-slot:label>
                        <x-slot:input type="date" wire:model.live="date_etd_initial"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>ETD</x-slot:label>
                        <x-slot:input type="date" name="date_etd" wire:model="date_etd"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>ATD</x-slot:label>
                        <x-slot:input type="date" name="date_atd" wire:model="date_atd"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>ETD actualizada</x-slot:label>
                        <x-slot:input type="date" name="date_etd_updated" wire:model="date_etd_updated"></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Fecha estimada de llegada al hub</x-slot:label>
                        <x-slot:input type="date" name="date_estimated_hub_arrival" wire:model="date_estimated_hub_arrival"></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Fecha de llegada real al hub</x-slot:label>
                        <x-slot:input type="date" name="date_actual_hub_arrival" wire:model="date_actual_hub_arrival"></x-slot:input>
                    </x-form-input>


                    <!-- Arribo a destino -->
                    <div class="col-span-3">
                        <h4 class="text-sm font-semibold text-[#7288FF]">Arribo a destino</h4>
                    </div>

                    <x-form-input>
                        <x-slot:label>ETA</x-slot:label>
                        <x-slot:input type="date" name="date_eta" wire:model="date_eta"></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Fecha ATA (Fecha real de llegada)</x-slot:label>
                        <x-slot:input type="date" name="date_ata" wire:model="date_ata"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>ETA actualizada</x-slot:label>
                        <x-slot:input type="date" name="date_eta_updated" wire:model="date_eta_updated"></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Fecha requerida en destino</x-slot:label>
                        <x-slot:input
                            type="date"
                            name="date_required_in_destination"
                            wire:model="date_required_in_destination"
                            class="pr-10 {{ $errors->has('date_required_in_destination') ? 'border-red-500' : '' }}">
                        </x-slot:input>
                        <x-slot:error>
                            {{ $errors->first('date_required_in_destination') }}
                        </x-slot:error>
                    </x-form-input>

                    <!-- Almacén fiscal y recepción -->
                    <div class="col-span-3">
                        <h4 class="text-sm font-semibold text-[#7288FF]">Almacén fiscal y recepción</h4>
                    </div>

                    <x-form-input>
                        <x-slot:label>Ingreso Almacén Fiscal</x-slot:label>
                        <x-slot:input type="date" name="bonded_warehouse_enter" wire:model.live="bonded_warehouse_enter" class="pr-10 {{ $errors->has('bonded_warehouse_enter') ? 'border-red-500' : '' }}">
                        </x-slot:input>
                        <x-slot:error>{{ $errors->first('bonded_warehouse_enter') }}</x-slot:error>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Salida Almacén Fiscal</x-slot:label>
                        <x-slot:input type="date" wire:model.live="bonded_warehouse_exit" name="bonded_warehouse_exit" class="pr-10 {{ $errors->has('bonded_warehouse_exit') ? 'border-red-500' : '' }}">
                        </x-slot:input>
                        <x-slot:error>{{ $errors->first('bonded_warehouse_exit') }}</x-slot:error>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Fecha Recepción</x-slot:label>
                        <x-slot:input type="date" name="date_received" wire:model="date_received"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Fecha Nota de Recibo</x-slot:label>
                        <x-slot:input type="date" wire:model.live="receipt_note_date"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Fecha Disp. Bogeda Estimada</x-slot:label>
                        <x-slot:input type="date" wire:model.live="estimated_dc_availability_date"></x-slot:input>
                    </x-form-input>

                    <!-- Pagos y cargos -->
                    <div class="col-span-3">
                        <h4 class="text-sm font-semibold text-[#7288FF]">Pagos y cargos</h4>
                    </div>

                    <x-form-input>
                        <x-slot:label>Fecha Pago Balance</x-slot:label>
                        <x-slot:input type="date" wire:model.live="balance_payment_date"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Fecha Pago Cargos Locales</x-slot:label>
                        <x-slot:input type="date" wire:model.live="local_charges_payment_date"></x-slot:input>
                    </x-form-input>

                    <!-- Métricas y varios -->
                    <div class="col-span-3">
                        <h4 class="text-sm font-semibold text-[#7288FF]">Métricas y varios</h4>
                    </div>

                    <x-form-input>
                        <x-slot:label>Días Libres Contenedor</x-slot:label>
                        <x-slot:input type="number" step="1" inputmode="numeric" placeholder="0" wire:model.live="container_free_days"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Dif Fechas ETD (días)</x-slot:label>
                        <x-slot:input type="number" step="1" inputmode="numeric" readonly placeholder="0" wire:model.live="etd_dates_difference"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Dif Fechas ETA (días)</x-slot:label>
                        <x-slot:input type="number" step="1" inputmode="numeric" readonly placeholder="0" wire:model.live="eta_dates_difference"></x-slot:input>
                    </x-form-input>
                </div>
            </div>

            <div class="space-y-6 w-full">
                <h3 class="text-lg font-bold text-blue-600">Información Adicional</h3>

                <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">

                    <!-- Configuración del envío -->
                    <div class="col-span-3">
                        <h4 class="text-sm font-semibold text-[#7288FF]">Configuración del envío</h4>
                    </div>

                    <x-form-select label="Tipo de Transporte" name="mode" wire:model.live="mode" :options="['maritimo' => 'Marítimo', 'aereo' => 'Aéreo']" :error="$errors->has('mode') ? true : false" />

                    <x-form-select class="hidden" label="Seguro" name="ensurence_type" wire:model="ensurence_type" :options="['pending' => 'Pendiente', 'applied' => 'Aplicado']" />

                    <x-form-input>
                        <x-slot:label>Número de Booking</x-slot:label>
                        <x-slot:input name="tracking_id" placeholder="Ingrese Tracking ID" wire:model="tracking_id"></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Conocimiento de Embarque</x-slot:label>
                        <x-slot:input name="bill_of_lading" placeholder="Ingrese conocimiento de embarque" wire:model="bill_of_lading"></x-slot:input>
                    </x-form-input>

                    <!-- Tipo de material -->
                    <div class="col-span-3 hidden">
                        <h4 class="text-sm font-semibold text-[#565AFF]">Tipo de material</h4>

                        <!-- junto y compacto -->
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-2">
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

                    <!-- Opciones / Flags -->
                    <div class="col-span-3">
                        <h4 class="text-sm font-semibold text-[#565AFF]">Opciones</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-2 mt-2">
                            <div class="flex items-center hidden">
                                <input id="is_dropship" type="checkbox" wire:model="is_dropship"
                                       class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                <label for="is_dropship" class="block ml-2 text-sm text-gray-700">Dropship</label>
                            </div>
                            <div class="flex items-center">
                                <input id="applies_tlc" type="checkbox" wire:model="applies_tlc"
                                       class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                <label for="applies_tlc" class="block ml-2 text-sm text-gray-700">Aplica TLC</label>
                            </div>
                            <div class="flex items-center">
                                <input id="applies_af" type="checkbox" wire:model="applies_af"
                                       class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                <label for="applies_af" class="block ml-2 text-sm text-gray-700">Aplica AF</label>
                            </div>

                            <div class="flex items-center">
                                <input id="has_facture_merca" type="checkbox" wire:model="has_facture_merca"
                                       class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                <label for="has_facture_merca" class="block ml-2 text-sm text-gray-700">Tiene Factura Mercancía</label>
                            </div>
                            <div class="flex items-center">
                                <input id="used_rate_ok" type="checkbox" wire:model="used_rate_ok"
                                       class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                <label for="used_rate_ok" class="block ml-2 text-sm text-gray-700">Tarifa Utilizada OK</label>
                            </div>
                            <div class="flex items-center">
                                <input id="uses_bonded_warehouse" type="checkbox" wire:model="uses_bonded_warehouse"
                                       class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                <label for="uses_bonded_warehouse" class="block ml-2 text-sm text-gray-700">Usa Almacén Fiscal</label>
                            </div>

                            <div class="flex items-center">
                                <input id="apply_technical_note" type="checkbox" wire:model="apply_technical_note"
                                       class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                <label for="apply_technical_note" class="block ml-2 text-sm text-gray-700">Aplica Nota Técnica</label>
                            </div>
                            <div class="flex items-center">
                                <input id="etd_initial_validated" type="checkbox" wire:model="etd_initial_validated"
                                       class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                <label for="etd_initial_validated" class="block ml-2 text-sm text-gray-700">ETD Inicial Validada</label>
                            </div>
                            <div class="flex items-center">
                                <input id="port_of_loading_validated" type="checkbox" wire:model="port_of_loading_validated"
                                       class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                <label for="port_of_loading_validated" class="block ml-2 text-sm text-gray-700">Puerto de Embarque Validado</label>
                            </div>
                        </div>

                        @error('is_dropship')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        @error('applies_tlc')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        @error('applies_af')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>


                    <!-- Volúmenes / pallets -->
                    <div class="col-span-3">
                        <h4 class="text-sm font-semibold text-[#7288FF]">Volúmenes / pallets</h4>
                    </div>

                    <x-form-input>
                        <x-slot:label>Cantidad estimada de pallets</x-slot:label>
                        <x-slot:input type="number" name="pallet_quantity" placeholder="0" wire:model.live="pallet_quantity"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Cantidad Real de Pallets</x-slot:label>
                        <x-slot:input type="number" name="pallet_quantity_real" placeholder="0" wire:model.live="pallet_quantity_real"></x-slot:input>
                    </x-form-input>

                    <!-- Costos base -->
                    <div class="col-span-3">
                        <h4 class="text-sm font-semibold text-[#7288FF]">Costos base</h4>
                    </div>

                    <x-form-input>
                        <x-slot:label>Monto Factura</x-slot:label>
                        <x-slot:input type="number" step="0.01" inputmode="decimal" wire:model.live="Invoice_amount"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Monto Flete</x-slot:label>
                        <x-slot:input type="number" step="0.01" inputmode="decimal" wire:model.live="freight_amount"></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Costo de Seguro</x-slot:label>
                        <x-slot:input type="number" step="0.01" name="insurance_cost" placeholder="0.00" wire:model.live="insurance_cost"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Otros Gastos</x-slot:label>
                        <x-slot:input type="number" step="0.01" name="other_expenses" placeholder="0.00" wire:model.live="other_expenses"></x-slot:input>
                    </x-form-input>

                    <!-- Costos logísticos -->
                    <div class="col-span-3">
                        <h4 class="text-sm font-semibold text-[#7288FF]">Costos logísticos</h4>
                    </div>

                    <x-form-input class="hidden">
                        <x-slot:label>Costo Transporte terrestre EWR</x-slot:label>
                        <x-slot:input type="number" step="0.01" name="ground_transport_cost_1" placeholder="0.00" wire:model.live="ground_transport_cost_1"></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Costo Transporte terrestre MIA</x-slot:label>
                        <x-slot:input type="number" step="0.01" name="ground_transport_cost_2" placeholder="0.00" wire:model.live="ground_transport_cost_2"></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Costo de Nacionalización</x-slot:label>
                        <x-slot:input type="number" step="0.01" name="cost_nationalization" placeholder="0.00" wire:model="cost_nationalization"></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Costo OFR Estimado</x-slot:label>
                        <x-slot:input type="number" step="0.01" name="cost_ofr_estimated" placeholder="0.00" wire:model.live="cost_ofr_estimated" disabled></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Costo OFR Real</x-slot:label>
                        <x-slot:input type="number" step="0.01" name="cost_ofr_real" placeholder="0.00" wire:model.live="cost_ofr_real"></x-slot:input>
                    </x-form-input>

                    <!-- Totales y cálculos -->
                    <div class="col-span-3">
                        <h4 class="text-sm font-semibold text-[#7288FF]">Totales y cálculos</h4>
                    </div>

                    <x-form-input>
                        <x-slot:label>Costo Estimado de Pallets</x-slot:label>
                        <x-slot:input type="number" step="0.01" name="estimated_pallet_cost" placeholder="0.00" wire:model.live="estimated_pallet_cost"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Costo Total Estimado PO</x-slot:label>
                        <x-slot:input type="number" step="0.01" name="real_cost_estimated_po" placeholder="0.00" wire:model="real_cost_estimated_po"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Costo Real PO</x-slot:label>
                        <x-slot:input type="number" step="0.01" name="real_cost_real_po" placeholder="0.00" wire:model="real_cost_real_po"></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Peso Variable Calculado</x-slot:label>
                        <x-slot:input type="number" step="0.01" name="variable_calculare_weight" placeholder="0.00" wire:model="variable_calculare_weight"></x-slot:input>
                    </x-form-input>

                    <!-- Ahorros -->
                    <div class="col-span-3">
                        <h4 class="text-sm font-semibold text-[#7288FF]">Ahorros</h4>
                    </div>

                    <x-form-input class="hidden">
                        <x-slot:label>Ahorros OFR FCL</x-slot:label>
                        <x-slot:input type="number" step="0.01" name="savings_ofr_fcl" placeholder="0.00" wire:model.live="savings_ofr_fcl" disabled></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Ahorro en pickup</x-slot:label>
                        <x-slot:input type="number" step="0.01" name="saving_pickup" placeholder="0.00" wire:model="saving_pickup" disabled></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Ahorro Ejecutado</x-slot:label>
                        <x-slot:input type="number" step="0.01" name="saving_executed" placeholder="0.00" wire:model="saving_executed"></x-slot:input>
                    </x-form-input>

                    <x-form-input class="hidden">
                        <x-slot:label>Ahorro No Ejecutado</x-slot:label>
                        <x-slot:input type="number" step="0.01" name="saving_not_executed" placeholder="0.00" wire:model="saving_not_executed"></x-slot:input>
                    </x-form-input>

                </div>
            </div>

            <div class="space-y-6 w-full">
                <div class="space-y-6 w-full">
                    <h3 class="text-lg font-bold text-blue-600">Datos de negocio</h3>

                    <div class="grid grid-cols-[1fr,1fr,1fr] gap-x-5 gap-y-6">

                        <!-- Proveedores y contratación -->
                        <div class="col-span-3">
                            <h4 class="text-sm font-semibold text-[#7288FF]">Proveedores y contratación</h4>
                        </div>

                        <x-form-input>
                            <x-slot:label>Agente de Carga</x-slot:label>
                            <x-slot:input name="forwarder_name" placeholder="Ingrese agente de carga" wire:model="forwarder_name"></x-slot:input>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>Proveedor de Servicio</x-slot:label>
                            <x-slot:input type="text" placeholder="Ingrese proveedor de servicio" wire:model.live="service_provider"></x-slot:input>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>Comercializadora</x-slot:label>
                            <x-slot:input type="text" placeholder="Ingrese comercializadora" wire:model.live="trading_company"></x-slot:input>
                        </x-form-input>

                        <!-- Tarifas y ruta -->
                        <div class="col-span-3">
                            <h4 class="text-sm font-semibold text-[#7288FF]">Tarifas y ruta</h4>
                        </div>

                        <x-form-input>
                            <x-slot:label>Tipo Tarifa</x-slot:label>
                            <x-slot:input name="tariff_type" placeholder="Ingrese el tipo de tarifa" wire:model="tariff_type"></x-slot:input>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>Ruta Logística</x-slot:label>
                            <x-slot:input name="route_label" placeholder="Ingrese la ruta logística" wire:model="route_label" class="pr-10 {{ $errors->has('route_label') ? 'border-red-500' : '' }}">
                            </x-slot:input>
                            <x-slot:error>{{ $errors->first('route_label') }}</x-slot:error>
                        </x-form-input>

                        <!-- Segmento / cliente -->
                        <div class="col-span-3">
                            <h4 class="text-sm font-semibold text-[#7288FF]">Segmento / cliente</h4>
                        </div>

                        <x-form-input>
                            <x-slot:label>Grupo Repositor</x-slot:label>
                            <x-slot:input type="text" placeholder="Ingrese grupo repositor" wire:model.live="retail_group"></x-slot:input>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>Tipo Cliente</x-slot:label>
                            <x-slot:input type="text" placeholder="Ingrese tipo de cliente" wire:model.live="customer_type"></x-slot:input>
                        </x-form-input>

                        <!-- Documentos y referencias -->
                        <div class="col-span-3">
                            <h4 class="text-sm font-semibold text-[#7288FF]">Documentos y referencias</h4>
                        </div>

                        <x-form-input>
                            <x-slot:label>Factura</x-slot:label>
                            <x-slot:input type="text" placeholder="Ingrese factura" wire:model.live="invoice"></x-slot:input>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>Fecha recepción de factura</x-slot:label>
                            <x-slot:input type="date" name="date_invoice_received" wire:model="date_invoice_received"></x-slot:input>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>Fecha recepción doc. proveedor</x-slot:label>
                            <x-slot:input type="date" name="date_vendor_document_received" wire:model="date_vendor_document_received"></x-slot:input>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>Factura Flete</x-slot:label>
                            <x-slot:input name="cargo_invoice_number" placeholder="Ingrese factura de cargo" wire:model="cargo_invoice_number"></x-slot:input>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>Factura Mercancía</x-slot:label>
                            <x-slot:input type="text" placeholder="Ingrese factura de mercancía" wire:model.live="factura_merca"></x-slot:input>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>DUA Internamiento</x-slot:label>
                            <x-slot:input type="text" placeholder="Ingrese DUA Internamiento" wire:model.live="customs_dua"></x-slot:input>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>Expediente</x-slot:label>
                            <x-slot:input type="text" placeholder="Ingrese expediente" wire:model.live="case_number_file"></x-slot:input>
                        </x-form-input>

                        <x-form-input>
                            <x-slot:label>Nota de Recibo</x-slot:label>
                            <x-slot:input type="text" placeholder="Ingrese nota de recibo" wire:model.live="receipt_note"></x-slot:input>
                        </x-form-input>

                        <!-- Notas -->
                        <div class="col-span-3">
                            <h4 class="text-sm font-semibold text-[#7288FF]">Notas</h4>
                        </div>

                        <div class="col-span-3">
                            <x-form-input>
                                <x-slot:label>Notas de Visibilidad</x-slot:label>
                                <x-slot:input type="text" placeholder="Ingrese notas de visibilidad" wire:model.live="visibility_notes"></x-slot:input>
                            </x-form-input>
                        </div>

                    </div>
                </div>
            </div>


            <div class="space-y-6 w-full mb-10">
                <h3 class="text-lg font-bold text-blue-600">Estado de llegada</h3>
                <div class="grid grid-cols-[1fr,1fr] gap-x-5 gap-y-f6">
                    <x-form-input>
                        <x-slot:label>Estado</x-slot:label>
                        <x-slot:input name="arrival_status"  wire:model="arrival_status" placeholder="Ingrese estado"></x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>Días de retraso</x-slot:label>
                        <x-slot:input type="number" name="delay_days" wire:model="delay_days" placeholder="0"></x-slot:input>
                    </x-form-input>
                </div>
            </div>
        </div>

        <div class="p-8 space-y-6 w-full bg-white rounded-2xl hidden">
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
                            <tr class="hidden">
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
