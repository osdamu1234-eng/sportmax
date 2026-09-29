-- Ejecutar una sola vez en phpMyAdmin si la tabla usuarios ya existe.
ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) NULL AFTER rol;
