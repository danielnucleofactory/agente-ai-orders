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
    </div>

    {{-- SECCIÓN CON TODOS LOS CAMPOS DE LA PO --}}
    <div class="rounded-[0.625rem] bg-white p-6 space-y-6">
        {{-- Información Básica --}}
        <div>
            <h3 class="text-md font-bold mb-3 text-gray-700">Información Básica</h3>
            <div class="grid grid-cols-4 gap-4 text-xs">
                <div>
                    <p class="text-gray-500">Estado</p>
                    <p class="font-semibold">{{ $purchaseOrder->status ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha de orden</p>
                    <p class="font-semibold">{{ $purchaseOrder->order_date ? \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Moneda</p>
                    <p class="font-semibold">{{ $purchaseOrder->currency ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Modo</p>
                    <p class="font-semibold">{{ $purchaseOrder->mode ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Incoterms</p>
                    <p class="font-semibold">{{ $purchaseOrder->incoterms ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Términos de pago</p>
                    <p class="font-semibold">{{ $purchaseOrder->payment_terms ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Email agente</p>
                    <p class="font-semibold">{{ $purchaseOrder->email_agent ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Tracking ID</p>
                    <p class="font-semibold">{{ $purchaseOrder->tracking_id ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Dimensiones y Peso --}}
        <div>
            <h3 class="text-md font-bold mb-3 text-gray-700">Dimensiones y Peso</h3>
            <div class="grid grid-cols-4 gap-4 text-xs">
                <div>
                    <p class="text-gray-500">Peso (kg)</p>
                    <p class="font-semibold">{{ $purchaseOrder->weight_kg ?? '0.00' }} kg</p>
                </div>
                <div>
                    <p class="text-gray-500">Peso (lb)</p>
                    <p class="font-semibold">{{ $purchaseOrder->weight_lb ?? '0.00' }} lb</p>
                </div>
                <div>
                    <p class="text-gray-500">Alto</p>
                    <p class="font-semibold">{{ $purchaseOrder->height ?? '0' }} in</p>
                </div>
                <div>
                    <p class="text-gray-500">Ancho</p>
                    <p class="font-semibold">{{ $purchaseOrder->width ?? '0' }} in</p>
                </div>
                <div>
                    <p class="text-gray-500">Largo</p>
                    <p class="font-semibold">{{ $purchaseOrder->length ?? '0' }} in</p>
                </div>
                <div>
                    <p class="text-gray-500">Alto (cm)</p>
                    <p class="font-semibold">{{ $purchaseOrder->height_cm ?? '0.00' }} cm</p>
                </div>
                <div>
                    <p class="text-gray-500">Ancho (cm)</p>
                    <p class="font-semibold">{{ $purchaseOrder->width_cm ?? '0.00' }} cm</p>
                </div>
                <div>
                    <p class="text-gray-500">Largo (cm)</p>
                    <p class="font-semibold">{{ $purchaseOrder->length_cm ?? '0.00' }} cm</p>
                </div>
                <div>
                    <p class="text-gray-500">Volumen</p>
                    <p class="font-semibold">{{ $purchaseOrder->volume ?? '0.000' }} m³</p>
                </div>
                <div>
                    <p class="text-gray-500">CBM</p>
                    <p class="font-semibold">{{ $purchaseOrder->cbm ?? '0.00' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Cantidad pallets</p>
                    <p class="font-semibold">{{ $purchaseOrder->pallet_quantity ?? '0' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Pallets real</p>
                    <p class="font-semibold">{{ $purchaseOrder->pallet_quantity_real ?? '0' }}</p>
                </div>
            </div>
        </div>

        {{-- Montos y Costos --}}
        <div>
            <h3 class="text-md font-bold mb-3 text-gray-700">Montos y Costos</h3>
            <div class="grid grid-cols-4 gap-4 text-xs">
                <div>
                    <p class="text-gray-500">Monto total</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->total_amount ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Neto total</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->net_total ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Costo adicional</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->additional_cost ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Total</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->total ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Costo seguro</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->insurance_cost ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Transporte terrestre 1</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->ground_transport_cost_1 ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Transporte terrestre 2</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->ground_transport_cost_2 ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Costo nacionalización</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->cost_nationalization ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">OFR estimado</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->cost_ofr_estimated ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">OFR real</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->cost_ofr_real ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Costo pallet estimado</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->estimated_pallet_cost ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Costo real estimado PO</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->real_cost_estimated_po ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Costo real real PO</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->real_cost_real_po ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Otros costos</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->other_costs ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Otros gastos</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->other_expenses ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Variable peso calculable</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->variable_calculare_weight ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Monto Invoice</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->Invoice_amount ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Monto flete</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->freight_amount ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        {{-- Ahorros --}}
        <div>
            <h3 class="text-md font-bold mb-3 text-gray-700">Ahorros</h3>
            <div class="grid grid-cols-4 gap-4 text-xs">
                <div>
                    <p class="text-gray-500">Ahorro OFR FCL</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->savings_ofr_fcl ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Ahorro pickup</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->saving_pickup ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Ahorro ejecutado</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->saving_executed ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Ahorro no ejecutado</p>
                    <p class="font-semibold">$ {{ number_format($purchaseOrder->saving_not_executed ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        {{-- Fechas Principales --}}
        <div>
            <h3 class="text-md font-bold mb-3 text-gray-700">Fechas Principales</h3>
            <div class="grid grid-cols-4 gap-4 text-xs">
                <div>
                    <p class="text-gray-500">Fecha requerida en destino</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_required_in_destination ? \Carbon\Carbon::parse($purchaseOrder->date_required_in_destination)->format('d/m/Y') : '-' }}</p>
            </div>
                <div>
                    <p class="text-gray-500">Fecha pickup planificada</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_planned_pickup ? \Carbon\Carbon::parse($purchaseOrder->date_planned_pickup)->format('d/m/Y') : '-' }}</p>
        </div>
                <div>
                    <p class="text-gray-500">Fecha pickup real</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_actual_pickup ? \Carbon\Carbon::parse($purchaseOrder->date_actual_pickup)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha estimada llegada hub</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_estimated_hub_arrival ? \Carbon\Carbon::parse($purchaseOrder->date_estimated_hub_arrival)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha real llegada hub</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_actual_hub_arrival ? \Carbon\Carbon::parse($purchaseOrder->date_actual_hub_arrival)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha ETD</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_etd ? \Carbon\Carbon::parse($purchaseOrder->date_etd)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha ATD</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_atd ? \Carbon\Carbon::parse($purchaseOrder->date_atd)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha ETA</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_eta ? \Carbon\Carbon::parse($purchaseOrder->date_eta)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha ATA</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_ata ? \Carbon\Carbon::parse($purchaseOrder->date_ata)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha consolidación</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_consolidation ? \Carbon\Carbon::parse($purchaseOrder->date_consolidation)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha release</p>
                    <p class="font-semibold">{{ $purchaseOrder->release_date ? \Carbon\Carbon::parse($purchaseOrder->release_date)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha ETD inicial</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_etd_initial ? \Carbon\Carbon::parse($purchaseOrder->date_etd_initial)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha ETA inicial</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_eta_initial ? \Carbon\Carbon::parse($purchaseOrder->date_eta_initial)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha solicitud booking</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_booking_request ? \Carbon\Carbon::parse($purchaseOrder->date_booking_request)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha booking autorizado</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_booking_authorized ? \Carbon\Carbon::parse($purchaseOrder->date_booking_authorized)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha carga teórica</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_theorical_load ? \Carbon\Carbon::parse($purchaseOrder->date_theorical_load)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha variable</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_variable_date ? \Carbon\Carbon::parse($purchaseOrder->date_variable_date)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha carga PO</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_carga_po ? \Carbon\Carbon::parse($purchaseOrder->date_carga_po)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha recibida</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_received ? \Carbon\Carbon::parse($purchaseOrder->date_received)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha inspección</p>
                    <p class="font-semibold">{{ $purchaseOrder->inspection_date ? \Carbon\Carbon::parse($purchaseOrder->inspection_date)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha corte VGM</p>
                    <p class="font-semibold">{{ $purchaseOrder->vgm_cut_date ? \Carbon\Carbon::parse($purchaseOrder->vgm_cut_date)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha pago balance</p>
                    <p class="font-semibold">{{ $purchaseOrder->balance_payment_date ? \Carbon\Carbon::parse($purchaseOrder->balance_payment_date)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha pago cargos locales</p>
                    <p class="font-semibold">{{ $purchaseOrder->local_charges_payment_date ? \Carbon\Carbon::parse($purchaseOrder->local_charges_payment_date)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Entrada almacén aduanero</p>
                    <p class="font-semibold">{{ $purchaseOrder->bonded_warehouse_enter ? \Carbon\Carbon::parse($purchaseOrder->bonded_warehouse_enter)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Salida almacén aduanero</p>
                    <p class="font-semibold">{{ $purchaseOrder->bonded_warehouse_exit ? \Carbon\Carbon::parse($purchaseOrder->bonded_warehouse_exit)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha nota de recepción</p>
                    <p class="font-semibold">{{ $purchaseOrder->receipt_note_date ? \Carbon\Carbon::parse($purchaseOrder->receipt_note_date)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Disponibilidad DC estimada</p>
                    <p class="font-semibold">{{ $purchaseOrder->estimated_dc_availability_date ? \Carbon\Carbon::parse($purchaseOrder->estimated_dc_availability_date)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha invoice recibido</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_invoice_received ? \Carbon\Carbon::parse($purchaseOrder->date_invoice_received)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha doc vendor recibido</p>
                    <p class="font-semibold">{{ $purchaseOrder->date_vendor_document_received ? \Carbon\Carbon::parse($purchaseOrder->date_vendor_document_received)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Actualización fecha PO</p>
                    <p class="font-semibold">{{ $purchaseOrder->update_date_po ? \Carbon\Carbon::parse($purchaseOrder->update_date_po)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Emisión fecha PO</p>
                    <p class="font-semibold">{{ $purchaseOrder->emision_date_po ? \Carbon\Carbon::parse($purchaseOrder->emision_date_po)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Fecha forwarder</p>
                    <p class="font-semibold">{{ $purchaseOrder->forwader_date ? \Carbon\Carbon::parse($purchaseOrder->forwader_date)->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Diferencia fecha carga</p>
                    <p class="font-semibold">{{ $purchaseOrder->dif_load_date ? \Carbon\Carbon::parse($purchaseOrder->dif_load_date)->format('d/m/Y') : '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Información de Envío --}}
        <div>
            <h3 class="text-md font-bold mb-3 text-gray-700">Información de Envío</h3>
            <div class="grid grid-cols-4 gap-4 text-xs">
                <div>
                    <p class="text-gray-500">Bill of Lading</p>
                    <p class="font-semibold">{{ $purchaseOrder->bill_of_lading ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Tipo de seguro</p>
                    <p class="font-semibold">{{ $purchaseOrder->ensurence_type ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Número proforma fábrica</p>
                    <p class="font-semibold">{{ $purchaseOrder->factory_proforma_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Número MBL</p>
                    <p class="font-semibold">{{ $purchaseOrder->mbl_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Tipo contenedor</p>
                    <p class="font-semibold">{{ $purchaseOrder->container_type ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Número contenedor</p>
                    <p class="font-semibold">{{ $purchaseOrder->container_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Línea naviera</p>
                    <p class="font-semibold">{{ $purchaseOrder->shipping_line ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Puerto salida</p>
                    <p class="font-semibold">{{ $purchaseOrder->departure_port ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Puerto llegada</p>
                    <p class="font-semibold">{{ $purchaseOrder->arrival_port ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Estado llegada</p>
                    <p class="font-semibold">{{ $purchaseOrder->arrival_status ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Días de retraso</p>
                    <p class="font-semibold">{{ $purchaseOrder->delay_days ?? '0' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Días libres contenedor</p>
                    <p class="font-semibold">{{ $purchaseOrder->container_free_days ?? '0' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Diferencia fechas ETD</p>
                    <p class="font-semibold">{{ $purchaseOrder->etd_dates_difference ?? '0' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Diferencia fechas ETA</p>
                    <p class="font-semibold">{{ $purchaseOrder->eta_dates_difference ?? '0' }}</p>
                </div>
            </div>
        </div>

        {{-- Clasificación y Referencias --}}
        <div>
            <h3 class="text-md font-bold mb-3 text-gray-700">Clasificación y Referencias</h3>
            <div class="grid grid-cols-4 gap-4 text-xs">
                <div>
                    <p class="text-gray-500">Incoterm logístico</p>
                    <p class="font-semibold">{{ $purchaseOrder->logistics_incoterm ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Incoterm precio</p>
                    <p class="font-semibold">{{ $purchaseOrder->price_incoterm ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Razón</p>
                    <p class="font-semibold">{{ $purchaseOrder->reason ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Categoría</p>
                    <p class="font-semibold">{{ $purchaseOrder->category ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Nombre forwarder</p>
                    <p class="font-semibold">{{ $purchaseOrder->forwarder_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Número invoice carga</p>
                    <p class="font-semibold">{{ $purchaseOrder->cargo_invoice_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Tipo tarifa</p>
                    <p class="font-semibold">{{ $purchaseOrder->tariff_type ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Etiqueta ruta</p>
                    <p class="font-semibold">{{ $purchaseOrder->route_label ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Grupo retail</p>
                    <p class="font-semibold">{{ $purchaseOrder->retail_group ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Tipo cliente</p>
                    <p class="font-semibold">{{ $purchaseOrder->customer_type ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Trading company</p>
                    <p class="font-semibold">{{ $purchaseOrder->trading_company ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Proveedor servicio</p>
                    <p class="font-semibold">{{ $purchaseOrder->service_provider ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Nombre consolidador</p>
                    <p class="font-semibold">{{ $purchaseOrder->consolidator_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Número vendor</p>
                    <p class="font-semibold">{{ $purchaseOrder->vendor_number ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Documentación --}}
        <div>
            <h3 class="text-md font-bold mb-3 text-gray-700">Documentación</h3>
            <div class="grid grid-cols-4 gap-4 text-xs">
                <div>
                    <p class="text-gray-500">DUA Aduanas</p>
                    <p class="font-semibold">{{ $purchaseOrder->customs_dua ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Invoice</p>
                    <p class="font-semibold">{{ $purchaseOrder->invoice ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Factura merca</p>
                    <p class="font-semibold">{{ $purchaseOrder->factura_merca ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Número caso/archivo</p>
                    <p class="font-semibold">{{ $purchaseOrder->case_number_file ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Flags Booleanos --}}
        <div>
            <h3 class="text-md font-bold mb-3 text-gray-700">Indicadores</h3>
            <div class="grid grid-cols-4 gap-4 text-xs">
                <div>
                    <p class="text-gray-500">Puerto carga validado</p>
                    <p class="font-semibold">{{ $purchaseOrder->port_of_loading_validated ? 'Sí' : 'No' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Es dropship</p>
                    <p class="font-semibold">{{ $purchaseOrder->is_dropship ? 'Sí' : 'No' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Aplica TLC</p>
                    <p class="font-semibold">{{ $purchaseOrder->applies_tlc ? 'Sí' : 'No' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Aplica AF</p>
                    <p class="font-semibold">{{ $purchaseOrder->applies_af ? 'Sí' : 'No' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Tiene factura merca</p>
                    <p class="font-semibold">{{ $purchaseOrder->has_facture_merca ? 'Sí' : 'No' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Tarifa usada OK</p>
                    <p class="font-semibold">{{ $purchaseOrder->used_rate_ok ? 'Sí' : 'No' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Usa almacén aduanero</p>
                    <p class="font-semibold">{{ $purchaseOrder->uses_bonded_warehouse ? 'Sí' : 'No' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Aplica nota técnica</p>
                    <p class="font-semibold">{{ $purchaseOrder->apply_technical_note ? 'Sí' : 'No' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">ETD inicial validado</p>
                    <p class="font-semibold">{{ $purchaseOrder->etd_initial_validated ? 'Sí' : 'No' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Confirmar actualización fecha PO</p>
                    <p class="font-semibold">{{ $purchaseOrder->confirm_update_date_po ? 'Sí' : 'No' }}</p>
                </div>
            </div>
        </div>

        {{-- Notas y Comentarios --}}
        <div>
            <h3 class="text-md font-bold mb-3 text-gray-700">Notas y Comentarios</h3>
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <p class="text-gray-500">Notas</p>
                    <p class="font-semibold">{{ $purchaseOrder->notes ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Comentarios</p>
                    <p class="font-semibold">{{ $purchaseOrder->comments ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Nota de recepción</p>
                    <p class="font-semibold">{{ $purchaseOrder->receipt_note ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Notas de visibilidad</p>
                    <p class="font-semibold">{{ $purchaseOrder->visibility_notes ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Razón rechazo</p>
                    <p class="font-semibold">{{ $purchaseOrder->rejection_reason ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Tipo de Material (JSON) --}}
        @if($purchaseOrder->material_type)
        <div>
            <h3 class="text-md font-bold mb-3 text-gray-700">Tipo de Material</h3>
            <div class="text-xs">
                <p class="font-semibold">{{ is_array($materialType) ? json_encode($materialType) : $materialType }}</p>
            </div>
        </div>
        @endif
    </div>

    @if($purchaseOrder->tracking_id)
    <div class="mb-8">
        <h3 class="mb-6 text-lg font-bold">Estado del Envío</h3>

        @if($loadingTracking)
            <div class="flex justify-center">
                <div class="w-8 h-8 rounded-full border-b-2 animate-spin border-dark-blue"></div>
            </div>
        @elseif(isset($trackingData['raw_data']) && isset($trackingData['raw_data']['events']))
            <div class="relative">
                <!-- Timeline track -->
                <div class="absolute h-[2px] top-6 left-0 right-0 bg-gray-200"></div>

                <!-- Timeline events -->
                <div class="flex overflow-x-scroll relative justify-between w-full">
                    @foreach($trackingData['raw_data']['events'] as $event)
                        @php
                            // Determinar estado basado en la fecha de ocurrencia
                            $eventDate = \Carbon\Carbon::parse($event['occurrenceDatetime'] ?? now());
                            $isCompleted = $eventDate->isPast();
                            $isActive = $eventDate->isToday();
                            $status = $isCompleted ? 'completed' : ($isActive ? 'active' : 'pending');
                        @endphp
                        <div class="flex flex-col items-center">
                            <!-- Status dot -->
                            <div class="relative">
                                <div class="z-20 flex items-center justify-center w-12 h-12 mb-2 rounded-full transition-all duration-300
                                    {{ $status == 'completed' ? 'bg-dark-blue' : ($status == 'active' ? 'bg-dark-blue' : 'bg-gray-200') }}">
                                    <svg class="w-6 h-6 {{ $status == 'completed' || $status == 'active' ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Status details -->
                            <div class="mt-4 w-32 text-center">
                                <p class="mb-1 text-sm font-bold {{ $status == 'completed' || $status == 'active' ? 'text-dark-blue' : 'text-gray-400' }}">
                                    {{ $event['status'] }}
                                </p>
                                @if(isset($event['occurrenceDatetime']))
                                    <p class="mb-1 text-xs font-medium {{ $status == 'completed' || $status == 'active' ? 'text-dark-blue font-bold' : 'text-gray-400' }}">
                                        {{ \Carbon\Carbon::parse($event['occurrenceDatetime'])->format('d/m/Y') }}
                                        <span class="{{ $status == 'completed' || $status == 'active' ? 'text-dark-blue font-bold' : 'text-gray-400' }}">
                                            {{ \Carbon\Carbon::parse($event['occurrenceDatetime'])->format('H:i') }}
                                        </span>
                                    </p>
                                @endif
                                @if(isset($event['location']))
                                    <p class="text-xs {{ $status == 'completed' || $status == 'active' ? 'text-dark-blue font-bold' : 'text-gray-400' }}">
                                        {{ $event['location'] }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Información adicional -->
                <div class="p-6 mt-12 bg-white rounded-lg border border-gray-100 shadow-sm">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Estado actual</p>
                            @php
                            // Encontrar el evento con statusMilestone "out_for_delivery"
                            $activeEvent = null;
                            foreach($trackingData['raw_data']['events'] as $evt) {
                                if($evt['statusMilestone'] === 'out_for_delivery') {
                                    $activeEvent = $evt;
                                    break;
                                }
                            }
                            // Si no encontramos out_for_delivery, usar el primer evento
                            $activeEvent = $activeEvent ?? $trackingData['raw_data']['events'][0] ?? null;
                            @endphp
                            <p class="text-lg font-bold text-dark-blue">
                                {{ $activeEvent ? $activeEvent['status'] : 'No disponible' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="p-4 text-yellow-800 bg-yellow-100 rounded-lg">
                <p>No se pudieron cargar los datos de seguimiento. Por favor, inténtelo de nuevo más tarde.</p>
            </div>
        @endif
    </div>
    @endif

    <div class="space-y-[1.875rem]" x-data="{
        activeTab: 'tab3'
    }">
        <!-- Selector de pestañas -->
        <div class="flex gap-6 items-center text-lg font-bold">
            <button @click="activeTab = 'tab3'"
                :class="activeTab === 'tab3' ? 'border-dark-blue text-dark-blue' : 'border-transparent'"
                class="border-b-2 py-[0.625rem]">
                Costos y ahorros
            </button>
            <button @click="activeTab = 'tab4'"
                :class="activeTab === 'tab4' ? 'border-dark-blue text-dark-blue' : 'border-transparent'"
                class="border-b-2 py-[0.625rem]">
                Histórico
            </button>
        </div>

        <!-- Contenido de las pestañas -->
        <div>
            <div x-show="activeTab === 'tab3'" x-transition>
                <div class="flex justify-between mb-6 items-centers">
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
                                    stroke="#565AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
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

                            <th scope="col"
                                class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                                Sobre costo
                            </th>

                            <th scope="col"
                                class="px-6 py-5 text-xs font-bold tracking-wider text-left text-black uppercase cursor-pointer">
                                Comentario
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            // Variables para acumular los totales de ahorro
                            $totalSavingsOfrFcl = 0;
                            $totalSavingPickup = 0;
                            $totalSavingExecuted = 0;
                            $totalSavingNotExecuted = 0;

                            // Si tenemos un shipping document, calcular los totales de todas las POs asociadas
                            if (isset($shippingDocument) && $shippingDocument) {
                                foreach ($shippingDocument->purchaseOrders as $po) {
                                    $totalSavingsOfrFcl += $po->savings_ofr_fcl ?? 0;
                                    $totalSavingPickup += $po->saving_pickup ?? 0;
                                    $totalSavingExecuted += $po->saving_executed ?? 0;
                                    $totalSavingNotExecuted += $po->saving_not_executed ?? 0;
                                }
                            } else {
                                // Si no hay shipping document, usar los valores de la PO actual
                                $totalSavingsOfrFcl = $purchaseOrder->savings_ofr_fcl ?? 0;
                                $totalSavingPickup = $purchaseOrder->saving_pickup ?? 0;
                                $totalSavingExecuted = $purchaseOrder->saving_executed ?? 0;
                                $totalSavingNotExecuted = $purchaseOrder->saving_not_executed ?? 0;
                            }
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">$ {{ number_format($totalSavingsOfrFcl, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">$ {{ number_format($totalSavingPickup, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">$ {{ number_format($totalSavingExecuted, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">$ {{ number_format($totalSavingNotExecuted, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">$ {{ number_format($totalOverCost, 2) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if(count($overCostData) > 0)
                                    <div class="space-y-1">
                                        @foreach($overCostData as $overCost)
                                            <div class="text-sm text-gray-900">
                                                <span class="font-medium">{{ $overCost['order_number'] }}:</span>
                                                {{ $overCost['comment_final'] ?: 'Sin comentario adicional' }}
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-sm text-gray-500">Sin comentarios de sobre costo</div>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div x-show="activeTab === 'tab4'" x-transition>
                <div class="flex justify-between items-center mb-6">
                    <x-search-input class="w-64" wire:model.debounce.300ms="search" placeholder="Buscar en el historial..." />

                    <div class="flex gap-4">
                        <x-primary-button
                            type="button"
                            class="flex gap-2 items-center group"
                            x-on:click="window.location.reload()"
                        >
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18.453 10.8927C18.1752 13.5026 16.6964 15.9483 14.2494 17.3611C10.1839 19.7083 4.98539 18.3153 2.63818 14.2499L2.38818 13.8168M1.54613 9.10664C1.82393 6.49674 3.30272 4.05102 5.74971 2.63825C9.8152 0.29104 15.0137 1.68398 17.3609 5.74947L17.6109 6.18248M1.49316 16.0657L2.22521 13.3336L4.95727 14.0657M15.0424 5.93364L17.7744 6.66569L18.5065 3.93364" stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </x-primary-button>

                        <x-secondary-button type="button" class="group flex items-center gap-[0.625rem]" @click="$dispatch('open-modal', 'modal-upload-document')">
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

                <!-- Tabla de historial -->
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
                                    {{ $comment['user_role'] }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    {{ $comment['operation'] }}
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
                                            Rechazada <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    {{ $comment['comment'] }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    @if($comment['attachment'])
                                        <a href="{{ $comment['attachment']['url'] }}"
                                           class="flex gap-1 items-center text-blue-600 hover:text-blue-800"
                                           target="_blank">
                                            {{ $comment['attachment']['name'] }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
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
                                d="M19.1525 9.89897L10.1369 18.9146C8.08662 20.9648 4.7625 20.9648 2.71225 18.9146C0.661997 16.8643 0.661998 13.5402 2.71225 11.4909L11.7281 2.47532C13.0949 1.10849 15.311 1.10849 16.6779 2.47532C18.0447 3.84216 18.0447 6.05823 16.6779 7.42507L8.01579 16.0871C7.33238 16.7705 6.22434 16.7705 5.54092 16.0871C4.8575 15.4037 4.8575 14.2957 5.54092 13.6123L13.1423 6.01086"
                                stroke="#565AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="transition-colors duration-500 group-hover:stroke-dark-blue group-active:stroke-neutral-blue group-disabled:stroke-[#C2C2C2]" />
                        </svg>

                        <span>Adjuntar documentación...</span>
                    </x-secondary-button>

                    @if($attachment)
                        <div class="text-sm text-gray-600">
                            Archivo seleccionado: {{ $attachment->getClientOriginalName() }}
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
</div>