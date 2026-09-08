<?php
include("../../class/conexion.php");

$userId = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$incluirPermisos = $userId !== '';
$bdato = new MySQL("", "", "");
$data = [];

// Obtener los menús
$menuQuery = "SELECT id_menu, nombre FROM menu_1 ORDER BY orden ASC";
$menuConsulta = $bdato->consulta($menuQuery);

while ($menuRow = mysqli_fetch_assoc($menuConsulta)) {
    $menuId = htmlspecialchars($menuRow['id_menu']);
    $menuName = htmlspecialchars($menuRow['nombre']);

    // Verificar si el menú tiene submenús
    $submenuQuery = "SELECT id_submenu, nombre FROM menu_1_sub WHERE id_menu = '$menuId'";
    $submenuConsulta = $bdato->consulta($submenuQuery);

    $submenus = [];
    while ($submenuRow = mysqli_fetch_assoc($submenuConsulta)) {
        $submenuId = htmlspecialchars($submenuRow['id_submenu']);
        $submenuName = htmlspecialchars($submenuRow['nombre']);
        $submenus[] = [
            'id_submenu' => $submenuId,
            'nombre' => $submenuName
        ];
    }

    $idTipoPermiso = null;
    if ($incluirPermisos) {
        // Obtener el tipo de permiso para el menú
        $permisoQuery = "SELECT id_tipo_permiso FROM permisos_menu_1 WHERE id_usuario = '$userId' AND id_menu1 = '$menuId'";
        $permisoConsulta = $bdato->consulta($permisoQuery);
        $permisoRow = mysqli_fetch_assoc($permisoConsulta);
        $idTipoPermiso = $permisoRow ? $permisoRow['id_tipo_permiso'] : null;
    }

    $data[] = [
        'id_menu'           => $menuId,
        'nombre'            => $menuName,
        'submenus'          => $submenus,
        'id_tipo_permiso'   => $idTipoPermiso
    ];
}

echo json_encode($data);
?>
