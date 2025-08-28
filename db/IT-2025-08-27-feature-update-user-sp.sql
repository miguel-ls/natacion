DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_update_user`$$

CREATE PROCEDURE `sp_update_user`(
    IN p_id_usuario INT,
    IN p_nombre VARCHAR(100),
    IN p_email VARCHAR(100)
)
BEGIN
    -- Este procedimiento actualiza los datos básicos de un usuario (sin incluir la contraseña).
    -- La contraseña se maneja en un procedimiento separado (sp_change_user_password)
    -- para mayor seguridad y separación de lógica.

    UPDATE `usuarios`
    SET
        `nombre` = p_nombre,
        `email` = p_email
    WHERE `id_usuario` = p_id_usuario;
END$$

DELIMITER ;
