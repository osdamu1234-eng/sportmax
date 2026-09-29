-- Ejecuta este archivo en phpMyAdmin sobre la base sport_maxx.
-- CREATE TABLE IF NOT EXISTS conserva las tablas que ya existan.
CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(120) NOT NULL,
    correo_electronico VARCHAR(190) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL DEFAULT 'cliente',
    foto_perfil VARCHAR(255) NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS productos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT UNSIGNED NULL,
    nombre VARCHAR(180) NOT NULL,
    descripcion TEXT NULL,
    precio DECIMAL(12,2) NOT NULL DEFAULT 0,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    imagen VARCHAR(500) NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_productos_categoria (id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contacto (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    tipo_mensaje VARCHAR(60) NOT NULL DEFAULT 'General',
    mensaje TEXT NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categorias (nombre) VALUES ('Calzado'), ('Ropa'), ('Accesorios');

INSERT INTO productos (id_categoria, nombre, descripcion, precio, stock, imagen)
SELECT c.id, 'Tenis Running Pro Max', 'Tenis deportivos para running y entrenamiento.', 220000, 12, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80'
FROM categorias c WHERE c.nombre = 'Calzado' AND NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Tenis Running Pro Max') LIMIT 1;
INSERT INTO productos (id_categoria, nombre, descripcion, precio, stock, imagen)
SELECT c.id, 'Camiseta Sport Dry-Fit', 'Camiseta ligera y transpirable para entrenar.', 75000, 20, 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=600&q=80'
FROM categorias c WHERE c.nombre = 'Ropa' AND NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Camiseta Sport Dry-Fit') LIMIT 1;
INSERT INTO productos (id_categoria, nombre, descripcion, precio, stock, imagen)
SELECT c.id, 'Sudadera de Entrenamiento', 'Sudadera cómoda para tus sesiones de entrenamiento.', 140000, 10, 'https://images.unsplash.com/photo-1556906781-9a412961c28c?auto=format&fit=crop&w=600&q=80'
FROM categorias c WHERE c.nombre = 'Ropa' AND NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Sudadera de Entrenamiento') LIMIT 1;
INSERT INTO productos (id_categoria, nombre, descripcion, precio, stock, imagen)
SELECT c.id, 'Mochila Deportiva Impermeable', 'Mochila resistente para llevar tu equipo.', 95000, 8, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=600&q=80'
FROM categorias c WHERE c.nombre = 'Accesorios' AND NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Mochila Deportiva Impermeable') LIMIT 1;

-- Después de crear una cuenta normal, promuévela desde phpMyAdmin:
-- UPDATE usuarios SET rol = 'admin' WHERE correo_electronico = 'tu-correo@ejemplo.com';
