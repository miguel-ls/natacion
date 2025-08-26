-- Script de Actualización de Procedimientos Almacenados (Filtros de Búsqueda Adicionales)

-- Eliminar procedimientos que serán reemplazados.
DROP PROCEDURE IF EXISTS `sp_get_all_matriculas_details`;
DROP PROCEDURE IF EXISTS `sp_get_all_tipos_piscina`;
DROP PROCEDURE IF EXISTS `sp_get_all_piscinas`;
DROP PROCEDURE IF EXISTS `sp_get_all_carriles`;
DROP PROCEDURE IF EXISTS `sp_get_all_tipos_horario`;
DROP PROCEDURE IF EXISTS `sp_get_all_horarios_details`;
DROP PROCEDURE IF EXISTS `sp_get_all_formas_pago`;
DROP PROCEDURE IF EXISTS `sp_get_all_tipos_precio`;
DROP PROCEDURE IF EXISTS `sp_get_all_precios_cursos`;

DELIMITER $$

-- =============================================
-- PROCEDIMIENTOS MODIFICADOS CON BÚSQUEDA
-- =============================================

-- Tipos de Piscina
CREATE PROCEDURE `sp_get_all_tipos_piscina`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT id_tipo_piscina, nombre, descripcion FROM tipos_piscina
    WHERE p_search_term = '' OR nombre LIKE CONCAT('%', p_search_term, '%');
END$$

-- Piscinas
CREATE PROCEDURE `sp_get_all_piscinas`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT p.id_piscina, p.nombre, p.id_tipo_piscina, tp.nombre AS tipo_piscina_nombre
    FROM piscinas p
    LEFT JOIN tipos_piscina tp ON p.id_tipo_piscina = tp.id_tipo_piscina
    WHERE p_search_term = '' OR p.nombre LIKE CONCAT('%', p_search_term, '%')
    ORDER BY p.id_piscina;
END$$

-- Carriles
CREATE PROCEDURE `sp_get_all_carriles`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT c.id_carril, c.id_piscina, p.nombre AS piscina_nombre, c.numero_carril, c.capacidad_maxima
    FROM carriles c
    JOIN piscinas p ON c.id_piscina = p.id_piscina
    WHERE p_search_term = '' OR p.nombre LIKE CONCAT('%', p_search_term, '%') OR c.numero_carril LIKE CONCAT('%', p_search_term, '%')
    ORDER BY p.nombre, c.numero_carril;
END$$

-- Tipos de Horario
CREATE PROCEDURE `sp_get_all_tipos_horario`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT id_tipo_horario, nombre, dias_semana FROM tipos_horario
    WHERE p_search_term = '' OR nombre LIKE CONCAT('%', p_search_term, '%');
END$$

-- Horarios
CREATE PROCEDURE `sp_get_all_horarios_details`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT
        h.id_horario,
        c.nombre AS curso_nombre,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        CONCAT(pi.nombre, ' - Carril ', ca.numero_carril) as carril_nombre,
        th.nombre as tipo_horario_nombre,
        h.hora_inicio,
        h.hora_fin
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

-- Formas de Pago
CREATE PROCEDURE `sp_get_all_formas_pago`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT id_forma_pago, nombre FROM formas_pago
    WHERE p_search_term = '' OR nombre LIKE CONCAT('%', p_search_term, '%');
END$$

-- Tipos de Precio
CREATE PROCEDURE `sp_get_all_tipos_precio`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT * FROM tipos_precio
    WHERE p_search_term = '' OR nombre LIKE CONCAT('%', p_search_term, '%');
END$$

-- Precios de Cursos
CREATE PROCEDURE `sp_get_all_precios_cursos`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT pc.id_precio_curso, c.nombre as curso_nombre, tp.nombre as tipo_precio_nombre, pc.precio, pc.fecha_inicio, pc.fecha_fin
    FROM precios_cursos pc
    JOIN cursos c ON pc.id_curso = c.id_curso
    JOIN tipos_precio tp ON pc.id_tipo_precio = tp.id_tipo_precio
    WHERE p_search_term = '' OR c.nombre LIKE CONCAT('%', p_search_term, '%') OR tp.nombre LIKE CONCAT('%', p_search_term, '%')
    ORDER BY pc.fecha_inicio DESC;
END$$

-- Matrículas
CREATE PROCEDURE `sp_get_all_matriculas_details`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT
        m.id_matricula,
        CONCAT(a.nombres, ' ', a.apellidos) AS alumno_nombre,
        c.nombre AS curso_nombre,
        m.fecha_inicio,
        m.fecha_fin,
        m.precio_final,
        m.estado,
        u.nombre as admin_nombre,
        m.fecha_matricula
    FROM matriculas m
    JOIN alumnos a ON m.id_alumno = a.id_alumno
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN usuarios u ON m.id_usuario_admin = u.id_usuario
    WHERE p_search_term = ''
        OR CONCAT(a.nombres, ' ', a.apellidos) LIKE CONCAT('%', p_search_term, '%')
        OR c.nombre LIKE CONCAT('%', p_search_term, '%')
        OR m.estado LIKE CONCAT('%', p_search_term, '%')
    ORDER BY m.fecha_matricula DESC;
END$$

DELIMITER ;
