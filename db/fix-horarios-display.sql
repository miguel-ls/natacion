-- =================================================================
-- ARCHIVO DE SCRIPT PARA CORREGIR LA VISUALIZACIÓN EN HORARIOS
-- =================================================================

--
-- Este script actualiza los procedimientos almacenados para que la
-- concatenación de Area y Sub Area incluya la descripción de la sub area.
--

DELIMITER $$

-- Eliminar los procedimientos antiguos para poder recrearlos
DROP PROCEDURE IF EXISTS `sp_get_all_horarios_details`;
DROP PROCEDURE IF EXISTS `sp_get_all_carriles`;

-- Recrear `sp_get_all_horarios_details` con la nueva concatenación
CREATE PROCEDURE `sp_get_all_horarios_details`()
BEGIN
    SELECT
        h.id_horario,
        c.nombre AS curso_nombre,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        CONCAT(pi.nombre, ' - ', ca.descripcion, ' ', ca.numero_carril) as carril_nombre,
        th.nombre as tipo_horario_nombre,
        h.hora_inicio,
        h.hora_fin,
        h.fecha_inicio,
        h.fecha_fin
    FROM horarios h
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN profesores p ON h.id_profesor = p.id_profesor
    JOIN carriles ca ON h.id_carril = ca.id_carril
    JOIN piscinas pi ON ca.id_piscina = pi.id_piscina
    JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    ORDER BY h.id_horario;
END$$


-- Recrear `sp_get_all_carriles` para que devuelva un nombre de display unificado
CREATE PROCEDURE `sp_get_all_carriles`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT
        c.id_carril,
        c.id_piscina,
        c.descripcion,
        p.nombre AS piscina_nombre,
        c.numero_carril,
        c.capacidad_maxima,
        CONCAT(p.nombre, ' - ', c.descripcion, ' ', c.numero_carril) as display_nombre
    FROM carriles c
    JOIN piscinas p ON c.id_piscina = p.id_piscina
    WHERE p_search_term = ''
       OR p.nombre LIKE CONCAT('%', p_search_term, '%')
       OR c.numero_carril LIKE CONCAT('%', p_search_term, '%')
       OR c.descripcion LIKE CONCAT('%', p_search_term, '%')
    ORDER BY p.nombre, c.numero_carril;
END$$

DELIMITER ;
