DELIMITER $$

-- =============================================
-- Procedimientos para la tabla `tipos_piscina`
-- =============================================

-- Crear un nuevo tipo de piscina
CREATE PROCEDURE `sp_create_tipo_piscina`(
    IN p_nombre VARCHAR(50),
    IN p_descripcion TEXT
)
BEGIN
    INSERT INTO tipos_piscina (nombre, descripcion) VALUES (p_nombre, p_descripcion);
END$$

-- Obtener todos los tipos de piscina
CREATE PROCEDURE `sp_get_all_tipos_piscina`()
BEGIN
    SELECT id_tipo_piscina, nombre, descripcion FROM tipos_piscina;
END$$

-- Obtener un tipo de piscina por ID
CREATE PROCEDURE `sp_get_tipo_piscina_by_id`(
    IN p_id_tipo_piscina INT
)
BEGIN
    SELECT id_tipo_piscina, nombre, descripcion FROM tipos_piscina WHERE id_tipo_piscina = p_id_tipo_piscina;
END$$

-- Actualizar un tipo de piscina
CREATE PROCEDURE `sp_update_tipo_piscina`(
    IN p_id_tipo_piscina INT,
    IN p_nombre VARCHAR(50),
    IN p_descripcion TEXT
)
BEGIN
    UPDATE tipos_piscina
    SET nombre = p_nombre, descripcion = p_descripcion
    WHERE id_tipo_piscina = p_id_tipo_piscina;
END$$

-- Eliminar un tipo de piscina
CREATE PROCEDURE `sp_delete_tipo_piscina`(
    IN p_id_tipo_piscina INT
)
BEGIN
    DELETE FROM tipos_piscina WHERE id_tipo_piscina = p_id_tipo_piscina;
END$$

-- =============================================
-- Procedimientos para la tabla `cursos` (MODIFICADOS)
-- =============================================

-- Crear un nuevo curso
CREATE PROCEDURE `sp_create_curso`(
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT
)
BEGIN
    INSERT INTO cursos (nombre, descripcion) VALUES (p_nombre, p_descripcion);
END$$

-- Obtener todos los cursos
CREATE PROCEDURE `sp_get_all_cursos`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT id_curso, nombre, descripcion
    FROM cursos
    WHERE p_search_term = ''
       OR nombre LIKE CONCAT('%', p_search_term, '%')
       OR descripcion LIKE CONCAT('%', p_search_term, '%');
END$$

-- Obtener un curso por ID
CREATE PROCEDURE `sp_get_curso_by_id`(
    IN p_id_curso INT
)
BEGIN
    SELECT id_curso, nombre, descripcion FROM cursos WHERE id_curso = p_id_curso;
END$$

-- Actualizar un curso
CREATE PROCEDURE `sp_update_curso`(
    IN p_id_curso INT,
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT
)
BEGIN
    UPDATE cursos
    SET nombre = p_nombre, descripcion = p_descripcion
    WHERE id_curso = p_id_curso;
END$$

-- Eliminar un curso
CREATE PROCEDURE `sp_delete_curso`(
    IN p_id_curso INT
)
BEGIN
    DELETE FROM cursos WHERE id_curso = p_id_curso;
END$$

-- =============================================
-- NUEVOS Procedimientos para la tabla `tipos_precio`
-- =============================================
CREATE PROCEDURE `sp_create_tipo_precio`(IN p_nombre VARCHAR(50), IN p_descripcion TEXT)
BEGIN INSERT INTO tipos_precio (nombre, descripcion) VALUES (p_nombre, p_descripcion); END$$

CREATE PROCEDURE `sp_get_all_tipos_precio`()
BEGIN SELECT * FROM tipos_precio; END$$

CREATE PROCEDURE `sp_get_tipo_precio_by_id`(IN p_id INT)
BEGIN SELECT * FROM tipos_precio WHERE id_tipo_precio = p_id; END$$

CREATE PROCEDURE `sp_update_tipo_precio`(IN p_id INT, IN p_nombre VARCHAR(50), IN p_descripcion TEXT)
BEGIN UPDATE tipos_precio SET nombre = p_nombre, descripcion = p_descripcion WHERE id_tipo_precio = p_id; END$$

CREATE PROCEDURE `sp_delete_tipo_precio`(IN p_id INT)
BEGIN DELETE FROM tipos_precio WHERE id_tipo_precio = p_id; END$$

-- =============================================
-- NUEVOS Procedimientos para la tabla `precios_cursos`
-- =============================================
CREATE PROCEDURE `sp_create_precio_curso`(IN p_id_curso INT, IN p_id_tipo_precio INT, IN p_precio DECIMAL(10, 2), IN p_fecha_inicio DATE, IN p_fecha_fin DATE)
BEGIN INSERT INTO precios_cursos (id_curso, id_tipo_precio, precio, fecha_inicio, fecha_fin) VALUES (p_id_curso, p_id_tipo_precio, p_precio, p_fecha_inicio, p_fecha_fin); END$$

