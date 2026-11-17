# Diagnóstico de integración: sitio web / API → n8n ↔ Kommo → Google Ads

Auditoría de **solo lectura** del repositorio Mac del Norte (commit `bfaa8957`, 2026-10-06). No se modificó nada, no se leyó `.env` (de las variables solo se citan nombres) y no se llamó a ninguna API externa. Las rutas y líneas salen de lectura estática y pueden moverse con cambios posteriores. Se verificaron por muestreo directo: el formulario de contacto sin envío, las rutas Artisan públicas, la ausencia de webhooks entrantes y el bug de `TrackConversionController`.

Flujo objetivo: sitio web + API propia → n8n; call tracking con número dinámico (DNI) → n8n; n8n ↔ Kommo; Outlook y WhatsApp Business conectados a Kommo; n8n → Google Ads Data Manager API.

## Resumen ejecutivo

1. El repo es un Laravel 10 / PHP 8 en cPanel con MySQL. Ya tiene una API para n8n (`/api/marketing/*`, autenticada con token propio), pero es **de salida de datos de marketing**: no tiene ningún endpoint ni tabla para **recibir leads**.
2. **Los leads se pierden o salen del sistema sin control.** El formulario de contacto y la cotización rápida de producto no envían nada; cuatro formularios más van a formsubmit.co hacia una cuenta de Gmail personal, sin base de datos ni atribución.
3. **No existe tabla de leads** con estado, origen ni id de CRM, ni deduplicación por teléfono. Los datos están repartidos en `users`, `cotizaciones` y `track_conversions`.
4. **La atribución es parcial:** solo `gclid` y 3 UTM en `localStorage`; no hay `gbraid`, `wbraid`, `fbclid`, referrer ni id de sesión, y nada viaja con el lead, la cotización o el pedido.
5. No hay call tracking ni número dinámico: el teléfono está en unos 15 archivos, con 3 números distintos y datos inconsistentes. WhatsApp usa un solo enlace corto sin mensaje ni parámetros de origen.
6. No hay Kommo, Zapier ni Google Ads API / Data Manager en el código. Las etiquetas de Ads, GA4, GTM y Meta Pixel existen, pero **sin banner de cookies ni Consent Mode**, y el aviso de privacidad tiene secciones pendientes.
7. **Riesgo de seguridad inmediato:** hay 5 rutas públicas sin autenticación que ejecutan comandos Artisan o sobrescriben imágenes (`routes/web.php:85,90,100,218,230`).
8. Casi no hay pruebas ni alertas, no hay staging, y los logs no cubren las integraciones. Para probar sin ensuciar el CRM real hay que construirlo.

## Tabla de estado

