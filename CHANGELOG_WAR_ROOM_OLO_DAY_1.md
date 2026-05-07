# Changelog War Room OLO - Dia 1

Fecha: 2026-05-06
Branch: `war-room-olo-day-1-actions`

## Resumen

Este cambio consolida los ajustes levantados en el war room del dia 1 para OLO, enfocados en:

- bloqueo de campos en edicion de PO
- reglas de bloqueo de fechas asociadas a Porth
- calculos derivados de fechas
- ajustes de automatizacion de etapas por tracking Porth
- base de autorizaciones para cambios sensibles
- flujo de "No aplica tracking"
- validacion del numero de contenedor
- timeline de tracking alimentado desde BD
- correcciones de auditoria e historial
- correccion de exportacion Excel de incoterms
- robustecimiento del diccionario de puertos Porth -> Maestros

## 1. Bloqueo de campos en edicion de PO

Se bloquearon en modo edicion de PO los campos definidos en el Excel revisado con Raga.

Archivos principales:

- `app/Livewire/Forms/CreatePucharseOrder.php`
- `resources/views/livewire/forms/create-pucharse-order.blade.php`

Notas:

- El bloqueo aplica en edicion, no en creacion.
- Se separo el tratamiento de campos generales vs reglas especiales de tracking/fechas.

## 2. Bloqueo de fechas de Porth

Se implementaron reglas para impedir edicion manual de fechas sincronizadas desde Porth, manteniendo el criterio acordado en la minuta.

Campos considerados:

- `date_etd_initial`
- `date_etd`
- `date_atd`
- `date_eta_initial`
- `date_eta`
- `date_eta_updated`
- `date_ata`

Archivos principales:

- `app/Livewire/Forms/CreatePucharseOrder.php`
- `resources/views/livewire/forms/create-pucharse-order.blade.php`

Notas:

- Si la PO esta en `tracking_not_applicable`, las reglas de tracking Porth no fuerzan bloqueo por sincronizacion.

## 3. Ajuste de calculos derivados de fechas

Se alinearon los calculos con la regla de negocio levantada:

- `delay_days` se calcula siempre contra `date_eta_initial`, sin fallback
- `delay_days` solo se recalcula al cambiar `date_ata`
- `arrival_status` representa cumplimiento respecto a la fecha comprometida de entrega
- `dif_load_date` queda tratado como entero

Archivos principales:

- `app/Livewire/Forms/CreatePucharseOrder.php`
- `app/Models/PurchaseOrder.php`
- `database/migrations/2026_05_05_000004_change_dif_load_date_to_integer.php`

## 4. Automatizacion de etapas por Porth

Se ajusto la automatizacion para que las fases Porth desde llegada a puerto en adelante muevan la PO a la etapa `Puerto`, y se dejo habilitada para operar desde produccion segun el criterio acordado.

Archivos principales:

- `app/Services/PorthImportService.php`
- `config/services.php`

## 5. Base de autorizaciones para cambios sensibles

Se dejo la base generica para canalizar cambios que deban pasar por autorizacion, en vez de guardarse directo.

Caso implementado en este ciclo:

- `tracking_not_applicable`

Archivos principales:

- `app/Services/AuthorizationService.php`
- `app/Livewire/Forms/CreatePucharseOrder.php`
- `app/Livewire/Kanban/KanbanBoard.php`
- `app/Models/PurchaseOrder.php`
- `database/migrations/2026_05_05_000005_add_tracking_not_applicable_to_purchase_orders.php`

## 6. Flujo "No aplica tracking"

Se implemento y refino el flujo funcional y visual de `No aplica tracking`.

Incluye:

- checkbox visible en la seccion correcta (`Naviera y equipo`)
- ocultamiento temporal del campo de motivo en UI
- bloqueo de:
  - `shipping_line`
  - `container_type`
  - `container_number`
- exclusion de esos campos del guardado cuando `No aplica tracking` esta activo
- restauracion de valores persistidos para evitar cambios fantasma
- estilo gris consistente con otros campos bloqueados

