-- =================================================================
-- Archivo de Script para IT
-- Tarea: Permitir desmarcar la asistencia de un profesor
-- Fecha: 2025-08-26
-- Autor: Jules
-- =================================================================

-- Resumen de Cambios:
-- Se modifica el procedimiento almacenado `sp_create_or_update_asistencia_profesor`
-- para que acepte un nuevo estado 'no_marcado'.
-- Cuando se pasa 'no_marcado', el procedimiento elimina el registro de
-- asistencia existente para ese día.

DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_create_or_update_asistencia_profesor`$$

CREATE PROCEDURE `sp_create_or_update_asistencia_profesor`(
    IN p_id_profesor INT,
    IN p_id_horario INT,
    IN p_fecha DATE,
    IN p_estado VARCHAR(30), -- Cambiado de ENUM a VARCHAR para aceptar 'no_marcado'
    IN p_observaciones TEXT
)
BEGIN
    -- Si el estado es 'no_marcado', eliminamos el registro de asistencia si existe.
    IF p_estado = 'no_marcado' THEN
        DELETE FROM asistencias_profesores
        WHERE id_profesor = p_id_profesor
          AND id_horario = p_id_horario
          AND fecha = p_fecha;
    ELSE
        -- Si el estado es cualquier otro, usamos la lógica de insertar o actualizar.
        INSERT INTO asistencias_profesores(id_profesor, id_horario, fecha, estado, observaciones)
        VALUES (p_id_profesor, p_id_horario, p_fecha, p_estado, p_observaciones)
        ON DUPLICATE KEY UPDATE
            estado = p_estado,
            observaciones = p_observaciones;
    END IF;
END$$

DELIMITER ;

-- Nota para IT:
-- Se ha modificado el procedimiento para añadir la funcionalidad de eliminación
-- de registros de asistencia. El `INSERT ... ON DUPLICATE KEY UPDATE` es
-- más eficiente que la lógica anterior de SELECT y luego IF/ELSE.
-- Este cambio es seguro de aplicar.
