// ─── Validaciones de formularios ─────────────────────────────────────────────

(function () {

    function validarCampo(input) {
        const valor     = input.value.trim();
        const requerido = input.hasAttribute('required');
        const tipo      = input.type;
        let   error     = '';

        if (requerido && !valor) {
            error = 'Este campo es obligatorio';
        } else if (tipo === 'email' && valor && !validarEmail(valor)) {
            error = 'Correo no válido';
        } else if (tipo === 'number' && valor && isNaN(Number(valor))) {
            error = 'Debe ser un número';
        } else if (input.minLength > 0 && valor && valor.length < input.minLength) {
            error = `Mínimo ${input.minLength} caracteres`;
        }

        mostrarErrorCampo(input, error);
        return error === '';
    }

    function mostrarErrorCampo(input, mensaje) {
        let span = input.parentElement.querySelector('.field-error');
        if (!span) {
            const field = input.closest('.field');
            if (field) span = field.querySelector('.field-error');
        }
        if (!span) return;
        span.textContent = mensaje;
        input.classList.toggle('input-error', mensaje !== '');
    }

    function validarFormulario(form) {
        let valido = true;
        form.querySelectorAll('input, select, textarea').forEach(el => {
            if (!validarCampo(el)) valido = false;
        });
        return valido;
    }

    document.addEventListener('blur', function (e) {
        if (['INPUT', 'SELECT', 'TEXTAREA'].includes(e.target.tagName)) {
            validarCampo(e.target);
        }
    }, true);

    window.validarFormulario = validarFormulario;
    window.validarCampo      = validarCampo;

}());
