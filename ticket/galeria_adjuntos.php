<?php
declare(strict_types=1);
$depth = '../';
require_once __DIR__ . '/../configuracion/_inicio.php';
$usuarioId = (int) Sesion::get('id', 0);
$ticketId = (int) ($_GET['id'] ?? 0);
$ticket = $ticketId > 0 ? ticket_buscar_para_acceso($ticketId, $db) : false;
if (!$ticket || !puede_ver_ticket($usuarioId, $ticket, $db)) {
    http_response_code(403);
    exit('No tienes permiso para ver los archivos de este ticket.');
}
try {
    $archivos = ticket_listar_adjuntos($db, $ticketId);
} catch (Throwable $e) {
    error_log('[galeria_adjuntos] ' . $e->getMessage());
    http_response_code(500);
    exit('No fue posible consultar los adjuntos.');
}
$imagenes = [];
$documentos = [];
foreach ($archivos as $archivo) {
    if (in_array(strtolower((string) ($archivo['extension'] ?? '')), ['jpg','jpeg','png','webp','gif'], true)) {
        $imagenes[] = $archivo;
    } else {
        $documentos[] = $archivo;
    }
}
?><!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Adjuntos del ticket #<?= $ticketId ?></title>
<style>
*{box-sizing:border-box}body{margin:0;padding:18px;font:14px system-ui,sans-serif;color:#26354a;background:#fff}
h2{font-size:16px;margin:0 0 14px}.stage{position:relative;background:#f0f3f7;border-radius:12px;min-height:260px;display:flex;align-items:center;justify-content:center;overflow:hidden}
.slide{display:flex;align-items:center;justify-content:center;width:100%;height:min(55vh,480px)}.slide[hidden]{display:none}
.slide img{max-width:100%;max-height:100%;object-fit:contain}.nav{position:absolute;top:50%;transform:translateY(-50%);border:1px solid #d9e0e9;border-radius:50%;width:38px;height:38px;background:#fff;color:#17385d;cursor:pointer;font-size:20px}
.prev{left:12px}.next{right:12px}.meta{display:flex;justify-content:space-between;margin:12px 0;color:#52657c}
.thumbs{display:flex;gap:8px;overflow:auto;padding:5px 2px}.thumb{width:66px;height:66px;flex:0 0 66px;border:2px solid transparent;border-radius:8px;padding:2px;background:#f0f3f7;cursor:pointer}.thumb.active{border-color:#17385d}.thumb img{width:100%;height:100%;object-fit:cover}
.docs{margin-top:18px}.docs a{display:block;padding:9px 0;color:#17385d;overflow-wrap:anywhere}
</style></head><body>
<h2>Archivos adjuntos · Ticket #<?= $ticketId ?></h2>
<?php if ($imagenes): ?>
<div class="stage" id="stage">
<?php foreach ($imagenes as $i=>$a): ?>
<a class="slide" href="../ajax/ticket_archivo.php?a=<?= (int) $a['id'] ?>" target="_blank" rel="noopener" <?= $i ? 'hidden' : '' ?>><img src="../ajax/ticket_archivo.php?a=<?= (int) $a['id'] ?>" alt="<?= htmlspecialchars((string)$a['nombre'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy"></a>
<?php endforeach; ?>
<?php if (count($imagenes)>1): ?><button class="nav prev" id="prev" aria-label="Anterior">‹</button><button class="nav next" id="next" aria-label="Siguiente">›</button><?php endif; ?>
</div><div class="meta"><span>Imágenes (<?= count($imagenes) ?>)</span><span id="counter">1 de <?= count($imagenes) ?></span></div>
<div class="thumbs"><?php foreach ($imagenes as $i=>$a): ?><button class="thumb <?= $i===0?'active':'' ?>" type="button" data-index="<?= $i ?>" aria-label="Imagen <?= $i+1 ?>"><img src="../ajax/ticket_archivo.php?a=<?= (int) $a['id'] ?>" alt="" loading="lazy"></button><?php endforeach; ?></div>
<?php endif; ?>
<?php if ($documentos): ?><div class="docs"><strong>Documentos</strong><?php foreach ($documentos as $a): ?><a href="../ajax/ticket_archivo.php?a=<?= (int) $a['id'] ?>" target="_blank" rel="noopener">📎 <?= htmlspecialchars((string)$a['nombre'], ENT_QUOTES, 'UTF-8') ?></a><?php endforeach; ?></div><?php endif; ?>
<?php if (!$archivos): ?><p>Este ticket no tiene archivos adjuntos.</p><?php endif; ?>
<script>
(()=>{const slides=[...document.querySelectorAll('.slide')],thumbs=[...document.querySelectorAll('.thumb')];let index=0;function show(n){if(!slides.length)return;index=(n+slides.length)%slides.length;slides.forEach((el,i)=>el.hidden=i!==index);thumbs.forEach((el,i)=>{el.classList.toggle('active',i===index);el.setAttribute('aria-pressed',String(i===index))});document.getElementById('counter').textContent=(index+1)+' de '+slides.length}
document.getElementById('prev')?.addEventListener('click',()=>show(index-1));document.getElementById('next')?.addEventListener('click',()=>show(index+1));thumbs.forEach((el,i)=>el.addEventListener('click',()=>show(i)));
let x=null;document.getElementById('stage')?.addEventListener('touchstart',e=>x=e.changedTouches[0].clientX,{passive:true});document.getElementById('stage')?.addEventListener('touchend',e=>{if(x===null)return;const dx=e.changedTouches[0].clientX-x;x=null;if(Math.abs(dx)>45)show(index+(dx<0?1:-1))},{passive:true});})();
</script></body></html>