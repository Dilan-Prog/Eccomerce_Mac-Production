# Solicitudes web (formularios públicos)

Estado: implementado el 2026-11-06. Reemplaza el envío directo a `formsubmit.co`.

## Por qué

Cuatro formularios públicos enviaban los datos **desde el navegador del cliente** a `https://formsubmit.co/<gmail>`:

| Formulario | URL | Vista |
|---|---|---|
| Asociados ("Quiero unirme") | `/associate` | `resources/views/frontend/pages/associate_page.blade.php` |
| Calibración y puesta en marcha | `/calibracion-puesta` | `resources/views/frontend/pages/calibracion-puesta.blade.php` |
| Medición | `/medicion` | `resources/views/frontend/pages/medicion.blade.php` |
| Cotización pública | `/price` | `resources/views/frontend/pages/quotes.blade.php` |

Problemas detectados el 2026-11-06, cuando `formsubmit.co` cayó (error 522 de Cloudflare):

- Si el servicio externo falla, el cliente ve su pantalla de error con el correo del dueño en la barra de direcciones y **el mensaje se pierde** (no había copia en la base de datos).
- El correo del dueño estaba visible en el HTML.
- `_next` apuntaba a `http://127.0.0.1:8000/cotizar` (el cliente terminaba en una dirección local inexistente).
- `_captcha=false` (sin anti-spam) y dos campos con `name="Ciudad"` que se pisaban.

Nada de esto tiene relación con los botones de WhatsApp (folio `MN…`), con n8n/Kommo ni con la cotización formal con sesión (`/cotizacion/*`).

## Cómo funciona ahora

1. El formulario envía `POST /solicitudes-web` (ruta `web-leads.store`) a este mismo sitio, con CSRF.
2. **Se guarda primero** en la tabla `web_leads` (aunque falle todo lo demás, la solicitud queda a salvo).
3. Después se avisa, de forma independiente:
   - **Correo** a los destinatarios configurados en el panel (Solicitudes web → "Correos que reciben el aviso"). Si ahí no hay ninguno, a `WEB_LEADS_NOTIFY_TO` del `.env` y, si tampoco, a `MAIL_ADMIN_ORDER_ADDRESS`.
   - **Webhook** opcional (n8n) a `WEB_LEADS_WEBHOOK_URL`.
4. Si un aviso falla, la solicitud sigue guardada y queda marcada (`notify_error` / `webhook_error`); se puede **reenviar desde el panel**.
5. El cliente ve "¡Recibimos tu solicitud!" en la misma página.

Cada solicitud guarda también el origen y la atribución (página, landing, referrer, `session_id`, gclid/gbraid/wbraid/fbclid y UTM), igual que los eventos de `track_conversions`. No guarda IP ni user-agent.

### Anti-spam
- Campo trampa `website` (si viene con valor se responde "éxito" sin guardar ni avisar).
- Tiempo mínimo entre cargar la página y enviar (`min_fill_seconds`, 3 s).
- Límite de envíos por IP (`throttle:web-leads`, 10 por minuto por IP).
- CSRF activo.

## Variables de entorno

| Variable | Qué hace |
|---|---|
| `WEB_LEADS_NOTIFY_TO` | Respaldo de los correos que reciben el aviso, separados por comas. **Lo normal es configurarlos desde el panel** (tienen prioridad sobre esta variable). Si faltan los dos usa `MAIL_ADMIN_ORDER_ADDRESS`. Sin destino no se envía correo (la solicitud igual queda en el panel). |
| `WEB_LEADS_WEBHOOK_URL` | Webhook (n8n). Vacío = apagado. |
| `WEB_LEADS_WEBHOOK_TOKEN` | Opcional; se envía como `Authorization: Bearer …`. |

Configuración en `config/webleads.php` (los controladores leen `config()`, nunca `env()`).

## Panel de administración

**Administrar Sitio Web → Solicitudes web** (`/admin/web-leads`, permiso del módulo `site`): listado con pestañas (Todas / Nuevas / Contactadas / Descartadas), búsqueda, detalle completo, cambio de estado, reenvío del aviso y borrado.

## Archivos

- Migraciones: `database/migrations/2026_11_06_100000_create_web_leads_table.php` y `2026_11_06_110000_create_web_lead_settings_table.php` (ajustes editables desde el panel; modelo `app/Models/WebLeadSetting.php`)
- Modelo: `app/Models/WebLead.php`; config: `config/webleads.php`
- Público: `app/Http/Controllers/Frontend/WebLeadController.php`, `app/Http/Requests/StoreWebLeadRequest.php`, `app/Support/WebLeads/WebLeadNotifier.php`, `app/Mail/WebLeadReceivedMail.php`
- Vistas y JS públicos: parciales `frontend/partials/web-lead-fields.blade.php` y `web-lead-alert.blade.php`, `public/frontend/js/web-lead-form.js`
- Admin: `app/Http/Controllers/Backend/WebLeadController.php`, `app/Support/AdminTable/Queries/WebLeadTableQuery.php`, `resources/views/admin-ui/web-leads/`
- Pruebas: `WebLeadStoreTest`, `AdminWebLeadTest`, `WebLeadFormsViewTest`

## Puesta en producción

1. Desplegar y correr `php artisan migrate` (crea `web_leads`).
2. Entrar a **Administrar Sitio Web → Solicitudes web** y poner en "Correos que reciben el aviso" el correo (o varios, separados por comas), por ejemplo `contacto@macdelnorte.com`, y Guardar. (Alternativa: `WEB_LEADS_NOTIFY_TO` en el `.env`.) Si se quiere webhook: `WEB_LEADS_WEBHOOK_URL`. Verificar que el correo del servidor (`MAIL_*`) esté configurado y que `MAIL_FROM_ADDRESS` sea una cuenta del dominio (con SPF/DKIM).
3. `php artisan config:clear` (y `config:cache` si se usa) y purgar cachés de páginas/CDN si las hay.
4. Probar cada formulario con un envío real y comprobar: fila en el panel, correo recibido y, si aplica, el webhook.
