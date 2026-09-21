<?php
/**
 * guardar_admin_colegio.php
 * Marca / desmarca a un usuario como administrador de un colegio.
 *
 * Reglas:
 *   - Solo el Super Admin (usuario_perfil.id_perfil = 3) puede ejecutar esto.
 *   - Un colegio tiene como máximo UN administrador: al asignar uno nuevo se
 *     desmarca automáticamente al anterior.
 *
 * Espera por POST: id_relacion (uc.id, opcional), id_usuario, id_colegio,
 *                  es_admin_colegio (1|0).
 * Responde JSON: { success, message, colegios_sin_admin: [nombres...] }
 */

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../../class/conexion.php';

$db = new MySQL('', '', '');

function responder(bool $ok, string $msg, array $extra = []): void
{
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Autenticación ───────────────────────────────────────────────────────────
$idSesion = isset($_SESSION['id']) ? (int) $_SESSION['id'] : 0;
if ($idSesion <= 0) {
    responder(false, 'Sesión no válida. Vuelve a iniciar sesión.');
}

// ── Autorización: solo Super Admin (id_perfil = 3) ─────────────────────────
$resPerfil = $db->consulta(
    "SELECT 1 FROM usuario_perfil WHERE id_usuario = $idSesion AND id_perfil = 3 LIMIT 1"
);
if (!$db->fetch_assoc($resPerfil)) {
    responder(false, 'No tienes permiso para realizar esta acción.');
}

// ── Entrada ────────────────────────────────────────────────────────────────
$idRelacion = isset($_POST['id_relacion']) ? (int) $_POST['id_relacion'] : 0;
$idUsuario  = isset($_POST['id_usuario'])  ? (int) $_POST['id_usuario']  : 0;
$idColegio  = isset($_POST['id_colegio'])  ? (int) $_POST['id_colegio']  : 0;
$esAdmin    = (isset($_POST['es_admin_colegio']) && (int) $_POST['es_admin_colegio'] === 1) ? 1 : 0;

if ($idUsuario <= 0 || $idColegio <= 0) {
    responder(false, 'Datos incompletos.');
}

// ── Ubicar la relación usuario-colegio activa ──────────────────────────────
if ($idRelacion > 0) {
    $resRel = $db->consulta(
        "SELECT id FROM usuario_colegio
         WHERE id = $idRelacion AND id_usuario = $idUsuario AND id_colegio = $idColegio AND estado = 1
         LIMIT 1"
    );
} else {
    $resRel = $db->consulta(
        "SELECT id FROM usuario_colegio
         WHERE id_usuario = $idUsuario AND id_colegio = $idColegio AND estado = 1
         ORDER BY id DESC LIMIT 1"
    );
}
$rel = $db->fetch_assoc($resRel);
if (!$rel) {
    responder(false, 'El usuario no está vinculado a ese colegio.');
}
$idRelacion = (int) $rel['id'];

// ── Aplicar cambio ────────────────────────────────────────────────────────
if ($esAdmin === 1) {
    // Un solo administrador por colegio: quitar a cualquier otro.
    if ($db->guardar(
        "UPDATE usuario_colegio SET es_admin_colegio = 0
         WHERE id_colegio = $idColegio AND estado = 1 AND id <> $idRelacion"
    ) !== 0) {
        responder(false, 'No se pudo actualizar el administrador anterior.');
    }
}

if ($db->guardar("UPDATE usuario_colegio SET es_admin_colegio = $esAdmin WHERE id = $idRelacion") !== 0) {
    responder(false, 'No se pudo guardar el cambio.');
}

// ── Recalcular colegios sin administrador (para refrescar el aviso) ────────
$colegiosSinAdmin = [];
$resSin = $db->consulta("
    SELECT c.id_colegio, c.nom_colegio
    FROM colegio c
    WHERE c.estado = 1
      AND NOT EXISTS (
          SELECT 1 FROM usuario_colegio uc
          WHERE uc.id_colegio = c.id_colegio AND uc.estado = 1 AND uc.es_admin_colegio = 1
      )
    ORDER BY c.nom_colegio ASC
");
while ($s = $db->fetch_assoc($resSin)) {
    $colegiosSinAdmin[] = ['id' => (int) $s['id_colegio'], 'nom' => $s['nom_colegio']];
}

responder(
    true,
    $esAdmin === 1 ? 'Administrador asignado correctamente.' : 'Se quitó el administrador del colegio.',
    ['colegios_sin_admin' => $colegiosSinAdmin]
);
