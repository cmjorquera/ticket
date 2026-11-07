<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/clases/Conexion.php';

/**
 * Almuerzos (modelo v2): menús mensuales y elección diaria.
 * Tablas (almuerzos/sql/03_modelo_almuerzos.sql):
 *   almuerzo_tipo, almuerzo_dia_semana, almuerzo_configuracion,
 *   almuerzo_mes → almuerzo_dia → almuerzo_menu, almuerzo_eleccion, almuerzo_cierre.
 *
 * Hacia la pantalla se mantienen las claves cortas de siempre:
 *   menu | hipo | picnic | no   (en la base: codigo menu | hipocalorico | picnic, id_menu NULL = no)
 * Cada menú se entrega como texto "entrada + plato de fondo + postre".
 */
final class Almuerzos
{
    /**
     * Solo para QA: permite al encargado simular el horario abierto para
     * probar fuera del horario. En PRODUCCIÓN dejar en false.
     */
    public const PERMITIR_SIMULAR_HORARIO = true;

    public const OPCIONES = [
        'menu'   => 'Menú del día',
        'hipo'   => 'Hipocalórico',
        'picnic' => 'Picnic',
        'no'     => 'No almuerza',
    ];
    /** clave de pantalla → almuerzo_tipo.codigo */
    private const CODIGOS = ['menu' => 'menu', 'hipo' => 'hipocalorico', 'picnic' => 'picnic'];
    /** Valores por defecto si falta una clave en almuerzo_configuracion */
    private const CONFIG_DEFECTO = [
        'hora_inicio' => '08:00',
        'hora_cierre' => '10:00',
        'id_usuario_encargado' => '8',
        'id_colegio_pdf' => '15',
        'dias_anticipacion' => '31',
        'dias_planificacion' => '6,7',   // N de ISO: 6 = sábado, 7 = domingo
    ];
    public const MAX_PLATO = 160;

    private Conexion $db;
    private ?bool $colImagen = null;
    private DateTimeZone $zona;
    private ?array $config = null;

    public function __construct(Conexion $db)
    {
        $this->db = $db;
        $this->zona = new DateTimeZone('America/Santiago');
    }

    // ------------------------------------------------------------ configuración

    public function config(string $clave): string
    {
        if ($this->config === null) {
            $this->config = self::CONFIG_DEFECTO;
            try {
                foreach ($this->db->fetchAll('SELECT clave, valor FROM almuerzo_configuracion') as $f) {
                    $this->config[$f['clave']] = trim((string) $f['valor']);
                }
            } catch (Throwable $e) {
                error_log('[almuerzos] configuración: ' . $e->getMessage());
            }
        }
        return (string) ($this->config[$clave] ?? '');
    }

    public function horaInicio(): string
    {
        return substr($this->config('hora_inicio'), 0, 5);
    }

    public function horaFin(): string
    {
        return substr($this->config('hora_cierre'), 0, 5);
    }

