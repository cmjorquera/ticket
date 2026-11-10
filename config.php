<?php
declare(strict_types=1);

/**
 * Configuración pública compartida entre QA y producción.
 * Los datos específicos de cada servidor se obtienen exclusivamente desde
 * <home>/config_privada/ticket_config.php (fuera de la raíz web).
 *
 * Sección opcional recomendada en la configuración privada:
 * 'app' => ['base_url' => 'https://dominio.ejemplo.cl/']
 */
require_once __DIR__ . '/clases/ConfigPrivada.php';

$entorno = ConfigPrivada::entorno();
$urlsPorEntorno = [
    'qa'         => 'https://sistemabase.webnia.cl/',
    'produccion' => 'https://acceso.seduc.cl/',
];
$app = ConfigPrivada::opcional('app') ?? [];
$baseUrl = $app['base_url'] ?? ($urlsPorEntorno[$entorno] ?? null);

if (!is_string($baseUrl) || !filter_var($baseUrl, FILTER_VALIDATE_URL)
    || !in_array(strtolower((string) parse_url($baseUrl, PHP_URL_SCHEME)), ['https', 'http'], true)) {
    error_log('[config] Falta app.base_url válida para el entorno configurado.');
    throw new RuntimeException('Error de configuración del sistema. Contacte al administrador.');
}

$dbConfig = ConfigPrivada::get('db');
$prefijo = $dbConfig['prefijo'] ?? null;
if (!is_string($prefijo) || $prefijo === '') {
    error_log('[config] Falta db.prefijo en la configuración privada.');
    throw new RuntimeException('Error de configuración del sistema. Contacte al administrador.');
}

define('BASE_URL', rtrim($baseUrl, '/') . '/');
define('APP_NAME', 'SEDUC Chile - Panel Central');
define('DEBUG_MODE', false);
define('DB_PREFIX', $prefijo);
