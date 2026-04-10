<?php
$modo = $modo ?? 'crear';
$sitio = $sitio ?? [];
$action = $modo === 'editar' ? 'actualizar_sitio_web.php' : 'guardar_sitio_web.php';
?>
<form id="formInventario" action="<?= inventario_h($action) ?>" method="post" class="inv-form">
    <?php if ($modo === 'editar'): ?><input type="hidden" name="id_sitio" value="<?= (int)$sitio['id_sitio'] ?>"><?php endif; ?>
    <div class="accordion inv-accordion" id="accordionSitio">
        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#datosSitio">Datos del sitio</button></h2>
            <div id="datosSitio" class="accordion-collapse collapse show" data-bs-parent="#accordionSitio"><div class="accordion-body"><div class="row g-3">
                <div class="col-md-4"><label class="form-label">Colegio</label><select name="id_colegio" class="form-select" required><option value="">Seleccione</option><?php foreach ($colegios as $colegio): ?><option value="<?= (int)$colegio['id_colegio'] ?>" <?= (int)($sitio['id_colegio'] ?? 0) === (int)$colegio['id_colegio'] ? 'selected' : '' ?>><?= inventario_h($colegio['nom_colegio']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Responsable</label><select name="id_usuario_responsable" class="form-select"><option value="0">Sin asignar</option><?php foreach ($usuarios as $usuario): ?><option value="<?= (int)$usuario['id'] ?>" <?= (int)($sitio['id_usuario_responsable'] ?? 0) === (int)$usuario['id'] ? 'selected' : '' ?>><?= inventario_h($usuario['nombre_completo']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Tipo</label><select name="tipo_sitio" class="form-select" required><?php foreach ($tiposSitio as $tipo): ?><option value="<?= inventario_h($tipo) ?>" <?= ($sitio['tipo_sitio'] ?? 'Web') === $tipo ? 'selected' : '' ?>><?= inventario_h($tipo) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Nombre</label><input type="text" name="nombre_sitio" class="form-control" value="<?= inventario_h($sitio['nombre_sitio'] ?? '') ?>" required></div>
                <div class="col-md-4"><label class="form-label">URL / Dominio</label><input type="text" name="url_sitio" class="form-control" value="<?= inventario_h($sitio['url_sitio'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Hosting / proveedor</label><input type="text" name="proveedor_hosting" class="form-control" value="<?= inventario_h($sitio['proveedor_hosting'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Estado</label><select name="estado_sitio" class="form-select" required><?php foreach ($estadosSitio as $estado): ?><option value="<?= inventario_h($estado) ?>" <?= ($sitio['estado_sitio'] ?? 'Activo') === $estado ? 'selected' : '' ?>><?= inventario_h($estado) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-8"><label class="form-label">Observaciones</label><textarea name="observaciones" class="form-control" rows="3"><?= inventario_h($sitio['observaciones'] ?? '') ?></textarea></div>
            </div></div></div>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4"><a href="index.php" class="btn btn-secondary inv-system-btn">Volver</a><button type="submit" class="btn btn-primary inv-system-btn"><?= $modo === 'editar' ? 'Actualizar sitio' : 'Guardar sitio' ?></button></div>
</form>
