<?php
/* include('admin.inc.php');
// Para depurar, podemos ver si está definido $thisstaff
if (!defined('OSTSCPINC')) die('Acceso Denegado: No estás en el contexto del panel de control');
if (!$thisstaff) {
    // Muestra información de la variable para depuración
    var_dump($thisstaff); 
    die('Acceso Denegado: Usuario no autorizado o no inicializado correctamente');
} */

/* require_once(INCLUDE_DIR . 'class.plugin.php');

class DictaminacionPlugin extends Plugin {
    var $config_class = 'DictaminacionPluginConfig';

    public function bootstrap() {
        // Enganchar la nueva pestaña de navegación
        $this->addNavItem();
    }

    private function addNavItem() {
        // Solo para administradores
        if ($thisstaff && $thisstaff->isAdmin()) {
            // Agregar el ítem de navegación al menú de administración
            Navigation::addNav('admin', 'nuevo', __('Nuevo'), 'nuevo.php');
        }
    }
} */

require_once(INCLUDE_DIR . 'class.plugin.php');
require_once('config.php');
require_once('sentencias.php');
class DictaminacionPlugin extends Plugin
{
    var $config_class = 'DictaminacionPluginConfig';
    var $sentencias = 'Sentencias';
    var $activado = false;
    function bootstrap()
    {
        // Puedes añadir lógica si se necesita cuando el plugin esté activo
        $activado = true;
        if (db_query("")) {
            echo "ok";
        } else {
            echo "no";
        }
        
    }
}
