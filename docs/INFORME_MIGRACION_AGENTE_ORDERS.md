# Informe Técnico de Migración — Agente IA "RAGA-x" (Orders)

**Repositorio:** `danielnucleofactory/agente-ai-orders`
**Rama analizada:** `develop` (donde vive el agente; **no** existe en `main`)
**Rama de trabajo:** `claude/orders-agent-migration-analysis-k50at6` (rebasada sobre `develop`)
**Fecha:** 2026-08-01
**Alcance:** Análisis estático. No se modificó código de la aplicación.
**Objetivo:** Evaluar el acoplamiento al proveedor de LLM (hoy **Groq**) de cara a migrar a **Qwen (DashScope, API OpenAI-compatible)** o **Kimi K2 (Moonshot)**.

> **Nota de corrección:** una primera pasada se hizo sobre `main`, donde el agente **no está mergeado**, por lo que concluyó erróneamente que no existía. El agente vive completo en la rama **`develop`**. Este informe reemplaza esa conclusión.

---

## Resumen ejecutivo

- Sí existe un agente conversacional funcional: **"RAGA-x"**, un asistente logístico de **solo lectura** sobre las órdenes de compra.
- **Proveedor activo: Groq** (`llama-3.3-70b-versatile`) vía API **OpenAI-compatible** (`/openai/v1/chat/completions`), invocado con **HTTP directo de Laravel (Guzzle)** — sin SDK.
- Patrón: **tool-calling / ReAct** con bucle de hasta 6 iteraciones y **14 herramientas** que consultan PostgreSQL directamente.
- Fallbacks **Gemini** y **Cerebras** están **desactivados** ("pendiente aprobación del jefe"); además el path de Gemini llama a `CerebrasService`, **una clase que no existe** (bug latente).
- **No hay abstracción de proveedor**: los componentes Livewire instancian `GroqService` directamente y hay **dos adaptadores divergentes** (Groq estilo OpenAI vs Gemini estilo Google).
- **Buena noticia para la migración:** como el path activo ya es OpenAI-compatible, mover a **Qwen/DashScope o Kimi K2 es de esfuerzo BAJO-MEDIO** (base URL + modelo + key + una interfaz), siempre que se retire/ajuste el post-procesado acoplado a Llama.

---

## 1. Arquitectura

**Framework:** Laravel 11 + Livewire 3 (mismo stack que la app). El agente **no** es un microservicio aparte: es un par de componentes Livewire dentro del monolito.

**Puntos de entrada (2):**
- `app/Livewire/Agent/ChatPage.php` — chat de página completa. Ruta `routes/web.php:371` → `/agent` (`agent.index`) → `resources/views/agent/index.blade.php`.
- `app/Livewire/Agent/ChatWidget.php` — widget flotante embebible. **System prompt propio y más corto**, y **sin recorte de historial** (envía todos los mensajes).

**Patrón:** **tool-calling nativo (function calling) en bucle tipo ReAct**.
- `GroqService::chat()` → si la respuesta trae `tool_calls`, entra a `runToolLoop()` (`GroqService.php:93`), máx. **6 iteraciones** (`maxToolIterations = 6`, línea 13), con `tool_choice = 'auto'`.
- Las tools se ejecutan en proceso (no vía HTTP) y sus resultados se reinyectan como mensajes `role: tool` hasta que el modelo produce texto final.

**Cadena de proveedores** (`config/agent.php:18-35`):
| Nivel | Proveedor | Modelo | Estado |
|---|---|---|---|
| primary | groq | `llama-3.3-70b-versatile` | **Activo** |
| fallback | gemini | `gemini-2.5-flash` | Desactivado (comentado en `GroqService.php:41`) |
| tertiary | cerebras | `gpt-oss-120b` | Desactivado + **clase inexistente** |

### Diagrama de flujo de una ejecución típica