| Componente | Estado | Evidencia | Brecha |
|---|---|---|---|
| Stack y hosting | Implementado | `composer.json:8,14` (PHP ^8.1, Laravel ^10.10; `composer.lock:1876` v10.48.29); `.cpanel.yml:1-14` (cPanel, `ea-php83`); `config/database.php:18` (mysql); `package.json:13-16` (Vite 5) | `vendor/` versionado con paquetes de desarrollo (`composer.json:25,33`: debugbar, ignition) |
| Entornos dev/staging/prod | Parcial | `config/app.php:32` (`APP_ENV` default `production`); sin guardas `environment()` en `app/` ni `routes/` | No hay staging. El deploy no corre `migrate` ni pruebas (`.cpanel.yml`) |
| Formulario de contacto | Parcial (no envía) | `resources/views/frontend/pages/contact.blade.php:379` (`<form id="contact-form">` sin `action`, sin `name` en inputs; `contact-form` no aparece en ningún JS) | Los datos se pierden |
| Cotización rápida en producto | Parcial (no envía) | `product-detail.blade.php:739` hace POST a `route('contact')`, que solo es GET (`routes/web.php:36`) | Responde 405; los datos se pierden |
| Cotización pública, asociados, calibración, medición | Parcial | `pages/quotes.blade.php:11,106`; `associate_page.blade.php:74,100,105`; `calibracion-puesta.blade.php:71`; `medicion.blade.php:70` | POST a formsubmit.co (Gmail personal) con `_captcha=false`; `_next` apunta a `http://127.0.0.1:8000/cotizar`; "Empresa" y "Ciudad" comparten `name="Ciudad"`; sin DB, CSRF, atribución ni consentimiento |
| Cotización formal (autenticada) | Implementado | `cotizaciones/formulario.blade.php:195`; `Frontend/CotizacionController.php:68`; `CotizacionRequest.php:20`; `routes/web.php:119` | Exige login; sin correo al cliente ni atribución; sin id de CRM |
| Registro de usuario / revendedor B2B | Implementado | `auth/register.blade.php:115`; `RegisteredUserController.php:34,96,99` | `register-login.blade.php:67` omite campos que el controlador exige; el checkbox `newsletter` (`register.blade.php:431`) no se guarda; no hay verificación de correo (`User.php:5`) |
| Anti-spam | No existe (efectivo) | Sin honeypot ni throttle en formularios públicos; reCAPTCHA desactivado en `master.blade.php` (~1270, ~1445); sigue `recaptcha/api.js` (`master.blade.php:141`) y `ReCaptchaController` (`routes/web.php:73`) sin throttle | Cualquier endpoint de leads nuevo nace sin protección |
| Atribución | Parcial | `master.blade.php:143-174` (`gclid`, `utm_source/medium/campaign`, `landing_page` en `localStorage`); `registrarConversion()` `:1062-1092`; `TrackConversionController.php:62-80`; migración `2025_07_08_162704_create_track_conversions_table.php` | Faltan `gbraid`, `wbraid`, `fbclid`, `utm_term/content`, referrer, id de sesión, expiración; no se une a lead/cotización/pedido (`users`, `cotizaciones`, `orders` no tienen columnas) |
| API propia | Implementado | `routes/api.php:25-96`; `AspelApiTokenMiddleware.php:22-37`; `MarketingApiTokenMiddleware.php:62-77`; `RouteServiceProvider.php:27,39` | Sin scopes ni expiración de token; CORS `*` (`config/cors.php:22`); sin `Idempotency-Key`; `report` de secuencias no valida estado (`MarketingSequenceController.php:211-224`) |
| Idempotencia | Parcial | `updateOrCreate` en sync de Aspel (`AspelClientSyncController.php:61`, etc.); `claim` atómico (`MarketingCampaignController.php:117`) | `POST /track-conversion` siempre inserta (`TrackConversionController.php:77`) |
| Webhooks entrantes | No existe | Busqué `webhook`, `constructEvent`, `hash_hmac` en `routes/` y `app/`; solo hay columnas de configuración (`StripeSetting.php:20-23,45`, `PaypalSetting.php:20-23,45`) | No hay ruta ni verificación de firma para n8n, Kommo, Stripe ni PayPal |
| n8n | Parcial | API de marketing consumida por n8n (`routes/api.php:52-96`); `MarketingDataController.php:39-493` | Solo lectura de datos y marcado de envíos; no recibe leads ni eventos |
| Kommo / amoCRM / Zapier / Make / HubSpot | No existe | grep sin resultados en `app`, `config`, `routes`, `resources`, `database` | Todo por construir |
| Google Sheets (Apps Script) | Implementado | URL de `script.google.com` en `master.blade.php:148,1062` (y duplicadas en bloques comentados `:1339,1456`) | Envío `no-cors` sin respuesta verificable; sin reintentos |
| WhatsApp | Implementado | Un solo `wa.link/f28njw` en 30 lugares (20 archivos); `wa.me/528135825559` en `contact.blade.php:452`; `config/brasil.php:20` | Sin `?text=` ni parámetro de origen; ~10 enlaces sin `track-conversion` (`banner-slider.blade.php:36`, `category-product-slider-*`, `catalogo.blade.php:48`, etc.); listener duplicado en `whastapp-chat.blade.php:31-70`; llamadas a `/analytics/beacon` y `/analytics/track` que no existen |
| Teléfonos | Parcial | 81-2473-8768 (`footer.blade.php:87-91`, `contact.blade.php:461`, ~15 archivos); 81-3582-5559 (`top-bar.blade.php:10`); 81-2473-8744 (`footer.blade.php:110-114`); `$phone` definido en 3 layouts de servicios (`service-layout.blade.php:37`, `category-detail-layout.blade.php:30`, `category-hub-layout.blade.php:26`) | Sin componente central; placeholders `+52 (81) 0000-0000` (`categorias.blade.php:28`); en `product-detail.blade.php:675-679,728-730,775-777` el texto dice 81-3582-5559 pero el `tel:` llama a 81-2473-8768 |
| Call tracking / DNI | No existe | Busqué CallRail, WhatConverts, CallTrackingMetrics, Twilio en `resources`, `public/frontend/js`, `app`, `config` | Solo se registra el clic en `tel:` (`data-type` `telefono_*`) |
| Correo saliente | Parcial | `config/mail.php:16` (SMTP); `PaymentController.php:352,518,696,756-760`; `StaffUserController.php:205`; `EmailTemplateController.php` (envío de prueba) | Cola `sync` (`config/queue.php:16`); `PaymentConfirmation`/`SendPaymentConfirmation` nunca se disparan; sin correo al cliente en cotizaciones; el correo de alta de personal manda la contraseña en claro |
| Outlook | No existe | Sin referencias a Outlook/Microsoft Graph en el repo | El correo actual sale por SMTP del hosting (nombres `MAIL_*`); proveedor real no se pudo determinar |
| Google Ads / GA4 / GTM / Meta Pixel | Implementado | Ads `AW-16512201966` (`master.blade.php:930-935`), GTM (`:940-944,978`), GA4 (`:948-953`), Meta Pixel (`:958-973`); conversión de compra `payment-success.blade.php:335` | Etiquetas duplicadas (riesgo de doble conteo); `payment-transfer-success.blade.php:302` solo envía `page_view`; layouts admin/asociado con `UA-94034622-3` obsoleto |
| Google Ads API / Data Manager / conversiones offline / Meta CAPI | No existe | grep en `app`, `config`, `routes`, `composer.json` | El `gclid` se guarda solo en `track_conversions`, sin vínculo con lead o venta |
| Consent Mode y banner de cookies | No existe | grep de `gtag('consent')`, CMP y cookie de consentimiento sin resultados; etiquetas cargan en `master.blade.php:930-968` | Se rastrea sin consentimiento previo |
| Aviso de privacidad | Parcial | `routes/web.php:59`; `pages/aviso-privacidad.blade.php:86,99` (ARCO; retención 5 años); `:104-105` ("Texto pendiente", pendiente de revisión legal) | No menciona transferencia a terceros (formsubmit.co, n8n, Kommo); sección de cookies incompleta |
| Consentimiento en formularios | Parcial | `register.blade.php:419,431` (enlaces `href="#"`, el controlador no valida `terms`); `contact.blade.php:~424` | Resto de formularios sin casilla; sin columna de fecha/aceptación (`consent`, `opt_in` no existen en migraciones) |
| Modelo de leads | No existe | Busqué en `database/migrations` y `app/Models`: no hay `leads`. Datos en `users` (`role`, `phone`, `company`, `rfc`; email y rfc únicos), `cotizaciones` (`source`, `status`), `aspel_clients` (`clave` única), `track_conversions` | Sin estados de lead, sin origen/atribución, sin id externo de CRM, sin deduplicación por teléfono; usuarios del cotizador con correo ficticio `pendiente-…@sin-correo.macdelnorte.com` (`User.php:133-140`) |
| Secretos en el repo | Implementado (sin hallazgos graves) | `.env` ignorado; sin llaves `sk_live`/`whsec_`/`AKIA`/`AIza` en archivos rastreados; credenciales de pago en BD (`StripeSetting.php:21-23`) | Confirmar que estén cifradas en esas columnas (no se pudo determinar) |
| CORS / CSRF / cabeceras | Parcial | `config/cors.php:22` (`*`); `VerifyCsrfToken.php:15-16`; sin CSP/HSTS/X-Frame-Options en `app/`, `.htaccess` | CORS abierto; sin cabeceras de seguridad |
| Rutas públicas peligrosas | Implementado (riesgo) | `routes/web.php:85,90,100,218,230`: ejecutan Artisan (`products:discount-dc1200`, `images:convert-png`, `storage:link`) o sobrescriben imágenes (`/waterimage`), sin autenticación | Cualquiera con la URL puede dispararlas |
| Rate limit | Parcial | `RouteServiceProvider.php:27-41`; `LoginRequest.php:62`; `routes/auth.php:49,53` | Nada en `routes/web.php`; endpoints de pago y `track-conversion` sin límite |
| Logs y alertas | Parcial | `config/logging.php:13,29` (solo `single`); ~35 `Log::` en `app/`; `PaymentController.php:380` vuelca `session()->all()` | Sin Sentry/Telescope/alertas; los endpoints de marketing no registran nada; posible fuga de datos en logs |
| Pruebas automatizadas | Parcial | 8 archivos en `tests/` (Breeze y `ImageUploadTraitUpdateImageTest`); `phpunit.xml:24` (sqlite en memoria) | Sin pruebas de API, pagos ni formularios |
| Entorno de prueba para integraciones | Parcial | Stripe/PayPal con `mode` sandbox/live (`StripeSetting.php:27-45`, `PaymentController.php:257`) | El toggle vive en BD (un clon de producción heredaría `mode=1`); no hay sandbox de Aspel ni de CRM |

