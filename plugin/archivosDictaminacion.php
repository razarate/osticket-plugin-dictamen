<?php
class ArchivosDictaminacion
{
    private $ruta;
    private $dictaminacion;
    private $dictaminacion_admin;
    private $configuracion_admin;
    private $formulario_dictaminacion;
    private $asignacion_dictamen;
    private $libreria1;
    private $libreria2;
    private $propiedades;

    public function __construct($ruta)
    {
        $this->ruta = $ruta;
        $this->dictaminacion = "dictaminacion.php";
        $this->dictaminacion_admin = "dictaminacion_admin.php";
        $this->configuracion_admin = "configuracion_dictamen.php";
        $this->formulario_dictaminacion = "formulario_dictamen.php";
        $this->asignacion_dictamen = "asignacion_dictamen.php";
        $this->libreria1 = "jspdf.plugin.autotable.min.js";
        $this->libreria2 = "jspdf.umd.min.js";
    }

    public function copiarArchivos()
    {
        $this->cambiarPermisosDirectorio(); // Cambia permisos del directorio
        $archivos = [
            $this->dictaminacion,
            $this->dictaminacion_admin,
            $this->configuracion_admin,
            $this->formulario_dictaminacion,
            $this->asignacion_dictamen,
            $this->libreria1,
            $this->libreria2
        ];

        foreach ($archivos as $archivo) {
            $sourceFile = __DIR__ . '/' . $archivo;
            $destinationFile = $this->ruta . 'scp/' . $archivo;
            if (copy($sourceFile, $destinationFile)) {
                echo "<script> console.log('Success al copiar $sourceFile a $destinationFile')</script>";
            } else {
                echo "<script> console.log('Error al copiar $sourceFile a $destinationFile')</script>";
            }
        }
    }

    public function eliminarArchivos()
    {
        $this->cambiarPermisosDirectorio(); // Cambia permisos del directorio
        $archivos = [
            $this->dictaminacion,
            $this->dictaminacion_admin,
            $this->configuracion_admin,
            $this->formulario_dictaminacion,
            $this->asignacion_dictamen,
            $this->libreria1,
            $this->libreria2
        ];

        foreach ($archivos as $archivo) {
            $destinationFile = $this->ruta . 'scp/' . $archivo; // Ruta completa al archivo
            if (file_exists($destinationFile)) {
                unlink($destinationFile); // Elimina el archivo
            }
        }
    }

    // Cambiar permisos solo del directorio
    private function cambiarPermisosDirectorio()
    {
        if (is_dir($this->ruta)) {
            chmod($this->ruta, 0755); // Cambiar permisos solo del directorio
        }
    }
}
