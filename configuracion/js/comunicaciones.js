(() => {
  'use strict';
  const $ = id => document.getElementById(id);
  const modal=$('modal-comunicaciones'), btn=$('btn-comunicaciones');
  if(!modal || !btn)return;
  let usuarios=[], colegios=[], areas=[], csrf='', prueba=false, ocupado=false;
  const modo=$('com-modo'), grupo=$('com-grupo'), manual=$('com-manual'), estado=$('com-estado');
  const asunto=$('com-asunto'), mensaje=$('com-mensaje'), enviar=$('com-enviar'), probar=$('com-prueba');
  const nombrePropio='Cristian Jorquera';
  const buscar=$('com-buscar'), todosManual=$('com-todos-manual'), manualWrap=$('com-manual-wrap');
  function filtrarManual(){
    const q=buscar.value.trim().toLocaleLowerCase('es');
    manual.querySelectorAll('label').forEach(label=>{label.hidden=!label.textContent.toLocaleLowerCase('es').includes(q);});
    actualizarSeleccionManual();
  }
  function actualizarSeleccionManual(){
    const visibles=[...manual.querySelectorAll('label:not([hidden]) input')];
    todosManual.checked=visibles.length>0 && visibles.every(c=>c.checked);
    todosManual.indeterminate=visibles.some(c=>c.checked) && !todosManual.checked;
  }
  buscar.addEventListener('input',filtrarManual);
  todosManual.addEventListener('change',()=>{manual.querySelectorAll('label:not([hidden]) input').forEach(c=>{c.checked=todosManual.checked;});cambiar();});
  function seleccionado(){
    if(modo.value==='todos')return usuarios;
    if(modo.value==='manual'){const ids=new Set([...manual.querySelectorAll('input:checked')].map(x=>Number(x.value)));return usuarios.filter(u=>ids.has(Number(u.id)));}
    if(!grupo.value)return [];
    return usuarios.filter(u=>String(u[modo.value==='colegio'?'id_colegio':'id_area_trabajo']??'0')===grupo.value);
  }
  function cambiar(){
    prueba=false;enviar.disabled=true;
    grupo.hidden=!['colegio','area'].includes(modo.value);
    manualWrap.hidden=modo.value!=='manual';
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
    estado.textContent='';
    actualizarSeleccionManual();
  }
  async function api(accion,body){
    const opt=body?{method:'POST',body:new URLSearchParams({accion,csrf,asunto:asunto.value,mensaje:mensaje.value,...body})}:{};
    const res=await fetch('comunicaciones_api.php'+(body?'':'?accion='+accion),{credentials:'same-origin',cache:'no-store',...opt});
    const data=await res.json();
    if(!res.ok||!data.ok)throw new Error(data.mensaje||'No fue posible realizar la operación.');
    return data;
  }
  btn.addEventListener('click',async()=>{
    if (typeof window.openModal === 'function') window.openModal('modal-comunicaciones'); else { modal.style.display='flex'; }
    try{
      const r=await api('opciones');
      csrf=r.csrf;
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
      if(!prueba)return;
      const ok=window.confirm('¿Confirmas el envío del comunicado a '+ids.length+' usuarios? Esta acción enviará correos reales.');
      if(!ok)return;
    }
    ocupado=true;probar.disabled=true;enviar.disabled=true;estado.textContent=accion==='prueba'?'Enviando prueba…':'Enviando comunicados…';
    try{
      const body={};
      ids.forEach((id,i)=>{body['ids['+i+']']=String(id);});
      const r=await api(accion,body);
      estado.textContent=r.mensaje;
      if(accion==='prueba'){prueba=true;enviar.disabled=false;}else{prueba=false;}
    }catch(e){estado.textContent=e.message;if(accion==='enviar')enviar.disabled=!prueba;}
    finally{ocupado=false;probar.disabled=false;}
  }
  probar.addEventListener('click',()=>ejecutar('prueba'));
  enviar.addEventListener('click',()=>ejecutar('enviar'));
})();
