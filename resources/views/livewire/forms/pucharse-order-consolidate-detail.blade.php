<div>
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-start gap-[3.75rem]">
            <x-view-title>
                <x-slot:title>
                    {{ $shippingDocument->document_number ?? 'Documento de embarque' }}
                </x-slot:title>

                <x-slot:content>
                    Órdenes consolidadas: {{ $poCount }}
                </x-slot:content>
            </x-view-title>

            <x-label class="hidden mt-6 bg-success">
                <span>Estado:</span>
                <span>{{ $shippingDocument->status ?? 'Pendiente' }}</span>
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path
                            d="M14.7873 2.34374C15.5913 1.51335 16.9196 1.5026 17.7369 2.31989C18.5318 3.11479 18.5466 4.39893 17.7703 5.21193L15.4547 7.63674C15.2732 7.8268 15.1825 7.92183 15.1265 8.03313C15.077 8.13166 15.0476 8.23903 15.0399 8.34903C15.0312 8.47328 15.0607 8.60131 15.1198 8.85738L16.5597 15.097C16.6204 15.3599 16.6507 15.4913 16.6409 15.6184C16.6323 15.7309 16.6008 15.8405 16.5485 15.9405C16.4895 16.0535 16.3941 16.1489 16.2033 16.3396L15.8944 16.6486C15.3892 17.1537 15.1367 17.4063 14.8782 17.452C14.6525 17.4919 14.4203 17.4371 14.2362 17.3005C14.0255 17.144 13.9125 16.8051 13.6866 16.1274L12.0118 11.103L9.22408 13.8908C9.05767 14.0572 8.97447 14.1404 8.91881 14.2384C8.86951 14.3252 8.8362 14.4202 8.82048 14.5187C8.80273 14.63 8.81572 14.747 8.84171 14.9809L8.9948 16.3587C9.02079 16.5926 9.03378 16.7095 9.01604 16.8208C9.00031 16.9194 8.967 17.0143 8.9177 17.1011C8.86204 17.1991 8.77884 17.2823 8.61243 17.4487L8.44785 17.6133C8.05363 18.0075 7.85652 18.2046 7.63748 18.2617C7.44536 18.3118 7.24167 18.2916 7.0631 18.2049C6.85951 18.1059 6.70488 17.874 6.39563 17.4101L5.0887 15.4497C5.03345 15.3668 5.00582 15.3254 4.97375 15.2878C4.94526 15.2544 4.91418 15.2233 4.8808 15.1949C4.84321 15.1628 4.80178 15.1352 4.7189 15.0799L2.7585 13.773C2.29462 13.4637 2.06269 13.3091 1.96375 13.1055C1.87698 12.9269 1.85681 12.7232 1.90688 12.5311C1.96396 12.3121 2.16107 12.115 2.55529 11.7208L2.71988 11.5562C2.88628 11.3898 2.96948 11.3066 3.06747 11.2509C3.15427 11.2016 3.24922 11.1683 3.34781 11.1526C3.45909 11.1348 3.57603 11.1478 3.80993 11.1738L5.18775 11.3269C5.42164 11.3529 5.53859 11.3659 5.64987 11.3481C5.74845 11.3324 5.84341 11.2991 5.93021 11.2498C6.02819 11.1941 6.1114 11.1109 6.2778 10.9445L9.06557 8.15676L4.04117 6.48196C3.36348 6.25606 3.02464 6.14311 2.86814 5.93236C2.73149 5.74832 2.67667 5.51613 2.7166 5.29041C2.76232 5.03192 3.01488 4.77936 3.52 4.27424L3.82897 3.96526C4.01972 3.77452 4.11509 3.67915 4.22809 3.62007C4.32809 3.56778 4.43768 3.53635 4.5502 3.52769C4.67733 3.5179 4.80875 3.54823 5.0716 3.60888L11.2875 5.04333C11.5458 5.10294 11.675 5.13274 11.7997 5.12387C11.9201 5.11531 12.0372 5.08071 12.1428 5.02244C12.2523 4.9621 12.3445 4.86687 12.5289 4.67643L14.7873 2.34374Z"
                            stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </x-slot:icon>
            </x-label>
        </div>

        <div class="flex space-x-4">
            <a href="#" class="relative" wire:click.prevent="toggleEdit">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none"
                    class="absolute left-4 top-1/2 -translate-y-1/2">
                    <path
                        d="M1.87604 17.1159C1.92198 16.7024 1.94496 16.4957 2.00751 16.3025C2.06301 16.131 2.14143 15.9679 2.24064 15.8174C2.35246 15.6478 2.49955 15.5008 2.79373 15.2066L16 2.0003C17.1046 0.895732 18.8955 0.895734 20 2.0003C21.1046 3.10487 21.1046 4.89573 20 6.0003L6.79373 19.2066C6.49955 19.5008 6.35245 19.6479 6.18289 19.7597C6.03245 19.8589 5.86929 19.9373 5.69785 19.9928C5.5046 20.0553 5.29786 20.0783 4.88437 20.1243L1.5 20.5003L1.87604 17.1159Z"
                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <x-primary-button class="pl-12">{{ $isEditing ? 'Cancelar' : 'Editar' }}</x-primary-button>
            </a>
        </div>
    </div>

    @if($isEditing)
        <div class="p-4 mb-8 bg-white rounded-lg shadow">
            <div class="mb-4">
                <h3 class="mb-2 text-lg font-semibold">Agregar órdenes de compra</h3>
                <div class="relative">
                    <input
                        type="text"
                        wire:model.debounce.300ms="searchPO"
                        wire:keyup="searchPurchaseOrders"
                        placeholder="Buscar por número de PO..."
                        class="rounded-xl border-2 border-[#A5A3A3] pl-11 pr-[1.125rem] py-[0.625rem] placeholder:text-[#9AABFF] w-full"
                    >
                </div>
            </div>

            @if(count($searchResults) > 0)
                <div class="overflow-hidden mt-2 rounded-lg border">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#E0E5FF]">
                            <tr>
                                <th class="px-6 py-6 text-xs font-bold text-left uppercase textblack">Número PO</th>
                                <th class="px-6 py-6 text-xs font-bold text-left uppercase textblack">Proveedor</th>
                                <th class="px-6 py-6 text-xs font-bold text-left uppercase textblack">Total</th>
                                <th class="px-6 py-6 text-xs font-bold text-left uppercase textblack">Estado</th>
                                <th class="px-6 py-6 text-xs font-bold text-left uppercase textblack">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($searchResults as $result)
                                <tr>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">{{ $result['order_number'] }}</td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">{{ $result['vendor_name'] }}</td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">{{ number_format($result['total_amount'], 2) }}</td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">{{ $result['status'] }}</td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        <button
                                            wire:click="attachPurchaseOrder({{ $result['id'] }})"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            Agregar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50">
                                <td colspan="3" class="px-6 py-4 text-sm font-bold text-right text-gray-900">
                                    Total consolidado:
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                    {{ number_format($totalConsolidated ?? 0, 2) }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @elseif(strlen($searchPO) >= 3)
                <p class="mt-2 text-gray-500">No se encontraron resultados</p>
            @endif
        </div>
    @endif

    <div class="mb-8 flex max-w-[600px] justify-between gap-5 rounded-[0.625rem] bg-white p-4 text-xs">
        <div class="flex flex-col justify-between space-y-[0.875rem]">
            <x-label class="bg-warning">
                <span>Documento de embarque</span>

                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" viewBox="0 0 18 16"
                        fill="none">
                        <path
                            d="M16 2H11.82C11.4 0.84 10.3 0 9 0C7.7 0 6.6 0.84 6.18 2H2C0.9 2 0 2.9 0 4V14C0 15.1 0.9 16 2 16H16C17.1 16 18 15.1 18 14V4C18 2.9 17.1 2 16 2ZM9 2C9.55 2 10 2.45 10 3C10 3.55 9.55 4 9 4C8.45 4 8 3.55 8 3C8 2.45 8.45 2 9 2ZM11 12H4V10H11V12ZM14 9H4V7H14V9Z"
                            fill="#F7F7F7" />
                    </svg>
                </x-slot:icon>
            </x-label>

            <x-label class="bg-[#E0E5FF] py-[0.625rem] text-neutral-blue">
                <p class="text-base">HUB: <span>{{ $hubLocation ?? 'No especificado' }}</span></p>

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
                <p>Creación:
                    {{-- <span>{{ $shippingDocument->creation_date ? $shippingDocument->creation_date->format('d/m/Y') : 'N/A' }}</span> --}}
                </p>
                <p>Salida estimada:
                    {{-- <span>{{ $shippingDocument->estimated_departure_date ? $shippingDocument->estimated_departure_date->format('d/m/Y') : 'N/A' }}</span> --}}
                </p>
                <p>Llegada estimada:
                    {{-- <span>{{ $shippingDocument->estimated_arrival_date ? $shippingDocument->estimated_arrival_date->format('d/m/Y') : 'N/A' }}</span> --}}
                </p>
            </div>
        </div>

        <div class="flex gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="18" viewBox="0 0 16 18" fill="none">
                <path
                    d="M2.00016 16H14.0002L12.5752 6H3.42517L2.00016 16ZM8.00016 4C8.2835 4 8.52116 3.904 8.71317 3.712C8.90517 3.52 9.00083 3.28267 9.00016 3C8.9995 2.71733 8.9035 2.48 8.71216 2.288C8.52083 2.096 8.2835 2 8.00016 2C7.71683 2 7.4795 2.096 7.28817 2.288C7.09683 2.48 7.00083 2.71733 7.00016 3C6.9995 3.28267 7.0955 3.52033 7.28817 3.713C7.48083 3.90567 7.71816 4.00133 8.00016 4ZM10.8252 4H12.5752C13.0752 4 13.5085 4.16667 13.8752 4.5C14.2418 4.83333 14.4668 5.24167 14.5502 5.725L15.9752 15.725C16.0585 16.325 15.9045 16.8543 15.5132 17.313C15.1218 17.7717 14.6175 18.0007 14.0002 18H2.00016C1.3835 18 0.879165 17.771 0.487165 17.313C0.0951649 16.855 -0.058835 16.3257 0.025165 15.725L1.45016 5.725C1.5335 5.24167 1.7585 4.83333 2.12516 4.5C2.49183 4.16667 2.92517 4 3.42517 4H5.17517C5.12516 3.83333 5.0835 3.671 5.05017 3.513C5.01683 3.355 5.00016 3.184 5.00016 3C5.00016 2.16667 5.29183 1.45833 5.87516 0.875C6.4585 0.291667 7.16683 0 8.00016 0C8.8335 0 9.54183 0.291667 10.1252 0.875C10.7085 1.45833 11.0002 2.16667 11.0002 3C11.0002 3.18333 10.9835 3.35433 10.9502 3.513C10.9168 3.67167 10.8752 3.834 10.8252 4Z"
                    fill="black" />
            </svg>

            <div>
                <p>Carga total: <span>{{ number_format($totalWeight, 0) }} kg</span></p>
                <p>Compañía: <span>{{ $shippingDocument->company->name ?? 'N/A' }}</span></p>
                <p>Master BL: <span>{{ $shippingDocument->mbl_number ?? 'N/A' }}</span></p>
                <p>Container: <span>{{ $shippingDocument->container_number ?? 'N/A' }}</span></p>
            </div>
        </div>
    </div>

    @if($shippingDocument->tracking_id || $shippingDocument->mbl_number || $shippingDocument->container_number)
    <div class="mb-8">
        <h3 class="mb-6 text-lg font-bold">Estado del Envío</h3>

        @if($loadingTracking)
            <div class="flex justify-center">
                <div class="w-8 h-8 rounded-full border-b-2 animate-spin border-dark-blue"></div>
            </div>
        @else
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
                            <!-- Status dot and line -->
                            <div class="relative">
                                <!-- Active/Completed dot -->
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
                                        {{ \Carbon\Carbon::parse($phase['date'])->format('d/m/Y') }}
                                        <span class="{{ $phase['is_completed'] || $phase['is_current'] ? 'text-dark-blue font-bold' : 'text-gray-400' }}">
                                            {{ \Carbon\Carbon::parse($phase['date'])->format('H:i') }}
                                        </span>
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Estimated delivery -->
                <div class="p-6 mt-12 bg-white rounded-lg border border-gray-100 shadow-sm">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-blue-50 rounded-full">
                                <svg class="w-6 h-6 text-dark-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Entrega estimada</p>
                                <p class="text-lg font-bold text-dark-blue">
                                    {{ \Carbon\Carbon::parse($trackingData['estimated_delivery'])->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-blue-50 rounded-full">
                                <svg class="w-6 h-6 text-dark-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Estado actual</p>
                                <p class="text-lg font-bold text-dark-blue">{{ $trackingData['current_phase'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    @endif

    <div class="space-y-[1.875rem]" x-data="{
        activeTab: 'tab1'}">
        <!-- Selector de pestañas -->
        <div class="flex gap-6 items-center text-lg font-bold">
            <button @click="activeTab = 'tab1'"
                :class="activeTab === 'tab1' ? 'border-dark-blue text-dark-blue' : 'border-transparent'"
                class="border-b-2 py-[0.625rem]">
                Información general
            </button>
            <button @click="activeTab = 'tab2'"
                :class="activeTab === 'tab2' ? 'border-dark-blue text-dark-blue' : 'border-transparent'"
                class="border-b-2 py-[0.625rem]">
                Costos y ahorros
            </button>
            <button @click="activeTab = 'tab3'"
                :class="activeTab === 'tab3' ? 'border-dark-blue text-dark-blue' : 'border-transparent'"
                class="border-b-2 py-[0.625rem]">
                Histórico
            </button>
        </div>

        <!-- Contenido de las pestañas -->
        <div>
            <div x-show="activeTab === 'tab1'" x-transition>
                @php
                    // Default values for sorting to avoid undefined variable errors
                    $sortField = $sortField ?? 'po_number';
                    $sortDirection = $sortDirection ?? 'asc';
                @endphp

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#E0E5FF]">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer"
                                    wire:click="sortBy('po_number')">
                                    Número de PO
                                    @if ($sortField === 'po_number')
                                        @if ($sortDirection === 'asc')
                                            <svg class="inline-block ml-1 w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @else
                                            <svg class="inline-block ml-1 w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        @endif
                                    @endif
                                </th>
                                <th scope="col"
                                    class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer"
                                    wire:click="sortBy('supplier')">
                                    Proveedor
                                    @if ($sortField === 'supplier')
                                        @if ($sortDirection === 'asc')
                                            <svg class="inline-block ml-1 w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @else
                                            <svg class="inline-block ml-1 w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        @endif
                                    @endif
                                </th>
                                <th scope="col"
                                    class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer"
                                    wire:click="sortBy('items_count')">
                                    Cant. Items
                                    @if ($sortField === 'items_count')
                                        @if ($sortDirection === 'asc')
                                            <svg class="inline-block ml-1 w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @else
                                            <svg class="inline-block ml-1 w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        @endif
                                    @endif
                                </th>
                                <th scope="col"
                                    class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer"
                                    wire:click="sortBy('total_amount')">
                                    Total
                                    @if ($sortField === 'total_amount')
                                        @if ($sortDirection === 'asc')
                                            <svg class="inline-block ml-1 w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @else
                                            <svg class="inline-block ml-1 w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        @endif
                                    @endif
                                </th>
                                <th scope="col"
                                    class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer"
                                    wire:click="sortBy('status')">
                                    Estado
                                    @if ($sortField === 'status')
                                        @if ($sortDirection === 'asc')
                                            <svg class="inline-block ml-1 w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @else
                                            <svg class="inline-block ml-1 w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        @endif
                                    @endif
                                </th>
                                <th scope="col"
                                    class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer"
                                    wire:click="sortBy('expected_lead_time')">
                                    Leadtime requerido
                                    @if ($sortField === 'expected_lead_time')
                                        @if ($sortDirection === 'asc')
                                            <svg class="inline-block ml-1 w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @else
                                            <svg class="inline-block ml-1 w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        @endif
                                    @endif
                                </th>
                                <th scope="col"
                                    class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer"
                                    wire:click="sortBy('real_lead_time')">
                                    Leadtime real
                                    @if ($sortField === 'real_lead_time')
                                        @if ($sortDirection === 'asc')
                                            <svg class="inline-block ml-1 w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @else
                                            <svg class="inline-block ml-1 w-4 h-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        @endif
                                    @endif
                                </th>
                                <th scope="col"
                                    class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer"
                                    wire:click="sortBy('actual_lead_time')">
                                    Desviación fecha de arribo
                                </th>
                                <th scope="col"
                                    class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer"
                                    wire:click="sortBy('actual_lead_time')">
                                    Desviación leadtime
                                </th>
                                <th scope="col"
                                    class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($relatedPurchaseOrders ?? [] as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $order['po_number'] }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $order['supplier'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $order['items_count'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            $ {{ number_format($order['total_amount'], 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="text-{{ $order['status_color'] }}-800 bg-{{ $order['status_color'] }}-100 inline-flex rounded-full px-2 text-xs font-semibold leading-5">
                                            {{ $order['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $order['expected_lead_time'] }} días</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $order['real_lead_time'] }} días</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $order['desviacion1'] }} días</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $order['desviacion2'] }} días</div>
                                    </td>
                                    <td class="flex gap-2 items-center px-6 py-4 text-sm font-medium whitespace-nowrap">
                                        <a href="{{ route('purchase-orders.detail', $order['id']) }}"
                                            class="text-indigo-600 hover:text-indigo-900">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M1.61342 8.4761C1.52262 8.33234 1.47723 8.26046 1.45182 8.1496C1.43273 8.06632 1.43273 7.93498 1.45182 7.85171C1.47723 7.74084 1.52262 7.66896 1.61341 7.5252C2.36369 6.33721 4.59693 3.33398 8.00027 3.33398C11.4036 3.33398 13.6369 6.33721 14.3871 7.5252C14.4779 7.66896 14.5233 7.74084 14.5487 7.85171C14.5678 7.93498 14.5678 8.06632 14.5487 8.1496C14.5233 8.26046 14.4779 8.33234 14.3871 8.4761C13.6369 9.66409 11.4036 12.6673 8.00027 12.6673C4.59693 12.6673 2.36369 9.66409 1.61342 8.4761Z" stroke="#666666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M8.00027 10.0007C9.10484 10.0007 10.0003 9.10522 10.0003 8.00065C10.0003 6.89608 9.10484 6.00065 8.00027 6.00065C6.8957 6.00065 6.00027 6.89608 6.00027 8.00065C6.00027 9.10522 6.8957 10.0007 8.00027 10.0007Z" stroke="#666666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </a>

                                        <button
                                            wire:click="setSelectedPo({{ $order['id'] }})"
                                            x-on:click="$dispatch('open-modal', 'modal-delete-order')"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No se encontraron órdenes de compra relacionadas con este documento de embarque
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50">
                                <td colspan="3" class="px-6 py-4 text-sm font-bold text-right text-gray-900">
                                    Total consolidado:
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                    {{ number_format($totalConsolidated ?? 0, 2) }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div x-show="activeTab === 'tab2'" x-transition class="space-y-[1.875rem]">
                <div class="flex justify-between items-centers">
                    <x-search-input class="w-64" />

                    <div class="flex hidden gap-4">
                        <x-primary-button>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 20 20" fill="none">
                                <path
                                    d="M18.453 10.8927C18.1752 13.5026 16.6964 15.9483 14.2494 17.3611C10.1839 19.7083 4.98539 18.3153 2.63818 14.2499L2.38818 13.8168M1.54613 9.10664C1.82393 6.49674 3.30272 4.05102 5.74971 2.63825C9.8152 0.29104 15.0137 1.68398 17.3609 5.74947L17.6109 6.18248M1.49316 16.0657L2.22521 13.3336L4.95727 14.0657M15.0424 5.93364L17.7744 6.66569L18.5065 3.93364"
                                    stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="disabled:stroke-[#C2C2C2]" />
                            </svg>
                        </x-primary-button>

                        <x-secondary-button class="flex items-center gap-[0.625rem]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="22"
                                viewBox="0 0 21 22" fill="none">
                                <path
                                    d="M19.1527 9.89994L10.1371 18.9156C8.08686 20.9658 4.76275 20.9658 2.71249 18.9156C0.662241 16.8653 0.662242 13.5412 2.71249 11.4909L11.7281 2.47532C13.0949 1.10849 15.311 1.10849 16.6779 2.47532C18.0447 3.84216 18.0447 6.05823 16.6779 7.42507L8.01579 16.0871C7.33238 16.7705 6.22434 16.7705 5.54092 16.0871C4.8575 15.4037 4.8575 14.2957 5.54092 13.6123L13.1423 6.01086"
                                    stroke="#565AFF" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>

                            <span>Adjuntar costos</span>
                        </x-secondary-button>
                    </div>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#E0E5FF]">
                        <tr>
                            <th scope="col"
                                class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                                Número PO
                            </th>
                            <th scope="col"
                                class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                                Ahorro OFR para FCL
                            </th>
                            <th scope="col"
                                class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                                Ahorro en pickup
                            </th>
                            <th scope="col"
                                class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                                Ahorro ejecutado
                            </th>
                            <th scope="col"
                                class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                                Ahorro no ejecutado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($poSavingsData as $poData)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <a href="{{ route('purchase-orders.detail', $poData['id']) }}" class="text-[#190FDB] underline underline-offset-4">
                                            {{ $poData['order_number'] }}
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">$ {{ number_format($poData['savings_ofr_fcl'], 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">$ {{ number_format($poData['saving_pickup'], 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">$ {{ number_format($poData['saving_executed'], 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">$ {{ number_format($poData['saving_not_executed'], 2) }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No se encontraron órdenes de compra para este documento de embarque
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-bold bg-gray-50">
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                TOTAL
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                $ {{ number_format($totalSavingsOfrFcl, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                $ {{ number_format($totalSavingPickup, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                $ {{ number_format($totalSavingExecuted, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                $ {{ number_format($totalSavingNotExecuted, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div x-show="activeTab === 'tab3'" x-transition x-data="{ fileSelected: false }">
                <div class="flex justify-between items-center mb-6">
                    <x-search-input class="w-64" wire:model.debounce.300ms="search" placeholder="Buscar comentarios o archivos..." />

                    <div class="flex gap-4">
                        <!-- Botón upload -->
                        <x-primary-button
                            type="button"
                            class="flex gap-2 items-center group"
                            x-on:click="window.location.reload()">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18.453 10.8927C18.1752 13.5026 16.6964 15.9483 14.2494 17.3611C10.1839 19.7083 4.98539 18.3153 2.63818 14.2499L2.38818 13.8168M1.54613 9.10664C1.82393 6.49674 3.30272 4.05102 5.74971 2.63825C9.8152 0.29104 15.0137 1.68398 17.3609 5.74947L17.6109 6.18248M1.49316 16.0657L2.22521 13.3336L4.95727 14.0657M15.0424 5.93364L17.7744 6.66569L18.5065 3.93364" stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </x-primary-button>

                        <x-secondary-button type="button" class="group flex items-center gap-[0.625rem]" x-on:click="$dispatch('open-modal', 'modal-upload-document')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="22"
                                viewBox="0 0 21 22" fill="none">
                                <path
                                    d="M19.1527 9.89994L10.1371 18.9156C8.08686 20.9658 4.76275 20.9658 2.71249 18.9156C0.662241 16.8653 0.662242 13.5412 2.71249 11.4909L11.7281 2.47532C13.0949 1.10849 15.311 1.10849 16.6779 2.47532C18.0447 3.84216 18.0447 6.05823 16.6779 7.42507L8.01579 16.0871C7.33238 16.7705 6.22434 16.7705 5.54092 16.0871C4.8575 15.4037 4.8575 14.2957 5.54092 13.6123L13.1423 6.01086"
                                    stroke="#565AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="transition-colors duration-500 group-hover:stroke-dark-blue group-active:stroke-neutral-blue group-disabled:stroke-[#C2C2C2]" />
                            </svg>

                            <span>Adjuntar documentación</span>
                        </x-secondary-button>
                    </div>
                </div>

                <!-- Mostrar mensajes de notificación -->
                @if (session()->has('message'))
                    <div class="p-4 mb-6 text-sm text-green-800 bg-green-100 rounded-md">
                        {{ session('message') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="p-4 mb-6 text-sm text-red-800 bg-red-100 rounded-md">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Tabla para comentarios y archivos adjuntos -->
                <div class="overflow-x-auto w-full">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#E0E5FF]">
                            <tr>
                                <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase">
                                    <input type="checkbox" class="rounded text-primary-600">
                                </th>
                                <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase">
                                    Fecha y hora
                                </th>
                                <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase">
                                    Usuario
                                </th>
                                <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase">
                                    Rol
                                </th>
                                <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase">
                                    Operación
                                </th>
                                <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase">
                                    Estado
                                </th>
                                <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase">
                                    Comentarios
                                </th>
                                <th class="px-6 py-6 text-xs font-bold text-left text-black uppercase">
                                    Archivos adjuntos
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($comments as $comment)
                                <tr>
                                    <td class="px-6 py-4">
                                        <input type="checkbox" class="rounded text-primary-600">
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($comment['created_at'])->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        {{ $comment['user_name'] }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        {{ $comment['user_role'] ?? 'Sin Rol' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        {{ $comment['stage'] ?? 'Shipping Document' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($comment['status'] === 'Aprobado')
                                            <span class="inline-flex px-2 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">
                                                Aprobado <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </span>
                                        @elseif($comment['status'] === 'Pendiente')
                                            <span class="inline-flex px-2 text-xs font-semibold leading-5 text-yellow-800 bg-yellow-100 rounded-full">
                                                Pendiente <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full">
                                                Rechazado <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        {{ $comment['comment'] }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        @if(count($comment['attachments'] ?? []) > 0)
                                            @foreach($comment['attachments'] as $attachment)
                                                <a href="{{ $attachment['url'] }}"
                                                   class="flex gap-1 items-center mb-1 text-blue-600 hover:text-blue-800"
                                                   target="_blank">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                    </svg>
                                                    {{ $attachment['filename'] }}
                                                </a>
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-sm text-center text-gray-500">
                                        No hay registros disponibles
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="modal-upload-document" maxWidth="lg">
        <h3 class="mb-2 text-lg font-bold text-center text-light-blue">
            Agregar comentario
        </h3>

        <div class="mb-8">
            <x-form-textarea
                label=""
                name="comment_stage_01"
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
                        id="file-upload-po"
                        x-bind:disabled="!$wire.comment || $wire.comment.trim() === ''"
                    >
                    <x-secondary-button
                        onclick="document.getElementById('file-upload-po').click()"
                        class="group flex w-full items-center justify-center gap-[0.625rem]"
                        x-bind:disabled="!$wire.comment || $wire.comment.trim() === ''"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="21" height="22" viewBox="0 0 21 22"
                            fill="none">
                            <path
                                d="M19.1525 9.89897L10.1369 18.9146C8.08662 20.9648 4.7625 20.9648 2.71225 18.9146C0.661997 16.8643 0.661998 13.5402 2.71225 11.49L11.7279 2.47435C13.0947 1.10751 15.3108 1.10751 16.6776 2.47434C18.0444 3.84118 18.0444 6.05726 16.6776 7.42409L8.01555 16.0862C7.33213 16.7696 6.22409 16.7696 5.54068 16.0862C4.85726 15.4027 4.85726 14.2947 5.54068 13.6113L13.1421 6.00988"
                                stroke="#565AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
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
                x-on:click="$dispatch('close-modal', 'modal-upload-document')"
                class="w-full"
            >
                Cancelar
            </x-secondary-button>

            <x-primary-button
                wire:click="setComments"
                x-on:click="$dispatch('close-modal', 'modal-upload-document')"
                class="w-full"
            >
                Guardar
            </x-primary-button>
        </div>
    </x-modal>

    <x-modal name="modal-delete-order" maxWidth="md">
        <h3 class="mb-2 mb-6 text-lg font-bold text-center text-[#FF3459]">
            Eliminar orden de compra
        </h3>

        <div class="flex justify-center mb-6">
            <svg width="104" height="104" viewBox="0 0 104 104" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M51.9998 34.667V52.0003M51.9998 69.3337H52.0432M95.3332 52.0003C95.3332 75.9327 75.9322 95.3337 51.9998 95.3337C28.0675 95.3337 8.6665 75.9327 8.6665 52.0003C8.6665 28.068 28.0675 8.66699 51.9998 8.66699C75.9322 8.66699 95.3332 28.068 95.3332 52.0003Z" stroke="#FF3459" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <div class="flex gap-[1.875rem]">
            <x-secondary-button
                x-on:click="$dispatch('close-modal', 'modal-delete-order')"
                class="w-full"
            >
                Cancelar
            </x-secondary-button>

            <x-primary-button x-on:click="$dispatch('close-modal', 'modal-delete-order')" wire:click="deleteOrder({{ $selectedPoId }})" class="w-full !bg-[#FF3459] !border-[#FF3459]" >
                Eliminar
            </x-primary-button>
        </div>
    </x-modal>
</div>
