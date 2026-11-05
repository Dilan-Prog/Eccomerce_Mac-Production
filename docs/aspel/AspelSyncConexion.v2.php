<?php
/**
 * AspelSyncConexion v2 — sincroniza Aspel SAE (Firebird) con macdelnorte.com.
 *
 * Envia dos cosas al sitio:
 *   1) INVENTARIO  (INVE01 + campo libre MODELO de INVE_CLIB01)  -> POST /api/aspel/sync
 *   2) PRECIOS     (PRECIO_X_PROD01)                              -> POST /api/aspel/precio-x-producto
 *
 * Que cambia respecto a la version anterior:
 *   - Manda el token (Authorization: Bearer) en cada peticion.
 *   - Si INVE01 devuelve 0 filas ya NO dice "OK": lo marca como ERROR (asi se ve cuando algo esta mal en la base).
 *   - Un lote que falla NO detiene los demas: se reintenta y se sigue con el siguiente; al final se informa cuales fallaron.
 *   - Ajusta los datos a lo que acepta el sitio (textos largos, fechas, numeros, JSON) para que un producto raro
 *     no se rechace; y cuenta los registros rechazados por el sitio.
 *   - Lee la respuesta del sitio (OK / PARTIAL, guardados, rechazados), no solo el codigo HTTP.
 *   - Lotes mas chicos con una pausa corta entre peticiones (evita rafagas que el hosting pueda bloquear).
 *   - No sigue redirecciones (convertirian el POST en GET y perderian el token): avisa y se detiene en ese lote.
 *   - Deja un registro con fecha en AspelSync.log (junto a este archivo) y termina con codigo 1 si algo fallo, para que
 *     el Programador de tareas de Windows lo muestre como fallido.
 *   - Compatible con PHP 7.4 o superior. No incluye usuario/contrasenas: se configuran abajo o por variables de entorno.
 *
 * USO (linea de comandos en el equipo de Aspel):
 *   php AspelSyncConexion.v2.php                 envia inventario y precios
 *   php AspelSyncConexion.v2.php --dry-run       arma todo y muestra conteos, SIN enviar nada al sitio
 *   php AspelSyncConexion.v2.php --only=inv      solo inventario   (--only=precios: solo precios)
 *   php AspelSyncConexion.v2.php --source-json=ARCHIVO.json   (pruebas: usa filas de un JSON en vez de Firebird)
 * Desde el navegador: .../AspelSyncConexion.v2.php?sync=1  (texto)  o sin parametros (resumen en pantalla).
 */

// =====================================================================================================
// CONFIGURACION  (copia aqui los valores de tu script actual; NO los pegues en chats ni en el repositorio)
// =====================================================================================================
$CONFIG = [
    // Base de datos Aspel (Firebird)
    'fb_database' => getenv('ASPEL_FB_DATABASE') ?: 'C:\\ASPEL\\DATOS\\Sistemas Aspel\\SAE9.00\\Empresa01\\Datos\\SAE90EMPRE01.fdb',
    'fb_user'     => getenv('ASPEL_FB_USER') ?: 'SYSDBA',
    'fb_password' => getenv('ASPEL_FB_PASSWORD') ?: 'PEGA_AQUI_LA_CONTRASENA_DE_FIREBIRD',

    // Sitio
    'api_base'    => getenv('ASPEL_API_BASE') ?: 'https://www.macdelnorte.com/api/aspel',
    // Token de Aspel: panel de administracion > Integracion (Aspel) > crear/regenerar. Formato  key_id.secret
    'api_token'   => getenv('ASPEL_API_TOKEN') ?: 'PEGA_AQUI_EL_TOKEN',

    // Envio
    'batch_inventory' => 100,    // productos por peticion
    'batch_prices'    => 300,    // precios por peticion
    'pause_ms'        => 300,    // pausa entre peticiones
    'retries'         => 3,      // intentos por lote (errores de red, 429 y 5xx)
    'timeout'         => 120,    // segundos por peticion
    'connect_timeout' => 20,
    'verify_ssl'      => false,  // pon true si tu PHP tiene los certificados (cacert.pem) configurados
    'log_file'        => getenv('ASPEL_LOG_FILE') ?: __DIR__ . DIRECTORY_SEPARATOR . 'AspelSync.log',
    'log_max_bytes'   => 5 * 1024 * 1024,
];

