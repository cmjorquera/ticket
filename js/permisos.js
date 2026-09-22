// Función para cargar usuarios asociados
function cargarUsuariosAsociados() {
    return $.ajax({
        url: 'configuracion/ajax/obtener_usuarios_asociados.php',
        type: 'GET',
        dataType: 'json'
    });
}

// Función para guardar permisos de usuario
function guardarPermisosUsuario(idUsuario, permisos) {
    return $.ajax({
        url: 'configuracion/ajax/guardar_permisos_usuario.php',
        type: 'POST',
        dataType: 'json',
        data: {
            id_usuario: idUsuario,
            permisos: JSON.stringify(permisos)
        }
    });
}

// Obtener permisos de un usuario
function obtenerPermisosUsuario(idUsuario) {
    return $.ajax({
        url: 'configuracion/ajax/obtener_permisos_usuario.php?id=' + idUsuario,
        type: 'GET',
        dataType: 'json'
    });
}

// Guardar cambios de permisos desde modal
function guardarCambiosPermisos(idUsuario, permisosArray) {
    guardarPermisosUsuario(idUsuario, permisosArray)
        .done(function(response) {
            if (response.ok) {
                Swal.fire({
                    title: 'Éxito',
                    text: 'Permisos guardados (' + response.permisos_procesados + ' menús actualizados)',
                    icon: 'success',
                    timer: 2000
                });
            } else {
                Swal.fire('Error', response.mensaje, 'error');
            }
        })
        .fail(function() {
            Swal.fire('Error', 'Error al guardar los permisos', 'error');
        });
}
