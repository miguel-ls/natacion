-- =================================================================
-- Archivo de Script para IT
-- Tarea: Mejorar filtros en la página de Asistencia de Profesores
-- Fecha: 2025-08-26
-- Autor: Jules
-- =================================================================

-- Resumen de Cambios:
-- Se crea un nuevo procedimiento almacenado `sp_listar_horarios_profesor_filtrado`
-- para la página de control de asistencia de profesores.
-- Este procedimiento permite filtrar los horarios por profesor, curso y un estado calculado.

DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_listar_horarios_profesor_filtrado`$$

CREATE PROCEDURE `sp_listar_horarios_profesor_filtrado`(
    IN p_id_profesor INT,   -- 0 para todos
    IN p_id_curso INT,      -- 0 para todos
    IN p_estado VARCHAR(20) -- 'Todos', 'Futuro', 'En Curso', 'Finalizado'
)
BEGIN
    SELECT
        h.id_horario,
        c.nombre AS curso_nombre,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        h.id_profesor,
        c.id_curso,
        CONCAT(pi.nombre, ' - Carril ', ca.numero_carril) as carril_nombre,
        th.nombre as tipo_horario_nombre,
        h.hora_inicio,
        h.hora_fin,
        h.fecha_inicio,
        h.fecha_fin,
        CASE
            WHEN CURDATE() < h.fecha_inicio THEN 'Futuro'
            WHEN CURDATE() > h.fecha_fin THEN 'Finalizado'
            ELSE 'En Curso'
        END AS estado_calculado
    FROM horarios h
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN profesores p ON h.id_profesor = p.id_profesor
    JOIN carriles ca ON h.id_carril = ca.id_carril
    JOIN piscinas pi ON ca.id_piscina = pi.id_piscina
    JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    WHERE
        (p_id_profesor = 0 OR h.id_profesor = p_id_profesor)
        AND (p_id_curso = 0 OR h.id_curso = p_id_curso)
    HAVING
        (p_estado = 'Todos' OR estado_calculado = p_estado)
    ORDER BY
        h.fecha_inicio DESC,
        profesor_nombre;
END$$

DELIMITER ;

-- Nota para IT:
-- Este procedimiento no modifica ninguna tabla. Es seguro de ejecutar.
-- Reemplaza la lógica de filtrado que antes se intentaba hacer en la aplicación
-- por una más robusta y centralizada en la base de datos.
-- El procedimiento anterior `sp_get_all_horarios_details` no se modifica y sigue
-- disponible para otras partes del sistema que lo puedan necesitar.
