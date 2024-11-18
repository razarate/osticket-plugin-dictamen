<?php
class ArchivosDictaminacion
{
    private $ruta;
    private $dictaminacion;
    private $dictaminacion_admin;
    private $configuracion_admin;
    private $formulario_dictaminacion;
    private $asignacion_dictamen;
    private $dictaminacion_editar;
    private $libreria1;
    private $libreria2;
    private $libreria3;
    private $libreria4;
    private $libreria5;
    private $word;
    private $wordSegundo;
    private $logoWord;
    private $logoPDF;

    public function __construct($ruta)
    {
        $this->ruta = $ruta;
        $this->dictaminacion = "dictaminacion.php";
        $this->dictaminacion_admin = "dictaminacion_admin.php";
        $this->configuracion_admin = "configuracion_dictamen.php";
        $this->formulario_dictaminacion = "formulario_dictamen.php";
        $this->asignacion_dictamen = "asignacion_dictamen.php";
        $this->dictaminacion_editar = "dictaminacion_editar.php";
        $this->libreria1 = "jspdf.plugin.autotable.min.js";
        $this->libreria2 = "jspdf.umd.min.js";
        $this->libreria3 = "pizzip.min.js";
        $this->libreria4 = "FileSaver.min.js";
        $this->libreria5 = "docxtemplater.min.js";
        $this->word = "documento.docx";
        $this->wordSegundo = "documento2.docx";
        $this->logoWord = "word-logo.png";
        $this->logoPDF = "pdf-logo.png";
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
            $this->dictaminacion_editar,
            $this->libreria1,
            $this->libreria2,
            $this->libreria3,
            $this->libreria4,
            $this->libreria5,
            $this->word,
            $this->wordSegundo,
            $this->logoWord,
            $this->logoPDF
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
            $this->dictaminacion_editar,
            $this->libreria1,
            $this->libreria2,
            $this->libreria3,
            $this->libreria4,
            $this->libreria5,
            $this->word,
            $this->wordSegundo,
            $this->logoWord,
            $this->logoPDF
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
