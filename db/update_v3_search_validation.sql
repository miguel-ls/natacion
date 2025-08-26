-- Script de Actualización de Procedimientos Almacenados (Validación y Búsqueda)

-- Se eliminan los procedimientos que serán modificados para añadirles la funcionalidad de búsqueda.
DROP PROCEDURE IF EXISTS `sp_get_all_alumnos`;
DROP PROCEDURE IF EXISTS `sp_get_all_profesores`;
DROP PROCEDURE IF EXISTS `sp_get_all_cursos`;
DROP PROCEDURE IF EXISTS `sp_get_all_users`;

DELIMITER $$

-- =============================================
-- PROCEDIMIENTOS MODIFICADOS CON BÚSQUEDA
-- =============================================

-- Obtener todos los alumnos (con filtro de búsqueda)
CREATE PROCEDURE `sp_get_all_alumnos`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT id_alumno, nombres, apellidos, documento_identidad, fecha_nacimiento, grupo_sanguineo, direccion, telefono, email, nombre_padre_tutor, telefono_emergencia, fecha_registro
    FROM alumnos
    WHERE p_search_term = ''
       OR nombres LIKE CONCAT('%', p_search_term, '%')
       OR apellidos LIKE CONCAT('%', p_search_term, '%')
       OR documento_identidad LIKE CONCAT('%', p_search_term, '%');
END$$

-- Obtener todos los profesores (con filtro de búsqueda)
CREATE PROCEDURE `sp_get_all_profesores`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT id_profesor, nombres, apellidos, documento_identidad, telefono, email, direccion, fecha_contratacion, estado
    FROM profesores
    WHERE p_search_term = ''
       OR nombres LIKE CONCAT('%', p_search_term, '%')
       OR apellidos LIKE CONCAT('%', p_search_term, '%')
       OR documento_identidad LIKE CONCAT('%', p_search_term, '%')
       OR email LIKE CONCAT('%', p_search_term, '%');
END$$

-- Obtener todos los cursos (con filtro de búsqueda)
CREATE PROCEDURE `sp_get_all_cursos`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT id_curso, nombre, descripcion
    FROM cursos
    WHERE p_search_term = ''
       OR nombre LIKE CONCAT('%', p_search_term, '%')
       OR descripcion LIKE CONCAT('%', p_search_term, '%');
END$$

-- Obtener todos los usuarios (con filtro de búsqueda)
CREATE PROCEDURE `sp_get_all_users`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT id_usuario, nombre, email, rol
    FROM usuarios
    WHERE p_search_term = ''
       OR nombre LIKE CONCAT('%', p_search_term, '%')
       OR email LIKE CONCAT('%', p_search_term, '%');
END$$


-- =============================================
-- NUEVO PROCEDIMIENTO PARA VALIDACIÓN DE HORARIOS
-- =============================================

-- Obtener todos los horarios de un profesor específico
CREATE PROCEDURE `sp_get_horarios_by_profesor`(IN p_id_profesor INT)
BEGIN
    SELECT
        h.id_horario,
        h.hora_inicio,
        h.hora_fin,
        th.dias_semana
    FROM horarios h
    JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    WHERE h.id_profesor = p_id_profesor;
END$$


DELIMITER ;
