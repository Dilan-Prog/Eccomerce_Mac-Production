# Atribución por folio: del sitio a Google Ads (vía Kommo y n8n)

Documento de operación para el dueño del negocio y para quien configure n8n.
Rama: `feature/atribucion-folio`. Última revisión de las líneas de código citadas: 18 de octubre de 2026.

> Estado: el backend, el JavaScript y este documento se construyeron en paralelo en la misma rama. Los nombres de campos, variables y rutas salen del diseño aprobado y del código ya presente (`config/attribution.php`, `AttributionApiTokenMiddleware`, modelo `TrackConversion`). Si algo del endpoint no coincide con lo que ves al probarlo, manda el código: la sección 3 se debe alinear con `AttributionLookupService` (y `AttributionLookupController`) al integrar la rama. La sección 10 (Laboratorio de atribución, columna `is_test`) describe el diseño aprobado, que se implementó en paralelo; verifica nombres de pantallas y botones contra el admin al integrar.

---

## 1. Resumen

### Qué es el folio MN

Un **folio** es un código corto, por ejemplo `MN7K4Q2`:

- Empieza con `MN` (Mac del Norte) y le siguen **5 caracteres** tomados de `23456789BCDFGHJKMNPQRSTVWXYZ` (sin vocales ni caracteres que se confunden como 0/O o 1/I/L, para que no forme palabras ni se dicte mal).
- Hay unos 17 millones de combinaciones.
- Se genera **en el navegador del visitante en el momento del clic** y se guarda en la base de datos junto con todo lo que sabemos de ese visitante: anuncio (`gclid`, `gbraid`, `wbraid`, `fbclid`), campaña (`utm_*`), página de aterrizaje, producto que veía, etc.
- El mismo folio viaja **dentro del mensaje** que el cliente va a mandar: en WhatsApp ("Hola, me interesa: DC1010. Folio: MN7K4Q2") o en el asunto del correo ("Cotización web - Folio MN7K4Q2").

Cuando ese mensaje llega a Kommo, n8n puede leer el folio, preguntarle al sitio "¿de qué anuncio vino este folio?" y subir la conversión a Google Ads con el `gclid` correcto. La lógica de atribución vive en el sitio y en n8n, no en el CRM (si mañana cambian de Kommo a un CRM propio, el flujo no cambia).

El folio de cotización formal `COT-…` (el que sale en el PDF) ya existía, **no se reemplaza** y también se puede consultar con el mismo endpoint.

### Cómo fluye

```
 Anuncio de Google (gclid)
        |
        v
 Sitio macdelnorte (cookie mdn_attr + localStorage guardan gclid/UTM/sesión)
        |
        |  el cliente toca el botón de WhatsApp / correo / cotización
        v
 El navegador genera el folio MN7K4Q2, lo guarda con POST /track-conversion
 y abre:  https://wa.me/52...?text=Hola, me interesa: ... Folio: MN7K4Q2
        |
        v
 El cliente envía el mensaje  -->  Kommo (lead nuevo con el texto del mensaje)
        |
        v
 n8n: extrae MN7K4Q2 del texto del mensaje
        |
        v
 GET /api/attribution/lookup?folio=MN7K4Q2   (Authorization: Bearer <token>)
        |   devuelve gclid, edad del click, conflicto de toques, ambigüedad...
        v
 n8n aplica la política (sección 4) y, si procede,
        |
        v
 Sube la conversión a Google Ads (gclid + hora + order_id)
```

Las llamadas telefónicas **no pueden llevar folio** (no hay texto). Para ellas existe el modo de consulta por hora (sección 3), que es probabilístico y no debe usarse como conversión principal.

### Qué NO resuelve

- Solo se atribuye a quien pasó por el sitio con un click id vigente. Quien llega por buscador de marca, directo o recomendación queda legítimamente como `none`.
- Un `gclid` guardado no significa que el contacto sea un cliente. Lo que separa clientes de curiosos es el cruce con Kommo.

---

## 2. Variables `.env` nuevas

Todas se leen desde `config/attribution.php` (nunca con `env()` directo en el código, para que funcionen con `config:cache`). Ya están (comentadas) en `.env.example`.

| Variable | Valor por defecto | Qué hace |
|---|---|---|
| `ATTRIBUTION_API_TOKEN` | vacío | **Respaldo** del token Bearer del endpoint `GET /api/attribution/lookup` (lo normal es crear el token desde el panel: ver "Cómo crear el token"). Vacío, o de menos de 32 caracteres, este respaldo no existe; si además no hay ningún token activo de atribución creado en el panel = endpoint cerrado: responde 503 a todos. Es independiente del token de marketing y del de Aspel. |
| `ATTRIBUTION_FOLIO_ENABLED` | `false` | Interruptor maestro del folio en el navegador. Apagado, el JavaScript **no cambia ningún enlace ni agrega folio**; el registro de eventos sigue funcionando igual que antes. |
| `ATTRIBUTION_FOLIO_PLACEMENTS` | `*` | Dónde se aplica el folio: `*` (todos los botones) o una lista separada por comas de ubicaciones (placements), por ejemplo `flotante` o `flotante,footer_oficina`. Sirve para un piloto gradual. Los nombres válidos están en la columna "placement sugerido" de la sección 5. |
| `ATTRIBUTION_INVISIBLE_FOLIO` | `false` | Agrega al final del mensaje de WhatsApp una marca invisible con el folio (caracteres U+2060, U+200B, U+200C). **No activar hasta publicar el aviso de privacidad** (sección 7). |
| `ATTRIBUTION_ORIGIN_CHECK` | `true` | Verificación de `Origin`/`Referer` en `POST /track-conversion`. Con `true` solo se guardan eventos enviados desde el propio sitio; `false` la apaga sin redeploy si algo falla (junto con `config:clear`). |
| `ATTRIBUTION_ALLOWED_HOSTS` | vacío | Hosts extra permitidos, separados por comas y con comparación exacta (no por sufijo), además del host de `APP_URL` con y sin `www.`. |
| `ATTRIBUTION_TEST_WHATSAPP_NUMBER` | vacío | (Respaldo: el número guardado desde el panel, en Laboratorio de atribución > Ajustes de prueba, tiene prioridad sobre esta variable.) Número de WhatsApp que abren los botones **solo en modo prueba** (Laboratorio de atribución, sección 10): la línea de la cuenta de prueba de Kommo. Solo dígitos, de 10 a 15, con lada de país (por ejemplo `528112345678`). Vacío o inválido = el botón abre `https://wa.me/?text=…` **sin número** (eliges el chat a mano); nunca se usa el número real de producción. No afecta a visitantes normales. |

**Verificación de origen en `POST /track-conversion`.** El endpoint es público y sin CSRF, así que el middleware `tracking.origin` compara el host de la cabecera `Origin` (o, si no viene, del `Referer`) con el de `APP_URL` (con y sin `www.`) y con `ATTRIBUTION_ALLOWED_HOSTS`. Las páginas `/br/*` viven en el mismo dominio y quedan cubiertas. Solo en los entornos `local` y `testing` también se aceptan `localhost`, `127.0.0.1`, `::1`, `*.test` y `*.localhost` (y se ignora el puerto). Si no hay `Origin` ni `Referer`, o el host no coincide, responde **204 sin contenido y no guarda nada**, sin error visible: **si depurando ves un 204, significa "no guardado"** (revisa `APP_URL`, el `Origin` que manda el navegador y `ATTRIBUTION_ALLOWED_HOSTS`). Corre antes de los límites de frecuencia, así que el tráfico ajeno no gasta el cupo de los visitantes reales.

Importante: `ATTRIBUTION_FOLIO_ENABLED` se evalúa con el valor que tenga el `.env` cuando se carga la configuración; si en producción se usa `config:cache`, el cambio no surte efecto hasta limpiar o regenerar el caché (ver abajo).

### Cómo crear el token (desde el panel, recomendado)

1. Entra a **Marketing > Integración** (`/admin/marketing-tokens`) y pulsa **+ Crear Nuevo**.
2. Escribe un nombre (por ejemplo "n8n Google Ads") y en **Uso del token** elige **Atribución Google Ads (/api/attribution/lookup)**. El otro uso, *Email Marketing*, es el de siempre (`/api/marketing/*`) y es el valor por defecto.
3. Al guardar se muestra el valor completo, con el formato `<key_id>.<secret>`, **una sola vez**: cópialo con el botón de copiar. Después solo se ve el `key_id` y los últimos 4 caracteres del secreto; si lo pierdes, usa **Regenerar** (el valor anterior deja de servir al instante) o crea otro.
4. En n8n guarda el valor como credencial (tipo "Header Auth": nombre `Authorization`, valor `Bearer <key_id>.<secret>`).

Reglas:

- **Aislamiento por uso.** Un token de *Email Marketing* **no** abre `/api/attribution/*` y uno de *Atribución Google Ads* **no** abre `/api/marketing/*` (responden 401). Los tokens que ya existían quedan como *Email Marketing* y siguen funcionando igual.
- Se pueden tener varios tokens de atribución activos (uno por flujo o por ambiente) y revocarlos por separado en **Editar** (interruptor de estado); la tabla muestra el uso de cada uno y su último uso.
- La tarjeta "API de atribución (Google Ads)" de esa misma página y el panel de estado del laboratorio muestran "Token configurado" si hay al menos un token activo de atribución **o** un token válido en el `.env`. Solo cuentan tokens, nunca se muestran valores.
- Requiere la migración `2026_10_22_100000_add_scope_to_marketing_api_tokens_table.php` (`php artisan migrate`; agrega la columna `scope`, default `marketing`, sin tocar los tokens existentes). Si el código se despliega antes de migrar, el endpoint y el correo siguen funcionando como antes (el endpoint solo con el `.env`) y el panel rechaza crear tokens de atribución hasta migrar.

### Respaldo: token en el `.env`

Sigue funcionando como antes y no necesita migración. En cualquier terminal con PHP:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Imprime 64 caracteres hexadecimales. Cópialo al `.env` del servidor de producción:

```dotenv
ATTRIBUTION_API_TOKEN=pega-aqui-los-64-caracteres
```

Después, **en el servidor**:

```bash
php artisan config:clear      # imprescindible para que Laravel lea el .env nuevo
php artisan config:cache      # opcional, si en producción se usa el caché de configuración
```

Nota: con el caché de configuración activo, cambiar el `.env` **no tiene efecto** hasta ejecutar `config:clear` (o `config:cache` de nuevo). Es el motivo más común de "puse el token y sigue dando 503".

En n8n guarda el token como credencial (tipo "Header Auth": nombre `Authorization`, valor `Bearer <token>`); no lo pegues dentro de los nodos ni lo compartas por correo o chat.

### Rotación del token

**Token del panel:** en **Marketing > Integración** pulsa **Regenerar** en la fila del token, copia el valor nuevo (se muestra una sola vez) y actualiza la credencial de n8n. No hay periodo de gracia: el valor anterior deja de servir al instante. Para revocar, edita el token y apaga su estado (o bórralo); no hace falta `config:clear` porque no depende del `.env`.

**Token del `.env` (respaldo):**

1. Genera un token nuevo con el mismo comando.
2. Edita `ATTRIBUTION_API_TOKEN` en el `.env` de producción.
3. Ejecuta `php artisan config:clear` (y `config:cache` si lo usas).
4. Actualiza la credencial en n8n.

No hay periodo de gracia: el token viejo deja de servir en cuanto se limpia el caché, así que haz los pasos 3 y 4 seguidos. Para revocar de emergencia el token del `.env` basta con dejar la variable vacía; si no queda ningún token activo de atribución en el panel, el endpoint responde 503 a todos (si quedan, solo esos entran).

---

## 3. Referencia del endpoint `GET /api/attribution/lookup`

Solo lectura. Devuelve lo que el sitio sabe del contacto asociado a un folio (o a una ventana de tiempo). **Nunca** devuelve IP, user-agent, correo, teléfono, `href`, texto del enlace ni el referrer completo.

### Autenticación y límites

- Cabecera obligatoria: `Authorization: Bearer <key_id>.<secret>` (token creado en Marketing > Integración con uso "Atribución Google Ads") o, como respaldo, `Authorization: Bearer <ATTRIBUTION_API_TOKEN>`. Solo por cabecera; el token en la URL no se acepta (quedaría en logs e historial).
- Comparación en tiempo constante. Las respuestas del endpoint llevan `Cache-Control: no-store` (el 429 del limitador sale antes y no trae datos).
- Límite de 120 solicitudes por minuto por IP, que se evalúa **antes** de revisar el token (también frena a quien pruebe tokens a ciegas).

### Códigos de respuesta

| Código | Cuándo | Cuerpo |
|---|---|---|
| 200 | Modo folio: el folio existe. Modo hora: consulta válida, **haya o no eventos** (sin eventos devuelve `{"type":…,"from":…,"to":…,"count":0,"events":[]}`) | JSON con los campos de abajo |
| 401 | Token ausente, incorrecto, revocado o de otro uso (p. ej. uno de Email Marketing) | `{"status":"error","message":"Token inválido o revocado."}` |
| 404 | Solo modo folio: el folio no existe o no tiene formato válido. La ventana de hora **sin eventos NO es 404** (es 200 con `count: 0`) | `{"status":"error","message":"Folio no encontrado."}` |
| 422 | Parámetros inválidos: ni `folio` ni `type+from+to`; `from`/`to` sin zona horaria; ventana mayor a 15 minutos; `type` desconocido | JSON de error con el detalle |
| 429 | Más de 120 solicitudes por minuto desde la misma IP | Respuesta estándar de Laravel; reintenta con calma |
| 503 | No existe ninguna credencial posible: ni token activo de atribución en el panel, ni `ATTRIBUTION_API_TOKEN` válido (de 32 caracteres o más) en el `.env` (falla cerrado) | `{"status":"error","message":"Endpoint de atribución no configurado."}` |

### Modo 1: por folio (el normal)

```
GET /api/attribution/lookup?folio=MN7K4Q2
GET /api/attribution/lookup?folio=MN7K4Q2&message_at=2026-11-05T15:03:40Z
```

