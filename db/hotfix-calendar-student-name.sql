-- =================================================================
-- HOTFIX: SCRIPT PARA AÑADIR NOMBRE DE ALUMNO AL CALENDARIO
-- =================================================================
-- Este script actualiza el procedimiento almacenado del calendario
-- para que devuelva el nombre del alumno.
-- =================================================================

DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_get_clases_for_calendar`;
CREATE PROCEDURE `sp_get_clases_for_calendar`(
    IN p_start_date DATE,
    IN p_end_date DATE
)
BEGIN
    SELECT
        md.id_matricula_dia,
        c.id_curso,
        c.nombre AS curso_nombre,
        h.hora_inicio,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        pi.nombre AS area_nombre,
        ca.descripcion AS sub_area_descripcion,
        ca.numero_carril AS sub_area_numero,
        CONCAT(al.nombres, ' ', al.apellidos) AS alumno_nombre,
        CONCAT(md.fecha_clase, 'T', h.hora_inicio) AS start_datetime,
        CONCAT(md.fecha_clase, 'T', h.hora_fin) AS end_datetime
    FROM matricula_dias md
    JOIN matriculas m ON md.id_matricula = m.id_matricula
    JOIN alumnos al ON m.id_alumno = al.id_alumno
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN profesores p ON h.id_profesor = p.id_profesor
    JOIN carriles ca ON h.id_carril = ca.id_carril
    JOIN piscinas pi ON ca.id_piscina = pi.id_piscina
    WHERE
        md.fecha_clase BETWEEN p_start_date AND p_end_date
        AND m.estado IN ('activa', 'vigente');
END$$
DELIMITER ;
