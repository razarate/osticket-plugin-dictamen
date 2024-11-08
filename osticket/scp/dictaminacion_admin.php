<?php
include('admin.inc.php');
$TABLE_PREFIX = $GLOBALS['mi_prefijo_global'];
$nav->setTabActive('manage');
require_once(INCLUDE_DIR . 'class.plugin.php');

require(STAFFINC_DIR . 'header.inc.php');

if ($GLOBALS['esta_activado']) {
	$sql_status = db_query("SELECT id FROM " . $TABLE_PREFIX . "ticket_status WHERE name LIKE '%dictaminacion%'");
	if (db_num_rows($sql_status) == 1) {
		$id_status = 0;
		while ($filas_status = db_fetch_array($sql_status)) {
			$id_status = $filas_status['id'];
		}

		$esAdmin = false;
		global $esAdmin;
		if ($thisstaff && $thisstaff->isAdmin()) {
			$esAdmin = true;
		}

?>

		<style>
			.tickets {
				/*color: blue;*/
				font-weight: bold;
			}

			.filaEvaluado {
				background-color: #b8ffbb;
			}

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

			.tab-navbar {
				display: flex;
				border-bottom: 1px solid orangered;
				padding: 0;
				margin: 0;
				list-style: none;
			}

			.tab-navbar li {
				margin-right: 10px;
			}

			.tab-navbar a {
				display: block;
				padding: 10px 15px;
				color: #333;
				text-decoration: none;
				border: 1px solid transparent;
				border-bottom: none;
				background-color: white;
				border-radius: 4px 4px 0 0;
				border-color: orangered;
			}

			/* Estilo para la pestaña activa */
			.tab-navbar a.activo {
				background-color: orangered;
				border-color: orange;
				color: white;
				font-weight: bold;
				border-bottom: 1px solid orangered;
			}

			.tab-content {
				border: 1px solid orangered;
				padding: 20px;
				background-color: #fff;
			}

			.button-container {
				display: flex;
				
				/* Espacio entre los botones */
				justify-content: center;
				/* Centra los botones horizontalmente */
				align-items: center;
				/* Centra los botones verticalmente */
			}

			.image-button {
				background: url('pdf-logo.png') no-repeat center center;
				background-size: contain;
				width: 50px;
				height: 50px;
				border: none;
				cursor: pointer;
			}

			.imageW-button {
				background: url('word-logo.png') no-repeat center center;
				background-size: contain;
				width: 80px;
				height: 80px;
				border: none;
				cursor: pointer;
			}
		</style>

		<script>
			function editar(ticket_id) {
				window.location.href = 'asignacion_dictamen.php?id=' + ticket_id + '&idEstado=3';
			}

			function irConfiguracion() {
				window.location.href = 'configuracion_dictamen.php';
			}

			document.addEventListener('DOMContentLoaded', function() {
				// Mostrar el contenido de la primera pestaña
				document.querySelectorAll('.tab-navbar a').forEach(link => {
					link.addEventListener('click', function(e) {
						e.preventDefault();

						// Remover la clase "active" de todas las pestañas y ocultar contenido
						document.querySelectorAll('.tab-navbar a').forEach(tab => tab.classList.remove('activo'));
						document.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');

						// Añadir la clase "active" a la pestaña seleccionada y mostrar contenido
						this.classList.add('activo');
						const selectedTab = document.querySelector(this.getAttribute('href'));
						if (selectedTab) {
							selectedTab.style.display = 'block';
						}
					});
				});
			});
		</script>
		<?php
		$usuario = "";
		// Obtener todos los tickets con su estado
		$sql = "SELECT ticket_id, number 
        FROM " . $TABLE_PREFIX . "ticket 
        WHERE status_id=$id_status 
        ORDER BY lastupdate DESC";
		$res = db_query($sql);

		// Procesar cada ticket
		while ($row = db_fetch_array($res)) {
			$ticket_number = $row['number'];
			$ticket_id = $row['ticket_id'];

			$sql_usuario = "SELECT LEFT(u.name, 15) AS name FROM " . $TABLE_PREFIX . "ticket t JOIN " . $TABLE_PREFIX . "user u ON t.user_id = u.id WHERE t.number = '$ticket_number'";
			$result_usuario = db_query($sql_usuario);
			while ($fila_usuario = db_fetch_array($result_usuario)) {
				$usuario =  $fila_usuario['name'];
			}
			// Obtener el estado del ticket
			$sql_estado = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion 
                   WHERE id_ticket=$ticket_id 
                   AND id_estado=1 AND (id_valoracion=1 OR id_valoracion=0)";
			$res_estado = db_query($sql_estado);
			$num_diferencia = 0;
			while ($diferencia = db_fetch_array($res_estado)) {
				if ($diferencia['id_valoracion'] == 1) {
					$num_diferencia++;
				}
			}

			// Obtener asignaciones
			$sql_asignacion = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion_asignaciones WHERE id_ticket=$ticket_id";
			$res_asignacion = db_query($sql_asignacion);

			$deshabilitar = 'enabled';
			if (db_num_rows($res_asignacion) >= 2) {
				switch (db_num_rows($res_estado)) {
					case 0:
						$idEstado = 1;
						$estadoAsignar = 'CONSULTAR';
						$estadoDictamen = 'Sin Dictaminar';
						break;
					case 1:
						$idEstado = 1;
						$estadoAsignar = 'CONSULTAR';
						$estadoDictamen = 'En proceso';
						break;
					case 2:
						if ($num_diferencia == 1 && db_num_rows($res_asignacion) == 3) {
							$idEstado = 1;
							$estadoAsignar = 'CONSULTAR';
							$estadoDictamen = 'En proceso';
						} elseif ($num_diferencia == 1 && db_num_rows($res_asignacion) == 2) {
							$idEstado = 2;
							$estadoAsignar = 'Asignar(3ro)';
							$estadoDictamen = 'Discrepancia';
							$deshabilitar = 'disabled';
						} elseif ($num_diferencia == 2 || $num_diferencia == 0) {
							$idEstado = 1;
							$estadoAsignar = 'CONSULTAR';
							$estadoDictamen = 'Evaluado';
							$deshabilitar = 'disabled';
						}
						break;
					case 3:
						$idEstado = 1;
						$estadoAsignar = 'CONSULTAR';
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

			// Clasificar los tickets según el estado de dictamen

			if ($estadoDictamen === 'Evaluado') {
				$ticketData = [
					'number' => $ticket_number,
					'usuario' => $usuario,
					'asignar' => $estadoAsignar,
					'estado' => $estadoDictamen,
					'ticket_id' => $ticket_id,
					'idEstado' => $idEstado
				];
				$dictaminadosTickets[] = $ticketData;
			} else {
				$ticketData = [
					'number' => $ticket_number,
					'usuario' => $usuario,
					'asignar' => $estadoAsignar,
					'estado' => $estadoDictamen,
					'ticket_id' => $ticket_id,
					'deshabilitar' => $deshabilitar,
					'idEstado' => $idEstado
				];
				$sinDictaminarTickets[] = $ticketData;
			}
		} ?>

		<div class="header-container">
			<h1>Dictaminación</h1>
			<input type="button" class="botones" onclick="irConfiguracion()" value="Configuración">
		</div>
		<br>
		<nav>
			<ul class="tab-navbar">
				<li><a href="#tab1" class="activo">Sin Dictaminar</a></li>
				<li><a href="#tab2">Dictaminados</a></li>
			</ul>
		</nav>

		<div id="tab1" class="tab-content">
			<table border="1">
				<thead>
					<tr>
						<th>Ticket</th>
						<th>ASIGNACIÓN</th>
						<th>Estado</th>
						<th>Editar</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($sinDictaminarTickets as $ticket): ?>
						<tr>
							<td>
								<p class="tickets">#<?= $ticket['number'] ?></p><?= $ticket['usuario'] ?>
							</td>
							<td><a href="asignacion_dictamen.php?id=<?= $ticket['ticket_id'] ?>&idEstado=<?= $ticket['idEstado'] ?>"><?= $ticket['asignar'] ?></a></td>
							<td><?= $ticket['estado'] ?></td>
							<td><input type="button" class="botones" value="Editar" onclick="editar(<?= $ticket['ticket_id'] ?>)" <?= $ticket['deshabilitar'] ?>></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<div id="tab2" class="tab-content" style="display:none;">
			<table border="1">
				<thead>
					<tr>
						<th>Ticket</th>
						<th>ASIGNACIÓN</th>
						<th>Estado</th>
						<th>Exportar en </th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($dictaminadosTickets as $ticket): ?>
						<tr>
							<td>
								<p class="tickets">#<?= $ticket['number'] ?></p><?= $ticket['usuario'] ?>
							</td>
							<td><a href="asignacion_dictamen.php?id=<?= $ticket['ticket_id'] ?>&idEstado=<?= $ticket['idEstado'] ?>"><?= $ticket['asignar'] ?></a></td>
							<td><?= $ticket['estado'] ?></td>
							<td class="button-container"><button class="image-button" onclick=""></button><button class="imageW-button"></button></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
<?php
	} else {
		echo "Verifique que haya un solo estado del ticket llamado 'dictaminación' o 'En dictaminación'. 
        </br>Lo puede verificar dentro de la lista llamada 'ticket statues'";
	}
} else {
	echo "Verifique que su plugin Dictaminación Plugin se encuentre activado.";
}
include(STAFFINC_DIR . 'footer.inc.php');
?>