<?php
class Funciones
{
    function __construct()
    {
        setlocale(LC_CTYPE, "es_ES");
        date_default_timezone_set("America/Santiago");
        setlocale(LC_TIME, 'spanish');
        return 0;
    }

    private function resolverRutaSistema($ruta)
    {
        $ruta = trim(str_replace('\\', '/', (string)$ruta));
        if ($ruta === '') {
            return '#';
        }

        if (
            preg_match('/^(?:[a-z]+:)?\/\//i', $ruta) ||
            strpos($ruta, '#') === 0 ||
            strpos($ruta, 'javascript:') === 0 ||
            strpos($ruta, 'mailto:') === 0 ||
            strpos($ruta, 'tel:') === 0 ||
            strpos($ruta, '/') === 0
        ) {
            return $ruta;
        }

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $directorioActual = trim(dirname($scriptName), '/.');
        if ($directorioActual === '') {
            return $ruta;
        }

        $niveles = count(array_filter(explode('/', $directorioActual)));
        return str_repeat('../', $niveles) . ltrim($ruta, '/');
    }

    private function renderizarIconoMenu($icono, $iconoPorDefecto = 'bi bi-dot')
    {
        $icono = trim((string) $icono);
        if ($icono === '') {
            return '<i class="' . htmlspecialchars($iconoPorDefecto, ENT_QUOTES, 'UTF-8') . '"></i>';
        }

        if (strpos($icono, '<') !== false && strpos($icono, '>') !== false) {
            return $icono;
        }

        return '<i class="' . htmlspecialchars($icono, ENT_QUOTES, 'UTF-8') . '"></i>';
    }

    function lupe()
    {
        echo "guadalupe jorquera";
    }




    function colaboradores($idUsuarioSession)
    {

        $bdato = new MySQL("", "", "");
        $idsEspecificos = [28, 29, 30, 31, 32, 33, 34];  // ID DE LOS COLEGIOS
        if (in_array($idUsuarioSession, $idsEspecificos)) {
            // Mostrar solo los usuarios con los mismos IDs que $idUsuarioSession
            $idsString = implode(',', $idsEspecificos);
            $sql = "SELECT * FROM usuarios WHERE id IN ($idsString) AND id <> $idUsuarioSession ORDER BY nombre ASC";
        } else {
            // Mostrar todos los usuarios excepto el logueado
            $sql = "SELECT * FROM usuarios WHERE id <> $idUsuarioSession ORDER BY nombre ASC";
        }

        $resultado = $bdato->consulta($sql);
        while ($row = $bdato->fetch_array($resultado)) {
            // Escapar y formatear datos
            $nombre      = htmlentities($row["nombre"], ENT_HTML5, "ISO-8859-1");
            $ape_paterno = htmlentities($row["apellido_paterno"], ENT_HTML5, "ISO-8859-1");
            $ape_materno = htmlentities($row["apellido_materno"], ENT_HTML5, "ISO-8859-1");
            $cargo       = !empty($row['cargo']) ? htmlentities($row['cargo'], ENT_HTML5, "ISO-8859-1") : "<span style='color: red;'>--------</span>";
            $anexo       = $row['anexo'];
            $email       = !empty($row["email"]) ? htmlentities($row["email"], ENT_HTML5, "ISO-8859-1") : "<span style='color: red;'>--------</span>";
        
            // Generar un ID único para la tarjeta basado en el ID del colaborador
            $cardId = "card-" . $row['id'];
        
            // HTML de la tarjeta del colaborador
            echo '<div class="col-xl-3 col-md-6 mb-4 position-relative">';  
            echo '  <div class="card border-left-primary shadow h-100 py-2" id="' . $cardId . '">';  
            echo '      <div class="card-body">';
            echo '          <div class="row no-gutters align-items-center">';

                // Columna izquierda: Imagen del avatar e icono
            echo '<div class="col-4 d-flex flex-column align-items-center position-relative">';

            // Asignar la imagen según el ID del usuario
            switch ($row['id']) {
                case 28:
                    $imagenPerfil = 'img/logo_tabancura.jpeg';
                    break;
                case 29:
                    $imagenPerfil = 'img/logo_los_andes.png';
                    break;
                case 30:
                    $imagenPerfil = 'img/logo_huinganal.png';
                    break;
                case 31:
                    $imagenPerfil = 'img/logo_cordillera.jpeg';
                    break;
                case 32:
                    $imagenPerfil = 'img/logo_huelen.jpeg';
                    break;
                case 33:
                    $imagenPerfil = 'img/logo_huinganal.png';
                    break;
                default:
                    // Si no cumple ninguna condición, usar lógica del sexo
                    $imagenPerfil = ($row['sexo'] == 2) ? 'img/undraw_profile_1.svg' : 'img/undraw_profile.svg';
                    break;
            }

                // Mostrar la imagen seleccionada
                echo '<img src="' . $imagenPerfil . '" class="img-fluid rounded-circle mb-3" alt="Avatar" style="height: 100px; width: 100px;">';


            // Ícono de mensaje
          echo '<i class="fas fa-comments fa-lg text-primary mx-2 mb-3" 
                onclick="crearMensajeColaboradores(' . $idUsuarioSession . ', ' . $row['id'] . ');" 
                style="font-size: 1.9em;" 
                data-toggle="tooltip" 
                data-placement="top" 
                title="Enviar mensaje">
              </i>';


            // Checkbox en la esquina superior derecha
            echo '<input type="checkbox" class="form-check-input colaborador-checkbox position-absolute" style="top: 0; right: 0;" value="' . $row['id'] . '" data-card-id="' . $cardId . '">';  // Agregamos el data-card-id para usar en el JS
            echo '</div>';

            // Columna derecha: Información adicional
            echo '<div class="col-8">';
            echo '<div class="row">';

            // Nombre completo centrado en la parte superior de la columna derecha
            echo '<div class="col-12 text-center">';
            echo '<div class="text-sm font-weight-bold mb-1" id="id_NombreColaborador">';
            echo $nombre . " " . $ape_paterno . " " . $ape_materno;
            echo '</div>';
            echo '</div>';

            // Información adicional más pequeña y centrada en la parte inferior de la columna derecha
            echo '<div class="col-12 text-center mt-2">';
            echo '<div class="text-sm font-weight-bold text mb-1" id="id_Colaborador_cargo" style="font-size: 0.8em;">';
            if (!empty($row['cargo'])) {
                echo htmlentities($row['cargo'], ENT_HTML5, "ISO-8859-1");
            } else {
                echo "<span style='color: red;'>--------</span>";
            }
            echo '</div>';
            echo '<div class="text-sm font-weight-bold text-primary text-uppercase mb-1" id="id_AnexoColaborador" style="font-size: 0.8em;">';
            echo $row['anexo'];
            echo '</div>';
            echo '<div class="h7 mb-0 font-weight-bold text-gray-800" id="id_colaborador_correo" style="font-size: 0.8em;">';
            if (empty($row["email"])) {
                echo "<span style='color: red;'>--------</span>";
            } else {
                $tooltip = "Puedes mandar un correo a {$nombre} {$ape_paterno} {$ape_materno}";
                echo "<a href='mailto:" . $row["email"] . "' style='color:black;' target='_blank' rel='noopener noreferrer' data-toggle='tooltip' data-placement='top' title='" . $tooltip . "' class='email-popover'>" . $row["email"] . "</a>";
            }
            echo '</div>';
            echo '</div>';

            echo '</div>'; // Fin de la fila interna de la columna derecha
            echo '</div>'; // Fin de la columna derecha
            echo '</div>'; // Fin de la fila principal
            echo '</div>'; // Fin del cuerpo de la tarjeta
            echo '</div>'; // Fin de la tarjeta
            echo '</div>'; // Fin de la columna
        }
    }
    
    

    public function listaUsuarios()
    {
        $bdato = new MySQL('', '', '');
        $consulta = "
            SELECT 
                u.id,
                u.nombre,
                u.apellido_paterno,
                u.apellido_materno,
                CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo,
                u.email,
                u.telefono,
                u.clave,
                u.token_reinicio,
                u.estado,
                at.nombre_area,
                GROUP_CONCAT(DISTINCT uc.id_colegio ORDER BY uc.id_colegio ASC SEPARATOR ',') AS colegios_ids,
                GROUP_CONCAT(DISTINCT c.nom_colegio ORDER BY c.nom_colegio ASC SEPARATOR '||') AS colegios_nombres
            FROM usuarios u 
            LEFT JOIN area_trabajo at ON at.id_area = u.id_area_trabajo
            LEFT JOIN usuario_colegio uc ON uc.id_usuario = u.id AND uc.estado = 1
            LEFT JOIN colegio c ON c.id_colegio = uc.id_colegio
            GROUP BY u.id, u.nombre, u.apellido_paterno, u.apellido_materno, u.email, u.telefono, u.clave, u.estado, at.nombre_area
            ORDER BY u.nombre ASC
        ";
        $resultado = $bdato->consulta($consulta);
        
        if ($bdato->num_rows($resultado) > 0) {
            $html = '<div class="usuarios-table-wrap">
                       <table id="dataTableUsuarios" class="table table-bordered table-hover table-striped w-100">

                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Colegio</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Área</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>';
                            
            $indiceVisible = 1;

            while ($row = mysqli_fetch_array($resultado)) {
                $userId     = htmlspecialchars($row['id']);
                $fullName   = htmlspecialchars($row['nombre_completo']);
                $email      = htmlspecialchars($row['email']);
                $area       = htmlspecialchars($row['nombre_area'] ?? '', ENT_QUOTES, 'UTF-8');
                $estado     = htmlspecialchars($row['estado']);
                $tieneClave = !empty($row['clave']);
                $puedeReenviarActivacion = ($estado !== "Activo" && !$tieneClave);
                $estadoEsActivo = ($estado == "Activo");
                $stateLabel = $estadoEsActivo ? 'Activo' : 'Inactivo';
                $stateDetail = $estadoEsActivo ? 'Cuenta habilitada' : 'Revision requerida';
                $stateColor = $estadoEsActivo ? '#b8f4bb' : '#d9e2ec';
                $detalleEstado = '';
                if ($estado !== "Activo") {
                    $detalleEstado = $tieneClave
                        ? 'Usuario bloqueado.'
                        : 'Esperando confirmacion de activacion de cuenta.';
                }
                $areaTitulo = $area !== '' ? $area : 'Sin area';
                $areaDetalle = 'Area de trabajo';
                $colegiosIds = array_filter(array_map('trim', explode(',', (string)($row['colegios_ids'] ?? ''))));
                $colegiosNombres = array_filter(array_map('trim', explode('||', (string)($row['colegios_nombres'] ?? ''))));

                $colegiosHtml = '<span class="usuario-colegio-vacio">Sin colegio</span>';
                if (!empty($colegiosIds)) {
                    $items = [];
                    foreach ($colegiosIds as $indexColegio => $idColegio) {
                        $idColegioSeguro = (int)$idColegio;
                        $nombreColegio = htmlspecialchars($colegiosNombres[$indexColegio] ?? ('Colegio ' . $idColegioSeguro), ENT_QUOTES, 'UTF-8');
                        $rutaLogo = '../img/colegios/colegio_' . $idColegioSeguro . '.png';

                        $items[] = '<div class="usuario-colegio-item" title="' . $nombreColegio . '">
                            <img src="' . $rutaLogo . '" alt="' . $nombreColegio . '" class="usuario-colegio-logo" loading="lazy"
                                onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'inline-flex\';">
                            <span class="usuario-colegio-fallback" style="display:none;">' . $idColegioSeguro . '</span>
                            <span class="usuario-colegio-nombre">' . $nombreColegio . '</span>
                        </div>';
                    }

                    $colegiosHtml = '<div class="usuario-colegio-lista">' . implode('', $items) . '</div>';
                }
                
                $emailHtml = '<div class="usuario-email-wrap">
                        <span class="usuario-email-text">' . $email . '</span>' .
                        ($puedeReenviarActivacion ? '<button type="button" class="usuario-email-action" onclick="confirmarReenvioActivacion(' . $userId . ', \'' . addslashes($email) . '\')" title="Reenviar correo de activacion">
                            <i class="bi bi-envelope-arrow-up"></i>
                        </button>' : '') . '
                    </div>';

                    $html .= '<tr>
                    <td>' . $indiceVisible . '</td>
                    <td>' . $colegiosHtml . '</td>
                    <td>' . $fullName . '</td>
                    <td>' . $emailHtml . '</td>
                    <td class="celda-estado-config">
                        <div class="ticket-resumen-estado ticket-resumen-estado--config">
                            <span class="ticket-resumen-estado__dot" style="background:' . htmlspecialchars('#b8f4bb', ENT_QUOTES, 'UTF-8') . ';"></span>
                            <div class="ticket-resumen-estado__body">
                                <div class="ticket-resumen-estado__titulo">' . $areaTitulo . '</div>
                                <div class="ticket-resumen-estado__detalle">' . $areaDetalle . '</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="usuario-estado-wrap">
                            <div class="ticket-resumen-estado ticket-resumen-estado--config ticket-resumen-estado--config-estado">
                                <span class="ticket-resumen-estado__dot" style="background:' . htmlspecialchars($stateColor, ENT_QUOTES, 'UTF-8') . ';"></span>
                                <div class="ticket-resumen-estado__body">
                                    <div class="ticket-resumen-estado__titulo">' . $stateLabel . '</div>
                                    <div class="ticket-resumen-estado__detalle">' . $stateDetail . '</div>
                                </div>
                            </div>' .
                            ($detalleEstado !== '' ? '<button type="button" class="usuario-estado-info" onclick="mostrarDetalleEstadoUsuario(\'' . htmlspecialchars($detalleEstado, ENT_QUOTES, 'UTF-8') . '\')" title="Ver detalle del estado">
                                <i class="bi bi-question-lg"></i>
                            </button>' : '') . '
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="btn-group usuario-acciones-group" role="group">
                            <!-- Modificar -->
                            <a href="#" class="btn btn-primary btn-sm me-1 usuario-accion-btn" onclick="modificarUsuario(' . $userId . ')" 
                                data-bs-toggle="popover" data-bs-placement="top" 
                                data-bs-content=\"Modificar usuario\" aria-label=\"Modificar usuario\">
                                <i class="bi bi-pencil"></i>
                            </a>
        
                            <!-- Permisos -->
                            <a href="#" class="btn btn-success btn-sm me-1 usuario-accion-btn" onclick="mostrarPermisos(' . $userId . ')" 
                                data-bs-toggle="popover" data-bs-placement="top" 
                                data-bs-content=\"Ver permisos del usuario\" aria-label=\"Permisos\">
                                <i class="bi bi-shield-lock"></i>
                            </a>
        
                            <!-- Estado -->
                            <a href="#" class="btn btn-secondary btn-sm me-1 usuario-accion-btn" onclick="estadoUsuario(' . $userId . ', \'' . $estado . '\')" 
                                data-bs-toggle="popover" data-bs-placement="top" 
                                data-bs-content=\"Activar/Desactivar usuario\" aria-label=\"Estado\">
                                <i class="bi bi-toggle-off"></i>
                            </a>
        
                            <!-- Cambiar clave
                            <a href="#" class="btn btn-warning btn-sm me-1" onclick="cambiarClave(' . $userId . ')" 
                                data-bs-toggle="popover" data-bs-placement="top" 
                                data-bs-content=\"Cambiar clave\" aria-label=\"Cambiar clave\">
                                <i class="bi bi-key"></i>
                            </a> -->
                        </div>
                    </td>
                 </tr>';

                $indiceVisible++;
            }
            $html .= '</tbody></table></div>';
            return $html;
        } else {
            return '<div class="alert alert-warning">No existen usuarios en la base de datos.</div>';
        }
    }







public function listaTecnicos()
{
    $bdato = new MySQL("", "", "");

    // Traer categorías con íconos
    $query_categorias = "SELECT id_categoria, nombre_categoria, icono
                         FROM categoria_de_ticket
                         WHERE id_categoria != 10
                           AND estado = 1";
    $consulta_categorias = $bdato->consulta($query_categorias);

    $categorias = [];
    while ($row = mysqli_fetch_array($consulta_categorias)) {
        $id_categoria = $row['id_categoria'];
        $categorias[$id_categoria] = [
            'nombre' => htmlspecialchars($row['nombre_categoria'] ?? '', ENT_QUOTES, 'UTF-8'),
            'icono'  => htmlspecialchars($row['icono'] ?? '', ENT_QUOTES, 'UTF-8'),
        ];
    }

    // Consulta para obtener técnicos con categorías asignadas
    $query = "SELECT usu.*, 
                     atr.nombre_area, 
                     GROUP_CONCAT(cat.id_categoria) AS categoria_ids,
                     GROUP_CONCAT(cu.id_categoria_tecnico) AS categoria_tecnico_ids
              FROM usuarios usu
              JOIN area_trabajo atr ON atr.id_area = usu.id_area_trabajo
              LEFT JOIN categoria_tecnico cu ON cu.id_tecnico = usu.id
              LEFT JOIN categoria_de_ticket cat ON cat.id_categoria = cu.id_categoria
              WHERE usu.id_area_trabajo = '1'
                AND usu.id != 27
              GROUP BY usu.id
              ORDER BY usu.id ASC";
    $consulta = $bdato->consulta($query);

    if ($bdato->num_rows($consulta) > 0) {
        $html = '<div class="table-responsive">
                    <table id="dataTableTecnicos" class="table table-bordered table-hover table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Avatar</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Categorías</th>
                            </tr>
                        </thead>
                        <tbody>';
        $count = 1;

        while ($row = mysqli_fetch_array($consulta)) {
            $userId   = htmlspecialchars($row['id'] ?? '', ENT_QUOTES, 'UTF-8');
            $nombre   = htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
            $apellido = htmlspecialchars($row['apellido_paterno'] ?? '', ENT_QUOTES, 'UTF-8');

            $categoria_ids        = isset($row['categoria_ids']) ? explode(',', $row['categoria_ids']) : [];
            $categoria_tecnico_ids = isset($row['categoria_tecnico_ids']) ? explode(',', $row['categoria_tecnico_ids']) : [];

            // Mostrar las categorías en formato badges con íconos
            $categoriesHtml = '<div class="submenu-container">';
            foreach ($categorias as $id_categoria => $catData) {
                $index              = array_search($id_categoria, $categoria_ids);
                $colorClass         = $index !== false ? 'green' : 'red';
                $idCategoriaTecnico = $index !== false ? $categoria_tecnico_ids[$index] : '';
                $icono              = '<i class="bi ' . $catData['icono'] . ' me-1"></i>';
                $nombreCategoria    = $catData['nombre'];

                $categoriesHtml .= '<div class="submenu-item ' . $colorClass . '" data-id_categoria="' . $id_categoria . '" data-id_categoria_tecnico="' . $idCategoriaTecnico . '" onclick="toggleSubmenuColor(this)">' . $icono . $nombreCategoria . '</div>';
            }
            $categoriesHtml .= '</div>';

            $html .= '<tr data-id_usuario="' . $userId . '">
                        <td>' . $count++ . '</td>
                        <td class="text-center"><img src="img/undraw_profile.svg" alt="avatar" width="40" class="rounded-circle shadow"></td>
                        <td>' . $nombre . '</td>
                        <td>' . $apellido . '</td>
                        <td>' . $categoriesHtml . '</td>
                      </tr>';
        }

        $html .= '</tbody></table></div>';

        // CSS para visuales
        $html .= '<style>
                    .submenu-container {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 5px;
                    }
                    .submenu-item {
                        display: flex;
                        align-items: center;
                        gap: 5px;
                        margin: 6px;
                        padding: 6px 12px;
                        border-radius: 8px;
                        cursor: pointer;
                        font-weight: 500;
                        transition: transform 0.2s;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    .submenu-item.green {
                        background-color: #90ee90;
                        color: #000;
                    }
                    .submenu-item.red {
                        background-color: #F9856C;
                        color: white;
                    }
                    .submenu-item:hover {
                        transform: scale(1.03);
                    }
                  </style>';

        return $html;
    } else {
        return '<div class="alert alert-warning">No hay técnicos registrados</div>';
    }
}



    function header()
    {
        // Iniciar sesión si aún no está iniciada


        echo '<meta charset="utf-8">' .
            '<meta http-equiv="X-UA-Compatible" content="IE=edge">' .
            '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">' .
            '<meta name="description" content="Enter your site description here">' .
            '<meta name="author" content="Your Name or Company Name">' .
            '<title>SEDUC SPA</title>' .
            '<!-- Custom fonts for this template -->' .
            '<link rel="icon" type="image/x-icon" href="imagenes/logo_seduc.png" />' .
            '<link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">' .
            '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">' .
            '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">' .
            '<link rel="stylesheet" type="text/css" href="css/sb-admin-2.css">' .
            '<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">' .
            '<!-- Custom styles for this template -->' .
            '<link href="css/sb-admin-2.min.css" rel="stylesheet">' .
 
            '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>' .
            '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>' .
            //  '<script type="text/javascript" src="js/actualizaciones.js"></script>' .
            '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">' .
            '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>';
    }


    function script()
    {
        echo 
            '<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>' .
            '<!-- Core plugin JavaScript-->' .
            '<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>' .
            '<!-- Custom scripts for all pages-->' .
            '<script src="js/sb-admin-2.min.js"></script>' .
            '<!-- Page level plugins -->' .
            '<script src="vendor/chart.js/Chart.min.js"></script>' .
            '<!-- Page level custom scripts -->' .
            '<script src="js/demo/chart-area-demo.js"></script>' .
            '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>' .
             '<script src="js/funciones.js"></script>' .
            '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">' .
            '<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>' .
            '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
            </script>';
         '<script src="js/demo/chart-pie-demo.js"></script>';
         

    }

public function menuLateral($idUsuarioSession, $idPagActual)
{
    $bdato = new MySQL("", "", "");

    $sql1 = "SELECT m.id_menu, m.nombre AS NombreMenu, m.abreviacion AS abreviacion, 
                    m.icono AS iconoMenu, m.archivo AS archivoMenu, m.caracteristica AS descripcionMenu,
                    p.nombre AS NombrePermiso, u.nombre AS NombreUsuario, pm.id AS PermisoMenuID  
            FROM permisos_menu_1 pm 
            INNER JOIN menu_1 m ON pm.id_menu1 = m.id_menu 
            INNER JOIN usuarios u ON pm.id_usuario = u.id 
            INNER JOIN permisos p ON pm.id_tipo_permiso = p.id 
            WHERE pm.id_usuario = '$idUsuarioSession' AND (p.id IN (1, 2) )
            ORDER BY m.orden asc";

    $resultado = $bdato->consulta($sql1);
    $menuItems = '';
    $hasAddedTicketsHeading = false;

    while ($dato1 = $bdato->fetch_array($resultado)) {
        $id_menu = $dato1['id_menu'];
        $isActive = ($id_menu == $idPagActual) ? 'active-menu-item' : '';
        $descripcionMenu = htmlspecialchars($dato1['descripcionMenu'], ENT_QUOTES, 'UTF-8');
        $idElemento = 'id_' . htmlspecialchars($dato1['abreviacion']);
        $archivoMenu = htmlspecialchars($this->resolverRutaSistema($dato1['archivoMenu']), ENT_QUOTES, 'UTF-8');

        $subMenuItems = '';
        $sql2 = "SELECT nombre, archivo FROM menu_1_sub WHERE id_menu = '$id_menu' ORDER BY orden";
        $resultadoSub = $bdato->consulta($sql2);
        while ($datoSub = $bdato->fetch_array($resultadoSub)) {
            $archivoSubmenu = htmlspecialchars($this->resolverRutaSistema($datoSub['archivo']), ENT_QUOTES, 'UTF-8');
            $nombreSubmenu = htmlspecialchars($datoSub['nombre'], ENT_QUOTES, 'UTF-8');
            $subMenuItems .= "<a class='collapse-item' href='{$archivoSubmenu}'>{$nombreSubmenu}</a>";
        }

        if ($id_menu == 8) {
            $menuItems .= '<hr class="sidebar-divider">';
        }

        if ($subMenuItems) {
            $menuItems .= "<li class='nav-item $isActive'>
                                <a class='nav-link collapsed' href='#' data-toggle='collapse' 
                                    data-target='#collapseMenu$id_menu' aria-expanded='true' aria-controls='collapseMenu$id_menu' id='$idElemento'>
                                    {$dato1['iconoMenu']}
                                    <span style='color: black'>{$dato1['NombreMenu']}</span>
                                </a>
                                <div id='collapseMenu$id_menu' class='collapse' aria-labelledby='headingMenu$id_menu' data-parent='#accordionSidebar'>
                                    <div class='bg-white py-2 collapse-inner rounded'>
                                        $subMenuItems
                                    </div>
                                </div>
                            </li>";
        } else {
            $menuItems .= "<li class='nav-item $isActive'>
                                <a class='nav-link' href='{$archivoMenu}' id='$idElemento'>
                                    {$dato1['iconoMenu']}
                                    <span style='color: black'>{$dato1['NombreMenu']}</span>
                                </a>
                            </li>";
        }

        if ($id_menu == 8) {
            $menuItems .= '<hr class="sidebar-divider">';
            if (!$hasAddedTicketsHeading) {
                $menuItems .= '<div class="sidebar-heading" style="color:black">Tickets</div>';
                $hasAddedTicketsHeading = true;
            }
        }
    }

    $urlPrincipal = htmlspecialchars($this->resolverRutaSistema('principal.php'), ENT_QUOTES, 'UTF-8');

    echo '<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar" style="background-color:#85C1E9;">
                <a class="sidebar-brand d-flex align-items-center justify-content-center" href="' . $urlPrincipal . '">
                    <div class="sidebar-brand-icon"><img style="width:40px;" src="' . htmlspecialchars($this->resolverRutaSistema('imagenes/logo_seduc.png'), ENT_QUOTES, 'UTF-8') . '" alt="..."></div>
                    <div class="sidebar-brand-text mx-3">SEDUC</div>
                </a>
                ' . $menuItems . '
                <hr class="sidebar-divider d-none d-md-block">
                <div class="text-center d-none d-md-inline">
                    <button class="rounded-circle border-0" id="sidebarToggle"></button>
                </div>
          </ul>';
}

public function menuLateral2Antiguo($idUsuarioSession, $idPagActual, $archivoActual = null, $tituloSistema = 'SEDUC')
{
    $bdato = new MySQL("", "", "");
    $idUsuarioSession = intval($idUsuarioSession);
    $archivoActual = $archivoActual ?: basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
    $urlPrincipal = htmlspecialchars($this->resolverRutaSistema('principal.php'), ENT_QUOTES, 'UTF-8');

    $sqlMenus = "SELECT DISTINCT
                    m.id_menu,
                    m.nombre AS NombreMenu,
                    m.abreviacion,
                    m.icono AS iconoMenu,
                    m.archivo AS archivoMenu,
                    m.caracteristica AS descripcionMenu,
                    m.orden
                FROM permisos_menu_1 pm
                INNER JOIN menu_1 m ON pm.id_menu1 = m.id_menu
                WHERE pm.id_usuario = $idUsuarioSession
                  AND pm.id_tipo_permiso IN (1, 2)
                ORDER BY m.orden ASC";

    $resultadoMenus = $bdato->consulta($sqlMenus);
    $menuItems = '';
    $agregoHeadingTickets = false;

    while ($menu = $bdato->fetch_array($resultadoMenus)) {
        $idMenu = (int) $menu['id_menu'];
        $nombreMenu = htmlspecialchars($menu['NombreMenu'], ENT_QUOTES, 'UTF-8');
        $archivoMenu = trim((string) $menu['archivoMenu']);
        $archivoMenuSeguro = htmlspecialchars($this->resolverRutaSistema($archivoMenu), ENT_QUOTES, 'UTF-8');
        $iconoMenu = $menu['iconoMenu'];
        $descripcionMenu = htmlspecialchars((string) $menu['descripcionMenu'], ENT_QUOTES, 'UTF-8');
        $isActive = ($idMenu === (int) $idPagActual);
        $submenuHtml = '';
        $submenuActivo = false;

        $sqlSubmenus = "SELECT id_submenu, nombre, archivo, icono
                        FROM menu_1_sub
                        WHERE id_menu = $idMenu
                        ORDER BY orden ASC";
        $resultadoSubmenus = $bdato->consulta($sqlSubmenus);

        while ($submenu = $bdato->fetch_array($resultadoSubmenus)) {
            $nombreSubmenu = htmlspecialchars($submenu['nombre'], ENT_QUOTES, 'UTF-8');
            $archivoSubmenu = trim((string) $submenu['archivo']);
            $archivoSubmenuSeguro = htmlspecialchars($this->resolverRutaSistema($archivoSubmenu), ENT_QUOTES, 'UTF-8');
            $iconoSubmenu = trim((string) ($submenu['icono'] ?? ''));
            $submenuEstaActivo = ($archivoSubmenu !== '' && $archivoActual === $archivoSubmenu);
            $submenuActivo = $submenuActivo || $submenuEstaActivo;
            $submenuClase = $submenuEstaActivo ? 'is-active' : '';
            $iconoSubmenuHtml = $this->renderizarIconoMenu($iconoSubmenu, 'bi bi-dot');

            $submenuHtml .= "
                <a class='menuLateral2-subitem $submenuClase' href='{$archivoSubmenuSeguro}'>
                    <span class='menuLateral2-subicon'>{$iconoSubmenuHtml}</span>
                    <span>{$nombreSubmenu}</span>
                </a>";
        }

        $bloqueActivo = $isActive || $submenuActivo;
        $itemClase = $bloqueActivo ? 'is-active' : '';

        if ($idMenu === 8) {
            $menuItems .= '<div class="menuLateral2-divider"></div>';
            if (!$agregoHeadingTickets) {
                $menuItems .= '<div class="menuLateral2-heading">Tickets</div>';
                $agregoHeadingTickets = true;
            }
        }

        $claseInicio = ($idMenu === 8) ? 'menuLateral2-item-home' : '';

        if ($submenuHtml !== '') {
            $mostrarSubmenu = $bloqueActivo ? ' style="display:block;"' : '';
            $ariaExpanded = $bloqueActivo ? 'true' : 'false';

            $menuItems .= "
                <li class='menuLateral2-item has-children {$itemClase} {$claseInicio}'>
                    <button type='button'
                            class='menuLateral2-link menuLateral2-toggle'
                            data-menu-toggle='submenu-{$idMenu}'
                            aria-expanded='{$ariaExpanded}'
                            title='{$descripcionMenu}'>
                        <span class='menuLateral2-link-main'>
                            <span class='menuLateral2-icon'>{$iconoMenu}</span>
                            <span class='menuLateral2-text'>{$nombreMenu}</span>
                        </span>
                        <span class='menuLateral2-compact-indicator'>
                            <i class='bi bi-chevron-down'></i>
                        </span>
                        <i class='bi bi-chevron-down menuLateral2-chevron'></i>
                    </button>
                    <div id='submenu-{$idMenu}' class='menuLateral2-submenu'{$mostrarSubmenu}>
                        {$submenuHtml}
                    </div>
                </li>";
        } else {
            $menuItems .= "
                <li class='menuLateral2-item {$itemClase} {$claseInicio}'>
                    <a class='menuLateral2-link' href='{$archivoMenuSeguro}' title='{$descripcionMenu}'>
                        <span class='menuLateral2-icon'>{$iconoMenu}</span>
                        <span class='menuLateral2-text'>{$nombreMenu}</span>
                    </a>
                </li>";
        }
    }

    $menuLateralCss = htmlspecialchars($this->resolverRutaSistema('css/menuLateral.css'), ENT_QUOTES, 'UTF-8');

    echo "<link rel='stylesheet' href='{$menuLateralCss}'>
    <ul class='navbar-nav sidebar sidebar-v2 is-collapsed' id='accordionSidebarV2'>
        <a class='menuLateral2-brand' href='{$urlPrincipal}'>
            <img src='" . htmlspecialchars($this->resolverRutaSistema('imagenes/logo_seduc.png'), ENT_QUOTES, 'UTF-8') . "' alt='Logo SEDUC'>
            <div class='menuLateral2-brand-text'>" . htmlspecialchars($tituloSistema, ENT_QUOTES, 'UTF-8') . "</div>
        </a>
        <div class='menuLateral2-divider'></div>
        <div class='menuLateral2-list'>
            {$menuItems}
        </div>
    </ul>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('accordionSidebarV2');

            document.querySelectorAll('[data-menu-toggle]').forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-menu-toggle');
                    const target = document.getElementById(targetId);
                    if (!target) {
                        return;
                    }

                    const expanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                    target.style.display = expanded ? 'none' : 'block';
                    this.parentElement.classList.toggle('is-active', !expanded);
                });
            });
        });
    </script>";
}




