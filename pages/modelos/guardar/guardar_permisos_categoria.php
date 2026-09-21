<?php
include("../../class/conexion.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metodo no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    echo json_encode(['status' => 'error', 'message' => 'JSON invalido']);
    exit;
}

$idTecnico = isset($data['id_usuario']) ? intval($data['id_usuario']) : 0;
$idsCategorias = isset($data['ids_categorias']) && is_array($data['ids_categorias'])
    ? array_values(array_unique(array_map('intval', $data['ids_categorias'])))
    : [];

if ($idTecnico <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'id_usuario invalido']);
    exit;
}

$bdato = new MySQL("", "", "");

// Rescatar asignaciones actuales del tecnico
$queryActuales = "SELECT id_categoria_tecnico, id_categoria
                  FROM categoria_tecnico
                  WHERE id_tecnico = $idTecnico";
$resultActuales = $bdato->consulta($queryActuales);

$actuales = [];
while ($row = mysqli_fetch_assoc($resultActuales)) {
    $actuales[(int)$row['id_categoria']] = (int)$row['id_categoria_tecnico'];
}

$categoriasNuevas = array_fill_keys($idsCategorias, true);

// Insertar categorias nuevas
foreach ($idsCategorias as $idCategoria) {
    if ($idCategoria <= 0) {
        continue;
    }

    if (!isset($actuales[$idCategoria])) {
        $queryInsert = "INSERT INTO categoria_tecnico (id_tecnico, id_categoria)
                        VALUES ($idTecnico, $idCategoria)";
        $bdato->consulta($queryInsert);
    }
}

// Eliminar categorias que ya no quedaron marcadas
foreach ($actuales as $idCategoria => $idCategoriaTecnico) {
    if (!isset($categoriasNuevas[$idCategoria])) {
        $queryDelete = "DELETE FROM categoria_tecnico
                        WHERE id_categoria_tecnico = $idCategoriaTecnico";
        $bdato->consulta($queryDelete);
    }
}

echo json_encode([
    'status' => 'ok',
    'id_usuario' => $idTecnico,
    'ids_categorias' => $idsCategorias
]);
