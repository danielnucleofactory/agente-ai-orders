# Sistema de Permisos - Guía de Implementación

## Resumen

Este documento describe la implementación completa de un sistema de permisos granular para la aplicación Laravel usando `spatie/laravel-permission` versión 6.16.

## Estructura de Permisos Implementada

### Convención de Nomenclatura
- `has_view_[recurso]`: Permite ver listados/índices
- `has_create_[recurso]`: Permite crear nuevos elementos
- `has_show_[recurso]`: Permite ver detalles específicos
- `has_edit_[recurso]`: Permite editar elementos existentes
- `has_delete_[recurso]`: Permite eliminar elementos
- `has_[accion]_[contexto]`: Para acciones específicas

### Permisos por Módulo

#### Dashboard
- `has_view_dashboard`: Ver dashboard principal

#### Órdenes de Compra
- `has_create_orders`: Crear órdenes de compra
- `has_view_orders`: Ver listado de órdenes
- `has_show_orders`: Ver detalles de órdenes
- `has_edit_orders`: Editar órdenes
- `has_delete_orders`: Eliminar órdenes
- `has_view_tracking`: Ver seguimiento
- `has_view_consolidated_orders`: Ver órdenes consolidadas
- `has_view_kanban`: Ver tableros Kanban
- `has_manage_kanban`: Gestionar tableros Kanban
- `has_comment_orders`: Agregar comentarios

#### Productos
- `has_view_products`: Ver listado de productos
- `has_create_products`: Crear productos
- `has_show_products`: Ver detalles de productos
- `has_edit_products`: Editar productos
- `has_delete_products`: Eliminar productos

#### Forecast
- `has_view_forecast`: Ver forecast de materiales
- `has_view_forecast_graph`: Ver gráficos de forecast
- `has_view_forecast_table`: Ver tabla de forecast
- `has_edit_forecast`: Editar forecast

#### Y más... (ver PermissionsSeeder.php para la lista completa)

## Roles Predefinidos

### Super Administrador
- Todos los permisos del sistema

### Administrador
- Permisos de gestión sin eliminar usuarios/roles críticos
- Acceso a configuraciones y gestión operativa

### Operador
- Permisos operativos sin gestión de usuarios/configuraciones
- Crear y editar órdenes, productos, etc.

### Lector
- Solo permisos de visualización
- Exportar datos y ver reportes

### Aprobador
- Permisos de visualización + aprobaciones
- Aprobar/rechazar solicitudes y autorizaciones

## Archivos Implementados

### 1. Seeder de Permisos
**Archivo:** `database/seeders/PermissionsSeeder.php`
- Define todos los permisos del sistema
- Crea roles predefinidos con sus permisos correspondientes
- Organiza permisos por módulos funcionales

### 2. Middleware de Permisos
**Archivo:** `app/Http/Middleware/CheckPermission.php`
- Verifica permisos específicos en rutas
- Redirige a login si no está autenticado
- Retorna 403 si no tiene permisos

### 3. Service Provider
**Archivo:** `app/Providers/AuthServiceProvider.php`
- Registra gates dinámicamente basados en permisos
- Maneja errores durante migraciones

### 4. Componentes Livewire Actualizados
**Archivos:**
- `app/Livewire/Settings/RoleCreate.php`
- `app/Livewire/Settings/RoleEdit.php`
- `resources/views/livewire/settings/role-create.blade.php`
- `resources/views/livewire/settings/role-edit.blade.php`

### 5. Sidebar con Permisos
**Archivo:** `resources/views/livewire/partials/main-sidebar.blade.php`
- Implementa directivas `@can` para mostrar/ocultar enlaces
- Agrupa enlaces por permisos relacionados

### 6. Rutas Protegidas
**Archivo:** `routes/web.php`
- Aplica middleware de permisos a rutas específicas
- Ejemplos de protección por acción

### 7. Comando Artisan
**Archivo:** `app/Console/Commands/AssignPermissionsCommand.php`
- Asigna roles a usuarios desde línea de comandos

## Instrucciones de Implementación

### Paso 1: Ejecutar Migraciones y Seeders

```bash
# Ejecutar migraciones de permisos (ya existentes)
php artisan migrate

# Ejecutar seeder de permisos
php artisan db:seed --class=PermissionsSeeder
```

### Paso 2: Limpiar Cache de Permisos

```bash
php artisan permission:cache-reset
```

### Paso 3: Asignar Roles a Usuarios Existentes

```bash
# Asignar rol de Super Administrador al usuario con ID 1
php artisan permissions:assign 1 "Super Administrador"

# Asignar rol de Administrador al usuario con ID 2
php artisan permissions:assign 2 "Administrador"

# Asignar rol de Operador al usuario con ID 3
php artisan permissions:assign 3 "Operador"
```

