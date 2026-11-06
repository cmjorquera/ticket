/* Chat flotante: abre una conversación de bienvenida.
 * Por ahora responde con un mensaje fijo; más adelante enviará la pregunta
 * al chatbot (ver componentes/chat_flotante.php). La conversación se guarda
 * solo mientras la pestaña esté abierta (sessionStorage).
 */
(function () {
    'use strict';
    const raiz = document.getElementById('chatFlotante');
    if (!raiz) { return; }

    const panel = document.getElementById('chatfPanel');
    const burbuja = document.getElementById('chatfBurbuja');
    const lista = document.getElementById('chatfMensajes');
    const form = document.getElementById('chatfForm');
    const texto = document.getElementById('chatfTexto');
    const nombre = (raiz.dataset.nombre || '').split(' ')[0];
    const CLAVE = 'chatf_conversacion';
    const SUGERENCIAS = ['¿Cómo creo un ticket?', '¿En qué va mi ticket?', '¿Qué hay de almuerzo hoy?'];

    const guardar = msgs => { try { sessionStorage.setItem(CLAVE, JSON.stringify(msgs)); } catch (e) { /* sin almacenamiento */ } };
    const leer = () => { try { return JSON.parse(sessionStorage.getItem(CLAVE) || '[]'); } catch (e) { return []; } };
    const hora = () => { const d = new Date(); return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0'); };
    let mensajes = leer();

    function burbujaMsg(m) {
        const div = document.createElement('div');
        div.className = 'chatf-msg chatf-msg--' + m.de;
        div.textContent = m.texto;           // texto plano: nunca HTML
        const small = document.createElement('small');
        small.textContent = m.hora;
        div.appendChild(small);
        return div;
    }

    function pintar() {
        lista.innerHTML = '';
        mensajes.forEach(m => lista.appendChild(burbujaMsg(m)));
        if (mensajes.length <= 1) {
            const sug = document.createElement('div');
            sug.className = 'chatf-sugerencias';
            SUGERENCIAS.forEach(s => {
                const b = document.createElement('button');
                b.type = 'button';
                b.textContent = s;
                b.addEventListener('click', () => enviar(s));
                sug.appendChild(b);
            });
            lista.appendChild(sug);
        }
        lista.scrollTop = lista.scrollHeight;
    }

    function iniciar() {
        mensajes = [{
            de: 'bot',
            texto: '¡Hola' + (nombre ? ', ' + nombre : '') + '! 👋 Soy el asistente de SEDUC.\nMuy pronto podrás preguntarme por tus tickets, el sistema y más. ¿En qué te puedo ayudar?',
            hora: hora(),
        }];
        guardar(mensajes);
        pintar();
    }

    function responder() {
        const escribiendo = document.createElement('div');
        escribiendo.className = 'chatf-escribiendo';
        escribiendo.innerHTML = '<i></i><i></i><i></i>';
        lista.appendChild(escribiendo);
        lista.scrollTop = lista.scrollHeight;
        setTimeout(() => {
            mensajes.push({
                de: 'bot',
                texto: 'Gracias por tu mensaje. Todavía estoy en preparación y aún no puedo responder preguntas. Mientras tanto, puedes crear un ticket en Ticket › Ticket Nuevo.',
                hora: hora(),
            });
            guardar(mensajes);
            pintar();
        }, 900);
    }

    function enviar(t) {
        t = String(t || '').trim();
        if (!t) { return; }
        mensajes.push({ de: 'yo', texto: t.slice(0, 500), hora: hora() });
        guardar(mensajes);
        pintar();
        responder();
    }

    function abrir(abre) {
        raiz.classList.toggle('is-abierto', abre);
        raiz.classList.add('is-visto');
        panel.setAttribute('aria-hidden', abre ? 'false' : 'true');
        burbuja.setAttribute('aria-expanded', abre ? 'true' : 'false');
        burbuja.setAttribute('aria-label', abre ? 'Cerrar chat' : 'Abrir chat');
        if (abre) {
            if (!mensajes.length) { iniciar(); } else { pintar(); }
            setTimeout(() => texto.focus(), 150);
        }
    }

    burbuja.addEventListener('click', () => abrir(!raiz.classList.contains('is-abierto')));
    document.getElementById('chatfCerrar').addEventListener('click', () => { abrir(false); burbuja.focus(); });
    document.getElementById('chatfReiniciar').addEventListener('click', iniciar);
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && raiz.classList.contains('is-abierto')) { abrir(false); burbuja.focus(); } });
    form.addEventListener('submit', e => { e.preventDefault(); enviar(texto.value); texto.value = ''; texto.style.height = ''; });
    texto.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); } });
    texto.addEventListener('input', () => { texto.style.height = 'auto'; texto.style.height = Math.min(texto.scrollHeight, 110) + 'px'; });
}());
