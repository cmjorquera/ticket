      
      
      
      
    function actualizarChat(emisor, receptor) {
      Promise.all([
        obtenerNombre(emisor),
        obtenerNombre(receptor),
        fetch(`/api/mensajes-usuarios?emisor=${emisor}&receptor=${receptor}`).then(res => res.json())
      ])
      .then(([nombreEmisor, nombreReceptor, data]) => {
        if (data.success) {
          const chatLog = document.querySelector(`#acordeonChat${receptor} .chat-log`);
          chatLog.innerHTML = ''; // limpia
    
          data.mensajes.forEach(msg => {
            const esEmisor = parseInt(msg.de) === emisor;
            const alineacion = esEmisor ? 'text-end' : 'text-start';
            const estilo = esEmisor
              ? 'bg-success text-dark bg-opacity-25'
              : 'bg-white';
    
            const nombre = esEmisor
              ? `<strong style="font-size: 10px;">${nombreEmisor}:</strong>`
              : `<strong style="font-size: 10px;">${nombreReceptor}:</strong>`;
    
            const mensajeHTML = `
              <div class="mb-2 ${alineacion}">
                <div class="px-3 py-2 ${estilo} rounded shadow-sm d-inline-block">
                  ${nombre}<br>
                  ${msg.mensaje}<br>
                  <small class="text-muted">${formatearFecha(msg.fecha)}</small>
                </div>
              </div>
            `;
    
            chatLog.innerHTML += mensajeHTML;
          });
    
          chatLog.scrollTop = chatLog.scrollHeight;
        }
      })
      .catch(error => console.error("Error actualizando chat:", error));
    }


    function filtrarUsuarios(filtro) {
            const texto = filtro.toLowerCase();
            const tarjetas = document.querySelectorAll('.usuario-card');
        
            tarjetas.forEach(card => {
                const nombre = card.querySelector('span').textContent.toLowerCase();
                card.style.display = nombre.includes(texto) ? 'block' : 'none';
            });
        }
        
        
    function formatearFecha(fechaISO) {
          if (!fechaISO) return '';
        
          const fecha = new Date(fechaISO);
        
          const diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado','Domingo'];
          const nombreDia = diasSemana[fecha.getDay()];
        
          const dia = fecha.getDate().toString().padStart(2, '0');
          const mes = fecha.toLocaleString('es-CL', { month: 'long' });
          const año = fecha.getFullYear();
        
        return `<span style="color:red;">${nombreDia}</span> (<span>${dia}</span> ${mes}-${año})`;
        }
        
        
    let intervaloMensajes = null;
    function abrirConversacion(idUsuarioReceptor, idUsuarioEmisor) {
      const contenedor = document.getElementById('acordeonChat' + idUsuarioReceptor);
    
      if (contenedor.style.display === 'block') {
          
        contenedor.style.display = 'none';
        contenedor.innerHTML = '';
        return;
      }
    
      document.querySelectorAll('[id^="acordeonChat"]').forEach(div => {
        div.style.display = 'none';
        div.innerHTML = '';
      });
    
      const obtenerNombre = (id) => {
        return fetch('modelos/rescatar/usuario.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'id=' + encodeURIComponent(id)
        })
        .then(response => response.json())
        .then(data => data.nombre + ' ' + data.apellido_paterno);
      };
    
      Promise.all([
        obtenerNombre(idUsuarioReceptor),
        obtenerNombre(idUsuarioEmisor),
        fetch('modelos/rescatar/mensajes.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `usuario1=${idUsuarioReceptor}&usuario2=${idUsuarioEmisor}`
        }).then(res => res.json())
      ])
      .then(([nombreReceptor, nombreEmisor, mensajesData]) => {
        let mensajesHTML = '';
    
        if (mensajesData.success && Array.isArray(mensajesData.mensajes)) {
          mensajesData.mensajes.forEach(msg => {
            const esEmisor = parseInt(msg.de) === idUsuarioEmisor;
            const alineacion = esEmisor ? 'text-end' : 'text-start';
            const estilo = esEmisor
              ? 'bg-success text-dark bg-opacity-25'
              : 'bg-white';
    
            const nombre = esEmisor ? nombreEmisor : nombreReceptor;
    
            mensajesHTML += `
              <div class="mb-2 ${alineacion}">
                <div class="px-3 py-2 ${estilo} rounded shadow-sm d-inline-block">
                 <strong style="font-size: 10px;">${nombre}:</strong>
                    <br> ${msg.mensaje}<br>
                    <small class="text-muted">${formatearFecha(msg.fecha)}</small>
                </div>
              </div>
            `;
          });
        } else {
          mensajesHTML = '<div class="text-muted">No hay mensajes aún.</div>';
        }
    
        contenedor.innerHTML = `
          <div class="p-3 rounded" style="background-color: #f4f0ea; min-height: 500px;">
            <div class="chat-log mb-3" style="max-height: 650px; overflow-y: auto;">
              ${mensajesHTML}
            </div>
    
            <div class="input-group mt-2">
              <input type="text" class="form-control" id="mensaje_${idUsuarioReceptor}" placeholder="Escribe un mensaje..." 
                onkeypress="if(event.key === 'Enter') enviarMensaje(${idUsuarioReceptor}, ${idUsuarioEmisor})">
              <button class="btn btn-primary" onclick="enviarMensaje(${idUsuarioReceptor}, ${idUsuarioEmisor})">
                <i class="bi bi-send"></i>
              </button>
            </div>
          </div>
        `;
    
        contenedor.style.display = 'block';
        contenedor.querySelector('.chat-log').scrollTop = contenedor.querySelector('.chat-log').scrollHeight;
    
        // 🟢 Refresco automático de mensajes
        if (intervaloMensajes) clearInterval(intervaloMensajes);
        intervaloMensajes = setInterval(() => {
          actualizarChat(idUsuarioEmisor, idUsuarioReceptor);
        }, 3000);
      })
      .catch(error => {
        console.error("Error al cargar conversación:", error);
        contenedor.innerHTML = '<div class="alert alert-danger">Error al cargar el chat.</div>';
        contenedor.style.display = 'block';
      });
    }
        
        
    function obtenerNombre(id) {
      return fetch('modelos/rescatar/usuario.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id)
      })
      .then(response => response.json())
      .then(data => `${data.nombre} ${data.apellido_paterno}`);
    }
        
        
    function enviarMensaje(idReceptor, idEmisor) {
          const input = document.getElementById('mensaje_' + idReceptor);
          const mensaje = input.value.trim();
          if (mensaje === '') return;
        
          // Mostrar mensaje al instante (nombre dinámico)
          obtenerNombre(idEmisor).then(nombreEmisor => {
            const chatLog = document.querySelector(`#acordeonChat${idReceptor} .chat-log`);
            const hora = new Date().toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
        
            const nuevoMensaje = document.createElement('div');
            nuevoMensaje.className = "mb-2 text-end";
            nuevoMensaje.innerHTML = `
              <div class="px-3 py-2 bg-success text-dark bg-opacity-25 rounded shadow-sm d-inline-block">
                <strong>${nombreEmisor}:</strong> ${mensaje}<br>
                <small class="text-muted">${hora}</small>
              </div>
            `;
            chatLog.appendChild(nuevoMensaje);
            chatLog.scrollTop = chatLog.scrollHeight;
        
            input.value = '';
        
            // Enviar por AJAX al backend
            fetch("modelos/guardar/mensajes.php", {
              method: "POST",
              headers: { "Content-Type": "application/x-www-form-urlencoded" },
              body: `emisor=${idEmisor}&receptor=${idReceptor}&mensaje=${encodeURIComponent(mensaje)}`
            }).then(r => r.json()).then(d => {
              if (!d.success) {
                console.error("No se guardó el mensaje:", d.message);
              }
            }).catch(error => {
              console.error("Error AJAX al guardar mensaje:", error);
            });
          });
        }
        
    function detenerActualizacionMensajes() {
      if (intervaloMensajes) {
        clearInterval(intervaloMensajes);
        intervaloMensajes = null;
      }
    }
