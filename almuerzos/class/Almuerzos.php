<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/clases/Conexion.php';

/**
 * Almuerzos: menús mensuales y pedidos diarios.
 * Tablas: almuerzo_menu_mes, almuerzo_menu_dia, almuerzo_pedido (almuerzos/sql/).
 */
final class Almuerzos
{
    /** Única persona que administra almuerzos: publica el menú y descarga el PDF. */
    public const ENCARGADO_ID = 8;
    /** Horario para pedir (hora de Chile). */
    public const HORA_INICIO = '09:00';
    public const HORA_FIN = '12:00';
    /**
     * Solo para QA: permite al encargado simular el horario abierto para
     * probar fuera de 09:00–12:00. En PRODUCCIÓN dejar en false.
     */
    public const PERMITIR_SIMULAR_HORARIO = true;

    public const OPCIONES = [
        'menu'   => 'Menú del día',
        'hipo'   => 'Hipocalórico',
        'picnic' => 'Picnic',
        'no'     => 'No almuerza',
    ];
    public const MAX_PLATO = 255;

    private Conexion $db;
    private DateTimeZone $zona;

    public function __construct(Conexion $db)
    {
        $this->db = $db;
        $this->zona = new DateTimeZone('America/Santiago');
    }

    public static function esEncargado(int $idUsuario): bool
    {
        return $idUsuario === self::ENCARGADO_ID;
    }