    public function esEncargado(int $idUsuario): bool
    {
        return $idUsuario > 0 && $idUsuario === (int) $this->config('id_usuario_encargado');
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
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME IN ('almuerzo_tipo','almuerzo_dia_semana','almuerzo_configuracion',
                                   'almuerzo_mes','almuerzo_dia','almuerzo_menu','almuerzo_eleccion')"
        );
        return (int) ($n['n'] ?? 0) === 7;
    }

    /** Estado del horario: abierto | antes | cerrado. */
    public function horario(bool $simular = false): array
    {
        $ahora = $this->ahora();
        $hm = $ahora->format('H:i');
        $habil = self::esDiaHabil($ahora);
        if ($simular || ($habil && $hm >= $this->horaInicio() && $hm < $this->horaFin())) {
            $estado = 'abierto';
        } elseif ($habil && $hm < $this->horaInicio()) {
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
            'inicio' => $this->horaInicio(),
            'fin' => $this->horaFin(),
            'siguiente_habil' => $siguiente->format('Y-m-d'),
        ];
    }

    /**
     * Planificación semanal: los días de 'dias_planificacion' (sábado y domingo)
     * cada persona deja elegido el almuerzo de lunes a viernes de la semana siguiente.
     */
    public function planificacion(bool $simular = false): array
    {
        $ahora = $this->ahora();
        $dias = array_values(array_filter(array_map('intval', explode(',', $this->config('dias_planificacion'))),
            static fn (int $n): bool => $n >= 1 && $n <= 7));
        $abierta = $simular || in_array((int) $ahora->format('N'), $dias, true);
        $lunes = $ahora->modify('monday next week')->setTime(0, 0);
        $fechas = [];
        for ($i = 0; $i < 5; $i++) {
            $fechas[] = $lunes->modify("+$i day")->format('Y-m-d');
        }
        // Próximo día en que se abre (para avisar cuando está cerrada)
        $abre = null;
        if ($dias) {
            $d = $ahora;
            do {
                $d = $d->modify('+1 day');
            } while (!in_array((int) $d->format('N'), $dias, true));
            $abre = $d->format('Y-m-d');
        }
        return ['abierta' => $abierta, 'simulada' => $simular && !in_array((int) $ahora->format('N'), $dias, true),
                'fechas' => $fechas, 'desde' => $fechas[0], 'hasta' => $fechas[4], 'abre' => $abre];
    }

    /** Semana a planificar: menús de cada día y lo que ya eligió la persona. */
    public function semana(int $idUsuario, bool $simular = false): array
    {
        $p = $this->planificacion($simular);
        $menus = $this->menusEntre($p['desde'], $p['hasta']);
        $pedidos = $this->pedidosUsuarioEntre($idUsuario, $p['desde'], $p['hasta']);
        $p['dias'] = array_map(static fn (string $f): array => [
            'fecha' => $f,
            'menu' => $menus[$f] ?? null,
            'pedido' => $pedidos[$f] ?? null,
        ], $p['fechas']);
        return $p;
    }

    // ------------------------------------------------------------ menús

    /** "entrada + fondo + postre" (omite las partes vacías). El picnic es solo el sándwich. */
    private static function textoMenu(array $f): string
    {
        if (($f['codigo'] ?? '') === 'picnic') {
            return trim((string) ($f['plato_fondo'] ?? ''));
        }
        return implode(' + ', array_filter([
            trim((string) ($f['entrada'] ?? '')),
            trim((string) ($f['plato_fondo'] ?? '')),
            trim((string) ($f['postre'] ?? '')),
        ], static fn (string $t): bool => $t !== ''));
    }

    /** clave de pantalla desde almuerzo_tipo.codigo */
    private static function clave(string $codigo): string
    {
        return (string) (array_search($codigo, self::CODIGOS, true) ?: $codigo);
    }

    /** ¿Ya se ejecutó sql/06_picnic_e_imagen.sql? (columna almuerzo_menu.imagen) */
    private function hayColumnaImagen(): bool
    {
        if ($this->colImagen === null) {
            $this->colImagen = (bool) $this->db->fetchOne("SHOW COLUMNS FROM almuerzo_menu LIKE 'imagen'");
        }
        return $this->colImagen;
    }

    /**
     * Menús de un rango de fechas:
     * ['AAAA-MM-DD' => ['menu'=>…, 'hipo'=>…, 'picnic'=>…, 'img'=>['menu'=>ruta, …]]]
     * 'img' solo trae las fotos cargadas; sin foto la pantalla usa la imagen referencial.
     */
    private function menusEntre(string $desde, string $hasta): array
    {
        $dias = [];
        $img = $this->hayColumnaImagen() ? 'm.imagen' : 'NULL AS imagen';
        foreach ($this->db->fetchAll(
            "SELECT d.fecha, t.codigo, m.entrada, m.plato_fondo, m.postre, $img
               FROM almuerzo_dia d
               JOIN almuerzo_mes ms  ON ms.id_mes = d.id_mes AND ms.estado = 'publicado'
               JOIN almuerzo_menu m  ON m.id_dia_menu = d.id_dia_menu
               JOIN almuerzo_tipo t  ON t.id_tipo = m.id_tipo AND t.activo = 1
              WHERE d.fecha BETWEEN ? AND ? AND d.hay_servicio = 1
           ORDER BY d.fecha, t.orden",
            [$desde, $hasta]
        ) as $f) {
            $k = self::clave($f['codigo']);
            $dias[$f['fecha']][$k] = self::textoMenu($f);
            $ruta = trim((string) ($f['imagen'] ?? ''));
            if ($ruta !== '' && preg_match('~^(https://|img/)[^"\'<>\s]+$~', $ruta)) {
                $dias[$f['fecha']]['img'][$k] = $ruta;
            }
        }
        // Solo días con los tres menús completos
        return array_filter($dias, static fn (array $m): bool => isset($m['menu'], $m['hipo'], $m['picnic']));
    }

    /** Menú de un día, o null si no hay servicio. */
    public function menuDia(string $fecha): ?array
    {
        $dias = $this->menusEntre($fecha, $fecha);
        return isset($dias[$fecha]) ? ['fecha' => $fecha] + $dias[$fecha] : null;
    }

    /** Menú de un mes + datos de publicación. */
    public function menuMes(int $anio, int $mes): array
    {
        $cab = $this->db->fetchOne(
            "SELECT m.estado, m.publicado_en, m.actualizado_en,
                    TRIM(CONCAT(COALESCE(u.nombre,''), ' ', COALESCE(u.apellido_paterno,''))) AS publicado_por
               FROM almuerzo_mes m LEFT JOIN usuarios u ON u.id = m.id_usuario
              WHERE m.anio = ? AND m.mes = ?",
            [$anio, $mes]
        );
        $desde = sprintf('%04d-%02d-01', $anio, $mes);
        $hasta = (new DateTimeImmutable($desde))->modify('last day of this month')->format('Y-m-d');
        return [
            'publicado' => ($cab['estado'] ?? '') === 'publicado',
            'publicado_por' => $cab['publicado_por'] ?? null,
            'actualizado_en' => $cab['actualizado_en'] ?? null,
            'dias' => $this->menusEntre($desde, $hasta),
        ];
    }

    /** "entrada + fondo + postre" → [entrada, fondo, postre]. Con 1 parte = fondo; con 2 = fondo + postre. */
    private static function partes(string $texto): array
    {
        $p = array_values(array_filter(array_map('trim', explode('+', $texto)), static fn (string $t): bool => $t !== ''));
        return match (count($p)) {
            0 => [null, '', null],
            1 => [null, $p[0], null],
            2 => [null, $p[0], $p[1]],
            default => [$p[0], $p[1], implode(' + ', array_slice($p, 2))],
        };
    }

    /**
     * Publica (o actualiza) el menú de un mes. Los días que no vienen en la carga
     * quedan sin servicio (no se borran, para no perder elecciones ya hechas).
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
            foreach (self::CODIGOS as $clave => $codigo) {
                $t = trim((string) preg_replace('/\s+/u', ' ', (string) ($m[$clave] ?? '')));
                // El picnic es siempre un sándwich: todo el texto va como plato principal
                [$entrada, $fondo, $postre] = $codigo === 'picnic' ? [null, $t, null] : self::partes($t);
                if ($fondo === '') {
                    throw new RuntimeException('Falta un menú el ' . $d->format('d-m-Y') . '.');
                }
                foreach ([$entrada, $fondo, $postre] as $parte) {
                    if ($parte !== null && mb_strlen($parte) > self::MAX_PLATO) {
                        throw new RuntimeException('Un plato del ' . $d->format('d-m-Y') . ' supera los ' . self::MAX_PLATO . ' caracteres.');
                    }
                }
                $limpios[$fecha][$codigo] = [$entrada, $fondo, $postre];
            }
        }
        if (!$limpios) {
            throw new RuntimeException('El menú no tiene días.');
        }

        $tipos = [];
        foreach ($this->db->fetchAll('SELECT id_tipo, codigo FROM almuerzo_tipo') as $t) {
            $tipos[$t['codigo']] = (int) $t['id_tipo'];
        }
        $ahora = $this->ahora()->format('Y-m-d H:i:s');

        $pdo = $this->db->getPDO();
        $pdo->beginTransaction();
        try {
            $this->db->execute(
                "INSERT INTO almuerzo_mes (anio, mes, estado, id_usuario, publicado_en, creado_en, actualizado_en)
                 VALUES (?, ?, 'publicado', ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE estado = 'publicado', id_usuario = VALUES(id_usuario),
                                         publicado_en = VALUES(publicado_en), actualizado_en = VALUES(actualizado_en)",
                [$anio, $mes, $idUsuario, $ahora, $ahora, $ahora]
            );
            $idMes = (int) $this->db->fetchOne('SELECT id_mes FROM almuerzo_mes WHERE anio = ? AND mes = ?', [$anio, $mes])['id_mes'];

            // Días que no vienen en esta carga: sin servicio
            $marcas = implode(',', array_fill(0, count($limpios), '?'));
            $this->db->execute(
                "UPDATE almuerzo_dia SET hay_servicio = 0 WHERE id_mes = ? AND fecha NOT IN ($marcas)",
                array_merge([$idMes], array_keys($limpios))
            );

            foreach ($limpios as $fecha => $menus) {
                $this->db->execute(
                    'INSERT INTO almuerzo_dia (id_mes, fecha, id_dia, hay_servicio) VALUES (?, ?, ?, 1)
                     ON DUPLICATE KEY UPDATE id_mes = VALUES(id_mes), hay_servicio = 1',
                    [$idMes, $fecha, (int) (new DateTimeImmutable($fecha))->format('N')]
                );
                $idDia = (int) $this->db->fetchOne('SELECT id_dia_menu FROM almuerzo_dia WHERE fecha = ?', [$fecha])['id_dia_menu'];
                foreach ($menus as $codigo => [$entrada, $fondo, $postre]) {
                    if (!isset($tipos[$codigo])) {
                        throw new RuntimeException('Falta el tipo de menú "' . $codigo . '" en almuerzo_tipo.');
                    }
                    $this->db->execute(
                        'INSERT INTO almuerzo_menu (id_dia_menu, id_tipo, entrada, plato_fondo, postre) VALUES (?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE entrada = VALUES(entrada), plato_fondo = VALUES(plato_fondo), postre = VALUES(postre)',
                        [$idDia, $tipos[$codigo], $entrada, $fondo, $postre]
                    );
                }
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

    // ------------------------------------------------------------ elecciones

    /** Elecciones de una persona en un mes: ['AAAA-MM-DD' => menu|hipo|picnic|no]. */
    public function pedidosUsuarioMes(int $idUsuario, int $anio, int $mes): array
    {
        $desde = sprintf('%04d-%02d-01', $anio, $mes);
        $hasta = (new DateTimeImmutable($desde))->modify('last day of this month')->format('Y-m-d');
        return $this->pedidosUsuarioEntre($idUsuario, $desde, $hasta);
    }

    /** Elecciones de una persona entre dos fechas (inclusive). */
    public function pedidosUsuarioEntre(int $idUsuario, string $desde, string $hasta): array
    {
        $res = [];
        foreach ($this->db->fetchAll(
            "SELECT d.fecha, t.codigo
               FROM almuerzo_eleccion e
               JOIN almuerzo_dia d       ON d.id_dia_menu = e.id_dia_menu
          LEFT JOIN almuerzo_menu m      ON m.id_menu = e.id_menu
          LEFT JOIN almuerzo_tipo t      ON t.id_tipo = m.id_tipo
              WHERE e.id_usuario = ? AND d.fecha BETWEEN ? AND ?",
            [$idUsuario, $desde, $hasta]
        ) as $f) {
            $res[$f['fecha']] = $f['codigo'] === null ? 'no' : self::clave((string) $f['codigo']);
        }
        return $res;
    }

    /**
     * Guarda una elección. El horario se valida aquí, no en el navegador.
     *  - Hoy: dentro del horario diario (hora_inicio a hora_cierre) → origen 'diaria'.
     *  - Un día de la semana siguiente: solo con la planificación abierta
     *    (sábado y domingo) → origen 'anticipada'.
     */
    public function pedir(int $idUsuario, string $opcion, bool $simular = false, ?string $fecha = null): array
    {
        if (!isset(self::OPCIONES[$opcion])) {
            throw new RuntimeException('Opción no válida.');
        }
        $h = $this->horario($simular);
        $fecha = ($fecha === null || $fecha === '') ? $h['hoy'] : $fecha;
        if ($fecha === $h['hoy']) {
            if ($h['estado'] !== 'abierto') {
                throw new RuntimeException('El horario para elegir el almuerzo de hoy es de ' . $h['inicio'] . ' a ' . $h['fin'] . ' (lunes a viernes).');
            }
            $origen = 'diaria';
        } else {
            $p = $this->planificacion($simular);
            if (!in_array($fecha, $p['fechas'], true)) {
                throw new RuntimeException('Solo puedes dejar elegidos los almuerzos de la próxima semana.');
            }
            if (!$p['abierta']) {
                throw new RuntimeException('La semana se planifica el sábado y el domingo. Durante la semana eliges el almuerzo del día entre las ' . $h['inicio'] . ' y las ' . $h['fin'] . '.');
            }
            $origen = 'anticipada';
        }
        $dia = $this->db->fetchOne(
            "SELECT d.id_dia_menu FROM almuerzo_dia d
               JOIN almuerzo_mes ms ON ms.id_mes = d.id_mes AND ms.estado = 'publicado'
              WHERE d.fecha = ? AND d.hay_servicio = 1",
            [$fecha]
        );
        if (!$dia) {
            throw new RuntimeException($fecha === $h['hoy'] ? 'Hoy no hay menú publicado.' : 'Ese día todavía no tiene menú publicado.');
        }
        $idMenu = null;
        if ($opcion !== 'no') {
            $m = $this->db->fetchOne(
                'SELECT m.id_menu FROM almuerzo_menu m JOIN almuerzo_tipo t ON t.id_tipo = m.id_tipo
                  WHERE m.id_dia_menu = ? AND t.codigo = ? AND t.activo = 1',
                [(int) $dia['id_dia_menu'], self::CODIGOS[$opcion]]
            );
            if (!$m) {
                throw new RuntimeException('Esa opción no está disponible ese día.');
            }
            $idMenu = (int) $m['id_menu'];
        }
        // Hora de Chile desde PHP (el reloj del MySQL de QA está en otra zona)
        $this->db->execute(
            "INSERT INTO almuerzo_eleccion (id_usuario, id_dia_menu, id_menu, origen, creado_en, actualizado_en)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE id_menu = VALUES(id_menu), origen = VALUES(origen), actualizado_en = VALUES(actualizado_en)",
            [$idUsuario, (int) $dia['id_dia_menu'], $idMenu, $origen, $h['ahora'], $h['ahora']]
        );
        return ['fecha' => $fecha, 'opcion' => $opcion, 'origen' => $origen];
    }

    /**
     * Usuarios activos del colegio configurado (id_colegio_pdf, 15 = Seduc SpA)
     * con su elección del día ('' = no respondió), ordenados por opción y nombre.
     */
    public function pedidosDelDia(string $fecha): array
    {
        $filas = $this->db->fetchAll(
            "SELECT u.id,
                    TRIM(CONCAT(COALESCE(u.nombre,''), ' ', COALESCE(u.apellido_paterno,''), ' ', COALESCE(u.apellido_materno,''))) AS nombre,
                    c.nom_colegio AS colegio,
                    CASE WHEN e.id_eleccion IS NULL THEN '' WHEN e.id_menu IS NULL THEN 'no' ELSE t.codigo END AS codigo,
                    e.actualizado_en
               FROM usuarios u
               JOIN usuario_colegio uc ON uc.id_usuario = u.id AND uc.estado = 1 AND uc.id_colegio = ?
               JOIN colegio c          ON c.id_colegio = uc.id_colegio
          LEFT JOIN almuerzo_dia d     ON d.fecha = ?
          LEFT JOIN almuerzo_eleccion e ON e.id_usuario = u.id AND e.id_dia_menu = d.id_dia_menu
          LEFT JOIN almuerzo_menu m    ON m.id_menu = e.id_menu
          LEFT JOIN almuerzo_tipo t    ON t.id_tipo = m.id_tipo
              WHERE LOWER(u.estado) = 'activo'
           GROUP BY u.id, nombre, colegio, codigo, e.actualizado_en",
            [(int) $this->config('id_colegio_pdf'), $fecha]
        );
        $orden = ['menu' => 0, 'hipo' => 1, 'picnic' => 2, 'no' => 3, '' => 4];
        $lista = array_map(static fn (array $f): array => [
            'id' => (int) $f['id'],
            'nombre' => $f['nombre'] !== '' ? $f['nombre'] : 'Usuario #' . $f['id'],
            'colegio' => (string) $f['colegio'],
            'pedido' => in_array($f['codigo'], ['', 'no'], true) ? (string) $f['codigo'] : self::clave((string) $f['codigo']),
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
