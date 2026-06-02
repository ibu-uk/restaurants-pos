<?php
// Migration script to add image_path column to items table
require_once 'db/connect.php';

// Add image_path column if it doesn't exist
$check = $conn->query("SHOW COLUMNS FROM items LIKE 'image_path'");
if ($check->num_rows == 0) {
    $conn->query("ALTER TABLE items ADD COLUMN image_path VARCHAR(255) DEFAULT NULL AFTER price");
    echo "SUCCESS: image_path column added to items table\n";
} else {
    echo "INFO: image_path column already exists in items table\n";
}
?>
