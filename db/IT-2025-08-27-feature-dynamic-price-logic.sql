DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_get_precio_by_curso_and_fecha`$$

CREATE PROCEDURE `sp_get_precio_by_curso_and_fecha`(
    IN p_id_curso INT,
    IN p_fecha DATE
)
BEGIN
    -- Este procedimiento busca el precio de un curso basado en una fecha específica.
    -- 1. Busca en `precios_cursos` un rango de fechas que contenga `p_fecha`.
    -- 2. Si hay múltiples rangos válidos, ordena por el ID (el más reciente) y toma el primero.
    -- 3. Si no se encuentra ningún precio, devuelve 0.00.

    SELECT
        COALESCE(
            (SELECT pc.precio
             FROM precios_cursos pc
             WHERE pc.id_curso = p_id_curso
               AND p_fecha BETWEEN pc.fecha_inicio AND pc.fecha_fin
             ORDER BY pc.id_precio_curso DESC
             LIMIT 1),
            0.00
        ) AS precio;
END$$

DELIMITER ;
