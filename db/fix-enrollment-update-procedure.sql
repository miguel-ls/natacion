-- =============================================
-- Author:      Jules
-- Create date: 2025-08-31
-- Description: Updates an enrollment's schedule and/or dates.
--              This procedure is self-contained and robust. It checks
--              if the schedule ID has changed, validates the new ID if
--              it has, updates dates, and rebuilds attendance records.
-- =============================================

-- Drop the existing procedure to replace it
DROP PROCEDURE IF EXISTS `sp_change_horario_matricula`;

DELIMITER $$

CREATE PROCEDURE `sp_change_horario_matricula`(
    IN p_id_matricula INT,
    IN p_new_id_horario INT,
    IN p_new_fecha_inicio DATE,
    IN p_new_fecha_fin DATE
)
BEGIN
    DECLARE v_current_id_horario INT;
    DECLARE v_dias_semana VARCHAR(50);
    DECLARE v_horario_for_rebuild INT;
    DECLARE v_horario_exists INT;

    -- Start transaction for atomicity
    START TRANSACTION;

    -- 1. Get the current schedule ID from the database for a reliable comparison.
    SELECT id_horario INTO v_current_id_horario
    FROM matriculas
    WHERE id_matricula = p_id_matricula;

    -- 2. Determine which schedule ID to use for rebuilding attendance.
    --    Also, validate and update if the schedule ID has actually changed.
    IF v_current_id_horario != p_new_id_horario THEN
        -- The schedule ID is changing. We must validate the new ID before using it.
        SELECT COUNT(*) INTO v_horario_exists FROM horarios WHERE id_horario = p_new_id_horario;

        IF v_horario_exists = 0 THEN
            -- The new ID is invalid. Abort the entire transaction.
            ROLLBACK;
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El nuevo ID de horario proporcionado no es válido o no existe.';
        ELSE
            -- The new ID is valid. Update the matricula's horario.
            UPDATE matriculas SET id_horario = p_new_id_horario WHERE id_matricula = p_id_matricula;
            SET v_horario_for_rebuild = p_new_id_horario;
        END IF;
    ELSE
        -- The schedule ID is NOT changing. Use the existing id_horario for the rebuild.
        SET v_horario_for_rebuild = v_current_id_horario;
    END IF;

    -- 3. Update the enrollment dates. This happens in both cases (schedule change or date-only change).
    UPDATE matriculas
    SET
        fecha_inicio = p_new_fecha_inicio,
        fecha_fin = p_new_fecha_fin
    WHERE id_matricula = p_id_matricula;

    -- 4. Get the days of the week for the schedule that will be used for the rebuild.
    SELECT th.dias_semana INTO v_dias_semana
    FROM horarios h
    JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    WHERE h.id_horario = v_horario_for_rebuild;

    -- 5. Rebuild the attendance days using the new dates and correct schedule days.
    DELETE FROM matricula_dias WHERE id_matricula = p_id_matricula;
    CALL sp_generate_dias_clase(p_id_matricula, p_new_fecha_inicio, p_new_fecha_fin, v_dias_semana);

    -- If we got this far without error, commit the transaction.
    COMMIT;
END$$

DELIMITER ;
