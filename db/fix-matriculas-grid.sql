-- =================================================================
-- ARCHIVO DE SCRIPT PARA CORREGIR LA GRILLA DE GESTIÓN DE MATRÍCULAS
-- =================================================================

DELIMITER $$

--
-- 1. Actualización de `sp_get_matriculas_filtradas` para añadir campos faltantes
--
DROP PROCEDURE IF EXISTS `sp_get_matriculas_filtradas`;

CREATE PROCEDURE `sp_get_matriculas_filtradas`(
    IN p_id_alumno INT,
    IN p_id_curso INT,
    IN p_id_tipo_curso INT,
    IN p_fecha_inicio_desde DATE,
    IN p_fecha_inicio_hasta DATE,
    IN p_estado VARCHAR(20)
)
BEGIN
    SELECT
        m.id_matricula,
        CONCAT(a.nombres, ' ', a.apellidos) AS alumno_nombre,
        c.nombre AS curso_nombre,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        m.fecha_inicio,
        m.fecha_fin,
        m.descuento,
        m.precio_final,
        m.estado,
        u.nombre as admin_nombre,
        m.fecha_matricula,
        (SELECT pc.fecha_inicio FROM precios_cursos pc WHERE pc.id_curso = c.id_curso AND m.fecha_matricula BETWEEN pc.fecha_inicio AND pc.fecha_fin ORDER BY pc.id_tipo_precio DESC LIMIT 1) AS fecha_inicio_curso,
        (SELECT pc.fecha_fin FROM precios_cursos pc WHERE pc.id_curso = c.id_curso AND m.fecha_matricula BETWEEN pc.fecha_inicio AND pc.fecha_fin ORDER BY pc.id_tipo_precio DESC LIMIT 1) AS fecha_fin_curso
    FROM matriculas m
    JOIN alumnos a ON m.id_alumno = a.id_alumno
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN profesores p ON h.id_profesor = p.id_profesor
    JOIN usuarios u ON m.id_usuario_admin = u.id_usuario
    WHERE
        (p_id_alumno = 0 OR m.id_alumno = p_id_alumno)
        AND (p_id_curso = 0 OR c.id_curso = p_id_curso)
        AND (p_id_tipo_curso = 0 OR c.id_tipo_profesor = p_id_tipo_curso)
        AND (p_fecha_inicio_desde IS NULL OR m.fecha_inicio >= p_fecha_inicio_desde)
        AND (p_fecha_inicio_hasta IS NULL OR m.fecha_inicio <= p_fecha_inicio_hasta)
        AND (p_estado = 'Todos' OR m.estado = p_estado)
    ORDER BY m.fecha_matricula DESC;
END$$

DELIMITER ;
