<?php
include('admin.inc.php');
require(STAFFINC_DIR . 'header.inc.php');
$nav->setTabActive('manage');

$TABLE_PREFIX = "ostck_";
$id_lista = 0;

$sql_prueba = "CREATE TABLE
    IF NOT EXISTS ost_dictaminacion_opciones (
        id_opcion INT AUTO_INCREMENT PRIMARY KEY,
        id_lista INT UNSIGNED NOT NULL,
        opcion_nombre VARCHAR(255),
        es_correcta TINYINT (1),
        CONSTRAINT fk_lista
        FOREIGN KEY (id_lista) REFERENCES ost_list(id)  -- Relación con ost_list
        ON DELETE CASCADE 
    ) ENGINE = InnoDB;";

$sql_listas = "SELECT L.id AS lista_id, L.name AS lista_nombre 
FROM " . $TABLE_PREFIX . "list L
JOIN " . $TABLE_PREFIX . "list_items I ON L.id = I.list_id
GROUP BY L.id, L.name";

$sql_idLista = db_query("SELECT DISTINCT id_lista FROM " . TABLE_PREFIX . "dictaminacion_opciones");
$sql_opciones = db_query("SELECT * FROM " . $TABLE_PREFIX . "dictaminacion_opciones");

$existe_opciones = false;
$idLista;
if ($idListaSeleccionada = db_fetch_array($sql_idLista)) {
    $existe_opciones = true;
    $idLista = $idListaSeleccionada['id_lista'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $existe_opciones = true;
    //print_r($_POST);
    if (isset($_POST['lista']) && isset($_POST['respuesta_correcta'])) {
        $id_lista = intval($_POST['lista']);
        $respuestas_correctas = $_POST['respuesta_correcta']; // Solo las respuestas seleccionadas

        $sql_opciones = "SELECT opcion_nombre FROM " . $TABLE_PREFIX . "dictaminacion_opciones WHERE id_lista = $id_lista";
        $result_opciones = db_query($sql_opciones);

        // Crear un array para almacenar todas las opciones existentes
        $todas_opciones = [];
        while ($row = db_fetch_array($result_opciones)) {
            $todas_opciones[] = $row['opcion_nombre'];
        }

        if ($id_lista != $idLista) {
            $sql_eliminar_todas = "DELETE FROM " . $TABLE_PREFIX . "dictaminacion_opciones WHERE id_lista= $idLista";
            db_query($sql_eliminar_todas);
            $idLista = $id_lista;
        }

        // Paso 2: Insertar las nuevas respuestas correctas
        foreach ($respuestas_correctas as $opcion_nombre) {
            if (in_array($opcion_nombre, $todas_opciones)) {

                $sql_actualizar = "UPDATE " . $TABLE_PREFIX . "dictaminacion_opciones 
            SET es_correcta = 1 
            WHERE id_lista = $id_lista AND opcion_nombre = '$opcion_nombre'";
                db_query($sql_actualizar);

                // Si no existe, insertamos una nueva opción

            } else {
                // Paso 1: Eliminar las opciones anteriores de la lista
                $sql_insertar = "INSERT INTO " . $TABLE_PREFIX . "dictaminacion_opciones (id_lista, opcion_nombre, es_correcta) 
                VALUES ($id_lista, '$opcion_nombre', 1)";
                db_query($sql_insertar);
                // Si ya existe, actualizamos el estado de es_correcta

            }
        }
        $sql_eliminar = "DELETE FROM " . $TABLE_PREFIX . "dictaminacion_opciones 
        WHERE id_lista = $id_lista AND opcion_nombre NOT IN ('" . implode("', '", $respuestas_correctas) . "')";
        db_query($sql_eliminar);
        // Guardar la lista y las opciones seleccionadas para mostrarlas
        $lista_seleccionada = $id_lista;
        $opciones_seleccionadas = $respuestas_correctas;
    }
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
        border: 1px solid #ddd;
        /* Bordes suaves */
    }

    th {
        background-color: #f2f2f2;
        /* Color de fondo de encabezados */
        font-weight: bold;
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
    #lista {
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

    #lista:active {
        background-color: orangered;
        /* Cambia a naranja cuando está en foco */
        color: white;
        /* Asegúrate de que el texto sea legible */
    }

    #lista option:active {
        background-color: white;
        /* Color de fondo para opciones en foco */
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
    var maxSeleccionables; // Variable global para el límite de checkboxes seleccionables

    function seleccionarLista() {

        // Obtener la lista seleccionada
        var select = document.getElementById('lista');
        var selectedOption = select.options[select.selectedIndex];
        var idLista = selectedOption.value; // ID de la lista seleccionada
        var valoresConcatenados = selectedOption.getAttribute('data-valores'); // Obtener los valores concatenados

        // Limpiar la tabla antes de agregar nuevas filas
        var tabla = document.getElementById('tablaValores');
        tabla.innerHTML = ""; // Limpiar las filas de la tabla

        // 'valoresConcatenados' tiene los valores como una cadena separada por comas
        var valoresArray = valoresConcatenados.split(',');
        maxSeleccionables = valoresArray.length - 1; // Permitir seleccionar n-1 checkboxes

        // Crear encabezado de la tabla
        var filaEncabezado = document.createElement('tr');
        var encabezadoOpcion = document.createElement('th');
        encabezadoOpcion.textContent = 'Opción';
        var encabezadoCorrecta = document.createElement('th');
        encabezadoCorrecta.textContent = 'Respuesta Correcta';
        filaEncabezado.appendChild(encabezadoOpcion);
        filaEncabezado.appendChild(encabezadoCorrecta);
        tabla.appendChild(filaEncabezado);

        // Iterar por cada valor y agregar una fila a la tabla
        valoresArray.forEach(function(valor) {
            var fila = document.createElement('tr');

            // Columna de Opción (valor)
            var celdaValor = document.createElement('td');
            celdaValor.textContent = valor;
            fila.appendChild(celdaValor);

            // Columna de Respuesta Correcta (checkbox)
            var celdaCheckbox = document.createElement('td');
            var checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'respuesta_correcta[]'; // Nombre para los checkboxes
            checkbox.value = valor; // Asignar el valor al checkbox

            // Añadir evento para limitar el número de checkboxes seleccionables
            checkbox.addEventListener('change', verificarSeleccion);

            celdaCheckbox.appendChild(checkbox);
            fila.appendChild(celdaCheckbox);

            // Agregar la fila a la tabla
            tabla.appendChild(fila);
        });
    }


    function verificarSeleccion() {
        // Obtener todos los checkboxes
        var checkboxes = document.querySelectorAll('input[type="checkbox"][name="respuesta_correcta[]"]');

        // Contar cuántos están seleccionados
        var seleccionados = Array.from(checkboxes).filter(checkbox => checkbox.checked).length;

        // Si el número de seleccionados alcanza el máximo permitido, deshabilitar los checkboxes no seleccionados
        checkboxes.forEach(function(checkbox) {
            if (seleccionados >= maxSeleccionables) {
                // Deshabilitar checkboxes que no están seleccionados
                if (!checkbox.checked) {
                    checkbox.disabled = true;
                }
            } else {
                // Habilitar todos los checkboxes si aún no se ha alcanzado el límite
                checkbox.disabled = false;
            }
        });
    }

    function seleccionarListaAlmacenada() {
        var checkboxe = document.getElementsByName('respuesta_correcta[]');

        // Iterar sobre todos los checkboxes y agregar el event listener
        Array.from(checkboxe).forEach(function(checkbox) {
            checkbox.addEventListener('change', verificarSeleccionAlmacenada);
        });
    }


    function verificarSeleccionAlmacenada() {
        // Obtener todos los checkboxes
        var checkboxes = document.querySelectorAll('input[type="checkbox"][name="respuesta_correcta[]"]');

        // Contar cuántos están seleccionados
        var seleccionados = Array.from(checkboxes).filter(checkbox => checkbox.checked).length;

        // Si el número de seleccionados alcanza el máximo permitido, deshabilitar los checkboxes no seleccionados
        checkboxes.forEach(function(checkbox) {
            if (seleccionados >= maxSeleccionables) {
                // Deshabilitar checkboxes que no están seleccionados
                if (checkbox.checked) {
                    checkbox.disabled = false;
                }
            }
        });
    }


    function validarGuardar() {
        var checkboxes = document.querySelectorAll('input[type="checkbox"][name="respuesta_correcta[]"]');
        var seleccionados = Array.from(checkboxes).filter(checkbox => checkbox.checked).length;

        // Si no hay ningún checkbox seleccionado, bloquear el envío
        if (seleccionados === 0) {
            alert("Por favor, seleccione al menos una opción para guardar."); // Mensaje de advertencia
            return false; // Prevenir el envío
        }
        return true; // Permitir el envío
    }

    function editar() {
        var select = document.getElementById('lista');
        select.disabled = false; // Habilitar el combobox
        var checkboxes = document.querySelectorAll('input[type="checkbox"][name="respuesta_correcta[]"]');

        // Habilitar todos los checkboxes
        checkboxes.forEach(function(checkbox) {
            checkbox.disabled = false;
        });

        // Cambiar el texto del botón a "Guardar"
        var btnGuardar = document.getElementById('btnGuardar');
        btnGuardar.value = "Guardar";
        btnGuardar.onclick = function(event) {
            guardar(event); // Asegúrate de pasar el evento aquí
        };
    }

    function guardar(event) {
        if (validarGuardar()) { // Verificar si se pueden guardar
            document.getElementById('configForm').submit(); // Enviar el formulario
        } else {
            event.preventDefault(); // Evita que el formulario se envíe si no hay seleccionados
        }
    }

    function volver() {
        window.location.href = 'dictaminacion_admin.php';
    }
</script>



<h1>Configuración</h1>
<p>Este apartado deberá seleccionar las opciones correspondientes para la valoración general de la dictaminación y cuáles
    deberán ser las que son correctas para el dictamen.</p>

<div id="formulario">
    <form action="configuracion_dictamen.php" id="configForm" class="dynamic-form" method="post"
        onsubmit="validarSeleccion(event)">
        <?php csrf_token(); ?>
        <label for="lista" class="titulo">Nombre de la lista: </label>
        <select name="lista" id="lista" onchange="seleccionarLista()" <?php echo $existe_opciones ? 'disabled' : ''; ?>><br>
            <?php
            $resultado_listas = db_query($sql_listas);

            while ($listas = db_fetch_array($resultado_listas)) {
                $nombre_lista = $listas['lista_nombre'];
                $id_lista = $listas['lista_id'];

                // Selecciona la opción si existen opciones y coincide con idLista
                $selected = ($existe_opciones && $id_lista == $idLista) ? 'selected' : '';

                // Obtén los valores para esta lista
                $sql_valores_lista = "SELECT value FROM " . $TABLE_PREFIX . "list_items WHERE list_id = " . $id_lista . " ORDER BY sort";
                $resultado_valores = db_query($sql_valores_lista);
                $valores = [];
                while ($elemento = db_fetch_array($resultado_valores)) {
                    $valores[] = $elemento['value'];
                }
                $valores_concatenados = implode(',', $valores);

                // Imprime la opción del select
                echo "<option value='$id_lista' data-valores='$valores_concatenados' $selected>" . $nombre_lista . "</option>";
            }
            ?>
        </select>

        <table id="tablaValores" border="1" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>Opción</th>
                    <th>Respuesta Correcta</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql_valores_lista = "SELECT value FROM " . $TABLE_PREFIX . "list_items WHERE list_id = " . $idLista . " ORDER BY sort";
                $resultado_valores = db_query($sql_valores_lista);
                echo "<tr>";
                // Mostrar las opciones de la lista y marcar las que coincidan con las almacenadas
                // Mostrar las opciones de la lista y marcar las que coincidan con las almacenadas
                if ($existe_opciones) {

                    $sql_opciones_almacenadas = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion_opciones WHERE id_lista = " . $idLista;
                    $resultado_opciones = db_query($sql_opciones_almacenadas);
                    $opciones_almacenadas = [];

                    // Guardar las opciones almacenadas
                    while ($opcion = db_fetch_array($resultado_opciones)) {
                        $opciones_almacenadas[] = $opcion;
                    }

                    // Mostrar las opciones de la lista y marcar las que coincidan con las almacenadas
                    while ($elemento = db_fetch_array($resultado_valores)) {
                        $nombre_opcion = $elemento['value'];
                        $checked = false;

                        // Verificar si esta opción está almacenada como correcta
                        foreach ($opciones_almacenadas as $opcion_almacenada) {
                            if ($opcion_almacenada['opcion_nombre'] == $nombre_opcion && $opcion_almacenada['es_correcta'] == 1) {
                                $checked = true;
                                break;
                            }
                        }

                        // Generar la fila de la tabla con el checkbox marcado si corresponde
                        echo "<tr>";
                        echo "<td>$nombre_opcion</td>";
                        echo "<td><input type='checkbox' nombre_opcion' id= name='respuesta_correcta[]' value='$nombre_opcion' " . ($checked ? "checked" : "") . " disabled></td>";
                        echo "<script> seleccionarListaAlmacenada(); </script>";
                        echo "</tr>";
                    }
                } else {
                    echo "<script> seleccionarLista() </script>";
                }
                ?>
            </tbody>
        </table>

        <br>
        <input type="button" id="btnGuardar" value="<?php echo $existe_opciones ? 'Editar' : 'Guardar'; ?>"
            class="button" onclick="<?php echo $existe_opciones ? 'editar()' : 'guardar()'; ?>">
        <input type="button" onclick="volver()" class="button" value="Cancelar">
    </form>
</div>

<?php require(STAFFINC_DIR . 'footer.inc.php'); ?>