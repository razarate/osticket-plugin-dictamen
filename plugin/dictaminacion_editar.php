<?php
include('admin.inc.php');
$TABLE_PREFIX = $GLOBALS['mi_prefijo_global'];
$nav->setTabActive('manage');
require_once(INCLUDE_DIR . 'class.plugin.php');

require(STAFFINC_DIR . 'header.inc.php');

if (isset($_GET['id'])) {
    $ticket_id = intval($_GET['id']);
    global $ticket_id;
}

if (isset($_GET['staff'])) {
    $staff_id = intval($_GET['staff']);
    global $staff_id;
}

if ($GLOBALS['esta_activado']) {
    $sql_opcionesAsignadas = db_query("SELECT * FROM " . $TABLE_PREFIX . "dictaminacion_opciones");
    $sql_idLista = db_query("SELECT DISTINCT id_lista FROM " . $TABLE_PREFIX . "dictaminacion_opciones WHERE es_correcta=1");

    if (db_num_rows($sql_opcionesAsignadas) > 0) {
        $row = db_fetch_array($sql_idLista); // Obtiene el primer registro de la consulta
        $idListaAsignada = $row['id_lista'];
        $sql_nombreLista = db_query("SELECT name FROM " . $TABLE_PREFIX . "list WHERE id=" . $idListaAsignada);
        $row = db_fetch_array($sql_nombreLista); // Obtiene el primer registro de la consulta
        $nombreListaAsignada = $row['name'];
    }
?>
    <style>
        /* Ajuste general de la tabla */
        table {
            margin: 0 auto;
            /* Centramos la tabla */
            border-collapse: collapse;
            /* Eliminamos espacios entre celdas */
            width: 80%;
        }

        th,
        td {
            padding: 10px;
            /* Espaciado interno */
            text-align: center;
            border: 1px solid lightsalmon;
            /* Bordes suaves */
        }

        th {
            background-color: orangered;
            /* Color de fondo de encabezados */
            font-weight: bold;
            color: white;
        }

        /* Ajuste para los botones */
        input[type="button"],
        input[type="submit"] {
            background-color: orangered;
            /* Color verde */
            color: white;
            /* Texto en blanco */
            padding: 8px 16px;
            /* Tamaño moderado */
            font-size: 14px;
            /* Texto más pequeño */
            border: none;
            border-radius: 4px;
            cursor: pointer;
            /* Icono de mano para interacción */
            margin-left: 10px;
            /* Espacio entre botones */
        }

        input[type="submit"]:hover,
        input[type="button"]:hover {
            background-color: orange;
            /* Efecto hover */
        }

        /* Estilo para el combo box lista */
        .items {
            background-color: orangered;
            /* Color de fondo del select */
            color: white;
            /* Texto en blanco */
            font-size: 14px;
            /* Tamaño de fuente igual a los botones */
            padding: 10px 16px;
            /* Tamaño igual a los botones, un poco más grande */
            border: none;
            /* Sin bordes */
            border-radius: 4px;
            /* Bordes redondeados */
            cursor: pointer;
            /* Icono de mano para interacción */
            margin-right: 10px;
            /* Espacio entre el select y los botones */
            /* Ancho específico para igualar botones */
            height: 40px;
            /* Altura del select */
        }

        .items:active {
            background-color: orangered;
            /* Cambia a naranja cuando está en foco */
            color: white;
            /* Asegúrate de que el texto sea legible */
        }

        .items option:active {
            background-color: white;
            /* Color de fondo para opciones en foco */
        }

        /* Alineación de botones a la derecha */
        form {
            text-align: left;
            /* Alinea los botones a la derecha */
            margin-top: 20px;
            /* Espacio superior */
        }
    </style>

<?php
    echo "<h1>Eitar valoracion global de dictaminador</h1>";
    echo "<p>En este apartado podrá seleccionar el valor de la valoración global de dictaminador siempre y cuando no este en proceso el estado de dictaminación.</p>";
    echo "<form method='POST' action=''>";
    csrf_token();

    echo "<p>DICTAMINADOR:</p>";
    echo "<select class='items' id='" . $nombreListaAsignada . "' name='" . $nombreListaAsignada . "'>";
    $sql_listas = "SELECT * FROM " . $TABLE_PREFIX . "list_items WHERE list_id = $idListaAsignada ORDER BY sort";
    $opciones = db_query($sql_listas);

    while ($row = db_fetch_array($opciones)) {
        $opcion = htmlspecialchars($row['value'], ENT_QUOTES, 'UTF-8');
        $opcion_val = $row['extra'] ? htmlspecialchars($row['extra'], ENT_QUOTES, 'UTF-8') : $opcion;
        echo "<option value=\"$opcion\">" . $opcion_val . "</option>";
    }

    echo "</select></br></br>";

    // Obtener respuesta actual
    $sql_opciones = "SELECT respuesta FROM " . $TABLE_PREFIX . "dictaminacion_respuestas WHERE id_ticket=$ticket_id AND id_staff=$staff_id AND pregunta='$nombreListaAsignada'";
    $opcion_seleccionada = db_query($sql_opciones);
    if ($resultante = db_fetch_array($opcion_seleccionada)) {
        $claro = htmlspecialchars($resultante['respuesta'], ENT_QUOTES, 'UTF-8');
        echo "<script>
                document.getElementById('$nombreListaAsignada').value = '$claro';
              </script>";
    }

    // Botones Guardar y Cancelar
    echo "<input type='submit' name='guardar' value='GUARDAR'>";
    echo "<input type='button' value='VOLVER' onclick=\"window.location.href='dictaminacion_admin.php'\">";
    echo "</form>";
} else {
    echo "Verifique que su plugin Dictaminación Plugin se encuentre activado.";
}

