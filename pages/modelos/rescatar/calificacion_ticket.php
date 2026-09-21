<?php
header('Content-Type: application/json; charset=utf-8');

// Datos quemados (hardcoded) para pruebas
$datos = [
    [
        'id' => 4,
        'calificacion' => 'Satisfactorio',
        'orden' => 4
    ],
    [
        'id' => 3,
        'calificacion' => 'Insatisfactorio',
        'orden' => 3
    ],
    [
        'id' => 2,
        'calificacion' => 'Incompleto',
        'orden' => 2
    ],
    [
        'id' => 1,
        'calificacion' => 'Requiere revisión',
        'orden' => 1
    ]
];

echo json_encode($datos);
