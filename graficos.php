<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';
$funciones = new Funciones();
$idUsuarioSession   = htmlspecialchars($_SESSION['id']);    
$nombresession      = htmlspecialchars($_SESSION['nombre']) . '-' . htmlspecialchars($_SESSION['apellido_paterno']);
$idPagActual = 7;

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <?php  $funciones->header();   ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- CSS -->
    <!-- <link href="css/estilo.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet"> -->
</head>
<style>
        .badge-counter {
    position: absolute;
    top: 22px; /* Ajusta este valor según sea necesario */
    right: 0px; /* Ajusta este valor según sea necesario */
    transform: translate(50%, -50%);
    z-index: 10;
    font-size: 0.75rem;
    font-weight: 700;
    color: #fff;
    background-color: #e74a3b;
    border-radius: 10rem;
    padding: 0.25rem 0.5rem;
}
   
   .active-menu-item {
        background-color: #d1e7fd; /* Fondo más claro para el menú activo */
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); /* Sombra para el efecto 3D */
        transform: translateY(-2px); /* Levantar ligeramente */
        border-radius: 5px;
    }
</style> 

<body id="page-top">
    <div id="wrapper">
    <?php  $funciones->menuLateral2($idUsuarioSession,$idPagActual);   ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php  $funciones->cabezera();   ?>
                </nav>
                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <!-- <h1 class="h3 mb-0 text-gray-800">Agregar</h1> -->
                    </div>
                    <div class="row">

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Line Chart</h5>

                                    <!-- Line Chart -->
                                    <canvas id="lineChart"
                                        style="max-height: 400px; display: block; box-sizing: border-box; height: 296px; width: 592px;"
                                        width="592" height="296"></canvas>
                                    <script>
                                    document.addEventListener("DOMContentLoaded", () => {
                                        new Chart(document.querySelector('#lineChart'), {
                                            type: 'line',
                                            data: {
                                                labels: ['January', 'February', 'March', 'April', 'May',
                                                    'June', 'July'
                                                ],
                                                datasets: [{
                                                    label: 'Line Chart',
                                                    data: [65, 59, 80, 81, 56, 55, 40],
                                                    fill: false,
                                                    borderColor: 'rgb(75, 192, 192)',
                                                    tension: 0.1
                                                }]
                                            },
                                            options: {
                                                scales: {
                                                    y: {
                                                        beginAtZero: true
                                                    }
                                                }
                                            }
                                        });
                                    });
                                    </script>
                                    <!-- End Line CHart -->

                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Bar CHart</h5>

                                    <!-- Bar Chart -->
                                    <canvas id="barChart"
                                        style="max-height: 400px; display: block; box-sizing: border-box; height: 296px; width: 592px;"
                                        width="592" height="296"></canvas>
                                    <script>
                                    document.addEventListener("DOMContentLoaded", () => {
                                        new Chart(document.querySelector('#barChart'), {
                                            type: 'bar',
                                            data: {
                                                labels: ['January', 'February', 'March', 'April', 'May',
                                                    'June', 'July'
                                                ],
                                                datasets: [{
                                                    label: 'Bar Chart',
                                                    data: [65, 59, 80, 81, 56, 55, 40],
                                                    backgroundColor: [
                                                        'rgba(255, 99, 132, 0.2)',
                                                        'rgba(255, 159, 64, 0.2)',
                                                        'rgba(255, 205, 86, 0.2)',
                                                        'rgba(75, 192, 192, 0.2)',
                                                        'rgba(54, 162, 235, 0.2)',
                                                        'rgba(153, 102, 255, 0.2)',
                                                        'rgba(201, 203, 207, 0.2)'
                                                    ],
                                                    borderColor: [
                                                        'rgb(255, 99, 132)',
                                                        'rgb(255, 159, 64)',
                                                        'rgb(255, 205, 86)',
                                                        'rgb(75, 192, 192)',
                                                        'rgb(54, 162, 235)',
                                                        'rgb(153, 102, 255)',
                                                        'rgb(201, 203, 207)'
                                                    ],
                                                    borderWidth: 1
                                                }]
                                            },
                                            options: {
                                                scales: {
                                                    y: {
                                                        beginAtZero: true
                                                    }
                                                }
                                            }
                                        });
                                    });
                                    </script>
                                    <!-- End Bar CHart -->

                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Pie Chart</h5>

                                    <!-- Pie Chart -->
                                    <canvas id="pieChart"
                                        style="max-height: 400px; display: block; box-sizing: border-box; height: 400px; width: 592px;"
                                        width="592" height="400"></canvas>
                                    <script>
                                    document.addEventListener("DOMContentLoaded", () => {
                                        new Chart(document.querySelector('#pieChart'), {
                                            type: 'pie',
                                            data: {
                                                labels: [
                                                    'Red',
                                                    'Blue',
                                                    'Yellow'
                                                ],
                                                datasets: [{
                                                    label: 'My First Dataset',
                                                    data: [300, 50, 100],
                                                    backgroundColor: [
                                                        'rgb(255, 99, 132)',
                                                        'rgb(54, 162, 235)',
                                                        'rgb(255, 205, 86)'
                                                    ],
                                                    hoverOffset: 4
                                                }]
                                            }
                                        });
                                    });
                                    </script>
                                    <!-- End Pie CHart -->

                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Doughnut Chart</h5>

                                    <!-- Doughnut Chart -->
                                    <canvas id="doughnutChart"
                                        style="max-height: 400px; display: block; box-sizing: border-box; height: 400px; width: 592px;"
                                        width="592" height="400"></canvas>
                                    <script>
                                    document.addEventListener("DOMContentLoaded", () => {
                                        new Chart(document.querySelector('#doughnutChart'), {
                                            type: 'doughnut',
                                            data: {
                                                labels: [
                                                    'Red',
                                                    'Blue',
                                                    'Yellow'
                                                ],
                                                datasets: [{
                                                    label: 'My First Dataset',
                                                    data: [300, 50, 100],
                                                    backgroundColor: [
                                                        'rgb(255, 99, 132)',
                                                        'rgb(54, 162, 235)',
                                                        'rgb(255, 205, 86)'
                                                    ],
                                                    hoverOffset: 4
                                                }]
                                            }
                                        });
                                    });
                                    </script>
                                    <!-- End Doughnut CHart -->

                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Radar Chart</h5>

                                    <!-- Radar Chart -->
                                    <canvas id="radarChart"
                                        style="max-height: 400px; display: block; box-sizing: border-box; height: 400px; width: 592px;"
                                        width="592" height="400"></canvas>
                                    <script>
                                    document.addEventListener("DOMContentLoaded", () => {
                                        new Chart(document.querySelector('#radarChart'), {
                                            type: 'radar',
                                            data: {
                                                labels: [
                                                    'Eating',
                                                    'Drinking',
                                                    'Sleeping',
                                                    'Designing',
                                                    'Coding',
                                                    'Cycling',
                                                    'Running'
                                                ],
                                                datasets: [{
                                                    label: 'First Dataset',
                                                    data: [65, 59, 90, 81, 56, 55, 40],
                                                    fill: true,
                                                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                                    borderColor: 'rgb(255, 99, 132)',
                                                    pointBackgroundColor: 'rgb(255, 99, 132)',
                                                    pointBorderColor: '#fff',
                                                    pointHoverBackgroundColor: '#fff',
                                                    pointHoverBorderColor: 'rgb(255, 99, 132)'
                                                }, {
                                                    label: 'Second Dataset',
                                                    data: [28, 48, 40, 19, 96, 27, 100],
                                                    fill: true,
                                                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                                    borderColor: 'rgb(54, 162, 235)',
                                                    pointBackgroundColor: 'rgb(54, 162, 235)',
                                                    pointBorderColor: '#fff',
                                                    pointHoverBackgroundColor: '#fff',
                                                    pointHoverBorderColor: 'rgb(54, 162, 235)'
                                                }]
                                            },
                                            options: {
                                                elements: {
                                                    line: {
                                                        borderWidth: 3
                                                    }
                                                }
                                            }
                                        });
                                    });
                                    </script>
                                    <!-- End Radar CHart -->

                                </div>
                            </div>
                        </div>

        

                    </div>
                </div>
                <a class="scroll-to-top rounded" href="#page-top">
                    <i class="fas fa-angle-up"></i>
                </a>
            </div>
            <?php  $funciones->footer();   ?>
        </div>
    </div>
    <?php include("modal_salir.php")?>
