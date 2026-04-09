    async function crearTicketGuadalupe(idUsuarioSession) {
        const uid = Number(idUsuarioSession);
        const esAdmin = [7, 8, 42].includes(uid);

          let campoUsuarioHTML = "";
          let selectCategoriasHTML = "";
          let editorDescripcion = null;
        
          try {
            if (esAdmin) {
              const response = await fetch("modelos/rescatar/usuarios.php");
              const data = await response.json();
              if (data.success && data.usuarios.length > 0) {
                campoUsuarioHTML = `
                  <select id="usuarioTicket" class="form-select">
                    ${data.usuarios.map(u => `
                      <option value="${u.id}" ${u.id == idUsuarioSession ? 'selected' : ''}>
                        ${u.nombre} ${u.apellido_paterno}
                      </option>`).join("")}
                  </select>`;
              }
            } else {
              const response = await fetch("modelos/rescatar/usuario.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${idUsuarioSession}`
              });
              const user = await response.json();
              campoUsuarioHTML = `
                <input type="text" id="usuarioTicket" class="form-control" value="${user.nombre} ${user.apellido_paterno}" readonly>
              `;
            }
        
            const catResponse = await fetch("modelos/rescatar/categoria_de_ticket.php");
            const categorias = await catResponse.json();
            selectCategoriasHTML = `
              <select id="selectCategoria" class="form-select">
                <option value="">Selecciona</option>
                ${categorias.map(c => `<option value="${c.id}">${c.nombre_categoria}</option>`).join("")}
              </select>
            `;
        
            Swal.fire({
              title: '<div class="alert alert-dark mb-2 text-center">CREAR NUEVO TICKET</div>',
              html: `
                       <div class="contenedor-badges d-flex gap-2 justify-content-center mb-3 flex-wrap">
                    <span id="badgeCategoria" class="insignia_ticket bg-secondary text-white" 
                          data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="xdswww">
                      <i class="fas fa-layer-group"></i> Sin categoría
                    </span>
                   <!-- <span id="badgeFecha" class="insignia_ticket bg-primary text-white" 
                          data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="xdswww">
                      <i class="fas fa-calendar-alt"></i> ${new Date().toISOString().slice(0, 10)}
                    </span>-->
                   <!-- <span id="badgeHora" class="insignia_ticket bg-primary text-white" 
                          data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="xdswww">
                      <i class="fas fa-clock"></i> ${new Date().toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' })}
                    </span>-->
                    <span id="badgeArchivos" class="insignia_ticket bg-secondary text-white" 
                          data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="xdswww">
                      <i class="fas fa-paperclip"></i> Sin archivos
                    </span>
                  </div>
                  
         <form class="text-start" id="formCrearTicket">
          <!-- Fila de columnas -->
          <div class="row">
            <div class="col-md-6 pe-2">
              <div class="mb-3">
                <label><strong>Usuario:</strong></label>
                ${campoUsuarioHTML}
              </div>
              <div class="mb-3">
                <label><strong>Categoría:</strong></label>
                ${selectCategoriasHTML}
              </div>
             
              <div class="mb-6">
                <label><strong>Archivos Cargados:</strong></label>
                <div id="contenedorArchivos"></div>
              </div>
            </div>
        
            <div class="col-md-6 ps-2">
              <div class="mb-3">
                <label for="asuntoTicket"><strong>Asunto:</strong></label>
                <input type="text" class="form-control" id="asuntoTicket" placeholder="Ej: Problema con la impresora">
              </div>
               <div class="mb-3">
                <label for="archivoTicket"><strong>Adjuntar archivos:</strong></label>
                <input type="file" class="form-control" id="archivoTicket" multiple>
              </div>
            </div>
            
              <!-- Descripción en ancho completo -->
          <div class="mb-3">
            <label for="descripcionTicketEditor"><strong>Descripción:</strong></label>
            <div id="descripcionTicketEditor" style="height: 220px; background: #fff;"></div>
            <input type="hidden" id="descripcionTicket">
          </div>
          </div>
        </form>
        `,
              width: '800px',
              showDenyButton: true,
              showCloseButton: true,
              allowOutsideClick: false,
              confirmButtonText: 'Crear Ticket',
              denyButtonText: 'Borrador',
              customClass: {
                popup: 'cuerpo_modal_guardar',
                confirmButton: 'bt_crear',
                denyButton: 'bt_borrador'
              },
                didOpen: () => {
                  const campos = ['asuntoTicket', 'descripcionTicket', 'selectCategoria'];
                  campos.forEach(id => {
                    const el = document.getElementById(id);
                    if (!el) return;
                    el.addEventListener("input", () => el.classList.remove("input-error"));
                    el.addEventListener("change", () => el.classList.remove("input-error"));
                  });

                  if (window.Quill) {
                    editorDescripcion = new Quill('#descripcionTicketEditor', {
                      theme: 'snow',
                      placeholder: 'Describe el problema...',
                      modules: {
                        toolbar: [
                          [{ header: [1, 2, 3, false] }],
                          ['bold', 'italic', 'underline', 'strike'],
                          [{ color: [] }, { background: [] }],
                          [{ list: 'ordered' }, { list: 'bullet' }],
                          [{ align: [] }],
                          ['link', 'blockquote', 'code-block'],
                          ['clean']
                        ]
                      }
                    });

                    editorDescripcion.on('text-change', () => {
                      const descripcionInput = document.getElementById('descripcionTicket');
                      if (!descripcionInput) return;
                      descripcionInput.value = editorDescripcion.root.innerHTML;
                      descripcionInput.classList.remove('input-error');
                    });
                  }
                
                  const archivosInput   = document.getElementById("archivoTicket");
                  const contenedor      = document.getElementById("contenedorArchivos");
                  const badge           = document.getElementById("badgeArchivos") || { className: '', innerHTML: '' };
                  const categoria       = document.getElementById("selectCategoria");
                  const badgeCategoria  = document.getElementById("badgeCategoria");
                
                  // Actualizar lista al cargar
                  actualizarListaArchivos(archivosInput, contenedor, badge, new DataTransfer());
                
                  // Badge de archivos
                  archivosInput.addEventListener("change", () => {
                    validandoFormatoDeAdjunto("archivoTicket", "contenedorArchivos", "badgeArchivos");
                  });
                
                  // Badge de categoría
                  categoria.addEventListener("change", () => {
                    const val                   = categoria.options[categoria.selectedIndex].text || "Categoría";
                    badgeCategoria.className    = "insignia_ticket bg-primary text-white";
                    badgeCategoria.innerHTML    = `<i class="fas fa-layer-group"></i> ${val}`;
                  });
                },
        
              preConfirm: () => {
                const asunto = document.getElementById('asuntoTicket');
                const descripcion = document.getElementById('descripcionTicket');
                const categoria = document.getElementById('selectCategoria');
                const htmlDescripcion = editorDescripcion
                  ? editorDescripcion.root.innerHTML
                  : descripcion.value;
                const textoDescripcion = editorDescripcion
                  ? editorDescripcion.getText().trim()
                  : descripcion.value.trim();

                descripcion.value = htmlDescripcion;
        
                let valido = true;
                [asunto, categoria].forEach(field => {
                  if (!field.value.trim()) {
                    field.classList.add("input-error");
                    valido = false;
                  } else {
                    field.classList.remove("input-error");
                  }
                });

                if (!textoDescripcion) {
                  descripcion.classList.add("input-error");
                  const editorContenedor = document.querySelector('#descripcionTicketEditor .ql-container');
                  if (editorContenedor) {
                    editorContenedor.style.borderColor = '#e74a3b';
                  }
                  valido = false;
                } else {
                  descripcion.classList.remove("input-error");
                  const editorContenedor = document.querySelector('#descripcionTicketEditor .ql-container');
                  if (editorContenedor) {
                    editorContenedor.style.borderColor = '';
                  }
                }
        
                if (!valido) {
                  Swal.showValidationMessage('Debes completar todos los campos obligatorios');
                }
        
                return valido;
              }
            }).then(result => {
              if (result.isConfirmed || result.isDenied) {
                const asunto        = document.getElementById('asuntoTicket').value.trim();
                const descripcion   = editorDescripcion
                  ? editorDescripcion.root.innerHTML
                  : document.getElementById('descripcionTicket').value.trim();
                const categoria     = document.getElementById('selectCategoria')?.value || '';
                const data = {
                  usuarioId: idUsuarioSession,
                  asunto,
                  ticketTexarea: descripcion,
                  categoria
                };
        
                const estado = result.isConfirmed ? 1 : 4;
                const mensaje = result.isConfirmed ? "TICKET CREADO" : "BORRADOR CREADO";
        
                enviarDatosTicket(data, estado, mensaje, "ingresar_ticket");
              }
            });
          } catch (error) {
            console.error("Error al cargar usuarios o categorías:", error);
            Swal.fire("Error", "Ocurrió un problema al cargar los datos", "error");
          }
        }
        

    function enviarDatosTicket(data, estado, mensaje, accionType) {
    let formData = new FormData();

    // Leer la categoría una sola vez
    const categoria = (document.getElementById("selectCategoria")?.value || "").toString();

    // Campos obligatorios
    formData.append("usuarioId", data.usuarioId);
    formData.append("asunto", data.asunto);
    formData.append("descripcion_ticket", data.ticketTexarea);
    formData.append("categoria", categoria);
    formData.append("estado", estado);
    formData.append("accion", accionType);

    // Nuevos campos para prioridad, días y fecha (si los hubiera)
    formData.append("prioridad", data.prioridad || "");
    formData.append("dias_resolucion", data.dias || "");
    formData.append("fecha_resolucion", data.fecha || "");

    // Adjuntar archivos
    const archivosInput = document.getElementById("archivoTicket");
    if (archivosInput && archivosInput.files.length > 0) {
        for (let i = 0; i < archivosInput.files.length; i++) {
            formData.append("archivo[]", archivosInput.files[i]);
        }
    }

    // Elegir endpoint:
    // - cat = 10  -> guardar_ticket_sin_tecnico.php
    // - otro      -> guardar_ticket1.php (tu flujo actual)
    const urlDestino = (categoria === "10")
        ? "modelos/guardar/guardar_ticket_sin_tecnico.php"
        : "modelos/guardar/guardar_ticket1.php";

    // Enviar vía AJAX
    $.ajax({
        url: urlDestino,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            console.log(response);
            Swal.fire({
                title: `<div class="alert alert-d2ark" role="alert">${mensaje}</div>`,
                icon: "success",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                allowOutsideClick: false,
                customClass: {
                    popup: "cuerpo_modal_guardar",
                    confirmButton: "btn btn-primary"
                },
            }).then(() => {
                location.reload();
            });
        },
        error: function (xhr, status, error) {
            console.error(error);
            Swal.fire("Error", "Hubo un problema al registrar el ticket.", "error");
        },
    });
}

    function validandoFormatoDeAdjunto(inputArchivosId, contenedorId, badgeId) {
        const input          = document.getElementById(inputArchivosId);
        const contenedor    = document.getElementById(contenedorId);
        const badge         = document.getElementById(badgeId);
    
        const archivosPermitidos = [
            "application/pdf",
            "application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "application/vnd.ms-excel",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "image/png",
            "image/jpeg",
            "image/jpg",
            "image/gif",
            "image/webp"
        ];
    
        const extensionesPermitidas = [
            "pdf", "doc", "docx", "xls", "xlsx", "png", "jpg", "jpeg", "gif", "webp"
        ];
    
        // ✅ Recuperar archivos previamente cargados (si existen)
        const dataTransfer = new DataTransfer();
        const archivosPrevios = input.dataset.archivosPrevios ? JSON.parse(input.dataset.archivosPrevios) : [];
    
        archivosPrevios.forEach(fileData => {
            const blob = b64toBlob(fileData.base64, fileData.type);
            const file = new File([blob], fileData.name, { type: fileData.type });
            dataTransfer.items.add(file);
        });
    
        const nuevosArchivos = Array.from(input.files);
    
        for (const file of nuevosArchivos) {
            const extension = file.name.split('.').pop().toLowerCase();
            const tipoMimeValido = archivosPermitidos.includes(file.type);
            const extensionValida = extensionesPermitidas.includes(extension);
    
            if (!tipoMimeValido && !extensionValida) {
                const mensaje = document.createElement("div");
                mensaje.className = "alert alert-warning mt-2 small d-flex align-items-center gap-2";
                mensaje.innerHTML = `<i class="fas fa-exclamation-circle"></i> El archivo "${file.name}" tiene un formato no permitido.`;
    
                // Elimina cualquier mensaje anterior
                const form = document.getElementById("formCrearTicket");
                const anterior = document.getElementById("alertaArchivoInvalido");
                if (anterior) anterior.remove();
    
                mensaje.id = "alertaArchivoInvalido";
                form.prepend(mensaje);
                setTimeout(() => {
                    const alerta = document.getElementById("alertaArchivoInvalido");
                    if (alerta) alerta.remove();
                }, 4000);
    
                input.value = "";
                return;
            }
        }
    
        if ((dataTransfer.files.length + nuevosArchivos.length) > 5) {
            Swal.fire("Límite alcanzado", "Solo puedes adjuntar hasta 5 archivos en total.", "warning");
            input.value = "";
            return;
        }
    
        nuevosArchivos.forEach(file => dataTransfer.items.add(file));
    
        input.files = dataTransfer.files;
    
        // Guardar los archivos actuales como base64 para mantenerlos después del cambio
        const base64Archivos = [];
        const readerPromises = Array.from(input.files).map(file => {
            return new Promise(resolve => {
                const reader = new FileReader();
                reader.onload = () => {
                    base64Archivos.push({ name: file.name, type: file.type, base64: reader.result.split(",")[1] });
                    resolve();
                };
                reader.readAsDataURL(file);
            });
        });
    
        Promise.all(readerPromises).then(() => {
            input.dataset.archivosPrevios = JSON.stringify(base64Archivos);
            actualizarListaArchivos(input, contenedor, badge, dataTransfer);
        });
    }

    function actualizarListaArchivos(input, contenedor, badge, dataTransfer) {
      contenedor.innerHTML = "";
    
      const archivos = Array.from(input.files);
    
      if (archivos.length === 0) {
        contenedor.innerHTML = `<div class="text-muted fst-italic">Sin archivos adjuntos.</div>`;
        badge.className = "insignia_ticket bg-secondary text-white";
        badge.innerHTML = `<i class="fas fa-paperclip"></i> 0 archivos`;
        return;
      }
    
      archivos.forEach((file, index) => {
        const fila = document.createElement("div");
        fila.className = "d-flex justify-content-between align-items-center bg-white p-2 border mb-1 rounded";
    
        fila.innerHTML = `
          <span class="text-truncate" style="max-width: 60%">${file.name}</span>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-info verArchivo" data-index="${index}" title="Ver archivo">
              <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-sm btn-danger eliminarArchivo" data-index="${index}" title="Eliminar archivo">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        `;
        contenedor.appendChild(fila);
    
        // Botón VER
        fila.querySelector(".verArchivo").addEventListener("click", (event) => {
          event.preventDefault();
          event.stopPropagation();
          const file = input.files[index];
          const fileURL = URL.createObjectURL(file);
          window.open(fileURL, "_blank");
        });
    
        // Botón ELIMINAR
        fila.querySelector(".eliminarArchivo").addEventListener("click", (event) => {
          event.preventDefault();
          event.stopPropagation();
          dataTransfer.items.remove(index);
          input.files = dataTransfer.files;
    
          // Recalcular base64 para mantener consistencia
          const nuevosBase64 = Array.from(dataTransfer.files).map(file => {
            return new Promise(resolve => {
              const reader = new FileReader();
              reader.onload = () => {
                resolve({ name: file.name, type: file.type, base64: reader.result.split(",")[1] });
              };
              reader.readAsDataURL(file);
            });
          });
    
          Promise.all(nuevosBase64).then(resultado => {
            input.dataset.archivosPrevios = JSON.stringify(resultado);
            actualizarListaArchivos(input, contenedor, badge, dataTransfer);
          });
        });
      });
    
      badge.className = "insignia_ticket bg-primary text-white";
      badge.innerHTML = `<i class="fas fa-paperclip"></i> ${input.files.length} archivo${input.files.length !== 1 ? 's' : ''}`;
    }

    function b64toBlob(b64Data, contentType = '', sliceSize = 512) {
        const byteCharacters = atob(b64Data);
        const byteArrays = [];
      
        for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
          const slice = byteCharacters.slice(offset, offset + sliceSize);
      
          const byteNumbers = new Array(slice.length);
          for (let i = 0; i < slice.length; i++) {
            byteNumbers[i] = slice.charCodeAt(i);
          }
      
          const byteArray = new Uint8Array(byteNumbers);
          byteArrays.push(byteArray);
        }
      
        return new Blob(byteArrays, { type: contentType });
      }
    
    function archivosAdjuntos(id) {
      $.ajax({
        url: 'modelos/rescatar/archivos_adjuntos.php',
        type: 'POST',
        data: { id_ticket: id },
        dataType: 'json',
        success: function (archivos) {
          if (archivos.length === 0) {
            Swal.fire({
              title: '<div class="alert alert-dark" role="alert">VISUALIZACIÓN DE ADJUNTOS</div>',
              html: '<p>No existen archivos adjuntos para este ticket.</p>',
              width: "870px",
              showCloseButton: true,
              confirmButtonText: 'Cerrar',
              customClass: { popup: 'cuerpo_modal_guardar' }
            });
            return;
          }
    
          let tabs = '', tabContents = '';
          archivos.forEach((archivo, index) => {
            const active = index === 0 ? 'active' : '';
            const tabId = `content-${index}`;
            const ext = archivo.urlArchivo.split('.').pop().toLowerCase();
            let contentHtml = '';
    
            switch (ext) {
              case 'jpg': case 'jpeg': case 'png': case 'gif':
                contentHtml = `<img src="${archivo.urlArchivo}" class="img-fluid" alt="Imagen Adjunta">`;
                break;
              case 'pdf':
                contentHtml = `<embed src="${archivo.urlArchivo}" type="application/pdf" width="100%" height="500px"/>`;
                break;
              case 'mp4':
                contentHtml = `<video controls width="100%"><source src="${archivo.urlArchivo}" type="video/mp4"></video>`;
                break;
              case 'xls': case 'xlsx': case 'csv':
                contentHtml = `
                  <div class="text-center mb-3">
                    <button class="btn btn-success btn-sm" onclick="mostrarExcel('${archivo.urlArchivo}', '${tabId}-tabla')">
                      Ver como Tabla
                    </button>
                    <a class="btn btn-outline-primary btn-sm" href="${archivo.urlArchivo}" target="_blank">
                      Descargar
                    </a>
                  </div>
                  <div id="${tabId}-tabla" class="table-responsive"></div>`;
                break;
              case 'doc': case 'docx':
                contentHtml = `<div>Documento Word detectado. <a href="${archivo.urlArchivo}" target="_blank">Descargar</a></div>`;
                break;
              default:
                contentHtml = `<p>Archivo no soportado para vista previa. <a href="${archivo.urlArchivo}" target="_blank">Descargar</a></p>`;
                break;
            }
    
            tabs += `<li class="nav-item" role="presentation">
                       <button class="nav-link ${active}" id="tab-${index}" data-bs-toggle="tab" data-bs-target="#${tabId}" type="button" role="tab" aria-controls="${tabId}" aria-selected="${index === 0}">
                         Adjunto ${index + 1}
                       </button>
                     </li>`;
            tabContents += `<div class="tab-pane fade ${active} show" id="${tabId}" role="tabpanel" aria-labelledby="tab-${index}">
                              ${contentHtml}
                            </div>`;
          });
    
          const htmlContent = `
            <ul class="nav nav-tabs" id="myTab" role="tablist">${tabs}</ul>
            <div class="tab-content mt-3" id="myTabContent">${tabContents}</div>`;
    
          Swal.fire({
              title: '<div class="alert alert-dark" role="alert">VISUALIZACIÓN DE ADJUNTOS</div>',
              html: htmlContent,
              width: "900px",
              padding: "30px",
              showCloseButton: true,
              confirmButtonText: 'Cerrar',
              allowOutsideClick: false,   // No cerrar al hacer clic fuera
              allowEscapeKey: false,      // (opcional) No cerrar con Escape
                 customClass: {
                popup: 'cuerpo_modal_guardar',
                confirmButton: 'bt_crear'
              },
            });

        },
        error: function () {
          Swal.fire({
            title: 'Error',
            text: 'No se pudieron cargar los archivos adjuntos.',
            icon: 'error',
            confirmButtonText: 'Cerrar'
          });
        }
      });
    }

    // ✅ Función auxiliar para Excel
    function mostrarExcel(url, contenedorId) {
      fetch(url)
        .then(res => res.arrayBuffer())
        .then(data => {
          const workbook = XLSX.read(data, { type: 'array' });
          const sheet = workbook.Sheets[workbook.SheetNames[0]];
          const html = XLSX.utils.sheet_to_html(sheet);
          document.getElementById(contenedorId).innerHTML = html;
        })
        .catch(err => {
          document.getElementById(contenedorId).innerHTML = '<div class="text-danger">Error al cargar Excel</div>';
          console.error(err);
        });
    }

    function reactivarTicket(id_ticket, idUsuarioSession) {
        Swal.fire({
          title:
            '<div class="alert alert-dark" role="alert">¿FINALIZAR TICKET? ' +
            '<i class="bi bi-question-circle" id="infoIcon" style="font-size: 1.0em; cursor: pointer;" title="Click para más información" data-bs-toggle="popover" data-bs-content="En este modal tienes la posibilidad de Finalizar el ticket (cuando consideras que está bien resuelto) o reactivar el ticket, agregando un comentario que explique qué falta para que el ticket quede resuelto."></i>' +
            "</div>",

          text: "Deberías elegir una de las 2 opciones",
          html: `
                          <div id="textareaContainer" style="margin-top: 20px;">
                              <textarea id="comentarioReactivacion" class="form-control" placeholder="Agregue un comentario sobre la reactivación" style="height: 100px;"></textarea>
                          </div>
                      `,
          width: "870px",
          padding: "40px",
          showCancelButton: false, // Eliminar botón de cancelar
          showDenyButton: true,
          confirmButtonText: "Activar Ticket",
          denyButtonText: "Finalizar Ticket",
          confirmButtonColor: "#3085d6",

          denyButtonColor: "#d33",
          allowOutsideClick: false,
          allowEscapeKey: false,
          allowEnterKey: false,
          showCloseButton: true,

          customClass: {
            popup: "cuerpo_modal_guardar",
            confirmButton: "bt_activar_alumno",
            denyButton: "bt_finalizar_alumno",
          },
          didRender: () => {
            // Inicializar popover
            const popoverTrigger = new bootstrap.Popover(
              document.getElementById("infoIcon"),
              {
                trigger: "hover",
              }
            );
          },
          preConfirm: () => {
            const comentario = document
              .getElementById("comentarioReactivacion")
              .value.trim();
            if (comentario === "") {
              document
                .getElementById("comentarioReactivacion")
                .classList.add("is-invalid");
              Swal.showValidationMessage(
                "Debe agregar un comentario para reactivar el ticket."
              );
              return false;
            }
            return true;
          },
        }).then((result) => {
          if (result.isConfirmed) {
            // El usuario presionó "Activar Ticket"
            const comentario = document
              .getElementById("comentarioReactivacion")
              .value.trim();
            $.ajax({
              url: "modelos/guardar/reactivat_ticket.php",
              type: "POST",
              data: {
                id_ticket: id_ticket,
                comentario: comentario,
                validacion: "El ticket necesita más trabajo",
                usuario: idUsuarioSession,
              },
              success: function (response) {
                Swal.fire({
                  title:
                    '<div class="alert alert-dark" role="alert">Ticket reactivado</div>',
                  text: "En unos minutos el técnico revaluará la solución de su ticket.",
                  icon: "success",
                  timer: 3000,
                  timerProgressBar: true,
                  customClass: {
                    popup: "cuerpo_modal_guardar",
                  },
                  willClose: () => {
                    location.reload();
                  },
                });
              },
              error: function (error) {
                Swal.fire(
                  "Error",
                  "Hubo un problema al intentar procesar la solicitud.",
                  "error"
                );
              },
            });
          } else if (result.isDenied) {
            // El usuario presionó "Finalizar Ticket"
            $.ajax({
              url: "modelos/guardar/reactivat_ticket.php",
              type: "POST",
              data: {
                id_ticket: id_ticket,
                comentario: "", // No hay comentario al finalizar el ticket
                validacion: "El ticket está bien resuelto",
                usuario: idUsuarioSession,
              },
              success: function (response) {
                Swal.fire({
                  title:
                    '<div class="alert alert-dark" role="alert">Ticket finalizado</div>',
                  text: "Gracias por confiar en nosotros.",
                  icon: "success",
                  timer: 20000, // 5 minutos (300,000 ms)
                  timerProgressBar: true, // Muestra la barra de progreso
                  customClass: {
                    popup: "cuerpo_modal_guardar",
                  },
                  willClose: () => {
                    location.reload();
                  },
                });
              },
              error: function (error) {
                Swal.fire(
                  "Error",
                  "Hubo un problema al intentar procesar la solicitud.",
                  "error"
                );
              },
            });
          }
        });
      }

