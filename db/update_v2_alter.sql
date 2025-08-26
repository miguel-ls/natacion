-- Script de Modificación de Tablas (Actualización de Precios Dinámicos)
-- Elimina la columna de precio fijo de la tabla de cursos.

ALTER TABLE `cursos` DROP COLUMN `precio_base`;
