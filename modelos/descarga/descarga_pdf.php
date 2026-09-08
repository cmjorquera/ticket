<?php
require_once '../../class/conexion.php'; // Incluye la clase MySQL para la conexión a la base de datos
require '../../vendor/autoload.php'; // Asegúrate de cargar las dependencias instaladas por Composer
require_once '../../class/funciones.php'; // Incluir las funciones donde está la función listarEquipos

use Mpdf\Mpdf;

// Crear una instancia de mPDF
$mpdf = new Mpdf();

// Obtener los datos de los equipos
$funciones = new Funciones();
$equipos = $funciones->listarEquipos();

// Comenzar a construir el HTML del PDF
$html = '<h1>Lista de Equipos Registrados</h1>';
$html .= '<table border="1" cellpadding="5" cellspacing="0">';
$html .= '<thead>
             <tr>
                 <th>N</th>
                 <th>Nombre del Equipo</th>
                 <th>Modelo del Procesador</th>
                 <th>Tamaño de Memoria</th>
                 <th>Tipo de Almacenamiento</th>
                 <th>Capacidad de Almacenamiento</th>
                 <th>Modelo del Monitor</th>
                 <th>Número de Serie Monitor</th>
                 <th>Usuario</th>
                 <th>Área de Trabajo</th>
             </tr>
          </thead>';
$html .= '<tbody>';

foreach ($equipos as $i => $equipo) {
    $html .= '<tr>';
    $html .= '<td>' . ($i + 1) . '</td>';
    $html .= '<td>' . htmlspecialchars($equipo['nombre_equipo']) . '</td>';
    $html .= '<td>' . htmlspecialchars($equipo['modelo_procesador']) . '</td>';
    $html .= '<td>' . htmlspecialchars($equipo['tamano_memoria']) . ' GB</td>';
    $html .= '<td>' . htmlspecialchars($equipo['tipo_almacenamiento']) . '</td>';
    $html .= '<td>' . htmlspecialchars($equipo['capacidad_almacenamiento']) . ' GB</td>';
    $html .= '<td>' . htmlspecialchars($equipo['monitor_0_modelo']) . '</td>';
    $html .= '<td>' . htmlspecialchars($equipo['monitor_0_numero_serie']) . '</td>';
    $html .= '<td>' . (isset($equipo['nombre_completo_usuario']) ? htmlspecialchars($equipo['nombre_completo_usuario']) : 'Sin usuario asociado') . '</td>';
    $html .= '<td>' . (isset($equipo['nombre_area']) ? htmlspecialchars($equipo['nombre_area']) : 'Sin área') . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table>';

// Escribir el contenido HTML en el PDF
$mpdf->WriteHTML($html);

// Salida del archivo PDF (Forzar descarga)
$mpdf->Output('reporte_equipos.pdf', 'D');
exit;
