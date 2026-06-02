<?php
// Hold/open an order for a table (no payment yet)
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

$table_id   = intval($data['table_id']);
$table_name = $conn->real_escape_string($data['table_name']);
$total      = floatval($data['total']);
$current_user = current_user();
$user_id    = intval($current_user['id']);
$user_name  = $current_user['full_name'];

$conn->begin_transaction();

try {
    // Check if table already has an open order
    $check = $conn->prepare("SELECT id FROM invoices WHERE table_id = ? AND status = 'open' LIMIT 1");
    $check->bind_param('i', $table_id);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();
    $check->close();

    if ($existing) {
        // Update existing open order: delete old items, add new ones
        $invoice_id = $existing['id'];
        $upd = $conn->prepare("UPDATE invoices SET total = ?, user_id = ?, user_name = ? WHERE id = ?");
        $upd->bind_param('disi', $total, $user_id, $user_name, $invoice_id);
        $upd->execute();
        $upd->close();

        $conn->query("DELETE FROM invoice_items WHERE invoice_id = $invoice_id");
    } else {
        // Create new open invoice
        $invoice_number = 'TBL-' . $table_id . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $status = 'open';
        $payment_mode = 'Pending';
        $cash_paid = 0;
        $change_due = 0;

        $stmt = $conn->prepare("INSERT INTO invoices (invoice_number, user_id, user_name, table_id, table_name, payment_mode, status, total, cash_paid, change_due) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sisiissddd', $invoice_number, $user_id, $user_name, $table_id, $table_name, $payment_mode, $status, $total, $cash_paid, $change_due);
        $stmt->execute();
        $invoice_id = $conn->insert_id;
        $stmt->close();
    }

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

    // Mark table as occupied
    $conn->query("UPDATE restaurant_tables SET status = 'occupied' WHERE id = $table_id");

    $conn->commit();
    echo json_encode(['success' => true, 'invoice_id' => $invoice_id]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => $e->getMessage()]);
}
?>
