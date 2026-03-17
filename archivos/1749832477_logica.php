<?php

class Logica
{

    public function prueba()
    {
        echo "lupe";
    }



    public function obtenerMensajes($idusuario)
    {
        // Consulta para obtener los mensajes
        $consulta = "SELECT mensajes.mensaje,
                            mensajes.id,
                            mensajes.fecha,   
                            mensajes.hora,    
                            mensajes.leido,    
                            mensajes.urgente,  
                            usuarios.nombre,
                            usuarios.apellido_paterno,
                            usuarios.sexo
                     FROM mensajes 
                     JOIN usuarios ON mensajes.de = usuarios.id
                     WHERE mensajes.para = $idusuario";

        echo $ 
        // Ejecuta la consulta utilizando la clase MySQL
        $db = new MySQL("", "", ""); // Conéctate a la base de datos
        $resultado = $db->consulta($consulta); // Ejecuta la consulta

        // Contar el número de resultados ANTES de recorrerlos
        $numeroMensajes = $db->num_rows($resultado);

        // Construye el HTML
        $html = '<h6 class="dropdown-header">Mensajes</h6>';

        if ($numeroMensajes > 0) {
            // Si hay mensajes, los mostramos
            while ($fila = $db->fetch_assoc($resultado)) {
                // Definir la imagen según el sexo del usuario
                $imageSrc = ($fila['sexo'] == 2) ? 'img/undraw_profile_1.svg' : 'img/undraw_profile_2.svg';

                // Añadir el HTML del mensaje
                $html .= '
                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <div class="dropdown-list-image mr-3">
                            <img class="rounded-circle" src="' . $imageSrc . '" alt="...">
                            <div class="status-indicator bg-success"></div>
                        </div>
                        <div class="font-weight-bold">
                            <div class="text-truncate">' . $fila['mensaje'] . '</div>
                            <div class="small text-gray-500">' . $fila['fecha'] . '</div>
                        </div>
                    </a>';
            }

            // Mostrar el enlace de "Leer más mensajes" solo si hay más de 1 mensaje
            if ($numeroMensajes > 1) {
                $html .= '<a class="dropdown-item text-center small text-gray-500" href="mensaje.php">Leer más mensajes</a>';
            }
        } else {
            // Si no hay mensajes, mostramos el mensaje de "Sin mensajes"
            $html .= '<a class="dropdown-item text-center small text-gray-500" href="#">Sin mensajes</a>';
        }

        // Devolver el HTML completo
        echo $html;
    }


    public function obtenerCantidadMensajes($idusuario)
    {
        // Consulta para contar los mensajes
        // var_dump("obtenerCantidadMensajes");       
        // die();
        $consulta = "SELECT COUNT(*) AS total_mensajes
                     FROM mensajes 
                     WHERE mensajes.para = $idusuario";
        echo $consulta."****";
        $db = new MySQL("", "", "");
        $resultado = $db->consulta($consulta);

        $fila = $db->fetch_assoc($resultado); // Obtenemos el resultado de la consulta
        return $fila['total_mensajes'];
        // return 99;
    }

    public function obtenerCantidadAlertas($idusuario)
    {
        // Consulta para contar los mensajes
        // var_dump("obtenerCantidadMensajes");       
        // die();
        $consulta = "SELECT 
            (SELECT COUNT(*) FROM recordatorio WHERE id_usuario = $idusuario) +
            (SELECT COUNT(*) FROM tickets WHERE id_usuario = $idusuario) 
        AS total_mensajes";


        // Conexión a la base de datos
        $db = new MySQL("ticket", "root", "seduc2024");
        $resultado = $db->consulta($consulta);

        // Procesar el resultado
        $fila = $db->fetch_assoc($resultado); // Obtenemos el resultado de la consulta

        // Devolver la cantidad de mensajes
        return $fila['total_mensajes'];
        // return 99;
    }