## Brechas priorizadas

### P0 — bloquean el flujo o exponen riesgo crítico

1. **No hay un punto único y confiable de captura de leads.** Contacto y cotización de producto no envían (`contact.blade.php:379`, `product-detail.blade.php:739`); otros cuatro dependen de formsubmit.co sin copia en base de datos. Hace falta un endpoint propio (con CSRF o token, validación en servidor, throttle y anti-spam) que persista el lead y lo reenvíe a n8n.
2. **No existe el modelo de lead.** Se necesita una tabla con: estado, origen/canal, campos de atribución, consentimiento y fecha, id externo de Kommo, y llave de deduplicación (teléfono normalizado y/o correo). Sin el id externo no hay bidireccionalidad con Kommo.
3. **Consentimiento y aviso de privacidad antes de enviar datos personales a terceros** (Kommo, n8n, Google). Hoy no hay banner, ni Consent Mode, ni registro de aceptación, y el aviso tiene secciones pendientes y no nombra a estos terceros. Debe validarlo la persona responsable de lo legal.
4. **Rutas Artisan públicas** (`routes/web.php:85,90,100,218,230`). Hay que retirarlas o protegerlas antes de abrir más superficie hacia el exterior. `/waterimage` sobrescribe imágenes del catálogo.

### P1 — necesarias para atribución y cierre de ciclo

