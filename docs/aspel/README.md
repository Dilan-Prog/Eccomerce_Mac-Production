# Script de sincronización Aspel → macdelnorte.com (v2)

`AspelSyncConexion.v2.php` lee Aspel SAE (Firebird) y manda al sitio:

| Qué | Origen en Aspel | Ruta del sitio |
|---|---|---|
| Inventario | `INVE01` + campo libre MODELO (`INVE_CLIB01.CAMPLIB3`) | `POST /api/aspel/sync` |
| Precios por producto | `PRECIO_X_PROD01` | `POST /api/aspel/precio-x-producto` |

Los datos llegan a la tabla `aspel_products` del sitio; después, cada hora, las tareas `app:sync-aspel-qty` y `app:sync-aspel-prices` (cron `schedule:run`) los copian a los productos de la tienda.

## Qué corrige respecto a la versión anterior

- Manda el **token** (`Authorization: Bearer`) en cada petición.
- Si `INVE01` devuelve **0 filas**, lo marca como **ERROR** (antes decía "Inventario OK — 0 productos en 0 lotes").
- Un lote que falla **no detiene a los demás**: se reintenta (3 veces) y se sigue; al final lista los lotes con error.
- Ajusta los datos a lo que acepta el sitio (textos largos, fechas, números, JSON, 1 carácter) para que un producto raro no se rechace.
- Lee la respuesta del sitio (`OK` / `PARTIAL`, guardados, rechazados), no solo el código HTTP.
- Lotes de 100 productos con pausa de 300 ms entre peticiones (evita ráfagas que el hosting pueda bloquear).
- No sigue redirecciones (convertirían el `POST` en `GET` y se perdería el token): avisa la URL final.
- Escribe `AspelSync.log` (junto al script) y termina con **código 1** si algo falló, para que el Programador de tareas de Windows lo muestre como fallido.

## Instalación

1. Copia `AspelSyncConexion.v2.php` al equipo de Aspel, junto a tu script actual (no lo sobrescribas todavía).
2. Abre el archivo y llena el bloque `CONFIGURACION`:
   - `fb_database`, `fb_user`, `fb_password`: los mismos de tu script actual.
   - `api_token`: el token de Aspel, formato `key_id.secret` (panel de administración → Integración (Aspel) → crear o regenerar). **No lo pegues en chats ni en el repositorio.**
   - También puedes usar las variables de entorno `ASPEL_FB_PASSWORD`, `ASPEL_API_TOKEN`, `ASPEL_API_BASE`, `ASPEL_LOG_FILE`.
3. Prueba **sin enviar nada**:

```
php AspelSyncConexion.v2.php --dry-run
```

   Debe decir cuántos productos y precios se enviarían. Si dice `ERROR … devolvió 0 productos`, el problema está en la base de Aspel (empresa, archivo `.fdb`, permisos), no en el sitio.
4. Envía de verdad:

```
php AspelSyncConexion.v2.php
```

5. Cuando funcione, cambia la tarea programada para que ejecute este archivo.

## Cómo leer el resultado

```
[INV]    OK    enviados 2832/2832 en 29 lotes; guardados por el sitio: 2832
[PRECIO] OK    enviados 9000/9000 en 30 lotes; guardados por el sitio: 8700; con aviso del sitio: 300
[PRECIO]    · lote 4: Item 12 (CVE_ART: AL1-5002, CVE_PRECIO: 1): Producto con cve_art 'AL1-5002' no existe
```

- `RECHAZADOS por datos inválidos`: el sitio rechazó ese registro (los demás del lote sí se guardaron). El mensaje dice cuál y por qué.
- `con aviso del sitio`: el registro llegó pero no se pudo aplicar (por ejemplo, precio de un producto que aún no está en `aspel_products`). Suele resolverse cuando el inventario se sincroniza.
- `LOTES CON ERROR`: el lote no llegó (red, token, servidor). Revisa el mensaje y `AspelSync.log`.

Opciones: `--dry-run`, `--only=inv`, `--only=precios`. Desde el navegador: `…/AspelSyncConexion.v2.php?sync=1` (texto) o sin parámetros (resumen en pantalla; la vista con tablas del script anterior ya no está).

## Cambio relacionado en el sitio

`POST /api/aspel/sync` y `/api/aspel/precio-x-producto` ahora validan **registro por registro**: un registro inválido se rechaza y se reporta en `errors` (con `rejected` en la respuesta), pero los demás del lote se guardan. Si **todos** los registros del lote son inválidos responde `422`, igual que antes.
