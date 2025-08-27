DELIMITER $$

-- =============================================
-- Procedimiento para Eliminar Permanentemente una Matrícula
-- =============================================

CREATE PROCEDURE `sp_delete_matricula`(
    IN p_id_matricula INT
)
BEGIN
    -- Iniciar transacción para asegurar la atomicidad de la operación
    START TRANSACTION;

    -- 1. Eliminar los días de clase asociados a la matrícula para evitar violaciones de clave foránea.
    DELETE FROM matricula_dias WHERE id_matricula = p_id_matricula;

    -- 2. Eliminar la matrícula principal.
    DELETE FROM matriculas WHERE id_matricula = p_id_matricula;

    -- Confirmar la transacción si todo ha ido bien.
    COMMIT;
END$$

DELIMITER ;
