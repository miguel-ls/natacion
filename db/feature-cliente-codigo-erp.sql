-- =================================================================
-- ARCHIVO DE SCRIPT PARA LA FUNCIONALIDAD DE CODIGO ERP EN CLIENTES
-- =================================================================

--
-- 1. Modificación de la tabla `alumnos` para añadir `codigo_erp`
--
ALTER TABLE `alumnos`
ADD COLUMN `codigo_erp` CHAR(10) NULL DEFAULT NULL AFTER `fecha_nacimiento`;


--
-- 2. Actualización de los Procedimientos Almacenados de `alumnos`
--

DELIMITER $$

-- Eliminar los procedimientos antiguos para poder recrearlos
DROP PROCEDURE IF EXISTS `sp_create_alumno`;
DROP PROCEDURE IF EXISTS `sp_update_alumno`;
DROP PROCEDURE IF EXISTS `sp_create_alumno_simple`;
DROP PROCEDURE IF EXISTS `sp_get_all_alumnos`;
DROP PROCEDURE IF EXISTS `sp_get_alumno_by_id`;

-- Recrear `sp_create_alumno` con el nuevo campo
CREATE PROCEDURE `sp_create_alumno`(
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_id_tipo_documento INT,
    IN p_documento_identidad VARCHAR(20),
    IN p_fecha_nacimiento DATE,
    IN p_codigo_erp CHAR(10),
    IN p_grupo_sanguineo VARCHAR(5),
    IN p_direccion VARCHAR(255),
    IN p_telefono VARCHAR(20),
    IN p_email VARCHAR(100),
    IN p_nombre_padre_tutor VARCHAR(200),
    IN p_telefono_emergencia VARCHAR(20)
)
BEGIN
    INSERT INTO alumnos (nombres, apellidos, id_tipo_documento, documento_identidad, fecha_nacimiento, codigo_erp, grupo_sanguineo, direccion, telefono, email, nombre_padre_tutor, telefono_emergencia)
    VALUES (p_nombres, p_apellidos, p_id_tipo_documento, p_documento_identidad, p_fecha_nacimiento, p_codigo_erp, p_grupo_sanguineo, p_direccion, p_telefono, p_email, p_nombre_padre_tutor, p_telefono_emergencia);
END$$

-- Recrear `sp_update_alumno` con el nuevo campo
CREATE PROCEDURE `sp_update_alumno`(
    IN p_id_alumno INT,
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_id_tipo_documento INT,
    IN p_documento_identidad VARCHAR(20),
    IN p_fecha_nacimiento DATE,
    IN p_codigo_erp CHAR(10),
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
        codigo_erp = p_codigo_erp,
        grupo_sanguineo = p_grupo_sanguineo,
        direccion = p_direccion,
        telefono = p_telefono,
        email = p_email,
        nombre_padre_tutor = p_nombre_padre_tutor,
        telefono_emergencia = p_telefono_emergencia
    WHERE id_alumno = p_id_alumno;
END$$

-- Recrear `sp_create_alumno_simple` (no se le añade codigo_erp por ser simple)
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

-- Recrear `sp_get_all_alumnos` con el nuevo campo
CREATE PROCEDURE `sp_get_all_alumnos`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT id_alumno, nombres, apellidos, id_tipo_documento, documento_identidad, fecha_nacimiento, codigo_erp, grupo_sanguineo, direccion, telefono, email, nombre_padre_tutor, telefono_emergencia, fecha_registro
    FROM alumnos
    WHERE p_search_term = ''
       OR nombres LIKE CONCAT('%', p_search_term, '%')
       OR apellidos LIKE CONCAT('%', p_search_term, '%')
       OR documento_identidad LIKE CONCAT('%', p_search_term, '%')
       OR codigo_erp LIKE CONCAT('%', p_search_term, '%');
END$$

-- Recrear `sp_get_alumno_by_id` con el nuevo campo
CREATE PROCEDURE `sp_get_alumno_by_id`(IN p_id_alumno INT)
BEGIN
    SELECT id_alumno, nombres, apellidos, id_tipo_documento, documento_identidad, fecha_nacimiento, codigo_erp, grupo_sanguineo, direccion, telefono, email, nombre_padre_tutor, telefono_emergencia, fecha_registro
    FROM alumnos WHERE id_alumno = p_id_alumno;
END$$

DELIMITER ;
