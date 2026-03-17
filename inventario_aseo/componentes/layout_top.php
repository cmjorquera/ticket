<?php require __DIR__ . '/head.php'; ?>
<input type="hidden" id="idUsuario" value="<?= (int)$idUsuarioSession ?>">
<div id="wrapper">
    <?php $funciones->menuLateral2($idUsuarioSession, $idPagActual); ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>
                <?php $funciones->cabezera(); ?>
            </nav>
            <div class="container-fluid pb-4">