public function menuLateral2($idUsuarioSession, $idPagActual)
{
    $bdato = new MySQL("", "", "");
    $idUsuarioSession = (int) $idUsuarioSession;
    $archivoActual = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
    $urlPrincipal = htmlspecialchars($this->resolverRutaSistema('principal.php'), ENT_QUOTES, 'UTF-8');

    $sqlMenus = "SELECT DISTINCT
                    m.id_menu,
                    m.nombre AS NombreMenu,
                    m.abreviacion,
                    m.icono AS iconoMenu,
                    m.archivo AS archivoMenu,
                    m.caracteristica AS descripcionMenu,
                    m.orden
                FROM permisos_menu_1 pm
                INNER JOIN menu_1 m ON pm.id_menu1 = m.id_menu
                WHERE pm.id_usuario = $idUsuarioSession
                  AND pm.id_tipo_permiso IN (1, 2)
                ORDER BY m.orden ASC";

    $resultadoMenus = $bdato->consulta($sqlMenus);
    $menuItems = '';

    while ($menu = $bdato->fetch_array($resultadoMenus)) {
        $idMenu = (int) $menu['id_menu'];
        $nombreMenu = htmlspecialchars($menu['NombreMenu'], ENT_QUOTES, 'UTF-8');
        $archivoMenu = trim((string) $menu['archivoMenu']);
        $archivoMenuSeguro = htmlspecialchars($this->resolverRutaSistema($archivoMenu), ENT_QUOTES, 'UTF-8');
        $descripcionMenu = htmlspecialchars((string) $menu['descripcionMenu'], ENT_QUOTES, 'UTF-8');
        $iconoMenu = $this->renderizarIconoMenu($menu['iconoMenu'] ?? '', 'bi bi-grid');
        $isActive = ($idMenu === (int) $idPagActual);

        $submenuHtml = '';
        $submenuActivo = false;
        $sqlSubmenus = "SELECT id_submenu, nombre, archivo, icono
                        FROM menu_1_sub
                        WHERE id_menu = $idMenu
                        ORDER BY orden ASC";
        $resultadoSubmenus = $bdato->consulta($sqlSubmenus);

        while ($submenu = $bdato->fetch_array($resultadoSubmenus)) {
            $nombreSubmenu = htmlspecialchars($submenu['nombre'], ENT_QUOTES, 'UTF-8');
            $archivoSubmenu = trim((string) $submenu['archivo']);
            $archivoSubmenuSeguro = htmlspecialchars($this->resolverRutaSistema($archivoSubmenu), ENT_QUOTES, 'UTF-8');
            $submenuEstaActivo = ($archivoSubmenu !== '' && $archivoActual === $archivoSubmenu);
            $submenuActivo = $submenuActivo || $submenuEstaActivo;
            $submenuClase = $submenuEstaActivo ? 'is-active' : '';
            $iconoSubmenu = $this->renderizarIconoMenu($submenu['icono'] ?? '', 'bi bi-dot');

            $submenuHtml .= "
                <a class='menuLateralClassic-subitem {$submenuClase}' href='{$archivoSubmenuSeguro}'>
                    <span class='menuLateralClassic-subicon'>{$iconoSubmenu}</span>
                    <span>{$nombreSubmenu}</span>
                </a>";
        }

        $itemActivo = ($isActive || $submenuActivo) ? 'is-active' : '';
        $tieneSubmenu = $submenuHtml !== '';

        if ($tieneSubmenu) {
            $mostrarSubmenu = $submenuActivo ? ' style=\"display:block;\"' : '';
            $ariaExpanded = $submenuActivo ? 'true' : 'false';
            $menuItems .= "
                <li class='menuLateralClassic-item has-children {$itemActivo}'>
                    <button type='button'
                            class='menuLateralClassic-link menuLateralClassic-toggle'
                            data-classic-toggle='classic-submenu-{$idMenu}'
                            aria-expanded='{$ariaExpanded}'
                            title='{$descripcionMenu}'>
                        <span class='menuLateralClassic-link-main'>
                            <span class='menuLateralClassic-icon'>{$iconoMenu}</span>
                            <span class='menuLateralClassic-text'>{$nombreMenu}</span>
                        </span>
                        <i class='bi bi-chevron-down menuLateralClassic-chevron'></i>
                    </button>
                    <div id='classic-submenu-{$idMenu}' class='menuLateralClassic-submenu'{$mostrarSubmenu}>
                        {$submenuHtml}
                    </div>
                </li>";
        } else {
            $menuItems .= "
                <li class='menuLateralClassic-item {$itemActivo}'>
                    <a class='menuLateralClassic-link' href='{$archivoMenuSeguro}' title='{$descripcionMenu}'>
                        <span class='menuLateralClassic-link-main'>
                            <span class='menuLateralClassic-icon'>{$iconoMenu}</span>
                            <span class='menuLateralClassic-text'>{$nombreMenu}</span>
                        </span>
                        <span class='menuLateralClassic-dot'></span>
                    </a>
                </li>";
        }
    }

    // ── Ítem fijo: "Administrar Colegios" ────────────────────────────────────
    // No vive en menu_1; se muestra a quien tenga acceso al menú de
    // Configuración (id_menu = 7). Para moverlo a la BD, ver menu_1_sub.
    $resColegiosMenu = $bdato->consulta(
        "SELECT 1 FROM permisos_menu_1
         WHERE id_usuario = $idUsuarioSession AND id_menu1 = 7 AND id_tipo_permiso IN (1, 2)
         LIMIT 1"
    );
    if ($resColegiosMenu && $bdato->fetch_array($resColegiosMenu)) {
        $rutaColegios  = htmlspecialchars($this->resolverRutaSistema('configuracion/mantenedor_colegios.php'), ENT_QUOTES, 'UTF-8');
        $iconoColegios = $this->renderizarIconoMenu('bi bi-buildings', 'bi bi-buildings');
        $colegiosActivo = ($archivoActual === 'mantenedor_colegios.php') ? 'is-active' : '';
        $menuItems .= "
                <li class='menuLateralClassic-item {$colegiosActivo}'>
                    <a class='menuLateralClassic-link' href='{$rutaColegios}' title='Panel de establecimientos SEDUC'>
                        <span class='menuLateralClassic-link-main'>
                            <span class='menuLateralClassic-icon'>{$iconoColegios}</span>
                            <span class='menuLateralClassic-text'>Administrar Colegios</span>
                        </span>
                        <span class='menuLateralClassic-dot'></span>
                    </a>
                </li>";
    }

    $menuLateralCss = htmlspecialchars($this->resolverRutaSistema('css/menuLateral.css'), ENT_QUOTES, 'UTF-8');
    $logoSistema = htmlspecialchars($this->resolverRutaSistema('imagenes/logo_seduc.png'), ENT_QUOTES, 'UTF-8');

    echo "<link rel='stylesheet' href='{$menuLateralCss}'>
    <aside class='navbar-nav sidebar sidebar-classic' id='accordionSidebarClassic'>
    <script>
    (function(){
        var s = document.getElementById('accordionSidebarClassic');
        if (!s) return;
        if (localStorage.getItem('menuLateral2Collapsed') === '1') {
            s.classList.add('is-collapsed');
        }
        s.classList.add('sidebar-no-transition');
        requestAnimationFrame(function(){
            requestAnimationFrame(function(){
                s.classList.remove('sidebar-no-transition');
            });
        });
    })();
    </script>
        <a class='menuLateralClassic-brand' href='{$urlPrincipal}'>
            <img src='{$logoSistema}' alt='Logo administrador'>
            <div class='menuLateralClassic-brand-copy'>
                <strong>Admin</strong>
                <span>Panel de Control</span>
            </div>
        </a>
        <div class='menuLateralClassic-list'>
            {$menuItems}
        </div>
    </aside>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('accordionSidebarClassic');
            const sidebarStorageKey = 'menuLateral2Collapsed';
            const sidebarToggles = Array.prototype.slice.call(
                document.querySelectorAll('#sidebarToggleTop, #sidebarToggle')
            );
            let submenuFlotanteActivo = null;

            function cerrarSubmenuFlotante() {
                if (!submenuFlotanteActivo) {
                    return;
                }

                submenuFlotanteActivo.classList.remove('is-floating-open');
                submenuFlotanteActivo.style.display = 'none';
                submenuFlotanteActivo.style.top = '';
                submenuFlotanteActivo.style.left = '';
                submenuFlotanteActivo = null;
            }

            function actualizarSubmenus() {
                if (!sidebar) {
                    return;
                }

                const colapsado = sidebar.classList.contains('is-collapsed');
                sidebar.querySelectorAll('[data-classic-toggle]').forEach(function (trigger) {
                    const targetId = trigger.getAttribute('data-classic-toggle');
                    const target = document.getElementById(targetId);
                    if (!target) {
                        return;
                    }

                    if (colapsado) {
                        target.style.display = 'none';
                        target.classList.remove('is-floating-open');
                        return;
                    }

                    const expanded = trigger.getAttribute('aria-expanded') === 'true';
                    target.style.display = expanded ? 'block' : 'none';
                });
            }

            function aplicarEstadoSidebar(colapsado) {
                if (!sidebar) {
                    return;
                }

                sidebar.classList.toggle('is-collapsed', colapsado);
                sidebarToggles.forEach(function (toggle) {
                    toggle.setAttribute('aria-expanded', colapsado ? 'false' : 'true');
                });
                cerrarSubmenuFlotante();
                localStorage.setItem(sidebarStorageKey, colapsado ? '1' : '0');
                actualizarSubmenus();
            }

            if (sidebar) {
                const estadoGuardado = localStorage.getItem(sidebarStorageKey);
                aplicarEstadoSidebar(estadoGuardado === '1');

                sidebarToggles.forEach(function (toggle) {
                    toggle.addEventListener('click', function () {
                        aplicarEstadoSidebar(!sidebar.classList.contains('is-collapsed'));
                    });
                });
            }

            document.querySelectorAll('[data-classic-toggle]').forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-classic-toggle');
                    const target = document.getElementById(targetId);
                    if (!target) {
                        return;
                    }

                    if (sidebar && sidebar.classList.contains('is-collapsed')) {
                        const abierto = target.classList.contains('is-floating-open');

                        cerrarSubmenuFlotante();

                        if (abierto) {
                            this.setAttribute('aria-expanded', 'false');
                            this.parentElement.classList.remove('is-active');
                            return;
                        }

                        target.style.display = 'block';
                        target.classList.add('is-floating-open');
                        target.style.top = '0px';
                        target.style.left = 'calc(100% - 6px)';

                        const submenuRect = target.getBoundingClientRect();
                        const overflowBottom = submenuRect.bottom - (window.innerHeight - 12);
                        const overflowTop = submenuRect.top - 12;

                        if (overflowBottom > 0) {
                            target.style.top = (-overflowBottom) + 'px';
                        }

                        if (overflowTop < 0) {
                            const topActual = parseFloat(target.style.top || '0') || 0;
                            target.style.top = (topActual + Math.abs(overflowTop)) + 'px';
                        }

                        submenuFlotanteActivo = target;
                        this.setAttribute('aria-expanded', 'true');
                        this.parentElement.classList.add('is-active');
                        return;
                    }

                    const expanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                    target.style.display = expanded ? 'none' : 'block';
                    this.parentElement.classList.toggle('is-active', !expanded);
                });
            });

            document.addEventListener('click', function (event) {
                if (!sidebar || !sidebar.classList.contains('is-collapsed') || !submenuFlotanteActivo) {
                    return;
                }

                if (event.target.closest('.menuLateralClassic-submenu.is-floating-open') || event.target.closest('[data-classic-toggle]')) {
                    return;
                }

                cerrarSubmenuFlotante();
                document.querySelectorAll('[data-classic-toggle]').forEach(function (toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                    if (toggle.parentElement) {
                        toggle.parentElement.classList.remove('is-active');
                    }
                });
            });

            actualizarSubmenus();
        });
    </script>";
}



















