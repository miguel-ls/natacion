DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_get_matriculas_filtradas`$$

CREATE PROCEDURE `sp_get_matriculas_filtradas`(
    IN p_id_alumno INT,
    IN p_id_curso INT,
    IN p_fecha_inicio_desde DATE,
    IN p_fecha_inicio_hasta DATE,
    IN p_estado VARCHAR(20)
)
BEGIN
    -- Se modifica para agregar el nombre del profesor y el monto del descuento.
    SELECT
        m.id_matricula,
        CONCAT(a.nombres, ' ', a.apellidos) AS alumno_nombre,
        c.nombre AS curso_nombre,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre, -- Columna añadida
        m.fecha_inicio,
        m.fecha_fin,
        m.descuento, -- Columna añadida
        m.precio_final,
        m.estado,
        u.nombre as admin_nombre,
        m.fecha_matricula
    FROM matriculas m
    JOIN alumnos a ON m.id_alumno = a.id_alumno
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN usuarios u ON m.id_usuario_admin = u.id_usuario
    JOIN profesores p ON h.id_profesor = p.id_profesor -- JOIN añadido
    WHERE
        (p_id_alumno = 0 OR m.id_alumno = p_id_alumno)
        AND (p_id_curso = 0 OR c.id_curso = p_id_curso)
        AND (p_fecha_inicio_desde IS NULL OR m.fecha_inicio >= p_fecha_inicio_desde)
        AND (p_fecha_inicio_hasta IS NULL OR m.fecha_inicio <= p_fecha_inicio_hasta)
        AND (p_estado = 'Todos' OR m.estado = p_estado)
    ORDER BY m.fecha_matricula DESC;
END$$

DELIMITER ;
