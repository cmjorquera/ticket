<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

class Persona
{

    public $id;
    public $nombre;
    public $apellido_paterno;
    public $apellido_materno;
    public $fecha_nacimiento;
    public $email;
    public $telefono;
    public $sexo;
    public $cargo;
    public $id_area_trabajo;
    public $foto;
    // Constructor de la clase
    function __construct()
    {
        setlocale(LC_CTYPE, "es_ES");
        date_default_timezone_set("America/Santiago");
        setlocale(LC_TIME, 'spanish');
        return 0;
    }

    // Método para obtener todas las categorías
    public function obtenerTodasLasCategorias()
    {
        $db = new MySQL("servidor", "usuario", "password", "base_de_datos"); // Completar con los datos correctos
        $categorias = array();
        $consulta = $db->consulta("SELECT id_categoria, nombre_categoria FROM categoria_de_ticket");

        while ($row = $db->fetch_array($consulta)) {
            $categorias[] = $row;
        }

        return $categorias;
    }

    // Método para obtener los datos del usuario
    public function datosUsuario($idUsuarioSession)
    {
        $db = new MySQL("", "", "", ""); // Asegúrate de completar con los datos correctos
        $st = "SELECT * FROM usuarios WHERE id = '$idUsuarioSession'";
    
        // Ejecutar la consulta
        $consulta = $db->consulta($st);
    
        // Verificar si se encontraron resultados
        if ($db->num_rows($consulta) > 0) {
            // Obtener los datos del usuario y asignarlos a las propiedades de la clase
            $row = $db->fetch_array($consulta);
            $this->id                = $row["id"];
            $this->nombre            = $row["nombre"];
            $this->apellido_paterno   = $row["apellido_paterno"];
            $this->apellido_materno   = $row["apellido_materno"];
            $this->fecha_nacimiento   = $row["fecha_nacimiento"];
            $this->email             = $row["email"];
            $this->telefono          = $row["telefono"];
            $this->sexo              = $row["sexo"];
            $this->cargo             = $row["cargo"];
            $this->id_area_trabajo   = $row["id_area_trabajo"];
            $this->foto              = $row["foto"];
        } else {
            // Si no se encontró el usuario, establecer valores predeterminados
            $this->id                = "";
            $this->nombre            = "";
            $this->apellido_paterno   = "";
            $this->apellido_materno   = "";
            $this->fecha_nacimiento   = "";
            $this->email             = "";
            $this->telefono          = "";
            $this->sexo              = "";
            $this->cargo             = "";
            $this->id_area_trabajo   = "";
            $this->foto              = "";
        }
    
        // Limpiar la consulta
        // $db->LimpiarConsulta();
    }

    // Método para obtener todos los usuarios con ciertos IDs

    
    
    

 public function obtenerTodosLosUsuarios()
    {
        $db = new MySQL("servidor", "usuario", "password", "base_de_datos"); // Completar con los datos correctos
        $st = "SELECT id, nombre, apellido_paterno, apellido_materno FROM usuarios WHERE id IN (6, 7, 8)";
        $consulta = $db->consulta($st);

        $usuarios = [];
        if ($db->num_rows($consulta) > 0) {
            while ($row = $db->fetch_array($consulta)) {
                $usuarios[] = [
                    "id" => $row["id"],
                    "nombre" => $row["nombre"],
                    "apellido_paterno" => $row["apellido_paterno"],
                    "apellido_materno" => $row["apellido_materno"]
                ];
            }
        }

        return $usuarios;
    }
    
    
    
}