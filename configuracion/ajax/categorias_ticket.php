<?php
/**
 * Catálogo de categorías de tickets (Configuración → Admin categorías).
 * POST JSON con csrf (csrf_admin_categorias) y action:
 *   crear   nombre, abreviacion, icono, tecnicos[]
 *   editar  id, nombre, abreviacion, icono, tecnicos[]
 *   estado  id                      → activa / desactiva
 *   mover   id, direccion (arriba|abajo)
 * Pueden usarlo el super admin y los funcionarios de Informática de Seduc.
 * Lógica: configuracion/logica/LOGICA_CATEGORIAS.txt
 */
declare(strict_types=1);

require_once __DIR__ . '/../../ajax/_bootstrap.php';
require_once __DIR__ . '/../../clases/RolesUsuario.php';
Sesion::requerir();

const CAT_OTROS = 10;   // categoría por defecto: no se asigna a técnicos ni se desactiva

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responder_json(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
}
$d = entrada_ajax();
$csrf = (string) ($_SESSION['csrf_admin_categorias'] ?? '');
if ($csrf === '' || !hash_equals($csrf, (string) ($d['csrf'] ?? ''))) {
    responder_json(['ok' => false, 'mensaje' => 'La sesión expiró. Recarga la página.'], 419);
}

/** "bi bi-wifi", "bi-wifi" o <i class="bi bi-wifi"> → "bi-wifi" (o '' si no es válido). */
function cat_icono(string $v): string
{
    if (preg_match('/class\s*=\s*["\']([^"\']+)["\']/i', $v, $m)) {
        $v = $m[1];
    }
    foreach (preg_split('/\s+/', trim($v)) ?: [] as $c) {
        if (preg_match('/^bi-[a-z0-9-]{1,40}$/', $c)) {
            return $c;
        }
    }
    return '';
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $roles = new RolesUsuario($db);
    $actor = (int) ($_SESSION['id'] ?? 0);
    if (!$roles->esSuperAdmin($actor) && !$roles->esDeInformaticaSeduc($actor)) {
        responder_json(['ok' => false, 'mensaje' => 'No tienes permiso para administrar categorías.'], 403);
    }
    [$condTec, $paramTec] = $roles->modeloActivo()
        ? RolesUsuario::condicionDepartamento('u')
        : ['u.id_area_trabajo = 1 AND u.id <> 27', []];

    $action = (string) ($d['action'] ?? '');
    $id = (int) ($d['id'] ?? 0);
    if ($action !== 'crear') {
        $cat = $db->fetchOne('SELECT id_categoria, nombre_categoria, estado FROM categoria_de_ticket WHERE id_categoria = ?', [$id]);
        if (!$cat) {
            responder_json(['ok' => false, 'mensaje' => 'La categoría no existe.'], 404);
        }
    }

    if ($action === 'crear' || $action === 'editar') {
        $nombre = mb_substr(trim(preg_replace('/\s+/u', ' ', (string) ($d['nombre'] ?? '')) ?? ''), 0, 80);
        $abrev = mb_substr(trim(preg_replace('/\s+/u', ' ', (string) ($d['abreviacion'] ?? '')) ?? ''), 0, 50);
        $icono = cat_icono((string) ($d['icono'] ?? ''));
        if ($nombre === '') {
            responder_json(['ok' => false, 'mensaje' => 'Escribe el nombre de la categoría.'], 422);
        }
        if ($abrev === '') {
            $abrev = mb_substr($nombre, 0, 20);
        }
        if ($icono === '') {
            $icono = 'bi-tag';
        }
        $repetida = $db->fetchOne(
            'SELECT id_categoria FROM categoria_de_ticket WHERE LOWER(TRIM(nombre_categoria)) = LOWER(?) AND id_categoria <> ? LIMIT 1',
            [$nombre, $id]
        );
        if ($repetida) {
            responder_json(['ok' => false, 'mensaje' => 'Ya existe una categoría con ese nombre.'], 409);
        }
        // Técnicos que la atienden (solo Informática de Seduc)
        $tecnicos = array_values(array_unique(array_filter(array_map('intval', (array) ($d['tecnicos'] ?? [])), static fn ($v) => $v > 0)));
        if ($tecnicos) {
            $marcas = implode(',', array_fill(0, count($tecnicos), '?'));
            $validos = $db->fetchAll(
                "SELECT u.id FROM usuarios u WHERE u.id IN ($marcas) AND {$condTec} AND LOWER(u.estado) = 'activo'",
                array_merge($tecnicos, $paramTec)
            );
            if (count($validos) !== count($tecnicos)) {
                responder_json(['ok' => false, 'mensaje' => 'Uno de los técnicos no está disponible.'], 422);
            }
        }

        $pdo = $db->getPDO();
        $pdo->beginTransaction();
        try {
            if ($action === 'crear') {
                $orden = (int) ($db->fetchOne('SELECT COALESCE(MAX(orden), 0) + 1 AS o FROM categoria_de_ticket')['o'] ?? 1);
                $db->execute(
                    'INSERT INTO categoria_de_ticket (nombre_categoria, abreviacion, icono, orden, estado) VALUES (?, ?, ?, ?, 1)',
                    [$nombre, $abrev, $icono, $orden]
                );
                $id = (int) $db->lastInsertId();
            } else {
                $db->execute(
                    'UPDATE categoria_de_ticket SET nombre_categoria = ?, abreviacion = ?, icono = ? WHERE id_categoria = ?',
                    [$nombre, $abrev, $icono, $id]
                );
            }
            if ($id !== CAT_OTROS) {
                // Deja exactamente los técnicos elegidos (sin tocar técnicos fuera de Informática de Seduc)
                $actuales = array_map('intval', array_column($db->fetchAll(
                    "SELECT ct.id_tecnico FROM categoria_tecnico ct JOIN usuarios u ON u.id = ct.id_tecnico
                      WHERE ct.id_categoria = ? AND {$condTec}",
                    array_merge([$id], $paramTec)
                ), 'id_tecnico'));
                foreach (array_diff($actuales, $tecnicos) as $t) {
                    $db->execute('DELETE FROM categoria_tecnico WHERE id_categoria = ? AND id_tecnico = ?', [$id, $t]);
                }
                foreach (array_diff($tecnicos, $actuales) as $t) {
                    $db->execute('INSERT INTO categoria_tecnico (id_categoria, id_tecnico) VALUES (?, ?)', [$id, $t]);
                }
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
        responder_json(['ok' => true, 'id' => $id, 'mensaje' => $action === 'crear' ? "Categoría \"{$nombre}\" creada." : 'Categoría actualizada.']);
    }

    if ($action === 'estado') {
        if ($id === CAT_OTROS) {
            responder_json(['ok' => false, 'mensaje' => '"Otros" es la categoría por defecto y no se puede desactivar.'], 422);
        }
        $nuevo = (int) $cat['estado'] === 1 ? 0 : 1;
        $db->execute('UPDATE categoria_de_ticket SET estado = ? WHERE id_categoria = ?', [$nuevo, $id]);
        responder_json(['ok' => true, 'estado' => $nuevo, 'mensaje' => $nuevo ? 'Categoría activada.' : 'Categoría desactivada: ya no aparece al crear tickets.']);
    }

    if ($action === 'mover') {
        $dir = (string) ($d['direccion'] ?? '');
        $lista = array_map('intval', array_column($db->fetchAll('SELECT id_categoria FROM categoria_de_ticket ORDER BY orden ASC, id_categoria ASC'), 'id_categoria'));
        $pos = array_search($id, $lista, true);
        $otra = $dir === 'arriba' ? $pos - 1 : $pos + 1;
        if ($pos === false || !in_array($dir, ['arriba', 'abajo'], true) || $otra < 0 || $otra >= count($lista)) {
            responder_json(['ok' => true, 'mensaje' => 'Sin cambios.']);
        }
        [$lista[$pos], $lista[$otra]] = [$lista[$otra], $lista[$pos]];
        $pdo = $db->getPDO();
        $pdo->beginTransaction();
        foreach ($lista as $i => $idCat) {   // deja el orden 1..n sin repetidos
            $db->execute('UPDATE categoria_de_ticket SET orden = ? WHERE id_categoria = ?', [$i + 1, $idCat]);
        }
        $pdo->commit();
        responder_json(['ok' => true, 'mensaje' => 'Orden actualizado.']);
    }

    responder_json(['ok' => false, 'mensaje' => 'Acción no válida.'], 400);
} catch (Throwable $e) {
    error_log('[categorias_ticket] ' . $e->getMessage());
    responder_json(['ok' => false, 'mensaje' => 'No fue posible guardar los cambios.'], 500);
}
