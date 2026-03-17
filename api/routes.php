
<?php
require_once '../class/conexion.php';
require_once 'logica.php';

$bdato = new MySQL("", "", ""); 

// var_dump($_GET);
// die();
// Obtenemos el URI y el método de la solicitud
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
// $baseUri = '/ticket/api';
$baseUri = '/api';

if (strpos($uri, $baseUri) === 0) {
    $uri = substr($uri, strlen($baseUri));
}

// var_dump($uri);
$logica = new logica;

switch (true) {
    case $uri === '/':
        $logica->prueba();
        break;
    case $uri === '/obtenerMensajes':
        $id_usuario = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : null;

        if (is_null($id_usuario)) {
            echo json_encode(['error' => 'Parámetros requeridos: id_usuario']);
            break;
        }

        // Obtén los mensajes llamando al método en la clase `Logica`
        // En lugar de devolver JSON, la función ahora generará y devolverá HTML
        $logica->obtenerMensajes($id_usuario);

        // No es necesario hacer echo o json_encode aquí, ya que el HTML se genera dentro de obtenerMensajes
        break;


    case $uri === '/obtenerCantidadMensajes':
        $id_usuario = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : null;

        if (is_null($id_usuario)) {
            echo json_encode(['error' => 'Parámetros requeridos: id_usuario']);
            break;
        }
        // Obtén los mensajes llamando al método en la clase `Logica`
        $cantidadMensajes = $logica->obtenerCantidadMensajes($id_usuario);
        // Retorna los mensajes en formato JSON
        echo json_encode($cantidadMensajes);
        break;

    case $uri === '/obtenerCantidadAlertas':
        $id_usuario = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : null;

        if (is_null($id_usuario)) {
            echo json_encode(['error' => 'Parámetros requeridos: id_usuario']);
            break;
        }
        // Obtén los mensajes llamando al método en la clase `Logica`
        $cantidadAlertas = $logica->obtenerCantidadAlertas($id_usuario);
        // Retorna los mensajes en formato JSON
        echo json_encode($cantidadAlertas);
        break;
    case $uri === '/obtenerRecordatorios':
        $id_usuario = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : null;

        if (is_null($id_usuario)) {
            echo json_encode(['error' => 'Parámetros requeridos: id_usuario']);
            break;
        }

        $logica->obtenerRecordatorios($id_usuario);

        break;
    case $uri === '/mensajes-usuarios' && $method === 'GET':
        $emisor     = $_GET['emisor'] ?? null;
        $receptor   = $_GET['receptor'] ?? null;

        if ($emisor && $receptor) {
            $logica->MensajesUsuarios($emisor, $receptor);
        } else {
            echo json_encode(["success" => false, "message" => "Parámetros faltantes"]);
        }
        break;

}
