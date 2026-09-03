<?php

require_once __DIR__ . '/validar_sesion.php';

$pagina_titulo = 'Dashboard';

require_once __DIR__ . '/includes/header.php';

?>

<div class="page-header">
    <h2><i class="fa-solid fa-gauge"></i> Dashboard</h2>
</div>

<div class="cards-grid">
    <div class="card-stat">
        <div class="card-stat-main">
            <div class="card-stat-icon bg-blue">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="card-stat-info">
                <span class="card-stat-value" id="stat-usuarios">—</span>
                <span class="card-stat-label">Usuarios</span>
            </div>
        </div>
        <canvas class="sparkline" data-values="18,22,20,28,31,35,39"></canvas>
    </div>

    <div class="card-stat">
        <div class="card-stat-main">
            <div class="card-stat-icon bg-green">
                <i class="fa-solid fa-cubes"></i>
            </div>
            <div class="card-stat-info">
                <span class="card-stat-value" id="stat-modulos">—</span>
                <span class="card-stat-label">Módulos activos</span>
            </div>
        </div>
        <canvas class="sparkline" data-values="4,4,5,5,5,6,6"></canvas>
    </div>

    <div class="card-stat">
        <div class="card-stat-main">
            <div class="card-stat-icon bg-orange">
                <i class="fa-solid fa-school"></i>
            </div>
            <div class="card-stat-info">
                <span class="card-stat-value" id="stat-colegios">—</span>
                <span class="card-stat-label">Colegios</span>
            </div>
        </div>
        <canvas class="sparkline" data-values="2,2,3,4,4,5,6"></canvas>
    </div>

    <div class="card-stat">
        <div class="card-stat-main">
            <div class="card-stat-icon bg-purple">
                <i class="fa-solid fa-calendar"></i>
            </div>
            <div class="card-stat-info">
                <span class="card-stat-value" id="stat-eventos">—</span>
                <span class="card-stat-label">Eventos</span>
            </div>
        </div>
        <canvas class="sparkline" data-values="21,28,25,34,39,41,47"></canvas>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3>Bienvenido, <?= htmlspecialchars(Session::get('usuario_nombre')) ?></h3>
    </div>
    <div class="card-body">
        <p>Selecciona un módulo del menú lateral para comenzar a trabajar.</p>
    </div>
</div>

<script>
async function cargarEstadisticas() {
    try {
        const [resU, resM] = await Promise.all([
            fetch('api/usuarios.php?accion=listar'),
            fetch('api/modulos.php?accion=listar'),
        ]);
        const dataU = await resU.json();
        const dataM = await resM.json();

        if (dataU.success) document.getElementById('stat-usuarios').textContent = dataU.data.length;
        if (dataM.success) document.getElementById('stat-modulos').textContent  = dataM.data.length;
    } catch (e) {
        console.warn('No se pudieron cargar estadísticas');
    }
}
cargarEstadisticas();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
