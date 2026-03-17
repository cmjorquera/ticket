<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Equipo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      padding: 40px;
    }
    .form-container {
      max-width: 800px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .form-title {
      text-align: center;
      margin-bottom: 30px;
    }
  </style>
</head>
<body>

<div class="form-container">
  <h2 class="form-title">Registro de Equipo</h2>

  <form action="guardar_equipo.php" method="POST" enctype="multipart/form-data">

    <div class="mb-3">
      <label class="form-label">Nombre del Equipo</label>
      <input type="text" name="nombre" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Marca</label>
      <input type="text" name="marca" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Modelo</label>
      <input type="text" name="modelo" class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">Número de Serie</label>
      <input type="text" name="numero_serie" class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">Tipo de Equipo</label>
      <select name="tipo" class="form-select">
        <option>PC</option>
        <option>Notebook</option>
        <option>All-in-One</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Usuario Asignado</label>
      <input type="text" name="usuario" class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">Procesador</label>
      <input type="text" name="procesador" class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">Memoria RAM (GB)</label>
      <input type="number" name="ram" class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">Almacenamiento (GB)</label>
      <input type="number" name="almacenamiento" class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">Tipo de Almacenamiento</label>
      <select name="tipo_almacenamiento" class="form-select">
        <option>HDD</option>
        <option>SSD</option>
        <option>NVMe</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Software Instalado</label><br>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" name="software[]" value="Windows">
        <label class="form-check-label">Windows</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" name="software[]" value="Office">
        <label class="form-check-label">Office</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" name="software[]" value="Antivirus">
        <label class="form-check-label">Antivirus</label>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Observaciones</label>
      <textarea name="observaciones" class="form-control" rows="3"></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Foto del Equipo</label>
      <input type="file" name="imagen" class="form-control" accept="image/*">
    </div>

    <div class="text-center">
      <button type="submit" class="btn btn-primary">Guardar Equipo</button>
    </div>

  </form>
</div>

</body>
</html>
