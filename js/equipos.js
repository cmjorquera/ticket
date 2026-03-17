function crearEquipo() {
    
    alert("******");
    // Obtener el formulario de características del equipo
    const formulario = document.getElementById('formAgregarEquipo');
    const formularioCompras = document.getElementById('pestanaObservacion'); // Formulario de la pestaña observaciones

    // Obtener todos los inputs del formulario de características y observaciones
    const inputs = formulario.querySelectorAll('input, select');
    const inputsCompras = formularioCompras.querySelectorAll('input, textarea'); // Incluir los de observaciones

    // Crear un objeto para almacenar los datos
    let equipoData = {};
    let camposVacios = [];
    let nombreEquipoVacio = false; // Variable para controlar si el nombre del equipo está vacío

    // Recorrer los inputs y almacenarlos en equipoData
    inputs.forEach(input => {
        equipoData[input.id] = input.value.trim();
        if (!input.value.trim()) {
            camposVacios.push(input); // Si el campo está vacío, añadirlo a la lista
            input.classList.add('is-invalid'); // Añadir clase para borde rojo

            // Agregar evento para quitar la clase is-invalid cuando el usuario empiece a escribir
            input.addEventListener('input', function () {
                input.classList.remove('is-invalid');
            });

            if (input.id === 'nombre_equipo') {
                nombreEquipoVacio = true; // Marcar si el campo "Nombre del Equipo" está vacío
            }
        } else {
            input.classList.remove('is-invalid'); // Quitar borde rojo si el campo no está vacío
        }
    });

    // Recorrer los inputs de la pestaña de observaciones
    inputsCompras.forEach(input => {
        equipoData[input.id] = input.value.trim();
        if (!input.value.trim()) {
            input.classList.add('is-invalid');

            // Agregar evento para quitar la clase is-invalid cuando el usuario empiece a escribir
            input.addEventListener('input', function () {
                input.classList.remove('is-invalid');
            });
        } else {
            input.classList.remove('is-invalid');
        }
    });

    // Verificar si el campo "Nombre del Equipo" está vacío
    if (nombreEquipoVacio) {
        Swal.fire({
            title: '<div class="alert alert-dark" role="alert">ERROR</div>',
            text: 'El campo "Nombre del Equipo" es obligatorio y no puede estar vacío.',
            // icon: 'error',
            confirmButtonText: 'OK',
            customClass: {
                popup: 'cuerpo_modal_eliminar',
                confirmButton: 'bt_eliminar'
            }
        });
        return; // Salir de la función si "Nombre del Equipo" está vacío
    }

    // Si hay otros campos vacíos, mostrar el SweetAlert con la opción de "Guardar de todas formas"
    if (camposVacios.length > 0) {
        Swal.fire({
            title: '¿Desea guardare la información de este Computador?',
            text: 'Algunos campos están vacíos, ¿desea continuar?',
            // icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Guardar de todas formas',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'cuerpo_modal_guardar',
                confirmButton: 'bt_crear',
                cancelButton: 'bt_cerrar',
            },
        }).then((result) => {
            if (result.isConfirmed) {
                enviarDatosEquipo(equipoData); // Llamada a la función que envía los datos
            }
        });
    } else {
        // No hay campos vacíos, guardar directamente
        enviarDatosEquipo(equipoData);
    }
}


function enviarDatosEquipo(equipoData) {
    // Enviar la solicitud AJAX al servidor
    fetch('modelos/guardar/equipo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(equipoData)
    })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: data.message, // Mostrar el mensaje que venga desde el servidor
                    timer: 2000, // Tiempo en milisegundos (3 segundos)
                    timerProgressBar: true, // Mostrar la barra de progreso del tiempo
                    showConfirmButton: false, // No mostrar el botón de confirmación
                    customClass: {
                        popup: 'cuerpo_modal_guardar',

                    },
                    didOpen: () => {
                        Swal.showLoading(); // Mostrar el icono de carga mientras dura el tiempo
                    }
                }).then(() => {
                    window.location.href = 'bitacora.php';
                });

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.error || 'Hubo un problema al guardar los datos.',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'cuerpo_modal_guardar',
                        confirmButton: 'bt_activar_alumno',
                        cancelButton: 'bt_activar_alumno'
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error al guardar el equipo:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema al guardar el equipo.',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'cuerpo_modal_guardar',
                    confirmButton: 'bt_activar_alumno'
                }
            });
        });
}










