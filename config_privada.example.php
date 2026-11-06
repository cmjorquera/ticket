<?php
/**
 * EJEMPLO de configuración privada del Sistema SEDUC. NO contiene datos reales.
 *
 * Cómo usarlo:
 *   - Servidor (QA o producción): copiar a
 *       <home de la cuenta>/config_privada/ticket_config.php
 *     Ejemplo QA: /home3/crist668/config_privada/ticket_config.php
 *     Permisos: carpeta 700, archivo 600.
 *   - Desarrollo local: copiar a la raíz del proyecto como
 *       config_privada.local.php   (está en .gitignore)
 *
 * Nunca subir a GitHub un archivo con contraseñas reales.
 */

return [
    // 'local', 'qa' o 'produccion'
    'entorno' => 'qa',

    // BD principal. Usada por clases/Conexion.php.
    // El nombre final es prefijo + nombre pedido en Conexion::getInstance(),
    // por ejemplo 'crist668_' + 'sistema_panel_central'.
    'db' => [
        'host'     => 'localhost',
        'usuario'  => 'CAMBIAR_usuario_bd',
        'password' => 'CAMBIAR_password_bd',
        'prefijo'  => 'crist668_',
        'charset'  => 'utf8',
    ],

    // BD de permisos. Usada por PermisosManager.php y gestionar_permisos.php.
    'db_permisos' => [
        'host'     => 'localhost',
        'usuario'  => 'CAMBIAR_usuario_bd',
        'password' => 'CAMBIAR_password_bd',
        'nombre'   => 'crist668_logica_permisos',
        'charset'  => 'utf8mb4',
    ],

    // Clase antigua class_odd/conexion.php (no usada actualmente).
    // Puede quedar con valores vacíos.
    'db_antigua' => [
        'host'     => 'localhost',
        'usuario'  => '',
        'password' => '',
        'nombre'   => '',
    ],
];
