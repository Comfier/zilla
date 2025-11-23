-- Add payment fields to orders table
-- Run this in phpMyAdmin

USE furniture_orders;

-- Add payment-related columns to orders table
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS amount DECIMAL(10, 2) DEFAULT NULL AFTER due_date,
ADD COLUMN IF NOT EXISTS payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending' AFTER amount,
ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT NULL AFTER payment_status,
ADD COLUMN IF NOT EXISTS payment_date DATETIME DEFAULT NULL AFTER payment_method,
ADD COLUMN IF NOT EXISTS payment_transaction_id VARCHAR(255) DEFAULT NULL AFTER payment_date;

-- Add index for payment status
ALTER TABLE orders ADD INDEX IF NOT EXISTS idx_payment_status (payment_status);

