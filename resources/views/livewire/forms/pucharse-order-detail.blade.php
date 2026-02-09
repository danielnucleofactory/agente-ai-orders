@php
    $travelMethod = $purchaseOrder->mode ?? 'aéreo';
    $materialType = $purchaseOrder->material_type;
@endphp

<div class="space-y-8">
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-[3.75rem]">
            <x-view-title>
                <x-slot:title>
                    {{ $purchaseOrder->order_number }}
                </x-slot:title>
            </x-view-title>

            <x-label class="hidden bg-success">
                <span>En tránsito</span>

                <span>{{ $travelMethod }}</span>

                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path
                            d="M14.7873 2.34374C15.5913 1.51335 16.9196 1.5026 17.7369 2.31989C18.5318 3.11479 18.5466 4.39893 17.7703 5.21193L15.4547 7.63674C15.2732 7.8268 15.1825 7.92183 15.1265 8.03313C15.077 8.13166 15.0476 8.23903 15.0399 8.34903C15.0312 8.47328 15.0607 8.60131 15.1198 8.85738L16.5597 15.097C16.6204 15.3599 16.6507 15.4913 16.6409 15.6184C16.6323 15.7309 16.6008 15.8405 16.5485 15.9405C16.4895 16.0535 16.3941 16.1489 16.2033 16.3396L15.8944 16.6486C15.3892 17.1537 15.1367 17.4063 14.8782 17.452C14.6525 17.4919 14.4203 17.4371 14.2362 17.3005C14.0255 17.144 13.9125 16.8051 13.6866 16.1274L12.0118 11.103L9.22408 13.8908C9.05767 14.0572 8.97447 14.1404 8.91881 14.2384C8.86951 14.3252 8.8362 14.4202 8.82048 14.5187C8.80273 14.63 8.81572 14.747 8.84171 14.9809L8.9948 16.3587C9.02079 16.5926 9.03378 16.7095 9.01604 16.8208C9.00031 16.9194 8.967 17.0143 8.9177 17.1011C8.86204 17.1991 8.77884 17.2823 8.61243 17.4487L8.44785 17.6133C8.05363 18.0075 7.85652 18.2046 7.63748 18.2617C7.44536 18.3118 7.24167 18.2916 7.0631 18.2049C6.85951 18.1059 6.70488 17.874 6.39563 17.4101L5.0887 15.4497C5.03345 15.3668 5.00582 15.3254 4.97375 15.2878C4.94526 15.2544 4.91418 15.2233 4.8808 15.1949C4.84321 15.1628 4.80178 15.1352 4.7189 15.0799L2.7585 13.773C2.29462 13.4637 2.06269 13.3091 1.96375 13.1055C1.87698 12.9269 1.85681 12.7232 1.90688 12.5311C1.96396 12.3121 2.16107 12.115 2.55529 11.7208L2.71988 11.5562C2.88628 11.3898 2.96948 11.3066 3.06747 11.2509C3.15427 11.2016 3.24922 11.1683 3.34781 11.1526C3.45909 11.1348 3.57603 11.1478 3.80993 11.1738L5.18775 11.3269C5.42164 11.3529 5.53859 11.3659 5.64987 11.3481C5.74845 11.3324 5.84341 11.2991 5.93021 11.2498C6.02819 11.1941 6.1114 11.1109 6.2778 10.9445L9.06557 8.15676L4.04117 6.48196C3.36348 6.25606 3.02464 6.14311 2.86814 5.93236C2.73149 5.74832 2.67667 5.51613 2.7166 5.29041C2.76232 5.03192 3.01488 4.77936 3.52 4.27424L3.82897 3.96526C4.01972 3.77452 4.11509 3.67915 4.22809 3.62007C4.32809 3.56778 4.43768 3.53635 4.5502 3.52769C4.67733 3.5179 4.80875 3.54823 5.0716 3.60888L11.2875 5.04333C11.5458 5.10294 11.675 5.13274 11.7997 5.12387C11.9201 5.11531 12.0372 5.08071 12.1428 5.02244C12.2523 4.9621 12.3445 4.86687 12.5289 4.67643L14.7873 2.34374Z"
                            stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </x-slot:icon>
            </x-label>
        </div>

        @can('has_edit_orders')
        <div class="flex space-x-4">
            <a href="{{ route('purchase-orders.edit', $purchaseOrder->id) }}" class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none"
                    class="absolute left-4 top-1/2 -translate-y-1/2">
                    <path
                        d="M1.87604 17.1159C1.92198 16.7024 1.94496 16.4957 2.00751 16.3025C2.06301 16.131 2.14143 15.9679 2.24064 15.8174C2.35246 15.6478 2.49955 15.5008 2.79373 15.2066L16 2.0003C17.1046 0.895732 18.8955 0.895734 20 2.0003C21.1046 3.10487 21.1046 4.89573 20 6.0003L6.79373 19.2066C6.49955 19.5008 6.35245 19.6479 6.18289 19.7597C6.03245 19.8589 5.86929 19.9373 5.69785 19.9928C5.5046 20.0553 5.29786 20.0783 4.88437 20.1243L1.5 20.5003L1.87604 17.1159Z"
                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <x-primary-button class="pl-12">Editar</x-primary-button>
            </a>
        </div>
        @endcan
    </div>

    {{-- SECCIÓN: Estado del Envío (Timeline de Porth) --}}
    @if($this->shouldShowTrackingSection())
    <div class="bg-white rounded-[0.625rem] p-6 shadow-sm mb-8">
        <h3 class="mb-6 text-lg font-bold text-[#1AAD8A]">Estado del Envío</h3>

        @if($loadingTracking)
            <div class="flex justify-center py-8">
                <div class="w-8 h-8 rounded-full border-b-2 animate-spin border-dark-blue"></div>
            </div>
        @elseif($this->shouldShowTimeline())
            <div class="relative">
                <!-- Timeline track -->
                <div class="absolute h-[2px] top-6 left-0 right-0 flex">
                    @php
                        $completedPhases = collect($trackingData['timeline'])->where('is_completed', true)->count();
                        $totalPhases = count($trackingData['timeline']);
                        $completedWidth = $totalPhases > 0 ? ($completedPhases / $totalPhases) * 100 : 0;
                    @endphp
                    <div class="bg-dark-blue" style="width: {{ $completedWidth }}%"></div>
                    <div class="bg-gray-200" style="width: {{ 100 - $completedWidth }}%"></div>
                </div>

                <!-- Timeline events -->
                <div class="flex relative justify-between">
                    @foreach($trackingData['timeline'] as $phase)
                        <div class="flex flex-col items-center">
                            <!-- Status dot -->
                            <div class="relative">
                                <div class="z-20 flex items-center justify-center w-12 h-12 mb-2 rounded-full transition-all duration-300
                                    {{ $phase['is_completed'] ? 'bg-dark-blue' : ($phase['is_current'] ? 'bg-dark-blue' : 'bg-gray-200') }}">
                                    @switch($phase['icon'])
                                        @case('warehouse')
                                            <svg class="w-6 h-6 {{ $phase['is_completed'] || $phase['is_current'] ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            @break
                                        @case('truck')
                                            <svg class="w-6 h-6 {{ $phase['is_completed'] || $phase['is_current'] ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 18.5h6M3 17h1m0 0v-4m0 4H2m2-4h8m11 4h-1m0 0v-4m0 4h1m-2-4h-7m-2-3V4a1 1 0 00-1-1H5a1 1 0 00-1 1v6m12 0h3.5a1 1 0 011 1v3M4 10h12"></path>
                                            </svg>
                                            @break
                                        @case('port')
                                            <svg class="w-6 h-6 {{ $phase['is_completed'] || $phase['is_current'] ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v2a2 2 0 002 2h8a2 2 0 002-2v-2M18 9l-6-6-6 6m6-6v10"></path>
                                            </svg>
                                            @break
                                        @case('ship')
                                            <svg class="w-6 h-6 {{ $phase['is_completed'] || $phase['is_current'] ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12c0 1.66-4.03 3-9 3s-9-1.34-9-3m18 0V9c0-1.66-4.03-3-9-3s-9 1.34-9 3v3m18 0v3c0 1.66-4.03 3-9 3s-9-1.34-9-3v-3"></path>
                                            </svg>
                                            @break
                                        @case('check')
                                            <svg class="w-6 h-6 {{ $phase['is_completed'] || $phase['is_current'] ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            @break
                                        @default
                                            <svg class="w-6 h-6 {{ $phase['is_completed'] || $phase['is_current'] ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                    @endswitch
                                </div>
                            </div>

                            <!-- Status details -->
                            <div class="mt-4 w-32 text-center">
                                <p class="mb-1 text-sm font-bold {{ $phase['is_completed'] || $phase['is_current'] ? 'text-dark-blue' : 'text-gray-400' }}">
                                    {{ $phase['name'] }}
                                </p>
                                @if($phase['date'])
                                    <p class="mb-1 text-xs font-medium {{ $phase['is_completed'] || $phase['is_current'] ? 'text-gray-600' : 'text-gray-400' }}">
                                        {{ formatDate($phase['date']) }}
                                        <span class="{{ $phase['is_completed'] || $phase['is_current'] ? 'text-dark-blue font-bold' : 'text-gray-400' }}">
                                            {{ \Carbon\Carbon::parse($phase['date'])->format('H:i') }}
                                        </span>
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Estimated delivery and current status -->
                <div class="p-6 mt-12 bg-white rounded-lg border border-gray-100 shadow-sm">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-[#E6F9F4] rounded-full">
                                <svg class="w-6 h-6 text-dark-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Entrega estimada</p>
                                <p class="text-lg font-bold text-dark-blue">
                                    {{ isset($trackingData['estimated_delivery']) ? formatDate($trackingData['estimated_delivery']) : 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-[#E6F9F4] rounded-full">
                                <svg class="w-6 h-6 text-dark-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Estado actual</p>
                                <p class="text-lg font-bold text-dark-blue">{{ $trackingData['current_phase'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="py-8 text-center text-gray-500">
                <p>No hay datos de tracking disponibles en Porth</p>
                <p class="text-sm mt-2">El contenedor, Documento de tránsito o booking no tiene información en Porth</p>
            </div>
        @endif
    </div>
    @endif

    {{-- SECCIÓN CON LOS CAMPOS DE LA PO (IGUAL QUE EN EDICIÓN) --}}
    <div class="rounded-[0.625rem] bg-white p-6 space-y-4" x-data="{
        datosGenerales: false,
        identificadores: false,
        proveedor: false,
        dimensiones: false,
        fechas: false,
        infoAdicional: false,
        datosNegocio: false,
        estadoLlegada: false,
        expandAll() {
            this.datosGenerales = true;
            this.identificadores = true;
            this.proveedor = true;
            this.dimensiones = true;
            this.fechas = true;
            this.infoAdicional = true;
            this.datosNegocio = true;
            this.estadoLlegada = true;
        },
        collapseAll() {
            this.datosGenerales = false;
            this.identificadores = false;
            this.proveedor = false;
            this.dimensiones = false;
            this.fechas = false;
            this.infoAdicional = false;
            this.datosNegocio = false;
            this.estadoLlegada = false;
        }
    }">
        {{-- Botones para expandir/contraer todo --}}
        <div class="flex gap-2 justify-end mb-2">
            <button @click="expandAll()" class="text-sm text-[#1AAD8A] hover:text-[#127A62] font-medium">
                Expandir todo
            </button>
            <span class="text-gray-300">|</span>
            <button @click="collapseAll()" class="text-sm text-[#1AAD8A] hover:text-[#127A62] font-medium">
                Contraer todo
            </button>
        </div>
        {{-- Datos generales --}}
        <div class="rounded-lg border border-gray-200">
            <button @click="datosGenerales = !datosGenerales" class="flex justify-between items-center p-4 w-full transition-colors hover:bg-gray-50">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Datos generales</h2>
                <svg :class="{ 'rotate-180': datosGenerales }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="datosGenerales" x-collapse class="px-4 pb-4">

            {{-- Identificación de la OC --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Identificación de la OC</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Número de Orden (PO)</p>
                    <p class="font-semibold">{{ $purchaseOrder->order_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Fecha emisión PO</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->emision_date_po) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Fecha de creación en Next</p>
                    <p class="font-semibold">{{ $purchaseOrder->created_at ? formatDate($purchaseOrder->created_at) : '-' }}</p>
                </div>
            </div>

            {{-- Condiciones comerciales --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Condiciones comerciales</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Moneda</p>
                    <p class="font-semibold">{{ $purchaseOrder->currency ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Incoterm Precios</p>
                    <p class="font-semibold">{{ $purchaseOrder->price_incoterm ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Incoterm de Compra</p>
                    <p class="font-semibold">{{ $purchaseOrder->incoterms ?? '-' }}</p>
                </div>
            </div>

            {{-- Planificación logística --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Planificación logística</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Incoterm logístico</p>
                    <p class="font-semibold">{{ $purchaseOrder->logistics_incoterm ?? '-' }}</p>
                </div>
            </div>

            {{-- Clasificación --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Clasificación</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Categoría</p>
                    <p class="font-semibold">{{ $purchaseOrder->category ?? '-' }}</p>
                </div>
            </div>

            {{-- Notas / Motivo --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Notas / Motivo</h4>
            <div class="grid grid-cols-1 gap-4 text-sm">
                <div>
                    <p class="mb-1 text-gray-500">Motivo</p>
                    <p class="font-semibold">{{ $purchaseOrder->reason ?? '-' }}</p>
                </div>
            </div>
            </div>
        </div>

        {{-- Identificadores y transporte --}}
        <div class="rounded-lg border border-gray-200">
            <button @click="identificadores = !identificadores" class="flex justify-between items-center p-4 w-full transition-colors hover:bg-gray-50">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Identificadores y transporte</h2>
                <svg :class="{ 'rotate-180': identificadores }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="identificadores" x-collapse class="px-4 pb-4">

            {{-- Itinerario --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Itinerario</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Puerto de Embarque</p>
                    <p class="font-semibold">{{ $purchaseOrder->departure_port ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Puerto de Embarque Validado</p>
                    <div class="flex items-center">
                        <input id="port_of_loading_validated" type="checkbox"
                               {{ $purchaseOrder->port_of_loading_validated ? 'checked' : '' }}
                               disabled
                               class="w-4 h-4 text-[#1AAD8A] rounded border-gray-300 focus:ring-[#1AAD8A]">
                        <label for="port_of_loading_validated" class="block ml-2 text-sm text-gray-700">
                            {{ $purchaseOrder->port_of_loading_validated ? 'Validado' : 'No validado' }}
                        </label>
                    </div>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Puerto de Arribo</p>
                    <p class="font-semibold">{{ $purchaseOrder->arrival_port ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Línea Naviera</p>
                    <p class="font-semibold">{{ $purchaseOrder->shipping_line ?? '-' }}</p>
                </div>
            </div>

            {{-- Naviera y equipo --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Naviera y equipo</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Tipo de Contenedor</p>
                    <p class="font-semibold">{{ $purchaseOrder->container_type ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Número de Contenedor</p>
                    <p class="font-semibold">{{ $purchaseOrder->container_number ?? '-' }}</p>
                </div>
            </div>

            {{-- Identificadores de embarque --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Identificadores de embarque</h4>
            <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Documento de tránsito</p>
                    <p class="font-semibold">{{ $purchaseOrder->mbl_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Proforma de Fábrica</p>
                    <p class="font-semibold">{{ $purchaseOrder->factory_proforma_number ?? '-' }}</p>
                </div>
            </div>
            </div>
        </div>

        {{-- Datos Proveedor --}}
        <div class="rounded-lg border border-gray-200">
            <button @click="proveedor = !proveedor" class="flex justify-between items-center p-4 w-full transition-colors hover:bg-gray-50">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Datos Proveedor</h2>
                <svg :class="{ 'rotate-180': proveedor }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="proveedor" x-collapse class="px-4 pb-4">
            <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Nombre del Proveedor</p>
                    <p class="font-semibold">{{ $purchaseOrder->vendor->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Código de Proveedor</p>
                    <p class="font-semibold">{{ $purchaseOrder->vendor_number ?? $purchaseOrder->vendor->vendo_code ?? '-' }}</p>
                </div>
            </div>
            </div>
        </div>

        {{-- Dimensiones --}}
        <div class="rounded-lg border border-gray-200">
            <button @click="dimensiones = !dimensiones" class="flex justify-between items-center p-4 w-full transition-colors hover:bg-gray-50">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Dimensiones</h2>
                <svg :class="{ 'rotate-180': dimensiones }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="dimensiones" x-collapse class="px-4 pb-4">
            <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">CBM (m³)</p>
                    <p class="font-semibold">{{ number_format($purchaseOrder->cbm ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Peso (kg)</p>
                    <p class="font-semibold">{{ number_format($purchaseOrder->weight_kg ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Peso (lb)</p>
                    <p class="font-semibold">{{ number_format($purchaseOrder->weight_lb ?? 0, 2) }}</p>
                </div>
            </div>
            </div>
        </div>

        {{-- Fechas --}}
        <div class="rounded-lg border border-gray-200">
            <button @click="fechas = !fechas" class="flex justify-between items-center p-4 w-full transition-colors hover:bg-gray-50">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Fechas</h2>
                <svg :class="{ 'rotate-180': fechas }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="fechas" x-collapse class="px-4 pb-4">

            {{-- Booking y coordinación --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Booking y coordinación</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Solicitud de Booking</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_booking_request) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Autorización Booking</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_booking_authorized) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Fecha de asignación de agente de carga</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->forwader_date) }}</p>
                </div>
            </div>

            {{-- Origen: preparación y carga --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Origen: preparación y carga</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Fecha Inspección</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->inspection_date) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Fecha Corte VGM</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->vgm_cut_date) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Fecha Carga Lista Teórica</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_theorical_load) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Fecha Carga Lista Variable</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_variable_date) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Carga Lista Validada</p>
                    <p class="font-semibold">{{ $purchaseOrder->carga_lista_validada ? 'Sí' : 'No' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Fecha de consolidado</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_consolidation) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Fecha de release</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->release_date) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Diferencia de fecha de carga lista</p>
                    <p class="font-semibold">{{ $this->calculateLoadDateDifference($purchaseOrder) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Nombre del Consolidador</p>
                    <p class="font-semibold">{{ $purchaseOrder->consolidator_name ?? '-' }}</p>
                </div>
            </div>

            {{-- Salida (origen) --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Salida (origen)</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">ETD Inicial</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_etd_initial) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">ETD Inicial Validada</p>
                    <div class="flex items-center">
                        <input id="etd_initial_validated" type="checkbox"
                               {{ $purchaseOrder->etd_initial_validated ? 'checked' : '' }}
                               disabled
                               class="w-4 h-4 text-[#1AAD8A] rounded border-gray-300 focus:ring-[#1AAD8A]">
                        <label for="etd_initial_validated" class="block ml-2 text-sm text-gray-700">
                            {{ $purchaseOrder->etd_initial_validated ? 'Validada' : 'No validada' }}
                        </label>
                    </div>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">ETD</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_etd) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">ATD</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_atd) }}</p>
                </div>
            </div>

            {{-- Arribo a destino --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Arribo a destino</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">ETA</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_eta) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">ETA Inicial</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_eta_initial) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">ATA</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_ata) }}</p>
                </div>
            </div>

            {{-- Almacén fiscal y recepción --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Almacén fiscal y recepción</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Ingreso Almacén Fiscal</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->bonded_warehouse_enter) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Salida Almacén Fiscal</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->bonded_warehouse_exit) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Fecha Nota de Recibo</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->receipt_note_date) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Fecha Disp. Bogeda Estimada</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->estimated_dc_availability_date) }}</p>
                </div>
            </div>

            {{-- Pagos y cargos --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Pagos y cargos</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Fecha Pago Balance</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->balance_payment_date) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Fecha Pago Cargos Locales</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->local_charges_payment_date) }}</p>
                </div>
            </div>

            {{-- Métricas y varios --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Métricas y varios</h4>
            <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Días Libres Contenedor</p>
                    <p class="font-semibold">{{ $purchaseOrder->container_free_days ?? '0' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Dif Fechas ETD (días)</p>
                    <p class="font-semibold">{{ $purchaseOrder->etd_dates_difference ?? '0' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Dif Fechas ETA (días)</p>
                    <p class="font-semibold">{{ $purchaseOrder->eta_dates_difference ?? '0' }}</p>
                </div>
            </div>
            </div>
        </div>

        {{-- Información Adicional --}}
        <div class="rounded-lg border border-gray-200">
            <button @click="infoAdicional = !infoAdicional" class="flex justify-between items-center p-4 w-full transition-colors hover:bg-gray-50">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Información Adicional</h2>
                <svg :class="{ 'rotate-180': infoAdicional }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="infoAdicional" x-collapse class="px-4 pb-4">

            {{-- Configuración del envío --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Configuración del envío</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Tipo de Transporte</p>
                    <p class="font-semibold">{{ ucfirst($purchaseOrder->mode ?? '-') }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Número de Booking</p>
                    <p class="font-semibold">{{ $purchaseOrder->tracking_id ?? '-' }}</p>
                </div>
            </div>

            {{-- Opciones --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Opciones</h4>
            <div class="grid grid-cols-2 gap-4 mb-6 text-sm md:grid-cols-3">
                <div class="flex items-center">
                    <input type="checkbox" {{ ($purchaseOrder->applies_tlc ?? false) ? 'checked' : '' }} disabled
                           class="w-4 h-4 text-[#1AAD8A] rounded border-gray-300 focus:ring-[#1AAD8A] opacity-75">
                    <label class="block ml-2 text-sm text-gray-700">Aplica TLC</label>
                </div>
                <div class="flex hidden items-center">
                    <input type="checkbox" {{ ($purchaseOrder->applies_af ?? false) ? 'checked' : '' }} disabled
                           class="w-4 h-4 text-[#1AAD8A] rounded border-gray-300 focus:ring-[#1AAD8A] opacity-75">
                    <label class="block ml-2 text-sm text-gray-700">Aplica AF</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" {{ ($purchaseOrder->has_facture_merca ?? false) ? 'checked' : '' }} disabled
                           class="w-4 h-4 text-[#1AAD8A] rounded border-gray-300 focus:ring-[#1AAD8A] opacity-75">
                    <label class="block ml-2 text-sm text-gray-700">Tiene Factura Mercancía</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" {{ ($purchaseOrder->used_rate_ok ?? false) ? 'checked' : '' }} disabled
                           class="w-4 h-4 text-[#1AAD8A] rounded border-gray-300 focus:ring-[#1AAD8A] opacity-75">
                    <label class="block ml-2 text-sm text-gray-700">Tarifa Utilizada OK</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" {{ ($purchaseOrder->uses_bonded_warehouse ?? false) ? 'checked' : '' }} disabled
                           class="w-4 h-4 text-[#1AAD8A] rounded border-gray-300 focus:ring-[#1AAD8A] opacity-75">
                    <label class="block ml-2 text-sm text-gray-700">Usa Almacén Fiscal</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" {{ ($purchaseOrder->apply_technical_note ?? false) ? 'checked' : '' }} disabled
                           class="w-4 h-4 text-[#1AAD8A] rounded border-gray-300 focus:ring-[#1AAD8A] opacity-75">
                    <label class="block ml-2 text-sm text-gray-700">Aplica Nota Técnica</label>
                </div>
            </div>

            {{-- Volúmenes / pallets --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Volúmenes / pallets</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Cantidad estimada de pallets</p>
                    <p class="font-semibold">{{ $purchaseOrder->pallet_quantity ?? '0' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Cantidad Real de Pallets</p>
                    <p class="font-semibold">{{ $purchaseOrder->pallet_quantity_real ?? '0' }}</p>
                </div>
            </div>

            {{-- Costos base --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Costos base</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Monto Factura</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->Invoice_amount ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Monto Flete</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->freight_amount ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Otros Gastos</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->other_expenses ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Monto Total</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->total_amount ?? 0, 2) }}</p>
                </div>
            </div>

            {{-- Totales y cálculos --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Totales y cálculos</h4>
            <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Costo Estimado de Pallets</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->estimated_pallet_cost ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Costo Total Estimado PO</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->real_cost_estimated_po ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Costo Real PO</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->real_cost_real_po ?? 0, 2) }}</p>
                </div>
            </div>
            </div>
        </div>

        {{-- Datos de negocio --}}
        <div class="rounded-lg border border-gray-200">
            <button @click="datosNegocio = !datosNegocio" class="flex justify-between items-center p-4 w-full transition-colors hover:bg-gray-50">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Datos de negocio</h2>
                <svg :class="{ 'rotate-180': datosNegocio }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="datosNegocio" x-collapse class="px-4 pb-4">

            {{-- Proveedores y contratación --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Proveedores y contratación</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div class="hidden">
                    <p class="mb-1 text-gray-500">Agente de Carga</p>
                    <p class="font-semibold">{{ $purchaseOrder->forwarder_name ?? '-' }}</p>
                </div>
                <div class="hidden">
                    <p class="mb-1 text-gray-500">Proveedor de Servicio</p>
                    <p class="font-semibold">{{ $purchaseOrder->service_provider ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Cliente</p>
                    <p class="font-semibold">{{ $purchaseOrder->trading_company ?? '-' }}</p>
                </div>
            </div>

            {{-- Tarifas y ruta --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Tarifas y ruta</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Tipo Tarifa</p>
                    <p class="font-semibold">{{ $purchaseOrder->tariff_type ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Ruta Logística</p>
                    <p class="font-semibold">{{ $purchaseOrder->route_label ?? '-' }}</p>
                </div>
            </div>

            {{-- Segmento / cliente --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Segmento / cliente</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Grupo Repositor</p>
                    <p class="font-semibold">{{ $purchaseOrder->retail_group ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Tipo Cliente</p>
                    <p class="font-semibold">{{ $purchaseOrder->customer_type ?? '-' }}</p>
                </div>
            </div>

            {{-- Documentos y referencias --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Documentos y referencias</h4>
            <div class="grid grid-cols-1 gap-4 mb-6 text-sm md:grid-cols-3">
                <div>
                    <p class="mb-1 text-gray-500">Factura</p>
                    <p class="font-semibold">{{ $purchaseOrder->invoice ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Fecha recepción de factura</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_invoice_received) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Fecha recepción doc. proveedor</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_vendor_document_received) }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Factura Flete</p>
                    <p class="font-semibold">{{ $purchaseOrder->cargo_invoice_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Factura Mercancía</p>
                    <p class="font-semibold">{{ $purchaseOrder->factura_merca ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">DUA Internamiento</p>
                    <p class="font-semibold">{{ $purchaseOrder->customs_dua ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Expediente</p>
                    <p class="font-semibold">{{ $purchaseOrder->case_number_file ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Nota de Recibo</p>
                    <p class="font-semibold">{{ $purchaseOrder->receipt_note ?? '-' }}</p>
                </div>
            </div>

            {{-- Notas --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Notas</h4>
            <div class="grid grid-cols-1 gap-4 text-sm">
                <div>
                    <p class="mb-1 text-gray-500">Notas de Visibilidad</p>
                    <p class="font-semibold">{{ $purchaseOrder->visibility_notes ?? '-' }}</p>
                </div>
            </div>
            </div>
        </div>

        {{-- Estado de llegada --}}
        <div class="rounded-lg border border-gray-200">
            <button @click="estadoLlegada = !estadoLlegada" class="flex justify-between items-center p-4 w-full transition-colors hover:bg-gray-50">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Estado de llegada</h2>
                <svg :class="{ 'rotate-180': estadoLlegada }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="estadoLlegada" x-collapse class="px-4 pb-4">
            <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                <div>
                    <p class="mb-1 text-gray-500">Estado</p>
                    <p class="font-semibold">{{ $purchaseOrder->arrival_status ?? '-' }}</p>
                </div>
                <div>
                    <p class="mb-1 text-gray-500">Días de retraso</p>
                    <p class="font-semibold">{{ $purchaseOrder->delay_days ?? '0' }}</p>
                </div>
            </div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN: Historia --}}
    <div class="bg-white rounded-[0.625rem] p-6 shadow-sm">
        {{-- Tab Content: Historia --}}
        <div>
            <div class="space-y-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold">Historia de Comentarios</h3>
                    <button wire:click="openCommentModal" x-on:click="$dispatch('open-modal', 'modal-comment-document')" class="flex items-center gap-2 text-sm text-gray-600 hover:text-[#0F614D]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="#1AAD8A" stroke-width="1.5">
                            <path d="M14.25 10.5V14.25M14.25 14.25V18M14.25 14.25H18M14.25 14.25H10.5M6 11.25H3.75C2.50736 11.25 1.5 10.2426 1.5 9C1.5 7.75736 2.50736 6.75 3.75 6.75H6M12 6.75H14.25C15.4926 6.75 16.5 7.75736 16.5 9C16.5 9.62132 16.2542 10.1835 15.8504 10.6M12 4.5V13.5M6 4.5V13.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Adjuntar documentación
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-[#D4F5ED]">
                            <tr>
                                <th class="px-4 py-3 text-sm font-semibold text-left text-gray-900">
                                    <button type="button" wire:click="sortComments('created_at')" class="flex items-center gap-1 hover:text-[#0F614D]">
                                        Fecha
                                        @if($commentSortField === 'created_at')
                                            <span>{{ $commentSortDirection === 'desc' ? '↓' : '↑' }}</span>
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-sm font-semibold text-left text-gray-900">
                                    <button type="button" wire:click="sortComments('user_name')" class="flex items-center gap-1 hover:text-[#0F614D]">
                                        Usuario
                                        @if($commentSortField === 'user_name')
                                            <span>{{ $commentSortDirection === 'desc' ? '↓' : '↑' }}</span>
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-sm font-semibold text-left text-gray-900">
                                    <button type="button" wire:click="sortComments('action_type')" class="flex items-center gap-1 hover:text-[#0F614D]">
                                        Tipo
                                        @if($commentSortField === 'action_type')
                                            <span>{{ $commentSortDirection === 'desc' ? '↓' : '↑' }}</span>
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-sm font-semibold text-left text-gray-900">
                                    <button type="button" wire:click="sortComments('comment')" class="flex items-center gap-1 hover:text-[#0F614D]">
                                        Comentario
                                        @if($commentSortField === 'comment')
                                            <span>{{ $commentSortDirection === 'desc' ? '↓' : '↑' }}</span>
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-sm font-semibold text-left text-gray-900">
                                    <button type="button" wire:click="sortComments('attachment_name')" class="flex items-center gap-1 hover:text-[#0F614D]">
                                        Archivos
                                        @if($commentSortField === 'attachment_name')
                                            <span>{{ $commentSortDirection === 'desc' ? '↓' : '↑' }}</span>
                                        @endif
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-sm font-semibold text-left text-gray-900">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($filteredComments as $comment)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ formatDateTime($comment['created_at']) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $comment['user_name'] ?? 'Usuario desconocido' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm whitespace-nowrap">
                                        @if(($comment['action_type'] ?? 'comment') === 'comment')
                                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">
                                                Comentario
                                            </span>
                                        @elseif($comment['action_type'] === 'field_change')
                                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">
                                                Cambio de Datos
                                            </span>
                                        @elseif($comment['action_type'] === 'status_change')
                                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium text-purple-800 bg-purple-100 rounded-full">
                                                Cambio de Estado
                                            </span>
                                        @elseif($comment['action_type'] === 'record_create')
                                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                                                Creación
                                            </span>
                                        @elseif($comment['action_type'] === 'porth_sync')
                                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium text-sky-800 bg-sky-100 rounded-full">
                                                Actualización Porth
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium text-gray-800 bg-gray-100 rounded-full">
                                                Otro
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $comment['comment'] }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if(!empty($comment['attachment']))
                                            <div class="space-y-1">
                                                <a href="{{ $comment['attachment']['url'] }}" target="_blank"
                                                   class="block text-blue-600 hover:text-blue-800">
                                                    {{ $comment['attachment']['name'] }}
                                                    @if($comment['attachment']['is_pending'])
                                                        <span class="text-xs text-orange-600">(pendiente de aprobación)</span>
                                                    @endif
                                                </a>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm whitespace-nowrap">
                                        @if($comment['has_changes'] ?? false)
                                            <button wire:click="$dispatchTo('partials.activity-detail-modal', 'openActivityDetail', @js($comment))"
                                                    class="text-[#1AAD8A] hover:text-[#0F614D] hover:underline">
                                                Ver cambios
                                            </button>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-sm text-center text-gray-500">
                                        No hay comentarios registrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="modal-comment-document" maxWidth="lg">
        <h3 class="mb-2 text-lg font-bold text-center text-light-blue">
            Agregar comentario
        </h3>

        <div class="mb-8">
            <x-form-textarea
                label=""
                name="comment"
                wire:model.live="comment"
                placeholder="Comentarios"
            />
        </div>

        <div class="mb-12 space-y-2">
            <div class="space-y-4">
                <div class="flex flex-col gap-4 items-start">
                    <input
                        type="file"
                        wire:model.live="attachment"
                        class="hidden"
                        x-ref="fileInput"
                        id="file-upload-comment"
                        x-bind:disabled="!$wire.comment || $wire.comment.trim() === ''"
                    >
                    <x-secondary-button
                        onclick="document.getElementById('file-upload-comment').click()"
                        class="group flex w-full items-center justify-center gap-[0.625rem]"
                        x-bind:disabled="!$wire.comment || $wire.comment.trim() === ''"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="21" height="22" viewBox="0 0 21 22"
                            fill="none">
                            <path
                                d="M19.1525 9.89897L10.1369 18.9146C8.08662 20.9648 4.7625 20.9648 2.71225 18.9146C0.661997 16.8643 0.661998 13.5402 2.71225 11.49L11.7279 2.47435C13.0947 1.10751 15.3108 1.10751 16.6776 2.47434C18.0444 3.84118 18.0444 6.05726 16.6776 7.42409L8.01555 16.0862C7.33213 16.7696 6.22409 16.7696 5.54068 16.0862C4.85726 15.4027 4.85726 14.2947 5.54068 13.6113L13.1421 6.00988"
                                stroke="#1AAD8A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="transition-colors duration-500 group-hover:stroke-dark-blue group-active:stroke-neutral-blue group-disabled:stroke-[#C2C2C2]" />
                        </svg>

                        <span>Adjuntar documentación...</span>
                    </x-secondary-button>

                    @if($attachment)
                        <div class="text-sm text-gray-600">
                            Archivo seleccionado: {{ is_object($attachment) ? $attachment->getClientOriginalName() : $attachment['name'] ?? 'Archivo' }}
                        </div>
                    @endif

                    <div class="flex flex-col text-sm text-[#A5A3A3]">
                        <span>Tipo de formato .xls .xlsx .pdf</span>
                        <span>Tamaño máximo 5MB</span>
                    </div>
                </div>

                @error('attachment')
                    <span class="text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="flex gap-[1.875rem]">
            <x-secondary-button
                x-on:click="$dispatch('close-modal', 'modal-comment-document')"
                class="w-full"
            >
                Cancelar
            </x-secondary-button>

            <x-primary-button
                wire:click="setComments"
                x-on:click="$dispatch('close-modal', 'modal-comment-document')"
                class="w-full"
            >
                Guardar
            </x-primary-button>
        </div>
    </x-modal>

    @livewire('partials.activity-detail-modal')
</div>
</div>
