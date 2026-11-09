<?php
declare(strict_types=1);

require_once __DIR__ . '/../../clases/Conexion.php';
require_once __DIR__ . '/../../clases/RolesUsuario.php';

/**
 * Módulo Eventos: calendario institucional tipo Google Calendar.
 * Tablas: eventos, eventos_invitados, eventos_historial (eventos/sql/01_eventos.sql)
 * Lógica: eventos/logica/LOGICA.txt
 *
 * Quién ve qué:
 *   super admin              todos los eventos
 *   resto                    eventos de sus colegios + donde es responsable,
 *                            creador o invitado
 * Quién edita / cancela:     super admin, creador, responsable o admin del colegio del evento
 */
final class Eventos
{
    public const TIPOS = [
        'reunion' => ['Reunión', '#2563eb'],
        'capacitacion' => ['Capacitación', '#059669'],
        'celebracion' => ['Celebración', '#d97706'],
        'actividad escolar' => ['Actividad escolar', '#7c3aed'],
        'otro' => ['Otro', '#0f766e'],
    ];
    public const ESTADOS = ['programado' => 'Programado', 'finalizado' => 'Finalizado', 'cancelado' => 'Cancelado'];
    public const MAX_INVITADOS = 60;
    private const ACTIVO = "(e.eliminado = '0' OR e.eliminado = 0 OR e.eliminado IS NULL)";

    private Conexion $db;
    private RolesUsuario $roles;

    public function __construct(Conexion $db)
    {
        $this->db = $db;
        $this->roles = new RolesUsuario($db);
    }

    public function esSuper(int $actor): bool
    {
        return $this->roles->esSuperAdmin($actor);
    }

    /** Colegios donde la persona tiene alguna asignación activa. @return int[] */
    public function colegiosDe(int $actor): array
    {
        return array_map('intval', array_column($this->db->fetchAll(
            'SELECT DISTINCT id_colegio FROM usuario_colegio WHERE id_usuario = ? AND estado = 1', [$actor]
        ), 'id_colegio'));
    }

    /** Colegios en los que puede crear eventos (super admin: todos). */
    public function colegiosParaCrear(int $actor): array
    {
        if ($this->esSuper($actor)) {
            return $this->db->fetchAll('SELECT id_colegio AS id, nom_colegio AS nombre FROM colegio WHERE estado = 1 ORDER BY id_colegio = 15 DESC, nom_colegio');
        }
        $ids = $this->colegiosDe($actor);
        if (!$ids) {
            return [];
        }
        return $this->db->fetchAll(
            'SELECT id_colegio AS id, nom_colegio AS nombre FROM colegio WHERE id_colegio IN (' . implode(',', $ids) . ') ORDER BY nom_colegio'
        );
    }

    /** @return array{0:string,1:array} condición SQL de eventos visibles para $actor */
    private function visibles(int $actor): array
    {
        if ($this->esSuper($actor)) {
            return ['1 = 1', []];
        }
        $cond = 'e.responsable_id = ? OR e.creado_por = ? OR e.id IN (SELECT id_evento FROM eventos_invitados WHERE id_usuario = ?)';
        $p = [$actor, $actor, $actor];
        if ($ids = $this->colegiosDe($actor)) {
            $cond .= ' OR e.id_colegio IN (' . implode(',', $ids) . ')';
        }
        return ['(' . $cond . ')', $p];
    }

    public function puedeEditar(array $e, int $actor): bool
    {
        if ($this->esSuper($actor) || (int) ($e['creado_por'] ?? 0) === $actor || (int) $e['responsable_id'] === $actor) {
            return true;
        }
        return in_array((int) ($e['id_colegio'] ?? 0), $this->roles->alcance($actor)['colegios_admin'], true);
    }

