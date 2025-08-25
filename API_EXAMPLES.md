# API Documentation - Ejemplos de Uso

## Autenticación

Para usar las rutas protegidas de la API, necesitas incluir el token en el header `Authorization` con el formato `Bearer {token}`.

### Crear un Token

1. Ve a la sección "Gestión de Tokens API" en el sistema
2. Haz clic en "Crear Nuevo Token"
3. Proporciona un nombre descriptivo para el token
4. Opcionalmente, establece una fecha de expiración
5. Copia el token generado (solo se muestra una vez)

## Rutas Disponibles

### Rutas Públicas (sin autenticación)

#### Verificar Estado de la API
```bash
curl -X GET "http://localhost:8000/api/status"
```

**Respuesta:**
```json
{
    "status": "API funcionando correctamente",
    "timestamp": "2024-01-15T10:30:00.000000Z",
    "version": "1.0.0"
}
```

#### Crear Orden de Compra (Endpoint Público)
```bash
curl -X POST "http://localhost:8000/api/purchase-orders" \
  -H "Content-Type: application/json" \
  -d '{
    "vendor_id": 1,
    "description": "Orden desde API externa",
    "total_amount": 1500.00
  }'
```

### Rutas Protegidas (requieren token)

#### Obtener Información del Usuario
```bash
curl -X GET "http://localhost:8000/api/user" \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

**Respuesta:**
```json
{
    "user": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@example.com",
        "company_id": 1
    },
    "company": {
        "id": 1,
        "name": "Mi Empresa S.A.",
        "address": "Calle Principal 123"
    }
}
```

#### Obtener Órdenes de Compra del Usuario
```bash
curl -X GET "http://localhost:8000/api/my-purchase-orders" \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

**Respuesta:**
```json
{
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "description": "Orden de ejemplo",
                "total_amount": "1500.00",
                "vendor": {
                    "name": "Proveedor ABC"
                }
            }
        ],
        "per_page": 10,
        "total": 1
    },
    "user": "Juan Pérez",
    "company": "Mi Empresa S.A."
}
```

#### Crear Nueva Orden de Compra
```bash
curl -X POST "http://localhost:8000/api/my-purchase-orders" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{
    "vendor_id": 1,
    "description": "Nueva orden desde API",
    "total_amount": 2500.00
  }'
```

**Respuesta:**
```json
{
    "message": "Orden de compra creada exitosamente",
    "data": {
        "vendor_id": 1,
        "description": "Nueva orden desde API",
        "total_amount": 2500.00
    },
    "created_by": "Juan Pérez"
}
```

#### Obtener Estadísticas del Dashboard
```bash
curl -X GET "http://localhost:8000/api/dashboard-stats" \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

**Respuesta:**
```json
{
    "stats": {
        "total_orders": 15,
        "pending_orders": 3,
        "completed_orders": 12
    },
    "user_info": {
        "name": "Juan Pérez",
        "email": "juan@example.com",
        "company": "Mi Empresa S.A."
    }
}
```

#### Actualizar Perfil del Usuario
```bash
curl -X PUT "http://localhost:8000/api/profile" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Juan Carlos Pérez",
    "phone": "+1234567890"
  }'
```

**Respuesta:**
```json
{
    "message": "Perfil actualizado exitosamente",
    "user": {
        "id": 1,
        "name": "Juan Carlos Pérez",
        "email": "juan@example.com",
        "phone": "+1234567890"
    }
}
```

## Manejo de Errores

### Token Faltante
```json
{
    "error": "Token de acceso requerido",
    "message": "Debe proporcionar un token de acceso válido en el header Authorization"
}
```

### Token Inválido
```json
{
    "error": "Token inválido",
    "message": "El token proporcionado no es válido o ha expirado"
}
```

### Token Expirado
```json
{
    "error": "Token expirado",
    "message": "El token ha expirado"
}
```

## Códigos de Estado HTTP

- `200` - Éxito
- `201` - Creado exitosamente
- `400` - Error en la solicitud
- `401` - No autorizado (token inválido/faltante)
- `403` - Prohibido
- `404` - No encontrado
- `422` - Error de validación
- `500` - Error interno del servidor

## Notas Importantes

1. **Seguridad del Token**: Nunca compartas tu token API. Manténlo seguro como una contraseña.

2. **Expiración**: Los tokens pueden tener fecha de expiración. Verifica regularmente el estado de tus tokens.

3. **Límites de Uso**: Actualmente no hay límites de rate limiting, pero se recomienda un uso responsable.

4. **Formato de Fechas**: Todas las fechas están en formato ISO 8601 UTC.

5. **Paginación**: Las respuestas que devuelven listas están paginadas por defecto (10 elementos por página).

## Ejemplo con JavaScript (Fetch API)

```javascript
const token = 'TU_TOKEN_AQUI';

// Obtener información del usuario
fetch('/api/user', {
    method: 'GET',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));

// Crear nueva orden
fetch('/api/my-purchase-orders', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        vendor_id: 1,
        description: 'Orden desde JavaScript',
        total_amount: 1000.00
    })
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
```
