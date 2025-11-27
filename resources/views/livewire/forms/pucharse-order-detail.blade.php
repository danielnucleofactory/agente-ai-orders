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
        <div class="flex justify-end gap-2 mb-2">
            <button @click="expandAll()" class="text-sm text-[#1AAD8A] hover:text-[#127A62] font-medium">
                Expandir todo
            </button>
            <span class="text-gray-300">|</span>
            <button @click="collapseAll()" class="text-sm text-[#1AAD8A] hover:text-[#127A62] font-medium">
                Contraer todo
            </button>
        </div>
        {{-- Datos generales --}}
        <div class="border border-gray-200 rounded-lg">
            <button @click="datosGenerales = !datosGenerales" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Datos generales</h2>
                <svg :class="{ 'rotate-180': datosGenerales }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="datosGenerales" x-collapse class="px-4 pb-4">

            {{-- Identificación de la OC --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Identificación de la OC</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Número de Orden (PO)</p>
                    <p class="font-semibold">{{ $purchaseOrder->order_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Fecha emisión PO</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->emision_date_po) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Fecha de creación en Next</p>
                    <p class="font-semibold">{{ $purchaseOrder->created_at ? formatDate($purchaseOrder->created_at) : '-' }}</p>
                </div>
            </div>

            {{-- Condiciones comerciales --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Condiciones comerciales</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Moneda</p>
                    <p class="font-semibold">{{ $purchaseOrder->currency ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Incoterm Precios</p>
                    <p class="font-semibold">{{ $purchaseOrder->price_incoterm ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Incoterm de Compra</p>
                    <p class="font-semibold">{{ $purchaseOrder->incoterms ?? '-' }}</p>
                </div>
            </div>

            {{-- Planificación logística --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Planificación logística</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Incoterm logístico</p>
                    <p class="font-semibold">{{ $purchaseOrder->logistics_incoterm ?? '-' }}</p>
                </div>
            </div>

            {{-- Clasificación --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Clasificación</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Categoría</p>
                    <p class="font-semibold">{{ $purchaseOrder->category ?? '-' }}</p>
                </div>
            </div>

            {{-- Notas / Motivo --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Notas / Motivo</h4>
            <div class="grid grid-cols-1 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 mb-1">Motivo</p>
                    <p class="font-semibold">{{ $purchaseOrder->reason ?? '-' }}</p>
                </div>
            </div>
            </div>
        </div>

        {{-- Identificadores y transporte --}}
        <div class="border border-gray-200 rounded-lg">
            <button @click="identificadores = !identificadores" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Identificadores y transporte</h2>
                <svg :class="{ 'rotate-180': identificadores }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="identificadores" x-collapse class="px-4 pb-4">

            {{-- Itinerario --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Itinerario</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Puerto de Embarque</p>
                    <p class="font-semibold">{{ $purchaseOrder->departure_port ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Puerto de Embarque Validado</p>
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
                    <p class="text-gray-500 mb-1">Puerto de Arribo</p>
                    <p class="font-semibold">{{ $purchaseOrder->arrival_port ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Línea Naviera</p>
                    <p class="font-semibold">{{ $purchaseOrder->shipping_line ?? '-' }}</p>
                </div>
            </div>

            {{-- Naviera y equipo --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Naviera y equipo</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Tipo de Contenedor</p>
                    <p class="font-semibold">{{ $purchaseOrder->container_type ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Número de Contenedor</p>
                    <p class="font-semibold">{{ $purchaseOrder->container_number ?? '-' }}</p>
                </div>
            </div>

            {{-- Identificadores de embarque --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Identificadores de embarque</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 mb-1">MBL Number</p>
                    <p class="font-semibold">{{ $purchaseOrder->mbl_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Proforma de Fábrica</p>
                    <p class="font-semibold">{{ $purchaseOrder->factory_proforma_number ?? '-' }}</p>
                </div>
            </div>
            </div>
        </div>

        {{-- Datos Proveedor --}}
        <div class="border border-gray-200 rounded-lg">
            <button @click="proveedor = !proveedor" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Datos Proveedor</h2>
                <svg :class="{ 'rotate-180': proveedor }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="proveedor" x-collapse class="px-4 pb-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 mb-1">Nombre del Proveedor</p>
                    <p class="font-semibold">{{ $purchaseOrder->vendor->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Código de Proveedor</p>
                    <p class="font-semibold">{{ $purchaseOrder->vendor->vendo_code ?? '-' }}</p>
                </div>
            </div>
            </div>
        </div>

        {{-- Dimensiones --}}
        <div class="border border-gray-200 rounded-lg">
            <button @click="dimensiones = !dimensiones" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Dimensiones</h2>
                <svg :class="{ 'rotate-180': dimensiones }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="dimensiones" x-collapse class="px-4 pb-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 mb-1">CBM (m³)</p>
                    <p class="font-semibold">{{ number_format($purchaseOrder->cbm ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Peso (kg)</p>
                    <p class="font-semibold">{{ number_format($purchaseOrder->weight_kg ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Peso (lb)</p>
                    <p class="font-semibold">{{ number_format($purchaseOrder->weight_lb ?? 0, 2) }}</p>
                </div>
            </div>
            </div>
        </div>

        {{-- Fechas --}}
        <div class="border border-gray-200 rounded-lg">
            <button @click="fechas = !fechas" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Fechas</h2>
                <svg :class="{ 'rotate-180': fechas }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="fechas" x-collapse class="px-4 pb-4">

            {{-- Booking y coordinación --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Booking y coordinación</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Solicitud de Booking</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_booking_request) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Autorización Booking</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_booking_authorized) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Fecha de asignación de agente de carga</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->forwader_date) }}</p>
                </div>
            </div>

            {{-- Origen: preparación y carga --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Origen: preparación y carga</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Fecha Inspección</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->inspection_date) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Fecha Corte VGM</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->vgm_cut_date) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Fecha Carga Lista Teórica</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_theorical_load) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Fecha Carga Lista Variable</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_variable_date) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Fecha Carga Lista Real</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_carga_po) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Fecha de consolidado</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_consolidation) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Fecha de release</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->release_date) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Diferencia de fecha de carga lista</p>
                    <p class="font-semibold">{{ $this->calculateLoadDateDifference($purchaseOrder) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Nombre del Consolidador</p>
                    <p class="font-semibold">{{ $purchaseOrder->consolidator_name ?? '-' }}</p>
                </div>
            </div>

            {{-- Salida (origen) --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Salida (origen)</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">ETD Inicial</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_etd_initial) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">ETD Inicial Validada</p>
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
                    <p class="text-gray-500 mb-1">ETD</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_etd) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">ATD</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_atd) }}</p>
                </div>
            </div>

            {{-- Arribo a destino --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Arribo a destino</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">ETA</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_eta) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">ETA Inicial</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_eta_updated) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">ATA</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_ata) }}</p>
                </div>
            </div>

            {{-- Almacén fiscal y recepción --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Almacén fiscal y recepción</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Ingreso Almacén Fiscal</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->bonded_warehouse_enter) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Salida Almacén Fiscal</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->bonded_warehouse_exit) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Fecha Nota de Recibo</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->receipt_note_date) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Fecha Disp. Bogeda Estimada</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->estimated_dc_availability_date) }}</p>
                </div>
            </div>

            {{-- Pagos y cargos --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Pagos y cargos</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Fecha Pago Balance</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->balance_payment_date) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Fecha Pago Cargos Locales</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->local_charges_payment_date) }}</p>
                </div>
            </div>

            {{-- Métricas y varios --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Métricas y varios</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 mb-1">Días Libres Contenedor</p>
                    <p class="font-semibold">{{ $purchaseOrder->container_free_days ?? '0' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Dif Fechas ETD (días)</p>
                    <p class="font-semibold">{{ $purchaseOrder->etd_dates_difference ?? '0' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Dif Fechas ETA (días)</p>
                    <p class="font-semibold">{{ $purchaseOrder->eta_dates_difference ?? '0' }}</p>
                </div>
            </div>
            </div>
        </div>

        {{-- Información Adicional --}}
        <div class="border border-gray-200 rounded-lg">
            <button @click="infoAdicional = !infoAdicional" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Información Adicional</h2>
                <svg :class="{ 'rotate-180': infoAdicional }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="infoAdicional" x-collapse class="px-4 pb-4">

            {{-- Configuración del envío --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Configuración del envío</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Tipo de Transporte</p>
                    <p class="font-semibold">{{ ucfirst($purchaseOrder->mode ?? '-') }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Número de Booking</p>
                    <p class="font-semibold">{{ $purchaseOrder->tracking_id ?? '-' }}</p>
                </div>
            </div>

            {{-- Opciones --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Opciones</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm mb-6">
                <div class="flex items-center">
                    <input type="checkbox" {{ ($purchaseOrder->applies_tlc ?? false) ? 'checked' : '' }} disabled
                           class="w-4 h-4 text-[#1AAD8A] rounded border-gray-300 focus:ring-[#1AAD8A] opacity-75">
                    <label class="block ml-2 text-sm text-gray-700">Aplica TLC</label>
                </div>
                <div class="flex items-center hidden">
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Cantidad estimada de pallets</p>
                    <p class="font-semibold">{{ $purchaseOrder->pallet_quantity ?? '0' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Cantidad Real de Pallets</p>
                    <p class="font-semibold">{{ $purchaseOrder->pallet_quantity_real ?? '0' }}</p>
                </div>
            </div>

            {{-- Costos base --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Costos base</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Monto Factura</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->Invoice_amount ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Monto Flete</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->freight_amount ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Otros Gastos</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->other_expenses ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Monto Total</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->total_amount ?? 0, 2) }}</p>
                </div>
            </div>

            {{-- Totales y cálculos --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Totales y cálculos</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 mb-1">Costo Estimado de Pallets</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->estimated_pallet_cost ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Costo Total Estimado PO</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->real_cost_estimated_po ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Costo Real PO</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->real_cost_real_po ?? 0, 2) }}</p>
                </div>
            </div>
            </div>
        </div>

        {{-- Datos de negocio --}}
        <div class="border border-gray-200 rounded-lg">
            <button @click="datosNegocio = !datosNegocio" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Datos de negocio</h2>
                <svg :class="{ 'rotate-180': datosNegocio }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="datosNegocio" x-collapse class="px-4 pb-4">

            {{-- Proveedores y contratación --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Proveedores y contratación</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Agente de Carga</p>
                    <p class="font-semibold">{{ $purchaseOrder->forwarder_name ?? '-' }}</p>
                </div>
                <div class="hidden">
                    <p class="text-gray-500 mb-1">Proveedor de Servicio</p>
                    <p class="font-semibold">{{ $purchaseOrder->service_provider ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Cliente</p>
                    <p class="font-semibold">{{ $purchaseOrder->trading_company ?? '-' }}</p>
                </div>
            </div>

            {{-- Tarifas y ruta --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Tarifas y ruta</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Tipo Tarifa</p>
                    <p class="font-semibold">{{ $purchaseOrder->tariff_type ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Ruta Logística</p>
                    <p class="font-semibold">{{ $purchaseOrder->route_label ?? '-' }}</p>
                </div>
            </div>

            {{-- Segmento / cliente --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Segmento / cliente</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Grupo Repositor</p>
                    <p class="font-semibold">{{ $purchaseOrder->retail_group ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Tipo Cliente</p>
                    <p class="font-semibold">{{ $purchaseOrder->customer_type ?? '-' }}</p>
                </div>
            </div>

            {{-- Documentos y referencias --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Documentos y referencias</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <p class="text-gray-500 mb-1">Factura</p>
                    <p class="font-semibold">{{ $purchaseOrder->invoice ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Fecha recepción de factura</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_invoice_received) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Fecha recepción doc. proveedor</p>
                    <p class="font-semibold">{{ formatDate($purchaseOrder->date_vendor_document_received) }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Factura Flete</p>
                    <p class="font-semibold">{{ $purchaseOrder->cargo_invoice_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Factura Mercancía</p>
                    <p class="font-semibold">{{ $purchaseOrder->factura_merca ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">DUA Internamiento</p>
                    <p class="font-semibold">{{ $purchaseOrder->customs_dua ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Expediente</p>
                    <p class="font-semibold">{{ $purchaseOrder->case_number_file ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Nota de Recibo</p>
                    <p class="font-semibold">{{ $purchaseOrder->receipt_note ?? '-' }}</p>
                </div>
            </div>

            {{-- Notas --}}
            <h4 class="text-sm font-semibold text-[#1AAD8A] mb-3">Notas</h4>
            <div class="grid grid-cols-1 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 mb-1">Notas de Visibilidad</p>
                    <p class="font-semibold">{{ $purchaseOrder->visibility_notes ?? '-' }}</p>
                </div>
            </div>
            </div>
        </div>

        {{-- Estado de llegada --}}
        <div class="border border-gray-200 rounded-lg">
            <button @click="estadoLlegada = !estadoLlegada" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                <h2 class="text-lg font-bold text-[#1AAD8A]">Estado de llegada</h2>
                <svg :class="{ 'rotate-180': estadoLlegada }" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="estadoLlegada" x-collapse class="px-4 pb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 mb-1">Estado</p>
                    <p class="font-semibold">{{ $purchaseOrder->arrival_status ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Días de retraso</p>
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
                    <button wire:click="openCommentModal" class="flex items-center gap-2 text-sm text-gray-600 hover:text-[#0F614D]">
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
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Fecha</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Usuario</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Tipo</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Comentario</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Archivos</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Acciones</th>
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
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Comentario
                                            </span>
                                        @elseif($comment['action_type'] === 'field_change')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Cambio de Datos
                                            </span>
                                        @elseif($comment['action_type'] === 'status_change')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                Cambio de Estado
                                            </span>
                                        @elseif($comment['action_type'] === 'record_create')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Creación
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
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
                                                   class="text-blue-600 hover:text-blue-800 block">
                                                    {{ $comment['attachment']['name'] }}
                                                    @if($comment['attachment']['is_pending'])
                                                        <span class="text-orange-600 text-xs">(pendiente de aprobación)</span>
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
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
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

    @livewire('partials.activity-detail-modal')
</div>
</div>