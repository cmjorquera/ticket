<?php

require_once __DIR__ . '/validar_sesion.php';

$pagina_titulo = 'Dashboard';

$stats = [
    'usuarios' => 0,
    'colegios' => 0,
    'eventos' => 0,
    'modulos' => 0,
];

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $row = $db->fetchOne(
        "SELECT
            (SELECT COUNT(*) FROM usuarios WHERE estado = 'activo') AS usuarios,
            (SELECT COUNT(*) FROM colegio) AS colegios,
            (SELECT COUNT(*) FROM eventos WHERE MONTH(fecha) = MONTH(CURDATE())) AS eventos,
            (SELECT COUNT(*) FROM menu_1) AS modulos",
        []
    );
    if ($row) {
        $stats = $row;
    }
} catch (\Throwable $e) {
    // Se conservan los valores de respaldo si la consulta no está disponible.
}

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
                <span class="card-stat-value" id="stat-usuarios"><?= (int) $stats['usuarios'] ?></span>
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
                <span class="card-stat-value" id="stat-modulos"><?= (int) $stats['modulos'] ?></span>
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
                <span class="card-stat-value" id="stat-colegios"><?= (int) $stats['colegios'] ?></span>
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
                <span class="card-stat-value" id="stat-eventos"><?= (int) $stats['eventos'] ?></span>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