```
Usuario  (vista Livewire /agent o widget)
   │  sendMessage()
   ▼
ChatPage::sendMessage()  (app/Livewire/Agent/ChatPage.php:36)
   │  1. arma system prompt dinámico (identidad + contexto temporal + reglas de routing)
   │  2. toma los últimos 10 mensajes (array_slice(-10), línea 312)
   │  3. adjunta las 14 tool definitions
   ▼
GroqService::chat(messages, tools)  (GroqService.php:32)
   │  POST https://api.groq.com/openai/v1/chat/completions
   ▼
Groq / llama-3.3-70b-versatile
   │  ¿tool_calls?
   ├── sí ─► runToolLoop() (máx 6 it.)
   │          └─ RagaOrdersToolService::execute(tool, args)  (RagaOrdersToolService.php:20)
   │                ├─ query_operational_data ─► OperationalDataQueryService ─► DB::table (whitelisted)
   │                └─ resto de tools ─► AgentOrdersController (in-process) ─► DB::table('purchase_orders')
   │          └─ resultados como role:tool ─► vuelve a Groq
   └── no ──► texto final
   ▼
cleanResponse()  (GroqService.php:185)  — limpia JSON/razonamiento filtrado por Llama
   ▼
$messages[] (assistant)  ─►  render Livewire
```

**Observabilidad:** logging estructurado con `Log::info/warning/error` en cada tool y transición de key (sin datos sensibles).

---

## 2. Dependencia del LLM

**SDK:** ninguno. Todo es `Illuminate\Support\Facades\Http` (Guzzle) con payloads construidos a mano. No hay `openai-php`, `prism-php`, ni cliente de Groq/Gemini.

| # | Archivo:línea | Proveedor | Endpoint | Modelo | Parámetros | Features usadas |
|---|---|---|---|---|---|---|
| 1 | `GroqService.php:29` + `:60-64` (primera llamada) | **Groq** | `api.groq.com/openai/v1/chat/completions` | `llama-3.3-70b-versatile` (`config/agent.php:21`) | `max_tokens=1024`, `temperature=0.3` (`config/agent.php:43-44`), `tools`, `tool_choice='auto'` | **Tool use nativo** (OpenAI schema), multi-turno |
| 2 | `GroqService.php:120-133` (dentro del bucle de tools) | **Groq** | idem | idem | idem + `tools`/`tool_choice` | Reinyección de `role:tool` |
| 3 | `GeminiService.php:18-21` + `:74` | **Gemini** (fallback, off) | `generativelanguage.googleapis.com/v1beta/models/{model}:generateContent` | `gemini-2.5-flash` | `temperature`, `maxOutputTokens` en `generationConfig` | **Tool use** pero con **schema distinto** (`contents`/`parts`/`systemInstruction`/`functionDeclarations`) |
| 4 | `GeminiService.php:190-193` (respuesta a tools) | **Gemini** (off) | idem | idem | idem | `functionResponse` |

**Features del proveedor:**
- ✅ **Tool use nativo / function calling** — sí, base del agente.
- ✅ **Multi-key cascade** — específico de Groq: ante `429` rota a la siguiente `GROQ_API_KEY_n` (`GroqService.php:34-38, 135-146`).
- ❌ **Structured output / JSON mode** — no se usa (se depende del texto libre + limpieza heurística).
- ❌ **Prompt caching** — no; el system prompt (~2.500-3.000 tokens) se reconstruye y reenvía **cada turno**.
- ❌ **Streaming** — no; `Http::post` síncrono con `timeout(30)`. La UI muestra un spinner (`isLoading`).
- ❌ **Visión / multimodal** — no.

### Portabilidad a Qwen (DashScope OpenAI-compatible) / Kimi K2 (Moonshot)

| Componente | Portabilidad | Nota |
|---|---|---|
| **Path Groq** (`GroqService`) | **ALTA** | Ya es OpenAI-compatible. Migrar = cambiar base URL (`https://dashscope-intl.aliyuncs.com/compatible-mode/v1/chat/completions` para Qwen; `https://api.moonshot.ai/v1/chat/completions` para Kimi), el modelo (`qwen-max`/`qwen-plus` o `kimi-k2-…`) y la key. `tools` + `tool_choice=auto` funcionan igual en ambos. |
| **Path Gemini** (`GeminiService`) | **NO portable directo** | Schema Google propietario (`contents`/`parts`/`functionDeclarations`). No aplica a Qwen/Kimi; si se quisiera un fallback OpenAI-compatible convendría descartar este adaptador y clonar el de Groq. |
| **`cleanResponse()`** (`GroqService.php:185-223`) | **Riesgo** | Heurística acoplada a fugas de `llama-3.3` (frases "We need to call", nombres de tools, bloques `{...}`). Qwen/Kimi filtran de forma distinta → puede **sobre-limpiar o dejar pasar** ruido. Revisar/retirar tras migrar. |
| **`tool_calls`/`arguments` (JSON string)** | ALTA | Qwen y Kimi devuelven `tool_calls[].function.arguments` como string JSON igual que OpenAI/Groq; el `json_decode` de `GroqService.php:112` sirve tal cual. |

