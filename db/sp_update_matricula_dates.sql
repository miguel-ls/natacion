-- Stored procedure to update the dates of an enrollment
DROP PROCEDURE IF EXISTS `sp_update_matricula_dates`;

DELIMITER $$

CREATE PROCEDURE `sp_update_matricula_dates`(
    IN p_id_matricula INT,
    IN p_new_fecha_inicio DATE,
    IN p_new_fecha_fin DATE
)
BEGIN
    DECLARE v_id_horario INT;
    DECLARE v_dias_semana VARCHAR(50);

    -- Start transaction for atomicity
    START TRANSACTION;

    -- 1. Get the current id_horario from the matricula
    SELECT id_horario INTO v_id_horario
    FROM matriculas
    WHERE id_matricula = p_id_matricula;

    -- 2. Get the days of the week from the existing horario
    SELECT th.dias_semana INTO v_dias_semana
    FROM horarios h
    JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    WHERE h.id_horario = v_id_horario;

    -- 3. Update the matricula with the new dates
    UPDATE matriculas
    SET
        fecha_inicio = p_new_fecha_inicio,
        fecha_fin = p_new_fecha_fin
    WHERE id_matricula = p_id_matricula;

    -- 4. Delete all old class days for this matricula
    DELETE FROM matricula_dias WHERE id_matricula = p_id_matricula;

    -- 5. Generate the new list of class days with the new configuration
    CALL sp_generate_dias_clase(p_id_matricula, p_new_fecha_inicio, p_new_fecha_fin, v_dias_semana);

    COMMIT;
END$$

DELIMITER ;
