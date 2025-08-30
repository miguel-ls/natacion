-- =================================================================
-- ARCHIVO CONSOLIDADO DE ACTUALIZACIÓN DE BASE DE DATOS
-- =================================================================
-- Este script contiene las versiones finales y correctas de todos los
-- procedimientos almacenados necesarios para las funcionalidades de
-- "Matrícula Múltiple" y "Calendario".
--
-- Ejecute este script en su base de datos `natacion_bd` para resolver
-- inconsistencias y asegurar que todo funcione correctamente.
-- =================================================================

-- -----------------------------------------------------------------
-- 1. Procedimiento para crear una matrícula (versión final)
--    Añade el parámetro `p_id_grupo_matricula`.
-- -----------------------------------------------------------------
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
    IN p_id_grupo_matricula INT
)
BEGIN
    DECLARE v_precio_final DECIMAL(10, 2);
    SET v_precio_final = p_precio_base - p_descuento;

    INSERT INTO matriculas (id_alumno, id_horario, id_usuario_admin, fecha_inicio, fecha_fin, precio_final, id_forma_pago, estado, observaciones, precio_base, descuento, id_grupo_matricula)
    VALUES (p_id_alumno, p_id_horario, p_id_usuario_admin, p_fecha_inicio, p_fecha_fin, v_precio_final, p_id_forma_pago, 'activa', p_observaciones, p_precio_base, p_descuento, p_id_grupo_matricula);

    SELECT LAST_INSERT_ID() as nueva_matricula_id;
END$$
DELIMITER ;

-- -----------------------------------------------------------------
-- 2. Procedimiento para obtener todos los profesores (versión final)
--    Añade la descripción del tipo de profesor.
-- -----------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_get_all_profesores`;
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
DELIMITER ;

-- -----------------------------------------------------------------
-- 3. Procedimiento para obtener grupos de matrícula (versión final)
--    Añade los filtros.
-- -----------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_get_all_matricula_grupos`;
CREATE PROCEDURE `sp_get_all_matricula_grupos`(
    IN p_id_alumno INT,
    IN p_fecha_desde DATE,
    IN p_fecha_hasta DATE
)
BEGIN
    SELECT
        mg.id_grupo_matricula,
        mg.fecha_creacion,
        CONCAT(a.nombres, ' ', a.apellidos) AS nombre_cliente
    FROM matricula_grupos mg
    JOIN alumnos a ON mg.id_alumno = a.id_alumno
    WHERE
        (p_id_alumno = 0 OR mg.id_alumno = p_id_alumno)
        AND (p_fecha_desde IS NULL OR mg.fecha_creacion >= p_fecha_desde)
        AND (p_fecha_hasta IS NULL OR mg.fecha_creacion <= p_fecha_hasta)
    ORDER BY mg.fecha_creacion DESC;
END$$
DELIMITER ;

-- -----------------------------------------------------------------
-- 4. Procedimiento para obtener detalles de un grupo de matrícula
-- -----------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_get_matricula_grupo_details`;
CREATE PROCEDURE `sp_get_matricula_grupo_details`(
    IN p_id_grupo_matricula INT
)
BEGIN
    SELECT
        mg.id_grupo_matricula,
        mg.fecha_creacion,
        a.id_alumno,
        CONCAT(a.nombres, ' ', a.apellidos) AS nombre_cliente
    FROM matricula_grupos mg
    JOIN alumnos a ON mg.id_alumno = a.id_alumno
    WHERE mg.id_grupo_matricula = p_id_grupo_matricula;

    SELECT
        m.id_matricula,
        c.nombre AS curso_nombre,
        m.fecha_inicio,
        m.fecha_fin,
        m.precio_final,
        m.estado
    FROM matriculas m
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    WHERE m.id_grupo_matricula = p_id_grupo_matricula
    ORDER BY c.nombre;
END$$
DELIMITER ;

-- -----------------------------------------------------------------
-- 5. Procedimiento para encontrar carriles libres
-- -----------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_find_free_lanes`;
CREATE PROCEDURE `sp_find_free_lanes`(
    IN `p_id_tipo_area` INT,
    IN `p_fecha_inicio` DATE,
    IN `p_fecha_fin` DATE,
    IN `p_hora_inicio` TIME,
    IN `p_hora_fin` TIME
)
BEGIN
    SELECT
        ca.id_carril,
        CONCAT(ca.descripcion, ' ', ca.numero_carril) AS sub_area_nombre,
        pi.nombre AS area_nombre
    FROM
        carriles ca
    JOIN
        piscinas pi ON ca.id_piscina = pi.id_piscina
    WHERE
        (p_id_tipo_area = 0 OR pi.id_tipo_piscina = p_id_tipo_area)
        AND NOT EXISTS (
            SELECT 1
            FROM horarios h
            WHERE
                h.id_carril = ca.id_carril
                AND (h.hora_inicio < p_hora_fin AND h.hora_fin > p_hora_inicio)
                AND (h.fecha_inicio <= p_fecha_fin AND h.fecha_fin >= p_fecha_inicio)
        );
END$$
DELIMITER ;

-- -----------------------------------------------------------------
-- 6. Procedimiento para obtener clases para el calendario
-- -----------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_get_clases_for_calendar`;
CREATE PROCEDURE `sp_get_clases_for_calendar`(
    IN p_start_date DATE,
    IN p_end_date DATE
)
BEGIN
    SELECT
        md.id_matricula_dia,
        c.nombre AS title,
        CONCAT(md.fecha_clase, 'T', h.hora_inicio) AS start_datetime,
        CONCAT(md.fecha_clase, 'T', h.hora_fin) AS end_datetime
    FROM matricula_dias md
    JOIN matriculas m ON md.id_matricula = m.id_matricula
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    WHERE
        md.fecha_clase BETWEEN p_start_date AND p_end_date
        AND m.estado IN ('activa', 'vigente');
END$$
DELIMITER ;
