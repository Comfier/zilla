<?php
// Database Configuration
// Update these values according to your XAMPP MySQL settings

// Start session first (before any output) - only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'furniture_orders');

// Create database connection with auto-create database
function getDBConnection() {
    try {
        // First connect without database to create it if needed
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Create database if it doesn't exist
        $createDbQuery = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (!$conn->query($createDbQuery)) {
            throw new Exception("Failed to create database: " . $conn->error);
        }
        
        // Select the database
        $conn->select_db(DB_NAME);
        $conn->set_charset("utf8mb4");
        
        // Create tables if they don't exist
        createTablesIfNotExist($conn);
        
        return $conn;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }
}

// Create tables if they don't exist
function createTablesIfNotExist($conn) {
    // Create users table
    $usersTable = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20),
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($usersTable)) {
        throw new Exception("Failed to create users table: " . $conn->error);
    }
    
    // Create orders table
    $ordersTable = "CREATE TABLE IF NOT EXISTS orders (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($ordersTable)) {
        throw new Exception("Failed to create orders table: " . $conn->error);
    }
    
    // Insert default admin user if it doesn't exist
    $checkAdmin = $conn->query("SELECT id FROM users WHERE email = 'admin@furniture.com'");
    if ($checkAdmin->num_rows == 0) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $insertAdmin = "INSERT INTO users (name, email, phone, password) VALUES 
            ('Admin User', 'admin@furniture.com', '1234567890', '$adminPassword')";
        $conn->query($insertAdmin);
    }
}

// CORS headers for API requests (only if not already sent)
if (!headers_sent()) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
