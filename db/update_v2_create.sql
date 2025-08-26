-- Script de Creación de Nuevas Tablas (Actualización de Precios Dinámicos)
-- Crea las tablas necesarias para el nuevo sistema de precios.

-- 1. Tabla para los tipos de precio (Regular, Oferta, etc.)
CREATE TABLE `tipos_precio` (
  `id_tipo_precio` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabla para la lista de precios por curso con vigencia
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
