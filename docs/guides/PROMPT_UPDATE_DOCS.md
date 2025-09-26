# Guía para Actualizar la Documentación con IA

Este documento es una guía de procedimientos para mantener la documentación del proyecto sincronizada con el código fuente utilizando un asistente de IA.

### ¿Cuándo Usar Este Documento?

Usa los prompts de este archivo **antes de finalizar una rama o un Pull Request** que contenga cambios en:
- La lógica de la API (nuevos endpoints, cambio de parámetros, etc.).
- La estructura de la base de datos (nuevos modelos, migraciones, campos).
- La arquitectura fundamental del sistema.

### ¿Cómo Usarlo?

1.  **Identifica el tipo de cambio** que realizaste.
2.  **Copia el prompt** correspondiente de la sección de abajo.
3.  **Pégalo en la conversación** con el asistente de IA.
4.  **Proporciona el contexto necesario**, como las rutas de los archivos modificados o el resultado de `git diff`.

---

## Catálogo de Prompts

### 1. Para Cambios en la API
*Úsalo cuando modifiques controladores, archivos de rutas (`routes/api.php`) o Form Requests.*

```markdown
Hola, he realizado cambios en la API. Necesito que actualices la documentación para que refleje estas modificaciones.

**Contexto de los Cambios:**
*(Pega aquí el `git diff` o describe los cambios. Ejemplo: "He añadido un nuevo endpoint `GET /api/reports` en `routes/api.php` y su lógica está en `ReportController.php`")*

**Acciones Requeridas:**
1.  **Analiza los cambios** en los archivos proporcionados.
2.  **Actualiza el archivo de referencia de la API:** `docs/api/REFERENCE.md` para añadir/modificar/eliminar los endpoints correspondientes.
3.  **Actualiza el mapeo de campos:** `docs/api/FIELD_MAPPING.md` si se han añadido o modificado campos en el cuerpo (body) de las peticiones o respuestas.
4.  **Confirma que ambas documentaciones estén sincronizadas** entre sí y con el código.
```

### 2. Para Cambios en la Base de Datos o Modelos
*Úsalo cuando modifiques un modelo de Eloquent (`app/Models/`), crees o modifiques una migración.*

```markdown
Hola, he realizado cambios en la estructura de datos. Necesito que actualices la documentación para que refleje estas modificaciones.

**Contexto de los Cambios:**
*(Pega aquí el `git diff`, el contenido del modelo o de la migración. Ejemplo: "He añadido un campo `priority` (string) al modelo `PurchaseOrder` y su migración correspondiente.")*

**Acciones Requeridas:**
1.  **Analiza los cambios** en el modelo/migración proporcionada.
2.  **Actualiza el diccionario de datos principal:** `docs/architecture/DATA_MODEL.md` para reflejar los nuevos campos, tipos de datos o relaciones en el modelo afectado.
3.  **Actualiza el mapeo de campos de la API:** `docs/api/FIELD_MAPPING.md` si los nuevos campos van a ser expuestos a través de la API.
4.  **Confirma que ambas documentaciones estén sincronizadas.**
```

### 3. Para Nuevas Funcionalidades o Cambios de Arquitectura
*Úsalo para cambios más grandes que no encajan en las categorías anteriores.*

```markdown
Hola, he implementado una nueva funcionalidad y necesito ayuda para documentarla.

**Contexto de la Nueva Funcionalidad:**
*(Describe la funcionalidad a alto nivel. Ejemplo: "He creado un nuevo sistema de notificaciones por roles. Los archivos principales son `NotificationService.php`, el modelo `Notification.php` y las vistas en `resources/views/notifications/`.")*

**Acciones Requeridas:**
1.  **Analiza la descripción y los archivos** relacionados con la nueva funcionalidad.
2.  **Sugiere qué documentos existentes** deberían ser actualizados (ej: `DATA_MODEL.md` para el nuevo modelo, un nuevo documento en `architecture/` para el flujo, etc.).
3.  **Crea un borrador** para un nuevo documento de arquitectura o guía en `docs/architecture/` o `docs/guides/` si lo consideras necesario para explicar el nuevo sistema.
```
