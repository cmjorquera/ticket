
<?php
class CodigosQR
{
    function __construct()
    {
        setlocale(LC_CTYPE, "es_ES");
        date_default_timezone_set("America/Santiago");
        setlocale(LC_TIME, 'spanish');
        return 0;
    }

    public function ObtenerCursoQR() {
        $conexion = new MySQL("", "", "");
        $sql = "SELECT id_qr, nombre_taller, fecha_taller, url_formulario, qr_path, fecha_generacion 
                FROM curso_codigos_qr  WHERE eliminado = 'no'
                ORDER BY fecha_generacion DESC";
        
        $resultado = $conexion->consulta($sql);
        
        $qrs = [];
        while ($row = $resultado->fetch_assoc()) {
            $qrs[] = $row;
        }
        
        return $qrs; // Devuelve un array con los QR generados
    }
    
    public function listarCursoQR($idCurso) {
        $conexion = new MySQL("", "", ""); // Crear conexión a la BD
    
        $idCurso = intval($idCurso); // Convertir a número entero para seguridad
        $sql = "SELECT c.*, 
                       u.nombre AS nombre_usuario, 
                       u.apellido_paterno AS apellido_paterno_usuario
                FROM curso_codigos_qr c
                LEFT JOIN usuarios u ON c.generado_por = u.id
                WHERE c.id_qr = $idCurso";
    
        $resultado = $conexion->consulta($sql);
    
        if ($conexion->num_rows($resultado) > 0) {
            return $conexion->fetch_assoc($resultado); // Retornar el primer resultado
        } else {
            return null; // Si no hay resultados, retornar null
        }
    }
    
    

}
