<?php
// ============================================
// API: Search Customers by name or phone
// ============================================
header('Content-Type: application/json');
require_once '../db/connect.php';
require_once '../auth.php';
require_api_login();

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

// Prefix match (q%) lets MySQL use the name/phone indexes (index-backed range scan).
// This stays fast even at 10,000+ customers, unlike a leading-wildcard (%q%) full scan.
$prefix = $conn->real_escape_string($q) . '%';
$stmt = $conn->prepare("SELECT id, name, phone, address FROM customers WHERE name LIKE ? OR phone LIKE ? ORDER BY name LIMIT 10");
$stmt->bind_param('ss', $prefix, $prefix);
$stmt->execute();
$res = $stmt->get_result();
$customers = [];
while ($row = $res->fetch_assoc()) {
    $customers[] = $row;
}
$stmt->close();
$conn->close();
echo json_encode($customers);
?>
