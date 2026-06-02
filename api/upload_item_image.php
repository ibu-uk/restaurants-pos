<?php
require_once '../db/connect.php';
require_once '../auth.php';
require_api_admin();

header('Content-Type: application/json');

if (!isset($_FILES['image']) || !isset($_POST['item_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

$item_id = intval($_POST['item_id']);
$image = $_FILES['image'];

// Validate image
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp']);
    exit;
}

if ($image['size'] > 2097152) { // 2MB
    echo json_encode(['success' => false, 'error' => 'File too large. Maximum 2MB']);
    exit;
}

// Create uploads directory if not exists
if (!file_exists('../uploads')) {
    mkdir('../uploads', 0777, true);
}

// Generate unique filename
$new_name = 'item_' . $item_id . '_' . time() . '.' . $ext;
$upload_path = '../uploads/' . $new_name;

// Upload file
if (!move_uploaded_file($image['tmp_name'], $upload_path)) {
    echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
    exit;
}

// Check if image_path column exists
$check = $conn->query("SHOW COLUMNS FROM items LIKE 'image_path'");
if ($check->num_rows == 0) {
    // Try to add the column
    $conn->query("ALTER TABLE items ADD COLUMN image_path VARCHAR(255) DEFAULT NULL AFTER price");
}

// Update database
$image_path = 'uploads/' . $new_name;
$stmt = $conn->prepare("UPDATE items SET image_path = ? WHERE id = ?");
$stmt->bind_param('si', $image_path, $item_id);
$stmt->execute();

if ($stmt->error) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
    exit;
}

echo json_encode(['success' => true, 'image_path' => $image_path]);
?>