function cabezera()
{
    $db = new MySQL("", "", "");
    $idUsuarioSession = htmlspecialchars($_SESSION['id']);
    $urlPerfilImagen = htmlspecialchars($this->resolverRutaSistema('img/undraw_profile.svg'), ENT_QUOTES, 'UTF-8');
    $urlPerfilImagenMujer = htmlspecialchars($this->resolverRutaSistema('img/undraw_profile_1.svg'), ENT_QUOTES, 'UTF-8');
    $urlPerfilImagenHombre = htmlspecialchars($this->resolverRutaSistema('img/undraw_profile_2.svg'), ENT_QUOTES, 'UTF-8');
    $urlPerfil = htmlspecialchars($this->resolverRutaSistema('perfil.php'), ENT_QUOTES, 'UTF-8');
    $urlConfiguracion = htmlspecialchars($this->resolverRutaSistema('configuracion.php'), ENT_QUOTES, 'UTF-8');

    // --- ALERTAS ---
    $alertasQuery = "
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
        WHERE tickets.id_usuario = $idUsuarioSession
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
        WHERE recordatorio.id_usuario = $idUsuarioSession
    ";

    $alertasResults = $db->consulta($alertasQuery);
    $alertas = [];
    while ($row = $db->fetch_array($alertasResults)) {
        $alertas[] = $row;
    }

    // --- MENSAJES ---
    $messageQuery = "
        SELECT mensajes.*, usuarios.* 
        FROM mensajes 
        JOIN usuarios ON usuarios.id = mensajes.de 
        WHERE mensajes.para = $idUsuarioSession
    ";

    $messageResults = $db->consulta($messageQuery);
    $mensajes = [];
    while ($row = $db->fetch_array($messageResults)) {
        $mensajes[] = $row;
    }

    // --- NAVBAR ---
    echo '<ul class="navbar-nav ml-auto">';
    echo '<li class="nav-item no-arrow mx-1" id="idGoogleCalendar">';
    echo "<a class=\"nav-link\" href=\"#\"
            onclick=\"abrirModalGoogleCalendarDesdeTicket(123, 'Problema PC', '2026-03-20T10:00', '2026-03-20T11:00', 'Detalle del ticket'); return false;\"
            title=\"Agendar en Google Calendar\">
            <i class=\"bi bi-calendar-event fa-fw\"></i>
          </a>";
    echo '</li>';
    // ALERTAS
    echo '<li class="nav-item dropdown no-arrow mx-1" id="idAlertar">';
    echo '<a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
    echo '<i class="fas fa-bell fa-fw"></i>';
    if (count($alertas) > 0) {
        echo '<span class="badge badge-danger badge-counter" id="numeroAlertas">' . count($alertas) . '</span>';
    }
    echo '</a>';

    echo '<div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown" id="contendorTicket" style="min-width: 320px;">';
    echo '<h6 class="dropdown-header">Recordatorios</h6>';
    echo '<button class="btn btn-primary btn-sm float-right" type="button" onclick="mostrarAlertas(8)">+</button>';

    foreach ($alertas as $alerta) {
        echo '<a class="dropdown-item d-flex align-items-center" href="' .
             ($alerta['tipo'] === 'ticket' ? 'ticket_asignados.php?id=' . $alerta['id'] : '#') . '">';

        echo '<div class="dropdown-list-image mr-3">';
        echo '<img class="rounded-circle" src="' . $urlPerfilImagenMujer . '" alt="...">';
        echo '<div class="status-indicator" style="background-color:' . ($alerta['estado_color'] ?: '#17a2b8') . ';"></div>';
        echo '</div>';

        echo '<div>';
        if ($alerta['tipo'] === 'ticket') {
            echo '<div class="text-truncate">' . $alerta['asunto'] . ' <span style="color:' . $alerta['estado_color'] . '; font-weight: bold;">(' . $alerta['estado_nombre'] . ')</span></div>';
            echo '<div class="small text-gray-500">' . $alerta['usuario_nombre'] . ' ' . $alerta['apellido_paterno'] . ' ' . $alerta['apellido_materno'] . '</div>';
        } else {
            echo '<div class="text-truncate">Recordatorio: ' . $alerta['recordatorio_titulo'] . '</div>';
            echo '<div class="small text-gray-500">' . $alerta['recordatorio_detalle'] . '</div>';
        }
        echo '</div>';

        echo '</a>';
    }

    echo '</div>'; // Fin alertas
    echo '</li>';

    // MENSAJES
    echo '<li class="nav-item dropdown no-arrow mx-2" id="idMensajes">';
    echo '<a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
    echo '<i class="fas fa-envelope fa-fw"></i>';
    if (count($mensajes) > 0) {
        echo '<span class="badge badge-danger badge-counter" id="numeroMensajes">' . count($mensajes) . '</span>';
    }
    echo '</a>';

    echo '<div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="messagesDropdown" id="contendorMensajes">';
    echo '<h6 class="dropdown-header">Mensajes</h6>';
    foreach ($mensajes as $mensaje) {
        echo '<a class="dropdown-item d-flex align-items-center" href="#">';
        echo '<div class="dropdown-list-image mr-3">';
        $imageSrc = ($mensaje['sexo'] == 2) ? $urlPerfilImagenMujer : $urlPerfilImagenHombre;
        echo '<img class="rounded-circle" src="' . $imageSrc . '" alt="...">';
        echo '<div class="status-indicator bg-success"></div>';
        echo '</div>';
        echo '<div class="font-weight-bold">';
        echo '<div class="text-truncate">' . $mensaje['mensaje'] . '</div>';
        echo '<div class="small text-gray-500">' . $mensaje['fecha'] . '</div>';
        echo '</div>';
        echo '</a>';
    }
    echo '</div>';
    echo '</li>';

    // USUARIO
    
    echo '<div class="topbar-divider d-none d-sm-block"></div>';
    echo '<li class="nav-item dropdown no-arrow" id="idDatosPersonales">';
    echo '<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
    echo '<span class="mr-2 text-gray-600 small topbar-user-name">' . $_SESSION['nombre'] . " " .  htmlentities($_SESSION['apellido_paterno'], ENT_HTML5, "ISO-8859-1") . " <br> " . $_SESSION['apellido_materno'] .  '</span>';
    echo '<img class="img-profile rounded-circle" src="' . $urlPerfilImagen . '">';
    echo '</a>';
    echo '<div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">';
    echo '<a class="dropdown-item" href="' . $urlPerfil . '"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Perfil</a>';
    echo '<a class="dropdown-item" href="' . $urlConfiguracion . '"><i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i> Configuración</a>';
  


    echo '<a class="dropdown-item" href="#"><i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i> Actividades</a>';
    echo '<div class="dropdown-divider"></div>';
    echo '<a class="dropdown-item" onclick="cerrar_session()"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Cerrar sesión</a>';
    echo '</div>';
    echo '</li>';

    echo '</ul>';
}

    function footer()
    {
        $currentYear = date("Y");
        echo '<footer class="sticky-footer bg-white">' .
            '<div class="container my-auto">' .
            '<div class="copyright text-center my-auto">' .
            '<span>&copy; Seduc Spa ' . $currentYear . '</span>' .
            '</div>' .
            '</div>' .
            '</footer>';
    }

    //************************************************************************* */
    //************PRINCIPAL.PHP************************************************ */                   

    function accesoDirecto($idUsuarioSession)
    {
        $bdato = new MySQL("", "", "");  // Asegúrate de proporcionar los detalles correctos de conexión
        $sql = "SELECT * FROM contenedor WHERE id_usuario = $idUsuarioSession";  // Específica los campos necesarios para mejorar el rendimiento
        $resultado = $bdato->consulta($sql);

        // Verifica que la consulta devuelva resultados
        if ($bdato->num_rows($resultado) > 0) {
            while ($row = $bdato->fetch_array($resultado)) {
                echo '<div class="col-xl-2 col-md-6 mb-3">';
                echo '<div class="card contenedor-card shadow h-100 py-2 position-relative" style="background-image: url(\'imagenes/' . htmlspecialchars($row['imagen']) . '\'); background-size: cover; min-height: 200px;">';

                // Botones de acción
                echo '<div class="button-container position-absolute" style="top: 10px; right: 10px;">';
                echo '<a href="#" class="btn btn-danger btn-circle btn-sm" onclick="eliminarContenedor(' . $row['id'] . ')" data-bs-toggle="tooltip" data-bs-placement="top" id="idBasurero">';
                echo '<i class="fas fa-trash"></i>';
                echo '</a>';
                echo '<a href="#" class="btn btn-warning btn-circle btn-sm" onclick="modificarContenedor(' . $row['id'] . ')" data-bs-toggle="tooltip" data-bs-placement="top" id="idModificar">';
                echo '<i class="fas fa-edit"></i>';
                echo '</a>';
                echo '</div>';

                // Contenido del contenedor
                echo '<div class="card-body position-absolute" style="bottom: 10px; left: 10px; color: white;">';
                echo '<div class="text-xs font-weight-bold text-uppercase mb-1">';
                echo htmlspecialchars($row['nombre']);
                echo '</div>';
                echo '</div>';

                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<div class="col-12">No hay contenedores disponibles.</div>';
        }
    }

    function accesoDirecto2($idUsuarioSession)
    {
        $bdato = new MySQL("", "", "");  // Asegúrate de proporcionar los detalles correctos de conexión
        $sql = "SELECT id, nombre, imagen, url_ FROM contenedor WHERE id_usuario = $idUsuarioSession";  // Específica los campos necesarios para mejorar el rendimiento
        $resultado = $bdato->consulta($sql);

        // Verifica que la consulta devuelva resultados
        if ($bdato->num_rows($resultado) > 0) {
            while ($row = $bdato->fetch_array($resultado)) {
                echo '<div class="col-xl-2 col-md-6 mb-3 position-relative">';

                echo '<a href="https://' . htmlspecialchars($row['url_']) . '" target="_blank" class="contenedor-card" id="idContenedor1" style="display: block; text-decoration: none; color: inherit;">';

                echo '<div class="card-body p-0">';
                echo '<div class="contenedor-imagen" style="overflow: hidden; position: relative;">';
                echo '<img class="img-fluid w-100 h-100" src="imagenes/' . htmlspecialchars($row['imagen']) . '" alt="' . htmlspecialchars($row['nombre']) . '">';
                echo '<div class="contenedor-nombre text-center text-white">' . htmlspecialchars($row['nombre']) . '</div>';
                echo '</div>';
                echo '</div>';
                echo '</a>';

                // Botón de menú desplegable en la esquina superior derecha
                echo '<div class="button-container" style="position: absolute; top: 10px; right: 10px; display: flex; flex-direction: column; gap: 5px;">';
                echo '<div class="dropdown">';
                echo '<button class="btn btn-secondary btn-sm" type="button" id="dropdownMenuButtonLight" data-bs-toggle="dropdown" aria-expanded="false">';
                echo '<i class="bi bi-list" style="font-size:100%;"></i>';
                echo '</button>';
                echo '<ul class="dropdown-menu" aria-labelledby="dropdownMenuButtonLight">';
                echo '<li><a class="dropdown-item text-danger" href="#" onclick="eliminarContenedor(' . $row['id'] . ')">Eliminar <i class="fas fa-trash"></i></a></li>';
                echo '<li><a class="dropdown-item text-warning" href="#" onclick="modificarContenedor(' . $row['id'] . ')">Modificar <i class="fas fa-edit"></i></a></li>';
                echo '</ul>';
                echo '</div>';
                echo '</div>';

                echo '</div>';
            }
        } else {
            echo '<div class="col-12">No hay contenedores disponibles.</div>';
        }
    }
        
    // FUNCION PARA INRGREDAR ACCESOS DIRECTOS 
    function obtenerAccesosDirectos($idUsuarioSession) {
        $bdato = new MySQL("", "", ""); // Ajusta los parámetros
        $sql = "SELECT id, nombre, imagen, url_ FROM contenedor WHERE id_usuario = $idUsuarioSession";
        $resultado = $bdato->consulta($sql);
    
        // Iniciar el contenedor de accesos directos
        $output = '<div id="contenedorAccesosDirectos" class="d-none">';
    
        // Botón para agregar un nuevo contenedor
        $output .= '<button type="button" class="btn btn-primary mb-2" onclick="agregar_contenedorr(' . htmlspecialchars($idUsuarioSession) . ')">';
        $output .= '<i class="bi bi-plus-circle"></i>';
        $output .= '</button>';
    
        // Mostrar accesos directos si existen
        if ($bdato->num_rows($resultado) > 0) {
            while ($row = $bdato->fetch_array($resultado)) {
                $id = htmlspecialchars($row['id']);
                $nombre = htmlspecialchars($row['nombre']);
                $imagen = htmlspecialchars($row['imagen']);
                $url = htmlspecialchars($row['url_']);
    
                $output .= '<div class="acceso-directo-container">';
                // Botón de menú (hamburguesa)
                $output .= '<div class="menu-hamburguesa">';
                $output .= '<button class="btn-menu" onclick="toggleMenu(' . $id . ')">';
                $output .= '<i class="bi bi-list"></i>';
                $output .= '</button>';
                // Menú de acciones
                $output .= '<div class="menu-acciones d-none" id="menu-acciones-' . $id . '">';
                $output .= '<button class="boton-editar" onclick="modificarContenedor(' . $id . ')">';
                $output .= '<i class="bi bi-pencil-fill"></i>';
                $output .= '</button>';
                $output .= '<button class="boton-eliminar" onclick="eliminarContenedor(' . $id . ')">';
                $output .= '<i class="bi bi-x-circle-fill"></i>';
                $output .= '</button>';
                $output .= '</div>'; // Fin del menú de acciones
                $output .= '</div>'; // Fin del menú hamburguesa
                // Ícono de acceso directo
                $output .= '<a href="https://' . $url . '" target="_blank" class="acceso-directo">';
                $output .= '<img src="imagenes/' . $imagen . '" alt="' . $nombre . '">';
                $output .= '</a>';
                $output .= '</div>'; // Fin del contenedor de acceso directo
            }
        } else {
            $output .= '<p>No hay accesos directos disponibles.</p>';
        }
    
        $output .= '</div>'; // Fin del contenedor principal
    
        echo $output;
    }
           
        

    //************************************************************************* */
    //************************************************************************* */        

    //************************************************ */
    //*************MENSAJES.PHP***************** */
    function obtenerMensajes($idUsuario)
    {
        $bdato = new MySQL("", "", "");
    
        $sql = "
            SELECT 
                mc.id_conversacion AS id_mensaje,
                mc.id_conversacion,
                mc.mensaje,
                mc.de,
                mc.para,
                mc.fecha_hora,
                mc.leido,
                mc.urgente,
                mc.eliminado,
                mc.prioridad,
                u.nombre,
                u.apellido_paterno,
                u.apellido_materno,
                u.email,
                u.telefono,
                u.cargo
            FROM mensajes_chat mc
            INNER JOIN (
                SELECT 
                    id_conversacion, 
                    MAX(fecha_hora) AS ultima_fecha
                FROM mensajes_chat
                WHERE (para = $idUsuario OR de = $idUsuario) AND eliminado = 0
                GROUP BY id_conversacion
            ) ultimos ON mc.id_conversacion = ultimos.id_conversacion AND mc.fecha_hora = ultimos.ultima_fecha
            JOIN usuarios u ON u.id = mc.de
            ORDER BY mc.fecha_hora DESC
        ";
    
        $resultado = $bdato->consulta($sql);
        $mensajes = [];
    
        while ($row = $bdato->fetch_array($resultado)) {
            $mensajes[] = $row;
        }
    
        return $mensajes;
    }
     
    
    

    public function mensajesModal($idUsuarioSession)
    {
        $bdato = new MySQL("", "", ""); // Asegúrate de llenar con los parámetros correctos
        $sql = "SELECT mensaje FROM mensajes WHERE para = '$idUsuarioSession' AND eliminado != 'SI' AND leido != 'SI'";

        $resultado = $bdato->consulta($sql);

        $mensajes = "";
        $countMensajes = 0;
        while ($row = $bdato->fetch_array($resultado)) {
            $countMensajes++;
        }

        if ($countMensajes === 0) {
            $mensajes = '<div class="alert alert-danger" role="alert"><i class="bi bi-chat-left-text-fill"></i>Sin mensajes</div>';
        } else {
            $mensajes = '<div class="alert alert-danger" role="alert"><span class="me-2"><i class="bi bi-chat-left-text-fill"></i></span><a href="mensaje.php" class="alert-link">Tienes ' . $countMensajes . ' mensajes</a></div>' . $mensajes;
        }

        return $mensajes;
    }


    public function alertaModal($idUsuarioSession)
    {
        $bdato = new MySQL("", "", ""); // Asegúrate de llenar con los parámetros correctos
        $notificacionCumpleanos = "";

        // Verificar cumpleaños
        $fechaActual = date('m-d');
        $sqlCumpleanos = "SELECT nombre, apellido_paterno, DATE_FORMAT(fecha_nacimiento, '%m-%d') AS fecha_nacimiento 
                                          FROM usuarios 
                                          WHERE DATE_FORMAT(fecha_nacimiento, '%m-%d') = '$fechaActual'";
        $resultadoCumpleanos = $bdato->consulta($sqlCumpleanos);

        $countCumpleanos = 0;
        if ($bdato->num_rows($resultadoCumpleanos) > 0) {
            while ($row = $bdato->fetch_array($resultadoCumpleanos)) {
                $countCumpleanos++;
                $nombreCumple = htmlspecialchars($row['nombre']);
                $apellido = htmlspecialchars($row['apellido_paterno']);
                $notificacionCumpleanos .= '
                                <div class="alert alert-dark" role="alert">
                                    ¡Hoy es el Cumpleaños de, ' . $nombreCumple . ' ' . $apellido . '!
                                </div><br>';
            }
        }

        // Verificar tickets con estado 2 (asignados)
        $countTicketsAsignados = 0;
        $sqlTicketsAsignados = "SELECT * FROM tickets WHERE id_tecnico = '$idUsuarioSession' AND id_estado = 2";
        $resultadoTicketsAsignados = $bdato->consulta($sqlTicketsAsignados);

        if ($bdato->num_rows($resultadoTicketsAsignados) > 0) {
            while ($row = $bdato->fetch_array($resultadoTicketsAsignados)) {
                $countTicketsAsignados++;
            }
        }
        $notificacionTicketsAsignadosCount = $countTicketsAsignados > 0 ? '<div class="alert alert-danger" role="alert"><span class="me-2"><i class="bi bi-bell-fill"></i></span><a href="ticket_asignados.php" class="alert-link">Tienes ' . $countTicketsAsignados . ' tickets asignados</a></div>' : '';

        // Verificar tickets con estado 3 (en proceso) asignados al id_usuario
        $countTicketsEnProcesoUsuario = 0;
        $sqlTicketsEnProcesoUsuario = "SELECT * FROM tickets WHERE id_usuario = '$idUsuarioSession' AND id_estado = 3";
        $resultadoTicketsEnProcesoUsuario = $bdato->consulta($sqlTicketsEnProcesoUsuario);

        if ($bdato->num_rows($resultadoTicketsEnProcesoUsuario) > 0) {
            while ($row = $bdato->fetch_array($resultadoTicketsEnProcesoUsuario)) {
                $countTicketsEnProcesoUsuario++;
            }
        }
        $notificacionTicketsEnProcesoUsuarioCount = $countTicketsEnProcesoUsuario > 0 ? '<div class="alert alert-danger" role="alert"><span class="me-2"><i class="bi bi-bell-fill"></i></span><a href="ticket.php" class="alert-link">Tienes ' . $countTicketsEnProcesoUsuario . ' tickets en proceso</a></div>' : '';

        // Verificar tickets terminados
        $countTicketsTerminados = 0;
        $sqlTicketsTerminados = "SELECT * FROM tickets WHERE id_usuario = '$idUsuarioSession' AND id_estado = 5";
        $resultadoTicketsTerminados = $bdato->consulta($sqlTicketsTerminados);

        if ($bdato->num_rows($resultadoTicketsTerminados) > 0) {
            while ($row = $bdato->fetch_array($resultadoTicketsTerminados)) {
                $countTicketsTerminados++;
            }
        }
        $notificacionTicketsTerminadosCount = $countTicketsTerminados > 0 ? '<div class="alert alert-danger" role="alert"><span class="me-2"><i class="bi bi-bell-fill"></i></span><a href="ticket.php" class="alert-link">Tienes ' . $countTicketsTerminados . ' tickets terminados</a></div>' : '';

        // Verificar tickets no asignados cuando idUsuarioSession es 7 y id_estado es 1
        $countTicketsNoAsignados = 0;
        $notificacionTicketsNoAsignadosCount = '';
        if ($idUsuarioSession == 7) {

            $sqlTicketsNoAsignados = "SELECT * FROM tickets WHERE id_estado = 1";
            $resultadoTicketsNoAsignados = $bdato->consulta($sqlTicketsNoAsignados);

            if ($bdato->num_rows($resultadoTicketsNoAsignados) > 0) {
                while ($row = $bdato->fetch_array($resultadoTicketsNoAsignados)) {
                    $countTicketsNoAsignados++;
                }
            }
            $notificacionTicketsNoAsignadosCount = $countTicketsNoAsignados > 0 ? '<div class="alert alert-warning" role="alert"><span class="me-2"><i class="bi bi-bell-fill"></i></span><a href="ticket.php" class="alert-link">Tienes ' . $countTicketsNoAsignados . ' tickets que no has designado técnico</a></div>' : '';
        }

        // Verificar recordatorios
        $notificacionRecordatorios = "";
        $countRecordatorios = 0;
        $sqlRecordatorios = "SELECT * FROM recordatorio WHERE id_usuario = '$idUsuarioSession' AND recordar = 'SI'";
        $resultadoRecordatorios = $bdato->consulta($sqlRecordatorios);

        if ($bdato->num_rows($resultadoRecordatorios) > 0) {
            while ($row = $bdato->fetch_array($resultadoRecordatorios)) {
                $countRecordatorios++;
                $tituloRecordatorio = htmlspecialchars($row['titulo']);
                $detalleRecordatorio = htmlspecialchars($row['detalle']);
                $fechaRecordatorio = htmlspecialchars($row['fecha']);
                $notificacionRecordatorios .= '
                                <div class="alert alert-info" role="alert">
                                    Recordatorio: ' . $tituloRecordatorio . '
                                </div><br>';
            }
        }

        if ($countCumpleanos === 0 && $countTicketsAsignados === 0 && $countTicketsEnProcesoUsuario === 0 && $countTicketsTerminados === 0 && $countTicketsNoAsignados === 0 && $countRecordatorios === 0) {
            return '<div class="alert alert-danger" role="alert"><i class="bi bi-bell-slash-fill"></i>Sin Ticket</div>';
        }

        $notificacionCumpleanosCount = $countCumpleanos > 0 ? '<div class="alert alert-danger" role="alert">Tienes ' . $countCumpleanos . ' cumpleaños hoy</div>' : '';

        return $notificacionCumpleanosCount . $notificacionCumpleanos . $notificacionTicketsAsignadosCount . $notificacionTicketsEnProcesoUsuarioCount . $notificacionTicketsTerminadosCount . $notificacionTicketsNoAsignadosCount . $notificacionRecordatorios;
    }


    
        
    function mensajes_Enviados($idUsuarioSession)
    {
        $bdato = new MySQL("", "", ""); // Asegúrate de llenar con los parámetros correctos
        $sql = 'SELECT 
                            m1.mensaje,
                            m1.de,
                            m1.para,
                            m1.id_conversacion,
                            m1.id AS id_mensaje,
                            m1.fecha,   
                            m1.hora,    
                            m1.leido,    
                            m1.urgente,  
                            u.nombre,
                            u.apellido_paterno
                        FROM 
                            mensajes m1
                        LEFT JOIN 
                            usuarios u ON u.id = m1.de
                       
                        INNER JOIN 
                            (SELECT 
                                 id_conversacion, 
                                 MIN(id) AS primer_id
                             FROM 
                                 mensajes
                             WHERE 
                                 eliminado != "SI"
                             GROUP BY 
                                 id_conversacion
                            ) m2 ON m1.id_conversacion = m2.id_conversacion AND m1.id = m2.primer_id
                        WHERE 
                            m1.de = "' . $idUsuarioSession . '"
                            AND m1.leido != "SI"
                        ORDER BY 
                            m1.fecha ASC, 
                            m1.hora ASC';
        // echo $sql."¨***";
        $resultado = $bdato->consulta($sql);
        $mensajes = [];
    
        while ($row = $bdato->fetch_array($resultado)) {
            $mensajes[] = $row;
        }
        // var_dump($mensajes);
        return $mensajes;
    }
    
        
    
    
    
    
    
    
    
    

    function mensajes_archivados($idUsuarioSession)
    {
        // echo "***".$idUsuarioSession."***********";
        echo '<div class="card-body">';
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped" id="dataTable" width="100%" cellspacing="0">';
        echo '<thead>';
        echo '<tr>';
        echo '<th id="numero">N°</th>';
        echo '<th id="fecha">FECHA</th>';
        echo '<th id="hora">HORA</th>';
        echo '<th id="de">PARA</th>';
        echo '<th id="mensajes">MENSAJE</th>';
        echo '<th id="opciones">OPCIONES</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        $bdato = new MySQL("", "", ""); // Asegúrate de llenar con los parámetros correctos
        $sql = 'SELECT 
                            mensajes.mensaje,
                            mensajes.id AS "id_mensaje",
                            mensajes.fecha,   
                            mensajes.hora,    
                            mensajes.leido,    
                            mensajes.urgente,  
                            usuarios.nombre,
                            usuarios.apellido_paterno
                        FROM 
                            mensajes 
                        JOIN 
                            usuarios 
                        ON 
                            usuarios.id = mensajes.para 
                        WHERE 
                            mensajes.para = "' . $idUsuarioSession . '"
                            AND mensajes.leido = "SI"
                            AND mensajes.eliminado = "NO"
                        ORDER BY 
                            mensajes.fecha ASC, 
                            mensajes.hora ASC
                        '; // Ordenar por fecha y hora en orden ascendente
        // echo $sql."*******";

        $contador = 1;
        $resultado = $bdato->consulta($sql);
        while ($row = $bdato->fetch_array($resultado)) {
            echo "<tr>";
            echo "<td>$contador</td>";
            echo "<td>{$row['fecha']}</td>";
            echo "<td>{$row['hora']}</td>";
            echo "<td>{$row['nombre']} - {$row['apellido_paterno']}</td>";
            echo "<td>" . htmlentities($row['mensaje'], ENT_HTML5, "ISO-8859-1") . "</td>";
            echo '<td>';

            echo '<span style="margin-left: 10px;"></span>'; // Espaciador con un margen a la izquierda
            echo '<a href="#" class="btn btn-primary btn-icon-split position-relative" id="btnvermensaje" onclick="pasar_borrador_creado(' . $row['id_mensaje'] . ')">';
            echo '<i class="bi bi-eye" ></i>';
            echo '</a>';
            if ($row['urgente'] == 'SI') {
                echo '<span class="position-absolute top-10 start-10 translate-middle badge rounded-pill bg-danger" id="spanUrgente">';
                echo 'Urgente';
                echo '<span class="visually-hidden">unread messages</span>';
                echo '</span>';
            }

            echo '<span style="margin-left: 10px;"></span>'; // Espaciador con un margen a la izquierda

            echo '<a href="#" class="btn btn-danger btn-icon-split" id="btneliminar" title="Eliminar mensaje" onclick="eliminarMensaje(' . $row['id_mensaje'] . ')">';
            echo '<i class="bi bi-trash3"></i>';
            echo '</a>';


            echo '</td>';
            echo '</tr>';
            $contador++;
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    function cerrarSession()
    {
        echo '<div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">';
        echo '<a class="dropdown-item" href="#">';
        echo '<i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>';
        echo 'Perfil';
        echo '</a>';

        echo '<a class="dropdown-item" href="#">';
        echo '<i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>'; // Cambiado el icono para Configuración
        echo 'Configuración';
        echo '</a>';

        echo '<a class="dropdown-item" href="#">';
        echo '<i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>'; // Cambiado el icono para Actividades
        echo 'Actividades';
        echo '</a>';

        echo '<div class="dropdown-divider"></div>';

        echo '<a class="dropdown-item" href="template_02/index.php" data-toggle="modal" data-target="#logoutModal">';
        echo '<i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>';
        echo 'Cerrar sesión';
        echo '</a>';

        echo '</div>';
    }


    function CumpleanosUsuarios()
    {
        $bdato = new MySQL("", "", ""); // Asegúrate de reemplazar con tus credenciales reales
        $hoy = date('Y-m-d'); // Usamos el formato de fecha completo para comparación precisa
        $sql = "SELECT id, nombre, apellido_paterno FROM usuarios WHERE DATE_FORMAT(fecha_nacimiento, '%m-%d') = DATE_FORMAT(CURDATE(), '%m-%d')";
        $resultado = $bdato->consulta($sql);
        $cumplenanosHoy = [];

        while ($dato1 = $bdato->fetch_array($resultado)) {
            // Aquí debes almacenar $dato1 en lugar de $usuario
            $cumplenanosHoy[] = $dato1;
        }

        return $cumplenanosHoy;
    }

    function alertas() {}


    // *************************************************************************
    //            CONTENEDORES MENSAJES
    // *************************************************************************

    function contendormensajesRecibidos($idUsuarioSession)
    {
        echo '<div class="col-lg-6 col-xl-3 mb-4">';
        echo '<div class="card text-white h-100"style="background-color: #D7DBDD; ">';
        echo '<div class="card-body">';
        $bdato = new MySQL("", "", ""); // Asegúrate de configurar tus parámetros reales

        // Construcción de la consulta para contar los mensajes recibidos
        $sql = "SELECT COUNT(*) as total_mensajes FROM mensajes WHERE `para` = $idUsuarioSession AND `leido` = 'NO' AND `eliminado` = 'NO'";



        $resultado = $bdato->consulta($sql);
        $contador = 0; // Iniciar contador

        if ($fila = $bdato->fetch_array($resultado)) {
            $contador = $fila['total_mensajes']; // Guardar el total de mensajes recibidos
        }

        // HTML para mostrar la cantidad de mensajes recibidos
        echo '<div class="d-flex justify-content-between align-items-center">';
        echo '<div class="me-3">';
        echo '<div class="text-white-75 small" style="color:black;"><strong>Mensajes Sin Gestionar</strong></div>';
        echo '<div class="text-lg fw-bold" style="color:black;">' . $contador . '</div>';
        echo '</div>';
        echo '<i class="bi bi-envelope fa-2x text-gray-300" style="color: black !important;"></i>';
        echo '</div>';
        echo '</div>';
        echo '<div class="card-footer d-flex align-items-center justify-content-between small">';
        echo '<a class="text-white" href="mensajes.php">';
        echo '<i class="bi bi-bar-chart-fill fa-2x" style="color:black;"></i>';
        echo '</a>';
        echo '<div class="text-white ms-auto">';
        echo '<i class="bi bi-search fa-2x" onclick="filtrarMensajes()"></i>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    function contenedorMensajesArchivados($idUsuarioSession)
    {
        echo '<div class="col-lg-6 col-xl-3 mb-4">';
        echo '<div class="card bg-primary  text-white h-100">';
        echo '<div class="card-body">';
        $bdato = new MySQL("", "", ""); // Asegúrate de configurar tus parámetros reales

        // Construcción de la consulta para contar los mensajes recibidos
        $sql = "SELECT COUNT(*) as total_mensajes FROM mensajes WHERE `para` = $idUsuarioSession AND `leido` = 'SI' AND `eliminado` = 'NO'";

        $resultado = $bdato->consulta($sql);
        $contador = 0; // Iniciar contador

        if ($fila = $bdato->fetch_array($resultado)) {
            $contador = $fila['total_mensajes']; // Guardar el total de mensajes recibidos
        }

        // HTML para mostrar la cantidad de mensajes recibidos
        echo '<div class="d-flex justify-content-between align-items-center">';
        echo '<div class="me-3">';
        echo '<div class="text-white-75 small" style="color:black;"><strong>Mensajes Gestionados (Archivados)</strong></div>';
        echo '<div class="text-lg fw-bold" style="color:black;">' . $contador . '</div>';
        echo '</div>';
        echo '<i class="bi bi-envelope fa-2x text-gray-300" style="color: black !important;"></i>';
        echo '</div>';
        echo '</div>';
        echo '<div class="card-footer d-flex align-items-center justify-content-between small">';
        echo '<a class="text-white" href="mensajes.php">';
        echo '<i class="bi bi-bar-chart-fill fa-2x" style="color:black;"></i>';
        echo '</a>';
        echo '<div class="text-white ms-auto">';
        echo '<i class="bi bi-search fa-2x" onclick="filtrarMensajes()"></i>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    function contenedorMensajesEliminados($idUsuarioSession)
    {
        echo '<div class="col-lg-6 col-xl-3 mb-4">';
        echo '<div class="card text-white h-100" style="background-color: #82E0AA;">';
        echo '<div class="card-body">';
        $bdato = new MySQL("", "", ""); // Asegúrate de configurar tus parámetros reales

        // Construcción de la consulta para contar los mensajes recibidos
        $sql = "SELECT COUNT(*) as total_mensajes FROM mensajes WHERE `para` = $idUsuarioSession  AND `eliminado` = 'SI'";

        $resultado = $bdato->consulta($sql);
        $contador = 0; // Iniciar contador

        if ($fila = $bdato->fetch_array($resultado)) {
            $contador = $fila['total_mensajes']; // Guardar el total de mensajes recibidos
        }

        // HTML para mostrar la cantidad de mensajes recibidos
        echo '<div class="d-flex justify-content-between align-items-center">';
        echo '<div class="me-3">';
        echo '<div class="text-white-75 small" style="color:black;"><strong>Mensajes Archivados</strong></div>';
        echo '<div class="text-lg fw-bold"style="color:black;">' . $contador . '</div>';
        echo '</div>';
        echo '<i class="bi bi-envelope fa-2x text-gray-300" style="color: black !important;"></i>';
        echo '</div>';
        echo '</div>';
        echo '<div class="card-footer d-flex align-items-center justify-content-between small">';
        echo '<a class="text-white" href="mensajes.php">';
        echo '<i class="bi bi-bar-chart-fill fa-2x" style="color:black;"></i>';
        echo '</a>';
        echo '<div class="text-white ms-auto">';
        echo '<i class="bi bi-search fa-2x" onclick="filtrarMensajes()"></i>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }


    // *************************************************************************
    // *************************************************************************
    //              CONTENEDORES DE LOS ESTADO TICKEC
    // *************************************************************************

    public function obtenerColoresEstados()
    {
        $bdato = new MySQL("", "", "");
        $sql = "SELECT id, color, color_degradado, descripcion_estado FROM estados_ticket";
        $consulta = $bdato->consulta($sql);
    
        $colores = [];
    
        while ($row = $bdato->fetch_assoc($consulta)) {
            $colores[$row['id']] = [
                "color" => $row["color"],
                "degradado" => $row["color_degradado"],
                "descripcion" => $row["descripcion_estado"]
            ];
        }
    
        return $colores;
    }

    public function actualizarTicketsDemoradosAutomaticamente()
    {
        $bdato = new MySQL("", "", "");
        $sql = "UPDATE tickets t
                INNER JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
                SET t.id_estado = 7
                WHERE t.id_estado IN (2, 3)
                  AND pt.fecha_estimada_admin IS NOT NULL
                  AND pt.fecha_estimada_admin <> '0000-00-00'
                  AND pt.fecha_estimada_admin < CURDATE()";
        $bdato->guardar($sql);
    }
    
    
            
      function contenedorTicketRecibidos($idUsuarioSession, $idPagActual)
    {
        $bdato = new MySQL("", "", "");
        $idUsuarioSession = intval($idUsuarioSession);
        $idEstado = 1; // Recibido
    
        // Construcción de la consulta
        if ($idPagActual == 4) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets";
        } elseif ($idPagActual == 5) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_tecnico = $idUsuarioSession";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_tecnico = $idUsuarioSession";
        } elseif ($idPagActual == 3 ) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_usuario = $idUsuarioSession";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_usuario = $idUsuarioSession";
        
        } elseif ($idPagActual == 8) {
        $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_usuario = $idUsuarioSession";
        $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_usuario = $idUsuarioSession";
        // echo $sqlTotal."</br>";
        // echo $sql."</br>";


       } else {
            $sql = $sqlTotal = "";
        }
    
        // Resultados
        $contador = 0;
        $totalTickets = 1;
    
        if ($sql) {
            $res = $bdato->consulta($sql);
            if ($fila = $bdato->fetch_array($res)) {
                $contador = intval($fila['total']);
            }
        }
    
        if ($sqlTotal) {
            $resTotal = $bdato->consulta($sqlTotal);
            if ($fila = $bdato->fetch_array($resTotal)) {
                $totalTickets = intval($fila['total']);
            }
        }
    
        $porcentaje = round(($contador / max(1, $totalTickets)) * 100);
        $claseAtencion = ($contador >= 1) ? 'estado-atencion' : '';
    
        // Traer color y descripción
        $colorFondo = "linear-gradient(135deg, #E6E6FA, #FFFFFF)";
        $DescripcionEstados = "";
        $resColor = $bdato->consulta("SELECT color_degradado, descripcion_estado FROM estados_ticket WHERE id = $idEstado");
        if ($filaColor = $bdato->fetch_array($resColor)) {
            $colorFondo = $filaColor['color_degradado'] ?: $colorFondo;
            $DescripcionEstados = $filaColor['descripcion_estado'] ?: $DescripcionEstados;
        }
        ?>
    
        <div class="tarjeta-ticket-estado fade-in <?= $claseAtencion ?>" style="background: <?= $colorFondo ?>;" data-intro="Este recuadro muestra la cantidad de tickets que aún no han sido asignados a un técnico. Es útil para identificar rápidamente qué solicitudes están pendientes de distribución dentro del equipo técnico.">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <div class="fw-bold">Tickets No Asignados</div>
                        <div class="fw-bold text-dark" data-bs-toggle="tooltip" title="<?= $contador ?> de <?= $totalTickets ?>"><?= $contador ?> (<?= $porcentaje ?>%)</div>
                    </div>
                    <div class="icon-circle bg-white shadow-sm text-dark">
                        <i 
                            class="bi bi-ticket-perforated"
                            data-bs-toggle="popover"
                            data-bs-trigger="hover focus"
                            data-bs-placement="top"
                            data-bs-html="true"
                            title="¿Qué significa este estado?"
                            data-bs-content="<?= htmlspecialchars($DescripcionEstados, ENT_QUOTES, 'UTF-8') ?>"
                            style="cursor: pointer;"
                        ></i>
                    </div>
                </div>
    
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar bg-warning" style="width: <?= $porcentaje ?>%;" role="progressbar" aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-end small" data-intro="Al hacer clic en la lupa, se filtrarán los tickets según el estado correspondiente al contenedor.
Si haces clic nuevamente en la lupa, se eliminará el filtro y se volverán a mostrar todos los tickets disponibles.">
                <i class="bi bi-search fa-2x" onclick="filtrarTickets(1);" style="color:black; cursor:pointer;" title="Ver solo asignados"></i>
            </div>
        </div>
    
        <?php
    }

        
    function contenedorTicketAsignados($idUsuarioSession, $idPagActual)
    {
        $bdato = new MySQL("", "", "");
        $idUsuarioSession = intval($idUsuarioSession);
        $idEstado = 2; // Asignado
    
        if ($idPagActual == 4) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets";
        } elseif ($idPagActual == 5) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_tecnico = $idUsuarioSession";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_tecnico = $idUsuarioSession";
        } elseif ($idPagActual == 3) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_usuario = $idUsuarioSession";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_usuario = $idUsuarioSession";
        } elseif ($idPagActual == 8) {
        $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_usuario = $idUsuarioSession";
        $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_usuario = $idUsuarioSession";
        // echo $sqlTotal."</br>";
        // echo $sql."</br>";
       }else {
            $sql = $sqlTotal = "";
        }
    
        $contador = 0;
        $totalTickets = 1;
    
        if (!empty($sql)) {
            $res = $bdato->consulta($sql);
            if ($fila = $bdato->fetch_array($res)) {
                $contador = intval($fila['total']);
            }
        }
    
        if (!empty($sqlTotal)) {
            $resTotal = $bdato->consulta($sqlTotal);
            if ($fila = $bdato->fetch_array($resTotal)) {
                $totalTickets = intval($fila['total']);
            }
        }
    
        $porcentaje = round(($contador / max(1, $totalTickets)) * 100);
    
        $colorFondo = "linear-gradient(135deg, #FFF4C1, #FFFFE0)"; $DescripcionEstados = "";
        $resColor = $bdato->consulta("SELECT color_degradado, descripcion_estado FROM estados_ticket WHERE id = $idEstado");
        if ($filaColor = $bdato->fetch_array($resColor)) {
            $colorFondo = $filaColor['color_degradado'] ?: $colorFondo;
            $DescripcionEstados = $filaColor['descripcion_estado'] ?: $DescripcionEstados;
        }
        ?>
    
        <div class="tarjeta-ticket-estado fade-in" style="background: <?= $colorFondo ?>;" data-intro="Muestra la cantidad de tickets que ya han sido asignados a un técnico, pero que aún no han comenzado su resolución.">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <div class="fw-bold" style="color:black;">  Tickets Asignados</div>
                        <div class="fw-bold text-dark" data-bs-toggle="tooltip" title="<?= $contador ?> de <?= $totalTickets ?>"><?= $contador ?> (<?= $porcentaje ?>%)</div>
                    </div>
             
                    <div class="icon-circle bg-white shadow-sm text-dark">
                        <i 
                            class="bi bi-person-check fa-lg"
                            data-bs-toggle="popover"
                            data-bs-trigger="hover focus"
                            data-bs-placement="top"
                            data-bs-html="true"
                            title="¿Qué significa este estado?"
                            data-bs-content="<?= $DescripcionEstados ?>"
                            style="cursor: pointer;"
                        ></i>
                    </div>
                </div>
    
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar bg-info" style="width: <?= $porcentaje ?>%;" role="progressbar" aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
    
            <div class="card-footer d-flex align-items-center justify-content-end small">
                <i class="bi bi-search fa-2x" onclick="filtrarTickets(2);" style="color:black; cursor:pointer;" title="Ver solo asignados"></i>
            </div>
        </div>
    
        <?php
    }

    
    function contenedorTicketEnProceso($idUsuarioSession, $idPagActual)
    {
        $bdato = new MySQL("", "", "");
        $idUsuarioSession = intval($idUsuarioSession);
        $idEstado = 3; // En proceso
    
        if ($idPagActual == 4) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets";
        } elseif ($idPagActual == 5) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_tecnico = $idUsuarioSession";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_tecnico = $idUsuarioSession";
        } elseif ($idPagActual == 3) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_usuario = $idUsuarioSession";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_usuario = $idUsuarioSession";
        } 
        elseif ($idPagActual == 8) {
        $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_usuario = $idUsuarioSession";
        $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_usuario = $idUsuarioSession";
        // echo $sqlTotal."</br>";
        // echo $sql."</br>";

       } else {
            $sql = $sqlTotal = "";
        }
    
        $contador = 0;
        $totalTickets = 1;
    
        if (!empty($sql)) {
            $res = $bdato->consulta($sql);
            if ($fila = $bdato->fetch_array($res)) {
                $contador = intval($fila['total']);
            }
        }
    
        if (!empty($sqlTotal)) {
            $resTotal = $bdato->consulta($sqlTotal);
            if ($fila = $bdato->fetch_array($resTotal)) {
                $totalTickets = intval($fila['total']);
            }
        }
    
        $porcentaje = round(($contador / max(1, $totalTickets)) * 100);
        
        
            $colorFondo = "linear-gradient(135deg, #E6E6FA, #FFFFFF)";
            $DescripcionEstados = "";
            $resColor = $bdato->consulta("SELECT color_degradado, descripcion_estado FROM estados_ticket WHERE id = $idEstado");
            if ($filaColor = $bdato->fetch_array($resColor)) {
                $colorFondo = $filaColor['color_degradado'] ?: $colorFondo;
                $DescripcionEstados = $filaColor['descripcion_estado'] ?: $DescripcionEstados;
            }
        
        ?>
    
        <div class="tarjeta-ticket-estado fade-in"  style="background: <?= $colorFondo ?>;" data-intro="Muestra la cantidad de tickets que ya han sido asignados a un técnico, pero que aún no han comenzado su resolución.">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <div class="fw-bold" style="color:black;">Tickets en Proceso</div>
                        <div class="fw-bold text-dark" data-bs-toggle="tooltip" title="<?= $contador ?> de <?= $totalTickets ?>"><?= $contador ?> (<?= $porcentaje ?>%)</div>
                    </div>
                    
                <div class="icon-circle bg-white shadow-sm text-dark">
                    <i 
                        class="bi bi-hourglass-split fa-lg"
                        data-bs-toggle="popover"
                        data-bs-trigger="hover focus"
                        data-bs-placement="top"
                        data-bs-html="true"
                        title="¿Qué significa este estado?"
                        data-bs-content="<?= htmlspecialchars($DescripcionEstados, ENT_QUOTES, 'UTF-8') ?>"
                        style="cursor: pointer;"
                    ></i>
                </div>
                    
                </div>
    
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar bg-warning" style="width: <?= $porcentaje ?>%;" role="progressbar" aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
    
            <div class="card-footer d-flex align-items-center justify-content-end small">
                <i class="bi bi-search fa-2x" onclick="filtrarTickets(3);" style="color:black; cursor:pointer;" title="Ver en proceso"></i>
            </div>
        </div>
    
        <?php
    }


        
    function contenedorTicketTerminados($idUsuarioSession, $idPagActual)
    {
        $bdato = new MySQL("", "", "");
        $idUsuarioSession = intval($idUsuarioSession);
        $idEstado = 5; // Terminado
    
        if ($idPagActual == 4) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets";
        } elseif ($idPagActual == 5) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_tecnico = $idUsuarioSession";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_tecnico = $idUsuarioSession";
        } elseif ($idPagActual == 3) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_usuario = $idUsuarioSession";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_usuario = $idUsuarioSession";
        } elseif ($idPagActual == 8) {
        $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_usuario = $idUsuarioSession";
        $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_usuario = $idUsuarioSession";
        // echo $sqlTotal."</br>";
        // echo $sql."</br>";


       } else {
            $sql = $sqlTotal = "";
        }
    
        $contador = 0;
        $totalTickets = 1;
    
        if (!empty($sql)) {
            $res = $bdato->consulta($sql);
            if ($fila = $bdato->fetch_array($res)) {
                $contador = intval($fila['total']);
            }
        }
    
        if (!empty($sqlTotal)) {
            $resTotal = $bdato->consulta($sqlTotal);
            if ($fila = $bdato->fetch_array($resTotal)) {
                $totalTickets = intval($fila['total']);
            }
        }
    
        $porcentaje = round(($contador / max(1, $totalTickets)) * 100);
    
            $colorFondo = "linear-gradient(135deg, #E6E6FA, #FFFFFF)";
            $DescripcionEstados = "";
            $resColor = $bdato->consulta("SELECT color_degradado, descripcion_estado FROM estados_ticket WHERE id = $idEstado");
            if ($filaColor = $bdato->fetch_array($resColor)) {
                $colorFondo = $filaColor['color_degradado'] ?: $colorFondo;
                $DescripcionEstados = $filaColor['descripcion_estado'] ?: $DescripcionEstados;
            }
        ?>
    
        <div class="tarjeta-ticket-estado fade-in"  style="background: <?= $colorFondo ?>;" data-intro="Indica los tickets que están actualmente siendo trabajados por un técnico. Representan aquellos en ejecución activa.">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <div class="fw-bold" style="color:black;">Tickets Terminados</div>
                        <div class="fw-bold text-dark" data-bs-toggle="tooltip" title="<?= $contador ?> de <?= $totalTickets ?>"><?= $contador ?> (<?= $porcentaje ?>%)</div>
                    </div>
                <div class="icon-circle bg-white shadow-sm text-dark">
                    <i 
                        class="bi bi-check-circle-fill fa-lg"
                        data-bs-toggle="popover"
                        data-bs-trigger="hover focus"
                        data-bs-placement="top"
                        data-bs-html="true"
                        title="¿Qué significa este estado?"
                        data-bs-content="<?= htmlspecialchars($DescripcionEstados, ENT_QUOTES, 'UTF-8') ?>"
                        style="cursor: pointer;"
                    ></i>
                </div>
                </div>
    
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: <?= $porcentaje ?>%;" role="progressbar" aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
    
            <div class="card-footer d-flex align-items-center justify-content-end small">
                <i class="bi bi-search fa-2x" onclick="filtrarTickets(5);" style="color:black; cursor:pointer;" title="Ver terminados"></i>
            </div>
        </div>
    
        <?php
    }

    
    function contenedorTicketCerrados($idUsuarioSession, $idPagActual)
    {
        $bdato = new MySQL("", "", "");
        $idUsuarioSession = intval($idUsuarioSession);
        $idEstado = 6; // Cerrado
    
        if ($idPagActual == 4) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets";
        } elseif ($idPagActual == 5) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_tecnico = $idUsuarioSession";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_tecnico = $idUsuarioSession";
        } elseif ($idPagActual == 3) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_usuario = $idUsuarioSession";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_usuario = $idUsuarioSession";
        } 
        elseif ($idPagActual == 8) {
        $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_usuario = $idUsuarioSession";
        $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_usuario = $idUsuarioSession";
        // echo $sqlTotal."</br>";
        // echo $sql."</br>";


       } else {
            $sql = $sqlTotal = "";
        }
    
        $contador = 0;
        $totalTickets = 1;
    
        if (!empty($sql)) {
            $res = $bdato->consulta($sql);
            if ($fila = $bdato->fetch_array($res)) {
                $contador = intval($fila['total']);
            }
        }
    
        if (!empty($sqlTotal)) {
            $resTotal = $bdato->consulta($sqlTotal);
            if ($fila = $bdato->fetch_array($resTotal)) {
                $totalTickets = intval($fila['total']);
            }
        }
    
        $porcentaje = round(($contador / max(1, $totalTickets)) * 100);
    
        // ✅ Degradado dinámico desde tabla estados_ticket
      $colorFondo = "linear-gradient(135deg, #E6E6FA, #FFFFFF)";
            $DescripcionEstados = "";
            $resColor = $bdato->consulta("SELECT color_degradado, descripcion_estado FROM estados_ticket WHERE id = $idEstado");
            if ($filaColor = $bdato->fetch_array($resColor)) {
                $colorFondo = $filaColor['color_degradado'] ?: $colorFondo;
                $DescripcionEstados = $filaColor['descripcion_estado'] ?: $DescripcionEstados;
            }
        ?>
    
        <div class="tarjeta-ticket-estado fade-in"  style="background: <?= $colorFondo ?>;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <div class="fw-bold" style="color:black;">Tickets Cerrados</div>
                        <div class="fw-bold text-dark" data-bs-toggle="tooltip" title="<?= $contador ?> de <?= $totalTickets ?>"><?= $contador ?> (<?= $porcentaje ?>%)</div>
                    </div>
                    <div class="icon-circle bg-white shadow-sm text-dark">
                        <i class="bi bi-x-circle-fill fa-lg"></i>
                    </div>
                </div>
    
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar bg-dark" style="width: <?= $porcentaje ?>%;" role="progressbar" aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
    
            <div class="card-footer d-flex align-items-center justify-content-end small">
                <i class="bi bi-search fa-2x" onclick="filtrarTickets(6);" style="color:black; cursor:pointer;" title="Ver cerrados"></i>
            </div>
        </div>
    
        <?php
    }


    function contenedorTicketDemorados($idUsuarioSession, $idPagActual)
    {
        $bdato = new MySQL("", "", "");
        $idUsuarioSession = intval($idUsuarioSession);
        $idEstado = 7; // Estado de tickets demorados
    
        if ($idPagActual == 4) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets";
        } elseif ($idPagActual == 5) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_tecnico = $idUsuarioSession";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_tecnico = $idUsuarioSession";
        } elseif ($idPagActual == 3) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_usuario = $idUsuarioSession";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_usuario = $idUsuarioSession";
        } elseif ($idPagActual == 8) {
        $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_usuario = $idUsuarioSession";
        $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_usuario = $idUsuarioSession";
        // echo $sqlTotal."</br>";
        // echo $sql."</br>";


       } else {
            $sql = $sqlTotal = "";
        }
    
        $contador = 0;
        $totalTickets = 1;
    
        if (!empty($sql)) {
            $res = $bdato->consulta($sql);
            if ($fila = $bdato->fetch_array($res)) {
                $contador = intval($fila['total']);
            }
        }
    
        if (!empty($sqlTotal)) {
            $resTotal = $bdato->consulta($sqlTotal);
            if ($fila = $bdato->fetch_array($resTotal)) {
                $totalTickets = intval($fila['total']);
            }
        }
    
        $porcentaje = round(($contador / max(1, $totalTickets)) * 100);
    
        // ✅ Obtener el degradado desde estados_ticket
        $colorFondo = "linear-gradient(135deg, #E6E6FA, #FFFFFF)";
            $DescripcionEstados = "";
            $resColor = $bdato->consulta("SELECT color_degradado, descripcion_estado FROM estados_ticket WHERE id = $idEstado");
            if ($filaColor = $bdato->fetch_array($resColor)) {
                $colorFondo = $filaColor['color_degradado'] ?: $colorFondo;
                $DescripcionEstados = $filaColor['descripcion_estado'] ?: $DescripcionEstados;
            }
        ?>
    
        <div class="tarjeta-ticket-estado fade-in" style="background: <?= $colorFondo ?>;" data-intro="Agrupa los tickets cuya solución ya fue completada por el técnico, pero que aún podrían no estar cerrados oficialmente.">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <div class="fw-bold" style="color:black;">Tickets Atrasados</div>
                        <div class="fw-bold text-dark" data-bs-toggle="tooltip" title="<?= $contador ?> de <?= $totalTickets ?>"><?= $contador ?> (<?= $porcentaje ?>%)</div>
                    </div>
               
                    
                    
                       
                <div class="icon-circle bg-white shadow-sm text-dark">
                    <i 
                        class="bi bi-exclamation-triangle-fill"
                        data-bs-toggle="popover"
                        data-bs-trigger="hover focus"
                        data-bs-placement="top"
                        data-bs-html="true"
                        title="¿Qué significa este estado?"
                        data-bs-content="<?= htmlspecialchars($DescripcionEstados, ENT_QUOTES, 'UTF-8') ?>"
                        style="cursor: pointer;"
                    ></i>
                </div>
                </div>
    
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar bg-danger" style="width: <?= $porcentaje ?>%;" role="progressbar" aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
    
            <div class="card-footer d-flex align-items-center justify-content-end small">
                <i class="bi bi-search fa-2x" onclick="filtrarTickets(7);" style="color:black; cursor:pointer;" title="Ver demorados"></i>
            </div>
        </div>
    
        <?php
    }
    

    function contenedorTicketBorrador($idUsuarioSession, $idPagActual)
    {
        $bdato = new MySQL("", "", "");
        $idUsuarioSession = intval($idUsuarioSession);
        $idEstado = 4; // Estado de borrador
    
        if ($idPagActual == 4) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets";
        } elseif ($idPagActual == 5) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_tecnico = $idUsuarioSession";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_tecnico = $idUsuarioSession";
        } elseif ($idPagActual == 3) {
            $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_usuario = $idUsuarioSession";
            $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_usuario = $idUsuarioSession";
        } elseif ($idPagActual == 8) {
        $sql = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = $idEstado AND id_usuario = $idUsuarioSession";
        $sqlTotal = "SELECT COUNT(*) as total FROM tickets WHERE id_usuario = $idUsuarioSession";
        // echo $sqlTotal."</br>";
        // echo $sql."</br>";


       } else {
            $sql = $sqlTotal = "";
        }
    
        $contador = 0;
        $totalTickets = 1;
    
        if (!empty($sql)) {
            $res = $bdato->consulta($sql);
            if ($fila = $bdato->fetch_array($res)) {
                $contador = intval($fila['total']);
            }
        }
    
        if (!empty($sqlTotal)) {
            $resTotal = $bdato->consulta($sqlTotal);
            if ($fila = $bdato->fetch_array($resTotal)) {
                $totalTickets = intval($fila['total']);
            }
        }
    
        $porcentaje = round(($contador / max(1, $totalTickets)) * 100);
    
        // ✅ Obtener el degradado desde estados_ticket
        $colorFondo = "linear-gradient(135deg, #F3E5F5, #FFFFFF)"; // por defecto
        $DescripcionEstados = "";
        $resColor = $bdato->consulta("SELECT color_degradado, descripcion_estado FROM estados_ticket WHERE id = $idEstado");
        if ($filaColor = $bdato->fetch_array($resColor)) {
            $colorFondo = $filaColor['color_degradado'] ?: $colorFondo;
            $DescripcionEstados = $filaColor['descripcion_estado'] ?: $DescripcionEstados;
        }
        ?>
    
        <div class="tarjeta-ticket-estado fade-in" style="background: <?= $colorFondo ?>;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <div class="fw-bold" style="color:black;">Tickets en Borrador</div>
                        <div class="fw-bold text-dark" data-bs-toggle="tooltip" title="<?= $contador ?> de <?= $totalTickets ?>"><?= $contador ?> (<?= $porcentaje ?>%)</div>
                    </div>
    
                    <div class="icon-circle bg-white shadow-sm text-dark">
                        <i 
                            class="bi bi-pencil-fill"
                            data-bs-toggle="popover"
                            data-bs-trigger="hover focus"
                            data-bs-placement="top"
                            data-bs-html="true"
                            title="¿Qué significa este estado?"
                            data-bs-content="<?= htmlspecialchars($DescripcionEstados, ENT_QUOTES, 'UTF-8') ?>"
                            style="cursor: pointer;"
                        ></i>
                    </div>
                </div>
    
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar bg-secondary" style="width: <?= $porcentaje ?>%;" role="progressbar" aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
    
            <div class="card-footer d-flex align-items-center justify-content-end small">
                <i class="bi bi-search fa-2x" onclick="filtrarTickets(4);" style="color:black; cursor:pointer;" title="Ver borradores"></i>
            </div>
        </div>
    
        <?php
    }



    // *************************************************************************              
    // *************************************************************************
    // ********************* IMPRIMIR TICKET ******************************               
   public function ticketUsuario($idUsuarioSession, $estado = null)
    {
        $bdato = new MySQL("", "", "");
        $sql = "SELECT 
    t.*, 
    et.nombre AS 'nombreEstado',
    et.orden AS 'ordenEstado',
    et.color AS 'colorEstado',
    et.descripcion_estado AS 'descripcionEstado',
    pt.fecha_creacion_inicio,
    pt.dias_estimada_admin AS dias_administrador_estima,
    pt.hora_creacion_inicio,
    pt.fecha_estimada_admin,
    pt.fecha_asignacion_tecnico,
    pt.hora_asignacion_tecnico,
    pt.fecha_comienzo_ticket,
    pt.hora_comienzo_ticket,
    pt.fecha_termino_ticket,
    pt.hora_termino_ticket,
    ct.id_ticket AS tieneCalificacion,
    ct.id_calificacion AS calificacionEstrellas,
    -- Subconsultas para contar correctamente sin multiplicación
    (SELECT COUNT(*) FROM archivos_adjuntos_ticket aa WHERE aa.id_ticket = t.id_ticket) AS cantidadArchivos,
    (SELECT COUNT(*) FROM conversaciones c WHERE c.id_ticket = t.id_ticket) AS cantidadConversaciones,
    (SELECT COUNT(*) FROM ticket_conversaciones tc WHERE tc.id_ticket = t.id_ticket) AS cantidadMensajes,
    (SELECT COUNT(*) FROM ticket_conversaciones tc 
        WHERE tc.id_ticket = t.id_ticket AND tc.leido = 0 AND tc.receptor = '$idUsuarioSession') AS cantidadMensajesNoLeidos,
    t.id_usuario AS 'id_usuario',
    t.id_tecnico AS 'id_tecnico',
    us.nombre,
    us.apellido_paterno,   
    us.apellido_materno,
    ct.id_ticket AS tieneCalificacion
        FROM 
            tickets t
        JOIN estados_ticket           AS et ON et.id = t.id_estado
        JOIN usuarios                 AS us ON us.id = t.id_usuario
        LEFT JOIN proceso_tickets     AS pt ON pt.id_ticket = t.id_ticket
        LEFT JOIN calificacion_tickett AS ct ON ct.id_ticket = t.id_ticket
        WHERE t.id_usuario = '$idUsuarioSession'
        ";
         
        if ($estado !== null) {
            $sql .= " AND t.id_estado = '$estado'";
        }
    
        $sql .= " GROUP BY t.id_ticket
                  ORDER BY pt.fecha_creacion_inicio DESC, pt.hora_creacion_inicio DESC";
    
        return $bdato->consulta($sql);
    }


    function obtenerTicketsTecnico($idUsuarioSession, $estado = null)
    {
        $bdato = new MySQL("", "", "");
        $sql = "SELECT  
                    t.id_ticket, 
                    t.id_usuario AS 'id_usuario',
                    t.id_tecnico AS 'id_tecnico',
                    t.asunto, 
                    t.descripcion_ticket, 
                    t.id_categoria_ticket, 
                    t.id_estado, 
                    t.id_prioridad, 
                    t.id_tecnico, 
                    t.comentario_administrador, 
                    t.identificador, 
                    et.nombre AS nombreEstado, 
                    et.color AS colorEstado,
                    us.nombre AS nombreUsuario, 
                    us.apellido_paterno AS apellidoUsuario,
                    tecnico.nombre AS nombreTecnico, 
                    tecnico.apellido_paterno AS apellidoTecnico,
                    pt.fecha_creacion_inicio,
                    pt.hora_creacion_inicio,
                    pt.fecha_estimada_admin,
                    pt.dias_estimada_admin AS dias_administrador_estima,
                    pt.fecha_asignacion_tecnico,
                    pt.hora_asignacion_tecnico,
                    pt.fecha_comienzo_ticket,
                    pt.hora_comienzo_ticket,
                    COUNT(tc.id) AS cantidadMensajes,
                    pt.fecha_termino_ticket,
                    pt.hora_termino_ticket,
                    ct.id_ticket AS tieneCalificacion,
                    ct.id_calificacion AS calificacionEstrellas,
                    (SELECT COUNT(*) FROM archivos_adjuntos_ticket WHERE id_ticket = t.id_ticket) AS cantidadArchivos,
                    COUNT(c.id) AS cantidadConversaciones
                FROM tickets t
                JOIN estados_ticket             AS et       ON et.id            = t.id_estado
                JOIN usuarios                   AS us       ON us.id            = t.id_usuario
                LEFT JOIN usuarios              AS tecnico  ON tecnico.id       = t.id_tecnico
                LEFT JOIN proceso_tickets       AS pt       ON pt.id_ticket     = t.id_ticket
                LEFT JOIN conversaciones        AS c        ON c.id_ticket      = t.id_ticket
                LEFT JOIN calificacion_tickett  AS ct       ON ct.id_ticket     = t.id_ticket
                LEFT JOIN ticket_conversaciones     AS tc ON tc.id_ticket = t.id_ticket

                
                WHERE t.id_tecnico = '$idUsuarioSession'
                AND t.id_estado != 4";

        if ($estado !== null) {
            $sql .= " AND t.id_estado = '$estado'";
        }

        $sql .= " GROUP BY t.id_ticket
                ORDER BY pt.fecha_creacion_inicio DESC, pt.hora_creacion_inicio DESC";

        return $bdato->consulta($sql);
    }

    // Cambia la función para que solo devuelva los resultados
    function ticketAdministrador($idUsuarioSession, $estado = null)
    {
        $bdato = new MySQL("", "", ""); // Asegúrate de reemplazar con tus parámetros de conexión reales
        $sql = "SELECT 
                            t.id_ticket, 
                            t.id_usuario, 
                            t.asunto, 
                            t.descripcion_ticket, 
                            t.id_categoria_ticket, 
                            t.id_estado, 
                            t.id_prioridad, 
                            t.id_tecnico, 
                            t.comentario_administrador, 
                            t.identificador, 
                            et.nombre AS 'nombreEstado', 
                            et.color AS colorEstado,
                            us.nombre AS 'nombreUsuario', 
                            us.apellido_paterno AS 'apellidoUsuario',
                            tecnico.nombre AS 'nombreTecnico', 
                            tecnico.apellido_paterno AS 'apellidoTecnico',
                            pt.fecha_creacion_inicio,
                            pt.hora_creacion_inicio,
                            pt.fecha_estimada_admin,
                            pt.dias_estimada_admin AS dias_administrador_estima,
                            pt.fecha_asignacion_tecnico,
                            pt.hora_asignacion_tecnico,
                            pt.fecha_comienzo_ticket,
                            pt.hora_comienzo_ticket,
                            pt.fecha_termino_ticket,
                            pt.hora_termino_ticket,
                            ct.id_ticket AS tieneCalificacion,

                        (SELECT COUNT(*) FROM archivos_adjuntos_ticket aa WHERE aa.id_ticket = t.id_ticket) AS cantidadArchivos,
                        COUNT(c.id) AS 'cantidadConversaciones'
                            FROM tickets t
                                JOIN estados_ticket         AS et       ON et.id        = t.id_estado
                                JOIN usuarios               AS us       ON us.id        = t.id_usuario
                                LEFT JOIN usuarios          AS tecnico  ON tecnico.id   = t.id_tecnico
                                LEFT JOIN proceso_tickets   AS pt       ON pt.id_ticket = t.id_ticket
                                LEFT JOIN conversaciones    AS c        ON c.id_ticket  = t.id_ticket
                                LEFT JOIN calificacion_tickett      AS ct ON ct.id_ticket = t.id_ticket

                                
                                    WHERE t.id_estado != 4"; // Excluye los tickets con id_estado igual a 4

        if ($estado !== null) {
            $sql .= " AND t.id_estado = '$estado'";
        }
        $sql .= " GROUP BY 
            t.id_ticket
            ORDER BY 
                pt.fecha_creacion_inicio DESC, 
                pt.hora_creacion_inicio DESC";

        return $bdato->consulta($sql);
    }

    // *************************************************************************              
    // *************************************************************************



    
    function obtenerColorEstado($id_estado)
    {
        $colores = [
            1 => '#D7DBDD', // Gris
            2 => '#F7DC6F', // Amarillo
            3 => '#F0B27A', // Naranja
            5 => '#82E0AA', // Verde
        ];

        return isset($colores[$id_estado]) ? $colores[$id_estado] : ''; // Devuelve el color correspondiente o una cadena vacía si no hay coincidencia
    }




    // *************************************************************************              
    // ***********************BENEFICIOS ****************************

    function beneficios_principales($idUsuarioSession)
    {
        $bdato = new MySQL("", "", "");
        $sql = "SELECT * FROM beneficios_principales";
        $resultado = $bdato->consulta($sql);

        if ($bdato->num_rows($resultado) > 0) {
            while ($row = $bdato->fetch_array($resultado)) {
                echo '<div class="col-xl-3 col-md-6 mb-3 position-relative">';
                echo '<div class="shadow-box p-3 mb-5 rounded imagen1" style="background-image: url(' . htmlspecialchars($row['imagen']) . ');">';
                echo '<a href="' . htmlspecialchars($row['url']) . '" style="text-decoration: none;">';
                echo '<h4 class="card-title card-title-white__usm text-center">' . htmlspecialchars($row['nombre']) . '</h4>';
                echo '</a>';
                echo '</div>';

                if (in_array($idUsuarioSession, [6, 7, 8])) {
                    echo '<div class="button-container" style="position: absolute; top: 10px; right: 10px; display: flex; flex-direction: column; gap: 5px;">';
                    echo '<div class="dropdown">';
                    echo '<button class="btn btn-secondary btn-sm" type="button" id="dropdownMenuButtonLight" data-bs-toggle="dropdown" aria-expanded="false">';
                    echo '<i class="bi bi-list" style="font-size:100%;"></i>';
                    echo '</button>';
                    echo '<ul class="dropdown-menu" aria-labelledby="dropdownMenuButtonLight">';
                    echo '<li><a class="dropdown-item text-danger" href="#" onclick="eliminarBeneficioPrincipal(' . $row['id'] . ')">Eliminar <i class="fas fa-trash"></i></a></li>';
                    echo '<li><a class="dropdown-item text-warning" href="#" onclick="modificarBeneficioPrincipal(' . $row['id'] . ')">Modificar <i class="fas fa-edit"></i></a></li>';
                    echo '</ul>';
                    echo '</div>';
                    echo '</div>';
                }

                echo '</div>';
            }
        }
    }

    function beneficios_carrusel()
    {
        $bdato = new MySQL("", "", ""); // Ajusta los valores de conexión
        $sql = "SELECT * FROM beneficios_destacados";
        $resultado = $bdato->consulta($sql);

        if ($bdato->num_rows($resultado) > 0) {
            $activeClass = ' active';
            while ($row = $bdato->fetch_array($resultado)) {
                echo '<div class="carousel-item' . $activeClass . '">';
                echo '<img src="' . htmlspecialchars($row['img']) . '" class="d-block w-100" alt="' . htmlspecialchars($row['titulo']) . '">';
                echo '<div class="carousel-caption d-flex flex-column align-items-center justify-content-center">';
                echo '<a href="' . htmlspecialchars($row['url']) . '" class="btn btn-warning mb-2">Ir al beneficio</a>';
                echo '<h5>' . htmlspecialchars($row['titulo']) . '</h5>';
                echo '<p>' . htmlspecialchars($row['descripcion']) . '</p>';
                echo '</div>';
                echo '</div>';
                $activeClass = ''; // Solo la primera iteración debe tener la clase 'active'
            }
        }
    }


    public function tecnicos($id_tecnico_actual = null)
    {
        // echo $id_tecnico_actual."*****";
        $bdato = new MySQL("", "", ""); // Asegúrate de usar los parámetros de conexión reales
        $sql = "SELECT id, nombre, apellido_paterno FROM usuarios WHERE id_area_trabajo = 1";
        $result = $bdato->consulta($sql);

        $output = "<select name='calificacion' onchange='asignacionTecnico(this.value, \"" . $id_tecnico_actual . "\")'>";
        $output .= "<option value=''>Seleccione un técnico...</option>"; // Opción por defecto

        while ($row = $bdato->fetch_array($result)) {
            $selected = ($row['id'] == $id_tecnico_actual) ? 'selected' : '';
            $output .= "<option value='" . htmlspecialchars($row['id']) . "' " . $selected . ">" . htmlspecialchars($row['nombre']) . " " . htmlspecialchars($row['apellido_paterno']) . "</option>";
        }

        $output .= "</select>";
        return $output;
    }
    // *************************************************************************              
    // ***********************BITAORA ****************************



    public function obtenerFechaEquipo($id_usuario)
    {
        // Conexión a la base de datos
        $bdato = new MySQL('', '', ''); // Asegúrate de usar los parámetros correctos

        // Consulta para obtener solo la fecha de creación del equipo
        $consulta = "SELECT fecha_creacion 
                     FROM equipos 
                     WHERE id_usuario = '" . $bdato->escape_string($id_usuario) . "'";

        // Ejecutar la consulta
        $resultado = $bdato->consulta($consulta);

        // Verificar si se encontró el dato
        if ($bdato->num_rows($resultado) > 0) {
            // Obtener la fecha de creación
            $row = $bdato->fetch_assoc($resultado);
            return $row['fecha_creacion'];  // Retornar la fecha
        } else {
            return null;  // Si no hay fecha, retornar null
        }
    }


        public function listarOtrosDispositivos()
        {
            $bdato = new MySQL('', '', ''); // Conecta a la base de datos
            $consulta = "
            SELECT 
                e.id_dispositivo,
                td.nombre_dispositivo AS tipo,
                e.marca,
                e.qr_code,
                e.modelo,
                e.n_serie,
                e.n_factura,
                e.asignado,
                e.proveedor,
                u.nombre AS usuario_nombre,
                u.apellido_paterno AS usuario_apellido,
                e.precio,
                e.observaciones
                FROM 
                    otros_dispositivos AS e
                LEFT JOIN 
                    tipos_dispositivos AS td ON td.id_tipo_dispositivo = e.tipo
                LEFT JOIN 
                    usuarios AS u ON u.id = e.asignado
                ORDER BY 
                    e.id_dispositivo;
            ";
        

            $resultado = $bdato->consulta($consulta);

            if ($bdato->num_rows($resultado) > 0) {
                $dispositivos = [];
                while ($row = $bdato->fetch_assoc($resultado)) {
                    $dispositivos[] = $row;
                }
                return $dispositivos; // Retornar el array con los dispositivos
            } else {
                return []; // Si no hay dispositivos, retornar un array vacío
            }
        }




    // 77con el join en usuarios 

    public function obtenerDispositivoPorId($id_dispositivo)
    {
        $bdato = new MySQL('', '', ''); // Conecta a la base de datos
    
        $consulta = "
        SELECT 
            d.id_dispositivo, 
            td.nombre_dispositivo AS tipo, 
            td.icono, 
            d.qr_code,
            d.marca, 
            d.modelo, 
            d.n_serie, 
            d.n_factura,
            d.proveedor,
            d.asignado, 
            d.fecha_compra,
            u.nombre AS usuario_nombre, 
            u.apellido_paterno AS usuario_apellido, 
            d.precio, 
            d.observaciones
            FROM 
                otros_dispositivos AS d
            LEFT JOIN 
                usuarios AS u ON d.asignado = u.id
            LEFT JOIN 
                tipos_dispositivos AS td ON d.tipo = td.id_tipo_dispositivo
            WHERE 
                d.id_dispositivo = $id_dispositivo
        ";
    
    
        $resultado = $bdato->consulta($consulta);
    
        if (!$resultado) {
            echo "Error al ejecutar la consulta: " . $bdato->getLastError();
            return null;
        }
    
        return $bdato->fetch_assoc($resultado);
    }
    




    public function listarEquipos()
    {
        $bdato = new MySQL('', '', ''); // Conexión a la base de datos
        $consulta = "
                    SELECT 
                        e.id_equipo, 
                        e.id_usuario, 
                        e.nombre_equipo, 
                        e.fabricante, 
                        e.qr_code,
                        e.producto, 
                        e.numero_serie, 
                        e.tipo_pc, 
                        CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_usuario,
                        atr.nombre_area,
                        ec.valor_equipo,
                        ec.proveedor,
                        ec.numero_factura,
                        ec.fecha_compra,
                        ec.observacion,
                        ea.equipo_modelo        AS modelo_almacenamiento,
                        ea.equipo_capacidad     AS capacidad_almacenamiento,
                        ea.equipo_tamano        AS tamano_almacenamiento,
                        es.windows,
                        es.office,
                        es.antivirus,
                        u.nombre,
                        u.apellido_paterno,
                        u.apellido_materno,
                        ep.equipo_fabricante AS fabricante_procesador,
                        ep.equipo_modelo AS modelo_procesador,
                        ep.equipo_velocidad AS velocidad_procesador,
                        (SELECT SUM(tamano_memoria) FROM equipo_memoria WHERE id_equipo = e.id_equipo) AS tamano_total_memoria,
                        GROUP_CONCAT(em.designacion_memoria ORDER BY em.orden_memoria) AS designacion_memorias,
                        GROUP_CONCAT(em.formato_memoria ORDER BY em.orden_memoria) AS formatos_memorias,
                        GROUP_CONCAT(em.tipo_memoria ORDER BY em.orden_memoria) AS tipos_memorias,
                        GROUP_CONCAT(em.tamano_memoria ORDER BY em.orden_memoria) AS tamanos_memorias,
                        GROUP_CONCAT(em.frecuencia_memoria ORDER BY em.orden_memoria) AS frecuencias_memorias,
                        GROUP_CONCAT(em.marca_memoria ORDER BY em.orden_memoria) AS marcas_memorias,
                        GROUP_CONCAT(em.orden_memoria ORDER BY em.orden_memoria) AS orden_memorias,
                        GROUP_CONCAT(mo.modelo_monitor ORDER BY mo.orden_monitor) AS modelos_monitores,
                        GROUP_CONCAT(mo.orden_monitor ORDER BY mo.orden_monitor) AS orden_monitores
                    FROM equipos e
                    LEFT JOIN usuarios              u       ON e.id_usuario             = u.id  
                    LEFT JOIN area_trabajo          atr     ON atr.id_area              = u.id_area_trabajo
                    LEFT JOIN equipos_compra        ec      ON ec.id_equipo             = e.id_equipo
                    LEFT JOIN equipo_almacenamiento ea      ON ea.id_equipo             = e.id_equipo
                    LEFT JOIN equipo_memoria        em      ON em.id_equipo             = e.id_equipo
                    LEFT JOIN equipo_procesador     ep      ON ep.id_equipo             = e.id_equipo
                    LEFT JOIN equipo_software       es      ON es.id_equipo             = e.id_equipo
                    LEFT JOIN equipo_monitor        mo      ON mo.id_equipo             = e.id_equipo
                    GROUP BY e.id_equipo";


        // echo $consulta."********";




        $resultado = $bdato->consulta($consulta);

        if (!$resultado) {
            echo "Error al ejecutar la consulta: " . $bdato->getLastError();
            return [];
        }

        $equipos = [];
        while ($fila = $bdato->fetch_assoc($resultado)) {
            $equipos[] = $fila;
        }

        return $equipos;
    }

    public function obtenerEquipoPorId($id_equipo)
    {
        $bdato = new MySQL('', '', ''); // Conexión a la base de datos
        $id_equipo = $bdato->escape_string($id_equipo); // Escapa el ID para prevenir inyección SQL
        $consulta = "
            SELECT 
                e.id_equipo, 
                e.id_usuario, 
                e.nombre_equipo, 
                e.fabricante, 
                e.producto, 
                e.qr_code,
                e.numero_serie, 
                e.tipo_pc, 
                CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo_usuario,
                atr.nombre_area,
                ec.valor_equipo,
                ec.proveedor,
                ec.fecha_compra,
                ec.numero_factura,
                ec.observacion,
                ea.equipo_modelo            AS modelo_almacenamiento,
                ea.equipo_capacidad         AS capacidad_almacenamiento,
                ea.equipo_tamano            AS tamano_almacenamiento,              
                es.windows,
                es.office,
                es.antivirus,        
                SUM(em.tamano_memoria)      AS total_tamano_memoria,  -- Suma de los tamaños de memoria
                COUNT(em.id_memoria)        AS cantidad_memorias,     -- Cantidad de memorias asociadas
                em.designacion_memoria,
                em.formato_memoria,
                em.tipo_memoria,
                em.tamano_memoria,
                em.frecuencia_memoria,
                em.marca_memoria,
                ep.equipo_fabricante AS fabricante_procesador,
                ep.equipo_modelo AS modelo_procesador,
                ep.equipo_velocidad AS velocidad_procesador
            FROM equipos e
            LEFT JOIN usuarios              u       ON e.id_usuario = u.id  
            LEFT JOIN area_trabajo          atr     ON atr.id_area  = u.id_area_trabajo
            LEFT JOIN equipos_compra        ec      ON ec.id_equipo = e.id_equipo
            LEFT JOIN equipo_almacenamiento ea      ON ea.id_equipo = e.id_equipo
            LEFT JOIN equipo_memoria        em      ON em.id_equipo = e.id_equipo
            LEFT JOIN equipo_procesador     ep      ON ep.id_equipo = e.id_equipo
            LEFT JOIN equipo_software       es      ON es.id_equipo = e.id_equipo
            WHERE e.id_equipo = '$id_equipo'
        ";

        $resultado = $bdato->consulta($consulta);

        if (!$resultado) {
            echo "Error al ejecutar la consulta: " . $bdato->getLastError();
            return null;
        }

        return $bdato->fetch_assoc($resultado);
    }

    public function listarUsuarios()
    {
        $bdato = new MySQL('', '', '');

        $consulta = "
           SELECT 
            u.id,
            u.nombre,
            u.apellido_paterno,
            u.apellido_materno,
            CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo_usuario,
            u.email,
            u.telefono
        FROM usuarios u
        LEFT JOIN equipos e ON u.id = e.id_usuario
        ORDER BY u.nombre ASC;

        ";
        $resultado = $bdato->consulta($consulta);

        if ($bdato->num_rows($resultado) > 0) {
            $usuarios = [];
            while ($row = $bdato->fetch_assoc($resultado)) {
                $usuarios[] = $row;
            }
            return $usuarios; // Retornar el array con los usuarios
        } else {
            return [];  // Si no hay usuarios, retornar un array vacío
        }
    }

    public function obtenerEstadisticasUsuarios()
    {
        $bdato = new MySQL('', '', '');
    
        // Obtener los usuarios base
        $consultaUsuarios = "SELECT id, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) AS nombre_completo_usuario FROM usuarios ORDER BY nombre ASC";
        $resultadoUsuarios = $bdato->consulta($consultaUsuarios);
    
        $usuarios = [];
    
        while ($row = $bdato->fetch_assoc($resultadoUsuarios)) {
            $idUsuario = $row['id'];
    
            // Verificar si el usuario está activo (sin fecha_salida)
            $consultaActivo = "SELECT COUNT(*) AS activo FROM log_sesiones WHERE id_usuario = $idUsuario AND fecha_salida IS NULL";
            $activo = $bdato->fetch_assoc($bdato->consulta($consultaActivo))['activo'] > 0;
    
            // Últimas 5 entradas
            $consultaEntradas = "SELECT fecha_ingreso AS fecha, hora_ingreso AS hora, ip FROM log_sesiones WHERE id_usuario = $idUsuario ORDER BY fecha_ingreso DESC, hora_ingreso DESC LIMIT 5";
            $entradas = [];
            $resultadoEntradas = $bdato->consulta($consultaEntradas);
            while ($entrada = $bdato->fetch_assoc($resultadoEntradas)) {
                $entradas[] = $entrada;
            }
    
            // Tickets terminados
            $consultaTerminados = "SELECT COUNT(*) AS total FROM tickets WHERE id_usuario = $idUsuario AND id_estado = 5";
            $ticketsTerminados = $bdato->fetch_assoc($bdato->consulta($consultaTerminados))['total'];
    
            // Tickets ingresados
            $consultaIngresados = "SELECT COUNT(*) AS total FROM tickets WHERE id_usuario = $idUsuario AND id_estado = 1";
            $ticketsIngresados = $bdato->fetch_assoc($bdato->consulta($consultaIngresados))['total'];
    
            // Mensajes enviados
            $consultaMensajes = "SELECT COUNT(*) AS total FROM mensajes WHERE de = $idUsuario";
            $mensajes = $bdato->fetch_assoc($bdato->consulta($consultaMensajes))['total'];
    
            $usuarios[] = [
                'id' => $idUsuario,
                'nombre_completo_usuario' => $row['nombre_completo_usuario'],
                'activo' => $activo,
                'ultimas_entradas' => $entradas,
                'tickets_terminados' => $ticketsTerminados,
                'tickets_ingresados' => $ticketsIngresados,
                'mensajes' => $mensajes
            ];
        }
    
        return $usuarios;
    }
    
    public function listarTiposDispositivos() {
        $bdato = new MySQL('', '', ''); // Conexión a la base de datos

        // Consulta para obtener los tipos de dispositivos
        $consulta = "SELECT id_tipo_dispositivo, nombre_dispositivo FROM tipos_dispositivos";
        
        // Ejecutar la consulta
        $resultado = $bdato->consulta($consulta);

        // Verificar si hay resultados
        if ($resultado && $bdato->num_rows($resultado) > 0) {
            $dispositivos = [];
            
            // Recorrer los resultados y almacenarlos en el array
            while ($row = $bdato->fetch_assoc($resultado)) {
                $dispositivos[] = $row;
            }

            return $dispositivos; // Retornar el array con los dispositivos
        } else {
            // Registrar en el log que no hay datos encontrados
            error_log("No se encontraron tipos de dispositivos o la consulta falló.");
            return [];  // Si no hay datos, retornar un array vacío
        }
    }

    

    public function listar_mensajes($idUsuarioSession)
    {
        $bdato = new MySQL('', '', ''); // Cambia los parámetros de conexión según sea necesario

        $consulta = "
            SELECT 
                id, 
                mensaje, 
                para, 
                de, 
                tiempo, 
                leido, 
                urgente, 
                fecha, 
                hora, 
                eliminado 
            FROM mensajes
            WHERE eliminado = 0 
            ORDER BY fecha, hora;
        ";
        $resultado = $bdato->consulta($consulta);

        if ($bdato->num_rows($resultado) > 0) {
            $mensajes = [];
            while ($row = $bdato->fetch_assoc($resultado)) {
                $mensajes[] = $row;
            }
            return $mensajes; // Retornar el array con los mensajes
        } else {
            return [];  // Si no hay mensajes, retornar un array vacío
        }
    }




    
    public function listarTodosDispositivosqr() {
        $bdato = new MySQL('', '', ''); // Conexión a la base de datos
    
        $consulta = "
            SELECT 
                d.id_dispositivo,
                d.tipo,
                d.marca,
                d.modelo,
                d.n_serie,
                d.n_factura,
                d.asignado,
                u.nombre AS usuario_nombre,
                u.apellido_paterno AS usuario_apellido,
                d.precio,
                d.fecha_compra,
                d.observaciones,
                d.qr_code
            FROM 
                otros_dispositivos AS d
            LEFT JOIN 
                usuarios AS u ON d.asignado = u.id
            ORDER BY 
                d.id_dispositivo;
        ";
    
        $resultado = $bdato->consulta($consulta);
    
        $dispositivos = [];
        while ($row = $bdato->fetch_assoc($resultado)) {
            $dispositivos[] = $row;
        }
    
        return $dispositivos; // Retornar el array con todos los dispositivos
    }
    
    
        public function convertirArrayUtf8($array) {
            if (!is_array($array)) {
                return $array;
            }
        
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = convertirArrayUtf8($value);
                } else {
                    $array[$key] = mb_convert_encoding($value, 'UTF-8', 'auto');
                }
            }
        
            return $array;
        }

        // GRAFICOS 
        public function cargarEstadosTicket($idUsuario)
        {
            $bdato = new MySQL('', '', ''); // Conexión a la base de datos
        
            $consulta = "
                SELECT 
                    e.id AS id_estado,
                    e.nombre AS estado,
                    e.color AS color_estado,    
                    COALESCE(COUNT(t.id_ticket), 0) AS cantidad_tickets 
                FROM 
                    estados_ticket e
                LEFT JOIN 
                    tickets t ON e.id = t.id_estado AND t.id_tecnico = $idUsuario
                GROUP BY 
                    e.id, e.nombre, e.color 
                ORDER BY 
                    e.orden ASC;
            ";

            // echo $consulta;
        
            $resultado = $bdato->consulta($consulta);
        
            return $this->procesarResultados($resultado, $bdato);
        }

        
        


        public function cargarEstadosTicketUsuario($idUsuario)
        {
            $bdato = new MySQL('', '', ''); // Conexión a la base de datos
        
            $consulta = "
                SELECT 
                    e.id AS id_estado,
                    e.nombre AS estado,
                    e.color AS color_estado,    
                    COALESCE(COUNT(t.id_ticket), 0) AS cantidad_tickets 
                FROM 
                    estados_ticket e
                LEFT JOIN 
                    tickets t ON e.id = t.id_estado AND t.id_usuario = $idUsuario
                GROUP BY 
                    e.id, e.nombre, e.color 
                ORDER BY 
                    e.orden ASC;
            ";

            // echo $consulta;
        
            $resultado = $bdato->consulta($consulta);
        
            return $this->procesarResultados($resultado, $bdato);
        }





        // Nueva función para obtener TODOS los tickets sin filtro
        public function cargarEstadosTicketTodos()
        {
            $bdato = new MySQL('', '', ''); // Conexión a la base de datos
        
            $consulta = "
                SELECT 
                    e.id AS id_estado,
                    e.nombre AS estado,
                    e.color AS color_estado,    
                    COUNT(t.id_ticket) AS cantidad_tickets 
                FROM 
                    estados_ticket e
                LEFT JOIN 
                    tickets t ON e.id = t.id_estado
                GROUP BY 
                    e.id, e.nombre, e.color 
                ORDER BY 
                    e.orden ASC;
            ";
        
            $resultado = $bdato->consulta($consulta);
        
            return $this->procesarResultados($resultado, $bdato);
        }
        
        // Función auxiliar para evitar repetir código
        private function procesarResultados($resultado, $bdato)
        {
            if ($bdato->num_rows($resultado) > 0) {
                $estados = [];
                while ($row = $bdato->fetch_assoc($resultado)) {
                    $estados[] = $row;
                }
                return $estados;
            } else {
                return [];
            }
        }
        


        // funcio para mostrar el tosta tcon los estados del ticket en ticket_administrador
   
        function mostrarToastTicket($row, $nombreUsuarioTecnico)
        {
            ob_start();
            
            $estados = [
                "Creado"     => [$row['fecha_creacion_inicio'], $row['hora_creacion_inicio']],
                "Asignado"   => [$row['fecha_asignacion_tecnico'], $row['hora_asignacion_tecnico']],
                "En proceso" => [$row['fecha_comienzo_ticket'], $row['hora_comienzo_ticket']],
                "Finalizado" => [$row['fecha_termino_ticket'], $row['hora_termino_ticket']]
            ];
        
            ?>
        <script>
        function verLineaTiempo<?= $row['id_ticket']; ?>() {
            let contenido = `
                                <h3 class="text-center">Cronología del Ticket</h3>
                                <div class="text-center text-primary mb-2">
                                    <?= empty($row['id_tecnico']) ? '<div class="text-danger">Técnico no asignado</div>' : 'Técnico: ' . htmlspecialchars($nombreUsuarioTecnico); ?>
                                </div>
                                <div class="timeline-container">
                                    <div class="timeline-line"></div>
                                    <div class="timeline-items">
                                        <?php foreach ($estados as $estado => [$fecha, $hora]) { ?>
                                            <div class="timeline-item">
                                                <div class="timeline-dot <?= empty($fecha) ? 'inactive' : ''; ?>"></div>
                                                <div class="timeline-content">
                                                    <span class="timeline-label"><?= $estado; ?></span><br>
                                                    <span class="timeline-date"><?= $fecha ?: '--/--/----'; ?></span><br>
                                                    <span class="timeline-time"><?= $hora ?: '--:--'; ?></span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            `;
        
            Swal.fire({
                html: contenido,
                showConfirmButton: false,
                showCloseButton: true,
                width: 600,
                customClass: {
                    popup: 'cuerpo_modal_guardar'
                }
            });
        }
        </script>
        
        <button onclick="verLineaTiempo<?= $row['id_ticket']; ?>()" class="btn btn-primary btn-sm fixed-width-button"
            data-bs-toggle="popover" data-bs-placement="top" title="Ver Línea de Tiempo"
            data-bs-content="Aquí podrás ver una línea de tiempo de los estados del ticket.">
            <?= htmlspecialchars($row['nombreEstado']); ?>
        </button>
        <?php
        
            return ob_get_clean();
        }
        
        
        
        //*****************************************************MODAL BIENVENIDA
        
         public function mostrarBotonesPerfiles($id_usuario)
            {
                $db = new MySQL("", "", "");
            
                // Obtener nombre del usuario
                $sql_nombre = "SELECT nombre, apellido_paterno FROM usuarios WHERE id = $id_usuario";
                $res_nombre = $db->consulta($sql_nombre);
                $nombre = "Usuario";
                $apellido = "";
                if ($row = $db->fetch_assoc($res_nombre)) {
                    $nombre = $row['nombre'];
                    $apellido = $row['apellido_paterno'];
                }
            
                // Obtener perfiles del usuario
                $sql_perfiles = "SELECT id_perfil FROM usuario_perfil WHERE id_usuario = $id_usuario";
                $res_perfiles = $db->consulta($sql_perfiles);
            
                $perfilNombres = [
                    1 => "Usuario",
                    2 => "Tecnico",
                    3 => "Administrador"
                ];
            
                $perfiles = [];
                while ($row = $db->fetch_assoc($res_perfiles)) {
                    $id_perfil = $row['id_perfil'];
                    if (isset($perfilNombres[$id_perfil])) {
                        $perfiles[] = [
                            'id' => $id_perfil,
                            'nombre' => $perfilNombres[$id_perfil]
                        ];
                    }
                }
            
                // Si solo tiene un perfil
                if (count($perfiles) === 1) {
                    $perfil = $perfiles[0];
                    $contenidoHTML = "";
                    $perfilJS = strtolower($perfil['nombre']);
                    if ($perfilJS === 'administrador') {
                        $perfilJS = 'admin';
                    }
            
                    // Consulta según perfil
                    if ($perfil['id'] == 1) {
                        $sql_tickets = "SELECT t.id_ticket, t.asunto, e.color 
                                        FROM tickets t 
                                        JOIN estados_ticket e ON t.id_estado = e.id 
                                        WHERE t.id_estado = 1";
                    } elseif ($perfil['id'] == 2) {
                        $sql_tickets = "SELECT t.id_ticket, t.asunto, e.color 
                                        FROM tickets t 
                                        JOIN estados_ticket e ON t.id_estado = e.id 
                                        WHERE t.id_estado = 2 AND t.id_tecnico = $id_usuario";
                    } else { // Usuario
                        $sql_tickets = "SELECT t.id_ticket, t.asunto, e.color 
                                        FROM tickets t 
                                        JOIN estados_ticket e ON t.id_estado = e.id 
                                        WHERE t.id_estado = 5 AND t.id_usuario = $id_usuario";
                    }
            
                    $res_tickets = $db->consulta($sql_tickets);
                    $cantidad = $db->num_rows($res_tickets);
            
                    if ($cantidad > 0) {
                        $rowColor = $db->fetch_assoc($res_tickets);
                        $color = $rowColor['color'];
                        mysqli_data_seek($res_tickets, 0);
            
                        $contenidoHTML .= "<div style='background-color: $color; padding: 15px; border-radius: 5px; margin-bottom: 10px;'>";
                        $contenidoHTML .= "<ul style='padding-left: 1.2rem;'>";
                        while ($ticket = $db->fetch_assoc($res_tickets)) {
                            $id = $ticket['id_ticket'];
                            $asunto = htmlspecialchars($ticket['asunto']);
                            $contenidoHTML .= "<li><b>Ticket #$id:</b> $asunto</li>";
                        }
                        $contenidoHTML .= "</ul></div>";
                    } else {
                        $contenidoHTML .= "<div style='background-color: #E6E6FA; padding: 15px; border-radius: 5px;'>No hay tickets pendientes</div>";
                    }
            
                    echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: '<div class=\"alert alert-dark\" role=\"alert\">¡Bienvenido, <br> $nombre $apellido!</div>',
                                html: `$contenidoHTML`,
                                showCancelButton: false,
                                confirmButtonText: 'Ok',
                                customClass: { popup: 'cuerpo_modal_guardar' }
                            }).then(() => {
                                cambiarPerfil('$perfilJS'); // aplicar filtro automáticamente
                            });
                        });
                    </script>";
                    return;
                }
            
                // Si tiene múltiples perfiles
                $tabs = "";
                $contenidos = "";
                $botones = "";
            
                foreach ($perfiles as $index => $perfil) {
                    $activo = ($index === 0) ? 'active' : '';
                    $nombreLower = strtolower($perfil['nombre']);
            
                    if ($perfil['id'] == 1) {
                        $sql_tickets = "SELECT t.id_ticket, t.asunto, e.color 
                                        FROM tickets t 
                                        JOIN estados_ticket e ON t.id_estado = e.id 
                                        WHERE t.id_estado = 1";
                    } elseif ($perfil['id'] == 2) {
                        $sql_tickets = "SELECT t.id_ticket, t.asunto, e.color 
                                        FROM tickets t 
                                        JOIN estados_ticket e ON t.id_estado = e.id 
                                        WHERE t.id_estado = 2 AND t.id_tecnico = $id_usuario";
                    } else {
                        $sql_tickets = "SELECT t.id_ticket, t.asunto, e.color 
                                        FROM tickets t 
                                        JOIN estados_ticket e ON t.id_estado = e.id 
                                        WHERE t.id_estado = 5 AND t.id_usuario = $id_usuario";
                    }
            
                    $res_tickets = $db->consulta($sql_tickets);
                    $cantidad = $db->num_rows($res_tickets);
            
                    $contenidoHTML = "";
            
                    if ($cantidad > 0) {
                        $rowColor = $db->fetch_assoc($res_tickets);
                        $color = $rowColor['color'];
                        mysqli_data_seek($res_tickets, 0);
            
                        $contenidoHTML .= "<div style='background-color: $color; padding: 15px; border-radius: 5px; margin-bottom: 10px;'>";
                        $contenidoHTML .= "<ul style='padding-left: 1.2rem;'>";
                        while ($ticket = $db->fetch_assoc($res_tickets)) {
                            $id = $ticket['id_ticket'];
                            $asunto = htmlspecialchars($ticket['asunto']);
                            $contenidoHTML .= "<li><b>Ticket #$id:</b> $asunto</li>";
                        }
                        $contenidoHTML .= "</ul></div>";
                    } else {
                        $contenidoHTML .= "<div style='background-color: #E6E6FA; padding: 15px; border-radius: 5px;'>No hay tickets pendientes</div>";
                    }
            
                    $tabs .= "<li class='nav-item' role='presentation'>
                                <button class='nav-link $activo' id='tab-$nombreLower' data-bs-toggle='tab' data-bs-target='#contenido-$nombreLower' type='button' role='tab'>
                                    {$perfil['nombre']}
                                </button>
                              </li>";
            
                    $contenidos .= "<div class='tab-pane fade show $activo' id='contenido-$nombreLower' role='tabpanel'>
                                        $contenidoHTML
                                    </div>";
            
                    $botones .= "<button class='btn btn-outline-primary me-2' onclick=\"cambiarPerfil('$nombreLower')\">{$perfil['nombre']}</button>";
                }
            
                $html_modal = "
                    <ul class='nav nav-tabs' id='perfilTabs' role='tablist'>
                        $tabs
                    </ul>
                    <div class='tab-content mt-3'>
                        $contenidos
                    </div>
                    <div class='text-center mt-3'>
                        <p><b>Selecciona un perfil:</b></p>
                        $botones
                    </div>
                ";
            
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: '<div class=\"alert alert-dark\" role=\"alert\">¡Bienvenido, <br> $nombre $apellido!</div>',
                            html: `$html_modal`,
                            showCancelButton: false,
                            confirmButtonText: 'Cerrar',
                            customClass: { popup: 'cuerpo_modal_guardar', confirmButton: 'btn-modal-eliminar' }
                        });
                    });
                </script>";
            }
            
            
            
            
        public function mostrarSoloBotonesPerfiles($id_usuario)
    {
        $db = new MySQL("", "", "");
    
        // Obtener nombre del usuario
        $sql_nombre = "SELECT nombre, apellido_paterno FROM usuarios WHERE id = $id_usuario";
        $res_nombre = $db->consulta($sql_nombre);
        $nombre = "Usuario";
        $apellido = "";
        if ($row = $db->fetch_assoc($res_nombre)) {
            $nombre = $row['nombre'];
            $apellido = $row['apellido_paterno'];
        }
    
        // Obtener perfiles del usuario
        $sql_perfiles = "SELECT id_perfil FROM usuario_perfil WHERE id_usuario = $id_usuario";
        $res_perfiles = $db->consulta($sql_perfiles);
    
        $perfilNombres = [
            1 => "Usuario",
            2 => "Tecnico",
            3 => "Administrador"
        ];
    
        $perfilIconos = [
            1 => "<i class='bi bi-person-fill me-1'></i>",    // Usuario
            2 => "<i class='bi bi-tools me-1'></i>",          // Técnico
            3 => "<i class='bi bi-shield-lock me-1'></i>"     // Admin
        ];
    
        $perfiles = [];
        while ($row = $db->fetch_assoc($res_perfiles)) {
            $id_perfil = $row['id_perfil'];
            if (isset($perfilNombres[$id_perfil])) {
                $perfiles[] = [
                    'id' => $id_perfil,
                    'nombre' => $perfilNombres[$id_perfil],
                    'icono' => $perfilIconos[$id_perfil]
                ];
            }
        }
    
        // Si solo tiene un perfil, no mostrar nada
        if (count($perfiles) <= 1) {
            return;
        }
    
        // Generar botones con íconos
        $botones = "";
        foreach ($perfiles as $perfil) {
            $nombreLower = strtolower($perfil['nombre']);
            if ($nombreLower === 'administrador') {
                $nombreLower = 'admin';
            }
            $botones .= "<button class='btn btn-outline-primary me-2 mb-2' onclick=\"cambiarPerfil('$nombreLower')\">
                            {$perfil['icono']} {$perfil['nombre']}
                         </button>";
        }
    
        // HTML del modal
        $html = "
            <div class='text-center mt-2'>
                <p><b>Selecciona un perfil:</b></p>
                $botones
                <p class='mt-3 text-muted' style='font-size: 0.9rem;'>
                    El perfil seleccionado determinará qué tickets puedes ver y qué acciones puedes realizar en el sistema.
                </p>
            </div>
        ";
    
        // Modal con bienvenida
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '<div class=\"alert alert-dark mb-2\">¡Bienvenido, $nombre $apellido!</div>',
                    html: `$html`,
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: false, // 👈 esta línea evita que se cierre al hacer clic fuera
    
                    customClass: {
                        popup: 'cuerpo_modal_guardar'
                    }
                });
            });
        </script>";
    }

        
        
        public function bienvenidaUnificada($idUsuarioSession) {
            $db = new MySQL("", "", "");
        
            // Obtener nombre y apellido
            $sql_usuario = "SELECT nombre, apellido_paterno FROM usuarios WHERE id = $idUsuarioSession";
            $res_usuario = $db->consulta($sql_usuario);
            $nombre = "Usuario";
            $apellido = "";
            if ($fila = $db->fetch_assoc($res_usuario)) {
                $nombre = $fila['nombre'];
                $apellido = $fila['apellido_paterno'];
            }
        
            // Obtener perfiles
            $sql_perfiles = "SELECT p.id_perfil, p.nombre FROM usuario_perfil up
                             JOIN perfiles p ON up.id_perfil = p.id_perfil
                             WHERE up.id_usuario = $id_usuario
                             ORDER BY FIELD(p.id_perfil, 3, 2, 1)";
            $res_perfiles = $db->consulta($sql_perfiles);
            $perfiles = [];
        
            while ($fila = $db->fetch_assoc($res_perfiles)) {
                $perfil_id = $fila['id_perfil'];
                $perfil_nombre = strtolower($fila['nombre']);
                $perfiles[$perfil_nombre] = [
                    'id_perfil' => $perfil_id,
                    'html' => ''
                ];
        
                // Obtener tickets por perfil (ejemplo: usuario → id_usuario = $id_usuario)
                $sql_tickets = "SELECT 
                        SUM(CASE WHEN id_estado = 3 THEN 1 ELSE 0 END) AS en_proceso,
                        SUM(CASE WHEN id_estado = 5 THEN 1 ELSE 0 END) AS terminados
                    FROM tickets
                    WHERE id_usuario = $id_usuario"; // Aquí podrías filtrar más según perfil si deseas
                $res_tickets = $db->consulta($sql_tickets);
                $en_proceso = 0;
                $terminados = 0;
        
                if ($fila_t = $db->fetch_assoc($res_tickets)) {
                    $en_proceso = $fila_t['en_proceso'];
                    $terminados = $fila_t['terminados'];
                }
        
                $html = "";
        
                if ($en_proceso == 0 && $terminados == 0) {
                    $html .= "<p class='text-center'>No tenemos nada que recordarte por ahora.</p>";
                } else {
                    if ($en_proceso > 0) {
                        $html .= "<div style='background-color:#F0B27A; padding:12px; border-radius:6px; margin-bottom:10px'>
                                    Tienes <b>$en_proceso</b> tickets en proceso.
                                  </div>";
                    }
                    if ($terminados > 0) {
                        $html .= "<div style='background-color:#82E0AA; padding:12px; border-radius:6px;'>
                                    Tienes <b>$terminados</b> tickets finalizados.
                                  </div>";
                    }
                }
        
                $perfiles[$perfil_nombre]['html'] = $html;
            }
        
            // Construir contenido para el modal
            $tabs = "";
            $tab_content = "";
            $botones = "";
            $primero = true;
        
            if (count($perfiles) > 1) {
                // Pestañas
                $tabs .= "<ul class='nav nav-tabs justify-content-center' id='tabsPerfil' role='tablist'>";
                $tab_content .= "<div class='tab-content mt-3'>";
                foreach ($perfiles as $nombre => $data) {
                    $active = $primero ? "active" : "";
                    $show = $primero ? "show active" : "";
                    $nombreC = ucfirst($nombre);
                    $tabs .= "<li class='nav-item'>
                                <a class='nav-link $active' id='tab-$nombre' data-bs-toggle='tab' href='#contenido-$nombre' role='tab'>$nombreC</a>
                              </li>";
                    $tab_content .= "<div class='tab-pane fade $show' id='contenido-$nombre' role='tabpanel'>
                                        {$data['html']}
                                     </div>";
                    $botones .= "<button class='btn btn-outline-primary m-1' onclick=\"cambiarPerfil('$nombre'); document.getElementById('textoPerfilActual').innerText = '$nombreC'; Swal.close();\">$nombreC</button>";
                    $primero = false;
                }
                $tabs .= "</ul>";
                $tab_content .= "</div>";
            } else {
                // Solo un perfil
                foreach ($perfiles as $nombre => $data) {
                    $tab_content .= $data['html'];
                    $botones = "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            cambiarPerfil('$nombre');
                            document.getElementById('textoPerfilActual').innerText = '" . ucfirst($nombre) . "';
                        });
                    </script>";
                }
            }
        
            // Construir modal completo
            $modal = "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: '<div class=\"alert alert-dark\" role=\"alert\">¡Bienvenido,<br>$nombre $apellido!</div>',
                        html: `$tabs $tab_content
                               <div class='text-center mt-3'>" . (count($perfiles) > 1 ? "<p><strong>Selecciona un perfil:</strong><br>$botones</p>" : "") . "</div>`,
                        showCancelButton: false,
                        showConfirmButton: " . (count($perfiles) == 1 ? "true" : "false") . ",
                        confirmButtonText: 'Ok',
                        allowOutsideClick: false,
                        customClass: {
                            popup: 'cuerpo_modal_guardar'
                        }
                    });
                });
            </script>";
        
            echo $modal;
        }


        public function alertasBienvenida($id_usuario,$AreaTrabajo) {
            $db = new MySQL("", "", "");
        
            // Obtener el nombre del usuario
            $sql_usuario = "SELECT nombre, apellido_paterno FROM usuarios WHERE id =$id_usuario";
            $resultado_usuario = $db->consulta($sql_usuario);
            $nombre = "Usuario";
            $apellido_paterno = "";
        
            if ($fila_usuario = $db->fetch_assoc($resultado_usuario)) {
                $nombre = $fila_usuario['nombre'];
                $apellido_paterno = $fila_usuario['apellido_paterno'];
            }
        
            // Obtener resumen de tickets
            $sql_tickets = "SELECT 
                    SUM(CASE WHEN id_estado = 3 THEN 1 ELSE 0 END) AS total_en_proceso,
                    SUM(CASE WHEN id_estado = 5 THEN 1 ELSE 0 END) AS total_terminados
                FROM tickets
                WHERE id_usuario = $id_usuario";
            $resultado_tickets = $db->consulta($sql_tickets);
            $total_en_proceso = 0;
            $total_terminados = 0;
        
            if ($fila_tickets = $db->fetch_assoc($resultado_tickets)) {
                $total_en_proceso = $fila_tickets['total_en_proceso'];
                $total_terminados = $fila_tickets['total_terminados'];
            }
        
            // Colores de estado
            $sql_colores = "SELECT id, color FROM estados_ticket WHERE id IN (3, 5)";
            $resultado_colores = $db->consulta($sql_colores);
            $colores = [3 => "#F0B27A", 5 => "#82E0AA"];
            while ($fila_color = $db->fetch_assoc($resultado_colores)) {
                $colores[$fila_color['id']] = $fila_color['color'];
            }
        
            // Perfiles disponibles
            $sql_perfiles = "SELECT p.nombre FROM usuario_perfil up 
                             JOIN perfiles p ON up.id_perfil = p.id_perfil 
                             WHERE up.id_usuario = $id_usuario 
                             ORDER BY FIELD(p.id_perfil, 3, 2, 1)";
            $res_perfiles = $db->consulta($sql_perfiles);
            $perfiles = [];
            while ($fila = $db->fetch_assoc($res_perfiles)) {
                $perfiles[] = strtolower($fila['nombre']); // admin, tecnico, usuario
            }
        
            // Construir contenido HTML del modal
            $mensaje_tickets = "";
        
            if ($total_en_proceso > 0) {
                $mensaje_tickets .= "
                    <div style='background-color: {$colores[3]}; padding: 15px; border-radius: 5px; margin-bottom: 10px;'>
                        Tienes <b>{$total_en_proceso}</b> tickets que están siendo resueltos.
                    </div>";
            }
        
            if ($total_terminados > 0) {
                $mensaje_tickets .= "
                    <div style='background-color: {$colores[5]}; padding: 15px; border-radius: 5px; margin-bottom: 10px;'>
                        Tienes <b>{$total_terminados}</b> tickets finalizados.
                    </div>";
            }
        
            // Agregar botones de perfil si hay más de uno
            if (count($perfiles) > 1) {
                $mensaje_tickets .= "<div style='margin-top: 20px; text-align: center;'>
                    <p><strong>Selecciona un perfil para continuar:</strong></p>";
                foreach ($perfiles as $perfil) {
                    $nombrePerfil = ucfirst($perfil);
                    $mensaje_tickets .= "
                        <button class='btn btn-outline-primary m-1' onclick=\"cambiarPerfil('$perfil'); document.getElementById('textoPerfilActual').innerText = '$nombrePerfil'; Swal.close();\">
                            $nombrePerfil
                        </button>";
                }
                $mensaje_tickets .= "</div>";
            } elseif (count($perfiles) == 1) {
                // Si solo tiene un perfil, activarlo automáticamente
                $perfil = $perfiles[0];
                $nombrePerfil = ucfirst($perfil);
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        cambiarPerfil('$perfil');
                        document.getElementById('textoPerfilActual').innerText = '$nombrePerfil';
                    });
                </script>";
            }
        
            // Mostrar el modal si hay contenido
            if (!empty($mensaje_tickets)) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: '<div class=\"alert alert-dark\" role=\"alert\">¡Bienvenido, <br> $nombre $apellido_paterno!</div>',
                            html: `$mensaje_tickets`,
                            showCancelButton: false,
                            showConfirmButton: " . (count($perfiles) > 1 ? "false" : "true") . ",
                            confirmButtonText: 'Ok',
                            customClass: {
                                popup: 'cuerpo_modal_guardar'
                            }
                        });
                    });
                </script>";
            }
        }

        function bienvenidoUsuario($id_usuario) {
            $db = new MySQL("", "", ""); 
        
            $sql_usuario = "SELECT nombre, apellido_paterno FROM usuarios WHERE id =$id_usuario";
            $resultado_usuario = $db->consulta($sql_usuario);
                echo $sql_usuario;
            $nombre = "Usuario";
            $apellido_paterno = "";
        
            if ($fila_usuario = $db->fetch_assoc($resultado_usuario)) {
                $nombre = $fila_usuario['nombre'];
                $apellido_paterno = $fila_usuario['apellido_paterno'];
            }
        
            // Consultar la suma de los tickets con id_estado = 3 (En proceso) y id_estado = 5 (Terminados)
            $sql_tickets = "SELECT 
                    SUM(CASE WHEN id_estado = 3 THEN 1 ELSE 0 END) AS total_en_proceso,
                    SUM(CASE WHEN id_estado = 5 THEN 1 ELSE 0 END) AS total_terminados
                FROM tickets
                WHERE id_usuario = $id_usuario";
                // echo $sql_tickets;
            
            $resultado_tickets = $db->consulta($sql_tickets, [$id_usuario]);
        
            $total_en_proceso = 0;
            $total_terminados = 0;
        
            if ($fila_tickets = $db->fetch_assoc($resultado_tickets)) {
                $total_en_proceso = $fila_tickets['total_en_proceso'];
                $total_terminados = $fila_tickets['total_terminados'];
            }
        
            // Obtener los colores correspondientes desde la tabla estados_ticket
            $sql_colores = "SELECT id, color FROM estados_ticket WHERE id IN (3, 5)";
            $resultado_colores = $db->consulta($sql_colores);
            // echo $sql_colores;
        
            $colores = [
                3 => "#F0B27A", // Color por defecto para "En proceso"
                5 => "#82E0AA"  // Color por defecto para "Terminados"
            ];
        
            while ($fila_color = $db->fetch_assoc($resultado_colores)) {
                $colores[$fila_color['id']] = $fila_color['color'];
            }
        
            $mensaje_tickets = "";
        
            // Agregar solo si hay tickets en proceso
            if ($total_en_proceso > 0) {
                $mensaje_tickets .= "
                    <div style='background-color: {$colores[3]}; padding: 15px; border-radius: 5px; margin-top: 10px; position: relative;'>
                        Tienes <b>{$total_en_proceso}</b> tickets que están siendo resueltos.
                    </div>";
            }
        
            // Agregar solo si hay tickets terminados
            if ($total_terminados > 0) {
                $mensaje_tickets .= "
                    <div style='background-color: {$colores[5]}; padding: 15px; border-radius: 5px; margin-top: 10px; position: relative;'>
                        Tienes <b>{$total_terminados}</b> tickets finalizados.
                    </div>";
            }
        
            // Generar el modal solo si hay tickets
            if (!empty($mensaje_tickets)) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: '<div class=\"alert alert-dark\" role=\"alert\">¡Bienvenido, <br> $nombre $apellido_paterno!</div>',
                            html: `$mensaje_tickets`,
                            showCancelButton: false,
                            confirmButtonText: 'Ok',
                            customClass: {
                                popup: 'cuerpo_modal_guardar'
                            }
                        });
                        console.log('Modal mostrado para ID: $id_usuario');
                    });
                </script>";
            } else {
                echo "<script>console.log('No hay tickets para mostrar modal. ID: $id_usuario');</script>";
            }
        }
        
        
        
          public  function bienvenidoUsuario1($id_usuario) {
            // Puedes personalizar el nombre si lo deseas
            $nombre = "Usuario";
            $apellido_paterno = "Genérico";
        
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: '<div class=\"alert alert-dark\" role=\"alert\">¡Bienvenido, <br> $nombre $apellido_paterno!</div>',
                        html: '$id_usuario',
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                        customClass: {
                            popup: 'cuerpo_modal_guardar'
                        }
                    });
                });
            </script>";
        }

        //********************************************************************


        // public function mostrarBotonesPerfiles($id_usuario) {
        //     $db = new MySQL("", "", "");
        
        //     $sql = "SELECT p.nombre 
        //             FROM usuario_perfil up
        //             JOIN perfiles p ON up.id_perfil = p.id_perfil
        //             WHERE up.id_usuario = $id_usuario
        //             ORDER BY FIELD(p.id_perfil, 3, 2, 1)";
            
        //     $resultado = $db->consulta($sql);
        //     $perfiles = [];
        
        //     while ($fila = $db->fetch_assoc($resultado)) {
        //         $perfiles[] = strtolower($fila['nombre']); // admin, tecnico, usuario
        //     }
        
        //     if (count($perfiles) <= 1) {
        //         return; // No mostramos botones si solo hay un perfil
        //     }
        
        //     echo "<div class='text-center mt-3'>
        //             <p><strong>Selecciona un perfil:</strong></p>";
            
        //     foreach ($perfiles as $perfil) {
        //         $nombrePerfil = ucfirst($perfil);
        //         echo "<button class='btn btn-outline-primary m-1'
        //                       onclick=\"cambiarPerfil('$perfil'); 
        //                               document.getElementById('textoPerfilActual').innerText = '$nombrePerfil'; 
        //                               Swal.close();\">
        //                 $nombrePerfil
        //               </button>";
        //     }
        
        //     echo "</div>";
        // }

        //******************************************************************************************** */
        // ESTADISTICAS

        public function obtenerTicketsPorEstadoYTecnico($idUsuario)
        {
            $bdato = new MySQL("", "", ""); // Instanciamos la conexión
        
            $sql = "SELECT 
                        t.id_estado, 
                        e.nombre AS nombre_estado, 
                        u.id AS id_tecnico, 
                        CONCAT(u.nombre, ' ', u.apellido_paterno) AS tecnico, 
                        COUNT(t.id_ticket) AS cantidad_tickets
                    FROM tickets t
                    JOIN estados_ticket e ON t.id_estado = e.id
                    JOIN usuarios u ON t.id_tecnico = u.id";
        
            // Si se pasa un usuario, filtramos por ese ID
            if ($idUsuario) {
                $sql .= " WHERE u.id = " . (int)$idUsuario;
            }
        
            $sql .= " GROUP BY t.id_estado, t.id_tecnico
                      ORDER BY e.orden, tecnico"; // Ordenamos por el orden de los estados
        
            $consulta = $bdato->consulta($sql); // Ejecutamos la consulta
        
            if (!$consulta) {
                return json_encode(["error" => $bdato->getLastError()]); // Manejo de errores
            }
        
            $data = [];
        
            while ($row = $bdato->fetch_assoc($consulta)) {
                $estado = $row['nombre_estado'];
                $tecnico = $row['tecnico'];
                $cantidad = (int)$row['cantidad_tickets'];
        
                if (!isset($data[$tecnico])) {
                    $data[$tecnico] = [];
                }
        
                $data[$tecnico][$estado] = $cantidad;
            }
        
            return json_encode($data);
        }
        
        function contenedorPorAreaTrabajo()
        {
            $bdato = new MySQL("", "", ""); // Instancia de la conexión a la base de datos
        
            // Consulta para contar los tickets en cada estado, agrupados por área de trabajo
            $sql = "SELECT 
                        a.id_area,
                        a.nombre_area,
                        a.correo_encargado,
                        COALESCE(SUM(CASE WHEN t.id_estado = 3 THEN 1 ELSE 0 END), 0) AS en_proceso,
                        COALESCE(SUM(CASE WHEN t.id_estado = 5 THEN 1 ELSE 0 END), 0) AS terminados,
                        COALESCE(SUM(CASE WHEN t.id_estado = 6 THEN 1 ELSE 0 END), 0) AS cerrados
                    FROM area_trabajo a
                    JOIN usuarios u ON a.id_area = u.id_area_trabajo
                    LEFT JOIN tickets t ON u.id = t.id_usuario
                    GROUP BY a.id_area, a.nombre_area, a.correo_encargado";
            $resultado = $bdato->consulta($sql);
        
            // Definir colores para los diferentes estados
            $coloresEstados = [
                'en_proceso' => '#F0B27A',  // Naranja
                'terminados' => '#82E0AA',  // Verde
                'cerrados'   => '#85929E'   // Gris/Azul
            ];
        
            // Generar HTML para cada área
            while ($area = $bdato->fetch_assoc($resultado)) {
                $id_area         = htmlspecialchars($area['id_area']);
                $nombre_area     = htmlspecialchars($area['nombre_area']);
                $correo_encargado= htmlspecialchars($area['correo_encargado']);
                $en_proceso      = $area['en_proceso'];
                $terminados      = $area['terminados'];
                $cerrados        = $area['cerrados'];
        
                echo '<div class="mb-4 col-xl-3">';
                    echo '<div class="card shadow h-100">';
                        // Encabezado del área
                        echo '<div class="card-header text-white text-center" style="background-color: #2C3E50;">';
                            echo '<h6 class="m-0 font-weight-bold">' . strtoupper($nombre_area) . '</h6>';
                            echo '<small>' . $correo_encargado . '</small>';
                        echo '</div>';
                        // Cuerpo con la cantidad de tickets por estado
                        echo '<div class="card-body">';
                            echo '<div class="estado-container">';
                                foreach (['en_proceso', 'terminados', 'cerrados'] as $estadoKey) {
                                    $cantidad = $area[$estadoKey];
                                    $color = $coloresEstados[$estadoKey];
                                    echo '<div class="estado">';
                                        echo '<span style="background-color: ' . $color . '; width: 12px; height: 12px; border-radius: 50%; display: inline-block;"></span>';
                                        echo '<span class="fw-bold ms-2">' . ucfirst(str_replace('_', ' ', $estadoKey)) . ': <b>' . $cantidad . '</b></span>';
                                    echo '</div>';
                                }
                            echo '</div>'; // Fin estado-container
                        echo '</div>'; // Fin card-body
                    echo '</div>'; // Fin card
                echo '</div>'; // Fin col-xl-3
            }
        }
        
        function obtenerTicketsPorCategoria()
        {
            $bdato = new MySQL("", "", ""); // Instancia de la conexión a la base de datos

            // Consulta para obtener todas las categorías de ticket
            $sqlCategorias = "SELECT id_categoria, nombre_categoria FROM categoria_de_ticket";
            $resultadoCategorias = $bdato->consulta($sqlCategorias);

            // Obtener los estados "En Proceso", "Terminados" y "Cerrados" con sus colores
            $sqlEstados = "SELECT id, nombre, color FROM estados_ticket WHERE id IN ('3', '5', '6')";
            $resultadoEstados = $bdato->consulta($sqlEstados);

            // Mapear los estados con sus respectivos IDs y colores
            $estados = [];
            while ($estado = $bdato->fetch_assoc($resultadoEstados)) {
                $estados[$estado['id']] = [
                    'nombre' => $estado['nombre'],
                    'color' => $estado['color']
                ];
            }

            echo '<div class="row justify-content-center contenedor-tickets">';

            while ($categoria = $bdato->fetch_assoc($resultadoCategorias)) {
                $id_categoria = htmlspecialchars($categoria['id_categoria']);
                $nombre_categoria = htmlspecialchars($categoria['nombre_categoria']);

                // Obtener cantidad de tickets en cada estado por categoría
                $sqlTickets = "SELECT 
                                    COALESCE(SUM(CASE WHEN t.id_estado = 3 THEN 1 ELSE 0 END), 0) AS en_proceso,
                                    COALESCE(SUM(CASE WHEN t.id_estado = 5 THEN 1 ELSE 0 END), 0) AS terminados,
                                    COALESCE(SUM(CASE WHEN t.id_estado = 6 THEN 1 ELSE 0 END), 0) AS cerrados
                                FROM tickets t
                                WHERE t.id_categoria_ticket = $id_categoria";
                $resultadoTickets = $bdato->consulta($sqlTickets);
                $datos = $bdato->fetch_assoc($resultadoTickets);

                // Valores de tickets en cada estado
                $en_proceso = $datos['en_proceso'];
                $terminados = $datos['terminados'];
                $cerrados = $datos['cerrados'];

                // Generar contenedor para cada categoría con la misma estructura de `contenedorPorAreaTrabajo()`
                echo '<div class="mb-4 col-xl-3">';
                echo '<div class="card shadow h-10">';

                // **Encabezado de la categoría**
                echo '<div class="card-header text-white text-center" style="background-color: #2C3E50;">';
                echo '<h6 class="m-0 font-weight-bold">' . strtoupper($nombre_categoria) . '</h6>';
                echo '</div>';

                // **Contenedor de estados**
                echo '<div class="card-body">';
                echo '<div class="estado-container">';

                // Generar los estados con sus respectivos colores y cantidad
                foreach (['en_proceso' => $en_proceso, 'terminados' => $terminados, 'cerrados' => $cerrados] as $estadoKey => $cantidad) {
                    $color = $estados[array_search(ucfirst(str_replace('_', ' ', $estadoKey)), array_column($estados, 'nombre'))]['color'] ?? '#ccc';

                    echo '<div class="estado">';
                    echo '<span style="background-color: ' . $color . '; width: 12px; height: 12px; border-radius: 50%; display: inline-block;"></span>';
                    echo '<span class="fw-bold ms-2">' . ucfirst(str_replace('_', ' ', $estadoKey)) . ': <b>' . $cantidad . '</b></span>';
                    echo '</div>';
                }

                echo '</div>'; // Fin estado-container
                echo '</div>'; // Fin card-body
                echo '</div>'; // Fin card
                echo '</div>'; // Fin col-xl-3
            }

            echo '</div>'; // Fin row contenedor-tickets
        }

        function contarTicketsPorEstado()
        {
            $bdato = new MySQL("", "", ""); // Instancia de la conexión a la base de datos
        
            // Consulta para contar los tickets por estado
            $sql = "SELECT e.nombre AS estado, COUNT(t.id_ticket) AS cantidad 
                    FROM estados_ticket e
                    LEFT JOIN tickets t ON e.id = t.id_estado
                    GROUP BY e.nombre";
            
            $resultado = $bdato->consulta($sql);
        
            $estados = [];
            $cantidades = [];
        
            while ($fila = $bdato->fetch_assoc($resultado)) {
                $estados[] = $fila['estado'];
                $cantidades[] = $fila['cantidad'];
            }
        
            return json_encode([
                'estados' => $estados,
                'cantidades' => $cantidades
            ]);
        }
        
        public function contarTicketsPorCategoria() {
            $bdato = new MySQL("", "", ""); 
            
            // Obtener estados y sus colores
            $sqlEstados = "SELECT id, nombre, color FROM estados_ticket WHERE id != 4";
            $resultadoEstados = $bdato->consulta($sqlEstados);
        
            $estados = [];
            while ($estado = $bdato->fetch_assoc($resultadoEstados)) {
                $estados[$estado['id']] = [
                    'nombre' => $estado['nombre'],
                    'color' => $estado['color']
                ];
            }
        
            // Consulta de tickets por categoría y estado
            $sql = "SELECT 
                        c.id_categoria, 
                        c.nombre_categoria, 
                        t.id_estado, 
                        COUNT(t.id_ticket) AS cantidad
                    FROM categoria_de_ticket c
                    LEFT JOIN tickets t ON c.id_categoria = t.id_categoria_ticket
                    GROUP BY c.id_categoria, c.nombre_categoria, t.id_estado";
        
            $resultado = $bdato->consulta($sql);
        
            $datosCategorias = [];
            while ($fila = $bdato->fetch_assoc($resultado)) {
                $categoria = $fila['nombre_categoria'];
                $estado = isset($estados[$fila['id_estado']]) ? $estados[$fila['id_estado']]['nombre'] : null;
                $cantidad = $fila['cantidad'];
        
                if (!$estado) continue;
        
                if (!isset($datosCategorias[$categoria])) {
                    $datosCategorias[$categoria] = [];
                    foreach ($estados as $estadoInfo) {
                        $datosCategorias[$categoria][$estadoInfo['nombre']] = 0;
                    }
                }
        
                $datosCategorias[$categoria][$estado] = $cantidad;
            }
        
            return json_encode([
                'datos' => $datosCategorias,
                'colores' => array_column($estados, 'color', 'nombre')
            ]);
        }
        
        
        public function obtenerPorcentajeEstadosPorCategoria()
        {
            $bdato = new MySQL("", "", ""); // Instancia de la conexión a la base de datos
        
            // Consulta para obtener la cantidad de tickets por categoría y estado
            $sql = "SELECT 
                        c.id_categoria,
                        c.nombre_categoria,
                        e.id AS id_estado,
                        e.nombre AS nombre_estado,
                        COUNT(t.id_ticket) AS total_tickets
                    FROM categoria_de_ticket c
                    LEFT JOIN tickets t ON c.id_categoria = t.id_categoria_ticket
                    LEFT JOIN estados_ticket e ON t.id_estado = e.id
                    GROUP BY c.id_categoria, e.id
                    ORDER BY c.id_categoria, e.id";
            // echo $sql;
            $resultado = $bdato->consulta($sql);
        
            // Organizar los datos en un array estructurado
            $datos = [];
            while ($fila = $bdato->fetch_assoc($resultado)) {
                $categoria = $fila['nombre_categoria'];
                $estado = $fila['nombre_estado'];
                $cantidad = $fila['total_tickets'];
        
                // Sumar los tickets totales por categoría
                if (!isset($datos[$categoria])) {
                    $datos[$categoria] = ['total' => 0, 'estados' => [], 'numTickets' => []];
                }
        
                $datos[$categoria]['total'] += $cantidad;
                $datos[$categoria]['estados'][$estado] = $cantidad;
                $datos[$categoria]['numTickets'][$estado] = $cantidad; // Guardar el número de tickets
            }
        
            // Calcular el porcentaje de cada estado
            foreach ($datos as $categoria => &$info) {
                foreach ($info['estados'] as $estado => &$cantidad) {
                    $cantidad = ($info['total'] > 0) ? round(($cantidad / $info['total']) * 100, 2) : 0;
                }
            }
        
            return json_encode($datos);
        }
    
    
        public function obtenerPromedioTiempoEstados()
            {
                $bdato = new MySQL("", "", "");

                $sql = "SELECT 
                            id_ticket, 
                            TIMESTAMPDIFF(HOUR, fecha_creacion_inicio, fecha_asignacion_tecnico) AS recibido_a_asignado,
                            TIMESTAMPDIFF(HOUR, fecha_asignacion_tecnico, fecha_comienzo_ticket) AS asignado_a_en_proceso,
                            TIMESTAMPDIFF(HOUR, fecha_comienzo_ticket, fecha_termino_ticket)     AS en_proceso_a_terminado,
                            TIMESTAMPDIFF(HOUR, fecha_termino_ticket, fecha_cierre_ticket)      AS terminado_a_cerrado
                        FROM proceso_tickets
                        WHERE fecha_creacion_inicio         IS NOT NULL
                            AND fecha_asignacion_tecnico    IS NOT NULL
                            AND fecha_comienzo_ticket       IS NOT NULL
                            AND fecha_termino_ticket        IS NOT NULL
                            AND fecha_cierre_ticket         IS NOT NULL";

                $resultado = $bdato->consulta($sql);

                $conteo = 0;
                $sumas = ['recibido_a_asignado' => 0, 'asignado_a_en_proceso' => 0, 'en_proceso_a_terminado' => 0, 'terminado_a_cerrado' => 0];

                while ($fila = $bdato->fetch_assoc($resultado)) {
                    foreach ($sumas as $clave => &$suma) {
                        if ($fila[$clave] !== null) {
                            $suma += $fila[$clave];
                        }
                    }
                    $conteo++;
                }

                $promedios = [];
                foreach ($sumas as $clave => $suma) {
                    $promedios[$clave] = ($conteo > 0) ? round($suma / $conteo, 2) : 0;
                }

                return json_encode($promedios);
            }
        

        // PESTAÑA DE COLEGIOS 

        // 🔹 1. Obtener usuarios con ID 28-34
        public function obtenerUsuariosEspecificos() {
            $bdato = new MySQL("", "", "");

            $sql = "SELECT id, apellido_paterno AS nombre_completo 
                    FROM usuarios 
                    WHERE id IN (28, 29, 30, 31, 32, 33, 34)";
            $resultado = $bdato->consulta($sql);

            $usuarios = [];
            while ($fila = $bdato->fetch_assoc($resultado)) {
                $usuarios[$fila['id']] = $fila['nombre_completo'];
            }
            return json_encode(array_values($usuarios)); 
        }

        // 🔹 2. Obtener estados de los tickets y sus colores
        public function obtenerEstadosTickets() {
            $bdato = new MySQL("", "", "");

            $sql = "SELECT nombre, color FROM estados_ticket";
            $resultado = $bdato->consulta($sql);

            $estados = [];
            while ($fila = $bdato->fetch_assoc($resultado)) {
                $estados[$fila['nombre']] = $fila['color'];
            }
            return json_encode($estados);
        }

        // 🔹 3. Obtener la cantidad de tickets por estado de los usuarios especificados
        public function obtenerUsuariosColegios() {
            $bdato = new MySQL("", "", "");
            $sql = "SELECT u.id, u.apellido_paterno AS nombre, u.email,
                        SUM(CASE WHEN t.id_estado = 1 THEN 1 ELSE 0 END) AS recibidos,
                        SUM(CASE WHEN t.id_estado = 3 THEN 1 ELSE 0 END) AS en_proceso,
                        SUM(CASE WHEN t.id_estado = 5 THEN 1 ELSE 0 END) AS finalizados,
                        COUNT(t.id_ticket) AS total
                    FROM usuarios u
                    LEFT JOIN tickets t ON u.id = t.id_usuario
                    WHERE u.id BETWEEN 28 AND 34
                    GROUP BY u.id, u.apellido_paterno, u.email";
                    // echo $sql."********";
            
            $resultado = $bdato->consulta($sql);
            $usuariosColegios = [];

            while ($fila = $bdato->fetch_assoc($resultado)) {
                $usuariosColegios[] = [
                    'id' => $fila['id'],
                    'nombre' => strtoupper("COLEGIO_" . $fila['nombre']),
                    'email' => $fila['email'],
                    'recibidos' => $fila['recibidos'],
                    'en_proceso' => $fila['en_proceso'],
                    'finalizados' => $fila['finalizados'],
                    'total' => $fila['total']
                ];
            }

            if (is_array($usuariosColegios)) {
                foreach ($usuariosColegios as $usuario) {
                    $img = "img/colegios/colegio_" . $usuario["id"] . ".png";

                    // Evitar división por 0
                    $total = $usuario["total"] > 0 ? $usuario["total"] : 1;

                    $porcRecibidos   = round(($usuario["recibidos"] / $total) * 100);
                    $porcEnProceso   = round(($usuario["en_proceso"] / $total) * 100);
                    $porcFinalizados = round(($usuario["finalizados"] / $total) * 100);

                    echo '<div class="col-md-3 mb-4">';
                    echo '<div class="p-3 rounded shadow-sm bg-light h-100">';
                    echo '<div class="d-flex justify-content-between align-items-center">';
                    
                    // Datos del colegio
                    echo '<div>';
                    echo '<div class="fw-bold fs-5">' . $usuario["nombre"] . '</div>';
                    echo '<div class="text-muted small">' . $usuario["total"] . ' Total</div>';
                    echo '</div>';

                    // Logo
                    echo '<div class="position-relative">';
                    echo '<div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 50px; height: 50px;">';
                    echo '<img src="' . $img . '" alt="' . $usuario["nombre"] . '" width="30">';
                    echo '</div>';
                    echo '</div>';

                    echo '</div>'; // cierre header colegio

                    // Barras de progreso
                    echo '<div class="mt-3">';
                    
                    // Recibidos
                    echo '<div class="d-flex align-items-center mb-2 justify-content-between">';
                    echo '<div class="text-muted" style="width: 80px;">Recibidos</div>';
                    echo '<div class="progress flex-grow-1 me-2" style="height: 8px;">';
                    echo '<div class="progress-bar bg-info" style="width: ' . $porcRecibidos . '%;"></div>';
                    echo '</div>';
                    echo '<div class="text-muted small" style="width: 30px;">' . $porcRecibidos . '%</div>';
                    echo '</div>';

                    // En Proceso
                    echo '<div class="d-flex align-items-center mb-2 justify-content-between">';
                    echo '<div class="text-muted" style="width: 80px;">En Proceso</div>';
                    echo '<div class="progress flex-grow-1 me-2" style="height: 8px;">';
                    echo '<div class="progress-bar bg-warning" style="width: ' . $porcEnProceso . '%;"></div>';
                    echo '</div>';
                    echo '<div class="text-muted small" style="width: 30px;">' . $porcEnProceso . '%</div>';
                    echo '</div>';

                    // Finalizados
                    echo '<div class="d-flex align-items-center justify-content-between">';
                    echo '<div class="text-muted" style="width: 80px;">Finalizados</div>';
                    echo '<div class="progress flex-grow-1 me-2" style="height: 8px;">';
                    echo '<div class="progress-bar bg-success" style="width: ' . $porcFinalizados . '%;"></div>';
                    echo '</div>';
                    echo '<div class="text-muted small" style="width: 30px;">' . $porcFinalizados . '%</div>';
                    echo '</div>';


                    echo '</div>'; // cierre barras
                    echo '</div>'; // cierre tarjeta
                    echo '</div>'; // cierre columna
                }
            } else {
                echo "No se encontraron usuarios de colegios.";
            }
        }

