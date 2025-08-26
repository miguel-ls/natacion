-- Script de Actualización de Procedimientos Almacenados (Edición Unificada de Matrícula)

DROP PROCEDURE IF EXISTS `sp_change_horario_matricula`;

DELIMITER $$

CREATE PROCEDURE `sp_change_horario_matricula`(
    IN p_id_matricula INT,
    IN p_new_id_horario INT,
    IN p_new_fecha_inicio DATE,
    IN p_new_fecha_fin DATE
)
BEGIN
    DECLARE v_new_dias_semana VARCHAR(50);

    -- Iniciar transacción para asegurar la atomicidad
    START TRANSACTION;

    -- 1. Obtener los días de la semana del nuevo horario
    SELECT th.dias_semana INTO v_new_dias_semana
    FROM horarios h JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    WHERE h.id_horario = p_new_id_horario;

    -- 2. Actualizar la matrícula con el nuevo id_horario Y las nuevas fechas
    UPDATE matriculas
    SET
        id_horario = p_new_id_horario,
        fecha_inicio = p_new_fecha_inicio,
        fecha_fin = p_new_fecha_fin
    WHERE id_matricula = p_id_matricula;

    -- 3. Eliminar todos los días de clase antiguos para esta matrícula
    DELETE FROM matricula_dias WHERE id_matricula = p_id_matricula;

    -- 4. Generar la nueva lista de días de clase con la nueva configuración
    CALL sp_generate_dias_clase(p_id_matricula, p_new_fecha_inicio, p_new_fecha_fin, v_new_dias_semana);

    COMMIT;
END$$

DELIMITER ;
