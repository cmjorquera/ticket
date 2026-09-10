<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conectar - USUARIO CORRECTO
$db = new mysqli('localhost', 'crist668_jorquera', 'Ingeniero86#', 'crist668_logica_permisos');
if ($db->connect_error) die("Error BD: " . $db->connect_error);
$db->set_charset("utf8");

// Cambios
if ($_POST) {
    $id = (int)$_POST['id_usuario'];
    
    if ($_POST['accion'] == 'guardar') {
        $perfiles_seleccionados = isset($_POST['perfiles']) ? $_POST['perfiles'] : [];
        $depto = (int)$_POST['id_departamento'];
        $colegio = (int)$_POST['id_colegio'];
        
        // Eliminar perfiles anteriores
        $db->query("DELETE FROM usuario_perfil WHERE id_usuario = $id");
        
        // Insertar nuevos perfiles
        foreach ($perfiles_seleccionados as $perfil_id) {
            $perfil_id = (int)$perfil_id;
            $db->query("INSERT INTO usuario_perfil (id_usuario, id_perfil) VALUES ($id, $perfil_id)");
        }
        
        // Eliminar jefatura anterior
        $db->query("DELETE FROM jefatura_departamento WHERE id_usuario = $id");
        
        // Insertar nueva jefatura si la hay
        if ($depto > 0 && $colegio > 0) {
            $db->query("INSERT INTO jefatura_departamento (id_usuario, id_departamento, id_colegio) VALUES ($id, $depto, $colegio)");
        }
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    if ($_POST['accion'] == 'eliminar') {
        $db->query("DELETE FROM usuario_perfil WHERE id_usuario = $id");
        $db->query("DELETE FROM jefatura_departamento WHERE id_usuario = $id");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Obtener usuarios (SIN REPETIDOS)
$sql = "SELECT DISTINCT u.id, u.nombre, u.apellido_paterno, u.email,
        d.nombre_departamento,
        j.id_departamento, j.id_colegio, c.nom_colegio
        FROM usuarios u
        LEFT JOIN jefatura_departamento j ON u.id = j.id_usuario
        LEFT JOIN departamentos d ON j.id_departamento = d.id_departamento
        LEFT JOIN colegio c ON j.id_colegio = c.id_colegio
        WHERE u.estado = 'Activo'
        ORDER BY u.nombre ASC LIMIT 100";

$usuarios = $db->query($sql);

// Datos para dropdowns
$perfiles = $db->query("SELECT * FROM perfiles WHERE estado = 1 ORDER BY id_perfil");
$deptos = $db->query("SELECT * FROM departamentos WHERE estado = 1 ORDER BY nombre_departamento");
$colegios = $db->query("SELECT * FROM colegio WHERE estado = 1 ORDER BY nom_colegio");

// Función para obtener perfiles del usuario
function obtener_perfiles_usuario($db, $id_usuario) {
    $result = $db->query("SELECT id_perfil FROM usuario_perfil WHERE id_usuario = $id_usuario");
    $perfiles = [];
    while ($row = $result->fetch_assoc()) {
        $perfiles[] = $row['id_perfil'];
    }
    return $perfiles;
}

// Función para obtener color del badge según perfil
function obtener_color_perfil($perfil_nombre) {
    if ($perfil_nombre == 'super_admin') return 'danger'; // ROJO
    if ($perfil_nombre == 'tecnico') return 'warning'; // AMARILLO
    if ($perfil_nombre == 'admin_area') return 'success'; // VERDE
    if ($perfil_nombre == 'admin_colegio') return 'purple'; // MORADO
    return 'info'; // AZUL (usuario, otros)
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Permisos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; }
        .navbar { background: #163A5F; }
        table { background: white; }
        thead { background: #163A5F; color: white; }
        .badge-perfil { margin-right: 5px; }
        .bg-purple { background-color: #9966cc !important; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand">👥 Gestionar Permisos</span>
        </div>
    </nav>

    <div class="container mt-4">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Perfiles</th>
                    <th>Departamento</th>
                    <th>Colegio</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = $usuarios->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><?php echo $u['nombre'] . ' ' . $u['apellido_paterno']; ?></td>
                    <td><small><?php echo $u['email']; ?></small></td>
                    <td>
                        <?php 
                        $perfiles_usuario = obtener_perfiles_usuario($db, $u['id']);
                        if (empty($perfiles_usuario)) {
                            echo '<span class="badge bg-secondary">Sin perfil</span>';
                        } else {
                            $perfiles_temp = $db->query("SELECT id_perfil, nombre FROM perfiles WHERE id_perfil IN (" . implode(',', $perfiles_usuario) . ")");
                            while ($p = $perfiles_temp->fetch_assoc()) {
                                $color = obtener_color_perfil($p['nombre']);
                                echo '<span class="badge bg-' . $color . ' badge-perfil">' . strtoupper(str_replace('_', ' ', $p['nombre'])) . '</span>';
                            }
                        }
                        ?>
                    </td>
                    <td>
                        <?php echo $u['nombre_departamento'] ? $u['nombre_departamento'] : '—'; ?>
                    </td>
                    <td>
                        <?php echo $u['nom_colegio'] ? substr($u['nom_colegio'], 0, 20) : '—'; ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal<?php echo $u['id']; ?>">Editar</button>
                    </td>
                </tr>

                <!-- Modal -->
                <div class="modal fade" id="modal<?php echo $u['id']; ?>">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5><?php echo $u['nombre']; ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST">
                                    <input type="hidden" name="id_usuario" value="<?php echo $u['id']; ?>">
                                    
                                    <!-- Perfiles (Checkboxes) -->
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Perfiles:</strong></label>
                                        <?php 
                                        $perfiles_usuario = obtener_perfiles_usuario($db, $u['id']);
                                        $perfiles_temp = $db->query("SELECT * FROM perfiles WHERE estado = 1 ORDER BY id_perfil");
                                        while ($p = $perfiles_temp->fetch_assoc()): 
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="perfiles[]" value="<?php echo $p['id_perfil']; ?>" id="perfil<?php echo $u['id'] . '_' . $p['id_perfil']; ?>" <?php echo in_array($p['id_perfil'], $perfiles_usuario) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="perfil<?php echo $u['id'] . '_' . $p['id_perfil']; ?>">
                                                <?php echo strtoupper(str_replace('_', ' ', $p['nombre'])); ?>
                                            </label>
                                        </div>
                                        <?php endwhile; ?>
                                    </div>

                                    <!-- Departamento -->
                                    <div class="mb-3">
                                        <label>Departamento:</label>
                                        <select name="id_departamento" class="form-select form-select-sm">
                                            <option value="0">-- Sin departamento --</option>
                                            <?php 
                                            $deptos_temp = $db->query("SELECT * FROM departamentos WHERE estado = 1 ORDER BY nombre_departamento");
                                            while ($d = $deptos_temp->fetch_assoc()): 
                                            ?>
                                            <option value="<?php echo $d['id_departamento']; ?>" <?php echo $u['id_departamento'] == $d['id_departamento'] ? 'selected' : ''; ?>>
                                                <?php echo $d['nombre_departamento']; ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <!-- Colegio -->
                                    <div class="mb-3">
                                        <label>Colegio:</label>
                                        <select name="id_colegio" class="form-select form-select-sm">
                                            <option value="0">-- Selecciona --</option>
                                            <?php 
                                            $colegios_temp = $db->query("SELECT * FROM colegio WHERE estado = 1 ORDER BY nom_colegio");
                                            while ($col = $colegios_temp->fetch_assoc()): 
                                            ?>
                                            <option value="<?php echo $col['id_colegio']; ?>" <?php echo $u['id_colegio'] == $col['id_colegio'] ? 'selected' : ''; ?>>
                                                <?php echo substr($col['nom_colegio'], 0, 30); ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <!-- Botones -->
                                    <div class="d-flex gap-2">
                                        <button type="submit" name="accion" value="guardar" class="btn btn-primary btn-sm flex-grow-1">💾 Guardar</button>
                                        <button type="submit" name="accion" value="eliminar" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar permisos de este usuario?')">🗑️ Eliminar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>