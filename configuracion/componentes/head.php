<?php
$tituloPagina = isset($tituloPagina) ? $tituloPagina : 'Configuracion';
$assetPrefix = isset($assetPrefix) ? $assetPrefix : '../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($assetPrefix . 'imagenes/logo_seduc.png', ENT_QUOTES, 'UTF-8'); ?>">
    <link href="<?php echo htmlspecialchars($assetPrefix . 'vendor/fontawesome-free/css/all.min.css', ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo htmlspecialchars($assetPrefix . 'css/sb-admin-2.min.css', ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet">
    <link href="<?php echo htmlspecialchars($assetPrefix . 'css/modalesTicket.css', ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet">
    <link href="<?php echo htmlspecialchars($assetPrefix . 'css/estilo.css', ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet">
    <link href="<?php echo htmlspecialchars($assetPrefix . 'css/bitacora.css', ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet">
    <link href="<?php echo htmlspecialchars($assetPrefix . 'css/contenedor.css', ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet">
    <link href="<?php echo htmlspecialchars($assetPrefix . 'css/principal.css', ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet">
    <link href="<?php echo htmlspecialchars($assetPrefix . 'css/ticket_admin.css', ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css" rel="stylesheet">
    <link href="<?php echo htmlspecialchars($assetPrefix . 'configuracion/css/index.css', ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet">
    <link href="<?php echo htmlspecialchars($assetPrefix . 'configuracion/css/mantenimiento_tickets.css', ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet">
</head>
