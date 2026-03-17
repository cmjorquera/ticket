

        // < !--- FUNCION PARA ACTUALIZAR EL NUMERO DE MENSAJES-- >

        document.addEventListener('DOMContentLoaded', function() {
            const usuarioId = document.getElementById('idUsuario').value;

            // Función para cargar y actualizar el número de mensajes
            function actualizarNumeroMensajes() {
                $.ajax({
                    type: 'GET',
                    url: 'https://acceso.seduc.cl/api/obtenerCantidadMensajes',
                    dataType: "json",
                    data: {
                        id_usuario: usuarioId
                    },  
                    success: function(data) {
                        $('#numeroMensajes').text(data);
                    }
                });
            }

            actualizarNumeroMensajes();

            setInterval(actualizarNumeroMensajes, 3000);
            // setInterval(actualizarNumeroMensajes, 120000);
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            const usuarioId = document.getElementById('idUsuario').value;

            // Función para cargar y actualizar el número de mensajes
            function actualizarNumeroAlertas() {
                $.ajax({
                    type: 'GET',
                    url: 'https://acceso.seduc.cl/api/obtenerCantidadAlertas',
                    dataType: "json",
                    data: {
                        id_usuario: usuarioId
                    },
                    success: function(data) {
                        $('#numeroAlertas').text(data);
                    }
                });
            }

            actualizarNumeroAlertas();

            setInterval(actualizarNumeroAlertas, 3000);
        });

        document.addEventListener('DOMContentLoaded', function() {
            const usuarioId = document.getElementById('idUsuario').value;

            // Función para cargar y actualizar el contenedor de mensajes
            function actualizarMensajes() {
                $.ajax({
                    type: 'GET',
                    url: 'https://acceso.seduc.cl/api/obtenerMensajes',
                    data: {
                        id_usuario: usuarioId
                    },
                    success: function(data) {
                        // Actualizar el contenido del div con el HTML devuelto por la API
                        $('#contendorMensajes').html(data);
                    }
                });
            }

            // Llamar a la función inmediatamente cuando la página cargue
            actualizarMensajes();

            // Actualizar cada 3 segundos
            setInterval(actualizarMensajes, 3000);
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            const usuarioId = document.getElementById('idUsuario').value;

            // Función para cargar y actualizar el contenedor de mensajes
            function actualizarRecordatorio() {
                $.ajax({
                    type: 'GET',
                    url: 'https://acceso.seduc.cl/api/obtenerRecordatorios',
                    data: {
                        id_usuario: usuarioId
                    },
                    success: function(data) {
                        // Actualizar el contenido del div con el HTML devuelto por la API
                        $('#contendorTicket').html(data);
                    }
                });
            }

            // Llamar a la función inmediatamente cuando la página cargue
            actualizarRecordatorio();

            // Actualizar cada 3 segundos
            setInterval(actualizarRecordatorio, 3000);
        });