> **Marcado explícito de lo NO portable directamente:** (a) todo `GeminiService.php` (schema Google); (b) la cascada multi-key es un patrón de Groq que en Qwen/Kimi normalmente se resuelve con una sola key + reintento/backoff; (c) `cleanResponse()` está calibrado para Llama.

---

## 3. Tools y acceso a datos

**14 herramientas** declaradas en `RagaOrdersToolService::getToolDefinitions()` (`RagaOrdersToolService.php:144-314`) y despachadas por `execute()` (`:20-39`):

`get_full_summary`, `get_orders_in_transit`, `get_orders_in_transshipment`, `get_orders_with_alerts`, `get_all_delayed_orders`, `get_orders_by_ata`, `get_orders_by_atd`, `get_orders_by_eta`, `get_shipment_eta`, `get_orders_summary`, `get_orders_pending_confirmation`, `get_orders_delayed_in_transit`, `get_teus_summary`, `query_operational_data`.

**Acceso a datos: DB DIRECTA a PostgreSQL** (Laravel Query Builder, **no** Eloquent), por dos caminos:

1. **La mayoría de tools** → `RagaOrdersToolService::call()` (`:54`) crea un `Request` interno y llama **en proceso** a `AgentOrdersController` (`app(AgentOrdersController::class)`), que consulta `DB::table('purchase_orders')` (`AgentOrdersController.php:26+`).
2. **`query_operational_data`** → `OperationalDataQueryService::run()` (`OperationalDataQueryService.php:17`) construye la query con `selectRaw/groupByRaw/whereRaw`, **validando todo contra un catálogo whitelist** (`config/agent.php` vía `OperationalDataCatalog`): `entities`, `metrics`, `group_by`, `filters`, `operators` permitidos.

**Tablas/datos de Raga que toca:**
- `purchase_orders` — tabla principal (campos: `porth_phase`, `arrival_status`, `delay_days`, `date_ata`, `date_atd`, `porth_first_eta`, `container_type`, `shipping_line`, `trading_company`, `route_label`, `vendor_id`, `company_id`, `deleted_at`…).
- `vendors` — vía `LEFT JOIN` (para nombre de proveedor).

**Aislamiento de datos:** siempre por `company_id`
- en tools directas: `Auth::user()->company_id` (`RagaOrdersToolService.php:17`) → header `X-Company-Id` → `AgentOrdersController::getCompanyId()`.
- en `query_operational_data`: `->where($companyCol, $companyId)->whereNull(deleted_at)` (`OperationalDataQueryService.php:40-42`).

**Detalle relevante para migración/infra:** existen endpoints REST `/api/agent/*` (`routes/api.php:125-134`) protegidos por `AgentTokenAuth` (header `X-Agent-Token`), pero **el agente Livewire NO los usa por HTTP**: llama al controlador en proceso. La config `services.agent.base_url`/`api_token` queda para un consumidor externo, no para el flujo actual.

---

## 4. Lógica por cliente (OLO / Dokka / Kerry / GLF)

**En el código del agente NO hay ninguna ramificación por variante de cliente.** Verificado sobre `GroqService`, `GeminiService`, `RagaOrdersToolService`, `OperationalDataQueryService`, `OperationalDataCatalog`, `ChatPage`, `ChatWidget`, `AgentOrdersController` y `config/agent.php`: **cero coincidencias** de `Dokka`, `Kerry`, `GLF`; y `OLO` solo aparece como parte de URLs de marca en `config/services.php`.

- La segmentación real es **multi-empresa genérica** por `company_id` (del usuario autenticado), no por nombres de variante.
- `trading_company` **no es un branch**: es un **parámetro de filtro/agrupación** genérico expuesto como filtro (`config/agent.php:212`), group_by (`:143`) y query param en `AgentOrdersController`. El LLM lo rellena con lo que pida el usuario; no hay `if cliente == X`.