function leerArchivoTXT(archivo) {
    if (archivo) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var contenido           = e.target.result;
            var nombreArchivo       = archivo.name.split(/[_-]NTBK\.txt/)[0];
            document.getElementById('nombre_equipo').value = nombreArchivo;
            // Llamar a verificarNombreEquipo() después de cargar el nombre para mostrar la pestaña Observaciones si es necesario
            // verificarNombreEquipo();

            // *** Información del sistema ***
            if (contenido.includes('DMI System Information')) {
                var sistema         = contenido.split('DMI System Information')[1];
                var fabricante      = sistema.match(/manufacturer\s+(.+)/i);
                var producto        = sistema.match(/product\s+(.+)/i);
                var serial          = sistema.match(/serial\s+(.+)/i);

                if (fabricante) {
                    document.getElementById('fabricante').value = fabricante[1].trim();
                }
                if (producto) {
                    document.getElementById('producto').value = producto[1].trim();
                }
                if (serial) {
                    document.getElementById('numero_serie').value = serial[1].trim();
                }
            }

            // *** Información del sistema - Tipo de PC (Chasis) ***
            if (contenido.includes('DMI System Enclosure')) {
                var chasis = contenido.split('DMI System Enclosure')[1];
                var tipoPc = chasis.match(/chassis type\s+(.+)/i);

                if (tipoPc) {
                    document.getElementById('tipo_pc').value = tipoPc[1].trim();
                }
            }

            // ***** Información de la memoria ******
            if (archivo) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const contenido = e.target.result;
                    
                    // Llamar a la función procesarMemoria
                    procesarMemoria(contenido);
                };
                reader.readAsText(archivo);
            }





            // *** Información del procesador ***
            if (contenido.includes('DMI Processor')) {
                var procesador = contenido.split('DMI Processor')[1];
                var fabricanteProcesador = procesador.match(/manufacturer\s+(.+)/i);
                var modeloProcesador = procesador.match(/model\s+(.+)/i);
                var frecuencia_procesador = procesador.match(/clock speed\s+(.+)/i);


                if (fabricanteProcesador) {
                    document.getElementById('fabricante_procesador').value = fabricanteProcesador[1].trim();
                }
                if (modeloProcesador) {
                    document.getElementById('modelo_procesador').value = modeloProcesador[1].trim();
                }
                if (frecuencia_procesador) {
                    document.getElementById('frecuencia_procesador').value = frecuencia_procesador[1].replace('MHz', '').trim();
                }
            }

            // *** Información del almacenamiento ***
            if (contenido.includes('Drive')) {
                var almacenamiento = contenido.split('Drive')[1];
                var nombreAlmacenamiento = almacenamiento.match(/Name\s+(.+)/i);
                var capacidadAlmacenamiento = almacenamiento.match(/Capacity\s+(.+)/i);
                var tipoAlmacenamiento = almacenamiento.match(/Type\s+(.+)/i);

                if (nombreAlmacenamiento) {
                    document.getElementById('nombre_almacenamiento').value = nombreAlmacenamiento[1].trim();
                }
                if (capacidadAlmacenamiento) {
                    document.getElementById('capacidad_almacenamiento').value = capacidadAlmacenamiento[1].replace('GB', '').trim();
                }
                if (tipoAlmacenamiento) {
                    document.getElementById('tipo_almacenamiento').value = tipoAlmacenamiento[1].trim();
                }
            }

            if (contenido.includes('Monitor 0')) {
                const monitor0 = contenido.split('Monitor 0')[1];

                // Extraer valores específicos para Monitor 0
                const modeloMonitor0 = monitor0.match(/Model\s+(.*)/i);
                const idMonitor0 = monitor0.match(/ID\s+(.+)/i);
                const numeroSerieMonitor0 = monitor0.match(/Serial\s+(.+)/i);
                const tamanoMonitor0 = monitor0.match(/Size\s+(.+?)\s+inches/i);
                const resolucion0 = monitor0.match(/Max Resolution\s+([\d\s]+x[\d\s]+)/i); // Captura solo "1920 x 1080" antes de "@"

                // Asignar valores al formulario correspondiente a Monitor 1 (HTML id monitor_1_)
                document.getElementById('monitor_1_modelo').value = (modeloMonitor0 && modeloMonitor0[1].trim() !== '') ? modeloMonitor0[1].trim() : '';
                if (idMonitor0) {
                    document.getElementById('monitor_1_codigo').value = idMonitor0[1].trim();
                }
                if (numeroSerieMonitor0 && numeroSerieMonitor0[1].trim() !== '' && !numeroSerieMonitor0[1].trim().startsWith('Manufacturing Date')) {
                    document.getElementById('monitor_1_numero_serie').value = numeroSerieMonitor0[1].trim();
                } else {
                    document.getElementById('monitor_1_numero_serie').value = ''; // Asignar vacío si el número de serie no es válido
                }

                if (tamanoMonitor0) {
                    document.getElementById('monitor_1_tamano').value = tamanoMonitor0[1].trim();
                }
                if (resolucion0) {
                    document.getElementById('monitor_1_resolucion').value = resolucion0[1].trim(); // Asignar solo la resolución
                }
            }

            // Procesar la información de Monitor 1 (similar para Monitor 2 en HTML)
            if (contenido.includes('Monitor 1')) {
                const monitor1 = contenido.split('Monitor 1')[1];

                // Extraer valores específicos para Monitor 1
                const modeloMonitor1 = monitor1.match(/Model\s+(.*)/i);
                const idMonitor1 = monitor1.match(/ID\s+(.+)/i);
                const numeroSerieMonitor1 = monitor1.match(/Serial\s+(.+)/i);
                const tamanoMonitor1 = monitor1.match(/Size\s+(.+?)\s+inches/i);
                const resolucion1 = monitor1.match(/Max Resolution\s+([\d\s]+x[\d\s]+)/i); // Captura solo "1920 x 1080" antes de "@"

                // Asignar valores al formulario correspondiente a Monitor 2 (HTML id monitor_2_)
                document.getElementById('monitor_2_modelo').value = (modeloMonitor1 && modeloMonitor1[1].trim() !== '') ? modeloMonitor1[1].trim() : '';
                if (idMonitor1) {
                    document.getElementById('monitor_2_codigo').value = idMonitor1[1].trim();
                }
                if (numeroSerieMonitor1 && numeroSerieMonitor1[1].trim() !== '' && !numeroSerieMonitor1[1].trim().startsWith('Manufacturing Date')) {
                    document.getElementById('monitor_2_numero_serie').value = numeroSerieMonitor1[1].trim();
                } else {
                    document.getElementById('monitor_2_numero_serie').value = ''; // Asignar vacío si el número de serie no es válido
                }





                if (tamanoMonitor1) {
                    document.getElementById('monitor_2_tamano').value = tamanoMonitor1[1].trim();
                }
                if (resolucion1) {
                    document.getElementById('monitor_2_resolucion').value = resolucion1[1].trim(); // Asignar solo la resolución
                }
            }


            if (contenido.includes('Software')) {
                var sistema = contenido.split('Software')[1];
                var versionWindows = sistema.match(/Windows Version\s+(.+)/i);

                if (versionWindows) {
                    document.getElementById('windows').value = versionWindows[1].trim();
                }
            }



        };

        reader.readAsText(archivo);
    }
}


