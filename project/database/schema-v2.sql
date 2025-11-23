-- Furniture Order Management System Database Schema v2
-- Enhanced with Client/Admin roles and Furniture Catalog
-- Run this SQL script in phpMyAdmin

-- Create database
CREATE DATABASE IF NOT EXISTS furniture_orders CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE furniture_orders;

-- Users table with role support (admin or client)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client') DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Furniture/Products catalog table
CREATE TABLE IF NOT EXISTS furniture (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    default_measurements TEXT,
    price_range VARCHAR(100),
    image_url VARCHAR(255),
    features TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table (updated to link with furniture and track client orders)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    furniture_id INT,
    client_name VARCHAR(100) NOT NULL,
    client_email VARCHAR(100) NOT NULL,
    client_phone VARCHAR(20),
    design TEXT NOT NULL,
    measurements TEXT NOT NULL,
    instructions TEXT,
    status ENUM('pending', 'in-progress', 'completed', 'cancelled') DEFAULT 'pending',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (furniture_id) REFERENCES furniture(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_furniture_id (furniture_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
-- Run set-admin-password.php after this to ensure correct password hash
INSERT INTO users (name, email, phone, password, role) VALUES 
('Admin User', 'admin@furniture.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE email=email;

-- Insert sample furniture/products
INSERT INTO furniture (name, category, description, default_measurements, price_range, features) VALUES
('Modern Sofa Set', 'Living Room', 'Comfortable 3-seater sofa with matching armchairs. Available in various fabrics and colors.', 'Sofa: 220cm x 95cm x 85cm, Armchair: 90cm x 95cm x 85cm', '$800 - $1,500', 'Removable covers, High-density foam, Wooden legs, Multiple color options'),
('Dining Table Set', 'Dining Room', 'Elegant dining table with 6 matching chairs. Extendable design for larger gatherings.', 'Table: 180cm x 90cm x 75cm (extends to 240cm), Chairs: 45cm x 45cm x 95cm', '$600 - $1,200', 'Extendable leaves, Padded seats, Durable hardwood, Easy to clean'),
('Bookshelf Unit', 'Storage', '5-shelf bookshelf with adjustable shelves. Perfect for home office or living room.', 'Width: 120cm, Height: 200cm, Depth: 35cm', '$300 - $600', 'Adjustable shelves, Cable management, Multiple finishes, Wall-mountable'),
('Bed Frame with Storage', 'Bedroom', 'Queen size bed frame with built-in storage drawers. Modern design with headboard.', 'Width: 160cm, Length: 200cm, Height: 50cm', '$700 - $1,400', 'Storage drawers, Upholstered headboard, Solid wood construction, Easy assembly'),
('Coffee Table', 'Living Room', 'Modern coffee table with storage compartment. Glass top with wooden base.', 'Length: 120cm, Width: 60cm, Height: 40cm', '$200 - $500', 'Tempered glass top, Storage compartment, Soft-close mechanism, Multiple finishes'),
('Wardrobe System', 'Bedroom', '3-door wardrobe with hanging space, shelves, and drawers. Full-length mirror included.', 'Width: 180cm, Height: 220cm, Depth: 60cm', '$1,000 - $2,000', 'LED lighting, Full-length mirror, Adjustable shelves, Soft-close doors'),
('Office Desk', 'Office', 'L-shaped office desk with cable management and filing cabinet. Ergonomic design.', 'Main: 150cm x 75cm, Side: 120cm x 60cm, Height: 75cm', '$500 - $1,000', 'Cable management, Filing cabinet, Ergonomic height, Multiple finishes'),
('TV Stand', 'Living Room', 'Floating TV stand with media storage. Accommodates up to 65" TV.', 'Length: 200cm, Height: 50cm, Depth: 40cm', '$300 - $700', 'Cable management, Ventilation, Media storage, Wall-mountable'),
('Kitchen Cabinet Set', 'Kitchen', 'Custom kitchen cabinets with soft-close hinges. Available in various styles.', 'Custom sizes available', '$2,000 - $5,000', 'Soft-close hinges, Adjustable shelves, Multiple finishes, Custom sizing'),
('Dresser with Mirror', 'Bedroom', '6-drawer dresser with attached mirror. Ample storage space.', 'Width: 120cm, Height: 150cm, Depth: 50cm', '$400 - $800', '6 drawers, Attached mirror, Soft-close drawers, Multiple finishes')
ON DUPLICATE KEY UPDATE name=name;




