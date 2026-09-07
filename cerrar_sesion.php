<?php

$sp = '/var/cpanel/php/sessions/ea-php83';
if (!is_dir($sp)) {
    session_save_path(sys_get_temp_dir());
}

require_once __DIR__ . '/clases/Session.php';

Session::destruir();

header('Location: index.php');
exit;