- `folio` se normaliza antes de buscar: mayúsculas y sin espacios, guiones ni caracteres invisibles (`mn-7k4q2` equivale a `MN7K4Q2`). Un folio con formato inválido responde 404 sin consultar la base. Acepta también un folio de cotización `COT-…` (busca en `meta.folio`; este conserva sus guiones).
- **Eventos de prueba:** el endpoint devuelve también las filas generadas desde el Laboratorio de atribución (sección 10), marcadas con `is_test: true`. Es a propósito, para poder probar n8n de punta a punta; **n8n debe ignorarlas** (sección 4.3).
- `message_at` (opcional): hora a la que llegó el mensaje al CRM, ISO 8601 con zona (por ejemplo `2026-11-05T15:03:40Z` o `2026-11-05T09:03:40-06:00`). Si se manda, la respuesta incluye `message_to_event_seconds`.
- **Cotización formal:** el navegador genera un folio MN al iniciar la cotización (`quote_start`) y lo manda también en `quote_submit`, junto con `meta.folio = COT-…`. Buscar por ese MN o por el `COT-` devuelve **la misma fila `quote_submit`**: entre las filas del folio (`quote_start`, `quote_submit`, `quote_dismiss`) el endpoint prefiere la de tipo `quote_submit`, y solo si no hay ninguna usa la regla de abajo. Varias filas de la misma sesión no cuentan como ambigüedad.
- Si hay varias filas con ese folio: sin `message_at` devuelve la **más reciente**; con `message_at` devuelve la **más cercana** a esa hora. En ambos casos marca `ambiguous` y `candidates` (ver abajo). Un `message_at` sin zona horaria responde 422.

### Modo 2: por hora (llamadas y respaldo)

```
GET /api/attribution/lookup?type=phone_click&from=2026-11-05T15:00:00-06:00&to=2026-11-05T15:10:00-06:00
```

- `type`: tipo canónico: `whatsapp_click`, `phone_click` o `email_click`.
- `from` y `to`: **con zona horaria obligatoria** (`Z` o `±hh:mm`). Una fecha sin zona responde 422. La ventana `to - from` no puede pasar de **15 minutos**.
- Respuesta: `{ "type": "phone_click", "from": "…", "to": "…", "count": 2, "events": [ … ] }`. `events` trae como máximo 20 eventos de ese tipo dentro de la ventana, ordenados por cercanía a `to` (la hora de la llamada). Cada evento trae los mismos campos del modo folio más `match_type: "time_window"` y `seconds_before_call` (segundos entre el clic y `to`; positivo = el clic fue antes de la llamada). Sin eventos en la ventana **no es un error**: responde `200` con `{"type":"phone_click","from":"…","to":"…","count":0,"events":[]}`. En n8n evalúa `count`, no el código de respuesta. **Por defecto la ventana solo devuelve eventos reales** (`is_test = 0`): una fila del laboratorio cercana a una llamada real no debe desplazarla del tope de 20. Parámetro opcional `include_test`: `only` = solo pruebas (lo usa el laboratorio) y `1` = reales y de prueba; el modo folio siempre devuelve la fila del folio, sea real o de prueba (con `is_test`).
- Es un cruce **probabilístico**: dos personas pudieron tocar "Llamar" en la misma ventana. Úsalo para análisis, y solo considera una coincidencia cuando `count` sea exactamente 1; no lo subas como conversión principal.

### Respuesta (modo folio), campo por campo

Ejemplo:

```json
{
  "folio": "MN7K4Q2",
  "event_id": "e-lq3x9a-7f2k1m0p",
  "session_id": "5b1c2d3e-4f50-4a6b-8c7d-9e0f1a2b3c4d",
  "is_test": false,
  "type": "whatsapp_click",
  "placement": "flotante",
  "event_at": "2026-11-05T15:02:41Z",
  "captured_at": "2026-11-05T14:58:03Z",
  "received_at": "2026-11-05T15:02:42Z",
  "gclid": "EAIaIQobChMI_EJEMPLO_0123456789abcdef",
  "gbraid": null,
  "wbraid": null,
  "fbclid": null,
  "utm_source": "google",
  "utm_medium": "cpc",
  "utm_campaign": "Avatra_Pmax",
  "utm_term": null,
  "utm_content": null,
  "landing_page": "/producto/controlador-ejemplo",
  "page_url": "https://tu-dominio.com/producto/controlador-ejemplo",
  "referrer_host": "www.google.com",
  "product": { "sku": "SKU-EJEMPLO-001", "name": "Controlador de ejemplo" },
  "attribution_source": "event",
  "has_google_click_id": true,
  "google_click_id_type": "gclid",
  "click_id_at": "2026-11-05T14:58:03Z",
  "click_age_days": 0,
  "click_channel": "google",
  "touch_conflict": false,
  "session_has_fresh_click": true,
  "ambiguous": false,
  "candidates": 1,
  "message_to_event_seconds": 59
}
```

| Campo | Tipo | Significado |
|---|---|---|
| `folio` | texto | Folio consultado (`MN…` o `COT-…`), ya normalizado. |
| `event_id` | texto | Identificador único del evento (el navegador lo genera; reenviarlo no duplica la fila). |
| `session_id` | texto o null | Sesión del visitante (cambia tras 30 minutos de inactividad). Es null en las 4,332 filas históricas anteriores a la captura de sesión. |
| `is_test` | booleano | `true` si el evento se generó desde el **modo prueba** del Laboratorio de atribución (sección 10); `false` en todo el tráfico real y en las filas históricas. Aparece en cada evento, tanto en modo folio como en modo hora. **n8n debe descartar los `is_test = true` antes de cualquier subida a Google Ads.** La decide el servidor al guardar (nunca el navegador por sí solo): cookie de modo prueba válida **más** `test: 1` en el payload, o un `gclid` que empieza exactamente con `TEST_GCLID_`. |
| `type` | texto | Tipo canónico: `whatsapp_click`, `phone_click`, `email_click`, `quote_submit`… El tipo histórico del botón (`whatsapp_flotante`, etc.) queda en la base como `meta.legacy_type`. |
| `placement` | texto o null | Dónde estaba el botón (`flotante`, `footer_oficina`, `producto`…). |
| `event_at` | fecha UTC con `Z` | Hora del clic según el navegador, recortada a `[recepción − 7 días, recepción]`; si el navegador no la mandó, es la hora de recepción. |
| `captured_at` | fecha UTC | Hora del **último toque de atribución** del visitante (cuándo entró con ese anuncio/UTM). **No es la hora del clic**; puede ser horas o días anterior. |
| `received_at` | fecha UTC | Hora en que el servidor recibió el evento. |
| `gclid`, `gbraid`, `wbraid`, `fbclid` | texto o null | Identificadores de clic **ya validados por formato**: un valor con formato inválido (prefijo `gtm`, solo dígitos, demasiado corto) sale como null. `gclid` tiene de 20 a 255 caracteres URL-safe; `gbraid`/`wbraid`, de 10 a 255. |
| `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content` | texto o null | UTM del toque de atribución. |
| `landing_page` | texto o null | Ruta de la primera página que vio el visitante en la sesión. |
| `page_url` | texto o null | Página donde ocurrió el clic, **sin query ni fragmento**. |
| `referrer_host` | texto o null | Solo el dominio de procedencia (`www.google.com`), nunca la URL completa. |
| `product` | objeto | `{sku, name}` del producto que se veía al hacer clic; ambos pueden ser null. |
| `attribution_source` | `event` / `session` / `none` | De dónde salió la atribución: `event` = el propio evento trae click id o UTM; `session` = el evento no trae, pero una fila anterior de la misma sesión sí (solo se mira entre filas del mismo tipo de tráfico: una fila real nunca hereda atribución de una fila de prueba de la misma sesión, ni al revés); `none` = sin atribución (directo, orgánico, o dato perdido). |
| `has_google_click_id` | booleano | Hay `gclid`, `gbraid` o `wbraid` válido. Es la condición mínima para subir a Google Ads. |
| `google_click_id_type` | `gclid` / `gbraid` / `wbraid` / null | Cuál es. Se sube **uno solo** a Google Ads. |
| `click_id_at` | fecha UTC o null | Momento en que se capturó ese click id. |
| `click_age_days` | entero o null | Días desde `click_id_at` hasta el evento, **redondeados hacia arriba** (1 minuto = 1 día). Es `null` si `click_id_at` es más de 5 minutos posterior al evento (reloj del navegador desajustado o forjado). |
| `click_channel` | `google` / `meta` / `none` o null | Canal del click id más reciente del visitante (null si el navegador no lo envió, p. ej. en filas históricas). |
| `touch_conflict` | booleano | `true` si después del click id de Google hubo un toque de otro canal (un UTM que no es de Google o un `fbclid`). La atribución es "pegajosa" (hasta 90 días), así que este campo avisa que el último toque pudo no ser el anuncio. |
| `session_has_fresh_click` | booleano | `true` solo si en la **misma sesión** hubo antes una visita (`page_visit`) con ese mismo `gclid` **y** el click id entró en esta sesión (`click_id_at` no es anterior a 5 minutos antes de la primera fila de la sesión). Un visitante que vuelve a los 60 días trae el `gclid` pegajoso en su visita, pero sale `false`. `false` también si no hay `gclid`, falta `click_id_at` o el evento no trae sesión. |
| `ambiguous` | booleano | `true` si el folio aparece en filas de **sesiones distintas** (colisión). Dos clics de la misma sesión con el mismo folio no cuentan como ambigüedad. |
| `candidates` | entero | Cuántas **sesiones distintas** comparten ese folio (una fila sin sesión cuenta como una sesión propia). `1` = sin ambigüedad. |
| `message_at` | fecha UTC o null | Eco de la hora del mensaje que mandaste (null si no la mandaste). |
| `message_to_event_seconds` | entero o null | Solo si mandaste `message_at`: segundos entre el clic y el mensaje; **positivo = el mensaje llegó después del clic**. Sirve para detectar mensajes viejos reenviados. |

### Ejemplos con curl

```bash
TOKEN="pega-aqui-el-token"
BASE="https://tu-dominio.com"   # URL de ejemplo: usa el dominio real del sitio

# Por folio
curl -sS -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" \
  "$BASE/api/attribution/lookup?folio=MN7K4Q2"

# Por folio, con la hora del mensaje (UTC)
curl -sS -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" \
  "$BASE/api/attribution/lookup?folio=MN7K4Q2&message_at=2026-11-05T15:03:40Z"

# Folio de cotización formal
curl -sS -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" \
  "$BASE/api/attribution/lookup?folio=COT-000123"

# Cruce por hora (llamada recibida a las 15:10 hora de Monterrey; ventana de 10 minutos).
# --data-urlencode es necesario: el "+" de una zona horaria rompería la URL.
curl -sS -G -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" \
  --data-urlencode "type=phone_click" \
  --data-urlencode "from=2026-11-05T15:00:00-06:00" \
  --data-urlencode "to=2026-11-05T15:10:00-06:00" \
  "$BASE/api/attribution/lookup"

# Comprobar el token y el cierre por defecto (debe dar 401 sin token válido)
curl -i -H "Authorization: Bearer token-incorrecto" "$BASE/api/attribution/lookup?folio=MN7K4Q2"
```

Prueba local de punta a punta (con `ATTRIBUTION_FOLIO_ENABLED=true` en tu `.env` local): abre
`http://localhost/?gclid=TEST_GCLID_0123456789ABCDEF&utm_source=google&utm_medium=cpc&utm_campaign=prueba`,
toca el botón de WhatsApp, copia el folio del mensaje y consulta con curl. El `gclid` de prueba debe tener **20 caracteres o más**; uno más corto (`TEST123`) se descarta por inválido y la consulta saldrá con `has_google_click_id: false`. Un `gclid` que empieza exactamente con `TEST_GCLID_` queda marcado como `is_test` por el servidor aunque no haya modo prueba. Las consultas SQL de la sección 6 excluyen las filas `is_test = 1` (y, por compatibilidad con pruebas antiguas, `utm_campaign=prueba` y `gclid` que empieza con `test`).

La forma recomendada de probar es el **Laboratorio de atribución** del admin (sección 10): activa el modo prueba solo en tu navegador, apaga GTM/GA4/Ads/Pixel/Sheets en él y marca las filas como `is_test`, sin afectar a visitantes reales.

---

## 4. Guía para n8n

### 4.1 Extraer el folio del mensaje

El cliente puede editar el texto, y Kommo puede traer el mensaje con etiquetas en cualquier idioma ("Folio", "Código", "Code"). **No dependas de la etiqueta**: busca el patrón.

Regex base (exige mayúsculas, así que conviértelo antes):

```
\bMN[2-9BCDFGHJKMNPQRSTVWXYZ]{5}\b
```

Reglas para el nodo de extracción:

1. **Forzar mayúsculas** antes de buscar (el cliente puede escribir `mn7k4q2`).
2. **Ignorar** los caracteres invisibles U+2060 (word joiner), U+200B y U+200C (y de paso U+200D y U+FEFF): quítalos antes de aplicar la regex.
3. **Tolerar** `MN-7K4Q2`, `MN 7K4Q2` o `MN.7K4Q2`: permite un separador opcional entre `MN` y los 5 caracteres y reconstruye el folio sin él.
4. **Si hay más de un folio** en el mensaje, usa el último (el cliente pudo pegar uno anterior) y registra el hecho.
5. Un folio con letras fuera del alfabeto (A, E, I, L, O, U) no es nuestro: la regex lo descarta, no lo "arregles".

Nodo Code de n8n (JavaScript), como punto de partida. Ajusta `texto` al campo donde Kommo entrega el mensaje:

```javascript
const texto = String($json.texto ?? '');

// 1) Quitar invisibles y normalizar, 2) mayúsculas
const limpio = texto
  .normalize('NFKC')
  .replace(/[⁠​‌‍﻿]/g, '')
  .toUpperCase();

// 3) MN + separador opcional + 5 caracteres del alfabeto
const re = /\bMN[\s\-_.]?([2-9BCDFGHJKMNPQRSTVWXYZ]{5})\b/g;
const folios = [...limpio.matchAll(re)].map(m => 'MN' + m[1]);

// Respaldo: si el cliente borró la parte visible pero quedó el folio invisible.
// Cada carácter del folio va como 8 bits entre dos U+2060 (U+200B = 0, U+200C = 1).
let invisible = null;
const mi = texto.match(/⁠([​‌]+)⁠/);
if (mi) {
  const bits = mi[1];
  let s = '';
  for (let i = 0; i + 8 <= bits.length; i += 8) {
    let code = 0;
    for (let b = 0; b < 8; b++) code = (code << 1) | (bits[i + b] === '‌' ? 1 : 0);
    s += String.fromCharCode(code);
  }
  if (/^MN[2-9BCDFGHJKMNPQRSTVWXYZ]{5}$/.test(s)) invisible = s;
}

return [{ json: {
  ...$json,
  folio: folios.length ? folios[folios.length - 1] : invisible,
  folio_fuente: folios.length ? 'visible' : (invisible ? 'invisible' : null),
  folios_en_mensaje: folios.length,
} }];
```