// ********************************************************************************
//* ********************************** TICKECK   USARIO
// *******************************************************************************
    function calcularBarraProgresoTicket(data) {
      let total = 4;
      let completos = 0;
      if (data.fecha_creacion_inicio) completos++;
      if (data.fecha_asignacion_tecnico) completos++;
      if (data.fecha_comienzo_ticket) completos++;
      if (data.fecha_termino_ticket) completos++;
      const porcentaje = (completos / total) * 100;
      let clase = "bg-danger";
      if (porcentaje === 100) clase = "bg-success";
      else if (porcentaje >= 50) clase = "bg-warning";
    
      return `
        <div class="progress" style="height:18px;">
          <div class="progress-bar ${clase}" style="width: ${porcentaje}%;">
            ${completos}/${total}
          </div>
        </div>
      `;
    }
    
    function actualizarBarraResumenEstados() {
      const pasos = document.querySelectorAll('.linea-tiempo-ticket .estado');
      const activos = document.querySelectorAll('.linea-tiempo-ticket .estado.activo');
      const barra = document.getElementById('barraProgresoTicket');
    
      if (!barra || pasos.length === 0) return;
    
      const total = pasos.length;
      const completados = activos.length;
      const porcentaje = (completados / total) * 100;
    
      barra.style.width = `${porcentaje}%`;
      barra.textContent = `${completados} de ${total} completados`;
    
      // Cambiar color según progreso
      if (porcentaje < 50) {
        barra.className = 'progress-bar bg-danger';
      } else if (porcentaje < 100) {
        barra.className = 'progress-bar bg-warning text-dark';
      } else {
        barra.className = 'progress-bar bg-success';
      }
    }
    
    function lineaDeTiempoTicket(id_ticket) {
      return $.ajax({
        type: "POST",
        url: "modelos/rescatar/linea_tiempo_ticket.php",
        data: { id_ticket },
        dataType: "json"
      }).then(data => {
        const f_creado    = data.fecha_creacion_inicio     || '--';
        const h_creado    = data.hora_creacion_inicio      || '--';
        const f_asignado  = data.fecha_asignacion_tecnico  || '--';
        const h_asignado  = data.hora_asignacion_tecnico   || '--';
        const f_proceso   = data.fecha_comienzo_ticket     || '--';
        const h_proceso   = data.hora_comienzo_ticket      || '--';
        const f_terminado = data.fecha_termino_ticket      || '--';
        const h_terminado = data.hora_termino_ticket       || '--';
    
        return `
          <div class="progress mb-2">
            <div id="barraProgresoTicket" class="progress-bar bg-primary" style="width: 0%;">
              0 de 4 completados
            </div>
          </div>
    
          <div class="linea-tiempo-ticket mt-1">
            <div class="estado activo">
              <div class="icono-linea-estado"><i class="fas fa-plus"></i></div>
              <div class="texto">Creado<br><small>${f_creado}<br>${h_creado}</small></div>
            </div>
            <div class="estado ${h_asignado !== '--' ? 'activo' : ''}">
              <div class="icono-linea-estado"><i class="fas fa-check"></i></div>
              <div class="texto">Asignado<br><small>${f_asignado}<br>${h_asignado}</small></div>
            </div>
            <div class="estado ${h_proceso !== '--' ? 'activo' : ''}">
              <div class="icono-linea-estado"><i class="fas fa-spinner"></i></div>
              <div class="texto">En proceso<br><small>${f_proceso}<br>${h_proceso}</small></div>
            </div>
            <div class="estado ${h_terminado !== '--' ? 'activo' : ''}">
              <div class="icono-linea-estado"><i class="fas fa-flag-checkered"></i></div>
              <div class="texto">Terminado<br><small>${f_terminado}<br>${h_terminado}</small></div>
            </div>
          </div>
        `;
      }).catch(error => {
        console.error("Error al obtener línea de tiempo:", error);
        return `<div class="alert alert-danger">Error al cargar línea de tiempo</div>`;
      });
    }
    
    
    // *******************************************************************************
    // ******************** MODAL USUARIO TICKET ******************************** 
    // *******************************************************************************

  
    function modalTicketUsuario(id_ticket) {
        // alert("************************");
      $.ajax({
        type: "POST",
        url: "modelos/rescatar/ticket_administracion.php",
        data: { id: id_ticket },
        dataType: "json",
        success: function (data) {
            const id_estado            = data.id_estado ?? "null";
            const nombrePrioridad      = data.nombrePrioridad       || "Sin prioridad";
            const nombreEstado         = data.nombreEstado          || "Sin estado";
            const nombre_categoria     = data.nombre_categoria      || "Sin categoría";
            const dias_estimada_admin  = data.dias_estimada_admin   || "";
            const nombreUsuario        = data.nombreUsuario         || "";
            const apePaternoUsuario    = data.apePaternoUsuario     || "";
            const asunto               = data.asunto                || "";
            const descripcion          = data.descripcion_ticket    || "";
            const descripcionLimpia    = limpiarTextoTicket(descripcion);
            
            const nombreTecnico        = data.nombreTecnico        || "";
            const apePaternoTecnico    = data.apePaternoTecnico   || "";
    

          const tecnico              = (data.nombreTecnico && data.apePaternoTecnico)
            ? `${data.nombreTecnico} ${data.apePaternoTecnico}`
            : "Sin técnico asignado";
    
          const esc = (s = "") =>
            String(s)
              .replaceAll("&", "&amp;")
              .replaceAll("<", "&lt;")
              .replaceAll(">", "&gt;")
              .replaceAll('"', "&quot;")
              .replaceAll("'", "&#39;");
    
          const esBorrador = (id_estado == 4);
    
          // IDs únicos por ticket
          const btnQrId    = `btnQrTicket_${id_ticket}`;
          const dropdownId = `dropdownDescargarTicket_${id_ticket}`;
    
          // ===== Badges (una sola constante) =====
          const badgesHTML = (() => {
            const prioridadHTML =
              (nombrePrioridad && nombrePrioridad.trim() && nombrePrioridad !== "Sin prioridad")
                ? `
                  <span class="insignia_ticket bg-primary text-white"
                        data-bs-toggle="popover" data-bs-trigger="hover"
                        data-bs-content="Nivel de prioridad del ticket">
                    <i class="fas fa-exclamation-circle"></i> ${esc(nombrePrioridad.trim())}
                  </span>`
                : "";
    
            const diasHTML =
              (dias_estimada_admin && Number(dias_estimada_admin) > 0)
                ? `
                  <span id="badgeDias" class="insignia_ticket bg-primary text-white"
                        data-bs-toggle="popover" data-bs-trigger="hover"
                        data-bs-content="Días estimados de resolución">
                    <i class="fas fa-clock"></i> ${esc(dias_estimada_admin)} días
                  </span>`
                : "";
    
            return `
              <div class="contenedor-badges d-flex gap-2 justify-content-center mb-0 flex-wrap">
                ${prioridadHTML}
            <span class="insignia_ticket bg-primary text-white"
                      data-bs-toggle="popover" data-bs-trigger="hover"
                      data-bs-content="Tecnico Asignado ">
                 <i class="fas fa-user"></i>${nombreTecnico} ${apePaternoTecnico}
                </span>
                <span class="insignia_ticket bg-primary text-white"
                      data-bs-toggle="popover" data-bs-trigger="hover"
                      data-bs-content="Estado del Ticket">
                  <i class="fas fa-spinner"></i> ${esc(nombreEstado || "Sin estado")}
                </span>
    
                <span class="insignia_ticket bg-primary text-white"
                      data-bs-toggle="popover" data-bs-trigger="hover"
                      data-bs-content="Categoría asignada">
                  <i class="fas fa-layer-group"></i> ${esc(nombre_categoria || "Sin categoría")}
                </span>
    
                <button type="button" id="${btnQrId}"
                        class="insignia_ticket bg-primary text-white"
                        data-bs-toggle="popover" data-bs-trigger="hover"
                        data-bs-content="Código QR del ticket">
                  <i class="fas fa-qrcode"></i> QR Code
                </button>
    
                ${diasHTML}
    
                <div class="dropdown">
                <!--  <button class="btn btn-danger dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-pill shadow-sm"
                          type="button" id="${dropdownId}"
                          data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-download"></i> -->
                  </button>
                  <ul class="dropdown-menu shadow dropdown-menu-end p-0 border-0 rounded-3"
                      aria-labelledby="${dropdownId}">
                    <li>
                      <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2"
                         href="#" onclick="enviarTicketPorCorreo(${id_ticket})">
                        <i class="fas fa-envelope text-primary"></i> <span>Enviar al correo</span>
                      </a>
                    </li>
                    <li><hr class="dropdown-divider m-0"></li>
                    <li>
                      <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2"
                         href="#" onclick="exportarTicketAPDF(${id_ticket})">
                        <i class="fas fa-file-pdf text-danger"></i> <span>Descargar PDF</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2"
                         href="#" onclick="descargarTicketExcel(${id_ticket})">
                        <i class="fas fa-file-excel text-success"></i> <span>Descargar Excel</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            `;
          })();
    
          const botonActivar = esBorrador
            ? `
              <div class="d-flex justify-content-center mt-4 mb-2">
                <button id="btnActivarTicket" class="bt_crear">
                  <i class="fas fa-play me-2"></i>Activar Ticket
                </button>
              </div>
            `
            : "";
    
          const showModal = (timelineHTML = "") => {
            Swal.fire({
              html: `
                <!-- Header -->
                <div class="modal-header bg-dark text-white d-flex justify-content-between align-items-center px-3 py-2">
                  <h5 class="modal-title mb-0 d-flex align-items-center gap-2 flex-wrap">
                    TICKET 00-${id_ticket}
                  </h5>
                  ${badgesHTML}
                </div>
    
                <div class="container-fluid py-3">
                  <form class="text-start">
                    <div class="row">
                      <div class="col-md-6 pe-2">
                        <!--
                        <div class="mb-3">
                          <label class="form-label"><strong>Técnico:</strong></label>
                          <input class="form-control" value="${esc(tecnico)}" readonly aria-readonly="true">
                        </div>
                        -->
                        <div class="mb-3">
                          <label class="form-label"><strong>Usuario:</strong></label>
                          <input class="form-control" value="${esc(nombreUsuario)} ${esc(apePaternoUsuario)}" readonly aria-readonly="true">
                        </div>
                      </div>
                      <div class="col-md-6 ps-2">
                        <div class="mb-3">
                          <label class="form-label"><strong>Asunto:</strong></label>
                          <input class="form-control" value="${esc(asunto)}" readonly aria-readonly="true">
                        </div>
                      </div>
                      <div class="mb-3">
                        <label class="form-label"><strong>Descripción:</strong></label>
                          <textarea id="descTicket" class="form-control" rows="4" readonly aria-readonly="true"></textarea>
                      </div>
                    </div>
                  </form>
    
                  <div class="mt-4">
                    ${timelineHTML}
                  </div>
    
                  ${botonActivar}
                </div>
              `,
              width: "50%",
              showCloseButton: true,
              allowEscapeKey: false,
              allowOutsideClick: false,
              showConfirmButton: !esBorrador,
              confirmButtonText: "Cerrar",
              customClass: {
                popup: "cuerpo_modal_guardar",
                confirmButton: "bt_crear",
                title: "titulo_swal_custom",
              },
              didOpen: () => {
                  
                  
                   const ta = document.getElementById('descTicket');
                  if (ta) ta.value = descripcionLimpia; // tildes/ñ OK
                  
                // Popovers
                [].slice.call(document.querySelectorAll("[data-bs-toggle='popover']"))
                  .forEach(el => new bootstrap.Popover(el));
    
                // QR: apunta a codigosQR/ticket/ticketQRinformacion.php?id=<id_ticket>
                const btnQr = document.getElementById(btnQrId);
                if (btnQr) {
                  const qrTargetURL = new URL("/codigosQR/ticket/ticketQRinformacion.php", window.location.origin);
                  qrTargetURL.searchParams.set("id", String(id_ticket));
                  const qrImgSrc =
                    `https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=1&data=${encodeURIComponent(qrTargetURL.href)}`;
    
                  const qrBox = document.createElement("div");
                  qrBox.id = `qrPopoverTicket_${id_ticket}`;
                  qrBox.style.cssText = `
                    position:absolute;padding:10px;background:#fff;border:1px solid #ccc;
                    box-shadow:0 4px 18px rgba(0,0,0,.15);border-radius:10px;z-index:9999;display:none;
                  `;
                  qrBox.innerHTML = `
                    <div style="display:flex;flex-direction:column;align-items:center;gap:8px;min-width:210px">
                      <a href="${qrTargetURL.href}" target="_blank" rel="noopener">
                        <img src="${qrImgSrc}" alt="QR Ticket ${id_ticket}" width="180" height="180" style="display:block"/>
                      </a>
                  
               
                    </div>
                  `;
                  document.body.appendChild(qrBox);
    
                  btnQr.addEventListener("click", (e) => {
                    e.stopPropagation();
                    const visible = qrBox.style.display === "block";
                    qrBox.style.display = visible ? "none" : "block";
                    if (!visible) {
                      const r = btnQr.getBoundingClientRect();
                      qrBox.style.top  = `${r.bottom + window.scrollY + 6}px`;
                      qrBox.style.left = `${r.left + window.scrollX - 60}px`;
                    }
                  });
    
                  const onDocClick = (ev) => {
                    if (!btnQr.contains(ev.target) && !qrBox.contains(ev.target)) {
                      qrBox.style.display = "none";
                      document.removeEventListener("click", onDocClick);
                    }
                  };
                  document.addEventListener("click", onDocClick);
    
                  qrBox.addEventListener("click", (ev) => {
                    const closeBtn = ev.target.closest(`#cerrarQr_${id_ticket}`);
                    if (closeBtn) qrBox.style.display = "none";
                  });
                }
    
                actualizarBarraResumenEstados();
    
                // Activar ticket (si es borrador)
                const btnActivar = document.getElementById("btnActivarTicket");
                if (btnActivar) {
                  btnActivar.addEventListener("click", () => {
                    Swal.fire({
                      title: "¿Estás seguro?",
                      text: "Este ticket se reactivará.",
                      icon: "warning",
                      showCloseButton: true,
                      allowEscapeKey: false,
                      allowOutsideClick: false,
                      showCancelButton: true,
                      confirmButtonText: "Sí, activar",
                      cancelButtonText: "Cancelar",
                      customClass: {
                        popup: "cuerpo_modal_guardar",
                        confirmButton: "bt_crear",
                        cancelButton: "bt_eliminar",
                      },
                    }).then((result) => {
                      if (result.isConfirmed) {
                        $.ajax({
                          type: "POST",
                          url: "modelos/guardar/activarTicket.php",
                          data: { id_ticket },
                          success: function () {
                            Swal.fire({
                              title: "Activado",
                              text: "El ticket ha sido reactivado.",
                              icon: "success",
                              timer: 2000,
                              showConfirmButton: false,
                              customClass: {
                                popup: "cuerpo_modal_guardar",
                                confirmButton: "bt_crear",
                              },
                            }).then(() => location.reload());
                          },
                          error: function (xhr, status, error) {
                            Swal.fire("Error", "No se pudo activar el ticket.", "error");
                            console.error(error);
                          },
                        });
                      }
                    });
                  });
                }
              }
            });
          };
    
          // Con o sin timeline
          if (id_estado != 4) {
            lineaDeTiempoTicket(id_ticket).then(showModal);
          } else {
            showModal("");
          }
        },
        error: function (xhr, status, error) {
          console.error("Error al obtener detalles del ticket:", error);
        }
      });
    }
    
    
    
    function ticketBorrador(id_ticket) {
      $.ajax({
        type: "POST",
        url: "modelos/rescatar/ticket_administracion.php",
        data: { id: id_ticket },
        dataType: "json",
        success: function (data) {
          const nombreUsuario = `${data.nombreUsuario || ''} ${data.apePaternoUsuario || ''}`;
          const asunto = data.asunto || '';
          const descripcion = data.descripcion_ticket || '';
          const id_categoria_ticket = data.id_categoria_ticket || '';
          const nombre_categoria = data.nombre_categoria || 'Sin categoría';
          const cantidad_archivos = data.cantidad_archivos || 0;
    
          // Cargar categorías
          $.ajax({
            type: "POST",
            url: "modelos/rescatar/categoria_de_ticket.php",
            dataType: "json",
            success: function (catData) {
              let opcionesCategoria = '<option value="">Selecciona</option>';
              if (Array.isArray(catData)) {
                opcionesCategoria += catData.map(cat => `
                  <option value="${cat.id}" ${cat.id == id_categoria_ticket ? 'selected' : ''}>
                    ${cat.nombre_categoria}
                  </option>
                `).join('');
              }
    
              Swal.fire({
                title: `<div class="alert alert-warning">Borrador</div>`,
                html: `
                  <div class="d-flex gap-2 justify-content-center flex-wrap my-3">
                    <span class="insignia_ticket bg-secondary text-white">
                      <i class="fas fa-pencil-alt"></i> Borrador
                    </span>
                    <span class="insignia_ticket bg-primary text-white" id="badgeCategoria">
                      <i class="fas fa-layer-group"></i> ${nombre_categoria}
                    </span>
                    <span class="insignia_ticket bg-primary text-white" id="badgeArchivos">
                      <i class="fas fa-paperclip"></i> ${cantidad_archivos} archivos
                    </span>
                  </div>
    
                  <form id="formEditarBorrador" enctype="multipart/form-data">
                    <input type="hidden" name="id_ticket" value="${id_ticket}">
    
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label><strong>Usuario:</strong></label>
                        <input type="text" class="form-control" value="${nombreUsuario}" disabled>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label><strong>Asunto:</strong></label>
                        <input type="text" class="form-control" name="asunto" id="asuntoBorrador" value="${asunto}" required>
                      </div>
                    </div>
    
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label><strong>Categoría:</strong></label>
                        <select class="form-select" name="categoria" id="categoriaBorrador" required>
                          ${opcionesCategoria}
                        </select>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label><strong>Adjuntar archivos:</strong></label>
                        <input type="file" class="form-control" name="archivos[]" id="archivoTicket" multiple>
                      </div>
                    </div>
    
                    <div class="mb-3">
                      <label><strong>Archivos Cargados:</strong></label>
                      <div id="contenedorArchivos">
                        <i>Sin archivos adjuntos.</i>
                      </div>
                    </div>
    
                    <div class="mb-3">
                      <label><strong>Descripción:</strong></label>
                      <textarea class="form-control" name="descripcion" id="descripcionBorrador" rows="4" required>${descripcion}</textarea>
                    </div>
                  </form>
                `,
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonText: 'Guardar Borrador',
                confirmButtonText: 'Activar Ticket',
                width: '800px',
                customClass: {
                  popup: 'cuerpo_modal_guardar',
                  confirmButton: 'bt_crear',
                  cancelButton: 'bt_guardar'
                },
                didOpen: () => {
                  const archivosInput = document.getElementById("archivoTicket");
    
                  archivosInput.addEventListener("change", () => {
                    validandoFormatoDeAdjunto("archivoTicket", "contenedorArchivos", "badgeArchivos");
                  });
    
                  const categoriaSelect = document.getElementById("categoriaBorrador");
                  categoriaSelect.addEventListener("change", function () {
                    const selectedText = this.options[this.selectedIndex].text;
                    document.getElementById("badgeCategoria").innerHTML = `
                      <i class="fas fa-layer-group"></i> ${selectedText}
                    `;
                  });
                },
                preConfirm: () => {
                  const form = $('#formEditarBorrador')[0];
                  const formData = new FormData(form);
    
                  return $.ajax({
                    type: 'POST',
                    url: 'modelos/guardar/activarTicket.php',
                    data: formData,
                    processData: false,
                    contentType: false
                  }).then(() => true).catch(() => {
                    Swal.showValidationMessage('Error al activar el ticket.');
                  });
                }
              }).then(result => {
                if (result.isConfirmed) {
                  Swal.fire({
                    icon: 'success',
                    title: 'Ticket activado',
                    timer: 2000,
                    showConfirmButton: false
                  }).then(() => location.reload());
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                  const form = $('#formEditarBorrador')[0];
                  const formData = new FormData(form);
    
                  $.ajax({
                    type: 'POST',
                    url: 'modelos/guardar/guardarBorrador.php',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: () => {
                      Swal.fire({
                        icon: 'success',
                        title: 'Borrador actualizado',
                        timer: 1500,
                        showConfirmButton: false
                      }).then(() => location.reload());
                    }
                  });
                }
              });
            }
          });
        },
        error: function () {
          Swal.fire('Error', 'No se pudo cargar el ticket.', 'error');
        }
      });
    }

    
    
    
    function descargarTicket(id_ticket) {
      Swal.fire({
        title: `<div class="alert alert-dark">¿Deseas descargar el ticket?</div>`,
        text: "Se generará un archivo PDF con todos los detalles.",
        icon: 'question',
        allowOutsideClick: false,
        showCloseButton: true,
        showCancelButton: true,
        confirmButtonText: 'Sí, descargar',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: () => {
          // Aquí puedes poner una validación si quieres antes de abrir el archivo
          window.open('modelos/descarga/ticket_pdf.php?id=' + id_ticket, '_blank');
        },
        customClass: {
          popup: 'cuerpo_modal_guardar',
          confirmButton: 'bt_crear',
          cancelButton: 'bt_eliminar'
        }
      });
    }
    
    



