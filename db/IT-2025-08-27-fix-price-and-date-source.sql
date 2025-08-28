DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_get_horarios_disponibles_por_curso`$$

CREATE PROCEDURE `sp_get_horarios_disponibles_por_curso`(
    IN p_id_curso INT,
    IN p_id_profesor INT,
    IN p_hora_inicio TIME,
    IN p_hora_fin TIME
)
BEGIN
    -- Lógica de fechas y precios corregida según el último feedback.
    -- 1. Las fechas de inicio y fin del período ahora se obtienen directamente de la tabla `horarios`.
    -- 2. El precio se busca en `precios_cursos` donde el rango de fechas del horario
    --    esté contenido dentro del rango de fechas de una entrada de precio.
    -- 3. Si no se encuentra un precio, se devuelve 0.00.

    SELECT
        h.id_horario,
        c.nombre AS curso_nombre,
        -- Lógica de precio corregida:
        COALESCE(
            (SELECT pc.precio
             FROM precios_cursos pc
             WHERE pc.id_curso = h.id_curso
               AND h.fecha_inicio >= pc.fecha_inicio
               AND h.fecha_fin <= pc.fecha_fin
             LIMIT 1),
            0.00
        ) AS precio_actual,
        -- Lógica de fechas corregida:
        h.fecha_inicio,
        h.fecha_fin,
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
      AND (p_id_profesor = 0 OR h.id_profesor = p.id_profesor)
      AND (p_hora_inicio IS NULL OR h.hora_inicio >= p_hora_inicio)
      AND (p_hora_fin IS NULL OR h.hora_fin <= p_hora_fin)
    HAVING vacantes_disponibles > 0;
END$$

DELIMITER ;
