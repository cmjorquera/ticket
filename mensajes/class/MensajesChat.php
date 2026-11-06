<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/clases/Conexion.php';

/**
 * Mensajería interna sobre la tabla mensajes_chat (misma que usa Colaboradores).
 *
 * - Una conversación = todos los mensajes con el mismo id_conversacion.
 *   El primer mensaje tiene id_conversacion = su propio id.
 * - Las respuestas se guardan con ese id_conversacion e id_padre = conversación.
 * - Archivar / eliminar es por usuario (tabla mensajes_chat_carpeta): lo que
 *   uno archiva o elimina no desaparece para la otra persona.
 * - Si llega una respuesta a una conversación archivada, vuelve a Recibidos.
 */
final class MensajesChat
{
    public const MAX_MENSAJE = 1000;
    public const MAX_DESTINATARIOS = 200;

    private Conexion $db;
    private int $usuario;

    public function __construct(Conexion $db, int $usuario)
    {
        $this->db = $db;
        $this->usuario = $usuario;
    }

    public function instalado(): bool
    {
        return $this->tablaExiste('mensajes_chat') && $this->tablaExiste('mensajes_chat_carpeta');
    }

    /** Conteos para las tarjetas y el menú. */
    public function resumen(): array
    {
        $u = $this->usuario;
        $fila = $this->db->fetchOne(
            "SELECT
                SUM(CASE WHEN m.para = ? AND m.leido = 0 THEN 1 ELSE 0 END) AS no_leidos,
                COUNT(DISTINCT CASE WHEN m.para = ? AND c.carpeta IS NULL THEN m.id_conversacion END) AS recibidos,
                COUNT(DISTINCT CASE WHEN m.de = ? AND c.carpeta IS NULL THEN m.id_conversacion END) AS enviados,
                COUNT(DISTINCT CASE WHEN c.carpeta = 'archivado' THEN m.id_conversacion END) AS archivados
               FROM mensajes_chat m
          LEFT JOIN mensajes_chat_carpeta c ON c.id_conversacion = m.id_conversacion AND c.id_usuario = ?
              WHERE m.eliminado = 0 AND (m.de = ? OR m.para = ?)
                AND (c.carpeta IS NULL OR c.carpeta <> 'eliminado')",
            [$u, $u, $u, $u, $u, $u]
        ) ?: [];
        return [
            'no_leidos' => (int) ($fila['no_leidos'] ?? 0),
            'recibidos' => (int) ($fila['recibidos'] ?? 0),
            'enviados' => (int) ($fila['enviados'] ?? 0),
            'archivados' => (int) ($fila['archivados'] ?? 0),
        ];
    }

