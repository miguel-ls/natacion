-- =================================================================
-- ARCHIVO DE SCRIPT PARA NUEVOS GRÁFICOS Y FILTROS
-- =================================================================

DELIMITER $$

--
-- 1. Nuevo procedimiento para el gráfico de ventas por tipo de curso
--
CREATE PROCEDURE `sp_get_ventas_by_tipo_curso`(
    IN p_year INT,
    IN p_month INT
)
BEGIN
    SELECT
        tp.descripcion AS tipo_curso,
        SUM(m.precio_final) AS total_ventas
    FROM matriculas m
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN tipos_profesor tp ON c.id_tipo_profesor = tp.id
    WHERE
        (p_year = 0 OR YEAR(m.fecha_matricula) = p_year)
        AND (p_month = 0 OR MONTH(m.fecha_matricula) = p_month)
        AND m.estado != 'anulada'
    GROUP BY tp.descripcion;
END$$


--
-- 2. Actualización de `sp_reporte_ventas_por_piscina_carril` para filtrar por tipo de curso 'NATACION'
--
DROP PROCEDURE IF EXISTS `sp_reporte_ventas_por_piscina_carril`;

CREATE PROCEDURE `sp_reporte_ventas_por_piscina_carril`(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    DECLARE v_id_tipo_natacion INT;

    -- Asumimos que el tipo de curso para natación tiene la descripción 'NATACION'
    SELECT id INTO v_id_tipo_natacion FROM tipos_profesor WHERE descripcion = 'NATACION' LIMIT 1;

    SELECT
        pi.nombre AS piscina_nombre,
        CONCAT(ca.descripcion, ' ', ca.numero_carril) AS carril_nombre,
        SUM(m.precio_final) AS total_ventas
    FROM matriculas m
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN carriles ca ON h.id_carril = ca.id_carril
    JOIN piscinas pi ON ca.id_piscina = pi.id_piscina
    JOIN cursos c ON h.id_curso = c.id_curso
    WHERE
        (m.fecha_matricula BETWEEN p_fecha_inicio AND p_fecha_fin)
        AND m.estado != 'anulada'
        AND (v_id_tipo_natacion IS NULL OR c.id_tipo_profesor = v_id_tipo_natacion)
    GROUP BY pi.nombre, carril_nombre
    ORDER BY total_ventas DESC;
END$$


--
-- 3. Actualización de `sp_get_matriculas_filtradas` para filtrar por tipo de curso
--
DROP PROCEDURE IF EXISTS `sp_get_matriculas_filtradas`;

CREATE PROCEDURE `sp_get_matriculas_filtradas`(
    IN p_id_alumno INT,
    IN p_id_curso INT,
    IN p_id_tipo_curso INT,
    IN p_fecha_inicio_desde DATE,
    IN p_fecha_inicio_hasta DATE,
    IN p_estado VARCHAR(20)
)
BEGIN
    SELECT
        m.id_matricula,
        CONCAT(a.nombres, ' ', a.apellidos) AS alumno_nombre,
        c.nombre AS curso_nombre,
        m.fecha_inicio,
        m.fecha_fin,
        m.precio_final,
        m.estado,
        u.nombre as admin_nombre,
        m.fecha_matricula,
        (SELECT pc.fecha_inicio FROM precios_cursos pc WHERE pc.id_curso = c.id_curso AND m.fecha_matricula BETWEEN pc.fecha_inicio AND pc.fecha_fin ORDER BY pc.id_tipo_precio DESC LIMIT 1) AS fecha_inicio_curso,
        (SELECT pc.fecha_fin FROM precios_cursos pc WHERE pc.id_curso = c.id_curso AND m.fecha_matricula BETWEEN pc.fecha_inicio AND pc.fecha_fin ORDER BY pc.id_tipo_precio DESC LIMIT 1) AS fecha_fin_curso
    FROM matriculas m
    JOIN alumnos a ON m.id_alumno = a.id_alumno
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN usuarios u ON m.id_usuario_admin = u.id_usuario
    WHERE
        (p_id_alumno = 0 OR m.id_alumno = p_id_alumno)
        AND (p_id_curso = 0 OR c.id_curso = p_id_curso)
        AND (p_id_tipo_curso = 0 OR c.id_tipo_profesor = p_id_tipo_curso)
        AND (p_fecha_inicio_desde IS NULL OR m.fecha_inicio >= p_fecha_inicio_desde)
        AND (p_fecha_inicio_hasta IS NULL OR m.fecha_inicio <= p_fecha_inicio_hasta)
        AND (p_estado = 'Todos' OR m.estado = p_estado)
    ORDER BY m.fecha_matricula DESC;
END$$


DELIMITER ;
