-- Nueva tabla para la asistencia de alumnos
CREATE TABLE `asistencias_alumnos` (
  `id_asistencia_alumno` INT AUTO_INCREMENT PRIMARY KEY,
  `id_matricula` INT NOT NULL,
  `id_alumno` INT NOT NULL,
  `fecha_clase` DATE NOT NULL,
  `estado` ENUM('asistio', 'falto', 'postergado') NOT NULL,
  `observaciones` TEXT,
  FOREIGN KEY (`id_matricula`) REFERENCES `matriculas`(`id_matricula`) ON DELETE CASCADE,
  FOREIGN KEY (`id_alumno`) REFERENCES `alumnos`(`id_alumno`) ON DELETE CASCADE,
  UNIQUE KEY `idx_matricula_fecha` (`id_matricula`, `fecha_clase`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Stored Procedure para crear o actualizar la asistencia de un alumno
DROP PROCEDURE IF EXISTS `sp_create_or_update_asistencia_alumno`;
DELIMITER $$
CREATE PROCEDURE `sp_create_or_update_asistencia_alumno`(
    IN `p_id_matricula` INT,
    IN `p_id_alumno` INT,
    IN `p_fecha_clase` DATE,
    IN `p_estado` VARCHAR(20),
    IN `p_observaciones` TEXT
)
BEGIN
    -- Verificar si ya existe un registro para esa matrícula y fecha
    IF EXISTS (SELECT 1 FROM `asistencias_alumnos` WHERE `id_matricula` = `p_id_matricula` AND `fecha_clase` = `p_fecha_clase`) THEN
        -- Si existe, actualizar el registro
        UPDATE `asistencias_alumnos`
        SET
            `estado` = `p_estado`,
            `observaciones` = `p_observaciones`
        WHERE `id_matricula` = `p_id_matricula` AND `fecha_clase` = `p_fecha_clase`;
    ELSE
        -- Si no existe, insertar un nuevo registro
        INSERT INTO `asistencias_alumnos` (`id_matricula`, `id_alumno`, `fecha_clase`, `estado`, `observaciones`)
        VALUES (`p_id_matricula`, `p_id_alumno`, `p_fecha_clase`, `p_estado`, `p_observaciones`);
    END IF;
END$$
DELIMITER ;

-- Stored Procedure para listar las matrículas de alumnos con filtros
DROP PROCEDURE IF EXISTS `sp_listar_matriculas_alumno_filtrado`;
DELIMITER $$
CREATE PROCEDURE `sp_listar_matriculas_alumno_filtrado`(
    IN `p_id_alumno` INT,
    IN `p_id_curso` INT,
    IN `p_estado_matricula` VARCHAR(20)
)
BEGIN
    SELECT
        m.id_matricula,
        m.fecha_inicio,
        m.fecha_fin,
        m.estado AS estado_matricula,
        a.id_alumno,
        CONCAT(a.nombres, ' ', a.apellidos) AS alumno_nombre,
        c.id_curso,
        c.nombre AS curso_nombre,
        h.id_horario,
        th.nombre AS tipo_horario_nombre,
        h.hora_inicio,
        h.hora_fin,
        -- El estado calculado de la matrícula ya existe en la tabla matriculas
        m.estado AS estado_calculado
    FROM matriculas m
    JOIN alumnos a ON m.id_alumno = a.id_alumno
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    WHERE
        (p_id_alumno = 0 OR m.id_alumno = p_id_alumno)
        AND (p_id_curso = 0 OR h.id_curso = p_id_curso)
        AND (p_estado_matricula = 'Todos' OR m.estado = p_estado_matricula)
        AND m.estado != 'anulada' -- No mostrar matrículas anuladas
    ORDER BY
        a.apellidos, a.nombres, m.fecha_inicio DESC;
END$$
DELIMITER ;