// ************************************************************
// *****************************************************
    public function obtenerEventos() {
        $bd = new MySQL("", "", ""); // Ajusta los parámetros de conexión si es necesario
        $sql = "SELECT 
        e.id, 
        e.titulo, 
        e.descripcion, 
        e.fecha_inicio, 
        e.hora_evento, 
        e.con_audio, 
        e.solo_presentacion, 
        e.musica_ambiental, 
        e.cantidad_personas, 
        e.creado_en, 
        e.responsable_id, 
        e.eliminado,
        u.nombre,
        u.apellido_paterno,
        u.apellido_materno


        FROM eventos e
        LEFT JOIN usuarios u ON e.responsable_id = u.id
        WHERE e.eliminado = 'no'
        ORDER BY e.fecha_inicio ASC, e.hora_evento ASC;
        ";

        $resultado = $bd->consulta($sql);

        $eventos = [];
        while ($fila = $bd->fetch_assoc($resultado)) {
            $eventos[] = $fila;
        }
        return $eventos;
    }

public function renderizarResumenEventosPorDia($eventos) {
    $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
    $iconos = ['bi-calendar-week', 'bi-calendar-week', 'bi-calendar-week', 'bi-calendar-week', 'bi-calendar-week'];
    $eventosPorDia = array_fill(0, 5, []);
    $diaActual = date('N'); // 1 (Lunes) a 7 (Domingo)

    foreach ($eventos as $evento) {
        $diaSemana = date('N', strtotime($evento['fecha_inicio']));
        if ($diaSemana >= 1 && $diaSemana <= 5) {
            $eventosPorDia[$diaSemana - 1][] = $evento;
        }
    }

    echo '<div class="row d-flex flex-wrap justify-content-between">';
    foreach ($diasSemana as $i => $nombreDia) {
        $eventosDia = $eventosPorDia[$i];
        $cantidad = count($eventosDia);
        $progreso = $cantidad > 0 ? 100 : 0;
        $esHoy = ($diaActual == $i + 1) ? 'resaltar-hoy' : '';

        echo '<div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="tarjeta-ticket-estado fade-in ' . $esHoy . '">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="fw-bold">' . $nombreDia . '</div>
                            <div class="icon-circle bg-white shadow-sm text-dark">
                                <i class="bi ' . $iconos[$i] . '"></i>
                            </div>
                        </div>';

        echo '<div class="fw-semibold text-muted mb-2">'
                . $cantidad . ' evento' . ($cantidad === 1 ? '' : 's') .
             '</div>';

        if ($cantidad > 0) {
            echo '<ul class="list-unstyled small mb-2">';
            $maxMostrar = 3;
            foreach (array_slice($eventosDia, 0, $maxMostrar) as $ev) {
                echo '<li><i class="bi bi-dot"></i> ' . htmlspecialchars($ev['titulo']) .
                     ' (' . date('H:i', strtotime($ev['hora_evento'])) . ')</li>';
            }
            if ($cantidad > $maxMostrar) {
                echo '<li class="text-muted"><i class="bi bi-three-dots"></i> y ' . ($cantidad - $maxMostrar) . ' más...</li>';
            }
            echo '</ul>';
        } else {
            echo '<p class="text-muted small">Sin eventos</p>';
        }

        echo '<div class="progress mt-2" style="height: 6px;">
                <div class="progress-bar" role="progressbar" style="width: ' . $progreso . '%;"></div>
              </div>';

        echo '</div></div></div>';
    }
    echo '</div>';
}























        public function obtenerEstados() {
            $bdato = new MySQL("", "", ""); // Instancia de la conexión a la base de datos
            $sql = "SELECT nombre FROM estados_ticket ORDER BY orden ASC";
            $resultado = $bdato->consulta($sql);

            $estados = [];
            while ($fila = $bdato->fetch_assoc($resultado)) {
                $estados[] = $fila['nombre'];
            }

            return $estados;
        }

        public function generarContenedorListados($datosPorcentajes) {
                $estados = $this->obtenerEstados(); // Obtener los estados desde la base de datos
                ob_start();
                ?>

<div class="card-body">
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Categoría</th>
                    <?php
                                            foreach ($estados as $estado) {
                                                echo "<th>{$estado} (%)</th>";
                                            }
                                            ?>
                    <th>Detalles</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($datosPorcentajes as $categoria => $info): ?>
                <tr>
                    <td><b><?php echo $categoria; ?></b></td>
                    <?php foreach ($estados as $estado): ?>
                    <?php
                                            $valor = isset($info['estados'][$estado]) ? $info['estados'][$estado] : 0;
                                            $numTickets = isset($info['numTickets'][$estado]) ? $info['numTickets'][$estado] : 0;
                                            $color = ($valor == 100) ? "bg-danger" : "bg-primary";
                                            ?>
                    <td>
                        <div class="progress">
                            <div class="progress-bar <?php echo $color; ?>" role="progressbar"
                                style="width: <?php echo $valor; ?>%;" aria-valuenow="<?php echo $valor; ?>"
                                aria-valuemin="0" aria-valuemax="100">
                                <?php echo $valor; ?>%
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: <?php echo $porcentaje; ?>%;"></div>
                            </div>
                            <span class="ms-2 text-muted small"><?php echo $numTickets; ?> Ticket</span>
                        </div>

                    </td>
                    <?php endforeach; ?>
                    <td>
                        <button class="btn btn-primary" onclick="mostrarPromedioEstados()">📊 Ver
                            Cronología</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- </div> -->
<?php
                    return ob_get_clean();
            }



        public function obtenerDatosGraficoColegios() {
            $bdato = new MySQL("", "", ""); // Instancia de la conexión a la base de datos
            $sql = "
                SELECT t.id_ticket, t.id_usuario, t.id_estado, p.fecha_creacion_inicio, p.fecha_cierre_ticket
                FROM tickets t
                JOIN proceso_tickets p ON t.id_ticket = p.id_ticket
            ";
            $resultado = $bdato->consulta($sql);

            $datos = [];
            while ($fila = $bdato->fetch_assoc($resultado)) {
                $usuario = $fila['id_usuario'];
                $estado = $fila['id_estado'];
                if (!isset($datos[$usuario])) {
                    $datos[$usuario] = [];
                }
                if (!isset($datos[$usuario][$estado])) {
                    $datos[$usuario][$estado] = 0;
                }
                $datos[$usuario][$estado]++;
            }

            return json_encode($datos);
        }

        public function obtenerDatosGraficoColegios2() {
            $bdato = new MySQL("", "", ""); // Instancia de la conexión a la base de datos
            $sql = "
                SELECT t.id_usuario, p.fecha_creacion_inicio, t.id_estado
                FROM tickets t
                JOIN proceso_tickets p ON t.id_ticket = p.id_ticket
                WHERE t.id_usuario BETWEEN 28 AND 34
            ";
            $resultado = $bdato->consulta($sql);

            $mesesEspañol = [
                'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo', 'April' => 'Abril',
                'May' => 'Mayo', 'June' => 'Junio', 'July' => 'Julio', 'August' => 'Agosto',
                'September' => 'Septiembre', 'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
            ];

            $datos = [];
            while ($fila = $bdato->fetch_assoc($resultado)) {
                $mes = date('F', strtotime($fila['fecha_creacion_inicio']));
                $mes = $mesesEspañol[$mes];
                $estado = $fila['id_estado'];
                if (!isset($datos[$mes])) {
                    $datos[$mes] = ['Recibido' => 0, 'En Proceso' => 0, 'Terminado' => 0];
                }
                switch ($estado) {
                    case 1:
                        $datos[$mes]['Recibido']++;
                        break;
                    case 3:
                        $datos[$mes]['En Proceso']++;
                        break;
                    case 5:
                        $datos[$mes]['Terminado']++;
                        break;
                }
            }

            return json_encode($datos);
        }




        


