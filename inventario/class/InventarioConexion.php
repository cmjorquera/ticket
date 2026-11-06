<?php
/**
 * Conexión del módulo Inventario.
 *
 * El módulo se escribió para la clase MySQL del sistema antiguo y usa
 * mysqli directamente. Esta clase ofrece los mismos métodos que usaba
 * (getConexion, consulta, fetch_assoc, num_rows, escape_string), pero toma
 * las credenciales desde la configuración privada del sistema nuevo
 * (clases/ConfigPrivada.php, sección 'db'), sin contraseñas en el código.
 */

require_once dirname(__DIR__, 2) . '/clases/ConfigPrivada.php';

final class InventarioConexion
{
    private const BASE = 'sistema_panel_central';

    private mysqli $conexion;

    public function __construct()
    {
        $cfg = ConfigPrivada::get('db');
        mysqli_report(MYSQLI_REPORT_OFF);
        $cn = @mysqli_connect(
            (string) ($cfg['host'] ?? 'localhost'),
            (string) ($cfg['usuario'] ?? ''),
            (string) ($cfg['password'] ?? ''),
            (string) ($cfg['prefijo'] ?? '') . self::BASE
        );
        if (!$cn) {
            error_log('[inventario] conexion: ' . mysqli_connect_error());
            throw new RuntimeException('No fue posible conectar con la base de datos.');
        }
        mysqli_set_charset($cn, 'utf8mb4');
        $this->conexion = $cn;
    }

    public function getConexion(): mysqli
    {
        return $this->conexion;
    }

    public function consulta(string $sql)
    {
        $rs = mysqli_query($this->conexion, $sql);
        if ($rs === false) {
            error_log('[inventario] consulta: ' . mysqli_error($this->conexion));
            throw new RuntimeException('Error al consultar la base de datos.');
        }
        return $rs;
    }

    public function fetch_assoc($rs)
    {
        return mysqli_fetch_assoc($rs);
    }

    public function fetch_array($rs)
    {
        return mysqli_fetch_array($rs);
    }

    public function num_rows($rs): int
    {
        return (int) mysqli_num_rows($rs);
    }

    public function escape_string(string $valor): string
    {
        return mysqli_real_escape_string($this->conexion, $valor);
    }
}
