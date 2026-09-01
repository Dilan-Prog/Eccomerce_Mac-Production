# Auditoría QA — Mac del Norte (2026-08-05 / 2026-08-26)

Notas de seguimiento de la auditoría funcional hecha en Chrome sobre
`eccomerce-mac-del-norte.test`. El reporte visual completo (con capturas y
evidencia) quedó publicado como Artifact aparte; este archivo es el registro
de qué se corrigió, qué quedó pendiente a propósito, y qué no se alcanzó a
probar.

## Auditoría de Cotizaciones + Carrito (2026-08-26) — CORREGIDO (2026-08-31)

Revisión de código completa (agente dedicado, leyó controllers/models/views
de ambos módulos línea por línea) + verificación manual en navegador con una
cuenta de prueba real. Todos los hallazgos de esta ronda ya se corrigieron,
se verificaron (vía navegador real y/o `tinker` directo contra la base de
datos), y quedaron limpios en el código actual.

### 🔴 Confirmado en vivo, no solo en código — ✅ Corregido

- **Cualquier cliente "Persona Moral (Empresa)" no podía generar una
  cotización — error fatal 500.** `app/Http/Requests/CotizacionRequest.php:21`
  tenía `$rfcSize = $tipo === 'fisica' ? 13 : a12;` — `a12` no era el número
  12, era una referencia a una constante de PHP inexistente. Corregido a
  `: 12;`. Verificado que el formulario ya no truena para Persona Moral.

### 🔴 Alta prioridad — ✅ Corregido

- **El carrito aceptaba cantidades negativas.** Se agregó validación
  explícita (`qty`/`quantity` entero ≥ 1, existencia de producto/combinación)
  en `CartController::addToCart()` y `updateProductQty()`.
- **El checkout nunca revalidaba stock antes de cobrar.** Se reescribió
  `PaymentController::storeOrder()` para resolver precio/stock en tiempo
  real (con `lockForUpdate()`) justo antes de descontar, en vez de confiar en
  el valor cacheado del carrito. Si el stock no alcanza para completar una
  línea, el pedido se crea de todos modos (el pago ya se cobró) pero queda
  marcado con el nuevo estado **"Pendiente de surtir"** en vez de sobrevender
  o tronar.
- **El stock que se descontaba no era el que se mostraba.** `storeOrder()`
  ahora descuenta `qty_aspel` o `qty` según corresponda (mismo criterio que
  `effectiveStock()`), y el stock de la combinación correcta cuando el
  renglón es una variante — antes, cualquier producto con variante hacía
  tronar el checkout por completo (bug encontrado durante esta corrección,
  no estaba en la lista original).
- **El precio cobrado era el de cuando se agregó al carrito, no el actual.**
  Se centralizó la resolución de precio/stock en vivo en una clase nueva,
  `App\Support\CartPricing`, usada tanto por los totales del carrito
  (`helpers.php`) como por el checkout (`PaymentController::storeOrder()`),
  para que ambos siempre coincidan con el precio real del momento del pago.
- **Se podía agregar al carrito o cotizar un producto desactivado.** Se
  agregó el filtro por `status` en ambos flujos.

### 🟡 Media prioridad — ✅ Corregido (salvo lo marcado como decisión de negocio)

- El piso de "precio mínimo" en cotizaciones para un producto sin SKU —
  **revisado y confirmado como decisión de negocio, no un bug**: cuando el
  precio es personalizado, se usa tal cual se escribió, sin piso artificial.
  No requiere cambio de código.
- El carrito multi-dispositivo (sobreescribe en vez de fusionar) — **fuera de
  alcance por decisión explícita**, se deja como está.
- "Cotizar" desde la ficha de producto ya usa `effectivePrice()` en vez de
  `$product->price` crudo, igual que el carrito.
- Ya no se puede generar una cotización de cliente vacía — se agregó la
  misma validación que ya tenía el builder de admin.
- Corregido junto con el punto anterior: un producto sin precio configurado
  ya no se vuelve gratis en silencio (`Product::hasEffectivePrice()`).

### 🟢 Baja prioridad — ✅ Corregido

- Posible duplicado de `sort_order` en cotizaciones si dos requests agregan
  un artículo al mismo tiempo — `AdminCotizacionController::storeItem()`
  ahora usa `lockForUpdate()` dentro de una transacción.
