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

$conn->begin_transaction();
try {
    $d1 = $conn->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
    $d1->bind_param('i', $id);
    $d1->execute();
    $d1->close();

    $d2 = $conn->prepare("DELETE FROM invoices WHERE id = ?");
    $d2->bind_param('i', $id);
    $d2->execute();
    $d2->close();

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => 'Delete failed']);
}
$conn->close();
?>
