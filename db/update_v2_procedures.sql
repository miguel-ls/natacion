-- Script de Actualización de Procedimientos Almacenados (Precios Dinámicos)

-- Eliminar procedimientos antiguos que serán reemplazados para evitar errores.
DROP PROCEDURE IF EXISTS `sp_create_curso`;
DROP PROCEDURE IF EXISTS `sp_update_curso`;
DROP PROCEDURE IF EXISTS `sp_get_all_cursos`;
DROP PROCEDURE IF EXISTS `sp_get_curso_by_id`;
DROP PROCEDURE IF EXISTS `sp_get_horarios_disponibles_por_curso`;

DELIMITER $$

-- =============================================
-- Procedimientos para la tabla `cursos` (MODIFICADOS)
-- =============================================
CREATE PROCEDURE `sp_create_curso`(IN p_nombre VARCHAR(100), IN p_descripcion TEXT)
BEGIN INSERT INTO cursos (nombre, descripcion) VALUES (p_nombre, p_descripcion); END$$

CREATE PROCEDURE `sp_update_curso`(IN p_id_curso INT, IN p_nombre VARCHAR(100), IN p_descripcion TEXT)
BEGIN UPDATE cursos SET nombre = p_nombre, descripcion = p_descripcion WHERE id_curso = p_id_curso; END$$

CREATE PROCEDURE `sp_get_all_cursos`()
BEGIN SELECT id_curso, nombre, descripcion FROM cursos; END$$

CREATE PROCEDURE `sp_get_curso_by_id`(IN p_id_curso INT)
BEGIN SELECT id_curso, nombre, descripcion FROM cursos WHERE id_curso = p_id_curso; END$$

-- =============================================
-- NUEVOS Procedimientos para la tabla `tipos_precio`
-- =============================================
CREATE PROCEDURE `sp_create_tipo_precio`(IN p_nombre VARCHAR(50), IN p_descripcion TEXT)
BEGIN INSERT INTO tipos_precio (nombre, descripcion) VALUES (p_nombre, p_descripcion); END$$

CREATE PROCEDURE `sp_get_all_tipos_precio`()
BEGIN SELECT * FROM tipos_precio; END$$

CREATE PROCEDURE `sp_get_tipo_precio_by_id`(IN p_id INT)
BEGIN SELECT * FROM tipos_precio WHERE id_tipo_precio = p_id; END$$

CREATE PROCEDURE `sp_update_tipo_precio`(IN p_id INT, IN p_nombre VARCHAR(50), IN p_descripcion TEXT)
BEGIN UPDATE tipos_precio SET nombre = p_nombre, descripcion = p_descripcion WHERE id_tipo_precio = p_id; END$$

CREATE PROCEDURE `sp_delete_tipo_precio`(IN p_id INT)
BEGIN DELETE FROM tipos_precio WHERE id_tipo_precio = p_id; END$$

-- =============================================
-- NUEVOS Procedimientos para la tabla `precios_cursos`
-- =============================================
CREATE PROCEDURE `sp_create_precio_curso`(IN p_id_curso INT, IN p_id_tipo_precio INT, IN p_precio DECIMAL(10, 2), IN p_fecha_inicio DATE, IN p_fecha_fin DATE)
BEGIN INSERT INTO precios_cursos (id_curso, id_tipo_precio, precio, fecha_inicio, fecha_fin) VALUES (p_id_curso, p_id_tipo_precio, p_precio, p_fecha_inicio, p_fecha_fin); END$$

CREATE PROCEDURE `sp_get_all_precios_cursos`()
BEGIN SELECT pc.id_precio_curso, c.nombre as curso_nombre, tp.nombre as tipo_precio_nombre, pc.precio, pc.fecha_inicio, pc.fecha_fin FROM precios_cursos pc JOIN cursos c ON pc.id_curso = c.id_curso JOIN tipos_precio tp ON pc.id_tipo_precio = tp.id_tipo_precio ORDER BY pc.fecha_inicio DESC; END$$

CREATE PROCEDURE `sp_get_precio_curso_by_id`(IN p_id INT)
BEGIN SELECT * FROM precios_cursos WHERE id_precio_curso = p_id; END$$

CREATE PROCEDURE `sp_update_precio_curso`(IN p_id INT, IN p_id_curso INT, IN p_id_tipo_precio INT, IN p_precio DECIMAL(10, 2), IN p_fecha_inicio DATE, IN p_fecha_fin DATE)
BEGIN UPDATE precios_cursos SET id_curso = p_id_curso, id_tipo_precio = p_id_tipo_precio, precio = p_precio, fecha_inicio = p_fecha_inicio, fecha_fin = p_fecha_fin WHERE id_precio_curso = p_id; END$$

CREATE PROCEDURE `sp_delete_precio_curso`(IN p_id INT)
BEGIN DELETE FROM precios_cursos WHERE id_precio_curso = p_id; END$$

-- =============================================
-- NUEVO Procedimiento para obtener precio vigente
-- =============================================
CREATE PROCEDURE `sp_get_precio_vigente_por_curso`(IN p_id_curso INT, IN p_fecha_actual DATE)
BEGIN SELECT precio FROM precios_cursos WHERE id_curso = p_id_curso AND p_fecha_actual BETWEEN fecha_inicio AND fecha_fin ORDER BY id_tipo_precio DESC LIMIT 1; END$$

-- =============================================
-- Procedimiento MODIFICADO para lógica de negocio (Matrícula)
-- =============================================
CREATE PROCEDURE `sp_get_horarios_disponibles_por_curso`(IN p_id_curso INT)
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
        (ca.capacidad_maxima - (
            SELECT COUNT(*)
            FROM matriculas m
            WHERE m.id_horario = h.id_horario AND m.estado IN ('activa', 'vigente')
        )) AS vacantes_disponibles
    FROM horarios h
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN profesores p ON h.id_profesor = p.id_profesor
    JOIN carriles ca ON h.id_carril = ca.id_carril
    JOIN piscinas pi ON ca.id_piscina = pi.id_piscina
    JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    WHERE h.id_curso = p_id_curso
    HAVING vacantes_disponibles > 0;
END$$

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
