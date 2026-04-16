@props([
    'po' => '12345a',
    'trackingId' => '11111',
    'hubLocation' => 'New Jersey',
    'leadTime' => '01/01/2024',
    'recolectaTime' => '11/11/2024',
    'pickupTime' => '11/11/24',
    'totalWeight' => '10 Ton',
    'id' => null
])

@php
    use App\Models\PurchaseOrder;
    use App\Services\TrackingService;

    // Trae también las anuladas (soft-deleted)
    // NO cargar shippingDocuments aquí para evitar consultas N+1 en el kanban
    $purchaseOrder = PurchaseOrder::withTrashed()
        ->with(['actualHub', 'vendor'])
        ->find($id);

    $isTrashed = $purchaseOrder?->trashed() ?? false;

    // Fechas ETD/ETA/ATA/ATD: calendario según valor guardado (sin TZ del perfil de usuario)
    $fmt = function ($date) {
        return $date ? formatDateOnly($date) : null;
    };

    // Valores seguros
    $hubId          = $purchaseOrder?->actual_hub_id;
    $hub            = $purchaseOrder?->actualHub?->name ?? 'Sin Hub';

    $dangerLevel    = $purchaseOrder?->material_type ?? null;
    $materialTypeRaw = $purchaseOrder?->material_type ?? '';
    $materialType    = strtolower(trim((string) $materialTypeRaw));
    $trackingIdCode = $purchaseOrder?->tracking_id ?? 'N/A';

    $eta            = $fmt($purchaseOrder?->date_eta);
    $ata            = $fmt($purchaseOrder?->date_ata);
    $atd            = $fmt($purchaseOrder?->date_atd);
    $etaEstimado    = $fmt($purchaseOrder?->date_eta_initial ?? $purchaseOrder?->date_eta);
    $puertoDestino  = $purchaseOrder?->arrival_port ?? 'N/A';
    $proveedorServicio = $purchaseOrder?->service_provider ?? 'N/A';
    $cliente        = $purchaseOrder?->trading_company ?? 'N/A';

    // Obtener mbl_number y container_number directamente de la PO
    // No cargar shippingDocuments para evitar consultas N+1 en el kanban
    $mblNumber = $purchaseOrder?->mbl_number ?? null;
    $containerNumber = $purchaseOrder?->container_number ?? null;

    // NO cargar datos de tracking en el kanban - esto causa timeouts
    // La línea de tiempo se mostrará solo si hay datos disponibles sin hacer llamadas HTTP
    // Los datos de tracking se cargarán de forma lazy/asíncrona cuando sea necesario
    $kanbanStatusId = $purchaseOrder?->kanban_status_id ?? 0;
    $shouldShowTimeline = false;
    $trackingData = null;
    // La línea de tiempo se mostrará solo si hay datos disponibles sin hacer llamadas HTTP
    // Los datos de tracking se cargarán de forma lazy/asíncrona cuando sea necesario
    $shouldShowTimeline = false;
    $trackingData = null;

    // Cálculos con guardas (mantenidos para posibles usos futuros)
    $delayDays = ($purchaseOrder?->date_eta && $purchaseOrder?->date_ata)
        ? \Carbon\Carbon::parse($purchaseOrder->date_eta)
            ->diffInDays(\Carbon\Carbon::parse($purchaseOrder->date_ata), false) // ATA - ETA (con signo)
        : null;
@endphp


