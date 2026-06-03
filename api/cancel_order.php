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
        $conn->rollback();
        echo json_encode(['error' => 'Order not found or already paid']);
        exit;
    }

    $table_id = $row['table_id'];

    // Delete invoice items
    $del1 = $conn->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
    $del1->bind_param('i', $invoice_id);
    $del1->execute();
    $del1->close();

    // Delete invoice
    $del2 = $conn->prepare("DELETE FROM invoices WHERE id = ? AND status = 'open'");
    $del2->bind_param('i', $invoice_id);
    $del2->execute();
    $del2->close();

    // Free the table
    if ($table_id) {
        $upd = $conn->prepare("UPDATE restaurant_tables SET status = 'available' WHERE id = ?");
        $upd->bind_param('i', $table_id);
        $upd->execute();
        $upd->close();
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => $e->getMessage()]);
}
$conn->close();
?>