El folio invisible solo existe si `ATTRIBUTION_INVISIBLE_FOLIO` está encendido. Con el flag apagado (el valor por defecto) `invisible` siempre será `null`.

### 4.2 Consultar el endpoint

Nodo HTTP Request: método GET, URL `https://tu-dominio.com/api/attribution/lookup`, parámetros `folio` (y `message_at` con la hora del mensaje en ISO 8601 con zona), autenticación "Header Auth" con la credencial del token. Configura el nodo para que un 404 **no detenga** el flujo (es un resultado normal: folio inventado o que no llegó a registrarse) y trátalo como "sin atribución". **Nunca subas un resultado con `ambiguous=true`, y manda siempre `message_at`** (con él el endpoint elige el evento más cercano al mensaje y calcula `message_to_event_seconds`). En el modo por hora, un `200` con `count: 0` también es "sin coincidencia", no un error.

**Inmediatamente después de este nodo, y antes de cualquier rama que suba una conversión a Google Ads, agrega un nodo IF sobre `is_test`**: si `is_test = true`, el flujo termina (o registra "prueba ignorada") sin subir nada. Las filas de prueba salen en el endpoint a propósito, para que puedas verificar que n8n recibe y procesa el folio; lo que **no** deben hacer es llegar a Google Ads. En el modo por hora aplica el mismo IF a cada elemento de `events`.

### 4.3 Política recomendada para subir a Google Ads

Subir **solo si se cumplen todas**:

| Condición | Por qué |
|---|---|
| `is_test = false` | Con `true` la fila salió del modo prueba del laboratorio (sección 10). Nunca se sube a Google Ads; se ignora con el nodo IF de 4.2. |
| `has_google_click_id = true` | Sin `gclid`, `gbraid` o `wbraid` Google no puede atribuir. |
| `click_age_days <= 30` | La cookie dura 90 días, pero un clic de hace 80 días rara vez explica el contacto de hoy. |
| `touch_conflict = false` | Si después del anuncio el visitante volvió por otro canal, no se le acredita al anuncio. |
| `ambiguous = false` | Folio repetido en sesiones distintas: no sabemos de quién es. |
| `message_to_event_seconds` razonable (por ejemplo entre 0 y 24 horas) | Un mensaje con un folio de hace una semana suele ser un reenvío o un cliente que vuelve a escribir. Criterio sugerido, ajústalo con datos. |

Otras reglas:

- **Deduplicar por lead de Kommo**: si el mismo lead escribe tres veces con folios distintos, se sube **una** conversión (la del primer contacto con atribución válida).
- Usar el **folio** (o el id del lead de Kommo) como `order_id` / identificador de transacción al subir a Google Ads; así un reintento no cuenta doble.
- Subir uno solo de `gclid`, `gbraid` o `wbraid`, el que indique `google_click_id_type`.
- La hora de la conversión que se sube es la del mensaje en Kommo, en el formato y zona que exige la cuenta de Google Ads (revisa la documentación de importación de conversiones sin conexión de Google Ads para el formato exacto).
- **No subir llamadas por cruce de hora como conversión principal.** Pueden guardarse como conversión secundaria o solo como dato de análisis.
- Con el modo por hora, subir solo si hay **una única coincidencia** en la ventana (si hay varias, es ambiguo: no subir).
- Los eventos con `attribution_source = none` o `click_channel = meta` no se suben a Google Ads.
- Los eventos con `is_test = true` **nunca** se suben, ni se cuentan como lead atribuido, ni deduplican a un lead real: se ignoran antes de aplicar cualquier otra regla.
- Guardar en una tabla de n8n (o en Kommo, en un campo del lead) lo que se hizo con cada folio: subido, descartado y motivo. Sin esto no se puede medir ni depurar.

### 4.4 Qué medir en n8n

El sitio no puede saber cuántos clientes de Kommo llegaron con folio; eso solo se ve en n8n. Cada mes:

| Métrica | Fórmula |
|---|---|
| Cobertura de folio | leads de Kommo cuyo mensaje trae folio ÷ leads de Kommo que vinieron del sitio web |
| Atribuibles | de los anteriores, los que cumplen la política de 4.3 (click id válido, ≤30 días, sin conflicto, sin ambigüedad) ÷ leads con folio |
| Descartados por motivo | conteo por: sin click id, click viejo, `touch_conflict`, `ambiguous`, folio no encontrado (404). Las pruebas (`is_test = true`) se registran aparte y no entran en estos conteos |
| Subidos a Google Ads | número de conversiones realmente cargadas, para comparar con lo que muestra la interfaz de Google Ads |

La meta del proyecto es atribuir al menos el 90% de los clientes que vienen de anuncios sin atribuciones falsas. Ese número **depende de que el cliente conserve el texto con el folio** (puede borrarlo) y de las llamadas, que no llevan folio. Por eso se mide aquí y no en el sitio.

---

## 5. Enlaces de contacto del sitio: tabla completa con archivo:línea

Líneas verificadas leyendo el código el 18 de octubre de 2026. Las líneas pueden moverse con cualquier edición; para regenerar la lista:

```bash
grep -rnE "contact\.whatsapp\.url|contact\.whatsapp\.number|wa\.link|wa\.me|api\.whatsapp\.com|tel:|mailto:|data-action=.solicitar-cotizacion|btn-cotizar|cotizacion\.formulario" \
  resources/views public/js public/frontend/js --include=*.php --include=*.js | grep -v "\.min\.js"
```

### Cómo leer la tabla

- Prefijos: `V/` = `resources/views/frontend/`; `JS/` = `public/js/`; `FJS/` = `public/frontend/js/`. El resto de las rutas se escribe completa.
- **WA url** = `config('contact.whatsapp.url')`, que hoy vale `https://wa.link/f28njw` (`config/contact.php`). **BR url** = `$br['whatsapp']` (`config/brasil.php:20`, variable `BR_WHATSAPP_URL`, mismo `wa.link` por defecto).
- **TC** = el enlace tiene la clase `track-conversion`. Ese enlace lo envía el **listener de master** (`resources/views/frontend/layouts/master.blade.php:1084-1119`, selector `a.track-conversion`). Hoy envía `type = data-type` sin `meta`.
- **mdn-events** = `FJS/mdn-events.js` lo registra porque el enlace **no** tiene la clase `track-conversion`: WhatsApp (`wa.link`, `wa.me`, `api.whatsapp.com`…) como `whatsapp_click` (línea 159), `tel:` como `phone_click` (160), enlaces a `/cotizacion/formulario` como `quote_start` formal (161), `.btn-cotizar` como `quote_start` invitado (162) y botones `data-action="solicitar-cotizacion"` como `quote_start` (134-137). Los `mailto:` sin clase **no** los rastrea nadie.
- **nadie** = ningún script lo registra.
- Las páginas que usan `@extends('frontend.layouts.master')` (casi todas) cargan los dos mecanismos. Las páginas **autónomas** `catalogo`, `categorias` y `categoria-productos` son HTML propio: cargan `attribution-capture` y `mdn-events`, pero **no** el listener de master. Las páginas de acceso (`layouts/guest`) no cargan ninguno de los dos.
- "Placement sugerido" es el nombre que conviene usar en `ATTRIBUTION_FOLIO_PLACEMENTS` y en `meta.placement`: el `data-type` sin su prefijo (`whatsapp_`, `telefono_`, `correo_`); `whatsapp` o `telefono` sueltos pasan a `producto`; `whatsapp-flotante` pasa a `flotante`; los `whatsapp_br_…` pasan a `br_…`. Para los enlaces sin `data-type` es una propuesta.

### 5.1 Layout global (en casi todas las páginas)

| archivo:línea | tipo | cómo se construye | clase + data-type | quién lo rastrea hoy | placement sugerido |
|---|---|---|---|---|---|
| `V/layouts/whastapp-chat.blade.php:31` (y `data-url` en :36) | WhatsApp, botón flotante | `href` = WA url | TC `whatsapp-flotante` | master | `flotante` |
| `V/layouts/top-bar.blade.php:20` | WhatsApp, "Soporte técnico" | WA url | TC `whatsapp_topbar_soporte` | master | `topbar_soporte` |
| `V/layouts/header.blade.php:107` | WhatsApp, "Solicitar cotización" | WA url | TC `whatsapp_menu_cotizacion` | master | `menu_cotizacion` |
| `V/layouts/menu.blade.php:64` | WhatsApp, "Solicitar cotización" | WA url | TC `whatsapp_menu_cotizacion` (mismo que header:107) | master | `menu_cotizacion` |
| `V/layouts/partials/nav-social-links.blade.php:2` (incluido por `nav-megamenu:121`, que incluye `menu:74`) | WhatsApp, ícono social | WA url | — | mdn-events | `nav_social` |
| `V/layouts/footer.blade.php:20` | WhatsApp, ícono social | WA url | TC `whatsapp_footer_social` | master | `footer_social` |
| `V/layouts/footer.blade.php:82` | WhatsApp, oficina | WA url | TC `whatsapp_footer_oficina` | master | `footer_oficina` |
| `V/layouts/footer.blade.php:88` | Teléfono, principal | `'tel:'.config('contact.phone.main.number')` | TC `telefono_footer_oficina` | master | `footer_oficina` |
| `V/layouts/footer.blade.php:94` | Correo, general | `'mailto:'.config('contact.email.general')` | TC `correo_footer_contacto` | master | `footer_contacto` |
| `V/layouts/footer.blade.php:100` | Correo, ventas | `'mailto:'.config('contact.email.sales')` | TC `correo_footer_ventas` | master | `footer_ventas` |
| `V/layouts/footer.blade.php:111` | Teléfono, asesorías | `'tel:'.config('contact.phone.advisory.number')` | TC `telefono_footer_asesorias` | master | `footer_asesorias` |
| `V/layouts/footer.blade.php:117` | Correo, product manager | `'mailto:'.config('contact.email.product_manager')` | TC `correo_footer_product_manager` | master | `footer_product_manager` |

### 5.2 Inicio, listados y productos (tarjetas)

| archivo:línea | tipo | cómo se construye | clase + data-type | quién lo rastrea hoy | placement sugerido |
|---|---|---|---|---|---|
| `V/home/sections/banner-slider.blade.php:36` | WhatsApp, "Cotización con ingeniero" | WA url | — | mdn-events | `home_banner` |
| `V/home/sections/category-product-slider-one.blade.php:191` | WhatsApp, botón de tarjeta | WA url | — (`btn-whatsapp`) | mdn-events | `home_slider_1` |
| `V/home/sections/category-product-slider-two.blade.php:190` | WhatsApp, botón de tarjeta | WA url | — (`btn-whatsapp`) | mdn-events | `home_slider_2` |
| `V/home/sections/category-product-slider-three.blade.php:191` | WhatsApp, botón de tarjeta | WA url | — (`btn-whatsapp`) | mdn-events | `home_slider_3` |
| `V/pages/product.blade.php:751` | WhatsApp, botón de tarjeta del listado | WA url | — (`btn-whatsapp`) | mdn-events | `listado_producto` |
| `V/home/sections/category-product-slider-one.blade.php:187`, `…two:186`, `…three:187`, `V/pages/product.blade.php:747` | "Cotizar" (va a `/contact`, no es WhatsApp) | `route('contact')` | — (`btn-cotizar`) | mdn-events como `quote_start` invitado | no aplica |

Nota: `category-product-slider-one` se incluye dos veces en la home (`V/home/home.blade.php:13` y `V/home/sections/categories-why-us.blade.php:379`), por lo que sus botones pueden aparecer duplicados en la página. No afecta al folio (cada clic genera el suyo), pero conviene saberlo al leer conteos.

### 5.3 Detalle de producto (`CLAUDE.md`: no editar esta vista)

| archivo:línea | tipo | cómo se construye | clase + data-type | quién lo rastrea hoy | placement sugerido |
|---|---|---|---|---|---|
| `V/pages/product-detail.blade.php:660` | Cotización formal | `route('cotizacion.formulario')`; `updateCotizarUrl()` (línea 1136-1145) reescribe el `href` con `producto_id`, cantidad y combinación | — (`id="btn-cotizar"`) | mdn-events como `quote_start` formal | no aplica (el folio es `COT-…`) |
| `V/pages/product-detail.blade.php:667` | WhatsApp, "Hablar con ingeniero" | WA url; `onclick` inline empuja a `dataLayer` (`whatsapp_conversion`) | TC `whatsapp` | master | `producto` |
| `V/pages/product-detail.blade.php:675` | Teléfono, "Atención inmediata" | `tel:` + `config('contact.phone.main.number')`; `onclick` inline a `dataLayer` (`Telefono_Conversion`) | TC `telefono` | master | `producto` |
| `V/pages/product-detail.blade.php:724` | WhatsApp, cliente sin sesión | WA url | TC `whatsapp_producto_auth` | master | `producto_auth` |
| `V/pages/product-detail.blade.php:728` | Teléfono, cliente sin sesión | `tel:` + número principal | TC `telefono_producto_auth` | master | `producto_auth` |
| `V/pages/product-detail.blade.php:749`, `:818`, `:821` | Enlaces a `/contact` ("Contacto directo", B2B) | `route('contact')` | — | nadie | no aplica |
| `V/pages/product-detail.blade.php:753` | WhatsApp, "Cotizar ahora" (producto sin precio) | WA url | TC `whatsapp` | master | `producto` |
| `V/pages/product-detail.blade.php:758` | Teléfono (producto sin precio) | `tel:` + número principal | TC `telefono` | master | `producto` |

### 5.4 Contacto, nosotros, carrito y cotización

