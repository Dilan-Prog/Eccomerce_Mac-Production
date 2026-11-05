# Sincronización Aspel → macdelnorte.com

Estado al **2026-11-05**: el inventario vuelve a sincronizarse. Hay dos scripts en esta carpeta; **el que corre en producción es `AspelSyncConexion.php`**.

| Archivo | Qué es | Estado |
|---|---|---|
| `AspelSyncConexion.php` | Script de producción (el que ejecuta `run_aspel_sync.bat` en el equipo de Aspel, cada hora). Hace inventario, precios, clientes, monedas, ventas y partidas. | **En uso** |
| `AspelSyncConexion.v2.php` | Reescritura solo de inventario + precios (con reintentos, avance en pantalla, `--dry-run`). | Respaldo / referencia. No sincroniza clientes, monedas, ventas ni partidas, por eso **no** reemplaza al de producción. |

Los datos llegan a las tablas `aspel_*` del sitio; cada hora las tareas `app:sync-aspel-qty` y `app:sync-aspel-prices` (cron `schedule:run` en el servidor) los copian a los productos de la tienda.

## Qué se corrigió el 2026-11-05 (inventario parado desde el 2026-08-28)

**Síntoma:** el log de cada hora decía `[INV] OK Inventario OK — sin cambios desde …` y `aspel_products` no se actualizaba desde el 28 de agosto. Precios, clientes, monedas y ventas sí llegaban.

**Causa:** el inventario se enviaba "por delta" con `WHERE VERSION_SINC > checkpoint`, y el checkpoint guardaba la hora de la corrida tomada de PHP, que estaba en **UTC**. Aspel guarda `VERSION_SINC` en **hora local de México (UTC−6)**. El checkpoint quedaba siempre 6 horas en el futuro respecto a cualquier cambio, así que la condición nunca se cumplía. Además, nada garantiza que Aspel actualice `VERSION_SINC` cuando solo cambia la existencia (`EXIST`).

**Arreglo (en `AspelSyncConexion.php`):**
- El inventario **ya no depende de `VERSION_SINC` ni de la hora**. Se lee `INVE01` completo y la función `sincronizarInventario()` compara el **contenido** de cada producto (huella `md5` de los campos que se envían) contra lo ya enviado, guardado en `aspel_inventory_hashes.json` (junto al script). Solo se envían los productos que cambiaron (existencia, costo, descripción, etc.).
- Se envía **todo** (modo "completo"): la primera vez, si falta el archivo de huellas, y cada 24 horas como resincronización de seguridad.
- Un lote que falla no detiene a los demás (se detiene tras 3 fallos seguidos o en 401/403). Los productos de un lote fallido o rechazados por el sitio **no se marcan como enviados**: se reintentan en la corrida siguiente.
- `date_default_timezone_set('America/Mexico_City')` al inicio: el log y las marcas coinciden con el reloj de Windows.
- El checkpoint de ventas/partidas (`aspel_sync_checkpoint.json`) sigue igual: guarda el `VERSION_SINC` más alto realmente enviado, en la misma hora de Aspel, así que no depende de la zona horaria de PHP.
- Se quitó el `Warning: Cannot modify header information` en consola: `header()` solo se llama en el navegador.

**Resultado verificado el 2026-11-04 23:38 (hora local):** `[INV] OK Inventario OK (completo) — 2846 productos enviados de 2844 …`; precios (7911), clientes (719), monedas (9), ventas y partidas en OK.

**Nota:** `2846 enviados de 2844`: `INVE01` tiene 2 productos cuya clave `CVE_ART` coincide con otra salvo por espacios o mayúsculas; el sitio guarda una sola fila por clave. Esos 2 se reenvían cada hora (no causa daño).

### Cómo leer el log de producción

El `.bat` escribe la salida en `C:\laragon\www\logs\aspel_sync.log`:

```
[INV]     OK    Inventario OK — sin cambios (2844 productos revisados)        <- nada cambió
[INV]     OK    Inventario OK (solo cambios) — 3 productos enviados de 2844 …  <- cambios normales
[INV]     OK    Inventario OK (completo) — 2846 productos enviados de 2844 …   <- 1ª vez o cada 24 h
[INV]     ERROR Inventario ERROR … fallaron N de M lotes: <motivo>             <- revisar red/token
```

### Instalación / actualización del script de producción

