# Título del PR

```
fix: Implementar sistema de envío de correos con CC en formulario de contacto
```

---

# Descripción del PR

```markdown
## 📋 Resumen

Este PR corrige y mejora el sistema de envío de correos del formulario de contacto, implementando un sistema robusto de notificaciones con copia (CC) al usuario y administrador, mejorando la experiencia de comunicación y trazabilidad.

## 🎯 Objetivo

Corregir el envío de correos del formulario de contacto para:
- Enviar correos al email de soporte (TO)
- Incluir al usuario que completa el formulario en CC
- Incluir al administrador en CC (si es diferente al email de soporte)
- Mejorar el manejo de errores y logging
- Implementar sistema de notificaciones toast

## 🔧 Cambios Principales

### Correcciones de Funcionalidad
- ✅ **Sistema de envío de correos mejorado**: Cambio de `Notification` a `Mail::to()` con soporte para CC
- ✅ **Notificación con CC**: Implementación de `SupportRequestNotification` con soporte para múltiples destinatarios en copia
- ✅ **Validación de emails**: Validación robusta de direcciones de correo antes del envío
- ✅ **Manejo de errores**: Mejora en el manejo de excepciones y logging detallado

### Archivos Modificados
- `app/Livewire/Support/ContactForm.php` - Lógica de envío de correos con CC
- `app/Mail/SupportRequestNotification.php` - Nueva clase Mailable con soporte para CC
- `app/Mail/SupportRequestMail.php` - Nueva clase de correo de soporte
- `resources/views/livewire/support/contact-form.blade.php` - Mejoras en la UI y modal de éxito
- `resources/views/emails/support-request-notification.blade.php` - Template de correo actualizado
- `config/mail.php` - Configuración de emails de soporte y admin

### Mejoras de Código
- ✅ Logging detallado para debugging
- ✅ Validación de emails con `filter_var()`
- ✅ Limpieza de espacios y comillas en configuración de emails
- ✅ Sistema de notificaciones toast para feedback al usuario
- ✅ Modal de éxito mejorado

### Tests Añadidos
- `tests/Feature/ContactFormTest.php` - Tests completos del formulario de contacto
- `tests/Feature/SupportRequestNotificationTest.php` - Tests del sistema de notificaciones
- `tests/Unit/SupportRequestNotificationTest.php` - Tests unitarios de la clase Mailable

## 📊 Estadísticas

- **19 archivos modificados**
- **1,419 inserciones**, **1,494 eliminaciones**
- Tests añadidos para cobertura completa

## 🧪 Testing

- ✅ Tests unitarios para `SupportRequestNotification`
- ✅ Tests de integración para el formulario de contacto
- ✅ Tests de validación de emails y configuración
- ✅ Verificación de envío de correos con CC correcto

## 📝 Configuración Requerida

Asegúrate de tener configurado en `.env` o `config/mail.php`:
```env
MAIL_SUPPORT_EMAIL=soporte@example.com
MAIL_ADMIN_EMAIL=admin@example.com  # Opcional, solo si es diferente al de soporte
```

## ✅ Checklist

- [x] Código sigue las convenciones del proyecto
- [x] Tests añadidos y pasando
- [x] Documentación actualizada
- [x] Logging implementado para debugging
- [x] Manejo de errores robusto
- [x] Validación de emails implementada
- [x] UI/UX mejorada con feedback al usuario

## 🔗 Relacionado

Este PR resuelve problemas de comunicación y trazabilidad en el sistema de soporte, permitiendo que los usuarios reciban copia de sus solicitudes de soporte.
```

---

# URL para crear el PR

```
https://github.com/Nucleo-Factory/olo-raga-orders/compare/develop...fix/contact-form-email-support-cc-notifications?expand=1
```

## Instrucciones para crear el PR

1. **Opción 1: Usando la URL directa**
   - Copia la URL de arriba y ábrela en tu navegador
   - GitHub detectará automáticamente la rama y la rama base
   - Completa el título y descripción usando el contenido de arriba

2. **Opción 2: Desde GitHub CLI**
   ```bash
   gh pr create --base develop --head fix/contact-form-email-support-cc-notifications --title "fix: Implementar sistema de envío de correos con CC en formulario de contacto" --body-file PR_DESCRIPTION.md
   ```

3. **Opción 3: Desde la interfaz web**
   - Ve a: https://github.com/Nucleo-Factory/olo-raga-orders
   - Haz clic en "Pull requests" → "New pull request"
   - Selecciona:
     - **Base branch**: `develop`
     - **Compare branch**: `fix/contact-form-email-support-cc-notifications`
   - Usa el título y descripción proporcionados arriba

## Verificar estado antes de crear el PR

```bash
# Verificar que estás en la rama correcta
git branch

# Verificar que los cambios están commiteados
git status

# Ver los últimos commits
git log --oneline -5

# Hacer push de la rama si no está en remoto
git push origin fix/contact-form-email-support-cc-notifications
```

