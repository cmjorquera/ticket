(function($) {
    "use strict"; // Start of use strict
  
    // Toggle the side navigation
    $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
      $("body").toggleClass("sidebar-toggled");
      $(".sidebar").toggleClass("toggled");
      if ($(".sidebar").hasClass("toggled")) {
        $('.sidebar .collapse').collapse('hide');
      };
    });
  
    // Close any open menu accordions when window is resized below 768px
    $(window).resize(function() {
      if ($(window).width() < 768) {
        $('.sidebar .collapse').collapse('hide');
      };
      
      // Toggle the side navigation when window is resized below 480px
      if ($(window).width() < 480 && !$(".sidebar").hasClass("toggled")) {
        $("body").addClass("sidebar-toggled");
        $(".sidebar").addClass("toggled");
        $('.sidebar .collapse').collapse('hide');
      };
    });
  
    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
    $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
      if ($(window).width() > 768) {
        var e0 = e.originalEvent,
          delta = e0.wheelDelta || -e0.detail;
        this.scrollTop += (delta < 0 ? 1 : -1) * 30;
        e.preventDefault();
      }
    });
  
    // Scroll to top button appear
    $(document).on('scroll', function() {
      var scrollDistance = $(this).scrollTop();
      if (scrollDistance > 100) {
        $('.scroll-to-top').fadeIn();
      } else {
        $('.scroll-to-top').fadeOut();
      }
    });
  
    // Smooth scrolling using jQuery easing
    $(document).on('click', 'a.scroll-to-top', function(e) {
      var $anchor = $(this);
      $('html, body').stop().animate({
        scrollTop: ($($anchor.attr('href')).offset().top)
      }, 1000, 'easeInOutExpo');
      e.preventDefault();
    });
  
  })(jQuery); // End of use strict
  
  
  
  
  
  
  // function mostrarModalConTextarea(id) {
  //     // alert(id); // Puedes quitar este alert una vez que confirmes que la función recibe el ID correctamente.
  //     $.ajax({
  //         type: "POST",
  //         url: "modelos/rescatar/usuario.php",
  //         dataType: "json",
  //         data: {
  //             id: id
  //         },
  //         success: function(data) {
  //             if (data) {
  //                 var nombreUsuario = data.nombre || "No disponible";
  //                 var apellidoPaterno = data.apellido_paterno || "No disponible";
  //                 var anexo = data.anexo || "No disponible";
  //                 var idUsuario = data.id || "No disponible";
  //                 var email = data.email || "No disponible";
  
  
  //             }
  //             Swal.fire({
  //                 title: '<div class="alert alert-dark" role="alert">Enviar mensaje</div>',
  //                 html: `
  //                 <div class="alert alert-primary" role="alert">
  //                 <div class="form-group"> <!-- Grupo de formulario para la casilla de verificación "Urgente" -->
  //                         <div class="form-check">
  //                             <input type="checkbox" class="form-check-input" id="swal-input-urgent">
  //                             <label class="form-check-label" for="swal-input-urgent">Urgente</label>
  //                         </div>
  //                     </div>  
  //             </div>
              
  //                     <div class="row"> <!-- Contenedor de fila para las dos columnas -->
  //                         <div class="col"> <!-- Primera columna para "De:" -->
  //                             <div class="form-group">
  //                                 <label for="swal-input-rem">De:</label>
  //                                 <input type="text" id="swal-input-rem" class="form-control" value="Catalina" readonly>
  //                             </div>
  //                         </div>
  //                         <div class="col"> <!-- Segunda columna para "Para:" -->
  //                             <div class="form-group">
  //                                 <label for="swal-input-dest">Para:</label>
  //                                 <input type="text" id="swal-input-dest" class="form-control" value="" readonly> 
  //                             </div>
  //                         </div>
  //                     </div>
  //                     <div class="form-group" style="padding:40px;"> 
  //                         <label for="swal-input-msg">Mensaje:</label>
  //                         <textarea id="swal-input-msg" class="form-control" rows="6" placeholder="Escribe el mensaje aquí..."></textarea>
  //                     </div>
  //                 `,
  //                 didOpen: () => {
  //                     // Actualiza el valor del input después de que el modal se haya abierto
  //                     document.getElementById('swal-input-dest').value = nombreUsuario + " " +
  //                         apellidoPaterno;
  //                 },
  //                 showCancelButton: true,
  //                 confirmButtonColor: '#5B8E4A',
  //                 cancelButtonColor: '#d33',
  //                 confirmButtonText: 'ENVIAR',
  //                 cancelButtonText: 'CANCELAR',
  //                 customClass: {
  //                     popup: 'cuerpo_modal_guardar',
  //                     confirmButton: 'bt_activar_alumno',
  //                     cancelButton: 'bt_activar_alumno'
  //                 },
  //                 preConfirm: () => {
  //                     const mensaje = document.getElementById('swal-input-msg').value;
  //                     // Verificar que el mensaje no esté vacío antes de proceder
  //                     if (!mensaje.trim()) {
  //                         Swal.showValidationMessage(
  //                             "Por favor, escribe un mensaje."
  //                             ); // Muestra un mensaje de error si el textarea está vacío
  //                         return false; // Previene que el modal se cierre
  //                     }
  //                     // Si hay mensaje, muestra un nuevo modal con el contenido del mensaje
  //                     Swal.fire({
  //                         title: '<div class="alert alert-dark" role="alert">CONFIRMACION </div>',
  //                         html: `
                          
  //                         <div class="alert alert-danger" role="alert">
  //                             <div class="row"> <!-- Contenedor de fila para las dos columnas -->
  //                                 <div class="col"> <!-- Primera columna para "De:" -->
  //                                     <div class="form-group">
  //                                         <label for="swal-input-rem">De:</label>
  //                                         <input type="text" id="idUsuarioLogiado" class="form-control" value="Catalina" readonly>
  //                                     </div>
  //                                 </div>
  //                                 <div class="col"> <!-- Segunda columna para "Para:" -->
  //                                     <div class="form-group">
  //                                         <label for="swal-input-dest">Para:</label>
  //                                         <input type="text" id="idUsuarioDestino" class="form-control" value="" readonly> 
  //                                     </div>
  //                                 </div>
  //                             </div>
  //                         </div>                                        
  //                         <textarea class="form-control" rows="6" readonly>${mensaje}</textarea>`,
  //                         showCancelButton: true,
  //                         confirmButtonColor: '#5B8E4A',
  //                         cancelButtonColor: '#d33',
  //                         confirmButtonText: 'ENVIAR',
  //                         cancelButtonText: 'CANCELAR',
  //                         customClass: {
  //                             popup: 'cuerpo_modal_guardar',
  //                             confirmButton: 'bt_activar_alumno',
  //                             cancelButton: 'bt_activar_alumno'
  //                         },
  //                         didOpen: () => {
  //                             // Actualiza el valor del input después de que el modal se haya abierto
  //                             document.getElementById('idUsuarioDestino').value =
  //                                 nombreUsuario + " " + apellidoPaterno;
  //                         },
  //                     }).then((result) => {
  //                         if (result.isConfirmed) {
  //                             // Realiza la llamada AJAX a 'guardar_mensaje.php'
  //                             $.ajax({
  //                                 type: "POST",
  //                                 // url: "modelos/rescatando/usuario.php",
  //                                 url: "modelos/guardar/guardar_mensaje.php",
  //                                 data: {
  //                                     idUsuario: idUsuario, // id del usuario
  //                                     mensaje: mensaje, // Usar el mensaje capturado previamente
  //                                     email: email, // Usar el mensaje capturado previamente
  //                                     destinatario: nombreUsuario + " " +
  //                                         apellidoPaterno
  //                                 },
  
  
  //                                 success: function(response) {
  //                                     Swal.fire({
  //                                         icon: 'success',
  //                                         title: '<div class="alert alert-dark" role="alert">' +
  //                                             "MENSAJE ENVIADO" +
  //                                             '</div>',
  //                                         showConfirmButton: false,
  //                                         width: "470px",
  //                                         timer: 2500,
  //                                         customClass: {
  //                                             popup: 'cuerpo_modal_guardar',
  //                                         }
  //                                     }).then(() => {
  //                                         location
  //                                             .reload(); // Aquí está la línea que recarga la página
  //                                     })
  //                                 },
  //                                 error: function(xhr, status, error) {
  //                                     Swal.fire('Error',
  //                                         'Hubo un problema al enviar tu mensaje.',
  //                                         'error');
  //                                 }
  //                             });
  //                         }
  //                     });
  //                     return false; // Previene que el primer modal se cierre automáticamente
  //                 }
  
  //             });
  //         }
  //     });
  // }
  
  
  // //INGRESAR TICKET 
  // function ingresarTiqweqcket() {
  //             // Asumimos que estas variables PHP son validadas y escapadas correctamente en el servidor
  //             var idUsuarioSession = "<?php echo $idUsuarioSession; ?>";
  //             var idAreaTrabajo = "<?php echo $idAreaTrabajo; ?>";
  //             var nombresession = "<?php echo $nombresession; ?>";
      
  //             // Obtener la fecha actual en formato adecuado para input de tipo 'date'
  //             var today = new Date();
  //             var dd = String(today.getDate()).padStart(2, '0');
  //             var mm = String(today.getMonth() + 1).padStart(2, '0'); // Enero es 0!
  //             var yyyy = today.getFullYear();
  //             today = yyyy + '-' + mm + '-' + dd; // Formato 'YYYY-MM-DD'
      
      
      
  //             Swal.fire({
  //                 title: '<div class="alert alert-dark" role="alert">Nuevo Ticket</div>',
  //                 html: `
  //                 <div class="alert alert-danger" role="alert">
  //                     <input type="hidden" id="swal-input1" value="${idUsuarioSession}">
  //                     <input type="hidden" id="swal-input2" value="${idAreaTrabajo}">
      
  //                     <div class="container-fluid"> <!-- Cambié a container-fluid para máximo ancho -->
  //                         <div class="row">
  //                             <div class="col-md-6 col-sm-12"> <!-- Asegúrate de que colapse en móviles -->
  //                                 <div class="input-column">
  //                                     <label for="fecha_hoy" class="form-label">Fecha:</label>
  //                                     <input type="date" id="fecha_hoy" class="form-control mb-3" placeholder="" value="${today}" disabled>
  //                                     <label for="usuario_problema" class="form-label">Usuario:</label>
  //                                     <input type="text" id="usuario_problema" class="form-control mb-3" placeholder="" value="${nombresession}" readonly>
  //                                 </div>
  //                             </div>
  //                             <div class="col-md-6 col-sm-12"> <!-- Asegúrate de que colapse en móviles -->
  //                                 <img id="image-preview" src="#" alt="Image Preview" style="display: none; width: 100%; height: auto; max-height: 200px;">
  //                             </div>
  //                         </div>
  //                         <div class="row mt-3">
  //                             <div class="col-12"> <!-- Ocupa el ancho completo -->
  //                                 <label for="id_asunto" class="form-label">Asunto:</label>
  //                                 <input type="text" id="id_asunto" class="form-control mb-2" placeholder="">
  //                                 <textarea id="id_ticket_texarea" rows="6" class="form-control" placeholder="Describa el asunto aquí" style="height: 200px;"></textarea>
  //                             </div>
  //                         </div>
  //                     </div>
  //                 </div>
  //             `,
      
  //                 showCancelButton: true,
  //                 confirmButtonColor: '#5B8E4A',
  //                 cancelButtonColor: '#d33',
  //                 confirmButtonText: 'ENVIAR',
  //                 cancelButtonText: 'CANCELAR',
  //                 width: "870px",
  //                 padding: "40px",
  //                 customClass: {
  //                     popup: 'cuerpo_modal_guardar',
  //                     confirmButton: 'bt_activar_alumno',
  //                     cancelButton: 'bt_activar_alumno'
  //                 },
  //                 preConfirm: () => {
  //                     let asunto = $('#id_asunto').val().trim();
  //                     let ticket_texarea = $('#id_ticket_texarea').val().trim();
      
      
  //                     if (!asunto) {
  //                         Swal.showValidationMessage("Por favor, describa el asunto.");
  //                         return false;
  //                     }
             
  //                     if (!ticket_texarea) {
  //                         Swal.showValidationMessage("Por favor, describa el problema.");
  //                         return false;
  //                     }
  //                 }
  //             }).then((result) => {
  //                 if (result.isConfirmed) {
  //                     let formData = new FormData();
  //                     formData.append('idUsuarioSession', "<?php echo $idUsuarioSession; ?>");
  //                     formData.append('area_trabajo', "<?php echo $idAreaTrabajo; ?>");
  //                     formData.append('asunto', $('#id_asunto').val());
  //                     formData.append('ticket_texarea', $('#id_ticket_texarea').val());
  //                     formData.append('accion', 'ingresarTicket');
  //                     // formData.append('archivo', $('#file-input')[0].files[0]); // Agregar el archivo
      
  //                     $.ajax({
  //                         url: "modelos/guardar/guardar_ticket.php",
  //                         type: 'POST',
  //                         processData: false,
  //                         contentType: false,
  //                         data: formData,
  //                         success: function(response) {
  //                             Swal.fire('Enviado', 'Su ticket ha sido registrado exitosamente.',
      
      
      
      
  //                                 'success').then((result) => {
  //                                 if (result.isConfirmed) {
  //                                     location.reload();
  //                                 }
  //                             });
  //                         },
  //                         error: function() {
  //                             Swal.fire('Error', 'No se pudo registrar el ticket. Intente nuevamente.',
  //                                 'error').then((result) => {
  //                                 if (result.isConfirmed) {
  //                                     location.reload();
  //                                 }
  //                             });
  //                         }
  //                     });
  //                 }
  //             });
  // }
  
  
  // function agregar_contenedor(idUsuario) {
  //     const content = document.createElement('div');
  //     content.className = 'modal-content'; // Se agrega esta clase para aplicar los estilos CSS
  //     content.innerHTML = `
  //                 <div class="alert alert-primary" role="alert">
  //                     <div class="row">
  //                         <!-- Columna izquierda para cargar y previsualizar imagen -->
  //                         <div class="col-md-6 col-sm-12">
  //                             <div class="input-column">
  //                                 <label for="imageInput" class="form-label">Imagen:</label>
  //                                 <input type="file" id="imageInput" accept="image/*" onchange="previewImage(event)">
  //                                 <!-- Imagen predeterminada establecida en el atributo src -->
  //                                 <img id="imagePreview" src="imagenes/sin_iamgen.png" alt="Vista previa de la imagen" style="width: 100%; height: auto; max-height: 200px; margin-top: 10px;">
  //                             </div>
  //                         </div>
  
  //                         <!-- Columna derecha para los inputs de texto -->
  //                         <div class="col-md-6 col-sm-12">
  //                             <div class="input-column">
  //                                 <label for="textInput1" class="form-label">Nombre Contenedor:</label>
  //                                 <input type="text" id="textInput1" class="form-control mb-3" placeholder="Nombre del contenedor">
  
  //                                 <label for="textInput2" class="form-label">URL:</label>
  //                                 <input type="text" id="textInput2" class="form-control" placeholder="URL del contenedor">
  //                             </div>
  //                         </div>
  //                     </div>
  //                 </div>
  //             `;
  
  //     Swal.fire({
  //         title: '<div class="alert alert-dark" role="alert">AGREGAR CONTENEDOR</div>',
  //         html: content,
  //         width: '870px',
  //         padding: '40px',
  //         showCancelButton: true,
  //         confirmButtonColor: '#5B8E4A',
  //         cancelButtonColor: '#d33',
  //         confirmButtonText: 'CREAR',
  //         cancelButtonText: 'CANCELAR',
  //         customClass: {
  //             popup: 'cuerpo_modal_guardar',
  //             confirmButton: 'bt_activar_alumno',
  //             cancelButton: 'bt_activar_alumno'
  //         },
  //         preConfirm: () => {
  //             return new Promise((resolve) => {
  //                 const imageInput        = document.getElementById('imageInput').files[0];
  //                 const nombreContenedor = document.getElementById('textInput1').value;
  //                 const url = document.getElementById('textInput2').value;
  
  //                 let formData = new FormData();
  //                 formData.append('image', imageInput);
  //                 formData.append('nombreContenedor', nombreContenedor);
  //                 formData.append('url', url);
  //                 formData.append('idUsuario', idUsuario); // Añadir ID de usuario al formData
  
  //                 $.ajax({
  //                     url: 'modelos/guardar/guardar_contenedor.php',
  //                     type: 'POST',
  //                     data: formData,
  //                     contentType: false,
  //                     processData: false,
  //                     success: function(response) {
  //                         Swal.fire({
  //                             title: 'Guardado',
  //                             text: 'El contenedor ha sido guardado correctamente',
  //                             icon: 'success',
  //                             confirmButtonText: 'OK',
  //                             customClass: {
  //                                 popup: 'cuerpo_modal_guardar',
  //                                 confirmButton: 'bt_activar_alumno',
  //                             },
  //                         }).then((result) => {
  //                             if (result.value) {
  //                                 // Recargar la página después de que el usuario haga clic en 'OK'
  //                                 window.location.reload();
  //                             }
  //                         });
  //                         resolve();
  //                     },
  //                     error: function() {
  //                         Swal.fire('Error', 'Hubo un error al guardar el contenedor',
  //                             'error');
  //                         resolve();
  //                     }
  //                 });
  //             });
  //         }
  //     });
  // }
  
  
  // function previewImage(event) {
  //     const file = event.target.files[0];
  //     if (file) {
  //         const reader = new FileReader();
  //         reader.onload = function(e) {
  //             const imgElement = document.getElementById('imagePreview');
  //             imgElement.src = e.target.result;
  //         };
  //         reader.readAsDataURL(file);
  //     }
  // }
  
  // // Función para confirmar la eliminación
  // function eliminarContenedor(id) {
  //     Swal.fire({
  //         title: '<div class="alert alert-dark" role="alert">¿ESTÁS SEGURO?</div>',
  //         text: "No podrás revertir esto!",
  //         icon: 'warning',
  //         width: '570px',
  //         padding: '40px',
  //         showCancelButton: true,
  //         confirmButtonColor: '#5B8E4A',
  //         cancelButtonColor: '#d33',
  //         confirmButtonText: 'ELIMINAR',
  //         cancelButtonText: 'CANCELAR',
  //         customClass: {
  //             popup: 'cuerpo_modal_eliminar',
  //             confirmButton: 'bt_activar_alumno',
  //             cancelButton: 'bt_activar_alumno'
  //         },
  //     }).then((result) => {
  //         if (result.isConfirmed) {
  //             $.ajax({
  //                 url: 'modelos/eliminar/eliminar_contenedor.php',  // Asegúrate de que esta URL es correcta
  //                 type: 'POST',
  //                 data: { id: id },  // Envía el ID del contenedor a eliminar
  //                 success: function(response) {
  //                     // Procesa la respuesta del servidor
  //                     if (response.success) {
  //                         Swal.fire({
  //                             title: 'Eliminado!',
  //                             text: 'El contenedor ha sido eliminado.',
  //                             icon: 'success',
  //                             confirmButtonText: 'OK'
  //                         }).then(() => {
  //                             location.reload();
  //                                                     });
  //                     } else {
  //                         Swal.fire('Error', 'No se pudo eliminar el contenedor: ' + response.error, 'error');
  //                     }
  //                 },
  //                 error: function(xhr, status, error) {
  //                     Swal.fire('Error', 'Ha ocurrido un error al intentar eliminar el contenedor: ' + error, 'error');
  //                 }
  //             });
  //         }
  //     });
  // }
  