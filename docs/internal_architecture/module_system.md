# 🚀 Sistema de Módulos Internos - Raga Orders

Este proyecto implementa un sistema de módulos internos que permite activar/desactivar funcionalidades sin necesidad de instalar/desinstalar packages de Composer.

## 🎯 ¿Por qué este sistema?

### Problema Tradicional
- ❌ Requiere instalar/desinstalar el package cada vez que cambias de repositorio
- ❌ No es flexible para activar/desactivar funcionalidades rápidamente
- ❌ Puede causar conflictos de dependencias
- ❌ Proceso engorroso para desarrollo interno

### Solución Implementada
- ✅ **Activación/desactivación instantánea** desde variables de entorno
- ✅ **Gestión centralizada** de módulos internos
- ✅ **Sin conflictos de dependencias** de Composer
- ✅ **Fácil cambio entre repositorios** sin reinstalar nada
- ✅ **Comandos Artisan** para gestión completa

## 🏗️ Arquitectura del Sistema

### Componentes Principales

1. **`ModuleServiceProvider`** - Núcleo del sistema
   - Detecta módulos activos desde `.env`
   - Carga dinámicamente providers, rutas, vistas y configuraciones
   - Gestiona la inicialización de módulos

2. **`ModuleCommand`** - Comando Artisan
   - Interfaz de línea de comandos para gestión
   - Activar/desactivar módulos
   - Ver estado y listar módulos disponibles
   - Instalar módulos (ejecutar migraciones)

3. **Script de Shell** - `scripts/manage-modules.sh`
   - Wrapper amigable para los comandos Artisan
   - Colores y mensajes informativos
   - Validaciones de seguridad

## 📦 Módulos Disponibles

### PO Confirmation
- **Nombre interno**: `po_confirmation`
- **Ruta**: `laravel-po-confirmation/`
- **Variable de entorno**: `PO_CONFIRMATION_ENABLED`
- **Descripción**: Módulo de confirmación de órdenes de compra via email
- **Funcionalidades**:
  - Detección automática de POs pendientes
  - Generación de URLs seguras con hash único
  - Envío de emails automáticos
  - Confirmación via web
  - Dashboard administrativo

## 🚀 Uso Rápido

### 1. Activar un Módulo
```bash
# Usando el comando Artisan
php artisan module:manage enable po_confirmation

# O usando el script de shell
./scripts/manage-modules.sh enable po_confirmation
```

### 2. Desactivar un Módulo
```bash
# Usando el comando Artisan
php artisan module:manage disable po_confirmation

# O usando el script de shell
./scripts/manage-modules.sh disable po_confirmation
```

### 3. Ver Estado
```bash
# Listar todos los módulos
php artisan module:manage list

# Ver estado específico
php artisan module:manage status po_confirmation
```

### 4. Instalar (Primera Vez)
```bash
php artisan module:manage install po_confirmation
```

## ⚙️ Configuración

### Variables de Entorno

```env
# Activar/desactivar módulos
PO_CONFIRMATION_ENABLED=true

# Configuración del módulo PO Confirmation
PO_CONFIRMATION_HASH_EXPIRY=72
PO_CONFIRMATION_FROM_NAME="Raga Orders"
PO_CONFIRMATION_FROM_ADDRESS=noreply@ragaorders.com
PO_CONFIRMATION_AUTO_SEND=true
PO_CONFIRMATION_CHECK_INTERVAL=hourly
PO_CONFIRMATION_NOTIFY_ADMIN=true
PO_CONFIRMATION_ADMIN_EMAIL=admin@ragaorders.com
```

## 🔄 Flujo de Trabajo Recomendado

### Desarrollo Local
```bash
# 1. Activar el módulo
./scripts/manage-modules.sh enable po_confirmation

# 2. Instalar (si es la primera vez)
./scripts/manage-modules.sh install po_confirmation

# 3. Verificar estado
./scripts/manage-modules.sh status po_confirmation
```

### Cambio de Repositorio
```bash
# 1. Desactivar antes de cambiar
./scripts/manage-modules.sh disable po_confirmation

# 2. Cambiar de repositorio...

# 3. Reactivar en el nuevo repositorio
./scripts/manage-modules.sh enable po_confirmation
```

## 🛠️ Comandos Disponibles

### Comando Artisan Principal
```bash
php artisan module:manage [acción] [módulo]
```