</body>


<?php  $funciones->script();   ?>



<script>
function cargarMenuPrimario_candados() {
    $.ajax({
        url: 'rescatando/rescatandomenu1_candados.php', // Asegúrate de que esta URL sea correcta
        type: 'GET',
        dataType: 'json',
        success: function(modulos) {
            var container = document.querySelector('.pestaña1');
            container.innerHTML = ''; // Limpia el contenedor antes de añadir nuevo contenido

            var accordion = document.createElement('div');
            accordion.className = 'accordion';
            accordion.id = 'myAccordion';

            modulos.forEach(function(modulo, index) {
                var accordionItem = document.createElement('div');
                accordionItem.className = 'accordion-item';
                accordionItem.style.border = '2px solid red !important;';

                var accordionHeader = document.createElement('h2');
                accordionHeader.className = 'accordion-header';
                accordionHeader.id = 'heading' + index;

                var accordionButton = document.createElement('button');
                accordionButton.className = 'accordion-button collapsed';
                accordionButton.type = 'button';
                accordionButton.setAttribute('data-bs-toggle', 'collapse');
                accordionButton.setAttribute('data-bs-target', '#collapse' + index);
                accordionButton.setAttribute('aria-expanded', 'false');
                accordionButton.setAttribute('aria-controls', 'collapse' + index);
                actualizarTextoBoton(accordionButton, modulo.nombre, 0, modulo.submodulos.length);

                accordionHeader.appendChild(accordionButton);
                accordionItem.appendChild(accordionHeader);

                var accordionCollapse = document.createElement('div');
                accordionCollapse.id = 'collapse' + index;
                accordionCollapse.className = 'accordion-collapse collapse';
                accordionCollapse.setAttribute('aria-labelledby', 'heading' + index);

                var accordionBody = document.createElement('div');
                accordionBody.className = 'accordion-body';
                accordionBody.style.display = 'grid';
                accordionBody.style.flexWrap = 'wrap'; // Corrección de 'werap' a 'wrap'
                accordionBody.style.gap = '10px'; // Corrección de 'werap' a 'wrap'

                // Aplicar estilos adicionales directamente
                accordionBody.style.gridTemplateColumns =
                    'repeat(2, 1fr)'; // Crea dos columnas de igual ancho
                accordionBody.style.padding = '20px'; // Espacio dentro de cada contenedor
                accordionBody.style.border =
                    '1px solid #ccc'; // Borde para visualizar los contenedores
                accordionBody.style.borderRadius = '5px'; // Bordes redondeados para estética
                // accordionBody.style.backgroundColor = '#ffcccc'; // Color de fondo rojo claro para los no marcados
                // La transición suave se aplica a los elementos individuales dentro del contenedor, no al contenedor en sí.

                modulo.submodulos.forEach(function(submodulo) {
                    var subMenuContainer = document.createElement('div');
                    subMenuContainer.className =
                        'menu-primario-item'; // Clase para estilos CSS
                    subMenuContainer.textContent = submodulo; // Texto del submódulo


                    var checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.id = 'menu' + submodulo
                        .id; // Asegúrate de que submodulo.id exista
                    checkbox.style.display = 'none'; // Oculta el checkbox

                    var label = document.createElement('label');
                    label.htmlFor = 'menu' + submodulo.id;
                    // Asegúrate de que submodulo.nombre sea una cadena antes de llamar a toUpperCase()
                    if (typeof submodulo.nombre === 'string') {
                        label.textContent = submodulo.nombre.toUpperCase();
                    } else {
                        console.warn('El nombre del submodulo no es una cadena:', submodulo
                            .nombre);
                        label.textContent =
                            ''; // Valor por defecto en caso de que no sea una cadena
                    }
                    subMenuContainer.appendChild(checkbox);
                    subMenuContainer.appendChild(label);

                    subMenuContainer.addEventListener('click', function() {
                        checkbox.checked = !checkbox.checked;
                        subMenuContainer.classList.toggle('active', checkbox
                            .checked); // Cambia el estado y el estilo al hacer clic
                        actualizarConteoSubmodulos(accordionButton, modulo.nombre,
                            accordionBody);
                    });

                    accordionBody.appendChild(subMenuContainer);
                });

                accordionCollapse.appendChild(accordionBody);
                accordionItem.appendChild(accordionCollapse);
                accordion.appendChild(accordionItem);
            });

            container.appendChild(accordion);
        },
        error: function(xhr, status, error) {
            console.error("Error: " + error);
            alert('No se pudieron cargar los menús. PRIMARIO Error: ' + error);
        }
    });
}


