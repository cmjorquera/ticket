<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../class/conexion.php';

function responderEdicionCategoria($success, $message = '')
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderEdicionCategoria(false, 'Metodo no permitido');
}

$idCategoria = isset($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : 0;
$idTecnico = isset($_POST['id_tecnico']) ? (int)$_POST['id_tecnico'] : 0;
$nombre = trim($_POST['nombre_categoria'] ?? '');
$abreviacion = trim($_POST['abreviacion'] ?? '');
$icono = trim($_POST['icono'] ?? '');

if ($idCategoria <= 0) {
    responderEdicionCategoria(false, 'Categoria invalida');
}

if ($idTecnico <= 0) {
    responderEdicionCategoria(false, 'Debes seleccionar un tecnico');
}

if ($nombre === '') {
    responderEdicionCategoria(false, 'El nombre es obligatorio');
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
    responderEdicionCategoria(false, 'No se pudo validar el tecnico');
}

$stmtTecnico->bind_param('i', $idTecnico);
if (!$stmtTecnico->execute()) {
    responderEdicionCategoria(false, 'No se pudo validar el tecnico');
}
$stmtTecnico->store_result();

if ($stmtTecnico->num_rows === 0) {
    responderEdicionCategoria(false, 'El tecnico seleccionado no esta disponible');
}

$stmtTecnico->close();

$bdato->consulta('START TRANSACTION');

try {
    $stmtCategoria = $bdato->prepare("
        UPDATE categoria_de_ticket
        SET nombre_categoria = ?,
            abreviacion = ?,
            icono = ?
        WHERE id_categoria = ?
    ");

    if (!$stmtCategoria) {
        throw new Exception('No se pudo preparar la categoria');
    }

    $stmtCategoria->bind_param('sssi', $nombre, $abreviacion, $icono, $idCategoria);

    if (!$stmtCategoria->execute()) {
        throw new Exception('No se pudo actualizar la categoria');
    }

    $stmtCategoria->close();

    $bdato->consulta("DELETE FROM categoria_tecnico WHERE id_categoria = $idCategoria");

    $stmtAsignacion = $bdato->prepare("
        INSERT INTO categoria_tecnico (id_categoria, id_tecnico)
        VALUES (?, ?)
    ");

    if (!$stmtAsignacion) {
        throw new Exception('No se pudo preparar la asignacion');
    }

    $stmtAsignacion->bind_param('ii', $idCategoria, $idTecnico);

    if (!$stmtAsignacion->execute()) {
        throw new Exception('No se pudo asignar el tecnico');
    }

    $stmtAsignacion->close();

    $bdato->consulta('COMMIT');
    responderEdicionCategoria(true, 'Categoria actualizada correctamente');
} catch (Exception $e) {
    $bdato->consulta('ROLLBACK');
    responderEdicionCategoria(false, $e->getMessage());
}
