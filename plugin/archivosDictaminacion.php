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
            $destinationFile = $this->ruta . $archivo;
            copy($sourceFile, $destinationFile);
        }
    }

    public function eliminarArchivos()
    {
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
            $destinationFile = $this->ruta . $archivo; // Ruta completa al archivo
            if (file_exists($destinationFile)) {
                unlink($destinationFile); // Elimina el archivo
            }
            unlink($destinationFile); // Elimina el archivo
        }
    }

}
