(() => {
  'use strict';
  const $ = id => document.getElementById(id);
  const modal=$('modal-comunicaciones'), btn=$('btn-comunicaciones');
  if(!modal || !btn)return;
  let usuarios=[], colegios=[], areas=[], csrf='', prueba=false, ocupado=false;
  const modo=$('com-modo'), grupo=$('com-grupo'), manual=$('com-manual'), estado=$('com-estado');
  const asunto=$('com-asunto'), mensaje=$('com-mensaje'), enviar=$('com-enviar'), probar=$('com-prueba');
  let nombrePropio='', correoPropio='';
  const confirmacion=$('modal-confirmar-prueba');
  const aviso=$('com-aviso-exito');
  for(const el of [confirmacion,aviso])if(el.parentElement!==document.body)document.body.appendChild(el);
  const confirmarBtn=$('com-confirmar-prueba');
  function cerrarConfirmacion(){
    confirmacion.classList.remove('com-visible');
    confirmacion.setAttribute('aria-hidden','true');
  }
  function abrirConfirmacion(){
    confirmacion.classList.add('com-visible');
    confirmacion.setAttribute('aria-hidden','false');
    confirmarBtn.focus();
  }
  let avisoTimer;
  function mostrarExito(titulo,texto){
    clearTimeout(avisoTimer);
    $('com-aviso-titulo').textContent=titulo;
    $('com-aviso-texto').textContent=texto;
    aviso.classList.add('com-visible');
    aviso.setAttribute('aria-hidden','false');
    avisoTimer=setTimeout(()=>{
      aviso.classList.remove('com-visible');
      aviso.setAttribute('aria-hidden','true');
    },2500);
  }
  cerrarConfirmacion();
  const buscar=$('com-buscar'), todosManual=$('com-todos-manual'), manualWrap=$('com-manual-wrap');
  function filtrarManual(){
    const q=buscar.value.trim().toLocaleLowerCase('es');
    manual.querySelectorAll('label').forEach(label=>{label.style.display=label.textContent.toLocaleLowerCase('es').includes(q)?'block':'none';});
    actualizarSeleccionManual();
  }
  function actualizarSeleccionManual(){
    const visibles=[...manual.querySelectorAll('label')].filter(l=>l.style.display!=='none').map(l=>l.querySelector('input'));
    todosManual.checked=visibles.length>0 && visibles.every(c=>c.checked);
    todosManual.indeterminate=visibles.some(c=>c.checked) && !todosManual.checked;
  }
  buscar.addEventListener('input',filtrarManual);
  todosManual.addEventListener('change',()=>{manual.querySelectorAll('label').forEach(l=>{if(l.style.display!=='none')l.querySelector('input').checked=todosManual.checked;});cambiar();});
  function seleccionado(){
    if(modo.value==='todos')return usuarios;
    if(modo.value==='manual'){const ids=new Set([...manual.querySelectorAll('input:checked')].map(x=>Number(x.value)));return usuarios.filter(u=>ids.has(Number(u.id)));}
    if(!grupo.value)return [];
    return usuarios.filter(u=>String(u[modo.value==='colegio'?'id_colegio':'id_area_trabajo']??'0')===grupo.value);
  }
  function cambiar(){
    prueba=false;enviar.disabled=true;
    enviar.title='Primero debes enviar una prueba a tu correo con esta selección y contenido.';
    grupo.hidden=!['colegio','area'].includes(modo.value);
    grupo.style.display=grupo.hidden?'none':'block';
    manualWrap.hidden=modo.value!=='manual';
    manualWrap.style.display=manualWrap.hidden?'none':'block';
    if(!grupo.hidden){
      const campo=modo.value==='colegio'?'id_colegio':'id_area_trabajo';
      const catalogo=modo.value==='colegio'?colegios:areas;
      const actual=grupo.value;
      const inicial=document.createElement('option'); inicial.value=''; inicial.textContent=modo.value==='colegio'?'Selecciona un colegio':'Selecciona un área';
      grupo.replaceChildren(inicial,...catalogo.map(item=>{const op=document.createElement('option');op.value=String(item.id);op.textContent=item.nombre;return op;}));
      if(catalogo.some(item=>String(item.id)===actual))grupo.value=actual;
    }
    const elegidos=seleccionado();
    $('com-contador').textContent=elegidos.length+' destinatarios activos con correo válido';
    $('com-preview-mensaje').textContent=mensaje.value;
    $('com-preview-nombre').textContent=nombrePropio;
    estado.textContent=elegidos.length ? 'Antes de enviar el comunicado, realiza una prueba a tu correo. Si cambias destinatarios o contenido, debes repetirla.' : 'Selecciona al menos un destinatario para continuar.';
    actualizarSeleccionManual();
  }
  async function api(accion,body){
    const opt=body?{method:'POST',body:new URLSearchParams({accion,csrf,asunto:asunto.value,mensaje:mensaje.value,...body})}:{};
    const controller=new AbortController();
    const timeout=setTimeout(()=>controller.abort(),30000);
    let res,data;
    try{
      res=await fetch('comunicaciones_api.php'+(body?'':'?accion='+accion),{credentials:'same-origin',cache:'no-store',signal:controller.signal,...opt});
      data=await res.json();
    }catch(e){
      if(e.name==='AbortError')throw new Error('La solicitud tardó más de 30 segundos. Revisa si llegó el correo antes de reintentar: el servidor podría haber completado el envío.');
      throw e;
    }finally{clearTimeout(timeout);}
    if(!res.ok||!data.ok)throw new Error(data.mensaje||'No fue posible realizar la operación.');
    return data;
  }
  btn.addEventListener('click',async()=>{
    prueba=false;enviar.disabled=true;
    if (typeof window.openModal === 'function') window.openModal('modal-comunicaciones'); else { modal.style.display='flex'; }
    try{
      const r=await api('opciones');
      csrf=r.csrf;
      nombrePropio=r.propio?.nombre||'Usuario';correoPropio=r.propio?.email||'';
      colegios=r.colegios||[];areas=r.areas||[];
      usuarios=r.usuarios.filter(u=>/^\S+@\S+\.\S+$/.test(u.email||''));
      manual.replaceChildren(...usuarios.map(u=>{const l=document.createElement('label');l.style.display='block';l.style.padding='5px';const c=document.createElement('input');c.type='checkbox';c.value=u.id;c.addEventListener('change',cambiar);l.append(c,' '+u.nombre+' ('+u.email+')');return l;}));
      buscar.value='';filtrarManual();cambiar();
    }catch(e){estado.textContent=e.message;}
  });
  [modo,grupo,asunto,mensaje].forEach(el=>el.addEventListener(el===mensaje||el===asunto?'input':'change',cambiar));
  async function ejecutar(accion){
    if(ocupado)return;
    const ids=seleccionado().map(u=>Number(u.id));
    if(!ids.length){estado.textContent='Selecciona destinatarios.';return;}
    if(accion==='enviar'){
      if(!prueba){estado.textContent='Debes enviar primero una prueba a tu correo con los destinatarios y contenido actuales.';return;}
      const ok=window.confirm('¿Confirmas el envío del comunicado a '+ids.length+' usuarios? Esta acción enviará correos reales.');
      if(!ok)return;
    }
    ocupado=true;probar.disabled=true;enviar.disabled=true;estado.textContent=accion==='prueba'?'Enviando prueba…':'Enviando comunicados…';
    try{
      const body={};
      ids.forEach((id,i)=>{body['ids['+i+']']=String(id);});
      const r=await api(accion,body);
      estado.textContent=r.mensaje;
      if(accion==='prueba'){prueba=true;enviar.disabled=false;enviar.title='Confirmar envío a los destinatarios seleccionados.';estado.textContent=r.mensaje+' Ya puedes enviar el comunicado a '+ids.length+' destinatario(s).';mostrarExito('Prueba enviada',r.mensaje);}else{prueba=false;enviar.disabled=true;mostrarExito('Envío procesado',r.mensaje);}
    }catch(e){estado.textContent=e.message;if(accion==='enviar')enviar.disabled=!prueba;}
    finally{ocupado=false;probar.disabled=false;}
  }
  probar.addEventListener('click',()=>{
    if(ocupado)return;
    if(!seleccionado().length){estado.textContent='Selecciona al menos un destinatario antes de realizar la prueba.';return;}
    if(!correoPropio){estado.textContent='Tu cuenta no tiene un correo disponible.';return;}
    $('com-correo-propio').textContent=correoPropio;
    abrirConfirmacion();
  });
  $('com-cancelar-prueba').addEventListener('click',cerrarConfirmacion);
  confirmarBtn.addEventListener('click',()=>{
    if(ocupado)return;
    cerrarConfirmacion();
    ejecutar('prueba');
  });
  confirmacion.addEventListener('click',e=>{if(e.target===confirmacion)cerrarConfirmacion();});
  document.addEventListener('keydown',e=>{if(e.key==='Escape'&&confirmacion.classList.contains('com-visible'))cerrarConfirmacion();});
  enviar.addEventListener('click',()=>ejecutar('enviar'));
})();