CREATE PROCEDURE `sp_get_all_precios_cursos`()
BEGIN SELECT pc.id_precio_curso, c.nombre as curso_nombre, tp.nombre as tipo_precio_nombre, pc.precio, pc.fecha_inicio, pc.fecha_fin FROM precios_cursos pc JOIN cursos c ON pc.id_curso = c.id_curso JOIN tipos_precio tp ON pc.id_tipo_precio = tp.id_tipo_precio ORDER BY pc.fecha_inicio DESC; END$$

CREATE PROCEDURE `sp_get_precio_curso_by_id`(IN p_id INT)
BEGIN SELECT * FROM precios_cursos WHERE id_precio_curso = p_id; END$$

CREATE PROCEDURE `sp_update_precio_curso`(IN p_id INT, IN p_id_curso INT, IN p_id_tipo_precio INT, IN p_precio DECIMAL(10, 2), IN p_fecha_inicio DATE, IN p_fecha_fin DATE)
BEGIN UPDATE precios_cursos SET id_curso = p_id_curso, id_tipo_precio = p_id_tipo_precio, precio = p_precio, fecha_inicio = p_fecha_inicio, fecha_fin = p_fecha_fin WHERE id_precio_curso = p_id; END$$

CREATE PROCEDURE `sp_delete_precio_curso`(IN p_id INT)
BEGIN DELETE FROM precios_cursos WHERE id_precio_curso = p_id; END$$

-- =============================================
-- NUEVO Procedimiento para obtener precio vigente
-- =============================================
CREATE PROCEDURE `sp_get_precio_vigente_por_curso`(IN p_id_curso INT, IN p_fecha_actual DATE)
BEGIN SELECT precio FROM precios_cursos WHERE id_curso = p_id_curso AND p_fecha_actual BETWEEN fecha_inicio AND fecha_fin ORDER BY id_tipo_precio DESC LIMIT 1; END$$

-- =============================================
-- Procedimientos para la tabla `profesores`
-- =============================================

-- Crear un nuevo profesor
CREATE PROCEDURE `sp_create_profesor`(
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_documento_identidad VARCHAR(20),
    IN p_telefono VARCHAR(20),
    IN p_email VARCHAR(100),
    IN p_direccion VARCHAR(255),
    IN p_fecha_contratacion DATE
)
BEGIN
    INSERT INTO profesores (nombres, apellidos, documento_identidad, telefono, email, direccion, fecha_contratacion, estado)
    VALUES (p_nombres, p_apellidos, p_documento_identidad, p_telefono, p_email, p_direccion, p_fecha_contratacion, 'activo');
END$$

-- Obtener todos los profesores
CREATE PROCEDURE `sp_get_all_profesores`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT id_profesor, nombres, apellidos, documento_identidad, telefono, email, direccion, fecha_contratacion, estado
    FROM profesores
    WHERE p_search_term = ''
       OR nombres LIKE CONCAT('%', p_search_term, '%')
       OR apellidos LIKE CONCAT('%', p_search_term, '%')
       OR documento_identidad LIKE CONCAT('%', p_search_term, '%')
       OR email LIKE CONCAT('%', p_search_term, '%');
END$$

-- Obtener un profesor por ID
CREATE PROCEDURE `sp_get_profesor_by_id`(IN p_id_profesor INT)
BEGIN
    SELECT id_profesor, nombres, apellidos, documento_identidad, telefono, email, direccion, fecha_contratacion, estado FROM profesores WHERE id_profesor = p_id_profesor;
END$$

-- Actualizar un profesor
CREATE PROCEDURE `sp_update_profesor`(
    IN p_id_profesor INT,
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_documento_identidad VARCHAR(20),
    IN p_telefono VARCHAR(20),
    IN p_email VARCHAR(100),
    IN p_direccion VARCHAR(255),
    IN p_fecha_contratacion DATE,
    IN p_estado ENUM('activo', 'inactivo')
)
BEGIN
    UPDATE profesores
    SET nombres = p_nombres,
        apellidos = p_apellidos,
        documento_identidad = p_documento_identidad,
        telefono = p_telefono,
        email = p_email,
        direccion = p_direccion,
        fecha_contratacion = p_fecha_contratacion,
        estado = p_estado
    WHERE id_profesor = p_id_profesor;
END$$

