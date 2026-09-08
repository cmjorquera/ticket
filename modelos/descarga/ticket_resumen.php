<?php
require '../../vendor/autoload.php';
require_once '../../class/conexion.php';
use Mpdf\Mpdf;

if (!isset($_GET['id_usuario'])) {
    die("Falta el parámetro id_usuario");
}
$id_usuario = intval($_GET['id_usuario']);
$contador=1;

$bd = new MySQL('', '', '');

// Obtener datos de usuario
$sql_usuario = "SELECT 
    u.id, 
    u.nombre, 
    u.apellido_paterno, 
    u.apellido_materno, 
    u.email, 
    u.telefono, 
    a.nombre_area
FROM usuarios u
LEFT JOIN area_trabajo a ON u.id_area_trabajo = a.id_area
WHERE u.id = $id_usuario";
$res_usuario = $bd->consulta($sql_usuario);
$usuario = $bd->fetch_assoc($res_usuario);

if (!$usuario) {
    die("Usuario no encontrado.");
}

// Obtener tickets del usuario
$sql_tickets = "SELECT 
                    t.id_ticket, t.asunto, t.descripcion_ticket, 
                    e.nombre AS estado, 
                    e.color,
                    DATE_FORMAT(p.fecha_creacion_inicio, '%d-%m-%Y') AS fecha_creacion,
                    c.nombre_categoria
                FROM tickets t
                LEFT JOIN estados_ticket e ON t.id_estado = e.id
                LEFT JOIN proceso_tickets p ON p.id_ticket = t.id_ticket
                LEFT JOIN categoria_de_ticket c ON t.id_categoria_ticket = c.id_categoria
                WHERE t.id_usuario = $id_usuario
                ORDER BY p.fecha_creacion_inicio DESC";
$res_tickets = $bd->consulta($sql_tickets);

$html = '
<h2 style="text-align:center;">Resumen de Tickets del Usuario</h2>
<p><strong>Nombre:</strong> ' . $usuario['nombre'] . ' ' . $usuario['apellido_paterno'] . ' ' . $usuario['apellido_materno'] . '<br>
<strong>Correo:</strong> ' . $usuario['email'] . '<br>
 <strong>Área de Trabajo:</strong> ' . htmlspecialchars($usuario['nombre_area']) . '</p>


<hr>
<table width="100%" border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr style="background-color:#f2f2f2;">
            <th>ID</th>
            <th>Asunto</th>
            <th>Categoría</th>
            <th>Fecha Creacion</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>';

if ($bd->num_rows($res_tickets) > 0) {
    while ($ticket = $bd->fetch_assoc($res_tickets)) {
        $html .= '
        <tr style="background-color:' . htmlspecialchars($ticket['color']) . ';">

            <td>' . $contador ++ . '</td>
            <td>' . htmlspecialchars($ticket['asunto']) . '</td>
            <td>' . htmlspecialchars($ticket['nombre_categoria']) . '</td>
            <td>' . $ticket['fecha_creacion'] . '</td>
            <td>' . $ticket['estado'] . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="5" style="text-align:center;">No hay tickets registrados</td></tr>';
}

$html .= '</tbody></table>
<p style="text-align:right; font-size:12px;">Generado el ' . date('d-m-Y H:i') . '</p>';

// Generar PDF
$mpdf = new Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output('resumen_usuario_' . $id_usuario . '.pdf', 'D');
exit;