    /** Eventos que cruzan el rango [desde, hasta) — formato para el calendario. */
    public function listar(int $actor, string $desde, string $hasta, array $filtros = []): array
    {
        [$cond, $p] = $this->visibles($actor);
        $sql = "SELECT e.*, c.nom_colegio, TRIM(CONCAT_WS(' ', r.nombre, r.apellido_paterno)) AS responsable,
                       (SELECT respuesta FROM eventos_invitados i WHERE i.id_evento = e.id AND i.id_usuario = ? LIMIT 1) AS mi_respuesta
                  FROM eventos e
             LEFT JOIN colegio c ON c.id_colegio = e.id_colegio
             LEFT JOIN usuarios r ON r.id = e.responsable_id
                 WHERE " . self::ACTIVO . " AND {$cond}
                   AND e.fecha_inicio < ? AND COALESCE(e.fecha_fin, e.fecha_inicio) >= ?";
        $params = array_merge([$actor], $p, [$hasta, $desde]);
        if (!empty($filtros['colegio'])) {
            $sql .= ' AND e.id_colegio = ?';
            $params[] = (int) $filtros['colegio'];
        }
        if (!empty($filtros['tipo']) && isset(self::TIPOS[$filtros['tipo']])) {
            $sql .= ' AND e.tipo_evento = ?';
            $params[] = $filtros['tipo'];
        }
        if (!empty($filtros['mios'])) {
            $sql .= ' AND (e.responsable_id = ? OR e.creado_por = ? OR e.id IN (SELECT id_evento FROM eventos_invitados WHERE id_usuario = ?))';
            array_push($params, $actor, $actor, $actor);
        }
        if (empty($filtros['cancelados'])) {
            $sql .= " AND e.estado <> 'cancelado'";
        }
        $sql .= ' ORDER BY e.fecha_inicio, e.hora_inicio LIMIT 1000';
        return array_map(fn (array $e): array => $this->formatoCalendario($e, $actor), $this->db->fetchAll($sql, $params));
    }

    private function formatoCalendario(array $e, int $actor): array
    {
        $todo = (int) ($e['todo_el_dia'] ?? 0) === 1 || !$e['hora_inicio'];
        $fin = $e['fecha_fin'] ?: $e['fecha_inicio'];
        $color = $e['color_evento'] ?: (self::TIPOS[$e['tipo_evento']][1] ?? '#0f766e');
        return [
            'id' => (int) $e['id'],
            'title' => (string) $e['titulo'],
            'start' => $todo ? $e['fecha_inicio'] : $e['fecha_inicio'] . 'T' . substr((string) $e['hora_inicio'], 0, 5),
            // en FullCalendar el fin de un evento de día completo es exclusivo
            'end' => $todo ? date('Y-m-d', strtotime($fin . ' +1 day')) : $fin . 'T' . substr((string) ($e['hora_fin'] ?: $e['hora_inicio']), 0, 5),
            'allDay' => $todo,
            'color' => $color,
            'editable' => $e['estado'] !== 'cancelado' && $this->puedeEditar($e, $actor),
            'classNames' => ['ev-estado-' . $e['estado']],
            'extendedProps' => [
                'ubicacion' => (string) $e['ubicacion'],
                'tipo' => (string) $e['tipo_evento'],
                'estado' => (string) $e['estado'],
                'colegio' => (string) ($e['nom_colegio'] ?? ''),
                'responsable' => (string) ($e['responsable'] ?? ''),
                'mi_respuesta' => $e['mi_respuesta'],
            ],
        ];
    }

