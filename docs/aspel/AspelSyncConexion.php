<?php

// Hora local de Mexico: los timestamps de Aspel (VERSION_SINC, etc.) estan en hora local, y asi el log coincide con el reloj de Windows.
date_default_timezone_set('America/Mexico_City');

$database = "C:\ASPEL\DATOS\Sistemas Aspel\SAE9.00\Empresa01\Datos\SAE90EMPRE01.fdb";
$dsn = "firebird:dbname=localhost:$database;charset=UTF8";
$username = "SYSDBA";
$password = ""; // <-- poner la contrasena de Firebird (NO pegarla en chats ni en el repositorio)

// Token del módulo "Integración" del panel admin (Aspel Integration > Integración).
// Formato: {key_id}.{secret} — el secreto solo se muestra una vez al crear o
// regenerar el token ahí. Sin esto, todas las peticiones a /api/aspel/* dan 401.
$aspelApiToken = ""; // <-- reemplazar con el valor real (NO pegarlo en chats ni en el repositorio)

// Tope de filas por tabla (ventas/partidas) que se procesan en UNA sola
// corrida — ver bloque 5 más abajo. Con historiales muy grandes, la primera
// corrida (y varias siguientes) van a reportar "llegó al límite, falta más"
// hasta ponerse al día; después, en operación normal (poco delta por
// corrida), este límite casi nunca se alcanza.
const HISTORY_BATCH_LIMIT = 5000;

// ============================================================
// CHECKPOINT DE SINCRONIZACIÓN POR DELTA
// ============================================================
// Guarda, en un archivo local junto a este script, la marca de avance de
// las tablas de facturación que se sincronizan por delta (FACTF01 /
// PAR_FACTF01). Un mismo archivo JSON con varias claves, una por tabla.
//
// FACTF01 / PAR_FACTF01 SÍ tienen tope de filas por corrida (HISTORY_BATCH_LIMIT),
// porque el historial de facturación puede ser enorme — así que su checkpoint
// es el VERSION_SINC más alto de las filas que REALMENTE se mandaron con éxito
// en esa corrida (no la hora de inicio). Así nunca se pierde nada, sin importar
// cuántas corridas tome ponerse al día.
//
// INVENTARIO (INVE01) YA NO USA ESTE CHECKPOINT. Antes comparaba
// VERSION_SINC > hora de la corrida, pero la hora de PHP (UTC) iba 6 horas
// adelante de la hora local de Aspel, así que la condición nunca se cumplía y
// el inventario siempre decía "sin cambios". Ahora el inventario se compara
// por CONTENIDO (huella de cada producto) — ver sincronizarInventario().
//
// Nota importante: CLIE01 (clientes), MONED01 (monedas) y PRECIO_X_PROD01
// (precios) NO tienen, en las columnas que ya usábamos, ninguna columna de
// fecha de modificación confirmada. Por eso esos tres bloques se quedan
// mandando la tabla completa en cada corrida (son tablas más chicas).
$checkpointFile = __DIR__ . '/aspel_sync_checkpoint.json';

function leerCheckpoint(string $file, string $key = 'inve01_last_sync'): ?string
{
    if (!file_exists($file)) return null;
    $data = json_decode((string) file_get_contents($file), true);
    return $data[$key] ?? null;
}

