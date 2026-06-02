<?php
// ============================================
// API: Save Order
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
$cash_paid    = floatval($data['cash_paid']);
$change_due   = floatval($data['change_due']);
$table_id     = isset($data['table_id']) ? intval($data['table_id']) : null;
$table_name   = isset($data['table_name']) ? $data['table_name'] : null;
$current_user = current_user();
$user_id      = intval($current_user['id']);
$user_name    = $current_user['full_name'];

// Generate invoice number: INV-YYYYMMDD-XXXX
$invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

$conn->begin_transaction();

try {
    // Insert invoice header
    $payment_mode = isset($data['payment_mode']) ? $data['payment_mode'] : 'Cash';
    $payment_reference = isset($data['payment_reference']) ? $data['payment_reference'] : null;
    $status = 'paid';
    $stmt = $conn->prepare("INSERT INTO invoices (invoice_number, user_id, user_name, table_id, table_name, payment_mode, payment_reference, status, total, cash_paid, change_due) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sisissssddd', $invoice_number, $user_id, $user_name, $table_id, $table_name, $payment_mode, $payment_reference, $status, $total, $cash_paid, $change_due);
    $stmt->execute();
    $invoice_id = $conn->insert_id;
    $stmt->close();

    // If this was a table order, free the table
    if ($table_id) {
        $conn->query("UPDATE restaurant_tables SET status = 'available' WHERE id = $table_id");
    }

    // Insert invoice items
    $stmt2 = $conn->prepare("INSERT INTO invoice_items (invoice_id, item_name, item_name_ar, size, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($data['items'] as $item) {
        $item_name = $item['name'];
        $item_name_ar = isset($item['name_ar']) ? $item['name_ar'] : '';
        $size      = isset($item['size']) ? $item['size'] : null;
        $price     = floatval($item['price']);
        $qty       = intval($item['qty']);
        $subtotal  = $price * $qty;
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
