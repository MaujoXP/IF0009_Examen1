CREATE DATABASE IF NOT EXISTS db_taller;
USE db_taller;

CREATE TABLE ordenes_trabajo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placa_vehiculo VARCHAR(10) NOT NULL,
    propietario VARCHAR(100) NOT NULL,
    descripcion_falla TEXT NOT NULL,
    costo_estimado DECIMAL(10,2) NOT NULL,
    estado ENUM('Pendiente', 'En Proceso', 'Finalizado', 'Entregado') DEFAULT 'Pendiente',
    fecha_ingreso TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);