function procesarMemoria(contenido) {
    // Dividir el contenido por bloques de "DMI Memory Device"
    const dispositivosMemoria = contenido.split("DMI Memory Device").slice(1); // Ignorar el primer split que no es un dispositivo
    const memoryDevices = [];

    // Referencias a pestañas
    const tabList = document.getElementById('memoryTabList');
    const tabContent = document.getElementById('memoryTabContent');

    // Limpiar las pestañas previas
    tabList.innerHTML = '';
    tabContent.innerHTML = '';

    // Palabras clave que indican el final de un bloque de memoria
    const endMarkers = ["DMI Processor", "Storage", "DMI Port Connector"];

    // Procesar cada dispositivo de memoria
    dispositivosMemoria.forEach((memoria, index) => {
        // Asegurarnos de procesar solo hasta encontrar un marcador de fin
        const endIndex = endMarkers.reduce((minIndex, marker) => {
            const markerIndex = memoria.indexOf(marker);
            return markerIndex !== -1 ? Math.min(minIndex, markerIndex) : minIndex;
        }, memoria.length);

        const memoryData = memoria.substring(0, endIndex);

        // Extraer valores
        const designationMatch = memoryData.match(/designation\s+(.+)/i);
        const formatMatch = memoryData.match(/format\s+(.+)/i);
        const typeMatch = memoryData.match(/type\s+(.+)/i);
        const sizeMatch = memoryData.match(/size\s+(.+)/i);
        const speedMatch = memoryData.match(/speed\s+(.+)/i);
        const manufacturerMatch = memoryData.match(/manufacturer\s+(.+)/i);
        const manufacturerIdMatch = memoryData.match(/manufacturer id\s+(.+)/i);

        // Validar valores y asignar vacíos si no se encuentran o son inválidos
        const cleanValue = (match) => {
            const value = match ? match[1].trim() : '';
            return value === 'unknown' || value === '0x0' ? '' : value;
        };

        const dispositivo = {
            designation: cleanValue(designationMatch),
            format: cleanValue(formatMatch),
            type: cleanValue(typeMatch),
            size: cleanValue(sizeMatch),
            speed: cleanValue(speedMatch),
            manufacturer: manufacturerIdMatch && manufacturerIdMatch[1].trim() === '0x0'
                ? '' // Si es "manufacturer id 0x0", dejar vacío
                : cleanValue(manufacturerMatch), // Tomar el valor de "manufacturer" si es válido
        };

        // Solo crear pestaña si hay valores válidos
        if (Object.values(dispositivo).some((value) => value !== '')) {
            memoryDevices.push(dispositivo);

            // Crear la pestaña
            const prefijo = index + 1;
            const tabItem = document.createElement('li');
            tabItem.className = 'nav-item';
            tabItem.innerHTML = `
                <button class="nav-link ${index === 0 ? 'active' : ''}" id="memory${prefijo}-tab" data-bs-toggle="tab" data-bs-target="#memory${prefijo}" type="button" role="tab" aria-controls="memory${prefijo}" aria-selected="${index === 0}">
                    Memoria ${prefijo}
                </button>`;
            tabList.appendChild(tabItem);

            // Crear contenido para la pestaña
            const tabPane = document.createElement('div');
            tabPane.className = `tab-pane fade ${index === 0 ? 'show active' : ''}`;
            tabPane.id = `memory${prefijo}`;
            tabPane.setAttribute('role', 'tabpanel');
            tabPane.setAttribute('aria-labelledby', `memory${prefijo}-tab`);

            tabPane.innerHTML = `
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="designacion_memoria${prefijo}" class="form-label">Designación Memoria</label>
                        <input type="text" class="form-control" id="designacion_memoria${prefijo}" value="${dispositivo.designation || ''}">
                    </div>
                    <div class="col-md-4">
                        <label for="formato_memoria${prefijo}" class="form-label">Formato Memoria</label>
                        <input type="text" class="form-control" id="formato_memoria${prefijo}" value="${dispositivo.format || ''}">
                    </div>
                    <div class="col-md-4">
                        <label for="tipo_memoria${prefijo}" class="form-label">Tipo Memoria</label>
                        <input type="text" class="form-control" id="tipo_memoria${prefijo}" value="${dispositivo.type || ''}">
                    </div>
                    <div class="col-md-4">
                        <label for="tamano_memoria${prefijo}" class="form-label">Tamaño Memoria (GB)</label>
                        <input type="text" class="form-control" id="tamano_memoria${prefijo}" value="${dispositivo.size || ''}">
                    </div>
                    <div class="col-md-4">
                        <label for="frecuencia_memoria${prefijo}" class="form-label">Frecuencia Memoria (MHz)</label>
                        <input type="text" class="form-control" id="frecuencia_memoria${prefijo}" value="${dispositivo.speed || ''}">
                    </div>
                    <div class="col-md-4">
                        <label for="marca_memoria${prefijo}" class="form-label">Marca De Memoria</label>
                        <input type="text" class="form-control" id="marca_memoria${prefijo}" value="${dispositivo.manufacturer || ''}">
                    </div>
                </div>`;
            tabContent.appendChild(tabPane);
        }
    });

    console.log(`Se procesaron ${memoryDevices.length} dispositivos de memoria.`);
}

