<div class="row mb-6">
    <div class="container mt-4">
        <div class="usuarios-panel">
            <div class="usuarios-panel__header">
                <div>
                    <h5 class="usuarios-panel__title">Tabla usuarios</h5>
                    <p class="usuarios-panel__text">Administra usuarios y define qué menú puede ver cada cuenta.</p>
                </div>
                <a href="#" class="btn btn-primary usuarios-panel__cta" id="buttonAgregarTicket" onclick="agregarUsuario()">
                    <i class="bi bi-person-plus-fill me-2"></i>Agregar usuario
                </a>
            </div>
            <div class="usuarios-panel__body">
                <?php echo $funciones->listaUsuarios(); ?>
            </div>
        </div>
    </div>
</div>
