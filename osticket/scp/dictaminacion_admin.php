<?php include('admin.inc.php');
$TABLE_PREFIX = "ostck_";
$nav->setTabActive('manage');

require(STAFFINC_DIR . 'header.inc.php');

$esAdmin = false;
global $esAdmin;
if ($thisstaff && $thisstaff->isAdmin()) {
	$esAdmin = true;
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
	function editar(ticket_id) {
		window.location.href = 'asignacion_dictamen.php?id=' + ticket_id + '&idEstado=3';
	}

	function irConfiguracion() {
		window.location.href = 'configuracion_dictamen.php';
	}
</script>
<div class="header-container">
	<h1>Dictaminación</h1>
	<input type="button" class="botones" onclick="irConfiguracion()" value="Configuración">
</div>

<br>
<table border="1">
	<thead>
		<tr>
			<th>Ticket</th>
			<th>Asignar a </th>
			<th>Estado</th>
			<th>Editar</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$sql = "SELECT ticket_id, number FROM " . $TABLE_PREFIX . "ticket WHERE status_id=1";
		$res = db_query($sql);

		while ($row = db_fetch_array($res)) {
			$ticket_number = $row['number'];
			$ticket_id = $row['ticket_id'];

			echo "<tr>";
			echo "<td>#" . $ticket_number . "</td>";

			$sql_estado = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion WHERE id_ticket=$ticket_id AND id_estado=1 AND (id_valoracion=1 OR id_valoracion=0)";
			$res_estado = db_query($sql_estado);
			$num_diferencia = 0;
			while ($diferencia = db_fetch_array($res_estado)) {
				if ($diferencia['id_valoracion'] == 1) {
					$num_diferencia++;
				}
			}

			//para saber si ya esta asignado
			$sql_asignacion = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion_asignaciones WHERE id_ticket=$ticket_id";
			$res_asignacion = db_query($sql_asignacion);

			$deshabilitar = 'enabled';
			if (db_num_rows($res_asignacion) >= 2) {
				switch (db_num_rows($res_estado)) {
					case 0:
						$idEstado = 1;
						$estadoAsignar = 'Asignado';
						$estadoDictamen = 'Sin Dictaminar';
						break;
					case 1:
						$idEstado = 1;
						$estadoAsignar = 'Asignado';
						$estadoDictamen = 'En proceso';
						break;
					case 2:
						if ($num_diferencia == 1 && db_num_rows($res_asignacion) == 3) {
							$idEstado = 1;
							$estadoAsignar = 'Asignado';
							$estadoDictamen = 'En proceso';
						} elseif ($num_diferencia == 1 && db_num_rows($res_asignacion) == 2) {
							$idEstado = 2;
							$estadoAsignar = 'Asignar(3ro)';
							$estadoDictamen = 'Discrepancia';
							$deshabilitar = 'disabled';
						} elseif ($num_diferencia == 2 || $num_diferencia == 0) {
							$idEstado = 1;
							$estadoAsignar = 'Asignado';
							$estadoDictamen = 'Evaluado';
							$deshabilitar = 'disabled';
						}
						break;
					case 3:
						$idEstado = 1;
						$estadoAsignar = 'Asignado';
						$estadoDictamen = 'Evaluado';
						$deshabilitar = 'disabled';
						break;
				}
			} else {
				$idEstado = 0;
				$estadoAsignar = 'Asignar';
				$estadoDictamen = 'Sin dictaminar';
				$deshabilitar = 'disabled';
			}

			echo "<td><a href='asignacion_dictamen.php?id=" . $ticket_id . "&idEstado=" . $idEstado . "'>" . $estadoAsignar . "</a></td>";
			echo "<td>$estadoDictamen</td>";
			echo "<td><input type='button' class='botones' value='Editar' onclick='editar(" . $ticket_id . ")' " . $deshabilitar . "></td>";
			echo "</tr>";
		}
		?>
	</tbody>
</table>
<?php
include(STAFFINC_DIR . 'footer.inc.php');
?>