5. **Completar la captura de atribución:** `gclid`, `gbraid`, `wbraid`, `fbclid`, `utm_term`, `utm_content`, referrer, landing page de primer toque, id de sesión y fecha/hora de captura, con expiración; y adjuntarlos a **cada** formulario, clic de WhatsApp y llamada. Hoy solo se guardan 4 valores en `localStorage` (`master.blade.php:150-157`).
6. **Unir la atribución al lead, la cotización y el pedido.** `track_conversions` no tiene `email`, `user_id` ni índices, y `cotizaciones`/`orders`/`users` no guardan `gclid` ni UTM. Sin esto no se pueden subir conversiones a Google Ads.
7. **Conversiones hacia Google Ads Data Manager API:** no hay código, tabla de eventos de conversión (con estado enviado/error), ni normalización y hash de correo y teléfono. Se necesita definir qué evento es la conversión (lead, lead calificado en Kommo, venta).
8. **Call tracking / DNI:** elegir proveedor, centralizar el número en un componente único (hoy ~15 archivos y 3 números) y corregir la inconsistencia texto/`tel:` de `product-detail.blade.php`.
9. **WhatsApp:** hoy un solo `wa.link` sin mensaje precargado ni código de origen. Para que Kommo identifique la fuente hace falta incluir un identificador de origen en el texto o en el enlace, y medir los ~10 puntos de WhatsApp que no pasan por `track-conversion`.
10. **Ambiente de pruebas sin ensuciar el CRM real:** n8n con credenciales y pipeline de Kommo de pruebas, bandera de entorno en el sitio y un `Idempotency-Key` o campo de `lead_uuid` para reintentos seguros.
11. **Autenticación de la API hacia n8n:** reutilizar el esquema de token con hash, pero agregar expiración y alcance, y registrar cada llamada. Hay antecedente operativo (fuera del repo): durante las pruebas el hosting bloqueó la IP de salida de n8n por una ráfaga de peticiones, así que conviene diseñar el envío en lotes y con límites.
12. **Excluir usuarios con correo ficticio** (`pendiente-…@sin-correo.macdelnorte.com`) y clientes sin correo de cualquier sincronización a Kommo.

### P2 — mejoras

13. Corregir `TrackConversionController.php:78-85` (lee claves opcionales sin validar que existan: puede responder 500 tras crear la fila), añadir throttle y deduplicación.
14. Eliminar doble conteo: etiquetas `gtag`/GTM/GA4 duplicadas y eventos de `dataLayer` repetidos (`master.blade.php:1117,1298,1326`); retirar `UA-94034622-3`; corregir `payment-transfer-success.blade.php:302`.
15. Webhooks entrantes de Stripe/PayPal (hoy la confirmación depende del redirect; no hay reembolsos ni disputas) y, si se desea, endpoint entrante para estados desde n8n con verificación de firma.
16. CORS restringido, cabeceras de seguridad, `TrustProxies`, saneamiento de las 12 vistas con `{!!`.
17. Logs con canal `daily`, alertas (Sentry o similar) y pruebas automatizadas de los nuevos endpoints; no volcar `session()->all()` (`PaymentController.php:380`).
18. Correos: cola real en lugar de `sync`, activar `PaymentConfirmation`, correo de acuse al cliente de cotización, baja pública de listas, y no enviar contraseñas en claro (`StaffUserController.php:205`).
19. Higiene del repo: `vendor/` versionado, `.env.example` sin variables de integración, `_next` de formsubmit apuntando a localhost, `name` duplicado `Ciudad`.

