-- =================================================================
-- Agrega la funcionalidad de descuentos a las matrículas (VERSIÓN CORREGIDA).
-- =================================================================

-- 1. Alterar la tabla de matrículas para añadir el campo de descuento y precio base.
-- Se elimina la columna de descuento anterior si existe y se añade la nueva estructura.
ALTER TABLE `matriculas`
DROP COLUMN IF EXISTS `descuento`,
ADD COLUMN `precio_base` DECIMAL(10, 2) NOT NULL AFTER `observaciones`,
ADD COLUMN `descuento` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER `precio_base`;


-- 2. Modificar el procedimiento almacenado para crear una matrícula (Lógica corregida)
DELIMITER $$
CREATE OR REPLACE PROCEDURE `sp_create_matricula`(
    IN p_id_alumno INT,
    IN p_id_horario INT,
    IN p_id_usuario_admin INT,
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_precio_base DECIMAL(10, 2), -- Se recibe el precio base
    IN p_descuento DECIMAL(10, 2),
    IN p_id_forma_pago INT,
    IN p_observaciones TEXT
)
BEGIN
    -- El precio final se calcula aquí para asegurar consistencia
    DECLARE v_precio_final DECIMAL(10, 2);
    SET v_precio_final = p_precio_base - p_descuento;

    INSERT INTO matriculas (
        id_alumno, id_horario, id_usuario_admin, fecha_inicio, fecha_fin,
        precio_final, precio_base, descuento,
        id_forma_pago, estado, observaciones
    )
    VALUES (
        p_id_alumno, p_id_horario, p_id_usuario_admin, p_fecha_inicio, p_fecha_fin,
        v_precio_final, p_precio_base, p_descuento,
        p_id_forma_pago, 'activa', p_observaciones
    );

    SELECT LAST_INSERT_ID() as nueva_matricula_id;
END$$
DELIMITER ;


-- 3. Modificar el procedimiento de reporte de ventas para incluir todos los campos necesarios
DELIMITER $$
CREATE OR REPLACE PROCEDURE `sp_reporte_ventas`(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_id_alumno INT,
    IN p_id_curso INT,
    IN p_id_forma_pago INT
)
BEGIN
    SELECT
        m.fecha_matricula,
        m.fecha_inicio, -- Añadido
        m.fecha_fin,    -- Añadido
        CONCAT(a.nombres, ' ', a.apellidos) AS alumno_nombre,
        c.nombre AS curso_nombre,
        fp.nombre AS forma_pago_nombre,
        m.precio_base,  -- Añadido
        m.descuento,
        m.precio_final
    FROM matriculas m
    JOIN alumnos a ON m.id_alumno = a.id_alumno
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    LEFT JOIN formas_pago fp ON m.id_forma_pago = fp.id_forma_pago
    WHERE
        (DATE(m.fecha_matricula) BETWEEN p_fecha_inicio AND p_fecha_fin)
        AND (p_id_alumno = 0 OR m.id_alumno = p_id_alumno)
        AND (p_id_curso = 0 OR h.id_curso = p_id_curso)
        AND (p_id_forma_pago = 0 OR m.id_forma_pago = p_id_forma_pago)
        AND m.estado != 'anulada'
    ORDER BY m.fecha_matricula DESC;
END$$
DELIMITER ;

-- 4. Modificar el SP para obtener detalles de la matrícula para incluir el precio base
-- (Este no se usa en las vistas modificadas, pero es bueno mantenerlo consistente)
DELIMITER $$
CREATE OR REPLACE PROCEDURE `sp_get_matricula_details_by_id`(
    IN p_id_matricula INT
)
BEGIN
    SELECT
        m.id_matricula,
        a.id_alumno,
        CONCAT(a.nombres, ' ', a.apellidos) AS alumno_nombre,
        c.nombre AS curso_nombre,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        CONCAT(pi.nombre, ' - Carril ', ca.numero_carril) as carril_nombre,
        th.nombre as tipo_horario_nombre,
        h.hora_inicio,
        h.hora_fin,
        m.fecha_inicio,
        m.fecha_fin,
        m.precio_base, -- Añadido
        m.descuento,
        m.precio_final,
        fp.nombre as forma_pago_nombre,
        m.estado,
        m.observaciones,
        m.fecha_matricula,
        u.nombre as admin_nombre
    FROM matriculas m
    JOIN alumnos a ON m.id_alumno = a.id_alumno
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN profesores p ON h.id_profesor = p.id_profesor
    JOIN carriles ca ON h.id_carril = ca.id_carril
    JOIN piscinas pi ON ca.id_piscina = pi.id_piscina
    JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    LEFT JOIN formas_pago fp ON m.id_forma_pago = fp.id_forma_pago
    JOIN usuarios u ON m.id_usuario_admin = u.id_usuario
    WHERE m.id_matricula = p_id_matricula;
END$$
DELIMITER ;
