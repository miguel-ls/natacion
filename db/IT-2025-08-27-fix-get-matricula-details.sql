DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_get_matricula_details_by_id`$$

CREATE PROCEDURE `sp_get_matricula_details_by_id`(
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
        h.id_horario,
        h.hora_inicio,
        h.hora_fin,
        m.fecha_inicio,
        m.fecha_fin,
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
