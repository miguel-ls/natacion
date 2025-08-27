-- Este script corrige la inconsistencia de datos de asistencia de alumnos
-- eliminando la tabla y el procedimiento almacenado redundantes que se crearon
-- para la nueva página de "Asistencia Alumno".
-- La funcionalidad ahora utilizará la tabla `matricula_dias` y el SP `sp_update_asistencia_alumno`
-- que ya existían y eran utilizados por la página de "Detalles de la Matrícula".

-- 1. Eliminar la tabla de asistencias de alumnos redundante
DROP TABLE IF EXISTS `asistencias_alumnos`;

-- 2. Eliminar el procedimiento almacenado redundante
DROP PROCEDURE IF EXISTS `sp_create_or_update_asistencia_alumno`;

-- NOTA: El procedimiento `sp_listar_matriculas_alumno_filtrado` se conserva
-- porque es utilizado por la página de índice de asistencia de alumnos para
-- listar las matrículas, y no depende de la tabla eliminada.
