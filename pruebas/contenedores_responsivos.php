<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
 .contenedor-tickets {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 16px;
  width: 100%;
}

.tarjeta-ticket {
  padding: 16px;
  border: 1px solid #ccc;
  border-radius: 8px;
}

</style>
<body>
<div class="contenedor-tickets">
<?php
for ($i = 1; $i <= 6; $i++) {
    echo "
    <div class='tarjeta-ticket'>
        <h5>Tarjeta $i</h5>
        <p>Contenido ejemplo</p>
        <div class='barra'></div>
    </div>
    ";
}
?>
</div>


</body>
</html>