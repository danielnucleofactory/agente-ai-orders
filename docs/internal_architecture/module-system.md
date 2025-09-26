# Sistema de Módulos Internos

Este documento explica cómo usar el nuevo sistema de módulos internos que permite activar/desactivar funcionalidades sin necesidad de instalar/desinstalar packages de Composer.

## ¿Por qué este sistema?

El sistema tradicional de packages de Composer tiene algunas limitaciones para desarrollo interno:
- Requiere instalar/desinstalar el package cada vez que cambias de repositorio
- No es flexible para activar/desactivar funcionalidades rápidamente
- Puede causar conflictos de dependencias

## Arquitectura del Sistema

### 1. ModuleServiceProvider
El `ModuleServiceProvider` es el núcleo del sistema. Se encarga de:
- Detectar qué módulos están activos desde variables de entorno
- Cargar dinámicamente Service Providers, rutas, vistas y configuraciones
- Gestionar la inicialización de módulos

### 2. Comando Artisan `module:manage`
Proporciona una interfaz de línea de comandos para:
- Listar módulos disponibles
- Activar/desactivar módulos
- Ver el estado de los módulos
- Instalar módulos (ejecutar migraciones, etc.)

## Módulos Disponibles

### PO Confirmation
- **Nombre interno**: `po_confirmation`
- **Ruta**: `laravel-po-confirmation/`
- **Variable de entorno**: `PO_CONFIRMATION_ENABLED`
- **Descripción**: Módulo de confirmación de órdenes de compra via email

## Uso del Sistema

### 1. Activar un Módulo

```bash
# Activar el módulo PO Confirmation
php artisan module:manage enable po_confirmation

# Esto actualiza automáticamente el archivo .env
# PO_CONFIRMATION_ENABLED=true
```

### 2. Desactivar un Módulo

```bash
# Desactivar el módulo PO Confirmation
php artisan module:manage disable po_confirmation

# Esto actualiza automáticamente el archivo .env
# PO_CONFIRMATION_ENABLED=false
```

### 3. Ver Estado de Módulos

```bash
# Listar todos los módulos
php artisan module:manage list

# Ver estado de un módulo específico
php artisan module:manage status po_confirmation
```

### 4. Instalar un Módulo

```bash
# Instalar un módulo (ejecuta migraciones, etc.)
php artisan module:manage install po_confirmation
```

## Variables de Entorno

### PO Confirmation
```env
# Activar/desactivar el módulo
PO_CONFIRMATION_ENABLED=true

# Configuración del hash de confirmación
PO_CONFIRMATION_HASH_EXPIRY=72

# Configuración de emails
PO_CONFIRMATION_FROM_NAME="Raga Orders"
PO_CONFIRMATION_FROM_ADDRESS=noreply@ragaorders.com

# Automatización
PO_CONFIRMATION_AUTO_SEND=true
PO_CONFIRMATION_CHECK_INTERVAL=hourly

# Notificaciones
PO_CONFIRMATION_NOTIFY_ADMIN=true
PO_CONFIRMATION_ADMIN_EMAIL=admin@ragaorders.com
```

## Flujo de Trabajo Recomendado

### 1. Desarrollo Local
```bash
# Activar el módulo
php artisan module:manage enable po_confirmation

# Instalar (si es la primera vez)
php artisan module:manage install po_confirmation

# Reiniciar la aplicación (opcional, pero recomendado)
php artisan config:clear
php artisan route:clear
```

### 2. Cambio de Repositorio
```bash
# Desactivar antes de cambiar
php artisan module:manage disable po_confirmation

# Cambiar de repositorio...

# Reactivar en el nuevo repositorio
php artisan module:manage enable po_confirmation
```

### 3. Verificación
```bash
# Verificar que el módulo esté activo
php artisan module:manage status po_confirmation

# Listar todos los módulos activos
php artisan module:manage list
```

## Agregar Nuevos Módulos

Para agregar un nuevo módulo al sistema:

### 1. Actualizar ModuleServiceProvider
```php
protected array $modules = [
    'po_confirmation' => [
        'enabled' => false,
        'path' => 'laravel-po-confirmation',
        'provider' => 'RagaOrders\\POConfirmation\\POConfirmationServiceProvider',
        'config' => 'po-confirmation',
        'migrations' => true,
        'routes' => true,
        'views' => true,
    ],
    'nuevo_modulo' => [
        'enabled' => false,
        'path' => 'laravel-nuevo-modulo',
        'provider' => 'App\\Providers\\NuevoModuloServiceProvider',
        'config' => 'nuevo-modulo',
        'migrations' => true,
        'routes' => true,
        'views' => true,
    ],
];
```

### 2. Actualizar getModulesConfig()
```php
protected function getModulesConfig(): array
{
    $modules = $this->modules;
    
    // Configurar el estado de cada módulo desde .env
    $modules['po_confirmation']['enabled'] = env('PO_CONFIRMATION_ENABLED', false);
    $modules['nuevo_modulo']['enabled'] = env('NUEVO_MODULO_ENABLED', false);
    
    return $modules;
}
```

### 3. Agregar Variables de Entorno
```env
NUEVO_MODULO_ENABLED=true
```

## Ventajas del Sistema

1. **Flexibilidad**: Activar/desactivar módulos sin reinstalar
2. **Control**: Gestión centralizada desde variables de entorno
3. **Desarrollo**: Fácil cambio entre repositorios
4. **Mantenimiento**: No hay conflictos de dependencias
5. **Escalabilidad**: Fácil agregar nuevos módulos

## Consideraciones

1. **Reinicio**: Después de activar/desactivar módulos, es recomendable reiniciar la aplicación
2. **Dependencias**: Los módulos deben ser independientes entre sí
3. **Configuración**: Cada módulo debe tener su propio archivo de configuración
4. **Migraciones**: Las migraciones se ejecutan automáticamente al instalar

## Troubleshooting

### Módulo no se activa
```bash
# Verificar variable de entorno
php artisan module:manage status po_confirmation

# Verificar logs
tail -f storage/logs/laravel.log | grep "Módulo"
```

### Error en migraciones
```bash
# Verificar que el módulo esté activo
php artisan module:manage status po_confirmation

# Ejecutar migraciones manualmente
php artisan migrate --path=laravel-po-confirmation/database/migrations
```

### Rutas no funcionan
```bash
# Limpiar cache de rutas
php artisan route:clear

# Verificar que el módulo esté activo
php artisan module:manage status po_confirmation
```
