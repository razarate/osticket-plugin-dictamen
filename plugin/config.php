<?php
require_once('sentencias.php');
class DictaminacionPluginConfig extends PluginConfig
{
    function getOptions()
    {
        return array(
            'prefijo' => new TextboxField(array(
                'label' => 'Introduzca el prefijo de tu base de datos. Regularmente es (ost_) en caso de no estar seguro contacte a su administrador',
                'configuration' => array('size' => 20, 'maxlength' => 10),
                'required' => true,
            )),
            'formulario' => new SelectionField(array(
                'label' => 'Seleccione el nombre de la lista para la valoración global de su dictamen',
                'required' => true
            ))
        );
    }

    function pre_save(&$config, &$errors)
    {
        $prefijo = $config['prefijo'];
        if ($this->crearTablas($prefijo)) {
            return true;
        } else {
            return false;
        }
    }
    function crearTablas($prefijo)
    {
        $sentencias = new Sentencias($prefijo);
        if (db_query('SELECT * FROM ' . $prefijo . 'ticket LIMIT 1')) {
            $sentencias->generadorTablas();
            return true;
        } else {
            echo "<script>alert('Parece que su prefijo no coincide con el de la base de datos.')</script>";
            return false;
        }
    }
    
}
