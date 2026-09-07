<?php
declare(strict_types=1);

/** AJAX: guarda asignaciones de categorías a técnicos. */

require_once __DIR__ . '/../../ajax/_bootstrap.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responder_json(['success' => false, 'ok' => false, 'message' => 'Método no permitido.'], 405);
}

$datos = entrada_ajax();
$csrfRecibido = (string) ($datos['csrf'] ?? '');
$csrfSesion = (string) ($_SESSION['csrf_admin_categorias'] ?? '');
if ($csrfSesion === '' || !hash_equals($csrfSesion, $csrfRecibido)) {
    responder_json(['success' => false, 'ok' => false, 'message' => 'La sesión de edición expiró. Recarga la página.'], 419);
}

$cambios = $datos['cambios'] ?? null;
if (!is_array($cambios) || !$cambios) {
    responder_json(['success' => false, 'ok' => false, 'message' => 'No se recibieron cambios válidos.'], 422);
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $pdo = $db->getPDO();
    $pdo->beginTransaction();
    $procesados = 0;

    try {
        foreach ($cambios as $cambio) {
            if (!is_array($cambio)) {
                continue;
            }
            $idTecnico = (int) ($cambio['id_tecnico'] ?? 0);
            $categorias = $cambio['categorias'] ?? [];
            if ($idTecnico <= 0 || !is_array($categorias)) {
                continue;
            }

            $tecnicoValido = $db->fetchOne(
                "SELECT id FROM usuarios
                  WHERE id = ? AND id_area_trabajo = 1 AND id <> 27
                    AND LOWER(estado) = 'activo' LIMIT 1",
                [$idTecnico]
            );
            if (!$tecnicoValido) {
                throw new RuntimeException('Uno de los técnicos ya no está disponible.');
            }

            foreach ($categorias as $categoria) {
                if (!is_array($categoria)) {
                    continue;
                }
                $idCategoria = (int) ($categoria['id_categoria'] ?? 0);
                $asignada = filter_var($categoria['asignada'] ?? false, FILTER_VALIDATE_BOOLEAN);
                if ($idCategoria <= 0) {
                    continue;
                }

                $categoriaValida = $db->fetchOne(
                    'SELECT id_categoria FROM categoria_de_ticket WHERE id_categoria = ? AND estado = 1 AND id_categoria <> 10 LIMIT 1',
                    [$idCategoria]
                );
                if (!$categoriaValida) {
                    throw new RuntimeException('Una de las categorías ya no está disponible.');
                }

                $relacion = $db->fetchOne(
                    'SELECT id_categoria_tecnico FROM categoria_tecnico WHERE id_tecnico = ? AND id_categoria = ? LIMIT 1',
                    [$idTecnico, $idCategoria]
                );
                if ($asignada && !$relacion) {
                    $db->execute('INSERT INTO categoria_tecnico (id_tecnico, id_categoria) VALUES (?, ?)', [$idTecnico, $idCategoria]);
                    $procesados++;
                } elseif (!$asignada && $relacion) {
                    $db->execute('DELETE FROM categoria_tecnico WHERE id_tecnico = ? AND id_categoria = ?', [$idTecnico, $idCategoria]);
                    $procesados++;
                }
            }
        }
        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $ex;
    }

    responder_json([
        'success' => true,
        'ok' => true,
        'message' => $procesados === 1 ? 'Se guardó 1 cambio.' : "Se guardaron {$procesados} cambios.",
        'cambios_procesados' => $procesados,
    ]);
} catch (RuntimeException $ex) {
    responder_json(['success' => false, 'ok' => false, 'message' => $ex->getMessage()], 422);
} catch (Throwable $ex) {
    error_log('Error al guardar categorías de técnicos: ' . $ex->getMessage());
    responder_json(['success' => false, 'ok' => false, 'message' => 'No fue posible guardar los cambios en la base de datos.'], 500);
}
