<?php
require_once 'db/connect.php';
header('Content-Type: text/plain');

echo "=== DB Connection ===\n";
echo "Host: " . DB_HOST . "\n";
echo "DB:   " . DB_NAME . "\n";

// Check if order_type column exists
echo "\n=== invoices columns ===\n";
$res = $conn->query("SHOW COLUMNS FROM invoices");
$has_order_type = false;
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
    if ($row['Field'] === 'order_type') $has_order_type = true;
}

echo "\n=== Result ===\n";
echo "order_type exists: " . ($has_order_type ? 'YES' : 'NO - THIS IS THE PROBLEM') . "\n";

// Show a sample invoice
echo "\n=== Sample invoice ===\n";
$r = $conn->query("SELECT id, invoice_number, status, order_type FROM invoices ORDER BY id DESC LIMIT 1");
if ($r && $r->num_rows) {
    print_r($r->fetch_assoc());
} else {
    echo "No invoices or error: " . $conn->error . "\n";
}

$conn->close();
?>
