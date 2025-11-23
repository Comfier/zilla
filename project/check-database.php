<?php
/**
 * Database Check Script
 * This script checks if the database and tables are set up correctly
 * Access via: http://localhost/project/check-database.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'furniture_orders';

echo "<h2>Database Status Check</h2>";
echo "<pre>";

try {
    // Connect to MySQL
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        die("✗ Connection failed: " . $conn->connect_error . "\n");
    }
    
    echo "✓ Connected to MySQL server\n\n";
    
    // Check if database exists
    $result = $conn->query("SHOW DATABASES LIKE '$dbname'");
    if ($result->num_rows > 0) {
        echo "✓ Database '$dbname' exists\n";
        $conn->select_db($dbname);
    } else {
        echo "✗ Database '$dbname' does NOT exist\n";
        echo "  Run setup-database.php to create it\n";
        $conn->close();
        exit;
    }
    
    // Check users table
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "✓ Table 'users' exists\n";
        
        // Count users
        $userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
        echo "  → Found $userCount user(s)\n";
    } else {
        echo "✗ Table 'users' does NOT exist\n";
    }
    
    // Check orders table
    $result = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($result->num_rows > 0) {
        echo "✓ Table 'orders' exists\n";
        
        // Count orders
        $orderCount = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
        echo "  → Found $orderCount order(s)\n";
    } else {
        echo "✗ Table 'orders' does NOT exist\n";
        echo "  Creating orders table...\n";
        
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
            echo "  ✓ Orders table created successfully\n";
        } else {
            echo "  ✗ Error creating orders table: " . $conn->error . "\n";
        }
    }
    
    echo "\n";
    echo "========================================\n";
    echo "Database check complete!\n";
    echo "========================================\n";
    echo "\n";
    echo "If all checks passed, you can now:\n";
    echo "1. Go to: http://localhost/project/\n";
    echo "2. Login with: admin@furniture.com / admin123\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>