function verQrEquipo(idEquipo) {
    if (!idEquipo) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'ID de equipo no válido.',
        });
        return;
    }

    fetch(`modelos/rescatar/obtener_equipo.php?id=${idEquipo}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
        .then((response) => response.json())
        .then((result) => {
            if (result.success) {
                const equipo = result.equipo;
                let qrCodePath = equipo.qr_code;

                // Eliminar "../../" de la ruta si existe
                if (qrCodePath.startsWith('../../')) {
                    qrCodePath = qrCodePath.replace('../../', '');
                }

                if (!qrCodePath) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sin código QR',
                        text: 'Este equipo no tiene un código QR asignado.',
                    });
                    return;
                }

                const idCompleto = idEquipo.toString().padStart(5, '0');

                Swal.fire({
                    title: `<div class="alert alert-dark" role="alert">Computador ${idCompleto}</div>`,
                    html: `
                        <div>
                            <img src="${qrCodePath}" alt="Código QR" style="max-width: 100%; height: auto; margin-bottom: 20px;">
                        </div>
                    `,
                    // showCancelButton: true,
                    confirmButtonText: 'Imprimir',
                    cancelButtonText: 'Cerrar',
                    customClass: {
                        popup: 'cuerpo_modal_guardar',
                        confirmButton: 'bt_crear',
                        cancelButton: 'bt_cerrar',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Acción del botón "Imprimir"
                        window.open(
                            `modelos/imprimir/computadores.php?id_computador=${idEquipo}`,
                            '_blank'
                        );
                    }
                    // No es necesario manejar el botón "Cerrar", ya que SweetAlert2 lo cierra automáticamente
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.error || 'No se encontró el equipo.',
                });
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al procesar la solicitud.',
            });
        });
}


//*********** ADJUNTAR TXT  ********************************************************** */
// ***********************************************************************************


//FUNCIOND EL BUTTON EDITAR QUE SE OCULTA AL APRETAR EL BUTTON DEL despalzamiento del acordeon
function activarEdicionEquipos() {
    
    const botonEditar   = document.getElementById("editarEquipo");
    const idEquipo      = botonEditar.getAttribute('data-id');
    const form1         = document.getElementById(`formEditarEquipo_${idEquipo}`);
    const form2         = document.getElementById(`formObservaciones_${idEquipo}`);

    // Crear un objeto para almacenar los datos
    let equipoData = {
        id_equipo: idEquipo
    };
    let camposVacios = [];
    let nombreEquipoVacio = false; // Variable para controlar si el nombre del equipo está vacío

    // Validación de campos de form1
    Array.from(form1.elements).forEach(input => {
        if (input.name) {
            equipoData[input.name] = input.value.trim();
            if (!input.value.trim()) {
                camposVacios.push(input);
                input.classList.add('is-invalid');
                input.addEventListener('input', function () {
                    input.classList.remove('is-invalid');
                });
                if (input.name === 'nombre_equipo') {
                    nombreEquipoVacio = true;
                }
            } else {
                input.classList.remove('is-invalid');
            }
        }
    });

    // Validación de campos de form2
    Array.from(form2.elements).forEach(input => {
        if (input.name) {
            equipoData[input.name] = input.value.trim();
            if (!input.value.trim()) {
                camposVacios.push(input);
                input.classList.add('is-invalid');
                input.addEventListener('input', function () {
                    input.classList.remove('is-invalid');
                });
            } else {
                input.classList.remove('is-invalid');
            }
        }
    });

    // Verificar si "Nombre del Equipo" está vacío y mostrar alerta
    if (nombreEquipoVacio) {
        Swal.fire({
            title: '<div class="alert alert-dark" role="alert">ERROR</div>',
            text: 'El campo "Nombre del Equipo" es obligatorio y no puede estar vacío.',
            confirmButtonText: 'OK',
            customClass: {
                popup: 'cuerpo_modal_eliminar',
                confirmButton: 'bt_eliminar'
            }
        });
        return; // Salir de la función si "Nombre del Equipo" está vacío
    }

    // Si hay otros campos vacíos, permitir guardar de todas formas
    if (camposVacios.length > 0) {
        Swal.fire({
            title: '¿Desea guardar la información de este Computador?',
            text: 'Algunos campos están vacíos, ¿desea continuar?',
            showCancelButton: true,
            confirmButtonText: 'Guardar de todas formas',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'cuerpo_modal_guardar',
                confirmButton: 'bt_crear',
                cancelButton: 'bt_cerrar',
            },
        }).then((result) => {
            if (result.isConfirmed) {
                guardarCambiosEquipo(equipoData); // Enviar los datos si se confirma
            }
        });
    } else {
        // No hay campos vacíos, guardar directamente
        guardarCambiosEquipo(equipoData);
    }
}



function activaAcordeonComputador(idEquipo) {
    const acordeonRow       = document.getElementById(`acordeonRow_${idEquipo}`);
    const botonEditar       = document.getElementById("editarEquipo");
    const filaEquipo        = document.getElementById(`equipo_${idEquipo}`);

    // Verificar si el acordeón ya está visible; si es así, ocultarlo
    if (acordeonRow.style.display === 'table-row') {
        acordeonRow.style.display = 'none';
        botonEditar.style.display = 'none';
        filaEquipo.classList.remove('efecto-3d');
        return;
    }

    // Ocultar otros acordeones abiertos
    document.querySelectorAll("tr[id^='acordeonRow_']").forEach(row => row.style.display = 'none');
    document.querySelectorAll("tr[id^='equipo_']").forEach(row => row.classList.remove('efecto-3d'));

    // Mostrar el acordeón actual
    acordeonRow.style.display = 'table-row';
    botonEditar.style.display = 'inline-block';
    botonEditar.setAttribute('data-id', idEquipo);
    filaEquipo.classList.add('efecto-3d');

    const acordeonContainer = document.getElementById(`acordeonEdicionEquipo_${idEquipo}`);
    acordeonContainer.innerHTML = '<p>Cargando...</p>';



    // Realizar la solicitud AJAX para obtener los datos del equipo
    fetch(`modelos/rescatar/obtener_equipo.php?id=${idEquipo}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const equipo = data.equipo;
                // console.log(equipo);
                usuarioEquipo = equipo.id_usuario;
                // console.log(usuarioEquipo);
                // Generar el contenido del acordeón con los datos del equipo
                acordeonContainer.innerHTML = `
                          <ul class="nav nav-tabs" id="editTab_${idEquipo}" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#caracteristicas_${idEquipo}" role="tab" aria-selected="true">Características del Computador</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#observaciones_${idEquipo}" role="tab" aria-selected="false">Observaciones</a>
                                </li>
                            </ul>
                                <div class="tab-content mt-3">
                                    <!-- Características del equipo -->
                                    <div class="tab-pane fade show active" id="caracteristicas_${idEquipo}" role="tabpanel">
                                        <form id="formEditarEquipo_${idEquipo}">
                                            <div class="container">
                                                <div class="row mb-3">
                                                    <div class="col-md-4">
                                                        <label for="nombre_equipo_${idEquipo}" class="form-label">Nombre del Equipo</label>
                                                        <input type="text" class="form-control" id="nombre_equipo_${idEquipo}" name="nombre_equipo" value="${equipo.nombre_equipo || ''}" ">
                                                    </div>
                                    
                                                    <div class="col-md-4">
                                                        <label for="fabricante_${idEquipo}" class="form-label">Marca</label>
                                                        <input type="text" class="form-control" id="fabricante_${idEquipo}" name="fabricante" value="${equipo.fabricante || ''}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="producto_${idEquipo}" class="form-label">Modelo</label>
                                                        <input type="text" class="form-control" id="producto_${idEquipo}" name="producto" value="${equipo.producto || ''}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="numero_serie_${idEquipo}" class="form-label">Número de Serie</label>
                                                        <input type="text" class="form-control" id="numero_serie_${idEquipo}" name="numero_serie" value="${equipo.numero_serie || ''}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="tipo_pc_${idEquipo}" class="form-label">Tipo de PC</label>
                                                        <input type="text" class="form-control" id="tipo_pc_${idEquipo}" name="tipo_pc" value="${equipo.tipo_pc || ''}">
                                                    </div>
                                                 <div class="col-md-4">
                                                        <label for="usuarios_select_${idEquipo}" class="form-label">Usuarios</label>
                                                        <select class="form-control" id="usuarios_select_${idEquipo}" name="usuarios_select" 
                                                            data-id="${equipo.id_usuario}">
                                                            <option value="">Cargando usuarios...</option>
                                                        </select>
                                                    </div>


                                                </div>

                                                <!-- Dispositivo de Memoria -->
                                                <h5>Dispositivo de Memoria</h5>
                                                <div id="memoriaContainer_${idEquipo}">
                                                    <p>Cargando módulos de memoria...</p>
                                                </div>

                                                <!-- Procesador -->
                                                <h5>Procesador</h5>
                                                <div class="row mb-3">
                                                    <div class="col-md-4">
                                                        <label for="fabricante_procesador_${idEquipo}" class="form-label">Fabricante del Procesador</label>
                                                        <input type="text" class="form-control" id="fabricante_procesador_${idEquipo}" name="fabricante_procesador" value="${equipo.equipo_fabricante || ''}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="modelo_procesador_${idEquipo}" class="form-label">Modelo del Procesador</label>
                                                        <input type="text" class="form-control" id="modelo_procesador_${idEquipo}" name="modelo_procesador" value="${equipo.modelo_procesador || ''}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="frecuencia_procesador_${idEquipo}" class="form-label">Frecuencia Procesador</label>
                                                        <input type="text" class="form-control" id="frecuencia_procesador_${idEquipo}" name="frecuencia_procesador" value="${equipo.equipo_velocidad || ''}"oninput="permitirSoloNumeros(event)">
                                                    </div>
                                                </div>

                                                <!-- Almacenamiento -->
                                                <h5>Almacenamiento</h5>
                                                <div class="row mb-3">
                                                    <div class="col-md-4">
                                                        <label for="nombre_almacenamiento_${idEquipo}" class="form-label">Nombre de Almacenamiento</label>
                                                        <input type="text" class="form-control" id="nombre_almacenamiento_${idEquipo}" name="nombre_almacenamiento" value="${equipo.equipo_modelo || ''}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="capacidad_almacenamiento_${idEquipo}" class="form-label">Capacidad de Almacenamiento (GB)</label>
                                                        <input type="text" class="form-control" id="capacidad_almacenamiento_${idEquipo}" name="capacidad_almacenamiento" value="${equipo.equipo_capacidad || ''}"oninput="permitirSoloNumeros(event)">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="tipo_almacenamiento_${idEquipo}" class="form-label">Tipo de Almacenamiento</label>
                                                        <input type="text" class="form-control" id="tipo_almacenamiento_${idEquipo}" name="tipo_almacenamiento" value="${equipo.equipo_tamano || ''}">
                                                    </div>
                                                </div>

                                                <!-- Caracteristicas -->
                                                <h5>Programas</h5>
                                                <div class="row mb-3">                                       
                                                    <div class="col-md-4">
                                                        <label for="windows_${idEquipo}" class="form-label">windows</label>
                                                        <input type="text" class="form-control" id="windows_${idEquipo}" name="windows" value="${equipo.windows || ''}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="office_${idEquipo}" class="form-label">Office</label>
                                                        <input type="text" class="form-control" id="office_${idEquipo}" name="office" value="${equipo.office || ''}">
                                                    </div>
                                                            <div class="col-md-4">
                                                        <label for="antiVirus_${idEquipo}" class="form-label">AntiVirus</label>
                                                        <input type="text" class="form-control" id="antiVirus_${idEquipo}" name="antiVirus" value="${equipo.antivirus || ''}">
                                                    </div>                                     
                                                </div>

                                                <h5>Monitores</h5>
                                                <div id="monitorContainer_${idEquipo}">
                                                    <p>Cargando monitores...</p>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Observaciones -->
                                    <div class="tab-pane fade" id="observaciones_${idEquipo}" role="tabpanel">
                                        <form id="formObservaciones_${idEquipo}">
                                            <div class="container">
                                                <!-- Fila con los 4 inputs alineados en una sola línea -->
                                                <div class="row mb-3">
                                                      <div class="col-md-3">
                                                        <label for="proveedor_${idEquipo}" class="form-label">Proveedor</label>
                                                        <input type="text" class="form-control" id="proveedor_${idEquipo}" name="proveedor" value="${equipo.proveedor || ''}">
                                                    </div>
                                                        <div class="col-md-3">
                                                        <label for="fecha_compra_${idEquipo}" class="form-label">Fecha Compra</label>
                                                        <input type="date" class="form-control" id="fecha_compra_${idEquipo}" name="fecha_compra" value="${equipo.fecha_compra || ''}">
                                                    </div>
                                                        <div class="col-md-3">
                                                        <label for="numero_factura_${idEquipo}" class="form-label">Número de Factura</label>
                                                        <input type="text" class="form-control" id="numero_factura_${idEquipo}" name="numero_factura" value="${equipo.numero_factura || ''}" placeholder="Número de Factura"oninput="permitirSoloNumeros(event)">
                                                    </div>
                                                     <div class="col-md-3">
                                                            <label for="valor_equipo_${idEquipo}" class="form-label">Valor del Equipo</label>
                                                            <input type="text" class="form-control" id="valor_equipo_${idEquipo}" name="valor_equipo" 
                                                                value="${equipo.valor_equipo ? equipo.valor_equipo.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') : ''}" 
                                                                placeholder="Precio en $" oninput="formatearPesos(event)">
                                                        </div>

                                              
                                                
                                                
                                                </div>
                                    
                                                <!-- Fila con el textarea que ocupa todo el ancho -->
                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <label for="observacion_${idEquipo}" class="form-label">Observaciones</label>
                                                        <textarea class="form-control" id="observacion_${idEquipo}" name="observacion" rows="3">${equipo.observacion || ''}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                </div>
                            `;
                cargarUsuariossss(idEquipo); // Carga usuarios en el select correspondiente
                cargarMemoria(idEquipo); // Carga los módulos de memoria
                cargarMonitores(idEquipo); // Carga los datos de los monitores

            } else {
                acordeonContainer.innerHTML = `<p>Error: ${data.error}</p>`;
            }
        })
        .catch(error => {
            console.error('Error al cargar los datos del equipo:', error);
            acordeonContainer.innerHTML = '<p>Error al cargar los datos del equipo.</p>';
        });
}






















