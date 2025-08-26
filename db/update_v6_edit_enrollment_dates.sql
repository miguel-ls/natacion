-- Script de Actualización de Procedimientos Almacenados (Editar Fechas de Matrícula)

DROP PROCEDURE IF EXISTS `sp_update_matricula_dates`;

DELIMITER $$

CREATE PROCEDURE `sp_update_matricula_dates`(
    IN p_id_matricula INT,
    IN p_new_fecha_inicio DATE,
    IN p_new_fecha_fin DATE
)
BEGIN
    DECLARE v_dias_semana VARCHAR(50);

    -- Iniciar transacción para asegurar la atomicidad
    START TRANSACTION;

    -- 1. Actualizar las fechas en la tabla principal de matrículas
    UPDATE matriculas
    SET fecha_inicio = p_new_fecha_inicio, fecha_fin = p_new_fecha_fin
    WHERE id_matricula = p_id_matricula;

    -- 2. Obtener los días de la semana del horario asociado a esta matrícula
    SELECT th.dias_semana INTO v_dias_semana
    FROM matriculas m
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    WHERE m.id_matricula = p_id_matricula;

    -- 3. Eliminar todos los días de clase antiguos para esta matrícula
    DELETE FROM matricula_dias WHERE id_matricula = p_id_matricula;

    -- 4. Generar la nueva lista de días de clase con las nuevas fechas
    CALL sp_generate_dias_clase(p_id_matricula, p_new_fecha_inicio, p_new_fecha_fin, v_dias_semana);

    COMMIT;
END$$

DELIMITER ;
