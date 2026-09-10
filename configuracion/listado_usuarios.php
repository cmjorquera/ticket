<?php
declare(strict_types=1);

require_once __DIR__ . '/../clases/Session.php';
require_once __DIR__ . '/../clases/Conexion.php';

Session::iniciar();
if (!isset($_SESSION['id']) || (int) $_SESSION['id'] <= 0) {
    header('Location: ../index.php');
    exit;
}

$buscarEntrada = trim((string) ($_GET['buscar'] ?? ''));
$buscar = function_exists('mb_substr')
    ? mb_substr($buscarEntrada, 0, 100, 'UTF-8')
    : substr($buscarEntrada, 0, 100);
$usuarios = [];
$errorCarga = null;

try {
    // Las tablas nuevas de perfiles y jefaturas viven en logica_permisos.
    $conexion = Conexion::getInstance('logica_permisos');
    $pdo = $conexion->getPDO();

    $sql = "SELECT u.id, u.nombre, u.apellido_paterno, u.apellido_materno,
                   u.email, u.id_area_trabajo,
                   GROUP_CONCAT(DISTINCT p.nombre ORDER BY p.id_perfil SEPARATOR ',') AS perfiles,
                   MAX(d.nombre_departamento) AS nombre_departamento,
                   MAX(j.id_departamento) AS id_departamento,
                   MAX(j.id_colegio) AS id_colegio,
                   MAX(c.nom_colegio) AS nom_colegio
              FROM usuarios u
         LEFT JOIN usuario_perfil up
                ON up.id_usuario = u.id AND up.estado = 1
         LEFT JOIN perfiles p
                ON p.id_perfil = up.id_perfil AND p.estado = 1
         LEFT JOIN jefatura_departamento j
                ON j.id_usuario = u.id AND j.estado = 1
         LEFT JOIN departamentos d
                ON d.id_departamento = j.id_departamento
         LEFT JOIN colegio c
                ON c.id_colegio = j.id_colegio
             WHERE LOWER(u.estado) = 'activo'";

    $parametros = [];
    if ($buscar !== '') {
        $sql .= " AND (u.nombre LIKE :buscar_nombre
                       OR u.apellido_paterno LIKE :buscar_apellido
                       OR u.email LIKE :buscar_email)";
        $termino = '%' . $buscar . '%';
        $parametros = [
            ':buscar_nombre' => $termino,
            ':buscar_apellido' => $termino,
            ':buscar_email' => $termino,
        ];
    }

    $sql .= ' GROUP BY u.id, u.nombre, u.apellido_paterno, u.apellido_materno,
                       u.email, u.id_area_trabajo
              ORDER BY u.nombre ASC, u.apellido_paterno ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $ex) {
    error_log('Error al cargar listado de usuarios y permisos: ' . $ex->getMessage());
    $errorCarga = 'No fue posible cargar el listado de usuarios.';
}

function e_listado(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function clase_badge_perfil(string $perfil): string
{
    $normalizado = strtolower(trim($perfil));
    $normalizado = str_replace([' ', '-'], '_', $normalizado);
    return match ($normalizado) {
        'super_admin', 'superadmin' => 'danger',
        'tecnico', 'técnico' => 'warning',
        'admin_area', 'administrador_area' => 'success',
        'admin_colegio', 'administrador_colegio' => 'purple',
        'usuario' => 'info',
        default => 'secondary',
    };
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Usuarios</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #163A5F;
            --secondary: #3F6EA6;
        }
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .container-main {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 15px;
        }
        .header-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .header-section h1 {
            color: var(--primary);
            margin-bottom: 20px;
            font-weight: 700;
        }
        .filtros {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .filtros input, .filtros select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-filtrar, .btn-limpiar {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-filtrar {
            background-color: var(--primary);
            color: white;
        }
        .btn-filtrar:hover {
            background-color: #0f2a47;
        }
        .btn-limpiar {
            background-color: #e9ecef;
            color: #495057;
        }
        .btn-limpiar:hover {
            background-color: #dee2e6;
        }
        .tabla-usuarios {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .tabla-usuarios table {
            margin-bottom: 0;
        }
        .tabla-usuarios thead {
            background-color: var(--primary);
            color: white;
        }
        .tabla-usuarios thead th {
            padding: 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        .tabla-usuarios tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.3s;
        }
        .tabla-usuarios tbody tr:hover {
            background-color: #f9f9f9;
        }
        .tabla-usuarios td {
            padding: 12px 15px;
            vertical-align: middle;
            font-size: 14px;
        }
        .badge {
            padding: 6px 12px;
            font-weight: 600;
            border-radius: 20px;
            font-size: 11px;
            margin-right: 4px;
            display: inline-block;
        }
        .bg-purple { background-color: #9966cc !important; }
        .info-usuarios {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-users"></i> Listado de Usuarios
            </span>
        </div>
    </nav>

    <div class="container-main">
        <div class="header-section">
            <h1><i class="fas fa-users"></i> Usuarios y Permisos</h1>

            <form method="GET" class="filtros">
                <input type="text" name="buscar" placeholder="🔍 Buscar por nombre o email..."
                       value="<?php echo e_listado($buscar); ?>">

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-filtrar">🔍 Buscar</button>
                    <a href="?" class="btn-limpiar">↺ Limpiar</a>
                </div>
            </form>

            <div class="info-usuarios">
                📊 Total: <strong><?php echo count($usuarios); ?></strong> usuarios encontrados
            </div>
            <?php if ($errorCarga): ?>
                <div class="alert alert-danger mb-0" role="alert"><?php echo e_listado($errorCarga); ?></div>
            <?php endif; ?>
        </div>

        <div class="tabla-usuarios">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Perfiles</th>
                        <th>Departamento</th>
                        <th>Colegio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (count($usuarios) > 0):
                        foreach ($usuarios as $usuario):
                    ?>
                    <tr>
                        <td><strong>#<?php echo (int) $usuario['id']; ?></strong></td>
                        <td>
                            <strong><?php echo e_listado(trim($usuario['nombre'] . ' ' . $usuario['apellido_paterno'])); ?></strong>
                            <?php if ($usuario['apellido_materno']): ?>
                                <br><small><?php echo e_listado($usuario['apellido_materno']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><small><?php echo e_listado($usuario['email']); ?></small></td>
                        <td>
                            <?php
                            if ($usuario['perfiles']) {
                                $perfiles_array = explode(',', $usuario['perfiles']);
                                foreach ($perfiles_array as $perfil) {
                                    $perfil = trim($perfil);
                                    $color = clase_badge_perfil($perfil);
                                    echo '<span class="badge bg-' . $color . '">' . e_listado(strtoupper(str_replace('_', ' ', $perfil))) . '</span>';
                                }
                            } else {
                                echo '<span class="badge bg-secondary">Sin perfil</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo e_listado($usuario['nombre_departamento'] ?: '—'); ?>
                        </td>
                        <td>
                            <?php echo e_listado($usuario['nom_colegio'] ?: '—'); ?>
                        </td>
                    </tr>
                    <?php
                        endforeach;
                    else:
                    ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                            <i class="fas fa-inbox"></i> No se encontraron usuarios
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