function cargarUsuariossss(idEquipo) {
    
    console.log(idEquipo);
    
    const selectUsuarios = document.getElementById(`usuarios_select_${idEquipo}`);
    if (!selectUsuarios) {
        console.error(`Elemento usuarios_select_${idEquipo} no encontrado.`);
        return;
    }

    // Mostrar mensaje de carga inicial
    selectUsuarios.innerHTML = '<option value="">Cargando usuarios...</option>';

    // Realizar la solicitud AJAX
    fetch(`modelos/rescatar/usuarios.php`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(userData => {
            console.log('Respuesta del servidor:', userData);
            if (userData.success) {
                const usuarios = userData.usuarios || [];
                console.log('Usuarios cargados:', usuarios);

                // Obtener el dataset 'id' del elemento select para marcar el usuario seleccionado
                const data = selectUsuarios.dataset['id']; // Obtiene el ID del usuario asociado
            selectUsuarios.innerHTML = `
                <option value="">Seleccionar usuario</option>
                ${usuarios.map(usuario => `
                    <option value="${usuario.id}" ${usuario.id === data ? 'selected' : ''}>
                        ${usuario.nombre} ${usuario.apellido_paterno}
                    </option>
                `).join('')}
            `;
            
                        } else {
                            selectUsuarios.innerHTML = '<option value="">No se encontraron usuarios</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar los usuarios:', error);
                        selectUsuarios.innerHTML = '<option value="">Error al cargar los usuarios</option>';
                    });
            }


function cargarMemoria(idEquipo) {
    const memoriaContainer = document.getElementById(`memoriaContainer_${idEquipo}`);
    if (!memoriaContainer) {
        console.error(`Elemento memoriaContainer_${idEquipo} no encontrado.`);
        return;
    }

    // Mostrar mensaje de carga inicial
    memoriaContainer.innerHTML = '<p>Cargando módulos de memoria...</p>';

    fetch(`modelos/rescatar/entregar_memoria.php?id_equipo=${idEquipo}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(memoriaData => {
            if (memoriaData.success) {
                const memorias = memoriaData.memorias || [];
                if (memorias.length === 0) {
                    memoriaContainer.innerHTML = '<p>No se encontraron módulos de memoria para este equipo.</p>';
                    return;
                }

                // Generar pestañas dinámicamente
                const tabs = memorias.map((_, index) => `
                    <li class="nav-item" role="presentation">
                        <a class="nav-link ${index === 0 ? 'active' : ''}" id="memory${index + 1}-tab_${idEquipo}" 
                           data-bs-toggle="tab" href="#memory${index + 1}_${idEquipo}" role="tab">
                            Memoria ${index + 1}
                        </a>
                    </li>
                `).join('');

                // Generar contenido de pestañas dinámicamente
                const tabContent = memorias.map((memoria, index) => `
                    <div class="tab-pane fade ${index === 0 ? 'show active' : ''}" id="memory${index + 1}_${idEquipo}" role="tabpanel">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="designacion_memoria${index + 1}_${idEquipo}" class="form-label">Designación Memoria</label>
                                <input type="text" class="form-control" id="designacion_memoria${index + 1}_${idEquipo}" 
                                       name="designacion_memoria${index + 1}" value="${memoria.designacion_memoria || ''}">
                            </div>
                            <div class="col-md-4">
                                <label for="formato_memoria${index + 1}_${idEquipo}" class="form-label">Formato Memoria</label>
                                <input type="text" class="form-control" id="formato_memoria${index + 1}_${idEquipo}" 
                                       name="formato_memoria${index + 1}" value="${memoria.formato_memoria || ''}">
                            </div>
                            <div class="col-md-4">
                                <label for="tipo_memoria${index + 1}_${idEquipo}" class="form-label">Tipo Memoria</label>
                                <input type="text" class="form-control" id="tipo_memoria${index + 1}_${idEquipo}" 
                                       name="tipo_memoria${index + 1}" value="${memoria.tipo_memoria || ''}">
                            </div>
                            <div class="col-md-4">
                                <label for="tamano_memoria${index + 1}_${idEquipo}" class="form-label">Tamaño Memoria (GB)</label>
                                <input type="text" class="form-control" id="tamano_memoria${index + 1}_${idEquipo}" 
                                       name="tamano_memoria${index + 1}" value="${memoria.tamano_memoria || ''}" oninput="permitirSoloNumeros(event)">
                            </div>
                            <div class="col-md-4">
                                <label for="frecuencia_memoria${index + 1}_${idEquipo}" class="form-label">Frecuencia Memoria (MHz)</label>
                                <input type="text" class="form-control" id="frecuencia_memoria${index + 1}_${idEquipo}" 
                                       name="frecuencia_memoria${index + 1}" value="${memoria.frecuencia_memoria || ''}" oninput="permitirSoloNumeros(event)">
                            </div>
                            <div class="col-md-4">
                                <label for="marca_memoria${index + 1}_${idEquipo}" class="form-label">Marca Memoria</label>
                                <input type="text" class="form-control" id="marca_memoria${index + 1}_${idEquipo}" 
                                       name="marca_memoria${index + 1}" value="${memoria.marca_memoria || ''}">
                            </div>
                        </div>
                    </div>
                `).join('');

                memoriaContainer.innerHTML = `
                    <ul class="nav nav-tabs" id="memoryTab_${idEquipo}" role="tablist">
                        ${tabs}
                    </ul>
                    <div class="tab-content mt-3" id="memoryTabContent_${idEquipo}">
                        ${tabContent}
                    </div>
                `;
            } else {
                memoriaContainer.innerHTML = '<p>No se encontraron módulos de memoria para este equipo.</p>';
            }
        })
        .catch(error => {
            console.error('Error al cargar módulos de memoria:', error);
            memoriaContainer.innerHTML = '<p>Error al cargar módulos de memoria.</p>';
        });
}






function cargarMonitores(idEquipo) {
    const monitorContainer = document.getElementById(`monitorContainer_${idEquipo}`);
    if (!monitorContainer) {
        console.error(`Elemento monitorContainer_${idEquipo} no encontrado.`);
        return;
    }

    // Mostrar mensaje de carga inicial
    monitorContainer.innerHTML = '<p>Cargando monitores...</p>';

    fetch(`modelos/rescatar/entrega_monitor.php?id_equipo=${idEquipo}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(monitorData => {
            if (monitorData.success) {
                const monitores = monitorData.monitores || [];
                monitorContainer.innerHTML = `
                    <ul class="nav nav-tabs" id="monitorTab_${idEquipo}" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="monitor1-tab_${idEquipo}" data-bs-toggle="tab" href="#monitor1_${idEquipo}" role="tab">Monitor 1</a>
                        </li>
                        ${monitores.length > 1 ? `
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="monitor2-tab_${idEquipo}" data-bs-toggle="tab" href="#monitor2_${idEquipo}" role="tab">Monitor 2</a>
                            </li>
                        ` : ''}
                    </ul>
                    <div class="tab-content mt-3" id="monitorTabContent_${idEquipo}">
                        ${monitores.map((monitor, index) => `
                            <div class="tab-pane fade ${index === 0 ? 'show active' : ''}" id="monitor${index + 1}_${idEquipo}" role="tabpanel">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="monitor_${index + 1}_modelo_${idEquipo}" class="form-label">Modelo</label>
                                        <input type="text" class="form-control" id="monitor_${index + 1}_modelo_${idEquipo}" name="monitor_${index + 1}_modelo" value="${monitor.modelo_monitor || ''}">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="monitor_${index + 1}_codigo_${idEquipo}" class="form-label">ID</label>
                                        <input type="text" class="form-control" id="monitor_${index + 1}_codigo_${idEquipo}" name="monitor_${index + 1}_codigo" value="${monitor.codigo_monitor || ''}">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="monitor_${index + 1}_numero_serie_${idEquipo}" class="form-label">Número de Serie</label>
                                        <input type="text" class="form-control" id="monitor_${index + 1}_numero_serie_${idEquipo}" name="monitor_${index + 1}_numero_serie" value="${monitor.serie_monitor || ''}">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="monitor_${index + 1}_tamano_${idEquipo}" class="form-label">Tamaño (pulgadas)</label>
                                        <input type="text" class="form-control" id="monitor_${index + 1}_tamano_${idEquipo}" name="monitor_${index + 1}_tamano" value="${monitor.tamano_monitor || ''}" oninput="permitirSoloNumeros(event)">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="monitor_${index + 1}_resolucion_${idEquipo}" class="form-label">Resolución</label>
                                        <input type="text" class="form-control" id="monitor_${index + 1}_resolucion_${idEquipo}" name="monitor_${index + 1}_resolucion" value="${monitor.resolucion_monitor || ''}">
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            } else {
                monitorContainer.innerHTML = '<p>No se encontraron datos de monitores para este equipo.</p>';
            }
        })
        .catch(error => {
            console.error('Error al cargar los monitores:', error);
            monitorContainer.innerHTML = '<p>Error al cargar los datos de los monitores.</p>';
        });
}








function limpiarFormularioComputador() {
    // IDs de los formularios a limpiar
    const formularios = ['pestanaObservacion', 'formAgregarEquipo'];

    formularios.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            // Selecciona todos los inputs dentro del formulario y los limpia
            const inputs = form.querySelectorAll('input');
            inputs.forEach(input => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false; // Deselecciona checkboxes y radios
                } else {
                    input.value = ''; // Limpia el valor de otros tipos de input
                }
            });

            // Limpia los select
            const selects = form.querySelectorAll('select');
            selects.forEach(select => {
                select.selectedIndex = 0; // Restaura el select al valor predeterminado
            });

            // Limpia los textareas
            const textareas = form.querySelectorAll('textarea');
            textareas.forEach(textarea => {
                textarea.value = ''; // Limpia el contenido del textarea
            });
        }
    });

    console.log("Formularios limpiados");
}


