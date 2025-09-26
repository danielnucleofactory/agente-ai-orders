# Diccionario de Datos del Proyecto

Este documento describe la estructura de la base de datos, los modelos principales y sus relaciones. Sirve como una referencia central para entender la arquitectura de datos de la aplicación.

## Índice de Modelos

- [Modelos Principales](#modelos-principales)
  - [1. PurchaseOrder](#1-purchaseorder)
  - [2. User](#2-user)
  - [3. Company](#3-company)
  - [4. Product](#4-product)
  - [5. Vendor](#5-vendor)
- [Modelos de Logística y Envío](#modelos-de-logística-y-envío)
  - [6. ShippingDocument](#6-shippingdocument)
  - [7. ShipTo](#7-shipto)
  - [8. BillTo](#8-billto)
  - [9. Hub](#9-hub)
  - [10. BoardingDocument](#10-boardingdocument)
  - [11. TrackingDataPO](#11-trackingdatapo)
- [Modelos de Kanban y Estado](#modelos-de-kanban-y-estado)
  - [12. KanbanBoard](#12-kanbanboard)
  - [13. KanbanStatus](#13-kanbanstatus)
- [Modelos de Soporte y Comentarios](#modelos-de-soporte-y-comentarios)
  - [14. Authorization](#14-authorization)
  - [15. Comment](#15-comment)
  - [16. PurchaseOrderComment](#16-purchaseordercomment)
  - [17. ShippingDocumentComment](#17-shippingdocumentcomment)
- [Otros Modelos](#otros-modelos)
  - [18. Forecast](#18-forecast)
  - [19. Notification](#19-notification)
  - [20. PurchaseOrderProduct (Tabla Pivote)](#20-purchaseorderproduct-tabla-pivote)

---

## Modelos Principales

### 1. PurchaseOrder
Representa la entidad central del sistema: una orden de compra. Contiene toda la información logística, financiera y de estado de un pedido.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `company_id` | foreignId | ID de la compañía que emite la orden. |
| `vendor_id` | foreignId | ID del proveedor. |
| `order_number` | string | Número único de la orden de compra. |
| `status` | string | Estado actual de la orden (ej: 'pending', 'completed'). |
| `order_date` | date | Fecha de emisión de la orden. |
| `net_total` | decimal | Costo neto de los productos. |
| `total` | decimal | Costo total incluyendo gastos adicionales. |
| `kanban_status_id` | foreignId | ID del estado actual en el tablero Kanban. |
| `...` | | (Contiene más de 100 campos para logística detallada). |

**Relaciones:**
- **company()**: Pertenece a una `Company`.
- **vendor()**: Pertenece a un `Vendor`.
- **shipTo()**: Pertenece a una dirección de envío `ShipTo`.
- **products()**: Tiene y pertenece a muchos `Product` (relación N:M).
- **shippingDocuments()**: Tiene y pertenece a muchos `ShippingDocument` (relación N:M).
- **boardingDocuments()**: Tiene muchos `BoardingDocument`.
- **trackingData()**: Tiene muchos `TrackingDataPO`.
- **kanbanStatus()**: Pertenece a un `KanbanStatus`.
- **plannedHub() / actualHub()**: Pertenece a un `Hub`.
- **comments()**: Tiene muchos `PurchaseOrderComment`.
- **authorizations()**: Puede tener muchas `Authorization` (relación polimórfica).

### 2. User
Representa a un usuario del sistema. Puede pertenecer a una compañía y tiene roles y permisos asociados.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `name` | string | Nombre del usuario. |
| `email` | string | Correo electrónico (único). |
| `password` | string | Contraseña hasheada. |
| `company_id` | foreignId | ID de la compañía a la que pertenece. |

**Relaciones:**
- **company()**: Pertenece a una `Company`.
- **notifications()**: Tiene muchas `Notification`.
- **roles()**: Heredado de Spatie/Permission, gestiona los roles del usuario.

### 3. Company
Representa una empresa o cliente dentro del sistema. Actúa como el contenedor principal para usuarios y órdenes de compra.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `name` | string | Nombre de la compañía. |
| `country` | string | País de la compañía. |
| `phone` | string | Teléfono de contacto. |

**Relaciones:**
- **users()**: Tiene muchos `User`.
- **purchaseOrders()**: Tiene muchas `PurchaseOrder`.

### 4. Product
Representa un producto o material que puede ser incluido en una orden de compra.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `material_id` | string | Identificador único del material. |
| `short_text` | string | Descripción corta del producto. |
| `price_per_unit` | decimal | Precio unitario. |

**Relaciones:**
- **purchaseOrders()**: Pertenece a muchas `PurchaseOrder` (relación N:M).
- **vendor()**: Pertenece a un `Vendor`.

### 5. Vendor
Representa un proveedor o vendedor de productos.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `company_id` | foreignId | ID de la compañía a la que está asociado este proveedor. |
| `name` | string | Nombre del proveedor. |
| `vendo_code` | string | Código único del proveedor. |
| `email` | string | Correo de contacto. |

**Relaciones:**
- **company()**: Pertenece a una `Company`.
- **purchaseOrders()**: Tiene muchas `PurchaseOrder`.

---

## Modelos de Logística y Envío

### 6. ShippingDocument
Representa un documento de embarque que puede agrupar una o más órdenes de compra para su transporte.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `document_number` | string | Número único del documento. |
| `status` | string | Estado del embarque. |
| `mbl_number` / `hbl_number`| string | Master/House Bill of Lading. |
| `container_number` | string | Número del contenedor. |

**Relaciones:**
- **company()**: Pertenece a una `Company`.
- **purchaseOrders()**: Tiene y pertenece a muchas `PurchaseOrder` (relación N:M).
- **kanbanStatus()**: Pertenece a un `KanbanStatus`.
- **comments()**: Tiene muchos `ShippingDocumentComment`.

### 7. ShipTo
Representa una dirección de destino o envío (`Ship To`).

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `name` | string | Nombre o identificador de la dirección. |
| `address` | string | Dirección completa. |
| `country` | string | País. |
| `company_id` | foreignId | Compañía a la que pertenece la dirección. |

**Relaciones:**
- **company()**: Pertenece a una `Company`.
- **purchaseOrders()**: Tiene muchas `PurchaseOrder`.

### 8. BillTo
Representa una dirección de facturación (`Bill To`).

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `name` | string | Nombre o identificador de la dirección. |
| `address` | string | Dirección completa. |
| `country` | string | País. |
| `company_id` | foreignId | Compañía a la que pertenece la dirección. |

**Relaciones:**
- **company()**: Pertenece a una `Company`.
- **purchaseOrders()**: Tiene muchas `PurchaseOrder`.

### 9. Hub
Representa un centro logístico o de consolidación.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `name` | string | Nombre del hub. |
| `code` | string | Código del hub (ej: 'MIA'). |
| `country` | string | País donde se ubica. |

**Relaciones:**
- **plannedPurchaseOrders()**: Tiene muchas `PurchaseOrder` que lo tienen como hub planificado.
- **actualPurchaseOrders()**: Tiene muchas `PurchaseOrder` que lo tienen como hub real.

### 10. BoardingDocument
Representa un documento de embarque específico asociado a una única orden de compra.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `purchase_order_id` | foreignId | Orden de compra a la que pertenece. |
| `document_type` | string | Tipo de documento (ej: 'Factura', 'Packing List'). |
| `status` | string | Estado del documento. |

**Relaciones:**
- **purchaseOrder()**: Pertenece a una `PurchaseOrder`.

### 11. TrackingDataPO
Almacena información de seguimiento para una orden de compra.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `purchase_order_id` | foreignId | Orden de compra a la que pertenece. |
| `tracking_number` | string | Número de seguimiento. |
| `carrier` | string | Transportista. |
| `estimated_delivery` | datetime | Fecha estimada de entrega. |

**Relaciones:**
- **purchaseOrder()**: Pertenece a una `PurchaseOrder`.

---

## Modelos de Kanban y Estado

### 12. KanbanBoard
Representa un tablero Kanban, que es un conjunto de estados (columnas) para gestionar un flujo de trabajo.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `name` | string | Nombre del tablero (ej: "Flujo de Órdenes"). |
| `type` | string | Tipo de entidad que gestiona (ej: 'PurchaseOrder'). |
| `company_id` | foreignId | Compañía a la que pertenece el tablero. |

**Relaciones:**
- **company()**: Pertenece a una `Company`.
- **statuses()**: Tiene muchos `KanbanStatus`.

### 13. KanbanStatus
Representa una columna o estado dentro de un tablero Kanban.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `name` | string | Nombre del estado (ej: "En Tránsito"). |
| `kanban_board_id` | foreignId | Tablero al que pertenece. |
| `position` | integer | Orden de la columna en el tablero. |
| `color` | string | Color para la UI. |

**Relaciones:**
- **board()**: Pertenece a un `KanbanBoard`.
- **purchaseOrders()**: Tiene muchas `PurchaseOrder` en este estado.

---

## Modelos de Soporte y Comentarios

### 14. Authorization
Gestiona las solicitudes de autorización para operaciones sensibles en el sistema.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `authorizable_type` | string | El tipo de modelo que requiere autorización (polimórfico). |
| `authorizable_id` | integer | El ID del modelo que requiere autorización (polimórfico). |
| `requester_id` | foreignId | Usuario que solicita la autorización. |
| `authorizer_id` | foreignId | Usuario que aprueba/rechaza. |
| `status` | string | 'pending', 'approved', 'rejected'. |

**Relaciones:**
- **authorizable()**: Relación polimórfica, puede apuntar a `PurchaseOrder`, `PurchaseOrderComment`, etc.
- **requester()**: Pertenece a un `User`.
- **authorizer()**: Pertenece a un `User`.

### 15. Comment
Modelo genérico para comentarios, aunque parece estar específicamente ligado a `ShippingDocument`.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `comment` | text | Contenido del comentario. |
| `user_id` | foreignId | Autor del comentario. |
| `shipping_document_id`| foreignId | Documento de embarque asociado. |

**Relaciones:**
- **user()**: Pertenece a un `User`.
- **shippingDocument()**: Pertenece a un `ShippingDocument`.

### 16. PurchaseOrderComment
Representa un comentario específico para una `PurchaseOrder`.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `comment` | text | Contenido del comentario. |
| `user_id` | foreignId | Autor del comentario. |
| `purchase_order_id`| foreignId | Orden de compra asociada. |

**Relaciones:**
- **user()**: Pertenece a un `User`.
- **purchaseOrder()**: Pertenece a una `PurchaseOrder`.

### 17. ShippingDocumentComment
Representa un comentario específico para un `ShippingDocument`.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `comment` | text | Contenido del comentario. |
| `user_id` | foreignId | Autor del comentario. |
| `shipping_document_id`| foreignId | Documento de embarque asociado. |

**Relaciones:**
- **user()**: Pertenece a un `User`.
- **shippingDocument()**: Pertenece a un `ShippingDocument`.

---

## Otros Modelos

### 18. Forecast
Almacena datos de pronóstico de demanda de materiales.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `material` | string | ID del material. |
| `quantity_requested` | decimal | Cantidad pronosticada. |
| `delivery_date` | date | Fecha de entrega esperada. |

**Relaciones:**
- (No tiene relaciones definidas directamente en el modelo).

### 19. Notification
Representa una notificación enviada a un usuario.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `user_id` | foreignId | Usuario que recibe la notificación. |
| `title` | string | Título de la notificación. |
| `message` | text | Cuerpo del mensaje. |
| `read_at` | datetime | Marca si la notificación fue leída. |

**Relaciones:**
- **user()**: Pertenece a un `User`.

### 20. PurchaseOrderProduct (Tabla Pivote)
Este modelo representa la tabla intermedia que conecta `PurchaseOrder` y `Product` en una relación de muchos a muchos.

**Campos Principales:**
| Campo | Tipo | Notas |
| --- | --- | --- |
| `purchase_order_id` | foreignId | ID de la orden de compra. |
| `product_id` | foreignId | ID del producto. |
| `quantity` | integer | Cantidad del producto en esa orden. |
| `unit_price` | decimal | Precio del producto para esa orden específica. |

**Relaciones:**
- **purchaseOrder()**: Pertenece a una `PurchaseOrder`.
- **product()**: Pertenece a un `Product`.
