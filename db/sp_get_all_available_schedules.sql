DELIMITER $$

CREATE PROCEDURE `sp_get_all_horarios_disponibles`()
BEGIN
    SELECT
        h.id_horario,
        c.nombre AS curso_nombre,
        (SELECT pc.fecha_inicio FROM precios_cursos pc WHERE pc.id_curso = h.id_curso AND CURDATE() BETWEEN pc.fecha_inicio AND pc.fecha_fin ORDER BY pc.id_tipo_precio DESC LIMIT 1) AS fecha_inicio_curso,
        (SELECT pc.fecha_fin FROM precios_cursos pc WHERE pc.id_curso = h.id_curso AND CURDATE() BETWEEN pc.fecha_inicio AND pc.fecha_fin ORDER BY pc.id_tipo_precio DESC LIMIT 1) AS fecha_fin_curso,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        pi.nombre as piscina_nombre,
        ca.numero_carril,
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
    WHERE (SELECT COUNT(*) FROM precios_cursos pc WHERE pc.id_curso = h.id_curso AND CURDATE() BETWEEN pc.fecha_inicio AND pc.fecha_fin) > 0
    HAVING vacantes_disponibles > 0;
END$$

DELIMITER ;