    /** Evento con invitados, historial y permisos (null si no existe o no lo puede ver). */
    public function obtener(int $id, int $actor): ?array
    {
        [$cond, $p] = $this->visibles($actor);
        $e = $this->db->fetchOne(
            "SELECT e.*, c.nom_colegio, TRIM(CONCAT_WS(' ', r.nombre, r.apellido_paterno)) AS responsable, r.email AS responsable_email,
                    TRIM(CONCAT_WS(' ', cr.nombre, cr.apellido_paterno)) AS creador
               FROM eventos e
          LEFT JOIN colegio c ON c.id_colegio = e.id_colegio
          LEFT JOIN usuarios r ON r.id = e.responsable_id
          LEFT JOIN usuarios cr ON cr.id = e.creado_por
              WHERE e.id = ? AND " . self::ACTIVO . " AND {$cond}",
            array_merge([$id], $p)
        );
        if (!$e) {
            return null;
        }
        $e['invitados'] = $this->db->fetchAll(
            "SELECT i.id, i.id_usuario, i.email, COALESCE(NULLIF(i.nombre, ''), TRIM(CONCAT_WS(' ', u.nombre, u.apellido_paterno)), i.email) AS nombre,
                    i.respuesta, i.respondido_en
               FROM eventos_invitados i LEFT JOIN usuarios u ON u.id = i.id_usuario
              WHERE i.id_evento = ? ORDER BY FIELD(i.respuesta,'si','quizas','pendiente','no'), nombre",
            [$id]
        );
        $e['historial'] = $this->db->fetchAll(
            "SELECT h.accion, h.descripcion, h.creado_en, TRIM(CONCAT_WS(' ', u.nombre, u.apellido_paterno)) AS usuario
               FROM eventos_historial h LEFT JOIN usuarios u ON u.id = h.id_usuario
              WHERE h.id_evento = ? ORDER BY h.id DESC LIMIT 30",
            [$id]
        );
        $e['puede_editar'] = $this->puedeEditar($e, $actor);
        $e['puede_eliminar'] = $this->esSuper($actor) || (int) ($e['creado_por'] ?? 0) === $actor;
        $e['mi_respuesta'] = null;
        foreach ($e['invitados'] as $i) {
            if ((int) $i['id_usuario'] === $actor) {
                $e['mi_respuesta'] = $i['respuesta'];
            }
        }
        return $e;
    }

