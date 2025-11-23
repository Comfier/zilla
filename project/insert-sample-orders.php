<?php
/**
 * Insert Sample Orders Script
 * This script creates sample orders for testing
 * Access via: http://localhost/project/insert-sample-orders.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'furniture_orders';

echo "<h2>Inserting Sample Orders</h2>";
echo "<pre>";

try {
    // Connect to MySQL
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        die("✗ Connection failed: " . $conn->connect_error . "\n");
    }
    
    echo "✓ Connected to database\n\n";
    
    // Get the first user (admin user)
    $userResult = $conn->query("SELECT id FROM users LIMIT 1");
    if ($userResult->num_rows === 0) {
        echo "✗ No users found in database!\n";
        echo "Please create a user account first.\n";
        $conn->close();
        exit;
    }
    
    $userId = $userResult->fetch_assoc()['id'];
    echo "✓ Found user ID: $userId\n\n";
    
    // Sample orders data
    $sampleOrders = [
        [
            'client_name' => 'John Smith',
            'client_email' => 'john.smith@email.com',
            'client_phone' => '555-0101',
            'design' => 'Modern 3-seater sofa with wooden legs and soft cushions. Upholstered in gray fabric.',
            'measurements' => 'Length: 220cm, Width: 95cm, Height: 85cm, Seat Height: 45cm',
            'instructions' => 'Please use high-quality foam for cushions. Wood finish should be walnut color.',
            'status' => 'in-progress',
            'due_date' => date('Y-m-d', strtotime('+2 weeks'))
        ],
        [
            'client_name' => 'Sarah Johnson',
            'client_email' => 'sarah.j@email.com',
            'client_phone' => '555-0102',
            'design' => 'Dining table with 6 matching chairs. Rustic farmhouse style.',
            'measurements' => 'Table: 180cm x 90cm x 75cm. Chairs: 45cm x 45cm x 95cm (seat height 45cm)',
            'instructions' => 'Table should have extendable leaves. Chairs need comfortable padded seats.',
            'status' => 'pending',
            'due_date' => date('Y-m-d', strtotime('+3 weeks'))
        ],
        [
            'client_name' => 'Michael Brown',
            'client_email' => 'm.brown@email.com',
            'client_phone' => '555-0103',
            'design' => 'Custom bookshelf with 5 shelves and sliding glass doors.',
            'measurements' => 'Width: 120cm, Height: 200cm, Depth: 35cm. Shelf spacing: 35cm each',
            'instructions' => 'Use oak wood. Glass doors should be frosted. Include lighting inside.',
            'status' => 'completed',
            'due_date' => date('Y-m-d', strtotime('-1 week'))
        ],
        [
            'client_name' => 'Emily Davis',
            'client_email' => 'emily.davis@email.com',
            'client_phone' => '555-0104',
            'design' => 'Bed frame with headboard. Queen size with storage drawers underneath.',
            'measurements' => 'Width: 160cm, Length: 200cm, Height: 50cm (bed height), Headboard: 120cm tall',
            'instructions' => 'Headboard should be upholstered in navy blue fabric. Drawers need smooth glides.',
            'status' => 'in-progress',
            'due_date' => date('Y-m-d', strtotime('+1 week'))
        ],
        [
            'client_name' => 'David Wilson',
            'client_email' => 'd.wilson@email.com',
            'client_phone' => '555-0105',
            'design' => 'Coffee table with storage compartment. Modern minimalist design.',
            'measurements' => 'Length: 120cm, Width: 60cm, Height: 40cm',
            'instructions' => 'Top should be tempered glass. Storage compartment with soft-close mechanism.',
            'status' => 'pending',
            'due_date' => date('Y-m-d', strtotime('+4 weeks'))
        ],
        [
            'client_name' => 'Lisa Anderson',
            'client_email' => 'lisa.a@email.com',
            'client_phone' => '555-0106',
            'design' => 'Wardrobe with 3 doors, hanging space, and drawers. Full-length mirror on one door.',
            'measurements' => 'Width: 180cm, Height: 220cm, Depth: 60cm',
            'instructions' => 'Interior should have LED lighting. Use white high-gloss finish.',
            'status' => 'pending',
            'due_date' => date('Y-m-d', strtotime('+5 weeks'))
        ],
        [
            'client_name' => 'Robert Taylor',
            'client_email' => 'r.taylor@email.com',
            'client_phone' => '555-0107',
            'design' => 'Office desk with cable management. L-shaped design with filing cabinet.',
            'measurements' => 'Main surface: 150cm x 75cm, Side surface: 120cm x 60cm, Height: 75cm',
            'instructions' => 'Include grommet holes for cables. Filing cabinet should have 3 drawers.',
            'status' => 'completed',
            'due_date' => date('Y-m-d', strtotime('-2 weeks'))
        ],
        [
            'client_name' => 'Jennifer Martinez',
            'client_email' => 'j.martinez@email.com',
            'client_phone' => '555-0108',
            'design' => 'TV stand with media storage. Floating design with hidden cable management.',
            'measurements' => 'Length: 200cm, Height: 50cm, Depth: 40cm',
            'instructions' => 'Should accommodate 65" TV. Include ventilation for electronics.',
            'status' => 'in-progress',
            'due_date' => date('Y-m-d', strtotime('+10 days'))
        ]
    ];
    
    echo "Inserting " . count($sampleOrders) . " sample orders...\n\n";
    
    $inserted = 0;
    $skipped = 0;
    
    foreach ($sampleOrders as $order) {
        // Check if order already exists (by client email and design)
        $check = $conn->prepare("SELECT id FROM orders WHERE user_id = ? AND client_email = ? AND design = ?");
        $check->bind_param("iss", $userId, $order['client_email'], $order['design']);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            echo "⊘ Skipping: {$order['client_name']} - Order already exists\n";
            $skipped++;
            $check->close();
            continue;
        }
        $check->close();
        
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, client_name, client_email, client_phone, design, measurements, instructions, status, due_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssss", 
            $userId,
            $order['client_name'],
            $order['client_email'],
            $order['client_phone'],
            $order['design'],
            $order['measurements'],
            $order['instructions'],
            $order['status'],
            $order['due_date']
        );
        
        if ($stmt->execute()) {
            echo "✓ Created order for: {$order['client_name']} ({$order['status']})\n";
            $inserted++;
        } else {
            echo "✗ Error creating order for {$order['client_name']}: " . $stmt->error . "\n";
        }
        
        $stmt->close();
    }
    
    echo "\n";
    echo "========================================\n";
    echo "Summary:\n";
    echo "========================================\n";
    echo "✓ Inserted: $inserted orders\n";
    echo "⊘ Skipped: $skipped orders (already exist)\n";
    echo "\n";
    echo "You can now view these orders at:\n";
    echo "http://localhost/project/orders.html\n";
    echo "\n";
    echo "Dashboard will show statistics:\n";
    echo "http://localhost/project/dashboard.html\n";
    echo "========================================\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>