Archivos principales:

- `app/Livewire/Forms/CreatePucharseOrder.php`
- `resources/views/livewire/forms/create-pucharse-order.blade.php`

## 7. Validacion del numero de contenedor

Se centralizo la regla de validacion del contenedor en formato:

- `4 letras + 7 digitos`
- ejemplo valido: `ABCD1234567`

Se agrego helper comun:

- `app/Support/ContainerNumber.php`

Se aplico validacion/normalizacion en:

- formulario de PO
- Kanban
- Shipping Documentation
- API de creacion/actualizacion de PO
- importacion desde Porth

Archivos principales:

- `app/Support/ContainerNumber.php`
- `app/Http/Controllers/PurchaseOrderController.php`
- `app/Livewire/Forms/CreatePucharseOrder.php`
- `app/Livewire/Kanban/KanbanBoard.php`
- `app/Livewire/ShippingDocumentation/ShippingDocumentationKanban.php`
- `app/Helpers/PorthImportHelper.php`

Adicionalmente, se corrigio la UX del campo:

- se elimino `maxlength="11"` para no truncar silenciosamente el valor pegado
- se habilito despliegue explicito del mensaje de error en la vista principal de PO

Archivos de vista tocados:

- `resources/views/livewire/forms/create-pucharse-order.blade.php`
- `resources/views/livewire/kanban/kanban-board.blade.php`
- `resources/views/livewire/shipping-documentation/shipping-documentation-kanban.blade.php`

## 8. Timeline de Porth desde BD

Se cambio el timeline de tracking para que se alimente desde datos persistidos en nuestra BD y no llame a Porth cada vez que se abre la PO.

Se agrego servicio nuevo:

- `app/Services/PorthTimelineService.php`

El timeline ahora usa:

- `porth_phase`
- `porth_itinerary`
- timestamps `porth_*`

Tambien se tradujeron los nombres de etapas al espanol.

Archivos principales:

- `app/Services/PorthTimelineService.php`
- `app/Livewire/Forms/PucharseOrderDetail.php`
- `app/Livewire/Forms/PucharseOrderConsolidateDetail.php`

## 9. Historico / auditoria de POs

Se corrigio el historico para que tambien registre la creacion de la PO, no solo sus ediciones.

Antes:

- el observer solo auditaba `updating`

Ahora:

- se agrega auditoria en `created`
- se crea comentario con `action_type = record_create`
- se almacenan valores iniciales visibles para el modal de detalle

Archivo principal:

- `app/Observers/PurchaseOrderObserver.php`

## 10. Exportacion Excel del kanban de PO

Se corrigio el mapeo de incoterms en la descarga Excel del kanban.

Antes estaban corridos:

- `Incoterm de Precios`
- `Incoterm de Compra`

Ahora quedan asi:

- `Incoterm de Precios` -> `price_incoterm`
- `Incoterm de Compra` -> `incoterms`
- `Incoterm Logistico` -> `logistics_incoterm`

Tambien se elimino un fallback incorrecto hacia `payment_terms`.

Archivo principal:

- `app/Exports/ActivePurchaseOrdersExport.php`

## 11. Archivos nuevos agregados

- `CHANGELOG_WAR_ROOM_OLO_DAY_1.md`
- `app/Services/PorthTimelineService.php`
- `app/Support/ContainerNumber.php`
- `database/migrations/2026_05_05_000004_change_dif_load_date_to_integer.php`
- `database/migrations/2026_05_05_000005_add_tracking_not_applicable_to_purchase_orders.php`

## 12. Diccionario de puertos Porth -> Maestros

Se fortalecio la traduccion de puertos para reducir casos donde el webhook no envia
`departure_port` o `arrival_port` porque el nombre recibido desde Porth no coincide
exactamente con el maestro.

Incluye:

- normalizacion semantica del nombre del puerto para busqueda
- tolerancia a variantes con:
  - acentos
  - puntuacion
  - sufijos como `Port`, `Port of`, `Puerto`, `Harbor`, `Terminal`
