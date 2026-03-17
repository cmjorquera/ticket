<?php
    session_start();
    require_once '../class/conexion.php';
    require_once '../class/funciones.php';
    
    $funciones          = new Funciones();
    $idUsuarioSession   = htmlspecialchars($_SESSION['id']);
    $idPagActual        = "9";  // Página de bitácora
    
    // *************** INSTANCIA DE LAS FUNCIONES
    $equipos                    = $funciones->listarEquipos();
    $usuarios                   = $funciones->listarUsuarios();
    $tiposDispositivos          = $funciones->listarTiposDispositivos();
    $listarOtrosDispositivos    = $funciones->listarOtrosDispositivos();
    $listarTodosDispositivosqr  = $funciones->listarTodosDispositivosqr();


?>

<!DOCTYPE html>
<html lang="es">
<meta charset="UTF-8">

<head>
    <?php $funciones->header(); ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Incluir jQuery -->
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>


</head>


<body id="page-top">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="table-responsive">
                    <table id="tablaDispositivos" class="table table-bordered table-hover table-striped">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>✔</th>
                                <th>Tipo</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Serie</th>
                                <th>Proveedor</th>
                                <th>Asignado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="otrosDispositivosTableBody">
                            <?php if (!empty($listarOtrosDispositivos)): ?>
                                <?php foreach ($listarOtrosDispositivos as $dispositivo): ?>
                                    <tr id="dispositivo_<?php echo $dispositivo['id_dispositivo']; ?>">
                                        <td><input type="checkbox" class="seleccionDispositivo"
                                            name="dispositivo_<?php echo $dispositivo['id_dispositivo']; ?>"
                                            id="dispositivo_<?php echo $dispositivo['id_dispositivo']; ?>"
                                            value="<?php echo $dispositivo['id_dispositivo']; ?>"></td>

                                        <td><?php echo !empty($dispositivo['tipo']) ? htmlspecialchars(mb_convert_encoding($dispositivo['tipo'], 'UTF-8', 'auto')) : '-----'; ?></td>
                                        <td><?php echo !empty($dispositivo['marca']) ? htmlspecialchars($dispositivo['marca']) : '-----'; ?></td>
                                        <td><?php echo !empty($dispositivo['modelo']) ? htmlspecialchars($dispositivo['modelo']) : '-----'; ?></td>
                                        <td><?php echo !empty($dispositivo['n_serie']) ? htmlspecialchars($dispositivo['n_serie']) : '-----'; ?></td>
                                        <td><?php echo !empty($dispositivo['proveedor']) ? htmlspecialchars($dispositivo['proveedor']) : '-----'; ?></td>
                                        <td>
                                            <?php echo (!empty($dispositivo['usuario_nombre']) && !empty($dispositivo['usuario_apellido']))
                                                ? htmlspecialchars($dispositivo['usuario_nombre'] . ' ' . $dispositivo['usuario_apellido'])
                                                : '-----'; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-primary btn-sm"
                                                    onclick="editarDispositivo(<?php echo $dispositivo['id_dispositivo']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm"
                                                    onclick="eliminarDispositivo(<?php echo $dispositivo['id_dispositivo']; ?>)">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                                <button class="btn btn-secondary btn-sm"
                                                    onclick="verQrDispositivo(<?php echo $dispositivo['id_dispositivo']; ?>)">
                                                    <i class="fas fa-qrcode"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">No se encontraron dispositivos registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php $funciones->footer(); ?>
</body>

<?php $funciones->script(); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#tablaDispositivos')) {
        $('#tablaDispositivos').DataTable().destroy();
    }
    $('#tablaDispositivos').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        pageLength: 10,
        responsive: true,
        ordering: true
    });
});
</script>

</html>