-- Eliminar un profesor (cambio de estado a inactivo)
CREATE PROCEDURE `sp_delete_profesor`(IN p_id_profesor INT)
BEGIN
    UPDATE profesores SET estado = 'inactivo' WHERE id_profesor = p_id_profesor;
END$$

-- =============================================
-- Procedimientos para la tabla `alumnos`
-- =============================================

-- Crear un nuevo alumno
CREATE PROCEDURE `sp_create_alumno`(
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_documento_identidad VARCHAR(20),
    IN p_fecha_nacimiento DATE,
    IN p_grupo_sanguineo VARCHAR(5),
    IN p_direccion VARCHAR(255),
    IN p_telefono VARCHAR(20),
    IN p_email VARCHAR(100),
    IN p_nombre_padre_tutor VARCHAR(200),
    IN p_telefono_emergencia VARCHAR(20)
)
BEGIN
    INSERT INTO alumnos (nombres, apellidos, documento_identidad, fecha_nacimiento, grupo_sanguineo, direccion, telefono, email, nombre_padre_tutor, telefono_emergencia)
    VALUES (p_nombres, p_apellidos, p_documento_identidad, p_fecha_nacimiento, p_grupo_sanguineo, p_direccion, p_telefono, p_email, p_nombre_padre_tutor, p_telefono_emergencia);
END$$

-- Obtener todos los alumnos
CREATE PROCEDURE `sp_get_all_alumnos`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT id_alumno, nombres, apellidos, documento_identidad, fecha_nacimiento, grupo_sanguineo, direccion, telefono, email, nombre_padre_tutor, telefono_emergencia, fecha_registro
    FROM alumnos
    WHERE p_search_term = ''
       OR nombres LIKE CONCAT('%', p_search_term, '%')
       OR apellidos LIKE CONCAT('%', p_search_term, '%')
       OR documento_identidad LIKE CONCAT('%', p_search_term, '%');
END$$

-- Obtener un alumno por ID
CREATE PROCEDURE `sp_get_alumno_by_id`(IN p_id_alumno INT)
BEGIN
    SELECT id_alumno, nombres, apellidos, documento_identidad, fecha_nacimiento, grupo_sanguineo, direccion, telefono, email, nombre_padre_tutor, telefono_emergencia, fecha_registro
    FROM alumnos WHERE id_alumno = p_id_alumno;
END$$

-- Actualizar un alumno
CREATE PROCEDURE `sp_update_alumno`(
    IN p_id_alumno INT,
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_documento_identidad VARCHAR(20),
    IN p_fecha_nacimiento DATE,
    IN p_grupo_sanguineo VARCHAR(5),
    IN p_direccion VARCHAR(255),
    IN p_telefono VARCHAR(20),
    IN p_email VARCHAR(100),
    IN p_nombre_padre_tutor VARCHAR(200),
    IN p_telefono_emergencia VARCHAR(20)
)
BEGIN
    UPDATE alumnos
    SET nombres = p_nombres,
        apellidos = p_apellidos,
        documento_identidad = p_documento_identidad,
        fecha_nacimiento = p_fecha_nacimiento,
        grupo_sanguineo = p_grupo_sanguineo,
        direccion = p_direccion,
        telefono = p_telefono,
        email = p_email,
        nombre_padre_tutor = p_nombre_padre_tutor,
        telefono_emergencia = p_telefono_emergencia
    WHERE id_alumno = p_id_alumno;
END$$

-- Eliminar un alumno
CREATE PROCEDURE `sp_delete_alumno`(IN p_id_alumno INT)
BEGIN
    DELETE FROM alumnos WHERE id_alumno = p_id_alumno;
END$$

-- =============================================
-- Procedimientos para la tabla `usuarios`
-- =============================================
-- Crear un nuevo usuario
CREATE PROCEDURE `sp_create_user`(
    IN p_nombre VARCHAR(100),
    IN p_email VARCHAR(100),
    IN p_password VARCHAR(255)
)
BEGIN
    INSERT INTO usuarios (nombre, email, password, rol) VALUES (p_nombre, p_email, p_password, 'administrador');
END$$

-- Obtener un usuario por email para el login
CREATE PROCEDURE `sp_get_user_by_email`(
    IN p_email VARCHAR(100)
)
BEGIN
    SELECT id_usuario, nombre, email, password, rol FROM usuarios WHERE email = p_email;
END$$

-- Actualizar código 2FA
CREATE PROCEDURE `sp_update_user_2fa_code`(
    IN p_id_usuario INT,
    IN p_auth_code VARCHAR(10)
)
BEGIN
    UPDATE usuarios SET auth_code_2fa = p_auth_code WHERE id_usuario = p_id_usuario;
END$$

