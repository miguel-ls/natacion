-- =================================================================
-- ARCHIVO DE SCRIPT PARA LA FUNCIONALIDAD DE DESCRIPCIÓN EN SUB AREAS (CARRILES)
-- =================================================================

--
-- 1. Modificación de la tabla `carriles` para añadir `descripcion`
--
ALTER TABLE `carriles`
ADD COLUMN `descripcion` VARCHAR(100) NULL DEFAULT NULL AFTER `id_piscina`;


--
-- 2. Actualización de los Procedimientos Almacenados de `carriles`
--

DELIMITER $$

-- Eliminar los procedimientos antiguos para poder recrearlos
DROP PROCEDURE IF EXISTS `sp_create_carril`;
DROP PROCEDURE IF EXISTS `sp_update_carril`;
DROP PROCEDURE IF EXISTS `sp_get_all_carriles`;
DROP PROCEDURE IF EXISTS `sp_get_carril_by_id`;

-- Recrear `sp_create_carril` con el nuevo campo
CREATE PROCEDURE `sp_create_carril`(
    IN p_id_piscina INT,
    IN p_descripcion VARCHAR(100),
    IN p_numero_carril INT,
    IN p_capacidad_maxima INT
)
BEGIN
    INSERT INTO carriles (id_piscina, descripcion, numero_carril, capacidad_maxima)
    VALUES (p_id_piscina, p_descripcion, p_numero_carril, p_capacidad_maxima);
END$$

-- Recrear `sp_update_carril` con el nuevo campo
CREATE PROCEDURE `sp_update_carril`(
    IN p_id_carril INT,
    IN p_id_piscina INT,
    IN p_descripcion VARCHAR(100),
    IN p_numero_carril INT,
    IN p_capacidad_maxima INT
)
BEGIN
    UPDATE carriles
    SET
        id_piscina = p_id_piscina,
        descripcion = p_descripcion,
        numero_carril = p_numero_carril,
        capacidad_maxima = p_capacidad_maxima
    WHERE id_carril = p_id_carril;
END$$

-- Recrear `sp_get_all_carriles` con el nuevo campo
CREATE PROCEDURE `sp_get_all_carriles`()
BEGIN
    SELECT
        c.id_carril,
        c.id_piscina,
        c.descripcion,
        p.nombre AS piscina_nombre,
        c.numero_carril,
        c.capacidad_maxima
    FROM carriles c
    JOIN piscinas p ON c.id_piscina = p.id_piscina
    ORDER BY p.nombre, c.numero_carril;
END$$

-- Recrear `sp_get_carril_by_id` con el nuevo campo
CREATE PROCEDURE `sp_get_carril_by_id`(
    IN p_id_carril INT
)
BEGIN
    SELECT id_carril, id_piscina, descripcion, numero_carril, capacidad_maxima
    FROM carriles
    WHERE id_carril = p_id_carril;
END$$

DELIMITER ;
