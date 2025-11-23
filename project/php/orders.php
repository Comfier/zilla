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

switch ($method) {
    case 'GET':
        handleGetOrders($userId);
        break;
    case 'POST':
        handleCreateOrder($userId);
        break;
    case 'PUT':
        handleUpdateOrder($userId);
        break;
    case 'DELETE':
        handleDeleteOrder($userId);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

function handleGetOrders($userId) {
    $conn = getDBConnection();
    
    // Get filter parameters
    $clientName = $_GET['client_name'] ?? '';
    $status = $_GET['status'] ?? '';
    
    $query = "SELECT * FROM orders WHERE user_id = ?";
    $params = [$userId];
    $types = "i";
    
    if (!empty($clientName)) {
        $query .= " AND client_name LIKE ?";
        $params[] = "%$clientName%";
        $types .= "s";
    }
    
    if (!empty($status) && $status !== 'all') {
        $query .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    if (count($params) > 1) {
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param($types, $userId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $order = [
            'id' => $row['id'],
            'furnitureId' => $row['furniture_id'],
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
        
        // Add payment fields if they exist
        if (isset($row['payment_status'])) {
            $order['paymentStatus'] = $row['payment_status'];
        }
        if (isset($row['amount'])) {
            $order['amount'] = $row['amount'];
        }
        if (isset($row['payment_date'])) {
            $order['paymentDate'] = $row['payment_date'];
        }
        
        $orders[] = $order;
    }
    
    echo json_encode($orders);
    
    $stmt->close();
    $conn->close();
}

function handleCreateOrder($userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['clientName']) || !isset($data['clientEmail']) || 
        !isset($data['design']) || !isset($data['measurements'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Required fields missing']);
        return;
    }
    
    $conn = getDBConnection();
    
    $clientName = $data['clientName'];
    $clientEmail = $data['clientEmail'];
    $clientPhone = $data['clientPhone'] ?? '';
    $design = $data['design'];
    $measurements = $data['measurements'];
    $instructions = $data['instructions'] ?? '';
    $status = $data['status'] ?? 'pending';
    $dueDate = !empty($data['dueDate']) ? $data['dueDate'] : null;
    $furnitureId = isset($data['furnitureId']) ? intval($data['furnitureId']) : null;
    
    $stmt = $conn->prepare("INSERT INTO orders (user_id, furniture_id, client_name, client_email, client_phone, design, measurements, instructions, status, due_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssssss", $userId, $furnitureId, $clientName, $clientEmail, $clientPhone, $design, $measurements, $instructions, $status, $dueDate);
    
    if ($stmt->execute()) {
        $orderId = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'id' => $orderId,
            'message' => 'Order created successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create order']);
    }
    
    $stmt->close();
    $conn->close();
}

function handleUpdateOrder($userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Order ID is required']);
        return;
    }
    
    $orderId = $data['id'];
    $conn = getDBConnection();
    
    // Verify order belongs to user
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        $stmt->close();
        $conn->close();
        return;
    }
    
    $clientName = $data['clientName'] ?? '';
    $clientEmail = $data['clientEmail'] ?? '';
    $clientPhone = $data['clientPhone'] ?? '';
    $design = $data['design'] ?? '';
    $measurements = $data['measurements'] ?? '';
    $instructions = $data['instructions'] ?? '';
    $status = $data['status'] ?? '';
    $dueDate = !empty($data['dueDate']) ? $data['dueDate'] : null;
    
    $stmt = $conn->prepare("UPDATE orders SET client_name = ?, client_email = ?, client_phone = ?, design = ?, measurements = ?, instructions = ?, status = ?, due_date = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssssssssii", $clientName, $clientEmail, $clientPhone, $design, $measurements, $instructions, $status, $dueDate, $orderId, $userId);
    
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

function handleDeleteOrder($userId) {
    $orderId = $_GET['id'] ?? null;
    
    if (!$orderId) {
        http_response_code(400);
        echo json_encode(['error' => 'Order ID is required']);
        return;
    }
    
    $conn = getDBConnection();
    
    // Verify order belongs to user
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        $stmt->close();
        $conn->close();
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $orderId, $userId);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Order deleted successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete order']);
    }
    
    $stmt->close();
    $conn->close();
}