function cargarMenuSecundario() {
    $.ajax({
        url: 'rescatando/rescatandomenu2.php',
        type: 'GET',
        dataType: 'json',
        success: function(menu2s) {
            var container = document.getElementById('menuSecundario');
            container.innerHTML = ''; // Limpia el contenedor

            menu2s.forEach(function(menu2) {
                var checkboxContainer = document.createElement('div');
                checkboxContainer.className = 'menu-secundario-item';

                var checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.id = 'menu_' + menu2.id;
                checkbox.name = 'menu_' + menu2.id;
                checkbox.className = 'd-none'; // La clase 'd-none' oculta visualmente el elemento

                // El cambio de estado se maneja al hacer clic en el contenedor
                checkboxContainer.addEventListener('click', function() {
                    // Cambia el estado del checkbox
                    checkbox.checked = !checkbox.checked;
                    // Alternar la clase 'active' basada en el estado del checkbox
                    checkboxContainer.classList.toggle('active', checkbox.checked);
                });

                var label = document.createElement('label');
                label.htmlFor = 'menu_' + menu2.id;
                label.textContent = menu2.nombre
                    .toUpperCase(); // Usa toUpperCase() si quieres el nombre en mayúsculas

                checkboxContainer.appendChild(
                    checkbox
                    ); // El checkbox oculto sigue siendo parte del DOM para su funcionalidad
                checkboxContainer.appendChild(label);

                container.appendChild(checkboxContainer);
            });
        },
        error: function(xhr, status, error) {
            console.log("Error: " + error);
            console.log("Status: " + status);
            console.log(xhr.responseText);
            alert('No se pudieron cargar los menús SECUNDARIOS. Error: ' + error);
        }
    });
}


