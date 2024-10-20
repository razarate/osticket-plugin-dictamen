<?php

class Sentencias
{
    private $db_prefijo;
    private $sql_dictaminacion;
    private $sql_dictaminacion_respuestas;
    private $sql_dictaminacion_asignaciones;
    private $sql_dictaminacion_opciones;

    public function __construct($db_prefijo)
    {
        $this->db_prefijo = $db_prefijo;
        $this->sql_dictaminacion = "CREATE TABLE IF NOT EXISTS " . $this->db_prefijo . "dictaminacion (
            id_dictaminacion INT AUTO_INCREMENT PRIMARY KEY,
            id_staff INT NOT NULL,
            id_ticket INT NOT NULL,
            id_estado INT NOT NULL,
            id_valoracion INT NOT NULL
        ) ENGINE = InnoDB";

        $this->sql_dictaminacion_respuestas = "CREATE TABLE IF NOT EXISTS " . $this->db_prefijo . "dictaminacion_respuestas (
            id_respuesta INT AUTO_INCREMENT PRIMARY KEY,
            id_staff INT NOT NULL,
            id_ticket INT NOT NULL,
            pregunta TEXT,
            pregunta_label TEXT,
            respuesta TEXT
        ) ENGINE = InnoDB";

        $this->sql_dictaminacion_asignaciones = "CREATE TABLE IF NOT EXISTS " . $this->db_prefijo . "dictaminacion_asignaciones (
            id_Asignacion INT AUTO_INCREMENT PRIMARY KEY,
            id_ticket INT NOT NULL,
            id_staff INT NOT NULL
        ) ENGINE = InnoDB";

        $this->sql_dictaminacion_opciones = "CREATE TABLE IF NOT EXISTS " . $this->db_prefijo . "dictaminacion_opciones (
            id_opcion INT AUTO_INCREMENT PRIMARY KEY,
            id_lista INT UNSIGNED NOT NULL,
            opcion_nombre VARCHAR(255),
            es_correcta TINYINT(1),
            CONSTRAINT fk_lista FOREIGN KEY (id_lista) REFERENCES " . $this->db_prefijo . "list(id)
            ON DELETE CASCADE 
        ) ENGINE = InnoDB";
    }

    public function generadorTablas()
    {
        // Execute each SQL statement
        $queries = [
            $this->sql_dictaminacion,
            $this->sql_dictaminacion_respuestas,
            $this->sql_dictaminacion_asignaciones,
            $this->sql_dictaminacion_opciones
        ];

        foreach ($queries as $query) {
            if (!db_query($query)) {
                // Handle error
                echo "Error creating table: " . db_error(); // Assuming db_error() returns the last error
            }
        }
    }
}
