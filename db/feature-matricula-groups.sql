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

-- Stored procedure para obtener todos los grupos de matrículas con filtros
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_get_all_matricula_grupos`;
CREATE PROCEDURE `sp_get_all_matricula_grupos`(
    IN p_id_alumno INT,
    IN p_fecha_desde DATE,
    IN p_fecha_hasta DATE
)
BEGIN
    SELECT
        mg.id_grupo_matricula,
        mg.fecha_creacion,
        CONCAT(a.nombres, ' ', a.apellidos) AS nombre_cliente
    FROM matricula_grupos mg
    JOIN alumnos a ON mg.id_alumno = a.id_alumno
    WHERE
        (p_id_alumno = 0 OR mg.id_alumno = p_id_alumno)
        AND (p_fecha_desde IS NULL OR mg.fecha_creacion >= p_fecha_desde)
        AND (p_fecha_hasta IS NULL OR mg.fecha_creacion <= p_fecha_hasta)
    ORDER BY mg.fecha_creacion DESC;
END$$
DELIMITER ;

-- Stored procedure para obtener el detalle de un grupo de matrícula
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_get_matricula_grupo_details`;
CREATE PROCEDURE `sp_get_matricula_grupo_details`(
    IN p_id_grupo_matricula INT
)
BEGIN
    SELECT
        mg.id_grupo_matricula,
        mg.fecha_creacion,
        a.id_alumno,
        CONCAT(a.nombres, ' ', a.apellidos) AS nombre_cliente
    FROM matricula_grupos mg
    JOIN alumnos a ON mg.id_alumno = a.id_alumno
    WHERE mg.id_grupo_matricula = p_id_grupo_matricula;

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

-- Stored procedure para actualizar sp_create_matricula
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_create_matricula`;
CREATE PROCEDURE `sp_create_matricula`(
    IN p_id_alumno INT,
    IN p_id_horario INT,
    IN p_id_usuario_admin INT,
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_precio_base DECIMAL(10, 2),
    IN p_descuento DECIMAL(10, 2),
    IN p_id_forma_pago INT,
    IN p_observaciones TEXT,
    IN p_id_grupo_matricula INT
)
BEGIN
    DECLARE v_precio_final DECIMAL(10, 2);
    SET v_precio_final = p_precio_base - p_descuento;

    INSERT INTO matriculas (id_alumno, id_horario, id_usuario_admin, fecha_inicio, fecha_fin, precio_final, id_forma_pago, estado, observaciones, precio_base, descuento, id_grupo_matricula)
    VALUES (p_id_alumno, p_id_horario, p_id_usuario_admin, p_fecha_inicio, p_fecha_fin, v_precio_final, p_id_forma_pago, 'activa', p_observaciones, p_precio_base, p_descuento, p_id_grupo_matricula);

    SELECT LAST_INSERT_ID() as nueva_matricula_id;
END$$
DELIMITER ;
