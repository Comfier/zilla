<?php
/**
 * Database Setup Script
 * Run this file once to create the database and tables
 * Access via: http://localhost/project/setup-database.php
 */

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'furniture_orders';

echo "<h2>Furniture Order Management System - Database Setup</h2>";
echo "<pre>";

try {
    // Connect to MySQL server
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error . "\n");
    }
    
    echo "✓ Connected to MySQL server\n";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql)) {
        echo "✓ Database '$dbname' created or already exists\n";
    } else {
        die("✗ Error creating database: " . $conn->error . "\n");
    }
    
    // Select database
    $conn->select_db($dbname);
    echo "✓ Selected database '$dbname'\n";
    
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
    
    if ($conn->query($usersTable)) {
        echo "✓ Table 'users' created or already exists\n";
    } else {
        die("✗ Error creating users table: " . $conn->error . "\n");
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
    
    if ($conn->query($ordersTable)) {
        echo "✓ Table 'orders' created or already exists\n";
    } else {
        die("✗ Error creating orders table: " . $conn->error . "\n");
    }
    
    // Check if admin user exists
    $checkAdmin = $conn->query("SELECT id FROM users WHERE email = 'admin@furniture.com'");
    if ($checkAdmin->num_rows == 0) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $insertAdmin = "INSERT INTO users (name, email, phone, password) VALUES 
            ('Admin User', 'admin@furniture.com', '1234567890', '$adminPassword')";
        
        if ($conn->query($insertAdmin)) {
            echo "✓ Default admin user created\n";
            echo "  Email: admin@furniture.com\n";
            echo "  Password: admin123\n";
        } else {
            echo "✗ Warning: Could not create admin user: " . $conn->error . "\n";
        }
    } else {
        echo "✓ Admin user already exists\n";
    }
    
    echo "\n";
    echo "========================================\n";
    echo "Database setup completed successfully!\n";
    echo "========================================\n";
    echo "\n";
    echo "You can now:\n";
    echo "1. Go to: http://localhost/project/\n";
    echo "2. Login with: admin@furniture.com / admin123\n";
    echo "3. Or create a new account\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>




