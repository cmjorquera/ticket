<?php
/**
 * Responder una invitación (página pública, sin sesión): se llega desde los
 * botones Sí / Quizás / No del correo (eventos/class/CorreoEvento.php).
 * El enlace solo PRESELECCIONA la respuesta; se guarda al confirmar con el botón
 * (así los antivirus de correo que abren enlaces no responden por la persona).
 * Lógica: eventos/logica/LOGICA.txt
 */
declare(strict_types=1);

require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/class/Eventos.php';
require_once __DIR__ . '/class/CorreoEvento.php';

header('Content-Type: text/html; charset=UTF-8');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex');

$token = (string) ($_GET['t'] ?? $_POST['t'] ?? '');
$pedida = (string) ($_GET['r'] ?? '');
$ev = new Eventos(Conexion::getInstance('sistema_panel_central'));
$guardada = '';
try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $ev->responderToken($token, (string) ($_POST['respuesta'] ?? ''))) {
        $guardada = (string) $_POST['respuesta'];
    }
    $e = $ev->porToken($token);
} catch (Throwable $ex) {
    error_log('[eventos/responder] ' . $ex->getMessage());
    $e = null;
}
$h = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$opciones = ['si' => ['Sí, asistiré', 'bi-check-circle-fill', '#2f7d32'], 'quizas' => ['Quizás', 'bi-question-circle-fill', '#8a6200'], 'no' => ['No asistiré', 'bi-x-circle-fill', '#6b7280']];
$actual = $guardada ?: ($e['respuesta'] ?? 'pendiente');
$marcada = $guardada ?: (isset($opciones[$pedida]) ? $pedida : $actual);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title>Responder invitación · SEDUC</title>
<link rel="icon" type="image/png" href="../imagenes/logo_seduc.png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}body{min-height:100vh;display:grid;place-items:center;padding:20px 14px;font-family:Inter,system-ui,sans-serif;color:#111827;background:linear-gradient(180deg,#15335a,#0f2440)}
.t{width:min(460px,100%);background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 30px 80px rgba(0,0,0,.35)}
.c{padding:18px 22px;background:#16365c;color:#fff;border-bottom:4px solid #e8a33d}.c small{opacity:.8;font-size:12px}.c h1{font-size:19px;margin-top:3px}
.c h1.x{text-decoration:line-through;opacity:.8}.b{padding:20px 22px}.d{display:grid;gap:8px;margin-bottom:18px;font-size:14px;color:#374151}.d i{color:#16365c;margin-right:6px}
form{display:grid;gap:8px}label{display:flex;align-items:center;gap:10px;padding:13px 14px;border:1.5px solid #e5e7eb;border-radius:12px;cursor:pointer;font-weight:600}
label input{accent-color:#16365c;width:18px;height:18px}label:has(input:checked){border-color:#16365c;background:#eaf1f8}
button{margin-top:6px;padding:13px;border:0;border-radius:12px;background:#16365c;color:#fff;font:600 15px Inter,sans-serif;cursor:pointer}
.ok{display:flex;gap:8px;align-items:center;padding:12px 14px;margin-bottom:14px;border-radius:12px;background:#e8f5e9;color:#2f7d32;font-weight:600}
.v{text-align:center;padding:34px 22px}.v i{font-size:40px;color:#b42318}.v p{color:#6b7280;margin-top:8px}.n{font-size:12px;color:#9ca3af;margin-top:14px;text-align:center}
</style>
</head>
<body>
<main class="t">
<?php if (!$e): ?>
  <div class="v"><i class="bi bi-calendar-x"></i><h1>Invitación no encontrada</h1><p>El enlace no es válido o el evento ya no existe.</p></div>
<?php else: $cancelado = $e['estado'] === 'cancelado'; ?>
  <div class="c"><small><?= $cancelado ? 'Evento cancelado' : 'Invitación' ?> · SEDUC</small><h1 class="<?= $cancelado ? 'x' : '' ?>"><?= $h($e['titulo']) ?></h1></div>
  <div class="b">
    <div class="d">
      <span><i class="bi bi-clock"></i><?= $h(CorreoEvento::cuando($e)) ?></span>
      <?php if ($e['ubicacion']): ?><span><i class="bi bi-geo-alt"></i><?= $h($e['ubicacion']) ?></span><?php endif; ?>
      <span><i class="bi bi-person"></i>Responsable: <?= $h($e['responsable']) ?></span>
    </div>
    <?php if ($cancelado): ?>
      <p>Este evento fue cancelado: no es necesario responder.</p>
    <?php else: ?>
      <?php if ($guardada): ?><div class="ok" role="status"><i class="bi bi-check2-circle"></i> ¡Listo! Respondiste: <?= $h($opciones[$guardada][0]) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="t" value="<?= $h($token) ?>">
        <?php foreach ($opciones as $val => [$txt, $ic, $col]): ?>
          <label><input type="radio" name="respuesta" value="<?= $val ?>" <?= $marcada === $val ? 'checked' : '' ?> required><i class="bi <?= $ic ?>" style="color:<?= $col ?>"></i> <?= $txt ?></label>
        <?php endforeach; ?>
        <button type="submit"><?= $actual === 'pendiente' ? 'Confirmar respuesta' : 'Cambiar respuesta' ?></button>
      </form>
      <p class="n">Hola <?= $h($e['nombre'] ?: $e['email']) ?>, puedes cambiar tu respuesta cuando quieras con este mismo enlace.</p>
    <?php endif; ?>
  </div>
<?php endif; ?>
</main>
</body>
</html>
