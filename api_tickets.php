<?php
require_once 'class/conexion.php';

$bdato = new MySQL("", "", "");

// Obtener ID del técnico filtrado (si existe)
$idUsuarioFiltro = isset($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : null;

// Consulta para obtener todos los estados de ticket
$queryEstados = "SELECT id, nombre FROM estados_ticket ORDER BY orden";
$resultEstados = $bdato->consulta($queryEstados);
$estados = [];

while ($row = $bdato->fetch_assoc($resultEstados)) {
    $estados[$row['id']] = $row['nombre'];
}

// Inicializar datos
$data = [];

// Si se filtra por un técnico específico
if ($idUsuarioFiltro) {
    // Consulta de tickets solo para el técnico seleccionado
    $query = "SELECT id_estado, COUNT(*) as total
              FROM tickets
              WHERE id_tecnico = $idUsuarioFiltro
              GROUP BY id_estado";

    $result = $bdato->consulta($query);

    // Inicializar con 0 en todos los estados
    $data[$idUsuarioFiltro] = array_fill_keys($estados, 0);

    while ($row = $bdato->fetch_assoc($result)) {
        $estadoNombre = $estados[$row['id_estado']] ?? 'Desconocido';
        $data[$idUsuarioFiltro][$estadoNombre] = (int)$row['total'];
    }
} else {
    // Obtener todos los técnicos del área de informática (id_area_trabajo = 1)
    $queryTecnicos = "SELECT id, CONCAT(nombre, ' ', apellido_paterno) AS nombre 
                      FROM usuarios 
                      WHERE id_area_trabajo = 1
                      ORDER BY nombre";

    $resultTecnicos = $bdato->consulta($queryTecnicos);

    while ($rowTecnico = $bdato->fetch_assoc($resultTecnicos)) {
        $idTecnico = $rowTecnico['id'];
        $nombreTecnico = htmlspecialchars($rowTecnico['nombre']);
        
        // Inicializar los estados en 0 para cada técnico
        $data[$nombreTecnico] = array_fill_keys($estados, 0);

        // Obtener la cantidad de tickets por estado para el técnico
        $queryTickets = "SELECT id_estado, COUNT(*) as total
                         FROM tickets
                         WHERE id_tecnico = $idTecnico
                         GROUP BY id_estado";

        $resultTickets = $bdato->consulta($queryTickets);

        while ($row = $bdato->fetch_assoc($resultTickets)) {
            $estadoNombre = $estados[$row['id_estado']] ?? 'Desconocido';
            $data[$nombreTecnico][$estadoNombre] = (int)$row['total'];
        }
    }
}

// Devolver los datos en formato JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
