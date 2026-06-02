<?php
// Cancel an open table order (no payment, free the table)
header('Content-Type: application/json');
require_once '../db/connect.php';
require_once '../auth.php';
require_api_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$invoice_id = intval($data['invoice_id'] ?? 0);

if (!$invoice_id) {
    echo json_encode(['error' => 'Missing invoice_id']);
    exit;
}

$conn->begin_transaction();
try {
    // Get table_id before deleting
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

    // Delete invoice items
    $conn->query("DELETE FROM invoice_items WHERE invoice_id = $invoice_id");

    // Delete invoice
    $conn->query("DELETE FROM invoices WHERE id = $invoice_id AND status = 'open'");

    // Free the table
    if ($table_id) {
        $conn->query("UPDATE restaurant_tables SET status = 'available' WHERE id = $table_id");
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => $e->getMessage()]);
}
?>
