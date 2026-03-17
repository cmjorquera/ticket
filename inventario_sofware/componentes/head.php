<?php $tituloPagina = isset($tituloPagina) ? $tituloPagina : 'Inventario de software'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= inventario_h($tituloPagina) ?></title>
    <link rel="icon" type="image/x-icon" href="<?= inventario_h(inventario_sistema_url('imagenes/logo_seduc.png')) ?>">
    <link href="<?= inventario_h(inventario_sistema_url('vendor/fontawesome-free/css/all.min.css')) ?>" rel="stylesheet" type="text/css">
    <link href="<?= inventario_h(inventario_sistema_url('css/sb-admin-2.min.css')) ?>" rel="stylesheet">
    <link href="<?= inventario_h(inventario_sistema_url('css/modalesTicket.css')) ?>" rel="stylesheet">
    <link href="<?= inventario_h(inventario_sistema_url('css/estilo.css')) ?>" rel="stylesheet">
    <link href="<?= inventario_h(inventario_sistema_url('css/bitacora.css')) ?>" rel="stylesheet">
    <link href="<?= inventario_h(inventario_sistema_url('css/contenedor.css')) ?>" rel="stylesheet">
    <link href="<?= inventario_h(inventario_sistema_url('css/principal.css')) ?>" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/inventario.css?v=<?= inventario_h(inventario_asset_version('css/inventario.css')) ?>">
</head>
<body id="page-top" class="inventario-body">
