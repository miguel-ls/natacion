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