// =====================================================================================================
// Campos de inventario: campo del API => [columna Firebird, tipo, largo maximo]   (largos = reglas del sitio)
//   s=texto  c=1 caracter  n=numero  i=entero  d=fecha-hora  j=JSON en texto
// =====================================================================================================
$INVENTORY_FIELDS = [
    'cve_art' => ['CVE_ART', 's', 50], 'descr' => ['DESCR', 's', 255], 'lin_prod' => ['LIN_PROD', 's', 50],
    'con_serie' => ['CON_SERIE', 'c'], 'uni_med' => ['UNI_MED', 's', 50], 'uni_emp' => ['UNI_EMP', 'n'],
    'ctrl_alm' => ['CTRL_ALM', 's', 50], 'tiem_surt' => ['TIEM_SURT', 'n'], 'stock_min' => ['STOCK_MIN', 'n'],
    'stock_max' => ['STOCK_MAX', 'n'], 'exist' => ['EXIST', 'n'], 'pend_surt' => ['PEND_SURT', 'n'],
    'apart' => ['APART', 'n'], 'comp_x_rec' => ['COMP_X_REC', 'n'], 'tip_costeo' => ['TIP_COSTEO', 'c'],
    'num_mon' => ['NUM_MON', 's', 10], 'costo_prom' => ['COSTO_PROM', 'n'], 'ult_costo' => ['ULT_COSTO', 'n'],
    'fch_ultcom' => ['FCH_ULTCOM', 'd'], 'fch_ultvta' => ['FCH_ULTVTA', 'd'], 'cve_obs' => ['CVE_OBS', 's', 50],
    'tipo_ele' => ['TIPO_ELE', 'c'], 'uni_alt' => ['UNI_ALT', 's', 50], 'fac_conv' => ['FAC_CONV', 'n'],
    'con_lote' => ['CON_LOTE', 'c'], 'con_pedimento' => ['CON_PEDIMENTO', 'c'], 'peso' => ['PESO', 'n'],
    'volumen' => ['VOLUMEN', 'n'], 'cve_esqimpu' => ['CVE_ESQIMPU', 's', 50], 'cve_bita' => ['CVE_BITA', 's', 50],
    'cuent_cont' => ['CUENT_CONT', 's', 50], 'cve_prodserv' => ['CVE_PRODSERV', 's', 50],
    'cve_unidad' => ['CVE_UNIDAD', 's', 50], 'vtas_anl_c' => ['VTAS_ANL_C', 'n'], 'vtas_anl_m' => ['VTAS_ANL_M', 'n'],
    'comp_anl_c' => ['COMP_ANL_C', 'n'], 'comp_anl_m' => ['COMP_ANL_M', 'n'], 'prefijo' => ['PREFIJO', 's', 50],
    'talla' => ['TALLA', 's', 50], 'color' => ['COLOR', 's', 50], 'cve_imagen' => ['CVE_IMAGEN', 's', 255],
    'campo_libre_modelo' => ['CAMPO_LIBRE_MODELO', 's', 25],
    'blk_cst_ext' => ['BLK_CST_EXT', 'c', 1, 'N'], 'status' => ['STATUS', 'c', 1, 'A'], 'man_ieps' => ['MAN_IEPS', 'c', 1, 'N'],
    'apl_man_imp' => ['APL_MAN_IMP', 'i'], 'cuota_ieps' => ['CUOTA_IEPS', 'n'], 'apl_man_ieps' => ['APL_MAN_IEPS', 'c'],
    'uuid' => ['UUID', 's', 50], 'version_sinc' => ['VERSION_SINC', 'd'], 'version_sinc_fecha_img' => ['VERSION_SINC_FECHA_IMG', 'd'],
    'edo_publ_ml' => ['EDO_PUBL_ML', 's', 50], 'cve_publ_ml' => ['CVE_PUBL_ML', 's', 50], 'condicion_ml' => ['CONDICION_ML', 's', 50],
    'tipo_publ_ml' => ['TIPO_PUBL_ML', 's', 50], 'categ_ml' => ['CATEG_ML', 's', 255], 'cve_cate_ml' => ['CVE_CATE_ML', 's', 50],
    'titulo_ml' => ['TITULO_ML', 's', 255], 'modo_envio_ml' => ['MODO_ENVIO_ML', 's', 50], 'envio_ml' => ['ENVIO_ML', 'c'],
    'largo_ml' => ['LARGO_ML', 'n'], 'alto_ml' => ['ALTO_ML', 'n'], 'ancho_ml' => ['ANCHO_ML', 'n'],
    'campos_categ_ml' => ['CAMPOS_CATEG_ML', 'j'], 'disponible_publ' => ['DISPONIBLE_PUBL', 'c'],
    'last_update_ml' => ['LAST_UPDATE_ML', 'd'], 'f_crea_ml' => ['F_CREA_ML', 'd'], 'imagen_ml' => ['IMAGEN_ML', 's', 255],
    'en_catalogo' => ['EN_CATALOGO', 's', 50], 'id_catalogo' => ['ID_CATALOGO', 's', 50], 'mat_peli' => ['MAT_PELI', 'c'],
];

