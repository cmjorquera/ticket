<?php
declare(strict_types=1);
require_once __DIR__ . '/_inicio.php';
require_once __DIR__ . '/../clases/Correo.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function salida(array $data, int $status=200): never { http_response_code($status); echo json_encode($data,JSON_UNESCAPED_UNICODE); exit; }
if ((int)Sesion::get('id',0)!==8) salida(['ok'=>false,'mensaje'=>'No autorizado.'],403);
if (empty($_SESSION['csrf_comunicaciones'])) $_SESSION['csrf_comunicaciones']=bin2hex(random_bytes(32));
$accion=(string)($_POST['accion']??$_GET['accion']??'');
if ($_SERVER['REQUEST_METHOD']==='GET') {
    if ($accion!=='opciones') salida(['ok'=>false,'mensaje'=>'Acción inválida.'],400);
    try {
        $usuarios=$db->fetchAll("SELECT id, TRIM(CONCAT(COALESCE(nombre,''),' ',COALESCE(apellido_paterno,''))) nombre, email, id_colegio, id_area_trabajo FROM usuarios WHERE LOWER(estado)='activo' AND email IS NOT NULL AND email <> '' ORDER BY nombre");
        salida(['ok'=>true,'usuarios'=>$usuarios,'csrf'=>$_SESSION['csrf_comunicaciones']]);
    } catch(Throwable $e){error_log('[comunicaciones] '.$e->getMessage());salida(['ok'=>false,'mensaje'=>'No fue posible cargar destinatarios.'],500);}
}
if ($_SERVER['REQUEST_METHOD']!=='POST') salida(['ok'=>false,'mensaje'=>'Método no permitido.'],405);
if (!hash_equals((string)$_SESSION['csrf_comunicaciones'],(string)($_POST['csrf']??''))) salida(['ok'=>false,'mensaje'=>'Token inválido.'],419);
$asunto=trim((string)($_POST['asunto']??''));
$mensaje=trim((string)($_POST['mensaje']??''));
if (mb_strlen($asunto)<5 || mb_strlen($asunto)>150 || mb_strlen($mensaje)<20 || mb_strlen($mensaje)>3000) salida(['ok'=>false,'mensaje'=>'Revisa el asunto (5–150) y mensaje (20–3000 caracteres).'],422);
$ids=array_values(array_unique(array_filter(array_map('intval',(array)($_POST['ids']??[])),fn($id)=>$id>0)));
if (!$ids || count($ids)>200) salida(['ok'=>false,'mensaje'=>'Selecciona entre 1 y 200 usuarios.'],422);
try {
    $marcas=implode(',',array_fill(0,count($ids),'?'));
    $destinatarios=$db->fetchAll("SELECT id,TRIM(CONCAT(COALESCE(nombre,''),' ',COALESCE(apellido_paterno,''))) nombre,email FROM usuarios WHERE id IN ($marcas) AND LOWER(estado)='activo' AND email IS NOT NULL AND email <> '' ORDER BY id",$ids);
    if(count($destinatarios)!==count($ids)) salida(['ok'=>false,'mensaje'=>'La selección contiene usuarios no disponibles. Actualiza la lista.'],422);
    $destinatarios=array_values(array_filter($destinatarios,fn($u)=>filter_var($u['email'],FILTER_VALIDATE_EMAIL)));
    if(count($destinatarios)!==count($ids)) salida(['ok'=>false,'mensaje'=>'Uno o más correos son inválidos.'],422);
    $firma=hash('sha256',$asunto."\n".$mensaje."\n".implode(',',array_column($destinatarios,'id')));
    $html=static function(string $nombre)use($mensaje):string{
        $h=static fn($s)=>htmlspecialchars($s,ENT_QUOTES,'UTF-8');
        $url='https://acceso.seduc.cl/';
        return '<!doctype html><html lang="es"><head><meta charset="UTF-8"></head><body style="margin:0;background:#f4f6f9;font-family:Arial,sans-serif;color:#26364b"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td align="center" style="padding:25px 12px"><table role="presentation" width="600" style="width:100%;max-width:600px;background:#fff" cellspacing="0" cellpadding="0"><tr><td><img src="https://www.siae.cl/email/archivos/1FiVR9m09zWTnZ4IdW1g/header_siae_seduc.png" alt="SEDUC" width="600" style="display:block;width:100%;height:auto"></td></tr><tr><td style="height:4px;background:linear-gradient(to right,#3A913F 50%,#EAAA00 50%,#EAAA00 75%,#005587 75%)"></td></tr><tr><td style="padding:28px 30px"><p style="font-size:12px;letter-spacing:1px;color:#005587;text-align:center">SISTEMA DE CENTRALIZACIÓN</p><h1 style="font-size:26px;text-align:center;color:#003B6F">Nueva versión disponible · v2.0</h1><p>Hola <strong>'.$h($nombre).'</strong>,</p><p style="line-height:1.7;white-space:pre-line">'.nl2br($h($mensaje)).'</p><p style="text-align:center;margin:30px 0"><a href="'.$url.'" style="display:inline-block;padding:15px 26px;border-radius:6px;background:#003B6F;color:white;text-decoration:none;font-weight:bold">Acceder al Sistema de Centralización</a></p><p style="font-size:13px;color:#64748b">Usuario: '.$h($nombre).'. Utiliza tus credenciales habituales. Nunca compartas tu contraseña.</p></td></tr><tr><td><img src="https://www.siae.cl/email/archivos/IuNYrJlxxZN9U0v254lx/Footer_colegios.png" alt="Colegios SEDUC" width="600" style="display:block;width:100%;height:auto"></td></tr></table></td></tr></table></body></html>';
    };
    if($accion==='prueba'){
        $propio=$db->fetchOne('SELECT nombre,apellido_paterno,email FROM usuarios WHERE id=8 LIMIT 1');
        if(!$propio || !filter_var($propio['email'],FILTER_VALIDATE_EMAIL)) salida(['ok'=>false,'mensaje'=>'Tu cuenta no tiene un correo válido.'],422);
        $nombre=trim($propio['nombre'].' '.$propio['apellido_paterno']);
        $r=Correo::enviar([[$propio['email'],$nombre]],'[PRUEBA] '.$asunto,$html($nombre));
        if(!$r['enviado']) salida(['ok'=>false,'mensaje'=>$r['motivo']],502);
        $_SESSION['comunicaciones_prueba']=['firma'=>$firma,'vence'=>time()+1800];
        salida(['ok'=>true,'mensaje'=>'Correo de prueba enviado a '.$propio['email']]);
    }
    if($accion!=='enviar') salida(['ok'=>false,'mensaje'=>'Acción inválida.'],400);
    $prueba=$_SESSION['comunicaciones_prueba']??[];
    if(($prueba['firma']??'')!==$firma || ($prueba['vence']??0)<time()) salida(['ok'=>false,'mensaje'=>'Debes enviar primero una prueba con exactamente esta selección y contenido.'],409);
    // No repetir accidentalmente un envío con el mismo contenido.
    if(($_SESSION['comunicaciones_ultimo_envio']??'')===$firma) salida(['ok'=>false,'mensaje'=>'Este comunicado ya fue enviado en esta sesión.'],409);
    // Tabla de auditoría obligatoria: no enviar sin poder registrar resultados.
    $db->fetchOne('SELECT id FROM comunicaciones_envios LIMIT 1');
    unset($_SESSION['comunicaciones_prueba']);
    $_SESSION['comunicaciones_ultimo_envio']=$firma;
    $ok=0;$fallos=0;
    foreach($destinatarios as $u){
        $r=Correo::enviar([[$u['email'],$u['nombre']]],$asunto,$html($u['nombre']));
        $db->execute('INSERT INTO comunicaciones_envios (id_administrador,id_destinatario,email,asunto,estado,detalle,firma) VALUES (8,?,?,?,?,?,?)',[(int)$u['id'],$u['email'],$asunto,$r['enviado']?'enviado':'error',mb_substr($r['motivo'],0,255),$firma]);
        $r['enviado']?$ok++:$fallos++;
    }
    salida(['ok'=>true,'mensaje'=>"Proceso terminado: $ok enviados, $fallos con error.",'enviados'=>$ok,'errores'=>$fallos]);
}catch(Throwable $e){error_log('[comunicaciones] '.$e->getMessage());salida(['ok'=>false,'mensaje'=>'Error al procesar el comunicado. Revisa la instalación y registros.'],500);}
