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
    IN p_id_grupo_matricula INT -- Nuevo parámetro
)
BEGIN
    DECLARE v_precio_final DECIMAL(10, 2);
    SET v_precio_final = p_precio_base - p_descuento;

    INSERT INTO matriculas (id_alumno, id_horario, id_usuario_admin, fecha_inicio, fecha_fin, precio_final, id_forma_pago, estado, observaciones, precio_base, descuento, id_grupo_matricula)
    VALUES (p_id_alumno, p_id_horario, p_id_usuario_admin, p_fecha_inicio, p_fecha_fin, v_precio_final, p_id_forma_pago, 'activa', p_observaciones, p_precio_base, p_descuento, p_id_grupo_matricula);

    SELECT LAST_INSERT_ID() as nueva_matricula_id;
END$$

DELIMITER ;
