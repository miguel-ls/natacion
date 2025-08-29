-- Este script actualiza el procedimiento almacenado sp_check_alumno_by_dni
-- para que acepte un ID de alumno a excluir. Esto es necesario para la funcionalidad
-- de edición de alumnos y para la validación en el formulario de matrícula.

-- Primero, eliminamos el procedimiento existente si existe.
DROP PROCEDURE IF EXISTS sp_check_alumno_by_dni;

-- Luego, creamos la nueva versión del procedimiento.
DELIMITER $$
CREATE PROCEDURE sp_check_alumno_by_dni(
    IN p_documento_identidad VARCHAR(20),
    IN p_id_alumno INT
)
BEGIN
    SELECT COUNT(*) AS count
    FROM alumnos
    WHERE documento_identidad = p_documento_identidad
    AND (p_id_alumno IS NULL OR id_alumno != p_id_alumno);
END$$
DELIMITER ;
