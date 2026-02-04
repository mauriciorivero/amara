-- Script para agregar campos de contacto y ubicación a la tabla madres
-- Fecha: 2026-02-04
-- Descripción: Agrega campos para correo electrónico, redes sociales, dirección, barrio y ciudad

USE minerva;

-- Agregar columnas de contacto
ALTER TABLE madres 
ADD COLUMN correo_electronico VARCHAR(255) NULL AFTER otro_contacto,
ADD COLUMN redes_sociales VARCHAR(500) NULL AFTER correo_electronico;

-- Agregar columnas de ubicación
ALTER TABLE madres 
ADD COLUMN direccion VARCHAR(500) NULL AFTER redes_sociales,
ADD COLUMN barrio VARCHAR(255) NULL AFTER direccion,
ADD COLUMN ciudad VARCHAR(255) NULL AFTER barrio;

-- Verificar que las columnas se agregaron correctamente
DESCRIBE madres;
