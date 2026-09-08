<?php
include("../../class/conexion.php");

$bdato = new MySQL("", "", "");
$sql = "SELECT c.id_categoria, c.nombre_categoria, c.abreviacion, c.icono, c.orden, c.estado,
               MIN(ct.id_tecnico) AS id_tecnico
        FROM categoria_de_ticket c
        LEFT JOIN categoria_tecnico ct ON ct.id_categoria = c.id_categoria
        GROUP BY c.id_categoria, c.nombre_categoria, c.abreviacion, c.icono, c.orden, c.estado
        ORDER BY c.orden ASC, c.id_categoria ASC";
$resultado = $bdato->consulta($sql);

if (!$resultado || mysqli_num_rows($resultado) === 0) {
    echo '<div class="alert alert-light border mb-0">No hay categorias para mostrar.</div>';
    exit;
}
?>

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 cat-admin-table">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Abreviacion</th>
                <th>Icono</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($resultado)): ?>
                <?php
                $id = (int)$row['id_categoria'];
                $nombre = htmlspecialchars($row['nombre_categoria'] ?? '', ENT_QUOTES, 'UTF-8');
                $abreviacion = htmlspecialchars($row['abreviacion'] ?? '', ENT_QUOTES, 'UTF-8');
                $icono = htmlspecialchars($row['icono'] ?? '', ENT_QUOTES, 'UTF-8');
                $orden = (int)($row['orden'] ?? 0);
                $estado = (int)($row['estado'] ?? 0);
                $idTecnico = (int)($row['id_tecnico'] ?? 0);
                $botonEstadoClase = $estado === 1 ? 'btn-danger' : 'btn-success';
                $botonEstadoTexto = $estado === 1 ? 'Desactivar' : 'Activar';
                $botonEstadoIcono = $estado === 1 ? 'bi-toggle-off' : 'bi-toggle-on';
                ?>
                <tr class="cat-admin-row">
                    <td><?php echo $id; ?></td>
                    <td><?php echo $nombre; ?></td>
                    <td><?php echo $abreviacion !== '' ? $abreviacion : '-'; ?></td>
                    <td>
                        <?php if ($icono !== ''): ?>
                            <i class="bi <?php echo $icono; ?> me-2"></i>
                            <span class="text-muted"><?php echo $icono; ?></span>
                        <?php else: ?>
                            <span class="text-muted">Sin icono</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="cat-admin-actions">
                        <button
                            type="button"
                            class="btn btn-primary btn-sm js-editar-categoria"
                            data-id_categoria="<?php echo $id; ?>"
                            data-nombre_categoria="<?php echo $nombre; ?>"
                            data-abreviacion="<?php echo $abreviacion; ?>"
                            data-icono="<?php echo $icono; ?>"
                            data-orden="<?php echo $orden; ?>"
                            data-id_tecnico="<?php echo $idTecnico; ?>">
                            <i class="bi bi-pencil-square"></i> Editar
                        </button>
                        <button
                            type="button"
                            class="btn <?php echo $botonEstadoClase; ?> btn-sm js-toggle-estado-categoria"
                            data-id_categoria="<?php echo $id; ?>"
                            data-estado="<?php echo $estado; ?>">
                            <i class="bi <?php echo $botonEstadoIcono; ?>"></i> <?php echo $botonEstadoTexto; ?>
                        </button>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
