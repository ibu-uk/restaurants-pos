<?php
header('Content-Type: application/json');
require_once '../db/connect.php';
require_once '../auth.php';
require_api_login();

if (!is_admin()) {
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? intval($data['id']) : 0;

if ($id <= 0) {
    echo json_encode(['error' => 'Invalid invoice ID']);
    exit;
}

$conn->query("DELETE FROM invoice_items WHERE invoice_id = $id");
$conn->query("DELETE FROM invoices WHERE id = $id");

if ($conn->affected_rows >= 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Delete failed']);
}
$conn->close();
?>
