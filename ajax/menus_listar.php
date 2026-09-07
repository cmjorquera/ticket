<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
Sesion::requerir();

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $idUsuario = max(0, (int) ($_GET['id_usuario'] ?? 0));
    $menus = $db->fetchAll(
        "SELECT m.id_menu, m.nombre, m.archivo, m.icono, m.orden,
                CASE WHEN EXISTS (
                    SELECT 1
                      FROM permisos_menu_1 pm
                     WHERE pm.id_usuario = ?
                       AND pm.id_menu1 = m.id_menu
                       AND pm.id_tipo_permiso = 1
                ) THEN 1 ELSE 0 END AS permitido
           FROM menu_1 m
       ORDER BY CAST(m.orden AS UNSIGNED) ASC, m.nombre ASC",
        [$idUsuario]
    );
    responder_json(['ok' => true, 'data' => $menus]);
} catch (Throwable $e) {
    responder_json(['ok' => false, 'mensaje' => 'No fue posible consultar los menús.'], 500);
}