function agregarDispositivo() {
    Swal.fire({
        title: 'Agregar dispositivo',
        html: `
            <div class="form-group">
                <label for="inputNuevoDispositivo">Nombre del dispositivo:</label>
                <input id="inputNuevoDispositivo" type="text" class="form-control" placeholder="Ingrese el nombre del dispositivo">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cerrar',
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_crear',
            cancelButton: 'bt_cerrar',
        },
        preConfirm: () => {
            const nuevoDispositivo = document.getElementById('inputNuevoDispositivo').value.trim();
            if (!nuevoDispositivo) {
                Swal.showValidationMessage('El campo no puede estar vacío');
                return false;
            }

            // Validar si el dispositivo ya existe antes de cerrar el modal
            return $.ajax({
                url: 'modelos/guardar/otros_dispositivos.php',
                type: 'POST',
                dataType: 'json',
                data: { nombre_dispositivo: nuevoDispositivo },
            })
                .then(response => {
                    if (!response.success) {
                        if (response.error === 'El dispositivo ya existe en la base de datos') {
                            Swal.showValidationMessage('El dispositivo ya existe en la base de datos');
                            return false; // Evita cerrar el modal
                        } else {
                            Swal.showValidationMessage(response.error || 'Error al guardar el dispositivo');
                            return false; // Evita cerrar el modal
                        }
                    }
                    return response; // Permite cerrar el modal si no hay errores
                })
                .catch(() => {
                    Swal.showValidationMessage('Error al conectar con el servidor');
                    return false; // Evita cerrar el modal
                });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                title: 'Éxito',
                text: 'El dispositivo ha sido agregado correctamente.',
                icon: 'success',
                timer: 2000,
                timerProgressBar: true, // Mostrar la barra de progreso del tiempo

                showConfirmButton: false,
                customClass: {
                    popup: 'cuerpo_modal_guardar',
                    confirmButton: 'bt_activar_alumno'
                }
            }).then(() => {
                window.location.href = '/ticket/agregar_equipos.php?tipo=equipo';
            });
        }
    });
}




// Función para guardar los cambios realizados
function guardarCambiosEquipo(equipoData) {
    const idEquipo = equipoData.id_equipo;

    const form1 = document.getElementById(`formEditarEquipo_${idEquipo}`);
    const form2 = document.getElementById(`formObservaciones_${idEquipo}`);

    // Agregar todos los valores de form1 al objeto equipoData
    Array.from(form1.elements).forEach(input => {
        if (input.name) {
            equipoData[input.name] = input.value.trim();
        }
    });

    // Agregar todos los valores de form2 al objeto equipoData
    Array.from(form2.elements).forEach(input => {
        if (input.name) {
            equipoData[input.name] = input.value.trim();
        }
    });

    // Recopilar datos de las memorias
    equipoData.memorias = []; // Array para almacenar cada memoria como un objeto separado
    const memoryTabContent = document.getElementById(`memoryTabContent_${idEquipo}`);
    if (memoryTabContent) {
        const memoryTabs = Array.from(memoryTabContent.querySelectorAll('.tab-pane'));

          memoryTabs.forEach((tab, index) => {
            let memoriaData = {};
            Array.from(tab.querySelectorAll('input')).forEach(input => {
                if (input.name) {
                    memoriaData[input.name] = input.value.trim();
                }
            });
            equipoData.memorias.push(memoriaData); // Añadir el objeto de memoria al array de memorias
        });

    }

    // Enviar los datos al servidor
    fetch(`modelos/editar/equipo.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(equipoData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'El equipo se ha actualizado correctamente.',
                    timer: 2000,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'cuerpo_modal_guardar',
                        confirmButton: 'bt_activar_alumno'
                    }
                }).then(() => {
                    window.location.href = 'bitacora.php';
                });
                cancelarEdicionEquipo(idEquipo);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.error || 'Hubo un problema al guardar los cambios.'
                });
            }
        })
        .catch(error => {
            console.error('Error al guardar los cambios:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema con la solicitud al servidor.'
            });
        });
}