function usuariopermisos() {
    Swal.fire({
        title: '<div class="alert alert-dark" role="alert">ASIGNAR PERMISOS</div>',
        html: `
                    <ul class="nav nav-tabs" id="myTab" role="tablist" style="margin-bottom: 20px; width: 100%; display: flex; justify-content: space-around;background: white;">
                        <li class="nav-item"  role="presentation">
                            <button class="nav-link active" id="tab-primario"   style ="color:black"    type="button">Menú Primario</button>
                        </li>
                        <li class="nav-item"  role="presentation">
                            <button class="nav-link"        id="tab-secundario"  style ="color:black"    type="button">Menú Secundario</button>
                        </li>
                    </ul>
                    
                    
                    <div id="contenidoModal">
                        <div class="pestaña1" style="display: block;"> <!-- Cambiado a 'block' para ser visible por defecto -->
                            <div style="display: flex; flex-wrap: wrap;" id="menuPrimario"></div>
                        </div>
               
                        <div class="pestaña2" style="display: none;">
                            <div style="display: flex; flex-wrap: wrap;" id="menuSecundario"></div>
                        </div>                
                    </div>
                    `,
        width: "570px",
        confirmButtonColor: '#5B8E4A',
        cancelButtonColor: '#d33',
        confirmButtonText: 'MODIFICAR PERMISOS',
        cancelButtonText: 'CANCELAR',
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_activar_alumno',
            cancelButton: 'bt_activar_alumno'
        },
        didOpen: () => {
            cargarMenuPrimario_candados
                (); // Llama a la función existente para cargar el menú primario en la pestaña1
            cargarMenuSecundario();
            // cargarMenuPrimario();

            // Manejador para la pestaña de Menú Primario
            document.getElementById('tab-primario').addEventListener('click', function() {
                document.querySelector('.pestaña1').style.display = 'block';
                document.querySelector('.pestaña2').style.display = 'none';
                this.classList.add('active');
                document.getElementById('tab-secundario').classList.remove('active');
            });

            // Manejador para la pestaña de Menú Secundario
            document.getElementById('tab-secundario').addEventListener('click', function() {
                document.querySelector('.pestaña1').style.display = 'none';
                document.querySelector('.pestaña2').style.display = 'block';
                this.classList.add('active');
                document.getElementById('tab-primario').classList.remove('active');
            });
        },
        preConfirm: () => {
            // Aquí puedes agregar la lógica para manejar la acción de guardar
        }
    });
}


