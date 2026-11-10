(() => {
  let dialog=document.getElementById('ticket-gallery-modal');
  if(!dialog){
    dialog=document.createElement('dialog');
    dialog.id='ticket-gallery-modal';
    dialog.setAttribute('aria-label','Galería de archivos adjuntos');
    dialog.style.cssText='width:min(920px,calc(100vw - 24px));height:min(760px,calc(100vh - 24px));max-width:none;max-height:none;padding:0;border:1px solid #dce3eb;border-radius:14px;box-shadow:0 20px 70px rgba(15,35,60,.25);overflow:hidden;background:white';
    dialog.innerHTML='<div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-bottom:1px solid #e2e8f0;font:600 14px system-ui;color:#20334b"><span id="ticket-gallery-title">Archivos adjuntos</span><button type="button" id="ticket-gallery-close" aria-label="Cerrar galería" style="border:0;background:#f1f4f8;border-radius:7px;padding:7px 11px;cursor:pointer">✕</button></div><iframe id="ticket-gallery-frame" title="Carrusel de imágenes adjuntas" style="display:block;width:100%;height:calc(100% - 53px);border:0" loading="lazy"></iframe>';
    const style=document.createElement('style');
    style.textContent=`
      dialog#ticket-gallery-modal[open]{
        position:fixed!important;top:50%!important;left:50%!important;
        right:auto!important;bottom:auto!important;
        transform:translate(-50%,-50%)!important;
        margin:0!important;inset:auto!important;
        width:min(920px,calc(100vw - 32px))!important;
        height:min(760px,calc(100dvh - 32px))!important;
        max-width:none!important;max-height:none!important;
        z-index:2147483647!important;
      }
      dialog#ticket-gallery-modal::backdrop{background:rgba(10,24,45,.55)}
    `;
    document.head.appendChild(style);
    document.body.appendChild(dialog);
    dialog.querySelector('#ticket-gallery-close').addEventListener('click',()=>dialog.close());
    dialog.addEventListener('click',e=>{if(e.target===dialog)dialog.close()});
    dialog.addEventListener('close',()=>{dialog.querySelector('iframe').removeAttribute('src')});
  }
  document.addEventListener('click',e=>{
    const button=e.target.closest('.js-ticket-gallery');
    if(!button)return;
    e.preventDefault();
    const id=Number(button.dataset.ticketId);
    if(!Number.isSafeInteger(id)||id<1)return;
    dialog.querySelector('#ticket-gallery-title').textContent='Adjuntos · Ticket #'+id;
    dialog.querySelector('iframe').src='galeria_adjuntos.php?id='+encodeURIComponent(id);
    if(!dialog.open)dialog.showModal();
  });
})();