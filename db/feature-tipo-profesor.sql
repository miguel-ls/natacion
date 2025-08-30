-- =================================================================
-- ARCHIVO DE SCRIPT PARA LA FUNCIONALIDAD DE TIPOS DE PROFESOR
-- =================================================================

--
-- 1. Creación de la nueva tabla `tipos_profesor`
--
CREATE TABLE IF NOT EXISTS `tipos_profesor` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `descripcion` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar algunos valores por defecto
INSERT INTO `tipos_profesor` (`descripcion`) VALUES
('NATACION'),
('GIMNASIO'),
('OTROS');


--
-- 2. Modificación de la tabla `profesores`
--
ALTER TABLE `profesores`
ADD COLUMN `id_tipo_profesor` INT NULL AFTER `apellidos`,
ADD CONSTRAINT `fk_profesores_tipos_profesor` FOREIGN KEY (`id_tipo_profesor`) REFERENCES `tipos_profesor`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;


--
-- 3. Procedimientos Almacenados para el CRUD de `tipos_profesor`
--

DELIMITER $$

-- Crear un nuevo tipo de profesor
CREATE PROCEDURE `sp_create_tipo_profesor`(IN p_descripcion VARCHAR(100))
BEGIN
    INSERT INTO tipos_profesor (descripcion) VALUES (p_descripcion);
END$$

-- Obtener todos los tipos de profesor
CREATE PROCEDURE `sp_get_all_tipos_profesor`()
BEGIN
    SELECT id, descripcion FROM tipos_profesor ORDER BY descripcion;
END$$

-- Obtener un tipo de profesor por ID
CREATE PROCEDURE `sp_get_tipo_profesor_by_id`(IN p_id INT)
BEGIN
    SELECT id, descripcion FROM tipos_profesor WHERE id = p_id;
END$$

-- Actualizar un tipo de profesor
CREATE PROCEDURE `sp_update_tipo_profesor`(IN p_id INT, IN p_descripcion VARCHAR(100))
BEGIN
    UPDATE tipos_profesor SET descripcion = p_descripcion WHERE id = p_id;
END$$

-- Eliminar un tipo de profesor
CREATE PROCEDURE `sp_delete_tipo_profesor`(IN p_id INT)
BEGIN
    DELETE FROM tipos_profesor WHERE id = p_id;
END$$

DELIMITER ;


--
-- 4. Actualización de los Procedimientos Almacenados de `profesores`
--

DELIMITER $$

-- Eliminar los procedimientos antiguos para poder recrearlos
DROP PROCEDURE IF EXISTS `sp_create_profesor`;
DROP PROCEDURE IF EXISTS `sp_update_profesor`;
DROP PROCEDURE IF EXISTS `sp_get_all_profesores`;
DROP PROCEDURE IF EXISTS `sp_get_profesor_by_id`;

-- Recrear `sp_create_profesor` con el nuevo campo
CREATE PROCEDURE `sp_create_profesor`(
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_id_tipo_profesor INT,
    IN p_documento_identidad VARCHAR(20),
    IN p_telefono VARCHAR(20),
    IN p_email VARCHAR(100),
    IN p_direccion VARCHAR(255),
    IN p_fecha_contratacion DATE
)
BEGIN
    INSERT INTO profesores (nombres, apellidos, id_tipo_profesor, documento_identidad, telefono, email, direccion, fecha_contratacion, estado)
    VALUES (p_nombres, p_apellidos, p_id_tipo_profesor, p_documento_identidad, p_telefono, p_email, p_direccion, p_fecha_contratacion, 'activo');
END$$

-- Recrear `sp_update_profesor` con el nuevo campo
CREATE PROCEDURE `sp_update_profesor`(
    IN p_id_profesor INT,
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_id_tipo_profesor INT,
    IN p_documento_identidad VARCHAR(20),
    IN p_telefono VARCHAR(20),
    IN p_email VARCHAR(100),
    IN p_direccion VARCHAR(255),
    IN p_fecha_contratacion DATE,
    IN p_estado ENUM('activo', 'inactivo')
)
BEGIN
    UPDATE profesores
    SET
        nombres = p_nombres,
        apellidos = p_apellidos,
        id_tipo_profesor = p_id_tipo_profesor,
        documento_identidad = p_documento_identidad,
        telefono = p_telefono,
        email = p_email,
        direccion = p_direccion,
        fecha_contratacion = p_fecha_contratacion,
        estado = p_estado
    WHERE id_profesor = p_id_profesor;
END$$

-- Recrear `sp_get_all_profesores` con el nuevo campo
CREATE PROCEDURE `sp_get_all_profesores`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT
        p.id_profesor,
        p.nombres,
        p.apellidos,
        tp.descripcion as tipo_profesor_nombre,
        p.documento_identidad,
        p.telefono,
        p.email,
        p.direccion,
        p.fecha_contratacion,
        p.estado
    FROM profesores p
    LEFT JOIN tipos_profesor tp ON p.id_tipo_profesor = tp.id
    WHERE p_search_term = ''
       OR p.nombres LIKE CONCAT('%', p_search_term, '%')
       OR p.apellidos LIKE CONCAT('%', p_search_term, '%')
       OR p.documento_identidad LIKE CONCAT('%', p_search_term, '%');
END$$

-- Recrear `sp_get_profesor_by_id` con el nuevo campo
CREATE PROCEDURE `sp_get_profesor_by_id`(IN p_id_profesor INT)
BEGIN
    SELECT
        p.id_profesor,
        p.nombres,
        p.apellidos,
        p.id_tipo_profesor,
        p.documento_identidad,
        p.telefono,
        p.email,
        p.direccion,
        p.fecha_contratacion,
        p.estado
    FROM profesores p
    WHERE p.id_profesor = p_id_profesor;
END$$

DELIMITER ;
