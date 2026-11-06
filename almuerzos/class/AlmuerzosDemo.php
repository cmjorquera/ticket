<?php
declare(strict_types=1);

/**
 * Almuerzos – reglas y datos de ejemplo (etapa 1, sin tablas propias).
 * Cuando exista almuerzo_pedido, pedidosDelDia() pasa a leer de la base.
 */
final class AlmuerzosDemo
{
    /** Única persona que administra almuerzos: carga el menú y descarga el PDF. */
    public const ENCARGADO_ID = 8;

    public const OPCIONES = [
        'menu'   => 'Menú del día',
        'hipo'   => 'Hipocalórico',
        'picnic' => 'Picnic',
        'no'     => 'No almuerza',
        ''       => 'Sin pedido',
    ];

    public static function esEncargado(int $idUsuario): bool
    {
        return $idUsuario === self::ENCARGADO_ID;
    }

    public static function esDiaHabil(DateTimeInterface $fecha): bool
    {
        return (int) $fecha->format('N') <= 5;
    }

    /**
     * Pedido inventado (siempre el mismo para la misma persona y fecha):
     * ~50 % menú, 20 % hipocalórico, 15 % picnic, 5 % no almuerza, 10 % sin pedido.
     */
    public static function pedidoEjemplo(int $idUsuario, string $fecha): string
    {
        $n = crc32($fecha . '#' . $idUsuario) % 20;
        return match (true) {
            $n < 10 => 'menu',
            $n < 14 => 'hipo',
            $n < 17 => 'picnic',
            $n < 18 => 'no',
            default => '',
        };
    }

    /**
     * Usuarios activos con su pedido del día, ordenados por opción y nombre.
     * @return array<int,array{id:int,nombre:string,colegio:string,pedido:string}>
     */
    public static function pedidosDelDia(Conexion $db, DateTimeInterface $fecha): array
    {
        $dia = $fecha->format('Y-m-d');
        $filas = $db->fetchAll(
            "SELECT u.id,
                    TRIM(CONCAT(COALESCE(u.nombre,''), ' ', COALESCE(u.apellido_paterno,''), ' ', COALESCE(u.apellido_materno,''))) AS nombre,
                    COALESCE(col.nom_colegio, '') AS colegio
               FROM usuarios u
          LEFT JOIN (SELECT uc.id_usuario, MIN(c.nom_colegio) AS nom_colegio
                       FROM usuario_colegio uc JOIN colegio c ON c.id_colegio = uc.id_colegio
                      WHERE uc.estado = 1 AND c.estado = 1
                   GROUP BY uc.id_usuario) col ON col.id_usuario = u.id
              WHERE LOWER(u.estado) = 'activo'"
        );
        $orden = array_flip(array_keys(self::OPCIONES));
        $lista = array_map(static fn (array $f): array => [
            'id' => (int) $f['id'],
            'nombre' => $f['nombre'] !== '' ? $f['nombre'] : 'Usuario #' . $f['id'],
            'colegio' => (string) $f['colegio'],
            'pedido' => self::pedidoEjemplo((int) $f['id'], $dia),
        ], $filas);
        usort($lista, static fn (array $a, array $b): int =>
            [$orden[$a['pedido']], mb_strtolower($a['nombre'])] <=> [$orden[$b['pedido']], mb_strtolower($b['nombre'])]);
        return $lista;
    }

    /** @return array<string,int> conteo por opción */
    public static function conteo(array $pedidos): array
    {
        $c = array_fill_keys(array_keys(self::OPCIONES), 0);
        foreach ($pedidos as $p) {
            $c[$p['pedido']]++;
        }
        return $c;
    }
}