## Preguntas abiertas para el equipo

**Kommo**
- ¿Qué plan de Kommo se contrató y cuáles son sus límites de API y de webhooks?
- ¿Se usará el canal nativo de WhatsApp de Kommo (WhatsApp Business API) o un proveedor intermedio? ¿Qué número se migrará? Un número que pasa a la API deja de funcionar en la app de WhatsApp Business.
- ¿Cómo se quiere el pipeline: etapas, campos personalizados para `gclid`/UTM, y quién es dueño de la deduplicación (n8n o Kommo)?

**n8n**
- ¿Dónde está alojado (hoy en Easypanel, según el historial de trabajo, no hay constancia en el repo) y con qué IP de salida? ¿Habrá instancia separada de pruebas?
- ¿Se conservará `$getWorkflowStaticData` o todo el estado vivirá en la base del sitio (ya se vio que no persiste de forma fiable)?

**Google Ads**
- ¿Quién administra la cuenta (`AW-16512201966` está en el sitio), existe MCC, y qué acciones de conversión deben alimentarse?
- ¿Qué evento cuenta como conversión (lead, lead calificado, venta) y con qué ventana de tiempo? ¿Quién provee developer token y credenciales OAuth?

**WhatsApp y teléfono**
- ¿Cuál es el número oficial de WhatsApp detrás de `wa.link/f28njw`, y quién administra ese enlace corto?
- ¿Qué proveedor de call tracking se usará (CallRail, CallTrackingMetrics, Twilio, otro) y cuántos números dinámicos? ¿Cuál es el número principal oficial (hoy conviven 81-2473-8768, 81-3582-5559 y 81-2473-8744)?

**Correo**
- ¿Cuál es el sistema de correo canónico: el SMTP actual del hosting (`mail.macdelnorte.com`) o Outlook / Microsoft 365? ¿Qué buzón se conectará a Kommo?

**Legal y datos**
- ¿Quién aprueba el aviso de privacidad y el texto de cookies pendiente? ¿Qué plazo de retención aplica a leads y a `track_conversions`?

## Qué se puede reutilizar

- **Esquema de tokens para n8n:** `MarketingApiTokenMiddleware.php`, `MarketingApiToken`, `MarketingApiTokenController.php` y su pantalla admin (rutas y vistas `marketing-tokens`) sirven de modelo para un token de ingesta de leads.
- **Patrón de API orientada a n8n:** `MarketingCampaignController` y `MarketingSequenceController` (`due` → `claim` atómico → `render` → `report`) y los limitadores nombrados de `RouteServiceProvider.php:27-41` (agregar uno `leads`).
- **Captura de atribución existente:** el script de `master.blade.php:143-174` y `registrarConversion()` (`:1062-1092`), más `TrackConversion` y su migración, como base a ampliar.
- **Convención de eventos `data-type`** de los enlaces `track-conversion` (WhatsApp, teléfono, correo, por página y por SKU en Brasil).
- **Cotización formal** (`CotizacionRequest.php:20`, `cotizacion_perfiles`) como fuente de leads ya validada, y `aspel_clients.clave` como llave externa estable para clientes de Aspel.
- **Listas y bajas de correo:** `email_contact_list_members` (único por lista y correo, `unsubscribed_at`) para respetar bajas al sincronizar.
- **Configuración centralizada:** `config/brasil.php` (teléfono y WhatsApp en un solo lugar) es el modelo para un `config/contacto.php`; los layouts de servicios ya definen `$phone`/`$whatsapp` en un solo bloque.
- **Panel admin:** las recetas de módulos de `Slider` y Email Marketing (controlador + `AdminTable\Queries` + vistas `admin-ui` + sidebar) sirven para una pantalla de Leads; el envío de prueba de plantillas (`EmailTemplateController::sendTest`) para probar correos.
- **Sandbox de pagos** (`StripeSetting.php:27-45`) como referencia de bandera de entorno, aunque conviene moverla a `.env`.

## Alcance y limitaciones

- No se leyó `.env`: **no se pudo determinar** `APP_ENV`/`APP_DEBUG` reales, el proveedor SMTP real, ni si las credenciales de pago están cifradas en base de datos.
- No se ejecutó código ni se probó ningún endpoint; todo es lectura estática.
- Se ignoraron las copias del repo en `.claude/worktrees/`.
- Cuentas y contratos de Kommo, Google Ads, n8n y WhatsApp quedan fuera de lo observable en el repositorio.
