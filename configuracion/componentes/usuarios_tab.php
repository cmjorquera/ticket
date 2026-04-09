<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h6 class="m-0 font-weight-bold text-primary">Tabla usuarios</h6>
            <p class="mb-0 text-muted">Administra usuarios y define qué menu puede ver cada cuenta.</p>
        </div>

        <a href="#" class="btn btn-primary" id="buttonAgregarTicket" onclick="agregarUsuario()">
            <i class="bi bi-person-plus-fill me-2"></i>Agregar usuario
        </a>
    </div>
    <div class="card-body p-0">
        <?php echo $funciones->listaUsuarios(); ?>
    </div>
</div>
