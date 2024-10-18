<?php

class DictaminacionPluginConfig extends PluginConfig {
    function getOptions() {
        return array(
            'hello_text' => new TextboxField(array(
                'label' => 'Introduce un mensaje',
                'configuration' => array('size' => 40),
                'required' => true,
            )),
        );
    }

    function pre_save(&$config, &$errors) {
        // Esto se ejecuta cuando se guarda la instancia del plugin
        $text_value = $config['hello_text']; // Captura el valor del campo de texto

        // Para enviar el valor a la consola del navegador, usamos JavaScript
        echo "<script>console.log('Hola, el valor ingresado es: " . addslashes($text_value) . "');</script>";

        return true;
    }
}
