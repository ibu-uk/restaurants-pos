<?php
// ============================================
// API: Save Pre-Order (creates open/pending invoice)
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

if (!$data || empty($data['items'])) {
    echo json_encode(['error' => 'No items provided']);
    exit;
}

$total        = floatval($data['total']);
$customer_id  = isset($data['customer_id']) ? intval($data['customer_id']) : null;
$customer_name = isset($data['customer_name']) ? $data['customer_name'] : '';
$customer_phone = isset($data['customer_phone']) ? $data['customer_phone'] : '';
$current_user = current_user();
$user_id      = intval($current_user['id']);
$user_name    = !empty($current_user['full_name']) ? $current_user['full_name'] : (!empty($current_user['username']) ? $current_user['username'] : 'Unknown');

// Generate pre-order number
$invoice_number = 'PRE-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

$conn->begin_transaction();

try {
    $status = 'open';
    $payment_mode = 'Pending';
    $cash_paid = 0;
    $change_due = 0;

    // Insert pre-order invoice (no table, no payment yet)
    $stmt = $conn->prepare("INSERT INTO invoices (invoice_number, user_id, user_name, customer_id, customer_name, customer_phone, payment_mode, status, total, cash_paid, change_due, order_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pre_order')");
    $stmt->bind_param('sisissssddd', $invoice_number, $user_id, $user_name, $customer_id, $customer_name, $customer_phone, $payment_mode, $status, $total, $cash_paid, $change_due);
    $stmt->execute();
    $invoice_id = $conn->insert_id;
    $stmt->close();

    // Insert items
    $stmt2 = $conn->prepare("INSERT INTO invoice_items (invoice_id, item_name, item_name_ar, size, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($data['items'] as $item) {
        $item_name    = $item['name'];
        $item_name_ar = isset($item['name_ar']) ? $item['name_ar'] : '';
        $size         = isset($item['size']) ? $item['size'] : null;
        $price        = floatval($item['price']);
        $qty          = intval($item['qty']);
        $subtotal     = $price * $qty;
        $stmt2->bind_param('isssdid', $invoice_id, $item_name, $item_name_ar, $size, $price, $qty, $subtotal);
        $stmt2->execute();
    }
    $stmt2->close();

    $conn->commit();
    echo json_encode(['success' => true, 'invoice_id' => $invoice_id, 'invoice_number' => $invoice_number]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => $e->getMessage()]);
}
$conn->close();
?>
