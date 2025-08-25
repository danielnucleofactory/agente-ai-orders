<nav class="flex items-center p-3 text-gray-500 rounded">
    <a href="{{ url('/') }}" class="hover:text-gray-700">
        Inicio
    </a>

    @if(request()->path() !== '/')
        <div class="mx-2 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                <path d="m9 18 6-6-6-6"/>
            </svg>
        </div>

        <a href="{{ url(request()->path()) }}" class="hover:text-gray-700">
            @php
                $path = request()->path();
                $segments = explode('/', $path);
                $lastSegment = end($segments);

                // Traducciones específicas
                $translations = [
                    'shipping-documentation' => 'Documentación de envío',
                    'purchase-orders' => 'Órdenes de compra',
                    'vendors' => 'Proveedores',
                    'products' => 'Productos',
                    'support' => 'Soporte',
                    'settings' => 'Configuración',
                    'hub' => 'Hubs',
                    'ship-to' => 'Direcciones de envío',
                    'bill-to' => 'Facturación',
                    'authorizations' => 'Autorizaciones',
                ];

                echo $translations[$lastSegment] ?? ucfirst(str_replace(['-', '_'], ' ', $lastSegment));
            @endphp
        </a>
    @endif
</nav>
