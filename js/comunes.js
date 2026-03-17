// FUNCIONES COMUNES QUE ESTARAN EN LA MAYORIA DE LAS PAGINAS

function obtenerResolucionPantalla() {
    var anchoPantalla = window.screen.width;
    var altoPantalla = window.screen.height;

    console.log(`Resolución de pantalla: ${anchoPantalla} x ${altoPantalla}`);

    // Puedes retornar los valores si los necesitas para otro propósito
    return {
        ancho: anchoPantalla,
        alto: altoPantalla
    };
}

function validateEmail(email) {
    const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return re.test(String(email).toLowerCase());
}


function mostrarOcultarClave() {
    const passwordInput = document.getElementById('pass');
    const toggleIcon = document.getElementById('toggleIcon');

    if (passwordInput && toggleIcon) {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text'; // Mostrar la clave
            toggleIcon.classList.remove('bi-eye-slash-fill');
            toggleIcon.classList.add('bi-eye-fill'); // Cambiar ícono a ojo abierto
        } else {
            passwordInput.type = 'password'; // Ocultar la clave
            toggleIcon.classList.remove('bi-eye-fill');
            toggleIcon.classList.add('bi-eye-slash-fill'); // Cambiar ícono a ojo cerrado
        }
    }
}


// esat funcion manda el coreo que se diguito ene l index hacia el recuperar_cclave 
function redirigirConCorreo() {
    // Obtener el valor del input de correo
    const correo = document.getElementById('usuario').value;

    // Redirigir a recuperar_clave.php con el correo en la URL solo si el campo no está vacío
    if (correo.trim() !== '') {
        window.location.href = 'recuperar_clave.php?email=' + encodeURIComponent(correo);
    } else {
        window.location.href = 'recuperar_clave.php';
    }
}


function mostrarocultarPass(passwordFieldId, iconId) {
    const passwordInput = document.getElementById(passwordFieldId);
    const toggleIcon = document.getElementById(iconId);

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('bi-eye-fill');
        toggleIcon.classList.add('bi-eye-slash-fill');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash-fill');
        toggleIcon.classList.add('bi-eye-fill');
    }
}



function validarFormulario(formId, campos) {
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById(formId);
        const invalidClass = 'is-invalid';  // Clase para marcar campos inválidos
        const validClass = 'is-valid';      // Clase para marcar campos válidos

        form.addEventListener('submit', function (event) {
            let esValido = true;

            campos.forEach(campo => {
                const inputElement = document.getElementById(campo.id);
                const errorElement = document.getElementById(campo.errorId);

                // Limpiar errores previos
                inputElement.classList.remove(invalidClass);
                inputElement.classList.remove(validClass);
                errorElement.textContent = '';

                // Validar si el campo está vacío
                if (inputElement.value.trim() === '' && campo.requerido) {
                    errorElement.textContent = campo.mensajeVacio || 'Este campo es obligatorio.';
                    inputElement.classList.add(invalidClass);
                    esValido = false;
                } else if (campo.tipo === 'email' && inputElement.value.trim() !== '' && !validarEmail(inputElement.value)) {
                    errorElement.textContent = campo.mensajeFormato || 'Formato de correo inválido.';
                    inputElement.classList.add(invalidClass);
                    esValido = false;
                } else if (inputElement.value.trim() !== '' && (campo.tipo !== 'email' || validarEmail(inputElement.value))) {
                    inputElement.classList.add(validClass); // Solo si tiene contenido válido
                }
            });

            // Si algún campo no es válido, prevenir el envío del formulario
            if (!esValido) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        });

        // Remover la clase 'is-invalid' al escribir y validar si está vacío
        campos.forEach(campo => {
            const inputElement = document.getElementById(campo.id);
            inputElement.addEventListener('input', function () {
                inputElement.classList.remove(invalidClass);
                inputElement.classList.remove(validClass); // Limpiar las clases 'is-valid' si hay algún cambio

                if (inputElement.value.trim() !== '') {
                    if (campo.tipo !== 'email' || validarEmail(inputElement.value)) {
                        inputElement.classList.add(validClass);  // Si no está vacío y es válido
                    }
                }
            });
        });
    });
}

// Función para validar formato de email
function validarEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}



function permitirSoloNumeros(event) {
    // Eliminar cualquier carácter que no sea número
    event.target.value = event.target.value.replace(/[^0-9]/g, '');
}





function formatearMiles(numero) {
    if (!numero) return ''; // Manejar valores nulos o indefinidos
    return numero.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Agregar separadores de miles
}



// function formatearPesos(event) {
//     const input = event.target;
//     let valor = input.value.replace(/\D/g, ''); // Elimina cualquier carácter que no sea dígito.
//     valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Agrega puntos como separadores de miles.
//     input.value = valor; // Actualiza el valor del input con el formato.
// }


function formatearPesos(event) {
    const input = event.target;
    let valor = input.value.replace(/\D/g, ''); // Elimina cualquier carácter que no sea numérico.
    valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Agrega puntos cada tres dígitos.
    input.value = valor; // Actualiza el valor del input con el formato.
}


// utils.js
function bloquearPasado(elOrId) {
  const el = typeof elOrId === "string" ? document.getElementById(elOrId) : elOrId;
  if (!el) return;
  const d = new Date(); d.setHours(0,0,0,0);
  const hoy = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
  el.min = hoy;
  el.addEventListener('change', () => { if (el.value && el.value < el.min) el.value = el.min; });
}



 function validarFecha(event) {
        const input = event.target;
        const fechaSeleccionada = new Date(input.value);
        const hoy = new Date();

        // Si la fecha seleccionada es posterior a hoy, resetea el campo
        if (fechaSeleccionada > hoy) {
            alert("No puedes seleccionar una fecha futura.");
            input.value = ""; // Limpia el campo
        }
    }


    // Función para validar URLs
    function isValidURL(url) {
        let pattern = new RegExp("^(https?:\\/\\/)?" +
            "((([a-zA-Z0-9\\-\\.]+)\\.([a-zA-Z]{2,5}))|" +
            "localhost|" +
            "((\\d{1,3}\\.){3}\\d{1,3}))" +
            "(\\:\\d+)?(\\/[-a-zA-Z0-9%_\\+.~#?&//=]*)?$", "i");
        return pattern.test(url);
    }


