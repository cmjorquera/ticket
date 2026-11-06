<?php
declare(strict_types=1);

/**
 * ConfigPrivada — carga las credenciales del sistema desde un archivo
 * que NO está en el repositorio ni dentro del directorio público.
 *
 * Orden de búsqueda (se usa el primero que exista):
 *   1. Carpeta "config_privada/ticket_config.php" subiendo desde esta
 *      carpeta hasta 4 niveles. En QA:
 *        /home3/crist668/config_privada/ticket_config.php
 *   2. "config_privada.local.php" en la raíz del proyecto
 *      (solo para desarrollo local; está en .gitignore).
 *
 * No depende de HTTP_HOST ni de ningún dato enviado por el navegador.
 *
 * Uso:
 *   $db = ConfigPrivada::get('db');            // array completo
 *   $host = ConfigPrivada::get('db')['host'];
 *
 * El formato del archivo está en config_privada.example.php.
 */
final class ConfigPrivada
{
    private const ARCHIVO_PRIVADO = 'config_privada/ticket_config.php';
    private const ARCHIVO_LOCAL   = 'config_privada.local.php';
    private const NIVELES_MAXIMOS = 4;

    private static ?array $config = null;

    /**
     * Devuelve una sección de la configuración (por ejemplo 'db').
     *
     * @throws RuntimeException si la sección no existe.
     */
    public static function get(string $seccion): array
    {
        $config = self::cargar();

        if (!isset($config[$seccion]) || !is_array($config[$seccion])) {
            self::fallar("Falta la sección '$seccion' en la configuración privada.");
        }

        return $config[$seccion];
    }

    /** Entorno declarado en la configuración: 'local', 'qa' o 'produccion'. */
    public static function entorno(): string
    {
        return (string)(self::cargar()['entorno'] ?? 'desconocido');
    }

    private static function cargar(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        $ruta = self::buscarArchivo();
        if ($ruta === null) {
            self::fallar('No se encontró el archivo de configuración privada.');
        }

        $datos = require $ruta;
        if (!is_array($datos)) {
            self::fallar('El archivo de configuración privada no devuelve un arreglo.');
        }

        self::$config = $datos;
        return self::$config;
    }

    private static function buscarArchivo(): ?string
    {
        // 1. Carpeta config_privada fuera del directorio público
        $dir = __DIR__;
        for ($i = 0; $i <= self::NIVELES_MAXIMOS; $i++) {
            $candidato = $dir . '/' . self::ARCHIVO_PRIVADO;
            if (is_file($candidato) && is_readable($candidato)) {
                return $candidato;
            }
            $padre = dirname($dir);
            if ($padre === $dir) {
                break;
            }
            $dir = $padre;
        }

        // 2. Archivo local de desarrollo en la raíz del proyecto
        $local = dirname(__DIR__) . '/' . self::ARCHIVO_LOCAL;
        if (is_file($local) && is_readable($local)) {
            return $local;
        }

        return null;
    }

    /**
     * Registra el detalle en el log del servidor y muestra al usuario
     * un mensaje genérico, sin rutas internas ni credenciales.
     */
    private static function fallar(string $detalle): void
    {
        error_log('[ConfigPrivada] ' . $detalle);
        throw new RuntimeException('Error de configuración del sistema. Contacte al administrador.');
    }
}