### Paso 4: Verificar Configuración

1. **Verificar que el modelo User tiene el trait:**
   ```php
   use Spatie\Permission\Traits\HasRoles;

   class User extends Authenticatable
   {
       use HasRoles;
       // ...
   }
   ```

2. **Verificar middleware registrado en bootstrap/app.php:**
   ```php
   $middleware->alias([
       'permission' => \App\Http\Middleware\CheckPermission::class,
   ]);
   ```

## Uso en el Código

### En Rutas
```php
// Proteger una ruta específica
Route::get('/products', ProductController::class)
    ->middleware('permission:has_view_products');

// Proteger un grupo de rutas
Route::middleware(['auth', 'permission:has_view_orders'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
});
```

### En Vistas Blade
```blade
{{-- Mostrar enlace solo si tiene permiso --}}
@can('has_create_orders')
    <a href="{{ route('orders.create') }}">Crear Orden</a>
@endcan

{{-- Mostrar contenido alternativo --}}
@can('has_edit_orders')
    <button>Editar</button>
@else
    <span>Solo lectura</span>
@endcan

{{-- Verificar múltiples permisos --}}
@if(auth()->user()->can('has_view_orders') || auth()->user()->can('has_create_orders'))
    <div>Sección de órdenes</div>
@endif
```

### En Controladores
```php
class OrderController extends Controller
{
    public function index()
    {
        // Verificar permiso programáticamente
        if (!auth()->user()->can('has_view_orders')) {
            abort(403, 'No tienes permisos para ver órdenes');
        }

        // O usar el helper authorize
        $this->authorize('has_view_orders');

        return view('orders.index');
    }

    public function create()
    {
        $this->authorize('has_create_orders');
        return view('orders.create');
    }
}
```

### En Componentes Livewire
```php
class OrdersList extends Component
{
    public function mount()
    {
        // Verificar permisos en el mount
        if (!auth()->user()->can('has_view_orders')) {
            abort(403);
        }
    }

    public function createOrder()
    {
        // Verificar permiso antes de la acción
        $this->authorize('has_create_orders');

        // Lógica para crear orden
    }
}
```

## Gestión de Permisos

### Crear Nuevos Permisos
```php
use Spatie\Permission\Models\Permission;

// Crear un nuevo permiso
Permission::create(['name' => 'has_new_feature']);

// Asignar a un rol
$role = Role::findByName('Administrador');
$role->givePermissionTo('has_new_feature');
```

### Asignar Permisos a Usuarios
```php
use App\Models\User;

$user = User::find(1);

// Asignar rol
$user->assignRole('Administrador');

// Asignar permiso directo
$user->givePermissionTo('has_special_access');

// Verificar permisos
if ($user->can('has_view_orders')) {
    // Usuario tiene el permiso
}

// Verificar rol
if ($user->hasRole('Administrador')) {
    // Usuario tiene el rol
}
```

## Comandos Útiles

```bash
# Limpiar cache de permisos
php artisan permission:cache-reset

# Asignar rol a usuario
php artisan permissions:assign {user_id} {role_name}

# Ver todos los permisos
php artisan tinker
>>> \Spatie\Permission\Models\Permission::all()->pluck('name');

# Ver todos los roles
>>> \Spatie\Permission\Models\Role::with('permissions')->get();
```

## Consideraciones de Seguridad

1. **Principio de Menor Privilegio**: Asigna solo los permisos mínimos necesarios
2. **Validación en Backend**: Siempre valida permisos en el servidor, no solo en frontend
3. **Auditoría**: Considera implementar logs de cambios de permisos
4. **Roles Jerárquicos**: Los roles están diseñados con niveles de acceso progresivos

## Troubleshooting

### Error: "Permission does not exist"
```bash
# Limpiar cache y re-ejecutar seeder
php artisan permission:cache-reset
php artisan db:seed --class=PermissionsSeeder
```

### Error: "Role does not exist"
```bash
# Verificar que los roles fueron creados
php artisan tinker
>>> \Spatie\Permission\Models\Role::all();
```

### Permisos no se aplican
```bash
# Verificar que el usuario tiene el rol asignado
php artisan tinker
>>> $user = \App\Models\User::find(1);
>>> $user->roles;
>>> $user->permissions;
```

## Extensión del Sistema

Para agregar nuevos módulos:

1. **Definir permisos** en `PermissionsSeeder.php`
2. **Actualizar roles** con los nuevos permisos
3. **Proteger rutas** con middleware
4. **Actualizar vistas** con directivas `@can`
5. **Actualizar sidebar** si es necesario

Este sistema proporciona una base sólida y escalable para la gestión de permisos en la aplicación.
