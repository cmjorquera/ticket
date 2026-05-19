<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../class/conexion.php';

function responderCategoria($success, $message = '', $extra = [])
{
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message,
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderCategoria(false, 'Metodo invalido.');
}

$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true);
$data = is_array($jsonInput) ? $jsonInput : $_POST;

$nombre = isset($data['nombre_categoria']) ? trim($data['nombre_categoria']) : '';
$abreviacion = isset($data['abreviacion']) ? trim($data['abreviacion']) : '';
$icono = isset($data['icono']) ? trim($data['icono']) : 'bi-tag';
$idTecnico = isset($data['id_tecnico']) ? (int)$data['id_tecnico'] : 0;

if ($nombre === '') {
    responderCategoria(false, 'El nombre de la categoria es obligatorio.');
}

if ($idTecnico <= 0) {
    responderCategoria(false, 'Debes seleccionar un tecnico.');
}

if ($icono === '') {
    $icono = 'bi-tag';
}

$bdato = new MySQL('', '', '');

$stmtTecnico = $bdato->prepare("
    SELECT id
    FROM usuarios
    WHERE id = ? AND id_area_trabajo = 1 AND id != 27
    LIMIT 1
");

if (!$stmtTecnico) {
    responderCategoria(false, 'No se pudo validar el tecnico.');
}

$stmtTecnico->bind_param('i', $idTecnico);
if (!$stmtTecnico->execute()) {
    responderCategoria(false, 'No se pudo validar el tecnico.');
}
$stmtTecnico->store_result();

if ($stmtTecnico->num_rows === 0) {
    responderCategoria(false, 'El tecnico seleccionado no esta disponible.');
}

$stmtTecnico->close();

$bdato->consulta('START TRANSACTION');

try {
    $resOrden = $bdato->consulta('SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente_orden FROM categoria_de_ticket');
    $rowOrden = $bdato->fetch_assoc($resOrden);
    $orden = isset($rowOrden['siguiente_orden']) ? (int)$rowOrden['siguiente_orden'] : 1;

    $estado = 1;
    $stmtCategoria = $bdato->prepare("
        INSERT INTO categoria_de_ticket (nombre_categoria, abreviacion, icono, orden, estado)
        VALUES (?, ?, ?, ?, ?)
    ");

    if (!$stmtCategoria) {
        throw new Exception('No se pudo preparar la categoria.');
    }

    $stmtCategoria->bind_param('sssii', $nombre, $abreviacion, $icono, $orden, $estado);

    if (!$stmtCategoria->execute()) {
        throw new Exception('No se pudo insertar la categoria.');
    }

    $idCategoria = $bdato->insert_id();
    $stmtCategoria->close();

    $stmtAsignacion = $bdato->prepare("
        INSERT INTO categoria_tecnico (id_categoria, id_tecnico)
        VALUES (?, ?)
    ");

    if (!$stmtAsignacion) {
        throw new Exception('No se pudo preparar la asignacion.');
    }

    $stmtAsignacion->bind_param('ii', $idCategoria, $idTecnico);

    if (!$stmtAsignacion->execute()) {
        throw new Exception('No se pudo asignar el tecnico.');
    }

    $idCategoriaTecnico = $bdato->insert_id();
    $stmtAsignacion->close();

    $bdato->consulta('COMMIT');

    responderCategoria(true, 'Categoria creada correctamente.', [
        'id_categoria' => $idCategoria,
        'id_categoria_tecnico' => $idCategoriaTecnico,
        'orden' => $orden,
    ]);
} catch (Exception $e) {
    $bdato->consulta('ROLLBACK');
    responderCategoria(false, $e->getMessage());
}
