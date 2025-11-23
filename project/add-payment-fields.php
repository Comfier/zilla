<?php
/**
 * Add payment fields to orders table
 * Run this once to update your database
 */

require_once 'php/config.php';

$conn = getDBConnection();

echo "<h2>Adding Payment Fields to Orders Table</h2>";

try {
    // Check if columns exist
    $checkColumns = $conn->query("SHOW COLUMNS FROM orders LIKE 'amount'");
    
    if ($checkColumns->num_rows == 0) {
        // Add payment columns
        $alterTable = "
        ALTER TABLE orders 
        ADD COLUMN amount DECIMAL(10, 2) DEFAULT NULL AFTER due_date,
        ADD COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending' AFTER amount,
        ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL AFTER payment_status,
        ADD COLUMN payment_date DATETIME DEFAULT NULL AFTER payment_method,
        ADD COLUMN payment_transaction_id VARCHAR(255) DEFAULT NULL AFTER payment_date
        ";
        
        if ($conn->query($alterTable)) {
            echo "✓ Payment columns added successfully.<br>";
        } else {
            echo "✗ Error adding columns: " . $conn->error . "<br>";
        }
    } else {
        echo "✓ Payment columns already exist.<br>";
    }
    
    // Add index for payment status
    $checkIndex = $conn->query("SHOW INDEX FROM orders WHERE Key_name = 'idx_payment_status'");
    if ($checkIndex->num_rows == 0) {
        $addIndex = "ALTER TABLE orders ADD INDEX idx_payment_status (payment_status)";
        if ($conn->query($addIndex)) {
            echo "✓ Payment status index added.<br>";
        } else {
            echo "✗ Error adding index: " . $conn->error . "<br>";
        }
    } else {
        echo "✓ Payment status index already exists.<br>";
    }
    
    echo "<br><strong>Database updated successfully!</strong><br>";
    echo "<a href='payment.html'>Go to Payment Page</a> | <a href='admin-panel.html'>Admin Panel</a>";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

$conn->close();
?>

