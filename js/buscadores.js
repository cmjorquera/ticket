document.addEventListener("DOMContentLoaded", function () {
    // Configuración de los buscadores
    const buscadores = [
        { inputId: "idBuscarmensaje", tableId: "dataTable" },
        { inputId: "idBuscarTicket", tableId: "idTablaTicket" },
        { inputId: "buscar_ticketAdministrador", tableId: "IDdataTablaAdmin" },
        { inputId: "buscarUsuario", tableId: "idTablaUsuarios" }   // BUSCADOR DE PERMISOS.PHP


    ];

    buscadores.forEach(({ inputId, tableId }) => {
        const inputBuscar = document.getElementById(inputId);
        const tabla = document.getElementById(tableId);

        if (inputBuscar && tabla) { // Verifica que existan
            inputBuscar.addEventListener("input", function () {
                const filtro = inputBuscar.value.toLowerCase();
                const filas = tabla.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

                Array.from(filas).forEach(fila => {
                    const celdas = Array.from(fila.getElementsByTagName("td"));
                    const textoFila = celdas.map(celda => celda.textContent.toLowerCase()).join(" ");
                    fila.style.display = textoFila.includes(filtro) ? "" : "none";
                });
            });
        }
    });
});