// =====================================================================================================
// Utilidades de conversion (ajustan el dato a lo que acepta el sitio; nunca lanzan excepciones)
// =====================================================================================================
function cleanText($value, $max = null)
{
    if ($value === null) return null;
    $s = trim((string) $value);
    if ($s === '') return null;
    // Quita caracteres de control y UTF-8 invalido (rompen json_encode).
    $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $s);
    if (function_exists('mb_convert_encoding')) {
        $s = mb_convert_encoding($s, 'UTF-8', 'UTF-8');
    }
    if ($max !== null) {
        $s = function_exists('mb_substr') ? mb_substr($s, 0, $max, 'UTF-8') : substr($s, 0, $max);
        $s = trim($s);
    }
    return $s === '' ? null : $s;
}

function convertField($value, $type, $max = null)
{
    if ($value === null || $value === '') return null;
    switch ($type) {
        case 'n': return is_numeric($value) ? (float) $value : null;
        case 'i': return is_numeric($value) ? (int) $value : null;
        case 'c':
            $s = cleanText($value);
            return $s === null ? null : strtoupper(function_exists('mb_substr') ? mb_substr($s, 0, 1, 'UTF-8') : substr($s, 0, 1));
        case 'd':
            $t = strtotime((string) $value);
            return $t === false ? null : date('Y-m-d H:i:s', $t);
        case 'j':
            $s = cleanText($value);
            if ($s === null) return null;
            json_decode($s);
            return json_last_error() === JSON_ERROR_NONE ? $s : null;
        default:
            return cleanText($value, $max);
    }
}

function buildInventoryItem(array $row, array $fields)
{
    $item = [];
    foreach ($fields as $apiKey => $def) {
        $v = convertField(isset($row[$def[0]]) ? $row[$def[0]] : null, $def[1], isset($def[2]) ? $def[2] : null);
        if ($v === null && isset($def[3])) $v = $def[3];        // valor por omision (N / A)
        $item[$apiKey] = $v;
    }
    return $item;
}

function logLine(array $cfg, $level, $message)
{
    $line = '[' . date('Y-m-d H:i:s') . '] [' . $level . '] ' . $message . PHP_EOL;
    $file = $cfg['log_file'];
    if (@is_file($file) && @filesize($file) > $cfg['log_max_bytes']) {
        @rename($file, $file . '.1');
    }
    @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
}

