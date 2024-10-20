<?php
require_once(INCLUDE_DIR . 'class.plugin.php');
require_once('config.php');
require_once('archivosDictaminacion.php');

class DictaminacionPlugin extends Plugin
{
    var $config_class = 'DictaminacionPluginConfig';

    function bootstrap()
    {
            $dirPath = ROOT_DIR . 'scp/';
            $prefijo_plugin = $this->obtenerPrefijo();
            $this->copiarArchivos($dirPath);
            echo "<script>console.log('Se guardo correctamente')</script>";
    }
    
    function enable(){
        if (!$this->isActive()) { 
            echo "ok";
            echo "<script>console.log('Se eliminó correctamente')</script>";
            $dirPath = ROOT_DIR . 'scp/';
            $archivos = new ArchivosDictaminacion($dirPath);
            $archivos->eliminarArchivos(); // Llama al método para eliminar los archivos
        }
    }

    function obtenerPrefijo()
    {
        $config = $this->getConfig();
        $prefijo = $config->get('prefijo');
        return $prefijo;
    }

    private function copiarArchivos($dirPath)
    {
        $archivos = new ArchivosDictaminacion($dirPath);
        $archivos->copiarArchivos();
    }
}
