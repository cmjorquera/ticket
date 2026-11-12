<?php
/**
 * Formulario compartido para registrar y editar monitores.
 * Variables esperadas del contexto:
 *   $modo           string 'crear' | 'editar'
 *   $monitor        array|null  datos del monitor (solo en modo editar)
 *   $colegioUsuario array       ['id_colegio', 'nom_colegio']
 *   $ubicaciones    array       lista de ubicaciones del colegio
 *   $usuarios       array       lista de usuarios
 *   $estados        array       lista de estados
 */
$modoEditar   = ($modo ?? 'crear') === 'editar';
$monitor      = $monitor ?? [];
$compra       = $monitor['compra'] ?? [];
$fotosActuales = $monitor['fotos'] ?? [];
$idMonitor    = (int)($monitor['id_monitor'] ?? 0);
$nomColegio   = htmlspecialchars($colegioUsuario['nom_colegio'] ?? 'Sin colegio', ENT_QUOTES, 'UTF-8');
$actionUrl    = $modoEditar ? 'actualizar_monitor.php' : 'guardar_monitor.php';
?>
<form id="formMonitor" action="<?= $actionUrl ?>" method="POST" enctype="multipart/form-data" novalidate>
    <?php if ($modoEditar): ?>
        <input type="hidden" name="id_monitor" value="<?= $idMonitor ?>">
    <?php endif; ?>

    <?php if ($colegioUsuario['id_colegio'] ?? 0): ?>
        <div class="alert alert-info py-2 mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-building-check"></i>
            <span>Colegio: <strong><?= $nomColegio ?></strong></span>
        </div>
    <?php else: ?>
        <div class="alert alert-danger py-2 mb-4">No se pudo determinar el colegio del usuario. Contacta al administrador.</div>
    <?php endif; ?>

    <!-- Identificacion -->
    <div class="card shadow-sm border-0 inv-panel mb-4">
        <div class="card-body">
            <h6 class="inv-section-title mb-3"><i class="bi bi-display me-2 text-primary"></i>Identificación del monitor</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nombre del monitor <span class="text-danger">*</span></label>
                    <input type="text" name="nombre_monitor" class="form-control" required
                           value="<?= inventario_h($monitor['nombre_monitor'] ?? '') ?>"
                           placeholder="Ej: Monitor Sala Cómputo 1">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Marca</label>
                    <input type="text" name="marca" class="form-control"
                           value="<?= inventario_h($monitor['marca'] ?? '') ?>"
                           placeholder="Samsung, Dell, LG...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Modelo</label>
                    <input type="text" name="modelo" class="form-control"
                           value="<?= inventario_h($monitor['modelo'] ?? '') ?>"
                           placeholder="Ej: S24F350FH">
                </div>
                <div class="col-md-3">
                    <label class="form-label">N° de serie <span class="text-danger">*</span></label>
                    <input type="text" name="numero_serie" class="form-control" required
                           value="<?= inventario_h($monitor['numero_serie'] ?? '') ?>"
                           placeholder="SN: ...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Código interno</label>
                    <input type="text" name="codigo_interno" class="form-control"
                           value="<?= inventario_h($monitor['codigo_interno'] ?? '') ?>"
                           placeholder="MON-001">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tamaño (pulgadas)</label>
                    <input type="text" name="tamano_monitor" class="form-control"
                           value="<?= inventario_h($monitor['tamano_monitor'] ?? '') ?>"
                           placeholder='24"'>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Resolución</label>
                    <input type="text" name="resolucion_monitor" class="form-control"
                           value="<?= inventario_h($monitor['resolucion_monitor'] ?? '') ?>"
                           placeholder="1920x1080">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo de panel</label>
                    <input type="text" name="tipo_panel" class="form-control"
                           value="<?= inventario_h($monitor['tipo_panel'] ?? '') ?>"
                           placeholder="IPS, TN, VA...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo de conexión</label>
                    <input type="text" name="tipo_conexion" class="form-control"
                           value="<?= inventario_h($monitor['tipo_conexion'] ?? '') ?>"
                           placeholder="HDMI, VGA, DisplayPort...">
                </div>
            </div>
        </div>
    </div>

    <!-- Asignacion y estado -->
    <div class="card shadow-sm border-0 inv-panel mb-4">
        <div class="card-body">
            <h6 class="inv-section-title mb-3"><i class="bi bi-person-check me-2 text-primary"></i>Asignación y estado</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Estado <span class="text-danger">*</span></label>
                    <select name="id_estado" class="form-select" required>
                        <option value="">Selecciona estado...</option>
                        <?php foreach ($estados as $est): ?>
                            <option value="<?= (int)$est['id_estado'] ?>"
                                <?= (int)($monitor['id_estado'] ?? 1) === (int)$est['id_estado'] ? 'selected' : '' ?>>
                                <?= inventario_h($est['nombre_estado']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ubicación <span class="text-danger">*</span></label>
                    <?php if (empty($ubicaciones)): ?>
                        <div class="alert alert-warning py-2 mb-0">No hay ubicaciones disponibles para este colegio.</div>
                    <?php else: ?>
                        <select name="id_ubicacion" class="form-select" required>
                            <option value="">Selecciona ubicación...</option>
                            <?php foreach ($ubicaciones as $ubic): ?>
                                <option value="<?= (int)$ubic['id_ubicacion'] ?>"
                                    <?= (int)($monitor['id_ubicacion'] ?? 0) === (int)$ubic['id_ubicacion'] ? 'selected' : '' ?>>
                                    <?= inventario_h(($ubic['tipo_ubicacion'] ? $ubic['tipo_ubicacion'] . ' — ' : '') . $ubic['nombre_ubicacion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Usuario asignado</label>
                    <select name="id_usuario_asignado" class="form-select">
                        <option value="">Sin asignar</option>
                        <?php foreach ($usuarios as $usu): ?>
                            <option value="<?= (int)$usu['id'] ?>"
                                <?= (int)($monitor['id_usuario_asignado'] ?? 0) === (int)$usu['id'] ? 'selected' : '' ?>>
                                <?= inventario_h($usu['nombre_completo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($modoEditar): ?>
                <div class="col-12">
                    <label class="form-label">Observación del movimiento de ubicación <span class="text-muted small">(opcional, solo si cambia la ubicación)</span></label>
                    <textarea name="observacion_movimiento" class="form-control" rows="2"
                              placeholder="Motivo del traslado..."></textarea>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Compra -->
    <div class="card shadow-sm border-0 inv-panel mb-4">
        <div class="card-body">
            <h6 class="inv-section-title mb-3"><i class="bi bi-receipt me-2 text-primary"></i>Datos de compra</h6>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Proveedor</label>
                    <input type="text" name="proveedor" class="form-control"
                           value="<?= inventario_h($compra['proveedor'] ?? '') ?>"
                           placeholder="Nombre del proveedor">
                </div>
                <div class="col-md-2">
                    <label class="form-label">N° factura</label>
                    <input type="text" name="numero_factura" class="form-control"
                           value="<?= inventario_h($compra['numero_factura'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha de compra</label>
                    <input type="date" name="fecha_compra" class="form-control"
                           value="<?= inventario_h($compra['fecha_compra'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Valor ($)</label>
                    <input type="number" name="valor_monitor" class="form-control" min="0"
                           value="<?= (int)($compra['valor_monitor'] ?? 0) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Observación compra</label>
                    <input type="text" name="observacion_compra" class="form-control"
                           value="<?= inventario_h($compra['observacion'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Observacion general -->
    <div class="card shadow-sm border-0 inv-panel mb-4">
        <div class="card-body">
            <h6 class="inv-section-title mb-3"><i class="bi bi-chat-text me-2 text-primary"></i>Observación</h6>
            <textarea name="observacion" class="form-control" rows="3"
                      placeholder="Detalles adicionales del monitor..."><?= inventario_h($monitor['observacion'] ?? '') ?></textarea>
        </div>
    </div>

    <!-- Fotos -->
    <div class="card shadow-sm border-0 inv-panel mb-4">
        <div class="card-body">
            <h6 class="inv-section-title mb-3"><i class="bi bi-images me-2 text-primary"></i>Fotografías</h6>

            <?php if ($modoEditar && !empty($fotosActuales)): ?>
                <p class="text-muted small mb-3">Fotos actuales. Marca el radio para establecer la foto principal. Marca la casilla para eliminar.</p>
                <div class="inv-photo-grid mb-4">
                    <?php foreach ($fotosActuales as $foto):
                        $rutaFoto = '/' . ltrim($foto['ruta_foto'], '/');
                        $idFoto   = (int)$foto['id_foto'];
                        $esPrincipal = (int)$foto['principal'] === 1;
                    ?>
                        <div class="inv-photo-item <?= $esPrincipal ? 'inv-photo-principal' : '' ?>">
                            <img src="<?= htmlspecialchars($rutaFoto, ENT_QUOTES, 'UTF-8') ?>"
                                 alt="Foto monitor" class="inv-photo-thumb">
                            <?php if ($esPrincipal): ?>
                                <span class="inv-badge-principal-overlay">⭐ Principal</span>
                            <?php endif; ?>
                            <div class="inv-photo-actions">
                                <label class="inv-photo-action-lbl" title="Marcar como principal">
                                    <input type="radio" name="foto_principal" value="<?= $idFoto ?>"
                                           <?= $esPrincipal ? 'checked' : '' ?>>
                                    <i class="bi bi-star-fill text-warning"></i>
                                </label>
                                <label class="inv-photo-action-lbl" title="Eliminar foto">
                                    <input type="checkbox" name="fotos_eliminar[]" value="<?= $idFoto ?>">
                                    <i class="bi bi-trash text-danger"></i>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <label class="form-label">Subir <?= $modoEditar ? 'fotos adicionales' : 'fotos' ?></label>
            <input type="file" id="fotos_monitor" name="fotos_monitor[]" class="form-control" multiple
                   accept="image/jpeg,image/png,image/webp,image/gif">
            <div class="form-text">Formatos permitidos: JPG, PNG, WEBP, GIF. La primera foto subida se marcará como principal si no existe una.</div>

            <div id="previewFotosMonitor" class="mt-3"></div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="d-flex gap-2 justify-content-end">
        <a href="index.php?tab=monitores" class="btn btn-light border">Cancelar</a>
        <button type="submit" class="btn btn-primary" id="btnGuardarMonitor">
            <i class="bi bi-save me-1"></i><?= $modoEditar ? 'Actualizar monitor' : 'Registrar monitor' ?>
        </button>
    </div>
</form>

<script>
document.getElementById('fotos_monitor').addEventListener('change', function () {
    const container = document.getElementById('previewFotosMonitor');
    container.innerHTML = '';
    const files = Array.from(this.files || []);
    if (files.length === 0) return;

    if (files.length === 1) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'inv-preview-single';
            container.appendChild(img);
        };
        reader.readAsDataURL(files[0]);
        return;
    }

    const carouselId = 'invCarouselMonPreview';
    let items = '';
    let thumbs = '';
    files.forEach(function (file, idx) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('carouselImgMon' + idx).src = e.target.result;
            document.getElementById('carouselThumbMon' + idx).src = e.target.result;
        };
        reader.readAsDataURL(file);
        items += `<div class="carousel-item ${idx === 0 ? 'active' : ''}">
            <div class="inv-carousel-img-wrap"><img id="carouselImgMon${idx}" src="" class="inv-carousel-img" alt="${file.name}"></div>
        </div>`;
        thumbs += `<img id="carouselThumbMon${idx}" src="" class="inv-carousel-thumb" data-idx="${idx}" alt="">`;
    });

    container.innerHTML = `
        <div id="${carouselId}" class="carousel slide mb-2">
            <div class="carousel-inner">${items}</div>
            <button class="carousel-control-prev" type="button" data-bs-target="#${carouselId}" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#${carouselId}" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
        <div class="inv-thumb-strip">${thumbs}</div>`;

    container.querySelectorAll('.inv-carousel-thumb').forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            bootstrap.Carousel.getOrCreateInstance(document.getElementById(carouselId)).to(parseInt(this.dataset.idx, 10));
        });
    });
});

document.getElementById('formMonitor').addEventListener('submit', function (e) {
    e.preventDefault();
    const btn = document.getElementById('btnGuardarMonitor');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

    const formData = new FormData(this);
    fetch(this.action, { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (response) {
            if (response.ok) {
                Swal.fire({ icon: 'success', title: 'Guardado', text: response.mensaje, confirmButtonText: 'Ver ficha' })
                    .then(function () {
                        if (response.redirect) window.location.href = response.redirect;
                    });
            } else {
                Swal.fire('Error', response.mensaje, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-save me-1"></i><?= $modoEditar ? 'Actualizar monitor' : 'Registrar monitor' ?>';
            }
        })
        .catch(function () {
            Swal.fire('Error', 'No fue posible conectar con el servidor.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save me-1"></i><?= $modoEditar ? 'Actualizar monitor' : 'Registrar monitor' ?>';
        });
});
</script>