/** Avance en pantalla (solo consola): sin esto el script parece "colgado" hasta el final. */
function progress($message)
{
    if (php_sapi_name() === 'cli') {
        echo '[' . date('H:i:s') . '] ' . $message . PHP_EOL;
    }
}

/** json_encode que no pierde un lote entero por un solo registro con texto invalido. */
function encodeBatch(array $batch, &$dropped)
{
    $flags = JSON_UNESCAPED_UNICODE | (defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0);
    $json = json_encode(['items' => $batch], $flags);
    if ($json !== false) return $json;
    $ok = [];
    foreach ($batch as $item) {
        if (json_encode($item, $flags) !== false) { $ok[] = $item; } else { $dropped++; }
    }
    $json = json_encode(['items' => $ok], $flags);
    return $json === false ? null : $json;
}

/** Una peticion POST con reintentos. Devuelve ['ok'=>bool,'http'=>int,'body'=>array|null,'error'=>string|null]. */
function postBatch(array $cfg, $url, array $batch, &$dropped)
{
    $json = encodeBatch($batch, $dropped);
    if ($json === null) return ['ok' => false, 'http' => 0, 'body' => null, 'error' => 'no se pudo codificar el lote a JSON'];

    $last = ['ok' => false, 'http' => 0, 'body' => null, 'error' => 'sin intentos'];
    for ($try = 1; $try <= max(1, (int) $cfg['retries']); $try++) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json', 'Accept: application/json',
                'Authorization: Bearer ' . $cfg['api_token'],
            ],
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,   // un redirect convertiria el POST en GET y perderia el token
            CURLOPT_TIMEOUT => $cfg['timeout'],
            CURLOPT_CONNECTTIMEOUT => $cfg['connect_timeout'],
            CURLOPT_SSL_VERIFYPEER => (bool) $cfg['verify_ssl'],
            CURLOPT_SSL_VERIFYHOST => $cfg['verify_ssl'] ? 2 : 0,
        ]);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $errstr = curl_error($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $redirect = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        curl_close($ch);

        $body = is_string($response) ? json_decode($response, true) : null;
        if ($response === false) {
            $last = ['ok' => false, 'http' => 0, 'body' => null, 'error' => "cURL #{$errno}: {$errstr}"];
        } elseif ($http >= 300 && $http < 400) {
            return ['ok' => false, 'http' => $http, 'body' => null,
                'error' => "el sitio redirige a {$redirect}: usa esa URL final en 'api_base' (no se siguen redirecciones)"];
        } elseif ($http >= 200 && $http < 300) {
            return ['ok' => true, 'http' => $http, 'body' => is_array($body) ? $body : null, 'error' => null];
        } else {
            $detail = is_array($body) && isset($body['message']) ? $body['message'] : substr((string) $response, 0, 200);
            $last = ['ok' => false, 'http' => $http, 'body' => is_array($body) ? $body : null, 'error' => "HTTP {$http}: {$detail}"];
            // 4xx distinto de 429: reintentar no sirve (token, datos o permisos)
            if ($http >= 400 && $http < 500 && $http != 429) return $last;
        }
        if ($try < $cfg['retries']) usleep(($try * 1500) * 1000);   // espera creciente entre intentos
    }
    return $last;
}

