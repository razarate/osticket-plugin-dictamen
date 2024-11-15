<?php
// Incluye los archivos necesarios para que el plugin funcione correctamente
require_once(INCLUDE_DIR . 'class.plugin.php');  // Archivo base de plugins en osTicket
require_once('config.php');                      // Archivo de configuración del plugin
require_once('archivosDictaminacion.php');       // Archivo que maneja operaciones con archivos
require_once('navegacion.php');                  // Archivo que maneja la navegación del plugin

/* 
NOTA: PARA PODER MANEJAR CARPETAS Y ARCHIVOS DESDE EL PLUGIN EN SU SISTEMA OSTICKET,
ES NECESARIO ASEGURAR QUE EXISTAN LOS SIGUIENTES PERMISOS:
- Cambiar el propietario de los archivos y carpetas al usuario del servidor web (www-data en sistemas basados en Linux):
  sudo chown -R www-data:www-data /ruta/al/directorio
- Asignar permisos de lectura y ejecución a las carpetas, y permisos de lectura, escritura y ejecución a los archivos:
  sudo chmod -R 755 /ruta/al/directorio
 */

class DictaminacionPlugin extends Plugin
{
    // Define la clase de configuración que usará el plugin
    var $config_class = 'DictaminacionPluginConfig';

    // Método de inicialización o bootstrap del plugin
    function bootstrap()
    {
        // Define un prefijo global que el plugin utilizará, obtenido de su configuración
        $GLOBALS['mi_prefijo_global'] = $this->obtenerPrefijo();
        // Define una variable global para activar ciertas funcionalidades del plugin
        $GLOBALS['esta_activado'] = true;
    }

    // Método que agrega elementos de navegación en osTicket
    function agregarNav()
    {
        // Crea una instancia de la clase Navegacion, que maneja la navegación del plugin
        $nav = new Navegacion();
        // Define la ruta base de inclusión
        $dirPath = INCLUDE_DIR;
        // Llama al método que agrega la navegación en el sistema osTicket
        $nav->agregarNav($dirPath);
    }

    // Método que se ejecuta cuando se habilita el plugin en osTicket
    function enable()
    {
        // Define la ruta raíz del sistema
        $dirPath = ROOT_DIR;
        // Llama al método para copiar los archivos necesarios para el plugin
        $this->copiarArchivos($dirPath);
        // Agrega elementos de navegación específicos del plugin
        $this->agregarNav();
    }

    // Método que se ejecuta cuando se deshabilita o elimina el plugin
    function eliminar()
    {
        // Muestra un mensaje en la consola del navegador para indicar que el plugin se eliminó correctamente
        echo "<script>console.log('Se eliminó correctamente')</script>";
        // Define la ruta raíz del sistema
        $dirPath = ROOT_DIR;
        // Crea una instancia de la clase ArchivosDictaminacion para manejar la eliminación de archivos
        $archivos = new ArchivosDictaminacion($dirPath);
        // Llama al método para eliminar archivos relacionados con el plugin
        $archivos->eliminarArchivos();
    }

    // Método para obtener un prefijo desde la configuración del plugin
    function obtenerPrefijo()
    {
        // Obtiene la configuración actual del plugin
        $config = $this->getConfig();
        // Obtiene el valor de la opción 'prefijo' de la configuración
        $prefijo = $config->get('prefijo');
        return $prefijo;  // Retorna el prefijo para su uso en otras partes del plugin
    }

    // Método privado para copiar archivos necesarios a una ruta específica
    private function copiarArchivos($dirPath)
    {
        // Crea una instancia de la clase ArchivosDictaminacion para manejar la copia de archivos
        $archivos = new ArchivosDictaminacion($dirPath);
        // Llama al método para copiar los archivos necesarios para el funcionamiento del plugin
        $archivos->copiarArchivos();
    }
}
