<?php
/**
 * Ensure All Furniture Images Work Offline
 * This script checks for external image URLs and ensures all images are stored locally
 * Access via: http://localhost/project/ensure-offline-images.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'furniture_orders';

echo "<h2>Ensure Offline Image Support</h2>";
echo "<pre>";

try {
    // Connect to MySQL
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        die("✗ Connection failed: " . $conn->connect_error . "\n");
    }
    
    echo "✓ Connected to database\n\n";
    
    // Get all furniture items
    $result = $conn->query("SELECT id, name, image_url FROM furniture");
    
    if ($result->num_rows === 0) {
        echo "No furniture items found in database.\n";
        $conn->close();
        exit;
    }
    
    $imageDir = __DIR__ . '/images/furniture/';
    $externalCount = 0;
    $localCount = 0;
    $missingCount = 0;
    $fixedCount = 0;
    
    echo "Checking furniture images...\n";
    echo str_repeat("=", 50) . "\n\n";
    
    $stmt = $conn->prepare("UPDATE furniture SET image_url = ? WHERE id = ?");
    
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $name = $row['name'];
        $imageUrl = $row['image_url'];
        
        echo "Checking: {$name} (ID: {$id})\n";
        
        if (empty($imageUrl)) {
            echo "  ⊘ No image URL set\n";
            $missingCount++;
        } elseif (strpos($imageUrl, 'http://') === 0 || strpos($imageUrl, 'https://') === 0) {
            // External URL detected
            echo "  ⚠ External URL detected: {$imageUrl}\n";
            $externalCount++;
            
            // Try to find local equivalent
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($name));
            $localFilename = $safeName . '.jpg';
            $localPath = $imageDir . $localFilename;
            $relativePath = 'images/furniture/' . $localFilename;
            
            if (file_exists($localPath)) {
                // Local file exists, update database
                echo "  ✓ Found local file: {$localFilename}\n";
                $stmt->bind_param("si", $relativePath, $id);
                if ($stmt->execute()) {
                    echo "  ✓ Updated to use local file\n";
                    $fixedCount++;
                } else {
                    echo "  ✗ Error updating: " . $stmt->error . "\n";
                }
            } else {
                echo "  ✗ Local file not found. Image will not work offline.\n";
                echo "  → Recommendation: Upload image manually or run add-sample-images.php with internet\n";
            }
        } elseif (strpos($imageUrl, 'images/furniture/') === 0) {
            // Local path
            $localPath = __DIR__ . '/' . $imageUrl;
            if (file_exists($localPath)) {
                echo "  ✓ Local file exists: {$imageUrl}\n";
                $localCount++;
            } else {
                echo "  ✗ Local file missing: {$imageUrl}\n";
                $missingCount++;
            }
        } else {
            echo "  ? Unknown path format: {$imageUrl}\n";
        }
        echo "\n";
    }
    
    $stmt->close();
    
    echo str_repeat("=", 50) . "\n";
    echo "Summary:\n";
    echo "  Local images: {$localCount}\n";
    echo "  External URLs: {$externalCount}\n";
    echo "  Missing images: {$missingCount}\n";
    echo "  Fixed (converted to local): {$fixedCount}\n";
    echo "\n";
    
    if ($externalCount > 0 || $missingCount > 0) {
        echo "⚠ WARNING: Some images may not work offline!\n";
        echo "\n";
        echo "To fix:\n";
        echo "1. Ensure all images are in: images/furniture/\n";
        echo "2. Run this script again to update database paths\n";
        echo "3. Or manually update image URLs in the database\n";
    } else {
        echo "✓ All images are stored locally and will work offline!\n";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>

