DELIMITER $$
CREATE PROCEDURE sp_get_matriculas_by_alumn_id(
    IN p_id_alumno INT
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
        m.estado
    FROM matriculas m
    JOIN alumnos a ON m.id_alumno = a.id_alumno
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN profesores p ON h.id_profesor = p.id_profesor
    WHERE m.id_alumno = p_id_alumno
    ORDER BY m.fecha_inicio DESC;
END$$
DELIMITER ;
