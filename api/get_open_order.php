<?php
header('Content-Type: application/json');
require_once '../db/connect.php';
require_once '../auth.php';
require_api_login();

$table_id = intval($_GET['table_id'] ?? 0);
if (!$table_id) {
    echo json_encode(['error' => 'Missing table_id']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM invoices WHERE table_id = ? AND status = 'open' LIMIT 1");
$stmt->bind_param('i', $table_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$invoice) {
    echo json_encode(['invoice' => null, 'items' => []]);
    exit;
}

$items_res = $conn->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$items_res->bind_param('i', $invoice['id']);
$items_res->execute();
$items = $items_res->get_result()->fetch_all(MYSQLI_ASSOC);
$items_res->close();

echo json_encode(['invoice' => $invoice, 'items' => $items]);
?>
