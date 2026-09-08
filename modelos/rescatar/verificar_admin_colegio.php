<?php
/**
 * verificar_admin_colegio.php
 * Indica si un colegio tiene administrador asignado y, si no lo tiene,
 * devuelve la lista de usuarios de ese colegio candidatos a serlo.
 *
 * Entrada  (POST): id_colegio
 * Salida   (JSON): { tiene_admin, id_colegio, nom_colegio, usuarios: [...] }
 *
 * Solo disponible para el Super Admin (usuario_perfil.id_perfil = 3).
 */

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../../class/conexion.php';

$db = new MySQL('', '', '');

$idSesion  = isset($_SESSION['id']) ? (int) $_SESSION['id'] : 0;
$idColegio = isset($_POST['id_colegio']) ? (int) $_POST['id_colegio'] : 0;

if ($idColegio <= 0) {
    echo json_encode(['error' => 'ID de colegio no proporcionado.']);
    exit;
}

// ── Autorización: solo Super Admin ────────────────────────────────────────
$esSuperAdmin = false;
if ($idSesion > 0) {
    $resPerm = $db->consulta(
        "SELECT 1 FROM usuario_perfil WHERE id_usuario = '$idSesion' AND id_perfil = 3 LIMIT 1"
    );
    $esSuperAdmin = (bool) $db->fetch_assoc($resPerm);
}
if (!$esSuperAdmin) {
    echo json_encode(['error' => 'Sin permiso.']);
    exit;
}

// ── Nombre del colegio ───────────────────────────────────────────────────
$resCol = $db->consulta("SELECT nom_colegio FROM colegio WHERE id_colegio = '$idColegio' LIMIT 1");
$rowCol = $db->fetch_assoc($resCol);
$nomColegio = $rowCol ? $rowCol['nom_colegio'] : ('Colegio ' . $idColegio);

// ── ¿Tiene administrador? ────────────────────────────────────────────────
$resAdmin = $db->consulta(
    "SELECT COUNT(*) AS n FROM usuario_colegio
     WHERE id_colegio = '$idColegio' AND estado = 1 AND es_admin_colegio = 1"
);
$rowAdmin = $db->fetch_assoc($resAdmin);
$tieneAdmin = ((int) $rowAdmin['n']) > 0;

// ── Usuarios candidatos (solo si no hay admin) ───────────────────────────
$usuarios = [];
if (!$tieneAdmin) {
    $resU = $db->consulta("
        SELECT uc.id AS id_relacion, uc.id_usuario, uc.id_colegio,
               u.nombre, u.apellido_paterno, u.email, u.estado
        FROM usuario_colegio uc
        JOIN usuarios u ON u.id = uc.id_usuario
        WHERE uc.id_colegio = '$idColegio'
          AND uc.estado = 1
          AND COALESCE(uc.es_admin_colegio, 0) = 0
        ORDER BY u.nombre ASC, u.apellido_paterno ASC
    ");
    while ($ru = $db->fetch_assoc($resU)) {
        $usuarios[] = [
            'id_relacion'      => (int) $ru['id_relacion'],
            'id_usuario'       => (int) $ru['id_usuario'],
            'id_colegio'       => (int) $ru['id_colegio'],
            'nombre'           => $ru['nombre'],
            'apellido_paterno' => $ru['apellido_paterno'],
            'email'            => $ru['email'],
            'estado'           => $ru['estado'],
        ];
    }
}

echo json_encode([
    'tiene_admin' => $tieneAdmin,
    'id_colegio'  => $idColegio,
    'nom_colegio' => $nomColegio,
    'usuarios'    => $usuarios,
], JSON_UNESCAPED_UNICODE);
