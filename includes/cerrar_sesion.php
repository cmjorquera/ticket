<?php

require_once __DIR__ . '/../clases/Session.php';

Session::destruir();

header('Location: ../index.php');
exit;