function cargarMenuPrimario_candados() {
    $.ajax({
        url: 'rescatando/rescatandomenu1_candados.php', // Asegúrate de que esta URL sea correcta
        type: 'GET',
        dataType: 'json',
        success: function(modulos) {
            var container = document.querySelector('.pestaña1');
            container.innerHTML = ''; // Limpia el contenedor antes de añadir nuevo contenido

            var accordion = document.createElement('div');
            accordion.className = 'accordion';
            accordion.id = 'myAccordion';

            modulos.forEach(function(modulo, index) {
                var accordionItem = document.createElement('div');
                accordionItem.className = 'accordion-item';
                accordionItem.style.border = '2px solid red !important;';

                var accordionHeader = document.createElement('h2');
                accordionHeader.className = 'accordion-header';
                accordionHeader.id = 'heading' + index;

                var accordionButton = document.createElement('button');
                accordionButton.className = 'accordion-button collapsed';
                accordionButton.type = 'button';
                accordionButton.setAttribute('data-bs-toggle', 'collapse');
                accordionButton.setAttribute('data-bs-target', '#collapse' + index);
                accordionButton.setAttribute('aria-expanded', 'false');
                accordionButton.setAttribute('aria-controls', 'collapse' + index);
                actualizarTextoBoton(accordionButton, modulo.nombre, 0, modulo.submodulos.length);

                accordionHeader.appendChild(accordionButton);
                accordionItem.appendChild(accordionHeader);

                var accordionCollapse = document.createElement('div');
                accordionCollapse.id = 'collapse' + index;
                accordionCollapse.className = 'accordion-collapse collapse';
                accordionCollapse.setAttribute('aria-labelledby', 'heading' + index);

                var accordionBody = document.createElement('div');
                accordionBody.className = 'accordion-body';
                accordionBody.style.display = 'grid';
                accordionBody.style.flexWrap = 'wrap'; // Corrección de 'werap' a 'wrap'
                accordionBody.style.gap = '10px'; // Corrección de 'werap' a 'wrap'

                // Aplicar estilos adicionales directamente
                accordionBody.style.gridTemplateColumns =
                    'repeat(2, 1fr)'; // Crea dos columnas de igual ancho
                accordionBody.style.padding = '20px'; // Espacio dentro de cada contenedor
                accordionBody.style.border =
                    '1px solid #ccc'; // Borde para visualizar los contenedores
                accordionBody.style.borderRadius = '5px'; // Bordes redondeados para estética
                // accordionBody.style.backgroundColor = '#ffcccc'; // Color de fondo rojo claro para los no marcados
                // La transición suave se aplica a los elementos individuales dentro del contenedor, no al contenedor en sí.

                modulo.submodulos.forEach(function(submodulo) {
                    var subMenuContainer = document.createElement('div');
                    subMenuContainer.className =
                        'menu-primario-item'; // Clase para estilos CSS
                    subMenuContainer.textContent = submodulo; // Texto del submódulo


                    var checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.id = 'menu' + submodulo
                        .id; // Asegúrate de que submodulo.id exista
                    checkbox.style.display = 'none'; // Oculta el checkbox

                    var label = document.createElement('label');
                    label.htmlFor = 'menu' + submodulo.id;
                    // Asegúrate de que submodulo.nombre sea una cadena antes de llamar a toUpperCase()
                    if (typeof submodulo.nombre === 'string') {
                        label.textContent = submodulo.nombre.toUpperCase();
                    } else {
                        console.warn('El nombre del submodulo no es una cadena:', submodulo
                            .nombre);
                        label.textContent =
                            ''; // Valor por defecto en caso de que no sea una cadena
                    }
                    subMenuContainer.appendChild(checkbox);
                    subMenuContainer.appendChild(label);

                    subMenuContainer.addEventListener('click', function() {
                        checkbox.checked = !checkbox.checked;
                        subMenuContainer.classList.toggle('active', checkbox
                            .checked); // Cambia el estado y el estilo al hacer clic
                        actualizarConteoSubmodulos(accordionButton, modulo.nombre,
                            accordionBody);
                    });

                    accordionBody.appendChild(subMenuContainer);
                });

                accordionCollapse.appendChild(accordionBody);
                accordionItem.appendChild(accordionCollapse);
                accordion.appendChild(accordionItem);
            });

            container.appendChild(accordion);
        },
        error: function(xhr, status, error) {
            console.error("Error: " + error);
            alert('No se pudieron cargar los menús. PRIMARIO Error: ' + error);
        }
    });
}