-- Obtener un usuario por ID
CREATE PROCEDURE `sp_get_user_by_id`(
    IN p_id_usuario INT
)
BEGIN
    SELECT id_usuario, nombre, email, rol, auth_code_2fa FROM usuarios WHERE id_usuario = p_id_usuario;
END$$

-- Obtener todos los usuarios
CREATE PROCEDURE `sp_get_all_users`(IN p_search_term VARCHAR(255))
BEGIN
    SELECT id_usuario, nombre, email, rol
    FROM usuarios
    WHERE p_search_term = ''
       OR nombre LIKE CONCAT('%', p_search_term, '%')
       OR email LIKE CONCAT('%', p_search_term, '%');
END$$

-- =============================================
-- Procedimientos para lógica de negocio (Matrícula)
-- =============================================

-- Verificar vacantes en un horario
CREATE PROCEDURE `sp_check_vacantes`(
    IN p_id_horario INT,
    OUT p_vacantes INT
)
BEGIN
    DECLARE v_capacidad_maxima INT;
    DECLARE v_matriculados INT;

    SELECT c.capacidad_maxima INTO v_capacidad_maxima
    FROM horarios h
    JOIN carriles c ON h.id_carril = c.id_carril
    WHERE h.id_horario = p_id_horario;

    SELECT COUNT(*) INTO v_matriculados
    FROM matriculas
    WHERE id_horario = p_id_horario AND estado IN ('activa', 'vigente');

    SET p_vacantes = v_capacidad_maxima - v_matriculados;
END$$

-- Crear una nueva matrícula
CREATE PROCEDURE `sp_create_matricula`(
    IN p_id_alumno INT,
    IN p_id_horario INT,
    IN p_id_usuario_admin INT,
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_precio_final DECIMAL(10, 2),
    IN p_id_forma_pago INT,
    IN p_observaciones TEXT
)
BEGIN
    INSERT INTO matriculas (id_alumno, id_horario, id_usuario_admin, fecha_inicio, fecha_fin, precio_final, id_forma_pago, estado, observaciones)
    VALUES (p_id_alumno, p_id_horario, p_id_usuario_admin, p_fecha_inicio, p_fecha_fin, p_precio_final, p_id_forma_pago, 'activa', p_observaciones);

    SELECT LAST_INSERT_ID() as nueva_matricula_id;
END$$

-- Generar los días de clase para una matrícula
CREATE PROCEDURE `sp_generate_dias_clase`(
    IN p_id_matricula INT,
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_dias_semana VARCHAR(50) -- Ej: '2,4,6' para L,M,V (Formato DAYOFWEEK)
)
BEGIN
    DECLARE v_current_date DATE;
    SET v_current_date = p_fecha_inicio;

    WHILE v_current_date <= p_fecha_fin DO
        IF FIND_IN_SET(DAYOFWEEK(v_current_date), p_dias_semana) > 0 THEN
            INSERT INTO matricula_dias (id_matricula, fecha_clase, estado)
            VALUES (p_id_matricula, v_current_date, 'programada');
        END IF;
        SET v_current_date = DATE_ADD(v_current_date, INTERVAL 1 DAY);
    END WHILE;
END$$

DELIMITER $$

-- =============================================
-- Procedimientos para la tabla `piscinas`
-- =============================================

-- Crear una nueva piscina
CREATE PROCEDURE `sp_create_piscina`(
    IN p_nombre VARCHAR(100),
    IN p_id_tipo_piscina INT
)
BEGIN
    INSERT INTO piscinas (nombre, id_tipo_piscina) VALUES (p_nombre, p_id_tipo_piscina);
END$$

-- Obtener todas las piscinas con el nombre de su tipo
CREATE PROCEDURE `sp_get_all_piscinas`()
BEGIN
    SELECT
        p.id_piscina,
        p.nombre,
        p.id_tipo_piscina,
        tp.nombre AS tipo_piscina_nombre
    FROM piscinas p
    LEFT JOIN tipos_piscina tp ON p.id_tipo_piscina = tp.id_tipo_piscina
    ORDER BY p.id_piscina;
END$$

-- Obtener una piscina por ID
CREATE PROCEDURE `sp_get_piscina_by_id`(
    IN p_id_piscina INT
)
BEGIN
    SELECT id_piscina, nombre, id_tipo_piscina FROM piscinas WHERE id_piscina = p_id_piscina;
END$$

-- Actualizar una piscina
CREATE PROCEDURE `sp_update_piscina`(
    IN p_id_piscina INT,
    IN p_nombre VARCHAR(100),
    IN p_id_tipo_piscina INT
)
BEGIN
    UPDATE piscinas
    SET nombre = p_nombre, id_tipo_piscina = p_id_tipo_piscina
    WHERE id_piscina = p_id_piscina;
END$$

