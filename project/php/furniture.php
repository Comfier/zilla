<?php
// For GET requests (viewing furniture), allow public access
// For POST/PUT/DELETE (admin operations), require authentication
$method = $_SERVER['REQUEST_METHOD'];

// Start session for all requests to check admin status
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only require config and authentication for non-GET requests
if ($method !== 'GET') {
    require_once 'config.php';
    // Check authentication for non-GET requests
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['user_role'] ?? 'client';
} else {
    // For GET requests, set headers but don't require session
    // But we still need config for database connection
    require_once 'config.php';
    if (!headers_sent()) {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }
}

// Database connection function for public access
function getDBConnectionPublic() {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'furniture_orders';
    
    try {
        $conn = new mysqli($host, $user, $pass, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }
}

switch ($method) {
    case 'GET':
        handleGetFurniture();
        break;
    case 'POST':
        if ($userRole !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            exit;
        }
        handleCreateFurniture();
        break;
    case 'PUT':
        if ($userRole !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            exit;
        }
        handleUpdateFurniture();
        break;
    case 'DELETE':
        if ($userRole !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            exit;
        }
        handleDeleteFurniture();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

function handleGetFurniture() {
    // Use public connection for GET requests
    $conn = getDBConnectionPublic();
    
    // Get single furniture item by ID
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        
        // Check if user is admin (to show all furniture including inactive)
        $isAdmin = false;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            $isAdmin = true;
        }
        
        // Admin can see all furniture, public can only see active
        if ($isAdmin) {
            $stmt = $conn->prepare("SELECT * FROM furniture WHERE id = ?");
        } else {
            $stmt = $conn->prepare("SELECT * FROM furniture WHERE id = ? AND is_active = 1");
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Furniture not found']);
            $stmt->close();
            $conn->close();
            return;
        }
        
        $furniture = $result->fetch_assoc();
        echo json_encode($furniture);
        $stmt->close();
        $conn->close();
        return;
    }
    
    // Get all furniture with optional filters
    $category = $_GET['category'] ?? '';
    $query = "SELECT * FROM furniture WHERE is_active = 1";
    $params = [];
    $types = "";
    
    // Check if user is admin (to show all furniture including inactive)
    $isAdmin = false;
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        $query = "SELECT * FROM furniture WHERE 1=1";
        $isAdmin = true;
    }
    
    if (!empty($category) && $category !== 'all') {
        $query .= " AND category = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    $query .= " ORDER BY category, name";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt = $conn->prepare($query);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $furniture = [];
    while ($row = $result->fetch_assoc()) {
        $furniture[] = $row;
    }
    
    echo json_encode($furniture);
    $stmt->close();
    $conn->close();
}

function handleCreateFurniture() {
    require_once 'config.php';
    $conn = getDBConnection();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['name']) || !isset($data['category']) || !isset($data['description'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name, category, and description are required']);
        return;
    }
    
    $name = $data['name'];
    $category = $data['category'];
    $description = $data['description'];
    $measurements = $data['default_measurements'] ?? '';
    $priceRange = $data['price_range'] ?? '';
    $features = $data['features'] ?? '';
    $imageUrl = $data['image_url'] ?? '';
    $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
    
    $stmt = $conn->prepare("INSERT INTO furniture (name, category, description, default_measurements, price_range, features, image_url, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssi", $name, $category, $description, $measurements, $priceRange, $features, $imageUrl, $isActive);
    
    if ($stmt->execute()) {
        $furnitureId = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'id' => $furnitureId,
            'message' => 'Furniture created successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create furniture']);
    }
    
    $stmt->close();
    $conn->close();
}

function handleUpdateFurniture() {
    require_once 'config.php';
    $conn = getDBConnection();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Furniture ID is required']);
        return;
    }
    
    $id = intval($data['id']);
    
    // Verify furniture exists
    $stmt = $conn->prepare("SELECT id FROM furniture WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Furniture not found']);
        $stmt->close();
        $conn->close();
        return;
    }
    $stmt->close();
    
    $name = $data['name'] ?? '';
    $category = $data['category'] ?? '';
    $description = $data['description'] ?? '';
    $measurements = $data['default_measurements'] ?? '';
    $priceRange = $data['price_range'] ?? '';
    $features = $data['features'] ?? '';
    $imageUrl = $data['image_url'] ?? '';
    $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
    
    $stmt = $conn->prepare("UPDATE furniture SET name = ?, category = ?, description = ?, default_measurements = ?, price_range = ?, features = ?, image_url = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("sssssssii", $name, $category, $description, $measurements, $priceRange, $features, $imageUrl, $isActive, $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Furniture updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update furniture']);
    }
    
    $stmt->close();
    $conn->close();
}

function handleDeleteFurniture() {
    require_once 'config.php';
    $conn = getDBConnection();
    
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Furniture ID is required']);
        return;
    }
    
    $id = intval($id);
    
    $stmt = $conn->prepare("DELETE FROM furniture WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Furniture deleted successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete furniture']);
    }
    
    $stmt->close();
    $conn->close();
}