| archivo:línea | tipo | cómo se construye | clase + data-type | quién lo rastrea hoy | placement sugerido |
|---|---|---|---|---|---|
| `V/pages/contact.blade.php:452` | WhatsApp | `https://wa.me/{{ config('contact.whatsapp.number') }}` (sin `text=`) | TC `whatsapp_contacto_business` | master | `contacto_business` |
| `V/pages/contact.blade.php:461` | Teléfono | `tel:` + número principal | TC `telefono_contacto_soporte` | master | `contacto_soporte` |
| `V/pages/contact.blade.php:477` | Correo | `mailto:` + correo de ventas | TC `correo_contacto_ventas1` | master | `contacto_ventas1` |
| `V/pages/about.blade.php:955` | Correo | `mailto:` + correo general | — | nadie | `about_correo` |
| `V/pages/cart-details.blade.php:490` | Cotización formal | `route('cotizacion.formulario')` | — | mdn-events como `quote_start` formal | no aplica |
| `V/cotizaciones/confirmacion.blade.php:133` | Cotización formal ("otra cotización") | `route('cotizacion.formulario', ['nuevo' => 1])` | — | mdn-events como `quote_start` formal | no aplica |

El formulario de `/contact` (`V/pages/contact.blade.php:379`, `id="contact-form"`) no tiene `action`: no envía nada. Los formularios que usan `formsubmit.co` (`associate_page`, `calibracion-puesta`, `medicion`, `quotes`) no se rastrean ni llevan folio. La cotización formal con sesión (`/cotizacion/*`) registra `quote_submit` desde `mdn-events.js` (`hookQuotePage`).

### 5.5 Páginas de servicios

Las ocho páginas que usan `service-layout` (`calibracion-ema`, `calibracion-quemadores`, `controles`, `medidores-flujo`, `plc`, `reparacion-videoregistradores`, `sistemas`, `videoregistradores`) y las tres que usan `category-detail-layout` (`calibraciones`, `instalacion-configuracion`, `reparacion-equipos`) comparten estos enlaces. **Ambos partials tienen cambios sin commitear en el árbol de trabajo**, así que verifica las líneas antes de apoyarte en ellas.

| archivo:línea | tipo | cómo se construye | clase + data-type | quién lo rastrea hoy | placement sugerido |
|---|---|---|---|---|---|
| `V/pages/partials/service-layout.blade.php:72`, `:208`, `:236` | Teléfono | `tel:{{ $phone }}`, `$phone = config('contact.phone.main.number')` (línea 37) | TC `telefono_servicios` | master | `servicios` |
| `V/pages/partials/service-layout.blade.php:76`, `:209`, `:232` | WhatsApp | `$whatsapp` = WA url (línea 39) | TC `whatsapp_servicios` | master | `servicios` |
| `V/pages/partials/category-detail-layout.blade.php:80`, `:119`, `:219` | Teléfono | `tel:{{ $phone }}` (línea 30) | TC `telefono_servicios` | master | `servicios` |
| `V/pages/partials/category-detail-layout.blade.php:79`, `:120`, `:218` | WhatsApp | `$whatsapp` = WA url (línea 32) | TC `whatsapp_servicios` | master | `servicios` |

Hoy los 12 enlaces de cada partial comparten el mismo `data-type`; si se quiere distinguir "hero" de "cierre de página", hay que agregar `data-placement` a cada uno (no se hizo).

### 5.6 Páginas autónomas de catálogo (sin listener de master)

| archivo:línea | tipo | cómo se construye | clase + data-type | quién lo rastrea hoy | placement sugerido |
|---|---|---|---|---|---|
| `V/pages/catalogo.blade.php:50` | WhatsApp, "Contactar asesor" | WA url; `data-action="contactar-asesor"` | — | mdn-events (`whatsapp_click`) | `catalogo_asesor` |
| `V/pages/categorias.blade.php:30` | Teléfono (barra de navegación) | `tel:` + número principal | — | mdn-events (`phone_click`) | `categorias_nav` |
| `V/pages/categorias.blade.php:63` | Botón "Solicitar cotización" (sin `href`) | `JS/categorias.js:126-135`: `window.open(CATALOGO_DATA.whatsappUrl, '_blank', 'noopener')`; `whatsappUrl` sale de `categorias.blade.php:74` | `data-action="solicitar-cotizacion"` | mdn-events como `quote_start` (aunque abre WhatsApp) | `categorias_cotizar` |
| `V/pages/categoria-productos.blade.php:31` | Teléfono (barra de navegación) | `tel:` + número principal | — | mdn-events (`phone_click`) | `catprod_nav` |
| `V/pages/categoria-productos.blade.php:88` | Botón "Solicitar cotización" (móvil) | `JS/categoria-productos.js:179-181` → `abrirWhatsApp()` (:122-127); `whatsappUrl` en `categoria-productos.blade.php:101` | `data-action="solicitar-cotizacion"` | mdn-events como `quote_start` | `catprod_cotizar` |
| `JS/categoria-productos.js:99` (renderiza) y `:171-176` (escucha, con `preventDefault`) | Botón de cada tarjeta de producto | mismo `abrirWhatsApp()` | `data-action="solicitar-cotizacion"` | mdn-events como `quote_start` | `catprod_tarjeta` |

### 5.7 Brasil (`/br/*`, extienden master: tienen listener y mdn-events)

| archivo:línea | tipo | cómo se construye | clase + data-type | quién lo rastrea hoy | placement sugerido |
|---|---|---|---|---|---|
| `V/layouts/br/header.blade.php:90` | WhatsApp | BR url | TC `whatsapp_br_header` | master | `br_header` |
| `V/layouts/br/header.blade.php:54` | Correo | `mailto:{{ $br['email'] }}` | — | nadie | `br_header_correo` |
| `V/layouts/br/footer.blade.php:60` | WhatsApp | BR url | TC `whatsapp_br_footer` | master | `br_footer` |
| `V/layouts/br/footer.blade.php:56` | Correo | `mailto:{{ $br['email'] }}` | — | nadie | `br_footer_correo` |
| `V/layouts/br/footer.blade.php:57` | Teléfono | `$br['phone_href']` (`tel:+528124738768`) | — | mdn-events (`phone_click`) | `br_footer_tel` |
| `V/layouts/br/relacionados-calibracoes.blade.php:64-66` | WhatsApp (tarjetas de calibración) | BR url | TC `whatsapp_br_calibracao_{tag}` | master | `br_calibracao_{tag}` |
| `V/pages/br/honeywell-dc1010.blade.php:129`, `:265`, `:330`, `:347` | WhatsApp | BR url | TC `whatsapp_br_dc1010_hero` / `_ficha` / `_orcamento` / `_sticky` | master | `br_dc1010_hero`… |
| `V/pages/br/honeywell-dc1040.blade.php:129`, `:266`, `:331`, `:348` | WhatsApp | BR url | TC `whatsapp_br_dc1040_hero` / `_ficha` / `_orcamento` / `_sticky` | master | `br_dc1040_hero`… |
| `V/pages/br/honeywell-dc1200.blade.php:129`, `:266`, `:331`, `:348` | WhatsApp | BR url | TC `whatsapp_br_dc1200_hero` / `_ficha` / `_orcamento` / `_sticky` | master | `br_dc1200_hero`… |
| `V/pages/br/honeywell-dc2800.blade.php:129`, `:263`, `:328`, `:345` | WhatsApp | BR url | TC `whatsapp_br_dc2800_hero` / `_ficha` / `_orcamento` / `_sticky` | master | `br_dc2800_hero`… |
| `V/pages/br/mcdonnell-miller.blade.php:127`, `:267`, `:332`, `:349` | WhatsApp | BR url | TC `whatsapp_br_mm150s_hero` / `_ficha` / `_orcamento` / `_sticky` | master | `br_mm150s_hero`… |
| `V/pages/br/mcdonnell-produto.blade.php:121`, `:218`, `:265`, `:281` | WhatsApp | BR url | TC `whatsapp_br_mm_{sku}_hero` / `_ficha` / `_orcamento` / `_sticky` | master | `br_mm_{sku}_hero`… |
| `V/pages/br/honeywell-dc1010.blade.php:337`, `…dc1040:338`, `…dc1200:338`, `…dc2800:335`, `…mcdonnell-miller:339`, `…mcdonnell-produto:272` | Correo y teléfono (texto de contacto) | `mailto:{{ $br['email'] }}` y `{{ $br['phone_href'] }}` | — | correo: nadie; teléfono: mdn-events (`phone_click`) | `br_contacto` |

### 5.8 Empleos, acceso y páginas legales

| archivo:línea | tipo | cómo se construye | clase + data-type | quién lo rastrea hoy | placement sugerido |
|---|---|---|---|---|---|
| `V/pages/empleos.blade.php:224`, `:309`, `:332`, `:366`, `:370`, `:460`, `:500` | WhatsApp (postulación y dudas) | `https://wa.me/{numero}?text=…` armado en `app/Http/Controllers/Frontend/HomeController.php:122-150` | `data-mdn-event="job_apply"` o `"job_question"` | mdn-events (líneas 150-157, como evento propio, **no** como `whatsapp_click`) | **no se reescribe ni lleva folio** (no es una venta) |
| `V/partials/auth-header.blade.php:4` | Teléfono | `'tel:'.config('contact.phone.main.number')` | — | nadie (el layout `guest` no carga mdn-events) | no aplica |
| `V/partials/auth-header.blade.php:8` | Correo | `'mailto:'.config('contact.email.sales')` | — | nadie | no aplica |
| `V/pages/aviso-privacidad.blade.php:89`, `:114` | Correo (`privacidad@macdelnorte.mx`, `contacto@macdelnorte.com`) | literal en el HTML | — | nadie | no aplica |
| `V/pages/Terminos-Conditions.blade.php:61`, `:105` | Correo (`logistica@macdelnorte.mx`, `contacto@macdelnorte.com`) | literal en el HTML | — | nadie | no aplica |
| `resources/views/mail/email-reset-password.html:37` | WhatsApp en un correo transaccional | literal `https://wa.link/f28njw` | — | nadie (un correo no ejecuta JavaScript) | no aplica |

(Las rutas `V/partials/…` de esta fila son `resources/views/partials/…`, fuera de `frontend/`.)

### 5.9 Código muerto (no se toca)

Estos bloques contienen enlaces, pero **no se ejecutan ni se muestran**; no cuentan para ninguna medición.

| archivo:línea | qué es | por qué está muerto |
|---|---|---|
| `V/layouts/master.blade.php:1258-1534` | Bloques viejos de rastreo: `wa.link` en 1294 y 1308, `tel:8124738768` en 1321, mapa de URLs en 1450-1453 | Todo el rango está dentro de comentarios Blade `{{-- … --}}` |
| `V/layouts/chat-personal.blade.php:155-157`, `:169-171`, `:182-184` | Menú de chat con `wa.link`, `mailto:product.manager@…` y `tel:8124738768` | Su `@include` está comentado en `master.blade.php:988` |
| `V/layouts/chatbot-assistan.blade.php:62` | Chatbot con `mailto:soporte@tusitio.com` y `wa.link` | Ninguna vista lo incluye |
| `V/pages/partials/category-hub-layout.blade.php:56-57`, `:190-191` | Teléfono y WhatsApp (`telefono_servicios`, `whatsapp_servicios`) | Ninguna página lo incluye (solo se menciona en su propio comentario) |
| `V/home/sections/video-eccomerce.blade.php:18` | WhatsApp (y enlace a `/contact`) | Ninguna vista lo incluye |

### 5.10 Resumen de cobertura actual

- Con clase `track-conversion` (los envía master): layout global, detalle de producto (salvo el botón de cotización formal), contacto, servicios y todo Brasil que sea WhatsApp.
- Sin la clase y **sí** rastreados por mdn-events: WhatsApp y teléfono de home, tarjetas, nav social, páginas autónomas, teléfonos de Brasil, y todas las cotizaciones formales y botones "Cotizar".
- **Sin ningún rastreo**: todos los `mailto:` sin clase (about, Brasil, legales, acceso), los enlaces a `/contact` desde producto y el layout de acceso.

---

## 6. Medición de cobertura por canal (consulta SQL mensual)

Sirve para responder: de los contactos que llegaron en el mes, ¿cuántos traen señal de anuncio propia, cuántos se pueden recuperar por la sesión, y cuántos quedan sin ninguna?

### Consulta 1: cobertura por canal (MySQL 8)

Validada en la base local (MySQL 8.0.30). Cambia `@from` y `@to` por el mes que quieras **en hora de México (UTC-6)**. La tabla guarda las fechas en UTC, así que el mes de noviembre de 2026 empieza el 1 de noviembre a las 00:00 de México, que son las 06:00 UTC. Monterrey está en UTC-6 todo el año (México eliminó el horario de verano en 2022).

Todas las consultas de esta sección excluyen las filas del Laboratorio de atribución con `is_test = 0` (en el cruce por sesión, `p.is_test = 0`). **Requieren haber corrido `php artisan migrate`**: sin la columna `is_test` fallan con "Unknown column". El filtro `is_test` se agregó después de validar la consulta en la base local; vuelve a ejecutarla una vez tras migrar.

