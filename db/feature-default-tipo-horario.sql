-- Insertar un tipo de horario genérico para reservas de matrícula múltiple si no existe
INSERT INTO tipos_horario (id_tipo_horario, nombre, dias_semana)
SELECT 100, 'Reserva Individual', ''
WHERE NOT EXISTS (SELECT 1 FROM tipos_horario WHERE id_tipo_horario = 100);