- Se encontró y corrigió una segunda condición de carrera del mismo tipo,
  fuera de la lista original: `resolveAspelClient()` podía crear un `User`
  duplicado si dos requests resolvían el mismo cliente Aspel sin vincular al
  mismo tiempo.
- `apply-coupon`/`coupon-calculation` ya son rutas `POST` (antes `GET`,
  saltaban la protección CSRF).
- El nombre de producto en el carrito ya se imprime escapado
  (`{{ $item->name }}` en vez de `{!! !!}`).

### Verificado manualmente en el navegador (funciona bien)

- El selector de cantidad +/- en la ficha de producto (tanto ahí como en la
  página del carrito) funciona correctamente y respeta el máximo de stock —
  **mi primera prueba automatizada dio un falso positivo** (usé una forma de
  llenar el campo que no dispara el JS real de sincronización); repetido con
  clicks reales, el flujo completo (ficha → carrito, cantidad y subtotal)
  funcionó perfecto.
- El stepper +/- dentro de la página del carrito actualiza cantidad y total
  correctamente vía AJAX.

## Pasarelas de pago — necesita tu intervención directa

Estado actual: **Stripe y PayPal siguen en modo LIVE** (`mode=1`) con sus
credenciales reales cargadas. **No se ha intentado ningún checkout de
prueba** — hacerlo así hubiera arriesgado un cargo real.

**Novedad (2026-08-31):** `/admin/payment-settings` ahora tiene un campo
separado para credenciales de **Sandbox** y de **Live** en cada pasarela,
más un selector "Modo Activo" que decide cuál se usa para cobrar de verdad —
ya no hay que borrar y volver a escribir las credenciales live cada vez que
se quiera probar en sandbox y viceversa. Antes de este cambio, el selector
de modo ni siquiera funcionaba: `PaymentController` leía siempre el mismo
campo sin importar el modo elegido.

Para probarlo juntos en modo seguro:
1. En `/admin/payment-settings`, pega tus credenciales de prueba en el
   bloque "Credenciales Sandbox" de cada pasarela (no las escribo yo — ni
   siquiera de prueba, por política: no debo manejar API keys/secrets). Se
   obtienen en:
   - Stripe: Dashboard → activa "Modo de prueba" (switch arriba a la
     derecha) → Developers → API keys → `pk_test_...` / `sk_test_...`.
   - PayPal: developer.paypal.com → Apps & Credentials → pestaña Sandbox →
     Client ID / Secret de tu app.
2. Cambia "Modo Activo" a **Sandbox** y guarda — tus credenciales live
   quedan intactas en su propio bloque, listas para cuando regreses el modo
   a Live.
3. Con eso cargado, puedo guiarte por un checkout de prueba completo (Stripe
   tiene tarjetas de prueba públicas como `4242 4242 4242 4242`; PayPal
   Sandbox usa una cuenta buyer de prueba que su Dashboard genera).

**Cuenta de prueba para QA del carrito/checkout** (creada 2026-08-31,
directamente en base de datos, cuenta personal ya activa):
- Correo: `qa.pruebas@macdelnorte.test`
- Contraseña: `QaPruebas2026!`

## Corregido en esta sesión

- **Prioridad alta — títulos duplicados en las 6 páginas de Servicios.**
  Las 6 vistas bajo el menú "Servicios" compartían literalmente el mismo
  `<title>` ("Instalacion de Controles de Temperatura"), verificado con
  `curl` sin caché de por medio. Cada una tiene ahora su propio título:
  - `resources/views/frontend/pages/videoregistradores.blade.php` → "Instalación de Videoregistradores"
  - `resources/views/frontend/pages/medidores-flujo.blade.php` → "Instalación de Medidores de Flujo"
  - `resources/views/frontend/pages/plc.blade.php` → "PLC — Configuración, Instalación y Llave en Mano"
  - `resources/views/frontend/pages/reparacion-videoregistradores.blade.php` → "Reparación de Videoregistradores"
  - `resources/views/frontend/pages/calibracion-ema.blade.php` → "Calibraciones EMA"
  - (`controles.blade.php`, el original, ya tenía el título correcto y no se tocó)

- **Prioridad alta — página 404 sin marca ni navegación.** No existía
  `resources/views/errors/404.blade.php`, así que Laravel mostraba su
  fallback genérico (fondo oscuro, sin header, sin forma de volver al
  sitio). Se creó una vista propia que extiende `frontend.layouts.master`
  (header, nav, buscador completos) con mensaje amable y botones a Inicio /
  Catálogo. Verificado que sigue devolviendo código HTTP 404 real.

