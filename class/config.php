<?php
// config.php
// Define una clave secreta para el cifrado. Debe ser larga y aleatoria
define('ENCRYPTION_KEY', 'mi_clave_secreta_muy_segura_que_debe_tener_suficiente_longitud');

// Define el vector de inicialización (IV) para AES-256-CBC. Debe tener exactamente 16 bytes.
define('ENCRYPTION_IV', '1234567890123456');
?>