    public function ahora(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->zona);
    }

    public static function esDiaHabil(DateTimeInterface $fecha): bool
    {
        return (int) $fecha->format('N') <= 5;
    }

    public function instalado(): bool
    {
        $n = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM information_schema.TABLES
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('almuerzo_menu_mes','almuerzo_menu_dia','almuerzo_pedido')"
        );
        return (int) ($n['n'] ?? 0) === 3;
    }

    /** Estado del horario de pedidos: abierto | antes | cerrado. */
    public function horario(bool $simular = false): array
    {
        $ahora = $this->ahora();
        $hm = $ahora->format('H:i');
        $habil = self::esDiaHabil($ahora);
        if ($simular) {
            $estado = 'abierto';
        } elseif ($habil && $hm >= self::HORA_INICIO && $hm < self::HORA_FIN) {
            $estado = 'abierto';
        } elseif ($habil && $hm < self::HORA_INICIO) {
            $estado = 'antes';
        } else {
            $estado = 'cerrado';
        }
        $siguiente = $ahora;
        do {
            $siguiente = $siguiente->modify('+1 day');
        } while (!self::esDiaHabil($siguiente));

        return [
            'estado' => $estado,
            'simulado' => $simular,
            'ahora' => $ahora->format('Y-m-d H:i:s'),
            'hoy' => $ahora->format('Y-m-d'),
            'inicio' => self::HORA_INICIO,
            'fin' => self::HORA_FIN,
            'siguiente_habil' => $siguiente->format('Y-m-d'),
        ];
    }

    // ------------------------------------------------------------ menús

    /** Menú de un día, o null si no hay. */
    public function menuDia(string $fecha): ?array
    {
        $f = $this->db->fetchOne(
            'SELECT fecha, menu_dia AS menu, hipocalorico AS hipo, picnic FROM almuerzo_menu_dia WHERE fecha = ?',
            [$fecha]
        );
        return $f ?: null;
    }

    /** Menús de un mes: dias['YYYY-MM-DD'] = [menu, hipo, picnic] + datos de publicación. */
    public function menuMes(int $anio, int $mes): array
    {
        $cab = $this->db->fetchOne(
            "SELECT m.id_menu_mes, m.publicado_en, m.actualizado_en,
                    TRIM(CONCAT(COALESCE(u.nombre,''), ' ', COALESCE(u.apellido_paterno,''))) AS publicado_por
               FROM almuerzo_menu_mes m LEFT JOIN usuarios u ON u.id = m.publicado_por
              WHERE m.anio = ? AND m.mes = ?",
            [$anio, $mes]
        );
        $dias = [];
        if ($cab) {
            foreach ($this->db->fetchAll(
                'SELECT fecha, menu_dia, hipocalorico, picnic FROM almuerzo_menu_dia WHERE id_menu_mes = ? ORDER BY fecha',
                [(int) $cab['id_menu_mes']]
            ) as $f) {
                $dias[$f['fecha']] = ['menu' => $f['menu_dia'], 'hipo' => $f['hipocalorico'], 'picnic' => $f['picnic']];
            }
        }
        return [
            'publicado' => (bool) $cab,
            'publicado_por' => $cab['publicado_por'] ?? null,
            'actualizado_en' => $cab['actualizado_en'] ?? null,
            'dias' => $dias,
        ];
    }

    /**
     * Publica (o reemplaza) el menú de un mes.
     * @param array<string,array{menu:string,hipo:string,picnic:string}> $dias
     */
    public function publicarMes(int $anio, int $mes, array $dias, int $idUsuario): int
    {
        if ($anio < 2020 || $anio > 2100 || $mes < 1 || $mes > 12) {
            throw new RuntimeException('Mes no válido.');
        }
        $limpios = [];
        foreach ($dias as $fecha => $m) {
            $fecha = (string) $fecha;
            $d = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha, $this->zona);
            if (!$d || $d->format('Y-m-d') !== $fecha || (int) $d->format('Y') !== $anio || (int) $d->format('n') !== $mes) {
                throw new RuntimeException('La fecha ' . $fecha . ' no corresponde al mes elegido.');
            }
            if (!self::esDiaHabil($d)) {
                throw new RuntimeException('El ' . $d->format('d-m-Y') . ' es fin de semana.');
            }
            $fila = [];
            foreach (['menu', 'hipo', 'picnic'] as $op) {
                $t = trim((string) preg_replace('/\s+/u', ' ', (string) ($m[$op] ?? '')));
                if ($t === '') {
                    throw new RuntimeException('Falta un menú el ' . $d->format('d-m-Y') . '.');
                }
                if (mb_strlen($t) > self::MAX_PLATO) {
                    throw new RuntimeException('Un menú del ' . $d->format('d-m-Y') . ' supera los ' . self::MAX_PLATO . ' caracteres.');
                }
                $fila[$op] = $t;
            }
            $limpios[$fecha] = $fila;
        }
        if (!$limpios) {
            throw new RuntimeException('El menú no tiene días.');
        }

        $pdo = $this->db->getPDO();
        $pdo->beginTransaction();
        try {
            $this->db->execute(
                'INSERT INTO almuerzo_menu_mes (anio, mes, publicado_por, publicado_en, actualizado_en) VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE publicado_por = VALUES(publicado_por), actualizado_en = VALUES(actualizado_en)',
                [$anio, $mes, $idUsuario, $this->ahora()->format('Y-m-d H:i:s'), $this->ahora()->format('Y-m-d H:i:s')]
            );
            $cab = $this->db->fetchOne('SELECT id_menu_mes FROM almuerzo_menu_mes WHERE anio = ? AND mes = ?', [$anio, $mes]);
            $idMes = (int) $cab['id_menu_mes'];
            // Se reemplaza el mes completo (los pedidos ya hechos se conservan)
            $this->db->execute('DELETE FROM almuerzo_menu_dia WHERE id_menu_mes = ?', [$idMes]);
            foreach ($limpios as $fecha => $f) {
                $this->db->execute(
                    'INSERT INTO almuerzo_menu_dia (fecha, id_menu_mes, menu_dia, hipocalorico, picnic) VALUES (?, ?, ?, ?, ?)',
                    [$fecha, $idMes, $f['menu'], $f['hipo'], $f['picnic']]
                );
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
        return count($limpios);
    }

    // ------------------------------------------------------------ pedidos

    /** Pedidos de una persona en un mes: ['YYYY-MM-DD' => opcion]. */
    public function pedidosUsuarioMes(int $idUsuario, int $anio, int $mes): array
    {
        $desde = sprintf('%04d-%02d-01', $anio, $mes);
        $res = [];
        foreach ($this->db->fetchAll(
            'SELECT fecha, opcion FROM almuerzo_pedido WHERE id_usuario = ? AND fecha >= ? AND fecha < DATE_ADD(?, INTERVAL 1 MONTH)',
            [$idUsuario, $desde, $desde]
        ) as $f) {
            $res[$f['fecha']] = $f['opcion'];
        }
        return $res;
    }

    /** Guarda el pedido de HOY. El horario se valida aquí, no en el navegador. */
    public function pedir(int $idUsuario, string $opcion, bool $simular = false): array
    {
        if (!isset(self::OPCIONES[$opcion])) {
            throw new RuntimeException('Opción no válida.');
        }
        $h = $this->horario($simular);
        if ($h['estado'] !== 'abierto') {
            throw new RuntimeException('El horario para pedir es de ' . self::HORA_INICIO . ' a ' . self::HORA_FIN . ' (lunes a viernes).');
        }
        if (!$this->menuDia($h['hoy'])) {
            throw new RuntimeException('Hoy no hay menú publicado.');
        }
        $this->db->execute(
            // Hora de Chile desde PHP (el reloj del servidor MySQL puede estar en otra zona)
            'INSERT INTO almuerzo_pedido (id_usuario, fecha, opcion, creado_en, actualizado_en) VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE opcion = VALUES(opcion), actualizado_en = VALUES(actualizado_en)',
            [$idUsuario, $h['hoy'], $opcion, $h['ahora'], $h['ahora']]
        );
        return ['fecha' => $h['hoy'], 'opcion' => $opcion];
    }

    /**
     * Usuarios activos con su pedido del día ('' = no respondió),
     * ordenados por opción y nombre.
     */
    public function pedidosDelDia(string $fecha): array
    {
        $filas = $this->db->fetchAll(
            "SELECT u.id,
                    TRIM(CONCAT(COALESCE(u.nombre,''), ' ', COALESCE(u.apellido_paterno,''), ' ', COALESCE(u.apellido_materno,''))) AS nombre,
                    COALESCE(col.nom_colegio, '') AS colegio,
                    COALESCE(p.opcion, '') AS pedido,
                    p.actualizado_en
               FROM usuarios u
          LEFT JOIN almuerzo_pedido p ON p.id_usuario = u.id AND p.fecha = ?
          LEFT JOIN (SELECT uc.id_usuario, MIN(c.nom_colegio) AS nom_colegio
                       FROM usuario_colegio uc JOIN colegio c ON c.id_colegio = uc.id_colegio
                      WHERE uc.estado = 1 AND c.estado = 1
                   GROUP BY uc.id_usuario) col ON col.id_usuario = u.id
              WHERE LOWER(u.estado) = 'activo'",
            [$fecha]
        );
        $orden = ['menu' => 0, 'hipo' => 1, 'picnic' => 2, 'no' => 3, '' => 4];
        $lista = array_map(static fn (array $f): array => [
            'id' => (int) $f['id'],
            'nombre' => $f['nombre'] !== '' ? $f['nombre'] : 'Usuario #' . $f['id'],
            'colegio' => (string) $f['colegio'],
            'pedido' => (string) $f['pedido'],
            'hora' => $f['actualizado_en'] ? substr((string) $f['actualizado_en'], 11, 5) : '',
        ], $filas);
        usort($lista, static fn (array $a, array $b): int =>
            [$orden[$a['pedido']] ?? 9, mb_strtolower($a['nombre'])] <=> [$orden[$b['pedido']] ?? 9, mb_strtolower($b['nombre'])]);
        return $lista;
    }

    /** @return array{menu:int,hipo:int,picnic:int,no:int,sin:int} */
    public static function conteo(array $pedidos): array
    {
        $c = ['menu' => 0, 'hipo' => 0, 'picnic' => 0, 'no' => 0, 'sin' => 0];
        foreach ($pedidos as $p) {
            $c[$p['pedido'] === '' ? 'sin' : $p['pedido']]++;
        }
        return $c;
    }
}
