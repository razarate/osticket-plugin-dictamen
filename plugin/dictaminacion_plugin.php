<?php
require_once(INCLUDE_DIR . 'class.plugin.php');
require_once('config.php');
require_once('archivosDictaminacion.php');
require_once('navegacion.php');

class DictaminacionPlugin extends Plugin
{
    var $config_class = 'DictaminacionPluginConfig';

    function bootstrap()
    {
        $GLOBALS['mi_prefijo_global'] = $this->obtenerPrefijo();
    }

    function agregarNav(){
        $nav = new Navegacion();
        $dirPath = INCLUDE_DIR;
        $nav->agregarNav($dirPath);
    }
  

    function enable()
    {
        $dirPath = ROOT_DIR . 'scp/';
        $this->copiarArchivos($dirPath);
        $this->agregarNav();
    }

    function eliminar() // Asegúrate de que este método esté correctamente definido
    {
        echo "<script>console.log('Se eliminó correctamente')</script>";
        $dirPath = ROOT_DIR . 'scp/';
        $archivos = new ArchivosDictaminacion($dirPath);
        $archivos->eliminarArchivos(); // Llama al método para eliminar los archivos
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