public function tickets_recibidos_hoy()
{
    $bdato = new MySQL("", "", "");
    $fechaHoy = date("Y-m-d");

    $sql = "SELECT COUNT(*) AS total
            FROM tickets t
            JOIN proceso_tickets p ON t.id_ticket = p.id_ticket
            WHERE t.id_estado = 1 AND DATE(p.fecha_creacion_inicio) = '$fechaHoy'";

    $resultado = $bdato->consulta($sql);
    $row = $bdato->fetch_array($resultado);
    $total = $row['total'] ?? 0;

    echo '
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card shadow border-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-ticket-perforated-fill fs-1 text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-xs fw-bold text-uppercase text-muted">
                            Tickets Recibidos <span class="text-secondary">| Hoy</span>
                        </div>
                        <h3 class="mb-0 fw-bold">' . $total . '</h3>
                        <small class="text-success fw-semibold">↑ aumento del 18%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>';
}

 public function tickets_en_proceso_mes()
{
    $bdato = new MySQL("", "", "");
    $anio = date("Y");
    $mes = date("m");

    $sql = "SELECT COUNT(*) AS total
            FROM tickets t
            JOIN proceso_tickets p ON t.id_ticket = p.id_ticket
            WHERE t.id_estado = 3 
            AND YEAR(p.fecha_creacion_inicio) = $anio 
            AND MONTH(p.fecha_creacion_inicio) = $mes";

    $resultado = $bdato->consulta($sql);
    $row = $bdato->fetch_array($resultado);
    $total = $row['total'] ?? 0;

    echo '
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card shadow border-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-tools fs-1 text-warning"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-xs fw-bold text-uppercase text-muted">
                            Tickets en Proceso <span class="text-secondary">| Este mes</span>
                        </div>
                        <h3 class="mb-0 fw-bold">' . $total . '</h3>
                        <small class="text-danger fw-semibold">↓ disminución del 5%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>';
}

