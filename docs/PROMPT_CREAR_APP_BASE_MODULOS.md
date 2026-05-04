# Prompt Inicial: Crear App Base con Sistema de Módulos Internos

Usa este prompt al iniciar un nuevo proyecto Laravel para que la aplicación base incluya el sistema de módulos internos, activación en menú, comandos de compilación y una sección para desarrollar nuevos módulos.

---

## PROMPT PARA EL ASISTENTE

```
Necesito crear una aplicación Laravel base con el siguiente sistema de módulos internos. La app debe ser funcional desde el inicio con:

### 1. ESTRUCTURA DE MÓDULOS INTERNOS

- Carpeta `internal_modules/` en la raíz del proyecto (excluida de git con .gitignore)
- Cada módulo vive en `internal_modules/{nombre-modulo}/` con esta estructura:

```
internal_modules/{nombre-modulo}/
├── config/                    # Configuración del módulo
│   └── {nombre}.php          # Debe incluir 'enabled' => env('MODULO_ENABLED', false)
├── database/
│   └── migrations/           # Migraciones del módulo
├── resources/
│   └── views/               # Vistas Blade del módulo
├── routes/
│   ├── web.php              # Rutas web
│   └── api.php              # Rutas API (opcional)
├── src/
│   ├── {Nombre}ServiceProvider.php
│   ├── Http/Controllers/
│   ├── Models/
│   ├── Services/
│   └── helpers.php          # Opcional: funciones helper
└── composer.json            # Namespace PSR-4 del módulo
```

### 2. ACTIVACIÓN EN EL MENÚ (SIDEBAR)

- El sidebar debe tener una sección "Configuraciones" con items condicionales por módulo
- Cada módulo solo aparece en el menú si está habilitado: `@if(config('nombre-modulo.enabled', false))`
- Ejemplo de item en el menú:
```blade
@if(config('mi-modulo.enabled', false))
<li>
    <x-sidebar-dropdown-item href="{{ route('mi-modulo.settings.index') }}" :active="request()->routeIs('mi-modulo.settings.*')">
        Mi Módulo
    </x-sidebar-dropdown-item>
</li>
@endif
```

### 3. CONFIGURACIÓN DE ACTIVACIÓN

- Cada módulo se activa/desactiva mediante variables en `.env`:
  - Ejemplo: `MI_MODULO_ENABLED=true` o `MI_MODULO_ENABLED=false`
- Crear `ModuleServiceProvider` que:
  - Lea la configuración de módulos desde array con paths y providers
  - Registre autoloader PSR-4 para cada módulo en `internal_modules/`
  - Solo registre el ServiceProvider del módulo si `enabled=true`
  - Cargue rutas, vistas, migraciones y config cuando esté habilitado
- Registrar `ModuleServiceProvider` en `bootstrap/providers.php`
- Agregar en `composer.json` autoload.psr-4 para cada módulo:
  ```json
  "MiApp\\MiModulo\\": "internal_modules/mi-modulo/src/"
  ```

### 4. COMANDOS PARA COMPILAR Y EJECUTAR

Incluir en el README o documentación estos comandos:

**Instalación inicial:**
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
```

**Desarrollo:**
```bash
# Opción 1: Servidor + Vite por separado
php artisan serve
npm run dev

# Opción 2: Todo en uno (si existe script dev en composer)
composer dev
```

**Producción:**
```bash
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Módulos:**
```bash
# Activar módulo (actualiza .env)
php artisan module:manage enable nombre_modulo

# Desactivar módulo
php artisan module:manage disable nombre_modulo

# Instalar módulo (ejecutar migraciones)
php artisan module:manage install nombre_modulo

# Listar módulos
php artisan module:manage list
```

### 5. SECCIÓN "NUEVO MÓDULO" PARA EMPEZAR A DESARROLLAR

Crear una plantilla o documentación que permita iniciar un nuevo módulo. Incluir:

**A) Estructura mínima de carpetas y archivos** para copiar/crear:

- `internal_modules/nuevo-modulo/config/nuevo-modulo.php` con `'enabled' => env('NUEVO_MODULO_ENABLED', false)`
- `internal_modules/nuevo-modulo/src/NuevoModuloServiceProvider.php` que cargue vistas, rutas, migraciones
- `internal_modules/nuevo-modulo/routes/web.php` con rutas básicas
- `internal_modules/nuevo-modulo/database/migrations/` (puede estar vacío inicialmente)
- `internal_modules/nuevo-modulo/resources/views/` con una vista index básica
- `internal_modules/nuevo-modulo/composer.json` con namespace PSR-4

**B) Pasos para integrar el nuevo módulo en la app:**

1. Agregar en `composer.json` autoload: `"MiApp\\NuevoModulo\\": "internal_modules/nuevo-modulo/src/"`
2. Ejecutar `composer dump-autoload`
3. Registrar en `ModuleServiceProvider` el array de módulos:
   ```php
   'nuevo_modulo' => [
       'enabled' => env('NUEVO_MODULO_ENABLED', false),
       'path' => 'internal_modules/nuevo-modulo',
       'provider' => 'MiApp\\NuevoModulo\\NuevoModuloServiceProvider',
       'config' => 'nuevo-modulo',
       'migrations' => true,
       'routes' => true,
       'views' => true,
   ],
   ```
4. Agregar en `.env.example`: `NUEVO_MODULO_ENABLED=false`
5. Agregar item en el sidebar (Configuraciones) con `@if(config('nuevo-modulo.enabled', false))`
6. Incluir en `tailwind.config.js` (si usas Tailwind): `'./internal_modules/**/resources/views/**/*.blade.php'`

**C) Comando Artisan** `module:manage` para enable/disable/install/list de módulos.

### 6. TAILWIND / VITE

Si la app usa Tailwind, incluir en `tailwind.config.js` en content:
```js
'./internal_modules/**/resources/views/**/*.blade.php'
```

### 7. RESUMEN DE ARCHIVOS CLAVE A CREAR

- `app/Providers/ModuleServiceProvider.php` - Gestión central de módulos
- `app/Console/Commands/ModuleCommand.php` - Comando `module:manage`
- `bootstrap/providers.php` - Registrar ModuleServiceProvider
- `resources/views/livewire/partials/main-sidebar.blade.php` - Menú con items condicionales por módulo
- `internal_modules/` - Carpeta para módulos (en .gitignore)
- `docs/NUEVO_MODULO.md` - Guía paso a paso para crear un nuevo módulo

Genera la aplicación base con todo lo anterior. Si algo no está claro o necesitas más contexto del dominio (ej: órdenes, inventario, etc.), avísame.
```

---

## Notas de Implementación (referencia del proyecto olo-raga-orders)

- **ModuleServiceProvider**: Controla qué módulos se cargan según `enabled` en .env. Siempre registra el autoloader si el directorio existe; solo registra provider, rutas y boot si está habilitado.
- **Sidebar**: Los items de módulos van dentro del dropdown "Configuraciones", condicionados por `config('nombre-modulo.enabled', false)`.
- **Composer**: Los namespaces de módulos se agregan en `autoload.psr-4` del composer.json principal.
- **ServiceProvider del módulo**: Cada módulo tiene su propio ServiceProvider que hace `mergeConfigFrom`, `loadViewsFrom`, `loadRoutesFrom`, `loadMigrationsFrom`. La lógica condicional (ej: listeners, schedule) debe verificar `config('nombre-modulo.enabled')` antes de ejecutarse.
