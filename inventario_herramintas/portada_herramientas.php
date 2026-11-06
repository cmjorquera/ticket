<?php
// Compatibilidad con enlaces antiguos: la portada ahora es index.php.
header('Location: index.php' . (empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING']), true, 301);
exit;
