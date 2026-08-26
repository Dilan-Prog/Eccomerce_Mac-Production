# Auditoría QA — Mac del Norte (2026-08-05 / 2026-08-25)

Notas de seguimiento de la auditoría funcional hecha en Chrome sobre
`eccomerce-mac-del-norte.test`. El reporte visual completo (con capturas y
evidencia) quedó publicado como Artifact aparte; este archivo es el registro
de qué se corrigió, qué quedó pendiente a propósito, y qué no se alcanzó a
probar.

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

## Dejado a propósito sin tocar (declinado explícitamente antes)

- **Años de experiencia inconsistentes** (Inicio dice "8 años", Nosotros
  "7+ años", Login "más de 20 años", y el meta-description del layout
  maestro dice "más de 7 años" — un **cuarto** valor distinto encontrado de
  paso). El usuario pidió explícitamente no tocar este texto. Falta que el
  negocio confirme la cifra real antes de que alguien lo alinee en las 4
  ubicaciones.
- **Errores de consola JS sitewide** (`prefixesArray is not defined`,
  `require is not defined`, ambos originados en el loader vendorizado de
  Font Awesome Kit en `public/frontend/js/Font-Awesome.js`, flag
  `autoA11y`). El usuario pidió explícitamente no tocarlo. No rompe
  funcionalidad visible, solo ensucia la consola.

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
