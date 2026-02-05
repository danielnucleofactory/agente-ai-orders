# Carrier codes (navieras) para Porth

Listado de **carrierCode** para usar en la creación de embarques en Porth. Si no se envía el código correcto, Porth puede no traer la información correcta.

## Origen

- **Fuente:** archivo `ports_and_shippinglines.csv` (columnas NAME SHIPPING LINE, TYPE, CODE).
- **Filtro:** solo líneas con `TYPE = ocean` (navieras marítimas).
- **Códigos:** estándar tipo SCAC (4 caracteres en la mayoría).

## Archivos generados

| Archivo | Contenido |
|---------|-----------|
| `docs/porth_carrier_codes_codes_only.txt` | Solo códigos, uno por línea (para copiar/pegar en config o código). |
| `docs/porth_carrier_codes.txt` | Código + nombre (tab separado). |

## Regenerar el listado

```bash
# Ver tabla en consola
php artisan porth:list-carrier-codes

# Solo códigos (uno por línea)
php artisan porth:list-carrier-codes --codes-only

# Exportar solo códigos a archivo
php artisan porth:list-carrier-codes --codes-only --export=docs/porth_carrier_codes_codes_only.txt

# Exportar código + nombre
php artisan porth:list-carrier-codes --export=docs/porth_carrier_codes.txt

# Exportar como JSON (carrierCode => nombre)
php artisan porth:list-carrier-codes --export=docs/porth_carrier_codes.json --json
```

Tras cambiar `ports_and_shippinglines.csv`, vuelve a ejecutar el comando para actualizar los listados.

## Mapeo nombre Maestros → carrierCode

La app ya usa `PorthTranslationService::translateShippingLine($carrierCode)` para traducir código Porth → nombre Maestros. El mapeo inverso (nombre en formulario → código para Porth) puede hacerse con la tabla en `porth_carrier_codes.txt` o con el JSON exportado. Algunos ejemplos:

| Nombre (Maestros / formulario) | carrierCode (Porth) |
|-------------------------------|---------------------|
| MAERSK                        | MAEU               |
| MSC                           | MEDU               |
| CMA CGM                       | CMDU               |
| EVERGREEN                     | EGLV               |
| HAPAG LLOYD                   | HLCU               |
| ONE                           | ONEY               |
| OOCL                          | OOLU               |
| ZIM                           | ZIMU               |
| PIL                           | PCIU               |
| WAN HAI                       | WHLU               |

Los nombres en el formulario (CreatePucharseOrder `shippingLineArray`) no siempre coinciden exactamente con el CSV; si hace falta, se puede añadir un mapeo explícito nombre → carrierCode al crear el embarque en Porth.
