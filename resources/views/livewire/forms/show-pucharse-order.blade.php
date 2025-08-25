<div>
    <div class="flex max-w-[1254px] items-center justify-between">
        <x-view-title>
            <x-slot:title>
                Detalles de Orden de compra
            </x-slot:title>

            <x-slot:content>
                Información completa de la orden de compra
            </x-slot:content>
        </x-view-title>

        <div class="flex space-x-4">
            <x-black-btn onclick="window.print()">Imprimir</x-black-btn>
            <a href="{{ route('purchase-orders.edit', $purchaseOrder->id) }}">
                <x-black-btn>Editar</x-black-btn>
            </a>
        </div>
    </div>

    <div class="mt-8 space-y-8">
        <!-- Información general -->
        <div class="p-6 bg-white rounded-lg shadow">
            <h2 class="mb-4 text-xl font-semibold">Información General</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Número de Orden</p>
                    <p class="text-lg">{{ $purchaseOrder->order_number }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Estado</p>
                    <p class="text-lg">{{ ucfirst($purchaseOrder->status) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Fecha de Orden</p>
                    <p class="text-lg">{{ $purchaseOrder->order_date ? $purchaseOrder->order_date->format('d/m/Y') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Moneda</p>
                    <p class="text-lg">{{ $purchaseOrder->currency ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Incoterms</p>
                    <p class="text-lg">{{ $purchaseOrder->incoterms ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Términos de Pago</p>
                    <p class="text-lg">{{ $purchaseOrder->payment_terms ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Información de Vendor -->
        <div class="p-6 bg-white rounded-lg shadow">
            <h2 class="mb-4 text-xl font-semibold">Información del Vendor</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">ID Vendor</p>
                    <p class="text-lg">{{ $purchaseOrder->vendor_id ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Dirección</p>
                    <p class="text-lg">{{ $purchaseOrder->vendor_direccion ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Código Postal</p>
                    <p class="text-lg">{{ $purchaseOrder->vendor_codigo_postal ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">País</p>
                    <p class="text-lg">{{ $purchaseOrder->vendor_pais ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Estado</p>
                    <p class="text-lg">{{ $purchaseOrder->vendor_estado ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Teléfono</p>
                    <p class="text-lg">{{ $purchaseOrder->vendor_telefono ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Información de Ship To -->
        <div class="p-6 bg-white rounded-lg shadow">
            <h2 class="mb-4 text-xl font-semibold">Información de Envío</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Dirección</p>
                    <p class="text-lg">{{ $purchaseOrder->ship_to_direccion ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Código Postal</p>
                    <p class="text-lg">{{ $purchaseOrder->ship_to_codigo_postal ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">País</p>
                    <p class="text-lg">{{ $purchaseOrder->ship_to_pais ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Estado</p>
                    <p class="text-lg">{{ $purchaseOrder->ship_to_estado ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Teléfono</p>
                    <p class="text-lg">{{ $purchaseOrder->ship_to_telefono ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Información de Facturación -->
        <div class="p-6 bg-white rounded-lg shadow">
            <h2 class="mb-4 text-xl font-semibold">Información de Facturación</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Dirección</p>
                    <p class="text-lg">{{ $purchaseOrder->bill_to_direccion ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Código Postal</p>
                    <p class="text-lg">{{ $purchaseOrder->bill_to_codigo_postal ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">País</p>
                    <p class="text-lg">{{ $purchaseOrder->bill_to_pais ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Estado</p>
                    <p class="text-lg">{{ $purchaseOrder->bill_to_estado ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Teléfono</p>
                    <p class="text-lg">{{ $purchaseOrder->bill_to_telefono ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Productos -->
        <div class="p-6 bg-white rounded-lg shadow">
            <h2 class="mb-4 text-xl font-semibold">Productos</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">ID</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Descripción</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Precio unitario</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Cantidad</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($orderProducts as $product)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $product['material_id'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $product['description'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ number_format($product['price_per_unit'], 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $product['quantity'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ number_format($product['subtotal'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-sm text-center text-gray-500">No hay productos asociados a esta orden</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-sm font-medium text-right text-gray-900">Total Neto:</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">{{ number_format($net_total, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-sm font-medium text-right text-gray-900">Costo Adicional:</td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{{ number_format($additional_cost, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-sm font-medium text-right text-gray-900">Costo de Seguro:</td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{{ number_format($insurance_cost, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-sm font-bold text-right text-gray-900">TOTAL:</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900 whitespace-nowrap">{{ number_format($total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Dimensiones -->
        <div class="p-6 bg-white rounded-lg shadow">
            <h2 class="mb-4 text-xl font-semibold">Dimensiones</h2>
            <div class="grid grid-cols-4 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Alto (cm)</p>
                    <p class="text-lg">{{ $purchaseOrder->height_cm ? number_format($purchaseOrder->height_cm, 2) : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Ancho (cm)</p>
                    <p class="text-lg">{{ $purchaseOrder->width_cm ? number_format($purchaseOrder->width_cm, 2) : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Largo (cm)</p>
                    <p class="text-lg">{{ $purchaseOrder->length_cm ? number_format($purchaseOrder->length_cm, 2) : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Volumen (m³)</p>
                    <p class="text-lg">{{ $purchaseOrder->volume_m3 ? number_format($purchaseOrder->volume_m3, 3) : 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Fechas -->
        <div class="p-6 bg-white rounded-lg shadow">
            <h2 class="mb-4 text-xl font-semibold">Fechas</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Fecha de entrega solicitada</p>
                    <p class="text-lg">{{ $purchaseOrder->requested_delivery_date ? $purchaseOrder->requested_delivery_date->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Fecha estimada de recogida</p>
                    <p class="text-lg">{{ $purchaseOrder->estimated_pickup_date ? $purchaseOrder->estimated_pickup_date->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Fecha real de recogida</p>
                    <p class="text-lg">{{ $purchaseOrder->actual_pickup_date ? $purchaseOrder->actual_pickup_date->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Llegada estimada al hub</p>
                    <p class="text-lg">{{ $purchaseOrder->estimated_hub_arrival ? $purchaseOrder->estimated_hub_arrival->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Llegada real al hub</p>
                    <p class="text-lg">{{ $purchaseOrder->actual_hub_arrival ? $purchaseOrder->actual_hub_arrival->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">ETD (Fecha estimada de salida)</p>
                    <p class="text-lg">{{ $purchaseOrder->etd_date ? $purchaseOrder->etd_date->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">ATD (Fecha real de salida)</p>
                    <p class="text-lg">{{ $purchaseOrder->atd_date ? $purchaseOrder->atd_date->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">ETA (Fecha estimada de llegada)</p>
                    <p class="text-lg">{{ $purchaseOrder->eta_date ? $purchaseOrder->eta_date->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">ATA (Fecha real de llegada)</p>
                    <p class="text-lg">{{ $purchaseOrder->ata_date ? $purchaseOrder->ata_date->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
