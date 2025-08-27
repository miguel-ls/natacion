-- Script de Actualización de DB (Fechas en Horarios)
-- Añade rangos de fecha a los horarios y actualiza la lógica relacionada.

-- 1. Modificar la tabla de horarios para añadir fechas de inicio y fin
ALTER TABLE `horarios`
ADD COLUMN `fecha_inicio` DATE NULL DEFAULT NULL AFTER `hora_fin`,
ADD COLUMN `fecha_fin` DATE NULL DEFAULT NULL AFTER `fecha_inicio`;


-- 2. Actualizar procedimientos almacenados
DROP PROCEDURE IF EXISTS `sp_create_horario`;
DROP PROCEDURE IF EXISTS `sp_update_horario`;
DROP PROCEDURE IF EXISTS `sp_get_horarios_by_profesor`;
DROP PROCEDURE IF EXISTS `sp_get_all_horarios_details`;
DROP PROCEDURE IF EXISTS `sp_get_horarios_disponibles_por_curso`;

DELIMITER $$

-- Crear Horario (con fechas)
CREATE PROCEDURE `sp_create_horario`(
    IN p_id_curso INT,
    IN p_id_profesor INT,
    IN p_id_carril INT,
    IN p_id_tipo_horario INT,
    IN p_hora_inicio TIME,
    IN p_hora_fin TIME,
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    INSERT INTO horarios (id_curso, id_profesor, id_carril, id_tipo_horario, hora_inicio, hora_fin, fecha_inicio, fecha_fin)
    VALUES (p_id_curso, p_id_profesor, p_id_carril, p_id_tipo_horario, p_hora_inicio, p_hora_fin, p_fecha_inicio, p_fecha_fin);
END$$

-- Actualizar Horario (con fechas)
CREATE PROCEDURE `sp_update_horario`(
    IN p_id_horario INT,
    IN p_id_curso INT,
    IN p_id_profesor INT,
    IN p_id_carril INT,
    IN p_id_tipo_horario INT,
    IN p_hora_inicio TIME,
    IN p_hora_fin TIME,
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    UPDATE horarios
    SET id_curso = p_id_curso,
        id_profesor = p_id_profesor,
        id_carril = p_id_carril,
        id_tipo_horario = p_id_tipo_horario,
        hora_inicio = p_hora_inicio,
        hora_fin = p_hora_fin,
        fecha_inicio = p_fecha_inicio,
        fecha_fin = p_fecha_fin
    WHERE id_horario = p_id_horario;
END$$

-- Obtener Horarios de un Profesor (con fechas)
CREATE PROCEDURE `sp_get_horarios_by_profesor`(IN p_id_profesor INT)
BEGIN
    SELECT
        h.id_horario,
        h.hora_inicio,
        h.hora_fin,
        h.fecha_inicio,
        h.fecha_fin,
        th.dias_semana
    FROM horarios h
    JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    WHERE h.id_profesor = p_id_profesor;
END$$

-- Obtener todos los Horarios (con fechas)
CREATE PROCEDURE `sp_get_all_horarios_details`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT
        h.id_horario,
        c.nombre AS curso_nombre,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        CONCAT(pi.nombre, ' - Carril ', ca.numero_carril) as carril_nombre,
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
    WHERE p_search_term = ''
        OR c.nombre LIKE CONCAT('%', p_search_term, '%')
        OR CONCAT(p.nombres, ' ', p.apellidos) LIKE CONCAT('%', p_search_term, '%')
    ORDER BY h.id_horario;
END$$

-- Obtener Horarios Disponibles (con fechas)
CREATE PROCEDURE `sp_get_horarios_disponibles_por_curso`(
    IN p_id_curso INT,
    IN p_id_profesor INT,
    IN p_hora_inicio TIME,
    IN p_hora_fin TIME
)
BEGIN
    SELECT
        h.id_horario,
        c.nombre AS curso_nombre,
        (SELECT precio FROM precios_cursos pc WHERE pc.id_curso = h.id_curso AND CURDATE() BETWEEN pc.fecha_inicio AND pc.fecha_fin ORDER BY pc.id_tipo_precio DESC LIMIT 1) AS precio_actual,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        CONCAT(pi.nombre, ' - Carril ', ca.numero_carril) as carril_nombre,
        th.nombre as tipo_horario_nombre,
        th.dias_semana,
        h.hora_inicio,
        h.hora_fin,
        h.fecha_inicio,
        h.fecha_fin,
        (ca.capacidad_maxima - (SELECT COUNT(*) FROM matriculas m WHERE m.id_horario = h.id_horario AND m.estado IN ('activa', 'vigente'))) AS vacantes_disponibles
    FROM horarios h
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN profesores p ON h.id_profesor = p.id_profesor
    JOIN carriles ca ON h.id_carril = ca.id_carril
    JOIN piscinas pi ON ca.id_piscina = pi.id_piscina
    JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    WHERE h.id_curso = p_id_curso
      AND (p_id_profesor = 0 OR h.id_profesor = p_id_profesor)
      AND (p_hora_inicio IS NULL OR h.hora_inicio >= p_hora_inicio)
      AND (p_hora_fin IS NULL OR h.hora_fin <= p_hora_fin)
      AND (CURDATE() <= h.fecha_fin OR h.fecha_fin IS NULL) -- Solo mostrar horarios que no han terminado
    HAVING vacantes_disponibles > 0;
END$$

DELIMITER ;
