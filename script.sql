CREATE TABLE
    IF NOT EXISTS ost_dictaminacion (
        id_dictaminacion INT AUTO_INCREMENT PRIMARY KEY,
        id_staff INT NOT NULL,
        id_ticket INT NOT NULL,
        id_estado INT NOT NULL,
        id_valoracion INT NOT NULL
    ) ENGINE = InnoDB;

CREATE TABLE
    IF NOT EXISTS ost_dictaminacion_opciones (
        id_opcion INT AUTO_INCREMENT PRIMARY KEY,
        id_lista INT UNSIGNED NOT NULL,
        opcion_nombre VARCHAR(255),
        es_correcta TINYINT (1),
        CONSTRAINT fk_lista
        FOREIGN KEY (id_lista) REFERENCES ost_list(id)  -- Relación con ost_list
        ON DELETE CASCADE 
    ) ENGINE = InnoDB;

CREATE TABLE
    IF NOT EXISTS ost_dictaminacion_asignaciones (
        id_Asignacion INT AUTO_INCREMENT PRIMARY KEY,
        id_ticket INT NOT NULL,
        id_staff INT NOT NULL
    ) ENGINE = InnoDB;

CREATE TABLE
    IF NOT EXISTS ost_dictaminacion_respuestas (
        id_respuesta INT AUTO_INCREMENT PRIMARY KEY,
        id_staff INT NOT NULL,
        id_ticket INT NOT NULL,
        pregunta TEXT,
        pregunta_label text,
        respuesta TEXT
    ) ENGINE = InnoDB;