<?php
header('Content-Type: application/json');
require_once '../db/connect.php';
require_once '../auth.php';
require_api_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
if ($category_id <= 0 || !isset($_FILES['image'])) {
    echo json_encode(['error' => 'Missing category or image']);
    exit;
}

$file = $_FILES['image'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Upload failed']);
    exit;
}

if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(['error' => 'Image must be 2MB or less']);
    exit;
}

$info = getimagesize($file['tmp_name']);
if ($info === false) {
    echo json_encode(['error' => 'Invalid image file']);
    exit;
}

$allowed = [
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG => 'png',
    IMAGETYPE_GIF => 'gif',
    IMAGETYPE_WEBP => 'webp'
];

if (!isset($allowed[$info[2]])) {
    echo json_encode(['error' => 'Only JPG, PNG, GIF, or WEBP allowed']);
    exit;
}

$upload_dir = dirname(__DIR__) . '/uploads/categories';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0775, true);
}

$ext = $allowed[$info[2]];
$filename = 'cat_' . $category_id . '_' . time() . '.' . $ext;
$target = $upload_dir . '/' . $filename;
$relative_path = 'uploads/categories/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $target)) {
    echo json_encode(['error' => 'Could not save image']);
    exit;
}

$stmt = $conn->prepare("UPDATE categories SET image_path = ? WHERE id = ?");
$stmt->bind_param('si', $relative_path, $category_id);
$stmt->execute();
$stmt->close();

$conn->close();
echo json_encode(['success' => true, 'image_path' => $relative_path]);
?>
