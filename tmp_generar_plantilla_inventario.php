<?php
session_id('codex_inventory_template');
session_start();

$_SESSION['id'] = 8;
$_SESSION['nombre'] = 'Codex';
$_SESSION['apellido_paterno'] = 'Inventario';
$_SERVER['SCRIPT_NAME'] = '/inventario/exportar_plantilla.php';

require __DIR__ . '/inventario/exportar_plantilla.php';
