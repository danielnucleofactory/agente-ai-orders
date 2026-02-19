# Creación de embarques en Porth

## Flujo general

Cuando un documento (Shipping Document o Purchase Order) tiene identificadores de seguimiento, el sistema intenta **buscar primero** el embarque en Porth. Si no existe, lo **crea**.

```
Documento (PO o Shipping Document)
    → Extrae identificadores (container_number, mbl_number, booking_code)
    → Para cada identificador:
        1. Busca en Porth (GET)
        2. Si no existe → Crea (POST)
```

## Endpoint de creación

```
POST {PORTH_API_URL}/api/shipment/add
```

- **URL base:** `https://api.porth.app` (configurable vía `PORTH_API_URL`)
- **Headers:** `apikey`, `Accept: application/json`, `Content-Type: application/json`

## Payload de creación

El payload es **mínimo**: solo incluye el nombre y el identificador de seguimiento. Porth completa el resto con sus propias fuentes.

### Estructura

| Campo | Tipo | Obligatorio | Descripción |
|-------|------|-------------|-------------|
| `name` | string | Sí | Formato: `OLO-{order_number}-{timestamp}` |
| `containerNumber` | string | Condicional | Número de contenedor (si el identificador es container) |
| `masterBl` | string | Condicional | Número MBL (si el identificador es mbl_number) |
| `bookingNumber` | string | Condicional | Código de booking (si el identificador es booking_code) |
| `carrierCode` | string | Opcional | Código de naviera (ej: `MAEU` para Maersk). Se obtiene de `shipping_line` si existe |

**Nota:** Solo se envía **uno** de los identificadores (`containerNumber`, `masterBl` o `bookingNumber`), según el tipo usado en esa iteración.

### Ejemplo de payload (por contenedor)

```json
{
  "name": "OLO-4107513-1739962202",
  "containerNumber": "CONT1234567",
  "carrierCode": "MAEU"
}
```

### Ejemplo de payload (por MBL)

```json
{
  "name": "OLO-SD-001-1739962202",
  "masterBl": "MAEU1234567890",
  "carrierCode": "MAEU"
}
```

### Ejemplo de payload (por booking)

```json
{
  "name": "OLO-4107519-1739962202",
  "bookingNumber": "BK001234"
}
```

## Identificadores válidos

Para que se pueda crear un embarque, el documento debe tener al menos uno de:

| Origen | Campos |
|--------|--------|
| Shipping Document | `tracking_id`, `mbl_number`, `container_number`, `booking_code` |
| Purchase Order | `tracking_id`, `mbl_number`, `container_number` |

## Código de naviera (carrierCode)

Si el documento tiene `shipping_line` (ej: "MAERSK", "MSC"), el sistema traduce el nombre a código IATA (ej: `MAEU`, `MSCU`) mediante `PorthTranslationService` y lo incluye en el payload. Esto mejora el matching en Porth.

## Respuesta exitosa

```json
{
  "id": "shipment_id_porth",
  ...
}
```

El `id` devuelto se guarda en el documento como `porth_id` para futuras sincronizaciones.
