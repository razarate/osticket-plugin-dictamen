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
    print_r($_POST);
    if (isset($_POST['lista']) && isset($_POST['respuesta_correcta'])) {
        $id_lista = intval($_POST['lista']);
        $respuestas_correctas = $_POST['respuesta_correcta']; // Solo las respuestas seleccionadas

        // Obtener todas las posibles opciones de la base de datos
        $sql_opciones = "SELECT opcion_nombre FROM " . $TABLE_PREFIX . "dictaminacion_opciones WHERE id_lista = $id_lista";
        $result_opciones = db_query($sql_opciones);

        // Crear un array para almacenar todas las opciones existentes
        $todas_opciones = [];
        while ($row = db_fetch_array($result_opciones)) {
            $todas_opciones[] = $row['opcion_nombre'];
        }

        // Si existen opciones, proceder a actualizar o eliminar
        if (isset($existe_opciones) && $existe_opciones) {
            // Actualizar las opciones existentes
            foreach ($respuestas_correctas as $opcion_nombre) {
                if (!in_array($opcion_nombre, $todas_opciones)) {
                    // Si no existe, insertamos una nueva opción
                    $sql_insertar = "INSERT INTO " . $TABLE_PREFIX . "dictaminacion_opciones (id_lista, opcion_nombre, es_correcta) 
                                     VALUES ($id_lista, '$opcion_nombre', 1)";
                    db_query($sql_insertar);
                } else {
                    // Si ya existe, actualizamos el estado de es_correcta
                    $sql_actualizar = "UPDATE " . $TABLE_PREFIX . "dictaminacion_opciones 
                                       SET es_correcta = 1 
                                       WHERE id_lista = $id_lista AND opcion_nombre = '$opcion_nombre'";
                    db_query($sql_actualizar);
                }
            }

            // Eliminar las opciones que no están en las respuestas correctas
            $sql_eliminar = "DELETE FROM " . $TABLE_PREFIX . "dictaminacion_opciones 
                             WHERE id_lista = $id_lista AND opcion_nombre NOT IN ('" . implode("', '", $respuestas_correctas) . "')";
            db_query($sql_eliminar);
        } else {
            // Si no existen opciones, insertar todas las respuestas correctas
            foreach ($respuestas_correctas as $opcion_nombre) {
                $sql_insertar = "INSERT INTO " . $TABLE_PREFIX . "dictaminacion_opciones (id_lista, opcion_nombre, es_correcta) 
                                 VALUES ($id_lista, '$opcion_nombre', 1)";
                db_query($sql_insertar);
            }
        }

        // Guardar la lista y las opciones seleccionadas para mostrarlas
        $lista_seleccionada = $id_lista;
        $opciones_seleccionadas = $respuestas_correctas;
        $existe_opciones = true;
    }
}


?>

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

    function validarSeleccion(event) {
        var checkboxes = document.querySelectorAll('input[type="checkbox"][name="respuesta_correcta[]"]');
        var seleccionados = Array.from(checkboxes).filter(checkbox => checkbox.checked).length;

        // Si no hay ningún checkbox seleccionado, bloquear el envío
        if (seleccionados === 0) {
            event.preventDefault(); // Evita que el formulario se envíe
        }
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
        document.getElementById('btnGuardar').value = "Guardar";
        document.getElementById('btnGuardar').setAttribute('onclick', 'guardar()');
    }

    function guardar() {
        document.getElementById('configForm').submit(); // Enviar el formulario
    }

    function volver() {
        window.location.href = 'dictaminacion_admin.php';
    }
</script>

<h1>Configuración</h1>
<p>Este apartado deberá crear las opciones correspondientes para la valoración general de la dictaminación y cuáles
    deberán ser las que son correctas para el dictamen.</p>

<div id="formulario">
    <label for="lista" class="titulo">Nombre de la lista: </label>
    <form action="configuracion_dictamen.php" id="configForm" class="dynamic-form" method="post"
        onsubmit="validarSeleccion(event)">
        <?php csrf_token(); ?>
        <select name="lista" id="lista" onchange="seleccionarLista()" <?php echo $existe_opciones ? 'disabled' : ''; ?>>
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
                        echo "<td><input type='checkbox' name='respuesta_correcta[]' value='$nombre_opcion' " . ($checked ? "checked" : "") . " disabled></td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>

        <br>
        <input type="button" id="btnGuardar" value="<?php echo $existe_opciones ? 'Editar' : 'Guardar'; ?>" class="button" onclick="<?php echo $existe_opciones ? 'editar()' : 'guardar()'; ?>">
        <button type="button" onclick="volver()" class="button">Cancelar</button>
    </form>
</div>

<?php require(STAFFINC_DIR . 'footer.inc.php'); ?>