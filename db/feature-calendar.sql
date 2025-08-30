DELIMITER $$

CREATE PROCEDURE `sp_get_clases_for_calendar`(
    IN p_start_date DATE,
    IN p_end_date DATE
)
BEGIN
    SELECT
        md.id_matricula_dia,
        c.nombre AS title,
        CONCAT(md.fecha_clase, 'T', h.hora_inicio) AS start_datetime,
        CONCAT(md.fecha_clase, 'T', h.hora_fin) AS end_datetime
    FROM matricula_dias md
    JOIN matriculas m ON md.id_matricula = m.id_matricula
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    WHERE
        md.fecha_clase BETWEEN p_start_date AND p_end_date
        AND m.estado IN ('activa', 'vigente');
END$$

DELIMITER ;