```sql
SET @from = '2026-11-01 06:00:00';   -- 1 nov 00:00 hora de México
SET @to   = '2026-12-01 06:00:00';   -- 1 dic 00:00 hora de México

WITH ev AS (
  SELECT
    t.id,
    t.session_id,
    CASE
      WHEN t.type LIKE 'whatsapp%'                           THEN 'whatsapp'
      WHEN t.type LIKE 'telefono%' OR t.type = 'phone_click' THEN 'phone'
      WHEN t.type LIKE 'correo%'   OR t.type = 'email_click' THEN 'email'
      WHEN t.type = 'quote_submit'                           THEN 'quote'
    END AS channel,
    -- señal propia de Google: gclid válido, o gbraid/wbraid válido
    COALESCE(
      ( t.gclid REGEXP '^[A-Za-z0-9_-]{20,255}$'
        AND t.gclid NOT LIKE 'gtm%'
        AND t.gclid NOT REGEXP '^[0-9]+$' )
      OR t.gbraid REGEXP '^[A-Za-z0-9_-]{10,255}$'
      OR t.wbraid REGEXP '^[A-Za-z0-9_-]{10,255}$',
      0) AS own_google,
    COALESCE(t.fbclid <> '', 0)                              AS own_meta,
    COALESCE(t.utm_source <> '' OR t.utm_campaign <> '', 0)  AS own_utm
  FROM track_conversions t
  WHERE t.created_at >= @from
    AND t.created_at <  @to
    AND t.is_test = 0                                -- excluye las filas del Laboratorio de atribución
    AND ( t.type LIKE 'whatsapp%' OR t.type LIKE 'telefono%' OR t.type LIKE 'correo%'
          OR t.type IN ('phone_click', 'email_click', 'quote_submit') )
    AND COALESCE(t.utm_campaign, '') <> 'prueba'     -- excluye pruebas antiguas (anteriores a is_test)
    AND COALESCE(t.gclid, '')        NOT LIKE 'test%'
),
cls AS (
  SELECT
    ev.channel,
    CASE
      WHEN ev.own_google = 1 THEN '1_propia_google'
      WHEN ev.own_meta   = 1 THEN '2_propia_meta'
      WHEN ev.own_utm    = 1 THEN '3_propia_solo_utm'
      WHEN ev.session_id IS NULL THEN '5_sin_sesion_legacy'
      WHEN EXISTS (            -- una fila ANTERIOR de la misma sesión con señal válida
        SELECT 1
        FROM track_conversions p
        WHERE p.session_id = ev.session_id
          AND p.id < ev.id
          AND p.is_test = 0           -- igual que ev (ya filtrado a 0): real no hereda de prueba
          AND (
            COALESCE(
              ( p.gclid REGEXP '^[A-Za-z0-9_-]{20,255}$'
                AND p.gclid NOT LIKE 'gtm%'
                AND p.gclid NOT REGEXP '^[0-9]+$' )
              OR p.gbraid REGEXP '^[A-Za-z0-9_-]{10,255}$'
              OR p.wbraid REGEXP '^[A-Za-z0-9_-]{10,255}$',
              0) = 1
            OR COALESCE(p.fbclid, '')       <> ''
            OR COALESCE(p.utm_source, '')   <> ''
            OR COALESCE(p.utm_campaign, '') <> ''
          )
      ) THEN '4_por_sesion'
      ELSE '6_none'
    END AS bucket
  FROM ev
)
SELECT
  bucket,
  channel,
  COUNT(*) AS eventos,
  ROUND(100 * COUNT(*) / SUM(COUNT(*)) OVER (PARTITION BY channel), 1) AS pct_del_canal
FROM cls
GROUP BY bucket, channel
ORDER BY channel, bucket;
```

Qué significa cada grupo (`bucket`):

| bucket | Significado |
|---|---|
| `1_propia_google` | El propio evento trae `gclid`, `gbraid` o `wbraid` válido. |
| `2_propia_meta` | El propio evento trae `fbclid`. |
| `3_propia_solo_utm` | Solo trae `utm_source`/`utm_campaign` (sin click id). |
| `4_por_sesion` | El evento no trae nada, pero una fila **anterior** de la misma sesión sí (el orden se toma por `id`, no por hora). |
| `5_sin_sesion_legacy` | No hay `session_id` (filas anteriores a la captura de sesión). Con datos nuevos debería tender a cero. |
| `6_none` | Sin señal alguna. |

Resultado de referencia, ejecutada contra la base local con eventos de marzo a julio de 2026 (cada canal suma 100%):

| canal | 1_propia_google | 3_propia_solo_utm | 5_sin_sesion_legacy |
|---|---|---|---|
| whatsapp (816 eventos) | 83.8% | 3.1% | 13.1% |
| phone (113) | 85.8% | 3.5% | 10.6% |
| email (64) | 93.8% | 0% | 6.3% |

### Cómo leerla

- **`6_none` NO es pérdida.** Quien llega directo, por búsqueda orgánica o por recomendación no trae click id y es legítimo. No lo interpretes como "perdimos esos clientes".
- **Esta consulta mide eventos, no clientes.** Un evento con `gclid` casi nunca es un cliente; el cruce con Kommo (sección 4.4) es lo que separa clientes de curiosos.
- **La señal de pérdida de verdad** es comparar, mes a mes, las visitas del sitio con click id válido contra los clics que reporta la interfaz de Google Ads. Si el sitio registra bastante menos, hay clics que no se están capturando (bloqueadores, carga lenta, parámetro mal armado, página en caché…). Consulta de apoyo (`page_visit` se registra una vez por sesión y otra vez si llega un click id nuevo):

```sql
SELECT
  COUNT(DISTINCT t.gclid)        AS gclid_distintos,
  COUNT(DISTINCT t.session_id)   AS sesiones_con_gclid
FROM track_conversions t
WHERE t.type = 'page_visit'
  AND t.created_at >= @from AND t.created_at < @to
  AND t.is_test = 0
  AND t.gclid REGEXP '^[A-Za-z0-9_-]{20,255}$'
  AND t.gclid NOT LIKE 'gtm%'
  AND t.gclid NOT REGEXP '^[0-9]+$'
  AND COALESCE(t.utm_campaign, '') <> 'prueba';
```

  Compara `gclid_distintos` con los "Clics" de la cuenta de Google Ads del mismo mes (en la zona horaria de la cuenta). No esperes igualdad exacta: Google cuenta también clics inválidos y visitas que cierran antes de cargar; lo que importa es la tendencia y una brecha grande.
- **Regla de decisión:** si, después de un mes completo con folio y captura nuevos, el porcentaje de `6_none` en contactos que Kommo confirma como venidos de anuncios es alto (como punto de partida, más de 1 de cada 4; es un criterio sugerido, no una cifra del sistema), conviene reconsiderar un `visitor_id` persistente (hoy solo existe `session_id`, que cambia tras 30 minutos de inactividad). Si es bajo, no hace falta.

### Consulta 2: gclid inválidos en PRODUCCIÓN

La base local es una copia vieja de producción: solo contiene 5 `gclid` de prueba inválidos (`test154`, `test123`, `test2`, de agosto de 2025). **El conteo real de `gtm_…`, numéricos y cortos solo se puede obtener corriendo esto en producción:**

```sql
-- Cuántos hay y de qué tipo
SELECT
  CASE
    WHEN gclid LIKE 'gtm%'                          THEN 'prefijo_gtm'
    WHEN gclid REGEXP '^[0-9]+$'                    THEN 'solo_digitos'
    WHEN CHAR_LENGTH(gclid) < 20                    THEN 'corto_menos_de_20'
    WHEN gclid NOT REGEXP '^[A-Za-z0-9_-]{20,255}$' THEN 'caracteres_no_validos'
  END AS motivo,
  COUNT(*)                   AS filas,
  COUNT(DISTINCT session_id) AS sesiones,
  MIN(created_at)            AS primera,
  MAX(created_at)            AS ultima
FROM track_conversions
WHERE gclid IS NOT NULL AND gclid <> ''
  AND is_test = 0
  AND NOT ( gclid REGEXP '^[A-Za-z0-9_-]{20,255}$'
            AND gclid NOT LIKE 'gtm%'
            AND gclid NOT REGEXP '^[0-9]+$' )
GROUP BY motivo
ORDER BY filas DESC;

-- Ejemplos (primeros 20 caracteres) para ver de dónde sale la basura
SELECT LEFT(gclid, 20) AS ejemplo, CHAR_LENGTH(gclid) AS largo, COUNT(*) AS filas
FROM track_conversions
WHERE gclid IS NOT NULL AND gclid <> ''
  AND is_test = 0
  AND NOT ( gclid REGEXP '^[A-Za-z0-9_-]{20,255}$'
            AND gclid NOT LIKE 'gtm%'
            AND gclid NOT REGEXP '^[0-9]+$' )
GROUP BY LEFT(gclid, 20), CHAR_LENGTH(gclid)
ORDER BY filas DESC
LIMIT 25;
```

Una vez desplegado el backend nuevo, los valores rechazados dejan de guardarse en `gclid` (queda null) y se anotan en `meta`. Para contarlos:

```sql
SELECT LEFT(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.click_id_rejected_val')), 20) AS ejemplo,
       COUNT(*) AS filas
FROM track_conversions
WHERE JSON_EXTRACT(meta, '$.click_id_rejected') = 1
  AND is_test = 0
GROUP BY 1
ORDER BY filas DESC
LIMIT 25;
```

Si aparecen muchos valores numéricos, sugiere un parámetro ValueTrack mal armado en la plantilla de seguimiento de la cuenta de Google Ads.

### Versión de la base de datos en producción

Las consultas usan CTE (`WITH`) y funciones de ventana (`SUM() OVER`). Requieren **MySQL 8.0 o MariaDB 10.2+** (las funciones de ventana: MariaDB 10.2+). **Hay que confirmar qué versión corre en producción** (`SELECT VERSION();`) antes de depender de ellas. Si fuera MySQL 5.7, habría que reescribirlas con subconsultas.

---

## 7. Privacidad (LFPDPPP): no se publica nada

Este documento **no cambia el sitio**: el aviso de privacidad no se tocó. Aquí van las lagunas detectadas y un texto propuesto para que lo revise el abogado. Nada de lo siguiente es asesoría legal.

### 7.1 Las 12 lagunas del aviso actual

Verificadas leyendo `resources/views/frontend/pages/aviso-privacidad.blade.php` y el layout del sitio.

| # | Laguna | Evidencia |
|---|---|---|
| 1 | No cubre los identificadores de clic (`gclid`, `gbraid`, `wbraid`, `fbclid`) ni los UTM | La sección 2 solo lista datos de identificación, fiscales y comerciales. |
| 2 | No menciona la cookie propia `mdn_attr` ni el uso de `localStorage` | La sección 9 habla de cookies en genérico; el texto no contiene `mdn_attr`. |
| 3 | La analítica y la publicidad no figuran como finalidad, y no hay forma de oponerse | La sección 3 solo lista promociones, encuestas y personalización como finalidades secundarias. |
| 4 | No declara las transferencias a Google, Meta, Kommo, n8n ni Google Apps Script | La sección 5 solo menciona paquetería, procesadores de pago y autoridades fiscales. |
| 5 | No informa que el mensaje de WhatsApp/correo lleva un folio | No aparece la palabra "folio" en el aviso. |
| 6 | No informa el envío de conversiones a Google Ads por n8n | Tampoco aparece Google Ads ni n8n. |
| 7 | No hay plazo de conservación para los datos de navegación | La sección 8 solo cubre la relación comercial y 5 años posteriores. |
| 8 | No explica cómo revocar el consentimiento | El aviso habla de derechos ARCO, pero no menciona la revocación. |
| 9 | Hay un recuadro público "Texto pendiente" en la sección de cookies | `aviso-privacidad.blade.php:103-106`, clase `legal-placeholder`, con estilo visible (no está oculto). |
| 10 | No hay banner de cookies ni Consent Mode | `master.blade.php` carga Google Ads (`AW-16512201966`), GTM (`GTM-PX9LXWGR`), GA4 (`G-K8M2ZT5HYK`) y el píxel de Meta (`1912650302805252`) sin condición. |
| 11 | Los correos de privacidad son inconsistentes (`.mx` vs `.com`) | Los derechos ARCO usan `privacidad@macdelnorte.mx` (línea 89) y el contacto de la sección 11 usa `contacto@macdelnorte.com` (línea 114). |
| 12 | Aviso desactualizado | "Última actualización: 9 de mayo de 2025" (línea 8), anterior a la captura de identificadores y a este proyecto. |

Dato útil para el aviso: la tabla `track_conversions` **no tiene columnas de IP ni de user-agent** (no están en `$fillable` del modelo). Sí guarda identificadores de clic, UTM, referrer, página, sesión y, si el visitante lo provoca, el folio. Los servidores web y los proveedores (Google, Meta) pueden recibir la IP por su cuenta.

### 7.2 Texto propuesto (no publicado)

Para copiar, revisar por abogado y publicar cuando se decida. Lo que va entre [corchetes] se debe confirmar o decidir antes. Está redactado como nuevas secciones o ampliación de las secciones 2, 3, 5, 8 y 9 del aviso.

---

**Datos de navegación e identificadores**

Cuando usted visita nuestro sitio web, recabamos de forma automática datos de navegación que no lo identifican por su nombre, tales como: la página por la que ingresó, la página de procedencia, los parámetros de campaña de publicidad (por ejemplo, UTM), los identificadores de clic de anuncios que asignan las plataformas publicitarias (gclid, gbraid, wbraid y fbclid), un identificador aleatorio de sesión, el producto que consultó, y los botones de contacto que utilizó (WhatsApp, teléfono, correo o cotización). Cuando usted nos contacta, generamos además un código de contacto ("folio") que asociamos a estos datos.

**Finalidades de analítica y publicidad**

Utilizamos estos datos para: (a) medir qué anuncios y páginas generan contactos, (b) mejorar el sitio y nuestras campañas, y (c) informar a Google Ads y a otras plataformas publicitarias que un contacto proveniente de un anuncio derivó en una cotización o venta, con el fin de evaluar y optimizar nuestra publicidad. Estas son **finalidades secundarias**: no son necesarias para que usted nos compre o solicite una cotización, y puede oponerse a ellas en cualquier momento sin que se afecte el servicio.

**Cookie propia `mdn_attr` y almacenamiento local**

Nuestro sitio guarda en su navegador una cookie propia llamada `mdn_attr`, con una vigencia de **90 días**, y una copia de la misma información en el almacenamiento local de su navegador (clave `mdn_attr`). Contienen: los identificadores de clic y parámetros de campaña de su visita, la página y la fuente de procedencia, la fecha del último contacto publicitario y un identificador aleatorio de sesión. El almacenamiento local también puede incluir las claves `gclid`, `utm_source`, `utm_medium`, `utm_campaign` y `landing_page` (versión anterior de esta captura), `mdn_visit_sent` (evita registrar la misma visita dos veces) [y `mdn_queue`, cola temporal de eventos pendientes de envío, hasta 7 días]. Además, utilizamos cookies y tecnologías de Google (Google Ads, Google Analytics, Google Tag Manager) y de Meta (Píxel de Meta), que establecen sus propias cookies [lista y vigencia a confirmar con cada proveedor].

**Terceros destinatarios**