public function tickets_resueltos_anio()
{
    $bdato = new MySQL("", "", "");
    $anio = date("Y");

    $sql = "SELECT COUNT(*) AS total
            FROM tickets t
            JOIN proceso_tickets p ON t.id_ticket = p.id_ticket
            WHERE t.id_estado = 5 
            AND YEAR(p.fecha_creacion_inicio) = $anio";

    $resultado = $bdato->consulta($sql);
    $row = $bdato->fetch_array($resultado);
    $total = $row['total'] ?? 0;

    echo '
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card shadow border-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-check-circle-fill fs-1 text-success"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-xs fw-bold text-uppercase text-muted">
                            Tickets Resueltos <span class="text-secondary">| Este año</span>
                        </div>
                        <h3 class="mb-0 fw-bold">' . $total . '</h3>
                        <small class="text-success fw-semibold">↑ aumento del 22%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>';
}
// Estas son funciones sueltas, FUERA de la clase
function obtenerPerfilesUsuario($idUsuario, $conexion) {
    $perfiles = [];
    $sql = "SELECT LOWER(p.nombre) AS nombre
            FROM usuario_perfil up
            JOIN perfiles p ON up.id_perfil = p.id_perfil
            WHERE up.id_usuario = $idUsuario";

    $resultado = $conexion->consulta($sql);
    while ($row = $conexion->fetch_array($resultado)) {
        $perfiles[] = $row['nombre']; // 'usuario', 'tecnico', etc.
    }
    return $perfiles;
}


    public function obtenerPerfilPorDefecto($perfiles) {
        $prioridad = ['usuario', 'tecnico', 'administrador'];
        foreach ($prioridad as $p) {
            if (in_array($p, $perfiles)) {
                return $p;
            }
        }
        return $perfiles[0] ?? null;
    }





    
    public function generarIdentificadorUnico($tabla, $bd, $num_caracteres) {
        $columna = 'identificador';
        do {
            $identificador = $this->generarIdentificador($num_caracteres);
        } while ($this->buscarIdentificador($bd, $tabla, $columna, $identificador) !== "");
        return $identificador;
    }

    /**
     * Genera un string aleatorio de longitud $num_caracteres
     */
    public function generarIdentificador($num_caracteres) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $identificador = '';
        for ($i = 0; $i < $num_caracteres; $i++) {
            $identificador .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $identificador;
    }

    /**
     * Busca si ya existe el identificador en la tabla/columna indicada.
     */
    public function buscarIdentificador($bd, $tabla, $columna, $identificador) {
        $query = "SELECT $columna FROM $tabla WHERE $columna = '$identificador'";
        $resultado = $bd->consulta($query);
        if ($bd->num_rows($resultado) > 0) {
            $fila = $bd->fetch_array($resultado);
            return $fila[$columna];
        }
        return "";
    }
    function contenedoresUsuario($idUsuario, $filtro = '')
    {
        $bdato = new MySQL("", "", "");
        $filtro = $bdato->escape_string($filtro);
    
        $sql = "SELECT * FROM contenedores_layout WHERE id_usuario = $idUsuario";
        if (!empty($filtro)) {
            $sql .= " AND titulo LIKE '%$filtro%'";
        }
        $sql .= " ORDER BY orden ASC";
    
        $resultado = $bdato->consulta($sql);
    
        while ($row = $bdato->fetch_array($resultado)) {
            $titulo = htmlentities($row["titulo"], ENT_HTML5, "UTF-8");
            $icono  = !empty($row["icono_fa"]) ? htmlentities($row["icono_fa"], ENT_HTML5, "UTF-8") : 'fas fa-box fa-2x';
    
    echo '<div class="acceso" data-titulo="' . $titulo . '" onclick="window.location.href=\'' . $row["url"] . '\'" style="cursor: pointer;">';
    echo '  <div class="dropdown text-end" style="position:absolute; top:10px; right:10px;" onclick="event.stopPropagation();">';
    echo '    <button class="btn btn-sm rounded-circle bg-dark text-white border-0 px-2 py-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 28px; height: 28px; font-size: 18px; line-height: 18px;">⋮</button>';
    echo '    <ul class="dropdown-menu dropdown-menu-alert">';
    echo '      <li><a class="dropdown-item" href="' . $row["url"] . '" target="_blank">Abrir</a></li>';
    echo '      <li><a class="dropdown-item" href="#">Editar</a></li>';
    echo '      <li><hr class="dropdown-divider"></li>';
    echo '      <li><a class="dropdown-item text-danger" href="#">Eliminar</a></li>';
    echo '    </ul>';
    echo '  </div>';
    echo '  <i class="' . $icono . '"></i>';
    echo      $titulo;
    echo '</div>';
    
    
        }
    }









    public function contarPorEstadoYMes($bd, $estado, $mes) {
 $sql = "SELECT COUNT(*) AS total
        FROM tickets t
        INNER JOIN proceso_tickets p ON t.id_ticket = p.id_ticket
        WHERE t.id_estado = $estado AND MONTH(p.fecha_creacion_inicio) = '$mes'";

        $res = $bd->consulta($sql);
        $row = $bd->fetch_assoc($res);
        return (int)$row['total'];
    }
    
    public function contarPorMes($bd, $mes) {
     $sql = "SELECT COUNT(*) AS total
        FROM proceso_tickets
        WHERE MONTH(fecha_creacion_inicio) = '$mes'";

        $res = $bd->consulta($sql);
        $row = $bd->fetch_assoc($res);
        return (int)$row['total'];
    }

