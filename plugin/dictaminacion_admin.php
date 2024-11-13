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

			.filaDiscrepancia {
				background-color: lightcoral;
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

		<script src="FileSaver.min.js"></script>
		<script src="pizzip.min.js"></script>
		<script src="docxtemplater.min.js"></script>
		<script src="jspdf.umd.min.js"></script>
		<script src="jspdf.plugin.autotable.min.js"></script>
		<script>
			function editar(ticket_id) {
				window.location.href = 'asignacion_dictamen.php?id=' + ticket_id + '&idEstado=3';
			}

			function irConfiguracion() {
				window.location.href = 'configuracion_dictamen.php';
			}

			function irAsignacion(ticketId, idEstado) {
				var url = 'asignacion_dictamen.php?id=' + ticketId + '&idEstado=' + idEstado;
				window.location.href = url; // Redirige a la URL construida
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

			function formatHtmlText(html) {
				// Reemplaza <br> por saltos de línea
				html = html.replace(/<br\s*\/?>/gi, "\n");
				// Reemplaza <p> y </p> con saltos de línea adicionales para formar párrafos
				html = html.replace(/<\/?p[^>]*>/gi, "\n");
				// Elimina cualquier otra etiqueta HTML (si hay más)
				return html.replace(/<\/?[^>]+(>|$)/g, "");
			}

			async function generarPdf(preguntas_json, ticket_number, usuario) {
				let preguntas = preguntas_json;
				//console.log(preguntas_json);
				const {
					jsPDF
				} = window.jspdf;

				const doc = new jsPDF({
					orientation: 'horizontal',
					unit: 'mm',
					format: 'a4'
				});
				doc.setFontSize(12);

				const pageWidth = doc.internal.pageSize.getWidth();
				const pageHeight = doc.internal.pageSize.getHeight();

				const titulo1 = "BENEMÉRITA ESCUELA NORMAL VERACRUZANA ENRIQUE C. RÉBSAMEN.";
				const titulo2 = "DEPARTAMENTO DE INVESTIGACIÓN E INNOVACIÓN EDUCATIVA.";

				const titulo1Width = doc.getTextWidth(titulo1);
				const titulo2Width = doc.getTextWidth(titulo2);

				const titulo1X = (pageWidth - titulo1Width) / 2;
				const titulo2X = (pageWidth - titulo2Width) / 2;

				doc.text(titulo1, titulo1X, 20);
				doc.text(titulo2, titulo2X, 30);

				doc.setFontSize(11);
				const nombreTicket = "Evaluación del ticket #" + ticket_number;
				doc.text(nombreTicket, 15, 45);

				const nombreUsuario = "Nombre del Autor(es): " + usuario;
				doc.text(nombreUsuario, 15, 50);

				// Define the columns for the table

				var preguntaInfo = '';
				var espacio = 58;
				var espacioTabla = 65;
				var id_staff = 0;
				var numeroRespuesta = 0;
				let preguntasPorStaff = {};
				preguntas.forEach(pregunta => {
					if (id_staff != pregunta.id_staff) {
						id_staff = pregunta.id_staff;
						preguntasPorStaff[id_staff] = [];
					}
					preguntasPorStaff[pregunta.id_staff].push(pregunta);
					// Check if the response is equal to "titulo"

				});

				const columns = ["ASPECTO A EVALUAR", "VALORACIÓN"];
				Object.keys(preguntasPorStaff).forEach((id_staff, index) => {
					numeroRespuesta = index + 1;
					const rows = [];
					const preguntasStaff = preguntasPorStaff[id_staff];
					preguntasStaff.forEach(pregunta => {
						if (pregunta.pregunta == "valoracion") {
							rows.push([{
									content: pregunta.pregunta_label,
									styles: {
										halign: 'justify'
									} // Justified
								},
								{
									content: pregunta.respuesta,
									styles: {
										halign: 'center'
									} // Centered
								}
							]);
						} else {
							if (pregunta.pregunta.includes("t")) {
								// Add a header row with the pregunta_label
								rows.push([{
									content: pregunta.pregunta_label,
									colSpan: 3,
									styles: {
										halign: 'center',
										fontStyle: 'bold',
										fillColor: [200, 200, 200]
									}
								}]);
							} else if (pregunta.pregunta.includes("r")) {
								// Check if the pregunta_label contains "rec" to set the recommendation
								rows.push([{
									content: pregunta.respuesta,
									colSpan: 3,
									styles: {
										halign: 'justify', // Justify for the recommendations
										fontStyle: 'normal',
										fillColor: [240, 240, 240]
									}
								}]);
							} else if (pregunta.pregunta.includes("p")) {
								preguntaInfo = formatHtmlText(pregunta.respuesta);
							} else if (pregunta.pregunta.includes("a")) {
								// Add regular question and response along with an empty recommendation
								rows.push([{
										content: preguntaInfo,
										styles: {
											halign: 'justify'
										} // Justified
									},
									{
										content: pregunta.respuesta,
										styles: {
											halign: 'center'
										} // Centered
									}
								]);
							}
						}
					});



					// Generate the table of responses
					doc.text('Dictaminador ' + numeroRespuesta, 15, espacio);
					doc.autoTable({
						head: [columns],
						body: rows,
						startY: espacioTabla,
						styles: {
							fontSize: 11,
							cellPadding: 3,
							textColor: [0, 0, 0],
							lineWidth: 0.65,
							lineColor: [0, 0, 0]
						},
						headStyles: {
							halign: 'center',
						},
						columnStyles: {
							0: {
								halign: 'justify'
							}, // Justify "ASPECTO A EVALUAR"
							1: {
								halign: 'center'
							} // Center "VALORACIÓN"
						}
					});


					if (index < Object.keys(preguntasPorStaff).length - 1) {
						doc.addPage();
						espacio = 25;
						espacioTabla = 30;
					}
				});


				// Posición de la sección de "Lugar y fecha" y "Nombre y firma del Lector(a)"
				const sectionY = doc.autoTable.previous.finalY + 20;

				// Verifica si queda suficiente espacio en la página actual
				if (sectionY + 10 > pageHeight) { // Ajusta el valor si necesitas más espacio
					doc.addPage();
				}

				// Ancho de los espacios subrayados
				const lineWidth = 50; // Ajusta el ancho de la línea
				const spaceBetween = 20; // Espacio entre los dos campos

				// Calcula las posiciones centradas
				const centerXNombre = (pageWidth / 2) - (lineWidth + spaceBetween / 2);
				const centerXFirma = (pageWidth / 2) + (spaceBetween / 2);

				// Dibuja las líneas encima de los campos
				doc.line(centerXNombre, sectionY - 10, centerXNombre + lineWidth, sectionY - 10); // Línea para "Lugar y fecha"
				doc.line(centerXFirma, sectionY - 10, centerXFirma + lineWidth, sectionY - 10); // Línea para "Nombre y firma del Lector(a)"

				// Añade el texto para "Lugar y fecha" y "Nombre y firma del Lector(a)"
				doc.text("Lugar y fecha", centerXNombre + (lineWidth / 2), sectionY - 5, {
					align: "center"
				});
				doc.text("Nombre y firma del Lector(a)", centerXFirma + (lineWidth / 2), sectionY - 5, {
					align: "center"
				});
				doc.save('Oficio Dictamen No. Reg.' + ticket_number + '.pdf');
			}

			function generarWord(preguntas_json, ticket_number, usuario, numStaffs) {
				let preguntas = preguntas_json;
				var documento = "";
				// Cargar el archivo usando fetch
				if (numStaffs == 3) {
					documento = "documento2.docx";
				} else if (numStaffs == 2) {
					documento = "documento.docx";
				}
				fetch(documento)
					.then(response => response.blob())
					.then(blob => {
						const reader = new FileReader();

						reader.onload = function(event) {
							const zip = new PizZip(event.target.result);
							const doc = new window.docxtemplater().loadZip(zip);

							// Crear un objeto datos que contendrá el número de ticket y todas las preguntas
							const datos = {
								ticket: ticket_number,
								usuario: usuario
							};

							// Obtener la fecha actual
							const fecha = new Date();
							const opcionesMes = {
								month: 'long'
							}; // Opciones para obtener el mes en texto (e.g., "octubre")

							datos.dia = fecha.getDate();
							datos.mes = fecha.toLocaleDateString('es-ES', opcionesMes); // Obtiene el mes en texto en español
							datos.annio = fecha.getFullYear();

							// Agrupar preguntas por id_staff
							let preguntasPorStaff = {};
							preguntas.forEach(pregunta => {
								if (!preguntasPorStaff[pregunta.id_staff]) {
									preguntasPorStaff[pregunta.id_staff] = [];
								}
								preguntasPorStaff[pregunta.id_staff].push(pregunta);
							});

							// Recorrer cada grupo de preguntas por id_staff
							Object.keys(preguntasPorStaff).forEach((id_staff, index) => {
								const preguntasStaff = preguntasPorStaff[id_staff];
								const staffIndex = index + 1;
								let numRespuesta = 1;

								preguntasStaff.forEach((pregunta) => {
									if (pregunta.pregunta && pregunta.pregunta.startsWith("r")) {
										const placeholder = `r${staffIndex}.${numRespuesta}`;
										datos[placeholder] = pregunta.respuesta || "";
										numRespuesta++;
									} else if (pregunta.pregunta && pregunta.pregunta.startsWith("t")) {
										const placeholder = `t${staffIndex}.${numRespuesta}`;
										datos[placeholder] = pregunta.pregunta_label || "";
									} 
								});
							});

							// Configurar los datos en el documento
							doc.setData(datos);

							try {
								// Renderizar el documento
								doc.render();
							} catch (error) {
								console.error("Error al renderizar el documento:", error);
								if (error.properties) {
									console.error("Detalles del error de plantilla:", error.properties);
								}
								return;
							}

							// Generar el archivo de salida
							const output = doc.getZip().generate({
								type: "blob"
							});
							saveAs(output, 'Oficio Dictamen No. Reg.' + ticket_number + '.docx'); // Guardar el archivo modificado
						};

						reader.readAsBinaryString(blob);
					})
					.catch(error => {
						console.error("Error al cargar el archivo:", error);
					});
			}
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

			$sql_usuario = "SELECT LEFT(u.name, 50) AS name FROM " . $TABLE_PREFIX . "ticket t JOIN " . $TABLE_PREFIX . "user u ON t.user_id = u.id WHERE t.number = '$ticket_number'";
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
						$estadoDictamen = 'SIN DICTAMINAR';
						break;
					case 1:
						$idEstado = 1;
						$estadoAsignar = 'CONSULTAR';
						$estadoDictamen = 'EN PROCESO';
						break;
					case 2:
						if ($num_diferencia == 1 && db_num_rows($res_asignacion) == 3) {
							$idEstado = 1;
							$estadoAsignar = 'CONSULTAR';
							$estadoDictamen = 'EN PROCESO';
						} elseif ($num_diferencia == 1 && db_num_rows($res_asignacion) == 2) {
							$idEstado = 2;
							$estadoAsignar = 'ASIGNAR(3ro)';
							$estadoDictamen = 'DISCREPANCIA';
							$deshabilitar = 'disabled';
						} elseif ($num_diferencia == 2 || $num_diferencia == 0) {
							$idEstado = 1;
							$estadoAsignar = 'CONSULTAR';
							$estadoDictamen = 'EVALUADO';
							$deshabilitar = 'disabled';
						}
						break;
					case 3:
						$idEstado = 1;
						$estadoAsignar = 'CONSULTAR';
						$estadoDictamen = 'EVALUADO';
						$deshabilitar = 'disabled';
						break;
				}
			} else {
				$idEstado = 0;
				$estadoAsignar = 'ASIGNAR';
				$estadoDictamen = 'SIN DICTAMINAR';
				$deshabilitar = 'disabled';
			}

			// Clasificar los tickets según el estado de dictamen
			$ticketData = [
				'number' => $ticket_number,
				'usuario' => $usuario,
				'asignar' => $estadoAsignar,
				'estado' => $estadoDictamen,
				'ticket_id' => $ticket_id,
				'deshabilitar' => $deshabilitar,
				'idEstado' => $idEstado
			];

			if ($estadoDictamen === 'EVALUADO') {
				$dictaminadosTickets[] = $ticketData;
			} else {
				$sinDictaminarTickets[] = $ticketData;
			}
		} ?>

		<div class="header-container">
			<h1>Dictaminación</h1>
			<input type="button" class="botones" onclick="irConfiguracion()" value="CONFIGURACIÓN">
		</div>
		<br>
		<?php
		$sql_config = db_query("SELECT * FROM " . $TABLE_PREFIX . "dictaminacion_opciones");
		if (db_num_rows($sql_config) >= 1) {


		?>
			<nav>
				<ul class="tab-navbar">
					<li><a href="#tab1" class="activo">SIN DICTAMINAR</a></li>
					<li><a href="#tab2">DICTAMINADOS</a></li>
				</ul>
			</nav>

			<div id="tab1" class="tab-content">
				<?php
				if ($sinDictaminarTickets) {
				?>
					<table border="1">
						<thead>
							<tr>
								<th>TICKET</th>
								<th>ASIGNACIÓN</th>
								<th>ESTADO</th>
								<th>EDITAR</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($sinDictaminarTickets as $ticket): ?>
								<tr>
									<td>
										<p class="tickets">#<?= $ticket['number'] ?></p><?= $ticket['usuario'] ?>
									</td>
									<td><input type="button" value="<?= $ticket['asignar'] ?>" onclick="irAsignacion(<?= $ticket['ticket_id'] ?>, <?= $ticket['idEstado'] ?>)"></td>
									<?php
									if ($ticket['estado'] === 'DISCREPANCIA') {
										echo "<td class='filaDiscrepancia'>" . htmlspecialchars($ticket['estado']) . "</td>";
									} elseif ($ticket['estado'] === 'EN PROCESO') {
										echo "<td class='filaEvaluado'>" . htmlspecialchars($ticket['estado']) . "</td>";
									} else {
										echo "<td>" . htmlspecialchars($ticket['estado']) . "</td>"; // Para manejar otros posibles estados
									}
									?>

									<td><input type="button" class="botones" value="EDITAR" onclick="editar(<?= $ticket['ticket_id'] ?>)" <?= $ticket['deshabilitar'] ?>></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php
				} else {
					echo "<p>Por el momento no hay tickets en dictaminación</p>";
				} ?>
			</div>

			<div id="tab2" class="tab-content" style="display:none;">
				<?php
				if ($dictaminadosTickets) {
				?>
					<table border="1">
						<thead>
							<tr>
								<th>TICKET</th>
								<th>ASIGNACIÓN</th>
								<th>ESTADO</th>
								<th>EXPORTAR</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($dictaminadosTickets as $ticket):
								$ticket_number = $ticket['number'];
								$usuario = $ticket['usuario'];
								$ticket_id = $ticket['ticket_id'];
								$sql_form = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion_respuestas WHERE id_ticket=$ticket_id ORDER BY id_respuesta";
								$result_form = db_query($sql_form);

								$preguntas = [];
								while ($fila_preguntas = db_fetch_array($result_form)) {
									$preguntas[] = $fila_preguntas;
								}
								$preguntas_json = json_encode($preguntas);
							?>
								<tr>
									<td>
										<p class="tickets">#<?= $ticket_number ?></p><?= $usuario ?>
									</td>
									<td><input type="button" value="<?= $ticket['asignar'] ?>" onclick="irAsignacion(<?= $ticket['ticket_id'] ?>, <?= $ticket['idEstado'] ?>)"></td>
									<td><?= $ticket['estado'] ?></td>
									<td class="button-container">
										<?php
										$numStaffs = 0;
										$resultado = db_query("SELECT * FROM " . $TABLE_PREFIX . "dictaminacion_asignaciones WHERE id_ticket=$ticket_id");
										if (db_num_rows($resultado) == 3) {
											$numStaffs = 3;
										} else {
											$numStaffs = 2;
										}
										echo "<button class='imageW-button' onclick='generarWord($preguntas_json, " . json_encode($ticket_number) . "," . json_encode($usuario) . "," . $numStaffs . ")'></button>";
										echo "<button class='image-button' onclick='generarPdf($preguntas_json, " . json_encode($ticket_number) . ", " . json_encode($usuario) . ")'></button>";
										?>

									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php } else {
					echo "<p>Por el momento no hay tickets dictaminados.</p>";
				} ?>
			</div>
<?php
		} else {
			echo "<p>Seleccione la lista y sus correspondientes respuestas correctas en la opción de 'Configuración' antes de la asignación.</p>";
		}
	} else {
		echo "Verifique que haya un solo estado del ticket llamado 'dictaminación' o 'En dictaminación'. 
        </br>Lo puede verificar dentro de la lista llamada 'ticket statues.'";
	}
} else {
	echo "Verifique que su plugin Dictaminación Plugin se encuentre activado.";
}
include(STAFFINC_DIR . 'footer.inc.php');
?>