Los datos de navegación y de contacto descritos pueden ser tratados o enviados a: Google LLC (Google Ads, Google Analytics, Google Tag Manager y Google Apps Script/Hojas de cálculo, para medición y publicidad), Meta Platforms, Inc. (píxel y publicidad), Kommo (sistema de gestión de clientes con el que atendemos sus mensajes de WhatsApp y correo) y n8n [proveedor/hospedaje a confirmar] (automatización que relaciona su mensaje con el anuncio de origen y envía la conversión a Google Ads). Algunos de estos proveedores se encuentran fuera de México; con su uso del sitio y de los botones de contacto, usted [consiente / se informa] esta transferencia [redacción de consentimiento a definir con el abogado, según artículos 36 y 37 de la LFPDPPP].

**Folio de contacto en WhatsApp y correo**

Cuando usted toca un botón de WhatsApp o de correo de nuestro sitio, el mensaje que se abre ya incluye un código ("Folio: MN…") [y, de ser el caso, una marca invisible con ese mismo código]. Este código nos permite identificar de qué anuncio o página proviene su contacto. Puede borrarlo del mensaje antes de enviarlo; si lo hace, podremos atenderlo igualmente, pero no podremos relacionar su mensaje con el anuncio de origen. Las llamadas telefónicas no incluyen este código.

**Cómo oponerse o revocar su consentimiento**

Puede oponerse a las finalidades de analítica y publicidad, o revocar el consentimiento que nos haya dado, en cualquier momento, enviando su solicitud a [privacidad@macdelnorte.mx / correo único a definir] con su nombre y la descripción de su petición; responderemos en un máximo de 20 días hábiles. Adicionalmente, usted puede: (a) borrar la cookie `mdn_attr` y los datos del sitio desde la configuración de su navegador, (b) bloquear cookies de terceros, (c) administrar sus preferencias de anuncios en la configuración de su cuenta de Google y de Meta, y (d) [usar el banner de cookies para rechazar la analítica y la publicidad, cuando esté disponible].

**Plazo de conservación**

La cookie `mdn_attr` se conserva hasta **90 días** desde su última actualización. Los datos de navegación y los folios asociados a un contacto se conservan en nuestras bases por [plazo a definir, por ejemplo 24 meses] y después se eliminan o se anonimizan. Si el contacto se convierte en cliente, los datos de la relación comercial se conservan según la sección de conservación de este aviso.

---

### 7.3 Checklist para activar el folio invisible

El folio **visible** puede salir antes: es texto que el cliente ve y puede borrar. El **invisible** no se ve, así que debe esperar al aviso.

- [ ] El abogado revisó y aprobó el texto propuesto de 7.2 (con los [corchetes] resueltos).
- [ ] El aviso de privacidad nuevo está **publicado** en el sitio, con fecha de actualización nueva, y se quitó el recuadro "Texto pendiente".
- [ ] Se unificó el correo para derechos ARCO y contacto de privacidad (`.mx` o `.com`, uno solo).
- [ ] Preferiblemente: banner de cookies con **Consent Mode v2** (Google) funcionando, de modo que Ads, GA4 y el píxel de Meta respeten la elección del visitante. Hoy todo carga sin condición.
- [ ] Hay un procedimiento interno para atender solicitudes de oposición/revocación (quién las recibe y cómo se borran los datos asociados a un folio; el panel de administración muestra el folio de cada evento).
- [ ] El folio **visible** ya lleva al menos una semana funcionando sin incidentes (secciones 8 y 4.4).
- [ ] Solo entonces: `ATTRIBUTION_INVISIBLE_FOLIO=true` + `php artisan config:clear`. Probar con un mensaje real de WhatsApp: el texto se ve igual y n8n sigue extrayendo el folio.
- [ ] El envío de conversiones a Google Ads por n8n tampoco debe activarse en producción antes de publicar el aviso.

---

## 8. Checklist de salida a producción por fases

El diseño es aditivo: `POST /track-conversion` sigue aceptando el payload viejo, no se borran columnas ni datos, y con el interruptor apagado el sitio se comporta como antes.

**Fase 0. Antes de desplegar**
- [ ] La rama pasó `php artisan test` y `node --test tests/js`.
- [ ] Se decidió quién genera el token y dónde se guarda (credencial de n8n, gestor de contraseñas).
- [ ] Se confirmó la versión de MySQL/MariaDB de producción (sección 6).
- [ ] Se verificó con quien administra `wa.link/f28njw` si se usa su analítica o un mensaje predeterminado (ver riesgo 6 en la sección 9).

**Fase 1. Desplegar con el folio apagado** (en este orden)

1. [ ] **Desplegar** el código con `ATTRIBUTION_FOLIO_ENABLED=false` (o sin la variable: el valor por defecto es `false`).
2. [ ] **`php artisan migrate`** (agrega `contact_folio`, `event_at` y dos índices, y `is_test` —boolean, no nulo, default 0— con el índice `(is_test, id)`, migración `2026_10_20_100000_add_is_test_to_track_conversions_table.php`; no toca datos existentes: todas las filas históricas quedan con `is_test = 0`). Esa misma ejecución crea la tabla `attribution_lab_settings` (migración `2026_10_21_100000_create_attribution_lab_settings_table.php`), donde el laboratorio guarda el número de WhatsApp de prueba y la URL del webhook de n8n; sin ella, el panel muestra un aviso al intentar guardar ajustes pero el resto sigue funcionando. Hazlo **antes** de que el código nuevo reciba tráfico: el admin, el endpoint y las consultas SQL de la sección 6 usan `is_test`.
3. [ ] **`php artisan config:clear`** (y `config:cache` si se usa).
4. [ ] **Crear el token de atribución**: en Marketing > Integración, **+ Crear Nuevo** con uso "Atribución Google Ads" (sección 2; requiere haber corrido `php artisan migrate`, que incluye `2026_10_22_100000_add_scope_to_marketing_api_tokens_table.php`), copiar el valor `<key_id>.<secret>` y guardarlo en la credencial de n8n. Alternativa/respaldo: `ATTRIBUTION_API_TOKEN` en el `.env` del servidor (mínimo 32 caracteres) y `php artisan config:clear`.
5. [ ] **Verificar `APP_URL`**: debe ser el dominio público real del sitio (por ejemplo `https://www.macdelnorte.com`). La verificación de origen de `/track-conversion` corre **siempre**, aunque el folio esté apagado: con `APP_URL=http://localhost` en producción todo el tráfico real recibiría `204` y no se guardaría ningún evento, **sin ningún aviso visible**. Si el sitio responde también en otro dominio (otro alias), agrégalo en `ATTRIBUTION_ALLOWED_HOSTS`.
6. [ ] **Verificar que nada cambió en los enlaces**: abrir el sitio, inspeccionar el botón flotante y el del detalle de producto; el `href` sigue siendo `https://wa.link/f28njw` y el clic abre WhatsApp sin mensaje nuevo.
7. [ ] **Verificar que los eventos siguen llegando**: tocar un botón y confirmar la fila nueva en el panel de administración (módulo de conversiones). Si no aparece nada, revisar el punto 5 (`APP_URL`) antes que cualquier otra cosa.
8. [ ] **Probar el endpoint**: sin ningún token (ni de panel ni en el `.env`) debe dar 503; con el token, un folio inexistente debe dar 404 y un token malo 401 (si usas el `.env`, recuerda `config:clear`; un `.env` de menos de 32 caracteres cuenta como no configurado). Comprueba también que un token de Email Marketing da 401 en este endpoint.

**Fase 2. Verificar el número y encender solo el botón flotante**

9. [ ] **Verificar que el número de WhatsApp es el número conectado a Kommo.** El mensaje con folio se abre en `wa.me/{config('contact.whatsapp.number')}` (hoy `528135825559`, el 81 3582 5559): ese número debe ser **exactamente** el que está conectado a Kommo, o los mensajes con folio llegarán a un chat que Kommo no ve y n8n nunca recibirá el folio. Confirmar también que `https://wa.link/f28njw` apunta a ese mismo número.
10. [ ] **Encender solo el botón flotante**: en `.env` poner `ATTRIBUTION_FOLIO_ENABLED=true` y `ATTRIBUTION_FOLIO_PLACEMENTS=flotante`; `php artisan config:clear`. Es el único placement; el resto de los botones no cambia. (La cotización formal usa el placement `cotizacion`; queda apagada mientras no esté en la lista.)
11. [ ] Probar en un teléfono real (Android y iPhone): el botón flotante abre WhatsApp con "Folio: MN…" en el mensaje, el mismo folio aparece en la base y el endpoint lo devuelve. En Kommo debe verse el mensaje con el folio.
12. [ ] Probar el flujo completo con n8n en modo de prueba, **sin subir nada a Google Ads todavía**. La forma segura es el **Laboratorio de atribución** (sección 10): con el número de la cuenta de prueba de Kommo guardado en el panel (Ajustes de prueba; o en `ATTRIBUTION_TEST_WHATSAPP_NUMBER` como respaldo) y el modo prueba activado, las filas salen con `is_test: true`. **Antes de esto, agrega en n8n el nodo IF que ignora `is_test = true` antes de cualquier subida a Google Ads** (sección 4.2).

**Fase 3. Medir una semana y ampliar**

13. [ ] **Dejar correr al menos una semana** y medir (sección 4.4): cobertura de folio (leads de Kommo con folio / leads web), atribuibles, ambiguos, y que el volumen de contactos del botón flotante no haya bajado respecto a la semana anterior.
14. [ ] **Ampliar** la lista de a poco (por ejemplo `flotante,footer_oficina,producto,cotizacion`) y, si todo va bien, `ATTRIBUTION_FOLIO_PLACEMENTS=*`. Cada ampliación: `php artisan config:clear`.
15. [ ] Revisar de nuevo los contactos por canal con la consulta de la sección 6 al cerrar el mes.
16. [ ] **Activar la subida a Google Ads por n8n** solo con el aviso de privacidad publicado (sección 7.3) y la política de la sección 4.3.

**Cómo revertir sin redeploy**
1. En `.env` del servidor: `ATTRIBUTION_FOLIO_ENABLED=false`.
2. `php artisan config:clear` (y `config:cache` si se usa).
3. Si hay caché de páginas o CDN, purgarlo: el interruptor se imprime en el HTML de cada página.
4. Los navegadores que ya cargaron la página siguen con el comportamiento anterior hasta que la recarguen.

**Si después de desplegar dejan de aparecer eventos nuevos** (la causa más probable es `APP_URL` o un dominio alterno): pon temporalmente `ATTRIBUTION_ORIGIN_CHECK=false` y `php artisan config:clear` para volver a guardar todo, corrige `APP_URL` / `ATTRIBUTION_ALLOWED_HOSTS` y vuelve a encenderlo. Un `204` en `POST /track-conversion` significa "recibido pero no guardado" por la verificación de origen.

Con el flag apagado, el JavaScript no cambia ningún `href` ni agrega folio, pero el registro de eventos continúa. No hace falta revertir las migraciones (las columnas nuevas son nulas o con default 0, e inofensivas); no ejecutes `migrate:rollback` salvo que sea indispensable.

### Cambios de métrica a anunciar al equipo

- **Los botones de las páginas autónomas** (`catalogo`, `categorias`, `categoria-productos`) que abren WhatsApp dejan de contar como `quote_start` y pasan a `whatsapp_click`. Los conteos mensuales de "inicio de cotización" bajarán y los de WhatsApp subirán en esa misma cantidad.
- **El `type` de los eventos nuevos pierde granularidad**: en la base queda el tipo canónico (`whatsapp_click`, `phone_click`, `email_click`) y el lugar del botón pasa a `meta.placement`; el tipo histórico del botón queda en `meta.legacy_type`. Los filtros por prefijo (`whatsapp%`, `telefono%`, `correo%`) siguen funcionando con datos viejos y nuevos. Google Tag Manager y Google Sheets **siguen recibiendo el valor viejo** (`data-type`), así que lo que ya dependía de él no cambia.
- Los eventos nuevos traen `contact_folio` y `event_at`; las filas históricas no se reescriben.
- **Los eventos de prueba (`is_test = 1`) no cuentan** en el resumen, la tabla, las pestañas ni la exportación del módulo de conversiones del admin, ni en las consultas SQL de la sección 6. Tras `php artisan migrate` todas las filas históricas quedan con `is_test = 0` (cuentan como reales); las pruebas antiguas hechas a mano con `utm_campaign=prueba` o `gclid` `test…` siguen excluidas solo de las consultas SQL, no del admin. Si el conteo del admin y el de SQL difieren por poco, es la causa más probable. Las pruebas del laboratorio solo se ven en la sección "Eventos de prueba recientes" del propio laboratorio.
- Los valores de `gclid` con formato inválido (`gtm_…`, solo dígitos, cortos) ya no se guardan en `gclid`: quedan como null y anotados en `meta`. La cantidad de "visitas con gclid" puede bajar respecto al histórico por esta limpieza, no por menos tráfico.

---

## 9. Riesgos y límites

