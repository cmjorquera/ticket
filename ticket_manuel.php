<?php
    session_start();
    require_once 'class/conexion.php';// Asegúrate de ajustar la ruta al archivo 'funciones.php' según la estructura de tu directorio
    require_once 'class/funciones.php';
    $funciones = new Funciones();// Crear una instancia de la clase Funciones (aca puedo llamar a todas la funciones de la class funciones)
    $idUsuarioSession = htmlspecialchars($_SESSION['id']);
    $idPagActual      = 4;  // PAGINA DEL ADMINISTARDPR
    


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <?php  $funciones->header();   ?>
    <script type='text/javascript' src='template_01/js/funciones.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/ticket.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script type="text/javascript" src="funciones.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
    <script data-search-pseudo-elements defer
        src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js" crossorigin="anonymous">
    </script>
</head>


<body id="page-top">
    <div id="wrapper">
        <?php  $funciones->menuLateral2($idUsuarioSession, $idPagActual);   ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php  $funciones->cabezera();   ?>
                </nav>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <!-- <h1 class="h3 mb-0 text-gray-800">Tichet</h1> -->
                        <h1 class="h3 mb-0 text-gray-800"></h1>
                    </div>
                 
                    <div class="row">
                        <?php  $funciones->contenedorTicketResibidos($idUsuarioSession,$idPagActual);   ?>
                        <?php  $funciones->contenedorTicketAsignados($idUsuarioSession,$idPagActual);   ?>
                        <?php  $funciones->contenedorTicketEnProceso($idUsuarioSession,$idPagActual);   ?>
                        <?php  $funciones->contenedorTicketTerminados($idUsuarioSession,$idPagActual);   ?>        
                    </div>



                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Ticket </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <?php  $funciones->ticketAdministrador($idUsuarioSession);   ?>        
                            </div>
                        </div>
                </div>
            </div>
            <?php  $funciones->footer();   ?>
        </div>
    </div>
            <a class="scroll-to-top rounded" href="#page-top">
                <i class="fas fa-angle-up"></i>
            </a>
            <?php include("modal_salir.php")?>
            <?php  $funciones->script();   ?>





            <script>
//****************************************************** */  
            function validaTexto(id, error) {
                var er = "";
                var campo = document.getElementById(id);
                campo.classList.remove("campo-invalido"); // Elimina la clase de campo inválido antes de realizar la validación
                campo.style.background = "#FFFFFF"; // Restablece el color de fondo a blanco

                if (campo.value.trim() === "") {
                    campo.classList.add("campo-invalido"); // Agrega la clase de campo inválido para resaltar el borde en rojo
                    er = error; // Establece el mensaje de error
                }

                // Agrega un controlador de eventos para revertir el color del borde cuando el usuario comienza a escribir
                campo.addEventListener('input', function() {
                    this.classList.remove("campo-invalido"); // Elimina la clase de campo inválido
                });

                return er; // Retorna el mensaje de error (vacío si no hay error)
            }


