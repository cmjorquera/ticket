/* Mi cuenta · Perfil: datos, contraseña y foto. */
(function () {
    'use strict';
    const { post, toast, esc } = window.MC;

    // ---------- datos personales
    const formDatos = document.getElementById('formDatos');
    formDatos.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = formDatos.querySelector('button[type="submit"]');
        btn.disabled = true;
        try {
            const fd = new FormData(formDatos);
            fd.append('accion', 'datos');
            const r = await post(fd);
            toast('success', 'Listo', r.mensaje);
        } catch (err) {
            toast('error', 'No se guardó', err.message);
        } finally {
            btn.disabled = false;
        }
    });

    // ---------- contraseña
    const formClave = document.getElementById('formClave');
    const nueva = document.getElementById('claveNueva');
    const repetir = document.getElementById('claveRepetir');
    const btnClave = document.getElementById('btnClave');
    const barra = document.getElementById('fuerzaBarra');
    const textoFuerza = document.getElementById('fuerzaTexto');

    function revisar() {
        const v = nueva.value;
        const reglas = {
            largo: v.length >= 8,
            letra: /[A-Za-zÁÉÍÓÚÑáéíóúñ]/.test(v),
            numero: /\d/.test(v),
            iguales: v !== '' && v === repetir.value,
        };
        Object.entries(reglas).forEach(([k, ok]) => {
            const li = document.querySelector('[data-regla="' + k + '"]');
            li.classList.toggle('is-ok', ok);
            li.querySelector('i').className = 'bi ' + (ok ? 'bi-check-circle-fill' : 'bi-circle');
        });
        // Fuerza: largo, variedad de caracteres
        let p = 0;
        if (v.length >= 8) { p++; }
        if (v.length >= 12) { p++; }
        if (/[a-z]/.test(v) && /[A-Z]/.test(v)) { p++; }
        if (/\d/.test(v)) { p++; }
        if (/[^A-Za-z0-9]/.test(v)) { p++; }
        const niveles = [['', 'Escribe una contraseña nueva'], ['debil', 'Débil'], ['debil', 'Débil'], ['media', 'Aceptable'], ['buena', 'Buena'], ['fuerte', 'Muy buena']];
        const [clase, texto] = v ? niveles[p] : niveles[0];
        barra.style.width = v ? Math.max(12, p * 20) + '%' : '0';
        barra.className = clase;
        textoFuerza.textContent = texto;
        btnClave.disabled = !(Object.values(reglas).every(Boolean) && formClave.actual.value !== '');
    }
    [nueva, repetir, formClave.actual].forEach(i => i.addEventListener('input', revisar));

    formClave.addEventListener('click', e => {
        const ojo = e.target.closest('.mc-ojo');
        if (!ojo) { return; }
        const input = ojo.previousElementSibling;
        const ver = input.type === 'password';
        input.type = ver ? 'text' : 'password';
        ojo.querySelector('i').className = 'bi ' + (ver ? 'bi-eye-slash' : 'bi-eye');
        ojo.setAttribute('aria-label', ver ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });

    formClave.addEventListener('submit', async e => {
        e.preventDefault();
        btnClave.disabled = true;
        try {
            const fd = new FormData(formClave);
            fd.delete('usuario');
            fd.append('accion', 'clave');
            const r = await post(fd);
            formClave.reset();
            revisar();
            toast('success', 'Contraseña cambiada', r.mensaje);
        } catch (err) {
            toast('error', 'No se cambió', err.message);
            revisar();
        }
    });

    // ---------- foto
    const inputFoto = document.getElementById('inputFoto');
    const btnQuitar = document.getElementById('btnQuitarFoto');
    function pintarFoto(url) {
        document.querySelectorAll('#mcAvatar, #fotoGrande').forEach(el => {
            const ini = document.getElementById('mcAvatar').dataset.iniciales || 'U';
            el.innerHTML = url ? '<img src="' + esc(url) + '" alt="Tu foto">' : '<span>' + esc(ini) + '</span>';
        });
        btnQuitar.hidden = !url;
    }
    inputFoto.addEventListener('change', async () => {
        const f = inputFoto.files[0];
        inputFoto.value = '';
        if (!f) { return; }
        if (f.size > 3 * 1024 * 1024) { toast('error', 'Foto muy grande', 'La imagen supera los 3 MB.'); return; }
        const fd = new FormData();
        fd.append('accion', 'foto');
        fd.append('foto', f);
        document.getElementById('fotoGrande').classList.add('is-cargando');
        try {
            const r = await post(fd);
            pintarFoto(r.url);
            toast('success', 'Listo', r.mensaje);
        } catch (err) {
            toast('error', 'No se subió la foto', err.message);
        } finally {
            document.getElementById('fotoGrande').classList.remove('is-cargando');
        }
    });
    btnQuitar.addEventListener('click', async () => {
        if (!confirm('¿Quitar tu foto de perfil?')) { return; }
        try {
            const r = await post({ accion: 'quitar_foto' });
            pintarFoto(null);
            toast('success', 'Listo', r.mensaje);
        } catch (err) {
            toast('error', 'Error', err.message);
        }
    });
})();