/** Envia todos los lotes de una seccion y acumula estadisticas. */
function sendAll(array $cfg, $label, $url, array $items, $batchSize, $dryRun, &$stats)
{
    $stats = ['label' => $label, 'total' => count($items), 'sent' => 0, 'saved' => 0, 'rejected' => 0, 'server_errors' => 0,
              'failed_batches' => [], 'samples' => [], 'dropped' => 0, 'batches' => 0];
    if ($dryRun) { $stats['batches'] = (int) ceil(count($items) / max(1, $batchSize)); return; }

    $batches = array_chunk($items, max(1, $batchSize));
    $stats['batches'] = count($batches);
    $consecutiveFails = 0;
    foreach ($batches as $i => $batch) {
        $n = $i + 1;
        $res = postBatch($cfg, $url, $batch, $stats['dropped']);
        progress("{$label} lote {$n}/" . count($batches) . ($res['ok'] ? ' OK' : ' FALLO: ' . $res['error']));
        if (!$res['ok']) {
            $stats['failed_batches'][] = "lote {$n}/" . count($batches) . ': ' . $res['error'];
            logLine($cfg, 'ERROR', "{$label} lote {$n}/" . count($batches) . ' fallo: ' . $res['error']);
            // Token o permisos mal: seguir mandando lotes no sirve de nada.
            if (in_array($res['http'], [401, 403], true)) break;
            // Sitio inalcanzable (IP bloqueada, sin internet): 3 lotes seguidos fallando = parar, no esperar horas.
            if (++$consecutiveFails >= 3) {
                $stats['failed_batches'][] = 'ABORTADO: 3 lotes seguidos fallaron; no se intentan los lotes restantes (' . (count($batches) - $n) . ').';
                break;
            }
        } else {
            $consecutiveFails = 0;
            $stats['sent'] += count($batch);
            $b = $res['body'];
            if (is_array($b)) {
                $stats['saved'] += isset($b['synced']) ? (int) $b['synced'] : 0;
                $stats['rejected'] += isset($b['rejected']) ? (int) $b['rejected'] : 0;
                $errs = isset($b['errors']) && is_array($b['errors']) ? $b['errors'] : [];
                $stats['server_errors'] += count($errs);
                foreach (array_slice($errs, 0, 3) as $e) {
                    if (count($stats['samples']) < 8) $stats['samples'][] = "lote {$n}: " . substr((string) $e, 0, 200);
                }
            }
        }
        if ($n < count($batches)) usleep((int) $cfg['pause_ms'] * 1000);
    }
}

function summarize($label, array $s, $extraError = null)
{
    if ($extraError !== null) return ['ERROR', "{$label}: {$extraError}"];
    $ok = empty($s['failed_batches']);
    $msg = "enviados {$s['sent']}/{$s['total']} en {$s['batches']} lotes; guardados por el sitio: {$s['saved']}";
    if ($s['rejected'] > 0) $msg .= "; RECHAZADOS por datos invalidos: {$s['rejected']}";
    if ($s['server_errors'] - $s['rejected'] > 0) $msg .= '; con aviso del sitio: ' . ($s['server_errors'] - $s['rejected']);
    if ($s['dropped'] > 0) $msg .= "; descartados por texto invalido: {$s['dropped']}";
    if (!$ok) $msg .= '; LOTES CON ERROR: ' . count($s['failed_batches']);
    return [$ok ? 'OK' : 'ERROR', $msg];
}

// =====================================================================================================
// EJECUCION
// =====================================================================================================
$isCli = (php_sapi_name() === 'cli');
$args = $isCli ? array_slice($argv, 1) : [];
$dryRun = in_array('--dry-run', $args, true) || (isset($_GET['dry']) && $_GET['dry'] == '1');
$only = null;
$sourceJson = null;
foreach ($args as $a) {
    if (strpos($a, '--only=') === 0) $only = substr($a, 7);
    if (strpos($a, '--source-json=') === 0) $sourceJson = substr($a, 14);
}
if (!$isCli && isset($_GET['only'])) $only = $_GET['only'];
$textMode = $isCli || (isset($_GET['sync']) && $_GET['sync'] == '1');
$doInv = ($only === null || $only === 'inv');
$doPrecios = ($only === null || $only === 'precios');

$lines = [];
$failed = false;
$invStats = null;
$prStats = null;
logLine($CONFIG, 'INFO', 'Inicio' . ($dryRun ? ' (dry-run)' : '') . ($only ? " only={$only}" : ''));

