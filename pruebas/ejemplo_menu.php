<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulación de Menú Lateral y Superior</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<style>
    body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    height: 100vh;
}

.navbar {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 100%;
    position: fixed;
    top: 0;
    z-index: 1000;
}

.sidebar {
    width: 60px;
    background-color: #f8f9fa;
    padding-top: 60px;
    position: fixed;
    height: 100%;
    transition: all 0.3s;
}

.sidebar .nav-item .nav-link {
    color: #333;
    padding: 15px;
    text-align: center;
}

.sidebar .nav-item .nav-link i {
    font-size: 20px;
}

.content {
    margin-left: 60px;
    padding: 20px;
    margin-top: 60px;
    transition: all 0.3s;
}

.navbar-brand, .nav-item.dropdown {
    margin-left: 20px;
}

.dropdown-menu {
    width: 600px;
}

.dropdown-menu .row {
    margin: 0;
}

.dropdown-menu .col {
    padding: 15px;
}

.dropdown-menu .dropdown-item {
    padding: 5px 0;
}

.dropdown-menu .badge {
    margin-left: 5px;
}

    </style>
<body>
    <!-- Menú Superior -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <button class="btn" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <a class="navbar-brand ms-3" href="#">Mega Menu</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="megaMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Mega Menu <span class="badge bg-danger">4</span>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="megaMenu">
                            <div class="row">
                                <div class="col">
                                    <h6>Overview</h6>
                                    <a class="dropdown-item" href="#">Contacts</a>
                                    <a class="dropdown-item" href="#">Incidents <span class="badge bg-danger">5</span></a>
                                    <a class="dropdown-item" href="#">Companies</a>
                                    <a class="dropdown-item" href="#">Dashboards</a>
                                </div>
                                <div class="col">
                                    <h6>Favourites</h6>
                                    <a class="dropdown-item" href="#">Reports Conversions</a>
                                    <a class="dropdown-item" href="#">Quick Start <span class="badge bg-success">NEW</span></a>
                                    <a class="dropdown-item" href="#">Users & Groups</a>
                                    <a class="dropdown-item" href="#">Properties</a>
                                </div>
                                <div class="col">
                                    <h6>Sales & Marketing</h6>
                                    <a class="dropdown-item" href="#">Queues</a>
                                    <a class="dropdown-item" href="#">Resource Groups</a>
                                    <a class="dropdown-item" href="#">Goal Metrics <span class="badge bg-warning">3</span></a>
                                    <a class="dropdown-item" href="#">Campaigns</a>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Menú Lateral -->
    <div class="sidebar" id="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-chart-pie"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-gem"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-table"></i>
                </a>
            </li>
            <!-- Más elementos aquí -->
        </ul>
    </div>

    <div class="content">
        <!-- Contenido principal -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="scripts.js"></script>

    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
    let sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed');
});

    </script>
</body>
</html>
