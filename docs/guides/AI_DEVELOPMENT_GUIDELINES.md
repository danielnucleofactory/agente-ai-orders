# Guías de Desarrollo con IA - Buenas Prácticas

Este documento establece las directrices y mejores prácticas para el desarrollo con asistencia de IA en el proyecto OLO Raga Orders, con el objetivo de mantener la calidad del código y evitar problemas comunes.

## 🎯 Objetivos

- Mantener la consistencia del código
- Evitar la acumulación de archivos temporales
- Preservar la arquitectura del proyecto
- Facilitar el mantenimiento a largo plazo
- Asegurar la calidad de las contribuciones

---

## 📋 Directrices Generales

### 1. **Antes de Solicitar Asistencia de IA**

✅ **SÍ hacer:**
- Revisar la documentación existente en `/docs`
- Entender la arquitectura actual del proyecto
- Identificar claramente el problema o funcionalidad a implementar
- Preparar contexto específico sobre los cambios necesarios

❌ **NO hacer:**
- Solicitar cambios sin contexto específico
- Pedir implementaciones que contradigan la arquitectura existente
- Solicitar creación de archivos sin justificación clara

### 2. **Gestión de Archivos Temporales**

#### Archivos que NO deben ser creados:
- `temp_*.php`, `test_*.php`, `debug_*.php`
- `backup_*.php`, `old_*.php`
- Archivos con nombres genéricos como `helper.php`, `utils.php`
- Archivos de prueba en producción (`test.php`, `demo.php`)

#### Archivos permitidos temporalmente:
- `*.tmp` (deben ser eliminados inmediatamente)
- Archivos de migración con timestamps específicos
- Archivos de configuración con nombres descriptivos

### 3. **Estructura de Prompts Efectivos**

#### Template para Nuevas Funcionalidades:
```markdown
**Contexto del Proyecto:**
- Proyecto: OLO Raga Orders (Laravel + Livewire)
- Arquitectura: [describir módulo específico]
- Documentación relevante: [enlaces a docs existentes]

**Objetivo:**
[Descripción específica de lo que se necesita]

**Restricciones:**
- NO crear archivos temporales
- Seguir la estructura existente en [directorio específico]
- Mantener consistencia con [patrón específico]
- Actualizar documentación en [archivos específicos]

**Archivos a Modificar/Crear:**
[Lista específica de archivos con justificación]
```

---

## 🔧 Procesos Específicos por Tipo de Desarrollo

### Desarrollo de API

#### Antes de implementar:
1. Revisar `docs/api/REFERENCE.md`
2. Consultar `docs/api/FIELD_MAPPING.md`
3. Verificar endpoints existentes en `routes/api.php`

#### Durante el desarrollo:
- Seguir el patrón de controladores existente
- Implementar validación con Form Requests
- Mantener consistencia en respuestas JSON
- Documentar nuevos endpoints inmediatamente

#### Después del desarrollo:
- Actualizar `docs/api/REFERENCE.md`
- Actualizar `docs/api/FIELD_MAPPING.md`
- Ejecutar tests existentes
- Verificar que no se crearon archivos temporales

### Desarrollo de Modelos y Base de Datos

#### Antes de implementar:
1. Revisar `docs/architecture/DATA_MODEL.md`
2. Consultar migraciones existentes
3. Verificar relaciones con otros modelos

#### Durante el desarrollo:
- Crear migraciones con nombres descriptivos
- Seguir convenciones de Laravel
- Implementar relaciones correctamente
- Agregar validaciones en el modelo

#### Después del desarrollo:
- Actualizar `docs/architecture/DATA_MODEL.md`
- Ejecutar migraciones en entorno de prueba
- Verificar integridad de datos
- Limpiar migraciones fallidas

### Desarrollo de Interfaces (Livewire)

#### Antes de implementar:
1. Revisar componentes existentes en `app/Livewire/`
2. Consultar patrones de UI en `app/View/Components/`
3. Verificar estilos en `public/css/`

#### Durante el desarrollo:
- Seguir convenciones de Livewire
- Mantener separación de responsabilidades
- Reutilizar componentes existentes
- Implementar validación del lado cliente

#### Después del desarrollo:
- Verificar responsividad
- Probar funcionalidad completa
- Limpiar archivos CSS/JS temporales
- Actualizar documentación de componentes

---

## 🚨 Checklist de Calidad

### Antes de Commit:
- [ ] No hay archivos temporales en el proyecto
- [ ] Todos los archivos nuevos tienen nombres descriptivos
- [ ] La documentación está actualizada
- [ ] Los tests pasan correctamente
- [ ] El código sigue las convenciones del proyecto
- [ ] No hay código comentado innecesario
- [ ] Las migraciones están en orden cronológico

### Antes de Pull Request:
- [ ] Revisar `git status` para archivos no deseados
- [ ] Ejecutar `php artisan test`
- [ ] Verificar que no hay archivos `.tmp`, `.bak`, `.old`
- [ ] Confirmar que la documentación refleja los cambios
- [ ] Probar funcionalidad en entorno local
- [ ] Verificar que no se rompió funcionalidad existente

---

## 🛠 Comandos Útiles para Mantenimiento

### Limpiar archivos temporales:

#### Usando scripts automatizados:
```bash
# Linux/Mac
./scripts/cleanup-temp-files.sh

# Windows PowerShell
.\scripts\cleanup-temp-files.ps1
```

#### Comandos manuales:
```bash
# Buscar archivos temporales
find . -name "*.tmp" -o -name "*.bak" -o -name "*.old" -o -name "temp_*"

# Eliminar archivos temporales (¡CUIDADO!)
find . -name "*.tmp" -delete
```

### Verificar estado del proyecto:
```bash
# Ver archivos no rastreados
git status

# Ver archivos ignorados
git status --ignored

# Verificar sintaxis PHP
php -l app/Models/Modelo.php
```

### Limpiar caché después de cambios:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

---

## 📚 Recursos Adicionales

### Documentación del Proyecto:
- [Documentación Principal](../README.md)
- [Referencia de API](../api/REFERENCE.md)
- [Modelo de Datos](../architecture/DATA_MODEL.md)
- [Guía de Actualización de Docs](./PROMPT_UPDATE_DOCS.md)

### Enlaces Externos:
- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [PHP Best Practices](https://www.php.net/manual/en/language.oop5.best-practices.php)

---

## ⚠️ Advertencias Importantes

1. **NUNCA** commits archivos temporales o de prueba
2. **SIEMPRE** actualiza la documentación cuando cambies la API
3. **VERIFICA** que los cambios no rompan funcionalidad existente
4. **CONSULTA** la documentación antes de implementar nuevas funcionalidades
5. **MANTÉN** la consistencia con la arquitectura existente

---

## 📞 Soporte

Si tienes dudas sobre estas directrices o necesitas ayuda para implementar alguna funcionalidad siguiendo estas mejores prácticas, consulta:

1. La documentación existente en `/docs`
2. El código fuente de funcionalidades similares
3. Los ejemplos en `docs/guides/PROMPT_UPDATE_DOCS.md`

**Recuerda:** El objetivo es mantener un proyecto limpio, bien documentado y fácil de mantener a largo plazo.
