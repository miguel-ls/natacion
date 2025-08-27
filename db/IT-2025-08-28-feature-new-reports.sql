-- =================================================================
-- Nuevos reportes de ventas agregados.
-- =================================================================

-- 1. Reporte de ventas agrupado por forma de pago
DELIMITER $$
CREATE PROCEDURE `sp_reporte_ventas_por_forma_pago`(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    SELECT
        fp.nombre AS forma_pago_nombre,
        COUNT(m.id_matricula) AS cantidad_transacciones,
        SUM(m.precio_base) AS total_base,
        SUM(m.descuento) AS total_descuentos,
        SUM(m.precio_final) AS total_ventas
    FROM matriculas m
    JOIN formas_pago fp ON m.id_forma_pago = fp.id_forma_pago
    WHERE DATE(m.fecha_matricula) BETWEEN p_fecha_inicio AND p_fecha_fin
      AND m.estado != 'anulada'
    GROUP BY fp.nombre
    ORDER BY total_ventas DESC;
END$$
DELIMITER ;

-- 2. Reporte de ventas agrupado por curso
DELIMITER $$
CREATE PROCEDURE `sp_reporte_ventas_por_curso`(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    SELECT
        c.nombre AS curso_nombre,
        COUNT(m.id_matricula) AS cantidad_matriculas,
        SUM(m.precio_base) AS total_base,
        SUM(m.descuento) AS total_descuentos,
        SUM(m.precio_final) AS total_ventas
    FROM matriculas m
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    WHERE DATE(m.fecha_matricula) BETWEEN p_fecha_inicio AND p_fecha_fin
      AND m.estado != 'anulada'
    GROUP BY c.nombre
    ORDER BY total_ventas DESC;
END$$
DELIMITER ;

-- 3. Reporte de ventas agrupado por profesor
DELIMITER $$
CREATE PROCEDURE `sp_reporte_ventas_por_profesor`(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    SELECT
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        COUNT(m.id_matricula) AS cantidad_matriculas,
        SUM(m.precio_base) AS total_base,
        SUM(m.descuento) AS total_descuentos,
        SUM(m.precio_final) AS total_ventas
    FROM matriculas m
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN profesores p ON h.id_profesor = p.id_profesor
    WHERE DATE(m.fecha_matricula) BETWEEN p_fecha_inicio AND p_fecha_fin
      AND m.estado != 'anulada'
    GROUP BY profesor_nombre
    ORDER BY total_ventas DESC;
END$$
DELIMITER ;

-- 4. Reporte de ventas agrupado por piscina y carril
DELIMITER $$
CREATE PROCEDURE `sp_reporte_ventas_por_piscina_carril`(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    SELECT
        pi.nombre AS piscina_nombre,
        ca.numero_carril,
        COUNT(m.id_matricula) AS cantidad_matriculas,
        SUM(m.precio_base) AS total_base,
        SUM(m.descuento) AS total_descuentos,
        SUM(m.precio_final) AS total_ventas
    FROM matriculas m
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN carriles ca ON h.id_carril = ca.id_carril
    JOIN piscinas pi ON ca.id_piscina = pi.id_piscina
    WHERE DATE(m.fecha_matricula) BETWEEN p_fecha_inicio AND p_fecha_fin
      AND m.estado != 'anulada'
    GROUP BY pi.nombre, ca.numero_carril
    ORDER BY piscina_nombre, ca.numero_carril;
END$$
DELIMITER ;
