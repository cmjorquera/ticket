<?php
    class Funciones
            {
                // public $min_espera=90;
                // public $fila_hoja= 25;
                // public $permiso_gab=false;
                // public $messt=array("-","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
                // public $diast=array("-","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado","Domingo");
                // public $filtro=array("Todosss","Iguallll","Empiezaaaa","Contieneeeee","Terminaaaaa");
                // public $filtro_num=array("Todos","=====",">",">=","<","<=","<>","Entre");
                // public $cuotas=array("1","2","3","4");

                    function __construct() {
                            setlocale(LC_CTYPE,"es_ES");
                            date_default_timezone_set("America/Santiago");
                            setlocale(LC_TIME, 'spanish');
                            return 0;
                    }//funciones
                    
                    // para dar saltos de lines y m codigo se vea ams ordenado 
                    public function tab($numTabs) {
                        echo "\n\t"; 
                    }


    



                    function menu_primario($idUsuario){ // id del usuario sesion
                        $bdato = new MySQL("", "", "", ""); // Asegúrate de reemplazar con tus credenciales reales
                        $sql1 = "SELECT m.nombre AS NombreMenu,  m.archivo AS archivoMenu, p.nombre AS NombrePermiso, u.nombre AS NombreUsuario, pm.id AS PermisoMenuID FROM permisos_menu_1 pm 
                                        INNER JOIN menu_1   m       ON pm.id_menu1          = m.id_menu
                                        INNER JOIN usuarios u       ON pm.id_usuario        = u.id 
                                        INNER JOIN permisos p       ON pm.id_tipo_permiso   = p.id 
                                        WHERE pm.id_usuario = '$idUsuario' AND p.id = 1 ";  

                        $resultado = $bdato->consulta($sql1);
                            // echo $sql1."**";                
                        while ($dato1 = $bdato->fetch_array($resultado)) {
                            // Asumiendo que 'nombre' y 'archivo' son columnas en la tabla 'menu_1' que contienen el texto del enlace y el archivo de destino respectivamente
                            echo "<li class='nav-item'>";
                            echo "<a class='nav-link' href='{$dato1['archivoMenu']}'>";
                            echo "<i class='fas fa-fw fa-table'></i>";
                            echo "<span>{$dato1['NombreMenu']}</span>";
                            echo "</a>";
                            echo "</li>";
                        }
                    }


                    function colaboradores() {
                        $bdato = new MySQL("", "", "");  // You should provide actual parameters for MySQL connection
                        $sql = "SELECT * FROM usuarios";  // It's better to specify only needed columns
                        $resultado = $bdato->consulta($sql);
                        while ($row = $bdato->fetch_array($resultado)) {
                            echo '<div class="col-xl-3 col-md-6 mb-4">';
                                echo '<div class="card border-left-warning shadow h-100 py-2">';
                                    echo '<div class="card-body">';
                                        echo '<div class="row no-gutters align-items-center">';
                                            echo '<div class="col mr-3">';
                                                echo '<div class="text-xs font-weight-bold text-warning text-uppercase mb-1">';
                                                    echo htmlentities($row['nombre'], ENT_HTML5, "ISO-8859-1") . " " . htmlentities($row['apellido_paterno'], ENT_HTML5, "ISO-8859-1");
                                                echo '</div>';
                                                echo '<div class="h5 mb-0 font-weight-bold text-gray-800">';
                                                    if (empty($row["email"])) {
                                                        echo "<span style='color: red;'>---</span>";
                                                    } else {
                                                        $nombre = htmlentities($row["nombre"], ENT_HTML5, "ISO-8859-1");
                                                        $ape_paterno = htmlentities($row["apellido_paterno"], ENT_HTML5, "ISO-8859-1");
                                                        $ape_materno = htmlentities($row["apellido_materno"], ENT_HTML5, "ISO-8859-1");
                                                        $tooltip = "Puedes mandar un correo a {$nombre} {$ape_paterno} {$ape_materno}";
                                                        echo "<a href='mailto:" . $row["email"] . "' style='color:black;' target='_blank' rel='noopener noreferrer' data-toggle='tooltip' data-placement='top' title='" . $tooltip . "' class='email-popover'>" . $row["email"] . "</a>";
                                                    }
                                                echo '</div>';
                                            echo '</div>';
                                            echo '<div class="col-auto">';
                                                echo '<i class="fas fa-comments fa-2x text-gray-300" onclick="mostrarModalConTextarea(' . $row['id'] . ');"></i>';
                                            echo '</div>';
                                        echo '</div>';
                                    echo '</div>';
                                echo '</div>';
                            echo '</div>';
                        }
                    }
                
                    function lista__crear_ticket(){
                        echo '<div class="table-responsive">';
                            echo '<table class="table table-striped" id="dataTable" width="100%" cellspacing="0">';
                                echo '<thead class="table-light">';
                                    echo '<tr>';
                                        echo '<th>ID</th>';
                                        echo '<th>FECHA</th>';
                                        echo '<th>HORA</th>';
                                        echo '<th>ASUNTO</th>';
                                        echo '<th>ESTADO</th>';
                                        echo '<th>DIA DE RESOLUCION</th>';
                                        echo '<th>CALIFICACION</th>';
                                        echo '<th>INFO</th>';
                                    echo '<tr';
                                echo '</thead>';

                                echo '<tbody>';
                                $bdato = new MySQL("", "", "");
                                $sql = "SELECT 
                                t.id AS 'idTichek', t.fecha, t.hora, t.area_trabajo AS area_trabajo_id, t.asunto, t.estado,t.dias_asignados, t.id_asignacion, 
                                a.id_area, a.nombre_area, a.encargado_area, a.sigla_area, a.correo_encargado,
                                et.nombre as 'nombreEstado'
                            FROM  tickett AS t
                            JOIN area_trabajo     AS a    ON t.area_trabajo     = a.id_area
                            JOIN usuarios         AS us   ON us.id              = t.id_usuario
                            JOIN estados_ticket   AS et   ON et.id              = t.estado
                            WHERE us.id = $idUsuarioSession";
                                $resultado = $bdato->consulta($sql);
                                $contador = 1;
                                if ($bdato->num_rows($resultado) > 0) {
                                    while ($row = $bdato->fetch_array($resultado)) {
                                        echo "<tr>";
                                        echo "<td>" . $contador++ . "</td>";
                                        echo "<td>" . $row['fecha'] . "</td>";
                                        echo "<td>" . $row['hora'] . "</td>";
                                        echo "<td>" . $row['asunto'] . "</td>";
                                        echo "<td style='color: red; text-shadow: 2px 2px 2px rgba(150, 150, 150, 0.5);'>";
                                        echo $row['nombreEstado'];
                                        echo "</td>";                                                echo "<td> ";
                                        if ($row['dias_asignados'] == 0) {
                                            echo "---";
                                        } else {
                                            echo $row['dias_asignados'];
                                        }
                                        echo "</td>";                              
                        
                                        echo "<td>";
                                        if ($row['estado'] == 5) {
                                            echo "<select name='calificacion'>";
                                            $sqlCalificaciones = "SELECT calificacion FROM calificacion_ticket ORDER BY orden";
                                            $resultCalificaciones = $bdato->consulta($sqlCalificaciones);
                                            echo "<option value=''>Seleccione un técnico...</option>"; // Agregar opción para seleccionar
                                            while ($calificacion = $bdato->fetch_array($resultCalificaciones)) {
                                                echo "<option value='" . htmlspecialchars($calificacion['id']) . "'>" . htmlentities($calificacion['calificacion'], ENT_HTML5, "ISO-8859-1")."</option>";
                                            }
                                            echo "</select>";
                                        } else {
                                            echo "No aplica"; // O cualquier otra indicación cuando no aplica
                                        }
                                        echo "</td>";
                                        echo '<td><button onclick="verTicket(\'' . $row['idTichek'] . '\')">Ver</button></td>';

                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8'>SIN TICKET</td></tr>";  // Asegúrate de ajustar el número de columnas (colspan) según el número de columnas que tiene tu tabla
                                }
                                echo '</body>';
                                echo '</table>';
                                echo '</div>';

                                echo '</div>';










                    }


                    function ticket_proceso($idUsuario) {
                        // Asumiendo que la clase MySQL gestiona la conexión a la base de datos
                        $bdato = new MySQL("", "", "", "");                    
                        $sql = "SELECT * FROM tickett WHERE id_usuario = '$idUsuario'";
                    
                        // Asumiendo que `consulta` es un método que prepara y ejecuta consultas, y `num_rows` y `fetch_array` están correctamente implementados
                        if ($stmt = $bdato->prepare($sql)) { // Asumiendo que `$bdato->prepare()` está disponible
                            $stmt->bind_param("i", $idUsuario); // "i" indica que $idUsuario es un entero
                            $stmt->execute();
                            $resultado = $stmt->get_result();
                    
                            if ($resultado->num_rows > 0) {
                                while ($row = $resultado->fetch_assoc()) {
                                    echo '<div class="col-xl-3 col-md-6 mb-4">';
                                    echo '  <div class="card border-left-warning shadow h-100 py-2">';
                                    echo '      <div class="card-body">';
                                    echo '          <div class="row no-gutters align-items-center">';
                                    echo '              <div class="col mr-2">';
                                    echo '                  <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">';
                                    echo '                      Ticket en proceso';
                                    echo '                  </div>';
                                    echo '              </div>';
                                    echo '              <div class="col-auto">';
                                    echo '                  <i class="fas fa-comments fa-2x text-gray-300"></i>';
                                    echo '              </div>';
                                    echo '          </div>';
                                    echo '      </div>';
                                    echo '  </div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="col-md-12">No hay tickets en proceso.</div>';
                            }
                            $stmt->close(); // Asegúrate de cerrar el statement
                        } else {
                            echo '<div class="col-md-12">Error al preparar la consulta.</div>';
                        }
                    }
                    

                
            }
                