<?php

// Retorna un arreglo asociativo con la configuración del plugin para osTicket
return array(
    // Identificador único del plugin, usado para referenciarlo internamente
    'id' => 'osticket:dictaminacion', 

    // Versión del plugin, útil para gestión de actualizaciones y compatibilidad
    'version' => '1.0',

    // Nombre del plugin, que se mostrará en la interfaz de administración de osTicket
    'name' => 'Dictaminación Plugin',

    // Autor del plugin, normalmente quien desarrolla o mantiene el plugin
    'author' => 'Santiago Chávez',

    // Descripción breve del propósito del plugin
    'description' => 'Plugin para agregar funcionalidad de dictaminación en osTicket',

    // URL relacionada con el plugin, en este caso el repositorio en GitHub
    'url' => 'https://github.com/Santiago840/osticket-plugin-dictamen',

    // Archivo principal del plugin y la clase que contiene la lógica del plugin
    'plugin' => 'dictaminacion_plugin.php:DictaminacionPlugin',
);