-- Eliminar una piscina
CREATE PROCEDURE `sp_delete_piscina`(
    IN p_id_piscina INT
)
BEGIN
    DELETE FROM piscinas WHERE id_piscina = p_id_piscina;
END$$

DELIMITER $$

-- =============================================
-- Procedimientos para la tabla `carriles`
-- =============================================

-- Crear un nuevo carril
CREATE PROCEDURE `sp_create_carril`(
    IN p_id_piscina INT,
    IN p_numero_carril INT,
    IN p_capacidad_maxima INT
)
BEGIN
    INSERT INTO carriles (id_piscina, numero_carril, capacidad_maxima) VALUES (p_id_piscina, p_numero_carril, p_capacidad_maxima);
END$$

-- Obtener todos los carriles con el nombre de su piscina
CREATE PROCEDURE `sp_get_all_carriles`()
BEGIN
    SELECT
        c.id_carril,
        c.id_piscina,
        p.nombre AS piscina_nombre,
        c.numero_carril,
        c.capacidad_maxima
    FROM carriles c
    JOIN piscinas p ON c.id_piscina = p.id_piscina
    ORDER BY p.nombre, c.numero_carril;
END$$

-- Obtener un carril por ID
CREATE PROCEDURE `sp_get_carril_by_id`(
    IN p_id_carril INT
)
BEGIN
    SELECT id_carril, id_piscina, numero_carril, capacidad_maxima FROM carriles WHERE id_carril = p_id_carril;
END$$

-- Actualizar un carril
CREATE PROCEDURE `sp_update_carril`(
    IN p_id_carril INT,
    IN p_id_piscina INT,
    IN p_numero_carril INT,
    IN p_capacidad_maxima INT
)
BEGIN
    UPDATE carriles
    SET id_piscina = p_id_piscina,
        numero_carril = p_numero_carril,
        capacidad_maxima = p_capacidad_maxima
    WHERE id_carril = p_id_carril;
END$$

-- Eliminar un carril
CREATE PROCEDURE `sp_delete_carril`(
    IN p_id_carril INT
)
BEGIN
    DELETE FROM carriles WHERE id_carril = p_id_carril;
END$$

DELIMITER $$

-- =============================================
-- Procedimientos para la tabla `tipos_horario`
-- =============================================

-- Crear un nuevo tipo de horario
CREATE PROCEDURE `sp_create_tipo_horario`(
    IN p_nombre VARCHAR(100),
    IN p_dias_semana VARCHAR(50)
)
BEGIN
    INSERT INTO tipos_horario (nombre, dias_semana) VALUES (p_nombre, p_dias_semana);
END$$

-- Obtener todos los tipos de horario
CREATE PROCEDURE `sp_get_all_tipos_horario`()
BEGIN
    SELECT id_tipo_horario, nombre, dias_semana FROM tipos_horario;
END$$

-- Obtener un tipo de horario por ID
CREATE PROCEDURE `sp_get_tipo_horario_by_id`(
    IN p_id_tipo_horario INT
)
BEGIN
    SELECT id_tipo_horario, nombre, dias_semana FROM tipos_horario WHERE id_tipo_horario = p_id_tipo_horario;
END$$

-- Actualizar un tipo de horario
CREATE PROCEDURE `sp_update_tipo_horario`(
    IN p_id_tipo_horario INT,
    IN p_nombre VARCHAR(100),
    IN p_dias_semana VARCHAR(50)
)
BEGIN
    UPDATE tipos_horario
    SET nombre = p_nombre, dias_semana = p_dias_semana
    WHERE id_tipo_horario = p_id_tipo_horario;
END$$

-- Eliminar un tipo de horario
CREATE PROCEDURE `sp_delete_tipo_horario`(
    IN p_id_tipo_horario INT
)
BEGIN
    DELETE FROM tipos_horario WHERE id_tipo_horario = p_id_tipo_horario;
END$$

DELIMITER $$

-- =============================================
-- Procedimientos para la tabla `horarios`
-- =============================================

-- Crear un nuevo horario
CREATE PROCEDURE `sp_create_horario`(
    IN p_id_curso INT,
    IN p_id_profesor INT,
    IN p_id_carril INT,
    IN p_id_tipo_horario INT,
    IN p_hora_inicio TIME,
    IN p_hora_fin TIME
)
BEGIN
    INSERT INTO horarios (id_curso, id_profesor, id_carril, id_tipo_horario, hora_inicio, hora_fin)
    VALUES (p_id_curso, p_id_profesor, p_id_carril, p_id_tipo_horario, p_hora_inicio, p_hora_fin);
END$$

