<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../helpers/tickets.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responder_json(['ok' => false, 'error' => 'Método no permitido.'], 405);
}

$datos = entrada_ajax();
$actorId = (int) ($_SESSION['id'] ?? 0);
$solicitanteId = (int) ($datos['solicitante_id'] ?? $actorId);
$csrf = (string) ($datos['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

if (!ticket_csrf_valido($csrf)) {
    responder_json(['ok' => false, 'error' => 'La sesión venció. Recarga la página.'], 419);
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $esGlobal = es_administrador_global($actorId, $db);
    $esAdminColegio = es_admin_colegio($actorId, null, $db);
    $puedeElegir = $esGlobal || $esAdminColegio;

    if (!puede_crear_ticket_para($actorId, $solicitanteId, $db)) {
        responder_json(['ok' => false, 'error' => 'No puedes crear tickets para ese usuario.'], 403);
    }

    $categorias = $db->fetchAll(
        'SELECT id_categoria, nombre_categoria FROM categoria_de_ticket WHERE estado = 1 ORDER BY orden ASC, nombre_categoria ASC'
    );
    $usuarios = [];
    if ($puedeElegir) {
        if ($esGlobal) {
            $usuarios = $db->fetchAll(
                "SELECT id, CONCAT_WS(' ', nombre, apellido_paterno) AS nombre
                   FROM usuarios
                  WHERE LOWER(estado) = 'activo'
               ORDER BY nombre, apellido_paterno"
            );
        } else {
            $usuarios = $db->fetchAll(
                "SELECT DISTINCT u.id, CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS nombre
                   FROM usuario_colegio administrador
              LEFT JOIN perfiles p ON p.id_perfil = administrador.id_perfil
                   JOIN usuario_colegio uc
                     ON uc.id_colegio = administrador.id_colegio AND uc.estado = 1
                   JOIN usuarios u ON u.id = uc.id_usuario AND LOWER(u.estado) = 'activo'
                  WHERE administrador.id_usuario = ? AND administrador.estado = 1
                    AND (administrador.es_admin_colegio = 1 OR LOWER(p.nombre) IN ('admin colegio','admin_colegio','administrador colegio'))
               ORDER BY nombre",
                [$actorId]
            );
        }
    }

    $solicitante = $db->fetchOne(
        "SELECT u.id, CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS nombre,
                colegio_usuario.id_colegio, c.nom_colegio
           FROM usuarios u
      LEFT JOIN (
                    SELECT id_usuario, MIN(id_colegio) AS id_colegio
                      FROM usuario_colegio
                     WHERE estado = 1
                  GROUP BY id_usuario
                ) colegio_usuario ON colegio_usuario.id_usuario = u.id
      LEFT JOIN colegio c ON c.id_colegio = colegio_usuario.id_colegio AND c.estado = 1
          WHERE u.id = ? AND LOWER(u.estado) = 'activo'
          LIMIT 1",
        [$solicitanteId]
    );
    if (!$solicitante) {
        responder_json(['ok' => false, 'error' => 'El usuario seleccionado no está disponible.'], 404);
    }

    responder_json([
        'ok' => true,
        'puede_elegir_solicitante' => $puedeElegir,
        'usuario_sesion_id' => $actorId,
        'usuarios' => $usuarios,
        'categorias' => $categorias,
        'solicitante' => [
            'id' => (int) $solicitante['id'],
            'nombre' => (string) $solicitante['nombre'],
            'colegio_id' => (int) ($solicitante['id_colegio'] ?? 0),
            'colegio' => (string) ($solicitante['nom_colegio'] ?? ''),
        ],
    ]);
} catch (Throwable $ex) {
    error_log('Error al cargar formulario dinámico de ticket: ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible cargar los datos del formulario.'], 500);
}