    /** Valida y normaliza lo que llega del formulario. Lanza InvalidArgumentException con el mensaje. */
    public function normalizar(array $d, int $actor): array
    {
        $t = static fn ($v, int $max): string => mb_substr(trim((string) $v), 0, $max);
        $f = [
            'titulo' => $t($d['titulo'] ?? '', 255),
            'descripcion' => $t($d['descripcion'] ?? '', 5000),
            'todo_el_dia' => !empty($d['todo_el_dia']) && $d['todo_el_dia'] !== '0' ? 1 : 0,
            'fecha_inicio' => (string) ($d['fecha_inicio'] ?? ''),
            'fecha_fin' => (string) ($d['fecha_fin'] ?? '') ?: (string) ($d['fecha_inicio'] ?? ''),
            'hora_inicio' => substr((string) ($d['hora_inicio'] ?? ''), 0, 5),
            'hora_fin' => substr((string) ($d['hora_fin'] ?? ''), 0, 5),
            'ubicacion' => $t($d['ubicacion'] ?? '', 150),
            'tipo_evento' => (string) ($d['tipo_evento'] ?? 'reunion'),
            'estado' => (string) ($d['estado'] ?? 'programado'),
            'color_evento' => (string) ($d['color_evento'] ?? ''),
            'responsable_id' => (int) ($d['responsable_id'] ?? 0) ?: $actor,
            'id_colegio' => (int) ($d['id_colegio'] ?? 0),
            'con_audio' => !empty($d['con_audio']) && $d['con_audio'] !== '0' ? 1 : 0,
            'solo_presentacion' => !empty($d['solo_presentacion']) && $d['solo_presentacion'] !== '0' ? 1 : 0,
            'musica_ambiental' => !empty($d['musica_ambiental']) && $d['musica_ambiental'] !== '0' ? 1 : 0,
            'cantidad_personas' => max(0, min(100000, (int) ($d['cantidad_personas'] ?? 0))),
            'observaciones_logisticas' => $t($d['observaciones_logisticas'] ?? '', 3000),
        ];
        $error = static function (string $m): never {
            throw new InvalidArgumentException($m);
        };
        if ($f['titulo'] === '') {
            $error('Escribe el título del evento.');
        }
        $fi = DateTimeImmutable::createFromFormat('!Y-m-d', $f['fecha_inicio']);
        $ff = DateTimeImmutable::createFromFormat('!Y-m-d', $f['fecha_fin']);
        if (!$fi || !$ff) {
            $error('Indica la fecha del evento.');
        }
        if ($f['todo_el_dia']) {
            $f['hora_inicio'] = $f['hora_fin'] = null;
            if ($ff < $fi) {
                $error('La fecha de término no puede ser anterior a la de inicio.');
            }
        } else {
            if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $f['hora_inicio']) || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $f['hora_fin'])) {
                $error('Indica la hora de inicio y de término.');
            }
            if (strtotime($f['fecha_fin'] . ' ' . $f['hora_fin']) <= strtotime($f['fecha_inicio'] . ' ' . $f['hora_inicio'])) {
                $error('El término debe ser después del inicio.');
            }
        }
        if (!isset(self::TIPOS[$f['tipo_evento']])) {
            $error('Tipo de evento no válido.');
        }
        if (!in_array($f['estado'], ['programado', 'finalizado'], true)) {
            $f['estado'] = 'programado';
        }
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $f['color_evento'])) {
            $f['color_evento'] = self::TIPOS[$f['tipo_evento']][1];
        }
        if (!$this->db->fetchOne("SELECT 1 FROM usuarios WHERE id = ? AND LOWER(estado) = 'activo'", [$f['responsable_id']])) {
            $error('El responsable no está disponible.');
        }
        $colegios = array_map('intval', array_column($this->colegiosParaCrear($actor), 'id'));
        if (!$f['id_colegio']) {
            $f['id_colegio'] = $colegios[0] ?? 0;
        }
        if (!in_array($f['id_colegio'], $colegios, true)) {
            $error('No puedes crear eventos para ese colegio.');
        }
        // Invitados: [{id_usuario}] o [{email, nombre}]
        $inv = [];
        foreach ((array) ($d['invitados'] ?? []) as $i) {
            if (is_string($i)) {
                $i = json_decode($i, true) ?: ['email' => $i];
            }
            $idU = (int) ($i['id_usuario'] ?? 0);
            if ($idU > 0) {
                $u = $this->db->fetchOne("SELECT id, email, TRIM(CONCAT_WS(' ', nombre, apellido_paterno)) AS nombre FROM usuarios WHERE id = ? AND LOWER(estado) = 'activo'", [$idU]);
                if ($u && filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
                    $inv[strtolower($u['email'])] = ['id_usuario' => (int) $u['id'], 'email' => strtolower($u['email']), 'nombre' => (string) $u['nombre']];
                }
                continue;
            }
            $email = strtolower(trim((string) ($i['email'] ?? '')));
            if ($email === '') {
                continue;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error('Correo de invitado no válido: ' . $email);
            }
            $u = $this->db->fetchOne("SELECT id, TRIM(CONCAT_WS(' ', nombre, apellido_paterno)) AS nombre FROM usuarios WHERE LOWER(email) = ? AND LOWER(estado) = 'activo' LIMIT 1", [$email]);
            $inv[$email] = ['id_usuario' => $u ? (int) $u['id'] : null, 'email' => $email, 'nombre' => $u ? (string) $u['nombre'] : mb_substr(trim((string) ($i['nombre'] ?? '')), 0, 150)];
        }
        if (count($inv) > self::MAX_INVITADOS) {
            $error('Máximo ' . self::MAX_INVITADOS . ' invitados por evento.');
        }
        $f['invitados'] = array_values($inv);
        return $f;
    }

    private const CAMPOS = ['titulo', 'descripcion', 'todo_el_dia', 'fecha_inicio', 'fecha_fin', 'hora_inicio', 'hora_fin', 'ubicacion',
        'tipo_evento', 'estado', 'color_evento', 'responsable_id', 'id_colegio', 'con_audio', 'solo_presentacion',
        'musica_ambiental', 'cantidad_personas', 'observaciones_logisticas'];

    /**
     * Crea o actualiza. @return array{id:int, nuevo:bool, cambios:string[], invitados_nuevos:array, invitados_quitados:array}
     */
    public function guardar(array $f, int $actor, ?int $id = null): array
    {
        $anterior = null;
        if ($id) {
            $anterior = $this->obtener($id, $actor);
            if (!$anterior || !$this->puedeEditar($anterior, $actor)) {
                throw new RuntimeException('No puedes editar este evento.');
            }
            if ($anterior['estado'] === 'cancelado') {
                throw new InvalidArgumentException('El evento está cancelado.');
            }
        }
        $pdo = $this->db->getPDO();
        $pdo->beginTransaction();
        try {
            $valores = array_map(static fn ($c) => $f[$c], self::CAMPOS);
            if ($id) {
                $sets = implode(', ', array_map(static fn ($c) => "`{$c}` = ?", self::CAMPOS));
                $this->db->execute(
                    "UPDATE eventos SET {$sets}, hora_evento = COALESCE(?, '00:00:00'), secuencia = secuencia + 1, actualizado_en = NOW() WHERE id = ?",
                    array_merge($valores, [$f['hora_inicio'], $id])
                );
            } else {
                $cols = implode(', ', array_map(static fn ($c) => "`{$c}`", self::CAMPOS));
                $marcas = implode(', ', array_fill(0, count(self::CAMPOS), '?'));
                $this->db->execute(
                    "INSERT INTO eventos ({$cols}, hora_evento, creado_por, eliminado, correo_enviado, secuencia, creado_en, actualizado_en)
                     VALUES ({$marcas}, COALESCE(?, '00:00:00'), ?, '0', 0, 0, NOW(), NOW())",
                    array_merge($valores, [$f['hora_inicio'], $actor])
                );
                $id = (int) $this->db->lastInsertId();
            }
            // Invitados: agrega los nuevos (con su token) y quita los que ya no están
            $actuales = [];
            foreach ($this->db->fetchAll('SELECT id, email, id_usuario, nombre, token FROM eventos_invitados WHERE id_evento = ?', [$id]) as $r) {
                $actuales[strtolower($r['email'])] = $r;
            }
            $nuevos = [];
            foreach ($f['invitados'] as $i) {
                if (!isset($actuales[$i['email']])) {
                    $token = bin2hex(random_bytes(16));
                    $this->db->execute(
                        'INSERT INTO eventos_invitados (id_evento, id_usuario, email, nombre, respuesta, token, creado_en) VALUES (?, ?, ?, ?, \'pendiente\', ?, NOW())',
                        [$id, $i['id_usuario'], $i['email'], $i['nombre'], $token]
                    );
                    $nuevos[] = $i + ['token' => $token];
                }
            }
            $quitados = array_values(array_diff_key($actuales, array_flip(array_column($f['invitados'], 'email'))));
            foreach ($quitados as $q) {
                $this->db->execute('DELETE FROM eventos_invitados WHERE id = ?', [(int) $q['id']]);
            }
            $cambios = $anterior ? $this->cambios($anterior, $f) : [];
            $this->historial($id, $anterior ? 'edicion' : 'creacion',
                $anterior ? ($cambios ? 'Cambió: ' . implode(', ', $cambios) . '.' : 'Guardó sin cambios.') : 'Creó el evento "' . $f['titulo'] . '".', $actor);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
        return ['id' => $id, 'nuevo' => !$anterior, 'cambios' => $cambios, 'invitados_nuevos' => $nuevos, 'invitados_quitados' => $quitados];
    }

    /** Nombres legibles de lo que cambió (para el correo y el historial). */
    private function cambios(array $a, array $f): array
    {
        $nombres = ['titulo' => 'título', 'descripcion' => 'descripción', 'fecha_inicio' => 'fecha', 'fecha_fin' => 'fecha de término',
            'hora_inicio' => 'hora de inicio', 'hora_fin' => 'hora de término', 'todo_el_dia' => 'todo el día', 'ubicacion' => 'lugar',
            'tipo_evento' => 'tipo', 'estado' => 'estado', 'responsable_id' => 'responsable', 'id_colegio' => 'colegio',
            'con_audio' => 'audio', 'solo_presentacion' => 'presentación', 'musica_ambiental' => 'música', 'cantidad_personas' => 'cantidad de personas',
            'observaciones_logisticas' => 'observaciones'];
        $out = [];
        foreach ($nombres as $c => $n) {
            $va = (string) ($a[$c] ?? '');
            $vn = (string) ($f[$c] ?? '');
            if (in_array($c, ['hora_inicio', 'hora_fin'], true)) {
                $va = substr($va, 0, 5);
            }
            if ($va !== $vn) {
                $out[] = $n;
            }
        }
        return $out;
    }

    /** Mover / cambiar duración desde el calendario (arrastrar). */
    public function mover(int $id, int $actor, string $inicio, string $fin, bool $todoElDia): array
    {
        $e = $this->obtener($id, $actor);
        if (!$e) {
            throw new RuntimeException('El evento no existe.');
        }
        $f = array_intersect_key($e, array_flip(self::CAMPOS));
        $ini = new DateTimeImmutable($inicio);
        $fn = new DateTimeImmutable($fin ?: $inicio);
        $f['todo_el_dia'] = $todoElDia ? 1 : 0;
        $f['fecha_inicio'] = $ini->format('Y-m-d');
        if ($todoElDia) {
            $f['fecha_fin'] = ($fin ? $fn->modify('-1 day') : $ini)->format('Y-m-d');   // fin exclusivo del calendario
            $f['hora_inicio'] = $f['hora_fin'] = null;
        } else {
            $f['fecha_fin'] = $fn->format('Y-m-d');
            $f['hora_inicio'] = $ini->format('H:i');
            $f['hora_fin'] = ($fin ? $fn : $ini->modify('+1 hour'))->format('H:i');
        }
        $f['hora_inicio'] = $f['hora_inicio'] !== null ? substr((string) $f['hora_inicio'], 0, 5) : null;
        $f['invitados'] = array_map(static fn ($i) => ['id_usuario' => $i['id_usuario'] ? (int) $i['id_usuario'] : null, 'email' => strtolower($i['email']), 'nombre' => $i['nombre']], $e['invitados']);
        return $this->guardar($f, $actor, $id);
    }

    public function cancelar(int $id, int $actor, string $motivo = ''): array
    {
        $e = $this->obtener($id, $actor);
        if (!$e || !$this->puedeEditar($e, $actor)) {
            throw new RuntimeException('No puedes cancelar este evento.');
        }
        $this->db->execute("UPDATE eventos SET estado = 'cancelado', secuencia = secuencia + 1, actualizado_en = NOW() WHERE id = ?", [$id]);
        $this->historial($id, 'cancelacion', 'Canceló el evento' . ($motivo !== '' ? ': ' . mb_substr($motivo, 0, 300) : '.'), $actor);
        return $e;
    }

    public function eliminar(int $id, int $actor): void
    {
        $e = $this->obtener($id, $actor);
        if (!$e || !$e['puede_eliminar']) {
            throw new RuntimeException('Solo quien creó el evento o el super admin puede eliminarlo.');
        }
        $this->db->execute("UPDATE eventos SET eliminado = '1', actualizado_en = NOW() WHERE id = ?", [$id]);
        $this->historial($id, 'eliminacion', 'Eliminó el evento.', $actor);
    }

    /** Respuesta Sí / No / Quizás por token (enlace del correo). @return ?array invitado + evento */
    public function responderToken(string $token, string $respuesta): ?array
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $token) || !in_array($respuesta, ['si', 'no', 'quizas'], true)) {
            return null;
        }
        $i = $this->db->fetchOne('SELECT id, id_evento, id_usuario, nombre, email FROM eventos_invitados WHERE token = ?', [$token]);
        if (!$i) {
            return null;
        }
        $this->db->execute('UPDATE eventos_invitados SET respuesta = ?, respondido_en = NOW() WHERE id = ?', [$respuesta, (int) $i['id']]);
        $etiqueta = ['si' => 'Asistirá', 'no' => 'No asistirá', 'quizas' => 'Quizás'][$respuesta];
        $this->historial((int) $i['id_evento'], 'respuesta', ($i['nombre'] ?: $i['email']) . ': ' . $etiqueta . '.', $i['id_usuario'] ? (int) $i['id_usuario'] : null);
        return $i;
    }

    public function responder(int $id, int $actor, string $respuesta): bool
    {
        $t = $this->db->fetchOne('SELECT token FROM eventos_invitados WHERE id_evento = ? AND id_usuario = ?', [$id, $actor]);
        return $t && $this->responderToken((string) $t['token'], $respuesta) !== null;
    }

    public function porToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
            return null;
        }
        $r = $this->db->fetchOne(
            "SELECT i.respuesta, i.nombre, i.email, e.* , c.nom_colegio, TRIM(CONCAT_WS(' ', r.nombre, r.apellido_paterno)) AS responsable
               FROM eventos_invitados i JOIN eventos e ON e.id = i.id_evento
          LEFT JOIN colegio c ON c.id_colegio = e.id_colegio LEFT JOIN usuarios r ON r.id = e.responsable_id
              WHERE i.token = ? AND " . self::ACTIVO,
            [$token]
        );
        return $r ?: null;
    }

    /** Buscador de personas para invitar / responsable. */
    public function buscarUsuarios(string $q): array
    {
        $q = trim($q);
        if (mb_strlen($q) < 2) {
            return [];
        }
        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
        return $this->db->fetchAll(
            "SELECT u.id, TRIM(CONCAT_WS(' ', u.nombre, u.apellido_paterno)) AS nombre, u.email,
                    (SELECT c.nom_colegio FROM usuario_colegio uc JOIN colegio c ON c.id_colegio = uc.id_colegio
                      WHERE uc.id_usuario = u.id AND uc.estado = 1 ORDER BY uc.id LIMIT 1) AS colegio
               FROM usuarios u
              WHERE LOWER(u.estado) = 'activo' AND u.email <> ''
                AND (CONCAT_WS(' ', u.nombre, u.apellido_paterno, u.apellido_materno) LIKE ? OR u.email LIKE ?)
           ORDER BY u.nombre, u.apellido_paterno LIMIT 12",
            [$like, $like]
        );
    }

    public function historial(int $id, string $accion, string $descripcion, ?int $usuario): void
    {
        try {
            $this->db->execute(
                'INSERT INTO eventos_historial (id_evento, accion, descripcion, id_usuario, creado_en) VALUES (?, ?, ?, ?, NOW())',
                [$id, $accion, mb_substr($descripcion, 0, 500), $usuario]
            );
        } catch (Throwable $e) {
            error_log('[eventos] historial: ' . $e->getMessage());
        }
    }

    public function marcarCorreo(int $id): void
    {
        $this->db->execute('UPDATE eventos SET correo_enviado = 1 WHERE id = ?', [$id]);
    }

    /** Destinatarios de los correos: responsable + invitados, con su token (el responsable sin token si no está invitado). */
    public function destinatarios(int $id): array
    {
        $out = [];
        foreach ($this->db->fetchAll('SELECT id_usuario, email, nombre, token FROM eventos_invitados WHERE id_evento = ?', [$id]) as $i) {
            $out[strtolower($i['email'])] = ['id_usuario' => $i['id_usuario'] ? (int) $i['id_usuario'] : null, 'email' => strtolower($i['email']), 'nombre' => (string) $i['nombre'], 'token' => (string) $i['token']];
        }
        $r = $this->db->fetchOne(
            "SELECT u.id, u.email, TRIM(CONCAT_WS(' ', u.nombre, u.apellido_paterno)) AS nombre FROM eventos e JOIN usuarios u ON u.id = e.responsable_id WHERE e.id = ?",
            [$id]
        );
        if ($r && filter_var($r['email'], FILTER_VALIDATE_EMAIL) && !isset($out[strtolower($r['email'])])) {
            $out[strtolower($r['email'])] = ['id_usuario' => (int) $r['id'], 'email' => strtolower($r['email']), 'nombre' => (string) $r['nombre'], 'token' => '', 'responsable' => true];
        }
        return array_values($out);
    }
}