public function contarPorEstadoYAnio($bd, $estado, $anio) {
    $sql = "SELECT COUNT(*) AS total
            FROM tickets t
            INNER JOIN proceso_tickets p ON t.id_ticket = p.id_ticket
            WHERE t.id_estado = $estado AND YEAR(p.fecha_creacion_inicio) = '$anio'";
    $res = $bd->consulta($sql);
    $row = $bd->fetch_assoc($res);
    return (int)$row['total'];
}

public function obtenerResumenTicketsPorUsuarios() {
    $bd = new MySQL("", "", "");

    $sql = "SELECT 
                u.id,
                u.apellido_paterno AS nombre,
                u.email,
                SUM(CASE WHEN t.id_estado = 1 THEN 1 ELSE 0 END) AS recibidos,
                SUM(CASE WHEN t.id_estado = 3 THEN 1 ELSE 0 END) AS en_proceso,
                SUM(CASE WHEN t.id_estado = 5 THEN 1 ELSE 0 END) AS finalizados,
                COUNT(t.id_ticket) AS total
            FROM usuarios u
            LEFT JOIN tickets t ON u.id = t.id_usuario
            WHERE u.id BETWEEN 28 AND 34
            GROUP BY u.id, u.apellido_paterno, u.email";

    $res = $bd->consulta($sql);

    while ($row = $bd->fetch_assoc($res)) {
        $total = ($row['total'] > 0) ? $row['total'] : 1;

        $porcRecibidos   = round(($row['recibidos'] / $total) * 100);
        $porcEnProceso   = round(($row['en_proceso'] / $total) * 100);
        $porcFinalizados = round(($row['finalizados'] / $total) * 100);

        $nombreColegio = strtoupper("COLEGIO " . $row["nombre"]);

        echo '<div class="col-md-6 col-lg-4 mb-4">';
        echo '  <div class="card shadow p-4 rounded-4">';
        echo '    <div class="d-flex justify-content-between align-items-center mb-3">';
        echo '      <h5 class="fw-bold mb-0">' . $nombreColegio . '</h5>';
        echo '    </div>';

        echo '    <small class="text-muted">Avance de Tickets</small>';

        // Recibidos
        echo '    <div class="colegio-item d-flex align-items-center justify-content-between mt-3">';
        echo '      <span class="text-muted">Recibidos</span>';
        echo '      <div class="progress-circle red">' . $porcRecibidos . '%</div>';
        echo '    </div>';

        // En Proceso
        echo '    <div class="colegio-item d-flex align-items-center justify-content-between mt-2">';
        echo '      <span class="text-muted">En Proceso</span>';
        echo '      <div class="progress-circle orange">' . $porcEnProceso . '%</div>';
        echo '    </div>';

        // Finalizados
        echo '    <div class="colegio-item d-flex align-items-center justify-content-between mt-2">';
        echo '      <span class="text-muted">Finalizados</span>';
        echo '      <div class="progress-circle green">' . $porcFinalizados . '%</div>';
        echo '    </div>';

        echo '    <div class="text-end mt-3">';
        echo '      <small class="text-muted">Total: ' . $row['total'] . ' tickets</small>';
        echo '    </div>';
        echo '  </div>';
        echo '</div>';
    }
}







