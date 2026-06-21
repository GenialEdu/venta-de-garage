CREATE DATABASE IF NOT EXISTS venta_garage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE venta_garage;

CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    normal_price DECIMAL(10,2) NOT NULL,
    rebaja1_price DECIMAL(10,2),
    rebaja2_price DECIMAL(10,2),
    image VARCHAR(500),
    status ENUM('disponible','vendido') DEFAULT 'disponible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    buyer_name VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    message TEXT,
    contact_whatsapp VARCHAR(20),
    status ENUM('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS meeting_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    notes TEXT,
    lat DECIMAL(10,7),
    lng DECIMAL(10,7),
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('admin_password', ''),
('phone_whatsapp', ''),
('phone_signal', ''),
('auto_approve_offers', '0'),
('page_title', 'Venta de Garage'),
('page_description', 'Artículos en venta - Entrega solo local'),
('city_name', ''),
('max_delivery_size', 'Paquetes máximo del tamaño de un garrafón de agua de 20 litros');

INSERT IGNORE INTO meeting_points (id, name, address, notes, lat, lng) VALUES
(1, 'Punto A', 'Dirección del punto A', 'Comentarios sobre el punto A', 0, 0),
(2, 'Punto B', 'Dirección del punto B', 'Comentarios sobre el punto B', 0, 0),
(3, 'Punto C', 'Dirección del punto C', 'Comentarios sobre el punto C', 0, 0);
