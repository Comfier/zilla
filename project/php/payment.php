<?php
require_once 'config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Process payment
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['orderId']) || !isset($data['paymentMethod']) || !isset($data['amount'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required payment information']);
    exit;
}

$orderId = intval($data['orderId']);
$paymentMethod = $data['paymentMethod'];
$amount = floatval($data['amount']);

$conn = getDBConnection();

// Verify order exists and belongs to user
$stmt = $conn->prepare("SELECT id, user_id FROM orders WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Order not found']);
    $stmt->close();
    $conn->close();
    exit;
}

$order = $result->fetch_assoc();

// Verify order belongs to current user
if ($order['user_id'] != $userId) {
    http_response_code(403);
    echo json_encode(['error' => 'You do not have permission to pay for this order']);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();

// Check if payment columns exist, if not, add them
$checkPaymentStatus = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_status'");
if ($checkPaymentStatus->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending' AFTER status");
    $conn->query("ALTER TABLE orders ADD COLUMN amount DECIMAL(10,2) DEFAULT NULL AFTER payment_status");
    $conn->query("ALTER TABLE orders ADD COLUMN payment_date TIMESTAMP NULL DEFAULT NULL AFTER amount");
}

// Simulate payment processing
// In a real system, this would integrate with payment gateways (Stripe, PayPal, etc.)
$paymentSuccess = true; // For demo, always succeed

if ($paymentMethod === 'cash') {
    // Cash on delivery - mark as pending payment
    $paymentStatus = 'pending';
} else {
    // Simulate payment processing
    // In real system, validate card details and process through payment gateway
    $paymentStatus = $paymentSuccess ? 'paid' : 'failed';
}

// Update order with payment information
$updateQuery = "UPDATE orders SET payment_status = ?, amount = ?";
$params = [$paymentStatus, $amount];
$types = "sd";

if ($paymentStatus === 'paid') {
    $updateQuery .= ", payment_date = NOW()";
}

$updateQuery .= " WHERE id = ?";
$params[] = $orderId;
$types .= "i";

$stmt = $conn->prepare($updateQuery);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Payment processed successfully',
        'paymentStatus' => $paymentStatus,
        'amount' => $amount
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to process payment']);
}

$stmt->close();
$conn->close();


