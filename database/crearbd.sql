CREATE DATABASE IF NOT EXISTS appdb;

USE appdb;

CREATE TABLE Usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(30) NOT NULL,
    activo TINYINT NOT NULL DEFAULT 1
);

CREATE TABLE Categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL UNIQUE
);

CREATE TABLE Producto (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    imagen VARCHAR(255),
    stock_minimo INT NOT NULL DEFAULT 0,
    activo TINYINT NOT NULL DEFAULT 1,
    id_categoria INT NOT NULL,
    FOREIGN KEY (id_categoria) REFERENCES Categoria(id_categoria)
);

CREATE TABLE Producto_Talle (
    id_producto INT NOT NULL,
    talle VARCHAR(10) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id_producto, talle),
    FOREIGN KEY (id_producto) REFERENCES Producto(id_producto)
);

CREATE TABLE Pedido (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL,
    estado VARCHAR(30) NOT NULL,
    metodo_pago VARCHAR(30) NOT NULL,
    envio_nombre VARCHAR(60) NULL,
    envio_direccion VARCHAR(100) NULL,
    envio_ciudad VARCHAR(50) NULL,
    envio_departamento VARCHAR(30) NULL,
    envio_telefono VARCHAR(15) NULL,
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario)
);

CREATE TABLE Detalle_Pedido (
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    talle VARCHAR(10) NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id_pedido, id_producto, talle),
    FOREIGN KEY (id_pedido) REFERENCES Pedido(id_pedido),
    FOREIGN KEY (id_producto, talle) REFERENCES Producto_Talle(id_producto, talle)
);

CREATE TABLE Pago (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resultado VARCHAR(20) NOT NULL,
    ultimos4 CHAR(4) NOT NULL,
    FOREIGN KEY (id_pedido) REFERENCES Pedido(id_pedido)
);

-- Datos de prueba
INSERT INTO Categoria (id_categoria, nombre)
VALUES
(1, 'Abrigos'),
(2, 'Remeras'),
(3, 'Pantalones'),
(4, 'Shorts');

INSERT INTO Producto (id_producto, nombre, descripcion, precio, imagen, stock_minimo, id_categoria)
VALUES
(1, 'Canguro Oversize', 'Canguro Oversize', 1890.00, 'imagenes/canguro.jpg', 5, 1),
(2, 'Remera Basica', 'Remera basica de uso diario', 990.00, 'imagenes/remera-basica.jpg', 5, 2),
(3, 'Pantalon Cargo', 'Pantalon cargo con bolsillos', 1790.00, 'imagenes/pantalon-cargo.jpg', 5, 3),
(4, 'Buzo Clasico', 'Buzo clasico de abrigo', 1690.00, 'imagenes/buzo-clasico.jpg', 5, 1),
(5, 'Campera Liviana', 'Campera liviana para media estacion', 2390.00, 'imagenes/campera-liviana.jpg', 10, 1),
(6, 'Short Deportivo', 'Short deportivo comodo y liviano', 890.00, 'imagenes/short-deportivo.jpg', 5, 4);

INSERT INTO Producto_Talle (id_producto, talle, stock)
VALUES
(1, 'XS', 0), (1, 'S', 2), (1, 'M', 3), (1, 'L', 3), (1, 'XL', 2), (1, 'XXL', 0),
(2, 'XS', 0), (2, 'S', 3), (2, 'M', 5), (2, 'L', 4), (2, 'XL', 3), (2, 'XXL', 0),
(3, 'XS', 0), (3, 'S', 2), (3, 'M', 3), (3, 'L', 3), (3, 'XL', 2), (3, 'XXL', 0),
(4, 'XS', 0), (4, 'S', 3), (4, 'M', 3), (4, 'L', 3), (4, 'XL', 3), (4, 'XXL', 0),
(5, 'XS', 0), (5, 'S', 0), (5, 'M', 3), (5, 'L', 3), (5, 'XL', 2), (5, 'XXL', 0),
(6, 'XS', 0), (6, 'S', 3), (6, 'M', 4), (6, 'L', 4), (6, 'XL', 3), (6, 'XXL', 0);