- **Prioridad media — título incorrecto en Asociados y Revendedores.**
  `resources/views/frontend/pages/associate_page.blade.php` decía
  "Calibracion y Puesta en Marcha" (copiado de otra página). Corregido a
  "Asociados y Revendedores".

- **Prioridad media — años de experiencia inconsistentes.** Se encontraron
  6 menciones con 4 cifras distintas (8, "7+", "más de 20" y "más de 7")
  repartidas entre Inicio (2 lugares), Nosotros (2 lugares), Login (2
  lugares) y el meta-description del layout maestro. El cliente confirmó
  que la cifra real es **8 años** — las 6 menciones quedaron unificadas:
  - `resources/views/frontend/home/sections/banner-slider.blade.php` (ya decía 8, sin cambios)
  - `resources/views/frontend/home/sections/categories-why-us.blade.php` (2 menciones)
  - `resources/views/frontend/pages/about.blade.php` (2 menciones — se dejó intacta la bio de un empleado que menciona su propia experiencia personal, no el dato de la empresa)
  - `resources/views/auth/login.blade.php` (2 menciones)
  - `resources/views/frontend/layouts/master.blade.php` (meta-description)

- **Errores de consola JS sitewide — parcialmente corregido.**
  `ReferenceError: prefixesArray is not defined` (el más frecuente, ~8 por
  carga) venía del flag `autoA11y` del kit vendorizado de Font Awesome en
  `public/frontend/js/Font-Awesome.js` — se desactivó y se regeneró el
  build (`npm run build`). Verificado: ya no aparece.
  Queda **sin corregir** `ReferenceError: require is not defined`, que
  viene del wrapper UMD de `public/frontend/js/slick.min.js` (el carrusel)
  chocando con cómo Vite/Rollup empaqueta scripts estilo CommonJS. El
  carrusel sigue funcionando pese al error; arreglarlo de raíz requeriría
  tocar la configuración de build o parchear el vendor — no se tocó sin
  autorización explícita.

## Pendiente — necesita decisión antes de tocarse (alcance mayor al esperado)

- **Imagen principal de producto con dominio de producción absoluto.**
  Lo que parecía un dato suelto de un producto resultó ser el patrón de
  **828 de 833 productos (99.4% del catálogo)**: `thumb_image` se guarda
  como URL absoluta (`https://www.macdelnorte.com/uploads/...`) porque así
  es como `asset()` de Laravel genera rutas — usando el `APP_URL` vigente
  **en el momento en que se guardó** el producto (en este caso, el de
  producción). Las imágenes de galería, en cambio, se resuelven
  dinámicamente en cada carga, así que siempre siguen al dominio actual.
  Arreglarlo de fondo implica dos cosas separadas, no una línea:
  1. Cambiar la lógica de guardado de imágenes (`app/Traits/ImageUploadTrait.php`,
     compartida también por Marca/Slider/otros módulos) para guardar rutas
     relativas en vez de absolutas.
  2. Migrar los 828 registros ya existentes para quitarles el dominio fijo.

  No se tocó porque el alcance real (arquitectura + migración de datos en
  el 99% del catálogo) es mucho mayor al de "alinear un dato" — necesita
  confirmación explícita antes de proceder, idealmente coordinado con una
  ventana donde se pueda verificar que ningún flujo (PDF de cotización,
  feed de Google Merchant, etc. — todos leen `thumb_image` directamente) se
  rompa a medio camino.

## No se alcanzó a probar (fuera del alcance de esta pasada)

- Flujo completo de cotización de cliente (armar cotización desde el sitio
  público, generar y descargar el PDF final).
- Envío real de los formularios de **Contacto** y **Asociados y
  Revendedores** — se confirmó que cargan y se ven bien, no que el submit
  efectivamente guarde/notifique.
- Checkout / pago completo (Stripe y PayPal) — no se intentó por tratarse
  de una acción con efectos reales (cargos, pedidos).
- Vista responsive / mobile de las páginas principales.
- Enlaces del pie de página y páginas legales (Términos y Condiciones,
  Aviso de Privacidad, etc.).
- El recuadro flotante "Invalid domain" (iframe de `google.com`, visible en
  todas las páginas) y las imágenes rotas de las 6 páginas de Servicios —
  ambos con alta probabilidad de ser artefactos del dominio local `.test`,
  pero **no verificados directamente contra macdelnorte.com**.
