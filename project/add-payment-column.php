<?php
/**
 * Add payment_status column to orders table
 * Run this once to update the database schema
 */

require_once 'php/config.php';

$conn = getDBConnection();

// Check if payment_status column exists
$checkColumn = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_status'");

if ($checkColumn->num_rows == 0) {
    // Add payment_status column
    $alterQuery = "ALTER TABLE orders ADD COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending' AFTER status";
    
    if ($conn->query($alterQuery)) {
        echo "✓ Successfully added 'payment_status' column to 'orders' table.\n";
    } else {
        echo "✗ Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "✓ 'payment_status' column already exists in 'orders' table.\n";
}

// Check if amount column exists (for storing order amount)
$checkAmount = $conn->query("SHOW COLUMNS FROM orders LIKE 'amount'");

if ($checkAmount->num_rows == 0) {
    $alterQuery = "ALTER TABLE orders ADD COLUMN amount DECIMAL(10,2) DEFAULT NULL AFTER payment_status";
    
    if ($conn->query($alterQuery)) {
        echo "✓ Successfully added 'amount' column to 'orders' table.\n";
    } else {
        echo "✗ Error adding amount column: " . $conn->error . "\n";
    }
} else {
    echo "✓ 'amount' column already exists in 'orders' table.\n";
}

// Check if payment_date column exists
$checkPaymentDate = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_date'");

if ($checkPaymentDate->num_rows == 0) {
    $alterQuery = "ALTER TABLE orders ADD COLUMN payment_date TIMESTAMP NULL DEFAULT NULL AFTER amount";
    
    if ($conn->query($alterQuery)) {
        echo "✓ Successfully added 'payment_date' column to 'orders' table.\n";
    } else {
        echo "✗ Error adding payment_date column: " . $conn->error . "\n";
    }
} else {
    echo "✓ 'payment_date' column already exists in 'orders' table.\n";
}

$conn->close();

echo "\n✓ Database update complete!\n";