function guardarCheckpoint(string $file, string $key, string $timestamp): void
{
    $data = file_exists($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
    $data[$key] = $timestamp;
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

/**
 * Sincroniza INVE01 comparando el CONTENIDO de cada producto contra lo ya enviado (no depende de VERSION_SINC ni de la hora).
 * Devuelve [status('success'|'error'), mensaje].
 *
 * - Primera corrida / cada $fullEveryHours horas / si falta el archivo de estado: envia TODO (completo).
 * - Las demas: solo los productos cuyo contenido (existencia, costo, descripcion, etc.) cambio.
 * - Un lote que falla no detiene los demas; tras 3 fallos seguidos (o 401/403) se detiene.
 * - Los productos que el sitio rechaza o cuyo lote fallo NO se marcan como enviados: se reintentan en la siguiente corrida.
 */
function sincronizarInventario(array $items, bool $soloCambios, string $url, string $token, string $stateFile, int $batchSize = 200, int $fullEveryHours = 24): array
{
    $state = ['full_at' => null, 'hashes' => []];
    if ($soloCambios && is_file($stateFile)) {
        $s = json_decode((string) file_get_contents($stateFile), true);
        if (is_array($s) && isset($s['hashes']) && is_array($s['hashes'])) {
            $state = ['full_at' => $s['full_at'] ?? null, 'hashes' => $s['hashes']];
        }
    }

    $fullTs  = $state['full_at'] ? strtotime($state['full_at']) : false;
    $fullDue = !$soloCambios || empty($state['hashes']) || $fullTs === false
        || $fullTs > time() || (time() - $fullTs) >= $fullEveryHours * 3600;

    $hashes  = [];
    $pending = [];
    foreach ($items as $it) {
        $k = isset($it['cve_art']) ? (string) $it['cve_art'] : '';
        if ($k === '') continue;
        $hashes[$k] = md5(json_encode($it, JSON_UNESCAPED_UNICODE | (defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0)));
        if ($fullDue || !isset($state['hashes'][$k]) || $state['hashes'][$k] !== $hashes[$k]) {
            $pending[] = $it;
        }
    }
    $total = count($hashes);

    if (empty($pending)) {
        return ['success', "Inventario OK — sin cambios ({$total} productos revisados)"];
    }

    $newHashes = $fullDue ? [] : $state['hashes'];
    $batches = array_chunk($pending, max(1, $batchSize));
    $sent = $failed = $rejected = $consecutive = 0;
    $firstError = '';

    foreach ($batches as $i => $batch) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ["Content-Type: application/json", "Accept: application/json", "Authorization: Bearer {$token}"],
            CURLOPT_POSTFIELDS     => json_encode(['items' => $batch], JSON_UNESCAPED_UNICODE | (defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0)),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $response = curl_exec($ch);
        $http     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno    = curl_errno($ch);
        $errstr   = curl_error($ch);
        curl_close($ch);

        if ($response === false || $http < 200 || $http >= 300) {
            $failed++;
            $consecutive++;
            if ($firstError === '') {
                $firstError = $response === false ? "cURL #{$errno}: {$errstr}" : "HTTP {$http}: " . substr((string) $response, 0, 200);
            }
            if ($http === 401 || $http === 403 || $consecutive >= 3) break;
        } else {
            $consecutive = 0;
            $sent += count($batch);
            $body = json_decode((string) $response, true);
            $rej  = is_array($body) ? (int) ($body['rejected'] ?? 0) : 0;
            if ($rej > 0) {
                $rejected += $rej;
                if ($firstError === '' && !empty($body['errors'][0])) $firstError = substr((string) $body['errors'][0], 0, 200);
                // El sitio rechazo algun producto del lote: no se marca el lote como enviado, se reintenta la proxima vez.
            } else {
                foreach ($batch as $it) {
                    $k = (string) $it['cve_art'];
                    if (isset($hashes[$k])) $newHashes[$k] = $hashes[$k];
                }
            }
        }
        if ($i < count($batches) - 1) usleep(300000);
    }

    if ($soloCambios) {
        $out = [
            'full_at' => ($fullDue && $failed === 0) ? date('Y-m-d H:i:s') : $state['full_at'],
            'hashes'  => $newHashes,
        ];
        $tmp = $stateFile . '.tmp';
        if (@file_put_contents($tmp, json_encode($out)) !== false) {
            @rename($tmp, $stateFile);
        }
    }

    $modo = $fullDue ? 'completo' : 'solo cambios';
    if ($failed > 0) {
        return ['error', "Inventario ERROR ({$modo}) — fallaron {$failed} de " . count($batches) . " lotes: {$firstError}; enviados {$sent} de " . count($pending)];
    }
    $msg = "Inventario OK ({$modo}) — {$sent} productos enviados de {$total} en " . count($batches) . ' lotes';
    if ($rejected > 0) $msg .= "; RECHAZADOS por el sitio: {$rejected} (ej: {$firstError})";
    return ['success', $msg];
}

try {
    $isCli    = (php_sapi_name() === 'cli');
    $onlySync = $isCli || (isset($_GET['sync']) && $_GET['sync'] == '1');

    // Momento de inicio de ESTA corrida (ya no se usa para el inventario; se conserva por compatibilidad).
    $syncStartedAt = date('Y-m-d H:i:s');

    // El modo "solo cambios" se aplica nada más cuando corre de forma
    // automática/programada. Si alguien abre el script directo en el
    // navegador (sin ?sync=1), se manda y se muestra el inventario COMPLETO
    // — es la vista de depuración/inspección manual.
    $applyDelta = $onlySync;

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // ========================================================
    // HELPERS
    // ========================================================
    function postToApi(string $url, array $items, string $token, int $batchSize = 300, int $timeout = 120)
    {
        if (empty($items)) return;

        foreach (array_chunk($items, $batchSize) as $batch) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => ["Content-Type: application/json", "Accept: application/json", "Authorization: Bearer {$token}"],
                CURLOPT_POSTFIELDS     => json_encode(['items' => $batch], JSON_UNESCAPED_UNICODE),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => $timeout,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);
            $response = curl_exec($ch);
            $errno    = curl_errno($ch);
            $errstr   = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || $httpCode < 200 || $httpCode >= 300) {
                $msg = $response === false
                    ? "cURL error (#{$errno}): {$errstr}"
                    : "HTTP {$httpCode}: " . substr($response, 0, 200);
                throw new Exception("Error enviando a {$url}: {$msg}");
            }
        }
    }

    /** Como postToApi() pero para /api/aspel/ventas, que espera {ventas:[...], partidas:[...]} en vez de {items:[...]}. */
    function postVentasPartidas(string $url, array $ventas, array $partidas, string $token, int $timeout = 120): void
    {
        if (empty($ventas) && empty($partidas)) return;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ["Content-Type: application/json", "Accept: application/json", "Authorization: Bearer {$token}"],
            CURLOPT_POSTFIELDS     => json_encode(['ventas' => $ventas, 'partidas' => $partidas], JSON_UNESCAPED_UNICODE),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $errstr   = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            $msg = $response === false
                ? "cURL error (#{$errno}): {$errstr}"
                : "HTTP {$httpCode}: " . substr($response, 0, 300);
            throw new Exception("Error enviando a {$url}: {$msg}");
        }
    }

    function convertValue($value, $type = 'string') {
        if ($value === null || $value === '') return null;
        switch ($type) {
            case 'numeric': return (float) $value;
            case 'integer': return (int) $value;
            case 'string':  return (string) trim($value);
            case 'date':    return date('Y-m-d H:i:s', strtotime($value));
            case 'char':    return strtoupper(substr((string) $value, 0, 1)) ?? null;
            default:        return $value;
        }
    }

    // ========================================================
    // BLOQUE 1 — INVENTARIO (INVE01) — SOLO LO QUE CAMBIO (por contenido)
    // ========================================================
    // LEFT JOIN a INVE_CLIB01 para traer el campo libre CAMPLIB3, reetiquetado
    // "MODELO" en PARAM_CAMPOSLIBRES01 de esta instalación (ver comentario en
    // database/migrations/2026_08_17_120000_add_campo_libre_modelo_to_aspel_products_table.php
    // del repo Laravel). Los demás campos libres (CAMPLIB1,2,4,5,6) no se usan.
    //
    // Se lee INVE01 COMPLETO en cada corrida; sincronizarInventario() decide
    // qué mandar comparando contra la huella de lo ya enviado.
    $sql = 'SELECT
        CVE_ART, DESCR, LIN_PROD, CON_SERIE, UNI_MED, UNI_EMP, CTRL_ALM,
        TIEM_SURT, STOCK_MIN, STOCK_MAX, TIP_COSTEO, NUM_MON, FCH_ULTCOM,
        COMP_X_REC, FCH_ULTVTA, PEND_SURT, EXIST, COSTO_PROM, ULT_COSTO,
        CVE_OBS, TIPO_ELE, UNI_ALT, FAC_CONV, APART, CON_LOTE, CON_PEDIMENTO,
        PESO, VOLUMEN, CVE_ESQIMPU, CVE_BITA, VTAS_ANL_C, VTAS_ANL_M,
        COMP_ANL_C, COMP_ANL_M, PREFIJO, TALLA, COLOR, CUENT_CONT, CVE_IMAGEN,
        BLK_CST_EXT, STATUS, MAN_IEPS, APL_MAN_IMP, CUOTA_IEPS, APL_MAN_IEPS,
        UUID, VERSION_SINC, VERSION_SINC_FECHA_IMG, CVE_PRODSERV, CVE_UNIDAD,
        EDO_PUBL_ML, CVE_PUBL_ML, CONDICION_ML, TIPO_PUBL_ML, MODO_ENVIO_ML,
        LARGO_ML, ALTO_ML, ANCHO_ML, ENVIO_ML, CATEG_ML, CAMPOS_CATEG_ML,
        DISPONIBLE_PUBL, CVE_CATE_ML, LAST_UPDATE_ML, F_CREA_ML, IMAGEN_ML,
        EN_CATALOGO, ID_CATALOGO, TITULO_ML, MAT_PELI,
        INVE_CLIB01.CAMPLIB3 AS CAMPO_LIBRE_MODELO
        FROM INVE01
        LEFT JOIN INVE_CLIB01 ON INVE_CLIB01.CVE_PROD = INVE01.CVE_ART';

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $itemsForApi = array_map(function ($row) {
        return [
            // Strings
            'cve_art'        => convertValue($row['CVE_ART'], 'string'),
            'descr'          => convertValue($row['DESCR'], 'string'),
            'lin_prod'       => convertValue($row['LIN_PROD'], 'string'),
            'con_serie'      => convertValue($row['CON_SERIE'], 'char'),
            'uni_med'        => convertValue($row['UNI_MED'], 'string'),
            'ctrl_alm'       => convertValue($row['CTRL_ALM'], 'string'),
            'cve_obs'        => convertValue($row['CVE_OBS'], 'string'),
            'tipo_ele'       => convertValue($row['TIPO_ELE'], 'char'),
            'uni_alt'        => convertValue($row['UNI_ALT'], 'string'),
            'con_lote'       => convertValue($row['CON_LOTE'], 'char'),
            'con_pedimento'  => convertValue($row['CON_PEDIMENTO'], 'char'),
            'cve_esqimpu'    => convertValue($row['CVE_ESQIMPU'], 'string'),
            'cve_bita'       => convertValue($row['CVE_BITA'], 'string'),
            'prefijo'        => convertValue($row['PREFIJO'], 'string'),
            'talla'          => convertValue($row['TALLA'], 'string'),
            'color'          => convertValue($row['COLOR'], 'string'),
            'cuent_cont'     => convertValue($row['CUENT_CONT'], 'string'),
            'cve_imagen'     => convertValue($row['CVE_IMAGEN'], 'string'),
            'campo_libre_modelo' => convertValue($row['CAMPO_LIBRE_MODELO'], 'string'),
            'cve_prodserv'   => convertValue($row['CVE_PRODSERV'], 'string'),
            'cve_unidad'     => convertValue($row['CVE_UNIDAD'], 'string'),
            'num_mon'        => convertValue($row['NUM_MON'], 'string'),
            'tip_costeo'     => convertValue($row['TIP_COSTEO'], 'char'),

            // Campos CHAR (S/N)
            'blk_cst_ext'    => convertValue($row['BLK_CST_EXT'] ?? 'N', 'char'),
            'status'         => convertValue($row['STATUS'] ?? 'A', 'char'),
            'man_ieps'       => convertValue($row['MAN_IEPS'] ?? 'N', 'char'),
            'apl_man_ieps'   => convertValue($row['APL_MAN_IEPS'], 'char'),
            'envio_ml'       => convertValue($row['ENVIO_ML'], 'char'),
            'disponible_publ'=> convertValue($row['DISPONIBLE_PUBL'], 'char'),
            'mat_peli'       => convertValue($row['MAT_PELI'], 'char'),

            // Numéricos
            'uni_emp'        => convertValue($row['UNI_EMP'], 'numeric'),
            'tiem_surt'      => convertValue($row['TIEM_SURT'], 'numeric'),
            'stock_min'      => convertValue($row['STOCK_MIN'], 'numeric'),
            'stock_max'      => convertValue($row['STOCK_MAX'], 'numeric'),
            'exist'          => convertValue($row['EXIST'], 'numeric'),
            'pend_surt'      => convertValue($row['PEND_SURT'], 'numeric'),
            'apart'          => convertValue($row['APART'], 'numeric'),
            'comp_x_rec'     => convertValue($row['COMP_X_REC'], 'numeric'),
            'costo_prom'     => convertValue($row['COSTO_PROM'], 'numeric'),
            'ult_costo'      => convertValue($row['ULT_COSTO'], 'numeric'),
            'fac_conv'       => convertValue($row['FAC_CONV'], 'numeric'),
            'peso'           => convertValue($row['PESO'], 'numeric'),
            'volumen'        => convertValue($row['VOLUMEN'], 'numeric'),
            'vtas_anl_c'     => convertValue($row['VTAS_ANL_C'], 'numeric'),
            'vtas_anl_m'     => convertValue($row['VTAS_ANL_M'], 'numeric'),
            'comp_anl_c'     => convertValue($row['COMP_ANL_C'], 'numeric'),
            'comp_anl_m'     => convertValue($row['COMP_ANL_M'], 'numeric'),
            'cuota_ieps'     => convertValue($row['CUOTA_IEPS'], 'numeric'),
            'largo_ml'       => convertValue($row['LARGO_ML'], 'numeric'),
            'alto_ml'        => convertValue($row['ALTO_ML'], 'numeric'),
            'ancho_ml'       => convertValue($row['ANCHO_ML'], 'numeric'),
            'apl_man_imp'    => convertValue($row['APL_MAN_IMP'], 'integer'),

            // Fechas
            'fch_ultcom'             => convertValue($row['FCH_ULTCOM'], 'date'),
            'fch_ultvta'             => convertValue($row['FCH_ULTVTA'], 'date'),
            'version_sinc'           => convertValue($row['VERSION_SINC'], 'date'),
            'version_sinc_fecha_img' => convertValue($row['VERSION_SINC_FECHA_IMG'], 'date'),
            'last_update_ml'         => convertValue($row['LAST_UPDATE_ML'], 'date'),
            'f_crea_ml'              => convertValue($row['F_CREA_ML'], 'date'),

            // Mercado Libre
            'edo_publ_ml'     => convertValue($row['EDO_PUBL_ML'], 'string'),
            'cve_publ_ml'     => convertValue($row['CVE_PUBL_ML'], 'string'),
            'condicion_ml'    => convertValue($row['CONDICION_ML'], 'string'),
            'tipo_publ_ml'    => convertValue($row['TIPO_PUBL_ML'], 'string'),
            'modo_envio_ml'   => convertValue($row['MODO_ENVIO_ML'], 'string'),
            'categ_ml'        => convertValue($row['CATEG_ML'], 'string'),
            'campos_categ_ml' => convertValue($row['CAMPOS_CATEG_ML'], 'string'),
            'cve_cate_ml'     => convertValue($row['CVE_CATE_ML'], 'string'),
            'imagen_ml'       => convertValue($row['IMAGEN_ML'], 'string'),
            'en_catalogo'     => convertValue($row['EN_CATALOGO'], 'string'),
            'id_catalogo'     => convertValue($row['ID_CATALOGO'], 'string'),
            'titulo_ml'       => convertValue($row['TITULO_ML'], 'string'),
            'uuid'            => convertValue($row['UUID'], 'string'),
        ];
    }, $rows);

    // Envío del inventario: completo la primera vez / cada 24 h, y después solo los productos que cambiaron.
    list($invStatus, $invMessage) = sincronizarInventario(
        $itemsForApi,
        $applyDelta,
        'https://www.macdelnorte.com/api/aspel/sync',
        $aspelApiToken,
        __DIR__ . '/aspel_inventory_hashes.json'
    );

    // ========================================================
    // BLOQUE 2 — PRECIOS X PRODUCTO (sigue siendo tabla completa —
    // no se confirmó ninguna columna de fecha de modificación aquí)
    // ========================================================
    $precioStatus  = 'success';
    $precioMessage = '';

    try {
        $sqlPX      = 'SELECT CVE_ART, CVE_PRECIO, PRECIO FROM PRECIO_X_PROD01';
        $precioRows = $pdo->query($sqlPX)->fetchAll();

        $preciosForApi = array_map(function ($row) {
            return [
                'cve_art'    => trim($row['CVE_ART'] ?? ''),
                'cve_precio' => isset($row['CVE_PRECIO']) ? (int) $row['CVE_PRECIO'] : 0,
                'precio'     => isset($row['PRECIO'])     ? (float) $row['PRECIO']   : 0.0,
            ];
        }, $precioRows);

        postToApi(
            'https://www.macdelnorte.com/api/aspel/precio-x-producto',
            $preciosForApi,
            $aspelApiToken,
            300,
            120
        );

        $precioMessage = "Precios OK — " . count($preciosForApi) . " registros";

    } catch (Exception $e) {
        $precioStatus  = 'error';
        $precioMessage = "Precios ERROR: " . $e->getMessage();
    }

    // ========================================================
    // BLOQUE 3 — CLIENTES (CLIE01) (sigue siendo tabla completa —
    // no se confirmó ninguna columna de fecha de modificación aquí)
    // ========================================================
    $clienteStatus  = 'success';
    $clienteMessage = '';

    try {
        $sqlClie = 'SELECT
            CLAVE, STATUS, NOMBRE, NOMBRECOMERCIAL, RFC, TIPO_EMPRESA, USO_CFDI,
            REG_FISC, EMAILPRED, TELEFONO, CALLE, NUMEXT, COLONIA, MUNICIPIO,
            ESTADO, CODIGO, LISTA_PREC, DESCUENTO, CVE_VEND
            FROM CLIE01';
        $clieRows = $pdo->query($sqlClie)->fetchAll();

        $clientesForApi = array_map(function ($row) {
            return [
                'clave'            => convertValue($row['CLAVE'], 'string'),
                'status'           => convertValue($row['STATUS'], 'char'),
                'nombre'           => convertValue($row['NOMBRE'], 'string'),
                'nombre_comercial' => convertValue($row['NOMBRECOMERCIAL'], 'string'),
                'rfc'              => convertValue($row['RFC'], 'string'),
                'tipo_empresa'     => convertValue($row['TIPO_EMPRESA'], 'char'),
                'uso_cfdi'         => convertValue($row['USO_CFDI'], 'string'),
                'regimen_fiscal'   => convertValue($row['REG_FISC'], 'string'),
                'email'            => convertValue($row['EMAILPRED'], 'string'),
                'telefono'         => convertValue($row['TELEFONO'], 'string'),
                'calle'            => convertValue($row['CALLE'], 'string'),
                'num_ext'          => convertValue($row['NUMEXT'], 'string'),
                'colonia'          => convertValue($row['COLONIA'], 'string'),
                'municipio'        => convertValue($row['MUNICIPIO'], 'string'),
                'estado'           => convertValue($row['ESTADO'], 'string'),
                'codigo_postal'    => convertValue($row['CODIGO'], 'string'),
                'lista_precio'     => convertValue($row['LISTA_PREC'], 'integer'),
                'descuento'        => convertValue($row['DESCUENTO'], 'numeric'),
                'cve_vendedor'     => convertValue($row['CVE_VEND'], 'string'),
            ];
        }, $clieRows);

        postToApi(
            'https://www.macdelnorte.com/api/aspel/clientes',
            $clientesForApi,
            $aspelApiToken,
            300,
            120
        );

        $clienteMessage = "Clientes OK — " . count($clientesForApi) . " registros";

    } catch (Exception $e) {
        $clienteStatus  = 'error';
        $clienteMessage = "Clientes ERROR: " . $e->getMessage();
    }

    // ========================================================
    // BLOQUE 4 — MONEDAS / TIPO DE CAMBIO (MONED01) (sigue siendo
    // tabla completa — es un catálogo chico, el costo es mínimo)
    // ========================================================
    $monedaStatus  = 'success';
    $monedaMessage = '';

    try {
        $sqlMoned  = 'SELECT NUM_MONED, DESCR, SIMBOLO, TCAMBIO, STATUS, CVE_MONED FROM MONED01';
        $monedRows = $pdo->query($sqlMoned)->fetchAll();

        $monedasForApi = array_map(function ($row) {
            return [
                'num_moneda'  => isset($row['NUM_MONED']) ? (int) $row['NUM_MONED'] : 0,
                'descripcion' => convertValue($row['DESCR'], 'string'),
                'simbolo'     => convertValue($row['SIMBOLO'], 'string'),
                'tipo_cambio' => convertValue($row['TCAMBIO'], 'numeric'),
                // La API espera boolean; en Aspel STATUS es char ('A' = activo).
                'status'      => (($row['STATUS'] ?? '') === 'A') ? 1 : 0,
                'cve_moned'   => convertValue($row['CVE_MONED'], 'string'),
            ];
        }, $monedRows);

        postToApi(
            'https://www.macdelnorte.com/api/aspel/tipo-cambio',
            $monedasForApi,
            $aspelApiToken,
            300,
            120
        );

        $monedaMessage = "Monedas OK — " . count($monedasForApi) . " registros";

    } catch (Exception $e) {
        $monedaStatus  = 'error';
        $monedaMessage = "Monedas ERROR: " . $e->getMessage();
    }

    // ========================================================
    // BLOQUE 5 — VENTAS / FACTURACIÓN (FACTF01 + PAR_FACTF01) — POR DELTA,
    // ACOTADO A HISTORY_BATCH_LIMIT FILAS POR CORRIDA
    // ========================================================
    // Ambas tablas tienen VERSION_SINC. A diferencia del inventario, aquí SÍ se
    // acota por corrida: el historial de facturación puede tener años de
    // documentos, y mandarlo todo de golpe podría tardar demasiado o saturar
    // el endpoint. Por eso:
    // - Se ordena por VERSION_SINC ascendente y se pide como mucho
    //   HISTORY_BATCH_LIMIT filas por corrida (constante al inicio del archivo).
    // - El checkpoint de cada tabla es el VERSION_SINC más alto de lo que
    //   REALMENTE se mandó con éxito en esa corrida (no la hora de inicio),
    //   así que está en la misma hora que usa Aspel y no depende de la zona
    //   horaria de PHP.
    // - Ventas y partidas son deltas INDEPENDIENTES (checkpoints separados).
    $ventaStatus    = 'success';
    $ventaMessage   = '';
    $partidaStatus  = 'success';
    $partidaMessage = '';

    // ---- Ventas (encabezado, FACTF01) ----
    try {
        $checkpointVentas = $applyDelta ? leerCheckpoint($checkpointFile, 'factf01_last_sync') : null;

        $sqlVentas = 'SELECT FIRST ' . HISTORY_BATCH_LIMIT . '
            CVE_DOC, CVE_CLPV, FECHA_DOC, FECHA_CANCELA, IMPORTE, RFC, NUM_MONED, TIPCAMB, UUID, VERSION_SINC
            FROM FACTF01 WHERE TIP_DOC = \'F\'';
        $paramsVentas = [];
        if ($applyDelta && $checkpointVentas !== null) {
            $sqlVentas .= ' AND VERSION_SINC > :checkpoint';
            $paramsVentas['checkpoint'] = $checkpointVentas;
        }
        $sqlVentas .= ' ORDER BY VERSION_SINC ASC';

        $stmtVentas = $pdo->prepare($sqlVentas);
        $stmtVentas->execute($paramsVentas);
        $ventaRows = $stmtVentas->fetchAll();

        $maxVersionSincVentas = null;
        $ventasForApi = array_map(function ($row) use (&$maxVersionSincVentas) {
            if ($row['VERSION_SINC'] !== null) {
                $maxVersionSincVentas = $row['VERSION_SINC'];
            }
            return [
                'cve_doc'       => convertValue($row['CVE_DOC'], 'string'),
                'cve_clpv'      => convertValue($row['CVE_CLPV'], 'string'),
                'fecha_doc'     => convertValue($row['FECHA_DOC'], 'date'),
                'fecha_cancela' => convertValue($row['FECHA_CANCELA'], 'date'),
                'importe'       => convertValue($row['IMPORTE'], 'numeric'),
                'rfc'           => convertValue($row['RFC'], 'string'),
                'num_moned'     => convertValue($row['NUM_MONED'], 'integer'),
                'tipcamb'       => convertValue($row['TIPCAMB'], 'numeric'),
                'uuid'          => convertValue($row['UUID'], 'string'),
            ];
        }, $ventaRows);

        if (empty($ventasForApi)) {
            $ventaMessage = $applyDelta
                ? 'Ventas OK — sin cambios desde ' . ($checkpointVentas ?? 'siempre')
                : 'Ventas OK — 0 facturas';
        } else {
            foreach (array_chunk($ventasForApi, 300) as $batch) {
                postVentasPartidas('https://www.macdelnorte.com/api/aspel/ventas', $batch, [], $aspelApiToken);
            }
            $llegoAlLimite = count($ventasForApi) >= HISTORY_BATCH_LIMIT;
            $ventaMessage = 'Ventas OK (' . ($applyDelta ? 'delta' : 'completo') . ') — ' . count($ventasForApi) . ' facturas'
                . ($llegoAlLimite ? ' [llegó al límite de ' . HISTORY_BATCH_LIMIT . ' — falta más backlog, seguirá en la próxima corrida]' : '');

            if ($applyDelta && $maxVersionSincVentas !== null) {
                guardarCheckpoint($checkpointFile, 'factf01_last_sync', convertValue($maxVersionSincVentas, 'date'));
            }
        }
    } catch (Exception $e) {
        $ventaStatus  = 'error';
        $ventaMessage = 'Ventas ERROR: ' . $e->getMessage();
    }

    // ---- Partidas (detalle, PAR_FACTF01) ----
    try {
        $checkpointPartidas = $applyDelta ? leerCheckpoint($checkpointFile, 'par_factf01_last_sync') : null;

        $sqlPartidas = 'SELECT FIRST ' . HISTORY_BATCH_LIMIT . '
            CVE_DOC, NUM_PAR, CVE_ART, CANT, PREC, TOT_PARTIDA, DESCR_ART, VERSION_SINC
            FROM PAR_FACTF01';
        $paramsPartidas = [];
        if ($applyDelta && $checkpointPartidas !== null) {
            $sqlPartidas .= ' WHERE VERSION_SINC > :checkpoint';
            $paramsPartidas['checkpoint'] = $checkpointPartidas;
        }
        $sqlPartidas .= ' ORDER BY VERSION_SINC ASC';

        $stmtPartidas = $pdo->prepare($sqlPartidas);
        $stmtPartidas->execute($paramsPartidas);
        $partidaRows = $stmtPartidas->fetchAll();

        $maxVersionSincPartidas = null;
        $partidasForApi = array_map(function ($row) use (&$maxVersionSincPartidas) {
            if ($row['VERSION_SINC'] !== null) {
                $maxVersionSincPartidas = $row['VERSION_SINC'];
            }
            return [
                'cve_doc'     => convertValue($row['CVE_DOC'], 'string'),
                'num_par'     => (int) $row['NUM_PAR'],
                'cve_art'     => convertValue($row['CVE_ART'], 'string'),
                'cant'        => convertValue($row['CANT'], 'numeric'),
                'prec'        => convertValue($row['PREC'], 'numeric'),
                'tot_partida' => convertValue($row['TOT_PARTIDA'], 'numeric'),
                'descr_art'   => convertValue($row['DESCR_ART'], 'string'),
            ];
        }, $partidaRows);

        if (empty($partidasForApi)) {
            $partidaMessage = $applyDelta
                ? 'Partidas OK — sin cambios desde ' . ($checkpointPartidas ?? 'siempre')
                : 'Partidas OK — 0 partidas';
        } else {
            foreach (array_chunk($partidasForApi, 300) as $batch) {
                postVentasPartidas('https://www.macdelnorte.com/api/aspel/ventas', [], $batch, $aspelApiToken);
            }
            $llegoAlLimite = count($partidasForApi) >= HISTORY_BATCH_LIMIT;
            $partidaMessage = 'Partidas OK (' . ($applyDelta ? 'delta' : 'completo') . ') — ' . count($partidasForApi) . ' partidas'
                . ($llegoAlLimite ? ' [llegó al límite de ' . HISTORY_BATCH_LIMIT . ' — falta más backlog, seguirá en la próxima corrida]' : '');

            if ($applyDelta && $maxVersionSincPartidas !== null) {
                guardarCheckpoint($checkpointFile, 'par_factf01_last_sync', convertValue($maxVersionSincPartidas, 'date'));
            }
        }
    } catch (Exception $e) {
        $partidaStatus  = 'error';
        $partidaMessage = 'Partidas ERROR: ' . $e->getMessage();
    }

    // ========================================================
    // BLOQUE 6 — exit SILENCIOSO ✅ AL FINAL con todo enviado
    // ========================================================
    if ($onlySync) {
        if (!$isCli && !headers_sent()) {
            header('Content-Type: text/plain; charset=UTF-8');   // solo en navegador; en consola no aplica y generaba un Warning
        }
        echo "[" . date('Y-m-d H:i:s') . "]" . PHP_EOL;
        echo "[INV]     " . ($invStatus     === 'success' ? 'OK    ' : 'ERROR ') . $invMessage     . PHP_EOL;
        echo "[PRECIO]  " . ($precioStatus  === 'success' ? 'OK    ' : 'ERROR ') . $precioMessage  . PHP_EOL;
        echo "[CLIENTE] " . ($clienteStatus === 'success' ? 'OK    ' : 'ERROR ') . $clienteMessage . PHP_EOL;
        echo "[MONEDA]  " . ($monedaStatus  === 'success' ? 'OK    ' : 'ERROR ') . $monedaMessage  . PHP_EOL;
        echo "[VENTA]   " . ($ventaStatus   === 'success' ? 'OK    ' : 'ERROR ') . $ventaMessage   . PHP_EOL;
        echo "[PARTIDA] " . ($partidaStatus === 'success' ? 'OK    ' : 'ERROR ') . $partidaMessage . PHP_EOL;
        exit;
    }

    // ========================================================
    // BLOQUE 7 — HTML para navegador (muestra inventario completo,
    // no el delta — es la vista de inspección manual)
    // ========================================================
    $jsonRows = json_encode($rows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    ?>
    <!doctype html>
    <html lang="es">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <title>Inventario Aspel — Vistas Completas</title>
      <style>
        :root{--bg:#f4f7fb;--card:#fff;--accent:#00468c;--success:#00a650;--danger:#ef4444;--text:#0b2540;--muted:#6b7280;}
        *{box-sizing:border-box}
        body{font-family:Inter,Segoe UI;background:var(--bg);color:var(--text);margin:0;padding:16px}
        .container{max-width:1600px;margin:0 auto}
        header{background:var(--card);padding:20px;border-radius:8px;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,0.08)}
        h1{margin:0 0 6px;font-size:28px;color:var(--accent)}
        .meta{color:var(--muted);font-size:13px;margin:0}
        .alert{padding:12px 16px;border-radius:8px;margin-bottom:16px;border-left:4px solid}
        .alert.success{background:#e8f5e9;color:#1b5e20;border-color:var(--success)}
        .alert.error{background:#ffebee;color:#c62828;border-color:var(--danger)}
        .tabs{display:flex;gap:4px;margin-bottom:16px;flex-wrap:wrap}
        .tab-btn{background:var(--card);border:1px solid #ddd;padding:10px 14px;border-radius:6px;cursor:pointer;color:var(--muted);font-weight:600;transition:all 0.3s}
        .tab-btn:hover{border-color:var(--accent);color:var(--accent)}
        .tab-btn.active{background:var(--accent);color:#fff;border-color:var(--accent)}
        .tab-content{display:none;background:var(--card);border-radius:8px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.08)}
        .tab-content.active{display:block}
        .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px}
        .stat{background:var(--card);padding:14px;border-radius:8px;border-left:4px solid var(--accent)}
        .stat-value{font-size:28px;font-weight:700;color:var(--accent)}
        .stat-label{color:var(--muted);font-size:12px;margin-top:4px}
        table{width:100%;border-collapse:collapse}
        thead th{background:linear-gradient(180deg,var(--accent),#0b4f8a);color:#fff;padding:10px;text-align:left;font-weight:600;font-size:12px;position:sticky;top:0}
        tbody td{padding:10px;border-top:1px solid #e8e8e8}
        tbody tr:hover{background:#f9f9f9}
        .badge{display:inline-block;padding:4px 8px;border-radius:4px;font-size:11px;color:#fff;font-weight:600}
        .ok{background:var(--success)}.bad{background:var(--danger)}.neutral{background:#9e9e9e}
        .price{color:var(--accent);font-weight:700}
      </style>
    </head>
    <body>
    <div class="container">
      <header>
        <h1>📦 Inventario Aspel SAE</h1>
        <p class="meta">Sincronización y visualización de INVE01</p>
      </header>

      <div class="alert <?php echo $invStatus; ?>">
        <strong><?php echo $invStatus === 'success' ? '✓' : '✗'; ?> Inventario:</strong>
        <?php echo htmlspecialchars($invMessage); ?>
      </div>
      <div class="alert <?php echo $precioStatus; ?>">
        <strong><?php echo $precioStatus === 'success' ? '✓' : '✗'; ?> Precios:</strong>
        <?php echo htmlspecialchars($precioMessage); ?>
      </div>
      <div class="alert <?php echo $clienteStatus; ?>">
        <strong><?php echo $clienteStatus === 'success' ? '✓' : '✗'; ?> Clientes:</strong>
        <?php echo htmlspecialchars($clienteMessage); ?>
      </div>
      <div class="alert <?php echo $monedaStatus; ?>">
        <strong><?php echo $monedaStatus === 'success' ? '✓' : '✗'; ?> Monedas:</strong>
        <?php echo htmlspecialchars($monedaMessage); ?>
      </div>
      <div class="alert <?php echo $ventaStatus; ?>">
        <strong><?php echo $ventaStatus === 'success' ? '✓' : '✗'; ?> Ventas:</strong>
        <?php echo htmlspecialchars($ventaMessage); ?>
      </div>
      <div class="alert <?php echo $partidaStatus; ?>">
        <strong><?php echo $partidaStatus === 'success' ? '✓' : '✗'; ?> Partidas:</strong>
        <?php echo htmlspecialchars($partidaMessage); ?>
      </div>

      <div class="stats">
        <div class="stat">
          <div class="stat-value"><?php echo count($rows); ?></div>
          <div class="stat-label">Total productos</div>
        </div>
        <div class="stat">
          <div class="stat-value"><?php echo count(array_filter($rows, fn($r) => ($r['EXIST'] ?? 0) > 0)); ?></div>
          <div class="stat-label">Con stock</div>
        </div>
        <div class="stat">
          <div class="stat-value"><?php echo count(array_filter($rows, fn($r) => ($r['STATUS'] ?? 'A') === 'A')); ?></div>
          <div class="stat-label">Activos</div>
        </div>
      </div>

      <div class="tabs">
        <button class="tab-btn active" data-tab="tab1">📋 Resumen</button>
        <button class="tab-btn" data-tab="tab2">📦 Inventario</button>
        <button class="tab-btn" data-tab="tab3">💰 Costos</button>
        <button class="tab-btn" data-tab="tab9">📄 JSON</button>
      </div>

      <div id="tab1" class="tab-content active">
        <div style="overflow-x:auto">
          <table>
            <thead><tr><th>SKU</th><th>Nombre</th><th>Stock</th><th>Último Costo</th><th>Tipo</th><th>Estado</th></tr></thead>
            <tbody>
              <?php foreach (array_slice($rows, 0, 50) as $r): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($r['CVE_ART'] ?? ''); ?></strong></td>
                <td><?php echo htmlspecialchars($r['DESCR'] ?? ''); ?></td>
                <td><?php echo $r['EXIST'] ?? 0; ?></td>
                <td class="price"><?php echo number_format($r['ULT_COSTO'] ?? 0, 2); ?></td>
                <td><?php echo htmlspecialchars($r['TIPO_ELE'] ?? '—'); ?></td>
                <td><span class="badge <?php echo ($r['STATUS'] ?? 'A') === 'A' ? 'ok' : 'bad'; ?>">
                  <?php echo ($r['STATUS'] ?? 'A') === 'A' ? 'Activo' : 'Baja'; ?>
                </span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div id="tab2" class="tab-content">
        <div style="overflow-x:auto">
          <table>
            <thead><tr><th>SKU</th><th>Descripción</th><th>Stock Min</th><th>Stock Actual</th><th>Stock Max</th></tr></thead>
            <tbody>
              <?php foreach (array_slice($rows, 0, 50) as $r): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($r['CVE_ART'] ?? ''); ?></strong></td>
                <td><?php echo htmlspecialchars($r['DESCR'] ?? ''); ?></td>
                <td><?php echo $r['STOCK_MIN'] ?? 0; ?></td>
                <td><strong><?php echo $r['EXIST'] ?? 0; ?></strong></td>
                <td><?php echo $r['STOCK_MAX'] ?? 0; ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div id="tab3" class="tab-content">
        <div style="overflow-x:auto">
          <table>
            <thead><tr><th>SKU</th><th>Descripción</th><th>Costo Promedio</th><th>Último Costo</th><th>Moneda</th></tr></thead>
            <tbody>
              <?php foreach (array_slice($rows, 0, 50) as $r): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($r['CVE_ART'] ?? ''); ?></strong></td>
                <td><?php echo htmlspecialchars($r['DESCR'] ?? ''); ?></td>
                <td class="price"><?php echo number_format($r['COSTO_PROM'] ?? 0, 4); ?></td>
                <td class="price"><?php echo number_format($r['ULT_COSTO'] ?? 0, 4); ?></td>
                <td><?php echo htmlspecialchars($r['NUM_MON'] ?? '—'); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div id="tab9" class="tab-content">
        <button onclick="copiarJSON()" style="padding:8px 12px;background:var(--accent);color:#fff;border:none;border-radius:6px;cursor:pointer;margin-bottom:12px">Copiar JSON</button>
        <textarea id="jsonData" style="width:100%;height:600px;padding:12px;border:1px solid #ddd;border-radius:6px;font-family:monospace;font-size:12px"><?php echo json_encode($itemsForApi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></textarea>
      </div>
    </div>

    <script>
      document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
          document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
          btn.classList.add('active');
          document.getElementById(btn.dataset.tab).classList.add('active');
        });
      });
      function copiarJSON() {
        navigator.clipboard.writeText(document.getElementById('jsonData').value)
          .then(() => alert('JSON copiado al portapapeles'));
      }
    </script>
    </body>
    </html>
    <?php

} catch (Exception $e) {
    $isCli = (php_sapi_name() === 'cli');
    if ($isCli) {
        echo "[FATAL ERROR] " . $e->getMessage() . PHP_EOL;
    } else {
        echo "❌ ERROR: " . htmlspecialchars($e->getMessage());
    }
}