**Implicación para análisis de impacto cross-variante:** un cambio en el agente **no** requiere hoy revisar cuatro rutas de código porque no existen. El vector de impacto cross-cliente está en los **datos** (`company_id`/`trading_company`) y en el **catálogo whitelist** (`config/agent.php`): si una empresa usa enums/estados distintos (p.ej. `arrival_status` mezcla `delayed` y `Atrasado`), el catálogo y el system prompt deben cubrir ambas formas. Ese es el único acoplamiento "por cliente" real y es de datos, no de flujo.

---

## 5. Prompts

**Ubicación:** los system prompts están **embebidos en código PHP**, no en archivos de plantilla:
- `ChatPage.php:110-308` — prompt **grande** (~200 líneas), construido dinámicamente en cada `sendMessage()`.
- `ChatWidget.php:56-65` — prompt **corto** (~10 líneas), distinto y más laxo.

**Longitud aproximada (ChatPage):** ~1.360 palabras → **≈ 2.500-3.000 tokens** por turno (denso en tablas de fechas). El de `ChatWidget` ronda **~150 tokens**.

**Supuestos implícitos sobre el modelo (críticos para migrar):**
- **Idioma:** respuesta **siempre en español** (regla explícita).
- **Formato:** **prohibido Markdown** y símbolos de formato — regla que existe porque Llama tiende a formatear; su necesidad y redacción pueden variar en Qwen/Kimi.
- **Razonamiento filtrado:** el prompt + `cleanResponse()` asumen que el modelo **puede filtrar su cadena de razonamiento y JSON** en el texto; es un supuesto de comportamiento de Llama.
- **Routing determinista NL→tool:** el prompt codifica un mapa muy detallado de "si el usuario dice X → usa tool Y con estos parámetros" (fechas exactas precalculadas por el servidor e inyectadas), además de reglas anti-alucinación ("todos los números vienen de las tools", "prohibido preguntas de seguimiento", "nunca limit:1"). Depende fuertemente de la **capacidad de instruction-following** del modelo destino.
- **Inyección de contexto temporal:** el servidor precomputa decenas de fechas (hoy, semanas, meses, "en N días") y las mete en el prompt para evitar que el modelo calcule fechas. Esto es agnóstico de proveedor (bien), pero infla tokens cada turno.
- **Sin caching:** al reconstruirse por turno, no aprovecha prompt caching de ningún proveedor.

---

## 6. Estado y memoria

- **Historial de conversación:** en memoria de componente Livewire — propiedad pública `array $messages` (`ChatPage.php:12`, `ChatWidget.php:13`). **No se persiste en base de datos.** `mount()` reinicia con un saludo; `clearChat()` = `mount()`.
- **Ventana enviada al modelo:**
  - `ChatPage`: **solo los últimos 10 mensajes** (`array_slice($this->messages, -10)`, `:312`) + system prompt.
  - `ChatWidget`: **todos** los mensajes acumulados (sin recorte, `:69-76`) → crece sin límite en sesiones largas (riesgo de exceder contexto/costos).
- **Persistencia entre ejecuciones:** ninguna. Recargar la página o abrir una sesión nueva pierde la conversación. No hay tabla de historial del agente, ni memoria vectorial, ni RAG.
- **Estado de tenant:** `company_id` del usuario autenticado (Laravel Auth/sesión), aplicado a cada tool.
- **Idempotencia/side-effects:** el agente es **solo lectura** por diseño (reglas de seguridad del prompt + tools que solo hacen `SELECT`); no escribe estado de dominio.

---

## 7. Riesgos de migración (top 5 acoplamientos)

Ordenados por impacto. Esfuerzo = trabajo para abstraer/resolver detrás de una interfaz `LlmProvider` común.

