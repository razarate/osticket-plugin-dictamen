<?php
include('staff.inc.php');
$TABLE_PREFIX = $GLOBALS['mi_prefijo_global'];
$nav->setTabActive('dictaminacions');
require_once(STAFFINC_DIR . 'header.inc.php');


$agent_id = $thisstaff->getId();
global $agent_id;

if ($GLOBALS['esta_activado']) {
    $sql_status = db_query("SELECT id FROM " . $TABLE_PREFIX . "ticket_status WHERE name LIKE '%dictaminacion%'");
    if (db_num_rows($sql_status) == 1) {
        $id_status = 0;
        while ($filas_status = db_fetch_array($sql_status)) {
            $id_status = $filas_status['id'];
        }

        $sql = "SELECT t.ticket_id, t.number 
    FROM " . $TABLE_PREFIX . "ticket t 
    WHERE t.status_id=$id_status AND t.ticket_id IN (
    SELECT da.id_ticket FROM "  . $TABLE_PREFIX . "dictaminacion_asignaciones da 
    WHERE da.id_staff ='$agent_id') ORDER BY t.lastupdate DESC";
        $res = db_query($sql);
        $sql_opcionesAsignadas = db_query("SELECT * FROM " . $TABLE_PREFIX . "dictaminacion_opciones");

        $form_titulo = 'Dictaminación';
        $sql_idForm = "SELECT * FROM " . $TABLE_PREFIX . "form WHERE title = '$form_titulo'";
        $res_formulario = db_query($sql_idForm);

        $error = '';
        $ir_formulario = false;
        //verificar si hay registros
        if (db_num_rows($sql_opcionesAsignadas) == 0) {
            $error = 'Parece que el administrador no ha configurado el formulario de dictaminación correctamente. 
    Verifique que haya seleccionado las opciones de la valoración global en el apartado de configuración';
        }

        if (db_num_rows($res_formulario) == 0) {
            $error = 'Parece que el administrador no ha configurado el formulario de dictaminación correctamente. 
    Verifique que esté nombrado como Dictaminación en el apartado de formularios';
        } elseif (db_num_rows($res_formulario) > 2) {
            $error = 'Parece que el administrador no ha configurado el formulario de dictaminación correctamente. 
    Verifique que no haya duplicidad en el nombre de Dictaminación en el apartado de formularios';
        }

        if (db_num_rows($sql_opcionesAsignadas) > 0 && db_num_rows($res_formulario) > 0) {
            $ir_formulario = true;
        } else {
            $ir_formulario = false;
        }

        function validarIrFormulario($ir_formulario, $ticket_id, $error, $nombre)
        {
            if ($ir_formulario) {
                echo "<td><input type='button' value='$nombre' 
            onclick='irFormulario(" . $ticket_id . ")'>
            </td>";
            } else {
                echo "<td><input type='button' value='$nombre' 
            onclick='mostrarAlerta(" . json_encode($error) . ")'>
            </td>";
            }
        }
?>

        <style>
            .header-container {
                display: flex;
                /* Usar flexbox para alinear elementos en una fila */
                justify-content: space-between;
                /* Distribuir espacio entre h1 y el botón */
                align-items: center;
                /* Alinear verticalmente al centro */
            }

            .tickets {
                /*color: blue;*/
                font-weight: bold;
            }

            .filaEvaluado {
                background-color: #b8ffbb;
            }

            h1 {
                margin: 0;
                /* Eliminar margen por defecto del h1 */
            }

            #btn_config {
                margin-left: auto;
                /* Empujar el botón a la derecha */
            }


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
            input[type="submit"],
            .botones {
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

            /* Alineación de botones a la derecha */
            form {
                text-align: right;
                /* Alinea los botones a la derecha */
                margin-top: 20px;
                /* Espacio superior */
            }
        </style>
        <script>
            function mostrarAlerta(error) {
                alert(error);
                window.location.href = 'dictaminacion.php';
            }

            function irFormulario(ticket_id) {
                window.location.href = 'formulario_dictamen.php?id=' + ticket_id;
            }
        </script>

        <br>
        <h1>Dictaminación</h1>
        <br>

        <?php

        if (db_num_rows($res) > 0) {
        ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>NO. DE TICKET</th>
                        <th>ESTADO</th>
                        <th>DICTAMINACIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = db_fetch_array($res)) {
                        $ticket_number = $row['number'];
                        $ticket_id = $row['ticket_id'];
                        $usuario = "";
                        $preguntas = [];

                        $sql_usuario = "SELECT u.name FROM " . $TABLE_PREFIX . "ticket t JOIN " . $TABLE_PREFIX . "user u ON t.user_id = u.id WHERE t.number = '$ticket_number'";
                        $result_usuario = db_query($sql_usuario);
                        while ($fila_usuario = db_fetch_array($result_usuario)) {
                            $usuario =  $fila_usuario['name'];
                        }

                        $sql_form = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion_respuestas WHERE id_ticket=$ticket_id AND id_staff = $agent_id ORDER BY id_respuesta";
                        $result_form = db_query($sql_form);

                        while ($fila_preguntas = db_fetch_array($result_form)) {
                            $preguntas[] = $fila_preguntas;
                        }
                        $preguntas_json = json_encode($preguntas);

                        $sql_estado = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion WHERE id_staff = $agent_id AND id_ticket = $ticket_id AND id_estado=1";
                        $estado = db_query($sql_estado);

                        if (db_num_rows($estado) == 1) {
                            echo "<tr>";
                            echo "<td><p class='tickets'>#" . $ticket_number . "</p>$usuario</td>";
                            echo "<td  class='filaEvaluado'>EVALUADO</td>";
                            validarIrFormulario($ir_formulario, $ticket_id, $error, 'CONSULTAR');
                        } elseif (db_num_rows($estado) == 0) {
                            echo "<tr>";
                            echo "<td><p class='tickets'>#" . $ticket_number . "</p>$usuario</td>";
                            echo "<td>PENDIENTE</td>";
                            validarIrFormulario($ir_formulario, $ticket_id, $error, 'DICTAMINAR');
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
<?php
        } else {
            echo "<p>No tiene tickets asignados por el momento.</p>";
        }
    } else {
        echo "Verifique que haya un solo estado del ticket llamado 'dictaminación' o 'En dictaminación'. 
        </br>Lo puede verificar dentro de la lista llamada 'ticket statues'";
    }
} else {
    echo "Verifique que su plugin Dictaminación Plugin se encuentre activado.";
}


include(STAFFINC_DIR . 'footer.inc.php');
?>