function cargarMenuSecundario() {
    $.ajax({
        url: 'rescatando/rescatandomenu2.php',
        type: 'GET',
        dataType: 'json',
        success: function(menu2s) {
            var container = document.getElementById('menuSecundario');
            container.innerHTML = ''; // Limpia el contenedor

            menu2s.forEach(function(menu2) {
                var checkboxContainer = document.createElement('div');
                checkboxContainer.className = 'menu-secundario-item';

                var checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.id = 'menu_' + menu2.id;
                checkbox.name = 'menu_' + menu2.id;
                checkbox.className = 'd-none'; // La clase 'd-none' oculta visualmente el elemento

                // El cambio de estado se maneja al hacer clic en el contenedor
                checkboxContainer.addEventListener('click', function() {
                    // Cambia el estado del checkbox
                    checkbox.checked = !checkbox.checked;
                    // Alternar la clase 'active' basada en el estado del checkbox
                    checkboxContainer.classList.toggle('active', checkbox.checked);
                });

                var label = document.createElement('label');
                label.htmlFor = 'menu_' + menu2.id;
                label.textContent = menu2.nombre
                    .toUpperCase(); // Usa toUpperCase() si quieres el nombre en mayúsculas

                checkboxContainer.appendChild(
                    checkbox
                    ); // El checkbox oculto sigue siendo parte del DOM para su funcionalidad
                checkboxContainer.appendChild(label);

                container.appendChild(checkboxContainer);
            });
        },
        error: function(xhr, status, error) {
            console.log("Error: " + error);
            console.log("Status: " + status);
            console.log(xhr.responseText);
            alert('No se pudieron cargar los menús SECUNDARIOS. Error: ' + error);
        }
    });
}



function usuariopermisos() {
    Swal.fire({
        title: '<div class="alert alert-dark" role="alert">ASIGNAR PERMISOS</div>',
        html: `
                    <ul class="nav nav-tabs" id="myTab" role="tablist" style="margin-bottom: 20px; width: 100%; display: flex; justify-content: space-around;background: white;">
                        <li class="nav-item"  role="presentation">
                            <button class="nav-link active" id="tab-primario"   style ="color:black"    type="button">Menú Primario</button>
                        </li>
                        <li class="nav-item"  role="presentation">
                            <button class="nav-link"        id="tab-secundario"  style ="color:black"    type="button">Menú Secundario</button>
                        </li>
                    </ul>
                    
                    
                    <div id="contenidoModal">
                        <div class="pestaña1" style="display: block;"> <!-- Cambiado a 'block' para ser visible por defecto -->
                            <div style="display: flex; flex-wrap: wrap;" id="menuPrimario"></div>
                        </div>
               
                        <div class="pestaña2" style="display: none;">
                            <div style="display: flex; flex-wrap: wrap;" id="menuSecundario"></div>
                        </div>                
                    </div>
                    `,
        width: "570px",
        confirmButtonColor: '#5B8E4A',
        cancelButtonColor: '#d33',
        confirmButtonText: 'MODIFICAR PERMISOS',
        cancelButtonText: 'CANCELAR',
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_activar_alumno',
            cancelButton: 'bt_activar_alumno'
        },
        didOpen: () => {
            cargarMenuPrimario_candados
                (); // Llama a la función existente para cargar el menú primario en la pestaña1
            cargarMenuSecundario();
            // cargarMenuPrimario();

            // Manejador para la pestaña de Menú Primario
            document.getElementById('tab-primario').addEventListener('click', function() {
                document.querySelector('.pestaña1').style.display = 'block';
                document.querySelector('.pestaña2').style.display = 'none';
                this.classList.add('active');
                document.getElementById('tab-secundario').classList.remove('active');
            });

            // Manejador para la pestaña de Menú Secundario
            document.getElementById('tab-secundario').addEventListener('click', function() {
                document.querySelector('.pestaña1').style.display = 'none';
                document.querySelector('.pestaña2').style.display = 'block';
                this.classList.add('active');
                document.getElementById('tab-primario').classList.remove('active');
            });
        },
        preConfirm: () => {
            // Aquí puedes agregar la lógica para manejar la acción de guardar
        }
    });
}
</script>
