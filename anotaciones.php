<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .toggle-button {
            position: fixed;
            top: 50%;
            right: 0;
            transform: translateY(-50%) rotate(0deg);
            transform-origin: center;
            transition: transform 0.3s ease-in-out;
            z-index: 1050;
            border: none;
            background-color: transparent;
        }

        .toggle-button.active {
            transform: translateY(-50%) rotate(90deg);
        }

        .bi {
            font-size: 1.5rem;
        }
        @media screen and (min-width: 1366px) and (max-width: 1366px),
screen and (min-height: 768px) and (max-height: 768px) {
    body {
        zoom: 100%;
        /*-moz-transform: scale(0.75);*/
        /*-webkit-transform: scale(0.75);*/
        /*transform: scale(0.75);*/
    }
}
    </style>
</head>
<body>
    <div class="container">
        <!-- Botón para activar el offcanvas, estilizado y posicionado -->
        <button class="btn toggle-button" id="offcanvasToggleButton">
        <i class="bi bi-lock"></i>        </button>
        
        <!-- Offcanvas a la derecha -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="demoOffcanvas" aria-labelledby="offcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasLabel">Offcanvas</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <p>Contenido del offcanvas que se puede expandir al hacer clic en el botón.</p>
            </div>
        </div>
    </div>

    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var offcanvasElement = document.getElementById('demoOffcanvas');
        var toggleButton = document.getElementById('offcanvasToggleButton');
        var offcanvas = new bootstrap.Offcanvas(offcanvasElement);

        toggleButton.addEventListener('click', function () {
            offcanvas.toggle();
        });

        offcanvasElement.addEventListener('show.bs.offcanvas', function () {
            toggleButton.innerHTML = '<i class="bi bi-x-lg"></i>';
            toggleButton.classList.add('active');
        });

        offcanvasElement.addEventListener('hide.bs.offcanvas', function () {
            toggleButton.innerHTML = '<i class="bi bi-lock"></i>';
            toggleButton.classList.remove('active');
        });
    </script>
</body>
</html>
