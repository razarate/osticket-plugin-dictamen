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
$opciones_almacenadas = [];
if ($idListaSeleccionada = db_fetch_array($sql_idLista)) {
    $existe_opciones = true;
    $idLista = $idListaSeleccionada['id_lista'];

    $sql_opciones_almacenadas = "SELECT *   
                                 FROM " . $TABLE_PREFIX . "dictaminacion_opciones 
                                 WHERE id_lista = " . $idLista;

    $resultado_opciones = db_query($sql_opciones_almacenadas);

    while ($opcion = db_fetch_array($resultado_opciones)) {
        $opciones_almacenadas[] = $opcion;
    }
}

?>

<script>
    function seleccionarOpcionesAlmacenadas(opcionesAlmacenadas) {
        var tabla = document.getElementById('tablaValores');
        tabla.innerHTML = ""; // Limpiar las filas de la tabla

        // Crear encabezado de la tabla
        var filaEncabezado = document.createElement('tr');
        var encabezadoOpcion = document.createElement('th');
        encabezadoOpcion.textContent = 'Opción';
        var encabezadoCorrecta = document.createElement('th');
        encabezadoCorrecta.textContent = 'Respuesta Correcta';
        filaEncabezado.appendChild(encabezadoOpcion);
        filaEncabezado.appendChild(encabezadoCorrecta);
        tabla.appendChild(filaEncabezado);

        // Iterar sobre las opciones almacenadas y mostrarlas en la tabla
        opcionesAlmacenadas.forEach(function(opcion) {
            document.getElementById('lista').value = opcion.id_lista;
            var fila = document.createElement('tr');

            // Columna de Opción (valor)
            var celdaValor = document.createElement('td');
            celdaValor.textContent = opcion.opcion_nombre;
            fila.appendChild(celdaValor);

            // Columna de Respuesta Correcta (checkbox)
            var celdaCheckbox = document.createElement('td');
            var checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'respuesta_correcta[]'; // Nombre para los checkboxes
            checkbox.value = opcion.opcion_nombre; // Asignar el valor al checkbox

            // Si es correcta, marcar el checkbox
            if (opcion.es_correcta == 1) {
                checkbox.checked = true;
            }

            celdaCheckbox.appendChild(checkbox);
            fila.appendChild(celdaCheckbox);

            // Agregar la fila a la tabla
            tabla.appendChild(fila);
        });
    }


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
        valoresArray.forEach(function(valor, index) {
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

    function volver() {
        window.location.href = 'dictaminacion_admin.php';
    }
</script>

<h1>Configuración</h1>
<p>Este apartado deberá crear las opciones correspondientes para la valoración general de la dictaminación y cuáles
    deberán ser las que son correctas para el dictamen.</p>

<div id="formulario">
    <label for="lista" class="titulo">Nombre de la lista: </label>
    <form action="configuracion_dictamen.php" id="configForm" class="dynamic-form" method="post" onsubmit="validarSeleccion(event)">
        <?php csrf_token(); ?>
        <select name="lista" id="lista">
            <?php
            $resultado_listas = db_query($sql_listas);

            while ($listas = db_fetch_array($resultado_listas)) {
                $nombre_lista = $listas['lista_nombre'];
                $id_lista = $listas['lista_id'];

                $sql_valores_lista = "SELECT value FROM " . $TABLE_PREFIX . "list_items WHERE list_id = " . $id_lista;
                $resultado_valores = db_query($sql_valores_lista);

                $valores = [];
                while ($elemento = db_fetch_array($resultado_valores)) {
                    $valores[] = $elemento['value'];
                }
                $valores_concatenados = implode(',', $valores);

                echo "<option value='$id_lista' data-valores='$valores_concatenados'>" . $nombre_lista . "</option>";
            }
            ?>
        </select>
        <?php
        if ($existe_opciones) {
            echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            seleccionarOpcionesAlmacenadas(" . json_encode($opciones_almacenadas) . ");
        });
    </script>";
        }
        ?>

        <input type="button" value="Seleccionar" onclick="seleccionarLista()">
        <table id="tablaValores" border="1" style="margin-top: 20px;"></table>
        <br>
        <input type="submit" value="Guardar">
        <input type="button" value="Cancelar" onclick="volver()">
    </form>
</div>