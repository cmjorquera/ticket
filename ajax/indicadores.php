<?php
declare(strict_types=1);

/**
 * Indicadores económicos para la pantalla de login (UF, Dólar, Euro, IPC).
 * Fuente: https://mindicador.cl/api (datos públicos del Banco Central).
 *
 * - Se consulta desde el servidor y se guarda en caché 1 hora, para no
 *   depender de la API en cada carga ni exponer la IP de los usuarios.
 * - Si la API falla, se devuelve el último valor en caché (aunque esté
 *   vencido) o valores vacíos. Nunca rompe el login.
 * - No requiere sesión: solo entrega datos públicos.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=900');

const INDICADORES_URL = 'https://mindicador.cl/api';
const INDICADORES_TTL = 3600;

$cache = sys_get_temp_dir() . '/seduc_indicadores_cache.json';

function leerCache(string $ruta): ?array
{
    if (!is_file($ruta)) {
        return null;
    }
    $datos = json_decode((string)file_get_contents($ruta), true);
    return is_array($datos) ? $datos : null;
}

function consultarApi(): ?array
{
    $json = false;
    if (function_exists('curl_init')) {
        $ch = curl_init(INDICADORES_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_USERAGENT      => 'SEDUC-Panel-Central/1.0',
        ]);
        $json = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200) {
            $json = false;
        }
    } else {
        $ctx  = stream_context_create(['http' => ['timeout' => 5]]);
        $json = @file_get_contents(INDICADORES_URL, false, $ctx);
    }
    if ($json === false) {
        return null;
    }

    $api = json_decode((string)$json, true);
    if (!is_array($api)) {
        return null;
    }

    $valor = static fn(string $k): ?float =>
        isset($api[$k]['valor']) && is_numeric($api[$k]['valor']) ? (float)$api[$k]['valor'] : null;

    $fecha = isset($api['uf']['fecha']) ? substr((string)$api['uf']['fecha'], 0, 10) : date('Y-m-d');

    return [
        'uf'     => $valor('uf'),
        'dolar'  => $valor('dolar'),
        'euro'   => $valor('euro'),
        'ipc'    => $valor('ipc'),
        'fecha'  => $fecha,
        'fuente' => 'mindicador.cl',
        'obtenido' => time(),
    ];
}

$enCache = leerCache($cache);
$vigente = $enCache !== null && (time() - (int)($enCache['obtenido'] ?? 0)) < INDICADORES_TTL;

if ($vigente) {
    echo json_encode($enCache);
    exit;
}

$nuevos = consultarApi();
if ($nuevos !== null && $nuevos['uf'] !== null) {
    @file_put_contents($cache, json_encode($nuevos), LOCK_EX);
    echo json_encode($nuevos);
    exit;
}

// API caída: último dato conocido o vacío
echo json_encode($enCache ?? ['uf' => null, 'dolar' => null, 'euro' => null, 'ipc' => null, 'fecha' => null]);
