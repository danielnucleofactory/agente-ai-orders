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
    $purchaseOrder = App\Models\PurchaseOrder::find($id);
    $hubId = $purchaseOrder->actual_hub_id;
    $hub =  $purchaseOrder->actualHub->name ?? 'Sin Hub';
    $leadTime = \Carbon\Carbon::parse($purchaseOrder->date_required_in_destination)->format('d/m/Y');
    $recolectaTime = \Carbon\Carbon::parse($purchaseOrder->date_estimated_hub_arrival)->format('d/m/Y');
    $pickupTime = \Carbon\Carbon::parse($purchaseOrder->date_planned_pickup)->format('d/m/Y');
    $totalWeight = $purchaseOrder->total_weight;
    $dangerLevel = $purchaseOrder->material_type;
    $materialType = $purchaseOrder->material_type;
    $trackingIdCode = $purchaseOrder->tracking_id ?? 'N/A';

    // Calcular expectedLeadTime = date_required_in_destination - date_planned_pickup (en días)
    $expectedLeadTime = 0;
    if ($purchaseOrder->date_required_in_destination && $purchaseOrder->date_planned_pickup) {
        $dateRequired = \Carbon\Carbon::parse($purchaseOrder->date_required_in_destination);
        $datePlannedPickup = \Carbon\Carbon::parse($purchaseOrder->date_planned_pickup);
        $expectedLeadTime = $datePlannedPickup->diffInDays($dateRequired);
    }

    $eta = \Carbon\Carbon::parse($purchaseOrder->date_eta)->format('d/m/Y');
    $ata = \Carbon\Carbon::parse($purchaseOrder->date_ata)->format('d/m/Y');

    // Calcular realLeadTime (Lead en transito) = ETA - pickup real (en días)
    $realLeadTime = 0;
    if ($purchaseOrder->date_eta && $purchaseOrder->date_actual_pickup) {
        $etaDate = \Carbon\Carbon::parse($purchaseOrder->date_eta);
        $actualPickupDate = \Carbon\Carbon::parse($purchaseOrder->date_actual_pickup);
        $realLeadTime = $actualPickupDate->diffInDays($etaDate);
    }

    // Calcular días de atraso (ATA - ETA) para referencia si se necesita
    $delayDays = 0;
    if ($purchaseOrder->date_eta && $purchaseOrder->date_ata) {
        $etaDate = \Carbon\Carbon::parse($purchaseOrder->date_eta);
        $ataDate = \Carbon\Carbon::parse($purchaseOrder->date_ata);
        $delayDays = $etaDate->diffInDays($ataDate, false); // ATA - ETA (invertir parámetros)
    }

    // Calcular actualLeadTime (Lead time real) = ATA - fecha pick planificada (en días)
    $actualLeadTime = 0;
    if ($purchaseOrder->date_ata && $purchaseOrder->date_planned_pickup) {
        $ataDate = \Carbon\Carbon::parse($purchaseOrder->date_ata);
        $plannedPickupDate = \Carbon\Carbon::parse($purchaseOrder->date_planned_pickup);
        $actualLeadTime = $plannedPickupDate->diffInDays($ataDate);
    }

@endphp

