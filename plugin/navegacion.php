<?php

class Navegacion
{
    public function __construct() {}

    public function agregarNav($ruta)
    {
        // Ruta del archivo que deseas modificar
        $file_path = $ruta . 'class.nav.php';
        
        // Contenido que deseas agregar
        $staff = "\$this->tabs['dictaminacions'] = array('desc'=>__('Dictaminación'),'href'=>'dictaminacion.php','title'=>__('Dictaminacion'));";
        $admin = "\$subnav[]=array('desc'=>__('Dictaminación'),'href'=>'dictaminacion_admin.php','iconclass'=>'forms');";

        // Lee el contenido actual del archivo
        $file_contents = file($file_path);

        // Encuentra la línea donde quieres insertar el nuevo código para $staff
        $insert_position_staff = -1;
        foreach ($file_contents as $line_number => $line) {
            if (strpos($line, '$this->tabs[\'kbase\']') !== false) {
                $insert_position_staff = $line_number;
                break;
            }
        }

        // Si encontramos la posición, insertemos el contenido de $staff
        if ($insert_position_staff !== -1) {
            array_splice($file_contents, $insert_position_staff, 0, "\n    " . $staff . "\n");
        } else {
            echo "No se encontró la línea para insertar el código de staff.";
            return;
        }

        // Encuentra la línea donde quieres insertar el nuevo código para $admin
        $insert_position_admin = -1;
        foreach ($file_contents as $line_number => $line) {
            if (strpos($line, '$subnav[]=array(\'desc\'=>__(\'Plugins\')') !== false) {
                $insert_position_admin = $line_number;
                break;
            }
        }

        // Si encontramos la posición, insertemos el contenido de $admin
        if ($insert_position_admin !== -1) {
            array_splice($file_contents, $insert_position_admin, 0, "\n    " . $admin . "\n");
            
            // Escribe el contenido modificado de nuevo al archivo
            file_put_contents($file_path, implode('', $file_contents));
            echo "Código agregado exitosamente.";
        } else {
            echo "No se encontró la línea para insertar el código de admin.";
        }
    }
}
