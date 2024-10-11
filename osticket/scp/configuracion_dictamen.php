<?php
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

include('admin.inc.php');

$nav->setTabActive('manage');

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

        // Insertar o actualizar solo las opciones seleccionadas
        foreach ($respuestas_correctas as $opcion_nombre) {
            // Verificar si la opción ya existe
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
    }
}


require(STAFFINC_DIR . 'header.inc.php');
?>

<script>
var idSeleccionado;
var conteo = 0;
var maxSeleccionables = 0; // Se establecerá según el número de valores

$(document).ready(function() {
    const select = document.getElementById('lista');
    idSeleccionado = select.options[0].value; // Toma el valor de la primera opción al cargar
    select.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        idSeleccionado = selectedOption.value; // Actualiza idSeleccionado cuando cambias la lista
    });
});

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

// Validar que al menos un checkbox esté seleccionado antes de enviar el formulario
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
    deberán ser las que son positivas o negativas para el dictamen.</p>
<div id="formulario">
    <label for="lista" class="titulo">Nombre de la lista:</label>
    </br>
    <form id="confgForm" class="dynamic-form" action="configuracion_dictamen.php" method="post"
        onsubmit="validarSeleccion(event)">
        <?php csrf_token(); ?>
        <select id="lista" name="lista">
            <?php
			$resultado_listas = db_query($sql_listas);

			while ($listas = db_fetch_array($resultado_listas)) {
				$nombre_lista = $listas['lista_nombre'];
				$id_lista = $listas['lista_id'];
				// Consulta los elementos de la lista seleccionada
				$sql_valores_lista = "SELECT value FROM " . $TABLE_PREFIX . "list_items WHERE list_id = " . $id_lista;
				$resultado_valores = db_query($sql_valores_lista);

				// Concatenar los valores de los elementos de la lista en una cadena
				$valores = [];
				while ($elemento = db_fetch_array($resultado_valores)) {
					$valores[] = $elemento['value'];
				}
				$valores_concatenados = implode(',', $valores); // Concatenar valores con comas

				// Generar la opción con el nombre de la lista, usando 'data-valores' para almacenar los valores
				echo "<option value='$id_lista' data-valores='$valores_concatenados'>" . $nombre_lista . "</option>";
			}
			?>
        </select>

        <input type="button" value="Seleccionar" onclick="seleccionarLista()">

        <!-- Aquí irán los checkboxes -->
        <table id="tablaValores" border="1" style="margin-top: 20px;"></table>

        <!-- Botón de Guardar -->
        <input type="submit" value="Guardar">
        <input type="button" value="Cancelar" onclick="volver()">
    </form>
</div>


<?php
include(STAFFINC_DIR . 'footer.inc.php');
?>