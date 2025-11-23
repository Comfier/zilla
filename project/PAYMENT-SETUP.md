# Payment System Setup Guide

## Overview
The payment system has been integrated into the furniture order management system. After placing an order, clients are redirected to a payment page to complete payment.

## Setup Instructions

### 1. Update Database Schema
Run the database update script to add payment columns:

**Option A: Via Browser**
- Navigate to: `http://localhost/project/add-payment-column.php`
- The script will automatically add the required columns

**Option B: Via phpMyAdmin**
- Open phpMyAdmin
- Select the `furniture_orders` database
- Run the following SQL:
```sql
ALTER TABLE orders 
ADD COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending' AFTER status,
ADD COLUMN amount DECIMAL(10,2) DEFAULT NULL AFTER payment_status,
ADD COLUMN payment_date TIMESTAMP NULL DEFAULT NULL AFTER amount;
```

### 2. Features Included

#### Payment Page (`payment.html`)
- Order summary display
- Multiple payment methods:
  - Credit/Debit Card
  - Bank Transfer
  - Cash on Delivery
- Card number formatting
- Payment processing simulation
- Secure payment interface

#### Payment API (`php/payment.php`)
- Validates order ownership
- Processes payment
- Updates order payment status
- Records payment date and amount

#### Order Display Updates
- Shows payment status badge on orders
- Displays payment amount
- "Pay Now" button for unpaid orders
- Payment date display

## Payment Flow

1. **Client places order** → Order created with `payment_status = 'pending'`
2. **Redirect to payment page** → Shows order summary and payment form
3. **Client selects payment method** → Card, Bank Transfer, or Cash on Delivery
4. **Client submits payment** → Payment processed via API
5. **Order updated** → `payment_status` set to 'paid', amount and date recorded
6. **Redirect to orders page** → Client sees updated payment status

## Payment Methods

### Credit/Debit Card
- Requires card number, expiry, CVC, and cardholder name
- Validates card format
- Simulated processing (no real payment gateway)

### Bank Transfer
- No additional details required
- Payment marked as pending (admin can update later)

### Cash on Delivery
- No payment required upfront
- Payment status remains pending until delivery

## Testing

1. Place a new order from the catalog
2. You should be redirected to `payment.html?order={orderId}`
3. Fill in payment details
4. Submit payment
5. Check `client-orders.html` to see payment status updated

## Notes

- **This is a demo system** - No real payments are processed
- Payment amounts are calculated based on order details (simulated)
- In production, integrate with real payment gateways (Stripe, PayPal, etc.)
- Cash on Delivery orders remain in "pending" payment status until manually updated

## Files Modified/Created

### New Files:
- `payment.html` - Payment page
- `js/payment.js` - Payment processing logic
- `php/payment.php` - Payment API endpoint
- `add-payment-column.php` - Database update script

### Modified Files:
- `js/place-order.js` - Redirects to payment after order
- `js/client-catalog.js` - Redirects to payment after order
- `js/client-orders.js` - Displays payment status
- `php/orders.php` - Includes payment fields in response
- `css/orders.css` - Payment status badge styles