    public function obtenerRecordatorios($idusuario) {
        // Consulta para obtener los tickets y los recordatorios relacionados
        $consulta = "
            SELECT 
                tickets.id_ticket AS id, 
                tickets.asunto, 
                usuarios.nombre AS usuario_nombre, 
                usuarios.apellido_paterno, 
                usuarios.apellido_materno, 
                estados_ticket.nombre AS estado_nombre, 
                estados_ticket.color AS estado_color,
                'ticket' AS tipo,
                NULL AS recordatorio_titulo,
                NULL AS recordatorio_detalle
            FROM tickets
            JOIN usuarios ON tickets.id_usuario = usuarios.id
            JOIN estados_ticket ON tickets.id_estado = estados_ticket.id
            WHERE tickets.id_usuario = $idusuario
            UNION
            SELECT 
                recordatorio.id AS id,
                recordatorio.titulo AS asunto,
                '' AS usuario_nombre, 
                '' AS apellido_paterno, 
                '' AS apellido_materno, 
                '' AS estado_nombre, 
                '' AS estado_color,
                'recordatorio' AS tipo,
                recordatorio.titulo AS recordatorio_titulo,
                recordatorio.detalle AS recordatorio_detalle
            FROM recordatorio
            WHERE recordatorio.id_usuario = $idusuario";
    
            //AND recordatorio.completada = 'SI'
            
        // Ejecuta la consulta
        $db = new MySQL("ticket", "root", "seduc2024");
        $resultado = $db->consulta($consulta);
    
        // Inicializar el HTML como cadena vacía
        $html = '';
    
        // Verificamos si hay resultados
        if ($db->num_rows($resultado) > 0) {
            // Agregamos la cabecera para Recordatorios
            $html .= '<h6 class="dropdown-header">RECORDATORIOS';
            $html .= '<button class="btn btn-primary btn-sm float-right" type="button" onclick="mostrarAlertas(8)">+</button>';
            $html .= ' </h6>';
           
            // Mostrar los resultados
            while ($fila = $db->fetch_assoc($resultado)) {
                // Definir imagen por defecto y colores basados en el tipo
                $imageSrc = 'img/undraw_profile_1.svg';  // Imagen por defecto para tickets
                $estadoColor = isset($fila['estado_color']) ? 'background-color:' . $fila['estado_color'] . ';' : '';
    
                // Mostrar ticket o recordatorio
                $html .= '<a class="dropdown-item d-flex align-items-center" href="';
                
                if ($fila['tipo'] === 'ticket') {
                    $html .= 'ticket_asignados.php?id=' . $fila['id'];
                } else {
                    $html .= '#';
                }
    
                $html .= '" id="contendorRecordatorio">';
    
                // Imagen y estado del ticket o recordatorio
                $html .= '<div class="dropdown-list-image mr-3">';
                $html .= '<img class="rounded-circle" src="' . $imageSrc . '" alt="...">';
                
                if ($fila['tipo'] === 'ticket') {
                    $html .= '<div class="status-indicator" style="' . $estadoColor . '"></div>';
                } else {
                    $html .= '<div class="status-indicator bg-info"></div>';
                }
    
                $html .= '</div><div>';
    
                // Si es un ticket, mostrar detalles del ticket
                if ($fila['tipo'] === 'ticket') {
                    $html .= '<div class="text-truncate">' . $fila['asunto'] . ' <span style="color:' . $fila['estado_color'] . ';"><strong>(' . $fila['estado_nombre'] . ')</strong></span></div>';
                    $html .= '<div class="small text-gray-500">' . $fila['usuario_nombre'] . ' ' . $fila['apellido_paterno'] . ' ' . $fila['apellido_materno'] . '</div>';
                } else {
                    // Si es un recordatorio, mostrar detalles del recordatorio
                    $html .= '<div class="text-truncate">Recordatorio: ' . $fila['recordatorio_titulo'] . '</div>';
                    $html .= '<div class="small text-gray-500">' . $fila['recordatorio_detalle'] . '</div>';
                }
    
                $html .= '</div></a>';
            }
        } else {
            // Si no hay recordatorios o tickets, mostrar "Sin recordatorios"
            $html .= '<a class="dropdown-item text-center small text-gray-500" href="#">Sin recordatorios</a>';
        }
    
        // Devolver el HTML completo
        echo $html;
    }
    
}
