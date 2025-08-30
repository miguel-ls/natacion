-- =================================================================
-- ARCHIVO DE SCRIPT PARA FILTRAR PROFESORES POR TIPO DE CURSO
-- =================================================================

DELIMITER $$

--
-- 1. Nuevo procedimiento para obtener profesores por tipo
--
CREATE PROCEDURE `sp_get_profesores_by_tipo`(IN p_id_tipo_profesor INT)
BEGIN
    SELECT id_profesor, nombres, apellidos
    FROM profesores
    WHERE (p_id_tipo_profesor = 0 OR id_tipo_profesor = p_id_tipo_profesor)
    AND estado = 'activo';
END$$


--
-- 2. Actualización de `sp_get_all_cursos` para devolver `id_tipo_profesor`
--
DROP PROCEDURE IF EXISTS `sp_get_all_cursos`;

CREATE PROCEDURE `sp_get_all_cursos`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT
        c.id_curso,
        c.nombre,
        c.id_tipo_profesor,
        tp.descripcion as tipo_curso_nombre,
        c.descripcion,
        c.codigo_erp
    FROM cursos c
    LEFT JOIN tipos_profesor tp ON c.id_tipo_profesor = tp.id
    WHERE p_search_term = ''
       OR c.nombre LIKE CONCAT('%', p_search_term, '%')
       OR c.descripcion LIKE CONCAT('%', p_search_term, '%')
       OR c.codigo_erp LIKE CONCAT('%', p_search_term, '%');
END$$

DELIMITER ;
