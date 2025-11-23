<?php
/**
 * Add Sample Images to Furniture Items
 * This script downloads sample images and saves them locally, then updates the database
 * Access via: http://localhost/project/add-sample-images.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'furniture_orders';

echo "<h2>Add Sample Images to Furniture</h2>";
echo "<pre>";

// Function to download image from URL
function downloadImage($url, $savePath) {
    $imageData = @file_get_contents($url);
    if ($imageData === false) {
        return false;
    }
    
    $result = @file_put_contents($savePath, $imageData);
    return $result !== false;
}

try {
    // Connect to MySQL
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        die("✗ Connection failed: " . $conn->connect_error . "\n");
    }
    
    echo "✓ Connected to database\n\n";
    
    // Ensure images/furniture directory exists
    $imageDir = __DIR__ . '/images/furniture/';
    if (!is_dir($imageDir)) {
        mkdir($imageDir, 0755, true);
        echo "✓ Created images/furniture directory\n\n";
    }
    
    // Map furniture items to appropriate image URLs
    // Using Pexels for reliable, free images
    $imageMap = [
        'Modern Sofa Set' => 'https://images.pexels.com/photos/276583/pexels-photo-276583.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
        'Dining Table Set' => 'https://images.pexels.com/photos/1350789/pexels-photo-1350789.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
        'Bookshelf Unit' => 'https://images.pexels.com/photos/1370704/pexels-photo-1370704.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
        'Bed Frame with Storage' => 'https://images.pexels.com/photos/1648771/pexels-photo-1648771.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
        'Coffee Table' => 'https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
        'Wardrobe System' => 'https://images.pexels.com/photos/1571463/pexels-photo-1571463.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
        'Office Desk' => 'https://images.pexels.com/photos/380769/pexels-photo-380769.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
        'TV Stand' => 'https://images.pexels.com/photos/1571453/pexels-photo-1571453.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
        'Kitchen Cabinet Set' => 'https://images.pexels.com/photos/1080721/pexels-photo-1080721.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop',
        'Dresser with Mirror' => 'https://images.pexels.com/photos/1648775/pexels-photo-1648775.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop'
    ];
    
    // Update each furniture item with downloaded image
    $stmt = $conn->prepare("UPDATE furniture SET image_url = ? WHERE name = ?");
    $updated = 0;
    $skipped = 0;
    $downloaded = 0;
    $failed = 0;
    
    foreach ($imageMap as $name => $imageUrl) {
        // Create safe filename from furniture name
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($name));
        $filename = $safeName . '.jpg';
        $filepath = $imageDir . $filename;
        $relativePath = 'images/furniture/' . $filename;
        
        // Download image
        echo "Downloading image for: {$name}...\n";
        if (downloadImage($imageUrl, $filepath)) {
            echo "  ✓ Downloaded: {$filename}\n";
            $downloaded++;
            
            // Update database with local path
            $stmt->bind_param("ss", $relativePath, $name);
            if ($stmt->execute()) {
                if ($conn->affected_rows > 0) {
                    echo "  ✓ Updated database with local image path\n";
                    $updated++;
                } else {
                    echo "  ⊘ No furniture found with name: {$name}\n";
                    $skipped++;
                    // Delete downloaded image if furniture not found
                    @unlink($filepath);
                }
            } else {
                echo "  ✗ Error updating database: " . $stmt->error . "\n";
                $failed++;
            }
        } else {
            echo "  ✗ Failed to download image from URL\n";
            // Try using URL directly if download fails
            $stmt->bind_param("ss", $imageUrl, $name);
            if ($stmt->execute() && $conn->affected_rows > 0) {
                echo "  ✓ Updated with external URL instead\n";
                $updated++;
            }
            $failed++;
        }
        echo "\n";
    }
    
    $stmt->close();
    
    echo "========================================\n";
    echo "Image update completed!\n";
    echo "========================================\n";
    echo "Downloaded: {$downloaded} images\n";
    echo "Updated in database: {$updated} items\n";
    echo "Skipped: {$skipped} items\n";
    echo "Failed: {$failed} items\n";
    echo "\n";
    echo "You can now view the furniture with images at:\n";
    echo "1. Client Catalog: http://localhost/project/client-catalog.html\n";
    echo "2. Admin Furniture: http://localhost/project/admin-furniture.html\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>

