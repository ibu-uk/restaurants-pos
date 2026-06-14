<?php
// ============================================
// API: Get Pre-Order Details (to load into POS)
// ============================================
header('Content-Type: application/json');
require_once '../db/connect.php';
require_once '../auth.php';
require_api_login();

$invoice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($invoice_id <= 0) {
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

$whereUser = is_admin() ? '' : ' AND i.user_id = ' . intval(current_user()['id']);

$inv = $conn->query("SELECT i.id, i.customer_id, i.customer_name, i.customer_phone, i.total FROM invoices i WHERE i.id = $invoice_id AND i.order_type = 'pre_order' AND i.status = 'open' $whereUser LIMIT 1")->fetch_assoc();

if (!$inv) {
    echo json_encode(['error' => 'Pre-order not found']);
    exit;
}

$items = [];
$res = $conn->query("SELECT item_name, item_name_ar, size, price, quantity FROM invoice_items WHERE invoice_id = $invoice_id");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
    }
}
$conn->close();

echo json_encode([
    'success' => true,
    'invoice' => $inv,
    'items' => $items
]);
?>