// ******************************************************************************************************************************************************
// ***************************************************************************************************************************TICKET-ASIGNADOS******
// ********************************************************************************************************TECNICO **********************************
// ******************************************************************************************************************************************************


   
   
      function rescatandoPrioridad() {
      $.ajax({
        url: "../modelos/rescatar/prioridaddesTicket.php", // ✅ tu ruta
        type: "GET",                                        // ✅ método GET
        dataType: "json",
        success: function (data) {
          const select = document.getElementById("prioridadAsignada");
          select.innerHTML = '<option value="">Seleccionar</option>';
    
          data.forEach(item => {
            // ✅ nombre visible, id como value
            select.innerHTML += `<option value="${item.id}">${item.nombre}</option>`;
          });
    
          activarCambioDePrioridad(); // ⏎ conectar el listener para mostrar badge dinámico
        }
      });
    }
    
    function activarCambioDePrioridad() {
      const select = document.getElementById("prioridadAsignada");
      const contenedorBadges = document.querySelector(".contenedor-badges");
    
      if (!select || !contenedorBadges) return;
    
      select.addEventListener("change", function () {
        const valorSeleccionado = this.value;
        const textoSeleccionado = this.options[this.selectedIndex].text;
    
        if (!valorSeleccionado) return; // Evita mostrar si no eligió nada
    
        let badge = document.getElementById("badgePrioridad");
    
        if (!badge) {
          badge = document.createElement("span");
          badge.id = "badgePrioridad";
          badge.className = "insignia_ticket bg-primary text-white";
          badge.setAttribute("data-bs-toggle", "popover");
          badge.setAttribute("data-bs-trigger", "hover");
          badge.setAttribute("data-bs-content", "Nivel de prioridad");
          contenedorBadges.insertBefore(badge, contenedorBadges.firstChild);
          new bootstrap.Popover(badge);
        }
    
        badge.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${textoSeleccionado}`;
        badge.style.display = "inline-block";
      });
    }

    
    
    // *************************************************************************
    // ******************** MODAL TECNICO TICKET ******************************** 
    // *************************************************************************
    
    function modalTicketTecnico(id_ticket) {
    //   alert("MODAL DEL TECNICO");  
      $.ajax({
        type: "POST",
        url: "modelos/rescatar/ticket_administracion.php",
        data: { id: id_ticket },
        dataType: "json",
        success: function (data) {
          const {
            id_ticket,id_estado,nombreUsuario,apePaternoUsuario,nombreTecnico,apePaternoTecnico,nombrePrioridad,dias_estimada_admin,nombre_categoria,nombreEstado,
            asunto,descripcion_ticket,fecha_creacion_inicio,hora_creacion_inicio,fecha_asignacion_tecnico,hora_asignacion_tecnico,fecha_comienzo_ticket,hora_comienzo_ticket,
            fecha_termino_ticket,hora_termino_ticket,hora_cierre_ticket,fecha_cierre_ticket,acciones,comentario_final
          } = data;
  
            const prioridadDisplay  = (nombrePrioridad && nombrePrioridad.trim() !== "") ? nombrePrioridad.trim() : null;
            const diasDisplay       = dias_estimada_admin ? `${dias_estimada_admin} días` : "";
            const categoriaDisplay  = nombre_categoria        || "Sin categoría";
            const estadoDisplay     = nombreEstado            || "Sin estado";
            const tecnicoDisplay    = (nombreTecnico && apePaternoTecnico)
            ? `${nombreTecnico} ${apePaternoTecnico}`
            : '<span class="text-danger fw-bold">Sin técnico asignado</span>';
            // ************************************************
            // contantes para el codigo QR
            const btnQrId    = `btnQrTicket_${id_ticket}`;
            const dropdownId = `dropdownDescargarTicket_${id_ticket}`;
            // ************************************************
            //condiciones
            // ************************************************
               let seleccionarPrioridad = "";
                if (id_estado == 2) {
                    seleccionarPrioridad = `
                      <div class="mb-3">
                        <label><strong>:</strong></label>
                        <label class="form-label"><strong>Prioridad:</strong></label>
                        <select class="form-select" id="prioridadAsignada">
                          <option value="">Cargando...</option>
                        </select>
                      </div>
                    `;
                  } else {
                          seleccionarPrioridad = ""; // si no hay prioridad
                }
    
        
               let seleccionarDiasEstimados = "";
                if (id_estado == 2) {
                    seleccionarDiasEstimados = `
                     <div class="mb-3">
                        <label class="form-label"><strong>Días Estimados:</strong></label>
                        <input type="date" class="form-control" id="fechaDiasEstimados">
                    </div>
                    `;
                  } else {
                          seleccionarDiasEstimados = ""; // si no hay prioridad
                }
                
            // ************************************************
            
            let badgeDiasHTML = dias_estimada_admin && parseInt(dias_estimada_admin) > 0 ? `
              <span id="badgeDias" class="insignia_ticket bg-primary text-white" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Días estimados de resolución">
                <i class="fas fa-clock"></i> ${diasDisplay}
              </span>
            ` : "";
    
            let badgePrioridadHTML = "";
                if (nombrePrioridad && nombrePrioridad.trim() !== "") {
                  const prioridadDisplay = nombrePrioridad.trim();
                  badgePrioridadHTML = `
                    <span id="badgePrioridad" class="insignia_ticket bg-primary text-white data-bs-toggle="popover" " 
                          data-bs-toggle="popover" data-bs-trigger="hover" 
                          data-bs-content="Nivel de prioridad">
                      <i class="fas fa-exclamation-circle"></i> ${prioridadDisplay}
                    </span>
                  `;
                }
      
          // Badge "Sin comentario final" (solo para tickets terminados sin comentario)
            let badgeComentarioFinalHTML = "";
                if (+id_estado === 5) {
                  const cf = (comentario_final ?? "").trim();
                  if (cf === "") {
                    badgeComentarioFinalHTML = `
                      <span class="insignia_ticket bg-primary text-white"
                            data-bs-toggle="popover" data-bs-trigger="hover"
                            data-bs-content="Mensaje final del técnico">
                        <i class="fas fa-comment-dots"></i> Sin comentario final
                      </span>
                    `;
                  }
                }

            // Bloque de badges (siempre se muestran) el que esta en la cabezera 
            const badgesHTML = `
                ${badgePrioridadHTML}
                ${badgeComentarioFinalHTML}
                    <span class="insignia_ticket bg-primary text-white" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Tecnico Asignado">
                    <i class="fas fa-user"></i> ${tecnicoDisplay}
                  </span>
                  <span class="insignia_ticket bg-primary text-white" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Estado actual del ticket">
                    <i class="fas fa-spinner"></i> ${estadoDisplay}
                  </span>
                  <span class="insignia_ticket bg-primary text-white" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Categoría asignada">
                    <i class="fas fa-layer-group"></i> ${categoriaDisplay}
                  </span>
                    <button type="button" id="${btnQrId}"class="insignia_ticket bg-primary text-white"data-bs-toggle="popover" data-bs-trigger="hover"data-bs-content="Código QR del ticket">
                      <i class="fas fa-qrcode"></i> QR
                    </button>
                  ${badgeDiasHTML}
    
                <!-- *************Descarga del ticket  / envio al correo  ***************************-->  
              <div class="dropdown">
                <button class="btn btn-danger dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-pill shadow-sm" type="button" id="${dropdownId}" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-download"></i>
                </button>
                <ul class="dropdown-menu shadow dropdown-menu-end p-0 border-0 rounded-3" aria-labelledby="${dropdownId}">
                  <li>
                    <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" href="#" onclick="enviarTicketPorCorreo(${id_ticket}); return false;">
                      <i class="fas fa-envelope text-primary"></i> <span>Enviar al correo</span>
                    </a>
                  </li>
                  <li><hr class="dropdown-divider m-0"></li>
                  <li>
                    <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" href="#" onclick="exportarTicketAPDF(${id_ticket}); return false;">
                      <i class="fas fa-file-pdf text-danger"></i> <span>Descargar PDF</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" href="#" onclick="descargarTicketExcel(${id_ticket}); return false;">
                      <i class="fas fa-file-excel text-success"></i> <span>Descargar Excel</span>
                    </a>
                  </li>
                </ul>
              </div>`;
 
              // Determinar el texto del botón y la acción según el estado:
              let confirmButtonText = "";
              let preConfirmAction = null;
                if (id_estado == 2) {
                  confirmButtonText = "COMENZAR TICKET";
                  preConfirmAction = () => {
                    let valido = true;
                    const prioridad = document.getElementById("prioridadAsignada");
                    const fecha = document.getElementById("fechaDiasEstimados");
                
                    if (!prioridad.value) {
                      prioridad.classList.add("border", "border-danger");
                      valido = false;
                    } else {
                      prioridad.classList.remove("border", "border-danger");
                    }
                
                    if (!fecha.value) {
                      fecha.classList.add("border", "border-danger");
                      valido = false;
                    } else {
                      fecha.classList.remove("border", "border-danger");
                    }
                
                    if (!valido) {
                      Swal.showValidationMessage("Debes seleccionar una prioridad y una fecha estimada.");
                      return false;
                    }
                
                    const prioridadValor = prioridad.value;
                    const fechaValor = fecha.value;
                
                    // Calcular días estimados
                    const fechaSeleccionada = new Date(fechaValor);
                    const hoy = new Date();
                    hoy.setHours(0,0,0,0);
                    const diferenciaEnMs = fechaSeleccionada - hoy;
                    const dias = Math.round(diferenciaEnMs / (1000 * 60 * 60 * 24));
                
                    return $.ajax({
                      url: "modelos/guardar/guardar_ticket.php",
                      type: "POST",
                      data: {
                        id: id_ticket,
                        accion: "comenzar_proceso",
                        prioridad: prioridadValor,
                        fecha_estimada: fechaValor,
                        dias_estimados: dias
                      }
                    });
                  };
                }
                else if (id_estado == 3) {
                  confirmButtonText = "TERMINAR TICKET";
                  preConfirmAction = () => {
                    // Lee el mensaje del textarea si existe
                    const campo = document.getElementById('mensajeUsuario');
                    const mensajeUsuario = campo ? (campo.value || '').trim() : '';
                
                    return Swal.fire({
                      title: `<div class="alert alert-dark">¿Estás seguro?</div>`,
                      text: "¿Deseas finalizar el ticket?",
                      icon: "warning",
                      allowOutsideClick: false,
                      showCancelButton: true,
                      confirmButtonText: "Sí, finalizar",
                      cancelButtonText: "No, cerrar",
                      reverseButtons: true,
                      customClass: {
                        popup: 'cuerpo_modal_guardar',
                        confirmButton: 'bt_crear',
                        cancelButton: 'bt_eliminar'
                      }
                    }).then(result => {
                      if (result.isConfirmed) {
                        // Confirma -> finaliza ticket y envía el mensaje al backend
                        return $.ajax({
                          url: "modelos/guardar/guardar_ticket.php",
                          type: "POST",
                          data: {
                            id: id_ticket,
                            accion: "terminar_proceso",
                            mensaje_usuario: mensajeUsuario
                          }
                        });
                      }
                
                      // Canceló -> cierra también el modal principal y corta el flujo
                      Swal.close();                               //  cierra el modal principal
                      return Promise.reject('cerrado_por_usuario'); // evita que se ejecute el .then exterior
                      // (alternativa: return false; pero ya cerramos manualmente)
                    });
                  };
                }
                else if (id_estado == 4 || id_estado == 5 || id_estado == 6) {
                  confirmButtonText = "CERRAR";
                  preConfirmAction = () => Promise.resolve(); // No hace nada
                }         
          
          // Se incorpora el timeline de acciones obtenido de construirAccionesHTML
          // (en este ejemplo se muestra debajo del timeline principal)
        const accionesHTML = construirAccionesHTML(acciones, id_ticket, id_estado);
        const escapeHTML = (str = "") =>
          String(str)
            .replaceAll("&","&amp;")
            .replaceAll("<","&lt;")
            .replaceAll(">","&gt;")
            .replaceAll('"',"&quot;")
            .replaceAll("'","&#39;");

        // UNICA constante para ambos casos (id_estado 3 y 5)
        const mensajeUsuarioHTML = (() => {
          // Caso 1: EN PROCESO (id_estado == 3) → textarea editable
          if (+id_estado === 3) {
            return `
              <div class="row mt-3">
                <div class="col-12">
                  <label class="form-label d-flex justify-content-center align-items-center gap-2">
                    <strong>Mensaje al Usuario:</strong>
                    <button type="button"
                      class="btn btn-sm btn-light rounded-circle d-inline-flex align-items-center justify-content-center"
                      style="width:22px;height:22px;"
                      aria-label="Ayuda: Mensaje al Usuario"
                      data-bs-toggle="popover" data-bs-trigger="focus" data-bs-placement="top"
                      data-bs-title="¿Para qué sirve?"
                      data-bs-content="Este es el mensaje que le llegará al usuario para explicarle lo que se hizo en su requerimiento.">
                      <i class="bi bi-question-lg"></i>
                    </button>
                  </label>
                  <textarea class="form-control form-control-sm w-100"
                    id="mensajeUsuario" rows="4"
                    placeholder="Escribe el mensaje para el usuario..."></textarea>
                </div>
              </div>
            `;
          }
        
          // Caso 2: TERMINADO (id_estado == 5) → mostrar comentario_final solo si trae contenido
          if (+id_estado === 5) {
            const comentarioLimpio = (comentario_final ?? "").trim();
            if (comentarioLimpio.length > 0) {
              return `
                <div class="row mt-3">
                  <div class="col-12">
                    <label class="form-label d-flex justify-content-center align-items-center gap-2">
                      <strong>Mensaje al Usuario:</strong>
                      <button type="button"
                        class="btn btn-sm btn-light rounded-circle d-inline-flex align-items-center justify-content-center"
                        style="width:22px;height:22px;"
                        aria-label="Ayuda: Mensaje al Usuario"
                        data-bs-toggle="popover" data-bs-trigger="focus" data-bs-placement="top"
                        data-bs-title="¿Para qué sirve?"
                        data-bs-content="Este es el mensaje que le llegó al usuario al finalizar su requerimiento.">
                        <i class="bi bi-question-lg"></i>
                      </button>
                    </label>
                    <textarea class="form-control form-control-sm w-100"id="comentarioFinal" rows="4" disabled>${escapeHTML(comentarioLimpio)}</textarea>
                  </div>
                </div>
              `;
            }
          }
         // Por defecto: no mostrar nada
          return "";
        })();


          Swal.fire({
              html: `
                <!-- Header oscuro con título + badges -->
                     <!-- Header oscuro con título + badges -->
                      <div class="modal-header bg-dark text-white d-flex justify-content-between align-items-center px-3 py-2">
                        <h5 class="modal-title mb-0 d-flex align-items-center gap-2 flex-wrap">
                          TICKET 00-${id_ticket}
                        </h5>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                          ${badgesHTML}
                        </div>
                      </div>

                  <!-- Cuerpo -->
                  <div class="container-fluid py-3 ticket-modal-layout">
                    <!-- FILA 1: Datos del ticket (izquierda) + Avances (derecha) -->
                    <div class="row g-3 ticket-modal-main">
                      <!-- Izquierda -->
                      <div class="col-lg-7">
                        <form class="text-start ticket-modal-form">
                          <div class="row g-2">
                            <div class="col-12 col-md-6">
                              <div class="mb-2">
                                <label class="form-label"><strong>Usuario:</strong></label>
                                <input type="text" class="form-control form-control-sm" value="${nombreUsuario} ${apePaternoUsuario}" disabled>
                              </div>
                              <!-******************************************** --->
                              <!-Solo si el ticket esta en estado Asignado (estado = 2) --->
                                 ${seleccionarPrioridad}
                                <!-******************************************** --->             
                                </div>
                
                            <div class="col-12 col-md-6">
                              <div class="mb-2">
                                <label class="form-label"><strong>Asunto:</strong></label>
                                <input type="text" class="form-control form-control-sm" value="${asunto}" disabled>
                              </div>
                              ${seleccionarDiasEstimados}
                            </div>
                
                            <div class="col-12">
                              <label class="form-label"><strong>Descripción:</strong></label>
                              <textarea class="form-control form-control-sm ticket-modal-description" rows="6" disabled>${limpiarTextoTicket(descripcion_ticket)}</textarea>
                            </div>
                          </div>
                        </form>
                      </div>
                        <!-- Derecha: Avances del Técnico -->
                     <div class="col-lg-5 d-flex flex-column ticket-modal-side" id="timelineContainer">
                          <input type="hidden" id="ticketId" value="${id_ticket}">
                          <input type="hidden" id="idEstadoTicket" value="${id_estado}">
                          ${accionesHTML}
                        </div>
                        ${mensajeUsuarioHTML}
                        <!-- FILA 3: Línea de tiempo dinámica (ancho completo) -->
                        <div class="row mt-3">
                          <div class="col-12">
                            <div id="lineaTiempoDinamica" class="mt-3"></div>
                          </div>
                        </div>
                      </div>
                  `,
            width: "88%",   
            showCloseButton: true,
            allowEscapeKey: false,
            confirmButtonText: confirmButtonText,
            allowOutsideClick: false,
            customClass: {popup: "cuerpo_modal_guardar modal-ticket-tecnico",confirmButton: "bt_crear",title: "titulo_swal_custom"},
            showCancelButton: false,
            preConfirm: preConfirmAction,
               didOpen: () => {
        // 1) Scroll al inicio del modal
        document.querySelector('.cuerpo_modal_guardar')?.scrollTo({ top: 0, behavior: 'auto' });

        // 2) Popovers (activar todos los que estén en el DOM actual)
        document.querySelectorAll("[data-bs-toggle='popover']")
          .forEach(el => new bootstrap.Popover(el));

        // 3) Timeline dinámico
        const lineaBox = document.getElementById("lineaTiempoDinamica");
        if (lineaBox) {
          lineaDeTiempoTicket(id_ticket).then(html => {
            lineaBox.innerHTML = html;
            if (typeof actualizarBarraResumenEstados === 'function') {
              actualizarBarraResumenEstados();
            }
          });
        }

  // 4) Fecha estimada (min = hoy) + badge de días
  const inputFecha = document.getElementById("fechaDiasEstimados");
  if (inputFecha) {
    // min hoy (en-CA = YYYY-MM-DD)
    inputFecha.min = new Date().toLocaleDateString('en-CA');
    // función tuya (si existe)
    if (typeof bloquearPasado === 'function') bloquearPasado(inputFecha);

    inputFecha.addEventListener("change", () => {
      const fechaSeleccionada = new Date(inputFecha.value);
      const hoy = new Date();
      hoy.setHours(0, 0, 0, 0);

      if (isNaN(fechaSeleccionada)) return;

      const dias = Math.round((fechaSeleccionada - hoy) / (1000 * 60 * 60 * 24));

      // eliminar badge previo si existe
      document.getElementById("badgeDias")?.remove();

      if (dias > 0) {
        const cont = document.querySelector(".contenedor-badges");
        if (cont) {
          const nuevoBadge = document.createElement("span");
          nuevoBadge.id = "badgeDias";
          nuevoBadge.className = "insignia_ticket bg-primary text-white";
          nuevoBadge.setAttribute("data-bs-toggle", "popover");
          nuevoBadge.setAttribute("data-bs-trigger", "hover");
          nuevoBadge.setAttribute("data-bs-content", "Días estimados de resolución");
          nuevoBadge.innerHTML = `<i class="fas fa-clock"></i> ${dias} días`;
          cont.appendChild(nuevoBadge);
          new bootstrap.Popover(nuevoBadge);
        }
      }
    });
  }

  // 5) Prioridad (solo en estado 2)
  if (id_estado == 2) {
    const selectPrioridad = document.getElementById("prioridadAsignada");
    if (selectPrioridad) {
      // Llenar opciones del select
      rescatandoPrioridad();

      // Mostrar el TEXTO de la opción en el badge (crear si no existe)
      selectPrioridad.addEventListener("change", function () {
        const tieneValor = !!this.value;
        const texto = this.options[this.selectedIndex]?.text || "";
        let badge = document.getElementById("badgePrioridad");

        if (!tieneValor) {
          if (badge) badge.style.display = "none";
          return;
        }

        if (!badge) {
          const contenedorBadges = document.querySelector(".contenedor-badges");
          if (contenedorBadges) {
            badge = document.createElement("span");
            badge.id = "badgePrioridad";
            badge.className = "insignia_ticket bg-primary text-white";
            badge.setAttribute("data-bs-toggle", "popover");
            badge.setAttribute("data-bs-trigger", "hover");
            badge.setAttribute("data-bs-content", "Nivel de prioridad");
            contenedorBadges.prepend(badge);
            new bootstrap.Popover(badge);
          }
        }

        if (badge) {
          badge.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${texto}`;
          badge.style.display = "inline-block";
        }
      }, { once: true }); // evita duplicar listeners al reabrir
    }
  }

  // 6) Lógica del QR
  const btnQr = document.getElementById(btnQrId);
  if (btnQr) {
    const qrTargetURL = new URL("/codigosQR/ticket/ticketQRinformacion.php", window.location.origin);
    qrTargetURL.searchParams.set("id", String(id_ticket));
    const qrImgSrc = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=1&data=${encodeURIComponent(qrTargetURL.href)}`;

    const qrBox = document.createElement("div");
    qrBox.id = `qrPopoverTicket_${id_ticket}`;
    qrBox.style.cssText = `
      position:absolute;padding:10px;background:#fff;border:1px solid #ccc;
      box-shadow:0 4px 18px rgba(0,0,0,.15);border-radius:10px;z-index:9999;display:none;
    `;
    qrBox.innerHTML = `
      <div style="display:flex;flex-direction:column;align-items:center;gap:8px;min-width:210px">
        <a href="${qrTargetURL.href}" target="_blank" rel="noopener">
          <img src="${qrImgSrc}" alt="QR Ticket ${id_ticket}" width="180" height="180" style="display:block"/>
        </a>
      </div>
    `;
    document.body.appendChild(qrBox);

    btnQr.addEventListener("click", (e) => {
      e.stopPropagation();
      const visible = qrBox.style.display === "block";
      qrBox.style.display = visible ? "none" : "block";
      if (!visible) {
        const r = btnQr.getBoundingClientRect();
        qrBox.style.top  = `${r.bottom + window.scrollY + 6}px`;
        qrBox.style.left = `${r.left + window.scrollX - 60}px`;
      }
    });

    const onDocClick = (ev) => {
      if (!btnQr.contains(ev.target) && !qrBox.contains(ev.target)) {
        qrBox.style.display = "none";
        document.removeEventListener("click", onDocClick);
      }
    };
    document.addEventListener("click", onDocClick);

    qrBox.addEventListener("click", (ev) => {
      const closeBtn = ev.target.closest(`#cerrarQr_${id_ticket}`);
      if (closeBtn) qrBox.style.display = "none";
    });
  }

  // 7) Toggle de columna derecha (si existe)
  const btnToggle = document.getElementById("toggleColumnaDerecha");
  const columna   = document.getElementById("columnaDerecha");
  if (btnToggle && columna) {
    btnToggle.addEventListener("click", () => {
      columna.classList.toggle("d-none");
      btnToggle.innerHTML = columna.classList.contains("d-none")
        ? '<i class="fas fa-eye"></i>'      // estaba oculto → mostrar
        : '<i class="fas fa-columns"></i>'; // visible → ocultar
    });
  }
}

            
          
              
              
              
              
          }).then((result) => {
            if (result.isConfirmed) {
              if (id_estado == 4 || id_estado == 5 || id_estado == 6) {
                return; // Solo cerrar modal
              }
          
              let mensaje = "";
              // let mensaje2 = "";

          
              if (id_estado == 2) {
                mensaje = "COMENZANDO TICKET";
                // mensaje2 ='<p>El ticket fue comenzado  exitosamente.</p>'
              } else if (id_estado == 3) {
                mensaje = "TICKET TERNMINADO CON ÉXITO";
                  // mensaje2 ='<p>El ticket fue cerrado exitosamente.</p>'
              }
          
              Swal.fire({
                title: `<div class="alert alert-dark">${mensaje}</div>`,
                  // html: `${mensaje2}`,
                icon: "success",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                allowOutsideClick: false,
                customClass: {
                  popup: "cuerpo_modal_guardar",
                  confirmButton: "btn btn-primary"
                }
              }).then(() => location.reload());
            }
          }).catch((error) => {
          // Si se canceló voluntariamente, no hacer nada
        // Usuario canceló la confirmación: no hacer nada
          if (error === 'cancelado' || error === 'cerrado_por_usuario') return;
          
          // Si fue otro tipo de error real, mostrar modal de error
          console.error("Error:", error);
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "Ocurrió un error al procesar la solicitud",
            confirmButtonColor: "#d33",
            confirmButtonText: "CANCELAR",
            customClass: {
              popup: "cuerpo_modal_guardar",
              confirmButton: "bt_eliminar"
            },
            timer: 5000,
            showConfirmButton: true
          });
        });
                },
        error: function (xhr, status, error) {
          console.error("Error al obtener detalles del ticket:", error);
        }
      });
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
   
  function obtenerPopupTicketActivo() {
        return Swal.getPopup ? Swal.getPopup() : document;
      }

  function escapeHTMLTicket(valor = "") {
        return String(valor)
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;")
          .replace(/'/g, "&#39;");
      }

  function sanitizeHtmlTicket(html = "") {
        const template = document.createElement("template");
        template.innerHTML = String(html || "");

        const allowedTags = new Set([
          "P", "BR", "STRONG", "B", "EM", "I", "U", "S",
          "OL", "UL", "LI", "A", "BLOCKQUOTE", "CODE", "PRE",
          "SPAN", "DIV", "H1", "H2", "H3"
        ]);

        const allowedAttrs = {
          A: new Set(["href", "target", "rel"])
        };

        const limpiarNodo = (node) => {
          if (node.nodeType === Node.TEXT_NODE) return;

          if (node.nodeType !== Node.ELEMENT_NODE) {
            node.remove();
            return;
          }

          if (!allowedTags.has(node.tagName)) {
            const parent = node.parentNode;
            while (node.firstChild) {
              parent.insertBefore(node.firstChild, node);
            }
            node.remove();
            return;
          }

          [...node.attributes].forEach((attr) => {
            const permitidos = allowedAttrs[node.tagName];
            const nombre = attr.name.toLowerCase();

            if (!permitidos || !permitidos.has(attr.name)) {
              node.removeAttribute(attr.name);
              return;
            }

            if (node.tagName === "A" && nombre === "href") {
              const href = (node.getAttribute("href") || "").trim();
              if (!/^(https?:|mailto:|tel:|#)/i.test(href)) {
                node.removeAttribute("href");
              }
            }
          });

          [...node.childNodes].forEach(limpiarNodo);
        };

        [...template.content.childNodes].forEach(limpiarNodo);
        return template.innerHTML;
      }

  function limpiarTextoTicket(html = "") {
        const template = document.createElement("template");
        template.innerHTML = String(html || "");

        template.content.querySelectorAll("br").forEach((node) => {
          node.replaceWith("\n");
        });

        template.content.querySelectorAll("p, div, li").forEach((node) => {
          if (!node.textContent) {
            return;
          }

          if (!node.textContent.endsWith("\n")) {
            node.appendChild(document.createTextNode("\n"));
          }
        });

        const texto = template.content.textContent || "";
        return texto.replace(/\n{3,}/g, "\n\n").trim();
      }

  function obtenerMetaAvanceTicket(html = "") {
        const template = document.createElement("template");
        template.innerHTML = String(html || "");

        const autorNode = template.content.querySelector("[data-avance-autor]");
        const autor = autorNode
          ? (autorNode.getAttribute("data-avance-autor") || "").trim()
          : "";
        const contenido = autorNode ? autorNode.innerHTML : String(html || "");

        return {
          autor: autor || "Registro del sistema",
          contenido: sanitizeHtmlTicket(contenido)
        };
      }

  function obtenerFechaHoraLocal() {
        const ahora = new Date();
        const pad = (numero) => String(numero).padStart(2, "0");

        return {
          fecha_avance: `${ahora.getFullYear()}-${pad(ahora.getMonth() + 1)}-${pad(ahora.getDate())}`,
          hora_avance: `${pad(ahora.getHours())}:${pad(ahora.getMinutes())}:${pad(ahora.getSeconds())}`
        };
      }

  function normalizarAccionAvance(avance = {}) {
        const fechaHora = obtenerFechaHoraLocal();

        return {
          accion: String(avance.accion || "").trim(),
          fecha_avance: avance.fecha_avance || fechaHora.fecha_avance,
          hora_avance: avance.hora_avance || fechaHora.hora_avance
        };
      }

  function obtenerAccionesDesdeTimelineDOM() {
        const popup = obtenerPopupTicketActivo();
        const lista = popup ? popup.querySelectorAll(".timeline-item[data-avance-item='1']") : [];

        return Array.from(lista).map((item) => ({
          accion: item.getAttribute("data-accion") || "",
          fecha_avance: item.getAttribute("data-fecha") || "",
          hora_avance: item.getAttribute("data-hora") || ""
        }));
      }

  function desplazarTimelineAlFinal() {
        const popup = obtenerPopupTicketActivo();
        const scrollAvances = popup ? popup.querySelector(".scroll-avances") : null;
        if (scrollAvances) {
          scrollAvances.scrollTop = scrollAvances.scrollHeight;
        }
      }

  function mostrarErrorAvance(mensaje) {
        const popup = obtenerPopupTicketActivo();
        const cajaError = popup ? $(popup.querySelector("#mensajeErrorAvance")) : $("#mensajeErrorAvance");
        if (!cajaError.length) {
          return;
        }

        cajaError.text(mensaje).removeClass("d-none").show();
      }

  function ocultarErrorAvance() {
        const popup = obtenerPopupTicketActivo();
        const cajaError = popup ? $(popup.querySelector("#mensajeErrorAvance")) : $("#mensajeErrorAvance");
        if (!cajaError.length) {
          return;
        }

        cajaError.addClass("d-none").hide().text("Este campo no puede estar vacio.");
      }

  function renderizarAvancesTecnicos(idTicket, idEstado, acciones = []) {
        const popup = obtenerPopupTicketActivo();
        const timelineContainer = popup ? popup.querySelector('#timelineContainer') : null;
        if (!timelineContainer) {
          return;
        }

        const accionesHTML = construirAccionesHTML(acciones, idTicket, idEstado);
        timelineContainer.innerHTML = `
          <input type="hidden" id="ticketId" value="${idTicket}">
          <input type="hidden" id="idEstadoTicket" value="${idEstado}">
          ${accionesHTML}
        `;

        desplazarTimelineAlFinal();
      }

  function actualizarAvanceTecnico(idTicket) {
        const popup = obtenerPopupTicketActivo();
        const contenedorAvances = popup ? popup.querySelector('#contenedorAvances') : null;
        const iconoToggle = popup ? popup.querySelector('#iconoToggleAvance') : null;
        const idEstadoInput = popup ? popup.querySelector('#idEstadoTicket') : null;
        const visible = contenedorAvances ? $(contenedorAvances).is(':visible') : false;
        const idEstado = idEstadoInput ? idEstadoInput.value : 0;
    
        $.ajax({
          url: "modelos/rescatar/avances_tecnicos.php",
          type: "POST",
          data: { id: idTicket },
          dataType: "json",
          success: function (data) {
            const acciones = Array.isArray(data?.acciones) ? data.acciones : [];
            renderizarAvancesTecnicos(idTicket, idEstado, acciones);
    
            if (visible && contenedorAvances) {
              $(contenedorAvances).show();
              if (iconoToggle) {
                $(iconoToggle).removeClass('fa-chevron-right').addClass('fa-chevron-down');
              }
            }

            desplazarTimelineAlFinal();
          }
        });
      }
      
      // Enviar nuevo avance técnico
    $(document).on("click", "#btnEnviarAvance", function () {
        const popup = obtenerPopupTicketActivo();
        const idTicketInput = popup ? popup.querySelector("#ticketId") : null;
        const avanceTextarea = popup ? $(popup.querySelector("#nuevoAvance")) : $("#nuevoAvance");
        const botonEnviar = $(this);
        const idEstadoInput = popup ? popup.querySelector("#idEstadoTicket") : null;
        const id_ticket = idTicketInput ? idTicketInput.value : "";
        const idEstado = idEstadoInput ? idEstadoInput.value : 0;
        const avance = avanceTextarea.val().trim();
    
        if (avance === "") {
          // Mostrar borde rojo y mensaje
          avanceTextarea.addClass("is-invalid");
          mostrarErrorAvance("Este campo no puede estar vacio.");
          return;
        }
    
        // Limpieza visual si todo está bien
        avanceTextarea.removeClass("is-invalid");
        ocultarErrorAvance();
    
        const accionesActuales = obtenerAccionesDesdeTimelineDOM();
        const nuevoAvanceTemporal = normalizarAccionAvance({ accion: avance });
        botonEnviar.prop("disabled", true);
        accionesActuales.push(nuevoAvanceTemporal);
        avanceTextarea.val("");
        renderizarAvancesTecnicos(id_ticket, idEstado, accionesActuales);

        $.ajax({
          url: "modelos/guardar/guardar_avance_tecnicos.php",
          type: "POST",
          dataType: "text",
          data: { id_ticket, avance },
          success: function (response) {
            let data = null;

            if (typeof response === "string") {
              const texto = response.trim();
              try {
                data = JSON.parse(texto);
              } catch (errorJson) {
                const inicio = texto.indexOf("{");
                const fin = texto.lastIndexOf("}");
                if (inicio !== -1 && fin !== -1 && fin >= inicio) {
                  try {
                    data = JSON.parse(texto.substring(inicio, fin + 1));
                  } catch (errorRecuperacion) {
                    data = null;
                  }
                }
              }
            } else {
              data = response;
            }

            if (data && data.success) {
              actualizarAvanceTecnico(id_ticket);
              return;
            }

            avanceTextarea.val(avance);
            actualizarAvanceTecnico(id_ticket);
            mostrarErrorAvance((data && data.message) ? data.message : "No se pudo guardar el avance.");
          },
          error: function () {
            avanceTextarea.val(avance);
            actualizarAvanceTecnico(id_ticket);
            mostrarErrorAvance("No se pudo guardar el avance en este momento.");
          },
          complete: function () {
            botonEnviar.prop("disabled", false);
          }
        });
      });

    function construirAccionesHTML(acciones = [], idTicket, idEstado) {
          const hayAvances = Array.isArray(acciones) && acciones.length > 0;
          const isTerminado = Number(idEstado) === 5; // estado 5 ⇒ no se puede escribir ni enviar avances
        
          // ---- Items del timeline
          let items = "";
          if (hayAvances) {
            acciones.forEach((a) => {
              const avance = normalizarAccionAvance(a);
              const texto = avance.accion;
              const metaAvance = obtenerMetaAvanceTicket(texto);
              const esCritico =
                texto.includes("Ticket Finalizado") ||
                texto.includes("Inicio Ticket");
        
              items += `
                <li class="timeline-item ${esCritico ? "timeline-item--critico" : ""}"
                    data-avance-item="1"
                    data-accion="${escapeHTMLTicket(texto)}"
                    data-fecha="${escapeHTMLTicket(avance.fecha_avance)}"
                    data-hora="${escapeHTMLTicket(avance.hora_avance)}">
                  <div class="timeline-dot"></div>
                  <div class="timeline-content">
                    <div class="timeline-author">${escapeHTMLTicket(metaAvance.autor)}</div>
                    <div class="timeline-time">
                      <strong>${escapeHTMLTicket(avance.fecha_avance)} (${escapeHTMLTicket(avance.hora_avance)})</strong>
                    </div>
                    <div class="timeline-text">${metaAvance.contenido}</div>
                  </div>
                </li>`;
            });
          }
        
          // ---- Contenido del timeline
          const contenidoTimeline = hayAvances
            ? `<ul class="timeline mb-0">${items}</ul>`
            : `
              <div class="empty-avances">
                <i class="bi bi-pin-angle-fill me-2"></i>
                No hay avances registrados.
              </div>`;
        
          // ---- Editor (textarea + botón) solo si NO está terminado
          const editorAvanceHTML = isTerminado
            ? "" // o muestra un aviso si prefieres: `<div class="alert alert-light border text-muted">Ticket terminado: no se pueden agregar avances.</div>`
            : `
              <textarea class="form-control mb-2 ticket-admin-avance-input" id="nuevoAvance" rows="3" placeholder="Escribe el nuevo avance..."></textarea>
              <div id="mensajeErrorAvance" class="invalid-feedback d-none">Este campo no puede estar vacio.</div>
              <button class="btn btn-primary w-100 mt-2" id="btnEnviarAvance" data-id="${idTicket}">
                <i class="fas fa-paper-plane"></i> Enviar Avance
              </button>`;
        
          // ---- Card final
          return `
            <div class="card-avances border rounded-3 p-3 mb-3">
              <div class="scroll-avances ${hayAvances ? "" : "is-empty"} w-100 mb-3" role="region" aria-live="polite">
                ${contenidoTimeline}
              </div>
              ${editorAvanceHTML}
            </div>`;
        }

             
             
             
               // Mostrar/ocultar los avances
        function acordenAvanceTecnico() {
            const contenedor  = document.getElementById('contenedorAvances');
            const icono       = document.getElementById('iconoToggleAvance');
            const visible     = window.getComputedStyle(contenedor).display !== 'none';
            contenedor.style.display = visible ? 'none' : 'block';
            icono.classList.toggle('fa-chevron-down', !visible);
            icono.classList.toggle('fa-chevron-right', visible);
          }
    







    function descargarTicketExcel(idTicket) {
      window.open(`modelos/descarga/descarga_ticket_tecnico.php?id=${idTicket}`, '_blank');
    }
    
    function exportarTicketAPDF(id_ticket) {
      window.open(`modelos/descarga/descarga_ticket_tecnico_pdf.php?id=${id_ticket}`, '_blank');
    }
    
        
    function enviarTicketPorCorreo(id_ticket) {
      Swal.fire({
        title: `<div class="alert alert-dark">¿Enviar ticket al correo?</div>`,
        text: "Se enviará el PDF al correo registrado del usuario.",
        icon: 'question',
        allowOutsideClick: false,
        showCloseButton: true,
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar',
        customClass: {
          popup: 'cuerpo_modal_guardar',
          confirmButton: 'bt_crear',
          cancelButton: 'bt_eliminar'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: 'Enviando ticket...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            customClass: {
              popup: "cuerpo_modal_guardar"
            },
            didOpen: () => {
              Swal.showLoading();
            }
          });
    
          $.post("../modelos/correos/ticket_correo.php", { id: id_ticket }, function (data) {
            Swal.close();
            if (data.success) {
             Swal.fire({
              title: "¡Enviado!",
              text: "El ticket fue enviado al correo correctamente.",
              icon: "success",
              timer: 3000,
              showConfirmButton: false,
              customClass: {
                popup: "cuerpo_modal_guardar"
              }
            });
    
            } else {
              Swal.fire({
                title: "Error",
                text: data.message || "No se pudo enviar el correo.",
                icon: "error",
                customClass: {
                  popup: "cuerpo_modal_guardar",
                  confirmButton: "btn btn-danger"
                }
              });
            }
          }, "json");
        }
      });
    }



