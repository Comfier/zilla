-- Furniture Order Management System Database Schema
-- Run this SQL script in phpMyAdmin to create the database and tables
-- IMPORTANT: First select the database 'furniture_orders' from the dropdown, then run this script

-- Create database (run this first if database doesn't exist)
CREATE DATABASE IF NOT EXISTS furniture_orders CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- After creating database, select it from the dropdown menu in phpMyAdmin
-- Then run the rest of the script below:

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table for client orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
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
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert a test user (password: admin123)
-- NOTE: The password hash below may not work. Run set-admin-password.php to set the password correctly
-- Password: admin123
INSERT INTO users (name, email, phone, password) VALUES 
('Admin User', 'admin@furniture.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE email=email;

-- IMPORTANT: After running this script, visit http://localhost/project/set-admin-password.php
-- to ensure the password hash is correctly generated for your PHP version
