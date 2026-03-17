<?php
session_start();
require_once 'class/conexion.php';
$bdato = new MySQL("", "", "");

$idUsuarioSession = $_SESSION['id'];
$perfilSolicitado = $_GET['perfil'] ?? 'auto';

// Detectar perfil real si no se envía por GET
if ($perfilSolicitado === 'auto') {
    $sqlPerfil = "SELECT id_perfil FROM usuario_perfil WHERE id_usuario = $idUsuarioSession";
    $resultPerfil = $bdato->consulta($sqlPerfil);
    $perfiles = [];
    while ($row = $bdato->fetch_array($resultPerfil)) {
        $perfiles[] = $row['id_perfil'];
    }
    $perfilSolicitado = in_array(3, $perfiles) ? 'admin' : (in_array(2, $perfiles) ? 'tecnico' : 'usuario');
}

$filtro = "";
if ($perfilSolicitado === 'usuario') {
    $filtro = "AND t.id_usuario = $idUsuarioSession";
} elseif ($perfilSolicitado === 'tecnico') {
    $filtro = "AND t.id_tecnico = $idUsuarioSession";
}

$sql = "
    SELECT 
        c.abreviacion AS categoria,
        e.nombre AS estado,
        COUNT(*) AS cantidad
    FROM tickets t
    JOIN categoria_de_ticket c ON t.id_categoria_ticket = c.id_categoria
    JOIN estados_ticket e ON t.id_estado = e.id
    $filtro
    GROUP BY c.abreviacion, e.nombre, c.orden
    ORDER BY c.orden ASC, e.nombre ASC
";

$resultado = $bdato->consulta($sql);

$categorias = [];
$estados = [];
$datosPorEstado = [];
// Obtener todos los estados posibles primero
$sqlEstados = "SELECT nombre FROM estados_ticket  ORDER BY orden";
$resultEstados = $bdato->consulta($sqlEstados);
$estados = [];

while ($row = $bdato->fetch_array($resultEstados)) {
    $estados[] = $row['nombre'];
}

// Procesar resultados de los tickets
$categorias = [];
$datosPorEstado = [];

while ($row = $bdato->fetch_array($resultado)) {
    $categoria  = $row['categoria'];
    $estado      = $row['estado'];
    $cantidad   = (int) $row['cantidad'];

    if (!in_array($categoria, $categorias)) $categorias[] = $categoria;

    $datosPorEstado[$estado][$categoria] = $cantidad;
}

// Asegurar que cada estado tenga valor para todas las categorías
$series = [];
foreach ($estados as $estado) {
    $data = [];
    foreach ($categorias as $cat) {
        $data[] = $datosPorEstado[$estado][$cat] ?? 0;
    }
    $series[] = ['name' => $estado, 'data' => $data];
}

echo json_encode([
    'series' => $series,
    'categorias' => $categorias
]);

