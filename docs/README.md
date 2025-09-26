# Documentación del Sistema OLO - Órdenes de Compra

## 📚 Índice de Documentación

Esta carpeta contiene toda la documentación técnica del Sistema de Gestión de Órdenes de Compra OLO.

### 🚀 Documentación Principal

#### API Documentation
- **[📖 API Completa](API_DOCUMENTATION.md)** - **DOCUMENTACIÓN PRINCIPAL** - Todo lo que necesitas saber sobre la API
- **[💻 Ejemplos de Código](CODE_EXAMPLES.md)** - Ejemplos en JavaScript, Python, PHP, Java, C# y Go

#### Arquitectura Interna
- **[Sistema de Permisos](internal_architecture/permission_system.md)** - Guía de implementación del sistema de permisos de usuario (roles).
- **[Sistema de Autorizaciones](internal_architecture/authorization_system.md)** - Sistema para aprobar/rechazar acciones sobre entidades (ej. comentarios).
- **[Sistema de Módulos](internal_architecture/module_system.md)** - Arquitectura para módulos internos.
- **[Estructura de Datos del Dashboard](internal_architecture/dashboard-data-structure.md)** - Estructura de datos del dashboard.

#### Importación de Datos
- **[Guía de Importación](data_import/index.md)** - Proceso de importación de datos
- **[Plantillas CSV](data_import/templates/)** - Plantillas para importación

### 📋 Historial de Cambios

- **[Changelog de Documentación](CHANGELOG_DOCUMENTACION.md)** - Historial de mejoras en la documentación

---

## 🎯 Guía Rápida

### Para Desarrolladores
1. **Comenzar con la [API Completa](API_DOCUMENTATION.md)** - Todo en un solo lugar
2. **Consultar los [Ejemplos de Código](CODE_EXAMPLES.md)** para implementación práctica
3. **Revisar la [Arquitectura Interna](internal_architecture/)** para entender el sistema

### Para Integradores
1. **Documentación Principal**: [API Completa](API_DOCUMENTATION.md) - Campos, endpoints, validaciones
2. **Ejemplos de Código**: [CODE_EXAMPLES.md](CODE_EXAMPLES.md) - JavaScript, Python, PHP, Java, C#, Go
3. **Implementación**: Copia y pega los ejemplos según tu lenguaje

### Para Administradores
1. **Sistema de Permisos**: Ver [guía de implementación de permisos](internal_architecture/permission_system.md).
2. **Sistema de Autorizaciones**: Ver [guía de implementación de autorizaciones](internal_architecture/authorization_system.md).
3. **Sistema de Módulos**: Ver [guía de implementación de módulos](internal_architecture/module_system.md).
4. **Importación de Datos**: Ver [guía de importación](data_import/index.md).

---

## 🔧 Estructura de la API

### Endpoints Públicos
- `POST /api/purchase-orders` - Crear órdenes de compra
- `GET /api/status` - Verificar estado de la API

### Endpoints Protegidos (requieren token)
- `GET /api/user` - Información del usuario
- `GET /api/my-purchase-orders` - Órdenes del usuario
- `POST /api/my-purchase-orders` - Crear orden simple
- `GET /api/dashboard-stats` - Estadísticas del dashboard
- `PUT /api/profile` - Actualizar perfil

---

## 📊 Campos de la API

### Campos Obligatorios (11 campos)
- `order_number`, `category`, `factory_proforma_number`
- `route_label`, `date_theorical_load`, `bonded_warehouse_enter`
- `bonded_warehouse_exit`, `reason`, `incoterms`
- `logistics_incoterm`, `price_incoterm`

### Campos OLO (80+ campos)
- **Información textual** (25+ campos)
- **Campos booleanos** (9 campos)
- **Campos enteros** (4 campos)
- **Campos decimales** (3 campos)
- **Fechas adicionales** (20+ campos)

Para ver la lista completa, consulta la [documentación completa](API_DOCUMENTATION.md).

---

## 🚀 Ejemplos Rápidos

### Crear Orden Simple (JavaScript)
```javascript
const client = new OLOClient('https://su-dominio.com/api');

const order = await client.createPurchaseOrder({
    order_number: 'PO-2024-001',
    category: 'Electronics',
    factory_proforma_number: 'PRF-001',
    route_label: 'RUTA-A',
    date_theorical_load: '2024-01-20',
    bonded_warehouse_enter: '2024-01-15',
    bonded_warehouse_exit: '2024-01-25',
    reason: 'Stock replenishment',
    incoterms: 'FOB',
    logistics_incoterm: 'FOB',
    price_incoterm: 'FOB',
    vendor_name: 'Proveedor ABC',
    net_total: 2500.00,
    items: [
        {
            material: 'MAT-001',
            price_per_unit: 25.50,
            peso_kg: 100.0
        }
    ]
});
```

### Crear Orden Simple (Python)
```python
client = OLOClient('https://su-dominio.com/api')

order = client.create_purchase_order({
    'order_number': 'PO-2024-001',
    'category': 'Electronics',
    'factory_proforma_number': 'PRF-001',
    'route_label': 'RUTA-A',
    'date_theorical_load': '2024-01-20',
    'bonded_warehouse_enter': '2024-01-15',
    'bonded_warehouse_exit': '2024-01-25',
    'reason': 'Stock replenishment',
    'incoterms': 'FOB',
    'logistics_incoterm': 'FOB',
    'price_incoterm': 'FOB',
    'vendor_name': 'Proveedor ABC',
    'net_total': 2500.00,
    'items': [
        {
            'material': 'MAT-001',
            'price_per_unit': 25.50,
            'peso_kg': 100.0
        }
    ]
})
```

> **📚 Para más ejemplos**: Consulta [CODE_EXAMPLES.md](CODE_EXAMPLES.md) para ejemplos completos en JavaScript, Python, PHP, Java, C# y Go.

---

## 📞 Soporte

- **Email**: soporte-api@empresa.com
- **Horario**: Lunes a Viernes, 8:00 AM - 6:00 PM
- **Tiempo de respuesta**: 24 horas hábiles

---

## 📝 Notas de Versión

### v1.1.0 (2024-01-15)
- ✨ Documentación completa con todos los campos OLO
- ✨ Ejemplos en JavaScript y Python
- ✨ Manejo de errores mejorado
- ✨ Utilidades y helpers para integración
- ✨ Estructura modular de documentación

### v1.0.0 (2024-01-10)
- 📋 Documentación inicial básica
- 📋 Ejemplos simples de uso

---

*Esta documentación se actualiza regularmente. Para ver el historial completo de cambios, consulta el [Changelog de Documentación](CHANGELOG_DOCUMENTACION.md).*