1. Copia `AspelSyncConexion.php` al equipo de Aspel, a `C:\laragon\www\AspelSyncScript\` (el `.bat` ya apunta ahí).
2. Rellena **`$password`** (Firebird) y **`$aspelApiToken`** (formato `key_id.secret`, panel → Integración (Aspel)). Vienen vacíos en el repositorio a propósito: **no los pegues en chats ni los subas al repositorio.**
3. Prueba a mano: `php AspelSyncConexion.php`. La primera corrida es completa; la segunda debe decir `sin cambios`.
4. Verifica en el servidor del sitio: `php artisan tinker --execute="echo \App\Models\AspelSync::max('updated_at');"` (fecha de hoy) y que `crontab -l` tenga una línea con `schedule:run`.

Para forzar un reenvío completo: borra `aspel_inventory_hashes.json`.

---

## Script v2 (referencia): `AspelSyncConexion.v2.php`

Lee Aspel SAE (Firebird) y manda inventario y precios:

| Qué | Origen en Aspel | Ruta del sitio |
|---|---|---|
| Inventario | `INVE01` + campo libre MODELO (`INVE_CLIB01.CAMPLIB3`) | `POST /api/aspel/sync` |
| Precios por producto | `PRECIO_X_PROD01` | `POST /api/aspel/precio-x-producto` |

### Qué ofrece respecto al script anterior

- Manda el **token** (`Authorization: Bearer`) en cada petición.
- Si `INVE01` devuelve **0 filas**, lo marca como **ERROR** (antes decía "Inventario OK — 0 productos en 0 lotes").
- Un lote que falla **no detiene a los demás**: se reintenta (3 veces) y se sigue; con 3 lotes seguidos fallando se detiene (evita esperar horas si el sitio no responde) y lista los lotes con error.
- Muestra **avance en pantalla** (`Conectando a Firebird…`, `Inventario lote 3/29 OK`).
- Ajusta los datos a lo que acepta el sitio (textos largos, fechas, números, JSON, 1 carácter).
- Lee la respuesta del sitio (`OK` / `PARTIAL`, guardados, rechazados), no solo el código HTTP.
- Lotes de 100 productos con pausa de 300 ms entre peticiones.
- No sigue redirecciones (convertirían el `POST` en `GET` y se perdería el token).
- Escribe `AspelSync.log` (junto al script) y termina con **código 1** si algo falló.
- Envía el inventario **completo** en cada corrida (no hace delta).

Opciones: `--dry-run`, `--only=inv`, `--only=precios`, `--source-json=ARCHIVO.json`. Desde el navegador: `…/AspelSyncConexion.v2.php?sync=1` (texto).

Variables de entorno: `ASPEL_FB_DATABASE`, `ASPEL_FB_USER`, `ASPEL_FB_PASSWORD`, `ASPEL_API_BASE`, `ASPEL_API_TOKEN`, `ASPEL_LOG_FILE`.

### Cómo leer el resultado del v2

```
[INV]    OK    enviados 2832/2832 en 29 lotes; guardados por el sitio: 2832
[PRECIO] OK    enviados 9000/9000 en 30 lotes; guardados por el sitio: 8700; con aviso del sitio: 300
[PRECIO]    · lote 4: Item 12 (CVE_ART: AL1-5002, CVE_PRECIO: 1): Producto con cve_art 'AL1-5002' no existe
```

- `RECHAZADOS por datos inválidos`: el sitio rechazó ese registro (los demás del lote sí se guardaron).
- `con aviso del sitio`: el registro llegó pero no se pudo aplicar (por ejemplo, precio de un producto que aún no está en `aspel_products`).
- `LOTES CON ERROR`: el lote no llegó (red, token, servidor). Revisa el mensaje y `AspelSync.log`.

## Cambio relacionado en el sitio

`POST /api/aspel/sync` y `/api/aspel/precio-x-producto` validan **registro por registro**: un registro inválido se rechaza y se reporta en `errors` (con `rejected` en la respuesta), pero los demás del lote se guardan. Si **todos** los registros del lote son inválidos responde `422`. Pruebas: `tests/Feature/AspelSyncPerItemValidationTest.php`.

## Pendientes conocidos

- Confirmar en producción que existe el cron `schedule:run` (sin él, `aspel_products` se actualiza pero los productos de la tienda no).
- `[VENTA]`/`[PARTIDA]` reenvían la misma factura/partida cada hora porque el checkpoint se guarda sin fracciones de segundo (inofensivo: el sitio las acepta de nuevo).
- 2 productos con `CVE_ART` duplicado salvo espacios/mayúsculas (ver nota arriba).
