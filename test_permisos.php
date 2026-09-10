<?php
declare(strict_types=1);

require_once __DIR__ . '/PermisosManager.php';

header('Content-Type: text/plain; charset=UTF-8');

try {
    $permisos = new PermisosManager(2); // Usuario 2 (Chavez)

    if ($permisos->esValido()) {
        echo "✅ Conexión exitosa a BD Permisos\n\n";
        echo $permisos->resumen();
    } else {
        echo "❌ Usuario no válido o no encontrado\n";
    }
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage();
}