//****************************************************** */  

            function asignando_tecnico(usuarioTecnico, fechaAsignacionTicket, fechaResolucionTicket, idTicket,
                usuarioProblema, nombreUsuarioProblema, hora, estado,asunto,problema) {
                // alert(usuarioTecnico);
                if (!usuarioTecnico) return; // Exit if no technician is selected
                //  usuarioProblema  // ESTE ES EL ID DEL USUARIO CON EL PROBLEMA .
                // usuarioTecnico  // ESTE ES EL ID DEL TECNICO (A VEVES NO EXISTE AUN )

                    document.addEventListener('DOMContentLoaded', function() {
                    const inputFechaTermino = document.getElementById('fecha_termino');
                    const fechaHoy = new Date();
                    const fechaMinima = fechaHoy.toISOString().split('T')[0]; // Formatea la fecha actual a YYYY-MM-DD

                    inputFechaTermino.setAttribute('min', fechaMinima);
                });

                $.ajax({
                    url: "modelos/rescatar/usuario.php",
                    type: 'POST',
                    data: {
                        id: usuarioTecnico
                    },
                    dataType: 'json',
                    success: function(data) {
                        let nombreTecnico = `${data.nombre} ${data.apellido_paterno}`;

                        let fechaResolucionHtml = fechaResolucionTicket ? `
                        <label for="fechaAsignacion" class="form-label">Fecha de Resolución:</label>
                        <input type="date" id="fechaAsignacion" class="form-control mb-3" value="${fechaResolucionTicket}" disabled>
                            ` : '';


                        let contentHtml = `
                                    <div class="alert alert-secondary" role="alert">
                                        <form class="row g-3">
                                            <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingName" placeholder=""value="${fechaAsignacionTicket}" disabled>
                                                <label for="floatingName">Fecha ticket</label>
                                            </div>
                                            </div>
                                            <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="email" class="form-control" id="floatingEmail" placeholder="Your Email" value="${hora}" disabled>
                                                <label for="floatingEmail">Hora Ticket</label>
                                            </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="nombreUsuarioProblema" placeholder="" value="${nombreUsuarioProblema}" disabled>
                                                    <label for="nombreUsuarioProblema">De</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control"  id="floatingPassword" placeholder="" value="${estado}" disabled>
                                                    <label for="floatingPassword">Estado</label>
                                                </div>
                                            </div>
                                
                                            <div class="col-md-12">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="floatingName" placeholder="Asunto"   value="${asunto}" disabled>
                                                    <label for="floatingName">Asunto</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating">
                                                    <textarea class="form-control" placeholder="Address" id="floatingTextarea" disabled style="height: 100px;">${problema}</textarea>
                                                    <label for="floatingTextarea">Problema</label>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                               

                                    <div class="alert alert-secondary" role="alert">
                                        <form class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3">
                                                    <select class="form-select" id="prioridadTicket" aria-label="prioridadTicket">
                                                        <option value="alta">Alta</option>
                                                        <option value="media">Media</option>
                                                        <option value="baja">Baja</option>
                                                    </select>
                                                    <label for="prioridadTicket">Prioridad</label>
                                                </div>
                                            </div>    
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control"  id="floatingPassword" placeholder="" value="${nombreTecnico}" disabled>
                                                    <label for="floatingPassword">Responsable</label>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-floating">
                                                    <input type="date" class="form-control"  id="fecha_termino" placeholder="">
                                                    <label for="fecha_termino">fecha Termino</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating">
                                                    <textarea class="form-control" placeholder="Address" id="comentarioTicket" style="height: 100px;"></textarea>
                                                    <label for="comentarioTicket">Asignar Comentario</label>
                                                </div>
                                            </div>
                                            <div class="alert alert-danger" role="alert" id="daysSinceTicket" style="display: none;"></div>
                                        </form>              
                                    </div>`;
                        Swal.fire({
                            title: '<div class="alert alert-dark" role="alert">ASIGNANDO TICKET</div>',
                            html: contentHtml,
                            width: "870px",
                            padding: "40px",
                            showCancelButton: true,
                            confirmButtonColor: '#5B8E4A',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'ENVIAR',
                            cancelButtonText: 'CERRAR',
                            customClass: {
                                popup: 'cuerpo_modal_guardar',
                                confirmButton: 'bt_activar_alumno',
                                cancelButton: 'bt_activar_alumno'
                            },
                            didOpen: function() {
                                // const inputFechaTermino     = document.getElementById('fecha_termino');
                                const alertDaysSinceTicket  = document.getElementById('daysSinceTicket');
                                const inputFechaTermino     = document.getElementById('fecha_termino');
                                const prioridadTicket     = document.getElementById('prioridadTicket');

                                const fechaHoy              = new Date();
                                const fechaMinima           = fechaHoy.toISOString().split('T')[0]; // Formatea la fecha actual a YYYY-MM-DD
                                inputFechaTermino.setAttribute('min', fechaMinima);
                               
                          

                                inputFechaTermino.addEventListener('change', function() {
                                    const fechaElegida = this.value;
                                    const fechaHoy = new Date();
                                    fechaHoy.setHours(0, 0, 0, 0);

                                    
                                    if (fechaElegida) {
                                        const dias = calculateDaysDifference(fechaHoy,
                                            fechaElegida);
                                        alertDaysSinceTicket.textContent =
                                            `Días hasta la fecha de término: ${dias}`;
                                        alertDaysSinceTicket.style.display = 'block';
                                    } else {
                                        alertDaysSinceTicket.style.display = 'none';
                                    }
                                });
                            },
                            preConfirm: () => {
                                const selectedDate      = document.getElementById('fecha_termino').value;
                                const comentario        = document.getElementById('comentarioTicket').value;
                                const prioridad         = document.getElementById('prioridadTicket').value;
                                const diffDays          = document.getElementById('daysSinceTicket').innerText.replace(/[^0-9]/g,''); // Esto asume que el texto es "X días hasta la fecha de término"

                                        
                                if (!selectedDate) {
                                    Swal.showValidationMessage(
                                        "Por favor selecciona una fecha de término");
                                    return false;
                                }

                                return {
                                    selectedDate,
                                    comentario,
                                    prioridad,
                                    diffDays
                                }; // Retornar todos los datos para su uso en then
                           
                            }


                        }).then((result) => {
                            if (result.isConfirmed) {
                                enviarDatos(
                                    result.value.selectedDate,          // Fecha seleccionada para la terminación
                                    fechaAsignacionTicket,              // Fecha de asignación del ticket
                                    nombreTecnico,                      // Información del técnico
                                    data.id,                            // ID del técnico
                                    idTicket,                           // ID del ticket
                                    usuarioProblema,                    // ID del usuario con problema
                                    result.value.diffDays,              // Días hasta la fecha de término calculados
                                    result.value.comentario,            // Comentario asignado
                                    result.value.prioridad              // Prioridad seleccionada
                                );
                            }
                        });


                        alert(prioridad);


                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching user data: " + error);
                        alert("Error loading user information.");
                    }
                });
            }


            function calculateDaysDifference(startDate, endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                return diffDays;
            }

            function enviarDatos(selectedDate, ticketDate, technicianInfo, id_responsable, idTicket, usuarioProblema,
                diffDays, comentario, prioridad) {

                    // alert(prioridad);
                $.ajax({
                    url: "modelos/guardar/guardar_responsable_ticket.php",
                    type: 'POST',
                    data: {
                        fechaSeleccionada: selectedDate,
                        fechaTicket: ticketDate,
                        nombreTecnico: technicianInfo,
                        id_responsable: id_responsable,
                        idTicket: idTicket,
                        userId: usuarioProblema,
                        diffDays: diffDays,
                        comentario: comentario,
                        prioridad: prioridad,
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: '<div class="alert alert-dark" role="alert">TECNICO ASIGNADO</div>',
                            showConfirmButton: false,
                            showCancelButton: false,
                            timer: 2000, // 5000 milisegundos = 5 segundos
                            timerProgressBar: true, // Muestra una barra de progreso que indica el tiempo restante
                            customClass: {
                                popup: 'cuerpo_modal_guardar'
                            },
                            willClose: () => {
                                // Código que se ejecuta cuando el modal se cierra
                                console.log('Modal cerrado');
                                location.reload(); // Recarga la página tras cerrarse el modal
                            }
                        }).then((result) => {
                            /* Si se usa timer, result.dismiss puede ser Swal.DismissReason.timer */
                            if (result.dismiss === Swal.DismissReason.timer) {
                                console.log('Cerrado por el timer');
                                // Recargar aquí si no se desea esperar a que `willClose` se ejecute por alguna razón
                            }
                        });


                    }
                });
            }

            function agregarComentarioTecnico(idTicket) {
                var comentario = $('#comentario_tecnico').val();
                if (comentario.trim() === '') {
                    alert('Por favor, escribe un comentario antes de enviar.');
                    return;
                }
                $.ajax({
                    url: "modelos/guardar/guardar_avance_tecnicos.php",
                    type: 'POST',
                    data: {
                        id: idTicket,
                        comentario: comentario
                    },
                    success: function(response) {
                        alert('Comentario agregado correctamente');
                        $('#comentario_tecnico').val(''); // Limpiar el campo de texto
                        actualizarTimeline(idTicket); // Llamar a la función para actualizar la línea de tiempo
                    },
                    error: function() {
                        alert('Error al agregar el comentario');
                    }
                });
            }


            function actualizarTimeline(idTicket) {
                $.ajax({
                    url: "modelos/rescatar/avances_tecnicos.php",
                    type: "POST",
                    data: {
                        id: idTicket
                    },
                    dataType: "json",
                    success: function(data) {
                        console.log("Datos recibidos para actualizar el timeline:", data);
                        var accionesHTML = construirAccionesHTML(data.acciones);
                        $('#collapseOne .accordion-body .sbp-preview-content .timeline').html(accionesHTML);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al obtener la línea de tiempo:', error);
                    }
                });
            }

    
            function construirAccionesHTML(acciones) {
                var accionesHTML = '';
                if (acciones && acciones.length > 0) {
                    acciones.forEach(function(accion) {
                        // Check if the action includes specific keywords
                        var styleClass = '';
                        if (accion.accion.includes("Ticket Finalizado") || accion.accion.includes("Inicio Ticket")) {
                            styleClass = 'style="color: red;"'; // Apply red color style if conditions are met
                        }

                        accionesHTML += `
                            <div class="timeline-item">
                                <div class="timeline-item-marker">
                                    <div class="timeline-item-marker-text">${accion.fecha_avance}(${accion.hora_avance})</div>
                                    <div class="timeline-item-marker-indicator bg-primary"></div>
                                </div>
                                <div class="timeline-item-content" ${styleClass}>${accion.accion}</div>
                            </div>`;
                    });
                } else {
                    accionesHTML = '<div class="timeline-item"><div class="timeline-item-content">No hay avances registrados.</div></div>';
                }
                return accionesHTML;
            }
            function construirAccionesAcordeonHTML(acciones, idTicket, estado,fecha_proceso,hora_proceso) {
                var accionesHTML = construirAccionesHTML(acciones); // Usar la función original para construir el HTML de las acciones

                if (estado == 3 || estado == 5) {
                    // El acordeón se muestra solo si el estado es 3
                    return `
                        <div class="accordion" id="accordionExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Detalle del Avance del Técnico 
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <div class="alert alert-secondary" role="alert">
                                            <div class="sbp-preview-content">
                                                <div class="timeline timeline-xs">${accionesHTML}</div>
                                            </div>
                                        </div>
                        
                                    </div>
                                </div>
                        
                            </div>
                        </div></br>`;
                } else {
                    // Retorna un string vacío si el estado no es 3
                    return '';
                }
            }

            function verTicket(id) {
                $.ajax({
                    url: "modelos/rescatar/ticket.php",
                    type: "POST",
                    data: {
                        id: id
                    },
                    dataType: "json",
                    success: function(data) {
                        if (data) {
                            // INFORMACION ESTADO 1 (RECIBIDO) EL USUARIO DA ESTA INFORMACION
                            var fecha                   = data.fecha || '';
                            var nombreUsuario           = data.nombre || "No disponible";
                            var apellidoPaterno         = data.apellido_paterno || "No disponible";
                            var nombreTecnico           = data.nombreTecnico || "No disponible";
                            var apellidoTecnico           = data.apellidoTecnico || "No disponible";
                            var nombreApellidoTecnico =   nombreTecnico + ' ' + apellidoTecnico;

                            var estado                  = data.estado || "No disponible";
                            var idAsunto                = data.asunto || "No disponible";
                            var nombreEstado            = data.nombreEstado || "No disponible";
                            var detallesTicket          = data.ticket_texarea || "No hay detalles disponibles.";
                            var hora                    = data.hora || "No hay detalles disponibles.";
                            var idt                     = data.id || '';


                            // INFORMACION ESTADO 2 (ASIGNADO) MANUEL DA ESTA INFO
                            var prioridad               = data.prioridad || '';             // COMBO-BOX
                            var dias_asignados          = data.dias_asignados || '';     // CALCULO DE LA FECHA ACTUAL A LA FECHA DE ASIGANCION
                            var fecha_asignacion        = data.fecha_asignacion || '';  // FECHA QUE DIO MANUEL
                            var comen_asignacion        = data.comentario_asignacion || "";   // COMENTARIO QUE HACE MANUEL          
                            var fecha_asigna_click      = data.fecha_asignacion_click  ||"";                // FECHA Y HORA EN QUE MANUEL ASIGNA UN TECNICO          
                            if (fecha_asigna_click) {
                            var fechaPartes             = fecha_asigna_click.split('-'); // Dividir la fecha en partes [año, mes, día]
                            var fecha_asigna_click      = fechaPartes[2] + '-' + fechaPartes[1] + '-' + fechaPartes[0]; // Reordenar a [día-mes-año]
                            } else {
                                var fecha_asigna_click = ""; // Mantener vacío si no hay fecha
                            }                        
                            var hora_asigna_click       = data.hora_asignacion_click || "";   // HORAA EN QUE MANUEL ASIGNA UN TECNICO          
       
                          
                            // INFORMACION ESTADO 3 (PROCESO) EL TENCINO DA ESTA INFORMACION (CUANDO COMINEZA A TRABAJAR EN EL TICKET)
                            var dias_proceso           = data.dias_proceso || '';     // CUANTOS DIAS SE DEMORO EN CONTESTAR EL  TICKET
                            var fecha_proceso          = data.fecha_proceso || '';   // FECHA EN QUE COMENZO EL TICKET
                            if (fecha_proceso) {
                            var fechaPartes             = fecha_proceso.split('-'); // Dividir la fecha en partes [año, mes, día]
                            var fecha_proceso           = fechaPartes[2] + '-' + fechaPartes[1] + '-' + fechaPartes[0]; // Reordenar a [día-mes-año]
                            } else {
                                var fecha_proceso = ""; // Mantener vacío si no hay fecha
                            }             
                            var hora_proceso           = data.hora_proceso || '';   // HORA EN QUE COMENZO EL TICKET


                            // INFORMACION ESTADO 5 (TERMINADO) EL TENCINO DA ESTA INFORMACION
                            var fecha_termino           = data.fecha_termino || '';    // FECHA EN QUE TERMINO EL TICKET
                            var hora_termino            = data.hora_termino || '';      // HORA EN QUE TERMINO EL TICKET
                            var dias_termino            = data.dias_termino || '';      // DIAS QUE SE DEMORO EN TERMINAR EL TICKET
                            var comentario_final        = data.comentario_final || '';   // COMENTARIO AL FINALIZAR EL TICKET

                            var accion = data.accion || '';                                             //TABLA avance_tecnicos     
                            var accionesAcordeonHTML = construirAccionesAcordeonHTML(data.acciones, data.id,estado,fecha_proceso,hora_proceso);      





                            var datosAsignacionClickHTML;
                                if (fecha_asigna_click.trim() === '0000-00-00') {
                                    datosAsignacionClickHTML = '';
                                } else {
                                    datosAsignacionClickHTML =
                                    `<div class="alert alert-danger" role="alert">
                                        <div class="row">
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label for="fecha_click" class="form-label">Fecha Manuel hizo click:</label>
                                                        <input type="text" id="fecha_click" class="form-control mb-3" value="${fecha_asigna_click}" readonly>                                                   
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group">
                                                    <label for="nombre_tecnico" class="form-label">Hora Manuel hizo click:</label>
                                                        <input type="text" id="nombre_tecnico" class="form-control mb-3" value="${hora_asigna_click}" readonly>                                       
                                                    </div>
                                                </div>
                                        </div>
                                    </div>`;
                                    }

                            var comentarioFechaTerminoHTML;
                                if (fecha_asignacion.trim() === '') { inputFechaTermino =   ``;
                                    comentarioFechaTerminoHTML = ''
                                } else {
                                    comentarioFechaTerminoHTML =
                                        `<label for="fecha_termino" class="form-label">Fecha Termino:</label>
                                        <input type="date" class="form-control mb-3" value="${fecha_asignacion.split('T')[0]}" disabled>`;
                            }
                            // COMENTARIO JEFE 
                            var comentarioAsignacionHTML;
                                if (comen_asignacion.trim() === '' || comen_asignacion === "No hay detalles disponibles.") {
                                    comentarioAsignacionHTML =
                                        ''; // No mostrar nada si el comentario está vacío o es "No hay detalles disponibles."
                                } else {
                                    comentarioAsignacionHTML =
                                        `<label for="comentario_resolucion" class="form-label">Comentario Jefe:</label>
                                        <textarea id="comentario_resolucion" rows="3" class="form-control" placeholder="" style="height: 100px;" readonly>${comen_asignacion}</textarea>`;
                                }

                            
                            // COMENTARIO FINAL
                            var comentarioFinalHTML = '';
                                if (comentario_final.trim() !== '' && comentario_final !== "No hay detalles disponibles.") {
                                    comentarioFinalHTML =
                                        `<div class="accordion" id="acordeonComentarioFinal">
                                            <!-- Comentario Final -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingFinal">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFinal" aria-expanded="false" aria-controls="collapseFinal">
                                                        Comentario Final
                                                    </button>
                                                </h2>
                                                <div id="collapseFinal" class="accordion-collapse collapse" aria-labelledby="headingFinal" data-bs-parent="#acordeonComentarioFinal">
                                                    <div class="accordion-body">
                                                        <textarea id="comentario_final" rows="3" class="form-control" style="height: 100px;" readonly>${comentario_final}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>`;
                                }
                            var comentarioResolucionHTML = '';
                                    if (comentarioAsignacionHTML.trim() !== '' && comentarioAsignacionHTML !== "No hay detalles disponibles.") {
                                        comentarioResolucionHTML =
                                        `<div class="accordion" id="acordeonComentarioJefe">
                                            <div class="accordion-item">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseResolucion" aria-expanded="false" aria-controls="collapseResolucion">
                                                        Comentario Jefe
                                                    </button>
                                                </h2>   
                                                <div id="collapseResolucion" class="accordion-collapse collapse" aria-labelledby="headingResolucion" data-bs-parent="#acordeonComentarioJefe">
                                                    <div class="accordion-body">
                                                        <textarea id="comentario_resolucion" rows="3" class="form-control" style="height: 100px;" readonly>${comentarioAsignacionHTML}</textarea>
                                                    </div>
                                                </div>
                                            </div>`;
                                    }


                            var inputAlertaEstadoHTML;
                                if (estado === 1) { // Estado RECIBIDO
                                    inputAlertaEstadoHTML =  `<div class="alert alert-danger" role="alert">
                                                                Aún no se ha asignado un técnico
                                                            </div>`;
                                } else {
                                    inputAlertaEstadoHTML = '';
                                }






                        
                        
                            var estadoAsignadoHTML;
                            if(estado === 2 || estado === 5 || estado === 3) {//Asignado - En Proceso
                                    estadoAsignadoHTML  = 
                                    ` <div class="accordion" id="accordionExample">
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingOne">
                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#acordeonEstados_1_2_3" aria-expanded="true" aria-controls="acordeonEstados_1_2_3">
                                                        Detalles de la Asignación Técnica
                                                    </button>
                                                </h2>
                                                <div id="acordeonEstados_1_2_3" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="alert alert-info" role="alert">
                                                            <form class="row g-3">              
                                                                <div class="col-md-6">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control" id="hora_click1" placeholder="Hora" value="${fecha_asigna_click}" disabled>
                                                                        <label for="hora_click1">Fecha Asignación Técnico</label>        
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control" id="hora_click2" placeholder="Hora" value="${hora_asigna_click}" disabled>
                                                                        <label for="hora_click2">Hora Asignación Técnico</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control" id="nombre_tecnico" placeholder="Técnico" value="${nombreApellidoTecnico}" disabled>
                                                                        <label for="nombre_tecnico">Técnico</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control" id="prioridad" placeholder="Prioridad" value="${prioridad}" readonly>
                                                                        <label for="prioridad">Prioridad</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-floating">
                                                                        <input type="date" class="form-control" id="fecha_termino" placeholder="Fecha" value="${fecha_asignacion}" disabled>
                                                                        <label for="fecha_termino">Fecha Resolución</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control" id="dias_resolucion" placeholder="Días Resolución" value="${dias_asignados} Días" disabled>
                                                                        <label for="dias_resolucion">Días resolución</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12">
                                                                    <div class="form-floating">
                                                                        <textarea class="form-control" placeholder="Comentario del Jefe" id="comentario_asignacion" style="height: 100px;" disabled>${comen_asignacion}</textarea>
                                                                        <label for="comentario_asignacion">Comentario del Jefe</label>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><br>`;
                                    } else {
                                        estadoAsignadoHTML = '';
                                    }


                            //   var estadoProcesoHTML;
                            // if (estado === 3) { //PROCESO
                            //     estadoProcesoHTML =  `   <div class="alert alert-warning" role="alert">
                            //             <form class="row g-3">              
                            //                 <div class="col-md-6">
                            //                     <div class="form-floating">
                            //                         <input type="text" class="form-control" id="hora_click" placeholder=""  value="${fecha_proceso}" disabled>  
                            //                          <label for="hora_click">Fecha Tecnico Comenzo ticket</label>        
                            //                     </div>
                            //                 </div>
                            //                 <div class="col-md-6">
                            //                     <div class="form-floating">
                            //                         <input type="text" class="form-control" id="hora_click" placeholder=""  value="${hora_proceso}" disabled>  
                            //                          <label for="hora_click">Hora Tecnico Comenzo ticket</label>        
                            //                     </div>
                            //                 </div>
                            //             </form>
                            //         </div>`;
                            //     } else {
                            //         estadoProcesoHTML = '';
                            // }


                            var estadoTerminadoHTML;
                            if (estado === 5) { //TERMINADO
                                estadoTerminadoHTML =
                                    `<div class="accordion" id="acordeonTerminado">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingTerminado">
                                                <!-- Asegúrate de que el botón del acordeón esté marcado como expandido inicialmente -->
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTerminado" aria-expanded="true" aria-controls="collapseTerminado">
                                                    Detalles del Término del Ticket
                                                </button>
                                            </h2>
                                            <!-- Asegúrate de que la clase del acordeón incluya 'show' para que se muestre expandido inicialmente -->
                                            <div id="collapseTerminado" class="accordion-collapse collapse show" aria-labelledby="headingTerminado" data-bs-parent="#acordeonTerminado">
                                                <div class="accordion-body">
                                                    <div class="alert alert-warning" role="alert">
                                                        <form class="row g-3">
                                                            <div class="col-md-6">
                                                                <div class="form-floating">
                                                                    <input type="date" class="form-control" id="fecha" placeholder="Fecha" value="${fecha_termino}" disabled>
                                                                    <label for="fecha">Fecha Término Ticket</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control" id="hora" placeholder="Hora" value="${hora_termino}" disabled>
                                                                    <label for="hora">Hora Término Ticket</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control" id="de1" placeholder="De" value="${dias_termino}" disabled>
                                                                    <label for="de1">Días demora</label>
                                                                </div>
                                                            </div>                                
                                                            <div class="col-md-6">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control" id="de2" placeholder="De" value="Dentro de la fecha estipulada" disabled>
                                                                    <label for="de2">Calificación Técnico</label>
                                                                </div>
                                                            </div>
                                                            <!-- <div class="col-12">
                                                                <div class="form-floating">
                                                                    <textarea class="form-control" placeholder="Address" id="detalles" style="height: 100px;" disabled>${comentario_final}</textarea>
                                                                    <label for="detalles">Comentario del Técnico</label>
                                                                </div>
                                                            </div> -->
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>`;
                            } else {
                                estadoTerminadoHTML = '';
                            }



                            Swal.fire({
                                title: `<div class="alert alert-dark" role="alert">TICKET A-${idt}</div>`, // Corregido para usar la variable correctamente
                                html: `
                                 <div class="alert alert-secondary" role="alert">
                                   
                                    <div class="container-fluid">${inputAlertaEstadoHTML}</div> <!--********COMENTARIO FINAL ***********-->

                                 
                                    <form class="row g-3">              
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="date" class="form-control" id="floatingEmail" placeholder="Fecha"  value="${fecha}" disabled>
                                                <label for="floatingEmail">Fecha Ticket</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingPassword" placeholder="hora" value="${hora}" disabled>
                                                <label for="floatingPassword">Hora</label>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingPassword" placeholder="De" value="${nombreUsuario + ' ' + apellidoPaterno}" disabled>
                                                <label for="floatingPassword">De</label>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingPassword" placeholder="De" value="${nombreEstado}" disabled>
                                                <label for="floatingPassword">Estado</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingName" placeholder="Asunto" value="${idAsunto}" disabled>
                                                <label for="floatingName">Asunto</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <textarea class="form-control" placeholder="Address" id="floatingTextarea" disabled style="height: 100px;">${detallesTicket}</textarea>
                                                <label for="floatingTextarea">Problema</label>
                                            </div>
                                        </div>
                                    </form>
                                </div>   

                                ${estadoAsignadoHTML}      <!-- estado 1 || 2 -->
                                ${accionesAcordeonHTML}    <!-- Incluir el acordeón aquí -->
                                ${estadoTerminadoHTML}     <!-- estado 5 TERMINADO -->

                         

                             

                                                              
                            `,
                                width: "870px",
                                padding: "40px",
                                showCancelButton: false,
                                confirmButtonText: 'CERRAR',
                                confirmButtonColor: '#5B8E4A',
                                customClass: {
                                    popup: 'cuerpo_modal_guardar',
                                    confirmButton: 'bt_activar_alumno'
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar los detalles del ticket:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Error al cargar los datos del ticket. Por favor, intente de nuevo.',
                            confirmButtonText: 'Cerrar'
                        });
                    }
                });
            }


            function verTicketAdministrativo(id) {
                $.ajax({
                    url: "modelos/rescatar/ticket_administracion.php",
                    type: "POST",
                    data: {
                        id: id
                    },
                    dataType: "json",
                    success: function(data) {
                        if (data) {
                            // INFORMACION ESTADO 1 (RECIBIDO) EL USUARIO DA ESTA INFORMACION
                            var fecha                   = data.fecha || '';
                            var nombreUsuario           = data.nombre || "No disponible";
                            var apellidoPaterno         = data.apellido_paterno || "No disponible";
                            var nombreTecnico           = data.nombreTecnico || "No disponible";
                            var apellidoTecnico           = data.apellidoTecnico || "No disponible";
                            var nombreApellidoTecnico =   nombreTecnico + ' ' + apellidoTecnico;

                            var estado                  = data.estado || "No disponible";
                            var idAsunto                = data.asunto || "No disponible";
                            var nombreEstado            = data.nombreEstado || "No disponible";
                            var detallesTicket          = data.ticket_texarea || "No hay detalles disponibles.";
                            var hora                    = data.hora || "No hay detalles disponibles.";
                            var idt                     = data.id || '';


                            // INFORMACION ESTADO 2 (ASIGNADO) MANUEL DA ESTA INFO
                            var prioridad               = data.prioridad || '';             // COMBO-BOX
                            var dias_asignados          = data.dias_asignados || '';     // CALCULO DE LA FECHA ACTUAL A LA FECHA DE ASIGANCION
                            var fecha_asignacion        = data.fecha_asignacion || '';  // FECHA QUE DIO MANUEL
                            var comen_asignacion        = data.comentario_asignacion || "";   // COMENTARIO QUE HACE MANUEL          
                            var fecha_asigna_click      = data.fecha_asignacion_click  ||"";                // FECHA Y HORA EN QUE MANUEL ASIGNA UN TECNICO          
                            if (fecha_asigna_click) {
                            var fechaPartes             = fecha_asigna_click.split('-'); // Dividir la fecha en partes [año, mes, día]
                            var fecha_asigna_click      = fechaPartes[2] + '-' + fechaPartes[1] + '-' + fechaPartes[0]; // Reordenar a [día-mes-año]
                            } else {
                                var fecha_asigna_click = ""; // Mantener vacío si no hay fecha
                            }                        
                            var hora_asigna_click       = data.hora_asignacion_click || "";   // HORAA EN QUE MANUEL ASIGNA UN TECNICO          
       
                          
                            // INFORMACION ESTADO 3 (PROCESO) EL TENCINO DA ESTA INFORMACION (CUANDO COMINEZA A TRABAJAR EN EL TICKET)
                            var dias_proceso           = data.dias_proceso || '';     // CUANTOS DIAS SE DEMORO EN CONTESTAR EL  TICKET
                            var fecha_proceso          = data.fecha_proceso || '';   // FECHA EN QUE COMENZO EL TICKET
                            if (fecha_proceso) {
                            var fechaPartes             = fecha_proceso.split('-'); // Dividir la fecha en partes [año, mes, día]
                            var fecha_proceso           = fechaPartes[2] + '-' + fechaPartes[1] + '-' + fechaPartes[0]; // Reordenar a [día-mes-año]
                            } else {
                                var fecha_proceso = ""; // Mantener vacío si no hay fecha
                            }             
                            var hora_proceso           = data.hora_proceso || '';   // HORA EN QUE COMENZO EL TICKET


                            // INFORMACION ESTADO 5 (TERMINADO) EL TENCINO DA ESTA INFORMACION
                            var fecha_termino           = data.fecha_termino || '';    // FECHA EN QUE TERMINO EL TICKET
                            var hora_termino            = data.hora_termino || '';      // HORA EN QUE TERMINO EL TICKET
                            var dias_termino            = data.dias_termino || '';      // DIAS QUE SE DEMORO EN TERMINAR EL TICKET
                            var comentario_final        = data.comentario_final || '';   // COMENTARIO AL FINALIZAR EL TICKET

                            var accion = data.accion || '';                                             //TABLA avance_tecnicos     
                            var accionesAcordeonHTML = construirAccionesAcordeonHTML(data.acciones, data.id,estado,fecha_proceso,hora_proceso);      





                            var datosAsignacionClickHTML;
                                if (fecha_asigna_click.trim() === '0000-00-00') {
                                    datosAsignacionClickHTML = '';
                                } else {
                                    datosAsignacionClickHTML =
                                    `<div class="alert alert-danger" role="alert">
                                        <div class="row">
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label for="fecha_click" class="form-label">Fecha Manuel hizo click:</label>
                                                        <input type="text" id="fecha_click" class="form-control mb-3" value="${fecha_asigna_click}" readonly>                                                   
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group">
                                                    <label for="nombre_tecnico" class="form-label">Hora Manuel hizo click:</label>
                                                        <input type="text" id="nombre_tecnico" class="form-control mb-3" value="${hora_asigna_click}" readonly>                                       
                                                    </div>
                                                </div>
                                        </div>
                                    </div>`;
                                    }

                            var comentarioFechaTerminoHTML;
                                if (fecha_asignacion.trim() === '') { inputFechaTermino =   ``;
                                    comentarioFechaTerminoHTML = ''
                                } else {
                                    comentarioFechaTerminoHTML =
                                        `<label for="fecha_termino" class="form-label">Fecha Termino:</label>
                                        <input type="date" class="form-control mb-3" value="${fecha_asignacion.split('T')[0]}" disabled>`;
                            }
                            // COMENTARIO JEFE 
                            var comentarioAsignacionHTML;
                                if (comen_asignacion.trim() === '' || comen_asignacion === "No hay detalles disponibles.") {
                                    comentarioAsignacionHTML =
                                        ''; // No mostrar nada si el comentario está vacío o es "No hay detalles disponibles."
                                } else {
                                    comentarioAsignacionHTML =
                                        `<label for="comentario_resolucion" class="form-label">Comentario Jefe:</label>
                                        <textarea id="comentario_resolucion" rows="3" class="form-control" placeholder="" style="height: 100px;" readonly>${comen_asignacion}</textarea>`;
                                }

                            
                            // COMENTARIO FINAL
                            var comentarioFinalHTML = '';
                                if (comentario_final.trim() !== '' && comentario_final !== "No hay detalles disponibles.") {
                                    comentarioFinalHTML =
                                        `<div class="accordion" id="acordeonComentarioFinal">
                                            <!-- Comentario Final -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingFinal">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFinal" aria-expanded="false" aria-controls="collapseFinal">
                                                        Comentario Final
                                                    </button>
                                                </h2>
                                                <div id="collapseFinal" class="accordion-collapse collapse" aria-labelledby="headingFinal" data-bs-parent="#acordeonComentarioFinal">
                                                    <div class="accordion-body">
                                                        <textarea id="comentario_final" rows="3" class="form-control" style="height: 100px;" readonly>${comentario_final}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>`;
                                }
                            var comentarioResolucionHTML = '';
                                    if (comentarioAsignacionHTML.trim() !== '' && comentarioAsignacionHTML !== "No hay detalles disponibles.") {
                                        comentarioResolucionHTML =
                                        `<div class="accordion" id="acordeonComentarioJefe">
                                            <div class="accordion-item">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseResolucion" aria-expanded="false" aria-controls="collapseResolucion">
                                                        Comentario Jefe
                                                    </button>
                                                </h2>   
                                                <div id="collapseResolucion" class="accordion-collapse collapse" aria-labelledby="headingResolucion" data-bs-parent="#acordeonComentarioJefe">
                                                    <div class="accordion-body">
                                                        <textarea id="comentario_resolucion" rows="3" class="form-control" style="height: 100px;" readonly>${comentarioAsignacionHTML}</textarea>
                                                    </div>
                                                </div>
                                            </div>`;
                                    }


                            var inputAlertaEstadoHTML;
                                if (estado === 1) { // Estado RECIBIDO
                                    inputAlertaEstadoHTML =  `<div class="alert alert-danger" role="alert">
                                                                Aún no se ha asignado un técnico
                                                            </div>`;
                                } else {
                                    inputAlertaEstadoHTML = '';
                                }






                        
                        
                            var estadoAsignadoHTML;
                            if(estado === 2 || estado === 5 || estado === 3) {//Asignado - En Proceso
                                    estadoAsignadoHTML  = 
                                    ` <div class="accordion" id="accordionExample">
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingOne">
                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#acordeonEstados_1_2_3" aria-expanded="true" aria-controls="acordeonEstados_1_2_3">
                                                        Detalles de la Asignación Técnica
                                                    </button>
                                                </h2>
                                                <div id="acordeonEstados_1_2_3" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="alert alert-info" role="alert">
                                                            <form class="row g-3">              
                                                                <div class="col-md-6">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control" id="hora_click1" placeholder="Hora" value="${fecha_asigna_click}" disabled>
                                                                        <label for="hora_click1">Fecha Asignación Técnico</label>        
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control" id="hora_click2" placeholder="Hora" value="${hora_asigna_click}" disabled>
                                                                        <label for="hora_click2">Hora Asignación Técnico</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control" id="nombre_tecnico" placeholder="Técnico" value="${nombreApellidoTecnico}" disabled>
                                                                        <label for="nombre_tecnico">Técnico</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control" id="prioridad" placeholder="Prioridad" value="${prioridad}" readonly>
                                                                        <label for="prioridad">Prioridad</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-floating">
                                                                        <input type="date" class="form-control" id="fecha_termino" placeholder="Fecha" value="${fecha_asignacion}" disabled>
                                                                        <label for="fecha_termino">Fecha Resolución</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control" id="dias_resolucion" placeholder="Días Resolución" value="${dias_asignados} Días" disabled>
                                                                        <label for="dias_resolucion">Días resolución</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12">
                                                                    <div class="form-floating">
                                                                        <textarea class="form-control" placeholder="Comentario del Jefe" id="comentario_asignacion" style="height: 100px;" disabled>${comen_asignacion}</textarea>
                                                                        <label for="comentario_asignacion">Comentario del Jefe</label>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><br>`;
                                    } else {
                                        estadoAsignadoHTML = '';
                                    }


                            //   var estadoProcesoHTML;
                            // if (estado === 3) { //PROCESO
                            //     estadoProcesoHTML =  `   <div class="alert alert-warning" role="alert">
                            //             <form class="row g-3">              
                            //                 <div class="col-md-6">
                            //                     <div class="form-floating">
                            //                         <input type="text" class="form-control" id="hora_click" placeholder=""  value="${fecha_proceso}" disabled>  
                            //                          <label for="hora_click">Fecha Tecnico Comenzo ticket</label>        
                            //                     </div>
                            //                 </div>
                            //                 <div class="col-md-6">
                            //                     <div class="form-floating">
                            //                         <input type="text" class="form-control" id="hora_click" placeholder=""  value="${hora_proceso}" disabled>  
                            //                          <label for="hora_click">Hora Tecnico Comenzo ticket</label>        
                            //                     </div>
                            //                 </div>
                            //             </form>
                            //         </div>`;
                            //     } else {
                            //         estadoProcesoHTML = '';
                            // }


                            var estadoTerminadoHTML;
                            if (estado === 5) { //TERMINADO
                                estadoTerminadoHTML =
                                    `<div class="accordion" id="acordeonTerminado">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingTerminado">
                                                <!-- Asegúrate de que el botón del acordeón esté marcado como expandido inicialmente -->
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTerminado" aria-expanded="true" aria-controls="collapseTerminado">
                                                    Detalles del Término del Ticket
                                                </button>
                                            </h2>
                                            <!-- Asegúrate de que la clase del acordeón incluya 'show' para que se muestre expandido inicialmente -->
                                            <div id="collapseTerminado" class="accordion-collapse collapse show" aria-labelledby="headingTerminado" data-bs-parent="#acordeonTerminado">
                                                <div class="accordion-body">
                                                    <div class="alert alert-warning" role="alert">
                                                        <form class="row g-3">
                                                            <div class="col-md-6">
                                                                <div class="form-floating">
                                                                    <input type="date" class="form-control" id="fecha" placeholder="Fecha" value="${fecha_termino}" disabled>
                                                                    <label for="fecha">Fecha Término Ticket</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control" id="hora" placeholder="Hora" value="${hora_termino}" disabled>
                                                                    <label for="hora">Hora Término Ticket</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control" id="de1" placeholder="De" value="${dias_termino}" disabled>
                                                                    <label for="de1">Días demora</label>
                                                                </div>
                                                            </div>                                
                                                            <div class="col-md-6">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control" id="de2" placeholder="De" value="Dentro de la fecha estipulada" disabled>
                                                                    <label for="de2">Calificación Técnico</label>
                                                                </div>
                                                            </div>
                                                            <!-- <div class="col-12">
                                                                <div class="form-floating">
                                                                    <textarea class="form-control" placeholder="Address" id="detalles" style="height: 100px;" disabled>${comentario_final}</textarea>
                                                                    <label for="detalles">Comentario del Técnico</label>
                                                                </div>
                                                            </div> -->
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>`;
                            } else {
                                estadoTerminadoHTML = '';
                            }



                            Swal.fire({
                                title: `<div class="alert alert-dark" role="alert">TICKET A-${idt}</div>`, // Corregido para usar la variable correctamente
                                html: `
                                 <div class="alert alert-secondary" role="alert">
                                   
                                    <div class="container-fluid">${inputAlertaEstadoHTML}</div> <!--********COMENTARIO FINAL ***********-->

                                 
                                    <form class="row g-3">              
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="date" class="form-control" id="floatingEmail" placeholder="Fecha"  value="${fecha}" disabled>
                                                <label for="floatingEmail">Fecha Ticket</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingPassword" placeholder="hora" value="${hora}" disabled>
                                                <label for="floatingPassword">Hora</label>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingPassword" placeholder="De" value="${nombreUsuario + ' ' + apellidoPaterno}" disabled>
                                                <label for="floatingPassword">De</label>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingPassword" placeholder="De" value="${nombreEstado}" disabled>
                                                <label for="floatingPassword">Estado</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="floatingName" placeholder="Asunto" value="${idAsunto}" disabled>
                                                <label for="floatingName">Asunto</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <textarea class="form-control" placeholder="Address" id="floatingTextarea" disabled style="height: 100px;">${detallesTicket}</textarea>
                                                <label for="floatingTextarea">Problema</label>
                                            </div>
                                        </div>
                                    </form>
                                </div>   

                                ${estadoAsignadoHTML}      <!-- estado 1 || 2 -->
                                ${accionesAcordeonHTML}    <!-- Incluir el acordeón aquí -->
                                ${estadoTerminadoHTML}     <!-- estado 5 TERMINADO -->

                         

                             

                                                              
                            `,
                                width: "870px",
                                padding: "40px",
                                showCancelButton: false,
                                confirmButtonText: 'CERRAR',
                                confirmButtonColor: '#5B8E4A',
                                customClass: {
                                    popup: 'cuerpo_modal_guardar',
                                    confirmButton: 'bt_activar_alumno'
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar los detalles del ticket:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Error al cargar los datos del ticket. Por favor, intente de nuevo.',
                            confirmButtonText: 'Cerrar'
                        });
                    }
                });
            }





            function mostrarArchivo(urlArchivo) {
                // Determinar el tipo de archivo basado en la extensión
                const fileType = urlArchivo.split('.').pop().toLowerCase();
                let contentHtml = '';  // Contenido HTML basado en el tipo de archivo

                switch (fileType) {
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                        contentHtml = '<img src="' + urlArchivo + '" class="img-fluid" alt="Imagen Adjunta">';
                        break;
                    case 'pdf':
                        contentHtml = '<embed src="' + urlArchivo + '" type="application/pdf" width="100%" height="500px"/>';
                        break;
                    case 'mp4':
                        contentHtml = '<video controls width="100%"><source src="' + urlArchivo + '" type="video/mp4">Your browser does not support the video tag.</video>';
                        break;
                    case 'csv':
                        contentHtml = '<div>CSV file detected. <a href="' + urlArchivo + '" target="_blank">Download CSV</a></div>';
                        break;
                    default:
                        contentHtml = '<p>Archivo no soportado para visualización.</p>';
                        break;
                }

                // Usar SweetAlert para mostrar el modal
                Swal.fire({
                    title: '<div class="alert alert-dark" role="alert">Visualizacion de Archivo</div>',
                    html: contentHtml,
                    width: "870px",
                    padding: "40px",
                    showCancelButton: false,
                    confirmButtonText: 'CERRAR',
                    confirmButtonColor: '#5B8E4A',
                        customClass: {
                            popup: 'cuerpo_modal_guardar',
                            confirmButton: 'bt_activar_alumno'
                        }
                    });
            }
            </script>


            </script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
            <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</body>

</html>
