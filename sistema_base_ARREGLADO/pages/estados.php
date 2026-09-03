<?php

require_once __DIR__ . '/../validar_sesion.php';

$pagina_titulo = 'Estados del sistema';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">
    <h2><i class="fa-solid fa-circle-info"></i> Estados del sistema</h2>
</div>

<div class="system-grid">
    <div class="system-state">
        <svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18A2 2 0 0 0 3.11 21h17.78a2 2 0 0 0 1.72-3L14.14 3.86a2 2 0 0 0-3.85 0Z"/></svg>
        <h3>404 no encontrado</h3>
        <p>El recurso solicitado no existe o fue movido.</p>
        <button class="btn btn-primary mt-3" onclick="location.href='../dashboard.php'">Volver al dashboard</button>
    </div>
    <div class="system-state">
        <svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>
        <h3>Sin permisos</h3>
        <p>Tu perfil no tiene acceso a esta sección.</p>
        <button class="btn btn-outline mt-3" onclick="showToast('info','Solicitud enviada','El administrador revisará el acceso.')">Solicitar acceso</button>
    </div>
    <div class="system-state">
        <svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 1l22 22M16.72 11.06A10.94 10.94 0 0 1 21 12M5 12a7 7 0 0 1 7-7c1.13 0 2.2.27 3.14.75M8.53 16.11A4 4 0 0 1 12 14c.64 0 1.25.15 1.78.42"/></svg>
        <h3>Sin conexión</h3>
        <p>No se pudo conectar con el servidor.</p>
        <button class="btn btn-primary mt-3" onclick="showToast('warning','Reintentando','Verificando conexión.')">Reintentar</button>
    </div>
    <div class="system-state">
        <svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12a9 9 0 1 1-6.22-8.56"/></svg>
        <h3>Cargando</h3>
        <p>Estamos preparando la información solicitada.</p>
        <button class="btn btn-outline mt-3" onclick="showSkeleton('state-demo',3,3)">Ver carga</button>
    </div>
    <div class="system-state">
        <svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg>
        <h3>Operación exitosa</h3>
        <p>Los cambios fueron guardados correctamente.</p>
        <button class="btn btn-primary mt-3" onclick="showToast('success','Listo','Operación completada.')">Continuar</button>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body" id="state-demo"></div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