| # | Punto de acoplamiento | Por qué complica la migración | Esfuerzo |
|---|---|---|---|
| 1 | **No existe interfaz de proveedor; dos adaptadores divergentes.** `ChatPage`/`ChatWidget` inyectan `GroqService` directo (`boot()`), y `GroqService` (schema OpenAI) y `GeminiService` (schema Google) no comparten contrato. | Cambiar de proveedor hoy implica tocar los servicios y ambos componentes. Hay que introducir un `LlmProviderInterface { chat(messages, tools): string }` y hacer que Livewire dependa de la interfaz (via `config/agent.php`). | **MEDIO** |
| 2 | **`cleanResponse()` calibrado a Llama.** (`GroqService.php:185-223`) Elimina JSON y frases de razonamiento típicas de `llama-3.3`. | Qwen/Kimi filtran distinto: la heurística puede borrar texto legítimo o dejar pasar ruido. Idealmente se sustituye por `response_format`/JSON mode o manejo de `stop`, y se re-valida con prompts reales. | **MEDIO** |
| 3 | **System prompt gigante, por-turno, sin caching, con reglas modelo-específicas** (~2.5-3k tokens en `ChatPage.php:110-308`; formato/idioma/anti-markdown pensados para Llama). | El coste/latencia por turno es alto y el instruction-following no es idéntico entre modelos; migrar exige re-probar todas las reglas de routing NL→tool y, a ser posible, activar prompt caching del proveedor destino. | **MEDIO-ALTO** |
| 4 | **Fallback roto / config inconsistente.** `GeminiService` invoca `CerebrasService` (`:86,99,123,203,221`) que **no existe** → fatal si se activa el fallback; `config/agent.php:33` etiqueta Cerebras como `gpt-oss-120b`. | Cualquier plan que reactive el fallback (o que use Qwen/Kimi como fallback) choca con esta clase ausente. Debe resolverse antes de tocar la cascada. | **BAJO** |
| 5 | **Config del proveedor dispersa + cascada multi-key acoplada a Groq.** Modelo/params en `config/agent.php`, keys/endpoints en `config/services.php`; la rotación `GROQ_API_KEY_1..5` vive dentro de `GroqService` (`:20-38, 135-146`) y el endpoint está **hardcodeado** (`:29`). `.env.example` **no** documenta `GROQ/GEMINI/CEREBRAS/AGENT_*`. | Un proveedor nuevo necesita su propia base URL/params; el endpoint hardcodeado y la lógica de keys mezclada con el transporte dificultan parametrizar. Conviene mover base URL a config y aislar la política de reintentos. | **MEDIO** |

**Consideración de seguridad (no bloqueante para migrar):** `query_operational_data` usa `selectRaw/groupByRaw/whereRaw` con fragmentos SQL del catálogo. Hoy es seguro porque **todo pasa por whitelist** (`OperationalDataQueryService::validatePayload`/`applyFilters`), pero al ampliar el catálogo hay que mantener esa disciplina; el LLM nunca debe poder inyectar SQL arbitrario.

---

## Ruta de migración recomendada (Groq → Qwen/Kimi)

1. **Introducir `LlmProviderInterface`** con `chat(array $messages, array $tools): string` y registrar la implementación activa desde `config/agent.php` (`models.primary.provider`). `ChatPage`/`ChatWidget` dependen de la interfaz, no de `GroqService`.
2. **Clonar el adaptador OpenAI-compatible** para Qwen/DashScope y Kimi/Moonshot (misma forma que `GroqService`, cambian base URL + modelo + auth). Mover el endpoint a config (`services.<provider>.base_url`).
3. **Neutralizar `cleanResponse()`**: probar Qwen/Kimi con el prompt actual; si no filtran razonamiento, retirar la heurística o reemplazarla por JSON mode / `tool_choice` estricto.
4. **Unificar los dos system prompts** y evaluar recorte + prompt caching; re-validar las reglas de routing NL→tool con un set de preguntas reales (las "quick questions" y los casos del prompt son un buen banco).
5. **Arreglar/retirar la cascada de fallback** (`CerebrasService` inexistente) antes de reconfigurar niveles; si se quiere fallback, que sea otro proveedor OpenAI-compatible reutilizando el adaptador del punto 2.
6. **Documentar env** (`GROQ_API_KEY*`, `GEMINI_API_KEY`, `CEREBRAS_API_KEY`, `AGENT_API_TOKEN`, `AGENT_BASE_URL`) en `.env.example`.

**Esfuerzo global estimado:** BAJO-MEDIO para el "happy path" (Groq→Qwen o Groq→Kimi sin fallback), gracias a la compatibilidad OpenAI del path activo; el grueso del trabajo es la **interfaz de proveedor** y **re-validar el prompt + `cleanResponse()`** contra el comportamiento del nuevo modelo.
