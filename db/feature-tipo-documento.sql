-- =================================================================
-- ARCHIVO DE SCRIPT PARA LA FUNCIONALIDAD DE TIPOS DE DOCUMENTO
-- =================================================================

--
-- 1. Creación de la nueva tabla `tipos_documento`
--
CREATE TABLE IF NOT EXISTS `tipos_documento` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `descripcion` VARCHAR(100) NOT NULL,
  `longitud` INT NOT NULL,
  `sunat` VARCHAR(3) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar algunos valores por defecto
INSERT INTO `tipos_documento` (`descripcion`, `longitud`, `sunat`) VALUES
('DNI', 8, '1'),
('RUC', 11, '6'),
('Carnet de Extranjería', 12, '4'),
('Pasaporte', 12, '7'),
('Otro', 20, '0');


--
-- 2. Modificación de la tabla `alumnos`
--
ALTER TABLE `alumnos`
ADD COLUMN `id_tipo_documento` INT NULL DEFAULT 1 AFTER `apellidos`,
ADD CONSTRAINT `fk_alumnos_tipos_documento` FOREIGN KEY (`id_tipo_documento`) REFERENCES `tipos_documento`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;


--
-- 3. Procedimientos Almacenados para el CRUD de `tipos_documento`
--

DELIMITER $$

-- Crear un nuevo tipo de documento
CREATE PROCEDURE `sp_create_tipo_documento`(
    IN p_descripcion VARCHAR(100),
    IN p_longitud INT,
    IN p_sunat VARCHAR(3)
)
BEGIN
    INSERT INTO tipos_documento (descripcion, longitud, sunat)
    VALUES (p_descripcion, p_longitud, p_sunat);
END$$

-- Obtener todos los tipos de documento
CREATE PROCEDURE `sp_get_all_tipos_documento`()
BEGIN
    SELECT id, descripcion, longitud, sunat FROM tipos_documento ORDER BY descripcion;
END$$

-- Obtener un tipo de documento por ID
CREATE PROCEDURE `sp_get_tipo_documento_by_id`(
    IN p_id INT
)
BEGIN
    SELECT id, descripcion, longitud, sunat FROM tipos_documento WHERE id = p_id;
END$$

-- Actualizar un tipo de documento
CREATE PROCEDURE `sp_update_tipo_documento`(
    IN p_id INT,
    IN p_descripcion VARCHAR(100),
    IN p_longitud INT,
    IN p_sunat VARCHAR(3)
)
BEGIN
    UPDATE tipos_documento
    SET
        descripcion = p_descripcion,
        longitud = p_longitud,
        sunat = p_sunat
    WHERE id = p_id;
END$$

-- Eliminar un tipo de documento
CREATE PROCEDURE `sp_delete_tipo_documento`(
    IN p_id INT
)
BEGIN
    DELETE FROM tipos_documento WHERE id = p_id;
END$$

DELIMITER ;


--
-- 4. Actualización de los Procedimientos Almacenados de `alumnos`
--

DELIMITER $$

-- Eliminar los procedimientos antiguos para poder recrearlos
DROP PROCEDURE IF EXISTS `sp_create_alumno`;
DROP PROCEDURE IF EXISTS `sp_update_alumno`;
DROP PROCEDURE IF EXISTS `sp_create_alumno_simple`;

-- Recrear `sp_create_alumno` con el nuevo campo
CREATE PROCEDURE `sp_create_alumno`(
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_id_tipo_documento INT,
    IN p_documento_identidad VARCHAR(20),
    IN p_fecha_nacimiento DATE,
    IN p_grupo_sanguineo VARCHAR(5),
    IN p_direccion VARCHAR(255),
    IN p_telefono VARCHAR(20),
    IN p_email VARCHAR(100),
    IN p_nombre_padre_tutor VARCHAR(200),
    IN p_telefono_emergencia VARCHAR(20)
)
BEGIN
    INSERT INTO alumnos (nombres, apellidos, id_tipo_documento, documento_identidad, fecha_nacimiento, grupo_sanguineo, direccion, telefono, email, nombre_padre_tutor, telefono_emergencia)
    VALUES (p_nombres, p_apellidos, p_id_tipo_documento, p_documento_identidad, p_fecha_nacimiento, p_grupo_sanguineo, p_direccion, p_telefono, p_email, p_nombre_padre_tutor, p_telefono_emergencia);
END$$

-- Recrear `sp_update_alumno` con el nuevo campo
CREATE PROCEDURE `sp_update_alumno`(
    IN p_id_alumno INT,
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_id_tipo_documento INT,
    IN p_documento_identidad VARCHAR(20),
    IN p_fecha_nacimiento DATE,
    IN p_grupo_sanguineo VARCHAR(5),
    IN p_direccion VARCHAR(255),
    IN p_telefono VARCHAR(20),
    IN p_email VARCHAR(100),
    IN p_nombre_padre_tutor VARCHAR(200),
    IN p_telefono_emergencia VARCHAR(20)
)
BEGIN
    UPDATE alumnos
    SET
        nombres = p_nombres,
        apellidos = p_apellidos,
        id_tipo_documento = p_id_tipo_documento,
        documento_identidad = p_documento_identidad,
        fecha_nacimiento = p_fecha_nacimiento,
        grupo_sanguineo = p_grupo_sanguineo,
        direccion = p_direccion,
        telefono = p_telefono,
        email = p_email,
        nombre_padre_tutor = p_nombre_padre_tutor,
        telefono_emergencia = p_telefono_emergencia
    WHERE id_alumno = p_id_alumno;
END$$

-- Crear `sp_create_alumno_simple` con el nuevo campo
CREATE PROCEDURE `sp_create_alumno_simple`(
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_id_tipo_documento INT,
    IN p_documento_identidad VARCHAR(20),
    IN p_telefono VARCHAR(20),
    IN p_email VARCHAR(100)
)
BEGIN
    INSERT INTO alumnos (nombres, apellidos, id_tipo_documento, documento_identidad, telefono, email)
    VALUES (p_nombres, p_apellidos, p_id_tipo_documento, p_documento_identidad, p_telefono, p_email);
    SELECT LAST_INSERT_ID() as nuevo_alumno_id;
END$$

DELIMITER ;
