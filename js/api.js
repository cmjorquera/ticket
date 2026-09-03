// Ejecuta llamadas fetch centralizadas con manejo uniforme de errores y toasts.
async function apiCall(endpoint, method = 'GET', data = null) {
  const options = {
    method,
    headers: { 'Accept': 'application/json' },
    credentials: 'same-origin'
  };

  if (data !== null) {
    options.headers['Content-Type'] = 'application/json';
    options.body = JSON.stringify(data);
  }

  try {
    const response = await fetch(endpoint, options);

    if (response.status === 401) {
      showToast?.('warning', 'Sesión expirada', 'Vuelve a iniciar sesión.');
      setTimeout(() => { window.location.href = 'index.php'; }, 1200);
      return { success: false, mensaje: 'Sesión expirada', data: null };
    }

    if (response.status >= 500) {
      showToast?.('danger', 'Error del servidor', 'Intenta nuevamente en unos minutos.');
      return { success: false, mensaje: 'Error del servidor', data: null };
    }

    const json = await response.json();
    if (!response.ok || json.success === false) {
      showToast?.('danger', 'Error', json.mensaje || 'No se pudo completar la operación.');
    } else if (json.mensaje) {
      showToast?.('success', 'Listo', json.mensaje);
    }

    return json;
  } catch (error) {
    showToast?.('danger', 'Sin conexión', 'No fue posible conectar con el servidor.');
    return { success: false, mensaje: 'Error de red', data: null, error };
  }
}