public function obtenerColegios($idUsuario): array
{
    $bd = new MySQL("", "", "");
    $bd->consulta("SET NAMES 'utf8mb4'");

    // Usuarios que pueden ver TODOS los colegios
    $usuariosGlobales = [6, 8, 9, 24, 42];
    $esGlobal = in_array((int)$idUsuario, $usuariosGlobales, true);

    if ($esGlobal) {
        // Ve todos los colegios activos
        $sql = "
            SELECT 
                id_colegio,
                nom_colegio
            FROM colegio
            WHERE estado = 1
            ORDER BY nom_colegio ASC
        ";
        $res = $bd->consulta($sql);

    } else {
        // Ve SOLO los colegios asignados en usuario_colegio (activos)
        $sql = "
            SELECT 
                c.id_colegio,
                c.nom_colegio
            FROM usuario_colegio uc
            INNER JOIN colegio c ON c.id_colegio = uc.id_colegio
            WHERE uc.id_usuario = " . (int)$idUsuario . "
              AND uc.estado = 1
              AND c.estado = 1
            GROUP BY c.id_colegio, c.nom_colegio
            ORDER BY c.nom_colegio ASC
        ";
        $res = $bd->consulta($sql);
    }

    $colegios = [];
    while ($row = $bd->fetch_assoc($res)) {
        $colegios[] = $row;
    }

    return $colegios;
}


public function invPC_getKpis($idUsuario, $idColegioSel)
{
    $bd = new MySQL("", "", "");
    $bd->consulta("SET NAMES 'utf8mb4'");

    $idUsuario = (int)$idUsuario;
    $idColegioSel = (int)$idColegioSel;

    $adminsGlobales = [6,8,9,42,24];
    $esGlobal = in_array($idUsuario, $adminsGlobales, true);

    // seguridad: si no es global, forzar al colegio asignado (primero)
    if (!$esGlobal) {
        $sqlC = "SELECT id_colegio FROM usuario_colegio
                 WHERE id_usuario={$idUsuario} AND estado=1
                 LIMIT 1";
        $rC = $bd->consulta($sqlC);
        $rowC = $bd->fetch_assoc($rC);
        $idColegioSel = (int)($rowC['id_colegio'] ?? 0);
    } else {
        // global: 0 = todos
        // si viene un id específico se respeta
    }

    $where = " WHERE 1=1 ";
    if ($idColegioSel > 0) {
        $where .= " AND e.id_colegio = {$idColegioSel} ";
    } else {
        // todos (solo si es global)
        if (!$esGlobal) $where .= " AND 1=0 ";
    }

    $sql = "
      SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN e.id_estado = 1 THEN 1 ELSE 0 END) AS ok,
        SUM(CASE WHEN e.id_estado = 2 THEN 1 ELSE 0 END) AS rep,
        SUM(CASE WHEN e.id_estado = 3 THEN 1 ELSE 0 END) AS baja
      FROM equipos e
      {$where}
    ";

    $res = $bd->consulta($sql);
    $row = $bd->fetch_assoc($res);

    return [
        'total' => (int)($row['total'] ?? 0),
        'ok'    => (int)($row['ok'] ?? 0),
        'rep'   => (int)($row['rep'] ?? 0),
        'baja'  => (int)($row['baja'] ?? 0),
        'id_colegio' => $idColegioSel,
        'is_global'  => $esGlobal
    ];
}

public function invPC_getChartPorColegio($idUsuario, $idColegioSel)
{
    $bd = new MySQL("", "", "");
    $bd->consulta("SET NAMES 'utf8mb4'");

    $idUsuario = (int)$idUsuario;
    $idColegioSel = (int)$idColegioSel;

    $adminsGlobales = [6,8,9,42,24];
    $esGlobal = in_array($idUsuario, $adminsGlobales, true);

    if (!$esGlobal) {
        $sqlC = "SELECT id_colegio FROM usuario_colegio
                 WHERE id_usuario={$idUsuario} AND estado=1
                 LIMIT 1";
        $rC = $bd->consulta($sqlC);
        $rowC = $bd->fetch_assoc($rC);
        $idColegioSel = (int)($rowC['id_colegio'] ?? 0);
    }

    $where = " WHERE c.estado = 1 ";
    if ($idColegioSel > 0) $where .= " AND c.id_colegio = {$idColegioSel} ";
    else if (!$esGlobal)   $where .= " AND 1=0 ";

    $sql = "
      SELECT
        c.id_colegio,
        c.nom_colegio,
        SUM(CASE WHEN e.id_estado = 1 THEN 1 ELSE 0 END) AS ok,
        SUM(CASE WHEN e.id_estado = 2 THEN 1 ELSE 0 END) AS rep,
        SUM(CASE WHEN e.id_estado = 3 THEN 1 ELSE 0 END) AS baja
      FROM colegio c
      LEFT JOIN equipos e ON e.id_colegio = c.id_colegio
      {$where}
      GROUP BY c.id_colegio, c.nom_colegio
      ORDER BY c.nom_colegio ASC
    ";

    $res = $bd->consulta($sql);
    $out = [];
    while ($row = $bd->fetch_assoc($res)) $out[] = $row;
    return $out;
}

public function invPC_getListadoEquipos($idUsuario, $idColegioSel)
{
    $bd = new MySQL("", "", "");
    $bd->consulta("SET NAMES 'utf8mb4'");

    $idUsuario = (int)$idUsuario;
    $idColegioSel = (int)$idColegioSel;

    $adminsGlobales = [6,8,9,42,24];
    $esGlobal = in_array($idUsuario, $adminsGlobales, true);

    if (!$esGlobal) {
        $sqlC = "SELECT id_colegio FROM usuario_colegio
                 WHERE id_usuario={$idUsuario} AND estado=1
                 LIMIT 1";
        $rC = $bd->consulta($sqlC);
        $rowC = $bd->fetch_assoc($rC);
        $idColegioSel = (int)($rowC['id_colegio'] ?? 0);
    }

    $where = " WHERE 1=1 ";
    if ($idColegioSel > 0) $where .= " AND e.id_colegio = {$idColegioSel} ";
    else if (!$esGlobal)   $where .= " AND 1=0 ";

    $sql = "
      SELECT
        e.id_equipo        AS id_pc,
        e.nombre_equipo,
        c.nom_colegio,
        e.numero_serie,
        e.tipo_pc,
        e.fabricante       AS marca,
        e.producto         AS modelo,
        e.id_estado,
        ua.nombre          AS asignado_nombre,

        p.equipo_fabricante AS cpu_fabricante,
        p.equipo_modelo     AS cpu_modelo,
        p.equipo_velocidad  AS cpu_freq,

        a.equipo_modelo     AS alm_nombre,
        a.equipo_tamano     AS alm_tipo,
        a.equipo_capacidad  AS disco_gb,

        m.tamano_memoria    AS ram_gb,

        s.windows,
        s.office,
        s.antivirus,

        ec.observacion      AS observaciones

      FROM equipos e
      INNER JOIN colegio c ON c.id_colegio = e.id_colegio

      LEFT JOIN usuarios ua ON ua.id = e.id_usuario_asignado

      LEFT JOIN equipo_procesador p ON p.id_equipo = e.id_equipo
      LEFT JOIN equipo_almacenamiento a ON a.id_equipo = e.id_equipo
      LEFT JOIN equipo_software s ON s.id_equipo = e.id_equipo
      LEFT JOIN equipos_compra ec ON ec.id_equipo = e.id_equipo

      LEFT JOIN equipo_memoria m
        ON m.id_equipo = e.id_equipo AND m.orden_memoria = 1

      {$where}
      ORDER BY c.nom_colegio ASC, e.nombre_equipo ASC ";
// echo "<br><br>".$sql."<br><br>";
    $res = $bd->consulta($sql);
    $out = [];
    while ($row = $bd->fetch_assoc($res)) {
        // traducir id_estado a texto tipo OK/REP/BAJA para tu HTML actual
        $estadoTxt = 'OK';
        $idEstado = (int)($row['id_estado'] ?? 1);
        if ($idEstado === 2) $estadoTxt = 'REP';
        if ($idEstado === 3) $estadoTxt = 'BAJA';
        $row['estado'] = $estadoTxt;

        // normalizar asignado
        if (empty($row['asignado_nombre'])) $row['asignado_nombre'] = 'Sin asignar';

        $out[] = $row;
    }
    return $out;
}

/**
 * ====================================================================
 * MANTENEDOR DE COLEGIOS
 * ==================================================================== */

/**
 * Colegios activos (estado = 1) con sus datos de contacto.
 * @return array<int,array<string,mixed>>
 */
public function obtenerColegiosActivos()
{
    $bdato = new MySQL('', '', '');
    $sql = "SELECT id_colegio, nom_colegio, rza_colegio, dir_colegio,
                   tel_colegio, email_comunicaciones, rbd_colegio, estado
            FROM colegio
            WHERE estado = 1
            ORDER BY nom_colegio ASC";
    $res = $bdato->consulta($sql);
    $colegios = [];
    while ($row = $bdato->fetch_assoc($res)) {
        $colegios[] = $row;
    }
    return $colegios;
}

/**
 * Nº de usuarios vinculados (activos) a un colegio.
 * @param int $idColegio
 * @return int
 */
public function contarUsuariosPorColegio($idColegio)
{
    $bdato = new MySQL('', '', '');
    $idColegio = (int) $idColegio;
    $res = $bdato->consulta(
        "SELECT COUNT(DISTINCT id_usuario) AS n
         FROM usuario_colegio
         WHERE id_colegio = $idColegio AND estado = 1"
    );
    $row = $bdato->fetch_assoc($res);
    return (int) ($row['n'] ?? 0);
}

/**
 * Nº de eventos asociados a un colegio.
 * Requiere la columna eventos.id_colegio.
 * @param int $idColegio
 * @return int
 */
public function contarEventosPorColegio($idColegio)
{
    $bdato = new MySQL('', '', '');
    $idColegio = (int) $idColegio;
    $res = $bdato->consulta(
        "SELECT COUNT(*) AS n FROM eventos WHERE id_colegio = $idColegio"
    );
    $row = $bdato->fetch_assoc($res);
    return (int) ($row['n'] ?? 0);
}

/**
 * Nº de módulos de un colegio.
 * Placeholder: no hay origen de datos definido todavía.
 * @param int $idColegio
 * @return int
 */
public function contarModulosPorColegio($idColegio)
{
    return 0;
}

}