1. **Safari e iOS acortan la memoria del navegador.** Safari limita las cookies que crea JavaScript y el `localStorage` a 7 días (a 24 horas cuando el visitante llegó por un enlace con parámetros de seguimiento). La promesa de "90 días" **no se cumple en iPhone**: quien vuelva después de una semana ya no traerá su `gclid`. Una cookie puesta desde el servidor mitigaría esto; queda como fase aparte, después de medir un mes.
2. **El cliente puede borrar el texto.** El folio vive en el mensaje prellenado. Si el cliente lo borra o escribe otro mensaje, no hay folio y n8n no puede atribuir ese lead (se mide como cobertura de folio, sección 4.4).
3. **Las llamadas no llevan folio.** El cruce por hora es probabilístico (dos personas pudieron tocar "Llamar" en la misma ventana) y no debe subirse a Ads como conversión principal.
4. **La atribución es "pegajosa".** El click id dura hasta 90 días aunque la visita de hoy no venga de un anuncio. Por eso el endpoint expone `click_age_days` y `touch_conflict`, y la política de n8n exige 30 días o menos y sin conflicto.
5. **Los folios pueden chocar.** Son unos 17 millones de combinaciones: la colisión es rara pero posible. El endpoint la detecta con `ambiguous` y `candidates` y n8n no sube esos casos; no se pierde ningún evento porque el índice del folio no es único.
6. **Cambiar de `wa.link` a `wa.me` pierde la analítica de `wa.link`.** Con el folio encendido, los botones de WhatsApp abren `https://wa.me/{número}?text=…` en lugar de `https://wa.link/f28njw` (el `href` del HTML no cambia: sin JavaScript sigue abriendo `wa.link`). Si alguien usa las estadísticas o el mensaje predeterminado de `wa.link/f28njw`, hay que verificarlo con quien lo administra **antes** de encender el folio. Además, "copiar dirección del enlace" sigue dando el enlace sin folio.
7. **Una conversión con `gclid` no equivale a un cliente.** Muchos contactos con anuncio son curiosos, proveedores o competidores. Subir todo a Google Ads sin filtrar por lo que ocurre en Kommo hace que Google optimice hacia contactos baratos y no hacia ventas. La política de 4.3 es el mínimo; lo ideal es subir solo etapas del embudo que representen cliente real.
8. **Bloqueadores y consentimiento.** Un visitante con bloqueador de anuncios o que rechace cookies (cuando exista banner) no será atribuible. Es un techo de cobertura natural.
9. **El ≥90% no se mide en el sitio.** Depende de cuántos clientes de Kommo llegan con folio y de las llamadas. Medirlo es responsabilidad de n8n (sección 4.4).
10. **La base local es una copia vieja de producción.** Los conteos de `gtm_…` y numéricos, y la consulta mensual, hay que correrlos en producción.
11. **Privacidad.** Hasta publicar el aviso nuevo y, preferiblemente, un banner con Consent Mode, el folio invisible y el envío a Ads deben seguir apagados (sección 7).

---

## 10. Cómo probar desde el laboratorio

### 10.1 Objetivo

Recorrer el flujo completo (visita de anuncio con `gclid` → clic en WhatsApp → fila en la base → consulta del endpoint → mensaje real a Kommo → n8n) **desde el panel de administración**, sin tocar a los visitantes reales, sin ensuciar las métricas y sin que nada llegue a Google Ads, GA4 ni Meta.

El laboratorio vive en **Admin > Administrar Sitio Web > "Laboratorio de atribución"** (`/admin/attribution-lab`).

### 10.2 Qué hace y qué NO hace el modo prueba

El modo prueba se activa con una cookie propia, `mdn_attr_test`, que solo emite el panel de administración. Es **por navegador**: el resto del mundo ve el sitio exactamente igual que siempre.

| Característica | Detalle |
|---|---|
| Cookie | `mdn_attr_test`: cifrada, `HttpOnly`, `SameSite=Lax`, vigencia de **120 minutos** (config `attribution.test_mode.ttl_minutes`). En producción lleva el dominio `.macdelnorte.com` (cubre `macdelnorte.com` y `www.`); en local y en `.test` es de host exacto. Es `Secure` si la conexión es HTTPS o si en producción `APP_URL` empieza con `https://`. Su contenido (`{u, e, n, s}`) lleva además una **firma HMAC** ligada a `APP_KEY`: aunque en URLs sin ruta (404) el cifrado de cookies de Laravel no corra, un valor forjado sin la firma no activa el modo prueba. Si cambias `APP_KEY`, las cookies de prueba vigentes dejan de valer (basta con volver a activar el modo). |
| Validación | Cada uso la revalida: que no haya expirado y que el usuario que la emitió **siga siendo administrador con acceso al módulo `site`**. Una cookie corrupta, vencida o de un usuario sin permiso se trata como si no existiera. |
| Comportamiento del sitio | En ese navegador el folio se comporta como si `ATTRIBUTION_FOLIO_ENABLED=true` y `ATTRIBUTION_FOLIO_PLACEMENTS=*`, **aunque en el servidor estén apagados**. Los botones de WhatsApp abren `https://wa.me/{número de prueba}?text=…` (o `https://wa.me/?text=…` sin número, si no hay uno de prueba válido). El número de prueba sale del panel ("Ajustes de prueba", guardado en la base de datos) y, si ahí está vacío, de `ATTRIBUTION_TEST_WHATSAPP_NUMBER` del `.env`. |
| Analítica | **No carga** GTM, GA4, Google Ads, el Píxel de Meta ni el webhook de Google Sheets; en su lugar se ponen funciones vacías (`dataLayer`, `gtag`, `fbq`) para que el resto del JavaScript no falle. |
| Banda | Una etiqueta fija "Modo prueba de atribución activo" indica que ese navegador está en modo prueba. No bloquea clics. |
| Atribución previa | Al entrar al sitio por primera vez después de activar, la página **borra** la atribución guardada en ese navegador (`mdn_attr` en cookie y `localStorage`, claves antiguas `gclid`/`utm_*`/`landing_page` y `mdn_visit_sent`), para que la prueba no herede un `gclid` real tuyo. Al desactivar, vuelve a limpiar. Consecuencia: **pierdes la atribución real que ese navegador tuviera**. |
| Qué marca como prueba | `POST /track-conversion` decide `is_test` en el servidor: cookie de modo prueba válida **y** `test: 1` en el payload. `test: 1` sin cookie válida responde `204` y no guarda nada; cookie sin `test: 1` guarda una fila **real**; y, como respaldo, un `gclid` que empieza exactamente con `TEST_GCLID_` fuerza `is_test` aunque no haya cookie. |
| Aislamiento | Las filas de prueba no cuentan en el resumen, la tabla, las pestañas ni la exportación del admin, ni en las consultas SQL de la sección 6. Una fila real nunca hereda atribución de una fila de prueba de la misma sesión, ni al revés. |
| Cuenta de WhatsApp | El número de prueba pertenece a la cuenta de prueba de Kommo (**"test2mac"**), no al CRM real. |

**Lo que el modo prueba NO hace:**

- **No apaga todo lo de terceros.** reCAPTCHA, las fuentes y las librerías de CDN siguen cargando (no son analítica).
- **No desvía `tel:` ni `mailto:`.** Un clic en un teléfono o un correo usa la línea o la dirección reales. En modo prueba, no los toques.
- **El probador "como n8n" no es n8n.** Llama a la misma lógica que el endpoint pero **en el servidor, directamente**: no ejercita el token `Bearer`, ni el límite de 120 solicitudes por minuto, ni la cabecera `Cache-Control: no-store` de la respuesta. Para comprobar eso usa el `curl` de la sección 3 (el laboratorio genera uno con "Copiar como curl").
- **No prueba teléfonos móviles.** La cookie se emite al navegador donde estás logueado en el admin; no existe (todavía) un enlace o QR para activar el modo en un celular.
- **No sustituye** la prueba final con un WhatsApp real a la cuenta de prueba de Kommo (pasos 7 y 8).

### 10.3 Antes de empezar

1. `php artisan migrate` ya corrió (agrega `is_test`; sección 8, paso 2).
2. Tu usuario es administrador con acceso al módulo **Administrar Sitio Web** (`site`). Para consultar filas **reales** en el probador necesitas además el módulo de **Ads** (`ads`).
3. **Ajustes de prueba, desde el panel** (sin tocar el `.env` ni correr `config:clear`): en el laboratorio, sección 2 "Modo prueba" > "Ajustes de prueba", escribe el **número de WhatsApp de prueba** (cuenta de prueba de Kommo: solo dígitos, con lada de país, de 10 a 15; los espacios, `+`, guiones y paréntesis se limpian solos) y la **URL del webhook de n8n para pruebas** (https, pública), y pulsa "Guardar ajustes". Se guardan en la base de datos y valen de inmediato. El número del panel tiene prioridad; `ATTRIBUTION_TEST_WHATSAPP_NUMBER` del `.env` queda solo como **respaldo** cuando el panel no tiene uno (el panel indica la fuente: "Guardado en el panel", "Tomado del .env" o "Sin configurar"). Para consultar el endpoint con `curl` hace falta un token de atribución (creado en Marketing > Integración con uso "Atribución Google Ads", o `ATTRIBUTION_API_TOKEN` en el `.env` con `php artisan config:clear` si lo cambias). El panel de estado del laboratorio avisa si falta el número de prueba, si es inválido, si falta el token o si `ATTRIBUTION_INVISIBLE_FOLIO` está encendido.
4. Tienes a la mano un teléfono con WhatsApp **distinto** del número de prueba, y acceso a la cuenta de prueba de Kommo (y a n8n) para los pasos 7 y 8.

### 10.4 Guía paso a paso

1. **Activar el modo prueba.** En el laboratorio, "Activar modo prueba". El panel de estado cambia a activo y la cookie queda puesta por 120 minutos. Para salir antes está "Desactivar modo prueba" (paso 11).
2. **Comprobar la banda y "Verificar sitio público".** Abre el sitio en **este mismo navegador**: debe verse la banda "Modo prueba de atribución activo". Vuelve al laboratorio y pulsa "Verificar sitio público": comprueba que la página pública trae la marca de modo prueba y **no** trae GTM. Si falla, ve a 10.8 antes de seguir.
3. **Simular la visita de un anuncio.** En el generador de visita escribe un `gclid` que empiece con `TEST_GCLID_` (por ejemplo `TEST_GCLID_0123456789ABCDEF`; el generador valida que tenga el largo mínimo) y, si quieres, `gbraid`/`wbraid` (10 caracteres o más) y UTM (`utm_source=google`, `utm_medium=cpc`, `utm_campaign=…`). Opcionalmente elige un producto con el buscador (desde 2 letras) para aterrizar en su página. Pulsa **"Abrir en pestaña nueva"**. El generador no ofrece copiar la URL a propósito (ver el aviso de 10.6).
4. **Tocar un botón de WhatsApp.** En la pestaña abierta (con la banda visible) toca el botón flotante u otro de WhatsApp. Se abre `wa.me` con el **número de prueba** (o sin número) y un mensaje del tipo "Hola, me interesa: …. Folio: MN7K4Q2". **Todavía no envíes nada.** Revisa en la barra de dirección que el número sea el de prueba (10.6).
5. **Ver la fila.** Vuelve al laboratorio: en "Eventos de prueba recientes" aparece la fila nueva (se actualiza sola mientras la pestaña está visible; fechas en UTC y en hora de México). Ábrela para ver el JSON completo del evento tal como lo devolvería el endpoint, incluyendo `is_test: true`, el `gclid`, `has_google_click_id` y `attribution_source`. Normalmente verás también la fila `page_visit` de la visita simulada.
6. **Consultar el endpoint.** En el probador "como n8n" elige **folio** (pega el folio del mensaje) o **ventana de tiempo** (tipo, `from` y `to` con zona y máximo 15 minutos, como en la sección 3). Debe devolver el mismo evento, con `is_test: true`. Con "Copiar como curl" obtienes el comando listo (con `$TOKEN` literal: pega el token en tu terminal, nunca en el panel) para repetirlo contra el servidor real y sí ejercitar token y límite. Para folios reales (`is_test: false`) el probador exige acceso al módulo `ads`.
7. **Enviar el WhatsApp al número de prueba.** Desde tu teléfono (distinto del de prueba) envía el mensaje tal como se prellenó, **sin borrar el folio**, al número de la cuenta de prueba.
8. **Verificar Kommo y n8n.** Confirma que el mensaje aparece en la cuenta de prueba de Kommo ("test2mac") con el folio. En n8n comprueba, en la ejecución: (a) que el nodo de extracción obtuvo `MN…`; (b) que la consulta al endpoint devolvió el evento con `is_test: true`; y (c) que el nodo IF de 4.2 **desvió** el caso y **no** llegó a la rama de subida a Google Ads. Si el flujo sube algo con `is_test = true`, es un error que se debe corregir antes de seguir.
   - **Atajo sin WhatsApp: "Enviar mensaje de prueba a n8n".** Con la URL del webhook de n8n guardada en "Ajustes de prueba" (10.3, punto 3), este botón (sección 2 del laboratorio) hace que **el servidor** mande a n8n un mensaje simulado con el folio de **tu último evento de prueba** (necesitas al menos uno: pasos 3 a 5). Sirve para depurar n8n sin enviar un WhatsApp real. El panel muestra el código HTTP, el tiempo en milisegundos, el folio y el texto enviados y el extracto de la respuesta de n8n; si falla, un aviso en rojo con el motivo. Ver el payload en 10.5. No sustituye la prueba con un WhatsApp real a Kommo (pasos 7 y 8): no pasa por Kommo.
9. **Simulador de mensaje entrante (opcional).** Pega en el simulador un texto como el que llegaría de Kommo (con el folio dentro, en minúsculas, con espacios o guiones, con caracteres invisibles…). Muestra dos resultados: lo que extrae la **regex exacta** (la base de 4.1, que exige mayúsculas y el folio pegado) y lo que extrae la **tolerante** (acepta `MN-7K4Q2`, `MN 7K4Q2`, `MN.7K4Q2`, quita invisibles y no es sensible a mayúsculas). Si solo la tolerante lo encuentra, el laboratorio avisa: tu nodo de n8n debe ser el tolerante (el Code de 4.1), o ese mensaje se perdería.
10. **Borrar los eventos de prueba.** "Borrar mis eventos de prueba" pide confirmación y ejecuta un borrado fijo: **elimina todas las filas con `is_test = 1`** (la tabla no distingue qué usuario las generó) y nunca toca filas reales. No acepta ids ni filtros.
11. **Desactivar el modo prueba.** "Desactivar modo prueba": olvida la cookie y, en la siguiente visita, la página limpia la atribución de prueba. Recarga el sitio en ese navegador y confirma que ya no hay banda y que los botones vuelven a su comportamiento de siempre. La cookie también expira sola a los 120 minutos.

### 10.5 Referencia

**Variable de entorno**

| Variable | Qué hace en el laboratorio |
|---|---|
| `ATTRIBUTION_TEST_WHATSAPP_NUMBER` | **Respaldo** del número que abren los botones de WhatsApp en modo prueba (solo dígitos, 10 a 15): solo se usa si el panel no tiene uno guardado. Vacío o inválido en ambos = `wa.me/?text=` sin número; el panel lo avisa. Ver la tabla de la sección 2. |
| `ATTRIBUTION_API_TOKEN` | Respaldo del token de la API. El laboratorio **nunca** lo muestra ni lo envía al navegador (ni el de los tokens del panel); solo avisa si no hay ninguno válido (ni en el `.env` ni activo en Marketing > Integración). Para `curl`. |

