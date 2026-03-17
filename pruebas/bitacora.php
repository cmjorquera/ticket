<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/systeminformation/5.3.0/systeminformation.min.js"></script>

    <title>Características del PC Cliente</title>
    <script>
        function obtenerInformacionCliente() {
            // Navegador y versión
            var navegador = navigator.userAgent;

            // Información de la memoria (experimental en algunos navegadores)
            if (navigator.deviceMemory) {
                var memoria = navigator.deviceMemory + " GB";
            } else {
                var memoria = "No disponible";
            }

            // Mostrar la información
            document.getElementById('navegador').innerText = navegador;
            document.getElementById('memoria').innerText = memoria;
        }

        function obtenerPerformance() {
            // Obteniendo datos de rendimiento
            const performanceData = performance.timing;

            // Mostrando algunos de los datos
            document.getElementById('cargaPagina').innerText = 
                (performanceData.loadEventEnd - performanceData.navigationStart) + " ms";
        }

        async function obtenerInformacionSistema() {
            const si = window.systeminformation;

            // Obtener información de CPU
            const cpu = await si.cpu();
            document.getElementById('cpu').innerText = cpu.manufacturer + " " + cpu.brand;

            // Obtener información de la memoria
            const memoria = await si.mem();
            document.getElementById('memoria_total').innerText = (memoria.total / (1024 * 1024 * 1024)).toFixed(2) + " GB";
        }

        // Combinar todas las funciones en una para ejecutar al cargar la página
        function iniciar() {
            obtenerInformacionCliente();
            obtenerPerformance();
            obtenerInformacionSistema();
        }

        window.onload = iniciar;
    </script>
</head>
<body>
    <h1>Características del PC del Cliente</h1>
    <p><strong>Navegador y Sistema Operativo:</strong>       <span id="navegador"></span></p>
    <p><strong>Memoria del Dispositivo:</strong>             <span id="memoria"></span></p>
    <p><strong>Tiempo de carga de la página:</strong>        <span id="cargaPagina"></span></p>
    <p><strong>Procesador:</strong>                         <span id="cpu"></span></p>
    <p><strong>Memoria Total:</strong>                      <span id="memoria_total"></span></p>
</body>
</html>
