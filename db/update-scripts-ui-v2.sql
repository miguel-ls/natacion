-- =================================================================
-- ARCHIVO DE ACTUALIZACIÓN DE SCRIPT PARA MEJORAS DE UI
-- =================================================================

-- Este script debe ejecutarse en la base de datos `natacion_bd` para actualizar
-- los procedimientos almacenados necesarios para las últimas mejoras de interfaz.

-- -----------------------------------------------------------------
-- 1. Actualización de sp_get_all_profesores para incluir el tipo de profesor
-- -----------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_get_all_profesores`;
CREATE PROCEDURE `sp_get_all_profesores`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT
        p.id_profesor,
        p.nombres,
        p.apellidos,
        tp.descripcion as tipo_profesor_nombre,
        p.documento_identidad,
        p.telefono,
        p.email,
        p.direccion,
        p.fecha_contratacion,
        p.estado
    FROM profesores p
    LEFT JOIN tipos_profesor tp ON p.id_tipo_profesor = tp.id
    WHERE p_search_term = ''
       OR p.nombres LIKE CONCAT('%', p_search_term, '%')
       OR p.apellidos LIKE CONCAT('%', p_search_term, '%')
       OR p.documento_identidad LIKE CONCAT('%', p_search_term, '%');
END$$
DELIMITER ;

-- -----------------------------------------------------------------
-- 2. Actualización de sp_find_free_lanes para concatenar el nombre del carril
-- -----------------------------------------------------------------
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
        CONCAT(ca.descripcion, ' ', ca.numero_carril) AS sub_area_nombre,
        pi.nombre AS area_nombre
    FROM
        carriles ca
    JOIN
        piscinas pi ON ca.id_piscina = pi.id_piscina
    WHERE
        (p_id_tipo_area = 0 OR pi.id_tipo_piscina = p_id_tipo_area)
        AND NOT EXISTS (
            SELECT 1
            FROM horarios h
            WHERE
                h.id_carril = ca.id_carril
                AND (h.hora_inicio < p_hora_fin AND h.hora_fin > p_hora_inicio)
                AND (h.fecha_inicio <= p_fecha_fin AND h.fecha_fin >= p_fecha_inicio)
        );
END$$
DELIMITER ;
