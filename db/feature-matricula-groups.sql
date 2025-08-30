-- Nueva tabla para agrupar matrículas múltiples
CREATE TABLE `matricula_grupos` (
  `id_grupo_matricula` INT NOT NULL AUTO_INCREMENT,
  `id_alumno` INT NOT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_grupo_matricula`),
  KEY `fk_matricula_grupos_alumnos_idx` (`id_alumno`),
  CONSTRAINT `fk_matricula_grupos_alumnos` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Alterar la tabla de matrículas para añadir la referencia al grupo
ALTER TABLE `matriculas`
ADD COLUMN `id_grupo_matricula` INT NULL DEFAULT NULL AFTER `observaciones`,
ADD INDEX `fk_matriculas_grupos_idx` (`id_grupo_matricula` ASC);

ALTER TABLE `matriculas`
ADD CONSTRAINT `fk_matriculas_grupos`
  FOREIGN KEY (`id_grupo_matricula`)
  REFERENCES `matricula_grupos` (`id_grupo_matricula`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

-- Stored procedure para obtener todos los grupos de matrículas
DELIMITER $$

CREATE PROCEDURE `sp_get_all_matricula_grupos`()
BEGIN
    SELECT
        mg.id_grupo_matricula,
        mg.fecha_creacion,
        CONCAT(a.nombres, ' ', a.apellidos) AS nombre_cliente
    FROM matricula_grupos mg
    JOIN alumnos a ON mg.id_alumno = a.id_alumno
    ORDER BY mg.fecha_creacion DESC;
END$$

DELIMITER ;

-- Stored procedure para obtener el detalle de un grupo de matrícula
DELIMITER $$

CREATE PROCEDURE `sp_get_matricula_grupo_details`(
    IN p_id_grupo_matricula INT
)
BEGIN
    -- Primero, obtener la información del grupo y del alumno
    SELECT
        mg.id_grupo_matricula,
        mg.fecha_creacion,
        a.id_alumno,
        CONCAT(a.nombres, ' ', a.apellidos) AS nombre_cliente
    FROM matricula_grupos mg
    JOIN alumnos a ON mg.id_alumno = a.id_alumno
    WHERE mg.id_grupo_matricula = p_id_grupo_matricula;

    -- Segundo, obtener todas las matrículas asociadas a ese grupo
    SELECT
        m.id_matricula,
        c.nombre AS curso_nombre,
        m.fecha_inicio,
        m.fecha_fin,
        m.precio_final,
        m.estado
    FROM matriculas m
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    WHERE m.id_grupo_matricula = p_id_grupo_matricula
    ORDER BY c.nombre;
END$$

DELIMITER ;
