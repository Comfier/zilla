<?php
require_once 'config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? 'client';
$conn = getDBConnection();

// Get order statistics
$stats = [];

// Check if admin - show all orders, otherwise show only user's orders
$isAdmin = ($userRole === 'admin');
$whereClause = $isAdmin ? "" : "WHERE user_id = ?";
$paramTypes = $isAdmin ? "" : "i";
$params = $isAdmin ? [] : [$userId];

// Total orders
if ($isAdmin) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders");
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
}
$stmt->execute();
$result = $stmt->get_result();
$stats['total'] = $result->fetch_assoc()['total'];
$stmt->close();

// Pending orders
if ($isAdmin) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND status = 'pending'");
    $stmt->bind_param("i", $userId);
}
$stmt->execute();
$result = $stmt->get_result();
$stats['pending'] = $result->fetch_assoc()['count'];
$stmt->close();

// Completed orders
if ($isAdmin) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE status = 'completed'");
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND status = 'completed'");
    $stmt->bind_param("i", $userId);
}
$stmt->execute();
$result = $stmt->get_result();
$stats['completed'] = $result->fetch_assoc()['count'];
$stmt->close();

// In progress orders
if ($isAdmin) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE status = 'in-progress'");
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND status = 'in-progress'");
    $stmt->bind_param("i", $userId);
}
$stmt->execute();
$result = $stmt->get_result();
$stats['inProgress'] = $result->fetch_assoc()['count'];
$stmt->close();

// Get recent orders (last 10 for admin, 5 for clients)
$limit = $isAdmin ? 10 : 5;
if ($isAdmin) {
    $stmt = $conn->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
} else {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $userId, $limit);
}
$stmt->execute();
$result = $stmt->get_result();

$recentOrders = [];
while ($row = $result->fetch_assoc()) {
    $recentOrders[] = [
        'id' => $row['id'],
        'clientName' => $row['client_name'],
        'clientEmail' => $row['client_email'],
        'clientPhone' => $row['client_phone'],
        'design' => $row['design'],
        'measurements' => $row['measurements'],
        'instructions' => $row['instructions'],
        'status' => $row['status'],
        'dueDate' => $row['due_date'],
        'createdAt' => $row['created_at'],
        'updatedAt' => $row['updated_at']
    ];
}

$stmt->close();
$conn->close();

echo json_encode([
    'stats' => $stats,
    'recentOrders' => $recentOrders
]);




