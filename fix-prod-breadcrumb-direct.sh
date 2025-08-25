#!/bin/bash

# Script para solucionar específicamente el problema del breadcrumb en producción

echo "=== SOLUCIONANDO PROBLEMA DE BREADCRUMB ==="

# Verificar el entorno y establecer la ruta base
BASE_DIR=$(pwd)
echo "Trabajando en: $BASE_DIR"

# 1. ELIMINAR todas las versiones existentes (puede haber conflictos)
echo "Eliminando versiones existentes..."
rm -f "$BASE_DIR/app/View/Components/breadcrumb.php"
rm -f "$BASE_DIR/app/View/Components/Breadcrumb.php"
echo "✓ Archivos anteriores eliminados"

# 2. CREAR el archivo correcto con el nombre exacto (Breadcrumb.php)
echo "Creando nuevo archivo Breadcrumb.php con el nombre correcto..."
mkdir -p "$BASE_DIR/app/View/Components"

cat > "$BASE_DIR/app/View/Components/Breadcrumb.php" << 'EOL'
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

    // Traducciones para los segmentos del breadcrumb
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

    // Grupos de rutas base
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
        try {
            if ($this->currentPath === '/') {
                return;
            }

            // Para "shipping-documentation" usa una traducción directa
            if (strpos($this->currentPath, 'shipping-documentation') === 0) {
                $this->segments = [
                    [
                        'name' => 'Documentación de envío',
                        'url' => 'shipping-documentation'
                    ]
                ];
                return;
            }

            $pathParts = explode('/', trim($this->currentPath, '/'));
            $segments = [];
            $urlPath = '';

            foreach ($pathParts as $i => $part) {
                $urlPath .= ($i > 0 ? '/' : '') . $part;

                // Para el primer segmento (sección)
                if ($i === 0) {
                    if (isset($this->pathGroups[$part])) {
                        $name = $this->pathGroups[$part];
                    } else {
                        $name = ucfirst(str_replace(['-', '_'], ' ', $part));
                    }
                } else {
                    $name = ucfirst(str_replace(['-', '_'], ' ', $part));
                }

                $segments[] = [
                    'name' => $name,
                    'url' => $urlPath
                ];
            }

            $this->segments = $segments;
        } catch (\Exception $e) {
            // En caso de error, solo muestra Inicio
            $this->segments = [];
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.breadcrumb');
    }
}
EOL
echo "✓ Archivo creado correctamente"

# 3. CREAR la vista del componente
echo "Creando vista del componente..."
mkdir -p "$BASE_DIR/resources/views/components"

cat > "$BASE_DIR/resources/views/components/breadcrumb.blade.php" << 'EOL'
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
echo "✓ Vista creada correctamente"

# 4. MODIFICAR AppServiceProvider para registrar el componente
echo "Actualizando AppServiceProvider..."
SERVICE_PROVIDER="$BASE_DIR/app/Providers/AppServiceProvider.php"

# Si no existe la importación, agrégala
if ! grep -q "use App\\\\View\\\\Components\\\\Breadcrumb;" "$SERVICE_PROVIDER"; then
    # Insertar después de la última línea de importación use
    sed -i '/^use /a use App\\View\\Components\\Breadcrumb;' "$SERVICE_PROVIDER"
    echo "✓ Importación añadida"
fi

# Si no existe el registro del componente, agrégalo
if ! grep -q "Blade::component.*breadcrumb" "$SERVICE_PROVIDER"; then
    # Buscar el método boot y agregar la línea después de la apertura de la función
    sed -i '/public function boot/,/^    }/ s/public function boot()[^{]*{/&\n        Blade::component("breadcrumb", Breadcrumb::class);/' "$SERVICE_PROVIDER"
    echo "✓ Registro de componente añadido"
fi

# 5. LIMPIAR cachés
echo "Limpiando cachés..."
php "$BASE_DIR/artisan" view:clear
php "$BASE_DIR/artisan" config:clear
php "$BASE_DIR/artisan" route:clear
php "$BASE_DIR/artisan" cache:clear
php "$BASE_DIR/artisan" optimize:clear
echo "✓ Cachés limpiadas"

echo ""
echo "=== SOLUCIÓN COMPLETADA ==="
echo "El problema del breadcrumb debería estar resuelto."
echo "Si persiste el error, verifica en los logs:"
echo "tail -f $BASE_DIR/storage/logs/laravel.log"
echo ""
