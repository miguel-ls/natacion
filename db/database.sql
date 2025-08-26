-- Base de datos: `sistema_natacion`
--
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--
CREATE TABLE `usuarios` (
  `id_usuario` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `rol` ENUM('administrador') NOT NULL DEFAULT 'administrador',
  `auth_code_2fa` VARCHAR(10),
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `tipos_piscina`
--
CREATE TABLE `tipos_piscina` (
  `id_tipo_piscina` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(50) NOT NULL,
  `descripcion` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `piscinas`
--
CREATE TABLE `piscinas` (
  `id_piscina` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `id_tipo_piscina` INT,
  FOREIGN KEY (`id_tipo_piscina`) REFERENCES `tipos_piscina`(`id_tipo_piscina`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `carriles`
--
CREATE TABLE `carriles` (
  `id_carril` INT AUTO_INCREMENT PRIMARY KEY,
  `id_piscina` INT NOT NULL,
  `numero_carril` INT NOT NULL,
  `capacidad_maxima` INT NOT NULL,
  FOREIGN KEY (`id_piscina`) REFERENCES `piscinas`(`id_piscina`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `cursos` (MODIFICADA)
--
CREATE TABLE `cursos` (
  `id_curso` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- NUEVA Estructura de tabla para la tabla `tipos_precio`
--
CREATE TABLE `tipos_precio` (
  `id_tipo_precio` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- NUEVA Estructura de tabla para la tabla `precios_cursos`
--
CREATE TABLE `precios_cursos` (
  `id_precio_curso` INT AUTO_INCREMENT PRIMARY KEY,
  `id_curso` INT NOT NULL,
  `id_tipo_precio` INT NOT NULL,
  `precio` DECIMAL(10, 2) NOT NULL,
  `fecha_inicio` DATE NOT NULL,
  `fecha_fin` DATE NOT NULL,
  FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id_curso`) ON DELETE CASCADE,
  FOREIGN KEY (`id_tipo_precio`) REFERENCES `tipos_precio`(`id_tipo_precio`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Estructura de tabla para la tabla `profesores`
--
CREATE TABLE `profesores` (
  `id_profesor` INT AUTO_INCREMENT PRIMARY KEY,
  `nombres` VARCHAR(100) NOT NULL,
  `apellidos` VARCHAR(100) NOT NULL,
  `documento_identidad` VARCHAR(20) UNIQUE,
  `telefono` VARCHAR(20),
  `email` VARCHAR(100) UNIQUE,
  `direccion` VARCHAR(255),
  `fecha_contratacion` DATE,
  `estado` ENUM('activo', 'inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `tipos_horario` (L-M-V, M-J-S, etc.)
--
CREATE TABLE `tipos_horario` (
  `id_tipo_horario` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `dias_semana` VARCHAR(50) NOT NULL COMMENT 'Ej: 2,4,6 para L,M,V (Formato DAYOFWEEK: 1=Dom, 2=Lun..)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `horarios`
--
CREATE TABLE `horarios` (
  `id_horario` INT AUTO_INCREMENT PRIMARY KEY,
  `id_curso` INT NOT NULL,
  `id_profesor` INT NOT NULL,
  `id_carril` INT NOT NULL,
  `id_tipo_horario` INT NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `hora_fin` TIME NOT NULL,
  FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id_curso`),
  FOREIGN KEY (`id_profesor`) REFERENCES `profesores`(`id_profesor`),
  FOREIGN KEY (`id_carril`) REFERENCES `carriles`(`id_carril`),
  FOREIGN KEY (`id_tipo_horario`) REFERENCES `tipos_horario`(`id_tipo_horario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `alumnos`
--
CREATE TABLE `alumnos` (
  `id_alumno` INT AUTO_INCREMENT PRIMARY KEY,
  `nombres` VARCHAR(100) NOT NULL,
  `apellidos` VARCHAR(100) NOT NULL,
  `documento_identidad` VARCHAR(20) UNIQUE,
  `fecha_nacimiento` DATE,
  `grupo_sanguineo` VARCHAR(5),
  `direccion` VARCHAR(255),
  `telefono` VARCHAR(20),
  `email` VARCHAR(100),
  `nombre_padre_tutor` VARCHAR(200),
  `telefono_emergencia` VARCHAR(20),
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `formas_pago`
--
CREATE TABLE `formas_pago` (
    `id_forma_pago` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `matriculas`
--
CREATE TABLE `matriculas` (
  `id_matricula` INT AUTO_INCREMENT PRIMARY KEY,
  `id_alumno` INT NOT NULL,
  `id_horario` INT NOT NULL,
  `id_usuario_admin` INT NOT NULL,
  `fecha_inicio` DATE NOT NULL,
  `fecha_fin` DATE NOT NULL,
  `precio_final` DECIMAL(10, 2) NOT NULL,
  `id_forma_pago` INT,
  `estado` ENUM('activa', 'vigente', 'anulada', 'finalizada') NOT NULL,
  `observaciones` TEXT,
  `fecha_matricula` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_alumno`) REFERENCES `alumnos`(`id_alumno`),
  FOREIGN KEY (`id_horario`) REFERENCES `horarios`(`id_horario`),
  FOREIGN KEY (`id_usuario_admin`) REFERENCES `usuarios`(`id_usuario`),
  FOREIGN KEY (`id_forma_pago`) REFERENCES `formas_pago`(`id_forma_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `matricula_dias` (días de clase específicos)
--
CREATE TABLE `matricula_dias` (
  `id_matricula_dia` INT AUTO_INCREMENT PRIMARY KEY,
  `id_matricula` INT NOT NULL,
  `fecha_clase` DATE NOT NULL,
  `estado` ENUM('programada', 'asistio', 'falto', 'postergada', 'recuperada') NOT NULL,
  `observacion` VARCHAR(255),
  FOREIGN KEY (`id_matricula`) REFERENCES `matriculas`(`id_matricula`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estructura de tabla para la tabla `asistencias_profesores`
--
CREATE TABLE `asistencias_profesores` (
  `id_asistencia_profesor` INT AUTO_INCREMENT PRIMARY KEY,
  `id_profesor` INT NOT NULL,
  `id_horario` INT NOT NULL,
  `fecha` DATE NOT NULL,
  `estado` ENUM('presente', 'falta_justificada', 'falta_injustificada', 'permiso') NOT NULL,
  `observaciones` TEXT,
  FOREIGN KEY (`id_profesor`) REFERENCES `profesores`(`id_profesor`),
  FOREIGN KEY (`id_horario`) REFERENCES `horarios`(`id_horario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
