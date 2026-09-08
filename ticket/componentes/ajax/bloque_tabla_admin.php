<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../configuracion/_inicio.php';
header('Content-Type: text/html; charset=UTF-8');

$usuarioId = (int) Sesion::get('id', 0);
$esGlobal = false;
$autorizado = false;
$tickets = [];
$colegios = [];
$tecnicos = [];
$errorCarga = null;
$estadoFiltro = strtolower(trim((string) ($_GET['estado'] ?? '')));
$colegioFiltro = (int) ($_GET['colegio'] ?? 0);
$tecnicoFiltro = (int) ($_GET['tecnico'] ?? 0);
if (!in_array($estadoFiltro, ['', 'nuevo', 'en_proceso', 'atrasado', 'resuelto', 'cerrado'], true)) {
    $estadoFiltro = '';
}

try {
    $esGlobal = es_administrador_global($usuarioId, $db);
    $autorizado = $esGlobal || es_admin_colegio($usuarioId, null, $db);
    if (!$autorizado) {
        http_response_code(403);
        echo '<div class="ticket-empty"><i class="bi bi-shield-lock"></i>Acceso restringido.</div>';
        exit;
    }

    if ($esGlobal) {
        $colegios = $db->fetchAll('SELECT id_colegio, nom_colegio FROM colegio WHERE estado = 1 ORDER BY nom_colegio');
    } else {
        $colegios = $db->fetchAll(
            "SELECT DISTINCT c.id_colegio, c.nom_colegio
               FROM usuario_colegio uc
               JOIN colegio c ON c.id_colegio = uc.id_colegio
          LEFT JOIN perfiles p ON p.id_perfil = uc.id_perfil
              WHERE uc.id_usuario = ? AND uc.estado = 1 AND c.estado = 1
                AND (uc.es_admin_colegio = 1 OR LOWER(p.nombre) IN ('admin colegio','admin_colegio','administrador colegio'))
           ORDER BY c.nom_colegio",
            [$usuarioId]
        );
    }

    $idsColegio = array_map('intval', array_column($colegios, 'id_colegio'));
    if ($colegioFiltro > 0 && !in_array($colegioFiltro, $idsColegio, true)) {
        $colegioFiltro = 0;
    }
    $tecnicos = $db->fetchAll("SELECT id, CONCAT_WS(' ', nombre, apellido_paterno) AS nombre FROM usuarios WHERE id_area_trabajo = 1 AND LOWER(estado) = 'activo' ORDER BY nombre, apellido_paterno");
    $idsTecnico = array_map('intval', array_column($tecnicos, 'id'));
    if ($tecnicoFiltro > 0 && !in_array($tecnicoFiltro, $idsTecnico, true)) {
        $tecnicoFiltro = 0;
    }

    $sql = "SELECT t.id_ticket, t.asunto, t.descripcion, t.estado, t.fecha_creacion, t.fecha_respuesta,
                   t.prioridad, t.id_colegio, t.id_tecnico_asignado,
                   CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS usuario_nombre,
                   c.nombre_categoria AS categoria_nombre, col.nom_colegio AS colegio_nombre,
                   CONCAT_WS(' ', tec.nombre, tec.apellido_paterno) AS tecnico_nombre
              FROM tickets t
              JOIN usuarios u ON u.id = t.id_usuario
              JOIN categoria_de_ticket c ON c.id_categoria = t.id_categoria
              JOIN colegio col ON col.id_colegio = t.id_colegio
         LEFT JOIN usuarios tec ON tec.id = t.id_tecnico_asignado
             WHERE 1 = 1";
    $params = [];
    if (!$esGlobal) {
        if (!$idsColegio) {
            $sql .= ' AND 1 = 0';
        } else {
            $sql .= ' AND t.id_colegio IN (' . implode(',', array_fill(0, count($idsColegio), '?')) . ')';
            array_push($params, ...$idsColegio);
        }
    }
    if ($estadoFiltro !== '') {
        $sql .= ' AND t.estado = ?';
        $params[] = $estadoFiltro;
    }
    if ($colegioFiltro > 0) {
        $sql .= ' AND t.id_colegio = ?';
        $params[] = $colegioFiltro;
    }
    if ($tecnicoFiltro > 0) {
        $sql .= ' AND t.id_tecnico_asignado = ?';
        $params[] = $tecnicoFiltro;
    }
    $sql .= " ORDER BY FIELD(t.estado, 'nuevo','en_proceso','atrasado','resuelto','cerrado'), t.fecha_creacion DESC";
    $tickets = $db->fetchAll($sql, $params);
} catch (Throwable $ex) {
    error_log('Error AJAX en administración de tickets: ' . $ex->getMessage());
    $errorCarga = 'No fue posible consultar los tickets.';
}

require __DIR__ . '/../bloque_tabla_admin.php';
