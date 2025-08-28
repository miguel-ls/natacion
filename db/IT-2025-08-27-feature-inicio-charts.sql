DELIMITER $$

-- Gráfico 1: Ventas mensuales por curso (año actual)
DROP PROCEDURE IF EXISTS `sp_get_ventas_por_curso_mensual_anual`$$
CREATE PROCEDURE `sp_get_ventas_por_curso_mensual_anual`()
BEGIN
    SELECT
        MONTH(m.fecha_matricula) AS mes,
        c.nombre AS curso_nombre,
        SUM(m.precio_final) AS total_ventas
    FROM matriculas m
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    WHERE YEAR(m.fecha_matricula) = YEAR(CURDATE()) AND m.estado != 'anulada'
    GROUP BY MONTH(m.fecha_matricula), c.nombre
    ORDER BY mes, curso_nombre;
END$$

-- Gráfico 2: Ventas totales por curso (año actual)
DROP PROCEDURE IF EXISTS `sp_get_ventas_por_curso_anual`$$
CREATE PROCEDURE `sp_get_ventas_por_curso_anual`()
BEGIN
    SELECT
        c.nombre AS curso_nombre,
        SUM(m.precio_final) AS total_ventas
    FROM matriculas m
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    WHERE YEAR(m.fecha_matricula) = YEAR(CURDATE()) AND m.estado != 'anulada'
    GROUP BY c.nombre;
END$$

-- Gráfico 3: Ventas totales por forma de pago (año actual)
DROP PROCEDURE IF EXISTS `sp_get_ventas_por_forma_pago_anual`$$
CREATE PROCEDURE `sp_get_ventas_por_forma_pago_anual`()
BEGIN
    SELECT
        fp.nombre AS forma_pago,
        SUM(m.precio_final) AS total_ventas
    FROM matriculas m
    JOIN formas_pago fp ON m.id_forma_pago = fp.id_forma_pago
    WHERE YEAR(m.fecha_matricula) = YEAR(CURDATE()) AND m.estado != 'anulada'
    GROUP BY fp.nombre;
END$$

-- Gráfico 4: Ventas totales por tipo de piscina (año actual)
DROP PROCEDURE IF EXISTS `sp_get_ventas_por_piscina_anual`$$
CREATE PROCEDURE `sp_get_ventas_por_piscina_anual`()
BEGIN
    SELECT
        tp.nombre AS tipo_piscina,
        SUM(m.precio_final) AS total_ventas
    FROM matriculas m
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN carriles ca ON h.id_carril = ca.id_carril
    JOIN piscinas pi ON ca.id_piscina = pi.id_piscina
    JOIN tipos_piscina tp ON pi.id_tipo_piscina = tp.id_tipo_piscina
    WHERE YEAR(m.fecha_matricula) = YEAR(CURDATE()) AND m.estado != 'anulada'
    GROUP BY tp.nombre;
END$$

DELIMITER ;
