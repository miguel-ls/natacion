DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_find_free_lanes`;

CREATE PROCEDURE `sp_find_free_lanes`(
    IN `p_id_tipo_area` INT,
    IN `p_fecha_inicio` DATE,
    IN `p_fecha_fin` DATE,
    IN `p_hora_inicio` TIME,
    IN `p_hora_fin` TIME
)
BEGIN
    SELECT
        ca.id_carril,
        ca.descripcion AS sub_area_nombre,
        pi.nombre AS area_nombre
    FROM
        carriles ca
    JOIN
        piscinas pi ON ca.id_piscina = pi.id_piscina
    WHERE
        (p_id_tipo_area = 0 OR pi.id_tipo_piscina = p_id_tipo_area)
        AND NOT EXISTS (
            -- Subconsulta para verificar si existe algún horario que se cruce
            SELECT 1
            FROM horarios h
            WHERE
                h.id_carril = ca.id_carril
                -- Conflicto de tiempo: si el rango del horario se solapa con el rango solicitado
                AND (h.hora_inicio < p_hora_fin AND h.hora_fin > p_hora_inicio)
                -- Conflicto de fecha: si el rango del horario se solapa con el rango solicitado
                AND (h.fecha_inicio <= p_fecha_fin AND h.fecha_fin >= p_fecha_inicio)
        );
END$$

DELIMITER ;