-- Obtener todos los horarios con nombres
CREATE PROCEDURE `sp_get_all_horarios_details`()
BEGIN
    SELECT
        h.id_horario,
        c.nombre AS curso_nombre,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        CONCAT(pi.nombre, ' - Carril ', ca.numero_carril) as carril_nombre,
        th.nombre as tipo_horario_nombre,
        h.hora_inicio,
        h.hora_fin
    FROM horarios h
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN profesores p ON h.id_profesor = p.id_profesor
    JOIN carriles ca ON h.id_carril = ca.id_carril
    JOIN piscinas pi ON ca.id_piscina = pi.id_piscina
    JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    ORDER BY h.id_horario;
END$$

-- Obtener un horario por ID
CREATE PROCEDURE `sp_get_horario_by_id`(
    IN p_id_horario INT
)
BEGIN
    SELECT id_horario, id_curso, id_profesor, id_carril, id_tipo_horario, hora_inicio, hora_fin FROM horarios WHERE id_horario = p_id_horario;
END$$

-- Actualizar un horario
CREATE PROCEDURE `sp_update_horario`(
    IN p_id_horario INT,
    IN p_id_curso INT,
    IN p_id_profesor INT,
    IN p_id_carril INT,
    IN p_id_tipo_horario INT,
    IN p_hora_inicio TIME,
    IN p_hora_fin TIME
)
BEGIN
    UPDATE horarios
    SET id_curso = p_id_curso,
        id_profesor = p_id_profesor,
        id_carril = p_id_carril,
        id_tipo_horario = p_id_tipo_horario,
        hora_inicio = p_hora_inicio,
        hora_fin = p_hora_fin
    WHERE id_horario = p_id_horario;
END$$

-- Eliminar un horario
CREATE PROCEDURE `sp_delete_horario`(
    IN p_id_horario INT
)
BEGIN
    DELETE FROM horarios WHERE id_horario = p_id_horario;
END$$

DELIMITER $$

-- =============================================
-- Procedimientos para la tabla `formas_pago`
-- =============================================

-- Crear una nueva forma de pago
CREATE PROCEDURE `sp_create_forma_pago`(
    IN p_nombre VARCHAR(50)
)
BEGIN
    INSERT INTO formas_pago (nombre) VALUES (p_nombre);
END$$

-- Obtener todas las formas de pago
CREATE PROCEDURE `sp_get_all_formas_pago`()
BEGIN
    SELECT id_forma_pago, nombre FROM formas_pago;
END$$

-- Obtener una forma de pago por ID
CREATE PROCEDURE `sp_get_forma_pago_by_id`(
    IN p_id_forma_pago INT
)
BEGIN
    SELECT id_forma_pago, nombre FROM formas_pago WHERE id_forma_pago = p_id_forma_pago;
END$$

-- Actualizar una forma de pago
CREATE PROCEDURE `sp_update_forma_pago`(
    IN p_id_forma_pago INT,
    IN p_nombre VARCHAR(50)
)
BEGIN
    UPDATE formas_pago
    SET nombre = p_nombre
    WHERE id_forma_pago = p_id_forma_pago;
END$$

-- Eliminar una forma de pago
CREATE PROCEDURE `sp_delete_forma_pago`(
    IN p_id_forma_pago INT
)
BEGIN
    DELETE FROM formas_pago WHERE id_forma_pago = p_id_forma_pago;
END$$

DELIMITER $$

-- Obtener horarios disponibles para un curso (con vacantes)
CREATE PROCEDURE `sp_get_horarios_disponibles_por_curso`(
    IN p_id_curso INT,
    IN p_id_profesor INT,
    IN p_hora_inicio TIME,
    IN p_hora_fin TIME
)
BEGIN
    SELECT
        h.id_horario,
        c.nombre AS curso_nombre,
        (SELECT pc.precio FROM precios_cursos pc WHERE pc.id_curso = h.id_curso AND CURDATE() BETWEEN pc.fecha_inicio AND pc.fecha_fin ORDER BY pc.id_tipo_precio DESC LIMIT 1) AS precio_actual,
        (SELECT pc.fecha_fin FROM precios_cursos pc WHERE pc.id_curso = h.id_curso AND CURDATE() BETWEEN pc.fecha_inicio AND pc.fecha_fin ORDER BY pc.id_tipo_precio DESC LIMIT 1) AS fecha_fin_curso,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        CONCAT(pi.nombre, ' - Carril ', ca.numero_carril) as carril_nombre,
        th.nombre as tipo_horario_nombre,
        th.dias_semana,
        h.hora_inicio,
        h.hora_fin,
        (ca.capacidad_maxima - (
            SELECT COUNT(*)
            FROM matriculas m
            WHERE m.id_horario = h.id_horario AND m.estado IN ('activa', 'vigente')
        )) AS vacantes_disponibles
    FROM horarios h
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN profesores p ON h.id_profesor = p.id_profesor
    JOIN carriles ca ON h.id_carril = ca.id_carril
    JOIN piscinas pi ON ca.id_piscina = pi.id_piscina
    JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    WHERE h.id_curso = p_id_curso
      AND (p_id_profesor = 0 OR h.id_profesor = p_id_profesor)
      AND (p_hora_inicio IS NULL OR h.hora_inicio >= p_hora_inicio)
      AND (p_hora_fin IS NULL OR h.hora_fin <= p_hora_fin)
    HAVING vacantes_disponibles > 0;
