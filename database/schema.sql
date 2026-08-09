-- =========================================================
-- AgroLink™ — Esquema de base de datos (Prototipo Sprint 1 + Sprint 2)
-- SC-505 Administración de Proyectos
-- =========================================================

CREATE DATABASE IF NOT EXISTS agrolink CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agrolink;


CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('agricultor', 'consumidor') NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    cedula VARCHAR(20) NULL,
    telefono VARCHAR(20) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    ubicacion VARCHAR(150) NULL,          
    latitud DECIMAL(10,6) NULL,
    longitud DECIMAL(10,6) NULL,
    zona_cobertura_km INT NULL,           
    bio TEXT NULL,                        
    calificacion_promedio DECIMAL(3,2) DEFAULT 0,
    reset_token VARCHAR(10) NULL,         
    reset_token_expira DATETIME NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agricultor_id INT NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    categoria VARCHAR(60) NOT NULL,
    precio_crc DECIMAL(10,2) NOT NULL,
    cantidad_kg DECIMAL(10,2) NOT NULL,
    imagen VARCHAR(255) NULL,
    descripcion TEXT NULL,
    activo TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agricultor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CHECK (precio_crc >= 0),
    CHECK (cantidad_kg >= 0)
) ENGINE=InnoDB;


CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consumidor_id INT NOT NULL,
    agricultor_id INT NOT NULL,
    estado ENUM('carrito','pendiente','aceptado','en_camino','entregado','rechazado','pago_liberado') DEFAULT 'carrito',
    total_crc DECIMAL(10,2) DEFAULT 0,
    metodo_pago ENUM('sinpe','tarjeta') NULL,
    pago_en_custodia TINYINT(1) DEFAULT 0,  
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (consumidor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (agricultor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pedido_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad_kg DECIMAL(10,2) NOT NULL,
    precio_unitario_crc DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;


CREATE TABLE resenas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL UNIQUE,
    agricultor_id INT NOT NULL,
    consumidor_id INT NOT NULL,
    calificacion TINYINT NOT NULL CHECK (calificacion BETWEEN 1 AND 5),
    comentario TEXT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (agricultor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (consumidor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    mensaje VARCHAR(255) NOT NULL,
    leido TINYINT(1) DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;


INSERT INTO usuarios (tipo, nombre, cedula, telefono, email, password_hash, ubicacion, zona_cobertura_km, bio)
VALUES
('agricultor', 'Héctor Víquez', '1-2345-6789', '8888-1111', 'hector@agrolink.test',
 '$2b$10$peEIEt6fnxlONO2bZxJt5OmcfMTDYd4SyEx0/dbtOrsd6dRUQPegi', 'San Ramón, Alajuela', 15,
 'Productor de hortalizas orgánicas desde hace 8 años.'),
('agricultor', 'Diomer Quirós', '2-3456-7890', '8888-2222', 'diomer@agrolink.test',
 '$2b$10$peEIEt6fnxlONO2bZxJt5OmcfMTDYd4SyEx0/dbtOrsd6dRUQPegi', 'Grecia, Alajuela', 20,
 'Café y frutas de estación.'),
('consumidor', 'Fabián Chavarría', '1-1122-3344', '8888-3333', 'fabian@agrolink.test',
 '$2b$10$peEIEt6fnxlONO2bZxJt5OmcfMTDYd4SyEx0/dbtOrsd6dRUQPegi', 'La Unión, Cartago', NULL, NULL);
-- Contraseña de prueba para las 3 cuentas: agrolink123

INSERT INTO productos (agricultor_id, nombre, categoria, precio_crc, cantidad_kg, descripcion)
VALUES
(1, 'Tomate orgánico', 'Verduras', 850.00, 40, 'Cosechado esta semana, sin agroquímicos.'),
(1, 'Culantro fresco', 'Hierbas', 400.00, 15, 'Manojo grande, ideal para casados.'),
(2, 'Café en grano', 'Granos', 3200.00, 25, 'Tueste medio, finca de altura.');
