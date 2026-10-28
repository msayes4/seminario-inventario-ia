CREATE DATABASE IF NOT EXISTS inventario_ia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventario_ia;

CREATE TABLE IF NOT EXISTS usuarios (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL,
 correo VARCHAR(150) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 rol VARCHAR(30) NOT NULL DEFAULT 'Administrador',
 estado TINYINT(1) NOT NULL DEFAULT 1,
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categorias (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL,
 descripcion VARCHAR(255),
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS proveedores (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(150) NOT NULL,
 telefono VARCHAR(30),
 correo VARCHAR(150),
 direccion VARCHAR(255),
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS productos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 codigo VARCHAR(50) NOT NULL UNIQUE,
 nombre VARCHAR(150) NOT NULL,
 categoria_id INT NULL,
 proveedor_id INT NULL,
 precio_compra DECIMAL(10,2) NOT NULL DEFAULT 0,
 precio_venta DECIMAL(10,2) NOT NULL DEFAULT 0,
 stock INT NOT NULL DEFAULT 0,
 stock_minimo INT NOT NULL DEFAULT 5,
 stock_maximo INT NOT NULL DEFAULT 50,
 activo TINYINT(1) NOT NULL DEFAULT 1,
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
 FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS movimientos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 producto_id INT NOT NULL,
 tipo ENUM('ENTRADA','SALIDA','AJUSTE') NOT NULL,
 cantidad INT NOT NULL,
 motivo VARCHAR(255),
 fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 usuario_id INT NULL,
 FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS ventas (
 id INT AUTO_INCREMENT PRIMARY KEY,
 fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 total DECIMAL(10,2) NOT NULL DEFAULT 0,
 usuario_id INT NULL,
 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS detalle_ventas (
 id INT AUTO_INCREMENT PRIMARY KEY,
 venta_id INT NOT NULL,
 producto_id INT NOT NULL,
 cantidad INT NOT NULL,
 precio DECIMAL(10,2) NOT NULL,
 subtotal DECIMAL(10,2) NOT NULL,
 FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
 FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS pronosticos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 producto_id INT NOT NULL,
 fecha_pronostico DATE NOT NULL,
 horizonte_dias INT NOT NULL DEFAULT 7,
 demanda_predicha DECIMAL(10,2) NOT NULL,
 modelo VARCHAR(100) NOT NULL DEFAULT 'Promedio mÃ³vil',
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS alertas (
 id INT AUTO_INCREMENT PRIMARY KEY,
 producto_id INT NOT NULL,
 tipo VARCHAR(50) NOT NULL,
 mensaje VARCHAR(255) NOT NULL,
 estado ENUM('PENDIENTE','ATENDIDA') DEFAULT 'PENDIENTE',
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

INSERT IGNORE INTO categorias (id,nombre,descripcion) VALUES
(1,'Bebidas','Productos de bebidas'),
(2,'Alimentos','Productos alimenticios'),
(3,'Limpieza','ArtÃ­culos de limpieza'),
(4,'Otros','Otros productos');

INSERT IGNORE INTO proveedores (id,nombre,telefono,correo,direccion) VALUES
(1,'Proveedor General','5555-5555','proveedor@example.com','Guatemala');

INSERT IGNORE INTO productos
(codigo,nombre,categoria_id,proveedor_id,precio_compra,precio_venta,stock,stock_minimo,stock_maximo)
VALUES
('P001','CafÃ© molido',1,1,25,40,35,10,80),
('P002','AzÃºcar',2,1,5,8,20,10,50),
('P003','Leche',1,1,8,12,8,10,40),
('P004','Galletas',2,1,3,6,50,15,100);


-- Historial de ejemplo para poder probar el pronÃ³stico
INSERT INTO movimientos (producto_id,tipo,cantidad,motivo) VALUES
(1,'SALIDA',15,'Venta histÃ³rica'),(1,'SALIDA',18,'Venta histÃ³rica'),
(1,'SALIDA',17,'Venta histÃ³rica'),(1,'SALIDA',20,'Venta histÃ³rica'),
(1,'SALIDA',22,'Venta histÃ³rica'),(1,'SALIDA',19,'Venta histÃ³rica'),
(2,'SALIDA',5,'Venta histÃ³rica'),(2,'SALIDA',7,'Venta histÃ³rica'),
(2,'SALIDA',6,'Venta histÃ³rica'),(2,'SALIDA',8,'Venta histÃ³rica');