// Función para cancelar la edición y cerrar el acordeón
function cancelarEdicionEquipo(idEquipo) {

    // alert("funcion cancelarEdicionEquipo" + idEquipo );
    const acordeon = document.getElementById(`acordeonEquipo - ${idEquipo}`);
    if (acordeon) acordeon.remove();
}


// Función para eliminar equipo
function eliminarEquipo(idEquipo) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer',
        // icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'cuerpo_modal_eliminar',
            confirmButton: 'bt_crear',
            cancelButton: 'bt_cerrar',
        }



    }).then((result) => {
        if (result.isConfirmed) {
            // Realizar solicitud AJAX para eliminar el equipo
            fetch(`modelos/eliminar/eliminar_equipo.php?id=${idEquipo}`, {
                method: 'DELETE'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Equipo eliminado',
                            text: 'El equipo ha sido eliminado correctamente.',
                            timer: 2000,
                            showConfirmButton: false,
                            customClass: {
                                popup: 'cuerpo_modal_guardar',
                                confirmButton: 'bt_activar_alumno'
                            }
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.error || 'No se pudo eliminar el equipo.',
                            customClass: {
                                popup: 'cuerpo_modal_guardar',
                                confirmButton: 'bt_activar_alumno'
                            }
                        });
                    }
                })
                .catch(error => console.error('Error al eliminar el equipo:', error));
        }
    });
}

function filtrarEquipos() {
    const input = document.getElementById('buscadorEquipos');
    const filtro = input.value.toLowerCase();
    const filas = document.querySelectorAll('#equiposTableBody tr');

    // Cerrar todos los acordeones
    document.querySelectorAll("tr[id^='acordeonRow_']").forEach(row => {
        row.style.display = 'none'; // Ocultar todos los acordeones
    });

    // Quitar cualquier efecto 3D de las filas principales
    document.querySelectorAll("tr[id^='equipo_']").forEach(row => {
        row.classList.remove('efecto-3d');
    });

    // Filtrar las filas en base al texto ingresado
    filas.forEach(fila => {
        const textoFila = fila.innerText.toLowerCase();
        if (textoFila.includes(filtro)) {
            fila.style.display = ''; // Mostrar la fila si coincide con el filtro
        } else {
            fila.style.display = 'none'; // Ocultar la fila si no coincide
        }
    });

    // Asegurarnos de que ningún acordeón quede visible
    document.querySelectorAll("tr[id^='acordeonRow_']").forEach(acordeon => {
        acordeon.style.display = 'none';
    });
}


