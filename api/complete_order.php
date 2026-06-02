<?php
// Complete payment for an open table order
header('Content-Type: application/json');
require_once '../db/connect.php';
require_once '../auth.php';
require_api_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$invoice_id   = intval($data['invoice_id']);
$payment_mode = $data['payment_mode'] ?? 'Cash';
$total        = floatval($data['total']);
$cash_paid    = floatval($data['cash_paid']);
$change_due   = floatval($data['change_due']);

if (!$invoice_id) {
    echo json_encode(['error' => 'Missing invoice_id']);
    exit;
}

$conn->begin_transaction();

try {
    // Get table_id for this invoice
    $inv = $conn->prepare("SELECT table_id FROM invoices WHERE id = ? AND status = 'open' LIMIT 1");
    $inv->bind_param('i', $invoice_id);
    $inv->execute();
    $row = $inv->get_result()->fetch_assoc();
    $inv->close();

    if (!$row) {
        echo json_encode(['error' => 'Order not found or already paid']);
        exit;
    }

    $table_id = $row['table_id'];

    // Mark invoice as paid
    $stmt = $conn->prepare("UPDATE invoices SET status = 'paid', payment_mode = ?, total = ?, cash_paid = ?, change_due = ? WHERE id = ?");
    $stmt->bind_param('sdddi', $payment_mode, $total, $cash_paid, $change_due, $invoice_id);
    $stmt->execute();
    $stmt->close();

    // Free the table
    $conn->query("UPDATE restaurant_tables SET status = 'available' WHERE id = $table_id");

    $conn->commit();
    echo json_encode(['success' => true, 'invoice_id' => $invoice_id]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => $e->getMessage()]);
}
?>