    /**
     * Conversaciones de una carpeta: recibidos | enviados | archivados.
     * Cada fila: la otra persona, el último mensaje, fecha y no leídos.
     */
    public function listar(string $carpeta): array
    {
        $u = $this->usuario;
        if ($carpeta === 'archivados') {
            $filtro = "c.carpeta = 'archivado'";
            $participa = '(m.de = ? OR m.para = ?)';
            $params = [$u, $u];
        } else {
            $filtro = 'c.carpeta IS NULL';
            $participa = $carpeta === 'enviados' ? 'm.de = ?' : 'm.para = ?';
            $params = [$u];
        }

        $conversaciones = $this->db->fetchAll(
            "SELECT m.id_conversacion,
                    MAX(m.fecha_hora) AS ultima_fecha,
                    COUNT(*) AS total,
                    MAX(m.urgente) AS urgente
               FROM mensajes_chat m
          LEFT JOIN mensajes_chat_carpeta c ON c.id_conversacion = m.id_conversacion AND c.id_usuario = ?
              WHERE m.eliminado = 0 AND {$filtro}
                AND m.id_conversacion IN (SELECT DISTINCT m2.id_conversacion FROM mensajes_chat m2 WHERE m2.eliminado = 0 AND " . str_replace('m.', 'm2.', $participa) . ")
           GROUP BY m.id_conversacion
           ORDER BY ultima_fecha DESC
              LIMIT 500",
            array_merge([$u], $params)
        );
        if (!$conversaciones) {
            return [];
        }

        $ids = array_map(static fn (array $f): int => (int) $f['id_conversacion'], $conversaciones);
        $marcas = implode(',', array_fill(0, count($ids), '?'));

        // Último mensaje de cada conversación
        $ultimos = [];
        foreach ($this->db->fetchAll(
            "SELECT m.id_conversacion, m.mensaje, m.de, m.para
               FROM mensajes_chat m
               JOIN (SELECT id_conversacion, MAX(id) AS id FROM mensajes_chat
                      WHERE eliminado = 0 AND id_conversacion IN ($marcas) GROUP BY id_conversacion) ult ON ult.id = m.id",
            $ids
        ) as $f) {
            $ultimos[(int) $f['id_conversacion']] = $f;
        }
        // Participantes (el primer mensaje define quién es quién)
        $raices = [];
        foreach ($this->db->fetchAll(
            "SELECT m.id_conversacion, m.de, m.para FROM mensajes_chat m
               JOIN (SELECT id_conversacion, MIN(id) AS id FROM mensajes_chat
                      WHERE id_conversacion IN ($marcas) GROUP BY id_conversacion) pri ON pri.id = m.id",
            $ids
        ) as $f) {
            $raices[(int) $f['id_conversacion']] = $f;
        }
        $noLeidos = [];
        foreach ($this->db->fetchAll(
            "SELECT id_conversacion, COUNT(*) AS n FROM mensajes_chat
              WHERE eliminado = 0 AND leido = 0 AND para = ? AND id_conversacion IN ($marcas)
           GROUP BY id_conversacion",
            array_merge([$u], $ids)
        ) as $f) {
            $noLeidos[(int) $f['id_conversacion']] = (int) $f['n'];
        }

        $otros = [];
        foreach ($raices as $r) {
            $otros[] = (int) $r['de'] === $u ? (int) $r['para'] : (int) $r['de'];
        }
        $nombres = $this->nombres($otros);

        $resultado = [];
        foreach ($conversaciones as $c) {
            $id = (int) $c['id_conversacion'];
            $raiz = $raices[$id] ?? ['de' => $u, 'para' => $u];
            $otro = (int) $raiz['de'] === $u ? (int) $raiz['para'] : (int) $raiz['de'];
            $ultimo = $ultimos[$id] ?? ['mensaje' => '', 'de' => 0];
            $resultado[] = [
                'id' => $id,
                'otro_id' => $otro,
                'otro' => $nombres[$otro] ?? 'Usuario #' . $otro,
                'iniciada_por_mi' => (int) $raiz['de'] === $u,
                'ultimo' => (string) $ultimo['mensaje'],
                'ultimo_mio' => (int) $ultimo['de'] === $u,
                'fecha' => (string) $c['ultima_fecha'],
                'total' => (int) $c['total'],
                'no_leidos' => $noLeidos[$id] ?? 0,
                'urgente' => (int) $c['urgente'] === 1,
            ];
        }
        return $resultado;
    }