// ***************************************************************************************************************************TICKET-ADMIN******
// ********************************************************************************************************ADMINISTRADOR **********************************

    function rescatandoTecnicos() {
      $.ajax({
        url: "../modelos/rescatar/tecnicos.php", // Este endpoint debe devolver solo usuarios con id_area_trabajo = 8
        type: "GET",
        dataType: "json",
        success: function(response) {
          let options = '<option value="">Seleccionar</option>';
          
          // Si la respuesta es un arreglo directo
          if (Array.isArray(response)) {
            response.forEach(t => {
              options += `<option value="${t.id}">${t.nombre} ${t.apellido_paterno || t.apellido}</option>`;
            });
          }
          // O si viene en un objeto con response.success y response.usuarios
          else if (response.success && Array.isArray(response.usuarios)) {
            response.usuarios.forEach(t => {
              options += `<option value="${t.id}">${t.nombre} ${t.apellido_paterno || t.apellido}</option>`;
            });
          }
          else {
            console.warn("No se encontraron usuarios con id_area_trabajo=8.");
            options = '<option value="">Sin técnicos disponibles</option>';
          }
          $("#tecnicoAsignado").html(options);
        },
        error: function(xhr, status, error) {
          console.error("Error al cargar técnicos:", error);
          $("#tecnicoAsignado").html('<option value="">Error al cargar</option>');
        }
      });
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
      
    function modalTicketAdministrativo(id_ticket) {
        //   alert("MODAL PARA ADMINISTRADOR");  
          $.ajax({
            type: "POST",
            url: "modelos/rescatar/ticket_administracion.php",
            data: { id: id_ticket },
            dataType: "json",
            success: function (data) {
              const {
                id_ticket,id_estado,nombreUsuario,apePaternoUsuario,nombreTecnico,apePaternoTecnico,nombrePrioridad,dias_estimada_admin,nombre_categoria,nombreEstado,
                asunto,descripcion_ticket,fecha_creacion_inicio,hora_creacion_inicio,fecha_asignacion_tecnico,hora_asignacion_tecnico,fecha_comienzo_ticket,hora_comienzo_ticket,
                fecha_termino_ticket,hora_termino_ticket,fecha_cierre_ticket,hora_cierre_ticket,acciones,comentario_final
              } = data;
              const avanceTecnicoBadgeHTML = data.avance_count > 0 
              ? `<button type="button" class="insignia_ticket bg-primary position-relative d-flex flex-column align-items-center" onclick="acordenAvanceTecnico()">
                   <span class="badge_ticket rounded-pill bg-danger">
                     ${data.avance_count}
                   </span>
                   <span>Avances Técnico</span>
                 </button>`
              : "";
              const prioridadDisplay  = nombrePrioridad || "Sin prioridad";
              const diasDisplay       = dias_estimada_admin ? `${dias_estimada_admin} días` : "";
              const categoriaDisplay  = nombre_categoria || "Sin categoría";
              const estadoDisplay     = nombreEstado || "Sin estado";
          // ************************************************
            // contantes para el codigo QR
            const btnQrId    = `btnQrTicket_${id_ticket}`;
            const dropdownId = `dropdownDescargarTicket_${id_ticket}`;
            // ************************************************
              const tecnicoAsignado = Boolean(nombreTecnico && apePaternoTecnico);
              const tecnicoDisplay = tecnicoAsignado
                ? `${nombreTecnico} ${apePaternoTecnico}`
                : "Sin tecnico asignado";
              const tecnicoInputClass = tecnicoAsignado ? "" : " text-danger fw-bold";
          
             
               let badgeDiasHTML = dias_estimada_admin && parseInt(dias_estimada_admin) > 0 ? `
            
              <span id="badgeDias" class="insignia_ticket bg-primary text-white" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Días estimados de resolución">
                <i class="fas fa-clock"></i> ${diasDisplay}
              </span>
            ` : "";
    
              let badgePrioridadHTML = "";
                if (nombrePrioridad && nombrePrioridad.trim() !== "") {
                  const prioridadDisplay = nombrePrioridad.trim();
                  badgePrioridadHTML = `
                  
        
                    <span id="badgePrioridad" class="insignia_ticket bg-primary text-white data-bs-toggle="popover" " 
                          data-bs-toggle="popover" data-bs-trigger="hover" 
                          data-bs-content="Nivel de prioridad">
                      <i class="fas fa-exclamation-circle"></i> ${prioridadDisplay}
                    </span>
                  `;
                }

              
                // Badge "Sin comentario final" (solo para tickets terminados sin comentario)
            let badgeComentarioFinalHTML = "";
                if (+id_estado === 5) {
                  const cf = (comentario_final ?? "").trim();
                  if (cf === "") {
                    badgeComentarioFinalHTML = `
                      <span class="insignia_ticket bg-primary text-white"
                            data-bs-toggle="popover" data-bs-trigger="hover"
                            data-bs-content="Mensaje final del técnico">
                        <i class="fas fa-comment-dots"></i> Sin comentario final
                      </span>
                    `;
                  }
                }

          
                // Bloque de badges (siempre se muestran)
              const badgesHTML = `
                ${badgePrioridadHTML}
                ${badgeComentarioFinalHTML}
                     <span class="insignia_ticket bg-primary text-white" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Tecnico Asigando">
                    <i class="fas fa-spinner"></i> ${tecnicoDisplay}
                  </span>
                  <span class="insignia_ticket bg-primary text-white" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Estado actual del ticket">
                    <i class="fas fa-spinner"></i> ${estadoDisplay}
                  </span>
                  <span class="insignia_ticket bg-primary text-white" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Categoría asignada">
                    <i class="fas fa-layer-group"></i> ${categoriaDisplay}
                  </span>
                <button type="button" id="${btnQrId}"class="insignia_ticket bg-primary text-white"data-bs-toggle="popover" data-bs-trigger="hover"data-bs-content="Código QR del ticket">
                      <i class="fas fa-qrcode"></i> QR
                    </button>
                  ${badgeDiasHTML}
    
                <!-- *************Descarga del ticket  / envio al correo  ***************************-->  
              <div class="dropdown">
                <button class="btn btn-danger dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-pill shadow-sm" type="button" id="${dropdownId}" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-download"></i>
                </button>
                <ul class="dropdown-menu shadow dropdown-menu-end p-0 border-0 rounded-3" aria-labelledby="${dropdownId}">
                  <li>
                    <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" href="#" onclick="enviarTicketPorCorreo(${id_ticket}); return false;">
                      <i class="fas fa-envelope text-primary"></i> <span>Enviar al correo</span>
                    </a>
                  </li>
                  <li><hr class="dropdown-divider m-0"></li>
                  <li>
                    <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" href="#" onclick="exportarTicketAPDF(${id_ticket}); return false;">
                      <i class="fas fa-file-pdf text-danger"></i> <span>Descargar PDF</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" href="#" onclick="descargarTicketExcel(${id_ticket}); return false;">
                      <i class="fas fa-file-excel text-success"></i> <span>Descargar Excel</span>
                    </a>
                  </li>
                </ul>
              </div>
            `;

              // Determinar el texto del botón y la acción según el estado:
              // id_estado == 2
              // id_estado == 3 
              // id_estado == 5 
              let confirmButtonText = "";
              let preConfirmAction = null;
              
              if (id_estado == 2) {
                confirmButtonText = "COMENZAR TICKET";
                preConfirmAction = () =>
                  $.ajax({
                    url: "modelos/guardar/guardar_ticket.php",
                    type: "POST",
                    data: { id: id_ticket, accion: "comenzar_proceso" }
                  });
              } else if (id_estado == 3) {
                confirmButtonText = "FINALIZAR";
                preConfirmAction = () =>
                  $.ajax({
                    url: "modelos/guardar/guardar_ticket.php",
                    type: "POST",
                    data: { id: id_ticket, accion: "terminar_proceso" }
                  });
                } else if (id_estado == 4 || id_estado == 5 || id_estado == 6) {
                  confirmButtonText = "CERRAR";
                  preConfirmAction = () => Promise.resolve(); // No hace nada
                }         
          
              // Se incorpora el timeline de acciones obtenido de construirAccionesHTML
              // (en este ejemplo se muestra debajo del timeline principal)
        const accionesHTML = construirAccionesHTML(acciones, id_ticket, id_estado);
           //MENSAJE DEL TECNICO AL USUARIO PARA FINALIZAR EL TICKET

        const escapeHTML = (str = "") =>
          String(str)
            .replaceAll("&","&amp;")
            .replaceAll("<","&lt;")
            .replaceAll(">","&gt;")
            .replaceAll('"',"&quot;")
            .replaceAll("'","&#39;");

        // UNICA constante para ambos casos (id_estado 3 y 5)
        const mensajeUsuarioHTML = (() => {
          // Caso 1: EN PROCESO (id_estado == 3) → textarea editable
          if (+id_estado === 3) {
            return `
              <div class="row mt-3">
                <div class="col-12">
                  <label class="form-label d-flex justify-content-center align-items-center gap-2">
                    <strong>Mensaje al Usuario:</strong>
                    <button type="button"
                      class="btn btn-sm btn-light rounded-circle d-inline-flex align-items-center justify-content-center"
                      style="width:22px;height:22px;"
                      aria-label="Ayuda: Mensaje al Usuario"
                      data-bs-toggle="popover" data-bs-trigger="focus" data-bs-placement="top"
                      data-bs-title="¿Para qué sirve?"
                      data-bs-content="Este es el mensaje que le llegará al usuario para explicarle lo que se hizo en su requerimiento.">
                      <i class="bi bi-question-lg"></i>
                    </button>
                  </label>
                  <textarea class="form-control form-control-sm w-100"
                    id="mensajeUsuario" rows="4"
                    placeholder="Escribe el mensaje para el usuario..."></textarea>
                </div>
              </div>
            `;
          }
        
          // Caso 2: TERMINADO (id_estado == 5) → mostrar comentario_final solo si trae contenido
          if (+id_estado === 5) {
            const comentarioLimpio = (comentario_final ?? "").trim();
            if (comentarioLimpio.length > 0) {
              return `
                <div class="row mt-3">
                  <div class="col-12">
                    <label class="form-label d-flex justify-content-center align-items-center gap-2">
                      <strong>Mensaje al Usuario:</strong>
                      <button type="button"
                        class="btn btn-sm btn-light rounded-circle d-inline-flex align-items-center justify-content-center"
                        style="width:22px;height:22px;"
                        aria-label="Ayuda: Mensaje al Usuario"
                        data-bs-toggle="popover" data-bs-trigger="focus" data-bs-placement="top"
                        data-bs-title="¿Para qué sirve?"
                        data-bs-content="Este es el mensaje que le llegó al usuario al finalizar su requerimiento.">
                        <i class="bi bi-question-lg"></i>
                      </button>
                    </label>
                    <textarea class="form-control form-control-sm w-100"
                      id="comentarioFinal" rows="4" readonly>${escapeHTML(comentarioLimpio)}</textarea>
                  </div>
                </div>
              `;
            }
          }
        
          // Por defecto: no mostrar nada
          return "";
        })();


              Swal.fire({
      

    html: `
                  <!-- Header oscuro con título + badges -->
                      <div class="modal-header bg-dark text-white d-flex justify-content-between align-items-center px-3 py-2">
                        <h5 class="modal-title mb-0 d-flex align-items-center gap-2 flex-wrap">
                          TICKET 00-${id_ticket}
                        </h5>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                          ${badgesHTML}
                        </div>
                      </div>
                    <div class="container-fluid py-3 ticket-modal-layout">
                    <!-- FILA 1: Datos del ticket (izquierda) + Avances (derecha) -->
                    <div class="row g-3 ticket-modal-main">
                      <!-- Izquierda -->
                      <div class="col-lg-7">
                        <form class="text-start ticket-modal-form">
                          <div class="row g-2">
                            <div class="col-12 col-md-6">
                              <div class="mb-2">
                                <label class="form-label"><strong>Usuario:</strong></label>
                                <input type="text" class="form-control form-control-sm"
                                       value="${nombreUsuario} ${apePaternoUsuario}" readonly>
                              </div>
                            </div>
                            <div class="col-12 col-md-6">
				             <div class="mb-2">
                                <label class="form-label"><strong>Tecnico:</strong></label>
                                <input type="text" class="form-control form-control-sm${tecnicoInputClass}" value="${tecnicoDisplay}" readonly>
                              </div>
                            </div>
                
                    
                
                            <div class="col-12">
                              <label class="form-label"><strong>Descripción:</strong></label>
                              <div class="form-control form-control-sm ticket-admin-descripcion ticket-modal-description ticket-modal-description-html" style="height:auto;">${sanitizeHtmlTicket(descripcion_ticket)}</div>
                            </div>
                          </div>
                        </form>
                      </div>
            
                        <!-- Derecha: Avances del Técnico -->
                        <div class="col-lg-5 d-flex flex-column ticket-modal-side mt-4 p-3 rounded" id="timelineContainer">
                          <input type="hidden" id="ticketId" value="${id_ticket}">
                          <input type="hidden" id="idEstadoTicket" value="${id_estado}">
                            ${accionesHTML}  <!-- aquí se inyecta la card completa -->
                        </div>
                    
                          ${mensajeUsuarioHTML}

            
                        <!-- FILA 3: Línea de tiempo dinámica (ancho completo) -->
                         <div class="row mt-1">
                          <div class="col-12">
                            <div id="lineaTiempoDinamica" class="mt-1"></div>
                          </div>
                        </div>
            </div>

                  `,
    
                width: "88%",
                showCloseButton: true,
                confirmButtonText: confirmButtonText,
                customClass: {
                  popup: "cuerpo_modal_guardar modal-ticket-tecnico",
                  confirmButton: "bt_crear",
                    title: "titulo_swal_custom"   // 👈 agregamos clase personalizada al título
                },
                
                showCancelButton: false,
                preConfirm: preConfirmAction,
                
                   didOpen: () => {
                      const popoverTriggerList = [].slice.call(document.querySelectorAll("[data-bs-toggle='popover']"));
                      popoverTriggerList.forEach(triggerEl => new bootstrap.Popover(triggerEl));
                      // 🔽 Forzar scroll arriba al abrir modal
                      document.querySelector('.cuerpo_modal_guardar')?.scrollTo({ top: 0, behavior: 'auto' });
                      
                      
                        lineaDeTiempoTicket(id_ticket).then(html => {
                          document.getElementById("lineaTiempoDinamica").innerHTML = html;
                          actualizarBarraResumenEstados();
                        });
                        
                         // ************************************************LOGICA PARA EL QR
            const btnQr = document.getElementById(btnQrId);
                if (btnQr) {
                  const qrTargetURL = new URL("/codigosQR/ticket/ticketQRinformacion.php", window.location.origin);
                  qrTargetURL.searchParams.set("id", String(id_ticket));
                  const qrImgSrc =
                    `https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=1&data=${encodeURIComponent(qrTargetURL.href)}`;
    
                  const qrBox = document.createElement("div");
                  qrBox.id = `qrPopoverTicket_${id_ticket}`;
                  qrBox.style.cssText = `
                    position:absolute;padding:10px;background:#fff;border:1px solid #ccc;
                    box-shadow:0 4px 18px rgba(0,0,0,.15);border-radius:10px;z-index:9999;display:none;
                  `;
                  qrBox.innerHTML = `
                    <div style="display:flex;flex-direction:column;align-items:center;gap:8px;min-width:210px">
                      <a href="${qrTargetURL.href}" target="_blank" rel="noopener">
                        <img src="${qrImgSrc}" alt="QR Ticket ${id_ticket}" width="180" height="180" style="display:block"/>
                      </a>
                  
               
                    </div>
                  `;
                  document.body.appendChild(qrBox);
    
                  btnQr.addEventListener("click", (e) => {
                    e.stopPropagation();
                    const visible = qrBox.style.display === "block";
                    qrBox.style.display = visible ? "none" : "block";
                    if (!visible) {
                      const r = btnQr.getBoundingClientRect();
                      qrBox.style.top  = `${r.bottom + window.scrollY + 6}px`;
                      qrBox.style.left = `${r.left + window.scrollX - 60}px`;
                    }
                  });
    
                  const onDocClick = (ev) => {
                    if (!btnQr.contains(ev.target) && !qrBox.contains(ev.target)) {
                      qrBox.style.display = "none";
                      document.removeEventListener("click", onDocClick);
                    }
                  };
                  document.addEventListener("click", onDocClick);
    
                  qrBox.addEventListener("click", (ev) => {
                    const closeBtn = ev.target.closest(`#cerrarQr_${id_ticket}`);
                    if (closeBtn) qrBox.style.display = "none";
                  });
                }
                // ********************************************LOGICA PARA EL QR


                    }
     
                
                
              }).then((result) => {
                if (result.isConfirmed) {
                  if (id_estado == 4 || id_estado == 5 || id_estado == 6) {
                    return; // Solo cerrar modal
                  }
              
                  let mensaje = "";
                  // let mensaje2 = "";
    
              
                  if (id_estado == 2) {
                    mensaje = "COMENZANDO TICKET";
                    // mensaje2 ='<p>El ticket fue comenzado  exitosamente.</p>'
                  } else if (id_estado == 3) {
                    mensaje = "TICKET FINALIZADO CON ÉXITO";
                      // mensaje2 ='<p>El ticket fue cerrado exitosamente.</p>'
                  }
              
                  Swal.fire({
                    title: `<div class="alert alert-dark">${mensaje}</div>`,
                      // html: `${mensaje2}`,
                    icon: "success",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    allowOutsideClick: false,
                    customClass: {
                      popup: "cuerpo_modal_guardar",
                      confirmButton: "btn btn-primary"
                    }
                  }).then(() => location.reload());
                }
              }).catch((error) => {
                console.error("Error:", error);
                Swal.fire({
                  icon: "error",
                  title: "Error",
                  text: "Ocurrió un error al procesar la solicitud",
                  confirmButtonColor: "#d33",
                  confirmButtonText: "CANCELAR",
                  customClass: {
                    popup: "cuerpo_modal_guardar",
                    confirmButton: "bt_eliminar"
                  },
                  timer: 5000,
                  showConfirmButton: true
                });
              });
            },
            error: function (xhr, status, error) {
              console.error("Error al obtener detalles del ticket:", error);
            }
          });
        }
        
        
        
        
        
        

    function calificarTicket(id_ticket) {
        // alert(id_ticket);
        fetch('modelos/rescatar/calificacionUsuario.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id_ticket=${id_ticket}`
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin calificación',
                    text: data.message,
                    confirmButtonText: 'Cerrar'
                });
                return;
            }
    
            const calificacion = data.data.id_calificacion;
            const comentario = data.data.comentario || '';
            const fecha = data.data.fecha;
            const hora = data.data.hora;
            const ip_usuario = data.data.ip_usuario;
    
    
    
            // Generar HTML de estrellas fijas
            let estrellasHTML = '';
            for (let i = 1; i <= 4; i++) {
                estrellasHTML += `<i class="fa fa-star ${i <= calificacion ? 'text-warning' : 'text-secondary'}" style="font-size: 35px; margin: 2px;"></i>`;
            }
    
            Swal.fire({
                title: "<div class='alert alert-dark' role='alert'>Calificación</div>",
                html: `
                    <div class="text-center">
                        <div class="mb-3">
                            ${estrellasHTML}
                         <div class="row mb-3">
                      <div class="col-md-6">
                        <label><strong>Fecha de Calificación:</strong></label>
                        <input type="text" class="form-control" value="2025-06-11" readonly>
                      </div>
                      <div class="col-md-6">
                        <label><strong>Hora:</strong></label>
                        <input type="text" class="form-control" value="14:32:00" readonly>
                      </div>
                    </div>
                    
                    <div class="mb-3">
                      <label><strong>Comentario del Usuario:</strong></label>
                    <textarea class="form-control" rows="6" readonly style="resize: none;">${comentario}</textarea>
                        </div>
                    </div>
                    </div>
                `,
                width: "800px",
                showCloseButton: true,
                     customClass: {
                  popup: "cuerpo_modal_guardar",
                  confirmButton: "bt_crear"
                },
                  allowOutsideClick: false,  // ← no se cierra al hacer click fuera
              allowEscapeKey: false      // ← opcional: bloquea cerrar con ESC
            });
        })
        .catch(error => {
            console.error("Error al cargar calificación:", error);
            Swal.fire("❌ Error", "No se pudo obtener la calificación.", "error");
        });
    }

    

    function cambioDeTecnico(id_ticket) {
      Swal.fire({
        title: "<div class='alert alert-dark' role='alert'>Cambio de Técnico</div>",
        html: `
          <label for="nuevoTecnico"><strong>Selecciona el nuevo técnico:</strong></label>
          <select id="nuevoTecnico" class="form-select mt-2">
            <option value="8">Cristian Jorquera</option>
            <option value="9">Alejandro Rojas</option>
            <option value="10">Constanza Nieto</option>
          </select>
        `,
        showCancelButton: true,
        confirmButtonText: 'Confirmar Cambio',
        cancelButtonText: 'Cancelar',
        width: "800px",
        showCloseButton: true,
        customClass: {
          popup: "cuerpo_modal_guardar",
          confirmButton: "bt_crear"
        },
        buttonsStyling: false,
        preConfirm: () => {
          const nuevoTecnico = document.getElementById('nuevoTecnico').value;
          if (!nuevoTecnico) {
            Swal.showValidationMessage('Debes seleccionar un técnico');
          }
          return nuevoTecnico;
        }
      }).then((result) => {
        if (result.isConfirmed) {
          const tecnicoSeleccionado = result.value;
          console.log("Ticket ID:", id_ticket);
          console.log("Nuevo técnico:", tecnicoSeleccionado);
    
          // Aquí podrías enviar el cambio al backend con fetch o AJAX
          // ejemplo: enviarCambioTecnico(id_ticket, tecnicoSeleccionado);
        }
      });
    }

