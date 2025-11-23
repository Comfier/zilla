<?php
require_once 'config.php';

// Check authentication and admin role
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userRole = $_SESSION['user_role'] ?? 'client';
if ($userRole !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'orders':
        handleGetAllOrders();
        break;
    case 'stats':
        handleGetStats();
        break;
    case 'update-order':
        handleUpdateOrder();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function handleGetAllOrders() {
    $conn = getDBConnection();
    
    // Get filter parameters
    $clientName = $_GET['client_name'] ?? '';
    $status = $_GET['status'] ?? '';
    
    $query = "SELECT o.*, u.name as user_name, u.email as user_email, f.name as furniture_name 
              FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id 
              LEFT JOIN furniture f ON o.furniture_id = f.id 
              WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($clientName)) {
        $query .= " AND o.client_name LIKE ?";
        $params[] = "%$clientName%";
        $types .= "s";
    }
    
    if (!empty($status) && $status !== 'all') {
        $query .= " AND o.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    $query .= " ORDER BY o.created_at DESC";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt = $conn->prepare($query);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = [
            'id' => $row['id'],
            'userId' => $row['user_id'],
            'userName' => $row['user_name'],
            'userEmail' => $row['user_email'],
            'furnitureId' => $row['furniture_id'],
            'furnitureName' => $row['furniture_name'],
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
    
    echo json_encode($orders);
    $stmt->close();
    $conn->close();
}

function handleGetStats() {
    $conn = getDBConnection();
    
    $stats = [];
    
    // Total orders
    $result = $conn->query("SELECT COUNT(*) as total FROM orders");
    $stats['total'] = $result->fetch_assoc()['total'];
    
    // Pending orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
    $stats['pending'] = $result->fetch_assoc()['count'];
    
    // In progress orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'in-progress'");
    $stats['inProgress'] = $result->fetch_assoc()['count'];
    
    // Completed orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed'");
    $stats['completed'] = $result->fetch_assoc()['count'];
    
    // Total clients
    $result = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM orders");
    $stats['totalClients'] = $result->fetch_assoc()['count'];
    
    $conn->close();
    
    echo json_encode($stats);
}

function handleUpdateOrder() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Order ID is required']);
        return;
    }
    
    $conn = getDBConnection();
    $orderId = intval($data['id']);
    
    // Verify order exists
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        $stmt->close();
        $conn->close();
        return;
    }
    $stmt->close();
    
    $status = $data['status'] ?? '';
    $dueDate = !empty($data['dueDate']) ? $data['dueDate'] : null;
    
    $stmt = $conn->prepare("UPDATE orders SET status = ?, due_date = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $dueDate, $orderId);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Order updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update order']);
    }
    
    $stmt->close();
    $conn->close();
}