    /** Mensajes de una conversación (solo si participo). Marca como leídos los dirigidos a mí. */
    public function conversacion(int $idConversacion): ?array
    {
        if (!$this->participa($idConversacion)) {
            return null;
        }
        $mensajes = $this->db->fetchAll(
            "SELECT m.id, m.mensaje, m.de, m.para, m.fecha_hora, m.urgente
               FROM mensajes_chat m
              WHERE m.id_conversacion = ? AND m.eliminado = 0
           ORDER BY m.fecha_hora ASC, m.id ASC",
            [$idConversacion]
        );
        $raiz = $this->raiz($idConversacion);
        $otro = (int) $raiz['de'] === $this->usuario ? (int) $raiz['para'] : (int) $raiz['de'];
        $nombres = $this->nombres([$otro, $this->usuario]);

        $this->db->execute(
            'UPDATE mensajes_chat SET leido = 1 WHERE id_conversacion = ? AND para = ? AND leido = 0',
            [$idConversacion, $this->usuario]
        );
        $carpeta = $this->db->fetchOne(
            'SELECT carpeta FROM mensajes_chat_carpeta WHERE id_usuario = ? AND id_conversacion = ?',
            [$this->usuario, $idConversacion]
        );

        return [
            'id' => $idConversacion,
            'otro_id' => $otro,
            'otro' => $nombres[$otro] ?? 'Usuario #' . $otro,
            'archivada' => ($carpeta['carpeta'] ?? '') === 'archivado',
            'mensajes' => array_map(fn (array $m): array => [
                'id' => (int) $m['id'],
                'texto' => (string) $m['mensaje'],
                'mio' => (int) $m['de'] === $this->usuario,
                'autor' => (int) $m['de'] === $this->usuario ? 'Tú' : ($nombres[(int) $m['de']] ?? 'Usuario'),
                'fecha' => (string) $m['fecha_hora'],
                'urgente' => (int) $m['urgente'] === 1,
            ], $mensajes),
        ];
    }

    /** Responde dentro de una conversación. */
    public function responder(int $idConversacion, string $texto): void
    {
        $texto = $this->validarTexto($texto);
        if (!$this->participa($idConversacion)) {
            throw new RuntimeException('No tienes acceso a esta conversación.');
        }
        $raiz = $this->raiz($idConversacion);
        $otro = (int) $raiz['de'] === $this->usuario ? (int) $raiz['para'] : (int) $raiz['de'];
        if ($otro <= 0 || $otro === $this->usuario) {
            throw new RuntimeException('La conversación no tiene destinatario.');
        }
        $this->db->execute(
            "INSERT INTO mensajes_chat (id_padre, id_conversacion, mensaje, de, para, fecha_hora, leido, urgente, eliminado, prioridad)
             VALUES (?, ?, ?, ?, ?, ?, 0, 0, 0, 'NORMAL')",
            [$idConversacion, $idConversacion, $texto, $this->usuario, $otro, $this->ahora()]
        );
        // Vuelve a la bandeja de ambos (si alguno la había archivado o eliminado)
        $this->db->execute('DELETE FROM mensajes_chat_carpeta WHERE id_conversacion = ?', [$idConversacion]);
    }