END$$

DELIMITER $$

-- Obtener todas las matrículas con detalles
CREATE PROCEDURE `sp_get_all_matriculas_details`()
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
        m.fecha_matricula
    FROM matriculas m
    JOIN alumnos a ON m.id_alumno = a.id_alumno
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN usuarios u ON m.id_usuario_admin = u.id_usuario
    ORDER BY m.fecha_matricula DESC;
END$$

DELIMITER $$

-- Obtener los detalles de una matrícula específica
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

-- Obtener los días de clase de una matrícula
CREATE PROCEDURE `sp_get_matricula_dias`(
    IN p_id_matricula INT
)
BEGIN
    SELECT
        id_matricula_dia,
        fecha_clase,
        estado,
        observacion
    FROM matricula_dias
    WHERE id_matricula = p_id_matricula
    ORDER BY fecha_clase;
END$$

DELIMITER $$

-- Cancelar (anular) una matrícula
CREATE PROCEDURE `sp_cancel_matricula`(
    IN p_id_matricula INT
)
BEGIN
    UPDATE matriculas
    SET
        estado = 'anulada'
    WHERE id_matricula = p_id_matricula;
END$$

DELIMITER $$

-- Cambiar un alumno de horario
CREATE PROCEDURE `sp_change_horario_matricula`(
    IN p_id_matricula INT,
    IN p_new_id_horario INT
)
BEGIN
    DECLARE v_old_id_horario INT;
    DECLARE v_new_dias_semana VARCHAR(50);
    DECLARE v_fecha_inicio DATE;
    DECLARE v_fecha_fin DATE;

    -- Iniciar transacción para asegurar la atomicidad
    START TRANSACTION;

    -- 1. Obtener datos necesarios de la matrícula y el nuevo horario
    SELECT id_horario, fecha_inicio, fecha_fin INTO v_old_id_horario, v_fecha_inicio, v_fecha_fin
    FROM matriculas WHERE id_matricula = p_id_matricula;

    SELECT th.dias_semana INTO v_new_dias_semana
    FROM horarios h JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    WHERE h.id_horario = p_new_id_horario;

    -- 2. Actualizar la matrícula con el nuevo id_horario
    -- Esto libera el cupo en el horario antiguo y ocupa uno en el nuevo
    UPDATE matriculas SET id_horario = p_new_id_horario WHERE id_matricula = p_id_matricula;

    -- 3. Eliminar los antiguos días de clase
    DELETE FROM matricula_dias WHERE id_matricula = p_id_matricula;

    -- 4. Generar los nuevos días de clase usando el SP existente
    CALL sp_generate_dias_clase(p_id_matricula, v_fecha_inicio, v_fecha_fin, v_new_dias_semana);

    COMMIT;
END$$

DELIMITER $$

-- =============================================
-- Procedimientos para Seguimiento y Control
-- =============================================

-- Actualizar el estado de un día de clase de un alumno
CREATE PROCEDURE `sp_update_asistencia_alumno`(
    IN p_id_matricula_dia INT,
    IN p_nuevo_estado ENUM('programada', 'asistio', 'falto', 'postergada', 'recuperada'),
    IN p_observacion VARCHAR(255)
)
BEGIN
    UPDATE matricula_dias
    SET
        estado = p_nuevo_estado,
        observacion = p_observacion
    WHERE id_matricula_dia = p_id_matricula_dia;
END$$

-- Añadir un día de clase de recuperación
CREATE PROCEDURE `sp_add_recuperacion_clase`(
    IN p_id_matricula INT,
    IN p_fecha_clase DATE,
    IN p_observacion VARCHAR(255)
)
BEGIN
    INSERT INTO matricula_dias(id_matricula, fecha_clase, estado, observacion)
    VALUES (p_id_matricula, p_fecha_clase, 'recuperada', p_observacion);
END$$

DELIMITER $$

-- =============================================
-- Procedimientos para Asistencia de Profesores
-- =============================================

