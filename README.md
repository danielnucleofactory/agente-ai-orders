# OLO Raga Orders - Sistema de Gestión de Órdenes de Compra
![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-4d51b3?style=for-the-badge&logo=livewire&logoColor=white)

Este repositorio contiene el código fuente del sistema OLO Raga Orders, una aplicación web construida con Laravel para la gestión avanzada de órdenes de compra, logística y seguimiento.

## Propósito del Proyecto

El objetivo de esta aplicación es centralizar y optimizar el ciclo de vida de las órdenes de compra, proporcionando visibilidad en tiempo real a todas las partes involucradas, desde la creación del pedido hasta la entrega final.

---

## 🚀 Guía de Inicio Rápido

Para configurar el proyecto en un entorno de desarrollo local, sigue estos pasos:

1.  **Clonar el Repositorio**
```bash
    git clone https://github.com/danielnucleofactory/agente-ai-orders.git
    cd agente-ai-orders
    git checkout develop
```

2.  **Instalar Dependencias**
    Asegurate de tener Composer instalado.
```bash
    composer install
```

3.  **Configuración del Entorno**
    Copia el archivo de ejemplo `.env.example` y crea tu propio archivo `.env`.
```bash
    cp .env.example .env
```
    Genera la clave de la aplicación.
```bash
    php artisan key:generate
```
    Configura las credenciales de tu base de datos y otros servicios en el archivo `.env`.

4.  **Ejecutar Migraciones y Seeders**
    Esto creará la estructura de la base de datos y cargará los datos iniciales necesarios.
```bash
    php artisan migrate --seed
```

5.  **Iniciar el Servidor de Desarrollo**
```bash
    php artisan serve
```
    La aplicación estará disponible en `http://localhost:8000`.

---

## 🤖 Agente IA

Este proyecto incluye un Agente conversacional integrado que permite consultar información operativa de las órdenes de compra en lenguaje natural.

### Configuración del Agente IA

Para que el Agente IA funcione, debes agregar las siguientes variables a tu archivo `.env`:

```env
# Groq API - Modelo de IA (llama-3.3-70b-versatile)
# Crea tu API Key gratis en: https://console.groq.com/keys
GROQ_API_KEY=tu_groq_api_key_aqui

# Agente IA - Endpoints REST internos
AGENT_API_TOKEN=tu_token_aqui
AGENT_BASE_URL=http://localhost/api/agent
```

Para generar el `AGENT_API_TOKEN` corre:
```bash
php artisan tinker --execute="echo Str::random(64);"
```

### Herramientas disponibles

El agente puede responder consultas sobre:

- Ordenes de compra en transito
- Embarques en puerto de transbordo
- Ordenes con alertas o incidencias activas
- Ordenes por fecha ATA
- ETA/ATA de un embarque especifico por numero de documento
- Resumen general de ordenes
- Ordenes pendientes de confirmacion
- Ordenes retrasadas en transito

### Limites del plan gratuito de Groq

El plan gratuito permite **100,000 tokens por dia**. Cada consulta al agente consume aproximadamente 1,000 a 2,000 tokens. Para produccion con alto volumen de usuarios se recomienda el plan Developer en `https://console.groq.com/settings/billing`.

### Limpiar cache despues de cambios

**En Windows:**
```powershell
.\cache.ps1
```

**En Linux o Mac:**
```bash
php artisan optimize:clear
php artisan optimize
```

---

## 📚 Documentación Completa

Toda la documentación técnica y funcional del proyecto se encuentra centralizada en la carpeta `/docs`. **Es el punto de partida obligatorio para cualquier desarrollador o integrador.**

> **[Haz clic aquí para empezar a explorar la documentación.](./docs/README.md)**

### ¿Qué encontrarás en la documentación?

-   **Referencia de la API:** Detalles exhaustivos de todos los endpoints, ejemplos de código en múltiples lenguajes y mapeo de campos.
-   **Arquitectura del Sistema:** Diagramas, descripción del modelo de datos y decisiones de diseño.
-   **Guías y Procedimientos:** Tutoriales para tareas comunes y guías para mantener la documentación actualizada.

---

## 🛠 Comandos Útiles

-   **Ejecutar tests:**
```bash
    php artisan test
```

-   **Enviar resúmenes de notificaciones manualmente:**
```bash
    php artisan notifications:send-daily
    php artisan notifications:send-weekly
```

-   **Limpiar notificaciones antiguas:**
```bash
    php artisan notifications:prune
```

---

## Contribuciones

Para contribuir al proyecto, por favor sigue el flujo de trabajo estándar:

1.  Crea una nueva rama para tu feature (`git checkout -b feature/nombre-feature`).
2.  Realiza tus cambios y haz commit.
3.  Asegurate de actualizar la documentacion relevante siguiendo la [guía de actualización](./docs/guides/PROMPT_UPDATE_DOCS.md).
4.  Sigue las [guías de desarrollo con IA](./docs/guides/AI_DEVELOPMENT_GUIDELINES.md) para mantener la calidad del código.
5.  Consulta las [mejores prácticas del proyecto](./docs/guides/PROJECT_BEST_PRACTICES.md) para estándares de desarrollo.
6.  Abre un Pull Request para su revisión.