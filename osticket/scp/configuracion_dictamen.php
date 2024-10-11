<?php
$TABLE_PREFIX = "ostck_";
$id_lista = 0;
$sql_prueba = "CREATE TABLE IF NOT EXISTS ost_dictaminacion_listas_positivas(id_positiva INT AUTO_INCREMENT PRIMARY KEY,
	id_Asignacion INT AUTO_INCREMENT PRIMARY KEY, id_ticket INT NOT NULL, id_staff INT NOT NULL) ENGINE=InnoDB;";

//$sql_listas = "SELECT * FROM " . $TABLE_PREFIX . "list";
$sql_valores_lista = "SELECT * FROM " . $TABLE_PREFIX . "list_items WHERE list_id = " . $id_lista;
/* $sql_listas = "SELECT L.name AS lista_nombre, I.value AS item_valor 
FROM " . $TABLE_PREFIX . "list L
JOIN " . $TABLE_PREFIX . "list_items I ON L.id = I.list_id"; */

$sql_listas = "SELECT L.id AS lista_id, L.name AS lista_nombre 
FROM " . $TABLE_PREFIX . "list L
JOIN " . $TABLE_PREFIX . "list_items I ON L.id = I.list_id
GROUP BY L.id, L.name";

include('admin.inc.php');

$nav->setTabActive('manage');

require(STAFFINC_DIR . 'header.inc.php');
?>

<script>
	function seleccionarLista(){
		// Obtener el elemento select
		var selectElement = document.getElementById('lista');
		// Obtener el valor seleccionado (el ID)
		var idLista = selectElement.value;
		alert('ID de la lista seleccionada: ' + idLista);
	}

	var conteo = 2;

	function agregarOpcion() {

		if (conteo < 6) {
			conteo++;
			var op = 'op' + conteo;
			var label = document.createElement('label');
			label.textContent = 'Opción ' + conteo + ': ';
			label.htmlFor = op;
			label.id = 'lb' + op;

			var input = document.createElement('input');
			input.type = 'text';
			input.id = op;

			var espacio = document.createElement('br');
			espacio.id = 'b' + op;

			var contenedor = document.getElementById('formulario');
			var lastElement = contenedor.lastElementChild;
			contenedor.insertBefore(input, lastElement.nextSibling);
			contenedor.insertBefore(label, lastElement.nextSibling);
			contenedor.insertBefore(espacio, lastElement.nextSibling);
		}
	}

	function eliminarOpcion() {
		if (conteo > 2) {
			var op = 'op' + conteo;
			var espacio = document.getElementById('b' + op);
			var elementoInput = document.getElementById(op);
			var elementoLabel = document.getElementById('lb' + op);

			elementoLabel.remove();
			elementoInput.remove();
			espacio.remove();
			/*var ultimoInput = ultimoElemento.lastElementChild.previousElementSibling;
			var ultimoLabel = ultimoInput.previousElementSibling;
			
			contenedor.removeChild(utlimoLabel);
			contenedor.removeChild(ultimoInput);*/
			conteo--;
		}
	}

	function volver() {
		window.location.href = 'dictaminacion_admin.php';
	}
</script>
<h1>Configuración</h1>
<p>Este apartado deberá crear las opciones correspondientes para la valoración general de la dictaminación y cuáles
	deberán ser las que son positivas o negativas para el dictamen.</p>
<div id="formulario-1">
	<label for="lista" class="titulo">Nombre de la lista:</label>
	</br>
	<form id="confgForm" class="dynamic-form" action="configuracion_dictamen.php" method="post">

		<select id="lista" name="lista">
			<?php
			$resultado_listas = db_query($sql_listas);

			while ($listas = db_fetch_array($resultado_listas)) {
				$nombre_lista = $listas['lista_nombre'];
				$id_lista = $listas['lista_id'];
				echo "<option id='.$id_lista.' name='$nombre_lista'>" . $nombre_lista . "</option>";
			}
			?>
		</select>
		<input type="button" value="Seleccionar" onclick="seleccionarLista()">
	</form>
	<input type="button" value="Guardar">

	<input type="button" value="Cancelar" onclick="volver()">
</div>

<?php
include(STAFFINC_DIR . 'footer.inc.php');
?>