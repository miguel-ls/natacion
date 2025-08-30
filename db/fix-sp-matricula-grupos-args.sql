-- Corrige el procedimiento almacenado para aceptar filtros.
-- Este script debe ejecutarse en la base de datos `natacion_bd`.

DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_get_all_matricula_grupos`;

CREATE PROCEDURE `sp_get_all_matricula_grupos`(
    IN p_id_alumno INT,
    IN p_fecha_desde DATE,
    IN p_fecha_hasta DATE
)
BEGIN
    SELECT
        mg.id_grupo_matricula,
        mg.fecha_creacion,
        CONCAT(a.nombres, ' ', a.apellidos) AS nombre_cliente
    FROM matricula_grupos mg
    JOIN alumnos a ON mg.id_alumno = a.id_alumno
    WHERE
        (p_id_alumno = 0 OR mg.id_alumno = p_id_alumno)
        AND (p_fecha_desde IS NULL OR mg.fecha_creacion >= p_fecha_desde)
        AND (p_fecha_hasta IS NULL OR mg.fecha_creacion <= p_fecha_hasta)
    ORDER BY mg.fecha_creacion DESC;
END$$

DELIMITER ;
