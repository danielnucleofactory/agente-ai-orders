# Guía: Crear un Nuevo Módulo Interno

Esta guía describe paso a paso cómo crear un nuevo módulo en la carpeta `internal_modules/` para integrarlo en la aplicación.

---

## 1. Crear la estructura de carpetas

```bash
mkdir -p internal_modules/mi-modulo/{config,database/migrations,resources/views,routes,src/Http/Controllers,src/Models,src/Services}
```

## 2. Archivos mínimos del módulo

### `internal_modules/mi-modulo/config/mi-modulo.php`

```php
<?php

return [
    'enabled' => env('MI_MODULO_ENABLED', false),
    // Agrega más opciones según necesites
];
```

### `internal_modules/mi-modulo/src/MiModuloServiceProvider.php`

```php
<?php

namespace MiApp\MiModulo;

use Illuminate\Support\ServiceProvider;

class MiModuloServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mi-modulo.php', 'mi-modulo');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'mi-modulo');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Lógica condicional solo si está habilitado
        if (!config('mi-modulo.enabled', false)) {
            return;
        }
        // Registrar listeners, schedule, etc.
    }
}
```

### `internal_modules/mi-modulo/routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use MiApp\MiModulo\Http\Controllers\MiModuloController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('mi-modulo/settings')->group(function () {
        Route::get('/', [MiModuloController::class, 'index'])->name('mi-modulo.settings.index');
    });
});
```

### `internal_modules/mi-modulo/resources/views/settings/index.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold">Configuración de Mi Módulo</h1>
    <p class="mt-2 text-gray-600">Aquí va el contenido del módulo.</p>
</div>
@endsection
```

### `internal_modules/mi-modulo/composer.json`

```json
{
    "name": "mi-app/mi-modulo",
    "description": "Módulo de ejemplo",
    "type": "library",
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0"
    },
    "autoload": {
        "psr-4": {
            "MiApp\\MiModulo\\": "src/"
        }
    }
}
```

## 3. Integrar el módulo en la aplicación

### 3.1 `composer.json` (raíz del proyecto)

En la sección `autoload.psr-4`:

```json
"MiApp\\MiModulo\\": "internal_modules/mi-modulo/src/"
```

Luego ejecutar: `composer dump-autoload`

### 3.2 `app/Providers/ModuleServiceProvider.php`

Agregar en el array `$modules`:

```php
'mi_modulo' => [
    'enabled' => env('MI_MODULO_ENABLED', false),
    'path' => 'internal_modules/mi-modulo',
    'provider' => 'MiApp\MiModulo\MiModuloServiceProvider',
    'config' => 'mi-modulo',
    'migrations' => true,
    'routes' => true,
    'views' => true,
],
```

Y en `getModulesConfig()`:

```php
$modules['mi_modulo']['enabled'] = env('MI_MODULO_ENABLED', false);
```

### 3.3 `.env.example`

```
MI_MODULO_ENABLED=false
```

### 3.4 Sidebar (menú)

En `resources/views/livewire/partials/main-sidebar.blade.php`, dentro del dropdown "Configuraciones":

```blade
@if(config('mi-modulo.enabled', false))
<li>
    <x-sidebar-dropdown-item href="{{ route('mi-modulo.settings.index') }}" :active="request()->routeIs('mi-modulo.settings.*')">
        Mi Módulo
    </x-sidebar-dropdown-item>
</li>
@endif
```

### 3.5 `tailwind.config.js` (si aplica)

En `content`:

```js
'./internal_modules/**/resources/views/**/*.blade.php',
```

## 4. Activar el módulo

```bash
# Opción 1: Editar .env manualmente
MI_MODULO_ENABLED=true

# Opción 2: Comando Artisan (si existe module:manage)
php artisan module:manage enable mi_modulo
```

## 5. Ejecutar migraciones del módulo

```bash
php artisan module:manage install mi_modulo
# o manualmente:
php artisan migrate --path=internal_modules/mi-modulo/database/migrations
```

---

## Checklist rápido

- [ ] Estructura de carpetas creada
- [ ] Config con `enabled` => env()
- [ ] ServiceProvider del módulo
- [ ] Rutas web.php
- [ ] Vista index
- [ ] composer.json del módulo
- [ ] Autoload en composer.json principal
- [ ] Registro en ModuleServiceProvider
- [ ] Variable en .env.example
- [ ] Item en sidebar
- [ ] Tailwind content (si aplica)
- [ ] `composer dump-autoload`
- [ ] Activar en .env y probar
