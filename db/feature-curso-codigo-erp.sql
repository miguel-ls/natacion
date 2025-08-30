-- =================================================================
-- ARCHIVO DE SCRIPT PARA LA FUNCIONALIDAD DE CODIGO ERP EN CURSOS
-- =================================================================

--
-- 1. Modificación de la tabla `cursos` para añadir `codigo_erp`
--
ALTER TABLE `cursos`
ADD COLUMN `codigo_erp` CHAR(10) NULL DEFAULT NULL AFTER `descripcion`;


--
-- 2. Actualización de los Procedimientos Almacenados de `cursos`
--

DELIMITER $$

-- Eliminar los procedimientos antiguos para poder recrearlos
DROP PROCEDURE IF EXISTS `sp_create_curso`;
DROP PROCEDURE IF EXISTS `sp_update_curso`;
DROP PROCEDURE IF EXISTS `sp_get_all_cursos`;
DROP PROCEDURE IF EXISTS `sp_get_curso_by_id`;

-- Recrear `sp_create_curso` con el nuevo campo
CREATE PROCEDURE `sp_create_curso`(
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_codigo_erp CHAR(10)
)
BEGIN
    INSERT INTO cursos (nombre, descripcion, codigo_erp)
    VALUES (p_nombre, p_descripcion, p_codigo_erp);
END$$

-- Recrear `sp_update_curso` con el nuevo campo
CREATE PROCEDURE `sp_update_curso`(
    IN p_id_curso INT,
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_codigo_erp CHAR(10)
)
BEGIN
    UPDATE cursos
    SET
        nombre = p_nombre,
        descripcion = p_descripcion,
        codigo_erp = p_codigo_erp
    WHERE id_curso = p_id_curso;
END$$

-- Recrear `sp_get_all_cursos` con el nuevo campo
CREATE PROCEDURE `sp_get_all_cursos`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT id_curso, nombre, descripcion, codigo_erp
    FROM cursos
    WHERE p_search_term = ''
       OR nombre LIKE CONCAT('%', p_search_term, '%')
       OR descripcion LIKE CONCAT('%', p_search_term, '%')
       OR codigo_erp LIKE CONCAT('%', p_search_term, '%');
END$$

-- Recrear `sp_get_curso_by_id` con el nuevo campo
CREATE PROCEDURE `sp_get_curso_by_id`(
    IN p_id_curso INT
)
BEGIN
    SELECT id_curso, nombre, descripcion, codigo_erp
    FROM cursos
    WHERE id_curso = p_id_curso;
END$$

DELIMITER ;
