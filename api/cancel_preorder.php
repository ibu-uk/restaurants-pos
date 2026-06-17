<?php
// ============================================
// API: Cancel Pre-Order
// ============================================
header('Content-Type: application/json');
require_once '../db/connect.php';
require_once '../auth.php';
require_api_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$invoice_id = intval($data['invoice_id']);

$whereUser = is_admin() ? '' : ' AND user_id = ' . intval(current_user()['id']);
$sql = "UPDATE invoices SET status = 'cancelled' WHERE id = $invoice_id AND order_type = 'pre_order' AND status = 'open' $whereUser";

try {
    $conn->query($sql);
} catch (\Throwable $e) {
    error_log("[cancel_preorder] SQL Error: " . $e->getMessage() . " | SQL: $sql");
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    $conn->close();
    exit;
}

if ($conn->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Order not found or already processed']);
}
$conn->close();
?>
