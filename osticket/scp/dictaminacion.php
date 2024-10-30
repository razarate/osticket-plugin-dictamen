<?php
include('staff.inc.php');
$TABLE_PREFIX = $GLOBALS['mi_prefijo_global'];
$nav->setTabActive('dictaminacions');
require_once(STAFFINC_DIR . 'header.inc.php');


$agent_id = $thisstaff->getId();
global $agent_id;

if ($GLOBALS['esta_activado']) {
    $sql = "SELECT t.ticket_id, t.number 
    FROM " . $TABLE_PREFIX . "ticket t 
    WHERE t.ticket_id IN (
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

    <script src="FileSaver.min.js"></script>
    <script src="pizzip.min.js"></script>
    <script src="docxtemplater.min.js"></script>
    <script src="jspdf.umd.min.js"></script>
    <script src="jspdf.plugin.autotable.min.js"></script>
    <script>
        function mostrarAlerta(error) {
            alert(error);
            window.location.href = 'dictaminacion.php';
        }

        /* async function generarPdf(preguntas_json, ticket_number) {
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

            // Define the columns for the table
            const columns = ["ASPECTO A EVALUAR", "VALORACIÓN"];
            const rows = [];

            preguntas.forEach(pregunta => {
                // Check if the response is equal to "titulo"
                if (pregunta.respuesta === "titulo") {
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
                } else {
                    // Check if the pregunta_label contains "rec" to set the recommendation
                    if (pregunta.pregunta.includes("rec")) {
                        rows.push([{
                            content: pregunta.respuesta,
                            colSpan: 3,
                            styles: {
                                halign: 'justify', // Justify for the recommendations
                                fontStyle: 'normal',
                                fillColor: [240, 240, 240]
                            }
                        }]);
                    } else {
                        // Add regular question and response along with an empty recommendation
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
                    }
                }
            });

            // Generate the table of responses
            doc.autoTable({
                head: [columns],
                body: rows,
                margin: {
                    top: 50
                },
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
                    }, // Center "VALORACIÓN"
                    2: {
                        halign: 'justify'
                    } // Justify "RECOMENDACIONES"
                }
            });

            // Add a section for "Fecha Nombre" and "Firma del Lector(a)"
            const sectionY = doc.autoTable.previous.finalY + 40; // Get the position after the table

            // Width for the underline spaces
            const lineWidth = 50; // Adjust width for underline
            const spaceBetween = 20; // Space between the two fields

            // Calculate center positions
            const centerXNombre = (pageWidth / 2) - (lineWidth + spaceBetween / 2);
            const centerXFirma = (pageWidth / 2) + (spaceBetween / 2);

            // Draw lines above the fields
            doc.line(centerXNombre, sectionY - 10, centerXNombre + lineWidth, sectionY - 10); // Line for "Lugar y fecha"
            doc.line(centerXFirma, sectionY - 10, centerXFirma + lineWidth, sectionY - 10); // Line for "Nombre y firma del Lector(a)"

            // Add text for "Lugar y fecha" and "Nombre y firma del Lector(a)"
            doc.text("Lugar y fecha", centerXNombre + (lineWidth / 2), sectionY - 5, {
                align: "center"
            }); // Align text at the center of the line
            doc.text("Nombre y firma del Lector(a)", centerXFirma + (lineWidth / 2), sectionY - 5, {
                align: "center"
            }); // Align text at the center of the line
            // Save the PDF
            doc.save('Ticket_No.' + ticket_number + '_evaluación.pdf');
        }
         */
        function generarPdf() {


            // Cargar el archivo usando fetch
            fetch('investigacion.docx')
                .then(response => response.blob())
                .then(blob => {
                    const reader = new FileReader();

                    reader.onload = function(event) {
                        // Cargar el archivo en PizZip
                        const zip = new PizZip(event.target.result);
                        const doc = new window.docxtemplater().loadZip(zip);

                        // Reemplazar marcadores con los datos
                        const data = {
                            nombre: "nombre" // Cambia este texto al que necesites
                        };
                        doc.setData({
                            nombre: "nombre"
                        });

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
                        saveAs(output, "documento_modificado.docx"); // Guardar el archivo modificado
                    };

                    reader.readAsBinaryString(blob);
                })
                .catch(error => {
                    console.error("Error al cargar el archivo:", error);
                });
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
                    <th>Ticket</th>
                    <th>Estado</th>
                    <th>Exportar</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = db_fetch_array($res)) {
                    $ticket_number = $row['number'];
                    $ticket_id = $row['ticket_id'];

                    $preguntas = [];

                    $sql_form = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion_respuestas WHERE id_ticket=$ticket_id AND id_staff = $agent_id ORDER BY id_respuesta";
                    $result_form = db_query($sql_form);

                    while ($fila_preguntas = db_fetch_array($result_form)) {
                        $preguntas[] = $fila_preguntas;
                    }
                    $preguntas_json = json_encode($preguntas);

                    $sql_estado = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion WHERE id_staff = $agent_id AND id_ticket = $ticket_id AND id_estado=1";
                    $estado = db_query($sql_estado);
                    echo "<tr>";
                    if ($ir_formulario) {
                        echo "<td><a href='formulario_dictamen.php?id=" . $ticket_id . "'>#" . $ticket_number . "</a></td>";
                    } else {
                        echo "<td><span style='color: blue; text-decoration: underline; cursor: pointer;' onclick='mostrarAlerta(\"$error\")'>#$ticket_number</span></td>";
                    }
                    if (db_num_rows($estado) == 1) {
                        echo "<td>Evaluado</td>";
                        echo "<td><input type='button' value='PDF' onclick='generarPdf($preguntas_json, $ticket_number)'></td>";
                    } elseif (db_num_rows($estado) == 0) {
                        echo "<td>Pendiente</td>";
                        echo "<td><input type='button' value='PDF' onclick='generarPdf($preguntas_json, $ticket_number)' disabled></td>";
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
    echo "Verifique que su plugin Dictaminación Plugin se encuentre activado.";
}


include(STAFFINC_DIR . 'footer.inc.php');
?>