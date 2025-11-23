<?php
/**
 * Check Furniture Images
 * This script displays all furniture items and their image URLs
 * Access via: http://localhost/project/check-furniture-images.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'furniture_orders';

echo "<h2>Furniture Items and Images</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Name</th><th>Category</th><th>Image URL</th><th>Image Status</th></tr>";

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $result = $conn->query("SELECT id, name, category, image_url FROM furniture ORDER BY category, name");
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $imageUrl = $row['image_url'] ?? 'No image';
            $imageStatus = 'No image';
            
            if (!empty($imageUrl)) {
                // Check if it's a local file
                if (strpos($imageUrl, 'images/furniture/') === 0) {
                    $localPath = __DIR__ . '/' . $imageUrl;
                    if (file_exists($localPath)) {
                        $imageStatus = '✓ Local file exists';
                    } else {
                        $imageStatus = '✗ Local file missing';
                    }
                } else {
                    $imageStatus = 'External URL';
                }
            }
            
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['name']}</td>";
            echo "<td>{$row['category']}</td>";
            echo "<td>" . htmlspecialchars($imageUrl) . "</td>";
            echo "<td>{$imageStatus}</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No furniture items found in database</td></tr>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<tr><td colspan='5'>Error: " . $e->getMessage() . "</td></tr>";
}

echo "</table>";
?>