    /** Mensaje nuevo a uno o varios colaboradores (una conversación por destinatario). */
    public function nuevo(array $destinatarios, string $texto, bool $urgente): int
    {
        $texto = $this->validarTexto($texto);
        $ids = array_values(array_unique(array_filter(array_map('intval', $destinatarios), fn (int $id): bool => $id > 0 && $id !== $this->usuario)));
        if (!$ids) {
            throw new RuntimeException('Selecciona al menos un destinatario.');
        }
        if (count($ids) > self::MAX_DESTINATARIOS) {
            throw new RuntimeException('Máximo ' . self::MAX_DESTINATARIOS . ' destinatarios por envío.');
        }
        $marcas = implode(',', array_fill(0, count($ids), '?'));
        $validos = $this->db->fetchAll("SELECT id FROM usuarios WHERE id IN ($marcas) AND LOWER(estado) = 'activo'", $ids);
        if (count($validos) !== count($ids)) {
            throw new RuntimeException('Uno o más destinatarios no están disponibles.');
        }

        $pdo = $this->db->getPDO();
        $pdo->beginTransaction();
        try {
            $fecha = $this->ahora();
            foreach ($ids as $para) {
                $this->db->execute(
                    "INSERT INTO mensajes_chat (id_conversacion, mensaje, de, para, fecha_hora, leido, urgente, eliminado, prioridad)
                     VALUES (0, ?, ?, ?, ?, 0, ?, 0, 'NORMAL')",
                    [$texto, $this->usuario, $para, $fecha, $urgente ? 1 : 0]
                );
                $id = (int) $this->db->lastInsertId();
                $this->db->execute('UPDATE mensajes_chat SET id_conversacion = ? WHERE id = ?', [$id, $id]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
        return count($ids);
    }

    /** archivar | desarchivar | eliminar (solo para mí). */
    public function moverACarpeta(int $idConversacion, string $accion): void
    {
        if (!$this->participa($idConversacion)) {
            throw new RuntimeException('No tienes acceso a esta conversación.');
        }
        if ($accion === 'desarchivar') {
            $this->db->execute('DELETE FROM mensajes_chat_carpeta WHERE id_usuario = ? AND id_conversacion = ?', [$this->usuario, $idConversacion]);
            return;
        }
        $carpeta = $accion === 'eliminar' ? 'eliminado' : 'archivado';
        $this->db->execute(
            'INSERT INTO mensajes_chat_carpeta (id_usuario, id_conversacion, carpeta) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE carpeta = VALUES(carpeta), actualizado_en = CURRENT_TIMESTAMP',
            [$this->usuario, $idConversacion, $carpeta]
        );
        if ($carpeta === 'eliminado') {
            // Al eliminarla, lo pendiente de leer deja de contar
            $this->db->execute('UPDATE mensajes_chat SET leido = 1 WHERE id_conversacion = ? AND para = ?', [$idConversacion, $this->usuario]);
        }
    }

    /** Colaboradores activos para elegir destinatario. */
    public function destinatarios(): array
    {
        return array_map(static fn (array $f): array => [
            'id' => (int) $f['id'],
            'nombre' => trim((string) $f['nombre']),
            'cargo' => trim((string) ($f['cargo'] ?? '')),
        ], $this->db->fetchAll(
            "SELECT id, TRIM(CONCAT(COALESCE(nombre,''), ' ', COALESCE(apellido_paterno,''), ' ', COALESCE(apellido_materno,''))) AS nombre, cargo
               FROM usuarios WHERE LOWER(estado) = 'activo' AND id <> ? ORDER BY nombre",
            [$this->usuario]
        ));
    }

    // --------------------------------------------------------------------

    private function validarTexto(string $texto): string
    {
        $texto = trim($texto);
        if ($texto === '') {
            throw new RuntimeException('Escribe un mensaje.');
        }
        if (mb_strlen($texto) > self::MAX_MENSAJE) {
            throw new RuntimeException('El mensaje supera los ' . self::MAX_MENSAJE . ' caracteres.');
        }
        return $texto;
    }

    private function participa(int $idConversacion): bool
    {
        if ($idConversacion <= 0) {
            return false;
        }
        return (bool) $this->db->fetchOne(
            'SELECT 1 FROM mensajes_chat WHERE id_conversacion = ? AND (de = ? OR para = ?) LIMIT 1',
            [$idConversacion, $this->usuario, $this->usuario]
        );
    }

    private function raiz(int $idConversacion): array
    {
        return $this->db->fetchOne(
            'SELECT de, para FROM mensajes_chat WHERE id_conversacion = ? ORDER BY id ASC LIMIT 1',
            [$idConversacion]
        ) ?: ['de' => 0, 'para' => 0];
    }

    /** @param int[] $ids @return array<int,string> */
    private function nombres(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids)));
        if (!$ids) {
            return [];
        }
        $marcas = implode(',', array_fill(0, count($ids), '?'));
        $nombres = [];
        foreach ($this->db->fetchAll(
            "SELECT id, TRIM(CONCAT(COALESCE(nombre,''), ' ', COALESCE(apellido_paterno,''))) AS nombre FROM usuarios WHERE id IN ($marcas)",
            $ids
        ) as $f) {
            $nombres[(int) $f['id']] = $f['nombre'] !== '' ? $f['nombre'] : 'Usuario #' . $f['id'];
        }
        return $nombres;
    }

    private function ahora(): string
    {
        return (new DateTimeImmutable('now', new DateTimeZone('America/Santiago')))->format('Y-m-d H:i:s');
    }

    private function tablaExiste(string $tabla): bool
    {
        return (bool) $this->db->fetchOne(
            'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1',
            [$tabla]
        );
    }
}
