-- =================================================================
-- Archivo de Script para IT
-- Tarea: Corregir validación de cruce de horarios
-- Fecha: 2025-08-26
-- Autor: Jules
-- =================================================================

-- Resumen de Cambios:
-- Se crea un nuevo procedimiento almacenado `sp_check_schedule_conflict`
-- para validar si un nuevo horario o una actualización de horario
-- entra en conflicto con uno existente, ya sea por el profesor o por el carril.

DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_check_schedule_conflict`$$

CREATE PROCEDURE `sp_check_schedule_conflict`(
    IN p_id_profesor INT,
    IN p_id_carril INT,
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_hora_inicio TIME,
    IN p_hora_fin TIME,
    IN p_id_horario_a_excluir INT, -- Usar 0 al crear, o el ID del horario al actualizar
    OUT p_conflict_type VARCHAR(20),
    OUT p_conflicting_horario_id INT
)
BEGIN
    DECLARE v_teacher_conflict_count INT DEFAULT 0;
    DECLARE v_lane_conflict_count INT DEFAULT 0;
    DECLARE v_conflicting_id INT DEFAULT 0;

    SET p_conflict_type = 'NONE';
    SET p_conflicting_horario_id = 0;

    -- 1. Verificar conflicto de PROFESOR
    SELECT COUNT(*), MAX(h.id_horario) INTO v_teacher_conflict_count, v_conflicting_id
    FROM horarios h
    WHERE h.id_profesor = p_id_profesor
      AND h.id_horario != p_id_horario_a_excluir
      -- Comprobar si los rangos de fechas se solapan
      AND p_fecha_inicio <= h.fecha_fin AND p_fecha_fin >= h.fecha_inicio
      -- Comprobar si los rangos de horas se solapan
      AND p_hora_inicio < h.hora_fin AND p_hora_fin > h.hora_inicio;

    IF v_teacher_conflict_count > 0 THEN
        SET p_conflict_type = 'TEACHER';
        SET p_conflicting_horario_id = v_conflicting_id;
    ELSE
        -- 2. Si no hay conflicto de profesor, verificar conflicto de CARRIL
        SELECT COUNT(*), MAX(h.id_horario) INTO v_lane_conflict_count, v_conflicting_id
        FROM horarios h
        WHERE h.id_carril = p_id_carril
          AND h.id_horario != p_id_horario_a_excluir
          -- Comprobar si los rangos de fechas se solapan
          AND p_fecha_inicio <= h.fecha_fin AND p_fecha_fin >= h.fecha_inicio
          -- Comprobar si los rangos de horas se solapan
          AND p_hora_inicio < h.hora_fin AND p_hora_fin > h.hora_inicio;

        IF v_lane_conflict_count > 0 THEN
            SET p_conflict_type = 'LANE';
            SET p_conflicting_horario_id = v_conflicting_id;
        END IF;
    END IF;

END$$

DELIMITER ;

-- Nota para IT:
-- Este procedimiento es crucial para mantener la integridad de los horarios.
-- No modifica datos, solo realiza consultas de validación.
-- Devuelve 'NONE', 'TEACHER', o 'LANE' a través del parámetro OUT p_conflict_type.
-- También devuelve el ID del horario conflictivo para referencia.
-- Es seguro de ejecutar en el entorno de producción.
