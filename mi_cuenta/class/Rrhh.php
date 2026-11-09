<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/clases/ConfigPrivada.php';
require_once __DIR__ . '/FuenteRrhh.php';

/**
 * Elige de dónde salen las liquidaciones y documentos y guarda en la sesión
 * lo que viene de Buk por unos minutos (para no llamar a la API en cada clic).
 *
 *   'buk' en config_privada  → FuenteBuk
 *   sin Buk, entorno ≠ produccion → FuenteDemo (datos de ejemplo, marcados)
 *   sin Buk, en producción   → FuenteNinguna (aviso "conexión pendiente")
 */
final class Rrhh
{
    private const CACHE_SEGUNDOS = 600;
    private static ?FuenteRrhh $fuente = null;

    public static function fuente(): FuenteRrhh
    {
        if (self::$fuente !== null) {
            return self::$fuente;
        }
        $buk = ConfigPrivada::opcional('buk');
        if ($buk && !empty($buk['url']) && !empty($buk['token']) && !str_starts_with((string) $buk['token'], 'CAMBIAR')) {
            require_once __DIR__ . '/FuenteBuk.php';
            return self::$fuente = new FuenteBuk($buk);
        }
        $entorno = 'desconocido';
        try {
            $entorno = ConfigPrivada::entorno();
        } catch (Throwable $e) {
        }
        if ($entorno !== 'produccion') {
            require_once __DIR__ . '/FuenteDemo.php';
            return self::$fuente = new FuenteDemo();
        }
        return self::$fuente = new FuenteNinguna();
    }

    public static function liquidaciones(array $persona): array
    {
        return self::cache('liq', $persona, static fn () => self::fuente()->liquidaciones($persona));
    }

    public static function documentos(array $persona): array
    {
        return self::cache('doc', $persona, static fn () => self::fuente()->documentos($persona));
    }

    /** Borra lo guardado (botón "Actualizar desde Buk"). */
    public static function olvidar(): void
    {
        unset($_SESSION['mi_cuenta_cache']);
    }

    private static function cache(string $clave, array $persona, callable $leer): array
    {
        if (self::fuente()->tipo() !== 'buk') {
            return $leer();
        }
        $k = $clave . ':' . (int) $persona['id'];
        $c = $_SESSION['mi_cuenta_cache'][$k] ?? null;
        if (is_array($c) && ($c['t'] ?? 0) > time() - self::CACHE_SEGUNDOS) {
            return $c['d'];
        }
        $datos = $leer();
        $_SESSION['mi_cuenta_cache'][$k] = ['t' => time(), 'd' => $datos];
        return $datos;
    }
}