try {
    if (!$dryRun && (strpos($CONFIG['api_token'], 'PEGA_AQUI') !== false || trim($CONFIG['api_token']) === '')) {
        throw new Exception("Falta el token del sitio: configura 'api_token' (key_id.secret) al inicio del script.");
    }

    // ---- Origen de datos -------------------------------------------------------------------------
    $rows = [];
    $precioRows = [];
    if ($sourceJson !== null) {
        $data = json_decode((string) @file_get_contents($sourceJson), true);
        if (!is_array($data)) throw new Exception("No se pudo leer --source-json={$sourceJson}");
        $rows = isset($data['inve']) ? $data['inve'] : [];
        $precioRows = isset($data['precios']) ? $data['precios'] : [];
    } else {
        progress('Conectando a Firebird...');
        $dsn = 'firebird:dbname=localhost:' . $CONFIG['fb_database'] . ';charset=UTF8';
        $pdo = new PDO($dsn, $CONFIG['fb_user'], $CONFIG['fb_password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        if ($doInv) {
            $cols = [];
            foreach ($INVENTORY_FIELDS as $apiKey => $def) {
                if ($def[0] === 'CAMPO_LIBRE_MODELO') continue;
                $cols[] = 'i.' . $def[0];
            }
            // MODELO = campo libre 3 de INVE_CLIB01 (unido por CVE_PROD = CVE_ART)
            $sql = 'SELECT ' . implode(', ', $cols) . ', c.CAMPLIB3 AS CAMPO_LIBRE_MODELO '
                 . 'FROM INVE01 i LEFT JOIN INVE_CLIB01 c ON c.CVE_PROD = i.CVE_ART';
            progress('Leyendo INVE01...');
            $rows = $pdo->query($sql)->fetchAll();
            progress('INVE01: ' . count($rows) . ' filas leidas');
        }
        if ($doPrecios) {
            progress('Leyendo PRECIO_X_PROD01...');
            $precioRows = $pdo->query('SELECT CVE_ART, CVE_PRECIO, PRECIO FROM PRECIO_X_PROD01')->fetchAll();
            progress('PRECIO_X_PROD01: ' . count($precioRows) . ' filas leidas');
        }
    }

    // ---- BLOQUE 1: INVENTARIO -------------------------------------------------------------------
    if ($doInv) {
        $items = [];
        $skipped = 0;
        foreach ($rows as $row) {
            $item = buildInventoryItem($row, $INVENTORY_FIELDS);
            if ($item['cve_art'] === null) { $skipped++; continue; }   // sin clave no se puede guardar
            $items[] = $item;
        }
        if (count($items) === 0) {
            // Antes esto se reportaba como "OK — 0 productos": ahora es un error visible.
            $lines[] = '[INV]    ERROR La consulta a INVE01 devolvio 0 productos (' . count($rows) . ' filas leidas). Revisa la empresa/archivo .fdb y los permisos.';
            logLine($CONFIG, 'ERROR', 'INVE01 devolvio 0 productos');
            $failed = true;
        } else {
            sendAll($CONFIG, 'Inventario', rtrim($CONFIG['api_base'], '/') . '/sync', $items, $CONFIG['batch_inventory'], $dryRun, $invStats);
            list($st, $msg) = summarize('Inventario', $invStats);
            if ($skipped) $msg .= "; omitidos sin clave: {$skipped}";
            if ($dryRun) $msg = 'DRY-RUN: se enviarian ' . count($items) . " productos en {$invStats['batches']} lotes de {$CONFIG['batch_inventory']}";
            $lines[] = '[INV]    ' . ($st === 'OK' ? 'OK    ' : 'ERROR ') . $msg;
            logLine($CONFIG, $st === 'OK' ? 'INFO' : 'ERROR', 'Inventario: ' . $msg);
            if ($st !== 'OK') $failed = true;
            foreach ($invStats['failed_batches'] as $fb) { $lines[] = '[INV]      ! ' . $fb; }
            foreach ($invStats['samples'] as $sm) { $lines[] = '[INV]      · ' . $sm; }
            if ($dryRun && !empty($items)) {
                $lines[] = '[INV]      ejemplo: ' . substr(json_encode($items[0], JSON_UNESCAPED_UNICODE), 0, 300);
            }
        }
    }

    // ---- BLOQUE 2: PRECIOS ----------------------------------------------------------------------
    if ($doPrecios) {
        $precios = [];
        foreach ($precioRows as $r) {
            $cve = cleanText(isset($r['CVE_ART']) ? $r['CVE_ART'] : null, 50);
            if ($cve === null) continue;
            $precios[] = [
                'cve_art' => $cve,
                'cve_precio' => isset($r['CVE_PRECIO']) && is_numeric($r['CVE_PRECIO']) ? (int) $r['CVE_PRECIO'] : 0,
                'precio' => isset($r['PRECIO']) && is_numeric($r['PRECIO']) ? (float) $r['PRECIO'] : 0.0,
            ];
        }
        if (count($precios) === 0) {
            $lines[] = '[PRECIO] ERROR La consulta a PRECIO_X_PROD01 devolvio 0 registros.';
            logLine($CONFIG, 'ERROR', 'PRECIO_X_PROD01 devolvio 0 registros');
            $failed = true;
        } else {
            sendAll($CONFIG, 'Precios', rtrim($CONFIG['api_base'], '/') . '/precio-x-producto', $precios, $CONFIG['batch_prices'], $dryRun, $prStats);
            list($st, $msg) = summarize('Precios', $prStats);
            if ($dryRun) $msg = 'DRY-RUN: se enviarian ' . count($precios) . " precios en {$prStats['batches']} lotes de {$CONFIG['batch_prices']}";
            $lines[] = '[PRECIO] ' . ($st === 'OK' ? 'OK    ' : 'ERROR ') . $msg;
            logLine($CONFIG, $st === 'OK' ? 'INFO' : 'ERROR', 'Precios: ' . $msg);
            if ($st !== 'OK') $failed = true;
            foreach ($prStats['failed_batches'] as $fb) { $lines[] = '[PRECIO]    ! ' . $fb; }
            foreach ($prStats['samples'] as $sm) { $lines[] = '[PRECIO]    · ' . $sm; }
        }
    }
} catch (Throwable $e) {
    $failed = true;
    $lines[] = '[FATAL] ' . $e->getMessage();
    logLine($CONFIG, 'ERROR', 'FATAL: ' . $e->getMessage());
}

logLine($CONFIG, $failed ? 'ERROR' : 'INFO', 'Fin ' . ($failed ? 'CON ERRORES' : 'sin errores'));

// =====================================================================================================
// SALIDA
// =====================================================================================================
if ($textMode) {
    if (!$isCli) header('Content-Type: text/plain; charset=UTF-8');
    echo '[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . implode(PHP_EOL, $lines) . PHP_EOL;
    if ($isCli) exit($failed ? 1 : 0);
    exit;
}
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><title>Aspel — sincronizacion</title>
<style>body{font-family:Segoe UI,Arial,sans-serif;margin:24px;color:#0b2540}.ok{color:#1b5e20}.err{color:#c62828}pre{background:#f4f7fb;padding:12px;border-radius:6px;white-space:pre-wrap}</style></head>
<body>
<h2>Sincronizacion Aspel <span class="<?php echo $failed ? 'err' : 'ok'; ?>"><?php echo $failed ? '✗ con errores' : '✓ sin errores'; ?></span></h2>
<pre><?php echo htmlspecialchars(implode("\n", $lines)); ?></pre>
<p>Registro: <code><?php echo htmlspecialchars($CONFIG['log_file']); ?></code></p>
</body></html>
