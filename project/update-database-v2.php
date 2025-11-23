<?php
/**
 * Update Database to v2 Schema
 * This script updates the existing database to support furniture catalog and roles
 * Access via: http://localhost/project/update-database-v2.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'furniture_orders';

echo "<h2>Update Database to v2 Schema</h2>";
echo "<pre>";

try {
    // Connect to MySQL
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        die("✗ Connection failed: " . $conn->connect_error . "\n");
    }
    
    echo "✓ Connected to database\n\n";
    
    // Check if role column exists
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($result->num_rows === 0) {
        echo "Adding 'role' column to users table...\n";
        $conn->query("ALTER TABLE users ADD COLUMN role ENUM('admin', 'client') DEFAULT 'client' AFTER password");
        echo "✓ Role column added\n\n";
        
        // Update existing admin user
        $conn->query("UPDATE users SET role = 'admin' WHERE email = 'admin@furniture.com'");
        echo "✓ Updated admin user role\n\n";
    } else {
        echo "✓ Role column already exists\n\n";
    }
    
    // Check if furniture table exists
    $result = $conn->query("SHOW TABLES LIKE 'furniture'");
    if ($result->num_rows === 0) {
        echo "Creating furniture table...\n";
        $furnitureTable = "CREATE TABLE furniture (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            category VARCHAR(100) NOT NULL,
            description TEXT,
            default_measurements TEXT,
            price_range VARCHAR(100),
            image_url VARCHAR(255),
            features TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_category (category),
            INDEX idx_is_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conn->query($furnitureTable)) {
            echo "✓ Furniture table created\n\n";
        } else {
            die("✗ Error creating furniture table: " . $conn->error . "\n");
        }
    } else {
        echo "✓ Furniture table already exists\n\n";
    }
    
    // Check if furniture_id column exists in orders
    $result = $conn->query("SHOW COLUMNS FROM orders LIKE 'furniture_id'");
    if ($result->num_rows === 0) {
        echo "Adding 'furniture_id' column to orders table...\n";
        $conn->query("ALTER TABLE orders ADD COLUMN furniture_id INT NULL AFTER user_id");
        $conn->query("ALTER TABLE orders ADD FOREIGN KEY (furniture_id) REFERENCES furniture(id) ON DELETE SET NULL");
        $conn->query("ALTER TABLE orders ADD INDEX idx_furniture_id (furniture_id)");
        echo "✓ Furniture_id column added\n\n";
    } else {
        echo "✓ Furniture_id column already exists\n\n";
    }
    
    // Check if furniture data exists
    $result = $conn->query("SELECT COUNT(*) as count FROM furniture");
    $count = $result->fetch_assoc()['count'];
    
    if ($count == 0) {
        echo "Inserting sample furniture items...\n";
        
        $furnitureItems = [
            ['Modern Sofa Set', 'Living Room', 'Comfortable 3-seater sofa with matching armchairs. Available in various fabrics and colors.', 'Sofa: 220cm x 95cm x 85cm, Armchair: 90cm x 95cm x 85cm', '$800 - $1,500', 'Removable covers, High-density foam, Wooden legs, Multiple color options'],
            ['Dining Table Set', 'Dining Room', 'Elegant dining table with 6 matching chairs. Extendable design for larger gatherings.', 'Table: 180cm x 90cm x 75cm (extends to 240cm), Chairs: 45cm x 45cm x 95cm', '$600 - $1,200', 'Extendable leaves, Padded seats, Durable hardwood, Easy to clean'],
            ['Bookshelf Unit', 'Storage', '5-shelf bookshelf with adjustable shelves. Perfect for home office or living room.', 'Width: 120cm, Height: 200cm, Depth: 35cm', '$300 - $600', 'Adjustable shelves, Cable management, Multiple finishes, Wall-mountable'],
            ['Bed Frame with Storage', 'Bedroom', 'Queen size bed frame with built-in storage drawers. Modern design with headboard.', 'Width: 160cm, Length: 200cm, Height: 50cm', '$700 - $1,400', 'Storage drawers, Upholstered headboard, Solid wood construction, Easy assembly'],
            ['Coffee Table', 'Living Room', 'Modern coffee table with storage compartment. Glass top with wooden base.', 'Length: 120cm, Width: 60cm, Height: 40cm', '$200 - $500', 'Tempered glass top, Storage compartment, Soft-close mechanism, Multiple finishes'],
            ['Wardrobe System', 'Bedroom', '3-door wardrobe with hanging space, shelves, and drawers. Full-length mirror included.', 'Width: 180cm, Height: 220cm, Depth: 60cm', '$1,000 - $2,000', 'LED lighting, Full-length mirror, Adjustable shelves, Soft-close doors'],
            ['Office Desk', 'Office', 'L-shaped office desk with cable management and filing cabinet. Ergonomic design.', 'Main: 150cm x 75cm, Side: 120cm x 60cm, Height: 75cm', '$500 - $1,000', 'Cable management, Filing cabinet, Ergonomic height, Multiple finishes'],
            ['TV Stand', 'Living Room', 'Floating TV stand with media storage. Accommodates up to 65" TV.', 'Length: 200cm, Height: 50cm, Depth: 40cm', '$300 - $700', 'Cable management, Ventilation, Media storage, Wall-mountable'],
            ['Kitchen Cabinet Set', 'Kitchen', 'Custom kitchen cabinets with soft-close hinges. Available in various styles.', 'Custom sizes available', '$2,000 - $5,000', 'Soft-close hinges, Adjustable shelves, Multiple finishes, Custom sizing'],
            ['Dresser with Mirror', 'Bedroom', '6-drawer dresser with attached mirror. Ample storage space.', 'Width: 120cm, Height: 150cm, Depth: 50cm', '$400 - $800', '6 drawers, Attached mirror, Soft-close drawers, Multiple finishes']
        ];
        
        $stmt = $conn->prepare("INSERT INTO furniture (name, category, description, default_measurements, price_range, features) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($furnitureItems as $item) {
            $stmt->bind_param("ssssss", $item[0], $item[1], $item[2], $item[3], $item[4], $item[5]);
            if ($stmt->execute()) {
                echo "✓ Inserted: {$item[0]}\n";
            } else {
                echo "✗ Error inserting {$item[0]}: " . $stmt->error . "\n";
            }
        }
        
        $stmt->close();
        echo "\n";
    } else {
        echo "✓ Furniture items already exist ($count items)\n\n";
    }
    
    echo "========================================\n";
    echo "Database update completed successfully!\n";
    echo "========================================\n";
    echo "\n";
    echo "You can now:\n";
    echo "1. Browse catalog: http://localhost/project/client-catalog.html\n";
    echo "2. View admin panel: http://localhost/project/admin-panel.html\n";
    echo "3. Manage furniture: http://localhost/project/admin-furniture.html\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>




