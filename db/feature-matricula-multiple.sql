DELIMITER $$

CREATE PROCEDURE `sp_get_available_areas_multiple`(
    IN `p_id_tipo_area` INT,
    IN `p_fecha_inicio` DATE,
    IN `p_fecha_fin` DATE,
    IN `p_hora_inicio` TIME,
    IN `p_hora_fin` TIME
)
BEGIN
    SELECT
        h.id_horario,
        c.nombre AS curso_nombre,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        pi.nombre AS area_nombre,
        ca.descripcion AS sub_area_nombre,
        th.nombre AS tipo_horario_nombre,
        h.hora_inicio,
        h.hora_fin,
        h.fecha_inicio AS horario_fecha_inicio,
        h.fecha_fin AS horario_fecha_fin,
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
    WHERE
        -- Filtro por Tipo de Area (si se especifica)
        (p_id_tipo_area = 0 OR pi.id_tipo_piscina = p_id_tipo_area)

        -- Filtro de Fechas: el rango del horario debe solaparse con el rango solicitado
        AND (h.fecha_inicio <= p_fecha_fin AND h.fecha_fin >= p_fecha_inicio)

        -- Filtro de Horas: el rango del horario debe solaparse con el rango solicitado
        AND (h.hora_inicio < p_hora_fin AND h.hora_fin > p_hora_inicio)

    HAVING vacantes_disponibles > 0;
END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE `sp_create_matricula_multiple`(
    IN `p_id_alumno` INT,
    IN `p_id_usuario` INT,
    IN `p_selected_schedules_json` TEXT, -- JSON array of schedule IDs, e.g., "[1, 2, 3]"
    IN `p_id_grupo_matricula` INT
)
BEGIN
    DECLARE v_schedule_id INT;
    DECLARE i INT DEFAULT 0;
    DECLARE v_num_schedules INT;

    SET v_num_schedules = JSON_LENGTH(p_selected_schedules_json);

    WHILE i < v_num_schedules DO
        SET v_schedule_id = JSON_UNQUOTE(JSON_EXTRACT(p_selected_schedules_json, CONCAT('$[', i, ']')));

        -- For each schedule, create a matricula and its classes
        BEGIN
            DECLARE v_new_matricula_id INT;
            DECLARE v_horario_fecha_inicio DATE;
            DECLARE v_horario_fecha_fin DATE;
            DECLARE v_dias_semana VARCHAR(255);
            DECLARE v_precio_base DECIMAL(10, 2);

            -- Get schedule details
            SELECT
                h.fecha_inicio,
                h.fecha_fin,
                th.dias_semana,
                COALESCE((SELECT pc.precio FROM precios_cursos pc WHERE pc.id_curso = h.id_curso AND h.fecha_inicio >= pc.fecha_inicio ORDER BY pc.fecha_inicio DESC LIMIT 1), 0.00)
            INTO v_horario_fecha_inicio, v_horario_fecha_fin, v_dias_semana, v_precio_base
            FROM horarios h
            JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
            WHERE h.id_horario = v_schedule_id;

            -- 1. Create the matricula record
            -- Using default values for payment and discount for now.
            CALL sp_create_matricula(
                p_id_alumno,
                v_schedule_id,
                p_id_usuario,
                v_horario_fecha_inicio, -- Usar la fecha de inicio del horario
                v_horario_fecha_fin,    -- Usar la fecha de fin del horario
                v_precio_base,          -- Usar el precio base encontrado
                0,                      -- descuento
                1,                      -- id_forma_pago (default a 'Efectivo' o similar)
                'Matrícula múltiple',   -- observaciones
                p_id_grupo_matricula    -- Nuevo ID de grupo
            );

            -- Get the ID of the matricula just created
            -- Note: sp_create_matricula must return the new ID or we can use LAST_INSERT_ID() if it's safe.
            -- Assuming sp_create_matricula sets the last insert id correctly.
            SELECT LAST_INSERT_ID() INTO v_new_matricula_id;

            -- 2. Generate class days
            CALL sp_generate_dias_clase(
                v_new_matricula_id,
                v_horario_fecha_inicio,
                v_horario_fecha_fin,
                v_dias_semana
            );
        END;

        SET i = i + 1;
    END WHILE;
END$$

DELIMITER ;