Duración (120 minutos) y nombre de la cookie (`mdn_attr_test`) se cambian en `config/attribution.php` (`test_mode`), no en el `.env`.

**Ajustes de prueba (se guardan en la base de datos, se editan en el laboratorio)**

| Ajuste | Qué hace | Reglas |
|---|---|---|
| Número de WhatsApp de prueba | Número que abren los botones de WhatsApp en modo prueba (cuenta de prueba de Kommo). Tiene prioridad sobre `ATTRIBUTION_TEST_WHATSAPP_NUMBER`. | Vacío (= usar el `.env` si lo hay; si no, `wa.me/?text=` sin número) o de 10 a 15 dígitos. El panel quita espacios, `+`, guiones, paréntesis y puntos antes de guardar. |
| URL del webhook de n8n (para pruebas) | Destino del botón "Enviar mensaje de prueba a n8n". No se usa en el sitio público ni con visitantes reales. | Vacía (= borrar) o `https://` pública, sin usuario ni contraseña, de 500 caracteres o menos. Puede llevar un token en la ruta: el panel la muestra solo en su campo, nunca en avisos ni en el resultado del envío. |

Guardar con un campo vacío lo borra de la base. El panel valida en el navegador con las mismas reglas del servidor y, si el servidor rechaza algo, marca el campo bajo su cuadro de texto.

**Payload del mensaje de prueba a n8n.** "Enviar mensaje de prueba a n8n" hace un `POST` JSON desde el servidor a la URL guardada, con estos campos:

| Campo | Contenido |
|---|---|
| `source` | Origen del envío: marca que identifica que lo manda el laboratorio, no Kommo. |
| `is_test` | Siempre `true`. El flujo de n8n debe tratarlo como prueba y **no** subir nada a Google Ads. |
| `folio` | Folio (`MN…`) de tu último evento de prueba. |
| `text` | Mensaje simulado, como el que llegaría de WhatsApp, con el folio dentro. |
| `message_at` | Hora del mensaje simulado (ISO 8601). |
| `lookup_url` | URL del endpoint de consulta (`/api/attribution/lookup`) ya apuntando a ese folio, para que n8n lo consulte igual que con un mensaje real. |

La respuesta del panel incluye `http_status`, `elapsed_ms`, un extracto de la respuesta de n8n (`response_excerpt`) y lo enviado (`sent.folio`, `sent.text`).

**Rutas del panel** (todas bajo `/admin`, nombres `admin.attribution-lab.*`)

| Método | Ruta | Para qué |
|---|---|---|
| GET | `/admin/attribution-lab` | Página del laboratorio |
| GET | `/admin/attribution-lab/status` | Tarjetas de estado y advertencias |
| GET | `/admin/attribution-lab/events` | Últimos 50 eventos de prueba (acepta `?after_id=` para actualizar) |
| GET | `/admin/attribution-lab/events/{id}` | JSON de un evento de prueba |
| GET | `/admin/attribution-lab/products-search` | Buscador de productos del generador (desde 2 caracteres, máximo 15) |
| POST | `/admin/attribution-lab/lookup` | Probador "como n8n" (folio o ventana) |
| POST | `/admin/attribution-lab/extract` | Simulador de mensaje entrante |
| POST | `/admin/attribution-lab/activate` | Activa el modo prueba (emite la cookie) |
| POST | `/admin/attribution-lab/deactivate` | Desactiva el modo prueba |
| POST | `/admin/attribution-lab/settings` | Guarda los ajustes de prueba (`test_whatsapp_number`, `n8n_url`; vacío = borrar). Error de validación: `422` con `errors` por campo |
| POST | `/admin/attribution-lab/n8n-test` | Envía el mensaje de prueba a la URL de n8n guardada (sin cuerpo) |
| DELETE | `/admin/attribution-lab/tests` | Borra todas las filas `is_test = 1` |

Activar, desactivar y borrar solo funcionan por `POST`/`DELETE` con el token CSRF del panel; no hay enlaces `GET` que cambien el estado.

**Permisos**

| Acción | Requiere |
|---|---|
| Entrar al laboratorio y ver el estado | Administrador con acceso al módulo `site` |
| Activar / desactivar el modo prueba | Lo mismo |
| Guardar los ajustes de prueba y enviar el mensaje de prueba a n8n | Lo mismo |
| Ver y consultar eventos de prueba, simular mensajes | Lo mismo |
| Consultar un folio o ventana con filas **reales** en el probador | Además, acceso al módulo `ads` (sin él, el probador solo devuelve filas `is_test`) |
| Borrar eventos de prueba | Módulo `site`; solo borra filas `is_test = 1` |

### 10.6 Checklist manual: números reales que aún podrían abrirse en modo prueba

El modo prueba cambia el número de los botones que pasan por el sistema de folio y de los enlaces que se imprimen desde el servidor con `AttributionTestMode::whatsappNumber()` (la página de contacto y las vacantes de `/empleos`). Quedan **enlaces que no se pueden garantizar por código** y hay que comprobar a mano, **antes de enviar cualquier mensaje**, que el chat que se abre es el de prueba o está vacío:

- [ ] **Botón flotante y botones de WhatsApp de México** (`wa.link` → `wa.me`): comprobar que la URL abierta usa el número de prueba o `wa.me/?text=` sin número.
- [ ] **Páginas `/br/*`**: usan `config('brasil.whatsapp')` (`BR_WHATSAPP_URL`). El sistema de folio solo reescribe los `wa.link`/`wa.me` que coinciden con la URL o el número configurados; si la URL de Brasil es distinta, el botón podría abrir el número real. Probar un botón de `/br/*` en modo prueba y verificar el destino.
- [ ] **`V/layouts/chat-personal.blade.php`**: su `@include` está comentado en `master.blade.php`; si alguien lo reactiva, trae `wa.link`, `tel:` y `mailto:` reales (sección 5.9). Confirmar que sigue comentado y, si no, si pasa por el delegado de `mdn-events.js`.
- [ ] **`V/layouts/chatbot-assistan.blade.php`**: ninguna vista lo incluye hoy (sección 5.9); confirmar que sigue así.
- [ ] **Bloque comentado de `master.blade.php`** (1258-1534, enlaces viejos con `wa.link` y `tel:`): sigue entre comentarios Blade `{{-- … --}}`.
- [ ] **`tel:` y `mailto:`** en todas las páginas: el modo prueba no los desvía. Un toque llama o escribe a la línea real. No los uses durante la prueba.
- [ ] **Cualquier `wa.link` distinto de `f28njw`** o enlace de WhatsApp nuevo que alguien agregue: no se reescribe (solo se reescriben los `wa.link`/`wa.me` que coinciden con la URL o el número configurados) y abriría el número real.

Y dos avisos importantes:

- **La URL de la visita simulada NO debe abrirse en otro navegador, ventana de incógnito, perfil o teléfono.** Ahí no hay cookie de modo prueba, así que la página cargaría GTM, GA4, Google Ads y el Píxel con ese `gclid` falso, y mandaría datos de prueba a esas plataformas (aunque la fila se guarde como prueba por el prefijo `TEST_GCLID_`). Por eso el generador solo ofrece "Abrir en pestaña nueva" y no copiar la URL. No la pegues en chats ni correos.
- **Nunca envíes el mensaje si el chat abierto no es el de prueba.** Si abre el número de producción, cierra el chat sin enviar.

### 10.7 Caché y CDN

- Las respuestas de las páginas públicas que se sirven **en modo prueba** llevan `Cache-Control: private, no-store`, para que ningún proxy las guarde y las sirva a otros visitantes.
- Hoy ni Laravel ni el servidor cachean el HTML ni `/track-conversion`. Pero si un CDN o el hosting **sí** cachean HTML y no respetan `no-store`, podrían servir la versión de prueba (sin analítica) a todos los visitantes, o la normal a quien está probando. "Verificar sitio público" (paso 2) detecta lo segundo desde tu navegador. Si añades un CDN con caché de HTML, repite esa verificación en producción y evita que cachee respuestas de la cookie `mdn_attr_test`.
- Si pruebas en producción, confirma antes que `APP_URL` es el dominio público real (sección 8, paso 5); si no, ni la cookie ni los eventos funcionarán.

### 10.8 Solución de problemas

| Síntoma | Causa probable y qué hacer |
|---|---|
| Activé el modo prueba pero **no aparece la banda** | **La cookie no se fijó o no llega al sitio público.** Revisa: (1) el **dominio**: en local, `localhost` y `127.0.0.1` son sitios distintos; entra al admin y al sitio por el mismo host. En producción la cookie usa `.macdelnorte.com`: si el sitio o el admin responden desde otro dominio, no se comparte. (2) **HTTP vs `Secure`**: en producción con `APP_URL=https://…` la cookie es `Secure` y el navegador no la envía por `http://`. (3) **`APP_URL`** incorrecto. (4) Se venció (120 minutos) o tu usuario ya no tiene acceso a `site`. (5) Una extensión o el modo del navegador bloquea cookies. (6) Hiciste el cambio en el `.env` y no corriste `php artisan config:clear`. En las herramientas del navegador (Aplicación > Cookies) debe existir `mdn_attr_test` con `HttpOnly`. |
| La banda aparece pero **no llegan filas** | Un `204` en `POST /track-conversion` significa "no guardado": revisa la verificación de origen (`APP_URL`, `ATTRIBUTION_ALLOWED_HOSTS`, sección 2) o que el JavaScript mande `test: 1` (recarga forzada: una versión antigua en caché no lo manda). |
| La fila llega como **real**, no de prueba | Se guardó sin `test: 1` (JavaScript antiguo en caché, o la página no es la de modo prueba). Recarga con Ctrl+F5 y repite. Bórrala en el módulo de conversiones si hace falta. |
| El botón abre `wa.me/?text=…` **sin número** | Es lo esperado cuando no hay número de prueba válido ni en el panel ni en `ATTRIBUTION_TEST_WHATSAPP_NUMBER` (el panel lo avisa y muestra la fuente actual). Escríbelo en "Ajustes de prueba" (solo dígitos, de 10 a 15) y guarda: no requiere `config:clear`. Si lo defines en el `.env`, sí corre `php artisan config:clear`. |
| "Enviar mensaje de prueba a n8n" está **deshabilitado** | No hay una URL de n8n **guardada**. Escríbela en "Ajustes de prueba" y pulsa "Guardar ajustes" (el botón envía la URL guardada, no la que está a medio escribir). |
| El envío a n8n responde error o `HTTP 4xx/5xx` | Mira el código y el extracto de la respuesta del panel: 404 suele ser un workflow inactivo o una URL de prueba (`/webhook-test/`) que ya se usó; 5xx es un fallo dentro del workflow. Sin código HTTP (tiempo agotado o no se pudo conectar): revisa que la URL sea https y pública. |
| El botón abre el **número real** | Es un enlace que no pasa por el folio (checklist 10.6). No envíes; anota cuál es. |
| "Verificar sitio público" falla o aún carga GTM | Una caché (CDN, hosting, `php artisan view:cache` desactualizado) sirve una versión anterior de la página. Purga la caché y vuelve a verificar. |
| El `.env` cambió pero el sitio actúa como antes | Con `config:cache` activo el `.env` no se relee: `php artisan config:clear` (y `config:cache` si se usa). |
| El probador devuelve 404 o `count: 0` | El folio o la ventana no corresponden a una fila de prueba (el probador sin acceso a `ads` solo ve `is_test`); revisa el folio en "Eventos de prueba recientes" y que `from`/`to` lleven zona horaria y no pasen de 15 minutos. |
| Perdí mi atribución en ese navegador | Es esperado: activar y desactivar el modo prueba limpia `mdn_attr` y el `localStorage` de atribución de ese navegador (10.2). |

---

## Archivos relevantes

| Archivo | Para qué |
|---|---|
| `config/attribution.php` | Variables, formato del folio, plantillas de mensaje y asunto (es y pt), límites del endpoint |
| `app/Http/Middleware/AttributionApiTokenMiddleware.php` | Autenticación Bearer del endpoint (401/503) |
| `app/Models/TrackConversion.php` | Reglas de validación de click ids, tipo canónico, `$fillable` |
| `app/Http/Controllers/Backend/TrackConversionController.php` | Recibe `POST /track-conversion` |
| `public/frontend/js/attribution.js` | Captura y guarda la atribución (cookie y localStorage `mdn_attr`) |
| `public/frontend/js/mdn-contact.js` | Genera el folio y prepara los enlaces |
| `public/frontend/js/mdn-events.js` | Rastrea los enlaces sin la clase `track-conversion` |
| `resources/views/frontend/layouts/master.blade.php` | Listener de `a.track-conversion` (líneas 1034-1119) |
| `database/migrations/2026_10_19_100000_add_contact_folio_and_event_at_to_track_conversions_table.php` | Columnas `contact_folio` y `event_at` |
| `database/migrations/2026_10_20_100000_add_is_test_to_track_conversions_table.php` | Columna `is_test` (boolean, default 0) e índice `(is_test, id)`; requiere `php artisan migrate` |
| `app/Support/Attribution/AttributionLookupService.php` | Lógica del endpoint (folio y ventana) y `is_test` en la respuesta |
| `app/Support/Attribution/AttributionTestMode.php` | Cookie `mdn_attr_test`: validación, emisión, dominio y número de WhatsApp de prueba |
| `app/Support/Attribution/FolioExtractor.php` | Extracción del folio (regex exacta y tolerante) para el simulador del laboratorio |
| `app/Http/Controllers/Backend/AttributionLabController.php` | Laboratorio de atribución del admin (estado, eventos, probador, simulador, activar/desactivar, borrar pruebas) |
| `resources/views/admin-ui/attribution-lab/index.blade.php` y `public/admin-ui/js/attribution-lab/lab.js` | Pantalla y JavaScript del laboratorio |
| `resources/views/frontend/partials/analytics-tags.blade.php` | Etiquetas de analítica (GTM, GA4, Ads, Pixel); en modo prueba se sustituyen por funciones vacías |
| `app/Support/AdminTable/Queries/TrackConversionTableQuery.php` y `TrackConversionTestTableQuery.php` | Tabla del admin: la primera excluye `is_test`; la segunda lista solo pruebas |
| `routes/admin.php` | Rutas `attribution-lab/*` bajo `/admin` |