-- Crear o actualizar la asistencia de un profesor para un día/horario
CREATE PROCEDURE `sp_create_or_update_asistencia_profesor`(
    IN p_id_profesor INT,
    IN p_id_horario INT,
    IN p_fecha DATE,
    IN p_estado ENUM('presente', 'falta_justificada', 'falta_injustificada', 'permiso'),
    IN p_observaciones TEXT
)
BEGIN
    DECLARE v_asistencia_id INT;

    SELECT id_asistencia_profesor INTO v_asistencia_id
    FROM asistencias_profesores
    WHERE id_profesor = p_id_profesor AND id_horario = p_id_horario AND fecha = p_fecha;

    IF v_asistencia_id IS NULL THEN
        INSERT INTO asistencias_profesores(id_profesor, id_horario, fecha, estado, observaciones)
        VALUES (p_id_profesor, p_id_horario, p_fecha, p_estado, p_observaciones);
    ELSE
        UPDATE asistencias_profesores
        SET estado = p_estado, observaciones = p_observaciones
        WHERE id_asistencia_profesor = v_asistencia_id;
    END IF;
END$$

-- Obtener los horarios programados para un día específico
CREATE PROCEDURE `sp_get_horarios_for_day`(
    IN p_fecha DATE
)
BEGIN
    DECLARE v_day_of_week INT;
    SET v_day_of_week = DAYOFWEEK(p_fecha);

    SELECT
        h.id_horario,
        h.id_profesor,
        c.nombre as curso_nombre,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        h.hora_inicio,
        h.hora_fin,
        (SELECT ap.estado FROM asistencias_profesores ap
         WHERE ap.id_horario = h.id_horario AND ap.fecha = p_fecha) AS estado_asistencia,
        (SELECT ap.observaciones FROM asistencias_profesores ap
         WHERE ap.id_horario = h.id_horario AND ap.fecha = p_fecha) AS observaciones_asistencia
    FROM horarios h
    JOIN tipos_horario th ON h.id_tipo_horario = th.id_tipo_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    JOIN profesores p ON h.id_profesor = p.id_profesor
    WHERE FIND_IN_SET(v_day_of_week, th.dias_semana) > 0;
END$$

DELIMITER $$

-- =============================================
-- Procedimientos para Reportes
-- =============================================

-- Reporte de ventas con filtros
CREATE PROCEDURE `sp_reporte_ventas`(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_id_alumno INT,      -- Usar 0 para "todos"
    IN p_id_curso INT,       -- Usar 0 para "todos"
    IN p_id_forma_pago INT -- Usar 0 para "todos"
)
BEGIN
    SELECT
        m.fecha_matricula,
        CONCAT(a.nombres, ' ', a.apellidos) AS alumno_nombre,
        c.nombre AS curso_nombre,
        fp.nombre AS forma_pago_nombre,
        m.precio_final
    FROM matriculas m
    JOIN alumnos a ON m.id_alumno = a.id_alumno
    JOIN horarios h ON m.id_horario = h.id_horario
    JOIN cursos c ON h.id_curso = c.id_curso
    LEFT JOIN formas_pago fp ON m.id_forma_pago = fp.id_forma_pago
    WHERE
        (m.fecha_matricula >= p_fecha_inicio AND m.fecha_matricula <= p_fecha_fin)
        AND (p_id_alumno = 0 OR m.id_alumno = p_id_alumno)
        AND (p_id_curso = 0 OR h.id_curso = p_id_curso)
        AND (p_id_forma_pago = 0 OR m.id_forma_pago = p_id_forma_pago)
        AND m.estado != 'anulada' -- Importante: Excluir matrículas anuladas de los reportes de ventas
    ORDER BY m.fecha_matricula DESC;
END$$

DELIMITER $$

-- Reporte de horas trabajadas por profesor
CREATE PROCEDURE `sp_reporte_horas_profesor`(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_id_profesor INT
)
BEGIN
    SELECT
        p.id_profesor,
        CONCAT(p.nombres, ' ', p.apellidos) AS profesor_nombre,
        SEC_TO_TIME(SUM(TIME_TO_SEC(h.hora_fin) - TIME_TO_SEC(h.hora_inicio))) AS horas_trabajadas,
        COUNT(ap.id_asistencia_profesor) as clases_asistidas
    FROM asistencias_profesores ap
    JOIN profesores p ON ap.id_profesor = p.id_profesor
    JOIN horarios h ON ap.id_horario = h.id_horario
    WHERE
        ap.estado = 'presente'
        AND ap.fecha BETWEEN p_fecha_inicio AND p_fecha_fin
        AND (p_id_profesor = 0 OR ap.id_profesor = p_id_profesor)
    GROUP BY p.id_profesor, profesor_nombre
    ORDER BY profesor_nombre;
END$$

DELIMITER ;