<li class="kanban-card relative flex justify-between min-h-[180px] w-full gap-5 rounded-[0.625rem] border-2 border-[#E0E5FF] bg-white px-4 py-2 text-xs"
    x-data x-init="$el.addEventListener('click', () => {
        window.selectedTaskId = '{{ $trackingId }}';
        console.log('Card clicked, set ID:', window.selectedTaskId);
        document.dispatchEvent(new CustomEvent('card-selected', { detail: { id: '{{ $trackingId }}' } }));
    });" data-task-id="{{ $trackingId }}">

    <div class="flex grow flex-col space-y-[0.875rem]">
        <div class="flex gap-4">
            <div class="space-y-1 text-sm">
                <p>
                    <a class="text-[#190FDB] underline underline-offset-4" href="/purchase-orders/{{ $trackingId }}/detail">
                        PO: {{ $po }}
                    </a>
                </p>
                <p>ID Tracking: {{ $trackingIdCode }}</p>
            </div>
        </div>

        <div class="flex flex-col justify-between space-y-[0.875rem]"
            @if (str_contains($materialType, 'dangerous'))
            <x-label class="bg-danger">
                <span>Producto peligroso</span>

                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="18" viewBox="0 0 20 18"
                        fill="none">
                        <path
                            d="M9.99979 6.50019V9.83353M9.99979 13.1669H10.0081M8.84588 2.24329L1.99181 14.0821C1.61164 14.7388 1.42156 15.0671 1.44965 15.3366C1.47416 15.5716 1.5973 15.7852 1.78843 15.9242C2.00756 16.0835 2.38695 16.0835 3.14572 16.0835H16.8539C17.6126 16.0835 17.992 16.0835 18.2111 15.9242C18.4023 15.7852 18.5254 15.5716 18.5499 15.3366C18.578 15.0671 18.3879 14.7388 18.0078 14.0821L11.1537 2.24329C10.7749 1.58899 10.5855 1.26184 10.3384 1.15196C10.1228 1.05612 9.87675 1.05612 9.6612 1.15196C9.4141 1.26184 9.22469 1.58899 8.84588 2.24329Z"
                            stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </x-slot:icon>
            </x-label>
            @endif

            @if (str_contains($materialType, 'general'))
            <x-label class="bg-gray-500">
                <span>Producto general</span>

                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="18" viewBox="0 0 20 18"
                        fill="none">
                        <path
                            d="M9 2.25H3C2.17157 2.25 1.5 2.92157 1.5 3.75V14.25C1.5 15.0784 2.17157 15.75 3 15.75H17C17.8284 15.75 18.5 15.0784 18.5 14.25V7.5C18.5 6.67157 17.8284 6 17 6H10.5L9 2.25Z"
                            stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </x-slot:icon>
            </x-label>
            @endif

            @if (str_contains($materialType, 'estibable'))
            <x-label class="bg-success">
                <span>Producto estibable</span>

                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="18" viewBox="0 0 20 18"
                        fill="none">
                        <path
                            d="M1 13.5L1 14.25C1 15.4926 2.00736 16.5 3.25 16.5H16.75C17.9926 16.5 19 15.4926 19 14.25V13.5M14.5 9L10 13.5M10 13.5L5.5 9M10 13.5V1.5"
                            stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </x-slot:icon>
            </x-label>
            @endif

            @if (str_contains($materialType, 'exclusive'))
            <x-label class="bg-warning">
                <span>Producto exclusivo</span>

                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="18" viewBox="0 0 20 18"
                        fill="none">
                        <path
                            d="M6 16.5V8.25M6 8.25V1.5L14 8.25L10 10.5L6 8.25Z"
                            stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </x-slot:icon>
            </x-label>
            @endif

            <x-label class="bg-[#E0E5FF] py-[0.625rem] text-neutral-blue">
                <p class="text-base">Hub: <span>{{ $hub }}</span></p>

                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="19" viewBox="0 0 18 19"
                        fill="none">
                        <path
                            d="M5.66667 14.1663H12.3333M8.18141 2.30297L2.52949 6.6989C2.15168 6.99275 1.96278 7.13968 1.82669 7.32368C1.70614 7.48667 1.61633 7.67029 1.56169 7.86551C1.5 8.0859 1.5 8.32521 1.5 8.80384V14.833C1.5 15.7664 1.5 16.2331 1.68166 16.5896C1.84144 16.9032 2.09641 17.1582 2.41002 17.318C2.76654 17.4996 3.23325 17.4996 4.16667 17.4996H13.8333C14.7668 17.4996 15.2335 17.4996 15.59 17.318C15.9036 17.1582 16.1586 16.9032 16.3183 16.5896C16.5 16.2331 16.5 15.7664 16.5 14.833V8.80384C16.5 8.32521 16.5 8.0859 16.4383 7.86551C16.3837 7.67029 16.2939 7.48667 16.1733 7.32368C16.0372 7.13968 15.8483 6.99275 15.4705 6.69891L9.81859 2.30297C9.52582 2.07526 9.37943 1.9614 9.21779 1.91763C9.07516 1.87902 8.92484 1.87902 8.78221 1.91763C8.62057 1.9614 8.47418 2.07526 8.18141 2.30297Z"
                            stroke="#7288FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </x-slot:icon>
            </x-label>
        </div>
    </div>

    <div class="flex flex-col justify-between">
        <div class="flex gap-2 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="20" viewBox="0 0 18 20" fill="none">
                <path
                    d="M5.625 10.25C5.32663 10.25 5.04048 10.3685 4.8295 10.5795C4.61853 10.7905 4.5 11.0766 4.5 11.375C4.5 11.6734 4.61853 11.9595 4.8295 12.1705C5.04048 12.3815 5.32663 12.5 5.625 12.5C5.92337 12.5 6.20952 12.3815 6.4205 12.1705C6.63147 11.9595 6.75 11.6734 6.75 11.375C6.75 11.0766 6.63147 10.7905 6.4205 10.5795C6.20952 10.3685 5.92337 10.25 5.625 10.25ZM7.875 11.375C7.875 11.0766 7.99353 10.7905 8.2045 10.5795C8.41548 10.3685 8.70163 10.25 9 10.25H12.375C12.6734 10.25 12.9595 10.3685 13.1705 10.5795C13.3815 10.7905 13.5 11.0766 13.5 11.375C13.5 11.6734 13.3815 11.9595 13.1705 12.1705C12.9595 12.3815 12.6734 12.5 12.375 12.5H9C8.70163 12.5 8.41548 12.3815 8.2045 12.1705C7.99353 11.9595 7.875 11.6734 7.875 11.375ZM5.625 13.25C5.32663 13.25 5.04048 13.3685 4.8295 13.5795C4.61853 13.7905 4.5 14.0766 4.5 14.375C4.5 14.6734 4.61853 14.9595 4.8295 15.1705C5.04048 15.3815 5.32663 15.5 5.625 15.5H9C9.29837 15.5 9.58452 15.3815 9.79549 15.1705C10.0065 14.9595 10.125 14.6734 10.125 14.375C10.125 14.0766 10.0065 13.7905 9.79549 13.5795C9.58452 13.3685 9.29837 13.25 9 13.25H5.625Z"
                    fill="black" />
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M4.125 0.5C3.82663 0.5 3.54048 0.618526 3.3295 0.829505C3.11853 1.04048 3 1.32663 3 1.625V3.5C2.20435 3.5 1.44129 3.81607 0.87868 4.37868C0.31607 4.94129 0 5.70435 0 6.5V17C0 17.7956 0.31607 18.5587 0.87868 19.1213C1.44129 19.6839 2.20435 20 3 20H15C15.7956 20 16.5587 19.6839 17.1213 19.1213C17.6839 18.5587 18 17.7956 18 17V6.5C18 5.70435 17.6839 4.94129 17.1213 4.37868C16.5587 3.81607 15.7956 3.5 15 3.5V1.625C15 1.32663 14.8815 1.04048 14.6705 0.829505C14.4595 0.618526 14.1734 0.5 13.875 0.5C13.5766 0.5 13.2905 0.618526 13.0795 0.829505C12.8685 1.04048 12.75 1.32663 12.75 1.625V3.5H5.25V1.625C5.25 1.32663 5.13147 1.04048 4.9205 0.829505C4.70952 0.618526 4.42337 0.5 4.125 0.5ZM2.25 9.5C2.25 9.10218 2.40804 8.72064 2.68934 8.43934C2.97064 8.15804 3.35218 8 3.75 8H14.25C14.6478 8 15.0294 8.15804 15.3107 8.43934C15.592 8.72064 15.75 9.10218 15.75 9.5V16.25C15.75 16.6478 15.592 17.0294 15.3107 17.3107C15.0294 17.592 14.6478 17.75 14.25 17.75H3.75C3.35218 17.75 2.97064 17.592 2.68934 17.3107C2.40804 17.0294 2.25 16.6478 2.25 16.25V9.5Z"
                    fill="black" />
            </svg>

            <div class="space-y-1">
                <p class="whitespace-nowrap">Lead requerido: <span>{{ $expectedLeadTime }}</span></p>
                <p class="whitespace-nowrap">Lead en transito: <span>{{ $realLeadTime }}</span></p>
            </div>
        </div>

        <div class="flex gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="18" viewBox="0 0 16 18" fill="none">
                <path
                    d="M2.00016 16H14.0002L12.5752 6H3.42517L2.00016 16ZM8.00016 4C8.2835 4 8.52116 3.904 8.71317 3.712C8.90517 3.52 9.00083 3.28267 9.00016 3C8.9995 2.71733 8.9035 2.48 8.71216 2.288C8.52083 2.096 8.2835 2 8.00016 2C7.71683 2 7.4795 2.096 7.28817 2.288C7.09683 2.48 7.00083 2.71733 7.00016 3C6.9995 3.28267 7.0955 3.52033 7.28817 3.713C7.48083 3.90567 7.71816 4.00133 8.00016 4ZM10.8252 4H12.5752C13.0752 4 13.5085 4.16667 13.8752 4.5C14.2418 4.83333 14.4668 5.24167 14.5502 5.725L15.9752 15.725C16.0585 16.325 15.9045 16.8543 15.5132 17.313C15.1218 17.7717 14.6175 18.0007 14.0002 18H2.00016C1.3835 18 0.879165 17.771 0.487165 17.313C0.0951649 16.855 -0.058835 16.3257 0.025165 15.725L1.45016 5.725C1.5335 5.24167 1.7585 4.83333 2.12516 4.5C2.49183 4.16667 2.92517 4 3.42517 4H5.17517C5.12516 3.83333 5.0835 3.671 5.05017 3.513C5.01683 3.355 5.00016 3.184 5.00016 3C5.00016 2.16667 5.29183 1.45833 5.87516 0.875C6.4585 0.291667 7.16683 0 8.00016 0C8.8335 0 9.54183 0.291667 10.1252 0.875C10.7085 1.45833 11.0002 2.16667 11.0002 3C11.0002 3.18333 10.9835 3.35433 10.9502 3.513C10.9168 3.67167 10.8752 3.834 10.8252 4Z"
                    fill="black" />
            </svg>

            <div>
                <p>Peso total: <span>{{ $totalWeight }}</span></p>
            </div>
        </div>
    </div>
</li>
