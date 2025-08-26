-- Script de Actualización de Procedimientos Almacenados (Creación Rápida de Alumno)

DROP PROCEDURE IF EXISTS `sp_create_alumno_simple`;

DELIMITER $$

CREATE PROCEDURE `sp_create_alumno_simple`(
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_documento_identidad VARCHAR(20),
    IN p_telefono VARCHAR(20),
    IN p_email VARCHAR(100)
)
BEGIN
    INSERT INTO alumnos (nombres, apellidos, documento_identidad, telefono, email)
    VALUES (p_nombres, p_apellidos, p_documento_identidad, p_telefono, p_email);
    SELECT LAST_INSERT_ID() as nuevo_alumno_id;
END$$

DELIMITER ;
