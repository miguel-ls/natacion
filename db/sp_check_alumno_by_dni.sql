DELIMITER $$
CREATE PROCEDURE sp_check_alumno_by_dni(
    IN p_documento_identidad VARCHAR(20)
)
BEGIN
    SELECT COUNT(*) AS count
    FROM alumnos
    WHERE documento_identidad = p_documento_identidad;
END$$
DELIMITER ;
