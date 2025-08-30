-- =================================================================
-- ARCHIVO DE SCRIPT PARA RELACIONAR CURSOS CON TIPOS DE CURSO (PROFESOR)
-- =================================================================

--
-- 1. Modificación de la tabla `cursos` para añadir `id_tipo_profesor`
--
ALTER TABLE `cursos`
ADD COLUMN `id_tipo_profesor` INT NULL AFTER `nombre`,
ADD CONSTRAINT `fk_cursos_tipos_profesor` FOREIGN KEY (`id_tipo_profesor`) REFERENCES `tipos_profesor`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;


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
    IN p_id_tipo_profesor INT,
    IN p_descripcion TEXT,
    IN p_codigo_erp CHAR(10)
)
BEGIN
    INSERT INTO cursos (nombre, id_tipo_profesor, descripcion, codigo_erp)
    VALUES (p_nombre, p_id_tipo_profesor, p_descripcion, p_codigo_erp);
END$$

-- Recrear `sp_update_curso` con el nuevo campo
CREATE PROCEDURE `sp_update_curso`(
    IN p_id_curso INT,
    IN p_nombre VARCHAR(100),
    IN p_id_tipo_profesor INT,
    IN p_descripcion TEXT,
    IN p_codigo_erp CHAR(10)
)
BEGIN
    UPDATE cursos
    SET
        nombre = p_nombre,
        id_tipo_profesor = p_id_tipo_profesor,
        descripcion = p_descripcion,
        codigo_erp = p_codigo_erp
    WHERE id_curso = p_id_curso;
END$$

-- Recrear `sp_get_all_cursos` con el nuevo campo
CREATE PROCEDURE `sp_get_all_cursos`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT
        c.id_curso,
        c.nombre,
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

-- Recrear `sp_get_curso_by_id` con el nuevo campo
CREATE PROCEDURE `sp_get_curso_by_id`(
    IN p_id_curso INT
)
BEGIN
    SELECT id_curso, nombre, id_tipo_profesor, descripcion, codigo_erp
    FROM cursos
    WHERE id_curso = p_id_curso;
END$$

DELIMITER ;
