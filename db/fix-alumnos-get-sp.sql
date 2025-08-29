-- =================================================================
-- ARCHIVO DE SCRIPT PARA CORREGIR LOS PROCEDIMIENTOS DE OBTENCIÓN DE ALUMNOS
-- =================================================================

--
-- Este script actualiza los procedimientos almacenados `sp_get_all_alumnos`
-- y `sp_get_alumno_by_id` para que incluyan el campo `id_tipo_documento`
-- en sus resultados. Esto es necesario para que la página de edición de alumnos
-- funcione correctamente.
--

DELIMITER $$

-- Eliminar los procedimientos existentes para poder recrearlos
DROP PROCEDURE IF EXISTS `sp_get_all_alumnos`;
DROP PROCEDURE IF EXISTS `sp_get_alumno_by_id`;

-- Recrear `sp_get_all_alumnos` con el nuevo campo
CREATE PROCEDURE `sp_get_all_alumnos`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT id_alumno, nombres, apellidos, id_tipo_documento, documento_identidad, fecha_nacimiento, grupo_sanguineo, direccion, telefono, email, nombre_padre_tutor, telefono_emergencia, fecha_registro
    FROM alumnos
    WHERE p_search_term = ''
       OR nombres LIKE CONCAT('%', p_search_term, '%')
       OR apellidos LIKE CONCAT('%', p_search_term, '%')
       OR documento_identidad LIKE CONCAT('%', p_search_term, '%');
END$$

-- Recrear `sp_get_alumno_by_id` con el nuevo campo
CREATE PROCEDURE `sp_get_alumno_by_id`(IN p_id_alumno INT)
BEGIN
    SELECT id_alumno, nombres, apellidos, id_tipo_documento, documento_identidad, fecha_nacimiento, grupo_sanguineo, direccion, telefono, email, nombre_padre_tutor, telefono_emergencia, fecha_registro
    FROM alumnos WHERE id_alumno = p_id_alumno;
END$$

DELIMITER ;