<li class="kanban-card relative flex flex-col min-h-[180px] w-full rounded-[0.625rem] border-2 border-[#D4F5ED] bg-white px-4 py-2 text-xs"
    x-data x-init="$el.addEventListener('click', () => {
        window.selectedTaskId = '{{ $trackingId }}';
        console.log('Card clicked, set ID:', window.selectedTaskId);
        document.dispatchEvent(new CustomEvent('card-selected', { detail: { id: '{{ $trackingId }}' } }));
    });" data-task-id="{{ $trackingId }}">

    {{-- Primer tercio (33%): Número de PO e ID Tracking --}}
    <div class="flex-shrink-0 h-1/3 flex items-center justify-between border-b border-[#D4F5ED] pb-2 mb-2">
        <div class="space-y-1 text-sm w-full">
            <p>
                @if (!$isTrashed)
                    <a class="text-[#127A62] underline underline-offset-4 font-semibold"
                       href="/purchase-orders/{{ $trackingId }}/detail">
                        PO: {{ $po }}
                    </a>
                @else
                    <span class="text-gray-400 cursor-not-allowed select-none font-semibold"
                          title="PO anulada: detalle bloqueado">
                        PO: {{ $po }}
                    </span>
                @endif
            </p>
            @if($mblNumber || $containerNumber || ($trackingIdCode && $trackingIdCode !== 'N/A'))
                <div class="text-xs text-gray-600 space-y-0.5">
                    @if($mblNumber)
                        <p>Documento de tránsito: {{ $mblNumber }}</p>
                    @endif
                    @if($containerNumber)
                        <p>Contenedor: {{ $containerNumber }}</p>
                    @endif
                    @if($trackingIdCode && $trackingIdCode !== 'N/A')
                        <p>Booking: {{ $trackingIdCode }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Dos tercios restantes (67%): Campos adicionales --}}
    <div class="flex-1 flex flex-col justify-between min-h-0">
        <div class="flex gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="20" viewBox="0 0 18 20" fill="none" class="flex-shrink-0">
                <path
                    d="M5.625 10.25C5.32663 10.25 5.04048 10.3685 4.8295 10.5795C4.61853 10.7905 4.5 11.0766 4.5 11.375C4.5 11.6734 4.61853 11.9595 4.8295 12.1705C5.04048 12.3815 5.32663 12.5 5.625 12.5C5.92337 12.5 6.20952 12.3815 6.4205 12.1705C6.63147 11.9595 6.75 11.6734 6.75 11.375C6.75 11.0766 6.63147 10.7905 6.4205 10.5795C6.20952 10.3685 5.92337 10.25 5.625 10.25ZM7.875 11.375C7.875 11.0766 7.99353 10.7905 8.2045 10.5795C8.41548 10.3685 8.70163 10.25 9 10.25H12.375C12.6734 10.25 12.9595 10.3685 13.1705 10.5795C13.3815 10.7905 13.5 11.0766 13.5 11.375C13.5 11.6734 13.3815 11.9595 13.1705 12.1705C12.9595 12.3815 12.6734 12.5 12.375 12.5H9C8.70163 12.5 8.41548 12.3815 8.2045 12.1705C7.99353 11.9595 7.875 11.6734 7.875 11.375ZM5.625 13.25C5.32663 13.25 5.04048 13.3685 4.8295 13.5795C4.61853 13.7905 4.5 14.0766 4.5 14.375C4.5 14.6734 4.61853 14.9595 4.8295 15.1705C5.04048 15.3815 5.32663 15.5 5.625 15.5H9C9.29837 15.5 9.58452 15.3815 9.79549 15.1705C10.0065 14.9595 10.125 14.6734 10.125 14.375C10.125 14.0766 10.0065 13.7905 9.79549 13.5795C9.58452 13.3685 9.29837 13.25 9 13.25H5.625Z"
                    fill="black" />
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M4.125 0.5C3.82663 0.5 3.54048 0.618526 3.3295 0.829505C3.11853 1.04048 3 1.32663 3 1.625V3.5C2.20435 3.5 1.44129 3.81607 0.87868 4.37868C0.31607 4.94129 0 5.70435 0 6.5V17C0 17.7956 0.31607 18.5587 0.87868 19.1213C1.44129 19.6839 2.20435 20 3 20H15C15.7956 20 16.5587 19.6839 17.1213 19.1213C17.6839 18.5587 18 17.7956 18 17V6.5C18 5.70435 17.6839 4.94129 17.1213 4.37868C16.5587 3.81607 15.7956 3.5 15 3.5V1.625C15 1.32663 14.8815 1.04048 14.6705 0.829505C14.4595 0.618526 14.1734 0.5 13.875 0.5C13.5766 0.5 13.2905 0.618526 13.0795 0.829505C12.8685 1.04048 12.75 1.32663 12.75 1.625V3.5H5.25V1.625C5.25 1.32663 5.13147 1.04048 4.9205 0.829505C4.70952 0.618526 4.42337 0.5 4.125 0.5ZM2.25 9.5C2.25 9.10218 2.40804 8.72064 2.68934 8.43934C2.97064 8.15804 3.35218 8 3.75 8H14.25C14.6478 8 15.0294 8.15804 15.3107 8.43934C15.592 8.72064 15.75 9.10218 15.75 9.5V16.25C15.75 16.6478 15.592 17.0294 15.3107 17.3107C15.0294 17.592 14.6478 17.75 14.25 17.75H3.75C3.35218 17.75 2.97064 17.592 2.68934 17.3107C2.40804 17.0294 2.25 16.6478 2.25 16.25V9.5Z"
                    fill="black" />
            </svg>

            <div class="space-y-1 flex-1">
                <p class="whitespace-nowrap">ATD: <span>{{ $atd ?? 'N/A' }}</span></p>
                <p class="whitespace-nowrap">ETA Estimado: <span>{{ $etaEstimado ?? 'N/A' }}</span></p>
                <p class="whitespace-nowrap">Puerto destino: <span>{{ $puertoDestino }}</span></p>
                <p class="whitespace-nowrap">Proveedor de Servicio: <span>{{ $proveedorServicio }}</span></p>
                <p class="whitespace-nowrap">Cliente: <span>{{ $cliente }}</span></p>
            </div>
        </div>

        @if($shouldShowTimeline && $trackingData && isset($trackingData['timeline']) && count($trackingData['timeline']) > 0)
            {{-- Línea de tiempo compacta --}}
            <div class="mb-2 mt-2 pt-2 border-t border-gray-200">
                <p class="mb-1 text-xs font-semibold text-[#127A62]">Estado del Envío</p>
                <div class="relative">
                    {{-- Timeline track --}}
                    <div class="absolute h-[2px] top-2 left-0 right-0 flex">
                        @php
                            $completedPhases = collect($trackingData['timeline'])->where('is_completed', true)->count();
                            $totalPhases = count($trackingData['timeline']);
                            $completedWidth = $totalPhases > 0 ? ($completedPhases / $totalPhases) * 100 : 0;
                        @endphp
                        <div class="bg-[#127A62]" style="width: {{ $completedWidth }}%"></div>
                        <div class="bg-gray-200" style="width: {{ 100 - $completedWidth }}%"></div>
                    </div>

                    {{-- Timeline events compactos --}}
                    <div class="flex relative justify-between mt-1">
                        @foreach($trackingData['timeline'] as $phase)
                            <div class="flex flex-col items-center" style="flex: 1;">
                                {{-- Status dot --}}
                                <div class="z-20 flex items-center justify-center w-6 h-6 mb-1 rounded-full transition-all duration-300
                                    {{ $phase['is_completed'] ? 'bg-[#127A62]' : ($phase['is_current'] ? 'bg-[#127A62]' : 'bg-gray-200') }}">
                                    @if($phase['is_completed'] || $phase['is_current'])
                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </div>
                                {{-- Phase name (solo mostrar si está completada o es actual) --}}
                                @if($phase['is_completed'] || $phase['is_current'])
                                    <p class="text-[8px] text-center text-[#127A62] font-medium leading-tight mt-0.5" style="max-width: 40px;">
                                        {{ strlen($phase['name']) > 12 ? substr($phase['name'], 0, 12) . '...' : $phase['name'] }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @if(isset($trackingData['current_phase']))
                    <p class="mt-1 text-[10px] text-gray-600">
                        Fase actual: {{ $trackingData['current_phase'] }}
                    </p>
                @endif
            </div>
        @endif

    </div>
</li>
