<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">{{ $order->order_number }}</h3>
            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5
                {{ $order->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $order->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                {{ $order->status === 'shipped' ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $order->status === 'delivered' ? 'bg-purple-100 text-purple-800' : '' }}
            ">
                {{ ucfirst($order->status) }}
            </span>
        </div>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <p class="text-sm text-gray-500">Proveedor</p>
                <p class="text-sm font-medium">{{ $order->vendor_id ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Fecha</p>
                <p class="text-sm font-medium">{{ $order->order_date ? $order->order_date->format('d/m/Y') : 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total</p>
                <p class="text-sm font-medium">{{ $order->total ? number_format($order->total, 2) : 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Productos</p>
                <p class="text-sm font-medium">{{ $order->products->count() }}</p>
            </div>
        </div>
        <div class="flex justify-end space-x-2">
            <a href="/purchase-orders/{{ $order->id }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Ver detalles
            </a>
            <a href="/purchase-orders/{{ $order->id }}/edit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Editar
            </a>
        </div>
    </div>
</div>
