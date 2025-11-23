<?php
/**
 * Set Admin Password Script
 * This script sets the admin password to 'admin123'
 * Access via: http://localhost/project/set-admin-password.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'furniture_orders';

echo "<h2>Set Admin Password to 'admin123'</h2>";
echo "<pre>";

try {
    // Connect to MySQL
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        die("✗ Connection failed: " . $conn->connect_error . "\n");
    }
    
    echo "✓ Connected to database\n\n";
    
    // Password to set
    $password = 'admin123';
    $email = 'admin@furniture.com';
    
    // Generate password hash
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    echo "Email: $email\n";
    echo "Password: $password\n";
    echo "Hash: $hashedPassword\n\n";
    
    // Check if admin user exists
    $check = $conn->query("SELECT id, email, name FROM users WHERE email = '$email'");
    
    if ($check->num_rows > 0) {
        // Update existing admin user
        $user = $check->fetch_assoc();
        echo "Found existing admin user (ID: {$user['id']})\n";
        echo "Updating password...\n";
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);
        
        if ($stmt->execute()) {
            echo "✓ Password updated successfully!\n";
        } else {
            echo "✗ Error updating password: " . $stmt->error . "\n";
            $stmt->close();
            $conn->close();
            exit;
        }
        $stmt->close();
    } else {
        // Create new admin user
        echo "Admin user not found. Creating new admin user...\n";
        
        $name = 'Admin User';
        $phone = '1234567890';
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone, $hashedPassword);
        
        if ($stmt->execute()) {
            echo "✓ Admin user created successfully!\n";
        } else {
            echo "✗ Error creating user: " . $stmt->error . "\n";
            $stmt->close();
            $conn->close();
            exit;
        }
        $stmt->close();
    }
    
    // Verify the password works
    echo "\nVerifying password...\n";
    $result = $conn->query("SELECT password FROM users WHERE email = '$email'");
    if ($result->num_rows > 0) {
        $storedHash = $result->fetch_assoc()['password'];
        if (password_verify($password, $storedHash)) {
            echo "✓ Password verification successful!\n";
        } else {
            echo "✗ Password verification failed!\n";
        }
    }
    
    echo "\n";
    echo "========================================\n";
    echo "SUCCESS! Login Credentials:\n";
    echo "========================================\n";
    echo "Email: admin@furniture.com\n";
    echo "Password: admin123\n";
    echo "\n";
    echo "You can now login at: http://localhost/project/\n";
    echo "========================================\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>




