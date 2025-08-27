DELIMITER $$

CREATE PROCEDURE `sp_get_horario_details_for_enrollment`(
    IN p_id_horario INT
)
BEGIN
    SELECT
        h.id_horario,
        h.id_curso,
        c.nombre AS curso_nombre,
        h.id_profesor,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        th.dias_semana,
        (SELECT pc.fecha_fin FROM precios_cursos pc WHERE pc.id_curso = h.id_curso AND CURDATE() BETWEEN pc.fecha_inicio AND pc.fecha_fin ORDER BY pc.id_tipo_precio DESC LIMIT 1) AS fecha_fin_curso,
        (SELECT pc.precio FROM precios_cursos pc WHERE pc.id_curso = h.id_curso AND CURDATE() BETWEEN pc.fecha_inicio AND pc.fecha_fin ORDER BY pc.id_tipo_precio DESC LIMIT 1) AS precio_sugerido
    FROM horarios h
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN profesores p ON h.id_profesor = p.id_profesor
    JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    WHERE h.id_horario = p_id_horario;
END$$

DELIMITER ;
