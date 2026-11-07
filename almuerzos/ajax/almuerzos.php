<?php
/**
 * Almuerzos (JSON)
 *   GET  accion=estado                → horario, menú y pedido de hoy, resumen del mes, cocina (encargado)
 *   GET  accion=mes&mes=AAAA-MM       → menú del mes y mis pedidos de ese mes
 *   POST accion=pedir&opcion=…[&fecha=AAAA-MM-DD] → guarda mi pedido de hoy, o de un día de la
 *                                        semana siguiente si la planificación (sáb-dom) está abierta
 *   POST accion=publicar&mes=…&dias=… → publica el menú del mes (solo encargado)
 *   POST accion=simular&valor=0|1     → simular horario abierto (solo encargado, solo QA)
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');

require_once dirname(__DIR__, 2) . '/clases/Session.php';
require_once dirname(__DIR__) . '/class/Almuerzos.php';
Session::iniciar();

function alm_json(array $r, int $codigo = 200): never
{
    http_response_code($codigo);
    echo json_encode($r, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$idUsuario = (int) ($_SESSION['id'] ?? 0);
if ($idUsuario <= 0) {
    alm_json(['ok' => false, 'mensaje' => 'Sesión expirada. Vuelve a iniciar sesión.'], 401);
}
try {
    $alm = new Almuerzos(Conexion::getInstance('sistema_panel_central'));
    if (!$alm->instalado()) {
        alm_json(['ok' => false, 'mensaje' => 'Faltan las tablas de almuerzos. Ejecuta almuerzos/sql/03_modelo_almuerzos.sql.'], 503);
    }
    $encargado = $alm->esEncargado($idUsuario);
    $simular = $encargado && Almuerzos::PERMITIR_SIMULAR_HORARIO && !empty($_SESSION['alm_simular']);

    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($metodo === 'GET') {
        $accion = (string) ($_GET['accion'] ?? 'estado');

        if ($accion === 'estado') {
            $h = $alm->horario($simular);
            $hoy = new DateTimeImmutable($h['hoy']);
            $pedidosMes = $alm->pedidosUsuarioMes($idUsuario, (int) $hoy->format('Y'), (int) $hoy->format('n'));
            // Próximos 4 días hábiles con menú
            $proximos = [];
            for ($d = $hoy->modify('+1 day'), $i = 0; count($proximos) < 4 && $i < 40; $d = $d->modify('+1 day'), $i++) {
                if (Almuerzos::esDiaHabil($d) && ($m = $alm->menuDia($d->format('Y-m-d')))) {
                    $proximos[] = $m;
                }
            }
            $res = [
                'ok' => true,
                'horario' => $h,
                'menu_hoy' => $alm->menuDia($h['hoy']),
                'mi_pedido' => $pedidosMes[$h['hoy']] ?? null,
                'mis_pedidos_mes' => $pedidosMes,
                'proximos' => $proximos,
                'semana' => $alm->semana($idUsuario, $simular),
                'encargado' => $encargado,
            ];
            if ($encargado) {
                $res['cocina'] = Almuerzos::esDiaHabil($hoy) ? Almuerzos::conteo($alm->pedidosDelDia($h['hoy'])) : null;
            }
            alm_json($res);
        }

        if ($accion === 'mes') {
            $mes = (string) ($_GET['mes'] ?? '');
            if (!preg_match('/^(\d{4})-(\d{2})$/', $mes, $m)) {
                alm_json(['ok' => false, 'mensaje' => 'Mes no válido.'], 400);
            }
            alm_json(['ok' => true, 'mes' => $mes, 'menu' => $alm->menuMes((int) $m[1], (int) $m[2]),
                'pedidos' => $alm->pedidosUsuarioMes($idUsuario, (int) $m[1], (int) $m[2])]);
        }
        alm_json(['ok' => false, 'mensaje' => 'Acción no válida.'], 400);
    }

    if ($metodo !== 'POST') {
        alm_json(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
    }
    if (empty($_SESSION['csrf_almuerzos']) || !hash_equals((string) $_SESSION['csrf_almuerzos'], (string) ($_POST['csrf'] ?? ''))) {
        alm_json(['ok' => false, 'mensaje' => 'La sesión expiró. Recarga la página.'], 419);
    }

    switch ((string) ($_POST['accion'] ?? '')) {
        case 'pedir':
            $fecha = (string) ($_POST['fecha'] ?? '');
            if ($fecha !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                alm_json(['ok' => false, 'mensaje' => 'Fecha no válida.'], 422);
            }
            $r = $alm->pedir($idUsuario, (string) ($_POST['opcion'] ?? ''), $simular, $fecha);
            alm_json(['ok' => true, 'pedido' => $r]);

        case 'publicar':
            if (!$encargado) {
                alm_json(['ok' => false, 'mensaje' => 'Solo el encargado de almuerzos puede publicar el menú.'], 403);
            }
            if (!preg_match('/^(\d{4})-(\d{2})$/', (string) ($_POST['mes'] ?? ''), $m)) {
                alm_json(['ok' => false, 'mensaje' => 'Mes no válido.'], 422);
            }
            $dias = json_decode((string) ($_POST['dias'] ?? ''), true);
            if (!is_array($dias) || count($dias) > 31) {
                alm_json(['ok' => false, 'mensaje' => 'El menú enviado no es válido.'], 422);
            }
            $n = $alm->publicarMes((int) $m[1], (int) $m[2], $dias, $idUsuario);
            alm_json(['ok' => true, 'mensaje' => "Menú publicado: {$n} días."]);

        case 'simular':
            if (!$encargado || !Almuerzos::PERMITIR_SIMULAR_HORARIO) {
                alm_json(['ok' => false, 'mensaje' => 'No disponible.'], 403);
            }
            $_SESSION['alm_simular'] = ($_POST['valor'] ?? '') === '1';
            alm_json(['ok' => true]);
    }
    alm_json(['ok' => false, 'mensaje' => 'Acción no válida.'], 400);
} catch (PDOException $e) {
    error_log('[almuerzos] BD: ' . $e->getMessage());
    alm_json(['ok' => false, 'mensaje' => 'No fue posible guardar. Intenta nuevamente.'], 500);
} catch (RuntimeException $e) {
    alm_json(['ok' => false, 'mensaje' => $e->getMessage()], 422);
} catch (Throwable $e) {
    error_log('[almuerzos] ' . $e->getMessage());
    alm_json(['ok' => false, 'mensaje' => 'Ocurrió un error inesperado.'], 500);
}