// Manejar acción de Guardar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar'])) {

    $nueva_respuesta = htmlspecialchars($_POST[$nombreListaAsignada], ENT_QUOTES, 'UTF-8');

    // Actualizar la respuesta en dictaminacion_respuestas
    $update_sql = "UPDATE " . $TABLE_PREFIX . "dictaminacion_respuestas 
                   SET respuesta = '$nueva_respuesta' 
                   WHERE id_ticket = $ticket_id AND id_staff = $staff_id AND pregunta_label = 'Valoración Global'";

    $verificar = db_query("SELECT * FROM " . $TABLE_PREFIX . "dictaminacion WHERE id_ticket = $ticket_id AND id_staff = $staff_id");
    if (db_num_rows($verificar) != 0) {


        if (db_query($update_sql)) {
            // Buscar si la respuesta existe en dictaminacion_opciones
            $buscar_sql = "SELECT COUNT(*) AS total 
                       FROM " . $TABLE_PREFIX . "dictaminacion_opciones 
                       WHERE opcion_nombre = '$nueva_respuesta'";

            $resultado = db_query($buscar_sql);

            if ($row = db_fetch_array($resultado)) {
                $existe = $row['total'] > 0; // Verificar si existe
            } else {
                $existe = false; // Consulta fallida
            }

            // Actualizar id_valoracion en dictaminacion según el resultado
            if ($existe) {
                $actualizar_sql = "UPDATE " . $TABLE_PREFIX . "dictaminacion 
                               SET id_valoracion = 1 
                               WHERE id_staff = $staff_id AND id_ticket = $ticket_id";

                if (db_query($actualizar_sql)) {
                    echo "<script>alert('Respuesta guardada y valoración actualizada correctamente.');</script>";
                } else {
                    echo "<script>alert('Error al actualizar la valoración.');</script>";
                }
            } else {
                $actualizar_sql = "UPDATE " . $TABLE_PREFIX . "dictaminacion 
                               SET id_valoracion = 0 
                               WHERE id_staff = $staff_id AND id_ticket = $ticket_id";

                if (db_query($actualizar_sql)) {
                    echo "<script>alert('Respuesta guardada. La valoración se actualizó correctamente.');</script>";
                } else {
                    echo "<script>alert('Error al actualizar la valoración.');</script>";
                }
            }
        } else {
            echo "<script>alert('No se pudo guardar la respuesta. Intente nuevamente.');</script>";
        }
    } else {
        echo "<script>alert('No se pudo guardar la respuesta. regrese a la pagina de dictaminación admin');</script>";
    }
}


require_once(STAFFINC_DIR . 'footer.inc.php');