### Acciones Disponibles
- `list` - Lista todos los módulos
- `enable <módulo>` - Activa un módulo
- `disable <módulo>` - Desactiva un módulo
- `status <módulo>` - Muestra estado de un módulo
- `install <módulo>` - Instala un módulo

### Script de Shell
```bash
./scripts/manage-modules.sh [comando] [módulo]
```

## 📁 Estructura de Archivos

```
raga-orders/
├── app/
│   ├── Providers/
│   │   └── ModuleServiceProvider.php    # Núcleo del sistema
│   └── Console/Commands/
│       └── ModuleCommand.php            # Comando Artisan
├── laravel-po-confirmation/             # Módulo PO Confirmation
│   ├── src/                             # Código fuente del módulo
│   ├── config/                          # Configuración del módulo
│   ├── routes/                          # Rutas del módulo
│   ├── database/                        # Migraciones del módulo
│   └── resources/                       # Vistas del módulo
├── scripts/
│   └── manage-modules.sh                # Script de gestión
├── docs/
│   └── module-system.md                 # Documentación técnica
└── bootstrap/
    └── providers.php                    # Providers registrados
```

## 🔧 Agregar Nuevos Módulos

### 1. Crear la Estructura del Módulo
```
laravel-nuevo-modulo/
├── src/
├── config/
├── routes/
├── database/
└── resources/
```

### 2. Actualizar ModuleServiceProvider
```php
protected array $modules = [
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

### 3. Agregar Variable de Entorno
```env
NUEVO_MODULO_ENABLED=true
```

## 🎉 Ventajas del Sistema

1. **🚀 Flexibilidad Total**
   - Activar/desactivar módulos sin reinstalar
   - Cambio instantáneo entre funcionalidades

2. **🔧 Control Centralizado**
   - Gestión desde variables de entorno
   - Comandos Artisan para administración

3. **💻 Desarrollo Simplificado**
   - Fácil cambio entre repositorios
   - No más conflictos de dependencias

4. **📈 Escalabilidad**
   - Agregar nuevos módulos fácilmente
   - Arquitectura modular y extensible

5. **🛡️ Mantenimiento**
   - Sin conflictos de versiones
   - Gestión independiente de cada módulo

## ⚠️ Consideraciones Importantes

1. **Reinicio de Aplicación**
   - Después de activar/desactivar módulos, es recomendable reiniciar
   - O ejecutar `php artisan config:clear` y `php artisan route:clear`

2. **Dependencias**
   - Los módulos deben ser independientes entre sí
   - Evitar dependencias circulares

3. **Configuración**
   - Cada módulo debe tener su propio archivo de configuración
   - Usar variables de entorno para configuración

4. **Migraciones**
   - Se ejecutan automáticamente al instalar
   - Verificar compatibilidad de versiones

## 🐛 Troubleshooting

### Módulo no se activa
```bash
# Verificar variable de entorno
./scripts/manage-modules.sh status po_confirmation

# Verificar logs
tail -f storage/logs/laravel.log | grep "Módulo"
```

### Error en migraciones
```bash
# Verificar que el módulo esté activo
./scripts/manage-modules.sh status po_confirmation

# Ejecutar migraciones manualmente
php artisan migrate --path=laravel-po-confirmation/database/migrations
```

### Rutas no funcionan
```bash
# Limpiar cache de rutas
php artisan route:clear

# Verificar estado del módulo
./scripts/manage-modules.sh status po_confirmation
```

## 📚 Documentación Adicional

- **Documentación Técnica**: `docs/module-system.md`
- **API del Sistema**: Ver `ModuleServiceProvider` para métodos disponibles
- **Ejemplos de Uso**: Ver `scripts/manage-modules.sh`

## 🤝 Contribución

Para contribuir al sistema de módulos:

1. Crear el módulo siguiendo la estructura establecida
2. Actualizar `ModuleServiceProvider` con la configuración
3. Agregar variables de entorno necesarias
4. Documentar el módulo en `docs/module-system.md`
5. Probar activación/desactivación del módulo

---

**🎯 Objetivo**: Simplificar el desarrollo interno y eliminar la dependencia de packages de Composer para módulos internos.

**💡 Idea Principal**: "Activar funcionalidades con una variable de entorno, no con un comando de instalación."
