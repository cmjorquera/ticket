<?php
require_once __DIR__ . '/../bootstrap.php';

eventos_requiere_login(true);
$db = eventos_db();
$year = date('Y');
$where = eventos_where_activos();

$meses = array_fill(1, 12, 0);
$stmtMes = $db->prepare("SELECT MONTH(fecha_inicio) AS mes, COUNT(*) AS total
                         FROM eventos e
                         WHERE $where AND YEAR(fecha_inicio) = ?
                         GROUP BY MONTH(fecha_inicio)");
$stmtMes->bind_param('i', $year);
$stmtMes->execute();
foreach (eventos_stmt_result_all($stmtMes) as $row) {
    $meses[(int) $row['mes']] = (int) $row['total'];
}
$stmtMes->close();

$porTipo = [];
$resultadoTipo = $db->consulta("SELECT tipo_evento, COUNT(*) AS total FROM eventos e WHERE $where GROUP BY tipo_evento ORDER BY total DESC");
while ($row = $db->fetch_assoc($resultadoTipo)) {
    $porTipo[] = $row;
}

$porEstado = [];
$resultadoEstado = $db->consulta("SELECT estado, COUNT(*) AS total FROM eventos e WHERE $where GROUP BY estado ORDER BY total DESC");
while ($row = $db->fetch_assoc($resultadoEstado)) {
    $porEstado[] = $row;
}

$topResponsables = [];
$resultadoResponsables = $db->consulta("SELECT CONCAT(u.nombre, ' ', u.apellido_paterno) AS responsable, COUNT(*) AS total
                                        FROM eventos e
                                        LEFT JOIN usuarios u ON u.id = e.responsable_id
                                        WHERE $where
                                        GROUP BY e.responsable_id, u.nombre, u.apellido_paterno
                                        ORDER BY total DESC
                                        LIMIT 10");
while ($row = $db->fetch_assoc($resultadoResponsables)) {
    $topResponsables[] = $row;
}

$requerimientos = ['audio' => 0, 'musica_ambiental' => 0, 'solo_presentacion' => 0];
$resultadoReq = $db->consulta("SELECT
        SUM(CASE WHEN con_audio = 1 THEN 1 ELSE 0 END) AS audio,
        SUM(CASE WHEN musica_ambiental = 1 THEN 1 ELSE 0 END) AS musica_ambiental,
        SUM(CASE WHEN solo_presentacion = 1 THEN 1 ELSE 0 END) AS solo_presentacion
    FROM eventos e
    WHERE $where");
if ($row = $db->fetch_assoc($resultadoReq)) {
    $requerimientos['audio'] = (int) ($row['audio'] ?? 0);
    $requerimientos['musica_ambiental'] = (int) ($row['musica_ambiental'] ?? 0);
    $requerimientos['solo_presentacion'] = (int) ($row['solo_presentacion'] ?? 0);
}

eventos_responder_json([
    'ok' => true,
    'year' => (int) $year,
    'por_mes' => ['labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'], 'values' => array_values($meses)],
    'por_tipo' => ['labels' => array_map(fn($row) => ucfirst($row['tipo_evento']), $porTipo), 'values' => array_map(fn($row) => (int) $row['total'], $porTipo)],
    'por_estado' => ['labels' => array_map(fn($row) => ucfirst($row['estado']), $porEstado), 'values' => array_map(fn($row) => (int) $row['total'], $porEstado)],
    'top_responsables' => ['labels' => array_map(fn($row) => $row['responsable'], $topResponsables), 'values' => array_map(fn($row) => (int) $row['total'], $topResponsables)],
    'requerimientos' => ['labels' => ['Con audio', 'Música ambiental', 'Solo presentación'], 'values' => [$requerimientos['audio'], $requerimientos['musica_ambiental'], $requerimientos['solo_presentacion']]],
]);
