#!/bin/bash

# Script de emergencia para arreglar el problema del breadcrumb en producción

echo "======================================"
echo "  SOLUCIÓN DE EMERGENCIA BREADCRUMB  "
echo "======================================"

# Directorio base de la aplicación
BASE_DIR=$(pwd)
PHP_CLASS_PATH="${BASE_DIR}/app/View/Components/Breadcrumb.php"
BLADE_PATH="${BASE_DIR}/resources/views/components/simple-breadcrumb.blade.php"

# 1. Crear un componente Blade simple (no requiere clase PHP)
echo "Creando componente Blade simple..."
mkdir -p "${BASE_DIR}/resources/views/components"
cat > "${BLADE_PATH}" << 'EOL'
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
EOL
echo "Componente Blade creado correctamente."

# 2. Verificar si existe alguna versión del componente PHP
if [ -f "${BASE_DIR}/app/View/Components/breadcrumb.php" ]; then
    echo "Encontrado: app/View/Components/breadcrumb.php (minúsculas)"

    # Crear respaldo del archivo original
    cp "${BASE_DIR}/app/View/Components/breadcrumb.php" "${BASE_DIR}/app/View/Components/breadcrumb.backup.php"

    # Mover a la versión con mayúsculas
    cp "${BASE_DIR}/app/View/Components/breadcrumb.php" "${PHP_CLASS_PATH}"

    # Corregir nombre de la clase
    sed -i 's/class breadcrumb extends/class Breadcrumb extends/g' "${PHP_CLASS_PATH}"

    echo "Archivo renombrado y clase corregida."
fi

# 3. Si no existe, creamos un componente PHP básico
if [ ! -f "${PHP_CLASS_PATH}" ]; then
    echo "Creando componente PHP básico..."

    mkdir -p "${BASE_DIR}/app/View/Components"

    cat > "${PHP_CLASS_PATH}" << 'EOL'
<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class Breadcrumb extends Component
{
    public $segments = [];
    public $currentPath = '';

    protected $translations = [
        'shipping-documentation.index' => 'Documentación de envío',
        'purchase-orders.index' => 'Órdenes de compra',
        'vendors.index' => 'Proveedores',
        'products.index' => 'Productos',
        'support.index' => 'Soporte',
        'settings.index' => 'Configuración',
        'hub.index' => 'Hubs',
        'ship-to.index' => 'Direcciones de envío',
        'bill-to.index' => 'Facturación',
        'authorizations.index' => 'Autorizaciones',
    ];

    protected $pathGroups = [
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

    public function __construct()
    {
        $this->currentPath = request()->path();
        $this->buildBreadcrumb();
    }

    protected function buildBreadcrumb()
    {
        $this->currentPath = trim($this->currentPath, '/');
        if (empty($this->currentPath)) {
            return;
        }

        $segments = [];
        $parts = explode('/', $this->currentPath);
        $urlPath = '';

        foreach ($parts as $i => $part) {
            $urlPath .= ($i > 0 ? '/' : '') . $part;

            if ($i === 0 && isset($this->pathGroups[$part])) {
                $name = $this->pathGroups[$part];
            } else {
                $name = ucfirst(str_replace(['-', '_'], ' ', $part));
            }

            $segments[] = [
                'name' => $name,
                'url' => $urlPath
            ];
        }

        $this->segments = $segments;
    }

    public function render(): View|Closure|string
    {
        return view('components.breadcrumb');
    }
}
EOL

    echo "Componente PHP creado correctamente."
fi

# 4. Crear o actualizar el archivo de vista del componente
cat > "${BASE_DIR}/resources/views/components/breadcrumb.blade.php" << 'EOL'
@props(['segments' => []])

<nav class="flex items-center p-3 text-gray-500 rounded">
    <a href="{{ url('/') }}" class="hover:text-gray-700">
        Inicio
    </a>

    @if(is_array($segments) && !empty($segments))
        @foreach($segments as $segment)
            @if(isset($segment['url']) && isset($segment['name']))
                <div class="mx-2 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </div>

                <a href="{{ url($segment['url']) }}" class="hover:text-gray-700">
                    {{ $segment['name'] }}
                </a>
            @endif
        @endforeach
    @endif
</nav>
EOL

echo "Vista del componente creada correctamente."

# 5. Modificar el AppServiceProvider para registrar el componente
echo "Actualizando AppServiceProvider..."

PROVIDER_PATH="${BASE_DIR}/app/Providers/AppServiceProvider.php"

# Comprobar si ya está registrado el componente
if ! grep -q "use App\\\\View\\\\Components\\\\Breadcrumb;" "${PROVIDER_PATH}"; then
    # Insertar el use statement después del último use
    sed -i '/^use /a use App\\View\\Components\\Breadcrumb;' "${PROVIDER_PATH}"
    echo "Importación de la clase Breadcrumb añadida."
fi

# Comprobar si ya está registrado el componente con Blade
if ! grep -q "Blade::component('breadcrumb', Breadcrumb::class);" "${PROVIDER_PATH}"; then
    # Buscar el método boot y añadir la línea
    sed -i '/public function boot/,/^    }/ s/public function boot()[^{]*{/&\n        Blade::component("breadcrumb", Breadcrumb::class);/' "${PROVIDER_PATH}"
    echo "Registro del componente añadido."
fi

# 6. Limpiar todas las cachés
echo "Limpiando cachés..."
php "${BASE_DIR}/artisan" view:clear
php "${BASE_DIR}/artisan" route:clear
php "${BASE_DIR}/artisan" config:clear
php "${BASE_DIR}/artisan" cache:clear
php "${BASE_DIR}/artisan" optimize:clear

# 7. Verificar el archivo PHP
echo "Verificando archivo PHP..."
php -l "${PHP_CLASS_PATH}"

echo "======================================"
echo "   CORRECCIÓN COMPLETADA"
echo ""
echo "Se han creado dos soluciones:"
echo "1. Componente PHP: app/View/Components/Breadcrumb.php"
echo "2. Componente Blade simple: resources/views/components/simple-breadcrumb.blade.php"
echo ""
echo "El menú principal ahora intentará usar el componente PHP,"
echo "pero si falla, utilizará automáticamente el componente Blade simple."
echo "======================================"