- fallback al maestro de puertos cuando el CSV local no alcanza
- devolucion del nombre canonico del maestro cuando se encuentra match

Caso cubierto explicitamente:

- `Shenzen port, China` -> `Shenzen, China`

Archivos principales:

- `app/Services/PorthTranslationService.php`
- `tests/Unit/PorthTranslationServiceTest.php`

Verificacion:

- prueba unitaria pasando para fallback al maestro con variacion por sufijo

## 13. Ajuste de reglas en creacion/edicion para tracking

Se ajustaron las reglas del formulario de PO para reflejar mejor el flujo real
de captura manual vs. bloqueo posterior.

Incluye:

- `No aplica tracking` ahora tambien aparece en creacion
- cuando `No aplica tracking` esta activo en creacion, no se guardan:
  - `shipping_line`
  - `container_type`
  - `container_number`
- `ETA Inicial` se separo del candado general de fechas Porth
- `ETA Inicial` queda:
  - editable en creacion
  - editable en edicion solo si aun no tiene dato
  - bloqueado en edicion cuando ya existe valor guardado
- en creacion, el resto de fechas de tracking se mantienen bloqueadas; la unica
  excepcion editable es `ETA Inicial`
- correccion del componente `date-picker` para que `:readonly=\"false\"` y `:disabled=\"false\"`
  no bloqueen el campo por error
- correccion en `updatePurchaseOrder` para guardar solo cambios reales filtrados y
  evitar errores de persistencia por campos "ruidosos" al editar una PO

Archivos principales:

- `app/Livewire/Forms/CreatePucharseOrder.php`
- `resources/views/livewire/forms/create-pucharse-order.blade.php`
- `resources/views/components/date-picker.blade.php`

## 14. Endurecimiento de sincronizacion del date-picker

Se reforzo la sincronizacion entre Flatpickr, Alpine y Livewire para evitar
casos donde el usuario selecciona o escribe una fecha, pero el valor no llega
al submit de edicion.

Incluye:

- `ETA Inicial` ahora usa `wire:model.live`
- el componente `date-picker` sincroniza el valor no solo en `onChange`, sino
  tambien en:
  - `onClose`
  - `onValueUpdate`
  - `blur` del `altInput`
  - `change` del `altInput`
- se normaliza entrada manual usando tanto el formato visible del usuario como
  `Y-m-d`
- se recompilo el bundle de Vite para dejar el JS actualizado en
  `public/build`

Archivos principales:

- `resources/js/app.js`
- `resources/views/livewire/forms/create-pucharse-order.blade.php`

Verificacion:

- `php artisan view:clear`
- `php artisan view:cache`
- `npm run build`

## 15. Correccion de bloqueo de fechas en edicion de PO

Se ajusto la regla de bloqueo de fechas en edicion para que no dependa de si la
PO ya tiene tracking activo en Porth.

Incluye:

- en edicion, las fechas de tracking bloqueadas quedan siempre no editables:
  - `ETD Inicial`
  - `ETD Variable`
  - `ATD`
  - `ETA Variable`
  - `ATA`
- `ETA Inicial` se mantiene como la unica excepcion:
  - editable si no tiene valor guardado
  - bloqueada si ya fue llenada
- esto corrige el caso de PO creadas localmente sin `porth_id`, donde esas
  fechas se estaban habilitando por error al entrar a editar

Archivos principales:

- `app/Livewire/Forms/CreatePucharseOrder.php`

Verificacion:

- `php -l app/Livewire/Forms/CreatePucharseOrder.php`
- `php artisan view:clear`
- `php artisan view:cache`

## 16. Consideraciones pendientes fuera de este cambio

Se analizaron pero no se implementaron todavia en este branch:

- rediseño estructural completo de integracion con Porth
- persistencia normalizada de cargos/fases/itinerarios ligados a PO
- cola de fallidos y reintentos Porth
- scheduler horario por ventana CR y batches de 50
- uso de `cargoId` para update correcto de contenedor en Porth
